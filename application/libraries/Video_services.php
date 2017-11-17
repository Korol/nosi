<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Video_services
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

	public function get_video_player($link,$size="560x315")
	{
		$size=explode("x",$size);
		$video_id=$this->get_video_id($link);
		if(preg_match("#youtube#is",$link)){
			$html=<<<EOF
<iframe width="{$size[0]}" height="{$size[1]}" src="http://www.youtube.com/embed/{$video_id}" frameborder="0" allowfullscreen></iframe>
EOF;
		}elseif(preg_match("#vimeo#is",$link)){
			$html=<<<EOF
<iframe src="http://player.vimeo.com/video/{$video_id}?badge=0&amp;color=ffffff" width="{$size[0]}" height="{$size[1]}" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
EOF;
		}else{
			return false;
		}

		return $html;
	}

	public function get_video_id($link)
	{
		if(preg_match("#youtube#is",$link)){
			$u=parse_url($link);
			if(preg_match("#v=([^=&?]+)#is",$u['query'],$matches)){
				$video_id=$matches[1];
			}
		}elseif(preg_match("#vimeo#is",$link)){
			$u=preg_replace("#(\?.*)?$#is","",$link);
			$video_id=end(explode("/",$u));
			if(!is_numeric($video_id)) return false;
		}else{
			return false;
		}

		return $video_id;
	}

	public function get_video_duration($link)
	{
		if(preg_match("#youtube#is",$link)){
			parse_str(parse_url($link,PHP_URL_QUERY),$arr);
	        $video_id=$arr['v'];

	        $data=file_get_contents("http://gdata.youtube.com/feeds/api/videos/".$video_id."?v=2&alt=jsonc");

	        if ($data===false)return false;

	        $data=json_decode($data);

	        return $data->data->duration;
		}
	}

	public function get_video_thumb_url($link,$thumb_size="max")
	{
		$video_id=$this->get_video_id($link);

		if($video_id===false)return false;

		if($thumb_size=="max"){
			if(preg_match("#youtube#is",$link)){
				return "http://img.youtube.com/vi/".$video_id."/maxresdefault.jpg";
			}elseif(preg_match("#vimeo#is",$link)){
				$thumb_data=json_decode(file_get_contents("http://vimeo.com/api/v2/video/".$video_id.".json"));
				return current($thumb_data)->thumbnail_large;
			}else{
				return false;
			}	
		}
	}
}