<?php
$this->title($page_res->title,"append");

if($page_res->id==7){
	?><div class="bigBtn2W"><div class="bigBtn2">
		<?php $items=$this->menu->get_menu_items(1565); ?>
		<a href="<?php print $items[0]->link; ?>" class="left"></a>
		<a href="<?php print $items[1]->link; ?>" class="right"></a>
		<div class="clear"></div>
	</div></div>


	<div class="frontCatsW"><div class="frontCats">
		<div class="tit">Мы предлагаем</div>

		<?php
		$items=$this->menu->get_menu_items(1568);
		foreach($items AS $r)
		{
			if($r->parent_id!=1568)continue;
			$img_res=$this->db
			->get_where("uploads",array(
				"name"=>"menu_item_main_picture",
				"extra_id"=>$r->id
			))
			->row();

			?><div class="frontCatsRow"><a href="<?php print $r->link; ?>"><i><img src="/<?php print $img_res->file_path.$img_res->file_name; ?>" alt="" /></i><span><?php print $r->title; ?></span></a></div><?php
			
			$i++;
		}
		?>

		<div class="clear"></div>
	</div></div><?php
}

if(($page_res->id!=8 && $page_res->id!=20)
		// калькуляторы
		&& $page_res->id!=14
		&& $page_res->id!=15

		// контакты
		&& $page_res->id!=13
	){
	if($page_res->id!=7
		
		){
?>
<div class="breadcrumbsW" itemtype="http://data-vocabulary.org/Breadcrumb">
	<a href="/" itemprop="url"><span itemprop="title">Главная</span></a> → <strong itemprop="title"><?php print $page_res->title; ?></strong>
</div>
<?php
	}

	if($page_res->id==10){
		?><div class="bigBtn2W"><div class="bigBtn2">
		<?php $items=$this->menu->get_menu_items(1565); ?>
		<a href="<?php print $items[0]->link; ?>" class="left"></a>
		<a href="<?php print $items[1]->link; ?>" class="right"></a>
		<div class="clear"></div>
	</div></div><?php
	}
?>

<div class="pageTextContW"><div class="pageTextCont">
	<h1><?php print $page_res->title; ?></h1>
	
	<p><?php print $page_res->content2.$page_res->content; ?></p>
</div></div>
<?php
}else{
	print $page_res->content2.$page_res->content;
}
?>