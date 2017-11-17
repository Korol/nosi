<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Pages
 * @property Base_model $base_model    Base Model Class
 * CI_DB_active_record|CI_DB_mysql_driver $db
 *
 */
class Order extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        // debug
        if ($this->input->ip_address() == '127.0.0.1') {
            $this->output->enable_profiler(true);
            error_reporting(E_ALL);
        }
//        $this->load->library('cart');
        $this->load->helper('form');
        $this->load->helper('array');
        $this->load->helper('email');
    }

    public function index()
    {
        $this->cart();
    }

    /**
     * страница корзины с товарами
     */
    public function cart()
    {
        $this->data['cart'] = $this->cart->contents();
        $this->data['cart_total'] = 0;
        $this->data['products'] = $this->data['colors_info'] = array();
        if(!empty($this->data['cart'])){
            $p_ids = $p_colors = array();
            foreach($this->data['cart'] as $dc_k => $dc_v){
                if($dc_v['options']['currency'] != $this->site_currency){
                    $this->data['cart'][$dc_k]['price'] = $this->convert_price($dc_v['price'], $dc_v['options']['currency']); // цена в валюте сайта
                }
                $this->data['cart_total'] += ($this->data['cart'][$dc_k]['price'] * $dc_v['qty']);
                $p_ids[] = $dc_v['id'];
                if(!empty($dc_v['options']['color'])){
                    $p_colors[$dc_v['id']] = $dc_v['options']['color'];
                }
            }
            if(!empty($p_ids)){
                // получаем доп.информацию о товарах
                $products = $this->db
                    ->select('shop_products.id, shop_products.title, shop_products.name, shop_products.code, shop_products.category_ids, uploads.file_name')
                    ->join('uploads', 'uploads.extra_id = shop_products.id')
                    ->where_in('shop_products.id', $p_ids)
                    ->where(array(
                        'shop_products.show' => 1,
                        'uploads.name' => 'product-photo',
                        'uploads.order' => 1,
                    ))
                    ->get('shop_products')->result_array();
                if(!empty($products)){
                    $products = toolIndexArrayBy($products, 'id');
                    $cat_ids = array();
                    foreach($products as $pk => $product){
                        $last_cat = end(explode(',', $product['category_ids']));
                        $cat_ids[] = $products[$pk]['last_cat'] = $last_cat;
                    }
                    $cat_ids = array_unique($cat_ids);
                    if(!empty($cat_ids)){
                        // получаем инфу о последних категориях товаров – для формирования URL товара
                        $categories = $this->db
                            ->select('categoryes.id, url_structure.url')
                            ->join('url_structure', 'url_structure.extra_id = categoryes.id')
                            ->where_in('categoryes.id', $cat_ids)
                            ->where(array(
                                'categoryes.show' => 1,
                                'url_structure.extra_name' => 'category_id'
                            ))
                            ->get('categoryes')->result_array();
                        $categories = (!empty($categories)) ? toolIndexArrayBy($categories, 'id') : array();
                    }
                    // данные в вид
                    foreach($products as $prod_k => $prod_val){
                        $this->data['products'][$prod_val['id']] = array(
                            'title' => $prod_val['title'],
                            'url' => (!empty($categories[$prod_val['last_cat']])) ? base_url($categories[$prod_val['last_cat']]['url'] . '/' . $prod_val['name'] . '-' . $prod_val['id'] . '.html') : '',
                            'file_name' => $prod_val['file_name'],
                            'code' => $prod_val['code'],
                        );
                    }
                }
            }
            // информация по выбранным цветам товаров – получаем для них картинки
            if(!empty($p_colors)){
                $pc_ids = array_values($p_colors);
                $colors_info = $this->db
                    ->select('uploads.extra_id, uploads.file_name')
                    ->where_in('uploads.id', $pc_ids)
                    ->get('uploads')->result_array();
                if(!empty($colors_info)){
                    $this->data['colors_info'] = toolIndexArrayBy($colors_info, 'extra_id');
                }
            }
        }
        $this->data['currency_mark'] = $this->currency_marks[$this->site_currency];
        // настройки страницы
        $this->data['page']['title'] = 'Корзина';
        $this->data['page']['meta_title'] = 'Корзина ваших покупок';
        $this->data['page']['meta_description'] = 'Корзина ваших покупок, оформление заказа';
        $this->data['view'] = (empty($this->is_mobile)) ? 'cart' : 'cart_mobile';
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    /**
     * оформление заказа
     */
    public function checkout()
    {
        $cart_content = $this->cart->contents();
        if(empty($cart_content)){
            redirect(base_url('order/cart'));
        }
        // информация о пользователе – если он авторизован
        $username = $this->session->userdata('username');
        $this->data['username'] = (!empty($username)) ? $username : '';
        $session_data = $this->session->all_userdata();
        // ошибки при оформлении заказа
        $this->data['checkout_error'] = $this->session->flashdata('checkout_error');
        // настройки страницы
        $this->data['page']['title'] = 'Оформление заказа';
        $this->data['page']['meta_title'] = 'Оформление вашего заказа';
        $this->data['page']['meta_description'] = 'Оформление заказа, ваши покупки.';
        $this->data['view'] = 'checkout';
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    public function checkout_action()
    {
//         error_reporting(1);
        $ts = time();
        // данные из формы Оформления заказа
        $post = $this->input->post(null, true);
        if(!empty($post)){
            foreach($post as $post_key => $post_value){
                $post[$post_key] = trim($post_value);
            }
        }

        // данные из сессии
        $session_data = $this->session->all_userdata();
        $cart_contents = $this->cart->contents();
        $cart_total = 0;
        $currency = 'usd'; // usd
        $currency_mark = '$';
        // новые направления конвертации для новых валют в системе добавлять здесь:
        $this->e_rates = array('grn_usd' => 1, 'eur_usd' => 1, 'usd_grn' => 1, 'eur_grn' => 1, 'usd_eur' => 1, 'grn_eur' => 1);
        // получаем курсы валют согласно направлениям конвертации
        $e_rates_db = $this->db->select('var_name, value')->get('e_rates')->result_array();
        if(!empty($e_rates_db)){
            foreach($e_rates_db as $e_rate){
                $this->e_rates[$e_rate['var_name']] = str_replace(',', '.', $e_rate['value']);
            }
        }
        if(!empty($cart_contents)){
            // поправим цены, если какой-то товар не в валюте $currency
            foreach($cart_contents as $ck => $cv){
                if($cv['options']['currency'] != $currency){
                    // пока корректируем только общую стоимость корзины
                    $cart_total += (ceil($cv['price'] * $this->e_rates[$cv['options']['currency'] . '_' . $currency]) * $cv['qty']);
                }
                else{
                    $cart_total += ($cv['price'] * $cv['qty']);
                }
            }
        }
        else{
            // корзина пуста
            redirect(base_url('order/cart'));
        }

        // данные о заказчике, которые нужны в shop_orders
        // если заказчик авторизован – извлекаем данные из БД
        // если не авторизован – то авторизуем и извлекаем его данные из БД
        // если заказчик не зарегистрирован – то регистрируем его, авторизуем, и извлекаем данные из БД:
        // user_id, name, phone, email
        $user = array();
        if(!empty($session_data['user_id'])){
            // пользователь авторизован
            $user = $this->db
                ->select('id, first_name, phone, email')
                ->where('id', $session_data['user_id'])
                ->get('users')->row_array();
        }
        elseif(!empty($post['user_type']) && !empty($post['user_email']) && !empty($post['user_password'])){
            // постоянный клиент - логиним юзера и извлекаем информацию о клиенте
            if($this->ion_auth->login($post['user_email'], $post['user_password'])){
                // информация о клиенте
                $user_info = $this->ion_auth->user()->row_array();
                if(!empty($user_info)){
                    $user = array(
                        'id' => $user_info['id'],
                        'first_name' => trim($user_info['first_name'] . ' ' . $user_info['last_name']),
                        'phone' => $user_info['phone'],
                        'email' => $user_info['email'],
                    );
                }
            }
            else{
                // что-то пошло не так – возвращаем на страницу оформления с просьбой внимательно заполнить все поля формы
                $this->session->set_flashdata('checkout_error', 'Вы указали неверный email и/или пароль!<br/>Пожалуйста, внимательно заполните все поля формы – и повторите попытку.');
                $this->session->set_flashdata('checkout_form', $post);
                redirect(base_url('order/checkout'));
            }
        }
        elseif(!empty($post['new_email']) && !empty($post['new_password']) && !empty($post['new_fio']) && !empty($post['new_phone']) && valid_email($post['new_email'])){
            // новый покупатель – регистрируем его, потом оформляем заказ
            $additional_data = array(
                'first_name' => $post['new_fio'],
                'phone' => $post['new_phone'],
            );
            $group = array('2');
            // регистрируем
            if($this->ion_auth->register($post['new_email'], $post['new_password'], $post['new_email'], $additional_data, $group)){
                // логиним
                if($this->ion_auth->login($post['new_email'], $post['new_password'])) {
                    // информация о клиенте
                    $user_info = $this->ion_auth->user()->row_array();
                    if (!empty($user_info)) {
                        $user = array(
                            'id' => $user_info['id'],
                            'first_name' => $user_info['first_name'],
                            'phone' => $user_info['phone'],
                            'email' => $user_info['email'],
                        );
                    }
                }
            }
            else{
                // что-то пошло не так – возвращаем на страницу оформления с просьбой внимательно заполнить все поля формы
                $this->session->set_flashdata('checkout_error', 'Вы указали неверные данные для регистрации нового пользователя!<br/>Пожалуйста, внимательно заполните все поля формы – и повторите попытку.');
                $this->session->set_flashdata('checkout_form', $post);
                redirect(base_url('order/checkout'));
            }
        }
        else{
            // что-то пошло не так – возвращаем на страницу оформления с просьбой внимательно заполнить все поля формы
            $this->session->set_flashdata('checkout_error', 'Вы указали не всю необходимую для оформления заказа контактную информацию!<br/>Пожалуйста, внимательно заполните все поля формы – и повторите попытку.');
            $this->session->set_flashdata('checkout_form', $post);
            redirect(base_url('order/checkout'));
        }

        // данные о заказе (информация из cart), которые нужны в shop_orders (все цены и суммы – в родной валюте товара)
        // basket, total_amount, total_amount_with_discount, discount(?), notes(комментарий к заказу), status(submited),date_add(timestamp), date_update(timestamp)
        // basket:
        // 'id' => string '31' (length=2) - ID корзины из shop_carts (сейчас не используется)
        // 'user_id' => string '0' (length=1) - ID покупателя (пользователя)
        // 'order_id' => int 4141
        // products (массив информации о товарах в корзине), для каждого товара в корзине:
            // main_picture_file_name - uploads.file_name для картинки товара с uploads.order = 1
            // main_picture_file_path - uploads.file_path
            // main_picture_image_size - uploads.image_size
            // shop_products.* - вся инфа о товаре из таблицы shop_products
            // quantity - количество единиц этого товара в корзине
            // 'color' => string 'uploads/shop/products/thumbs2/0_258721c42ddc747964ff9c2bf029b7ed.jpg' - ссылка на картинку-цвет (если указан цвет)
            // 'size' => boolean false - размер (если указан)
            // 'dimensions' => string '' (length=0) - габариты покупателя (если указаны)
            // 'price_hmn' => string '140.00 $' (length=8) - цена единицы товара + знак валюты (в родной валюте товара)
            // 'price_total' => int 140 - общая стоимость всех единиц товара (цена * количество единиц товара) (в родной валюте товара)
            // 'price_total_hmn' => string '140.00 $' (length=8) - общая стоимость всех единиц товара (цена * количество единиц товара) + знак валюты (в родной валюте товара)
        // 'total_amount' => int 140 - общая стоимость всех товаров в корзине (в родной валюте товара)
        // 'date_add' => string '1476798564' (length=10) - timestamp добавления заказа
        // 'total_amount_hmn' => string '140.00 $' (length=8) - общая стоимость всех товаров в корзине + знак валюты (в родной валюте товара)
        $basket_products = array();
        if(!empty($cart_contents)){
            $p_ids = $c_ids = array();
            $cart_contents = toolIndexArrayBy($cart_contents, 'id');
            foreach($cart_contents as $cart_row){
                $p_ids[] = $cart_row['id'];
                if(!empty($cart_row['options']['color'])){
                    $c_ids[] = $cart_row['options']['color'];
                }
            }
            // получаем картинки-цвета товаров, если есть
            if(!empty($c_ids)){
                $colors = $this->db
                    ->select('id, file_name')
                    ->where_in('id', $c_ids)
                    ->get('uploads')->result_array();
                $colors = (!empty($colors)) ? toolIndexArrayBy($colors, 'id') : array();
            }
            // получаем товары
            if(!empty($p_ids)){
                $products = $this->db
                    ->where_in('id', $p_ids)
                    ->get('shop_products')->result_array();
                $images = $this->db
                    ->select('extra_id, file_name, file_path, image_size')
                    ->where_in('extra_id', $p_ids)
                    ->where(array(
                        'name' => 'product-photo',
                        'order' => 1,
                    ))
                    ->get('uploads')->result_array();
                $images = (!empty($images)) ? toolIndexArrayBy($images, 'extra_id') : array();
                if(!empty($products)){
                    foreach($products as $pk => $pv){
                        $new_p_key = $pv['id'] . ':' . $images[$pv['id']]['file_name'] . ':' . $cart_contents[$pv['id']]['options']['size'];
                        $basket_products[$new_p_key] = $pv;
                        $basket_products[$new_p_key]['main_picture_file_name'] = $images[$pv['id']]['file_name'];
                        $basket_products[$new_p_key]['main_picture_file_path'] = $images[$pv['id']]['file_path'];
                        $basket_products[$new_p_key]['main_picture_image_size'] = $images[$pv['id']]['image_size'];
                        $basket_products[$new_p_key]['quantity'] = $cart_contents[$pv['id']]['qty'];
                        $basket_products[$new_p_key]['color'] = (!empty($cart_contents[$pv['id']]['options']['color'])
                            && !empty($colors[$cart_contents[$pv['id']]['options']['color']]))
                            ? '/uploads/shop/products/thumbs3/' . $colors[$cart_contents[$pv['id']]['options']['color']]['file_name']
                            : '';
                        $basket_products[$new_p_key]['size'] = $cart_contents[$pv['id']]['options']['size'];
                        $basket_products[$new_p_key]['dimensions'] = '';
                        // если валюта товара не соответствует валюте корзины $currency - тогда конвертируем цены в валюту $currency
                        $basket_products[$new_p_key]['price'] = ($pv['currency'] != $currency)
                            ? (ceil($pv['price'] * $this->e_rates[$pv['currency'] . '_' . $currency]))
                            : $pv['price'];
                        $basket_products[$new_p_key]['price_hmn'] = $basket_products[$new_p_key]['price'] . ' ' . $currency_mark;
                        $basket_products[$new_p_key]['price_total'] = ($basket_products[$new_p_key]['price'] * $cart_contents[$pv['id']]['qty']);
                        $basket_products[$new_p_key]['price_total_hmn'] = $basket_products[$new_p_key]['price_total'] . ' ' . $currency_mark;
                    }
                }
            }
        }
        if(empty($basket_products)){
            // корзина пуста
            return;
        }
        $basket = array(
            'id' => 31,
            'user_id' => $user['id'],
            'order_id' => 0,
            'total_amount' => $cart_total,
            'total_amount_hmn' => $cart_total . ' ' . $currency_mark,
            'date_add' => $ts,
            'products' => $basket_products,
        );

        // параметры доставки, в зависимости от выбранного способа доставки:
        // delivery_method, delivery_country, delivery_address, delivery_city, delivery_storage, delivery_name(название службы доставки)
        $delivery_method = 0;
        $delivery_country = $delivery_address = $delivery_city = $delivery_storage = $delivery_name = '';
        if($post['delivery_method'] == 'curier'){
            $delivery_method = 1;
            $delivery_country = 'Украина';
            $delivery_city = element('delivery_city', $post, '');
            $delivery_address = element('delivery_address', $post, '');
        }
        elseif($post['delivery_method'] == 'company'){
            $delivery_method = 2;
            $delivery_country = 'Украина';
            $delivery_city = element('delivery_city', $post, '');
            $delivery_address = element('delivery_address', $post, '');
            $delivery_name = element('company_name', $post, '');
            $delivery_storage = element('delivery_department', $post, '');
        }
        elseif($post['delivery_method'] == 'country'){
            $delivery_method = 3;
            $delivery_country = element('country_name', $post, '');
            $delivery_city = element('delivery_city', $post, '');
            $delivery_address = element('delivery_address', $post, '');
        }

        // проверка основных параметров доставки: способ, страна, город
        if(empty($delivery_method) || empty($delivery_country) || empty($delivery_city)){
            // что-то пошло не так – возвращаем на страницу оформления с просьбой внимательно заполнить все поля формы
            $this->session->set_flashdata('checkout_error', 'Вы указали не все необходимые для оформления заказа параметры доставки!<br/>Пожалуйста, внимательно заполните все поля формы – и повторите попытку.');
            $this->session->set_flashdata('checkout_form', $post);
            redirect(base_url('order/checkout'));
        }

        // собираем вместе данные, которые нужны для БД (shop_orders)
        $discount = array(
            'delivery' => 25,
            'delivery_hmn' => '25 $',
            'price_total' => $cart_total,
            'price' => $cart_total,
            'price_total_hmn' => $cart_total . ' ' . $currency_mark,
            'price_hmn' => $cart_total . ' ' . $currency_mark,
            'difference' => '0.00',
            'difference_hmn' => '0.00 ' . $currency_mark,
            'discounts' => array(),
        );
        $insert = array(
            'user_id' => $user['id'],
            'name' => $user['first_name'],
            'phone' => ($user['phone']) ? $user['phone'] : '',
            'email' => $user['email'],
            'delivery_method' => $delivery_method,
            'delivery_country' => $delivery_country,
            'delivery_address' => $delivery_address,
            'delivery_city' => $delivery_city,
            'delivery_storage' => $delivery_storage,
            'delivery_name' => $delivery_name,
            'notes' => $post['notes'],
            'status' => 'submited',
            'date_add' => $ts,
            'date_update' => $ts,
            'status_history' => 'submited:' . $ts . ':' . $user['id'],
            'total_amount' => $cart_total,
            'total_amount_with_discount' => $cart_total,
            'discount' => json_encode($discount),
            'basket' => json_encode($basket),
        );

        // если всё ОК – сохраняем информацию о заказе в БД, и вызываем $this->thanks()
        if($this->db->insert('shop_orders', $insert)){
            $last_id = $this->db->insert_id();
            if(!empty($last_id)){
                // добавляем в поле basket значение для order_id
                $basket = $this->db
                    ->select('basket')
                    ->where('id', $last_id)
                    ->get('shop_orders')->row()->basket;
                if(!empty($basket)){
                    $basket = json_decode($basket, true);
                    $basket['order_id'] = $last_id;
                    $update = array(
                        'basket' => json_encode($basket),
                    );
                    $this->db->update('shop_orders', $update, array('id' => $last_id));
                }
                // очищаем корзину
                $this->cart->destroy();
                // отправляем письма администраторам и клиенту
                $this->send_emails($last_id, $insert);
                // редирект на страницу благодарности
                redirect(base_url('order/thanks/' . $last_id));
            }
        }
        else{
            // что-то пошло не так – возвращаем на страницу оформления с просьбой внимательно заполнить все поля формы
            $this->session->set_flashdata('checkout_error', 'Произошла ошибка при добавлении информации о заказе в БД сайта!<br/>' . $this->db->last_query());// . $this->db->last_query()
            $this->session->set_flashdata('checkout_form', $post);
            redirect(base_url('order/checkout'));
        }
    }

    public function send_emails($order_id, $data)
    {
        // способы доставки
        $delivery_methods = array(
            1 => 'Доставка по Киеву',
            2 => 'Транспортная компания по Украине',
            3 => 'Международная доставка',
        );

        // информация о товарах
        $email_html_products = '<br/><table cellspacing="0" cellpadding="5" border="1" align="center">
            <tr>
                <th><strong>Товар</strong></th>
                <th><strong>Наименование</strong></th>
                <th><strong>Количество</strong></th>
                <th><strong>Цена</strong></th>
            </tr>';
        if(!empty($data['basket'])){
            $basket = json_decode($data['basket'], true);
            if(!empty($basket['products'])){
                $currency = 'grn'; // в email суммы в грн
                $currency_mark = 'грн'; // в email суммы в грн
                $bt = 0;// в email суммы в грн
                foreach($basket['products'] as $prod){
                    $orig = $this->db // в email суммы в грн start
                        ->select('price, currency')
                        ->get_where('shop_products', array('id' => $prod['id']))->row_array();
                    if(!empty($orig)){
                        if($orig['currency'] != $currency){
                            $pprice = ceil($orig['price'] * $this->e_rates[$orig['currency'] . '_grn']); // в email суммы в грн
                            $prod['price_hmn'] = $pprice . ' ' . $currency_mark;
                            $prod['price_total_hmn'] = ($pprice * $prod['quantity']) . $currency_mark;
                            $bt += ($pprice * $prod['quantity']);
                        }
                        else{
                            $prod['price_hmn'] = $orig['price'] . ' ' . $currency_mark;
                            $prod['price_total_hmn'] = ($orig['price'] * $prod['quantity']) . $currency_mark;
                            $bt += ($orig['price'] * $prod['quantity']);
                        }
                    } // в email суммы в грн end
                    $email_html_products .= '<tr>';
                    $email_html_products .= '<td><img src="' . ((!empty($prod['color'])) ? HTTP_HOST . ltrim($prod['color'], '/') : HTTP_HOST . 'uploads/shop/products/thumbs3/' . $prod['main_picture_file_name']) . '"/></td>';
                    $email_html_products .= '<td><a href="' . $this->build_product_url($prod['id'], $prod['category_ids'], $prod['name']) . '">' . $prod['title'] . '</a>';
                    $email_html_products .= (!empty($prod['size'])) ? '<br/>Размер: ' . $prod['size'] . '</td>' : '</td>';
                    $email_html_products .= '<td align="center">' . $prod['quantity'] . '</td>';
                    $email_html_products .= '<td nowrap>' . $prod['price_hmn'] . ' * ' . $prod['quantity'] . ' = ' . $prod['price_total_hmn'] . '</td>';
                    $email_html_products .= '</tr>';
                }
                $basket['total_amount_hmn'] = $bt . ' ' . $currency_mark; // в email суммы в грн
                $email_html_products .= '<tr><td colspan="4"><b>Итого:</b> ' . $basket['total_amount_hmn'] . '</td></tr>';
            }
        }
        $email_html_products .= '</table>';

        // информация о клиенте и доставке
        $email_html_order_info =  "<p><strong>Номер заказа:</strong> {$order_id}</p>
            <p><strong>ID пользователя в системе:</strong> {$basket['user_id']}</p>
            <p><strong>ФИО:</strong> {$data['name']}</p>
            <p><strong>E-mail:</strong> {$data['email']}</p>
            <p><strong>Телефон:</strong> {$data['phone']}</p>
            <p><strong>Способ доставки:</strong> {$delivery_methods[$data['delivery_method']]}</p>
            <p><strong>Страна доставки:</strong> {$data['delivery_country']}</p>
            <p><strong>Город доставки:</strong> {$data['delivery_city']}</p>
            <p><strong>Адрес доставки:</strong> {$data['delivery_address']}</p>";
        if($data['delivery_method'] == 2){
            $email_html_order_info .= "<p><strong>Компания доставки:</strong> {$data['delivery_name']}</p>
                <p><strong>Отделение доставки:</strong> {$data['delivery_storage']}</p>";
        }
        $email_html_order_info .= (!empty($data['notes'])) ? "<p><strong>Комментарии к заказу:</strong> {$data['notes']}</p>" : "";

        // получаем список всех администраторов
        $users_res = $this->db
            ->select("users.id, users.username, users.email, users.first_name, users.last_name")
            ->join("users_groups", "users_groups.user_id = users.id && users_groups.group_id = 1")
            ->group_by("users.email")
            ->get_where("users", array(
                "active" => 1
            ))
            ->result();

        // отправка писем
        $this->load->library("email");
        // письма администрации
        $user_ip = $this->input->ip_address(); // локально – только мне
        foreach ($users_res AS $r) {
            if(($user_ip == '127.0.0.1') && ($r->email != 'andkorol.reg@gmail.com')){
                continue;
            }
            $this->email->from($this->config->config['email_from'], $this->config->config['email_from_name']);
            $this->email->to($r->email, trim($r->first_name . " " . $r->last_name));
            $this->email->subject("Новый заказ №" . $order_id);
            $this->email->message($email_html_order_info . $email_html_products);
            $this->email->send();
//            var_dump($r->email);
//            print $this->email->print_debugger();
        }
        //письмо клиенту
        $this->email->from($this->config->config['email_from'], $this->config->config['email_from_name']);
        $this->email->to($data['email']);
        $this->email->subject("Новый заказ №" . $order_id);
        $this->email->message($email_html_order_info . $email_html_products);
        $this->email->send();

//        var_dump($data['email']);
//        print $this->email->print_debugger();
//        echo $email_html_order_info, $email_html_products;
//        exit('<br/><br/>Done!');
    }

    /**
     * строим URL для товара по последней категории в поле category_ids товара
     * @param $id - товар
     * @param $cat_ids - категории товара, перечислены через запятую
     * @param $name - URL-slug товара
     * @return string
     */
    public function build_product_url($id, $cat_ids, $name)
    {
        $last_cat = end(explode(',', $cat_ids));
        $cat_url = $this->db
            ->select('url')
            ->where(array(
                'extra_id' => $last_cat,
                'extra_name' => 'category_id'
            ))
            ->limit(1)
            ->get('url_structure')->row()->url;
        return base_url($cat_url . $name . '-' . $id . '.html');
    }

    /**
     * страницы благодарности за заказ
     */
    public function thanks($order_id = 0)
    {
        $this->data['order_id'] = $order_id;
        // настройки страницы
        $this->data['page']['title'] = 'Ваш заказ оформлен';
        $this->data['page']['meta_title'] = 'Ваш заказ успешно оформлен и принят в обработку';
        $this->data['page']['meta_description'] = 'Ваш заказ успешно оформлен и принят в обработку';
        $this->data['view'] = 'thanks';
        $this->data['fb_pixel_event'] = "fbq('track', 'Purchase', {value: '0.00', currency: 'USD'});\n";
        $this->load->vars($this->data);
        $this->load->view($this->view_container);
    }

    /**
     * добавление товара в корзину
     */
    public function add_to_cart()
    {
        $this->output->enable_profiler(false);
        $product_id = $this->input->post('product_id', true);
        $color = $this->input->post('color', true);
        $size = $this->input->post('size', true);
        $product = $this->db
            ->select('price, currency')
            ->where('id', $product_id)
            ->get('shop_products')->row_array();
        if(empty($product)) return false;

        $cart_data = array(
            'id' => $product_id,
            'qty' => 1,
            'price' => $product['price'],
            'name' => 'ProductID:' . $product_id,
            'options' => array(
                'color' => $color,
                'size' => $size,
                'currency' => $product['currency']
            ),
        );
        $this->cart->insert($cart_data);
        $total_items = $this->cart->total_items();
        $total_price = $this->cart->total();

        echo json_encode(array('total_items' => $total_items, 'total_price' => $total_price));
    }

    /**
     * отображение корзины в шапке сайта
     */
    public function get_cart()
    {
        $this->output->enable_profiler(false);
        echo $this->display_cart();
    }

    /**
     * удаление товара из корзины
     */
    public function del_from_cart()
    {
        $return = array(
            'product_id' => 0,
            'total_price' => 0,
            'items_in_cart' => 0,
        );
        $this->output->enable_profiler(false);
        $product_id = $this->input->post('product_id', true);
        $cart = $this->cart->contents();
        $total_price = 0;
        if(!empty($cart)){
            foreach($cart as $cart_item){
                if($cart_item['id'] == $product_id){
                    $update = array(
                        'rowid' => $cart_item['rowid'],
                        'qty'   => 0,
                    );
                }
                else{
                    if($cart_item['options']['currency'] != $this->site_currency){
                        $total_price += ($this->convert_price($cart_item['price'], $cart_item['options']['currency']) * $cart_item['qty']);
                    }
                    else{
                        $total_price += ($cart_item['price'] * $cart_item['qty']);
                    }
                }
            }
            if(!empty($update)){
                $this->cart->update($update);
                $return = array(
                    'product_id' => $product_id,
                    'total_price' => $total_price,
                    'items_in_cart' => $this->cart->total_items(),
                );
            }
        }
        echo json_encode($return);
    }

    /**
     * меняем количество товара в корзине
     */
    public function cart_qty()
    {
        $this->output->enable_profiler(false);
        $product_id = $this->input->post('product_id', true);
        $operator = $this->input->post('operator', true);
        if(empty($product_id) || !in_array($operator, array('plus', 'minus'))) return false;

        $cart = $this->cart->contents();
        if(!empty($cart)) {
            foreach ($cart as $cart_item) {
                if ($cart_item['id'] == $product_id) {
                    $update = array(
                        'rowid' => $cart_item['rowid'],
                        'qty' => (($operator == 'plus') ? ($cart_item['qty'] + 1) : ($cart_item['qty'] - 1)),
                    );
                }
            }
            if(!empty($update)) {
                $this->cart->update($update);
            }
        }
        return true;
    }
}