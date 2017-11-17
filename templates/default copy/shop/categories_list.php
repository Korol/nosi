<?php

// $this->meta($category_res->title." – Огромный выбор кухонных принадлежностей в интернет - магазине «Империя посуды». Звоните ☎(044) 599-2-555. Цены радуют","description");






if($_GET['category_id']<1){
	// $this->title("Купить посуду в интернет магазине Империя Посуды – Киев, Украина - im-ps.com","clear");
?>
<div class="searchW">
	<div class="searchI">
		<div class="search">
			<form method="get" action="/search.html">
				<input name="keywords" id="keywords" style="position:relative; top:5px;" type="text" value="" placeholder="Название или артикул..." /> <button class="btn">искать</button>
			</form>
		</div>
	</div>
</div>
<?php
	// дескрипшен на главной
	$this->meta("Посуда с доставкой по Киеву и Украине от интернет магазина Империя Посуда. Посуда от лучших мировых брендов Berghoff, Bohemia, Tefal.","description");
}else{
	$this->ci->config->config['site_default_title']="";
	$this->title($category_res->title." - Купить посуду в интернет магазине Империя Посуды – Киев, Украина — im-ps.com","clear");
	$this->meta("Купить ".$category_res->title." в Киеве и Украине. ".$category_res->title." в интернет магазине посуды Империя Посуда im-ps.com","description");
}

$shopModuleHelper=new shopModuleHelper;
?><div class="content1StructW">
	<div class="content1Struct1">
		<?php
		print $this->widgets("welcome");
		?>
		<div class="content1Struct1I">

			<?php
			if($_GET['category_id']<1){
			?>

			<noindex>
			<div class="categoriesMainPageW">
				<div class="categoriesMainPageI">

					<?php
					$itd=1;
					foreach($categories_res AS $r)
					{
						if($r->parent_id!=0)continue;

						$link=$shopModuleHelper->link_category($r);
					?>
					<div class="categoriesMainPageRow<?php print $itd%4==0?" categoriesMainPageRow4":""; ?>">
						<?php
						if(!empty($r->file_name)){
							?><a rel="nofollow" href="<?php print $link; ?>" class="thumb"><img src="/uploads/shop/category/thumbs/<?php print $r->file_name; ?>" /></a><?php
						}
						?>
						<a rel="nofollow" href="<?php print $link; ?>" class="title"><?php print $r->title; ?></a>
						<div class="subcats">
							<?php
							foreach($categories_res AS $r2)
							{
								if($r2->parent_id!=$r->id)continue;

								$link=$shopModuleHelper->link_category($r2);
								?><div class="subcatsRow"><a rel="nofollow" href="<?php print $link; ?>"><span><?php print $r2->title; ?></span></a></div><?php
							}
							?>
						</div>
					</div>
					<?php
						if($itd==4){
							$itd=0;
							?><div class="clear"></div><?php
						}
						$itd++;
					}
					?>
				</div>
			</div>
			</noindex>

			<?php
			$manufacturers_res=$this->db
			->select("uploads.file_path, uploads.file_name")
			->select("categoryes.*")
			->join("uploads","uploads.extra_id = categoryes.id && uploads.name = 'manufacturer_logo' && uploads.component_name = 'shop'")
			->get_where("categoryes",array(
				"type"=>"shop-manufacturer",
				"show"=>1
			))
			->result();
			?>
			<div class="brandsBoxW">
			<script>
			var current_pos=0;
			var slider_timer;
			$(document).ready(function(){
				$(".controlLeft").click(function(){
					clearTimeout(slider_timer);
					
					if(current_pos<1)return false;
					current_pos--;

					move_slider();
				});

				$(".controlRight").click(function(){
					clearTimeout(slider_timer);

					if(current_pos+1==$(".brandsBoxRow").length-6)return false;
					current_pos++;

					move_slider();
				});

				startSlider();
			});

			var slideBack=false;
			function startSlider()
			{
				slider_timer=setTimeout(function(){
					if(current_pos+1==$(".brandsBoxRow").length-6 || slideBack){
						slideBack=true;
						current_pos--;

						if(current_pos==-1){
							slideBack=false;
							current_pos=1;
						}
					}else{
						current_pos++;
					}

					move_slider();

					startSlider();
				},1000);
			}

			function move_slider()
			{
				$(".brandsBox").animate({marginLeft:-(current_pos*120)},200);
			}
			</script>
			<a href="#" onclick="return false;" class="control controlLeft"></a>
			<a href="#" onclick="return false;" class="control controlRight"></a>
				<div class="brandsBoxI">
					<div class="brandsBox">
					<table cellspacing="0" cellpadding="0" border="0">
					<tr>
						<?php
						foreach($manufacturers_res AS $r)
						{
							$link=$shopModuleHelper->link_manufacturer($r);
							?><td class="brandsBoxRow"><a href="<?php print $link; ?>" style="background-image:url(/uploads/shop/manufacturer/thumbs/<?php print $r->file_name; ?>);"><span></span></a></td><?
						}
						?>
					</tr>
					</table>
					</div>
				</div>
			</div>
			<?php
			print $this->widgets("frontpage-bottom");
			?>

			<?php
			include_once("modules/content/content.helper.php");
			$contentModuleHelper=new contentModuleHelper;
			$res=$contentModuleHelper
			->posts_query()
			->limit(3)
			->where("category_id",1522)
			->get()
			->result();

			if(sizeof($res)>0){
				?>
				<br /><br />
				<div class="btit">Новости</div>
				<div class="mainNewsListW">
					<div class="mainNewsListI">
						<div class="mainNewsList">
							<?php
							foreach($res AS $r)
							{
								$r->link=$contentModuleHelper->link_post_view($r);
								?><div class="mainNewsListRow">
								<a href="<?php print $r->link; ?>" class="title"><?php print $r->title; ?></a>
								<div class="text"><?php print $r->short_text; ?></div>
							</div><?php
							}
							?>
						</div>
					</div>
				</div>
				<?php
			}
			?>

			<?php
			}else{
				// $this->title($category_res->title." - Купить в Киеве, отличные цены, отзывы | Интернет-магазин кухонного инвентаря «Империя посуды»");
				?>
				<div class="catTitle catTitle2"><h1><?php print $category_res->title; ?></h1></div>
				
				<div class="categoriesMainPageW categoriesMainPageSubW">
				<div class="categoriesMainPageI">

					<?php
					$itd=1;
					foreach($categories_res AS $r)
					{
						$link=$shopModuleHelper->link_category($r);
					?>
					<div class="categoriesMainPageRow<?php print $itd%4==0?" categoriesMainPageRow4":""; ?>">
						<?php
						if(!empty($r->file_name)){
							?><a href="<?php print $link; ?>" class="thumb"><img src="/uploads/shop/category/thumbs/<?php print $r->file_name; ?>" /></a><?php
						}
						?>
						<a href="<?php print $link; ?>" class="title"><?php print $r->title; ?></a>
					</div>
					<?php
						if($itd==4){
							$itd=0;
							?><div class="clear"></div><?php
						}
						$itd++;
					}
					?>
				</div>
			</div>
			<div class="catDesc catDesc2">
				<?php
				if(!empty($category_res->description)){
					print $category_res->description."<p>&nbsp;</p>";
				}
				$citys=array("Киев","Харьков","Одесса","Днепропетровск","Донецк","Запорожье","Львов","Кривой Рог","Николаев","Мариуполь");
				$_citys=array();
				while(sizeof($citys)>0)
				{
					$k=array_rand($citys);
					$_citys[]=$citys[$k];
					unset($citys[$k]);
				}
				?>
				<p>Купить <?php print mb_strtolower($category_res->title); ?> - Отличные цены с доставкой по Украине: <?php print implode(", ",$_citys); ?></p>
			</div><?php
			}
			?>
		</div>
	</div>
</div>