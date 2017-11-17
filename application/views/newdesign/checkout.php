<?php
/**
 *
 */
$breadcrumbs = array(
    0 => array(
        'title' => 'Главная',
        'url' => base_url(),
    ),
    1 => array(
        'title' => 'Корзина',
        'url' => base_url('order/cart'),
    ),
    2 => array(
        'title' => 'Оформление заказа',
        'url' => base_url('order/checkout'),
    ),
);
$checkout_form = $this->session->flashdata('checkout_form');
?>
<div class="modal fade" id="coTermsModal" tabindex="-1" role="dialog" aria-labelledby="coTermsModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center" id="coTermsModalLabel">Внимание!</h4>
            </div>
            <div class="modal-body">
                <p class="text-center">Пожалуйста отметьте галочкой пункт "Подтверждая заказ, я принимаю условия <a href="<?=  base_url('dogovor-na-okazanie-uslug.html'); ?>" target="_blank" >пользовательского соглашения</a>"!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Вернуться к оформлению заказа</button>
            </div>
        </div>
    </div>
</div>
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
<?php if(!empty($checkout_error)): ?>
<div class="row">
    <div class="col-lg-6 col-lg-offset-3">
        <div class="alert alert-danger" role="alert"><?= $checkout_error; ?></div>
    </div>
</div>
<?php endif; ?>
<?= form_open('order/checkout_action'); ?>
<div class="row">
    <div class="col-lg-12">
        <h2 class="text-center order-header">Оформление заказа</h2>
    </div>
</div>
<div class="row checkout-form">
    <div class="col-lg-6 col-lg-offset-3">
        <div class="control-group">
            <div class="row">
                <div class="col-lg-12">
                    <?php if(empty($username)): ?>
                    <ul class="nav nav-tabs nav-justified" role="tablist">
                        <li role="presentation" class="active">
                                <label class="control control--radio cc-user-type" data-target="#new_user">я новый покупатель
                                    <input type="radio" name="user_type" class="user-contr" checked="checked" value="0"/>
                                    <div class="control__indicator"></div>
                                </label>
                        </li>
                        <li role="presentation">
                                <label class="control control--radio cc-user-type" data-target="#old_user">я постоянный клиент
                                    <input type="radio" name="user_type" class="user-contr" value="1"/>
                                    <div class="control__indicator"></div>
                                </label>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="new_user">
                        <!--new user form-->
                            <div class="row">
                                <div class="col-lg-12">
                                    <p class="text-center">Введите Ваш e-mail и придумайте пароль для входа на сайт</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group checkout-fg">
                                        <label for="new_email">Email:</label>
                                        <input type="text" name="new_email" class="form-control co-input input-lg" value="<?=element('new_email', $checkout_form, ''); ?>"/>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group checkout-fg">
                                        <label for="new_password">Пароль:</label>
                                        <input type="password" name="new_password" class="form-control co-input input-lg" value="<?=element('new_password', $checkout_form, ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                            <?php /*div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group checkout-fg">
                                        <label for="new_email">Email:</label>
                                        <input type="text" name="new_email" class="form-control co-input input-lg"/>
                                    </div>
                                </div>
                            </div*/?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group checkout-fg">
                                        <label for="new_fio">Ф.И.О:</label>
                                        <input type="text" name="new_fio" class="form-control co-input input-lg" value="<?=element('new_fio', $checkout_form, ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group checkout-fg">
                                        <label for="new_phone">Номер телефона:</label>
                                        <input type="text" name="new_phone" class="form-control co-input input-lg" value="<?=element('new_phone', $checkout_form, ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="old_user">
                        <!--old user form-->
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group checkout-fg">
                                        <label for="user_email">Email:</label>
                                        <input type="text" name="user_email" class="form-control co-input input-lg" value="<?=element('user_email', $checkout_form, ''); ?>"/>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group checkout-fg">
                                        <label for="user_password">Пароль:</label>
                                        <input type="password" name="user_password" class="form-control co-input input-lg" value="<?=element('user_password', $checkout_form, ''); ?>"/>
                                    </div>
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
<div class="row">
    <div class="col-lg-12">
        <h3 class="text-center order-header">Укажите способ доставки и оплаты</h3>
    </div>
</div>
<div class="row checkout-form">
    <div class="col-lg-6 col-lg-offset-3">
        <div class="row">
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-lg-12">
                        <?php
                        $delivery_method = element('delivery_method', $checkout_form, '');
                        ?>
                        <label class="control control--radio cc-delivery-method text-right">Адресная доставка по Украине
                            <input type="radio" name="delivery_method" class="" <?=($delivery_method == 'curier') ? 'checked="checked"' : ''; ?> value="curier" onclick="disable_cof('curier'); hide_countries_list(); show_address(); hide_department();"/>
                            <div class="control__indicator"></div>
                        </label>
                        <?php /*label class="control control--radio cc-delivery-method text-right">Транспортная компания по Украине:
                            <input type="radio" name="delivery_method" class="" <?=($delivery_method == 'company') ? 'checked="checked"' : ''; ?> value="company"/>
                            <div class="control__indicator"></div>
                        </label*/?>
                        <label class="control control--radio cc-delivery-method text-right">На отделение Новой Почты
                            <input type="radio" name="delivery_method" class="" <?=($delivery_method == 'company') ? 'checked="checked"' : ''; ?> value="company" onclick="disable_cof('company'); hide_countries_list(); hide_address(); show_department();"/>
                            <div class="control__indicator"></div>
                        </label>
                        <label class="control control--radio cc-delivery-method text-right">Международная доставка:
                            <input type="radio" name="delivery_method" class="" <?=($delivery_method == 'country') ? 'checked="checked"' : ''; ?> value="country" onclick="disable_cof('country'); show_countries_list(); show_address(); hide_department()"/>
                            <div class="control__indicator"></div>
                        </label>
                    </div>
                </div>
            </div>
            <link rel="stylesheet" href="<?=HTTP_HOST; ?>assets/newdesign/selectric/selectric.css"/>
            <div class="col-lg-6">
                <div class="row co-delivery-block">
                    <div class="col-lg-12">
                        <div class="co-del-select-block">
                            <?php
                            //$company_name = element('company_name', $checkout_form, 'Новая Почта');
                            ?>
                            <?php /*select name="company_name" id="company_name" class="sel-filter">
                                <option value="Новая Почта" <?=($company_name == 'Новая Почта') ? 'selected' : ''; ?>> Новая Почта </option>
                                <option value="Интайм" <?=($company_name == 'Интайм') ? 'selected' : ''; ?>> Интайм </option>
                                <option value="Деливери" <?=($company_name == 'Деливери') ? 'selected' : ''; ?>> Деливери </option>
                                <option value="Автолюкс" <?=($company_name == 'Автолюкс') ? 'selected' : ''; ?>> Автолюкс </option>
                                <option value="Мист Экспресс" <?=($company_name == 'Мист Экспресс') ? 'selected' : ''; ?>> Мист Экспресс </option>
                            </select*/?>
                            <input type="hidden" name="company_name" value="Новая Почта"/>
                        </div>
                        <?php $country_name_class = ($delivery_method == 'country') ? ' mvisible' : ' minvisible'; ?>
                        <div class="co-del-select-block<?=$country_name_class; ?>" id="country_name_block" style="margin-top: 40px; margin-bottom: 0;">
                            <select name="country_name" id="country_name" class="sel-filter">
                                <?php
                                $country_name = element('country_name', $checkout_form, 'Россия');
                                $countries = array(
                                    'Россия', 'Узбекистан', 'Казахстан', 'Киргизия', 'Армения', 'Азербайджан', 'Таджикистан', 'Грузия', 'Молдова', 'Белорусия', 'Эстония', 'Туркменистан', 'Латвия',
                                    'Австралия', 'Австрия', 'Албания', 'Англия', 'Андора', 'Аргентина', 'Бангладеш', 'Бахрэйн', 'Бельгия', 'Болгария', 'Босния', 'Ботсвана', 'Бразилия', 'Буркино-Фасо',
                                    'Ватикан', 'Венгрия', 'Вьетнам', 'Габон', 'Гайана', 'Гана', 'Гвинея', 'Германия', 'Голандия', 'Греция', 'Дания', 'Джибути', 'Египет', 'Израиль', 'Индия', 'Индонезия', 'Иордания', 'Иран', 'Ирландия', 'Исландия', 'Испания', 'Италия',
                                    'Каймановы острова', 'Камбоджа', 'Канада', 'Катар', 'Кения', 'Кипр', 'Колумбия', 'Конго', 'Корея', 'Кот-д Ивуар', 'Кувейт', 'Лаос', 'Ливан', 'Литва', 'Лихтенштейн', 'Люксембург',
                                    'Македония', 'Малайзия', 'Мальта', 'Марокко', 'Мексика', 'Монако', 'Монголия', 'Непал', 'Новая Гвинея', 'Новая Зеландия', 'Норвегия', 'ОАЭ', 'Оман',
                                    'Пакистан', 'Панама', 'Перу', 'Польша', 'Португалия', 'Руанде', 'Румыния', 'Сан-Марино', 'Саудовская Аравия', 'Северная Корея', 'Сенегал', 'Сербия', 'Сингапур', 'Сирия', 'Словакия', 'Словения', 'США',
                                    'Тайланд', 'Тунис', 'Турция', 'Уганда', 'Филиппины', 'Финляндия', 'Франция', 'Хорватия', 'Чад', 'Чехия', 'Швейцария', 'Швеция', 'Шри-Ланка', 'Эфиопия', 'Япония');
                                foreach ($countries as $country){
                                    echo '<option value="' . $country . '" ' . (($country_name == $country) ? 'selected' : '') . '> ' . $country . ' </option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row checkout-form">
    <div class="col-lg-6 col-lg-offset-3">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group checkout-fg">
                    <label for="delivery_city">Город:</label>
                    <input type="text" id="delivery_city" name="delivery_city" class="form-control co-input input-lg delivery-info" value="<?=element('delivery_city', $checkout_form, ''); ?>"/>
                </div>
            </div>
            <?php $address_block_class = ($delivery_method == 'company') ? ' minvisible' : ' mvisible'; ?>
            <div class="col-lg-12<?=$address_block_class; ?>" id="address_block">
                <div class="form-group checkout-fg">
                    <label for="delivery_address">Адрес:</label>
                    <input type="text" id="delivery_address" name="delivery_address" class="form-control co-input input-lg delivery-info" <?=(($delivery_method == 'company')) ? 'disabled' : ''; ?> value="<?=element('delivery_address', $checkout_form, ''); ?>"/>
                </div>
            </div>
            <?php $department_block_class = ($delivery_method == 'company') ? ' minvisible' : ' mvisible'; ?>
            <div class="col-lg-12<?=$department_block_class; ?>" id="department_block">
                <div class="form-group checkout-fg">
                    <label for="delivery_department">Отделение №:</label>
                    <input type="text" id="delivery_department" name="delivery_department" <?=(($delivery_method == 'curier') || ($delivery_method == 'country')) ? 'disabled' : ''; ?> class="form-control co-input input-lg delivery-info" value="<?=element('delivery_department', $checkout_form, ''); ?>"/>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="form-group checkout-fg">
                    <label for="notes">Комментарий к заказу:</label>
                    <textarea name="notes" class="form-control co-input input-lg"><?=element('notes', $checkout_form, ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row checkout-form">
    <div class="col-lg-6 col-lg-offset-3">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <div class="row">
                    <?php $type_payment = element('type_payment', $checkout_form, 'cash'); ?>
                    <div class="col-lg-6">
                        <label class="control control--radio cc-delivery-method">Наличные
                            <input type="radio" name="type_payment" class="" <?=($type_payment == 'cash') ? 'checked="checked"' : ''; ?> value="cash"/>
                            <div class="control__indicator"></div>
                        </label>
                    </div>
                    <div class="col-lg-6">
                        <label class="control control--radio cc-delivery-method">Visa/Mastercard
                            <input type="radio" name="type_payment" class="" <?=($type_payment == 'card') ? 'checked="checked"' : ''; ?> value="card"/>
                            <div class="control__indicator"></div>
                        </label>
                    </div>
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
                <label class="control control--checkbox cc-delivery-method">Подтверждая заказ, я принимаю условия <a href="<?=  base_url('dogovor-na-okazanie-uslug.html'); ?>" target="_blank" >пользовательского соглашения</a>
                    <input type="checkbox" name="register_terms" value="1"/>
                    <div class="control__indicator"></div>
                </label>
            </div>
        </div>
    </div>
</div>
<div class="row checkout-form">
    <div class="col-lg-6 col-lg-offset-3 text-center">
        <button type="submit" id="form_checkout" class="btn btn-orange order-checkout-btn">Заказ подтверждаю</button>
    </div>
</div>
<?= form_close(); ?>

<script type="text/javascript">
function disable_cof(d_method){
    $('.delivery-info').removeAttr('disabled');
    if(d_method == 'curier'){
        $('input[name=delivery_department]').attr('disabled', 'disabled');
    }
    else if(d_method == 'company'){
        $('input[name=delivery_address]').attr('disabled', 'disabled');
    }
    else if(d_method == 'country'){
        $('input[name=delivery_department]').attr('disabled', 'disabled');
    }
}

function  hide_countries_list(){
    $('#country_name_block').hide('slow');
}

function  show_countries_list(){
    $('#country_name_block').show('slow');
}

function hide_address(){
    $('#address_block').hide('slow');
}

function show_address(){
    $('#address_block').show('slow');
}

function hide_department(){
    $('#department_block').hide('slow');
}

function show_department(){
    $('#department_block').show('slow');
}
</script>