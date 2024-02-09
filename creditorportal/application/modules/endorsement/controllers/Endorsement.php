<?php header('Access-Control-Allow-Origin: *');
if (!defined('BASEPATH')) exit('No direct script access allowed');
// session_start(); //we need to call PHP's session object to access it through CI
class Endorsement extends CI_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!(isset($_POST['source']) && $_POST['source'] != 'customer')) {

            checklogin();
            $this->RolePermission = getRolePermissions();
        }
    }

    public function index()
    {
        $result = array();
        //Get all creditors
        $data = array();
        $data['utoken'] = $_SESSION['webpanel']['utoken'];
        $data['role_id'] = $_SESSION['webpanel']['role_id'];
        $data['user_id'] = $_SESSION['webpanel']['employee_id'];
        $data['cid'] = $_GET['cid'];
        //echo $_GET['cid'];exit;
        if ($_SESSION['webpanel']['role_id'] == 3) {
            $getCreditors = curlFunction(SERVICE_URL . '/api/getRoleWiseCreditorsData', $data);
        } else {
            $getCreditors = curlFunction(SERVICE_URL . '/api/getCreditorsData', $data);
        }
        $getCreditors = json_decode($getCreditors, true);
        //echo "<pre>";print_r($getCreditors);exit;


        $result['creditors'] = $getCreditors['Data'];
        $this->load->view('template/header.php');
        $this->load->view('endorsement/addEndorsement', $result);
        $this->load->view('template/footer.php');
    }
    function fetchendorsement()
    {
        $_GET['utoken'] = $_SESSION['webpanel']['utoken'];
        //echo "get: ".$_GET['cid'];exit;
        $_GET['cid'] = $_POST['cid'];
        $_GET['plan_id'] = $_POST['plan_id'];
        if (empty($_GET['sSearch_0'])) {
            $_GET['sSearch_0'] = 'All';
        }
        if (empty($_GET['sSearch_0'])) {
            $_GET['sSearch_0'] = 'Approved';
        } else if (!empty($_GET['sSearch_0']) && $_GET['sSearch_0'] == 'All') {
            $_GET['sSearch_0'] = '';
        } else {
            $_GET['sSearch_0'] = $_GET['sSearch_0'];
        }

        $dataListing = curlFunction(SERVICE_URL . '/api/fetchendoresement', $_GET);
        $dataListing = json_decode($dataListing, true);
//print_r($dataListing);die;
        if ($dataListing['status_code'] == '401') {
            redirect('login');
            exit();
        }
       // print_r($dataListing);

        $result = array();
        $result["sEcho"] = $_GET['sEcho'];

        $result["iTotalRecords"] = $dataListing['Data']['totalRecords'];
        $result["iTotalDisplayRecords"] = $dataListing['Data']['totalRecords'];

        $items = array();

        if (!empty($dataListing['Data']['query_result']) && count($dataListing['Data']['query_result']) > 0) {
            for ($i = 0; $i < sizeof($dataListing['Data']['query_result']); $i++) {
                $filepath = FRONT_URL.'/'.$dataListing['Data']['query_result'][$i]['original_file'];
                $file_icon = FRONT_URL."/assets/excel-icon.png";
                $temp = array();
                array_push($temp, $dataListing['Data']['query_result'][$i]['creaditor_name']);
                array_push($temp, $dataListing['Data']['query_result'][$i]['plan_name']);
                array_push($temp, $dataListing['Data']['query_result'][$i]['trace_id']);
                array_push($temp, $dataListing['Data']['query_result'][$i]['first_name'] ." ". $dataListing['Data']['query_result'][$i]['last_name']);
                array_push($temp, $dataListing['Data']['query_result'][$i]['endorsement_type']);
                array_push($temp, 'Success' );
                array_push($temp, '<a href="#" onclick="myfileDownload(this);" data-target = "'.$filepath.'"><img src='.$file_icon.' width="18"></a>');
                array_push($temp, '<a href="#" onclick="myerrorDocument(this);"><img src='.$file_icon.' width="18"></a>');

                array_push($temp, $dataListing['Data']['query_result'][$i]['created_on']);
                array_push($temp, $dataListing['Data']['query_result'][$i]['status']);




                array_push($items, $temp);
            }
        }

        $result["aaData"] = $items;
      //  print_r($items);
        echo json_encode($result);
        exit;
    }
    function valid_email($str) {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
    }
    function valid_mobile($mobile)
    {
        return (!preg_match("/^[6-9][0-9]{9}$/", $mobile)) ? FALSE : TRUE;

    }
    function valid_alpha($str,$name)
    {
        // print_r($str);
        $data['status'] = 'true';

        if(preg_match ('/^[a-zA-Z ]+$/', $str)){
            $data['status'] = 'true';


        }
        else{
            $data['status'] = 'false';
            $data['msg'] = 'Invalid '. $name;
        }
        //die;
        return $data;
    }

    function valid_numeric($str,$name)
    {
        $data['status'] = 'true';
        if(!preg_match ('/^[1-9][0-9]*$/', $str)){
            //  echo 56;
            $data['status'] = 'false';
            $data['msg'] = 'Invalid '. $name . ' .Required Numeric Value';

        }
        return $data;

    }
public function exceltophpdate($exceldate)
{
    $excelDate = $exceldate; //2018-11-03
    $miliseconds = ($excelDate - (25567 + 2)) * 86400 * 1000;
    $seconds = $miliseconds / 1000;
    return date("Y-m-d", $seconds); //2018-11-03
}
    public function correctionbulk()
    { //echo 123;die;
        // print_r($_POST);

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $this->load->library('excel');
        if (isset($_FILES["uploadfileCorrection"]["name"])) {
            $path = $_FILES["uploadfileCorrection"]["tmp_name"];

            $arr = [];
            $arr_age = [];
            $lead_arr = [];
            $member_details = [];
            $nominee_det = [];
            $sumInsured = [];
            $member_all_det = [];
            $endorsement = [];
            $get_arr = [];
            $object = PHPExcel_IOFactory::load($path);
            $worksheet = $object->getSheet(0);
            //print_r($worksheet);
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $rowDatafirst = $worksheet->rangeToArray('A1:' . $highestColumn . '1',null, true, false);
                    $data_array = array('Certificate Number', 'Salutation', 'First Name', 'Middle Name', 'Last Name', 'Gender', 'DOB', 'Email Id', 'Mobile Number', 'Alternate Mobile No', 'Address1', 'Address2', 'Address3', 'Pincode', 'MemberNo', 'Member Salutation', 'Member First Name','Member Last Name', 'Member Gender', 'Member DOB', 'Relation with proposer', 'LoanDisbursementDate', 'LoanAmount', 'LoanAccountNo', 'LoanTenure', 'Tenure',  'Payment Mode','Nominee Salutation', 'Nominee First Name', 'Nominee Last Name', 'Nominee Contact Number',  'Nominee Gender', 'Nominee Relation','Sum Insured GHI','Sum Insured GPA','Sum Insured GCS','Sum Insured Supertopup','Sum Insured Hospicash','Sum Insured GCI');
            $difference_array = array_merge(array_diff($data_array, $rowDatafirst[0]), array_diff($rowDatafirst[0], $data_array));
            //print_r(array_filter($difference_array));die;
            if (count(array_filter($difference_array)) == 0) {
                $dir ='endorsement';
                $filename = "./assets/" . $dir . "/";
                if (file_exists($filename)) {

                } else {

                    mkdir('./assets/' . $dir, 0777);

                }
                $target_file_name= $_FILES["uploadfileCorrection"]["name"];
                $target = FCPATH."assets/endorsement/".$target_file_name;
                if (file_exists($target)) {
                    unlink($target);
                }
                move_uploaded_file($_FILES["uploadfileCorrection"]["tmp_name"] , $target);
                $filepath="assets/endorsement/".$target_file_name;
                $creditor_id = $_POST['creditor_id'];
                $plan_id = $_POST['plan_id'];
                $k = 0;
                $i = 32;
                $j = 37;
                $adult_count = 0;
                $child_count = 0;
               // $creditor_id = '';
               // $plan_id = '';
                $lead_id = '';
                for ($row = 1; $row <= $highestRow; $row++) {

                    $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);
                    $rowData = $rowData[0];
                 //   print_r($rowData);


                    $rowData = array_filter($rowData);
                    $suminsued_arr = [];
                    if ($row != 1) {
                        if (!empty($rowData[0])) {
                            $trace_id = $rowData[0];
                            $check = true;
                            $lead_details = $this->db->query("select * from lead_details where trace_id ='$trace_id'")->row_array();
                            //print_r($this->db->last_query());
                            if(!empty($lead_details)) {
                                $endorsement[$k - 1]['status'] = 'Success';
                                $endorsement[$k - 1]['endorsement_type'] = 'Correction';
$endorsement[$k-1]['original_file'] = $filepath;
                                  $endorsement[$k-1]['created_on'] = date('d-m-Y H:i:s');

                                $lead_id = $lead_details['lead_id'];
                                $lead_id_que = $this->db->query("select mc.*,l.creditor_id,l.plan_id from master_customer as  mc inner join lead_details as l  where mc.lead_id = l.lead_id and l.lead_id='" . $lead_id . "'")->row_array();
                                $creditor = $lead_details['creditor_id'];
                                $plan = $lead_details['plan_id'];
                                $creditor_det = $this->db->query("select mc.creditor_id from master_ceditors as mc  where  mc.creditor_id = '$creditor' ")->row_array();
                                if ($creditor_det['creditor_id'] != $creditor_id) {
                                    $endorsement[$k - 1]['Reason'][] = 'Trace id not mapped with this partner';
                                    $check = false;
                                }
                            }else{
                                $endorsement[$k - 1]['Reason'][] = 'Lead Not Exist';
                                $check = false;
                            }
                            $endorsement[$k - 1]['lead_id'] = (!empty($lead_id) )? $lead_id : '';
                            $endorsement[$k - 1]['creditor_id'] = $creditor_id;
                            $endorsement[$k - 1]['plan_id'] = $plan_id;
                            $endorsement[$k - 1]['trace_id'] = $rowData[0];
                            $endorsement[$k - 1]['sum_insured_ghi'] = (!empty($rowData[32])) ? $rowData[32] : '';
                            $endorsement[$k - 1]['sum_insured_gpa'] = (!empty($rowData[33])) ? $rowData[33] : '';
                            $endorsement[$k - 1]['sum_insured_gcs'] = (!empty($rowData[34])) ? $rowData[34] : '';
                            $endorsement[$k - 1]['sum_insured_supertopup'] = (!empty($rowData[35])) ? $rowData[35] : '';
                            $endorsement[$k - 1]['sum_insured_hospicash'] = (!empty($rowData[36])) ? $rowData[36] : '';
                            $endorsement[$k - 1]['sum_insured_gci'] = (!empty($rowData[37])) ? $rowData[37] : '';
                            $endorsement[$k - 1]['mem_salutation'] = (!empty($rowData[15])) ? $rowData[15] : '';
                            $endorsement[$k - 1]['mem_first_name'] = (!empty($rowData[16])) ? $rowData[16] : '';
                            $endorsement[$k - 1]['mem_last_name'] = (!empty($rowData[17])) ? $rowData[17] : '';
                            $endorsement[$k - 1]['mem_gender'] = (!empty($rowData[18])) ? $rowData[18] : '';
                            $endorsement[$k - 1]['mem_dob'] = (!empty($rowData[19])) ? $this->exceltophpdate($rowData[19]) : '';
                            $endorsement[$k - 1]['mem_relation'] = (!empty($rowData[20])) ? $rowData[20] : '';
                            $endorsement[$k - 1]['salutation'] = (!empty($rowData[1])) ? $rowData[1] : '';
                            $endorsement[$k - 1]['first_name'] = (!empty($rowData[2])) ? $rowData[2] : '';
                            $endorsement[$k - 1]['middle_name'] = (!empty($rowData[3])) ? $rowData[3] : '';
                            $endorsement[$k - 1]['last_name'] = (!empty($rowData[4])) ? $rowData[4] : '';
                            $endorsement[$k - 1]['gender'] = (!empty($rowData[5])) ? $rowData[5] : '';
                            $endorsement[$k - 1]['dob'] = (!empty($rowData[6])) ? $rowData[6] : '';
                            $endorsement[$k - 1]['email_id'] = (!empty($rowData[7])) ? $rowData[7] : '';
                            $endorsement[$k - 1]['mobile_number'] = (!empty($rowData[8])) ? $rowData[8] : '';
                            $endorsement[$k - 1]['alternate_mobile_no'] = (!empty($rowData[9])) ? $rowData[9] : '';
                            $endorsement[$k - 1]['address'] = (!empty($rowData[10])) ? $rowData[10] : '';
                            $endorsement[$k - 1]['address1'] = (!empty($rowData[11])) ? $rowData[11] : '';
                            $endorsement[$k - 1]['address2'] = (!empty($rowData[12])) ? $rowData[12] : '';
                            $endorsement[$k - 1]['pincode'] = (!empty($rowData[13])) ? $rowData[13] : '';
                            $endorsement[$k - 1]['loan_disbursement_date'] = (!empty($rowData[21])) ? $rowData[21] : '';
                            $endorsement[$k - 1]['LoanAmount'] = (!empty($rowData[22])) ? $rowData[22] : '';
                            $endorsement[$k - 1]['LoanAccountNo'] = (!empty($rowData[23])) ? $rowData[23] : '';
                            $endorsement[$k - 1]['loan_tenure'] = (!empty($rowData[24])) ? $rowData[24] : '';
                            $endorsement[$k - 1]['tenure'] = (!empty($rowData[25])) ? $rowData[25] : '';
                            $endorsement[$k - 1]['payment_mode'] = (!empty($rowData[38])) ? $rowData[38] : '';
                            $endorsement[$k - 1]['nominee_relation'] = (!empty($rowData[26])) ? $rowData[26] : '';
                            $endorsement[$k - 1]['nominee_salutation'] = (!empty($rowData[27])) ? $rowData[27] : '';
                            $endorsement[$k - 1]['nominee_first_name'] = (!empty($rowData[28])) ? $rowData[28] : '';
                            $endorsement[$k - 1]['nominee_last_name'] = (!empty($rowData[29])) ? $rowData[29] : '';
                            $endorsement[$k - 1]['nominee_contact_no'] = (!empty($rowData[30])) ? $rowData[30] : '';
                            $endorsement[$k - 1]['mem_no'] = (!empty($rowData[14])) ? $rowData[14] : '';


                            if(!empty($lead_id)){
                            if ($rowData[20] == 'Self') {

                                if (!empty($rowData[1])) {
                                    $arr['salutation'] = $rowData[1];
                                    //****valid
                                    $check_name = $this->valid_alpha($arr['salutation'], 'Salutation');
                                    if ($check_name['status'] == 'false') {
                                        $endorsement[$k - 1]['Reason'] = $check_name['msg'];
                                        $check = false;
                                    }
                                    if(!empty($rowData[5]))
                                    {
                                        if(strtolower($rowData[1]) == 'mr' && strtolower($rowData[5]) == 'female' || strtolower($rowData[1]) == 'master' && strtolower($rowData[5]) == 'female' )
                                        {
                                            $endorsement[$k - 1]['Reason'][] = "Salutaion and Gender does not matched";
                                            $check = false;
                                        }
                                        if(strtolower($rowData[1]) == 'mrs' && strtolower($rowData[5]) == 'male' || strtolower($rowData[1]) == 'ms' && strtolower($rowData[5] == 'male'))
                                        {
                                            $endorsement[$k - 1]['Reason'][] = "Salutaion and Gender does not matched";
                                            $check = false;
                                        }
                                    }
                                }
                                if (!empty($rowData[2])) {
                                    $arr['first_name'] = $rowData[2];
                                    $check_name = $this->valid_alpha($arr['first_name'], 'First Name');
                                    if ($check_name['status'] == 'false') {
                                        $endorsement[$k - 1]['Reason'][] = $check_name['msg'];
                                        $check = false;
                                    }
                                }
                                if (!empty($rowData[3])) {
                                    $arr['middle_name'] = $rowData[3];
                                    $check_name = $this->valid_alpha($arr['middle_name'], 'Middle Name');
                                    if ($check_name['status'] == 'false') {
                                        $endorsement[$k - 1]['Reason'][] = $check_name['msg'];
                                        $check = false;
                                    }
                                }
                                if (!empty($rowData[4])) {
                                    $arr['last_name'] = $rowData[4];
                                    $check_name = $this->valid_alpha($arr['last_name'], 'Last Name');
                                    if ($check_name['status'] == 'false') {
                                        $endorsement[$k - 1]['Reason'][] = $check_name['msg'];
                                        $check = false;
                                    }
                                }
                                if (!empty($rowData[2])  ) {
                                    $arr['full_name'] = $arr['first_name'] . " " . $arr['middle_name'] . " " . $arr['last_name'];
                                }
                                if (!empty($rowData[5])) {
                                    $arr['gender'] = $rowData[5];
                                    $check_name = $this->valid_alpha($arr['gender'], 'Gender');
                                    if ($check_name['status'] == 'false') {
                                        $endorsement[$k - 1]['Reason'] = $check_name['msg'];
                                        $check = false;
                                    }

                                }
                                if (!empty($rowData[6])) {
                                    $arr['dob'] = $this->exceltophpdate($rowData[6]);
                                    // $arr['dob'] = $rowData[6];
                                    //$check_name =  $this->is_valid_date($arr['dob'],'Date Of Birth');
                                    /*if($check_name['status'] == 'false'){
                                        $array_failed['Reason'] = $check_name['msg'];
                                        $check = false;
                                    }*/
                                }
                                if (!empty($rowData[7])) {
                                    $arr['email_id'] = $rowData[7];
                                    $lead_arr['email_id'] = $arr['email_id'];
                                    $email_fn = $this->valid_email($lead_arr['email_id']);
                                    if (!($email_fn)) {
                                        $endorsement[$k - 1]['Reason'] = "Invalid Email Address";
                                        $check = false;
                                        // echo "Invalid email address.";
                                    }
                                }
                                if (!empty($rowData[8])) {
                                    $arr['mobile_no'] = $rowData[8];
                                    $lead_arr['mobile_no'] = $arr['mobile_no'];
                                    $mob_fn = $this->valid_mobile($lead_arr['mobile_no']);
                                    if (!($mob_fn)) {
                                        $endorsement[$k - 1]['Reason'] = "Invalid Mobile Number";
                                        $check = false;
                                    }

                                }
                                if (!empty($rowData[9])) {
                                    $arr['mobile_no2'] = $rowData[9];
                                    $mob_fn = $this->valid_mobile($arr['mobile_no']);
                                    if (!($mob_fn)) {
                                        $endorsement[$k - 1]['Reason'] = "Invalid Mobile Number";
                                        $check = false;
                                    }
                                }
                                if (!empty($rowData[10])) {
                                    $arr['address_line1'] = $rowData[10];
                                    $lead_arr['address_line1'] = $arr['address_line1'];
                                }
                                if (!empty($rowData[11])) {
                                    $endorsement[$k - 1]['address1'] = $rowData[11];
                                    $arr['address_line2'] = $rowData[11];
                                    $lead_arr['address_line2'] = $arr['address_line2'];

                                }
                                if (!empty($rowData[12])) {
                                    $arr['address_line3'] = $rowData[12];
                                    $lead_arr['address_line3'] = $arr['address_line3'];

                                }
                                if (!empty($rowData[13])) {
                                    $arr['pincode'] = $rowData[13];
                                    $lead_arr['pincode'] = $arr['pincode'];
                                    //print_r($lead_arr);
                                    $check_name = $this->valid_numeric($rowData[13], 'Pincode');
                                    if ($check_name['status'] == 'false') {
                                        $endorsement[$k - 1]['Reason'] = $check_name['msg'];
                                    }
                                }

                                if (!empty($rowData[21])) {
                                    $arr['loan_disbursement_date'] = $rowData[21];
                                    $lead_arr['loan_disbursement_date'] = $arr['loan_disbursement_date'];
                                    //$check_name =  $this->is_valid_date($arr['loan_disbursement_date'],'Loan Disbursement Date');
                                    /*if($check_name['status'] == 'false'){
                                        $array_failed['Reason'] = $check_name['msg'];
                                        $check = false;

                                    }*/
                                }
                                if (!empty($rowData[22])) {
                                    $arr['loan_amt'] = $rowData[22];
                                    $lead_arr['loan_amt'] = $arr['loan_amt'];
                                }
                                if (!empty($rowData[23])) {
                                    $endorsement[$k - 1]['LoanAccountNo'] = $rowData[23];
                                    $arr['lan_id'] = $rowData[23];
                                    $lead_arr['lan_id'] = $arr['lan_id'];
                                }
                                if (!empty($rowData[24])) {
                                    $endorsement[$k - 1]['loan_tenure'] = $rowData[24];
                                    $arr['loan_tenure'] = $rowData[24];
                                    $lead_arr['loan_tenure'] = $arr['loan_tenure'];
                                    $check_name = $this->valid_numeric($arr['loan_tenure'], 'Loan Tenure');
                                    if ($check_name['status'] == 'false') {
                                        $endorsement[$k - 1]['Reason'] = $check_name['msg'];
                                        $check = false;
                                    }
                                }
                                if (!empty($rowData[25])) {
                                    $arr['tenure'] = $rowData[25];
                                    $lead_arr['tenure'] = $arr['tenure'];
                                    $check_name = $this->valid_numeric($arr['loan_tenure'], 'Loan Tenure');
                                    if ($check_name['status'] == 'false') {
                                        $endorsement[$k - 1]['Reason'] = $check_name['msg'];
                                        $check = false;
                                    }
                                }

                                if (!empty($rowData[38])) {
                                    $payment_mode_name = $rowData[38];
                                    $payment_mode_id = $this->db->query("select payment_mode_id from payment_modes where   payment_mode_name= '$payment_mode_name'")->row_array();
                                    if (!empty($payment_mode_id)) {
                                        $lead_arr['mode_of_payment'] = $payment_mode_id['payment_mode_id'];
                                        $this->db->where('lead_id', $lead_id);
                                        $this->db->update('proposal_payment_details', ['payment_mode' => $payment_mode_id['payment_mode_id']]);
                                    }

                                }
                                if (isset($rowData[26]) && !empty($rowData[26])) {
                                    $nominee_rel = $rowData[26];
                                    $noninee_rel_id = $this->db->query("select id from master_nominee_relations where name = '$nominee_rel' ")->row_array();
                                    if (empty($noninee_rel_id)) {
                                        $endorsement[$k - 1]['Reason'] = "Invalid Relation ";
                                        $check = false;
                                    } else {
                                        $nominee_det['nominee_relation'] = $noninee_rel_id[26];
                                    }
                                }
                                if (isset($rowData[27]) && !empty($rowData[27])) {
                                    $nominee_det['nominee_salutation'] = $rowData[27];
                                    $check_name = $this->valid_alpha($nominee_det['nominee_salutation'], 'Nominee Salutation');
                                    if ($check_name['status'] == 'false') {
                                        $endorsement[$k - 1]['Reason'] = $check_name['msg'];
                                        $check = false;
                                    }

                                }
                                if (isset($rowData[28]) && !empty($rowData[28])) {
                                    $nominee_det['nominee_first_name'] = $rowData[28];
                                    $check_name = $this->valid_alpha($nominee_det['nominee_first_name'], 'Nominee First Name');
                                    if ($check_name['status'] == 'false') {
                                        $endorsement[$k - 1]['Reason'] = $check_name['msg'];
                                        $check = false;
                                    }

                                }
                                if (isset($rowData[29]) && !empty($rowData[29])) {
                                    $nominee_det['nominee_last_name'] = $rowData[29];
                                    $check_name = $this->valid_alpha($nominee_det['nominee_last_name'], 'Nominee Last Name');
                                    if ($check_name['status'] == 'false') {
                                        $endorsement[$k - 1]['Reason'] = $check_name['msg'];
                                        $check = false;
                                    }

                                }
                                if (isset($rowData[30]) && !empty($rowData[30])) {
                                    $nominee_det['nominee_contact'] = $rowData[30];
                                    $check_name = $this->valid_numeric($nominee_det['nominee_contact'], 'Nominee Contact');
                                    if ($check_name['status'] == 'false') {
                                        $endorsement[$k - 1]['Reason'] = $check_name['msg'];
                                        $check = false;
                                    }

                                }
                                if (isset($rowData[31]) && !empty($rowData[31])) {
                                    $endorsement[$k - 1]['nominee_gender'] = $rowData[31];
                                    $nominee_det['nominee_gender'] = $rowData[31];
                                    $check_name = $this->valid_alpha($nominee_det['nominee_gender'], 'Nominee Gender');
                                    if ($check_name['status'] == 'false') {
                                        $endorsement[$k - 1]['Reason'] = $check_name['msg'];
                                        $check = false;
                                    }

                                }

                                if ($check == true) {
                                    $this->db->where('lead_id', $lead_id);
                                    $this->db->update('master_customer', $arr);

                                    $this->db->where('lead_id', $lead_id);
                                    $this->db->update('lead_details', $lead_arr);
                                }

                            }
//**** self end

                            //**** Correction  of Member Data
                            $rel_id = $rowData[20];
                            if (empty($rel_id) && isset($rowData[16])) {
                                $endorsement[$k - 1]['Reason'] = "Relation Id is Required";
                                $check = false;
                            } else {
                                $relation_type = $this->db->query("select id,is_adult from family_construct where member_type = '$rel_id' ")->row_array();
                                $rel_id_det = $relation_type['id'];
                                $member_data = $this->db->query("select ppmd.relation_with_proposal,ppmd.member_id,ppm.policy_id from proposal_policy_member as ppm  inner join proposal_policy_member_details as ppmd on ppm.member_id = ppmd.member_id where ppm.lead_id = '$lead_id' and ppmd.relation_with_proposal = '$rel_id_det'")->row_array();
                                if (!empty($member_data)) {
                                    if($rel_id_det == 1){
                                        $self_det['rel'] = $rel_id_det;
                                        $self_det['member_id'] = $member_data['member_id'];
                                        array_push($member_details,$self_det);
                                    }


                                    $all_mem_det['rel'] = $rel_id_det;
                                    $all_mem_det['member_id'] = $member_data['member_id'];
                                    //array_push($member_all_det,$all_mem_det);

                                    if (isset($rowData[15]) && !empty($rowData[15])) {
                                        $member_det['policy_member_salutation'] = $rowData[15];
                                    }
                                    if (isset($rowData[16]) && !empty($rowData[16])) {
                                        $member_det['policy_member_first_name'] = $rowData[16];
                                    }
                                    if (isset($rowData[17]) && !empty($rowData[17])) {
                                        $member_det['policy_member_last_name'] = $rowData[17];
                                    }
                                    if (isset($rowData[18]) && !empty($rowData[18])) {
                                        $member_det['policy_member_gender'] = $rowData[18];
                                    }
                                    if (isset($rowData[19]) && !empty($rowData[19])) {
                                        $member_det['policy_member_dob'] = $rowData[19];
                                        $all_dob = $this->exceltophpdate($rowData[19]);
                                        $from = new DateTime($all_dob);
                                        $to = new DateTime('today');
                                        $all_age = $from->diff($to)->y;

                                        $all_age_days = $from->diff($to)->d;
                                        $member_det['policy_member_dob'] = $all_age;
                                        $policy_details = $this->db->query("select master_policy_id from proposal_policy where lead_id = '$lead_id'")->result_array();
                                        foreach ($policy_details as $policyArr) {
                                            $policy_master_id = $policyArr['master_policy_id'];
                                            $check_min_max = $this->db->query("select * from master_policy_family_construct where master_policy_id = '$policy_master_id' AND member_type_id = '$rel_id_det' and  isactive =1")->row_array();
                                            if ($relation_type['is_adult'] == 'Y') {

                                                if (!($all_age >= $check_min_max['member_min_age'] && $all_age <= $check_min_max['member_max_age'])) {

                                                    $endorsement[$k - 1]['Reason'] = "Member Dob is not eligible in policy";
                                                    $check = false;
                                                }

                                            } else {

                                                if ($all_age == 0) {
                                                    $member_det['policy_member_dob'] = $all_age_days;

                                                    if (!($all_age_days >= $check_min_max['member_min_age_days'])) {

                                                        $endorsement[$k - 1]['Reason'] = "Member Dob is not eligible in policy";
                                                        $check = false;
                                                    }
                                                } else {
                                                    if (!($all_age <= $check_min_max['member_max_age'])) {

                                                        $endorsement[$k - 1]['Reason'] = "Member Dob is not eligible in policy";
                                                        $check = false;
                                                    }


                                                }

                                            }
                                        }
                                    }
                                    if (isset($rowData[20]) && !empty($rowData[20])) {
                                        //print_r($rowData[20]);die;
                                        $member_det['relation_with_proposal'] = $rel_id_det;
                                        $check_name = $this->valid_numeric($member_det['relation_with_proposal'], 'Relation With Proposer');
                                        if ($check_name['status'] == 'false') {
                                            $endorsement[$k - 1]['Reason'][] = $check_name['msg'];
                                            $check = false;
                                        }
                                    }
                                    if ($check == true) {


                                        $this->db->where('member_id', $member_data['member_id']);
                                        $this->db->update('proposal_policy_member_details', $member_det);

                                    }


                                } else {
                                    $endorsement[$k - 1]['Reason'][] = "Member does not exist";
                                    $check = false;
                                }
                            }

                          //  if($check == true) {
                                    $name = '';
                                    if(isset($rowData[20]))
                                    {
                                        $name = "_".$rowData[20];
                                    }
                                    for ($i = 32; $i < 37; $i++) {
                                        if (isset($rowData[$i])) {
                                            $data_excel = $rowDatafirst[0][$i];
                                            if (strpos($data_excel, 'Sum') !== false) {
                                                $data_excel_name = str_replace(" ", "_", $data_excel);

                                                $sumInsured[$k - 1][$data_excel_name.$name] = $rowData[$i];
                                                // array_push($suminsued_arr,$sumInsured);

                                            }
                                        }

                                    }


                          //  }

                        }
                         /*   $ghi =   (!empty($rowData[32]))?$rowData[32]:'';
                            $gpa =   (!empty($rowData[33]))?$rowData[33]:'';
                            $gcs =   (!empty($rowData[34]))?$rowData[34]:'';
                            $supertopup =   (!empty($rowData[35]))?$rowData[35]:'';
                            $hospicash =   (!empty($rowData[36]))?$rowData[36]:'';*/


                        }



                    }
                    $k++;


                }
              //  print_r($sumInsured);
                $premium_total = 0;
                $sum_total = 0;
                $cover_total = 0;
                $premium_total_bal = 0;
                if(!empty($sumInsured)) {
                   // print_r($sumInsured);
                    $premium_policy_total = 0;
                    foreach ($sumInsured as $key => $sum_datas) {

                       // print_r($member_details[$key]);

                        foreach($sum_datas as $key2 => $sum_data) {

                       //     print_r($sum_data);
                            $sum_exp = explode('_', $key2);
                            $sum_ins = $sum_exp[2];
                            $rel_sum = $sum_exp[3];
                            $check_sum = $this->db->query("select apr.master_policy_id,mpst.policy_sub_type_id from api_proposal_response as apr inner join lead_details as l inner join master_policy_sub_type as mpst where apr.lead_id = l.lead_id  and apr.policy_sub_type_id = mpst.policy_sub_type_id and mpst.code = '$sum_ins' and l.lead_id = '$lead_id'")->row_array();
                            if(empty($check_sum))
                            { //echo 123;
                                $endorsement[$key]['Reason'][] = "$sum_ins Not Opted for trace id";
                                $check = false;

                            }else
                            {
                                $cover_total = $cover_total + $sum_data;
                                $master_policy_id = $check_sum['master_policy_id'];
                                $get_all_count = $this->db->query("select adult_count,child_count,proposal_policy_id from proposal_policy where master_policy_id = '$master_policy_id' and lead_id = '$lead_id'")->row_array();
                               // print_r($this->db->last_query());
                                $req_data['lead_id'] = $lead_id;
                                $req_data['customer_id'] = $lead_id_que['customer_id'];
                                $req_data['policy_id'] = $check_sum['master_policy_id'];
                                $req_data['adult_count'] = $get_all_count['adult_count'];
                                $req_data['child_count'] = $get_all_count['child_count'];
                               // $req_data['is_adult'] = $is_adult;
                                $req_data['partner_id'] = $creditor_id;
                                $req_data['policy_sub_type_id'] = $check_sum['policy_sub_type_id'];
                                $req_data['deductable'] = 0;
                                $proposal_policy_id = $get_all_count['proposal_policy_id'];
                                $member_max_age = $this->db->query("select  max(ppmd.policy_member_age)as age  from proposal_policy_member as ppm  inner join proposal_policy_member_details as ppmd on ppm.member_id = ppmd.member_id where ppm.lead_id = '$lead_id' and  ppm.proposal_policy_id = '$proposal_policy_id' group by ppm.proposal_policy_id ")->row_array();
                                $req_data['max_age'] = $member_max_age['age'];
                                $member_age = $this->db->query("select  ppmd.member_id,ppmd.policy_member_age as age  from proposal_policy_member as ppm  inner join proposal_policy_member_details as ppmd on ppm.member_id = ppmd.member_id where ppm.lead_id = '$lead_id' and  ppm.proposal_policy_id = '$proposal_policy_id' and policy_member_age_in_months = 'years' ")->result_array();
                                $sum_insured_type = $this->db->query("select * from master_policy_si_type_mapping where master_policy_id = '$master_policy_id' ")->row_array();
                                if($sum_insured_type['suminsured_type_id'] == 2 && !in_array(1,$member_details[$key]['rel']))
                                {
                                    $endorsement[$key]['Reason'][] = "Self required for family floater";
                                    $check = false;
                                }
                                if($sum_insured_type['suminsured_type_id'] == 2 && in_array(1,$member_details[$key]['rel'] && $rel_sum == 'self'))
                                {
                                    $req_data['cover'] = $sum_data;
                                    $get_data =   $this->get_premium_update($req_data,$lead_id,$sum_total,$premium_total,$sum_data,$premium_total_bal,$sum_ins,$member_age,$plan_id,$master_policy_id,$creditor_id,$lead_arr['email_id'],$sum_type_id = 2);
                                 if($get_data['check'] == false)
                                 {
                                     $endorsement[$key]['Reason'][] = $get_data['reason'] ;
                                     $check = false;

                                 }
                                }
                                if($sum_insured_type['suminsured_type_id'] == 1)
                                { //echo 456;
                                    $req_data['cover'] = $sum_data;
                                    $get_data =   $this->get_premium_update($req_data,$lead_id,$sum_total,$premium_total,$sum_data,$premium_total_bal,$sum_ins,$member_age,$plan_id,$master_policy_id,$creditor_id,$lead_arr['email_id'],$sum_type_id = 1);
                                    if($get_data['check'] == false)
                                    {
                                        $endorsement[$key]['Reason'][] = $get_data['reason'] ;
                                        $check = false;


                                    }
                                }
                            }
                          // print_r($endorsement);
                        }


                    }
                    if( $check == true)
                    {
                        $this->db->where('lead_id', $lead_id);
                        $this->db->update('proposal_payment_details', ['sum_insured' => $cover_total]);


                        $this->db->where('lead_id', $lead_id);
                        $this->db->update('proposal_payment_details', ['premium_bal'=>$get_data['premium_bal'],'premium' => $get_data['premium'],'premium_with_tax'=>$get_data['premium']]);
                        $get_payment_mode = $this->db->query("select premium_total_bal,payment_mode from proposal_payment_details where lead_id = '$lead_id'")->row_array();
                        if(!empty($get_payment_mode))
                        {
                            if($get_payment_mode['payment_mode'] == 4)
                            {
                                $q_creditor_id= $this->db->query("select creditor_id,ceditor_email,creaditor_name from master_ceditors where creditor_id = '$creditor_id' ")->row_array();
                                $creditor_id=$q_creditor_id['creditor_id'];
                                $ceditor_email=$q_creditor_id['ceditor_email'];
                                $creaditor_name=$q_creditor_id['creaditor_name'];
                                $response_mail_sent_cd=checkdailymailsent($creditor_id,'status_cd');
                                if ($response_mail_sent_cd == 0) {
                                    $response_cd = CheckCDThreshold($creditor_id, $plan_id, $premium_total_bal);

                                    $data_arr_cd = $response_cd['data'];
                                    $cd_threshold = $data_arr_cd['threshold_amount'];
                                    $balance = $data_arr_cd['balance'];
                                    $collection_amt = $data_arr_cd['collection_amount'];
                                    if ($response_cd['status'] == 201) {
                                        $mail = sendMailCDbalance($ceditor_email, $creaditor_name, $cd_threshold, $balance, $collection_amt, $response_cd['msg']);
                                        updatedailymail($creditor_id, 'status_cd');
                                        echo $response_cd['msg'];
                                        die;
                                    } else {
                                        if ($response_cd['msg'] == "NegativeAllow") {
                                            $mail = sendMailCDbalance($ceditor_email, $creaditor_name, $cd_threshold, $balance, $collection_amt, $response_cd['msg']);
                                            updatedailymail($creditor_id, 'status_cd');
                                        } else if ($response_cd['msg'] == "LessCD") {
                                            $mail = sendMailCDbalance($ceditor_email, $creaditor_name, $cd_threshold, $balance, $collection_amt, $response_cd['msg']);
                                            updatedailymail($creditor_id, 'status_cd');
                                        } else {
                                            $response = array('status' => 200, 'msg' => "Success", 'data' => array());

                                        }
                                    }
                                }
                                $cd_data = array(
                                    'type' => 2,
                                    'amount' => $premium_total_bal,
                                    'lead_id' => $lead_id,
                                    'creditor_id' => $creditor_id,
                                    'type_trans' =>"Policy Issuance",
                                );
                                $update_cd_data = curlFunction(SERVICE_URL . '/customer_api/CD_credit_debit_entry', $cd_data);

                            }
                        }
                        if($get_payment_mode['payment_mode'] == 1)
                        {
                            $payment_url= FRONT_URL.'/quotes/redirect_to_pg?lead_id='.encrypt_decrypt_password($lead_id);
                            sendMail( $lead_arr['email_id'],"Payment Url",$payment_url);
                        }

                    }

                    }
                foreach($endorsement as $key =>$end) {
                    if (!empty($end['Reason'])) {

                    $end_reason = implode(',', $end['Reason']);
                    $endorsement[$key]['Reason'] = $end_reason;
                    $endorsement[$key]['status'] = 'Failed';

                    }

                }
               // print_r();
                $result =   $this->endorsement_insert($endorsement,'insert');

                if($result == true)
                {
                    $response['msg']="Uploaded Successfully";
                    $response['success']=true;
                    echo json_encode($response);
                }
                }else{
                $response['msg']="Wrong file Uploaded";
                $response['success']=false;
            }
            }
        }
        public function endorsement_insert($endorsement,$stmt)
        {
            foreach ($endorsement as $key=>$value)
            {
                if($stmt == 'insert')
                {
                    $this->db->insert('endorsement_details',$value);

                }
            }
            return true;

        }

    public function get_premium_update($req_data,$lead_id,$sum_total,$premium_total,$sum_data,$premium_total_bal,$sum_ins,$member_age,$plan_id,$master_policy_id,$creditor_id,$email_id,$sum_type_id)
    {
        $data['check'] = true;
        // echo 4567;
        $sum_total = 0;
        $premium_total_bal = 0;
        foreach ($member_age as $all_details) {
            //    print_r($all_details);
            $req_data['age'] = $all_details['age'];

            $response = json_decode(curlFunction(SERVICE_URL . '/customer_api/get_premium', $req_data), true);
            // print_r($response);
            if (!empty($response['amount'])) {
                if ($sum_type_id == 1) {
                    $sum_total = $sum_total + $response['amount'];

                } else {
                    $sum_total = $response['amount'];

                }
                $this->db->where('member_id', $all_details['member_id']);
                $this->db->where('policy_id', $master_policy_id);
                $this->db->update('proposal_policy_member', ['premium' => $response['amount'], 'premium_with_tax' => $response['amount']]);

            } else {
                $data['reason']= "Rate  Not Found for " . $sum_ins;
                $data['check'] = false;
            }
        }
        if($data['check'] == true){
            $data['premium'] = $sum_total;
        $premium_check = $this->db->query("select premium_amount as premium_amount from proposal_policy where lead_id = '$lead_id' and master_policy_id = '$master_policy_id' group by master_policy_id ")->row_array();
        if (!empty($premium_check)) {
            $premium_bal = 0;
            if ($premium_check['premium_amount'] != $sum_total) {

                if ($sum_total > $premium_check['premium_amount']) {
                    $premium_bal = $sum_total - $premium_check['premium_amount'];
                    $data['premium_bal'] = $premium_bal;

                    // $premium_total_bal = $premium_total_bal + $premium_bal;
                    //redirect to pg insert into new column
                }

                $this->db->where('lead_id', $lead_id);
                $this->db->where('master_policy_id', $master_policy_id);
                $this->db->update('proposal_policy', ['premium_amount' => $sum_total, 'tax_amount' =>$sum_total,'premium_bal'=>$premium_bal,'old_premium'=>$premium_check['premium_amount']]);

                $this->db->where('lead_id', $lead_id);
                $this->db->where('master_policy_id', $master_policy_id);
                $this->db->update('proposal_policy', ['sum_insured' => $sum_data]);

                $cover['cover'] = $sum_data;
                $cover['premium'] = $sum_total;
                $this->db->where('lead_id', $lead_id);
                $this->db->where('policy_id', $master_policy_id);
                $this->db->update('quote_member_plan_details', $cover);

                $data['check'] = true;
            }
        }
    }

        return $data;
    }

}