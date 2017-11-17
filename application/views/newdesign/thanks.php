<?php
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
    3 => array(
        'title' => 'Подтверждение',
        'url' => base_url('order/thanks'),
    ),
);
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
<div class="row thanks-page-content">
    <div class="col-lg-8 col-lg-offset-2">
        <h2 class="text-center order-header">Спасибо</h2>
        <div class="row">
            <div class="col-lg-12">
                <p class="text-center">Ваш заказ <span class="thanks-bold">№ <?= $order_id; ?></span> успешно принят в обработку.</p>
                <p class="text-center">В ближайшее время с Вами свяжется оператор для согласования деталей заказа.</p>
                <p class="text-center">Спасибо за то, что Вы воспользовались услугами нашего интернет-магазина.</p>
                <p class="text-center">Будем рады видеть Вас в кругу наших постоянных клиентов!</p>
            </div>
        </div>
    </div>
</div>
<div class="row category-bottom-slider">
    <div class="col-lg-12">
        <?php echo $this->widgets('content_bottom'); ?>
    </div>
</div>

<!-- Google Code for &#1055;&#1086;&#1082;&#1091;&#1087;&#1082;&#1072;
Conversion Page -->
<script type="text/javascript">
    /* <![CDATA[ */
    var google_conversion_id = 852163093;
    var google_conversion_language = "en";
    var google_conversion_format = "3";
    var google_conversion_color = "ffffff";
    var google_conversion_label = "oG70CO3EznEQlfSrlgM";
    var google_conversion_value = 9.00;
    var google_conversion_currency = "RUB";
    var google_remarketing_only = false;
    /* ]]> */
</script>
<script type="text/javascript"
        src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
    <div style="display:inline;">
        <img height="1" width="1" style="border-style:none;" alt=""
             src="//www.googleadservices.com/pagead/conversion/852163093/?value=9.00&amp;currency_code=RUB&amp;label=oG70CO3EznEQlfSrlgM&amp;guid=ON&amp;script=0"/>
    </div>
</noscript>