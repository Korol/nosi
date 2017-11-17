<script>
$(document).ready(function(){
	if($("#options_method").val()!=""){
		$("."+$("#options_method").val()).show();
	}

	$("form#form input.methods").change(function(){
		$("input#title").val($(this).data("title"));
		$("input#name").val($(this).data("name"));

		$(".hidden_fields").hide();
		$("#options_method").val("");
		if($(this).data("options_method")!=""){
			$("."+$(this).data("options_method")).show();
			$("#options_method").val($(this).data("options_method"));
		}
	});
});

function add_page_sm()
{
	$('form#form').find(".hidden_fields:hidden input, .hidden_fields:hidden textarea, .hidden_fields:hidden select").attr("disabled",true);
	$('form#form').append('<input type=\'hidden\' name=\'sm\' value=\'1\' />').submit();
}
</script>
<form enctype="multipart/form-data" method="post" name="form" id="form">
<div class="well">
<?php
foreach($this->modules AS $r)
{
	if(is_null($r->info))continue;

	if($section){
		if(!method_exists($r->info,"front_structure_sections"))continue;
		$pages=$r->info->front_structure_sections();
	}else{
		if(!method_exists($r->info,"front_structure_pages"))continue;
		$pages=$r->info->front_structure_pages();
	}

	if(!is_array($pages) || sizeof($pages)<1)continue;

	?><h5><?php print $r->info->title; ?></h5><?php
	foreach($pages AS $r2)
	{
		$method_exists_onclick="";
		$method_exists_disabled="";
		if(!$r2['multi_section'] && in_array($r2['method_name'],$added_module_action_list_ids[$r->name])){
			$method_exists_onclick=' onclick="alert(\'Этот раздел уже добавлен!\');"';
			$method_exists_disabled=' disabled="disabled"';
		}

		$s=$_POST['page'][$r->name]==$r2['method_name']?' checked="checked"':'';
		?><label<?php print $method_exists_onclick; ?>><input<?php print $s.$method_exists_disabled; ?> class="methods" data-title="<?php print $r2['title']; ?>" data-name="<?php print $r2['method_name']; ?>" data-options_method="<?php print $r2['options_method']; ?>" type="radio" name="page[<?php print $r->name; ?>]" value="<?php print $r2['method_name']; ?>" /> <?php print $r2['title']; ?></label><?php
		if(!empty($r2['description'])){
			?><div><small><?php print $r2['description']; ?></small></div><?php
		}
		?><br /><?php
	}
}
?>
</div>

<input type="hidden" id="options_method" name="options_method" value="<?php print $this->input->post("options_method"); ?>" />

<?php print $render; ?>
</form>