<?php
class contentModuleHelper extends Cms_modules {
	function __construct()
	{
		parent::__construct();

		// подключаем библиотеку для отрисовки интерфейса (формы, таблицы и тп.)
		$this->ci->load->library("fb");
		$this->load->helper('url');
		$this->load->helper('cms');
	}

	public function rcats_list($parent_id=0,$level=-1,&$rows=array())
	{
		$cats_res=$this->db
		->order_by("order")
		->get_where("categoryes",array(
			"type"=>"content-category",
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

	public function posts_query()
	{
		$db=clone $this->ci->db;

		return $db

		->select("uploads.file_name AS main_picture_file_name, uploads.file_path AS main_picture_file_path, uploads.image_size AS main_picture_image_size")
		->join("uploads","uploads.extra_id = content_posts.id && uploads.extra_type = 'post_id' && uploads.name = 'main_picture'","left")

		->select("content_posts.*")
		->from("content_posts")
		->order_by("content_posts.order","DESC");
	}

	public function link_post_view(&$r)
	{
		$category_path=$_category_path=explode(",",$r->category_path);

		$r->cat_res=$this->db
		->select("url_structure.url")
		->get_where("url_structure",array(
			"url_structure.module"=>"content",
			"url_structure.extra_name"=>"category_id",
			"url_structure.extra_id"=>$r->category_id
		))
		->row();

		if(!empty($r->cat_res->url)){
			return rtrim($r->cat_res->url,"/")."/".$r->alias."-".$r->id.".html";
		}

		return false;
	}
}
?>