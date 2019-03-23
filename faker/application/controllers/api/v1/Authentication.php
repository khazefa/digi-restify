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
	
    /**
     * This function used to generate reset password request link
     */
    function reset_pass_post()
    {
		$email = filter_var($this->input->post('email', TRUE), FILTER_SANITIZE_EMAIL);
		$url = filter_var($this->input->post('url', TRUE), FILTER_SANITIZE_URL);
        
        if($this->m_auth->check_email_exist($email))
        {
            $encoded_email = urlencode($email);

            $data['email'] = $email;
            $data['activation_id'] = generateRandomString(15);
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['agent'] = getBrowserAgent();
            $data['client_ip'] = $this->input->ip_address();
            
            $save = $this->m_auth->reset_password_user($data);                
            
            if($save)
            {
                $data_reset['reset_link'] = $url . "/" . $data['activation_id'] . "/" . $encoded_email;
                $userInfo = $this->m_auth->get_info_by_email($email);

                if(!empty($userInfo)){
                    $data_reset["name"] = $userInfo[0]->user_name;
                    $data_reset["email"] = $userInfo[0]->user_email;
                    $data_reset["message"] = "Reset Password Instructions";
                }

				$this->response([
					'status' => TRUE,
					'results' => $data_reset,
					'message' => 'Please use this data for email content.'
				], REST_Controller::HTTP_OK);
            }
            else
            {
                $this->response([
                    'status' => FALSE,
					'message' => 'failure some error occured:'.$result[1]
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
        else
        {
            $this->response([
                'status' => FALSE,
                'message' => 'If your email is already registered with our system, we will send you the confirmation email'
            ], REST_Controller::HTTP_OK);
        }
    }

    // This function used to reset the password 
    function reset_pass_confirm_get()
    {
        // Get email and activation code from URL values at index 3-4
        $activation_id = $this->input->get('activation_id', TRUE);
		$email = filter_var($this->input->get('email', TRUE), FILTER_SANITIZE_EMAIL);

        $email = urldecode($email);
        
        // Check activation id in database
        $is_correct = $this->m_auth->check_activation_details($email, $activation_id);
        
        $data['email'] = $email;
        $data['activation_code'] = $activation_id;
        
        if ($is_correct == 1)
        {
            $this->response([
                'status' => TRUE,
                'email' => $email,
                'activation_code' => $activation_id,
                'message' => 'This email is registered with our system.'
            ], REST_Controller::HTTP_OK);
        }
        else
        {
            $this->response([
                'status' => TRUE,
                'message' => 'This email is not registered with our system.'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
    // This function used to create new password
    function create_pass_post()
    {
		$email = filter_var($this->input->post('email', TRUE), FILTER_SANITIZE_EMAIL);
        $activation_id = $this->input->post("activation_code");

        $password = $this->input->post('password', TRUE);
        $cpassword = $this->input->post('cpassword', TRUE);
        
        // Check activation id in database
        $is_correct = $this->m_auth->check_activation_details($email, $activation_id);
        
        if($is_correct == 1)
        {                
            $this->m_auth->create_password($email, $password);
            $this->response([
                'status' => TRUE,
                'message' => 'Password changed successfully'
            ], REST_Controller::HTTP_OK);
        }
        else
        {
            $this->m_auth->create_password($email, $password);
            $this->response([
                'status' => TRUE,
                'message' => 'Password changed failed'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

}
