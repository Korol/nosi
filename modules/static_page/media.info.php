<?php
class mediaModuleInfo {
	public $title="Модуль материалов";

	public function admin_menu()
	{
		return array(
			"media:browse"=>array(
					"Медиа",
					"media:browse"=>"Обзор"
			)
		);
	}

	public function front_structure_pages()
	{
		return array();
	}

	public function front_structure_sections()
	{
		return array();
	}

	public function list_category_options(&$fb)
	{
		$options=$this->categoryes_options_list();

		$fb->add("list:select",array(
			"label"=>"Позиция виджета",
			"name"=>"category_id",
			"parent"=>"greed",
			"primary"=>true,
			"options"=>$options,
			// прячем поле, оно будет показываться только если мы выбрали нужный раздел
			"hidden"=>true,
			// применяем к блоку поля класс по названию текущего метода, чтоб при выборе этого параметра показывать это поле
			"class"=>"hidden_fields ".__FUNCTION__,
		));
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