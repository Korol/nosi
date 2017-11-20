<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class MY_Controller
 * @property Base_model $base_model Base Model Class
 */
class MY_Controller extends CI_Controller
{
    public $view_path = 'newdesign/';
    public $view_container;
    public $data = array();
    public $http_host;
    public $footer_menu = array();
    public $footer_menu_ids = array(
        1620 => 'info',
        1626 => 'account',
        1632 => 'online',
    );
//    public $fake_uri_segment = 1; // 1 for /newdesign/, else: 0
    public $fake_uri_segment = 0; // 1 for /newdesign/, else: 0
    public $items_in_cart = '';
    public $cart_content = array();
    public $cart_total = 0;
    public $e_rates = array();
    public $site_currency = 'grn';
    public $currency_marks = array(
        'usd' => '$',
        'eur' => '&euro;',
        'grn' => 'грн',
    );
    public $top_menu = array();
    public $orig_top_menu = array();
    public $title_suffix = ' Цена, купить в Киеве, Харькове, Днепропетровске, Одессе, Запорожье, Львове. {__nc__}: обзор, описание, продажа | Интернет-магазин Носи Это';

    public $tm_active_id = 0;
    public $is_mobile = 0;
    public static
        $idKeyName = 'id', $parentIdKeyName = 'parent_id', $childrenKeyName = 'children';

    public function __construct()
    {
        parent::__construct();
        // подгружаем всё необходимое
        $this->load->helper(array('url', 'functions', 'array'));
        $this->load->model('base_model');
        $this->load->library('cart');
        $this->load->library('user_agent');
        // mobile?
        $this->is_mobile = ($this->agent->is_mobile()) ? 1 : 0;
        // валюта сайта
        $session_currency = $this->session->userdata('currency');
        $this->site_currency = (!empty($session_currency)) ? $session_currency : 'grn';
        // корзина
        $this->items_in_cart = $this->cart->total_items();
//        if(!empty($this->items_in_cart)){
//            $this->cart_content = $this->get_cart_content();
//        }

        // включаем CSRF-защиту
        $this->config->set_item('csrf_protection', true);

        // меняем base_url() под добавочный элемент /newdesign/
//        $this->set_base_url('newdesign'); // просто вызвать с пустым значением для возврата на обычные URL, без /newdesign/
        $this->set_base_url(); // просто вызвать с пустым значением для возврата на обычные URL, без /newdesign/

        // «чистый» URL сайта для доступа к локальным ресурсам (js, css, etc...) при наличии добавочного элемента
        if(!defined('HTTP_HOST')) define('HTTP_HOST', $this->http_host);

        // определяем путь к новому дизайну и контейнер с отображениями
        $this->data['view_path'] = $this->view_path;
        $this->view_container = $this->view_path . 'container';

        // формируем нижнее меню
        foreach($this->footer_menu_ids as $fmi_k => $fmi_v){
            $this->footer_menu[$fmi_v] = $this->get_simple_menu($fmi_k);
        }

        // формируем верхнее меню
        $this->top_menu = $this->get_top_menu();
    }

    /**
     * Меняем base_url() при необходимости использования добавочного элемента в URl
     * @param string $append
     */
    public function set_base_url($append = '')
    {
        $base_url = $this->http_host = $this->config->item('base_url');
        $this->config->set_item('base_url', $base_url . ((!empty($append)) ? $append . '/' : ''));
        return;
    }

    /**
     * Формируем простое одноуровневое меню (нижнее, в нашем случае)
     * @param $parent_id
     * @return array
     */
    public function get_simple_menu($parent_id)
    {
        $structure_ids = array();
        // получаем пункты меню
        $cat_res = $this->base_model->get_list('categoryes', 'id, parent_id, title, options', array('parent_id' => $parent_id, 'show' => 1), 'order asc');
        if(!empty($cat_res)) {
            // проставляем URL
            foreach ($cat_res as $k => $res) {
                $cat_res[$k]['options'] = json_decode($res['options'], true);
                if (!empty($cat_res[$k]['options']['url'])) {
                    $cat_res[$k]['url'] = $cat_res[$k]['options']['url']; // URL указан явно
                } elseif (!empty($cat_res[$k]['options']['structure_id'])) {
                    $structure_ids[] = $cat_res[$k]['options']['structure_id']; // URL забит в таблице url_structure
                }
                else{
                    $cat_res[$k]['url'] = '#';
                }
            }
        }
        if(!empty($structure_ids)){
            // получаем URL из таблицы url_structure
            $structure_res = $this->base_model->get_fields_by_ids('url_structure', 'id', $structure_ids, 'id, url');
            if(!empty($structure_res)){
                // группируем элементы по structure_id
                $structure_res = toolIndexArrayBy($structure_res, 'id');
                // проставляем URL
                foreach ($cat_res as $kr => $item) {
                    if(!empty($structure_res[$item['options']['structure_id']])){
                        $cat_res[$kr]['url'] = $structure_res[$item['options']['structure_id']]['url'];
                    }
                }
            }
        }
        return (!empty($cat_res)) ? $cat_res : array();
    }

    /**
     * получаем товары для корзины
     * @return array
     */
    public function get_cart_content()
    {
        $cart = $this->cart->contents(); // содержимое корзины
        $cart_products = toolIndexArrayBy($cart, 'id');
        $product_ids = array_keys($cart_products); // ID товаров в корзине
        // получаем инфо о товарах в корзине
        $products = $this->db
            ->select('shop_products.id, shop_products.title, shop_products.price, shop_products.currency, uploads.file_name')
            ->join('uploads', 'uploads.extra_id = shop_products.id')
            ->where_in('shop_products.id', $product_ids)
            ->where(array(
                'shop_products.show' => 1,
                'uploads.name' => 'product-photo',
                'uploads.order' => 1,
            ))
            ->get('shop_products')->result_array();
        if(!empty($products)){
            // устанавливаем цену для отображения в валюте сайта
            foreach($products as $k => $v){
                if($v['currency'] != $this->site_currency){
                    $converted = $this->convert_price($v['price'], $v['currency']);
                    $products[$k]['price'] = ($converted * $cart_products[$v['id']]['qty']);
                    $products[$k]['currency'] = $this->site_currency;
                }
                // акционная цена товара
                $products[$k]['price'] = $this->set_action_price($v['id'], $products[$k]['price']);
                $this->cart_total += $products[$k]['price']; // общая стоимость корзины
            }
        }
        return (!empty($products)) ? $products : array();
    }

    /**
     * конвертируем стоимость товара в валюту сайта $this->site_currency
     * @param $sum
     * @param $currency
     * @return float
     */
    public function convert_price($sum, $currency)
    {
        if(empty($this->e_rates)){
            // новые направления конвертации для новых валют в системе добавлять здесь:
            $this->e_rates = array('grn_usd' => 1, 'eur_usd' => 1, 'usd_grn' => 1, 'eur_grn' => 1, 'usd_eur' => 1, 'grn_eur' => 1);
            // получаем курсы валют согласно направлениям конвертации
            $e_rates_db = $this->db->select('var_name, value')->get('e_rates')->result_array();
            if(!empty($e_rates_db)){
                foreach($e_rates_db as $e_rate){
                    $this->e_rates[$e_rate['var_name']] = str_replace(',', '.', $e_rate['value']);
                }
            }
        }
        return ceil($sum * $this->e_rates[$currency . '_' . $this->site_currency]);
    }

    /**
     * отображение корзины в верхнем меню
     */
    public function display_cart()
    {
        $this->items_in_cart = $this->cart->total_items();
        if(!empty($this->items_in_cart)){
            $this->cart_content = $this->get_cart_content();
        }
        $data['items_in_cart'] = $this->items_in_cart;
        $data['cart_content'] = $this->cart_content;
        $data['currency_mark'] = $this->currency_marks[$this->site_currency];
        $data['cart_total'] = $this->cart_total;
        //$this->load->vars($data);
        return $this->load->view($this->view_path . 'top_cart', $data, true);
    }

    /**
     * формируем верхнее меню
     * @return array
     */
    public function get_top_menu()
    {
        //error_reporting(1);
        $return = array();
        // получаем все разрешенные пункты меню из categoryes, кроме тех, которые относятся к нижним меню:
        // нижние меню parent_id: 0, 1620, 1626, 1632
        $all_menu_items = $this->db
            ->select('categoryes.id, categoryes.parent_id, categoryes.title, categoryes.extra_id, categoryes.order, categoryes.options, url_structure.url')
            ->join('url_structure', 'url_structure.id = categoryes.extra_id', 'left')
            ->where_not_in('categoryes.parent_id', array(0, 1620, 1626, 1632))
            ->where(array(
                'categoryes.show' => 1,
            ))
            ->order_by('categoryes.parent_id, categoryes.order')
            ->get('categoryes')->result_array();
        if(!empty($all_menu_items)){
            // строим меню
            $menu = self::build($all_menu_items);
            // фильтруем меню от «лишних» пунктов – временная мера, т.к. по дизайну в верхний уровень идут не более 3-4 пунктов максимум
            $menu = $this->filter_menu($menu);
            $this->orig_top_menu = $menu;
            // разделяем меню по уровням – у нас по дизайну максимум 3 уровня
            foreach($menu as $level1){
                // уровень 1
                $return[1][$level1['id']] = array(
                    'id' => $level1['id'],
                    'title' => $level1['title'],
                    'url' => $level1['url'],
                    'order' => $level1['order'],
                );
                // уровень 2
                if(!empty($level1['children'])){
                    foreach($level1['children'] as $level2){
                        // для элементов меню, которым просто задали некоторые URl, без привязки к структуре сайта
                        $l2_options = (!empty($level2['options'])) ? json_decode($level2['options'], true) : array();
                        $l2_options_url = (!empty($l2_options['url'])) ? '/' . end(explode('/', $l2_options['url'])) . '/' : '';
                        $return[2][$level2['parent_id']][] = array(
                            'id' => $level2['id'],
                            'parent_id' => $level2['parent_id'],
                            'title' => $level2['title'],
                            'url' => (!empty($level2['url'])) ? $level2['url'] : $l2_options_url,
                            'order' => $level2['order'],
                        );
                        // уровень 3
                        if(!empty($level2['children'])){
                            foreach($level2['children'] as $level3){
                                $return[3][$level3['parent_id']][] = array(
                                    'id' => $level3['id'],
                                    'parent_id' => $level3['parent_id'],
                                    'title' => $level3['title'],
                                    'url' => $level3['url'],
                                    'order' => $level3['order'],
                                );
                            }
                        }
                    }
                }
            }
//            debug($return);
            return $return;
        }
        else{
            return array();
        }
    }

    /**
     * фильтруем верхний уровень меню – временная мера, т.к. по дизайну в верхний уровень идут не более 3-4 пунктов максимум
     * @param $menu
     * @return array
     */
    public function filter_menu($menu)
    {
        $parent_id = 24;
        $return = array();
        $allowed_ids = array(
            'nosieto.loc' => array(1558, 1559, 1560, 1561),
            'nosieto.com.ua' => array(1558, 1559, 1560, 1561),
        );
        $host = $_SERVER['HTTP_HOST'];
        if(!empty($menu)){
            foreach($menu as $k => $item){
                if(($item['parent_id'] == $parent_id) && in_array($item['id'], $allowed_ids[$host])){
                    $return[$k] = $item;
                }
            }
            return $return;
        }
        else{
            return $return;
        }
    }

    /**
     * служебный метод для self::build
     * @param $a
     * @param $b
     * @return int
     */
    protected static function operator($a, $b)
    {
        return ($a[self::$parentIdKeyName] === $b[self::$parentIdKeyName]) ?
            0 : (($a[self::$parentIdKeyName] < $b[self::$parentIdKeyName]) ? -1 : 1);
    }

    /**
     * строим дерево категорий по parent_id
     * @param array $a
     * @return array
     */
    public static function build(array $a)
    {
        $tree = $links = array();
        usort($a, "self::operator");
        $acmeId = $a[0][self::$parentIdKeyName];
        for ($n = count($a), $i = 0; $i < $n; $i++)
        {
            $id = $a[$i][self::$idKeyName];
            $parentId = $a[$i][self::$parentIdKeyName];
            if ($parentId === $acmeId)
            {
                $tree[$id] = $a[$i];
                $links[$id] = &$tree[$id];
            }
            else
            {
                if (!isset($links[$parentId][self::$childrenKeyName]))
                    $links[$parentId][self::$childrenKeyName] = array();
                $links[$parentId][self::$childrenKeyName][$id] = $a[$i];
                $links[$id] = &$links[$parentId][self::$childrenKeyName][$id];
            }
        }

        return $tree;
    }

    /**
     * Получаем описание для псевдо-категорий типа Дети/Новинки, Женщины/Распродажа, Мужчины/Бренды
     * @param int $category_id – ID категории
     * @return array
     */
    public function getFakeCategoryDescription($category_id)
    {
        $res = $this->db->select('title, description, meta_description')
            ->where(array(
                'id' => $category_id,
            ))
            ->limit(1)
            ->get('categoryes')->row_array();
        if(!empty($res['title']) && (mb_strpos($res['title'], 'X ', null, 'UTF-8') !== false)){
            $res['title'] = str_replace('X ', '', $res['title']);
        }
        return (!empty($res)) ? $res : array();
    }

    public function buildTitle($title)
    {
        if(!empty($title)){
            $title = $title . str_replace('{__nc__}', $title, $this->title_suffix);
        }
        return $title;
    }

    /**
     * если продукт участвует в активной акции
     * то возвращает массив с информацией о скидке,
     * назначенной продукту в данной активной акции
     * если не участвует – то false
     * @param $product_id
     * @return bool|array
     */
    public function check_product_action($product_id)
    {
        $active_action = $this->db
            ->where('active', 1)
            ->where("NOW() BETWEEN `start` AND `end`", null, false)
            ->limit(1)
            ->get('action')->row_array();
        if(!empty($active_action)){
            $check_product = $this->db
                ->where(array(
                    'action_id' => $active_action['id'],
                    'product_id' => $product_id,
                ))
                ->get('action_product')->row_array();
            if(!empty($check_product)) {
                $check_product['action_info'] = $active_action;
            }
            return $check_product;
        }
        return false;
    }

    /**
     * акционная цена товара – если она есть
     * если нет – возвращаем цену товара без изменений
     * @param $product_id
     * @param $product_price
     * @param bool $no_ceil – не округлять (важно, если потом будет выполняться конвертация!)
     * @return float
     */
    public function set_action_price($product_id, $product_price, $no_ceil = false)
    {
        if(empty($product_id) || empty($product_price)) return $product_price;

        $action = $this->check_product_action($product_id);
        if(!empty($action['percent']) && !empty($product_price)){
            // сумма скидки
            $sale = ($product_price * $action['percent']) / 100;
            // цена со скидкой
            if(empty($no_ceil))
                $product_price = ceil($product_price - $sale);
            else
                $product_price = ($product_price - $sale);
        }
        return $product_price;
    }
} 