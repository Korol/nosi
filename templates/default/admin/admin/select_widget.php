<script>
function addWidgetNext()
{
	if($("#widget:checked").length==0){
		alert('Выберите тип виджета!');
		return false;
	}

	document.location.href='<?php print $this->admin_url; ?>?m=admin&a=add_widget&id='+$("#widget:checked").val();
}
</script>
<?php print $render; ?>