<?php
class standard_excel_importerPlugin extends Cms_plugins {
	function import_onMethodAfterSaveErrorsCheck()
	{
		$ci=&get_instance();

		if($_POST['importer']!="standard_excel_importer")return false;

		if(empty($_FILES['import_file']['tmp_name'])){
			$ci->module->d['global_errors'][]="Выберите файл для импортирования!";
		}

		if($ci->input->post("code_col")===false || $ci->input->post("code_col")==-1){
			$ci->module->d['global_errors'][]="Укажите колонку для артикула!";
		}
	}

	function import_onMethodBeforeSave()
	{
		$ci=&get_instance();

		if($_POST['importer']!="standard_excel_importer")return false;

		$ext=strtolower(end(explode(".",$_FILES['import_file']['name'])));
		$file_name=md5(uniqid(rand(),1)).md5(uniqid(rand(),1)).".import.".$ext;

		if(!is_dir("./uploads/shop_import/"))mkdir("./uploads/shop_import/",0777);
		move_uploaded_file($_FILES['import_file']['tmp_name'],"./uploads/shop_import/".$file_name);
	
		$ci->module->d['insert']['options']=json_encode(array(
			"file_name"=>$file_name,
			"file_original_name"=>$_FILES['import_file']['name'],
			"supplier"=>$_POST['supplier'],
			"code_col"=>$_POST['code_col'],
			"group_col"=>$_POST['group_col'],
			"title_col"=>$_POST['title_col'],
			"price_col"=>$_POST['price_col'],
			"price_old_col"=>$_POST['price_old_col'],
			"category_col"=>$_POST['category_col'],
			"short_description_col"=>$_POST['short_description_col'],
			"full_description_col"=>$_POST['full_description_col'],
			"show_col"=>$_POST['show_col']
		));
	}

	public function import_onMethodBeforeRender()
	{
		$ci=&get_instance();

		$options=$ci->fb->get("importer","options");

		$options['standard_excel_importer']="Стандартный Excel импорт";

		$ci->fb->change("importer",array("options"=>$options));

		$cols=array();
		$cols[-1]="--";
		foreach(range("a","z") as $letter)
		{
			$cols[]=strtoupper($letter);
		}

		$name=str_replace("Plugin","",__CLASS__);

		$ci->fb->add("input:file",array(
			"label"=>"Excel файл",
			"name"=>"import_file",
			"parent"=>"greed1",
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));

		$ci->fb->add("html",array(
			"content"=>"<h5>Настройка колонок</h5>",
			"name"=>"import_file",
			"parent"=>"greed1",
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));

		$shop_suppliers_res=$ci->db
		->get_where("shop_suppliers")
		->result();
		$options=array();
		if(sizeof($shop_suppliers_res)>0){
			foreach($shop_suppliers_res AS $r)
			{
				$r->title=trim($r->title);
				$options[$r->title]=$r->title;
			}
		}

		$supplier_html="";
		$supplier_html.=<<<EOF
<script>
function addSupplier()
{
	var new_name=$("#new_position_name").val().trim();

	if(new_name==""){
		alert('Введите имя поставщика!');
		return false;
	}

	var html='';
	html+='<option value="'+new_name+'">'+new_name+'</option>';
	$("#supplier").append(html);

	$("#supplier option:selected").removeAttr("selected");
	$("#supplier option:last").attr("selected",true);
	$("#addPositionModal button.close").click();
}
</script>
<div class="modal hide" id="addPositionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Добавить поставщика</h3>
  </div>
  <div class="modal-body">
	<form class="bs-docs-example form-inline">
		<input type="text" name="new_position_name" id="new_position_name" placeholder="Название поставщика">
	</form>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" type="button" aria-hidden="true">Отмена</button>
    <button class="btn btn-primary" type="button" onclick="addSupplier(); return false;">Добавить поставщика</button>
  </div>
</div>
&nbsp;&nbsp;
<a href="#addPositionModal" role="button" class="btn btn-mini btn-success" data-toggle="modal" onclick="return false;" style="position:relative; top:-6px;">+ добавить</a>
EOF;

		$ci->fb->add("list:select",array(
			"label"=>"Поставщик",
			"name"=>"supplier",
			"parent"=>"greed1",
			"options"=>$options,
			"hidden"=>true,
			"append"=>$supplier_html,
			"class"=>"hidden_fields hidden_".$name
		));

		$ci->fb->add("list:select",array(
			"label"=>"Артикул",
			"name"=>"code_col",
			"parent"=>"greed1",
			"options"=>$cols,
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));

		$ci->fb->add("list:select",array(
			"label"=>"Группа товаров",
			"name"=>"group_col",
			"parent"=>"greed1",
			"options"=>$cols,
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));

		$ci->fb->add("list:select",array(
			"label"=>"Наименование",
			"name"=>"title_col",
			"parent"=>"greed1",
			"options"=>$cols,
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));

		$ci->fb->add("list:select",array(
			"label"=>"Цена",
			"name"=>"price_col",
			"parent"=>"greed1",
			"options"=>$cols,
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));

		$ci->fb->add("list:select",array(
			"label"=>"Старая цена",
			"name"=>"price_old_col",
			"parent"=>"greed1",
			"options"=>$cols,
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));

		$ci->fb->add("list:select",array(
			"label"=>"Категория",
			"name"=>"category_col",
			"parent"=>"greed1",
			"options"=>$cols,
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name,
			"help"=>"полное наименование категории или ID"
		));

		$ci->fb->add("list:select",array(
			"label"=>"Краткое описание",
			"name"=>"short_description_col",
			"parent"=>"greed1",
			"options"=>$cols,
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));

		$ci->fb->add("list:select",array(
			"label"=>"Полное описание",
			"name"=>"full_description_col",
			"parent"=>"greed1",
			"options"=>$cols,
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));

		$ci->fb->add("list:select",array(
			"label"=>"Опубликован",
			"name"=>"show_col",
			"parent"=>"greed1",
			"options"=>$cols,
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));

		if($ci->input->post("code_col")===false){
			$ci->fb->change("code_col",array("value"=>-1));
			$ci->fb->change("title_col",array("value"=>-1));
			$ci->fb->change("group_col",array("value"=>-1));
			$ci->fb->change("price_col",array("value"=>-1));
			$ci->fb->change("price_old_col",array("value"=>-1));
			$ci->fb->change("category_col",array("value"=>-1));
			$ci->fb->change("short_description_col",array("value"=>-1));
			$ci->fb->change("full_description_col",array("value"=>-1));
			$ci->fb->change("show_col",array("value"=>-1));
		}
	}
}
?>