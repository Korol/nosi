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
<div class="row">
    <div class="col-xs-12">
        <h1 class="category-title"><?= $category_info['title']; ?></h1>
    </div>
</div>

<link rel="stylesheet" href="<?=HTTP_HOST; ?>assets/newdesign/selectric/selectric.css"/>
<form action="" name="filtersForm" id="filtersForm" method="get">
    <input type="hidden" name="pg" value="<?=(isset($_GET['pg'])) ? (int)$_GET['pg'] : 0; ?>"/>

<div class="row">
<!--    Описание категории-->
    <?php
    // black friday
    $current_ts = time();
    $end_ts = mktime(23, 59, 59, 11, 24, 2017);
    if($current_ts <= $end_ts):
        ?>
        <div class="col-lg-12">
            <img src="/uploads/banners/black_friday.jpg" style="margin: 10px auto 20px;" class="img-responsive" alt="Black Friday Banner"/>
        </div>
    <?php else: ?>
    <?php
    if(!empty($category_description)) {
        ?>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-center">
            <a class="btn btn-custom cat-mobile-sorting btn-block" role="button" data-toggle="collapse" href="#collapseAbout" aria-expanded="false" aria-controls="collapseAbout">
                О КАТЕГОРИИ
            </a>
            <div class="collapse" id="collapseAbout">
                <div class="well text-left">
                    <?= $category_description; ?>
                    <a class="btn btn-custom btn-block" onclick="window.scrollTo(0, 200)" role="button" data-toggle="collapse" href="#collapseAbout" aria-expanded="false" aria-controls="collapseAbout">
                        ЗАКРЫТЬ
                    </a>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php endif; ?>
<!--    Категории-->
<?php if(!empty($subcats)){ ?>
    <div class="col-lg-4 col-lg-offset-2 col-md-4 col-md-offset-2 col-sm-4 col-sm-offset-2 col-xs-12 text-center">
        <a class="btn btn-custom cat-mobile-sorting btn-block" role="button" data-toggle="collapse" href="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters">
            КАТЕГОРИИ
        </a>
        <div class="collapse" id="collapseFilters">
            <div class="well text-left">
                <!-- Categories -->
                <ul class="nav nav-pills nav-stacked cat-childs-list">
                    <?php foreach($subcats as $subcat): ?>
                        <li role="presentation"><a href="<?=base_url($subcats_url . '/?subcategory=' . $subcat['extra_id']); ?>"><?= $subcat['title']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <!-- / Categories -->
            </div>
        </div>
    </div>
<?php } ?>
<?php if(!empty($subcategories)){ ?>
    <div class="col-lg-4 col-lg-offset-2 col-md-4 col-md-offset-2 col-sm-4 col-sm-offset-2 col-xs-12 text-center">
        <a class="btn btn-custom cat-mobile-sorting btn-block" role="button" data-toggle="collapse" href="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters">
            КАТЕГОРИИ
        </a>
        <div class="collapse" id="collapseFilters">
            <div class="well text-left">
                <!-- Categories -->
                <ul class="nav nav-pills nav-stacked cat-childs-list">
                    <?php foreach($subcategories as $subcatitem): ?>
                        <li role="presentation"><a href="<?=base_url($subcatitem['url']); ?>"><?= $subcatitem['title']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <!-- / Categories -->
            </div>
        </div>
    </div>
<?php } ?>
<!--    Сортировка-->
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-center">
        <a class="btn btn-custom cat-mobile-sorting btn-block" role="button" data-toggle="collapse" href="#collapseSortings" aria-expanded="false" aria-controls="collapseSortings">
            СОРТИРОВКА
        </a>
        <div class="collapse" id="collapseSortings">
            <div class="well text-left">
<!--        Top filters-->
                <div class="row cat-mobile-sorting">
                    <div class="col-lg-12">
                        <select name="brand" id="brand" class="sel-filter-e form-control" onchange="document.filtersForm.submit();">
                            <option value="">-- Все бренды --</option>
                            <?php foreach($brands as $bitem_key => $bitem){ ?>
                                <?php $selected = (isset($filters_checked['brand']) && ($filters_checked['brand'] == $bitem_key)) ? 'selected' : ''; ?>
                                <option value="<?= $bitem_key; ?>" <?=$selected; ?>><?= $bitem['title']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="row cat-mobile-sorting">
                    <div class="col-lg-12">
                        <select name="sort" id="sort" class="sel-filter-e form-control" onchange="document.filtersForm.submit();">
                            <?php foreach($sort as $sitem_key => $sitem){ ?>
                                <?php $selected = (isset($filters_checked['sort']) && ($filters_checked['sort'] == $sitem_key)) ? 'selected' : ''; ?>
                                <option value="<?= $sitem_key; ?>" <?=$selected; ?>><?= $sitem; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="row cat-mobile-sorting">
                    <div class="col-lg-12">
                        <select name="currency" id="currency" class="sel-filter-e form-control" onchange="document.filtersForm.submit();">
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
    <?php if(!empty($category_products)){ ?>
        <?php foreach($category_products as $pk => $product){ ?>
            <?php
//            if(($pk > 0) && (($pk % 3) == 0)){
//                echo '</div><div class="row">';
//            }
            // URL товара: либо по конечной категории товара – либо по текущей категории, если вдруг что
            $cat_product_url_base = (!empty($products_urls[$product['id']]['url'])) ? $products_urls[$product['id']]['url'] : $category_info['url_structure']['url'];
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
                                $price_old = $product['price'];
                                $price_old_class = ' vhidden';

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

                                if(!empty($product['price_old'])){
                                    $price_old = $product['price_old'];
                                    $price_old_class = '';
                                }
                                ?>
                                <span class="cat-prod-price-span"><?=$product['price'] . ' ' . $products_currency; ?></span><br/>
                                <span class="cat-prod-price-span cat-prod-price-old<?=$price_old_class; ?>"><?=$price_old . ' ' . $products_currency; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="col-lg-12 cat-we-sorry">
            <h4>К сожалению, в нашем Каталоге временно отсутствуют товары, соответствующие указанным Вами критериям.</h4>
            <h4>Пожалуйста, измените критерии фильтров – и мы обязательно найдём лучшие товары для Вас!</h4>
        </div>
    <?php } ?>
</div>
<!-- / Products -->
<div class="top-pg">
    <?=$pagination; ?>
</div>

<div class="row category-bottom-slider">
    <div class="col-lg-12">
        <?php echo $this->widgets('content_bottom'); ?>
    </div>
</div>