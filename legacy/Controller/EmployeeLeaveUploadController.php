<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class EmployeeLeaveUploadController extends AppController {

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'EmployeeLeaveUpload';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'EmployeeLeaveUpload', 'EmployeeLeaveTransaction', 'LeaveRequests', 'SalaryHeadItems', 'EmployeeDetails', 'Units', 'FinancialYear', 'LeaveEntries', 'EmployeeInfo','LeavePolicy');
    public $components = array('DatatablesManagement');

    public function index()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeInfo->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $emp_pkey = $this->Session->read('emp_fkey');
        //$arr_leavetypes = $this->SalaryHeadItems->query("select salaLeaveRequestsry_head_item_pkey,item from salary_head_items where head_fkey=6 and upper(value)='Y'");
        $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey" => 6, "value" => "Y", "status" => 1)));
        $this->set("arr_leavetypes", $arr_leavetypes);
        $arr_branches = $this->Units->find("all", array("conditions" => array('status' => 1)));
        if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            $this->set('is_ho', $is_ho);
            if ($is_ho != 1) {
                $arr_branches = $this->Units->find("all", array(
                    "conditions" => array(
                        'status' => 1,
                        'branch_code' => $is_ho // Only fetch the current branch
                    )
                ));
            }
        }
        $this->set("arr_branches", $arr_branches);
         //edited by athira on  21-09-2025
         $this->set("company_code", $company_code);
         //end
        $arr_employees = $this->EmployeeInfo->find("all");
        $this->set("arr_employees", $arr_employees);
    }

//branch name
    public function branchwiss($branch = '') {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($branch != null) {
            $branch_condition = "and branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select * from emp_details where status = 1 $branch_condition");
        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }

    public function form($emp_leave_upload_pkey = "")
    {
        $this->layout = null;
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //$leavebalance = isset($arr_leavebalance[0][0]['leave_balance'])?$arr_leavebalance[0][0]['leave_balance']:0;
        $joins  = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        //debug($arr_branches);

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        // Edited by Akshay on 11-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $company_code = $this->Session->read('company_code');

        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $conditions = array("branch_code" => $is_ho, "status" => 1);
            } else {
                $conditions = array("status" => 1);
            }
        }else
        // End
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
              if ($company_code == 'WHOO') {
            $conditions = array("status" => 1);
        }else{
            $conditions = array("branch_code" => $cur_emp_branch, "status" => 1);
        }
        } else {
            $conditions = array("status" => 1);
        }
        //employee branch wise sorting ends here


        $arr_employees = $this->EmployeeDetails->find("all", array('fields' => 'EmployeeDetails.*,EmployeeProfessionalDetails.emp_company_id,', 'conditions' => $conditions, 'joins' => $joins));
        $this->set("arr_employees", $arr_employees);
        $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave'
        and value='Y' and status=1)")));
        $this->set("arr_leavetypes", $arr_leavetypes);
        $arr_leave_details = $this->LeaveRequests->find("all");
        if (isset($arr_leave_details[0]['LeaveRequests']['FROMHALF'])) {
            if ($arr_leave_details[0]['LeaveRequests']['FROMHALF'] == 1) {
                $arr_leave_details[0]['LeaveRequests']['FROMHALF'] = 'First Half';
            } else if ($arr_leave_details[0]['LeaveRequests']['FROMHALF'] == 2) {
                $arr_leave_details[0]['LeaveRequests']['FROMHALF'] = 'Second Half';
            }
        }
        if (isset($arr_leave_details[0]['LeaveRequests']['TOHALF'])) {
            if ($arr_leave_details[0]['LeaveRequests']['TOHALF'] == 1) {
                $arr_leave_details[0]['LeaveRequests']['TOHALF'] = 'First Half';
            } else if ($arr_leave_details[0]['LeaveRequests']['TOHALF'] == 2) {
                $arr_leave_details[0]['LeaveRequests']['TOHALF'] = 'Second Half';
            }
        }
        $this->set("arr_leave_details", $arr_leave_details);

        // debug($arr_leave_details);
        $data['emp_leave_upload_pkey'] = 0;
        $data['emp_fkey'] = ""; //$this->Session->read('emp_fkey');
        $data['leave_type'] = "";
        $data['leave_start_date'] = "";
        $data['leave_start_session'] = "";
        $data['leave_end_date'] = "";
        $data['leave_end_session'] = "";
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
        if (isset($emp_leave_upload_pkey) && $emp_leave_upload_pkey != "") {
            $data_db = $this->EmployeeLeaveUpload->find("first", array("conditions" => array("emp_leave_upload_pkey" => $emp_leave_upload_pkey)));
            $data = $data_db['EmployeeLeaveUpload'];
        }
        $this->set("data", $data);

        $arr_leave_details = $this->LeaveRequests->find("first", array(
            'fields' => 'salary_head_item_fkey,EMP_fkey',
            'conditions' => array('EMP_fkey' => $data['emp_fkey'])
        ));
        $salary_head_item_fkey = isset($arr_leave_details['LeaveRequests']['salary_head_item_fkey']) ? $arr_leave_details['LeaveRequests']['salary_head_item_fkey'] : 0;
        $emp_fkey = isset($arr_leave_details['LeaveRequests']['EMP_fkey']) ? $arr_leave_details['LeaveRequests']['EMP_fkey'] : 0;
        //        $year=$this->EmployeeLeaveUpload->query("SELECT fin_year  FROM  fin_year where  current_date between start_month and end_month");       
        //        $y=$year[0]['fin_year']['fin_year'];       
        //        $emp=$this->Session->read('emp_fkey');
        //        $leavebalance=$this->EmployeeLeaveUpload->query("SELECT `leave_balance_inthe_year_fn`($emp_fkey,$salary_head_item_fkey, $y)");        
        //        $this->set('leavebalance',$leavebalance);
        //        debug($leavebalance);

        // echo $emp_fkey;
        //echo $salary_head_item_fkey;
        //debug($leavebalance);
    }

    // edited by athira on 02-10-2025
public function getEmployeeDates() {
    $this->autoRender = false; // we’ll return JSON
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    $emp_fkey = $_REQUEST['emp_fkey']; // or ->getData() if POST

    // Get joining date
    $joining_date = $this->EmployeeDetails->query("
        SELECT joining_date 
        FROM emp_proff 
        WHERE emp_fkey='$emp_fkey'
    ");
    $joining_date = isset($joining_date[0]['emp_proff']['joining_date']) 
        ? $joining_date[0]['emp_proff']['joining_date'] 
        : '';

    // Get termination date
     $termination = $this->EmployeeDetails->query("
        SELECT last_approved_working_date 
        FROM termination LEFT JOIN emp_details ON (emp_details.emp_pkey = termination.emp_fkey)
        WHERE termination.emp_fkey='$emp_fkey'  AND emp_details.status=2
    ");

    $termination_date = !empty($termination[0]['termination']['last_approved_working_date']) 
        ? $termination[0]['termination']['last_approved_working_date'] 
        : null;

    // Return JSON
    echo json_encode([
        'joining_date' => $joining_date,
        'termination_date' => $termination_date
    ]);
}
// end

    public function leavesave() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $this->request->data;
        
        /* save data to EmployeeLeaveUpload */

        $data['emp_leave_upload_pkey'] = $arr_form_data['emp_leave_upload_pkey'];
        $data['leave_type'] = $arr_form_data['leave_type'];
        $data['emp_fkey'] = $arr_form_data['emp_fkey1'];
        $data['status'] = 1;
        $leavefromtimestamp = strtotime($arr_form_data['leave_start_date']);
        $leavetotimestamp = strtotime($arr_form_data['leave_end_date']);
        $data['leave_start_date'] = date('Y-m-d H:i:s', $leavefromtimestamp);
        $data['leave_start_session'] = $arr_form_data['leave_start_session'];
        $data['leave_end_date'] = date('Y-m-d H:i:s', $leavetotimestamp);
        $data['leave_end_session'] = $arr_form_data['leave_end_session'];
        $data['created_by'] = $this->Session->read('user_name');
          //added by megha approved remarks 28_11_19
        $data['Autherized_date'] = date('Y-m-d');
        $data['REMARKS'] = 'Leave approved from Leave Upload';
        $data['APPROVED_date'] = date('Y-m-d');
        $data['AuthoriseRemarks'] = 'Leave authorised from Leave Upload ';
        $data['ApproveRemarks'] = 'Leave approved from Leave Upload';
        $from_date = date("Y-m-d",$leavefromtimestamp);
        
        $to_date = date("Y-m-d",$leavetotimestamp);
        //edited by megha calculation of leavedays
        $salary_head_item_fkey =  $arr_form_data['leave_type'];
                  $arr_item = $this->LeaveRequests->query("SELECT item_part from  salary_head_items where salary_head_item_pkey = '$salary_head_item_fkey'");
                  $item_part = $arr_item['0']['salary_head_items']['item_part'];
	$diff = $leavetotimestamp - $leavefromtimestamp;
        $dif = round($diff / 86400);
        $leavedays = $dif + 1;
        if($arr_form_data['leave_start_session'] == 1 && $arr_form_data['leave_end_session'] == 1){
            $leavedays = $leavedays - 0.5;
        }
        else if($arr_form_data['leave_start_session'] == 2 && $arr_form_data['leave_end_session'] == 2){
            $leavedays = $leavedays - 0.5;
        }
        else if($arr_form_data['leave_start_session'] == 2 && $arr_form_data['leave_end_session'] == 1){
            $leavedays = $leavedays - 1;
        }  
        $data['leave_days'] =$leavedays;
        //ends megha calculation of leavedays
        $cur_emp_key = $emp = $arr_form_data['emp_fkey1'];
        //edited by megha on 5_7_19 Half day Leave 
        //$arr_count_leave_exists = $this->LeaveRequests->query("select count(*) AS COUNT from emp_leave_transactions where leave_date between '$from_date' and '$to_date' and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = $emp )");
        $arr_count_leave_exists = $this->LeaveRequests->query("select count(*) AS COUNT from emp_leave_transactions where leave_date between '$from_date' and '$to_date' and leave_session = 3 and Leavestatus in ('Approved','Applied','Authorized') and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = $emp )");
//        debug("select count(*) AS COUNT from emp_leave_transactions where leave_date between '$from_date' and '$to_date' and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = $emp )");
        $countleave = isset($arr_count_leave_exists[0][0]['COUNT']) ? $arr_count_leave_exists[0][0]['COUNT'] : 0;
        if ($countleave > 0) {
            $resp["success"] = false;
            $resp["msg"] = "Leave already existing in the range $from_date - $to_date. Please remove it, before applying.";
            return json_encode($resp);
        }
        
// Checking for attendance exists -- Added By Nimisha 17/05/2019
//        $arr_count_atte_exists = $this->LeaveRequests->query("SELECT count(*) AS COUNT FROM emp_detail_timeattandance WHERE emp_pkey = '$emp' and  att_date between '$from_date' and '$to_date' and present = 'P/P' ");
//         $countatt = isset($arr_count_atte_exists[0][0]['COUNT']) ? $arr_count_atte_exists[0][0]['COUNT'] : 0;
// //        if($leavestatus != 'Applied'){
//             if ($countatt > 0) {
//                 $resp["msg"] = "Attendance already existing in the range of these Leave . Please remove it, before applying.";
//                 $resp["success"] = false;
//                 return json_encode($resp);
//             }

        // Checking for attendance exists -- Edited by athira on 22-05-2026
        $hasPunch = $this->checkAttendancePunches($emp, $from_date, $to_date, $arr_form_data['leave_start_session'], $arr_form_data['leave_end_session']);
        if ($hasPunch) {
            if ($hasPunch == 1) {
                $msg = "Leave cannot be applied, attendance exists for the first half. Apply leave for next halves";
            } elseif ($hasPunch == 2) {
                $msg = "Leave cannot be applied, attendance exists for the second half. Apply leave for next halves";
            } else {
                $msg = "Leave cannot be applied, attendance exists for full day";
            }
            $resp["msg"] = $msg;
            $resp["message"] = $msg;
            $resp["success"] = false;
            return json_encode($resp);
        }
        //ended by athira on 22-05-2026
        //edited by megha on 08_04_2020 attendance register date range change
	    $month  = date('m', strtotime($from_date));
		$year  = date('Y', strtotime($from_date));
		$month1 = $year.'-'.$month.'-01';
        $att_startdate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");  
        $att_startdate1 = $att_startdate['0']['0']['monthly_att_fromdate'];
        $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];
//	if (($from_date >= $att_startdate1) && ($from_date <= $att_enddate1)){
//        $arr_attendance_register= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$from_date','%Y-%m') and isdelete='N' and emp_fkey= '$emp' ");
//        }else{
//        $arr_attendance_register['0']['0']['cnt'] = 0;
//        }
        if (($from_date >= $att_startdate1) && ($to_date <= $att_enddate1)){
        $yearmonth = date('Y-m-d', strtotime($att_enddate1));
        $arr_attendance_register= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth','%Y-%m') and isdelete='N' and emp_fkey= '$cur_emp_key' ");
        $count = $arr_attendance_register['0']['0']['cnt'];
        }else if (($from_date <= $att_enddate1) && ($to_date >= $att_enddate1)){
        $yearmonth = date('Y-m-d', strtotime($att_enddate1));
        $arr_attendance_register= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$from_date','%Y-%m') and isdelete='N' and emp_fkey= '$cur_emp_key' ");
        $yearmonth1 = date('Y-m-d', strtotime($from_date.' + 1 months'));
        $arr_attendance_register1= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth1','%Y-%m') and isdelete='N' and emp_fkey= '$cur_emp_key' ");
        $count = $arr_attendance_register['0']['0']['cnt'] + $arr_attendance_register1['0']['0']['cnt'];
        }else if(($from_date >= $att_enddate1) && ($to_date >= $att_enddate1)){
        $yearmonth = date('Y-m-d', strtotime($att_enddate1.' + 1 months'));
        $arr_attendance_register= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth','%Y-%m') and isdelete='N' and emp_fkey= '$cur_emp_key' ");
        $count = $arr_attendance_register['0']['0']['cnt'];
        }else if(($from_date <= $att_enddate1) && ($to_date <= $att_enddate1)){
        $yearmonth = date('Y-m-d', strtotime($att_enddate1));
        $arr_attendance_register= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth','%Y-%m') and isdelete='N' and emp_fkey= '$cur_emp_key' ");
        $count = $arr_attendance_register['0']['0']['cnt'];
        }else{
        $count = 0;    
        }
           //end  attendance register date range change
         //   $arr_attendance_register= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$from_date','%Y-%m') and isdelete='N' and emp_fkey= '$emp' ");
           if(isset($count) && $count > 0){
                    $resp['msg'] = "Leave Can not be saved , Attendance Verified for this Month ";
                    $resp["success"] = false; 
                   return json_encode($resp);
                } 
                 //edited by megha on 5_7_19 Indirect leave item
//        if($item_part != 'Indirect'){
//         //Weekoff
//             $arr_checkweekoff = $this->LeaveRequests->query("SELECT count(*) AS COUNT FROM emp_detail_timeattandance WHERE emp_pkey = '$emp' and  att_date between '$from_date' and '$to_date' and weekoff= 'WO'");
//                $countweek = isset($arr_checkweekoff[0][0]['COUNT']) ? $arr_checkweekoff[0][0]['COUNT'] : 0;
//                if ($countweek > 0) {
//                    $resp["msg"] = "Weeekoff already existing in the range of these Leave(s) . Please remove it, before applying.";
//                    $resp["success"] = false;
//                     return json_encode($resp);
//                }
//        //Check FOR HOLIDAY------------------------------------//
//                $arr_checkholiday = $this->LeaveRequests->query("SELECT count(*) AS COUNT FROM emp_detail_timeattandance WHERE emp_pkey = '$emp' and  att_date between '$from_date' and '$to_date' and holiday= 'HO' ");
//                $countholi = isset($arr_checkholiday[0][0]['COUNT']) ? $arr_checkholiday[0][0]['COUNT'] : 0;
//                if ($countholi > 0) {
//                    $resp["msg"] = "Holiday already existing in the range of these Leave(s) . Please remove it, before applying.";
//                    $resp["success"] = false;
//                     return json_encode($resp);
//                }
//        }

//edited by athira on 21-09-2025
        $company_code= $this->Session->read('company_code');
    //      if ($company_code=='HDFN' || $company_code=='HDEQ'){
    //         $leave_check = $this->EmployeeLeaveUpload->query("SELECT salary_head_item_pkey FROM salary_head_items WHERE occurance ='COFF' AND item_type='LEAVE' ");
    //     $comp_off = $leave_check['0']['salary_head_items']['salary_head_item_pkey'];
    //     $emp_fkey = $data['emp_fkey'];
    //     if ($data['leave_type'] == $comp_off) {
    //         $this->EmployeeLeaveUpload->query("
    //                                             SELECT leave_half_comp_off_Allocation_fn($emp_fkey)
    //                                         ");
    //     }
    // }
        //end
        $this->EmployeeLeaveUpload->save($data);
        try{
            $emp_upload_id = $this->EmployeeLeaveUpload->getInsertID();
            
        } catch (Exception $ex) {
            $resp["success"] = false;
            $resp["msg"] = "Leave Saving Failed Please Try Again ";
            return json_encode($resp);
        }
        

        /* save data to EmployeeLeaveUpload */

        if (isset($emp_upload_id)) {

            /* save data to LeaveRequests */
             //edited by megha leavedays
            $arr_data['leave_days'] = $leavedays;
            $arr_data['salary_head_item_fkey'] = $arr_form_data['leave_type'];
            $arr_data['emp_leave_upload_fkey'] = $emp_upload_id;
            $arr_data['FROMHALF'] = $arr_form_data['leave_start_session'];
            $arr_data['LEAVESTATUS'] = 'Applied';
            $arr_data['applied_date'] = date('Y-m-d H:i:s');
            $arr_data['FROMDATE'] = date('Y-m-d H:i:s', $leavefromtimestamp);
            $arr_data['TODATE'] = date('Y-m-d H:i:s', $leavetotimestamp);
            $arr_data['TOHALF'] = $arr_form_data['leave_end_session'];
            $arr_data['EMP_fkey'] = $arr_form_data['emp_fkey1'];
            $arr_data['Reason'] = $arr_form_data['Reason'];
        //added by megha approved remarks 28_11_19
            $arr_data['Autherized_date'] = date('Y-m-d');
            $arr_data['REMARKS'] = 'Leave approved from Leave Upload';
            $arr_data['APPROVED_date'] = date('Y-m-d');
            $arr_data['AuthoriseRemarks'] = 'Leave authorised from Leave Upload';
            $arr_data['ApproveRemarks'] = 'Leave approved from Leave Upload';
            $res = $this->LeaveRequests->save($arr_data);
            $leave_entry_id = $this->LeaveRequests->getInsertID();

            /* save data to LeaveRequests */

            if (isset($leave_entry_id)) {
                /*
                 * Call procedure 'leave_transaction_prc'
                 * On Applying leave
                 * By sruthi on 29 feb 2016
                 */
                $arr_leave_details = $this->LeaveRequests->find("first", array(
                    'fields' => 'LEAVEENTRYID,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
                    'conditions' => array('LEAVEENTRYID' => $leave_entry_id)
                ));
                $outputParameter = isset($arr_leave_details['LeaveRequests']) ? $arr_leave_details['LeaveRequests'] : array();

                //Set status as applied on resubmitting the leave : On 21 Feb 2016
                $outputParameter['LEAVESTATUS'] = ($outputParameter['LEAVESTATUS'] == 'Cancelled') ? $outputParameter['LEAVESTATUS'] : 'Applied';
            
            // $outputParameter['leave_days'] = ($outputParameter['leave_days'] == 'NULL') ? '0' : '0';
                $resp["leavestatus"] = isset($outputParameter['LEAVESTATUS']) ? $outputParameter['LEAVESTATUS'] : '';

                $outputParameter['LEAVESTATUS'] = "'" . $outputParameter['LEAVESTATUS'] . "'";
                $outputParameter['FROMDATE'] = "'" . $outputParameter['FROMDATE'] . "'";
                $outputParameter['TODATE'] = "'" . $outputParameter['TODATE'] . "'";

                // CALL `leave_transaction_prc`('46', '9', '2016-03-02', '1', '2016-03-03', '1', '', 'Applied', @`Perror_message`)   
                try{
                    $out = $this->LeaveRequests->leaveTransactionPrc($outputParameter);   
					
                } catch (Exception $ex) {
                    
                    $arr_leave_message = $this->LeaveRequests->find("first", array(
                        'fields' => 'message',
                        'conditions' => array('LEAVEENTRYID' => $leave_entry_id)
                    ));

                    if (isset($arr_leave_message["LeaveRequests"]['message']) && $arr_leave_message["LeaveRequests"]['message'] != '') {
                        $resp["warningmessage"] = $arr_leave_message["LeaveRequests"]['message'];
                    } else {
                        //$resp["message"] = $message;
                        $resp["message"] = $arr_leave_message["LeaveRequests"]['message'];
                    }
                }

                if (!$out/* !== 'Successfull' */) {
                    
                }

                /* update EmployeeLeaveUpload */

                $data_arr['emp_leave_upload_pkey'] = $emp_upload_id;
                //$data_arr['emp_leaveentry_fkey'] = $leave_entry_id;
                $data_arr['leaveentry_id'] = $leave_entry_id;
                $result = $this->EmployeeLeaveUpload->save($data_arr);

                
                $arr_usercredentials = $this->UserCredentials->find('first', array(
                    'fields' => 'emp_fkey',
                    'conditions' => array(
                        'user_id' => $this->Session->read('login_user_id')
                    )
                ));
                $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '0';

                $arr_leave_details_old = $this->LeaveRequests->find("first", array(
                    'fields' => 'LEAVEENTRYID,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
                    'conditions' => array('LEAVEENTRYID' => $leave_entry_id)
                ));
               // debug($arr_leave_details_old);
                
                /* update leave entry */
                
                if($arr_leave_details_old['LeaveRequests']['LEAVESTATUS'] == 'Applied'){
                    //edited by megha on 5_7_19 indirect item
            if($item_part == 'Indirect'){
              $leaveid = $arr_leave_details_old['LeaveRequests']['LEAVEENTRYID'];
              $leavedays = $arr_leave_details_old['LeaveRequests']['leave_days'];
              $status = "Approved";
              $out = $this->LeaveRequests->query("update emp_leave_transactions set Leavestatus = '$status' where LEAVEENTRYID = $leaveid"); 
              $out = $this->LeaveRequests->query("update leaveentries set LEAVESTATUS = '$status',leave_days = $leavedays where LEAVEENTRYID = $leaveid"); 
            }else{
//                try{
                    $outputParameter = array();
                    $outputParameter[] = $arr_leave_details_old['LeaveRequests']['LEAVEENTRYID'];
                    $outputParameter[] = $arr_leave_details_old['LeaveRequests']['EMP_fkey'];
                    $outputParameter[] = "'" .$arr_leave_details_old['LeaveRequests']['FROMDATE']."'" ;
                    $outputParameter[] = $arr_leave_details_old['LeaveRequests']['FROMHALF'];
                    $outputParameter[] = "'" .$arr_leave_details_old['LeaveRequests']['TODATE']."'" ;
                    $outputParameter[] = $arr_leave_details_old['LeaveRequests']['TOHALF'];
                    $outputParameter[] = $arr_leave_details_old['LeaveRequests']['leave_days'];
                    $outputParameter[] = "'Approved'";
					 //added by megha approved remarks
                    $arr_data['Autherized_date'] = date('Y-m-d');
                    $arr_data['REMARKS'] = 'Leave approved from Leave Upload';
                    $arr_data['APPROVED_date'] = date('Y-m-d');
                    $arr_data['AuthoriseRemarks'] = 'Leave authorised from Leave Upload';
                    $arr_data['ApproveRemarks'] = 'Leave approved from Leave Upload';			
                   
                    $out = $this->LeaveRequests->leaveTransactionPrc($outputParameter);
 
//                } catch (Exception $ex) {
//                    
//                    
//                }
            }
                }

                /* update leave entry */
            }
        }
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Employee Leave Uploaded successfully";
        echo json_encode($resp);
    }

    public function load() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $data['emp_leave_upload_pkey'] = 0;
        $data['emp_fkey'] = "";
        $data['leave_type'] = "";
        $data['leave_start_date'] = "";
        $data['leave_start_session'] = "";
        $data['leave_end_date'] = "";
        $data['leave_end_session'] = "";
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
        if (isset($_REQUEST['emp_leave_upload_pkey']) && $_REQUEST['emp_leave_upload_pkey'] != 0) {
            $data_db = $this->EmployeeLeaveUpload->find("first", array("conditions" => array("emp_leave_upload_pkey" => $_REQUEST['emp_leave_upload_pkey'])));
            $data = $data_db['EmployeeLeaveUpload'];
        }
        $respdata = array('success' => true, "data" => $data);
        echo json_encode($respdata);
    }

    public function listleave() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
         // debug($arr_request_data);
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $mm = isset($arr_request_data['month']) ? $arr_request_data['month'] : '';
        if ($mm != '') {
            $month = isset($arr_request_data['month']) ? $arr_request_data['month'] : '';
         //added by megha on on 11_03_2020 changed for SH Infra
            $month1 =  $month.'-01';     
            $att_startdate = $this->EmployeeLeaveUpload->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
            $att_enddate = $this->EmployeeLeaveUpload->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");  
            $first = $att_startdate['0']['0']['monthly_att_fromdate'];
            $last = $att_enddate['0']['0']['monthly_att_todate'];
        //end
            /* $first = date('Y-m-d', strtotime($month));
            $last = date('Y-m-t', strtotime($month)); */
            $month_condition = "  and DATE_FORMAT(lu.leave_start_date,'%Y-%m-%d') BETWEEN 
                            DATE_FORMAT('$first', '%Y-%m-%d') AND LAST_DAY(DATE_FORMAT('$last', '%Y-%m-%d')) ";
        } else {
            $month_condition = '';
        }
          //debug($arr_request_data);
       
        
        //edited by megha on 6_07_19 employee id filteration
        if(!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0)
        {
        $emp_fkey = $arr_request_data['employee'];
        $emp_cond = " and lu.emp_fkey= $emp_fkey ";
        }
        else
        {
            $emp_fkey = 'emp_fkey';
            $emp_cond = " ";
        }
        
         // debug($arr_request_data['employee']);
        //  debug($arr_request_data['branch']);
        $bb = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        if ($bb != '') {
            $branch = $arr_request_data['branch'];
            $branch_code = " and ed.branch_code='$branch' ";
        } else {
            $branch_code = "";
        }
//The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
$user_group = $this->Session->read('user_group');
if ($user_group == 2) {
    $cur_emp_key = $this->Session->read("emp_fkey");
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
    $branch_code =  " and ed.branch_code='$cur_emp_branch' ";
}
//employee branch wise sorting ends here
       // Edited by Megha on 03-11-2025
        $company_code = strtoupper($this->Session->read('company_code'));
        if ($company_code == 'WHOO') {
             if ($bb != '') {
            $branch = $arr_request_data['branch'];
            $branch_code = " and ed.branch_code='$branch' ";
        } else {
            $branch_code = "";
        }
        }
        // End
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');

        $this->datatable["conditions"] = array("status" => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $counts = $this->EmployeeLeaveUpload->query("select 
                COUNT(*) 
                from emp_details ed 
                INNER join  emp_leave_upload lu on (ed.emp_pkey = lu.emp_fkey)
                INNER join  salary_head_items shi on (shi.salary_head_item_pkey = lu.leave_type)
                INNER join employee_info  ei on (ed.emp_pkey = ei.emp_pkey)
                where lu.status=1 $emp_cond $month_condition $branch_code ");
          //edited by megha on 6_07_19 employee id filteration
//                where lu.status=1 and lu.emp_fkey=$emp_fkey $month_condition $branch_code ");

        //debug($counts);
        $count = $counts[0][0]['COUNT(*)'];
        //debug($count);
        $arr_leave = $this->EmployeeLeaveUpload->query("select 
                 emp_leave_upload_pkey,created_date,leave_type,leaveentry_id,leave_start_date,leave_start_session,leave_end_date,leave_end_session,ei.employee_id,ei.EmpName,shi.item,le.Reason
                from emp_details ed 
                INNER join  emp_leave_upload lu on (ed.emp_pkey = lu.emp_fkey)
                INNER join  salary_head_items shi on (shi.salary_head_item_pkey = lu.leave_type)
                INNER join  leaveentries le on (lu.leaveentry_id= le.LEAVEENTRYID)
                INNER join employee_info  ei on (ed.emp_pkey = ei.emp_pkey)
                where lu.status=1 $emp_cond $month_condition $branch_code "
                //where lu.status=1 and lu.emp_fkey= $emp_fkey $month_condition $branch_code "
                . " ORDER BY created_date desc "
                . " limit $limit  offset $ofst ");
//        debug($arr_leave);  
        $this->set("arr_leave", $arr_leave);
        $out = array();
        //debug($arr_leave);
        $counts = count($arr_leave);
        //  debug($counts);
        $resp_leave = array();
        $resp_leave["rows"] = array();
        $baseResumeUrl = Router::url('/', true) . 'EmployeeLeaveUpload/leave';

        if ($counts > 0) {
            foreach ($arr_leave as $key => $value) {
                $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : '';
                $out['empid'] = isset($value['ei']['employee_id']) ? $value['ei']['employee_id'] : '';
                $out['type'] = isset($value['shi']['item']) ? $value['shi']['item'] : '';
                $out['emp_leave_upload_pkey'] = isset($value['lu']['emp_leave_upload_pkey']) ? $value['lu']['emp_leave_upload_pkey'] : '';
                $out['emp_fkey'] = isset($value['lu']['emp_fkey']) ? $value['lu']['emp_fkey'] : ''; 
                $out['leaveentry_id'] = isset($value['lu']['leaveentry_id']) ? $value['lu']['leaveentry_id'] : '';
                $out['created_date'] = isset($value['lu']['created_date']) ?  date('d-m-Y H:i:s', strtotime($value['lu']['created_date'])) : '';
                $out['Reason'] = isset($value['le']['Reason']) ? $value['le']['Reason'] : '';
               // $out['buttons'] = "<a onclick='leave(\"" .$out['emp_leave_upload_pkey'] . "\")'><li class='fa fa-file-pdf-o'></li></a>";
 $out['buttons'] = "<a href='" . $baseResumeUrl . "' download><li class='fa fa-file-pdf-o'></li></a>";

                $startdate = isset($value['lu']['leave_start_date']) ?date('d-m-Y', strtotime( $value['lu']['leave_start_date'])) : '';

                if ($value['lu']['leave_start_session'] == 1) {
                    $startsess  = '(FH)';
                } else if ($value['lu']['leave_start_session'] == 2) {
                    $startsess  = '(SH)';
                }
                $out['leave_start_date'] = $startdate.$startsess;
//                $out['leave_start_date'] = isset($value['lu']['leave_start_date']) ? $value['lu']['leave_start_date'] : '';
                $enddate = isset($value['lu']['leave_end_date']) ? date('d-m-Y', strtotime($value['lu']['leave_end_date'])) : '';
//                if ($value['lu']['leave_start_session'] == 1) {
//                    $out['leave_start_session'] = 'First Half';
//                } else if ($value['lu']['leave_start_session'] == 2) {
//                    $out['leave_start_session'] = 'Second Half';
//                }
                if ($value['lu']['leave_end_session'] == 1) {
                    $endsess = '(FH)';
                } else if ($value['lu']['leave_end_session'] == 2) {
                    $endsess = '(SH)';
                }
                $out['leave_end_date'] = $enddate.$endsess;
//                if ($value['lu']['leave_end_session'] == 1) {
//                    $out['leave_end_session'] = 'First Half';
//                } else if ($value['lu']['leave_end_session'] == 2) {
//                    $out['leave_end_session'] = 'Second Half';
//                }
                $resp_leave["rows"][$key] = $out;
            }
        }
        //debug($resp_leave);
        $resp_leave["total"] = $count;
        echo json_encode($resp_leave);
    }

    public function deleteleave() {
        $this->autoRender = FALSE;
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["emp_leave_upload_pkeys"])) {
            $ar_ids = explode(",", $_REQUEST["emp_leave_upload_pkeys"]);

            $this->EmployeeLeaveUpload->updateAll(array('EmployeeLeaveUpload.status' => 0), array('EmployeeLeaveUpload.emp_leave_upload_pkey' => $ar_ids));

            $result['success'] = true;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }

    /* public function attendancesave(){
      $this->autoRender = false;
      $arr_form_data = $this->request->data;
      $sessionid= $this -> Session -> read("emp_fkey");
      $arr_form_data['emp_attendance_upload_pkey']="1";
      $arr_form_data['emp_fkey']=$sessionid;
      $this->EmployeeAttendanceUpload->save($arr_form_data);
      //debug($this->EmployeeAttendanceUpload->getLastQuery());
      echo json_encode(array('msg'=>'Employee Attendance Upload  saved successfully'));

      } */
    public function getfinyear($date = null){
        $this -> autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $get_finyear = $this->LeaveRequests->query("select fin_year from fin_year where '$date' between start_month and end_month");
        $finyear = isset($get_finyear['0']['fin_year']['fin_year'])?$get_finyear['0']['fin_year']['fin_year']:date('Y');
        return $finyear;
    }
    
    public function getleave() {
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        //  $year = date('Y');


        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $_REQUEST["employee"];
        $leavebalance = $_REQUEST["type"];
        $years = $this->FinancialYear->query("select fin_year  from fin_year where lcase(Year_status)='open' and vattr1 = 0 and is_current_finyear='Y' and status=1
   and branch_code= (select branch_code from emp_details where emp_Pkey='$cur_emp_key' ) ORDER BY fin_year DESC LIMIT 1  ");
        $year = $years['0']['fin_year']['fin_year'];
        //$get_finyear = $this->LeaveRequests->query("select fin_year from fin_year where '$date' between start_month and end_month");
        //$finyear = isset($get_finyear['0']['fin_year']['fin_year'])?$get_finyear['0']['fin_year']['fin_year']:date('Y');
        
        //      debug($year);
        //$finyear = $this->getfinyear($to_date);
        //edited by athira on 21-09-2025
        $company_code= $this->Session->read('company_code');
        $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
             $leave_date="NULL";
             $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$cur_emp_key','$leavebalance','$leave_date') as LeaveBalance");
         }else{
             $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$cur_emp_key','$leavebalance','$year') as LeaveBalance");
         }
        //end
        $this->set('lbalance', $lbalance);
        $resp = $lbalance['0']['0']['LeaveBalance'];

        return isset($resp) ? $resp : '0';
        //debug($arr_request_data);
    }

    public function downloadempctcformat($ctcuploadtype = 0, $branch = '', $employee = '') {
        $this->autoRender = FALSE;

//        debug($ctcuploadtype);
//        debug($branch);
        //  debug($employee);
//        die();

        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_employee_LeaveUpload.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";
        // echo 'hi' ;die();
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);
        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

        $empctcdata = new EmployeeCTCData($ctcuploadtype);
        $emp_credentials_schema = $empctcdata->getFieldHeadings('UserCredentials');
        $emp_details_schema = $empctcdata->getFieldHeadings('EmployeeDetails');
        $emp_ctc_schema = $empctcdata->getFieldHeadings('EmployeeLeaveUpload');
        $emp_proff_schema = $empctcdata->getFieldHeadings('EmployeeProfessionalDetails');
        $emp_schema = array_merge($emp_credentials_schema, $emp_proff_schema,  $emp_details_schema, $emp_ctc_schema );


        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $leaves = $this->FinancialYear->query("select * from salary_head_items where head_fkey in(select head_pkey from  salary_heads where lcase(head_occurance)='leave') and lcase(item_type)='leave'
and value='Y' and status=1");




        /*$this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave'
and value='Y' and status=1)")));

        foreach ($arr_leavetypes as $arr_leavetypes) {
            $leave_type[] = $arr_leavetypes['SalaryHeadItems']['occurance'];
        }
        $leavetype = implode(", ", $leave_type);*/







        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(27);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(21);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(26);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
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









        $objWorkSheet = $objPHPExcel->createSheet(2);
        $objWorkSheet->getStyle('A1')->getFont()->setBold(true);
        $objWorkSheet->getColumnDimension('B')->setWidth(20);
        $objWorkSheet->getStyle('B1')->getFont()->setBold(true);
        $cll = 2;

        foreach ($leaves as $lev) {
            $cll = $cll + 1;
            $objWorkSheet->setCellValue('A' . $cll, $lev['salary_head_items']['item'] . ' = ' . $lev['salary_head_items']['occurance']);
        }

//            $objPHPExcel->getActiveSheet()->setCellValue('M8', 'Leave Type');
//            $objPHPExcel->getActiveSheet()->setCellValue('M9', 'Sick Leave = SL');
//            $objPHPExcel->getActiveSheet()->setCellValue('M10', 'Earned Leave = EL');
//            $objPHPExcel->getActiveSheet()->setCellValue('M11', 'Casual Leave =Cl');
//            $objPHPExcel->getActiveSheet()->setCellValue('M12', 'Privilege Leave = PL');
        $objWorkSheet->setCellValue('A1', ' Basic Leave Type');
        $objWorkSheet->setCellValue('B1', 'Leave Session');
        $objWorkSheet->setCellValue('B2', 'First Half = 1');
        $objWorkSheet->setCellValue('B3', 'Second Half = 2');
        $objWorkSheet->setTitle('Help');
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

        //debug($employee);
        // debug($branch);


        $cond = '';
        if (isset($branch) && !empty($branch) && $branch != 'null') {

            $cond.= " AND  EmployeeDetails.branch_code= if('$branch' in(null,''),EmployeeDetails.Branch_code,'$branch') ";
        }

        //debug($cond);

        if (isset($employee) && !empty($employee) && $employee != 'null') {

            $cond.= " AND  EmployeeDetails.emp_pkey= if('$employee' in(null,''),EmployeeDetails.emp_pkey, '$employee' )";
        }

        // debug($cond);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_empdetails = $this->EmployeeDetails->query(" SELECT UserCredentials.user_id,EmployeeInfo.employee_id, EmployeeInfo.EmpName FROM emp_details AS EmployeeDetails "
                . " INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) "
                . " INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) "
                . " WHERE EmployeeDetails.status = ' 1 '   "
                . " $cond ");
        // . " "
        //. "ORDER BY (CONCAT(EmployeeDetails.first_name, ' ' , EmployeeDetails.last_name)) ASC "
        //. " ");
//        $arr_empdetails = $this->EmployeeDetails->find('all', array(
////                                    'fields'=>'EmployeeInfo.employee_id,UserCredentials.user_id,EmployeeDetails.first_name,EmployeeDetails.middile_name,EmployeeDetails.last_name',
//
//            'fields' => 'EmployeeInfo.employee_id,EmployeeInfo.EmpName',
//            //'fields'=>"'".implode(',',$emp_fields)."'",
//            'joins' => array(
//                array(
//                    'table' => 'user_credentials',
//                    'alias' => 'UserCredentials',
//                    'type' => 'INNER',
//                    'foreignKey' => false,
//                    'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
//                ),
//                array(
//                    'table' => 'employee_info',
//                    'alias' => 'EmployeeInfo',
//                    'type' => 'INNER',
//                    'foreignKey' => false,
//                    'conditions' => array('EmployeeInfo.emp_fkey = UserCredentials.emp_fkey')
//                )
//            ),
//            'conditions' => $cond
//        ));
        //debug($arr_empdetails);



        $blockNames = array(1, 2);
        $blocksList = implode(", ", $blockNames);
        $rowindex = 2;
        $columnindex = 0;
        foreach ($arr_empdetails as $rows) {
            $columnindex = 0;
            
            /*
             * Filter leave types based on employee
             * On 15 Oct 2016
             */
            $leave_type = array();
            $emp_company_id = isset($rows['EmployeeInfo']['employee_id'])?$rows['EmployeeInfo']['employee_id']:'';
            if(!empty($emp_company_id)){
                $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
//                $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where leavepolicy.status = '1' and LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_company_id='$emp_company_id'))")));
//                foreach ($arr_leavetypes as $arr_leavetypes) {
//                    $leave_type[] = $arr_leavetypes['SalaryHeadItems']['occurance'];
//                }
                //edited by megha on 5_7_19 leave indirect items start
               $arr_leavetypes = array();
               $arr_leavetypes[] = $this->SalaryHeadItems->find("all",array("conditions" => array("head_fkey in(select head_pkey from  
                salary_heads where lcase(head_occurance)='leave' and status=1) and salary_head_item_pkey IN(select salary_head_item_fkey 
                from leavepolicy where leavepolicy.status = '1' and LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID 
                      FROM emp_proff WHERE emp_company_id='$emp_company_id' ))")));
               $arr_leavetypes[] = $this->SalaryHeadItems->find("all",array("conditions" => array(" item_part='Indirect' and value='Y' and item_type ='LEAVE'")));
                foreach ($arr_leavetypes as $arr_leavetypes1) {
                 foreach ($arr_leavetypes1 as $arr_leavetypes2) {
                    $leave_type[] = $arr_leavetypes2['SalaryHeadItems']['occurance'];
                 }
                }
                //edited by megha on 5_7_19 leave indirect items ends
                $leavetype = implode(", ", $leave_type);
            }
            //Ends
            
            $objValidation = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3, $rowindex)->getDataValidation();
            $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            $objValidation->setAllowBlank(true);
            $objValidation->setShowDropDown(true);
            $objValidation->setErrorTitle('Input error');
            $objValidation->setError('Value is not in list');
            $objValidation->setFormula1('"' . $leavetype . '"');

            $objValidation = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(5, $rowindex)->getDataValidation();
            $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            $objValidation->setAllowBlank(true);
            $objValidation->setShowDropDown(true);
            $objValidation->setErrorTitle('Input error');
            $objValidation->setError('Value is not in list');
            $objValidation->setFormula1('"' . $blocksList . '"');

            $objValidation = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(7, $rowindex)->getDataValidation();
            $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            $objValidation->setAllowBlank(true);
            $objValidation->setShowDropDown(true);
            $objValidation->setErrorTitle('Input error');
            $objValidation->setError('Value is not in list');
            $objValidation->setFormula1('"' . $blocksList . '"');
            
            foreach ($rows as $columns) {
                foreach ($columns as $column) {
                    
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $column);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $columnindex++;
                    
                }
            }
            $rowindex++;
        }
        
        /*$rowindex = 2;
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
        }*/

        $objPHPExcel->getActiveSheet()->setTitle('Employee Leave Data ');

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    // public function uploadandsaveempctc($ctcuploadtype = 0)
    // {
    //     $this->autoRender = FALSE;
    //     if ($ctcuploadtype != 0) {
    //         $authuser['company_code'] = $this->Session->read('company_code');
    //         $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_empattendance_' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
    //         $targetpath = getcwd() . "/files/" . $filename;
    //         if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

    //             App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

    //             $objReader = new PHPExcel_Reader_Excel2007();
    //             $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

    //             $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
    //             $lastColumn++;
    //             $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
    //             //echo $highestRowIndex;die();
    //             $arrayempdata = array();
    //             $mandatory_fields_warning = FALSE;
    //             if ($highestRowIndex > 1) {
    //                 $intImportedCount = 0;
    //                 //atleast one employee records found
    //                 $index = 0;
    //                 for ($row = 1; $row <= $highestRowIndex; $row++) {
    //                     if ($row == 1) {
    //                         //Get mandatory headings array here
    //                         $array_mandatory_columns = array();
    //                         $array_mandatory_column_names = array('Employee ID', 'Employee Name');
    //                         //   $array_mandatory_column_names    =   array('User ID');
    //                         for ($col = 'A'; $col != $lastColumn; $col++) {

    //                             $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
    //                             //debug($value);
    //                             if (in_array($value, $array_mandatory_column_names)) {
    //                                 array_push($array_mandatory_columns, $col);
    //                             }
    //                         }
    //                     } else {
    //                         for ($col = 'A'; $col != $lastColumn; $col++) {
    //                             if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
    //                                 $mandatory_fields_warning = true;
    //                                 break 2;
    //                             }
    //                             $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
    //                             $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
    //                         }
    //                         $index++;
    //                     }
    //                 }
    //                 if ($mandatory_fields_warning) {
    //                     //Exit if mandatory fields not entered
    //                     unlink($targetpath);
    //                     echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
    //                     exit;
    //                 } else {

    //                     $started_time = time();

    //                     //Continue with save if mandatory field warning is not there
    //                     //Save employee ctc and return success
    //                     $this->UserCredentials->useDbConfig = $this->Session->read('ds');
    //                     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //                     $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
    //                     $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');
    //                     $this->LeaveRequests->useDbConfig = $this->Session->read('ds');

    //                     App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
    //                     $empcsvdata = new EmployeeCTCData($ctcuploadtype);
    //                     $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
    //                     $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
    //                     $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeLeaveUpload');
    //                     $employee_array = array();

    //                     $PID = $this->LeaveRequests->query("select max(PID) as PIDs from Upload_leave_errirs  ");
    //                     $PID_Key = isset($PID['0']['0']['PIDs']) ? $PID['0']['0']['PIDs'] + 1 : 0;

    //                     foreach ($arrayempdata as $key => $row) {


    //                         $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
    //                         $leavetype = isset($row['Leave Type']) ? $row['Leave Type'] : '';

    //                         if ($user_id == '') {
    //                             continue;
    //                         }
    //                         if ($leavetype == '') {
    //                             continue;
    //                         }

    //                         //Edited by Akshay on 10-5-2024
    //                         $salary_head_item_fkey = $this->getLeaveTypeByOccurance($leavetype);

    //                         //fetch emp_fkey using user_id
    //                         $arr_usercredentials = $this->UserCredentials->find('first', array(
    //                             'fields' => 'emp_fkey',
    //                             'conditions' => array(
    //                                 'user_id' => $user_id
    //                             )
    //                         ));
    //                         $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';

    //                         $arr_empctc_data = array();
    //                         $arr_empctc_data['emp_leave_upload_pkey'] = '';
    //                         $arr_empctc_data['PID'] = $PID_Key;
    //                         $arr_empctc_data['leaveentry_id'] = 0;
    //                         $arr_empctc_data['emp_fkey'] = $emp_fkey;
    //                         $arr_empctc_data['status'] = 1;
    //                         $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
    //                         //$arr_empctc_data['created_date'] = date('Y-m-d');
    //                         foreach ($arr_empctc_fields as $field => $fieldlabel) {
    //                             $fieldValue = $row[$fieldlabel];
    //                             //debug($field.' '.$fieldValue);
    //                             $arr_empctc_data[$field] = $fieldValue;
    //                         }

    //                         $start = $arr_empctc_data['leave_start_date'];
    //                         $end = $arr_empctc_data['leave_end_date'];
    //                         //Edited by Akshay on 9-12-2024
    //                         $leave_start_session = $arr_empctc_data['leave_start_session'];
    //                         $leave_end_session = $arr_empctc_data['leave_end_session'];
    //                         //End
    //                         $arr_attendance_register = $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$start','%Y-%m') and isdelete='N' and emp_fkey= '$emp_fkey' ");
    //                         if (isset($arr_attendance_register) && $arr_attendance_register['0']['0']['cnt'] != 0) {
    //                             $save_error = $this->LeaveRequests->query("INSERT INTO Upload_leave_errirs (PID,Type,emp_fkey,textd,start_date,end_date) VALUES('$PID_Key','VERIFIED','$emp_fkey','Attendance Verified !','$start','$end' ) ");
    //                             continue;
    //                         }
    //                         $arr_empctc_data['leave_type'] = $salary_head_item_fkey;


    //                         $to_date = isset($arr_empctc_data['leave_end_date']) ? $arr_empctc_data['leave_end_date'] : '';
    //                         $arr_leave_balance = $this->getLeaveBalanceForAuthOrApproval($emp_fkey, $salary_head_item_fkey, $to_date);
    //                         $leave_days = isset($arr_leave_balance['l_day']) ? $arr_leave_balance['l_day'] : 0;
    //                         $monthly_balance = isset($arr_leave_balance['month_balance']) ? $arr_leave_balance['month_balance'] : 0;
    //                         if ($monthly_balance - $leave_days < 0) {
    //                             $save_error = $this->LeaveRequests->query("INSERT INTO Upload_leave_errirs (PID,Type,emp_fkey,textd,start_date,end_date) VALUES('$PID_Key','BALANCE','$emp_fkey','No Leave Balance Available !','$start','$end' ) ");
    //                             continue;
    //                         }
    //                         //Ends


    //                         if ($start == null && $end == null) {
    //                             continue;
    //                         }
    //                         if ($start > $end) {
    //                             $save_error = $this->LeaveRequests->query("INSERT INTO Upload_leave_errirs (PID,Type,emp_fkey,textd,start_date,end_date) VALUES('$PID_Key','BALANCE','$emp_fkey','Date Validation  !','$start','$end' ) ");
    //                             continue;
    //                         }
    //                         //                            $arr_leave_check = $this->EmployeeLeaveUpload->query("Select count(*) from emp_leave_transactions 
    //                         //                                where leave_date between  '$start' and '$end'  and LEAVEENTRYID in (select LEAVEENTRYID  
    //                         //                                from leaveentries where EMP_fkey = $emp_fkey) and LEAVESTATUS in ('Applied','Approved','Authorized' ) 
    //                         //                            ");
    //                         //edit by megha
    //                         try {
    //                             //     $arr_leave_check = $this->EmployeeLeaveUpload->query("Select count(*) from emp_leave_transactions 
    //                             //     where leave_date between  '$start' and '$end'  and LEAVEENTRYID in (select LEAVEENTRYID  
    //                             //     from leaveentries where EMP_fkey = $emp_fkey) and LEAVESTATUS in ('Applied','Approved','Authorized' ) and leave_session = 3
    //                             // ");
    //                             // Edited by Akshay on 20-12-2024
    //                             $arr_leave_check = $this->EmployeeLeaveUpload->query("
    //                                                                                     SELECT count(*)
    //                                                                                         FROM emp_leave_upload
    //                                                                                         WHERE emp_fkey = '$emp_fkey'
    //                                                                                         AND (
    //                                                                                             -- Overlapping date ranges
    //                                                                                             ('$start' BETWEEN leave_start_date AND leave_end_date)
    //                                                                                             OR ('$end' BETWEEN leave_start_date AND leave_end_date)
    //                                                                                             OR (leave_start_date BETWEEN '$start' AND '$end')
    //                                                                                             OR (leave_end_date BETWEEN '$start' AND '$end')
    //                                                                                             )
    //                                                                                         AND (
    //                                                                                             -- Overlapping sessions
    //                                                                                             (leave_start_date = '$start' AND leave_start_session = '$leave_start_session')
    //                                                                                             OR (leave_end_date = '$end' AND leave_end_session = '$leave_end_session')
    //                                                                                             OR ('$start' = leave_end_date AND '$leave_start_session' = leave_end_session)
    //                                                                                             OR ('$end' = leave_start_date AND '$leave_end_session' = leave_start_session)
    //                                                                                             );
    //                                                                                 ");
    //                             // End
    //                         } catch (Exception $e) {
    //                             debug($e);
    //                         }

    //                         $data = $arr_leave_check['0']['0']['count(*)'];
    //                         if ($data != 0) {
    //                             $save_error = $this->LeaveRequests->query("INSERT INTO Upload_leave_errirs (PID,Type,emp_fkey,textd,start_date,end_date) VALUES('$PID_Key','EXISTS','$emp_fkey','Leave Already Existing !','$start','$end' ) ");
    //                             continue;
    //                         }

    //                         //Edited by Akshay on 17-5-2024
    //                         $arr_empctc_data['leave_start_date'] = date('Y-m-d', strtotime($arr_empctc_data['leave_start_date']));
    //                         $arr_empctc_data['leave_end_date'] = date('Y-m-d', strtotime($arr_empctc_data['leave_end_date']));
    //                         //End
                           

    //                         try {
    //                             $result1 = $this->EmployeeLeaveUpload->save($arr_empctc_data);
    //                         } catch (Exception $e) {
    //                             debug($e);
    //                             exit;
    //                         }
    //                         $emp_upload_id = $this->EmployeeLeaveUpload->getInsertID();

    //                         /*
    //                          * Check if leave balance is available for the employee for the leave type chosen
    //                          * On 15 Oct 2016
    //                          */

    //                         $employee_array[] = $emp_fkey;
    //                         $leavefromtimestamp = strtotime($arr_empctc_data['leave_start_date']);
    //                         $leavetotimestamp = strtotime($arr_empctc_data['leave_end_date']);
    //                         //debug($emp_upload_id);
    //                         if (isset($emp_upload_id) && isset($arr_empctc_data['leave_type'])) {
    //                             /* save data to LeaveRequests */
    //                             // $arr_data['salary_head_item_fkey']=$arr_form_data['leave_type'];

    //                             $arr_data['LEAVEENTRYID'] = '';
    //                             $arr_data['emp_leave_upload_fkey'] = $emp_upload_id;
    //                             $arr_data['salary_head_item_fkey'] = $arr_empctc_data['leave_type'];
    //                             $arr_data['LEAVESTATUS'] = 'Applied';
    //                             $arr_data['applied_date'] = date('Y-m-d H:i:s');
    //                             $arr_data['FROMDATE'] = date('Y-m-d H:i:s', $leavefromtimestamp);
    //                             $arr_data['TODATE'] = date('Y-m-d H:i:s', $leavetotimestamp);
    //                             $arr_data['FROMHALF'] = $arr_empctc_data['leave_start_session'];
    //                             $arr_data['TOHALF'] = $arr_empctc_data['leave_end_session'];
    //                             $arr_data['EMP_fkey'] = $arr_empctc_data['emp_fkey'];
    //                             //debug($arr_empctc_data['leave_type']);
    //                             $arr_data['Reason'] = isset($arr_empctc_data['Reason']) ? $arr_empctc_data['Reason'] : '';

    //                             //Edited by Akshay on 17-8-2024
    //                             $leave_start_date = date('Y-m-d', $leavefromtimestamp);
    //                             $leave_end_date = date('Y-m-d', $leavetotimestamp);
    //                             $leave_start_session = $arr_empctc_data['leave_start_session'];
    //                             $leave_end_session = $arr_empctc_data['leave_end_session'];


    //                             $res = $this->LeaveRequests->save($arr_data);
    //                             $leave_entry_id = $this->LeaveRequests->getInsertID();
    //                             //debug($arr_data);
    //                             if (isset($leave_entry_id)) {
    //                                 /*
    //                                  * Call procedure 'leave_transaction_prc'
    //                                  * On Applying leave
    //                                  * By sruthi on 29 feb 2016
    //                                  */
    //                                 $arr_leave_details = $this->LeaveRequests->find("first", array(
    //                                     'fields' => 'LEAVEENTRYID,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
    //                                     'conditions' => array('LEAVEENTRYID' => $leave_entry_id)
    //                                 ));
    //                                 $outputParameter = isset($arr_leave_details['LeaveRequests']) ? $arr_leave_details['LeaveRequests'] : array();

    //                                 //Set status as applied on resubmitting the leave : On 21 Feb 2016
    //                                 $outputParameter['LEAVESTATUS'] = ($outputParameter['LEAVESTATUS'] == 'Cancelled') ? $outputParameter['LEAVESTATUS'] : 'Applied';
    //                                 $outputParameter['leave_days'] = ($outputParameter['leave_days'] == 'NULL') ? '0' : '0';

    //                                 $resp["leavestatus"] = isset($outputParameter['LEAVESTATUS']) ? $outputParameter['LEAVESTATUS'] : '';

    //                                 $outputParameter['LEAVESTATUS'] = "'" . $outputParameter['LEAVESTATUS'] . "'";
    //                                 $outputParameter['FROMDATE'] = "'" . $outputParameter['FROMDATE'] . "'";
    //                                 $outputParameter['TODATE'] = "'" . $outputParameter['TODATE'] . "'";

    //                                 // CALL `leave_transaction_prc`('46', '9', '2016-03-02', '1', '2016-03-03', '1', '', 'Applied', @`Perror_message`)   
    //                                 // debug($outputParameter);die();

    //                                 $out = $this->LeaveRequests->leaveTransactionPrc($outputParameter);
    //                                 if (!$out/* !== 'Successfull' */) {
    //                                     $arr_leave_message = $this->LeaveRequests->find("first", array(
    //                                         'fields' => 'message',
    //                                         'conditions' => array('LEAVEENTRYID' => $leave_entry_id)
    //                                     ));

    //                                     if (isset($arr_leave_message["LeaveRequests"]['message']) && $arr_leave_message["LeaveRequests"]['message'] != '') {
    //                                         $resp["warningmessage"] = $arr_leave_message["LeaveRequests"]['message'];
    //                                     } else {
    //                                         //  $resp["message"] = $message;
    //                                         $resp["message"] = $arr_leave_message["LeaveRequests"]['message'];
    //                                     }
    //                                 }

    //                                 /* update EmployeeLeaveUpload */
    //                                 $data_arr['emp_leave_upload_pkey'] = $emp_upload_id;
    //                                 //$data_arr['emp_leaveentry_fkey'] = $leave_entry_id;
    //                                 $data_arr['leaveentry_id'] = $leave_entry_id;



    //                                 $result = $this->EmployeeLeaveUpload->save($data_arr);



    //                                 /*  EmployeeLeaveTransaction update */
    //                                 $emp_leave_transaction = $this->EmployeeLeaveTransaction->find('all', array(
    //                                     'conditions' => array(
    //                                         'LEAVEENTRYID' => $leave_entry_id
    //                                     )
    //                                 ));
    //                                 foreach ($emp_leave_transaction as $value) {
    //                                     $status = $value['EmployeeLeaveTransaction']['Leavestatus'];
    //                                     if ($status == 'Applied') {
    //                                         $data_tran['emp_leave_transactions_pkey'] = $value['EmployeeLeaveTransaction']['emp_leave_transactions_pkey'];
    //                                         $data_tran['Leavestatus'] = 'Approved';
    //                                         $this->EmployeeLeaveTransaction->save($data_tran);
    //                                     }
    //                                 }
    //                                 // die();
    //                                 //
    //                                 //fetch emp_fkey using user_id
    //                                 $arr_usercredentials = $this->UserCredentials->find('first', array(
    //                                     'fields' => 'emp_fkey',
    //                                     'conditions' => array(
    //                                         'user_id' => $this->Session->read('login_user_id')
    //                                     )
    //                                 ));
    //                                 $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';

    //                                 /* update leave entry */
    //                                 //$arr_data_final['salary_head_item_fkey']=$arr_form_data['leave_type'];
    //                                 $arr_data_final['APPROVEDBY'] = $emp_fkey;
    //                                 $arr_data_final['ISAPPROVED'] = 1;
    //                                 $arr_data_final['LEAVESTATUS'] = 'Approved';
    //                                 $arr_data_final['ISAutherizedby'] = $emp_fkey;
    //                                 $arr_data_final['ISAutherized'] = 1;
    //                                 $arr_data_final['Autherized_date'] = date('Y-m-d');
    //                                 $arr_data_final['REMARKS'] = 'Leave approved from Leave Upload excel';
    //                                 $arr_data_final['APPROVED_date'] = date('Y-m-d');
    //                                 //added by megha approved remarks 28_11_19
    //                                 $arr_data_final['AuthoriseRemarks'] = 'Leave authorised from Leave Upload excel';
    //                                 $arr_data_final['ApproveRemarks'] = 'Leave approved from Leave Upload excel';
    //                                 $res = $this->LeaveRequests->save($arr_data_final);  //debug($res);
    //                             }
    //                         }
    //                         $intImportedCount++;
    //                     }
    //                     unlink($targetpath);

    //                     if ($ctcuploadtype == 1) {
    //                         echo json_encode(array('success' => 1, 'msg' => 'Employee Leave  imported successfully', 'started_time' => $started_time, 'imported_count' => $intImportedCount, "employees" => $employee_array, "PIDS" => $PID_Key));
    //                         exit;
    //                     } else {
    //                         echo json_encode(array('success' => 1, 'msg' => 'Employee Leave revised successfully', 'started_time' => $started_time, 'imported_count' => $intImportedCount, "employees" => $employee_array, "PIDS" => $PID_Key));
    //                         exit;
    //                     }
    //                 }
    //             } else {
    //                 unlink($targetpath);
    //                 if ($ctcuploadtype == 1) {
    //                     echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Leave import failed, no data found!'));
    //                     exit;
    //                 } else {
    //                     echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Leave revision failed, no data found!'));
    //                     exit;
    //                 }
    //             }
    //         } else {
    //             if ($ctcuploadtype == 1) {
    //                 echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Leave import failed!'));
    //                 exit;
    //             } else {
    //                 echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Leave revision failed!'));
    //                 exit;
    //             }
    //         }
    //     } else {
    //         if ($ctcuploadtype == 1) {
    //             echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Leave import failed!'));
    //         } else {
    //             echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Leave revision failed!'));
    //             exit;
    //         }
    //         exit;
    //     }
    // } 

    public function uploadandsaveempctc($ctcuploadtype = 0)
    {
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
                //echo $highestRowIndex;die();
                $arrayempdata = array();
                $mandatory_fields_warning = FALSE;
                if ($highestRowIndex > 1) {
                    $intImportedCount = 0;
                    //atleast one employee records found
                    $index = 0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row == 1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('Employee ID', 'Employee Name');
                            //   $array_mandatory_column_names    =   array('User ID');
                            for ($col = 'A'; $col != $lastColumn; $col++) {

                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                //debug($value);
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                                $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
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

                        $started_time = time();

                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');
                        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeLeaveUpload');
                        $employee_array = array();

                        $PID = $this->LeaveRequests->query("select max(PID) as PIDs from Upload_leave_errirs  ");
                        $PID_Key = isset($PID['0']['0']['PIDs']) ? $PID['0']['0']['PIDs'] + 1 : 0;

                        foreach ($arrayempdata as $key => $row) {


                            $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            $leavetype = isset($row['Leave Type']) ? $row['Leave Type'] : '';

                            if ($user_id == '') {
                                continue;
                            }
                            if ($leavetype == '') {
                                continue;
                            }

                            //Edited by Akshay on 10-5-2024
                            $salary_head_item_fkey = $this->getLeaveTypeByOccurance($leavetype);

                            //fetch emp_fkey using user_id
                            $arr_usercredentials = $this->UserCredentials->find('first', array(
                                'fields' => 'emp_fkey',
                                'conditions' => array(
                                    'user_id' => $user_id
                                )
                            ));
                            $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';

                            $arr_empctc_data = array();
                            $arr_empctc_data['emp_leave_upload_pkey'] = '';
                            $arr_empctc_data['PID'] = $PID_Key;
                            $arr_empctc_data['leaveentry_id'] = 0;
                            $arr_empctc_data['emp_fkey'] = $emp_fkey;
                            $arr_empctc_data['status'] = 1;
                            $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
                            //$arr_empctc_data['created_date'] = date('Y-m-d');
                            foreach ($arr_empctc_fields as $field => $fieldlabel) {
                                $fieldValue = $row[$fieldlabel];
                                //debug($field.' '.$fieldValue);
                                $arr_empctc_data[$field] = $fieldValue;
                            }

                            $start = $arr_empctc_data['leave_start_date'];
                            $end = $arr_empctc_data['leave_end_date'];
                            //Edited by Akshay on 9-12-2024
                            $leave_start_session = $arr_empctc_data['leave_start_session'];
                            $leave_end_session = $arr_empctc_data['leave_end_session'];
                            //End
                            $arr_attendance_register = $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$start','%Y-%m') and isdelete='N' and emp_fkey= '$emp_fkey' ");
                            if (isset($arr_attendance_register) && $arr_attendance_register['0']['0']['cnt'] != 0) {
                                $save_error = $this->LeaveRequests->query("INSERT INTO Upload_leave_errirs (PID,Type,emp_fkey,textd,start_date,end_date) VALUES('$PID_Key','VERIFIED','$emp_fkey','Attendance Verified !','$start','$end' ) ");
                                continue;
                            }
                            $arr_empctc_data['leave_type'] = $salary_head_item_fkey;


                            $to_date = isset($arr_empctc_data['leave_end_date']) ? $arr_empctc_data['leave_end_date'] : '';
                            $arr_leave_balance = $this->getLeaveBalanceForAuthOrApproval($emp_fkey, $salary_head_item_fkey, $to_date);
                            $leave_days = isset($arr_leave_balance['l_day']) ? $arr_leave_balance['l_day'] : 0;
                            $monthly_balance = isset($arr_leave_balance['month_balance']) ? $arr_leave_balance['month_balance'] : 0;
                            if ($monthly_balance - $leave_days < 0) {
                                $save_error = $this->LeaveRequests->query("INSERT INTO Upload_leave_errirs (PID,Type,emp_fkey,textd,start_date,end_date) VALUES('$PID_Key','BALANCE','$emp_fkey','No Leave Balance Available !','$start','$end' ) ");
                                continue;
                            }
                            //Ends


                            if ($start == null && $end == null) {
                                continue;
                            }
                            if ($start > $end) {
                                $save_error = $this->LeaveRequests->query("INSERT INTO Upload_leave_errirs (PID,Type,emp_fkey,textd,start_date,end_date) VALUES('$PID_Key','BALANCE','$emp_fkey','Date Validation  !','$start','$end' ) ");
                                continue;
                            }
                            //                            $arr_leave_check = $this->EmployeeLeaveUpload->query("Select count(*) from emp_leave_transactions 
                            //                                where leave_date between  '$start' and '$end'  and LEAVEENTRYID in (select LEAVEENTRYID  
                            //                                from leaveentries where EMP_fkey = $emp_fkey) and LEAVESTATUS in ('Applied','Approved','Authorized' ) 
                            //                            ");
                            //edit by megha
                            try {
                                //     $arr_leave_check = $this->EmployeeLeaveUpload->query("Select count(*) from emp_leave_transactions 
                                //     where leave_date between  '$start' and '$end'  and LEAVEENTRYID in (select LEAVEENTRYID  
                                //     from leaveentries where EMP_fkey = $emp_fkey) and LEAVESTATUS in ('Applied','Approved','Authorized' ) and leave_session = 3
                                // ");
                                // Edited by Akshay on 20-12-2024
                                $arr_leave_check = $this->EmployeeLeaveUpload->query("
                                                                                        SELECT count(*)
                                                                                            FROM emp_leave_upload lu
                                                                                            INNER JOIN leaveentries le ON (lu.leaveentry_id = le.LEAVEENTRYID)
                                                                                            WHERE lu.emp_fkey = '$emp_fkey'
                                                                                            AND lu.status = 1
                                                                                            AND le.LEAVESTATUS IN ('Applied', 'Approved', 'Authorized')
                                                                                            AND (
                                                                                                -- Overlapping date ranges
                                                                                                ('$start' BETWEEN lu.leave_start_date AND lu.leave_end_date)
                                                                                                OR ('$end' BETWEEN lu.leave_start_date AND lu.leave_end_date)
                                                                                                OR (lu.leave_start_date BETWEEN '$start' AND '$end')
                                                                                                OR (lu.leave_end_date BETWEEN '$start' AND '$end')
                                                                                                )
                                                                                            AND (
                                                                                                -- Overlapping sessions
                                                                                                (lu.leave_start_date = '$start' AND lu.leave_start_session = '$leave_start_session')
                                                                                                OR (lu.leave_end_date = '$end' AND lu.leave_end_session = '$leave_end_session')
                                                                                                OR ('$start' = lu.leave_end_date AND '$leave_start_session' = lu.leave_end_session)
                                                                                                OR ('$end' = lu.leave_start_date AND '$leave_end_session' = lu.leave_start_session)
                                                                                                );
                                                                                    ");
                                // End
                            } catch (Exception $e) {
                                debug($e);
                            }

                            $data = $arr_leave_check['0']['0']['count(*)'];
                            if ($data != 0) {
                                $save_error = $this->LeaveRequests->query("INSERT INTO Upload_leave_errirs (PID,Type,emp_fkey,textd,start_date,end_date) VALUES('$PID_Key','EXISTS','$emp_fkey','Leave Already Existing !','$start','$end' ) ");
                                continue;
                            }

                            //Edited by Akshay on 17-5-2024
                            $arr_empctc_data['leave_start_date'] = date('Y-m-d', strtotime($arr_empctc_data['leave_start_date']));
                            $arr_empctc_data['leave_end_date'] = date('Y-m-d', strtotime($arr_empctc_data['leave_end_date']));
                            //End


                            try {
                                $result1 = $this->EmployeeLeaveUpload->save($arr_empctc_data);
                            } catch (Exception $e) {
                                debug($e);
                                exit;
                            }
                            $emp_upload_id = $this->EmployeeLeaveUpload->getInsertID();

                            /*
                             * Check if leave balance is available for the employee for the leave type chosen
                             * On 15 Oct 2016
                             */

                            $employee_array[] = $emp_fkey;
                            $leavefromtimestamp = strtotime($arr_empctc_data['leave_start_date']);
                            $leavetotimestamp = strtotime($arr_empctc_data['leave_end_date']);
                            //debug($emp_upload_id);
                            if (isset($emp_upload_id) && isset($arr_empctc_data['leave_type'])) {
                                /* save data to LeaveRequests */
                                // $arr_data['salary_head_item_fkey']=$arr_form_data['leave_type'];

                                $arr_data['LEAVEENTRYID'] = '';
                                $arr_data['emp_leave_upload_fkey'] = $emp_upload_id;
                                $arr_data['salary_head_item_fkey'] = $arr_empctc_data['leave_type'];
                                $arr_data['LEAVESTATUS'] = 'Applied';
                                $arr_data['applied_date'] = date('Y-m-d H:i:s');
                                $arr_data['FROMDATE'] = date('Y-m-d H:i:s', $leavefromtimestamp);
                                $arr_data['TODATE'] = date('Y-m-d H:i:s', $leavetotimestamp);
                                $arr_data['FROMHALF'] = $arr_empctc_data['leave_start_session'];
                                $arr_data['TOHALF'] = $arr_empctc_data['leave_end_session'];
                                $arr_data['EMP_fkey'] = $arr_empctc_data['emp_fkey'];
                                //debug($arr_empctc_data['leave_type']);
                                $arr_data['Reason'] = isset($arr_empctc_data['Reason']) ? $arr_empctc_data['Reason'] : '';

                                //Edited by Akshay on 17-8-2024
                                $leave_start_date = date('Y-m-d', $leavefromtimestamp);
                                $leave_end_date = date('Y-m-d', $leavetotimestamp);
                                $leave_start_session = $arr_empctc_data['leave_start_session'];
                                $leave_end_session = $arr_empctc_data['leave_end_session'];


                                $res = $this->LeaveRequests->save($arr_data);
                                $leave_entry_id = $this->LeaveRequests->getInsertID();
                                //debug($arr_data);
                                if (isset($leave_entry_id)) {
                                    /*
                                     * Call procedure 'leave_transaction_prc'
                                     * On Applying leave
                                     * By sruthi on 29 feb 2016
                                     */
                                    $arr_leave_details = $this->LeaveRequests->find("first", array(
                                        'fields' => 'LEAVEENTRYID,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
                                        'conditions' => array('LEAVEENTRYID' => $leave_entry_id)
                                    ));
                                    $outputParameter = isset($arr_leave_details['LeaveRequests']) ? $arr_leave_details['LeaveRequests'] : array();

                                    //Set status as applied on resubmitting the leave : On 21 Feb 2016
                                    $outputParameter['LEAVESTATUS'] = ($outputParameter['LEAVESTATUS'] == 'Cancelled') ? $outputParameter['LEAVESTATUS'] : 'Applied';
                                    $outputParameter['leave_days'] = ($outputParameter['leave_days'] == 'NULL') ? '0' : '0';

                                    $resp["leavestatus"] = isset($outputParameter['LEAVESTATUS']) ? $outputParameter['LEAVESTATUS'] : '';

                                    $outputParameter['LEAVESTATUS'] = "'" . $outputParameter['LEAVESTATUS'] . "'";
                                    $outputParameter['FROMDATE'] = "'" . $outputParameter['FROMDATE'] . "'";
                                    $outputParameter['TODATE'] = "'" . $outputParameter['TODATE'] . "'";

                                    // CALL `leave_transaction_prc`('46', '9', '2016-03-02', '1', '2016-03-03', '1', '', 'Applied', @`Perror_message`)   
                                    // debug($outputParameter);die();

                                    $out = $this->LeaveRequests->leaveTransactionPrc($outputParameter);
                                    if (!$out/* !== 'Successfull' */) {
                                        $arr_leave_message = $this->LeaveRequests->find("first", array(
                                            'fields' => 'message',
                                            'conditions' => array('LEAVEENTRYID' => $leave_entry_id)
                                        ));

                                        if (isset($arr_leave_message["LeaveRequests"]['message']) && $arr_leave_message["LeaveRequests"]['message'] != '') {
                                            $resp["warningmessage"] = $arr_leave_message["LeaveRequests"]['message'];
                                        } else {
                                            //  $resp["message"] = $message;
                                            $resp["message"] = $arr_leave_message["LeaveRequests"]['message'];
                                        }
                                    }

                                    /* update EmployeeLeaveUpload */
                                    $data_arr['emp_leave_upload_pkey'] = $emp_upload_id;
                                    //$data_arr['emp_leaveentry_fkey'] = $leave_entry_id;
                                    $data_arr['leaveentry_id'] = $leave_entry_id;



                                    $result = $this->EmployeeLeaveUpload->save($data_arr);



                                    /*  EmployeeLeaveTransaction update */
                                    $emp_leave_transaction = $this->EmployeeLeaveTransaction->find('all', array(
                                        'conditions' => array(
                                            'LEAVEENTRYID' => $leave_entry_id
                                        )
                                    ));
                                    foreach ($emp_leave_transaction as $value) {
                                        $status = $value['EmployeeLeaveTransaction']['Leavestatus'];
                                        if ($status == 'Applied') {
                                            $data_tran['emp_leave_transactions_pkey'] = $value['EmployeeLeaveTransaction']['emp_leave_transactions_pkey'];
                                            $data_tran['Leavestatus'] = 'Approved';
                                            $this->EmployeeLeaveTransaction->save($data_tran);
                                        }
                                    }
                                    // die();
                                    //
                                    //fetch emp_fkey using user_id
                                    $arr_usercredentials = $this->UserCredentials->find('first', array(
                                        'fields' => 'emp_fkey',
                                        'conditions' => array(
                                            'user_id' => $this->Session->read('login_user_id')
                                        )
                                    ));
                                    $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';

                                    /* update leave entry */
                                    //$arr_data_final['salary_head_item_fkey']=$arr_form_data['leave_type'];
                                    $arr_data_final['APPROVEDBY'] = $emp_fkey;
                                    $arr_data_final['ISAPPROVED'] = 1;
                                    $arr_data_final['LEAVESTATUS'] = 'Approved';
                                    $arr_data_final['ISAutherizedby'] = $emp_fkey;
                                    $arr_data_final['ISAutherized'] = 1;
                                    $arr_data_final['Autherized_date'] = date('Y-m-d');
                                    $arr_data_final['REMARKS'] = 'Leave approved from Leave Upload excel';
                                    $arr_data_final['APPROVED_date'] = date('Y-m-d');
                                    //added by megha approved remarks 28_11_19
                                    $arr_data_final['AuthoriseRemarks'] = 'Leave authorised from Leave Upload excel';
                                    $arr_data_final['ApproveRemarks'] = 'Leave approved from Leave Upload excel';
                                    $res = $this->LeaveRequests->save($arr_data_final);  //debug($res);
                                }
                            }
                            $intImportedCount++;
                        }
                        unlink($targetpath);

                        if ($ctcuploadtype == 1) {
                            echo json_encode(array('success' => 1, 'msg' => 'Employee Leave  imported successfully', 'started_time' => $started_time, 'imported_count' => $intImportedCount, "employees" => $employee_array, "PIDS" => $PID_Key));
                            exit;
                        } else {
                            echo json_encode(array('success' => 1, 'msg' => 'Employee Leave revised successfully', 'started_time' => $started_time, 'imported_count' => $intImportedCount, "employees" => $employee_array, "PIDS" => $PID_Key));
                            exit;
                        }
                    }
                } else {
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Leave import failed, no data found!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Leave revision failed, no data found!'));
                        exit;
                    }
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Leave import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Leave revision failed!'));
                    exit;
                }
            }
        } else {
            if ($ctcuploadtype == 1) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Leave import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Leave revision failed!'));
                exit;
            }
            exit;
        }
    }
    
    /*
     * Show leave balance
     * On 15 oct 2016
     */
    /*public function getleavebalance($cur_emp_key, $leavebalance) {

        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->query("select fin_year from fin_year where Year_status = 'OPEN' and is_current_finyear = 'Y' and status = '1'");
        $year = $years['0']['fin_year']['fin_year'];
        $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$cur_emp_key','$leavebalance','$year') as LeaveBalance");

        $this->set('lbalance', $lbalance);
        $resp = $lbalance['0']['0']['LeaveBalance'];

        return isset($resp) ? $resp : 0;
    }*/
     public function GetLeaveBalance($salary_head_item_fkey = "",$emp_fkey="",$end_date="") {
         $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
          //edited by megha on 7/4/19 Indirect Leaves
            $arr_item = $this->LeaveRequests->query("SELECT item_part from  salary_head_items where salary_head_item_pkey = '$salary_head_item_fkey'");
            $item_part = $arr_item['0']['salary_head_items']['item_part'];
            
            if($item_part == 'Indirect'){
            return json_encode(array(
                'yearly_balance'=>365
             ));
         }else{
              //edited by megha on 7/4/19 Indirect Leaves
        if(!empty($salary_head_item_fkey) && !empty($emp_fkey)){
            
            $year = date('Y');
              //edited by megha on 07/08/2019 passing today date into leave balance month function   
//            if ($end_date != '') {
//                $months = date("Y-m-1", strtotime($end_date));
//            } 
//            else $months = date("Y-m-1");
            if ($end_date != '') {
                $months = date("Y-m-d", strtotime($end_date));
            } 
            else $months = date("Y-m-d");

            $get_finyear = $this->LeaveRequests->query("select fin_year  from fin_year where lcase(Year_status)='open' and vattr1 = 0 and is_current_finyear='Y' and status=1
            and branch_code= (select branch_code from emp_details where emp_Pkey='$emp_fkey' ) ORDER BY fin_year DESC LIMIT 1 ");
            $finyear = isset($get_finyear['0']['fin_year']['fin_year'])?$get_finyear['0']['fin_year']['fin_year']:date('Y');
            $encash = $this->LeaveRequests->query("select approved_days FROM leave_encashment_master  WHERE salary_head_item_fkey = '$salary_head_item_fkey' and is_approved = 'Y' AND status = 1 AND emp_fkey = '$emp_fkey' AND fin_year='$finyear' ORDER BY creation_date LIMIT 1");
			$encashleave = isset($encash['0']['leave_encashment_master']['approved_days'])?$encash['0']['leave_encashment_master']['approved_days']:0;
            $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$emp_fkey','$salary_head_item_fkey','$finyear') as LeaveBalance");
            $yearly_balance = isset($lbalance['0']['0']['LeaveBalance']) ? $lbalance['0']['0']['LeaveBalance'] : 0;
            
            $lmonthbalance = $this->LeaveRequests->query("select leave_balance_inthe_month_fn('$emp_fkey','$salary_head_item_fkey','$months','$finyear') as LeaveBalancemonth");
            $monthly_balance = isset($lmonthbalance['0']['0']['LeaveBalancemonth']) ? $lmonthbalance['0']['0']['LeaveBalancemonth'] : 0;
            
            //Check if leave is isnegative
            //On 09 Oct 2016
            $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
            $arr_leave_policy =  $this -> LeavePolicy ->find("all",array(
                'fields' => 'ALLOW_NEGETIVE',
                'conditions' => array(
                        'salary_head_item_fkey'=>$salary_head_item_fkey,
                        'LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='.$emp_fkey.')'
                    )
                )
            );
            // debug($arr_leave_policy);
            // exit;
            $allow_negative = isset($arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE'])?$arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE']:'';
            //Ends

            return json_encode(array(
                'yearly_balance'=>$yearly_balance,
                'monthly_balance'=>$monthly_balance,
                'allow_negative'=>strtoupper($allow_negative)
            ));
//            return json_encode(array(
//                'yearly_balance'=>12,
//                'monthly_balance'=>0,
//                'allow_negative'=>"N"
//            ));
           } //edited by megha on 7/4/19 Indirect Leaves
         }
    }
    //Ends

    public function getLeaveTypeByOccurance($type) {

        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        // $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey" => 6, "value" => "Y", "status" => 1, "item" => $type)));
        //Edited by Akshay on 13-5-2024
        //edited by athira on 10-11-2025
        $company_code=$this->Session->read('company_code');
        if($company_code=='STFR'){
            $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey" => 6, "value" => "Y", "status" => 1, "occurance" => $type)));
        }
        else{
         $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey" => 6, "value" => "Y", "status" => 1, "occurance" => $type, "item_part" => 'Direct')));
        }
        //end
        $leave_type = isset($arr_leavetypes[0]['SalaryHeadItems']['salary_head_item_pkey']) ? $arr_leavetypes[0]['SalaryHeadItems']['salary_head_item_pkey'] : NULL;
        return $leave_type;
    }
    
    public function Loadingform(){
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
    }

    public function getLeaveType($emp_fkey="") {     
        $this -> autoRender = FALSE;
        $arr_leave_type = array();
        $this -> SalaryHeadItems -> useDbConfig = $this -> Session -> read('ds');
        
        //On 09 Oct 2016
        //$arr_leave_type = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1)")));
        $emp_fkey = empty($emp_fkey)?$this -> Session -> read("emp_fkey"):$emp_fkey;
//        $arr_leave_type = $this->SalaryHeadItems->find("all", array(
//            "conditions" => array(
//                "head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where status = 1 and LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_fkey'))"
//            )
//        ));
          
            //edited by athira on 21-09-2025
            $company_code=$this->Session->read('company_code');
            $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
           $arr_leave = $this->SalaryHeadItems->query("SELECT `SalaryHeadItems`.`salary_head_item_pkey`, `SalaryHeadItems`.`head_fkey`, `SalaryHeadItems`.`item`, `SalaryHeadItems`.`item_type`,
          `SalaryHeadItems`.`item_value`, `SalaryHeadItems`.`occurance`, `SalaryHeadItems`.`start_from`, `SalaryHeadItems`.`comments`, 
          `SalaryHeadItems`.`value`, `SalaryHeadItems`.`is_show_salslip`, `SalaryHeadItems`.`item_part`, `SalaryHeadItems`.`status`, 
          `SalaryHeadItems`.`salary_head_item_order1` FROM `salary_head_items` AS `SalaryHeadItems` 
           WHERE head_fkey in(select head_pkey from salary_heads where lcase(item_type)='leave' and value='Y' and status=1) 
           and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where status = 1 and LEAVEPOLICY_GROUP_ID 
           IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey = '$emp_fkey'))
           union
          SELECT `SalaryHeadItems`.`salary_head_item_pkey`, `SalaryHeadItems`.`head_fkey`, concat(SalaryHeadItems.item,'(Admin Only)') item, `SalaryHeadItems`.`item_type`,
          `SalaryHeadItems`.`item_value`, `SalaryHeadItems`.`occurance`, `SalaryHeadItems`.`start_from`, `SalaryHeadItems`.`comments`, 
          `SalaryHeadItems`.`value`, `SalaryHeadItems`.`is_show_salslip`, `SalaryHeadItems`.`item_part`, `SalaryHeadItems`.`status`, 
          `SalaryHeadItems`.`salary_head_item_order1` FROM `salary_head_items` AS `SalaryHeadItems`
           where  item_part='Indirect' and value='Y' and item_type ='LEAVE'"
            );
        }else{
           $arr_leave = $this->SalaryHeadItems->query(
            "SELECT `SalaryHeadItems`.`salary_head_item_pkey`, `SalaryHeadItems`.`head_fkey`,
        CASE 
            WHEN `SalaryHeadItems`.`item_part` IN ('Indirect', 'Admin Only') THEN CONCAT(`SalaryHeadItems`.`item`, ' (Admin Only)')
            ELSE `SalaryHeadItems`.`item`
            END AS item, 
 `SalaryHeadItems`.`item_type`,
 `SalaryHeadItems`.`item_value`, `SalaryHeadItems`.`occurance`, `SalaryHeadItems`.`start_from`, `SalaryHeadItems`.`comments`, 
 `SalaryHeadItems`.`value`, `SalaryHeadItems`.`is_show_salslip`, `SalaryHeadItems`.`item_part`, `SalaryHeadItems`.`status`, 
 `SalaryHeadItems`.`salary_head_item_order1` FROM `salary_head_items` AS `SalaryHeadItems` 
  WHERE head_fkey in(select head_pkey from salary_heads where lcase(item_type)='leave' and value='Y' and status=1) 
  and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where status = 1 and LEAVEPOLICY_GROUP_ID 
  IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey = '$emp_fkey'))
  union
 SELECT `SalaryHeadItems`.`salary_head_item_pkey`, `SalaryHeadItems`.`head_fkey`,  
        CASE 
            WHEN `SalaryHeadItems`.`item_part` IN ('Indirect', 'Admin Only') THEN CONCAT(`SalaryHeadItems`.`item`, ' (Admin Only)')
            ELSE `SalaryHeadItems`.`item`
             END AS item,
 `SalaryHeadItems`.`item_type`,
 `SalaryHeadItems`.`item_value`, `SalaryHeadItems`.`occurance`, `SalaryHeadItems`.`start_from`, `SalaryHeadItems`.`comments`, 
 `SalaryHeadItems`.`value`, `SalaryHeadItems`.`is_show_salslip`, `SalaryHeadItems`.`item_part`, `SalaryHeadItems`.`status`, 
 `SalaryHeadItems`.`salary_head_item_order1` FROM `salary_head_items` AS `SalaryHeadItems`
  where value='Y' and item_type ='LEAVE'");
        }
            foreach ($arr_leave as $value) {
                $arr_leave_type[]['SalaryHeadItems'] = $value['0'];
            }
        return json_encode(Set::extract('/SalaryHeadItems/.', $arr_leave_type));
    }

   public function leavecheck() {
        $arr_request = $this->request->data;
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');
        $empfkey = $arr_request['employee'];
        $start = $arr_request['start'];
        $end = $arr_request['enddate'];
        $salary_head_iotem = $arr_request['salary_head_item_fkey'];
        $leave_balance = $this->getLeaveBalanceForAuthOrApproval($empfkey,$salary_head_iotem,$start);
//        if($leave_balance['month_balance'] == '0'){
//            return "Employee have no Monthly Balance for applying Leave on the selected months";
//        }
        $arr_leave_check = 0;
        if($start && $end){
        $arr_leave_check = $this->EmployeeLeaveTransaction->query("Select count(*) from emp_leave_transactions 
            where leave_date between  $start and $end  and LEAVEENTRYID in (select LEAVEENTRYID  
            from leaveentries where EMP_fkey = $empfkey)
        ");
        }
        //$extingsalary=$arr_salary_month_check[0]['emp_salary_slip']['salary_amount'];
        $this->set('arr_leave_check', $arr_leave_check);
        $data = $arr_leave_check['0']['0']['count(*)'];
        $result = ($data > 0)?"Employee already Had leaves on these days":0;
	if($result =='0'){
        $att_startdate = $this->EmployeeLeaveTransaction->query("select att_start_end_fn(DATE_FORMAT('$end', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->EmployeeLeaveTransaction->query("select att_start_end_fn(DATE_FORMAT('$end', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_startdate1 = date("Y-m-d", strtotime($att_startdate['0']['0']['monthly_att_fromdate']));
        $att_enddate1 = date("Y-m-d", strtotime($att_enddate['0']['0']['monthly_att_todate']));
        if ($end != '') {
            $months = date("Y-m-d", strtotime($end));
        } else $months = date("Y-m-d");
        $from_date = $arr_request['start'];
        $from_half = $arr_request['start_sess'];
        $to_half = $arr_request['end_sess'];
        $salary_head_item_fkey = $arr_request['salary_head_item_fkey'];
         //edited by athira on 21-09-2025
            $company_code=$this->Session->read('company_code');
            $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
        $lrule = "Success";
        if ($end != '') {
            $lrule = $this->EmployeeLeaveTransaction->query("select leave_rules_fn('$empfkey','$salary_head_item_fkey','','$from_date','$from_half','$end','$to_half') as LeaveRule");
            $lrule = isset($lrule['0']['0']['LeaveRule']) ? $lrule['0']['0']['LeaveRule'] : '';
        }
        if ($lrule == 'Success') {
			$this->GetLeaveBalance($salary_head_iotem,$empfkey,$end);
        }else{
            $result = $lrule;
        }
		}else{
                 $lrule = "Success";
                  if ($lrule == 'Success') {
                      $this->GetLeaveBalanceNew($salary_head_iotem, $empfkey, $start, $end);
                  }
        }
    }
	return $result;
    
   }

    /*
     * Get leave balance for auth/approve
     * On 24 Sep 2016
     */
    public function getLeaveBalanceForAuthOrApproval($emp_fkey='', $salary_head_item_fkey='',$to_date='') {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $to_month = date('m',strtotime($to_date));
        $to_year = date('Y',strtotime($to_date));
        $get_finyear = $this->LeaveRequests->query("select fin_year  from fin_year where lcase(Year_status)='open' and vattr1 = 0 and is_current_finyear='Y' and status=1
   and branch_code= (select branch_code from emp_details where emp_Pkey='$emp_fkey' ) ORDER BY fin_year DESC LIMIT 1 ");
        $finyear = isset($get_finyear['0']['fin_year']['fin_year'])?$get_finyear['0']['fin_year']['fin_year']:date('Y');
        //edited by athira on 21-09-2025
        $company_code=$this->Session->read('company_code');
        $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
         $monthly_balance=0;
        }
        else{
         $lmonthbalance = $this->LeaveRequests->query("select leave_balance_inthe_month_fn('$emp_fkey','$salary_head_item_fkey','$to_month','$finyear') as LeaveBalancemonth");
        $monthly_balance = isset($lmonthbalance['0']['0']['LeaveBalancemonth']) ? $lmonthbalance['0']['0']['LeaveBalancemonth'] : 0;
        }
        //end
        $leave_days = 0;
        return array("l_days"=>$leave_days,"month_balance"=>$monthly_balance);	
        
    }
    //Ends
    
    
    
    /*
     * To show failed leave requests
     * On 15 Oct 2016
     */
    public function showfailedleaverequests($importedCount = '', $startedTime = '',$employee= array()) {
        $this->set('importedCount', $importedCount);
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
        if($importedCount > 0){
//            $emps =  explode(",", $employee);
//            $emp = implode("','", $emps);
            
//            $fetch_emp = $this->EmployeeLeaveUpload->query("select EmpName from employee_info where emp_pkey in ('$emp') ");
            $this->set('title', 'Leave requests imported successfully');
//            $this->set('fetch_emp', $fetch_emp);
        }else{
            $this->set('title', 'Sorry, not imported any leave requests');
        }
        
        
        $arrFailedLeaveRequests = array();
        if($startedTime != ''){
            
            $month_condition = " and UNIX_TIMESTAMP(lu.created_date) > $startedTime ";
            
            $arrFailedLeaveRequests = $this->EmployeeLeaveUpload->query("SELECT ue.*,ed.first_name,last_name
FROM `Upload_leave_errirs` ue
join emp_details ed on (ed.emp_pkey = ue.emp_fkey) where PID = '$employee'  "); 
       }
       $this->set('arrFailedLeaveRequests', $arrFailedLeaveRequests);
    }
    //Ends
     //Edited by Akshay on 17-8-2024
    public function checkLeaveDays()
    {
        $this->autoRender = FALSE;
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->layout = null;
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $this->request->data;

        $emp_fkey = $arr_form_data['emp_pkey'];
        $leave_type = $arr_form_data['leave_type'];
        $leave_start_date = isset($arr_form_data['start_date']) ? (DateTime::createFromFormat('d-m-Y', $arr_form_data['start_date'])->format('Y-m-d')) : '';
        $leave_end_date = isset($arr_form_data['end_date']) ? (DateTime::createFromFormat('d-m-Y', $arr_form_data['end_date'])->format('Y-m-d')) : '';
        $leave_start_session = trim($arr_form_data['start_session']);
        $leave_end_session = trim($arr_form_data['end_session']);
        try {
            // $curr_leaves_count = $this->EmployeeLeaveUpload->query("SELECT COUNT(*)AS count
            // FROM emp_leave_upload 
            // WHERE emp_fkey = '$emp_fkey'
            // AND leave_start_date >= '$leave_start_date'
            // AND leave_end_date <= '$leave_end_date'
            // AND leave_start_session = 1
            // AND leave_end_session = 2    
            // ");
            // Edited by Akshay on 20-12-2024
            // $curr_leaves_count = $this->EmployeeLeaveUpload->query("SELECT COUNT(*) AS count
            //                                                             FROM emp_leave_upload
            //                                                             WHERE emp_fkey = '$emp_fkey'
            //                                                             AND (
            //                                                                 -- Overlapping date ranges
            //                                                                 ('$leave_start_date' BETWEEN leave_start_date AND leave_end_date)
            //                                                                 OR ('$leave_end_date' BETWEEN leave_start_date AND leave_end_date)
            //                                                                 OR (leave_start_date BETWEEN '$leave_start_date' AND '$leave_end_date')
            //                                                                 OR (leave_end_date BETWEEN '$leave_start_date' AND '$leave_end_date')
            //                                                                 )
            //                                                             AND (
            //                                                                 -- Overlapping sessions
            //                                                                 (leave_start_date = '$leave_start_date' AND leave_start_session = '$leave_start_session')
            //                                                                 OR (leave_end_date = '$leave_end_date' AND leave_end_session = '$leave_end_session')
            //                                                                 OR ('$leave_start_date' = leave_end_date AND '$leave_start_session' = leave_end_session)
            //                                                                 OR ('$leave_end_date' = leave_start_date AND '$leave_end_session' = leave_start_session)
            //                                                                 );
 
            //                                                         ");
            $curr_leaves_count = $this->EmployeeLeaveUpload->query("SELECT COUNT(*) AS count FROM emp_leave_transactions left join leaveentries on (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID)
WHERE EMP_fkey = '$emp_fkey'
AND (
    ('$leave_start_date' BETWEEN FROMDATE AND TODATE)
    OR ('$leave_end_date' BETWEEN FROMDATE AND TODATE)
    OR (FROMDATE BETWEEN '$leave_start_date' AND '$leave_end_date')
    OR (TODATE BETWEEN '$leave_start_date' AND '$leave_end_date')
    )
AND (
    (FROMDATE = '$leave_start_date' AND FROMHALF = '$leave_start_session')
    OR (TODATE= '$leave_end_date' AND TOHALF= '$leave_end_session')
    OR ('$leave_start_date' = TODATE AND '$leave_start_session' = TOHALF)
    OR ('$leave_end_date' = FROMDATE AND '$leave_end_session' = FROMHALF)
    )
and leaveentries.LEAVESTATUS in ('Approved','Authorized','Applied','CancellationOfApproved','CancellationOfAuthorized')");
            // End
        } catch (Exception $e) {
            debug($e);
        }

        $curr_leaves_count = isset($curr_leaves_count[0][0]['count']) ? $curr_leaves_count[0][0]['count'] : 0;
        //  $leavestart = isset($curr_leaves_count[0][0]['leave_start_session']) ? $curr_leaves_count[0][0]['leave_start_session'] : 0;
        //  $leaveend = isset($curr_leaves_count[0][0]['leave_end_session']) ? $curr_leaves_count[0][0]['leave_end_session'] : 0;
        // debug($curr_leaves_count); edited by sinsiya on 15-12-2024
        if ($curr_leaves_count > 0) {
            $resp["success"] = false;
            $resp["msg"] = "Leave already present in this date range. ";
        } else {
            $resp["success"] = true;
            $resp["msg"] = "";
        }
        return json_encode($resp);
    }
    //End
    
      //edited by athira on 21-09-2025
     public function GetLeaveBalanceNew($salary_head_item_fkey = "",$emp_fkey="",$start_date="",$end_date="") {
         $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
         $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
         $this->autoRender=FALSE;
        $start_date = $this->request->data('start_date'); 
            $arr_item = $this->LeaveRequests->query("SELECT item_part from  salary_head_items where salary_head_item_pkey = '$salary_head_item_fkey'");
            $item_part = $arr_item['0']['salary_head_items']['item_part'];

 if ($item_part == 'Indirect') {
            return json_encode(array(
                'yearly_balance' => 365
            ));
        }

 $leavePolicy = $this->LeavePolicy->find("all", array(
    'fields' => array('minimum_service','exceptions','status','minimum_leave','maximum_leave','min_day_before_apply','leave_policy_type','alloted_leave_forthe_year'),
    'conditions' => array(
        'salary_head_item_fkey' => $salary_head_item_fkey,
        'status' => 1,
        "LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey = '$emp_fkey')"
    )
));

$leave_policy_type=isset($leavePolicy[0]['LeavePolicy']['leave_policy_type'])? $leavePolicy[0]['LeavePolicy']['leave_policy_type']: '';
$alloted_leave_forthe_year=isset($leavePolicy[0]['LeavePolicy']['alloted_leave_forthe_year']) ? $leavePolicy[0]['LeavePolicy']['alloted_leave_forthe_year'] : 0;

$att_start_date="";
$att_end_date="";

$this->LeaveRequests->query("
        CALL leave_start_end_prc('$emp_fkey', '$salary_head_item_fkey', '$start_date', @p_startdate1, @p_enddate1)
    ");
    $from_result = $this->LeaveRequests->query("SELECT @p_startdate1 AS start_date1, @p_enddate1 AS end_date1");
    $from_startdate = $from_result[0][0]['start_date1'];
    $from_enddate = $from_result[0][0]['end_date1'];

$min_service_flag = true;
$min_service_msg  = "";
$adv_notice_flag  = true;
$adv_notice       = "";
$min_leave_limit="";
$max_leave_limit="";

// ✅ Minimum Service Check
if ($leavePolicy && $leavePolicy[0]['LeavePolicy']['exceptions'] == 'Y') {
    
    if (!empty($leavePolicy[0]['LeavePolicy']['minimum_service'])) {
        $joining_date = $this->LeaveRequests->query("SELECT joining_date from emp_proff where emp_fkey='$emp_fkey'");
        $joining_date = $joining_date[0]['emp_proff']['joining_date'];

        $joining = new DateTime($joining_date);
        $today   = new DateTime($start_date);
        $interval = $joining->diff($today);
        $months_worked = ($interval->y * 12) + $interval->m;

        if ($months_worked < $leavePolicy[0]['LeavePolicy']['minimum_service']) {
            $min_service_flag = false;
            $min_service_msg  = "Employee must complete minimum service of " .
                                $leavePolicy[0]['LeavePolicy']['minimum_service'] .
                                " month(s) before applying leave.";
        }
    }

    // ✅ Advance Notice Check

    if (!empty($leavePolicy[0]['LeavePolicy']['min_day_before_apply'])) {
        $minDays = (int)$leavePolicy[0]['LeavePolicy']['min_day_before_apply'];

        $today = date('Y-m-d');
        $leaveStart = $start_date; // coming from form

        $diffDays = (strtotime($leaveStart) - strtotime($today)) / (60 * 60 * 24);

        if ($diffDays < $minDays) {
            $adv_notice_flag = false;
            $adv_notice = "Leave should be applied at least ".$minDays." day(s) in advance.";
        }
    }

     $min_leave_limit = !empty($leavePolicy[0]['LeavePolicy']['minimum_leave']) 
                    ? (int)$leavePolicy[0]['LeavePolicy']['minimum_leave'] 
                    : 0;

$max_leave_limit = !empty($leavePolicy[0]['LeavePolicy']['maximum_leave']) 
                    ? (int)$leavePolicy[0]['LeavePolicy']['maximum_leave'] 
                    : 0;
    
}

            
        //     if($item_part == 'Indirect'){
        //     return json_encode(array(
        //         'yearly_balance'=>365
        //      ));
        //  }else{
        if(!empty($salary_head_item_fkey) && !empty($emp_fkey)){
            
            $year = date('Y');
            
            if ($end_date != '') {
                $months = date("Y-m-d", strtotime($end_date));
            } 
            else $months = date("Y-m-d");

            $get_finyear = $this->LeaveRequests->query("select fin_year  from fin_year where lcase(Year_status)='open' and vattr1 = 0 and is_current_finyear='Y' and status=1
            and branch_code= (select branch_code from emp_details where emp_Pkey='$emp_fkey' ) ORDER BY fin_year DESC LIMIT 1 ");
            $finyear = isset($get_finyear['0']['fin_year']['fin_year'])?$get_finyear['0']['fin_year']['fin_year']:date('Y');
            $encash = $this->LeaveRequests->query("select approved_days FROM leave_encashment_master  WHERE salary_head_item_fkey = '$salary_head_item_fkey' and is_approved = 'Y' AND status = 1 AND emp_fkey = '$emp_fkey'  ORDER BY creation_date LIMIT 1");
			$encashleave = isset($encash['0']['leave_encashment_master']['approved_days'])?$encash['0']['leave_encashment_master']['approved_days']:0;

            if ($start_date == '') {
                $start_date = "NULL"; // true SQL NULL (no quotes)
                 $lbalance = $this->LeaveRequests->query("
                SELECT leave_balance_inthe_year_fn('$emp_fkey', '$salary_head_item_fkey', $start_date) AS LeaveBalance
            ");
            } else {
                 $lbalance = $this->LeaveRequests->query("
                SELECT leave_balance_inthe_year_fn('$emp_fkey', '$salary_head_item_fkey', '$start_date') AS LeaveBalance
            ");
            }

            $occurance=$this->LeaveRequests->query("SELECT occurance FROM salary_head_items WHERE salary_head_item_pkey='$salary_head_item_fkey'");
            $occurance=$occurance[0]['salary_head_items']['occurance']; 

            $yearly_balance = isset($lbalance[0][0]['LeaveBalance']) ? $lbalance[0][0]['LeaveBalance'] : 0;

             if($occurance=='COFF'){
                $yearly_balance= $yearly_balance-$alloted_leave_forthe_year;
                 if ($yearly_balance < 0) {
                        $yearly_balance = 0;
                    }
            }

            // $lmonthbalance = $this->LeaveRequests->query("select leave_balance_inthe_month_fn('$emp_fkey','$salary_head_item_fkey','$months','$finyear') as LeaveBalancemonth");
            $monthly_balance = 0;
            
            //Check if leave is isnegative
            //On 09 Oct 2016
            
            $arr_leave_policy =  $this -> LeavePolicy ->find("all",array(
                'fields' => 'ALLOW_NEGETIVE',
                'conditions' => array(
                        'salary_head_item_fkey'=>$salary_head_item_fkey,
                        'LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='.$emp_fkey.')'
                    )
                )
            );
            $allow_negative = isset($arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE'])?$arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE']:'';
            //Ends

            return json_encode(array(
                'yearly_balance'=>$yearly_balance,
                'monthly_balance'=>$monthly_balance,
                "min_service_flag" => $min_service_flag,
                'adv_notice_flag'=>$adv_notice_flag,
                'adv_notice'=>$adv_notice,
                "min_service_msg"  => $min_service_msg,
                'min_leave_limit'=>$min_leave_limit,
                'max_leave_limit' => $max_leave_limit,
               'leave_policy_type'=> $leave_policy_type,
                'allow_negative'   => strtoupper($allow_negative),
                'att_start_date'   => $from_startdate, // ✅ add cycle start
                'att_end_date'     => $from_enddate,   // ✅ add cycle end
            ));

           } 
        //  }
    }
    //end
    //edited by athira on 21-09-2025
    public function form_new($emp_leave_upload_pkey = "")
    {
        $this->layout = null;
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //$leavebalance = isset($arr_leavebalance[0][0]['leave_balance'])?$arr_leavebalance[0][0]['leave_balance']:0;
        $joins  = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        //debug($arr_branches);

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        // Edited by Akshay on 11-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $company_code = $this->Session->read('company_code');

        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $conditions = array("branch_code" => $is_ho, "status" => 1);
            } else {
                $conditions = array("status" => 1);
            }
        } else
            // End
            if ($user_group == 2) {
                $cur_emp_key = $this->Session->read("emp_fkey");
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                $conditions = array("branch_code" => $cur_emp_branch, "status" => 1);
            } else {
                $conditions = array("status" => 1);
            }
        //employee branch wise sorting ends here
         // Edited by Megha on 03-11-2025
        $company_code = strtoupper($this->Session->read('company_code'));
        if ($company_code == 'WHOO') {
             $conditions = array("status" => 1);
        }
        // End

        // Edited by bindu 22-12-2025
        if($company_code =='ELSL'){
$logged_emp_key = (int)$this->Session->read('emp_fkey');

$join = [
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
    'fields' => ['EmployeeDetails.*', 'EmployeeProfessionalDetails.emp_company_id'],
    'conditions' => $emp_conditions,
    'joins' => $join,
    'order' => ['EmployeeDetails.first_name' => 'ASC']
]);

$this->set('arr_employees', $arr_employees);
 // Edited by bindu 22-12-2025 end
}
else{
$arr_employees = $this->EmployeeDetails->find("all", array('fields' => 'EmployeeDetails.*,EmployeeProfessionalDetails.emp_company_id,', 'conditions' => $conditions, 'joins' => $joins));
    $this->set("arr_employees", $arr_employees);
}
    

        
        $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave'
        and value='Y' and status=1)")));
        $this->set("arr_leavetypes", $arr_leavetypes);
        $arr_leave_details = $this->LeaveRequests->find("all");
        if (isset($arr_leave_details[0]['LeaveRequests']['FROMHALF'])) {
            if ($arr_leave_details[0]['LeaveRequests']['FROMHALF'] == 1) {
                $arr_leave_details[0]['LeaveRequests']['FROMHALF'] = 'First Half';
            } else if ($arr_leave_details[0]['LeaveRequests']['FROMHALF'] == 2) {
                $arr_leave_details[0]['LeaveRequests']['FROMHALF'] = 'Second Half';
            }
        }
        if (isset($arr_leave_details[0]['LeaveRequests']['TOHALF'])) {
            if ($arr_leave_details[0]['LeaveRequests']['TOHALF'] == 1) {
                $arr_leave_details[0]['LeaveRequests']['TOHALF'] = 'First Half';
            } else if ($arr_leave_details[0]['LeaveRequests']['TOHALF'] == 2) {
                $arr_leave_details[0]['LeaveRequests']['TOHALF'] = 'Second Half';
            }
        }
        $this->set("arr_leave_details", $arr_leave_details);

        $data['emp_leave_upload_pkey'] = 0;
        $data['emp_fkey'] = ""; //$this->Session->read('emp_fkey');
        $data['leave_type'] = "";
        $data['leave_start_date'] = "";
        $data['leave_start_session'] = "";
        $data['leave_end_date'] = "";
        $data['leave_end_session'] = "";
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
        if (isset($emp_leave_upload_pkey) && $emp_leave_upload_pkey != "") {
            $data_db = $this->EmployeeLeaveUpload->find("first", array("conditions" => array("emp_leave_upload_pkey" => $emp_leave_upload_pkey)));
            $data = $data_db['EmployeeLeaveUpload'];
        }
        $this->set("data", $data);
        $this->set("company_code", $company_code);

        $arr_leave_details = $this->LeaveRequests->find("first", array(
            'fields' => 'salary_head_item_fkey,EMP_fkey',
            'conditions' => array('EMP_fkey' => $data['emp_fkey'])
        ));
        $salary_head_item_fkey = isset($arr_leave_details['LeaveRequests']['salary_head_item_fkey']) ? $arr_leave_details['LeaveRequests']['salary_head_item_fkey'] : 0;
        $emp_fkey = isset($arr_leave_details['LeaveRequests']['EMP_fkey']) ? $arr_leave_details['LeaveRequests']['EMP_fkey'] : 0;
        
    }
    //end


    // edited by athira on 22-05-2026
    private function checkAttendancePunches($emp_pkey, $fromdate, $todate, $fromhalf = 1, $tohalf = 2)
    {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $attendance_records = $this->LeaveRequests->query("SELECT att_date, present FROM emp_detail_timeattandance WHERE emp_pkey = '$emp_pkey' and att_date between '$fromdate' and '$todate' and (present LIKE 'P/%' OR present LIKE '%/P' OR present = 'P' OR present LIKE 'p/%' OR present LIKE '%/p' OR present = 'p')");
        if (empty($attendance_records)) {
            return 0;
        }

        $from_date_only = date('Y-m-d', strtotime($fromdate));
        $to_date_only   = date('Y-m-d', strtotime($todate));
        $conflictMask   = 0;

        foreach ($attendance_records as $rec) {
            $att_date = date('Y-m-d', strtotime($rec['emp_detail_timeattandance']['att_date']));
            $present  = isset($rec['emp_detail_timeattandance']['present']) ? trim($rec['emp_detail_timeattandance']['present']) : '';

            $first_half_present  = false;
            $second_half_present = false;

            if (strpos($present, '/') !== false) {
                $parts = explode('/', $present);
                if (isset($parts[0]) && strcasecmp(trim($parts[0]), 'P') == 0) {
                    $first_half_present = true;
                }
                if (isset($parts[1]) && strcasecmp(trim($parts[1]), 'P') == 0) {
                    $second_half_present = true;
                }
            } else {
                if (strcasecmp($present, 'P') == 0) {
                    $first_half_present = true;
                    $second_half_present = true;
                }
            }

            if (!$first_half_present && !$second_half_present) {
                continue;
            }

            $check_first_half  = true;
            $check_second_half = true;
            if ($att_date === $from_date_only && $fromhalf == 2) {
                $check_first_half = false;
            }
            if ($att_date === $to_date_only && $tohalf == 1) {
                $check_second_half = false;
            }

            if ($check_first_half && $first_half_present) {
                $conflictMask |= 1;
            }
            if ($check_second_half && $second_half_present) {
                $conflictMask |= 2;
            }
        }

        return $conflictMask;
    }
    //ended by athira on 22-05-2026
}
