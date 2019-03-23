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
                $data1['reset_link'] = $this->config->item('frontend') . "reset_pass_confirm/" . $data['activation_id'] . "/" . $encoded_email;
                $userInfo = $this->MLog->get_info_by_email($email);

                if(!empty($userInfo)){
                    $data1["name"] = $userInfo[0]->user_fullname;
                    $data1["email"] = $userInfo[0]->user_email;
                    $data1["message"] = "Reset Your Password";
                }

                $sendStatus = resetPasswordEmail($data1);

                if($sendStatus){
                    $this->response([
                        'status' => TRUE,
                        'message' => 'Reset password link sent successfully, please check your email.'
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Email has been failed, try again.'
                    ], REST_Controller::HTTP_OK);
                }
            }
            else
            {
                $this->response([
                    'status' => FALSE,
                    'message' => 'It seems an error while sending your details, try again.'
                ], REST_Controller::HTTP_OK);
            }
        }
        else
        {
            $this->response([
                'status' => FALSE,
                'message' => 'Your email is not registered with us.'
            ], REST_Controller::HTTP_OK);
        }
    }

    // This function used to reset the password 
    function reset_pass_confirm_get()
    {
        // Get email and activation code from URL values at index 3-4
        $activation_id = $this->get('activation_id');
        $email = $this->get('email');

        $email = urldecode($email);
        
        // Check activation id in database
        $is_correct = $this->MLog->check_activation_details($email, $activation_id);
        
        $data['email'] = $email;
        $data['activation_code'] = $activation_id;
        
        if ($is_correct == 1)
        {
            $this->response([
                'status' => TRUE,
                'email' => $email,
                'activation_code' => $activation_id,
                'message' => 'This email is registered with us.'
            ], REST_Controller::HTTP_OK);
        }
        else
        {
            $this->response([
                'status' => TRUE,
                'message' => 'This email is not registered with us.'
            ], REST_Controller::HTTP_OK);
        }
    }
    
    // This function used to create new password
    function create_pass_post()
    {
        $status = '';
        $message = '';
        $femail = $this->input->post("femail", TRUE);
        $activation_id = $this->input->post("activation_code");

        $password = $this->input->post('password', TRUE);
        $cpassword = $this->input->post('cpassword', TRUE);
        
        // Check activation id in database
        $is_correct = $this->MLog->check_activation_details($email, $activation_id);
        
        if($is_correct == 1)
        {                
            $this->MLog->create_password($email, $password);
            $this->response([
                'status' => TRUE,
                'message' => 'Password changed successfully'
            ], REST_Controller::HTTP_OK);
        }
        else
        {
            $this->MLog->create_password($email, $password);
            $this->response([
                'status' => TRUE,
                'message' => 'Password changed failed'
            ], REST_Controller::HTTP_OK);
        }
    }

}
