<?php
/**
 * @var $cart_total
 * @var $cart_content
 * @var $currency_mark
 * @var $items_in_cart
 */
//var_dump($cart_content, $cart_total, $currency_mark, $items_in_cart);
?>
<li>
    <?php if(!empty($items_in_cart)): ?>
    <div class="top-cart-content">
        <?php foreach($cart_content as $row): ?>
        <div class="tcc-row" id="tccr_<?=$row['id']; ?>">
            <div class="tccr-img">
                <img src="/uploads/shop/products/thumbs3/<?= $row['file_name']; ?>" alt="<?=$row['title']; ?>"/>
            </div>
            <div class="tccr-title"><span class="tccr-title-text"><?=$row['title']; ?></span></div>
            <div class="tccr-price text-center"><?=$row['price']; ?> <?= $currency_mark; ?></div>
            <div class="tccr-del text-right" onclick="remove_from_cart(<?=$row['id']; ?>);">&times;</div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="top-cart-checkout">
        <span class="tcc-total-price-title pull-left">Всего : <span class="tcc-total-price-value" id="tcctpv"><?= $cart_total; ?></span> <span class="tcc-total-price-currency"><?= $currency_mark; ?></span></span>
        <a href="<?=base_url('order/cart'); ?>" class="btn btn-orange pull-right" id="top_checkout">оформить</a>
        <div class="clearfix"></div>
    </div>
    <?php else: ?>
    <p class="text-center">Ваша Корзина пуста</p>
    <?php endif; ?>
</li>