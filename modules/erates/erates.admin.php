<?php

class eratesModule extends Cms_modules {
	function __construct()
	{
		parent::__construct();

		// подключаем библиотеку для отрисовки интерфейса (формы, таблицы и тп.)

		$this->ci->load->library("fb");
		$this->load->helper('url');
		$this->load->helper('cms');
	}
	/**
	 * Вкладка курсов
	 * List of Exchange Rates
	 */
	public function lister(){
            
            if(!$this->ci->load->check_page_access_new("lister","erates","module")) return;
            
//		$this->ci->load->check_page_access("config_accepted","admin","module");
		
		
		$d=array();

		if($this->input->get("id")!==false){
			$this->buttons("main",array(
				array("save"),
				//array("apply"),
				array("back",NULL,$this->admin_url."?m=erates&a=lister")
			));
		}
		// Получаем список курсов
		$e_rates = $this->db
			->get_where("e_rates")
			->result();
		// Генерим форму
		$content = array();
		foreach ($e_rates as $rate){
			//var_dump($rate);
			$content[] = array(
				'<div align="right">'.$rate->name.':</div>', 
				'<input type="text" name="e_rates['.$rate->var_name.']" value="'.$rate->value.'" />'
				);
		}

		// Сборка страницы
		$this->ci->fb->add("table",array(
			"parent"=>"main",
			"width"=>500,
			"rows"=>$content
		));

		$this->ci->fb->add("form",array(
			"name"=>"main",
			"parent"=>"form",
			"method"=>"post"
		));
		// Кнопки управления
		$buttons = array();
		$buttons[] = array("save");
		$this->buttons("main",$buttons);
		
		// Обновление курсов
		if($this->ci->fb->submit){
			
			$d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($d['global_errors'])==0){
				foreach ($_POST['e_rates'] as $key => $value){
					$value = floatval($value);
					$this->db
						->where(array(
							"var_name"=> $key
						))
						->update("e_rates",array(
							"value" => $value
						));
				}
				redirect($this->admin_url."?m=erates&a=lister");
			}else{
				echo '<pre>';
				echo 'Error:';
				var_dump($d['global_errors']);
				die();
			}
		}

		$d['render']=$this->ci->fb->render("form");

		$this->ci->load->adminView("erates/lister",$d);
	}
	




        /*
	public function login()
	{
		$d['login']=true;
		$this->ci->load->adminView("user/login",$d);
	}

	public function logout()
	{
		$this->ci->ion_auth->logout();

		redirect($this->admin_url."?m=user&a=login");
	}

	public function groups()
	{
		$d=array();

		$this->buttons("main",array(
			array("add","Добавить<br />группу",$this->admin_url."?m=user&a=add_group")
		));

		$d['users_res']=$this->db
		->get_where("groups",array(
		))
		->result();
		
		$rows=array();
		foreach($d['users_res'] AS $r)
		{
			$users_num=$this->db
			->where("group_id",$r->id)
			->count_all_results("users_groups");

			$rows[]=array(
				'<a href="'.$this->admin_url."?m=user&a=edit_group&id=".$r->id.'">'.$r->name.'</a>',
				$users_num,
				$r->admin_panel_access==1?"Да":"Нет",
				"buttons"=>array(
					array("pencil",$this->admin_url."?m=user&a=edit_group&id=".$r->id),
					array("cross",$this->admin_url."?m=user&a=rm_group&id=".$r->id)
				)
			);
		}

		$this->ci->fb->add("table",array(
			"id"=>"users",
			"parent"=>"table",
			"head"=>array(
				"Название группы",
				"Пользователей",
				"Доступ в админ. панель"
			),
			"rows"=>$rows
		));

		$d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("user/groups",$d);
	}

	public function edit_group($edit=false)
	{
		$_GET['id']=(int)$_GET['id'];
		$this->add_group(true);
	}

	public function add_group($edit=false)
	{
		$d=array();

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=user&a=groups");

		$this->buttons("form",$buttons);

		if($edit){
			$d['item_res']=$this->db
			->get_where("groups",array(
				"groups.id"=>$_GET['id']
			))
			->row();
		}

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"name",
			"parent"=>"greed"
		));

		$this->ci->fb->add("textarea",array(
			"label"=>"Описание",
			"name"=>"description",
			"parent"=>"greed"
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"admin_panel_access",
			"label"=>"разрешить доступ к админ. панели",
			"parent"=>"greed"
		));

		$this->ci->fb->add("access",array(
			"name"=>"access",
			"label"=>"Настройки прав и доступа",
			"parent"=>"greed",
			"name"=>"access_rules"
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed",
			"parent"=>"block"
		));

		$this->ci->fb->add("block",array(
			"name"=>"block",
			"parent"=>"form",
			"method"=>"post"
		));

		$this->ci->fb->add("form",array(
			"name"=>"form",
			"parent"=>"render",
			"method"=>"post"
		));

		if($edit && !$this->ci->fb->submit){
			$this->ci->fb->change("name",array("value"=>$d['item_res']->name));
			$this->ci->fb->change("description",array("value"=>$d['item_res']->description));
			if($d['item_res']->admin_panel_access==1){
				$this->ci->fb->change("admin_panel_access",array("attr:checked"=>true));
			}
			$this->ci->fb->change("access_rules",array("value"=>$d['item_res']->access_rules),false);
		}

		if($this->ci->fb->submit){
			$d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($d['global_errors'])==0){

				$access_rules=array();
				if($this->input->post("access_rules")!==false){
					$access_rules=$this->input->post("access_rules");
				}

				if($edit){
					$this->db
					->where(array(
						"id"=>$_GET['id']
					))
					->update("groups",array(
						"name"=>$this->input->post("name"),
						"description"=>$this->input->post("description"),
						"admin_panel_access"=>$this->input->post("admin_panel_access")==1?1:0,
						"access_rules"=>json_encode($access_rules)
					));
				}else{
					$this->db
					->insert("groups",array(
						"name"=>$this->input->post("name"),
						"description"=>$this->input->post("description"),
						"admin_panel_access"=>$this->input->post("admin_panel_access")==1?1:0,
						"access_rules"=>json_encode($access_rules)
					));
				}

				redirect($this->admin_url."?m=user&a=groups");
			}
		}

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("user/add_group",$d);
	}

	public function rm_group()
	{
		$id=(int)$this->input->get("id");

		$this->db
		->where(array(
			"id"=>$id
		))
		->delete("groups");

		redirect($this->admin_url."?m=user&a=groups");
	}

	public function history_login()
	{
		$d=array();

		$d['log_res_num']=$this->db
		->where(array(
			"type"=>"admin-login"
		))
		->count_all_results("log");

		$pagination=$this->ci->fb->pagination_init($d['log_res_num'],10,current_url_query(array("pg"=>NULL)),"pg");

		$order_by_direction=current(array_keys($_GET['order_by']['users']));
		if($order_by_direction!="asc")$order_by_direction="desc";
		switch(current($_GET['order_by']['users']))
		{
			case'users.first_name':
				$order_by="users.first_name";
			break;
			case'log.description':
				$order_by="log.description";
			break;
			default:
			case'log.date_add':
				$order_by="log.date_add";
			break;
		}

		$d['log_res']=$this->db
		->select("log.*")
		->select("users.first_name, users.last_name")
		->join("users","users.id = log.user_id","left")
		->limit((int)$pagination->per_page,(int)$pagination->cur_page)
		->order_by("date_add","DESC")
		->get_where("log",array(
			"log.type"=>"admin-login"
		))
		->result();
		
		$rows=array();
		foreach($d['log_res'] AS $r)
		{
			$rows[]=array(
				$r->first_name.' '.$r->last_name.' (ID:'.$r->user_id.')',
				$r->description,
				date("d.m.Y H:i:s",$r->date_add)
			);
		}

		$this->ci->fb->add("table",array(
			"id"=>"users",
			"parent"=>"table",
			"head"=>array(
				array("Пользователь","order_by"=>"users.first_name"),
				array("Информация","order_by"=>"users.username"),
				array("Дата","order_by"=>"users.email")
			),
			"rows"=>$rows,
			"pagination"=>$pagination->create_links()
		));

		$d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("user/history_login",$d);
	}*/
}
?>