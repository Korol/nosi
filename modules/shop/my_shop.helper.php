<?php
class shopModuleHelper extends Cms_modules {
	var $delivery_methos=array(
		0=>"самовывоз",
		1=>"доставка по Киеву",
		2=>"отправка в другой город",
                3=>"международная доставка"
	);

	var $product_statuses=array(
		// 1=>array("резерв","#fd0a00"),
		// 2=>array("в работе","#24ff00"),
		// 3=>array("отправлено постащиком","#23ffff"),
		// 4=>array("доставка","#ffff00"),
		// 5=>array("оприходован","#666666"),
		// 6=>array("снято","#666666"),
		// 7=>array("отказ клиента","#999999"),
		// 8=>array("самовывоз","#e59311"),
		// 9=>array("нужно заказать","#2e2e2e"),
		// 10=>array("ожидает оплаты","#0000ff"),
		// 11=>array("возврат","#731a49"),
		// 12=>array("в офисе","#fd00ff")
		1=>array("резерв","#fe847f"),
		2=>array("в работе","#91ff7f"),
		3=>array("отправлено постащиком","#91ffff"),
		4=>array("доставка","#ffff7f"),
		5=>array("оприходован","#b2b2b2"),
		6=>array("снято","#b2b2b2"),
		7=>array("отказ клиента","#b2b2b2"),
		8=>array("самовывоз","#f2c988"),
		9=>array("нужно заказать","#969696"),
		10=>array("ожидает оплаты","#7f7fff"),
		11=>array("возврат","#b98ca4"),
		12=>array("в офисе","#fe7fff")
	);

	function __construct()
	{
		parent::__construct();

		// подключаем библиотеку для отрисовки интерфейса (формы, таблицы и тп.)
		$this->ci->load->library("fb");
		$this->load->helper('url');
		$this->load->helper('cms');
	}

	protected function get_product_statuses_options()
	{
		$d=array();
		foreach($this->product_statuses AS $id=>$r)
		{
			$d[$id]=$r[0];
		}

		return $d;
	}

	protected function order_success_link()
	{
		$structure_res=$this->db->get_where("url_structure",array(
			"module"=>"shop",
			"action"=>"order_success"
		))->row();

		return $structure_res->url;
	}

	public function order_status($status_name=NULL,$color=false)
	{
		$d=array(
			// "-1"=>array("Пустой заказ","gray"),
			// "submited"=>array("Не оплачен (оформлен)","orange"),
			// "paid"=>array("Оплачен","green"),
			// "canceled"=>array("Отменен","gray")

			"-1"=>array("Пустой заказ","gray"),
			"submited"=>array("Не оплачен (оформлен)","orange"),
			"paid"=>array("Оплачен","green"),
			"canceled"=>array("Отменен","gray"),


			"delivery"=>array("Доставка","orange"),
			"shipping"=>array("Отправка","orange"),
			"pending-payment"=>array("Ожидает оплаты","orange"),
			"client-refusal"=>array("Отказ клиента","red"),
			"issued"=>array("Выдано","green"),
			"posted"=>array("Отправлено","green"),
			"delivered"=>array("Доставлено","green")
		);

		if(is_null($status_name)){
			$statuses=array();
			foreach($d AS $key=>$r)
			{
				$statuses[$key]=$r[$color?1:0];
			}
			return $statuses;
		}

		return $d[$status_name][$color?1:0];
	}

	public function change_order_status($order_id,$status)
	{
		$user_id=intval($this->ci->session->userdata("user_id"));

		$this->db
		->query("UPDATE shop_orders SET status='".$status."',status_history=CONCAT(`status_history`,'".$status.":".mktime().":".$user_id."\n'),`date_update`='".mktime()."' WHERE `id`=".$order_id);

		return true;
	}

	protected function check_cart_exists()
	{
		$user_id=intval($this->ci->session->userdata("user_id"));
		$create_cart=false;
		if($user_id<1){
			// создаем корзину для гостя, используя сессии
			if(intval($this->ci->session->userdata("shop_cart_id"))<1){
				// создаем корзину
				$create_cart=true;
			}else{
				$this->d['cart_res']=$this->ci->db
				->get_where("shop_carts",array(
					"id"=>intval($this->ci->session->userdata("shop_cart_id"))
				))
				->row();

				if(intval($this->d['cart_res']->id)<1){
					$create_cart=true;
				}
			}
		}else{
			if(intval($this->ci->session->userdata("shop_cart_id"))>0){
				$this->d['cart_res']=$this->ci->db
				->get_where("shop_carts",array(
					"id"=>$this->ci->session->userdata("shop_cart_id")
				))
				->row();

				if(intval($this->d['cart_res']->id<1)){
					$create_cart=true;
				}else{
					$this->ci->session->set_userdata("shop_cart_id",$this->d['cart_res']->id);
				}
			}else{
				$create_cart=true;
			}
		}

		if($create_cart){
			$this->ci->db->insert("shop_carts",array(
				"user_id"=>$user_id,
				"date_add"=>mktime()
			));

			$new_cart_id=$this->ci->db->insert_id();

			$this->d['cart_res']=$this->ci->db
			->get_where("shop_carts",array(
				"id"=>$new_cart_id
			))
			->row();
			$this->ci->session->set_userdata("shop_cart_id",$new_cart_id);

			// удаляем старые корзины, если им больше недели
			$this->db
			->where("date_add <",mktime()-86400*7)
			->delete("shop_carts");
		}
	}

	public function calc_products_amount($products,$calc_hidden=false)
	{
		if(sizeof($products)==0)return false;

		$where=array();

		if(!$calc_hidden){
			$where['show']=1;
		}

		// Den
		foreach($products as $id=>$q){
			$id=reset(explode(":",$id));
			$_products[$id]=$q;
		}
		$products=$_products;
		// Den

		$products_res=$this->ci->db
		->select("id, price, currency, show")
		->where($where)
		->get_where("shop_products",array(
			"id IN(".implode(",",array_keys($products)).")"=>NULL
		))
		->result();
                
                // price by e-rates
                $cart_currency = 'usd';
                $e_rates_res = $this->ci->db->select('var_name, value')->get('e_rates')->result_array();
                $e_rates = array();
                if(!empty($e_rates_res)){
                    foreach ($e_rates_res as $e_rate){
                        $e_rates[$e_rate['var_name']] = $e_rate['value'];
                    }
                }
                
		$total_amount=0;
		foreach($products_res AS $r)
		{
			$products[$r->id]=intval($products[$r->id]);
			if($products[$r->id]<1)$products[$r->id]=1;

			$total_amount+= ($r->currency == $cart_currency) ? $r->price*$products[$r->id] : ceil(($r->price*$products[$r->id])*$e_rates[$r->currency . '_usd']);
		}

		return $total_amount;
	}

	public function save_cart($cart_r,$recalc_total_amount=true)
	{
		if($recalc_total_amount){
			$products=array();
			foreach($cart_r->products AS $product_id=>$r)
			{
				$products[$product_id]=$r->quantity;
			}

			$cart_r->total_amount=$this->calc_products_amount($products);
			$cart_r->total_amount_hmn=$this->price($cart_r->total_amount);
		}

		$cart=clone $cart_r;
		$cart->products=json_encode($cart->products);
		$cart_id=$cart->id;
		unset($cart->id,$cart->total_amount_hmn);
		$cart=(array)$cart;

		$this->ci->db
		->where("id",$cart_id)
		->update("shop_carts",$cart);
		
		return $cart_r;
	}

	public function get_cart($shop_cart_id=NULL)
	{
		if(is_null($shop_cart_id)){
			$shop_cart_id=intval($this->ci->session->userdata("shop_cart_id"));
		}

		$cart_res=$this->ci->db
		->get_where("shop_carts",array(
			"id"=>$shop_cart_id
		))
		->row();
	
		$cart_res->products=json_decode($cart_res->products);
		$ids=array();
		foreach($cart_res->products AS $product_id=>$product_r)
		{
			if(!isset($product_id->quantity))$product_id->quantity=1;
			$ids[$product_id]=$product_r->quantity;
		}
		// пересчитываем стоимость товаров каждый раз!
		$cart_res->total_amount=$this->calc_products_amount($ids);

		$cart_res->total_amount_hmn=$this->price($cart_res->total_amount);
		return $cart_res;

	}

	public function rm_product_photos($product_id)
	{
		$photo_res=$this->ci->db->get_where("uploads",array(
			
		))
		->result();

		$this->ci->load->library("uploads");
		$upload_id=$this->ci->uploads->remove(array(
			"component_type"=>"module",
			"component_name"=>"shop",
			"name"=>"product-photo",
			"extra_id"=>$product_id
		));

		return true;
	}

	public function add_product_photo($source_file_path,$product_id,$extra_color=NULL,$original_file_name=NULL)
	{
		r_mkdir("./uploads/shop/products/original/");
		
		$size=getimagesize($source_file_path);
		if(is_null($original_file_name) || !preg_match("#\.#is",$source_file_path)){
			if(preg_match("#jpeg|jpg#is",$size['mime'])){
				$original_file_name=$original_file_name."."."jpg";
			}elseif(preg_match("#png#is",$size['mime'])){
				$original_file_name=$original_file_name."."."png";
			}elseif(preg_match("#bmp#is",$size['mime'])){
				$original_file_name=$original_file_name."."."bmp";
			}elseif(preg_match("#gif#is",$size['mime'])){
				$original_file_name=$original_file_name."."."gif";
			}
		}

		$file_name=file_name("./uploads/shop/products/original/",$original_file_name,true);
		$dest_file_path="./uploads/shop/products/original/".$file_name;

		$this->ci->load->library("uploads");
		$upload_id=$this->ci->uploads->upload_file($source_file_path,$dest_file_path,array(
			"file_original_name"=>$original_file_name,
			"name"=>"product-photo",
			"component_type"=>"module",
			"component_name"=>"shop",
			"extra_id"=>$product_id,
			"proc_config_var_name"=>"mod_shop[images_options]"
		),array(
			"component_type"=>"module",
			"component_name"=>"shop",
			"name"=>"product-photo",
			"extra_id"=>$product_id,
			"extra_color"=>$extra_color
		));
	}

	public function add_product($d)
	{
		$d['show']=$d['show']==1?1:0;
		if(isset($d['availability']))unset($d['availability']);

		if(is_array($d['category_ids'])){
			$category_ids=array();
			$category_all_ids=array();
			$category_paths=array();
			if(sizeof($d['category_ids'])>0){
				if($d['category_ids']!==false){
					$category_ids=$d['category_ids'];
					foreach($d['category_ids'] AS $cat_id)
					{
						$category_paths[$cat_id]=$this->cat_parents_ids($cat_id);
						$category_all_ids=array_merge($category_all_ids,$category_paths[$cat_id]);
					}
				}
				$category_all_ids=array_unique($category_all_ids);
			}
			$d['category_paths']=json_encode($category_paths);
			$d['category_ids']=implode(",",$category_ids);
		}

		if(!isset($d['date_add'])){
			$d['date_add']=mktime();
		}
		if(!isset($d['date_public'])){
			$d['date_public']=mktime();
		}

		if(!isset($d['order'])){
			$d['order']=0;
			$d['order']=$this->db
			->count_all_results("shop_products");
			$d['order']++;
		}

		$this->db
		->insert("shop_products",$d);//var_dump($this->db->_error_message());

		$id=$this->db->insert_id();

		// удаляем все связи данного товара с категориями
		$this->db
		->where("product_id",$id)
		->delete("shop_products_categories_link");

		// создаем новые связи данного товара с категориями
		foreach($category_all_ids AS $category_id)
		{
			$category_id=intval($category_id);
			if($category_id<1)continue;

			$this->db
			->insert("shop_products_categories_link",array(
				"product_id"=>$id,
				"category_id"=>$category_id
			));
		}

		return $id;
	}

	public function update_product($d,$where)
	{
		if(isset($d['show'])){
			$d['show']=$d['show']==1?1:0;
		}
		if(isset($d['availability']))unset($d['availability']);

		if(is_array($d['category_ids'])){
			$category_ids=array();
			$category_all_ids=array();
			$category_paths=array();
			if(sizeof($d['category_ids'])>0){
				if($d['category_ids']!==false){
					$category_ids=$d['category_ids'];
					foreach($d['category_ids'] AS $cat_id)
					{
						$category_paths[$cat_id]=$this->cat_parents_ids($cat_id);
						$category_all_ids=array_merge($category_all_ids,$category_paths[$cat_id]);
					}
				}
				$category_all_ids=array_unique($category_all_ids);
			}
			$d['category_paths']=json_encode($category_paths);
			$d['category_ids']=implode(",",$category_ids);
		}

		$id=$where['id'];
		if(!isset($where['id'])){
			$product_res=$this->db
			->select("id")
			->get_where("shop_products",$where)
			->row();
			$id=$product_res->id;
		}

		if(!isset($d['date_edit'])){
			$d['date_edit']=mktime();
		}

		$this->db
		->where($where)
		->update("shop_products",$d);

		if(is_array($d['category_ids'])){
			// удаляем все связи данного товара с категориями
			$this->db
			->where("product_id",$id)
			->delete("shop_products_categories_link");

			// создаем новые связи данного товара с категориями
			foreach($category_all_ids AS $category_id)
			{
				$category_id=intval($category_id);
				if($category_id<1)continue;

				$this->db
				->insert("shop_products_categories_link",array(
					"product_id"=>$id,
					"category_id"=>$category_id
				));
			}
		}

		return true;
	}

	public function delete_product($where)
	{
		$products_res=$this->db
		->select("id")
		->get_where("shop_products",$where)
		->result();

		foreach($products_res AS $r)
		{
			$this->db
			->where(array(
				"product_id"=>$r->id,
				"type !="=>"deleted"
			))
			->delete("shop_import_report");

			$this->db
			->where("product_id",$r->id)
			->delete("shop_products_categories_link");

			$this->db
			->where("id",$r->id)
			->delete("shop_products");

			$this->db
			->where("product_id",$r->id)
			->delete("shop_suppliers_products_availability");

			$this->db
			->where("product_id",$r->id)
			->delete("shop_products_codes");

			$this->rm_product_photos($products_res->id);
		}

		return true;
	}

    /**
     * Рекурсивно получаем ID всех родителей Категории (до parent_id = 0)
     * @param int $child_id
     * @param array $data
     * @return array
     */
	function cat_parents_ids($child_id=0,&$data=array())
	{
		if($child_id==0)return array();

		$res=$this->db
		->select("id, parent_id")
		->get_where("categoryes",array(
			"type"=>"shop-category",
			"id"=>$child_id
		))
		->row();

		array_unshift($data,$res->id);
		
		if($res->parent_id>0){
			$this->cat_parents_ids($res->parent_id,$data);
		}

		return $data;
	}

	public function rcats_list($parent_id=0,$level=-1,&$rows=array())
	{
		$cats_res=$this->db
		->order_by("order")
		->get_where("categoryes",array(
			"type"=>"shop-category",
			"parent_id"=>$parent_id
		))
		->result();

		$level++;

		foreach($cats_res AS $r)
		{
			$r->level=$level;
			$r->title_level=trim(str_repeat("- - - ",$level)." ".$r->title);
			$rows[$r->id]=$r;

			$this->rcats_list($r->id,$level,$rows);
		}

		return $rows;
	}

	public function products_num_query()
	{
		$db=clone $this->db;

//		return $db
//		->select("shop_products.id")
//		->from("shop_products");
        return $db
		->select("COUNT(DISTINCT(shop_products.id)) as `numrows`", false)
		->from("shop_products");
	}

	public function products_query()
	{
		$db = clone $this->db;

		return $db
		->select("uploads.file_name AS main_picture_file_name, uploads.file_path AS main_picture_file_path, uploads.image_size AS main_picture_image_size")
		->join("uploads","uploads.extra_id = shop_products.id && uploads.order = 1 && uploads.name = 'product-photo'","left")

		->select("shop_products.*")
		->from("shop_products")
		->group_by("shop_products.id");
		// ->order_by("shop_products.order","DESC");
	}

	public function link_category(&$r)
	{
		$category_path=$_category_path=explode(",",$r->category_path);

		$r->cat_res=$this->db
		->select("url_structure.url")
		->get_where("url_structure",array(
			"url_structure.extra_name"=>"category_id",
			"url_structure.extra_id"=>$r->category_id
		))
		->row();

		if(!empty($r->cat_res->url)){
			return rtrim($r->cat_res->url,"/")."/";
			// return rtrim($r->cat_res->url,"/")."/".$r->alias."-".$r->id.".html";
		}else{
			// страница для данной категории в стуктуре не найден, попробуем найти общую страницу категорий (базовую)
			$r->cat_res=$this->db
			->select("url_structure.url")
			->get_where("url_structure",array(
				"module"=>"shop",
				"action"=>"category_base"
			))
			->row();
			return rtrim($r->cat_res->url,"/")."/".$r->name."-".$r->id."/";
		}

		return false;
	}

	public function link_manufacturer(&$r)
	{
		$r->manufacturer_res=$this->db
		->select("url_structure.url")
		->get_where("url_structure",array(
			"url_structure.extra_name"=>"manufacturer_id",
			"url_structure.extra_id"=>$r->id
		))
		->row();

		if(!empty($r->manufacturer_res->url)){
			return rtrim($r->manufacturer_res->url,"/")."/".$r->alias."-".$r->id.".html";
		}else{
			// страница для данной категории в стуктуре не найден, попробуем найти общую страницу категорий (базовую)
			$r->manufacturer_res=$this->db
			->select("url_structure.url")
			->get_where("url_structure",array(
				"module"=>"shop",
				"action"=>"category_base"
			))
			->row();
			return rtrim($r->manufacturer_res->url,"/")."/".$r->name."-".$r->id."m/";
		}

		return false;
	}

	public function product_price(&$r,$type=NULL,$currency=NULL)
	{
		if(!is_null($type)){
			$price=$r->{"price_".$type};
		}else{
			$price=$r->price;
		}

		if($price==0)return 0;

		return $this->price($price,$r->currency,$currency);
	}

	public function discount_price_calc($price,$calc_delivery=true,$_user_id=NULL)
	{
		$user_id=intval($this->ci->session->userdata("user_id"));
		if(isset($_user_id)){
			$user_id=$_user_id;
		}

		$price_before=$price;

		// проверяем есть ли скидка для этого пользователя
		$discount_res=$this->ci->db
		->get_where("shop_discounts",array(
			"type"=>"user",
			"extra_id"=>$user_id,
			"show"=>1
		))
		->row();
		$discount="user";
		if(intval($discount_res->id)<1){
			// скидки для этого пользователя нет, проверяем есть ли скидка для его группы
			$group_res=$this->ci->db
			->select("group_id")
			->get_where("users_groups",array(
				"users_groups.user_id"=>$user_id
			))->row();
			$group_id=intval($group_res->group_id);

			$discount_res=$this->ci->db
			->get_where("shop_discounts",array(
				"type"=>"user_group",
				"extra_id"=>$group_id,
				"show"=>1
			))
			->row();

			$discount="group";
		}

		$discounts=array();
		if(intval($discount_res->id)>0){
			if(preg_match("#([+-]?)([0-9.,]+)(%?)#is",$discount_res->discounts,$pregs)){
				$pregs[2]=$this->price_number_format($pregs[2]);

				if($pregs[3]=="%"){
					$p=($price/100)*$pregs[2];
				}else{
					$p=$pregs[2];
				}

				if($pregs[1]=="-"){
					$price-=$p;
				}else{
					$price+=$p;
				}

				$discounts[]=array(
					"id"=>$discount_res->id,
					"type"=>$discount,
					"discount"=>$pregs[1].$pregs[2].$pregs[3],
					"price_p"=>$p,
					"price_p_type"=>$pregs[1],
					"price_before"=>$price_before,
					"price_after"=>$price
				);
			}
		}

		// просчитываем доставку
		$delivery=0;
		if($price<500)$delivery=25;

		$price_total=$price;
		if($calc_delivery){
			$price_total+=$delivery;
		}

		return array(
			"delivery"=>$delivery,
			"delivery_hmn"=>$this->price($this->price_number_format($delivery)),
			"price_total"=>$this->price_number_format($price_total),
			"price_total_hmn"=>$this->price($this->price_number_format($price_total)),
			"price"=>$this->price_number_format($price),
			"price_hmn"=>$this->price($this->price_number_format($price)),
			"difference"=>$this->price_number_format($price_before-$price),
			"difference_hmn"=>$this->price($this->price_number_format($price_before-$price)),
			"discounts"=>$discounts
		);
	}

	public function price_number_format($number)
	{
		list($n1,$n2)=explode(".",$number);
		return $n1.".".substr($n2,0,2).substr("00",0,2-strlen($n2));
	}

//	public function price($price,$current_currency,$new_currency=NULL)
        public function price($price)
	{
		return $this->price_number_format($price)." $";
	}

	public function link_product_view(&$r)
	{
		// товар может быть привязан сразу к нескольким категориям, выбираем самую последнию, по ней будет построена основная ссылка 
		$last_category_id=end(explode(",",$r->category_ids));

		// страница для данной категории в стуктуре не найден, попробуем найти общую страницу категорий (базовую)
		// $r->manufacturer_res=$this->db
		// ->select("url_structure.url")
		// ->get_where("url_structure",array(
		// 	"module"=>"shop",
		// 	"action"=>"category_base"
		// ))
		// ->row();

		// return rtrim($r->manufacturer_res->url,"/")."/".$r->name."-".$r->id.".html";
		$r->cat_res=$this->db
		->select("url_structure.url")
		->get_where("url_structure",array(
			"url_structure.extra_name"=>"category_id",
			"url_structure.extra_id"=>$last_category_id
		))
		->row();
		if(!empty($r->cat_res->url)){
			return rtrim($r->cat_res->url,"/")."/".$r->name."-".$r->id.".html";
		}

		return false;
	}

	function add_to_cart_attrs(&$r,$quantity=0)
	{
		$html="";

		$cart=$this->get_cart();

		if(isset($cart->products->{$r->id})){
			$html=' href="#" onclick="shop_product_cart(this,'.$r->id.','.$quantity.',\'delete\'); return false;"';
		}else{
			$html=' href="#" onclick="shop_product_cart(this,'.$r->id.','.$quantity.',\'add\'); return false;"';
		}

		return $html;
	}

	protected function remove_category($id)
	{
		// получаем все ссылки на категории, для получения товаров
		$shop_products_categories_link_res=$this->db
		->where("category_id",$id)
		->group_by("product_id")
		->get("shop_products_categories_link")
		->result();

		$product_ids=array();
		foreach($shop_products_categories_link_res AS $r)
		{
			$product_ids[]=$r->product_id;
		}
		$product_ids=array_unique($product_ids);

		if(sizeof($product_ids)>0){
			$products_res=$this->db
			->select("id, category_ids, category_paths")
			->get_where("shop_products",array(
				"shop_products.id IN (".implode(",",$product_ids).")"=>NULL
			))
			->result();

			foreach($products_res AS $r)
			{
				$category_ids=explode(",",$r->category_ids);

				if(is_string($r->category_paths)){
					$r->category_paths=json_decode($r->category_paths);
				}

				foreach($r->category_paths AS $k=>$v)
				{
					if($k==$id){
						unset($r->category_paths->{$k});
						break;
					}

					if(($key=array_search($id,$r->category_paths->{$k}))!==false){
						unset($r->category_paths->{$k}[$key]);
					}
				}

				if(($key=array_search($id,$category_ids))!==false){
					unset($category_ids[$key]);
				}

				$this->db
				->where("id",$r->id)
				->update("shop_products",array(
					"category_ids"=>implode(",",$category_ids),
					"category_paths"=>json_encode($category_paths)
				));
			}
		}

		$this->db
		->where("category_id",$id)
		->delete("shop_products_categories_link");

		$this->db
		->where(array(
			"type"=>"shop-category",
			"id"=>$id
		))
		->delete("categoryes");

		return true;
	}

	public function repair_product($id)
	{
        // если вызов идет из shop.admin.php->repair()
        // то этот блок не выполняется, т.к. массив уже был создан
        // при восстановлении категорий ($this->repair_categories())
		if(!isset($this->ci->repair_product_cats)){
			$cats_res=$this->db->
			get_where("categoryes",array(
				"type"=>"shop-category"
			))
			->result();

			$this->ci->repair_product_cats=array();
			foreach($cats_res AS $r)
			{
				$r->cat_parents_ids=$this->cat_parents_ids($r->id);
				$this->ci->repair_product_cats[$r->id]=$r;
			}
		}

        // получаем ID всех категорий в каталоге на данный момент
		$cat_ids=array_keys($this->ci->repair_product_cats);

        // получаем ID, category_ids, category_paths
        // для текущего товара (передать их из shop.admin.php->repair()???)
		$product_res=$this->db
		->select("id, category_ids, category_paths")
		->get_where("shop_products",array(
			"shop_products.id"=>$id
		))
		->row();

        // получаем из строки массив категорий, которые указаны как связи
        // для текущего товара в поле category_ids
		$category_ids=explode(",",$product_res->category_ids);
        // проходим по массиву всех категорий каталога
		foreach($category_ids AS $k=>$cat_id)
		{
            // если такого ID категории нет в массиве ID каталога на данный момент
            // тогда удаляем этот ID из массива ID категорий, указанных как связи товара
            // в поле category_ids
			if(!in_array($cat_id,$cat_ids)){
				unset($category_ids[$k]);
			}
		}
        // собираем оставшиеся связи товара с категориями обратно в строку
		$product_res->category_ids=implode(",",$category_ids);

        // если прописан json-путь для товара по категориям
		if(is_string($product_res->category_paths)){
            // декодируем его в массив объектов
			$product_res->category_paths=json_decode($product_res->category_paths);
		}
        // проходим по этому массиву объектов
        // где ключем является ID категории, с которой связан товар
        // а значением является массив, указывающий родителей данной категории
		foreach($product_res->category_paths AS $k=>$sub_cats)
		{
            // проверяем ID категории на присутствие в массиве ID категорий
            // на данный момент
			if(!in_array($k,$cat_ids)){
                // если такого ID в массиве нет – удаляем его, и прерываем цикл
                // (??? почему не continue для проверки следующего элемента массива???)
				unset($product_res->category_paths->{$k});
				break;
			}
            // если проверка ID категории ничего не дала
            // проверяем массив родителей данной категории
			foreach($sub_cats AS $k2=>$cat_id)
			{
                // если ID нет в текущем массиве ID категорий
                // тогда удаляем его
				if(!in_array($cat_id,$cat_ids)){
					unset($product_res->category_paths->{$k}[$k2]);
				}
			}
		}

        // удаляем ID товара для использования данных
        // при обновлении
		unset($product_res->id);
        // проходим по массиву объектов ID категорий, связанных с товаром
		foreach($product_res->category_paths AS $cat_id=>$subcats)
		{
            // формируем новых родителей для категории, на основании текущей структуры каталога
			$product_res->category_paths->{$cat_id}=$this->ci->repair_product_cats[$cat_id]->cat_parents_ids;
		}

        // кодируем всё это в json
		$product_res->category_paths=json_encode($product_res->category_paths);

        // обновляем информацию о товаре
        // (новые данные в полях category_ids и category_paths)
        // на основании новой структуры каталога
		$this->db
		->where("id",$id)
		->update("shop_products",(array)$product_res);

		return false;
	}

    // восстановление категорий
	public function repair_categories()
	{
		if(!isset($this->ci->repair_product_cats)){
            // получаем все категории товаров в каталоге
			$cats_res=$this->db->
			get_where("categoryes",array(
				"type"=>"shop-category"
			))
			->result();

            // инициализируем новый массив категорий для восстановления
			$this->ci->repair_product_cats=array();
            // проходим по массиву категорий
			foreach($cats_res AS $r)
			{
                // Рекурсивно получаем ID всех родителей текущей Категории (до parent_id = 0)
				$r->cat_parents_ids=$this->cat_parents_ids($r->id);
                // заносим информацию о категории в новый массив восстановления, ключ = ID текущей категории
				$this->ci->repair_product_cats[$r->id]=$r;
			}
		}

        // получаем массив ID всех категорий из нового массива восстановления
        // массив ныне существующих категорий в Каталоге
		$cat_ids=array_keys($this->ci->repair_product_cats);

        // получаем фактически все ID категорий, которые в данный момент
        // используются для связи с товарами
		$shop_products_categories_link_res=$this->db
		->group_by("category_id")
		->get("shop_products_categories_link")
		->result();

        // массив удаления связей категорий с товарами
		$shop_products_categories_link_remove=array();
        // проходим по массиву категорий, использующихся для связей с товарами
		foreach($shop_products_categories_link_res AS $r)
		{
            // если ID такой категории нет среди ID в массиве ныне существующих категорий Каталога
            // тогда заносим этот ID в массив на удаление
			if(!in_array($r->category_id,$cat_ids)){
				$shop_products_categories_link_remove[]=$r->category_id;
			}
		}
        // оставляем только уникальные ID для удаления
		$shop_products_categories_link_remove=array_unique($shop_products_categories_link_remove);

        // если есть ID для удаления
		if(sizeof($shop_products_categories_link_remove)>0){
            // удаляем связи между товарами каталога и этими ID
			$this->db
			->where(array(
				"category_id IN(".implode(",",$shop_products_categories_link_remove).")"=>NULL
			))
			->delete("shop_products_categories_link");
		}
	}

	// метод которые публикует/скрывает товар в зависимости от различных факторов (его вообще нет в началии, или его нет у поставщиков и т.д.)
	public function check_product_public($product_id)
	{
		$res=$this->db
		->select("SUM(availability) AS sum")
		->get_where("shop_suppliers_products_availability",array(
			"product_id"=>$product_id
		))
		->row();

		$show=intval($res->sum)>0?1:0;

		$this->ci->db
		->where("id",$product_id)
		->update("shop_products",array(
			"show"=>$show
		));

		return $show;
	}

	public function calc_product_delivery_price($product_r)
	{
		if($product_r->price<500){
			return 25;
		}

		return 0;
	}
        
        /**
         * 
         * @param string $price - цена товара
         * @param string $price_currency - валюта товара (usd, eur, grn)
         * @param tystringpe $current_currency - текущая валюта для отображения цен на сайте (usd, eur, grn)
         * @param array $cc_erate - массив объектов с курсами валют для конвертации
         * @return string
         */
        public function convert_price_by_currency($price, $price_currency, $current_currency, $cc_erate) {
            if($price_currency == $current_currency){
                return $price; // валюты совпадают – просто возвращаем цену без изменений
            }
            else{
                $var_name = $price_currency . '_' . $current_currency; // формируем направление конвертации
                $erate = 1; // дефолтный курс = 1
                foreach ($cc_erate as $item) {
                    if($item->var_name == $var_name){
                        $erate = $item->value; // курс согласно направления конвертации
                        break;
                    }
                }
                return ceil($price * $erate); // возвращаем цену по курсу текущей валюты
            }
        }
        
        /**
         * 
         * @return array - массив с курсами валют по направлениям обмена
         */
        public function get_erates() {
            return $this->db->get('e_rates')->result();
        }
}
?>