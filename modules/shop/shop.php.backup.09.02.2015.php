<?php
include_once("./modules/shop/shop.helper.php");

class shopModule extends shopModuleHelper {
	function __construct()
	{
		parent::__construct();

		$this->check_cart_exists();

		// объединяем одинаковый поставщиков
// 		$res=$this->db->query("SELECT * FROM `shop_suppliers`")->result();
// 		$suppliers=array();
// 		$suppliers_ids=array();
// 		foreach($res AS $r)
// 		{
// 			if(!isset($suppliers[$r->title]))$suppliers[$r->title]=array();
// 			$suppliers[$r->title][]=$r->id;
// 		}

// 		foreach($suppliers AS $title=>$ids)
// 		{
// 			if(sizeof($ids)<2)continue;
// 			$suppliers_ids[$title]=min($ids);
// 		}
// 		foreach($suppliers_ids AS $title=>$new_id)
// 		{
// 			unset($suppliers[$title][array_search($new_id,$suppliers)]);

// 			foreach($suppliers[$title] AS $dublicate_id)
// 			{
// 				print "UPDATED: ".$dublicate_id." TO ".$new."<br />";
// 				$this->db
// 				->where("supplier_id",$dublicate_id)
// 				->update("shop_suppliers_products_availability",array(
// 					"supplier_id"=>$new_id
// 				));

// 				$this->db
// 				->where("id",$dublicate_id)
// 				->delete("shop_suppliers");
// 			}
// 		}

// 		$products_to_update=array();
// 		$res=$this->db->query("SELECT COUNT(*) AS count, supplier_id, product_id FROM shop_suppliers_products_availability GROUP BY CONCAT(supplier_id,':',product_id)")->result();
// 		foreach($res AS $r)
// 		{
// 			if($r->count<2)continue;
// // 34,877
// 			$this->db->query("DELETE FROM shop_suppliers_products_availability WHERE supplier_id=".$r->supplier_id." && product_id=".$r->product_id." LIMIT ".($r->count-1));
// 			print "delete dublicate ".$r->supplier_id." : ".$r->product_id."<br />";
// 		}
// 		// print_r($suppliers_ids);
// 		die("\n\n<hr />OK!");

		
		// добавляем всем товарам поставщиков
		// if($_GET['s']){
		// 	ini_set("memory_limit","512M");
		// 	ini_set("display_errors",1);
		// 	error_reporting(E_ALL);
		// 	include("modules/shop/shop_products-all-dump.php");
		// 	$i=0;
		// 	foreach($shop_products AS $r)
		// 	{
				
		// 		$num=$this->db
		// 		->where("product_id",$r['id'])
		// 		->count_all_results("shop_suppliers_products_availability");
				
		// 		if($num==0){
		// 			print "INSERT INTO `shop_suppliers_products_availability` VALUES (2,".$r['id'].",0);<br />";
		// 			$i++;
		// 		}
		// 	}
		// 	exit;
		// }
	}

	public function search()
	{
		$where=array();

		if($this->input->get("keywords")!==false && $this->input->get("keywords")!=""){
			$keywords=search_clear_text($this->input->get("keywords"));

			$where['((CONCAT(shop_products.title,\' \',shop_products.code) LIKE \'%'.str_replace(" ","%' || CONCAT(shop_products.title,' ',shop_products.code) LIKE '%",$keywords).'%\') || shop_products.code=\''.mysql_escape_string($this->input->get("keywords")).'\')']=NULL;
		}else{
			$this->ci->load->frontView("content/search_no_results",$this->d);
		}

		$this->category($where,array(
			"search"=>true
		));
	}

	public function category_base()
	{
		if(preg_match("#-([0-9]+)/[^/]*$#is",$_SERVER['REQUEST_URI'],$matches)){
			$_GET['category_id']=$matches[1];
			
			$this->category_res=$this->db
			->select("uploads.file_path, uploads.file_name")
			->select("categoryes.*")
			->join("uploads","uploads.extra_id = categoryes.id && uploads.name = 'category_image' && uploads.component_name = 'shop'","left")
			->get_where("categoryes",array(
				"categoryes.id"=>$_GET['category_id']
			))->row();

			if($this->category_res->id<1)show_404();

			$this->d['category_res']=&$this->category_res;

			if(is_string($this->category_res->options)){
				$this->category_res->options=json_decode($this->category_res->options);
			}

			if($this->category_res->parent_id==0){
				$this->categories_list();
				return false;
			}

			$this->category(NULL,array(
				"join"=>array(
					array(
						"shop_products_categories_link","shop_products_categories_link.product_id = shop_products.id && shop_products_categories_link.category_id IN(".$matches[1].")"
					)
				)
			));
			return false;
		}elseif(preg_match("#-([0-9]+)m/[^/]*$#is",$_SERVER['REQUEST_URI'],$matches)){
			$_GET['manufacturer_id']=$matches[1];
			$this->manufacturer_view();
			// $this->category(array(
			// 	"shop_products.brand_id"=>$matches[1]
			// ));
			return false;
		}elseif(preg_match("#-([0-9]+)\.html(\?.*)?$#is",$_SERVER['REQUEST_URI'],$matches)){
			$_GET['product_id']=$matches[1];
			$this->view_product();
			return false;
		}
	}

	public function category($where=NULL,$d=NULL)
	{
		$this->d['category_res']=&$this->category_res;
		if(is_string($this->url_structure_res->options)){
			$this->url_structure_res->options=json_decode($this->url_structure_res->options);
		}

		$this->url_structure_res->options->category_id=intval($this->url_structure_res->options->category_id);
		if($this->url_structure_res->options->category_id>0){
			if(!isset($d['join']))$d['join']=array();
			$d['join'][]=array(
				"shop_products_categories_link","shop_products_categories_link.product_id = shop_products.id && shop_products_categories_link.category_id IN(".$this->url_structure_res->options->category_id.")"
			);
		}

		if(preg_match("#-([0-9]+)\.html$#is",$_SERVER['REQUEST_URI'],$matches)){
			$_GET['product_id']=$matches[1];
			$this->view_product();
			return false;
		}

		$this->d['where']=array();
		if(!is_null($where)){
			$this->d['where']=$where;
		}
		if(isset($this->url_structure_res)){
			$this->url_structure_res->options->category_id=(int)$this->url_structure_res->options->category_id;
			if($this->url_structure_res->options->category_id>0){
				$cat_ids=array();
				$cat_ids=$this->rcats_list($this->url_structure_res->options->category_id);
				$cat_ids[]=$this->url_structure_res->options->category_id;

					// !!!!// !!!!// !!!!// !!!!// !!!!// !!!!// !!!!// !!!!// !!!!
				// $this->d['where']['shop_products.category_id IN ('.implode(",",$cat_ids).')']=NULL;

				// получаем информацию о выбранной категории
				$this->d['category_res']=$this->db
				->get_where("categoryes",array(
					"id"=>$this->url_structure_res->options->category_id
				))
				->row();
			}
		}

		if($this->input->get("year")!==false){
			$year=intval($this->input->get("year"));

			$this->d['where']['(shop_products.date_public >= '.mktime(0,0,0,1,1,$year).' && shop_products.date_public <= '.mktime(0,0,0,1,1,$year+1).')']=NULL;
		}

		$this->d['where']['shop_products.show']=1;

		$products_query=$this->products_query();
		$products_query_num=$this->products_num_query();

		if(sizeof($d['join'])>0){
			foreach($d['join'] AS $join)
			{
				$products_query->join($join[0],$join[1],$join[2]);
				$products_query_num->join($join[0],$join[1],$join[2]);
			}
		}

		$products_filter_query=clone $products_query;

		if($this->input->get("filter")!==false){
			$secs=explode(":",$this->input->get("filter"));
			$this->d['filter_selected']=array();
			foreach($secs AS $sec)
			{
				$ids=explode("-",$sec);
				$field_id=$ids[0];
				unset($ids[0]);
				$this->d['filter_selected'][$field_id]=$ids;
			}
		}

		if($this->input->get("manufacturer_id")!==false){
			$this->d['where']['shop_products.brand_id']=intval($this->input->get("manufacturer_id"));
		}

		if(intval($_GET['manufacturer_id'])>0){
			$this->d['manufacturer_res']=$this->db
			->get_where("categoryes",array(
				"id"=>intval($_GET['manufacturer_id']),
				"show"=>1
			))
			->row();
		}

		if($this->category_res->options->disable_filter!=1){
			// получаем данные по фильтрам
			$this->d['products_filter_res']=$products_filter_query
			->join("shop_product_types","shop_product_types.id = shop_products.type_id && shop_product_types.show = 1")
			->group_by("shop_products.type_id")
			->where($this->d['where'])
			->get()
			->result();

			$filter_where=array();
			$exists_filters=array();
			foreach($this->d['products_filter_res'] AS $r)
			{
				if(!in_array($r->type_id,$exists_filters)){
					$exists_filters[]=$r->type_id;
				}else{
					continue;
				}
				
				$r->fields=$this->db
				->get_where("shop_product_type_fields",array(
					"type_id"=>$r->type_id,
					"filter"=>1
				))
				->result();

				foreach($r->fields AS $field)
				{
					if(is_string($field->params)){
						$field->params=json_decode($field->params);
					}

					if(isset($this->d['filter_selected'][$field->id])){
						switch($field->field_type)
						{
							case'select':
								$ids=array();
								foreach($field->params->options AS $value=>$option)
								{
									if(!in_array($value,$this->d['filter_selected'][$field->id]))continue;

									// if(!isset($filter_where['shop_products.f_'.$field->id])){
									// 	$filter_where['shop_products.f_'.$field->id]=array($value);
									// }
									$ids[]=$value;
									// $filter_where['shop_products.f_'.$field->id][]=$value;
								}

								if(sizeof($ids)>0){
									$products_query->where("`f_".$field->id."` IN(".implode(",",$ids).")");
									$products_query_num->where("`f_".$field->id."` IN(".implode(",",$ids).")");
								}
							break;
						}
					}
				}
			}
		}

		$this->d['perpage']=3;

		$this->d['pg']=intval($this->input->get("pg"));
		if($this->d['pg']<2)$this->d['pg']=1;

		include_once("./application/libraries/Cms_paginator.php");
		$limit=100;

		$this->d['paginator']=new cms_paginator;

		$this->d['products_num']=$products_query_num
		->where($this->d['where'])
		->count_all_results();

		$this->d['paginator']->items_total=$this->d['products_num'];
		$this->d['paginator']->current_page=$this->d['pg'];
		$this->d['paginator']->mid_range=5;
		$this->d['paginator']->items_per_page=30;
		$this->d['paginator']->paginate();

		$this->d['pgs_num']=ceil($this->d['products_num']/$this->d['perpage']);

		$products_query_exists_manufacturers_query=clone $products_query;

		$order_dir="asc";
		if($this->input->get("order_by")!==false){
			list($order_by,$order_dir)=explode(":",$this->input->get("order_by"));
			$order_dir=$order_dir=="asc"?"asc":"desc";
		}
		switch($order_by)
		{
			case'added':
				$this->d['products_order_by']="added:".$order_dir;
				$order_by="shop_products.date_public";
			break;
			default:
			case'price':
				$this->d['products_order_by']="price:".$order_dir;
				$order_by="shop_products.price";
			break;
		}
		$order_by.=" ".strtoupper($order_dir);

		if($d['search']){
			$order_by="shop_products.code = '".mysql_escape_string($this->input->get("keywords"))."' DESC, ".$order_by;
		}

		$this->d['products_res']=$products_query
		->select("brand.title AS brand_title")
		->where($this->d['where'])
		->limit($this->d['paginator']->items_per_page,$this->d['paginator']->low)
		->join("categoryes AS brand","brand.type IN('shop-manufacturer') && brand.id = shop_products.brand_id","left")
		->order_by($order_by)
		->get()
		->result();

		foreach($this->d['products_res'] AS $r)
		{
			$r->link=$this->link_product_view($r);
			$r->add_to_cart_attrs=$this->add_to_cart_attrs($r);
			$r->price_hmn=$this->product_price($r);
		}


		$products_query_manufacturers_where=$this->d['where'];
		unset($products_query_manufacturers_where['shop_products.brand_id']);
		$this->d['exists_products_manufacturers']=$products_query_exists_manufacturers_query
		->select("shop_products.brand_id")
		->group_by("shop_products.brand_id")
		->where($products_query_manufacturers_where)
		->get()
		->result();

		$exists_products_manufacturers=array();
		foreach($this->d['exists_products_manufacturers'] AS $r)
		{
			if($r->brand_id<1)continue;
			$exists_products_manufacturers[]=$r->brand_id;
		}

		$this->d['manufacturers_res']=array();
		if(sizeof($exists_products_manufacturers)>0){
			$this->d['manufacturers_res']=$this->db
			->select("categoryes.id, categoryes.title")
			->get_where("categoryes",array(
				"categoryes.type"=>"shop-manufacturer",
				"categoryes.show"=>1,
				"categoryes.id IN(".implode(",",$exists_products_manufacturers).")"=>NULL
			))
			->result();
		}

		$this->ci->load->meta(base_url($this->link_category($this->category_res)),"og:url"); 
		$this->ci->load->meta($this->category_res->title,"og:title");
		$this->ci->load->meta($this->category_res->title,"og:description");
		if(!empty($this->category_res->file_name)){
			$this->ci->load->meta(base_url($this->category_res->file_path.$this->category_res->file_name),"og:image");
		}

		$this->ci->load->frontView("shop/products_list",$this->d);
	}

	public function view_product()
	{
		$product_id=(int)$this->input->get("product_id");

		$product_query=$this->products_query();

		$this->d['product_res']=$product_query
		->select("shop_collection_products.collection_id")
		->select("brand.name AS brand_name, brand.title AS brand_title")
		->join("categoryes AS brand","brand.id = shop_products.brand_id","left")
		->join("shop_product_types","shop_product_types.id = shop_products.type_id","left")
		->join("shop_collection_products","shop_collection_products.product_id = shop_products.id","left")
		->where("shop_products.id",$product_id)
		->get()
		->row();

		if($this->d['product_res']->id<1)show_404();

		$brand_r=clone $this->d['product_res'];
		$brand_r->title=$this->d['product_res']->brand_title;
		$brand_r->id=$this->d['product_res']->brand_id;
		$brand_r->name=$this->d['product_res']->brand_name;
		// var_dump($this->link_manufacturer($brand_r));exit;
		$this->d['product_res']->brand_link=$this->link_manufacturer($brand_r);

		

		$this->d['categoryes_res']=$this->db
		->select("category_id")
		->group_by("category_id")
		->get_where("shop_products_categories_link",array(
			"product_id"=>$product_id
		))
		->result();

		$this->d['shop_product_type_fields_res']=$this->db
		->get_where("shop_product_type_fields",array(
			"type_id"=>$this->d['product_res']->type_id
		))
		->result();

		
		$this->d['product_res']->price_hmn=$this->product_price($this->d['product_res']);
		$this->d['product_res']->price_old_hmn=$this->product_price($this->d['product_res'],"old");

		foreach($this->d['shop_product_type_fields_res'] AS $r)
		{
			if(is_string($r->params)){
				$r->params=json_decode($r->params);
			}
		}

		// получаем товары из этой коллекции
		if($this->d['product_res']->collection_id>0){
			$this->d['shop_collection_products_res']=$this->db
			->select("shop_collection_products.product_id")
			->get_where("shop_collection_products",array(
				"shop_collection_products.collection_id"=>$this->d['product_res']->collection_id,
				"shop_collection_products.product_id !="=>$this->d['product_res']->id
			))
			->result();

			$collection_products_ids=array();
			foreach($this->d['shop_collection_products_res'] AS $r)
			{
				$collection_products_ids[]=$r->product_id;
			}

			$this->d['shop_collection_products_res']=$this
			->products_query()
			->where("shop_products.show",1)
			->where(array(
				"shop_products.id IN (".implode(",",$collection_products_ids).")"=>NULL
			))
			->get()
			->result();

			foreach($this->d['shop_collection_products_res'] AS $r)
			{
				$r->link=$this->link_product_view($r);
				$r->add_to_cart_attrs=$this->add_to_cart_attrs($r);
				$r->price_hmn=$this->product_price($r);

				// $r->short_description="~~";
			}
		}

		if(is_string($this->d['product_res']->widgets_options)){
			$this->d['product_res']->widgets_options=json_decode($this->d['product_res']->widgets_options);
		}

		if(is_string($this->d['product_res']->category_path)){
			$this->d['product_res']->category_path=json_decode($this->d['product_res']->category_path);
		}

		$this->ci->load->meta($this->d['product_res']->meta_title,"title");
		$this->ci->load->meta($this->d['product_res']->meta_description,"description");
		$this->ci->load->meta($this->d['product_res']->meta_keywords,"keywords");

		if($this->d['product_res']->disallow_bot_index==1){
			$this->ci->load->meta("noindex","robots");
		}

		// получаем все фотографии
		$this->d['product_photos_res']=$this->db
		->get_where("uploads",array(
			"name"=>"product-photo",
			"component_type"=>"module",
			"component_name"=>"shop",
			"extra_id"=>$this->d['product_res']->id
		))
		->result();

		$this->ci->load->meta(base_url($this->link_product_view($this->d['product_res'])),"og:url"); 
		$this->ci->load->meta($this->d['product_res']->title,"og:title");
		$this->ci->load->meta($this->d['product_res']->short_description,"og:description");
		if(!empty($this->d['product_res']->main_picture_file_name)){
			$this->ci->load->meta(base_url($this->d['product_res']->main_picture_file_path.$this->d['product_res']->main_picture_file_name),"og:image");
		}

		$this->ci->load->frontView("shop/view_product",$this->d);
	}

	public function add_product_to_cart()
	{
		$cart=$this->get_cart();

		$imgName = explode('/',$this->input->get("img"));
		$product_id=intval($this->input->get("product_id")).':'.end($imgName).':'.$this->input->get("size");
		// $product_id=intval($this->input->get("product_id"));
		$quantity=intval($this->input->get("quantity"));
		$type=intval($this->input->get("type"));
		$img=$this->input->get("img");
		$size=$this->input->get("size");
		if($product_id<1)return false;

		if($quantity<1)$quantity=1;


		if($type=="add"){
			$cart->products->{$product_id}->img=$img;
			$cart->products->{$product_id}->size=$size;
			if(!isset($cart->products->{$product_id})){
				$cart->products->{$product_id}->quantity=$quantity;
			}else{
				if(!isset($cart->products->{$product_id}->quantity)){
					$cart->products->{$product_id}->quantity=1;
				}else{
					$cart->products->{$product_id}->quantity+=$quantity;
				}
			}
		}else{
			if(isset($cart->products->{$product_id})){
				unset($cart->products->{$product_id});
			}
		}

		$cart=$this->save_cart($cart,true);


		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		print json_encode(array(
			"products_num"=>sizeof((array)$cart->products),
			"price"=>$cart->total_amount,
			"price_hmn"=>$this->price($cart->total_amount)
		));
	}

	public function categories_list()
	{
		$where=array(
			"categoryes.type"=>"shop-category",
			"categoryes.show"=>1
		);

		if($_GET['category_id']>0){
			$where['categoryes.parent_id']=$_GET['category_id'];
		}

		$this->d['categories_res']=$this->db
		->select("uploads.file_path, uploads.file_name")
		->select("categoryes.*")
		->join("uploads","uploads.extra_id = categoryes.id && uploads.name = 'category_image' && uploads.component_name = 'shop'","left")
		->order_by("categoryes.order")
		->get_where("categoryes",$where)
		->result();

		$this->ci->load->frontView("shop/categories_list",$this->d);
	}

	public function cart()
	{
		$this->d['delivery_methos']=$this->delivery_methos;
		$this->d['cart']=$this->get_cart($cart,true);
		if($this->input->post("recalc_sm")!==false){
			$quantity=$this->input->post("quantity");
			foreach($this->d['cart']->products AS $product_id=>$product_r)
			{
				$orig_id = $product_id;
				$product_id = str_replace('.','-',$product_id);
				if(isset($quantity[$product_id])){
					$quantity[$product_id]=intval($quantity[$product_id]);

					if($quantity[$product_id]>0){
						$product_r->quantity=$quantity[$product_id];
					}
				}else{
					unset($this->d['cart']->products->{$orig_id});
				}
			}
			$this->d['cart']=$this->save_cart($this->d['cart'],true);
		}

		if($this->input->post("login_type")===false){
			$this->d['login_type_checked']['register']=' checked="checked"';
		}else{
			$this->d['login_type_checked'][$this->input->post("login_type")]=' checked="checked"';
		}

		$user_id=intval($this->ci->session->userdata("user_id"));
		$login_type=trim(htmlspecialchars($this->input->post("login_type")));
		$register_name=trim(htmlspecialchars($this->input->post("register_name")));
		$register_password=trim(htmlspecialchars($this->input->post("register_password")));
		$login_name=trim(htmlspecialchars($this->input->post("login_name")));
		$login_password=trim(htmlspecialchars($this->input->post("login_password")));
		$delivery_method=trim(htmlspecialchars($this->input->post("delivery_method")));
		$delivery_address=trim(htmlspecialchars($this->input->post("delivery_address")));

		$delivery_city=trim(htmlspecialchars($this->input->post("delivery_city")));
		$delivery_storage=trim(htmlspecialchars($this->input->post("delivery_storage")));
		$delivery_name=trim(htmlspecialchars($this->input->post("delivery_name")));

		$name=trim(htmlspecialchars($this->input->post("name")));
		$phone=trim(htmlspecialchars($this->input->post("phone")));
		$email=trim(htmlspecialchars($this->input->post("email")));
		$notes=trim(htmlspecialchars($this->input->post("notes")));
		$order_step2=trim(htmlspecialchars($this->input->post("order_step2")));

		// создаем заказ пустышку
		$create_order=false;
		if($this->d['cart']->order_id==0){
			$create_order=true;
		}else{
			$orders_num=$this->db->count_all_results("shop_orders");
			if($orders_num<1){
				$create_order=true;
			}
		}

		if($create_order){
			$this->db->insert("shop_orders",array(
				"user_id"=>$user_id,
				"delivery_method"=>$delivery_method,
				"delivery_address"=>"",
				"delivery_city"=>"",
				"delivery_storage"=>"",
				"delivery_name"=>"",
				"name"=>"",
				"phone"=>"",
				"notes"=>"",
				"basket"=>json_encode($this->d['cart']),
				"total_amount"=>0,
				"total_amount_with_discount"=>0,
				"discount"=>"",
				"status"=>"-1",
				"status_history"=>"",
				"date_add"=>mktime()
			));

			$order_id=$this->db->insert_id();

			$this->d['cart']->order_id=$order_id;

			$this->d['cart']=$this->save_cart($this->d['cart'],true);
		}

		$this->d['order_res']=$this->db
		->get_where("shop_orders",array(
			"id"=>$this->d['cart']->order_id
		))
		->row();

		$this->d['order_errors']=array();

		$product_ids=array_keys((array)$this->d['cart']->products);

		// Den фото с разными выбранными цветами выводятся с разными айдишниками
		foreach($product_ids as $id){
			$_product_ids[] = reset(explode(':',$id));
		}
		$product_ids=$_product_ids;
		// Den

		if(sizeof($product_ids)>0){
			$this->d['cart_products_res']=$this
			->products_query()
			->where(array(
				"shop_products.id IN(".implode(",",$product_ids).")"=>NULL
			))
			->get()
			->result();
		}

		foreach($this->d['cart_products_res'] AS $r)
		{
			foreach($this->d['cart']->products as $id=>$p){
				if(reset(explode(':',$id))==$r->id){
					$cr = clone $r;
					$cr->quantity=$p->quantity;
					$cr->color=$p->img;
					$cr->size=$p->size;
					$cr->price_hmn=$this->product_price($cr);
					$cr->price_total=$cr->price*$cr->quantity;
					$cr->price_total_hmn=$this->price($cr->price_total);

					$this->d['cart']->products->$id=$cr;
				}
			}
		}
// $color_recalc = 0;
// foreach($this->d['cart']->products as $idp=>$r){
// 	$idr = explode(':',$idp);
// 	if($idr[1]!=$r->main_picture_file_name){
// 		$color_recalc = 1;
// 	}
// 	$prices[] = $r->price_total;
// }
// $prices_total = 0;
// foreach($prices as $p){
// 	$prices_total = $prices_total+$p;
// }

// $this->d['cart']->total_amount=$prices_total;
// $this->d['cart']->total_amount_hmn=$prices_total.".00 грн";
		$this->d['discount']=$this->discount_price_calc($this->d['cart']->total_amount,false);

		// первый шаг при заполнении формы
		$submit=false;
		if($this->input->post("order_step2")!==false
			|| $this->input->post("order_step3")!==false){
			$submit=true;

			if($this->input->post("order_step2")!==false){
				// if($user_id==0){
				// 	if($login_type=="register"){
				// 		if(empty($register_name))$this->d['order_errors'][]="Поле \"ФИО\" не заполнено!";
				// 		if(empty($register_password))$this->d['order_errors'][]="Поле \"Пароль\" не заполнено!";
				// 	}else{
				// 		if(empty($login_name))$this->d['order_errors'][]="Поле \"E-mail\" не заполнено!";
				// 		if(empty($login_password))$this->d['order_errors'][]="Поле \"Пароль\" не заполнено!";
				// 	}
				// }
				// if(empty($email))$this->d['order_errors'][]="Поле \"E-mail\" не заполнено!";
				// if(empty($name))$this->d['order_errors'][]="Поле \"ФИО\" не заполнено!";
				// if(empty($phone))$this->d['order_errors'][]="Поле \"Телефон\" не заполнено!";

				// првоеряем адрес доставки !!
				if($delivery_method==1){
					// доставка по киеву
					if(empty($delivery_address))$this->d['order_errors'][]="Поле \"Адрес доставки\" не заполнено!";
				}elseif($delivery_method==2){
					// доставка в другой город
					if(empty($delivery_city))$this->d['order_errors'][]="Поле \"Город доставки\" не заполнено!";
					if(empty($delivery_storage))$this->d['order_errors'][]="Поле \"Склад доставки\" не заполнено!";
					if(empty($delivery_name))$this->d['order_errors'][]="Поле \"Фамилия получателя посылки\" не заполнено!";
				}
				
				// if(empty($notes))$this->d['order_errors'][]="Поле \"Комментарии к заказу\" не заполнено!";
			}

			// if($user_id==0){
			// 	// регистрируем нового пользователя
			// 	if($login_type=="register"){
			// 		$additional_data=array(
			// 			"first_name"=>$register_name,
			// 			"last_name"=>"",
			// 			"phone"=>$phone,
			// 			"active"=>1
			// 		);
			// 		$group_id=2;

			// 		$user_id=$this->ci->ion_auth->register($email,$register_password,$email,$additional_data,array($group_id));

			// 		if($user_id===false){
			// 			$this->d['order_errors'][]=$this->ci->ion_auth->errors();
			// 		}elseif($user_id===0){
			// 			$this->d['order_errors'][]="Вы не можете быть зарегистрированы по неизвестным причинам, обратитесь к администрации!";
			// 		}

			// 		if(sizeof($this->d['order_errors'])==0){
			// 			$remember=true;
			// 			if(!$this->ci->ion_auth->login($email,$register_password,$remember)){
			// 				$this->d['order_errors'][]=$this->ion_auth->errors();
			// 			}else{
			// 				$user_id=intval($this->ci->session->userdata("user_id"));
			// 			}
			// 		}
			// 	}else{
			// 		// авторизируем уже существующего
			// 		$remember=true;
			// 		if(!$this->ci->ion_auth->login($login_name,$login_password,$remember)){
			// 			$this->d['order_errors'][]=$this->ion_auth->errors();
			// 		}else{
			// 			$user_id=intval($this->ci->session->userdata("user_id"));
			// 		}
			// 	}
			// }

			// оформление, этап №1
			if(sizeof($this->d['order_errors'])==0 && $this->input->post("order_step2")!==false){
				$this->d['discount']=$this->discount_price_calc($this->d['cart']->total_amount,$delivery_method==1);

				$this->d['order_res']->delivery_method=$delivery_method;
				$this->d['order_res']->delivery_address=$delivery_address;
				$this->d['order_res']->delivery_city=$delivery_city;
				$this->d['order_res']->delivery_storage=$delivery_storage;
				$this->d['order_res']->delivery_name=$delivery_name;
				// $this->d['order_res']->user_id=$user_id;
				$this->d['order_res']->user_id=0;
				$this->d['order_res']->name=$name;
				$this->d['order_res']->phone=$phone;
				$this->d['order_res']->email=$email;
				$this->d['order_res']->notes=$notes;
				$this->d['order_res']->color=$img;
				$this->d['order_res']->basket=json_encode($this->d['cart']);
				$this->d['order_res']->total_amount=$this->d['cart']->total_amount;
				$this->d['order_res']->discount=json_encode($this->d['discount']);
				$this->d['order_res']->total_amount_with_discount=$this->d['discount']['price'];
				
				$order_id=$this->d['order_res']->id;

				$this->db
				->where("id",$order_id)
				->update("shop_orders",(array)$this->d['order_res']);

				//  отправляем письмо администратору, отправляем письмо покупателю

				$order_id=$this->d['order_res']->id;
				$this->change_order_status($order_id,"submited");

				// Den
					if($user_id>0){
						$res=$this->db->query("SELECT * FROM `users` WHERE `id`='".$user_id."' ")->result();
					}
				// Den
				$email_html="";
				$email_html_products="";
				$email_html_products.=<<<EOF
<table cellspacing="0" cellpadding="5" border="1" align="center">
<tr>
	<th><strong>Товар</strong></th>
	<th><strong>Наименование</strong></th>
	<th><strong>Количество</strong></th>
	<th><strong>Цена</strong></th>
</tr>
EOF;
				foreach($this->d['cart']->products AS $idr=>$r)
				{
					$r->link=base_url($this->link_product_view($r));

					$total_line="{$r->price_hmn} * {$r->quantity} = {$r->price_total_hmn}";
					if($r->show!=1){
						$r->quantity=0;
						$total_line="<strong style=\"color:red;\">на момент заказа этого товара небыло в наличии</strong>";
					}
					$email_html_products.=<<<EOF
					<tr>
EOF;
					// Den
					if(empty($r->color)){
						$res=$this->db->query("SELECT * FROM `uploads` WHERE `extra_id`=".$r->id." && `name`='product-photo' && `order`=1")->result();
						$img = base_url('/uploads/shop/products/thumbs3/'.$res[0]->file_name);
						$email_html_products.='<td><img src="'.$img.'" /></td>';
					}else{
						$img = explode(':',$idr);
						$img = base_url('/uploads/shop/products/thumbs3/'.$img[1]);
						$email_html_products.='<td><img src="'.$img.'" /></td>';
					}
					$email_html_products.=<<<EOF
	<td><a href="{$r->link}" target="_blank">{$r->title}</a>
EOF;
					if(!empty($r->size)){ 
						$size = explode(':',$idr);
						$size = $size[2];
						$email_html_products.='<br />Размер: '.$size; 
					}

					$email_html_products.='</td>';
					$email_html_products.=<<<EOF
	<td align="center">{$r->quantity}</td>
	<td>{$total_line}</td>
</tr>
EOF;
				}

				$email_html_products.=<<<EOF
<tr>
	<td colspan="4">
EOF;

				if($this->d['discount']['difference']>0){
					$email_html_products.=<<<EOF
<strong>Общая сумма: </strong> {$this->d['cart']->total_amount_hmn}
<br /><strong>Скидка: </strong> {$this->d['discount']['difference_hmn']}
<br />
<br /><strong>Доставка: </strong> {$this->d['discount']['delivery_hmn']}
<br />
<br /><strong>Итого: </strong> {$this->d['discount']['price_total_hmn']}
EOF;
				
				}else{
					$email_html_products.=<<<EOF
<strong>Итого: </strong> {$this->d['cart']->total_amount} $
EOF;
				
				}

				$email_html_products.=<<<EOF
	</td>
</tr>
</table>
EOF;

				$email_html_order_info="";
				$email_html_order_info2="";

				$delivery_method_hmn=$this->delivery_methos[$delivery_method];
// Den
				$email_html_order_info.=<<<EOF
<p><strong>Номер заказа:</strong> {$this->d['order_res']->id}</p>
<p><strong>ID пользователя в системе:</strong> {$user_id}</p>
<p><strong>ФИО:</strong> {$res[0]->first_name}</p>
<p><strong>E-mail:</strong> {$res[0]->email}</p>
<p><strong>Телефон:</strong> {$res[0]->phone}</p>

<p><strong>Комментарии к заказу:</strong> {$this->d['order_res']->notes}</p>
EOF;
// Den
				if($user_id>0){
					$email_html_order_info2.=<<<EOF
EOF;
				}
				$this->ci->load->library("email");

				// получаем список всех администраторов
				$users_res=$this->db
				->select("users.id, users.username, users.email, users.first_name, users.last_name")
				->join("users_groups","users_groups.user_id = users.id && users_groups.group_id = 1")
				->group_by("users.email")
				->get_where("users",array(
					"active"=>1
				))
				->result();

				foreach($users_res AS $r)
				{
					$this->ci->email->from($this->ci->config->config['email_from'],$this->ci->config->config['email_from_name']);
					$this->ci->email->to($r->email,trim($r->first_name." ".$r->last_name));
					$this->ci->email->subject("Новый заказ №".$this->d['order_res']->id);
					$this->ci->email->message($email_html_order_info.$email_html_order_info2.$email_html_products);	
					$this->ci->email->send();
					// print $this->ci->email->print_debugger();
					// exit;
				}

				$this->ci->email->from($this->ci->config->config['email_from'],$this->ci->config->config['email_from_name']);
				$this->ci->email->to($res[0]->email);
				$this->ci->email->subject("Новый заказ №".$this->d['order_res']->id);
				$this->ci->email->message($email_html_order_info.$email_html_products);	
				$this->ci->email->send();

				// отправляем SMS
				$phone=preg_replace("#[^+0-9]#is","",$this->d['order_res']->phone);
				if(!empty($phone)){
					$client=new SoapClient("http://turbosms.in.ua/api/wsdl.html");
					$result=$client->Auth(array(
					"login"=>"impsapi",
					"password"=>"4432089"
					));
					$result=$client->SendSMS(Array(
				        "sender"=>"im-ps.com",
				        "destination"=>$phone,
				        "text"=>"Номер вашего заказа: ".$this->d['order_res']->id
		    		));
	    		}

				// удаляем корзину
				$shop_cart_id=intval($this->ci->session->userdata("shop_cart_id"));
				$this->db
				->where("id",$shop_cart_id)
				->delete("shop_carts");

				redirect($this->order_success_link());
				exit;
			}
		}

		if(!$submit && $user_id>0){
			$this->d['email']=$this->ci->session->userdata("email");
			$this->d['delivery_method']="";
			$this->d['delivery_address']="";
			$this->d['delivery_city']="";
			$this->d['delivery_storage']="";
			$this->d['delivery_name']="";

			$this->d['name']="";
			$this->d['phone']="";
			$this->d['notes']="";
		}else{
			if($submit){
				$this->d['email']=$email;
				$this->d['delivery_method']=$delivery_method;
				$this->d['delivery_address']=$delivery_address;
				$this->d['delivery_city']=$delivery_city;
				$this->d['delivery_storage']=$delivery_storage;
				$this->d['delivery_name']=$delivery_name;
				$this->d['name']=$name;
				$this->d['phone']=$phone;
				$this->d['notes']=$notes;
			}
		}

		foreach($this->d['cart']->products AS $r)
		{
			$r->link=$this->link_product_view($r);
		}

		$this->ci->load->frontView("shop/cart",$this->d);
	}

	public function order_success()
	{
		$this->ci->load->frontView("shop/order_success",$this->d);
	}

	public function manufacturer_view()
	{
		$this->d['manufacturer_res']=$this->db
		->get_where("categoryes",array(
			"id"=>intval($_GET['manufacturer_id']),
			"show"=>1
		))
		->row();

        $this->d['manufacturer_res']->image=$this->db
            ->get_where("uploads",array(
                "extra_id"=>intval($_GET['manufacturer_id']),
                "name"=>"manufacturer_logo",
            ))
            ->row();

		if(intval($this->d['manufacturer_res']->id)<1){
			show_404();
		}

		$shop_products_res=$this->db
		->select("id")
		->get_where("shop_products",array(
			"show"=>1,
			"brand_id"=>intval($_GET['manufacturer_id'])
		))
		->result();

		$ids=array();
		foreach($shop_products_res AS $r)
		{
			$ids[]=$r->id;
		}

		if(sizeof($ids)>0){
			$shop_products_categories_link_res=$this->db
			->select("category_id")
			->group_by("category_id")
			->get_where("shop_products_categories_link",array(
				"product_id IN(".implode(",",$ids).")"=>NULL
			))
			->result();

			$ids=array();
			foreach($shop_products_categories_link_res AS $r)
			{
				$ids[]=$r->category_id;
			}
			
			$this->d['categories_res']=$this->db
			->select("uploads.file_path, uploads.file_name")
			->select("categoryes.*")
			->join("uploads","uploads.extra_id = categoryes.id && uploads.name = 'category_image' && uploads.component_name = 'shop'","left")
			->get_where("categoryes",array(
				"categoryes.id IN(".implode(",",$ids).")"=>NULL,
				"categoryes.show"=>1
			))->result();
		}

		$this->ci->load->frontView("shop/manufacturer",$this->d);
	}

	public function hotline_export()
	{
		ini_set("memory_limit","256M");

		// http://imperia-posudy.com/index.php?m=shop&a=hotline_export
		$this->d['categories_res']=$this->db
		->select("categoryes.id, categoryes.title")
		->order_by("categoryes.order")
		->get_where("categoryes",array(
			"categoryes.type"=>"shop-category",
			"categoryes.show"=>1
		))
		->result();

		$xml="";
		$date_xml=date("Y-m-d H:i");
		$xml.=<<<EOF
<?xml version="1.0" encoding="windows-1251" ?>
<price>
    <date>{$date_xml}</date>
    <firmName>Империя посуды</firmName>
    <firmId>21247</firmId>
    <rate></rate>
    <categories>
EOF;
		$cat_ids=array();
		foreach($this->d['categories_res'] AS $r)
		{
			$xml.=<<<EOF
<category><id>{$r->id}</id><name><![CDATA[{$r->title}]]></name></category>\n
EOF;
			$cat_ids[]=$r->id;
		}
		$xml.=<<<EOF
    </categories>
    <items>
EOF;

		$products_q=$this
		->products_query()
		->select("shop_products.*")
		
		// ->select("shop_products_categories_link.category_id")
		// ->join("shop_products_categories_link","shop_products_categories_link.product_id = shop_products.id")

		->select("brand.title AS brand_title")
		->join("categoryes AS brand","brand.type IN('shop-manufacturer') && brand.id = shop_products.brand_id","left")

		->where("shop_products.show",1)
		->get();

		// if($products_q===false){
		// 	print $products_q->last_query();exit;
		// }

		$this->d['products_res']=$products_q->result();
		
		foreach($this->d['products_res'] AS $i=>$r)
		{
			$cats_res=$this->db
			->select("shop_products_categories_link.category_id")
			->select("categoryes.parent_id")
			->join("categoryes","categoryes.id = shop_products_categories_link.category_id")
			->get_where("shop_products_categories_link",array(
				"shop_products_categories_link.product_id"=>$r->id
			))
			->result();

			// if(sizeof($cats_res)<1)continue;

			$level2cats=array();
			foreach($cats_res AS $r2)
			{
				if($r2->parent_id>0){
					$level2cats[]=$r2;
				}
			}

			// if(sizeof($level2cats)<1)continue;

			$category_id=current($level2cats)->category_id;

			$first_category_id=intval(current(explode(",",$r->category_ids)));
			$r->short_description=preg_replace("#<br[^>]*>#is","\n",$r->short_description);
			$r->short_description=preg_replace("#<[^>]*>#is","",$r->short_description);
			$r->short_description=preg_replace("# +#is"," ",$r->short_description);
			$r->short_description=trim($r->short_description);
			$r->link=base_url($this->link_product_view($r));
			$r->picture=base_url("/".$r->main_picture_file_path.$r->main_picture_file_name);
			$r->brand_title=htmlspecialchars($r->brand_title);
			$r->stock=$r->show==1?"На складе":"";
		$xml.=<<<EOF
<item>
    <id>{$r->id}</id>
    <categoryId>{$category_id}</categoryId>
    <code><![CDATA[{$r->code}]]></code>
    <vendor><![CDATA[{$r->brand_title}]]></vendor>
    <name><![CDATA[{$r->title}]]></name>
    <description><![CDATA[{$r->short_description}]]></description>
    <url>{$r->link}</url>
    <image>{$r->picture}</image>
    <priceRUAH>{$r->price}</priceRUAH>
    <priceRUSD></priceRUSD>
    <priceOUSD></priceOUSD>
    <stock>{$r->stock}</stock>
    <guarantee></guarantee>
</item>\n
EOF;
		}
		$xml.=<<<EOF
    </items>
</price> 
EOF;
	
		$xml=iconv("UTF-8","Windows-1251",$xml);
		header("Content-type: text/xml; charset=windows-1251");
		print $xml;
	}

	
}
?>