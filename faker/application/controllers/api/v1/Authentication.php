<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Authentication extends REST_Controller 
{
    function __construct()
    {
        // Construct the parent class
		parent::__construct();
		// Call model to represent database table
		$this->load->model('Auth_model','m_auth');
		$this->load->helper('security');

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['auth_get']['limit'] = 10; // 10 requests per hour per user/key
        $this->methods['auth_post']['limit'] = 10; // 10 requests per hour per user/key
	}
    
    /**
     * This function used to logged in user
     */
    public function auth_post()
    {
        $username = strip_tags($this->input->post('username', TRUE));
        $password = $this->input->post('password', TRUE);
        
        $result = $this->m_auth->auth_default($username, $password);
		
        if(count($result) > 0)
        {
            foreach ($result as $res)
            {
                $this->response([
                    'status' => TRUE,
                    'accessId'=>$res->user_id,
                    'accessUr'=>$res->user_key,
                    'accessName'=>$res->user_name,
                    'accessMail'=>$res->user_email,
                    'message' => 'User sign in'
                ], REST_Controller::HTTP_OK);
            }
        }
        else
        {
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid Account!'
			], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

}
