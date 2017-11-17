<?php

class userModule extends Cms_modules {
	function __construct()
	{
		parent::__construct();

		// подключаем библиотеку для отрисовки интерфейса (формы, таблицы и тп.)

		$this->ci->load->library("fb");
		$this->load->helper('url');
		$this->load->helper('cms');
	}

	public function users()
	{
            if(!$this->ci->load->check_page_access_new("users","user","module")) return;
            
		$d=array();

		$this->buttons("main",array(
			array("add","Добавить<br />пользователя",$this->admin_url."?m=user&a=add_user")
		));

		$d['users_res_num']=$this->db
		->count_all_results("users");

		$pagination=$this->ci->fb->pagination_init($d['users_res_num'],10,current_url_query(array("pg"=>NULL)),"pg");

		$order_by_direction=current(array_keys($_GET['order_by']['users']));
		if($order_by_direction!="asc") $order_by_direction="desc";
                
                $url_order = (!empty($_GET['order_by']['users'])) ? $_GET['order_by']['users'] : array('');
		switch(array_shift($url_order))
		{
			case'users.first_name':
				$order_by="users.first_name";
			break;
			case'users.username':
				$order_by="users.username";
			break;
			case'users.email':
				$order_by="users.email";
			break;
			case'users_groups.group_id':
				$order_by="users_groups.group_id";
			break;
			case'users.created_on':
				$order_by="users.created_on";
			break;
			default:
			case'users.last_login':
				$order_by="users.last_login";
			break;
		}

		$d['users_res']=$this->db
		->select("users.*, users_groups.group_id, groups.name AS `group_name`")
		->order_by($order_by,$order_by_direction)
		->limit((int)$pagination->per_page,(int)$pagination->cur_page)
		->join("users_groups","users_groups.user_id = users.id","left")
		->join("groups","groups.id = users_groups.group_id","left")
		->get_where("users",array())
		->result();
		
		$rows=array();
		foreach($d['users_res'] AS $r)
		{
			$rows[]=$this->input->get("iframe_display")!==false?
			array(
				'<a href="#" onclick="top.add_user_'.$this->input->get("f_id").'(this); return false;" data-id="'.$r->id.'" data-email="'.$r->email.'" data-first-name="'.$r->first_name.'" data-last-name="'.$r->last_name.'" data-username="'.$r->username.'">'.$r->first_name.' '.$r->last_name.'</a>',
				$r->username,
				'<a href="mailto:'.$r->email.'">'.$r->email.'</a>',
				$r->group_name
			)
			:array(
				'<a href="'.$this->admin_url.'?m=user&a=edit_user&id='.$r->id.'">'.$r->first_name.' '.$r->last_name.'</a>',
				$r->username,
				'<a href="mailto:'.$r->email.'">'.$r->email.'</a>',
				$r->group_name,
				date("d.m.Y H:i:s",$r->created_on),
				date("d.m.Y H:i:s",$r->last_login),
				"buttons"=>array(
					array("pencil",$this->admin_url."?m=user&a=edit_user&id=".$r->id),
					$r->id==$this->ci->session->userdata("user_id")?NULL:array("cross",$this->admin_url."?m=user&a=rm_user&id=".$r->id)
				)
			);
		}

		$this->ci->fb->add("table",array(
			"id"=>"users",
			"parent"=>"table",
			"head"=>$this->input->get("iframe_display")!==false?
			array(
				array("Имя","order_by"=>"users.first_name"),
				array("Логин","order_by"=>"users.username"),
				array("E-mail","order_by"=>"users.email"),
				array("Группа","order_by"=>"users_groups.group_id")
			)
			:array(
				array("Имя","order_by"=>"users.first_name"),
				array("Логин","order_by"=>"users.username"),
				array("E-mail","order_by"=>"users.email"),
				array("Группа","order_by"=>"users_groups.group_id"),
				array("Создан","order_by"=>"users.created_on"),
				array("Последний вход","order_by"=>"users.last_login")
			),
			"rows"=>$rows,
			"pagination"=>$pagination->create_links()
		));

		$d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("user/users",$d);
	}

	public function edit_user($edit=false)
	{
		$_GET['id']=(int)$_GET['id'];
		$this->add_user(true);
	}

	public function add_user($edit=false)
	{
            if(!$this->ci->load->check_page_access_new("users","user","module")) return;
            
		$d=array();

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=user&a=users");

		$this->buttons("form",$buttons);

		if($edit){
			$d['item_res']=$this->db
			->select("users.*, users_groups.group_id, groups.name AS `group_name`")
			->join("users_groups","users_groups.user_id = users.id","left")
			->join("groups","groups.id = users_groups.group_id","left")
			->get_where("users",array(
				"users.id"=>$_GET['id']
			))
			->row();
		}

		$options=array();

		$res=$this->db
		->select("id, name")
		->get_where("groups")
		->result();

		foreach($res AS $r)
		{
			$options[$r->id]=$r->name;
		}

		$this->ci->fb->add("list:select",array(
			"label"=>"Группа",
			"name"=>"group_id",
			"parent"=>"greed",
			"primary"=>true,
			"options"=>$options
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Имя",
			"name"=>"first_name",
			"parent"=>"greed",
			"primary"=>true
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Фамилия",
			"name"=>"last_name",
			"parent"=>"greed",
			"primary"=>true
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Логин",
			"name"=>"username",
			"parent"=>"greed",
			"primary"=>true,
			"check"=>array(
				"min_length"=>0
			)
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"E-mail",
			"name"=>"email",
			"parent"=>"greed",
			"primary"=>true,
			"check"=>array(
				"min_length"=>0
			)
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"block",
			"label"=>"заблокирован",
			"parent"=>"greed",
			"order"=>9999
		));

		$this->ci->fb->add("input:text",array(
			"label"=>$edit?"Изменить пароль":"Пароль",
			"name"=>"password",
			"parent"=>"greed",
			"primary"=>true,
			"check"=>$edit?array():array(
				"min_length"=>0
			)
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
			$this->ci->fb->change("group_id",array("value"=>$d['item_res']->group_id));
			$this->ci->fb->change("first_name",array("value"=>$d['item_res']->first_name));
			$this->ci->fb->change("last_name",array("value"=>$d['item_res']->last_name));
			$this->ci->fb->change("username",array("value"=>$d['item_res']->username));
			$this->ci->fb->change("email",array("value"=>$d['item_res']->email));

			if($d['item_res']->active!=1){
				$this->ci->fb->change("block",array("attr:checked"=>true));
			}
		}

		if($this->ci->fb->submit){
			$d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($d['global_errors'])==0){

				if($edit){
					if($this->input->post("password")!==false && $this->input->post("password")!=""){

						$salt       = $this->ci->ion_auth_model->store_salt ? $this->ci->ion_auth_model->salt() : FALSE;
						$password   = $this->ci->ion_auth_model->hash_password($this->input->post("password"), $salt);

						$this->db
						->where(array(
							"id"=>$_GET['id']
						))
						->update("users",array(
							"password"=>$password
						));
					}
					
					$this->db
					->where(array(
						"id"=>$_GET['id']
					))
					->update("users",array(
						"username"=>$this->input->post("username"),
						"email"=>$this->input->post("email"),
						"first_name"=>$this->input->post("first_name"),
						"last_name"=>$this->input->post("last_name"),
						"active"=>$this->input->post("block")==1?0:1
					));

					$this->db
					->where(array(
						"user_id"=>$_GET['id']
					))
					->delete("users_groups");

					$this->db
					->insert("users_groups",array(
						"user_id"=>$_GET['id'],
						"group_id"=>$this->input->post("group_id")
					));
				}else{
					$additional_data=array(
						"first_name"=>$this->input->post("first_name"),
						"last_name"=>$this->input->post("last_name"),
						"active"=>1
					);

					if($this->input->post("block")==1){
						$additional_data['active']=0;
					}

					$user_id=$this->ci->ion_auth->register($this->input->post("username"),$this->input->post("password"),$this->input->post("email"),$additional_data,array($this->input->post("group_id")));


					if($user_id===false){
						if(!isset($d['global_errors']))$d['global_errors']=array();
						$errors_array=$this->ci->ion_auth->errors_array();
						$d['global_errors']=$d['global_errors']+$errors_array;
					}
				}

				if(!isset($errors_array)){
					redirect($this->admin_url."?m=user&a=users");
				}
			}
		}

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("user/add_user",$d);
	}

	public function rm_user()
	{
            if(!$this->ci->load->check_page_access_new("users","user","module")) return;
            
		$id=(int)$this->input->get("id");

		$this->ci->ion_auth_model->delete_user($id);

		redirect($this->admin_url."?m=user&a=users");
	}

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
            if(!$this->ci->load->check_page_access_new("groups","user","module")) return;
            
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
                        
                    $descr = (!empty($r->description)) ? '&nbsp;&nbsp; <span style="font-size: 12px;">(' . $r->description . ')</span>' : '' ;

			$rows[]=array(
				'<a href="'.$this->admin_url."?m=user&a=edit_group&id=".$r->id.'">'.$r->name.'</a>',
				$users_num,
				($r->admin_panel_access==1) ? "Да" . $descr : "Нет",
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
            if(!$this->ci->load->check_page_access_new("groups","user","module")) return;
            
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
            if(!$this->ci->load->check_page_access_new("groups","user","module")) return;
            
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
            if(!$this->ci->load->check_page_access_new("history_login","user","module")) return;
            
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
	}
        
        public function staff_stats()
        {
            if(!$this->ci->load->check_page_access_new("staff_stats","user","module")) return;
            
            $get = $this->ci->input->get(NULL, TRUE);
            
            // работа с датами
            $func = function($value){ return sprintf('%d', $value); };
            $date_start_ex = (!empty($get['date_start'])) ? array_map($func, explode('.', $get['date_start'])) : array(1, date('n'), date('Y'));
            $date_end_ex = (!empty($get['date_end'])) ? array_map($func, explode('.', $get['date_end'])) : array(date('t'), date('n'), date('Y'));
            $start_ts = mktime(0, 0, 0, $date_start_ex[1], $date_start_ex[0], $date_start_ex[2]);
            $end_ts = mktime(23, 59, 59, $date_end_ex[1], $date_end_ex[0], $date_end_ex[2]);
            $data['dp_start_date'] = date('d.m.Y', $start_ts);
            $data['dp_end_date'] = date('d.m.Y', $end_ts);
            
            $data['staff'] = array();
            $data['products_cnt'] = 0;
            
            // получаем список сотрудников
            $staff = $this->ci->ion_auth->users(array(1, 3))->result_array();

            // получаем данные статистики
            $sql = "SELECT `user_id`, COUNT(`id`) AS `cnt` FROM `users_added_products` WHERE DATE(`added`) >= '" . date('Y-m-d', $start_ts) . "' AND DATE(`added`) <= '" . date('Y-m-d', $end_ts) . "' GROUP BY `user_id`";
            $stats = $this->db->query($sql)->result_array();
            if(!empty($stats)){
                $stats = array_by_index($stats, 'user_id');
                foreach ($staff as $pkey => $person){
                    $staff[$pkey]['cnt'] = (!empty($stats[$person['user_id']])) ? $stats[$person['user_id']]['cnt'] : 0;
                    $data['products_cnt'] += $staff[$pkey]['cnt'];
                }
                $staff = array_order_by($staff, 'cnt', SORT_DESC, 'user_id', SORT_DESC);
            }
            
            $data['staff'] = $staff;
            $this->ci->load->adminView("user/staff_stats", $data);
        }
        
        public function staff_user_stats()
        {
            if(!$this->ci->load->check_page_access_new("staff_stats","user","module")) return;
            
            $get = $this->ci->input->get(NULL, TRUE);
            
            if(empty($get['user_id'])){
                redirect(base_url('admin/?m=user&a=staff_stats'));
            }
            
            // работа с датами
            $func = function($value){ return sprintf('%d', $value); };
            $date_start_ex = (!empty($get['date_start'])) ? array_map($func, explode('.', $get['date_start'])) : array(1, date('n'), date('Y'));
            $date_end_ex = (!empty($get['date_end'])) ? array_map($func, explode('.', $get['date_end'])) : array(date('t'), date('n'), date('Y'));
            $start_ts = mktime(0, 0, 0, $date_start_ex[1], $date_start_ex[0], $date_start_ex[2]);
            $end_ts = mktime(23, 59, 59, $date_end_ex[1], $date_end_ex[0], $date_end_ex[2]);
            $data['dp_start_date'] = date('d.m.Y', $start_ts);
            $data['dp_end_date'] = date('d.m.Y', $end_ts);
            
            // получаем данные статистики
            $sql = "SELECT `uap`.`product_id`, DATE_FORMAT(`uap`.`added`, '%d.%m.%Y %H:%i:%s') AS `product_added`, `sp`.`title`, `sp`.`code`, `sp`.`category_ids`, `sp`.`name` FROM `users_added_products` `uap` LEFT JOIN `shop_products` `sp` ON `sp`.`id` = `uap`.`product_id` WHERE `uap`.`user_id` = '" . (int)$get['user_id'] . "' AND (DATE(`uap`.`added`) >= '" . date('Y-m-d', $start_ts) . "' AND DATE(`uap`.`added`) <= '" . date('Y-m-d', $end_ts) . "') ORDER BY `uap`.`added` DESC";
            $data['stats'] = $this->db->query($sql)->result_array();
            
            // получаем ID категорий товаров – для формирования ссылок на страницы товаров
            $data['links'] = array();
            if(!empty($data['stats'])){
                $ids = array();
                foreach($data['stats'] as $sk => $sv){
                    $data['stats'][$sk]['last_category_id'] = end(explode(',', $sv['category_ids']));
                    $ids[] = $data['stats'][$sk]['last_category_id'];
                }
                if(!empty($ids)){
                    $data['links'] = $this->get_products_links($ids);
                }
            }
            
            $data['user_id'] = $get['user_id'];
            $this->ci->load->adminView("user/staff_user_stats", $data);
            // TODO: постраничная навигация для списка товаров
            // TODO: сортировка по названию, артикулу, дате добавления товаров
        }
        
        public function get_products_links($ids)
        {
            $return = array();
            $result = $this->db->select('url, extra_id')
                    ->where('extra_name', 'category_id')
                    ->where_in('extra_id', $ids)
                    ->get('url_structure')->result_array();
            if(!empty($result)){
                foreach ($result as $row){
                    $return[$row['extra_id']] = $row['url'];
                }
            }
            return $return;
        }
}
?>