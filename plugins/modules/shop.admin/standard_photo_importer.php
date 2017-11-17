<?php
class standard_photo_importerPlugin extends Cms_plugins {
	function import_onMethodAfterSaveErrorsCheck()
	{
		$ci=&get_instance();

		if($_POST['importer']!="standard_photo_importer")return false;

		if(empty($_FILES['import_photo_file']['tmp_name'])){
			$ci->module->d['global_errors'][]="Выберите архив для импортирования фотографий!";
		}
	}

	function import_onMethodBeforeSave()
	{
		$ci=&get_instance();

		if($_POST['importer']!="standard_photo_importer")return false;

		$ext=strtolower(end(explode(".",$_FILES['import_photo_file']['name'])));
		$file_name=md5(uniqid(rand(),1)).md5(uniqid(rand(),1)).".import.".$ext;

		if(!is_dir("./uploads/shop_import/"))mkdir("./uploads/shop_import/",0777);
		move_uploaded_file($_FILES['import_photo_file']['tmp_name'],"./uploads/shop_import/".$file_name);
		
		$ci->module->d['insert']['options']=json_encode(array(
			"file_name"=>$file_name,
			"file_original_name"=>$_FILES['import_photo_file']['name'],
			"rm_current_photo"=>$this->input->post("rm_current_photo")
		));
	}

	public function import_onMethodBeforeRender()
	{
		$ci=&get_instance();

		$options=$ci->fb->get("importer","options");

		$options['standard_photo_importer']="Стандартный фото импорт";

		$ci->fb->change("importer",array("options"=>$options));

		$name=str_replace("Plugin","",__CLASS__);

		$ci->fb->add("input:checkbox",array(
			"label"=>"Удалить имеющиеся фотографии товаров",
			"name"=>"rm_current_photo",
			"parent"=>"greed1",
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));

		$ci->fb->add("input:file",array(
			"label"=>"Zip архив",
			"name"=>"import_photo_file",
			"parent"=>"greed1",
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));
	}
}
?>