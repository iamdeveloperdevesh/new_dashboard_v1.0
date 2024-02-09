<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// session_start(); //we need to call PHP's session object to access it through CI
class Newdashboard extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();
        checklogin();
        $this->RolePermission = getRolePermissions();
        // print_r($this->db->last_query());
	}
 
	function index()
	{
		$this->load->view('template/new_dashboard_header.php');
		$this->load->view('newdashboard/index');
		$this->load->view('template/new_dashboard_footer.php');
	}

	function getClientData(){
        // $creditor_id=$_SESSION['webpanel']['creditor_id'];
        $role_id=$_SESSION['webpanel']['role_id'];
        $where='';
        $_GET['utoken'] = $_SESSION['webpanel']['utoken'];
        $_GET['role_id'] = $_SESSION['webpanel']['role_id'];
        $_GET['user_id'] = $_SESSION['webpanel']['employee_id'];
        $getCreditors = curlFunction(SERVICE_URL.'/api/getRoleWiseCreditorsData',$_GET);
        $getCreditors = json_decode($getCreditors, true);
        $creditor_id=$getCreditors['Data'][0]['creditor_id'];
        //  print_r($getCreditors);die;

        foreach($getCreditors['Data'] as $cr){
            $arr[]=($cr['creditor_id']);
        }
        if(!empty($arr)){
            $creditor_id=implode(',',$arr);

        }
        if($role_id == 3 || $role_id == 12){
            $where =' AND creditor_id in ('.$creditor_id.')';
        }
        $quey=$this->db->query("select creditor_id,creaditor_name from master_ceditors where isactive=1".$where)->result();
        $response['data']=$quey;
        $response['code']=200;
        echo json_encode($response);
        exit;
    }

    function getPlanName(){
        $partner_id=$this->input->post('partner_id');
        $quey=$this->db->query("select plan_id,plan_name from master_plan where creditor_id=".$partner_id." and isactive=1")->result();
        $response['data']=$quey;
        $response['code']=200;
        echo json_encode($response);
        exit;
    }
    
    function getCoverType(){
        $quey=$this->db->query("select policy_sub_type_id,code from master_policy_sub_type where isactive=1 and code is not null")->result();
        $response['data']=$quey;
        $response['code']=200;
        echo json_encode($response);
        exit;
    }

    function getInsurerName(){
        $quey=$this->db->query("select insurer_id,insurer_name from master_insurer where isactive=1")->result();
        $response['data']=$quey;
        $response['code']=200;
        echo json_encode($response);
        exit;
    }

    function getPartenrWiseData(){
        $partner_id=$this->input->post('partner_id');
        $plan_name=$this->input->post('plan_name');
        $cover_type=$this->input->post('cover_type');
        $stateNameNew=$this->input->post('stateNameNew');
        $insurer_name=$this->input->post('insurer_name');
        $daterange=$this->input->post('daterange');
        $type_id=$this->input->post('type_id');
        $exp=explode("-",$daterange);
        $from=$exp[0];
        $to=$exp[1];
        $where='';
        if(isset($partner_id) && !empty($partner_id)){
            $where=" mpp.creditor_id=".$partner_id;
        }else{
            $where= '1=1';
        }
        if(isset($plan_name) && !empty($plan_name)){
            $where .=" and mp.plan_id=".$plan_name;
        }
        if(isset($cover_type) && !empty($cover_type)){
            $cover=implode(",",$cover_type);
            $where .=" and apr.policy_sub_type_id in (".$cover.")";
        }
        if(isset($daterange) && !empty($daterange)){
            $from=date('Y-m-d',strtotime($from));
            $to=date('Y-m-d',strtotime($to));
            $where .=" and (date(apr.created_date) >= date('".$from."') and date(apr.created_date) <= date('".$to."'))";
        }else{
           // $where .=" and year(apr.created_date)=year(curdate())";
        }
        $creditor_id=$_SESSION['webpanel']['creditor_id'];
        $role_id=$_SESSION['webpanel']['role_id'];
        $_GET['utoken'] = $_SESSION['webpanel']['utoken'];
        $_GET['role_id'] = $_SESSION['webpanel']['role_id'];
        $_GET['user_id'] = $_SESSION['webpanel']['employee_id'];
        $getCreditors = curlFunction(SERVICE_URL.'/api/getRoleWiseCreditorsData',$_GET);
        $getCreditors = json_decode($getCreditors, true);
        $creditor_id=$getCreditors['Data'][0]['creditor_id'];
        //  print_r($getCreditors);die;
        foreach($getCreditors['Data'] as $cr){
            $arr[]=($cr['creditor_id']);
        }
        // print_r($arr);die;
        if(!empty($arr)){
            $creditor_id=implode(',',$arr);

        }
        if($role_id == 3 || $role_id == 12){
            $where .=' AND mp.creditor_id in ('.$creditor_id.')';
        }
        $inner='';
        /*if(!empty($type_id)){
            $inner =" inner join master_plan mpp on mpp.plan_id=mp.plan_id and mpp.policy_type_id=".$type_id;
        }else{
            $inner =" inner join master_plan mpp on mpp.plan_id=mp.plan_id and mpp.policy_type_id=1";
        }*/


        if(isset($stateNameNew) && !empty($stateNameNew)){
            $inner .=" inner join master_customer mcc on mcc.lead_id=apr.lead_id and state='".$stateNameNew."'";
        }
        if(isset($insurer_name) && !empty($insurer_name)){
            $inner .=" inner join master_policy mp1 on mp1.policy_id=apr.master_policy_id and mp1.insurer_id=".$insurer_name;
        }else{

        }
        $query=$this->db->query("select round(sum(apr.gross_premium),2) as gross_premium,count(apr.certificate_number) as certificate_number,
        (select policy_sub_type_name from master_policy_sub_type mpst where mpst.policy_sub_type_id=apr.policy_sub_type_id) as policy_sub_type_name,
        (select policy_type_id from master_policy_sub_type mpst where mpst.policy_sub_type_id=apr.policy_sub_type_id) as policy_type_id,
        (select logo from master_policy_sub_type mpst where mpst.policy_sub_type_id=apr.policy_sub_type_id) as logo,
        apr.policy_sub_type_id from api_proposal_response apr 
        inner join master_policy mp on mp.policy_id=apr.master_policy_id
        inner join master_plan mpp on mpp.plan_id=mp.plan_id and mpp.isactive=1
        inner join master_ceditors mc on mc.creditor_id= mp.creditor_id
        ".$inner."
        where ".$where." and mc.isactive=1
        
        group by apr.policy_sub_type_id")->result();
        //   echo $this->db->last_query();
        $policy_sub_type_idData=array();
        $dataNew=array();
        foreach ($query as $row){
            $policy_sub_type_idData[]=$row->policy_sub_type_id;
            $data=array(
                "gross_premium"=>$row->gross_premium,
                "certificate_number"=>$row->certificate_number,
                "policy_sub_type_name"=>$row->policy_sub_type_name,
                "logo"=>$row->logo,
                "policy_sub_type_id"=>$row->policy_sub_type_id,
                "policy_type_id"=>$row->policy_type_id,
            );
            array_push($dataNew,$data);
        }
        //var_dump($dataNew);die;
        if(is_array($cover_type))
            $result = array_diff($cover_type, $policy_sub_type_idData);
        if(!empty($result) && count($result) > 0){
            $covers=implode(",",$result);
            $queryRemain=$this->db->query("select policy_sub_type_name,logo,policy_sub_type_id,policy_type_id from master_policy_sub_type where policy_sub_type_id  in (".$covers.")")->result();
            foreach ($queryRemain as $item){
                $dataR=array(
                    "gross_premium"=>0,
                    "certificate_number"=>0,
                    "policy_sub_type_name"=>$item->policy_sub_type_name,
                    "logo"=>$item->logo,
                    "policy_sub_type_id"=>$item->policy_sub_type_id,
                    "policy_type_id"=>$item->policy_type_id,
                );
                array_push($dataNew,$dataR);
            }
        }

        $response['policy_sub_type_idData']=$policy_sub_type_idData;
        $response['result']=$result;
        $response['data']=$dataNew;
        $response['code']=200;
        $response['cover_type']=$cover_type;
        echo json_encode($response);
        exit;
    }

    function getPerformanceData(){
        
        //Query for getting total policies issued and total premium collected
        $query1=$this->db->query("select count(apr.certificate_number) AS total_policies, round(sum(apr.gross_premium),2) 
        AS total_premium from api_proposal_response apr
        inner join lead_details ld on ld.lead_id=apr.lead_id
        left join master_customer ms on ms.lead_id=apr.lead_id and ms.lead_id=ld.lead_id
        where ms.isactive=1")->row();
        
        //Query for getting average daily policies
        $query2=$this->db->query("select round(avg(daily_policies)) as average_daily_policies
        from (
            select date(apr.created_date) as policy_date, count(apr.certificate_number) as daily_policies
            from api_proposal_response apr
            inner join lead_details ld on ld.lead_id=apr.lead_id
            left join master_customer ms on ms.lead_id=apr.lead_id and ms.lead_id=ld.lead_id
            where ms.isactive=1
            group by policy_date
            ) as daily_counts")->row();
            
        //Query for getting avg. daily issues premium collected
        $query3=$this->db->query("select round(avg(average_daily_premium),2) as average_daily_premium
        from (
            select date(apr.created_date) as Date, avg(apr.gross_premium) as average_daily_premium
            from api_proposal_response apr
            inner join lead_details ld on ld.lead_id=apr.lead_id
            left join master_customer ms on ms.lead_id=apr.lead_id and ms.lead_id=ld.lead_id
            where ms.isactive=1
            group by 
                Date
            order by 
                Date desc
            ) as innerQuery")->row();


        $response['data'] = array(
            'total_policies' => $query1->total_policies,
            'total_premium' => $query1->total_premium,
            'average_daily_policies' => $query2->average_daily_policies,
            'average_daily_premium' => $query3->average_daily_premium
        );
        $response['code'] = 200;
    
        echo json_encode($response);
        exit;
    }

    function getPendingStatus() {
        //Query for getting pending proposal
        $query1=$this->db->query("select count(*) as pending_proposals
        from lead_details as ld
        left join proposal_policy as pp on ld.lead_id = pp.lead_id
        where pp.lead_id is null")->row();
        
        //Query for getting payment pending
        $query2=$this->db->query("select count(payment_status) as payment_pending from proposal_payment_details 
        where payment_status='pending'")->row();
            
        //Query for getting insurance pending
        $query3=$this->db->query("select count(certificate_number) as insurance_pending from api_proposal_response
        where certificate_number != '' and certificate_number is not null")->row();

        
        $query4=$this->db->query("select count(COI_url) as policy_pdf from api_proposal_response
        where COI_url != '' and COI_url is not null")->row();


        $response['data'] = array(
            'pending_proposal' => $query1->pending_proposals,
            'payment_pending' => $query2->payment_pending,
            'insurance_pending' => $query3->insurance_pending,
            'policy_pdf' => $query4->policy_pdf
        );
        $response['code'] = 200;
    
        echo json_encode($response);
        exit;
    }

    function getClientPerformance(){

        $daterange=$this->input->post('date');
        $policy_type=!empty($this->input->post('policy_type'))?$this->input->post('policy_type'):1;

        $exp=explode("-",$daterange);
        $from=$exp[0];
        $to=$exp[1];
        $where='';
        if(isset($daterange) && !empty($daterange)){
            $from=date('Y-m-d',strtotime($from));
            $to=date('Y-m-d',strtotime($to));
            $where .=" and (date(apr.created_date) >= date('".$from."') and date(apr.created_date) <= date('".$to."'))";
        }else{
           // $where .=" and year(apr.created_date)=year(curdate())";
        }
        $creditor_id=$_SESSION['webpanel']['creditor_id'];
        $role_id=$_SESSION['webpanel']['role_id'];
        $_GET['utoken'] = $_SESSION['webpanel']['utoken'];
        $_GET['role_id'] = $_SESSION['webpanel']['role_id'];
        $_GET['user_id'] = $_SESSION['webpanel']['employee_id'];
        $getCreditors = curlFunction(SERVICE_URL.'/api/getRoleWiseCreditorsData',$_GET);
        $getCreditors = json_decode($getCreditors, true);
        $creditor_id=$getCreditors['Data'][0]['creditor_id'];
        //  print_r($getCreditors);die;
        foreach($getCreditors['Data'] as $cr){
            $arr[]=($cr['creditor_id']);
        }
        // print_r($arr);die;
        if(!empty($arr)){
            $creditor_id=implode(',',$arr);

        }
        if($role_id == 3 || $role_id == 12){
            $where .=' AND mp.creditor_id in ('.$creditor_id.')';
        }
        $queryData=$this->db->query("select mc.creditor_id,mc.creaditor_name,count(apr.certificate_number) as certificate,
        sum(gross_premium) as premium from master_ceditors mc
        inner join master_policy mp on mp.creditor_id= mc.creditor_id
        inner join master_plan mpp on mpp.plan_id= mp.plan_id and mpp.policy_type_id=".$policy_type." and mpp.isactive=1
        inner join api_proposal_response apr on apr.master_policy_id= mp.policy_id ".$where ." and mc.isactive=1
        group by creditor_id order by certificate desc")->result();
        //  echo $this->db->last_query();die;

        $response['data']=$queryData;
        $response['code']=200;
        echo json_encode($response);
    }
}

?>