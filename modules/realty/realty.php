<?php
include_once("./modules/realty/realty.helper.php");

class realtyModule extends realtyModuleHelper {
	function __construct()
	{
		parent::__construct();
	}

	public function search()
	{
		$where=array();

		if($this->input->get("keywords")!==false && $this->input->get("keywords")!=""){
			$keywords=search_clear_text($this->input->get("keywords"));

			$where['((CONCAT(realty_items.title,\' \',realty_items.code) LIKE \'%'.str_replace(" ","%' || CONCAT(realty_items.title,' ',realty_items.code) LIKE '%",$keywords).'%\') || realty_items.code=\''.mysql_escape_string($this->input->get("keywords")).'\')']=NULL;
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
			->join("uploads","uploads.extra_id = categoryes.id && uploads.name = 'category_image' && uploads.component_name = 'realty'","left")
			->get_where("categoryes",array(
				"categoryes.id"=>$_GET['category_id']
			))->row();

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
						"realty_items_categories_link","realty_items_categories_link.item_id = realty_items.id && realty_items_categories_link.category_id IN(".$matches[1].")"
					)
				)
			));
			return false;
		}elseif(preg_match("#-([0-9]+)m/[^/]*$#is",$_SERVER['REQUEST_URI'],$matches)){
			$_GET['manufacturer_id']=$matches[1];
			$this->manufacturer_view();
			// $this->category(array(
			// 	"realty_items.brand_id"=>$matches[1]
			// ));
			return false;
		}elseif(preg_match("#-([0-9]+)\.html(\?.*)?$#is",$_SERVER['REQUEST_URI'],$matches)){
			$_GET['item_id']=$matches[1];
			$this->view_item();
			return false;
		}
	}

	public function category($where=NULL,$d=NULL)
	{
		$this->d['category_res']=&$this->category_res;
		if(is_string($this->url_structure_res->options)){
			$this->url_structure_res->options=json_decode($this->url_structure_res->options);
		}

		if(preg_match("#-([0-9]+)\.html$#is",$_SERVER['REQUEST_URI'],$matches)){
			$_GET['id']=$matches[1];
			$this->view_post();
			return false;
		}

		$this->items_query=$this
		->items_query()
		->where("show",1);

		$category_id=intval($this->ci->url_structure_res->options->category_id);
		if($category_id>0){
			$this->items_query->where("realty_items.category_ids regexp '[[:<:]](" . implode ( '|',array($category_id)) . ")[[:>:]]'");

			$this->d['category_res']=$this->db->get_where("categoryes",array(
				"id"=>$category_id
			))
			->row();

			if($default_language->name!=$this->ci->config->config['language']){
				foreach($this->ci->languages_res AS $r2)
				{
					if($r2->enabled!=1)continue;
					
					if($r2->name==$this->ci->config->config['language']){
						$this->d['category_res']->title=$this->d['category_res']->{"l_title_".$r2->code};
						$this->d['category_res']->description=$this->d['category_res']->{"l_description_".$r2->code};
					}
				}
			}
		}

		$this->items_query=$this->items_query->get();

		if($this->items_query===false){
			die("no items!");
		}

		$this->d['items_res']=$this->items_query->result();

		$default_language=array();
		foreach($this->ci->languages_res AS $r2)
		{
			if($r2->default==1){
				$default_language=$r2;
				break;
			}
		}

		foreach($this->d['items_res'] AS $r)
		{
			$r->price_hmn=$this->price($r->price);
			$r->link=$this->link_item_view($r);

			if($default_language->name!=$this->ci->config->config['language']){
				foreach($this->ci->languages_res AS $r2)
				{
					if($r2->enabled!=1)continue;
					
					if($r2->name==$this->ci->config->config['language']){
						$r->title=$r->{"l_title_".$r2->code};
						$r->location=$r->{"l_location_".$r2->code};
						$r->short_desc=$r->{"l_short_desc_".$r2->code};
						$r->full_desc=$r->{"l_full_desc_".$r2->code};
						$r->params=$r->{"l_params_".$r2->code};
					}
				}
			}
		}

		$this->ci->load->frontView("realty/items_list",$this->d);
	}

	public function view_item()
	{
		$item_id=(int)$this->input->get("item_id");

		$item_query=$this->items_query();

		$item_query=$item_query
		->where("realty_items.id",$item_id)
		->get();

		if($item_query===false)show_404();

		$this->d['item_res']=$item_query->row();

		$default_language=array();
		foreach($this->ci->languages_res AS $r2)
		{
			if($r2->default==1){
				$default_language=$r2;
				break;
			}
		}

		if($default_language->name!=$this->ci->config->config['language']){
			foreach($this->ci->languages_res AS $r2)
			{
				if($r2->enabled!=1)continue;
				
				if($r2->name==$this->ci->config->config['language']){
					$this->d['item_res']->title=$this->d['item_res']->{"l_title_".$r2->code};
					$this->d['item_res']->location=$this->d['item_res']->{"l_location_".$r2->code};
					$this->d['item_res']->short_desc=$this->d['item_res']->{"l_short_desc_".$r2->code};
					$this->d['item_res']->full_desc=$r->{"l_full_desc_".$r2->code};
					$this->d['item_res']->params=$r->{"l_params_".$r2->code};
					$this->d['item_res']->meta_title=$r->{"l_meta_title_".$r2->code};
					$this->d['item_res']->meta_keywords=$r->{"l_meta_keywords_".$r2->code};
					$this->d['item_res']->meta_description=$r->{"l_meta_description_".$r2->code};
				}
			}
		}

		$this->d['categoryes_res']=$this->db
		->select("categoryes.*")
		->join("categoryes","categoryes.id = realty_items_categories_link.category_id")
		->get_where("realty_items_categories_link",array(
			"item_id"=>$item_id
		))
		->result();

		$this->d['item_res']->price_hmn=$this->price($this->d['item_res']);

		$this->ci->load->meta($this->d['item_res']->meta_title,"title");
		$this->ci->load->meta($this->d['item_res']->meta_description,"description");
		$this->ci->load->meta($this->d['item_res']->meta_keywords,"keywords");

		// получаем все фотографии
		$this->d['item_photos_res']=$this->db
		->order_by("uploads.order")
		->get_where("uploads",array(
			"name"=>"item-photo",
			"component_type"=>"module",
			"component_name"=>"realty",
			"extra_id"=>$this->d['item_res']->id
		))
		->result();

		$this->ci->load->meta(base_url($this->link_item_view($this->d['item_res'])),"og:url"); 
		$this->ci->load->meta($this->d['item_res']->title,"og:title");
		$this->ci->load->meta($this->d['item_res']->short_description,"og:description");
		if(!empty($this->d['item_res']->main_picture_file_name)){
			$this->ci->load->meta(base_url($this->d['item_res']->main_picture_file_path.$this->d['item_res']->main_picture_file_name),"og:image");
		}

		$this->ci->load->frontView("realty/view_item",$this->d);
	}

	public function add_item_to_cart()
	{
		$cart=$this->get_cart();

		$item_id=intval($this->input->get("item_id"));
		$quantity=intval($this->input->get("quantity"));
		$type=intval($this->input->get("type"));

		if($item_id<1)return false;

		if($quantity<1)$quantity=1;


		if($type=="add"){
			if(!isset($cart->items->{$item_id})){
				$cart->items->{$item_id}->quantity=$quantity;
			}else{
				if(!isset($cart->items->{$item_id}->quantity)){
					$cart->items->{$item_id}->quantity=1;
				}else{
					$cart->items->{$item_id}->quantity+=$quantity;
				}
			}
		}else{
			if(isset($cart->items->{$item_id})){
				unset($cart->items->{$item_id});
			}
		}
		
		$cart=$this->save_cart($cart,true);

		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		print json_encode(array(
			"items_num"=>sizeof((array)$cart->items),
			"price"=>$cart->total_amount,
			"price_hmn"=>$this->price($cart->total_amount)
		));
	}

	public function categories_list()
	{
		$where=array(
			"categoryes.type"=>"realty-category",
			"categoryes.show"=>1
		);

		if($_GET['category_id']>0){
			$where['categoryes.parent_id']=$_GET['category_id'];
		}

		$this->d['categories_res']=$this->db
		->select("uploads.file_path, uploads.file_name")
		->select("categoryes.*")
		->join("uploads","uploads.extra_id = categoryes.id && uploads.name = 'category_image' && uploads.component_name = 'realty'","left")
		->order_by("categoryes.order")
		->get_where("categoryes",$where)
		->result();

		$this->ci->load->frontView("realty/categories_list",$this->d);
	}

	public function cart()
	{
		$this->d['delivery_methos']=$this->delivery_methos;
		$this->d['cart']=$this->get_cart($cart,true);

		if($this->input->post("recalc_sm")!==false){
			$quantity=$this->input->post("quantity");
			foreach($this->d['cart']->items AS $item_id=>$item_r)
			{
				if(isset($quantity[$item_id])){
					$quantity[$item_id]=intval($quantity[$item_id]);
					if($quantity[$item_id]>0){
						$item_r->quantity=$quantity[$item_id];
					}
				}else{
					unset($this->d['cart']->items->{$item_id});
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
			$orders_num=$this->db->count_all_results("realty_orders");
			if($orders_num<1){
				$create_order=true;
			}
		}

		if($create_order){
			$this->db->insert("realty_orders",array(
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
		->get_where("realty_orders",array(
			"id"=>$this->d['cart']->order_id
		))
		->row();

		$this->d['order_errors']=array();

		$item_ids=array_keys((array)$this->d['cart']->items);

		if(sizeof($item_ids)>0){
			$this->d['cart_items_res']=$this
			->items_query()
			->where(array(
				"realty_items.id IN(".implode(",",$item_ids).")"=>NULL
			))
			->get()
			->result();
		}

		foreach($this->d['cart_items_res'] AS $r)
		{
			$r->quantity=$this->d['cart']->items->{$r->id}->quantity;
			$r->price_hmn=$this->item_price($r);
			$r->price_total=$r->price*$r->quantity;
			$r->price_total_hmn=$this->price($r->price_total);
			$this->d['cart']->items->{$r->id}=$r;
		}

		// $ids=array();
		// foreach($this->d['cart']->items AS $r)
		// {
		// 	$ids[$r->id]=$r->quantity;
		// }
		// $this->d['cart']->total_amount=$this->calc_items_amount($ids);

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
				if(empty($email))$this->d['order_errors'][]="Поле \"E-mail\" не заполнено!";
				if(empty($name))$this->d['order_errors'][]="Поле \"ФИО\" не заполнено!";
				if(empty($phone))$this->d['order_errors'][]="Поле \"Телефон\" не заполнено!";

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
				$this->d['order_res']->basket=json_encode($this->d['cart']);
				$this->d['order_res']->total_amount=$this->d['cart']->total_amount;
				$this->d['order_res']->discount=json_encode($this->d['discount']);
				$this->d['order_res']->total_amount_with_discount=$this->d['discount']['price'];
				
				$order_id=$this->d['order_res']->id;

				$this->db
				->where("id",$order_id)
				->update("realty_orders",(array)$this->d['order_res']);

				//  отправляем письмо администратору, отправляем письмо покупателю

				$order_id=$this->d['order_res']->id;
				$this->change_order_status($order_id,"submited");

				$email_html="";
				$email_html_items="";
				$email_html_items.=<<<EOF
<table cellspacing="0" cellpadding="5" border="1" align="center">
<tr>
	<th><strong>Наименование</strong></th>
	<th><strong>Количество</strong></th>
	<th><strong>Цена</strong></th>
</tr>
EOF;
				foreach($this->d['cart']->items AS $r)
				{
					$r->link=base_url($this->link_item_view($r));

					$total_line="{$r->price_hmn} * {$r->quantity} = {$r->price_total_hmn}";
					if($r->show!=1){
						$r->quantity=0;
						$total_line="<strong style=\"color:red;\">на момент заказа этого товара небыло в наличии</strong>";
					}

					$email_html_items.=<<<EOF
<tr>
	<td><a href="{$r->link}" target="_blank">{$r->title}</a></td>
	<td align="center">{$r->quantity}</td>
	<td>{$total_line}</td>
</tr>
EOF;
				}

				$email_html_items.=<<<EOF
<tr>
	<td colspan="3">
EOF;

				if($this->d['discount']['difference']>0){
					$email_html_items.=<<<EOF
<strong>Общая сумма: </strong> {$this->d['cart']->total_amount_hmn}
<br /><strong>Скидка: </strong> {$this->d['discount']['difference_hmn']}
<br />
<br /><strong>Доставка: </strong> {$this->d['discount']['delivery_hmn']}
<br />
<br /><strong>Итого: </strong> {$this->d['discount']['price_total_hmn']}
EOF;
				
				}else{
					$email_html_items.=<<<EOF
<strong>Итого: </strong> {$cart->total_amount}
EOF;
				
				}

				$email_html_items.=<<<EOF
	</td>
</tr>
</table>
EOF;

				$email_html_order_info="";
				$email_html_order_info2="";

				$delivery_method_hmn=$this->delivery_methos[$delivery_method];

				$email_html_order_info.=<<<EOF
<p><strong>Номер заказа:</strong> {$this->d['order_res']->id}</p>
<p><strong>E-mail:</strong> {$this->d['order_res']->email}</p>
<p><strong>Метод доставки:</strong> {$delivery_method_hmn}</p>

<p><strong>Адрес доставки:</strong> {$this->d['order_res']->delivery_address}</p>
<p><strong>Город доставки:</strong> {$this->d['order_res']->delivery_city}</p>
<p><strong>Склад доставки:</strong> {$this->d['order_res']->delivery_storage}</p>
<p><strong>Фамилия получателя посылки:</strong> {$this->d['order_res']->delivery_name}</p>

<p><strong>ФИО:</strong> {$this->d['order_res']->name}</p>
<p><strong>Телефон:</strong> {$this->d['order_res']->phone}</p>
<p><strong>Комментарии к заказу:</strong> {$this->d['order_res']->notes}</p>
EOF;
				if($user_id>0){
					$email_html_order_info2.=<<<EOF
<p><strong>ID пользователя в системе:</strong> {$this->d['order_res']->user_id}</p>
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
					$this->ci->email->message($email_html_order_info.$email_html_order_info2.$email_html_items);	
					$this->ci->email->send();
					// print $this->ci->email->print_debugger();
					// exit;
				}

				$this->ci->email->from($this->ci->config->config['email_from'],$this->ci->config->config['email_from_name']);
				$this->ci->email->to($this->d['order_res']->email);
				$this->ci->email->subject("Новый заказ №".$this->d['order_res']->id);
				$this->ci->email->message($email_html_order_info.$email_html_items);	
				$this->ci->email->send();

				// удаляем корзину
				$realty_cart_id=intval($this->ci->session->userdata("realty_cart_id"));
				$this->db
				->where("id",$realty_cart_id)
				->delete("realty_carts");

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

		foreach($this->d['cart']->items AS $r)
		{
			$r->link=$this->link_item_view($r);
		}

		$this->ci->load->frontView("realty/cart",$this->d);
	}

	public function order_success()
	{
		$this->ci->load->frontView("realty/order_success",$this->d);
	}

	public function manufacturer_view()
	{
		$this->d['manufacturer_res']=$this->db
		->get_where("categoryes",array(
			"id"=>intval($_GET['manufacturer_id']),
			"show"=>1
		))
		->row();

		if(intval($this->d['manufacturer_res']->id)<1){
			show_404();
		}

		$realty_items_res=$this->db
		->select("id")
		->get_where("realty_items",array(
			"show"=>1,
			"brand_id"=>intval($_GET['manufacturer_id'])
		))
		->result();

		$ids=array();
		foreach($realty_items_res AS $r)
		{
			$ids[]=$r->id;
		}

		if(sizeof($ids)>0){
			$realty_items_categories_link_res=$this->db
			->select("category_id")
			->group_by("category_id")
			->get_where("realty_items_categories_link",array(
				"item_id IN(".implode(",",$ids).")"=>NULL
			))
			->result();

			$ids=array();
			foreach($realty_items_categories_link_res AS $r)
			{
				$ids[]=$r->category_id;
			}
			
			$this->d['categories_res']=$this->db
			->select("uploads.file_path, uploads.file_name")
			->select("categoryes.*")
			->join("uploads","uploads.extra_id = categoryes.id && uploads.name = 'category_image' && uploads.component_name = 'realty'","left")
			->get_where("categoryes",array(
				"categoryes.id IN(".implode(",",$ids).")"=>NULL,
				"categoryes.show"=>1
			))->result();
		}

		$this->ci->load->frontView("realty/manufacturer",$this->d);
	}

	public function hotline_export()
	{
		ini_set("memory_limit","256M");

		// http://imperia-posudy.com/index.php?m=realty&a=hotline_export
		$this->d['categories_res']=$this->db
		->select("categoryes.id, categoryes.title")
		->order_by("categoryes.order")
		->get_where("categoryes",array(
			"categoryes.type"=>"realty-category",
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

		$items_q=$this
		->items_query()
		->select("realty_items.*")
		
		// ->select("realty_items_categories_link.category_id")
		// ->join("realty_items_categories_link","realty_items_categories_link.item_id = realty_items.id")

		->select("brand.title AS brand_title")
		->join("categoryes AS brand","brand.type IN('realty-manufacturer') && brand.id = realty_items.brand_id","left")

		->where("realty_items.show",1)
		->get();

		// if($items_q===false){
		// 	print $items_q->last_query();exit;
		// }

		$this->d['items_res']=$items_q->result();
		
		foreach($this->d['items_res'] AS $i=>$r)
		{
			$cats_res=$this->db
			->select("realty_items_categories_link.category_id")
			->select("categoryes.parent_id")
			->join("categoryes","categoryes.id = realty_items_categories_link.category_id")
			->get_where("realty_items_categories_link",array(
				"realty_items_categories_link.item_id"=>$r->id
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
			$r->link=base_url($this->link_item_view($r));
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