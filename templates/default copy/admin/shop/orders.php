<?php
if($this->input->get("iframe_display")===false){
?>
<ul class="nav nav-tabs">
	<li<?php print $this->input->get("v")!=="other"?' class="active"':''; ?>><a href="<?php print $this->admin_url; ?>?m=shop&a=orders">Новые заказы</a></li>
	<li<?php print $this->input->get("v")==="other"?' class="active"':''; ?>><a href="<?php print $this->admin_url; ?>?m=shop&a=orders&v=other">Обработанные заказы</a></li>
</ul>
<?php
}
?>
<div class="modal hide fade" id="statusModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 data-id="0">Статус заказа #0</h3>
	</div>
	<div class="modal-body">
		<p><strong>Новый статус заказа:</strong><br />
		<select name="status" id="status">
			<option value="">&nbsp;</option>
		<?php
		foreach($this->ci->module->order_status() AS $value=>$txt)
		{
			if($value=="-1")continue;
			?><option data-color="<?php print $this->ci->module->order_status($value,true); ?>" value="<?php print $value; ?>"><?php print $txt; ?></option><?php
		}
		?>
		</select></p>
		<strong>История:</strong><br />
		<div style="height:200px; overflow:auto; border:1px solid #CDCDCD;">
			<table id="status_history" class="table table-striped" width="100%">
			<tr>
				<th>Статус</th>
				<th>Дата</th>
				<th>Пользователь</th>
			</tr>
			</table>
		</div>
	</div>
	<div class="modal-footer">
		<a href="#" onclick="closeStatusModal(); return false;" class="btn">Отмена</a>
		<a href="#" onclick="changeStatus(); return false;" class="btn btn-primary">Изменить статус</a>
	</div>
</div>
<script>
function changeStatus()
{
	var status=$("#statusModal select#status").val();
	if(status==""){
		alert("Выберите новый статус из выпадающего списка!");
		return false;
	}
	var option=$("#statusModal select option[value='"+status+"']");
	var id=$("#statusModal h3").data("id");

	$.post("<?php print $this->admin_url; ?>?m=shop&a=change_status",{
		id:id,
		status:status
	},function(d){
		closeStatusModal();
		ordersInfo[id]=d.status_history;
		$("a.order_status[data-id='"+id+"']").css({
			color:option.data("color")
		}).html(option.text()).data("status",status).effect("bounce",{},1300);
	});
}

function closeStatusModal()
{
	$("#statusModal button.close").click();
}

function openStatusModal(o)
{
	var id=$(o).data("id");
	var status=$(o).data("status");

	$("#statusModal h3").html("Статус заказа #"+id).data("id",id);
	// $("#statusModal select#status").val(status);

	var html_status_history="";
	$.each(ordersInfo[id],function(i,v){
		html_status_history+='<tr>';
		html_status_history+='<td><span style="color:'+v.status_color+';">'+v.status_hmn+'</span></td>';
		html_status_history+='<td>'+v.date_add_hmn+'</td>';
		html_status_history+='<td><a href="<?php print $this->admin_url; ?>?m=user&a=edit_user&id='+v.user_id+'" target="_blank">'+v.username+'</a><br /><small>id: '+v.user_id+'</small></td>';
		html_status_history+='</tr>';
	});

	$("table#status_history tr:gt(0)").remove();
	$("table#status_history tr:first").after(html_status_history);

	$("#statusModal").modal();
}

var ordersInfo=<?php
print empty($status_history) || sizeof($status_history)==0?"[]":json_encode($status_history);
?>;
</script>
<?php print $render; ?>