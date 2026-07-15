<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class EmployeeAttendanceUploadController extends AppController {

    public $datatable = array();
    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'EmployeeAttendanceUpload';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'EmployeeAttendanceUpload', 'EmpDetailedAttendanceUpload', 'EmployeeDetails', 'Units', 'CompanyInfo','LeaveEntries');
    public $components = array('DatatablesManagement');

    public function index()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        $this->set('user_group', $user_group);
        $company_code = $this->Session->read('company_code');
        if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $cur_emp_key = $this->Session->read("emp_fkey");

            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $conditions = array("branch_code" => $cur_emp_branch, "status" => 1);
            //edited by athira on 24-01-2025
            $emp_pkey = $this->Session->read("emp_fkey");
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            $this->set('is_ho', $is_ho);
            if ($is_ho != 1) {
                // If the employee is not from HO, filter by the branch code
                $conditions = array("branch_code" => $is_ho, "status" => 1);
            } else {
                $conditions = array("status" => 1);
            }
            //end
        } else {
            $conditions = array("status" => 1);
        }
        //employee branch wise sorting ends here

        $arr_branches = $this->Units->find("all", array("conditions" => $conditions));
        $this->set("arr_branches", $arr_branches);
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        //debug($arr_branches);
        $arr_employees = $this->EmployeeDetails->find("all", array('fields' => 'EmployeeDetails.*,EmployeeProfessionalDetails.emp_company_id,', 'conditions' => $conditions, 'joins' => $joins));
        $this->set("arr_employees", $arr_employees);
    }
//employee search added by megha on 15_06_19
      public function employeefilter($branch = ''){
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = "and (first_name like '%$q%' OR emp_proff.emp_company_id like '%$q%' ) ";//Added employee company id search by ***ARUL P DAS on 19/12/2019
        } else {
            $q_condition = "";
        }
        $condition = '';
        if ($branch != '') {
            $condition = " and emp_details.branch_code='$branch' ";
        }
        $arr_site_cnt = $this->EmployeeDetails->query("select count(*) from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)  left join branches bn on (emp_details.branch_code = bn.branch_code) where emp_details.status= '1' and bn.status ='1' and emp_proff.day_time_seq is not null $q_condition $condition ");
        $arr_site = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) left join branches bn on (emp_details.branch_code = bn.branch_code) where emp_details.status= '1' and bn.status ='1' and emp_proff.day_time_seq is not null $q_condition $condition order by first_name ASC");
        
        $array = array();
        $employees[] = array("id" => "0", "text" => "ALL");
        //***emp_pkey replaced with emp_company_id in the employee list. By ARUL P DAS on 05-11-2019***
        foreach ($arr_site as $key => $value) { 
            $employees[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] ." ". $value['emp_details']['last_name']." - ".$value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $employees;
        echo json_encode($array);  
    }
    public function getemployeenames() {
        $this->autoRender = false;
        $s = $this->request->data;
        $status = $s['status'];
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('EmployeeDetails.status' => $status)));
        $this->set("arr_employees", $arr_employees);
        //debug($arr_employees);
        $str_empname_options_html = '';
        foreach ($arr_employees as $value) {
            $emp_pkey = isset($value['EmployeeDetails']['emp_pkey']) ? $value['EmployeeDetails']['emp_pkey'] : '';
            $f_name = isset($value['EmployeeDetails']['first_name']) ? $value['EmployeeDetails']['first_name'] : '';
            $l_name = isset($value['EmployeeDetails']['last_name']) ? $value['EmployeeDetails']['last_name'] : '';
            $str_empname_options_html .= '<option value="' . $emp_pkey . '">' . $f_name . $l_name . '</option>';
        }
        echo $str_empname_options_html;
        //debug($str_empname_options_html);
    }

//     public function form($emp_fkey = 0) {
//         $this->layout = null;
//         $this->CompanyInfo->useDbConfig = $this->Session->read('ds');
//         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//         //edited by megha on 17_06_19 shift policy only employees
//         //$arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
//         //Query edited and added emp_company_id by ***ARUL P DAS on 11/12/2019
        
//         $user_group = $this->Session->read('user_group');
//          if ($user_group == 2) {
//             $cur_emp_key = $this->Session->read("emp_fkey");
//             $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//             $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
//             $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
//             // Edited by Akshay on 3-2-2025
//             $emp_pkey = $this->Session->read("emp_fkey");
//             $company_code = $this->Session->read('company_code');
//             if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {
//                 $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) AS branch;");
//                 $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
//                 if ($is_ho != 1) {
//                     $branch_condition = " and EmployeeDetails.branch_code ='" . $cur_emp_branch . "'";
//                 } else {
//                     $branch_condition = "";
//                 }
//             } else {
//                 //edit by megha on 06_11_2025
//                  $company_code = strtoupper($this->Session->read('company_code'));
//                if ($company_code == 'WHOO') {
//                  $branch_condition = "";
//                }else{
//                 $branch_condition = " and EmployeeDetails.branch_code ='" . $cur_emp_branch . "'";
//                }
//             }
//             // End
//         } else {
//             $branch_condition = "";
//         }
        
      

//         // edited by bindu 20-12-2025
//         $company_code = $this->Session->read('company_code');
//         if($company_code=='ELSL'){
//  $logged_emp_key = (int)$this->Session->read('emp_fkey');

// $joins = [
//     [
//         'table' => 'emp_proff',
//         'alias' => 'EmployeeProfessionalDetails',
//         'type' => 'LEFT',
//         'foreignKey' => false,
//         'conditions' => ['EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey']
//     ]
// ];

// $emp_conditions = [
//     'EmployeeDetails.status' => 1,
//     'OR' => [
//         'EmployeeProfessionalDetails.attr1' => $logged_emp_key,
//         'EmployeeProfessionalDetails.attr2' => $logged_emp_key
//     ]
// ];

// $arr_employees = $this->EmployeeDetails->find('all', [
//     'fields' => ['EmployeeDetails.*', 'EmployeeProfessionalDetails.emp_company_id'],
//     'conditions' => $emp_conditions,
//     'joins' => $joins,
//     'order' => ['EmployeeDetails.first_name' => 'ASC']
// ]);

// $this->set('arr_employees', $arr_employees);
//         }
//       else{

//           $arr_employees = $this->EmployeeDetails->query("select EmployeeDetails.emp_pkey,EmployeeDetails.first_name,EmployeeDetails.last_name,emp_proff.emp_company_id from emp_details as EmployeeDetails inner join emp_proff on (emp_proff.emp_fkey = EmployeeDetails.emp_pkey) inner join branches bn on (EmployeeDetails.branch_code = bn.branch_code) where EmployeeDetails.status= '1' ".$branch_condition." and bn.status ='1' and emp_proff.day_time_seq is not null order by first_name ASC");

//         $this->set("arr_employees", $arr_employees);

//       }

// // edited by bindu 20-12-2025 end
//         $this->EmployeeAttendanceUpload->useDbConfig = $this->Session->read('ds');
//         $arr_att_types = $this->CompanyInfo->find("all", array('conditions' => array('active' => 'Y')));
//         //  debug($emp_fkey);
//         if (isset($arr_att_types[0]['CompanyInfo']['attendance_type'])) {
//             if ($arr_att_types[0]['CompanyInfo']['attendance_type'] == 1) {
//                 $arr_att_types[0]['CompanyInfo']['attendance_type'] = 'Simple Attendance';
//             } else if ($arr_att_types[0]['CompanyInfo']['attendance_type'] == 2) {
//                 $arr_att_types[0]['CompanyInfo']['attendance_type'] = 'Time Attendance';
//             }
//         }
//         $this->set("arr_att_types", $arr_att_types);
//         $data['emp_attendance_upload_pkey'] = 0;
//         $data['emp_fkey'] = $this->Session->read('emp_fkey');
//         // debug($data['emp_fkey']);
//         $data['attendance_type'] = "";
//         $data['in_date'] = "";
//         $data['in_time'] = "";
//         $data['out_date'] = "";
//         $data['out_time'] = "";
//         $this->EmployeeAttendanceUpload->useDbConfig = $this->Session->read('ds');
//         //debug($_REQUEST['emp_attendance_upload_pkey']);
//         if (isset($_REQUEST['emp_attendance_upload_pkey']) && $_REQUEST['emp_attendance_upload_pkey'] != 0) {
//             $data_db = $this->EmployeeAttendanceUpload->find("first", array("conditions" => array("emp_attendance_upload_pkey" => $_REQUEST['emp_attendance_upload_pkey'])));
//             $data = $data_db['EmployeeAttendanceUpload'];
//         }
//         //   debug($data);
//         $this->set("data", $data);
//     }

public function form($emp_fkey = 0)
    {
        ini_set('memory_limit', '-1');
        Configure::write('debug', 0);
        $this->layout = null;
        $this->CompanyInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //edited by megha on 17_06_19 shift policy only employees
        //$arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        //Query edited and added emp_company_id by ***ARUL P DAS on 11/12/2019

        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            // Edited by Akshay on 3-2-2025
            $emp_pkey = $this->Session->read("emp_fkey");
            $company_code = $this->Session->read('company_code');
            if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) AS branch;");
                $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                if ($is_ho != 1) {
                    $branch_condition = " and EmployeeDetails.branch_code ='" . $cur_emp_branch . "'";
                } else {
                    $branch_condition = "";
                }
            } else {
                //edit by megha on 06_11_2025
                $company_code = strtoupper($this->Session->read('company_code'));
                if ($company_code == 'WHOO') {
                    $branch_condition = "";
                } else {
                    $branch_condition = " and EmployeeDetails.branch_code ='" . $cur_emp_branch . "'";
                }
            }
            // End
        } else {
            $branch_condition = "";
        }



        // edited by bindu 20-12-2025
        $company_code = $this->Session->read('company_code');
        if ($company_code == 'ELSL') {
            $logged_emp_key = (int) $this->Session->read('emp_fkey');

            $joins = [
                [
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => ['EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey']
                ]
            ];

            $emp_conditions = [
                'EmployeeDetails.status' => 1,
                'OR' => [
                    'EmployeeProfessionalDetails.attr1' => $logged_emp_key,
                    'EmployeeProfessionalDetails.attr2' => $logged_emp_key
                ]
            ];

            $arr_employees = $this->EmployeeDetails->find('all', [
                'fields' => ['EmployeeDetails.emp_pkey', 'EmployeeDetails.first_name', 'EmployeeDetails.last_name', 'EmployeeProfessionalDetails.emp_company_id'],
                'conditions' => $emp_conditions,
                'joins' => $joins,
                'order' => ['EmployeeDetails.first_name' => 'ASC']
            ]);

            $this->set('arr_employees', $arr_employees);
        } else {

            $arr_employees = $this->EmployeeDetails->query("select EmployeeDetails.emp_pkey,EmployeeDetails.first_name,EmployeeDetails.last_name,EmployeeProfessionalDetails.emp_company_id from emp_details as EmployeeDetails inner join emp_proff as EmployeeProfessionalDetails on (EmployeeProfessionalDetails.emp_fkey = EmployeeDetails.emp_pkey) inner join branches bn on (EmployeeDetails.branch_code = bn.branch_code) where EmployeeDetails.status= '1' " . $branch_condition . " and bn.status ='1' and EmployeeProfessionalDetails.day_time_seq is not null order by first_name ASC");

            $this->set("arr_employees", $arr_employees);

        }

        // edited by bindu 20-12-2025 end
        $this->EmployeeAttendanceUpload->useDbConfig = $this->Session->read('ds');
        $arr_att_types = $this->CompanyInfo->find("all", array('conditions' => array('active' => 'Y')));
        //  debug($emp_fkey);
        if (isset($arr_att_types[0]['CompanyInfo']['attendance_type'])) {
            if ($arr_att_types[0]['CompanyInfo']['attendance_type'] == 1) {
                $arr_att_types[0]['CompanyInfo']['attendance_type'] = 'Simple Attendance';
            } else if ($arr_att_types[0]['CompanyInfo']['attendance_type'] == 2) {
                $arr_att_types[0]['CompanyInfo']['attendance_type'] = 'Time Attendance';
            }
        }
        $this->set("arr_att_types", $arr_att_types);
        $data['emp_attendance_upload_pkey'] = 0;
        $data['emp_fkey'] = $this->Session->read('emp_fkey');
        // debug($data['emp_fkey']);
        $data['attendance_type'] = "";
        $data['in_date'] = "";
        $data['in_time'] = "";
        $data['out_date'] = "";
        $data['out_time'] = "";
        $this->EmployeeAttendanceUpload->useDbConfig = $this->Session->read('ds');
        //debug($_REQUEST['emp_attendance_upload_pkey']);
        if (isset($_REQUEST['emp_attendance_upload_pkey']) && $_REQUEST['emp_attendance_upload_pkey'] != 0) {
            $data_db = $this->EmployeeAttendanceUpload->find("first", array("conditions" => array("emp_attendance_upload_pkey" => $_REQUEST['emp_attendance_upload_pkey'])));
            $data = $data_db['EmployeeAttendanceUpload'];
        }
        //   debug($data);
        $this->set("data", $data);
    }
    //The below function is to check whether the date of upload attendance is already applied for leave or approved leave. 
    //////////////By ***ARUL P DAS on 19/11/2019***
    public function checkleave($emp_fkey=0, $in_date=0,$out_date=0){
        $this->autoRender=FALSE;
        $this->EmployeeAttendanceUpload->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->LeaveEntries->useDbConfig = $this->Session->read('ds');
        $date=date("Y-m",strtotime($in_date));//This is to convert Y-m-d into Y-m


        ////////////////////THE BELOW CODE IS TO CHECK WHETHER THE EMPLOYEE HAVE SHIFT POLICY. AND ALSO USED TO CHECK IN
        ////////TIME AND OUT TIME. AND TO CALCULATE THE FIRST HALF AND SECOND HALF TIME. BY ***ARUL P DAS on 30/11/2019
        $shift_policy=$this->LeaveEntries->query("select st.on_dutty1,st.off_dutty1 from working_day_time_procedures st join emp_proff ep on ep.day_time_seq=st.day_time_seq where ep.emp_fkey='$emp_fkey'");
        $sp=array();
        if(count($shift_policy)==0){
            $sp['status']=0;
            $sp['msg']="Employee have no shift policy";
        }else{
            $sp['status']=1;
            // $sp['msg']="Employee have shift policy";
            $sp['on']=$shift_policy[0]['st']['on_dutty1'];
            $sp['off']=$shift_policy[0]['st']['off_dutty1'];
        }
        // debug($shift_policy);
        /////////////////////LEAVE POLICY CHECKING ENDS HERE////////////////////////////////////////////////////////



        if($out_date==0){//This is to check the in_date only. By ***ARUL P DAS on 21/11/2019***

            $result=$this->LeaveEntries->query("select emp_leave_transactions.Leavestatus,emp_leave_transactions.leave_date,emp_leave_transactions.leave_session,leaveentries.EMP_fkey,salary_head_items.occurance,employee_info.EmpName,leaveentries.applied_date, leaveentries.ISAutherizedby from emp_leave_transactions left join leaveentries on (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID) left join salary_head_items on (salary_head_items.salary_head_item_pkey = leaveentries.salary_head_item_fkey) left join employee_info on (leaveentries.APPROVEDBY=employee_info.emp_pkey) where leaveentries.EMP_fkey = '$emp_fkey' and emp_leave_transactions.leave_date='$in_date' and emp_leave_transactions.Leavestatus in ('Applied','Approved','Authorized')");
            // debug($result);


            $msg=0;
            $status="";
            if(count($result)>0){
                for($i=0;$i<count($result);$i++){
                    if($result[$i]['emp_leave_transactions']['Leavestatus']=='Applied'){
                        // $status="Leave Applied on ".$result[$i]['leaveentries']['applied_date'];
                        $occurance=$result[$i]['salary_head_items']['occurance'];
                        $leave_session=$result[$i]['emp_leave_transactions']['leave_session'];
                        $ls="";
                        if($leave_session==1){
                            $ls="FH";
                            // $ls="First half";
                        }elseif ($leave_session==2) {
                            $ls="SH";
                            // $ls="Second half";
                        }else{
                            $ls="FD";
                            // $ls="Full day";
                            $msg=1;//1 means leave Approved/Applied Full day. Blocking save button. By ARUL P DAS
                        }
                        // $status.="$ls $occurance applied on ".$result[$i]['emp_leave_transactions']['leave_date'];
                        $status.="$ls $occurance applied<br>";
                        if($msg!=1){
                            $msg=2;//2 means leave applied. By ARUL P DAS
                        }
                    }elseif($result[$i]['emp_leave_transactions']['Leavestatus']=='Approved'){
                        $occurance=$result[$i]['salary_head_items']['occurance'];
                        $leave_session=$result[$i]['emp_leave_transactions']['leave_session'];
                        $ls="";
                        if($leave_session==1){
                            $ls="FH";
                            // $ls="First half";
                        }elseif ($leave_session==2) {
                            $ls="SH";
                            // $ls="Second half";
                        }else{
                            $ls="FD";
                            // $ls="Full day";
                            $msg=1;//1 means leave Approved/Applied Full day. Blocking save button. By ARUL P DAS
                        }
                        $approved="";
                        $approved_by=$result[$i]['employee_info']['EmpName'];
                        if($approved_by!="" || $approved_by!=NULL){
                            $approved=" by ".$approved_by;
                        }else{
                            $approved=" by Admin";
                        }
                        $approved_by=$result[$i]['employee_info']['EmpName'];
                        $leave_date=$result[$i]['emp_leave_transactions']['leave_date'];
                        // $status.="$ls $occurance Approved on ".$leave_date.$approved;
                        $status.="$ls $occurance Approved".$approved."<br>";
                    }else{//This is the case of Authorized leaves. By **ARUL P DAS on 4/12/2019
                        $occurance=$result[$i]['salary_head_items']['occurance'];
                        $leave_session=$result[$i]['emp_leave_transactions']['leave_session'];
                        $ls="";
                        if($leave_session==1){
                            $ls="FH";
                            // $ls="First half";
                        }elseif ($leave_session==2) {
                            $ls="SH";
                            // $ls="Second half";
                        }else{
                            $ls="FD";
                            // $ls="Full day";
                            $msg=1;//1 means leave Approved/Applied Full day. Blocking save button. By ARUL P DAS
                        }
                        $authorized="";
                        $authorized_id=$result[$i]['leaveentries']['ISAutherizedby'];//This is the authorized persons emp_pkey
                        // debug($authorized_id);
                        if($authorized_id!=0 || $authorized_id!=NULL){
                            $result2=$this->LeaveEntries->query('select EmpName from employee_info where emp_pkey='.$authorized_id);
                            // debug($result2);
                            $authorized_empname=$result2[0]['employee_info']['EmpName'];
                            if($authorized_empname!="" || $authorized_empname!=NULL){
                                $authorized=" by ".$authorized_empname;
                            }
                        }
                        $leave_date=$result[$i]['emp_leave_transactions']['leave_date'];
                        // $status.="$ls $occurance Authorized on ".$leave_date.$authorized;
                        $status.="$ls $occurance Authorized".$authorized."<br>";
                    }
                    if(count($shift_policy)!=0){
                        $sp['shift']=$ls;
                    }
                }
                // echo json_encode(array("status"=>$status,"msg"=>$msg,"count"=>0));
                echo json_encode(array("status"=>$status,"msg"=>$msg,"policy"=>$sp));
                return;
            }


        }else{//This is to check between the in_date and out_date. By ***ARUL P DAS on 21/11/2019***

            $result=$this->LeaveEntries->query("select emp_leave_transactions.Leavestatus,emp_leave_transactions.leave_date,emp_leave_transactions.leave_session,leaveentries.EMP_fkey,salary_head_items.occurance,employee_info.EmpName,leaveentries.applied_date from emp_leave_transactions left join leaveentries on (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID) left join salary_head_items on (salary_head_items.salary_head_item_pkey = leaveentries.salary_head_item_fkey) left join employee_info on (leaveentries.APPROVEDBY=employee_info.emp_pkey) where leaveentries.EMP_fkey = '$emp_fkey'  and date_format(emp_leave_transactions.leave_date,'%Y-%m') = '$date'and emp_leave_transactions.Leavestatus in ('Applied','Approved')");
            // debug($result);
            $msg=0;
            $status="";
            $count=0;
            $flag=0;
            if(count($result)>0){
                for($i=0;$i<count($result);$i++){
                    if($result[$i]['emp_leave_transactions']['Leavestatus']=='Applied'){
                        // $status="Leave Applied on ".$result[$i]['leaveentries']['applied_date'];
                        $occurance=$result[$i]['salary_head_items']['occurance'];
                        $leave_session=$result[$i]['emp_leave_transactions']['leave_session'];
                        $ls="";
                        if($leave_session==1){
                            $ls="First half";
                        }elseif ($leave_session==2) {
                            $ls="Second half";
                        }else{
                            $ls="Full day";
                        }
                        $leave_date=$result[$i]['emp_leave_transactions']['leave_date'];
                        if($leave_date>=$in_date && $leave_date<=$out_date){
                            $status.="$ls $occurance applied on ".$leave_date."<br>";
                            $msg=2;//msg=2 means leave applied.
                        }
                    }else{
                        $occurance=$result[$i]['salary_head_items']['occurance'];
                        $leave_session=$result[$i]['emp_leave_transactions']['leave_session'];
                        $ls="";
                        if($leave_session==1){
                            $ls="First half";
                        }elseif ($leave_session==2) {
                            $ls="Second half";
                        }else{
                            $ls="Full day";
                        }
                        $approved_by=$result[$i]['employee_info']['EmpName'];
                        $leave_date=$result[$i]['emp_leave_transactions']['leave_date'];
                        if($leave_date>=$in_date && $leave_date<=$out_date){
                            $status.="$ls $occurance Approved on ".$leave_date." by ".$approved_by."<br>";
                            $msg=1;
                            $flag=1;
                        }
                    }
                    $count++;
                }
                if($flag==1){$msg=1;}
                echo json_encode(array("status"=>$status,"msg"=>$msg,"count"=>$count));
                return;
            }
        }
        echo json_encode(array("status"=>'',"msg"=>0,"policy"=>$sp));//msg=0 means, no leaves neither applied or approved

    }

    public function attendancesave() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmpDetailedAttendanceUpload->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $emp_fkey = $data['emp_fkey'] = $arr_form_data['emp_fkeys'];
        $date = $arr_form_data['in_date'];
        $array = $this->EmpDetailedAttendanceUpload->query("select emp_fkey from attendance_register where month_year = DATE_FORMAT('$date', '%Y-%m')
        and isdelete='N' and emp_fkey='$emp_fkey'");
        $array1 = $this->EmpDetailedAttendanceUpload->query("select emp_pkey from emp_detail_timeattandance where att_date = '$date'
        and isdelete='N' and emp_pkey='$emp_fkey'");
        $sum = count($array) + count($array1);

        $originalDate = isset($arr_form_data['in_date']) ? $arr_form_data['in_date'] : '';
        $newDate = date("Y-m-d", strtotime($originalDate));
        $arr_form_data['in_date'] = $newDate;
        $originalDate1 = isset($arr_form_data['out_date']) ? $arr_form_data['out_date'] : '';
        $newDate1 = date("Y-m-d", strtotime($originalDate1));
        $arr_form_data['out_date'] = $newDate1;

        $data = array();
        $data['emp_attendance_upload_pkey'] = $arr_form_data['emp_attendance_upload_pkey'];
        
        $data['emp_fkey'] = $emp_fkey;
        $data['attendance_type'] = $arr_form_data['attendance_type'];
        $data['att_date'] = $arr_form_data['in_date'];
        $data['att_time'] = $arr_form_data['in_date'].' '.$arr_form_data['in_time'];
        $data['c1'] = 'in';
        $data['created_by'] = $this->Session->read('login_user_id');
        // $data['created_date'] = date('Y-m-d');
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Attendance Uploaded successfully";
      try {
	    if($sum == 0){
             $result1 = $this->EmpDetailedAttendanceUpload->saveAll($data);
           // $data['att_date'] = $arr_form_data['out_date'];
            // $data['att_time'] = $arr_form_data['out_date'].' '.$arr_form_data['out_time'];
            $data['att_date'] = $arr_form_data['in_date'];
            $data['att_time'] = $arr_form_data['in_date'].' '.$arr_form_data['out_time'];
            $data['c1'] = 'out';
            $result1 = $this->EmpDetailedAttendanceUpload->saveAll($data);
        
            }else {
                $resp["success"] = false;
                $resp["msg"] = "Can't add attendance. Attendance verified.";
            }           
        } catch (Exception $ex) {
            
        }
														  
        echo json_encode($resp);
    }

    public function load() {

        $this->autoRender = FALSE;
        $this->layout = null;
        $data['emp_attendance_upload_pkey'] = 0;
        $data['emp_fkey'] = "";
        $data['attendance_type'] = "";
        $data['in_date'] = "";
        $data['in_time'] = "";
        $data['out_date'] = "";
        $data['out_time'] = "";
        $this->EmployeeAttendanceUpload->useDbConfig = $this->Session->read('ds');
        if (isset($_REQUEST['emp_attendance_upload_pkey']) && $_REQUEST['emp_attendance_upload_pkey'] != 0) {
            $data_db = $this->EmployeeAttendanceUpload->find("first", array("conditions" => array("emp_attendance_upload_pkey" => $_REQUEST['emp_attendance_upload_pkey'])));
            //	debug($data);
            $data = $data_db['EmployeeAttendanceUpload'];
        }
        $respdata = array('success' => true, "data" => $data);
        echo json_encode($respdata);
    }

    public function listattendance() {

        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $month = isset($arr_request_data['month']) ? $arr_request_data['month'] : '';
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //$first = date('Y-m-d', strtotime($month));
        //$last = date('Y-m-t', strtotime($month));
		
		//added by megha on on 11_03_2020 changed for SH Infra
        $month1 =  $month.'-01';     
        $att_startdate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");  
        $first = $att_startdate['0']['0']['monthly_att_fromdate'];
        $last = $att_enddate['0']['0']['monthly_att_todate'];
        //end
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $condition = '';
        $condition1 = '';
        $condition2 = '';

        $conditions = array();

        if ($branch_code != '') {
            $condition1 = " and ed.branch_code='$branch_code' ";
        }
        
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        $emp_pkey = $this->Session->read('emp_fkey');
        $company_code = $this->Session->read('company_code');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");

            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                $this->set('is_ho', $is_ho);

                if ($is_ho != 1) {
                    $condition1 =  " and ed.branch_code='$cur_emp_branch' ";
                }
            } else {
                 $company_code = strtoupper($this->Session->read('company_code'));
        if ($company_code == 'WHOO') {
             $$condition1 = '';
        }else{
                $condition1 =  " and ed.branch_code='$cur_emp_branch' ";
            }

        }
        }
        //employee branch wise sorting ends here
        
        if ($emp_fkey > 0) {
            $condition2 = "and au.emp_fkey='$emp_fkey' ";
        }
        if ($month != '') {
            $condition = " and au.att_date  BETWEEN '$first' AND '$last' ";
        }

//debug($emp_fkey);        

        $this->EmpDetailedAttendanceUpload->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $count = $this->EmpDetailedAttendanceUpload->query("select count(*) cntd
 from emp_details ed INNER join emp_detailed_attendance_uploads au on (ed.emp_pkey = au.emp_fkey)  INNER join branches bn on (ed.branch_code = bn.branch_code)
 inner join emp_proff on (emp_proff.emp_fkey = ed.emp_pkey)
  where au.status=1  and bn.status='1' and emp_proff.day_time_seq is not null $condition $condition2 $condition1  ");
        //edited by megha on 17_06_19 ed.middile_name removed
        $arr_att = $this->EmpDetailedAttendanceUpload->query("select distinct concat(ed.first_name,' ',ed.last_name,' - ',emp_proff.emp_company_id) 
 empname,emp_detailed_attendance_pkey,emp_proff.emp_fkey,att_date,att_time,c1
 from emp_details ed LEFT join emp_detailed_attendance_uploads as au on (ed.emp_pkey = au.emp_fkey)  
  LEFT join branches as bn on (ed.branch_code = bn.branch_code)
LEFT join emp_proff on (emp_proff.emp_fkey = ed.emp_pkey)
  where au.status=1  and bn.status='1' and emp_proff.day_time_seq is not null $condition $condition2 $condition1 order by au.created_date DESC limit $limit OFFSET $ofst ");

        $out = array();
        foreach ($arr_att as $key => $value) {
            $out['empname'] = isset($value['0']['empname']) ? $value['0']['empname'] : '';
            $out['emp_attendance_upload_pkey'] = isset($value['au']['emp_detailed_attendance_pkey']) ? $value['au']['emp_detailed_attendance_pkey'] : '';
            $out['emp_fkey'] = isset($value['au']['emp_fkey']) ? $value['au']['emp_fkey'] : '';

            //edited by sinsiya
            $dateindate = isset($value['au']['att_date']) ? $value['au']['att_date'] : '';
            if ($dateindate != '0000-00-00') {
                $out['in_date'] = date('d-m-Y', strtotime($dateindate));
            } else {
                // Handle the special case, set a default value, or take any appropriate action
                $out['in_date'] = '00-00-0000'; // Adjust as needed
            }
            $dateToCheck = isset($value['au']['att_time']) ? $value['au']['att_time'] : '';

            // Check if the date is '0000-00-00'
            if ($dateToCheck != '0000-00-00 00:00:00') {
                $out['in_time'] = date('d-m-Y H:i:s', strtotime($dateToCheck));
            } else {
                // Handle the special case, set a default value, or take any appropriate action
                $out['in_time'] = '00-00-0000 00:00:00'; // Adjust as needed
            }

            $out['direction'] = isset($value['au']['c1']) ? $value['au']['c1'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = isset($count['0']['0']['cntd']) ? $count['0']['0']['cntd'] : '0';
        echo json_encode($resp_att);
    }

    public function deleteattendance() {
        $this->autoRender = FALSE;
        $this->EmpDetailedAttendanceUpload->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);

        if (isset($_REQUEST["emp_attendance_upload_pkeys"])) {
            $ar_ids = explode(",", $_REQUEST["emp_attendance_upload_pkeys"]);

            //debug($ar_ids);

            $this->EmpDetailedAttendanceUpload->updateAll(array('EmpDetailedAttendanceUpload.status' => 0), array('EmpDetailedAttendanceUpload.emp_detailed_attendance_pkey' => $ar_ids));

            //Edited by Akshay on 15-5-2024
            try{
                foreach($ar_ids as $id){
                    $arr_device_att = $this->EmpDetailedAttendanceUpload->query("SELECT * FROM emp_detailed_attendance_uploads WHERE emp_detailed_attendance_pkey = '$id'");
                    if(count($arr_device_att) > 0){
                        $emp_fkey = isset($arr_device_att[0]['emp_detailed_attendance_uploads']['emp_fkey']) ? $arr_device_att[0]['emp_detailed_attendance_uploads']['emp_fkey'] : '';
                        $att_date = isset($arr_device_att[0]['emp_detailed_attendance_uploads']['att_time']) ? $arr_device_att[0]['emp_detailed_attendance_uploads']['att_time'] : '';
                        $dir = isset($arr_device_att[0]['emp_detailed_attendance_uploads']['c1']) ? $arr_device_att[0]['emp_detailed_attendance_uploads']['c1'] : '';
    
                        $arr_emp_details = $this->EmpDetailedAttendanceUpload->query("SELECT ed.branch_code, ed.emp_id, ed.company_code FROM emp_details ed WHERE ed.emp_pkey = '$emp_fkey'");
                        $emp_id = isset($arr_emp_details[0]['ed']['emp_id'])? $arr_emp_details[0]['ed']['emp_id']: ''; 
                        
                        $update_device_att = $this->EmpDetailedAttendanceUpload->query("UPDATE device_attandance
                                                                                            SET status = 'N'
                                                                                            WHERE emp_id = '$emp_id'
                                                                                            AND LOGDATE = '$att_date'
                                                                                            AND C1 ='$dir'
                                                                                            AND C3 = 'Uploaded attandance'
                                                                                            AND status = 'Y';
                                                                                            ");
                    }
                }
            }catch(Exception $e){
                debug($e);
            }
            //End

            $result['success'] = true;
            $result['msg'] = "Record(s)  deleted successfully.";
        }

        echo json_encode($result);
    }

    //modified by sruthi 07/09/16 add branch only 
    public function downloadempattendanceformat($ctcuploadtype = 0, $branch = "", $month  = "", $emp_pkey = '') {
        $this->autoRender = FALSE;
        $company_code = $this->Session->read('company_code');
        $file_name = isset($company_code) ? strtolower($company_code) . "_employee_attendance_uploads.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);
        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $empctcdata = new EmployeeCTCData($ctcuploadtype);
        $emp_credentials_schema = $empctcdata->getFieldHeadings('UserCredentials');
        $emp_details_schema = $empctcdata->getFieldHeadings('EmployeeDetails');
        $emp_proff_schema = $empctcdata->getFieldHeadings('EmployeeProfessionalDetails');
        $emp_ctc_schema = $empctcdata->getFieldHeadings('EmployeeAttendanceUploadDetiled');
        $emp_schema = array_merge($emp_credentials_schema,$emp_proff_schema, $emp_details_schema, $emp_ctc_schema);

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");

        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->EmployeeDetails->query("select salary_head_item_pkey,item,occurance from salary_head_items where head_fkey = 6  and value='Y' and status = '1' ");
        
        $sheet = array($emp_schema);
        foreach ($sheet as $row => $columns) {
            foreach ($columns as $column => $data) {///$data===>Employee ID,Company Employee ID,Employee Name,Direction
                $data = ($data == 'Company Employee ID')? 'Company ID': $data; //Edited by Akshay on 19-3-2024
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . "1", $data);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column))->setWidth(14);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column) . "1")->getFont()->setBold(true);
            }
        }
        //added and commented by megha for attendance range change for sh infra
		$month1 =  $month.'-01'; 
		$att_startdate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");  
        $att_startdate1 = strtotime($att_startdate['0']['0']['monthly_att_fromdate']);
        $att_enddate1 = strtotime($att_enddate['0']['0']['monthly_att_todate']);
		
        //$monthens =  strtotime("+1 months", strtotime($month));
        //$begin = new DateTime(date("Y-m-1", strtotime($month)));
        //$end = new DateTime(date('Y-m-1', $monthens));
        
		$att_enddate2 =  strtotime("+1 day", $att_enddate1);
        $begin = new DateTime(date('Y-m-d',$att_startdate1));
        $end = new DateTime(date('Y-m-d', $att_enddate2));
        $interval = new DateInterval('P1D'); // 1 Day
        $dateRange = new DatePeriod($begin, $interval, $end);
        $format = "Y-m-d";
        $range = [];
        foreach ($dateRange as $date) {
            $range[] = $date->format($format);
        }
       
        $user_login = $this->Session->read('login_user_id');
        $yearmonth = $month . '-01';
        $query = "CALL insert_update_att_reg('$company_code','$branch','$user_login','$yearmonth',@Perr_msg);";
         $this->EmployeeDetails->query($query);
        try{
            
            $attend = $this->EmployeeDetails->query("Call emp_detail_att_reg('$company_code','$branch','$user_login','$yearmonth','$emp_pkey', @`Perr_msg`)");
        } catch (Exception $ex) {

        }
        //Fill form with existing users 
        $emp_credentials_fields = $empctcdata->getFieldNames('UserCredentials');
        $emp_details_fields = $empctcdata->getFieldNames('EmployeeDetails');
        $emp_ctc_fields = $empctcdata->getFieldNames('EmployeeCTC');
        $emp_fields = array_merge(array_keys($emp_credentials_fields), array_keys($emp_details_fields), array_keys($emp_ctc_fields));
        $condtion = "";
        if (isset($branch) && !empty($branch)) {
            $condtion[] ="  EmployeeDetails.branch_code='$branch'";
        }
        if (isset($emp_pkey) && !empty($emp_pkey)) {
            $condtion[] ="  EmployeeDetails.emp_pkey='$emp_pkey'";
        }
        $col = 4;
        foreach($range as $vals){
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . "1", $vals);
            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setWidth(14);
            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . "1")->getFont()->setBold(true);
            $col++;
        }
        $arr_empdetails = $this->EmployeeDetails->find('all', array(
            'fields' => 'UserCredentials.user_id,EmployeeProff.emp_company_id, concat(EmployeeDetails.first_name," ",EmployeeDetails.last_name) as Name',
            'joins' => array(
                array(
                    'table' => 'user_credentials',
                    'alias' => 'UserCredentials',
                    'type' => 'INNER',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
                ),
                array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProff',
                    'type' => 'INNER',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProff.emp_fkey')
                )
            ),
            'conditions' => array(
                'status' => 1, $condtion
            )
        ));
        $rowindex = 2;
        $columnindex = 0;
        foreach ($arr_empdetails as $rows) {
            $columnindex = 0;
            foreach ($rows as $columns) {
                $user_ids = isset($columns['user_id'])?$columns['user_id']:0;
                foreach ($columns as $column) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $column);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $columnindex++;
                }
                $user_ids = isset($rows['UserCredentials']['user_id'])?$rows['UserCredentials']['user_id']:0;
            }
            $arr_user_pkeys = $this->EmployeeDetails->query("select emp_fkey from user_credentials where user_id = '$user_ids' ");
            $emp_pkey = isset($arr_user_pkeys['0']['user_credentials']['emp_fkey'])?$arr_user_pkeys['0']['user_credentials']['emp_fkey']:0;
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, "in");////////////////////THIS IS IN TIME////////////////////////
            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $columnindex++;
            $yearmonths = date("Y-m-1", strtotime($month));
            // $arr_empdetailedattendances = $this->EmployeeDetails->query("SELECT CONCAT(IFNULL(present,''),'',IFNULL(weekoff,''),'',IFNULL(leaves,''),'',IFNULL(holiday,''),'',IFNULL(others,''),'') AS Presents, duration,att_date,att_in_time,att_out_time FROM `emp_detail_timeattandance` WHERE `emp_pkey` = '$emp_pkey' AND `yearmonth` = '$yearmonths' ORDER BY `att_date` ");

            $month=date('Y-m',strtotime($yearmonths));
            //$query = "CALL insert_update_att_reg('$company_code','$branch','$user_login','$month');";
            // $this->EmployeeDetails->query($query);

            $test_empdetaileattendance= $this->EmployeeDetails->query("select * from `attendance_register` where `emp_fkey` = '$emp_pkey' AND `month_year` = '$month'");
            // debug($test_empdetaileattendance);
            ////////The below code is to find the salary structure and prorate code of employee. To check whether he is in working days or calandar days.
            ////By ****ARUL P DAS on 13/12/2019
            $salary_structure=$this->EmployeeDetails->query("select prorate_code from salary_structure ss join emp_proff ep on ss.structure_id=ep.structure_id where ep.emp_fkey='$emp_pkey'");
            $prorate_code=0;
            if(count($salary_structure)!=0){
                $prorate_code=$salary_structure[0]['ss']['prorate_code'];
            }
            //////////////PRORATE CODE QUERY ENDS HERE////////////
            
            foreach($test_empdetaileattendance as $val){
                foreach ($val as $value) {
                    // debug($value);
                    for($i=1;$i<32;$i++){
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex,$value['FIELD'.$i]);
                        $att_date=date('Y-m-'.$i,strtotime($month));
                        ////////The Below Code is to Check whether the looping dates are already applied for leave or not. And if the current looping date of an employee is already applied for leave, It will indicate by orange color in the upload attendance excel file. BY *****ARUL P DAS on 25-11-2019****
                        $leave_details=$this->EmployeeDetails->query("select emp_leave_transactions.Leavestatus,emp_leave_transactions.leave_date,emp_leave_transactions.leave_session,leaveentries.EMP_fkey, salary_head_items.occurance from emp_leave_transactions left join leaveentries on (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID) left join salary_head_items on (salary_head_items.salary_head_item_pkey = leaveentries.salary_head_item_fkey) where leaveentries.EMP_fkey = '$emp_pkey' and emp_leave_transactions.leave_date = '$att_date' and emp_leave_transactions.Leavestatus ='Applied'");
                        ////////Here ORANGE INDICATION query code ends.............
                        $data=$value['FIELD'.$i];
                        if($data!=null){
                            if($data=='LOP' || preg_match('/LOP\//', $data)){
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('163782');
                            }
                            if($data=='WFH' || preg_match('/WFH\//', $data)){
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4ba74a');
                            }
                            if(strpos($data, 'P/')!=false || preg_match('/P\//', $data)){
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4ba74a');
                            }
                            if($prorate_code==2){
                                if ($data=='NA' || preg_match('/NA\//', $data)) {
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00bfff');
                                }
                            }
                            $explode_leaves=explode('/', $data);
                            foreach($arr_leavetypes as $leave_temp){
                                if (strpos($explode_leaves[0], $leave_temp['salary_head_items']['occurance']) !== false){
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('163782');
                                }
                            }
                            if($data=='HO' || preg_match('/HO\//', $data)){
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('0000ff');
                            }
                            if($data=='WO' || preg_match('/WO\//', $data)){
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffff00');
                            }
                            if($prorate_code==1 || $prorate_code==0 || $prorate_code==3){
                                if($data!=''){
                                    if ($data=='NA' || preg_match('/NA\//', $data)) {
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00bfff');
                                    }
                                }
                            }

                            
                        } //$data!=null
                        // If the employee already applied for leave in current month, here checks if it only apply for first half only or for a full day. Both first or full day leave, this section will fill orange color with the field.(if leave only applied for second half, the code will not execute and leave the field blank.) BY ******ARUL P DAS on 25-11-2019*****

                            if(count($leave_details)>0){
                                $in=$leave_details[0]['emp_leave_transactions']['leave_session'];
                                if($in=='1' || $in=='3')//This is for first half or full day check.
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFA500');
                            }
                            ///////////Orange indication ends here.............
                        $columnindex++;
                    }//for loop $i
                }//$value foreach
            }//$test_empdetaileattendance IN row.
            $rowindex++;
            $columnindex = 0;
            foreach ($rows as $columns) {
                foreach ($columns as $column) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $column);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $columnindex++;
                }
            }
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, "out");
            /////////////////////////OUT//////////////////////////////
            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $columnindex++;

            foreach($test_empdetaileattendance as $val){
                foreach ($val as $value) {
                    // debug($value);
                    for($i=1;$i<32;$i++){
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex,$value['FIELD'.$i]);
                        $att_date=date('Y-m-'.$i,strtotime($month));
                        ////////The Below Code is to Check whether the looping dates are already applied for leave or not. And if the current looping date of an employee is already applied for leave, It will indicate by orange color in the upload attendance excel file. BY *****ARUL P DAS on 25-11-2019****
                        $leave_details=$this->EmployeeDetails->query("select emp_leave_transactions.Leavestatus,emp_leave_transactions.leave_date,emp_leave_transactions.leave_session,leaveentries.EMP_fkey, salary_head_items.occurance from emp_leave_transactions left join leaveentries on (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID) left join salary_head_items on (salary_head_items.salary_head_item_pkey = leaveentries.salary_head_item_fkey) where leaveentries.EMP_fkey = '$emp_pkey' and emp_leave_transactions.leave_date = '$att_date' and emp_leave_transactions.Leavestatus ='Applied'");
                        ////////Here ORANGE INDICATION query code ends.............
                        $data=$value['FIELD'.$i];
                        if($data!=null){
                            if($data=='LOP' || preg_match('/\/LOP/', $data)){
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('163782');
                            }
                            if($data=='WFH' || preg_match('/\/WFH/', $data)){
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4ba74a');
                            }
                            if(strpos($data, '/P')){
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4ba74a');
                            }
                            if($prorate_code==2){
                                if ($data=='NA' || preg_match('/\/NA/', $data)) {
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00bfff');
                                }
                            }
                            // $explode_leaves=explode('/', $data);
                            foreach($arr_leavetypes as $leave_temp){
                                if (strpos($data, '/'.$leave_temp['salary_head_items']['occurance']) !== false){
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('163782');
                                }
                                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex,);
                            }
                            if($data=='HO' || preg_match('/\/HO/', $data)){
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('0000ff');
                            }
                            if($data=='WO'){
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffff00');
                            }
                            if (preg_match('/\/WO/', $data)) {
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e1ff2f');
                            }
                            if($prorate_code==1 || $prorate_code==0 || $prorate_code==3){
                                if($data!=''){
                                    if ($data=='NA' || preg_match('/\/NA/', $data)) {
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00bfff');
                                    }
                                }
                            }

                           
                        }//$data!=null
                         //// If the employee already applied for leave in current month, here checks if it only apply for first half only or for a full day. Both first or full day leave, this section will fill orange color with the field.(if leave only applied for second half, the code will not execute and leave the field blank.) BY ******ARUL P DAS on 25-11-2019*****

                            if(count($leave_details)>0){
                                $in=$leave_details[0]['emp_leave_transactions']['leave_session'];
                                if($in=='2' || $in=='3')//This is for second half or full day check.
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFA500');
                            }
                            /////////////Orange indication ends here.............
                        $columnindex++;
                    }//for loop $i
                }//$value foreach
            }//$test_empdetaileattendance OUT row.
            $rowindex++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('Employee Attendance Upload Data');
        $objWorkSheet = $objPHPExcel->createSheet(2);
        $objWorkSheet->getStyle('A1')->getFont()->setBold(true);
        $objWorkSheet->getColumnDimension('B')->setWidth(80);
        $objWorkSheet->getStyle('B1')->getFont()->setBold(true);
        $cll = 2;


//            $objPHPExcel->getActiveSheet()->setCellValue('M8', 'Leave Type');
//            $objPHPExcel->getActiveSheet()->setCellValue('M9', 'Sick Leave = SL');
//            $objPHPExcel->getActiveSheet()->setCellValue('M10', 'Earned Leave = EL');
//            $objPHPExcel->getActiveSheet()->setCellValue('M11', 'Casual Leave =Cl');
//            $objPHPExcel->getActiveSheet()->setCellValue('M12', 'Privilege Leave = PL');
        $objWorkSheet->setCellValue('B1', 'Help');
        $objWorkSheet->setCellValue('B3', 'Time format should be in hh:mm 24 hrs format ');
        $objPHPExcel->getActiveSheet()->SetCellValue('E12', 'WO');
        $objPHPExcel->getActiveSheet()->getStyle('D12')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffff00');
        $objPHPExcel->getActiveSheet()->SetCellValue('E14', 'P');
        $objPHPExcel->getActiveSheet()->getStyle('D14')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4ba74a');
        $objPHPExcel->getActiveSheet()->SetCellValue('E16', 'HO');
        $objPHPExcel->getActiveSheet()->getStyle('D16')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('0000ff');
        $objPHPExcel->getActiveSheet()->SetCellValue('E18', 'L');
        $objPHPExcel->getActiveSheet()->getStyle('D18')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('163782');

        //////////////BELOW CODES ARE BY ***ARUL P DAS
        ////////This is the color indication help menu. The below field color(orange) indicates that, the day is already applied for leave.
        $objPHPExcel->getActiveSheet()->SetCellValue('E20', 'Leave Applied');
        $objPHPExcel->getActiveSheet()->getStyle('D20')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFA500');
        $objPHPExcel->getActiveSheet()->SetCellValue('E22', 'Half Day WO');
        $objPHPExcel->getActiveSheet()->getStyle('D22')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e1ff2f');
        $objPHPExcel->getActiveSheet()->SetCellValue('E24', 'NA');
        $objPHPExcel->getActiveSheet()->getStyle('D24')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00bfff');
        
        /////////////indication ends here...................
        
        $objWorkSheet->setCellValue('D10', 'Color Fill');
        $objWorkSheet->setTitle('Help');
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    //modified by sruthi 07/09/16 add branch only 
    public function downloadempctcformat($ctcuploadtype = 0, $branch = "") {
        $this->autoRender = FALSE;

        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_employee_attendance.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);

        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $empctcdata = new EmployeeCTCData($ctcuploadtype);
        $emp_credentials_schema = $empctcdata->getFieldHeadings('UserCredentials');
        $emp_details_schema = $empctcdata->getFieldHeadings('EmployeeDetails');
        $emp_ctc_schema = $empctcdata->getFieldHeadings('EmployeeAttendanceUpload');
        $emp_schema = array_merge($emp_credentials_schema, $emp_details_schema, $emp_ctc_schema);

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");

        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet();

        $sheet = array($emp_schema);
        foreach ($sheet as $row => $columns) {
            foreach ($columns as $column => $data) {
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . "1", $data);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }
        }

        //Fill form with existing users 
        $emp_credentials_fields = $empctcdata->getFieldNames('UserCredentials');
        $emp_details_fields = $empctcdata->getFieldNames('EmployeeDetails');
        $emp_ctc_fields = $empctcdata->getFieldNames('EmployeeCTC');
        $emp_fields = array_merge(array_keys($emp_credentials_fields), array_keys($emp_details_fields), array_keys($emp_ctc_fields));

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $condtion = "";
        if (isset($branch) && !empty($branch)) {
            $condtion .="  EmployeeDetails.branch_code='$branch'";
        }

        $arr_empdetails = $this->EmployeeDetails->find('all', array(
            'fields' => 'UserCredentials.user_id,concat(EmployeeDetails.first_name," ",EmployeeDetails.last_name) as Name',
            'joins' => array(
                array(
                    'table' => 'user_credentials',
                    'alias' => 'UserCredentials',
                    'type' => 'INNER',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
                )
            ),
            'conditions' => array(
                'status' => 1, $condtion
            )
        ));

        $rowindex = 2;
        $columnindex = 0;
        foreach ($arr_empdetails as $rows) {
            $columnindex = 0;
            foreach ($rows as $columns) {
                foreach ($columns as $column) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $column);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $columnindex++;
                }
            }
            $rowindex++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Employee CTC Data');

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

//     public function uploadandsaveempctc($ctcuploadtype = 0) {
//         $this->autoRender = FALSE;
//         if ($ctcuploadtype != 0) {
//             $authuser['company_code'] = $this->Session->read('company_code');
//             $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_empattendance_' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
//             $targetpath = getcwd() . "/files/" . $filename;
//             if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

//                 App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

//                 $objReader = new PHPExcel_Reader_Excel2007();
//                 $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

//                 $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
//                 $lastColumn++;
//                 $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
//                 $arrayempdata = array();
//                 $mandatory_fields_warning = FALSE;
                
//                 if ($highestRowIndex > 1) {
//                     //atleast one employee records found
//                     $index = 0;
//                     for ($row = 1; $row <= $highestRowIndex; $row++) {
//                         if ($row == 1) {
//                             //Get mandatory headings array here
//                             $array_mandatory_columns = array();
//                             $array_mandatory_column_names = array('User ID', 'First Name', 'Last Name');
//                             for ($col = 'A'; $col != $lastColumn; $col++) {
//                                 $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
//                                 if (in_array($value, $array_mandatory_column_names)) {
//                                     array_push($array_mandatory_columns, $col);
//                                 }
//                             }
//                         } else {
//                             for ($col = 'A'; $col != $lastColumn; $col++) {
//                                 if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
//                                     $mandatory_fields_warning = true;
//                                     break 2;
//                                 }
//                                 $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
//                                 $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
//                             }
//                             $index++;
//                         }
//                     }
//                     if ($mandatory_fields_warning) {
//                         //Exit if mandatory fields not entered
//                         unlink($targetpath);
//                         echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
//                         exit;
//                     } else {
//                         //Iam here now
//                         //debug($arrayempdata);
//                         //Continue with save if mandatory field warning is not there
//                         //Save employee ctc and return success
//                         $this->UserCredentials->useDbConfig = $this->Session->read('ds');
//                         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//                         $this->EmpDetailedAttendanceUpload->useDbConfig = $this->Session->read('ds');

//                         App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
//                         $empcsvdata = new EmployeeCTCData($ctcuploadtype);
//                         $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
//                         $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
//                         $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeAttendanceUpload');
//                         $arr_no_user_id = array();
//                        // debug($arrayempdata);
//                         foreach ($arrayempdata as $key => $row) {

//                             $emp_id = $row['Employee ID'];
//                             $c1 = $row['Direction'];
                            
//                             $user_id = $emp_id;
//                             if ($user_id == '') {
                              
//                                 $arr_no_user_id[] = $key;
//                                  continue;
//                             }

//                             //fetch emp_fkey using user_id
//                             $arr_usercredentials = $this->UserCredentials->find('first', array(
//                                 'fields' => 'emp_fkey',
//                                 'conditions' => array(
//                                     'user_id' => $user_id
//                                 )
//                             ));
                            
// //                           debug($row);
                            
//                             $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';

//                             $arr_empctc_data = array();
//                             $arr_empctc_data['emp_attendance_upload_pkey'] = '';
//                             $arr_empctc_data['attendance_type'] = 1;
//                             $arr_empctc_data['emp_fkey'] = $emp_fkey;
//                             $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
//                             $arr_empctc_data['created_date'] = date("Y-m-d H:i:s");
//                             $arr_empctc_data['c1'] = $c1;
                            
//                             foreach($row as $key => $vals){
//                                 //debug($key);
//                                 //debug($vals);  
//                                 //debug($c1); 
//                                 if($key == 'Direction')continue;
//                                 if($key == 'Employee ID')continue;
//                                 if($key == 'Employee Name')continue;
//                                 if($key == 'Company Employee ID')continue;
//                                 if($key == '')continue;
//                                 if($vals == '')continue;
//                                 //edited by megha on 12_6_19 null value insertion
//                                 if($vals == 'null')continue;
//                                 //try{
//                                     if ($c1 == "in") {
 
//                                         $arr_empctc_data['att_date'] = $key;
//                                         $arr_empctc_data['att_time'] = $key.' '.$vals; //date(strtotime("Y-m-d H:i:s", $vals));
// //                                       $arr_empctc_data['att_time'] = date(strtotime("Y-m-d H:i:s", $time));
// //                                       debug(date(strtotime("Y-m-d H:i:s", $vals)));
// //                                       debug(date(strtotime("Y-m-d H:i:s", $time)));
//                                     } else {

//                                         $arr_empctc_data['att_date'] = $key; //date(strtotime("Y-m-d", $vals));
//                                         $arr_empctc_data['att_time'] = $key.' '.$vals; //date(strtotime("Y-m-d H:i:s", $vals));
// //                                         $arr_empctc_data['att_time'] = date(strtotime("Y-m-d H:i:s", $time));
// //                                         debug(date(strtotime("Y-m-d H:i:s", $vals)));
// //                                       debug(date(strtotime("Y-m-d H:i:s", $time)));
//                                     }
//                                   debug($arr_empctc_data);
//                                     $result1 = $this->EmpDetailedAttendanceUpload->saveAll($arr_empctc_data);
// //                                } catch (Exception $ex) {
// //
// //                                }
                                

                                    
                                
//                             }
                            
                            
//                         }
//                     }
//                     unlink($targetpath);
//                     if ($result1) {
//                         echo json_encode(array('success' => 1, 'msg' => 'Employee Attendance Uploaded '));
//                         exit;
//                     } else {
//                         echo json_encode(array('success' => 1, 'msg' => 'Employee Attendance Uploaded '));
//                         exit;
//                     }
//                 } else {
//                     unlink($targetpath);
//                     if ($ctcuploadtype == 1) {
//                         echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Attendance import failed, no data found!'));
//                         exit;
//                     } else {
//                         echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Attendance revision failed, no data found!'));
//                         exit;
//                     }
//                 }
//             } else {
//                 if ($ctcuploadtype == 1) {
//                     echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Attendance import failed!'));
//                     exit;
//                 } else {
//                     echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Attendance revision failed!'));
//                     exit;
//                 }
//             }
//         } else {
//             if ($ctcuploadtype == 1) {
//                 echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Attendance import failed!'));
//             } else {
//                 echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Attendance revision failed!'));
//                 exit;
//             }
//             exit;
//         }
//     }

    public function uploadandsaveempctc($ctcuploadtype = 0) {
        $this->autoRender = FALSE;
        if ($ctcuploadtype != 0) {
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_empattendance_' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
            if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objReader = new PHPExcel_Reader_Excel2007();
                $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

                $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $lastColumn++;
                $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                $arrayempdata = array();
                $mandatory_fields_warning = FALSE;
                
                if ($highestRowIndex > 1) {
                    //atleast one employee records found
                    $index = 0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row == 1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('User ID', 'First Name', 'Last Name');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                // Use getFormattedValue() so date-formatted header cells return
                                // readable strings (e.g. "3/1/2025") instead of float serials
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getFormattedValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $cellValue = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getFormattedValue();
                                if (in_array($col, $array_mandatory_columns) && trim($cellValue) == '') {
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                // Key the data by the formatted header string (e.g. "3/1/2025")
                                // so column names are always human-readable, never float serials
                                $headerKey = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getFormattedValue();
                                $arrayempdata[$index][$headerKey] = $cellValue;
                            }
                            $index++;
                        }
                    }
                    if ($mandatory_fields_warning) {
                        //Exit if mandatory fields not entered
                        unlink($targetpath);
                        echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
                        exit;
                    } else {
                        //Iam here now
                        //debug($arrayempdata);
                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmpDetailedAttendanceUpload->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeAttendanceUpload');
                        $arr_no_user_id = array();
                       // debug($arrayempdata);
                        foreach ($arrayempdata as $key => $row) {

                            $emp_id = trim($row['Employee ID']);
                            $c1 = $row['Direction'];
                            
                            $user_id = $emp_id;
                            if ($user_id == '') {
                              
                                $arr_no_user_id[] = $key;
                                 continue;
                            }

                            //fetch emp_fkey using user_id
                            $arr_usercredentials = $this->UserCredentials->find('first', array(
                                'fields' => 'emp_fkey',
                                'conditions' => array(
                                    'user_id' => $user_id
                                )
                            ));
                            
//                           debug($row);
                            
                            $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';

                            // Reset model state so CakePHP does INSERT not UPDATE
                            $this->EmpDetailedAttendanceUpload->create();
                            
                            $arr_empctc_data = array();
                            $arr_empctc_data['emp_attendance_upload_pkey'] = '';
                            $arr_empctc_data['attendance_type'] = 1;
                            $arr_empctc_data['emp_fkey'] = $emp_fkey;
                            $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
                            $arr_empctc_data['created_date'] = date("Y-m-d H:i:s");
                            $arr_empctc_data['c1'] = $c1;
                            
                            foreach($row as $col_name => $vals){
                                // Use $col_name (not $key) to avoid overwriting outer loop variable
                                if($col_name == 'Direction')continue;
                                if($col_name == 'Employee ID')continue;
                                if($col_name == 'Employee Name')continue;
                                if($col_name == 'Company Employee ID')continue;
                                if($col_name == '')continue;
                                if($vals == '')continue;
                                //edited by megha on 12_6_19 null value insertion
                                if($vals == 'null')continue;
                                    $vals = trim($vals);

                                    // Try parsing the col_name (date header) + vals (time) as a datetime string.
                                    // getFormattedValue() gives us readable strings (e.g. "3/1/2025" + "09:30 AM")
                                    // Try direct combination first
                                    $datetime_str = $col_name . ' ' . $vals;
                                    $timestamp = strtotime($datetime_str);

                                    // Fallback: try just the column name as a date and vals as a time separately
                                    if ($timestamp === false) {
                                        $date_only = strtotime($col_name);
                                        $time_parts = date_parse($vals);
                                        if ($date_only !== false && $time_parts && !$time_parts['errors']) {
                                            $timestamp = mktime(
                                                $time_parts['hour'] !== false ? $time_parts['hour'] : 0,
                                                $time_parts['minute'] !== false ? $time_parts['minute'] : 0,
                                                $time_parts['second'] !== false ? $time_parts['second'] : 0,
                                                date('n', $date_only),
                                                date('j', $date_only),
                                                date('Y', $date_only)
                                            );
                                        }
                                    }

                                    if ($timestamp === false || $timestamp === null) {
                                        // Skip columns that are not valid dates (e.g., "Company ID", non-date headers)
                                        continue;
                                    }

                                    $arr_empctc_data['att_date'] = date("Y-m-d", $timestamp);
                                    $arr_empctc_data['att_time'] = date("Y-m-d H:i:s", $timestamp);
                                    // Reset model id for each new date row insert
                                    $this->EmpDetailedAttendanceUpload->create();
                                    $result1 = $this->EmpDetailedAttendanceUpload->save($arr_empctc_data);
//                                } catch (Exception $ex) {
//
//                                }
                                

                                    
                                
                            }
                            
                            
                        }
                    }
                    unlink($targetpath);
                    if ($result1) {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee Attendance Uploaded '));
                        exit;
                    } else {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee Attendance Uploaded '));
                        exit;
                    }
                } else {
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Attendance import failed, no data found!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Attendance revision failed, no data found!'));
                        exit;
                    }
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Attendance import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Attendance revision failed!'));
                    exit;
                }
            }
        } else {
            if ($ctcuploadtype == 1) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Attendance import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Attendance revision failed!'));
                exit;
            }
            exit;
        }
    }

}
