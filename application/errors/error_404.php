<?php
$ci=&get_instance();
$ci->load->helper('url');
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Ошибка 404 - Страница не существует</title>
<style type="text/css">

::selection{ background-color: #E13300; color: white; }
::moz-selection{ background-color: #E13300; color: white; }
::webkit-selection{ background-color: #E13300; color: white; }

body {
	background-color: #fff;
	margin: 40px;
	font: 13px/20px normal Helvetica, Arial, sans-serif;
	color: #4F5155;
}

a {
	color: #003399;
	background-color: transparent;
	font-weight: normal;
}

h1 {
	color: #444;
	background-color: transparent;
	border-bottom: 1px solid #D0D0D0;
	font-size: 19px;
	font-weight: normal;
	margin: 0 0 14px 0;
	padding: 14px 15px 10px 15px;
}

code {
	font-family: Consolas, Monaco, Courier New, Courier, monospace;
	font-size: 12px;
	background-color: #f9f9f9;
	border: 1px solid #D0D0D0;
	color: #002166;
	display: block;
	margin: 14px 0 14px 0;
	padding: 12px 10px 12px 10px;
}

#container {
	margin: 10px;
	border: 1px solid #D0D0D0;
	-webkit-box-shadow: 0 0 8px #D0D0D0;
}

p {
	margin: 12px 15px 12px 15px;
}
</style>
</head>
<body>
	<div id="container">
		<h1><?php
		if($status_code==404){
			?>Ошибка 404 - Страница не существует<?php
		}else{
			echo $heading;
		}
		?></h1>
		<?php

		if($status_code==404){
			?>
            <p>Запрашиваемая вами страница не найдена.</p>
            <ul>
                <li><a href="<?=base_url(); ?>">Главная</a></li>
                <li><a href="<?=base_url('woman'); ?>">Женщины</a></li>
                <li><a href="<?=base_url('muzhskaya-odezhda'); ?>">Мужчины</a></li>
                <li><a href="<?=base_url('children-clothing-shoes'); ?>">Дети</a></li>
            </ul>
            <?php
			/*
			if($ci->config->config['404_page_menu_id']>0){
				$i=1;
				$active_menu_items=$ci->menu->get_active_menu_item_ids();
				$items=$ci->menu->get_menu_items($ci->config->config['404_page_menu_id']);
				?><ul><?php
				foreach($items AS $r)
				{
					if($r->parent_id!=$ci->config->config['404_page_menu_id'])continue;
					$a=in_array($r->id,$active_menu_items)?' class="active"':'';
					$r->title=str_replace(" ","&nbsp;",$r->title);
					?><li<?php print $a; ?>><a href="<?php print $r->link; ?>"><?php print $r->title; ?></a></li><?php

					$i++;
				}
				?></ul><?php
			}*/
		}else{
		echo $message;	
		}
		 ?>
	</div>
</body>
</html>