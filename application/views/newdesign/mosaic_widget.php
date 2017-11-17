<?php
$td_w = $td_h = (!empty($products) || !empty($categories)) ? 160 : 120;
$images_path = '/uploads/shop/products/thumbs4/';
$mosaic_path = '/uploads/shop/products/mosaic/';
//var_dump($products);
?>
<style type="text/css">
    .mosaic {
        margin: 10px 0 40px;
    }
    .mosaic td {
        border: 1px solid #000;
    }
    .mosaic-square, .mosaic-colonne {
        width: <?= $td_w; ?>px;
        position: relative;
        box-sizing: border-box;
        font-size: 30px;
    }
    .mosaic-square {
        height: <?= $td_h; ?>px;
        text-align: center;
        line-height: <?= $td_h; ?>px;
    }
    .mosaic-colonne {
        height: <?= ($td_h*2); ?>px;
        text-align: center;
        line-height: <?= ($td_h*2); ?>px;
    }
    .mosaic-colonne img {
        width: <?= $td_w; ?>px;
        height: <?= ($td_h*2); ?>px;
    }
    .mosaic-square img {
        width: <?= $td_w; ?>px;
        height: <?= $td_h; ?>px;
    }
    .mosaic-category {
        width: 100%;
        position: absolute;
        top: 48px;
        color: #fff;
        font-size: 14px;
        font-weight: lighter;
        text-align: center;
        text-transform: uppercase;
        line-height: 1.2;
    }
    .mosaic-info-hor {
        width: 100%;
        height: 45px;
        background: url(/assets/newdesign/images/rectangle11.png) repeat;
        position: absolute;
        bottom: 0;
        color: #dddddd;
        padding: 5px 0;
    }
    .mih-title {
        width: <?=$td_w; ?>px;
        height: 45px;
        overflow: hidden;
        font-size: 12px;
        text-transform: uppercase;
        float: left;
        line-height: 1.3;
        font-weight: lighter;
    }
    .mosaic-colonne .mosaic-info-hor {
        bottom: -1px;
    }
    .t-shadow {
        font-size: 30px;
        color: #fff;
        position: absolute;
        top: 0;
        text-align: left;
        line-height: 1;
        padding: 5px;
        text-shadow: 2px 2px #000;/*#ff0000*/
    }
    .edit-btn {
        position: absolute;
        top: 7px;
        right: 7px;
    }
</style>
<?php /*if(!empty($products)){ ?>
<!-- Выводим сетку с картинками товаров -->
<?php } else {*/ ?>
<!-- Выводим сетку -->
    <h5>Сетка мозаики с номерами расположения в ней элементов:</h5>
    <div class="alert alert-info">
        <p>Для перехода к редактированию (обрезке) изображений товаров в блоках <strong>1</strong> и <strong>7</strong>
        <br/>нажмите на кнопку
        <a class="btn btn-info btn-small" href="/cropper/select/<?= (int)$_GET['id'] . '/' . $products[1]['product_id']; ?>" title="Редактировать изображение товара">
            <i class="icon-picture"></i> <i class="icon-pencil"></i>
        </a> в правом верхнем углу соответствующего блока.</p>
        <p>При редактировании (обрезке) изображения создаётся его копия – так что этот процесс редактирования<br/>
        никак не влияет на размеры или содержание оригинальных фото товара.</p>
        <p>Результаты редактирования (обрезки) изображения сохраняются автоматически по завершении редактирования – так что после<br/>редактирования изображения
            не нужно нажимать кнопки «Сохранить» или «Применить» в правой части этой страницы.</p>
        <p>Позицию виджета без крайней необходимости желательно <strong>НЕ ИЗМЕНЯТЬ!</strong></p>
    </div>
    <table class="mosaic">
        <tr>
            <td rowspan="2">
                <div class="mosaic-colonne">
                    <?php
                    if(!empty($products[1]['file_name']) || !empty($products[1]['image'])){
                        $src = (!empty($products[1]['image'])) ? $mosaic_path . $products[1]['image'] : $images_path . $products[1]['file_name'];
                    ?>
                        <div class="edit-btn">
                            <a class="btn btn-info" href="/cropper/select/<?= (int)$_GET['id'] . '/' . $products[1]['product_id']; ?>" title="Редактировать изображение товара">
                                <i class="icon-picture"></i> <i class="icon-pencil"></i>
                            </a>
                        </div>
                        <img src="<?= $src; ?>" alt="Mosaic 1"/>
                        <div class="mosaic-info-hor">
                            <div class="mih-title"><?= $products[1]['title']; ?><br/>Арт: <?= $products[1]['code']; ?></div>
                            <div class="clear"></div>
                        </div>
                        <div class="t-shadow">1</div>
                    <?php } else { ?>
                    1
                    <?php } ?>
                </div>
            </td>
            <td>
                <div class="mosaic-square" id="pos_2">
                    <?php if(!empty($products[2]['file_name'])){ ?>
                        <div class="edit-btn">
                            <a class="btn btn-danger" onclick="removeFromMosaic(<?=$products[2]['product_id']; ?>, 2); return false;" title="Удалить товар из мозаики">
                                <i class="icon-trash"></i>
                            </a>
                        </div>
                        <img src="<?= $images_path . $products[2]['file_name']; ?>" alt="Mosaic 2"/>
                        <div class="mosaic-info-hor">
                            <div class="mih-title"><?= $products[2]['title']; ?><br/>Арт: <?= $products[2]['code']; ?></div>
                            <div class="clear"></div>
                        </div>
                        <div class="t-shadow">2</div>
                    <?php } else { ?>
                        2
                    <?php } ?>
                </div>
            </td>
            <td>
                <div class="mosaic-square" id="pos_3">
                    <?php if(!empty($products[3]['file_name'])){ ?>
                        <div class="edit-btn">
                            <a class="btn btn-danger" onclick="removeFromMosaic(<?=$products[3]['product_id']; ?>, 3); return false;" title="Удалить товар из мозаики">
                                <i class="icon-trash"></i>
                            </a>
                        </div>
                        <img src="<?= $images_path . $products[3]['file_name']; ?>" alt="Mosaic 3"/>
                        <div class="mosaic-info-hor">
                            <div class="mih-title"><?= $products[3]['title']; ?><br/>Арт: <?= $products[3]['code']; ?></div>
                            <div class="clear"></div>
                        </div>
                        <div class="t-shadow">3</div>
                    <?php } else { ?>
                        3
                    <?php } ?>
                </div>
            </td>
            <td>
                <div class="mosaic-square">
                    <?php if(!empty($categories[4]['title'])){ ?>
                        <img src="/assets/newdesign/images/m4.jpg" alt="Mosaic 4">
                        <div class="mosaic-category"><?= $categories[4]['title']; ?></div>
                        <div class="t-shadow">4</div>
                    <?php } else { ?>
                        4
                    <?php } ?>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="mosaic-square" id="pos_5">
                    <?php if(!empty($products[5]['file_name'])){ ?>
                        <div class="edit-btn">
                            <a class="btn btn-danger" onclick="removeFromMosaic(<?=$products[5]['product_id']; ?>, 5); return false;" title="Удалить товар из мозаики">
                                <i class="icon-trash"></i>
                            </a>
                        </div>
                        <img src="<?= $images_path . $products[5]['file_name']; ?>" alt="Mosaic 5"/>
                        <div class="mosaic-info-hor">
                            <div class="mih-title"><?= $products[5]['title']; ?><br/>Арт: <?= $products[5]['code']; ?></div>
                            <div class="clear"></div>
                        </div>
                        <div class="t-shadow">5</div>
                    <?php } else { ?>
                        5
                    <?php } ?>
                </div>
            </td>
            <td>
                <div class="mosaic-square" id="pos_6">
                    <?php if(!empty($products[6]['file_name'])){ ?>
                        <div class="edit-btn">
                            <a class="btn btn-danger" onclick="removeFromMosaic(<?=$products[6]['product_id']; ?>, 6); return false;" title="Удалить товар из мозаики">
                                <i class="icon-trash"></i>
                            </a>
                        </div>
                        <img src="<?= $images_path . $products[6]['file_name']; ?>" alt="Mosaic 6"/>
                        <div class="mosaic-info-hor">
                            <div class="mih-title"><?= $products[6]['title']; ?><br/>Арт: <?= $products[6]['code']; ?></div>
                            <div class="clear"></div>
                        </div>
                        <div class="t-shadow">6</div>
                    <?php } else { ?>
                        6
                    <?php } ?>
                </div>
            </td>
            <td rowspan="2">
                <div class="mosaic-colonne">
                    <?php
                    if(!empty($products[7]['file_name']) || !empty($products[7]['image'])){
                        $src = (!empty($products[7]['image'])) ? $mosaic_path . $products[7]['image'] : $images_path . $products[7]['file_name'];
                    ?>
                        <div class="edit-btn">
                            <a class="btn btn-info" href="/cropper/select/<?= (int)$_GET['id'] . '/' . $products[7]['product_id']; ?>" title="Редактировать изображение товара">
                                <i class="icon-picture"></i> <i class="icon-pencil"></i>
                            </a>
                        </div>
                        <img src="<?= $src; ?>" alt="Mosaic 7"/>
                        <div class="mosaic-info-hor">
                            <div class="mih-title"><?= $products[7]['title']; ?><br/>Арт: <?= $products[7]['code']; ?></div>
                            <div class="clear"></div>
                        </div>
                        <div class="t-shadow">7</div>
                    <?php } else { ?>
                        7
                    <?php } ?>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="mosaic-square" id="pos_8">
                    <?php if(!empty($products[8]['file_name'])){ ?>
                        <div class="edit-btn">
                            <a class="btn btn-danger" onclick="removeFromMosaic(<?=$products[8]['product_id']; ?>, 8); return false;" title="Удалить товар из мозаики">
                                <i class="icon-trash"></i>
                            </a>
                        </div>
                        <img src="<?= $images_path . $products[8]['file_name']; ?>" alt="Mosaic 8"/>
                        <div class="mosaic-info-hor">
                            <div class="mih-title"><?= $products[8]['title']; ?><br/>Арт: <?= $products[8]['code']; ?></div>
                            <div class="clear"></div>
                        </div>
                        <div class="t-shadow">8</div>
                    <?php } else { ?>
                        8
                    <?php } ?>
                </div>
            </td>
            <td>
                <div class="mosaic-square">
                    <?php if(!empty($categories[9]['title'])){ ?>
                        <img src="/assets/newdesign/images/m4.jpg" alt="Mosaic 9">
                        <div class="mosaic-category"><?= $categories[9]['title']; ?></div>
                        <div class="t-shadow">9</div>
                    <?php } else { ?>
                        9
                    <?php } ?>
                </div>
            </td>
            <td>
                <div class="mosaic-square" id="pos_10">
                    <?php if(!empty($products[10]['file_name'])){ ?>
                        <div class="edit-btn">
                            <a class="btn btn-danger" onclick="removeFromMosaic(<?=$products[10]['product_id']; ?>, 10); return false;" title="Удалить товар из мозаики">
                                <i class="icon-trash"></i>
                            </a>
                        </div>
                        <img src="<?= $images_path . $products[10]['file_name']; ?>" alt="Mosaic 10"/>
                        <div class="mosaic-info-hor">
                            <div class="mih-title"><?= $products[10]['title']; ?><br/>Арт: <?= $products[10]['code']; ?></div>
                            <div class="clear"></div>
                        </div>
                        <div class="t-shadow">10</div>
                    <?php } else { ?>
                        10
                    <?php } ?>
                </div>
            </td>
        </tr>
    </table>
<?php /*}*/ ?>
<input type="hidden" name="products_ids" id="p_ids" value=""/>
<div style="width: 100%; height: 40px; border-top: 1px solid #d3d3d3;">
    <h5>Выберите новые товары для мозаики, согласно позициям в ней, указанным в таблице выше:</h5>
</div>
<link rel="stylesheet" href="http://ajax.aspnetcdn.com/ajax/jquery.ui/1.10.3/themes/base/jquery-ui.css">

<?php for($i = 1; $i <= 10; $i++): ?>
<div class="hr-row" id="hrow_<?=$i; ?>" style="margin: 15px 0;">
    <?php if(!in_array($i, array(4, 9))): ?>
    <b style="margin-right: 10px; font-size: 20px;"><?= $i; ?></b>Поиск товара по:
    <select name="search_target" id="s_<?=$i; ?>" style="width: 120px;">
        <option value="title">названию</option>
        <option value="code">артикулу</option>
        <option value="name">URL</option>
    </select>
    <input type="text" id="t_<?=$i; ?>" name="products[<?=$i; ?>]" class="autoc" placeholder="Автокомплит от 3-х символов" style="margin-left: 15px;"/>
    <?php else: ?>
    <b style="margin-right: 10px; font-size: 20px;"><?= $i; ?></b>Выберите категорию товаров:
    <select name="categories[<?=$i; ?>]" id="">
    <?php
    if(!empty($categories_select)){
        foreach($categories_select as $cat_id => $category){
    ?>
        <option value="<?=$cat_id; ?>"><?= $category; ?></option>
    <?php
        }
    }
    ?>
    </select>
    <?php endif; ?>
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
                    $('#t_'+elem_id_ex[1]).attr('name', 'products['+elem_id_ex[1]+']['+ui.item.id+']');
//                    var p_ids = $('#p_ids').val();
//                    if(p_ids != ''){
//                        $('#p_ids').val(p_ids+'_'+ui.item.id);
//                    }
//                    else{
//                        $('#p_ids').val(ui.item.id);
//                    }
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

    function removeFromMosaic(prodId, position){
        $.post(
            '/ajax/remove_product_from_mosaic',
            { product_id : prodId },
            function(data){
                if(data*1 == 1){
                    $('#pos_'+position).html(position);
                }
            },
            'text'
        );
    }
</script>