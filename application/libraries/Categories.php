<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Categories
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

	/**
	 * __call
	 *
	 * Acts as a simple way to call model methods without loads of stupid alias'
	 *
	 **/
	public function __call($method, $arguments)
	{
		if (!method_exists( $this->ci->ion_auth_model, $method) )
		{
			throw new Exception('Undefined method Fb::' . $method . '() called');
		}
	}

	/**
	 * $order - up / down
	**/
	public function order_category($category_id,$order="up")
	{
		$item_res=$this->ci->db
		->get_where("categoryes",array(
			"id"=>$category_id
		))
		->row();
		
		$this->ci->db
		->where(array(
			"type"=>$item_res->type,
			"order"=>$order=="up"?$item_res->order-1:$item_res->order+1,
			"parent_id"=>$item_res->parent_id
		))
		->update("categoryes",array(
			"order"=>$item_res->order
		));
		
		$this->ci->db
		->where(array(
			"type"=>$item_res->type,
			"id"=>$category_id,
			"parent_id"=>$item_res->parent_id
		))
		->update("categoryes",array(
			"order"=>$order=="up"?$item_res->order-1:$item_res->order+1
		));

		$this->rebuild_order($item_res->type,$item_res->parent_id);
	}

	public function rm_category($where)
	{
		$item_res=$this->ci->db
		->get_where("categoryes",$where)
		->row();
		
		$this->ci->db
		->where("id",$item_res->id)
		->delete("categoryes");

		$this->rebuild_order($item_res->type,$item_res->parent_id);

		return true;
	}

	public function update_category($d,$where=array())
	{
		if(!isset($d['type']))return false;

		if(!isset($d['date_update']))$d['date_update']=mktime();
		
		$this->ci->db
		->where($where)
		->update("categoryes",$d);

		return true;
	}

	public function add_category($d)
	{
		if(!isset($d['type']))return false;

		if(!isset($d['date_add']))$d['date_add']=mktime();
		if(!isset($d['show']))$d['show']=1;
		if(!isset($d['order'])){
			$where=array();

			$where['parent_id']=intval($d['parent_id']);
			if(isset($d['type']))$where['type']=$d['type'];

			$d['order']=0;
			$d['order']=$this->ci->db
			->where($where)
			->count_all_results("categoryes");
			$d['order']++;
		}


		$this->ci->db->insert("categoryes",$d);
		$category_id=$this->ci->db->insert_id();

		$this->rebuild_order($d['type'],$d['parent_id']);

		return $category_id;
	}

	public function rebuild_order($type="",$parent_id=0)
	{
		$res=$this->ci->db
		->order_by("order")
		->get_where("categoryes",array(
			"type"=>$type,
			"parent_id"=>$parent_id
		))
		->result();

		$i=1;
		foreach($res AS $r)
		{
			$this->ci->db
			->where(array(
				"id"=>$r->id
			))
			->update("categoryes",array(
				"order"=>$i
			));
			
			$this->rebuild_order($type,$r->id);
			$i++;
		}
	}
}