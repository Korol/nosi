<?php
// if($this->input->post("apply_sm")){
// 	if(isset($global_errors) && sizeof($global_errors)>0){
// 	}

// }
if(!$ajax){ // ajax

$this->title("Administrator panel");
$this->css("/templates/default/admin/assets/css/main.css");
$this->js("/templates/default/admin/assets/js/jquery-1.8.2.min.js");
$this->js("/templates/default/admin/assets/js/jquery-ui-1.9.1.custom.min.js");
$this->js("/templates/default/admin/assets/js/jquery.form.js");

$this->css("/templates/default/admin/assets/bootstrap/css/bootstrap.min.css");

$this->js("/templates/default/admin/assets/bootstrap/js/bootstrap.min.js");
$this->js("/templates/default/admin/assets/bootstrap/js/bootstrap-dropdown.js");

$this->css("/templates/default/admin/assets/bootstrap/css/datepicker.css");
$this->js("/templates/default/admin/assets/bootstrap/js/bootstrap-datepicker.js");

$this->js("/templates/default/admin/assets/js/main.js");
?><html>
<head>
<?php print $this->printHead(); ?>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<style>
#adminStructure1 {
	width:200px;
	padding:5px;
}
#adminStructure2 {
	padding:5px;
}
</style>
</head>
<body>
<?php
if($this->no_access_to_admin_panel){
	?><div style="width:600px; margin:0 auto; margin-top:10px;">
	<div class="alert alert-error">
		У вашей группы нет доступа к админ. панели!<br />
		Попробуйте <a href="<?php print $this->admin_url."?m=user&a=logout"; ?>">выйти</a> и войти под другим пользователем.
	</div>
</div><?php
}elseif($login){
?><?php
	if(isset($global_errors) && sizeof($global_errors)>0){
		?><div class="alert alert-error">
		<?php
		print implode("<br />",$global_errors);
		?></div><?php
		
	}
	if(isset($global_success) && sizeof($global_success)>0){
		?><div class="alert alert-success">
		<?php
		print implode("<br />",$global_success);
		?></div><?php
		
	}
	print $content;
	?><?php
}else{
	if($this->input->get("iframe_display")!==false){ // отображение в iframe
		if(isset($global_errors) && sizeof($global_errors)>0){
			?><div class="alert alert-error">
			<?php
			print implode("<br />",$global_errors);
			?></div><?php
			
		}
		if(isset($global_success) && sizeof($global_success)>0){
			?><div class="alert alert-success">
			<?php
			print implode("<br />",$global_success);
			?></div><?php
			
		}
		print $content;
	}else{
?>
<div class="navbar">
	<div class="navbar-inner">
		<div class="container">
			<a href="<?php print $this->admin_url; ?>" class="brand"><?php print $_SERVER['HTTP_HOST']; ?></a>
<ul class="nav nav-pills">
	<?php
	$_m=$_GET['m'];
	$_a=$_GET['a'];
	foreach($this->modules AS $r)
	{
		// у этого компонента нет меню в админ. панели, пропускаем
		if(!isset($r->info) || !method_exists($r->info,"admin_menu"))continue;

		foreach($r->info->admin_menu() AS $k=>$menu_r)
		{
			list($m,$a)=explode(":",$k);

			if(is_array($menu_r)){
				foreach($menu_r AS $k2=>$submenu_r)
				{
					if($k2=="0")continue;

					list($m2,$a2)=explode(":",$k2);

					if($m2==$_GET['m'] && $a2==$_GET['a']){
						$_m=$m;
						$_a=$a;
					}
				}
			}
		}
	}

	foreach($this->modules AS $r)
	{
		// у этого компонента нет меню в админ. панели, пропускаем
		if(!isset($r->info) || !method_exists($r->info,"admin_menu"))continue;

		foreach($r->info->admin_menu() AS $k=>$menu_r)
		{
			list($m,$a)=explode(":",$k);

			if($m==$_m && $a==$_a && is_array($menu_r)){
				foreach($menu_r AS $k2=>$submenu_r)
				{
					if($k2=="0")continue;

					list($m2,$a2)=explode(":",$k2);

					$s=$_GET['m']==$m2 && $_GET['a']==$a2?' class="active"':'';
					if(!empty($submenu_r)){
						?><li<?php print $s; ?>><a href="<?php print $this->admin_url."?m=".$m2."&a=".$a2; ?>"><?php print $submenu_r; ?></a></li><?php
					}
				}
			}
		}
	}
	?>
  <!--<li class="dropdown" id="menu1">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#menu1">
      Dropdown
      <b class="caret"></b>
    </a>
    <ul class="dropdown-menu">
      <li><a href="#">Action</a></li>
      <li><a href="#">Another action</a></li>
      <li><a href="#">Something else here</a></li>
      <li class="divider"></li>
      <li><a href="#">Separated link</a></li>
    </ul>
  </li>-->
</ul>

<ul class="nav pull-right">
  <li class="dropdown" id="profile">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#profile">
      <?php
      print trim($this->user->first_name." ".$this->user->last_name)
      ?>
      <b class="caret"></b>
    </a>
    <ul class="dropdown-menu">
      <li><a href="<?php print $this->admin_url."?m=user&a=edit_user&id=".$this->user->id; ?>">Личные данные</a></li>
      <li class="divider"></li>
      <li><a href="<?php print $this->admin_url."?m=user&a=logout"; ?>">Выход</a></li>
    </ul>
  </li>
</ul>
<script>
$(document).ready(function(){
	$('.dropdown-toggle').dropdown()
});
</script>



		</div>
	</div>
</div>


<table id="adminStructure" width="100%">
<tr>
<td valign="top" id="adminStructure1">
	<div class="well" style="padding:8px 0;">
		<ul class="nav nav-list">
			<li class="nav-header">
			Компоненты
			</li>
			<?php
                        // модули, доступные для группы текущего пользователя
                        $ia_user_group_id = $this->ion_auth->get_users_groups()->row()->id;
                        $ia_user_group_info = $this->ion_auth->group($ia_user_group_id)->row();
                        $ia_group_modules = array();
                        if(!empty($ia_user_group_info->access_rules)){
                            $ia_json = json_decode($ia_user_group_info->access_rules, TRUE);
                            $ia_group_modules = array_keys($ia_json['module']);
                        }
                        
			foreach($this->modules AS $r)
			{
				// у этого компонента нет меню в админ. панели, пропускаем
				if(!isset($r->info) || !method_exists($r->info,"admin_menu"))continue;

				foreach($r->info->admin_menu() AS $k=>$menu_r)
				{
					list($m,$a)=explode(":",$k);
                                        
                                        if(!in_array($m, $ia_group_modules)) continue; // модуля нет в списке разрешенных для группы

					$s=$m==$_m && $a==$_a?' class="active"':'';

					$name=is_string($menu_r)?$menu_r:current($menu_r);
					?><li<?php print $s; ?>><a href="<?php print $this->admin_url."?m=".$m."&a=".$a; ?>"><!--<span class="pull-right">0</span>--><?php print $name; ?></a></li><?php
				}
				
			}
			?>
		</ul>
	</div>
	<?php
	print $this->widgets("admin-left");
	?>
</td>
<td valign="top" id="adminStructure2">
	<?php
	if(isset($global_errors) && sizeof($global_errors)>0){
		?><div class="alert alert-error">
		<?php
		print implode("<br />",$global_errors);
		?></div><?php
		
	}
	if(isset($global_success) && sizeof($global_success)>0){
		?><div class="alert alert-success">
		<?php
		print implode("<br />",$global_success);
		?></div><?php
		
	}
	print $content;
	?>
</td>
<?php
	if(isset($this->buttons) && sizeof($this->buttons)>0){
		?><td width="80" align="center">
			<div class="fixed-nav"><?
		foreach($this->buttons AS $button)
		{
			if($button[0]=="language"){
				// languages select
				continue;
			}
			?><div class="nav-row">
				<?php
				if(!empty($button['onclick'])){
					?><a class="notr" href="#" onclick="<?php print $button['onclick']; ?>" data-url="<?php print $button[2]; ?>" data-name="<?php print $button[0]; ?>" data-form-name="<?php print $this->buttons_form_name; ?>" style="background-image:url(/templates/default/admin/assets/icons/32x32/<?php print $button[0]; ?>.png);"><span><?php print $button[1]; ?></span></a><?php
				}else{
					?><a href="#" onclick="return false;" data-url="<?php print $button[2]; ?>" data-name="<?php print $button[0]; ?>" data-form-name="<?php print $this->buttons_form_name; ?>" style="background-image:url(/templates/default/admin/assets/icons/32x32/<?php print $button[0]; ?>.png);"><span><?php print $button[1]; ?></span></a><?php
				}
				?>
			</div><?php
		}
		?>
			</div>
		</td><?
	}
?>
</tr>
</table>
<?php
	} // отображение в iframe
}
?>
</body>
</html>
<?php
} // ajax

else{ // ajax
	print $content;
} // ajax
?>