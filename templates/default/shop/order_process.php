<?php
// логинимся
  $checked_new_radio ="checked";
                $checked_old_radio="";
 if(isset($_POST)){    
 if (!empty($_POST['login']) && !empty($_POST['password'])) { // значит мы пробуем логиниться
            $log = $this->ci->ion_auth->login($this->input->post("login"), $this->input->post("password"), true);
            if ($log) { // если залогинились
               $user_data_obj = $this->ci->ion_auth_model->row(); // данные пользователя

                $user_data['register_login'] = $user_data_obj->username;
                $user_data['register_name'] = $user_data_obj->email;
                $user_data['register_password'] = $user_data_obj->password;
                $user_data['register_first_name'] = $user_data_obj->first_name;
                $user_data['register_phone'] = $user_data_obj->phone;
                $user_data['register_city'] = $user_data_obj->city;
                $user_data['register_address'] = $user_data_obj->adress;
                $checked_new_radio ="checked";
                $checked_old_radio="";
                
            } else {
                $errors_login = $this->ci->ion_auth_model->set_error_delimiters('', '<br>');
                $errors_login = $this->ci->ion_auth_model->errors();
                $checked_new_radio ="";
                $checked_old_radio="checked";
                          //   print $errors . "<br>"; // покажем людям ошибку
            }
        }
}
//посмотрим залогинены ли мы?
$username = $this->session->userdata['username'];
$user_id = $this->session->userdata['user_id'];
if (!empty($username)) {
    $syle_login_form = "style='display:none'";

    $query = $this->db->select('*')
            ->where('id', $user_id)
            ->limit(1)
            ->get('users');

    $user_data_obj = $query->row();


    //       $log = $this->ci->ion_auth->login($this->input->post("login"), $this->input->post("password"), true);
    // $user_data_obj = $_ci_CI->ion_auth_model->row(); // данные пользователя
    $user_data['register_login'] = $user_data_obj->username;
    $user_data['register_name'] = $user_data_obj->email;
    $user_data['register_password'] = $user_data_obj->password;
    $user_data['register_first_name'] = $user_data_obj->first_name;
    $user_data['register_phone'] = $user_data_obj->phone;
    $user_data['register_city'] = $user_data_obj->city;
    $user_data['register_address'] = $user_data_obj->adress;
} else {
    $syle_login_form = "";
}



if (method_exists($this->ci->module, "cart")) {
    $this->ci->module->{"cart"}(FALSE);
} else {
    die("Module method not found: " . "./modules/" . $module_name . "/" . $module_name . ".php : class " . $module_class_name . "->" . $action_name . "()");
}
$cart = $this->module->d[cart];

$cart_currency = 'usd';
$e_rates_res = $this->ci->db->select('var_name, value')->get('e_rates')->result_array();
$e_rates = array();
if(!empty($e_rates_res)){
    foreach ($e_rates_res as $e_rate){
        $e_rates[$e_rate['var_name']] = $e_rate['value'];
    }
}


if (!isset($cart->products) || sizeof((array) $cart->products) < 1) {
    ?><div class="content1Struct1">
        <div class="cartW">
            <div class="cartI">
                <div class="bascet">
                    <h1>Ваша корзина</h1>
                    <center><strong>Ваша корзина пуста.</strong></center>
                </div>
            </div>
        </div>
    </div>
    </div><?php
} else {
    if ($this->input->post("order_step2") === false || sizeof($order_errors) > 0) {
        ?>
        <script src="/modules/shop/media/js/shop.main.js"></script>
        <div class="content1Struct1">
            <div class="cartW">
                <div class="cartI">
                    <div class="bascet">
                        <h1>Оформление заказа</h1>

                        <form method="post" id="products_form">
                            <table class="products" cellspacing="1" cellpadding="0" border="0" align="center" width="100%">
                                <tr><td colspan="4" class="line"><br /></td></tr>
                                <tr><td colspan="4"><br /></td></tr>
                                <?php
                                $pric = 0;
                                foreach ($cart->products AS $idr => $r) {
                                    $r->price_total = ($r->currency == $cart_currency) ? $r->price_total : ceil($r->price_total * $e_rates[$r->currency . '_usd']);
                                    $pric = $pric + $r->price_total;

                                    if ($r->show != 1) {
                                        $r->quantity = 0;
                                    }
                                    ?><tr class="productRowP">
                                        <td class="imgP">
                                            <a href="<?php print $r->link; ?>">
                                                <?php
                                                if (empty($r->color)) {
                                                    if (empty($r->main_picture_file_name)) {
                                                        ?><img src="/assets/media/nophoto.png" /><?php
                                                    } else {
                                                        ?><img src="/uploads/shop/products/thumbs3/<?php print $r->main_picture_file_name; ?>" /><?php
                                                    }
                                                } else {
                                                    $img = explode(':', $idr);
                                                    $img = '/uploads/shop/products/thumbs3/' . $img[1];
                                                    ?><img src="<?php print $img; ?>" /><?
                                                    }
                                                    ?>
                                                </a>
                                            </td>
                                            <td class="titleP">
                                                <a href="<?php print $r->link; ?>"><?php print $r->title; ?></a>
                                                <?php
                                                if ($r->category_ids != "") {
                                                    $cats = explode(",", $r->category_ids);
                                                    foreach ($cats as $c) {
                                                        $res = $this->db->query("SELECT `parent_id`,`title` FROM `categoryes` WHERE `id`=" . $c . " ")->result();
                                                        if ($res[0]->parent_id > 0) {
                                                            print '<br /><span class="catP">' . $res[0]->title . '</span>';
                                                        }
                                                    }
                                                }
                                                if (!empty($r->size)) {
                                                    $size = explode(':', $idr);
                                                    $size = $size[2];
                                                    print "<br />Размер: " . $size;
                                                }

                                                $idr_quanity = str_replace('.', '-', $idr);
                                                ?>
                                            </td>
                                            <td align="center">
                                                <input type="text" name="quantity[<?php print $idr_quanity; ?>]" value="<?php print $r->quantity; ?>" class="quantity" disabled="disabled" />
                                                <?php if ($r->show != 1) { ?>
                                                    <input type="hidden" name="quantity[<?php print $r->id; ?>]" value="<?php print $r->quantity; ?>" />
                                                <?php } ?>
                                                <?php if ($r->show != 1) { ?>
                                            <center><small style="color:red;">этого товара нет в наличии!</small></center>
                                        <?php } ?>

                                        <div class="clear"><!-- --></div>
                                        </td>
                                        <td class="priceP"><?php print $r->price_total; ?> $</td>
                                        </tr>
                                        <tr><td colspan="4" class="line"><br /></td></tr>
                                        <tr><td colspan="4"><br /></td></tr>
                                        <?php
                                    }
                                    ?>
                                    <tr>
                                        <td colspan="1">
                                            <div class="centerFormRow centerFormRowButtons">
                                                <a class="backProcess" href="/cart.html">Редактировать заказ</a>
                                            </div>
                                        </td>
                                        <td colspan="3">
                                            <div class="total_amout" data-products-total="<?php print $discount['price']; ?>" data-delivery="<?php print $discount['delivery']; ?>" data-discount="<?php print $discount['difference']; ?>">

                                                <?php
                                                if ($discount['difference'] > 0) {
                                                    ?>
                                                    <strong>Общая стоимость со скидкой: </strong> <span class="products-total"><?php print $discount['price_hmn']; ?></span>
                                                    <br /><br />
                                                    <?php
                                                    if ($delivery_method !== 0) {
                                                        ?>
                                                        <div class="deliveryTotalW">
                                                            <strong>Доставка: </strong> <span class="delivery"><?php print $discount['delivery'] == 0 ? 'бесплатно (по киеву)' : $discount['delivery_hmn']; ?></span>
                                                            <br /><br />
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>
                                                    <strong>Итого: </strong> <span class="total"><?php print $discount['price_total_hmn']; ?></span>
                                                    <?php
                                                } else {
                                                    if ($delivery_method !== 0) {
                                                        ?>
                                                                                                                                                <!-- <strong>Общая стоимость: </strong> <span class="products-total"><?php print $discount['price_hmn']; ?></span> -->
                                                        <br />
                                                        <!-- <div class="deliveryTotalW">
                                                                <strong>Доставка: </strong> <span class="delivery"><?php print $discount['delivery'] == 0 ? 'бесплатно (по киеву)' : $discount['delivery_hmn']; ?></span>
                                                                <br /><br />
                                                        </div> -->
                                                        <?php
                                                    }
                                                    $discount['price_total_hmn'] = $pric . ' $.';
                                                    ?>
                                                    <p class="totalP">ОБЩАЯ СУММА <span class="total"><?php print $discount['price_total_hmn']; ?></span></p>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
//}


    
    ?>
    <style>

        .type_user li{
            display:inline;
        }   

        .type_user {

            position: center;
            font-size: 20px;

            margin-left: 20px;
            width: 50%;
            float: left;
        }
        .type_user .regSub, .bottom_login1 .regSub {
            margin-bottom:10px;

        }
        .type_user #registerBlock input {
            width:250px;
            height:30px;
            margin: auto;
        }

        .type_user #registerBlock{
            margin-left: 0px;
            margin-top: 0px;
        }

        .shipping_and_payment{

            position: center;
            font-size: 20px;
            width: 48%;
            float: left;
        }
        .shipping_and_payment input[type="text"]{
            margin: auto;
            width:250px;
            height: 30px;
            margin-bottom: 10px;
        }
        .shipping_and_payment ul{
            padding: 0px;
            margin: 0px;
            list-style: none;
            text-align: left;

        }   
        .shipping h2, .type_user h2{
            font-size: 30px;
        }


        #registerForm label {
            width: 250px;
        }


        .shipping_and_payment label, .new_user label {
            display: inline-block;
            width: 150px;
            font-size: 20px;
            text-align: left;
        }
	#registerBlock .error, .shipping_and_payment .error, .login_form .error {
            width: 320px;
            font-family: sans-serif;
            font-size: 18px;
            color: red;
            margin-left: 155px;
        }




	
		

        #registerForm label.error, #registerForm input.submit {
            margin-left: 253px;
        }

        .address label, .address_company label{
            font-size:18px;
        }

        .bottom_login1 {
            float: left;
            display: block;
            width: 400px;
            margin: 30px auto 50px 360px;
        }
        #error_messages{
            border:1px red solid;
        }
        
        .type_user h2{
            padding-left: 75px;
        }
        
        
        
 
        
    </style>
    <script>





        $(document).ready(function () {
            
            
       var type_user =   $("input[name=type_user]:checked").val();
           
            
            if(type_user=='new'){
             
            $(".login_form").hide();
            $(".new_user").show();
        }else{
        
            $(".login_form").show();
            $(".new_user").hide();
      
            }
            
            
		
		
            $('.type_shipping').change(function () {
                if ($(this).val() == 'company') {
                    $('.address_company').show();
                    $('.address').hide();
                } else {
                    $('.address_company').hide();
                    $('.address').show();
                }
            }
            );


            $("#send_zakaz").validate({
                rules: {
                    register_login: {
                        required: true,
                        minlength: 4
                    },
                    register_password: {
                        required: true,
                        minlength: 4
                    },
                    register_name: {
                        required: true,
                        email: true
                    },
                    register_first_name: {
                        required: true,
                        minlength: 4
                    },
                    register_phone: {
                        required: true,
                        minlength: 10
                    },
                    login: {
                        required: true,
                        minlength: 4
                    },
                    password: {
                        required: true,
                        minlength: 4
                    },
                    city: {
                        required: true,
                    },
                    delivery_address: {
                        required: true,
                    },
                    delivery_department: {
                        required: true,
                    },
                    register_terms: {
                        required: true,
                    },
                    agree: "required"
                },
                messages: {
                    register_login: {
                        required: "Пожалуйста введите Ваш логин",
                        minlength: "Логин должен быть минимум 4 символа"
                    },
                    register_password: {
                        required: "Пожалуйста введите пароль",
                        minlength: "Пароль должен быть минимум 4 символа"
                    },
                    login: {
                        required: "Пожалуйста введите Ваш логин",
                        minlength: "Логин должен быть минимум 4 символа"
                    },
                    password: {
                        required: "Пожалуйста введите пароль",
                        minlength: "Пароль должен быть минимум 4 символа"
                    },
                    city: {
                        required: "Пожалуйста введите Ваш город",
                    },
                    delivery_address: {
                        required: "Пожалуйста введите адрес доставки",
                    },
                    delivery_department: {
                        required: "Пожалуйста укажите номер отделения",
                    },
                    register_terms: {
                        required: "",
                    },
                    register_name: "Пожалуйста введите корректный адрес электронной почты",
                    register_first_name: "Пожалуйста введите ваше имя и фамилию",
                    register_phone: "Пожалуйста введите номер телефона"
                }
            });




        });
        function new_user() {
            $(".login_form").hide();
            $(".new_user").show();
        }
        function show_login() {
            $(".login_form").show();
            $(".new_user").hide();
        }
/*
        function check_username_and_mail() // регшится новый
        {
            $.post("/rams/validate.php", {validate: "validate", username: $("#register_login").val(), email: $("#register_name").val()})
                    .done(function (data) {
                        obj = JSON.parse(data);
                        $("#error_messages").html("");
                        var error = false;

                        if (obj['username']) {
                            $("#error_messages").append("Имя пользователя занято");
                            $("#error_messages").show();
                            error = true;

                        }

                        if (obj['email']) {
                            //   alert('email уже занят');
                            $("#error_messages").append("email уже занят");
                            $("#error_messages").show();
                            error = true;

                        }
                    });
        }
    */    
        function send_login_form() // регшится новый
        {
		var login =$('#login').val();
		var password = $('#password').val();
												$('#login_form').html('');
												$('#login_form').append('<input type=\'hidden\' name=\'login\' value=\''+login+'\'>');
												$('#login_form').append('<input type=\'hidden\' name=\'password\' value=\''+password+'\'>');
												$('#login_form').submit();
		
        }

        function send_zakaz_prev() {
            var form = $("#send_zakaz");
            var form_login = $("#login_form");
            
            // проверка принятия условий Пользовательского соглашения
            var terms = $('#register_terms').is(':checked');
//            console.log(terms);
            if(terms === false){
                $("#termsModal").modal();
                $('#termsDiv label').css('color', 'red');
            }
            else{
                $('#termsDiv label').css('color', '#000');
            }

            form.validate();

            if (form.valid()) {
         //       alert('form valid');
                if ($('#disabled').val() != 'disabled') {

                    $.post("/rams/validate.php", {validate: "validate", username: $("#register_login").val(), email: $("#register_name").val()})
                            .done(function (data) {
                                obj = JSON.parse(data);
                                $("#error_messages").html("");
                                var error = false;

                                if (obj['username']) {
                                    $("#error_messages").append("Имя пользователя занято");
                                    $("#error_messages").show();
                                    error = true;

                                }

                                if (obj['email']) {
                                    //   alert('email уже занят');
                                    $("#error_messages").append("email уже занят");
                                    $("#error_messages").show();
                                    error = true;

                                }

                                if (!error) {
            //                        alert('usernamevalid');

           //                         alert('ok'); // формы валидные можно идти дальше
                                    $("#send_zakaz").submit();
                                }
                            });
                } else {
             //       alert('ok_disabled'); // формы валидные можно идти дальше
                    $("#send_zakaz").submit();
                }
            }
        }







    </script>
	  <form method="post" id="login_form"> 
                
                 </form> 
	
	
    <form method="POST" id="send_zakaz" action="cart.html">
        <div class="type_user">

            <h2 class="splLink" >Контактные данные</h2>
            <div class="select_type" <?php echo $syle_login_form; ?> >
                <ul>
                    <li class="" name="new_user">
                        
                        <input type="radio" name="type_user" id="type_user_new" onclick="new_user();" value='new' <?php echo $checked_new_radio ?> ><label for="type_user_new">Я новый покупатель</label>
                    </li>

                    <li class="" name="member_user">
                        <input type="radio" name="type_user" id="type_user_old" onclick="show_login();" value='old' <?php echo $checked_old_radio ?> ><label for="type_user_old">Я постоянный клиент</label>
                    </li>
                </ul>
            </div>
            <div class="user_data splCont"  >
                <div class="new_user" >
                    <div id="registerBlock">
                        <div id="error_messages" style="display:none">  </div>
                        <!--         <form method="post" id="registerForm"> -->
    <?php
    if (!empty($user_data)) {
        $disabled = "disabled";
    } else {
        $disabled = "";
        $user_data['register_login'] = '';
        $user_data['register_name'] = '';
        $user_data['register_password'];
        $user_data['register_first_name'] = '';
        $user_data['register_phone'] = '';
        $user_data['register_city'] = '';
        $user_data['register_address'] = '';
    }
    ?>
                        <input type="hidden" id="disabled"  <?php echo 'value="' . $disabled . '"'; ?>/>

                        <p><label for="register_login">Логин:<sup>*</sup></label><input type="text" name="register_login" id="register_login" <?php echo 'value="' . $user_data['register_login'] . '" ' . $disabled; ?> required/></p>
                        <p><label for="register_name">E-mail:<sup>*</sup></label><input type="text" name="register_name" id="register_name" <?php echo 'value="' . $user_data['register_name'] . '" ' . $disabled; ?> required/></p>
                        <p><label for="register_password">Пароль:<sup>*</sup></label><input type="password" name="register_password" id="register_password" <?php echo 'value="' . $user_data['register_password'] . '" ' . $disabled; ?> required/></p>
                        <p><label for="register_first_name">Имя и фамилия:<sup>*</sup></label><input type="text" name="register_first_name" id="register_first_name" <?php echo 'value="' . $user_data['register_first_name'] . '" ' . $disabled; ?> required/></p>
                        <p><label for="register_phone">Мобильный телефон:<sup>*</sup></label><input type="text" name="register_phone" id="register_phone" <?php echo 'value="' . $user_data['register_phone'] . '" ' . $disabled; ?> required/></p>
                        <!--        <br />
    <?php /*
      if ($disabled <> "disabled") {
      echo'<a  class="regSub" onclick="send_registerBlock();">Продолжить</a>';
      }
     */ ?>
              </form> -->

                </div>          
            </div>
                <?php
                if(empty($errors_login)){
                ?>
            <div class ="login_form" style=" display:none;">
                <?php }else{?>
                    
             <div class ="login_form" >    
                 <div class='error'><?php echo $errors_login; ?> </div>
           <?php     }?>
                
                <div id="registerBlock">
                    <!--    <form method="post" id="login_form"> -->
                    <p><label for="login">Логин:<sup>*</sup></label><input type="text" name="login" id="login" required/></p>
                    <p><label for="password">Password:<sup>*</sup></label><input type="password" name="password" id="password" required/></p>
                    <a href="#" class="regSub" onclick="send_login_form();">Войти</a>
                    <!--    </form> -->
                </div>
            </div>     
                    
        </div>


    </div>

    <div class="shipping_and_payment" >
        <div class="shipping"> 
            <h2 class="splLink"> Выберите способ доставки и оплаты</h2>
            <!--     <form id="shipping_and_payment"> -->
            <div class="splCont">
                <ul>
                    <li style="margin-bottom: 15px;"><input class="type_shipping" type="radio" name="delivery_method" id="del_method_courier" value="curier" checked  onclick="changeFields(this, 'curier');"><label for="del_method_courier">Курьер по Киеву</label>
                    </li>
                    <li><input class="type_shipping"  type="radio" name="delivery_method" id="del_method_company" value="company" onclick="changeFields(this, 'company');"><label for="del_method_company"><span class="to-ukr">Транспортная компания<br/>по Украине:</span></label>
                        <select name="company_name" style="margin: -55px 0 10px 235px;">
                            <option value="Новая Почта"> Новая Почта </option>
                            <option value="Интайм"> Интайм </option>
                            <option value="Деливери"> Деливери </option>
                            <option value="Автолюкс"> Автолюкс </option>
                            <option value="Мист Экспресс"> Мист Экспресс </option>
                        </select>
                    </li>
                    <li><input class="type_shipping"  type="radio" name="delivery_method" id="del_method_country" value="country"  onclick="changeFields(this, 'country');"><label for="del_method_country">Международная доставка:</label>
                        <select name="country_name">
                        <?php
                        $countries = array(
                            'Россия', 'Узбекистан', 'Казахстан', 'Киргизия', 'Армения', 'Азербайджан', 'Таджикистан', 'Грузия', 'Молдова', 'Белорусия', 'Эстония', 'Туркменистан', 'Латвия', 
                            'Австралия', 'Австрия', 'Албания', 'Англия', 'Андора', 'Аргентина', 'Бангладеш', 'Бахрэйн', 'Бельгия', 'Болгария', 'Босния', 'Ботсвана', 'Бразилия', 'Буркино-Фасо', 
                            'Ватикан', 'Венгрия', 'Вьетнам', 'Габон', 'Гайана', 'Гана', 'Гвинея', 'Германия', 'Голандия', 'Греция', 'Дания', 'Джибути', 'Египет', 'Израиль', 'Индия', 'Индонезия', 'Иордания', 'Иран', 'Ирландия', 'Исландия', 'Испания', 'Италия', 
                            'Каймановы острова', 'Камбоджа', 'Канада', 'Катар', 'Кения', 'Кипр', 'Колумбия', 'Конго', 'Корея', 'Кот-д Ивуар', 'Кувейт', 'Лаос', 'Ливан', 'Литва', 'Лихтенштейн', 'Люксембург', 
                            'Македония', 'Малайзия', 'Мальта', 'Марокко', 'Мексика', 'Монако', 'Монголия', 'Непал', 'Новая Гвинея', 'Новая Зеландия', 'Норвегия', 'ОАЭ', 'Оман', 
                            'Пакистан', 'Панама', 'Перу', 'Польша', 'Португалия', 'Руанде', 'Румыния', 'Сан-Марино', 'Саудовская Аравия', 'Северная Корея', 'Сенегал', 'Сербия', 'Сингапур', 'Сирия', 'Словакия', 'Словения', 'США', 
                            'Тайланд', 'Тунис', 'Турция', 'Уганда', 'Филиппины', 'Финляндия', 'Франция', 'Хорватия', 'Чад', 'Чехия', 'Швейцария', 'Швеция', 'Шри-Ланка', 'Эфиопия', 'Япония');
                        foreach ($countries as $country){
                            echo '<option value="' . $country . '"> ' . $country . ' </option>';
                        }
                        ?>
                        </select>
                    </li>
                </ul>
            </div>
            <script type="text/javascript">
            function changeFields(obj, type){
                var status = $(obj).is(':checked');
                if(status === true){
                    if(type === 'curier'){
                        // курьер по Киеву – не нужны поля Город и Отделение №
                        $('#f_city').removeAttr('required').attr('disabled', 'disabled');
                        $('#f_department').removeAttr('required').attr('disabled', 'disabled');
                        // возвращаем required полю Адрес
                        $('#f_address').removeAttr('disabled').attr('required', 'required');
                        $('input[name=delivery_address]').rules('add', {required: true}); // добавляем правило
                    }
                    else if(type === 'company'){
                        // доставка по Украине – поле Адрес активно – но не обязательно
                        $('#f_address').removeAttr('required');
                        $('input[name=delivery_address]').rules('remove', 'required'); // удаляем правило
                        // возвращаем required полям Город и Отделение №
                        $('#f_city').removeAttr('disabled').attr('required', 'required');
                        $('#f_department').removeAttr('disabled').attr('required', 'required');
                    }
                    else if(type === 'country'){
                        // международная доставка – не нужно поле Отделение №
                        $('#f_department').removeAttr('required').attr('disabled', 'disabled');
                        // возвращаем required полям Город и Адрес
                        $('#f_city').removeAttr('disabled').attr('required', 'required');
                        $('#f_address').removeAttr('disabled').attr('required', 'required');
                        $('input[name=delivery_address]').rules('add', {required: true}); // добавляем правило
                    }
                }
            }
            </script>
            <p><label>Город </label> <input type="text" id="f_city" name="city" disabled></p>
            <p><label>Адрес</label> <input type="text" id="f_address" name="delivery_address"  required></p>
            <p><label>Отделение №</label> <input type="text" id="f_department" name="delivery_department" disabled></p>
        </div>
        <div class="payments"> 
            <div class="splCont">
                <ul>
                    <li><input type="radio" name="type_payment" id="type_payment_cash" checked><label for="type_payment_cash">Наличные</label></li>
                    <li><input type="radio" name="type_payment" id="type_payment_card" ><label for="type_payment_card">Visa/Mastercard</label></li>
                   <!-- <div class="card_to_pay"> Переведите деньги на карту номер 1234567891234</div>-->

                </ul>
            </div>
        </div>

    </div>
            
    <div class="clear"></div>
    
    <div class="bottom_login1"  >
        <div class="checkboxes" >
            <div class="chb"><input type="checkbox" name="register_news" id="register_news" value="1" /><label for="register_news"><span class="subNews">Получать новости</span></label></div>
            <div class="chb"><input type="checkbox" name="register_messages" id="register_messages" value="1" /><label for="register_messages"><span class="subNews">Получать сообщения</span></label></div>
            <div class="chb"><input type="checkbox" name="register_new_products" id="register_new_products" value="1" /><label for="register_new_products"><span class="subNews">Уведомлять меня о новых поступлениях</span></label></div>
            <div class="chb" id="termsDiv"><input type="checkbox" name="register_terms" id="register_terms" value="1" /><label for="register_terms"><span class="subNews">Подтверждая заказ, я принимаю условия <a href="<?=  base_url('dogovor-na-okazanie-uslug.html'); ?>" target="_blank" >пользовательского соглашения</a></span></label></div>
        </div>             
        <input type="hidden" name="order_step2" value="rams_form">
        <a  class="regSub" onclick="send_zakaz_prev();">Заказ подтверждаю</a> 
        <input type="hidden" name="notes" value="<?php       echo $_POST['notes'] ;  ?>">
    </div>
</form>	   
<div class="clear"></div>
<!-- модальное окно подтверждения принятия условий пользовательского соглашения -->
<div id="termsModal" class="modal hide fade">
        <div class="modal-header" style="background-color: #f9a0e3; border-top-left-radius: 6px; border-top-right-radius: 6px; border-color: #880568;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h1 style="text-align: center;">Внимание!</h1>
        </div>
        <div class="modal-body">
                <h2 style="text-align: center;">Пожалуйста отметьте галочкой пункт "Подтверждая заказ, я принимаю условия <a href="<?=  base_url('dogovor-na-okazanie-uslug.html'); ?>" target="_blank" >пользовательского соглашения</a>"!</h2>
        </div>
        <div class="modal-footer"></div>
</div>
<!-- /модальное окно -->