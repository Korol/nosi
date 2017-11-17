<?php
class mediaModule extends Cms_modules {
	function __construct()
	{
		parent::__construct();

		$this->load->helper('url');
		$this->load->helper('cms');
	}

	public function browse()
	{
		$this->d['item_type']="root";
		if(is_string($this->ci->url_structure_res->options)){
			$this->ci->url_structure_res->options=json_decode($this->ci->url_structure_res->options);
		}

		if(isset($this->ci->url_structure_res->options->album_id) && $this->ci->url_structure_res->options->album_id>0){
			$this->d['item_id']=$this->ci->url_structure_res->options->album_id;
			$this->d['item_type']="a";
		}

		if(preg_match("#-([0-9]+)([a-zA-Z])\.html(\?.*)?$#is",$_SERVER['REQUEST_URI'],$matches)){
			$this->d['item_id']=$matches[1];
			$this->d['item_type']=$matches[2];
		}

		if($this->d['item_id']>0){
			if($this->d['item_type']=="a"){
				$this->d['item_res']=$this->db
				->get_where("media_items",array(
					"id"=>$this->d['item_id']
				))
				->row();

				if($this->d['item_res']->id<1)show_404();

				$this->d['item_res']->childs=$this->db->query("
				(SELECT `uploads`.`id`,`uploads`.`thumb_id`,`uploads`.`parent_id`,`uploads`.`user_id`,`uploads`.`title`,`uploads`.`name` AS `type`,'' AS `cover_id`,'' AS `cover_file_name`,`uploads`.`file_size`,`uploads`.`file_name`,`uploads`.`file_path`,`uploads`.`image_size`,`uploads`.`date_add`,`uploads`.`order`,'' AS `cover_file_name2`,'' AS `cover_file_path`,`uploads2`.`file_path` AS `thumb_file_path`,`uploads2`.`file_name` AS `thumb_file_name` FROM `uploads` LEFT JOIN `uploads` AS `uploads2` ON `uploads2`.`id`=`uploads`.`thumb_id` WHERE `uploads`.`parent_id`='".$this->d['item_res']->id."' && `uploads`.`component_type`='module' && `uploads`.`component_name`='media' && `uploads`.`extra_type`!='downloaded-thumb')
				UNION ALL 
				(SELECT `media_items`.`id`,'thumb_id' AS '',`media_items`.`parent_id`,`media_items`.`user_id`,`media_items`.`title`,'folder' AS `type`,`media_items`.`cover_id`,`media_items`.`cover_file_name`,`media_items`.`file_size`,`media_items`.`file_name`,`media_items`.`file_path`,`media_items`.`image_size`,`media_items`.`date_add`,`media_items`.`order`,`uploads`.`file_name` AS `cover_file_name2`,`uploads`.`file_path` AS `cover_file_path`,'thumb_file_name' AS '','thumb_file_path' AS '' FROM `media_items` LEFT JOIN `uploads` ON `uploads`.`id`=`media_items`.`cover_id` WHERE `media_items`.`parent_id`='".$this->d['item_res']->id."' && `media_items`.`show`='1')
				ORDER BY `order` DESC
				")->result();
				
				foreach($this->d['item_res']->childs AS $r)
				{
					$r->comments_num=2;
				}

				$this->d['item_res']->view_hits++;
				$this->db
				->query("UPDATE `media_items` SET `view_hits`=`view_hits`+1 WHERE `id`='".$this->d['item_id']."'");
			}else{
				$this->d['item_res']=$this->db->get_where("uploads",array(
					"id"=>$this->d['item_id']
				))
				->row();

				if($this->d['item_res']->id<1)show_404();

				$this->d['item_res']->comments_num=$this->db
				->where(array(
					"component_type"=>"module",
					"component_name"=>"media",
					"extra_type"=>"item_id",
					"extra_id"=>$this->d['item_res']->id
				))
				->count_all_results("comments");

				$this->d['item_res']->view_hits++;
				$this->db
				->query("UPDATE `uploads` SET `view_hits`=`view_hits`+1 WHERE `id`='".$this->d['item_id']."'");
			}

			$this->ci->load->meta($this->d['item_res']->meta_title,"title");
			$this->ci->load->meta($this->d['item_res']->meta_description,"description");
			$this->ci->load->meta($this->d['item_res']->meta_keywords,"keywords");
		}else{
			if($this->d['item_type']=="root"){
				$parent_id=0;
				$this->d['item_res']=(object)array();
				$this->d['item_res']->childs=$this->db->query("
				(SELECT `uploads`.`id`,`uploads`.`thumb_id`,`uploads`.`parent_id`,`uploads`.`user_id`,`uploads`.`title`,`uploads`.`name` AS `type`,'' AS `cover_id`,'' AS `cover_file_name`,`uploads`.`file_size`,`uploads`.`file_name`,`uploads`.`file_path`,`uploads`.`image_size`,`uploads`.`date_add`,`uploads`.`order`,'' AS `cover_file_name2`,'' AS `cover_file_path`,`uploads2`.`file_path` AS `thumb_file_path`,`uploads2`.`file_name` AS `thumb_file_name`,`uploads`.`view_hits` FROM `uploads` LEFT JOIN `uploads` AS `uploads2` ON `uploads2`.`id`=`uploads`.`thumb_id` WHERE `uploads`.`parent_id`='".$parent_id."' && `uploads`.`component_type`='module' && `uploads`.`component_name`='media' && `uploads`.`extra_type`!='downloaded-thumb')
				UNION ALL 
				(SELECT `media_items`.`id`,'thumb_id' AS '',`media_items`.`parent_id`,`media_items`.`user_id`,`media_items`.`title`,'folder' AS `type`,`media_items`.`cover_id`,`media_items`.`cover_file_name`,`media_items`.`file_size`,`media_items`.`file_name`,`media_items`.`file_path`,`media_items`.`image_size`,`media_items`.`date_add`,`media_items`.`order`,`uploads`.`file_name` AS `cover_file_name2`,`uploads`.`file_path` AS `cover_file_path`,'thumb_file_name' AS '','thumb_file_path' AS '',`media_items`.`view_hits` FROM `media_items` LEFT JOIN `uploads` ON `uploads`.`id`=`media_items`.`cover_id` WHERE `media_items`.`parent_id`='".$parent_id."' && `media_items`.`show`='1')
				ORDER BY `order` DESC
				")->result();

				foreach($this->d['item_res']->childs AS $r)
				{
					$r->comments_num=$this->db
					->where(array(
						"component_type"=>"module",
						"component_name"=>"media",
						"extra_type"=>"item_id",
						"extra_id"=>$r->id
					))
					->count_all_results("comments");

					if($r->type!="video"){
						$r->childs_num=$this->db
						->where(array(
							"parent_id"=>$r->id,
							"component_type"=>"module",
							"component_name"=>"media"
						))
						->count_all_results("uploads");
					}
				}
			}
		}

		if(isset($this->d['item_res']->options) && is_string($this->d['item_res']->options))$this->d['item_res']->options=json_decode($this->d['item_res']->options);

		if($this->d['item_type']=="v"){
			$this->ci->load->library("Video_services");
			// $video_id=$this->ci->video_services->get_video_id($this->d['item_res']->options->video_link);
			$this->d['video_player']=$this->ci->video_services->get_video_player($this->d['item_res']->options->video_link,"900x506");
		}

		// для фейсбука
		$cover_file_url="";
		if($this->d['item_type']=="root"){
			$this->ci->load->meta($this->ci->url_structure_res->title,"og:title");
			$current_url=base_url($this->ci->url_structure_res->url);
		}elseif($this->d['item_type']=="a"){
			if(isset($this->d['item_res']->cover_id)){
				foreach($this->d['item_res']->childs AS $child)
				{
					if($child->id==$this->d['item_res']->cover_id){
						$cover_file_url=base_url($child->file_path.$child->file_name);
					}
				}
			}
		}elseif($this->d['item_type']=="v"){
			$thumb_res=$this->db->get_where("uploads",array(
				"id"=>intval($this->d['item_res']->thumb_id)
			))
			->row();
			if(!empty($this->d['item_res']->options->video_thumb_url)){
				$cover_file_url=base_url($thumb_res->file_path.$thumb_res->file_name);
			}
			$this->ci->load->meta("movie","og:type");
			$this->ci->load->meta("video",$this->d['item_res']->options->video_link);
			$current_url=base_url($this->ci->url_structure_res->url.rewrite_alias($this->d['item_res']->title)."-".$this->d['item_res']->id.$type."v.html");
			
		}
		$this->ci->load->meta($this->d['item_res']->title,"og:title");
		$this->ci->load->meta($current_url,"og:url");
		$this->ci->load->meta($cover_file_url,"og:image");
		$this->ci->load->meta($this->d['item_res']->description,"og:description");

		$this->ci->load->title($this->d['item_res']->title);

		$this->ci->load->frontView("media/browse",$this->d);
	}
}
?>
