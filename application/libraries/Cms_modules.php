<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cms_modules {
	var $ci;
	var $url_structure_res;
	var $d=array();

	public function __construct($params = array())
	{
		$this->ci =& get_instance();

		$this->db=$this->ci->db;
		$this->load=$this->ci->load;
		$this->input=$this->ci->input;
	}

	public function buttons($form_name,$buttons)
	{
		foreach($buttons AS $k=>$r)
		{
			if(!isset($r[1]) || empty($r[1])){
				switch($r[0])
				{
					case'edit':
						$r[1]="Редактировать";
					break;
					case'add':
						$r[1]="Добавить";
					break;
					case'back':
						$r[1]="Назад";
					break;
					case'next':
						$r[1]="Далее";
					break;
					case'delete':
						$r[1]="Удалить";
					break;
					case'apply':
						$r[1]="Применить";
					break;
					case'refresh':
						$r[1]="Обновить";
					break;
					case'save':
						$r[1]="Сохранить";
					break;
					case'upload':
						$r[1]="Загрузить";
					break;
				}
			}
			$buttons[$k]=$r;
		}

		$enabled_languages_num=0;
		foreach($this->ci->languages_res AS $r)
		{
			if($r->enabled==1)$enabled_languages_num++;
		}
		if($enabled_languages_num>1){
			$r=array("language","Перевод",array());
			foreach($this->ci->languages_res AS $language)
			{
				if($language->enabled!=1)continue;

				$r[2][]=$language;
			}

			$buttons[]=$r;
		}

		$this->ci->buttons=$buttons;
		$this->ci->buttons_form_name=$form_name;
	}

	protected function plugin_trigger($plugin_event,$file,$class,$method,$line)
	{
		if(!isset($this->ci->plugins))$this->ci->plugins=array();

		$args=func_get_args();
		unset($args[0],$args[1],$args[2],$args[3],$args[4]);

		$file=str_replace(str_replace("application/libraries","",dirname(__FILE__)),"",$file);

		$args_num=sizeof($args);

		if(preg_match("#^(.*)Module$#is",$class,$pregs)){
			$component_type="module";
			$component_dir="modules";
		}elseif(preg_match("#^(.*)Widget$#is",$class,$pregs)){
			$component_type="widget";
			$component_dir="widgets";
		}
		$component_name=$pregs[1];
		
		foreach(glob("./plugins/".$component_dir."/".$component_name.($this->ci->admin_panel?".admin":"")."/*.php") AS $file)
		{
			$className=preg_replace("#(^[^.]+)\..*#is","\\1",basename($file))."Plugin";

			if(!isset($this->ci->plugins[$className])){
				include_once($file);
				if(!class_exists($className)){
					log_message("error","Plugin error: class ".$className." don't exists, file: ".$file);
					continue;
				}
				
				$this->ci->plugins[$className]=new $className();
			}

			$plugin_method=$method."_".$plugin_event;
			if(!method_exists($this->ci->plugins[$className],$plugin_method)){
				log_message("error","Plugin error: method ".$className."->".$plugin_method."() don't exists, file: ".$file);
				continue;
			}

			call_user_func_array(array($this->ci->plugins[$className],$plugin_method),$args);
		}
	}
}