<?php

/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('AppController', 'Controller');

App::uses('ConnectionManager', 'Model');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class UserCredentialsController extends AppController {

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'UserCredentials';
    public $layout = 'default';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('MobileUserCredentials', 'Units', 'UserCredentials', 'CompanyContactInfo', 'EmployeeDetails', 'CentralUserCredentials');
    public $components = array('DatatablesManagement', 'MasterdataManagement');

    /*
     * Dashboard landing view
     */

    public function index() {
        $this->layout = FALSE;

        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $plan=$this->UserCredentials->query('SELECT plan FROM comp_contact_info');
        $plan=isset($plan['0']['comp_contact_info']['plan'])?$plan['0']['comp_contact_info']['plan']:'';
		 $this->set('plan',$plan);
    }

    public function getusers() {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        
        $arr_request_data = $this->request->query;
//debug($arr_request_data);
        $filter_condition = array();
        if (isset($arr_request_data['username'])) {
            $searchkey = $arr_request_data['username'];
            // $filter_condition[] = 'first_name   LIKE "%' . $searchkey . '%"';
            //edited by megha combined first name and last name
            $con="CONCAT(first_name,' ', last_name)";
            //search emp company id added by ***ARUL P DAS on 19/12/2019
            $filter_condition[] = ' ('.$con.'   LIKE "%' . $searchkey . '%" or EmployeeProfessionalDetails.emp_company_id LIKE "%' . $searchkey . '%" ) ';
        } else {
            $filter_condition[] = '';
        }
           //commented by megha on 16_07_19 show all employees
//        if($this->Session->read('emp_fkey')){
//            $emp_pkey = $this->Session->read('emp_fkey');
//            $filter_condition[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
//        }
        $joins  = array(
        array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        $arr_users = $this->EmployeeDetails->find('all', array(
            'fields' => 'emp_pkey,first_name,concat(first_name," ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as full_name ',
            'joins' => $joins,
            'order'=> array("first_name ASC"),
            'conditions' => array(
                'status'=>1,
                $filter_condition
            )
                )
        );
        //  debug($arr_users);
        $arr_filterresult = array();
        foreach ($arr_users as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? array_merge($val['EmployeeDetails'], $val[0]) : array();
        }
        //  debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    public function listCredentials()
    {


        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $resp_data = array();

        $this->autoRender = false;
        $arr_data = $this->request->data;
        $limit = $arr_data['rows'];
        $page = $arr_data['page'];
        $offset = ($page - 1) * $limit;

        $sort = isset($arr_data['sort']) ? strval($arr_data['sort']) : 'emp_fkey';
        if ($sort == 'parent') {
            $sort = 'concat(EmployeeDetails.first_name," ",EmployeeDetails.last_name)';
        }
        $order = isset($arr_data['order']) ? strval($arr_data['order']) : 'DESC';

        $user_fkey = isset($arr_data['user_fkey']) ? $arr_data['user_fkey'] : '';
        // debug($user_fkey);
        //debug($arr_useraccess);
        $where = array();
        if ($user_fkey == '') {
            $where[] = 'EmployeeDetails.status = 1 ';
        } else {
            $where[] = 'EmployeeDetails.status = 1 and `UserCredentials`.`emp_fkey` = ' . $user_fkey;
        }
        //        if(isset($arr_data['emp'])){
        //            $where[] = "(EmployeeDetails.first_name like '%".$arr_data['emp']."%' OR EmployeeProfessionalDetails.emp_company_id like '%".$arr_data['emp']."%'  OR EmployeeDetails.last_name like '%".$arr_data['emp']."%' OR EmployeeDetails.emp_id like '%".$arr_data['emp']."%')";
        //        }
        //User ID in search query ADDED BY **ARUL P DAS
        if (isset($arr_data['emp'])) {
            $where[] = "(EmployeeDetails.first_name like '%" . $arr_data['emp'] . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $arr_data['emp'] . "%'  OR EmployeeDetails.last_name like '%" . $arr_data['emp'] . "%')";
            //          OR EmployeeDetails.emp_id like '%".$arr_data['emp']."%'   OR UserCredentials.user_id like '%".$arr_data['emp']."%'
        }

        // Edited by Akshay on 6-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->UserCredentials->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $where[] = "EmployeeDetails.branch_code = '$is_ho ' ";
            }
        }
        // End

        $totalcount = $this->UserCredentials->find(
            "count",
            array(
                'joins' => array(
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmployeeDetails',
                        'type' => 'left',
                        'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
                    ),
                    array(
                        'table' => 'emp_proff',
                        'alias' => 'EmployeeProfessionalDetails',
                        'type' => 'left',
                        'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                    )
                ),
                'conditions' => $where
            )
        );

        //$arr_menu = $this->UserCredentials->query('select UserCredentials.*,EmployeeDetails.first_name,EmployeeDetails.last_name from user_credentials as UserCredentials join emp_details as EmployeeDetails');
        //added by megha on 27/09/2019 'EmployeeDetails.mobile_no' field 1
        $arr_menu = $this->UserCredentials->find('all', array(
            'joins' => array(
                array(
                    'table' => 'emp_details',
                    'alias' => 'EmployeeDetails',
                    'type' => 'left',
                    'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
                ),
                array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'left',
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                )
            ),
            'fields' => array('EmployeeDetails.first_name', 'EmployeeDetails.last_name', 'EmployeeDetails.mobile_no', 'UserCredentials.*', 'EmployeeProfessionalDetails.emp_company_id'),
            'order' => array($sort => $order),
            'conditions' => $where,
            'offset' => $offset,
            'limit' => intval($limit)
        ));
        $rows = array();
        //debug($arr_menu);
        //die();
        foreach ($arr_menu as $key => $val) {

            //$val['UserCredentials']['ss']=$emp[0]['EmployeeDetails']['first_name'];

            $val['UserCredentials']['parent'] = $val['EmployeeDetails']['first_name'] . ' ' . $val['EmployeeDetails']['last_name'] . ' - ' . $val['EmployeeProfessionalDetails']['emp_company_id'];
            //added by megha on 27/09/2019 'EmployeeDetails.mobile_no' field 2
            $val['UserCredentials']['phone'] = $val['EmployeeDetails']['mobile_no'];
            $rows[] = $val['UserCredentials'];
        }
        //debug($arr_menu);
        $resp_data["total"] = $totalcount;
        $resp_data["rows"] = $rows;




        echo json_encode($resp_data);
    }

    public function form($id = 0) {

        $this->layout = null;

        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $data['id'] = 0;
        $data['branch_name'] = "";
        $data['address'] = "";
        $data['state'] = "";
        $data['pincode'] = "";
        $data['city'] = "";
        //  debug($_REQUEST['id']);
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        if (isset($_REQUEST['id']) && $_REQUEST['id'] != 0) {
            $joins = array(
                array(
                    "table"=>"mob_user_credentials",
                    "type"=>"left",
                    "alias"=>"Mob",
                    "conditions"=>array("Mob.user_id = UserCredentials.user_id")
                )
            );
            $data_db = $this->UserCredentials->find("first", array("joins"=>$joins,"fields"=>array("UserCredentials.*,Mob.*"),"conditions" => array("emp_fkey" => $_REQUEST['id'])));

            $data = array_merge($data_db['UserCredentials'],$data_db['Mob']);
        }

        $this->set("data_db", $data_db); //debug($data_db);
    }

    public function bulkUpload($id = 0) {

        $this->layout = null;

        $this->UserCredentials->useDbConfig = $this->Session->read('ds');

        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);
    }

    public function deleteuser($bank_id = 0) {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($bank_id != 0) {
            $this->EmployeeDetails->query("UPDATE `emp_details` SET `status` = '0' WHERE `emp_pkey` = '$bank_id' ");
            echo json_encode(array('msg' => 'User deletion successfull!'));
        } else {
            echo json_encode(array('msg' => 'User deletion failed!'));
        }
    }
	
	public function resetmobiles(){
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $arr_form_data = $this->request->data;
        $emp_id = $arr_form_data['user_pkey'];
        $update = $this->UserCredentials->query("update mob_user_credentials set securitycode = '',macid = '',token = 'N', imei= '',token = 'Y' where user_id = '$emp_id' ");
        echo json_encode(array('msg' => 'User Mobile details reset successfully Updated'));
    }

    public function companyHistory_add($payloadData) {
        $this->autoRender = false;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://myportalapi.mypayrollmaster.online/thirdpartyapi/companyHistory/add',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payloadData),
            CURLOPT_HTTPHEADER => array(
                'username: profileadmin',
                'password: admin&*()',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function empDataThirdparty_update($data) {
        $this->autoRender = false;
        $curl = curl_init();

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $userId = $data['employeeId'];

        $fields = 'EmployeeDetails.*, UserCredentials.avatar, emp_pkey,EmployeeProfessionalDetails.emp_company_id,EmployeeDetails.first_name,EmployeeDetails.last_name,EmployeeProfessionalDetails.designation,DATE_FORMAT(EmployeeProfessionalDetails.joining_date, "%d/%m/%Y") AS joining_date,mobile_no';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'user_credentials',
                'alias' => 'UserCredentials',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
            )
        );

        $conditions[] = "UserCredentials.user_id = '$userId' ";

        $empdetailData = $this->EmployeeDetails->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            "conditions" => $conditions,
        ));

        // debug($empdetailData);

        $payloadData = array(
            "firstName" => $empdetailData['0']['EmployeeDetails']['first_name'],
            "profileId" => $data['profileId'],
            "middleName" => " ",
            "lastName" => ($empdetailData['0']['EmployeeDetails']['last_name'] != "") ? $empdetailData['0']['EmployeeDetails']['last_name']: '-',
            "gender" => $empdetailData['0']['EmployeeDetails']['classification'],
            "address" => $empdetailData['0']['EmployeeDetails']['address'],
            "city" => $empdetailData['0']['EmployeeDetails']['city'],
            "state" => $empdetailData['0']['EmployeeDetails']['state'],
            "nationality" => 1,
            "country" => 2,
            "pincode" => $empdetailData['0']['EmployeeDetails']['pincode'],
            "mobileNo" => $empdetailData['0']['EmployeeDetails']['mobile_no'],
            "email" => ($empdetailData['0']['EmployeeDetails']['email'] != "") ? $empdetailData['0']['EmployeeDetails']['email']: '',
            "maritalStatus" => $empdetailData['0']['EmployeeDetails']['maritual_status'],
            "education" => $empdetailData['0']['EmployeeDetails']['education'],
            "dateOfBirth" => date("d-m-Y", strtotime($empdetailData['0']['EmployeeDetails']['date_of_birth'])),
            "bankName" => $empdetailData['0']['EmployeeDetails']['bank_name'],
            "bankBranchName" => $empdetailData['0']['EmployeeDetails']['branch_name'],
            "branchAddress" => $empdetailData['0']['EmployeeDetails']['branch_address'],
            "ifscCode" => $empdetailData['0']['EmployeeDetails']['ifsc_code'],
            "nameAsPerBank" => $empdetailData['0']['EmployeeDetails']['name_as_per_bank'],
            "accountNo" => $empdetailData['0']['EmployeeDetails']['account_no'],
            "pf" => $empdetailData['0']['EmployeeDetails']['pf'],
            "companyPf" => $empdetailData['0']['EmployeeDetails']['company_pf'],
            "esiDispensary" => $empdetailData['0']['EmployeeDetails']['esi_dispensary'],
            "esi" => $empdetailData['0']['EmployeeDetails']['esi'],
            "idCard" => $empdetailData['0']['EmployeeDetails']['id_card'],
            "guardian" => $empdetailData['0']['EmployeeDetails']['guradian'],
            "relationGuardian" => $empdetailData['0']['EmployeeDetails']['relation_guardian'],
            "panNo" => $empdetailData['0']['EmployeeDetails']['pan_no'],
            "nameAsOnPan" => $empdetailData['0']['EmployeeDetails']['name_as_on_pan'],
            "nameAsOnAadhaar" => $empdetailData['0']['EmployeeDetails']['name_as_on_aadhaar'],
            "previousMemberId" => $empdetailData['0']['EmployeeDetails']['previous_member_id'],
            "blood" => $empdetailData['0']['EmployeeDetails']['blood'],
            "hearing" => $empdetailData['0']['EmployeeDetails']['hearing'],
            "visual" => $empdetailData['0']['EmployeeDetails']['visual'],
            "physicalHandicap" => $empdetailData['0']['EmployeeDetails']['physical_handicap'],
            "locomotive" => $empdetailData['0']['EmployeeDetails']['locomotive'],
            "internationalWorker" => $empdetailData['0']['EmployeeDetails']['international_worker'],
        );

        // debug(json_encode($payloadData));

        // $payloadData = array(
        //     "firstName" => "Mithun",
        //     "profileId" => "SA103",
        //     "middleName" => "Raj",
        //     "lastName" => "S",
        //     "gender" => "M",
        //     "address" => "Janatha Road",
        //     "city" => "Kochi",
        //     "state" => "Kerala",
        //     "nationality" => 1,
        //     "country" => 2,
        //     "pincode" => "682030",
        //     "mobileNo" => "9847225998",
        //     "email" => "mithunraj2006@gmail.com",
        //     "maritalStatus" => "Un",
        //     "education" => "BTech",
        //     "dateOfBirth" => "01-09-1982",
        //     "bankName" => "SBI",
        //     "bankBranchName" => "Kaloor",
        //     "branchAddress" => "Kaloor Branch",
        //     "ifscCode" => "SBIN0054",
        //     "nameAsPerBank" => "Mit Bank Name",
        //     "accountNo" => "32458799412",
        //     "pf" => "EPF013",
        //     "companyPf" => "CPF4545",
        //     "esiDispensary" => "esiDis442",
        //     "esi" => "ESIID024",
        //     "idCard" => "EMP3459",
        //     "guardian" => "GUA2",
        //     "relationGuardian" => "Friend",
        //     "panNo" => "ARSi49555",
        //     "nameAsOnPan" => "Mithun Su Raj",
        //     "nameAsOnAadhaar" => "Mithun S Raj",
        //     "previousMemberId" => "PMID222",
        //     "blood" => "A+",
        //     "hearing" => "Normal",
        //     "visual" => "20-20",
        //     "physicalHandicap" => "Functional",
        //     "locomotive" => "Loco",
        //     "internationalWorker" => "Yes"
        // );

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://myportalapi.mypayrollmaster.online/thirdpartyapi/empDetails/update',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payloadData),
            CURLOPT_HTTPHEADER => array(
                'username: profileadmin',
                'password: admin&*()',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function saveBulkAccess() {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        
        $arr_form_data = $this->request->data;
        //   debug($arr_form_data);
        $branchCode = $arr_form_data['filterby_branch'];
        $password = $arr_form_data['password'];
        $hased_password = Security::hash($password, null, true);

        $company_key = $this->Session->read('company_key');
        $arr_central_control = $this->UserCredentials->find('first', array('fields' => array('company_code')));
        $str_company_code = isset($arr_central_control['UserCredentials']['company_code']) ? $arr_central_control['UserCredentials']['company_code'] : '';

        $this->UserCredentials->useDbConfig = $this->Session->read('ds');

        $callProcudureAccess = $this->UserCredentials->query("SELECT `user_access_firstime_only`('" . $str_company_code . "', '" . $branchCode . "', 'null', '" . $hased_password . "', '" . $password . "') as response; ");

        $response = isset($callProcudureAccess['0']['0']['response']) ? $callProcudureAccess['0']['0']['response']: 'FALSE';

        if($response == 'SUCCESS') {
            $addressess = array();
            // $usersAccessAllocated = $this->UserCredentials->query("select email,start_date from user_credentials uc ,mob_user_credentials muc where uc.user_id=muc.user_id and uc.access_allowed='Y' and reset_login_flag='Y' and date_format(start_date,'%Y-%m-%d')=current_date and email is not null  ");
            $query = "select  email,start_date
                from user_credentials uc ,mob_user_credentials muc
                where uc.user_id=muc.user_id and uc.access_allowed='Y' and reset_login_flag='Y' 
                and  date_format(start_date,'%Y-%m-%d')=current_date
                and length(email)>3 and emp_fkey in (select emp_pkey from emp_details where branch_code='" . $branchCode . "')";
            $usersAccessAllocated = $this->UserCredentials->query($query);
             //debug($usersAccessAllocated);
            foreach ($usersAccessAllocated as $key => $value) {
                # code...
                // if valid mail
                $addressess[] = $value['uc']['email'];
            }
            $this->sendAccessMail($password, $addressess);
            
            // ob_clean();
            flush();
            echo json_encode(array('msg' => 'User Credentials saved successfully', 'success' => 1));
        } else {
            echo json_encode(array('msg' => 'User Credentials saving failed', 'success' => false));
        }
        
        //$this->render();
        
    }

    public function sendAccessMail($password1 = '', $addressess)
    {
        $this->autoRender = FALSE;
        // var_dump($password1);
        $comp = $this->EmployeeDetails->query("select subdomain,business_name from comp_contact_info");

        $subdomain = isset($comp['0']['comp_contact_info']['subdomain'])? $comp['0']['comp_contact_info']['subdomain'] :'';
      if($subdomain !='mbcet'){
          $url = 'https://login.mypayrollmaster.online/';
      }else{
          $url = 'https://mbcet.mypayrollmaster.online/';
      }
        App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));

        $mail = new PHPMailer();

        $mail->IsSMTP();
		 
		//SMTP DEBUGGING - Output connection log files
		$mail->SMTPDebug = false;
        // Debugoutput

		$mail->SMTPAuth   = true;
        $mail->Host       = 'smtp.zoho.in';
        $mail->Port       = 587;
        $mail->Username   = 'noreply@mypayrollmaster.online';
        $mail->Password   = '@Password90#';
	$mail->SetFrom("noreply@mypayrollmaster.online", 'My Payroll Master');
        $mail->AddReplyTo("noreply@mypayrollmaster.online",  'My Payroll Master');
        $mail->addAddress("noreply@mypayrollmaster.online",'My Payroll Master User'); 
        $mail->addBCC('projects@greatleap.tech'); 
        $mail->Subject    = "My Payroll Master - Access Informations";
        $mail->AltBody    = '<!DOCTYPE html>';

        $mail->MsgHTML('<html><div style="font-family:HelveticaNeue-Light,Arial,sans-serif;background-color:#eeeeee">
                <table align="center" width="60%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
                <tbody>
                    <tr>
                        <td>
                            <table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="width:100%!important">
                            <tbody>
                                <tr>
                                    <td>
                                        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" >
                                        <tbody>
                                            <tr>
                                                <td colspan="3" height="40" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="padding:0;margin:0;font-size:0;line-height:0">
                                                    <table width="690" align="center" border="0" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr>
                                                            <td width="30"></td>
                                                            <td align="left" valign="middle" style="padding:0;margin:0;font-size:0;line-height:0">
                                                            <a href="'.$url.'" target="_blank"><img style="height: 40px;" src="https://login.mypayrollmaster.online/newlogin/img/logo.png" alt="My Payroll Master" ></a></td>
                                                            <td width="30"></td>
                                                        </tr>
                                                    </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" align="center">
                                                    <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="3" height="60"></td></tr><tr style="background-color: #2e7695;
                                                            COLOR: white;
                                                            height: 140px;"><td width="25"></td>
                                                            <td align="center">
                                                                <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:35px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Welcome to <font style="color:#fff;"> My Payroll Master</font> </h1>
                                                            </td>
                                                            <td width="25"></td>
                                                            
                                                        </tr>
                                                        
                                                </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        
                                        <tr bgcolor="#ffffff">
                                        <td colspan="3" align="center">
                                        <table width="590" align="center" border="0" cellspacing="0" cellpadding="0">
                                                <tbody>
                                                <tr>
                                                        <td colspan="4" height="30">&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            
                                                            <div style="color:#404040;font-size:13px;line-height:16px;font-weight:lighter;padding:0;margin:0">
                                                                    Hi,&nbsp;<br><br>
                                                                    Your Access to the My Payroll Master has been set.  Please use the below credentials to login to My Payroll Master - Employee Self Service portal or Mob Application.</div>
                                                        </td>
                                                    </tr>
                                                
                                        </tbody>
                                        </table>
                                                <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                                                <tbody>
                                                <tr>
                                                        <td colspan="4" height="30">&nbsp;</td>
                                                    </tr>
                                                    
                                                    
                                                    <tr>
                                                        <td width="130" align="right" valign="top">
                                                            <h3 style="color:#404040;font-size:13px;line-height:16px;font-weight:bold;padding:0;margin:0">Username</h3></td>
                                                        <td width="30"></td>
                                                        <td align="left" valign="middle">
                                                            
                                                            <div style="color:#404040;font-size:13px;line-height:16px;font-weight:lighter;padding:0;margin:0">Will be your registered email Address</div>
                                                                <div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                                        </td>
                                                        <td width="30"></td>
                                                    </tr>
                                                    
                                                    <tr>
                                                        <td width="130" align="right" valign="top">
                                                            <h3 style="color:#404040;font-size:13px;line-height:16px;font-weight:bold;padding:0;margin:0">Password</h3></td>
                                                        <td width="30"></td>
                                                        <td align="left" valign="middle">
                                                            
                                                            <div style="color:#404040;font-size:13px;line-height:16px;font-weight:lighter;padding:0;margin:0">'.$password1.'</div>
                                                            <div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                                        </td>
                                                        <td width="30"></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="130" align="right" valign="top">
                                                            <h3 style="color:#404040;font-size:13px;line-height:16px;font-weight:bold;padding:0;margin:0">URL</h3></td>
                                                        <td width="30"></td>
                                                        <td align="left" valign="middle">
                                                            
                                                            <div style="color:#404040;font-size:13px;line-height:16px;font-weight:lighter;padding:0;margin:0">'.$url.'</div>
                                                            <div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                                        </td>
                                                        <td width="30"></td>
                                                    </tr>
                                                    
                                                </tbody>
                                                </table>
                                                <table width="590" align="center" border="0" cellspacing="0" cellpadding="0">
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <h2 style="color:#404040;font-size:22px;font-weight:bold;line-height:26px;padding:0;margin:0">&nbsp;</h2>
                                                            <div style="color:#404040;font-size:13px;line-height:16px;font-weight:lighter;padding:0;margin:0">
                                                                    You may apply and approve leaves, view your reports and payslips, mark your attendance using your Employee Self Service login. 
                                                                    You can download the mobile application from Playstore/Appstore by searching My Payroll Master. 
                                                                    You may also find more details about My Payroll Master by visiting <a href="http://mypayrollmaster.online/">mypayrollmaster.online</a>
                                                                    <br><br>Thanks & Regards<br>
                                                                    Team My Payroll Master</div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center">
                                                            <div style="text-align:center;width:100%;padding:40px 0">
                                                                <table align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;padding:0">
                                                                <tbody>
                                                                    <tr>
                                                                        <td align="center" style="margin:0;text-align:center"><a href="'.$url.'" style="font-size:18px;font-family:HelveticaNeue-Light,Arial,sans-serif;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#2e7695;padding:14px 40px;display:block" target="_blank">Login!</a></td>
                                                                    </tr>
                                                                </tbody>
                                                                </table>
                                                            </div>
                                                        </td>
                                                </tr><tr><td>&nbsp;</td>
                                                </tr></tbody></table></td>
                                            <td width="30" bgcolor="#eeeeee"></td>
                                        </tr>
                                        </tbody>
                                        </table>
                                        <table align="center" width="750px" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="width:750px!important">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <table width="630" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
                                                    <tbody>
                                                        <tr><td colspan="2" height="30"></td></tr>
                                                        <tr>
                                                            <td width="360" valign="top">
                                                                <div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">&copy; 2018 My Payroll Master. All rights reserved.</div>
                                                                <div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                                <div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">Made in India</div>
                                                            </td>
                                                            <td align="right" valign="top">
                                                                <span style="line-height:20px;font-size:10px"><a href="https://www.facebook.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/BggPYqAh.png" alt="fb"></a>&nbsp;</span>
                                                                <span style="line-height:20px;font-size:10px"><a href="https://twitter.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/j3NsGLak.png" alt="twit"></a>&nbsp;</span>
                                                                <span style="line-height:20px;font-size:10px"><a href="https://plus.google.com/mypayrollmater" target="_blank"><img src="http://i.imgbox.com/wFyxXQyf.png" alt="g"></a>&nbsp;</span>
                                                            </td>
                                                        </tr>
                                                        <tr><td colspan="2" height="5"></td></tr>
                                                    
                                                    </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
                </table>
            </div></html>');

        foreach ($addressess as $key => $value) {
           // $mail->addAddress($value,'My Payroll Master User');     // Add a recipient
            $mail->addBcc($value,'My Payroll Master User');     // Add a recipient
           // $mail->AddBcc($value, 'My Payroll Master User');
            //debug($value);
        }    
       // die();
		//CHECK IF WE SHOULD SEND EMAIL
		if(!$mail->Send()) {
            //echo 'The mail to '.$to.' failed to send. Check your SMTP settings in config.php<br />';
            return "false";
        } else {
            //echo 'The mail was sent successfully to '.$to.'.<br />';
            return "true";
        }
    }
	
    public function save($bank_id = 0)
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        if ($bank_id == 0) {
            $title = 'Add Bank Details';
        } else {
            $title = 'Edit Bank Details';
        }
        $arr_form_data = $this->request->data;
        //   debug($arr_form_data);
        $this->set('title', $title);
        $userid = $arr_form_data['user_id'];
        $email = $arr_form_data['email'];


        $arr_form_data['user_pkey'] = $arr_form_data['id'];
        if (isset($arr_form_data['status']) && $arr_form_data['status'] == 'Y') {
            $password1 = $arr_form_data['pasword'];
            $arr_form_data['password'] = Security::hash($password1, null, true);
            $arr_form_data['reset_login_flag'] = 'Y';
            $arr_form_data['locked'] = '0';
            $arr_form_data['incorrect_login_attempt'] = '0';
        }
        //edited by megha on 17_5_19
        if ($arr_form_data['locked'] == 0) {
            $arr_form_data['incorrect_login_attempt'] = '0';
        }
        //edited by megha on 17_5_19
        // debug($arr_form_data);
        $this->MobileUserCredentials->useDbConfig = $this->Session->read('ds');
        $arr_mobile_data = array();
        $userid = $arr_mobile_data['user_id'] = $arr_form_data['user_id'];
        $user = $this->MobileUserCredentials->find("first", array("conditions" => array("user_id" => $arr_mobile_data['user_id'])));
        if (count($user) > 0) {
            $arr_mobile_data['user_pkey'] = $user['MobileUserCredentials']['user_pkey'];
        }

        //if ($arr_form_data['pasword'] != '') {
        $first_name = $arr_mobile_data['firstname'] = $arr_form_data['fnam'];
        $last_name = $arr_mobile_data['lastname'] = $arr_form_data['lnam'];
        if (isset($arr_form_data['pasword']) && $arr_form_data['pasword'] != '')
            $password = $arr_mobile_data['password'] = $arr_form_data['pasword'];
        $name = $first_name . ' ' . $last_name;
        $arr_mobile_data['token'] = "Y";
        //}

        if ($arr_form_data['Mobile_Allow'] == 'Y') {
            $arr_form_data['mobileaccess'] = 'Y';
            $arr_mobile_data['locked'] = 'N';
        } else if ($arr_form_data['Mobile_Allow'] == 'N') {
            $arr_form_data['mobileaccess'] = 'N';
            $arr_mobile_data['locked'] = 'Y';
        }
        // Tracking access field -- start added by megha
        //edited by sinsiya on 16-10-2024
        //        if ($arr_form_data['Tracking_Allow'] == 'N') {
        //            $arr_mobile_data['is_track'] = 'N';
        //        }else if ($arr_form_data['Tracking_Allow'] == 'Y') {
        //            $arr_mobile_data['is_track'] = 'Y';
        //        }
        // Tracking access field -- end added by megha
        /*if(isset($arr_form_data['Mobile_Punch_Allow'])){
            if ($arr_form_data['Mobile_Punch_Allow'] == 'Y') {
                $arr_form_data['mobilepunch'] = 'Y';
                $arr_mobile_data['mobilepunch'] = 'Y';
            }else if ($arr_form_data['Mobile_Punch_Allow'] == 'N') {
                $arr_form_data['mobilepunch'] = 'N';
                $arr_mobile_data['mobilepunch'] = 'N';
            }
        }
        
        if(isset($arr_form_data['Mobile_Office_Punch_Allow'])){
            if ($arr_form_data['Mobile_Office_Punch_Allow'] == 'Y') {
                $arr_form_data['officepunch'] = 'Y';
                $arr_mobile_data['officepunch'] = 'Y';
            }else if ($arr_form_data['Mobile_Office_Punch_Allow'] == 'N') {
                $arr_form_data['officepunch'] = 'N';
                $arr_mobile_data['officepunch'] = 'N';
            }
        }*/
        if (isset($arr_form_data['punch_type'])) {

            $arr_form_data['punchtype'] = $arr_form_data['punch_type'];
            $arr_mobile_data['punchtype'] = $arr_form_data['punch_type'];
        }

        $employeedetailsResponses = '';
        // third party API CAlling
        $response_histor_add = json_encode(array("status" => 200));
        if ($arr_form_data['attr2']) {
            $userCredentials_data = $this->UserCredentials->find("first", array('fields' => 'emp_fkey', "conditions" => array("user_id" => $arr_mobile_data['user_id'])));
            $data_db = $this->EmployeeDetails->find("first", array('fields' => 'branch_code, company_code', "conditions" => array("emp_pkey" => $userCredentials_data['UserCredentials']['emp_fkey'])));

            $employeeInfo = $this->UserCredentials->query("select branch,designation,department,joining_date from employee_info WHERE emp_pkey = " . $userCredentials_data['UserCredentials']['emp_fkey']);
            $companyInfo = $this->UserCredentials->query("select * from comp_contact_info WHERE id = 1 ");

            $payloadData = array(
                "branchCode" => $data_db['EmployeeDetails']['branch_code'],
                "companyCode" => $data_db['EmployeeDetails']['company_code'],
                "email" => trim($email),
                "employeeId" => $userid,
                "joiningDate" => isset($employeeInfo['0']['employee_info']['joining_date']) ? date("d-m-Y", strtotime($employeeInfo['0']['employee_info']['joining_date'])) : '',
                "policyName" => "",
                "companyName" => isset($companyInfo['0']['comp_contact_info']['business_name']) ? $companyInfo['0']['comp_contact_info']['business_name'] : '',
                "department" => isset($employeeInfo['0']['employee_info']['department']) ? $employeeInfo['0']['employee_info']['department'] : '',
                "designation" => isset($employeeInfo['0']['employee_info']['designation']) ? $employeeInfo['0']['employee_info']['designation'] : '',
                "profileId" => $arr_form_data['attr2'],
                "status" => 1
            );

            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
            if (isset($password1)) {
                $callProcudureAccess = $this->UserCredentials->query("SELECT `user_access_firstime_only`('" . $data_db['EmployeeDetails']['company_code'] . "', '" . $data_db['EmployeeDetails']['branch_code'] . "', '" . $userCredentials_data['UserCredentials']['emp_fkey'] . "', '" . $arr_form_data['password'] . "', '" . $password1 . "') as response; ");

                $response = isset($callProcudureAccess['0']['0']['response']) ? $callProcudureAccess['0']['0']['response'] : 'FALSE';
            }
            $response_histor_add = $this->companyHistory_add($payloadData);
            if (json_decode($response_histor_add)->status != 200) {
                $arr_form_data['attr2'] = '';
            } else {
                $employeedetailsResponses = $this->empDataThirdparty_update($payloadData);
            }
        }

        // third party API CAlling

        $this->MobileUserCredentials->save($arr_mobile_data);

        $password = '';

        $this->UserCredentials->save($arr_form_data);
        //$curl = curl_init();
        //        curl_setopt_array($curl, array(
        //        CURLOPT_URL => "https://nextcloud.mypayrollmaster.com/ocs/v1.php/cloud/users",
        //        CURLOPT_RETURNTRANSFER => true,
        //        CURLOPT_ENCODING => "",
        //        CURLOPT_MAXREDIRS => 10,
        //        CURLOPT_TIMEOUT => 0,
        //        CURLOPT_FOLLOWLOCATION => true,
        //        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //        CURLOPT_CUSTOMREQUEST => "POST",
        //        CURLOPT_POSTFIELDS => "userid=$userid&password=$password&displayName=$first_name&email=$email",
        //        CURLOPT_HTTPHEADER => array(
        //        "OCS-APIRequest: true",
        //        "Authorization: Basic QXNob2thbjpXZWxjb21lMTM1",
        //        "Content-Type: application/x-www-form-urlencoded",
        //        "Cookie: cookie_test=test; oc_sessionPassphrase=BqXUqmcueFCtCJb3PE0glVctsIsbdjxd988kv%2FbVSNXwWdqO0%2FzD3qOyvcBKhqksJGAFtz%2FIRp03cVC2ek%2FoRishlZm21BH1DhY5Xf5lvN4OPGa5%2Fyoq2sqMVHD8koDR; __Host-nc_sameSiteCookielax=true; __Host-nc_sameSiteCookiestrict=true; ocrybqbu58pf=3kml6vs9i3eppcg1hhr1c5ig7r"
        //        ),
        //        ));
        //        $response = curl_exec($curl);
        //        curl_close($curl);
        //echo $response;
        /* $fromemail = "My Payroll Master <info@mypayrollmaster.com>";
          $ms='mypayrollmaster.com';
          $headers = "From: My Payroll Master Password Resets <info@mypayrollmaster.com>\r\n";
          $headers .= "Reply-To: ". strip_tags($fromemail) . "\r\n";
          $headers .= "MIME-Version: 1.0\r\n";
          $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
          $headers .= "CC: info@mypayrollmaster.com\r\n";
          $subject = "Your New My Payroll Master Password";
          $message = '<p><h1><strong>HI ! <p>Welcome to My Projects Master<?strong></h1></p><br/>'
          .'Your Login details are Userid='.$userid.' and Password = '.$password1.' </a><br/>'
          .'<pre>or Visit this Link</pre><br/>'
          .'<p><a href="'.$ms.'">'.$ms.'</a></p>';

          if(mail($email,$subject,$message,$headers)){
          $success = true;
          } */

        if (isset($password1)) {
            //$mail = $this->sendpasswordemail($email, $userid, $password1,$name);   
            $mail = $this->sendpasswordemail($email, $userid, $password1, $name);
        }
        //  echo json_encode(array('msg' => 'User Credentials saved successfully'));

        echo json_encode(array('msg' => 'User Credentials saved successfully', 'thirdPartyApi' => json_decode($response_histor_add), 'employeeAddAPi' => json_decode($employeedetailsResponses)));
        //$this->render();

    }

//      public function save() {
//        $this->autoRender = false;
//        $arr_form_data = $this->request->data;
//        $org_info=$this->Organization->find("first");
//        $arr_form_data['organization_id'] = $org_info['Organization']['organization_id']; 
//        $this->Bank->save($arr_form_data);
//        echo json_encode(array('msg'=>'Bank Details saved successfully'));
//    }

    public function sendpasswordemail($email, $userid, $password1,$name) {
        $this->autoRender = FALSE;
		 $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
      $comp = $this->EmployeeDetails->query("select subdomain,business_name from comp_contact_info");
 $subdomain = isset($comp['0']['comp_contact_info']['subdomain'])? $comp['0']['comp_contact_info']['subdomain'] :'';
      if($subdomain !='mbcet'){
          $url = 'https://login.mypayrollmaster.online/';
      }else{
          $url = 'https://mbcet.mypayrollmaster.online/';
      }
//         $url = isset($comp['0']['comp_contact_info']['subdomain'])? 'href="'.$comp['0']['comp_contact_info']['url'].'"': 'href="login.mypayrollmaster.com"';
			$companyname = isset($comp['0']['comp_contact_info']['business_name'])? $comp['0']['comp_contact_info']['business_name'] :'Your Company';
			//$user_name = $this->EmployeeDetails->query("select first_name,last_name from user_credentials where user_id = '$userid' ");
			//$name = $user_name['0']['user_credentials']['first_name'].' '.$user_name['0']['user_credentials']['last_name'];
	try {
            App::import('Vendor', 'PHPMailer', array('file'=>'PHPMailer/PHPMailerAutoload.php'));
            //$mail = new PHPMailer;
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = false;                               // Enable verbose debug output
            $mail->isSMTP();                                        // Set mailer to use SMTP
            $mail->Host = 'smtp.zoho.in'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
//            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
//            $mail->Password = 'welcome123';                           // SMTP password
            //$mail->Username = 'noreply@mypayrollmaster.com';                 // SMTP username
            //$mail->Password = 'mypayrollmaster123'; 
            $mail->Username = 'noreply@myparollmaster.online';                 // SMTP username
            $mail->Password = '@Password90#'; 
            
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to
            //$mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->setFrom('noreply@mypayrollmaster.online', 'My Payroll Master');
            $mail->addAddress($email);     // Add a recipient
            $mail->addBCC($email); 
            $mail->addBCC('projects@greatleap.tech'); 
            $mail->addReplyTo('noreply@mypayrollmaster.online', 'Support');
            $mail->isHTML(true);                                  // Set email format to HTML
	    $mail->AddEmbeddedImage('https://login.mypayrollmaster.online/newlogin/img/logo.png', 'MPM');
            $mail->Subject  = "Your New My Payroll Master Password";
            $mail->MsgHTML('<html><div style="font-family:HelveticaNeue-Light,Arial,sans-serif;background-color:#eeeeee">
	<table align="center" width="60%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
    <tbody>
        <tr>
        	<td>
                <table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="width:100%!important">
                <tbody>
                	<tr>
                    	<td>
                			<table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" >
                            <tbody>
                            	<tr>
                                    <td colspan="3" height="40" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="padding:0;margin:0;font-size:0;line-height:0">
                                        <table width="690" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td width="30"></td>
                                                <td align="left" valign="middle" style="padding:0;margin:0;font-size:0;line-height:0">
                                                <a href="'.$url.'" target="_blank"><img style="height: 40px;" src="https://login.mypayrollmaster.online/newlogin/img/logo.png" alt="My Payroll Master" ></a></td>
                                                <td width="30"></td>
                                            </tr>
                                       	</tbody>
                                        </table>
                                  	</td>
                    			</tr>
                                <tr>
                                    <td colspan="3" align="center">
                                        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td colspan="3" height="60"></td></tr><tr style="background-color: #2e7695;
                                                COLOR: white;
                                                height: 140px;"><td width="25"></td>
                                                <td align="center">
                                                    <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:35px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Welcome to <font style="color:#fff;"> My Payroll Master</font> </h1>
                                                </td>
                                                <td width="25"></td>
                                                
                                            </tr>
                                            
                                 	</tbody>
                                    </table>
                             	</td>
                   			</tr>
                            
                            <tr bgcolor="#ffffff">
                             <td colspan="3" align="center">
                             <table width="590" align="center" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                     <tr>
                                        	<td colspan="4" height="30">&nbsp;</td>
                                      	</tr>
                                    	<tr>
                                        	<td>
                                            	
                                        		<div style="color:#404040;font-size:13px;line-height:16px;font-weight:lighter;padding:0;margin:0">
                                                        Dear '.$name.',&nbsp;<br><br>
                                                        Your Employee Self-Service login has been reset. Please use the below credentials to login to My Payroll Master - Employee Self Service portal or Mob Application.</div>
                                          	</td>
                                      	</tr>
                                       
                          	</tbody>
                            </table>
                                    <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    <tr>
                                        	<td colspan="4" height="30">&nbsp;</td>
                                      	</tr>
                                    	
                                        
                                        <tr>
                                        	<td width="130" align="right" valign="top">
                                                <h3 style="color:#404040;font-size:13px;line-height:16px;font-weight:bold;padding:0;margin:0">Username</h3></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                                
                                                <div style="color:#404040;font-size:13px;line-height:16px;font-weight:lighter;padding:0;margin:0">'
                                                .$userid.
                                                ' / Your Registered Email ID</div>
                                                    <div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                          	</td>
                                            <td width="30"></td>
                                        </tr>
                                        
                                        <tr>
                                        	<td width="130" align="right" valign="top">
                                                <h3 style="color:#404040;font-size:13px;line-height:16px;font-weight:bold;padding:0;margin:0">Password</h3></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	
                                              	<div style="color:#404040;font-size:13px;line-height:16px;font-weight:lighter;padding:0;margin:0">'.$password1.'</div>
                                              	<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                          	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td width="130" align="right" valign="top">
                                                <h3 style="color:#404040;font-size:13px;line-height:16px;font-weight:bold;padding:0;margin:0">URL</h3></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	
                                              	<div style="color:#404040;font-size:13px;line-height:16px;font-weight:lighter;padding:0;margin:0">'.$url.'</div>
                                              	<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                          	</td>
                                            <td width="30"></td>
                                        </tr>
                                        
                                  	</tbody>
                                    </table>
                                    <table width="590" align="center" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    	<tr>
                                        	<td>
                                            	<h2 style="color:#404040;font-size:22px;font-weight:bold;line-height:26px;padding:0;margin:0">&nbsp;</h2>
                                        		<div style="color:#404040;font-size:13px;line-height:16px;font-weight:lighter;padding:0;margin:0">
                                                        You may apply and approve leaves, view your reports and payslips, mark your attendance using your Employee Self Service login. 
                                                        You can download the mobile application from Playstore/Appstore by searching My Payroll Master. 
                                                        You may also find more details about My Payroll Master by visiting <a href="http://mypayrollmaster.online/">mypayrollmaster.online</a>
                                                        <br><br>Thanks & Regards<br>
                                                        Team My Payroll Master</div>
                                          	</td>
                                      	</tr>
                                        <tr>
                                        	<td align="center">
                                                <div style="text-align:center;width:100%;padding:40px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                        	<td align="center" style="margin:0;text-align:center"><a href="'.$url.'" style="font-size:18px;font-family:HelveticaNeue-Light,Arial,sans-serif;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#2e7695;padding:14px 40px;display:block" target="_blank">Login!</a></td>
                                                    	</tr>
                                                   	</tbody>
                                                 	</table>
                                              	</div>
                                        	</td>
                                      </tr><tr><td>&nbsp;</td>
                                      </tr></tbody></table></td>
                                <td width="30" bgcolor="#eeeeee"></td>
                            </tr>
                          	</tbody>
                            </table>
                  			<table align="center" width="750px" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="width:750px!important">
                            <tbody>
                            	<tr>
                                	<td>
                                        <table width="630" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
                                        <tbody>
                                        	<tr><td colspan="2" height="30"></td></tr>
                                            <tr>
                                            	<td width="360" valign="top">
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">&copy; 2018 My Payroll Master. All rights reserved.</div>
                                                	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">Made in India</div>
                                        		</td>
                                              	<td align="right" valign="top">
                                                	<span style="line-height:20px;font-size:10px"><a href="https://www.facebook.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/BggPYqAh.png" alt="fb"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://twitter.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/j3NsGLak.png" alt="twit"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://plus.google.com/mypayrollmater" target="_blank"><img src="http://i.imgbox.com/wFyxXQyf.png" alt="g"></a>&nbsp;</span>
                                              	</td>
                                            </tr>
                                            <tr><td colspan="2" height="5"></td></tr>
                                           
                                      	</tbody>
                                        </table>
                                   	</td>
                  				</tr>
                          	</tbody>
                            </table>
                  		</td>
                	</tr>
              	</tbody>
                </table>
            </td>
		</tr>
 	</tbody>
    </table>
</div></html>');
            $mail->AltBody = 'This is a plain-text message body';
 return $mail->send();
        } catch (Exception $ex) {
            return false;
        }
       return false;
    }
 public function chekPassword($oldpassword = ''){
        $this->autoRender = FALSE;
        $arr_form_data = $_REQUEST;
       // debug($oldpassword);
        $password = $arr_form_data['oldpassword'];
        $emp = $this->Session->read('emp_fkey');
        // debug($emp);
        echo json_encode($oldpassword);
    }
    public function savedata()
    {
	 $this->autoRender = false;
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $userid = 'GLET100132';
		 $userCredentials_data = $this->UserCredentials->find("first", array('fields' => 'emp_fkey,email,attr2', "conditions" => array("user_id" => $userid)));
            $data_db = $this->EmployeeDetails->find("first", array('fields' => 'branch_code, company_code', "conditions" => array("emp_pkey" => $userCredentials_data['UserCredentials']['emp_fkey'])));

            $employeeInfo = $this->UserCredentials->query("select branch,designation,department,joining_date from employee_info WHERE emp_pkey = " . $userCredentials_data['UserCredentials']['emp_fkey']);
            $companyInfo = $this->UserCredentials->query("select * from comp_contact_info WHERE id = 1 ");
			 $payloadData = array(
                "branchCode" => $data_db['EmployeeDetails']['branch_code'],
                "companyCode" => $data_db['EmployeeDetails']['company_code'],
                "email" => $userCredentials_data['UserCredentials']['email'],
                "employeeId" => $userid,
                "joiningDate" => isset($employeeInfo['0']['employee_info']['joining_date']) ? date("d-m-Y", strtotime($employeeInfo['0']['employee_info']['joining_date'])) : '',
                "policyName" => "",
                "companyName" => isset($companyInfo['0']['comp_contact_info']['business_name']) ? $companyInfo['0']['comp_contact_info']['business_name'] : '',
                "department" => isset($employeeInfo['0']['employee_info']['department']) ? $employeeInfo['0']['employee_info']['department'] : '',
                "designation" => isset($employeeInfo['0']['employee_info']['designation']) ? $employeeInfo['0']['employee_info']['designation'] : '',
                "profileId" => $userCredentials_data['UserCredentials']['attr2'],
                "status" => 1
            );
			  $employeedetailsResponses = $this->empDataThirdparty_update($payloadData);
			    echo json_encode(array('msg' => 'User Credentials saved successfully',  'employeeAddAPi' => json_decode($employeedetailsResponses)));
      }
}