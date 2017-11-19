<?php
include_once("./modules/shop/shop.helper.php");

class actionModule extends shopModuleHelper
{
    public $bm;
    public $action = 'action';
    public $action_product = 'action_product';
    public $data;
    public $per_page = 25;
    public $action_id = 1;

    function __construct()
    {
        parent::__construct();
        $this->ci->load->model('base_model', 'bm');
        $this->ci->load->helper('functions_helper');
        $this->bm = $this->ci->bm;
        if($this->ci->input->ip_address() == '127.0.0.1'){
            $this->ci->output->enable_profiler(true);
            error_reporting(1);
        }
    }

    /**
     * базовое отображение страницы
     */
    public function setup()
    {
        // категории
        $this->data['selected_cat'] = (!empty($_GET['category_id'])) ? $_GET['category_id'] : 0;
        $this->data['categories'] = $this->cats_options_list();
        $this->data['action'] = $this->bm->get_by_id($this->action, $this->action_id);
        $this->data['get'] = $this->ci->input->get(null, true);
        $this->data['action_products'] = $this->get_action_participants();
        $this->data['flash'] = $this->ci->session->flashdata('action_success');
        // товары
        $page = (!empty($_GET['page'])) ? (int)$_GET['page'] : 1;
        if(!empty($this->data['selected_cat']) && empty($_GET['show'])){
            // постраничный показ товаров выбранной категории
            $this->get_category_products($this->data['selected_cat'], $page);
        }
        elseif (!empty($_GET['show']) && ($_GET['show'] == 'all')){
            // постраничный показ товаров, которые участвуют в акции
            $this->get_action_products($this->action_id, $page);
        }
        // постраничная навигация
        $this->setPagination();

        $this->ci->load->adminView("action/setup", $this->data);
    }

    /**
     * получаем всех участников акции
     * результат индексируем по product_id
     * @return mixed
     */
    public function get_action_participants()
    {
        $action_products = $this->db
            ->where(array(
                'action_id' => $this->action_id,
            ))
            ->get($this->action_product)->result_array();
        return (!empty($action_products)) ? toolIndexArrayBy($action_products, 'product_id') : array();
    }

    /**
     * товары из выбранной категории
     * @param $category_id
     * @param $page
     */
    public function get_category_products($category_id, $page)
    {
        $query = $this->db
            ->select('pc.*, p.*')
            ->from('shop_products_categories_link as pc')
            ->join('shop_products as p', 'p.id = pc.product_id')
            ->where(array(
                'pc.category_id' => $category_id,
                'p.show' => 1,
            ));
        $query_cnt = clone $query;
        $this->data['total_products'] = $query_cnt->count_all_results();

        $this->data['products'] = $query->order_by('p.id DESC')
            ->limit($this->per_page, (($page -1) * $this->per_page))
            ->get()->result_array();
        $this->data['data_type'] = 'category';
    }

    /**
     * товары, участвующие в акции
     * @param $action_id
     * @param $page
     */
    public function get_action_products($action_id, $page)
    {
        $query = $this->db
            ->select('ap.product_id, p.*')
            ->from('action_product as ap')
            ->join('shop_products as p', 'p.id = ap.product_id')
            ->where(array(
                'ap.action_id' => $action_id,
            ));
        $query_cnt = clone $query;
        $this->data['total_products'] = $query_cnt->count_all_results();

        $this->data['products'] = $query->order_by('p.id DESC')
            ->limit($this->per_page, (($page -1) * $this->per_page))
            ->get()->result_array();
        $this->data['data_type'] = 'action';
    }

    /**
     * настройка постраничной навигации
     */
    public function setPagination()
    {
        $get = $this->ci->input->get(null, true);
        unset($get['page']);
        $this->ci->load->library('pagination');
        $this->ci->config->load('pg', true);
        $config = $this->ci->config->item('pg', 'pg');
        unset($config['page_query_string'], $config['query_string_segment']);
        $config['base_url'] = base_url('admin') . '/?' . http_build_query($get);
        $config['total_rows'] = $this->data['total_products'];
        $config['per_page'] = $this->per_page;
        $config['num_links'] = 3;
        $config['use_page_numbers'] = TRUE;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $this->ci->pagination->initialize($config);
        $this->data['pagination'] = $this->ci->pagination->create_links();
    }

    /**
     * сохраняем данные по акции
     */
    public function save_action()
    {
        $this->ci->output->enable_profiler(false);
        $title = $this->ci->input->post('title', true);
        $from = $this->ci->input->post('from', true);
        $to = $this->ci->input->post('to', true);
        $percent = $this->ci->input->post('percent', true);
        $active = $this->ci->input->post('active', true);
        $return = 0;
        if(!empty($title)
            && !empty($from)
            && !empty($to)
            && (!empty($percent) && ((int)$percent < 100))
        ){
            $data = array(
                'title' => $title,
                'start' => $from . ':00',
                'end' => $to . ':59',
                'percent' => (int)trim(str_replace('%', '', $percent)),
                'active' => $active,
            );
            $check = $this->bm->count_rows($this->action, array('id' => 1));
            if(!empty($check)){
                $return = $this->bm->update_item($this->action, $data, array('id' => 1));
            }
            else{
                $return = $this->bm->insert_item($this->action, $data);
            }
        }
        echo $return;
    }

    /**
     * обработка множественного выбора
     */
    public function batch()
    {
        $return = 0;
        $this->ci->output->enable_profiler(false);
        $action = $this->ci->input->post('action', true);
        $ids = $this->ci->input->post('ids', true);
        $percents = $this->ci->input->post('percents', true);
        $category_id = $this->ci->input->post('category_id', true);
        $ids_arr = explode(',', $ids);
        if(empty($action) || empty($ids_arr)){
            echo $return;
            return;
        }

        switch ((int)$action){
            case 1:
                $return = $this->add_to_action($ids_arr);
                break;
            case 2:
                $return = $this->remove_from_action($ids_arr);
                break;
            case 3:
                $return = $this->add_to_action($ids_arr);
                break;
            case 4:
                $return = $this->set_personal_percents($percents);
                break;
            case 5:
                $return = $this->add_category_to_action($category_id);
                break;
            case 6:
                $return = $this->remove_category_from_action($category_id);
                break;
            case 7:
                $return = $this->clear_action();
                break;
        }
        if($return > 0){
            $this->ci->session->set_flashdata('action_success', 'Операция выполнена успешно!');
        }
        echo $return;
    }

    /**
     * добавляем товары в акцию по списку их ID
     * устанавливаем общую акционную скидку
     * @param $ids
     * @return int
     */
    public function add_to_action($ids)
    {
        $action = $this->bm->get_by_id($this->action, $this->action_id);
        if(!empty($action['percent'])) {
            $this->remove_from_action($ids); // удаляем старые записи с такими ID товаров
            $data = array();
            foreach ($ids as $id){
                $data[] = array(
                    'action_id' => $this->action_id,
                    'product_id' => $id,
                    'percent' => $action['percent'],
                );
            }
            $this->db->insert_batch($this->action_product, $data);
        }
        return 1;
    }

    /**
     * удаление товаров из акции по списку ID
     * @param $ids
     * @return int
     */
    public function remove_from_action($ids)
    {
        $this->db->where('action_id', $this->action_id);
        $this->db->where_in('product_id', $ids);
        $this->db->delete($this->action_product);
        return 1;
    }

    /**
     * установка персональных процентов для товаров
     * из таблицы на странице
     * @param $percents
     * @return int
     */
    public function set_personal_percents($percents)
    {
        if(!empty($percents)){
            $p_ex = explode(',', $percents);
            if(!empty($p_ex)){
                foreach ($p_ex as $p_ex_item){
                    // данные имеют вид: 23342_15 (IDтовара_процент)
                    $pei_ex = explode('_', $p_ex_item);
                    if((sizeof($pei_ex) == 2) && (!empty((int)$pei_ex[0]) && !empty((int)$pei_ex[1]))){
                        // если ID товара и % больше 0 – работаем
                        // проверяем, есть ли такая запись в БД
                        $check = $this->db
                            ->where(array(
                                'action_id' => $this->action_id,
                                'product_id' => (int)$pei_ex[0]
                            ))
                            ->get($this->action_product)->row_array();
                        if(!empty($check['id'])){
                            // запись с таким товаром в этой акции уже есть – обновляем %
                            $this->db->update(
                                $this->action_product,
                                array('percent' => $pei_ex[1]),
                                array('id' => $check['id'])
                            );
                        }
                        else{
                            // такой записи нет
                            // добавляем товар в акцию с указанной персональной скидкой
                            $data = array(
                                'product_id' => $pei_ex[0],
                                'action_id' => $this->action_id,
                                'percent' => $pei_ex[1],
                            );
                            $this->db->insert($this->action_product, $data);
                        }
                    }
                }
            }
        }
        return 1;
    }

    /**
     * добавление всех товаров категории в акцию
     * @param $category_id
     * @return int
     */
    public function add_category_to_action($category_id)
    {
        $products = $this->db
            ->select('pc.*')
            ->from('shop_products_categories_link as pc')
            ->join('shop_products as p', 'p.id = pc.product_id')
            ->where(array(
                'pc.category_id' => $category_id,
                'p.show' => 1,
            ))
            ->get()->result_array();
        if(!empty($products)){
            $action = $this->bm->get_by_id($this->action, $this->action_id);
            $products_chunks = array_chunk($products, 100);
            foreach ($products_chunks as $products_chunk){
                $data = array();
                $delete_ids = array();
                foreach ($products_chunk as $product){
                    $delete_ids[] = $product['product_id'];
                    $data[] = array(
                        'action_id' => $this->action_id,
                        'product_id' => $product['product_id'],
                        'percent' => $action['percent'],
                    );
                }
                // удаляем товары из акции – чтоб не было дублей в акции
                $this->db->where('action_id', $this->action_id);
                $this->db->where_in('product_id', $delete_ids);
                $this->db->delete($this->action_product);
                // добавляем товары в акцию
                $this->db->insert_batch($this->action_product, $data);
            }
        }
        return 1;
    }

    /**
     * удаление всех товаров категории из акции
     * @param $category_id
     * @return int
     */
    public function remove_category_from_action($category_id)
    {
        $products = $this->db
            ->select('pc.*')
            ->from('shop_products_categories_link as pc')
            ->join('shop_products as p', 'p.id = pc.product_id')
            ->where(array(
                'pc.category_id' => $category_id,
                'p.show' => 1,
            ))
            ->get()->result_array();
        if(!empty($products)){
            $products_chunks = array_chunk($products, 100);
            foreach ($products_chunks as $products_chunk){
                $ids = array();
                foreach ($products_chunk as $product){
                    $ids[] = $product['product_id'];
                }
                $this->db->where('action_id', $this->action_id);
                $this->db->where_in('product_id', $ids);
                $this->db->delete($this->action_product);
            }
        }
        return 1;
    }

    /**
     * удаление всех товаров из акции
     * @return int
     */
    public function clear_action()
    {
        $this->db->where('action_id', $this->action_id);
        $this->db->delete($this->action_product);
        return 1;
    }

    /**
     * категории для выпадающего списка формы
     * @param int $parent_id
     * @param bool $dir_only
     * @param int $level
     * @param array $data
     * @return array
     */
    private function cats_options_list($parent_id=0,$dir_only=true,$level=-1,&$data=array())
    {
        $res=$this->db
            ->select("id, title, type")
            ->get_where("categoryes",array(
                "type"=>"shop-category",
                "parent_id"=>$parent_id
            ))
            ->result();

        $level++;

        foreach($res AS $r)
        {
            $data[$r->id]=str_repeat("--",$level)." ".$r->title;
            $data[$r->id]=trim($data[$r->id]);
            $this->cats_options_list($r->id,$dir_only,$level,$data);
        }
        return $data;
    }
}