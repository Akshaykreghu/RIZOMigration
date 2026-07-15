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
App::uses('CakeEmail', 'Network/Email');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class EditAttendanceController extends AppController
{

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'EditAttendance';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'CompanyContactInfo', 'LeaveRequests', 'EditPunches', 'EmployeeDetails', 'DbConfig', 'EditPunchesHist', 'AttendanceRegister');
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */

    // duration showing wrong
    // shift and adtnal shift not showing
    // chnage columg order
    // column width
    // add color option

    public function index($emp_id = '')
    {
        $this->layout = null;

        $arr_months = json_decode($this->getmonths());
        $this->set('arr_months', $arr_months);

        $arr_employees = json_decode($this->requestAction("/ApiRequest/listemployees/"));
        $this->set('arr_employees', $arr_employees);

        if (!empty($emp_id)) {
            $this->set('emp_id', $emp_id);
        }
    }

    public function hierarchy()
    {
        $this->layout = null;

        $arr_months = json_decode($this->getmonths());
        $this->set('arr_months', $arr_months);

        $arr_employees = json_decode($this->requestAction("/ApiRequest/listemployeesforhierarchy/"));
        $this->set('arr_employees', $arr_employees);
        $this->render('index');
    }

    public function employeeeditpunch()
    {
        $arr_months = json_decode($this->getmonths());
        $this->set('arr_months', $arr_months);
        $arr_employees = json_decode($this->requestAction("/ApiRequest/listemployees/"));
        $this->set('arr_employees', $arr_employees);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $emp_pkeys = $this->Session->read('emp_fkey');
        $emp_idss = $this->EmployeeDetails->query("select emp_id from emp_details where emp_pkey = '$emp_pkeys' ");
        if (!empty($emp_id)) {
            $this->set('emp_id', $emp_id);
        }
        $this->set('emp_pkeys', $emp_idss['0']['emp_details']['emp_id']);
        //        $this->set('emp_pkeys',$emp_pkeys);
    }

    /* public function listpunches() {
      $this->autoRender = FALSE;
      $this->EditPunches->useDbConfig = $this->Session->read('ds');
      //debug($this->Session->read('ds'));

      $employee = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : 0;
      $month = isset($_REQUEST['month']) ? $_REQUEST['month'] : '';
      $includeinactive = isset($_REQUEST['includeinactive']) ? $_REQUEST['includeinactive'] : '';
      $limit = $_REQUEST['rows'];
      $page = $_REQUEST['page'];

      $ofst = ($page - 1) * $limit;

      $resp_mispunches = array();
      $resp_mispunches["rows"] = array();

      $condition = array();

      if ($includeinactive) {
      if ($includeinactive == 'N') {
      $condition['status'] = array('Y');
      } else {
      $condition['status'] = array('Y', 'N');
      }
      } else {
      $condition['status'] = array('Y');
      }
      if ($employee) {
      $condition['emp_id'] = $employee;
      }
      if ($month) {
      //$condition['MONTH(LOGDATE)'] = $month;
      $condition['DATE_FORMAT(LOGDATE,"%Y-%c")'] = $month;
      }
      $count = 0;
      if ($employee) {
      $count = $this->EditPunches->find("count", array('conditions' => $condition));
      $arr_mispunches = $this->EditPunches->find("all", array('conditions' => $condition, 'order' => array('EditPunches.LOGDATE'), 'limit' => intval($limit), 'offset' => intval($ofst)));
      }
      foreach ($arr_mispunches as $key => $value) {
      $resp_mispunches["rows"][$key] = $value["EditPunches"];
      }
      $resp_mispunches["total"] = $count;
      echo json_encode($resp_mispunches);
      } */

    //New List starts


    public function listpunches()
    {
        $this->autoRender = FALSE;

        $base_table = "emp_detail_timeattandance";

        $emp = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : 0;
        if ($emp == 0) {
            return;
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_id` = '$emp'");
        $emp_pkey = isset($arr_emp_pkey[0]['emp_details']['emp_pkey']) ? $arr_emp_pkey[0]['emp_details']['emp_pkey'] : 0;
        $branch_code = isset($arr_emp_pkey['0']['emp_details']['branch_code']) ? $arr_emp_pkey['0']['emp_details']['branch_code'] : '';
        $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
        // debug($monthdd);

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();
        $resp_mispunches["data"] = array();
        $resp_mispunch["out"] = array();

        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = $monthdd;
        $yearmonth = $month . '-01';

        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
        $arr_date_in_selectedmonth = range(1, $att_enddate);

        if ($att_startdate != 1) {
            $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }

        if ($emp_pkey != 0) {
            $condition = "$base_table.emp_pkey = '$emp_pkey' and ";
            $emp = $emp_pkey;

            // leave balance
            $dats = $month . '-01'; //date('Y-m-01');
            $att_enddate = $this->AttendanceRegister->query("select att_start_end_fn(DATE_FORMAT('$dats', '%Y-%m-01'), 2) as monthly_att_todate");
            $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];

            $years = $this->AttendanceRegister->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = 0 and is_current_finyear = 'Y' and status = '1' and branch_code in (select branch_code from emp_details where emp_pkey = '$emp_pkey') ");
            $year = isset($years['0']['fin_year']['fin_year']) ? $years['0']['fin_year']['fin_year'] : 0;
            $arr_leaves_heads = $this->AttendanceRegister->query("SELECT salary_head_item_pkey,occurance FROM `salary_head_items` "
                . "WHERE head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) "
                . "and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where LEAVEPOLICY_GROUP_ID IN "
                . "(SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_pkey') and status = 1 )");
            $arr_leave = array();
            //DEBUG($arr_leaves_heads);

            foreach ($arr_leaves_heads as $key => $val) {
                $head = $val['salary_head_items']['occurance'];
                $slary_head_item_pkey = $val['salary_head_items']['salary_head_item_pkey'];
                $lbalance = $this->AttendanceRegister->query("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
                // debug("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
                $resp = isset($lbalance['0']['0']['LeaveBalance']) ? round($lbalance['0']['0']['LeaveBalance'], 1) : 0;
                $arr_leave[] = array(
                    "salary_head_item_pkey" => $slary_head_item_pkey,
                    "Head" => $head,
                    "leaveBalance" => $resp
                );
            }
        } else {
            $condition = '';
            $emp = NULL;
        }

        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $useracess = $this->EmployeeDetails->query("select * from user_access as Useraccess where user_fkey = '$emp_pkeys' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            if ($access == 'Y') {
                $emp_condition = ""; // "emp.attr1 = '$emp_pkeys' and ";
            } else {
                $emp_condition = "";
            }
        } else {
            $emp_condition = "";
        }
        //                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //		$attend = $this->EmployeeDetails->query("select time_duration_check('$yearmonth','$emp','')");
        //	

         $deleterecords = $this->EmployeeDetails->query("delete from  emp_detail_timeattandance where emp_pkey in ('$emp_pkey')  and yearmonth='$yearmonth' and isdelete='Y' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$yearmonth','%Y-%m')) ");

        if (!$shiftdetailed = $this->EmployeeDetails->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey')")) {
            $resp_mispunches["total"] = "0";
            $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
            $resp_mispunches["type"] = "danger";
            echo json_encode($resp_mispunches);
            //            return FALSE;
            die();
        }

        //debug($shiftdetailed);
        if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
            if (!$this->EmployeeDetails->query("SELECT time_duration_check_multishift('$yearmonth', '$emp_pkey', '$branch_code')")) {
                $resp_mispunches["total"] = "0";
                $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
                $resp_mispunches["type"] = "danger";
                echo json_encode($resp_mispunches);
                //                return false;
                die();
            }
        } else {
            if (!$this->EmployeeDetails->query("SELECT time_duration_check('$yearmonth', '$emp_pkey', '$branch_code')")) {
                $resp_mispunches["total"] = "0";
                $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
                $resp_mispunches["type"] = "danger";
                echo json_encode($resp_mispunches);
                //                return false;
                die();
            }
        }

        $attendances_count = $this->EmployeeDetails->query("select count(*) AS count from $base_table left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) "
            . "where $emp_condition $condition yearmonth = '$yearmonth' and att_date!= '0000-00-00' order by att_date ");
        $count = isset($attendances_count[0][0]['count']) ? $attendances_count[0][0]['count'] : 0;

        $attendances = $this->EmployeeDetails->query("select 
            eup.main_status,
			empdetails.emp_id, 
			empdetails.first_name, 
			empdetails.last_name, 
			$base_table.*, 
			wd.minuts_calc_perday, 
			emp.emp_fkey,isdelete,emp.joining_date
			FROM 
			$base_table 
			left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
			left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
			left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
            left join emp_detail_status_update as eup on (eup.emp_fkey = emp_detail_timeattandance.emp_pkey and eup.att_date = emp_detail_timeattandance.att_date and eup.emp_detail_status_update_pkey = (
                SELECT MAX(emp_detail_status_update_pkey) 
                FROM emp_detail_status_update 
                WHERE emp_fkey = emp_detail_timeattandance.emp_pkey 
                AND att_date = emp_detail_timeattandance.att_date 
            )) 
            where $emp_condition $condition emp_detail_timeattandance.yearmonth = '$yearmonth' and emp_detail_timeattandance.att_date !='0000-00-00'
            order by att_date 
			LIMIT $ofst, $limit
		");

        $arr_attendance_register = $this->EmployeeDetails->query("select count(*) as count from attendance_register where emp_fkey = $emp_pkey AND month_year = '" . date('Y-m', strtotime($month)) . "' AND isdelete = 'N'");
        $iseditable = isset($arr_attendance_register[0][0]['count']) ? $arr_attendance_register[0][0]['count'] : 0;

        $employee_attendance = array();
        // continue;
        foreach ($attendances as $val) {
            // foreach ($arr_outputs as $val){
            // 	debug($val);
            // }
            //$emppk = $val[$base_table]['emp_pkey'];
            //$employee_attendance[$emppk][] = $val;


            $arr_output['emp_id'] = isset($val['empdetails']['emp_id']) ? $val['empdetails']['emp_id'] : '';
            $arr_output['leaves'] = isset($val['emp_detail_timeattandance']['leaves']) ? $val['emp_detail_timeattandance']['leaves'] : '';
            $arr_output['att_date'] = isset($val[$base_table]['att_date']) ? $val[$base_table]['att_date'] : '';
            if ($arr_output['att_date'] == '0000-00-00') {
                continue;
            }
            $arr_output['att_date_day'] = isset($val[$base_table]['att_date']) ? date("l", strtotime($val[$base_table]['att_date'])) : '';
            $arr_output['att_in_time'] = isset($val[$base_table]['att_in_time']) ? date("M-d H:i:s A", strtotime($val[$base_table]['att_in_time'])) : '';
            $arr_output['att_out_time'] = isset($val[$base_table]['att_out_time']) ? date("M-d H:i:s A", strtotime($val[$base_table]['att_out_time']))/* date("h:i:s A", strtotime($val[$base_table]['att_out_time'])) */ : '';
            $arr_output['duration'] = isset($val[$base_table]['duration']) ? $val[$base_table]['duration'] : '';
            // if ($arr_output['att_in_time'] != '' && $arr_output['att_out_time'] != '') {
            //     $start_datetime = new DateTime($arr_output['att_in_time']);
            //     $diff = $start_datetime->diff(new DateTime($arr_output['att_out_time']));
            //     $total_minutes = ($diff->days * 24 * 60);
            //     $total_minutes += ($diff->h * 60);
            //     $total_minutes += $diff->i;
            //     $arr_output['duration'] = isset($total_minutes) ? $total_minutes : '';
            // } else {
            //     $arr_output['duration'] = isset($val[$base_table]['duration']) ? $val[$base_table]['duration'] : '';
            // }
            $arr_output['ad_shift_string'] = isset($val[$base_table]['ad_shift_string']) ? $val[$base_table]['ad_shift_string'] : '';
            $arr_output['shift_string'] = isset($val[$base_table]['shift_string']) ? $val[$base_table]['shift_string'] : '';
            $arr_output['ad_present'] = isset($val[$base_table]['ad_present']) ? $val[$base_table]['ad_present'] : '';
            $arr_output['ad_in_time'] = isset($val[$base_table]['ad_in_time']) ? date("M-d H:i:s A", strtotime($val[$base_table]['ad_in_time'])) : '';
            $arr_output['ad_out_time'] = isset($val[$base_table]['ad_out_time']) ? date("M-d H:i:s A", strtotime($val[$base_table]['ad_out_time'])) : '';
            $arr_output['ad_duration'] = isset($val[$base_table]['ad_duration']) ? $val[$base_table]['ad_duration'] : '';
            $arr_output['ad_remarks'] = isset($val[$base_table]['ad_remarks']) ? $val[$base_table]['ad_remarks'] : '';
            $arr_output['site_transactions_fkey'] = isset($val[$base_table]['site_transactions_fkey']) ? $val[$base_table]['site_transactions_fkey'] : '';
            $arr_output['isdelete'] = isset($val[$base_table]['isdelete']) ? $val[$base_table]['isdelete'] : '';
            $arr_output['joining_date'] = isset($val['emp']['joining_date']) ? $val['emp']['joining_date'] : '';
            $arr_output['emp_detail_timeattandance_pkey'] = isset($val[$base_table]['emp_detail_timeattandance_pkey']) ? $val[$base_table]['emp_detail_timeattandance_pkey'] : '';
            $arr_output['main_status'] = isset($val['eup']['main_status']) ? $val['eup']['main_status'] : '';

            //$arr_output['min_bfr_on_dutyughjyulkjyuty_cal_ot'] = isset($val[$base_table]['min_bfr_on_dutty_cal_ot'])?$val[$base_table]['min_bfr_on_dutty_cal_ot']:'';
            //$arr_output['min_aftr_off_dutty_cal_ot'] = isset($val[$base_table]['min_aftr_off_dutty_cal_ot'])?$val[$base_table]['min_aftr_off_dutty_cal_ot']:'';
            //$arr_output['ot_duration'] = isset($val[$base_table]['ot_duration'])?$val[$base_table]['ot_duration']:'';

            $status = '';
            $status_color = 'black';

            $crnt_status = isset($val[$base_table]['ad_present']) ? str_replace(' ', '', strtoupper($val[$base_table]['ad_present'])) : '';

            if (in_array($crnt_status, array('A/A', 'P/A', 'A/P'))) {
                $adtnlstatus_color = 'red';
            } else if ($crnt_status == 'P/P') {
                $adtnlstatus_color = 'green';
            } else {
                $adtnlstatus_color = 'black';
            }

            $arr_output['adtnl_status_color'] = $adtnlstatus_color;


            if (!empty($val[$base_table]['weekoff'])) {
                //$status = $val[$base_table]['weekoff'];
                $status_color = "black";
            } else if (!empty($val[$base_table]['present'])) {
                //$status = $val[$base_table]['present'];
                if (in_array(strtoupper($val[$base_table]['present']), array('A/A', 'P/A', 'A/P'))) {
                    $status_color = 'red';
                } else if (strtoupper($val[$base_table]['present']) == 'P/P') {
                    $status_color = 'green';
                }
            }/* else if(!empty($val[$base_table]['holiday'])){
              $status = $val[$base_table]['holiday'];
              $status_color = "blue";
              }else if(!empty($val[$base_table]['leaves'])){
              $status = $val[$base_table]['leaves'];
              $status_color = "black";
              }else if(!empty($val[$base_table]['others'])){
              $status = $val[$base_table]['others'];
              $status_color = "black";
              }else{
              $status = $val[$base_table]['others'];
              $status_color = "black";
              } */ else {
                $status_color = "black";
            }
            $status = $val[$base_table]['present'] . ' ' .
                $val[$base_table]['holiday'] . ' ' .
                      $val[$base_table]['leaves'] . ' ' .
                $val[$base_table]['weekoff'] . ' ' .
                $val[$base_table]['others'];



            $arr_output['status'] = $status;
            // debug($arr_output);
            $arr_output['status_color'] = $status_color;

            $arr_output['arr_leaves'] = $arr_leave;

            $arr_output['editable'] = !empty($iseditable) ? false : true;

            $resp_mispunches["rows"][] = $arr_output;
            // $resp_mispunches["rows"][] = $arr_outputs;
        }

        $resp_mispunches["total"] = $count;
        $resp_mispunches["message"] = "Employee Attendance Retrieved Successfully ";
        $resp_mispunches["type"] = "success";
        echo json_encode($resp_mispunches);
//        debug($resp_mispunches);
//      exit;

    }

    public function AddLeave($head = '', $day = '', $emp_fkey = 0, $session = "")
    {
        // debug($session);
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $leaves = array();
        //added by megha auto delete applied leaves of employees on 13/02/2020
        if ($session == 'full') {
            $sess = 3;
            $fromhalf = 1;
            $tohalf = 2;
        } else if ($session == 'first') {
            $sess = 1;
            $fromhalf = 1;
            $tohalf = 1;
        } else {
            $sess = 2;
            $fromhalf = 2;
            $tohalf = 2;
        }
        // debug($session.$sess.$tohalf);
        $leave_count = $this->LeaveRequests->query("select count(*) cnt,emp_leave_transactions.LEAVEENTRYID "
            . "from emp_leave_transactions left join leaveentries on(leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID) "
            . "where leave_date=date_format('$day','%Y-%m-%d') and EMP_fkey= '$emp_fkey'  and "
            . "emp_leave_transactions.Leavestatus in('Applied','Athorised','Approved') and emp_leave_transactions.leave_session in ('$sess') ");
        $leave_count = isset($leave_count['0']['0']['cnt']) ? $leave_count['0']['0']['cnt'] : 0;

        // if ($leave_count > 0) {
        //     $id = $leave_count['0']['emp_leave_transactions']['LEAVEENTRYID'];
        //     $this->LeaveRequests->query("DELETE FROM emp_leave_transactions WHERE LEAVEENTRYID = '$id' ");
        //     $this->LeaveRequests->query("DELETE FROM leaveentries WHERE LEAVEENTRYID = '$id' ");
        // }
        //end auto delete

        $arr_leaves = $this->LeaveRequests->query("SELECT salary_head_item_pkey FROM `salary_head_items` WHERE `item_type` = 'Leave' AND `status` = '1' and occurance = '$head' ");
        $leaveentryId = 0;
        $leaves['salary_head_item_fkey'] = isset($arr_leaves['0']['salary_head_items']['salary_head_item_pkey']) ? $arr_leaves['0']['salary_head_items']['salary_head_item_pkey'] : 0;
        //edited by megha on 11/10/2019 leave date changed to today
        //$leaves['applied_date'] = $day;

        $leaves['applied_date'] = date('Y-m-d');
        $leaves['AuthoriseRemarks'] = "Leave Authorized For Verifying Attendance";
        $leaves['ApproveRemarks'] = "Leave Approved For Verifying Attendance";
        $leaves['LEAVESTATUS'] = "Approved";
        $leaves['EMP_fkey'] = $emp_fkey;
        $leaves['FROMDATE'] =  $day;
        $leaves['FROMHALF'] = $fromhalf;
        $leaves['TODATE'] = $day;
        $leaves['TOHALF'] = $tohalf;
        $leaves['ISAutherized'] = 1;
        $leaves['ISAutherizedby'] = "0";
        $leaves['Autherized_date'] = date("Y-m-d");
        $leaves['ISAPPROVED'] = 1;
        $leaves['APPROVEDBY'] = "0";
        $leaves['APPROVED_date'] = date("Y-m-d");
        $leaves['Reason'] = "Leave applied through status change";
        $leaves['REMARKS'] = "Leave applied through status change";
        $leaves['leave_days'] = $sess;
        $fromdate = $day;
        $fromhalf = $fromhalf;
        $todate = $day;
        $tohalf = $tohalf;
        $leavedays = $session == 'full' ? 1 : 0.5;
        $leavestatus = "Applied";
        $this->LeaveRequests->saveAll($leaves);
        $leaveentryId = $this->LeaveRequests->getLastInsertID();
        $out = $this->LeaveRequests->query("CALL leave_transaction_prc('$leaveentryId','$emp_fkey','$fromdate','$fromhalf','$todate','$tohalf','$leavedays','$leavestatus',@Perror_message);");
        $leavestatuses = "Approved";
        $outs = $this->LeaveRequests->query("CALL leave_transaction_prc('$leaveentryId','$emp_fkey','$fromdate','$fromhalf','$todate','$tohalf','$leavedays','$leavestatuses',@Perror_message);");
        return true;
    }

    public function editpunch($att_date = '', $emp_id = '', $site_t_fkey = '',  $att_in_time = '', $att_out_time = '')
    {
        $this->layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        // added by nimisha rajesh on 30/05/2019 issue - new button showing in edit puches when emp = heirarchy

        $emp_fkey = $this->Session->read('emp_fkey');
        $empid = $this->EmployeeDetails->query("SELECT `emp_id` FROM `emp_details` WHERE `emp_pkey` = '$emp_fkey'");
        $id = isset($empid['0']['emp_details']['emp_id']) ? $empid['0']['emp_details']['emp_id'] : '';
        //        debug($id);
        if ($emp_id == $id) {
            $shownewbutton = 'no';
        } else {
            $shownewbutton = 'yes';
        }
        $this->set('shownewbutton', $shownewbutton);

        // End

        $site_detailss = array();
        if (($site_t_fkey != 0)) {
            $site_detailss = $this->EmployeeDetails->query("select * from site_transactions left join designation on (designation.id = site_transactions.designation_id) left join site on (site.site_pkey = site_transactions.site_fkey) where site_transactions_pkey = '$site_t_fkey' ");
        }
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $useracess = $this->EmployeeDetails->query("select * from user_access as Useraccess where user_fkey = '$emp_pkeys' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            if ($access == 'Y') {
                $empmode = 1; //Hierarchy
            } else {
                $empmode = 0; //employee
            }
        } else {
            $empmode = 2; //admin
        }
        $this->set('empmode', $empmode);
        $this->set('att_date', $att_date);
        $this->set('emp_id', $emp_id);
        $this->set('att_in_time', $att_in_time);
        $this->set('site_detailss', $site_detailss);
        $this->set('att_out_time', $att_out_time);
    }

    public function listpunchesbydate()
    {
        $this->autoRender = FALSE;

        $employee = isset($_REQUEST['empid']) ? $_REQUEST['empid'] : 0;
        $att_date = isset($_REQUEST['att_date']) ? $_REQUEST['att_date'] : '';
        $att_in_time = isset($_REQUEST['att_in_time']) ? $_REQUEST['att_in_time'] : '';
        $att_out_time = isset($_REQUEST['att_out_time']) ? $_REQUEST['att_out_time'] : '';
        $includeinactive = isset($_REQUEST['includeinactive']) ? $_REQUEST['includeinactive'] : '';

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();

        $condition = array();

        if ($includeinactive) {
            if ($includeinactive == 'N') {
                $condition['status'] = array('Y');
            } else {
                $condition['status'] = array('Y', 'N');
            }
        } else {
            $condition['status'] = array('Y');
        }
        if ($employee) {
            $condition['emp_id'] = $employee;
        }
        if ($att_date) {
            //$condition['MONTH(LOGDATE)'] = $month;
            //$condition['DATE_FORMAT(LOGDATE,"%Y-%m-%d")'] = $att_date;
            if (!empty($att_in_time) && !empty($att_out_time)) {
                $condition[] = "DATE_FORMAT(LOGDATE,'%Y-%m-%d') BETWEEN '$att_in_time' AND '$att_out_time'";
            } else {
                $condition['DATE_FORMAT(LOGDATE,"%Y-%m-%d")'] = $att_date;
            }
        }

        $count = 0;
        if ($employee) {
            $this->EditPunches->useDbConfig = $this->Session->read('ds');
            $count = $this->EditPunches->find("count", array('conditions' => $condition));
            $arr_mispunches = $this->EditPunches->find(
                "all",
                array(
                    'conditions' => $condition,
                    'order' => array(
                        'EditPunches.LOGDATE'
                    ),
                    'limit' => intval($limit),
                    'offset' => intval($ofst)
                )
            );

            foreach ($arr_mispunches as $key => $value) {
                $resp_mispunches["rows"][$key] = $value["EditPunches"];
            }
        }
        $resp_mispunches["total"] = $count;
        echo json_encode($resp_mispunches);
    }

    //Ends

    public function Updateame()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        //debug($arr_data);
        $emp_id = $arr_data['emp'];
        $month = $arr_data['month'] . '-01';
        $get_emp = $this->EditPunches->query("select emp_pkey from emp_details where emp_id= '$emp_id' and status = '1' ");
        $emp_pkey = $get_emp['0']['emp_details']['emp_pkey'];
        $branch_code = isset($get_emp['0']['emp_details']['branch_code']) ? $get_emp['0']['emp_details']['branch_code'] : 'NULL';
        $company_code=$this->Session->read('company_code');
        $restrictedCompanies = [
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK',
    'GTRA','VGNN','SHYD','SRTS'
];
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
		if (in_array($company_code, $restrictedCompanies)) {
        $deleterecords = $this->EditPunches->query("delete from emp_detail_timeattandance where emp_pkey='$emp_pkey' and yearmonth='$month' and isdelete='Y' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m') ) ");
       
        if (!$shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )")) {
            return FALSE;
            die();
        }
        if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
            if (!$this->EditPunches->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")) {
                return false;
                die();
            }
        } else {
            if (!$this->EditPunches->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")) {
                return false;
                die();
            }
        }
         }
         else{

         }
    }

    public function Updateamendmens()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        $branch_code = $arr_data['brn'];
        $month = $arr_data['month'] . '-01';
        $get_emp = $this->EditPunches->query("select emp_pkey from emp_details where branch_code= '$branch_code' and status = '1' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq is not null)");
         $company_code=$this->Session->read('company_code');

        //die();
        //debug($get_emp);
        foreach ($get_emp as $value) {
            //debug($value);
            $emp_pkey = isset($value['emp_details']['emp_pkey']) ? $value['emp_details']['emp_pkey'] : 0;
            $restrictedCompanies = [
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK',
    'GTRA','VGNN','SHYD','SRTS'
];
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
		if (in_array($company_code, $restrictedCompanies)) {
            $deleterecords = $this->EditPunches->query("delete from  emp_detail_timeattandance where emp_pkey in ('$emp_pkey')  and yearmonth='$month' and isdelete='Y' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m')) ");
        }

            $shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )");
            if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
                if (!$this->EditPunches->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")) {
                    continue;
                    //return false;
                    //die();
                }
            } else {
                if (!$this->EditPunches->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")) {
                    continue;
                    //return false;
                    //die();
                }
            }
        }
        return true;
    }

    public function form($empid = 0, $att_date = '', $site_t_fkey = '')
    {
        $this->layout = null;
        $this->set("empid", $empid);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $site_detailss = array();

        if (($site_t_fkey != 0)) {
            $site_detailss = $this->EmployeeDetails->query("select * from site_transactions left join designation on (designation.id = site_transactions.designation_id) left join site on (site.site_pkey = site_transactions.site_fkey) where site_transactions_pkey = '$site_t_fkey' ");
        }
        $emp = $this->EmployeeDetails->query("select emp_pkey from emp_details where emp_id = $empid");
        foreach ($emp as $val) {
            $emp_pkey = isset($val['emp_details']['emp_pkey']) ? $val['emp_details']['emp_pkey'] : '';
        }
        $arr_leave = $this->EmployeeDetails->query("select emp_leave_transactions.Leavestatus, emp_leave_transactions.leave_session,emp_leave_transactions.Remarks,le.salary_head_item_fkey,sal.item from emp_leave_transactions
            left join leaveentries as le on (le.LEAVEENTRYID  = emp_leave_transactions.LEAVEENTRYID)
            left join salary_head_items as sal on (sal.salary_head_item_pkey = le.salary_head_item_fkey) where leave_date ='$att_date' and emp_leave_transactions.LEAVESTATUS in 
            ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
            and emp_leave_transactions.LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$emp_pkey')");

        $arr_leave_exists = $this->EmployeeDetails->query("select count(*) as COUNT  from emp_leave_transactions where leave_session = '3' and leave_date ='$att_date' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$emp_pkey')");
        $arr_diff_half_leave = $this->EmployeeDetails->query("select count(*) as COUNT from emp_leave_transactions where leave_session <> '3' "
            . " and leave_date ='$att_date' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') "
            . " and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$emp_pkey')");
        $diff_half_count = isset($arr_diff_half_leave[0][0]['COUNT']) ? $arr_diff_half_leave[0][0]['COUNT'] : 0;
        $leave = '';
        $sessions = '';
        $remarks = '';
        $statuss = '';
        $items = '';
        $half = '';
        $h_leave = array();
        foreach ($arr_leave as $value) {
            $status = isset($value['emp_leave_transactions']['Leavestatus']) ? $value['emp_leave_transactions']['Leavestatus'] : '';
            $remark = isset($value['emp_leave_transactions']['Remarks']) ? $value['emp_leave_transactions']['Remarks'] : '';
            $session = isset($value['emp_leave_transactions']['leave_session']) ? $value['emp_leave_transactions']['leave_session'] : '';
            $item = isset($value['sal']['item']) ? $value['sal']['item'] : '';
            if ($session == 3) {
                $remark = "Full day";
            }

            $leave .= "Cannot add attendance ," . $item . ' ' . $status . ' on ' . $remark . "";
            $sessions .= $session;
            if ($session == 1) {
                $half = "First half";
            }
            if ($session == 2) {
                $half = "Second half";
            }
            $remarks .=  $remark;
            $statuss .= $status;
            $items .= $item;
            $h_leave[] = $half . ' ' . $item . ' ' . $status;
        }
        $count = isset($arr_leave_exists[0][0]['COUNT']) ? $arr_leave_exists[0][0]['COUNT'] : 0;

        $this->set("leave", $leave);
        $this->set("statuss", $statuss);
        $this->set("items", $items);
        $this->set("h_leave", $h_leave);
        $this->set("sessions", $sessions);
        $this->set("remarks", $remarks);
        $this->set("count", $count);
        $this->set("att_date", $att_date);
        $this->set("site_t_fkey", $site_t_fkey);
        $this->set("diff_half_count", $diff_half_count);
    }


    public function remove()
    {
        $this->autoRender = FALSE;
        $device_attandance_seq = 0;
        $resp = array('success' => false);
        if (isset($_REQUEST['device_attandance_seq']) && $_REQUEST['device_attandance_seq'] != 0) {
            $device_attandance_seq = $_REQUEST['device_attandance_seq'];

            $data['device_attandance_seq'] = $device_attandance_seq;
            $data['status'] = "D";
            $this->EditPunches->useDbConfig = $this->Session->read('ds');
            $this->EditPunches->save($data);
            $resp = array('success' => true);
        }
        echo json_encode($resp);
    }

    //   <=======Edited by Amal ========> 
    public function insert_func($tableName = '', $data = array())
    {
        $this->EditPunchesHist->UseDbCnfig = $this->Session->read('ds');
        $fields = "INSERT INTO $tableName (";
        $values = " VALUES (";
        foreach ($data as $key => $value) {
            # code...
            $fields .= $key;
            $values .= "'" . $value . "'";
            end($data);
            if ($key != key($data)) {
                $fields .= ",";
                $values .= ",";
            }
        }
        $fields .= ")";
        $values .= ")";

        return $fields . " " . $values;
    }

    public function savenew()
    {
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EditPunchesHist->UseDbCnfig = $this->Session->read('ds');

        $resp = array();
        $data = array();
        $data["DIRECTION"] = $data["C1"] = $_POST["C1"];
        $data["C3"] = $_POST["C3"];
        $leavedate = $_POST["LOGDATE"];
        $emp_id = $_POST["empid"];
        $status = $_POST["empid"];
        $logdate = $_POST["LOGDATE"] . ' ' . $_POST["LOGTIME"];
        $d = strtotime($logdate);
        $data["LOGDATE"] = date("Y-m-d H:i:s", $d);
        $data["emp_id"] = $_POST["empid"];
        $data["device_attandance_seq"] = 0;
        $data["DEVICEID"] = 0;
        $data["company_code"] = $this->Session->read('company_code');
        $data["created_by"] = $this->Session->read('login_user_id');
        try {
            $br_details = $this->EmployeeDetails->find("first", array("conditions" => array("emp_id" => $_POST["empid"]), "fields" => array("branch_code")));
            $data["branch_code"] = isset($br_details['EmployeeDetails']['branch_code']) ? $br_details['EmployeeDetails']['branch_code'] : "";

            //--- Add condition for checking leave exists and restrict adding punch in upcoming days ---- Added By Nimisha 18/03/2019
            // start 
            $empid = $this->EmployeeDetails->query("select emp_pkey from emp_details where  emp_id = '$emp_id'");
            $status = $this->EmployeeDetails->query("select status from emp_details where  emp_id = '$emp_id'");
            $data['status'] = 'Y';

            $empkey = $empid['0']['emp_details']['emp_pkey'];
            $status = $status['0']['emp_details']['status'];
            $arr_leave = $this->EmployeeDetails->query("select Leavestatus,Remarks  from emp_leave_transactions where leave_session = '3' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");

            $arr_leave_exists = $this->EmployeeDetails->query("select count(*) AS COUNT from emp_leave_transactions where  leave_session = '3' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");
            $arr_f_half_day = $this->EmployeeDetails->query("select count(*) as COUNT  from emp_leave_transactions where leave_session = '1' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");
            $arr_s_half_day =  $this->EmployeeDetails->query("select count(*) as COUNT  from emp_leave_transactions where leave_session = '2' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");

            $f_half_count = isset($arr_f_half_day[0][0]['COUNT']) ? $arr_f_half_day[0][0]['COUNT'] : 0;
            $s_half_count = isset($arr_s_half_day[0][0]['COUNT']) ? $arr_s_half_day[0][0]['COUNT'] : 0;
            $leave = '';
            foreach ($arr_leave as $value) {
                $status = isset($value['emp_leave_transactions']['Leavestatus']) ? $value['emp_leave_transactions']['Leavestatus'] : '';
                $remark = isset($value['emp_leave_transactions']['Remarks']) ? $value['emp_leave_transactions']['Remarks'] : '';
                $leave .= $remark . '-' . $status;
            }

            $count = isset($arr_leave_exists[0][0]['COUNT']) ? $arr_leave_exists[0][0]['COUNT'] : 0;

            if ($count > 0) {
                $resp["success"] = false;
                $resp['msg'] = "Cannot add attendance, Leave Exists in this Date.";
                return json_encode($resp);
            }

            $arr_regularisation_exist = $this->EmployeeDetails->query("SELECT count(*) as COUNT from employee_regularaization where
        C1 = '" . $data["C1"] . "' and LOGDATE = '" . $leavedate . "' and approved in ('P') and empid = '" . $emp_id . "' and status = 1");

            $count = isset($arr_regularisation_exist[0][0]['COUNT']) ? $arr_regularisation_exist[0][0]['COUNT'] : 0;
            if ($count > 0) {
                $resp["success"] = false;
                $resp['msg'] = "Cannot add attendance, Attendance Regularisation Pending in this Date.";
                return json_encode($resp);
            }

            date_default_timezone_set("Asia/Calcutta");
            $curtime = time();
            $time = strtotime($data["LOGDATE"]);
            //debug($data);
            if ($curtime > $time) {
                try {
                    $this->EditPunches->save($data);
                } catch (RuntimeException $e) {
                    $resp["success"] = false;
                    $resp['msg'] = "Cannot add duplicate attendance for the same date.";
                    return json_encode($resp);
                }
                //     debug($data);
                // $this->EditPunchesHist->save($data);
                // amal->
                $data["status"] = 'Y';
                $data["action"] = 'insert';
                $this->EmployeeDetails->query($this->insert_func("device_attandance_hist", $data));

                // <-amal
                $resp["success"] = true;
                $resp['msg'] = "New Attendance saved successfully";
                return json_encode($resp);
            } else {
                $resp["success"] = false;
                $resp['msg'] = "Cannot add attendance to upcoming dates.";
                return json_encode($resp);
            }
        } catch (RuntimeException $e) {
            $resp["success"] = false;
            $resp['msg'] = "Cannot add duplicate attendance for the same date.";
            return json_encode($resp);
        }
        //End Nimisha 18/03/2019

    }
    public function savepunch()
    {

        $resp = array('success' => true);
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EditPunchesHist->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $_POST["modified_by"] = $this->Session->read('login_user_id');

        $data = array();

        $this->EditPunches->save($_POST);
        $data = $_REQUEST;

        //-> amal
        $direction = $data['C1'];
        $action = $data['status'];
        if ($action == 'Y') {
            $actions = 'Active';
        } else {
            $actions = 'Inactive';
        }

        if ($direction == 'in') {
            $dire = 'In';
        } else {
            $dire = 'Out';
        }
        if ($action == 'Y') {
            $out = $actions;
        } else {
            $out = $actions;
        }
        $data['action'] = $out;
        $data['created_by'] = $this->Session->read('login_user_id');

        $this->EmployeeDetails->query($this->insert_func("device_attandance_hist", $data));
        // <-amal
        echo json_encode($resp);
    }
    //    public function savepunch($empid = 0) {
    //
    //       
    //        $resp = array('success' => true);
    //        $arr_data = $this->request->data;
    //        $this->autoRender = FALSE;
    //        $this->EditPunches->useDbConfig = $this->Session->read('ds');
    //        $this->EditPunchesHist->useDbConfig = $this->Session->read('ds');
    //        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //        $resp = array();
    //        $data = array();
    //        $data["C1"] = $_POST["C1"];
    //        $data["C3"] = $_POST["C3"];
    //        $leavedate = $_POST["LOGDATE"];
    //        
    //        $logdate = $_POST["LOGDATE"] . ' ' . $_POST["LOGTIME"];
    //        $d = strtotime($logdate);
    //        $data["LOGDATE"] = date("Y-m-d H:i:s", $d);
    //        $data["emp_id"] =$empid;
    //       
    //        $data["device_attandance_seq"] = 0;
    //        $data["DEVICEID"] = 0;
    //        $data["company_code"] = $this->Session->read('company_code');
    //        $data["created_by"] = $this->Session->read('login_user_id');
    //        $data["status"] = $arr_data['status'];
    //        //$data["company_code"] = $this->Session->read('company_code');
    //        $br_details = $this->EmployeeDetails->find("first", array("conditions" => array("emp_id" => $empid), "fields" => array("branch_code")));
    //      
    //        $data['branch_code'] = isset($br_details['EmployeeDetails']['branch_code']) ? $br_details['EmployeeDetails']['branch_code'] : "";
    //        
    //       //--- Add condition for checking leave exists and restrict adding punch in upcoming days ---- Added By Nimisha 18/03/2019
    //        // start 
    //      
    //        $count = isset($arr_leave_exists[0][0]['COUNT']) ? $arr_leave_exists[0][0]['COUNT'] : 0;
    //       
    ////        $_POST["edited"] = 'Y';
    ////        $_POST["edited_time"] = date('Y-m-d H:i:s');
    //        $this->EditPunches->save($data);
    //        
    //
    //          //-> amal
    //         $data = $_REQUEST;
    //      $direction = $data['C1'];
    //      $action = $data['status'];
    //         if ($action == 'Y'){
    //        $actions = 'Active';
    //      }
    //      else{
    //        $actions = 'Inactive';
    //      }
    //     
    //      if($direction == 'in' ){
    //              $dire = 'In';
    //      }
    //      else{
    //        $dire = 'Out';
    //      }
    //      if($action == 'Y'){
    //        $out =$actions ;
    //      }
    //      else{
    //          $out = $actions;
    //      }
    //       $data['action'] = $out;
    //        //-> amal
    //        $this->EmployeeDetails->query($this->insert_func("device_attandance_hist",$data));
    //        // <-amal
    //        echo json_encode($resp);
    //    }
    public function getmonths()
    {
        //$this->autoRender = FALSE;
        $arr_months = array();
        $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
        for ($i = 0; $i < 12; $i++) {
            $month = date('Y-m', strtotime("-$i month", $start_month));
            $arr_months[] = array(
                'id' => $month,
                'text' => $month
            );
        }
        return json_encode($arr_months);
    }

    public function chnagestatus_old()
    {

        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');

        $data = $this->EditPunches->query("SELECT * FROM emp_detail_timeattandance WHERE emp_detail_timeattandance_pkey = '" . $_POST["device_attandance_seq"] . "' ");
        $status = $_POST["status"];

        // need to check about the N/A, WO, HO cases how to handle if they come 

        $newStatus = $_POST["status"]; // updated string as text only
        $statusType = $_POST["statusType"]; // types
        $newstatuses = $_POST["newstatuses"]; // new selected status
        $currentStatuses = $_POST["currentStatuses"]; // existing statuses
        // $isleave = $_POST["isleave"];

        $this->checkLeaveExists($data[0]['emp_detail_timeattandance']['att_date'], $data[0]['emp_detail_timeattandance']['emp_pkey'], $statusType);

        if ($statusType == 'full') {
            $newStatus = $newstatuses;

            if (!in_array($newstatuses, array('P/P', 'P/A', 'A/P', 'A/A', 'N/A', 'WO', 'HO'))) {

                $app = $this->AddLeave($newstatuses, $data[0]['emp_detail_timeattandance']['att_date'], $data[0]['emp_detail_timeattandance']['emp_pkey'], 1);
                $this->updateStatus($newstatuses, $data[0]['emp_detail_timeattandance']);
                $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET present = '', holiday = '', weekoff = '', others = '', leaves  = '" . $newstatuses . "' WHERE emp_detail_timeattandance_pkey = '" . $_POST["device_attandance_seq"] . "' ");
            } else {
                $this->updateStatus($newstatuses, $data[0]['emp_detail_timeattandance']);
                $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET present = '" . $newstatuses . "', holiday = '', weekoff = '', others = '', leaves  = '' WHERE emp_detail_timeattandance_pkey = '" . $_POST["device_attandance_seq"] . "' ");
            }
        } else {

            if (!in_array($newstatuses, array('P', 'A', 'N/A', 'WO', 'HO', '/WO'))) {

                $app = $this->AddLeave($newstatuses, $data[0]['emp_detail_timeattandance']['att_date'], $data[0]['emp_detail_timeattandance']['emp_pkey'],  $statusType);

                $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET leaves  = '" . $newstatuses . "', present = '', holiday = '', weekoff = '', others = '' WHERE emp_detail_timeattandance_pkey = '" . $_POST["device_attandance_seq"] . "' ");
                // $this->updateStatus($newStatus, $data[0]['emp_detail_timeattandance']);
            } else {
                if ($data[0]['emp_detail_timeattandance']['present'] == '') {
                    if ($statusType == 'first') {
                        // $newStatus = $newstatuses . '/A';
                    } else {
                        // $newStatus = 'A/' . $newstatuses;
                    }
                } else {

                    if ($statusType == 'first') {
                        // $newStatus = $newstatuses . '/' . explode("/", $data[0]['emp_detail_timeattandance']['present'])[1];
                    } else {
                        if ($newstatuses == "WO") {
                            $newStatus = $newStatus; // "/" . $newstatuses;
                        } else {
                            // $newStatus = explode("/", $data[0]['emp_detail_timeattandance']['present'])[0] . '/' . $newstatuses;
                        }
                    }

                    $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET present = '" . $newStatus . "', holiday = '', weekoff = '', others = '' WHERE emp_detail_timeattandance_pkey = '" . $_POST["device_attandance_seq"] . "' ");
                }
            }

            $this->updateStatus($newStatus, $data[0]['emp_detail_timeattandance']);
        }


        $resp = array('success' => true);

        echo json_encode($resp);
    }

    public function chnagestatus()
    {

        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');

        $data = $this->EditPunches->query("SELECT * FROM emp_detail_timeattandance WHERE emp_detail_timeattandance_pkey = '" . $_POST["device_attandance_seq"] . "' ");
        $status = $_POST["status"];

        // need to check about the N/A, WO, HO cases how to handle if they come 

        $newStatus = $_POST["status"]; // updated string as text only
        $statusType = $_POST["statusType"]; // types
        $newstatuses = $_POST["newstatuses"]; // new selected status
        $currentStatuses = $_POST["currentStatuses"]; // existing statuses
        // $isleave = $_POST["isleave"];
        // debug($data);
        $this->checkLeaveExists($data[0]['emp_detail_timeattandance']['att_date'], $data[0]['emp_detail_timeattandance']['emp_pkey'], $statusType); //Edited by Akshay on 4-12-2024
        $arr_leave = $this->EditPunches->query("SELECT * FROM leaveentries WHERE LEAVESTATUS = 'Approved' and EMP_fkey = '" . $data[0]['emp_detail_timeattandance']['emp_pkey'] . "' and " . " FROMDATE = '" . $data[0]['emp_detail_timeattandance']['att_date'] . "' and leave_days > 0"); // Edited by Akshay on 4-12-2024
        if ($statusType == 'full') {
            $newStatus = $newstatuses;

            if (!in_array($newstatuses, array('P/P', 'P/A', 'A/P', 'A/A', 'N/A', 'WO', 'HO'))) {

                $app = $this->AddLeave($newstatuses, $data[0]['emp_detail_timeattandance']['att_date'], $data[0]['emp_detail_timeattandance']['emp_pkey'], $statusType);
                if(!empty($arr_leave)) // Edited by Akshay on 4-12-2024
                $this->updateStatus($newstatuses, $data[0]['emp_detail_timeattandance']);
                // $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET present = '', holiday = '', weekoff = '', others = '', leaves  = '" . $newstatuses . "' WHERE emp_detail_timeattandance_pkey = '" . $_POST["device_attandance_seq"] . "' ");
            } else {
                $this->updateStatus($newstatuses, $data[0]['emp_detail_timeattandance']);
                // $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET present = '" . $newstatuses . "', holiday = '', weekoff = '', others = '', leaves  = '' WHERE emp_detail_timeattandance_pkey = '" . $_POST["device_attandance_seq"] . "' ");
            }
        } else {

            if (!in_array($newstatuses, array('P', 'P/P', 'P/A', 'A/P', 'A', 'N/A', 'WO', 'HO', '/WO'))) {

                $app = $this->AddLeave($newstatuses, $data[0]['emp_detail_timeattandance']['att_date'], $data[0]['emp_detail_timeattandance']['emp_pkey'],  $statusType);

                // $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET leaves  = '" . $newstatuses . "', present = '', holiday = '', weekoff = '', others = '' WHERE emp_detail_timeattandance_pkey = '" . $_POST["device_attandance_seq"] . "' ");
                // $this->updateStatus($newStatus, $data[0]['emp_detail_timeattandance']);
            } else {
                if ($statusType == 'first') {
                    // $newStatus = $newstatuses . '/' . explode("/", $data[0]['emp_detail_timeattandance']['present'])[1];
                } else {
                    if ($newstatuses == "WO") {
                        $newStatus = $newStatus; // "/" . $newstatuses;
                    } else {
                        $newStatus = $newStatus; //$newStatus = explode("/", $data[0]['emp_detail_timeattandance']['present'])[0] . '/' . $newstatuses;
                    }
                }
            }
            // Edited by Akshay on 4-12-2024
            if (in_array($newstatuses, array('P', 'P/P', 'P/A', 'A/P', 'A', 'N/A', 'WO', 'HO', '/WO')) || !empty($arr_leave)){
                $this->updateStatus($newStatus, $data[0]['emp_detail_timeattandance']);
              
            }
            // End
        }


        $resp = array('success' => true, 'status' => $newStatus);

        echo json_encode($resp);
    }

    public function chnagestatusadditonal()
    {


        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');

        $data = $this->EditPunches->query("SELECT * FROM emp_detail_timeattandance WHERE emp_detail_timeattandance_pkey = '" . $_POST["device_attandance_seq"] . "' ");
        $status = $_POST["status"];

        // need to check about the N/A, WO, HO cases how to handle if they come 

        $newStatus = $_POST["status"]; // updated string as text only
        $statusType = $_POST["statusType"]; // types
        $newstatuses = $_POST["newstatuses"]; // new selected status
        $currentStatuses = $_POST["currentStatuses"]; // existing statuses
        $statusmain = $_POST["statusmain"];
        // $isleave = $_POST["isleave"];

        $this->EditPunches->query("INSERT INTO emp_detail_status_update (emp_fkey, att_date, yearmonth, aditional_status, main_status, creation_date, created_by) " . "VALUES ('" . $data[0]['emp_detail_timeattandance']['emp_pkey'] . "', '" . $data[0]['emp_detail_timeattandance']['att_date'] . "', '" . $data[0]['emp_detail_timeattandance']['yearmonth'] . "', '" . $newStatus . "', '" . $statusmain . "', '" . date('Y-m-d H:i:s') . "', '" . $this->Session->read('login_user_id') . "') ");

        $resp = array('success' => true);

        echo json_encode($resp);
    }

    function checkLeaveExists($att_date, $emp_pkey, $statusType)
    {

        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');

        $data = $this->EditPunches->query("SELECT * FROM leaveentries WHERE LEAVESTATUS = 'Approved' and EMP_fkey = '" . $emp_pkey . "' and " . " FROMDATE = '" . $att_date . "' ");

        if (!empty($data)) {


            for ($i = 0; $i < count($data); $i++) {
                # code...
                $fromdate = $data[$i]['leaveentries']['FROMDATE'];
                $fromhalf = $data[$i]['leaveentries']['FROMHALF'];
                $todate = $data[$i]['leaveentries']['TODATE'];
                $tohalf = $data[$i]['leaveentries']['TOHALF'];
                $leavedays = $data[$i]['leaveentries']['leave_days'];
                $leaveentryId = $data[$i]['leaveentries']['LEAVEENTRYID'];
                $leavestatuses = "Cancelled";
                $outs = $this->LeaveRequests->query("CALL leave_transaction_prc('$leaveentryId','$emp_pkey','$fromdate','$fromhalf','$todate','$tohalf','$leavedays','$leavestatuses',@Perror_message);");
                // return true;

                $this->LeaveRequests->query("DELETE FROM emp_leave_transactions WHERE LEAVEENTRYID = '$leaveentryId' ");
                $this->LeaveRequests->query("DELETE FROM leaveentries WHERE LEAVEENTRYID = '$leaveentryId' ");
            }
        } else {
            // return true;
        }

        if (!empty($data)) {
            if ($statusType == 'full') {
                return true;
            } else {

                if ($data[0]['leaveentries']['leave_days'] == 1) {

                    if ($statusType == 'first') {
                        $statusType = 'second';
                    } else {
                        $statusType = 'first';
                    }

                    $loccurance = $this->EditPunches->query("SELECT occurance FROM salary_head_items WHERE salary_head_item_pkey = '" . $data[0]['leaveentries']['salary_head_item_fkey'] . "' ");

                    $app = $this->AddLeave($loccurance[0]['salary_head_items']['occurance'], $data[0]['leaveentries']['FROMDATE'], $data[0]['leaveentries']['EMP_fkey'],  $statusType);
                } else {

                    $loccurance = $this->EditPunches->query("SELECT occurance FROM salary_head_items WHERE salary_head_item_pkey = '" . $data[0]['leaveentries']['salary_head_item_fkey'] . "' ");

                    if ($data[0]['leaveentries']['FROMHALF'] == 1) {
                        // fisrt half
                        if ($statusType == 'second') {
                            $statusType = 'first';
                            $app = $this->AddLeave($loccurance[0]['salary_head_items']['occurance'], $data[0]['leaveentries']['FROMDATE'], $data[0]['leaveentries']['EMP_fkey'],  $statusType);
                        }
                    } else if ($data[0]['leaveentries']['FROMHALF'] == 2) {
                        // second half
                        if ($statusType == 'first') {
                            $statusType = 'second';
                            $app = $this->AddLeave($loccurance[0]['salary_head_items']['occurance'], $data[0]['leaveentries']['FROMDATE'], $data[0]['leaveentries']['EMP_fkey'],  $statusType);
                        }
                    } else {
                        // full day
                    }
                }
                return true;
            }
        } else {
            return true;
        }
    }

    public function bulkipdatestatus()
    {

        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');

        // need to check about the N/A, WO, HO cases how to handle if they come 

        $device_attandance_seq = $_POST["device_attandance_seq"]; // updated string as text only
        $status = $_POST["status"]; // types
        $adstatus = $_POST["adstatus"]; // new selected status
        $emp = $_POST["emp"];

        $empdetails = $this->EditPunches->query("select emp_pkey FROM emp_details WHERE emp_id = '" . $emp . "'");

        $emp_pkey = $empdetails[0]['emp_details']['emp_pkey'];


        foreach ($device_attandance_seq as $key => $value) {

            $data = $this->EditPunches->query("SELECT * FROM emp_detail_timeattandance WHERE att_date = '" . $value . "' and " . " emp_pkey = '" . $emp_pkey . "' and " . " yearmonth = '" . date('Y-m-1', strtotime($value)) . "' ");
            $data = $data[0]['emp_detail_timeattandance'];

            if (!empty($data)) {
                if ($status != "0") {
                    $this->checkLeaveExists($data['att_date'], $data['emp_pkey'], $status);

                    if ($adstatus != "0") {
                        $adStatsu = $adstatus;
                    } else {
                        $adStatsu = $data['ad_present'];
                    }


                    if ($status == "LOP") {
                        $app = $this->AddLeave($status, $data['att_date'], $data['emp_pkey'], 'full');
                        $get_emp = $this->EditPunches->query("INSERT INTO emp_detail_status_update (emp_fkey, att_date, yearmonth, main_status, aditional_status, creation_date, created_by) " . "VALUES ('" . $data['emp_pkey'] . "', '" . $data['att_date'] . "', '" . $data['yearmonth'] . "', '" . trim($status) . "', '" . $adStatsu . "', '" . date('Y-m-d H:i:s') . "', '" . $this->Session->read('login_user_id') . "') ");
                        // $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET present = '', holiday = '', weekoff = '', others = '', leaves  = '" . $status . "' WHERE emp_detail_timeattandance_pkey = '" . $data[0]['emp_detail_timeattandance']['emp_detail_timeattandance_pkey'] . "' ");
                    } else {
                        $get_emp = $this->EditPunches->query("INSERT INTO emp_detail_status_update (emp_fkey, att_date, yearmonth, main_status, aditional_status, creation_date, created_by) " . "VALUES ('" . $data['emp_pkey'] . "', '" . $data['att_date'] . "', '" . $data['yearmonth'] . "', '" . trim($status) . "', '" . $adStatsu . "', '" . date('Y-m-d H:i:s') . "', '" . $this->Session->read('login_user_id') . "') ");
                        // $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET present = '" . $status . "', holiday = '', weekoff = '', others = '', leaves  = '' WHERE emp_detail_timeattandance_pkey = '" . $data[0]['emp_detail_timeattandance']['emp_detail_timeattandance_pkey'] . "' ");
                    }
                }
            }
            // $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET present  = '" . $status . "' WHERE emp_detail_timeattandance_pkey = '" . $_POST["device_attandance_seq"] . "' ");
        }

        $resp = array('success' => true);

        echo json_encode($resp);
    }

    public function getstatus()
    {
    }

    public function updateStatus($newStatus, $data)
    {

        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');

        $get_emp = $this->EditPunches->query("INSERT INTO emp_detail_status_update (emp_fkey, att_date, yearmonth, main_status, aditional_status, creation_date, created_by) " . "VALUES ('" . $data['emp_pkey'] . "', '" . $data['att_date'] . "', '" . $data['yearmonth'] . "', '" . trim($newStatus) . "', '" . $data['ad_present'] . "', '" . date('Y-m-d H:i:s') . "', '" . $this->Session->read('login_user_id') . "') ");
       
        return $get_emp;
    }

    public function sendmemo()
    {



        $Email = new CakeEmail();
        $Email->from(array('sruthi.pb@gmail.com' => 'My Site'));
        $Email->to('sruthiforsight@gmail.com');
        $Email->subject('About');
        $Email->send('My message');
        //debug($Email);
        echo "sucess";
    }

    public function getmonthsList()
    {

        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');

        $monthyear = $_POST["month"];
        $emp = $_POST["emp"];

        $empdetails = $this->EditPunches->query("select emp_pkey FROM emp_details WHERE emp_id = '" . $emp . "'");
        $emp_pkey = $empdetails[0]['emp_details']['emp_pkey'];
        $sql = "select count(*) as v1 from
        emp_detail_timeattandance where present='P/P'
        and yearmonth = DATE_FORMAT(concat('" . $monthyear . "-01'), '%Y-%m-01') and emp_pkey = $emp_pkey and att_date !='0000-00-00'
        union all
        select count(present) * 0.5 as v1 from
        emp_detail_timeattandance where present in('P/A','A/P')
        and yearmonth = DATE_FORMAT(concat('" . $monthyear . "-01'), '%Y-%m-01') and emp_pkey = $emp_pkey and att_date !='0000-00-00'";
        $data = $this->EditPunches->query($sql);
        $presentDays = 0;
        $arr_attendance_register = $this->EditPunches->query("select count(*) as count from attendance_register where emp_fkey = $emp_pkey AND month_year = '" . date('Y-m', strtotime($monthyear)) . "' AND isdelete = 'N'");
        $iseditable = isset($arr_attendance_register[0][0]['count']) ? $arr_attendance_register[0][0]['count'] : 0;
        if($iseditable = 0){
        if($data){
             $presentDays = (isset($data[0][0]['v1']) ? $data[0][0]['v1'] : 0) + (isset($data[1][0]['v1']) ? $data[1][0]['v1'] : 0);
        }
        }else{
           $arr_attendance_register = $this->EditPunches->query("select presant_total from attendance_register where emp_fkey = $emp_pkey AND month_year = '" . date('Y-m', strtotime($monthyear)) . "' AND isdelete = 'N'");
        $presentDays = isset($arr_attendance_register[0]['attendance_register']['presant_total']) ? $arr_attendance_register[0]['attendance_register']['presant_total'] : 0;
        
        }

        $weeks = $this->EditPunches->query("select count(*) as v1 from emp_detail_timeattandance where weekoff = 'WO' and yearmonth = DATE_FORMAT(concat('" . $monthyear . "-01'), '%Y-%m-01') and emp_pkey = $emp_pkey; ");

        $holiday = $this->EditPunches->query("select count(*) as v1 from emp_detail_timeattandance where holiday = 'HO' and yearmonth = DATE_FORMAT(concat('" . $monthyear . "-01'), '%Y-%m-01') and emp_pkey = $emp_pkey; ");

        $dd = $this->EditPunches->query("select count(*) as v1 from
        emp_detail_timeattandance where ad_present IN ('D/D', 'P/P', 'D/P')
        and yearmonth = DATE_FORMAT(concat('" . $monthyear . "-01'), '%Y-%m-01') and emp_pkey = $emp_pkey
        union all
        select count(ad_present) * 0.5 as v1 from
        emp_detail_timeattandance where ad_present in('D/A','P/A','A/P','P/D','A/D')
        and yearmonth = DATE_FORMAT(concat('" . $monthyear . "-01'), '%Y-%m-01') and emp_pkey = $emp_pkey ");

        $sqls = "select count(present) * 0.5 as v1 from
        emp_detail_timeattandance where leaves IN ('LOP/A', 'LOP/P', 'A/LOP', 'P/LOP')
        and yearmonth = DATE_FORMAT(concat('" . $monthyear . "-01'), '%Y-%m-01') and emp_pkey = $emp_pkey
        union all
        select count(*) as v1 from
        emp_detail_timeattandance where leaves in('LOP/LOP')
        and yearmonth = DATE_FORMAT(concat('" . $monthyear . "-01'), '%Y-%m-01') and emp_pkey = $emp_pkey ";

        $lop = $this->EditPunches->query($sqls);

        $pl = $this->EditPunches->query("select count(*) as v1 from emp_detail_timeattandance where holiday is not null and yearmonth = DATE_FORMAT(concat('" . $monthyear . "-01'), '%Y-%m-01') and emp_pkey = $emp_pkey; ");

        $emp = $emp_pkey;

        // leave balance
        $dats = $monthyear . '-01'; //date('Y-m-01');
        $att_enddate = $this->EditPunches->query("select att_start_end_fn(DATE_FORMAT('$dats', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];

        $years = $this->EditPunches->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = 0 and is_current_finyear = 'Y' and status = '1' and branch_code in (select branch_code from emp_details where emp_pkey = '$emp_pkey') ");
        $year = isset($years['0']['fin_year']['fin_year']) ? $years['0']['fin_year']['fin_year'] : 0;
        $arr_leaves_heads = $this->EditPunches->query("SELECT salary_head_item_pkey,occurance FROM `salary_head_items` "
            . "WHERE head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) "
            . "and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where LEAVEPOLICY_GROUP_ID IN "
            . "(SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_pkey') and status = 1 )");
        $arr_leave = array();
        $arr_leave_t = [];
        //DEBUG($arr_leaves_heads);

        foreach ($arr_leaves_heads as $key => $val) {
            $head = $val['salary_head_items']['occurance'];
            $slary_head_item_pkey = $val['salary_head_items']['salary_head_item_pkey'];
            $lbalance = $this->EditPunches->query("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
            // debug("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
            $resp = isset($lbalance['0']['0']['LeaveBalance']) ? round($lbalance['0']['0']['LeaveBalance'], 1) : 0;

            $take = $this->EditPunches->query("SELECT SUM(leave_days) as takencounts FROM `leaveentries` 
                left join emp_leave_transactions on (emp_leave_transactions.Leaveentryid = leaveentries.Leaveentryid)
                WHERE emp_leave_transactions.`LEAVESTATUS` = 'Approved' AND `EMP_fkey` = '$emp_pkey' 
                AND `salary_head_item_fkey` = '$slary_head_item_pkey' and leave_date >= '$dats' and leave_date <= '$att_enddate1'");
            //$take = $this->EditPunches->query("SELECT SUM(leave_days) as takencounts FROM `leaveentries` WHERE `LEAVESTATUS` = 'Approved' AND `EMP_fkey` = '$emp_pkey' AND `salary_head_item_fkey` = '$slary_head_item_pkey' ");
            // debug("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
            $leavetaken = isset($take['0']['0']['takencounts']) ? $take['0']['0']['takencounts'] : 0;

            $arr_leave[] = array(
                "salary_head_item_pkey" => $slary_head_item_pkey,
                "Head" => $head,
                "leaveBalance" => $resp,
            );

            $arr_leave_t[] = array(
                "head" => $head, "balance" => $leavetaken
            );
        }

        $shoftNames = $this->EditPunches->query("select wrkd1.day_time_desc as shiftname, wrkd2.day_time_desc as shiftnames FROM emp_proff LEFT JOIN working_day_time_procedures as wrkd1 ON (wrkd1.day_time_seq = emp_proff.day_time_seq) LEFT JOIN working_day_time_procedures as wrkd2 ON (wrkd2.day_time_seq = emp_proff.multishift) WHERE emp_fkey = $emp_pkey LIMIT 1; ");

//        $presentDays = 0;
//        if ($data) {
//            $presentDays = (isset($data[0][0]['v1']) ? $data[0][0]['v1'] : 0) + (isset($data[1][0]['v1']) ? $data[1][0]['v1'] : 0);
//        } else {
//
//            $presentDays = 0;
//        }

        $dpresentDays = 0;
        if ($data) {
            $dpresentDays = (isset($dd[0][0]['v1']) ? $dd[0][0]['v1'] : 0) + (isset($dd[1][0]['v1']) ? $dd[1][0]['v1'] : 0);
        } else {

            $dpresentDays = 0;
        }

        $lopdays = 0;
        if ($lop) {
            $lopdays = (isset($lop[0][0]['v1']) ? $lop[0][0]['v1'] : 0) + (isset($lop[1][0]['v1']) ? $lop[1][0]['v1'] : 0);
        } else {

            $lopdays = 0;
        }

        $month = date('m', strtotime($monthyear . "-01")); // February
        $year = date('Y', strtotime($monthyear . "-01"));
        $numberOfDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $resp = array(
            'success' => true,
            'presentDays' => $presentDays,
            'weeks' => isset($weeks[0][0]['v1']) ? $weeks[0][0]['v1'] : 0,
            'leaves' => $arr_leave,
            'holiday' => isset($holiday[0][0]['v1']) ? $holiday[0][0]['v1'] : 0,
            'numberdays' => $numberOfDays,
            'shoftNames1' => isset($shoftNames[0]['wrkd1']['shiftname']) ? $shoftNames[0]['wrkd1']['shiftname'] : 'No Shift 1',
            'shoftNames2' => isset($shoftNames[0]['wrkd2']['shiftnametwo']) ? $shoftNames[0]['wrkd2']['shiftnametwo'] : 'No Multiple Shift',
            'lopdays' => $lopdays,
            'dd' => $dpresentDays,
            "leave" => $arr_leave_t

        );

        echo json_encode($resp);
    }
    public function ealry_out_late_in()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $arr_data = $this->request->data;
        $emp_pkey = $arr_data['emp'];
        $month = $arr_data['month'];
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //         $arr_late_in = $this->EmployeeDetails->query("SELECT COUNT(*) AS count
        //         FROM (
        //     (device_attandance AS att
        //     JOIN emp_details AS ed ON att.emp_id = ed.emp_id AND LOWER(att.C1) = 'in')
        //     JOIN emp_proff AS ep ON ed.emp_pkey = ep.emp_fkey
        //     )
        //     JOIN working_day_time_procedures AS wtp ON wtp.day_time_seq = ep.day_time_seq
        // WHERE TIME_FORMAT(att.LOGDATE, '%H:%i:%s') > ADDTIME(wtp.on_dutty1, SEC_TO_TIME(wtp.minuts_aftr_on_dutty_cal_late * 60))
        //     AND att.emp_id  = $emp_pkey
        //     AND att.status = 'Y'
        //     AND att.LOGDATE IN (
        //         SELECT MIN(device_attandance.LOGDATE)
        //         FROM device_attandance
        //         WHERE LOWER(device_attandance.C1) = 'in'
        //             AND UPPER(device_attandance.status) = 'Y'
        //             AND DATE_FORMAT(att.LOGDATE, '%Y-%m') = '" . date('Y-m', strtotime($month)) . "'
        //             AND att.DEVICEID > 0
        //         GROUP BY DATE_FORMAT(device_attandance.LOGDATE, '%Y-%m-%d'), device_attandance.emp_id)");
        //         $arr_late_incount = isset($arr_late_in[0][0]['count']) ? $arr_late_in[0][0]['count'] : 0;
        //         $arr_early_out = $this->EmployeeDetails->query("SELECT COUNT(*) AS count
        //         FROM ((device_attandance AS att
        //         JOIN emp_details AS ed ON att.emp_id = ed.emp_id AND LOWER(att.C1) = 'out')
        //         JOIN emp_proff AS ep ON ed.emp_pkey = ep.emp_fkey)
        //         JOIN working_day_time_procedures AS wtp ON wtp.day_time_seq = ep.day_time_seq
        //         WHERE TIME_FORMAT(att.LOGDATE, '%H:%i:%s') < ADDTIME(wtp.off_dutty1, SEC_TO_TIME(wtp.minuts_bfr_off_dutty_cal_early * 60))
        //         AND att.emp_id = $emp_pkey AND att.status = 'Y' AND att.LOGDATE IN (
        //         SELECT MAX(device_attandance.LOGDATE) FROM device_attandance
        //         WHERE LOWER(device_attandance.C1) = 'out' AND UPPER(device_attandance.status) = 'Y'
        //         AND DATE_FORMAT(LOWER(att.LOGDATE), '%Y-%m') = '" . date('Y-m', strtotime($month)) . "'
        //         AND TIMEDIFF(ADDTIME(wtp.off_dutty1, -(SEC_TO_TIME(wtp.minuts_bfr_off_dutty_cal_early * 60))), TIME_FORMAT(LOWER(att.LOGDATE), '%H:%i:%s')) > 0
        //         GROUP BY DATE_FORMAT(device_attandance.LOGDATE, '%Y-%m-%d'), device_attandance.emp_id)");
        //         $arr_early_outcount = isset($arr_early_out[0][0]['count']) ? $arr_early_out[0][0]['count'] : 0;
        //         $less_hour_count = 0;
        //         $count = $this->EmployeeDetails->query("select count(*) as count from less_hour_details where emp_fkey = '$emp_pkey' and month_year = '$month' and status = 1");
        //         $counts = $count['0']['0']['count'];
        //         if ($counts > 0) {
        //             $count = $this->EmployeeDetails->query("select less_hour_count from less_hour_details where emp_fkey = '$emp_pkey' and month_year = '$month' and status = 1");
        //             $less_hour_count = $count['0']['less_hour_details']['less_hour_count'];
        //         }
        $resp_mispunches["latein"] = 0; //$arr_late_incount;
        $resp_mispunches["earlyout"] = 0; //$arr_early_outcount;
        $resp_mispunches["lesshour"] = 0; // $less_hour_count;
        $resp_mispunches["type"] = "success";
        echo json_encode($resp_mispunches);
    }
    public function lesshoursave()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $arr_data = $this->request->data;
        $emp_id = $arr_data['emp'];
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->LessHourDetails->useDbConfig = $this->Session->read('ds');
        $emp_pkey = $this->EmployeeDetails->query("select emp_pkey from emp_details where emp_id = '$emp_id'");
        $data['emp_fkey'] = $emp_fkey = $emp_pkey['0']['emp_details']['emp_pkey'];
        $data['month_year'] = $month = $arr_data['month'];
        $data["created_by"] = $modified = $this->Session->read('login_user_id');
        $count = $this->LessHourDetails->query("select count(*) as count from less_hour_details where emp_fkey = '$emp_fkey' and month_year = '$month' and status = 1");
        $counts = $count['0']['0']['count'];

        if ($counts > 0) {
            $condition['emp_fkey'] = $emp_fkey;
            $condition['month_year'] = $month;
            $condition['status'] = 1;
            $this->LessHourDetails->updateAll(array('status' => "0", 'modified_by' =>  "'" . $modified . "'"), $condition);
        }
        $data['late_in_count'] = $arr_data['latein'];
        $data['early_out_count'] = $arr_data['earlyout'];
        $data['less_hour_count'] = $arr_data['lesshour'];

        $result = $this->LessHourDetails->save($data);
        $resp = array('success' => $result);

        echo json_encode($resp);
    }
}
