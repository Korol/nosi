<?php
$this->title($item_res->title);
if($this->input->get("print")==1){
?><div class="oneItemW oneItemWPrint">
	<div class="right">
		<div class="location"><?php print $item_res->location; ?></div>
		<div class="titleW"><h1><?php print $item_res->title; ?></h1></div>
		<div class="price"><?php print $item_res->area; ?> — <?php print $item_res->price_hmn; ?></div>
		<div class="desc"><p><?php print $item_res->full_desc; ?></p></div>

		<?php
		if(!empty($item_res->params)){
			?><div class="paramsText"><p><?php print $item_res->params; ?></p></div><?php
		}
		?>

		<?php
		if(sizeof($item_photos_res)>0){
			?><div class="photosW">
			<div class="mainPhoto">
				<?php
				foreach($item_photos_res AS $r)
				{
					?><a rel="photos" target="_blank" href="/uploads/realty/items/big/<?php print $r->file_name; ?>"><img src="/uploads/realty/items/thumbs2/<?php print $r->file_name; ?>" alt="" /></a><?php
					break;
				}
				?>
			</div>
		</div><?php
		}
		?>
	</div>

	<div class="clear"></div>
</div>
<script>
$(window).load(function(){
	window.print();
});
</script>
<?php
}else{
?><script type="text/javascript" src="/assets/fancybox/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" href="/assets/fancybox/jquery.fancybox.css" type="text/css" media="screen" />
<script>
$(document).ready(function(){
	$(".photosW a").fancybox();
});
</script>
<div class="oneItemW">
	<div class="left">
		<div class="printW">
			<a href="#" class="print"><?php print $this->ci->lang->line("t_print"); ?></a>
		</div>
	</div>
	<div class="right">
		<div class="location"><?php print $item_res->location; ?></div>
		<div class="titleW"><h1><?php print $item_res->title; ?></h1></div>
		<div class="price"><?php print $item_res->area; ?> — <?php print $item_res->price_hmn; ?></div>
		<div class="desc"><p><?php print $item_res->full_desc; ?></p></div>

		<?php
		if(!empty($item_res->params)){
			?><div class="paramsText"><p><?php print $item_res->params; ?></p></div><?php
		}
		?>

		<?php
		if(sizeof($item_photos_res)>0){
			?><div class="photosW">
			<div class="mainPhoto">
				<?php
				foreach($item_photos_res AS $r)
				{
					?>
					<a rel="photos" target="_blank" href="/uploads/realty/items/big/<?php print $r->file_name; ?>"><img src="/uploads/realty/items/thumbs2/<?php print $r->file_name; ?>" alt="" /></a>
					
					<div class="clear"></div>
					<br />
					<?php
				}
				?>
			</div>
		</div><?php
		}
		?>
	</div>

	<div class="clear"></div>
</div><?php
}
?>