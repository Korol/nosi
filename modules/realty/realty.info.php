<?php
class realtyModuleInfo {
	public $title="Модуль каталога недвижимости";

	public function admin_menu()
	{
		return array(
			"realty:items"=>array(
					"Каталог недвижимости",
					"realty:items"=>"Недвижимость/Земля",
					"realty:add_item_m"=>"",
					"realty:edit_item_m"=>"",
					
					"realty:cats"=>"Категории",
					"realty:add_cat"=>"",
					"realty:edit_cat"=>""
			)
		);
	}

	public function admin_config()
	{
		return array(
			array(
				"name"=>"Настройки каталога недвижимости",
				"type"=>"group"
			),
			array(
				"name"=>"Обработка изображений недвижимости",
				"var_name"=>"mod_realty[images_options]",
				"type"=>"textarea",
				"config_file_name"=>"config.php"
			),
			array(
				"name"=>"Обработка изображений категорий",
				"var_name"=>"mod_realty[categories_images_options]",
				"type"=>"textarea",
				"config_file_name"=>"config.php"
			)
		);
	}

	public function front_structure_pages()
	{
		return array(
			array(
				"method_name"=>"search",
				"title"=>"Страница поиска",
				"description"=>"страница поиска по материалам"
			),
			array(
				"method_name"=>"categories_list",
				"title"=>"Страница со списком всех категорий",
				"description"=>""
			),
			array(
				"method_name"=>"cart",
				"title"=>"Корзина",
				"description"=>""
			),
			array(
				"method_name"=>"order_success",
				"title"=>"Страница \"Заказ успешно оформлен\"",
				"description"=>""
			)
		);
	}

	public function front_structure_sections()
	{
		return array(
			array(
				"method_name"=>"category_base",
				"title"=>"Общая страница категорий",
				"description"=>"если категорий слишком много, можно добавить раздел такого типа, в таком случае все категории будут ссылаться на него",
				"multi_section"=>true
			),
			array(
				"method_name"=>"category",
				"title"=>"Список по категории",
				"description"=>"",
				"options_method"=>"list_realty_category_options",
				"multi_section"=>true
			),
			array(
				"method_name"=>"test",
				"title"=>"Тестовый раздел модуля"
			)
		);
	}

	public function list_realty_category_options(&$fb)
	{
		$options=$this->categoryes_options_list();

		$fb->add("list:select",array(
			"label"=>"Категория каталога",
			"name"=>"category_id",
			"parent"=>"greed",
			"primary"=>true,
			"options"=>$options,
			// прячем поле, оно будет показываться только если мы выбрали нужный раздел
			"hidden"=>true,
			// применяем к блоку поля класс по названию текущего метода, чтоб при выборе этого параметра показывать это поле
			"class"=>"hidden_fields ".__FUNCTION__,
		));

		$_POST['extra_name']="category_id";
		$_POST['extra_id']=$_POST['category_id'];
	}


	// возвращает дерево категорий
	private function categoryes_options_list($parent_id=0,$level=0,&$data=array())
	{
		$res=$this->db
		->get_where("categoryes",array(
			"type"=>"realty-category",
			"parent_id"=>$parent_id
		))
		->result();

		$level++;

		foreach($res AS $r)
		{
			$data[$r->id]=str_repeat("--",$level-1)." ".$r->title;
			$data[$r->id]=trim($data[$r->id]);
			$this->categoryes_options_list($r->id,$level,$data);
		}

		return $data;
	}

	public function translate_fields()
	{
		return array(
			"realty_items"=>array("title","location","short_desc","full_desc","params","meta_title","meta_keywords","meta_description")
		);
	}
}
?>