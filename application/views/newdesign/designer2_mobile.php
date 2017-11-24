<?php
$sort = array(
    'new' => 'Новинки',
    'popular' => 'По популярности',
    'asc' => 'Начиная с дешевых',
    'desc' => 'Начиная с дорогих',
);
$currencies = array(
    'grn' => 'В гривнах',
    'usd' => 'В долларах США',
    'eur' => 'В евро',
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

<link rel="stylesheet" href="<?=HTTP_HOST; ?>assets/newdesign/selectric/selectric.css"/>
<form action="" name="filtersForm" id="filtersForm" method="get">

<div class="row">
    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 designer-logo text-center">
        <?php if(!empty($designer_logo)): ?>
            <img src="<?= HTTP_HOST . $designer_logo['file_path'] . $designer_logo['file_name']; ?>" alt="<?= $designer['title']; ?>" class="designer-logo-mobile"/>
        <?php endif; ?>
    </div>
    <div class="col-lg-8 col-md-8 col-sm-6 col-xs-12">
        <h1 class="category-title"><?= $designer['title']; ?></h1>
        <div class="row">
            <!--    Описание категории-->
            <?php
            if(!empty($designer['description'])) {
//                $designer['description'] = str_replace('<p>&nbsp;</p>', '', $designer['description']);
//                $designer['description'] = str_replace('<p><span>&nbsp;</span></p>', '', $designer['description']);
                ?>
                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 text-center">
                    <a class="btn btn-custom btn-lg cat-mobile-sorting btn-block" role="button" data-toggle="collapse" href="#collapseAbout" aria-expanded="false" aria-controls="collapseAbout">
                        О БРЕНДЕ
                    </a>
                    <div class="collapse" id="collapseAbout">
                        <div class="well text-left">
                            <?= $designer['description']; ?>
                            <a class="btn btn-custom btn-block mobile-collapse-close" onclick="window.scrollTo(0, 200)" role="button" data-toggle="collapse" href="#collapseAbout" aria-expanded="false" aria-controls="collapseAbout">
                                ЗАКРЫТЬ
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <!--    Подкатегории-->
            <?php if(!empty($subcats)){ ?>
                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 text-center">
                    <a class="btn btn-custom btn-lg cat-mobile-sorting btn-block" role="button" data-toggle="collapse" href="#collapseCategories" aria-expanded="false" aria-controls="collapseCategories">
                        КАТЕГОРИИ
                    </a>
                    <div class="collapse" id="collapseCategories">
                        <div class="well text-left">
                            <ul class="nav nav-pills nav-stacked cat-childs-list">
                                <?php foreach($subcats as $s_cat): ?>
                                    <li role="presentation">
                                        <a href="<?= base_url($s_cat['url'] . '?pg=0&brand=' . $designer['id']); ?>"><?= $s_cat['title']; ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <a class="btn btn-custom btn-block mobile-collapse-close" onclick="window.scrollTo(0, 200)" role="button" data-toggle="collapse" href="#collapseCategories" aria-expanded="false" aria-controls="collapseAbout">
                                ЗАКРЫТЬ
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <!--    Сортировка-->
            <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 text-center">
                <a class="btn btn-custom btn-lg cat-mobile-sorting btn-block" role="button" data-toggle="collapse" href="#collapseSortings" aria-expanded="false" aria-controls="collapseSortings">
                    СОРТИРОВКА
                </a>
                <div class="collapse" id="collapseSortings">
                    <div class="well text-left">
                        <!--        Top filters-->
                        <div class="row cat-mobile-sorting">
                            <div class="col-lg-12">
                                <select name="sort" id="sort" class="sel-filter">
                                    <?php foreach($sort as $sitem_key => $sitem){ ?>
                                        <?php $selected = (isset($filters_checked['sort']) && ($filters_checked['sort'] == $sitem_key)) ? 'selected' : ''; ?>
                                        <option value="<?= $sitem_key; ?>" <?=$selected; ?>><?= $sitem; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row cat-mobile-sorting">
                            <div class="col-lg-12">
                                <select name="currency" id="currency" class="sel-filter">
                                    <?php foreach($currencies as $citem_key => $citem){ ?>
                                        <?php $selected = (isset($filters_checked['currency']) && ($filters_checked['currency'] == $citem_key)) ? 'selected' : ''; ?>
                                        <option value="<?= $citem_key; ?>" <?=$selected; ?>><?= $citem; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <!--        / Top filters-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
<div class="row">
    <div class="col-lg-12">
        <div class="top-pg">
            <?=$pagination; ?>
        </div>
    </div>
</div>
<!-- Products -->
<div class="row category-products">
    <?php if(!empty($products)){ ?>
        <?php foreach($products as $pk => $product){ ?>
            <?php
//            if(($pk > 0) && (($pk % 3) == 0)){
//                echo '</div><div class="row">';
//            }
            // URL товара: либо по конечной категории товара – либо по текущей категории, если вдруг что
            $cat_product_url_base = $products_urls[$product['id']]['url'];
            ?>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6">
                <div class="thumbnail cat-product-thumbnail">
                    <a href="<?=base_url($cat_product_url_base . $product['name'] . '-' . $product['id'] . '.html'); ?>" title="<?=$product['title']; ?>">
                        <?php /*/ базовый вариант с 1-м фото ?>
                        <img src="<?=$product['images'][0]; ?>" alt="<?=$product['title']; ?>">
                    <?php /*/ ?>
                        <?php /* // вариант 1 ?>
                    <div class="crossfade">
                        <img src="<?=$product['images'][0]; ?>" alt="<?=$product['title']; ?>" class="regular">
                        <img src="<?=$product['images'][1]; ?>" alt="<?=$product['title']; ?>" class="rollover">
                    </div>
                    <?php /*/ ?>
                        <?php // вариант 2 - оптимальный ?>
                        <div class="animate2">
                            <?php /*img class="first" src="<?=$product['images'][1]; ?>" alt="<?=$product['title']; ?>" /*/?>
                            <img class="second__" src="<?=$product['images'][0]; ?>" alt="<?=$product['title']; ?>" />
                        </div>
                        <?php // ?>
                        <div class="caption">
                            <div class="cat-product-title"><?=$product['title']; ?></div>
                            <div class="cat-product-prices">
                                <?php
                                // если продукт участвует в текущей активной акции
                                if(!empty($action['products'][$product['id']]['percent'])){
                                    // реальная цена идёт типа в «старую» (зачеркнута)
                                    $product['price_old'] = $product['price'];
                                    // акционная цена идёт типа как «реальная»
                                    // сумма скидки
                                    $sale = ($product['price'] * $action['products'][$product['id']]['percent']) / 100;
                                    // цена со скидкой
                                    $product['price'] = ceil($product['price'] - $sale);
                                }
                                ?>
                                <?php
                                $price_old = $product['price'];
                                $price_old_class = ' vhidden';
                                if(!empty($product['price_old'])){
                                    $price_old = $product['price_old'];
                                    $price_old_class = '';
                                }
                                ?>
                                <span class="cat-prod-price-span"><?=$product['price'] . ' ' . $products_currency; ?></span>
                                <span class="cat-prod-price-span cat-prod-price-old<?=$price_old_class; ?>"><?=$price_old . ' ' . $products_currency; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="col-lg-8 col-md-offset-2 cat-we-sorry">
            <h4>К сожалению, в нашем Каталоге временно отсутствуют товары дизайнера <?= $designer['title']; ?>.</h4>
        </div>
    <?php } ?>
</div>
<!-- / Products -->
<div class="top-pg">
    <?=$pagination; ?>
</div>