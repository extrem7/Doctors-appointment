<?php
/*
Plugin Name: Запись на приём | Doctors appointment
Plugin URI: http://страница_с_описанием_плагина_и_его_обновлений
Description: Плагин для записи на приём к врачу. Удобно, быстро, по-гаражному.
Version: 0.6
Author: Yurii Kinakh && Alex Roox (второй младший помошник джуниора)
Author URI: https://t.me/drKeinakh
*/

require_once plugin_dir_path(__FILE__) . 'includes/taxonomy-term-image.php';
require_once plugin_dir_path(__FILE__) . 'includes/GoogleCalendar.php';

function pre($array)
{
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}

require_once plugin_dir_path(__FILE__) . 'includes/class-doctors-appointment.php';

$DA = new Doctors_appointment();