<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Class User_model.php.
 * Desc: User Model
 * @author: Sigit Prayitno
 * @email: cybergitt@gmail.com
 */

class Vendor_model extends CI_Model
{
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

	public function get_data($arrWhere = array(), $arrOrder = array(), $limit = 0){
		$limit = $limit == 0 ? $this->config->item('api_limit_feed') : $limit;
		$rs = array();
		//Flush Param
		$this->db->flush_cache();
		
		$this->db->select('user_id, user_key, user_name, user_email, created_at');
		$this->db->from($this->tbl_vendors);

		if(empty($arrWhere)){
				//Limit
	if ($limit > 0){
		$this->db->limit($limit);
	}
		
	//Order By
	if (count($arrOrder) > 0){
		foreach ($arrOrder as $strField => $strValue){
			$this->db->order_by($strField, $strValue);
		}
	}
				$query = $this->db->get();
				$rs = $query->result_array();
		}else{
				foreach ($arrWhere as $strField => $strValue){
						if (is_array($strValue)){
								$this->db->where_in($strField, $strValue);
						}else{
								if(strpos(strtolower($strField), '_date1') !== false){
										$strField = substr($strField, 0, -6);
										if(!empty($strValue)){
												$this->db->where("$strField >= '".$strValue."' ");
										}
								}elseif(strpos(strtolower($strField), '_date2') !== false){
										$strField = substr($strField, 0, -6);
										if(!empty($strValue)){
												$this->db->where("$strField <= '".$strValue."' ");
										}
								}else{
										$this->db->where($strField, $strValue);
								}
						}
				}
	
		//Limit
		if ($limit > 0){
			$this->db->limit($limit);
		}
			
		//Order By
		if (count($arrOrder) > 0){
			foreach ($arrOrder as $strField => $strValue){
				$this->db->order_by($strField, $strValue);
			}
		}
	
				$query = $this->db->get();
				$rs = $query->result_array();
		}
		
		return $rs;
  }

    // This function used to get list data by this table only, not join table, with like parameters
    public function get_data_like($arrLike = array(), $arrOrder = array()){
        $rs = array();
        //Flush Param
        $this->db->flush_cache();
        
        $this->db->select('user_id, user_key, user_name, user_email, created_at');
        $this->db->from($this->tbl_vendors);

        if(empty($arrLike)){
            $rs = array();
        }else{
			foreach ($arrLike as $strField => $strValue){
				$this->db->like($strField, $strValue);
			}
            $query = $this->db->get();
            $rs = $query->result_array();
        }
        
        //Order By
        if (count($arrOrder) > 0){
            foreach ($arrOrder as $strField => $strValue){
                $this->db->order_by($strField, $strValue);
            }
        }
        
        return $rs;
    }

    /**
     * This function used to get data information by id
     * @param number $id : This is id
     * @return array $result : This is data information
     */
    public function get_data_info($id)
    {
        $this->db->select('*');
        $this->db->from($this->tbl_vendors);
        $this->db->where($this->primKey, $id);
        $query = $this->db->get();
        
        return $query->result();
    }
    
    /**
     * This function is used to add new data to system
     * @return number $insert_id : This is last inserted id
     */
    public function insert_data($dataPost)
    {
		try {
			$query = $this->db->insert($this->tbl_vendors, $dataPost);
			$insert_id = $this->db->insert_id();
			if ($query === FALSE){
                throw new Exception();
			}else{
				return array(TRUE, $insert_id);
			}
				
        } catch (Exception $e) {
            $errNo = $this->db->_error_number();
            return array(FALSE, $errNo);
        }
    }
    
    /**
     * This function is used to add new data to system
	 * Use Transactions only for inserting multiple data
     * @return number $insert_id : This is last inserted id
     */
    public function insert_bulk_data($dataPost)
    {
		try {
			$this->db->trans_start();
			$query = $this->db->insert($this->tbl_vendors, $dataPost);
			$this->db->trans_complete();
			$insert_id = 0;
			if ($this->db->trans_status() === FALSE)
			{
				$this->db->trans_rollback();
			}
			else
			{
				// $insert_id = $this->db->insert_id();
				$insert_id = $this->db->affected_rows();
				$this->db->trans_commit();
			}
			if ($query === FALSE){
                throw new Exception();
			}else{
				return array(TRUE, $insert_id);
			}
				
        } catch (Exception $e) {
            $errNo = $this->db->_error_number();
            return array(FALSE, $errNo);
        }
    }

    /**
     * This function is used to update the data information
     * @param array $dataInfo : This is data updated information
     * @param number $id : This is data id
     */
    public function update_data($dataPost, $id)
    {
		try {
			$query = $this->db->update($this->tbl_vendors, $dataPost, array($this->indexKey => $id));
			$affected_rows = $this->db->affected_rows();
		
			if ($query === FALSE){
				throw new Exception();
			}else{
				return array(TRUE, $affected_rows);
			}
        } catch (Exception $e) {
            $errNo = $this->db->_error_number();
            return array(FALSE, $errNo);
        }
    }
    
    /**
     * This function is used to delete the data information
     * @param number $id : This is data id
     * @return boolean $result : TRUE / FALSE
     */
    public function delete_data($id)
    {
		try {
			$query = $this->db->delete($this->tbl_vendors, array($this->indexKey => $id));
			$affected_rows = $this->db->affected_rows();
		
			if ($query === FALSE){
				throw new Exception();
			}else{
				return array(TRUE, $affected_rows);
			}
        } catch (Exception $e) {
            $errNo = $this->db->_error_number();
            return array(FALSE, $errNo);
        }
    }

	// This function is purposed for experimental only //cybgt
	public function process_dbase($act, $param = array(), $filter = array()) {
        try {
			$affected_rows = 0;
            switch ($act):
                case "insert":
					$this->db->trans_begin();
					$query = $this->db->insert($this->tbl_vendors, $param);
					$this->db->trans_complete();
					$insert_id = 0;
					if ($this->db->trans_status() === FALSE)
					{
						$this->db->trans_rollback();
					}
					else
					{
						$this->db->trans_commit();
						$insert_id = $this->db->insert_id();
					}
					$affected_rows = $insert_id;
                    break;

                case "update":
					$this->db->trans_begin();
                    $query = $this->db->update($this->tbl_vendors, $param, array($this->indexKey => $filter['ukey']));
					$this->db->trans_complete();
					$updated_id = 0;
					if ($this->db->trans_status() === FALSE)
					{
						$this->db->trans_rollback();
					}
					else
					{
						$this->db->trans_commit();
						$updated_id = $this->db->affected_rows();
					}
					$affected_rows = $updated_id;
                    break;

                case "delete":
					$this->db->trans_begin();
                    $query = $this->db->delete($this->tbl_vendors, $param, array($this->indexKey => $filter['ukey']));
					$this->db->trans_complete();
					if ($this->db->trans_status() === FALSE)
					{
						$this->db->trans_rollback();
					}
					else
					{
						$this->db->trans_commit();
					}
					$affected_rows = $this->db->affected_rows();
                    break;

            endswitch;

            if ($query === FALSE)
                throw new Exception();

            return $affected_rows;
        } catch (Exception $e) {
            $errNo = $this->db->_error_number();
            return $errNo;
        }
	}

    /**
     * This function is used to check whether field is already exist or not
     * @param {mixed} $arrWhere : This is param
     */
    public function check_data_exists($arrWhere = array())
    {
         //Flush Param
         $this->db->flush_cache();
         $this->db->from($this->tbl_vendors);
         //Criteria
         if (count($arrWhere) > 0){
             foreach ($arrWhere as $strField => $strValue){
                 if (is_array($strValue)){
                     $this->db->where_in($strField, $strValue);
                 }else{
                     $this->db->where($strField, $strValue);
                 }
             }
         }
         return $this->db->count_all_results();
    }
}
