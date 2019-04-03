<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Branch extends REST_Controller {
		function __construct(){
			parent::__construct(); // Construct the parent class
			$this->load->library('datatables'); //load library ignited-dataTable
			$this->load->model('Branch_model','m_branch'); // Call model to represent database table
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

			$results = $this->m_branch->json();
			$this->response([
				'status' => TRUE,
				'message' => 'data available '.$group,
				'results' => $results
			], REST_Controller::HTTP_OK);
		}
		
		public function input_post() {
			//$data = json_decode(file_get_contents("php://input"));
			
			$vendor_code 			= strip_tags($this->input->post('vendor_code', TRUE));
			$branch_code 			= $vendor_code.random_string('nozero', 3);
			$branch_name 			= strip_tags($this->input->post('branch_name', TRUE));
			$branch_address		= strip_tags($this->input->post('branch_address', TRUE));
			$branch_area			= strip_tags($this->input->post('branch_area', TRUE));
			$branch_district	= strip_tags($this->input->post('branch_district', TRUE));
			$branch_city			= strip_tags($this->input->post('branch_city', TRUE));
			$branch_province	= strip_tags($this->input->post('branch_province', TRUE));
			$branch_postcode	= strip_tags($this->input->post('branch_postcode', TRUE));
			$branch_phone 		= strip_tags($this->input->post('branch_phone', TRUE));
			$branch_mobile 		= strip_tags($this->input->post('branch_mobile', TRUE));
			$branch_reg_user	= strip_tags($this->input->post('branch_reg_user', TRUE));
			$branch_lat				= strip_tags($this->input->post('branch_lat', TRUE));
			$branch_lng				= strip_tags($this->input->post('branch_lng', TRUE));
			
			if(!empty($vendor_code) && !empty($branch_name) && !empty($branch_mobile)){
			 
				$data_post = array('branch_code'=>$branch_code, 'branch_name'=>$branch_name, 'vendor_code'=>$vendor_code, 'branch_address'=>$branch_address, 'branch_area'=>$branch_area, 'branch_district'=>$branch_district, 'branch_city'=>$branch_city, 'branch_province'=>$branch_province, 'branch_postcode'=>$branch_postcode, 'branch_phone'=>$branch_phone, 'branch_mobile'=>$branch_mobile, 'branch_reg_date'=>date('Y-m-d H:i:s'), 'branch_reg_user'=>$branch_reg_user, 'branch_status'=>1, 'branch_drop'=>0, 'branch_pic'=>$branch_reg_user, 'branch_lat'=>$branch_lat, 'branch_lng'=>$branch_lng );
				$data_post = $this->security->xss_clean($data_post);

				$count = $this->m_branch->check_data_exists(array('branch_code' => $branch_code));
				if ($count > 0){
					$this->response([
						'status' => FALSE,
						'message' => 'branch_code already exists'
					], REST_Controller::HTTP_BAD_REQUEST);
				}
				else{
					$result = $this->m_branch->insert_data($data_post);
				
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
		
			//$vendor_code 			= strip_tags($this->input->post('vendor_code', TRUE));
			$branch_code 			= strip_tags($this->input->post('branch_code', TRUE));
			$branch_name 			= strip_tags($this->input->post('branch_name', TRUE));
			$branch_address		= strip_tags($this->input->post('branch_address', TRUE));
			$branch_area			= strip_tags($this->input->post('branch_area', TRUE));
			$branch_district	= strip_tags($this->input->post('branch_district', TRUE));
			$branch_city			= strip_tags($this->input->post('branch_city', TRUE));
			$branch_province	= strip_tags($this->input->post('branch_province', TRUE));
			$branch_postcode	= strip_tags($this->input->post('branch_postcode', TRUE));
			$branch_phone 		= strip_tags($this->input->post('branch_phone', TRUE));
			$branch_mobile 		= strip_tags($this->input->post('branch_mobile', TRUE));
			$branch_pic				= strip_tags($this->input->post('branch_pic', TRUE));
			$branch_lat				= strip_tags($this->input->post('branch_lat', TRUE));
			$branch_lng				= strip_tags($this->input->post('branch_lng', TRUE));
			$branch_status		= strip_tags($this->input->post('branch_status', TRUE));
			$branch_drop			= strip_tags($this->input->post('branch_drop', TRUE));

			if(!empty($branch_name) && !empty($branch_mobile)){
				$data_post = array('branch_name'=>$branch_name, 'branch_address'=>$branch_address, 'branch_area'=>$branch_area, 'branch_district'=>$branch_district, 'branch_city'=>$branch_city, 'branch_province'=>$branch_province, 'branch_postcode'=>$branch_postcode, 'branch_phone'=>$branch_phone, 'branch_mobile'=>$branch_mobile,  'branch_status'=>$branch_status, 'branch_drop'=>$branch_drop, 'branch_pic'=>$branch_pic, 'branch_lat'=>$branch_lat, 'branch_lng'=>$branch_lng );
				$data_post = $this->security->xss_clean($data_post);

				$result = $this->m_branch->update_data($data_post, $branch_code);
			
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
			$branch_code = strip_tags($this->input->post('branch_code', TRUE));

			$result = $this->m_branch->delete_data($branch_code);

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
