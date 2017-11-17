<?php
/**
 * @var $cart
 * @var $cart_total
 * @var $products
 * @var $colors_info
 * @var $currency_mark
 */
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
        <table class="table cart-table" cellpadding="20">
            <thead>
                <th>фото</th>
                <th>название</th>
                <th>цвет</th>
                <th>размер</th>
                <th>цена</th>
                <th>количество</th>
                <th>стоимость</th>
                <th>удалить</th>
            </thead>
            <tbody>
            <?php
            foreach($cart as $c_row):
                $photo = (!empty($products[$c_row['id']]['file_name']))
                    ? '<img class="cart-product-img" src="/uploads/shop/products/thumbs3/' . $products[$c_row['id']]['file_name'] . '" alt="' . $products[$c_row['id']]['title'] . '"/>'
                    : '';
                $title = $products[$c_row['id']]['title'];
                $code = (!empty($products[$c_row['id']]['code'])) ? '<br/><span>артикул: ' . $products[$c_row['id']]['code'] . '</span>' : '';
                $color = (!empty($c_row['options']['color']) && !empty($colors_info[$c_row['id']]['file_name']))
                    ? '<img class="cart-color-img" src="/uploads/shop/products/thumbs3/' . $colors_info[$c_row['id']]['file_name'] . '" alt="' . $products[$c_row['id']]['title'] . '"/>'
                    : '';
                $size = (!empty($c_row['options']['size'])) ? $c_row['options']['size'] : '';
                $price = '<span id="spprice_' . $c_row['id'] . '">' . $c_row['price'] . '</span> ' . $currency_mark;
                $qty = $c_row['qty'];
                $cost = '<span id="spcost_' . $c_row['id'] . '">' . ($c_row['price'] * $c_row['qty']) . '</span> ' . $currency_mark;
             ?>
                <tr id="tccrt_<?=$c_row['id']; ?>">
                    <td><?= $photo; ?></td>
                    <td class="cart-table-title"><?= $title . $code; ?></td>
                    <td><?= $color; ?></td>
                    <td class="text-center cart-table-size"><?= $size; ?></td>
                    <td><?= $price; ?></td>
                    <td class="text-center">
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
                    </td>
                    <td><?= $cost; ?></td>
                    <td class="text-center cart-table-del" onclick="remove_from_cart(<?=$c_row['id']; ?>);">&times;</td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="5">&nbsp;</td>
                <td class="cart-table-summary" colspan="3">Итого: <span class="tcc-total-price-value" id="tcctpvt"><?= $cart_total; ?></span> <span class="tcc-total-price-currency"><?= $currency_mark; ?></span></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="row">
    <div class="col-lg-12 text-right cart-checkout-area">
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