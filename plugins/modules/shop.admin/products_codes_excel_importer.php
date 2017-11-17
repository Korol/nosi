<?php
class products_codes_excel_importerPlugin extends Cms_plugins {
	function import_onMethodAfterSaveErrorsCheck()
	{
		$ci=&get_instance();

		if($_POST['importer']!="products_codes_excel_importer")return false;

		if(empty($_FILES['codes_import_file']['tmp_name'])){
			$ci->module->d['global_errors'][]="Выберите файл для импортирования!";
		}
	}

	function import_onMethodBeforeSave()
	{
		$ci=&get_instance();

		if($_POST['importer']!="products_codes_excel_importer")return false;

		$ext=strtolower(end(explode(".",$_FILES['codes_import_file']['name'])));
		$file_name=md5(uniqid(rand(),1)).md5(uniqid(rand(),1)).".import.".$ext;

		if(!is_dir("./uploads/shop_import/"))mkdir("./uploads/shop_import/",0777);
		move_uploaded_file($_FILES['codes_import_file']['tmp_name'],"./uploads/shop_import/".$file_name);
	
		$ci->module->d['insert']['options']=json_encode(array(
			"file_name"=>$file_name,
			"file_original_name"=>$_FILES['codes_import_file']['name'],
			"remove_all_code_aliases"=>$_POST['remove_all_code_aliases']==1?1:0
		));
	}

	public function import_onMethodBeforeRender()
	{
		$ci=&get_instance();

		$options=$ci->fb->get("importer","options");

		$options['products_codes_excel_importer']="Импорт артикулов";

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
			"help"=>"в первой колонке главный артикул, во всех остальных дополнительные",
			"name"=>"codes_import_file",
			"parent"=>"greed1",
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));

		$ci->fb->add("input:checkbox",array(
			"label"=>"удалить все дополнительные артикулы товара добавленные до этого",
			"name"=>"remove_all_code_aliases",
			"parent"=>"greed1",
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));
	}
}
?>