<?php
$manufacturer_id=intval($this->input->get("manufacturer_id"));
$manufacturer_name="";
if($manufacturer_id>0){
	foreach($manufacturers_res AS $r)
	{
		if($r->id==$manufacturer_id){
			$manufacturer_name=" ".$r->title;
			break;
		}
	}
}

$pg=intval($this->input->get("pg"));
?><script src="/modules/shop/media/js/shop.main.js"></script>


<script type="text/javascript" src="/assets/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" type="text/css" href="/assets/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<script type="text/javascript">
$(document).ready(function(){
	$(".photoGalRow a").fancybox();
});
</script>
<div class="breadcrumbsW" itemtype="http://data-vocabulary.org/Breadcrumb">
	<?php
	function drawCat($parent_id=0,&$that,&$data=array(),&$cats_path=array())
	{
		if($parent_id==0)return $data;

		$res=$that->ci->db
		->where("id",$parent_id)
		->get_where("categoryes")
		->row();
		$res->link=$that->ci->module->link_category($res);

		$data[]=<<<EOF
<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb"> 
<a itemprop="url" href="{$res->link}"> <span itemprop="title">{$res->title}</span> </a> / 
</div>
EOF;
$cats_path[]=trim($res->title);
		drawCat($res->parent_id,$that,$data,$cats_path);

		return $data;
	}
	$data=array();
	$cats=drawCat($category_res->parent_id,$this,$data,$cats_path);
	$cats=array_reverse($cats);
	foreach($cats AS $r)
	{
		print $r;
	}
	$cats_path[]=$category_res->title;

	$category_res->link=$this->ci->module->link_category($category_res);
	?>
	<a href="/" itemprop="url"><span itemprop="title">Главная</span></a> → <strong itemprop="title"><?php print $category_res->title; ?></strong>
</div>

<div class="photoGalW"><div class="photoGal">
	<h1><?php print $category_res->title;
		if(!empty($manufacturer_name)){
			print $manufacturer_name;
		}
		?></h1><br />

	<?php
	foreach($products_res AS $r)
	{
		?><div class="productRow" itemscope itemtype="http://schema.org/Product">
		<a href="<?php print $r->link; ?>">
			<span class="title" itemprop="name"><?php print $r->title; ?></span>
			<span class="priceW"><span class="price">Цена: <span itemprop="price"><?php print $r->price; ?></span> <small>грн.</small></span></span>
			<span class="th"><img src="/uploads/shop/products/thumbs/<?php print $r->main_picture_file_name; ?>" alt="<?php print $r->title; ?>" /></span>
		</a>
	</div><?php
	}
	?>
</div></div>








<?php

$uri=$_SERVER['REQUEST_URI'];
$uri=preg_replace("#(\?|&)pg=[^=&]*#is","",$uri);
$pg_uri=$uri.(preg_match("#\?#is",$uri)?"&":"?")."pg=";

if(preg_match("#[^&?]([&?])#is",$uri,$matches)){
	if($matches[1]=="&"){
		$uri=preg_replace("#&#is","?",$uri,1);
	}
}

if(sizeof($paginator->display_pages())>1){
?>
<div class="pagination pagination-small pagination-centered">
	<strong style="position:relative; top:-8px;">Страница:&nbsp;</strong>
  <ul>
	<?php
	if(intval($this->input->get("pg"))>1){
		$i=intval($this->input->get("pg"))-1;
		?><li><a href="<?php print $i==1?$uri:($pg_uri.$i); ?>" rel="prev">назад</a></li><?php
	}
	$i=0;
	foreach($paginator->display_pages() AS $cpg)
	{
		?><li class="<?php print $pg==$cpg?' active':''; ?>"><?php
		if(is_numeric($cpg)){
			?><a href="<?php print $i==0?$uri:($pg_uri.$cpg); ?>"><?php
		}
		?><?php print $cpg; ?></a><?php
		if(is_numeric($cpg)){
			?></a><?php
		}
		?></li><?php
		$i++;
	}
	if(intval($this->input->get("pg"))!=sizeof($paginator->display_pages())){
		$i=intval($this->input->get("pg"))+1;
		?><li><a href="<?php print $pg_uri.$i; ?>" rel="next">вперед</a></li><?php
	}
	?>
	</ul>
</div>
<?php
}

?>
<div class="clear"></div>
<?php
if(!empty($category_res->description)){
	print $category_res->description;
	?><p>&nbsp;</p><?php
}
?>