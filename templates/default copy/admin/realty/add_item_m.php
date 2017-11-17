<script>
function addCodeField(button)
{
	var html='';
	html+='<div class="codeFieldRow">';
	html+='<input type="text" name="code_alias[]" style="width:150px;" value="" />';
	html+='&nbsp;<a style="position:relative; top:-4px;" title="удалить артикул" href="#" onclick="$(this).parents(\'div:eq(0)\').remove(); return false;"><img src="/templates/default/admin/assets/icons/cross.png" alt="удалить артикул" /></a>';
	html+='</div>';

	$(button).after(html);
}

function changeProductType(value)
{
	$(".hidden_fields").hide();
	$(".hidden_additional_"+value).show();
}

$(document).ready(function(){
	changeProductType($("select#type_id").val());
});
</script>
<?php print $render; ?>