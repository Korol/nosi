<?php
if($this->input->get("iframe_display")===false){
?>
<ul class="nav nav-tabs">
	<li class="active"><a href="<?php print $this->admin_url; ?>?m=shop&a=import">Импорт</a></li>
	<li><a href="<?php print $this->admin_url; ?>?m=shop&a=suppliers">Поставщики</a></li>
</ul>
<?php
}
?>
<script>
$(document).ready(function(){
	$("select#importer").change(function(){
		importer_change();
	});
	importer_change();
});

function importer_change()
{
	var class_name="hidden_"+$("select#importer").val();

	$(".hidden_fields").hide();
	$(".hidden_fields input, .hidden_fields select").attr("disabled",true);

	$("."+class_name).show();
	$("."+class_name+" input, ."+class_name+" select").removeAttr("disabled");
}
</script>
<?php
print $render;
?>