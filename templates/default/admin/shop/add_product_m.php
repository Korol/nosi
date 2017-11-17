<script>
function addCodeField(button)
{
	var html='';
	html+='<div class="codeFieldRow">';
	html+='<input type="text" name="code_alias[]" style="width:150px;" value="" />';
	html+='&nbsp;<a style="position:relative; top:-4px;" title="удалить артикул" href="#" onclick="$(this).parents(\'div:eq(0)\').remove(); return false;"><img src="/templates/default/admin/assets/icons/cross.png" alt="удалить артикул" /></a>';
	html+='</div>';

	$(button).after(html);
}

function changeProductType(value)
{
	$(".hidden_fields").hide();
	$(".hidden_additional_"+value).show();
}

$(document).ready(function(){
	changeProductType($("select#type_id").val());
        
        $('#colors').attr('onclick', 'setProductsColors();');
});

function changePrice(type){
//    return; // для отключения функции раскомментировать эту строку
    var price = $('#price').val()*1;
    var price_old = $('#price_old').val()*1;
    var discount = $('#discount').val()*1;
    
    if(type == 'price'){
        if((price_old != 0) && (price < price_old)){
            // вычисляем % скидки – на сколько % старая цена больше текущей
            discount = (price*100)/price_old;
            $('#discount').val(100 - Math.ceil(discount));
            return;
        }
        else if(discount != 0){
            // вычисляем "старую" цену по указанному % скидки – увеличиваем текущую цену на указанный процент
            price_old = (price*(100+discount))/100;
            $('#price_old').val(Math.ceil(price_old));
            return;
        }
    }
    else if(type == 'price_old'){
        if((price != 0) && (price < price_old)){
            // вычисляем % скидки - на сколько % старая цена больше текущей
            discount = (price*100)/price_old;
            $('#discount').val(100 - Math.ceil(discount));
            return;
        }
        else if((discount != 0) && (price_old != 0)){
            // вычисляем цену товара по старой цене и скидке – уменьшение старой цены на указанный процент
            price = (price_old*(100-discount))/100;
            $('#price').val(Math.ceil(price));
            return;
        }
    }
    else if(type == 'discount'){
        if((discount != 0) && (price != 0)){
            // вычисляем "старую" цену по указанному % скидки – увеличиваем текущую цену на указанный процент
            // v1 - работа с полями цен без замены значений
//            price_old = (price*(100+discount))/100;
//            $('#price_old').val(Math.ceil(price_old));
//            return;
            // v2 - работа с полями цен с заменой значений: текущая цена переходит в поле "Старая цена", а в поле "Цена" записываем цену уже со скидкой
            var new_price = (price*(100-discount))/100; // цена со скидкой
            $('#price_old').val(price); // текущая цена переходит в поле "Старая цена"
            $('#price').val(Math.ceil(new_price)); // поле "Цена" записываем цену уже со скидкой
        }
        else if((discount != 0) && (price_old != 0)){
            // вычисляем цену товара по старой цене и скидке – уменьшаем старую цену на указанный процент
            price = (price_old*(100-discount))/100;
            $('#price').val(Math.ceil(price));
            return;
        }
    }
    
    return;
}

// отметка всех фотографий, как цветов товаров – при отметке чекбокса "Цвета товара"
// и обратная операция – снятие чекбоксов
function setProductsColors(){
    var check_colors = $('#colors').is(':checked');
    if(check_colors === true){
        $('#form_field_product-photo').find('input[type=checkbox]').attr('checked', 'checked');
    }
    else{
        $('#form_field_product-photo').find('input[type=checkbox]').removeAttr('checked');
    }
    
}

// отметка фоторгафии товара в качестве "Цвета товара"
function photoColor(photoid){
    var colors_id = 'colors'; // ID чекбокса "Изображения товара как цвета"
    var check_colors = $('#'+colors_id).is(':checked'); // проверка – отмечен ли чекбокс "Изображения товара как цвета"
    var check_photo = $('#'+photoid).is(':checked'); // проверка – отмечен ли чекбокс картинки
    if((check_photo === true) && (check_colors === false)){
        // попытка отметить фото – когда не отмечен основной чекбокс. отмечаем его
        $('#'+colors_id).attr('checked', 'checked');console.log('not checked!');
    }
    // проверяем, отмечено ли хоть одно фото в качестве цвета товара
    var check_all_photos = $('#form_field_product-photo').find('input[type=checkbox]').is(':checked');
    if(check_all_photos === false){
        $('#colors').removeAttr('checked');
    }
}

// новый вариант удаления файлов
function removeUpload(id){
    // удаление файла из таблицы БД `uploads`
    $.post(
        '/admin/?m=admin&a=remove_upload',
        {id : id},
        function(data){
            if(data === 'ok'){
                // скрываем элемент
                $('#files_list_'+id).remove();
            }
        },
        'text'
    );
}

//            var tr = $('.move');
//            var down = $('.down');
//            var up = $('.up');
//
//            up.click(function() {
//                tr.after(tr.prev());
//            });
//
//            down.click(function() {
//                tr.before(tr.next());
//            });

// новый вариант перемещения файлов
function moveUpload(id, direct){
    // изменение `order` в таблице БД `uploads`
    $.post(
        '/admin/?m=admin&a=move_upload',
        {id : id, direct : direct},
        function(data){
            console.log(data);
            if(data === 'ok'){
                // перемещаем элемент
                var tr = $('#files_list_'+id);
                if(direct === 'up'){
                    tr.after(tr.prev()); // поднимаем
                }
                else{
                    tr.before(tr.next()); // опускаем
                }
            }
        },
        'text'
    );
}
</script>
<?php print $render; ?>