<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
Главный контроллер, разбирает URL и подключает нужный модуль
*/
class Core extends CI_Controller {
	public function admin()
	{
		$ci=&get_instance();

		$ci->languages_res=$ci->db
		->get("languages")
		->result();

		$this->load->helper('url');

		if(empty($_GET['m']))$_GET['m']="admin";
		if(empty($_GET['a']))$_GET['a']="index";

		$this->admin_panel=true;

		// базовая ссылка на админ. панель
		$this->admin_url="/admin/";

		if($this->ion_auth->logged_in()===false 
			&& ($_GET['a']!="login")){
			redirect($this->admin_url."?m=user&a=login");
			exit;
		}

		if($this->input->post("admin_login_sm")!==false){
			$remember=0;
			if ($this->ion_auth->login($this->input->post("username"),$this->input->post("password"),$remember)){
				// добавляем запись в историю входов
				$this->db
				->insert("log",array(
					"user_id"=>$this->session->userdata("user_id"),
					"type"=>"admin-login",
					"title"=>"Пользователь вошел в админ. центр",
					"description"=>"IP: ".$_SERVER['REMOTE_ADDR']."<br />Браузер: ".$_SERVER['HTTP_USER_AGENT'],
					"date_add"=>mktime()
				));

				redirect($this->admin_url);
				exit;
			}else{
				$this->login_error=$this->ion_auth->errors();
			}
		}

		if($this->ion_auth->logged_in()){
			$this->user=$this->ion_auth->user()->row();

			// проверяем есть ли у группы этого пользователя доступ к админ. панели
			$this->current_user_group_res=$this->db
			->where(array(
				"users_groups.user_id"=>$this->user->id
			))
			->join("groups","groups.id = users_groups.group_id && groups.admin_panel_access = 1")
			->get_where("users_groups")
			->row();

			if(is_string($this->current_user_group_res->access_rules))$this->current_user_group_res->access_rules=json_decode($this->current_user_group_res->access_rules);

			if($this->current_user_group_res->admin_panel_access!=1){
				$this->no_access_to_admin_panel=true;
			}
		}

		// подключаем модуль и вызываем action
		if(!file_exists("./modules/".$_GET['m']."/".$_GET['m'].".admin.php")){
			die("No admin module file exists!");
		}
		include_once("./modules/".$_GET['m']."/".$_GET['m'].".admin.php");
		$module_name=$_GET['m']."Module";
		$ci->module=new $module_name;

		$ci->module->admin_url=$this->admin_url;

		// получаем список включенных модулей
		$this->modules=$this->db
		->select("name")
		->get_where("components",array(
			"components.enabled"=>1
		))
		->result();

		// получаем информацию по включеным модулям, в том числе меню для админки
		foreach($this->modules AS $r)
		{
			if(!file_exists("./modules/".$r->name."/".$r->name.".info.php"))continue;

			include_once("./modules/".$r->name."/".$r->name.".info.php");
			$module_info_name=$r->name."ModuleInfo";
			$r->info=new $module_info_name;
		}

		if(method_exists($ci->module,$_GET['a'])){
			$ci->module->{$_GET['a']}();
		}else{
			die("Method <strong>class ".$module_name."->".$_GET['a']."();</strong> not exists!<br />File: "."./modules/".$_GET['m']."/".$_GET['m'].".admin.php");
		}
	}

	public function index()
	{
		$ci=&get_instance();
                
                $ci->output->enable_profiler(TRUE);

		$ci->languages_res=$ci->db
		->get("languages")
		->result();

		if($this->config->config['site_disabled']==1){
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Сайт отключен!</title>
</head>
<body>
	<center>
		<h1>Сайт отключен!</h1>
		<p><?php print $this->config->config['site_disabled_text']; ?></p>
	</center>
</body>
</html>
<?php
			exit;
		}

		if($this->input->get("m")===false || $this->input->get("a")===false){
			parse_str($_SERVER['REDIRECT_QUERY_STRING'],$u);
			$_GET['m']=$u['m'];
			$_GET['a']=$u['a'];
		}

		$change_lang=$ci->input->get("cl");
		if($change_lang!==false && is_dir("application/language/".$change_lang)){
			$ci->session->set_userdata("language",$change_lang);
		}

		if($ci->session->userdata("language")!==false){
			$ci->config->set_item("language",$ci->session->userdata("language"));
			$ci->lang->load("main");
		}else{
			$ci->config->set_item("language","russian");
			$ci->lang->load("main");
		}

		if($this->input->get("m")===false || $this->input->get("a")===false){
			$uri=$_SERVER['REQUEST_URI'];

			// в УРЛ передаются какие-то переменные! обрезаем, и потом их передадим в $_GET
			if(preg_match("#.*/(.*\.(php)\?.*)$#is",$uri,$matches)){
				$urlVars=$matches[1];
				$uri=str_replace($urlVars,"",$uri);
			}elseif(preg_match("#.*/.*\.html(\?.*)$#is",$uri,$matches)){
				$urlVars=$matches[1];
				$uri=str_replace($urlVars,"",$uri);
			}

			$clearUrl=substr($uri,-1)!="/"?$uri."/":$uri;
			$clearUrl=preg_replace("#\.html/$#is",".html",$clearUrl);
			$clearUrl=preg_replace("#\?.*$#is","",$clearUrl);

			if($clearUrl=="" || $clearUrl=="/" || $clearUrl=="/index.php"){
				// это главная страница сайта
				$this->url_structure_res=$this->db
				->select("*")
				->get_where("url_structure",array(
					"parent_id"=>0,
					"is_main_page"=>1
				))->row();
			}else{
                $this->url_structure_res=$this->db
				->select("*")
				->get_where("url_structure",array(
					"url"=>$clearUrl
				))->row();//var_dump($this->db->last_query());// сhildren-сlothing-shoes - чертова буква с (русская!!!)
			}

			if(isset($this->url_structure_res->id)){
				// это не страница, а раздел! ищем в нем главную страницу
				if($this->url_structure_res->type=="dir"){
					$this->url_structure_res=$this->db
					->select("*")
					->get_where("url_structure",array(
						"parent_id"=>$this->url_structure_res->id,
						"is_main_page"=>1
					))->row();
				}
			}elseif(!isset($this->url_structure_res->id)){
                // возможно этой страницы не существует в структуре, так как она находится в разделе компонента...
				$sectionUrl=preg_replace("#(.*)\?.*#is","\\1",$_SERVER['REQUEST_URI']);
				if(preg_match("#\.html$#is",$sectionUrl)){
					$sectionUrl=preg_replace("#(.*)/[^/]*#is","\\1/",$sectionUrl);
				}else{
					$sectionUrl=preg_replace("#(.*)/[^/]*/[^/]*#is","\\1/",$sectionUrl);
				}

				$clearUrl=substr($sectionUrl,-1)!="/"?$sectionUrl."/":$sectionUrl;
				
				$this->url_structure_res=$this->db
				->select("*")
				->get_where("url_structure",array(
					"url"=>$clearUrl
				))->row();
			}

			if(!isset($this->url_structure_res->id)){
				show_404('core/index');
			}

			if($this->url_structure_res->type=="static_page"){
				$this->url_structure_res->module="static_page";
				$this->url_structure_res->action="view_page";
			}

			$module_name=$this->url_structure_res->module;
			$action_name=$this->url_structure_res->action;
		}else{
			$module_name=$this->input->get("m");
			$action_name=$this->input->get("a");
		}

		$module_name=str_replace("..","",$module_name);
		$action_name=str_replace("..","",$action_name);//var_dump($module_name, $action_name);

		// подключаем модуль и вызываем action
		if(!file_exists("./modules/".$module_name."/".$module_name.".php")){
			die("Module file not found: "."./modules/".$module_name."/".$module_name.".php");
		}
		
		include_once("./modules/".$module_name."/".$module_name.".php");
		$module_class_name=$module_name."Module";
		$ci->module=new $module_class_name;
		$ci->module->url_structure_res=$this->url_structure_res;

		if(method_exists($ci->module,$action_name)){
			$ci->module->{$action_name}();
		}else{
			die("Module method not found: "."./modules/".$module_name."/".$module_name.".php : class ".$module_class_name."->".$action_name."()");
		}
	}
        
        public function user_register() {
            $username = 'andkorol';
            $password = 'andkorol1';
            $email = 'andkorol.reg@gmail.com';
            $additional_data = array(
                'first_name' => 'Andrey',
                'last_name' => 'Korol',
                'adress' => 'Улица дом 123',
                'city' => 'Город',
                'wishlist' => '42',
                'vkontakte' => '',
                'facebook' => '',
                
            );								
            $group = array('1'); // Sets user to admin. No need for array('1', '2') as user is always set to member by default

            if($this->ion_auth->register($username, $password, $email, $additional_data, $group)){
                echo 'Registered!';
            }
            else{
                echo 'Not registered!<br/>';
                echo $this->ion_auth->errors();
            }
        }
}