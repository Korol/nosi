<?php
include_once("./modules/shop/shop.helper.php");

class shopModule extends shopModuleHelper {
	function __construct()
	{
		parent::__construct();

		$this->load->library("categories");
        if($this->ci->input->ip_address() == '127.0.0.1')
            $this->ci->output->enable_profiler(true);
	}

	public function actual_products_sort($a,$b)
	{
		$order_field=current(current($_GET['order_by']));

		// if($order_field=="order_date_add"){
			
		// }else

		if($order_field=="code"){
			return strcmp($a->code,$b->code);
		}elseif($order_field=="product_status"){
			return strcmp($a->status,$b->status);
		}elseif($order_field=="brand"){
			return strcmp($a->brand_title,$b->brand_title);
		}elseif($order_field=="quantity"){
			if($a->quantity>$b->quantity){
				return 1;
			}elseif($a->quantity<$b->quantity){
				return -1;
			}
			return 0;
		}elseif($order_field=="original_price"){
			if($a->original_price>$b->original_price){
				return 1;
			}elseif($a->original_price<$b->original_price){
				return -1;
			}
			return 0;
		}elseif($order_field=="original_sum"){
			if($a->original_sum>$b->original_sum){
				return 1;
			}elseif($a->original_sum<$b->original_sum){
				return -1;
			}
			return 0;
		}elseif($order_field=="price"){
			if($a->price>$b->price){
				return 1;
			}elseif($a->price<$b->price){
				return -1;
			}
			return 0;
		}elseif($order_field=="sum"){
			$a_sum=$a->price*$a->quantity;
			$b_sum=$b->price*$b->quantity;
			if($a_sum>$b_sum){
				return 1;
			}elseif($a_sum<$b_sum){
				return -1;
			}
			return 0;
		}elseif($order_field=="profit"){
			$a_original_sum=price_double($a->original_price)*$a->quantity;
			$b_original_sum=price_double($b->original_price)*$b->quantity;
			$a_sum=($a->price*$a->quantity)-$a_original_sum;
			$b_sum=($b->price*$b->quantity)-$b_original_sum;

			if($a_sum>$b_sum){
				return 1;
			}elseif($a_sum<$b_sum){
				return -1;
			}
			return 0;
		}elseif($order_field=="client"){
			if($a->order_id>$b->order_id){
				return 1;
			}elseif($a->order_id<$b->order_id){
				return -1;
			}
			return 0;
			// return strcmp($a->name,$b->name);
		}else{
			return strcmp($this->orders[$a->order_id]->date_add,$this->orders[$b->order_id]->date_add);
		}
	}

	public function actual_products()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$where=array();

		// array("client-refusal","issued","posted","delivered","paid","canceled")

		if($this->input->get("order_by")===false){
			$_GET['order_by']['default']['asc']="order_date_add";
		}

		if($this->input->get("filter_start_date")!==false){
			$filter_start_date=strtotime($this->input->get("filter_start_date"));
			$where['shop_orders.date_add >=']=$filter_start_date;
		}


		if($this->input->get("filter_end_date")!==false){
			$filter_end_date=strtotime($this->input->get("filter_end_date"));
			$where['shop_orders.date_add <=']=$filter_end_date;
		}

		$this->d['orders_res']=$this->db
		->where($where)
		->where_not_in("status",array("-1","0","","client-refusal","issued","posted","delivered","paid","canceled"))
		// ->where_in("status",array("submited",""))
		// ->where("status","")
		->get("shop_orders")
		->result();

		$shop_suppliers_res=$this->db
		->get("shop_suppliers")
		->result();

		$this->orders=array();
		$availabilitys=array();
		$all_products=array();
		$orders_products=array();
		foreach($this->d['orders_res'] AS $r)
		{
			if(empty($r->basket))continue;
			$this->orders[$r->id]=$r;

			$r->basket=json_decode($r->basket);
			foreach($r->basket->products AS $product_id=>$product_r)
			{
				$product_r->id=$product_id;

				if(!isset($availability_res[$product_r->id])){
					$availability_res=$this->db
					->select("shop_suppliers.id, shop_suppliers.title")
					->select("shop_suppliers_products_availability.availability")
					->join("shop_suppliers","shop_suppliers.id = shop_suppliers_products_availability.supplier_id")
					->get_where("shop_suppliers_products_availability",array(
						"shop_suppliers_products_availability.product_id"=>$product_r->id,
						"shop_suppliers_products_availability.availability >"=>0
					))
					->result();

					$html=array();
					foreach($availability_res AS $availability_r)
					{
						if($availability_r->availability<1)continue;
						$html[]=$availability_r->title.'&nbsp;<sup>'.$availability_r->availability.'</sup>';
					}
					$availability_res['html']=implode(", ",$html);

					$availabilitys[$product_r->id]=&$availability_res;

					$orders_products[$product_r->id]=$r;
				}

				$product_r->availability_html=$availabilitys[$product_r->id]['html'];

				$product_r->order_id=$r->id;

				$all_products[]=$product_r;
			}
		}


		$products=array();
		if(sizeof($orders_products)>0){
			$products_res=$this->db
			->select("id, code, title")
			->where_in("id",array_keys($orders_products))
			->get("shop_products")
			->result();
			foreach($products_res AS $r)
			{
				$products[$r->id]=$r;
			}
		}

		if($this->input->post("save_products_sm")!==false){
			$product_status=$this->input->post("product_status");
			$product_supplier=$this->input->post("product_supplier");
			foreach($this->input->post("product_original_price") AS $ids=>$original_price)
			{
				list($order_id,$product_id)=explode(":",$ids);

				foreach($this->orders AS $oid=>$order_r)
				{
					if($oid!=$order_id)continue;

					foreach($order_r->basket->products AS $i=>$product_r)
					{
						if($product_r->id!=$product_id)continue;

						$this->orders[$oid]->basket->products->{$i}->original_price=$original_price;
						$this->orders[$oid]->basket->products->{$i}->status=$product_status[$oid.":".$product_r->id];
						$this->orders[$oid]->basket->products->{$i}->supplier=$product_supplier[$oid.":".$product_r->id];
					}
				}
			}

			foreach($this->orders AS $order_r)
			{
				$this->db
				->where("id",$order_r->id)
				->update("shop_orders",array(
					"basket"=>json_encode($order_r->basket),
				));
			}
			
			print 1;
			exit;
		}

		$manufacturers=array();
		$this->d['manufacturer_res']=$this->db->get_where("categoryes",array(
			"type"=>"shop-manufacturer"
		))
		->result();
		foreach($this->d['manufacturer_res'] AS $r)
		{
			$manufacturers[$r->id]=$r;
		}

		usort($all_products,array($this,"actual_products_sort"));

		if(current(array_keys(current($_GET['order_by'])))=="desc"){
			$all_products=array_reverse($all_products);
		}

		$get_product_statuses_options=$this->get_product_statuses_options();

		$rows=array();
		foreach($all_products AS $r)
		{
			$options=array('<option value="0">&nbsp; &nbsp; &nbsp;</option>');
			foreach($this->product_statuses AS $id=>$data)
			{
				$s=$id==intval($r->status)?' selected="selected"':'';
				$options[]='<option'.$s.' value="'.$id.'" data-color="'.$data[1].'">'.$data[0].'</option>';
			}

			$r->code=$products[$r->id]->code;
			$r->code_aliases=$products[$r->id]->code_aliases;
			$r->code_aliases=implode(", ",explode(",",$r->code_aliases));

			$shop_suppliers_select='';
			$shop_suppliers_select.='<select data-order-id="'.$r->order_id.'" data-product-id="'.$r->id.'" style="width:auto;" name="product_supplier'.$r->order_id.$r->id.'">';
			$shop_suppliers_select.='<option value="0"></option>';
			
			foreach($shop_suppliers_res AS $id=>$shop_suppliers_r)
			{
				$s=$shop_suppliers_r->id==intval($r->supplier)?' selected="selected"':'';
				$shop_suppliers_select.='<option'.$s.' value="'.$shop_suppliers_r->id.'">'.$shop_suppliers_r->title.'</option>';
			}
			$shop_suppliers_select.='</select>';

			$r->price=price_double($r->price);
			$r->original_price=price_double($r->original_price);

			$rows[]=array(
				'<a href="?m=shop&a=edit_order&id='.$r->order_id.'">'.date("d.m.Y H:i:s",$this->orders[$r->order_id]->date_add).'</a>',
				$r->code.(empty($r->code_aliases)?"":" - ".$r->code_aliases),
				'<select data-order-id="'.$r->order_id.'" data-product-id="'.$r->id.'" name="product_status'.$r->order_id.$r->id.'" style="width:auto;">'.
				implode("\n",$options).
				'</select>',
				// $r->status,
				$manufacturers[$r->brand_id]->title,
				$r->quantity,
				'<input data-price="'.$r->price.'" data-quantity="'.$r->quantity.'" data-order-id="'.$r->order_id.'" data-product-id="'.$r->id.'" name="product_original_price'.$r->order_id.$r->id.'" type="text" style="text-align:center; width:40px;" value="'.$r->original_price.'">',
				$this->price($r->original_price*$r->quantity),
				$r->availability_html.'<br />'.
				$shop_suppliers_select,
				$this->price($r->price),
				$this->price($r->price*$r->quantity),
				0,
				'<a href="'.$this->admin_url.'?m=shop&a=edit_order&id='.$r->order_id.'">№'.$r->order_id.'</a><br />'
				// .
				// $this->orders[$r->order_id]->name.'<br />'.
				// 'Тел.: '.$this->orders[$r->order_id]->phone.'<br />'
			);
		}

		$rows[]=array(
				"",
				"",
				"",
				"",
				0,
				"",
				0,
				"",
				0,
				0,
				0,
				""
			);

		$this->ci->fb->add("input:date",array(
			"label"=>"Дата с",
			"name"=>"filter_start_date",
			"parent"=>"filter_greed",
			"attr:style"=>"width:150px;",
			"append"=>"&nbsp;&nbsp;",
			"value"=>$this->input->get("filter_start_date")
		));

		$this->ci->fb->add("input:date",array(
			"label"=>"Дата по",
			"name"=>"filter_end_date",
			"parent"=>"filter_greed",
			"attr:style"=>"width:150px;",
			"append"=>"&nbsp;&nbsp;",
			"value"=>$this->input->get("filter_end_date")
		));

		$this->ci->fb->add("input:hidden",array(
			"name"=>"m",
			"value"=>"shop",
			"parent"=>"filter_greed"
		));

		$this->ci->fb->add("input:hidden",array(
			"name"=>"a",
			"value"=>"actual_products",
			"parent"=>"filter_greed"
		));

		$this->ci->fb->add("input:submit",array(
			"prepend"=>"<br />&nbsp;&nbsp;",
			"label"=>"Фильтровать",
			"name"=>"filter_sm",
			"parent"=>"filter_greed"
		));

		$this->ci->fb->add("greed:float",array(
			"name"=>"filter_greed",
			"parent"=>"table"
		));

		$this->ci->fb->add("table",array(
			"parent"=>"table",
			"head"=>array(
				array("дата заказа","order_by"=>"order_date_add"),
				array("артикул","order_by"=>"code"),
				array("статус","order_by"=>"product_status"),
				array("бренд","order_by"=>"brand"),
				array("кол.","order_by"=>"quantity"),
				array("цена","order_by"=>"original_price"),
				array("сумма","order_by"=>"original_sum"),
				"поставщик",
				array("оплата за единицу","order_by"=>"price"),
				array("сумма оплаты","order_by"=>"sum"),
				array("прибыль","order_by"=>"profit"),
				array("клиент","order_by"=>"client")
			),
			"rows"=>$rows
		));

		$this->ci->fb->add("form",array(
			"name"=>"table",
			"parent"=>"render",
			"method"=>"get"
		));

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("shop/actual_products",$this->d);
	}

	public function restore_from_backup()
	{
		$id=intval($this->input->get("id"));
		$backup_name=$this->input->get("backup_name");

		// if(file_exists($backup_name)){
			$this->db
			->where("id",$id)
			->update("shop_import",array(
				"status"=>"backup-start"
			));
		// }

		redirect($this->admin_url."?m=shop&a=import");
	}

	public function edit_supplier($edit=false)
	{
		$_GET['id']=(int)$_GET['id'];
		$this->add_supplier(true);
	}

	public function add_supplier($edit=false)
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=shop&a=suppliers");

		$this->buttons("form",$buttons);

		if($edit){
			$this->d['item_res']=$this->db
			->get_where("shop_suppliers",array("id"=>$_GET['id']))
			->row();

			if(is_string($this->d['item_res']->options)){
				$this->d['item_res']->options=json_decode($this->d['item_res']->options);
			}
		}

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true
		));

		$cols=array();
		$cols[-1]="--";
		foreach(range("a","z") as $letter)
		{
			$cols[]=strtoupper($letter);
		}

		$this->ci->fb->add("list:select",array(
			"label"=>"Артикул",
			"name"=>"code_col",
			"parent"=>"greed",
			"options"=>$cols
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Артикул + заголовок",
			"help"=>"артикул должен быть вначале строки, например: 'SP 66634 - тестовый товар'",
			"name"=>"code_title_col",
			"parent"=>"greed",
			"options"=>$cols
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Группа товаров",
			"name"=>"group_col",
			"parent"=>"greed",
			"options"=>$cols
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Наименование",
			"name"=>"title_col",
			"parent"=>"greed",
			"options"=>$cols
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Цена",
			"name"=>"price_col",
			"parent"=>"greed",
			"options"=>$cols
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Старая цена",
			"name"=>"price_old_col",
			"parent"=>"greed",
			"options"=>$cols
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Категория",
			"name"=>"category_col",
			"parent"=>"greed",
			"options"=>$cols,
			"help"=>"полное наименование категории или ID"
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Краткое описание",
			"name"=>"short_description_col",
			"parent"=>"greed",
			"options"=>$cols
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Полное описание",
			"name"=>"full_description_col",
			"parent"=>"greed",
			"options"=>$cols
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Опубликован",
			"name"=>"show_col",
			"parent"=>"greed",
			"options"=>$cols
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Наличие",
			"help"=>"колонка в которой указано сколько единиц в наличии",
			"name"=>"availability_col",
			"parent"=>"greed",
			"options"=>$cols
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

		if(!$edit && !$this->ci->fb->submit){
			$this->ci->fb->change("code_col",array("value"=>-1));
			$this->ci->fb->change("code_title_col",array("value"=>-1));
			$this->ci->fb->change("title_col",array("value"=>-1));
			$this->ci->fb->change("group_col",array("value"=>-1));
			$this->ci->fb->change("price_col",array("value"=>-1));
			$this->ci->fb->change("price_old_col",array("value"=>-1));
			$this->ci->fb->change("category_col",array("value"=>-1));
			$this->ci->fb->change("short_description_col",array("value"=>-1));
			$this->ci->fb->change("full_description_col",array("value"=>-1));
			$this->ci->fb->change("show_col",array("value"=>-1));
			$this->ci->fb->change("availability_col",array("value"=>-1));
		}

		if($edit && !$this->ci->fb->submit){
			$this->ci->fb->change("code_col",array("value"=>$this->d['item_res']->options->code_col));
			$this->ci->fb->change("code_title_col",array("value"=>$this->d['item_res']->options->code_title_col));
			$this->ci->fb->change("title_col",array("value"=>$this->d['item_res']->options->title_col));
			$this->ci->fb->change("group_col",array("value"=>$this->d['item_res']->options->group_col));
			$this->ci->fb->change("price_col",array("value"=>$this->d['item_res']->options->price_col));
			$this->ci->fb->change("price_old_col",array("value"=>$this->d['item_res']->options->price_old_col));
			$this->ci->fb->change("category_col",array("value"=>$this->d['item_res']->options->category_col));
			$this->ci->fb->change("short_description_col",array("value"=>$this->d['item_res']->options->short_description_col));
			$this->ci->fb->change("full_description_col",array("value"=>$this->d['item_res']->options->full_description_col));
			$this->ci->fb->change("show_col",array("value"=>$this->d['item_res']->options->show_col));
			$this->ci->fb->change("availability_col",array("value"=>$this->d['item_res']->options->availability_col));


			$this->ci->fb->change("parent_id",array("value"=>$this->d['item_res']->parent_id));
			$this->ci->fb->change("title",array("value"=>$this->d['item_res']->title));
			$this->ci->fb->change("description",array("value"=>$this->d['item_res']->description));
			if(isset($this->d['item_res']->options->disable_filter) && $this->d['item_res']->options->disable_filter==1){
				$this->ci->fb->change("disable_filter",array("attr:checked"=>true));
			}
		}

		if($this->ci->fb->submit){
			$this->d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($this->d['global_errors'])==0){
				$options=array(
					"code_col"=>$_POST['code_col'],
					"code_title_col"=>$_POST['code_title_col'],
					"group_col"=>$_POST['group_col'],
					"title_col"=>$_POST['title_col'],
					"price_col"=>$_POST['price_col'],
					"price_old_col"=>$_POST['price_old_col'],
					"category_col"=>$_POST['category_col'],
					"short_description_col"=>$_POST['short_description_col'],
					"full_description_col"=>$_POST['full_description_col'],
					"show_col"=>$_POST['show_col'],
					"availability_col"=>$_POST['availability_col']
				);

				if($edit){
					$this->db
					->where(array(
						"id"=>$_GET['id']
					))
					->update("shop_suppliers",array(
						"title"=>$this->input->post("title"),
						"options"=>json_encode($options)
					));
				}else{
					$this->db
					->insert("shop_suppliers",array(
						"title"=>$this->input->post("title"),
						"options"=>json_encode($options)
					));
				}

				redirect($this->admin_url."?m=shop&a=suppliers");
			}
		}

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("shop/add_supplier",$this->d);
	}

	public function suppliers()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->buttons("main",array(
			array("add","Добавить<br />поставщика",$this->admin_url."?m=shop&a=add_supplier")
		));

		$this->d['suppliers_res']=$this->db
		->get("shop_suppliers")
		->result();

		$rows=array();
		foreach($this->d['suppliers_res'] AS $r)
		{
			$products_num=$this->db->where(array(
				"supplier_id"=>$r->id
			))
			->count_all_results("shop_suppliers_products_availability");

			$products_not_availability_num=$this->db->where(array(
				"supplier_id"=>$r->id,
				"availability"=>0
			))
			->count_all_results("shop_suppliers_products_availability");

			$rows[]=array(
				$r->title,
				'Всего: '.$products_num.'<br />'.
				'<small style="color:red;">'.$products_not_availability_num.' - нет в наличии</small><br />'.
				'<small style="color:green;">'.($products_num-$products_not_availability_num).' - в наличии</small>',
				"buttons"=>array(
					array("pencil",$this->admin_url."?m=shop&a=edit_supplier&id=".$r->id),
					array("cross",$this->admin_url."?m=shop&a=rm_supplier&id=".$r->id)
				)
			);
		}

		$this->ci->fb->add("table",array(
			"parent"=>"table",
			"head"=>array(
				"Название",
				"Товары"
			),
			"rows"=>$rows
		));

		$this->d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("shop/suppliers",$this->d);
	}

	public function rm_supplier()
	{
		$id=(int)$this->input->get("id");
		$this->db
		->where("id",$id)
		->delete("shop_suppliers");

		$this->db
		->where("supplier_id",$id)
		->delete("shop_suppliers_products_availability");

		redirect($this->admin_url."?m=shop&a=suppliers");
	}

	function rebuild_cats_order($parent_id=0)
	{
		$res=$this->db
		->order_by("order")
		->get_where("categoryes",array(
			"type"=>"shop-category",
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
			
			$this->rebuild_cats_order($r->id);
			$i++;
		}
	}

	public function order_cat()
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
			"type"=>"shop-category",
			"order"=>$order=="up"?$res->order-1:$res->order+1,
			"parent_id"=>$res->parent_id
		))
		->update("categoryes",array(
			"order"=>$res->order
		));
		
		$this->db
		->where(array(
			"type"=>"shop-category",
			"id"=>$id,
			"parent_id"=>$res->parent_id
		))
		->update("categoryes",array(
			"order"=>$order=="up"?$res->order-1:$res->order+1
		));

		$this->rebuild_cats_order($res->parent_id);

		redirect($this->admin_url."?m=shop&a=cats");
	}

	public function enabled_cat()
	{
		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("categoryes",array(
			"show"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=shop&a=cats");
	}

	public function cats()
	{
            if(!$this->ci->load->check_page_access_new("shop_categories","shop","module")) return;
            
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->buttons("main",array(
			array("add","Добавить<br />категорию",$this->admin_url."?m=shop&a=add_cat")
		));

		$this->d['cats_res']=$this->rcats_list();

		$level_num=array();
		foreach($this->d['cats_res'] AS $r)
		{
			if(!isset($level_num[$r->level]))$level_num[$r->level]=0;
			$level_num[$r->level.":".$r->parent_id]++;
		}

		$rows=array();
		foreach($this->d['cats_res'] AS $r)
		{
			$rows[]=array(
				$r->title_level,
				"enabled"=>array($this->admin_url."?m=shop&a=enabled_cat&id=".$r->id."&enable=",$r->show),
				"order"=>array($this->admin_url."?m=shop&a=order_cat&id=".$r->id."&order=",$r->order,$level_num[$r->level.":".$r->parent_id]),
				"buttons"=>array(
					array("pencil",$this->admin_url."?m=shop&a=edit_cat&id=".$r->id),
					array("cross",$this->admin_url."?m=shop&a=rm_cat&id=".$r->id)
				)
			);
		}

		$this->ci->fb->add("table",array(
			"parent"=>"table",
			"head"=>array(
				"Название",
				"Опубликован",
				"Порядок"
			),
			"rows"=>$rows
		));

		$this->d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("shop/cats",$this->d);
	}

	private function cats_options_list($parent_id=0,$dir_only=true,$level=-1,&$data=array())
	{
		$res=$this->db
		->select("id, title, type")
		->get_where("categoryes",array(
			"type"=>"shop-category",
			"parent_id"=>$parent_id
		))
		->result();

		$level++;

		foreach($res AS $r)
		{
			$data[$r->id]=str_repeat("--",$level)." ".$r->title;
			$data[$r->id]=trim($data[$r->id]);
			$this->cats_options_list($r->id,$dir_only,$level,$data);
		}
		return $data;
	}

	public function edit_cat($edit=false)
	{
		$_GET['id']=(int)$_GET['id'];
		$this->add_cat(true);
	}

	public function add_cat($edit=false)
	{
            if(!$this->ci->load->check_page_access_new("shop_categories","shop","module")) return;
            
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=shop&a=cats");

		$this->buttons("form",$buttons);

		if($edit){
			$this->d['item_res']=$this->db
			->get_where("categoryes",array("categoryes.id"=>$_GET['id']))
			->row();

			if(is_string($this->d['item_res']->options)){
				$this->d['item_res']->options=json_decode($this->d['item_res']->options);
			}
		}

		$options=$this->cats_options_list();

		$options=array("0"=>"КОРНЕВАЯ КАТЕГОРИЯ")+$options;

		$this->ci->fb->add("list:select",array(
			"label"=>"Родительская категория",
			"name"=>"parent_id",
			"parent"=>"greed",
			"primary"=>true,
			"options"=>$options
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true
		));

		$this->ci->fb->add("textarea:editor",array(
			"label"=>"Описание",
			"name"=>"description",
			"id"=>"description",
			"parent"=>"greed",
			"attr:style"=>"height:200px; width:700px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true
		));

		$this->ci->fb->add("upload",array(
			"label"=>"Изображение",
			"component_type"=>"module",
			"component_name"=>"shop",
			"extra_type"=>"category_id",
			"upload_path"=>"./uploads/shop/category/",
			"key"=>$edit?"":(!empty($_POST['key'])?$_POST['key']:""),
			"extra_id"=>$edit?$_GET['id']:0,
			"name"=>"category_image",
			"parent"=>"greed",
			"dynamic"=>true,
			"proc_config_var_name"=>"mod_shop[categories_images_options]"
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"disable_filter",
			"label"=>"отключить фильтр на странице этой категории",
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

		if($edit && !$this->ci->fb->submit){
			$this->ci->fb->change("parent_id",array("value"=>$this->d['item_res']->parent_id));
			$this->ci->fb->change("title",array("value"=>$this->d['item_res']->title));
			$this->ci->fb->change("description",array("value"=>$this->d['item_res']->description));
			if(isset($this->d['item_res']->options->disable_filter) && $this->d['item_res']->options->disable_filter==1){
				$this->ci->fb->change("disable_filter",array("attr:checked"=>true));
			}
		}

		if($this->ci->fb->submit){
			$this->d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($this->d['global_errors'])==0){
				$name=$this->input->post("name");
				if($this->input->post("name")==""){
					$name=rewrite_alias($this->input->post("title"));
				}

				$options=array(
					"disable_filter"=>$this->input->post("disable_filter")==1?1:0
				);

				if($edit){
					$this->db
					->where(array(
						"id"=>$_GET['id']
					))
					->update("categoryes",array(
						"parent_id"=>(int)$this->input->post("parent_id"),
						"title"=>$this->input->post("title"),
						"description"=>$this->input->post("description"),
						"name"=>$name,
						"options"=>json_encode($options)
					));
                    ////////////
                    // если произошла смена родительской категории
                    // то мы запускаем обновление связей товаров с категориями, включая нового родителя
                    // + обновление иерархии в category_ids и category_paths всех продуктов, которые были связаны с этой категорией ранее
                    $new_parent_id = (int)$this->input->post("parent_id");
                    $old_parent_id = (int)$this->d['item_res']->parent_id;
                    if($new_parent_id !== $old_parent_id){
                        $this->repair_category_products((int)$_GET['id'], $old_parent_id, $new_parent_id);
                    }
                    ////////////
				}else{
					$this->db
					->insert("categoryes",array(
						"type"=>"shop-category",
						"parent_id"=>(int)$this->input->post("parent_id"),
						"title"=>$this->input->post("title"),
						"description"=>$this->input->post("description"),
						"name"=>$name,
						"date_add"=>mktime(),
						"show"=>1,
						"options"=>json_encode($options)
					));
				}

				$this->rebuild_cats_order($this->input->post("parent_id"));

				redirect($this->admin_url."?m=shop&a=cats");
			}
		}

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("shop/add_cat",$this->d);
	}
/////////////////
    /**
     * Перестройка связей товар-категория при изменении родительской категории для текущей категории
     * Также обновляются category_ids и category_paths для товаров, связанных с редактируемой категорией
     * @param int $cat_id - ID категории
     * @param int $old_parent_id - ID старого родителя категории
     * @return bool
     */
    public function repair_category_products($cat_id, $old_parent_id)
    {
        error_reporting(1);
        $this->load->helper('functions');
        // получаем ID всех товаров, связанных с отредактированной категорией
        $connected_products = $this->db
            ->select('shop_products_categories_link.product_id, shop_products.category_ids, shop_products.category_paths')
            ->join('shop_products', 'shop_products.id = shop_products_categories_link.product_id')
            ->get_where('shop_products_categories_link', array('category_id' => $cat_id))->result_array();
        if(empty($connected_products)) return false; // связанных товаров нет – делать нечего

        $connected_products = toolIndexArrayBy($connected_products, 'product_id');
        // удаляем связи товаров со старым родителем
        $this->db
            ->where('category_id', $old_parent_id)
            ->where_in('product_id', array_keys($connected_products))
            ->delete('shop_products_categories_link');
        // получаем ID всех категорий, с которыми остались связаны товары
        $connections = $this->db
            ->where_in('product_id', array_keys($connected_products))
            ->get('shop_products_categories_link')->result_array();
        if(empty($connections)) return false; // связанных товаров не осталось – больше делать нечего
        // группируем массив по ID товаров
        $connections = get_grouped_array($connections, 'product_id');


        // получаем все категории каталога
        $categories = $this->db->get_where('categoryes', array(
            'show' => 1,
            'type' => 'shop-category',
        ))->result_array();
        $categories = toolIndexArrayBy($categories, 'id');
        // получаем родителей редактируемой категории
        $parents = $this->repair_cat_parents_ids($categories, $cat_id);
        // удаляем из полученного массива ID текущей категории
        array_pop($parents);
        if(!empty($parents)){
            // удаляем возможные старые связи с новой родительской категорией
            $this->db
                ->where_in('product_id', array_keys($connected_products))
                ->where_in('category_id', $parents)
                ->delete('shop_products_categories_link');
            // добавляем новые связи товар-категория в таблицу shop_products_categories_link
            // формируем данные для вставки
            $link_sql = array();
            foreach($parents as $parent){
                foreach($connected_products as $p_id => $product){
                    $link_sql[] = "('" . $p_id . "', '" . $parent . "')";
                }
            }
            // добавляем новые связи в таблицу shop_products_categories_link
            if(!empty($link_sql)){
                $this->db->query("INSERT INTO `shop_products_categories_link` (`product_id`, `category_id`) VALUES " . implode(", ", $link_sql));
            }
        }

        // обновляем данные о категориях в товарах
        foreach($connected_products as $cp_key => $cp_val){
            // ID категории, по которой формируется ссылка на товар
            // этот ID всегда должен быть последним в строке category_ids
            $link_category_id = end(explode(',', $cp_val['category_ids']));
            // извлекаем из оставшихся связей ID категорий, кроме редактируемой
            $connected_cats_ids = array();
            foreach($connections[$cp_key] as $connection){
                if($connection['category_id'] != $link_category_id)
                    $connected_cats_ids[] = $connection['category_id'];
            }
            // мерджим эти ID с массивом ID родителей редактируемой категории
            $cat_ids = array_merge($connected_cats_ids, $parents);
            // оставляем только уникальные значения
            $cat_ids = array_unique($cat_ids);
            // и в конец добавляем $link_category_id = получили полный массив всех связей товара с категориями
            array_push($cat_ids, $link_category_id);
            // теперь формируем из этого полученного массива связей – массив иерархий путей для каждой категории
            $cat_paths = array();
            foreach($cat_ids as $one_cat_id){
                $cat_paths[$one_cat_id] = $this->repair_cat_parents_ids($categories, $one_cat_id);
            }
            // сохраняем изменения в БД
            $this->db->update('shop_products',
                array(
                    'category_ids' => implode(',', $cat_ids),
                    'category_paths' => json_encode($cat_paths),
                ),
                array(
                    'id' => $cp_key,
                ));
        }
        return false;
    }

    /**
     * Рекурсивно получаем ID всех родителей Категории (до parent_id = 0)
     * @param array $cats – массив всех категорий
     * @param int $child_id - ID категории, для которой нужно найти родителей
     * @param array $data - результирующий массив с ID родителей, включает в себя ID категории, для которой нужно найти родителей
     * @return array
     */
    function repair_cat_parents_ids($cats, $child_id=0,&$data=array())
    {
        if($child_id==0)return array();
        array_unshift($data,(int)$child_id);
        if($cats[$child_id]['parent_id'] > 0){
            $this->repair_cat_parents_ids($cats, $cats[$child_id]['parent_id'], $data);
        }
        return $data;
    }
////////////////
	function rm_cat()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$id=(int)$this->input->get("id");

		$child_ids=array_keys($this->rcats_list($id));
		$child_ids[]=$id;

		foreach($child_ids AS $id)
		{
			$this->remove_category($id);
		}
		// $this->db
		// ->where(array(
		// 	"type"=>"shop-category",
		// 	"id IN (".implode(",",$child_ids).")"=>NULL
		// ))
		// ->delete("categoryes");

		redirect($this->admin_url."?m=shop&a=cats");
	}

	public function products()
	{
            if(!$this->ci->load->check_page_access_new("shop_products","shop","module")) return;
		// $reees=$this->db->get_where("shop_products",array(
		// 	"supplier_id"=>2
		// ))->result();
		// foreach($reees AS $r)
		// {
		// 	$this->db->insert("shop_suppliers_products_availability",array(
		// 		"supplier_id"=>$r->supplier_id,
		// 		"product_id"=>$r->id,
		// 		"availability"=>$r->show
		// 	));
		// }
		// die("~~");
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->buttons("main",array(
			array("add","Добавить<br />товар",$this->admin_url."?m=shop&a=add_product_m")
		));

		$products_query=$this->db;
		$where=array();

		if($this->input->get("filter_keywords")){
			$filter_keywords=trim($this->input->get("filter_keywords"));
			$where["(shop_products.code='".$filter_keywords."' || shop_products.id='".$filter_keywords."' || shop_products.title LIKE '%".$filter_keywords."%' || shop_products.code LIKE '%".$filter_keywords."%' || shop_products.code_aliases LIKE '%".$filter_keywords."%')"]=NULL;


		}

		$filter_category_id=intval($this->input->get("filter_category_id"));
		if($filter_category_id>0){
			$products_query->join("shop_products_categories_link","shop_products_categories_link.product_id = shop_products.id && shop_products_categories_link.category_id='".$filter_category_id."'");
		}

		$filter_photo=intval($this->input->get("filter_photo"));
		if($filter_photo==1){
			$products_query->join("uploads","uploads.extra_id = shop_products.id && uploads.component_type = 'module' && uploads.component_name = 'shop' && uploads.order='1'");
		}elseif($filter_photo==2){
			$products_query->join("uploads","uploads.extra_id = shop_products.id && uploads.component_type = 'module' && uploads.component_name = 'shop' && uploads.order='1'","left");
			$where["uploads.id IS NULL"]=NULL;
		}

		$filter_show=$this->input->get("filter_show");
		if($filter_show==1 || $filter_show==2){
			$where["shop_products.show"]=$filter_show==1?1:0;
		}

		$filter_description=intval($this->input->get("filter_description"));
		if($filter_description==1){
			$where["shop_products.full_description !="]="";
		}elseif($filter_description==2){
			$where["shop_products.full_description"]="";
		}

		$filter_supplier=intval($this->input->get("filter_supplier"));
		if($filter_supplier>0){
			$products_query->join("shop_suppliers_products_availability","shop_suppliers_products_availability.product_id=shop_products.id && shop_suppliers_products_availability.supplier_id IN('".$filter_supplier."')");
			// $where["shop_products.supplier_id"]=$filter_supplier;
		}

		$filter_brand_id=intval($this->input->get("filter_brand_id"));
		if($filter_brand_id>0){
			$where['shop_products.brand_id']=$filter_brand_id;
		}

		if($this->input->post("select_all_from_table_sm")!==false){
			if(!is_array($this->input->post("exclud_ids"))){
				$_POST['exclud_ids']=array();
			}
			// доп. условие выборки для получения всех ID товаров (это для функции "выбрать все записи из таблицы, даже те которых нет на этой странице")
			$where["shop_products.id NOT IN('".implode("','",$this->input->post("exclud_ids"))."')"]=NULL;
		}

		$products_query2=clone $products_query;

		$this->d['products_res_num']=$products_query
		->where($where)
		->count_all_results("shop_products");

		$pagination=$this->ci->fb->pagination_init($this->d['products_res_num'],20,current_url_query(array("pg"=>NULL)),"pg");

		// сохранение порядка из таблицы
		if($_POST['table_order_sm']){
			foreach($_POST['order'] AS $id=>$order)
			{
				$this->db
				->where(array(
					"id"=>$id
				))
				->update("shop_products",array(
					"order"=>$this->d['products_res_num']-$order
				));
			}

			$this->rebuild_products_order();
		}

		$order_by_direction=current(array_keys($_GET['order_by']['products']));
		if($order_by_direction!="asc")$order_by_direction="desc";
		switch($_GET['order_by']['products'][$order_by_direction])
		{
			case'shop_products.category_id':
				$order_by="shop_products.category_ids";
			break;
			case'shop_products.title':
				$order_by="shop_products.title";
			break;
			case'shop_products.show':
				$order_by="shop_products.show";
			break;
			default:
			case'shop_products.order':
				$order_by="shop_products.order";
			break;
			case'shop_products.date_public':
				$order_by="shop_products.date_public";
			break;
		}

		if($this->input->get("filter_keywords")){
			$filter_keywords=trim($this->input->get("filter_keywords"));
			$order_by="code = '".$filter_keywords."' DESC, ".$order_by;
		}

		if($this->input->post("select_all_from_table_sm")!==false){
			// запрос по всем товарам для получения всех ID (это для выбора всех чекбоксов таблицы)
			$this->d['products_res']=$products_query2
			->select("shop_products.id")
			->get_where("shop_products",$where)
			->result();

			$rows=array();
			foreach($this->d['products_res'] AS $r)
			{
				$rows[]=intval($r->id);
			}
		}else{
			// нормальный запрос товаров, для отображения таблицы
			$this->d['products_res']=
			$products_query2
			->select("shop_products.*")
			->order_by($order_by." ".$order_by_direction)
			->limit((int)$pagination->per_page,(int)$pagination->cur_page)
			->get_where("shop_products",$where)
			->result();
		}
		
		if($this->input->post("select_all_from_table_sm")===false){
			$categories_res=$this->db
			->select("id, title, type")
			->get_where("categoryes",array(
				"type"=>"shop-category"
			))
			->result();
			$categories=array();
			foreach($categories_res AS $r)
			{
				$categories[$r->id]=$r;
			}
				
			$filters_uri="&filter_keywords=".$this->input->get("filter_keywords")."&filter_category_id=".$this->input->get("filter_category_id")."&filter_show=".$this->input->get("filter_show")."&filter_photo=".$this->input->get("filter_photo")."&filter_description=".$this->input->get("filter_description")."&filter_supplier=".$this->input->get("filter_supplier");

			$rows=array();
			foreach($this->d['products_res'] AS $r)
			{
				if(is_string($r->category_paths)){
					$r->category_paths=json_decode($r->category_paths);
				}

				$category_paths=array();
				foreach($r->category_paths AS $cat_id=>$path_ids)
				{
					$category_path=array();
					foreach($path_ids AS $cat_id)
					{
						$category_path[]=$categories[$cat_id]->title;
					}
					$category_paths[]=implode(" &gt; ",$category_path);
				}
				$category_paths=implode("<br />",$category_paths);

				$availability_res=$this->db
				->join("shop_suppliers","shop_suppliers.id = shop_suppliers_products_availability.supplier_id")
				->get_where("shop_suppliers_products_availability",array(
					"shop_suppliers_products_availability.product_id"=>$r->id,
					"shop_suppliers_products_availability.availability >"=>0
				))
				->result();

				$suppliers=array();
				foreach($availability_res AS $availability_r)
				{
					$suppliers[]=$availability_r->title." (".$availability_r->availability.")";
				}
				if(sizeof($suppliers)>0){
					$suppliers=implode(", ",$suppliers);
					$suppliers=" <sup style='white-space:nowrap;'><strong>{$suppliers}</strong></sup>";
				}else{
					$suppliers=" <sup style='white-space:nowrap;'>нет в наличии</sup>";
				}

				if($_GET['iframe_display']){
					$rows[]=array(
						'<div style="white-space:nowrap;"><a href="#" onclick="top.openAddProduct({id:\''.$r->id.'\',code:\''.$r->code.'\',title:\''.str_replace(array("'","\""),'&quot;',$r->title).'\'}); return false;">'.$r->title.'</a>'.$suppliers.'<br /><small>артикул: '.$r->code.'</small></div>',
						$category_paths,
						"buttons"=>array(
							array("pencil",$this->admin_url."?m=shop&a=edit_product&id=".$r->id.$filters_uri),
							array("cross",$this->admin_url."?m=shop&a=rm_product&id=".$r->id.$filters_uri)
						)
					);
				}else{
					$rows[]=array(
						"checkbox"=>array("name"=>"mass_id","value"=>$r->id,"select_all_from_table"=>true),
						'<div style="white-space:nowrap;"><a href="'.$this->admin_url.'?m=shop&a=edit_product&id='.$r->id.'">'.$r->title.'</a>'
						// .$suppliers
						// .'<br /><small title="'.implode(", ",array_merge(array($r->code),explode(",",$r->code_aliases))).'">артикул: '.$r->code.'<br /><a href="'.$this->admin_url.'?m=shop&a=import_report&product_id='.$r->id.'">изменения</a></small></div>'
						,
                                                $r->code,
						$category_paths,
						"enabled"=>array($this->admin_url."?m=shop&a=enabled_product&id=".$r->id."&enable=",$r->show),
						date("d.m.Y H:i:s",$r->date_public),
						"buttons"=>array(
							array("pencil",$this->admin_url."?m=shop&a=edit_product&id=".$r->id.$filters_uri),
							array("cross",$this->admin_url."?m=shop&a=rm_product&id=".$r->id.$filters_uri)
						)
					);
				}
			}
		}

		$this->ci->fb->add("input:button",array(
			"label"=>"Массовое действие",
			"hidden"=>true,
			"name"=>"mass_event",
			"id"=>"mass_event",
			"parent"=>"table",
			"attr:onclick"=>"catalogProductsMassEvent(this);",
			"attr:style"=>"float:right; margin-top:11px;"
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Ключевое слово",
			"name"=>"filter_keywords",
			"parent"=>"filter_greed",
			"attr:style"=>"width:150px;",
			"append"=>"&nbsp;&nbsp;",
			"value"=>$this->input->get("filter_keywords")
		));

		$options=array("-- любая --")+$this->cats_options_list();
		$this->ci->fb->add("list:select",array(
			"label"=>"Категория",
			"name"=>"filter_category_id",
			"parent"=>"filter_greed",
			"options"=>$options,
			"append"=>"&nbsp;&nbsp;",
			"value"=>$this->input->get("filter_category_id")
		));

		$options=array();
		$this->d['manufacturer_res']=$this->db->order_by('title', 'asc')->get_where("categoryes",array(
			"type"=>"shop-manufacturer"
		))
		->result();
		foreach($this->d['manufacturer_res'] AS $r)
		{
			$options[$r->id]=$r->title;
		}
		if(sizeof($options)>0){
			$options=array("-- любой --")+$options;
			$this->ci->fb->add("list:select",array(
				"label"=>"Бренд",
				"name"=>"filter_brand_id",
				"parent"=>"filter_greed",
				"options"=>$options,
				"append"=>"&nbsp;&nbsp;",
				"value"=>$this->input->get("filter_brand_id")
			));
		}

		$this->ci->fb->add("list:select",array(
			"label"=>"Фото",
			"name"=>"filter_photo",
			"parent"=>"filter_greed",
			"options"=>array(
			"",
			"есть",
			"нету"
			),
			"attr:style"=>"width:70px;",
			"append"=>"&nbsp;&nbsp;",
			"value"=>$this->input->get("filter_photo")
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Опубликован",
			"name"=>"filter_show",
			"parent"=>"filter_greed",
			"options"=>array(
			0=>"",
			1=>"да",
			2=>"нет"
			),
			"attr:style"=>"width:70px;",
			"append"=>"&nbsp;&nbsp;",
			"value"=>$this->input->get("filter_show")
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Описание",
			"name"=>"filter_description",
			"parent"=>"filter_greed",
			"options"=>array(
			"",
			"есть",
			"нету"
			),
			"attr:style"=>"width:70px;",
			"append"=>"&nbsp;&nbsp;",
			"value"=>$this->input->get("filter_description")
		));

		$options=array();

		$res=$this->db->get("shop_suppliers")
		->result();
		foreach($res AS $r)
		{
			$options[$r->id]=$r->title;
		}

		if(sizeof($options)>0){
			$options=array("-- все --")+$options;
			$this->ci->fb->add("list:select",array(
				"label"=>"Поставщик",
				"name"=>"filter_supplier",
				"parent"=>"filter_greed",
				"options"=>$options,
				"append"=>"&nbsp;&nbsp;",
				"value"=>$this->input->get("filter_supplier")
			));
		}

		$this->ci->fb->add("input:submit",array(
			"label"=>"Фильтровать",
			"name"=>"filter_sm",
			"parent"=>"filter_greed"
		));

		$this->ci->fb->add("html",array(
			"content"=>'<div style="padding-left:10px; padding-top:12px;">всего товаров: '.$this->d['products_res_num'].'</div>',
			"value"=>"shop",
			"parent"=>"filter_greed"
		));

		$this->ci->fb->add("input:hidden",array(
			"name"=>"m",
			"value"=>"shop",
			"parent"=>"filter_greed"
		));

		if($_GET['iframe_display']==1){
			$this->ci->fb->add("input:hidden",array(
				"name"=>"iframe_display",
				"value"=>$_GET['iframe_display'],
				"parent"=>"filter_greed"
			));
		}

		$this->ci->fb->add("input:hidden",array(
			"name"=>"a",
			"value"=>"products",
			"parent"=>"filter_greed"
		));

		$this->ci->fb->add("greed:float",array(
			"name"=>"filter_greed",
			"parent"=>"table"
		));

		if($_GET['iframe_display']){
			$this->ci->fb->add("table",array(
				"id"=>"products",
				"parent"=>"table",
				"head"=>array(
					array("Наименование","order_by"=>"shop_products.title"),
					"Категория"
				),
				"rows"=>$rows,
				"rows_num"=>$this->d['products_res_num'],
				"pagination"=>$pagination->create_links()
			));
		}else{
			$this->ci->fb->add("table",array(
				"id"=>"products",
				"parent"=>"table",
				"head"=>array(
					array("Наименование","order_by"=>"shop_products.title"),
                                        "Артикул",
					// array("Категория","order_by"=>"shop_products.category_id"),
					"Категория",
					array("Опубликован","order_by"=>"shop_products.show"),
					// array("Порядок","order_by"=>"shop_products.order"),
					array("Дата публикации","order_by"=>"shop_products.date_public")
				),
				"rows"=>$rows,
				"rows_num"=>$this->d['products_res_num'],
				"pagination"=>$pagination->create_links()
			));
		}

		$this->ci->fb->add("form",array(
			"name"=>"table",
			"parent"=>"render",
			"method"=>"get",
			// "disable_form_overflow"=>"Идет восстановление из резервной копии, дождитесь окончания..."
		));

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("shop/products",$this->d);
	}

	public function enabled_product()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("shop_products",array(
			"show"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=shop&a=products");
	}

	public function edit_product($edit=false)
	{
		$_GET['id']=(int)$_GET['id'];
		$this->add_product_m(true);
	}

	public function add_product_m($edit=false)
	{
            if(!$this->ci->load->check_page_access_new("shop_products","shop","module")) return;
            
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$buttons=array(); //var_dump($_POST);
		$buttons[]=array("save");
//		if($edit){
//			$buttons[]=array("apply");
//		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=shop&a=products");

		$this->buttons("form",$buttons);

		if($edit){
			$this->d['item_res']=$this->db
			->select("shop_products.*")
			->get_where("shop_products",array("shop_products.id"=>$_GET['id']))
			->row();
			$widgets_options_json=$this->d['item_res']->widgets_options;

			$this->d['shop_products_categories_link_res']=$this->db->get_where("shop_products_categories_link",array(
				"product_id"=>$_GET['id']
			))
			->result();
			$this->d['item_res']->category_ids=array();
			foreach($this->d['shop_products_categories_link_res'] AS $r)
			{
				$this->d['item_res']->category_ids[]=$r->category_id;
			}
		}

		$options=array();
		$this->d['shop_product_types_res']=$this->db->
		get_where("shop_product_types")
		->result();
		foreach($this->d['shop_product_types_res'] AS $r)
		{
			$options[$r->id]=$r->title;
		}

		if(sizeof($options)>0){
			$options=array("0"=>"-- Не выбран --")+$options;
			$this->ci->fb->add("list:select",array(
				"label"=>"Тип товара",
				"name"=>"type_id",
				"parent"=>"greed",
				"options"=>$options,
				"attr:onchange"=>"changeProductType(this.value);"
			));
		}

		$this->ci->fb->add("input:text",array(
			"label"=>"Наименование",
			"name"=>"title",
			"parent"=>"greed",
			"check" => array(
                "min_length" => 0
            )
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"URL (оставьте это поле пустым для автоматической транслитерации названия товара)",
			"name"=>"name",
			"parent"=>"greed"
		));

		$fields="";

		if($edit){
			$this->d['item_res']->code_aliases=trim($this->d['item_res']->code_aliases);
			if(!empty($this->d['item_res']->code_aliases)){
				foreach(explode(",",$this->d['item_res']->code_aliases) AS $code)
				{
					$code=trim($code);
					if(empty($code))continue;

					$fields.=<<<EOF
<div class="codeFieldRow">
<input type="text" name="code_alias[]" style="width:150px;" value="{$code}" />
&nbsp;<a style="position:relative; top:-4px;" title="удалить артикул" href="#" onclick="$(this).parents('div:eq(0)').remove(); return false;"><img src="/templates/default/admin/assets/icons/cross.png" alt="удалить артикул" /></a>
</div>
EOF;
				}
			}
		}

                if($edit){
                    $this->ci->fb->add("input:text",array(
                            "label"=>"Артикул",
                            "name"=>"code",
                            "parent"=>"greed",
                            "attr:style"=>"width:150px;",
                            // "append"=>'&nbsp;&nbsp;<a href="#" role="button" class="btn btn-mini btn-success" onclick="addCodeField(this); return false;" style="position:relative; top:-6px;">+ добавить артикул</a>'.$fields,
                    ));
                }


		$options=$this->cats_options_list();
		$this->ci->fb->add("list:select",array(
			"attr:multiple"=>"multiple",
			"attr:size"=>5,
			"label"=>"Категория",
			"name"=>"category_id[]",
			"parent"=>"greed",
			"options"=>$options
		));

		$options=array();
		$this->d['manufacturer_res']=$this->db->order_by('title', 'asc')->get_where("categoryes",array(
			"type"=>"shop-manufacturer"
		))
		->result();
		foreach($this->d['manufacturer_res'] AS $r)
		{
			$options[$r->id]=$r->title;
		}
		if(sizeof($options)>0){
			$this->ci->fb->add("list:select",array(
				"label"=>"Бренд",
				"name"=>"brand_id",
				"parent"=>"greed",
				"options"=>$options
			));
		}

		$this->ci->fb->add("list:select",array(
			"label"=>"Валюта",
			"name"=>"currency",
			"parent"=>"greed",
			"options"=> array('usd' => 'Доллар США', 'eur' => 'Евро', 'grn' => 'Гривны')
		));
			
		$this->ci->fb->add("input:text",array(
			"label"=>"Цена",
			"attr:style"=>"width:100px;",
			"name"=>"price",
			"parent"=>"greed",
                        "attr:onchange"=> ($edit) ? "changePrice('price');" : "",
            "check" => array(
                "min_length" => 0
            )
		));

		 $this->ci->fb->add("input:text",array(
		 	"label"=>"Старая цена",
		 	"attr:style"=>"width:100px;",
		 	"name"=>"price_old",
		 	"parent"=>"greed",
                        "attr:value"=>0,
                        "attr:onchange"=> ($edit) ? "changePrice('price_old');" : "",
		 ));

        $this->ci->fb->add("input:text",array(
            "label"=>"Скидка, %",
            "attr:style"=>"width:100px;",
            "name"=>"discount",
            "parent"=>"greed",
            "attr:value"=>0,
            "attr:onchange"=> ($edit) ? "changePrice('discount');" : "",
        ));

        if($edit){
            $action = $this->check_product_action($_GET['id']);
            if(!empty($action['percent'])){
                $this->ci->fb->add("input:text",array(
                    "label"=>"Скидка по Акции, %",
                    "attr:style"=>"width:100px;",
                    "name"=>"action_percent",
                    "parent"=>"greed",
                    "value" => $action['percent']
                ));
            }
        }

		$this->ci->fb->add("upload:editor",array(
			"label"=>"Прикрепить файлы",
			"component_type"=>"module",
			"component_name"=>"shop",
			"extra_type"=>"post_id",
			"key"=>$edit?"":(!empty($_POST['key'])?$_POST['key']:""),
			"extra_id"=>$edit?$_GET['id']:0,
			"name"=>"attach",
			"parent"=>"greed",
			"dynamic"=>true,
                        "new_remove"=>true,
                        "new_move"=>true,
			"upload_path"=>"./uploads/shop/products/attaches/"
		));

		// $this->ci->fb->add("textarea:editor",array(
		// 	"label"=>"Краткое описание",
		// 	"name"=>"short_description",
		// 	"id"=>"short_description",
		// 	"parent"=>"greed",
		// 	"attr:style"=>"height:100px; width:700px;",
		// 	"editor:pagebreak"=>false,
		// 	"editor:disabled_p"=>true
		// ));

		$this->ci->fb->add("textarea:editor",array(
			"label"=>"Полное описание",
			"name"=>"full_description",
			"id"=>"full_description",
			"parent"=>"greed",
			"attr:style"=>"height:400px; width:700px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true
		));

		$options = array(
			0=>'Не выводить',
			'16' => '16',
		    '18' => '18',
		    '20' => '20',
		    '22' => '22',
		);
		for($i=24;$i<60;$i++) $options[$i] = $i;

        $options['66'] = '66';
        $options['73'] = '73';

		$options = array_replace($options,array(
            'XS' => 'XS',
            'S' => 'S',
            'M' => 'M',
            'L' => 'L',
            'XL' => 'XL',
            '2XL' => '2XL',
            '3XL' => '3XL',
            '4XL' => '4XL',
            '5XL' => '5XL',
            '6XL' => '6XL', 
            '7XL' => '7XL', 
            '8XL' => '8XL',
            'UK8' => 'UK8',
            'UK10' => 'UK10',
            'UK12' => 'UK12',
            'UK14' => 'UK14',
		));

		$new_options = array(
            '80 см' => '80 см',
            '85 см' => '85 см',
            '90 см' => '90 см',
            '95 см' => '95 см',
            '100 см' => '100 см',
            '105 см' => '105 см',
            '110 см' => '110 см',
            '115 см' => '115 см',
            '120 см' => '120 см',
            '125 см' => '125 см',
            '130 см' => '130 см',
            '135 см' => '135 см',
            '140 см' => '140 см',
            '145 см' => '145 см',
            '150 см' => '150 см',
            '155 см' => '155 см',
            '160 см' => '160 см',
        );
        $options = array_replace($options, $new_options);

		$this->ci->fb->add("list:select",array(
			"attr:multiple"=>"multiple",
			"attr:size"=>5,
			"label"=>"Размеры",
			"name"=>"sizes[]",
			"parent"=>"greed",
			"options"=>$options
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"colors",
			"label"=>"Изображения товара как цвета",
			"parent"=>"greed"
		));

		$this->ci->fb->add("upload:editor",array(
			"label"=>"Фотографии товара <br/><em>максимальный размер файлов: до 100Кб<br/>разрешенные типы файлов: jpg, jpeg</em>",
			"component_type"=>"module",
			"component_name"=>"shop",
			"extra_type"=>"",
			"extra_id"=>$edit?$_GET['id']:0,
			"key"=>$edit?"":(!empty($_POST['key'])?$_POST['key']:""),
			"name"=>"product-photo[]",
            "id"=>"product-photo",
			"parent"=>"greed",
			"dynamic"=>true,
			"ordering"=>true,
			"thumbs"=>true,
                        "extra_color"=>true,
                        "new_remove"=>true,
                        "new_move"=>true,
			"proc_config_var_name"=>"mod_shop[images_options]",
			"upload_path"=>"./uploads/shop/products/original/",
			"max_size" => 100, // 100Kb
            "allowed_types" => "jpg|jpeg"
		));

		$this->d['shop_collection_products_res']=$this->db
		->join("categoryes","categoryes.id = shop_collection_products.collection_id")
		->get_where("shop_collection_products",array(
			"product_id"=>$_GET['id']
		))
		->result();
		
		if(sizeof($this->d['shop_collection_products_res'])>0){
			$html="";

			foreach($this->d['shop_collection_products_res'] AS $r)
			{
				$html.=<<<EOF
<div>
<label><input type="checkbox" name="collections[]" value="{$r->collection_id}" checked="checked" /> {$r->title}</label>
</div>
EOF;
			}

			$this->ci->fb->add("html",array(
				"label"=>"Этот товар находится в подбоках",
				"content"=>$html,
				"parent"=>"greed"
			));
		}

		$this->ci->fb->add("input:date",array(
			"label"=>"Дата публикации",
			"name"=>"date_public",
			"parent"=>"greed2"
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"show",
			"label"=>"опубликовать",
			"parent"=>"greed2"
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"frontpage",
			"label"=>"опубликовать на главной",
			"parent"=>"greed2"
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed",
			"parent"=>"tab1"
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"disallow_bot_index",
			"label"=>"запретить индексирование",
			"parent"=>"greed3"
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Meta title",
			"name"=>"meta_title",
			"parent"=>"greed3",
			"primary"=>true,
			"check"=>array(
				"max_length"=>255
			)
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Meta keywords",
			"name"=>"meta_keywords",
			"parent"=>"greed3",
			"primary"=>true,
			"check"=>array(
				"max_length"=>255
			)
		));

		$this->ci->fb->add("textarea",array(
			"label"=>"Meta description",
			"name"=>"meta_description",
			"parent"=>"greed3",
			"attr:style"=>"height:100px; width:300px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true
		));

		$widgets_options_json=urlencode($widgets_options_json);

		$widgets_cnotrol_html="";
		$widgets_cnotrol_html.=<<<EOF
<iframe id="widgets_list" frameborder="0" style="border:0 solid #CDCDCD;" width="100%" height="300" src="{$this->admin_url}?m=admin&a=components&t=widgets&iframe_display=1&widgets_options_json={$widgets_options_json}"></iframe>

<script>
$(document).ready(function(){
	$("form#form").submit(function(){
		buildWidgetsOptions();
	});
	$(".fixed-nav a[data-name='apply']").click(function(){
		buildWidgetsOptions();
	});
});

var widgetsOptions={};
function buildWidgetsOptions()
{
	var widgets_options_html='';

	$.each(widgetsOptions,function(widget_id,widget_options){
		$.each(widget_options,function(name,value){
			widgets_options_html+='<input type="hidden" name="widgets_options['+widget_id+']['+name+']" value="'+value+'">';
		});
	});
	
	$("form#form").append(widgets_options_html);
}
</script>
EOF;

		$this->ci->fb->add("html",array(
			"label"=>"Управление виджетами",
			"shop"=>$widgets_cnotrol_html,
			"parent"=>"greed3",
			"attr:style"=>"width:700px;"
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed2",
			"parent"=>"tab2"
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed3",
			"parent"=>"tab3"
		));

		foreach($this->d['shop_product_types_res'] AS $r)
		{
			$this->d['shop_product_types'][$r->id]=$this->db
			->get_where("shop_product_type_fields",array(
				"type_id"=>$r->id
			))
			->result();

			foreach($this->d['shop_product_types'][$r->id] AS $r2)
			{
				if(is_string($r2->params)){
					$r2->params=json_decode($r2->params);
				}

				switch($r2->field_type)
				{
					case'select':
						$options=array();
						foreach($r2->params->options AS $k=>$v)
						{
							$options[$k]=$v;
						}

						$this->ci->fb->add("list:select",array(
							"label"=>$r2->title,
							"name"=>"f_".$r2->id,
							"parent"=>"greed4",
							"options"=>$options,
							"hidden"=>true,
							"class"=>"hidden_fields hidden_additional_".$r->id
						));
					break;
					case'input:text':
						$this->ci->fb->add("input:text",array(
							"label"=>$r2->title,
							"name"=>"f_".$r2->id,
							"parent"=>"greed4",
							"hidden"=>true,
							"class"=>"hidden_fields hidden_additional_".$r->id
						));
					break;
					case'input:checkbox':
						$this->ci->fb->add("input:checkbox",array(
							"name"=>"f_".$r2->id,
							"label"=>$r2->title,
							"parent"=>"greed4",
							"hidden"=>true,
							"class"=>"hidden_fields hidden_additional_".$r->id
						));
					break;
					case'textarea':
						$this->ci->fb->add("textarea",array(
							"label"=>$r2->title,
							"name"=>"f_".$r2->id,
							"parent"=>"greed4",
							"attr:style"=>"height:100px; width:600px;",
							"hidden"=>true,
							"class"=>"hidden_fields hidden_additional_".$r->id
						));
					break;
				}
			}
		}

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed4",
			"parent"=>"tab4"
		));

		$tabs=array(
			"tab1"=>"Основное",
			"tab2"=>"Настройки публикации",
			"tab3"=>"Другие настройки"
		);

		if(sizeof($this->d['shop_product_types_res'])>0){
			$tabs['tab4']="Дополнительные поля";
		}

		$this->ci->fb->add("tabs",array(
			"tabs"=>$tabs,
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

		if($edit && !$this->ci->fb->submit){
			// $codes=array();
			// if(!empty($this->d['item_res']->code)){
			// 	$codes[]=$this->d['item_res']->code;
			// }

			// // получаем остальные артикулы товары
			// $shop_products_codes_res=$this->db
			// ->get_where("shop_products_codes",array(
			// 	"product_id"=>$this->d['item_res']->id
			// ))
			// ->result();
			// foreach($shop_products_codes_res AS $r)
			// {
			// 	$r->code=trim($r->code);
			// 	if(empty($r->code))continue;
			// 	$codes[]=$r->code;
			// }
			// $codes=array_unique($codes);

			// $code=implode(", ",$codes);
//
			$this->ci->fb->change("type_id",array("value"=>$this->d['item_res']->type_id));
			$this->ci->fb->change("title",array("value"=>$this->d['item_res']->title));
			$this->ci->fb->change("name",array("value"=>$this->d['item_res']->name));
			$this->ci->fb->change("code",array("value"=>$this->d['item_res']->code));
			$this->ci->fb->change("category_id[]",array("value"=>$this->d['item_res']->category_ids));
			$this->ci->fb->change("sizes[]",array("value"=>explode(',',$this->d['item_res']->sizes)));
			$this->ci->fb->change("brand_id",array("value"=>explode(",",$this->d['item_res']->brand_id)));
			$this->ci->fb->change("price",array("value"=>$this->d['item_res']->price));
			$this->ci->fb->change("currency",array("value"=>$this->d['item_res']->currency));// валюта 
			$this->ci->fb->change("price_old",array("value"=>$this->d['item_res']->price_old));
			$this->ci->fb->change("discount",array("value"=>$this->d['item_res']->discount));
			$this->ci->fb->change("short_description",array("value"=>$this->d['item_res']->short_description));
			$this->ci->fb->change("full_description",array("value"=>$this->d['item_res']->full_description));
			$this->ci->fb->change("stock_ids[]",array("value"=>explode(",",$this->d['item_res']->stock_ids)));
			$this->ci->fb->change("meta_title",array("value"=>$this->d['item_res']->meta_title));
			$this->ci->fb->change("meta_keywords",array("value"=>$this->d['item_res']->meta_keywords));
			$this->ci->fb->change("meta_description",array("value"=>$this->d['item_res']->meta_description));
			$this->ci->fb->change("date_public",array("value"=>$this->d['item_res']->date_public>0?date("d.m.Y H:i:s",$this->d['item_res']->date_public):""));
			if($this->d['item_res']->show==1){
				$this->ci->fb->change("show",array("attr:checked"=>true));
			}
			if($this->d['item_res']->frontpage==1){
				$this->ci->fb->change("frontpage",array("attr:checked"=>true));
			}
			if($this->d['item_res']->archive==1){
				$this->ci->fb->change("archive",array("attr:checked"=>true));
			}
			if($this->d['item_res']->disallow_bot_index==1){
				$this->ci->fb->change("disallow_bot_index",array("attr:checked"=>true));
			}
			if($this->d['item_res']->colors==1){
				$this->ci->fb->change("colors",array("attr:checked"=>true));
			}

			foreach($this->d['shop_product_types_res'] AS $r)
			{
				foreach($this->d['shop_product_types'][$r->id] AS $r2)
				{
					if($r2->field_type=="input:checkbox"){
						if($this->d['item_res']->{"f_".$r2->id}==1){
							$this->ci->fb->change("f_".$r2->id,array("attr:checked"=>true));
						}
					}else{
						$this->ci->fb->change("f_".$r2->id,array("value"=>$this->d['item_res']->{"f_".$r2->id}));
					}
				}
			}
		}

		if(!$edit && !$this->ci->fb->submit){
			$this->ci->fb->change("show",array("attr:checked"=>"true"));
			$this->ci->fb->change("frontpage",array("attr:checked"=>"true"));
			$this->ci->fb->change("date_public",array("value"=>date("d.m.Y H:i:s")));
		}

		if($this->ci->fb->submit){

			$this->d['global_errors']=$this->ci->fb->errors_list();//var_dump($this->ci->fb->errors_list());

			$order=0;
			$order=$this->db
			->count_all_results("shop_products");
			$order++;

			if(sizeof($this->d['global_errors'])==0){
				$category_ids=array();
				$category_all_ids=array();
				$category_paths=array();
				if($this->input->post("category_id")!==false){
					$category_ids=$this->input->post("category_id");
					foreach($this->input->post("category_id") AS $cat_id)
					{
						$category_paths[$cat_id]=$this->cat_parents_ids($cat_id);
						$category_all_ids=array_merge($category_all_ids,$category_paths[$cat_id]);
					}
				}
				$category_all_ids=array_unique($category_all_ids);

				$sizes=$this->input->post("sizes");

				$name=$this->input->post("name");
				if($this->input->post("name")==""){
//					$name=rewrite_alias($this->input->post("title"));
                                    $name = $this->url_slug($this->input->post("title"), array('transliterate' => true));
				}

				$date_public=intval(strtotime($this->input->post("date_public")));

				if($this->input->post("widgets_options")!==false){
					$widgets_options=$this->input->post("widgets_options");
				}

				$this->d['additional_fields']=array();
				foreach($this->d['shop_product_types_res'] AS $r)
				{
					foreach($this->d['shop_product_types'][$r->id] AS $r2)
					{
						$this->d['additional_fields']['f_'.$r2->id]=$val=$this->input->post("f_".$r2->id);
					}
				}

				$codes=array();
				if($this->input->post("code_alias")!==false && sizeof($this->input->post("code_alias"))>0){
					foreach($this->input->post("code_alias") AS $r)
					{
						$r=trim($r);
						if(empty($r))continue;
						$codes[]=$r;
					}
					$codes=array_unique($codes);
				}

				if($edit){
					$this->d['update']=array(
						"type_id"=>$this->input->post("type_id"),
						"title"=>$this->input->post("title"),
						"name"=>$name,
						"code"=>$this->input->post("code"),
						"code_aliases"=>implode(",",$codes),
						"category_ids"=>implode(",",$category_ids),
						"category_paths"=>json_encode($category_paths),
						"brand_id"=>$this->input->post("brand_id"),
						"price"=>price_double($this->input->post("price")),
						"currency"=>$this->input->post("currency"),
						"price_old"=>price_double($this->input->post("price_old")),
						"discount"=>price_double($this->input->post("discount")),
						"short_description"=>$this->input->post("short_description"),
						"full_description"=>$this->input->post("full_description"),
						"colors"=>$this->input->post("colors")==1?1:0,
						"sizes"=>implode(",",$sizes),
						// "stock_ids"=>$stock_ids,
						"disallow_bot_index"=>$this->input->post("disallow_bot_index")==1?1:0,
						"meta_title"=>$this->input->post("meta_title"),
						"meta_keywords"=>$this->input->post("meta_keywords"),
						"meta_description"=>$this->input->post("meta_description"),
						"show"=>$this->input->post("show")==1?1:0,
						"frontpage"=>$this->input->post("frontpage")==1?1:0,
						"date_edit"=>mktime(),
						"date_public"=>$date_public,
						"order"=>$order,
						"widgets_options"=>json_encode($widgets_options)
					);

					$this->d['update']=array_merge($this->d['update'],$this->d['additional_fields']);

					$this->update_product($this->d['update'],array(
						"id"=>$_GET['id']
					));

					$id=$_GET['id'];

					// обновляем акционную скидку – если она есть у товара
                    if(!empty($action['percent'])){
                        $post_percent = $this->input->post('action_percent', true);
                        if(empty($post_percent)){
                            // удаляем товар из акции
                            $this->db->delete(
                                'action_product',
                                array(
                                    'action_id' => $action['id'],
                                    'product_id' => $_GET['id'],
                                )
                            );
                        }
                        else{
                            // если новый процент отличается от старого – обновляем
                            if(((int)$post_percent != (int)$action['percent'])
                                && ((int)$post_percent < 100))
                            {
                                $this->db->update(
                                    'action_product',
                                    array('percent' => $post_percent),
                                    array(
                                        'action_id' => $action['action_info']['id'],
                                        'product_id' => $_GET['id'],
                                    )
                                );
                            }
                        }
                    }

				}else{
					
					$this->d['insert']=array(
						"type_id"=>$this->input->post("type_id"),
						"title"=>$this->input->post("title"),
						"name"=>$name,
                                                "supplier_id"=>0,
                                                "code"=>'', // Артикул теперь назначается автоматически, как ID нового товара (см. код ниже)
						"code_aliases"=>implode(",",$codes),
						"category_ids"=>implode(",",$category_ids),
						"category_paths"=>json_encode($category_paths),
						"brand_id"=>$this->input->post("brand_id"),
						"currency"=>$this->input->post("currency"),
						"price"=>price_double($this->input->post("price")),
						"price_old"=>price_double($this->input->post("price_old")),
						"discount"=>price_double($this->input->post("discount")),
						"short_description"=>$this->input->post("short_description"),
						"full_description"=>$this->input->post("full_description"),
						"colors"=>$this->input->post("colors")==1?1:0,
						"sizes"=>implode(",",$sizes),
						"stock_ids"=>'',
						"disallow_bot_index"=>$this->input->post("disallow_bot_index")==1?1:0,
						"meta_title"=>$this->input->post("meta_title"),
						"meta_keywords"=>$this->input->post("meta_keywords"),
						"meta_description"=>$this->input->post("meta_description"),
						"show"=>$this->input->post("show")==1?1:0,
						"frontpage"=>$this->input->post("frontpage")==1?1:0,
						"date_add"=>mktime(),
						"date_public"=>$date_public,
						"order"=>$order,
						"widgets_options"=>json_encode($widgets_options)
					);

					$this->d['insert']=array_merge($this->d['insert'],$this->d['additional_fields']);

					$id=$this->add_product($this->d['insert']);
                                        
                                        // 2015-12-14 - назначаем товару Артикул (`code`) == ID нового товара
                                        $this->db->where('id', $id)->update('shop_products', array('code' => $id));
                                        
                                        // фиксируем, кто добавил товар – для статистики
                                        $user_info = $this->ci->ion_auth->user()->row();
                                        $this->db->insert('users_added_products', array(
                                            'user_id' => $user_info->id,
                                            'product_id' => $id,
                                            'added' => date('Y-m-d H:i:s')
                                        ));
				}

				// создаем синонимы артикулов
				$this->db
				->where("product_id",$id)
				->delete("shop_products_codes");
				if(sizeof($codes)>0){
					foreach($codes AS $r)
					{
						$this->db
						->insert("shop_products_codes",array(
							"product_id"=>$id,
							"code"=>$r
						));
					}
				}

				if(sizeof($this->d['shop_collection_products_res'])>0){
					foreach($this->d['shop_collection_products_res'] AS $r)
					{
						if(in_array($r->id,$_POST['collections']))continue;

						$this->db
						->where(array(
							"collection_id"=>$r->id,
							"product_id"=>$id
						))
						->delete("shop_collection_products");
					}
				}

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

				$this->db
				->where(array(
					"key"=>$_POST['key'],
					"component_type"=>"module",
					"component_name"=>"shop",
					"extra_id"=>0
				))
				->update("uploads",array(
					"key"=>"",
					"extra_id"=>$id
				));
                                
                                // обновляем информацию о картинках-цветах товара
                                $uploads = $this->db->select('id')->get_where('uploads', array('extra_id' => $id))->result_array(); // все загрузки товара
                                $extra_color = $this->input->post('extra_color');
                                $extra_color = (!empty($extra_color)) ? $extra_color : array(0); // фотографии, отмеченные в качестве цветов товара
                                if(!empty($uploads)){
                                    foreach ($uploads as $item){
                                        $value = (in_array($item['id'], array_keys($extra_color))) ? 1 : 0;
                                        $this->db->where(array('id' => $item['id'], 'extra_id' => $id))->update('uploads', array('extra_color' => $value));
                                    }
                                }
                                
                                if(!$edit){
                                    redirect($this->admin_url."?m=shop&a=products");
                                }
                                else{
                                    redirect($this->admin_url."?m=shop&a=edit_product&id=" . intval($_GET['id']));
                                }
			}
		}

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("shop/add_product_m",$this->d);
	}

	function rebuild_products_order($parent_id=0)
	{
		$res=$this->db
		->order_by("order")
		->get_where("shop_products",array())
		->result();

		$i=1;
		foreach($res AS $r)
		{
			$this->db
			->where(array(
				"id"=>$r->id
			))
			->update("shop_products",array(
				"order"=>$i
			));
			
			$i++;
		}
	}

	public function order_product()
	{
		$id=(int)$this->input->get("id");
		$order=$this->input->get("order");

		$res=$this->db
		->get_where("shop_products",array(
			"id"=>$id
		))
		->row();
		
		$this->db
		->where(array(
			"order"=>$order=="up"?$res->order-1:$res->order+1
		))
		->update("shop_products",array(
			"order"=>$res->order
		));
		
		$this->db
		->where(array(
			"id"=>$id
		))
		->update("shop_products",array(
			"order"=>$order=="up"?$res->order-1:$res->order+1
		));

		$this->rebuild_products_order();

		redirect($this->admin_url."?m=shop&a=products&pg=".intval($this->input->get("pg")));
	}

	public function rm_product()
	{
		$id=(int)$this->input->get("id");

		$attaches_res=$this->db
		->select("file_name, file_path")
		->get_where("uploads",array(
			"component_type"=>"module",
			"component_name"=>"shop",
			"extra_type"=>"post_id",
			"extra_id"=>$id
		))
		->result();

		foreach($attaches_res AS $r)
		{
			if(file_exists("./".$r->file_path.$r->file_name)){
				unlink("./".$r->file_path.$r->file_name);
			}
		}

		$this->db
		->where(array(
			"component_type"=>"module",
			"component_name"=>"shop",
			"extra_type"=>"post_id",
			"extra_id"=>$id
		))
		->delete("uploads");

		$this->db
		->where(array(
			"id"=>$id
		))
		->delete("shop_products");
                
                // фиксируем, кто удалил товар – для статистики
                $user_info = $this->ci->ion_auth->user()->row();
                $this->db->insert('users_deleted_products', array(
                    'user_id' => $user_info->id,
                    'product_id' => $id,
                    'deleted' => date('Y-m-d H:i:s')
                ));

        $filters_uri="&filter_keywords=".$this->input->get("filter_keywords")."&filter_category_id=".$this->input->get("filter_category_id")."&filter_brand_id=".$this->input->get("filter_brand_id")."&filter_show=".$this->input->get("filter_show")."&filter_photo=".$this->input->get("filter_photo")."&filter_description=".$this->input->get("filter_description")."&filter_supplier=".$this->input->get("filter_supplier");

		redirect($this->admin_url."?m=shop&a=products".$filters_uri);
	}

	function import_statuses($status_id=NULL)
	{
		$statuses=array(
			0=>"На очереди",
			"during"=>"В процессе",
			"finish"=>"Готово",
			"backup-start"=>"Идет восстановление из резервной копии"
		);
		
		if(is_null($status_id))return $statuses;

		if(empty($status_id)){
			$status_id=0;
		}

		return $statuses[$status_id];
	}

	function import()
	{
		$buttons=array();
		$buttons[]=array("next","Импортировать","onclick"=>"$('form').append('<input type=hidden name=sm value=1>').submit(); return false;");

		$this->buttons("form",$buttons);

		$this->d['import_res']=$this->db
		->order_by("date_add DESC")
		->get_where("shop_import")
		->result();

		$rows=array();
		foreach($this->d['import_res'] AS $r)
		{
			if(is_string($r->options)){
				$r->options=json_decode($r->options);
			}

			$status_warning="";
			$remove_accept=false;
			if($r->status=="during"){
				if(mktime()-$r->date_add>1800)
				{
					$remove_accept=true;
					$status_warning.="<br /><small style=\"color:red;\">похоже это задание зависло, можете его удалить</small>";
				}
			}

			$supplier_res=$this->db
			->get_where("shop_suppliers",array(
				"id"=>$r->options->supplier
			))
			->row();

			$rows[]=array(
				'<a href="/uploads/shop_import/'.$r->options->file_name.'" target="_blank">'.$r->options->file_original_name.'</a>',
				$supplier_res->title,
				$r->accept_backup==1 && $r->status=="finish"?'<a href="#" onclick="if(confirm(\'Все текущие товары будут заменены товарами которые были в базе до '.date("d.m.Y H:i:s",$r->date_add).' ! Вы уверены?\')){ document.location.href=\''.$this->admin_url.'?m=shop&a=restore_from_backup&id='.$r->id.'&backup_name=backups/import-backup-'.$r->id.'.zip\'; } return false;" class="btn btn-mini">восстановить товары по этому бекапу</a><br /><!--размер: '.humn_file_size(filesize("backups/import-backup-".$r->id.".zip")).'-->':($r->status=="backup-start"?'<span style="font-weight:bolder; color:red;">идет восстановление!<br />перед тем как вносить изменения на сайте, дождитесь окончаения...</span>':'<small>-- резервной копии нет --</small>'),
				$this->import_statuses($r->status).$status_warning,
				"Загружен: ".date("d.m.Y H:i:s",$r->date_add)."<br />".
				"Начало импортирования: ".($r->date_start>0?date("d.m.Y H:i:s",$r->date_start):"--")."<br />".
				"Конец импортирования: ".($r->date_end>0?date("d.m.Y H:i:s",$r->date_end):"--"),
				"buttons"=>array(
					$r->status=="finish"?array("information",$this->admin_url."?m=shop&a=import_report&import_id=".$r->id):NULL,
					$r->status=="finish" || $remove_accept?array("cross",$this->admin_url."?m=shop&a=rm_import&id=".$r->id):NULL
				)
			);
		}

		$this->ci->fb->add("table",array(
			"prepend"=>'<div style="overflow:auto; height:270px; padding-right:20px;">',
			"append"=>'</div>',
			"title"=>"Файлы на импортирование",
			"parent"=>"greed1",
			"head"=>array(
				"Имя файла",
				"Поставщик",
				"Резервная копия",
				"Статус",
				"Дата"
			),
			"rows"=>$rows
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Импортер",
			"name"=>"importer",
			"parent"=>"greed1",
			"primary"=>true,
			"options"=>array()
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"accept_delete",
			"label"=>"удалить товары которые отсутствуют в импортируемом файле",
			"parent"=>"greed1",
			"attr:onclick"=>"if(!confirm('Если Вы выбираете пункт \'удалить товары которые отсутствуют в импортируемом файле\' все товары которые не будут найдены в этом Excel файле, будут удалены из базы сайта! Вы уверены?')){ this.checked=false; return false; };"
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"accept_hide",
			"label"=>"скрывать товары которые отсутствуют в импортируемом файле",
			"parent"=>"greed1",
			"help"=>"скрыт будет абсолютно любой товар который не будет найден в excel, даже от других поставщиков"
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"accept_add",
			"label"=>"добавить товары которые отсутствуют в базе",
			"parent"=>"greed1"
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"accept_backup",
			"label"=>"создать резервную копию товаров",
			"help"=>"создается полная копия всех фотографий, категорий, типов, товаров, брендов, подборок, скидок",
			"parent"=>"greed1",
			"attr:checked"=>"checked"
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed1",
			"parent"=>"tab1"
		));

		// $this->ci->fb->add("tabs",array(
		// 	"tabs"=>array(
		// 		"tab1"=>"Импорт",
		// 		"tab2"=>"Экспорт"
		// 	),
		// 	"name"=>"tabs",
		// 	"parent"=>"block"
		// ));

		$this->ci->fb->add("block",array(
			"name"=>"tab1",
			"parent"=>"form",
			"method"=>"post"
		));

		$this->ci->fb->add("form",array(
			"name"=>"form",
			"parent"=>"render",
			"method"=>"post"
		));

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		if($this->ci->fb->submit){
			$this->d['global_errors']=$this->ci->fb->errors_list();

			$this->plugin_trigger("onMethodAfterSaveErrorsCheck",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

			if(sizeof($this->d['global_errors'])==0){
				$this->d['insert']=array(
					"type"=>"import",
					"plugin_name"=>$this->input->post("importer"),
					"accept_delete"=>$this->input->post("accept_delete")==1?1:0,
					"accept_hide"=>$this->input->post("accept_hide")==1?1:0,
					"accept_add"=>$this->input->post("accept_add")==1?1:0,
					"accept_backup"=>$this->input->post("accept_backup")==1?1:0,
					"date_add"=>mktime()
				);
				$this->plugin_trigger("onMethodBeforeSave",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

				$this->db->insert("shop_import",$this->d['insert']);

				$this->plugin_trigger("onMethodAfterSave",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

				redirect($this->admin_url."?m=shop&a=import");
			}
		}

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("shop/import",$this->d);
	}

	public function rm_import()
	{
		$id=intval($this->input->get("id"));

		$import_res=$this->db->get_where("shop_import",array(
			"id"=>$id
		))
		->row();

		if(is_string($import_res->options)){
			$import_res->options=json_decode($import_res->options);
		}
		
		if(!empty($import_res->options->file_name) && file_exists("./uploads/shop_import/".$import_res->options->file_name)){
			unlink("./uploads/shop_import/".$import_res->options->file_name);
		}

		$this->db
		->where("id",$id)
		->delete("shop_import");

		$this->db
		->where("import_id",$id)
		->delete("shop_import_report");

		if(file_exists("backups/".$id.".zip"))unlink("backups/".$id.".zip");
		directory_remove("backups/".$id."/");

		redirect($this->admin_url."?m=shop&a=import");
	}

	protected function report_types($type=NULL)
	{
		$types=array(
			"added"=>"<span style='color:green;'>Товар добавлен</span>",
			"updated"=>"<span style='color:orange;'>Товар обновлен</span>",
			"deleted"=>"<span style='color:red;'>Товар удален</span>"
		);

		if(is_null($type))return $types;

		return $types[$type];
	}

	public function import_report()
	{
		$buttons=array();
		$buttons[]=array("back",NULL,$this->admin_url."?m=shop&a=import");
		
		$this->buttons("form",$buttons);

		$import_id=intval($this->input->get("import_id"));
		$product_id=intval($this->input->get("product_id"));

		$where=array();
		if($import_id>0){
			$where['shop_import_report.import_id']=$import_id;
		}
		if($product_id>0){
			$where['shop_import_report.product_id']=$product_id;
		}

		$this->d['report_res_num']=$this->db
		->where($where)
		->count_all_results("shop_import_report");

		$pagination=$this->ci->fb->pagination_init($this->d['report_res_num'],20,current_url_query(array("pg"=>NULL)),"pg");

		$this->d['report_res']=$this->db
		->select("shop_products.code AS product_code, shop_products.title AS product_title")
		->select("shop_import_report.*")
		->join("shop_products","shop_products.id = shop_import_report.product_id","left")
		->order_by("shop_import_report.date_add DESC")
		->limit((int)$pagination->per_page,(int)$pagination->cur_page)
		->get_where("shop_import_report",$where)
		->result();

		$rows=array();
		foreach($this->d['report_res'] AS $r)
		{
			$text="";

			$text.="<h6>".$this->report_types($r->type)." (ID: ".$r->product_id.")</h6>"
				.(empty($r->text)?"":$r->text."<br />");

			if(!empty($r->product_code)){
				$text.="товар: ".$r->product_title." (<a href=\"".$this->admin_url."?m=shop&a=edit_product&id=".$r->product_id."\">редактировать</a>)";
			}

			$rows[]=array(
				$text,
				date("d.m.Y H:i:s",$r->date_add)
			);
		}

		$this->ci->fb->add("table",array(
			"parent"=>"table",
			"head"=>array(
				"Действие",
				"Дата"
			),
			"rows"=>$rows,
			"pagination"=>$pagination->create_links()
		));

		$this->d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("shop/import_report",$this->d);
	}

	public function product_types()
	{
            if(!$this->ci->load->check_page_access_new("shop_products_types","shop","module")) return;
            
		$this->buttons("main",array(
			array("add","Добавить<br />тип",$this->admin_url."?m=shop&a=add_product_type")
		));

		$this->d['product_types_res']=$this->db
		->get_where("shop_product_types")
		->result();


		$rows=array();
		foreach($this->d['product_types_res'] AS $r)
		{
			$rows[]=array(
				'<a href="'.$this->admin_url.'?m=shop&a=edit_product_type&id='.$r->id.'">'.$r->title.'</a>',
				"enabled"=>array($this->admin_url."?m=shop&a=enabled_product_type&id=".$r->id."&enable=",$r->show),
				"buttons"=>array(
					array("pencil",$this->admin_url."?m=shop&a=edit_product_type&id=".$r->id),
					array("cross",$this->admin_url."?m=shop&a=rm_product_type&id=".$r->id)
				)
			);
		}

		$this->ci->fb->add("table",array(
			"id"=>"products",
			"parent"=>"table",
			"head"=>array(
				"Название",
				"Включен"
			),
			"rows"=>$rows
		));

		$this->d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("shop/product_types",$this->d);
	}

	function enabled_product_type()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("shop_product_types",array(
			"show"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=shop&a=product_types");
	}

	function edit_product_type()
	{
		$_GET['id']=intval($_GET['id']);
		$this->add_product_type(true);
	}

	function add_product_type($edit=false)
	{
            if(!$this->ci->load->check_page_access_new("shop_products_types","shop","module")) return;
            
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['item_res']=$this->db
		->get_where("shop_product_types",array(
			"id"=>$_GET['id']
		))
		->row();

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=shop&a=product_types");

		$this->buttons("form",$buttons);

		$this->ci->fb->add("input:text",array(
			"label"=>"Название типа",
			"name"=>"title",
			"parent"=>"greed"
		));

		$html="";

		function field_select_tr($d=NULL)
		{
			$id="";
			if(is_null($d)){
				$field_name="new_field";
			}else{
				$field_name="field";
				$id=$d->id;
			}

			$options=array();
			foreach($d->params->options AS $k=>$v)
			{
				$options[]=$k."=>".$v;
			}
			$options=implode("\n",$options);

			$filter_checked=$d->filter==1?' checked="checked"':'';
			
			$html="";
			$html.=<<<EOF
<tr>
	<td>
		Выпадающий список
	</td>
	<td>
		<input type="hidden" name="{$field_name}[type][{$id}]" value="select" />
		<strong>Заголовок:</strong><br />
		<input type="text" name="{$field_name}[title][{$id}]" value="{$d->title}" style="width:150px;" />
		<br /><br />
		<strong>Значения выпадающего списка:</strong><br />
		<textarea name="{$field_name}[options][{$id}]" style="width:200px; height:70px; font-size:10px; line-height:10px;">{$options}</textarea>
		<br />
		<small>В каждом новом ряду новое значение, перед значением можно указать ключ, например: ключ=>значение</small>
		<br /><br />
		<label><input type="checkbox" name="{$field_name}[filter][{$id}]" value="1"{$filter_checked} /> показывать в фильтре</label>
	</td>
	<td>
		<a href="#" onclick="$(this).parents('tr:eq(0)').remove(); return false;"><img src="/templates/default/admin/assets/icons/cross.png" border="0" /></a>
	</td>
</tr>
EOF;

			return $html;
		}

		function field_input_checkbox_tr($d=NULL)
		{
			$id="";
			if(is_null($d)){
				$field_name="new_field";
			}else{
				$field_name="field";
				$id=$d->id;
			}

			$filter_checked=$d->filter==1?' checked="checked"':'';
			
			$html="";
			$html.=<<<EOF
<tr>
	<td>
		Галочка
	</td>
	<td>
		<input type="hidden" name="{$field_name}[type][{$id}]" value="input:checkbox" />
		<strong>Заголовок:</strong><br />
		<input type="text" name="{$field_name}[title][{$id}]" value="{$d->title}" style="width:150px;" />
		<br /><br />
		<label><input type="checkbox" name="{$field_name}[filter][{$id}]" value="1"{$filter_checked} /> показывать в фильтре</label>
	</td>
	<td>
		<a href="#" onclick="$(this).parents('tr:eq(0)').remove(); return false;"><img src="/templates/default/admin/assets/icons/cross.png" border="0" /></a>
	</td>
</tr>
EOF;

			return $html;
		}

		function field_input_text_tr($d=NULL)
		{
			$id="";
			if(is_null($d)){
				$field_name="new_field";
			}else{
				$field_name="field";
				$id=$d->id;
			}

			$filter_checked=$d->filter==1?' checked="checked"':'';
			
			$html="";
			$html.=<<<EOF
<tr>
	<td>
		Текстовое поле (одна строка)
	</td>
	<td>
		<input type="hidden" name="{$field_name}[type][{$id}]" value="input:text" />
		<strong>Заголовок:</strong><br />
		<input type="text" name="{$field_name}[title][{$id}]" value="{$d->title}" style="width:150px;" />
		<br /><br />
		<label><input type="checkbox" name="{$field_name}[filter][{$id}]" value="1"{$filter_checked} /> показывать в фильтре</label>
	</td>
	<td>
		<a href="#" onclick="$(this).parents('tr:eq(0)').remove(); return false;"><img src="/templates/default/admin/assets/icons/cross.png" border="0" /></a>
	</td>
</tr>
EOF;

			return $html;
		}

		function field_textarea_tr($d=NULL)
		{
			$id="";
			if(is_null($d)){
				$field_name="new_field";
			}else{
				$field_name="field";
				$id=$d->id;
			}

			$filter_checked=$d->filter==1?' checked="checked"':'';
			
			$html="";
			$html.=<<<EOF
<tr>
	<td>
		Большое текстовое поле
	</td>
	<td>
		<input type="hidden" name="{$field_name}[type][{$id}]" value="textarea" />
		<strong>Заголовок:</strong><br />
		<input type="text" name="{$field_name}[title][{$id}]" value="{$d->title}" style="width:150px;" />
		<br /><br />
		<label><input type="checkbox" name="{$field_name}[filter][{$id}]" value="1"{$filter_checked} /> показывать в фильтре</label>
	</td>
	<td>
		<a href="#" onclick="$(this).parents('tr:eq(0)').remove(); return false;"><img src="/templates/default/admin/assets/icons/cross.png" border="0" /></a>
	</td>
</tr>
EOF;

			return $html;
		}

		$field_select_tr=str_replace("'","\'",str_replace(array("\r","\n","\t"),"",field_select_tr()));
		$field_input_checkbox_tr=str_replace("'","\'",str_replace(array("\r","\n","\t"),"",field_input_checkbox_tr()));
		$field_input_text_tr=str_replace("'","\'",str_replace(array("\r","\n","\t"),"",field_input_text_tr()));
		$field_textarea_tr=str_replace("'","\'",str_replace(array("\r","\n","\t"),"",field_textarea_tr()));

		$fields_html="";
		$fields_res=$this->db->get_where("shop_product_type_fields",array(
			"type_id"=>intval($_GET['id'])
		))
		->result();

		foreach($fields_res AS $r)
		{
			if(is_string($r->params)){
				$r->params=json_decode($r->params);
			}

			$type=str_replace(":","_",$r->field_type);

			$fields_html.=call_user_func("field_".$type."_tr",$r);
		}

		$html.=<<<EOF
<script>
function addField()
{
	var table=$("#fieldsList");
	var field_type=$("#add_field_type").val();

	var html='';

	switch(field_type)
	{
		case'select':
			html+='{$field_select_tr}';
		break;
		case'input:checkbox':
			html+='{$field_input_checkbox_tr}';
		break;
		case'input:text':
			html+='{$field_input_text_tr}';
		break;
		case'textarea':
			html+='{$field_textarea_tr}';
		break;
	}

	table.find("tr:last").after(html);
}
</script>
<br />
<table class="table" id="fieldsList">
<tr>
	<th>Тип</th>
	<th>Параметры</th>
	<th>&nbsp;</th>
</tr>
{$fields_html}
</table>
<br /><br />
<strong>Добавить поле:</strong><br />
<select name="add_field_type" id="add_field_type">
<option value="select">Выпадающий список</option>
<option value="input:checkbox">Галочка</option>
<option value="input:text">Текстовое поле (одна строка)</option>
<option value="textarea">Большое текстовое поле</option>
</select>
<button onclick="addField(); return false;" class="btn btn-mini">Добавить</button>
EOF;

		$this->ci->fb->add("html",array(
			"label"=>"Дополнительные поля",
			"content"=>$html,
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

		if(!$this->ci->fb->submit && $edit){
			$this->ci->fb->change("title",array("value"=>$this->d['item_res']->title));
		}

		if($this->ci->fb->submit){
			$this->d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($this->d['global_errors'])==0){
				if($edit){
					$this->db
					->where("id",$_GET['id'])
					->update("shop_product_types",array(
						"title"=>$this->input->post("title")
					));
					$type_id=$_GET['id'];
				}else{
					$this->db
					->where("id",$_GET['id'])
					->insert("shop_product_types",array(
						"title"=>$this->input->post("title"),
						"date_add"=>mktime(),
						"show"=>1
					));

					$type_id=$this->db->insert_id();
				}

				foreach($fields_res AS $r)
				{
					$type=$_POST['field']['type'][$r->id];
					if(empty($type)){
						$this->db
						->where("id",$r->id)
						->delete("shop_product_type_fields");

						$this->db->query("ALTER TABLE `shop_products` DROP `f_".$r->id."`");

						continue;
					}
					
					$title=$_POST['field']['title'][$r->id];
					$filter=$_POST['field']['filter'][$r->id]==1?1:0;

					$params=array();
					switch($type)
					{
						case'select':
							$params['options']=array();
							foreach(explode("\n",$_POST['field']['options'][$r->id]) AS $val)
							{
								$val=trim($val);
								if(empty($val))continue;
								if(preg_match("#=>#",$val)){
									list($key,$val)=explode("=>",$val);
									$params['options'][trim($key)]=trim($val);
								}else{
									$params['options'][]=$val;
								}
							}
						break;
					}

					$this->db
					->where("id",$r->id)
					->update("shop_product_type_fields",array(
						"title"=>$title,
						"params"=>json_encode($params),
						"show"=>1,
						"filter"=>$filter
					));
				}
				
				foreach($_POST['new_field']['type'] AS $i=>$type)
				{
					$title=$_POST['new_field']['title'][$i];
					$filter=$_POST['new_field']['filter'][$i]==1?1:0;

					$params=array();
					switch($type)
					{
						case'select':
							$params['options']=array();
							foreach(explode("\n",$_POST['new_field']['options'][$i]) AS $val)
							{
								$val=trim($val);
								if(empty($val))continue;
								if(preg_match("#=>#",$val)){
									list($key,$val)=explode("=>",$val);
									$params['options'][trim($key)]=trim($val);
								}else{
									$params['options'][]=$val;
								}
							}
						break;
					}

					$this->db
					->insert("shop_product_type_fields",array(
						"type_id"=>$type_id,
						"field_type"=>$type,
						"title"=>$title,
						"params"=>json_encode($params),
						"show"=>1,
						"filter"=>$filter,
						"date_add"=>mktime()
					));

					$field_id=$this->db->insert_id();

					switch($type)
					{
						case'select':
							// $sql="ALTER TABLE `shop_products` ADD  `f_".$field_id."` VARCHAR(255) NOT NULL";
							$sql="ALTER TABLE `shop_products` ADD  `f_".$field_id."` INT NOT NULL";
						break;
						case'input:checkbox':
							$sql="ALTER TABLE `shop_products` ADD  `f_".$field_id."` TINYINT(1) NOT NULL";
						break;
						case'input:text':
							$sql="ALTER TABLE `shop_products` ADD  `f_".$field_id."` VARCHAR(255) NOT NULL";
						break;
						case'textarea':
							$sql="ALTER TABLE `shop_products` ADD  `f_".$field_id."` TEXT NOT NULL";
						break;
					}

					$this->db->query($sql);
				}

				redirect($this->admin_url."?m=shop&a=product_types");
			}
		}

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("shop/add_product_type",$this->d);
	}

	function products_mass_event()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$buttons=array();
		$buttons[]=array("save");
		$buttons[]=array("apply");
		$buttons[]=array("back",NULL,$this->admin_url."?m=shop&a=products");

		$this->buttons("form",$buttons);

		$all_from_table=array();
		$mass_id=$this->input->post('mass_id');
		if(!empty($mass_id['all_from_table'])){
			$all_from_table=explode(",",$mass_id['all_from_table']);
			foreach($all_from_table AS $k=>$v)
			{
				$v=intval($v);
				if($v<1)unset($all_from_table[$k]);
			}
			unset($mass_id['all_from_table']);
		}

		foreach($mass_id AS $k=>$id)
		{
			$id=intval($id);
			if($id<1)unset($mass_id[$k]);
		}

		$mass_id=array_merge($mass_id,$all_from_table);
		unset($all_from_table);

		$this->ci->fb->add("html",array(
			"content"=>'Товаров выбрано: '.sizeof($mass_id),
			"value"=>"shop",
			"parent"=>"greed"
		));

		$this->ci->fb->add("list:select",array(
			"attr:size"=>5,
			"label"=>"Действие",
			"name"=>"event",
			"parent"=>"greed",
			"primary"=>true,
			"attr:onchange"=>"change_event();",
			"options"=>array(
				"change_category"=>"Изменить категорию",
				"change_type"=>"Изменить тип товаров",
				"show"=>"Опубликовать",
				"hide"=>"Снять с публикации",
				"delete"=>"Удалить товары"
			)
		));

		$options=$this->cats_options_list();
		$this->ci->fb->add("list:select",array(
			"attr:multiple"=>"multiple",
			"attr:size"=>5,
			"label"=>"Новая категория",
			"name"=>"category_id[]",
			"parent"=>"greed",
			"options"=>$options,
			"hidden"=>true,
			"class"=>"hidden_fields hidden_category_id"
		));

		$options=array();
		$this->d['shop_product_types_res']=$this->db->
		get_where("shop_product_types")
		->result();
		foreach($this->d['shop_product_types_res'] AS $r)
		{
			$options[$r->id]=$r->title;
		}
		$this->ci->fb->add("list:select",array(
			"label"=>"Новый тип товаров",
			"name"=>"type_id",
			"parent"=>"greed",
			"options"=>$options,
			"hidden"=>true,
			"class"=>"hidden_fields hidden_type_id"
		));

		$post_mass_id=$this->input->post('mass_id');

		$this->ci->fb->add("input:hidden",array(
			"name"=>"mass_id[all_from_table]",
			"parent"=>"greed",
			"value"=>$post_mass_id['all_from_table']
		));
		
		foreach($post_mass_id AS $k=>$id)
		{
			if(!is_numeric($k))continue;
			$this->ci->fb->add("input:hidden",array(
				"name"=>"mass_id[]",
				"parent"=>"greed",
				"value"=>$id
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

		if(sizeof($mass_id)<1){
			$this->d['global_errors'][]="Необходимо вернуться и выбрать хотя бы один товар!";
		}

		if($this->ci->fb->submit){
			$this->d['global_errors']=array_merge($this->d['global_errors'],$this->ci->fb->errors_list());

			if(sizeof($this->d['global_errors'])==0){
				if($this->input->post("event")===false){
					$this->d['global_errors'][]="Выберите действие!";
				}

				$event=$this->input->post("event");

				// if($event=="change_category" && $this->input->post("category_id")===false){
				// 	$this->d['global_errors'][]="Выберите категорию!";
				// }

				if($event=="change_type" && $this->input->post("type_id")===false){
					$this->d['global_errors'][]="Выберите тип!";
				}

				if(sizeof($this->d['global_errors'])==0){
					$where=array(
						"id IN ('".implode("','",$mass_id)."')"=>NULL
					);
					switch($event)
					{
						case "change_category":
							$category_ids=array();
							$category_all_ids=array();
							$category_paths=array();
							if($this->input->post("category_id")!==false){
								$category_ids=$this->input->post("category_id");
								foreach($this->input->post("category_id") AS $cat_id)
								{
									$category_paths[$cat_id]=$this->cat_parents_ids($cat_id);
									$category_all_ids=array_merge($category_all_ids,$category_paths[$cat_id]);
								}
							}
							$category_all_ids=array_unique($category_all_ids);

							$this->update_product(array(
								"category_ids"=>implode(",",$this->input->post("category_id")),
								"category_paths"=>json_encode($category_paths)
							),$where);

							// удаляем все связи данного товара с категориями
							$this->db
							->where("product_id IN (".implode(",",$mass_id).")")
							->delete("shop_products_categories_link");

							$insert=array();
							foreach($mass_id AS $id)
							{
								// создаем новые связи данного товара с категориями
								foreach($category_all_ids AS $category_id)
								{
									$category_id=intval($category_id);
									if($category_id<1)continue;
									$insert[]="(".$id.",".$category_id.")";
									// $this->db
									// ->insert("shop_products_categories_link",array(
									// 	"product_id"=>$id,
									// 	"category_id"=>$category_id
									// ));
								}
							}

							$per_query=50;
							$slices_num=ceil(sizeof($insert)/$per_query);
							for($i=0;$i<$slices_num;$i++)
							{
								$q=array_slice($insert,$i*$per_query,$per_query);

								if(sizeof($q)<1)continue;

								$this->db->query("INSERT INTO `shop_products_categories_link` (`product_id`,`category_id`) VALUES ".implode(",",$q));
							}
						break;
						case "change_type":
							$this->db
							->where($where)
							->update("shop_products",array(
								"type_id"=>$this->input->post("type_id")
							));
						break;
						case "show":
							$this->db
							->where($where)
							->update("shop_products",array(
								"show"=>1
							));
						break;
						case "hide":
							$this->db
							->where($where)
							->update("shop_products",array(
								"show"=>0
							));
						break;
						case "delete":
							$this->delete_product($where);
						break;
					}
					redirect($this->admin_url."?m=shop&a=products&filter_keywords=".$_GET['filter_keywords']."&filter_category_id=".$_GET['filter_category_id']."&filter_brand_id=".$_GET['filter_brand_id']."&filter_show=".$_GET['filter_show']."&filter_photo=".$_GET['filter_photo']."&filter_description=".$_GET['filter_description']."&filter_supplier=".$_GET['filter_supplier']."&pg=".$_GET['pg']);
				}
			}
		}

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("shop/products_mass_event",$this->d);
	}

	function manufacturers()
	{
            if(!$this->ci->load->check_page_access_new("shop_manufacturers","shop","module")) return;
            
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->buttons("main",array(
			array("add","Добавить<br />бренд",$this->admin_url."?m=shop&a=add_manufacturer")
		));

		$this->d['manufacturers_res']=$this->db
		->order_by("title", 'asc')
		->get_where("categoryes",array(
			"type"=>"shop-manufacturer"
		))
		->result();

		$rows=array();
		foreach($this->d['manufacturers_res'] AS $r)
		{
			$rows[]=array(
				'<a href="' . $this->admin_url . '?m=shop&a=edit_manufacturer&id=' . $r->id . '">' . $r->title . '</a>',
                                '<a href="' . base_url('catalog/' . $r->name . '-' . $r->id . 'm') . '/" target="_blank">' . base_url('catalog/' . $r->name . '-' . $r->id . 'm/') . '/</a>',
				"enabled"=>array($this->admin_url."?m=shop&a=enabled_manufacturer&id=".$r->id."&enable=",$r->show),
//				"order"=>array($this->admin_url."?m=shop&a=order_manufacturer&id=".$r->id."&order=",$r->order,sizeof($this->d['manufacturers_res'])),
				"buttons"=>array(
					array("pencil",$this->admin_url."?m=shop&a=edit_manufacturer&id=".$r->id),
					array("cross",$this->admin_url."?m=shop&a=rm_manufacturer&id=".$r->id)
				)
			);
		}

		$this->ci->fb->add("table",array(
			"parent"=>"table",
			"head"=>array(
				"Название",
                                "Ссылка на сайт",
				"Опубликован",
//				"Порядок"
			),
			"rows"=>$rows
		));

		$this->d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("shop/manufacturers",$this->d);
	}

	function edit_manufacturer()
	{
		$_GET['id']=intval($_GET['id']);
		$this->add_manufacturer(true);
	}

	function add_manufacturer($edit=false)
	{
            if(!$this->ci->load->check_page_access_new("shop_manufacturers","shop","module")) return;
            
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=shop&a=manufacturers");

		$this->buttons("form",$buttons);

		if($edit){
			$this->d['item_res']=$this->db
			->select("categoryes.*")
			->get_where("categoryes",array("categoryes.id"=>$_GET['id']))
			->row();
		}

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"URL",
			"name"=>"name",
			"parent"=>"greed",
			"primary"=>true
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Страна",
			"name"=>"country",
			"parent"=>"greed",
			"primary"=>true
		));

		$this->ci->fb->add("upload",array(
			"label"=>"Логотип",
			"component_type"=>"module",
			"component_name"=>"shop",
			"extra_type"=>"manufacturer_id",
			"upload_path"=>"./uploads/shop/manufacturer/",
			"key"=>$edit?"":(!empty($_POST['key'])?$_POST['key']:""),
			"extra_id"=>$edit?$_GET['id']:0,
			"name"=>"manufacturer_logo",
			"parent"=>"greed",
			"dynamic"=>true,
			"proc_config_var_name"=>"mod_shop[manufacturers_images_options]"
		));

		$this->ci->fb->add("textarea:editor",array(
			"label"=>"Описание",
			"name"=>"description",
			"id"=>"description",
			"parent"=>"greed",
			"attr:style"=>"height:200px; width:700px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true
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
			if(is_string($this->d['item_res']->options))$this->d['item_res']->options=json_decode($this->d['item_res']->options);

			if(isset($this->d['item_res']->options->country) && !empty($this->d['item_res']->options->country)){
				$this->ci->fb->change("country",array("value"=>$this->d['item_res']->options->country));
			}

			$this->ci->fb->change("title",array("value"=>$this->d['item_res']->title));
			$this->ci->fb->change("description",array("value"=>$this->d['item_res']->description));
			$this->ci->fb->change("name",array("value"=>$this->d['item_res']->name));
		}

		if($this->ci->fb->submit){
			$this->d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($this->d['global_errors'])==0){
				$name=$this->input->post("name");
				if($this->input->post("name")==""){
					$name=rewrite_alias($this->input->post("title"));
				}

				if($edit){
					$options=$this->d['item_res']->options;
					if(is_string($options))$options=json_decode($options);

					$options->country=$this->input->post("country");
				}else{
					$options=array();

					$options['country']=$this->input->post("country");
				}

				if($edit){
					$this->ci->categories->update_category(array(
						"type"=>"shop-manufacturer",
						"parent_id"=>(int)$this->input->post("parent_id"),
						"title"=>$this->input->post("title"),
						"description"=>$this->input->post("description"),
						"name"=>$name,
						"options"=>json_encode($options)
					),array(
						"id"=>$_GET['id']
					));

					$manufacturer_id=$_GET['id'];
				}else{
					$manufacturer_id=$this->ci->categories->add_category(array(
						"type"=>"shop-manufacturer",
						"parent_id"=>(int)$this->input->post("parent_id"),
						"title"=>$this->input->post("title"),
						"description"=>$this->input->post("description"),
						"name"=>$name,
						"options"=>json_encode($options)
					));
				}

				$this->db
				->where(array(
					"key"=>$_POST['key'],
					"component_type"=>"module",
					"component_name"=>"shop",
					"extra_id"=>0
				))
				->update("uploads",array(
					"key"=>"",
					"extra_id"=>$manufacturer_id
				));

				redirect($this->admin_url."?m=shop&a=manufacturers");
			}
		}

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("shop/add_manufacturer",$this->d);
	}

	public function enabled_manufacturer()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("categoryes",array(
			"show"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=shop&a=manufacturers");
	}

	function rm_manufacturer()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->ci->categories->rm_category(array(
			"type"=>"shop-manufacturer",
			"id"=>(int)$this->input->get("id")
		));

		redirect($this->admin_url."?m=shop&a=manufacturers");
	}

	function order_manufacturer()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->ci->categories->order_category(
			intval($this->input->get("id")),
			$this->input->get("order")
		);

		redirect($this->admin_url."?m=shop&a=manufacturers");
	}

	function collections()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->buttons("main",array(
			array("add","Добавить<br />подборку",$this->admin_url."?m=shop&a=add_collection")
		));

		$this->d['collections_res']=$this->db
		->order_by("order")
		->get_where("categoryes",array(
			"type"=>"shop-collection"
		))
		->result();

		$rows=array();
		foreach($this->d['collections_res'] AS $r)
		{
			$rows[]=array(
				$r->title,
				"enabled"=>array($this->admin_url."?m=shop&a=enabled_collection&id=".$r->id."&enable=",$r->show),
				"buttons"=>array(
					array("pencil",$this->admin_url."?m=shop&a=edit_collection&id=".$r->id),
					array("cross",$this->admin_url."?m=shop&a=rm_collection&id=".$r->id)
				)
			);
		}

		$this->ci->fb->add("table",array(
			"parent"=>"table",
			"head"=>array(
				"Название",
				"Опубликован"
			),
			"rows"=>$rows
		));

		$this->d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("shop/collections",$this->d);
	}

	public function enabled_collection()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("categoryes",array(
			"show"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=shop&a=collections");
	}

	function rm_collection()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->ci->categories->rm_category(array(
			"type"=>"shop-collection",
			"id"=>(int)$this->input->get("id")
		));

		redirect($this->admin_url."?m=shop&a=collections");
	}



	function edit_collection()
	{
		$_GET['id']=intval($_GET['id']);
		$this->add_collection(true);
	}

	function add_collection($edit=false)
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=shop&a=collections");

		$this->buttons("form",$buttons);

		if($edit){
			$this->d['item_res']=$this->db
			->select("categoryes.*")
			->get_where("categoryes",array("categoryes.id"=>$_GET['id']))
			->row();
		}

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true
		));

		// $this->ci->fb->add("input:text",array(
		// 	"label"=>"URL",
		// 	"name"=>"name",
		// 	"parent"=>"greed",
		// 	"primary"=>true
		// ));

		// $this->ci->fb->add("upload",array(
		// 	"label"=>"Логотип",
		// 	"component_type"=>"module",
		// 	"component_name"=>"shop",
		// 	"extra_type"=>"collection_id",
		// 	"upload_path"=>"./uploads/shop/collection/",
		// 	"key"=>$edit?"":(!empty($_POST['key'])?$_POST['key']:""),
		// 	"extra_id"=>$edit?$_GET['id']:0,
		// 	"name"=>"collection_logo",
		// 	"parent"=>"greed",
		// 	"dynamic"=>true
		// ));

		$html="";

		$shop_collection_products_res=array();
		if($edit){
			$shop_collection_products_res=$this->db
			->join("shop_products","shop_products.id = shop_collection_products.product_id")
			->get_where("shop_collection_products",array(
				"collection_id"=>$_GET['id']
			))
			->result();

			$trs="";
			foreach($shop_collection_products_res AS $r)
			{
				$trs.=<<<EOF
<tr>
<td>
	<input type="hidden" name="product_ids[]" value="{$r->id}" />
	<a href="{$this->admin_url}?m=shop&a=edit_product&id={$r->id}" target="_blank">{$r->title}</a>
</td>
<td>{$r->code}</td>
<td><a href="#" onclick="$(this).parents('tr:eq(0)').remove(); return false;"><img src="/templates/default/admin/assets/icons/cross.png" /></a></td>
</tr>
EOF;
			}
		}

		$html.=<<<EOF
<script>
function openAddProductModal()
{
	$("#modelAddProduct").modal();
}

function openAddProduct(d)
{
	var html='';
	html+='<tr>';
	html+='<td>';
	html+='<input type="hidden" name="product_ids[]" value="'+d.id+'" />';
	html+='<a href="{$this->admin_url}?m=shop&a=edit_product&id='+d.id+'" target="_blank">'+d.title+'</a>';
	html+='</td>';
	html+='<td>'+d.code+'</td>';
	html+='<td><a href="#" onclick="$(this).parents(\'tr:eq(0)\').remove(); return false;"><img src="/templates/default/admin/assets/icons/cross.png" /></a></td>';
	html+='</tr>';
	$("#products_list tr:last").after(html);
}
</script>
<style>
#modelAddProduct {
    width: 750px;
    margin: -345px 0 0 -375px;
}
</style>
<div id="modelAddProduct" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Добавить товар в колекцию</h3>
  </div>
  <div class="modal-body">
    <iframe src="{$this->admin_url}?m=shop&a=products&iframe_display=1" style="width:720px; height:300px; border:none;" frameborder="0"></iframe>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Отмена</button>
  </div>
</div>
<table id="products_list" class="table table-condensed table-striped">
<tr>
  <th>Название товара</th>
  <th>Артикул</th>
  <th>&nbsp;</th>
</tr>
{$trs}
</table>
<button class="btn btn-mini" onclick="openAddProductModal(); return false;">Добавить товара</button>
EOF;

		$this->ci->fb->add("html",array(
			"label"=>"Товары в колекции",
			"content"=>$html,
			"parent"=>"greed",
			"primary"=>true
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
			$this->ci->fb->change("title",array("value"=>$this->d['item_res']->title));
			$this->ci->fb->change("name",array("value"=>$this->d['item_res']->name));
		}

		if($this->ci->fb->submit){
			$this->d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($this->d['global_errors'])==0){
				$name=$this->input->post("name");
				if($this->input->post("name")==""){
					$name=rewrite_alias($this->input->post("title"));
				}

				if($edit){
					$this->ci->categories->update_category(array(
						"type"=>"shop-collection",
						"parent_id"=>(int)$this->input->post("parent_id"),
						"title"=>$this->input->post("title"),
						"name"=>$name
					),array(
						"id"=>$_GET['id']
					));

					$collection_id=$_GET['id'];
				}else{
					$collection_id=$this->ci->categories->add_category(array(
						"type"=>"shop-collection",
						"parent_id"=>(int)$this->input->post("parent_id"),
						"title"=>$this->input->post("title"),
						"name"=>$name
					));
				}

				if($edit){
					$this->db
					->where("collection_id",$_GET['id'])
					->delete("shop_collection_products");
				}

				foreach($_POST['product_ids'] AS $product_id)
				{
					$this->db->insert("shop_collection_products",array(
						"collection_id"=>$collection_id,
						"product_id"=>$product_id,
						"date_add"=>mktime()
					));
				}

				$this->db
				->where(array(
					"key"=>$_POST['key'],
					"component_type"=>"module",
					"component_name"=>"shop",
					"extra_id"=>0
				))
				->update("uploads",array(
					"key"=>"",
					"extra_id"=>$collection_id
				));

				redirect($this->admin_url."?m=shop&a=collections");
			}
		}

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("shop/add_collection",$this->d);
	}

	public function orders()
	{
            if(!$this->ci->load->check_page_access_new("shop_orders","shop","module")) return;
            
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->buttons("main",array(
			array("add","Добавить<br />заказ",$this->admin_url."?m=shop&a=add_order")
		));

		$where=array();

		$where['shop_orders.status !=']="";
		if($this->input->get("v")==="other"){
			$where['shop_orders.status IN (\''.implode("','",array("client-refusal","issued","posted","delivered","paid","canceled")).'\')']=NULL;
		}else{
			$where['shop_orders.status NOT IN (\''.implode("','",array("client-refusal","issued","posted","delivered","paid","canceled")).'\')']=NULL;
		}

		$this->d['orders_res_num']=$this->db
		->where(array(
			"status !="=>"-1"
		))
		->where($where)
		->count_all_results("shop_orders");

		$pagination=$this->ci->fb->pagination_init($this->d['orders_res_num'],20,current_url_query(array("pg"=>NULL)),"pg");

		$this->d['orders_res']=$this->db
		->order_by("date_add","DESC")
		->where($where)
		->limit((int)$pagination->per_page,(int)$pagination->cur_page)
		->get_where("shop_orders",array(
			"status !="=>"-1"
		))
		->result();

		$rows=array();
		$users=array();
		$this->d['status_history']=array();
		foreach($this->d['orders_res'] AS $r)
		{
			$this->d['status_history'][$r->id]=array();
			foreach(explode("\n",$r->status_history) AS $status)
			{
				$status=trim($status);
				if(empty($status))continue;
				list($t,$d,$u)=explode(":",$status);

				if(!isset($users[$u])){
					$users[$u]=$this->db
					->select("username")
					->get_where("users",array(
						"id"=>$u
					))
					->row();

					$users[$u]->username=str_replace(",","&#044;",$users[$u]->username);
				}
				array_unshift($this->d['status_history'][$r->id],array_merge((array)$users[$u],array(
					"date_add_hmn"=>date("d.m.Y H:i:s",$d),
					"date_add"=>$d,
					"user_id"=>$u,
					"status"=>$t,
					"status_hmn"=>$this->order_status($t),
					"status_color"=>$this->order_status($t,true)
				)));
			}

			$price=$this->price($r->total_amount);
			if($r->total_amount_with_discount!=0){
				$price=$this->price($r->total_amount_with_discount).'<br /><small>без скидки: '.$this->price($r->total_amount).'</small>';
			}
			$rows[]=array(
				'<a href="'.$this->admin_url.'?m=shop&a=edit_order&id='.$r->id.'">Заказ №'.$r->id.'</a> <sup style="font-size:9px; color:gray;">'.($r->type=="quick"?"заказ в один клик":"").'</sup>',
				'<a href="#" class="order_status" data-id="'.$r->id.'" data-status="'.$r->status.'" onclick="openStatusModal(this); return false;" style="color:'.$this->order_status($r->status,true).';">'.$this->order_status($r->status).'</a>',
				$price,
				date("d.m.Y H:i:s",$r->date_add),
				"buttons"=>array(
					array("pdf",$this->admin_url."?m=shop&a=export&format=pdf&id=".$r->id),
					array("information",$this->admin_url."?m=shop&a=edit_order&id=".$r->id),
					array("cross",$this->admin_url."?m=shop&a=rm_order&id=".$r->id)
				)
			);
		}

		$this->ci->fb->add("table",array(
			"parent"=>"table",
			"head"=>array(
				"Номер заказа",
				"Статус",
				"На сумму",
				"Дата создания"
			),
			"rows"=>$rows,
			"pagination"=>$pagination->create_links()
		));

		$this->d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("shop/orders",$this->d);
	}

	function change_status()
	{
		$id=$this->input->post("id");
		$status=$this->input->post("status");

		$this->change_order_status($id,$status);

		$order_res=$this->db
		->get_where("shop_orders",array(
			"id"=>$id
		))
		->row();

		$this->d['status_history']=array();
		foreach(explode("\n",$order_res->status_history) AS $status)
		{
			$status=trim($status);
			if(empty($status))continue;
			list($t,$d,$u)=explode(":",$status);

			if(!isset($users[$u])){
				$users[$u]=$this->db
				->select("username")
				->get_where("users",array(
					"id"=>$u
				))
				->row();

				$users[$u]->username=str_replace(",","&#044;",$users[$u]->username);
			}
			$this->d['status_history'][]=array_merge((array)$users[$u],array(
				"date_add_hmn"=>date("d.m.Y H:i:s",$d),
				"date_add"=>$d,
				"user_id"=>$u,
				"status"=>$t,
				"status_hmn"=>$this->order_status($t),
				"status_color"=>$this->order_status($t,true)
			));
		}

		$this->d['status_history']=array_reverse($this->d['status_history']);

		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		print json_encode($this->d);
		exit;
	}

	function edit_order()
	{
		$this->add_order(true);
	}

	function add_order($edit=false)
	{
            if(!$this->ci->load->check_page_access_new("shop_orders","shop","module")) return;
            
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$id=intval($this->input->get("id"));

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=shop&a=orders");
		$this->buttons("form",$buttons);

		$this->d['order_res']=$this->db->
		get_where("shop_orders",array(
			"id"=>$id
		))
		->row();

		$this->d['order_res']->basket=json_decode($this->d['order_res']->basket);

		if($_POST['sms_send_declaration_sm']==1){
			$phone=preg_replace("#[^+0-9]#is","",$_POST['phone']);
                        
                        $sms_result = 1;

			if(!empty($phone)){
                            // проверка номера на формат +38ХХХХХХХХХХ
                            if((strpos($phone, '+38') !== 0) && (strlen($phone) == 10)){
                                // добавляем +38
                                $phone = '+38' . $phone;
                            }
                            
				$client=new SoapClient("http://turbosms.in.ua/api/wsdl.html");// , '+38'!!!
				$result=$client->Auth(array(
				"login"=>"NosiEto",
				"password"=>"2390980"
				));
				$result=$client->SendSMS(Array(
			        "sender"=>"NosiEto",
			        "destination"=>$phone,
			        "text"=>"номер декларации: ".$_POST['declaration']
	    		));
                                $soap_result = $result->SendSMSResult->ResultArray;
                                $sms_result = (is_array($soap_result)) ? $soap_result[0] : $soap_result;
                        }
                        else{
                            $sms_result = 'Не указан номер телефона!';
                        }
			print $sms_result;
			exit;
		}

		$product_ids=array_keys((array)$this->d['order_res']->basket->products);

		if(sizeof($product_ids)>0){

            $product_ids_clear = array();
            foreach($product_ids AS $product_id)
            {
                $product_ids_clear[] = current(explode(':', $product_id));
            }
            
			$this->d['products']=array();

			$q=$this
			->products_query();

			$q=$q
			->where("shop_products.id IN(".implode(",",$product_ids_clear).")")
			->get();

			$this->d['products_res']=$q->result();

			foreach($this->d['products_res'] AS $r)
			{
				$this->d['products'][$r->id]=$r;
			}
		}

		if(is_string($this->d['order_res']->basket->discount)){
			$this->d['order_res']->basket->discount=json_decode($this->d['order_res']->basket->discount);
		}
		if(is_string($this->d['order_res']->discount)){
			$this->d['order_res']->discount=json_decode($this->d['order_res']->discount);
		}

		foreach($this->d['order_res']->discount->discounts AS $discount)
		{
			$discount->discount_res=$this->db
			->get_where("shop_discounts",array(
				"id"=>$discount->id
			))
			->row();
		}
                
		if($id>0){
//            $id = current(explode(':', $id));
			$title_html=<<<EOF
<h4>Номер заказа: {$id}
EOF;
		}
		if($this->d['order_res']->type=="quick"){
			$title_html.=<<<EOF
 <sup style="font-size:9px; color:gray;">заказ в один клик</sup>
EOF;
		}
		$title_html.=<<<EOF
 <small style="font-size:10px;">( <a target="_blank" href="{$this->admin_url}?m=shop&a=export&format=pdf&id={$_GET['id']}">смета в pdf</a> )</small>
</h4>
EOF;

		$this->ci->fb->add("html",array(
			"content"=>$title_html,
			"parent"=>"greed"
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"ФИО",
			"name"=>"name",
			"parent"=>"greed",
			"value"=>$this->d['order_res']->name
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Телефон",
			"name"=>"phone",
			"parent"=>"greed",
			"value"=>$this->d['order_res']->phone
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"E-mail",
			"name"=>"email",
			"parent"=>"greed",
			"value"=>$this->d['order_res']->email
		));

		$this->ci->fb->add("textarea",array(
			"label"=>"Примечания к заказу",
			"name"=>"notes",
			"parent"=>"greed",
			"value"=>$this->d['order_res']->notes
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Метод доставки",
			"name"=>"delivery_method",
			"parent"=>"greed",
			"options"=>$this->delivery_methos,
			"value"=>$this->d['order_res']->delivery_method
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Адрес доставки",
			"name"=>"delivery_address",
			"parent"=>"greed",
			"value"=>$this->d['order_res']->delivery_address
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Город доставки",
			"name"=>"delivery_city",
			"parent"=>"greed",
			"value"=>$this->d['order_res']->delivery_city
		));
                
                $this->ci->fb->add("input:text",array(
			"label"=>"Страна доставки",
			"name"=>"delivery_country",
			"parent"=>"greed",
			"value"=>(!empty($this->d['order_res']->delivery_country)) ? $this->d['order_res']->delivery_country : 'Украина'
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Склад доставки",
			"name"=>"delivery_storage",
			"parent"=>"greed",
			"value"=>$this->d['order_res']->delivery_storage
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Получатель посылки",
			"name"=>"delivery_name",
			"parent"=>"greed",
			"value"=>$this->d['order_res']->delivery_name
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Номер декларации",
			"name"=>"declaration",
			"parent"=>"greed",
			"value"=>$this->d['order_res']->declaration,
			"append"=>'&nbsp;<button class="btn" id="sms_send_declaration_sm" type="button" style="position:relative; top:-5px;" onclick="sms_send_declaration();">отправить по SMS на номер: <span>'.$this->d['order_res']->phone.'</span></button>'
		));

		$order_info="";

		if(isset($this->d['order_res']->discount->discounts) && sizeof($this->d['order_res']->discount->discounts)){
			$order_info.=<<<EOF
<strong>Скидки которые были использованы:</strong><br />
<table id="status_history" class="table table-bordered" width="100%">
<tr>
	<th>Цена до</th>
	<th>Цена после</th>
	<th>Скидка</th>
	<th>Название</th>
</tr>
EOF;
			foreach($this->d['order_res']->discount->discounts AS $r)
			{
				$r->price_before_hmn=$this->price($r->price_before);
				$r->price_after_hmn=$this->price($r->price_after);
				
				$order_info.=<<<EOF
<tr>
	<td>{$r->price_before_hmn}</td>
	<td>{$r->price_after_hmn}</td>
	<td>{$r->discount}</td>
	<td>{$r->discount_res->title}</td>
</tr>
EOF;
			}
			$order_info.=<<<EOF
</table>
<br />
EOF;
		}

		$order_info.=<<<EOF
<strong>Товары:</strong><br />
<table id="products_list" class="table table-bordered" width="100%">
<tr>
	<th>Наименование</th>
	<!--th>Статус</th-->
	<th>Цена</th>
	<th>Количество</th>
	<th>Размер</th>
	<th>Цвет</th>
        <th>Дополнительно</th>
	<th>&nbsp;</th>
</tr>
EOF;
                
// курсы валют
$order_currency = 'usd';
$order_currency_sign = '$';
$e_rates = $this->db->select('var_name, value')->get('e_rates')->result_array();
if(!empty($e_rates)){
    foreach ($e_rates as $key => $e_rate){
        $e_rates[$e_rate['var_name']] = $e_rate['value'];
        unset($e_rates[$key]);
    }
}

// габариты заказчиков
$dimensions = array();
$dimensions_res = $this->db->get('shop_dimensions')->result();//var_dump($dimensions_res);
if(!empty($dimensions_res)){
    foreach ($dimensions_res as $dimension){
        $dimensions[$dimension->type_id][$dimension->name] = $dimension;
    }
}
//var_dump($dimensions);
		$manufacturers=array();
		foreach($this->d['order_res']->basket->products AS $product_id=>$r)
		{
			if(!isset($manufacturers[$r->brand_id])){
				$manufacturers[$r->brand_id]=$this->db->get_where("categoryes",array(
					"id"=>$r->brand_id,
					"type"=>"shop-manufacturer"
				))
				->row();
			}
                        
			$r->id=$product_id;
			$product=$this->d['products'][$product_id];

			// цена товара должна быть не актуальная, а та которая была при оформлении
			$product->price= ($r->currency == $order_currency) ? $r->price : ceil($r->price * $e_rates[$r->currency . '_' . $order_currency]);

			$product->title=$product->code." - ".$product->title;

			if(isset($manufacturers[$r->brand_id]) && !empty($manufacturers[$r->brand_id]->title)){
				$product->title.=" - ".$manufacturers[$r->brand_id]->title;
			}

			//$product->price_hmn=$this->price($product->price);
                        $product->price_hmn = $product->price . ' ' . $order_currency_sign;

			$product->price_total=$product->price*$r->quantity;
			//$product->price_total_hmn=$this->price($product->price_total);
                        $product->price_total_hmn = $product->price_total . ' ' . $order_currency_sign;

			$availability_res=$this->db
			->join("shop_suppliers","shop_suppliers.id = shop_suppliers_products_availability.supplier_id")
			->get_where("shop_suppliers_products_availability",array(
				"shop_suppliers_products_availability.product_id"=>$r->id,
				"shop_suppliers_products_availability.availability >"=>0
			))
			->result();

			$suppliers=array();
			foreach($availability_res AS $availability_r)
			{
				$suppliers[]=$availability_r->title." (".$availability_r->availability.")";
			}
			if(sizeof($suppliers)>0){
				$suppliers=implode(", ",$suppliers);
				$suppliers=" <sup><strong>{$suppliers}</strong></sup>";
			}else{
				$suppliers=" <sup>нет в наличии</sup>";
			}

			$product_status_hmn="";
			if(isset($r->status)){
				$product_status_hmn='<span style="color:'.$this->product_statuses[$r->status][1].';">'.$this->product_statuses[$r->status][0].'</span>';
			}
                        
                        $dimension_hmn = '';
                        if(!empty($r->dimensions)){
                            foreach ($r->dimensions as $dim_key => $dim_val){
                                $dimension_hmn .= (!empty($dimensions[$r->type_id][$dim_key]) && !empty($dim_val)) ? $dimensions[$r->type_id][$dim_key]->title . ': ' . $dim_val . ' ' . $dimensions[$r->type_id][$dim_key]->mark . '<br/>' : '';
                            }
                        }
            $p_size = (!empty($r->size)) ? $r->size : '';
            $p_color = (!empty($r->color)) ? '<img src="/' . ltrim($r->color, '/') . '" width="80px" />' : '';
			$order_info.=<<<EOF
<tr>
	<td><a target="_blank" href="{$this->admin_url}?m=shop&a=edit_product&id={$r->id}">{$r->title}</a><br/><br/>{$suppliers}</td>
	<!--td>{$product_status_hmn}</td-->
	<td><span class="onePrice" data-price="{$product->price}">{$product->price_hmn}</span> * <span class="quantity">{$r->quantity}</span> = <span class="totalPrice" data-price="{$product->price_total}">{$product->price_total_hmn}</span></td>
	<td align="center"><center><input type="text" name="quantity[{$r->id}]" style="width:30px; text-align:center;" value="{$r->quantity}" /></center></td>
	<td align="center" style="text-transform: uppercase; font-size: 20px; font-weight: bold; text-align: center; vertical-align: middle;">{$p_size}</td>
	<td align="center">{$p_color}</td>
        <td align="center">{$dimension_hmn}</td>
	<td align="center">
		<a href="{$this->admin_url}?m=shop&a=rm_order_product&id={$r->id}&order_id={$_GET['id']}"><img src="/templates/default/admin/assets/icons/cross.png" /></a>
	</td>
</tr>
EOF;
		}

		// пересчитываем стоимость товаров каждый раз!
		// $this->d['order_res']->basket->total_amount=$this->calc_products_amount((array)$this->d['order_res']->basket->products);

		$this->d['order_res']->basket->total_amount_hmn=$this->price($this->d['order_res']->basket->total_amount);
		$this->d['order_res']->total_amount_with_discount_hmn=$this->price($this->d['order_res']->total_amount_with_discount);
		$order_info.=<<<EOF
</table>


<script>
function openAddProductModal()
{
	$("#modelAddProduct").modal();
}

function openAddProduct(d)
{
	$.get("{$this->admin_url}?m=shop&a=get_product&id="+d.id,function(d){
		var html='';
		html+='<tr>';
		html+='<td style="background-color:#fffddb;"><a target="_blank" href="{$this->admin_url}?m=shop&a=edit_product&id='+d.product_res.id+'">'+d.product_res.title+'</a>'+d.product_suppliers+'</td>';
		html+='<td style="background-color:#fffddb;"><span class="onePrice" data-price="'+d.product_res.price+'">'+d.product_res.price_hmn+'</span> * <span class="quantity">1</span> = <span class="totalPrice" data-price="'+d.product_res.price+'">'+d.product_res.price_hmn+'</span></td>';
		html+='<td style="background-color:#fffddb;" align="center"><center><input type="text" name="quantity['+d.product_res.id+']" style="width:30px; text-align:center;" value="1" /></center></td>';
		html+='<td style="background-color:#fffddb;" align="center">';
		html+='<a onclick="$(this).parents(\'tr:eq(0)\').remove(); return false;" href="{$this->admin_url}?m=shop&a=rm_order_product&id='+d.product_res.id+'&order_id={$_GET['id']}"><img src="/templates/default/admin/assets/icons/cross.png" /></a>';
		html+='<input type="hidden" name="add_product[]" value="'+d.product_res.id+'">';
		html+='</td>';
		html+='</tr>';

		$("#products_list tr:last").after(html);
	});
}
</script>
<style>
#modelAddProduct {
    width: 950px;
    margin: -345px 0 0 -475px;
}
</style>
<div id="modelAddProduct" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Добавить товар в колекцию</h3>
  </div>
  <div class="modal-body">
    <iframe src="{$this->admin_url}?m=shop&a=products&iframe_display=1" style="width:930px; height:300px; border:none;" frameborder="0"></iframe>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Отмена</button>
  </div>
</div>
<button class="btn btn-mini" onclick="openAddProductModal(); return false;">Добавить товар</button>
<div class="clear"></div>
<br />
EOF;
		if($edit){
			$order_info.=<<<EOF
<strong>Итого:</strong> {$this->d['order_res']->basket->total_amount_hmn}
EOF;
		}
		if($this->d['order_res']->total_amount_with_discount>0){
			$order_info.=<<<EOF
<br />
<strong>Итого со скидкой:</strong> {$this->d['order_res']->total_amount_with_discount_hmn}
EOF;
		}
		// это не самовывоз, выводим информацию о доставке
		if($this->d['order_res']->delivery_method == 1){
			$order_info.=<<<EOF
<br /><br />
<strong>Доставка по Киеву:</strong> 50 грн
<br /><br />
<strong>Цена с доставкой:</strong> {$this->d['order_res']->discount->price_total_hmn} + 50 грн
EOF;
		}
		// print_r($this->d['order_res']->discount->delivery_hmn);
		$order_info.=<<<EOF
<br /><br />
EOF;
		

		$this->ci->fb->add("html",array(
			"content"=>$order_info,
			"parent"=>"greed"
		));

		$users=array();
		$status_history=array();
		foreach(explode("\n",trim($this->d['order_res']->status_history)) AS $h)
		{
			$h=trim($h);
			list($status,$date_add,$user_id)=explode(":",$h);

			if(!isset($users[$u])){
				$users[$u]=$this->db
				->select("username")
				->get_where("users",array(
					"id"=>$user_id
				))
				->row();
			}

			$status_history[]=array(
				"status"=>$status,
				"status_hmn"=>$this->order_status($status),
				"status_color"=>$this->order_status($status,true),
				"date_add"=>$date_add,
				"date_add_hmn"=>date("d.m.Y H:i:s",$date_add),
				"user_id"=>$user_id,
				"user"=>$users[$u],
			);
		}
		$status_history=array_reverse($status_history);

		$options=array();
		foreach($this->order_status() AS $key=>$txt)
		{
			$options[$key]=$txt;
		}

		unset($options[-1]);

		$options=array("0"=>"&nbsp;")+$options;

		$this->ci->fb->add("list:select",array(
			"label"=>"Статус",
			"name"=>"status_name",
			"parent"=>"greed",
			"options"=>$options,
                        "value"=>$this->d['order_res']->status
		));

		$status_history_html="";
		$status_history_html.=<<<EOF
<div style="border:1px solid #CDCDCD; height:300px; overflow:auto;">
<table id="status_history" class="table table-striped" width="100%">
<tr>
	<th>Статус</th>
	<th>Дата</th>
	<th>Пользователь</th>
</tr>
EOF;
		foreach($status_history AS $r)
		{
			$status_history_html.=<<<EOF
<tr>
	<td><span style="color:{$r['status_color']};">{$r['status_hmn']}</span></td>
	<td>{$r['date_add_hmn']}</td>
	<td>{$r['user']->username}<br /><small>id: {$r['user_id']}</small></td>
</tr>
EOF;
		}
		$status_history_html.=<<<EOF
</table>
</div>
EOF;

		if($edit){
			$this->ci->fb->add("html",array(
				"label"=>"История статусов",
				"content"=>$status_history_html,
				"parent"=>"greed"
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

		if($edit && !$this->ci->fb->submit){
			// $this->ci->fb->change("parent_id",array("value"=>$this->d['item_res']->parent_id));
			// $this->ci->fb->change("title",array("value"=>$this->d['item_res']->title));
		}

		if($this->ci->fb->submit){
			$this->d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($this->d['global_errors'])==0){
				$status_name=$this->input->post("status_name");

				

				// всегда сохраняем заказ
				$save_order=true;
				if($edit){
					$this->db
					->where("id",$id)
					->update("shop_orders",array(
						"delivery_method"=>$this->input->post("delivery_method"),
						"delivery_address"=>$this->input->post("delivery_address"),
						"delivery_city"=>$this->input->post("delivery_city"),
						"delivery_storage"=>$this->input->post("delivery_storage"),
						"delivery_name"=>$this->input->post("delivery_name"),
						"declaration"=>$this->input->post("declaration"),
						"name"=>$this->input->post("name"),
						"phone"=>$this->input->post("phone"),
						"email"=>$this->input->post("email"),
						"notes"=>$this->input->post("notes"),
						"discount"=>json_encode($new_discount),
						"date_update"=>mktime()
					));
				}else{
					$this->d['order_res']=(object)array();

					$this->db->insert("shop_orders",array(
						"user_id"=>$this->ci->session->userdata("user_id"),
						"delivery_method"=>$this->input->post("delivery_method"),
						"delivery_address"=>$this->input->post("delivery_address"),
						"delivery_city"=>$this->input->post("delivery_city"),
						"delivery_storage"=>$this->input->post("delivery_storage"),
						"delivery_name"=>$this->input->post("delivery_name"),
						"declaration"=>$this->input->post("declaration"),
						"name"=>$this->input->post("name"),
						"phone"=>$this->input->post("phone"),
						"email"=>$this->input->post("email"),
						"notes"=>$this->input->post("notes"),
						"discount"=>json_encode($new_discount),
						"date_add"=>mktime(),
						"date_update"=>mktime()
					));

					$id=$this->db->insert_id();
				}

				// обновляем товары в уже существующем заказе
				if(sizeof($_POST['quantity'])>0){
					foreach($_POST['quantity'] AS $product_id=>$quantity)
					{
						if(isset($this->d['order_res']->basket->products->{$product_id})){
							$this->d['order_res']->basket->products->{$product_id}->quantity=$quantity;

							$this->d['order_res']->basket->products->{$product_id}->price_total=$this->d['order_res']->basket->products->{$product_id}->price*$this->d['order_res']->basket->products->{$product_id}->quantity;
							$this->d['order_res']->basket->products->{$product_id}->price_total_hmn=$this->price($this->d['order_res']->basket->products->{$product_id}->price_total);
						}
					}
					$save_order=true;
				}

				// добавляем новые товары в заказ
				if(sizeof($_POST['add_product'])>0){
					foreach($_POST['add_product'] AS $product_id)
					{
						$quantity=$_POST['quantity'][$product_id];

						$product_query=$this->products_query();
						$this->d['order_res']->basket->products->{$product_id}=$product_query
						->select("shop_collection_products.collection_id")
						->select("brand.title AS brand_title")
						->join("categoryes AS brand","brand.id = shop_products.brand_id","left")
						->join("shop_product_types","shop_product_types.id = shop_products.type_id","left")
						->join("shop_collection_products","shop_collection_products.product_id = shop_products.id","left")
						->where("shop_products.id",$product_id)
						->get()
						->row();

						$this->d['order_res']->basket->products->{$product_id}->quantity=$quantity;

						$this->d['order_res']->basket->products->{$product_id}->price_hmn=$this->price($this->d['order_res']->basket->products->{$product_id}->price);
						$this->d['order_res']->basket->products->{$product_id}->price_total=$this->d['order_res']->basket->products->{$product_id}->price*$this->d['order_res']->basket->products->{$product_id}->quantity;
						$this->d['order_res']->basket->products->{$product_id}->price_total_hmn=$this->price($this->d['order_res']->basket->products->{$product_id}->price_total);
					}

					$save_order=true;
				}

				if($save_order){
					$new_total_amount=0;

					$products=array();
					foreach($this->d['order_res']->basket->products AS $ord_id=>$r)
					{
						$products[$ord_id]=$r->quantity;
					}
					$new_total_amount=$this->calc_products_amount($products,true);

					$calc_delivery=false;
					if($this->input->post("delivery_method")==1){
						// считаем только доставку по киеву
						$calc_delivery=true;
					}

					if(!isset($this->d['order_res']->basket->user_id)){
						$this->d['order_res']->basket->user_id=$this->ci->session->userdata("user_id");
					}

					$discount=$this->discount_price_calc($new_total_amount,$calc_delivery,$this->d['order_res']->basket->user_id);

					$this->d['order_res']->basket->total_amount=$new_total_amount;
					$this->d['order_res']->basket->total_amount_hmn=$this->price($new_total_amount);
					$this->d['order_res']->total_amount=$discount['price_total'];
					$this->d['order_res']->total_amount_hmn=$this->price($discount['price_total']);
					$this->d['order_res']->total_amount_with_discount=$discount['price'];
					$this->d['order_res']->discount=json_encode($discount);

					$this->db
					->where("id",$id)
					->update("shop_orders",array(
						"basket"=>json_encode($this->d['order_res']->basket),
						"total_amount"=>$this->d['order_res']->total_amount,
						"total_amount_with_discount"=>$this->d['order_res']->total_amount_with_discount,
						"discount"=>$this->d['order_res']->discount
					));
				}

				if($status_name!==false && $status_name!="0"){
					$this->change_order_status($id,$status_name);
				}

				redirect($this->admin_url."?m=shop&a=orders");
			}
		}

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("shop/order",$this->d);
	}

	public function discounts()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->buttons("main",array(
			array("add","Добавить<br />скидку",$this->admin_url."?m=shop&a=add_discount")
		));

		$this->d['discounts_res']=$this->db
		->order_by("date_add DESC")
		->get("shop_discounts")
		->result();

		$rows=array();
		foreach($this->d['discounts_res'] AS $r)
		{
			$rows[]=array(
				$r->title,
				"enabled"=>array($this->admin_url."?m=shop&a=enabled_discount&id=".$r->id."&enable=",$r->show),
				"<center>".$r->discounts."</center>",
				"buttons"=>array(
					array("pencil",$this->admin_url."?m=shop&a=edit_discount&id=".$r->id),
					array("cross",$this->admin_url."?m=shop&a=rm_discount&id=".$r->id)
				)
			);
		}

		$this->ci->fb->add("table",array(
			"parent"=>"table",
			"head"=>array(
				"Название",
				"Включена",
				"Скидка"
			),
			"rows"=>$rows
		));

		$this->d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("shop/discounts",$this->d);
	}

	public function edit_discount()
	{
		$_GET['id']=intval($_GET['id']);
		$this->add_discount(true);
	}

	public function add_discount($edit=false)
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=shop&a=discounts");
		$this->buttons("form",$buttons);

		if($edit){
			$this->d['item_res']=$this->db
			->get_where("shop_discounts",array("shop_discounts.id"=>$_GET['id']))
			->row();
		}

		$options=array(
			"user"=>"Скидка пользователю",
			"user_group"=>"Скидка для группы пользователей"
		);

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed"
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Скидка",
			"description"=>"например в процентах -10% или фикс. сумма -20",
			"name"=>"discounts",
			"parent"=>"greed"
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Тип скидки",
			"name"=>"type",
			"parent"=>"greed",
			"options"=>$options
		));

		$options=array();
		$this->d['groups_res']=$this->db
		->get("groups")
		->result();
		foreach($this->d['groups_res'] AS $r)
		{
			$options[$r->id]=$r->name;
		}

		$this->ci->fb->add("user",array(
			"label"=>"Пользователь",
			"name"=>"user_id",
			"parent"=>"greed",
			"hidden"=>true,
			"class"=>"hidden_fields hidden_additional_user"
		));

		$this->ci->fb->add("list:select",array(
			"label"=>"Группа пользователей",
			"name"=>"group_id",
			"parent"=>"greed",
			"options"=>$options,
			"hidden"=>true,
			"class"=>"hidden_fields hidden_additional_user_group"
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
			$this->ci->fb->change("title",array("value"=>$this->d['item_res']->title));
			$this->ci->fb->change("discounts",array("value"=>$this->d['item_res']->discounts));
			$this->ci->fb->change("type",array("value"=>$this->d['item_res']->type));

			if($this->d['item_res']->type=="user"){
				$this->ci->fb->change("user_id",array("value"=>$this->d['item_res']->extra_id));
			}elseif($this->d['item_res']->type=="user_group"){
				$this->ci->fb->change("group_id",array("value"=>$this->d['item_res']->extra_id));
			}
		}

		if($this->ci->fb->submit){
			$this->d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($this->d['global_errors'])==0){
				if($this->input->post("type")=="user"){
					$extra_id=intval($this->input->post("user_id"));
				}elseif($this->input->post("type")=="user_group"){
					$extra_id=intval($this->input->post("group_id"));
				}

				if($edit){
					$this->db
					->where("id",$_GET['id'])
					->update("shop_discounts",array(
						"title"=>$this->input->post("title"),
						"type"=>$this->input->post("type"),
						"discounts"=>$this->input->post("discounts"),
						"extra_id"=>$extra_id
					));
				}else{
					$this->db
					->insert("shop_discounts",array(
						"title"=>$this->input->post("title"),
						"type"=>$this->input->post("type"),
						"discounts"=>$this->input->post("discounts"),
						"extra_id"=>$extra_id
					));
				}
				
				redirect($this->admin_url."?m=shop&a=discounts");
			}
		}

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("shop/add_discount",$this->d);
	}

	public function rm_order_product()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$id=(int)$this->input->get("id");
		$order_id=(int)$this->input->get("order_id");

		$res=$this->db
		->get_where("shop_orders",array(
			"id"=>$order_id
		))
		->row();

		if(is_string($res->basket))$res->basket=json_decode($res->basket);
		
		// удаляем товар
		unset($res->basket->products->{$id});

		$new_total_amount=0;

		$products=array();
		foreach($res->basket->products AS $id=>$r)
		{
			$products[$id]=$r->quantity;
		}
		$new_total_amount=$this->calc_products_amount($products);

		$calc_delivery=true;
		if($res->delivery_method==0){
			// самовывоз, доставку не считаем
			$calc_delivery=false;
		}

		$discount=$this->discount_price_calc($new_total_amount,$calc_delivery,$res->basket->user_id);

		$res->basket->total_amount=$new_total_amount;
		$res->basket->total_amount_hmn=$this->price($new_total_amount);
		$res->total_amount=$discount['price_total'];
		$res->total_amount_with_discount=$discount['price'];
		$res->discount=json_encode($discount);
		
		$this->db
		->where("id",$order_id)
		->update("shop_orders",array(
			"basket"=>json_encode($res->basket),
			"total_amount"=>$res->total_amount,
			"total_amount_with_discount"=>$res->total_amount_with_discount,
			"discount"=>$res->discount
		));

		redirect($this->admin_url."?m=shop&a=edit_order&id=".$order_id);
	}

	public function rm_order()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$id=(int)$this->input->get("id");

		$this->db
		->where("id",$id)
		->delete("shop_orders");

		redirect($this->admin_url."?m=shop&a=orders");
	}

	public function rm_discount()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$id=(int)$this->input->get("id");

		$this->db
		->where("id",$id)
		->delete("shop_discounts");

		redirect($this->admin_url."?m=shop&a=discounts");
	}

	public function enabled_discount()
	{
		$this->db
		->where("id",$this->input->get("id"))
		->update("shop_discounts",array(
			"show"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=shop&a=discounts");
	}

	public function repair()
	{
		$this->repair_categories();

		$products_res=$this->db
		->select("id")
		->get("shop_products")
		->result();

		foreach($products_res AS $r)
		{
			$this->repair_product($r->id);
			
			print "Product ID: ".$r->id."<hr />";
		}

		print "<br /><br />END OK!";
	}

	public function rm_product_type()
	{
		$id=(int)$this->input->get("id");
		
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->db
		->where("id",$id)
		->delete("shop_product_types");

		redirect($this->admin_url."?m=shop&a=product_types");
		
	}

	public function export()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$id=intval($this->input->get("id"));

		$this->d['order_res']=$this->db->
		get_where("shop_orders",array(
			"id"=>$id
		))
		->row();

		$this->d['order_res']->basket=json_decode($this->d['order_res']->basket);
                
		$product_ids=array_keys((array)$this->d['order_res']->basket->products);
                if(!empty($product_ids)){
                    foreach ($product_ids as $key => $val){
                        if(strpos($val, ':')){
                            $product_ids[$key] = current(explode(':', $val)); // для идиотских ProductID вида: '1380:0_dcd290a2bd0fd5a3ba60a0327560c99c.jpg:' – этож писдец ваще
                        }
                    }
                }

		if(sizeof($product_ids)>0){
			$this->d['products']=array();
			$this->d['products_res']=$this
			->products_query()
			->where("shop_products.id IN(".implode(",",$product_ids).")")
			->get()
                        ->result();
                
			foreach($this->d['products_res'] AS $r)
			{
				$this->d['products'][$r->id]=$r;
			}
		}

		if(is_string($this->d['order_res']->basket->discount)){
			$this->d['order_res']->basket->discount=json_decode($this->d['order_res']->basket->discount);
		}
		if(is_string($this->d['order_res']->discount)){
			$this->d['order_res']->discount=json_decode($this->d['order_res']->discount);
		}

		foreach($this->d['order_res']->discount->discounts AS $discount)
		{
			$discount->discount_res=$this->db
			->get_where("shop_discounts",array(
				"id"=>$discount->id
			))
			->row();
		}
                
		$months=array(
			"січня",
			"лютого",
			"березня",
			"квітня",
			"травня",
			"червня",
			"липня",
			"серпня",
			"вересня",
			"жовтня",
			"листопада",
			"грудня"
		);

		$date=date("d",$this->d['order_res']->date_update)." ".$months[intval(date("m",$this->d['order_res']->date_update))-1]." ".date("Y",$this->d['order_res']->date_update)." р.";

		$html=<<<EOF
<style>
td, th, body, p, div {
	font-family:Verdana;
}

#products {
	 border-top:1px solid #000000;
}
#products td, #products th {
	font-size:10pt;
	padding:3px;
	border-right:1px solid #000000;
	border-bottom:1px solid #000000;
}

#products td {
	font-weight:normal;
}

#ln {
	border-bottom:1px solid #000000;
	width:300pt;
}
</style>
<table border="0">
<tr>
	<td valign="top" width="150">
		<strong>Постачальник</strong>
	</td>
	<td valign="top">
		<p>Інтернет-магазин "Носи Это"</p>
		<p>тел. (068) 962-44-36, (066) 124-51-47</p>
		<p>Адреса: м.Київ</p>
	</td>
</tr>
<tr>
	<td colspan="2" height="10"></td>
</tr>
<tr>
	<td valign="top">
		<strong>Одержувач</strong>
	</td>
	<td valign="top">
		<p>{$this->d['order_res']->name}</p>
		<p>тел. {$this->d['order_res']->phone}</p>
	</td>
</tr>
</table>
<br />
<div align="center" style="text-align:center;">
	<p><strong>Видаткова накладна  № {$id}</strong><br />
	<strong>від {$date}</strong></p>
</div>
<br />
<table id="products" border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
	<th style="border-left:1px solid #000000;" bgcolor="#CDCDCD">№</th>
	<th bgcolor="#CDCDCD">Товар</th>
	<th bgcolor="#CDCDCD">Статус</th>
	<th bgcolor="#CDCDCD">Кількість, шт</th>
	<th bgcolor="#CDCDCD">Ціна</th>
	<th bgcolor="#CDCDCD">Сума</th>
</tr>
EOF;
                
        // статусы товара
        $statuses_res = $this->db->select('id, type_id, params')->get_where('shop_product_type_fields', array('title' => 'Статус товара'))->result_array();
        $statuses = array();
        if(!empty($statuses_res)){
            foreach ($statuses_res as $item){
                $not_js = json_decode($item['params']);
                if(!empty($not_js->options)){
                    $statuses[$item['type_id']] = array('field' => 'f_' . $item['id'], 'options' => $not_js->options);
                }
            }
        }

        // курсы валют
        $e_rates = $this->db->select('var_name, value')->get('e_rates')->result_array();
        if(!empty($e_rates)){
            foreach ($e_rates as $key => $e_rate){
                $e_rates[$e_rate['var_name']] = $e_rate['value'];
                unset($e_rates[$key]);
            }
        }
        
        // цена товара в определенной валюте
        $export_currency = 'grn'; // валюта экспорта
        $currencys = array(
            'grn' => 'грн',
            'usd' => '$',
            'eur' => '&euro;',
        );
        // общая стоимость заказа
        $total_price = 0;
        // стоимость доставки
        $delivery_price = ($this->d['order_res']->delivery_method == 1) ? 50 : 0; // только в grn!
        
		$i=1;
		foreach($this->d['order_res']->basket->products AS $r)
		{
			if(!isset($manufacturers[$r->brand_id])){
				$manufacturers[$r->brand_id]=$this->db->get_where("categoryes",array(
					"id"=>$r->brand_id,
					"type"=>"shop-manufacturer"
				))
				->row();
			}
                        
                        if(strpos($r->id, ':')){
                            $r->id = current(explode(':', $r->id)); // для идиотских ProductID вида: '1380:0_dcd290a2bd0fd5a3ba60a0327560c99c.jpg:' – этож писдец ваще
                        }

			$product=$this->d['products'][$r->id];
                        
                        // статус товара - использовать один из вариантов
                        // на момент заказа
                        $status = (!empty($statuses[$r->type_id])) ? $statuses[$r->type_id]['options'][$r->{$statuses[$r->type_id]['field']}] : '';
                        // на данный момент
                        //$status = (!empty($statuses[$product->type_id])) ? $statuses[$product->type_id]['options'][$product->{$statuses[$product->type_id]['field']}] : '';

			// цена товара должна быть не актуальная, а та которая была при оформлении
			$product->price=$r->price;

			$product->title=$product->code." - ".$product->title;

			if(isset($manufacturers[$r->brand_id]) && !empty($manufacturers[$r->brand_id]->title)){
				$product->title.=" - ".$manufacturers[$r->brand_id]->title;
			}

			$product->price_hmn=$this->price($product->price);

			$product->price_total=$product->price*$r->quantity;
			$product->price_total_hmn=$this->price($product->price_total);

			$availability_res=$this->db
			->join("shop_suppliers","shop_suppliers.id = shop_suppliers_products_availability.supplier_id")
			->get_where("shop_suppliers_products_availability",array(
				"shop_suppliers_products_availability.product_id"=>$r->id,
				"shop_suppliers_products_availability.availability >"=>0
			))
			->result();

			$suppliers=array();
			foreach($availability_res AS $availability_r)
			{
				$suppliers[]=$availability_r->title." (".$availability_r->availability.")";
			}
			if(sizeof($suppliers)>0){
				$suppliers=implode(", ",$suppliers);
				$suppliers=" <sup><strong>{$suppliers}</strong></sup>";
			}else{
				$suppliers=" <sup>нет в наличии</sup>";
			}
                        //var_dump($product);
                        
                        // пересчитываем
                        if($product->currency !== $export_currency){
                            // пересчитываем стоимость по курсу
                            $product->price_hmn = ceil($product->price * $e_rates[($product->currency . '_' . $export_currency)]) . ' ' . $currencys[$export_currency]; // цена товара
                            $product->price_total_hmn = ceil($product->price_total * $e_rates[($product->currency . '_' . $export_currency)]) . ' ' . $currencys[$export_currency]; // стоимость товара (одна или несколько единиц одного товара)
                            $product->price_total = ceil($product->price_total * $e_rates[($product->currency . '_' . $export_currency)]);
                        }
                        else{
                            // цепляем к цене и стоимости обозначение валюты экспорта
                            $product->price_hmn = $product->price . ' ' . $currencys[$export_currency]; // цена
                            $product->price_total_hmn=$product->price_total . ' ' . $currencys[$export_currency]; // стоимость
                        }
                        
                        // увеличиваем общую стоимость заказа на стоимость товара (одна или несколько единиц одного товара)
                        $total_price += (int)$product->price_total;
                        
			// {$suppliers}
			$html.=<<<EOF
<tr>
	<td align="right" style="border-left:1px solid #000000;">{$i}</td>
	<td>{$product->title}</td>
	<td align="center">{$status}</td>
	<td align="right">{$r->quantity}</td>
	<td align="right">{$product->price_hmn}</td>
	<td align="right">{$product->price_total_hmn}</td>
</tr>
EOF;
			$i++;
		}
                
		if($this->d['order_res']->delivery_method!=1){
			$this->d['order_res']->discount->delivery_hmn="";
		}
                // способ доставки
		$delivery_method_hmn=$this->delivery_methos[$this->d['order_res']->delivery_method];
		if(!empty($delivery_method_hmn))$delivery_method_hmn=" (".$delivery_method_hmn.")";
                
                // общая стоимость заказа
                $total_price_hmn = $total_price . ' ' . $currencys[$export_currency];
                // доставка
                $delivery_price_hmn = (!empty($delivery_price)) ? $delivery_price . ' ' . $currencys[$export_currency] : "";
                // общая стоимость заказа с доставкой
                $total_with_delivery_price = ($total_price + $delivery_price);
                $total_with_delivery_price_hmn = $total_with_delivery_price . ' ' . $currencys[$export_currency];
                
                $num2str=num2str($total_with_delivery_price);
$html.=<<<EOF
<tr>
	<td colspan="4" style="border:none; border-right:1px solid #000;"></td>
	<td align="right"><strong>Разом:</strong></td>
	<td align="right">{$total_price_hmn}</td>
</tr>
<tr>
	<td colspan="4" style="border:none; border-right:1px solid #000;"></td>
	<td align="right"><strong>Доставка{$delivery_method_hmn}:</strong></td>
	<td align="right" style="border-rigth:1px solid #000;">{$delivery_price_hmn}</td>
</tr>
<tr>
	<td colspan="4" style="border:none; border-right:1px solid #000;"></td>
	<td align="right"><strong>Всього з доставкою:</strong></td>
	<td align="right">{$total_with_delivery_price_hmn}</td>
</tr>
</table>

<table border="0" width="100%">
<tr>
	<td>Всього на суму:</td>
</tr>
<tr>
	<td><strong>{$num2str}</strong></td>
</tr>
</table>
<br />
<table border="0" width="100%">
<tr>
	<td width="50">Відвантажив(ла)</td>
	<td width="300" style="border-bottom:1px solid #000000;">&nbsp;</td>
	<td></td>
	<td width="50">Отримав(ла)</td>
	<td width="120" style="border-bottom:1px solid #000000;">&nbsp;</td>
</tr>
<tr>
	<td width="50"></td>
	<td width="300"></td>
	<td></td>
	<td width="50"></td>
	<td width="120" style="font-size:8pt; text-align:center;">Підпис покупця</td>
</tr>
</table>
<br />
<center>Товар зі статусом "В наличии" можливо повернути протягом 14 днів за умови збереження товарного вигляду товару та упаковки, при наявності даного документу.<br/> 
Для товарів зі статусом "Предзаказ" повернення та обмін товару не передбачено.</center>
EOF;
//echo $html; return; // debug
		include_once("./application/libraries/MPDF56/mpdf.php");
		$mpdf=new mPDF(); 
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		exit;
	}

	public function get_product()
	{
		$id=intval($this->input->get("id"));

		$this->d['product_res']=$this
		->products_query()
		->where("shop_products.id",$id)
		->get()
		->row();

		$this->d['availability_res']=$this->db
		->join("shop_suppliers","shop_suppliers.id = shop_suppliers_products_availability.supplier_id")
		->get_where("shop_suppliers_products_availability",array(
			"shop_suppliers_products_availability.product_id"=>$this->d['product_res']->id,
			"shop_suppliers_products_availability.availability >"=>0
		))
		->result();

		$this->d['product_suppliers']=array();
		foreach($this->d['availability_res'] AS $availability_r)
		{
			$this->d['product_suppliers'][]=$availability_r->title." (".$availability_r->availability.")";
		}
		if(sizeof($this->d['product_suppliers'])>0){
			$this->d['product_suppliers']=implode(", ",$this->d['product_suppliers']);
			$this->d['product_suppliers']=" <sup><strong>{$this->d['product_suppliers']}</strong></sup>";
		}else{
			$this->d['product_suppliers']=" <sup>нет в наличии</sup>";
		}

		$this->d['product_res']->price_hmn=$this->price($this->d['product_res']->price);

		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		print json_encode($this->d);
	}
        
        /**
        * Create a web friendly URL slug from a string.
        * 
        * Although supported, transliteration is discouraged because
        *     1) most web browsers support UTF-8 characters in URLs
        *     2) transliteration causes a loss of information
        * Cist:
        * https://gist.github.com/sgmurphy/3098978
        *
        * @author Sean Murphy <sean@iamseanmurphy.com>
        * @copyright Copyright 2012 Sean Murphy. All rights reserved.
        * @license http://creativecommons.org/publicdomain/zero/1.0/
        *
        * @param string $str
        * @param array $options
        * @return string
        */
        public function url_slug($str, $options = array()) {
            // Make sure string is in UTF-8 and strip invalid UTF-8 characters
            $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

            $defaults = array(
                    'delimiter' => '-',
                    'limit' => null,
                    'lowercase' => true,
                    'replacements' => array(),
                    'transliterate' => false,
            );

            // Merge options
            $options = array_merge($defaults, $options);

            $char_map = array(
                    // Latin
                    'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
                    'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
                    'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 
                    'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
                    'ß' => 'ss', 
                    'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
                    'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
                    'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 
                    'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 
                    'ÿ' => 'y',

                    // Latin symbols
                    '©' => '(c)',

                    // Greek
                    'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
                    'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
                    'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
                    'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
                    'Ϋ' => 'Y',
                    'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
                    'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
                    'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
                    'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
                    'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

                    // Turkish
                    'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
                    'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 

                    // Russian
                    'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
                    'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
                    'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
                    'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
                    'Я' => 'Ya',
                    'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
                    'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
                    'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
                    'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
                    'я' => 'ya',

                    // Ukrainian
                    'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
                    'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

                    // Czech
                    'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 
                    'Ž' => 'Z', 
                    'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
                    'ž' => 'z', 

                    // Polish
                    'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
                    'Ż' => 'Z', 
                    'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
                    'ż' => 'z',

                    // Latvian
                    'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 
                    'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
                    'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
                    'š' => 's', 'ū' => 'u', 'ž' => 'z'
            );

            // Make custom replacements
            $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

            // Transliterate characters to ASCII
            if ($options['transliterate']) {
                    $str = str_replace(array_keys($char_map), $char_map, $str);
            }

            // Replace non-alphanumeric characters with our delimiter
            $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

            // Remove duplicate delimiters
            $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

            // Truncate slug to max. characters
            $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

            // Remove delimiter from ends
            $str = trim($str, $options['delimiter']);

            return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
       }

    /**
     * если продукт участвует в активной акции
     * то возвращает массив с информацией о скидке,
     * назначенной продукту в данной активной акции
     * если не участвует – то false
     * @param $product_id
     * @return bool|array
     */
    public function check_product_action($product_id)
    {
        $active_action = $this->db
            ->where('active', 1)
            ->limit(1)
            ->get('action')->row_array();
        if(!empty($active_action)){
            $check_product = $this->db
                ->where(array(
                    'action_id' => $active_action['id'],
                    'product_id' => $product_id,
                ))
                ->get('action_product')->row_array();
            if(!empty($check_product)) {
                $check_product['action_info'] = $active_action;
            }
            return $check_product;
        }
        return false;
    }
}
?>