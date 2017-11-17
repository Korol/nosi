<?php

/**
 * Class slick_sliderWidget
 * отображение виджета на страницах сайта
 * в методе view_widget формируется HTML-код отображения виджета
 * доступ к функционалу CI через $this->ci->
 */
class slick_sliderWidget extends Cms_modules {
    public $num_items = 20; // не меньше 12-ти товаров

	public function __construct()
	{
		parent::__construct();
	}

    /**
     * @param $r
     * @return mixed
     */
	public function view_widget(&$r)
	{
        $data = array();
        // получаем товары для виджета
        $data['products'] = $this->get_products($r->id);
        $html = $this->load->view('newdesign/slick_slider', $data, true);

		return $html;
	}

    /**
     * @param $widget_id
     * @return array
     */
    public function get_products($widget_id)
    {
        $return = array();
        $select = 'slick_sliders.product_id, uploads.file_name, ';
        $select .= 'shop_products.title, shop_products.category_ids, shop_products.name';
        $result = $this->ci->db->select($select)
            ->join('uploads', 'uploads.extra_id = slick_sliders.product_id', 'left')
            ->join('shop_products', 'shop_products.id = slick_sliders.product_id', 'left')
            ->where(array(
                'uploads.order' => 1,
                'slick_sliders.widget_id' => $widget_id
            ))
            ->order_by('slick_sliders.order asc')
            ->get('slick_sliders')->result_array();
        if(!empty($result)){
            $products_ids = array(); // ID добавленых товаров, чтоб не повторялись
            foreach($result as $p_key => $p_res){
                // URL товара
                $return[$p_key] = $p_res;
                $return[$p_key]['url'] = $this->build_product_url($p_res['category_ids'], $p_res['name'], $p_res['product_id']);
                $products_ids[] = $p_res['product_id'];
            }
            // считаем количество продуктов и сверяем его с шаблоном ($this->num_items)
            $count_p = count(array_keys($return));
            if($count_p < $this->num_items){
                // добиваем пустые позиции товаров согласно схеме в $this->products_positions
                $num = $this->num_items - $count_p;
                $viewed = $this->get_viewed_products($products_ids, $num); // самые просматриваемые товары за 7 дней
                $return = array_merge($return, $viewed);
                $count_pv = count($return);
                if($count_pv < $this->num_items){
                    foreach($viewed as $item){
                        $products_ids[] = $item['product_id'];
                    }
                    $num = $this->num_items - $count_pv;
                    $lasts = $this->get_last_products($products_ids, $num); // последние добавленные на сайт товары
                    $return = array_merge($return, $lasts);
                }
            }
        }
        return $return;
    }

    /**
     * @param array $exiting_ids
     * @param int $limit
     * @return array
     */
    public function get_viewed_products($exiting_ids = array(), $limit = 10)
    {
        $return = array();
        if(sizeof($exiting_ids) > 0){
            $this->ci->db->where_not_in('shop_products.id', $exiting_ids);
        }
        $select = 'stats_products.item_id, COUNT(stats_products.id) AS visit_cnt, ';
        $select .= 'shop_products.id, shop_products.title, ';
        $select .= 'shop_products.name, uploads.file_name, shop_products.category_ids';
        $this->ci->db->distinct();
        $this->ci->db->select($select);
        $this->ci->db->join('shop_products', 'shop_products.id = stats_products.item_id', 'left');
        $this->ci->db->join('uploads', 'uploads.extra_id = stats_products.item_id', 'left');
        $this->ci->db->where('stats_products.visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)', null, false);
        $this->ci->db->where(array(
            'uploads.name' => 'product-photo',
            'uploads.order' => 1,
            'shop_products.show' => 1,
        ));
        $this->ci->db->group_by('stats_products.item_id');
        $this->ci->db->order_by(' visit_cnt desc, stats_products.item_id desc');
        $this->ci->db->limit($limit);
        $results = $this->ci->db->get('stats_products')->result_array();
        if(!empty($results)){
            foreach($results as $result) {
                $return[] = array(
                    'product_id' => $result['id'],
                    'file_name' => $result['file_name'],
                    'title' => $result['title'],
                    'name' => $result['name'],
                    'category_ids' => $result['category_ids'],
                    'url' => $this->build_product_url($result['category_ids'], $result['name'], $result['id'])
                );
            }
        }
        return $return;
    }

    /**
     * @param array $last_products
     * @param int $limit
     * @return array
     */
    public function get_last_products($last_products = array(), $limit = 10)
    {
        $return = array();
        if(sizeof($last_products) > 0){
            $this->ci->db->where_not_in('shop_products.id', $last_products);
        }
        $select = 'shop_products.id, shop_products.title, ';
        $select .= 'shop_products.name, uploads.file_name, shop_products.category_ids';
        $this->ci->db->select($select);
        $this->ci->db->join('uploads', 'uploads.extra_id = shop_products.id');
        $this->ci->db->where(array(
            'uploads.order' => 1
        ));
        $this->ci->db->order_by('shop_products.id desc');
        $this->ci->db->limit($limit);
        $results = $this->ci->db->get('shop_products')->result_array();
        if(!empty($results)){
            foreach($results as $result) {
                $return[] = array(
                    'product_id' => $result['id'],
                    'file_name' => $result['file_name'],
                    'title' => $result['title'],
                    'name' => $result['name'],
                    'category_ids' => $result['category_ids'],
                    'url' => $this->build_product_url($result['category_ids'], $result['name'], $result['id'])
                );
            }
        }
        return $return;
    }

    /**
     * @param array $cat_ids
     * @param string $name
     * @param int $id
     * @return bool|string
     */
    public function build_product_url($cat_ids = array(), $name = '', $id = 0)
    {
        if(empty($cat_ids)) return '';
        // товар может быть привязан сразу к нескольким категориям, выбираем самую последнию, по ней будет построена основная ссылка
        $last_category_id = end(explode(",", $cat_ids));

        $cat_res=$this->ci->db->select("url_structure.url")
            ->get_where("url_structure",array(
                "url_structure.extra_name"=>"category_id",
                "url_structure.extra_id"=>$last_category_id
            ))
            ->row();
        if(!empty($cat_res->url)){
            return rtrim($cat_res->url,"/")."/".$name."-".$id.".html";
        }
        return false;
    }
}
?>