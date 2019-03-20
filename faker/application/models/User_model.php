<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model
{
    private $tbl_users = 'users';
    private $primKey = 'user_id';
    private $indexKey = 'user_key';
    private $order = array('user_name' => 'asc'); // default order

    public function __construct()
    {
        parent::__construct();
    }
 
    public function get_total_rows()
    {
        $this->db->from($this->tbl_users);
        return $this->db->count_all_results();
	}

	public function get_data($arrWhere = array(), $arrOrder = array(), $limit = 0)
	{
		$limit = $limit == 0 ? $this->config->item('api_limit_feed') : $limit;
        $rs = array();
        //Flush Param
        $this->db->flush_cache();
        
        $this->db->select('user_id, user_key, user_name, user_email, created_at');
        $this->db->from($this->tbl_users);

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

	public function proccess_dbase($act, $param = array(), $filter = array()) {
        try {
			$affected_rows = 0;
            switch ($act):
                case "insert":
					$this->db->trans_begin();
					$query = $this->db->insert($this->tbl_users, $param);
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
                    $query = $this->db->update($this->tbl_users, $param, array($this->indexKey => $filter['ukey']));
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

                case "delete":
					$this->db->trans_begin();
                    $query = $this->db->delete($this->tbl_users, $param, array($this->indexKey => $filter['ukey']));
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
	
	
}
