<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Pages
 * @property Base_model $base_model    Base Model Class
 * CI_DB_active_record|CI_DB_mysql_driver $db
 *
 */
class Pages extends MY_Controller
{
    public $currency_marks = array(
        'usd' => '$',
        'eur' => '&euro;',
        'grn' => 'грн',
    );

    public function __construct()
    {
        parent::__construct();
        // debug
        if($this->input->ip_address() == '127.0.0.1'){
            $this->output->enable_profiler(true);
            error_reporting(E_ALL);
        }
    }

    /**
     * @return mixed
     */
    public function getMainPage()
    {
        $this->data['page'] = $this->base_model->get_one_row('pages', array('name' => '-'));
        $this->data['main_slider'] = $this->base_model->get_list('uploads', 'file_name, file_path, options', array('name' => 'slide'), 'order ASC');
        $this->data['main_seo_text'] = $this->base_model->get_one_value('pages', 'content', array('id' => 21));
        $this->data['view'] = 'main';
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    /**
     * @return array
     */
    public function getMainSlider()
    {
        return $this->base_model->get_list('uploads', '', array('name' => 'slide'), 'order ASC');
    }

    public function getBrandsListPage()
    {
        echo 'Brands List page';
    }

    public function getBrandPage($url)
    {
        echo 'Brand page: ' . $url;
    }

    public function getCategoryOrStatic($url)
    {
//        $url = str_replace('.html', '', $url);
//        $page = $this->db->get_where('url_structure', array('name' => $url), 1)->row();
        if(strpos($url, '.html') !== false){
            $this->db->where('url', '/' . $url);
        }
        else{
            $this->db->where('url', '/' . $url . '/');
        }
        $page = $this->db->get('url_structure')->row();
        if(empty($page)) show_404();

        if(($page->module == 'shop') && ($page->action == 'category')){
            $this->getCategoryPage($url);
        }
        elseif($page->type == 'static_page'){
            $this->getStaticPage($url);
        }
        elseif($page->type == 'module_action-one'){
            $this->getActionPage($url);
        }
    }

    public function getCategoryPage($url)
    {
        $this->data['category_info'] = $this->getCategoryInfo($url);
        $this->data['sub_categories'] = $this->getSubCategories($this->data['category_info']['id']);
        $this->data['category_products'] = $this->getProductsByCategory($this->data['category_info']['id'], $url);
        $this->data['filters'] = array();
        $this->data['brands'] = $this->getBrandsByCategory($this->data['category_info']['id']);
        if(!empty($this->data['category_products'][0])){
            $type_id = $this->data['category_products'][0]['type_id'];
            $this->data['filters'] = $this->getOptionsList($type_id);
//            $this->data['brands'] = $this->getBrandsByType($type_id);
        }
        else if(!empty($_GET)) {
            // товаров нет из-за фильтров – в этом случае получаем фильтры и бренды
            // по одному любому товару этой категории, без учета фильтров
            $type_id = $this->getTypeByCategory($this->data['category_info']['id']);
            $this->data['filters'] = $this->getOptionsList($type_id);
//            $this->data['brands'] = $this->getBrandsByType($type_id);
        }
        $this->data['breadcrumbs'] = $this->getCategoryBreadcrumbs($this->data['category_info']['id']);
        // определяем активный пункт верхнего меню первого уровня
        if(!empty($this->data['breadcrumbs'])){
            $url_ids = array();
            // получаем ID хлебных крошек из url_structure
            foreach($this->data['breadcrumbs'] as $bc){
                if(isset($bc['id'])){
                    $url_ids[] = $bc['id'];
                }
            }
            if(!empty($url_ids)){
                // по этим ID получаем ID пунктов меню для этих крошек
                $menu_ids_res = $this->db
                    ->select('id')
                    ->where_in('extra_id', $url_ids)
                    ->get('categoryes')->result_array();
                if(!empty($menu_ids_res)){
                    $menu_ids = array_keys(toolIndexArrayBy($menu_ids_res, 'id'));
                    $top_menu_ids = array_keys($this->top_menu[1]);
                    foreach($menu_ids as $mid){
                        if(in_array($mid, $top_menu_ids)){
                            $this->tm_active_id = $mid;
                            break;
                        }
                    }
                }
            }
        }
//        $this->data['category_childs'] = $this->getCategoryChilds($this->data['category_info']['id']);
        $this->data['category_childs'] = $this->getCategoryChilds2($this->data['category_info']['url_structure']['id']);
        $this->data['filters_checked'] = array(
            'left' => $this->input->get('left', true),
            'brand' => $this->input->get('brand', true),
            'sort' => $this->input->get('sort', true),
            'currency' => $this->input->get('currency', true),
        );
        $this->data['no_filters'] = true;
        foreach($this->data['filters_checked'] as $fc_value){
            if(!empty($fc_value)){
                $this->data['no_filters'] = false;
                break;
            }
        }

        $cat_titles = array(
            '1638' => 'Женская одежда, обувь, сумки',
            '1639' => 'Мужская одежда, обувь, сумки',
            '1640' => 'Детская одежда, обувь',
        );
        $p_title_fragment = (in_array($this->data['category_info']['id'], array_keys($cat_titles)))
            ? $cat_titles[$this->data['category_info']['id']]
            : $this->data['category_info']['title'];
        $this->data['page']['title'] = $p_title_fragment
            . ' Цена, купить в Киеве, Харькове, Днепропетровске, Одессе, Запорожье, Львове. '
            . $p_title_fragment
            . ': обзор, описание, продажа | Интернет-магазин Носи Это';
        // настройки страницы
        $this->data['page']['meta_title'] = $this->data['category_info']['title'];
        $this->data['page']['meta_description'] = $this->data['category_info']['title'];
        $this->data['view'] = (!empty($this->is_mobile)) ? 'category_mobile' : 'category';
        $this->data['pagination'] = $this->pagination->create_links();
        // валюта
        $session_currency = $this->session->userdata('currency');
        $currency = (!empty($session_currency)) ? $session_currency : 'grn';
        $this->data['products_currency'] = $this->currency_marks[$currency];
        $this->data['filters_checked']['currency'] = $currency;
        // var_dump($this->data);
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    public function getProductPage($url)
    {
        $this->load->helper('form');
        $url = str_replace('.html', '', $url);
        // информация о товаре и связанных с ним категория, бренде, изображениях и т.д.
        $this->data['product_id'] = end(explode('-', $url));
        $this->data['product'] = $this->db->get_where('shop_products', array('id' => $this->data['product_id']), 1)->row_array();
        $this->data['product_options'] = $this->getOptionsList($this->data['product']['type_id']);
        $this->data['product_categories'] = $this->getCategoriesInfo(explode(',', $this->data['product']['category_ids']));
        $this->data['product_images'] = $this->getProductImages($this->data['product_id']);
        $this->data['brand'] = $this->db->select('title')->get_where('categoryes', array('id' => $this->data['product']['brand_id']), 1)->row_array();
        $preorder_text = $this->config->item('preorder_text');
        $this->data['preorder_text'] = (!empty($preorder_text)) ? $preorder_text : '';
        // работа с валютой
        $session_currency = $this->session->userdata('currency');
        $currency = (!empty($session_currency)) ? $session_currency : 'grn';
        $this->data['product_currency'] = $this->currency_marks[$currency];
        if($this->data['product']['currency'] !== $currency){
            // получаем курсы валют согласно направлениям конвертации
            $e_rates = array('grn_usd' => 1, 'eur_usd' => 1, 'usd_grn' => 1, 'eur_grn' => 1, 'usd_eur' => 1, 'grn_eur' => 1); // новые направления конвертации для новых валют в системе добавлять здесь
            $e_rates_db = $this->db->select('var_name, value')->get('e_rates')->result_array();
            if(!empty($e_rates_db)){
                foreach($e_rates_db as $e_rate){
                    $e_rates[$e_rate['var_name']] = str_replace(',', '.', $e_rate['value']);
                }
            }
            // конвертим цены товара в валюту сайта
            $this->data['product']['price'] = ceil($this->data['product']['price'] * $e_rates[$this->data['product']['currency'] . '_' . $currency]);
            if(!empty($this->data['product']['price_old'])){
                $this->data['product']['price_old'] = ceil($this->data['product']['price_old'] * $e_rates[$this->data['product']['currency'] . '_' . $currency]);
            }
        }
        // категории и хлебные крошки
        if(!empty($this->data['product_categories'])){
            $last_category = end($this->data['product_categories']);
            $this->data['similar_products'] = $this->getSimilarProducts($last_category['id'], $last_category['url']);//var_dump($this->data['similar_products']);
            $category_title = (!empty($last_category['title'])) ? $last_category['title'] : '';
            $this->data['breadcrumbs'] = $this->getCategoryBreadcrumbs($last_category['id']);
            $this->data['breadcrumbs'][] = array(
                'title' => $this->data['product']['title'],
                'url' => base_url($last_category['url'] . $this->data['product']['name'] . '-' . $this->data['product_id'] . '.html'),
            );
        }
        else{
            $category_title = '';
            $this->data['breadcrumbs'] = array(
                0 => array(
                    'title' => 'Главная',
                    'url' => base_url(),
                ),
                1 => array(
                    'title' => $this->data['product']['title'],
                    'url' => '#',
                ),
            );
        }
        // сбор статистики
        $this->load->library('stats');
        $this->stats->set_stats($this->data['product_id']);

        // настройки страницы
        $this->data['page']['title'] = $this->data['product']['title']
            . ' Цена, купить в Киеве, Харькове, Днепропетровске, Одессе, Запорожье, Львове. '
            . $category_title
            . ': обзор, описание, продажа | Интернет-магазин Носи Это';
        $this->data['page']['meta_title'] = $this->data['product']['title'];
        $this->data['page']['meta_description'] = $this->data['product']['meta_description'];
        $this->data['page']['vk_api'] = 1;
        $this->data['view'] = 'product';
        $this->data['fb_pixel_event'] = "fbq('track', 'ViewContent');\n";
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    public function getSimilarProducts($category_id, $cat_url, $limit = 12)
    {
        $return = array();
        $result = $this->db
            ->distinct()
            ->select('shop_products.id, shop_products.title, shop_products.name, uploads.file_name')
            ->join('shop_products_categories_link', 'shop_products_categories_link.product_id = shop_products.id')
            ->join('uploads', 'uploads.extra_id = shop_products.id')
            ->where(array(
                'shop_products.show' => 1,
                'shop_products_categories_link.category_id' => $category_id,
                'uploads.name' => 'product-photo',
                'uploads.order' => 1,
            ))
            ->order_by('shop_products.id desc')
            ->limit($limit)
            ->get('shop_products')->result_array();
        if(!empty($result)){
            foreach($result as $row){
                $row['url'] = $cat_url . $row['name'] . '-' . $row['id'] . '.html';
                $return[] = $row;
            }
        }
        return  $return;
    }

    public function getProductImages($product_id){
        $return = array(
            0 => array(
                'file_name' => 'product-placeholder-2.jpg',
                'name' => 'placeholder',
            ),
        );
        $result = $this->db
            ->select('id, file_name, name, extra_color')
            ->where(array(
                'name' => 'product-photo',
                'extra_id' => $product_id,
            ))
            ->order_by('order', 'asc')
            ->get('uploads')->result_array();
        return (!empty($result)) ? $result : $return;
    }

    public function getStaticPage($url)
    {
        $url = str_replace('.html', '', $url);
        $page = $this->db
            ->where(array(
                'show' => 1,
                'name' => $url,
            ))
            ->get('pages')->row_array();
        if(empty($page)) show_404();
        $this->data['page_content'] = $page;
        // настройки страницы
        $this->data['page']['title'] = $page['title'] . ' | Интернет-магазин Носи Это';
        $this->data['page']['meta_title'] = $page['meta_title'];
        $this->data['page']['meta_description'] = $page['meta_description'];
        $this->data['view'] = 'static';
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    public function getActionPage($url)
    {

    }

    public function getOptionsList($type_id)
    {
        $return = array();
        $where = array('type_id' => $type_id, 'show' => 1);
        $options = $this->db->get_where('shop_product_type_fields', $where)->result_array();
        if(!empty($options)){
            foreach ($options as $option) {
                $params = json_decode($option['params'], true);
                $option['params'] = $params['options'];
                $return[$option['id']] = $option;
            }
        }
        return $return;
    }

    public function getCategoriesInfo($ids)
    {
//        $categories = $this->db->where_in('id', $ids)->get_where('categoryes', array('show' => 1))->result_array();
        $categories = array();
        foreach ($ids as $cat_id) {
            $categories[$cat_id] = $this->db
                ->select('categoryes.id, categoryes.parent_id, url_structure.title, url_structure.url')
                ->join('url_structure', 'url_structure.extra_id = categoryes.id')
                ->where(array(
                    'categoryes.show' => 1,
                    'categoryes.id' => $cat_id,
                    'url_structure.extra_name' => 'category_id'
                ))
                ->get('categoryes')->row_array();
        }
        return (!empty($categories)) ? $categories : array();
    }

    public function getCategoryInfo($url)
    {
        $return = array();
//        $url_structure = $this->db->get_where('url_structure', array('name' => $url), 1)->row_array();
        if(strpos($url, '.html') !== false){
            $this->db->where('url', '/' . $url);
        }
        else{
            $this->db->where('url', '/' . $url . '/');
        }
        $url_structure = $this->db->get('url_structure')->row_array();
        if(!empty($url_structure['extra_id'])){
            $return = $this->db->get_where('categoryes', array('id' => $url_structure['extra_id']), 1)->row_array();
            $return['url_structure'] = $url_structure;
        }
        return $return;
    }

    public function getSubCategories($category_id)
    {
        $return = $this->db->select('categoryes.*, url_structure.url')
            ->join('url_structure', 'url_structure.extra_id = categoryes.id')
            ->where('categoryes.parent_id', $category_id)
            ->get('categoryes')->result_array();
        return (!empty($return)) ? toolIndexArrayBy($return, 'id') : array();
    }

    public function getProductsByCategory($category_id, $base_url)
    {
        $page = (int)$this->input->get('pg');
        $per_page = 30;
        //$filters = $this->input->get('filters', true);
        $filters = array(
            'left' => $this->input->get('left', true),
            'brand' => $this->input->get('brand', true),
            'sort' => $this->input->get('sort', true),
            'currency' => $this->input->get('currency', true),
        );
        $order_dir = 'desc';
        $order_by = 'shop_products.id';
//        $order_by = 'shop_products.date_add';
//        $order_by = 'shop_products.date_public';
//        $order_by = 'shop_products.views';
        $session_currency = $this->session->userdata('currency');
        $currency = (!empty($session_currency)) ? $session_currency : 'grn';

        // сортировка по цене, независимо от валюты – сортируем по ценам, конвертированным в одну валюту usd
        $e_rates = array('grn_usd' => 1, 'eur_usd' => 1, 'usd_grn' => 1, 'eur_grn' => 1, 'usd_eur' => 1, 'grn_eur' => 1); // новые направления конвертации для новых валют в системе добавлять здесь
        // получаем курсы валют согласно направлениям конвертации - все валюты конвертим в usd
        $e_rates_db = $this->db->select('var_name, value')->get('e_rates')->result_array();
        if(!empty($e_rates_db)){
            foreach($e_rates_db as $e_rate){
                $e_rates[$e_rate['var_name']] = str_replace(',', '.', $e_rate['value']);
            }
        }

        // получаем продукты в Категории
        $this->db->start_cache();
        // добавить DISTINCT shop_products.id
        $this->db->select('shop_products.*'); // перечислить нужные поля для выборки
        $this->db->select("ROUND(shop_products.price*(IF(shop_products.currency='grn', " . $e_rates['grn_usd'] . ", IF(shop_products.currency='eur', " . $e_rates['eur_usd'] . ", 1))), 2) AS price_usd", FALSE); // новую проверку валюты и условие IF в запрос добавлять здесь
        $this->db->from('shop_products');
        $this->db->join('shop_products_categories_link', 'shop_products_categories_link.product_id = shop_products.id');
        // фильтры
        if(!empty($filters)){
            // по Производителю
            if(!empty($filters['brand'])){
                $this->db->where('shop_products.brand_id', $filters['brand']);
            }
            // по Популярности, Цене и Новинкам (по порядку добавления на сайт)
            if(!empty($filters['sort'])){
                switch ($filters['sort']){
                    case 'new':
                        $order_dir = 'desc';
                        // указать один из вариантов:
                        $order_by = 'shop_products.id'; // по порядку добавления на сайт (ID) – новинки идут первыми
//                        $order_by = 'shop_products.date_add'; // по дате добавления на сайт – новинки идут первыми
//                        $order_by = 'shop_products.date_public'; // по дате публикации – новинки идут первыми
                        break;
                    case 'popular':
                        $order_dir = 'desc';
                        $order_by = 'shop_products.views'; // по количеству просмотров - самые просматриваемые в начале
                        break;
                    case 'asc':
                        $order_dir = 'asc';
                        $order_by = 'price_usd'; // по цене – от дешевых к дорогим
                        break;
                    case 'desc':
                        $order_dir = 'desc';
                        $order_by = 'price_usd'; // по цене – от дорогих к дешевым
                        break;
                    default:
//                        $order_dir = 'desc';
//                        $order_by = 'shop_products.views';
                        $order_dir = 'desc';
                        // указать один из вариантов:
                        $order_by = 'shop_products.id';
//                        $order_by = 'shop_products.date_add';
//                        $order_by = 'shop_products.date_public';
                        break;
                }
            }
            // цены в валютах
            $currencies = array('usd', 'grn', 'eur');
            if(!empty($filters['currency']) && ($filters['currency'] !== $currency) && in_array($filters['currency'], $currencies)){
                $currency = $filters['currency'];
            }
            // фильтрация товаров по выбранным опциям боковых фильтров Категории
            if(!empty($filters['left'])){
                foreach($filters['left'] as $fk => $fv){
                    $this->db->where_in('shop_products.f_' . $fk, $fv);
                }
            }
        }
        // заменить на where_in('shop_products_categories_link.category_id', $cat_ids);
        // для выборки по текущей категории и всем её подкатегориям
        $this->db->where('shop_products_categories_link.category_id', $category_id);
        $this->db->where('shop_products.show', 1);
        $this->db->stop_cache();
        $total = $this->db->count_all_results(); // подсчитываем общее количество товаров для пагинации

        $offset = ($page > 1) ? (($page - 1) * $per_page) : 0;
        $this->db->order_by($order_by . ' ' . $order_dir);
        $this->db->limit($per_page, $offset);
        $products = $this->db->get()->result_array(); // получаем товары
        $this->db->flush_cache();

        // устанавливаем валюту в сессию
//        $this->session->set_userdata('currency', $filters['currency']);
        $this->session->set_userdata('currency', $currency);

        // get products images and set price in current currency
        // получаем картинки к товарам и конвертируем цены в текущую валюту
        if(!empty($products)){
            $ids = $last_cat_ids = array();
            foreach ($products as $kp => $product) {
                $ids[] = $product['id'];
                if($product['currency'] !== $currency){
                    $products[$kp]['price'] = ceil($product['price'] * $e_rates[$product['currency'] . '_' . $currency]);
                    if(!empty($product['price_old'])){
                        $products[$kp]['price_old'] = ceil($product['price_old'] * $e_rates[$product['currency'] . '_' . $currency]);
                    }
                }
                // получаем ID последней категории для формирования по ней URL товара
                $last_cat_ids[$product['id']] = end(explode(',', $product['category_ids']));
            }

            // получаем URL конечных категорий для формирования URL товаров
            if(!empty($last_cat_ids)) {
                $last_cat_urls = $this->db
                    ->select('extra_id, url')
                    ->where_in('extra_id', $last_cat_ids)
                    ->where(array(
                        'extra_name' => 'category_id',
                        'enabled' => 1,
                    ))
                    ->get('url_structure')->result_array();
                if (!empty($last_cat_urls)) {
                    $last_cat_urls_indexed = toolIndexArrayBy($last_cat_urls, 'extra_id');
                    $this->data['products_urls'] = array();
                    foreach($last_cat_ids as $p_key => $last_cat_id){
                        $this->data['products_urls'][$p_key] = (!empty($last_cat_urls_indexed[$last_cat_id])) ? $last_cat_urls_indexed[$last_cat_id] : '';
                    }
                }
            }

            // получаем фото товаров и добавляем их к товарам
            if(!empty($ids)){
                $uploads_where = array(
                    'name' => 'product-photo',
//                    'order' => 1
                );
                $images = $this->db->select('file_name, extra_id, order')
                    ->where($uploads_where)
                    ->where_in('extra_id', $ids)
                    ->where_in('order', array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10))
                    ->order_by('order asc')
                    ->get('uploads')->result_array();
                if(!empty($images)){
                    // вариант с одним изображением товара
//                    $images = toolIndexArrayBy($images, 'extra_id');
//                    foreach ($products as $ik => $item) {
//                        if(!empty($images[$item['id']]['file_name'])){
//                            $products[$ik]['image'] = HTTP_HOST . 'uploads/shop/products/thumbs/' .$images[$item['id']]['file_name'];
//                        }
//                        else{
//                            // image_placeholder
//                            $products[$ik]['image'] = HTTP_HOST . 'assets/newdesign/images/m4.jpg';
//                        }
//                    }
                    // вариант с двумя изображениями товара - для подмены при наведении курсора на картинку
                    $images = get_grouped_array($images, 'extra_id');
                    foreach ($products as $ik => $item) {
                        if(!empty($images[$item['id']][0]['file_name'])){
                            $products[$ik]['images'][0] = HTTP_HOST . 'uploads/shop/products/thumbs/' .$images[$item['id']][0]['file_name'];
                            $products[$ik]['images'][1] = (!empty($images[$item['id']][1]['file_name'])) ? HTTP_HOST . 'uploads/shop/products/thumbs/' .$images[$item['id']][1]['file_name'] : HTTP_HOST . 'assets/newdesign/images/m4.jpg';
                        }
                        else{
                            // заглушка – если у товара нет картинки
                            $products[$ik]['images'][0] = $products[$ik]['images'][1] = HTTP_HOST . 'assets/newdesign/images/m4.jpg';
                        }
                    }
                }
            }
        }

        // постраничная навигация
        $this->load->library('pagination');
        $this->config->load('pg', true);
        $config = $this->config->item('pg', 'pg');
        // custom pagination configuration
        // by http://stackoverflow.com/a/21191203
        $config['base_url'] = $config['first_url'] = base_url($base_url) . '/';
//        $query_string = $_GET;
        $query_string = $this->input->get(null, true);
        if (isset($query_string['pg']))
        {
            unset($query_string['pg']);
        }
        if (is_array($query_string) && count($query_string) > 0)
        {
            $config['suffix'] = '&' . http_build_query($query_string, '', "&");
            $config['first_url'] = $config['base_url'] . '?' . http_build_query($query_string, '', "&");
        }
        // /custom
        $config['per_page'] = $per_page;
        $config['total_rows'] = $total;
        $this->pagination->initialize($config);
        return $products;
    }

    public function getBrandsByType($type_id)
    {
        $brands = $this->db->distinct()
            ->select('shop_products.brand_id, categoryes.title')
            ->join('categoryes', 'categoryes.id = shop_products.brand_id')
            ->where(array('shop_products.type_id' => $type_id, 'shop_products.show' => 1))
            ->order_by('categoryes.title asc')
            ->get('shop_products')->result_array();//var_dump($this->db->last_query());
        return (!empty($brands)) ? toolIndexArrayBy($brands, 'brand_id') : array();
    }

    public function getBrandsByCategory($category_id)
    {
        $brands = $this->db->distinct()
            ->select('shop_products.brand_id, categoryes.title')
            ->join('categoryes', 'categoryes.id = shop_products.brand_id')
            ->join('shop_products_categories_link', 'shop_products_categories_link.product_id = shop_products.id')
            ->where(array(
                'shop_products_categories_link.category_id' => $category_id,
                'shop_products.show' => 1,
                'categoryes.show' => 1,
            ))
            ->order_by('categoryes.title asc')
            ->get('shop_products')->result_array();//var_dump($this->db->last_query());
        return (!empty($brands)) ? toolIndexArrayBy($brands, 'brand_id') : array();
    }

    public function getTypeByCategory($category_id){
        $this->db->select('shop_products.id, shop_products.type_id');
        $this->db->join('shop_products_categories_link', 'shop_products_categories_link.product_id = shop_products.id');
        $this->db->where(array(
            'shop_products.show' => 1,
            'shop_products_categories_link.category_id' => $category_id,
        ));
        $this->db->limit(1);
        $product = $this->db->get('shop_products')->row_array();
        return (!empty($product['type_id'])) ? $product['type_id'] : 0;
    }

    public function getCategoryBreadcrumbs($category_id){
        $categories = array();
        $this->__get_recursive_parents($category_id, $categories);
        $categories[] = array(
            'title' => 'Главная',
            'url' => base_url(),
        );
        return array_reverse($categories);
    }

    public function __get_recursive_parents($cat_id, &$output){
        $res = $this->db
            ->select('categoryes.id, categoryes.parent_id, categoryes.title, url_structure.url')
            ->select('url_structure.id as structure_id', false)
            ->from('categoryes')
            ->join('url_structure', 'url_structure.extra_id = categoryes.id')
            ->where(array(
                'categoryes.id' => $cat_id,
                'url_structure.extra_name' => 'category_id',
                'categoryes.show' => 1
            ))
            ->get()->row_array();
        if(!empty($res)){
            $output[] = array(
                'id' => $res['structure_id'],
                'title' => $res['title'],
                'url' => base_url($res['url']),
            );
        }
        if (isset($res['parent_id'])) {
            $this->__get_recursive_parents($res['parent_id'], $output);
        }
    }

    /**
     * получаем «подкатегории» согласно структуре Каталога – что не верно, если структура Каталога отличается от структуры Меню сайта
     * @param $category_id
     * @return mixed
     */
    public function getCategoryChilds($category_id){
        $res = $this->db
            ->distinct()
            ->select('`categoryes`.`id`, `categoryes`.`parent_id`, `categoryes`.`title`, `url_structure`.`url`, (SELECT COUNT(*) FROM `shop_products_categories_link` WHERE `category_id` = `categoryes`.`id`) AS `products_count`', false)
            ->from('categoryes')
            ->join('url_structure', 'url_structure.extra_id = categoryes.id')
            ->join('shop_products_categories_link', 'shop_products_categories_link.category_id = categoryes.id')
            ->where(array(
                'categoryes.parent_id' => $category_id,
                'url_structure.extra_name' => 'category_id',
                'categoryes.show' => 1
            ))
            ->order_by('categoryes.title', 'asc')
            ->having('products_count > 0')
            ->get()->result_array();
        return $res;
    }

    /**
     * получаем «подкатегории» согласно структуре Меню - более правильный подход
     * @param $category_url_id
     * @return array
     */
    public function getCategoryChilds2($category_url_id){
        $category_menu_id = $this->db
            ->select('id')
            ->where('extra_id', $category_url_id)
            ->limit(1)
            ->get('categoryes')->row()->id;
        $res = $this->db
            ->select('categoryes.title, categoryes.options, url_structure.url')
            ->join('url_structure', 'url_structure.id = categoryes.extra_id', 'left')
            ->where(array(
                'categoryes.show' => 1,
                'categoryes.parent_id' => $category_menu_id,
            ))
            ->order_by('categoryes.order asc')
            ->get('categoryes')->result_array();
        if(!empty($res)){
            foreach($res as $k => $r){
                if(empty($r['url']) && !empty($r['options'])){
                    $options = json_decode($r['options'], true);
                    $res[$k]['url'] = (!empty($options['url'])) ? '/' . end(explode('/', $options['url'])) . '/' : '';
                }
            }
        }
        return (!empty($res)) ? $res : array();
    }

    public function contacts()
    {
        $this->load->helper('form');
        $this->data['breadcrumbs'] = array(
            0 => array(
                'title' => 'Главная',
                'url' => base_url(),
            ),
            1 => array(
                'title' => 'Контакты',
                'url' => base_url('contacts'),
            ),
        );
        $this->data['page_header'] = 'Контакты';
        // настройки страницы
        $this->data['page']['title'] = 'Контакты | Интернет-магазин Носи Это';
        $this->data['page']['meta_title'] = 'Контакты';
        $this->data['page']['meta_description'] = 'Контакты, Интернет-магазин Носи Это';
        $this->data['view'] = 'contacts';
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    public function contact_form()
    {
        $this->load->helper('email');
        $post = $this->input->post(null, true);
        if(empty($post['from'])){
            if(!empty($post['c_message']) && valid_email($post['c_email'])){
                $message = '<b>От:</b> ' . (!empty($post['c_fio']) ? strip_tags($post['c_fio']) : '') . '<br/>';
                $message .= '<b>Email:</b> ' . $post['c_email'] . '<br/>';
                $message .= '<b>Дата:</b> ' . date('d-m-Y H:i') . '<br/>';
                $message .= '<b>Тема:</b> ' . strip_tags($post['c_subject']) . '<br/>';
                $message .= '<b>Сообщение:</b><br/>' . strip_tags($post['c_message']) . '<br/>';
                $to_address = 'sale@nosieto.com.ua';
                $this->load->library("email");
                $this->email->from($this->config->config['email_from'], $this->config->config['email_from_name']);
                $this->email->to($to_address);
                $this->email->subject('Новое сообщение с сайта nosieto.com.ua');
                $this->email->message($message);
                if($this->email->send()){
                    $this->session->set_flashdata('cf_success', 'Ваше сообщение успешно отправлено!<br/>Мы постараемся ответить Вам в кратчайшие сроки!<br/>Благодарим за внимание, успехов!');
                }
                else{
                    $this->session->set_flashdata('cf_error', 'Ваше сообщение НЕ отправлено!<br/>Попробуйте отправить ваше сообщение на адрес <a href="mailto:sale@nosieto.com.ua">sale@nosieto.com.ua</a> используя ваш браузер или почтовый клиент.');
                }
            }
            else{
                $this->session->set_flashdata('cf_error', 'Ваше сообщение НЕ отправлено!<br/>Возможные причины: пустое поле «Сообщение» или невалидный Email.<br/>Проверьте правильность заполнения полей формы ниже – и повторите попытку!');
            }
        }
        else{
            die('Hm, cool hacker? Ha-ha!!! Bye!');
        }

        redirect(base_url('contacts'));
    }

    public function search()
    {
        $search = $this->input->get('s', true);
        $search = (!empty($search)) ? trim(strip_tags($search)) : '';
        if(empty($search)) redirect(base_url());

        $this->data['search'] = $search;
        $get_all = $this->db
            ->distinct()
            ->select('shop_products.id')
            ->where('shop_products.show', 1)
            ->where('(shop_products.title LIKE \'%' . $this->db->escape_like_str($search) . '%\' OR shop_products.code = ' . $this->db->escape($search) . ')', null, false)
            ->get('shop_products')->result_array();
        $total = count($get_all);

        $brands = $this->db
            ->distinct()
            ->select('shop_products.brand_id, categoryes.title')
            ->join('categoryes', 'categoryes.id = shop_products.brand_id', 'left')
            ->where('shop_products.show', 1)
            ->where('(shop_products.title LIKE \'%' . $this->db->escape_like_str($search) . '%\' OR shop_products.code = ' . $this->db->escape($search) . ')', null, false)
            ->order_by('categoryes.title')
            ->get('shop_products')->result_array();
        $brands = (!empty($brands)) ? toolIndexArrayBy($brands, 'brand_id') : array();

        // from Pages::getProductsByCategory($category_id, $base_url)
        $page = (int)$this->input->get('pg');
        $per_page = 30;

        $filters = array(
            'left' => $this->input->get('left', true),
            'brand' => $this->input->get('brand', true),
            'sort' => $this->input->get('sort', true),
            'currency' => $this->input->get('currency', true),
        );
        $order_dir = 'desc';
        $order_by = 'shop_products.id';
//        $order_by = 'shop_products.date_add';
//        $order_by = 'shop_products.date_public';
//        $order_by = 'shop_products.views';
        $session_currency = $this->session->userdata('currency');
        $currency = (!empty($session_currency)) ? $session_currency : 'grn';

        // сортировка по цене, независимо от валюты – сортируем по ценам, конвертированным в одну валюту usd
        $e_rates = array('grn_usd' => 1, 'eur_usd' => 1, 'usd_grn' => 1, 'eur_grn' => 1, 'usd_eur' => 1, 'grn_eur' => 1); // новые направления конвертации для новых валют в системе добавлять здесь
        // получаем курсы валют согласно направлениям конвертации - все валюты конвертим в usd
        $e_rates_db = $this->db->select('var_name, value')->get('e_rates')->result_array();
        if(!empty($e_rates_db)){
            foreach($e_rates_db as $e_rate){
                $e_rates[$e_rate['var_name']] = str_replace(',', '.', $e_rate['value']);
            }
        }

        // получаем продукты
        $this->db->distinct();
        $this->db->select('shop_products.*'); // перечислить нужные поля для выборки
        $this->db->select("ROUND(shop_products.price*(IF(shop_products.currency='grn', " . $e_rates['grn_usd'] . ", IF(shop_products.currency='eur', " . $e_rates['eur_usd'] . ", 1))), 2) AS price_usd", FALSE); // новую проверку валюты и условие IF в запрос добавлять здесь
        $this->db->where('(shop_products.title LIKE \'%' . $this->db->escape_like_str($search) . '%\' OR shop_products.code = ' . $this->db->escape($search) . ')', null, false);

        // фильтры
        if(!empty($filters)){
            // по Производителю
            if(!empty($filters['brand'])){
                $this->db->where('shop_products.brand_id', $filters['brand']);
            }
            // по Популярности, Цене и Новинкам (по порядку добавления на сайт)
            if(!empty($filters['sort'])){
                switch ($filters['sort']){
                    case 'new':
                        $order_dir = 'desc';
                        // указать один из вариантов:
                        $order_by = 'shop_products.id'; // по порядку добавления на сайт (ID) – новинки идут первыми
//                        $order_by = 'shop_products.date_add'; // по дате добавления на сайт – новинки идут первыми
//                        $order_by = 'shop_products.date_public'; // по дате публикации – новинки идут первыми
                        break;
                    case 'popular':
                        $order_dir = 'desc';
                        $order_by = 'shop_products.views'; // по количеству просмотров - самые просматриваемые в начале
                        break;
                    case 'asc':
                        $order_dir = 'asc';
                        $order_by = 'price_usd'; // по цене – от дешевых к дорогим
                        break;
                    case 'desc':
                        $order_dir = 'desc';
                        $order_by = 'price_usd'; // по цене – от дорогих к дешевым
                        break;
                    default:
//                        $order_dir = 'desc';
//                        $order_by = 'shop_products.views';
                        $order_dir = 'desc';
                        // указать один из вариантов:
                        $order_by = 'shop_products.id';
//                        $order_by = 'shop_products.date_add';
//                        $order_by = 'shop_products.date_public';
                        break;
                }
            }
            // цены в валютах
            $currencies = array('usd', 'grn', 'eur');
            if(!empty($filters['currency']) && ($filters['currency'] !== $currency) && in_array($filters['currency'], $currencies)){
                $currency = $filters['currency'];
            }
            // фильтрация товаров по выбранным опциям боковых фильтров Категории
            if(!empty($filters['left'])){
                foreach($filters['left'] as $fk => $fv){
                    $this->db->where_in('shop_products.f_' . $fk, $fv);
                }
            }
        }
        $this->db->where('shop_products.show', 1);

        $offset = ($page > 1) ? (($page - 1) * $per_page) : 0;
        $this->db->order_by($order_by . ' ' . $order_dir);
        $this->db->limit($per_page, $offset);
        $products = $this->db->get('shop_products')->result_array(); // получаем товары

        // устанавливаем валюту в сессию
//        $this->session->set_userdata('currency', $filters['currency']);
        $this->session->set_userdata('currency', $currency);

        // get products images and set price in current currency
        // получаем картинки к товарам и конвертируем цены в текущую валюту
        if(!empty($products)){
            $ids = $last_cat_ids = array();
            foreach ($products as $kp => $product) {
                $ids[] = $product['id'];
                if($product['currency'] !== $currency){
                    $products[$kp]['price'] = ceil($product['price'] * $e_rates[$product['currency'] . '_' . $currency]);
                    if(!empty($product['price_old'])){
                        $products[$kp]['price_old'] = ceil($product['price_old'] * $e_rates[$product['currency'] . '_' . $currency]);
                    }
                }
                // получаем ID последней категории для формирования по ней URL товара
                $last_cat_ids[$product['id']] = end(explode(',', $product['category_ids']));
            }

            // получаем URL конечных категорий для формирования URL товаров
            if(!empty($last_cat_ids)) {
                $last_cat_urls = $this->db
                    ->select('extra_id, url')
                    ->where_in('extra_id', $last_cat_ids)
                    ->where(array(
                        'extra_name' => 'category_id',
                        'enabled' => 1,
                    ))
                    ->get('url_structure')->result_array();
                if (!empty($last_cat_urls)) {
                    $last_cat_urls_indexed = toolIndexArrayBy($last_cat_urls, 'extra_id');
                    $this->data['products_urls'] = array();
                    foreach($last_cat_ids as $p_key => $last_cat_id){
                        $this->data['products_urls'][$p_key] = (!empty($last_cat_urls_indexed[$last_cat_id])) ? $last_cat_urls_indexed[$last_cat_id] : '';
                    }
                }
            }

            // получаем фото товаров и добавляем их к товарам
            if(!empty($ids)){
                $uploads_where = array(
                    'name' => 'product-photo',
//                    'order' => 1
                );
                $images = $this->db->select('file_name, extra_id, order')
                    ->where($uploads_where)
                    ->where_in('extra_id', $ids)
                    ->where_in('order', array(1, 2))
                    ->order_by('order asc')
                    ->get('uploads')->result_array();
                if(!empty($images)){
                    // вариант с одним изображением товара
//                    $images = toolIndexArrayBy($images, 'extra_id');
//                    foreach ($products as $ik => $item) {
//                        if(!empty($images[$item['id']]['file_name'])){
//                            $products[$ik]['image'] = HTTP_HOST . 'uploads/shop/products/thumbs/' .$images[$item['id']]['file_name'];
//                        }
//                        else{
//                            // image_placeholder
//                            $products[$ik]['image'] = HTTP_HOST . 'assets/newdesign/images/m4.jpg';
//                        }
//                    }
                    // вариант с двумя изображениями товара - для подмены при наведении курсора на картинку
                    $images = get_grouped_array($images, 'extra_id');
                    foreach ($products as $ik => $item) {
                        if(!empty($images[$item['id']][0]['file_name'])){
                            $products[$ik]['images'][0] = HTTP_HOST . 'uploads/shop/products/thumbs/' .$images[$item['id']][0]['file_name'];
                            $products[$ik]['images'][1] = (!empty($images[$item['id']][1]['file_name'])) ? HTTP_HOST . 'uploads/shop/products/thumbs/' .$images[$item['id']][1]['file_name'] : HTTP_HOST . 'assets/newdesign/images/m4.jpg';
                        }
                        else{
                            // заглушка – если у товара нет картинки
                            $products[$ik]['images'][0] = $products[$ik]['images'][1] = HTTP_HOST . 'assets/newdesign/images/m4.jpg';
                        }
                    }
                }
            }
        }

        // постраничная навигация
        $this->load->library('pagination');
        $this->config->load('pg', true);
        $config = $this->config->item('pg', 'pg');
        // custom pagination configuration
        // by http://stackoverflow.com/a/21191203
        $config['base_url'] = $config['first_url'] = base_url('search') . '/';
//        $query_string = $_GET;
        $query_string = $this->input->get(null, true);
        if (isset($query_string['pg']))
        {
            unset($query_string['pg']);
        }
        if (is_array($query_string) && count($query_string) > 0)
        {
            $config['suffix'] = '&' . http_build_query($query_string, '', "&");
            $config['first_url'] = $config['base_url'] . '?' . http_build_query($query_string, '', "&");
        }
        // /custom
        $config['per_page'] = $per_page;
        $config['total_rows'] = $total;
        $this->pagination->initialize($config);

        $this->data['sub_categories'] = array();
        $this->data['category_products'] = $products;
        $this->data['filters'] = array();
        $this->data['brands'] = $brands;
        $this->data['category_info']['url_structure']['url'] = 'search';
        $this->data['category_info']['title'] = 'Результаты поиска по запросу «'
            . $search . '»';
//        if(!empty($this->data['category_products'][0])){
//            $type_id = $this->data['category_products'][0]['type_id'];
//            $this->data['filters'] = $this->getOptionsList($type_id);
//        }
//        else if(!empty($_GET)) {
//            // товаров нет из-за фильтров – в этом случае получаем фильтры и бренды
//            // по одному любому товару этой категории, без учета фильтров
//            $type_id = $this->getTypeByCategory($map[$url]['category_id']);
//            $this->data['filters'] = $this->getOptionsList($type_id);
//        }

        $this->data['breadcrumbs'] = array(
            0 => array(
                'title' => 'Главная',
                'url' => base_url(),
            ),
            1 => array(
                'title' => 'Результаты поиска',
                'url' => base_url('search') . '?s=' . $search,
            ),
        );

        $this->data['category_childs'] = array();
        $this->data['filters_checked'] = array(
            'left' => $this->input->get('left', true),
            'brand' => $this->input->get('brand', true),
            'sort' => $this->input->get('sort', true),
            'currency' => $this->input->get('currency', true),
        );
        $this->data['no_filters'] = true;
        foreach($this->data['filters_checked'] as $fc_value){
            if(!empty($fc_value)){
                $this->data['no_filters'] = false;
                break;
            }
        }
        $this->data['page']['title'] = 'Результаты поиска по запросу: '
            . $search
            . ' | Интернет-магазин Носи Это';
        // настройки страницы
        $this->data['page']['meta_title'] = 'Результаты поиска по запросу: '
            . $search;
        $this->data['page']['meta_description'] = 'Результаты поиска по запросу: '
            . $search;
        $this->data['view'] = (!empty($this->is_mobile)) ? 'category2_mobile' : 'category2';
        $this->data['pagination'] = $this->pagination->create_links();
        // валюта
        $session_currency = $this->session->userdata('currency');
        $currency = (!empty($session_currency)) ? $session_currency : 'grn';
        $this->data['products_currency'] = $this->currency_marks[$currency];
        $this->data['filters_checked']['currency'] = $currency;
        // var_dump($this->data);
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    public function login()
    {
        $this->output->enable_profiler(false);
        $remember=1;
        if(
            $this->input->post("login_sm")!==false
            && $this->ion_auth->login($this->input->post("email"),$this->input->post("password"),$remember)
        ){
            if($this->input->post("ajax")!==false){
                print 1;
                exit;
            }

            redirect(base_url());
        }else{
            if($this->input->post("ajax")!==false){
                print $this->ion_auth->errors();
            }else{
                $this->login_error=$this->ion_auth->errors();
            }
        }

        // $this->ci->load->frontView("user/login",$this->d);
    }

    public function logout()
    {
        $this->ion_auth->logout();
        redirect(base_url());
    }

    public function registration()
    {
        $user = $this->session->userdata('username');
        $registration_success = $this->session->flashdata('registration_success');
        if(!empty($user) && empty($registration_success)){
            redirect(base_url());
        }
        $errors = '';
        $this->load->helper('form');

        if($this->input->post("register")!==false){
            $post = $this->input->post(null, true);

            $this->load->library('form_validation');
            $this->form_validation->set_rules('new_fio', 'Ф.И.О', 'trim|required|min_length[4]|max_length[30]|xss_clean');
            $this->form_validation->set_rules('new_password', 'Пароль', 'trim|required|min_length[6]|max_length[12]|alpha_dash');
            $this->form_validation->set_rules('new_email', 'Email', 'trim|required|valid_email');
            $this->form_validation->set_rules('new_phone', 'Номер телефона', 'trim|required|xss_clean');
            $this->form_validation->set_rules('new_city', 'Город', 'trim|xss_clean');
            $this->form_validation->set_rules('new_address', 'Адрес', 'trim|xss_clean');
            $this->form_validation->set_rules('new_fb', 'Facebook', 'trim|xss_clean');
            $this->form_validation->set_rules('new_vk', 'Vkontakte', 'trim|xss_clean');

            if($this->form_validation->run() !== false){
                $additional_data=array(
                    "first_name" => $post['new_fio'],
                    "phone" => $post['new_phone'],
                    "adress" => $post['new_address'],
                    "city" => $post['new_city'],
                    "active" => 1,
                    "subscribe" => (!empty($post['register_news'])) ? 1 : 0,
                    "messages" => (!empty($post['register_messages'])) ? 1 : 0,
                    "new_products" => (!empty($post['register_new_products'])) ? 1 : 0,
                    "vkontakte" => $post['new_vk'],
                    "facebook" => $post['new_fb'],
                );

                $user_id = $this->ion_auth->register($post['new_email'], $post['new_password'], $post['new_email'], $additional_data, array("group_id"=>2));

                if($user_id === false){
                    $errors .= implode("\n", $this->ion_auth->errors_array());
                }else{
                    // отправляем письмо
                    $registration_email = $this->load->view('newdesign/registration_email',
                        array(
                            'post' => $post,
                        ),
                        true
                    );
                    $this->load->library("email");
                    $this->email->from($this->config->config['email_from'], $this->config->config['email_from_name']);
                    $this->email->to($post['new_email']);
                    $this->email->subject('Поздравляем! Вы успешно зарегистрированы на сайте NosiEto.com.ua!');
                    $this->email->message($registration_email);
                    $this->email->send();

                    // автовход сразу после регистрации
                    $this->ion_auth->login($post['new_email'], $post['new_password'], true);
                    $this->session->set_flashdata('registration_success', 'Поздравляем вас с успешной регистрацией!');
                    redirect(base_url('registration'));
                }
            }
        }

        // настройки страницы
        $this->data['page']['title'] = 'Регистрация нового пользователя | Интернет-магазин Носи Это';
        $this->data['page']['meta_title'] = 'Регистрация нового пользователя';
        $this->data['page']['meta_description'] = 'Регистрация нового пользователя';
        $this->data['view'] = 'registration';
        $this->data['registration_errors'] = $errors;
        $this->data['form_errors'] = validation_errors();
        $this->data['registration_success'] = $registration_success;
        // var_dump($this->data);
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }
} 