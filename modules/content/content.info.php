<?php
class contentModuleInfo {
	public $title="Модуль материалов";

	function __construct()
	{
		$this->ci=&get_instance();
	}

	public function admin_menu()
	{
		return array(
			"content:posts"=>array(
					"Материалы",
					"content:posts"=>"Материалы",
					"content:cats"=>"Категории",
					"content:add_cat"=>"",
					"content:edit_cat"=>"",
					"content:add_post"=>"",
					"content:edit_post"=>""
			)
		);
	}

	public function admin_config()
	{
		return array(
			array(
				"name"=>"Настройки материалов",
				"type"=>"group"
			),
			array(
				"name"=>"Обработка главных изображений",
				"var_name"=>"config[mod_content_main_picture_options]",
				"type"=>"textarea",
				"config_file_name"=>"config.php"
			),
			array(
				"name"=>"Обработка главных изображений категории",
				"var_name"=>"config[mod_content_category_main_picture_options]",
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
			)
		);
	}

	public function front_structure_sections()
	{
		return array(
			array(
				"method_name"=>"category",
				"title"=>"Список материалов по категории",
				"description"=>"список материалов",
				"options_method"=>"list_category_options",
				"multi_section"=>true
			),
			array(
				"method_name"=>"test",
				"title"=>"Тестовый раздел модуля"
			)
		);
	}

	public function list_category_options(&$fb)
	{
		$options=$this->categoryes_options_list();

		$fb->add("list:select",array(
			"label"=>"Категория материалов",
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
			"type"=>"content-category",
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
}
?>