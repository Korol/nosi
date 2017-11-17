<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cms_plugins {
	var $ci;

	public function __construct()
	{
		$this->ci =& get_instance();

		$this->db=$this->ci->db;
		$this->load=$this->ci->load;
		$this->input=$this->ci->input;
	}
}