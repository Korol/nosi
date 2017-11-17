<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Uploads
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

	function upload_file($source_file_path,$dest_file_path,$insert=array(),$order_where=NULL)
	{
		if(empty($source_file_path) || empty($dest_file_path)){
			return false;
		}

		r_mkdir(dirname($dest_file_path));

		if(!move_uploaded_file($source_file_path,$dest_file_path)){
			copy($source_file_path,$dest_file_path);
		}

		$file_name=basename($dest_file_path);

		$file_size=filesize($dest_file_path);
		list($image_width,$image_height)=getimagesize($dest_file_path);

		if(!empty($insert['proc_config_var_name'])){
			$images_options_res=$this->ci->db->get_where("config",array(
				"var_name"=>$insert['proc_config_var_name']
			))->row();

			if(isset($images_options_res->id)){
				$this->ci->load->library("img");
				if(!empty($images_options_res->value)){
					$images_options_data=$this->ci->img->proc($dest_file_path,$images_options_res->value);
				}
			}

			$insert['thumb_files']=implode("\n",$images_options_data['out_files']);
		}

		$file_path=dirname($dest_file_path)."/";
		$file_path=preg_replace("#^./#is","",$file_path);
		$file_path=str_replace(FCPATH,"",$file_path);

		if(!isset($insert['user_id']))$insert['user_id']=$this->ci->session->userdata("user_id");
		if(!isset($insert['file_size']))$insert['file_size']=$file_size;
		if(!isset($insert['file_name']))$insert['file_name']=$file_name;
		if(!isset($insert['file_path']))$insert['file_path']=$file_path;
		if(!isset($insert['image_size']))$insert['image_size']=$image_width."x".$image_height;
		if(!isset($insert['component_type']))$insert['component_type']="module";
		if(!isset($insert['component_name']))$insert['component_name']="media";
		if(!isset($insert['date_add']))$insert['date_add']=mktime();

		if(is_array($order_where)){
			$order=$this->ci->db
			->where($order_where)
			->count_all_results("uploads");
			$order++;

			if(!isset($insert['order']))$insert['order']=$order;
		}

		$this->ci->db->insert("uploads",$insert);

		if(is_array($order_where)){
			$this->rebuild_uploads_order($order_where);
		}

		$upload_id=$this->ci->db->insert_id();

		return $upload_id;
	}

	function rebuild_uploads_order($where)
	{
		$uploads_res=$this->ci->db
		->select("id, order")
		->order_by("order")
		->get_where("uploads",$where);

		$order=1;
		foreach($uploads_res AS $r)
		{
			$this->ci->db
			->where("id",$r->id)
			->update("uploads",array(
				"order"=>$order
			));

			$order++;
		}
	}

	function remove($where=array())
	{
		$uploads_res=$this->ci->db
		->where($where)
		->get("uploads")
		->result();

		$this->log=array();
		$dirs=array();
		// удаляем файлы
		foreach($uploads_res AS $r)
		{
			foreach(explode("\n",$r->thumb_files) AS $thumb_file)
			{
				$thumb_file=trim($thumb_file);
				if(file_exists("./".$thumb_file)){
					$this->log[]="Файл '"."./".$thumb_file."' удален!";
					unlink("./".$thumb_file);
				}else{
					$this->log[]="Файл '"."./".$thumb_file."' не существует!";
				}

				if(!isset($dirs[dirname("./".$thumb_file)]))$dirs[dirname("./".$thumb_file)]=0;
				$dirs[dirname("./".$thumb_file)]++;
			}

			if(file_exists("./".$r->file_path.$r->file_name)){
				$this->log[]="Файл '"."./".$r->file_path.$r->file_name."' удален!";
				unlink("./".$r->file_path.$r->file_name);
			}else{
				$this->log[]="Файл '"."./".$r->file_path.$r->file_name."' не существует!";
			}

			if(!isset($dirs[dirname("./".$r->file_path.$r->file_name)]))$dirs[dirname("./".$r->file_path.$r->file_name)]=0;
			$dirs[dirname("./".$r->file_path.$r->file_name)]++;
		}

		// удаляем директории если они пустые (только те что в директории ./uploads/)
		foreach($dirs AS $dir_path=>$removed_num)
		{
			if(!preg_match("#^./uploads/[^/]+#is",$dir_path))continue;

			$files_num=directory_files_num($dir_path);
			if($files_num===0){
				directory_remove($dir_path);
				$this->log[]="Директория '"."./".$r->file_path.$r->file_name."' удален!";
			}
		}

		$uploads_res=$this->ci->db
		->where($where)
		->delete("uploads");

		return true;
	}
}