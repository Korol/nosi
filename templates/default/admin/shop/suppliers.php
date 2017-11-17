<?php
if($this->input->get("iframe_display")===false){
?>
<ul class="nav nav-tabs">
	<li><a href="<?php print $this->admin_url; ?>?m=shop&a=import">Импорт</a></li>
	<li class="active"><a href="<?php print $this->admin_url; ?>?m=shop&a=suppliers">Поставщики</a></li>
</ul>
<?php
}
?>
<?php print $render; ?>