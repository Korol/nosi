<?php
class static_pageModule extends Cms_modules {
	function __construct()
	{
		parent::__construct();

		$this->load->helper('url');
		$this->load->helper('cms');
	}

	public function view_page()
	{
		$this->d=array();

		$this->d['page_res']=$this->db
		->get_where("pages",array(
			"id"=>$this->url_structure_res->extra_id
		))
		->row();

		if(!empty($this->d['page_res']->php_file_path) && file_exists($this->d['page_res']->php_file_path)){
			ob_start();
				include_once($this->d['page_res']->php_file_path);
			$this->d['page_res']->content2=ob_get_contents();
			ob_end_clean();
		}

		$this->ci->load->meta($this->d['page_res']->meta_title,"title");
		$this->ci->load->meta($this->d['page_res']->meta_description,"description");
		$this->ci->load->meta($this->d['page_res']->meta_keywords,"keywords");

		if($this->d['page_res']->disallow_bot_index==1){
			$this->ci->load->meta("noindex","robots");
		}

		$this->ci->load->frontView("static_page/view_page",$this->d);
	}



	public function sitemap()
	{
		$xml="";

		$menu_res=$this->db
		->get_where("categoryes",array(
			"type"=>"menu"
		))
		->result();

		$urls=array();
		foreach($menu_res AS $menu_r)
		{
			$items=$this->ci->menu->get_menu_items($menu_r->id);

			foreach($items AS $r)
			{
				if(isset($urls[$r->link]) 
					|| empty($r->link)
					|| strpos($r->link, '://') != false)
						continue;
				$r->link=base_url($r->link);

				$urls[$r->link]=array(
					"loc"=>$r->link,
					"lastmod"=>date("Y-d-m"),
					"changefreq"=>"monthly",
					"priority"=>"0.8"
				);
			}
		}

		$this->enabled_modules=$this->db
		->select("name, type")
		->get_where("components",array(
			"enabled"=>1
		))
		->result();

		foreach($this->enabled_modules AS $module_r)
		{
			if($module_r->type=="module"){
				if(!file_exists("./modules/".$module_r->name."/".$module_r->name.".info.php"))continue;
				include_once("./modules/".$module_r->name."/".$module_r->name.".info.php");
				$module_info_name=$module_r->name."ModuleInfo";
				$module_r->info=new $module_info_name;

				if(!method_exists($module_r->info,"get_sitemap_data"))continue;

				$get_sitemap_data=$module_r->info->get_sitemap_data();
				if(!is_array($get_sitemap_data) || sizeof($get_sitemap_data)<1)continue;
				$urls+=$get_sitemap_data;
			}
		}

		$xml.=<<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
EOF;
		
		foreach($urls AS $r)
		{
			$xml.=<<<EOF
<url>
<loc>{$r['loc']}</loc>
<lastmod>{$r['lastmod']}</lastmod>
<changefreq>{$r['changefreq']}</changefreq>
<priority>{$r['priority']}</priority>
</url>
EOF;
		}
   		
		$xml.=<<<EOF
</urlset>
EOF;
		header("Content-Type: text/xml");
		header("Expires: Thu, 19 Feb 1998 13:24:18 GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Cache-Control: post-check=0,pre-check=0");
		header("Cache-Control: max-age=0");
		header("Pragma: no-cache");

		print $xml;
	}
}
?>