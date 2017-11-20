<?php
/**
 * @var $categories
 * @var $selected_cat
 * @var $products
 * @var $pagination
 * @var $query_string
 * @var $total_products
 * @var $data_type
 * @var $action_products
 * @var $flash
 * @var $currencies
 */
//$currencies = array(
//    'usd' => '$',
//    'eur' => '€',
//    'grn' => '₴',
//);
$header = (!empty($data_type)) ? (($data_type == 'category') ? ' в категории' : ' в акции') : '';
?>

<?php if(!empty($flash)): ?>
<div class="row no-ml">
    <div class="col-md-4 col-md-offset-4" style="position: relative;">
        <div class="alert alert-success alert-dismissible" id="action_flash_block" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="text-center"><?= $flash; ?></div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row no-ml">
    <div class="col-md-12">
        <h4>Товары<?= $header; ?>:</h4>
    </div>
</div>

<div class="row no-ml">
    <div class="col-md-3">
        <form action="/admin/" name="chooseCat">
            <input type="hidden" name="m" value="action">
            <input type="hidden" name="a" value="setup">
            <div class="form-group">
                <label for="choose_category">Выберите категорию товаров</label>
                <select name="category_id" id="choose_category" class="form-control" onchange="document.chooseCat.submit();">
                    <option value="0">---</option>
                <?php
                if(!empty($categories)):
                    foreach($categories as $cat_k => $cat_t):
                        $selected = ($selected_cat == $cat_k) ? 'selected="selected"' : '';
                ?>
                    <option value="<?= $cat_k; ?>" <?= $selected; ?>><?= $cat_t; ?></option>
                <?php
                    endforeach;
                endif;
                ?>
                </select>
            </div>
        </form>
    </div>
    <div class="col-md-5 clearfix">
        <span class="action-products-count pull-left">
            <?= (!empty($total_products))
                ? $total_products . ' ' . pluralForm($total_products, 'товар', 'товара', 'товаров')
                : '';
            ?>
        </span>
        <?php if(!empty($action_products)): ?>
        <a href="/admin/?m=action&a=setup&show=all" class="btn btn-default show-all-btn pull-right">Показать все товары, участвующие в акции</a>
        <?php endif; ?>
    </div>
    <div class="col-md-4 text-right">
        <?php if(!empty($products)): ?>
        <form action="/admin/" class="form-inline" name="formActions">
            <?php
            if(!empty($get)):
                foreach ($get as $gk => $gv):
            ?>
            <input type="hidden" name="<?= $gk; ?>" value="<?= $gv; ?>">
            <?php
                endforeach;
            endif;
            ?>
            <div class="form-group" style="margin-top: 25px;">
                <label for="choose_product_action">С отмеченными:</label>
                <select id="choose_product_action" class="form-control" onchange="getAction();">
                    <option value="0">---</option>
                    <option value="1">Добавить в акцию</option>
                    <option value="2">Исключить из акции</option>
                    <option value="3">Установить общую скидку</option>
                    <option value="4">Установить заданные скидки</option>
                    <?php if(!empty($selected_cat)): ?>
                    <option value="5">Добавить всю категорию в акцию</option>
                    <option value="6">Удалить всю категорию из акции</option>
                    <?php endif; ?>
                    <?php if(!empty($action_products)): ?>
                    <option value="7">Удалить все товары из акции</option>
                    <?php endif; ?>
                </select>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php if(!empty($products)): ?>
<div class="row no-ml">
    <div class="col-md-12">
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th style="text-align: center;">
                    <input type="checkbox" id="checkall">
                </th>
                <th>ID</th>
                <th>Название</th>
                <th>Цена</th>
                <th>Скидка по акции</th>
                <th>Цена со скидкой</th>
                <th>Задать скидку, %</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach($products as $product):
                // конвертим все цены в грн
                if(
                    ($product['currency'] !== 'grn') &&
                    !empty($currencies[$product['currency'] . '_grn'])
                ){
                    $product['price'] = ceil($product['price'] * $currencies[$product['currency'] . '_grn']);
                }
            ?>
            <tr>
                <td class="check-batch">
                    <input type="checkbox" class="batch-checkbox"
                           id="ch_<?= $product['id']; ?>" value="p<?= $product['id']; ?>">
                </td>
                <td><?= $product['id']; ?></td>
                <td>
                    <a href="/admin/?m=shop&a=edit_product&id=<?= $product['id']; ?>" target="_blank">
                        <?= $product['title']; ?>
                    </a>
                </td>
                <td><?= $product['price'] . ' грн'; ?></td>
                <td>
                    <?= (!empty($action_products[$product['id']]['percent']))
                        ? $action_products[$product['id']]['percent'] . '%'
                        : '';
                    ?>
                </td>
                <td>
                    <?php
                    if(!empty($product['price']) && !empty($action_products[$product['id']]['percent'])){
                        // сумма скидки
                        $sale = ($product['price'] * $action_products[$product['id']]['percent']) / 100;
                        // цена со скидкой
                        $price = ceil($product['price'] - $sale) . ' грн';
                    }
                    else{
                        $price = '';
                    }
                    ?>
                    <?= $price; ?>
                </td>
                <td class="percent-batch">
                    <input type="text" class="form-control batch-percent"
                           id="bpc_<?= $product['id']; ?>" name="bpc[<?= $product['id']; ?>]"
                           onclick="checkCheckbox('ch_<?= $product['id']; ?>');">
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row no-ml">
    <div class="col-md-12">
        <?= $pagination; ?>
    </div>
</div>
<?php elseif(!empty($data_type)): ?>
    <div class="row no-ml">
        <div class="col-md-12 text-center">
            <h5>Нет товаров<?= $header; ?> для отображения</h5>
        </div>
    </div>
<?php endif; ?>

<script>
    $(function () {
        // переключаем все чекбоксы
        $('#checkall').click(function() {
            var checked = $(this).prop('checked');
            $('.batch-checkbox').prop('checked', checked);
        });
    });

    // обрабатываем множественный выбор
    function getAction() {
        var action = $('#choose_product_action').val();
        var ids = '';
        var percents = '';
        $('.batch-checkbox:checkbox:checked').each(function () {
            var tval = this.value.replace('p', '');
            ids = (ids === '') ? ids+tval : ids+','+tval; // ID товаров через запятую
            var tid = this.id.split('_');
            var tper = $('#bpc_'+tid[1]).val();
            if((tper !== '') && (tper !== undefined)){
                percents = (percents === '')
                    ? percents+tid[1]+'_'+tper
                    : percents+','+tid[1]+'_'+tper; // скидки для товаров через запятую
            }
        });
        // если указаны ID товаров для работы
        // или же действие имеет идентификатор > 4
        if((ids !== '') || (action*1 > 4)){
            $.post(
                '/admin/?m=action&a=batch',
                {
                    action: action,
                    ids: ids,
                    percents: percents,
                    category_id: <?= (!empty($selected_cat)) ? $selected_cat : 0; ?>
                },
                function (data) {
                    if(data*1 > 0){
                        document.formActions.submit(); // отправляем фейковую форму для перезагрузки страницы
                    }
                },
                'text'
            );
        }
    }

    function checkCheckbox(target) {
        $('#'+target).prop('checked', true);
    }

    function hideFlash() {
        $('#action_flash_block').show().delay(4000).fadeOut();
    }
    hideFlash();
</script>
