<?php
include_once("./modules/content/content.helper.php");

class contentModule extends contentModuleHelper {
	function __construct()
	{
		parent::__construct();
	}

	public function search()
	{
		$where=array();

		if($this->input->get("keywords")!==false && $this->input->get("keywords")!=""){
			$keywords=search_clear_text($this->input->get("keywords"));
			$where['(content_posts.title LIKE \'%'.str_replace(" ","%' || content_posts.title LIKE '%",$keywords).'%\')']=NULL;
		}else{
			$this->ci->load->frontView("content/search_no_results",$d);
		}

		$this->category($where,array(
			"search"=>true
		));
	}

	public function category($where=NULL,$d=NULL)
	{
		if(is_string($this->url_structure_res->options)){
			$this->url_structure_res->options=json_decode($this->url_structure_res->options);
		}

		if(preg_match("#-([0-9]+)\.html(\?.*)?$#is",$_SERVER['REQUEST_URI'],$matches)){
			$_GET['id']=$matches[1];
			$this->view_post();
			return false;
		}

		$d['where']=array();
		if(!is_null($where)){
			$d['where']=$where;
		}
		if(isset($this->url_structure_res)){
			$this->url_structure_res->options->category_id=(int)$this->url_structure_res->options->category_id;
			if($this->url_structure_res->options->category_id>0){
				$cat_ids=array();
				$cat_ids=$this->rcats_list($this->url_structure_res->options->category_id);
				$cat_ids[]=$this->url_structure_res->options->category_id;

				$d['where']['content_posts.category_id IN ('.implode(",",$cat_ids).')']=NULL;

				// получаем информацию о выбранной категории
				$d['category_res']=$this->db
				->select("uploads.file_path, uploads.file_name")
				->select("categoryes.*")
				->join("uploads","uploads.component_type IN('module') && uploads.component_name IN('content') && uploads.extra_type IN('category_id') && uploads.extra_id=categoryes.id","left")
				->get_where("categoryes",array(
					"categoryes.id"=>$this->url_structure_res->options->category_id
				))
				->row();
			}
		}

		if($this->input->get("year")!==false){
			$year=intval($this->input->get("year"));

			$d['where']['(content_posts.date_public >= '.mktime(0,0,0,1,1,$year).' && content_posts.date_public <= '.mktime(0,0,0,1,1,$year+1).')']=NULL;
		}

		$d['where']['content_posts.archive']=0;

		$d['posts_res']=$this
		->posts_query()
		->where($d['where'])
		->get()
		->result();
		
		foreach($d['posts_res'] AS $r)
		{
			$r->link=$this->link_post_view($r);
		}

		if(!empty($d['category_res']->file_name)){
			$this->ci->load->meta(base_url($d['category_res']->file_path.$d['category_res']->file_name),"og:image");
		}
		$this->ci->load->meta($d['category_res']->title,"og:title");
		$this->ci->load->meta($d['category_res']->description,"og:description");
		$this->ci->load->meta(base_url($this->ci->url_structure_res->url),"og:url");

		$this->ci->load->frontView("content/posts_list",$d);
	}

	public function view_post()
	{
		$id=(int)$this->input->get("id");

		$d['post_res']=$this
		->posts_query()
		->where(array(
			"content_posts.id"=>$id
		))
		->get()
		->row();

		if(is_string($d['post_res']->widgets_options)){
			$d['post_res']->widgets_options=json_decode($d['post_res']->widgets_options);
		}

		if(is_string($d['post_res']->category_path)){
			$d['post_res']->category_path=json_decode($d['post_res']->category_path);
		}

		$this->ci->load->meta($d['post_res']->meta_title,"title");
		$this->ci->load->meta($d['post_res']->meta_description,"description");
		$this->ci->load->meta($d['post_res']->meta_keywords,"keywords");

		if($d['post_res']->disallow_bot_index==1){
			$this->ci->load->meta("noindex","robots");
		}

		$d['post_res']->view_hits++;
		$this->db->query("UPDATE `content_posts` SET `view_hits`=`view_hits`+1 WHERE `id`='".$d['post_res']->id."'");

		$this->ci->item_res=$d['post_res'];

		$this->ci->load->frontView("content/view_post",$d);
	}
}
?>