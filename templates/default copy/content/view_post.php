<?php
$this->title($post_res->title,"append");
?><div class="content1StructW">
	<div class="content1Struct1">
		<div class="content1Struct1I">
			<div class="catTitle catTitle2"><h1><?php print date("d.m.Y",$post_res->date_public)." - ".$post_res->title; ?></h1></div>

			<div class="newsViewW">
				<div class="newsViewI">
					<div class="newsView">
						<?php print $post_res->full_text; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>