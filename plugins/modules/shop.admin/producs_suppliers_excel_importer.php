<?php
class producs_suppliers_excel_importerPlugin extends Cms_plugins {
	function import_onMethodAfterSaveErrorsCheck()
	{
		$ci=&get_instance();

		if($_POST['importer']!="producs_suppliers_excel_importer")return false;

		if($ci->input->post("supplier")<1){
			$ci->module->d['global_errors'][]="Выберите поставщика!";
		}

		if(empty($_FILES['import_file']['tmp_name'])){
			$ci->module->d['global_errors'][]="Выберите файл для импортирования!";
		}
	}

	function import_onMethodBeforeSave()
	{
		$ci=&get_instance();

		if($_POST['importer']!="producs_suppliers_excel_importer")return false;

		$ext=strtolower(end(explode(".",$_FILES['import_file']['name'])));
		$file_name=md5(uniqid(rand(),1)).md5(uniqid(rand(),1)).".import.".$ext;

		$supplier=intval($ci->input->post("supplier"));
		$supplier_res=$ci->db
		->get_where("shop_suppliers",array(
			"id"=>$supplier
		))
		->row();

		if(is_string($supplier_res->options))$supplier_res->options=json_decode($supplier_res->options);

		if(!is_dir("./uploads/shop_import/"))mkdir("./uploads/shop_import/",0777);
		move_uploaded_file($_FILES['import_file']['tmp_name'],"./uploads/shop_import/".$file_name);
	
		$ci->module->d['insert']['options']=json_encode(array(
			"file_name"=>$file_name,
			"file_original_name"=>$_FILES['import_file']['name'],
			"supplier"=>$supplier,
			"code_col"=>$r->code_col,
			"group_col"=>$r->group_col,
			"title_col"=>$r->title_col,
			"price_col"=>$r->price_col,
			"price_old_col"=>$r->price_old_col,
			"category_col"=>$r->category_col,
			"short_description_col"=>$r->short_description_col,
			"full_description_col"=>$r->full_description_col,
			"show_col"=>$r->show_col
		));
	}

	public function import_onMethodBeforeRender()
	{
		$ci=&get_instance();

		$options=$ci->fb->get("importer","options");

		$options['producs_suppliers_excel_importer']="Импорт товаров по поставщикам";

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

		$shop_suppliers_res=$ci->db
		->get_where("shop_suppliers")
		->result();
		$options=array(-1=>"-- не выбран --");
		foreach($shop_suppliers_res AS $r)
		{
			$options[$r->id]=trim($r->title);
		}

		$ci->fb->add("list:select",array(
			"label"=>"Поставщик",
			"name"=>"supplier",
			"parent"=>"greed1",
			"options"=>$options,
			"hidden"=>true,
			"class"=>"hidden_fields hidden_".$name
		));
	}
}
?>