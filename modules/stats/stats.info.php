<?php
class shopModuleInfo {
	public $title="Модуль интернет магазина";

	public function admin_menu()
	{
		return array(
			"shop:products"=>array(
					"Интернет магазин",

					"shop:products"=>"Товары",
					"shop:edit_product_m"=>"",
					"shop:add_product_m"=>"",
					
					"shop:product_types"=>"Типы товаров",
					"shop:add_product_type"=>"",
					"shop:add_product_type"=>"",

					// "shop:import"=>"Импорт",
					// "shop:suppliers"=>"",

					// "shop:discounts"=>"Скидки",
					// "shop:stock"=>"Акции",
					
					"shop:cats"=>"Категории",
					"shop:add_cat"=>"",
					"shop:edit_cat"=>"",

					// "shop:payment"=>"оплата",
					// "shop:delivery"=>"доставка",
					// "shop:locations"=>"Города/страны",
					// "shop:orders"=>"Заказы",

					"shop:manufacturers"=>"Бренды",
					"shop:add_manufacturer"=>"",
					"shop:edit_manufacturer"=>"",

					// "shop:collections"=>"Подборки",
					// "shop:add_collection"=>"",
					// "shop:edit_collection"=>"",

					// "shop:actual_products"=>"На доставку",

					"shop:orders"=>"Заказы",
					"shop:order"=>"",

					// "shop:discounts"=>"Скидки",
					// "shop:add_discount"=>"",
					// "shop:edit_discount"=>"",
                            
                                        "stats:test" => "Статистика",
			)
		);
	}

	public function admin_config()
	{
		return array(
			array(
				"name"=>"Настройки интернет магазина",
				"type"=>"group"
			),
			array(
				"name"=>"Обработка изображений товаров",
				"var_name"=>"mod_shop[images_options]",
				"type"=>"textarea",
				"config_file_name"=>"config.php"
			),
			array(
				"name"=>"Обработка изображений категорий",
				"var_name"=>"mod_shop[categories_images_options]",
				"type"=>"textarea",
				"config_file_name"=>"config.php"
			),
			array(
				"name"=>"Обработка изображений производителей",
				"var_name"=>"mod_shop[manufacturers_images_options]",
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
			),
			array(
				"method_name"=>"categories_list",
				"title"=>"Страница со списком всех категорий",
				"description"=>""
			),
			array(
				"method_name"=>"cart",
				"title"=>"Корзина",
				"description"=>""
			),
			array(
				"method_name"=>"order_success",
				"title"=>"Страница \"Заказ успешно оформлен\"",
				"description"=>""
			)
		);
	}

	public function front_structure_sections()
	{
		return array(
			array(
				"method_name"=>"category_base",
				"title"=>"Общая страница категорий",
				"description"=>"если категорий слишком много, можно добавить раздел такого типа, в таком случае все категории будут ссылаться на него",
				"multi_section"=>true
			),
			array(
				"method_name"=>"category",
				"title"=>"Список товаров по категории",
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
			"type"=>"shop-category",
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