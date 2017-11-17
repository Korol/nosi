<?php
$active_menu_items=$this->menu->get_active_menu_item_ids();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru-RUS" xml:lang="ru-RUS" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<?php print $this->printHead(); ?>
	<link rel="stylesheet" type="text/css" href="/assets/css/main.css?346387573434" />
	<link rel="stylesheet" type="text/css" href="/assets/bootstrap/css/bootstrap.css" />
	<script src="/assets/js/jquery-1.9.1.js" type="text/javascript"></script>
	<script src="/assets/js/jquery-ui-1.10.0.custom.min.js" type="text/javascript"></script>
	<script src="/assets/js/jquery-migrate-1.1.1.js"></script>
	<script src="/assets/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
	<script type="text/javascript"> 
	_shcp = []; _shcp.push({widget_id : 604560, widget : "Chat"}); (function() { var hcc = document.createElement("script"); hcc.type = "text/javascript"; hcc.async = true; hcc.src = ("https:" == document.location.protocol ? "https" : "http")+"://widget.siteheart.com/apps/js/sh.js"; var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(hcc, s.nextSibling); })();
	</script>
	<script>
		$(document).ready(function(){
			$("#emailShare").click(function(){
				open_emailShareModal();
				return false;
			});
		});

		function open_emailShareModal()
		{
			$("#emailShareModal").modal();
		}

		function close_emailShareModal()
		{
			$("#emailShareModal .close").click();
		}

		function send_emailShareModal()
		{
			var email=$("#emailShareModal #email").val();

			if(email==""){
				alert("Введите E-mail!");
				return false;
			}

			if(!/^([a-z0-9_.-]+)@([a-z0-9_.-]+)\.([a-z.]{2,6})$/.test(email)){
				alert("E-mail введен неправильно!");
				return false;
			}

			$.post(document.location.href,{
				email_share_sm:1,
				email:email
			},function(d){
				if(parseInt(d)!=1){
					alert(d);
				}else{
					close_emailShareModal();
				}
			});
		}

		function open_loginModal()
		{
			$("#loginModal").modal();
		}

		function close_loginModal()
		{
			$("#loginModal .close").click();
		}

		function send_loginModal()
		{
			var email=$("#loginModal #login_name").val();
			var password=$("#loginModal #login_password").val();

			if(password==""){
				alert("Введите пароль!");
				return false;
			}

			if(email==""){
				alert("Введите E-mail!");
				return false;
			}

			// if(!/^([a-z0-9_.-]+)@([a-z0-9_.-]+)\.([a-z.]{2,6})$/.test(email)){
			// 	alert("E-mail введен неправильно!");
			// 	return false;
			// }

			$.post("/login.html",{
				login_sm:1,
				ajax:1,
				email:email,
				password:password
			},function(d){
				if(parseInt(d)!=1){
					alert(d);
				}else{
					document.location.reload();
				}
			});
		}

		function showRemind(){
			close_loginModal();
			$("#popupRemind").modal();
		}


		function do_remind()
		{
			var email=$.trim($("#popupRemind input[name='email']").val());
			var password=$.trim($("#popupRemind input[name='password']").val());

			var err=[];
			// if(email=="")err[err.length]='Поле "Email" не заполнено!';
			// if(email!="" && !/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+$/.test(email))err[err.length]='Поле "Email" заполнено неверно!';

			if(err.length>0){
				var errors="";
				$.each(err,function(i,v){
					errors+=v+'\n';
				});
				alert(errors);
				return false;
			}

			$.post("/remind.html",{
				login_sm:1,
				ajax:1,
				email:email
			},function(d){
				if(parseInt(d)!=1){
					alert(d);
				}else{
					$("#popupRemind").modal();
					alert('Инструкция по восстановлению пароля отправлена вам на Email.');
				}
			});
		}
	</script>
</head>
<body>
	<div id="loginModal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Вход</h3>
		</div>
		<div class="modal-body">
			<br /><br />
			<form method="post" method="/login.html">
				<input type="text" name="login_name" id="login_name" placeholder="Логин" />
				<input type="password" name="login_password" id="login_password" placeholder="Пароль" />
				<br /><br />
				<a href="#" onclick="showRemind(); return false;">Вспомнить пароль</a>
			</form>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn btn-primary" onclick="send_loginModal();">Войти</a>
		</div>
	</div>
	<div class="popup modal hide fade" id="popupRemind">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Восстановление пароля</h3>
		</div>
		<div class="modal-body">
			<div class="form">
				
					<div class="formRow">
						<label>Логин: <span class="necessary">*</span></label><input type="text" name="email" value="" />
					</div>
					<div class="formRow formRowButton">
						<button type="button" onclick="do_remind(); return false;" name="register_sm">Отправить</button>
					</div>
				
			</div>
		</div>
	</div>
	<div id="bodyWrap">
		<div id="wrap">
			<div id="head">
				<a class="logo" href="/"><!-- --></a>
				<div class="contacts"><?php print $this->widgets("head-phone1"); ?></div>
				<div class="userP">
					<?php 
					if($this->ci->ion_auth->logged_in()===false){
						?><a href="#" class="login" onclick="open_loginModal(); return false;"><i></i>Вход</a>
						<a href="/regist.html" class="register" ><i></i>Регистрация</a><?php
					}else{
						?><div class="loggedIn"><a href="/profile.html">Привет, <?php print trim($this->session->userdata("username")); ?></a> <span class="cp">(<a href="/login.html?logout=1" class="login"> выход </a>)</span></div><?php
					} ?>
				</div>
				<div class="cart">
					<?php print $this->widgets("cart"); ?>
				</div>
				<div class="clear"><!-- --></div>
				<div class="mainmenu">
					<ul>
						<?php
						$items=$this->menu->get_menu_items(24);
						foreach($items AS $r)
						{
							if($r->parent_id!=24)continue;
							$a=in_array($r->id,$active_menu_items)?' class="active"':'';
							$r->title=str_replace(" ","&nbsp;",$r->title);
							?><li  class="item<?php print $r->id; ?>"><a<?php print $a; ?> href="<?php print $r->link; ?>"><span><?php print $r->title; ?></span></a><?php
								$child=$this->menu->get_menu_items($r->id);
								if($child){
									print '<div  class="subMenu"><div class="subMenuHead"></div><div class="subMenuCont"><ul>';
									foreach($child as $c){
										$a=in_array($c->id,$active_menu_items)?' class="active"':'';
										$c->title=str_replace(" ","&nbsp;",$c->title);
										?><li class="item<?php print $c->id; ?>"><a<?php print $a; ?> href="<?php print $c->link; ?>"><span><?php print $c->title; ?></span></a></li><?php
									}
									print '</ul></div><div class="subMenuFoot"></div></div>';
								}
								if($r->id==1562){
									$brand=$this->db->query("SELECT * FROM `categoryes` WHERE `type`='shop-manufacturer' && `show`='1' ORDER BY `title` ")->result();
									include_once("./modules/shop/shop.helper.php");
									$shopModuleHelper=new shopModuleHelper;
									print '<div  class="subMenu"><div class="subMenuHead"></div><div class="subMenuCont"><ul>';
									foreach($brand as $b){
										$a=in_array($c->id,$active_menu_items)?' class="active"':'';
										$link = $shopModuleHelper->link_manufacturer($b);
										?><li class="item<?php print $b->id; ?>"><a<?php print $a; ?> href="<?php print $link; ?>"><span><?php print $b->title; ?></span></a></li><?
									}
									print '</ul></div><div class="subMenuFoot"></div></div>';
								}
							?></li><?php
						}
						?>
					</ul>
				</div>
				<div class="search">
					<form method="get" action="/">
						<input type="submit" class="sbm" value="">
						<input type="text" name="keyword" class="keyword" />
					</form>
				</div>
				<div class="clear"><!-- --></div>
			</div>
			<div id="content">
				<?php if($page_res->id!=21){ print '<div class="headBorder"></div>'; } ?>
				<?php print $this->widgets("top"); ?>
				<?php if($this->widgets("mainCat")!=""){ print '<div class="mainCat">'.$this->widgets("mainCat").'<div class="clear"><!-- --></div></div>'; }?>
				<?php print $content; ?>
			</div>
		</div>
		<div id="footer">
			<div class="footMenu">
				<span class="footMenuTitle">Информация</span><br /><br />
				<ul>
					<?php
					$items=$this->menu->get_menu_items(1620);
					foreach($items AS $r)
					{
						$a=in_array($r->id,$active_menu_items)?' class="active"':'';
						$r->title=str_replace(" ","&nbsp;",$r->title);
						?><li><a<?php print $a; ?> href="<?php print $r->link; ?>"><span><?php print $r->title; ?></span></a></li><?php
						
						$i++;
					}
					?>
				</ul>
			</div>
			<div class="footMenu">
				<span class="footMenuTitle">Мой аккаунт</span><br /><br />
				<ul>
					<?php
					$items=$this->menu->get_menu_items(1626);
					foreach($items AS $r)
					{
						$a=in_array($r->id,$active_menu_items)?' class="active"':'';
						$r->title=str_replace(" ","&nbsp;",$r->title);
						?><li><a<?php print $a; ?> href="<?php print $r->link; ?>"><span><?php print $r->title; ?></span></a></li><?php
						
						$i++;
					}
					?>
				</ul>
			</div>
			<div class="footMenu">
				<span class="footMenuTitle">Мы в сети</span><br /><br />
				<ul>
					<?php
					$items=$this->menu->get_menu_items(1632);
					foreach($items AS $r)
					{
						$a=in_array($r->id,$active_menu_items)?' class="active"':'';
						$r->title=str_replace(" ","&nbsp;",$r->title);
						?><li><a<?php print $a; ?> href="<?php print $r->link; ?>"><span><?php print $r->title; ?></span></a></li><?php
						
						$i++;
					}
					?>
				</ul>
			</div>
			<div class="footerCont"><span>Контакты</span><br /><br /><?php print $this->widgets("footer-contact"); ?></div>
		</div>
	</div>
<?php print $this->config->config['template_bottom_scripts']; ?>
</body>
</html>