<?php
/**
 * Created by PhpStorm.
 * User: korol
 * Date: 10.02.16
 * Time: 11:01
 */

/**
 * @property Base_model $base_model Base Model Class
 * @property Menu $menu Menu Library Class
 */
class Test extends CI_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->library('menu');
        $this->load->model('base_model');
        $this->load->helper('form');
        $this->load->helper('url');
    }

    public function index()
    {
        echo 'Hello!))';
    }

    public function backup()
    {
        $type = '';
        $result = 0;


        if(!empty($_POST['subm'])) {
            $passw = $this->input->post('passw', true);
            if ($passw !== 'getbu2016') return;

            $type = $this->input->post('type', true);
            $result = $this->input->post('result', true);
            $result = (empty($result)) ? 0 : 1;
        }

        if(in_array($type, array('app', 'code'))) {

            $this->output->enable_profiler(true);
            $this->load->library('zip');

            $directories = array(
                'app' => array('application'),
                'code' => array('modules', 'plugins', 'application', 'assets', 'rams', 'templates', 'widgets', 'system'),
            );
            $files = array(
                'app' => array(),
                'code' => array('favicon.png', 'index.php', 'robots.txt', '.htaccess', 'config.php', 'sample_config.php', 'nd_integration'),
            );
            if(!empty($directories[$type])){
                foreach($directories[$type] as $dir){
                    $this->zip->read_dir(FCPATH . $dir . '/', false);
                }
            }
            if(!empty($files[$type])){
                foreach($files[$type] as $file){
                    $this->zip->read_file(FCPATH . $file);
                }
            }

            if(empty($result)){
                $this->zip->archive(FCPATH . 'uploads/nosieto_' . $type . '_' . date('Y-m-d_H-i-s') . '.zip');
            }
            else{
                $this->zip->download('nosieto_' . $type . '_' . date('Y-m-d_H-i-s') . '.zip');
            }
        }

        $this->load->view('backup');
    }

    public function email()
    {
//        $this->load->view('newdesign/registration_email');
    }

    public function test_login()
    {
//        var_dump($this->ion_auth_model->login('upopovich@mail.ru', 'andkorol1'));
//        var_dump($this->ion_auth->errors());
    }
} 