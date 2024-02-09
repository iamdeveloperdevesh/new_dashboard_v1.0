<?PHP
class Loginmodel extends CI_Model
{
	function login($username,$password)
	{
		$whr = "user_name = '".$username."' AND password = '".$password."' ";		
		$this->db->select('user_id, user_name, email_id, first_name , last_name, role_id, user_type, frecord_id');
		$this->db->from('tbl_admin_users');
		$this->db->where($whr);
		$this->db->limit(1);
		$query = $this->db->get();		
		if($query -> num_rows() == 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}
	
	function getEmailContent($mail_key)
	{	
		$whr = "mail_key ='".$mail_key."' ";		
		$this->db->select('*');
		$this->db->from('tbl_emailcontents');
		
		$query = $this->db->get();
		
		if($query -> num_rows() == 1)
		{
			$res = $query->result_array();
			return $res[0];
		}
		else
		{
			return false;
		}
	}
	
	 function getContentForgetPass($eid)
	{
		$this -> db -> select('fromemail,toemail,subject,content,eid');
		$this -> db -> from('tbl_emailcontents');
		$this -> db -> where('eid', $eid);
		$query = $this -> db -> get();	  
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	
	} 
	
	function forgotpass($email)
	{
		$where = "email_id = '" . $email . "'"; // Use a variable name like $where instead of $whr for clarity
	
		$this->db->select('user_name, email_id, user_name, password');
		$this->db->from('tbl_admin_users');
		$this->db->where($where);
		$this->db->limit(1);
	
		$query = $this->db->get();
	
		if ($query->num_rows() == 1) {
			return $query->row(); // Use row() instead of result() for a single row
		} else {
			return false;
		}
	}

	function sendVerificationEmail($user_name, $email)
    {
        $verification_token = md5(uniqid()); // Generate a unique token (You might want to use a more secure method)
        
        // Save the token in the database
        $data = array('verification_token' => $verification_token);
        $this->db->where('user_name', $user_name);
		$this->db->set($data);
        $this->db->update('tbl_admin_users');

		

        // Compose the verification email
        $subject = "Email Verification";
        $message = "Click the following link to verify your email: " . base_url("verify_email/{$verification_token}");

        // Send the email (you may use a library like PHPMailer or CI's Email Class)
        mail($email, $subject, $message);
    }
	
	
	public function changePassword($user_name, $new_password)
    {
        // $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // Hash the new password
		$hashed_password = encrypt_decrypt_password($new_password);

        // Update the user's password in the database
        $data = array(
            'employee_password' => $hashed_password,
        );
        $this->db->where('user_name', $user_name);
        $this->db->update('master_employee', $data);

        return ($this->db->affected_rows() != 1) ? false : true;
    }

	
		public function save_otp($postdata){
			$this->db->insert('forgot_pass',$postdata);
			return ($this->db->affected_rows() != 1) ? false : true;
		}
		public function email_id($user_name){
			
			$this->db->select('email_id');
			$this->db->from('master_employee');
			$this->db->where("user_name = '" . $user_name . "'");
			$this->db->limit(1);
			$query = $this->db->get();
	
			if ($query->num_rows() == 1) {
				$data =  $query->result_array();
				$res = $data[0]['email_id'];
				
				return $res; // Use row() instead of result() for a single row
			} else {
				return false;
			}
		}
	}

?>