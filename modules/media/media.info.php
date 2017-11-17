<?php
class mediaModuleInfo {
	public $title="Модуль медиа";

	public function admin_menu()
	{
		return array(
			"media:browse"=>array(
					"Медиа",
					"media:browse"=>"Обзор"
			)
		);
	}

	public function admin_config()
	{
		return array(
			array(
				"name"=>"Настройки медиа",
				"type"=>"group"
			),
			array(
				"name"=>"Обработка изображений видео",
				"var_name"=>"config[mod_media_video_picture_options]",
				"type"=>"textarea",
				"config_file_name"=>"config.php"
			),
			array(
				"name"=>"Обработка изображений фото",
				"var_name"=>"config[mod_media_photo_picture_options]",
				"type"=>"textarea",
				"config_file_name"=>"config.php"
			)
		);
	}

	public function front_structure_pages()
	{
		return array();
	}

	public function front_structure_sections()
	{
		return array(
			array(
				"method_name"=>"browse",
				"title"=>"Страница обозревателя",
				"description"=>"выводятся фото, видео, и альбомы",
				"options_method"=>"browse_options",
				"multi_section"=>true
			)
		);
	}

	public function browse_options(&$fb)
	{
		// $options=$this->categoryes_options_list();

		$fb->add("input:text",array(
			"label"=>"ID директории",
			"name"=>"album_id",
			"parent"=>"greed",
			// прячем поле, оно будет показываться только если мы выбрали нужный раздел
			"hidden"=>true,
			// применяем к блоку поля класс по названию текущего метода, чтоб при выборе этого параметра показывать это поле
			"class"=>"hidden_fields ".__FUNCTION__,
		));

		$_POST['extra_name']="album_id";
		$_POST['extra_id']=$_POST['album_id'];
	}
}
?>