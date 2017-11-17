var alertTimer;

function js_plural(n, forms){
    return n%10==1&&n%100!=11?forms[0]:(n%10>=2&&n%10<=4&&(n%100<10||n%100>=20)?forms[1]:forms[2]);
}

function shop_product_cart(o,product_id,quantity,type,img,size,dimensions)
{
	$.get("/?m=shop&a=add_product_to_cart",{
		shop_add_product_to_cart_sm:1,
		product_id:product_id,
		quantity:quantity,
		type:type,
		img:img,
		size:size,
                dimensions:dimensions
	},function(d){
		clearTimeout(alertTimer);
		$("body").prepend('<div id="shopAlert" style="border:1px solid #CDCDCD; border-radius:5px; display:none; position:fixed; z-index:9999999; left:10px; top:10px; text-align:center; padding:40px; width:200px; font-size:18px;background:#700255; color:#fff;" id="alert">Корзина обновлена</div>');
		shopAlertPos();
		$("#shopAlert").css({
			left:($(window).width()-$("#shopAlert").width())/2
		}).fadeIn("fast");

		alertTimer=setTimeout(function(){
				$("#shopAlert").fadeOut("fast",function(){
					$("#shopAlert").remove();
				});
		},800);
                var plrl = js_plural(d.products_num, ["товар","товара","товаров"]);
		$(".basketW .basket").html('Твоя корзина<br /> <span class="num">'+d.products_num+'</span> '+plrl+' <span class="total_amount">'+d.price_hmn+'</span>')
	});

	switch(type)
	{
		case'add':
		break;
		case'delete':

		break;
	}
}

$(document).ready(function(){
	shopAlertPos();
}).resize(function(){
	shopAlertPos();
});

function shopAlertPos()
{
	var shopAlert=$("#shopAlert");

	// alert(shopAlert.length);

	shopAlert.css({
		top:($(window).height()+shopAlert.height())/2
	});
}