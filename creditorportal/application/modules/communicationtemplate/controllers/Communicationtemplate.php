<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// session_start(); //we need to call PHP's session object to access it through CI
class Communicationtemplate extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();
		checklogin();
		$this->RolePermission = getRolePermissions();
	}
 
	function index()
	{
		$this->load->view('template/header.php');
		$this->load->view('communicationtemplate/index');
		$this->load->view('template/footer.php');
	}
  
	function fetch()
	{
		$_GET['utoken'] = $_SESSION['webpanel']['utoken'];
		//echo "<pre>GET ";print_r($_GET);exit;
		$dataListing = curlFunction(SERVICE_URL.'/api/commstemplateListing',$_GET);
		$dataListing = json_decode($dataListing, true);
		// echo "<pre>";print_r($dataListing);exit;
		if($dataListing['status_code'] == '401'){
			//echo "in condition";
			redirect('login');
			exit();
		}
		
		$result = array();
		$result["sEcho"]= $_GET['sEcho'];

		$result["iTotalRecords"] = $dataListing['Data']['totalRecords'];	//iTotalRecords get no of total recors
		$result["iTotalDisplayRecords"]= $dataListing['Data']['totalRecords']; //iTotalDisplayRecords for display the no of records in data table.
		$result["iTotalDisplayRecords"]= $dataListing['Data']['totalRecords']; //iTotalDisplayRecords for display the no of records in data table.

		$items = array();
		
		
		if(!empty($dataListing['Data']['query_result']) && count($dataListing['Data']['query_result']) > 0)
		{
			for($i=0;$i<sizeof($dataListing['Data']['query_result']);$i++)
			{
				$temp = array();
				array_push($temp, $dataListing['Data']['query_result'][$i]['creaditor_name'] );
				array_push($temp, $dataListing['Data']['query_result'][$i]['name'] );
				array_push($temp, $dataListing['Data']['query_result'][$i]['subject'] );
				
				array_push($temp, $dataListing['Data']['query_result'][$i]['content'] );
				array_push($temp, $dataListing['Data']['query_result'][$i]['type'] );	
				if ($dataListing['Data']['query_result'][$i]['isactive'] == 1) {
					array_push($temp, 'Active');
				} else {
					array_push($temp, 'In-Active');
				}
				
				$actionCol = "";
				
				$actionCol .='<a href="communicationtemplate/addEdit?text='.rtrim(strtr(base64_encode("id=".$dataListing['Data']['query_result'][$i]['id'] ), '+/', '-_'), '=').'" title="Edit"><span class="spn-9"><i class="ti-pencil"></i></span></a>';

				if ($dataListing['Data']['query_result'][$i]['isactive'] == 1) {
					$actionCol .= '&nbsp;&nbsp;<a href="javascript:void(0);" onclick="deleteData(\'' . $dataListing['Data']['query_result'][$i]['id'] . '\');" title="Delete">
						<span class="spn-9"><i class="ti-trash" style="color: red;"></i></span></a>';
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
			$checkDetails = curlFunction(SERVICE_URL.'/api/getCommsTemplateData',$data);
			$checkDetails = json_decode($checkDetails, true);
			//echo "<pre>";print_r($checkDetails);exit;
			$result['getCreditorDetails'] = json_decode(curlFunction(SERVICE_URL.'/api/getCreditorsDetails',[]),TRUE);
			$result['getDetails'] = $checkDetails['Data'];
			
		}else{
			$result['getCreditorDetails'] = json_decode(curlFunction(SERVICE_URL.'/api/getCreditorsDetails',[]),TRUE);
			$result['getDetails'] = array();
		}
		$result['events']= json_decode(curlFunction(SERVICE_URL.'/api/getDropoutEvents',[]),TRUE);;
		// print_r($result);exit;
		//echo $user_id;
		$this->load->view('template/header.php');
		$this->load->view('communicationtemplate/addEdit',$result);
		$this->load->view('template/footer.php');
	}
 
	function submitForm()
	{
		/*print_r($_POST);
		exit;*/
		
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			
			$data = array();
			$data['creditor_id'] = $_POST['creditor_id'];
			$data['utoken'] = $_SESSION['webpanel']['utoken'];
			$data['isactive'] = $_POST['isactive'];
			$data['subject'] = $_POST['subject'];
			$data['type'] = $_POST['type'];
			$data['content'] = $_POST['content'];
			$data['dropout_event'] = $_POST['dropout_event'];
			
			if(isset($_POST['template_id']) && $_POST['template_id'] > 0){
				$data['id'] = $_POST['template_id'];
			}
			
			
			$data['login_user_id'] = $_SESSION["webpanel"]['employee_id'];
			
			$addEdit = curlFunction(SERVICE_URL.'/api/addEditCommsTemplateJourney',$data);
			// echo "<pre>";print_r($addEdit);exit;
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
	function delRecord($id)
	{
		//$appdResult = $this->adcategorymodel->delrecord("tbl_categories","category_id ",$id);
		$data = array();
		$data['id'] = $id;
		$data['utoken'] = $_SESSION['webpanel']['utoken'];
		$delRecord = curlFunction(SERVICE_URL . '/api/delTemplate', $data);
		$delRecord = json_decode($delRecord, true);

		if ($delRecord['status_code'] == '200') {
			echo json_encode(array('success'=>true, 'msg'=>$delRecord['Metadata']['Message']));
			exit;
		}else{
			echo json_encode(array('success'=>false, 'msg'=>$delRecord['Metadata']['Message']));
			exit;
		}
	}
 
}

?>