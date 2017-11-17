<?php
$categoryes_res=$this->ci->db
->join("categoryes","categoryes.id = shop_products_categories_link.category_id")
->get_where("shop_products_categories_link",array(
	"shop_products_categories_link.product_id"=>$product_res->id
))
->result();


function drawCat($parent_id=0,&$categoryes_res,&$that)
{
	foreach($categoryes_res AS $r)
	{
		if($r->parent_id!=$parent_id)continue;
		$r->link=$that->ci->module->link_category($r);

		?><a itemprop="url" href="<?php print $r->link;?>"> <span itemprop="title"><?php print $r->title; ?></span> </a> → <?php

		drawCat($r->id,$categoryes_res,$that);
	}
}
?><script type="text/javascript" src="/assets/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" type="text/css" href="/assets/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<script type="text/javascript">
$(document).ready(function(){
	$(".thumbs a").fancybox();
});
</script>

<div class="breadcrumbsW" itemtype="http://data-vocabulary.org/Breadcrumb">
	<a href="/" itemprop="url"><span itemprop="title">Главная</span></a> → <?php drawCat(0,$categoryes_res,$this); ?> <strong itemprop="title"><?php print $product_res->title; ?></strong>
</div>

<div class="productViewW"><div class="productViewGal">
	<h1><?php print $product_res->title; ?></h1><br />

	<div class="thumbs"><div class="thumbsI">
		<ul>
			<li>
				<a rel="group" href="/uploads/shop/products/big/<?php print $product_res->main_picture_file_name; ?>"><img src="/uploads/shop/products/thumbs2/<?php print $product_res->main_picture_file_name; ?>" /></a>
			</li>
		<?php
		if(sizeof($product_photos_res)>0){
			unset($product_photos_res[0]);
			foreach($product_photos_res AS $r)
			{
				?><li><a rel="group" href="/uploads/shop/products/big/<?php print $r->file_name; ?>"><span><img src="/uploads/shop/products/thumbs3/<?php print $r->file_name; ?>" /></span></a></li><?php
			}
		}
		?>
		</ul>
		<div class="clear"></div>
	</div></div>

	<div class="description">
		<div class="price">
			Цена: <?php print $product_res->price; ?> грн.
		</div>
		<?php print $product_res->full_description; ?>
	</div>

	<div class="clear"></div>
</div></div>