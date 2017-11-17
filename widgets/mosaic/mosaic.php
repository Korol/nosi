<?php

/**
 * Class mosaicWidget
 * отображение виджета на страницах сайта
 * в методе view_widget формируется HTML-код отображения виджета
 * доступ к функционалу CI через $this->ci->
 */
class mosaicWidget extends Cms_modules {
    public $products_positions = array(
        1 => array('type' => 'manual'),
        2 => array('type' => 'category', 'category_id' => '1641'), // в наличии
        3 => array('type' => 'category', 'category_id' => '1618'), // SALE
        5 => array('type' => 'category', 'category_id' => '1617'), // NEW
        6 => array('type' => 'last'),
        7 => array('type' => 'manual'),
        8 => array('type' => 'last'),
        10 => array('type' => 'last'),
    );
    public $categories_positions = array(4, 9);
    public $e_rates;

	public function __construct()
	{
		parent::__construct();
        $this->e_rates = $this->getERates();
	}

    /**
     * @param $r
     * @return string
     */
	public function view_widget(&$r)
	{
		$data = array();
        // получаем товары и категории для виджета
        // товары
        $data['products'] = $this->get_products($r->id);
        // категории
        $data['categories'] = $this->get_categories($r->id);
//        $html = $this->ci->load->view('newdesign/mosaic.php', $data, true);
        $html = $this->ci->load->view('newdesign/mosaic_masonry.php', $data, true);

		return $html;
	}

    /**
     * @param $widget_id
     * @return array
     */
    public function get_products($widget_id)
    {
        $return = array();
        $select = 'mosaics.product_id, mosaics.position, mosaics.image, uploads.file_name, ';
        $select .= 'shop_products.title, shop_products.price, shop_products.price_old, ';
        $select .= 'shop_products.currency, shop_products.category_ids, shop_products.name';
        $result = $this->ci->db->select($select)
            ->join('uploads', 'uploads.extra_id = mosaics.product_id', 'left')
            ->join('shop_products', 'shop_products.id = mosaics.product_id', 'left')
            ->where(array(
                'uploads.order' => 1,
                'mosaics.widget_id' => $widget_id
            ))
            ->get('mosaics')->result_array();
        if(!empty($result)){
            foreach($result as $p_res){
                $p_res['price'] = $this->convertPrice($p_res['price'], $p_res['currency']);
                $p_res['price_old'] = $this->convertPrice($p_res['price_old'], $p_res['currency']);
                $return[$p_res['position']] = $p_res;
                // URL товара
                $return[$p_res['position']]['url'] = $this->build_product_url($p_res['category_ids'], $p_res['name'], $p_res['product_id']);
            }
            // считаем количество продуктов и сверяем его с шаблоном ($this->products_positions)
            $count_p = count(array_keys($return));
            $last_products = array(); // ID последних добавленых товаров, чтоб не повторялись
            $cats_products = array(); // ID последних добавленых товаров, чтоб избежать повтора товаров, если они присутствуют в нескольких основных категориях
            if($count_p < count(array_keys($this->products_positions))){
                // добиваем пустые позиции товаров согласно схеме в $this->products_positions
                foreach($this->products_positions as $key => $info){
                    if(empty($return[$key])){
                        if($info['type'] == 'last'){
                            $return[$key] = $this->get_last_product($last_products);
                            $last_products[] = $return[$key]['product_id'];
                        }
                        elseif($info['type'] == 'category'){
                            $return[$key] = $this->get_category_product($info['category_id'], $cats_products);
                            $cats_products[] = $return[$key]['product_id'];
                        }
                    }
                }
            }
        }
        return $return;
    }

    /**
     * @param int $category_id
     * @param array $p_ids
     * @return array
     */
    public function get_category_product($category_id = 0, $p_ids = array())
    {
        $return = array();
        $limit = 4; // для всех категорий
        if(empty($category_id)) return $return;
        // получаем ID $limit-х последних добавленных товаров в категории
        if(!empty($p_ids)) $this->ci->db->where_not_in('shop_products_categories_link.product_id', $p_ids);
        $cat_products = $this->ci->db->select('shop_products_categories_link.product_id')
            ->join('shop_products', 'shop_products.id = shop_products_categories_link.product_id')
            ->where(array('shop_products_categories_link.category_id' => $category_id, 'shop_products.show' => 1, 'shop_products.frontpage' => 1))
            ->order_by('shop_products_categories_link.product_id', 'desc')
            ->get('shop_products_categories_link', $limit)// ;var_dump($this->ci->db->last_query());
            ->result_array();
        $product_id = $cat_products[($limit - 1)]['product_id']; // нужный нам $limit-ый продукт в категории
//var_dump($product_id);return;
        // получаем товар по полученному ID
        if(!empty($product_id)){
            $select = 'shop_products.id, shop_products.title, shop_products.price, shop_products.price_old, ';
            $select .= 'shop_products.currency, shop_products.name, uploads.file_name, shop_products.category_ids';
            $this->ci->db->select($select);
            $this->ci->db->join('uploads', 'uploads.extra_id = shop_products.id');
            $this->ci->db->where(array(
                'uploads.order' => 1,
                'shop_products.id' => $product_id
            ));
            $this->ci->db->limit(1);
            $result = $this->ci->db->get('shop_products')->row_array();
            if(!empty($result)){
                $return = array(
                    'product_id' => $result['id'],
                    'image' => '',
                    'position' => '',
                    'file_name' => $result['file_name'],
                    'title' => $result['title'],
                    'price' => $this->convertPrice($result['price'], $result['currency']),
                    'price_old' => $this->convertPrice($result['price_old'], $result['currency']),
                    'currency' => $result['currency'],
                    'name' => $result['name'],
                    'url' => $this->build_product_url($result['category_ids'], $result['name'], $result['id'])
                );
            }
        }
        return $return;
    }

    /**
     * @param array $last_products
     * @return array
     */
    public function get_last_product($last_products = array())
    {
        $return = array();
        if(sizeof($last_products) > 0){
            $this->ci->db->where_not_in('shop_products.id', $last_products);
        }
        $select = 'shop_products.id, shop_products.title, shop_products.price, shop_products.price_old, ';
        $select .= 'shop_products.currency, shop_products.name, uploads.file_name, shop_products.category_ids';
        $this->ci->db->select($select);
        $this->ci->db->join('uploads', 'uploads.extra_id = shop_products.id');
        $this->ci->db->where(array(
            'uploads.order' => 1
        ));
        $this->ci->db->order_by('shop_products.id desc');
        $this->ci->db->limit(1);
        $result = $this->ci->db->get('shop_products')->row_array();
        if(!empty($result)){
            $return = array(
                'product_id' => $result['id'],
                'image' => '',
                'position' => '',
                'file_name' => $result['file_name'],
                'title' => $result['title'],
                'price' => $this->convertPrice($result['price'], $result['currency']),
                'price_old' => $this->convertPrice($result['price_old'], $result['currency']),
                'currency' => $result['currency'],
                'name' => $result['name'],
                'url' => $this->build_product_url($result['category_ids'], $result['name'], $result['id'])
            );
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

    /**
     * @param $widget_id
     * @return array
     */
    public function get_categories($widget_id)
    {
        $return = array();
        $result = $this->ci->db->select('mosaics.category_id, mosaics.position, url_structure.title, url_structure.url')
            ->join('url_structure', 'url_structure.extra_id = mosaics.category_id')
            ->where(array('mosaics.widget_id' => $widget_id))
            ->get('mosaics')->result_array();
        if(!empty($result)){
            foreach($result as $c_res){
                $return[$c_res['position']] = $c_res;
            }
        }
        return $return;
    }

    /**
     * @return array
     */
    public function getERates() {
        $e_rates_obj = $this->ci->db
            ->get_where("e_rates")
            ->result();
        $e_rates = array();
        foreach ($e_rates_obj as $obj) {
            $e_rates[$obj->var_name] = $obj->value;
        }
        return $e_rates;
    }

    /**
     * @param $price
     * @param $product_currency
     * @return float
     */
    public function convertPrice($price, $product_currency) {
        if(empty($price)) return $price;
        $currentCurrency = $this->ci->session->userdata('currentCurrency');
        if(empty($currentCurrency)) $currentCurrency = 'grn';

        $e_rates = $this->e_rates;
        if (isset($e_rates[$product_currency . '_' . $currentCurrency]) && $e_rates[$product_currency . '_' . $currentCurrency] > 0) {
            $price = ceil($price * $e_rates[$product_currency . '_' . $currentCurrency]);
        }
        //var_dump($price);
        return $price;
    }
}
?>