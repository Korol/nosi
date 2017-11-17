<div class="breadcrumbsW" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
	<a href="/" itemprop="url"><span itemprop="title">Главная</span></a> → <strong itemprop="title">Материалы</strong>
</div>

<div class="photoGalsW"><div class="photoGals">
	<h1>Материалы</h1>

<?php

/*
$menu_r=$this->db
->get_where("categoryes",array(
	"id"=>"1568",
	"show"=>1
))
->row();
if(intval($menu_r->id)>0){
?>
	<div class="photoGalsRow">
		<div class="tit">Товары</div>
		<div class="subdirs">
			<?php
$i=1;
$items=$this->ci->menu->get_menu_items(1568);
foreach($items AS $r)
{
	if($r->parent_id!=1568)continue;

	?><a<?php print $a; ?> href="<?php print $r->link; ?>"><?php print $r->title; ?></a><?php
	if($i!=sizeof($items)){
		?><span>•</span><?php
	}
	$i++;
}
?>
			<div class="clear"></div>
		</div>
	</div>
<?php
}
*/

$menu_r=$this->db
->get_where("categoryes",array(
	"id"=>"1581",
	"show"=>1
))
->row();
if(intval($menu_r->id)>0){
?>
	<div class="photoGalsRow">
		<!-- <div class="tit">Материалы</div> -->
		<div class="subdirs">
			<?php
$i=1;
$items=$this->ci->menu->get_menu_items(1581);
foreach($items AS $r)
{
	if($r->parent_id!=1581)continue;

	?><a<?php print $a; ?> href="<?php print $r->link; ?>"><?php print $r->title; ?></a><?php
	if($i!=sizeof($items)){
		?><span>•</span><?php
	}
	$i++;
}
?>
			<div class="clear"></div>
		</div>
	</div>
<?php
}
?>
</div></div>