<script>
$(document).ready(function(){
	$("table#table-products").find("tr").find("td input:checkbox:first, th input:checkbox:first").change(function(){
		changeTableCheckboxes();
	});
});

function changeTableCheckboxes()
{
	$("#form_field_mass_event").hide();
	if($("table#table-products").find("tr").find("td input:checkbox:first:checked").length>0){
		$("#form_field_mass_event").show();
	}
}

function catalogProductsMassEvent(that)
{
	var form=$(that).parents("form:eq(0)");
	form.attr("action","<?php print $this->admin_url; ?>?m=shop&a=products_mass_event&filter_keywords=<?php print $_GET['filter_keywords']; ?>&filter_category_id=<?php print $_GET['filter_category_id']; ?>&filter_photo=<?php print $_GET['filter_photo']; ?>&pg=<?php print $_GET['pg']; ?>");
	form.attr("method","post");
	form.find("input#m").remove();
	form.find("input#a").remove();
	form.submit();
}
</script>
<?php print $render; ?>