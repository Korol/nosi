<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Test extends CI_Controller
{
    public function index()
    {
        echo 'newdesign/test/index<br><pre>';
        return;
    }

    public function rest()
    {
        echo 'newdesign/test/rest<br>';
        return;
    }
} 