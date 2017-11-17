<script>
$(document).ready(function(){
	changeDiscountType();
	$("select#type").change(function(){
		changeDiscountType();
	});
});

function changeDiscountType()
{
	var type=$("select#type").val();

	$(".hidden_fields").hide();
	$(".hidden_additional_"+type).show();
}
</script>
<?php print $render; ?>