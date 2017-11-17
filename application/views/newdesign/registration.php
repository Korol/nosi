<?php
$breadcrumbs = array(
    0 => array(
        'title' => 'Главная',
        'url' => base_url(),
    ),
    1 => array(
        'title' => 'Регистрация',
        'url' => base_url('registration'),
    ),
);
$delay = 10;
?>

<div class="row">
    <div class="col-lg-12">
        <?php if(!empty($breadcrumbs)): ?>
            <ol class="breadcrumb category-breadcrumbs">
                <?php for($i = 0; $i < sizeof($breadcrumbs); $i++): ?>
                    <?php $bc_li_class = ($i == (sizeof($breadcrumbs) - 1)) ? 'active' : ''; ?>
                    <li class="<?= $bc_li_class; ?>"><a href="<?= $breadcrumbs[$i]['url']; ?>"><?= $breadcrumbs[$i]['title']; ?></a></li>
                <?php endfor; ?>
            </ol>
        <?php endif; ?>
    </div>
</div>
<?php if(!empty($registration_errors) || !empty($form_errors)): ?>
    <div class="row">
        <div class="col-lg-6 col-lg-offset-3">
            <div class="alert alert-danger" role="alert">
                <p><?= $form_errors; ?></p>
                <p><?= $registration_errors; ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if(!empty($registration_success)): ?>
    <script type="text/javascript">
        var delay = <?=$delay;?>000;
        setTimeout("document.location.href='<?=base_url();?>'", delay);
    </script>
    <div class="row">
        <div class="col-lg-6 col-lg-offset-3">
            <div class="alert alert-success text-center" role="alert">
                <?= $registration_success; ?>
                <br/>Через <?= $delay; ?> секунд вы будете автоматически перенаправлены <br/>на Главную страницу нашего сайта.
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if(empty($registration_success)): ?>
<?= form_open('registration'); ?>
<?= form_hidden('register', 1); ?>
<div class="row">
    <div class="col-lg-12">
        <h2 class="text-center order-header">Регистрация</h2>
        <h4 class="text-center">нового клиента</h4>
    </div>
</div>
<div class="row checkout-form">
    <div class="col-lg-6 col-lg-offset-3">
        <div class="control-group">
            <div class="row">
                <div class="col-lg-12">
                    <?php if(empty($username)): ?>
                        <div class="tab-content">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group checkout-fg">
                                        <label for="new_email"><span class="red-star">*</span>Email:</label>
                                        <input type="text" name="new_email" class="form-control co-input input-lg" placeholder="email@example.com" value="<?=set_value('new_email'); ?>"/>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group checkout-fg">
                                        <label for="new_password"><span class="red-star">*</span>Пароль:</label>
                                        <input type="password" name="new_password" class="form-control co-input input-lg" placeholder="6-12 символов: a-z0-9_-" value="<?=set_value('new_password'); ?>"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group checkout-fg">
                                        <label for="new_fio"><span class="red-star">*</span>Ф.И.О:</label>
                                        <input type="text" name="new_fio" class="form-control co-input input-lg" placeholder="Фамилия Имя Отчество" value="<?=set_value('new_fio'); ?>"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group checkout-fg">
                                        <label for="new_phone"><span class="red-star">*</span>Номер телефона:</label>
                                        <input type="text" name="new_phone" class="form-control co-input input-lg" placeholder="+380661245147" value="<?=set_value('new_phone'); ?>"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group checkout-fg">
                                        <label for="new_city">Город:</label>
                                        <input type="text" name="new_city" class="form-control co-input input-lg" placeholder="Киев" value="<?=set_value('new_city'); ?>"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group checkout-fg">
                                        <label for="new_address">Адрес:</label>
                                        <input type="text" name="new_address" class="form-control co-input input-lg" placeholder="бул. Дружбы Народов, 24/135" value="<?=set_value('new_address'); ?>"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group checkout-fg">
                                        <label for="new_fb">Facebook:</label>
                                        <input type="text" name="new_fb" class="form-control co-input input-lg" placeholder="https://facebook.com/your_user_id" value="<?=set_value('new_fb'); ?>"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group checkout-fg">
                                        <label for="new_vk">Vkontakte:</label>
                                        <input type="text" name="new_vk" class="form-control co-input input-lg" placeholder="https://vk.com/your_user_id" value="<?=set_value('new_vk'); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row checkout-form">
    <div class="col-lg-6 col-lg-offset-3">
        <div class="row">
            <div class="col-lg-9 col-lg-offset-2">
                <label class="control control--checkbox cc-delivery-method">Получать новости
                    <input type="checkbox" name="register_news" class="" value="1"/>
                    <div class="control__indicator"></div>
                </label>
                <label class="control control--checkbox cc-delivery-method">Получать сообщения
                    <input type="checkbox" name="register_messages" class="" value="1"/>
                    <div class="control__indicator"></div>
                </label>
                <label class="control control--checkbox cc-delivery-method">Уведомлять меня о новых поступлениях
                    <input type="checkbox" name="register_new_products" class="" value="1"/>
                    <div class="control__indicator"></div>
                </label>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h5 class="text-center">Регистрируясь, вы соглашаетесь с условиями <a href="<?=base_url('dogovor-na-okazanie-uslug.html'); ?>">Пользовательской оферты</a></h5>
            </div>
        </div>
    </div>
</div>
<div class="row checkout-form">
    <div class="col-lg-6 col-lg-offset-3 text-center">
        <button type="submit" id="form_checkout" class="btn btn-orange">Регистрация</button>
    </div>
</div>
<?= form_close(); ?>
<?php endif; ?>