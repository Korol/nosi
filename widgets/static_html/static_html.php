<?php
class static_htmlWidget extends Cms_modules {
	function __construct()
	{
		parent::__construct();
	}

	function view_widget(&$r)
	{
		$default_language=array();
		foreach($this->ci->languages_res AS $r2)
		{
			if($r2->default==1){
				$default_language=$r2;
				break;
			}
		}

		if($default_language->name!=$this->ci->config->config['language']){
			foreach($this->ci->languages_res AS $r2)
			{
				if($r2->enabled!=1)continue;
				
				if($r2->name==$this->ci->config->config['language']){
					$r->content=$r->{"l_content_".$r2->code};
				}
			}
		}

		return $r->content;
	}
}
?>