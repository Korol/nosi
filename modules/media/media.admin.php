<?php
include_once("media.helper.php");

class mediaModule extends mediaModuleHelper {
	function __construct()
	{
		parent::__construct();

		// подключаем библиотеку для отрисовки интерфейса (формы, таблицы и тп.)

		$this->ci->load->library("fb");
		$this->load->helper('url');
		$this->load->helper('cms');

		$this->ci->load->library("uploads");
	}

	private function bread_crumbs($id=0)
	{
		if($id==0)return array();

		$d=array();

		for($i=0;$i<100;$i++)
		{
			$res=$this->db
			->select("id, parent_id, title")
			->get_where("media_items",array(
				"id"=>$id
			))
			->row();

			$d[$res->id]=array($res->id,$res->title);

			$id=$res->parent_id;

			if($res->parent_id==0){
				break;
			}
		}

		$d[0]=array(0,"Начало");
		$d=array_reverse($d);

		return $d;
	}

	public function mass_remove()
	{
		$id=(int)$this->input->post("id");

		$photo_ids=$_POST['item']['photo'];
		if(is_null($photo_ids))$photo_ids=array();

		$folder_ids=$_POST['item']['folder'];
		if(is_null($folder_ids))$folder_ids=array();

		foreach($_POST['item']['folder'] AS $id)
		{
			$media_items_child_ids=$this->media_items_child_ids($id);

			if(isset($media_items_child_ids['uploads']) && is_array($media_items_child_ids['uploads'])){
				$photo_ids=array_merge($photo_ids,$media_items_child_ids['uploads']);
			}
			if(isset($media_items_child_ids['media_items']) && is_array($media_items_child_ids['media_items'])){
				$folder_ids=array_merge($folder_ids,$media_items_child_ids['media_items']);
			}
		}

		foreach($photo_ids AS $media_item_id)
		{
			$this->remove_media_item($media_item_id);
		}

		foreach($folder_ids AS $folder_id)
		{
			$this->db
			->where(array(
				"id"=>$folder_id
			))
			->delete("media_items");
		}

		redirect($this->admin_url."?m=media&a=browse&id=".$id);
	}

	public function browse()
	{
		$d=array();

		$id=(int)$this->input->get("id");

		if($this->input->post("mass_remove_sm")!==false){
			$this->mass_remove();
		}

		$d['ajax']=$this->input->get("ajax")!==false;

		if($this->input->post("save_order_sm")!==false
			&& is_array($this->input->post("order_ids")) && sizeof($this->input->post("order_ids"))>0){
			$order_ids=$this->input->post("order_ids");
			$order_ids=array_reverse($order_ids);

			$order=1;
			foreach($order_ids AS $r)
			{
				list($item_id,$type)=explode(":",$r);
				if($type=="folder"){
					$this->db
					->where(array(
						"id"=>$item_id
					))
					->update("media_items",array(
						"order"=>$order
					));
				}else{
					$this->db
					->where(array(
						"id"=>$item_id
					))
					->update("uploads",array(
						"order"=>$order
					));
				}
				$order++;
			}

			$this->rebuild_folder_order(intval($id));

			print 1;
			exit;
		}

		$d['bread_crumbs_res']=$this->bread_crumbs($id);

		$buttons=array();
		$buttons[]=array("upload","Загрузить<br />фото",$this->admin_url."?m=media&a=upload_photo&parent_id=".intval($_GET['id']));
		//$buttons[]=array("upload","Загрузить<br />аудио",$this->admin_url."?m=media&a=upload_audio&id=".intval($_GET['id']));
		// $buttons[]=array("upload","Загрузить<br />видео",$this->admin_url."?m=media&a=upload_video&id=".intval($_GET['id']));
		$buttons[]=array("upload","Добавить<br />видео",$this->admin_url."?m=media&a=add_video&parent_id=".intval($_GET['id']));

		$this->buttons("form",$buttons);

		$d['items_res']=$this->db
		->query("
(SELECT `uploads`.`id`,`uploads`.`thumb_id`,`uploads`.`parent_id`,`uploads`.`user_id`,`uploads`.`title`,`uploads`.`name` AS `type`,'' AS `cover_id`,'' AS `cover_file_name`,`uploads`.`file_size`,`uploads`.`file_name`,`uploads`.`file_path`,`uploads`.`image_size`,`uploads`.`date_add`,`uploads`.`order`,'' AS `cover_file_name2`,'' AS `cover_file_path`,`uploads2`.`file_path` AS `thumb_file_path`,`uploads2`.`file_name` AS `thumb_file_name` FROM `uploads` LEFT JOIN `uploads` AS `uploads2` ON `uploads2`.`id`=`uploads`.`thumb_id` WHERE `uploads`.`parent_id`='".$id."' && `uploads`.`component_type`='module' && `uploads`.`component_name`='media')
UNION ALL 
(SELECT `media_items`.`id`,'thumb_id' AS '',`media_items`.`parent_id`,`media_items`.`user_id`,`media_items`.`title`,'folder' AS `type`,`media_items`.`cover_id`,`media_items`.`cover_file_name`,`media_items`.`file_size`,`media_items`.`file_name`,`media_items`.`file_path`,`media_items`.`image_size`,`media_items`.`date_add`,`media_items`.`order`,`uploads`.`file_name` AS `cover_file_name2`,`uploads`.`file_path` AS `cover_file_path`,'thumb_file_name' AS '','thumb_file_path' AS '' FROM `media_items` LEFT JOIN `uploads` ON `uploads`.`id`=`media_items`.`cover_id` WHERE `media_items`.`parent_id`='".$id."')
ORDER BY `order` DESC
")
		->result();

		$this->ci->load->adminView("media/browse",$d);
	}

	public function upload_photo()
	{
		$parent_id=(int)$this->input->get("parent_id");

		$d=array();

		$buttons=array();
		$buttons[]=array("back",NULL,$this->admin_url."?m=media&a=browse&id=".$parent_id);
		$this->buttons("form",$buttons);

		$this->ci->fb->add("upload:swf",array(
			"label"=>"Загрузить фотографии",
			"name"=>"file",
			"parent"=>"greed",
			"primary"=>true,
			"options"=>$options
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

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("media/upload_photo",$d);
	}

	public function swf_upload_photo()
	{
		$id=(int)$this->input->get("parent_id");
//die(print_r($_FILES));
		if(empty($_FILES['file']['tmp_name'])){
			die("file not found");
		}

		$accepted=array("png","jpeg","jpg","gif");

		$ext=strtolower(end(explode(".",$_FILES['file']['name'])));

		if(!in_array($ext,$accepted)){
			die("bad file format");
		}

		if(!is_dir("./uploads/media/"))mkdir("./uploads/media/",0777);
		if(!is_writable("./uploads/media/")){
			die("media directory not permiss to write");
		}

		$file_size=filesize($_FILES['file']['tmp_name']);

		$file_name=md5(uniqid(rand(),1)).md5(uniqid(rand(),1)).".".$ext;
		$file_path="./uploads/media/".$file_name;
		move_uploaded_file($_FILES['file']['tmp_name'],$file_path);
		if(!file_exists($file_path)){
			die("cant copy file to server");
		}

		list($image_width,$image_height)=getimagesize($file_path);

		$order=0;

		$order+=$this->db
		->where(array("parent_id"=>$id))
		->count_all_results("media_items");

		$order+=$this->db
		->where(array(
			"component_type"=>"module",
			"component_name"=>"media",
			"parent_id"=>$id
		))
		->count_all_results("uploads");

		$order++;

		$this->ci->load->library("img");
		$thumb_files="";
		if(!empty($this->ci->config->config['mod_media_photo_picture_options'])){
			$images_options_data=$this->ci->img->proc("uploads/media/".$file_name,$this->ci->config->config['mod_media_photo_picture_options']);
			$thumb_files=implode("\n",$images_options_data['out_files']);
		}

		$this->db
		->insert("uploads",array(
			"parent_id"=>$id,
			"key"=>"",
			"user_id"=>$this->ci->session->userdata("user_id"),
			"title"=>"",
			"name"=>"photo",
			"file_size"=>$file_size,
			"file_name"=>$file_name,
			"file_path"=>"uploads/media/",
			"image_size"=>$image_width."x".$image_height,
			"component_type"=>"module",
			"component_name"=>"media",
			"extra_type"=>"",
			"extra_id"=>"",
			"date_add"=>mktime(),
			"order"=>$order,
			"thumb_files"=>$thumb_files
		));

		$this->rebuild_folder_order($id);

		exit;
	}

	private function rebuild_folder_order($parent_id)
	{
		$items_res=$this->db
		->query("
(SELECT `uploads`.`id`,`uploads`.`order` FROM `uploads` WHERE `uploads`.`parent_id`='".$parent_id."' && `uploads`.`component_type`='module' && `uploads`.`component_name`='media' && `uploads`.`extra_type`!='downloaded-thumb')
UNION ALL 
(SELECT `media_items`.`id`,`media_items`.`order` FROM `media_items` WHERE `media_items`.`parent_id`='".$parent_id."')
ORDER BY `order`
")
		->result();

		$order=1;
		foreach($items_res AS $r)
		{
			if($r->type=="folder"){
				$this->db
				->where(array(
					"id"=>$r->id
				))
				->update("media_items",array(
					"order"=>$order
				));
			}else{
				$this->db
				->where(array(
					"id"=>$r->id
				))
				->update("uploads",array(
					"order"=>$order
				));
			}
			$order++;
		}
	}

	public function add_folder_ajax()
	{
		$d=array();

		$id=(int)$this->input->get("id");

		$title=$this->input->post("title");

		if(empty($title)){
			$d['errors'][]="Введите название директории!";
		}

		if(!isset($d['errors'])){
			$order=0;
			
			$order+=$this->db
			->where(array("parent_id"=>$id))
			->count_all_results("media_items");

			$order+=$this->db
			->where(array(
				"component_type"=>"module",
				"component_name"=>"media",
				"parent_id"=>$id
			))
			->count_all_results("uploads");

			$order++;

			$this->db
			->insert("media_items",array(
				"parent_id"=>$id,
				"user_id"=>$this->ci->session->userdata("user_id"),
				"title"=>$title,
				"date_add"=>mktime(),
				"order"=>$order,
				"show"=>1
			));

			$this->rebuild_folder_order($id);
		}

		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');

		print json_encode($d);
		exit;
	}

	private function remove_media_item($id)
	{
		$item_res=$this->db
		->get_where("uploads",array(
			"id"=>$id
		))
		->row();

		// удаляем все файлы
		if(file_exists("./".$item_res->file_path.$item_res->file_name)){
			unlink("./".$item_res->file_path.$item_res->file_name);
		}

		switch($item_res->name)
		{
			case'photo':

			break;
		}

		$this->db
		->where(array(
			"id"=>$id
		))
		->delete("uploads");
	}

	private function media_items_child_ids($parent_id=0,&$data=array())
	{
		$items_res=$this->db
		->query("
(SELECT `uploads`.`id`,`uploads`.`parent_id`,`uploads`.`user_id`,`uploads`.`title`,`uploads`.`name` AS `type`,`uploads`.`file_size`,`uploads`.`file_name`,`uploads`.`file_path`,`uploads`.`image_size`,`uploads`.`date_add`,`order` FROM `uploads` WHERE `uploads`.`parent_id`='".$parent_id."' && `uploads`.`component_type`='module' && `uploads`.`component_name`='media')
UNION ALL 
(SELECT `media_items`.`id`,`media_items`.`parent_id`,`media_items`.`user_id`,`media_items`.`title`,'folder' AS `type`,`media_items`.`file_size`,`media_items`.`file_name`,`media_items`.`file_path`,`media_items`.`image_size`,`media_items`.`date_add`,`order` FROM `media_items` WHERE `media_items`.`parent_id`='".$parent_id."')
ORDER BY `order` DESC
")
		->result();

		foreach($items_res AS $r)
		{
			if($r->type=="folder"){
				if(!isset($data['media_items']))$data['media_items']=array();
				$data['media_items'][]=$r->id;

				$this->media_items_child_ids($r->id,$data);
			}else{
				if(!isset($data['uploads']))$data['uploads']=array();
				$data['uploads'][]=$r->id;
			}
		}

		return $data;
	}

	public function remove_folder()
	{
		$id=(int)$this->input->get("id");

		$item_res=$this->db
		->get_where("media_items",array(
			"id"=>$id
		))
		->row();

		$media_items_child_ids=$this->media_items_child_ids($id);

		$media_items_child_ids['media_items'][]=$id;

		foreach($media_items_child_ids['uploads'] AS $media_item_id)
		{
			$this->remove_media_item($media_item_id);
		}

		foreach($media_items_child_ids['media_items'] AS $folder_id)
		{
			$this->db
			->where(array(
				"id"=>$folder_id
			))
			->delete("media_items");
		}

		redirect($this->admin_url."?m=media&a=browse&id=".$item_res->parent_id);
	}

	public function remove_photo($id=NULL)
	{
		if(!isset($id)){
			$id=(int)$this->input->get("id");
		}

		$item_res=$this->db
		->get_where("uploads",array(
			"id"=>$id
		))
		->row();

		$this->remove_media_item($id);

		redirect($this->admin_url."?m=media&a=browse&id=".$item_res->parent_id);
	}

	public function edit_folder()
	{
		$_GET['id']=(int)$this->input->get("id");

		$this->add_folder(true);
	}

	public function add_folder($edit=false)
	{
		$d=array();

		if($edit){
			$d['item_res']=$this->db
			->get_where("media_items",array(
				"id"=>$_GET['id']
			))
			->row();
		}

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=media&a=browse&id=".$_GET['id']);
		$this->buttons("form",$buttons);

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true,
			"options"=>$options
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Заголовок в окне браузера",
			"name"=>"page_title",
			"parent"=>"greed2",
			"primary"=>true,
			"check"=>array(
				"max_length"=>255
			)
		));

		$this->ci->fb->add("textarea:editor",array(
			"label"=>"Описание",
			"name"=>"description",
			"id"=>"description",
			"parent"=>"greed",
			"attr:style"=>"height:100px; width:700px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"show",
			"label"=>"опубликовано на сайте",
			"parent"=>"greed"
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Meta title",
			"name"=>"meta_title",
			"parent"=>"greed2",
			"primary"=>true,
			"check"=>array(
				"max_length"=>255
			)
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Meta keywords",
			"name"=>"meta_keywords",
			"parent"=>"greed2",
			"primary"=>true,
			"check"=>array(
				"max_length"=>255
			)
		));

		$this->ci->fb->add("textarea",array(
			"label"=>"Meta description",
			"name"=>"meta_description",
			"parent"=>"greed2",
			"attr:style"=>"height:100px; width:300px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed",
			"parent"=>"tab1"
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed2",
			"parent"=>"tab2"
		));

		$this->ci->fb->add("tabs",array(
			"tabs"=>array(
				"tab1"=>"Основное",
				"tab2"=>"SEO"
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

		if($edit){
			$this->ci->fb->change("title",array("value"=>$d['item_res']->title));
			$this->ci->fb->change("description",array("value"=>$d['item_res']->description));
			$this->ci->fb->change("page_title",array("value"=>$d['item_res']->page_title));
			$this->ci->fb->change("meta_title",array("value"=>$d['item_res']->meta_title));
			$this->ci->fb->change("meta_keywords",array("value"=>$d['item_res']->meta_keywords));
			$this->ci->fb->change("meta_description",array("value"=>$d['item_res']->meta_description));
			if($d['item_res']->show==1){
				$this->ci->fb->change("show",array("attr:checked"=>true));
			}

		}

		if($this->ci->fb->submit){
			$d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($d['global_errors'])==0){
				if($edit){
					$this->db
					->where(array(
						"id"=>$_GET['id']
					))
					->update("media_items",array(
						"title"=>$this->input->post("title"),
						"description"=>$this->input->post("description"),
						"page_title"=>$this->input->post("page_title"),
						"meta_title"=>$this->input->post("meta_title"),
						"meta_keywords"=>$this->input->post("meta_keywords"),
						"meta_description"=>$this->input->post("meta_description"),
						"show"=>$this->input->post("show")==1?1:0,
						"date_edit"=>mktime()
					));
				}

				redirect($this->admin_url."?m=media&a=browse&id=".$_GET['id']);
			}
		}

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("media/add_folder",$d);
	}

	function edit_photo()
	{
		$_GET['id']=(int)$_GET['id'];
		$this->add_photo(true);
	}

	function add_photo($edit=false)
	{
		$d=array();

		if($edit){
			$d['item_res']=$this->db
			->get_where("uploads",array(
				"id"=>$_GET['id']
			))
			->row();
		}

		if($this->input->post("make_album_cover_sm")!==false){
			$photo_id=(int)$this->input->post("photo_id");
			$album_id=(int)$this->input->post("album_id");

			$this->db
			->where(array(
				"id"=>$album_id
			))
			->update("media_items",array(
				"cover_id"=>$photo_id
			));

			print 1;
			exit;
		}

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=media&a=browse&id=".$d['item_res']->parent_id);
		$this->buttons("form",$buttons);

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true,
			"options"=>$options
		));

		$this->ci->fb->add("input:button",array(
			"label"=>"Сделать обложкой альбома",
			"name"=>"title",
			"parent"=>"greed",
			"primary"=>true,
			"attr:class"=>"btn",
			"attr:onclick"=>"mediaMakeAlbumCover(this,".$_GET['id'].",".$d['item_res']->parent_id."); return false;",
			"options"=>$options
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

		if($edit){
			$this->ci->fb->change("title",array("value"=>$d['item_res']->title));
		}

		if($this->ci->fb->submit){
			$d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($d['global_errors'])==0){
				if($edit){
					$this->db
					->where(array(
						"id"=>$_GET['id']
					))
					->update("uploads",array(
						"title"=>$this->input->post("title")
					));
				}

				redirect($this->admin_url."?m=media&a=browse&id=".$d['item_res']->parent_id);
			}
		}

		$d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("media/add_photo",$d);
	}

	function edit_video()
	{
		$_GET['id']=intval($_GET['id']);
		$this->add_video(true);
	}

	function add_video($edit=false)
	{
		$this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$parent_id=intval($this->input->get("parent_id"));

		$buttons=array();
		$buttons[]=array("save");
		if($edit){
			$buttons[]=array("apply");
		}
		$buttons[]=array("back",NULL,$this->admin_url."?m=media&a=browse&id=".$parent_id);
		$this->buttons("form",$buttons);

		if($edit){
			$this->d['item_res']=$this->db
			->get_where("uploads",array("id"=>$_GET['id']))
			->row();

			if(is_string($this->d['item_res']->options)){
				$this->d['item_res']->options=json_decode($this->d['item_res']->options);
			}
		}

		$this->ci->fb->add("input:text",array(
			"label"=>"Название",
			"name"=>"title",
			"parent"=>"greed"
		));

		$this->ci->fb->add("textarea:editor",array(
			"label"=>"Описание",
			"name"=>"description",
			"parent"=>"greed",
			"attr:style"=>"height:100px; width:300px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Ссылка на видео (youtube или vimeo)",
			"name"=>"video_link",
			"parent"=>"greed"
		));

		$this->ci->fb->add("input:checkbox",array(
			"name"=>"show",
			"label"=>"видео опубликовано на сайте",
			"parent"=>"greed"
		));
		
		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed",
			"parent"=>"tab1"
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Meta title",
			"name"=>"meta_title",
			"parent"=>"greed2",
			"primary"=>true,
			"check"=>array(
				"max_length"=>255
			)
		));

		$this->ci->fb->add("input:text",array(
			"label"=>"Meta keywords",
			"name"=>"meta_keywords",
			"parent"=>"greed2",
			"primary"=>true,
			"check"=>array(
				"max_length"=>255
			)
		));

		$this->ci->fb->add("textarea",array(
			"label"=>"Meta description",
			"name"=>"meta_description",
			"parent"=>"greed2",
			"attr:style"=>"height:100px; width:300px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true
		));

		$this->ci->fb->add("greed:vertical",array(
			"name"=>"greed2",
			"parent"=>"tab2"
		));

		$this->ci->fb->add("tabs",array(
			"tabs"=>array(
				"tab1"=>"Основное",
				"tab2"=>"Другие настройки"
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
			$this->ci->fb->change("title",array("value"=>$this->d['item_res']->title));
			$this->ci->fb->change("description",array("value"=>$this->d['item_res']->description));
			$this->ci->fb->change("name",array("value"=>$this->d['item_res']->name));
			$this->ci->fb->change("video_link",array("value"=>$this->d['item_res']->options->video_link));
			$this->ci->fb->change("meta_title",array("value"=>$this->d['item_res']->meta_title));
			$this->ci->fb->change("meta_keywords",array("value"=>$this->d['item_res']->meta_keywords));
			$this->ci->fb->change("meta_description",array("value"=>$this->d['item_res']->meta_description));
			if($this->d['item_res']->show==1){
				$this->ci->fb->change("show",array("attr:checked"=>true));
			}
		}

		if(!$edit && !$this->ci->fb->submit){
			$this->ci->fb->change("show",array("attr:checked"=>"true"));
		}

		if($this->ci->fb->submit){
			$this->d['global_errors']=$this->ci->fb->errors_list();

			if(sizeof($this->d['global_errors'])==0){
				$name=$this->input->post("name");
				if($this->input->post("name")==""){
					$name=rewrite_alias($this->input->post("title"));
				}

				$date_start=intval(strtotime($this->input->post("date_start")));
				$date_end=intval(strtotime($this->input->post("date_end")));

				$this->ci->load->library("Video_services");
				if($this->input->post("video_link")!==false){
					$video_thumb_url=$this->ci->video_services->get_video_thumb_url($this->input->post("video_link"));
				}

				$download_thumb=false;
				if($edit && $this->input->post("video_link")!=$this->d['item_res']->options->video_link){
					$download_thumb=true;
				}
				if(!$edit && !empty($video_thumb_url)){
					$download_thumb=true;
				}

				$upload_id=0;
				if($download_thumb){
					$ext=strtolower(end(explode(".",$video_thumb_url)));
					$upload_id=$this->ci->uploads->upload_file($video_thumb_url,"./uploads/media/video_thumb/".substr(md5(uniqid(rand(),1)),1,10).".".md5($video_thumb_url).".".$ext,array(
						"extra_type"=>"downloaded-thumb",
						"proc_config_var_name"=>"config[mod_media_video_picture_options]"
					));
				}

				if($edit){
					$video_id=$_GET['id'];

					$this->d['update']=array(
						"title"=>$this->input->post("title"),
						"description"=>$this->input->post("description"),
						"show"=>$this->input->post("show")==1?1:0
					);

					if($download_thumb){
						// удаляем старый thumb
						if($this->d['item_res']->thumb_id>0){
							$this->ci->uploads->remove(array("id"=>$this->d['item_res']->thumb_id));
						}

						$this->d['update']['thumb_id']=$upload_id;
						$this->d['update']['options']=json_encode(array(
							"video_link"=>$this->input->post("video_link"),
							"video_thumb_url"=>$video_thumb_url,
							"video_thumb_upload_id"=>$upload_id
						));
					}

					$this->db
					->where("id",$video_id)
					->update("uploads",$this->d['update']);
				}else{
					$order=0;
					$order+=$this->db
					->where(array("parent_id"=>$parent_id))
					->count_all_results("media_items");
					$order+=$this->db
					->where(array(
						"component_type"=>"module",
						"component_name"=>"media",
						"parent_id"=>$parent_id
					))
					->count_all_results("uploads");
					$order++;

					$options=array();
					if($download_thumb){
						$options=array(
							"video_link"=>$this->input->post("video_link"),
							"video_thumb_url"=>$video_thumb_url,
							"video_thumb_upload_id"=>$upload_id
						);
					}

					$video_id=$this->ci->uploads->upload_file(NULL,NULL,array(
						"thumb_id"=>$upload_id,
						"parent_id"=>$parent_id,
						"title"=>$this->input->post("title"),
						"description"=>$this->input->post("description"),
						"file_path"=>"uploads/media/",
						"component_type"=>"module",
						"component_name"=>"media",
						"name"=>"video",
						"extra_type"=>"video",
						"order"=>$order,
						"options"=>$options,
						"show"=>$this->input->post("show")
					));
				}

				redirect($this->admin_url."?m=media&a=browse&id=".$_GET['parent_id']);
			}
		}

		$this->plugin_trigger("onMethodBeforeRender",__FILE__,__CLASS__,__FUNCTION__,__LINE__);

		$this->d['render']=$this->ci->fb->render("render");

		$this->ci->load->adminView("media/add_video",$this->d);
	}

	function remove_video()
	{
		$id=intval($this->input->get("id"));

		$this->d['item_res']=$this->db->get_where("uploads")->row();

		$this->ci->uploads->remove(array("id IN (".$id.",".intval($this->d['item_res']->thumb_id).")"=>NULL));

		redirect($this->admin_url."?m=media&a=browse&id=".$_GET['parent_id']);
	}
}
?>