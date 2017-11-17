<?php
include_once("./modules/admin/admin.helper.php");

class adminModule extends adminModuleHelper {
	function __construct()
	{
		parent::__construct();

		// подключаем библиотеку для отрисовки интерфейса (формы, таблицы и тп.)

		$this->ci->load->library("fb");
		$this->ci->load->library("uploads");
		$this->load->helper('url');
		$this->load->helper('cms');
	}

	public function languages_check_tables()
	{
		$translate_fields_method="translate_fields";
		foreach($this->ci->modules AS $r)
		{
			$r->info->db=&$this->db;

			if(is_null($r->info))continue;
			if(!method_exists($r->info,$translate_fields_method))continue;

			foreach($r->info->$translate_fields_method() AS $table=>$fields)
			{
				// print "Table: ".$table."<br /><br />";
				$current_fields=$this->db
				->query("SHOW COLUMNS FROM ".$table)
				->result();

				foreach($fields AS $field)
				{
					foreach($this->ci->languages_res AS $language)
					{
						if($language->default==1 || $language->enabled!=1)continue;

						$find=false;
						foreach($current_fields as $current_field)
						{
							if("l_".$field."_".$language->code==$current_field->Field){
								$find=true;
							}
						}
						if(!$find){
							foreach($current_fields as $current_field)
							{
								if($field==$current_field->Field){
									$this->db
									->query("ALTER TABLE `".$table."` ADD `l_".$field."_".$language->code."` ".$current_field->Type." NOT NULL AFTER `".$field."`");
									// print "Can't find: "."l_".$field."_".$language->code."<br />\n";
								}
							}
						}
					}
				}
			}
		}

		redirect($this->admin_url."?m=admin&a=languages");
	}

	public function rebuild_thumbs()
	{
		if($this->input->post("do_rebuild")){
			$rebuilded_ids=$this->input->post("rebuilded_ids");

			$this->d['uploads_to_rebuild_num']=$this->db
			->where(array(
				"proc_config_var_name !="=>"",
				"image_size !="=>""
			))
			// ->where("id NOT IN ('".implode("','",$rebuilded_ids)."')")
			->count_all_results("uploads");

			$this->d['uploads_to_rebuild_res']=$this->db
			->limit(2)
			->where("id NOT IN ('".implode("','",$rebuilded_ids)."')")
			->get_where("uploads",array(
				"proc_config_var_name !="=>"",
				"image_size !="=>""
			))
			->result();

			$proc_configs=array();
			$this->ci->load->library("img");
			foreach($this->d['uploads_to_rebuild_res'] AS $r)
			{
				if(!file_exists($r->file_path.$r->file_name))continue;
				
				if(!isset($proc_configs[$r->proc_config_var_name])){
					$proc_configs[$r->proc_config_var_name]=$this->ci->db->get_where("config",array(
						"var_name"=>$r->proc_config_var_name
					))->row();
				}

				$out_files="";
				
				$proc=$this->ci->img->proc($r->file_path.$r->file_name,$proc_configs[$r->proc_config_var_name]->value);
				$out_files=implode("\n",$proc['out_files']);

				$this->db
				->where("id","")
				->update("uploads",array(
					"thumb_files"=>$out_files
				));
			}

			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Content-type: application/json');

			print json_encode(array(
				"uploads_to_rebuild_num"=>$this->d['uploads_to_rebuild_num'],
				"uploads_to_rebuild_res"=>$this->d['uploads_to_rebuild_res']
			));

			exit;
		}

		$this->ci->load->adminView("admin/rebuild_thumbs",$d);
	}

	public function index()
	{
		$d=array();

		$current_config=$this->db
		->select("var_name, value")
		->get_where("config",array(
			"var_name IN ('db[default][username]','db[default][password]','db[default][hostname]','db[default][database]')"=>NULL
		))
		->result();

		foreach($current_config AS $r)
		{
			if($r->var_name=="db[default][username]" && $r->value!=$this->ci->db->username){
				$val=$this->ci->db->username;
			}elseif($r->var_name=="db[default][password]" && $r->value!=$this->ci->db->password){
				$val=$this->ci->db->password;
			}elseif($r->var_name=="db[default][hostname]" && $r->value!=$this->ci->db->hostname){
				$val=$this->ci->db->hostname;
			}elseif($r->var_name=="db[default][database]" && $r->value!=$this->ci->db->database){
				$val=$this->ci->db->database;
			}else{
				continue;
			}
			$this->db
			->where("var_name",$r->var_name)
			->update("config",array(
				"value"=>$val
			));
		}

		$d['users_res']=$this->db
		->select("users.*, users_groups.group_id, groups.name AS `group_name`")
		->join("users_groups","users_groups.user_id = users.id","left")
		->join("groups","groups.id = users_groups.group_id","left")
		->order_by("created_on","DESC")
		->limit(5)
		->get_where("users",array())
		->result();

		$rows=array();
		foreach($d['users_res'] AS $r)
		{
			$rows[]=array(
				$r->first_name.' '.$r->last_name.' (ID:'.$r->id.') '.
				'<sup>'.$r->group_name.'</sup>',
				date("d.m.Y H:i:s",$r->created_on)
			);
		}

		$this->ci->fb->add("table",array(
			"title"=>"Последние добавленые пользователи",
			"id"=>"users",
			"parent"=>"greed",
			"head"=>array(
				"Пользователь",
				"Дата добавления"
			),
			"rows"=>$rows
		));

		$d['log_res']=$this->db
		->select("log.*")
		->select("users.first_name, users.last_name")
		->join("users","users.id = log.user_id","left")
		->limit(5)
		->order_by("date_add","DESC")
		->get_where("log",array(
			"log.type"=>"admin-login"
		))
		->result();

		$rows=array();
		foreach($d['log_res'] AS $r)
		{
			if(is_null($r->first_name)){
				$r->first_name='<span style="color:red;">- пользователь удален -</span>';
			}
			$rows[]=array(
				'<a href="'.$this->admin_url.'?m=user&a=edit_user&id='.$r->user_id.'">'.trim($r->first_name.' '.$r->last_name).' (ID:'.$r->user_id.')</a>'.
				'&nbsp;<sup><a onclick="$(this).parent().nextAll(\'small:eq(0)\').show(); $(this).parent().remove();  return false;" href="#">информация</a></sup><br /><small style="display:none;">'.$r->description.'</small>',
				date("d.m.Y H:i:s",$r->date_add)
			);
		}

		$this->ci->fb->add("html",array(
			"content"=>"&nbsp;&nbsp;&nbsp;",
			"parent"=>"greed"
		));

		$this->ci->fb->add("table",array(
			"title"=>"Последние входы в админ. панель",
			"id"=>"users",
			"parent"=>"greed",
			"head"=>array(
				"Пользователь",
				"Дата входа"
			),
			"rows"=>$rows
		));

		$this->ci->fb->add("greed:horizontal",array(
			"name"=>"greed",
			"parent"=>"table",
			"width"=>"100%",
			"child:valign"=>"top"
		));

		$d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("admin/index",$d);
	}

	private function rstructure($type="html",$selected=0,$parentId=0,&$data=array(),$level=0)
	{
		if($level==0 && $type=="html"){
			// рисуем корень
			$html.="<div class=\"siteStructureTree\"><ul class=\"tree\"><li class=\"last\">";
			
			$links=array(
				"['/templates/default/admin/assets/icons/folder_add.png','Добавить раздел','".$this->admin_url."?m=admin&a=add_structure_section&parent_id=".$r->id."']",
				"['/templates/default/admin/assets/icons/page_add.png','Добавить статическую страницу','".$this->admin_url."?m=admin&a=add_page&parent_id=".$r->id."']",
				"[]",
				"['/templates/default/admin/assets/icons/folder_add.png','Добавить раздел из модуля','".$this->admin_url."?m=admin&a=add_module_section&parent_id=".$r->id."']",
				"['/templates/default/admin/assets/icons/page_add.png','Добавить страницу из модуля','".$this->admin_url."?m=admin&a=add_module_page&parent_id=".$r->id."']"
			);
			
			$links=implode(",",$links);
			
			$html.="<img src=\"/templates/default/admin/assets/icons/folder.png\" /> <a href=\"#\" onclick=\"return false;\" onmouseup=\"cpsSimpleDropDownShow({obj:this,links:[".$links."]}); return false;\">КОРЕНЬ САЙТА</a>";
			
			$html.=$this->rstructure($type,$selected,$parentId,$data,$level+1);
			$html.="</li></ul></div>";
			
			return $html;
		}elseif($level==0 && ($type=="array-sections" || $type=="array-structure")){
			$data[0]="КОРЕНЬ САЙТА";
			$this->rstructure($type,$selected,$parentId,$data,$level+1);
		}
		
			$res=$this->db
			->order_by("order")
			->get_where("url_structure",array(
				"parent_id"=>$parentId,
				"in_basket"=>"0"
			))
			->result();
		
		$html="";
		
		if($type=="array-sections" || $type=="array-structure"){
			foreach($res AS $r)
			{
				$data[$r->id]=str_repeat("&nbsp;&nbsp;&nbsp;",$level).($level>0?" +- ":"").$r->title;
				$this->rstructure($type,$selected,$r->id,$data,$level+1);
			}
			
			return $data;
		}elseif(sizeof($res)>0 && $type=="html"){
			if($parentId==0){
				$html.="<div class=\"siteStructureTree\"><ul class=\"tree\">";
			}else{
				$html.="<ul>";
			}
			foreach($res AS $i=>$r)
			{
				$simpleFolder=false;
				if($r->type=="static_page"){
					// статическая страница
					$links=array_merge(array(
						"['/templates/default/admin/assets/icons/page_edit.png','Редактировать','".$this->admin_url."?m=admin&a=edit_page&id=".$r->extra_id."']",
						"[]"),
						$r->is_main_page==1?array():array(
						"['','Сделать главной страницей модуля','".$this->admin_url."?m=admin&a=structure_section_main_page&id=".$r->id."']",
						"[]"),
						array("['/templates/default/admin/assets/icons/page_delete.png','Удалить страницу','".$this->admin_url."?m=admin&a=rm_structure_section&id=".$r->id."',true]"
					));
				}elseif($r->type=="module_action-one"){
					// страница модуля
					$links=array_merge(array(
						),
						$r->is_main_page==1?array():array(
						"['','Сделать главной страницей модуля','".$this->admin_url."?m=admin&a=structure_section_main_page&id=".$r->id."']",
						"[]"),
						array("['/templates/default/admin/assets/icons/page_delete.png','Удалить страницу','".$this->admin_url."?m=admin&a=rm_structure_section&id=".$r->id."',true]"
					));
				}elseif($r->type=="module_action-list"){
					// раздел модуля
					$links=array(
						"['/templates/default/admin/assets/icons/folder_delete.png','Удалить раздел','".$this->admin_url."?m=admin&a=rm_structure_section&id=".$r->id."',true]"
					);
				}else{
					// обычный раздел
					$simpleFolder=true;
					$links=array(
						"['/templates/default/admin/assets/icons/folder_add.png','Добавить раздел','".$this->admin_url."?m=admin&a=add_structure_section&parent_id=".$r->id."']",
						"['/templates/default/admin/assets/icons/page_add.png','Добавить статическую страницу','".$this->admin_url."?m=admin&a=add_page&parent_id=".$r->id."']",
						"[]",
						"['/templates/default/admin/assets/icons/folder_add.png','Добавить раздел из модуля','".$this->admin_url."?m=admin&a=add_module_section&parent_id=".$r->id."']",
						"['/templates/default/admin/assets/icons/page_add.png','Добавить страницу из модуля','".$this->admin_url."?m=admin&a=add_module_page&parent_id=".$r->id."']",
						"[]",
						"['/templates/default/admin/assets/icons/folder_delete.png','Удалить раздел','".$this->admin_url."?m=admin&a=rm_structure_section&id=".$r->id."',true]"
					);
				}
				
				if($simpleFolder)$class=" folder";
				
				if(sizeof($res)==$i+1){
					$html.="<li class=\"last".$class."\" data-order=\"{$i}\" data-id=\"{$r->id}\" data-parent-id=\"{$r->parent_id}\">";
				}else{
					$html.="<li".(empty($class)?"":" class=\"".$class."\"")." data-order=\"{$i}\" data-id=\"{$r->id}\" data-parent-id=\"{$r->parent_id}\">";
				}
				
				$links=implode(",",$links);
				
				$icon="folder.png";
				if($r->type=="static_page"){
					$icon="page.png";
				}elseif($r->type=="module_action-one"){
					$icon="page_code.png";
				}elseif($r->type=="module_action-list"){
					$icon="folder_brick.png";
				}
				
				$html.="<img src=\"/templates/default/admin/assets/icons/".$icon."\" /> <a href=\"#\" onclick=\"return false;\" onmouseup=\"cpsSimpleDropDownShow({obj:this,links:[".$links."]}); return false;\">".$r->title."</a> <input type=\"text\" style=\"display:none; width:200px; font-size:9px;\" onclick=\"this.select();\" value=\"".$m->base_url.$r->url."\" />".($r->is_main_page?"<sup style=\"color:gray;\"> главная</sup>":"");
				$html.=$this->rstructure($type,$selected,$r->id,$data,$level++);
				$html.="</li>";
			}
			if($parentId==0){
				$html.="</ul></div>";
			}else{
				$html.="</ul>";
			}
			
			return $html;
		}
	}

	private function getStructureChildIds($structureId,&$data=array())
	{
		$data[]=$structureId;
		
		$res=$this->db
		->select("id")
		->get_where("url_structure",array(
			"parent_id"=>$structureId
		))
		->result();
		foreach($res AS $r)
		{
			$this->getStructureChildIds($r->id,$data);
		}
		
		return $data;
	}

	public function rm_structure_section($id=NULL)
	{
		if(isset($id)){
			$_GET['id']=$id;
		}
		$_GET['id']=(int)$_GET['id'];

		$structIds=$this->getStructureChildIds($_GET['id']);

		$res=$this->db
		->get_where("url_structure",array(
			"id IN(".implode(",",$structIds).")"=>NULL
		))
		->result();
		foreach($res AS $r)
		{
			switch($r->type)
			{
				// перед удалением записи из стуктуры сайта, удаляем страницу
				case 'static_page':
					$this->db
					->where(array(
						"id"=>$r->extra_id
					))
					->delete("pages");
				break;
			}
		}

		$this->db
		->where(array(
			"url_structure.id IN (".implode(",",$structIds).")"=>null
		))
		->delete("url_structure");
		
		redirect($this->admin_url."?m=admin&a=structure");
	}

	public function structure_section_main_page()
	{
		$_GET['id']=(int)$_GET['id'];

		$res=$this->db
		->select("url_structure.parent_id")
		->get_where("url_structure",array(
			"id"=>$_GET['id']
		))
		->row();

		$this->db
		->where(array(
			"parent_id"=>$this->parent_id,
			"is_main_page"=>1
		))
		->update("url_structure",array(
			"is_main_page"=>0
		));

		$this->db
		->where(array(
			"id"=>$_GET['id']
		))
		->update("url_structure",array(
			"is_main_page"=>1
		));
		
		redirect($this->admin_url."?m=admin&a=structure");
	}

	private function rebuildStructHashes($parentId)
	{
		global $db;
		
		$res=$db->sq("SELECT `id`,`url` FROM `url_structure` WHERE `parent_id`='".$parentId."';");
		foreach($res AS $r)
		{
			$this->rebuildStructHashes($r->id);
		}
	}

	public function structure()
	{
		$d=array();

		$this->ci->load->check_page_access("site_structure_accepted","admin","module");

		if($this->input->post("save_structure_order")!==false){

			if($this->input->post("order")!==false && is_array($this->input->post("order"))){
				foreach($this->input->post("order") AS $parent_id=>$childs)
				{
					foreach($childs AS $order=>$id)
					{
						$this->db
						->where("id",$id)
						->update("url_structure",array(
							"order"=>$order
						));
					}
				}
			}

			print 1;
			exit;
		}

		//$this->rebuildStructHashes(0);

		$d['structure']=$this->rstructure();

		$this->ci->load->adminView("admin/structure",$d);
	}

	public function config()
	{
		$this->ci->load->check_page_access("config_accepted","admin","module");
		
		$d=array();

		if($this->input->get("id")!==false){
			$this->buttons("main",array(
				array("save"),
				//array("apply"),
				array("back",NULL,$this->admin_url."?m=admin&a=config")
			));
		}

		// получаем список настроек и групп настроек
		if($this->input->get("id")===false){
			$this->config_res=$this->get_config("group");

			$rows=array();
			foreach($this->config_res AS $r)
			{
				$rows[]=array(
					'<a href="'.$this->admin_url.'?m='.$this->input->get("m").'&a=config&id='.$r->id.'">'.$r->name.'</a>'
				);
			}
		}else{
			$this->config_res=$this->get_config(NULL,$this->input->get("id"));

			$rows=array();
			foreach($this->config_res AS $r)
			{
				$element="";
				switch($r->type)
				{
					case'textarea':
						$element.='<textarea style="height:130px;" name="config_var['.$r->id.']">'.$r->value.'</textarea>';
					break;
					case'input:file':
						$element.='<input type="file" name="config_var['.$r->id.']" value="" />';
						if(!empty($r->value)){
							$element.='<br />файл: <a target="_blank" href="/'.$r->value.'" />'.$r->value.'</a>';
						}
					break;
					case'input:text':
						$element.='<input type="text" name="config_var['.$r->id.']" value="'.$r->value.'" />';
					break;
					case'input:checkbox':
						$s=$r->value==1?' checked="checked"':"";
						$element.='<input type="checkbox" name="config_var_checkbox['.$r->id.']" value="1"'.$s.' />';
						$element.='<input type="hidden" name="config_var['.$r->id.']" value="1" />';
					break;
					case'menuselect':
						// получаем все меню сайта из БД
						if(!isset($this->site_menus_res)){
							$this->site_menus_res=$this->db
							->get_where("categoryes",array(
								"type"=>"menu"
							))
							->result();
						}
						$element.='<select name="config_var['.$r->id.']">';
						$element.='<option value="0"> </option>';
						foreach($this->site_menus_res AS $menu_r)
						{
							$s=$r->value==$menu_r->id?' selected="selected"':'';
							$element.='<option'.$s.' value="'.$menu_r->id.'">'.$menu_r->title.'</option>';
						}
						$element.='</select>';
					break;
				}

				$rows[]=array(
					'<div align="right">'.$r->name.':</div>',
					$element
				);
			}
		}

		$this->ci->fb->add("table",array(
			"parent"=>"main",
			"width"=>500,
			"rows"=>$rows
		));

		$this->ci->fb->add("form",array(
			"name"=>"main",
			"parent"=>"form",
			"method"=>"post"
		));

		if($this->ci->fb->submit){
			$d['global_errors']=$this->ci->fb->errors_list();

			$config_var_ids=array_keys($_POST['config_var']);

			if(is_array($_FILES['config_var']['tmp_name'])){
				$config_var_ids=array_merge($config_var_ids,array_keys($_FILES['config_var']['tmp_name']));
			}

			$save_config_res=$this->db
			->get_where("config",array(
				"id IN ('".implode("','",$config_var_ids)."')"=>NULL,
				"type NOT IN ('group')"=>NULL
			))
			->result();

			$save_config=array();
			foreach($save_config_res AS $r)
			{
				$save_config[$r->id]=$r;
			}

			if(sizeof($d['global_errors'])==0){
				foreach($_POST['config_var'] AS $id=>$value)
				{
					if(is_null($save_config[$id]->type))continue;

					switch($save_config[$id]->type)
					{
						case'input:text':
						case'textarea':
							$this->db
							->where(array(
								"id"=>$id
							))
							->update("config",array(
								"value"=>$value
							));
						break;
						case'input:checkbox':
							$this->db
							->where(array(
								"id"=>$id
							))
							->update("config",array(
								"value"=>$_POST['config_var_checkbox'][$id]==1?1:0
							));
						break;
						case'menuselect':
							$this->db
							->where(array(
								"id"=>$id
							))
							->update("config",array(
								"value"=>$value
							));
						break;
						default:
							die("Неизвестный тип поля!<br />".$save_config[$id]->type);
						break;
					}
				}

				foreach($_FILES['config_var']['tmp_name'] AS $id=>$tmp_name)
				{
					if(empty($tmp_name))continue;

					$ext=strtolower(end(explode(".",$_FILES['config_var']['name'][$id])));

					$upload_path=$save_config[$id]->upload_path;
					$upload_path=str_replace("%FILE_EXT%",$ext,$upload_path);

					if(file_exists($save_config[$id]->upload_path)){
						unlink($save_config[$id]->upload_path);
					}

					move_uploaded_file($tmp_name,$upload_path);
					if(file_exists($upload_path)){
						$this->db
						->where(array(
							"id"=>$id
						))
						->update("config",array(
							"value"=>preg_replace("#^./#","",$upload_path)
						));
					}
				}

				$this->rebuild_config_file();

				redirect($this->admin_url."?m=admin&a=config&id=".intval($_GET['id']));
			}
		}

		$d['render']=$this->ci->fb->render("form");

		$this->ci->load->adminView("admin/config",$d);
	}

	public function languages()
	{
		$d=array();
		$d=array();

		$this->buttons("main",array(
			array("add","Добавить<br />язык",$this->admin_url."?m=admin&a=add_language")
		));

		$this->templates_res=$this->db
		->select("*")
		->get_where("languages")
		->result();

		$rows=array();
		foreach($this->templates_res AS $r)
		{


			$rows[]=array(
				$r->title." (".$r->code.")",
				"enabled"=>sizeof($this->templates_res)==1 || $r->default==1?null:array($this->admin_url."?m=admin&a=enabled_language&id=".$r->id."&enable=",$r->enabled),
				"default"=>sizeof($this->templates_res)==1?null:array($this->admin_url."?m=admin&a=default_language&id=".$r->id."&default=",$r->default),
				"buttons"=>array(
					sizeof($this->templates_res)==1 || $r->default==1?null:array("cross",$this->admin_url."?m=admin&a=rm_language&id=".$r->id)
				)
			);
		}

		$this->ci->fb->add("table",array(
			"parent"=>"table",
			"head"=>array(
				"Язык",
				"Включен",
				"По умолчанию"
			),
			"rows"=>$rows
		));

		$d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("admin/languages",$d);
	}

	public function components()
	{
		$this->ci->load->check_page_access("components_accepted","admin","module");

		$d=array();
		
		if($this->input->get("t")=="install"){
			$this->ci->fb->add("input:file",array(
				"label"=>"Установочный архив (.zip)",
				"name"=>"component_file",
				"parent"=>"greed"
			));

			$this->ci->fb->add("input:submit",array(
				"label"=>"Установить",
				"name"=>"sm",
				"parent"=>"greed",
				"primary"=>true
			));

			$this->ci->fb->add("input:hidden",array(
				"name"=>"sm",
				"value"=>"1",
				"parent"=>"block"
			));

			$this->ci->fb->add("greed:horizontal",array(
				"name"=>"greed",
				"parent"=>"block"
			));

			if($this->ci->fb->submit){
				$d['global_errors']=$this->ci->fb->errors_list();

				// если нет ошибок, добавляем компонент в БД
				if(sizeof($d['global_errors'])==0){
					$accepted_file_types=array("zip");

					if(!in_array(strtolower(end(explode(".",$_FILES['component_file']['name']))),$accepted_file_types)){
						$d['global_errors']=array("Файл неверного формата!");
					}

					if(sizeof($d['global_errors'])==0){
						$r=$this->extract_component($_FILES['component_file']['tmp_name']);
						if(!isset($r['error'])){
							$r=$this->install_component($r['path']);

							if(!isset($r['error'])){
								//print_r($r['component_id']);
								$d['global_success']=array("Расширение успешно установлено!");
							}else{
								$d['global_errors']=array($r['error']);
							}
						}else{
							$d['global_errors']=array($r['error']);
						}
					}
				}
			}
		}else{
			switch($this->input->get("t"))
			{
				default:
				case 'widgets':
					$type="widget";
					$head=array(
						"Название",
						"Включен",
						"Используется"
					);

					// это в случае если пользователь смотрит на таблицу из iframe
					if($this->input->get("iframe_display")!==false){
						$head=array(
							"Название",
							"Позиция в шаблоне",
							"Показывать на этой странице"
						);
					}
				break;
				case 'modules':
					$type="module";
					$head=array(
						"Название",
						"Включен"
					);
				break;
				case 'templates':
					$type="template";
					$head=array(
						"Имя шаблона",
						"Включен",
						"По умолчанию"
					);
				break;
			}
			$this->components_res=$this->db
			->select("*")
			->get_where("components",array(
				"type"=>$type
			))
			->result();

			if($this->input->get("widgets_options_json")!==false){
				$widgets_options_json=json_decode($this->input->get("widgets_options_json"));
			}

			if($this->input->get("t")=="widgets" || $this->input->get("t")==""){
				$d['widgets_res']=$this->db
				->select("components.*")
				->get_where("components",array(
					"components.type"=>"widget"
				))
				->result();

				foreach($d['widgets_res'] AS $r)
				{

					$cols=array();
					$cols[]=$r->title.(preg_match("#^admin_#is",$r->name)?" <sup style=\"color:gray;\">виджет админ. панели</sup>":"");
					$cols['enabled']=array($this->admin_url."?m=admin&a=enabled_widget_component&id=".$r->id."&enable=",$r->enabled);

					$cols[]=$this->db
					->where("widget_id",$r->id)
					->count_all_results("widgets");

					$cols['buttons']=array(
						// array("pencil",$this->admin_url."?m=admin&a=edit_widget&id=".$r->id),
						array("cross",$this->admin_url."?m=admin&a=rm_widget_component&id=".$r->id)
					);
					
					$rows[]=$cols;
				}
			}else{
				$rows=array();
				foreach($this->components_res AS $r)
				{
					switch($this->input->get("t"))
					{
						default:
						case 'modules':
							$rows[]=array(
								$r->title." (".$r->name.")",
								"enabled"=>sizeof($this->components_res)==1 || $r->system==1?null:array($this->admin_url."?m=admin&a=enabled_module&id=".$r->id."&enable=",$r->enabled),
								"buttons"=>array(
									$r->system!=1?array("disk",$this->admin_url."?m=admin&a=export_module&id=".$r->id):NULL,
									$r->system!=1?array("cross",$this->admin_url."?m=admin&a=remove_component&id=".$r->id):NULL
								)
							);
						break;
						case 'templates':
							$rows[]=array(
								$r->title." (".$r->name.")",
								"enabled"=>$r->name=="default"?NULL:array($this->admin_url."?m=admin&a=enabled_template&id=".$r->id."&enable=",$r->enabled),
								"default"=>sizeof($this->components_res)==1?NULL:array($this->admin_url."?m=admin&a=default_template&id=".$r->id."&default=",$r->default),
								"buttons"=>array(
									array("disk",$this->admin_url."?m=admin&a=export_template&id=".$r->id),
									$r->name=="default"?NULL:array("cross",$this->admin_url."?m=admin&a=remove_component&id=".$r->id)
								)
							);
						break;
					}
				}
			}

			$this->ci->fb->add("table",array(
				"parent"=>$this->input->get("iframe_display")===false?"block":"form",
				"head"=>$head,
				"rows"=>$rows
			));
		}

		if($this->input->get("iframe_display")===false){
			$this->ci->fb->add("block",array(
				"name"=>"block",
				"parent"=>"form",
				"method"=>"post"
			));
		}

		$this->ci->fb->add("form",array(
			"name"=>"form",
			"parent"=>"render",
			"method"=>"post"
		));

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("admin/components",$d);
	}

	public function widget_positions()
	{
		$d=array();

		$this->ci->load->adminView("admin/index",$d);
	}

	public function select_widget()
	{
		$this->ci->load->check_page_access("site_widgets_accepted","admin","module");

		$d=array();

		$this->buttons("main",array(
			array("next","Далее","onclick"=>"addWidgetNext(); return false;")
		));

		$d['widgets_res']=$this->db
		->select("components.*")
		->get_where("components",array(
			"components.type"=>"widget",
			"components.name NOT LIKE 'admin_%'"=>NULL
		))
		->result();

		$options=array();
		foreach($d['widgets_res'] AS $r)
		{
			$options[$r->id]=$r->title;
		}

		$this->ci->fb->add("list:radio",array(
			"label"=>"Тип виджета",
			"id"=>"widget",
			"name"=>"widget",
			"parent"=>"greed",
			"options"=>$options
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

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("admin/select_widget",$d);
	}

	public function edit_widget()
	{
		$this->add_widget(true);
	}

	public function add_widget($edit=false)
	{
		$this->ci->load->check_page_access("site_widgets_accepted","admin","module");

		$d=array();

		$id=intval($this->input->get("id"));

		if($id<1){
			redirect($this->admin_url."?m=admin&a=select_widget");
		}

		if(!$edit){
			$d['item_res']=$this->db
			->select("components.name")
			->get_where("components",array("components.id"=>$id))
			->row();
		}else{
			$d['item_res']=$this->db
			->select("components.name")
			->select("widgets.*")
			->join("components","components.id = widgets.widget_id")
			->get_where("widgets",array("widgets.id"=>$id))
			->row();

			$d['show_structure_ids']=array();
			$d['show_structure_res']=$this->db->get_where("components_view_rules",array(
				"component_type"=>"widget",
				"component_id"=>$id,
				"extra_name"=>"show-structure"
			))
			->result();
			foreach($d['show_structure_res'] AS $r)
			{
				$d['show_structure_ids'][]=$r->extra_val;
			}
		}

		if(!empty($d['item_res']->name) && file_exists("widgets/".$d['item_res']->name."/".$d['item_res']->name.".info.php")){
			include_once("widgets/".$d['item_res']->name."/".$d['item_res']->name.".info.php");
			$name=$d['item_res']->name."WidgetInfo";
			$d['widget_info']=new $name;
		}else{
			die("No widget found!");
		}

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=admin&a=widgets");

		$this->buttons("main",$buttons);

		$this->ci->fb->add("input:text",array(
			"label"=>"Название виджета",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true,
			"check"=>array(
				"min_length"=>0,
				"max_length"=>255
			)
		));

		// получаем системные данные виджета
		//file_exists()

		// получаем список позиций виджетов
		$d['widget_positions_res']=$this->db
		->select("widgets.*")
		->group_by("widgets.position")
		->get_where("widgets",array("widgets.position !="=>""))
		->result();

		$position_options=array();
		foreach($d['widget_positions_res'] AS $r)
		{
			$position_options[$r->position]=$r->position;
		}

		$this->ci->fb->add("list:select",array(
			"label"=>"Позиция виджета",
			"name"=>"position",
			"id"=>"position",
			"parent"=>"greed",
			"primary"=>true,
			"append"=>'&nbsp;&nbsp;<a href="#addPositionModal" role="button" class="btn btn-mini btn-success" data-toggle="modal" onclick="return false;" style="position:relative; top:-6px;">+ добавить</a>',
			"options"=>$position_options
		));

		$d['widget_info']->admin_options($this->ci->fb); // передача материалов для рендеринга в {виджет}->admin_options

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"enabled",
			"label"=>"опубликован",
			"parent"=>"tab2",
			"order"=>9999
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed",
			"parent"=>"tab1"
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"show_structure_all",
			"label"=>"Отображать виджет на всех страницах",
			"parent"=>"greed2"
		));

		$options=$this->structure_options_list(0,false);
		$this->ci->fb->add("list:checkbox",array(
			"name"=>"show_structure[]",
			"label"=>"ИЛИ Отображать виджет только на выбранных страницах",
			"parent"=>"greed2",
			"options"=>$options
		));

		$this->ci->fb->add("textarea",array(
			"name"=>"php_options",
			"label"=>"PHP код перед выполнением",
			"help"=>"PHP код который будет выполняться перед показом виджета, если вписать например: return false; виджет не будет показан на сайте",
			"attr:style"=>"width:500px; height:200px;",
			"parent"=>"greed2"
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed2",
			"parent"=>"tab2"
		));

		$this->ci->fb->add("tabs",array(
			"tabs"=>array(
				"tab1"=>"Основное",
				"tab2"=>"Настройки публикации"
			),
			"name"=>"tabs",
			"parent"=>"block"
		));

		$this->ci->fb->add("block",array(
			"name"=>"block",
			"parent"=>"main",
			"method"=>"post"
		));

		$this->ci->fb->add("form",array(
			"name"=>"main",
			"parent"=>"render",
			"method"=>"post"
		));

		if($edit){
			// если мы зашли на страницу редактирования уже добавленого виджета, заносим данные из БД в поля
			$this->ci->fb->change("title",array("value"=>$d['item_res']->title));
			$this->ci->fb->change("content",array("value"=>$d['item_res']->content));
			$this->ci->fb->change("php_options",array("value"=>$d['item_res']->php));
			$this->ci->fb->change("position",array("value"=>$d['item_res']->position));
			$this->ci->fb->change("show_structure[]",array("value"=>$d['show_structure_ids']));
			if($d['item_res']->enabled==1){
				$this->ci->fb->change("enabled",array("attr:checked"=>"true"));
			}
			if(in_array("all",$d['show_structure_ids'])){
				$this->ci->fb->change("show_structure_all",array("attr:checked"=>"true"));
			}
			foreach($this->ci->languages_res AS $language)
			{
				if($language->default==1 || $language->enabled!=1)continue;

				$this->ci->fb->change("content_".$language->code,array("value"=>$d['item_res']->{"l_content_".$language->code}));
			}
		}elseif(!$edit && !$this->ci->fb->submit){
			$this->ci->fb->change("enabled",array("attr:checked"=>"true"));
			$this->ci->fb->change("show_structure_all",array("attr:checked"=>"true"));
		}

		// кнопка "сохранить нажата", выводим ошибки и обновляем/добавляем записи в БД
		if($this->ci->fb->submit){
			// после сабмита, если была добавлена новая позиция, добавляем ее в список...
			if(!in_array($_POST['position'],$position_options)){
				$this->ci->fb->change("position",array(
					"options"=>array_merge($position_options,array($_POST['position']=>$_POST['position']))
				));
			}
			
			$d['global_errors']=$this->ci->fb->errors_list();

			$d['edit']=$edit;

			// если нет ошибок, добавляем компонент в БД
			if(sizeof($d['global_errors'])==0){
				if(method_exists($d['widget_info'],"admin_before_save")){
					$d['widget_info']->admin_before_save($d);
				}

				if($edit){
					$update=array(
						"title"=>$_POST['title'],
						"content"=>$_POST['content'],
						"php"=>$_POST['php_options'],
						"options"=>"",
						"position"=>$_POST['position'],
						"enabled"=>$_POST['enabled']==1?1:0,
						"date_add"=>mktime()
					);

					foreach($this->ci->languages_res AS $language)
					{
						if($language->default==1 || $language->enabled!=1)continue;

						$update['l_content_'.$language->code]=$this->input->post("content_".$language->code);
					}

					$this->db
						->where("widgets.id",$id)
						->update("widgets",$update);
				}else{
					$insert=array(
						"widget_id"=>$id,
						"title"=>$_POST['title'],
						"content"=>(string)$_POST['content'],
						"php"=>$_POST['php_options'],
						"options"=>"",
						"position"=>$_POST['position'],
						"enabled"=>$_POST['enabled']==1?1:0,
						"date_add"=>mktime()
					);
					
					foreach($this->ci->languages_res AS $language)
					{
						if($language->default==1 || $language->enabled!=1)continue;

						$insert['l_content_'.$language->code]=$this->input->post("content_".$language->code);
					}

					$this->db->insert("widgets",$insert);

					$id=$this->db->insert_id();
				}

				if(method_exists($d['widget_info'],"admin_after_save")){
					$d['widget_info']->admin_after_save($id,$d);
				}

				$show_structure=$this->input->post("show_structure");
				$this->db
				->where(array(
					"component_type"=>"widget",
					"component_id"=>$id,
					"extra_name"=>"show-structure"
				))
				->delete("components_view_rules");
				if($show_structure!==false){
					foreach($show_structure AS $structure_id)
					{
						$structure_id=(int)$structure_id;
						if($structure_id<1)continue;

						$this->db
						->insert("components_view_rules",array(
							"component_type"=>"widget",
							"component_id"=>$id,
							"extra_name"=>"show-structure",
							"extra_val"=>$structure_id
						));
					}
				}

				if($this->input->post("show_structure_all")!==false){
					$show_structure[]="all";
					$this->db
					->insert("components_view_rules",array(
						"component_type"=>"widget",
						"component_id"=>$id,
						"extra_name"=>"show-structure",
						"extra_val"=>"all"
					));
				}

				redirect($this->admin_url."?m=admin&a=widgets");
			}
		}

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("admin/add_widget",$d);
	}

	public function enabled_widget()
	{
		$this->ci->load->check_page_access("site_widgets_accepted","admin","module");

		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("widgets",array(
			"enabled"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=admin&a=widgets");
	}

	public function rm_widget()
	{
		$this->ci->load->check_page_access("site_widgets_accepted","admin","module");

		$this->db
		->where(array("id"=>$this->input->get("id")))
		->delete("widgets");

		redirect($this->admin_url."?m=admin&a=widgets");
	}

	public function add_language($edit=false)
	{
		$d=array();

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=admin&a=components&t=languages");

		$this->buttons("form",$buttons);

		$this->ci->fb->add("input:text",array(
			"label"=>"Название языка",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true,
			"check"=>array(
				"min_length"=>0,
				"max_length"=>50
			)
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Код языка",
			"name"=>"code",
			"parent"=>"greed",
			"primary"=>true,
			"check"=>array(
				"min_length"=>0,
				"max_length"=>50
			)
		));
		
		$this->ci->fb->add("input:checkbox",array(
			"name"=>"default",
			"label"=>"по умолчанию",
			"parent"=>"greed"
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"enabled",
			"label"=>"включен",
			"parent"=>"greed"
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

		if($this->ci->fb->submit){
			$d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($d['global_errors'])==0){

				if($_POST['default']==1){
					$this->db
					->where(array(
						"languages.default"=>1
					))
					->update("languages",array(
						"default"=>0
					));
				}

				if($edit){
					$this->db
						->where("languages.id",$id)
						->update("languages",array(
						"code"=>$_POST['code'],
						"title"=>$_POST['title'],
						"default"=>$_POST['default'],
						"enabled"=>$_POST['enabled']
					));
				}else{
					$this->db->insert("languages",array(
						"code"=>$_POST['code'],
						"title"=>$_POST['title'],
						"default"=>$_POST['default'],
						"enabled"=>$_POST['enabled']
					));
				}

				redirect($this->admin_url."?m=admin&a=languages");
			}
		}

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("admin/add_language",$d);
	}

	public function default_language()
	{
		$this->db
		->where(array("default"=>1))
		->update("languages",array(
			"default"=>0
		));

		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("languages",array(
			"default"=>1
		));

		redirect($this->admin_url."?m=admin&a=languages");
	}

	public function enabled_language()
	{
		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("languages",array(
			"enabled"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=admin&a=languages");
	}

	public function rm_language()
	{
		$this->db
		->where(array(
			"id"=>$this->input->get("id"),
			"default"=>0
			))
		->delete("languages");

		redirect($this->admin_url."?m=admin&a=languages");
	}

	public function enabled_module()
	{
		$this->db
		->where(array(
			"type"=>"module",
			"id"=>$this->input->get("id")
		))
		->update("components",array(
			"enabled"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=admin&a=components&t=modules");
	}

	private function getSectionURL($sectionId)
	{
		global $db;
		
		$url="";
		
		$res=$this->db
		->select("parent_id,name,type")
		->get_where("url_structure",array(
			"id"=>$sectionId
		))
		->row();
		
		if((int)$res->parent_id>0){
			$url.=$this->getSectionURL($res->parent_id);
		}
		$url=$url.$res->name.($res->type=="dir"?"/":"");
		
		return $url;
	}

	public function add_structure_section($edit=false)
	{
		$this->ci->load->check_page_access("site_structure_accepted","admin","module");

		$d=array();

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=admin&a=structure");

		$this->buttons("form",$buttons);

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true,
			"check"=>array(
				"min_length"=>0,
				"max_length"=>100
			)
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"URL",
			"name"=>"name",
			"parent"=>"greed",
			"primary"=>true,
			"check"=>array(
				"max_length"=>100
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

		if($this->ci->fb->submit){
			$d['global_errors']=$this->ci->fb->errors_list();

			// если нет ошибок, добавляем компонент в БД
			if(sizeof($d['global_errors'])==0){
				$name=$this->input->post("name");
				if($this->input->post("name")==""){
					$name=rewrite_alias($this->input->post("title"));
				}

				$parent_id=intval($this->input->get("parent_id"));
				$path=$this->getSectionURL($parent_id);
				$url=$path.(substr($path,-1)!="/"?"/":"").$name."/";
				if(substr($url,0,1)!="/")$url="/".$url;
				
				if($edit){
					$this->db
						->where("url_structure.id",$id)
						->update("url_structure",array(
						//"parent_id"=>$parent_id,
						"name"=>$name,
						"title"=>$this->input->post("title"),
						"enabled"=>1
					));
				}else{
					$order=intval($this->db
					->where("parent_id",$parent_id)
					->count_all_results("url_structure"));
					$order++;

					$this->db->insert("url_structure",array(
						"user_id"=>$this->ci->session->userdata("user_id"),
						"parent_id"=>$parent_id,
						"name"=>$name,
						"url"=>$url,
						"title"=>$this->input->post("title"),
						"type"=>"dir",
						"date_add"=>mktime(),
						"enabled"=>1,
						"order"=>$order
					));

					$this->rebuild_url_structure_orders();
				}

				redirect($this->admin_url."?m=admin&a=structure");
			}
		}

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("admin/add_structure_section",$d);
	}

	private function structure_options_list($parent_id=0,$dir_only=true,$level=0,&$data=array())
	{
		$res=$this->db
		->select("id, title, type")
        ->order_by('title', 'asc')
		->get_where("url_structure",array(
			"parent_id"=>$parent_id
		))
		->result();

		$level++;

		foreach($res AS $r)
		{
			if($dir_only && $r->type!="dir")continue;

			$data[$r->id]=str_repeat("--",$level-1)." ".$r->title;
			$data[$r->id]=trim($data[$r->id]);
			$this->structure_options_list($r->id,$dir_only,$level,$data);
		}

		return $data;
	}

	public function edit_page()
	{
		$_GET['id']=(int)$_GET['id'];

		$this->add_page(true);
	}

	public function add_page($edit=false)
	{
		$this->ci->load->check_page_access("site_structure_accepted","admin","module");

		$d=array();

		if($edit){
			$d['item_res']=$this->db
			->select("pages.*, url_structure.parent_id AS structure_parent_id2")
			->join("url_structure","url_structure.id = pages.structure_parent_id")
			->get_where("pages",array("pages.id"=>$_GET['id']))
			->row();
		}

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=admin&a=structure");

		$this->buttons("form",$buttons);

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true,
			"check"=>array(
				"min_length"=>0,
				"max_length"=>100
			),
			"translate"=>true
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"URL",
			"name"=>"name",
			"parent"=>"greed",
			"primary"=>true,
			"check"=>array(
				"max_length"=>100
			)
		));

		$options=$this->structure_options_list();

		$options=array("0"=>"КОРЕНЬ САЙТА")+$options;

		$this->ci->fb->add("list:select",array(
			"label"=>"Разместить в разделе",
			"name"=>"structure_parent_id",
			"parent"=>"greed3",
			"primary"=>true,
			"options"=>$options,
			"value"=>$this->input->get("parent_id")
		));

//		$this->ci->fb->add("upload:editor",array(
//			"label"=>"Прикрепить файлы",
//			"component_type"=>"module",
//			"component_name"=>"admin",
//			"extra_type"=>"page_id",
//			"extra_id"=>$edit?$_GET['id']:0,
//			"name"=>"attach",
//			"parent"=>"greed",
//			"dynamic"=>true,
//			"ordering"=>true
//		));

		$this->ci->fb->add("textarea:editor",array(
			"label"=>"Содержимое страницы",
			"name"=>"content",
			"id"=>"content",
			"parent"=>"greed",
			"attr:style"=>"height:700px; width:1074px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true,
			"check"=>array(
				//"min_length"=>0
			),
			"translate"=>true
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"ИЛИ Путь к РНР файлу",
			"name"=>"php_file_path",
			"id"=>"php_file_path",
			"parent"=>"greed3",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true,
			"translate"=>true
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"foot_and_head",
			"label"=>"показывать шапку и подвал",
			"parent"=>"greed3"
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"show",
			"label"=>"опубликовать",
			"parent"=>"greed3"
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed",
			"parent"=>"tab1"
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Meta title",
			"name"=>"meta_title",
			"parent"=>"greed2",
			"primary"=>true,
			"check"=>array(
				"max_length"=>255
			),
			"translate"=>true
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Meta keywords",
			"name"=>"meta_keywords",
			"parent"=>"greed2",
			"primary"=>true,
			"check"=>array(
				"max_length"=>255
			),
			"translate"=>true
		));

		$this->ci->fb->add("textarea",array(
			"label"=>"Meta description",
			"name"=>"meta_description",
			"parent"=>"greed2",
			"attr:style"=>"height:100px; width:300px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true,
			"translate"=>true
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"disallow_bot_index",
			"label"=>"запретить индексирование",
			"parent"=>"tab2"
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed2",
			"parent"=>"tab2"
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed3",
			"parent"=>"tab3"
		));

		$this->ci->fb->add("tabs",array(
			"tabs"=>array(
				"tab1"=>"Основное",
				"tab2"=>"SEO",
				"tab3"=>"Настройки публикации"
			),
			"name"=>"tabs",
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

		if($edit){
			// если мы зашли на страницу редактирования уже добавленого виджета, заносим данные из БД в поля
			$this->ci->fb->change("title",array("value"=>$d['item_res']->title));
			$this->ci->fb->change("name",array("value"=>$d['item_res']->name));
			$this->ci->fb->change("structure_parent_id",array("value"=>$d['item_res']->structure_parent_id2));
			$this->ci->fb->change("content",array("value"=>$d['item_res']->content));
			$this->ci->fb->change("php_file_path",array("value"=>$d['item_res']->php_file_path));
			$this->ci->fb->change("meta_title",array("value"=>$d['item_res']->meta_title));
			$this->ci->fb->change("meta_keywords",array("value"=>$d['item_res']->meta_keywords));
			$this->ci->fb->change("meta_description",array("value"=>$d['item_res']->meta_description));
			if($d['item_res']->show==1){
				$this->ci->fb->change("show",array("attr:checked"=>"true"));
			}
			if($d['item_res']->foot_and_head==1){
				$this->ci->fb->change("foot_and_head",array("attr:checked"=>"true"));
			}
			if($d['item_res']->disallow_bot_index==1){
				$this->ci->fb->change("disallow_bot_index",array("attr:checked"=>"true"));
			}

			foreach($this->ci->languages_res AS $language)
			{
				if($language->default==1 || $language->enabled!=1)continue;

				$this->ci->fb->change("title_".$language->code,array("value"=>$d['item_res']->{"l_title_".$language->code}));
				$this->ci->fb->change("content_".$language->code,array("value"=>$d['item_res']->{"l_content_".$language->code}));
				$this->ci->fb->change("php_file_path_".$language->code,array("value"=>$d['item_res']->{"l_php_file_path_".$language->code}));
				$this->ci->fb->change("meta_title_".$language->code,array("value"=>$d['item_res']->{"l_meta_title_".$language->code}));
				$this->ci->fb->change("meta_keywords_".$language->code,array("value"=>$d['item_res']->{"l_meta_keywords_".$language->code}));
				$this->ci->fb->change("meta_description_".$language->code,array("value"=>$d['item_res']->{"l_meta_description_".$language->code}));
			}
		}else{
			$this->ci->fb->change("foot_and_head",array("attr:checked"=>"true"));
			$this->ci->fb->change("show",array("attr:checked"=>"true"));
		}

		if($this->ci->fb->submit){
			$d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($d['global_errors'])==0){
				$name=$this->input->post("name");
				if($this->input->post("name")==""){
					$name=rewrite_alias($this->input->post("title"));
				}

				$structure_parent_id=intval($this->input->post("structure_parent_id"));
				$path=$this->getSectionURL($structure_parent_id);
				$url=$path.(substr($path,-1)!="/"?"/":"").$name.".html";
				if(substr($url,0,1)!="/")$url="/".$url;

				if($edit){
					// если страница главная, и мы переносим ее в новый раздел и там уже есть главная страница, делаем ее не главной
					if($d['item_res']->structure_parent_id2!=$structure_parent_id2){
						$pages_num=$this->db
						->from("url_structure")
						->where(array(
							"is_main_page"=>1,
							"parent_id"=>$structure_parent_id
						))
						->where("(`type` IN('static_page','module_action-one'))")
						->count_all_results();

						if($pages_num>0){
							$is_main_page=0;
						}
					}

					$update=array(
						"title"=>$this->input->post("title"),
						"name"=>$name,
						"content"=>$this->input->post("content"),
						"php_file_path"=>$this->input->post("php_file_path"),
						"meta_title"=>$this->input->post("meta_title"),
						"meta_keywords"=>$this->input->post("meta_keywords"),
						"meta_description"=>$this->input->post("meta_description"),
						"foot_and_head"=>$this->input->post("foot_and_head")==1?1:0,
						"show"=>$this->input->post("show")==1?1:0,
						"date_edit"=>mktime(),
						"disallow_bot_index"=>$this->input->post("disallow_bot_index")==1?1:0
					);

					foreach($this->ci->languages_res AS $language)
					{
						if($language->default==1 || $language->enabled!=1)continue;

						$update['l_title_'.$language->code]=$this->input->post("title_".$language->code);
						$update['l_content_'.$language->code]=$this->input->post("content_".$language->code);
						$update['l_php_file_path_'.$language->code]=$this->input->post("php_file_path_".$language->code);
						$update['l_meta_title_'.$language->code]=$this->input->post("meta_title_".$language->code);
						$update['l_meta_keywords_'.$language->code]=$this->input->post("meta_keywords_".$language->code);
						$update['l_meta_description_'.$language->code]=$this->input->post("meta_description_".$language->code);
					}

					$this->db
					->where("id",$_GET['id'])
					->update("pages",$update);

					$page_id=$_GET['id'];

					$this->db
					->where(array(
						"id"=>$d['item_res']->structure_parent_id
					))
					->update("url_structure",array(
						"parent_id"=>$structure_parent_id,
						"name"=>$name,
						"url"=>$url,
						"title"=>$this->input->post("title")/*,
						"is_main_page"=>isset($is_main_page)?$is_main_page:NULL*/
					));

					$structure_parent_id2=$d['item_res']->structure_parent_id;
				}else{
					$insert=array(
						"title"=>$this->input->post("title"),
						"name"=>$name,
						"content"=>$this->input->post("content"),
						"php_file_path"=>$this->input->post("php_file_path"),
						"meta_title"=>$this->input->post("meta_title"),
						"meta_keywords"=>$this->input->post("meta_keywords"),
						"meta_description"=>$this->input->post("meta_description"),
						"foot_and_head"=>$this->input->post("foot_and_head")==1?1:0,
						"show"=>$this->input->post("show")==1?1:0,
						"date_edit"=>mktime(),
						"disallow_bot_index"=>$this->input->post("disallow_bot_index")==1?1:0
					);

					foreach($this->ci->languages_res AS $language)
					{
						if($language->default==1 || $language->enabled!=1)continue;

						$insert['l_title_'.$language->code]=$this->input->post("title_".$language->code);
						$insert['l_content_'.$language->code]=$this->input->post("content_".$language->code);
						$insert['l_php_file_path_'.$language->code]=$this->input->post("php_file_path_".$language->code);
						$insert['l_meta_title_'.$language->code]=$this->input->post("meta_title_".$language->code);
						$insert['l_meta_keywords_'.$language->code]=$this->input->post("meta_keywords_".$language->code);
						$insert['l_meta_description_'.$language->code]=$this->input->post("meta_description_".$language->code);
					}

					$this->db->insert("pages",$insert);

					$page_id=$this->db->insert_id();

					$order=intval($this->db
					->where("parent_id",$parent_id)
					->count_all_results("url_structure"));
					$order++;

					$this->db->insert("url_structure",array(
						"user_id"=>$this->ci->session->userdata("user_id"),
						"parent_id"=>$structure_parent_id,
						"name"=>$name,
						"url"=>$url,
						"title"=>$this->input->post("title"),
						"type"=>"static_page",
						"extra_id"=>$page_id,
						"date_add"=>mktime(),
						"enabled"=>1,
						"order"=>$order
					));

					$structure_parent_id2=$this->db->insert_id();

					$this->rebuild_url_structure_orders();

					$this->db
					->where("id",$page_id)
					->update("pages",array(
						"structure_parent_id"=>$structure_parent_id2
					));

					$this->db
					->where(array(
						"key"=>$_POST['key'],
						"component_type"=>"module",
						"component_name"=>"admin",
						"extra_id"=>0
					))
					->update("uploads",array(
						"key"=>"",
						"extra_id"=>$page_id
					));
				}
				
				// делаем страницу главной если она единственная в разделе + если мы ее переносим в другой раздел, и там осталась одна страница, делаем ее тоже главной
				$this->stucture_check_main_page($structure_parent_id);
				if($edit && $d['item_res']->structure_parent_id2!=$structure_parent_id2){
					$this->stucture_check_main_page($d['item_res']->structure_parent_id2);
				}

				redirect($this->admin_url."?m=admin&a=pages");
			}
		}

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("admin/add_page",$d);
	}

	// проверяем есть ли в стуктуре страницы, и если их всего 1, делает ее главной
	private function stucture_check_main_page($structure_id=0)
	{
		// если это единственная страница в стуктуре, делаем ее главной
		$res=$this->db
		->from("url_structure")
		->where(array(
			"parent_id"=>$structure_id
		))
		->where("(`type` IN('static_page','module_action-one'))")
		->get()
		->result();

		if(sizeof($res)==1){
			$this->db
			->where(array(
				"id"=>current($res)->id
			))
			->update("url_structure",array(
				"is_main_page"=>1
			));
		}
	}

	public function add_module_section()
	{
		$this->add_module_page(true);
	}

	public function add_module_page($section=false)
	{
		$d=array();

		$d['section']=$section;

		$buttons=array();
		$buttons[]=array("add","Добавить<br />страницу","onclick"=>"add_page_sm(); return false;");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=admin&a=structure");

		$this->buttons("form",$buttons);

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true,
			"check"=>array(
				"min_length"=>0,
				"max_length"=>100
			)
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"URL",
			"name"=>"name",
			"parent"=>"greed",
			"primary"=>true,
			"check"=>array(
				"max_length"=>100
			)
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed",
			"parent"=>"block"
		));

		$this->ci->fb->add("block",array(
			"name"=>"block",
			"parent"=>"render",
			"method"=>"post"
		));

		// получаем уже добавленые разделы компонентов, чтоб небыло возможность их добавить еще раз
		$d['added_module_action_list_ids']=array();
		$d['added_module_action_list']=$this->db
		->get_where("url_structure",array(
			"type"=>"module_action-list"
		))
		->result();
		foreach($d['added_module_action_list'] AS $r)
		{
			if(!isset($d['added_module_action_list_ids'][$r->module]))$d['added_module_action_list_ids'][$r->module]=array();
			$d['added_module_action_list_ids'][$r->module][$r->id]=$r->action;
		}

		foreach($this->ci->modules AS $r)
		{
			$r->info->db=&$this->db;

			if(is_null($r->info))continue;

			if($section){
				if(!method_exists($r->info,"front_structure_sections"))continue;
				$pages=$r->info->front_structure_sections();
			}else{
				continue;
			}

			foreach($pages AS $r2)
			{
				if(empty($r2['options_method']) || !method_exists($r->info,$r2['options_method']))continue;

				$r->info->$r2['options_method']($this->ci->fb);
			}
		}

		if($this->ci->fb->submit){
			$extra_name="";
			$extra_id=0;

			if($this->input->post("extra_name")!==false){
				$extra_name=$this->input->post("extra_name");
			}
			if($this->input->post("extra_id")!==false){
				$extra_id=$this->input->post("extra_id");
			}

			$d['global_errors']=$this->ci->fb->errors_list();

			if($this->input->post("page")===false)$d['global_errors'][]="Выберите страницу из модуля!";
			
			if(sizeof($d['global_errors'])==0){
				$name=$this->input->post("name");
				if($this->input->post("name")==""){
					$name=rewrite_alias($this->input->post("title"));
				}

				$parent_id=$this->input->get("parent_id");
				$path=$this->getSectionURL($parent_id);
				$url=$path.(substr($path,-1)!="/"?"/":"").$name.($section?"/":".html");
				if(substr($url,0,1)!="/")$url="/".$url;

				$module_name=current(array_keys($this->input->post("page")));
				$method_name=current($this->input->post("page"));
				
				$type="module_action-one";
				$options="";
				if($section){
					$options="";
					$options=$_POST;
					unset($options['page'],$options['options_method'],$options['title'],$options['name'],$options['sm']);
					$options=json_encode($options);

					$type="module_action-list";
				}

				$this->db
				->insert("url_structure",array(
					"parent_id"=>$parent_id,
					"name"=>$name,
					"url"=>$url,
					"title"=>$this->input->post("title"),
					"type"=>$type,
					"module"=>$module_name,
					"action"=>$method_name,
					"extra_name"=>$extra_name,
					"extra_id"=>$extra_id,
					"options"=>$options,
					"date_add"=>mktime(),
					"enabled"=>1,
				));

				redirect($this->admin_url."?m=admin&a=structure");
			}
		}

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("admin/add_module_page",$d);
	}

	public function menu()
	{
		$d=array();

		$buttons=array();
		$buttons[]=array("add","Добавить<br />меню",$this->admin_url."?m=admin&a=add_menu");
		$buttons[]=array("add","Добавить<br />пункт",$this->admin_url."?m=admin&a=add_menu_item");

		$this->buttons("main",$buttons);

		$menu_res=$this->db
		->get_where("categoryes",array(
			"type"=>"menu"
		))
		->result();
			
		$items_res=$this->db
		->order_by("order")
		->get_where("categoryes",array(
			"type IN('menu','menu-item')"=>NULL
		))
		->result();

		$items_res_ids=array();
		$items_res_ids_num=array();
		foreach($items_res AS $r2)
		{
			$items_res_ids[$r2->id]=$r2;
		}

		foreach($items_res_ids AS $id=>$r)
		{
			$items_res_ids_num[$id]=0;
			foreach($items_res AS $r2)
			{
				if($r2->parent_id==$id)$items_res_ids_num[$id]++;
			}
		}

		foreach($menu_res AS $r)
		{
			$childs_menu=$this->menu_options_list($r->id);

			$rows=array();
			foreach($childs_menu AS $id=>$title)
			{
				$r2=$items_res_ids[$id];
				
				$rows[]=array(
					'<a href="'.$this->admin_url."?m=admin&a=edit_menu_item&id=".$r2->id.'">'.$title.'</a>',
					"order"=>array($this->admin_url."?m=admin&a=order_menu_item&id=".$r2->id."&order=",$r2->order,$items_res_ids_num[$r2->parent_id]),
					"enabled"=>array($this->admin_url."?m=admin&a=enabled_menu_item&id=".$r2->id."&enable=",$r2->show),
					"buttons"=>array(
						array("pencil",$this->admin_url."?m=admin&a=edit_menu_item&id=".$r2->id),
						array("cross",$this->admin_url."?m=admin&a=rm_menu_item&id=".$r2->id)
					)
				);
			}

			$this->ci->fb->add("table",array(
				"title"=>"Меню: <a href=\"".$this->admin_url."?m=admin&a=edit_menu&id=".$r->id."\">".$r->title."</a> <a href=\"".$this->admin_url."?m=admin&a=enabled_menu&id=".$r->id."&enable=".($r->show==1?0:1)."\"><img src=\"/templates/default/admin/assets/icons/".($r->show==1?"lightbulb":"lightbulb_off").".png\" /></a> <a href=\"".$this->admin_url."?m=admin&a=edit_menu&id=".$r->id."\"><img src=\"/templates/default/admin/assets/icons/pencil.png\" /></a> <a href=\"#\" onclick=\"if(confirm('Вы уверены?')){ document.location.href='".$this->admin_url."?m=admin&a=rm_menu_item&id=".$r->id."'; } return false;\"><img src=\"/templates/default/admin/assets/icons/cross.png\" /></a>",
				"parent"=>"greed",
				"head"=>array(
					"Название пункта меню",
					"Порядок",
					"Включен"
				),
				"rows"=>$rows
			));
		}

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

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("admin/menu",$d);
	}

	public function enabled_menu_item()
	{
		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("categoryes",array(
			"show"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=admin&a=menu");
	}

	public function enabled_menu()
	{
		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("categoryes",array(
			"show"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=admin&a=menu");
	}

	public function order_menu_item()
	{
		$id=(int)$this->input->get("id");
		$order=$this->input->get("order");

		$res=$this->db
		->get_where("categoryes",array(
			"id"=>$id
		))
		->row();
		
		$this->db
		->where(array(
			"type"=>"menu-item",
			"order"=>$order=="up"?$res->order-1:$res->order+1,
			"parent_id"=>$res->parent_id
		))
		->update("categoryes",array(
			"order"=>$res->order
		));
		
		$this->db
		->where(array(
			"type"=>"menu-item",
			"id"=>$id,
			"parent_id"=>$res->parent_id
		))
		->update("categoryes",array(
			"order"=>$order=="up"?$res->order-1:$res->order+1
		));

		$this->rebuild_menu_order($res->parent_id);

		redirect($this->admin_url."?m=admin&a=menu");
	}

	function rebuild_menu_order($parent_id)
	{
		$res=$this->db
		->order_by("order")
		->get_where("categoryes",array(
			"parent_id"=>$parent_id
		))
		->result();

		$i=1;
		foreach($res AS $r)
		{
			$this->db
			->where(array(
				"id"=>$r->id
			))
			->update("categoryes",array(
				"order"=>$i
			));
			
			$this->rebuild_menu_order($r->id);
			$i++;
		}
	}

	private function menu_options_list($parent_id=0,$level=0,&$data=array())
	{
		$res=$this->db
		->select("id, title")
		->order_by("order")
		->get_where("categoryes",array(
			"type IN ('menu','menu-item')"=>NULL,
			"parent_id"=>$parent_id
		))
		->result();

		$level++;

		foreach($res AS $r)
		{
			$data[$r->id]=str_repeat("--",$level-1)." ".$r->title;
			$data[$r->id]=trim($data[$r->id]);
			$this->menu_options_list($r->id,$level,$data);
		}

		return $data;
	}

	public function edit_menu_item()
	{
		$_GET['id']=(int)$_GET['id'];

		$this->add_menu_item(true);
	}

	public function add_menu_item($edit=false)
	{
		$this->ci->load->check_page_access("site_menu_accepted","admin","module");

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=admin&a=menu");

		$this->buttons("form",$buttons);

		if($edit){
			$d['item_res']=$this->db
			->select("categoryes.*")
			->get_where("categoryes",array("categoryes.id"=>$_GET['id']))
			->row();
		}

		$options=$this->menu_options_list();
		$this->ci->fb->add("list:select",array(
			"label"=>"Родитель",
			"name"=>"parent_id",
			"parent"=>"greed",
			"primary"=>true,
			"options"=>$options
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true,
			"check"=>array(
				"min_length"=>0,
				"max_length"=>100
			),
			"translate"=>true
		));

		$options=$this->structure_options_list(0,false);
		$options=array("0"=>"КОРЕНЬ САЙТА")+$options;

		$this->ci->fb->add("list:select",array(
			"label"=>"Ссылка на страницу в разделе",
			"name"=>"structure_id",
			"parent"=>"greed",
			"options"=>$options
		));

		$this->ci->fb->add("upload",array(
			"label"=>"Изображение",
			"component_type"=>"module",
			"component_name"=>"admin",
			"extra_type"=>"menu_item_id",
			"extra_id"=>$edit?$_GET['id']:0,
			"key"=>$edit?"":(!empty($_POST['key'])?$_POST['key']:""),
			"name"=>"menu_item_main_picture",
			"parent"=>"greed",
			"dynamic"=>true,
			"upload_path"=>"./uploads/menu/",
			// "proc_config_var_name"=>"config[mod_admin_menu_item_main_picture]"
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"или URL",
			"name"=>"url",
			"parent"=>"greed",
			"primary"=>true,
			"options"=>$options
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
			$d['item_res']->options=json_decode($d['item_res']->options);
			foreach($d['item_res']->options AS $k=>$v)
			{
				$_POST[$k]=$v;
			}

			// если мы зашли на страницу редактирования уже добавленого виджета, заносим данные из БД в поля
			$this->ci->fb->change("parent_id",array("value"=>$d['item_res']->parent_id));
			$this->ci->fb->change("title",array("value"=>$d['item_res']->title));
			$this->ci->fb->change("structure_id",array("value"=>$d['item_res']->options->structure_id));
			$this->ci->fb->change("url",array("value"=>$d['item_res']->options->url));
			if($d['item_res']->show==1){
				$this->ci->fb->change("show",array("attr:checked"=>"true"));
			}

			foreach($this->ci->languages_res AS $language)
			{
				if($language->default==1 || $language->enabled!=1)continue;

				$this->ci->fb->change("title_".$language->code,array("value"=>$d['item_res']->{"l_title_".$language->code}));
			}
		}

		if($this->ci->fb->submit){
			$d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($d['global_errors'])==0){
				$order=$pages_num=$this->db
				->from("categoryes")
				->where(array(
					"parent_id"=>$this->input->post("parent_id")
				))
				->count_all_results();

				$order++;

				$options=array(
					"structure_id"=>$this->input->post("structure_id"),
					"url"=>$this->input->post("url")
				);
				if($edit){
					$update=array(
						"parent_id"=>$this->input->post("parent_id"),
						"title"=>$this->input->post("title"),
						"options"=>json_encode($options),
						"show"=>1,
						"extra_name"=>"structure_id",
						"extra_id"=>empty($options['url'])?$this->input->post("structure_id"):0
					);

					foreach($this->ci->languages_res AS $language)
					{
						if($language->default==1 || $language->enabled!=1)continue;

						$update['l_title_'.$language->code]=$this->input->post("title_".$language->code);
					}

					$this->db
					->where(array(
						"id"=>$_GET['id']
					))
					->update("categoryes",$update);
				}else{
					$insert=array(
						"parent_id"=>$this->input->post("parent_id"),
						"type"=>"menu-item",
						"title"=>$this->input->post("title"),
						"options"=>json_encode($options),
						"date_add"=>mktime(),
						"show"=>1,
						"order"=>$order,
						"extra_name"=>"structure_id",
						"extra_id"=>empty($options['url'])?$this->input->post("structure_id"):0
					);

					foreach($this->ci->languages_res AS $language)
					{
						if($language->default==1 || $language->enabled!=1)continue;

						$insert['l_title_'.$language->code]=$this->input->post("title_".$language->code);
					}

					$this->db
					->insert("categoryes",$insert);

					$this->db
					->where(array(
						"key"=>$_POST['key'],
						"component_type"=>"module",
						"component_name"=>"admin",
						"extra_id"=>0
					))
					->update("uploads",array(
						"key"=>"",
						"extra_id"=>$id
					));
				}

				$this->rebuild_menu_order($this->input->post("parent_id"));

				redirect($this->admin_url."?m=admin&a=menu");
			}
		}

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("admin/add_menu",$d);
	}

	public function rm_menu_item()
	{
		$id=$this->input->get("id");

		$item_res=$this->db
		->get_where("categoryes",array(
			"id"=>$id
		))
		->row();

		$ids=array_keys($this->menu_options_list($id));
		$ids[]=$id;

		$this->db
		->where(array(
			"id IN (".implode(",",$ids).")"=>NULL
		))
		->delete("categoryes");

		$this->rebuild_menu_order($item_res->parent_id);

		redirect($this->admin_url."?m=admin&a=menu");
	}

	public function edit_menu()
	{
		$_GET['id']=(int)$_GET['id'];

		$this->add_menu(true);
	}

	public function add_menu($edit=false)
	{
		$this->ci->load->check_page_access("site_menu_accepted","admin","module");

		$d=array();

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=admin&a=menu");

		$this->buttons("form",$buttons);

		if($edit){
			$d['item_res']=$this->db
			->select("categoryes.*")
			->get_where("categoryes",array("categoryes.id"=>$_GET['id']))
			->row();
		}

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true,
			"options"=>$options
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
			$this->ci->fb->change("title",array("value"=>$d['item_res']->title));
		}

		if($this->ci->fb->submit){
			$d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($d['global_errors'])==0){
				if($edit){
					$this->db
					->where(array(
						"id"=>$_GET['id']
					))
					->update("categoryes",array(
						"title"=>$this->input->post("title")
					));
				}else{
					$this->db
					->insert("categoryes",array(
						"type"=>"menu",
						"title"=>$this->input->post("title"),
						"date_add"=>mktime(),
						"show"=>1
					));
				}

				redirect($this->admin_url."?m=admin&a=menu");
			}
		}

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("admin/add_menu",$d);
	}

	function thumb()
	{
		$f=$this->input->get("f");
		$f=str_replace("..","",$f);
		$f=preg_replace("#\.php[0-9]*$#is","",$f);

		if(!file_exists("./".$f)){
			die("File no exists!");
		}

		$config['image_library']='gd2';
		$config['source_image']=$f;
		$config['create_thumb']=TRUE;
		$config['maintain_ratio']=TRUE;

		switch($_GET['s'])
		{
			case'100':
				$config['width']=100;
				$config['height']=100;
			break;
			case'128':
				$config['width']=128;
				$config['height']=128;
			break;
			default:
				$config['width']=100;
				$config['height']=100;
			break;
		}
		$config['dynamic_output']=true;
		$this->load->library('image_lib', $config); 
		$this->ci->image_lib->resize();
	}

	public function export_module()
	{
		$id=(int)$this->input->get("id");

		$res=$this->db
		->get_where("components",array(
			"id"=>$id
		))
		->row();

		$r=$this->download_component($res->name);

		if(!empty($r['error'])){
			print $r['error'];
			exit;
		}else{
			header("Location: ".str_replace("./","/",$r['path']));
		}
	}

	function remove_component()
	{
		$id=(int)$this->input->get("id");

		$res=$this->db
		->get_where("components",array(
			"id"=>$id
		))
		->row();

		switch($res->type)
		{
			case'module':
				$component_folder_path="./modules";

				directory_remove("./templates/default/".$res->name."/");
				directory_remove("./templates/default/admin/".$res->name."/");
			break;
			case'widget':
				$component_folder_path="./widgets";
			break;
			case'template':
				$component_folder_path="./templates";
			break;
			default:
				die("Расширение не опознано");
			break;
		}

		directory_remove($component_folder_path."/".$res->name);

		$this->db
		->where(array(
			"id"=>$id
		))
		->delete("components");

		switch($res->type)
		{
			case'module':
				redirect($this->admin_url."?m=admin&a=components&t=modules");
			break;
			case'widget':
				redirect($this->admin_url."?m=admin&a=components&t=widgets");
			break;
			case'template':
				redirect($this->admin_url."?m=admin&a=components&t=templates");
			break;
		}
	}

	public function enabled_template()
	{
		$this->db
		->where(array("id"=>(int)$this->input->get("id")))
		->update("components",array(
			"enabled"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=admin&a=components&t=templates");
	}

	public function default_template()
	{
		$this->db
		->where(array(
			"type"=>"template",
			"default"=>1
		))
		->update("components",array(
			"default"=>0
		));

		$this->db
		->where(array("id"=>(int)$this->input->get("id")))
		->update("components",array(
			"default"=>1
		));

		redirect($this->admin_url."?m=admin&a=components&t=templates");
	}

	public function export_template()
	{
		$id=(int)$this->input->get("id");

		$res=$this->db
		->get_where("components",array(
			"id"=>$id
		))
		->row();

		$r=$this->download_component($res->name);

		if(!empty($r['error'])){
			print $r['error'];
			exit;
		}else{
			header("Location: ".str_replace("./","/",$r['path']));
		}
	}

	function widgets()
	{
		$this->buttons("main",array(
			array("add","Добавить<br />виджет",$this->admin_url."?m=admin&a=select_widget")
		));

		if($this->input->get("widgets_options_json")!==false){
			$widgets_options_json=json_decode($this->input->get("widgets_options_json"));
		}

		$d['widgets_res']=$this->db
		->select("widgets.*")
		->select("components.enabled AS component_enabled")
		->select("components.name")
		->join("components","components.id = widgets.widget_id && components.name NOT LIKE 'admin_%'")
		->get_where("widgets",array())
		->result();

		foreach($d['widgets_res'] AS $r)
		{

			$cols=array();
			$cols[]="<a href=\"".$this->admin_url."?m=admin&a=edit_widget&id=".$r->id."\">".$r->title."</a>";
			$cols[]=$r->position;
			if($this->input->get("iframe_display")===false){
				$cols['enabled']=array($this->admin_url."?m=admin&a=enabled_widget&id=".$r->id."&enable=",$r->enabled,"warning"=>$r->component_enabled!=1?"этот виджет отключен в Система -> Расширения -> Виджеты, и не будет отображаться на сайте!":"");
			}else{
				$s=array();
				if(!isset($widgets_options_json->{$r->id}->enabled)){
					$widgets_options_json->{$r->id}->enabled="-1";
				}
				if($widgets_options_json->{$r->id}->enabled==1){
					$s[1]=' selected="selected"';
				}elseif($widgets_options_json->{$r->id}->enabled==0){
					$s[0]=' selected="selected"';
				}else{
					$s[-1]=' selected="selected"';
				}
				$cols[]='<select onchange="updateWidget'.$r->id.'Options();" name="widget_show['.$r->id.']" id="widget_show_'.$r->id.'">
<option value="-1"'.$s[-1].'>Наследовать свойства родителя</option>
<option value="1"'.$s[1].'>Да</option>
<option value="0"'.$s[0].'>Нет</option>
</select>
<script>
function updateWidget'.$r->id.'Options()
{
top.widgetsOptions['.$r->id.']={
\'enabled\':$("#widget_show_'.$r->id.'").val()
};

top.buildWidgetsOptions();
}
$(document).ready(function(){
updateWidget'.$r->id.'Options();
});
</script>';
			}
			
			if($this->input->get("iframe_display")===false){
				$cols['buttons']=array(
					array("pencil",$this->admin_url."?m=admin&a=edit_widget&id=".$r->id),
					array("cross",$this->admin_url."?m=admin&a=rm_widget&id=".$r->id)
				);
			}
			
			$rows[]=$cols;
		}

		$this->ci->fb->add("table",array(
			"parent"=>$this->input->get("iframe_display")===false?"block":"form",
			"head"=>$head,
			"rows"=>$rows
		));

		if($this->input->get("iframe_display")===false){
			$this->ci->fb->add("block",array(
				"name"=>"block",
				"parent"=>"form",
				"method"=>"post"
			));
		}

		$this->ci->fb->add("form",array(
			"name"=>"form",
			"parent"=>"render",
			"method"=>"post"
		));

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("admin/widgets",$d);
	}

	function rm_widget_component()
	{
		$this->ci->load->check_page_access("components_accepted","admin","module");

		$id=intval($this->input->get("id"));

		$d['item_res']=$this->db
		->get_where("components",array(
			"id"=>$id
		))
		->row();

		$this->ci->uploads->remove(array(
			"component_type"=>"widget",
			"component_name"=>$d['item_res']->name
		));

		if(!empty($d['item_res']->name) && file_exists("widgets/".$d['item_res']->name."/".$d['item_res']->name.".info.php")){
			include_once("widgets/".$d['item_res']->name."/".$d['item_res']->name.".info.php");
			$name=$d['item_res']->name."WidgetInfo";
			$d['widget_info']=new $name;

			if(method_exists($d['widget_info'],"on_widget_before_remove")){
				$d['widget_info']->on_widget_before_remove($d['item_res']);
			}

			directory_remove("./widgets/".$d['item_res']->name);
		}else{
			die("No widget found!<br />"."widgets/".$d['item_res']->name."/".$d['item_res']->name.".info.php");
		}

		$this->db
		->where("widget_id",$id)
		->delete("widgets");

		$this->db
		->where("id",$id)
		->delete("components");

		redirect($this->admin_url."?m=admin&a=components&t=widgets");
	}

	public function enabled_widget_component()
	{
		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("components",array(
			"enabled"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=admin&a=components&t=widgets");
	}
        
        public function pages()
	{
		$this->buttons("main",array(
			array("add","Добавить<br />страницу",$this->admin_url."?m=admin&a=add_page")
		));

		

		$d['pages_res']=$this->db
		->select("pages.*")
                ->order_by('id', 'desc')
		->get_where("pages",array())
		->result();

		foreach($d['pages_res'] AS $r)
		{

			$cols=array();
			$cols[]="<a href=\"".$this->admin_url."?m=admin&a=edit_page&id=".$r->id."\">".$r->title."</a>";
//                        if($this->input->get("iframe_display")===false){
//				$cols['enabled']=array($this->admin_url."?m=admin&a=show_page&id=".$r->id."&show=",$r->show,"warning"=>"");
//			}
			
			if($this->input->get("iframe_display")===false){
				$cols['buttons']=array(
					array("pencil",$this->admin_url."?m=admin&a=edit_page&id=".$r->id),
					array("cross",$this->admin_url."?m=admin&a=rm_page&id=".$r->id)
				);
			}
			
			$rows[]=$cols;
		}

		$this->ci->fb->add("table",array(
			"parent"=>$this->input->get("iframe_display")===false?"block":"form",
			"head"=>$head,
			"rows"=>$rows
		));

		if($this->input->get("iframe_display")===false){
			$this->ci->fb->add("block",array(
				"name"=>"block",
				"parent"=>"form",
				"method"=>"post"
			));
		}

		$this->ci->fb->add("form",array(
			"name"=>"form",
			"parent"=>"render",
			"method"=>"post"
		));

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("admin/pages",$d);
	}
        
        public function rm_page($id=NULL)
	{
		if(isset($id)){
                    $_GET['id']=$id;
		}
		$_GET['id']=(int)$_GET['id'];

                // удалили страницу из `pages`
                $this->db
                ->where(array(
                        "id"=>$_GET['id']
                ))
                ->delete("pages");
                // удалили запись о странице из `url_structure`
		$this->db
		->where(array(
			"url_structure.extra_id"=>$_GET['id']
		))
		->delete("url_structure");
		
		redirect($this->admin_url."?m=admin&a=pages");
	}
        
        public function show_page()
	{
		$this->ci->load->check_page_access("site_widgets_accepted","admin","module");

		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("pages",array(
			"show"=>$this->input->get("show")==1?1:0
		));

		redirect($this->admin_url."?m=admin&a=pages");
	}
        
        // удаление файлов из `uploads`
        public function remove_upload() {
            $id = $this->input->post('id');
            if(empty($id)){
                echo 'fail';
                return;
            }
            // проверяем если это товар с order=1 – и при этом ещё есть фото товара – присваиваем order=1 тому, у которого наименьший order
            // информация о текущем элементе
            $id_info = $this->db->select('name, extra_id, order')->get_where('uploads', array('id' => $id))->row();
            if($id_info->order == 1){
                // запрашиваем другое фото товара с наименьшим order-ом
                $min_order_element = $this->db->select('id')
                    ->where(array('name' => $id_info->name, 'extra_id' => $id_info->extra_id, 'id !=' => $id))
                    ->order_by('order', 'asc')
                    ->get('uploads', 1)
                    ->row();
                if(!empty($min_order_element)){
                    // обновляем order у соседнего элемента - теперь он под номером 1
                    $this->db->update('uploads', array('order' => 1), array('id' => $min_order_element->id)); 
                }
            }
            
            // удалили элемент
            $this->db->delete('uploads', array('id' => $id)); 
            // перестроили order у оставшихся
            $res = $this->db->select('id')
                    ->where(array('name' => $id_info->name, 'extra_id' => $id_info->extra_id))
                    ->order_by('order', 'asc')
                    ->get('uploads')
                    ->result_array();
            if(!empty($res)){
                foreach ($res as $key => $row){
                    $this->db->update( 'uploads', array('order' => ($key+1)), array('id' => $row['id']) );
                }
            }
            
            echo 'ok';
            return;
        }
        
        // сортировка файлов в `uploads`
        public function move_upload() {
            $id = $this->input->post('id');
            $direct = $this->input->post('direct');
                        
            //$direct = 'down';
            if(empty($id)){
                echo 'fail';
                return;
            }
//            $res = $this->db->query("select `id`, `file_original_name`, `order` from uploads where extra_id = 3292 order by `order`")->result_array();
//            var_dump($res);
//            $id = $res[0]['id'];
//            var_dump('$id: ' . $id);
            
            // информация о текущем элементе
            $id_info = $this->db->select('name, extra_id, order')->get_where('uploads', array('id' => $id))->row();
            // запрашиваем элемент со значением `order` меньше, чем у текущего элемента
            $min_order_element = $this->db->select('id, order')
                    ->where(array('name' => $id_info->name, 'extra_id' => $id_info->extra_id, 'order <' => $id_info->order))
                    ->order_by('order', 'desc')
                    ->get('uploads', 1)
                    ->row();
//            var_dump('MIN: ' . $this->db->last_query());
//            var_dump('$min_order_element: ', $min_order_element);
            // если такого элемента нет – и при этом стоит задача уменьшить `order` у текущего элемента = уменьшать уже некуда
            if(empty($min_order_element) && $direct == 'up'){
                echo 'fail - нет order меньше, чем ' . $id_info->order . ' : у текущего элемента order = ' . $id_info->order;
                return; // нет элементов с меньшим значением order
            }
            // запрашиваем элемент со значением `order` больше, чем у текущего элемента
            $max_order_element = $this->db->select('id, order')
                    ->where(array('name' => $id_info->name, 'extra_id' => $id_info->extra_id, 'order >' => $id_info->order))
                    ->order_by('order', 'asc')
                    ->get('uploads', 1)
                    ->row();
//            var_dump('MAX: ' . $this->db->last_query());
//            var_dump('$max_order_element: ', $max_order_element);
            // если такого элемента нет – и при этом стоит задача увеличить `order` у текущего элемента = увеличивать уже некуда
            if(empty($max_order_element) && $direct == 'down'){
                echo 'fail - нет order больше, чем ' . $id_info->order . ' : у текущего элемента order = ' . $id_info->order;
                return; // нет элементов с бОльшим значением order
            }
            // элемент можно перемещать 
            // значение, которое будет присвоено текущему елементу
            $new_order = ($direct == 'up') ? $min_order_element->order : $max_order_element->order;            
//            var_dump('$new_order: ' . $new_order);
            // значение, которое будет присвоено ближайшему соседнему элементу в направлении перемещения
            $new_order_to_neighbor = ($direct == 'up') ? array('id' => $min_order_element->id) : array('id' => $max_order_element->id);            
//            var_dump($new_order_to_neighbor); 
            // обновляем order у соседнего элемента
            $this->db->update('uploads', array('order' => $id_info->order), $new_order_to_neighbor);            
//            var_dump('Update neighbor: ' . $this->db->last_query());
            // обновляем order у текущего элемента
            $this->db->update('uploads', array('order' => $new_order), array('id' => $id)); 
//            var_dump('Update this: ' . $this->db->last_query());
            
            echo 'ok';
            return;
        }
}
?>