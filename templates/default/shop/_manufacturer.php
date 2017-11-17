<?php

$this->ci->config->config['site_default_title']=0;

$shopModuleHelper=new shopModuleHelper;
?><div class="content1StructW">
	<div class="content1Struct1">
		<?php
		print $this->widgets("welcome");
		?>
		<div class="content1Struct1I">

			<div class="categoriesMainPageW">
				<div class="categoriesMainPageI">

					<h1 class="category_title"><?php print $manufacturer_res->title; ?></h1>

					<?php
					$itd=1;
					foreach($categories_res AS $r)
					{
						if($r->parent_id!=0)continue;

						$link=$shopModuleHelper->link_category($r);
						$link.="?manufacturer_id=".$_GET['manufacturer_id'];
					?>
					<div class="categoriesMainPageRow<?php print $itd%4==0?" categoriesMainPageRow4":""; ?>">
						<?php
						if(!empty($r->file_name)){
							?><a href="<?php print $link; ?>" class="thumb"><img src="/uploads/shop/category/thumbs/<?php print $r->file_name; ?>" /></a><?php
						}
						?>
						<a href="<?php print $link; ?>" class="title"><?php print $r->title; ?></a>
						<div class="subcats">
							<?php
							foreach($categories_res AS $r2)
							{
								if($r2->parent_id!=$r->id)continue;

								$link=$shopModuleHelper->link_category($r2);
								$link.="?manufacturer_id=".$_GET['manufacturer_id'];
								?><div class="subcatsRow"><a href="<?php print $link; ?>"><span><?php print $r2->title; ?></span></a></div><?php
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

			<?php
			if(!empty($manufacturer_res->description)){
				?><div class="brandDesc">
				<p><?php print $manufacturer_res->description; ?></p>
			</div><?php
			}
			/*<div class="categoriesMainPageW categoriesMainPageSubW">
				<div class="categoriesMainPageI">

					<?php
					$itd=1;
					foreach($categories_res AS $r)
					{
						$link=$shopModuleHelper->link_category($r);

						$link.="?manufacturer_id=".$_GET['manufacturer_id'];
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
			</div>*/
			?>
		</div>
	</div>
</div>