<!-- FOOTER -->
<div class="row">
    <div class="col-lg-4 col-md-4 col-sm-4 new-footer">
        <h5>ИНФОРМАЦИЯ</h5>
        <?php
        foreach ($this->footer_menu['info'] as $item) {
        ?>
        <a href="<?=base_url($item['url']); ?>"><?= $item['title']; ?></a><br/>
        <?php
        }
        ?>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-4 new-footer">
        <div class="nf-vertical"></div>
        <h5>МОЙ АККАУНТ</h5>
        <?php
        foreach ($this->footer_menu['account'] as $item) {
        ?>
        <a href="<?=base_url($item['url']); ?>"><?= $item['title']; ?></a><br/>
        <?php
        }
        ?>
    </div>
    <?php /*div class="col-lg-3 new-footer">
        <div class="nf-vertical"></div>
        <h5>МЫ В СЕТИ</h5>
        <?php
        foreach ($this->footer_menu['online'] as $item) {
        ?>
        <a href="<?=$item['url']; ?>"><?= $item['title']; ?></a><br/>
        <?php
        }
        ?>
    </div*/?>
    <div class="col-lg-4 col-md-4 col-sm-4 new-footer">
        <div class="nf-vertical"></div>
        <h5>КОНТАКТЫ</h5>
        <div class="row">
            <div class="col-lg-2 col-sm-2 col-xs-2">
                <img class="b-dblock" src="<?=HTTP_HOST; ?>assets/newdesign/images/phone_bottom.png" alt="Phone bottom icon">
                <img class="b-dblock" src="<?=HTTP_HOST; ?>assets/newdesign/images/viber.png" alt="Phone bottom icon">
            </div>
            <div class="col-lg-10 col-sm-10 col-xs-10 bc-info">(097) 003-22-33<br/>(050) 43-43-123</div>
        </div>
        <div class="row b-mail">
            <div class="col-lg-2 col-sm-2 col-xs-2">
                <img src="<?=HTTP_HOST; ?>assets/newdesign/images/mail_bottom.png"  alt="Mail bottom icon">
            </div>
            <div class="col-lg-10 col-sm-10 col-xs-10 bc-info">sale@nosieto.com.ua</div>
        </div>
        <div class="row b-address">
            <div class="col-lg-2 col-sm-2 col-xs-2">
                <a href="https://goo.gl/maps/LVDd15Wkz7G2" target="blank">
                    <img class="gmaps-bottom-icon" src="<?=HTTP_HOST; ?>assets/newdesign/images/googlemaps.png" alt="Google Maps bottom icon">
                </a>
            </div>
            <div class="col-lg-10 col-sm-10 col-xs-10 bc-info">
                <span class="b-nobr">Киев, ул. Борщаговская 206,</span> <span class="b-nobr">II этаж, офис 186</span><br>
                <span class="b-nobr">Пн-Пт: 9:30 – 18:30,</span> <span class="b-nobr">Сб: 10:00 – 17:00</span>
            </div>
        </div>
        <div class="row b-mail">
            <div class="col-lg-2 col-sm-2 col-xs-2 b-social">
                <a href="http://google.com/+NosietoUa-modnaya-odezhda" target="_blank"><img src="<?=HTTP_HOST; ?>assets/newdesign/images/b_google.png" alt="Google+"></a>
            </div>
            <?php /*div class="col-lg-2 col-sm-2 col-xs-2 b-social">
                <a href="#"><img src="<?=HTTP_HOST; ?>assets/newdesign/images/b_viber.png" alt="Viber"></a>
            </div>
            <div class="col-lg-2 col-sm-2 col-xs-2 b-social">
                <a href="#"><img src="<?=HTTP_HOST; ?>assets/newdesign/images/b_skype.png" alt="Skype"></a>
            </div*/?>
            <div class="col-lg-2 col-sm-2 col-xs-2 b-social">
                <a href="https://vk.com/nosieto" target="_blank"><img src="<?=HTTP_HOST; ?>assets/newdesign/images/b_vk.png" alt="VKontakte"></a>
            </div>
            <div class="col-lg-2 col-sm-2 col-xs-2 b-social">
                <a href="https://www.facebook.com/nosieto" target="_blank"><img src="<?=HTTP_HOST; ?>assets/newdesign/images/b_facebook.png" alt="Facebook"></a>
            </div>
            <div class="col-lg-2 col-sm-2 col-xs-2 b-social">
                <a href="http://www.instagram.com/nosi_eto" target="_blank"><img src="<?=HTTP_HOST; ?>assets/newdesign/images/b_instagram.png" alt="Instagram"></a>
            </div>
        </div>
    </div>
</div>
</div>
</div><!-- /container-fluid -->
<!-- /FOOTER -->
<!-- Bootstrap -->
<link href="<?=HTTP_HOST; ?>assets/newdesign/bootstrap337/css/bootstrap.min.css" rel="stylesheet">
<link href="<?=HTTP_HOST; ?>assets/newdesign/bootstrap337/css/bootstrap-theme.min.css" rel="stylesheet">
<!-- Slick slider -->
<link rel="stylesheet" type="text/css" href="<?=HTTP_HOST; ?>assets/newdesign/slick/slick.css">
<link rel="stylesheet" type="text/css" href="<?=HTTP_HOST; ?>assets/newdesign/slick/slick-theme.css">
<!-- My styles -->
<link rel="stylesheet" type="text/css" href="<?=HTTP_HOST; ?>assets/newdesign/css/style.css?v=<?=mktime(); ?>">
<link rel="stylesheet" type="text/css" href="<?=HTTP_HOST; ?>assets/newdesign/css/cssmenu.css?v=<?=mktime(); ?>">
<link rel="stylesheet" type="text/css" href="<?=HTTP_HOST; ?>assets/newdesign/css/media.css?v=<?=mktime(); ?>">
<link rel="stylesheet" type="text/css" href="<?=HTTP_HOST; ?>assets/newdesign/css/font-awesome.min.css">
<!-- JAVASCRIPT's AREA -->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script type="text/javascript" src="<?=HTTP_HOST; ?>assets/newdesign/readmore/readmore.min.js"></script>
<script>
    jQuery(document).ready(function ($) {
        $('.cutted-text').readmore({
            maxHeight: 200,
            moreLink: '<button class="btn btn-custom ct-button">Подробнее</button>',
            lessLink: '<button class="btn btn-custom ct-button">Скрыть</button>'
        });

    });
</script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="<?=HTTP_HOST; ?>assets/newdesign/bootstrap337/js/bootstrap.min.js"></script>
<!-- slick slider -->
<script src="<?=HTTP_HOST; ?>assets/newdesign/slick/slick.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('.slick-slider').slick({
            infinite: true,
            variableWidth: true,
            slidesToShow: 8,
            accessibility: false,
            centerMode: true,
            slidesToScroll: 2,
            prevArrow: '<div class="my-slick-prev"><div class="msp-left"></div></div>',
            nextArrow: '<div class="my-slick-next"><div class="msp-right"></div></div>'
//            autoplay: true,
//            autoplaySpeed: 1000
        });
    });
</script>
<!-- cssmenu, fancybox -->
<script src="<?=HTTP_HOST; ?>assets/newdesign/js/cssmenu.js"></script>
<script src="<?=HTTP_HOST; ?>assets/newdesign/selectric/jquery.selectric.js"></script>
<script type="text/javascript" src="<?=HTTP_HOST; ?>assets/newdesign/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>
<script type="text/javascript" src="<?=HTTP_HOST; ?>assets/newdesign/fancybox/source/jquery.fancybox.pack.js?v=2.1.5"></script>
<script type="text/javascript" src="<?=HTTP_HOST; ?>assets/newdesign/fancybox/source/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>
<script type="text/javascript" src="<?=HTTP_HOST; ?>assets/newdesign/fancybox/source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>
<script type="text/javascript" src="<?=HTTP_HOST; ?>assets/newdesign/fancybox/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>
<script type="text/javascript" src="<?=HTTP_HOST; ?>assets/newdesign/js/masonry.pkgd.min.js"></script>

<?php /*script type="text/javascript">
    jQuery(document).ready(function($) {
        $("#cssmenu").menumaker({
            title: "Menu",
            breakpoint: 768,
            format: "multitoggle"
        });

        $('#cssmenu').prepend("<div id='menu-indicator'></div>");

        var foundActive = false, activeElement, indicatorPosition, indicator = $('#cssmenu #menu-indicator'), defaultPosition;

        $("#cssmenu > ul > li").each(function() {
            if ($(this).hasClass('active')) {
                activeElement = $(this);
                foundActive = true;
            }
        });

        if (foundActive === false) {
            activeElement = $("#cssmenu > ul > li").first();
        }

        defaultPosition = indicatorPosition = activeElement.position().left + activeElement.width()/2 - 5;
        indicator.css("left", indicatorPosition);
//        console.log(defaultPosition);
//        console.log(indicatorPosition);

        currentSubMenuId = $('#submenu > ul.active-submenu').prop('id');

        $("#cssmenu > ul > li").hover(function() {
            activeElement = $(this);
            indicatorPosition = activeElement.position().left + activeElement.width()/2 - 5;
            indicator.css("left", indicatorPosition);

            // change submenu
            $('#submenu > ul').removeClass('active-submenu');
            $('#sub_'+this.id).addClass('active-submenu');
            // hide subsubmenu
            $('#subsubmenu').css('display', 'none');
            $('.subsubwrapper').css('display', 'none');
            // class .active
            $('#cssmenu > ul > li').removeClass('active');
            $(this).addClass('active');

            // submenu blink
            $('#submenu > ul > li > a').fadeOut(300);
            $('#submenu > ul > li > a').fadeIn(300);

        }, function() {
            // indicator.css("left", defaultPosition);
            // // change submenu
            // $("#cssmenu > ul > li").each(function() {
            //   if ($(this).hasClass('active')) {
            //     $('#submenu > ul').removeClass('active-submenu');
            //     $('#sub_'+this.id).addClass('active-submenu');
            //   }
            // });

            // reset active submenu to current submenu
            indicator.css("left", defaultPosition);
            $('#submenu > ul').removeClass('active-submenu');
            $('#'+currentSubMenuId).addClass('active-submenu');
        });

        var activeSubmenuId;
        var lastSubmenuPosition;
        // show subsubmenu
        $("#submenu > ul > li").hover(function() {
            // close previous
            $('#subsubmenu').css('display', 'none');
            $('.subsubwrapper').css('display', 'none');
            //
            activeSubmenu = $(this);
            submenuPosition = activeSubmenu.position().left;console.log(submenuPosition);
            activeSubmenuId = activeSubmenu.closest('ul').attr('id');
            var submenuId = this.id;
            if(submenuPosition > 0){
                lastSubmenuPosition = submenuPosition;
            }
            else{
                submenuPosition = lastSubmenuPosition;
            }
            $('#sub_'+submenuId).css("left", submenuPosition);
            $('#sub_'+submenuId).css('display', 'block');
            $('#subsubmenu').css('display', 'block');

        }, function() {

        });

        $('#submenu > ul').hover(function(){
            // active submenu
            indicator.css("left", indicatorPosition);
            $('#submenu > ul').removeClass('active-submenu');
            $('#'+activeSubmenuId).addClass('active-submenu');
        }, function(){
            // reset active submenu to current submenu
            indicator.css("left", defaultPosition);
            $('#submenu > ul').removeClass('active-submenu');
            $('#'+currentSubMenuId).addClass('active-submenu');
        });

        // hide subsubmenu
        $(".subsubwrapper").hover(function() {
//            console.log(activeSubmenuId);
            // active submenu
            indicator.css("left", indicatorPosition);
            $('#submenu > ul').removeClass('active-submenu');
            $('#'+activeSubmenuId).addClass('active-submenu');
        }, function() {
            $('#subsubmenu').css('display', 'none');
            $('.subsubwrapper').css('display', 'none');

            // reset active submenu to current submenu
            indicator.css("left", defaultPosition);
            $('#submenu > ul').removeClass('active-submenu');
            $('#'+currentSubMenuId).addClass('active-submenu');
        });

        // show|hide filters block
        // by title + icon
        $('.filter-title').click(function(){
            var ex_id = this.id.split('_');
            $('#fis_'+ex_id[1]).toggle(500);
            if($('#'+this.id+'>span').hasClass('glyphicon-plus')){
                $('#'+this.id+'>span').removeClass('glyphicon-plus').addClass('glyphicon-minus');
            }
            else{
                $('#'+this.id+'>span').removeClass('glyphicon-minus').addClass('glyphicon-plus');
            }
            // v2

        });

        // selectric
        $('.sel-filter').selectric({
            maxHeight: 200
        });

        // submit filters
        $('.selectric-scroll>ul>li').click(function(){
            document.filtersForm.submit();
        });
        $('.check-filter').change(function(){
            document.filtersForm.submit();
        });

        // Bootstrap carousel
        $('#carousel-example-generic').on('slid.bs.carousel', function () {
          var hr = $('#carousel-example-generic>div.carousel-inner>div.active>a').attr('href');
          // console.log(hr);
          $('#sliderLogo').attr('href', hr);
        });

        // toggle category description
        $.fn.clicktoggle = function(a, b) {
            return this.each(function() {
                var clicked = false;
                $(this).click(function() {
                    if (clicked) {
                        clicked = false;
                        return b.apply(this, arguments);
                    }
                    clicked = true;
                    return a.apply(this, arguments);
                });
            });
        };

        $('#cat_descr').clicktoggle(
            function() {
                var sizetext = $('#size-text').height()+35; // Определяем высоту блока с текстом. Фактически определяем высоту текста и прибавляем ещё 35px - это высота ярлычка, чтобы при открытом состоянии ярлычок не заходил на текст.'
                $('#block-text').animate({'height':sizetext},500); // Плавная анимация к концу блока с текстом.
                $('#cat_descr').html("Свернуть");
            },
            function() {
                $('#block-text').animate({'height':'90'},500); // По второму нажатию на ярлычок будет происходить анимация вверх до 200px.
                $('#cat_descr').html("Подробнее");
                $('html, body').animate({scrollTop: 0},500);
            }
        );

        // select product color and size
        // color
        $('.select-color').click(function(){
            $('.select-colors>.select-color').removeClass('select-color-checked');
            $(this).addClass('select-color-checked');
            var elem_ex = this.id.split('_');
            $('input[name=color]').val(elem_ex[1]);
        });
        // size
        $('.btn-select-size').click(function(){
            $('.select-size>.btn-select-size').removeClass('btn-select-size-checked');
            $(this).addClass('btn-select-size-checked');
            var elem_ex = this.id.split('_');
            $('input[name=size]').val(elem_ex[1]);
        });

        // fancybox
        $(".product-fancybox").fancybox();

        // add product to cart
        $('#add_to_cart').click(function(){
            var p_id = $('input[name=product_id]').val();
            var p_color = $('input[name=color]').val();
            var p_size = $('input[name=size]').val();
            var colors_av = $('input[name=colors_available]').val();
            var sizes_av = $('input[name=sizes_available]').val();
            var modalId = '';
            if((colors_av*1 > 0) && (p_color == 0)){
                modalId += 'Color';
            }
            if((sizes_av*1 > 0) && (p_size == 0)){
                modalId += 'Size';
            }
            if(modalId.length > 0){
                $('#product'+modalId).modal();
                return;
            }

            $.post(
                '<?=(!empty($this->fake_uri_segment)) ? '/newdesign/order/add_to_cart' : '/order/add_to_cart'; ?>',
                { product_id : p_id, color : p_color, size : p_size, <?=$this->security->get_csrf_token_name();?> : '<?=$this->security->get_csrf_hash();?>' },
                function(data){
                    if((data.total_items * 1) > 0){
                        $('#top_cart_span').html('('+data.total_items+')');
                    }
                    $('#add_to_cart').attr('disabled', 'disabled');
                    $('#add_to_cart').addClass('btn-disabled-custom');
                    $('#add_to_cart').html('Товар добавлен в корзину');
                },
                'json'
            );
        });

        // display top cart
        $('#top_cart_link').click(function(){
            $.post(
                '<?=(!empty($this->fake_uri_segment)) ? '/newdesign/order/get_cart' : '/order/get_cart'; ?>',
                { <?=$this->security->get_csrf_token_name();?> : '<?=$this->security->get_csrf_hash();?>' },
                function(data){
                    $('#top_cart_ul').html(data);
                },
                'html'
            );
        });

        // don't close dropdown-menu
        $('#top_cart_ul').click(function(e) {
            e.stopPropagation();
        });

        // plus qty in cart
        $('.cbtn-plus').click(function(){
            var pid = this.id.split('_'); // ID продукта
            var pprice = $('#spprice_'+pid[1]).text(); // цена продукта
            var pcost = $('#spcost_'+pid[1]).text(); // стоимость продукта (цена * количество)
            var ptotal = $('#tcctpvt').text(); // общая стоимость корзины
            var pqty = $('#qty_'+pid[1]).val(); // количество единиц товара
            var re = /[\(\)]/g;
            var cartqtyval = $('#top_cart_span').text();
            var cartqty = cartqtyval.replace(re, ''); // количество товаров в корзине (в шапке сайта)
            var new_pqty = pqty*1 + 1;
            if(new_pqty <= 10){ // 10 max
                $('#qty_'+pid[1]).val(new_pqty); // увеличиваем количество единиц товара
                $('#spcost_'+pid[1]).text((pcost*1 + pprice*1)); // увеличиваем стоимость продукта (цена * количество)
                $('#tcctpvt').text((ptotal*1 + pprice*1)); // увеличиваем общую стоимость корзины
                $('#top_cart_span').text('('+(cartqty*1 + 1)+')'); // увеличиваем количество товаров в корзине (в шапке сайта)
                // меняем количество товара в корзине
                $.post(
                    '<?=(!empty($this->fake_uri_segment)) ? '/newdesign/order/cart_qty' : '/order/cart_qty'; ?>',
                    { product_id : pid[1], operator : 'plus', <?=$this->security->get_csrf_token_name();?> : '<?=$this->security->get_csrf_hash();?>' }
                );
            }
        });
        // minus qty in cart
        $('.cbtn-minus').click(function(){
            var pid = this.id.split('_'); // ID продукта
            var pprice = $('#spprice_'+pid[1]).text(); // цена продукта
            var pcost = $('#spcost_'+pid[1]).text(); // стоимость продукта (цена * количество)
            var ptotal = $('#tcctpvt').text(); // общая стоимость корзины
            var pqty = $('#qty_'+pid[1]).val(); // количество единиц товара
            var re = /[\(\)]/g;
            var cartqtyval = $('#top_cart_span').text();
            var cartqty = cartqtyval.replace(re, ''); // количество товаров в корзине (в шапке сайта)
            var new_pqty = pqty*1 - 1;
            if(new_pqty > 0){ // 1 min
                $('#qty_'+pid[1]).val(new_pqty); // уменьшаем количество единиц товара
                $('#spcost_'+pid[1]).text((pcost*1 - pprice*1)); // уменьшаем стоимость продукта (цена * количество)
                $('#tcctpvt').text((ptotal*1 - pprice*1)); // уменьшаем общую стоимость корзины
                $('#top_cart_span').text('('+(cartqty*1 - 1)+')'); // уменьшаем количество товаров в корзине (в шапке сайта)
                // меняем количество товара в корзине
                $.post(
                    '<?=(!empty($this->fake_uri_segment)) ? '/newdesign/order/cart_qty' : '/order/cart_qty'; ?>',
                    { product_id : pid[1], operator : 'minus', <?=$this->security->get_csrf_token_name();?> : '<?=$this->security->get_csrf_hash();?>' }
                );
            }
        });


        // toggle user type tabs
        $('.cc-user-type').click(function(){
            $(this).tab('show');
        });

        // check Terms checkbox before order checkout
        $('.order-checkout-btn').click(function(){
            if($('input[name=register_terms]').is(':checked')){
                return true;
            }
            else{
                $('#coTermsModal').modal();
                return false;
            }
        });

        // masonry
        $('.mgrid').masonry({
            // options
            itemSelector: '.mgrid-item'
            // columnWidth: 292
        });

    }); // / jQuery.ready

    function remove_from_cart(productId){
        $.post(
            '<?=(!empty($this->fake_uri_segment)) ? '/newdesign/order/del_from_cart' : '/order/del_from_cart'; ?>',
            { product_id : productId, <?=$this->security->get_csrf_token_name();?> : '<?=$this->security->get_csrf_hash();?>' },
            function(data){
                if(data.product_id*1 > 0){
                    $('#tccr_'+data.product_id).hide('slow'); // убираем товар из корзины в шапке
                    $('#tccrt_'+data.product_id).hide('slow'); // убираем товар из корзины в таблице
                    $('#tcctpv').html(data.total_price); // меняем общую стоимость корзины в шапке
                    $('#tcctpvt').html(data.total_price); // меняем общую стоимость корзины в таблице
                    if(data.items_in_cart*1 > 0){
                        $('#top_cart_span').html('('+data.items_in_cart+')');
                    }
                    else{
                        $('#top_cart_span').html('');
                    }
                }
            },
            'json'
        );
    }

    // login modal
    function send_loginModal()
    {
        var email=$("#loginModal #login_name").val();
        var password=$("#loginModal #login_password").val();

        if(password==""){
            alert("Введите пароль!");
            return false;
        }

        if(email==""){
            alert("Введите E-mail!");
            return false;
        }

        // if(!/^([a-z0-9_.-]+)@([a-z0-9_.-]+)\.([a-z.]{2,6})$/.test(email)){
        // 	alert("E-mail введен неправильно!");
        // 	return false;
        // }

        $.post("/login.html",{
            login_sm:1,
            ajax:1,
            email:email,
            password:password
        },function(d){
            if(parseInt(d)!=1){
                alert(d);
            }else{
                document.location.reload();
            }
        });
    }

    // remind modal
    function do_remind()
    {
//        var email=$.trim($("#myModal1Label input[name='email']").val());
        var email = $("#remail").val();
        //var password=$.trim($("#popupRemind input[name='password']").val());

        var err=[];
        // if(email=="")err[err.length]='Поле "Email" не заполнено!';
        // if(email!="" && !/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+$/.test(email))err[err.length]='Поле "Email" заполнено неверно!';

        if(err.length>0){
            var errors="";
            $.each(err,function(i,v){
                errors+=v+'\n';
            });
            alert(errors);
            return false;
        }

        $.post("/remind.html",{
            login_sm:1,
            ajax:1,
            email:email
        },function(d){
            if(parseInt(d)!=1){
                alert(d);
            }else{
                $("#myModal1").modal('toggle');
                alert('Инструкция по восстановлению пароля отправлена вам на Email.');
            }
        });
    }
</script*/?>
<script type="text/javascript">
    function remove_from_cart(a) {
        $.post('<?=(!empty($this->fake_uri_segment)) ? '/newdesign/order/del_from_cart' : '/order/del_from_cart'; ?>', {
            product_id: a,
        <?=$this->security->get_csrf_token_name();?> : '<?=$this->security->get_csrf_hash();?>'
        }, function(a) {
            1 * a.product_id > 0 && ($("#tccr_" + a.product_id).hide("slow"), $("#tccrt_" + a.product_id).hide("slow"), $("#tcctpv").html(a.total_price), $("#tcctpvt").html(a.total_price), 1 * a.items_in_cart > 0 ? $("#top_cart_span").html("(" + a.items_in_cart + ")") : $("#top_cart_span").html(""), 1 * a.items_in_cart > 0 ? $("#tm_cart_icon_num").html(a.items_in_cart) : $("#tm_cart_icon_num").html(""))
        }, "json")
    }

    function send_loginModal() {
        var a = $("#loginModal #login_name").val(),
            b = $("#loginModal #login_password").val();
        return "" == b ? (alert("Введите пароль!"), !1) : "" == a ? (alert("Введите E-mail!"), !1) : void $.post("/login.html", {
            login_sm: 1,
            ajax: 1,
            email: a,
            password: b
        }, function(a) {
            1 != parseInt(a) ? console.log(a) : document.location.reload()
        })
    }

    function do_remind() {
        var a = $("#remail").val(),
            b = [];
        if (b.length > 0) {
            var c = "";
            return $.each(b, function(a, b) {
                c += b + "\n"
            }), alert(c), !1
        }
        $.post("/remind.html", {
            login_sm: 1,
            ajax: 1,
            email: a
        }, function(a) {
            1 != parseInt(a) ? alert(a) : ($("#myModal1").modal("toggle"), alert("Инструкция по восстановлению пароля отправлена вам на Email."))
        })
    }

    jQuery(document).ready(function(a) {
        <?php if(empty($this->is_mobile)): ?>
        a("#cssmenu").menumaker({
            title: "Menu",
            breakpoint: 768,
            format: "multitoggle"
        }), a("#cssmenu").prepend("<div id='menu-indicator'></div>");
        var c, d, f, b = !1,
            e = a("#cssmenu #menu-indicator");
        a("#cssmenu > ul > li").each(function() {
            a(this).hasClass("active") && (c = a(this), b = !0)
        }), b === !1 && (c = a("#cssmenu > ul > li").first()), f = d = c.position().left + c.width() / 2 - 5, e.css("left", d), currentSubMenuId = a("#submenu > ul.active-submenu").prop("id"), a("#cssmenu > ul > li").hover(function() {
            c = a(this), d = c.position().left + c.width() / 2 - 5, e.css("left", d), a("#submenu > ul").removeClass("active-submenu"), a("#sub_" + this.id).addClass("active-submenu"), a("#subsubmenu").css("display", "none"), a(".subsubwrapper").css("display", "none"), a("#cssmenu > ul > li").removeClass("active"), a(this).addClass("active"), a("#submenu > ul > li > a").fadeOut(300), a("#submenu > ul > li > a").fadeIn(300)
        }, function() {
            a("#cssmenu > ul > li").each(function() {
                a(this).hasClass("active-bold") ? a(this).addClass("active") : a(this).removeClass("active")
            }), e.css("left", f), a("#submenu > ul").removeClass("active-submenu"), a("#" + currentSubMenuId).addClass("active-submenu")
        });
        var g, h;
        a("#submenu > ul > li").hover(function() {
            a("#subsubmenu").css("display", "none"), a(".subsubwrapper").css("display", "none"), activeSubmenu = a(this), submenuPosition = activeSubmenu.position().left, console.log(submenuPosition), g = activeSubmenu.closest("ul").attr("id");
            var b = this.id;
            submenuPosition > 0 ? h = submenuPosition : submenuPosition = h, a("#sub_" + b).css("left", submenuPosition), a("#sub_" + b).css("display", "block"), a("#subsubmenu").css("display", "block")
        }, function() {}), a("#submenu > ul").hover(function() {
            e.css("left", d), a("#submenu > ul").removeClass("active-submenu"), a("#" + g).addClass("active-submenu")
        }, function() {
            e.css("left", f), a("#submenu > ul").removeClass("active-submenu"), a("#" + currentSubMenuId).addClass("active-submenu")
        }), a(".subsubwrapper").hover(function() {
            e.css("left", d), a("#submenu > ul").removeClass("active-submenu"), a("#" + g).addClass("active-submenu")
        }, function() {
            a("#subsubmenu").css("display", "none"), a(".subsubwrapper").css("display", "none"), e.css("left", f), a("#submenu > ul").removeClass("active-submenu"), a("#" + currentSubMenuId).addClass("active-submenu")
        }),
        <?php endif; ?>
        a(".filter-title").click(function() {
            var b = this.id.split("_");
            a("#fis_" + b[1]).toggle(500), a("#" + this.id + ">span").hasClass("glyphicon-plus") ? a("#" + this.id + ">span").removeClass("glyphicon-plus").addClass("glyphicon-minus") : a("#" + this.id + ">span").removeClass("glyphicon-minus").addClass("glyphicon-plus")
        }), a(".sel-filter").selectric({
            maxHeight: 200
        }), a(".selectric-scroll>ul>li").click(function() {
            document.filtersForm.submit()
        }), a(".check-filter").change(function() {
            document.filtersForm.submit()
        }), a("#carousel-example-generic").on("slid.bs.carousel", function() {
            var b = a("#carousel-example-generic>div.carousel-inner>div.active>a").attr("href");
            a("#sliderLogo").attr("href", b)
        }), a.fn.clicktoggle = function(b, c) {
            return this.each(function() {
                var d = !1;
                a(this).click(function() {
                    return d ? (d = !1, c.apply(this, arguments)) : (d = !0, b.apply(this, arguments))
                })
            })
        }, a("#cat_descr").clicktoggle(function() {
            var b = a("#size-text").height() + 35;
            a("#block-text").animate({
                height: b
            }, 500), a("#cat_descr").html("Свернуть")
        }, function() {
            a("#block-text").animate({
                height: "90"
            }, 500), a("#cat_descr").html("Подробнее"), a("html, body").animate({
                scrollTop: 0
            }, 500)
        }), a(".select-color").click(function() {
            a(".select-colors>.select-color").removeClass("select-color-checked"), a(this).addClass("select-color-checked");
            var b = this.id.split("_");
            a("input[name=color]").val(b[1])
        }), a(".btn-select-size").click(function() {
            a(".select-size>.btn-select-size").removeClass("btn-select-size-checked"), a(this).addClass("btn-select-size-checked");
            var b = this.id.split("_");
            a("input[name=size]").val(b[1])
        }), a(".product-fancybox").fancybox(), a("#add_to_cart").click(function() {
            var b = a("input[name=product_id]").val(),
                c = a("input[name=color]").val(),
                d = a("input[name=size]").val(),
                e = a("input[name=colors_available]").val(),
                f = a("input[name=sizes_available]").val(),
                g = "";
            return 1 * e > 0 && 0 == c && (g += "Color"), 1 * f > 0 && 0 == d && (g += "Size"), g.length > 0 ? void a("#product" + g).modal() : void a.post('<?=(!empty($this->fake_uri_segment)) ? '/newdesign/order/add_to_cart' : '/order/add_to_cart'; ?>', {
                product_id: b,
                color: c,
                size: d,
            <?=$this->security->get_csrf_token_name();?> : '<?=$this->security->get_csrf_hash();?>'
            }, function(b) {
                1 * b.total_items > 0 && a("#top_cart_span").html("(" + b.total_items + ")"), a("#add_to_cart").attr("disabled", "disabled"), a("#add_to_cart").addClass("btn-disabled-custom"), a("#add_to_cart").html("Товар добавлен в корзину"), a("#add_to_cart").css("display", "none"), a("#add_to_cart_link").css("display", "block"), a("#tm_cart_icon_num").html(b.total_items)
            }, "json")
        }), a("#top_cart_link").click(function() {
            a.post('<?=(!empty($this->fake_uri_segment)) ? '/newdesign/order/get_cart' : '/order/get_cart'; ?>', {
        <?=$this->security->get_csrf_token_name();?> : '<?=$this->security->get_csrf_hash();?>'
            }, function(b) {
                a("#top_cart_ul").html(b)
            }, "html")
        }), a("#top_cart_ul").click(function(a) {
            a.stopPropagation()
        }), a(".cbtn-plus").click(function() {
            var b = this.id.split("_"),
                c = a("#spprice_" + b[1]).text(),
                d = a("#spcost_" + b[1]).text(),
                e = a("#tcctpvt").text(),
                f = a("#qty_" + b[1]).val(),
                g = /[\(\)]/g,
                h = a("#top_cart_span").text(),
                i = h.replace(g, ""),
                j = 1 * f + 1;
            j <= 10 && (a("#qty_" + b[1]).val(j), a("#spcost_" + b[1]).text(1 * d + 1 * c), a("#tcctpvt").text(1 * e + 1 * c), a("#top_cart_span").text("(" + (1 * i + 1) + ")"), a.post('<?=(!empty($this->fake_uri_segment)) ? '/newdesign/order/cart_qty' : '/order/cart_qty'; ?>', {
                product_id: b[1],
                operator: "plus",
        <?=$this->security->get_csrf_token_name();?> : '<?=$this->security->get_csrf_hash();?>'
            }))
        }), a(".cbtn-minus").click(function() {
            var b = this.id.split("_"),
                c = a("#spprice_" + b[1]).text(),
                d = a("#spcost_" + b[1]).text(),
                e = a("#tcctpvt").text(),
                f = a("#qty_" + b[1]).val(),
                g = /[\(\)]/g,
                h = a("#top_cart_span").text(),
                i = h.replace(g, ""),
                j = 1 * f - 1;
            j > 0 && (a("#qty_" + b[1]).val(j), a("#spcost_" + b[1]).text(1 * d - 1 * c), a("#tcctpvt").text(1 * e - 1 * c), a("#top_cart_span").text("(" + (1 * i - 1) + ")"), a.post('<?=(!empty($this->fake_uri_segment)) ? '/newdesign/order/cart_qty' : '/order/cart_qty'; ?>', {
                product_id: b[1],
                operator: "minus",
        <?=$this->security->get_csrf_token_name();?> : '<?=$this->security->get_csrf_hash();?>'
            }))
        }), a(".cc-user-type").click(function() {
            a(this).tab("show")
        }), a(".order-checkout-btn").click(function() {
            return !!a("input[name=register_terms]").is(":checked") || (a("#coTermsModal").modal(), !1)
        }), a('.mgrid').masonry({itemSelector: '.mgrid-item'});// added masonry line

        var menu = a('#mob_top_nav');
        var origOffsetY = menu.offset().top;

        function scroll() {
            if (a(window).scrollTop() >= origOffsetY) {
                a('#mob_top_nav').addClass('sticky');
//                a('.content').addClass('menu-padding');
            } else {
                a('#mob_top_nav').removeClass('sticky');
//                a('.content').removeClass('menu-padding');
            }


        }

        document.onscroll = scroll;

        a(".navbar-collapse").css({ maxHeight: a(window).height() - $(".navbar-header").height() + "px" });
    });
</script>
<!-- /JAVASCRIPT's AREA -->
<?php print $this->config->config['template_bottom_scripts']; ?>
</body>
</html>