<?php
/**
 * Class mosaicWidgetInfo
 * админка виджета
 * @property base_model $base_model
 */
class mosaicWidgetInfo {

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
        $post = $this->ci->input->post(null, true);//var_dump($post);
        // товары
        if(!empty($post['products'])){
            // сохраняем инфу о товарах для данного виджета($widget_id) в БД
            foreach($post['products'] as $pos => $product){
                if(empty($product)) continue;
                foreach($product as $product_id => $product_title){
                    // очищаем позицию
                    $this->ci->db->delete('mosaics', array(
                        'widget_id' => $widget_id,
                        'position' => "$pos"
                    ));
                    // добавляем товар на позицию
                    $this->ci->db->insert('mosaics', array(
                        'widget_id' => $widget_id,
                        'product_id' => $product_id,
                        'position' => "$pos"
                    ));//var_dump($pos, $this->ci->db->last_query());
                }
            }
        }
        // категории
        if(!empty($post['categories'])){
            // сохраняем инфу о товарах для данного виджета($widget_id) в БД
            foreach($post['categories'] as $pos => $category_id){
                if(empty($category_id)) continue;
                // очищаем позицию
                $this->ci->db->delete('mosaics', array(
                    'widget_id' => $widget_id,
                    'position' => "$pos"
                ));
                // добавляем категорию на позицию
                $this->ci->db->insert('mosaics', array(
                    'widget_id' => $widget_id,
                    'category_id' => $category_id,
                    'position' => "$pos",
                    'type' => 'category'
                ));//var_dump($pos, $this->ci->db->last_query());
            }
        }
//        die();
	}

	public function admin_options(&$f)
	{
        //error_reporting(E_ALL);
        // формирование страницы добавления/редактирования виджета
        // этот метод отдаёт в рендер сформированный HTML, который будет выводиться в табе «Основное»
        // на странице добавления/редактирования виджета

        //$key=md5(uniqid(rand(),1)).md5(uniqid(rand(),1)); // ???
        $data['items'] = array();
        $data['categories_select'] = array(" ")+$this->cats_options_list();
        // получаем товары для редактирования – если идет редактирование, а не добавление виджета
        if($_GET['a'] === 'edit_widget' && !empty($_GET['id'])) {
            // товары
            $products_res = $this->ci->db->select('mosaics.product_id, mosaics.position, mosaics.image, uploads.file_name, shop_products.title, shop_products.code')
                ->join('uploads', 'uploads.extra_id = mosaics.product_id', 'left')
                ->join('shop_products', 'shop_products.id = mosaics.product_id', 'left')
                ->where(array(
                    'uploads.order' => 1,
                    'mosaics.widget_id' => (int)$_GET['id']
                ))
                ->get('mosaics')->result_array();
            if(!empty($products_res)){
                foreach($products_res as $p_res){
                    $data['products'][$p_res['position']] = $p_res;
                }
            }
            // категории
            $categories_res = $this->ci->db->select('mosaics.category_id, mosaics.position, categoryes.title')
                ->join('categoryes', 'categoryes.id = mosaics.category_id')
                ->where(array('mosaics.widget_id' => (int)$_GET['id']))
                ->get('mosaics')->result_array();
            if(!empty($categories_res)){
                foreach($categories_res as $c_res){
                    $data['categories'][$c_res['position']] = $c_res;
                }
            }
        }

        $content = $this->ci->load->view('newdesign/mosaic_widget', $data, true);
        // скармливаем контент библиотеке рендеринга
		$f->add("html",array(
			"label"=> (!empty($data['items'])) ? "Slick-Слайды" : '',
			"content"=>$content,
			"parent"=>"greed"
		));
	}

    public function cats_options_list($parent_id=0, $dir_only=true, $level=-1, &$data=array())
    {
        $res = $this->ci->db
            ->select("id, title")
            ->get_where("categoryes",array(
                "type"=>"shop-category",
                "parent_id"=>$parent_id
            ))
            ->result();

        $level++;

        foreach($res AS $r)
        {
            $data[$r->id] = str_repeat("--", $level)." ".$r->title;
            $data[$r->id] = trim($data[$r->id]);
            $this->cats_options_list($r->id, $dir_only, $level,$data);
        }
        return $data;
    }
}
?>