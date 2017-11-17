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
print_r($cats_path);
?><script src="/modules/shop/media/js/shop.main.js"></script>


<script type="text/javascript" src="/assets/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" type="text/css" href="/assets/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<script type="text/javascript">
$(document).ready(function(){
	$(".photoGalRow a").fancybox();
});
</script>
<div class="loadFonBG" id="loadFonBG"><!-- Loading BG --></div>
<div class="loadFonLoader" id="loadFonLoader"> <!-- Loading Animation --> </div>
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
<div> 
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

<!-- filter -->
<script>

function print_r(arr, level) {
    var print_red_text = "";
    if(!level) level = 0;
    var level_padding = "";
    for(var j=0; j<level+1; j++) level_padding += "    ";
    if(typeof(arr) == 'object') {
        for(var item in arr) {
            var value = arr[item];
            if(typeof(value) == 'object') {
                print_red_text += level_padding + "'" + item + "' :\n";
                print_red_text += print_r(value,level+1);
		} 
            else 
                print_red_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
        }
    } 

    else  print_red_text = "===>"+arr+"<===("+typeof(arr)+")";
    return print_red_text;
}


$(document).ready(function(){
	$(".boxContentFilter input:checkbox").each(function(){
		$(this).change(function(){
			var url_groups={};
			$(".boxContentFilter input:checkbox:checked").each(function(){
				if(typeof url_groups[$(this).data("id")]=="undefined"){
					url_groups[$(this).data("id")]=[$(this).val()];
				}else{
					url_groups[$(this).data("id")][url_groups[$(this).data("id")].length]=$(this).val();
				}
				// $(this).remove();
				//alert($(this).data("id"));
			});

			var url='';
			$.each(url_groups,function(k,v){
				if(v.length>0){
					url+=k;
					$.each(v,function(k2,v2){
						url+='-'+v2;
					});
					url+=':';
				}
			});

			// var u=document.URL;

			// u=u.replace(/([?&]filter=[^=&]*)/ig,"");
			// u=u.replace(/[?&]pg=[^=&]*/,"");
			// u=u.replace(/[?&]/,"?");
			// if(!/\?/.test(u)){
			// 	u=u.replace(/\/&/,"/?");
			// }
			
			// document.location.href=u+(/\?/.test(u)?"&":"?")+"filter="+url;

			if($("#filterForm").find("input[name='filter']").length==0){
				$("#filterForm").append('<input type="hidden" name="filter" value="'+url+'">');
			}else{
				$("#filterForm").find("input[name='filter']").val(url);
			}
			$("#filterForm").submit();

		});
	});
})

function changeManufacturer(manufacturer_id)
{
	// var u=document.URL;

	// u=u.replace(/[?&]manufacturer_id=[^=&]*/,"");
	// u=u.replace(/[?&]pg=[^=&]*/,"");
	// u=u.replace(/[?&]/,"?");
	// if(!/\?/.test(u)){
	// 	u=u.replace(/\/&/,"/?");
	// }
	
	// if(manufacturer_id>0 && manufacturer_id!=""){
	// 	u+=(/\?/.test(u)?"&":"?")+"manufacturer_id="+manufacturer_id;
	// }

	// if(/manufacturer_id/.test(u)){
	// 	u=u.replace(/manufacturer_id=[0-9]+/,"manufacturer_id="+manufacturer_id);
	// }else{
	// 	u+=(/\?/.test(u)?"&":"?");
	// 	u+="manufacturer_id=";
	// 	u+=manufacturer_id;
	// }
	
	// document.location.href=u;


	if($("#filterForm").find("input[name='manufacturer_id']").length==0){
		$("#filterForm").append('<input type="hidden" name="manufacturer_id" value="'+manufacturer_id+'">');
	}else{
		$("#filterForm").find("input[name='manufacturer_id']").val(manufacturer_id);
	}
	$("#filterForm").submit();
}

function changeOrder(order_by)
{
	// var u=document.URL;

	// u=u.replace(/[?&]order_by=[^=&]*/,"");
	// u=u.replace(/[?&]/,"?");
	// if(!/\?/.test(u)){
	// 	u=u.replace(/\/&/,"/?");
	// }

	// if(/order_by/.test(u)){
	// 	u=u.replace(/order_by=[a-zA-Z]+:[a-zA-Z]+/,"order_by="+order_by);
	// }else{
	// 	u+=(/\?/.test(u)?"&":"?");
	// 	u+="order_by=";
	// 	u+=order_by;
	// }
	
	// document.location.href=u;

	if($("#filterForm").find("input[name='order_by']").length==0){
		$("#filterForm").append('<input type="hidden" name="order_by" value="'+order_by+'">');
	}else{
		$("#filterForm").find("input[name='order_by']").val(order_by);
	}
	$("#filterForm").submit();
}
function clearFilter(){
	$('.filterBox input').removeAttr("checked");
	window.location.href = "<?php print $_SERVER['SCRIPT_URI'];?>";
}
</script>
<?php
$uri=preg_replace("#\?.*$#is","",$_SERVER['REQUEST_URI']);
?>
<form id="filterForm" action="<?php print $uri; ?>" method="get">
<?php
foreach($_GET AS $k=>$v)
{
	if(!in_array($k,array("manufacturer_id","order_by","filter","pg")))continue;

	?><input type="hidden" name="<?php print htmlspecialchars($k); ?>" value="<?php print htmlspecialchars($v); ?>">
<?php
}
?>
</form>
<div class="filterBox">
	<div class="title">
		<div class="label">Производитель</div>
	</div>
	<br />
	<div class="boxContentW">
		<div class="boxContent boxContentFilter">
			<select onchange="changeManufacturer(this.value);" name="manufacturer_id" id="manufacturer_id" style="width:210px; margin-bottom:15px;">
				<option value="0">-- любой --</option>
			<?php
			foreach($manufacturers_res AS $r)
			{
				$s=$r->id==$_GET['manufacturer_id']?' selected="selected"':'';
				?><option<?php print $s; ?> value="<?php print $r->id; ?>"><?php print $r->title; ?></option><?php
			}
			?>
			</select>
			<br />
		</div>
	</div>
	<button class="clearFilter" onclick="clearFilter();">Очистить фильр</button>
</div>
<!-- Price Filter by Bomb Inside START -->





<!--<div id="priceFilter" class="priceFilter">
	<strong><span id="up">Возрастанию</span></strong>
 /  <strong><span id="down">Убыванию</span></strong>
</div> -->
<!-- Price Filter by Bomb Inside END -->
<?php
foreach($products_filter_res AS $type_r)
{
	foreach($type_r->fields AS $i=>$field)
	{
		?><div class="filterBox"><div class="title">
	<div class="label"><?php print $field->title; ?></div>
</div>

<div class="boxContentW">
	<div class="boxContent boxContentFilter">
		<?php
		switch($field->field_type)
		{
			case'select':
				foreach($field->params->options AS $value=>$option)
				{
					$s=isset($filter_selected[$field->id]) && in_array($value,$filter_selected[$field->id])?' checked="checked"':'';
					?><label><input<?php print $s; ?> type="checkbox" data-id="<?php print $field->id; ?>" name="filter_field[<?php print $field->id; ?>]" value="<?php print $value; ?>" /> <?php print $option; ?></label><?php
				}
			break;
		}
		?>
		<br />

	</div>
</div></div><?php if($i==4 || $i==10){ ?><div class="clear"><!-- --></div><? }
	}
}
?>
<div class="clear"><!-- --></div>
<!-- filter -->

<div class="photoGalW"><div class="photoGal">
	<?php if($category_res->id!=1654){ ?><h1><?php
		if($category_res->title=="Женщины"){ $category_res->title="Женская одежда"; }
		print $category_res->title;
		if(!empty($manufacturer_name)){
			print $manufacturer_name;
		}
		?></h1><br /><?php
		$active_menu_items=$this->menu->get_active_menu_item_ids();
		$child=$this->menu->get_menu_items($active_menu_items[0]);
		if($child){
			print '<div class="subCatW">';
			foreach($child as $c){
				$a=in_array($c->id,$active_menu_items)?' active':'';
				print '<a class="subCat'.$a.'" href="'.$c->link.'">'.$c->title.'</a>';
			}
			print '<div class="clear"><!-- --></div></div>';
		}
	} ?>
	<?php 
	if(!empty($category_res->description)){?>
		<script type="text/javascript">
			$(document).ready(function(){
				var height = $("#layout_fix").height()+400;
				var content = $("#layout_fix");
				// alert(height);
				$("#content").attr("style","padding-bottom:"+height+"px");
			});
		</script>
		<?
		print '<div id="layout_fix">'.$category_res->description.'</div>';
		?><p>&nbsp;</p><?php
	}
	?>
	<?php
	$num = 0;
	// print_r($products_res);exit;
	foreach($products_res AS $r)
	{
		$num++;
		// $r->full_description = implode(array_slice(explode('<br>',wordwrap(strip_tags($r->full_description),200,'<br>',false)),0,1));
		$description = str_replace('&nbsp;',' ',preg_replace('/<[^>]*>/is','',$p->full_description));
		$description = str_replace('&nbsp;',' ',preg_replace('/<[^>]*>/is','',$p->full_description));
		$max = strlen($description);
		$description=explode(" ",$description);
		$text='';
		foreach($description as $i=>$word){
			if($i>=50) break;
			$text.=$word." ";
		}
		// print_r($r->link);
		?><div class="productRow">
		<a href="<?php print $r->link; ?>">
			<span class="th"><img src="/uploads/shop/products/thumbs/<?php print $r->main_picture_file_name; ?>" alt="<?php print $r->title; ?>" title="купить <?php print $r->title; ?>" /></span><br />
			<span class="title" itemprop="name"><?php print $r->title; ?></span><br />
			<span class="description"><?php print $r->full_description; ?></span>
			<span class="price"><span itemprop="price" id="cur" ><?php print $r->price; ?></span> $</span>
			<!-- <a href="#"<?php print $this->module->add_to_cart_attrs($r,"$(this).parents('.buttons').find('.quantity').val()"); ?> class="addToCart current quantity"><span>В корзину</span></a> -->
		</a>
		</div><?php 
		if($num==3){
			print '<div class="clear"><!-- --></div>';
			if($category_res->id==1654) { print '<div class="line"><!-- --></div>'; break; }
			$num=0;
		}
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