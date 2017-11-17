<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Img
{
	/**
	 * CodeIgniter global
	 *
	 * @var string
	 **/
	protected $ci;
	private $image_library="gd2";
//	private $quality=100;
	private $quality=90;

	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->load->library("image_lib");
	}

	public function __call($method, $arguments)
	{
		if (!method_exists( $this->ci->ion_auth_model, $method) )
		{
			throw new Exception('Undefined method Img::' . $method . '() called');
		}
	}

	private function _config(&$config)
	{
		if(empty($config['image_source']))die("Img - No image source!");

		$config['quality']=isset($config['quality'])?$config['quality']:$this->quality;
		$config['image_library']=isset($config['image_library'])?$config['image_library']:$this->image_library;

		if(preg_match("#^./#is",$config['image_source'])){
			$config['image_source']=preg_replace("#^./#is","",$config['image_source']);
			$config['image_source']=FCPATH.$config['image_source'];
		}

		if(!empty($config['new_image'])){
			r_mkdir(dirname($config['new_image']));
		}
	}

	public function watermark($config)
	{
		// $this->_config($config);

		$this->ci->image_lib->initialize(array(
			"image_library"=>$config['image_library'],
			"source_image"=>$config['image_source'],
			"wm_type"=>"overlay",
			"wm_overlay_path"=>$config['wm_overlay_path'],
			"wm_vrt_alignment"=>$config['wm_vrt_alignment'],
			"wm_hor_alignment"=>$config['wm_hor_alignment'],
                        "wm_vrt_offset"=>$config['wm_vrt_offset'],
			"wm_padding"=>0,
			"quality"=>$config['quality']
		));

		if(!$this->ci->image_lib->watermark()){
		    return $this->ci->image_lib->display_errors();
		}else{
			$this->ci->image_lib->clear();
		}

		return true;
	}

	public function crop($config)
	{
		// $this->_config($config);
		
		if(!isset($config['size']))die("Img::square - No image size!");
		if(!isset($config['new_image']))$config['new_image']=$config['image_source'];
		// if(!isset($config['new_image']))$config['dynamic_output']=true;
		
		$cropThumbSize=explode("x",$config['size']);

		list($_width,$_height)=getimagesize($config['image_source']);
		$img_r=min($_width,$_height)/max($_width,$_height);

		$y_axis=0;
		$x_axis=0;
		if($cropThumbSize[0]>=$cropThumbSize[1]){
			// горизонтально
			$thumb_width=$cropThumbSize[0];
			if($_width>$_height){
				$thumb_height=$cropThumbSize[0]*$img_r;
			}else{
				$thumb_height=$cropThumbSize[0]/$img_r;
			}

			if($thumb_height>=$cropThumbSize[1]){
				// все ок, высота получившейся thumb выше или равняется
				$y_axis=($thumb_height-$cropThumbSize[1])/2;
			}else{
				// thumb слишком низкий! подстраиваем его по высоте
				if($_width>$_height){
					$thumb_width=$cropThumbSize[1]/$img_r;
				}else{
					$thumb_width=$cropThumbSize[1]*$img_r;
				}
				$thumb_height=$cropThumbSize[1];
			}
		}else{
			// вертикально
			if($_width>$_height){
				$thumb_width=$cropThumbSize[1]/$img_r;
			}else{
				$thumb_width=$cropThumbSize[1]*$img_r;
			}
			$thumb_height=$cropThumbSize[1];

			$x_axis=($thumb_width-$cropThumbSize[0])/2;			
		}

		$this->ci->image_lib->initialize(array(
			'image_library'=>$config['image_library'],
			"source_image"=>$config['image_source'],
			"new_image"=>$config['new_image'],
			"maintain_ratio"=>false,
			"width"=>$thumb_width,
			"height"=>$thumb_height,
			"quality"=>$config['quality']
		));

		if(!$this->ci->image_lib->resize()){
			return $this->ci->image_lib->display_errors();
		}else{
			$this->ci->image_lib->clear();
		}
		
		$this->ci->image_lib->initialize(array(
			'image_library'=>$config['image_library'],
			'source_image'=>$config['new_image'],
			'new_image'=>$config['new_image'],
			'create_thumb'=>false,
			'maintain_ratio'=>false,
			'width'=>$cropThumbSize[0],
			'height'=>$cropThumbSize[1],
			'x_axis'=>$x_axis,
			'y_axis'=>$y_axis,
			"quality"=>$config['quality']
		));

		if(!$this->ci->image_lib->crop()){
		    return $this->ci->image_lib->display_errors();
		}else{
			$this->ci->image_lib->clear();
		}

		return true;
	}

	public function square($config)
	{
		// $this->_config($config);
		
		if(!isset($config['size']))die("Img::square - No image size!");
		if(!isset($config['new_image']))$config['dynamic_output']=true;
		
		$cropThumbSize=$config['size'];

		list($_width,$_height)=getimagesize($config['image_source']);

		$r=max($_width,$_height)/min($_width,$_height);
		if($_width>$_height){
			$thumb_height=$cropThumbSize;
			$thumb_width=$cropThumbSize*$r;
			$x_axis=($thumb_width-$cropThumbSize)/2;
			$y_axis=0;
		}else{
			$thumb_height=$cropThumbSize*$r;
			$thumb_width=$cropThumbSize;
			$x_axis=0;
			$y_axis=($thumb_height-$cropThumbSize)/2;
		}

		$this->ci->image_lib->initialize(array(
			'image_library'=>$config['image_library'],
			"source_image"=>$config['image_source'],
			"new_image"=>$config['new_image'],
			"maintain_ratio"=>false,
			"width"=>$thumb_width,
			"height"=>$thumb_height,
			"quality"=>$config['quality']
		));

		if(!$this->ci->image_lib->resize()){
			return $this->ci->image_lib->display_errors();
		}else{
			$this->ci->image_lib->clear();
		}

		$this->ci->image_lib->initialize(array(
			'image_library'=>$config['image_library'],
			'source_image'=>$config['new_image'],
			'new_image'=>$config['new_image'],
			'create_thumb'=>false,
			'maintain_ratio'=>false,
			'width'=>$cropThumbSize,
			'height'=>$cropThumbSize,
			'x_axis'=>$x_axis,
			'y_axis'=>$y_axis,
			"quality"=>$config['quality']
		));

		if(!$this->ci->image_lib->crop()){
		    return $this->ci->image_lib->display_errors();
		}else{
			$this->ci->image_lib->clear();
		}

		return true;
	}

	public function resize($config)
	{
		// $this->_config($config);
		
		$maintain_ratio=true;
		if(isset($config['size'])){
			list($width,$height)=explode("x",$config['size']);
			$maintain_ratio=false;
		}elseif(isset($config['maxSize'])){
			$width=$height=$config['maxSize'];
		}elseif(isset($config['minSize'])){
			list($current_width,$current_height,)=getimagesize($config['image_source']);
			
			$rat=max($current_width/$current_height,$current_height/$current_width);
			if($current_width>$current_height){
				$width=$config['minSize']*($current_width/$current_height);
				$height=$config['minSize'];
			}else{
				$height=$config['minSize']*($current_height/$current_width);
				$width=$config['minSize'];
			}
			
			$maintain_ratio=false;
		}elseif(isset($config['maxHeight']) || isset($config['maxWidth'])){
			list($width,$height,)=getimagesize($config['image_source']);
			
			if(isset($config['maxWidth'])){
				if($width>$config['maxWidth']){
					$height=$config['maxWidth']*($height/$width);
					$width=$config['maxWidth'];
				}
			}
			
			if(isset($config['maxHeight'])){
				if($height>$config['maxHeight']){
					$width=$config['maxHeight']*($width/$height);
					$height=$config['maxHeight'];
				}
			}
			
			$maintain_ratio=false;
		}else{
			die("Img::resize - No size set!");
		}

		$this->ci->image_lib->initialize(array(
			'image_library'=>$config['image_library'],
			"source_image"=>$config['image_source'],
			"new_image"=>$config['new_image'],
			"maintain_ratio"=>$maintain_ratio,
			"width"=>$width,
			"height"=>$height,
			"quality"=>$config['quality']
		));

		if(!$this->ci->image_lib->resize()){
			return $this->ci->image_lib->display_errors();
		}else{
			$this->ci->image_lib->clear();
		}

		return true;
	}

	private function str2args($functionName,$str,$imageName,$imagePath)
	{
		// $fileName=basename($filePath);
		// $fileDir=dirname($filePath);
		$fileName=$imageName;
		$fileDir=$imagePath;
		$args=explode(",",$str);
		
		$oargs=array();
		foreach($args AS $arg)
		{
			$arg=explode(":",trim($arg));
			$arg[1]=str_replace("%IMAGE_PATH%",$imagePath,$arg[1]);
			$arg[1]=str_replace("%IMAGE_FULL_PATH%",$imagePath.$imageName,$arg[1]);
			$arg[1]=str_replace("%IMAGE_NAME%",$imageName,$arg[1]);
			
			$oargs[$arg[0]]=$arg[1];
		}

		if(empty($oargs['image_source']))$oargs['image_source']=$imagePath.$imageName;
		
		return $oargs;
	}

	public function proc($imageFullPath,$command)
	{
		$imageName=basename($imageFullPath);
		$imagePath=dirname($imageFullPath)."/";

		if(empty($command))return false;
		
		$functions=array("resize","square","watermark","copy","remove","crop");
		
		preg_match_all("#(".implode("|",$functions).")\(([^)]+)\)#is",$command,$functions);
		
		$out=array("functions"=>array(),"out_files"=>array());
		foreach($functions[2] AS $i=>$args)
		{
			$args=$this->str2args($functions[1][$i],$args,$imageName,$imagePath);

			$this->_config($args);
			
			switch($functions[1][$i])
			{
				case'resize':
					if(($result=$this->resize($args))!==true){
						die($result);
					}
				break;
				case'watermark':
					if(($result=$this->watermark($args))!==true){
						die($result);
					}
				break;
				case'square':
					if(($result=$this->square($args))!==true){
						die($result);
					}
				break;
				case'crop':
					if(($result=$this->crop($args))!==true){
						die($result);
					}
				break;
				case'copy':
					copy($args['image_source'],$args['new_image']);
				break;
				case'remove':
					if(file_exists($args['image_source']))unlink($args['image_source']);
				break;
			}

			$args['function']=$functions[1][$i];
			$out['functions'][]=$args;

			if($functions[1][$i]=="remove"){
				$key=array_search($args['image_source'],$out['out_files']);
				if($key!==false)unset($out['out_files'][$key]);
			}else{
				$out['out_files'][]=$args['new_image'];
			}
		}

		$out['out_files']=array_unique($out['out_files']);

		return $out;
	}
}