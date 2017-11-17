<style type="text/css">
    .products_list {
        margin: 20px 0 30px;
    }
    .pl-ul-item {
        /*height: 25px;*/
        padding: 10px 15px;
        background-color: #fff;
        border: 1px solid #000;
        margin: 15px 0;
        font-size: 14px;
        cursor: move;
    }
    .pl-ul-li-text {
        margin-left: 15px;
    }
    .pl-explain {
        font-style: italic;
        margin-bottom: 20px;
    }
    .pl-ul-li-img {
        width: 50px;
        height: 50px;
        margin-left: 10px;
        border: 1px solid #989FA9;
    }
</style>
<?php if(!empty($items)): ?>
<div class="products_list">
    <div class="pl-explain">(для изменения последовательности показа товаров – просто перетащите блок с товаром на нужную позицию (вверх или вниз))</div>
    <ul id="sortable_list">
    <?php foreach($items as $item): ?>
        <li class="pl-ul-item" style="" id="items_<?= $item['id']; ?>">
            <i class="icon-resize-vertical"></i>
            <?php if(!empty($images[$item['id']])): ?>
            <img class="pl-ul-li-img" src="/uploads/shop/products/thumbs3/<?= $images[$item['id']]['image']; ?>" alt="Item img <?= $item['id']; ?>"/>
            <?php endif; ?>
            <span class="pl-ul-li-text"><?= $item['title']; ?></span>
            <a class="btn btn-danger btn-small pull-right remove-item" id="remove_<?= $item['id']; ?>">Удалить</a>
        </li>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
<input type="hidden" name="products_ids" id="p_ids" value=""/>
<div style="width: 100%; height: 40px; border-top: 1px solid #d3d3d3;">
    <h5>Выберите новые товары для слайдера:</h5>
</div>
<link rel="stylesheet" href="http://ajax.aspnetcdn.com/ajax/jquery.ui/1.10.3/themes/base/jquery-ui.css">
<div class="hiden-row" style="display: none;">
    <div class="hr-row" id="" style="margin: 15px 0;">
        Поиск товара по:
        <select name="search_target" id="" style="width: 120px;">
            <option value="title">названию</option>
            <option value="code">артикулу</option>
            <option value="name">URL</option>
        </select>
        <input type="text" name="products[]" class="autoc" placeholder="Автокомплит от 3-х символов" style="margin-left: 15px;"/>
    </div>
</div>
<?php for($i = 1; $i <= 10; $i++): ?>
<div class="hr-row" id="hrow_<?=$i; ?>" style="margin: 15px 0;">
    Поиск товара по:
    <select name="search_target" id="s_<?=$i; ?>" style="width: 120px;">
        <option value="title">названию</option>
        <option value="code">артикулу</option>
        <option value="name">URL</option>
    </select>
    <input type="text" id="t_<?=$i; ?>" name="products[<?=$i; ?>]" class="autoc" placeholder="Автокомплит от 3-х символов" style="margin-left: 15px;"/>
</div>
<?php endfor; ?>

<script type="text/javascript">
    var numrows = 11;
    jQuery(document).ready(function ($) {
        // автокомплит с jQuery UI
        $('.autoc').on("focus", function(){
            var elem_id_ex = this.id.split('_');
            var trgt = $('#s_'+elem_id_ex[1]).val();
            $(this).autocomplete({
                minLength: 3,
//                source: '/widgets/slick_slider/slick_slider.info.php',
                source: function(request, response){
                    $.ajax({
                        url: '/ajax/get_products_to_make_widget',
                        dataType: 'json',
                        data:{
                            term: request.term, // поисковая фраза
                            target: trgt
                        },
                        success: function(data){
                            response(data);console.log(data);
                        }
                    });
                },
                delay: 1000,
                select: function( event, ui ) {
                    var p_ids = $('#p_ids').val();
                    if(p_ids != ''){
                        $('#p_ids').val(p_ids+'_'+ui.item.id);
                    }
                    else{
                        $('#p_ids').val(ui.item.id);
                    }
                }
            });
        });
        // /автокомплит

        // удаление товара из виджета
        $('.remove-item').click(function(){
            var widget = <?=(int)$_GET['id']; ?>;
            var ex_id = this.id.split('_');
            $.post(
                '/ajax/remove_product_from_slick',
                { widget_id : widget, product_id : ex_id[1] },
                function(data){
                    console.log(data);
                    if(data*1 == 1){
                        $('#items_'+ex_id[1]).hide('slow');
                    }
                },
                'text'
            );
        });

        // сортировка с jQuery UI
        $('#sortable_list').sortable({
            axis: 'y',
            update: function (event, ui) {
                var serialized_data = $('#sortable_list').sortable('serialize');
                // POST to server
                $.ajax({
                    data: serialized_data,
                    type: 'POST',
                    url: '/ajax/sort_products_in_slick/<?=(int)$_GET['id']; ?>',
                    success: function(data){
                        console.log(data);
                    }
                });
            }
        });
    });
</script>