<?php
class userModuleInfo {
	public $title="Модуль пользователей";

	public function admin_menu()
	{
		$d=array(
			"user:users"=>array(
				"Пользователи",
				"user:users"=>"Пользователи",
				"user:groups"=>"Группы",
				"user:history_login"=>"История входов",
				"user:add_user"=>"",
				"user:edit_user"=>"",
				"user:add_group"=>"",
				"user:edit_group"=>"",
                                "user:staff_stats"=>"Статистика добавления товаров",
                                "user:staff_user_stats"=>"",
			)
		);

		$this->ci=&get_instance();
		if(!$this->ci->load->access("users","user","module")){
                    unset($d['user:users']['user:users']);
                    unset($d['user:users']['user:add_user']);
                    unset($d['user:users']['user:edit_user']);
		}
                if(!$this->ci->load->access("groups","user","module")){
                    unset($d['user:users']['user:groups']);
                    unset($d['user:users']['user:add_group']);
                    unset($d['user:users']['user:edit_group']);
		}
                if(!$this->ci->load->access("history_login","user","module")){
                    unset($d['user:users']['user:history_login']);
		}
                if(!$this->ci->load->access("staff_stats","user","module")){
                    unset($d['user:users']['user:staff_stats']);
                    unset($d['user:users']['user:staff_user_stats']);
		}

		return $d;
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
				"method_name"=>"test",
				"title"=>"Тестовая страница"
			),
			array(
				"method_name"=>"login",
				"title"=>"Страница входа"
			),
			array(
				"method_name"=>"remind_password",
				"title"=>"Страница восстановления пароля"
			),
			array(
				"method_name"=>"register",
				"title"=>"Страница регистрации"
			)
		);
	}

	public function front_structure_sections()
	{
		return array(
			array(
				"method_name"=>"search",
				"title"=>"Список материалов по категории",
				"description"=>"список материалов",
				"options_method"=>"list_category_options"
			),
			array(
				"method_name"=>"test",
				"title"=>"Тестовый раздел модуля"
			)
		);
	}

	public function list_category_options(&$fb)
	{
		/*$options=$this->categoryes_options_list();

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
		));*/
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
        
        public function access_rules()
	{
		return array(
			"users"=>array(
				"type"=>"input:checkbox",
				"label"=>"управление пользователями"
			),
			"groups"=>array(
				"type"=>"input:checkbox",
				"label"=>"управление группами пользователей"
			),
			"history_login"=>array(
				"type"=>"input:checkbox",
				"label"=>"просмотр истории логинов"
			),
                        "staff_stats"=>array(
				"type"=>"input:checkbox",
				"label"=>"просмотр статистики добавления товаров"
			),
		);
	}
}
?>