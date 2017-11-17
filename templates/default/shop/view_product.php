<?php
$categoryes_res = $this->ci->db
        ->join("categoryes", "categoryes.id = shop_products_categories_link.category_id")
        ->get_where("shop_products_categories_link", array(
            "shop_products_categories_link.product_id" => $product_res->id
        ))
        ->result();

function drawCat($parent_id = 0, &$categoryes_res, &$that) {
    foreach ($categoryes_res AS $r) {
        if ($r->parent_id != $parent_id)
            continue;
        $r->link = $that->ci->module->link_category($r);
        ?><a itemprop="url" href="<?php print $r->link; ?>"> <span itemprop="title"><?php print $r->title; ?></span> </a> → <?php
        drawCat($r->id, $categoryes_res, $that);
    }
}
$currency = 'grn';
if ($this->ci->session->userdata('currentCurrency') != null){
    $currency = $this->ci->session->userdata('currentCurrency');    
}
switch (true){
    case $currency == 'usd':
        $currencyIcon = '$';
    break;
    case $currency == 'eur':
        $currencyIcon = '&euro;';
    break;
    case $currency == 'grn':
        $currencyIcon = 'грн.';
    break;
}

// габариты и размеры – новая версия
$dimensions = $this->ci->db->get_where('shop_dimensions', array('type_id' => $product_res->type_id))->result();

// статус "Снято с производства" - не выводим цвета, размеры, габариты и кнопки покупок
$discontinued = FALSE;
?>
<script type="text/javascript" src="/assets/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" type="text/css" href="/assets/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<script src="/modules/shop/media/js/shop.main.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $(".thumbs a").fancybox();
    });
</script>
<div class="breadcrumbsW" itemtype="http://data-vocabulary.org/Breadcrumb">
    <a href="/" itemprop="url"><span itemprop="title">Главная</span></a> → <?php drawCat(0, $categoryes_res, $this); ?> <strong itemprop="title"><?php print $product_res->title; ?></strong>
</div>
<div class="productViewW"><div class="productViewGal">
        <div class="productInfo">
            <h1><?php print $product_res->title; ?></h1><br />
            <?php
            if (sizeof($shop_product_type_fields_res)) {
                ?>
                <!-- вывод артикула товара By Bomb Inside -->
                <div class="product_code">
                    Артикул: <?php print $product_res->code; ?> 
                </div>
                <!-- end вывод артикула товара By Bomb Inside -->
                <div class="paramsW">
                    <ul class="params" style="margin-top: 15px;" >
                        <li>
                            <div class="typeTitle">Бренд:</div><div class="typeValue"><span><?php print $product_res->brand_title; ?></span></div>
                            <div class="clear"></div>
                        </li>
                        <?php
                        foreach ($shop_product_type_fields_res AS $r) {
                            switch ($r->field_type) {
                                default:
                                    $val = $r->params->options[$product_res->{"f_" . $r->id}];
                                    break;
                            }
                            // Пояснение к статусу "Предзаказ - ..."
                            if(($r->title == 'Статус товара') && (strpos($val, 'Предзаказ') !== FALSE)){
//                                $tooltip_text = 'Предварительный заказ или предзаказ (англ. Pre-order) — изъявление потребителем намерения приобрести тот или иной товар (работу, услугу). Предварительный заказ позволяет потребителю заранее гарантированно закрепить за собой копию этого товара.';
                                $tooltip_text = $this->config->item('preorder_text');
                                $val = (!empty($tooltip_text)) ? '<a class="tooltip1">' . $val . '<span class="classic">' . $tooltip_text . '</span></a>' : $val;
                            }
                            // статус "Снято с производства" - не выводим цвета, размеры, габариты и кнопки покупок
                            if(($r->title == 'Статус товара') && (strpos($val, 'Снято с') !== FALSE)){
                                $discontinued = TRUE;
                            }
                        ?>
                            <li>
                                <div class="typeTitle"><?php print $r->title; ?>:</div><div class="typeValue"><span><?php print $val; ?></span></div>
                                <div class="clear"></div>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
                <?php print "<br />" . strip_tags($product_res->full_description, '<br><br/>') . "<br />"; ?>
                <?php 
            // статус "Снято с производства" - не выводим цвета, размеры, габариты и кнопки покупок
            if(!$discontinued){
                if(!empty($product_res->sizes)): 
                    $sizes = (strpos($product_res->sizes, '0,') === 0) ? substr($product_res->sizes, 2) : $product_res->sizes;
                ?>
                <div class="product-sizes">
                    <span class="ps-header">Доступные размеры: </span><span class="ps-values"><?=str_replace(',', ', ', $sizes); ?></span>
                </div>
                <?php 
                endif; 
            } // статус "Снято с производства"
                ?>
                <div class="social">
                    <div class="tw"><a href="https://twitter.com/share" class="twitter-share-button" data-count="none">Tweet</a><script>!function (d, s, id) {
                            var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
                            if (!d.getElementById(id)) {
                                js = d.createElement(s);
                                js.id = id;
                                js.src = p + '://platform.twitter.com/widgets.js';
                                fjs.parentNode.insertBefore(js, fjs);
                            }
                        }(document, 'script', 'twitter-wjs');</script></div>
                        <div class="pi">
                    <a href="//ru.pinterest.com/pin/create/button/" data-pin-do="buttonBookmark" ><img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_gray_20.png" /></a>
<!-- Please call pinit.js only once per page -->
<script type="text/javascript" async defer src="//assets.pinterest.com/js/pinit.js"></script>
                            
                            
                    </div>
                    <div class="fb"><div id="fb-root"></div><script>(function (d, s, id) {
                            var js, fjs = d.getElementsByTagName(s)[0];
                            if (d.getElementById(id))
                                return;
                            js = d.createElement(s);
                            js.id = id;
                            js.src = "//connect.facebook.net/en_EN/all.js#xfbml=1&appId=213867361987961";
                            fjs.parentNode.insertBefore(js, fjs);
                        }(document, 'script', 'facebook-jssdk'));</script><div class="fb-like" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false"></div></div>
                    <div class="gp"><div class="g-plusone" data-size="medium" ></div><script type="text/javascript"> window.___gcfg = {lang: 'ru'};
                        (function () {
                            var po = document.createElement('script');
                            po.type = 'text/javascript';
                            po.async = true;
                            po.src = 'https://apis.google.com/js/plusone.js';
                            var s = document.getElementsByTagName('script')[0];
                            s.parentNode.insertBefore(po, s);
                        })();</script></div>
                    <div class="clear"><!-- --></div>
                </div>
                <?php
            }
            ?>
            <div class="description">
                <div class="price">
                    <?php print $product_res->price; ?> <?=$currencyIcon;?>
                </div>
                <?php if(!empty($product_res->price_old)): ?><div class="price price-old"><?=$product_res->price_old; ?> <?=$currencyIcon;?></div><?php endif; ?>
                <div class="clear"><!-- --></div>
                <?php $id = $product_res->id; ?>
                <?php
            // статус "Снято с производства" - не выводим цвета, размеры, габариты и кнопки покупок
            if(!$discontinued){
                
                if ($product_res->colors == 1) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            $('.selectColor img').first().addClass("scelectedColor");
                            $('.selectColor a').click(function () {
                                $('.selectColor a img').removeClass("scelectedColor");
                                $(this).children('img').addClass("scelectedColor");
                            });
                        });
                    </script>
                    <?php
                    print '<div class="selectColor"><br /><span>Выберите цвет</span><br /><ul>';
                    $i = 0;
                    $last = count($product_photos_res);
                    foreach ($product_photos_res AS $a => $r) {
                        // выводим только фото, отмеченные как цвет товара
                        if($r->extra_color == 0) continue;
                        
                        $i++;
                        ?><li class="more"><a rel="group" onclick="return false;" href="/uploads/shop/products/big/<?php print $r->file_name; ?>"><img src="/uploads/shop/products/thumbs3/<?php print $r->file_name; ?>" alt="<?php print $product_res->title; ?>" title="купить <?php print $product_res->title; ?>"/></a></li><?php
                        if ($i == 3 || $last == $a + 1) {
                            print '<div class="clear"><!-- --></div>';
                            $i = 0;
                        }
                    }
                    print '</ul></div>';
                }
                ?>
                <script type="text/javascript">


                    $(document).ready(function () {
                        $('.buyNow').click(function () {

                            if ($("#selectSize").length > 0) {
                                if ($('#selectSize').val() == "-- размеры --") {
                                    alert("Выберите размер");
                                    return false;
                                }
                                var size = $('#selectSize').val();
                            }
                            if ($('.selectColor img').is('.scelectedColor')) {
                                var color = $('.scelectedColor').attr('src');
                                var imgArray = color.split('/');
                                var leng = imgArray.length - 1;
                                var id = <?php print $id; ?> + ':' + imgArray[leng];
                            } else {
                                var color = 'uploads/shop/products/thumbs2/<?php print $product_res->main_picture_file_name; ?>'
                                var imgArray = color.split('/');
                                var leng = imgArray.length - 1;
                                var id = <?php print $id; ?> + ':' + imgArray[leng];
                            }

                            $.get("/?m=shop&a=add_product_to_cart", {
                                shop_add_product_to_cart_sm: 1,
                                product_id: id,
                                quantity: $(this).parents('.buttons').find('.quantity').val(),
                                type: 'add',
                                img: color,
                                size: size
                            }, function (d) {


                                $(".basketW .basket").html('Твоя корзина<br /> <span class="num">' + d.products_num + '</span> товар <span class="total_amount">' + d.price_hmn + '</span>')

                                location.href = '/cart.html';
                            });



                        });



                        $('.addToCart').click(function () {
                            if ($("#selectSize").length > 0) {
                                if ($('#selectSize').val() == "-- размеры --") {
                                    alert("Выберите размер");
                                    return false;
                                }
                                var size = $('#selectSize').val();
                            }
                            if ($('.selectColor img').is('.scelectedColor')) {
                                var color = $('.scelectedColor').attr('src');
                                var imgArray = color.split('/');
                                var leng = imgArray.length - 1;
                                var id = <?php print $id; ?> + ':' + imgArray[leng];
                            } else {
                                var color = 'uploads/shop/products/thumbs2/<?php print $product_res->main_picture_file_name; ?>'
                                var imgArray = color.split('/');
                                var leng = imgArray.length - 1;
                                var id = <?php print $id; ?> + ':' + imgArray[leng];
                            }
                            var dimensions = '';
                            $('.dim_input').each(function(i){ 
                                dimensions += $(this).attr('name')+'-'+$(this).val()+'|'; 
                            });
                            
                            shop_product_cart(this, id, $(this).parents('.buttons').find('.quantity').val(), 'add', color, size, dimensions);
                        });
                    });
                </script>
                <?php
                $orr = 'Введите ';
                if (!empty($product_res->sizes)) { 
                    ?><span>Выберите размер</span><br />
                    <select id="selectSize">
                        <option>-- размеры --</option>
                        <?php
                        $product_res->sizes = (strpos($product_res->sizes, '0,') === 0) ? substr($product_res->sizes, 2) : $product_res->sizes;
                        $sizes = explode(',',$product_res->sizes);
                        foreach($sizes as $s){
                            print '<option value="'.$s.'">'.$s.'</option>';
                        }
                        ?></select>
                <?php
                    $orr = 'или введите ';
                }
                if(!empty ($dimensions)){
                ?>
                    <div class="dimensions">
                        <span><?=$orr; ?>свои параметры (не обязательно):</span><br />
                    <table class="dim_table" border="0" cellpadding="3" cellspacing="0">
                        <thead><tr>
                <?php
                    foreach ($dimensions as $item){
                        echo '<th>' . $item->title . '</th>';
                    }
                ?>
                        </tr></thead>
                        <tbody><tr>
                <?php
                    foreach ($dimensions as $item){
                        echo '<td><input type="text" maxlength="3" class="dim_input" name="dimension_' . $item->name . '"></td>';
                    }
                ?>
                        </tr></tbody>
                    </table>
                    </div>
                <?php
                }
                ?>
                    <a href="#" onclick="return false;" class="addToCart current quantity"><span>В корзину</span></a>
                    <a href="#" onclick="return false;" class="buyNow current "><span>Купить сейчас</span></a>
                    <?php
                    $wis = $this->db->query("SELECT `wishlist` FROM `users` WHERE `id`='" . $this->ci->session->userdata("user_id") . "' ")->result();
                    $wi = explode(",", $wis[0]->wishlist);
                    if (in_array($id, $wi)) {
                        $wishStyle = "display:none;";
                    }
                    if (!empty($_POST['wishlist'])) {
                        $wishStyle = "display:none;";
                        if (empty($wis[0]->wishlist)) {
                            $wishlist = $_POST['wishlist'];
                        } else {
                            $wishlist = $wis[0]->wishlist . "," . $_POST['wishlist'];
                        }
                        $wis = $this->db->query("UPDATE `users` SET `wishlist`='" . $wishlist . "' WHERE `id`='" . $this->ci->session->userdata("user_id") . "' ");
                    }
                    ?>
    <!--		<a style="<?php print $wishStyle; ?>" href="#" onclick="$('#wishlist').submit(); return false;" class="wishList">В корзину_wish</a> -->
                    <form style="<?php print $wishStyle; ?>" method="post" id="wishlist"> <input type="hidden" name="wishlist" value="<?php print $id; ?>" /> </form>
                    <?php /*a href="/sizes-table.html" class="sizeTable" target="_blank">Таблица размеров</a*/?>
            <?php  
            } // статус "Снято с производства"
            ?>
                </div>
            </div>
            <div class="thumbs"><div class="thumbsI">
                    <ul>
                        <li style="float:none">
                            <div class="productImgWrap"><!-- для размещения ярлыка на фото товара -->
                            <a rel="group" href="/uploads/shop/products/big/<?php print $product_res->main_picture_file_name; ?>"><img src="/uploads/shop/products/thumbs2/<?php print $product_res->main_picture_file_name; ?>" alt="<?php print $product_res->title; ?>" title="купить <?php print $product_res->title; ?>" />
                            <?php
                            $img_size = @getimagesize('./uploads/shop/products/thumbs2/' . $product_res->main_picture_file_name);
                            if(!empty($img_size[0])){
                            $img_size[0] = ($img_size[0] <= 446) ? $img_size[0] : 446;
                            // проверка категорий товара – для размещения на нём wm-стикера
                            $stiker_info = array();
                            // приоритет ярлыков: Наличие => SALE => NEW
                            $wm_categories = array(
                                0 => array('id' => '1641', 'title' => 'В наличии', 'class' => 'wm-stock'),
                                1 => array('id' => '1618', 'title' => 'SALE', 'class' => 'wm-sale'),
                                2 => array('id' => '1617', 'title' => 'NEW', 'class' => 'wm-new'),
                            );
                            $r_cats = (!empty($product_res->category_ids)) ? explode(',', $product_res->category_ids) : '';
                            if (!empty($r_cats)) {
                                foreach ($wm_categories as $category) {
                                    if (in_array($category['id'], $r_cats)) {
                                        $stiker_info = $category;
                                        break;
                                    }
                                }
                            }
                            if(!empty($stiker_info)) {
                                echo '<div class="wm-stiker ' . $stiker_info['class'] . '" style="width: ' . $img_size[0] . 'px;"></div>'; 
                            }
                            ?>
                            <!--div class="wm-stiker " <?=' style="width: ' . $img_size[0] . 'px;"'; ?>></div-->
                            <?php 
                            }
                            ?>
                            </a>
                            </div>
                        </li>
                        <?php
                        if (sizeof($product_photos_res) > 0) {
                            unset($product_photos_res[0]);
                            $i = 0;
                            foreach ($product_photos_res AS $r) {
                                $i++;
                                ?><li class="more"><a rel="group" href="/uploads/shop/products/big/<?php print $r->file_name; ?>"><span><img src="/uploads/shop/products/thumbs3/<?php print $r->file_name; ?>" alt="<?php print $product_res->title; ?>" title="купить <?php print $product_res->title; ?>"/></span></a></li><?php
                                if ($i == 5) {
                                    print '<div class="clear"><!-- --></div>';
                                }
                            }
                        }
                        ?>
                    </ul>
                    <div class="clear"></div>
                </div></div>
            <div class="clear"></div>
            <br />
            <div class="line"><!-- --></div>
            <?php
            // похожие товары
            $cat_ids = array();
            foreach ($categoryes_res AS $r) {
                if ($r->parent_id == 0)
                    continue;
                $cat_ids[] = $r->id;
            }
            if (sizeof($cat_ids) > 0) {
                $similar_products_query = $this->ci->module
                        ->products_query()
                        ->join("shop_products_categories_link", "shop_products_categories_link.product_id = shop_products.id && shop_products_categories_link.category_id IN(" . implode(",", $cat_ids) . ")")
                        ->order_by("RAND()")
                        ->where("show", 1)
                        ->limit(5)
                        ->get();

                $similar_products_res = $similar_products_query->result();
                if (sizeof($similar_products_res) > 0) {
                    ?><div class="collection">
                        <br /><br />
                        <div class="collectionTitle">Похожие товары:</div>

                        <div class="collectionTextW">
                            <?php
                            foreach ($similar_products_res AS $r) {
                                $r->link = $this->ci->module->link_product_view($r);
                                $r->add_to_cart_attrs = $this->ci->module->add_to_cart_attrs($r);
                                $r->price_hmn = $this->ci->module->product_price($r);
                                ?><div class="productsListRow">
                                <?php
                                if (empty($r->main_picture_file_name)) {
                                    ?><a href="<?php print $r->link; ?>" class="thumb" ><img src="/assets/media/nophoto.png" /></a><?php
                                    } else {
                                        ?><a href="<?php print $r->link; ?>" class="thumb" ><img src="/uploads/shop/products/thumbs4/<?php print $r->main_picture_file_name; ?>" /></a><?php
                                        }
                                        ?>
                                    <br /><a href="<?php print $r->link; ?>" class="title"><?php print $r->title; ?></a>
                                </div><?php
                            }
                            ?>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <br /><br /><?php
                }
            }
            ?>
        <br />
        <div class="line"><!-- --></div>
    </div></div>