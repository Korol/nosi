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

<?php if(!empty($cart)): ?>
    <?= form_open('order/checkout'); ?>
    <div class="row">
        <div class="col-lg-12">
            <h2 class="text-center order-header">Ваша Корзина</h2>
            <div class="mobile-cart-block">
            <?php foreach($cart as $c_row): ?>
                <?php
                $photo = (!empty($products[$c_row['id']]['file_name']))
                    ? '<img class="cart-product-img" src="/uploads/shop/products/thumbs3/' . $products[$c_row['id']]['file_name'] . '" alt="' . $products[$c_row['id']]['title'] . '"/>'
                    : '';
                $title = $products[$c_row['id']]['title'];
                $code = (!empty($products[$c_row['id']]['code'])) ? '<br/><span class="m-code">Артикул: ' . $products[$c_row['id']]['code'] . '</span>' : '';
                $color = (!empty($c_row['options']['color']) && !empty($colors_info[$c_row['id']]['file_name']))
                    ? '<img class="cart-color-img" src="/uploads/shop/products/thumbs3/' . $colors_info[$c_row['id']]['file_name'] . '" alt="' . $products[$c_row['id']]['title'] . '"/>'
                    : '';
                $size = (!empty($c_row['options']['size'])) ? $c_row['options']['size'] : '';
                $price = '<span class="m-price-hidden" id="spprice_' . $c_row['id'] . '">' . $c_row['price'] . '</span>';
                $qty = $c_row['qty'];
                $cost = '<span class="m-cart-cost" id="spcost_' . $c_row['id'] . '">' . ($c_row['price'] * $c_row['qty']) . '</span> ' . $currency_mark;
                ?>
                <div class="row mobile-cart-row" id="tccrt_<?=$c_row['id']; ?>">
                    <div class="col-xs-5">
                        <?= (!empty($color)) ? $color : $photo; ?>
                    </div>
                    <div class="col-xs-7">
                        <div class="row">
                            <div class="col-xs-12 clearfix mobile-cr-topline">
                                <div class="mobile-cart-code pull-left"><?= $code; ?></div>
                                <div class="modile-cart-del pull-right" onclick="remove_from_cart(<?=$c_row['id']; ?>);">
                                    <span class="m-del">&times;</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <?= $title . $price; ?>
                            </div>
                        </div>
                        <div class="row mobile-cart-qty">
                            <div class="col-xs-12">
                                <div class="cart-table-qty-block">
                                    <div class="input-group">
                                      <span class="input-group-btn">
                                          <button type="button" class="btn btn-number cbtn-minus" id="minus_<?=$c_row['id'];?>" data-type="minus" data-field="qty[<?=$c_row['id'];?>]">
                                              <span class="glyphicon glyphicon-minus"></span>
                                          </button>
                                      </span>
                                    <input type="text" name="qty[<?=$c_row['id'];?>]" id="qty_<?=$c_row['id'];?>" class="form-control input-number cart-table-qty-input" value="<?=$qty; ?>" min="1" max="10" maxlength="2" readonly>
                                      <span class="input-group-btn">
                                          <button type="button" class="btn btn-number cbtn-plus" id="plus_<?=$c_row['id'];?>" data-type="plus" data-field="qty[<?=$c_row['id'];?>]">
                                              <span class="glyphicon glyphicon-plus"></span>
                                          </button>
                                      </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 m-cart-cost"><?= $cost; ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
                <div class="row">
                    <div class="col-lg-12 m-cart-cost">
                        Итого: <span class="tcc-total-price-value m-cart-cost" id="tcctpvt"><?= $cart_total; ?></span> <span class="tcc-total-price-currency m-cart-cost"><?= $currency_mark; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 text-center cart-checkout-area">
            <button class="btn btn-orange" id="table_checkout" type="submit">оформить заказ</button>
        </div>
    </div>
    <?= form_close(); ?>
<?php else: ?>
    <div class="row">
        <div class="col-lg-12">
            <h2 class="text-center order-header">Ваша Корзина</h2>
            <h4>Ваша корзина пуста.</h4>
        </div>
    </div>
<?php endif; ?>
<div class="row category-bottom-slider">
    <div class="col-lg-12">
        <?php echo $this->widgets('content_bottom'); ?>
    </div>
</div>