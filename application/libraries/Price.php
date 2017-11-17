<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
// 2016-02-08
class Price {
    
    public $type = 'hotline';
    public $table_price = 'price_hotline';
    public $table_categories = 'categoryes';
    public $price_params = array(
        'hotline' => array(
            'price_title' => '',
            'in_price' => 0, // checkbox with value 1
            'age' => '',
            'gender' => '',
            'original' => '',
            'season' => '',
            'type' => '',
        ),
    );
    public $price_fields = array(
        'hotline' => array(
            'price_title' => 'Категория Hotline',
            'in_price' => 'В прайсе',
            'age' => 'Возраст',
            'gender' => 'Пол',
            'original' => 'Оригинальность',
            'season' => 'Сезон',
            'type' => 'Тип',
        ),
    );
    public $price_options = array(
        'hotline' => array(
            'age' => array(
                '',
                'Взрослый',
                'Детский',
                'Для малышей',
            ),
            'gender' => array(
                '',
                'Мужской',
                'Женский',
                'Унисекс',
            ),
            'original' => array(
                '',
                'Реплика',
                'Оригинал',
            ),
            'season' => array(
                '',
                'Зима',
                'Весна',
                'Лето',
                'Осень',
                'Осень-Весна',
                'Весна-Лето',
                'Лето-Осень',
                'Осень-Зима',
                'Демисезонный',
            ),
        ),
    );
    public $ci;

    public function __construct($params = array())
    {
        $this->ci =& get_instance();
//        $this->ci->load->library('user_agent');
        // set params
        if(!empty($params)){
            $this->type = (!empty($params['type'])) ? $params['type'] : 'hotline';
            $this->table_price = 'price_' . $this->type; // price_hotline
        }
    }
    
    public function get_categories() 
    {
        $categories = $this->get_all_categories();
        $price_categories = $this->get_price_categories();
        if(!empty($categories)){
            foreach ($categories as $c_key => $category) {
                if(!empty($price_categories[$category['id']])){
                    $categories[$c_key]['price_properties'] = $price_categories[$category['id']];
                }
                else{
                    $categories[$c_key]['price_properties'] = $this->price_params[$this->type];
                }
            }
        }
        return $categories;
    }
    
    public function get_all_categories() 
    {
        return $this->ci->db->select('id, parent_id, title')
                ->where(array('type' => 'shop-category', 'parent_id >' => 0))
                ->get($this->table_categories)->result_array();
    }
    
    public function get_price_categories() 
    {
        $result = $this->ci->db->get($this->table_price)->result_array();
        return (!empty($result)) ? array_by_index($result, 'category_id') : array();
    }
    
    public function get_price_fields() 
    {
        return $this->price_fields[$this->type];
    }
    
    public function get_price_params() 
    {
        return $this->price_params[$this->type];
    }
    
    public function get_price_options() 
    {
        return $this->price_options[$this->type];
    }
    
    public function update_categories_info($data)
    {
        $this->ci->db->truncate($this->table_price);
        $res = $this->ci->db->insert_batch($this->table_price, $data);
        $affected = $this->ci->db->affected_rows();
        if($affected == 0){
            $this->ci->session->set_userdata('sql_error', 'Library: Price; Method: update_categories_info; SQL: ' . $this->ci->db->last_query() . '; Error: ' . $this->ci->db->_error_message());
        }
        return $affected;
    }
    
    public function get_in_price_categories()
    {
        $result = $this->ci->db->get_where($this->table_price, array('price_title !=' => '', 'in_price' => 1))->result_array();
        return (!empty($result)) ? array_by_index($result, 'category_id') : array();
    }
    
    public function get_price_products($category_ids)
    {
        $return = array();
        
        $options = $this->get_products_options();
        $brands = $this->get_products_brands();
        $products = $this->get_products_by_categories($category_ids);
        $product_images = array();
        if(!empty($products)){
            
            foreach ($products as $cat_id => $cat_products){
                $product_ids = array();
                
                foreach ($cat_products as $k => $prod){
                    // основная информация о товарах
                    $return[$cat_id][$k] = array(
                        'id' => $prod['id'],
                        'title' => $prod['title'],
                        'name' => $prod['name'],
                        'code' => $prod['code'],
                        'price' => $prod['price'],
                        'price_old' => $prod['price_old'],
                        'discount' => $prod['discount'],
                        'currency' => $prod['currency'],
                        'sizes' => $prod['sizes'],
                        'brand_name' => $brands[$prod['brand_id']]['title'],
                    );
                    
                    foreach ($options[$prod['type_id']] as $ok => $ov){
                        // параметры товаров
                        $return[$cat_id][$k]['options'][$ok] = array(
                            'title' => $ov['title'],
                            'value' => $ov['params']['options'][$prod['f_' . $ov['id']]]
                        );
                    }
                    $product_ids[] = $prod['id'];
                }
                $product_images[$cat_id] = $this->get_products_images($product_ids); // картинки товаров
                unset($products[$cat_id]); // чистим память вроде как
            }
            
            if(!empty($return)){
                foreach ($return as $rcat_id => $rcat_products){
                    foreach ($rcat_products as $rc_key => $rc_val){
                        $return[$rcat_id][$rc_key]['image'] = $product_images[$rcat_id][$rc_val['id']]['file_path'] . $product_images[$rcat_id][$rc_val['id']]['file_name'];
                    }
                }
            }
        }
//        var_dump($return);
        return $return;
    }
    
    public function get_products_by_categories($category_ids)
    {
        $result = $this->ci->db->select('shop_products.*, shop_products_categories_link.*')
                ->join('shop_products_categories_link', 'shop_products_categories_link.product_id = shop_products.id')
                ->where_in('shop_products_categories_link.category_id', $category_ids)
                ->get('shop_products')->result_array();
        //var_dump(count($result), $this->ci->db->last_query());
        return (!empty($result)) ? array_group_by_index($result, 'category_id') : array();
    }
    
    public function get_products_options()
    {
        $result = $this->ci->db->select('id, type_id, title, params')->where(array('show' => 1))->get('shop_product_type_fields')->result_array();
        if(!empty($result)){
            foreach ($result as $row_k => $row){
                $result[$row_k]['params'] = json_decode($row['params'], TRUE);
            }
        }
        return (!empty($result)) ? array_group_by_index($result, 'type_id') : array();
    }
    
    public function get_products_images($product_ids)
    {
        $result = $this->ci->db->select('file_name, file_path, extra_id')
                ->where_in('extra_id', $product_ids)
                ->where(array('name' => 'product-photo', 'order' => 1))
                ->get('uploads')->result_array();
        return (!empty($result)) ? array_by_index($result, 'extra_id') : array();
    }
    
    public function get_products_brands()
    {
        $result = $this->ci->db->select('id, title')->get_where('categoryes', array('type' => 'shop-manufacturer'))->result_array();
        return (!empty($result)) ? array_by_index($result, 'id') : array();
    }
}

/* End of file Price.php */