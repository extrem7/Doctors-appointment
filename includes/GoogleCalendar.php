<?php

require_once 'google-api-php-client-2.2.2/vendor/autoload.php';

class GoogleCalendar
{
    private $calendarId;

    private $client;

    private $service;

    public function __construct($calendarID = 'primary')
    {
        $this->client = $this->getClient();
        $this->service = new Google_Service_Calendar($this->client);
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    private function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName('Doctor appointment');
        $client->setScopes(Google_Service_Calendar::CALENDAR);
        $client->setAuthConfig(plugin_dir_path(__FILE__) . 'client_secret.json');
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true);
        $client->setApprovalPrompt('force');

        $refresh = '';//todo
        if (isset($_GET['code'])) {
            $auth = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            $token = $auth['access_token'];
            update_option('access_token', $token);
            $refresh = $auth['refresh_token'];
            update_option('refresh_token', $refresh);
            header('Location: /wp-admin/options-general.php?page=doctors-appointment&googleAccess=1');
            exit;
        } else {
            if (get_option('refresh_token')) {
                $newToken = $client->refreshToken(get_option('refresh_token'))['access_token'];
                update_option('access_token', $newToken);
            }
            if (get_option('access_token')) {
                $client->setAccessToken(get_option('access_token'));
            }
        }
        return $client;
    }

    public function insertEvent($date, $hour, $minute, $title, $text, $clientMail = null)
    {
        $endHour = $hour == 23 ? 0 : $hour + 1;

        $attendees = [];
        if ($clientMail) {
            $attendees[] = ['email' => $clientMail];
        }
        //$attendees[] = ['email' => 'alex.roox5@gmail.com'];

        $event = new Google_Service_Calendar_Event([
            'summary' => $title,
            'location' => 'Киевская городская клиническая больница 8 - Хирургия',
            'description' => $text,
            'start' => [
                'dateTime' => "{$date}T{$hour}:{$minute}:00",
                'timeZone' => 'Europe/Kiev',
            ],
            'end' => [
                'dateTime' => "{$date}T{$endHour}:{$minute}:00",
                'timeZone' => 'Europe/Kiev',
            ],
            'recurrence' => [
                'RRULE:FREQ=DAILY;COUNT=1'
            ],
            'attendees' => $attendees,
            'reminders' => [
                'useDefault' => FALSE,
                'overrides' => [
                    ['method' => 'email', 'minutes' => get_option('da_calendar_reminder_email') * 60],
                    ['method' => 'popup', 'minutes' => get_option('da_calendar_reminder_popup') * 60],
                ],
            ],
        ]);
        try {
            $this->service->events->insert('primary', $event);
        } catch (\Exception $e) {
            pre($e);
            return false;
        }
        return true;
    }

    public function getEvents()
    {
        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => 10,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        );
        $results = $this->service->events->listEvents($calendarId, $optParams);

        if (empty($results->getItems())) {
            print "No upcoming events found.\n";
        } else {
            print "Upcoming events:\n";
            foreach ($results->getItems() as $event) {
                $start = $event->start->dateTime;
                if (empty($start)) {
                    $start = $event->start->date;
                }
                printf("%s (%s)\n", $event->getSummary(), $start);
            }
        }
    }

}