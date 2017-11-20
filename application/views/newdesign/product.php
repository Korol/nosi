<?php
/**
 * Autocomplete
 *
 * @see Pages::getProductPage()
 * @var Pages $product_images
 * @var Pages $product
 * @var Pages $product_options
 * @var Pages $breadcrumbs
 * @var Pages $product_id
 * @var Pages $product_categories
 * @var Pages $brand
 * @var Pages $preorder_text
 * @var Pages $product_currency
 * @var Pages $similar_products
 *
 */

//var_dump($product);
//var_dump($product_categories);
//var_dump($product_images);
//var_dump($product_options);
// colors and sizes
$colors_images = array();
if(!empty($product_images)){
    foreach ($product_images as $one_image) {
        if($one_image['extra_color'] == 0) continue;
        $colors_images[] = $one_image;
    }
}
//$selected_color = (!empty($colors_images)) ? $colors_images[0]['id'] : 0;
//$selected_size = 0;
//$selected_color_class = 'select-color-checked';
//$selected_size_class = 'btn-select-size-checked';
$selected_color = $selected_size = 0;
$selected_color_class = '';
$selected_size_class = '';

// images
$main_image = array_shift($product_images);
$desktop_main_image_path = ($main_image['name'] != 'placeholder') ? HTTP_HOST . 'uploads/shop/products/thumbs2/' : HTTP_HOST . 'assets/newdesign/images/'; // big
$mobile_main_image_path = ($main_image['name'] != 'placeholder') ? HTTP_HOST . 'uploads/shop/products/thumbs2/' : HTTP_HOST . 'assets/newdesign/images/';
$other_image_path = HTTP_HOST . 'uploads/shop/products/thumbs4/';
$original_image_path = ($main_image['name'] != 'placeholder') ? HTTP_HOST . 'uploads/shop/products/big/' : HTTP_HOST . 'assets/newdesign/images/';
$color_image_path = HTTP_HOST . 'uploads/shop/products/thumbs3/';
$in_cart = $this->cart->in_cart($product['id']);
$in_cart_class = (!empty($in_cart)) ? 'btn-disabled-custom' : '';
$disabled_btn = (!empty($in_cart)) ? 'disabled="disabled"' : '';
$cart_btn_text = (!empty($in_cart)) ? 'Товар добавлен в корзину' : 'В корзину';
?>
<!-- Facebook SDK JavaScript -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/ru_RU/sdk.js#xfbml=1&version=v2.7";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
<!-- /Facebook SDK JavaScript -->

<!-- MODAL AREA -->
<div class="modal fade" id="productColorSize" tabindex="-1" role="dialog" aria-labelledby="productColorSizeLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center" id="productColorSizeLabel">Внимание!</h4>
            </div>
            <div class="modal-body">
                <p class="text-center">Пожалуйста, выберите цвет товара, а также укажите ваш размер.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Вернуться к товару</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="productColor" tabindex="-1" role="dialog" aria-labelledby="productColorLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center" id="productColorLabel">Внимание!</h4>
            </div>
            <div class="modal-body">
                <p class="text-center">Пожалуйста, выберите цвет товара.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Вернуться к товару</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="productSize" tabindex="-1" role="dialog" aria-labelledby="productSizeLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center" id="productSizeLabel">Внимание!</h4>
            </div>
            <div class="modal-body">
                <p class="text-center">Пожалуйста, укажите ваш размер.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Вернуться к товару</button>
            </div>
        </div>
    </div>
</div>
<!-- /MODAL AREA -->

<link rel="stylesheet" type="text/css" href="<?=HTTP_HOST; ?>assets/newdesign/fancybox/source/jquery.fancybox.css?v=2.1.5" media="screen" />
<link rel="stylesheet" type="text/css" href="<?=HTTP_HOST; ?>assets/newdesign/fancybox/source/helpers/jquery.fancybox-buttons.css?v=1.0.5" media="screen" />
<link rel="stylesheet" type="text/css" href="<?=HTTP_HOST; ?>assets/newdesign/fancybox/source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" media="screen" />
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
<div class="row">
    <div class="col-lg-6 product-images-block">
        <?php if(empty($this->is_mobile)): ?>
<!--        Product main image-->
        <div class="row">
            <div class="col-lg-12 product-main-image">
                <a class="product-fancybox" href="<?=$original_image_path . $main_image['file_name']; ?>" rel="prodgroup">
                <img src="<?=$desktop_main_image_path . $main_image['file_name']; ?>" alt="<?=$product['title']; ?>" title="<?=$product['title']; ?>"/>
                </a>
            </div>
        </div>
<!--        Product other images-->
        <div class="row product-preview-images">
            <?php
            $pi_i = 0;
            if(!empty($product_images)){
                foreach($product_images as $image){
                    $block_class = ($pi_i == 0) ? 'ppi-block' : 'ppi-block';
                    if(($pi_i > 0) && ($pi_i % 4) == 0){
                        echo '</div><div class="row product-preview-images">';
                        $block_class = 'ppi-block';
                    }
            ?>
            <div class="col-lg-3 <?= $block_class; ?>">
                <a class="product-fancybox" href="<?=$original_image_path . $image['file_name']; ?>" rel="prodgroup">
                <img src="<?=$other_image_path . $image['file_name']; ?>" alt="<?=$product['title']; ?>" title="<?=$product['title']; ?>"/>
                </a>
            </div>
            <?php
                    $pi_i++;
                }
            }
            ?>
        </div>
        <?php else: ?>
<!--            Carousel-->
        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel" data-interval="false">
            <!-- Wrapper for slides -->
            <?php $pim_counter = 0; ?>
            <div class="carousel-inner">
                <div class="item active">
                    <a class="product-fancybox" href="<?=$original_image_path . $main_image['file_name']; ?>" rel="prodgroup">
                        <img src="<?=$mobile_main_image_path . $main_image['file_name']; ?>" alt="<?=$product['title']; ?>" title="<?=$product['title']; ?>"/>
                    </a>
                </div>
                <?php
                if(!empty($product_images)){
                    foreach($product_images as $p_img){
                ?>
                <div class="item">
                    <a class="product-fancybox" href="<?=$original_image_path . $p_img['file_name']; ?>" rel="prodgroup">
                        <img src="<?=$mobile_main_image_path . $p_img['file_name']; ?>" alt="<?=$product['title']; ?> image <?=$pim_counter; ?>" title="<?=$product['title']; ?>"/>
                    </a>
                </div>
                <?php
                        $pim_counter++;
                    }
                }
                ?>
            </div>

            <!-- Indicators -->
            <ol class="carousel-indicators">
                <?php for($sli = 0; $sli <= $pim_counter; $sli++){ ?>
                    <li data-target="#carousel-example-generic" data-slide-to="<?=$sli; ?>" class="<?= ($sli == 0) ? 'active' : ''; ?>"></li>
                <?php } ?>
            </ol>

            <!-- Controls -->
            <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>

        </div>
<!--            /Carousel-->
        <?php endif; ?>
    </div>

    <div class="col-lg-6">
<!--        Product details-->
        <div class="row">
            <div class="col-lg-12">
                <h4 class="product-brand"><?= $brand['title']; ?></h4>
                <p class="product-title"><?= $product['title']; ?></p>
                <div class="product-options">
                <?php
                if(!empty($product_options)){
                    foreach ($product_options as $po_key => $po_option) {
                        if(($po_option['title'] == 'Статус товара') && (strpos($po_option['params'][$product['f_' . $po_key]], 'Предзаказ') !== FALSE)){
                            $filter_class = 'filter-hide';
                            $filter_icon_class = 'glyphicon-plus';
                ?>
                    <div class="product-option-row">
                        <div class="por-title-div pull-left"><?= $po_option['title']; ?>: </div>
                        <div class="por-value-div pull-left">
                            <div class="filter-title" id="ft_<?=$po_key; ?>">
                                <?=$po_option['params'][$product['f_' . $po_key]]; ?>
                                <span class="glyphicon <?= $filter_icon_class; ?> ft-icon pull-right" aria-hidden="true" id="fti_<?=$po_key; ?>"></span>
                            </div>
                            <div class="filter-items <?= $filter_class; ?>" id="fis_<?=$po_key; ?>">
                                <?= $preorder_text; ?>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                <?php
                        }
                        else{
                ?>
                    <div class="product-option-row">
                        <span class="por-title"><?= $po_option['title']; ?>: </span>
                        <span class="por-value"><?= $po_option['params'][$product['f_' . $po_key]]; ?></span>
                    </div>
                <?php
                        }
                    }
                }
                ?>
                </div>
                <p class="product-artikul">Артикул: <?= $product['code']; ?></p>
                <p class="product-price">
                    <span class="pt-title">Цена:</span>
                    <?php
                    // если продукт участвует в текущей активной акции
                    if(!empty($action['percent'])){
                        // реальная цена идёт типа в «старую» (зачеркнута)
                        $product['price_old'] = $product['price'];
                        // акционная цена идёт типа как «реальная»
                        // сумма скидки
                        $sale = ($product['price'] * $action['percent']) / 100;
                        // цена со скидкой
                        $product['price'] = ceil($product['price'] - $sale);
                    }
                    ?>
                    <span class="pt-value"><?= $product['price']; ?> <?= $product_currency; ?></span>
                    <?php if(!empty($product['price_old'])): ?>
                    <span class="pt-value-old"><?= $product['price_old']; ?> <?= $product_currency; ?></span>
                    <?php endif; ?>
                </p>
                <p class="product-description">
                    <?php /*= strip_tags($product['full_description'], '<b><a><em><strong><i><br><br/><p>');*/ ?>
                    <?= strip_tags($product['full_description'], '<br><br/>'); ?>
                </p>
            </div>
        </div>
        <div class="row product-select-options">
            <?php
            if(!empty($colors_images)){
                $color_parts = 3;
                $colors_chunked = array_chunk($colors_images, $color_parts); // разбиваем массив цветов на части по N картинок
            ?>
            <div class="col-lg-6">
                <p>Выберите цвет:</p>
            <?php
                $ck = 0;
                foreach ($colors_chunked as $color_chunk) {
            ?>
            <div class="row select-colors">
                <?php foreach($color_chunk as $color): ?>
                <div class="col-lg-<?=(12 / $color_parts); ?> col-xs-<?=(12 / $color_parts); ?> select-color <?= ($ck == 0) ? $selected_color_class : ''; ?>" id="sc_<?=$color['id']; ?>">
                    <img src="<?=$color_image_path . $color['file_name']; ?>" alt="<?=$product['title']; ?> select color" title="<?=$product['title']; ?> select color"/>
                </div>
                <?php
                    $ck++;
                endforeach;
                ?>
            </div>
            <?php
                } // /$colors_chunked
            ?>
            </div>
            <?php
            }
            ?>
            <?php
            if($product['sizes'] == '0'){
                $sizes = 0;
            }
            else{
                $sizes_str = (strpos($product['sizes'], '0,') === 0) ? substr($product['sizes'], 2) : $product['sizes'];
                $sizes = explode(',', $sizes_str);
            }
            if(!empty($sizes)) {
                //$selected_size = $sizes[0];
                $size_parts = 4;
                $sizes_chunked = array_chunk($sizes, $size_parts);
            ?>
            <div class="col-lg-6">
                <p>Выберите размер:</p>
            <?php
                $sk = 0;
                foreach($sizes_chunked as $size_chunk){
            ?>
            <div class="row select-sizes">
                <?php foreach($size_chunk as $size): ?>
                <div class="col-lg-<?=(12 / $size_parts); ?> col-xs-<?=(12 / $size_parts); ?> select-size">
                    <button class="btn btn-default btn-select-size <?= ($sk == 0) ? $selected_size_class : ''; ?>" id="ss_<?= $size; ?>"><?= $size; ?></button>
                </div>
                <?php
                    $sk++;
                endforeach;
                ?>
            </div>
            <?php
                } // /$sizes_chunked
            ?>
            </div>
            <?php
            }
            ?>
        </div>
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12">
                <?= form_open(base_url('order/add_to_cart'), array('name' => 'add_to_cart')); ?>
                <?= form_hidden('product_id', $product_id); ?>
                <?= form_hidden('colors_available', ((!empty($colors_images)) ? 1 : 0)); ?>
                <?= form_hidden('sizes_available', ((!empty($sizes)) ? 1 : 0)); ?>
                <?= form_hidden('color', $selected_color); ?>
                <?= form_hidden('size', $selected_size); ?>
                <?php
                $pbutton_class = ' mvisible';
                $plink_class = ' minvisible';
                if(!empty($in_cart)){
                    $pbutton_class = ' minvisible';
                    $plink_class = ' mvisible';
                }
                ?>
                <button class="btn btn-orange <?= $in_cart_class . $pbutton_class; ?>" id="add_to_cart" <?=$disabled_btn; ?> type="button"><?= $cart_btn_text; ?></button>
                <a class="btn btn-orange btn-disabled-custom<?=$plink_class; ?>" id="add_to_cart_link" href="<?=base_url('order/cart'); ?>">Товар добавлен в корзину</a>
                <?= form_close(); ?>
            </div>
        </div>
        <div class="row social-comments-block">
            <div class="col-lg-12" id="scb">
                <ul class="nav nav-tabs nav-justified" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#fb_comments" class="sc-fb-link" aria-controls="fb_comments" role="tab" data-toggle="tab">
                            <i class="fa fa-facebook"></i>Facebook
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#vk_comments" class="sc-vk-link" aria-controls="vk_comments" role="tab" data-toggle="tab">
                            <i class="fa fa-vk"></i>VK
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#g_comments" class="sc-gp-link" aria-controls="g_comments" role="tab" data-toggle="tab">
                            <i class="fa fa-google-plus"></i>Google+
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <script type="text/javascript">
                        var elem_gp = document.getElementById('scb');
                        var elem_gp_w = elem_gp.clientWidth || elem_gp.offsetWidth;
                        var cblock_width = elem_gp_w - 30;
                    </script>
                    <div role="tabpanel" class="tab-pane fade in active" id="fb_comments">
                        <div class="social-comments-content">
                            <div class="fb-comments" data-href="<?= current_url(); ?>" data-mobile="true" data-numposts="10"></div>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="vk_comments">
                        <div class="social-comments-content">
                            <div id="vk_comments-block"></div>
                            <script type="text/javascript">
                                VK.Widgets.Comments("vk_comments-block", {limit: 10, width: cblock_width, mini: 1, attach: "*"});
                            </script>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="g_comments">
                        <div class="social-comments-content">
                            <script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
                            <div id="plusonecomments"></div>
                            <script type="text/javascript">
                                gapi.comments.render('plusonecomments', {
                                    href: window.location,
                                    width: cblock_width,
                                    first_party_property: 'BLOGGER',
                                    view_type: 'FILTERED_POSTMOD'
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row category-bottom-slider">
    <div class="col-lg-12">
        <?php
        if(!empty($similar_products)){
            $this->load->view($view_path . 'slick_slider', array('products' => $similar_products)); // похожие продукты
        }
        else{
            echo $this->widgets('content_bottom'); // обычный нижний слайдер
        }
        ?>
    </div>
</div>