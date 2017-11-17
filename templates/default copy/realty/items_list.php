<?php
$this->title($category_res->title);
?><div class="productsListW"><div class="productsListI"><div class="productsList">

<?php
foreach($items_res AS $i=>$r)
{
	?><div class="productsListRow<?php print $i%2?" productsListRowRight":""; ?>">
	<div class="thumbW"><a href="<?php print $r->link; ?>" class="thumb" style="background-image:url();"><img src="/uploads/realty/items/thumbs3/<?php print $r->main_picture_file_name; ?>"></a></div>
	<div class="content">
		<div class="location"><?php print $r->location; ?></div>
		<div class="titleW"><a href="<?php print $r->link; ?>"><?php print $r->title; ?></a></div>
		<div class="price"><?php print $r->area; ?> — <?php print $r->price_hmn; ?></div>
		<div class="shortDescW"><p><?php print $r->short_desc; ?></p></div>
		<div class="print"><a href="#" data-id="<?php print $r->id; ?>" href="<?php print $r->link; ?>"></a></div>
		<div class="readMore"><a href="<?php print $r->link; ?>"><?php print $this->ci->lang->line("t_readmore"); ?></a></div>
	</div>
	<div class="clear"></div>
</div><?php
}
?>

<div class="clear"></div>
</div></div></div><?php
?>