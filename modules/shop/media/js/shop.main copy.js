var alertTimer;
function shop_product_cart(o,product_id,quantity,type)
{
	$.get("/?m=shop&a=add_product_to_cart",{
		shop_add_product_to_cart_sm:1,
		product_id:product_id,
		quantity:quantity,
		type:type
	},function(d){
		clearTimeout(alertTimer);
		$("body").prepend('<div id="shopAlert" style="border:1px solid #CDCDCD; border-radius:5px; display:none; position:fixed; z-index:99999; left:10px; top:10px; text-align:center; padding:40px; width:200px; font-size:18px;" id="alert">Корзина обновлена</div>');
		shopAlertPos();
		$("#shopAlert").css({
			left:($(window).width()-$("#shopAlert").width())/2
		}).fadeIn("fast");



		alertTimer=setTimeout(function(){
			$("#shopAlert").effect("transfer",{
				to:".headerW .basketW a",
				className: "ui-effects-transfer"
			},1000,function(){
				$("#shopAlert").remove();
			});
			$("#shopAlert").hide();
		},2000);
		
		$(".basketW .basket").html('В корзине <span class="num">'+d.products_num+'</span> товаров	 на сумму <span class="total_amount">'+d.price_hmn+'</span> &rarr;')
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