<?php
$active_menu_items=$this->menu->get_active_menu_item_ids();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru-RUS" xml:lang="ru-RUS" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<?php print $this->printHead(); ?>
	<link rel="stylesheet" type="text/css" href="/assets/css/main.css?3463875734" />
	<link rel="stylesheet" type="text/css" href="/assets/bootstrap/css/bootstrap.css" />
	<script src="/assets/js/jquery-1.9.1.js" type="text/javascript"></script>
	<script src="/assets/js/jquery-ui-1.10.0.custom.min.js" type="text/javascript"></script>
	<script src="http://jquery.com/jquery-wp-content/themes/jquery/js/jquery-migrate-1.1.1.min.js"></script>
	<script src="/assets/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

	<script src="/assets/js/main.js" type="text/javascript"></script>

	<script src="/assets/jquery.selectbox-0.2/js/jquery.selectbox-0.2.min.js" type="text/javascript"></script>
	<link href="/assets/jquery.selectbox-0.2/css/jquery.selectbox.css" type="text/css" rel="stylesheet" />
</head>
<body>
	<div id="pageWrap">
		<div id="pageContent">
			
			<div class="headW"><div class="head">
				<script type="text/javascript">
				var doors={};
				$(document).ready(function(){
					var doorWidth=110;
					var doorsWidth=$(".doorsW").width();
					$(".doorsW .door").draggable({
						axis:"x",
						containment:".doorsW",
						start:function(){
							$(this)
							.data("type","current");

							var doorsIndex=parseInt($(this).data("index").split(":")[0]);
							var doorIndex=parseInt($(this).data("index").split(":")[1]);
							if(doorIndex>0){
								for(var i=doorIndex;i!=0;i--)
								{
									doors[doorsIndex+":"+(i-1)]
									.data("type","left");
								}
							}

							for(var i=doorIndex;i<$(".doorsW:eq("+doorsIndex+") .door").length-1;i++)
							{
								doors[doorsIndex+":"+(i+1)]
								.data("type","right");
							}
						},
						stop:function(){
							$.each(doors,function(index,door){
								door
								.data("type","");
							});
						},
						drag:function(event,ui){
							var dragable=$(this);

							i=0;
							$.each(doors,function(index,current_door){
								if(current_door.data("type")!="current" || true){
									var that=current_door;
									var left=parseInt(current_door.css("left"));
									var right=left+doorWidth;

									if(index.split(":")[1]>0){
										// проверяем есть ли что-то слева
										var prev=doors[index.split(":")[0]+":"+(index.split(":")[1]-1)];
										var l=parseInt(prev.css("left"));
										var r=l+doorWidth;

										if(prev.data("type")=="left" && left>=l-50 && left<=r){
											var new_left=left-doorWidth-1;
											var min_left=prev.prevAll(".door").length*doorWidth;

											if(new_left>min_left){
												// prev.css({"left":new_left});
												moveDoor(prev,new_left);
											}else{
												// prev.css({"left":min_left});
												moveDoor(prev,min_left);
											}
										}
									}

									if(typeof doors[index.split(":")[0]+":"+(parseInt(index.split(":")[1])+1)]!="undefined"){
										// проверяем есть ли что-то справа
										var next=doors[index.split(":")[0]+":"+(parseInt(index.split(":")[1])+1)];
										var l=parseInt(next.css("left"));
										var r=l+doorWidth;

										if(next.data("type")=="right" && right>=l && right<=r){
											var max_left=doorsWidth-((next.nextAll(".door").length+1)*doorWidth);
											if(right<max_left){
												moveDoor(next,right);
												// next.css({"left":right});
											}else{
												moveDoor(next,max_left);
												// next.css({"left":max_left});
											}
										}
									}
									i++;
								}
							});



							var left=parseInt(dragable.css("left"));
							var right=left+doorWidth;

							$("span",dragable).css("background-position","-"+(ui.position.left)+"px center");

							var min_left=dragable.prevAll(".door").length*doorWidth;
							if(left<min_left){
								// dragable.css("left",min_left);
								moveDoor(dragable,min_left);
								return false;
							}

							var max_left=doorsWidth-((dragable.nextAll(".door").length+1)*doorWidth);
							if(left>max_left){
								// dragable.css("left",max_left);
								moveDoor(dragable,max_left);
								return false;
							}
						}
					});

					$(".doorsW").each(function(i1){
						var doorsNum=$(".door",this).length;

						var that=this;

						// doorsNum
						$(".door",this).each(function(i){
							$(this).data("index",i1+":"+i);
							doors[i1+":"+i]=$(this);
							var one_margin=$(that).width()-(doorsNum*doorWidth);
							one_margin=one_margin/(doorsNum-1);
							var margin=one_margin;

							if(i>1){
								margin+=one_margin;
							}
							if(i>2){
								margin+=one_margin;
							}

							if(i==0){
								margin=0;
							}
							// $(this).css("left",(doorWidth*i)+margin);
							moveDoor($(this),(doorWidth*i)+margin);
						});
					});
				});

				function moveDoor(o,left)
				{
					o.css("left",left);
					$("span",o).css("background-position","-"+(left)+"px center");
				}
				</script>
				<div class="doorsW"><div class="doors">
					<div class="door">
						<span></span>
					</div>
					<div class="door">
						<span></span>
					</div>
					<div class="door">
						<span></span>
					</div>
					<div class="door">
						<span></span>
					</div>
				</div></div>

				<div class="contacts">
					<div class="contactsRow contactsRow3">
						<div class="tit">
							<i></i>Наш адрес
						</div>
						<div class="cont">
							<?php print $this->widgets("head-address"); ?>
						</div>
					</div>


					<div class="contactsRow contactsRow2">
						<div class="tit">
							<i></i>Мобильный телефон
						</div>
						<div class="cont">
							<?php print $this->widgets("head-phone1"); ?>
						</div>
					</div>


					<div class="contactsRow contactsRow1">
						<div class="tit">
							<i></i>Стационарный телефон
						</div>
						<div class="cont">
							<?php print $this->widgets("head-phone2"); ?>
						</div>
					</div>

					<div class="clear"></div>
				</div>
				<div class="mainMenuW">
					<a href="/" class="logo"></a>
				<ul>
					<?php
					$items=$this->menu->get_menu_items(24);
					foreach($items AS $r)
					{
						if($r->parent_id!=24)continue;

						$a=in_array($r->id,$active_menu_items)?' class="active"':'';
						$r->title=str_replace(" ","&nbsp;",$r->title);
						?><li><a<?php print $a; ?> href="<?php print $r->link; ?>"><span><?php print $r->title; ?></span></a></li><?php
						
						$i++;
					}
					?>
				</ul>
				</div>
				<div class="clear"></div>
			</div></div>

			<div class="cols2structW"><div class="cols2struct">
				<div class="cols2struct1W"><div class="cols2struct1">

					<div class="boxLeftMenuW"><div class="boxLeftMenu">
						<div class="tit">Товары</div>
						<div class="cont">
						<ul>
							<?php
							$items=$this->menu->get_menu_items(1568);
							foreach($items AS $r)
							{
								if($r->parent_id!=1568)continue;

								$a=in_array($r->id,$active_menu_items)?' class="active"':'';
								$r->title=str_replace(" ","&nbsp;",$r->title);
								?><li><a<?php print $a; ?> href="<?php print $r->link; ?>#alvl"><?php print $r->title; ?></a></li><?php
								
								$i++;
							}
							?>
						</ul>
						</div>
					</div></div>

					<div class="boxLeftMenuW"><div class="boxLeftMenu">
						<div class="tit">Материалы</div>
						<div class="cont">
						<ul>
							<?php
							$items=$this->menu->get_menu_items(1581);
							foreach($items AS $r)
							{
								if($r->parent_id!=1581)continue;

								$a=in_array($r->id,$active_menu_items)?' class="active"':'';
								$r->title=str_replace(" ","&nbsp;",$r->title);
								?><li><a<?php print $a; ?> href="<?php print $r->link; ?>#alvl"><?php print $r->title; ?></a></li><?php
								
								$i++;
							}
							?>
						</ul>
						</div>
					</div></div>

					<?php print $this->widgets("left"); ?>

				</div></div>


				<div id="alvl" class="cols2struct2W"><div class="cols2struct2">

					<?php print $this->widgets("content-top"); ?>
					<?php print $content; ?>

				</div></div>

				<div class="clea"></div>
			</div></div>

			<div class="clear"></div>
		</div>
	</div>
	<div id="pageFooter">
		<div class="col1">
			<div class="bottomMenu">
				<?php
				$i=1;
				$items=$this->menu->get_menu_items(24);
				foreach($items AS $r)
				{
					if($r->parent_id!=24)continue;

					$a=in_array($r->id,$active_menu_items)?' class="active"':'';
					$r->title=str_replace(" ","&nbsp;",$r->title);
					?><a<?php print $a; ?> href="<?php print $r->link; ?>"><?php print $r->title; ?></a><?php
					
					if(sizeof($items)!=$i){
						?> &nbsp;|&nbsp; <?php
					}

					$i++;
				}
				?>
			</div>
			<div class="copyright"><?php print $this->widgets("copyright"); ?></div>
		</div>
		<div class="col2">
			<div class="contacts">
				<div class="address">
					<i></i><div class="tit">Адрес:</div>
					<div class="cont">
						<?php print $this->widgets("foot-address"); ?>
					</div>
				</div>

				<div class="email">
					<i></i><div class="tit">Электронная почта:</div>
					<div class="cont">
						<?php print $this->widgets("foot-email"); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="col3">
			<a href="/" class="logo2"><img src="/assets/media/logo2.png" alt="" /></a>
		</div>
	</div>
<?php print $this->config->config['template_bottom_scripts']; ?>
</body>
</html>