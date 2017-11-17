<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Loader extends CI_Loader {
	public $ci;
	private $css=array();
	private $js=array();
	private $meta=array();
	private $title=array();

	public function __construct()
	{
		parent::__construct();

		//$this->add_package_path("templates/");
		
		$this->ci=&get_instance();
	}

	public function access($rule_name,$component_name,$component_type)
	{
		if(!isset($this->ci->current_user_group_res->access_rules->{$component_type}))return false;
		if(!isset($this->ci->current_user_group_res->access_rules->{$component_type}->{$component_name}))return false;
		if(!isset($this->ci->current_user_group_res->access_rules->{$component_type}->{$component_name}->{$rule_name}))return false;

		if($this->ci->current_user_group_res->access_rules->{$component_type}->{$component_name}->{$rule_name}==1){
			return true;
		}

		return false;
	}

	public function check_page_access($rule_name,$component_name,$component_type)
	{
		if(!$this->access($rule_name,$component_name,$component_type)){
			die("NO ACCESS FOR YOUR GROUP!");
		}
	}

	// функция для отображения страницы сайта со стандартным шаблоном
	public function frontView($view,&$vars=array(),$template="structure")
	{
		$this->_ci_view_paths["templates/default/"]=true;

		if(!isset($this->ci->template_data))$this->ci->template_data=array();
		$vars=array_merge($vars,$this->ci->template_data);
		
		$this->ci->template_data['content']=parent::view($view,$vars,true);

		parent::view($template,$this->ci->template_data);
	}

	public function adminView($view,&$vars=array(),$template="structure")
	{
		unset($this->_ci_view_paths["templates/"]);
		$this->_ci_view_paths["templates/default/admin/"]=true;
		
		if(!isset($this->ci->template_data))$this->ci->template_data=array();
		$vars=array_merge($vars,$this->ci->template_data);
		
		$this->ci->template_data['content']=parent::view($view,$vars,true);
		
		parent::view($template,$this->ci->template_data);
	}

	public function widgets($position="",$order="")
	{
		$position=trim($position);
		if(empty($position))return false;
                
                // новая реализация "типа виджета" mainCat
                if($position == 'mainCat') return $this->mainCat();

		$this->ci->db
		->select("widgets.*")
		->select("components.name AS component_name");

		if(isset($this->url_structure_res) && $this->url_structure_res->id>0){
			$this->ci->db
			->select("components_view_rules.extra_val");
		}

		$this->ci->db
		->join("components","components.id = widgets.widget_id && components.enabled = 1");
		
		if(isset($this->url_structure_res) && $this->url_structure_res->id>0){
			$this->ci->db
			->join("components_view_rules","components_view_rules.extra_name IN('show-structure')  && components_view_rules.component_id = widgets.id && components_view_rules.extra_val IN('".$this->url_structure_res->id."','all')","left");
		}

		if(empty($order)){
			$order="widgets.date_add DESC";
		}

		$widgets_res=$this->ci->db
		->group_by("widgets.id")
		->order_by($order)
		->get_where("widgets",array(
			"widgets.position"=>$position,
			"widgets.enabled"=>1
		))
		->result();//var_dump($this->ci->db->last_query(), $widgets_res);

		$html="";

		foreach($widgets_res AS $r)
		{
			// у этой страницы есть уникальные настройки виджетов, ипользуем их
			if(isset($this->item_res->widgets_options) && isset($this->item_res->widgets_options->{$r->id})){
				// этот виджет необходимо скрыть
				if($this->item_res->widgets_options->{$r->id}->enabled==0){
					continue;
				}elseif($this->item_res->widgets_options->{$r->id}->enabled==-1){
					if(isset($this->url_structure_res) && $this->url_structure_res->id>0 && is_null($r->extra_val)){
						continue;
					}
				}
			}else{
				// этот виджет не разрешен к показу в данной еденице структуры
				if(isset($this->url_structure_res) && $this->url_structure_res->id>0 && is_null($r->extra_val)){
					continue;
				}
			}

			if(!file_exists("./widgets/".$r->component_name."/".$r->component_name.".php"))die("Widget file not found! "."./widgets/".$r->component_name."/".$r->component_name.".php");
			include_once("./widgets/".$r->component_name."/".$r->component_name.".php");

			$widget_name=$r->component_name."Widget";
			if(!class_exists($widget_name)){
				die("Widget class not found: "."./widgets/".$r->component_name."/".$r->component_name.".php : class ".$widget_name."");
			}

			$widget=new $widget_name;

			$widget->widget_id=$r->id;

			if(method_exists($widget,"view_widget")){
				$html.=$widget->view_widget($r);
			}else{
				die("Widget method not found: "."./widgets/".$r->component_name."/".$r->component_name.".php : class ".$widget_name."->view_widget()");
			}
		}

		return $html;
	}

	public function css($p,$pos="append")
	{
		if(is_string($p)){
			$p=array("file_path"=>$p);
		}

		if($pos=="prepend"){
			array_unshift($this->css,$p);
		}else{
			$this->css[]=$p;
		}
	}

	public function js($p,$pos="append")
	{
		if(is_string($p)){
			$p=array("file_path"=>$p);
		}

		if($pos=="prepend"){
			array_unshift($this->js,$p);
		}else{
			$this->js[]=$p;
		}
	}

	public function printHead()
	{
		$head="";

		$head.=<<<EOF
<title>
EOF;
		$head.=$this->printTitle();
		$head.=<<<EOF
</title>\n
EOF;

		foreach($this->css AS $r)
		{
			if(!empty($r['html'])){
				$head.="\t\t".$r['html']."\n";
				continue;
			}
			$head.=<<<EOF
\t<link href="{$r['file_path']}" rel="stylesheet" type="text/css">\n
EOF;
		}

		foreach($this->js AS $r)
		{
			if(!empty($r['html'])){
				$head.="\t\t".$r['html']."\n";
				continue;
			}
			$head.=<<<EOF
\t<script src="{$r['file_path']}" type="text/javascript"></script>\n
EOF;
		}

		if(!empty($this->config->config['template_favicon'])){
			$head.=<<<EOF
\t<link href="/{$this->config->config['template_favicon']}" rel="icon" type="image/x-icon" />\n
\t<link href="/{$this->config->config['template_favicon']}" rel="shortcut icon" type="image/x-icon" />\n
EOF;
		}

		if(!empty($this->ci->config->config['site_default_meta_title']) && (!isset($this->meta['title']) || sizeof($this->meta['title'])<1)){
			if(!isset($this->meta['title']))$this->meta['title']=array();
			$this->meta['title'][]=$this->ci->config->config['site_default_meta_title'];
		}

		if(!empty($this->ci->config->config['site_default_meta_keywords']) && (!isset($this->meta['keywords']) || sizeof($this->meta['keywords'])<1)){
			if(!isset($this->meta['keywords']))$this->meta['keywords']=array();
			$this->meta['keywords'][]=$this->ci->config->config['site_default_meta_keywords'];
		}

		if(!empty($this->ci->config->config['site_default_meta_description']) && (!isset($this->meta['description']) || sizeof($this->meta['description'])<1)){
			if(!isset($this->meta['description']))$this->meta['description']=array();
			$this->meta['description'][]=$this->ci->config->config['site_default_meta_description'];
		}

		foreach($this->meta AS $type=>$data)
		{
			$data=trim(implode(" ",$data));
			
			$name="name";
			if(preg_match("#^og:#is",$type)){
				$name="property";
				$data=og_tag_content($data);
			}

			$data=str_replace("\"","\\\"",$data);

			switch($type)
			{
				case'keywords':
					$head.=<<<EOF
\t<meta {$name}="keywords" content="{$data}" />\n
EOF;
				break;
				case'description':
					$head.=<<<EOF
\t<meta {$name}="description" content="{$data}" />\n
EOF;
				break;
				case'title':
					$head.=<<<EOF
\t<meta {$name}="title" content="{$data}" />\n
EOF;
				break;
				default:
					$head.=<<<EOF
\t<meta {$name}="{$type}" content="{$data}" />\n
EOF;
				break;
			}
		}

		return $head;
	}

	public function meta($text,$type,$pos="append")
	{
		if(empty($text))return false;

		if(!isset($this->meta[$type])){
			$this->meta[$type]=array();
		}

		if($pos=="prepend"){
			array_unshift($this->meta[$type],$text);
		}else{
			$this->meta[$type][]=$text;
		}
	}

	public function title($text,$pos="append")
	{
		if(empty($text))return false;

		if($pos=="prepend"){
			array_unshift($this->title,$text);
		}else{
			$this->title[]=$text;
		}
	}

	function printTitle()
	{
		$title=array();
		foreach($this->title AS $r)
		{
			$title[]=$r;
		}

		if(!empty($this->ci->config->config['site_default_title'])){
			$title[]=$this->ci->config->config['site_default_title'];
		}

		return implode(" - ",$title);
	}
        
        public function mainCat() {
            $return = '';
            
            // проверка прав доступа виджета
            $page_id = $this->url_structure_res->id;
            $maincat = $this->ci->db->select('id')->get_where('widgets', array('enabled' => 1, 'position' => 'mainCat'), 1)->row_array(); // виджет разрешен?
            if(!empty($maincat['id'])){
                $maincat_access = $this->ci->db->select('component_id')
                        ->where(array('component_type' => 'widget', 'component_id' => $maincat['id']))
                        ->where_in('extra_val', array($page_id, 'all'))
                        ->get('components_view_rules', 1)
                        ->row_array(); // виджет доступен на данной странице?
                if(empty($maincat_access['component_id'])){
                    return $return;
                }
            }
            else{
                return $return;
            }
            
            $cats = array(1641, 1618, 1617); // основные категории: В НАЛИЧИИ, РАСПРОДАЖА, НОВИНКИ
            $wm_categories = array(
                1641 => array('id' => '1641', 'title' => 'В НАЛИЧИИ', 'class' => 'wm-stock'),
                1618 => array('id' => '1618', 'title' => 'РАСПРОДАЖА', 'class' => 'wm-sale'),
                1617 => array('id' => '1617', 'title' => 'НОВИНКИ', 'class' => 'wm-new'),
            ); // для наложения значков на изображения товаров
            $thumbs_path = '/uploads/shop/products/thumbs/';
            $placeholder = 'http://lorempixel.com/image_output/fashion-q-c-336-336-9.jpg';
//            $placeholder = 'http://lorempixel.com/image_output/abstract-q-c-336-336-8.jpg';
            
            $goods = $good_ids = $cats_info = array();
            $good_ids[] = 0; // чтоб избежать повтора товаров, если они присутствуют в нескольких основных категориях
            foreach ($cats as $cat){
                // хак для категории НОВИНКИ - чтоб не было повторов фоток на Главной
                //$limit = ($cat !== 1617) ? 1 : 4;
                $limit = 4; // для всех категорий
                $cat_goods = $this->ci->db->select('shop_products_categories_link.product_id')
                        ->join('shop_products', 'shop_products.id = shop_products_categories_link.product_id')
                        ->where_not_in('shop_products_categories_link.product_id', $good_ids)
                        ->where(array('shop_products_categories_link.category_id' => $cat, 'shop_products.show' => 1, 'shop_products.frontpage' => 1))
                        ->order_by('shop_products_categories_link.product_id', 'desc')
                        ->get('shop_products_categories_link', $limit)
                        ->result_array();
                //$good_ids[$cat] = ($cat !== 1617) ? $cat_goods[0]['product_id'] : $cat_goods[3]['product_id'];
                $good_ids[$cat] = $cat_goods[3]['product_id'];
                $cats_info[$cat] = $this->ci->db->select('url_structure.url, url_structure.title')
                        ->get_where('url_structure', array('extra_id' => $cat), 1)
                        ->row_array();
            }
            unset($good_ids[0]);
            if(!empty($good_ids)){
                $return .= '<div class="mainCat">';
                $ids = array_values($good_ids);
                // получаем картинки товаров
                $images = $this->ci->db->select('uploads.file_name, uploads.extra_id')
                        ->where_in('uploads.extra_id', $ids)
                        ->where('order', 1)
                        ->get('uploads')
                        ->result();
                // приводим картинки к виду product_id => img_path
                if(!empty($images)){
                    foreach ($images as $key => $img){
                        $images[$img->extra_id] = $thumbs_path . $img->file_name;
                        unset($images[$key]);
                    }
                }
                
                foreach ($good_ids as $cat_id => $good){
                    if(!empty($images[$good])){
                        // выводим блок с картинкой товара
                        $return .= '<div class="productRow">
                        <a href="' . base_url($cats_info[$cat_id]['url']) . '" title="' . $wm_categories[$cat_id]['title'] . '">
                            <div class="productImgWrap"><!-- для размещения ярлыка на фото товара -->
                                <span class="th"><img src="' . $images[$good] . '" alt="' . $cats_info[$cat_id]['title'] . '" /></span>
                                <div class="wm-stiker ' . $wm_categories[$cat_id]['class'] . '"></div>
                                <div class="semilayer"><span>Подробнее...</span></div>
                            </div><!-- /для размещения ярлыка на фото товара -->
                        </a>
                        </div>'; 
                    }
                    else{
                        // выводим блок с картинкой-заглушкой
                        $return .= '<div class="productRow">
                        <a href="' . base_url($cats_info[$cat_id]['url']) . '" title="' . $wm_categories[$cat_id]['title'] . '">
                            <div class="productImgWrap"><!-- для размещения ярлыка на фото товара -->
                                <span class="th"><img src="' . $placeholder . '" width="336" alt="' . $cats_info[$cat_id]['title'] . '" /></span>
                                <div class="wm-stiker ' . $wm_categories[$cat_id]['class'] . '"></div>
                                <div class="semilayer"><span>Подробнее...</span></div>
                            </div><!-- /для размещения ярлыка на фото товара -->
                        </a>
                        </div>'; 
                    }
                }
                $return .= '<div class="clear"></div></div>';
            }
            
            return $return;
        }
        
        public function check_page_access_new($rule_name,$component_name,$component_type)
	{
		if(!$this->access($rule_name,$component_name,$component_type)){
                    $template = "structure";
                    $view = "access_denied";
                    $this->_ci_view_paths["templates/default/admin/"]=true;
                    $this->ci->template_data['content']=parent::view($view,'',true);
                    parent::view($template,$this->ci->template_data);
                    return FALSE;
		}
                else{
                    return TRUE;
                }
	}
}