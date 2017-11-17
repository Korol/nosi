<?php
include_once("./modules/realty/realty.helper.php");

class realtyModule extends realtyModuleHelper {
	function __construct()
	{
		parent::__construct();

		$this->load->library("categories");
	}

	function rebuild_cats_order($parent_id=0)
	{
		$res=$this->db
		->order_by("order")
		->get_where("categoryes",array(
			"type"=>"realty-category",
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
			"type"=>"realty-category",
			"order"=>$order=="up"?$res->order-1:$res->order+1,
			"parent_id"=>$res->parent_id
		))
		->update("categoryes",array(
			"order"=>$res->order
		));
		
		$this->db
		->where(array(
			"type"=>"realty-category",
			"id"=>$id,
			"parent_id"=>$res->parent_id
		))
		->update("categoryes",array(
			"order"=>$order=="up"?$res->order-1:$res->order+1
		));

		$this->rebuild_cats_order($res->parent_id);

		redirect($this->admin_url."?m=realty&a=cats");
	}

	public function enabled_cat()
	{
		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("categoryes",array(
			"show"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=realty&a=cats");
	}

	public function cats()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->buttons("main",array(
			array("add","Добавить<br />категорию",$this->admin_url."?m=realty&a=add_cat")
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
				'<a href="'.$this->admin_url."?m=realty&a=edit_cat&id=".$r->id.'">'.$r->title_level.'</a>',
				"enabled"=>array($this->admin_url."?m=realty&a=enabled_cat&id=".$r->id."&enable=",$r->show),
				"order"=>array($this->admin_url."?m=realty&a=order_cat&id=".$r->id."&order=",$r->order,$level_num[$r->level.":".$r->parent_id]),
				"buttons"=>array(
					array("pencil",$this->admin_url."?m=realty&a=edit_cat&id=".$r->id),
					array("cross",$this->admin_url."?m=realty&a=rm_cat&id=".$r->id)
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

		$this->ci->load->adminView("realty/cats",$this->d);
	}

	private function cats_options_list($parent_id=0,$dir_only=true,$level=-1,&$data=array())
	{
		$res=$this->db
		->select("id, title, type")
		->get_where("categoryes",array(
			"type"=>"realty-category",
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
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=realty&a=cats");

		$this->buttons("form",$buttons);

		if($edit){
			$this->d['item_res']=$this->db
			->get_where("categoryes",array("categoryes.id"=>$_GET['id']))
			->row();

			if(is_string($this->d['item_res']->options)){
				$this->d['item_res']->options=json_decode($this->d['item_res']->options);
			}
		}

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true,
			"translate"=>true
		));

		$this->ci->fb->add("textarea:editor",array(
			"label"=>"Описание",
			"name"=>"description",
			"id"=>"description",
			"parent"=>"greed",
			"attr:style"=>"height:200px; width:700px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true,
			"translate"=>true
		));

		$this->ci->fb->add("upload",array(
			"label"=>"Изображение",
			"component_type"=>"module",
			"component_name"=>"realty",
			"extra_type"=>"category_id",
			"upload_path"=>"./uploads/realty/category/",
			"key"=>$edit?"":(!empty($_POST['key'])?$_POST['key']:""),
			"extra_id"=>$edit?$_GET['id']:0,
			"name"=>"category_image",
			"parent"=>"greed",
			"dynamic"=>true,
			"proc_config_var_name"=>"mod_realty[categories_images_options]"
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
			$this->ci->fb->change("description",array("value"=>$this->d['item_res']->description));

			foreach($this->ci->languages_res AS $language)
			{
				if($language->default==1 || $language->enabled!=1)continue;
				$this->ci->fb->change("title_".$language->code,array("value"=>$this->d['item_res']->{"l_title_".$language->code}));
				$this->ci->fb->change("description_".$language->code,array("value"=>$this->d['item_res']->{"l_description_".$language->code}));
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
					
				);

				if($edit){
					$update=array(
						"title"=>$this->input->post("title"),
						"description"=>$this->input->post("description"),
						"name"=>$name,
						"options"=>json_encode($options)
					);
					
					foreach($this->ci->languages_res AS $language)
					{
						if($language->default==1 || $language->enabled!=1)continue;

						$update['l_title_'.$language->code]=$this->input->post("title_".$language->code);
						$update['l_description_'.$language->code]=$this->input->post("description_".$language->code);
					}

					$this->db
					->where(array(
						"id"=>$_GET['id']
					))
					->update("categoryes",$update);
				}else{
					$insert=array(
						"type"=>"realty-category",
						"title"=>$this->input->post("title"),
						"description"=>$this->input->post("description"),
						"name"=>$name,
						"date_add"=>mktime(),
						"show"=>1,
						"options"=>json_encode($options)
					);

					foreach($this->ci->languages_res AS $language)
					{
						if($language->default==1 || $language->enabled!=1)continue;

						$insert['l_title_'.$language->code]=$this->input->post("title_".$language->code);
						$insert['l_description_'.$language->code]=$this->input->post("description_".$language->code);
					}

					$this->db
					->insert("categoryes",$insert);
				}

				$this->rebuild_cats_order($this->input->post("parent_id"));

				redirect($this->admin_url."?m=realty&a=cats");
			}
		}

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("realty/add_cat",$this->d);
	}

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

		redirect($this->admin_url."?m=realty&a=cats");
	}

	public function items()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->buttons("main",array(
			array("add","Добавить",$this->admin_url."?m=realty&a=add_item_m")
		));

		$items_query=clone $this->db;
		$where=array();

		if($this->input->get("filter_keywords")){
			$filter_keywords=trim($this->input->get("filter_keywords"));
			$where["(realty_items.id='".$filter_keywords."' || realty_items.title LIKE '%".$filter_keywords."%')"]=NULL;
		}

		$filter_category_id=intval($this->input->get("filter_category_id"));
		if($filter_category_id>0){
			// $this->items_query->where("pbp_items.cat_ids regexp '[[:<:]](" . implode ( '|',array($category_id)) . ")[[:>:]]'");
			$items_query->join("realty_items_categories_link","realty_items_categories_link.item_id = realty_items.id && realty_items_categories_link.category_id='".$filter_category_id."'");
		}

		$filter_show=$this->input->get("filter_show");
		if($filter_show==1 || $filter_show==2){
			$where["realty_items.show"]=$filter_show==1?1:0;
		}
		$items_query2=clone $items_query;

		$this->d['items_res_num']=$items_query
		->where($where)
		->count_all_results("realty_items");

		$pagination=$this->ci->fb->pagination_init($this->d['items_res_num'],20,current_url_query(array("pg"=>NULL)),"pg");

		// сохранение порядка из таблицы
		if($_POST['table_order_sm']){
			foreach($_POST['order'] AS $id=>$order)
			{
				$this->db
				->where(array(
					"id"=>$id
				))
				->update("realty_items",array(
					"order"=>$this->d['items_res_num']-$order
				));
			}

			$this->rebuild_items_order();
		}

		$order_by_direction=current(array_keys($_GET['order_by']['items']));
		if($order_by_direction!="asc")$order_by_direction="desc";
		switch($_GET['order_by']['items'][$order_by_direction])
		{
			case'realty_items.category_id':
				$order_by="realty_items.category_ids";
			break;
			case'realty_items.title':
				$order_by="realty_items.title";
			break;
			case'realty_items.show':
				$order_by="realty_items.show";
			break;
			default:
			case'realty_items.order':
				$order_by="realty_items.order";
			break;
			case'realty_items.date_public':
				$order_by="realty_items.date_public";
			break;
		}

		if($this->input->get("filter_keywords")){
			$filter_keywords=trim($this->input->get("filter_keywords"));
			$order_by="id = '".$filter_keywords."' DESC, ".$order_by;
		}

		// нормальный запрос товаров, для отображения таблицы
		$this->d['items_res']=
		$items_query2
		->select("realty_items.*")
		->order_by($order_by." ".$order_by_direction)
		->limit((int)$pagination->per_page,(int)$pagination->cur_page)
		->get_where("realty_items",$where)
		->result();
		
		if($this->input->post("select_all_from_table_sm")===false){
			$categories_res=$this->db
			->select("id, title, type")
			->get_where("categoryes",array(
				"type"=>"realty-category"
			))
			->result();
			$categories=array();
			foreach($categories_res AS $r)
			{
				$categories[$r->id]=$r;
			}
				
			$filters_uri="&filter_keywords=".$this->input->get("filter_keywords")."&filter_category_id=".$this->input->get("filter_category_id")."&filter_show=".$this->input->get("filter_show")."&filter_photo=".$this->input->get("filter_photo")."&filter_description=".$this->input->get("filter_description")."&filter_supplier=".$this->input->get("filter_supplier");

			$rows=array();
			foreach($this->d['items_res'] AS $r)
			{
				$cats=array();
				foreach(explode(",",$r->category_ids) AS $cat_id)
				{
					$cat_id=intval($cat_id);
					$cats[]=$categories[$cat_id]->title;
				}
				if($_GET['iframe_display']){
					$rows[]=array(
						'<div style="white-space:nowrap;"><a href="#">'.$r->title.'</a></div>',
						implode(", ",$cats),
						"buttons"=>array(
							array("pencil",$this->admin_url."?m=realty&a=edit_item_m&id=".$r->id.$filters_uri),
							array("cross",$this->admin_url."?m=realty&a=rm_item&id=".$r->id.$filters_uri)
						)
					);
				}else{
					$rows[]=array(
						"checkbox"=>array("name"=>"mass_id","value"=>$r->id,"select_all_from_table"=>true),
						'<div style="white-space:nowrap;"><a href="'.$this->admin_url.'?m=realty&a=edit_item_m&id='.$r->id.'">'.$r->title.'</a>'.$suppliers.'</div>',
						implode(", ",$cats),
						"enabled"=>array($this->admin_url."?m=realty&a=enabled_item&id=".$r->id."&enable=",$r->show),
						date("d.m.Y H:i:s",$r->date_public),
						"buttons"=>array(
							array("pencil",$this->admin_url."?m=realty&a=edit_item_m&id=".$r->id.$filters_uri),
							array("cross",$this->admin_url."?m=realty&a=rm_item&id=".$r->id.$filters_uri)
						)
					);
				}
			}
		}

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

		$this->ci->fb->add("input:submit",array(
			"label"=>"Фильтровать",
			"name"=>"filter_sm",
			"parent"=>"filter_greed"
		));

		$this->ci->fb->add("html",array(
			"content"=>'<div style="padding-left:10px; padding-top:12px;">всего: '.$this->d['items_res_num'].'</div>',
			"value"=>"realty",
			"parent"=>"filter_greed"
		));

		$this->ci->fb->add("input:hidden",array(
			"name"=>"m",
			"value"=>"realty",
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
			"value"=>"items",
			"parent"=>"filter_greed"
		));

		$this->ci->fb->add("greed:float",array(
			"name"=>"filter_greed",
			"parent"=>"table"
		));

		if($_GET['iframe_display']){
			$this->ci->fb->add("table",array(
				"id"=>"items",
				"parent"=>"table",
				"head"=>array(
					array("Наименование","order_by"=>"realty_items.title"),
					"Категория"
				),
				"rows"=>$rows,
				"rows_num"=>$this->d['items_res_num'],
				"pagination"=>$pagination->create_links()
			));
		}else{
			$this->ci->fb->add("table",array(
				"id"=>"items",
				"parent"=>"table",
				"head"=>array(
					array("Наименование","order_by"=>"realty_items.title"),
					"Категория",
					array("Опубликован","order_by"=>"realty_items.show"),
					array("Дата публикации","order_by"=>"realty_items.date_public")
				),
				"rows"=>$rows,
				"rows_num"=>$this->d['items_res_num'],
				"pagination"=>$pagination->create_links()
			));
		}

		$this->ci->fb->add("form",array(
			"name"=>"table",
			"parent"=>"render",
			"method"=>"get"
		));

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("realty/items",$this->d);
	}

	public function enabled_item()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("realty_items",array(
			"show"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=realty&a=items");
	}

	public function edit_item_m($edit=false)
	{
		$_GET['id']=(int)$_GET['id'];
		$this->add_item_m(true);
	}

	public function add_item_m($edit=false)
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=realty&a=items");

		$this->buttons("form",$buttons);

		if($edit){
			$this->d['item_res']=$this->db
			->select("realty_items.*")
			->get_where("realty_items",array("realty_items.id"=>$_GET['id']))
			->row();
		}

		$this->ci->fb->add("input:text",array(
			"label"=>"Наименование",
			"name"=>"title",
			"parent"=>"greed",
			"translate"=>true
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

		
		$options=$this->cats_options_list();
		$this->ci->fb->add("list:select",array(
			"attr:multiple"=>"multiple",
			"attr:size"=>5,
			"label"=>"Категория",
			"name"=>"category_id[]",
			"parent"=>"greed",
			"options"=>$options
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Месторасположение",
			"name"=>"location",
			"parent"=>"greed",
			"translate"=>true
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Цена",
			"attr:style"=>"width:100px;",
			"name"=>"price",
			"parent"=>"greed"
		));	

		$this->ci->fb->add("input:text",array(
			"label"=>"Площадь",
			"name"=>"area",
			"parent"=>"greed"
		));

		$this->ci->fb->add("upload:editor",array(
			"label"=>"Прикрепить файлы",
			"component_type"=>"module",
			"component_name"=>"realty",
			"extra_type"=>"post_id",
			"key"=>$edit?"":(!empty($_POST['key'])?$_POST['key']:""),
			"extra_id"=>$edit?$_GET['id']:0,
			"name"=>"attach",
			"parent"=>"greed",
			"dynamic"=>true,
			"upload_path"=>"./uploads/realty/items/attaches/"
		));

		$this->ci->fb->add("textarea:editor",array(
			"label"=>"Краткое описание",
			"name"=>"short_desc",
			"id"=>"short_desc",
			"parent"=>"greed",
			"attr:style"=>"height:50px; width:700px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true,
			"translate"=>true
		));

		$this->ci->fb->add("textarea:editor",array(
			"label"=>"Полное описание",
			"name"=>"full_desc",
			"id"=>"full_desc",
			"parent"=>"greed",
			"attr:style"=>"height:200px; width:700px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true,
			"translate"=>true
		));

		$this->ci->fb->add("textarea:editor",array(
			"label"=>"Параметры",
			"name"=>"params",
			"id"=>"params",
			"parent"=>"greed",
			"attr:style"=>"height:100px; width:700px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true,
			"translate"=>true
		));

		$this->ci->fb->add("upload:editor",array(
			"label"=>"Фотографии",
			"component_type"=>"module",
			"component_name"=>"realty",
			"extra_type"=>"",
			"extra_id"=>$edit?$_GET['id']:0,
			"key"=>$edit?"":(!empty($_POST['key'])?$_POST['key']:""),
			"name"=>"item-photo",
			"parent"=>"greed",
			"dynamic"=>true,
			"ordering"=>true,
			"thumbs"=>true,
			"proc_config_var_name"=>"mod_realty[images_options]",
			"upload_path"=>"./uploads/realty/items/original/"
		));

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
			"name"=>"show_main",
			"label"=>"опубликовать на главной странице",
			"parent"=>"greed2"
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed",
			"parent"=>"tab1"
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"URL",
			"name"=>"name",
			"parent"=>"greed3"
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Meta title",
			"name"=>"meta_title",
			"parent"=>"greed3",
			"primary"=>true,
			"check"=>array(
				"max_length"=>255
			),
			"translate"=>true
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Meta keywords",
			"name"=>"meta_keywords",
			"parent"=>"greed3",
			"primary"=>true,
			"check"=>array(
				"max_length"=>255
			),
			"translate"=>true
		));

		$this->ci->fb->add("textarea",array(
			"label"=>"Meta description",
			"name"=>"meta_description",
			"parent"=>"greed3",
			"attr:style"=>"height:100px; width:300px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true,
			"translate"=>true
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed2",
			"parent"=>"tab2"
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed3",
			"parent"=>"tab3"
		));

		$this->ci->fb->add("tabs",array(
			"tabs"=>array(
				"tab1"=>"Основное",
				"tab2"=>"Настройки публикации",
				"tab3"=>"SEO"
			),
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

		if(!$edit && !$this->ci->fb->submit){
			$this->ci->fb->change("show_main",array("attr:checked"=>true));
			$this->ci->fb->change("show",array("attr:checked"=>"true"));
			$this->ci->fb->change("date_public",array("value"=>date("d.m.Y H:i:s")));
		}

		if($edit && !$this->ci->fb->submit){
			$this->ci->fb->change("title",array("value"=>$this->d['item_res']->title));
			$this->ci->fb->change("name",array("value"=>$this->d['item_res']->name));
			$this->ci->fb->change("category_id[]",array("value"=>explode(",",$this->d['item_res']->category_ids)));
			$this->ci->fb->change("location",array("value"=>$this->d['item_res']->location));
			$this->ci->fb->change("price",array("value"=>$this->d['item_res']->price));	
			$this->ci->fb->change("area",array("value"=>$this->d['item_res']->area));	
			$this->ci->fb->change("short_desc",array("value"=>$this->d['item_res']->short_desc));
			$this->ci->fb->change("full_desc",array("value"=>$this->d['item_res']->full_desc));
			$this->ci->fb->change("params",array("value"=>$this->d['item_res']->params));
			$this->ci->fb->change("meta_title",array("value"=>$this->d['item_res']->meta_title));
			$this->ci->fb->change("meta_keywords",array("value"=>$this->d['item_res']->meta_keywords));
			$this->ci->fb->change("meta_description",array("value"=>$this->d['item_res']->meta_description));
			$this->ci->fb->change("date_public",array("value"=>$this->d['item_res']->date_public>0?date("d.m.Y H:i:s",$this->d['item_res']->date_public):""));
			if($this->d['item_res']->show==1){
				$this->ci->fb->change("show",array("attr:checked"=>true));
			}
			if($this->d['item_res']->show==1){
				$this->ci->fb->change("show_main",array("attr:checked"=>true));
			}

			foreach($this->ci->languages_res AS $language)
			{
				if($language->default==1 || $language->enabled!=1)continue;
				$this->ci->fb->change("title_".$language->code,array("value"=>$this->d['item_res']->{"l_title_".$language->code}));
				$this->ci->fb->change("location_".$language->code,array("value"=>$this->d['item_res']->{"l_location_".$language->code}));
				$this->ci->fb->change("short_desc_".$language->code,array("value"=>$this->d['item_res']->{"l_short_desc_".$language->code}));
				$this->ci->fb->change("full_desc_".$language->code,array("value"=>$this->d['item_res']->{"l_full_desc_".$language->code}));
				$this->ci->fb->change("params_".$language->code,array("value"=>$this->d['item_res']->{"l_params_".$language->code}));
				$this->ci->fb->change("meta_title_".$language->code,array("value"=>$this->d['item_res']->{"l_meta_title_".$language->code}));
				$this->ci->fb->change("meta_keywords_".$language->code,array("value"=>$this->d['item_res']->{"l_meta_keywords_".$language->code}));
				$this->ci->fb->change("meta_description_".$language->code,array("value"=>$this->d['item_res']->{"l_meta_description_".$language->code}));
			}

		}

		if($this->ci->fb->submit){
			$this->d['global_errors']=$this->ci->fb->errors_list();

			$order=0;
			$order=$this->db
			->count_all_results("realty_items");
			$order++;

			if(sizeof($this->d['global_errors'])==0){
				$category_ids=array();
				if($this->input->post("category_id")!==false && is_array($this->input->post("category_id"))){
					$category_ids=$this->input->post("category_id");
				}
				$category_ids=array_unique($category_ids);

				$name=$this->input->post("name");
				if($this->input->post("name")==""){
					$name=rewrite_alias($this->input->post("title"));
				}


				$date_public=intval(strtotime($this->input->post("date_public")));

				if($edit){
					$this->d['update']=array(
						"title"=>$this->input->post("title"),
						"name"=>$name,
						"category_ids"=>implode(",",$category_ids),
						"location"=>$this->input->post("location"),
						"price"=>price_double($this->input->post("price")),
						"area"=>$this->input->post("area"),
						"short_desc"=>$this->input->post("short_desc"),
						"full_desc"=>$this->input->post("full_desc"),
						"params"=>$this->input->post("params"),
						"meta_title"=>$this->input->post("meta_title"),
						"meta_keywords"=>$this->input->post("meta_keywords"),
						"meta_description"=>$this->input->post("meta_description"),
						"show"=>$this->input->post("show")==1?1:0,
						"show_main"=>$this->input->post("show_main")==1?1:0,
						"date_edit"=>mktime(),
						"date_public"=>$date_public
					);

					if(isset($this->d['additional_fields']) && sizeof($this->d['additional_fields'])>0){
						$this->d['update']=array_merge($this->d['update'],$this->d['additional_fields']);
					}

					foreach($this->ci->languages_res AS $language)
					{
						if($language->default==1 || $language->enabled!=1)continue;

						$this->d['update']['l_title_'.$language->code]=$this->input->post("title_".$language->code);
						$this->d['update']['l_location_'.$language->code]=$this->input->post("location_".$language->code);
						$this->d['update']['l_short_desc_'.$language->code]=$this->input->post("short_desc_".$language->code);
						$this->d['update']['l_full_desc_'.$language->code]=$this->input->post("full_desc_".$language->code);
						$this->d['update']['l_params_'.$language->code]=$this->input->post("params_".$language->code);
						$this->d['update']['l_meta_title_'.$language->code]=$this->input->post("meta_title_".$language->code);
						$this->d['update']['l_meta_keywords_'.$language->code]=$this->input->post("meta_keywords_".$language->code);
						$this->d['update']['l_meta_description_'.$language->code]=$this->input->post("meta_description_".$language->code);
					}

					$this->update_item($this->d['update'],array(
						"id"=>$_GET['id']
					));

					$id=$_GET['id'];
				}else{

					$this->d['insert']=array(
						"title"=>$this->input->post("title"),
						"name"=>$name,
						"category_ids"=>implode(",",$category_ids),
						"location"=>$this->input->post("location"),
						"price"=>price_double($this->input->post("price")),
						"area"=>$this->input->post("area"),
						"short_desc"=>$this->input->post("short_desc"),
						"full_desc"=>$this->input->post("full_desc"),
						"params"=>$this->input->post("params"),
						"meta_title"=>$this->input->post("meta_title"),
						"meta_keywords"=>$this->input->post("meta_keywords"),
						"meta_description"=>$this->input->post("meta_description"),
						"show"=>$this->input->post("show")==1?1:0,
						"show_main"=>$this->input->post("show_main")==1?1:0,
						"order"=>$order,
						"date_add"=>mktime(),
						"date_public"=>$date_public
					);

					if(isset($this->d['additional_fields']) && sizeof($this->d['additional_fields'])>0){
						$this->d['insert']=array_merge($this->d['insert'],$this->d['additional_fields']);
					}

					foreach($this->ci->languages_res AS $language)
					{
						if($language->default==1 || $language->enabled!=1)continue;

						$this->d['insert']['l_title_'.$language->code]=$this->input->post("title_".$language->code);
						$this->d['insert']['l_location_'.$language->code]=$this->input->post("location_".$language->code);
						$this->d['insert']['l_short_desc_'.$language->code]=$this->input->post("short_desc_".$language->code);
						$this->d['insert']['l_full_desc_'.$language->code]=$this->input->post("full_desc_".$language->code);
						$this->d['insert']['l_params_'.$language->code]=$this->input->post("params_".$language->code);
						$this->d['insert']['l_meta_title_'.$language->code]=$this->input->post("meta_title_".$language->code);
						$this->d['insert']['l_meta_keywords_'.$language->code]=$this->input->post("meta_keywords_".$language->code);
						$this->d['insert']['l_meta_description_'.$language->code]=$this->input->post("meta_description_".$language->code);
					}

					$id=$this->add_item($this->d['insert']);
				}

				redirect($this->admin_url."?m=realty&a=items");
			}
		}

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("realty/add_item_m",$this->d);
	}

	function rebuild_items_order($parent_id=0)
	{
		$res=$this->db
		->order_by("order")
		->get_where("realty_items",array())
		->result();

		$i=1;
		foreach($res AS $r)
		{
			$this->db
			->where(array(
				"id"=>$r->id
			))
			->update("realty_items",array(
				"order"=>$i
			));
			
			$i++;
		}
	}

	public function order_item()
	{
		$id=(int)$this->input->get("id");
		$order=$this->input->get("order");

		$res=$this->db
		->get_where("realty_items",array(
			"id"=>$id
		))
		->row();
		
		$this->db
		->where(array(
			"order"=>$order=="up"?$res->order-1:$res->order+1
		))
		->update("realty_items",array(
			"order"=>$res->order
		));
		
		$this->db
		->where(array(
			"id"=>$id
		))
		->update("realty_items",array(
			"order"=>$order=="up"?$res->order-1:$res->order+1
		));

		$this->rebuild_items_order();

		redirect($this->admin_url."?m=realty&a=items&pg=".intval($this->input->get("pg")));
	}

	public function rm_item()
	{
		$id=(int)$this->input->get("id");

		$attaches_res=$this->db
		->select("file_name, file_path")
		->get_where("uploads",array(
			"component_type"=>"module",
			"component_name"=>"realty",
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
			"component_name"=>"realty",
			"extra_type"=>"post_id",
			"extra_id"=>$id
		))
		->delete("uploads");

		$this->db
		->where(array(
			"id"=>$id
		))
		->delete("realty_items");

		$filters_uri="&filter_keywords=".$this->input->get("filter_keywords")."&filter_category_id=".$this->input->get("filter_category_id")."&filter_brand_id=".$this->input->get("filter_brand_id")."&filter_show=".$this->input->get("filter_show")."&filter_photo=".$this->input->get("filter_photo")."&filter_description=".$this->input->get("filter_description")."&filter_supplier=".$this->input->get("filter_supplier");

		redirect($this->admin_url."?m=realty&a=items".$filters_uri);
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
}
?>