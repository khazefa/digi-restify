<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Service extends REST_Controller {
		function __construct(){
			parent::__construct(); // Construct the parent class
			$this->load->library('datatables'); //load library ignited-dataTable
			$this->load->model('Service_model','m_service'); // Call model to represent database table
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

			$results = $this->m_service->json();
			$this->response([
				'status' => TRUE,
				'message' => 'data available '.$group,
				'results' => $results
			], REST_Controller::HTTP_OK);
		}
		
		public function input_post() {
			//$data = json_decode(file_get_contents("php://input"));
			
			$Service_code 			= date('ymd').random_string('nozero', 3);
			$Service_name 			= strip_tags($this->input->post('services_name', TRUE));
		
			
			if( !empty($Service_name) ){
			 
				$data_post = array('services_code'=>$Service_code, 'services_name'=>$Service_name );
				$data_post = $this->security->xss_clean($data_post);

				$count = $this->m_service->check_data_exists(array('services_code' => $Service_code));
				if ($count > 0){
					$this->response([
						'status' => FALSE,
						'message' => 'Service_code already exists'
					], REST_Controller::HTTP_BAD_REQUEST);
				}
				else{
					$result = $this->m_service->insert_data($data_post);
				
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
		
			$Service_code 			= strip_tags($this->input->post('services_code', TRUE));
			$Service_name 			= strip_tags($this->input->post('services_name', TRUE));

			if(!empty($Service_name) ){
				$data_post = array('services_name'=>$Service_name);
				$data_post = $this->security->xss_clean($data_post);

				$result = $this->m_service->update_data($data_post, $Service_code);
			
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
			$Service_code = strip_tags($this->input->post('services_code', TRUE));

			$result = $this->m_service->delete_data($Service_code);

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
