<? $ru = pll_current_language() == 'ru' ?>
<div class="da-wrapper">
    <div class="doctors-carousel" id="doctors-slider" style="max-width: 970px">
        <? foreach ($doctors as $doctor): ?>
            <div class="doctors-carousel-item">
                <img src="<?= wp_prepare_attachment_for_js($doctor->term_image)['url'] ?>" class="doctors-image"
                     alt="<?= $doctor->name ?>">
                <button class="btn doctors-button"
                        data-doctor="<?= $doctor->term_id ?>"><?= $ru ? 'Записаться на прием' : 'Записатися на прийом' ?></button>
                <a href="<?= get_option('taxonomy_' . $doctor->term_id)['link_term_meta'] ?>"
                   class="doctors-name"><?= $ru ? $doctor->name : get_option('taxonomy_' . $doctor->term_id)['title_uk'] ?></a>
                <div class="doctors-description"><?= $ru ? $doctor->description : get_option('taxonomy_' . $doctor->term_id)['description_uk'] ?></div>
            </div>
        <? endforeach; ?>
    </div>
    <button class="btn doctors-button" data-doctor="20">Отдельная кнопка</button>
    <div id="modal-form" class="modal">
        <header class="modal-header">
            <h2><?= $ru ? 'Записаться на приём к врачу' : 'Записатися на прийом до лікаря' ?></h2>
        </header>
        <section class="modal-body">
            <form action="/" method="post" class="inline-form da-form">
                <div class="input-box">
                    <input type="text" class="input-control" name="name" required>
                    <span class="bar"></span>
                    <label><?= $ru ? 'Имя' : 'Ім\'я' ?></label>
                </div>
                <div class="input-box">
                    <input type="tel" class="input-control" name="phone" required>
                    <span class="bar"></span>
                    <label>Телефон</label>
                </div>
                <div class="input-box">
                    <input type="email" class="input-control" name="email" placeholder=" " required>
                    <span class="bar"></span>
                    <label>Email</label>
                </div>
                <div class="data-time">
                    <div class="input-box">
                        <input type="date" class="input-control" name="date"
                               value="<?= date('Y-m-d', strtotime(' +1 day')) ?>" required>
                        <span class="bar"></span>
                    </div>
                    <div class="input-box">
                        <input type="time" class="input-control" name="time" value="12:00" required>
                        <span class="bar"></span>
                    </div>
                </div>
                <div class="input-box">
                    <select name="doctor" class="input-control">
                        <? foreach ($doctors as $doctor): ?>
                            <option value="<?= $doctor->term_id ?>"><?= $ru ? $doctor->name : get_option('taxonomy_' . $doctor->term_id)['title_uk'] ?></option>
                        <? endforeach; ?>
                    </select>
                    <span class="bar"></span>
                </div>
                <div class="input-box">
                    <select name="disease" id="" class="input-control">
                        <? foreach ($diseases as $disease): ?>
                            <option value="<?= $disease->slug ?>"><?= $ru ? $disease->name : get_option('taxonomy_' . $disease->term_id)['title_uk'] ?></option>
                        <? endforeach; ?>
                    </select>
                    <span class="bar"></span>
                </div>
                <div class="input-box w-100">
                                <textarea name="comment" class="input-control" id="" rows="3"
                                          placeholder="<?= $ru ? 'Дополнительные коментарии' : 'Додатковий коментар' ?>"></textarea>
                    <span class="bar"></span>
                </div>
                <div class="input-box w-100 d-flex justify-content-end">
                    <input type="submit" class="btn btn-primary submit" name="da_form"
                           value="<?= $ru ? 'Записаться' : 'Записатися' ?>">
                </div>
                <input type="hidden" name="da_form" value="1">
            </form>
        </section>
    </div>
    <div id="modal-success" class="modal">
        <header class="modal-header">
            <h2><?= get_option('da_modal_success') ?></h2>
        </header>
    </div>
    <div id="modal-comment" class="modal">
        <header class="modal-header">
            <h2><?= get_option('da_modal_comment_title') ?></h2>
        </header>
        <section class="modal-body">
            <p class="text-center">
                <i class="fa fa-thumbs-o-up success" aria-hidden="true"></i>
                <?= get_option('da_modal_comment_text') ?>
            </p>
        </section>
    </div>
</div>