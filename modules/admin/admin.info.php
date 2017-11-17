<?php
class adminModuleInfo {
	public $title="Главный модуль админ. панели";

	public function admin_menu()
	{
		$d=array(
			"admin:index"=>"Главная страница",
			"admin:structure"=>array(
					"Сайт",
					"admin:structure"=>"Структура",
					"admin:menu"=>"Меню",
					"admin:widgets"=>"Виджеты",
                                        "admin:pages"=>"Страницы",
					"admin:add_structure_section"=>"",
					"admin:add_page"=>"",
					"admin:edit_page"=>"",
					"admin:add_module_page"=>"",
					"admin:add_menu"=>"",
					"admin:edit_menu"=>"",
					"admin:add_menu_item"=>"",
					"admin:edit_menu_item"=>"",
					"admin:edit_widget"=>"",
					"admin:select_widget"=>"",
					"admin:add_widget"=>""
			),
			"admin:config"=>array(
					"Система",
					"admin:config"=>"Настройки",
					"admin:languages"=>"Языки",
					"admin:components"=>"Расширения"
				),
		);

		$this->ci=&get_instance();
		if(!$this->ci->load->access("site_structure_accepted","admin","module")){
			unset($d['admin:structure']['admin:structure']);
			unset($d['admin:structure']['admin:add_structure_section']);
                        unset($d['admin:structure']['admin:pages']);
			unset($d['admin:structure']['admin:add_page']);
			unset($d['admin:structure']['admin:edit_page']);
			unset($d['admin:structure']['admin:add_module_page']);
		}
		if(!$this->ci->load->access("site_menu_accepted","admin","module")){
			unset($d['admin:structure']['admin:menu']);
			unset($d['admin:structure']['admin:add_menu']);
			unset($d['admin:structure']['admin:edit_menu']);
			unset($d['admin:structure']['admin:add_menu_item']);
			unset($d['admin:structure']['admin:edit_menu_item']);
		}
		if(!$this->ci->load->access("site_widgets_accepted","admin","module")){
			unset($d['admin:structure']['admin:widgets']);
		}
		if(!$this->ci->load->access("components_accepted","admin","module")){
			unset($d['admin:config']['admin:components']);
			unset($d['admin:config']['admin:edit_widget']);
			unset($d['admin:config']['admin:select_widget']);
			unset($d['admin:config']['admin:add_widget']);
		}
		if(!$this->ci->load->access("config_accepted","admin","module")){
			unset($d['admin:config']['admin:config']);
		}

		if(sizeof($d['admin:structure'])<2){
			unset($d['admin:structure']);
		}
		
		return $d;
	}

	public function translate_fields()
	{
		return array(
			"pages"=>array("title","content","php_file_path","meta_title","meta_keywords","meta_description"),
			"categoryes"=>array("title","description"),
			"uploads"=>array("title"),
			"widgets"=>array("content")
		);
	}

	public function access_rules()
	{
		return array(
			"site_structure_accepted"=>array(
				"type"=>"input:checkbox",
				"label"=>"управление структорой сайта"
			),
			"site_menu_accepted"=>array(
				"type"=>"input:checkbox",
				"label"=>"управление меню сайта"
			),
			"site_widgets_accepted"=>array(
				"type"=>"input:checkbox",
				"label"=>"управление виджетами сайта"
			),
			"components_accepted"=>array(
				"type"=>"input:checkbox",
				"label"=>"управление компонентами"
			),
			"config_accepted"=>array(
				"type"=>"input:checkbox",
				"label"=>"управление настройками сайта"
			)
		);
	}
}
?>