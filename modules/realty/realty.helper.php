<?php
class realtyModuleHelper extends Cms_modules {
	var $delivery_methos=array(
		0=>"самовывоз",
		1=>"доставка по киеву",
		2=>"отправка в другой город"
	);

	function __construct()
	{
		parent::__construct();

		// подключаем библиотеку для отрисовки интерфейса (формы, таблицы и тп.)
		$this->ci->load->library("fb");
		$this->load->helper('url');
		$this->load->helper('cms');
	}

	protected function order_success_link()
	{
		$structure_res=$this->db->get_where("url_structure",array(
			"module"=>"realty",
			"action"=>"order_success"
		))->row();

		return $structure_res->url;
	}

	public function order_status($status_name=NULL,$color=false)
	{
		$d=array(
			"-1"=>array("Пустой заказ","gray"),
			"submited"=>array("Не оплачен (оформлен)","orange"),
			"paid"=>array("Оплачен","green"),
			"canceled"=>array("Отменен","gray")
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
		->query("UPDATE realty_orders SET status='".$status."',status_history=CONCAT(`status_history`,'".$status.":".mktime().":".$user_id."\n'),`date_update`='".mktime()."' WHERE `id`=".$order_id);

		return true;
	}

	protected function check_cart_exists()
	{
		$user_id=intval($this->ci->session->userdata("user_id"));
		$create_cart=false;
		if($user_id<1){
			// создаем корзину для гостя, используя сессии
			if(intval($this->ci->session->userdata("realty_cart_id"))<1){
				// создаем корзину
				$create_cart=true;
			}else{
				$this->d['cart_res']=$this->ci->db
				->get_where("realty_carts",array(
					"id"=>intval($this->ci->session->userdata("realty_cart_id"))
				))
				->row();

				if(intval($this->d['cart_res']->id)<1){
					$create_cart=true;
				}
			}
		}else{
			if(intval($this->ci->session->userdata("realty_cart_id"))>0){
				$this->d['cart_res']=$this->ci->db
				->get_where("realty_carts",array(
					"id"=>$this->ci->session->userdata("realty_cart_id")
				))
				->row();

				if(intval($this->d['cart_res']->id<1)){
					$create_cart=true;
				}else{
					$this->ci->session->set_userdata("realty_cart_id",$this->d['cart_res']->id);
				}
			}else{
				$create_cart=true;
			}
		}

		if($create_cart){
			$this->ci->db->insert("realty_carts",array(
				"user_id"=>$user_id,
				"date_add"=>mktime()
			));

			$new_cart_id=$this->ci->db->insert_id();

			$this->d['cart_res']=$this->ci->db
			->get_where("realty_carts",array(
				"id"=>$new_cart_id
			))
			->row();
			$this->ci->session->set_userdata("realty_cart_id",$new_cart_id);

			// удаляем старые корзины, если им больше недели
			$this->db
			->where("date_add <",mktime()-86400*7)
			->delete("realty_carts");
		}
	}

	public function calc_items_amount($items,$calc_hidden=false)
	{
		if(sizeof($items)==0)return false;

		$where=array();

		if(!$calc_hidden){
			$where['show']=1;
		}

		$items_res=$this->ci->db
		->select("id, price, currency, show")
		->where($where)
		->get_where("realty_items",array(
			"id IN(".implode(",",array_keys($items)).")"=>NULL
		))
		->result();

		$total_amount=0;
		foreach($items_res AS $r)
		{
			$items[$r->id]=intval($items[$r->id]);
			if($items[$r->id]<1)$items[$r->id]=1;

			$total_amount+=$r->price*$items[$r->id];
		}

		return $total_amount;
	}

	public function save_cart($cart_r,$recalc_total_amount=true)
	{
		if($recalc_total_amount){
			$items=array();
			foreach($cart_r->items AS $item_id=>$r)
			{
				$items[$item_id]=$r->quantity;
			}

			$cart_r->total_amount=$this->calc_items_amount($items);
			$cart_r->total_amount_hmn=$this->price($cart_r->total_amount);
		}

		$cart=clone $cart_r;
		$cart->items=json_encode($cart->items);
		$cart_id=$cart->id;

		unset($cart->id,$cart->total_amount_hmn);

		$cart=(array)$cart;

		$this->ci->db
		->where("id",$cart_id)
		->update("realty_carts",$cart);
		
		return $cart_r;
	}

	public function get_cart($realty_cart_id=NULL)
	{
		if(is_null($realty_cart_id)){
			$realty_cart_id=intval($this->ci->session->userdata("realty_cart_id"));
		}

		$cart_res=$this->ci->db
		->get_where("realty_carts",array(
			"id"=>$realty_cart_id
		))
		->row();

		$cart_res->items=json_decode($cart_res->items);
		$ids=array();
		foreach($cart_res->items AS $item_id=>$item_r)
		{
			if(!isset($item_id->quantity))$item_id->quantity=1;
			$ids[$item_id]=$item_r->quantity;
		}

		
		// пересчитываем стоимость товаров каждый раз!
		$cart_res->total_amount=$this->calc_items_amount($ids);

		$cart_res->total_amount_hmn=$this->price($cart_res->total_amount);

		return $cart_res;
	}

	public function rm_item_photos($item_id)
	{
		$photo_res=$this->ci->db->get_where("uploads",array(
			
		))
		->result();

		$this->ci->load->library("uploads");
		$upload_id=$this->ci->uploads->remove(array(
			"component_type"=>"module",
			"component_name"=>"realty",
			"name"=>"item-photo",
			"extra_id"=>$item_id
		));

		return true;
	}

	public function add_item_photo($source_file_path,$item_id,$original_file_name=NULL)
	{
		r_mkdir("./uploads/realty/items/original/");
		
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

		$file_name=file_name("./uploads/realty/items/original/",$original_file_name,true);
		$dest_file_path="./uploads/realty/items/original/".$file_name;

		$this->ci->load->library("uploads");
		$upload_id=$this->ci->uploads->upload_file($source_file_path,$dest_file_path,array(
			"file_original_name"=>$original_file_name,
			"name"=>"item-photo",
			"component_type"=>"module",
			"component_name"=>"realty",
			"extra_id"=>$item_id,
			"proc_config_var_name"=>"mod_realty[images_options]"
		),array(
			"component_type"=>"module",
			"component_name"=>"realty",
			"name"=>"item-photo",
			"extra_id"=>$item_id
		));
	}

	public function add_item($d)
	{
		$d['show']=$d['show']==1?1:0;
		$d['show_main']=$d['show_main']==1?1:0;

		if(!isset($d['date_add'])){
			$d['date_add']=mktime();
		}
		if(!isset($d['date_public'])){
			$d['date_public']=mktime();
		}

		if(!isset($d['order'])){
			$d['order']=0;
			$d['order']=$this->db
			->count_all_results("realty_items");
			$d['order']++;
		}

		$this->db
		->insert("realty_items",$d);

		$id=$this->db->insert_id();

		// удаляем все связи данного товара с категориями
		$this->db
		->where("item_id",$id)
		->delete("realty_items_categories_link");

		// создаем новые связи данного товара с категориями
		foreach($category_all_ids AS $category_id)
		{
			$category_id=intval($category_id);
			if($category_id<1)continue;

			$this->db
			->insert("realty_items_categories_link",array(
				"item_id"=>$id,
				"category_id"=>$category_id
			));
		}

		$this->db
		->where(array(
			"key"=>$_POST['key'],
			"component_type"=>"module",
			"component_name"=>"realty",
			"extra_id"=>0
		))
		->update("uploads",array(
			"key"=>"",
			"extra_id"=>$id
		));

		return $id;
	}

	public function update_item($d,$where)
	{
		if(isset($d['show'])){
			$d['show']=$d['show']==1?1:0;
		}
		if(isset($d['show'])){
			$d['show_main']=$d['show_main']==1?1:0;
		}

		$id=$where['id'];
		if(!isset($where['id'])){
			$item_res=$this->db
			->select("id")
			->get_where("realty_items",$where)
			->row();
			$id=$item_res->id;
		}

		if(!isset($d['date_edit'])){
			$d['date_edit']=mktime();
		}

		$this->db
		->where($where)
		->update("realty_items",$d);

		if(is_string($d['category_ids']) && !empty($d['category_ids'])){
			$d['category_ids']=explode(",",$d['category_ids']);
		}

		// удаляем все связи данного товара с категориями
		$this->db
		->where("item_id",$id)
		->delete("realty_items_categories_link");

		if(is_array($d['category_ids'])){

			// создаем новые связи данного товара с категориями
			foreach($d['category_ids'] AS $category_id)
			{
				$category_id=intval($category_id);
				if($category_id<1)continue;

				$this->db
				->insert("realty_items_categories_link",array(
					"item_id"=>$id,
					"category_id"=>$category_id
				));
			}
		}

		return true;
	}

	public function delete_item($where)
	{
		$items_res=$this->db
		->select("id")
		->get_where("realty_items",$where)
		->result();

		foreach($items_res AS $r)
		{
			$this->db
			->where(array(
				"item_id"=>$r->id,
				"type !="=>"deleted"
			))
			->delete("realty_import_report");

			$this->db
			->where("item_id",$r->id)
			->delete("realty_items_categories_link");

			$this->db
			->where("id",$r->id)
			->delete("realty_items");

			$this->db
			->where("item_id",$r->id)
			->delete("realty_suppliers_items_availability");

			$this->db
			->where("item_id",$r->id)
			->delete("realty_items_codes");

			$this->rm_item_photos($items_res->id);
		}

		return true;
	}

	function cat_parents_ids($child_id=0,&$data=array())
	{
		if($child_id==0)return array();

		$res=$this->db
		->select("id, parent_id")
		->get_where("categoryes",array(
			"type"=>"realty-category",
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
			"type"=>"realty-category",
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

	public function items_num_query()
	{
		$db=clone $this->db;

		return $db
		->select("realty_items.*")
		->from("realty_items");
	}

	public function items_query()
	{
		$db=clone $this->db;

		return $db
		->select("uploads.file_name AS main_picture_file_name, uploads.file_path AS main_picture_file_path, uploads.image_size AS main_picture_image_size")
		->join("uploads","uploads.extra_id = realty_items.id && uploads.order = 1 && uploads.name = 'item-photo'","left")

		->select("realty_items.*")
		->from("realty_items")
		->group_by("realty_items.id");
		// ->order_by("realty_items.order","DESC");
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
			return rtrim($r->cat_res->url,"/")."/".$r->alias."-".$r->id.".html";
		}else{
			// страница для данной категории в стуктуре не найден, попробуем найти общую страницу категорий (базовую)
			$r->cat_res=$this->db
			->select("url_structure.url")
			->get_where("url_structure",array(
				"module"=>"realty",
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
				"module"=>"realty",
				"action"=>"category_base"
			))
			->row();
			return rtrim($r->manufacturer_res->url,"/")."/".$r->name."-".$r->id."m/";
		}

		return false;
	}

	public function item_price(&$r,$type=NULL,$currency=NULL)
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
		->get_where("realty_discounts",array(
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
			->get_where("realty_discounts",array(
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

	public function price($price,$current_currency,$new_currency=NULL)
	{
		return "\$".$this->price_number_format($price);
		// return $this->price_number_format($price)." грн.";
	}

	public function link_item_view(&$r)
	{
		// товар может быть привязан сразу к нескольким категориям, выбираем самую последнию, по ней будет построена основная ссылка 
		// $last_category_id=end(explode(",",$r->category_ids));

		// страница для данной категории в стуктуре не найден, попробуем найти общую страницу категорий (базовую)
		$r->manufacturer_res=$this->db
		->select("url_structure.url")
		->get_where("url_structure",array(
			"module"=>"realty",
			"action"=>"category_base"
		))
		->row();

		return rtrim($r->manufacturer_res->url,"/")."/".$r->name."-".$r->id.".html";

		// $r->cat_res=$this->db
		// ->select("url_structure.url")
		// ->get_where("url_structure",array(
		// 	"url_structure.extra_name"=>"category_id",
		// 	"url_structure.extra_id"=>$last_category_id
		// ))
		// ->row();

		// if(empty($r->cat_res->url)){
		// 	return rtrim($r->cat_res->url,"/")."/";
		// }

		return false;
	}

	function add_to_cart_attrs(&$r,$quantity=0)
	{
		$html="";

		$cart=$this->get_cart();

		if(isset($cart->items->{$r->id})){
			$html=' href="#" onclick="realty_item_cart(this,'.$r->id.','.$quantity.',\'delete\'); return false;"';
		}else{
			$html=' href="#" onclick="realty_item_cart(this,'.$r->id.','.$quantity.',\'add\'); return false;"';
		}

		return $html;
	}

	protected function remove_category($id)
	{
		// получаем все ссылки на категории, для получения товаров
		$realty_items_categories_link_res=$this->db
		->where("category_id",$id)
		->group_by("item_id")
		->get("realty_items_categories_link")
		->result();

		$item_ids=array();
		foreach($realty_items_categories_link_res AS $r)
		{
			$item_ids[]=$r->item_id;
		}
		$item_ids=array_unique($item_ids);

		if(sizeof($item_ids)>0){
			$items_res=$this->db
			->select("id, category_ids, category_paths")
			->get_where("realty_items",array(
				"realty_items.id IN (".implode(",",$item_ids).")"=>NULL
			))
			->result();

			foreach($items_res AS $r)
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
				->update("realty_items",array(
					"category_ids"=>implode(",",$category_ids),
					"category_paths"=>json_encode($category_paths)
				));
			}
		}

		$this->db
		->where("category_id",$id)
		->delete("realty_items_categories_link");

		$this->db
		->where(array(
			"type"=>"realty-category",
			"id"=>$id
		))
		->delete("categoryes");

		return true;
	}

	public function repair_item($id)
	{
		if(!isset($this->ci->repair_item_cats)){
			$cats_res=$this->db->
			get_where("categoryes",array(
				"type"=>"realty-category"
			))
			->result();

			$this->ci->repair_item_cats=array();
			foreach($cats_res AS $r)
			{
				$r->cat_parents_ids=$this->cat_parents_ids($r->id);
				$this->ci->repair_item_cats[$r->id]=$r;
			}
		}

		$cat_ids=array_keys($this->ci->repair_item_cats);

		$item_res=$this->db
		->select("id, category_ids, category_paths")
		->get_where("realty_items",array(
			"realty_items.id"=>$id
		))
		->row();

		$category_ids=explode(",",$item_res->category_ids);
		foreach($category_ids AS $k=>$cat_id)
		{
			if(!in_array($cat_id,$cat_ids)){
				unset($category_ids[$k]);
			}
		}
		$item_res->category_ids=implode(",",$category_ids);

		if(is_string($item_res->category_paths)){
			$item_res->category_paths=json_decode($item_res->category_paths);
		}

		foreach($item_res->category_paths AS $k=>$sub_cats)
		{
			if(!in_array($k,$cat_ids)){
				unset($item_res->category_paths->{$k});
				break;
			}

			foreach($sub_cats AS $k2=>$cat_id)
			{
				if(!in_array($cat_id,$cat_ids)){
					unset($item_res->category_paths->{$k}[$k2]);
				}
			}
		}

		unset($item_res->id);
		foreach($item_res->category_paths AS $cat_id=>$subcats)
		{
			$item_res->category_paths->{$cat_id}=$this->ci->repair_item_cats[$cat_id]->cat_parents_ids;
		}

		$item_res->category_paths=json_encode($item_res->category_paths);

		$this->db
		->where("id",$id)
		->update("realty_items",(array)$item_res);

		return false;
	}

	public function repair_categories()
	{
		if(!isset($this->ci->repair_item_cats)){
			$cats_res=$this->db->
			get_where("categoryes",array(
				"type"=>"realty-category"
			))
			->result();

			$this->ci->repair_item_cats=array();
			foreach($cats_res AS $r)
			{
				$r->cat_parents_ids=$this->cat_parents_ids($r->id);
				$this->ci->repair_item_cats[$r->id]=$r;
			}
		}

		$cat_ids=array_keys($this->ci->repair_item_cats);

		$realty_items_categories_link_res=$this->db
		->group_by("category_id")
		->get("realty_items_categories_link")
		->result();

		$realty_items_categories_link_remove=array();
		foreach($realty_items_categories_link_res AS $r)
		{
			if(!in_array($r->category_id,$cat_ids)){
				$realty_items_categories_link_remove[]=$r->category_id;
			}
		}
		$realty_items_categories_link_remove=array_unique($realty_items_categories_link_remove);

		if(sizeof($realty_items_categories_link_remove)>0){
			$this->db
			->where(array(
				"category_id IN(".implode(",",$realty_items_categories_link_remove).")"=>NULL
			))
			->delete("realty_items_categories_link");
		}
	}

	// метод которые публикует/скрывает товар в зависимости от различных факторов (его вообще нет в началии, или его нет у поставщиков и т.д.)
	public function check_item_public($item_id)
	{
		$res=$this->db
		->select("SUM(availability) AS sum")
		->get_where("realty_suppliers_items_availability",array(
			"item_id"=>$item_id
		))
		->row();

		$show=intval($res->sum)>0?1:0;

		$this->ci->db
		->where("id",$item_id)
		->update("realty_items",array(
			"show"=>$show
		));

		return $show;
	}

	public function calc_item_delivery_price($item_r)
	{
		if($item_r->price<500){
			return 25;
		}

		return 0;
	}
}
?>