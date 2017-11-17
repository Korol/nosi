<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Router extends CI_Controller
{
    public $controller;
    public $action;
//    public $num_url = 1; // 1 for /newdesign/, else: 0
    public $num_url = 0; // 1 for /newdesign/, else: 0

    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
//        $this->show_message();
        $segments_num = $this->uri->total_segments();

        include APPPATH . 'controllers/newdesign/pages.php';
        $this->controller = new Pages();

        switch ($segments_num) {
            case ($this->num_url + 0):
                // is Main page
                $this->controller->getMainPage();
                break;
            case ($this->num_url + 1):
                // is Category or Static page or Brand
                if($this->uri->segment($segments_num - 1) === 'catalog'){
                    // is Brand page
                    $this->controller->getBrandPage($this->uri->segment($segments_num));
                }
                elseif($this->uri->segment($segments_num) === 'brands'){
                    // is Brands list page
                    $this->controller->getBrandsListPage();
                }
                else{
                    // is Category or Static page
                    $this->controller->getCategoryOrStatic($this->uri->segment($segments_num));
                }
                break;
            case ($this->num_url + 2):
                // is Product page
                $this->controller->getProductPage($this->uri->segment($segments_num));
                break;
            default:
                show_404();
        }
    }
} 