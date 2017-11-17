<script type="text/javascript" src="/assets/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" type="text/css" href="/assets/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<script type="text/javascript">
$(document).ready(function(){
	$(".photoGalRow a").fancybox();
});
</script>
<div class="breadcrumbsW" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
	<a href="/" itemprop="url"><span itemprop="title">Главная</span></a> → <?php
	if($item_res->parent_id==14){
		// материалы
		?><a href="/materials.html" itemprop="url"><span itemprop="title">Материалы</span></a><?php
	}else{
		?><a href="/photo-gallery.html" itemprop="url"><span itemprop="title">Фотогалерея</span></a><?php
	}
	?> → <strong itemprop="title"><?php print $item_res->title; ?></strong>
</div>

<div class="photoGalW"><div class="photoGal">
	<h1><?php print $item_res->title; ?></h1>

	<?php
	foreach($item_res->childs AS $r)
	{
		?><div class="photoGalRow">
		<a rel="group" href="/uploads/media/big/<?php print $r->file_name; ?>"><span><img src="/uploads/media/thumbs/<?php print $r->file_name; ?>" alt="" /></span></a>
	</div><?php
	}
	?>
</div></div>