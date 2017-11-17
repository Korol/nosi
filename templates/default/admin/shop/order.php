<script type="text/javascript">
$(document).ready(function(){
	$("input#phone").change(function(){
		if($.trim($(this).val())==""){
			$("#sms_send_declaration_sm").hide();
		}else{
			$("#sms_send_declaration_sm").show();
		}
		$("#sms_send_declaration_sm span").text($.trim($(this).val()));
	});

	if($.trim($("input#phone").val())==""){
		$("#sms_send_declaration_sm").hide();
	}else{
		$("#sms_send_declaration_sm").show();
	}
});

function sms_send_declaration()
{
	var phone=$.trim($("input#phone").val());
	var declaration=$.trim($("input#declaration").val());
	$.post(document.location.href,{
		sms_send_declaration_sm:1,
		phone:phone,
		declaration:declaration
	},function(d){
		if(parseInt(d)!=1){
			alert(d);
		}else{
			alert('SMS отправлено успешно!');
		}
	});
}
</script>
<?php print $render; ?>