<?php
/**
 * Class sliderWidgetInfo
 * админка виджета
 */
class slick_sliderWidgetInfo {
	//public $title="Slick-слайдер";
    public $ci;

	public function __construct()
	{
		$this->ci=&get_instance(); // доступ к функционалу CI через $this->ci->
        $this->ci->load->model('base_model');
        $this->ci->output->enable_profiler(true);
	}

	public function admin_before_save($d)
	{
        // действия ДО добавления/изменения виджета
	}

	public function admin_after_save(&$widget_id,&$d)
	{
        // действия ПОСЛЕ добавления/изменения виджета, по идентификатору виджета $widget_id
        $post = $this->ci->input->post(null, true);
        if(!empty($post['products_ids'])){
            // сохраняем инфу о товарах для данного слайдера($widget_id) в БД
            $p_ids = explode('_', $post['products_ids']);
            $max_order = $this->ci->db->select_max('order', 'max_order')
                ->where('widget_id', $widget_id)
                ->get('slick_sliders')->row()->max_order;
            if(!empty($p_ids)){
                $p_ids = array_unique($p_ids); // чистим от возможных повторов из-за автокомплита
                foreach ($p_ids as $p_id) {
                    $insert = array(
                        'widget_id' => $widget_id,
                        'product_id' => $p_id,
                        'order' => ++$max_order
                    );
                    $this->ci->base_model->insert_item('slick_sliders', $insert);
                }
            }
        }
	}

	public function admin_options(&$f)
	{
        // формирование страницы добавления/редактирования виджета
        // этот метод отдаёт в рендер сформированный HTML, который будет выводиться в табе «Основное»
        // на странице добавления/редактирования виджета

        //$key=md5(uniqid(rand(),1)).md5(uniqid(rand(),1)); // ???
        $data['items'] = array();
        // получаем товары для редактирования – если идет редактирование, а не добавление виджета
        if($_GET['a'] === 'edit_widget' && !empty($_GET['id'])){
            $this->ci->db->select('slick_sliders.product_id, slick_sliders.order, shop_products.id, shop_products.title, shop_products.code, shop_products.name');
            $this->ci->db->join('shop_products', 'shop_products.id = slick_sliders.product_id', 'left');
            $this->ci->db->order_by('slick_sliders.order asc');
            $data['items'] = $this->ci->db->get('slick_sliders')->result_array();
            // картинки
            if(!empty($data['items'])){
                $ids = array();
                foreach($data['items'] as $item){
                    $ids[] = $item['id'];
                }
                $images_res = $this->ci->db->select('extra_id, file_name')
                    ->where_in('extra_id', $ids)
                    ->where(array(
                        'name' => 'product-photo',
                        'order' => 1
                    ))
                    ->get('uploads')->result_array();
                if(!empty($images_res)){
                    foreach($images_res as $img){
                        $data['images'][$img['extra_id']]['image'] = $img['file_name'];
                    }
                }
            }
        }
        $content = $this->ci->load->view('newdesign/slick_slider_widget', $data, true);
        // скармливаем контент библиотеке рендеринга
		$f->add("html",array(
			"label"=> (!empty($data['items'])) ? "Slick-Слайды" : '',
			"content"=>$content,
			"parent"=>"greed"
		));
	}


}
?>