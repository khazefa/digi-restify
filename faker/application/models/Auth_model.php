<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Auth_model extends CI_Model
{
    private $tbl_users = 'users';
    private $primKey = 'user_id';
    private $indexKey = 'user_key';

    function __construct()
    {
        parent::__construct();
	}

	/**
	 * This function used to check the login credentials of the user
	 * @param string $username : This is username of the user
	 * @param string $password : This is encrypted password of the user
	 */
	function auth_default($username, $password)
	{
		$this->db->select('u.user_id, u.user_key, u.user_pass, u.user_name, u.user_email');
		$this->db->from($this->tbl_users.' as u');
		$this->db->where('u.user_key', $username);
		$query = $this->db->get();

		$user = $query->result();

		if(!empty($user)){
			if(verifyHashedPassword($password, $user[0]->user_pass)){
				return $user;
			} else {
				return array();
			}
		} else {
			return array();
		}
	}

	/**
	 * This function used to check the login credentials of the user
	 * @param string $email : This is email of the user
	 * @param string $password : This is encrypted password of the user
	 */
	function auth_email($email, $password)
	{
		$this->db->select('u.user_id, u.user_email, u.user_pass, u.user_name, u.user_email');
		$this->db->from($this->tbl_users.' as u');
		$this->db->where('u.user_email', $email);
		$query = $this->db->get();

		$user = $query->result();

		if(!empty($user)){
			if(verifyHashedPassword($password, $user[0]->user_pass)){
				return $user;
			} else {
				return array();
			}
		} else {
			return array();
		}
	}

	/**
	 * This function used to check email exists or not
	 * @param {string} $email : This is users email id
	 * @return {boolean} $result : TRUE/FALSE
	 */
	function check_email_exist($email)
	{
		$this->db->select('user_email');
		$this->db->where('user_email', $email);
		$query = $this->db->get($this->tbl_users);

		if ($query->num_rows() > 0){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * This function used to insert reset password data
	 * @param {array} $data : This is reset password data
	 * @return {boolean} $result : TRUE/FALSE
	 */
	function reset_password_user($data)
	{
		try {
			$query = $this->db->insert('reset_password', $data);
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
	 * This function is used to get customer information by email-id for forget password email
	 * @param string $email : Email id of customer
	 * @return object $result : Information of customer
	 */
	function get_info_by_email($email)
	{
		$this->db->select('user_key, user_email, user_name');
		$this->db->from($this->tbl_users);
		$this->db->where('user_email', $email);
		$query = $this->db->get();

		return $query->result();
	}

	/**
	 * This function used to check correct activation deatails for forget password.
	 * @param string $email : Email id of user
	 * @param string $activation_id : This is activation string
	 */
	function check_activation_details($email, $activation_id)
	{
		$this->db->select('res_id');
		$this->db->from('reset_password');
		$this->db->where('email', $email);
		$this->db->where('activation_id', $activation_id);
		$query = $this->db->get();
		return $query->num_rows();
	}

	// This function used to create new password by reset link
	function create_password($email, $password)
	{
		$this->db->where('user_email', $email);
		$this->db->update($this->tbl_users, array('user_pass'=>getHashedPassword($password)));
		$this->db->delete('reset_password', array('email'=>$email));
	}
}
