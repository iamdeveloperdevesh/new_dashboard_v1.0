<?PHP
class Apimodel extends CI_Model
{
	function insertData($tbl_name,$data_array,$sendid = NULL)
	{
	 	$this->db->insert($tbl_name,$data_array);
	 	$result_id = $this->db->insert_id();
	 	
	 	/*echo $result_id;
	 	exit;*/
	 	
	 	if($sendid == 1)
	 	{
	 		//return id
	 		return $result_id;
	 	}
	}
	function getProductDetailsAll($id, $sort_by = '', $order = '', $policy_id_arr = [])
	{
		$this->db->select('mp.*,master_plan.creditor_id,master_plan.plan_name,master_plan.product_type_id,mpst.policy_sub_type_name,mpst.code as policy_sub_type_code,mpsi.suminsured_type_id as sitype_id,mppb.si_premium_basis_id as basis_id,mc.creaditor_name as creditor_name,mc.creditor_logo');
		$this->db->where('mp.plan_id', $id);
		$this->db->where('mp.isactive', 1);
		$this->db->where('mpsi.isactive', 1);
		$this->db->where('mppb.isactive', 1);
		$this->db->join('master_plan', 'mp.plan_id = master_plan.plan_id');
		$this->db->join('master_ceditors as mc', 'mc.creditor_id = master_plan.creditor_id');
		$this->db->join('master_policy_sub_type as mpst', 'mpst.policy_sub_type_id = mp.policy_sub_type_id');
		$this->db->join('master_policy_premium_basis_mapping as mppb', 'mppb.master_policy_id = mp.policy_id');
		$this->db->join('master_policy_si_type_mapping as mpsi', 'mpsi.master_policy_id = mp.policy_id');
		
		if(!empty($policy_id_arr)){

			$this->db->where_in('mp.policy_id', $policy_id_arr);
		}
		
		if($sort_by && $order){

			$this->db->order_by($sort_by,$order);
		}
		
		$data = $this->db->get('master_policy as mp')->result();
		return $data;
	}
		function getPolicyDetails($id)
	{
		$this->db->select('mp.*,mpst.code as policy_sub_type_code,mpstm.suminsured_type_id,mpst.policy_sub_type_id,mpst.policy_sub_type_name');
		$this->db->join('master_policy_sub_type as mpst', 'mpst.policy_sub_type_id = mp.policy_sub_type_id');
		$this->db->join('master_policy_si_type_mapping as mpstm', 'mpstm.master_policy_id = mp.policy_id');
		$this->db->where('mp.policy_id', $id);
		$this->db->where('mpstm.isactive', 1);
		$data = $this->db->get('master_policy as mp')->result();
		return $data;
	}
	function getGeneratedPremiums($data)
	{
		$response = [];

		$this->db->select('*');
		$where = "lead_id=" . $data['lead_id'] . " and premium_with_tax is NOT NULL";
		$this->db->where($where);
		$data = $this->db->get('master_quotes')->result();
		//print_R($data);
		foreach ($data as $key => $policy) {
			$policyDetails = $this->getPolicyDetails($policy->master_policy_id)[0];

			$members = $this->getMembers();

			$member = array_filter($members, function ($member) use ($policy) {
				if ($policy->member_type_id == $member->id) return true;
			});

			$member = current($member);

			$premium = number_format(round($data[$key]->premium_with_tax, 2), 2, '.', '');

			if ($policyDetails->suminsured_type_id == 1) {
				$response[$policy->master_customer_id][$policyDetails->policy_sub_type_code . "-" . $member->member_type] =
					$premium;
			} else {
				$response[$policy->master_customer_id][$policyDetails->policy_sub_type_code] = 	$premium;
			}
		}

		return  $response;
	}
	function validator($array)
	{
		$validationMessages = [];

		foreach ($array as $key => $value) {
			if ($key == 'child_count' || $key == 'number_of_ci' || $key == 'hospi_cash_group_code' || $key == 'group_code_type') continue;
			$value = trim($value);
			if (!$value) {
				$validationMessages[] = "Invalid $key";
			}
		}

		return $validationMessages;
	}
    function insertDeposit($data){
        $result=$this->db->insert('cd_deposit',$data);
        if($result == true){
            return true;
        }else{
            return false;
        }
    }
    function insertDepositCover($data){
        $result=$this->db->insert('cover_enhanced',$data);
        if($result == true){
            return true;
        }else{
            return false;
        }
    }
	function getPerMileWisePremium($data)
	{
		$response = [
			'status' => false,
			'messages' => [],
			'rate' => null
		];

		if ($messages = $this->validator($data)) {
			$response['messages'] = $messages;
			return $response;
		}
		$response = [
			'status' => false,
			'messages' => [],
			'rate' => null
		];

		if(in_array($data['policy_sub_type_id'], [2,3]) && $data['hospi_cash_group_code'] != ''){

			$sql = "SELECT * FROM master_policy_premium_permile WHERE min_age<=" . $data['age'] . " AND max_age>=" . $data['age'] . " AND ". $data['group_code_type'] ." = '".$data['hospi_cash_group_code']."' AND master_policy_id=" . $data['policy_id'] . " AND isactive=1";
		}
		else{

			if (isset($data['number_of_ci']) && $data['number_of_ci'] > 0) {
				$sql = "SELECT * FROM master_policy_premium_permile WHERE min_age<=" . $data['age'] . " AND max_age>=" . $data['age'] . " and numbers_of_ci=" . $data['number_of_ci'] . " and tenure=" . $data['tenure'] . " and master_policy_id=" . $data['policy_id'] . " and isactive=1";
			} else {
				$sql = "SELECT * FROM master_policy_premium_permile WHERE min_age<=" . $data['age'] . " AND max_age>=" . $data['age'] . " and tenure=" . $data['tenure'] . " and master_policy_id=" . $data['policy_id'] . " and isactive=1";
			}
		}

		$query = $this->db->query($sql);

		$rate = $query->result();

		if (empty($rate)) {
			$response['messages'][] = "No rates found for policy";
			return $response;
		}

		$amount = ($data['sum_insured'] / 1000) * $rate[0]->policy_rate;

		$final_rate = $amount  *  (100 + (int) getConfigValue('tax_percent')) / 100;

		$response['rate'] = number_format(ceil($final_rate), 2, '.', '');
		$response['rate_without_tax'] = number_format(round($amount, 2), 2, '.', '');
		$response['status'] = true;
		$response['group_code'] = $rate[0]->group_code;
		$response['group_code_spouse'] = $rate[0]->group_code_spouse;

		return $response;
	}
		function getProposersAge($lead_id, $customer_id)
	{
		$age = null;
		$data = $this->getLeadDetails($lead_id);

		foreach ($data as $customer) {
			if ($customer->customer_id == $customer_id) {
				$age = date_diff(date_create($data[0]->dob), date_create('today'))->y;
				break;
			}
		}

		return $age;
	}
    function cdDashBoradDetailsNew($post){
        //  echo 123;exit;
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $condition = "mc.creditor_id='".$_POST['cid']."' AND ppd.payment_mode=4";
        //  $page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
        //  $rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
        $this -> db -> select('mc.creaditor_name,mc.creditor_id,(mc.initial_cd) as initial_cd,(mc.cd_threshold) as threshold,
        (select sum(cd.amount) from cd_deposit cd where cd.partner_id  = '.$_POST['cid'].')as cd_deposited  , count(apr.certificate_number)as COI_numbers,sum(apr.gross_premium)as premium');
        $this -> db -> from('master_policy as mp');
        $this -> db -> join('master_plan as p', 'p.plan_id = mp.plan_id', 'left');
        $this -> db -> join('master_ceditors as mc', 'mc.creditor_id  = mp.creditor_id', 'left');
        $this -> db -> join('api_proposal_response as apr', 'mp.policy_id  = apr.master_policy_id', 'left');
        //$this -> db -> join('cd_deposit as cd', 'cd.partner_id  = mp.creditor_id', 'left');
        $this -> db -> join('proposal_payment_details as ppd', 'ppd.lead_id  = apr.lead_id', 'left');
        $this->db->where("($condition)");
        //$this->db->limit($rows,$page);
        $query = $this -> db -> get();
        // echo $this->db->last_query();exit;
        if($query -> num_rows() >= 1)
        {
            $totcount = $query -> num_rows();

            return array("query_result" => $query->result(), "totalRecords" => $totcount);
        }
        else
        {
            return array("totalRecords" => 0);
        }
    }
    function fetchendoresement($post){
        //  echo 123;exit;
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $condition = "mp.creditor_id=".$_POST['cid']." and mp.plan_id=".$_POST['plan_id'];
        //  $page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
        //  $rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
        $this -> db -> select('p.plan_name,mc.creaditor_name,mp.original_file,mp.first_name,mp.last_name,mp.endorsement_type,mp.trace_id,mp.status,mp.created_on');
        $this -> db -> from('endorsement_details as mp');
        $this -> db -> join('master_plan as p', 'mp.plan_id = p.plan_id', 'left');
        $this -> db -> join('master_ceditors as mc', 'mc.creditor_id  = mp.creditor_id', 'left');

        $this->db->where($condition);
        // $this->db->limit($rows,$page);
        $query = $this -> db -> get();
        //echo $this->db->last_query();exit;
        if($query -> num_rows() >= 1)
        {
            $totcount = $query -> num_rows();

            return array("query_result" => $query->result(), "totalRecords" => $totcount);
        }
        else
        {
            return array("totalRecords" => 0);
        }
    }

    function coverDashBoradDetails($post){
        //  echo 123;exit;
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $condition = "mp.creditor_id=".$_POST['cid']." and mp.plan_id=".$_POST['plan_id'];
        //  $page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
        //  $rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
        $this -> db -> select('mc.creaditor_name,p.plan_name,ps.policy_sub_type_name,mp.policy_sub_type_id,mp.policy_id,mp.plan_id,mp.creditor_id,(mp.initial_cover) as initial_cover,(mp.cover_limit) as threshold,
      count(apr.certificate_number)as COI_numbers,sum(pp.cover)as cover');
        $this -> db -> from('master_policy as mp');
        $this -> db -> join('master_plan as p', 'mp.plan_id = p.plan_id', 'left');
        $this -> db -> join('master_ceditors as mc', 'mc.creditor_id  = mp.creditor_id', 'left');
        $this -> db -> join('api_proposal_response as apr', 'mp.policy_id  = apr.master_policy_id', 'left');
        $this -> db -> join('policy_member_plan_details as pp', 'mp.policy_id  = pp.policy_id', 'left');
        $this -> db -> join('master_policy_sub_type as ps', 'mp.policy_sub_type_id  = ps.policy_sub_type_id', 'left');

        $this->db->where($condition);
       // $this->db->limit($rows,$page);
        $query = $this -> db -> get();
         //echo $this->db->last_query();exit;
        if($query -> num_rows() >= 1)
        {
            $totcount = $query -> num_rows();

            return array("query_result" => $query->result(), "totalRecords" => $totcount);
        }
        else
        {
            return array("totalRecords" => 0);
        }
    }
    function cdDashBoradDetails($post)
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $table = "master_plan";
        $table_id = 'plan_id';
        $default_sort_column = 'p.plan_id';
        $default_sort_order = 'desc';

        $condition = "mp.creditor_id='".$_POST['cid']."' AND  ppd.payment_mode = 4 ";


        $this -> db -> select('count(certificate_number) as COI_numbers,mc.creaditor_name,p.plan_name,pt.policy_type_name,GROUP_CONCAT(pts.policy_sub_type_name) as policy_sub_type_name
 ,sum(apr.gross_premium) as premium');
        $this -> db -> from('api_proposal_response as apr');
        $this -> db -> join('master_ceditors as mc', 'mc.creditor_id="'.$_POST['cid'].'"', 'left');
        $this -> db -> join('master_policy as mp', 'mp.policy_id=apr.master_policy_id', 'left');
        $this->db->join('master_plan as p', 'p.plan_id = mp.plan_id','left');
        $this->db->join('master_policy_type as pt', 'p.policy_type_id = pt.policy_type_id','left');
        $this -> db -> join('master_policy_sub_type as pts', 'pts.policy_sub_type_id = mp.policy_sub_type_id', 'left');
        $this -> db -> join('proposal_payment_details as ppd', 'ppd.lead_id=apr.lead_id', 'left');

        $this->db->where("($condition)");
        //  $this->db->order_by($sort, $order);
        $this->db->group_by('p.plan_id');
        // $this->db->group_by('pts.policy_sub_type_id');
        //  $this->db->limit($rows,$page);
        $query = $this -> db -> get();

        //echo $this->db->last_query();exit;
        if($query -> num_rows() >= 1)
        {
            $totcount = $query -> num_rows();

            return array("query_result" => $query->result(), "totalRecords" => $totcount);
        }
        else
        {
            return array("totalRecords" => 0);
        }

    }

		function getLeadDetails($id)
	{
		$this->db->select('lead_details.*,mc.customer_id,mc.salutation,mc.first_name,mc.pan,mc.middle_name,mc.last_name,mc.gender,mc.dob,mc.email_id,mc.mobile_no as customer_mobile_no,mc.mobile_no2 as customer_mobile_no2,mc.city,mc.state,mc.address_line1,mc.address_line2,mc.address_line3,mc.pincode');
		$this->db->join('master_customer as mc', 'mc.lead_id = lead_details.lead_id');
		$data = $this->db->get_where('lead_details', array('lead_details.lead_id' => $id))->result();
		//print_R($data);
		return $data;
	}
		function getPolicyFamilyDetails($id)
	{
		$this->db->select('*');
		$this->db->from('master_policy_family_construct');
		$this->db->join('family_construct', 'master_policy_family_construct.member_type_id =family_construct.id');

		if (!is_array($id)) {

			$this->db->where(array('master_policy_id' => $id, 'master_policy_family_construct.isactive' => 1));
		} else {

			$this->db->where_in('master_policy_id', $id);
			$this->db->where(array('master_policy_family_construct.isactive' => 1));
		}

		$this->db->order_by("member_type_id", "asc");

		$query = $this->db->get();
		return $query->result();
	}
	function getMembers()
	{
		$data = $this->db->get_where('family_construct', array('isactive' => 1))->result();
		return $data;
	}

	function getpolicysubtypeofplan($plan)
	{
		$this->db->select('policy_sub_type_id');
		$data = $this->db->get_where('master_policy', array('plan_id' => $plan))->result();
		return $data;
	}
	
	/*
		 * Fetch records from multiple tables [Join Queries] with multiple condition, Sorting, Limit, Group By
	*/
	function getdata_join($main_table = array(), $join_tables = array(), $condition = null, $sort_by = null, $group_by = null) {
		$columns = isset($main_table[1]) ? $main_table[1] : array();
		$main_table = $main_table[0];

		$join_str = "";
		foreach ($join_tables as $join_table) {
			$join_str .= $join_table[0] . " join " . $join_table[1] . " on (" . $join_table[2] . ") ";
			if (isset($join_table[3])) {
				$columns = array_merge($columns, $join_table[3]);
			}
		}

		$columns = (sizeof($columns) > 0) ? implode(", ", $columns) : "*";

		if (is_null($condition) || $condition == "") {
			$condition = "1=1";
		}

		$sort_order = "";
		if (is_array($sort_by) && $sort_by != null) {
			foreach ($sort_by as $key => $val) {
				$sort_order .= ($sort_order == "") ? "order by $key $val" : ", $key $val";
			}
		}

		if ($group_by != null) {
			$group_by = "group by " . $group_by;
		}

		//$this->db->query($this->set_timezone_query);
		$sql = trim("select $columns from $main_table $join_str where $condition $group_by $sort_order");
		// echo $sql.'<br/><br/><br/>';
		// exit;
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	
	
	
	
	function insertBatchData($tbl_name,$data_array,$sendid = NULL)
	{
	 	$this->db->insert_batch($tbl_name,$data_array);
	 	$result_id = $this->db->insert_id();
	 	
	 	/*echo $result_id;
	 	exit;*/
	 	
	 	if($sendid == 1)
	 	{
	 		//return id
	 		return $result_id;
	 	}
	}
	
	function login_check($condition) {
		$this -> db -> select('i.employee_id , i.employee_fname,smc.creditor_id, i.employee_mname,i.employee_lname, i.employee_full_name, i.user_name,i.employee_code,i.email_id,i.mobile_number,i.role_id, r.role_name');
		$this -> db -> from('master_employee as i');
		$this -> db -> join('roles as r', 'i.role_id  = r.role_id', 'left');
        $this -> db -> join('sm_creditor_mapping as smc', 'smc.sm_id  = i.employee_id', 'left');
		$this -> db -> where("($condition)");
		
		$query = $this -> db -> get();
		//print_r($this->db->last_query());
		//exit;
	 
		if($query -> num_rows() >= 1)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
    
	function fetch_otp($mobile,$country_code){
		
		//valid check 15 minutes
		$current_date_time = date("Y-m-d H:i:s");
		$current_timstamp = strtotime($current_date_time);
		$current_timstamp = $current_timstamp - (15 * 60);
		$from_date_time = date("Y-m-d H:i:s", $current_timstamp);

		$otp_condition = "";
		$sql = $this->db->query("select * from tbl_otp_check where  mobile = ".$this->db->escape($mobile)." && country_code = ".$this->db->escape($country_code)." && (time_stamp >='".$from_date_time."' && time_stamp <='".$current_date_time."' ) ");
		if($sql->num_rows()>0){
			return $sql->result();
		}else{
			return false;
		}
	}
  
	function generate_otp($mobile,$country_code,$otp){
		$sql = $this->db->query("select * from tbl_otp_check where mobile = ".$this->db->escape($mobile).' And country_code ='.$this->db->escape($country_code) );
		$data = array(
               "mobile"=>$mobile,
               "country_code"=>$country_code,
               "otp" => $otp,
               "time_stamp"=>date('Y-m-d H:i:s')
		);
    
		if($sql->num_rows() > 0){
			$this->updatedataotp($data, "tbl_otp_check", "mobile", $mobile,"country_code", $country_code);
		}else{
			$this->insertData("tbl_otp_check",$data,1);
		}
		
		return true;
	}
  
	function updatedataotp($data,$table,$column1,$value1,$column2,$value2){
		$this->db->where($column1,$value1);
		$this->db->where($column2,$value2);
		$this->db->update($table,$data);
	}
	
	function runquery($sql_query = ''){
		$query = $this->db->query($sql_query);
		if($query->num_rows() > 0){
			return $query->result();
		}else{
			return false;
		}
	}
	
	function runquery_array($sql_query = ''){
		$query = $this->db->query($sql_query);
		if($query->num_rows() > 0){
			return $query->result_array();
		}else{
			return false;
		}
	}
	
	function getdata($table, $fields, $condition = '1=1'){
		//echo "Select $fields from $table where $condition";exit;
		$sql = $this->db->query("Select $fields from $table where $condition");
		if($sql->num_rows() > 0){
			return $sql->result_array();
		}else{
			return false;
		}
	}

	public function getMasterQuotesByLeadID($cols, $lead_id)
	{

		$this->db->select($cols);
		$this->db->from('master_quotes');
		$this->db->where('lead_id', $lead_id);
		$data = $this->db->get();

		return $data->result_array();
	}
	
	function getSortedData($select, $table, $condition = "", $sort_col = "", $sort_order = "")
	{	
		$this->db->select($select);
		$this->db->from($table);
		
		if(!empty($condition)){
			$this->db->where($condition);
		}
		
		if(!empty($sort_col) && !empty($sort_order)){
			$this->db->order_by($sort_col, $sort_order);
		}
		
		$query = $this->db->get();
		
		// echo "<br/>";
		// print_r($this->db->last_query());
		// exit;
	   
		if($query->num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}
	
	function getdataCount($table, $fields, $condition = '1=1'){
		
		$sql = $this->db->query("Select count(*) as total  from $table where $condition");
		if($sql->num_rows() > 0){
		    $cnt = $sql->result_array();
			return $cnt[0]['total'];
		}else{
			return 0;
		}
	}
	
	function getdata_orderby($table, $fields, $condition = '1=1', $order_by){
		//echo "Select $fields from $table where $condition";exit;
		$sql = $this->db->query("Select $fields from $table where $condition $order_by");
		if($sql->num_rows() > 0){
			return $sql->result_array();
		}else{
			return false;
		}
	}
	
	function getdata_groupby_orderby($table, $fields, $condition = '1=1', $group_by="", $order_by=""){
		//echo "Select $fields from $table where $condition";exit;
		$sql = $this->db->query("Select $fields from $table where $condition $group_by $order_by");
		if($sql->num_rows() > 0){
			return $sql->result_array();
		}else{
			return false;
		}
	}
	
	function getdata_groupby_orderby_limit($table, $fields, $condition = '1=1', $group_by="", $order_by="", $page ){
		$rows = 10;
		$page = $rows * $page;
		$limit = $page.",".$rows;
		
		$sql = $this->db->query("Select $fields from $table where $condition $group_by $order_by limit $limit");
		//print_r($this->db->last_query());
		//exit;
		
		if($sql->num_rows() > 0){
			return $sql->result_array();
		}else{
			return false;
		}
	}
	
	function getdata_orderby_limit($table, $fields, $condition = '1=1', $order_by, $page){
	
		$rows = 10;
		$page = $rows * $page;
		$limit = $page.",".$rows;
		
		$sql = $this->db->query("Select $fields from $table where $condition $order_by limit $limit");
		//print_r($this->db->last_query());
		//exit;
		
		if($sql->num_rows() > 0){
			return $sql->result_array();
		}else{
			return false;
		}
	}
	
	function getdata_orderby1($table, $fields, $condition = '1=1', $order_by){
		//echo "Select $fields from $table where $condition";exit;
		$sql = $this->db->query("Select $fields from $table where $condition order by $order_by desc limit 1");
		if($sql->num_rows() > 0){
			return $sql->result_array();
		}else{
			return false;
		}
	}

	function getdata_orderby2($table, $fields, $condition = '1=1', $order_by){
		//echo "Select $fields from $table where $condition";exit;
		$sql = $this->db->query("Select $fields from $table where $condition order by $order_by limit 1");
		if($sql->num_rows() > 0){
			return $sql->result_array();
		}else{
			return false;
		}
	}
    
    
    
	function updateRecord($tbl_name,$datar,$condition)
	{
		//$this -> db -> where($comp_col, $eid);
		$this -> db -> where("($condition)");
		$this -> db -> update($tbl_name,$datar);
		 
		if ($this->db->affected_rows() > 0){
			return true;
		}else{
			return true;
		} 
	}	
	
	function delrecord($tbl_name,$tbl_id,$record_id)
	{
		$this->db->where($tbl_id, $record_id);
	    $this->db->delete($tbl_name);
		if($this->db->affected_rows() >= 1)
		{
			return true;
	    }
	    else
	    {
			return false;
	    }
	}
	
	function delrecord_condition($tbl_name,$condition)
	{
		//$this->db->where($tbl_id, $record_id);
		$this->db->where("($condition)");
		$this->db->delete($tbl_name);
		if($this->db->affected_rows() >= 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function check_utoken($decode_utoken,$usertype,$apptype,$login_userid) {
		$this -> db -> select('u.*');
		$this -> db -> from('tbl_users_master as u');
		
		if ($apptype != 'W'){
			if($usertype == 'P' ){
				$this -> db -> where('concat(u.user_master_id,provider_device_id)',$decode_utoken);
			}
			else if($usertype == 'C'){
				$this -> db -> where('concat(u.user_master_id,consumer_device_id)',$decode_utoken);
			}
		}
		// if called via web check decode_utoken only with user id
		else{
			$this -> db -> where('u.user_master_id',$decode_utoken);
		}

		//also check with login user id	- bascially user id within the decode_utoken should be same as loginuser id
		$this -> db -> where('u.user_master_id',$login_userid); 
		$this -> db -> where('u.status',"Active");	

		
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	
	function check_registered_utoken($utoken) {
		$this -> db -> select('u.*');
		$this -> db -> from('tbl_users as u');	
		$this -> db -> where('u.email',$utoken);
		$query = $this -> db -> get();
	
		if($query -> num_rows() >= 1)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
		
	}
	
	
	function getRecords($tbl_name, $condition, $page=null, $default_sort_column=null, $default_sort_order=null, $group_by=null){
		
		$table = $tbl_name;
		$default_sort_column = $default_sort_column;
		$default_sort_order = $default_sort_order;
		if($page != null){
			$rows = 10;
			$page = $rows * $page;
		}
		
		// sort order by column
		$sort = $default_sort_column;  
		$order = $default_sort_order;

		$this -> db -> select('*');
		$this -> db -> from($tbl_name);
		
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		if($page != null){
			$this->db->limit($rows,$page);
		}
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
				
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}
	
	
	function getCreditorList($post)
	{
		$table = "master_ceditors";
		$table_id = 'creditor_id ';
		$default_sort_column = 'creditor_id ';
		$default_sort_order = 'desc';
		$condition = "1=1";
		
		$colArray = array('i.creaditor_name','i.isactive');
		$sortArray = array('i.creaditor_name','i.creditor_code','i.ceditor_email','i.creditor_mobile','i.creditor_phone','i.creditor_pancard','i.creditor_gstn','i.isactive');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		//print_r($post['sSearch_1']);
		// sort order by column
		$sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;  
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<2;$i++)
		{
			if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='' )
			{
			    if($i==0){
                    $condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
                }else{
                    $condition .= " AND $colArray[$i] = ".$_POST['sSearch_'.$i];
                }
			}
		}
		if(($post['sSearch_1']) == ''){
		   // echo 123;die;
            $condition .= " AND isactive=1";
        }
		
		/*echo "Condition: ".$condition;
		exit;*/
		$this -> db -> select('creaditor_name,creditor_code,ceditor_email,creditor_mobile,creditor_phone,creditor_pancard,creditor_gstn
		,isactive,creditor_id');
		$this -> db -> from('master_ceditors as i');

		$this->db->where("($condition)");
        //$this->db->where("i.isactive",1);

        $this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		/*print_r($this->db->last_query());
		exit;
		*/
		$this -> db -> select('*');
		$this -> db -> from('master_ceditors as i');
		$this->db->where("($condition)");
       // $this->db->where("i.isactive",1);

        $this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
        /*print_r($this->db->last_query());
        exit;*/
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	function getCreditorFormData($ID)
	{	
		$this -> db -> select('creditor_id, creaditor_name,allow_negative_issuance,term_condition,short_code, creditor_code, ceditor_email, creditor_mobile, creditor_phone, creditor_pancard, creditor_gstn, address, creditor_logo, isactive, createdon, initial_cd, cd_threshold, cd_utilised, cd_balance_remain,threshold_value');
		$this -> db -> from('master_ceditors as i');
		$this -> db -> where('i.creditor_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}
	
	function getLoginUserDetails($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('master_employee as i');
		$this -> db -> where('i.employee_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}
	
	
	function getPermissionList($post)
	{
		$table = "permissions";
		$table_id = 'perm_id';
		$default_sort_column = 'perm_desc';
		$default_sort_order = 'asc';
		$condition = "1=1";
		
		$colArray = array('i.perm_desc');
		$sortArray = array('i.perm_desc');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		$sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;  
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<1;$i++)
		{
			if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
			{
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('*');
		$this -> db -> from('permissions as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('*');
		$this -> db -> from('permissions as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	function getPermissionFormData($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('permissions as i');
		$this -> db -> where('i.perm_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}
	
	function getRoleList($post)
	{
		$table = "roles";
		$table_id = 'role_id';
		$default_sort_column = 'role_name';
		$default_sort_order = 'asc';
		$condition = "1=1";
		
		$colArray = array('i.role_name');
		$sortArray = array('i.role_name');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		$sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;  
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<1;$i++)
		{
			if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
			{
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('*');
		$this -> db -> from('roles as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('*');
		$this -> db -> from('roles as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	function getRoleFormData($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('roles as i');
		$this -> db -> where('i.role_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}
	
	function getUserList($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "master_employee";
		$table_id = 'employee_id';
		$default_sort_column = 'employee_id';
		$default_sort_order = 'desc';
		$condition = "1=1";
		
		$colArray = array('i.user_name','i.employee_fname','i.employee_lname','i.employee_code','i.email_id','i.mobile_number','i.role_id','l.location_id','i.isactive');
		$sortArray = array('i.user_name','i.employee_fname','i.employee_lname','i.employee_code','i.email_id','i.mobile_number','i.role_id','i.isactive');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		//$s = $post['iSortCol_0'];
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<8;$i++)
		{
			if($i==6 ||$i == 7)
			{
				if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
				{
					$condition .= " AND $colArray[$i] = '".$_POST['sSearch_'.$i]."'";
				}
			}
			else
			{
				if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
				{
					$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
				}
			}
			
			
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('*,i.isactive as is_active');
		$this -> db -> from('master_employee as i');
		$this -> db -> join('roles as r', 'i.role_id  = r.role_id', 'left');
		//$this -> db -> join('user_locations as l', 'i.employee_id  = l.user_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->group_by('i.employee_id');
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('*');
		$this -> db -> from('master_employee as i');
		$this -> db -> join('roles as r', 'i.role_id  = r.role_id', 'left');
		//$this -> db -> join('user_locations as l', 'i.employee_id  = l.user_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	/*function getlocationList($post)
	{
		$table = "cities";
		$table_id = 'city_id';
		$default_sort_column = 's.state_name';
		$default_sort_order = 'asc';
		$condition = "1=1";
		
		$colArray = array('s.state_name','i.city_name');
		$sortArray = array('s.state_name','i.city_name');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		$sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;  
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<5;$i++)
		{
			if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
			{
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.city_id,i.city_name, i.isactive,s.state_name');
		$this -> db -> from('cities as i');
		$this -> db -> join('states as s', 'i.city_state_id  = s.state_id ', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.city_id,i.city_name, i.isactive,s.state_name');
		$this -> db -> from('cities as i');
		$this -> db -> join('states as s', 'i.city_state_id  = s.state_id ', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	*/
	
	function getlocationList($post)
	{
		$table = "master_location";
		$table_id = 'location_id';
		$default_sort_column = 'i.location_name';
		$default_sort_order = 'asc';
		$condition = "1=1";
		
		$colArray = array('i.location_name');
		$sortArray = array('i.location_name');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		$sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;  
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<5;$i++)
		{
			if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
			{
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.location_id,i.location_name,i.isactive');
		$this -> db -> from('master_location as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.location_id,i.location_name');
		$this -> db -> from('master_location as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	function getLocationFormData($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('master_location as i');
		$this -> db -> where('i.location_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}
	
	function branchListing($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "creditor_branches";
		$table_id = 'branch_id';
		$default_sort_column = 'branch_name';
		$default_sort_order = 'asc';
		$condition = "1=1";
		
		$colArray = array('i.branch_name','c.creaditor_name','l.location_name','i.contact_no','i.email_id','i.isactive');
		$sortArray = array('i.branch_name','c.creaditor_name','l.location_name','i.contact_no','i.email_id','i.isactive');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<8;$i++)
		{
			if($i==6)
			{
				if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
				{
					$condition .= " AND $colArray[$i] = '".$_POST['sSearch_'.$i]."'";
				}
			}
			else
			{
				if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
				{
					$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
				}
			}
			
			
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.*, c.creaditor_name, l.location_name');
		$this -> db -> from('creditor_branches as i');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('master_location as l', 'i.location_id  = l.location_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('*');
		$this -> db -> from('creditor_branches as i');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('master_location as l', 'i.location_id  = l.location_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	function getBranchesFormData($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('creditor_branches as i');
		$this -> db -> where('i.branch_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}
	
	function getLoginUserAccess($role_id)
	{	
		$condition = "i.role_id='".$role_id."' ";
		$this -> db -> select('p.perm_desc');
		$this -> db -> from('role_perm as i');
		$this -> db -> join('permissions as p', 'i.perm_id   = p.perm_id ', 'left');
		$this->db->where("($condition)");
		$this->db->order_by('i.perm_id', 'asc');
		
		$query = $this->db->get();
		
		// echo "<br/>";
		//print_r($this->db->last_query());
	//	exit;
	   
		if($query->num_rows() >= 1)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	
	function smCreditorMappingListing($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "sm_creditor_mapping";
		$table_id = 'sm_creditor_id';
		$default_sort_column = 'e.employee_full_name';
		$default_sort_order = 'asc';
		$condition = "1=1";
		
		$colArray = array('e.employee_full_name','c.creaditor_name','i.isactive');
		$sortArray = array('e.employee_full_name','c.creaditor_name','i.isactive');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<8;$i++)
		{
			if($i==6)
			{
				if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
				{
					$condition .= " AND $colArray[$i] = '".$_POST['sSearch_'.$i]."'";
				}
			}
			else
			{
				if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
				{
					$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
				}
			}
			
			
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.*, e.employee_full_name, c.creaditor_name');
		$this -> db -> from('sm_creditor_mapping as i');
		$this -> db -> join('master_employee as e', 'i.sm_id  = e.employee_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.*, e.employee_full_name, c.creaditor_name');
		$this -> db -> from('sm_creditor_mapping as i');
		$this -> db -> join('master_employee as e', 'i.sm_id  = e.employee_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}

	function exportSMCreditors($post)
	{
		$condition = "1=1";

		if(isset($_POST['sm_name']) && !empty($_POST['sm_name'])){
			$condition .= " AND e.employee_full_name LIKE '%".$_POST['sm_name']."%'";
		}
		
		if(isset($_POST['creditor_name']) && !empty($_POST['creditor_name'])){
			$condition .= " AND c.creaditor_name LIKE '%".$_POST['creditor_name']."%'";
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.*, e.employee_full_name, c.creaditor_name');
		$this -> db -> from('sm_creditor_mapping as i');
		$this -> db -> join('master_employee as e', 'i.sm_id  = e.employee_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by('c.creaditor_name', 'asc');
		
		$query = $this -> db -> get();
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	function getSMCreditorFormData($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('sm_creditor_mapping as i');
		$this -> db -> where('i.sm_creditor_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}

	function getSMCreditorMappingByUserId($ID)
	{	
		$this -> db -> select('i.creditor_id');
		$this -> db -> from('sm_creditor_mapping as i');
		$this -> db -> where('i.sm_id', $ID);
		$this -> db -> limit(1);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}
	
	function leadListing($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "lead_details";
		$table_id = 'lead_id';
		$default_sort_column = 'lead_id';
		$default_sort_order = 'desc';
        $creditor_id=$_POST['creditor_id'];
        $condition='';
        if($_POST['role_id'] == 3){
            // echo 123;die;
            $condition .= "  i.creditor_id in (".$creditor_id.") ";
        }else if($_POST['role_id'] == 12){
            $condition .= "i.createdby='".$_POST['user_id']."' ";
            $condition .= "AND i.creditor_id = (".$creditor_id.") ";
        }else{
            $condition = "1=1";
        }
		//echo "1".$condition; die;

		
		$colArray = array('i.trace_id','i.lan_id','p.plan_name','c.creaditor_name','s.employee_full_name','cust.full_name','i.mobile_no','i.email_id','i.status');
		$sortArray = array('i.trace_id','i.lan_id','p.plan_name','c.creaditor_name','s.employee_full_name','cust.full_name','i.mobile_no','i.email_id','i.status');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		//$s = $post['iSortCol_0'];
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<12;$i++)
		{
			if($i == 8 && isset($post['sSearch_8']) && !empty($post['sSearch_8'])){
				$condition .= "  AND i.createdon >= '".date("Y-m-d",strtotime($post['sSearch_8']))."  00:00:01' ";
			}else if($i == 9 && isset($post['sSearch_9']) && !empty($post['sSearch_9'])){
				$condition .= "  AND i.createdon <= '".date("Y-m-d",strtotime($post['sSearch_9']))."  23:59:59' ";
			}else if($i == 10 && isset($post['Searchkey_10']) && !empty($post['Searchkey_10'])){
				$condition .= " AND i.status = '".$_POST['Searchkey_10']."'";
			}else if(($i != 8 || $i !=9) && isset($post['sSearch_'.$i]) && $post['sSearch_'.$i] !=''){
			//if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
			
			
		}
       /* ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);*/
		//echo 123;die;
		//echo "Condition: ".$condition;exit;

		$this -> db -> select('c.api_lable_id,p.ic_api,p.coi_download,ar.certificate_number,i.lead_id, i.trace_id,i.portal_id,i.lan_id,i.is_api_lead,p.coi_type,pp.is_single_coi,group_concat(ar.COI_url) as COI_url,group_concat(ar.certificate_number) as certificate_number, i.plan_id,pp.policy_type_id, i.creditor_id, i.sales_manager_id, i.primary_customer_id, i.mobile_no, i.email_id, p.plan_name, c.creaditor_name, cust.full_name, s.employee_full_name, i.status, i.loan_amt, i.createdon, i.updatedon, pd.sum_insured, pd.premium, pd.premium_with_tax, pm.payment_mode_name, pd.payment_status, pd.transaction_number, ar.end_date, d.remark, l.location_name');
		$this -> db -> from('lead_details as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_plan as pp', 'pp.creditor_id  = i.creditor_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('master_employee as s', ' s.employee_id = i.sales_manager_id', 'left');
        $this -> db -> join('master_customer as cust', 'i.lead_id  = cust.lead_id', 'left');
		$this -> db -> join('proposal_payment_details as pd', 'i.lead_id  = pd.lead_id', 'left');
		$this -> db -> join('payment_modes as pm', 'i.mode_of_payment  = pm.payment_mode_id', 'left');
		$this -> db -> join('api_proposal_response as ar', 'i.lead_id  = ar.lead_id', 'left');
		$this -> db -> join('sm_creditor_mapping as scm', 'scm.creditor_id  = i.creditor_id AND scm.sm_id=i.createdby', 'left');
		$this -> db -> join('proposal_discrepancies as d', 'i.lead_id  = d.lead_id', 'left');
		$this -> db -> join('master_location as l', 'i.lead_location_id  = l.location_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->group_by('i.lead_id');
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
      //  echo 123;die;
	//	print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('c.api_lable_id,p.ic_api,p.coi_download,ar.certificate_number,i.lead_id, i.trace_id,i.lan_id, i.plan_id, i.creditor_id, i.sales_manager_id, i.primary_customer_id, i.mobile_no, i.email_id, p.plan_name, c.creaditor_name, cust.full_name, s.employee_full_name, i.status, i.loan_amt, i.createdon, i.updatedon, pd.sum_insured, pd.premium, pd.premium_with_tax, pm.payment_mode_name, pd.payment_status, pd.transaction_number, ar.end_date, d.remark, l.location_name');
		$this -> db -> from('lead_details as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('master_employee as s', 'i.sales_manager_id  = s.employee_id', 'left');
		$this -> db -> join('master_customer as cust', 'i.primary_customer_id  = cust.customer_id', 'left');
		$this -> db -> join('proposal_payment_details as pd', 'i.lead_id  = pd.lead_id', 'left');
		$this -> db -> join('payment_modes as pm', 'i.mode_of_payment  = pm.payment_mode_id', 'left');
		$this -> db -> join('api_proposal_response as ar', 'i.lead_id  = ar.lead_id', 'left');
		$this -> db -> join('proposal_discrepancies as d', 'i.lead_id  = d.lead_id', 'left');
		$this -> db -> join('master_location as l', 'i.lead_location_id  = l.location_id', 'left');

		$this->db->where("($condition)");
		$this->db->group_by('i.lead_id');
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;

		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
    function exportreport($post)
    {
        //echo "<pre>";print_r($post);exit;
        if($_POST['role_id'] == 3){
            $condition = "i.createdby='".$_POST['user_id']."' ";
        }else{
            $condition = "1=1";
        }

        if(isset($_POST['creditor_name']) && !empty($_POST['creditor_name'])){
            $condition .= " AND c.creditor_id like '%".$_POST['creditor_name']."%' ";
        }


$from_date = date("Y-m-d",strtotime($_POST['from_date']));
$to_date = date("Y-m-d",strtotime($_POST['to_date']));
        if(isset($_POST['from_date']) && !empty($_POST['from_date'])){
            $condition .= "  AND i.date_intimation_insured >'$from_date'" ;
        }

        if(isset($_POST['to_date']) && !empty($_POST['to_date'])){
            $condition .= "  AND i.date_intimation_insured <= '$to_date'";
        }

        //echo "Condition: ".$condition;
        //exit;
        $this -> db -> select('i.*,c.creaditor_name');
        $this->db->from('raise_bulk_upload as i');
        $this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');

        $this->db->where("($condition)");
        //$this->db->group_by('i.lead_id');

        $query = $this -> db -> get();

       // print_r($this->db->last_query());
       // exit;

        if($query -> num_rows() >= 1)
        {
            $totcount = $query -> num_rows();
            return array("query_result" => $query->result(), "totalRecords" => $totcount);
        }
        else
        {
            return array("totalRecords" => 0);
        }
    }
	function exportLeads($post)
	{
		//echo "<pre>";print_r($post);exit;
		if($_POST['role_id'] == 3){
			$condition = "i.createdby='".$_POST['user_id']."' ";
		}else{
			$condition = "1=1";
		}
		
		if(isset($_POST['trace_id']) && !empty($_POST['trace_id'])){
			$condition .= " AND i.trace_id = '".$_POST['trace_id']."'";
		}
		
		if(isset($_POST['lan_id']) && !empty($_POST['lan_id'])){
			$condition .= " AND i.lan_id = '".$_POST['lan_id']."'";
		}
		
		if(isset($_POST['plan_name']) && !empty($_POST['plan_name'])){
			$condition .= " AND p.plan_name like '%".$_POST['plan_name']."%' ";
		}

		if(isset($_POST['creditor_name']) && !empty($_POST['creditor_name'])){
			$condition .= " AND c.creaditor_name like '%".$_POST['creditor_name']."%' ";
		}

		if(isset($_POST['employee_full_name']) && !empty($_POST['employee_full_name'])){
			$condition .= " AND s.employee_full_name like '%".$_POST['employee_full_name']."%' ";
		}

		if(isset($_POST['full_name']) && !empty($_POST['full_name'])){
			$condition .= " AND cust.full_name like '%".$_POST['full_name']."%' ";
		}

		if(isset($_POST['mobile_no']) && !empty($_POST['mobile_no'])){
			$condition .= " AND i.mobile_no like '%".$_POST['mobile_no']."%' ";
		}

		if(isset($_POST['email_id']) && !empty($_POST['email_id'])){
			$condition .= " AND i.email_id like '%".$_POST['email_id']."%' ";
		}
		
		if(isset($_POST['from_date']) && !empty($_POST['from_date'])){
			$condition .= "  AND i.createdon >= '".date("Y-m-d",strtotime($_POST['from_date']))." 00:00:01' ";
		}
		
		if(isset($_POST['to_date']) && !empty($_POST['to_date'])){
			$condition .= "  AND i.createdon <= '".date("Y-m-d",strtotime($_POST['to_date']))." 23:59:59' ";
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.lead_id, i.trace_id,pp.proposal_no,i.lan_id, i.plan_id, i.creditor_id, i.sales_manager_id, i.primary_customer_id, i.mobile_no, i.email_id, p.plan_name, c.creaditor_name, cust.full_name, s.employee_full_name, i.status, i.loan_amt, i.createdon, i.updatedon, pd.sum_insured, pd.premium, pd.premium_with_tax, pm.payment_mode_name, pd.payment_status, pd.transaction_number, ar.end_date, d.remark, l.location_name,mps.policy_sub_type_name');
		$this->db->distinct('DISTINCT pp.proposal_no AS proposal_no');
		$this -> db -> from('lead_details as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_policy as mp', 'mp.plan_id  = p.plan_id', 'left');
        $this -> db -> join('master_policy_sub_type as mps', 'mps.policy_sub_type_id  = mp.policy_sub_type_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('master_employee as s', 'i.sales_manager_id  = s.employee_id', 'left');
		$this -> db -> join('master_customer as cust', 'i.primary_customer_id  = cust.customer_id', 'left');
		$this -> db -> join('proposal_payment_details as pd', 'i.lead_id  = pd.lead_id', 'left');
		$this -> db -> join('payment_modes as pm', 'i.mode_of_payment  = pm.payment_mode_id', 'left');
		$this -> db -> join('api_proposal_response as ar', 'i.lead_id  = ar.lead_id', 'left');
		$this -> db -> join('proposal_discrepancies as d', 'i.lead_id  = d.lead_id', 'left');
        $this -> db -> join('proposal_policy as pp', 'i.lead_id  = pp.lead_id', 'left');

        $this -> db -> join('master_location as l', 'i.lead_location_id  = l.location_id', 'left');
		
		$this->db->where("($condition)");
		//$this->db->group_by('i.lead_id');
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
    function exportProposal($post)
    {
        //echo "<pre>";print_r($post);exit;
        if($_POST['role_id'] == 3){
            $condition = "i.createdby='".$_POST['user_id']."' and p.policy_type_id=1";
        }else{
            $condition = "1=1";
        }


        if(isset($_POST['trace_id']) && !empty($_POST['trace_id'])){
            $condition .= " AND i.trace_id = '".$_POST['trace_id']."'";
        }

        if(isset($_POST['lan_id']) && !empty($_POST['lan_id'])){
            $condition .= " AND i.lan_id = '".$_POST['lan_id']."'";
        }

        if(isset($_POST['plan_name']) && !empty($_POST['plan_name'])){
            $condition .= " AND p.plan_name like '%".$_POST['plan_name']."%' ";
        }

        if(isset($_POST['creditor_name']) && !empty($_POST['creditor_name'])){
            $condition .= " AND c.creaditor_name like '%".$_POST['creditor_name']."%' ";
        }

        if(isset($_POST['employee_full_name']) && !empty($_POST['employee_full_name'])){
            $condition .= " AND s.employee_full_name like '%".$_POST['employee_full_name']."%' ";
        }

        if(isset($_POST['full_name']) && !empty($_POST['full_name'])){
            $condition .= " AND cust.full_name like '%".$_POST['full_name']."%' ";
        }

        if(isset($_POST['mobile_no']) && !empty($_POST['mobile_no'])){
            $condition .= " AND i.mobile_no like '%".$_POST['mobile_no']."%' ";
        }

        if(isset($_POST['email_id']) && !empty($_POST['email_id'])){
            $condition .= " AND i.email_id like '%".$_POST['email_id']."%' ";
        }

        if(isset($_POST['from_date']) && !empty($_POST['from_date'])){
            $condition .= "  AND i.createdon >= '".date("Y-m-d",strtotime($_POST['from_date']))." 00:00:01' ";
        }

        if(isset($_POST['to_date']) && !empty($_POST['to_date'])){
            $condition .= "  AND i.createdon <= '".date("Y-m-d",strtotime($_POST['to_date']))." 23:59:59' ";
        }

        //echo "Condition: ".$condition;
        //exit;
        $query=$this -> db -> query('SELECT i.lead_id,p.plan_name,c.creaditor_name,cust.salutation,cust.first_name,cust.middle_name,cust.last_name,
cust.gender,cust.dob,cust.mobile_no,i.is_coapplicant,
i.coapplicant_no,i.createdby,l.location_name,cust.mobile_no2,cust.address_line1,cust.address_line2,cust.address_line3,cust.pincode,cust.no_of_lives,i.loan_disbursement_date,
i.lan_id,i.loan_amt,i.loan_tenure,i.status,pm.payment_mode_name,pd.transaction_number,pd.transaction_date,prd.nominee_first_name,prd.nominee_last_name,
prd.nominee_contact,prd.nominee_gender,prd.nominee_salutation,prd.nominee_email,prd.nominee_relation,
( select group_concat(policy_sub_type_id,"-",master_policy_id,"-",sum_insured,"-",premium_amount,"-",tax_amount) from proposal_policy pp where pp.lead_id=i.lead_id
) as premium_details,
( select group_concat(relation_with_proposal, "|",policy_member_salutation,"|", policy_member_gender,"|", policy_member_first_name,"|",
        policy_member_last_name,"|", policy_member_dob) from proposal_policy_member_details ppmd where ppmd.lead_id=i.lead_id
) as member_details
 FROM `lead_details` as `i`
 LEFT JOIN `master_plan` as `p` ON `i`.`plan_id` = `p`.`plan_id` 
 LEFT JOIN `master_ceditors` as `c` ON `i`.`creditor_id` = `c`.`creditor_id`
 LEFT JOIN `master_employee` as `s` ON `i`.`sales_manager_id` = `s`.`employee_id` 
 LEFT JOIN `master_customer` as `cust` ON `i`.`primary_customer_id` = `cust`.`customer_id` 
 LEFT JOIN `proposal_payment_details` as `pd` ON `i`.`lead_id` = `pd`.`lead_id`
 LEFT JOIN `proposal_details` as `prd` ON `i`.`lead_id` = `prd`.`lead_id`
 LEFT JOIN `payment_modes` as `pm` ON `i`.`mode_of_payment` = `pm`.`payment_mode_id` 
 LEFT JOIN `api_proposal_response` as `ar` ON `i`.`lead_id` = `ar`.`lead_id` 
 LEFT JOIN `proposal_discrepancies` as `d` ON `i`.`lead_id` = `d`.`lead_id`
 LEFT JOIN `master_location` as `l` ON `i`.`lead_location_id` = `l`.`location_id` WHERE ('.$condition.' ) GROUP BY `i`.`lead_id`');


      /*  print_r($this->db->last_query());
        exit;*/

        if($query -> num_rows() >= 1)
        {
            $totcount = $query -> num_rows();
            return array("query_result" => $query->result(), "totalRecords" => $totcount);
        }
        else
        {
            return array("totalRecords" => 0);
        }
    }
	
	function getRoleWiseCreditorsData($user_id)
	{
		$condition = "i.sm_id='".$user_id."' and c.isactive=1 ";

		$this -> db -> select('i.creditor_id, c.creaditor_name');
		$this -> db -> from('sm_creditor_mapping as i');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> where("($condition)");

		$query = $this->db->get();

		// echo "<br/>";
		// print_r($this->db->last_query());
		// exit;

		if($query->num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	function getSMLocations($user_id)
	{	
		$condition = "i.user_id='".$user_id."' ";
		
		$this -> db -> select('i.location_id, l.location_name');
		$this -> db -> from('user_locations as i');
		$this -> db -> join('master_location as l', 'i.location_id  = l.location_id', 'left');
		$this -> db -> where("($condition)");
		
		$query = $this->db->get();
		
		// echo "<br/>";
		// print_r($this->db->last_query());
		// exit;
	   
		if($query->num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}
	
	function discrepancyProposalListing($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "lead_details";
		$table_id = 'lead_id';
		$default_sort_column = 'lead_id';
		$default_sort_order = 'desc';
		if($_POST['role_id'] == 3){
			$condition = "i.createdby='".$_POST['user_id']."' ";
		}else{
			$condition = "1=1";
		}
		
		$condition .= " && i.status='Discrepancy' ";
		
		$colArray = array('i.trace_id','i.lan_id','p.plan_name','c.creaditor_name','s.employee_full_name','cust.full_name','i.mobile_no','i.email_id','i.status');
		$sortArray = array('i.trace_id','i.lan_id','p.plan_name','c.creaditor_name','s.employee_full_name','cust.full_name','i.mobile_no','i.email_id','i.status');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		//$s = $post['iSortCol_0'];
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<9;$i++)
		{
			if($i == 8 && isset($post['sSearch_8']) && !empty($post['sSearch_8'])){
				$condition .= "  AND i.createdon >= '".date("Y-m-d",strtotime($post['sSearch_8']))."  00:00:01' ";
			}else if($i == 9 && isset($post['sSearch_9']) && !empty($post['sSearch_9'])){
				$condition .= "  AND i.createdon <= '".date("Y-m-d",strtotime($post['sSearch_9']))."  23:59:59' ";
			}else if(($i != 8 || $i !=9) && isset($post['sSearch_'.$i]) && $post['sSearch_'.$i] !=''){
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}			
			
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.lead_id, i.trace_id, i.lan_id, i.plan_id, i.creditor_id, i.sales_manager_id, i.primary_customer_id, i.mobile_no, i.email_id, p.plan_name, c.creaditor_name, cust.full_name, s.employee_full_name, i.status, d.remark, dt.discrepancy_type as discrepancy_type_val, dst.discrepancy_subtype as discrepancy_subtype_val, i.loan_amt, i.createdon, i.updatedon, pd.sum_insured, pd.premium, pd.premium_with_tax, pm.payment_mode_name');
		$this -> db -> from('lead_details as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('master_employee as s', 'i.sales_manager_id  = s.employee_id', 'left');
		$this -> db -> join('master_customer as cust', 'i.primary_customer_id  = cust.customer_id', 'left');
		$this -> db -> join('proposal_discrepancies as d', 'i.lead_id  = d.lead_id', 'left');
		$this -> db -> join('discrepancy_type as dt', 'd.discrepancy_type  = dt.discrepancy_type_id', 'left');
		$this -> db -> join('discrepancy_subtype as dst', 'd.discrepancy_subtype  = dst.discrepancy_subtype_id', 'left');
		$this -> db -> join('proposal_payment_details as pd', 'i.lead_id  = pd.lead_id', 'left');
		$this -> db -> join('payment_modes as pm', 'i.mode_of_payment  = pm.payment_mode_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.lead_id, i.trace_id, i.lan_id, i.plan_id, i.creditor_id, i.sales_manager_id, i.primary_customer_id, i.mobile_no, i.email_id, p.plan_name, c.creaditor_name, cust.full_name, s.employee_full_name, i.status');
		$this -> db -> from('lead_details as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('master_employee as s', 'i.sales_manager_id  = s.employee_id', 'left');
		$this -> db -> join('master_customer as cust', 'i.primary_customer_id  = cust.customer_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	function customerProposalListing($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "proposal_details";
		$table_id = 'proposal_details_id ';
		$default_sort_column = 'proposal_details_id ';
		$default_sort_order = 'desc';
		if($_POST['role_id'] == 3){
			$condition = "i.created_by='".$_POST['user_id']."' ";
		}else{
			$condition = "1=1";
		}
		
		$colArray = array('i.trace_id','p.plan_name','s.employee_full_name','cust.full_name');
		$sortArray = array('i.trace_id','p.plan_name','s.employee_full_name','cust.full_name');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		//$s = $post['iSortCol_0'];
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<8;$i++)
		{
			if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
			{
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
			
			
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.proposal_details_id, i.lead_id, i.trace_id, i.plan_id, i.trace_id, i.created_by, i.customer_id, p.plan_name, cust.full_name, s.employee_full_name');
		$this -> db -> from('proposal_details as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_employee as s', 'i.created_by  = s.employee_id', 'left');
		$this -> db -> join('master_customer as cust', 'i.customer_id  = cust.customer_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.proposal_details_id, i.lead_id, i.trace_id, i.plan_id, i.trace_id, i.created_by, i.customer_id, p.plan_name, cust.full_name, s.employee_full_name');
		$this -> db -> from('proposal_details as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_employee as s', 'i.created_by  = s.employee_id', 'left');
		$this -> db -> join('master_customer as cust', 'i.customer_id  = cust.customer_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	
	function boProposalListing($post)
	{
		//echo "<pre>";print_r($post);exit;
		//if(empty($_POST['sSearch_0'])){
			//return array("totalRecords" => 0);
		//}
		$table = "lead_details";
		$table_id = 'lead_id';
		$default_sort_column = 'lead_id';
		$default_sort_order = 'desc';
		
		$condition = "(i.status='BO-Approval-Awaiting' || i.status='Discrepancy' || i.status='Approved' || i.status='Rejected') && i.mode_of_payment='2' ";
		
		$colArray = array('i.trace_id','i.lan_id','p.plan_name','c.creaditor_name','s.employee_full_name','cust.full_name','i.status');
		$sortArray = array('i.trace_id','i.lan_id','p.plan_name','c.creaditor_name','s.employee_full_name','cust.full_name','i.status');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		//$s = $post['iSortCol_0'];
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<12;$i++)
		{
			if($i == 6 && isset($post['sSearch_6']) && !empty($post['sSearch_6'])){
				$condition .= "  AND i.updatedon >= '".date("Y-m-d",strtotime($post['sSearch_8']))." 00:00:01' ";
			}else if($i == 7 && isset($post['sSearch_7']) && !empty($post['sSearch_7'])){
				$condition .= "  AND i.updatedon <= '".date("Y-m-d",strtotime($post['sSearch_7']))."  23:59:59' ";
			}else if(($i != 6 || $i !=7) && isset($post['sSearch_'.$i]) && $post['sSearch_'.$i] !=''){
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.lead_id, i.trace_id,i.lan_id, i.plan_id, i.creditor_id, i.sales_manager_id, i.primary_customer_id, i.mobile_no, i.email_id, p.plan_name, c.creaditor_name, cust.full_name, s.employee_full_name, i.status, i.loan_amt, i.createdon, i.updatedon, pd.sum_insured, pd.premium, pd.premium_with_tax, pm.payment_mode_name, pd.cheque_number, pd.transaction_number, d.remark');
		$this -> db -> from('lead_details as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('master_employee as s', 'i.sales_manager_id  = s.employee_id', 'left');
		$this -> db -> join('master_customer as cust', 'i.primary_customer_id  = cust.customer_id', 'left');
		$this -> db -> join('proposal_payment_details as pd', 'i.lead_id  = pd.lead_id', 'left');
		$this -> db -> join('proposal_discrepancies as d', 'i.lead_id  = d.lead_id', 'left');
		$this -> db -> join('payment_modes as pm', 'i.mode_of_payment  = pm.payment_mode_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.lead_id, i.trace_id,i.lan_id, i.plan_id, i.creditor_id, i.sales_manager_id, i.primary_customer_id, i.mobile_no, i.email_id, p.plan_name, c.creaditor_name, cust.full_name, s.employee_full_name, i.status, i.loan_amt, i.createdon, i.updatedon, pd.sum_insured, pd.premium, pd.premium_with_tax, pm.payment_mode_name, pd.cheque_number, pd.transaction_number, d.remark');
		$this -> db -> from('lead_details as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('master_employee as s', 'i.sales_manager_id  = s.employee_id', 'left');
		$this -> db -> join('master_customer as cust', 'i.primary_customer_id  = cust.customer_id', 'left');
		$this -> db -> join('proposal_payment_details as pd', 'i.lead_id  = pd.lead_id', 'left');
		$this -> db -> join('proposal_discrepancies as d', 'i.lead_id  = d.lead_id', 'left');
		$this -> db -> join('payment_modes as pm', 'i.mode_of_payment  = pm.payment_mode_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	
	function coProposalListing($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "lead_details";
		$table_id = 'lead_id';
		$default_sort_column = 'lead_id';
		$default_sort_order = 'desc';
		
		//$condition = "(i.status='CO-Approval-Awaiting' || i.status='Discrepancy' || i.status='Approved' || i.status='Rejected') && i.mode_of_payment='NEFT'  ";
		
		$condition = "(i.status='CO-Approval-Awaiting' || i.status='Approved' || i.status='Rejected') && i.mode_of_payment='3'  ";
		
		
		$colArray = array('i.trace_id','i.lan_id','p.plan_name','c.creaditor_name','s.employee_full_name','cust.full_name','i.mobile_no','i.email_id','i.status');
		$sortArray = array('i.trace_id','i.lan_id','p.plan_name','c.creaditor_name','s.employee_full_name','cust.full_name','i.mobile_no','i.email_id','i.status');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		//$s = $post['iSortCol_0'];
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<12;$i++)
		{
			if($i == 8 && isset($post['sSearch_8']) && !empty($post['sSearch_8'])){
				$condition .= "  AND i.updatedon >= '".date("Y-m-d",strtotime($post['sSearch_8']))." 00:00:01' ";
			}else if($i == 9 && isset($post['sSearch_9']) && !empty($post['sSearch_9'])){
				$condition .= "  AND i.updatedon <= '".date("Y-m-d",strtotime($post['sSearch_9']))."  23:59:59' ";
			}else if(($i != 8 || $i !=9) && isset($post['sSearch_'.$i]) && $post['sSearch_'.$i] !=''){
			//if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.lead_id, i.lan_id, i.trace_id, i.plan_id, i.creditor_id, i.sales_manager_id, i.primary_customer_id, i.mobile_no, i.email_id, p.plan_name, c.creaditor_name, cust.full_name, s.employee_full_name, i.status, i.updatedon, i.loan_amt, i.createdon, i.updatedon, pd.sum_insured, pd.premium, pd.premium_with_tax, pm.payment_mode_name');
		$this -> db -> from('lead_details as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('master_employee as s', 'i.sales_manager_id  = s.employee_id', 'left');
		$this -> db -> join('master_customer as cust', 'i.primary_customer_id  = cust.customer_id', 'left');
		$this -> db -> join('proposal_payment_details as pd', 'i.lead_id  = pd.lead_id', 'left');
		$this -> db -> join('payment_modes as pm', 'i.mode_of_payment  = pm.payment_mode_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.lead_id, i.lan_id, i.trace_id, i.plan_id, i.creditor_id, i.sales_manager_id, i.primary_customer_id, i.mobile_no, i.email_id, p.plan_name, c.creaditor_name, cust.full_name, s.employee_full_name, i.status, i.updatedon, i.loan_amt, i.createdon, i.updatedon, pd.sum_insured, pd.premium, pd.premium_with_tax, pm.payment_mode_name');
		$this -> db -> from('lead_details as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('master_employee as s', 'i.sales_manager_id  = s.employee_id', 'left');
		$this -> db -> join('master_customer as cust', 'i.primary_customer_id  = cust.customer_id', 'left');
		$this -> db -> join('proposal_payment_details as pd', 'i.lead_id  = pd.lead_id', 'left');
		$this -> db -> join('payment_modes as pm', 'i.mode_of_payment  = pm.payment_mode_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	function getproposalpaymentdetailsbylead($leads){
		$this->db->select('*');
		$this->db->where_in('lead_id',$leads);
		$data = $this->db->get('proposal_payment_details')->result();
		return $data;
	}
	
	function uwProposalListing($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "lead_details";
		$table_id = 'lead_id';
		$default_sort_column = 'lead_id';
		$default_sort_order = 'desc';
		
		$condition = "i.status='UW-Approval-Awaiting' ";
		
		$colArray = array('i.trace_id','i.lan_id','p.plan_name','c.creaditor_name','s.employee_full_name','cust.full_name','i.mobile_no','i.email_id','i.status');
		$sortArray = array('i.trace_id','i.lan_id','p.plan_name','c.creaditor_name','s.employee_full_name','cust.full_name','i.mobile_no','i.email_id','i.status');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		//$s = $post['iSortCol_0'];
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<9;$i++)
		{
			if($i == 8 && isset($post['sSearch_8']) && !empty($post['sSearch_8'])){
				$condition .= "  AND i.updatedon >= '".date("Y-m-d",strtotime($post['sSearch_8']))." 00:00:01' ";
			}else if($i == 9 && isset($post['sSearch_9']) && !empty($post['sSearch_9'])){
				$condition .= "  AND i.updatedon <= '".date("Y-m-d",strtotime($post['sSearch_9']))." 23:59:59' ";
			}else if(($i != 8 || $i !=9) && isset($post['sSearch_'.$i]) && $post['sSearch_'.$i] !=''){
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.lead_id, i.trace_id,i.lan_id, i.plan_id, i.creditor_id, i.sales_manager_id, i.primary_customer_id, i.mobile_no, i.email_id, p.plan_name, c.creaditor_name, cust.full_name, s.employee_full_name, i.status, i.updatedon, i.loan_amt, i.createdon, i.updatedon, pd.sum_insured, pd.premium, pd.premium_with_tax, pm.payment_mode_name, pd.cheque_number');
		$this -> db -> from('lead_details as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('master_employee as s', 'i.sales_manager_id  = s.employee_id', 'left');
		$this -> db -> join('master_customer as cust', 'i.primary_customer_id  = cust.customer_id', 'left');
		$this -> db -> join('proposal_payment_details as pd', 'i.lead_id  = pd.lead_id', 'left');
		$this -> db -> join('payment_modes as pm', 'i.mode_of_payment  = pm.payment_mode_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.lead_id, i.trace_id,i.lan_id, i.plan_id, i.creditor_id, i.sales_manager_id, i.primary_customer_id, i.mobile_no, i.email_id, p.plan_name, c.creaditor_name, cust.full_name, s.employee_full_name, i.status, i.updatedon, i.loan_amt, i.createdon, i.updatedon, pd.sum_insured, pd.premium, pd.premium_with_tax, pm.payment_mode_name, pd.cheque_number');
		$this -> db -> from('lead_details as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('master_employee as s', 'i.sales_manager_id  = s.employee_id', 'left');
		$this -> db -> join('master_customer as cust', 'i.primary_customer_id  = cust.customer_id', 'left');
		$this -> db -> join('proposal_payment_details as pd', 'i.lead_id  = pd.lead_id', 'left');
		$this -> db -> join('payment_modes as pm', 'i.mode_of_payment  = pm.payment_mode_id', 'left');

		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	
	/*** 
		Policy Genration - Quick Qoute
		
		$emp_id = customer_id, 
		$policy_detail_id = master_policy_id, 
		$proposal_policy_id = proposal_policy_id
		$nominees = proposal nominee
	***/
	
	
	public function get_quote_data($lead_id, $emp_id,$master_policy_id, $proposal_policy_id, $proposal_details, $policy_sub_type_id, $sum_insured,$count=1){ 
		
		//get proposal policy details
		$data = array();
		$data['customer_data'] = (array)$this->get_profile($emp_id);
		$data['member_data'] = (array)$this->get_all_member_data($proposal_policy_id);
		
		$nominees = array();
		if(!empty($proposal_details)){
			$nominees['nominee_relation'] = $proposal_details[0]['nominee_relation'];
			$nominees['nominee_first_name'] = $proposal_details[0]['nominee_first_name'];
			$nominees['nominee_last_name'] = $proposal_details[0]['nominee_last_name'];
			$nominees['nominee_salutation'] = $proposal_details[0]['nominee_salutation'];
			$nominees['nominee_gender'] = $proposal_details[0]['nominee_gender'];
			$nominees['nominee_dob'] = $proposal_details[0]['nominee_dob'];
			$nominees['nominee_contact'] = $proposal_details[0]['nominee_contact'];
			$nominees['nominee_email'] = $proposal_details[0]['nominee_email'];
		}
		$data['nominee_data'] = $nominees;
		$data['proposal_data'] = $proposal_details;
		
		
		
		//echo "<pre>";print_r($data);exit;
		
		//get Api URL with policy_sub_type_id
		$getApiUrl = $this->db->query("select api_url from master_policy_sub_type where policy_sub_type_id='".$policy_sub_type_id."' ")->row_array();
		
		$url = trim($getApiUrl['api_url']);
		
		if($url == ''){
			return array(
				"status" => "error",
				"msg" => "Something Went Wrong"
			);
			exit;
		}
		 
		//echo $url;exit;
		
		$occupation = '';
		
		//Data Variables
		//$Member_Customer_ID = $data['customer_data']['customer_id'];
		$Member_Customer_ID = 10000000000004;
		$uidNo = (!empty($data['customer_data']['adhar'])) ? $data['customer_data']['adhar'] : null;
		
		$MasterPolicyNumber = '61-20-00040-00-00';
		$GroupID = 'GRP001';
		//$PlanCode = '4211';
		//$plan_code = (!empty($data['proposal_data']['plan_code'])) ? $data['proposal_data']['plan_code'] : null;
		$plan_code = '4211';
		$Product_Code = '4211';
		
		$Member_Type_Code = '';
		$intermediaryCode = '2108233';
		$intermediaryBranchCode = '10MHMUM01';
		$SchemeCode = '4112000003';
		
		//get SumInsured Type
		$sumins_type = $this->db->query("select mi.suminsured_type from master_policy_si_type_mapping as m left join master_suminsured_type as mi ON m.suminsured_type_id = mi.suminsured_type_id where m.master_policy_id='$master_policy_id' ")->row();
		
		//echo "<pre>";print_r($sumins_type);exit;
		$SumInsured_Type = $sumins_type->suminsured_type;
		//echo $SumInsured_Type;exit;
		
		
		
		$leadID = $lead_id;
		//$SumInsured = $sum_insured;
		$SumInsured = 500000;
		$product_id = $data['proposal_data'][0]['plan_id'];
		
		
		$SPID = 0;
		$RefCode1 = 0;
		$RefCode2 = 0;
		$Policy_Tanure = 1;
		$AutoRenewal = 'Y';
		//$intermediaryBranchCode = "10MHMUM01";
		
		$totalMembers = count($data['member_data']);
		$member = [];
		
		$explode_name = array($data['customer_data']['first_name'],$data['customer_data']['middle_name'],$data['customer_data']['last_name']);
		$explode_name_nominee = array($data['nominee_data']['nominee_first_name'],$data['nominee_data']['nominee_last_name']);
		
		for ($i = 0; $i < $totalMembers; $i++){
			//Checking relation based on self, spouse, son and daughter
			if($data['member_data'][$i]['relation_with_proposal'] == 1 ){
				$data['member_data'][$i]['relation_code'] = "R001";
			}else if($data['member_data'][$i]['relation_with_proposal'] == 2 ){
				$data['member_data'][$i]['relation_code'] = "R002";
			}else if($data['member_data'][$i]['relation_with_proposal'] == 3 ){
				$data['member_data'][$i]['relation_code'] = "R003";
			}else{
				$data['member_data'][$i]['relation_code'] = "R004";
			}
			
			//Nominee relation code
			if($data['nominee_data']['nominee_relation'] == 1 ){
				$data['nominee_data']['relation_code'] = "R001";
			}else if($data['nominee_data']['nominee_relation'] == 2 ){
				$data['nominee_data']['relation_code'] = "R002";
			}else if($data['nominee_data']['nominee_relation'] == 3 ){
				$data['nominee_data']['relation_code'] = "R003";
			}else{
				$data['nominee_data']['relation_code'] = "R004";
			}
			
			
			$abc = ["PEDCode" => null, "Remarks" => null];
			
			$explode_name_member = explode(" ", trim($data['member_data'][$i]['policy_member_first_name']),2);
		 
		
			$member[] = ["MemberNo" => $i + 1, "Salutation" => (($data['member_data'][$i]['policy_member_gender'] == "Male") ? 'Mr' : (($data['member_data'][$i]['policy_member_age'] >= 18) ? 'Mrs' : 'Ms')) , "First_Name" => $data['member_data'][$i]['policy_member_first_name'], "Middle_Name" => null, "Last_Name" => !empty($data['member_data'][$i]['policy_member_last_name']) ? $data['member_data'][$i]['policy_member_last_name'] : '.', "Gender" => ($data['member_data'][$i]['policy_member_gender'] == "Male") ? "M" : "F", "DateOfBirth" => date('m/d/Y', strtotime($data['member_data'][$i]['policy_member_dob'])) , "Relation_Code" => trim($data['member_data'][$i]['relation_code']), "Marital_Status" => null, "height" => "0.00", "weight" => "0", "occupation" => "O553", "PrimaryMember" => (($i == 0) ? "Y" : "N") , "MemberproductComponents" => [["PlanCode" => $plan_code, "MemberQuestionDetails" => [["QuestionCode" => null, "Answer" => null, "Remarks" => null]]]], "MemberPED" => $abc, "exactDiagnosis" => null, "dateOfDiagnosis" => null, "lastDateConsultation" => null, "detailsOfTreatmentGiven" => null, "doctorName" => null, "hospitalName" => null, "phoneNumberHosital" => null, "Nominee_First_Name" => $explode_name_nominee[0], "Nominee_Last_Name" => !empty($explode_name_nominee[1]) ? $explode_name_nominee[1] : '.', "Nominee_Contact_Number" => (!empty($data['nominee_data']['nominee_contact'])) ? $data['nominee_data']['nominee_contact'] : null, "Nominee_Home_Address" => null, "Nominee_Relationship_Code" => trim($data['nominee_data']['relation_code']), ];

		}
		
		//echo "<pre>";print_r($member);exit;
		$data['proposal_data']['SourceSystemName_api'] = 'CreditorPortal';
		
		
		//echo $policy_detail_id;exit;
		if($policy_sub_type_id == 1){ // ghi
			$fqrequest = ["ClientCreation" => ["Member_Customer_ID" =>  $Member_Customer_ID , "salutation" => $data['customer_data']['salutation'], "firstName" => $explode_name[0], "middleName" => "", "lastName" => !empty($explode_name[1]) ? $explode_name[1] : '.', "dateofBirth" => date('m/d/Y', strtotime($data['customer_data']['dob'])) , "gender" => ($data['customer_data']['gender'] == "Male") ? "M" : "F", "educationalQualification" => null, "pinCode" => $data['customer_data']['pincode'], "uidNo" => $uidNo, "maritalStatus" => null, "nationality" => "Indian", "occupation" => $occupation, "primaryEmailID" => $data['customer_data']['email_id'], "contactMobileNo" => substr(trim($data['customer_data']['mobile_no']), -10), "stdLandlineNo" => null, "panNo" => null, "passportNumber" => null, "contactPerson" => null, "annualIncome" => null, "remarks" => null, "startDate" => date('Y-m-d') , "endDate" => null, "IdProof" => "Adhaar Card", "residenceProof" => null, "ageProof" => null, "others" => null, "homeAddressLine1" => $data['customer_data']['address_line1'], "homeAddressLine2" => null, "homeAddressLine3" => null, "homePinCode" => $data['customer_data']['pincode'], "homeArea" => null, "homeContactMobileNo" => null, "homeContactMobileNo2" => null, "homeSTDLandlineNo" => null, "homeFaxNo" => null, "sameAsHomeAddress" => "1", "mailingAddressLine1" => null, "mailingAddressLine2" => null, "mailingAddressLine3" => null, "mailingPinCode" => null, "mailingArea" => null, "mailingContactMobileNo" => null, "mailingContactMobileNo2" => null, "mailingSTDLandlineNo" => null, "mailingSTDLandlineNo2" => null, "mailingFaxNo" => null, "bankAccountType" => null, "bankAccountNo" => null, "ifscCode" => null, "GSTIN" => null, "GSTRegistrationStatus" => "Consumers", "IsEIAavailable" => "0", "ApplyEIA" => "0", "EIAAccountNo" => null, "EIAWith" => "0", "AccountType" => null, "AddressProof" => null, "DOBProof" => null, "IdentityProof" => null], "PolicyCreationRequest" => ["Quotation_Number" => "", "MasterPolicyNumber" => $MasterPolicyNumber, "GroupID" => $GroupID, "Product_Code" => $Product_Code, "intermediaryCode" => $intermediaryCode, "AutoRenewal" => $AutoRenewal, "intermediaryBranchCode" => $intermediaryBranchCode, "agentSignatureDate" => null, "Customer_Signature_Date" => null, "businessSourceChannel" => null, "AssignPolicy" => "0", "AssigneeName" => null, "leadID" => $leadID, "Source_Name" => $data['proposal_data']['SourceSystemName_api'], "SPID" => $SPID, "TCN" => null, "CRTNO" => null, "RefCode1" => $RefCode1, "RefCode2" => $RefCode2, "Employee_Number" => $data['customer_data']['customer_id'],"enumIsEmployeeDiscount" => null, "QuoteDate" => null, "IsPayment" => "0", "PaymentMode" => "", "PolicyproductComponents" => [["PlanCode" => $plan_code, "SumInsured" => $SumInsured, "SchemeCode" => $SchemeCode]]], "MemObj" => ["Member" => $member], "ReceiptCreation" => ["officeLocation" => "", "modeOfEntry" => "", "cdAcNo" => null, "expiryDate" => null, "payerType" => "", "payerCode" => null, "paymentBy" => "", "paymentByName" => null, "paymentByRelationship" => null, "collectionAmount" => "", "collectionRcvdDate" => null, "collectionMode" => "", "remarks" => null, "instrumentNumber" => null, "instrumentDate" => null, "bankName" => null, "branchName" => null, "bankLocation" => null, "micrNo" => null, "chequeType" => null, "ifscCode" => "", "PaymentGatewayName" => "", "TerminalID" => "", "CardNo" => null]];
			
		}else{
			//group policies
			$fqrequest = ["ClientCreation" => ["Member_Customer_ID" => $Member_Customer_ID, "salutation" => $data['customer_data']['salutation'], "firstName" => $explode_name[0], "middleName" => "", "lastName" => !empty(trim($explode_name[1]))?$explode_name[1]:'.', "dateofBirth" => date('m/d/Y', strtotime($data['customer_data']['dob'])) , "gender" => ($data['customer_data']['gender'] == "Male") ? "M" : "F", "educationalQualification" => null, "pinCode" => $data['customer_data']['pincode'], "uidNo" => $uidNo, "maritalStatus" => null, "nationality" => "Indian", "occupation" => $occupation, "primaryEmailID" => $data['customer_data']['email_id'], "contactMobileNo" => substr(trim($data['customer_data']['mobile_no']), -10), "stdLandlineNo" => null, "panNo" => null, "passportNumber" => null, "contactPerson" => null, "annualIncome" => null, "remarks" => null, "startDate" => date('Y-m-d') , "endDate" => null, "IdProof" => "Adhaar Card", "residenceProof" => null, "ageProof" => null, "others" => null, "homeAddressLine1" => $data['customer_data']['address_line1'], "homeAddressLine2" => null, "homeAddressLine3" => null, "homePinCode" => $data['customer_data']['pincode'], "homeArea" => null, "homeContactMobileNo" => null, "homeContactMobileNo2" => null, "homeSTDLandlineNo" => null, "homeFaxNo" => null, "sameAsHomeAddress" => "1", "mailingAddressLine1" => null, "mailingAddressLine2" => null, "mailingAddressLine3" => null, "mailingPinCode" => null, "mailingArea" => null, "mailingContactMobileNo" => null, "mailingContactMobileNo2" => null, "mailingSTDLandlineNo" => null, "mailingSTDLandlineNo2" => null, "mailingFaxNo" => null, "bankAccountType" => null, "bankAccountNo" => null, "ifscCode" => null, "GSTIN" => null, "GSTRegistrationStatus" => "Consumers", "IsEIAavailable" => "0", "ApplyEIA" => "0", "EIAAccountNo" => null, "EIAWith" => "0", "AccountType" => null, "AddressProof" => null, "DOBProof" => null, "IdentityProof" => null], "PolicyCreationRequest" => ["Quotation_Number" => "", "MasterPolicyNumber" => $MasterPolicyNumber, "GroupID" => $GroupID, "Product_Code" => $Product_Code,"SumInsured_Type"=> $SumInsured_Type,"Policy_Tanure"=> $Policy_Tanure,"Member_Type_Code"=> $Member_Type_Code, "intermediaryCode" => $intermediaryCode, "AutoRenewal" => $AutoRenewal, "intermediaryBranchCode" => $intermediaryBranchCode, "agentSignatureDate" => null, "Customer_Signature_Date" => null, "businessSourceChannel" => null, "AssignPolicy" => "0", "AssigneeName" => null, "leadID" => $leadID, "Source_Name" => $data['proposal_data']['SourceSystemName_api'], "SPID" => $SPID, "TCN" => null, "CRTNO" => null, "RefCode1" => $RefCode1, "RefCode2" => $RefCode2, "Employee_Number" => $data['customer_data']['customer_id'],"enumIsEmployeeDiscount" => null, "QuoteDate" => null, "IsPayment" => "0", "PaymentMode" => null, "PolicyproductComponents" => ["PlanCode" => $plan_code, "SumInsured" => $SumInsured, "SchemeCode" => $SchemeCode] ], "MemObj" => ["Member" => $member], "ReceiptCreation" => ["officeLocation" => "", "modeOfEntry" => "", "cdAcNo" => null, "expiryDate" => null, "payerType" => "", "payerCode" => null, "paymentBy" => "", "paymentByName" => null, "paymentByRelationship" => null, "collectionAmount" => "", "collectionRcvdDate" => null, "collectionMode" => "", "remarks" => null, "instrumentNumber" => null, "instrumentDate" => null, "bankName" => null, "branchName" => null, "bankLocation" => null, "micrNo" => null, "chequeType" => null, "ifscCode" => "", "PaymentGatewayName" => "", "TerminalID" => "", "CardNo" => null]];
		}
		
		//echo "<pre>";print_r($fqrequest);exit;

		$request_arr = ["lead_id" => $leadID, "req" => json_encode($fqrequest) ,"product_id" => $product_id, "type"=>"quick_quote_request", "proposal_policy_id"=> $proposal_policy_id];
	
		$this->db->insert("logs_docs",$request_arr);
		$insert_id = $this->db->insert_id();

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($fqrequest) ,
			CURLOPT_HTTPHEADER => array(
				"Accept: */*",
				"Cache-Control: no-cache",
				"Connection: keep-alive",
				"Content-Length: " . strlen(json_encode($fqrequest)) ,
				"Content-Type: application/json",
				"Host: bizpre.adityabirlahealth.com"
			) ,
		));


		$response = curl_exec($curl);
		
		$err = curl_error($curl);
		if($response == '' || $response == NULL){
			$response = $err;
		}
		
		//echo "<pre>";print_r($response);exit;
		
		$request_arr = ["res" => json_encode($response)];
		$this->db->where("id",$insert_id);
		$this->db->update("logs_docs",$request_arr);

		curl_close($curl);

		if ($err){
			return array(
				"status" => "error",
				"msg" => $err
			);
			if($count <= 3){
				sleep(15);
				$this->get_quote_data($lead_id, $emp_id, $master_policy_id, $proposal_policy_id, $proposal_details, $policy_sub_type_id, $sum_insured,$count++);
			}
		}else{
			
			$new = simplexml_load_string($response);
			$con = json_encode($new);
			$newArr = json_decode($con, true);
			$errorObj = $newArr['errorObj'];
			//print_pre($errorObj);exit;
			if($errorObj['ErrorNumber'] == '00'){
			  $policydetail = $newArr['policyDtls'];
			  
			  $arr = ["emp_id" => $emp_id, "lead_id" => $lead_id,"proposal_policy_id" => $proposal_policy_id,"policy_subtype_id" =>$policy_sub_type_id, "QuotationNumber" => $policydetail['QuotationNumber'], "PolicyNumber" => $policydetail['PolicyNumber'],"status"=>"success"];
			  
			  $query = $this->db->query("select * from ghi_quick_quote_response where emp_id='$emp_id' AND proposal_policy_id='$proposal_policy_id'")->row_array();
				
				if($query > 0){
					$this->db->where("emp_id",$emp_id);
					$this->db->where("lead_id",$lead_id);
					$this->db->where("proposal_policy_id",$proposal_policy_id);
					
					$this->db->update("ghi_quick_quote_response",$arr);
				}else{
					$this->db->insert("ghi_quick_quote_response", $arr);
				}
			 
			  
				return array(
					"status" => "Success",
					"msg" => $policydetail['QuotationNumber']
				);
				
			}else{
			  
				$query = $this->db->query("select * from ghi_quick_quote_response where emp_id='$emp_id' AND proposal_policy_id='$proposal_policy_id'")->row_array();
				if($query > 0){
					$arr = ["count" => $query['count'] + 1,"status"=>"error"];
					$this->db->where("emp_id",$emp_id);
					$this->db->where("lead_id",$lead_id);
					$this->db->where("proposal_policy_id",$proposal_policy_id);
					$this->db->update("ghi_quick_quote_response",$arr);
				}else{
					$arr = ["emp_id" => $emp_id,"status"=>"error"];
					$this->db->insert("ghi_quick_quote_response", $arr);
				}
			  
				return array(
					"status" => "error",
					"msg" => $errorObj['ErrorMessage']
				);
				if($count <= 3){
					sleep(15);
					$this->get_quote_data($lead_id, $emp_id,$master_policy_id, $proposal_policy_id, $proposal_details, $policy_sub_type_id, $sum_insured,$count++);
				}
			}
		}
	}
	
	
	//Full Quote Request
	public function get_full_quote_data($lead_id, $emp_id,$master_policy_id, $proposal_policy_id, $proposal_details, $policy_sub_type_id, $sum_insured){ 

		//get proposal policy details
		$data = array();
		$data['customer_data'] = (array)$this->get_profile($emp_id);
		$data['member_data'] = (array)$this->get_all_member_data($proposal_policy_id);
		$nominees = array();
		if(!empty($proposal_details)){
			$nominees['nominee_relation'] = $proposal_details[0]['nominee_relation'];
			$nominees['nominee_first_name'] = $proposal_details[0]['nominee_first_name'];
			$nominees['nominee_last_name'] = $proposal_details[0]['nominee_last_name'];
			$nominees['nominee_salutation'] = $proposal_details[0]['nominee_salutation'];
			$nominees['nominee_gender'] = $proposal_details[0]['nominee_gender'];
			$nominees['nominee_dob'] = $proposal_details[0]['nominee_dob'];
			$nominees['nominee_contact'] = $proposal_details[0]['nominee_contact'];
			$nominees['nominee_email'] = $proposal_details[0]['nominee_email'];
		}
		$data['nominee_data'] = $nominees;
		$data['proposal_data'] = $proposal_details;
		// print_pre($data);exit;
		//get Api URL with policy_sub_type_id
		$getApiUrl = $this->db->query("select api_url from master_policy_sub_type where policy_sub_type_id='".$policy_sub_type_id."' ")->row_array();
		
		$url = trim($getApiUrl['api_url']);
		
		if($url == ''){
			return array(
				"status" => "error",
				"msg" => "Something Went Wrong"
			);
			exit;
		}
		
		//Get Policy Details
		$policyDetails = $this->db->query("select * from proposal_policy where proposal_policy_id='".$proposal_policy_id."' ")->row_array();
		
		$explode_name = explode(" ", trim($data['customer_data']['customer_name']),2);
		
		//Data Variables
		$uidNo = (!empty($data['customer_data']['adhar'])) ? $data['customer_data']['adhar'] : null;
		$source_name = "ABC_GFB";		
		$MasterPolicyNumber = '61-20-00040-00-00';
		$GroupID = 'GRP001';
		$plan_code = '4211';
		$Product_Code = '4211';
		$micrNo = null;
		
		$Member_Type_Code = "M209";
		$intermediaryCode = '2108233';
		$intermediaryBranchCode = '10MHMUM01';
		$SchemeCode = '4112000003';
		
		//get SumInsured Type
		$sumins_type = $this->db->query("select mi.suminsured_type from master_policy_si_type_mapping as m left join master_suminsured_type as mi ON m.suminsured_type_id = mi.suminsured_type_id where m.master_policy_id='$master_policy_id' ")->row();
		
		//echo "<pre>";print_r($sumins_type);exit;
		$SumInsured_Type = $sumins_type->suminsured_type;
		//echo $SumInsured_Type;exit;
		
		$leadID = $lead_id;
		$txnRefNumber = "pay_FrAaDQjzQFtQWG";
		
		$PaymentMode = "online";
		$bankName = 0;
		$branchName = null;
		$bankLocation = null;
		$chequeType = null;
		$ifscCode = null;
		$terminal_id = "EuxJCz8cZV9V63";
		
		$SumInsured = $sum_insured;
		$product_id = $data['proposal_data'][0]['plan_id'];
		$tax_amount = $policyDetails['tax_amount'];
		
		$collectionAmount = ( $policyDetails['premium_amount'] + $tax_amount );
		
		$transaction_date = explode(" ",$data['proposal_data'][0]['transaction_date']);
		$trans_date = date("Y-m-d", strtotime($transaction_date[0])); 
		//online,RTGS/ NEFT,Cheque
		$collectionMode = (!empty($data['proposal_data'][0]['mode_of_payment'])) ? $data['proposal_data'][0]['mode_of_payment'] : 2;
		
		$SPID = 0;
		$RefCode1 = 0;
		$RefCode2 = 0;
		$Policy_Tanure = 1;
		$AutoRenewal = 'Y';
	
	
		$query_quote = [];
	
		if($policy_sub_type_id == 1){
			$data['customer_data']['customer_id'] = $data['customer_data']['customer_id']."GHI";
			
			$query_quote = $this->db->query("select * from ghi_quick_quote_response where emp_id='$emp_id' and lead_id='$lead_id' and proposal_policy_id='$proposal_policy_id' and status = 'success'")->row_array();
			
			/*if(empty($query_quote)){
				$quote_data = $this->get_quote_data($emp_id, $policy_no);
				if($quote_data['status'] == 'Success')
				{
					$query_quote['QuotationNumber'] = $quote_data['msg'];
				}
			}*/
			
		}else{
			
			
			$query_quote = $this->db->query("select * from ghi_quick_quote_response where emp_id='$emp_id' and lead_id='$lead_id' and proposal_policy_id='$proposal_policy_id' and status = 'success' ")->row_array();
			
			/*if(empty($query_quote)){
				$quote_data = $this->get_quote_data($emp_id, $policy_no, $mem_data);
				if($quote_data['status'] == 'Success')
				{
					$query_quote['QuotationNumber'] = $quote_data['msg'];
				}
			}*/
			
			if($policy_sub_type_id == 2){
				$concat_string = "GPA";
			}elseif($policy_sub_type_id == 3){
				$concat_string = "GCI";
			}elseif($policy_sub_type_id == 7){
				$concat_string = "GP";
			}
			
			$data['customer_data']['customer_id'] = $data['customer_data']['cust_id'].$concat_string;
			
				
		}
	
		if($query_quote['QuotationNumber']){
		   
			$totalMembers = count($data['member_data']);
			$member = [];
				
			$explode_name = array($data['customer_data']['first_name'],$data['customer_data']['middle_name'],$data['customer_data']['last_name']);
			$explode_name_nominee = array($data['nominee_data']['nominee_first_name'],$data['nominee_data']['nominee_last_name']);
			
			for ($i = 0;$i < $totalMembers; $i++){
					
				//Checking relation based on self, spouse, son and daughter
				if($data['member_data'][$i]['relation_with_proposal'] == 1 ){
					$data['member_data'][$i]['relation_code'] = "R001";
				}else if($data['member_data'][$i]['relation_with_proposal'] == 2 ){
					$data['member_data'][$i]['relation_code'] = "R002";
				}else if($data['member_data'][$i]['relation_with_proposal'] == 3 ){
					$data['member_data'][$i]['relation_code'] = "R003";
				}else{
					$data['member_data'][$i]['relation_code'] = "R004";
				}
				
				//Nominee relation code
				if($data['nominee_data']['nominee_relation'] == 1 ){
					$data['nominee_data']['relation_code'] = "R001";
				}else if($data['nominee_data']['nominee_relation'] == 2 ){
					$data['nominee_data']['relation_code'] = "R002";
				}else if($data['nominee_data']['nominee_relation'] == 3 ){
					$data['nominee_data']['relation_code'] = "R003";
				}else{
					$data['nominee_data']['relation_code'] = "R004";
				}
			
				
				$abc = ["PEDCode" => null, "Remarks" => null];
			
				$member[] = ["MemberNo" => $i + 1, "Salutation" => $data['member_data'][$i]['policy_member_salutation'] , "First_Name" => $data['member_data'][$i]['policy_member_first_name'], "Middle_Name" => null, "Last_Name" => !empty($data['member_data'][$i]['policy_member_last_name']) ? $data['member_data'][$i]['policy_member_last_name'] : '.', "Gender" => ($data['member_data'][$i]['policy_member_gender'] == "Male") ? "M" : "F", "DateOfBirth" => date('m/d/Y', strtotime($data['member_data'][$i]['policy_member_dob'])) , "Relation_Code" => trim($data['member_data'][$i]['relation_code']), "Marital_Status" => null, "height" => "0.00", "weight" => "0", "occupation" => "O553", "PrimaryMember" => (($i == 0) ? "Y" : "N") , "MemberproductComponents" => [["PlanCode" => $plan_code, "MemberQuestionDetails" => [["QuestionCode" => null, "Answer" => null, "Remarks" => null]]]], "MemberPED" => $abc, "exactDiagnosis" => null, "dateOfDiagnosis" => null, "lastDateConsultation" => null, "detailsOfTreatmentGiven" => null, "doctorName" => null, "hospitalName" => null, "phoneNumberHosital" => null, "Nominee_First_Name" => $explode_name_nominee[0], "Nominee_Last_Name" => !empty($explode_name_nominee[1]) ? $explode_name_nominee[1] : '.', "Nominee_Contact_Number" => $data['nominee_data']['nominee_contact'], "Nominee_Home_Address" => null, "Nominee_Relationship_Code" => trim($data['nominee_data']['relation_code']), ];

			}
			
			$data['proposal_data']['SourceSystemName_api'] = 'CreditorPortal';	


			$fqrequest = ["ClientCreation" => ["Member_Customer_ID" => $data['customer_data']['customer_id'], "salutation" => $data['customer_data']['salutation'],"firstName" => $data['customer_data']['first_name'], "middleName" => "", "lastName" => !empty($data['customer_data']['last_name']) ? $data['customer_data']['last_name'] : '.',"dateofBirth" => date('m/d/Y', strtotime($data['customer_data']['dob'])) , "gender" => ($data['customer_data']['gender'] == "Male") ? "M" : "F","educationalQualification" => null, "pinCode" => $data['customer_data']['pincode'], "uidNo" => $uidNo,"maritalStatus" => null, "nationality" => "Indian", "occupation" => "O553", "primaryEmailID" => $data['customer_data']['email_id'],"contactMobileNo" => substr(trim($data['customer_data']['mobile_no']), -10), "stdLandlineNo" => null, "panNo" => null, "passportNumber" => null, "contactPerson" => null,"annualIncome" => null, "remarks" => null, "startDate" => date('Y-m-d') , "endDate" => null, "IdProof" => "Adhaar Card", "residenceProof" => null,"ageProof" => null, "others" => null, "homeAddressLine1" => $data['customer_data']['address_line1'], "homeAddressLine2" => null, "homeAddressLine3" => null,"homePinCode" => $data['customer_data']['pincode'], "homeArea" => null, "homeContactMobileNo" => null, "homeContactMobileNo2" => null,"homeSTDLandlineNo" => null, "homeFaxNo" => null, "sameAsHomeAddress" => "1", "mailingAddressLine1" => null, "mailingAddressLine2" => null,"mailingAddressLine3" => null, "mailingPinCode" => null, "mailingArea" => null, "mailingContactMobileNo" => null, "mailingContactMobileNo2" => null,"mailingSTDLandlineNo" => null, "mailingSTDLandlineNo2" => null, "mailingFaxNo" => null, "bankAccountType" => null, "bankAccountNo" => null,"ifscCode" => $ifscCode, "GSTIN" => null, "GSTRegistrationStatus" => "Consumers", "IsEIAavailable" => "0", "ApplyEIA" => "0", "EIAAccountNo" => null,"EIAWith" => "0", "AccountType" => null, "AddressProof" => null, "DOBProof" => null, "IdentityProof" => null],"PolicyCreationRequest" => ["Quotation_Number" => $query_quote['QuotationNumber'], "MasterPolicyNumber" => $MasterPolicyNumber,"GroupID" => $GroupID, "Product_Code" => $Product_Code,"SumInsured_Type"=> $SumInsured_Type,"Policy_Tanure"=> $Policy_Tanure,"Member_Type_Code"=> $Member_Type_Code, "intermediaryCode" => $intermediaryCode,"AutoRenewal" => $AutoRenewal, "intermediaryBranchCode" => $intermediaryBranchCode, "agentSignatureDate" => null,"Customer_Signature_Date" => null,"businessSourceChannel" => null, "AssignPolicy" => "0", "AssigneeName" => null, "leadID" => $leadID, "Source_Name" => $source_name, "SPID" => $SPID, "TCN" => null, "CRTNO" => null, "RefCode1" => $RefCode1,"RefCode2" => $RefCode2, "Employee_Number" => $data['customer_data']['customer_id'],"enumIsEmployeeDiscount" => null, "QuoteDate" => null, "IsPayment" => "1", "PaymentMode" => $collectionMode,"PolicyproductComponents" => [["PlanCode" => $plan_code, "SumInsured" => $SumInsured, "SchemeCode" => $SchemeCode]]], "MemObj" => ["Member" => $member],"ReceiptCreation" => ["officeLocation" => "Mumbai", "modeOfEntry" => "Direct", "cdAcNo" => null, "expiryDate" => null, "payerType" => "Customer", "payerCode" => null,"paymentBy" => "Customer", "paymentByName" => null, "paymentByRelationship" => null, "collectionAmount" => round($collectionAmount,2),"collectionRcvdDate" => $trans_date,"collectionMode" => $collectionMode, "remarks" => null, "instrumentNumber" => $txnRefNumber,"instrumentDate" => $trans_date, "bankName" => $bankName, "branchName" => $branchName,"bankLocation" => $bankLocation, "micrNo" => $micrNo, "chequeType" => $chequeType, "ifscCode" => $ifscCode, "PaymentGatewayName" => $source_name, "TerminalID" => $terminal_id,"CardNo" => null]];
		
			//print_pre($fqrequest);exit;
			//Monolog::saveLog("full_quote_request2", "I", json_encode($fqrequest));

			$request_arr = ["lead_id" => $leadID, "req" => json_encode($fqrequest) ,"product_id" => $product_id, "type"=>"full_quote_request", "proposal_policy_id"=> $proposal_policy_id];
		
			$this->db->insert("logs_docs",$request_arr);
			$insert_id = $this->db->insert_id();

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 90,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => json_encode($fqrequest) ,
				CURLOPT_HTTPHEADER => array(
					"Accept: */*",
					"Cache-Control: no-cache",
					"Connection: keep-alive",
					"Content-Length: " . strlen(json_encode($fqrequest)) ,
					"Content-Type: application/json",
					"Host: bizpre.adityabirlahealth.com"
				) ,
			));

			$response = curl_exec($curl);

			//Monolog::saveLog("full_quote_reponse2", "I", json_encode($response));

			$request_arr = ["res" => json_encode($response)];
			$this->db->where("id",$insert_id);
			$this->db->update("logs_docs",$request_arr);

			$err = curl_error($curl);
			//echo "<pre>";print_r($response);exit;
			curl_close($curl);

			if ($err){
				return array(
					"status" => "error",
					"msg" => $err
				);
			}else{
				$new = simplexml_load_string($response);
				$con = json_encode($new);
				$newArr = json_decode($con, true);
				
				$errorObj = $newArr['errorObj'];
				$premium = $newArr['premium'];
				$return_data = [];

				if($errorObj['ErrorNumber']=='00' || ($errorObj['ErrorNumber']=='302' && $master_policy_id == $newArr['policyDtls']['PolicyNumber'])){
					
					$return_data['status'] = 'Success';
					$return_data['msg'] = $errorObj['ErrorMessage'];

					$api_insert = array(
						"emp_id" => $emp_id,
						"client_id" => $newArr['policyDtls']['ClientID'],
						"certificate_number" => $newArr['policyDtls']['CertificateNumber'],
						"quotation_no" => $newArr['policyDtls']['QuotationNumber'],
						"proposal_no" => $newArr['policyDtls']['ProposalNumber'],
						"policy_no" => $newArr['policyDtls']['PolicyNumber'],
						"gross_premium" => empty($premium['GrossPremium'])?'':$premium['GrossPremium'],
						"status" => "Success",
						"start_date" => $newArr['policyDtls']['startDate'],
						"end_date" => $newArr['policyDtls']['EndDate'],
						"created_date" => date('Y-m-d H:i:s'),
						"proposal_no_lead"=>$proposal_policy_id,
						"PolicyStatus" => $newArr['policyDtls']['PolicyStatus'],
						"letter_url" => $newArr['policyDtls']['LetterURL'],
						"CustomerID" => $newArr['policyDtls']['MemberCustomerID'],
						"MemberCustomerID" => $newArr['policyDtls']['MemberCustomerID'],
						//"COI_url" => $newArr['policyDtls']['COIUrl'],

					);
					
					$this->db->insert('api_proposal_response', $api_insert);
					
					//Monolog::saveLog("api_insert", "I", json_encode($api_insert));	
					
					//HB emandate call
					$this->emandate_HB_call($emp_id,$data['proposal_data'][0]['proposal_details_id']);

					$request_arr = ["lead_id" => $leadID, "req" => json_encode($api_insert) ,"product_id"=> $product_id, "type"=>"api_insert"];
					$this->db->insert("logs_docs",$request_arr);
					//$this->Logs_m->insertLogs($dataArray);
					
					

				}else{

					$return_data = array(
						'status'=>'error',
						"msg" => $errorObj['ErrorMessage']
					);
				}
				
				return $return_data;
				
			}

		}else{
			
			return $return_data = array(
				'status'=>'error',
				"msg" => "Quote error"
			);
		}

	}
	
	// Emadate HB Call
	
	function emandate_HB_call($emp_id,$proposal_details_id)
	{ 	
		$query_check = $this->db->query("select ed.lead_id,ed.plan_id,ed.json_qote,apr.certificate_number,apr.proposal_no,apr.pr_api_id from proposal_details as ed,proposal_policy as p,api_proposal_response as apr,emandate_data as emd where ed.proposal_details_id = p.proposal_details_id and p.proposal_policy_id = apr.proposal_no_lead and p.status in('Success','Payment Received') and apr.mandate_send_status = 0 and emd.lead_id = ed.lead_id and emd.status = 'Success' and ed.proposal_details_id = '$proposal_details_id' group by p.proposal_details_id")->result_array();

		if($query_check){ 
		
		foreach ($query_check as $val)
		{
			
			//BIZ HB call start
			$json_data = json_decode($val['json_qote'],true);

			$url = 'https://bizpre.adityabirlahealth.com/ABHICL_HealthCoreXML/Service1.svc/AddEmendateDetails';
			
			$req_arr = [
					  'EmendateDeatails' => 
					  [
						'EmendateList' => 
						[ 
						  [
							'Bank_Name' => ($json_data['AXISBANKACCOUNT'] == 'Y')?'Axis Bank':'Other',
							'Debit_Account_Number' => $json_data['ACCOUNTNUMBER'],
							'Mandate_Start_Date' => '10-02-2020',
							'Mandate_End_Date' => '11-09-2020',
							'Account_Type' => 'Saving',
							'Bank_Branch_Name' => $json_data['BRANCH_NAME'],
							'MICR' => '123',
							'IFSC' => $json_data['IFSCCODE'],
							'Frequency' => "Annual",
							'Policy_Number' => $val['certificate_number'],
						  ],
						],
					  ],
					];
					
				   $curl = curl_init();

					curl_setopt_array($curl, array(
						CURLOPT_URL => $url,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 60,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "POST",
						CURLOPT_POSTFIELDS => json_encode($req_arr) ,
						CURLOPT_HTTPHEADER => array(
							"Accept: */*",
							"Cache-Control: no-cache",
							"Connection: keep-alive",
							"Content-Length: " . strlen(json_encode($req_arr)) ,
							"Content-Type: application/json",
							"Host: bizpre.adityabirlahealth.com"
						) ,
					));

				   $response = curl_exec($curl);
				   
				   curl_close($curl);
				   
				   // if($response){
					   // $response = json_decode($response);
				   // }
				   
				   $update_arr = ["mandate_send_status" => 1];
						
					$this->db->where("pr_api_id",$val['pr_api_id']);
					$this->db->update("api_proposal_response",$update_arr);
				   
				   $request_arr = ["lead_id" => $query_check['lead_id'], "req" => json_encode($req_arr),"res" => json_encode($response),"product_id"=> "ABC", "type"=>"emandate_HB_post"];
				
					$this->db->insert('logs_docs',$request_arr);
					//BIZ HB call end
					
		}
		
		}
	}
	
	
	//Get Customer data
	function get_profile($emp_id){
		return $this->db->query("select e.* from master_customer as e where e.customer_id='$emp_id'")->row();
    }
	
	//Get all member data
	function get_all_member_data($proposal_policy_id)
    {
        
		$response = $this->db->query('select * from proposal_policy_member where policy_id='.$proposal_policy_id.' ')->result_array();
		//echo $this->db->last_query();
        return $response;
    }
	
	//Get Policy nominee
	function get_all_nominee($emp_id)
    {
        $response = $this->db->select('*,mfr.relation_code,mfr.nominee_type as fr_name')
        ->from('member_policy_nominee AS mpn,master_nominee as mfr')
        ->where('mpn.emp_id', $emp_id)
        ->where('mpn.fr_id = mfr.nominee_id')
        ->get()->row_array();
        if ($response) {
            return $response;
        }
    }
	
	//Get assignment declarations
	function assignmentDeclarationListing($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "assignment_declaration";
		$table_id = 'assignment_declaration_id';
		$default_sort_column = 'assignment_declaration_id';
		$default_sort_order = 'desc';
		
		$condition = "1=1";
		
		
		$colArray = array('p.plan_name','c.creaditor_name','i.label','i.isactive');
		$sortArray = array('p.plan_name','c.creaditor_name','i.label','i.isactive');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		//$s = $post['iSortCol_0'];
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<8;$i++)
		{
			if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
			{
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
			
			
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.assignment_declaration_id, i.plan_id, i.creditor_id, i.label, i.is_active, p.plan_name, c.creaditor_name');
		$this -> db -> from('assignment_declaration as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.assignment_declaration_id, i.plan_id, i.creditor_id, i.label, i.is_active, p.plan_name, c.creaditor_name');
		$this -> db -> from('assignment_declaration as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	//Get assignment declarations
	function getAssignmentDeclarationDetails($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('assignment_declaration as i');
		$this -> db -> where('i.assignment_declaration_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}
	
	//Get ghd declarations
	function ghdDeclarationListing($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "ghd_declaration";
		$table_id = 'declaration_id';
		$default_sort_column = 'declaration_id';
		$default_sort_order = 'desc';
		
		$condition = "1=1";
		
		
		$colArray = array('p.plan_name','c.creaditor_name','i.label','i.isactive');
		$sortArray = array('p.plan_name','c.creaditor_name','i.label','i.isactive');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		//$s = $post['iSortCol_0'];
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<8;$i++)
		{
			if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
			{
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
			
			
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.declaration_id, i.plan_id, i.creditor_id, i.label, i.is_active, p.plan_name, c.creaditor_name');
		$this -> db -> from('ghd_declaration as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.declaration_id, i.plan_id, i.creditor_id, i.label, i.is_active, p.plan_name, c.creaditor_name');
		$this -> db -> from('ghd_declaration as i');
		$this -> db -> join('master_plan as p', 'i.plan_id  = p.plan_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	//Get ghd declarations
	function getGHDDeclarationDetails($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('ghd_declaration as i');
		$this -> db -> where('i.declaration_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}
	
	function discrepancyTypeListing($post)
	{
		$table = "discrepancy_type";
		$table_id = 'discrepancy_type_id';
		$default_sort_column = 'discrepancy_type';
		$default_sort_order = 'asc';
		$condition = "1=1";
		
		$colArray = array('i.discrepancy_type');
		$sortArray = array('i.discrepancy_type');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		$sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;  
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<1;$i++)
		{
			if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
			{
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('*');
		$this -> db -> from('discrepancy_type as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('*');
		$this -> db -> from('discrepancy_type as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	function getDiscrepancyTypeFormData($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('discrepancy_type as i');
		$this -> db -> where('i.discrepancy_type_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}
	
	function discrepancySubTypeListing($post)
	{
		$table = "discrepancy_subtype";
		$table_id = 'discrepancy_subtype_id';
		$default_sort_column = 'discrepancy_subtype';
		$default_sort_order = 'asc';
		$condition = "1=1";
		
		$colArray = array('t.discrepancy_type','i.discrepancy_subtype');
		$sortArray = array('t.discrepancy_type','i.discrepancy_subtype');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		$sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;  
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<2;$i++)
		{
			if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
			{
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.*, t.discrepancy_type');
		$this -> db -> from('discrepancy_subtype as i');
		$this -> db -> join('discrepancy_type as t', 'i.discrepancy_type_id  = t.discrepancy_type_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.*, t.discrepancy_type');
		$this -> db -> from('discrepancy_subtype as i');
		$this -> db -> join('discrepancy_type as t', 'i.discrepancy_type_id  = t.discrepancy_type_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	function getDiscrepancySubTypeFormData($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('discrepancy_subtype as i');
		$this -> db -> where('i.discrepancy_subtype_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}
	
	function saleAdminDashBorad($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "proposal_payment_details";
		$table_id = 'proposal_payment_id';
		$default_sort_column = 'premiumsum';
		$default_sort_order = 'desc';
		$condition = "1=1";
		
		$colArray = array('c.creaditor_name','s.employee_full_name');
		$sortArray = array('premiumsum','c.creaditor_name','s.employee_full_name','premiumsum','premiumsum','premiumsum');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = 200;	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<12;$i++)
		{
			/*if($i==6)
			{
				if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
				{
					$condition .= " AND $colArray[$i] = '".$_POST['sSearch_'.$i]."'";
				}
			}
			else
			{
				if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
				{
					$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
				}
			}*/
			
			if($i == 0 && isset($post['sSearch_0']) && !empty($post['sSearch_0'])){
				$condition .= " AND i.creditor_id = '".$_POST['sSearch_0']."'";
			}
			
			if($i == 1 && isset($post['sSearch_1']) && !empty($post['sSearch_1'])){
				$condition .= " AND i.created_by = '".$_POST['sSearch_1']."'";
			}
			
			if($i == 2 && isset($post['sSearch_2']) && !empty($post['sSearch_2'])){
				$condition .= " AND l.lead_location_id = '".$_POST['sSearch_2']."'";
			}
			
			if($i == 3 && isset($_POST['sSearch_3']) && !empty($_POST['sSearch_3'])){
				if($_POST['sSearch_3'] == 'Pending'){
					$condition .= " AND (l.status != 'Approved' && l.status != 'Rejected' ) ";
				}else{
					$condition .= " AND l.status = '".$_POST['sSearch_3']."'";
				}
				
			}
			
			if($i == 4 && isset($post['sSearch_4']) && !empty($post['sSearch_4'])){
				$condition .= "  AND i.created_at >= '".date("Y-m-d",strtotime($post['sSearch_4']))." 00:00:01' ";
			}
			
			if($i == 5 && isset($post['sSearch_5']) && !empty($post['sSearch_5'])){
				$condition .= "  AND i.created_at <= '".date("Y-m-d",strtotime($post['sSearch_5']))." 23:59:59' ";
			}
			
			
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.creditor_id,i.lead_id,i.trace_id,i.lan_id,i.sum_insured,i.created_by, c.creaditor_name, s.employee_full_name, SUM(i.premium) as premiumsum, SUM(i.premium_with_tax) as premiumwithtaxsum');
		$this -> db -> from('proposal_payment_details as i');
		$this -> db -> join('master_employee as s', 'i.created_by  = s.employee_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('lead_details as l', 'i.lead_id  = l.lead_id', 'left');
		$this->db->where("($condition)");
		$this->db->group_by(array("i.creditor_id", "i.created_by"));
		//$this->db->order_by('premiumwithtaxsum', 'desc');
		$this->db->order_by('premiumwithtaxsum', $_POST['sSearch_6']);

		//$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.creditor_id,i.lead_id,i.trace_id,i.lan_id,i.sum_insured,i.created_by, c.creaditor_name, s.employee_full_name, SUM(i.premium) as premiumsum, SUM(i.premium_with_tax) as premiumwithtaxsum');
		$this -> db -> from('proposal_payment_details as i');
		$this -> db -> join('master_employee as s', 'i.created_by  = s.employee_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('lead_details as l', 'i.lead_id  = l.lead_id', 'left');
		$this->db->where("($condition)");
		$this->db->group_by(array("i.creditor_id", "i.created_by"));
		$this->db->order_by('premiumwithtaxsum', $_POST['sSearch_6']);
		
		$query1 = $this -> db -> get();

		//print_r($this->db->last_query());
		//exit;

		//echo "total: ".$query1 -> num_rows();
		//exit;
		//echo $condition;exit;
		//echo "<pre>mmm";print_r($query1->result());exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			$totcount_val = $totcount;
			$final_result_arr = array();
			$i = 0;
			$tot_premium = 0;
			$tot_premium_withtax = 0;
			$current_month = date("m");
			if($current_month >= 4){
				$cond =  "( YEAR(created_at) BETWEEN '".date("Y")."' AND '".(date("Y") + 1)."' )";
			}else{
				$cond =  "( YEAR(created_at) BETWEEN '".(date("Y") - 1)."' AND '".date("Y")."' )";
			}
			foreach($query->result() as $row){
				//Get Yearly Total    
				//$yearly_query = $this->db->query("Select SUM(premium) as total from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and created_by='".$row->created_by."' and YEAR(created_at) = YEAR(NOW()) ");
				//echo $row->created_by;exit;
				$yearly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and $cond ");
				$yearly_result = $yearly_query->row();
				//echo $yearly_result->totalwithtax;exit;
				
				//Get monthly Total    
				$monthly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) ");
				$monthly_result = $monthly_query->row();
				//echo $monthly_result->total;exit;
				
				//Get weekly Total    
				$weekly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) AND WEEKOFYEAR(created_at) = WEEKOFYEAR(NOW()) ");
				$weekly_result = $weekly_query->row();
				//echo $weekly_result->total;exit;
				
				
				if(!empty($post['sSearch_4']) && !empty($post['sSearch_5'])){
					$row->range_total = round($row->premiumsum, 2);
					$row->range_total_withtax = round($row->premiumwithtaxsum, 2);
					$row->date_from = date("d-m-Y", strtotime($post['sSearch_4']));
					$row->date_to = date("d-m-Y", strtotime($post['sSearch_5']));
				}else{
					$row->range_total = 0;
					$row->range_total_withtax = 0;
					$row->date_from = "-";
					$row->date_to = "-";
				}
				
				$row->yearly_tot = ($yearly_result->total > 0) ? round($yearly_result->total, 2) : 0;
				$row->monthly_tot = ($monthly_result->total > 0) ? round($monthly_result->total, 2) : 0;
				$row->weekly_tot = ($weekly_result->total > 0) ? round($weekly_result->total, 2) : 0;

				$row->yearly_tot_withtax = ($yearly_result->totalwithtax > 0) ? round($yearly_result->totalwithtax, 2) : 0;
				$row->monthly_tot_withtax = ($monthly_result->totalwithtax > 0) ? round($monthly_result->totalwithtax, 2) : 0;
				$row->weekly_tot_withtax = ($weekly_result->totalwithtax > 0) ? round($weekly_result->totalwithtax, 2) : 0;
				
				if($_POST['sSearch_6'] == 'desc'){
					$row->rank = ++$i;
				}else{
					$row->rank = $totcount_val--;
				}
				
				$final_result_arr[] = $row;
			}
			

			$tot_premium = $tot_premium_withtax = $weeklyNetTot = $weeklyGrossTot = $mothlyNetTot = $mothlyGrossTot = $yearlyNetTot = $yearlyGrossTot = $dateRangeNetTot = $dateRangeGrossTot = 0;
			
			foreach($query1->result() as $row1){
				$tot_premium += $row1->premiumsum;
				$tot_premium_withtax += $row1->premiumwithtaxsum;

				//yearly total
				$yearly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row1->creditor_id."' and created_by='".$row1->created_by."' and $cond ");
				$yearly_result = $yearly_query->row();

				//echo $row1->creditor_id."<br/>";

				//Get monthly Total    
				$monthly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row1->creditor_id."' and created_by='".$row1->created_by."' and YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) ");
				$monthly_result = $monthly_query->row();

				//Get weekly Total    
				$weekly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row1->creditor_id."' and created_by='".$row1->created_by."' and YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) AND WEEKOFYEAR(created_at) = WEEKOFYEAR(NOW()) ");
				$weekly_result = $weekly_query->row();

				$yearlyNetTot += $yearly_result->total;
				$mothlyNetTot += $monthly_result->total;
				$weeklyNetTot += $weekly_result->total;

				$yearlyGrossTot += $yearly_result->totalwithtax;
				$mothlyGrossTot += $monthly_result->totalwithtax;
				$weeklyGrossTot += $weekly_result->totalwithtax;

				if(!empty($post['sSearch_4']) && !empty($post['sSearch_5'])){
					$dateRangeNetTot += $row1->premiumsum;
					$dateRangeGrossTot += $row1->premiumwithtaxsum;
				}


			}

			//echo "yearlyNetTot: ".$yearlyNetTot."mothlyNetTot".$mothlyNetTot."weeklyNetTot".$weeklyNetTot."yearlyGrossTot".$yearlyGrossTot."mothlyGrossTot".$mothlyGrossTot."weeklyGrossTot".$weeklyGrossTot."dateRangeNetTot".$dateRangeNetTot."dateRangeGrossTot".$dateRangeGrossTot;
			//exit;
			
			//echo $tot_premium;exit;
			return array("query_result" => $final_result_arr, "totalRecords" => $totcount, "tot_premium" => round($tot_premium,2), "tot_premium_withtax" => round($tot_premium_withtax,2), "yearlyNetTot" => round($yearlyNetTot,2), "yearlyGrossTot"=>round($yearlyGrossTot,2), "mothlyNetTot"=>round($mothlyNetTot,2), "mothlyGrossTot"=>round($mothlyGrossTot,2), "weeklyNetTot"=>round($weeklyNetTot,2), "weeklyGrossTot"=>round($weeklyGrossTot,2), "dateRangeNetTot"=>$dateRangeNetTot, "dateRangeGrossTot"=>$dateRangeGrossTot );
			//return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0, "tot_premium" => 0, "tot_premium_withtax" =>0, "yearlyNetTot" => 0, "yearlyGrossTot"=>0, "mothlyNetTot"=>0, "mothlyGrossTot"=>0, "weeklyNetTot"=>0, "weeklyGrossTot"=>0 );
		}
	}
	
	/*function saleAdminDashBorad($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "proposal_payment_details";
		$table_id = 'proposal_payment_id';
		$default_sort_column = 'premiumsum';
		$default_sort_order = 'desc';
		$condition = "1=1";
		
		$colArray = array('c.creaditor_name','s.employee_full_name');
		$sortArray = array('premiumsum','c.creaditor_name','s.employee_full_name','premiumsum','premiumsum','premiumsum');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<12;$i++)
		{
			if($i==6)
			{
				if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
				{
					$condition .= " AND $colArray[$i] = '".$_POST['sSearch_'.$i]."'";
				}
			}
			else
			{
				if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
				{
					$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
				}
			}
			
			
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$sql = "SELECT `i`.*, `c`.`creaditor_name`, `s`.`employee_full_name`, SUM(i.premium) as premiumsum
				FROM `proposal_payment_details` as `i`
				LEFT JOIN `master_employee` as `s` ON `i`.`created_by` = `s`.`employee_id`
				LEFT JOIN `master_ceditors` as `c` ON `i`.`creditor_id` = `c`.`creditor_id`
				WHERE ($condition)
				GROUP BY `i`.`creditor_id`, `i`.`created_by`
				
				LIMIT $page,$rows";
		echo $sql;exit;
		
		
		$query = $this->db->query($sql);
		
		//echo $query -> num_rows();exit;
		//print_r($this->db->last_query());
		//exit;
		
		$sql1 = "SELECT `i`.*, `c`.`creaditor_name`, `s`.`employee_full_name`, SUM(i.premium) as premiumsum
				FROM `proposal_payment_details` as `i`
				LEFT JOIN `master_employee` as `s` ON `i`.`created_by` = `s`.`employee_id`
				LEFT JOIN `master_ceditors` as `c` ON `i`.`creditor_id` = `c`.`creditor_id`
				WHERE ($condition)
				GROUP BY `i`.`creditor_id`, `i`.`created_by`
				LIMIT $page,$rows";
		$query1 = $this->db->query($sql);
		
		//var_dump($query->result());exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			$final_result_arr = array();
			$i = 0;
			foreach($query->result() as $row){
				$row->rank = ++$i;
				$final_result_arr[] = $row;
			}
			
			//echo "";print_r($final_result_arr);exit;
			//return array("query_result" => $query->result(), "totalRecords" => $totcount);
			return array("query_result" => $final_result_arr, "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}*/
	
	function exportSaleAdminDashBorad($post)
	{
		//echo "<pre>";print_r($post);exit;
		$condition = "1=1";
		
		if(isset($_POST['creditor_id']) && !empty($_POST['creditor_id'])){
			$condition .= " AND i.creditor_id = '".$_POST['creditor_id']."'";
		}
		
		if(isset($_POST['sm_id']) && !empty($_POST['sm_id'])){
			$condition .= " AND i.created_by = '".$_POST['sm_id']."'";
		}
		
		if(isset($_POST['location_id']) && !empty($_POST['location_id'])){
			$condition .= " AND l.lead_location_id = '".$_POST['location_id']."'";
		}
		
		if(isset($_POST['status']) && !empty($_POST['status'])){
			$condition .= " AND l.status = '".$_POST['status']."'";
		}
		
		if(isset($_POST['date_from']) && !empty($_POST['date_from'])){
			$condition .= "  AND i.created_at >= '".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:01' ";
		}
		
		if(isset($_POST['date_to']) && !empty($_POST['date_to'])){
			$condition .= "  AND i.created_at <= '".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59' ";
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.*, c.creaditor_name, s.employee_full_name, SUM(i.premium) as premiumsum, SUM(i.premium_with_tax) as premiumwithtaxsum');
		$this -> db -> from('proposal_payment_details as i');
		$this -> db -> join('master_employee as s', 'i.created_by  = s.employee_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('lead_details as l', 'i.lead_id  = l.lead_id', 'left');
		$this->db->where("($condition)");
		$this->db->group_by(array("i.creditor_id", "i.created_by"));
		$this->db->order_by('premiumsum', 'desc');
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query -> num_rows();
			$final_result_arr = array();
			$i = 0;
			$tot_premium = 0;
			$tot_premium_withtax = 0;
			$weeklyNetTot = $weeklyGrossTot = $mothlyNetTot = $mothlyGrossTot = $yearlyNetTot = $yearlyGrossTot = $dateRangeNetTot = $dateRangeGrossTot = 0;

			$current_month = date("m");
			if($current_month >= 4){
				$cond =  "( YEAR(created_at) BETWEEN '".date("Y")."' AND '".(date("Y") + 1)."' )";
			}else{
				$cond =  "( YEAR(created_at) BETWEEN '".(date("Y") - 1)."' AND '".date("Y")."' )";
			}
			
			foreach($query->result() as $row){
				//Get Yearly Total    
				//$yearly_query = $this->db->query("Select SUM(premium) as total from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and YEAR(created_at) = YEAR(NOW()) ");
				
				$yearly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and $cond ");
				$yearly_result = $yearly_query->row();
				//echo $yearly_result->total;exit;
				
				//Get monthly Total    
				$monthly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) ");
				$monthly_result = $monthly_query->row();
				//echo $monthly_result->total;exit;
				
				//Get weekly Total    
				$weekly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) AND WEEKOFYEAR(created_at) = WEEKOFYEAR(NOW()) ");
				$weekly_result = $weekly_query->row();
				//echo $weekly_result->total;exit;
				
				$row->yearly_tot = ($yearly_result->total > 0) ? round($yearly_result->total, 2) : 0;
				$row->monthly_tot = ($monthly_result->total > 0) ? round($monthly_result->total, 2) : 0;
				$row->weekly_tot = ($weekly_result->total > 0) ? round($weekly_result->total, 2) : 0;

				$row->yearly_tot_withtax = ($yearly_result->totalwithtax > 0) ? round($yearly_result->totalwithtax, 2) : 0;
				$row->monthly_tot_withtax = ($monthly_result->totalwithtax > 0) ? round($monthly_result->totalwithtax, 2) : 0;
				$row->weekly_tot_withtax = ($weekly_result->totalwithtax > 0) ? round($weekly_result->totalwithtax, 2) : 0;

				$yearlyNetTot += $yearly_result->total;
				$mothlyNetTot += $monthly_result->total;
				$weeklyNetTot += $weekly_result->total;

				$yearlyGrossTot += $yearly_result->totalwithtax;
				$mothlyGrossTot += $monthly_result->totalwithtax;
				$weeklyGrossTot += $weekly_result->totalwithtax;

				if(!empty($_POST['date_from']) && !empty($_POST['date_to'])){
					$row->range_total = round($row->premiumsum, 2);
					$row->range_total_withtax = round($row->premiumwithtaxsum, 2);
					$row->date_from = date("d-m-Y", strtotime($_POST['date_from']));
					$row->date_to = date("d-m-Y", strtotime($_POST['date_to']));

					$dateRangeNetTot += $row->premiumsum;
					$dateRangeGrossTot += $row->premiumwithtaxsum;
				}else{
					$row->range_total = 0;
					$row->range_total_withtax = 0;
					$row->date_from = "-";
					$row->date_to = "-";
				}
				
				$row->rank = ++$i;
				$final_result_arr[] = $row;
				
				$tot_premium += $row->premiumsum;
				$tot_premium_withtax += $row->premiumwithtaxsum;

			}
			
			
			//echo $tot_premium;exit;
			return array("query_result" => $final_result_arr, "totalRecords" => $totcount, "tot_premium" => round($tot_premium, 2), "tot_premium_withtax" => round($tot_premium_withtax,2)  , "yearlyNetTot" => round($yearlyNetTot,2), "yearlyGrossTot"=>round($yearlyGrossTot,2), "mothlyNetTot"=>round($mothlyNetTot,2), "mothlyGrossTot"=>round($mothlyGrossTot,2), "weeklyNetTot"=>round($weeklyNetTot,2), "weeklyGrossTot"=>round($weeklyGrossTot,2), "dateRangeNetTot"=>$dateRangeNetTot, "dateRangeGrossTot"=>$dateRangeGrossTot);
			//return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0, "tot_premium" => 0, "tot_premium_withtax" =>0, "yearlyNetTot" => 0, "yearlyGrossTot"=>0, "mothlyNetTot"=>0, "mothlyGrossTot"=>0, "weeklyNetTot"=>0, "weeklyGrossTot"=>0, "dateRangeNetTot"=>0, "dateRangeGrossTot"=>0);
		}
	}
	
	function adminDashBoradDetails($post)
	{
//var_dump($post);exit;

		$table = "proposal_payment_details";
		$table_id = 'lead_id';
		$default_sort_column = 'i.lead_id';
		$default_sort_order = 'desc';
		
		$condition = "i.creditor_id='".$_POST['cid']."' AND created_by='".$_POST['smid']."' ";
		
		$colArray = array('c.creaditor_name','s.employee_full_name');
		$sortArray = array('i.trace_id','i.created_at','cst.first_name','cst.last_name','p.plan_name','pt.policy_type_name','i.premium','i.sum_insured','pm.payment_mode_name','i.status');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<12;$i++)
		{
			if($i == 0 && isset($post['sSearch_0']) && !empty($post['sSearch_0']) && $post['sSearch_0'] =='Pending' ){
				$condition .= " AND (l.status != 'Approved' && l.status != 'Rejected' ) ";
			}else if($i == 0 && isset($post['sSearch_0']) && !empty($post['sSearch_0']) && $post['sSearch_0'] !='Pending' && $post['sSearch_0'] !='Approved'){
				$condition .= " AND l.status = '".$_POST['sSearch_0']."'";
			}else if($i == 0 && isset($post['sSearch_0']) && !empty($post['sSearch_0']) && $post['sSearch_0'] =='Approved' ){
                $condition .= " AND (l.status = 'Customer-Payment-Received' ) ";
            }
			//Customer-Payment-Received
			
		}
		//echo $condition;exit;
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.creditor_id, i.trace_id, i.sum_insured, i.premium, i.payment_mode, i.created_by, i.lead_id, l.status, l.createdon, cst.first_name, cst.last_name, p.plan_name, pm.payment_mode_name,pt.policy_type_name,(select Group_concat(certificate_number) from api_proposal_response apr where apr.lead_id=l.lead_id) as COI_numbers');
		$this -> db -> from('proposal_payment_details as i');
		$this -> db -> join('lead_details as l', 'i.lead_id  = l.lead_id', 'left');
		$this -> db -> join('master_customer as cst', 'l.primary_customer_id  = cst.customer_id', 'left');
		$this -> db -> join('master_plan as p', 'l.plan_id  = p.plan_id', 'left');
		$this->db->join('master_policy_type as pt', 'p.policy_type_id = pt.policy_type_id');
		$this -> db -> join('payment_modes as pm', 'i.payment_mode  = pm.payment_mode_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.creditor_id, i.trace_id, i.sum_insured, i.premium, i.payment_mode, i.created_by, i.lead_id, l.status, l.createdon, cst.first_name, cst.last_name, p.plan_name, pm.payment_mode_name,pt.policy_type_name');
		$this -> db -> from('proposal_payment_details as i');
		$this -> db -> join('lead_details as l', 'i.lead_id  = l.lead_id', 'left');
		$this -> db -> join('master_customer as cst', 'l.primary_customer_id  = cst.customer_id', 'left');
		$this -> db -> join('master_plan as p', 'l.plan_id  = p.plan_id', 'left');
		$this->db->join('master_policy_type as pt', 'p.policy_type_id = pt.policy_type_id');
		$this -> db -> join('payment_modes as pm', 'i.payment_mode  = pm.payment_mode_id', 'left');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		//echo $condition;exit;
		//echo "<pre>mmm";print_r($query->result());exit;

		//print_r($this->db->last_query());
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	
	function exportDashBoradDetails()
	{
		//echo "<pre>";print_r($post);exit;
		$table = "proposal_payment_details";
		$table_id = 'lead_id';
		$default_sort_column = 'i.lead_id';
		$default_sort_order = 'desc';
		
		$condition = "i.creditor_id='".$_POST['creditor_id']."' AND created_by='".$_POST['sm_id']."' ";
		
		for($i=0;$i<12;$i++)
		{
			if(isset($_POST['status']) && !empty($_POST['status']) && $_POST['status'] =='Pending' ){
				$condition .= " AND (l.status != 'Approved' && l.status != 'Rejected' ) ";
			}else if(isset($_POST['status']) && !empty($_POST['status']) && $_POST['status'] !='Pending' ){
				$condition .= " AND l.status = '".$_POST['status']."' ";
			}
			
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.creditor_id, i.trace_id, i.sum_insured, i.premium, i.payment_mode, i.created_by, i.lead_id, l.status, l.createdon, cst.first_name, cst.last_name, p.plan_name, pm.payment_mode_name,pt.policy_type_name');
		$this -> db -> from('proposal_payment_details as i');
		$this -> db -> join('lead_details as l', 'i.lead_id  = l.lead_id', 'left');
		$this -> db -> join('master_customer as cst', 'l.primary_customer_id  = cst.customer_id', 'left');
		$this -> db -> join('master_plan as p', 'l.plan_id  = p.plan_id', 'left');
		$this->db->join('master_policy_type as pt', 'p.policy_type_id = pt.policy_type_id');
		$this -> db -> join('payment_modes as pm', 'i.payment_mode  = pm.payment_mode_id', 'left');
		$this->db->where("($condition)");
		//$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		//echo $condition;exit;
		//echo "<pre>mmm";print_r($query->result());exit;
		
		if($query -> num_rows() >= 1)
		{
			return array("query_result" => $query->result(), "totalRecords" => 0);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}

	function smDashBorad($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "proposal_payment_details";
		$table_id = 'proposal_payment_id';
		$default_sort_column = 'premiumsum';
		$default_sort_order = 'desc';
		$condition = "1=1 AND i.created_by='".$_POST['sm_id']."' ";
		
		$colArray = array('c.creaditor_name','s.employee_full_name');
		$sortArray = array('premiumsum','c.creaditor_name','s.employee_full_name','premiumsum','premiumsum','premiumsum');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = 200;	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<12;$i++)
		{
			
			if($i == 0 && isset($_POST['sSearch_0']) && !empty($_POST['sSearch_0'])){
				$condition .= " AND i.creditor_id = '".$_POST['sSearch_0']."'";
			}
			
			if($i == 1 && isset($post['sSearch_1']) && !empty($post['sSearch_1'])){
				$condition .= "  AND i.created_at >= '".date("Y-m-d",strtotime($post['sSearch_1']))." 00:00:01' ";
			}
			
			if($i == 2 && isset($post['sSearch_2']) && !empty($post['sSearch_2'])){
				$condition .= "  AND i.created_at <= '".date("Y-m-d",strtotime($post['sSearch_2']))." 23:59:59' ";
			}
			
			if($i == 3 && isset($_POST['sSearch_3']) && !empty($_POST['sSearch_3'])){
				if($_POST['Searchkey_3'] == 'Pending'){
					$condition .= " AND (l.status != 'Approved' && l.status != 'Rejected' ) ";
				}else{
					$condition .= " AND l.status = '".$_POST['sSearch_3']."'";
				}
			}
			
			
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.*, c.creaditor_name, s.employee_full_name, SUM(i.premium) as premiumsum, SUM(i.premium_with_tax) as premiumwithtaxsum');
		$this -> db -> from('proposal_payment_details as i');
		$this -> db -> join('master_employee as s', 'i.created_by  = s.employee_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('lead_details as l', 'i.lead_id  = l.lead_id', 'left');
		$this->db->where("($condition)");
		$this->db->group_by(array("i.creditor_id", "i.created_by"));
		$this->db->order_by('premiumwithtaxsum', $_POST['sSearch_4']);
		//$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();

//		print_r($this->db->last_query());
//		exit;
		
		$this -> db -> select('i.*, c.creaditor_name, s.employee_full_name, SUM(i.premium) as premiumsum, SUM(i.premium_with_tax) as premiumwithtaxsum');
		$this -> db -> from('proposal_payment_details as i');
		$this -> db -> join('master_employee as s', 'i.created_by  = s.employee_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('lead_details as l', 'i.lead_id  = l.lead_id', 'left');
		$this->db->where("($condition)");
		$this->db->group_by(array("i.creditor_id", "i.created_by"));
		$this->db->order_by('premiumwithtaxsum', $_POST['sSearch_4']);
		//$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		//echo $condition;exit;
		//echo "<pre>mmm";print_r($query->result());exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			$totcount_val = $totcount;
			$final_result_arr = array();
			$i = 0;
			$tot_premium = 0;
			$tot_premium_withtax = 0;
			$current_month = date("m");
			if($current_month >= 4){
				$cond =  "( YEAR(created_at) BETWEEN '".date("Y")."' AND '".(date("Y") + 1)."' )";
			}else{
				$cond =  "( YEAR(created_at) BETWEEN '".(date("Y") - 1)."' AND '".date("Y")."' )";
			}
			foreach($query->result() as $row){
				//Get Yearly Total    
				$yearly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and $cond ");
				$yearly_result = $yearly_query->row();
				//echo $yearly_result->total;exit;
				
				//Get monthly Total    
				$monthly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) ");
				$monthly_result = $monthly_query->row();
				//echo $monthly_result->total;exit;
				
				//Get weekly Total    
				$weekly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) AND WEEKOFYEAR(created_at) = WEEKOFYEAR(NOW()) ");
				$weekly_result = $weekly_query->row();
				//echo $weekly_result->total;exit;
				
				
				if(!empty($post['sSearch_1']) && !empty($post['sSearch_2'])){
					$row->range_total = round($row->premiumsum, 2);
					$row->range_total_withtax = round($row->premiumwithtaxsum, 2);
					$row->date_from = date("d-m-Y", strtotime($post['sSearch_1']));
					$row->date_to = date("d-m-Y", strtotime($post['sSearch_2']));
				}else{
					$row->range_total = 0;
					$row->range_total_withtax = 0;
					$row->date_from = "-";
					$row->date_to = "-";
				}
				
				$row->yearly_tot = ($yearly_result->total > 0) ? round($yearly_result->total, 2) : 0;
				$row->monthly_tot = ($monthly_result->total > 0) ? round($monthly_result->total, 2) : 0;
				$row->weekly_tot = ($weekly_result->total > 0) ? round($weekly_result->total, 2) : 0;

				$row->yearly_tot_withtax = ($yearly_result->totalwithtax > 0) ? round($yearly_result->totalwithtax, 2) : 0;
				$row->monthly_tot_withtax = ($monthly_result->totalwithtax > 0) ? round($monthly_result->totalwithtax, 2) : 0;
				$row->weekly_tot_withtax = ($weekly_result->totalwithtax > 0) ? round($weekly_result->totalwithtax, 2) : 0;
				
				if($_POST['sSearch_4'] == 'desc'){
					$row->rank = ++$i;
				}else{
					$row->rank = $totcount_val--;
				}
				$final_result_arr[] = $row;
			}
			
			$tot_premium = $tot_premium_withtax = $weeklyNetTot = $weeklyGrossTot = $mothlyNetTot = $mothlyGrossTot = $yearlyNetTot = $yearlyGrossTot = $dateRangeNetTot = $dateRangeGrossTot = 0;
			
			foreach($query1->result() as $row1){
				//echo $row1->premiumsum."<br/>";
				$tot_premium += $row1->premiumsum;
				$tot_premium_withtax += $row1->premiumwithtaxsum;

				//Get Yearly Total    
				$yearly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row1->creditor_id."' and created_by='".$row1->created_by."' and $cond ");
				$yearly_result = $yearly_query->row();
				//echo $yearly_result->total;exit;
				
				//Get monthly Total    
				$monthly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row1->creditor_id."' and created_by='".$row1->created_by."' and YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) ");
				$monthly_result = $monthly_query->row();
				//echo $monthly_result->total;exit;
				
				//Get weekly Total    
				$weekly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row1->creditor_id."' and created_by='".$row1->created_by."' and YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) AND WEEKOFYEAR(created_at) = WEEKOFYEAR(NOW()) ");
				$weekly_result = $weekly_query->row();
				//echo $weekly_result->total;exit;
				
				$yearlyNetTot += $yearly_result->total;
				$mothlyNetTot += $monthly_result->total;
				$weeklyNetTot += $weekly_result->total;

				$yearlyGrossTot += $yearly_result->totalwithtax;
				$mothlyGrossTot += $monthly_result->totalwithtax;
				$weeklyGrossTot += $weekly_result->totalwithtax;

				if(!empty($post['sSearch_1']) && !empty($post['sSearch_2'])){
					$dateRangeNetTot += $row1->premiumsum;
					$dateRangeGrossTot += $row1->premiumwithtaxsum;
				}


			}
			
			//echo $tot_premium;exit;
			return array("query_result" => $final_result_arr, "totalRecords" => $totcount, "tot_premium" => round($tot_premium, 2), "tot_premium_withtax" => round($tot_premium_withtax,2), "yearlyNetTot" => round($yearlyNetTot,2), "yearlyGrossTot"=>round($yearlyGrossTot,2), "mothlyNetTot"=>round($mothlyNetTot,2), "mothlyGrossTot"=>round($mothlyGrossTot,2), "weeklyNetTot"=>round($weeklyNetTot,2), "weeklyGrossTot"=>round($weeklyGrossTot,2), "dateRangeNetTot"=>$dateRangeNetTot, "dateRangeGrossTot"=>$dateRangeGrossTot );
			//return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0, "tot_premium" => 0, "tot_premium_withtax" => 0, "yearlyNetTot" => 0, "yearlyGrossTot"=>0, "mothlyNetTot"=>0, "mothlyGrossTot"=>0, "weeklyNetTot"=>0, "weeklyGrossTot"=>0);
		}
	}
	
	function exportSMDashBorad($post)
	{
		//echo "<pre>";print_r($post);exit;
		$condition = "1=1 AND i.created_by='".$_POST['sm_id']."' ";
		
		if(isset($_POST['creditor_id']) && !empty($_POST['creditor_id'])){
			$condition .= " AND i.creditor_id = '".$_POST['creditor_id']."'";
		}
		
		if(isset($_POST['sm_id']) && !empty($_POST['sm_id'])){
			$condition .= " AND i.created_by = '".$_POST['sm_id']."'";
		}
		
		if(isset($_POST['status']) && !empty($_POST['status'])){
			$condition .= " AND l.status = '".$_POST['status']."'";
		}
		
		if(isset($_POST['date_from']) && !empty($_POST['date_from'])){
			$condition .= "  AND i.created_at >= '".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:01' ";
		}
		
		if(isset($_POST['date_to']) && !empty($_POST['date_to'])){
			$condition .= "  AND i.created_at <= '".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59' ";
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.*, c.creaditor_name, s.employee_full_name, SUM(i.premium) as premiumsum, SUM(i.premium_with_tax) as premiumwithtaxsum');
		$this -> db -> from('proposal_payment_details as i');
		$this -> db -> join('master_employee as s', 'i.created_by  = s.employee_id', 'left');
		$this -> db -> join('master_ceditors as c', 'i.creditor_id  = c.creditor_id', 'left');
		$this -> db -> join('lead_details as l', 'i.lead_id  = l.lead_id', 'left');
		$this->db->where("($condition)");
		$this->db->group_by(array("i.creditor_id", "i.created_by"));
		$this->db->order_by('premiumsum', 'desc');
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query -> num_rows();
			$final_result_arr = array();
			$i = 0;
			$tot_premium = 0;
			$tot_premium_withtax = 0;
			$weeklyNetTot = $weeklyGrossTot = $mothlyNetTot = $mothlyGrossTot = $yearlyNetTot = $yearlyGrossTot = $dateRangeNetTot = $dateRangeGrossTot = 0;

			$current_month = date("m");
			if($current_month >= 4){
				$cond =  "( YEAR(created_at) BETWEEN '".date("Y")."' AND '".(date("Y") + 1)."' )";
			}else{
				$cond =  "( YEAR(created_at) BETWEEN '".(date("Y") - 1)."' AND '".date("Y")."' )";
			}
			
			foreach($query->result() as $row){
				//Get Yearly Total 
				
				$yearly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and $cond ");
				$yearly_result = $yearly_query->row();
				//echo $yearly_result->total;exit;
				
				//Get monthly Total    
				$monthly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) ");
				$monthly_result = $monthly_query->row();
				//echo $monthly_result->total;exit;
				
				//Get weekly Total    
				$weekly_query = $this->db->query("Select SUM(premium) as total, SUM(premium_with_tax) as totalwithtax from proposal_payment_details where creditor_id='".$row->creditor_id."' and created_by='".$row->created_by."' and YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) AND WEEKOFYEAR(created_at) = WEEKOFYEAR(NOW()) ");
				$weekly_result = $weekly_query->row();
				//echo $weekly_result->total;exit;
				
				$row->yearly_tot = ($yearly_result->total > 0) ? $yearly_result->total : 0;
				$row->monthly_tot = ($monthly_result->total > 0) ? $monthly_result->total : 0;
				$row->weekly_tot = ($weekly_result->total > 0) ? $weekly_result->total : 0;

				$row->yearly_tot_withtax = ($yearly_result->totalwithtax > 0) ? round($yearly_result->totalwithtax, 2) : 0;
				$row->monthly_tot_withtax = ($monthly_result->totalwithtax > 0) ? round($monthly_result->totalwithtax, 2) : 0;
				$row->weekly_tot_withtax = ($weekly_result->totalwithtax > 0) ? round($weekly_result->totalwithtax, 2) : 0;

				$yearlyNetTot += $yearly_result->total;
				$mothlyNetTot += $monthly_result->total;
				$weeklyNetTot += $weekly_result->total;

				$yearlyGrossTot += $yearly_result->totalwithtax;
				$mothlyGrossTot += $monthly_result->totalwithtax;
				$weeklyGrossTot += $weekly_result->totalwithtax;

				if(!empty($_POST['date_from']) && !empty($_POST['date_to'])){
					$row->range_total = round($row->premiumsum, 2);
					$row->range_total_withtax = round($row->premiumwithtaxsum, 2);
					$row->date_from = date("d-m-Y", strtotime($post['date_from']));
					$row->date_to = date("d-m-Y", strtotime($post['date_to']));

					$dateRangeNetTot += $row->premiumsum;
					$dateRangeGrossTot += $row->premiumwithtaxsum;
				}else{
					$row->range_total = 0;
					$row->range_total_withtax = 0;
					$row->date_from = "-";
					$row->date_to = "-";
				}
				
				$row->rank = ++$i;
				$final_result_arr[] = $row;
				
				$tot_premium += $row->premiumsum;
				$tot_premium_withtax += $row->premiumwithtaxsum;
			}
			
			
			//echo $tot_premium;exit;
			return array("query_result" => $final_result_arr, "totalRecords" => $totcount, "tot_premium" => $tot_premium, "tot_premium_withtax" => round($tot_premium_withtax,2) , "yearlyNetTot" => round($yearlyNetTot,2), "yearlyGrossTot"=>round($yearlyGrossTot,2), "mothlyNetTot"=>round($mothlyNetTot,2), "mothlyGrossTot"=>round($mothlyGrossTot,2), "weeklyNetTot"=>round($weeklyNetTot,2), "weeklyGrossTot"=>round($weeklyGrossTot,2), "dateRangeNetTot"=>$dateRangeNetTot, "dateRangeGrossTot"=>$dateRangeGrossTot);
			//return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0, "tot_premium_withtax" =>0 , "yearlyNetTot" => 0, "yearlyGrossTot"=>0, "mothlyNetTot"=>0, "mothlyGrossTot"=>0, "weeklyNetTot"=>0, "weeklyGrossTot"=>0);
		}
	}

	function getLogActions()
	{			
		$this -> db -> select('i.action');
		$this -> db -> from('application_logs as i');
		//$this -> db -> where("($condition)");
		$this->db->group_by("i.action");
		
		$query = $this->db->get();
		
		// echo "<br/>";
		// print_r($this->db->last_query());
		// exit;
	   
		if($query->num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	function applicationLogs($post)
	{
		//echo "<pre>";print_r($post);exit;
		$table = "application_logs";
		$table_id = 'log_id';
		$default_sort_column = 'log_id';
		$default_sort_order = 'desc';
		$condition = "1=1";
		
		$colArray = array('l.trace_id','i.created_on', 'i.action', 'i.request_data', 'i.response_data', 'c.full_name');
		$sortArray = array('l.trace_id','i.created_on', 'i.action', 'i.request_data', 'i.response_data', 'c.full_name');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		//echo $post['iSortCol_0'];exit;
		$sort = isset($post['iSortCol_0']) ? strval($post['iSortCol_0']) : $default_sort_column; 
		//echo $sort;exit;		
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<12;$i++)
		{
			
			if($i == 0 && isset($_POST['sSearch_0']) && !empty($_POST['sSearch_0'])){
				$condition .= " AND l.trace_id like '%".$_POST['sSearch_0']."%'";
			}
			
			if($i == 1 && isset($_POST['sSearch_1']) && !empty($_POST['sSearch_1'])){
				//$condition .= " AND l.trace_id = '".$_POST['sSearch_0']."'";
				$condition .= " AND c.full_name like '%".$_POST['sSearch_1']."%'";
			}

			if($i == 2 && isset($_POST['sSearch_2']) && !empty($_POST['sSearch_2'])){
				//$condition .= " AND l.trace_id = '".$_POST['sSearch_0']."'";
				$condition .= " AND c.mobile_no like '%".$_POST['sSearch_2']."%'";
			}
			
			if($i == 3 && isset($_POST['sSearch_3']) && !empty($_POST['sSearch_3'])){
				$condition .= "  AND i.created_on >= '".date("Y-m-d",strtotime($_POST['sSearch_3']))." 00:00:01' ";
			}
			
			if($i == 4 && isset($_POST['sSearch_4']) && !empty($_POST['sSearch_4'])){
				$condition .= "  AND i.created_on <= '".date("Y-m-d",strtotime($_POST['sSearch_4']))." 23:59:59'";
			}

			if($i == 5 && isset($_POST['sSearch_5']) && !empty($_POST['sSearch_5'])){
				$condition .= "  AND i.action = '".$_POST['sSearch_5']."' ";
			}
					
			
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.*,l.trace_id, c.full_name, c.mobile_no, e.employee_full_name');
		$this -> db -> from('application_logs as i');
		$this -> db -> join('lead_details as l', 'i.lead_id  = l.lead_id', 'left');
		$this -> db -> join('master_customer as c', 'l.primary_customer_id  = c.customer_id', 'left');
		$this -> db -> join('master_employee as e', 'i.created_by  = e.employee_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.*,l.trace_id, c.full_name, c.mobile_no, e.employee_full_name');
		$this -> db -> from('application_logs as i');
		$this -> db -> join('lead_details as l', 'i.lead_id  = l.lead_id', 'left');
		$this -> db -> join('master_customer as c', 'l.primary_customer_id  = c.customer_id', 'left');
		$this -> db -> join('master_employee as e', 'i.created_by  = e.employee_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		//echo $condition;exit;
		//echo "<pre>mmm";print_r($query->result());exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}


	function exportApplicationLogs($post)
	{
		//echo "<pre>";print_r($post);exit;
		$condition = "1=1";
		
		if(isset($_POST['trace_id']) && !empty($_POST['trace_id'])){
			$condition .= " AND l.trace_id like '%".$_POST['trace_id']."%'";
		}
		
		if(isset($_POST['customer_name']) && !empty($_POST['customer_name'])){
			//$condition .= " AND l.trace_id = '".$_POST['sSearch_0']."'";
			$condition .= " AND c.full_name like '%".$_POST['customer_name']."%'";
		}

		if(isset($_POST['customer_mob']) && !empty($_POST['customer_mob'])){
			//$condition .= " AND l.trace_id = '".$_POST['sSearch_0']."'";
			$condition .= " AND c.mobile_no like '%".$_POST['customer_mob']."%'";
		}
		
		if(isset($_POST['date_from']) && !empty($_POST['date_from'])){
			$condition .= "  AND i.created_on >= '".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:01' ";
		}
		
		if(isset($_POST['date_to']) && !empty($_POST['date_to'])){ 
			$condition .= "  AND i.created_on <= '".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59' ";
		}

		if(isset($_POST['action_val']) && !empty($_POST['action_val'])){
			$condition .= "  AND i.action <= '".$_POST['action_val']."' ";
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.*,l.trace_id, c.full_name, c.mobile_no, e.employee_full_name');
		$this -> db -> from('application_logs as i');
		$this -> db -> join('lead_details as l', 'i.lead_id  = l.lead_id', 'left');
		$this -> db -> join('master_customer as c', 'l.primary_customer_id  = c.customer_id', 'left');
		$this -> db -> join('master_employee as e', 'i.created_by  = e.employee_id', 'left');
		
		$this->db->where("($condition)");
		$this->db->order_by('log_id', 'desc');
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}

	function getEnrollmentFormList($post)
	{
		$table = "enrollmentforms";
		$table_id = 'enrollmentforms_id';
		$default_sort_column = 'form_title';
		$default_sort_order = 'asc';
		$condition = "1=1";
		
		$colArray = array('i.form_title');
		$sortArray = array('i.form_title');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		$sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;  
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<1;$i++)
		{
			if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
			{
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('*');
		$this -> db -> from('enrollmentforms as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('*');
		$this -> db -> from('enrollmentforms as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}

	function getEnrollmentFormsData($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('enrollmentforms as i');
		$this -> db -> where('i.enrollmentforms_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}

	function getCompanyList($post)
	{
		$table = "master_company";
		$table_id = 'company_id';
		$default_sort_column = 'i.company_name';
		$default_sort_order = 'asc';
		$condition = "1=1";
		
		$colArray = array('i.company_name');
		$sortArray = array('i.company_name');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		$sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;  
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<5;$i++)
		{
			if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
			{
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('i.company_id,i.company_name,i.isactive');
		$this -> db -> from('master_company as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('i.company_id,i.company_name');
		$this -> db -> from('master_company as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}	

	function getCompanyFormData($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('master_company as i');
		$this -> db -> where('i.company_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}
	

	function getpaymentworkflowmasterList($post)
	{
		$table = "payment_workflow_master";
		$table_id = 'payment_workflow_master_id';
		$default_sort_column = 'workflow_name';
		$default_sort_order = 'asc';
		$condition = "1=1";
		
		$colArray = array('i.workflow_name');
		$sortArray = array('i.workflow_name');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		$sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;  
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<1;$i++)
		{
			if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
			{
				$condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
			}
		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this -> db -> select('*');
		$this -> db -> from('payment_workflow_master as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this -> db -> get();
		
		//print_r($this->db->last_query());
		//exit;
		
		$this -> db -> select('*');
		$this -> db -> from('payment_workflow_master as i');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this -> db -> get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}

	function getPaymentWorkflowFormData($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('payment_workflow_master as i');
		$this -> db -> where('i.payment_workflow_master_id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}

	function getEnumValues($table, $field){
		//echo "Select $fields from $table where $condition";exit;
		$sql = $this->db->query("SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'")->row(0)->Type;
		preg_match("/^enum\(\'(.*)\'\)$/", $sql, $matches);
		$enum = explode("','", $matches[1]);
		if(!empty($enum)){
			return $enum;
		}else{
			return false;
		}
	}
    function getraisebulkdata($post)
    {
        $table_id = 'id';
        $default_sort_column = 'i.id';
        $default_sort_order = 'desc';
        $condition = "c.creditor_id=i.creditor_id ";

        $colArray = array('c.creaditor_name');
        $sortArray = array('i.id');

        $page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
        $rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset

        // sort order by column
        $sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;
        $order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

        for($i=0;$i<5;$i++)
        {
            if($i != 1){
                if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
                {
                    $condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
                }
            }

        }

        //echo "Condition: ".$condition;
        //exit;
        $this->db-> select('i.*,c.creaditor_name');
        $this->db-> from('raise_bulk_upload as i,master_ceditors as c');
        $this->db->where("($condition)");
        $this->db->order_by($sort, $order);
        $this->db->limit($rows,$page);

        $query = $this->db-> get();


        $this->db-> select('i.*,c.creaditor_name');
        $this->db-> from('raise_bulk_upload as i,master_ceditors as c');
        $this->db->where("($condition)");
        $this->db->order_by($sort, $order);

        $query1 = $this->db->get();
        //echo "total: ".$query1 -> num_rows();
        //exit;

//print_r($this->db->last_query());die;
        if($query -> num_rows() >= 1)
        {
            $totcount = $query1 -> num_rows();
            return array("query_result" => $query->result(), "totalRecords" => $totcount);
        }
        else
        {
            return array("totalRecords" => 0);
        }
    }
    function gettrackclaimList($post)
    {
        $table_id = 'id';
        $default_sort_column = 'i.id';
        $default_sort_order = 'desc';
        $condition = "c.creditor_id=i.creditor_id and i.type = 2";

        $colArray = array('c.creaditor_name');
        $sortArray = array('i.id');

        $page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
        $rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset

        // sort order by column
        $sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;
        $order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

        for($i=0;$i<5;$i++)
        {
            if($i != 1){
                if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
                {
                    $condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
                }
            }

        }

        //echo "Condition: ".$condition;
        //exit;
        $this->db-> select('i.id,i.creditor_id,i.URL,c.creaditor_name');
        $this->db-> from('claim_mst_url as i,master_ceditors as c');
        $this->db->where("($condition)");
        $this->db->order_by($sort, $order);
        $this->db->limit($rows,$page);

        $query = $this->db-> get();


        $this->db-> select('i.id,i.creditor_id,i.URL,c.creaditor_name');
        $this->db-> from('claim_mst_url as i,master_ceditors as c');
        $this->db->where("($condition)");
        $this->db->order_by($sort, $order);

        $query1 = $this->db->get();
        //echo "total: ".$query1 -> num_rows();
        //exit;

        if($query -> num_rows() >= 1)
        {
            $totcount = $query1 -> num_rows();
            return array("query_result" => $query->result(), "totalRecords" => $totcount);
        }
        else
        {
            return array("totalRecords" => 0);
        }
    }

    function getlodgeclaimList($post)
    {
        $table = "master_single_journey";
        $table_id = 'id';
        $default_sort_column = 'i.id';
        $default_sort_order = 'desc';
        $condition = "c.creditor_id=i.creditor_id and i.type = 1";

        $colArray = array('c.creaditor_name');
        $sortArray = array('i.id');

        $page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
        $rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset

        // sort order by column
        $sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;
        $order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

        for($i=0;$i<5;$i++)
        {
            if($i != 1){
                if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
                {
                    $condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
                }
            }

        }

        //echo "Condition: ".$condition;
        //exit;
        $this->db-> select('i.id,i.creditor_id,i.URL,c.creaditor_name');
        $this->db-> from('claim_mst_url as i,master_ceditors as c');
        $this->db->where("($condition)");
        $this->db->order_by($sort, $order);
        $this->db->limit($rows,$page);

        $query = $this->db-> get();


        $this->db-> select('i.id,i.creditor_id,i.URL,c.creaditor_name');
        $this->db-> from('claim_mst_url as i,master_ceditors as c');
        $this->db->where("($condition)");
        $this->db->order_by($sort, $order);

        $query1 = $this->db->get();
        //echo "total: ".$query1 -> num_rows();
        //exit;

        if($query -> num_rows() >= 1)
        {
            $totcount = $query1 -> num_rows();
            return array("query_result" => $query->result(), "totalRecords" => $totcount);
        }
        else
        {
            return array("totalRecords" => 0);
        }
    }
	
	function getsinglejourneyList($post)
	{
		$table = "master_single_journey";
		$table_id = 'id';
		$default_sort_column = 'i.id';
		$default_sort_order = 'desc';
		$condition = "c.creditor_id=i.creditor_id and c.isactive=1";
		
		$colArray = array('c.creaditor_name');
		$sortArray = array('i.id');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		$sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;  
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<5;$i++)
		{
		    if($i != 1){
                if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
                {
                    $condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
                }
            }

		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this->db-> select('i.id,i.creditor_id,i.URL,i.is_active,c.creaditor_name');
		$this->db-> from('master_single_journey as i,master_ceditors as c');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this->db-> get();
		
		 
		$this->db-> select('i.id,i.creditor_id,i.URL,i.is_active,c.creaditor_name');
		$this->db-> from('master_single_journey as i,master_ceditors as c');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this->db->get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}

	function getSingleJourneyData($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('master_single_journey as i');
		$this -> db -> where('i.id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}
	function getCommstemplateList($post)
	{
		$table = "master_communication_templates";
		$table_id = 'id';
		$default_sort_column = 'i.id';
		$default_sort_order = 'desc';
		$condition = "c.creditor_id=i.creditor_id and c.isactive=1";
		
		$colArray = array('c.creaditor_name');
		$sortArray = array('i.id');
		
		$page = $post['iDisplayStart'];	// iDisplayStart starting offset of limit funciton
		$rows = $post['iDisplayLength'];	// iDisplayLength no of records from the offset
		
		// sort order by column
		$sort = isset($post['iSortCol_0']) ? strval($sortArray[$post['iSortCol_0']]) : $default_sort_column;  
		$order = isset($post['sSortDir_0']) ? strval($post['sSortDir_0']) : $default_sort_order;

		for($i=0;$i<5;$i++)
		{
		    if($i != 1){
                if(isset($post['sSearch_'.$i]) && $post['sSearch_'.$i]!='')
                {
                    $condition .= " AND $colArray[$i] like '%".$_POST['sSearch_'.$i]."%'";
                }
            }

		}
		
		//echo "Condition: ".$condition;
		//exit;
		$this->db-> select('i.*,c.creaditor_name,e.name');
		$this->db-> from($table.' as i,master_ceditors as c');
		$this->db->join('master_communication_events as e', 'e.id = i.dropout_event');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		$this->db->limit($rows,$page);
		
		$query = $this->db-> get();
		
		/* print_r($this->db->last_query());
		exit;*/
		$this->db-> select('i.id,i.creditor_id,i.isactive,c.creaditor_name');
		$this->db-> from($table.' as i,master_ceditors as c');
		$this->db->where("($condition)");
		$this->db->order_by($sort, $order);
		
		$query1 = $this->db->get();
		//echo "total: ".$query1 -> num_rows();
		//exit;
		
		if($query -> num_rows() >= 1)
		{
			$totcount = $query1 -> num_rows();
			return array("query_result" => $query->result(), "totalRecords" => $totcount);
		}
		else
		{
			return array("totalRecords" => 0);
		}
	}
	function getCommsTemplateData($ID)
	{	
		$this -> db -> select('i.*');
		$this -> db -> from('master_communication_templates as i');
		$this -> db -> where('i.id', $ID);
	
		$query = $this -> db -> get();
	   
		//print_r($this->db->last_query());
		//exit;
	   
		if($query -> num_rows() >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}	
	}

}
?>