<?php
//var_dump($category_info);
//var_dump($sub_categories);
//var_dump($category_products);
//var_dump($filters);
//var_dump($brands);
//$filters_checked = array(
//    43 => array(0),
//    44 => array(0, 2),
//);
//var_dump($filters_checked);
// filters
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
//var_dump($breadcrumbs);
//var_dump($category_childs);
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
    <div class="col-lg-2"></div>
    <div class="col-lg-8 category-info">
        <h1 class="category-title"><?= $category_info['title']; ?></h1>
        <?php
        if(!empty($category_info['description']) && empty($this->is_mobile)) {
//            $category_info['description'] = str_replace('<p>&nbsp;</p>', '', $category_info['description']);
//            $category_info['description'] = str_replace('<p><span>&nbsp;</span></p>', '', $category_info['description']);
        ?>
        <div id="block-text">
            <div id="size-text">
                <?= $category_info['description']; ?>
            </div>
        </div>
        <button class="btn btn-orange" id="cat_descr">Подробнее</button>
        <?php } ?>
    </div>
    <div class="col-lg-2"></div>
</div>

<link rel="stylesheet" href="<?=HTTP_HOST; ?>assets/newdesign/selectric/selectric.css"/>
<form action="" name="filtersForm" id="filtersForm" method="get">
<input type="hidden" name="pg" value="<?=(isset($_GET['pg'])) ? (int)$_GET['pg'] : 0; ?>"/>

<div class="row">
    <div class="col-lg-9 col-lg-push-3">
<!--        Top filters-->
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-lg-4">
                        <select name="brand" id="brand" class="sel-filter">
                            <option value="">-- Все бренды --</option>
                            <?php foreach($brands as $bitem_key => $bitem){ ?>
                                <?php $selected = (isset($filters_checked['brand']) && ($filters_checked['brand'] == $bitem_key)) ? 'selected' : ''; ?>
                                <option value="<?= $bitem_key; ?>" <?=$selected; ?>><?= $bitem['title']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <select name="sort" id="sort" class="sel-filter">
                        <?php foreach($sort as $sitem_key => $sitem){ ?>
                            <?php $selected = (isset($filters_checked['sort']) && ($filters_checked['sort'] == $sitem_key)) ? 'selected' : ''; ?>
                            <option value="<?= $sitem_key; ?>" <?=$selected; ?>><?= $sitem; ?></option>
                        <?php } ?>
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <select name="currency" id="currency" class="sel-filter">
                            <?php foreach($currencies as $citem_key => $citem){ ?>
                                <?php $selected = (isset($filters_checked['currency']) && ($filters_checked['currency'] == $citem_key)) ? 'selected' : ''; ?>
                                <option value="<?= $citem_key; ?>" <?=$selected; ?>><?= $citem; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">

                <div class="top-pg">
                    <?=$pagination; ?>
                </div>
            </div>
        </div>
<!--        / Top filters-->
        <!-- Products -->
        <div class="row category-products">
            <?php if(!empty($category_products)){ ?>
            <?php foreach($category_products as $pk => $product){ ?>
                    <?php
                    if(($pk > 0) && (($pk % 3) == 0)){
                        echo '</div><div class="row">';
                    }
                    // URL товара: либо по конечной категории товара – либо по текущей категории, если вдруг что
                    $cat_product_url_base = (!empty($products_urls[$product['id']]['url'])) ? $products_urls[$product['id']]['url'] : $category_info['url_structure']['url'];
                    ?>
            <div class="col-lg-4">
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
                            <img class="first" src="<?=$product['images'][1]; ?>" alt="<?=$product['title']; ?>" />
                            <img class="second" src="<?=$product['images'][0]; ?>" alt="<?=$product['title']; ?>" />
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
                        <?php if(!empty($product['price_old'])): ?>
                            <span class="cat-prod-price-span cat-prod-price-old"><?=$product['price_old'] . ' ' . $products_currency; ?></span>
                        <?php endif; ?>
                            <span class="cat-prod-price-span"><?=$product['price'] . ' ' . $products_currency; ?></span>
                        </div>
                    </div>
                    </a>
                </div>
            </div>
            <?php } ?>
            <?php } else { ?>
            <div class="col-lg-8 col-md-offset-2 cat-we-sorry">
                <h4>К сожалению, в нашем Каталоге временно отсутствуют товары, соответствующие указанным Вами критериям.</h4>
                <h4>Пожалуйста, измените критерии фильтров – и мы обязательно найдём лучшие товары для Вас!</h4>
            </div>
            <?php } ?>
        </div>
        <!-- / Products -->
        <div class="top-pg">
            <?=$pagination; ?>
        </div>
    </div>
    <div class="col-lg-3 col-lg-pull-9">
        <!-- Filters -->
        <?php if(!empty($filters) && empty($category_childs)){ ?>
            <?php foreach($filters as $filter){ ?>
                <?php
                $filter_class = 'filter-hide';
                $filter_icon_class = 'glyphicon-plus';
                if(isset($filters_checked['left'][$filter['id']]) || !empty($this->is_mobile)){
                    $filter_class = 'filter-show';
                    $filter_icon_class = 'glyphicon-minus';
                }
                ?>
            <div class="filter-title" id="ft_<?=$filter['id']; ?>">
                <?=$filter['title']; ?>
                <span class="glyphicon <?= $filter_icon_class; ?> ft-icon pull-right" aria-hidden="true" id="fti_<?=$filter['id']; ?>"></span>
            </div>
            <div class="filter-items <?= $filter_class; ?>" id="fis_<?=$filter['id']; ?>">
                <?php foreach($filter['params'] as $fp_key => $fp_value){ ?>
                    <?php
                    $checked = (isset($filters_checked['left'][$filter['id']]) && in_array($fp_key, $filters_checked['left'][$filter['id']])) ? 'checked' : '';
                    ?>
                <div class="filter-item checkbox">
                    <label>
                        <input class="check-filter" type="checkbox" name="left[<?=$filter['id']; ?>][]" value="<?=$fp_key; ?>" <?=$checked; ?> /> <?= $fp_value; ?>
                    </label>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
            <?php if(!empty($this->is_mobile)): ?>
                <div class="cat-filters-reset">
                    <button class="btn btn-custom" type="submit">Показать</button>
                </div>
            <?php endif; ?>
            <?php if(!$no_filters): ?>
            <div class="cat-filters-reset">
                <a class="btn btn-custom" href="<?= base_url($category_info['url_structure']['url']); ?>">Сбросить все фильтры</a>
            </div>
            <?php endif; ?>
        <?php
        }
        else if(!empty($category_childs)){
        ?>
            <ul class="nav nav-pills nav-stacked cat-childs-list">
            <?php foreach($category_childs as $cat_child): ?>
                <li role="presentation"><a href="<?=base_url($cat_child['url']); ?>"><?= $cat_child['title']; ?></a></li>
            <?php endforeach; ?>
            </ul>
        <?php
        }
        ?>
        <!-- / Filters -->
    </div>
</div>

</form>

<div class="row category-bottom-slider">
    <div class="col-lg-12">
        <?php echo $this->widgets('content_bottom'); ?>
    </div>
</div>