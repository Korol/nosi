<?php
class shop_cartWidget extends Cms_modules {
	function __construct()
	{
		parent::__construct();
	}

	function view_widget(&$r)
	{
		$html;

		include_once("./modules/shop/shop.helper.php");
		$shopModuleHelper=new shopModuleHelper;

		$cart=$shopModuleHelper->get_cart();
                
		$products_num=sizeof((array)$cart->products);
		$total_amount=$shopModuleHelper->price($cart->total_amount);

		$prodWord =  plural_form($products_num,array("товар","товара","товаров"));
		$html.=<<<EOF
<div class="basketW">
	<a href="/cart.html" class="basket">Твоя корзина<br /><span class="num">{$products_num}</span> {$prodWord}
EOF;
	// $html.= "text".."text";

		if($cart->total_amount>0){
			$html.=<<<EOF
	 <span class="total_amount">, {$total_amount}</span>
EOF;
		}
		$html.=<<<EOF
 </a>
</div>
EOF;

		return $html;
	}
}
?>