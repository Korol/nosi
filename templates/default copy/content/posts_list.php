<?php
if($search){
?>
<div class="contentPageW">
	<div class="contentPage">
		<h1 class="margin">Поиск</h1>
	</div>
	<div class="contentPageText">
		<div class="contentPageColsW">
			<div class="newsListW">
				<?php
				foreach($posts_res AS $k=>$r)
				{
					?><div class="newsListRow">
						<p><a href="<?php print $r->link; ?>"><?php print $r->title; ?></a></p>
						<?php
						print $r->short_text;
						?>
				</div><?php
				}
				?>
			</div>
		</div>
		<div class="clear"></div>
	</div>
</div><?php

}else{
?><div class="content1StructW">
	<div class="content1Struct1">
		<div class="content1Struct1I">
			<div class="catTitle catTitle2"><h1><?php print $category_res->title; ?></h1></div>

			<div class="newsListW">
			<?php
			$i=0;
			foreach($posts_res AS $k=>$r)
			{
			?>
			<div class="newsListRow">
				<a href="<?php print $r->link; ?>" class="title"><?php print date("d.m.Y",$r->date_public)." - ".$r->title; ?></a>
				<div class="text"><?php print $r->short_text; ?></div>
			</div><?php
			}
			?>
			</div>
		</div>
	</div>
</div><?php
}
?>