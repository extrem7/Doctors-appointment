<?php

Class Doctors_appointment
{
    private $encrypt_method = "AES-256-CBC";
    private $secret_key;
    private $secret_iv;
    private $key;
    private $iv;
    private $googleClient;

    function __construct()
    {
        add_action('init', [$this, 'pluginInit']);
        register_activation_hook(__FILE__, [$this, 'pluginActivation']);
        register_deactivation_hook(__FILE__, [$this, 'pluginDeactivation']);


        $this->secret_key = 'Do a 100 x 4 bench press' . $_SERVER['SERVER_NAME'];
        $this->secret_iv = 'Sorry, i am late for my tour' . $_SERVER['SERVER_NAME'];
        $this->key = hash('sha256', $this->secret_key);
        $this->iv = substr(hash('sha256', $this->secret_iv), 0, 16);

        $this->googleClient = new GoogleCalendar('primary');
    }

    public function add_action($hook, $component, $callback)
    {

    }

    public function add_filter($hook, $component, $callback)
    {

    }

    //plugin initialisation method
    public function pluginInit()
    {
        register_post_type('da-record', [
            'labels' => [
                'name' => 'Запись на приём', // основное название для типа записи
                'singular_name' => 'Запись на приём', // название для одной записи этого типа
                'add_new' => 'Добавить запись', // для добавления новой записи
                'add_new_item' => 'Добавление записи', // заголовка у вновь создаваемой записи в админ-панели.
                'edit_item' => 'Редактирование записи', // для редактирования типа записи
                'new_item' => '', // текст новой записи
                'view_item' => 'Смотреть запись', // для просмотра записи этого типа.
                'search_items' => 'Искать запись', // для поиска по этим типам записи
                'not_found' => 'Не найдено записей', // если в результате поиска ничего не было найдено
                'not_found_in_trash' => 'Не найдено в корзине', // если не было найдено в корзине
                'menu_name' => 'Журнал записей', // название меню
            ],
            'public' => true,
            'publicly_queryable' => false,
            'menu_position' => 2,
            'menu_icon' => 'dashicons-heart',
            'supports' => array('title', 'editor', 'custom-fields'),
            'has_archive' => false,
        ]);
        register_taxonomy('da-doctors', ['da-record'], [
            'label' => '', // определяется параметром $labels->name
            'labels' => array(
                'name' => 'Врач',
                'singular_name' => 'Врач',
                'search_items' => 'Искать врача',
                'all_items' => 'Все врачи',
                'view_item ' => 'Смотреть врача',
                'edit_item' => 'Редактировать врача',
                'update_item' => 'Обновить врача',
                'menu_name' => 'Врачи',
            ),
            'description' => '', // описание таксономии
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true, // равен аргументу public
            'hierarchical' => false,
            'show_admin_column' => true
        ]);
        register_taxonomy('da-disease', ['da-record'], [
            'label' => '', // определяется параметром $labels->name
            'labels' => array(
                'name' => 'Болезнь',
                'singular_name' => 'Болезнь',
                'search_items' => 'Искать болезни',
                'all_items' => 'Все болезни',
                'view_item ' => 'Смотреть болезнь',
                'edit_item' => 'Редактировать болезнь',
                'update_item' => 'Обновить болезнь',
                'menu_name' => 'Болезни',
            ),
            'description' => '', // описание таксономии
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true, // равен аргументу public
            'hierarchical' => false,
            'show_admin_column' => true
        ]);
        register_taxonomy('da-record_status', ['da-record'], [
            'label' => '', // определяется параметром $labels->name
            'labels' => array(
                'name' => 'Статус',
                'singular_name' => 'Статус',
                'search_items' => 'Искать статус',
                'all_items' => 'Все статусы',
                'view_item ' => 'Смотреть статус',
                'edit_item' => 'Редактировать статус',
                'update_item' => 'Обновить статус',
                'menu_name' => 'Статус',
            ),
            'description' => '', // описание таксономии
            'public' => false,
            'show_ui' => false, // равен аргументу public
            'hierarchical' => false,
            'show_admin_column' => false
        ]);
        $this->adminColumns();
        $this->registerAssets();

        if (isset($_POST['da_form'])) {
            $this->createRecord();
            exit;
        }
        if (is_admin()) {
            $this->admin();
        }
        add_filter('enter_title_here', function ($title) {
            $screen = get_current_screen();

            if ('da-record' == $screen->post_type) {
                $title = 'Введите имя клиента';
            }

            return $title;
        });

        add_filter('views_edit-da-record', function ($views) {

            $canceled = isset($_GET['da-record_status']) && $_GET['da-record_status'] == 'canceled' ? 'current' : '';
            $done = isset($_GET['da-record_status']) && $_GET['da-record_status'] == 'done' ? 'current' : '';
            $new = !($canceled || $done) ? 'current' : '';

            $count = new WP_Query([
                'post_type' => 'da-record',
                'posts_per_page' => -1,
                'tax_query' => [
                    [
                        'taxonomy' => 'da-record_status',
                        'field' => 'slug',
                        'terms' => 'canceled'
                    ]
                ]
            ]);
            $views['all'] = "<a href=\"edit.php?post_type=da-record&all_posts=1\" class=\"$new\" aria-current=\"page\">Новые</a>";
            $count = $count->post_count;
            $views['canceled'] = "<a href=\"edit.php?da-record_status=canceled&post_type=da-record\" class=\"$canceled\" > Отмененные <span class=\"count\">($count)</span></a>";
            $count = new WP_Query([
                'post_type' => 'da-record',
                'posts_per_page' => -1,
                'tax_query' => [
                    [
                        'taxonomy' => 'da-record_status',
                        'field' => 'slug',
                        'terms' => 'done'
                    ]
                ]
            ]);
            $count = $count->post_count;
            $views['done'] = "<a href=\"edit.php?da-record_status=done&post_type=da-record\" class=\"$done\">Завершенные <span class=\"count\">($count)</span></a>";
            return $views;
        });
        add_filter('pre_get_posts', function ($query) {
            if (is_admin() && isset($_GET['post_type']) && $_GET['post_type'] == 'da-record' && !isset($_GET['da-record_status']) && $query->is_main_query()) {
                $query->set('tax_query', [[
                    'taxonomy' => 'da-record_status',
                    'field' => 'term_id',
                    'terms' => array(14, 16),
                    'operator' => 'NOT IN',
                ]]);
            }
            return $query;
        });

        $this->doctorFields();
        $this->polyFields();
        $this->adminPage();
        $this->registerShortcodes();
    }

    //plugin activation method
    public function pluginActivation()
    {
        //$this->pluginInit();
        flush_rewrite_rules();
    }

    //plugin deactivation method
    public function pluginDeactivation()
    {
        flush_rewrite_rules();
    }

    public function registerAssets()
    {
        function da_enqueue_scripts()
        {
            $js = plugin_dir_url(dirname(__FILE__)) . 'public/js/';
            wp_enqueue_script('jquery-3.3.1', $js . 'jquery-3.3.1.min.js', null, false, true);
            wp_enqueue_script('maskedinput', $js . 'jquery.maskedinput.min.js', null, false, true);
            wp_enqueue_script('jquery.modal', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js', null, false, true);
            wp_enqueue_script('vue', 'https://cdn.jsdelivr.net/npm/vue@2.5.16/dist/vue.js', null, false, true);
            wp_enqueue_script('vue-resource', 'https://cdn.jsdelivr.net/npm/vue-resource@1.5.1', null, false, true);
            wp_enqueue_script('slick', $js . 'slick.min.js', null, false, true);
            wp_enqueue_script('main', $js . 'main.js', null, false, true);
        }

        add_action('wp_enqueue_scripts', 'da_enqueue_scripts');

        function da_enqueue_styles()
        {
            wp_enqueue_style('jquery.modal', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css');
            wp_enqueue_style('main', plugin_dir_url(dirname(__FILE__)) . 'public/css/main.css');
        }

        add_action('wp_print_styles', 'da_enqueue_styles');
    }

    public function admin()
    {
        function load_custom_wp_admin_style($hook)
        {

            if ($hook == 'edit.php') {
                wp_enqueue_style('custom_wp_admin_css', plugin_dir_url(dirname(__FILE__)) . '/admin/css/admin.css');
            }
        }

        add_action('admin_enqueue_scripts', 'load_custom_wp_admin_style');


        if (isset($_GET['cancel']) && !empty($_GET['cancel']) && get_post($_GET['cancel'])) {
            wp_set_object_terms($_GET['cancel'], 'canceled', 'da-record_status');
        }

        if (isset($_GET['done']) && !empty($_GET['done']) && get_post($_GET['done'])) {
            wp_set_object_terms($_GET['done'], 'done', 'da-record_status');
            $client = get_post($_GET['done']);
            if (get_post_meta($client->ID, 'email')) {
                $clientMail = get_post_meta($client->ID, 'email')[0];
                $clientName = $client->post_title;

                $this->sendCommentMail($clientMail, $clientName, $client->ID);
            } else {
                add_filter('views_edit-da-record', function ($views) {
                    echo '<h2>Клиент не указал email. Запись помещена в "Завершенные.</h2>"';
                    return $views;
                });
            }
        }
    }

    public function adminPage()
    {
        add_action('admin_menu', function () {
            add_options_page('Doctors appointment', 'Doctors appointment', 'manage_options', 'doctors-appointment', 'da_plugin_page');
        });


        add_action('admin_init', function () {
            register_setting('da-plugin-settings', 'da_email_admin');
            register_setting('da-plugin-settings', 'da_calendar_reminder_email');
            register_setting('da-plugin-settings', 'da_calendar_reminder_popup');
            register_setting('da-plugin-settings', 'da_record_subject');
            register_setting('da-plugin-settings', 'da_record_template');
            register_setting('da-plugin-settings', 'da_comment_subject');
            register_setting('da-plugin-settings', 'da_comment_template');
            register_setting('da-plugin-settings', 'da_modal_success');
            register_setting('da-plugin-settings', 'da_modal_comment_title');
            register_setting('da-plugin-settings', 'da_modal_comment_text');
        });


        function da_plugin_page()
        {
            require_once plugin_dir_path(dirname(__FILE__)) . 'templates/admin.php';
        }
    }

    public function doctorFields()
    {
        function add_doctor_link()
        {
            ?>
            <div class="form-field">
                <label for="term_meta[link_term_meta]">Cсылка</label>
                <input type="text" name="term_meta[links_term_meta]" id="term_meta[link_term_meta]" value="">
                <p class="description">Вставте ссылку на врача</p>
            </div>
            <?php
        }

        add_action('da-doctors_add_form_fields', 'add_doctor_link', 10, 2);
        function edit_doctor_link($term)
        {

            $t_id = $term->term_id;
            $term_meta = get_option("taxonomy_$t_id");
            ?>
            <tr class="form-field">
                <th scope="row" valign="top"><label
                            for="term_meta[link_term_meta]">Cсылка</label></th>
                <td>
                    <input type="text" name="term_meta[link_term_meta]" id="term_meta[link_term_meta]"
                           value="<?php echo esc_attr($term_meta['link_term_meta']) ? esc_attr($term_meta['link_term_meta']) : ''; ?>">
                    <p class="description">Вставте ссылку на врача</p>
                </td>
            </tr>
            <?php
        }

        add_action('da-doctors_edit_form_fields', 'edit_doctor_link', 10, 2);
        function save_doctor_link($term_id)
        {
            if (isset($_POST['term_meta'])) {

                $t_id = $term_id;
                $term_meta = get_option("taxonomy_$t_id");
                $cat_keys = array_keys($_POST['term_meta']);
                foreach ($cat_keys as $key) {
                    if (isset ($_POST['term_meta'][$key])) {
                        $term_meta[$key] = $_POST['term_meta'][$key];
                    }
                }
                // Save the option array.
                update_option("taxonomy_$t_id", $term_meta);
            }

        }

        add_action('edited_da-doctors', 'save_doctor_link', 10, 2);
        add_action('create_da-doctors', 'save_doctor_link', 10, 2);
        /*Polylang*/
        function add_doctor_title_uk()
        {
            ?>
            <div class="form-field">
                <label for="term_meta[title_uk]">Ім'я українською</label>
                <input type="text" name="term_meta[title_uk]" id="term_meta[title_uk]" value="">
            </div>
            <?php
        }

        add_action('da-doctors_add_form_fields', 'add_doctor_title_uk', 10, 2);
        function edit_doctor_title_uk($term)
        {

            $t_id = $term->term_id;
            $term_meta = get_option("taxonomy_$t_id");
            ?>
            <tr class="form-field">
                <th scope="row" valign="top"><label
                            for="term_meta[title_uk]">Ім'я українською</label></th>
                <td>
                    <input type="text" name="term_meta[title_uk]" id="term_meta[title_uk]"
                           value="<?php echo esc_attr($term_meta['title_uk']) ? esc_attr($term_meta['title_uk']) : ''; ?>">
                </td>
            </tr>
            <?php
        }

        add_action('da-doctors_edit_form_fields', 'edit_doctor_title_uk', 10, 2);
        function save_doctor_title_uk($term_id)
        {
            if (isset($_POST['term_meta'])) {

                $t_id = $term_id;
                $term_meta = get_option("taxonomy_$t_id");
                $cat_keys = array_keys($_POST['term_meta']);
                foreach ($cat_keys as $key) {
                    if (isset ($_POST['term_meta'][$key])) {
                        $term_meta[$key] = $_POST['term_meta'][$key];
                    }
                }
                // Save the option array.
                update_option("taxonomy_$t_id", $term_meta);
            }

        }

        add_action('edited_da-doctors', 'save_doctor_title_uk', 10, 2);
        add_action('create_da-doctors', 'save_doctor_title_uk', 10, 2);
        /*Polylang description*/
        function add_doctor_description_uk()
        {
            ?>
            <div class="form-field">
                <label for="term_meta[description_uk]">Опис українською</label>
                <textarea name="term_meta[description_uk]" id="term_meta[description_uk]" rows="5" cols="50"
                          class="large-text"></textarea>
            </div>
            <?php
        }

        add_action('da-doctors_add_form_fields', 'add_doctor_description_uk', 10, 2);
        function edit_doctor_description_uk($term)
        {

            $t_id = $term->term_id;
            $term_meta = get_option("taxonomy_$t_id");
            ?>
            <tr class="form-field">
                <th scope="row" valign="top"><label
                            for="term_meta[description_uk]">Опис українською</label></th>
                <td>
                    <textarea name="term_meta[description_uk]" id="term_meta[description_uk]" rows="5" cols="50"
                              class="large-text"><?php echo esc_attr($term_meta['description_uk']) ? esc_attr($term_meta['description_uk']) : ''; ?>
                    </textarea>
                </td>
            </tr>
            <?php
        }

        add_action('da-doctors_edit_form_fields', 'edit_doctor_description_uk', 10, 2);
        function save_doctor_description_uk($term_id)
        {
            if (isset($_POST['term_meta'])) {

                $t_id = $term_id;
                $term_meta = get_option("taxonomy_$t_id");
                $cat_keys = array_keys($_POST['term_meta']);
                foreach ($cat_keys as $key) {
                    if (isset ($_POST['term_meta'][$key])) {
                        $term_meta[$key] = $_POST['term_meta'][$key];
                    }
                }
                // Save the option array.
                update_option("taxonomy_$t_id", $term_meta);
            }

        }

        add_action('edited_da-doctors', 'save_doctor_description_uk', 10, 2);
        add_action('create_da-doctors', 'save_doctor_description_uk', 10, 2);
    }

    public function polyFields()
    {
        function add_title_uk()
        {
            ?>
            <div class="form-field">
                <label for="term_meta[title_uk]">Назва українською</label>
                <input type="text" name="term_meta[title_uk]" id="term_meta[title_uk]" value="">
            </div>
            <?php
        }

        add_action('da-disease_add_form_fields', 'add_title_uk', 10, 2);
        function edit_title_uk($term)
        {

            $t_id = $term->term_id;
            $term_meta = get_option("taxonomy_$t_id");
            ?>
            <tr class="form-field">
                <th scope="row" valign="top"><label
                            for="term_meta[title_uk]">Назва українською</label></th>
                <td>
                    <input type="text" name="term_meta[title_uk]" id="term_meta[title_uk]"
                           value="<?php echo esc_attr($term_meta['title_uk']) ? esc_attr($term_meta['title_uk']) : ''; ?>">
                </td>
            </tr>
            <?php
        }

        add_action('da-disease_edit_form_fields', 'edit_title_uk', 10, 2);
        function save_title_uk($term_id)
        {
            if (isset($_POST['term_meta'])) {

                $t_id = $term_id;
                $term_meta = get_option("taxonomy_$t_id");
                $cat_keys = array_keys($_POST['term_meta']);
                foreach ($cat_keys as $key) {
                    if (isset ($_POST['term_meta'][$key])) {
                        $term_meta[$key] = $_POST['term_meta'][$key];
                    }
                }
                // Save the option array.
                update_option("taxonomy_$t_id", $term_meta);
            }

        }

        add_action('edited_da-disease', 'save_title_uk', 10, 2);
        add_action('create_da-disease', 'save_title_uk', 10, 2);
    }

    public function adminColumns()
    {
        function add_record_columns($columns)
        {

            $columns['title'] = 'Пациент';
            unset($columns['date']);
            unset($columns['taxonomy-da-record_status']);
            $customColumns = [
                'phone' => 'Телефон',
                'email' => 'Почта',
                'comment' => 'Комментарий',
                'rate' => 'Оценка',
                'answer' => 'Отзыв',
                'date' => 'Дата',
                'status' => 'Статус',
            ];
            return array_merge($columns, $customColumns);
        }

        add_filter('manage_da-record_posts_columns', 'add_record_columns');

        function custom_record_column($column, $post_id)
        {
            switch ($column) {
                case 'phone' :
                    $phone = get_post_meta($post_id, 'phone')[0];
                    echo "<a href=\"tel:$phone\">" . $phone . "</a>";
                    break;
                case 'email' :
                    if (get_post_meta($post_id, 'email')) {
                        $email = get_post_meta($post_id, 'email')[0];
                        echo "<a href=\"mailto:$email\">" . $email . "</a>";
                    }
                    break;
                case 'comment' :
                    if (get_post_field('post_content', $post_id)) {
                        $comment = get_post_field('post_content', $post_id);
                        echo $comment;
                    }
                    break;
                case 'status' :
                    $hasStatus = !empty(wp_get_post_terms($post_id, 'da-record_status'));
                    $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    if (!$hasStatus) {
                        echo "<a class='button' href='" . $actual_link . "&cancel=$post_id'>Отменена</a><br><br>";
                        echo "<a class='button' href='" . $actual_link . "&done=$post_id'>Завершена</a><br><br>";
                    } else {
                        $term = wp_get_post_terms($post_id, 'da-record_status')[0];
                        $name = $term->name;
                        $slug = $term->slug;
                        echo "<a href='" . $actual_link . "&da-record_status=$slug'>$name</a><br><br>";
                    }
                    break;
                case 'rate' :
                    if (get_post_meta($post_id, 'rate')) {
                        echo get_post_meta($post_id, 'rate')[0];
                    }
                    break;
                case 'answer' :
                    if (get_post_meta($post_id, 'comment')) {
                        echo get_post_meta($post_id, 'comment')[0];
                    }
                    break;

            }
        }

        add_action('manage_posts_custom_column', 'custom_record_column', 10, 2);
    }

    public function registerShortcodes()
    {
        add_shortcode('da-carousel', [$this, 'theRecordForm']);
        add_shortcode('da-button', function ($atts) {
            $text = !empty($atts) && key_exists('text', $atts) ? $atts['text'] : 'Запись';
            $doctor = !empty($atts) && key_exists('doctor', $atts) ? $atts['doctor'] : '1';
            return "<button class='btn doctors-button' data-doctor=\"$doctor\">$text</button>";
        });
    }

    public static function theRecordForm()
    {
        $doctors = get_terms('da-doctors', ['hide_empty' => false]);
        $diseases = get_terms('da-disease', ['hide_empty' => false]);
        require_once plugin_dir_path(dirname(__FILE__)) . 'templates/form.php';
    }

    public function createRecord()
    {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = null;
        $doctor = null;
        $disease = null;
        $date = null;
        $time = null;
        $hour = null;
        $minute = null;
        $comment = null;
        $content = '';

        //check POST data for existing
        if (isset($_POST['email']) && !empty($_POST['email'])) {
            $email = $_POST['email'];
        }
        if (isset($_POST['doctor']) && !empty($_POST['doctor'])) {
            $doctor = $_POST['doctor'];
        }
        if (isset($_POST['disease']) && !empty($_POST['disease'])) {
            $disease = $_POST['disease'];
        }
        if (isset($_POST['date']) && !empty($_POST['date'])) {
            $date = $_POST['date'];
            $dateExploded = explode('-', $date);
            $year = $dateExploded[0];
            $month = $dateExploded[1];
            $day = $dateExploded[2];
        }
        if (isset($_POST['time']) && !empty($_POST['time'])) {
            $time = $_POST['time'];
            $timeExploded = explode(':', $time);
            $hour = $timeExploded[0];
            $minute = $timeExploded[1];
        }
        if (isset($_POST['comment']) && !empty($_POST['comment'])) {
            $comment = $_POST['comment'];
            $content = $comment . '<br>Добавлено в: ' . date('m/d/Y', time());
        }

        //preparing data for new record insert
        $postData = [
            'post_title' => $name,
            'post_content' => $content,
            'post_type' => 'da-record',
            'post_status' => 'publish',
            'meta_input' => [
                'phone' => $phone,
                'email' => $email
            ]
        ];
        if ($date) {
            $postData['post_date'] = "$date $time:00";
        }
        $postId = wp_insert_post($postData);
        if ($postId && !empty($date) && !empty($time)) {
            $doctor = get_term_by('id', $doctor, 'da-doctors')->slug;
            wp_set_object_terms($postId, $doctor, 'da-doctors');
            wp_set_object_terms($postId, $disease, 'da-disease');


            $doctor = get_term_by('slug', $doctor, 'da-doctors');
            $doctorName = $doctor->name;

            $calendarText = "Запись к врачу: $doctorName\nНа $date $time\nКлиент: $name\nТелефон: $phone\n";
            $calendarText .= $email ? "Почта: $email\n" : '';
            $calendarText .= $comment ? "Примечание клиента: $comment\n" : '';
            if ($email) {
                $this->sendClientMail($email, $name, $doctorName, $date, $time);
            }
            $this->sendAdminMail($calendarText);
            $this->googleClient->insertEvent($date, $hour, $minute, $name, $calendarText, $email);
        }
    }

    public function sendClientMail($to, $name, $doctor, $data, $time)
    {
        $headers = "From: $this->mailFrom <admin@" . $_SERVER['SERVER_NAME'] . ">";
        $subject = get_option('da_record_subject');
        $template = get_option('da_record_template');
        $message = sprintf($template, $name, $doctor, $data, $time);
        mail($to, $subject, $message, $headers);
    }

    public function sendCommentMail($to, $name, $id)
    {
        $headers = "From: Doctors <admin@" . $_SERVER['SERVER_NAME'] . ">\n";//todo
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $subject = get_option('da_comment_subject');


        $hash = openssl_encrypt($id, $this->encrypt_method, $this->key, 0, $this->iv);
        $hash = base64_encode($hash);
        $template = get_option('da_comment_template');
        $message = '<html><body>';
        $message .= sprintf($template, $name);
        $message .= '<form action="' . plugin_dir_url(dirname(__FILE__)) . 'answer.php" method="post" target="_blank">';
        $message .= '<input name="hash" value="' . $hash . '" type="hidden" />';
        $message .= '<input name="rating" value="1" type="radio" />1<br />';
        $message .= '<input name="rating" value="2" type="radio" />2<br />';
        $message .= '<input name="rating" value="3" type="radio" />3<br />';
        $message .= '<input name="rating" value="4" type="radio" />4<br />';
        $message .= '<input name="rating" value="5" type="radio" />5<br />';
        $message .= '<br />';
        $message .= '<label for="commentText">Оставте краткий отзыв:</label><br />';
        $message .= '<textarea cols="75" name="commentText" rows="5"></textarea><br />';
        $message .= '<br />';
        $message .= '<input type="submit" value="Отправить" />&nbsp;</form>';
        $message .= '</body></html>';
        mail($to, $subject, $message, $headers);
    }

    public function sendAdminMail($message)
    {
        $headers = "From: $this->mailFrom <admin@" . $_SERVER['SERVER_NAME'] . ">";
        $adminMail = get_option('da_email_admin');
        $subject = 'Запись на прием';//todo future
        mail($adminMail, $subject, $message, $headers);
    }

    public function verifyComment($hash)
    {
        $postId = openssl_decrypt(base64_decode($hash), $this->encrypt_method, $this->key, 0, $this->iv);
        if (get_post($postId) && (!get_post_meta($postId, 'rate') || !get_post_meta($postId, 'comment'))) {
            update_post_meta($postId, 'rate', $_POST['rating']);
            update_post_meta($postId, 'comment', $_POST['commentText']);
        } else {
            exit;
        }

    }
}