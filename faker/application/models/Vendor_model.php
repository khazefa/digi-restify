<?php 
	if(!defined('BASEPATH')) exit('No direct script access allowed');
	/**
	* Class User_model.php.
	* Desc: User Model
	* @author: Sigit Prayitno
	* @email: cybergitt@gmail.com
	*/

	class Vendor_model extends CI_Model{
		private $tbl_vendors = 'vendor';
		private $primKey = 'vendor_id';
		private $indexKey = 'vendor_code';
		//private $order = array('user_name' => 'asc'); // default order

		public function __construct()
		{
			parent::__construct();
		}
		
		public function json() {
			$this->datatables->select('vendor_code AS ven_cd, vendor_name AS ven_nm, vendor_owner AS owner, created_at');
			$this->datatables->from($this->tbl_vendors);
			//$this->datatables->where('user_key <>', 'cybergitt');
			// $this->datatables->join('country', 'city.CountryCode = country.Code');
			$this->datatables->add_column('button', '<a href="javascript:void(0);" data-id="$1">edit</a> | <a href="javascript:void(0);" data-id="$1">delete</a>', 'vendor_code');
			return $this->datatables->generate();
		}
	 
		public function get_total_rows(){
			$this->db->from($this->tbl_vendors);
			return $this->db->count_all_results();
		}

    public function insert_data($dataPost){
			try {
				$query = $this->db->insert($this->tbl_vendors, $dataPost);
				$insert_id = $this->db->insert_id();
				if ($query === FALSE){
					throw new Exception();
				}
				else{
					return array(TRUE, $insert_id);
				}
					
      } 
			catch (Exception $e) {
				$errNo = $this->db->_error_number();
				return array(FALSE, $errNo);
			}
    }
    
		public function update_data($dataPost, $id){
			try {
				$query = $this->db->update($this->tbl_vendors, $dataPost, array($this->indexKey => $id));
				$affected_rows = $this->db->affected_rows();
			
				if ($query === FALSE){
					throw new Exception();
				}
				else{
					return array(TRUE, $affected_rows);
				}
      } 
			catch (Exception $e) {
				$errNo = $this->db->_error_number();
				return array(FALSE, $errNo);
      }
    }
    
    public function delete_data($id){
			try {
				$query = $this->db->delete($this->tbl_vendors, array($this->indexKey => $id));
				$affected_rows = $this->db->affected_rows();
			
				if ($query === FALSE){
					throw new Exception();
				}
				else{
					return array(TRUE, $affected_rows);
				}
      } 
			catch (Exception $e) {
				$errNo = $this->db->_error_number();
				return array(FALSE, $errNo);
			}
    }

    public function check_data_exists($arrWhere = array()){
			//Flush Param
			$this->db->flush_cache();
			$this->db->from($this->tbl_vendors);
			//Criteria
			if (count($arrWhere) > 0){
				foreach ($arrWhere as $strField => $strValue){
					if (is_array($strValue)){
						$this->db->where_in($strField, $strValue);
					}
					else{
						$this->db->where($strField, $strValue);
					}
				}
			}
			return $this->db->count_all_results();
    }
	}
