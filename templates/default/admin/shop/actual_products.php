<?php
print $render;
?>
<script>
var current_products_hash="";
function price_format(n)
{
	if(n=="")return 0;
	
    return /\./.test(n)?String(n).split(".")[0]+"."+String(n).split(".")[1].substr(0,2)+(String(n).split(".")[1].length<2?0:''):(n);
}

function price_double(price)
{
	price=price.replace(",",".");
	price=price.replace(/[^0-9.-]/ig,"");
	price=price.replace(/\.+$/ig,"");
	return parseFloat(price);
}

function calcProductsHash()
{
	var product_original_price={};
	var product_status={};
	var product_supplier={};

	var new_hash="";
	$("input[name^='product_original_price']").each(function(){
		product_original_price[$(this).data("order-id")+':'+$(this).data("product-id")]=$(this).val();
		new_hash+=$(this).val();

		var quantity=$(this).data("quantity");
		var price=$(this).data("price");
		var sum=price*quantity;
		var original_sum=parseFloat(price_format($(this).val()))*$(this).data("quantity");

		var profit=price_format(sum-original_sum);

		var original_sum_hmn=/\./.test(original_sum)?original_sum:original_sum+".00";

		$(this).parents("td:eq(0)").next().text(original_sum_hmn+" $");

		$(this).parents("td:eq(0)").nextAll("td:eq(4)").text(profit+" $");
	});
	$("select[name^='product_status']").each(function(){
		product_status[$(this).data("order-id")+':'+$(this).data("product-id")]=$(this).val();
		new_hash+=$(this).val();
	});
	$("select[name^='product_supplier']").each(function(){
		product_supplier[$(this).data("order-id")+':'+$(this).data("product-id")]=$(this).val();
		new_hash+=$(this).val();
	});

	$("#table-default tr:gt(0)").each(function(){
		var color=$("select[name^='product_status'] option:selected",this).data("color");
		if(typeof color!="undefined"){
			$("td",this).css("background-color",color);
		}
	});

	if(current_products_hash!="" && current_products_hash!=new_hash){
		$.post(document.location.href,{
			save_products_sm:1,
			product_original_price:product_original_price,
			product_status:product_status,
			product_supplier:product_supplier
		},function(d){
			if(parseInt(d)!=1){
				alert(d);
			}

		});
	}

	// calc botton nums
	var total_quantity=0;
	var total_product_original_price=0;
	var total_product_original_sum=0;
	var total_price=0;
	var total_sum=0;
	var total_profit=0;
	$("#table-default tr:gt(0):lt(-1)").each(function(){
		var quantity=price_double($("td:eq(4)",this).text());
		var product_original_price=price_double($("td:eq(5) input",this).val());
		if(isNaN(product_original_price))product_original_price=0;
		var product_original_sum=quantity*product_original_price;

		var price=price_double($("td:eq(8)",this).text());
		var sum=price*quantity;
		var profit=sum-product_original_sum;

		total_quantity+=quantity;
		total_product_original_price+=product_original_price;
		total_product_original_sum+=product_original_sum;
		total_price+=price;
		total_sum+=sum;
		total_profit+=profit;
	});

	$("#table-default tr:last td:eq(4)").text(price_format(total_quantity));
	$("#table-default tr:last td:eq(5)").text(price_format(total_product_original_price)+" $");
	$("#table-default tr:last td:eq(6)").text(price_format(total_product_original_sum)+" $");
	$("#table-default tr:last td:eq(8)").text(price_format(total_price)+" $");
	$("#table-default tr:last td:eq(9)").text(price_format(total_sum)+" $");
	$("#table-default tr:last td:eq(10)").text(price_format(total_profit)+" $");

	current_products_hash=new_hash;
}

function initProductsSaver()
{
	calcProductsHash();
	$("input[name^='product_original_price']").change(function(){
		calcProductsHash();
	});
	$("select[name^='product_status']").change(function(){
		calcProductsHash();
	});
	$("select[name^='product_supplier']").change(function(){
		calcProductsHash();
	});
}

$(document).ready(function(){
	initProductsSaver();
});
</script>

<style>
#table-default td, #table-default th {
	padding:5px;
	font-size:12px;
	color:#000;
}

#table-default input {
	font-size:11px;
	color:#000;

}

#table-default select, #table-default select option {
	font-size:12px !important;
	line-height:10px;
	height:18px;
	padding:0;
	color:#000;
}

#table-default input {
	line-height:10px;
	height:18px;
}
</style>