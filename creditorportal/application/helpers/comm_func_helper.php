<?php defined('BASEPATH') OR exit('No direct script access allowed');

// created by Upendra - on 16-10-2021

use \Firebase\JWT\JWT;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

use function PHPSTORM_META\type;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require_once PATH_VENDOR.'vendor/autoload.php';

require_once PATH_VENDOR.'vendor/phpmailer/phpmailer/src/Exception.php';
require_once PATH_VENDOR.'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once PATH_VENDOR.'vendor/phpmailer/phpmailer/src/SMTP.php';
if (!function_exists('common_captcha_create'))
{
	function common_captcha_create($captcha_string)
	{	
		// echo"coming";exit;

		$ci =& get_instance();
		$ci ->load->library('image_lib');
        $ci ->load->helper('captcha');
        $vals = array(
            'word'          => $captcha_string,
            'img_path'      => 'assets/captchaimages/',
            'img_url'       =>  base_url().'assets/captchaimages/',
            'font_path'     => './path/to/fonts/texb.ttf',
            // 'font_path'     => './public/assets/images/texb.ttf',
            'img_width'     => '150',
            'img_height'    => '30',
            'expiration'    => '7200',
            'word_length'   => '6',
            'font_size'     => '16',
            'img_id'        => 'captcha_image_load',
            'pool'          => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
    
            // White background and border, black text and red grid
            'colors'        => array(
                    'background' => array(255, 255, 255),
                    'border' => array(255, 255, 255),
                    'text' => array(0, 0, 0),
                    'grid' => array(255, 40, 40)
            )
    );
    
    $cap = create_captcha($vals);
	// echo"Coming";
	// print_r($vals['img_path']);exit;
	return $cap;
    
     }
}

if (!function_exists('CheckCDThreshold')) {
    function CheckCDThreshold($creditor_id,$plan_id,$collection_amount=0)
    {
        $CI = &get_instance();
        $plan_payment_mode=$CI->db->query("select payment_mode_id  from plan_payment_mode  where master_plan_id = '$plan_id' and payment_mode_id=4");
        //echo $CI->db->last_query();die;
        if($CI->db->affected_rows() > 0){
            $amount_utilzed = (int) $CI->db->query("select sum(mt.amount) as amount_utilzed from master_cd_credit_debit_transaction mt
  join lead_details ld on ld.lead_id=mt.lead_id where mt.type=2 and mt.creditor_id=" . $creditor_id)->row()->amount_utilzed;

            $amount_deposite = $CI->db->query("select sum(mt.amount) as amount_deposite from master_cd_credit_debit_transaction mt
where mt.type=1 and mt.creditor_id=" . $creditor_id)->row()->amount_deposite;
            $get_policy_det = $CI->db->query("select initial_cd,cd_threshold,cd_utilised,allow_negative_issuance,threshold_value,(select sum(amount) from cd_deposit cd where cd.partner_id=mc.creditor_id) as deposit  from master_ceditors mc where creditor_id = '$creditor_id'")->row_array();
            $allow_negative_issuance=0;
            if (count($get_policy_det) > 0) {
                $cd_threshold_percent = $get_policy_det['cd_threshold'];
                $initial_cd = $get_policy_det['initial_cd'];
                $allow_negative_issuance = $get_policy_det['allow_negative_issuance'];
                $threshold_value = $get_policy_det['threshold_value'];
                $total_amount = ($amount_deposite);
                if($threshold_value == 1){
                    $cd_threshold = (($total_amount) * ($cd_threshold_percent)) / 100; //percent
                }else{
                    $cd_threshold=$cd_threshold_percent; //amount
                }

                $cd_utilised = $amount_utilzed;
                $balance = ($total_amount) - $cd_utilised;
            }
            $arr = array(
                'amount_utilized' => $amount_utilzed,
                'total_amount' => $total_amount,
                'threshold_amount' => $cd_threshold,
                'balance' => $balance,
                'initial_amount' => $initial_cd,
                'allow_negative_issuance' => $allow_negative_issuance,
                'collection_amount' => $collection_amount,
            );
            if ($balance <= 0) {
                if ($allow_negative_issuance == 1) {
                    $response = array('status' => 200, 'msg' => "NegativeAllow", 'data' => $arr);
                } else {
                    $response = array('status' => 201, 'msg' => "Not Sufficient CD Balance.", 'data' => $arr);
                }
            }else if($balance < $collection_amount){
                if ($allow_negative_issuance == 1) {
                    $response = array('status' => 200, 'msg' => "NegativeAllow", 'data' => $arr);
                } else {
                    $response = array('status' => 201, 'msg' => "Not Sufficient CD Balance.", 'data' => $arr);
                }
            } elseif ($balance <= $cd_threshold) {
                $response = array('status' => 200, 'msg' => "LessCD", 'data' => $arr);
            }else if($balance >= $collection_amount){
                $response = array('status' => 200, 'msg' => "Success", 'data' => $arr);
            } else {
                $response = array('status' => 201, 'msg' => "Not Sufficient CD Balance.", 'data' => $arr);
            }
        }else{
            $response = array('status' => 200, 'msg' => "Payment Mode is not CD balance for this plan.",'data'=>array());
        }

        return $response;
    }
}

if(!function_exists('CheckCoverBalance'))
{
    function CheckCoverBalance($creditor_id,$plan_id='',$policy_id='',$collection_amount=0)
    {
        $CI = &get_instance();
        $cond = false;
        if(!empty($policy_id)){
            $cover_details = $CI->db->query("select initial_cover  from master_policy  where policy_id = '$policy_id' and isactive=1")->row_array();
           // print_r($cover_details);die;
            if(!empty($cover_details['initial_cover'])){

            $amount_utilized = $CI->db->query("select sum(mt.amount) as amount_utilzed from master_cover_credit_debit_transaction mt

  join lead_details ld on ld.lead_id=mt.lead_id where mt.type=2 and mt.creditor_id = '$creditor_id' and mt.plan_id = '$plan_id' and mt.policy_id = '$policy_id'")->row()->amount_utilzed;

            $amount_deposite = $CI->db->query("select sum(mt.amount) as amount_deposite from master_cover_credit_debit_transaction mt
where mt.type=1 and mt.creditor_id = '$creditor_id' and mt.plan_id = '$plan_id' and mt.policy_id = '$policy_id'")->row()->amount_deposite;

            $get_policy_det = $CI->db->query("select initial_cover,cover_limit,cover_utilized  from master_policy mc where policy_id = '$policy_id'")->row_array();
            $allow_negative_issuance=0;
            if (count($get_policy_det) > 0) {
                $cover_limit = $get_policy_det['cover_limit'];
                $initial_cover = $get_policy_det['initial_cover'];
                $total_amount = $amount_deposite;
                $cover_utilised = $amount_utilized;
                $balance = ($total_amount) - $cover_utilised;
            }
            $arr = array(
                'amount_utilized' => $amount_utilized,
                'total_amount' => $total_amount,
                'cover_limit' => $cover_limit,
                'balance' => $balance,
                'initial_amount' => $initial_cover,
                'collection_amount' => $collection_amount,
            );
            if ($balance <= 0) {
                    $response = array('status' => 201, 'msg' => "Not Sufficient Cover Balance.", 'data' => $arr);

            }else if($balance < $collection_amount){

                    $response = array('status' => 201, 'msg' => "Not Sufficient Cover Balance.", 'data' => $arr);

            } elseif ($balance <= $cover_limit) {
                $response = array('status' => 200, 'msg' => "LessCover", 'data' => $arr);
            }else if($balance >= $collection_amount){
                $response = array('status' => 200, 'msg' => "Success", 'data' => $arr);
            } else {
                $response = array('status' => 201, 'msg' => "Not Sufficient Cover Balance.", 'data' => $arr);
            }


        return $response;
    }
            else{
                $response = array('status' => 0, 'msg' => "Not applicable", 'data' => '');
                return $response;
            }
        }

    }
}


if (!function_exists('sendMailCoverbalance')) {
    function sendMailCoverbalance($ceditor_email,$creaditor_name,$cover_limit,$balance,$collection_amt,$msg){


        $mail = new PHPMailer(true);

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.office365.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->SMTPSecure = "tls";
            $mail->Username   = 'noreply@elephant.in';                     //SMTP username
            $mail->Password   = 'dpwvzfrtjzmqlvcc';                               //SMTP password
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('noreply@elephant.in', 'Mailer');
            // $mail->addAddress('poojalote123@gmail.com', 'Pooja Lote');     //Add a recipient
            //    $mail->addAddress('pooja.lote@fyntune.com', 'Pooja Fyntune');     //Add a recipient
            $mail->addAddress($ceditor_email);
            //$mail->addBcc('poojalote123@gmail.com');

            //Add a recipient
            if($msg == "LessCover"){
                $p='<p>Your Cover Limit has reached its threshold value, as indicated below.</p>';
            } else if($msg == "NegativeAllow"){
                $p='<p>Your Cover Limit is in negative.</p>';
            }else{
                $p='<p>Your Cover Limit has reached its threshold value, as indicated below.</p>';
            }

            $body="
        Dear ".$creaditor_name.",<br>

".$p."
<p>Threshold for Cover Limit :	".$cover_limit."</p>
<p>Total Cover Limit :	".$balance."</p>
<p>Amount You Requested :	".$collection_amt."</p>
<p>Please add Cover Limit to your account.</p>
Thanks ,<br>
Team Elephant<br>

Please do not reply, this is system generated e-mail.
";
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Insufficient Cover balance.';
            $mail->Body    = $body;
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            return 'Message has been sent';
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}




if (!function_exists('sendMailCDbalance')) {
    function sendMailCDbalance($ceditor_email,$creaditor_name,$cd_threshold,$balance,$collection_amt,$msg){


        $mail = new PHPMailer(true);

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.office365.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->SMTPSecure = "tls";
            $mail->Username   = 'noreply@elephant.in';                     //SMTP username
            $mail->Password   = 'dpwvzfrtjzmqlvcc';                               //SMTP password
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('noreply@elephant.in', 'Mailer');
            // $mail->addAddress('poojalote123@gmail.com', 'Pooja Lote');     //Add a recipient
            //    $mail->addAddress('pooja.lote@fyntune.com', 'Pooja Fyntune');     //Add a recipient
            $mail->addAddress($ceditor_email);
           // $mail->addBcc('poojalote123@gmail.com');

            //Add a recipient
            if($msg == "LessCD"){
                $p='<p>Your CD balance has reached its threshold value, as indicated below.</p>';
            } else if($msg == "NegativeAllow"){
                $p='<p>Your CD balance is in negative.</p>';
            }else{
                $p='<p>Your CD balance has reached its threshold value, as indicated below.</p>';
            }
            
            $body="
        Dear ".$creaditor_name.",<br>

".$p."
<p>Threshold for CD balance :   ".$cd_threshold."</p>
<p>Total CD Balance :   ".$balance."</p>";
if($collection_amt>0){

    $body.="<p>Amount You Requested :   ".$collection_amt."</p>";
}

$body.="<p>Please add CD balance to your account.</p>
Thanks ,<br>
Team Elephant<br>

Please do not reply, this is system generated e-mail.
";
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Insufficient CD balance.';
            $mail->Body    = $body;
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            return 'Message has been sent';
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
if (!function_exists('sendMail'))
{
    function sendMail($to,$subject,$body){
        $mail = new PHPMailer(true);

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.office365.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->SMTPSecure = "tls";
            $mail->Username   = 'noreply@elephant.in';                     //SMTP username
            $mail->Password   = 'dpwvzfrtjzmqlvcc';                               //SMTP password
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('noreply@elephant.in', 'Mailer');
            ///    $mail->addAddress('poojalote123@gmail.com', 'Pooja Lote');     //Add a recipient
            //    $mail->addAddress('pooja.lote@fyntune.com', 'Pooja Fyntune');     //Add a recipient
            $mail->addAddress($to);
           

            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();

            return json_encode(["status"=>true]);
        } catch (Exception $e) {
            return json_encode(["status"=>false,'message'=>"Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);

        }
    }
}

if(!function_exists('sendSms')){
    function sendSms($sms_to,$sms_text){
          
        
        $postdata = http_build_query(
            array(
                 'feedid'=>'366724',
                 'senderid'=>'ELPHNT',
                 'username'=>'7777001974',
                 'password'=>'Alliance@123',
                 'To'=>$sms_to,
                 'Text'=>urlencode('sms_text'),
                       //'AxisDirect Trading a/c. Our Sales Team___________ (name - Mbl no) will contact you shortly.'
            )
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,'http://bulkpush.mytoday.com/BulkSms/SingleMsgApi');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $xml = simplexml_load_string($response);
        $json = json_encode($xml);
        
        return $json;

    
    }
}

function checkdailymailsent($creditor_id,$column){

    $CI = &get_instance();
    $date=date('Y-m-d');
$q=$CI->db->query('select '.$column. ' from daily_mail_sent_status where creditor_id='.$creditor_id. " and date(date)='".date($date)."'");
//echo $CI->db->last_query();die;
if($CI->db->affected_rows() > 0){
    $status=$q->row()->$column;
    if($status == 1){
        return 1;
    }else{
        return 0;
    }
}else{
    return 0;
}
}
function updatedailymail($creditor_id,$column){
    $data=array(
        'creditor_id'=>$creditor_id,
        $column=>1,
    );
    $date=date('Y-m-d');
    $CI = &get_instance();
    $q=$CI->db->query('select '.$column. ' from daily_mail_sent_status where creditor_id='.$creditor_id. " and date(date)='".date($date)."'");
    if($CI->db->affected_rows() > 0){
        $where=array('creditor_id'=>$creditor_id,'date(date)'=>date($date));
        $set=array($column=>1);
        $CI->db->where($where);
        $result=$CI->db->update('daily_mail_sent_status',$set);
    }else{
        $result=$CI->db->insert('daily_mail_sent_status',$data);
    }

    return 1;

}

if(!function_exists('createCommunicationLog')){
    function createCommunicationLog($data){

      
      $CI = &get_instance();    
      $CI->db->insert('communication_logs',$data);
      return $result_id = $CI->db->insert_id();
    
    }
}

if(!function_exists('setSessionByLeadId')){
    function setSessionByLeadId($lead_id){
      //  print_r($lead_id);die;
        $CI = &get_instance();
        $lead_id = encrypt_decrypt_password($lead_id, 'D');
        $CI->session->set_userdata('is_normal_customer', 0);
        $lead_details=$CI->db->query("select l.lead_id,l.trace_id,l.is_mailer_api,l.proposal_step_completed,l.plan_id,m.plan_id as quote_plan_id,m.si_type_id,m.cover,m.premium,m.policy_id,l.creditor_id,journey_type,dropout_page,d.route,c.customer_id,c.full_name,m.plan_name,m.policy_details from lead_details l left join master_customer c on c.lead_id=l.lead_id left join master_plan p on p.plan_id=l.plan_id left join master_communication_events d on d.id=l.dropout_page left join quote_member_plan_details m on m.lead_id=l.lead_id  where l.lead_id=".$lead_id." group by l.lead_id")->row(); 
        $payment_details = json_decode(curlFunction(SERVICE_URL . '/customer_api/checkPaymentStatus',['lead_id'=>$lead_id]), TRUE);

        if(!empty($payment_details) && $payment_details['payment_status'] == 'Success'){
            $proposal_policy  = $CI->db->query("select proposal_policy_id from proposal_policy where lead_id=" . $lead_id)->row();

            if(!empty($proposal_policy) &&  strpos( current_url(), 'success_view' ) === false  && !empty($lead_details->dropout_page) && $lead_details->dropout_page<=10 &&  strpos( current_url(), 'coidownload' ) === false  ){
                redirect('/quotes/success_view/'. encrypt_decrypt_password($lead_id, 'E').'?lead_id='.encrypt_decrypt_password($lead_id, 'E'));
            }else if(empty($proposal_policy) &&  strpos( current_url(), 'generate_proposal' ) === false && strpos( current_url(), 'success_view' ) === false ){
                 redirect('/quotes/success_view/'. encrypt_decrypt_password($lead_id, 'E').'?lead_id='.encrypt_decrypt_password($lead_id, 'E'));
            }
            

        }

        //echo '<pre>';print_r($lead_details);exit;
        $journey_type = $lead_details->journey_type;
        if(!empty($lead_details)){
            $CI->session->set_userdata('customer_id', encrypt_decrypt_password($lead_details->customer_id, 'E'));
            $CI->session->set_userdata('lead_id', encrypt_decrypt_password($lead_details->lead_id, 'E'));
            $CI->session->set_userdata('trace_id', encrypt_decrypt_password($lead_details->trace_id, 'E'));
            
            if(!empty( $lead_details->cover)){
                $CI->session->set_userdata('cover', $lead_details->cover);
            }if(!empty( $lead_details->premium)){
                $CI->session->set_userdata('premium', $lead_details->premium);
            }if(!empty( $lead_details->policy_id)){
                $CI->session->set_userdata('policy_id', encrypt_decrypt_password($lead_details->policy_id, 'E'));
                //$CI->session->set_userdata('policy_id', $lead_details->policy_id);
            }else if($lead_details->is_mailer_api==1){

                $policy_id=$CI->db->query("select policy_id from master_policy where plan_id in(".$lead_details->plan_id.")")->row();
                
                $CI->session->set_userdata('policy_id', encrypt_decrypt_password($policy_id->policy_id, 'E'));
                
                //$CI->session->set_userdata('policy_id', $lead_details->policy_id);
            }if(!empty( $lead_details->plan_id)){
                $CI->session->set_userdata('plan_id', $lead_details->plan_id);
            }if(!empty( $lead_details->quote_plan_id)){
                $CI->session->set_userdata('plan_id', $lead_details->quote_plan_id);
            }if(!empty( $lead_details->si_type_id)){
                $CI->session->set_userdata('si_type_id', $lead_details->si_type_id);
            }else if($lead_details->is_mailer_api==1){
                $si_type=$CI->db->query("select si_type_id from member_ages where lead_id='".$lead_details->lead_id."'")->row();
                $CI->session->set_userdata('si_type_id', $si_type->si_type_id);
            }if(!empty( $lead_details->plan_name)){
                $CI->session->set_userdata('plan_name', $lead_details->plan_name);
            }if(!empty( $lead_details->policy_details)){
                $CI->session->set_userdata('policy_details_session', json_decode($lead_details->policy_details,true));
            }        
            if($CI->session->userdata('plan_id')){
                $payment_plan  = $CI->db->query("select payment_first,payment_page from master_plan where plan_id=" . $CI->session->userdata('plan_id'))->row();
                if(!empty($payment_plan) && $payment_plan->payment_first==1  && $payment_plan->payment_page==1  && (empty($payment_details) || (!empty($payment_details) && $payment_details['payment_status'] != 'Success')) &&  strpos( current_url(), 'generate_proposal' ) !== false ){
                    $url = getRedirectRoute($lead_details,$journey_type);
                    redirect($url);

                }
                
                if(!empty($payment_details) && $payment_details['payment_status'] == 'Success' && strpos( current_url(), 'generate_proposal' ) !== false && empty($proposal_policy) && $payment_plan->payment_page!=1){
                   
                     if( $payment_plan->payment_page==2 &&  $_REQUEST['view']!= 'idetails' ){
                        redirect('/quotes/generate_proposal?lead_id='.encrypt_decrypt_password($lead_id, 'E').'&view=idetails');
                     } if( $payment_plan->payment_page==3 &&  $_REQUEST['view']!= 'ndetails' ){
                        redirect('/quotes/generate_proposal?lead_id='.encrypt_decrypt_password($lead_id, 'E').'&view=ndetails');
                     }
                     
                }
            }
            if($journey_type==2 || $journey_type==3){
                $CI->session->set_userdata('partner_id_session', encrypt_decrypt_password($lead_details->creditor_id, 'E'));
                if($journey_type==3){
                    $CI->session->set_userdata('product_id_session', encrypt_decrypt_password($lead_details->plan_id, 'E'));
                }
                $arr['partner_id'] =$lead_details->creditor_id;

                $checkDetails = json_decode(curlFunction(SERVICE_URL . '/customer_api/fetchPartnerDetails', $arr),TRUE);
                   
                if(!empty($checkDetails)){
                    $theme_param= $checkDetails['theme_param'];
                    $theme_param_arr=explode(",",$theme_param);
                    
                    $CI->session->set_userdata('primary_color', $theme_param_arr[0]);
                    $CI->session->set_userdata('secondary_color', $theme_param_arr[1]);
                    $CI->session->set_userdata('text_color', $theme_param_arr[2]);
                    $CI->session->set_userdata('background_color', $theme_param_arr[3]);
                    $CI->session->set_userdata('cta_color', $theme_param_arr[4]);
                }

                if($journey_type==3){
                    $qu=$CI->db->query("select * from link_ui_configuaration where plan_id=".$lead_details->plan_id);
                }else{
                    $qu=$CI->db->query("select * from link_ui_configuaration where creditor_id=".$lead_details->creditor_id);
                }

                
                if($CI->db->affected_rows() > 0){
                    $re_qu=$qu->result_array();
                    $CI->session->set_userdata('linkUI_configuaration', $re_qu);
                }else{
                    unset($_SESSION['linkUI_configuaration']);
                }

                
            }else{

                $CI->session->set_userdata('is_normal_customer', 1);
            }

          
        }    
       
    }


}

if(!function_exists('getRedirectRoute')){
    function getRedirectRoute($lead_details,$journey_type){
        $route = $lead_details->route;
        $url = base_url().$route.'?lead_id='.encrypt_decrypt_password($lead_details->lead_id, 'E');
        if($route=='customerportal'){
            switch ($journey_type) {
                case '2':
                    $url .= '&partner='.encrypt_decrypt_password($lead_details->creditor_id, 'E');
                    break;
                case '3':
                    $url .= '&partner='.encrypt_decrypt_password($lead_details->creditor_id, 'E').'&product='.encrypt_decrypt_password($lead_details->plan_id, 'E');
                    break;
                
                default:
                    $url .= '?lead_id='.encrypt_decrypt_password($lead_details->lead_id, 'E');
                    break;
            }
        }
        return $url;
    }

}

if(!function_exists('getSelfAge')){
    function getSelfAge(){
        $CI = &get_instance();
        $lead_id = encrypt_decrypt_password($_REQUEST['lead_id'], 'D');
       
        return $CI->db->query("select member_age from member_ages where lead_id=".$lead_id."  and member_type=1 order by id desc")->row(); 
    }

}

if(!function_exists('lsqPush')){
    function lsqPush($lead_id,$event){
return true;
        $CI = &get_instance();
        
        $lead_details=$CI->db->query("select * from lead_details where lead_id=".$lead_id)->row();
        //print_r($lead_details);exit;
        $policy = $CI->db->query("select * from master_policy where policy_sub_type_id=5 and plan_id=" . $lead_details->plan_id)->row();
        if(empty($policy)){
            return true;
        }

        $proposal_policy  = $CI->db->query("select * from proposal_policy where lead_id=" . $lead_id)->row();
        $customer_details = $CI->db->query("select * from master_customer where lead_id=" . $lead_id)->row();
        $plan_details = $CI->db->query("select * from quote_member_plan_details where lead_id=" . $lead_id)->row();
        $payment_details = $CI->db->query("select * from proposal_payment_details where lead_id=" . $lead_id)->row();
        $api_proposal_response = $CI->db->query("select * from api_proposal_response where lead_id=" . $lead_id)->row();

        $members = $CI->db->query("select * from proposal_policy_member_details where lead_id=" . $lead_id)->result_array();
        

        $member_agess = $CI->db->query("select * from member_ages where lead_id=" . $lead_id)->result_array();


        $keys = ['mx_Custom_75','mx_Custom_76','mx_Custom_77','mx_Custom_81','mx_Custom_82','mx_Custom_83','mx_Custom_84'];
        
        $lead_array =[
            [
                'Attribute' => 'FirstName',
                'Value' => $customer_details->first_name,
            ],
            [
                'Attribute' => 'LastName',
                'Value' => $customer_details->last_name,
            ],
            [
                'Attribute' => 'EmailAddress',
                'Value' => $customer_details->email_id,
            ],
             [
                'Attribute' => 'mx_Work_Email_Id', //
                'Value' => $customer_details->email_id,
            ],
            [
                'Attribute' => 'mx_Customer_Email_ID', //work email id
                'Value' => $customer_details->email_id,
            ],
            [
                'Attribute' => 'mx_Alternate_Email', //personal email id
                'Value' => $customer_details->email_id,
            ],
            [
                'Attribute' => 'Phone',
                'Value' => $customer_details->mobile_no,
            ],
            [
                'Attribute' => 'SourceCampaign',
                'Value' => '',
            ],
            [
                'Attribute' => 'SourceContent',
                'Value' => '',
            ],
            [
                'Attribute' => 'SourceMedium',
                'Value' => '',
            ],


            [
                'Attribute' => 'ProspectID',
                'Value' =>  $customer_details->mobile_no,
            ],
            [
                'Attribute' => 'SearchBy',
                'Value' => 'Phone',
            ],
            [
                'Attribute' => 'mx_Whatsapp_confirmation',
                'Value' =>  'No',
            ],
            [
                'Attribute' => 'mx_SMS_Opt_In',
                'Value' => 'No',
            ]
        ];

        $opp_lead_details = array(
            ['Attribute' => 'Phone', 'Value' => $customer_details->mobile_no],
            ['Attribute' => 'SearchBy', 'Value' => 'Phone']

        );
        $Opportunity = [

            'OpportunityEventCode'  => '12004',
            'OpportunityNote'       => 'Super Top Up', //'Opportunity capture api',
            'OpportunityDateTime'   => date('Y-m-d H:i:s')
        ];
        $fields[]=['SchemaName' => 'mx_Custom_1', 'Value' => ucwords('Super Top Up User')];
        $fields[]=['SchemaName' => 'mx_Custom_2', 'Value' => 'Need Analysis'];
        $fields[]=['SchemaName' => 'Status', 'Value' => 'Open'];
        //$fields[]=['SchemaName' => 'mx_Custom_10', 'Value' => 'Super Top Up'];
        
        $fields[]=['SchemaName' => 'mx_Custom_40', 'Value' => $customer_details->first_name.' '.$customer_details->last_name];
        $fields[]=['SchemaName' => 'mx_Custom_53', 'Value' => $lead_details->city];
        $fields[]=['SchemaName' => 'mx_Custom_69', 'Value' => $lead_details->state];
        $fields[]=['SchemaName' => 'mx_Custom_64', 'Value' => 'Super Top Up'];
        if(!empty($plan_details)){
            $fields[]=['SchemaName' => 'mx_Custom_62', 'Value' => $plan_details->cover];
            $fields[]=['SchemaName' => 'mx_Custom_39', 'Value' => $plan_details->premium];
            $fields[]=['SchemaName' => 'mx_Custom_42', 'Value' => $plan_details->premium];
        }

        
        
        if(!empty($lead_details->lsq_opportunity_id)){
            
            $fields[]=['SchemaName' => 'mx_CustomObject_90', 'Value' => $customer_details->gender];
            $fields[]=['SchemaName' => 'mx_CustomObject_92', 'Value' => $lead_details->pincode];
            
        }
        
        if(!empty($member_agess)){
            if(in_array(1, array_column($member_agess, 'member_type'))){
                $fields[]=['SchemaName' => 'mx_Custom_89', 'Value' => 'Yes'];
            }else{
                $fields[]=['SchemaName' => 'mx_Custom_89', 'Value' => 'No'];
            }
            $fields[]=['SchemaName' => 'mx_Custom_73', 'Value' => count($member_agess)];
        }
        if(!empty($member_agess) && !empty($member_agess[0]['deductable'])){
            $fields[]=['SchemaName' => 'mx_Custom_74', 'Value' => $member_agess[0]['deductable']];
        }
        
        $fields[]=[
            'SchemaName' => 'mx_Custom_49',
            'Value' => FRONT_URL."/customerportal?partner=" . encrypt_decrypt_password($lead_details->creditor_id,'E')."&product=". encrypt_decrypt_password($lead_details->plan_id,'E')."&lead_id=". encrypt_decrypt_password($lead_id,'E'),
           // 'Value' => FRONT_URL."/quotes/generate_quote_abc?lead_id=" . encrypt_decrypt_password($lead_id),
        ];
        if(empty( $lead_details->lsq_opportunity_id)){
            $opp_array['LeadDetails']=$opp_lead_details; 
            $opp_array['Opportunity']=$Opportunity; 
            $fields[]=['SchemaName' => 'mx_Custom_27', 'Value' => 'Quote Page'];
            $opp_array['Opportunity']['Fields']=$fields;
            // $opp_array['Opportunity']['Fields'][] = ['SchemaName' => 'CreatedOn', 'Value' => date('Y-m-d H:i:s')];
           
            //$opp_array['Opportunity']['Fields'][] = ['SchemaName' => 'mx_Custom_42', 'Value' => 'Contactable'];

            /*

            $opp_array['Opportunity']['Fields'][] = ['SchemaName' => 'mx_Custom_3', 'Value' => $userJourney->utm_source];

            $opp_array['Opportunity']['Fields'][] =['SchemaName' => 'mx_Custom_20', 'Value' => $userJourney->utm_campaign];
            $opp_array['Opportunity']['Fields'][] =['SchemaName' => 'mx_Custom_25', 'Value' => $userJourney->utm_url];
            $opp_array['Opportunity']['Fields'][] =['SchemaName' => 'mx_Custom_21', 'Value' => $userJourney->utm_medium];*/


            
           /*  $latest_stage = 'Lead Page';
            
            $opp_array['Opportunity']['Fields'][] = [
                'SchemaName' => 'mx_Custom_33', //lead generation date
                'Value' => $latest_stage,
            ];
            $opp_array['Opportunity']['Fields'][] = [
                'SchemaName' => 'mx_Custom_34', //lead generation date
                'Value' => $latest_stage,
            ];*/
        }
        $events =[$event];    
        if($event!='newLead' && empty($lead_details->lsq_lead_id)){
            $events[]='newLead';
        }
        


        if(!empty( $lead_details->lsq_opportunity_id)){

            $opp_array = $Opportunity;
            $opp_array['Fields']=$fields;


            $opp_array['Fields'][] = [
              'SchemaName' => 'mx_Custom_13',
              'Value' => '',
              'Fields' => [
                    ['SchemaName' => 'mx_CustomObject_1', 'Value' => $customer_details->first_name.' '.$customer_details->last_name],
                   
                    ['SchemaName' => 'mx_CustomObject_3', 'Value' => $customer_details->gender],
                    //['SchemaName' => 'mx_CustomObject_4', 'Value' => $lead_details->pincode],
                    ['SchemaName' => 'mx_CustomObject_6', 'Value' => $lead_details->pincode],
                    ['SchemaName' => 'mx_CustomObject_7', 'Value' => $lead_details->city],
                    ['SchemaName' => 'mx_CustomObject_8', 'Value' => $lead_details->state]
                ]
            ];
            
            
        }

        if(empty($members)){
            $members = $CI->db->query("select * from member_ages where lead_id=" . $lead_id)->result_array();

            if(!empty($members)){
                foreach ($members as $key => $value) {
                    $opp_array['Fields'][] = [
                          'SchemaName' => $keys[$key],
                          'Value' => '',
                          'Fields' => [
                            ['SchemaName' => 'mx_CustomObject_4', 'Value' => $value['member_type']],
                            ['SchemaName' => 'mx_CustomObject_3', 'Value' => date('Y-m-d',strtotime($value['dob']))],
                            ['SchemaName' => 'mx_CustomObject_9', 'Value' => $value['member_age']],
                        ]
                    ];
                    
                }
            }
        }else{
            
            foreach ($members as $key => $value) {
                $opp_array['Fields'][] = [
                      'SchemaName' => $keys[$key],
                      'Value' => '',
                      'Fields' => [
                        ['SchemaName' => 'mx_CustomObject_1', 'Value' => $value['policy_member_first_name'].' '.$value['policy_member_last_name']],
                        ['SchemaName' => 'mx_CustomObject_2', 'Value' => $value['policy_member_gender']],
                        ['SchemaName' => 'mx_CustomObject_4', 'Value' => $value['relation_with_proposal']],
                        ['SchemaName' => 'mx_CustomObject_3', 'Value' => date('Y-m-d',strtotime($value['policy_member_dob']))],
                        ['SchemaName' => 'mx_CustomObject_9', 'Value' => $value['policy_member_age'].' '.$value['policy_member_age_in_months']],
                    ]
                ];
                
            }
        }

        if(!empty($payment_details)){
            $opp_array['Fields'][] = [
                  'SchemaName' => 'mx Custom_99',
                  'Value' => '',
                  'Fields' => [
                    ['SchemaName' => 'mx_CustomObject_1', 'Value' => $payment_details->premium],
                    ['SchemaName' => 'mx_CustomObject_4', 'Value' => $payment_details->premium],
                    
                ]
            ];
            $fields[]=['SchemaName' => 'mx_Custom_68', 'Value' => $payment_details->transaction_date];
        }

        foreach ($events as $key => $value) {
            switch ($value) {
                case 'newLead':
                    insert_application_log($lead_id, 'lsq_lead_create', json_encode($lead_array), "", 12);
                    $insert_id = $CI
                    ->db
                    ->insert_id();
                    $data = curlLsqFunction(LSQ_LEAD_CREATE_API,$lead_array,false,'application/json');
                    

                    $lead_res = json_decode($data,true);
                    $request_arr = ["response_data" => $data];
                    $CI->db->where("log_id", $insert_id);
                    $CI->db->update("application_logs", $request_arr);
                    if($lead_res['Status']=='Success'){
                        $CI->db->where("lead_id", $lead_id);
                        $CI->db->update("lead_details", ['lsq_lead_id'=>$lead_res['Message']['Id']]);
                    }

                    
                    insert_application_log($lead_id, 'lsq_opp_create', json_encode($opp_array), "", 12);
                    $insert_id = $CI
                    ->db
                    ->insert_id();
                    $opp_data = curlLsqFunction(LSQ_OPPO_CREATE_API,$opp_array,false,'application/json');

                    $opp_res = json_decode($opp_data,true);
                    $opportunity_id = !empty($opp_res['CreatedOpportunityId'])?$opp_res['CreatedOpportunityId']:  $opp_res['ConflictedOpportunityId'];
                    $request_arr = ["response_data" => $opp_data];
                    $CI->db->where("log_id", $insert_id);
                    $CI->db->update("application_logs", $request_arr);
                    if($lead_res['Status']=='Success'){
                        $CI->db->where("lead_id", $lead_id);
                        $CI->db->update("lead_details", ['lsq_opportunity_id'=>$opportunity_id]);
                    }
                    $best_state = 'Input Page';


                    
                    /*
                    $opp_array['LeadDetails'] = array(
                        ['Attribute' => 'Phone', 'Value' => $user->mobile_no],
                        ['Attribute' => 'SearchBy', 'Value' => 'Phone']

                    );*/
                    // code...
                    break;
                case 'updateLead':
                      $best_state = 'Input Page';
                    break;
                case 'inputPincode':
                    // code...
                    break;
                
                case 'inputSumInsuredType':
                    $Index = array_search("mx_Custom_49", array_column($fields,'SchemaName'));
                    $best_state = 'Quote Page';
                    $opp_array['Fields'][$Index]['Value'] = FRONT_URL."/quotes?lead_id=". encrypt_decrypt_password($lead_id,'E'); 
                    break;
                case 'inputMembers':
                     $best_state = 'Input Page';
                    break;
                case 'productSelected':
                    $best_state = 'Quote Page';
                    $Index = array_search("mx_Custom_49", array_column($fields,'SchemaName'));
                    $opp_array['Fields'][$Index]['Value'] = FRONT_URL."/generate_quote_abc?lead_id=". encrypt_decrypt_password($lead_id,'E'); 
                    break;
                case 'updateMembers':
                        $best_state = 'Quote Page';
                        $Index = array_search("mx_Custom_49", array_column($fields,'SchemaName'));
                        $opp_array['Fields'][$Index]['Value'] = FRONT_URL."/quotes/generate_quote_abc?lead_id=". encrypt_decrypt_password($lead_id,'E');
                        $dob= true; 
                    break;
                case 'quoteFinal':
                        $best_state = 'Proposal Page';

                        $Index = array_search("mx_Custom_49", array_column($fields,'SchemaName'));
                        $opp_array['Fields'][$Index]['Value'] = FRONT_URL."/quotes/generate_proposal?lead_id=". encrypt_decrypt_password($lead_id,'E');
                        $dob=true;
                    break;
                case 'proposerUpdate':
                        $best_state = 'Proposal Page';
                        $Index = array_search("mx_Custom_49", array_column($fields,'SchemaName'));
                        $opp_array['Fields'][$Index]['Value'] = FRONT_URL."/quotes/generate_proposal?lead_id=". encrypt_decrypt_password($lead_id,'E');
                        $dob=true;
                    break;
                
                case 'updateNominee':
                        $best_state = 'Summary Page';

                        $Index = array_search("mx_Custom_49", array_column($fields,'SchemaName'));

                        $opp_array['Fields'][$Index]['Value'] = FRONT_URL."/quotes/proposal_summary?lead_id=". encrypt_decrypt_password($lead_id,'E');
                        $dob =true;
                    break;
                
                case 'summaryPage':
                       $best_state = 'Summary Page';
                       $dob=true;
                    break;
                
                case 'paymentPage':
                      $best_state = 'Payment Page';

                      $Index = array_search("mx_Custom_49", array_column($fields,'SchemaName'));

                      $opp_array['Fields'][$Index]['Value'] = FRONT_URL."/quotes/redirect_to_pg?lead_id=". encrypt_decrypt_password($lead_id,'E');
                      $dob=true;
                    break;
                
                case 'paymentSuccessPage':
                      $best_state = 'Payment Done';

                      $Index = array_search("mx_Custom_49", array_column($fields,'SchemaName'));

                      $opp_array['Fields'][$Index]['Value'] = FRONT_URL."/quotes/success_view/".encrypt_decrypt_password($lead_id,'E')."?lead_id=". encrypt_decrypt_password($lead_id,'E');
                      $dob = true;
                    break;
                
                case 'paymentFailedPage':
                      $best_state = 'Payment Failed';

                      $Index = array_search("mx_Custom_49", array_column($fields,'SchemaName'));

                      $opp_array['Fields'][$Index]['Value'] = FRONT_URL."/quotes/success_view/".encrypt_decrypt_password($lead_id,'E')."?lead_id=". encrypt_decrypt_password($lead_id,'E');
                      $dob = true;
                    break;
                
                case 'policyFailedPage':
                     $best_state = 'Policy Failed';
                     $Index = array_search("mx_Custom_49", array_column($fields,'SchemaName'));
                     $opp_array['Fields'][$Index]['Value'] = FRONT_URL."/quotes/success_view/".encrypt_decrypt_password($lead_id,'E')."?lead_id=". encrypt_decrypt_password($lead_id,'E');
                     $dob = true;
                    break;
                
                case 'policySuccessPage':
                      $best_state = 'Won'; 
                      $Index = array_search("Status", array_column($fields,'SchemaName'));
                      $opp_array['Fields'][$Index]['Value'] = 'Closed'; 

                      $Index = array_search("mx_Custom_49", array_column($fields,'SchemaName'));

                      $opp_array['Fields'][$Index]['Value'] = FRONT_URL."/quotes/success_view/".encrypt_decrypt_password($lead_id,'E')."?lead_id=". encrypt_decrypt_password($lead_id,'E');
                      $dob = true;
                    break;
                
                default:
                    // code...
                    break;
            }
        }
        $best_state_array = [
            'Input Page' =>0,
            'Quote Page'=>1,
            'Proposal Page'=>2,
            'Summary Page'=>3,
            'Payment Page'=>4,
            'Payment Done'=>5,
            'Payment Failed'=>6,
            'Policy Failed'=>7,
            'Policy Issued'=>8,
        ];

        if((!empty($lead_details->lsq_best_state) && $best_state_array[$best_state]>$best_state_array[$lead_details->lsq_best_state]) || empty($lead_details->lsq_best_state)){
            $CI->db->where("lead_id", $lead_id);
            $CI->db->update("lead_details", ['lsq_best_state'=>$best_state]);
            $best_stage = $best_state;

        }else{
            $best_stage = $lead_details->lsq_best_state;
        }
        
        
        if(!empty($lead_details->lsq_opportunity_id)){
            if($dob == true){
                $fields[]=['SchemaName' => 'mx_CustomObject_66', 'Value' => date('Y-m-d',strtotime($customer_details->dob))];
                $fields[]=['SchemaName' => 'mx_CustomObject_93', 'Value' => date('Y-m-d',strtotime($customer_details->dob))];
                $Index = array_search("mx_Custom_13", array_column($fields,'SchemaName'));
                $opp_array['Fields'][$Index]['Fields'][]=['SchemaName' => 'mx_CustomObject_2', 'Value' => date('Y-m-d',strtotime($customer_details->dob))];
            }
            if(!empty($api_proposal_response) && !empty($api_proposal_response->certificate_number)){
                $Index = array_search("Status", array_column($fields,'SchemaName'));
                $opp_array['Fields'][$Index]['Value'] = 'Closed'; 
                $opp_array['Fields'][]=['SchemaName' => 'mx_Custom_27', 'Value' => 'Won'];
                $opp_array['Fields'][]=['SchemaName' => 'mx_Custom_28', 'Value' => 'Won'];

            }else{
                $opp_array['Fields'][]=['SchemaName' => 'mx_Custom_27', 'Value' => $best_state];
                $opp_array['Fields'][]=['SchemaName' => 'mx_Custom_28', 'Value' => $best_stage];
            }
            $opp_array['ProspectOpportunityId'] = $lead_details->lsq_opportunity_id;
            

            insert_application_log($lead_id, 'lsq_opp_update', json_encode($opp_array), "", 12);
            $insert_id = $CI
            ->db
            ->insert_id();
            $opp_data = curlLsqFunction(LSQ_OPPO_UPDATE_API,$opp_array,false,'application/json');

            $opp_res = json_decode($opp_data,true);
            $request_arr = ["response_data" => $opp_data];
            $CI->db->where("log_id", $insert_id);
            $CI->db->update("application_logs", $request_arr);
        }    
        
        
    }

}