<?php
/**
 * @property Base_model $base_model Base Model Class
 * @property CI_DB_active_record $db
 */
class Cropper extends CI_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('base_model');
        $this->load->helper('url');

        // проверка авторизации юзера
        $this->admin_url="/admin/";
        if(($this->ion_auth->logged_in() === false) && ($_GET['a'] != "login")){
            redirect($this->admin_url."?m=user&a=login");
            exit;
        }

        $this->crop_settings = array(
            'width' => 292,
            'height' => 584,
            'quality' => 100,
            'ratio' => '1:2',
            'path_to_original' => 'uploads/shop/products/big/',
            'path_to_save' => 'uploads/shop/products/mosaic/'
        );
    }

    public function index()
    {
        echo 'Bad robot, BAD!!!';
    }

    public function select($widget_id, $product_id)
    {
        // все фото товара для выбора, какую будем обрезать
        $data['images'] = $this->db->select('id, file_name')
            ->where(array(
                'extra_id' => $product_id,
                'name' => 'product-photo'
            ))
            ->order_by('order asc')
            ->get('uploads')->result();
        $data['widget_id'] = $widget_id;
        $data['product_id'] = $product_id;
        $this->load->view('newdesign/image_select', $data);
    }

    public function polygon()
    {
        $post = $this->input->post(null, true);
        // проверяем загрузку или выбор файла
        if (!empty($_FILES['userfile']['tmp_name'])) {
            // загружаем фото на сервер
            // var_dump($post['widget_id'], $post['product_id'], $_FILES);
            $this->upload_polygon($post['widget_id'], $post['product_id']);
        }
        elseif (!empty($post['selected_image'])) {
            // кропаем выбранное фото
            // var_dump($post['widget_id'], $post['product_id'], $post['selected_image']);
            $this->crop_polygon($post['widget_id'], $post['product_id'], $post['selected_image']);
        }
        else {
            // отправляем обратно
            redirect('/cropper/select/' . $post['widget_id'] . '/' . $post['product_id']);
        }
    }

    public function upload_polygon($widget_id, $product_id)
    {
        $config['upload_path'] = './' . $this->crop_settings['path_to_save'];
        $config['allowed_types'] = 'jpg|jpeg';
        $config['max_size']	= '100'; // 100Kb max
        $config['max_width']  = '584'; // 292 * 2
        $config['max_height']  = '1168'; // 584 * 2
        $config['encrypt_name'] = true;

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload())
        {
            $this->session->set_flashdata('errors', $this->upload->display_errors());
            redirect('/cropper/select/' . $widget_id . '/' . $product_id);
        }
        else
        {
            $data = $this->upload->data();
            // var_dump($data);
            // запись о загруженном изображении в таблицу mosaics
            $this->db->update('mosaics', array(
                'image' => $data['file_name']
            ), array(
                'widget_id' => $widget_id,
                'product_id' => $product_id
            ));
            if($this->db->affected_rows() > 0){
                redirect('/admin/?m=admin&a=edit_widget&id=' . $widget_id);
            }
            else{
                $this->write_error('Query error: ' . $this->db->last_query(), __METHOD__);
            }
        }
    }

    public function crop_polygon($widget_id, $product_id, $selected_image){
        // ищем фотку
        $image_info = $this->db->select('file_name')
            ->where(array(
                'id' => $selected_image,
                'extra_id' => $product_id
            ))
            ->limit(1)
            ->get('uploads')->row();
        // var_dump($image_info, $widget_id, $product_id);
        // проверяем инфу и файл
        if(!empty($image_info->file_name)
            && file_exists($this->crop_settings['path_to_original'] . $image_info->file_name))
        {
            // выводим страницу с функционалом обрезки фото
            $data['image'] = $image_info->file_name;
            $data['path'] = '/' . $this->crop_settings['path_to_original'];
            $data['widget_id'] = $widget_id;
            $data['product_id'] = $product_id;
            $this->load->view('newdesign/image_crop', $data);
        }
        else{
            $this->write_error('File not found!', __METHOD__);
            echo 'Not OK';
//            return false;
        }
    }

    public function do_crop()
    {
        $post = $this->input->post(NULL, TRUE);
        if(isset($post)){
            $new_img_name = time() . '_' . $post['file_name'];
            // параметры обрезки
            $x1 = $post['x1'];
            $x2 = $post['x2'];
            $y1 = $post['y1'];
            $y2 = $post['y2'];
            $img = $this->crop_settings['path_to_original'] . $post['file_name'];
            $crop = $this->crop_settings['path_to_save'] . $new_img_name;
            $widget_id = $post['widget_id'];
            $product_id = $post['product_id'];
            // var_dump($post, $img, $crop);
            // обрезка изображения
            $this->crop($img, $crop, array($x1, $y1, $x2, $y2));
            // проверка
            if(file_exists($this->crop_settings['path_to_save'] . $new_img_name)){
                // echo '<img src="/' . $this->crop_settings['path_to_save'] . $post['file_name'] . '" alt="Result"/>';
                // сохраняем инфу о картинке в БД в таблице mosaics
                $this->db->update('mosaics', array(
                    'image' => $new_img_name
                ), array(
                    'widget_id' => $widget_id,
                    'product_id' => $product_id
                ));
                redirect('/admin/?m=admin&a=edit_widget&id=' . $widget_id);
            }
            else{
                // echo 'Error((';
                $this->write_error('Cropped file not found! May be he\'s nott cropped!', __METHOD__);
            }
        }
    }

    public function write_error($error_text, $method = '', $script = __FILE__)
    {
        $this->db->insert('error_log', array(
            'error_text' => $error_text,
            'script' => $script,
            'method' => $method
        ));
    }

    /**
     * Масштабирование изображения
     *
     * Функция работает с PNG, GIF и JPEG изображениями.
     * Масштабирование возможно как с указаниями одной стороны, так и двух, в процентах или пикселях.
     *
     * @param string Расположение исходного файла
     * @param string Расположение конечного файла
     * @param integer Ширина конечного файла
     * @param integer Высота конечного файла
     * @param bool Размеры даны в пискелях или в процентах
     * @return bool
     */
    function resize($file_input, $file_output, $w_o, $h_o, $percent = false) {
        list($w_i, $h_i, $type) = getimagesize($file_input);
        if (!$w_i || !$h_i) {
            echo 'Невозможно получить длину и ширину изображения ' . $file_input;
            return;
        }
        $types = array('','gif','jpeg','png');
        $ext = $types[$type];
        if ($ext) {
            $func = 'imagecreatefrom'.$ext;
            $img = $func($file_input);
        } else {
            echo 'Некорректный формат файла';
            return;
        }
        if ($percent) {
            $w_o *= $w_i / 100;
            $h_o *= $h_i / 100;
        }
        if (!$h_o) $h_o = $w_o/($w_i/$h_i);
        if (!$w_o) $w_o = $h_o/($h_i/$w_i);
        $img_o = imagecreatetruecolor($w_o, $h_o);
        imagecopyresampled($img_o, $img, 0, 0, 0, 0, $w_o, $h_o, $w_i, $h_i);
        if ($type == 2) {
            imagejpeg($img_o,$file_output,100);
        } else {
            $func = 'image'.$ext;
            $func($img_o,$file_output);
        }
        imagedestroy($img_o);
    }

    /**
     * Обрезка изображения
     *
     * Функция работает с PNG, GIF и JPEG изображениями.
     * Обрезка идёт как с указанием абсоютной длины, так и относительной (отрицательной).
     *
     * @param string Расположение исходного файла
     * @param string Расположение конечного файла
     * @param array Координаты обрезки
     * @param bool Размеры даны в пискелях или в процентах
     * @return bool
     */
    function crop($file_input, $file_output, $crop = array('square'),$percent = false) {
        list($w_i, $h_i, $type) = getimagesize($file_input);
        if (!$w_i || !$h_i) {
            echo 'Невозможно получить длину и ширину изображения' . $file_input;
            return;
        }
        $types = array('','gif','jpeg','png');
        $ext = $types[$type];
        if ($ext) {
            $func = 'imagecreatefrom'.$ext;
            $img = $func($file_input);
        } else {
            echo 'Некорректный формат файла';
            return;
        }
        if ($crop[0] == 'square') {
            $min = $w_i;
            if ($w_i > $h_i) $min = $h_i;
            $w_o = $h_o = $min;
        } else {
            list($x_o, $y_o, $w_o, $h_o) = $crop;
            if ($percent) {
                $w_o *= $w_i / 100;
                $h_o *= $h_i / 100;
                $x_o *= $w_i / 100;
                $y_o *= $h_i / 100;
            }
            if ($w_o < 0) $w_o += $w_i;
            $w_o -= $x_o;
            if ($h_o < 0) $h_o += $h_i;
            $h_o -= $y_o;
        }
        $img_o = imagecreatetruecolor($w_o, $h_o);
        imagecopy($img_o, $img, 0, 0, $x_o, $y_o, $w_o, $h_o);
        if ($type == 2) {
            imagejpeg($img_o,$file_output,100);

        } else {
            $func = 'image'.$ext;
            $func($img_o,$file_output);
        }
        imagedestroy($img_o);
    }

} 