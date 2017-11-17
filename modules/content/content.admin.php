<?php
include_once("./modules/content/content.helper.php");

class contentModule extends contentModuleHelper {
	function __construct()
	{
		parent::__construct();
	}

	function rebuild_cats_order($parent_id=0)
	{
		$res=$this->db
		->order_by("order")
		->get_where("categoryes",array(
			"type"=>"content-category",
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
			"type"=>"content-category",
			"order"=>$order=="up"?$res->order-1:$res->order+1,
			"parent_id"=>$res->parent_id
		))
		->update("categoryes",array(
			"order"=>$res->order
		));
		
		$this->db
		->where(array(
			"type"=>"content-category",
			"id"=>$id,
			"parent_id"=>$res->parent_id
		))
		->update("categoryes",array(
			"order"=>$order=="up"?$res->order-1:$res->order+1
		));

		$this->rebuild_cats_order($res->parent_id);

		redirect($this->admin_url."?m=content&a=cats");
	}

	public function enabled_cat()
	{
		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("categoryes",array(
			"show"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=content&a=cats");
	}

	public function cats()
	{
		$this->buttons("main",array(
			array("add","Добавить<br />категорию",$this->admin_url."?m=content&a=add_cat")
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
				'<a href="'.$this->admin_url.'?m=content&a=edit_cat&id='.$r->id.'">'.$r->title_level.'</a>',
				"enabled"=>array($this->admin_url."?m=content&a=enabled_cat&id=".$r->id."&enable=",$r->show),
				"order"=>array($this->admin_url."?m=content&a=order_cat&id=".$r->id."&order=",$r->order,$level_num[$r->level.":".$r->parent_id]),
				"buttons"=>array(
					array("pencil",$this->admin_url."?m=content&a=edit_cat&id=".$r->id),
					array("cross",$this->admin_url."?m=content&a=rm_cat&id=".$r->id)
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

		$this->ci->load->adminView("content/cats",$this->d);
	}

	private function cats_options_list($parent_id=0,$dir_only=true,$level=-1,&$data=array())
	{
		$res=$this->db
		->select("id, title, type")
		->get_where("categoryes",array(
			"type"=>"content-category",
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
		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=content&a=cats");

		$this->buttons("form",$buttons);

		if($edit){
			$this->d['item_res']=$this->db
			->select("categoryes.*")
			->get_where("categoryes",array("categoryes.id"=>$_GET['id']))
			->row();
		}

		$options=$this->cats_options_list();

		$options=array("0"=>"КОРЕНЬ САЙТА")+$options;

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

		$this->ci->fb->add("upload",array(
			"label"=>"Изображение категории",
			"component_type"=>"module",
			"component_name"=>"content",
			"extra_type"=>"category_id",
			"extra_id"=>$edit?$_GET['id']:0,
			"key"=>$edit?"":(!empty($_POST['key'])?$_POST['key']:""),
			"name"=>"category_main_picture",
			"parent"=>"greed",
			"dynamic"=>true,
			"upload_path"=>"./uploads/content/categories/",
			"proc_config_var_name"=>"config[mod_content_category_main_picture_options]"
		));

		$this->ci->fb->add("textarea:editor",array(
			"label"=>"Описание",
			"help"=>"желательно заполнить для соц. сервисов, и поисковиков",
			"name"=>"description",
			"id"=>"description",
			"parent"=>"greed",
			"attr:style"=>"height:100px; width:700px;",
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
			$this->ci->fb->change("parent_id",array("value"=>$this->d['item_res']->parent_id));
			$this->ci->fb->change("title",array("value"=>$this->d['item_res']->title));
			$this->ci->fb->change("description",array("value"=>$this->d['item_res']->description));
		}

		if($this->ci->fb->submit){
			$this->d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($this->d['global_errors'])==0){
				$name=$this->input->post("name");
				if($this->input->post("name")==""){
					$name=rewrite_alias($this->input->post("title"));
				}

				if($edit){
					$this->db
					->where(array(
						"id"=>$_GET['id']
					))
					->update("categoryes",array(
						"parent_id"=>(int)$this->input->post("parent_id"),
						"title"=>$this->input->post("title"),
						"description"=>$this->input->post("description"),
						"name"=>$name
					));
				}else{
					$this->db
					->insert("categoryes",array(
						"type"=>"content-category",
						"parent_id"=>(int)$this->input->post("parent_id"),
						"title"=>$this->input->post("title"),
						"description"=>$this->input->post("description"),
						"name"=>$name,
						"date_add"=>mktime(),
						"show"=>1
					));
				}

				redirect($this->admin_url."?m=content&a=cats");
			}
		}

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("content/add_cat",$this->d);
	}

	function rm_cat()
	{
		$id=(int)$this->input->get("id");

		$child_ids=array_keys($this->rcats_list($id));
		$child_ids[]=$id;

		$this->db
		->where(array(
			"type"=>"content-category",
			"id IN (".implode(",",$child_ids).")"=>NULL
		))
		->delete("categoryes");

		redirect($this->admin_url."?m=content&a=cats");
	}

	public function posts()
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->buttons("main",array(
			array("add","Добавить<br />материал",$this->admin_url."?m=content&a=add_post")
		));

		$this->d['posts_res_num']=$this->db
		->count_all_results("content_posts");

		$pagination=$this->ci->fb->pagination_init($this->d['posts_res_num'],20,current_url_query(array("pg"=>NULL)),"pg");

		// сохранение порядка из таблицы
		if($_POST['table_order_sm']){
			foreach($_POST['order'] AS $id=>$order)
			{
				$this->db
				->where(array(
					"id"=>$id
				))
				->update("content_posts",array(
					"order"=>$this->d['posts_res_num']-$order
				));
			}

			$this->rebuild_posts_order();
		}

		$order_by_direction=current(array_keys($_GET['order_by']['posts']));
		if($order_by_direction!="asc")$order_by_direction="desc";
		switch(current($_GET['order_by']['posts']))
		{
			case'content_posts.title':
				$order_by="content_posts.title";
			break;
			case'content_posts.show':
				$order_by="content_posts.show";
			break;
			default:
			case'content_posts.order':
				$order_by="content_posts.order";
			break;
			case'content_posts.date_public':
				$order_by="content_posts.date_public";
			break;
		}

		$this->d['posts_res']=$this->db
		->order_by($order_by,$order_by_direction)
		->limit((int)$pagination->per_page,(int)$pagination->cur_page)
		->get_where("content_posts",array())
		->result();

		$rows=array();
		foreach($this->d['posts_res'] AS $r)
		{
			$category_path=array();

			if(!empty($r->category_path)){
				$r->category_path_red=$this->db
				->select("id, title")
				->get_where("categoryes",array(
					"id IN (".$r->category_path.")"=>NULL
				))
				->result();
				foreach($r->category_path_red AS $category_r)
				{
					$category_path[]=$category_r->title;
				}
			}
			$category_path=implode(" &gt; ",$category_path);

			$rows[]=array(
				'<a href="'.$this->admin_url.'?m=content&a=edit_post&id='.$r->id.'">'.$r->title.'</a>',
				$category_path,
				"enabled"=>array($this->admin_url."?m=content&a=enabled_post&id=".$r->id."&enable=",$r->show),
				"order:num:desc"=>array($this->admin_url."?m=content&a=order_post&pg=".intval($this->input->get("pg"))."&id=".$r->id."&order=",($this->d['posts_res_num']+1)-$r->order,$this->d['posts_res_num'],$r->id),
				date("d.m.Y H:i:s",$r->date_public),
				"buttons"=>array(
					array("pencil",$this->admin_url."?m=content&a=edit_post&id=".$r->id),
					array("cross",$this->admin_url."?m=content&a=rm_post&id=".$r->id)
				)
			);
		}

		$this->ci->fb->add("table",array(
			"id"=>"posts",
			"parent"=>"table",
			"head"=>array(
				array("Название","order_by"=>"content_posts.title"),
				array("Категория","order_by"=>"content_posts.category_id"),
				array("Опубликован","order_by"=>"content_posts.show"),
				array("Порядок","order_by"=>"content_posts.order"),
				array("Дата публикации","order_by"=>"content_posts.date_public")
			),
			"rows"=>$rows,
			"pagination"=>$pagination->create_links()
		));

		$this->d['render']=$this->ci->fb->render("table");

		$this->ci->load->adminView("content/posts",$this->d);
	}

	public function enabled_post()
	{
		$this->db
		->where(array("id"=>$this->input->get("id")))
		->update("content_posts",array(
			"show"=>$this->input->get("enable")==1?1:0
		));

		redirect($this->admin_url."?m=content&a=posts");
	}

	public function edit_post($edit=false)
	{
		$_GET['id']=(int)$_GET['id'];
		$this->add_post(true);
	}

	public function add_post($edit=false)
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=content&a=posts");

		$this->buttons("form",$buttons);

		if($edit){
			$this->d['item_res']=$this->db
			->select("content_posts.*")
			->get_where("content_posts",array("content_posts.id"=>$_GET['id']))
			->row();
			$widgets_options_json=$this->d['item_res']->widgets_options;
		}

		$options=$this->cats_options_list();

		$options=array("0"=>"КОРЕНЬ САЙТА")+$options;

		$this->ci->fb->add("list:select",array(
			"label"=>"Категория",
			"name"=>"category_id",
			"parent"=>"greed",
			"options"=>$options
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed"
		));

		$this->ci->fb->add("upload",array(
			"label"=>"Главное изображение",
			"component_type"=>"module",
			"component_name"=>"content",
			"extra_type"=>"post_id",
			"key"=>$edit?"":(!empty($_POST['key'])?$_POST['key']:""),
			"extra_id"=>$edit?$_GET['id']:0,
			"name"=>"main_picture",
			"parent"=>"greed",
			"dynamic"=>true,
			"upload_path"=>"./uploads/content/",
			"proc_config_var_name"=>"config[mod_content_main_picture_options]"
		));

		$this->ci->fb->add("upload:editor",array(
			"label"=>"Прикрепить файлы",
			"component_type"=>"module",
			"component_name"=>"content",
			"extra_type"=>"post_id",
			"extra_id"=>$edit?$_GET['id']:0,
			"key"=>$edit?"":(!empty($_POST['key'])?$_POST['key']:""),
			"name"=>"attach",
			"parent"=>"greed",
			"dynamic"=>true
		));

		$this->ci->fb->add("textarea:editor",array(
			"label"=>"Краткий текст",
			"name"=>"short_text",
			"id"=>"short_text",
			"parent"=>"greed",
			"attr:style"=>"height:100px; width:700px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true,
			"check"=>array(
				//"min_length"=>0
			)
		));

		$this->ci->fb->add("textarea:editor",array(
			"label"=>"Полный текст",
			"name"=>"full_text",
			"id"=>"full_text",
			"parent"=>"greed",
			"attr:style"=>"height:400px; width:700px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true,
			"check"=>array(
				"min_length"=>0
			)
		));

		$this->ci->fb->add("input:date",array(
			"label"=>"Дата публикации",
			"name"=>"date_public",
			"parent"=>"greed2"
		));

		// $this->ci->fb->add("input:checkbox",array(
		// 	"name"=>"archive",
		// 	"label"=>"архивный",
		// 	"parent"=>"greed2"
		// ));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"show",
			"label"=>"опубликовать",
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
			"label"=>"URL",
			"name"=>"alias",
			"parent"=>"greed3"
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Заголовок в окне браузера",
			"name"=>"page_title",
			"parent"=>"greed3",
			"primary"=>true,
			"check"=>array(
				"max_length"=>255
			)
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
			"content"=>$widgets_cnotrol_html,
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

		if($edit && !$this->ci->fb->submit){
			$this->ci->fb->change("category_id",array("value"=>$this->d['item_res']->category_id));
			$this->ci->fb->change("title",array("value"=>$this->d['item_res']->title));
			$this->ci->fb->change("alias",array("value"=>$this->d['item_res']->alias));
			$this->ci->fb->change("short_text",array("value"=>$this->d['item_res']->short_text));
			$this->ci->fb->change("full_text",array("value"=>$this->d['item_res']->full_text));
			$this->ci->fb->change("page_title",array("value"=>$this->d['item_res']->page_title));
			$this->ci->fb->change("meta_title",array("value"=>$this->d['item_res']->meta_title));
			$this->ci->fb->change("meta_keywords",array("value"=>$this->d['item_res']->meta_keywords));
			$this->ci->fb->change("meta_description",array("value"=>$this->d['item_res']->meta_description));
			$this->ci->fb->change("date_public",array("value"=>$this->d['item_res']->date_public>0?date("d.m.Y H:i:s",$this->d['item_res']->date_public):""));
			if($this->d['item_res']->show==1){
				$this->ci->fb->change("show",array("attr:checked"=>true));
			}
			if($this->d['item_res']->archive==1){
				$this->ci->fb->change("archive",array("attr:checked"=>true));
			}
			if($this->d['item_res']->disallow_bot_index==1){
				$this->ci->fb->change("disallow_bot_index",array("attr:checked"=>true));
			}

			// информация о клиенте
			$this->ci->fb->change("project_client_id",array("value"=>$this->d['item_res']->project_client_id));
			$this->ci->fb->change("project_title",array("value"=>$this->d['item_res']->project_title));
			$this->ci->fb->change("project_date_start",array("value"=>$this->d['item_res']->project_date_start>0?date("d.m.Y H:i:s",$this->d['item_res']->project_date_start):""));
		}

		if(!$edit && !$this->ci->fb->submit){
			$this->ci->fb->change("show",array("attr:checked"=>"true"));
			$this->ci->fb->change("date_public",array("value"=>date("d.m.Y H:i:s")));
		}

		if($this->ci->fb->submit){
			$this->d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($this->d['global_errors'])==0){
				$alias=$this->input->post("alias");
				if($this->input->post("alias")==""){
					$alias=rewrite_alias($this->input->post("title"));
				}

				$date_public=intval(strtotime($this->input->post("date_public")));

				// информация о клиентах
				$project_date_start=intval(strtotime($this->input->post("project_date_start")));

				$category_parent_ids=$this->cat_parents_ids($this->input->post("category_id"));

				if($this->input->post("widgets_options")!==false){
					$widgets_options=$this->input->post("widgets_options");
				}

				$order=0;
				$order=$this->db
				->count_all_results("content_posts");
				$order++;

				if($edit){
					$this->db
					->where(array(
						"id"=>$_GET['id']
					))
					->update("content_posts",array(
						"category_id"=>$this->input->post("category_id"),
						"category_path"=>implode(",",$category_parent_ids),
						"title"=>$this->input->post("title"),
						"alias"=>$alias,
						"short_text"=>$this->input->post("short_text"),
						"full_text"=>$this->input->post("full_text"),
						"page_title"=>$this->input->post("page_title"),
						"meta_title"=>$this->input->post("meta_title"),
						"meta_keywords"=>$this->input->post("meta_keywords"),
						"meta_description"=>$this->input->post("meta_description"),
						"show"=>$this->input->post("show")==1?1:0,
						"archive"=>$this->input->post("archive")==1?1:0,
						"date_edit"=>mktime(),
						"date_public"=>$date_public,
						"widgets_options"=>json_encode($widgets_options),

						// информация о клиентах
						"project_client_id"=>intval($this->input->post("project_client_id")),
						"project_title"=>$this->input->post("project_title"),
						"project_date_start"=>$project_date_start,
						"disallow_bot_index"=>$this->input->post("disallow_bot_index")==1?1:0
					));
				}else{
					$this->db
					->insert("content_posts",array(
						"category_id"=>$this->input->post("category_id"),
						"category_path"=>implode(",",$category_parent_ids),
						"title"=>$this->input->post("title"),
						"alias"=>$alias,
						"short_text"=>$this->input->post("short_text"),
						"full_text"=>$this->input->post("full_text"),
						"page_title"=>$this->input->post("page_title"),
						"meta_title"=>$this->input->post("meta_title"),
						"meta_keywords"=>$this->input->post("meta_keywords"),
						"meta_description"=>$this->input->post("meta_description"),
						"show"=>$this->input->post("show")==1?1:0,
						"archive"=>$this->input->post("archive")==1?1:0,
						"date_add"=>mktime(),
						"date_public"=>$date_public,
						"order"=>$order,
						"widgets_options"=>json_encode($widgets_options),

						// информация о клиентах
						"project_client_id"=>intval($this->input->post("project_client_id")),
						"project_title"=>$this->input->post("project_title"),
						"project_date_start"=>$project_date_start,
						"disallow_bot_index"=>$this->input->post("disallow_bot_index")==1?1:0
					));

					$id=$this->db->insert_id();
				}

				$this->db
				->where(array(
					"key"=>$_POST['key'],
					"component_type"=>"module",
					"component_name"=>"content",
					"extra_id"=>0
				))
				->update("uploads",array(
					"key"=>"",
					"extra_id"=>$id
				));

				redirect($this->admin_url."?m=content&a=posts");
			}
		}

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("content/add_post",$this->d);
	}

	function cat_parents_ids($child_id=0,&$data=array())
	{
		if($child_id==0)return array();

		$res=$this->db
		->select("id, parent_id")
		->get_where("categoryes",array(
			"type"=>"content-category",
			"id"=>$child_id
		))
		->row();

		array_unshift($data,$res->id);
		
		if($res->parent_id>0){
			$this->cat_parents_ids($res->parent_id,$data);
		}

		return $data;
	}

	function rebuild_posts_order($parent_id=0)
	{
		$res=$this->db
		->order_by("order")
		->get_where("content_posts",array())
		->result();

		$i=1;
		foreach($res AS $r)
		{
			$this->db
			->where(array(
				"id"=>$r->id
			))
			->update("content_posts",array(
				"order"=>$i
			));
			
			$i++;
		}
	}

	public function order_post()
	{
		$id=(int)$this->input->get("id");
		$order=$this->input->get("order");

		$res=$this->db
		->get_where("content_posts",array(
			"id"=>$id
		))
		->row();
		
		$this->db
		->where(array(
			"order"=>$order=="up"?$res->order-1:$res->order+1
		))
		->update("content_posts",array(
			"order"=>$res->order
		));
		
		$this->db
		->where(array(
			"id"=>$id
		))
		->update("content_posts",array(
			"order"=>$order=="up"?$res->order-1:$res->order+1
		));

		$this->rebuild_posts_order();

		redirect($this->admin_url."?m=content&a=posts&pg=".intval($this->input->get("pg")));
	}

	public function rm_post()
	{
		$id=(int)$this->input->get("id");

		$attaches_res=$this->db
		->select("file_name, file_path")
		->get_where("uploads",array(
			"component_type"=>"module",
			"component_name"=>"content",
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
			"component_name"=>"content",
			"extra_type"=>"post_id",
			"extra_id"=>$id
		))
		->delete("uploads");

		$this->db
		->where(array(
			"id"=>$id
		))
		->delete("content_posts");

		redirect($this->admin_url."?m=content&a=posts");
	}
}
?>