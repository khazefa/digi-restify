<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Vendor extends REST_Controller {
		function __construct(){
			parent::__construct(); // Construct the parent class
			$this->load->library('datatables'); //load library ignited-dataTable
			$this->load->model('Vendor_model','m_vendor'); // Call model to represent database table
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

			$results = $this->m_vendor->json();
			$this->response([
				'status' => TRUE,
				'message' => 'data available '.$group,
				'results' => $results
			], REST_Controller::HTTP_OK);
		}
		
		public function input_post() {
			//$data = json_decode(file_get_contents("php://input"));
			
			$vendor_code 			= date('ymd'). random_string('nozero', 6);
			$vendor_name 			= strip_tags($this->input->post('vendor_name', TRUE));
			$vendor_owner 		= strip_tags($this->input->post('vendor_owner', TRUE));
			$vendor_phone 		= strip_tags($this->input->post('vendor_phone', TRUE));
			$vendor_mobile 		= strip_tags($this->input->post('vendor_mobile', TRUE));
			$vendor_reg_user	= strip_tags($this->input->post('vendor_reg_user', TRUE));
			
			if(!empty($vendor_name) && !empty($vendor_owner) && !empty($vendor_mobile)){
			 
				$data_post = array('vendor_code'=>$vendor_code, 'vendor_name'=>$vendor_name, 'vendor_owner'=>$vendor_owner, 'vendor_phone'=>$vendor_phone, 'vendor_mobile'=>$vendor_mobile, 'vendor_reg_user'=>$vendor_reg_user, 'vendor_reg_date'=>date('Y-m-d H:i:s'), 'vendor_status'=>1, 'vendor_drop'=>0, 'created_at'=>date('Y-m-d H:i:s') );
				$data_post = $this->security->xss_clean($data_post);

				$count = $this->m_vendor->check_data_exists(array('vendor_code' => $vendor_code));
				if ($count > 0){
					$this->response([
						'status' => FALSE,
						'message' => 'vendorcode already exists'
					], REST_Controller::HTTP_BAD_REQUEST);
				}
				else{
					$result = $this->m_vendor->insert_data($data_post);
				
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
		
			$vendor_code 			= strip_tags($this->input->post('vendor_code', TRUE));
			$vendor_name 			= strip_tags($this->input->post('vendor_name', TRUE));
			$vendor_owner 		= strip_tags($this->input->post('vendor_owner', TRUE));
			$vendor_phone 		= strip_tags($this->input->post('vendor_phone', TRUE));
			$vendor_mobile 		= strip_tags($this->input->post('vendor_mobile', TRUE));
			$vendor_status		= strip_tags($this->input->post('vendor_status', TRUE));
			$vendor_drop			= strip_tags($this->input->post('vendor_drop', TRUE));

			if(!empty($vendor_name) && !empty($vendor_owner) && !empty($vendor_mobile)){
				$data_post = array('vendor_name'=>$vendor_name, 'vendor_owner'=>$vendor_owner, 'vendor_phone'=>$vendor_phone, 'vendor_mobile'=>$vendor_mobile, 'vendor_status'=>$vendor_status, 'vendor_drop'=>$vendor_drop, 'updated_at'=>date('Y-m-d H:i:s') );
				
				$data_post = $this->security->xss_clean($data_post);

				$result = $this->m_vendor->update_data($data_post, $vendor_code);
			
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
			$vendor_code = strip_tags($this->input->post('vendor_code', TRUE));

			$result = $this->m_vendor->delete_data($vendor_code);

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
