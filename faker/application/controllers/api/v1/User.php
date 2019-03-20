<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
		parent::__construct();
		// Call model to represent database table
		$this->load->model('User_model','m_user');
		$this->load->helper('security');

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['user_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['user_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['user_delete']['limit'] = 2; // 2 requests per hour per user/key
	}

	/**
	 * Function user registration
	 */
	public function register_post()
    {
		
	}
	
}
