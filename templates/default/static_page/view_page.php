<?php
$this->title($page_res->title,"append");


if($page_res->id!=21 && $page_res->id!=22 && $page_res->id!=23 && $page_res->id!=26){ ?>
	<div class="breadcrumbsW" itemtype="http://data-vocabulary.org/Breadcrumb">
		<a href="/" itemprop="url"><span itemprop="title">Главная</span></a> → <strong itemprop="title"><?php print $page_res->title; ?></strong>
	</div>
<?php }
	if($page_res->id==10){
		?><div class="bigBtn2W"><div class="bigBtn2">
		<?php $items=$this->menu->get_menu_items(24); print_r($items); ?>
		<a href="<?php print $items[0]->link; ?>" class="left"></a>
		<a href="<?php print $items[1]->link; ?>" class="right"></a>
		<div class="clear"></div>
	</div></div><?php
	}
?>

<div class="pageTextContW <?=(empty($page_res->php_file_path)) ? 'staticContent' : ''; ?>"><div class="pageTextCont">
	<?php if($page_res->id!=21 && $page_res->id!=22 && $page_res->id!=23 && $page_res->id!=26){ ?><h1><?php print $page_res->title; ?></h1><?php } ?>
	
	<p><?php
		if($page_res->id==21){
			?>
			<script type="text/javascript">
				$(document).ready(function(){
					var height = $("#layout_fix").height()+10;
					var content = $("#layout_fix");
					$("#layout_fix").remove();
					$(".pageTextCont").prepend(content);
					$(".pageTextContW").attr("style","padding-bottom:"+height+"px");

				});
			</script>
			<?
			print $page_res->content2.'<div id="layout_fix">'.$page_res->content.'</div>'; 
		}else{
			print $page_res->content2.$page_res->content; 
		}
		
	?></p>
</div></div>