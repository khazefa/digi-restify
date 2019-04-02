<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends REST_Controller 
{
    function __construct()
    {
		parent::__construct(); // Construct the parent class
		$this->load->library('datatables'); //load library ignited-dataTable
		$this->load->model('User_model','m_user'); // Call model to represent database table
		$this->load->helper('security');

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['user_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['register_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['user_delete']['limit'] = 2; // 2 requests per hour per user/key
	}

	public function list_json_get() 
	{
		$group = filter_var($this->input->get('groupid', TRUE), FILTER_SANITIZE_STRING);

        // $results = $this->m_user->json(array('user_key <>','test1')); //get results with parameters
        $results = $this->m_user->json(); //get results without parameters
		$this->response([
			'status' => TRUE,
			'message' => 'data available '.$group,
			'results' => $results
		], REST_Controller::HTTP_OK);
    }
    
	/**
     * This function is used to check email exist
     */
    public function check_email_get()
    {
		$email = filter_var($this->input->get('email', TRUE), FILTER_SANITIZE_EMAIL);
		
		if($this->m_user->check_data_exists(array('user_email'=>$email)) > 0)
		{
			$this->response([
				'status' => TRUE,
				'message' => 'Account exists'
			], REST_Controller::HTTP_OK);
		}else{
			$this->response([
				'status' => FALSE,
				'message' => 'Account not exists'
			], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	/**
	 * Function user registration
	 */
	public function insert_post()
    {
        $ukey = strip_tags($this->input->post('username', TRUE));
        $password = $this->input->post('password', TRUE);
        $name = strip_tags($this->input->post('name', TRUE));
		$email = filter_var($this->input->post('email', TRUE), FILTER_SANITIZE_EMAIL);

		// Password length should be at least 8 characters
		if(strlen($password) < 8){
			$this->response([
				'status' => FALSE,
				'message' => 'Your password should be at least 8 characters'
			], REST_Controller::HTTP_BAD_REQUEST);
		}else{
			$data_post = array('user_key'=>$ukey, 'user_pass'=>getHashedPassword($password), 'user_name'=>$name, 'user_email'=>$email, 'created_at'=>date('Y-m-d H:i:s'));
			$data_post = $this->security->xss_clean($data_post);

			$count = $this->m_user->check_data_exists(array('user_key' => $ukey));
			if ($count > 0)
			{
				$this->response([
					'status' => FALSE,
					'message' => 'username already exists'
				], REST_Controller::HTTP_BAD_REQUEST);
			}else{
				$result = $this->m_user->insert_data($data_post);
			
				if($result[0])
				{
					$this->response([
						'status' => TRUE,
						'result' => $result[1],
						'message' => 'success'
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
		}
	}

	/**
	 * Function user update
	 */
	public function update_post()
    {
        $ukey = strip_tags($this->input->post('username', TRUE));
        $password = $this->input->post('password', TRUE);
        $name = strip_tags($this->input->post('name', TRUE));
		$email = filter_var($this->input->post('email', TRUE), FILTER_SANITIZE_EMAIL);

		if(empty($password)){
			$data_post = array('user_name'=>$name, 'updated_at'=>date('Y-m-d H:i:s'));
		}else{
			// Password length should be at least 8 characters
			$data_post = array('user_pass'=>getHashedPassword($password), 'user_name'=>$name, 'updated_at'=>date('Y-m-d H:i:s'));
		}

		$update_email = $this->m_user->check_data_exists(array('user_email' => $email)) >= 1 ? FALSE : TRUE;

		if($update_email){
			$data_post += ['user_email'=>$email];
		}
		
		$data_post = $this->security->xss_clean($data_post);

		$result = $this->m_user->update_data($data_post, $ukey);
	
		if($result[0])
		{
			$this->response([
				'status' => TRUE,
				'result' => $result[1],
				'message' => 'success'
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
	
	/**
     * This function is used to delete the data using id
     * @return boolean $result : TRUE / FALSE
     */
    function delete_post()
    {
        $ukey = strip_tags($this->input->post('username', TRUE));

        $result = $this->m_user->delete_data($ukey);

        if($result[0])
        {
            $this->response([
                'status' => TRUE,
                'message' => 'success'
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
}
