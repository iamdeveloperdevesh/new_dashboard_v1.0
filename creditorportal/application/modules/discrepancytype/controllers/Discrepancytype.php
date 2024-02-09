<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// session_start(); //we need to call PHP's session object to access it through CI
class Discrepancytype extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();
		//$this->load->model('permissionmodel','',TRUE); 
		checklogin();
		$this->RolePermission = getRolePermissions();
	}
 
	function index()
	{
		$this->load->view('template/header.php');
		$this->load->view('discrepancytype/index');
		$this->load->view('template/footer.php');
	}
 
	function fetch()
	{
		$_GET['utoken'] = $_SESSION['webpanel']['utoken'];
		//echo "<pre>GET ";print_r($_GET);exit;
		$dataListing = curlFunction(SERVICE_URL.'/api/discrepancyTypeListing',$_GET);
		$dataListing = json_decode($dataListing, true);
		//echo "<pre>";print_r($dataListing);exit;
		if($dataListing['status_code'] == '401'){
			//echo "in condition";
			redirect('login');
			exit();
		}
		
		$result = array();
		$result["sEcho"]= $_GET['sEcho'];

		$result["iTotalRecords"] = $dataListing['Data']['totalRecords'];	//iTotalRecords get no of total recors
		$result["iTotalDisplayRecords"]= $dataListing['Data']['totalRecords']; //iTotalDisplayRecords for display the no of records in data table.

		$items = array();
		
		if(!empty($dataListing['Data']['query_result']) && count($dataListing['Data']['query_result']) > 0)
		{
			for($i=0;$i<sizeof($dataListing['Data']['query_result']);$i++)
			{
				$temp = array();
				array_push($temp, $dataListing['Data']['query_result'][$i]['discrepancy_type'] );
				
				
				$actionCol = "";
				if(in_array('DiscrepancyTypeAdd',$this->RolePermission)){
					$actionCol .='<a href="discrepancytype/addEdit?text='.rtrim(strtr(base64_encode("id=".$dataListing['Data']['query_result'][$i]['discrepancy_type_id'] ), '+/', '-_'), '=').'" title="Edit"><span class="spn-9"><i class="ti-pencil"></i></span></a>';
				}
				if(in_array('DiscrepancyTypeAdd',$this->RolePermission)){
					//if($dataListing['Data']['query_result'][$i]['isactive'] == 1){
						//$actionCol .='&nbsp;&nbsp;<a href="javascript:void(0);" onclick="deleteData(\''.$dataListing['Data']['query_result'][$i]['discrepancy_type_id'] .'\');" title="Delete"><span class="spn-9"><i class="ti-trash" style="color: red;"></i></span></a>';
					//}
				}
			
				array_push($temp, $actionCol);
				array_push($items, $temp);
			}
		}

		$result["aaData"] = $items;
		echo json_encode($result);
		exit;
	}
 
	function addEdit($id=NULL)
	{
		$record_id = "";
		//print_r($_GET);
		if(!empty($_GET['text']) && isset($_GET['text'])){
			$varr=base64_decode(strtr($_GET['text'], '-_', '+/'));	
			parse_str($varr,$url_prams);
			$record_id = $url_prams['id'];
		}
		
		$result = array();
		
		if(!empty($record_id)){
			$data = array();
			$data['utoken'] = $_SESSION['webpanel']['utoken'];
			$data['id'] = $record_id;
			$checkDetails = curlFunction(SERVICE_URL.'/api/getDiscrepancyTypeFormData',$data);
			$checkDetails = json_decode($checkDetails, true);
			//echo "<pre>";print_r($checkDetails);exit;
			$result['getDetails'] = $checkDetails['Data'];
			
		}else{
			$result['getDetails'] = array();
		}
		
		//echo $user_id;
		$this->load->view('template/header.php');
		$this->load->view('discrepancytype/addEdit',$result);
		$this->load->view('template/footer.php');
	}
 
	function submitForm()
	{
		//print_r($_POST);
		//exit;
		
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			//check duplicate record.
			$checkdata = array();
			$checkdata['discrepancy_type'] = $_POST['discrepancy_type'];
			$checkdata['utoken'] = $_SESSION['webpanel']['utoken'];
			if(isset($_POST['discrepancy_type_id']) && $_POST['discrepancy_type_id'] > 0){
				$checkdata['discrepancy_type_id'] = $_POST['discrepancy_type_id'];
			}
			
			$checkDetails = curlFunction(SERVICE_URL.'/api/checkDuplicateDiscrepancyType',$checkdata);
			//echo "<pre>";print_r($checkDetails);exit;
			$checkDetails = json_decode($checkDetails, true);
			
			if($checkDetails['status_code'] == '200')
			{
				echo json_encode(array("success"=>false, 'msg'=>'Record Already Present!'));
				exit;
			}
			
			
			$data = array();
			$data['utoken'] = $_SESSION['webpanel']['utoken'];
			$data['discrepancy_type_id'] = (!empty($_POST['discrepancy_type_id'])) ? $_POST['discrepancy_type_id'] : '';
			$data['discrepancy_type'] = (!empty($_POST['discrepancy_type'])) ? $_POST['discrepancy_type'] : '';
			$data['login_user_id'] = $_SESSION["webpanel"]['employee_id'];
			
			$addEdit = curlFunction(SERVICE_URL.'/api/addEditDiscrepancyType',$data);
			//echo "<pre>";print_r($addEdit);exit;
			$addEdit = json_decode($addEdit, true);
			
			if($addEdit['status_code'] == '200'){
				echo json_encode(array('success'=>true, 'msg'=>$addEdit['Metadata']['Message']));
				exit;
			}else{
				echo json_encode(array('success'=>false, 'msg'=>$addEdit['Metadata']['Message']));
				exit;
			}
		}
		else
		{
			echo json_encode(array("success"=>false, 'msg'=>'Problem While Add/Edit Record..'));
			exit;
		}
	}
}

?>