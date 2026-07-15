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
ini_set('max_execution_time', 30000);
ini_set('memory_limit', '-1');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class TimesheetController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Timesheet';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Units', 'AttendanceRegister', 'DbConfig', 'EditPunches', 'LeaveRequests');
    public $components = array('MasterdataManagement');

    public function registerbook($monthdd = '', $emp_pkey = '', $branch = '')
    {
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
         $plan = $this->EmployeeDetails->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        $company_code = strtoupper($this->Session->read('company_code'));
        $user_login = $this->Session->read('login_user_id');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = $monthdd;
        $yearmonth = $month . '-01';
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        //added by megha on on 11_03_2020 changed for SH Infra
        //$att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        //$att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
        $att_startdate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_startdate1 = date("d", strtotime($att_startdate['0']['0']['monthly_att_fromdate']));
        $att_enddate1 = date("d", strtotime($att_enddate['0']['0']['monthly_att_todate']));

        $arr_date_in_selectedmonth = range(1, $att_enddate1);
        if ($att_startdate1 != 1) {
            $arr_date_in_prevmonth = range($att_startdate1, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }

        // if ($emp_pkey != 0) {
        //     $condition = "emp_detail_timeattandance.emp_pkey = '$emp_pkey' and ";
        //     $emp = $emp_pkey;
        // } else {
            $condition = '';
            $emp = NULL;
       // }
        if ($branch) {
            $condition1 = "emp.emp_branch = '$branch' and ";
            //$branch = $branch;
        } else {
            $condition1 = "";
            $branch = "X";
        }
        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);

        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $emp_condition = "emp.attr1 = '$emp_pkeys' and ";
        } else {
            $emp_condition = "";
        }
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
        }
        // if (isset($payroUser[0]['emp_proff']['payro_priv'])) {
        //     $emp_condition = "";
        // }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        try{
            $attend = $this->EmployeeDetails->query("Call emp_detail_att_reg('$company_code','$branch','$user_login','$yearmonth','$emp_pkey', @`Perr_msg`)");
            $result = $this->EmployeeDetails->query("SELECT @Perr_msg AS Perr_msg");
        }catch(Exception $e){debug($e);}
        // debug($result); exit;
        //Edited by Akshay on 30-11-2024
        try {
            $att_startdate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 1) as monthly_att_fromdate");
            $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 2) as monthly_att_todate");
            $att_startdate = $att_startdate['0']['0']['monthly_att_fromdate'];
            $att_enddate = $att_enddate['0']['0']['monthly_att_todate'];


            $attendances = $this->EmployeeDetails->query("select eup.main_status,empdetails.first_name,empdetails.last_name,emp_detail_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey "
                . "from emp_detail_timeattandance left join emp_details as empdetails on(empdetails.emp_pkey = emp_detail_timeattandance.emp_pkey) "
                . "left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) "
                . "left join emp_detail_status_update as eup on (eup.emp_fkey = emp_detail_timeattandance.emp_pkey and eup.att_date = emp_detail_timeattandance.att_date and eup.emp_detail_status_update_pkey = (
                 SELECT MAX(emp_detail_status_update_pkey) 
                FROM emp_detail_status_update 
                WHERE emp_fkey = emp_detail_timeattandance.emp_pkey 
                AND att_date = emp_detail_timeattandance.att_date 
            ))"
                . "where $emp_condition $condition $condition1  empdetails.emp_pkey in (select emp_pkey  from(
        select emp_pkey ,sum(ifnull(duration,0)) from emp_detail_timeattandance where emp_detail_timeattandance.yearmonth='$yearmonth' group by emp_pkey  )a) and emp_detail_timeattandance.yearmonth = '$yearmonth'  
        AND emp_detail_timeattandance.att_date BETWEEN '$att_startdate' AND '$att_enddate' 
        and empdetails.status = 1
        order by att_date,empdetails.first_name,empdetails.last_name ");
            //End
            //end
        } catch (Exception $e) {
            debug($e);
        }

        $this->set('arr_dates', $arr_dates);

        $employee_attendance = array();
        foreach ($attendances as $val) {
            $emppk = $val['emp_detail_timeattandance']['emp_pkey'];
            $employee_attendance[$emppk][] = $val;
            $arr_attendance_register = $this->EditPunches->query("select count(*) as count from attendance_register where emp_fkey = $emppk AND month_year = '" . date('Y-m', strtotime($yearmonth)) . "' AND isdelete = 'N'");
            $iseditable = isset($arr_attendance_register[0][0]['count']) ? $arr_attendance_register[0][0]['count'] : 0;

            $take = $this->EditPunches->query("SELECT SUM(leave_days) as takencounts FROM `leaveentries` 
            left join emp_leave_transactions on (emp_leave_transactions.Leaveentryid = leaveentries.Leaveentryid)
            WHERE emp_leave_transactions.`LEAVESTATUS` = 'Approved' AND `EMP_fkey` = '$emppk' 
             and leave_date >= '$yearmonth' and leave_date <= '$att_enddate1' and salary_head_item_fkey !='105'");

            $leavetaken = isset($take['0']['0']['takencounts']) ? $take['0']['0']['takencounts'] : 0;
            //Calculate date count
            $sql = "select count(*) as v1 from
        emp_detail_timeattandance where present='P/P'
        and yearmonth = '$yearmonth' and emp_pkey = $emppk and att_date !='0000-00-00'
        union all
        select count(present) * 0.5 as v1 from
        emp_detail_timeattandance where present in('P/A','A/P')
        and yearmonth = '$yearmonth' and emp_pkey = $emppk and att_date !='0000-00-00'";

            $data = $this->EmployeeDetails->query($sql);

            $weeks = $this->EmployeeDetails->query("select count(*) as v1 from emp_detail_timeattandance where weekoff = 'WO' and yearmonth = '$yearmonth' and emp_pkey = $emppk");

            $holiday = $this->EmployeeDetails->query("select count(*) as v1 from emp_detail_timeattandance where holiday = 'HO' and yearmonth = '$yearmonth' and emp_pkey = $emppk");



            $sqls = "select count(*) * 0.5 as v1 from
        emp_detail_timeattandance where leaves IN ('LOP/A', 'LOP/P', 'A/LOP', 'P/LOP')
        and yearmonth = '$yearmonth' and emp_pkey = $emppk
        union all
        select count(*) as v1 from
        emp_detail_timeattandance where leaves in('LOP/LOP')
        and yearmonth = '$yearmonth' and emp_pkey = $emppk ";

            $lop = $this->EmployeeDetails->query($sqls);

            $pl = $this->EmployeeDetails->query("select count(*) as v1 from emp_detail_timeattandance where holiday is not null and yearmonth = '$yearmonth' and emp_pkey = $emppk");
            //End
            $presentDays = 0;
            if ($data) {
                $presentDays = (isset($data[0][0]['v1']) ? $data[0][0]['v1'] : 0) + (isset($data[1][0]['v1']) ? $data[1][0]['v1'] : 0);
            } else {

                $presentDays = 0;
            }


            $lopdays = 0;
            if ($lop) {
                $lopdays = (isset($lop[0][0]['v1']) ? $lop[0][0]['v1'] : 0) + (isset($lop[1][0]['v1']) ? $lop[1][0]['v1'] : 0);
            } else {

                $lopdays = 0;
            }
            $status = $val['emp_detail_timeattandance']['present'] . ' ' .
                $val['emp_detail_timeattandance']['holiday'] . ' ' .
                $val['emp_detail_timeattandance']['leaves'] . ' ' .
                $val['emp_detail_timeattandance']['weekoff'] . ' ' .
                $val['emp_detail_timeattandance']['others'];

            $month = date('m', strtotime($yearmonth . "-01")); // February
            $year = date('Y', strtotime($yearmonth . "-01"));
            $numberOfDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $count = count($employee_attendance[$emppk]);
            $employee_attendance[$emppk][$count - 1]['status'] = $status;
            $employee_attendance[$emppk][$count - 1]['presentDays'] = $presentDays;
            $employee_attendance[$emppk][$count - 1]['editable'] = !empty($iseditable) ? false : true;
            //$employee_attendance[$emppk][$count -1]['dpresentDays'] = $dpresentDays;
            $employee_attendance[$emppk][$count - 1]['lopdays'] = $lopdays;
            $employee_attendance[$emppk][$count - 1]['calendar_days'] = $numberOfDays;
            $employee_attendance[$emppk][$count - 1]['week_off'] = isset($weeks[0][0]['v1']) ? $weeks[0][0]['v1'] : 0;
            $employee_attendance[$emppk][$count - 1]['leavetaken'] = $leavetaken;
            $employee_attendance[$emppk][$count - 1]['holiday'] = isset($holiday[0][0]['v1']) ? $holiday[0][0]['v1'] : 0;

            // Edited by Akshay on 4-12-2024
            $arr_emp = $this->EmployeeDetails->query("SELECT joining_date FROM emp_proff WHERE emp_fkey = '" . $emppk . "'");
            $employee_attendance[$emppk][$count - 1]['joining_date'] = isset($arr_emp[0]['emp_proff']['joining_date']) ? $arr_emp[0]['emp_proff']['joining_date'] : '';
            // End
        }

        $this->set('employee_attendance', $employee_attendance);
        //Edited by Akshay on 21-4-2-2024
        $this->set('month', $month);

        //Edited by Akshay on 27-11-2024
        $user_group = $this->Session->read('user_group');
        $this->set('user_group', $user_group);
        date_default_timezone_set('Asia/Kolkata');
        $currentDate = date('Y-m-d');
        $this->set('currentDate', $currentDate);
        //End
    }



    public function empregisterbook($monthdd = '', $emp_pkey = '', $branch = '')
    {
        //debug($emp_pkey);
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $company_code = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = $monthdd;
        $yearmonth = $month . '-01';
        //  debug($yearmonth);
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
            $condition = "emp_detail_timeattandance.emp_pkey = '$emp_pkey' and ";
            $emp = $emp_pkey;
        } else {
            $condition = '';
            $emp = NULL;
        }
        if ($branch != 0) {
            $condition1 = "emp.emp_branch = '$branch' and ";
            $branch = $branch;
        } else {
            $condition1 = "";
            $branch = NULL;
        }
        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);

        $emp_condition = "";
        $employee_pkeys = $emp_pkey;
        if (!$shiftdetailed = $this->EditPunches->query("select * from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_details where emp_pkey = '$employee_pkeys' )")) {
            return FALSE;
            die();
        }
        if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
            if (!$this->EditPunches->query("SELECT time_duration_check_multishift('$yearmonth', '$employee_pkeys', NULL)")) {
                return false;
                die();
            }
        } else {
            if (!$this->EditPunches->query("SELECT time_duration_check('$yearmonth', '$employee_pkeys', NULL)")) {
                return false;
                die();
            }
        }
        //$attend= $this->EmployeeDetails->query("select time_duration_check('$yearmonth','$emp','$branch')");
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $attendances = $this->EmployeeDetails->query("select empdetails.first_name,empdetails.last_name,emp_detail_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey from emp_detail_timeattandance left join emp_details as empdetails on(empdetails.emp_pkey = emp_detail_timeattandance.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where $emp_condition $condition $condition1 yearmonth = '$yearmonth' order by att_date ");
        $this->set('arr_dates', $arr_dates);
        //debug($attendances);
        $employee_attendance = array();
        foreach ($attendances as $val) {
            $emppk = $val['emp_detail_timeattandance']['emp_pkey'];
            $employee_attendance[$emppk][] = $val;
        }
        $this->set('employee_attendance', $employee_attendance);
    }

    // Add new function for bank search box  Added By Nimisha 19-03-2019
    public function getbranches()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
        } else {
            $emp_pkeys = 0;
        }
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
        //        $this->set('arr_branches', $arr_branches);
        //debug($arr_branches);
        $array = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($arr_branches as $key => $value) {
            //            debug($value);
            $branch[] = array(
                'id' => $value['branch_code'],
                'text' => $value['branch_name']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
    // function End 
    public function jsons($branch = '', $resigned = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        //edited by megha on 13_09_19 added condition for branch != 0
        if ($branch == '0') {
            $branch_condition = "";
        } else
        if ($branch != null) {
            $branch_condition = " and branch_code in ('$branch') ";
        } else {
            $branch_condition = "";
        }
        //added by megha on 8_6_19 resigned employee data
        if ($resigned == '1') {
            $resign_condition =  "  emp_details.status in('1','2') ";
        } else {
            $resign_condition = "  emp_details.status = '1' ";
        }
        //end
        if ($q != null) {
            // $q_condition = " first_name like '%$q%' and ";
            $q_condition = " and (first_name like '%$q%' OR emp_proff.emp_company_id like '%$q%' ) "; //Emp Company Id Added by ***ARUL P DAS on 19/12/2019

        } else {
            $q_condition = "";
        }
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            // $emp_condition = " and emp_proff.attr1 = '$emp_pkeys'  ";
            $emp_condition = "";
        } else {
            $emp_condition = "";
        }
        //added by megha on 8_6_19 resigned employee data
        //$branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where status = 1 $branch_condition $q_condition $emp_condition ORDER BY emp_pkey DESC ");
        //$branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where  $branch_condition $q_condition $emp_condition $resign_condition ORDER BY emp_pkey DESC ");
        //$branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.emp_pkey not in (SELECT emp_fkey FROM `termination` where termination.status=1) $resign_condition $branch_condition $q_condition $emp_condition ORDER BY emp_pkey DESC ");
        $branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where  $resign_condition $branch_condition $q_condition $emp_condition ORDER BY first_name ASC ");

        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);

        //        $array = array(
        //            "items"=>
        //            array(
        //            array(
        //                "id"=>0,
        //                'text'=>'sanjun'
        //                
        //            ),
        //            array(
        //                "id"=>1,
        //                "text"=>'ananthu'
        //                
        //            ),
        //            array(
        //                "id"=>2,
        //                "text"=>'sruthi'
        //                
        //            )
        //                )
        //        );
        //echo json_encode($array) ;
    }

    public function filter()
    {
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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



        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);

        $attend = $this->EmployeeDetails->query("select time_duration_check('$yearmonth')");
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $attendances = $this->EmployeeDetails->query("select empdetails.first_name,empdetails.last_name,emp_detail_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey from emp_detail_timeattandance left join emp_details as empdetails on(empdetails.emp_pkey = emp_detail_timeattandance.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where yearmonth = '$yearmonth' ");
        $this->set('arr_dates', $arr_dates);

        $employee_attendance = array();
        foreach ($attendances as $val) {
            $emppk = $val['emp_detail_timeattandance']['emp_pkey'];
            $employee_attendance[$emppk][] = $val;
        }
        //  debug($employee_attendance);
        $this->set('employee_attendance', $employee_attendance);
    }

    public function index()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
        } else {
            $emp_pkeys = 0;
        }
        //edited by sinsiya 26-02-2024
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
        }
        //edited by ASHIN on 29-11-24
        $company_code = strtoupper($this->Session->read('company_code'));
        if (($company_code == 'DEMO' || $company_code == 'BKHS' || $company_code == 'GLET') && $user_group == 2) { // Edited by Akshay on 29-11-2024
            //$arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
               $conditions = array("status = 1 and branch_code in (select distinct(emp_branch) from emp_proff join emp_details on (emp_proff.emp_fkey=emp_details.emp_pkey) where (emp_proff.attr1 = ".$cur_emp_key." OR emp_proff.emp_fkey = ".$cur_emp_key.") and emp_details.status=1)");
                    
          $arr_branches	= $this->Units->find('all',array('fields'=>'id,branch_code,branch_name','conditions'=>array($conditions)));
        } else {
            if (isset($payroUser[0]['emp_proff']['payro_priv'])) {
                $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
            } else {
                $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
            }
        }
        //        debug($arr_branches);

        $this->set('arr_branches', $arr_branches);
        $this->set('company_code', $company_code); // edited by ASHIN ANTONY on 04-12-24
        //debug($arr_branches);
        $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        $this->set("arr_employees", $arr_employees);
        $this->set("user_group", $user_group); // Edited by Akshay on 5-12-2024
    }

    public function empindex()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
        } else {
            $emp_pkeys = 0;
        }
        $this->set("emp", $emp_pkeys);
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
        $this->set('arr_branches', $arr_branches);
        //debug($arr_branches);
        $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        $this->set("arr_employees", $arr_employees);
    }

    public function showregister()
    {
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');


        $fields = 'AttendanceRegister.*';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey'
                )
            )
        );
        $emp_fkey = $this->Session->read('emp_fkey');
        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array(
            "fields" => array("emp_pkey", "emp_name"),
            'joins' => $joins,
            "conditions" => array('status' => 1, 'EmployeeProffessional.attr1' => $emp_fkey)
        )));
        $this->set('arr_employees', $arr_employees);
        //   debug($arr_employees);
        $arr_registerentries = array(
            'P' => array(
                'label' => 'Present',
                'color' => 'green',
                'textColor' => 'white'
            ),
            'L' => array(
                'label' => 'On Leave',
                'color' => 'orange',
                'textColor' => 'white'
            ),
            'WO' => array(
                'label' => 'Week Off',
                'color' => 'yellow',
                'textColor' => 'black'
            ),
            'HO' => array(
                'label' => 'Holiday',
                'color' => 'blue',
                'textColor' => 'white'
            ),
            'A' => array(
                'label' => 'Absent',
                'color' => 'red',
                'textColor' => 'white'
            ),
            'LOP' => array(
                'label' => 'Loss Of Pay',
                'color' => 'red',
                'textColor' => 'white'
            ),
            'OTHERS' => array(
                'label' => 'Others',
                'color' => 'deepskyblue',
                'textColor' => 'white'
            )
        );
        $this->set('arr_registerentries', $arr_registerentries);
    }

    public function showregistertab($verified = 0)
    {
        $this->set('tab', $verified);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $fields = 'AttendanceRegister.*';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey'
                )
            )
        );
        $emp_fkey = $this->Session->read('emp_fkey');
        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array(
            "fields" => array("emp_pkey", "emp_name"),
            'joins' => $joins,
            "conditions" => array('status' => 1, 'EmployeeProffessional.attr1' => $emp_fkey)
        )));
        // $this->set('arr_employees', $arr_employees);
        $arr_requestdata = $this->request->data;
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : date('Y-m');


        //  $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"),'joins' => $joins, "conditions" => array('status' => 1))));
        $this->set('arr_employees', $arr_employees);

        //Fetch company's attendance end date
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));

        //By santhosh on 27 Dec 2015
        //$att_startdate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_enddate = date('t',  strtotime($month));
        //On 20 Feb 2016
        //$att_enddate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_startdate = $att_enddate + 1;
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
        $arr_date_in_selectedmonth = range(1, $att_enddate);
        if ($att_startdate != 1) {
            $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }

        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        $this->set('arr_dates', $arr_dates);

        //On 27 Dec 2015
        //$date_start = date('Y-m-d',strtotime($month.'-'.$att_startdate));
        //$date_end = date('Y-m-d',strtotime('-1 day',strtotime('+1 months',strtotime($date_start))));
        //On 26/01/2016
        $date_start = date('Y-m-d', strtotime('-1 months', strtotime($month . '-' . $att_startdate)));
        $date_end = date('Y-m-d', strtotime($month . '-' . $att_enddate));

        //$date_start = current($arr_dates);
        //$date_end = end($arr_dates);		

        $this->set('date_start', $date_start);
        $this->set('date_end', $date_end);
    }

    /*
     * List attendance register
     * By santhosh on 02 Aug 2015
     */

    public function listregisterentries()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $conditions = array('AttendanceRegister.isdelete="Y"');
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $conditions[] = 'AttendanceRegister.branch_code="' . $_REQUEST['branch'] . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $conditions[] = 'AttendanceRegister.emp_fkey=' . $_REQUEST['employee'];
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $conditions[] = 'AttendanceRegister.month_year="' . $_REQUEST['month'] . '"';
        } else {
            $conditions[] = 'AttendanceRegister.month_year="' . date('Y-m', strtotime(date('M-Y'))) . '"';
        }

        $fields = 'AttendanceRegister.*';
        $joins = array(
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'AttendanceRegister.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'AttendanceRegister.emp_fkey = EmployeeDetails.emp_pkey',
                    'EmployeeDetails.status=1'
                )
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeProffessional.attr1 = EmployeeDetails.emp_pkey'
                )
            )
        );

        $this->datatable["conditions"] = $conditions;
        $resp_register = array();
        $resp_register["rows"] = array();
        $count = $this->AttendanceRegister->find("count", array("conditions" => $conditions));
        $arr_register = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'limit' => intval($limit), 'offset' => intval($ofst)));
        foreach ($arr_register as $key => $value) {
            $resp_register["rows"][$key] = $value["AttendanceRegister"];

            $int_days_present = count(array_keys($value["AttendanceRegister"], "P"));
            $int_days_leave = count(array_keys($value["AttendanceRegister"], "L"));
            $int_days_holidays = count(array_keys($value["AttendanceRegister"], "HO"));

            $resp_register["rows"][$key]['days_present'] = $int_days_present;
            $resp_register["rows"][$key]['days_leave'] = $int_days_leave;
            $resp_register["rows"][$key]['days_holidays'] = $int_days_holidays;
        }
        $resp_register["total"] = $count;
        echo json_encode($resp_register);
    }

    public function listverifiedregisterentries()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $conditions = array('AttendanceRegister.isdelete="N"');
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $conditions[] = 'AttendanceRegister.branch_code="' . $_REQUEST['branch'] . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $conditions[] = 'AttendanceRegister.emp_fkey=' . $_REQUEST['employee'];
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $conditions[] = 'AttendanceRegister.month_year="' . $_REQUEST['month'] . '"';
        } else {
            $conditions[] = 'AttendanceRegister.month_year="' . date('Y-m', strtotime(date('M-Y'))) . '"';
        }

        $fields = 'AttendanceRegister.*';
        $joins = array(
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'AttendanceRegister.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'AttendanceRegister.emp_fkey = EmployeeDetails.emp_pkey',
                    'EmployeeDetails.status=1'
                )
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeProffessional.attr1 = EmployeeDetails.emp_pkey'
                )
            )
        );

        $this->datatable["conditions"] = $conditions;
        $resp_register = array();
        $resp_register["rows"] = array();
        $count = $this->AttendanceRegister->find("count", array("conditions" => $conditions));
        $arr_register = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'limit' => intval($limit), 'offset' => intval($ofst)));
        foreach ($arr_register as $key => $value) {
            $resp_register["rows"][$key] = $value["AttendanceRegister"];

            $int_days_present = count(array_keys($value["AttendanceRegister"], "P"));
            $int_days_leave = count(array_keys($value["AttendanceRegister"], "L"));
            $int_days_holidays = count(array_keys($value["AttendanceRegister"], "HO"));

            $resp_register["rows"][$key]['days_present'] = $int_days_present;
            $resp_register["rows"][$key]['days_leave'] = $int_days_leave;
            $resp_register["rows"][$key]['days_holidays'] = $int_days_holidays;
        }
        $resp_register["total"] = $count;
        echo json_encode($resp_register);
    }

    public function processregisterentries()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        $outputParameter = array();
        $outputParameter[] = $this->Session->read('company_code'); //company_code
        $outputParameter[] = (isset($_POST['branch']) && $_POST['branch'] != '') ? $_POST['branch'] : '';
        $outputParameter[] = $this->Session->read("login_user_id"); //user id
        $outputParameter[] = (isset($_POST['month']) && $_POST['month'] != '') ? date('Y-m-d', strtotime($_POST['month'])) : '';
        $out = $this->AttendanceRegister->insertUpdateAttendanceRegisterProc($outputParameter);
        $result['success'] = 1;
        echo json_encode($result);
    }

    public function verifyregisterentries($registerid = 0)
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);

        $arr_requestdata = $this->request->data;
        //Modified On 21 Feb 2016
        if (isset($arr_requestdata["ids"])) {
            //$ar_ids = explode(",", $_REQUEST["ids"]);$arr_requestdata
            $arr_registerids = isset($arr_requestdata["ids"]) ? explode(",", $arr_requestdata["ids"]) : array();
            $ar_ids = array();
            foreach ($arr_registerids as $register_id) {
                if (empty(json_decode($this->checkifregistercanverify($register_id, $arr_requestdata)))) {
                    $ar_ids[] = $register_id;
                }
            }
            if (!empty($ar_ids)) {
                $this->AttendanceRegister->updateAll(
                    array('isdelete' => "'N'"),
                    array('AttendanceRegister.registerid' => $ar_ids)
                );
                $result['success'] = 1;
            } else {
                $result['success'] = 0;
            }
        } else if ($registerid != 0) {
            $this->AttendanceRegister->updateAll(
                array('isdelete' => "'N'"),
                array('AttendanceRegister.registerid' => $registerid)
            );
            $result['success'] = 1;
        }
        echo json_encode($result);
    }

    public function loadattendanceregisterheader()
    {
        $this->autoRender = FALSE;
        $arr_requestdata = $this->request->data;
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : date('Y-m');

        //Fetch company's attendance start date
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));

        //On 20 Feb 2016
        //$att_startdate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_enddate = date('t',strtotime($month));
        //$att_startdate = $att_enddate + 1;
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));

        $arr_columns = array();
        $arr_columns[] = array('field' => 'emp_name', 'title' => 'Employee name', 'width' => '10%');
        for ($i = $att_startdate; $i <= $att_enddate; $i++) {
            $arr_columns[] = array('field' => 'FIELD' . $i, 'title' => $i, 'width' => '3%', 'styler:styleDay');
        }
        $arr_columns[] = array('field' => 'days_present', 'title' => 'Days present', 'width' => '10%');
        $arr_columns[] = array('field' => 'days_leave', 'title' => 'Days on leave', 'width' => '10%');
        $arr_columns[] = array('field' => 'days_holidays', 'title' => 'Holidays', 'width' => '10%');
        echo json_encode($arr_columns);
    }

    //Modified On 21 Feb 2016
    public function checkifregistercanverify($registerid = 0, $arr_requestdata = array())
    {
        $this->autoRender = FALSE;
        if (empty($arr_requestdata)) {
            $arr_requestdata = $this->request->data;
        }
        $arr_misspunched_dates = array();
        if ($registerid != 0) {
            $startdate = isset($arr_requestdata['startdate']) ? $arr_requestdata['startdate'] : '';
            $enddate = isset($arr_requestdata['enddate']) ? $arr_requestdata['enddate'] : '';

            $arr_dates_between = $this->createDateRangeArray($startdate, $enddate);

            $startTimeStamp = strtotime($startdate);
            $endTimeStamp = strtotime($enddate);

            $timeDiff = abs($endTimeStamp - $startTimeStamp);

            $numberDays = $timeDiff / 86400;  // 86400 seconds in one day
            // and you might want to convert to integer
            $numberDays = intval($numberDays);

            $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
            $arr_register = Set::extract('/AttendanceRegister/.', $this->AttendanceRegister->find("first", array("conditions" => array('registerid' => $registerid))));

            /*
             * By santhosh on 27 Dec 2015
             */
            /* for($i=1;$i<=$numberDays+1;$i++){
              if($arr_register[0]['FIELD'.$i] == 'null' || $arr_register[0]['FIELD'.$i] == ''){
              $arr_misspunched_dates[$i] = $arr_dates_between[$i-1];
              }
              } */
            foreach ($arr_dates_between as $date) {
                $day = date('j', strtotime($date));
                if ($arr_register[0]['FIELD' . $day] == 'null' || $arr_register[0]['FIELD' . $day] == '') {
                    $arr_misspunched_dates[] = $date;
                }
            }
        }
        return json_encode($arr_misspunched_dates);
    }

    public function updateregisterentries($registerid = 0)
    {
        $this->set('registerid', $registerid);
        if ($registerid != 0) {
            $arr_requestdata = $this->request->data;
            $json_dates = $arr_requestdata['dates'];
            $arr_dates = json_decode($json_dates);
            $this->set('arr_dates', $arr_dates);
        }
    }

    public function submitregisterentry()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;
        $registerid = isset($arr_requestdata['hid-registerid']) ? $arr_requestdata['hid-registerid'] : 0;
        $count = isset($arr_requestdata['hid-count-missing']) ? $arr_requestdata['hid-count-missing'] : 0;
        if ($registerid != 0) {
            if ($count != 0) {
                $arr_fields = array();
                $arr_updateentries = array();
                for ($i = 0; $i < $count; $i++) {
                    $index = isset($arr_requestdata['hid-reg-field-' . $i]) ? $arr_requestdata['hid-reg-field-' . $i] : '';
                    $arr_fields[] = $index;
                    $arr_updateentries['FIELD' . $index] = isset($arr_requestdata['reg-date-' . $i]) ? '"' . $arr_requestdata['reg-date-' . $i] . '"' : '""';
                }
                $this->AttendanceRegister->updateAll(
                    $arr_updateentries,
                    array('AttendanceRegister.registerid' => $registerid)
                );

                //Check if all fields updated
                $arr_register = Set::extract('/AttendanceRegister/.', $this->AttendanceRegister->find("first", array("conditions" => array('registerid' => $registerid))));

                $canverify = true;

                foreach ($arr_fields as $index) {
                    if ($arr_register[0]['FIELD' . $index] == 'null' || $arr_register[0]['FIELD' . $index] == '') {
                        $canverify = false;
                    }
                }

                if ($canverify) {
                    //verify register entry
                    $this->verifyregisterentries($registerid);
                } else {
                    echo json_encode(array('success' => 2));
                }
            }
        } else {
            echo json_encode(array('success' => 0));
        }
    }

    /**
     * Returns every date between two dates as an array
     * @param string $startDate the start of the date range
     * @param string $endDate the end of the date range
     * @param string $format DateTime format, default is Y-m-d
     * @return array returns every date between $startDate and $endDate, formatted as "Y-m-d"
     */
    public function createDateRange($startDate, $endDate, $format = "Y-m-d")
    {
        $begin = new DateTime($startDate);
        $end = new DateTime($endDate);

        $interval = new DateInterval('P1D'); // 1 Day
        $dateRange = new DatePeriod($begin, $interval, $end);

        $range = [];
        foreach ($dateRange as $date) {
            $range[] = $date->format($format);
        }

        return $range;
    }

    public function createDateRangeArray($strDateFrom, $strDateTo)
    {
        // takes two dates formatted as YYYY-MM-DD and creates an
        // inclusive array of the dates between the from and to dates.
        // could test validity of dates here but I'm already doing
        // that in the main script

        $aryRange = array();

        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
            while ($iDateFrom < $iDateTo) {
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }
        return $aryRange;
    }

    //Edited by Akshay on 21-4-2024
    public function editPunch($month = '', $emp_pkey = '', $edtPkey = '')
    {
        //debug($emp_pkey);
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code'));
        $user_login = $this->Session->read('login_user_id');

        $dats = $month . '-01'; //date('Y-m-01');
        $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$dats', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];

        $years = $this->EmployeeDetails->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = 0 and is_current_finyear = 'Y' and status = '1' and branch_code in (select branch_code from emp_details where emp_pkey = '$emp_pkey') ");
        $year = isset($years['0']['fin_year']['fin_year']) ? $years['0']['fin_year']['fin_year'] : 0;
        $arr_leaves_heads = $this->EmployeeDetails->query("SELECT salary_head_item_pkey,occurance FROM `salary_head_items` "
            . "WHERE head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) "
            . "and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where LEAVEPOLICY_GROUP_ID IN "
            . "(SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_pkey') and status = 1 )");
        $arr_leave = array();
        //DEBUG($arr_leaves_heads);

        $this->set('emp_detail_timeattandance_pkey', $edtPkey);

        $arr_status = $this->EmployeeDetails->query("SELECT present FROM emp_detail_timeattandance WHERE emp_detail_timeattandance_pkey = '$edtPkey'");
        // debug($arr_status);
        $status = $arr_status[0]['emp_detail_timeattandance']['present'];
        $this->set('status', $status);
        foreach ($arr_leaves_heads as $key => $val) {
            $head = $val['salary_head_items']['occurance'];
            $slary_head_item_pkey = $val['salary_head_items']['salary_head_item_pkey'];
            $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
            // debug("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
            $resp = isset($lbalance['0']['0']['LeaveBalance']) ? round($lbalance['0']['0']['LeaveBalance'], 1) : 0;
            $arr_leave[] = array(
                "salary_head_item_pkey" => $slary_head_item_pkey,
                "Head" => $head,
                "leaveBalance" => $resp
            );
        }
        $this->set('arr_leave', $arr_leave);
    }
    //Edited by Akshay on 24-4-2024
    public function bulkipdatestatus()
    {

        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');

        // Edited by Akshay on 4-12-2024
        $user_group = $this->Session->read('user_group');
        date_default_timezone_set('Asia/Kolkata');
        $today_date = date('Y-m-d');
        //End

        // need to check about the N/A, WO, HO cases how to handle if they come 
        $device_attandance_seq = $_POST["device_attandance_seq"]; // updated string as text only
        $status = $_POST["status"]; // types
        $adstatus = $_POST["adstatus"]; // new selected status
        $month_year = $_POST["monthYear"]; //Edited by Akshay on 25-4-2024


        $arr_success = array();
        foreach ($device_attandance_seq as $key => $value) {

            $arr_attendance_register = $this->EditPunches->query("select count(*) as count from attendance_register where emp_fkey = '" . $value['empPkey'] . "'"
                . " AND month_year = '$month_year' AND isdelete = 'N'");
            $iseditable = isset($arr_attendance_register[0][0]['count']) ? $arr_attendance_register[0][0]['count'] : 0;
            if ($iseditable == 0) {
                // Edited by Akshay on 4-12-2024
                $arr_emp = $this->EditPunches->query("SELECT joining_date FROM emp_proff WHERE emp_fkey = '" . $value['empPkey'] . "'");
                $joiningDate = isset($arr_emp[0]['emp_proff']['joining_date']) ? $arr_emp[0]['emp_proff']['joining_date'] : '';
                if ($joiningDate != '') {
                    $condition = " AND att_date >= '$joiningDate' ";
                } else {
                    $condition = "";
                }
                if($user_group == 2){
                    $condition .= " AND att_date >= '$today_date' ";
                }

                if ($adstatus == 'blank') {
                    $arr_data = $this->EditPunches->query("SELECT * FROM emp_detail_timeattandance WHERE  emp_pkey = '" . $value['empPkey'] . "' and " . " yearmonth = '" . date('Y-m-1', strtotime($month_year)) . "' and ((present is null or present = 'A/A') and leaves is null and weekoff is null and holiday is null and others is null)" . $condition); // Edited by Akshay on 4-12-2024
                } else {
                    $arr_data = $this->EditPunches->query("SELECT * FROM emp_detail_timeattandance WHERE  emp_pkey = '" . $value['empPkey'] . "' and " . " yearmonth = '" . date('Y-m-1', strtotime($month_year)) . "' " . $condition); // Edited by Akshay on 4-12-2024
                }
                // End
                foreach ($arr_data as $data) {
                    $data = isset($data['emp_detail_timeattandance']) ? $data['emp_detail_timeattandance'] : array();
                    if (!empty($data)) {
                        if ($status != "0") {
                            if ($adstatus == 'all') {
                                $this->checkLeaveExists($data['att_date'], $data['emp_pkey'], '');
                            }
                            // if ($adstatus != "0") {
                            //     $adStatsu = $adstatus;
                            // } else {
                            //     $adStatsu = $data['ad_present'];
                            // }

                            $adStatsu = '';

                            //                        try{
                            //
                            //                        }catch(Exception $e){
                            if ($status == "LOP") {
                                $app = $this->AddLeave($status, $data['att_date'], $data['emp_pkey'], 'full');
                                $arr_leave = $this->EditPunches->query("SELECT * FROM leaveentries WHERE LEAVESTATUS = 'Approved' and EMP_fkey = '" . $data['emp_pkey'] . "' and " . " FROMDATE = '" . $data['att_date'] . "' and leave_days > 0 ");
                                if(!empty($arr_leave))
                                $get_emp = $this->EditPunches->query("INSERT INTO emp_detail_status_update (emp_fkey, att_date, yearmonth, main_status, aditional_status, creation_date, created_by) " . "VALUES ('" . $data['emp_pkey'] . "', '" . $data['att_date'] . "', '" . $data['yearmonth'] . "', '" . trim($status) . "', '" . $adStatsu . "', '" . date('Y-m-d H:i:s') . "', '" . $this->Session->read('login_user_id') . "') ");
                                // $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET present = '', holiday = '', weekoff = '', others = '', leaves  = '" . $status . "' WHERE emp_detail_timeattandance_pkey = '" . $data[0]['emp_detail_timeattandance']['emp_detail_timeattandance_pkey'] . "' ");
                            } else {
                                $get_emp = $this->EditPunches->query("INSERT INTO emp_detail_status_update (emp_fkey, att_date, yearmonth, main_status, aditional_status, creation_date, created_by) " . "VALUES ('" . $data['emp_pkey'] . "', '" . $data['att_date'] . "', '" . $data['yearmonth'] . "', '" . trim($status) . "', '" . $adStatsu . "', '" . date('Y-m-d H:i:s') . "', '" . $this->Session->read('login_user_id') . "') ");
                                // $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET present = '" . $status . "', holiday = '', weekoff = '', others = '', leaves  = '' WHERE emp_detail_timeattandance_pkey = '" . $data[0]['emp_detail_timeattandance']['emp_detail_timeattandance_pkey'] . "' ");
                            }
                            $arr_success[] = array(
                                'emp_pkey' => $data['emp_pkey'],
                                'att_date' => $data['att_date'],
                                'status' => 'Success'
                            );
                            //}
                        }
                    }
                }
                $resp = array('success' => true, 'array_success' => $arr_success);
            } else {
                $resp = array('success' => false, 'array_success' => $arr_success);
            }
        }
   

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
}
