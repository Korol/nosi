<?php

class Work extends CI_Controller {

    function __construct() {
        parent::__construct();
    }
    
    public function set_views() {
        exit();
        $stats = $this->db->query("SELECT DISTINCT `item_id`, COUNT(`item_id`) AS `cnt` FROM `stats_products` WHERE `item_id` <= '3304' GROUP BY `item_id`", FALSE)->result_array();
//        var_dump($stats);
        $cnt = 0;
        if(!empty($stats)){
            foreach ($stats as $item) {
                $this->db->update('shop_products', array('views' => $item['cnt']), array('id' => $item['item_id']));
                $cnt = ($this->db->affected_rows()) ? ++$cnt : $cnt;
            }
        }
        var_dump($cnt);
    }
    
    public function check_filename() {
        error_reporting(1);
        $this->load->helper('cms_helper');
//        $filename = 'розовый_с_булым_молния_138_юань136.jpg';
        $filename = '1.1-308_юань_р.М-ХХЛ-коричневый.jpg';
        //$file_name=file_name("./uploads/shop/products/original/",$filename,true);
        $ex_filename = explode('.', $filename);
        $file_name = '0_' . md5(uniqid(rand(),true)) . '.' . end($ex_filename);
        var_dump($filename, $file_name);
    }
    
    public function clean_images() {
        
    }
    
    public function copy_images() {
        // сохранять имя, которым переименовали файлы – для того, чтобы потом это же имя использовать при переименовании файлов в директориях:
        // /big
        // /thumbs
        // /thumbs2
        // /thumbs3
        // /thumbs4
        // 
        // 
        // 
//        insert into `uploads_cp` (`upload_id`, `file_name`, `file_path`) select `u`.`id`, `u`.`file_name`, `u`.`file_path` from `uploads` `u` where `u`.`name` = 'product-photo' and `file_name` REGEXP '[а-яА-Я\!\(\),]+';
        exit();
        ini_set('max_execution_time', 300); // time limits
        ini_set('memory_limit', '256M'); // memory limits
        error_reporting(1);
        $time = $this->script_worktime(0);
        
        $table_cp = 'uploads_cp'; 
        $upload_table = 'uploads';
        $upload_id = 'upload_id';
        $dir = 'uploads/shop/products/original/';
        
//        $res = $this->db->query("SELECT `upload_id`, `file_name`, `file_path` FROM `" . $table . "` WHERE `name` = 'product-photo' AND `file_name` REGEXP \"[а-яА-Я\!\(\),]+\"")->result_array();
        $res = $this->db->query("SELECT `upload_id`, `file_name`, `file_path` FROM `" . $table_cp . "`")->result_array();
        
        if(!empty($res)){
            foreach ($res as $row){
                // copy files
//                copy(FCPATH . $row['file_path'] . $row['file_name'],  FCPATH . 'uploads/shop/products/work_orig/' . $row['file_name']);
                
                // rename files
                $ex_name = explode('.', $row['file_name']);
                $new_name = '0_' . md5(uniqid(rand(),true)) . '.' . end($ex_name);
                $rename = rename(FCPATH . $dir . $row['file_name'], FCPATH . $dir . $new_name);
                
                // save changes into DB
                if($rename){
                    $this->db->update($upload_table, array('file_name' => $new_name), array('id' => $row[$upload_id])); // uploads
                    $this->db->update($table_cp, array('new_name' => $new_name), array($upload_id => $row[$upload_id])); // uploads_cp
                }
                else{
                    if(file_exists(FCPATH . $row['file_path'] . $row['file_name'])){
                        var_dump('File ' . $row['file_name'] . ' exist.');
                    }
                    else{
                        var_dump('File ' . $row['file_name'] . ' NOT exist.');
                    }
                    var_dump('File ' . $row['file_name'] . ' was NOT renamed!');
                }
            }
        }
        
        $worktime = $this->script_worktime($time, 1);
    }
    
    public function rename_original_images() {
        exit('Script was stopped!');
        ini_set('max_execution_time', 300); // time limits
        ini_set('memory_limit', '256M'); // memory limits
        error_reporting(1);
        $time = $this->script_worktime(0);
        
        $table_cp = 'uploads_cp'; 
        $upload_table = 'uploads';
        $upload_id = 'upload_id';
        $dir = 'uploads/shop/products/original/';
        
        $res = $this->db->query("SELECT `upload_id`, `file_name` FROM `" . $table_cp . "`")->result_array();
        
        $renamed = 0;
        if(!empty($res)){
            foreach ($res as $row){
                
                // rename files
                $ex_name = explode('.', $row['file_name']);
                $new_name = '0_' . md5(uniqid(rand(),true)) . '.' . end($ex_name);
                $rename = rename(FCPATH . $dir . $row['file_name'], FCPATH . $dir . $new_name);
                
                // save changes into DB
                if($rename){
                    $this->db->update($upload_table, array('file_name' => $new_name), array('id' => $row[$upload_id])); // uploads
                    $this->db->update($table_cp, array('new_name' => $new_name), array($upload_id => $row[$upload_id])); // uploads_cp
                    $renamed++;
                }
                else{
                    if(file_exists(FCPATH . $dir . $row['file_name'])){
                        var_dump('File ' . $row['file_name'] . ' exist.');
                    }
                    else{
                        var_dump('File ' . $row['file_name'] . ' NOT exist.');
                    }
                    var_dump('File ' . $row['file_name'] . ' was NOT renamed!');
                }
            }
        }
        var_dump('Renamed: ' . $renamed . ' thumbs in directory ' . $dir);
        $worktime = $this->script_worktime($time, 1);
    }
    
    public function rename_thumbs_images($path) {
        var_dump($path);
        exit('Script was stopped!');
        
        if(!in_array($path, array('big', 'thumbs', 'thumbs2', 'thumbs3', 'thumbs4'))){
            exit('Path is incorrect!');
        }
        
        ini_set('max_execution_time', 300); // time limits
        ini_set('memory_limit', '256M'); // memory limits
        error_reporting(1);
        $time = $this->script_worktime(0);
        
        $table_cp = 'uploads_cp'; 
        $dir = 'uploads/shop/products/' . $path . '/';
        
        $res = $this->db->query("SELECT `new_name`, `file_name` FROM `" . $table_cp . "`")->result_array();
        
        $renamed = 0;
        if(!empty($res)){
            foreach ($res as $row){
                if(empty($row['new_name']))
                    continue;
                
                // rename files
                $new_name = $row['new_name'];
                $rename = rename(FCPATH . $dir . $row['file_name'], FCPATH . $dir . $new_name);
                
                // save changes into DB
                if($rename){
                    $renamed++;
                }
                else{
                    if(file_exists(FCPATH . $dir . $row['file_name'])){
                        var_dump('File ' . $row['file_name'] . ' exist.');
                    }
                    else{
                        var_dump('File ' . $row['file_name'] . ' NOT exist.');
                    }
                    var_dump('File ' . $row['file_name'] . ' was NOT renamed!');
                }
            }
        }
        var_dump('Renamed: ' . $renamed . ' thumbs in directory ' . $dir);
        $worktime = $this->script_worktime($time, 1);
    }
    
    public function script_worktime($time, $type = 0){
        if($type == 0){
            return microtime(true);
        }
        else{
            $time = microtime(true) - $time;
            printf('Script worktime: %.4F sec.', $time);
            return sprintf('Script worktime: %.4F sec.', $time);
        }
    }
    
    public function set_uploads_info($dir) {
        exit();
        
        
    }
    
    public function compress_gif() {
        $dir = 'uploads/shop/products/original/';
        $file_name = '0_e1e25239e4599834db8a957fc5086187.gif';
        $file_size = filesize(FCPATH . $dir . $file_name);
        var_dump($file_size, $this->human_filesize($file_size));
        copy(FCPATH . $dir . $file_name, FCPATH . 'uploads/shop/products/tmp/' . $file_name);
        $this->compress_image($file_name);
    }
    
    public function human_filesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }
    
    public function compress_image($file_name = '', $quality = 75) 
    {
        $max_width = 600;
        $max_height = 800;
        $config['source_image'] = FCPATH . 'uploads/shop/products/tmp/' . $file_name;
        $info = getimagesize($config['source_image']);
        
        $config['quality'] = '90%';
        $config['maintain_ratio'] = TRUE;
        $config['width'] = ($info[0] > $max_width) ? $max_width : $info[0];
        $config['height'] = ($info[1] > $max_height) ? $max_height : $info[1];
        
        $this->load->library('image_lib', $config); 

        $this->image_lib->resize();
        var_dump($this->image_lib->display_errors());
        
        $file_size = filesize($config['source_image']);
        var_dump($file_size, $this->human_filesize($file_size));
    }

}