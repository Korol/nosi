<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Menu
{
	/**
	 * CodeIgniter global
	 *
	 * @var string
	 **/
	protected $ci;
	protected $editor_exists=false;

	public function __construct()
	{
		$this->ci =& get_instance();
	}

	public function get_active_menu_item_ids()
	{
		$ids=array();
		if(isset($this->ci->url_structure_res) && isset($this->ci->url_structure_res->id)){
			$res=$this->ci->db
			->select("id")
			->get_where("categoryes",array(
				"type IN ('menu','menu-item')"=>NULL,
				"extra_id"=>$this->ci->url_structure_res->id
			))
			->result();

			foreach($res AS $r)
			{
				$ids[]=$r->id;
			}
		}

		$ids=array_unique($ids);

		return $ids;
	}

	public function get_menu_items($menu_id, $recursion = true)
	{
		$ids=$this->menu_child_ids($menu_id, $recursion);

		$res=$this->ci->db
		->select("*")
		->order_by("order")
		->get_where("categoryes",array(
			"id IN ('".implode("','",$ids)."')"=>NULL,
			"show"=>1
		))
		->result();

		$default_language=array();
		foreach($this->ci->languages_res AS $r)
		{
			if($r->default==1){
				$default_language=$r;
				break;
			}
		}

		foreach($res AS $r)
		{
			if($default_language->name!=$this->ci->config->config['language']){
				foreach($this->ci->languages_res AS $r2)
				{
					if($r2->enabled!=1)continue;
					
					if($r2->name==$this->ci->config->config['language']){
						$r->title=$r->{"l_title_".$r2->code};
					}
				}
			}

			$r->options=json_decode($r->options);

			$r->link="/";
			if($r->options->structure_id>0){
				$url_structure_res=$this->ci->db
				->get_where("url_structure",array(
					"id"=>$r->options->structure_id
				))
				->row();

				$r->link=$url_structure_res->url;
			}

			if(!empty($r->options->url)){
				$r->link=$r->options->url;
			}
		}

		return $res;
	}

	public function menu_child_ids($parent_id=0, $recursion = true, &$data=array())
	{
		$res=$this->ci->db
		->select("id, title")
		->order_by("order")
		->get_where("categoryes",array(
			"type IN ('menu','menu-item')"=>NULL,
			"parent_id"=>$parent_id
		))
		->result();

		foreach($res AS $r)
		{
			$data[]=$r->id;
            if($recursion)
			    $this->menu_child_ids($r->id, $recursion,$data);
		}

		return $data;
	}
}