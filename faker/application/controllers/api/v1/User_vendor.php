<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

	class User_vendor extends REST_Controller {
		function __construct(){
			parent::__construct(); // Construct the parent class
			$this->load->library('datatables'); //load library ignited-dataTable
			$this->load->model('User_vendor_model','m_user_vendor'); // Call model to represent database table
			$this->load->helper('security');
			$this->load->helper('string');

			// Configure limits on our controller methods
			// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
			$this->methods['user_get']['limit'] = 500; // 500 requests per hour per user/key
			$this->methods['register_post']['limit'] = 100; // 100 requests per hour per user/key
			$this->methods['user_delete']['limit'] = 2; // 2 requests per hour per user/key
		}
		
		public function list_json_get() {
			$group = filter_var($this->input->get('groupid', TRUE), FILTER_SANITIZE_STRING);

			$results = $this->m_user_vendor->json();
			$this->response([
				'status' => TRUE,
				'message' => 'data available '.$group,
				'results' => $results
			], REST_Controller::HTTP_OK);
		}
		
		public function input_post() {
			
			$user_vendor_vendor 		= strip_tags($this->input->post('user_vendor_vendor', TRUE));
			$user_vendor_branch 		= strip_tags($this->input->post('user_vendor_branch', TRUE));
			$user_vendor_service 		= strip_tags($this->input->post('user_vendor_service', TRUE));
			$user_vendor_code 			= substr($user_vendor_vendor,-6).substr($user_vendor_branch,-3).substr($user_vendor_service,-3).random_string('nozero', 3);
			$user_vendor_name 			= strip_tags($this->input->post('user_vendor_name', TRUE));
			$user_vendor_email 			= strip_tags($this->input->post('user_vendor_email', TRUE));
			$user_vendor_mobile 		= strip_tags($this->input->post('user_vendor_mobile', TRUE));
			$user_vendor_level 			= strip_tags($this->input->post('user_vendor_level', TRUE));
			$user_vendor_pass 			= strip_tags($this->input->post('user_vendor_pass', TRUE));
			$user_vendor_reg_user		= strip_tags($this->input->post('user_vendor_reg_user', TRUE));
		
			
			if(!empty($user_vendor_vendor) or !empty($user_vendor_branch) or !empty($user_vendor_service) or !empty($user_vendor_name) or !empty($user_vendor_email) or !empty($user_vendor_mobile) or !empty($user_vendor_level) or !empty($user_vendor_pass) ){
			 
				$data_post = array('user_vendor_code'=>$user_vendor_code, 'user_vendor_name'=>$user_vendor_name , 'user_vendor_vendor'=>$user_vendor_vendor , 'user_vendor_branch'=>$user_vendor_branch , 'user_vendor_service'=>$user_vendor_service , 'user_vendor_email'=>$user_vendor_email , 'user_vendor_mobile'=>$user_vendor_mobile , 'user_vendor_level'=>$user_vendor_level, 'user_vendor_pass'=>getHashedPassword($user_vendor_pass), 'user_vendor_reg_date'=>date('Y-m-d H:i:s'), 'user_vendor_reg_user'=>$user_vendor_reg_user, 'user_vendor_status'=>1, 'user_vendor_drop'=>0);
				$data_post = $this->security->xss_clean($data_post);

				$count = $this->m_user_vendor->check_data_exists(array('user_vendor_code' => $user_vendor_code));
				if ($count > 0){
					$this->response([
						'status' => FALSE,
						'message' => 'user_vendor_code already exists'
					], REST_Controller::HTTP_BAD_REQUEST);
				}
				else{
					$result = $this->m_user_vendor->insert_data($data_post);
				
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
			else{
				$this->response([
					'status' => FALSE,
					'message' => 'Ada data kosong'
				], REST_Controller::HTTP_BAD_REQUEST);
			}
			
		}
	
		public function update_post(){
		
			$user_vendor_code 			= strip_tags($this->input->post('user_vendor_code', TRUE));
			$user_vendor_vendor 		= strip_tags($this->input->post('user_vendor_vendor', TRUE));
			$user_vendor_branch 		= strip_tags($this->input->post('user_vendor_branch', TRUE));
			$user_vendor_service 		= strip_tags($this->input->post('user_vendor_service', TRUE));
			$user_vendor_name 			= strip_tags($this->input->post('user_vendor_name', TRUE));
			$user_vendor_email 			= strip_tags($this->input->post('user_vendor_email', TRUE));
			$user_vendor_mobile 		= strip_tags($this->input->post('user_vendor_mobile', TRUE));
			$user_vendor_level 			= strip_tags($this->input->post('user_vendor_level', TRUE));
			//$user_vendor_pass 			= strip_tags($this->input->post('user_vendor_pass', TRUE));
			$user_vendor_chg_user		= strip_tags($this->input->post('user_vendor_chg_user', TRUE));
			$user_vendor_status			= strip_tags($this->input->post('user_vendor_status', TRUE));
			$user_vendor_drop				= strip_tags($this->input->post('user_vendor_drop', TRUE));
		

			if(!empty($user_vendor_vendor) or !empty($user_vendor_branch) or !empty($user_vendor_service) or !empty($user_vendor_name) or !empty($user_vendor_email) or !empty($user_vendor_mobile) or !empty($user_vendor_level) ){
			 
				$data_post = array('user_vendor_name'=>$user_vendor_name , 'user_vendor_vendor'=>$user_vendor_vendor , 'user_vendor_branch'=>$user_vendor_branch , 'user_vendor_service'=>$user_vendor_service , 'user_vendor_email'=>$user_vendor_email , 'user_vendor_mobile'=>$user_vendor_mobile , 'user_vendor_level'=>$user_vendor_level, 'user_vendor_chg_date'=>date('Y-m-d H:i:s'), 'user_vendor_chg_user'=>$user_vendor_chg_user, 'user_vendor_status'=>$user_vendor_status, 'user_vendor_drop'=>$user_vendor_drop);
				$data_post = $this->security->xss_clean($data_post);

				$result = $this->m_user_vendor->update_data($data_post, $user_vendor_code);
			
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
			else{
				$this->response([
					'status' => FALSE,
					'message' => 'Ada data kosong'
				], REST_Controller::HTTP_BAD_REQUEST);
			}
		}
		
		public function delete_post(){
			$user_vendor_code = strip_tags($this->input->post('user_vendors_code', TRUE));

			$result = $this->m_user_vendor->delete_data($user_vendor_code);

			if($result[0]){
				$this->response([
					'status' => TRUE,
					'message' => 'success'
				], REST_Controller::HTTP_OK);
			}
			else{
				$this->response([
					'status' => FALSE,
					'message' => 'failure some error occured:'.$result[1]
				], REST_Controller::HTTP_BAD_REQUEST);
			}
		}

	}
