<script>
function addWidgetPosition()
{
	var new_name=$("#new_position_name").val().trim();

	if(new_name==""){
		alert('Введите название позиции!');
		return false;
	}

	var html='';
	html+='<option value="'+new_name+'">'+new_name+'</option>';
	$("#position").append(html);

	$("#position option:selected").removeAttr("selected");
	$("#position option:last").attr("selected",true);
	$("#addPositionModal button.close").click();
}
</script>
<div class="modal hide" id="addPositionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Добавить позицию</h3>
  </div>
  <div class="modal-body">
	<form class="bs-docs-example form-inline">
		<input type="text" name="new_position_name" id="new_position_name" placeholder="Имя позиции">
	</form>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Отмена</button>
    <button class="btn btn-primary" onclick="addWidgetPosition();">Добавить позицию</button>
  </div>
</div>


<?php print $render; ?>