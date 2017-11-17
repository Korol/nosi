<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Shop
 * @property Base_model $base_model    Base Model Class
 * CI_DB_active_record|CI_DB_mysql_driver $db
 *
 */
class Shop extends MY_Controller
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

    public function add_to_cart()
    {
        $this->data['post'] = $this->input->post(null, true);
        // настройки страницы
        $this->data['page']['title'] = 'Ваша корзина | Интернет-магазин Носи Это';
        $this->data['page']['meta_title'] = '';
        $this->data['page']['meta_description'] = '';
        $this->data['view'] = 'cart';
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    public function by_category()
    {
        $map = array(
            'womens-sale' => array(
                'category_id' => 1638,
                'categories' => array(1638, 1618),
                'bc_maincat' => 'Женщины',
                'fake_category' => 1885,
            ),
            'mens-sale' => array(
                'category_id' => 1639,
                'categories' => array(1639, 1618),
                'bc_maincat' => 'Мужчины',
                'fake_category' => 1885,
            ),
            'childrens-sale' => array(
                'category_id' => 1640,
                'categories' => array(1640, 1618),
                'bc_maincat' => 'Дети',
                'fake_category' => 1885,
            ),
            'in-stock-womens' => array(
                'category_id' => 1638,
                'categories' => array(1638, 1641),
                'bc_maincat' => 'Женщины',
//                'bc_maincat_url' => base_url('in-stock'),
                'fake_category' => 1977,
                'page_title' => 'Женская одежда, обувь, сумки, аксессуары в наличии Цены, купить в Киеве, Харькове, Днепропетровске, Одессе, Запорожье, Львове. Женские вещи в наличии: обзор, описание, продажа | Интернет-магазин Носи Это',
            ),
            'in-stock-mens' => array(
                'category_id' => 1639,
                'categories' => array(1639, 1641),
                'bc_maincat' => 'Мужчины',
//                'bc_maincat_url' => base_url('in-stock'),
                'fake_category' => 1978,
                'page_title' => 'Мужская одежда, обувь, сумки, аксессуары в наличии Цены, купить в Киеве, Харькове, Днепропетровске, Одессе, Запорожье, Львове. Мужские вещи в наличии: обзор, описание, продажа | Интернет-магазин Носи Это',
            ),
            'in-stock-childrens' => array(
                'category_id' => 1640,
                'categories' => array(1640, 1641),
                'bc_maincat' => 'Дети',
//                'bc_maincat_url' => base_url('in-stock'),
                'fake_category' => 1979,
                'page_title' => 'Детская одежда, обувь в наличии Цены, купить в Киеве, Харькове, Днепропетровске, Одессе, Запорожье, Львове. Детские вещи в наличии: обзор, описание, продажа | Интернет-магазин Носи Это',
            ),
        );
        $url = end($this->uri->segment_array());
        if(!in_array($url, array_keys($map))) redirect(base_url());
        // описание для псевдо-категории
        $fake = $this->getFakeCategoryDescription($map[$url]['fake_category']);
        $this->data['category_description'] = $fake['description'];

        // учёт subcategory – если она указана в URL
        $subcategory = (int)$this->input->get('subcategory');
        $map_categories = $map[$url]['categories'];
        if(!empty($subcategory)){
            $map_categories[] = $subcategory;
        }
        $having_num = count($map_categories);

        $get_all = $this->db
            ->distinct()
            ->select('shop_products_categories_link.product_id')
//            ->where_in('category_id', $map[$url]['categories'])
            ->join('shop_products', 'shop_products.id = shop_products_categories_link.product_id')
            ->where_in('shop_products_categories_link.category_id', $map_categories)
            ->where('shop_products.show', 1)
            ->group_by('shop_products_categories_link.product_id')
//            ->having('COUNT(`category_id`)', 2, false)
            ->having('COUNT(`shop_products_categories_link`.`category_id`)', $having_num, false)
            ->get('shop_products_categories_link')->result_array();
        $total = count($get_all);

        $brands = $this->db
            ->distinct()
            ->select('shop_products.brand_id, categoryes.title')
            ->join('shop_products_categories_link', 'shop_products_categories_link.product_id = shop_products.id', 'left')
            ->join('categoryes', 'categoryes.id = shop_products.brand_id', 'left')
//            ->where_in('shop_products_categories_link.category_id', $map[$url]['categories'])
            ->where_in('shop_products_categories_link.category_id', $map_categories)
            ->where('shop_products.show', 1)
            ->where('categoryes.show', 1)
            ->group_by('shop_products_categories_link.product_id')
//            ->having('COUNT(`category_id`)', 2, false)
            ->having('COUNT(`category_id`)', $having_num, false)
            ->order_by('categoryes.title asc')
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
        $this->db->select('shop_products_categories_link.product_id');
        $this->db->select('shop_products.*'); // перечислить нужные поля для выборки
        $this->db->select("ROUND(shop_products.price*(IF(shop_products.currency='grn', " . $e_rates['grn_usd'] . ", IF(shop_products.currency='eur', " . $e_rates['eur_usd'] . ", 1))), 2) AS price_usd", FALSE); // новую проверку валюты и условие IF в запрос добавлять здесь
        $this->db->join('shop_products', 'shop_products.id = shop_products_categories_link.product_id');
//        $this->db->where_in('shop_products_categories_link.category_id', $map[$url]['categories']);
        $this->db->where_in('shop_products_categories_link.category_id', $map_categories);
            //->get('shop_products_categories_link')->result_array();

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
        $this->db->group_by('shop_products_categories_link.product_id');
//        $this->db->having('COUNT(`shop_products_categories_link`.`category_id`)', 2, false);
        $this->db->having('COUNT(`shop_products_categories_link`.`category_id`)', $having_num, false);

        $offset = ($page > 1) ? (($page - 1) * $per_page) : 0;
        $this->db->order_by($order_by . ' ' . $order_dir);
        $this->db->limit($per_page, $offset);
        $products = $this->db->get('shop_products_categories_link')->result_array(); // получаем товары

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
        $config['base_url'] = $config['first_url'] = base_url($url) . '/';
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
        $this->data['category_info']['url_structure']['url'] = $url;
        $this->data['category_info']['title'] = (!empty($fake['title']))
            ? $fake['title']
            : 'Распродажа в категории ' . $map[$url]['bc_maincat'];
        if(!empty($this->data['category_products'][0])){
            $type_id = $this->data['category_products'][0]['type_id'];
            $this->data['filters'] = $this->getOptionsList($type_id);
        }
        else if(!empty($_GET)) {
            // товаров нет из-за фильтров – в этом случае получаем фильтры и бренды
            // по одному любому товару этой категории, без учета фильтров
            $type_id = $this->getTypeByCategory($map[$url]['category_id']);
            $this->data['filters'] = $this->getOptionsList($type_id);
        }

        $main_cat_info = $this->getCatUrl($map[$url]['category_id']);
        $this->data['breadcrumbs'] = array(
            0 => array(
                'title' => 'Главная',
                'url' => base_url(),
            ),
            1 => array(
                'title' => $map[$url]['bc_maincat'],
                'url' => (!empty($main_cat_info['url'])) ? $main_cat_info['url'] : '',
            ),
            2 => array(
                'title' => (!empty($fake['title'])) ? $fake['title'] : 'Распродажа',
                'url' => base_url($url),
            ),
        );
        if(!empty($map[$url]['bc_maincat_url'])){
            $this->data['breadcrumbs'][1]['url'] = $map[$url]['bc_maincat_url'];
        }
        // определяем активный пункт верхнего меню первого уровня
        if(!empty($main_cat_info['id'])) {
            $url_ids = array($main_cat_info['id']);
            if (!empty($url_ids)) {
                // по этим ID получаем ID пунктов меню для этих крошек
                $menu_ids_res = $this->db
                    ->select('id')
                    ->where_in('extra_id', $url_ids)
                    ->get('categoryes')->result_array();
                if (!empty($menu_ids_res)) {
                    $menu_ids = array_keys(toolIndexArrayBy($menu_ids_res, 'id'));
                    $top_menu_ids = array_keys($this->top_menu[1]);
                    foreach ($menu_ids as $mid) {
                        if (in_array($mid, $top_menu_ids)) {
                            $this->tm_active_id = $mid;
                            break;
                        }
                    }
                }
            }
        }

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

        // проверяем наличие подкатегорий у основной категории
        // для вывода списка ссылок на подкатегории слева
        $subcat_ids = $this->get_subcats($map[$url]['category_id']);
        if(!empty($subcat_ids)){
            // проходим по подкатегориям, и проверяем, есть ли в БД (shop_products_categories_link) товары,
            // которые принадлежат к каждой подкатегории + имеют наш текущий бренд
            // те подкатегории, в которых есть хоть один товар, соответствующий критериям
            // подкатегория + бренд = оставляем, и выводим в боковое меню
            // остальные – удаляем и не выводим
            $real_subcats = array();
            foreach($subcat_ids as $subcat_id){
                $w_in = $map[$url]['categories'];
                $w_in[] = (int)$subcat_id;
                $sc_res = $this->db
                    ->distinct()
                    ->select('shop_products_categories_link.product_id')
                    ->where_in('shop_products_categories_link.category_id', $w_in)
                    ->group_by('shop_products_categories_link.product_id')
                    ->having('COUNT(`shop_products_categories_link`.`category_id`)', 3, false)
                    ->get('shop_products_categories_link')->result_array();
                if(!empty($sc_res) && (count($sc_res) > 0)){
                    $real_subcats[$subcat_id] = count($sc_res);
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
            // подкатегорией (?subcategory=1234) и по этому параметру фильтровать товары для новинок и распродаж

            // если выбрана субкатегория – добавляем её в breadcrumbs
            if(!empty($subcategory) && in_array($subcategory, array_keys($real_subcats))){
                if(!empty($this->data['subcats'])){
                    foreach($this->data['subcats'] as $subcat){
                        if($subcat['extra_id'] == $subcategory){
                            $this->data['breadcrumbs'][] = array(
                                'title' => $subcat['title'],
                                'url' => base_url($url . '/?subcategory=' . $subcat['extra_id']),
                            );
                        }
                    }
                }
            }
        }
        $this->data['subcats_url'] = $url;
//        var_dump($this->data['subcats'], $this->data['subcats_url']);

        $this->data['page']['title'] = (!empty($map[$url]['page_title']))
            ? $map[$url]['page_title']
            :(
                (!empty($fake['title']))
                ? $this->buildTitle($fake['title'])
                : 'Распродажа в категории ' . $map[$url]['bc_maincat'] . $this->title_suffix
            );
        // настройки страницы
        $this->data['page']['meta_title'] = (!empty($map[$url]['page_title']))
            ? $map[$url]['page_title']
            :(
                (!empty($fake['title']))
                ? $this->buildTitle($fake['title'])
                : 'Распродажа в категории ' . $map[$url]['bc_maincat'] . $this->title_suffix
            );
        $this->data['page']['meta_description'] = (!empty($fake['meta_description']))
            ? $fake['meta_description']
            : 'Распродажа в категории ' . $map[$url]['bc_maincat'];
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

    public function by_one_category()
    {
        $map = array(
            'in-stock' => array(
                'category_id' => 1641,
                'categories' => array(1641),
                'bc_maincat' => 'В наличии',
                'fake_category' => 1641,
                'subcategories' => array(
                    0 => array(
                        'title' => 'Женщины',
                        'url' => 'in-stock-womens',
                    ),
                    1 => array(
                        'title' => 'Мужчины',
                        'url' => 'in-stock-mens',
                    ),
                    2 => array(
                        'title' => 'Дети',
                        'url' => 'in-stock-childrens',
                    ),
                ),
                'page_title' => 'Одежда, обувь, сумки, аксессуары в наличии Цены, купить в Киеве, Харькове, Днепропетровске, Одессе, Запорожье, Львове. Вещи в наличии: обзор, описание, продажа | Интернет-магазин Носи Это',
            ),
        );
        $url = end($this->uri->segment_array());
        if(!in_array($url, array_keys($map))) redirect(base_url());
        // описание для псевдо-категории
        $fake = $this->getFakeCategoryDescription($map[$url]['fake_category']);
        $this->data['category_description'] = $fake['description'];

        // учёт subcategory – если она указана в URL
        $subcategory = (int)$this->input->get('subcategory');
        $map_categories = $map[$url]['categories'];
        if(!empty($subcategory)){
            $map_categories[] = $subcategory;
        }
        $having_num = count($map_categories);

        $get_all = $this->db
            ->distinct()
            ->select('shop_products_categories_link.product_id')
//            ->where_in('category_id', $map[$url]['categories'])
            ->join('shop_products', 'shop_products.id = shop_products_categories_link.product_id')
            ->where_in('shop_products_categories_link.category_id', $map_categories)
            ->where('shop_products.show', 1)
            ->group_by('shop_products_categories_link.product_id')
//            ->having('COUNT(`category_id`)', 2, false)
            ->having('COUNT(`shop_products_categories_link`.`category_id`)', $having_num, false)
            ->get('shop_products_categories_link')->result_array();
        $total = count($get_all);

        $brands = $this->db
            ->distinct()
            ->select('shop_products.brand_id, categoryes.title')
            ->join('shop_products_categories_link', 'shop_products_categories_link.product_id = shop_products.id', 'left')
            ->join('categoryes', 'categoryes.id = shop_products.brand_id', 'left')
//            ->where_in('shop_products_categories_link.category_id', $map[$url]['categories'])
            ->where_in('shop_products_categories_link.category_id', $map_categories)
            ->where('shop_products.show', 1)
            ->where('categoryes.show', 1)
            ->group_by('shop_products_categories_link.product_id')
//            ->having('COUNT(`category_id`)', 2, false)
            ->having('COUNT(`category_id`)', $having_num, false)
            ->order_by('categoryes.title asc')
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
        $this->db->select('shop_products_categories_link.product_id');
        $this->db->select('shop_products.*'); // перечислить нужные поля для выборки
        $this->db->select("ROUND(shop_products.price*(IF(shop_products.currency='grn', " . $e_rates['grn_usd'] . ", IF(shop_products.currency='eur', " . $e_rates['eur_usd'] . ", 1))), 2) AS price_usd", FALSE); // новую проверку валюты и условие IF в запрос добавлять здесь
        $this->db->join('shop_products', 'shop_products.id = shop_products_categories_link.product_id');
//        $this->db->where_in('shop_products_categories_link.category_id', $map[$url]['categories']);
        $this->db->where_in('shop_products_categories_link.category_id', $map_categories);
        //->get('shop_products_categories_link')->result_array();

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
        $this->db->group_by('shop_products_categories_link.product_id');
//        $this->db->having('COUNT(`shop_products_categories_link`.`category_id`)', 2, false);
        $this->db->having('COUNT(`shop_products_categories_link`.`category_id`)', $having_num, false);

        $offset = ($page > 1) ? (($page - 1) * $per_page) : 0;
        $this->db->order_by($order_by . ' ' . $order_dir);
        $this->db->limit($per_page, $offset);
        $products = $this->db->get('shop_products_categories_link')->result_array(); // получаем товары

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
        $config['base_url'] = $config['first_url'] = base_url($url) . '/';
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
        $this->data['category_info']['url_structure']['url'] = $url;
        $this->data['category_info']['title'] = (!empty($fake['title']))
            ? $fake['title']
            : 'Распродажа в категории ' . $map[$url]['bc_maincat'];
        if(!empty($this->data['category_products'][0])){
            $type_id = $this->data['category_products'][0]['type_id'];
            $this->data['filters'] = $this->getOptionsList($type_id);
        }
        else if(!empty($_GET)) {
            // товаров нет из-за фильтров – в этом случае получаем фильтры и бренды
            // по одному любому товару этой категории, без учета фильтров
            $type_id = $this->getTypeByCategory($map[$url]['category_id']);
            $this->data['filters'] = $this->getOptionsList($type_id);
        }

        $main_cat_info = $this->getCatUrl($map[$url]['category_id']);
        $this->data['breadcrumbs'] = array(
            0 => array(
                'title' => 'Главная',
                'url' => base_url(),
            ),
            1 => array(
                'title' => $map[$url]['bc_maincat'],
                'url' => (!empty($main_cat_info['url'])) ? $main_cat_info['url'] : '',
            ),
//            2 => array(
//                'title' => (!empty($fake['title'])) ? $fake['title'] : 'Распродажа',
//                'url' => base_url($url),
//            ),
        );
        // определяем активный пункт верхнего меню первого уровня
        if(!empty($main_cat_info['id'])) {
            $url_ids = array($main_cat_info['id']);
            if (!empty($url_ids)) {
                // по этим ID получаем ID пунктов меню для этих крошек
                $menu_ids_res = $this->db
                    ->select('id')
                    ->where_in('extra_id', $url_ids)
                    ->get('categoryes')->result_array();
                if (!empty($menu_ids_res)) {
                    $menu_ids = array_keys(toolIndexArrayBy($menu_ids_res, 'id'));
                    $top_menu_ids = array_keys($this->top_menu[1]);
                    foreach ($menu_ids as $mid) {
                        if (in_array($mid, $top_menu_ids)) {
                            $this->tm_active_id = $mid;
                            break;
                        }
                    }
                }
            }
        }

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

        $this->data['subcategories'] = $map[$url]['subcategories'];

        $this->data['page']['title'] = (!empty($map[$url]['page_title']))
            ? $map[$url]['page_title']
            :(
                (!empty($fake['title']))
                ? $this->buildTitle($fake['title'])
                : 'Распродажа в категории ' . $map[$url]['bc_maincat'] . $this->title_suffix
            );
        // настройки страницы
        $this->data['page']['meta_title'] = (!empty($map[$url]['page_title']))
            ? $map[$url]['page_title']
            :(
                (!empty($fake['title']))
                ? $this->buildTitle($fake['title'])
                : 'Распродажа в категории ' . $map[$url]['bc_maincat'] . $this->title_suffix
            );
        $this->data['page']['meta_description'] = (!empty($fake['meta_description']))
            ? $fake['meta_description']
            : 'В наличии в категории ' . $map[$url]['bc_maincat'];
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

    public function getCatUrl($cat_id)
    {
        $output = array();
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
            $output = array(
                'id' => $res['structure_id'],
                'title' => $res['title'],
                'url' => base_url($res['url']),
            );
        }
        return $output;
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

    public function by_new()
    {
        $map = array(
            'womens-new' => array(
                'category_id' => 1638,
                'categories' => array(1638, 1617),
                'bc_maincat' => 'Женщины',
                'fake_category' => 1885,
            ),
            'mens-new' => array(
                'category_id' => 1639,
                'categories' => array(1639, 1617),
                'bc_maincat' => 'Мужчины',
                'fake_category' => 1885,
            ),
            'childrens-new' => array(
                'category_id' => 1640,
                'categories' => array(1640, 1617),
                'bc_maincat' => 'Дети',
                'fake_category' => 1885,
            ),
        );
        $url = end($this->uri->segment_array());
        if(!in_array($url, array_keys($map))) redirect(base_url());
        // описание для псевдо-категории
        $fake = $this->getFakeCategoryDescription($map[$url]['fake_category']);
        $this->data['category_description'] = $fake['description'];

        // учёт subcategory – если она указана в URL
        // если указана – то всё завязываем именно на эту подкатегорию, а не на основную из $map
        $subcategory = (int)$this->input->get('subcategory');
        $map_category = (!empty($subcategory)) ? $subcategory : $map[$url]['category_id'];

//        $new_ts = strtotime('-1 year'); // test
        $new_ts = strtotime('-1 month'); // real
        $get_all = $this->db
            ->distinct()
            ->select('shop_products_categories_link.product_id')
            ->join('shop_products', 'shop_products.id = shop_products_categories_link.product_id')
//            ->where('shop_products_categories_link.category_id', $map[$url]['category_id'])
            ->where('shop_products_categories_link.category_id', $map_category)
            ->where('shop_products.date_add >', $new_ts)
            ->where('shop_products.show', 1)
            ->get('shop_products_categories_link')->result_array();
        $total = count($get_all);

        $brands = $this->db
            ->distinct()
            ->select('shop_products.brand_id, categoryes.title')
            ->join('shop_products_categories_link', 'shop_products_categories_link.product_id = shop_products.id', 'left')
            ->join('categoryes', 'categoryes.id = shop_products.brand_id', 'left')
//            ->where('shop_products_categories_link.category_id', $map[$url]['category_id'])
            ->where('shop_products_categories_link.category_id', $map_category)
            ->where('shop_products.date_add >', $new_ts)
            ->where('shop_products.show', 1)
            ->where('categoryes.show', 1)
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
        $this->db->select('shop_products_categories_link.product_id');
        $this->db->select('shop_products.*'); // перечислить нужные поля для выборки
        $this->db->select("ROUND(shop_products.price*(IF(shop_products.currency='grn', " . $e_rates['grn_usd'] . ", IF(shop_products.currency='eur', " . $e_rates['eur_usd'] . ", 1))), 2) AS price_usd", FALSE); // новую проверку валюты и условие IF в запрос добавлять здесь
        $this->db->join('shop_products', 'shop_products.id = shop_products_categories_link.product_id');
//        $this->db->where('shop_products_categories_link.category_id', $map[$url]['category_id']);
        $this->db->where('shop_products_categories_link.category_id', $map_category);
        $this->db->where('shop_products.date_add >', $new_ts);

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
        $products = $this->db->get('shop_products_categories_link')->result_array(); // получаем товары

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
        $config['base_url'] = $config['first_url'] = base_url($url) . '/';
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
        $this->data['category_info']['url_structure']['url'] = $url;
        $this->data['category_info']['title'] = (!empty($fake['title']))
            ? $fake['title']
            : 'Новинки в категории ' . $map[$url]['bc_maincat'];
        if(!empty($this->data['category_products'][0])){
            $type_id = $this->data['category_products'][0]['type_id'];
            $this->data['filters'] = $this->getOptionsList($type_id);
        }
        else if(!empty($_GET)) {
            // товаров нет из-за фильтров – в этом случае получаем фильтры и бренды
            // по одному любому товару этой категории, без учета фильтров
            $type_id = $this->getTypeByCategory($map[$url]['category_id']);
            $this->data['filters'] = $this->getOptionsList($type_id);
        }

        $main_cat_info = $this->getCatUrl($map[$url]['category_id']);
        $this->data['breadcrumbs'] = array(
            0 => array(
                'title' => 'Главная',
                'url' => base_url(),
            ),
            1 => array(
                'title' => $map[$url]['bc_maincat'],
                'url' => (!empty($main_cat_info['url'])) ? $main_cat_info['url'] : '',
            ),
            2 => array(
                'title' => (!empty($fake['title'])) ? $fake['title'] : 'Новинки',
                'url' => base_url($url),
            ),
        );
        // определяем активный пункт верхнего меню первого уровня
        if(!empty($main_cat_info['id'])) {
            $url_ids = array($main_cat_info['id']);
            if (!empty($url_ids)) {
                // по этим ID получаем ID пунктов меню для этих крошек
                $menu_ids_res = $this->db
                    ->select('id')
                    ->where_in('extra_id', $url_ids)
                    ->get('categoryes')->result_array();
                if (!empty($menu_ids_res)) {
                    $menu_ids = array_keys(toolIndexArrayBy($menu_ids_res, 'id'));
                    $top_menu_ids = array_keys($this->top_menu[1]);
                    foreach ($menu_ids as $mid) {
                        if (in_array($mid, $top_menu_ids)) {
                            $this->tm_active_id = $mid;
                            break;
                        }
                    }
                }
            }
        }

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

        // проверяем наличие подкатегорий у основной категории
        // для вывода списка ссылок на подкатегории слева
        $subcat_ids = $this->get_subcats($map[$url]['category_id']);
        if(!empty($subcat_ids)){
            // проходим по подкатегориям, и проверяем, есть ли в БД (shop_products_categories_link) товары,
            // которые принадлежат к каждой подкатегории + имеют наш текущий бренд
            // те подкатегории, в которых есть хоть один товар, соответствующий критериям
            // подкатегория + бренд = оставляем, и выводим в боковое меню
            // остальные – удаляем и не выводим
            $real_subcats = array();
            foreach($subcat_ids as $subcat_id){
                $w_in = array($map[$url]['category_id']);
                $w_in[] = (int)$subcat_id;
                $sc_res = $this->db
                    ->distinct()
                    ->select('shop_products_categories_link.product_id')
                    ->join('shop_products', 'shop_products.id = shop_products_categories_link.product_id')
                    ->where_in('shop_products_categories_link.category_id', $w_in)
                    ->where('shop_products.date_add >', $new_ts)
                    ->where('shop_products.show', 1)
                    ->group_by('shop_products_categories_link.product_id')
                    ->having('COUNT(`shop_products_categories_link`.`category_id`)', 2, false)
                    ->get('shop_products_categories_link')->result_array();
                if(!empty($sc_res) && (count($sc_res) > 0)){
                    $real_subcats[$subcat_id] = count($sc_res);
                }
            }

            // получаем для оставшихся подкатегорий их пункты меню
            // формируем ссылки на эту подкатегорию + указанный в URL бренд для фильтрации товаров
            if(!empty($real_subcats)){
                $this->data['subcats'] = $this->db
                    ->distinct()
                    ->select('extra_id, title, url')
                    ->where_in('extra_id', array_keys($real_subcats))
                    ->where('extra_name', 'category_id')
                    ->order_by('title', 'asc')
                    ->get('url_structure')->result_array();
            }

            // для новинок и распродаж – этот момент нужно продумать
            // как вариант: выводить ссылки прямо на те же страницы новинок или распродаж, с указанной в URL
            // подкатегорией (?subcategory=1234) и по этому параметру фильтровать товары для новинок и распродаж

            // если выбрана субкатегория – добавляем её в breadcrumbs
            if(!empty($subcategory) && in_array($subcategory, array_keys($real_subcats))){
                if(!empty($this->data['subcats'])){
                    foreach($this->data['subcats'] as $subcat){
                        if($subcat['extra_id'] == $subcategory){
                            $this->data['breadcrumbs'][] = array(
                                'title' => $subcat['title'],
                                'url' => base_url($url . '/?subcategory=' . $subcat['extra_id']),
                            );
                        }
                    }
                }
            }
        }
        $this->data['subcats_url'] = $url;

        $this->data['page']['title'] = (!empty($fake['title']))
            ? $this->buildTitle($fake['title'])
            : 'Новинки в категории '
                . $map[$url]['bc_maincat'] . $this->title_suffix;
        // настройки страницы
        $this->data['page']['meta_title'] = (!empty($fake['title']))
            ? $this->buildTitle($fake['title'])
            : 'Новинки в категории ' . $map[$url]['bc_maincat'] . $this->title_suffix;
        $this->data['page']['meta_description'] = (!empty($fake['meta_description']))
            ? $fake['meta_description']
            : 'Новинки в категории ' . $map[$url]['bc_maincat'];
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

    /**
     * получаем «подкатегории» согласно структуре Меню - более правильный подход
     * @param int $category_id
     * @return array
     */
    public function getCategoryChilds2($category_id){
        $category_url_id = $this->db
            ->select('id')
            ->where(array(
                'extra_id' => $category_id,
                'extra_name' => 'category_id',
            ))
            ->limit(1)
            ->get('url_structure')->row()->id;

        $category_menu_id = $this->db
            ->select('id')
            ->where('extra_id', $category_url_id)
            ->limit(1)
            ->get('categoryes')->row()->id;
        $res = $this->db
            ->select('categoryes.title, url_structure.url')
            ->join('url_structure', 'url_structure.id = categoryes.extra_id', 'left')
            ->where(array(
                'categoryes.show' => 1,
                'categoryes.parent_id' => $category_menu_id,
            ))
            ->order_by('categoryes.order asc')
            ->get('categoryes')->result_array();
        return (!empty($res)) ? $res : array();
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
        return (!empty($return)) ? array_unique(explode(',', $return)) : array();
    }
} 