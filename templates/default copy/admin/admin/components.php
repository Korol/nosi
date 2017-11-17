<?php
if($this->input->get("iframe_display")===false){
?>
<ul class="nav nav-tabs">
	<li<?php print $_GET['t']=="widgets" || empty($_GET['t'])?' class="active"':''; ?>><a href="<?php print $this->admin_url; ?>?m=admin&a=components&t=widgets">Виджеты</a></li>
	<li<?php print $_GET['t']=="modules"?' class="active"':''; ?>><a href="<?php print $this->admin_url; ?>?m=admin&a=components&t=modules">Модули</a></li>
	<li<?php print $_GET['t']=="templates"?' class="active"':''; ?>><a href="<?php print $this->admin_url; ?>?m=admin&a=components&t=templates">Шаблоны</a></li>
	<li<?php print $_GET['t']=="install"?' class="active"':''; ?>><a href="<?php print $this->admin_url; ?>?m=admin&a=components&t=install">Установка</a></li>
	<li<?php print $_GET['t']=="rebuild_thumbs"?' class="active"':''; ?>><a href="<?php print $this->admin_url; ?>?m=admin&a=rebuild_thumbs">Перестройка изображений</a></li>
</ul>
<?php
}
?>
<?php print $render; ?>