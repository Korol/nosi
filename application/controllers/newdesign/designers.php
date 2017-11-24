<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Pages
 * @property Base_model $base_model    Base Model Class
 * CI_DB_active_record|CI_DB_mysql_driver $db
 *
 */
class Designers extends MY_Controller
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
        if ($this->input->ip_address() == '127.0.0.1') {
            $this->output->enable_profiler(true);
            error_reporting(E_ALL);
        }
    }

    public function index()
    {
        $per_page = 36;
        $page = (is_numeric(end($this->uri->segment_array()))) ? (int)end($this->uri->segment_array()) - 1 : 0;

        $this->data['designers'] = $this->base_model
            ->get_list('categoryes', 'id, title, name',
                array(
                    'show' => 1,
                    'type' => 'shop-manufacturer',
                    ),
                'title asc',
                $per_page,
                ($page * $per_page));

        $this->data['designers_all'] = $this->base_model
            ->get_list('categoryes', 'id, title, name',
                array(
                    'show' => 1,
                    'type' => 'shop-manufacturer',
                ),
                'title asc');

        if(!empty($this->data['designers'])){
            $ids = array_values(get_keys_array($this->data['designers'], 'id'));
            $photos = $this->db
                ->select('id, extra_id, file_path, file_name')
                ->where(array(
                    'extra_type' => 'manufacturer_id',
                    'name' => 'manufacturer_logo',
                ))
                ->where_in('extra_id', $ids)
                ->get('uploads')->result_array();
            $this->data['designers_logos'] = (!empty($photos)) ? toolIndexArrayBy($photos, 'extra_id') : array();
        }
        else
            show_404();

        $this->data['breadcrumbs'] = array(
            0 => array(
                'title' => 'Главная',
                'url' => base_url(),
            ),
            1 => array(
                'title' => 'Бренды',
                'url' => base_url('brands'),
            ),
        );
        // постраничная навигация
        $this->load->library('pagination');
        $this->config->load('pg', true);
        $config = $this->config->item('pg', 'pg');
        unset($config['page_query_string'], $config['query_string_segment']);
        $config['base_url'] = $config['first_url'] = base_url('brands');
        $config['per_page'] = $per_page;
        $config['num_links'] = 2;
        $config['uri_segment'] = 2 + $this->fake_uri_segment;
        $config['total_rows'] = $this->base_model
            ->count_rows('categoryes',
                array(
                    'show' => 1,
                    'type' => 'shop-manufacturer',
                ));
        $this->pagination->initialize($config);
        $this->data['pagination'] = $this->pagination->create_links();
        // настройки страницы
        $this->data['page']['title'] = $this->buildTitle('Бренды');
        $this->data['page']['meta_title'] = $this->buildTitle('Бренды');
        $this->data['page']['meta_description'] = 'Бренды';
        $this->data['view'] = 'designers';
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    public function view($id, $slug, $page = 0)
    {
        $this->data['designer'] = $this->db
            ->select('categoryes.id, categoryes.title, categoryes.description, categoryes.name')
            ->where(array(
                'categoryes.show' => 1,
                'categoryes.type' => 'shop-manufacturer',
                'categoryes.id' => $id,
            ))
            ->limit(1)
            ->get('categoryes')->row_array();

        if(empty($this->data['designer'])) show_404();

        $this->data['designer_logo'] = $this->db
            ->select('uploads.file_path, uploads.file_name')
            ->where(array(
                'extra_type' => 'manufacturer_id',
                'name' => 'manufacturer_logo',
                'extra_id' => $id,
            ))
            ->limit(1)
            ->get('uploads')->row_array();
        if(empty($this->data['designer_logo'])){
            $this->data['designer_logo'] = array(
                'file_path' => 'assets/newdesign/images/',
                'file_name' => 'm4.jpg',
            );
        }

        $this->data['categories'] = $this->db
            ->distinct()
            ->select('shop_products_categories_link.category_id, categoryes.id, url_structure.title, url_structure.url, shop_products.brand_id')
            ->join('shop_products', 'shop_products.id = shop_products_categories_link.product_id')
            ->join('categoryes', 'categoryes.id = shop_products_categories_link.category_id')
            ->join('url_structure', 'url_structure.extra_id = categoryes.id')
            ->where(array(
                'shop_products.brand_id' => $id,
                'shop_products.show' => 1,
                'categoryes.show' => 1,
                'url_structure.extra_name' => 'category_id',
                'categoryes.type' => 'shop-category',
                'categoryes.parent_id >' => 0,
            ))
            ->order_by('url_structure.title asc')
            ->get('shop_products_categories_link')->result_array();

        $per_page = 30;
        $page = (is_numeric(end($this->uri->segment_array()))) ? (int)end($this->uri->segment_array()) - 1 : 0;

        $this->data['products'] = $this->db
            ->select('id, title, name, category_ids, price, price_old, currency')
            ->where(array(
                'show' => 1,
                'brand_id' => $id,
            ))
            ->order_by('views desc')
            ->limit($per_page, ($page * $per_page))
            ->get('shop_products')->result_array();
        if(!empty($this->data['products'])){
            $product_ids = array_values(get_keys_array($this->data['products'], 'id'));
            // участвуют ли продукты в текущей акции
            $this->data['action'] = $this->check_products_action($product_ids);
            $images = $this->db
                ->select('file_name, extra_id')
                ->where(array(
                    'name' => 'product-photo',
                ))
                ->where_in('order', array(1, 2))
                ->where_in('extra_id', $product_ids)
                ->order_by('order asc')
                ->get('uploads')->result_array();
            $images = (!empty($images)) ? get_grouped_array($images, 'extra_id') : array();

            // распихиваем фото по товарам и конвертим валюту товара в валюту сайта
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

            $last_cat_ids = array();
            foreach($this->data['products'] as $kp => $product){
                if($product['currency'] !== $currency){
                    $this->data['products'][$kp]['price'] = ceil($product['price'] * $e_rates[$product['currency'] . '_' . $currency]);
                    if(!empty($product['price_old'])){
                        $this->data['products'][$kp]['price_old'] = ceil($product['price_old'] * $e_rates[$product['currency'] . '_' . $currency]);
                    }
                }
                // получаем ID последней категории для формирования по ней URL товара
                $last_cat_ids[$product['id']] = end(explode(',', $product['category_ids']));
                // цепляем фотки к товару
                if(!empty($images[$product['id']][0]['file_name'])){
                    $this->data['products'][$kp]['images'][0] = HTTP_HOST . 'uploads/shop/products/thumbs/' .$images[$product['id']][0]['file_name'];
                    $this->data['products'][$kp]['images'][1] = (!empty($images[$product['id']][1]['file_name'])) ? HTTP_HOST . 'uploads/shop/products/thumbs/' .$images[$product['id']][1]['file_name'] : HTTP_HOST . 'assets/newdesign/images/m4.jpg';
                }
                else{
                    // заглушка – если у товара нет картинки
                    $this->data['products'][$kp]['images'][0] = $this->data['products'][$kp]['images'][1] = HTTP_HOST . 'assets/newdesign/images/m4.jpg';
                }
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

        }

        // постраничная навигация
        $this->load->library('pagination');
        $this->config->load('pg', true);
        $config = $this->config->item('pg', 'pg');
        unset($config['page_query_string'], $config['query_string_segment']);
        $config['base_url'] = $config['first_url'] = base_url('brand/' . $id . '/' . $this->data['designer']['name']);
        $config['per_page'] = $per_page;
        $config['num_links'] = 2;
        $config['uri_segment'] = 4 + $this->fake_uri_segment;
        $config['total_rows'] = $this->base_model
            ->count_rows('shop_products',
                array(
                    'show' => 1,
                    'brand_id' => $id,
                ));
        $this->pagination->initialize($config);
        $this->data['pagination'] = $this->pagination->create_links();

        $this->data['products_currency'] = $this->currency_marks[$currency];
        $this->data['breadcrumbs'] = array(
            0 => array(
                'title' => 'Главная',
                'url' => base_url(),
            ),
            1 => array(
                'title' => 'Бренды',
                'url' => base_url('brands'),
            ),
            2 => array(
                'title' => $this->data['designer']['title'],
                'url' => base_url('brand/' . $this->data['designer']['id'] . '/' . $this->data['designer']['name']),
            ),
        );
        // настройки страницы
        $this->data['page']['title'] = $this->buildTitle($this->data['designer']['title']);
        $this->data['page']['meta_title'] = $this->buildTitle($this->data['designer']['title']);
        $this->data['page']['meta_description'] = $this->data['designer']['title'];
        $this->data['view'] = 'designer';
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    public function by_category()
    {
        $map = array(
            'brands-womens' => 1638,
            'brands-mens' => 1639,
            'brands-childrens' => 1640,
        );
        $fake_categories = array(
            'brands-womens' => 1885,
            'brands-mens' => 1885,
            'brands-childrens' => 1885,
        );
        $url = end($this->uri->segment_array());
        if(!in_array($url, array_keys($map))) redirect(base_url());
        $this->data['url'] = $url;
        // описание для псевдо-категории
        $fake = $this->getFakeCategoryDescription($fake_categories[$url]);
        $this->data['category_description'] = $fake['description'];
        $this->data['category_title'] = $fake['title'];

//        select distinct shop_products.brand_id, categoryes.id, categoryes.title, categoryes.name from shop_products
//        left join shop_products_categories_link on shop_products_categories_link.product_id = shop_products.id
//        left join categoryes on categoryes.id = shop_products.brand_id
//        where shop_products_categories_link.category_id = '1640'
//            and shop_products.show = 1
//            and shop_products.brand_id > 0
//            and categoryes.show = 1
//        order by categoryes.title asc;
        $this->data['designers'] = $this->data['designers_all'] = $this->db
            ->distinct()
            ->select('shop_products.brand_id, categoryes.id, categoryes.title, categoryes.name')
            ->join('shop_products_categories_link', 'shop_products_categories_link.product_id = shop_products.id', 'left')
            ->join('categoryes', 'categoryes.id = shop_products.brand_id', 'left')
            ->where(array(
                'shop_products_categories_link.category_id' => $map[$url],
                'shop_products.show' => 1,
                'shop_products.brand_id >' => 0,
                'categoryes.show' => 1,
            ))
            ->order_by('categoryes.title asc')
            ->get('shop_products')->result_array();

        if(!empty($this->data['designers'])){
            $ids = array_values(get_keys_array($this->data['designers'], 'id'));
            $photos = $this->db
                ->select('id, extra_id, file_path, file_name')
                ->where(array(
                    'extra_type' => 'manufacturer_id',
                    'name' => 'manufacturer_logo',
                ))
                ->where_in('extra_id', $ids)
                ->get('uploads')->result_array();
            $this->data['designers_logos'] = (!empty($photos)) ? toolIndexArrayBy($photos, 'extra_id') : array();
        }
        else
            redirect(base_url());

        $this->data['breadcrumbs'] = array(
            0 => array(
                'title' => 'Главная',
                'url' => base_url(),
            ),
            1 => array(
                'title' => (!empty($fake['title'])) ? $fake['title'] : 'Бренды',
                'url' => base_url($url),
            ),
        );

        // определяем активный пункт верхнего меню первого уровня
        $url_ids = $this->db
            ->select('id')
            ->where(array(
                'extra_id' => $map[$url],
                'extra_name' => 'category_id',
            ))
            ->get('url_structure')->result_array();
        if(!empty($url_ids)){
            $url_ids = toolIndexArrayBy($url_ids, 'id');
            $cat_ids = $this->db
                ->select('id')
                ->where_in('extra_id', array_keys($url_ids))
                ->where(array(
                    'show' => 1,
                    'extra_name' => 'structure_id',
                ))
                ->get('categoryes')->result_array();
            if(!empty($cat_ids)){
                $top_menu_ids = array_keys($this->top_menu[1]);
                foreach($cat_ids as $c_id){
                    if(in_array($c_id['id'], $top_menu_ids)){
                        $this->tm_active_id = $c_id['id'];
                        break;
                    }
                }
            }
        }

        // настройки страницы
        $this->data['page']['title'] = (!empty($fake['title']))
            ? $this->buildTitle($fake['title'])
            : $this->buildTitle('Бренды');
        $this->data['page']['meta_title'] = (!empty($fake['title']))
            ? $this->buildTitle($fake['title'])
            : $this->buildTitle('Бренды');
        $this->data['page']['meta_description'] = (!empty($fake['meta_description']))
            ? $fake['meta_description']
            : 'Бренды';
        $this->data['view'] = 'designers2';
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    public function by_category_brand($id)
    {
        $map = array(
            'brands-womens' => array(
                'id' => 1638,
                'title' => 'Женские бренды',
            ),
            'brands-mens' => array(
                'id' => 1639,
                'title' => 'Мужские бренды',
            ),
            'brands-childrens' => array(
                'id' => 1640,
                'title' => 'Детские бренды',
            ),
        );
        $full_url = $this->uri->uri_string();
        $full_url = str_replace('newdesign/', '', $full_url);
        $ex_url = explode('/', $full_url);
        $url = current($ex_url);
        if(!in_array($url, array_keys($map))) redirect(base_url());

        $this->data['designer'] = $this->db
            ->select('categoryes.id, categoryes.title, categoryes.description, categoryes.name')
            ->where(array(
                'categoryes.show' => 1,
                'categoryes.type' => 'shop-manufacturer',
                'categoryes.id' => $id,
            ))
            ->limit(1)
            ->get('categoryes')->row_array();

        if(empty($this->data['designer'])) show_404();

        $this->data['designer_logo'] = $this->db
            ->select('uploads.file_path, uploads.file_name')
            ->where(array(
                'extra_type' => 'manufacturer_id',
                'name' => 'manufacturer_logo',
                'extra_id' => $id,
            ))
            ->limit(1)
            ->get('uploads')->row_array();
        if(empty($this->data['designer_logo'])){
            $this->data['designer_logo'] = array(
                'file_path' => 'assets/newdesign/images/',
                'file_name' => 'm4.jpg',
            );
        }

        $filters = array(
            'sort' => $this->input->get('sort', true),
            'currency' => $this->input->get('currency', true),
        );
        $order_dir = 'desc';
        $order_by = 'shop_products.id';

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

        $session_currency = $this->session->userdata('currency');
        $currency = (!empty($session_currency)) ? $session_currency : 'grn';

        // цены в валютах
        $currencies = array('usd', 'grn', 'eur');
        if(!empty($filters['currency']) && ($filters['currency'] !== $currency) && in_array($filters['currency'], $currencies)){
            $currency = $filters['currency'];
        }

        // устанавливаем валюту в сессию
        $this->session->set_userdata('currency', $currency);

        // сортировка по цене, независимо от валюты – сортируем по ценам, конвертированным в одну валюту usd
        $e_rates = array('grn_usd' => 1, 'eur_usd' => 1, 'usd_grn' => 1, 'eur_grn' => 1, 'usd_eur' => 1, 'grn_eur' => 1); // новые направления конвертации для новых валют в системе добавлять здесь
        // получаем курсы валют согласно направлениям конвертации - все валюты конвертим в usd
        $e_rates_db = $this->db->select('var_name, value')->get('e_rates')->result_array();
        if(!empty($e_rates_db)){
            foreach($e_rates_db as $e_rate){
                $e_rates[$e_rate['var_name']] = str_replace(',', '.', $e_rate['value']);
            }
        }

        $per_page = 30;
        $page = (is_numeric(end($this->uri->segment_array()))) ? (int)end($this->uri->segment_array()) - 1 : 0;

        $this->data['products'] = $this->db
            ->select('shop_products.id, shop_products.title, shop_products.name, shop_products.category_ids, shop_products.price, shop_products.price_old, shop_products.currency')
            ->select("ROUND(shop_products.price*(IF(shop_products.currency='grn', " . $e_rates['grn_usd'] . ", IF(shop_products.currency='eur', " . $e_rates['eur_usd'] . ", 1))), 2) AS price_usd", FALSE) // новую проверку валюты и условие IF в запрос добавлять здесь
            ->join('shop_products_categories_link', 'shop_products_categories_link.product_id = shop_products.id')
            ->where(array(
                'shop_products.show' => 1,
                'shop_products.brand_id' => $id,
                'shop_products_categories_link.category_id' => $map[$url]['id'],
            ))
            ->order_by($order_by . ' ' . $order_dir)
            ->limit($per_page, ($page * $per_page))
            ->get('shop_products')->result_array();
        if(!empty($this->data['products'])){
            $product_ids = array_values(get_keys_array($this->data['products'], 'id'));
            // участвуют ли продукты в текущей акции
            $this->data['action'] = $this->check_products_action($product_ids);
            $images = $this->db
                ->select('file_name, extra_id')
                ->where(array(
                    'name' => 'product-photo',
                ))
                ->where_in('order', array(1, 2))
                ->where_in('extra_id', $product_ids)
                ->order_by('order asc')
                ->get('uploads')->result_array();
            $images = (!empty($images)) ? get_grouped_array($images, 'extra_id') : array();

            // распихиваем фото по товарам и конвертим валюту товара в валюту сайта
            $last_cat_ids = array();
            foreach($this->data['products'] as $kp => $product){
                if($product['currency'] !== $currency){
                    $this->data['products'][$kp]['price'] = ceil($product['price'] * $e_rates[$product['currency'] . '_' . $currency]);
                    if(!empty($product['price_old'])){
                        $this->data['products'][$kp]['price_old'] = ceil($product['price_old'] * $e_rates[$product['currency'] . '_' . $currency]);
                    }
                }
                // получаем ID последней категории для формирования по ней URL товара
                $last_cat_ids[$product['id']] = end(explode(',', $product['category_ids']));
                // цепляем фотки к товару
                if(!empty($images[$product['id']][0]['file_name'])){
                    $this->data['products'][$kp]['images'][0] = HTTP_HOST . 'uploads/shop/products/thumbs/' .$images[$product['id']][0]['file_name'];
                    $this->data['products'][$kp]['images'][1] = (!empty($images[$product['id']][1]['file_name'])) ? HTTP_HOST . 'uploads/shop/products/thumbs/' .$images[$product['id']][1]['file_name'] : HTTP_HOST . 'assets/newdesign/images/m4.jpg';
                }
                else{
                    // заглушка – если у товара нет картинки
                    $this->data['products'][$kp]['images'][0] = $this->data['products'][$kp]['images'][1] = HTTP_HOST . 'assets/newdesign/images/m4.jpg';
                }
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

        }

        // определяем активный пункт верхнего меню первого уровня
        $url_ids = $this->db
            ->select('id')
            ->where(array(
                'extra_id' => $map[$url]['id'],
                'extra_name' => 'category_id',
            ))
            ->get('url_structure')->result_array();
        if(!empty($url_ids)){
            $url_ids = toolIndexArrayBy($url_ids, 'id');
            $cat_ids = $this->db
                ->select('id')
                ->where_in('extra_id', array_keys($url_ids))
                ->where(array(
                    'show' => 1,
                    'extra_name' => 'structure_id',
                ))
                ->get('categoryes')->result_array();
            if(!empty($cat_ids)){
                $top_menu_ids = array_keys($this->top_menu[1]);
                foreach($cat_ids as $c_id){
                    if(in_array($c_id['id'], $top_menu_ids)){
                        $this->tm_active_id = $c_id['id'];
                        break;
                    }
                }
            }
        }

        // постраничная навигация
        $this->load->library('pagination');
        $this->config->load('pg', true);
        $config = $this->config->item('pg', 'pg');
        unset($config['page_query_string'], $config['query_string_segment']);
        $config['base_url'] = $config['first_url'] = base_url($url . '/' . $id . '/' . $this->data['designer']['name']);
        $config['per_page'] = $per_page;
        $config['num_links'] = 2;
        $config['uri_segment'] = 4 + $this->fake_uri_segment;
        $query_string = $this->input->get(null, true);
        $config['suffix'] = (!empty($query_string)) ? '?' . http_build_query($query_string, '', "&") : '';
        $total = $this->db
            ->select('COUNT(shop_products.id) AS cntr', false)
            ->join('shop_products_categories_link', 'shop_products_categories_link.product_id = shop_products.id')
            ->where(array(
                'shop_products.show' => 1,
                'shop_products.brand_id' => $id,
                'shop_products_categories_link.category_id' => $map[$url]['id'],
            ))
            ->get('shop_products')->row()->cntr;
        $config['total_rows'] = $total;
        $this->pagination->initialize($config);
        $this->data['pagination'] = $this->pagination->create_links();

        $this->data['products_currency'] = $this->currency_marks[$currency];
        $this->data['breadcrumbs'] = array(
            0 => array(
                'title' => 'Главная',
                'url' => base_url(),
            ),
            1 => array(
                'title' => $map[$url]['title'],
                'url' => base_url($url),
            ),
            2 => array(
                'title' => $this->data['designer']['title'],
                'url' => base_url($url . '/' . $this->data['designer']['id'] . '/' . $this->data['designer']['name']),
            ),
        );

        $this->data['filters_checked'] = array(
            'sort' => $this->input->get('sort', true),
            'currency' => $this->input->get('currency', true),
        );
        if(empty($this->data['filters_checked']['currency'])){
            $this->data['filters_checked']['currency'] = $currency;
        }

        // проверяем наличие подкатегорий у основной категории
        // для вывода списка ссылок на подкатегории слева
        $subcat_ids = $this->get_subcats($map[$url]['id']);
        if(!empty($subcat_ids)){
            // проходим по подкатегориям, и проверяем, есть ли в БД (shop_products_categories_link) товары,
            // которые принадлежат к каждой подкатегории + имеют наш текущий бренд
            // те подкатегории, в которых есть хоть один товар, соответствующий критериям
            // подкатегория + бренд = оставляем, и выводим в боковое меню
            // остальные – удаляем и не выводим
            $real_subcats = array();
            foreach($subcat_ids as $subcat_id){
                $sc_res = $this->db
                    ->select('shop_products_categories_link.product_id')
                    ->join('shop_products', 'shop_products.id = shop_products_categories_link.product_id')
                    ->where(
                        array(
                            'shop_products_categories_link.category_id' => $subcat_id,
                            'shop_products.brand_id' => $id,
                            'shop_products.show' => 1,
                        )
                    )
                    ->count_all_results('shop_products_categories_link');
                if($sc_res > 0){
                    $real_subcats[$subcat_id] = $sc_res;
                }
            }

            // получаем для оставшихся подкатегорий их пункты меню
            // формируем ссылки на эту подкатегорию + указанный в URL бренд для фильтрации товаров
            if(!empty($real_subcats)){
                $this->data['subcats'] = $this->db
                    ->select('title, url, extra_id')
                    ->where_in('extra_id', array_keys($real_subcats))
                    ->where('extra_name', 'category_id')
                    ->order_by('title', 'asc')
                    ->get('url_structure')->result_array();
            }

            // для новинок и распродаж – этот момент нужно продумать
            // как вариант: выводить ссылки прямо на те же страницы новинок или распродаж, с указанной в URL
            // подкатегорией (?category_id=1234) и по этому параметру фильтровать товары для новинок и распродаж
        }

        // настройки страницы
        $this->data['page']['title'] = $this->buildTitle($map[$url]['title'] . ' – ' . $this->data['designer']['title']);
        $this->data['page']['meta_title'] = $this->buildTitle($map[$url]['title'] . ' – ' . $this->data['designer']['title']);
        $this->data['page']['meta_description'] = $map[$url]['title'] . ' ' . $this->data['designer']['title'];
        $this->data['view'] = (!empty($this->is_mobile)) ? 'designer2_mobile' : 'designer2';
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    /**
     * Получаем ID ВСЕХ подкатегорий основной категории
     * @param $id ID основной категории
     * @return array массив ID ВСЕХ подкатегорий (расчитано на 3 уровня вложенности)
     */
    public function get_subcats($id){
        $return = array();
        $cats = $this->db
            ->select('id, title, parent_id')
            ->where(
                array(
                    'type' => 'shop-category',
                    'show' => 1,
                )
            )
            ->get('categoryes')->result_array();
        if(!empty($cats)){
            $cats = toolIndexArrayBy(table_to_tree_array($cats), 'id');
            if(!empty($cats[$id])){
                if(!empty($cats[$id]['child'])){
                    $return = implode(',' ,array_keys(toolIndexArrayBy($cats[$id]['child'], 'id'))); // 1-st level
                    foreach($cats[$id]['child'] as $level2){
                        if(!empty($level2['child'])){
                            $childs2 = array_keys(toolIndexArrayBy($level2['child'], 'id')); // 2-nd level
                            $return = (!empty($childs2)) ? $return . ',' . implode(',', $childs2) : $return;
                            foreach($level2['child'] as $level3){
                                if(!empty($level3['child'])){
                                    $childs3 = array_keys(toolIndexArrayBy($level3['child'], 'id')); // 3-rd level
                                    $return = (!empty($childs3)) ? $return . ',' . implode(',', $childs3) : $return;
                                }
                            }
                        }
                    }
                }
            }
        }
        return (!empty($return)) ? explode(',', $return) : array();
    }

    /**
     * если продукты участвуют в активной акции
     * то возвращает массив с информацией о скидке,
     * назначенной каждому продукту в данной активной акции
     * если не участвуют – то false
     * @param $product_ids
     * @return bool|array
     */
    public function check_products_action($product_ids)
    {
        $active_action = $this->db
            ->where('active', 1)
            ->where("NOW() BETWEEN `start` AND `end`", null, false)
            ->limit(1)
            ->get('action')->row_array();
        if(!empty($active_action)){
            $check_products['products'] = $this->db
                ->where(array(
                    'action_id' => $active_action['id'],
                ))
                ->where_in('product_id', $product_ids)
                ->get('action_product')->result_array();
            if(!empty($check_products['products'])) {
                $check_products['products'] = toolIndexArrayBy($check_products['products'], 'product_id');
                $check_products['action_info'] = $active_action;
            }
            return $check_products;
        }
        return false;
    }
}