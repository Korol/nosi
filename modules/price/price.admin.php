<?php
include_once("./modules/shop/shop.helper.php");

class priceModule extends shopModuleHelper {
	function __construct()
	{
		parent::__construct();

		$this->load->library("categories");
                $this->load->library('price');
	}

        public function categories(){
            $this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);
            
            $data['categories'] = $this->ci->price->get_categories();
            $data['price_fields'] = $this->ci->price->get_price_fields();
            $data['price_options'] = $this->ci->price->get_price_options();
            $data['price_success'] = $this->ci->session->flashdata('price_cats_updated_success');
            $data['price_error'] = $this->ci->session->flashdata('price_cats_updated_error');

            $this->ci->load->adminView("price/categories", $data);
        }
        
        public function update_categories()
        {
            $this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);
            
            $post = $this->input->post(NULL, TRUE);
            $price_params = $this->ci->price->get_price_params();
            $insert = array();
            foreach ($post['ids'] as $id){
                $insert[$id] = array('category_id' => $id);
                foreach (array_keys($price_params) as $field){
                    $insert[$id][$field] = (isset($post[$field][$id])) ? $post[$field][$id] : 0;
                }
            }
            if(!empty($insert)){
                $affected = $this->ci->price->update_categories_info($insert);
                if($affected > 0){
                    $this->ci->session->set_flashdata('price_cats_updated_success', 'Данные успешно обновлены!');
                }
                else{
                    $this->ci->session->set_flashdata('price_cats_updated_error', 'Данные НЕ обновлены!');
                }
            }
            redirect('admin/?m=price&a=categories');
        }
        
        public function build_price()
        {
            $this->ci->output->enable_profiler(TRUE);
            $this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);
            $data['error'] = FALSE;
            
            $data['price_categories'] = $this->ci->price->get_in_price_categories();
            $data['price_products'] = $this->ci->price->get_price_products(array_keys($data['price_categories']));
//            var_dump($data['price_products']);
            
            $this->ci->load->adminView("price/build_price", $data);
        }
        
}