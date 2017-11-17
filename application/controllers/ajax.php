<?php
/**
 * @property Base_model $base_model Base Model Class
 */
class Ajax extends CI_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('base_model');
    }

    public function index()
    {
        echo 'Bad robot, BAD!!!';
    }

    public function test(){
        var_dump('test');
    }

    /**
     * получаем список товаров для автокомплита виджета Slick_slider
     */
    public function get_products_to_make_widget()
    {
        $target = $this->input->get('target', true);
        $search = $this->input->get('term', true);
        $limit = 30;
        $results = $this->base_model->get_like_list('shop_products', array($target), $search, array(), $limit);
        $res = array();
        if(!empty($results)){
            foreach ($results as $item) {
                $res[] = array(
                    'value' => $item['title'],
                    'label' => $item['title'],
                    'id' => $item['id']
                );
            }
            echo json_encode($res);
        }
//        else{ // for debugging only!
//            $res = array('value' => 'no value', 'label' => 'no label', 'id' => 0);
//            echo json_encode($res);
//        }

        return;
    }

    /**
     * удаление продукта из виджета slick_slider
     */
    public function remove_product_from_slick()
    {
        $widget_id = $this->input->post('widget_id');
        $product_id = $this->input->post('product_id');
        $affected = '';
        // deleting
        if(!empty($widget_id) && !empty($product_id)){
            $this->db->delete('slick_sliders', array(
                'product_id' => (int)$product_id,
                'widget_id' => (int)$widget_id
            ));
            $affected = $this->db->affected_rows();
        }
        // debug report
        if(!empty($affected)){
            echo 1;
        }
        else{
            echo 0;
        }
    }

    public function sort_products_in_slick($widget_id)
    {
        $items = $this->input->post('items', true);
        $affected = '';
        // updating
        if(!empty($items)){
            foreach($items as $pos => $product_id){
                $pos++;
                $this->base_model->update_item('slick_sliders', array('order' => (int)$pos), array(
                    'widget_id' => (int)$widget_id,
                    'product_id' => (int)$product_id
                ));
                $affected = $this->db->affected_rows();
            }
        }
        // debug report
        if(!empty($affected)){
            echo 1;
        }
        else{
            echo 0;
        }
    }

    public function remove_product_from_mosaic()
    {
        $product_id = $this->input->post('product_id');
        $affected = '';
        // deleting
        if(!empty($product_id)){
            $this->db->delete('mosaics', array(
                'product_id' => (int)$product_id,
            ));
            $affected = $this->db->affected_rows();
        }
        // debug report
        if(!empty($affected)){
            echo 1;
        }
        else{
            echo 0;
        }
    }
} 