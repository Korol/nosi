<script>
$(document).ready(function(){
	change_event();
})
function change_event()
{
	var val=$("select#event").val();

	$(".hidden_fields").hide();

	switch(val)
	{
		case "change_category":
			$(".hidden_category_id").show();
		break;
		case "change_type":
			$(".hidden_type_id").show();
		break;
		case "show":
		case "hide":
		case "delete":
		break;
	}
}
</script>
<?php
print $render;
?>