<?php
/**
 * @see Designers::view
 * @var Designers $designer
 * @var Designers $categories
 * @var Designers $designer_logo
 */
//var_dump($designer, $categories, $designer_logo);
//var_dump($products);
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
    <div class="col-lg-3 designer-logo">
        <?php if(!empty($designer_logo)): ?>
        <img src="<?= HTTP_HOST . $designer_logo['file_path'] . $designer_logo['file_name']; ?>" alt="<?= $designer['title']; ?>"/>
        <?php endif; ?>
    </div>
    <div class="col-lg-9 category-info designer-info">
        <h1 class="category-title"><?= $designer['title']; ?></h1>
        <?php
        if(!empty($designer['description'])) {
//            $designer['description'] = strip_tags($designer['description'], '<p>');
//            $designer['description'] = str_replace('<p>&nbsp;</p>', '', $designer['description']);
//            $designer['description'] = str_replace('<p><span>&nbsp;</span></p>', '', $designer['description']);
            ?>
            <div id="block-text">
                <div id="size-text">
                    <?= $designer['description']; ?>
                </div>
            </div>
<!--            <div class="description-blur"></div>-->
            <button class="btn btn-orange" id="cat_descr">Подробнее</button>
        <?php } ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-9 col-lg-push-3">
        <!-- Products -->
        <div class="row category-products">
            <?php if(!empty($products)){ ?>
                <?php foreach($products as $pk => $product){ ?>
                    <?php
                    if(($pk > 0) && (($pk % 3) == 0)){
                        echo '</div><div class="row">';
                    }
                    // URL товара: либо по конечной категории товара – либо по текущей категории, если вдруг что
                    $cat_product_url_base = $products_urls[$product['id']]['url'];
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
                    <h4>К сожалению, в нашем Каталоге временно отсутствуют товары дизайнера <?= $designer['title']; ?>.</h4>
                </div>
            <?php } ?>
        </div>
        <!-- / Products -->
        <div class="top-pg">
            <?=$pagination; ?>
        </div>
    </div>
    <div class="col-lg-3 col-lg-pull-9">
        <?php if(!empty($categories)): ?>
            <ul class="nav nav-pills nav-stacked cat-childs-list designer-categories-list">
                <?php foreach($categories as $category_row): ?>
                    <li role="presentation">
                        <a href="<?=base_url($category_row['url'] . '?pg=0&brand=' . $designer['id']); ?>">
                            <?= $category_row['title']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>