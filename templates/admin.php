<div class="wrap">
    <form action="options.php" method="post">

        <?php
        settings_fields('da-plugin-settings');
        do_settings_sections('da-plugin-settings');
        $googleCalendar = new GoogleCalendar('primary');
        ?>
        <h1>Настройки плагина Doctors appointment</h1>
        <br>
        <table>
            <tr>
                <td>Почта админа</td>
                <td><input type="text" placeholder="admin@example.com" name="da_email_admin"
                           value="<?= esc_attr(get_option('da_email_admin')); ?>" size="50"/></td>
            </tr>
            <tr>
                <td>Время оповещения на почту (в часах)</td>
                <td><input type="number" placeholder="1 час" name="da_calendar_reminder_email"
                           value="<?= esc_attr(get_option('da_calendar_reminder_email')); ?>" size="1"/></td>
            </tr>
            <tr>
                <td>Время оповещения на телефон (в часах)</td>
                <td><input type="number" placeholder="1 час" name="da_calendar_reminder_popup"
                           value="<?= esc_attr(get_option('da_calendar_reminder_popup')); ?>" size="1"/></td>
            </tr>
            <tr>
                <td colspan="2">
                    <? if (isset($_GET['googleAccess'])): ?>
                        <p>Вход успешно осуществлен</p>
                    <? endif; ?>
                    <a href="<?= $googleCalendar->getAuthUrl() ?>" class="button">Войти через Google</a>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>

            <tr>
                <td>Тема записи на приём</td>
                <td><input type="text" placeholder="" name="da_record_subject"
                           value="<?= esc_attr(get_option('da_record_subject')); ?>" size="50"/></td>
            </tr>
            <tr>
                <td>Шаблон записи на приём</td>
                <td><textarea placeholder="Здравствуйте, %1$s. Вы записались к врачу %2$s на %3$s %4$s"
                              name="da_record_template" rows="4"
                              cols="52"><?= esc_attr(get_option('da_record_template')); ?></textarea>
                </td>
            </tr>
            <tr>
                <td>Тема письма оценки</td>
                <td><input type="text" placeholder="" name="da_comment_subject"
                           value="<?= esc_attr(get_option('da_comment_subject')); ?>" size="50"/></td>
            </tr>
            <tr>
                <td>Шаблон записи на приём</td>
                <td><textarea placeholder="Здравствуйте, %1$s. Пожалуйста, оцените нашу работу"
                              name="da_comment_template" rows="4"
                              cols="52"><?= esc_attr(get_option('da_comment_template')); ?></textarea>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>

            <tr>
                <td>Текст модального окна записи</td>
                <td><input type="text" placeholder="" name="da_modal_success"
                           value="<?= esc_attr(get_option('da_modal_success')); ?>" size="50"/></td>
            </tr>
            <tr>
                <td>Заголовок модального окна благодарности</td>
                <td><input type="text" placeholder="" name="da_modal_comment_title"
                           value="<?= esc_attr(get_option('da_modal_comment_title')); ?>" size="50"/></td>
            </tr>
            <tr>
                <td>Текст модального окна благодарности</td>
                <td><textarea type="text" placeholder="" name="da_modal_comment_text"
                              rows="4" cols="52"><?= esc_attr(get_option('da_modal_comment_text')); ?></textarea></td>
            </tr>

            <tr>
                <td><?php submit_button(); ?></td>
            </tr>

        </table>

    </form>
</div>