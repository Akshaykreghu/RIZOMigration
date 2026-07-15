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

class DailyOvertimeVerifyController extends AppController
{

    public $name = 'DailyOvertimeVerify';
    public $datatable;
    public $uses = array('EmployeeDetails', 'Units', 'DbConfig', 'EditPunches', 'EditPunchesHist', 'AttendanceRegister'); //array('CentralControl', 'SalaryStructures','UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'EmployeeConfig');
    public $components = array('MasterdataManagement');

    public function index()
    {
        //edited by sinsiya 26-02-2024 for admin split
        $company_code = strtoupper($this->Session->read('company_code'));
        $user_group = $this->Session->read('user_group');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $emp_pkey = $this->Session->read('emp_fkey');
        if ($this->Session->read('emp_fkey')) {
            if ($company_code == 'HRBL') {
                $emp_pkeys = $this->Session->read('emp_fkey');
                $arr_branches = $this->MasterdataManagement->getBranchesListForCombo(0);
            } else {
                $emp_pkeys = 0;
                $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
            }
        } else {
            $emp_pkeys = 0;
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
        }
        if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_code_to_find = $is_ho; // Specific branch code
                $arr_branches = array_filter(
                    $arr_branches,
                    function ($branch) use ($branch_code_to_find) {
                        return $branch['branch_code'] === $branch_code_to_find;
                    }
                );
                $arr_branches = array_values($arr_branches);
            }
        }
        $this->set('arr_branches', $arr_branches);
    }

    public function listbreakoffdatesforsbo($monthChoosen, $branch)
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($monthChoosen . "-" . date('d') == date('Y-m-d')) {
            $lastDateOfMonth = date('j', strtotime('-1 day', strtotime($monthChoosen . "-" . date('d'))));
        } else {
            $lastDateOfMonth = date('t',  strtotime($monthChoosen));
        }
        $month = date('m',  strtotime($monthChoosen));
        $year = date('Y',  strtotime($monthChoosen));
        for ($i = 1; $i <= $lastDateOfMonth; $i++) {
            // add the date to the dates array
            //$arr_dates[] = $year . "-" . $month . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        //added and commented by megha for attendance range change for sh infra
        $month1 =  $monthChoosen . '-01';
        $att_startdate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_startdate1 = strtotime($att_startdate['0']['0']['monthly_att_fromdate']);
        $att_enddate1 = strtotime($att_enddate['0']['0']['monthly_att_todate']);
        $att_enddate2 =  strtotime("+1 day", $att_enddate1);
        $begin = new DateTime(date('Y-m-d', $att_startdate1));
        $end = new DateTime(date('Y-m-d', $att_enddate2));
        $interval = new DateInterval('P1D'); // 0 Day
        $dateRange = new DatePeriod($begin, $interval, $end);
        $format = "Y-m-d";
        $arr_dates = [];
        foreach ($dateRange as $date) {
            $arr_dates[] = $date->format($format);
        }
        //end
        $arr_not_verified_dates = $this->EmployeeDetails->query("select distinct att_date from 
       emp_detail_timeattandance where yearmonth='" . $month1 . "' and isdelete='Y' 
       and emp_pkey in (select emp_fkey from emp_proff where emp_branch='" . $branch . "') 
       and att_date  between att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 1) 
       and att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 2)");


        $not_verified = array();
        foreach ($arr_not_verified_dates as $arr_dates_verified) {
            $not_verified[] = $arr_dates_verified['emp_detail_timeattandance']['att_date'];
        }
        // print_r($not_verified);
        $arr_sbo = array();
        foreach ($arr_dates as $key => $value) {
            // if (in_array($value, $not_verified)) {
                $data['id'] = $value; //date('j',  strtotime($value));
                $data['data'] = array($value);
                $arr_sbo["rows"][] = $data;
            // }
        }
        echo json_encode($arr_sbo);
        // debug($arr_sbo);
    }

    public function listbreakoffdatesforsbo_verified($monthChoosen, $branch)
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($monthChoosen . "-" . date('d') == date('Y-m-d')) {
            $lastDateOfMonth = date('j', strtotime('-1 day', strtotime($monthChoosen . "-" . date('d'))));
        } else {
            $lastDateOfMonth = date('t',  strtotime($monthChoosen));
        }
        $month = date('m',  strtotime($monthChoosen));
        $year = date('Y',  strtotime($monthChoosen));
        for ($i = 1; $i <= $lastDateOfMonth; $i++) {
            // add the date to the dates array
            //$arr_dates[] = $year . "-" . $month . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        //added and commented by megha for attendance range change for sh infra
        $month1 =  $monthChoosen . '-01';
        $att_startdate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_startdate1 = strtotime($att_startdate['0']['0']['monthly_att_fromdate']);
        $att_enddate1 = strtotime($att_enddate['0']['0']['monthly_att_todate']);
        $att_enddate2 =  strtotime("+1 day", $att_enddate1);
        $begin = new DateTime(date('Y-m-d', $att_startdate1));
        $end = new DateTime(date('Y-m-d', $att_enddate2));
        $interval = new DateInterval('P1D'); // 0 Day
        $dateRange = new DatePeriod($begin, $interval, $end);
        $format = "Y-m-d";
        $arr_dates = [];
        foreach ($dateRange as $date) {
            $arr_dates[] = $date->format($format);
        }
        //end
        $arr_verified_dates = $this->EmployeeDetails->query("select distinct att_date from 
       emp_detail_timeattandance where yearmonth='" . $month1 . "' and isdelete='N' 
       and emp_pkey in (select emp_fkey from emp_proff where emp_branch='" . $branch . "') 
       and att_date  between att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 1) 
       and att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 2) and att_date not in (select distinct att_date from 
       emp_ot_timeattandance where yearmonth='" . $month1 . "' and isdelete='N' 
       and emp_pkey in (select emp_fkey from emp_proff where emp_branch='" . $branch . "') 
       and att_date  between att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 1) 
       and att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 2))");

        $arr_verified_dates_ot = $this->EmployeeDetails->query("select distinct att_date from 
       emp_ot_timeattandance where yearmonth='" . $month1 . "' and isdelete='N'and emp_ot_timeattandance.ot_duration > 0
       and emp_pkey in (select emp_fkey from emp_proff where emp_branch='" . $branch . "') 
       and att_date  between att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 1) 
       and att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 2)");

        $arr_verified_dates_att = $this->EmployeeDetails->query("select distinct att_date from 
       emp_detail_timeattandance where yearmonth='" . $month1 . "' 
       and emp_pkey in (select emp_fkey from attendance_register where branch_code='" . $branch . "' and 
       month_year = DATE_FORMAT('" . $month1 . "', '%Y-%m') and isdelete='N' ) 
       and att_date  between att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 1) 
       and att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 2)");

        $arr_verified_dates = array_merge($arr_verified_dates, $arr_verified_dates_ot, $arr_verified_dates_att);
        $not_verified = array();
        foreach ($arr_verified_dates as $arr_dates_verified) {
            $not_verified[] = isset($arr_dates_verified['emp_detail_timeattandance']['att_date']) ? $arr_dates_verified['emp_detail_timeattandance']['att_date'] : $arr_dates_verified['emp_ot_timeattandance']['att_date'];
        }
        // print_r($not_verified);
        $arr_sbo = array();
        foreach ($arr_dates as $key => $value) {
            if (in_array($value, $not_verified)) {
                $data['id'] = $value; //date('j',  strtotime($value));
                $data['data'] = array($value);
                $arr_sbo["rows"][] = $data;
            }
        }
        echo json_encode($arr_sbo);
    }


    // public function test(){
    //     $this->autoRender = false;
    //     $date = ($_REQUEST['date']) ? $_REQUEST['date'] : '';
    //     return $date;
    // }

    public function listpunches()
    {
        $this->autoRender = FALSE;
        $base_table = "emp_detail_timeattandance";

        $limit = isset($_REQUEST['rows']) ? $_REQUEST['rows'] : '';
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;

        $monthdd = isset($_REQUEST['month']) ? date('Y-m-d', strtotime($_REQUEST['month'])) : '';
        $branch_code = isset($_REQUEST['branch']) ? $_REQUEST['branch'] : '';
        if (!$branch_code) {
            return;
        }
        $resp_mispunches = array();
        if ($monthdd == '') {
            echo json_encode($resp_mispunches);
            die();
        }

        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');

        //added by megha on 09-07-2024 for getting yearmonth for attendance cycle changed dbs
        $selmonth = $this->EmployeeDetails->query("SELECT yearmonth FROM `emp_detail_timeattandance` WHERE `att_date` = '$monthdd'ORDER BY emp_detail_timeattandance_pkey DESC limit 1 "); // Edited by Akshay on 24-2-2026
        $yearmonth1 = $selmonth['0']['emp_detail_timeattandance']['yearmonth'];
        //        $company_code = strtoupper($this->Session->read('company_code'));
        //        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        //        $month = $monthdd;
        $yearmonth = $monthdd;
        //        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        //        $month1 =  $month . '-01';
        //        $att_startdate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 1) as monthly_att_fromdate");
        //        $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 2) as monthly_att_todate");
        //        $att_startdate1 = $att_startdate['0']['0']['monthly_att_fromdate'];
        //        $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];
        //        $yearmonth1 = date("Y-m", strtotime($att_enddate1)). '-01';
        //        $yearmonth = isset($_REQUEST['month']) ? date('Y-m-d', strtotime($_REQUEST['month'])) : '';

        $error = 0;
        $arry_names = array();
        //$yearmonth1 = date("Y-m", strtotime($month)) . '-01';
        //        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT emp_pkey FROM `emp_details` WHERE `branch_code` = '" . $branch_code . "' and status = 1 "
        //            . "  and emp_details.emp_pkey not in (select empdetails.emp_pkey 
        // from emp_details empdetails left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey) where 
        // emp_ot_timeattandance.att_date = '$yearmonth' AND emp_ot_timeattandance.isdelete = 'N' )  "
        //            . " and emp_pkey not in(select emp_fkey from emp_proff where day_time_seq is null) "
        //            . " and emp_pkey not in (select emp_fkey from attendance_register where branch_code='" . $branch_code . "' and 
        //                 month_year = DATE_FORMAT('" . $yearmonth1 . "', '%Y-%m') and isdelete='N' ) "
        //            . " and emp_pkey not in(select emp_fkey from emp_ot_master where month = '$yearmonth1' and emp_fkey = emp_details.emp_pkey and is_verified ='Y') "
        //            . " and emp_pkey in(select empdetails.emp_pkey from $base_table 
        //                left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
        //                left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
        //                left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
        //                left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey and emp_ot_timeattandance.att_date = '$yearmonth')
        //                where $base_table.emp_pkey = emp_details.emp_pkey and $base_table.att_date = '$yearmonth' 
        //                AND $base_table.isdelete = 'Y' group by emp_detail_timeattandance_pkey order by $base_table.att_date) order by emp_details.first_name ");
        //        
        //        //LIMIT $ofst, $limit
        //        $arr_emp_pkey_count = $this->EmployeeDetails->query("select count(*) as count FROM `emp_details` WHERE `branch_code` = '" . $branch_code . "' and status = 1 "
        //            . "  and emp_details.emp_pkey not in (select empdetails.emp_pkey 
        // from emp_details empdetails left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey) where 
        // emp_ot_timeattandance.att_date = '$yearmonth' AND emp_ot_timeattandance.isdelete = 'N' )  "
        //            . " and emp_pkey not in(select emp_fkey from emp_proff where day_time_seq is null) "
        //            . " and emp_pkey not in (select emp_fkey from attendance_register where branch_code='" . $branch_code . "' and 
        //                 month_year = DATE_FORMAT('" . $yearmonth1 . "', '%Y-%m') and isdelete='N' ) "
        //            . " and emp_pkey not in(select emp_fkey from emp_ot_master where month = '$yearmonth1' and emp_fkey = emp_details.emp_pkey and is_verified ='Y') "
        //            . " and emp_pkey in(select empdetails.emp_pkey from $base_table 
        //                left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
        //                left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
        //                left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
        //                left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey and emp_ot_timeattandance.att_date = '$yearmonth')
        //                where $base_table.emp_pkey = emp_details.emp_pkey and $base_table.att_date = '$yearmonth' 
        //                AND $base_table.isdelete = 'Y' group by emp_detail_timeattandance_pkey order by $base_table.att_date)");
        // Fetch employees with shifts
        $arr_emp_pkey = $this->EmployeeDetails->query("
    SELECT empdetails.emp_pkey 
    FROM emp_details AS empdetails
    WHERE empdetails.branch_code = '$branch_code' 
    AND empdetails.status = 1 
    AND empdetails.emp_pkey IN (
        SELECT DISTINCT emp_fkey 
        FROM emp_proff 
        WHERE day_time_seq IS NOT NULL
    )
    GROUP BY empdetails.emp_pkey
    ORDER BY empdetails.first_name
");
        //   $arr_emp_pkey = $this->EmployeeDetails->query(" 
        //    SELECT empdetails.emp_pkey 
        //    FROM emp_details AS empdetails
        //    LEFT JOIN emp_detail_timeattandance AS edta 
        //        ON edta.emp_pkey = empdetails.emp_pkey
        //    WHERE empdetails.branch_code = '$branch_code' 
        //    AND empdetails.status = 1 
        //    AND edta.isdelete = 'Y'
        //    AND empdetails.emp_pkey IN (
        //        SELECT DISTINCT emp_fkey 
        //        FROM emp_proff 
        //        WHERE day_time_seq IS NOT NULL
        //    )
        //    GROUP BY empdetails.emp_pkey
        //    ORDER BY empdetails.first_name
        //"); 
        //$arr_emp_pkey = $this->EmployeeDetails->query("
        //    SELECT empdetails.emp_pkey 
        //    FROM emp_details AS empdetails
        //    WHERE empdetails.branch_code = '$branch_code' 
        //    AND empdetails.status = 1 
        //    AND empdetails.emp_pkey IN (
        //        SELECT DISTINCT emp_fkey 
        //        FROM emp_proff 
        //        WHERE day_time_seq IS NOT NULL
        //    )
        //    AND empdetails.emp_pkey IN (
        //        SELECT empdetails.emp_pkey
        //        FROM $base_table 
        //        LEFT JOIN emp_details AS empdetails ON (empdetails.emp_pkey = $base_table.emp_pkey) 
        //        LEFT JOIN emp_proff AS emp ON (emp.emp_fkey = empdetails.emp_pkey) 
        //        LEFT JOIN working_day_time_procedures AS wd ON (wd.day_time_seq = emp.day_time_seq) 
        //        LEFT JOIN emp_ot_timeattandance ON (emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey AND emp_ot_timeattandance.att_date = '$yearmonth')
        //        WHERE $base_table.emp_pkey = empdetails.emp_pkey 
        //        AND $base_table.att_date = '$yearmonth' 
        //        AND $base_table.isdelete = 'Y'
        //        GROUP BY emp_detail_timeattandance_pkey
        //    )
        //    GROUP BY empdetails.emp_pkey
        //    ORDER BY empdetails.first_name
        //");  

        //   debug(" SELECT empdetails.emp_pkey 
        //    FROM emp_details AS empdetails
        //    WHERE empdetails.branch_code = '$branch_code' 
        //    AND empdetails.status = 1 
        //    AND empdetails.emp_pkey IN (
        //        SELECT DISTINCT emp_fkey 
        //        FROM emp_proff 
        //        WHERE day_time_seq IS NOT NULL
        //    )
        //    AND empdetails.emp_pkey IN (
        //        SELECT empdetails.emp_pkey
        //        FROM $base_table 
        //        LEFT JOIN emp_details AS empdetails ON (empdetails.emp_pkey = $base_table.emp_pkey) 
        //        LEFT JOIN emp_proff AS emp ON (emp.emp_fkey = empdetails.emp_pkey) 
        //        LEFT JOIN working_day_time_procedures AS wd ON (wd.day_time_seq = emp.day_time_seq) 
        //        LEFT JOIN emp_ot_timeattandance ON (emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey AND emp_ot_timeattandance.att_date = '$yearmonth')
        //        WHERE $base_table.emp_pkey = empdetails.emp_pkey 
        //        AND $base_table.att_date = '$yearmonth' 
        //        AND $base_table.isdelete = 'Y'
        //        AND $base_table.duration != 0
        //        GROUP BY emp_detail_timeattandance_pkey
        //    )
        //    GROUP BY empdetails.emp_pkey
        //    ORDER BY empdetails.first_name
        //");
        // Count of employees
        $arr_emp_pkey_count = count($arr_emp_pkey);
        //debug($arr_emp_pkey);
        foreach ($arr_emp_pkey as $emp_data) {
            // debug($emp_data);
            $emp_pkey = $emp_data['empdetails']['emp_pkey'];
            //  debug($emp_pkey);
            $isDeletedCheck = $this->EmployeeDetails->query("
    SELECT isdelete 
    FROM emp_ot_timeattandance
    WHERE emp_pkey = '$emp_pkey' 
    AND att_date = '$yearmonth'
");
            //DEBUG($isDeletedCheck);
            $hasN = false;
            foreach ($isDeletedCheck as $row) {
                if ($row['emp_ot_timeattandance']['isdelete'] === 'N') {
                    $hasN = true;
                    break;
                }
            }

            if ($hasN) {
                continue; // Skip this employee
            }
            //$emp_name = $emp_data['emp_details']['first_name'];
            if ($emp_pkey != 0) {
                $this->EmployeeDetails->query("SELECT `time_duration_check_date`('" . $yearmonth1 . "', '" . $yearmonth . "', '" . $emp_pkey . "', '" . $branch_code . "') ");

                $condition = "$base_table.emp_pkey = '$emp_pkey' and ";
                $emp = $emp_pkey;
            } else {
                $condition = '';
                $emp = NULL;
            }

            //if (!$shiftdetailed = $this->EmployeeDetails->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey')")) {
            // $resp_mispunches["total"] = "0";
            // $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
            // $resp_mispunches["type"] = "danger";
            // echo json_encode($resp_mispunches);
            // //            return FALSE;
            // die();


            // $error = 1;
            // $arry_names[] = $emp_name;
            //  continue;
            // }

            //debug($shiftdetailed);
            //  if ($shiftdetailed && $shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
            // if (!$this->EmployeeDetails->query("SELECT time_duration_check_multishift('$yearmonth', '$emp_pkey', '$branch_code')")) {
            //     // $resp_mispunches["total"] = "0";
            //     // $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
            //     // $resp_mispunches["type"] = "danger";
            //     // echo json_encode($resp_mispunches);
            //     // //                return false;
            //     // die();
            //     $error = 1;
            //     $arry_names[] = $emp_name;
            //     continue;
            // }
            //  } else {
            // if (!$this->EmployeeDetails->query("SELECT time_duration_check('$yearmonth', '$emp_pkey', '$branch_code')")) {
            //     // $resp_mispunches["total"] = "0";
            //     // $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
            //     // $resp_mispunches["type"] = "danger";
            //     // echo json_encode($resp_mispunches);
            //     // //                return false;
            //     // die();
            //     $error = 1;
            //     $arry_names[] = $emp_name;
            //     continue;
            // }
            //                if (!$this->EmployeeDetails->query("SELECT time_duration_check('$yearmonth', '$emp_pkey', '$branch_code')")) {
            //                $resp_mispunches["total"] = "0";
            //                $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
            //                $resp_mispunches["type"] = "danger";
            //                echo json_encode($resp_mispunches);
            //                die();
            //            }
            // }
            // $attendances_count = $this->EmployeeDetails->query("select count(*) AS count from $base_table left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where $condition att_date = '$yearmonth' order by att_date ");
            //debug($condition);
            $attendances = $this->EmployeeDetails->query("select 
                empdetails.emp_pkey,emp.joining_date,
                empdetails.emp_id, emp.emp_company_id,
                empdetails.first_name, 
                empdetails.last_name, 
                $base_table.*, 
                wd.minuts_calc_perday, 
                emp.emp_fkey,
                emp_ot_timeattandance.min_bfr_on_dutty_cal_ot,
                emp_ot_timeattandance.min_aftr_off_dutty_cal_ot,
                emp_ot_timeattandance.work_time_day_off_cal_ot,
                emp_ot_timeattandance.ot_duration,
                emp_ot_timeattandance.set_duration,emp_ot_timeattandance.remarks
                from $base_table 
                left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
                left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
                left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
                left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey and emp_ot_timeattandance.att_date = '$yearmonth' and emp_ot_timeattandance.yearmonth='$yearmonth1')
                where $condition $base_table.att_date = '$yearmonth' 
                 AND $base_table.yearmonth='$yearmonth1'
                AND $base_table.isdelete = 'Y' and empdetails.emp_pkey not in (select empdetails.emp_pkey from emp_details empdetails 
            left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey)
            where   emp_ot_timeattandance.att_date = '$yearmonth'  and emp_ot_timeattandance.yearmonth='$yearmonth1'
 AND emp_ot_timeattandance.isdelete = 'N' ) group by emp_detail_timeattandance_pkey order by $base_table.att_date ");
            //debug($attendances);
            //emp_ot_timeattandance.att_date = '$yearmonth'

            //$attendances = $this->EmployeeDetails->query("select empdetails.emp_pkey,empdetails.emp_id, emp.emp_company_id,
            //            empdetails.first_name, empdetails.last_name, $base_table.*, wd.minuts_calc_perday, emp.emp_fkey from $base_table 
            //            left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
            //            left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
            //            left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
            //            where $condition $base_table.att_date = '$yearmonth' AND $base_table.yearmonth='$yearmonth1' "
            //            . " AND $base_table.isdelete = 'N' and emp.emp_fkey not in (select empdetails.emp_pkey from emp_details empdetails 
            //            left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey)
            //            where $condition $base_table.att_date = '$yearmonth' AND emp_ot_timeattandance.att_date = '$yearmonth' "
            //            . " AND $base_table.yearmonth='$yearmonth1' "
            //            . " AND emp_ot_timeattandance.isdelete = 'N') "
            //                    . " AND emp.emp_fkey  in (select emp_fkey from attendance_register where
            //                 month_year = DATE_FORMAT('$yearmonth1', '%Y-%m') and isdelete='N') order by $base_table.att_date ");

            //            $attendances2 = $this->EmployeeDetails->query("select empdetails.emp_pkey,empdetails.emp_id, emp.emp_company_id,
            //            empdetails.first_name, empdetails.last_name, $base_table.*, wd.minuts_calc_perday, emp.emp_fkey from $base_table 
            //            left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
            //            left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
            //            left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
            //            where $condition $base_table.att_date = '$yearmonth' AND $base_table.yearmonth='$yearmonth1' "
            //            . " AND emp.emp_fkey in (select emp_fkey from attendance_register where
            //                 month_year = DATE_FORMAT('$yearmonth1', '%Y-%m') and isdelete='N') "
            //            . "  order by $base_table.att_date ");
            //            debug("select empdetails.emp_pkey,empdetails.emp_id, emp.emp_company_id,
            //            empdetails.first_name, empdetails.last_name, $base_table.*, wd.minuts_calc_perday, emp.emp_fkey from $base_table 
            //            left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
            //            left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
            //            left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
            //            where $condition $base_table.att_date = '$yearmonth' AND $base_table.yearmonth='$yearmonth1' "
            //            . " AND emp.emp_fkey in (select emp_fkey from attendance_register where
            //                 month_year = DATE_FORMAT('$yearmonth1', '%Y-%m') and isdelete='N') "
            //            . "  order by $base_table.att_date ");
            //            $attendances1 = $this->EmployeeDetails->query("select empdetails.emp_pkey,empdetails.emp_id, emp.emp_company_id,
            //            empdetails.first_name,empdetails.last_name, $base_table.*,wd.minuts_calc_perday, emp.emp_fkey,
            //            emp_ot_timeattandance.min_bfr_on_dutty_cal_ot,emp_ot_timeattandance.min_aftr_off_dutty_cal_ot,
            //            emp_ot_timeattandance.work_time_day_off_cal_ot,emp_ot_timeattandance.ot_duration,emp_ot_timeattandance.set_duration,
            //            emp_ot_timeattandance.remarks from $base_table 
            //            left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
            //            left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
            //            left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
            //            left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey)
            //            where $condition $base_table.att_date = '$yearmonth' AND emp_ot_timeattandance.att_date = '$yearmonth' "
            //            . " AND $base_table.yearmonth='$yearmonth1' "
            //            . " AND emp_ot_timeattandance.isdelete = 'N'   order by $base_table.att_date ");
            //          
            //            $attendances2 = array();
            //            
            //            if(count($attendances1) == 0 && count($attendances) == 0){
            //            $attendances2 = $this->EmployeeDetails->query("select empdetails.emp_pkey,empdetails.emp_id, emp.emp_company_id,
            //            empdetails.first_name, empdetails.last_name, $base_table.*, wd.minuts_calc_perday, emp.emp_fkey from $base_table 
            //            left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
            //            left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
            //            left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
            //            where $condition $base_table.att_date = '$yearmonth' AND $base_table.yearmonth='$yearmonth1' "
            //            . " AND emp.emp_fkey not in (select emp_fkey from attendance_register where
            //                 month_year = DATE_FORMAT('$yearmonth1', '%Y-%m') and isdelete='N') "
            //            . "  order by $base_table.att_date ");
            //            }


            $arr_attendance_register = $this->EmployeeDetails->query("select count(*) as count from attendance_register where emp_fkey = $emp_pkey AND month_year = '" . date('Y-m', strtotime($yearmonth1)) . "' AND isdelete = 'Y'");
            $iseditable = isset($arr_attendance_register[0][0]['count']) ? $arr_attendance_register[0][0]['count'] : 0;
            //debug($attendances);
            $employee_attendance = array();
            foreach ($attendances as $val) {
                $arr_output['emp_pkey'] = isset($val['empdetails']['emp_pkey']) ? $val['empdetails']['emp_pkey'] : '';
                $arr_output['emp_id'] = isset($val['empdetails']['emp_id']) ? $val['empdetails']['emp_id'] : '';
                $arr_output['first_name'] = isset($val['empdetails']['first_name']) ? $val['empdetails']['first_name'] . ' ' . $val['empdetails']['last_name'] . ' - ' . $val['emp']['emp_company_id'] : '';
                $arr_output['leaves'] = isset($val['emp_detail_timeattandance']['leaves']) ? $val['emp_detail_timeattandance']['leaves'] : '';
                $arr_output['emp_detail_timeattandance_pkey'] = isset($val['emp_detail_timeattandance']['emp_detail_timeattandance_pkey']) ? $val['emp_detail_timeattandance']['emp_detail_timeattandance_pkey'] : '';
                $arr_output['att_date'] = isset($val[$base_table]['att_date']) ? $val[$base_table]['att_date'] : '';
                $arr_output['att_in_time'] = isset($val[$base_table]['att_in_time']) ? $val[$base_table]['att_in_time']/* date("h:i:s A", strtotime($val[$base_table]['att_in_time'])) */ : '';
                $arr_output['att_out_time'] = isset($val[$base_table]['att_out_time']) ? $val[$base_table]['att_out_time']/* date("h:i:s A", strtotime($val[$base_table]['att_out_time'])) */ : '';
                $arr_output['duration'] = isset($val[$base_table]['duration']) ? $val[$base_table]['duration'] : '';
                $arr_output['site_transactions_fkey'] = isset($val[$base_table]['site_transactions_fkey']) ? $val[$base_table]['site_transactions_fkey'] : '';

                $arr_output['min_bfr_on_dutty_cal_ot'] = isset($val['emp_ot_timeattandance']['min_bfr_on_dutty_cal_ot']) ? $val['emp_ot_timeattandance']['min_bfr_on_dutty_cal_ot'] : '';
                $arr_output['min_aftr_off_dutty_cal_ot'] = isset($val['emp_ot_timeattandance']['min_aftr_off_dutty_cal_ot']) ? $val['emp_ot_timeattandance']['min_aftr_off_dutty_cal_ot'] : '';
                $arr_output['work_time_day_off_cal_ot'] = isset($val['emp_ot_timeattandance']['work_time_day_off_cal_ot']) ? $val['emp_ot_timeattandance']['work_time_day_off_cal_ot'] : '';
                $arr_output['ot_duration'] = isset($val['emp_ot_timeattandance']['ot_duration']) ? $val['emp_ot_timeattandance']['ot_duration'] : '';

                $arr_output['set_duration'] = isset($val['emp_ot_timeattandance']['set_duration']) ? $val['emp_ot_timeattandance']['set_duration'] : '';
                $arr_output['remarks'] = isset($val['emp_ot_timeattandance']['remarks']) ? $val['emp_ot_timeattandance']['remarks'] : '';
                $arr_output['joining_date'] = isset($val['emp']['joining_date']) ? $val['emp']['joining_date'] : '';
                //$arr_output['min_bfr_on_dutyughjyulkjyuty_cal_ot'] = isset($val[$base_table]['min_bfr_on_dutty_cal_ot'])?$val[$base_table]['min_bfr_on_dutty_cal_ot']:'';
                //$arr_output['min_aftr_off_dutty_cal_ot'] = isset($val[$base_table]['min_aftr_off_dutty_cal_ot'])?$val[$base_table]['min_aftr_off_dutty_cal_ot']:'';
                //$arr_output['ot_duration'] = isset($val[$base_table]['ot_duration'])?$val[$base_table]['ot_duration']:'';

                $status = '';
                $status_color = 'black';
                $from = strtotime($arr_output['att_date']);
                $today = time();
                $difference = $today - $from;
                $diff = floor($difference / 86400);
                $arr_output['day'] = isset($diff) ? $diff : '0';

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
                } else {
                    $status_color = "black";
                }
                $status = $val[$base_table]['present'] . ' ' .
                    $val[$base_table]['holiday'] . ' ' .
                    //      $val[$base_table]['leaves'] . ' ' .
                    $val[$base_table]['weekoff'] . ' ' .
                    $val[$base_table]['others'];



                $arr_output['status'] = $status;
                // debug($arr_output);
                $arr_output['status_color'] = $status_color;

                $arr_output['editable'] = !empty($iseditable) ? true : false;
                $company_code = strtoupper($this->Session->read('company_code'));
                $arr_output['company_code'] = $company_code;
                $resp_mispunches["rows"][] = $arr_output;
                // $resp_mispunches["rows"][] = $arr_outputs;
            }
        }

        $count = isset($resp_mispunches["rows"]) ? count($resp_mispunches["rows"]) : 0;

        if ($error) {
            $employees = '';
            foreach ($arry_names as $name) {
                $employees .= $name . ",";
            }
            //$resp_mispunches["total"] = $count;
            //$resp_mispunches["message"] = "The following Employee Does not have any Shift Policy , Please assign one <br>".$employees;
            //$resp_mispunches["type"] = "danger";
            echo json_encode($resp_mispunches);
            //                return false;
            //die();
        } else {
            //  echo json_encode($resp_mispunches);
            //$resp_mispunches["total"] = "$count";
            //$resp_mispunches["message"] = "Employee Attendance Retrieved Successfully ";
            //$resp_mispunches["type"] = "success";
        }
        $total = isset($arr_emp_pkey_count['0']['0']['count']) ? $arr_emp_pkey_count['0']['0']['count'] : 0;
        if ($count > 0)
            $resp_mispunches["total"] = $count;
        echo json_encode($resp_mispunches);
    }

    public function listpunchesverify()
    {
        $this->autoRender = FALSE;
        $arr_leave_status = array();
        $arr_type = array();
        $arr_session = array();
        $state = array();
        $leave_details = array();
        $arr_datess = array();
        $arr_outputs = array();
        $base_table = "emp_detail_timeattandance";

        $limit = isset($_REQUEST['rows']) ? $_REQUEST['rows'] : '';
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;

        $monthdd = isset($_REQUEST['month']) ? date('Y-m-d', strtotime($_REQUEST['month'])) : '';
        $branch_code = isset($_REQUEST['branch']) ? $_REQUEST['branch'] : '';
        if (!$branch_code) {
            return;
        }
        $resp_mispunches = array();
        if ($monthdd == '') {
            echo json_encode($resp_mispunches);
            die();
        }

        //$resp_mispunches["rows"] = array();
        //$resp_mispunches["data"] = array();
        //$resp_mispunch["out"] = array();

        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        // $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = $monthdd;
        $yearmonth = $monthdd;
        //  debug($monthdd);
        //added by megha on 09-07-2024 for getting yearmonth for attendance cycle changed dbs
        $selmonth = $this->EmployeeDetails->query("SELECT yearmonth FROM `emp_detail_timeattandance` WHERE `att_date` = '$monthdd' ORDER BY emp_detail_timeattandance_pkey DESC limit 1 "); // Edited by Akshay on 24-2-2026

        // debug($selmonth);
        $yearmonth1 = $selmonth[0]['emp_detail_timeattandance']['yearmonth'];
        // if (!empty($selmonth) && isset($selmonth[0]['emp_detail_timeattandance']['yearmonth'])) {
        // $yearmonth1 = $selmonth[0]['emp_detail_timeattandance']['yearmonth'];
        //} else {
        // Handle the case where no results were found
        //$yearmonth1 = null; // or assign a default value
        // Optionally, you can log an error or set a flash message to inform the user
        //}

        $error = 0;
        $arry_names = array();
        //$yearmonth1 = date("Y-m",strtotime($month)).'-01';
        //$arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `branch_code` = '".$branch_code."'");
        //edited by sinsiya on 13-05-2025

        $countResult = $this->EmployeeDetails->query("
            SELECT COUNT(*) as total
            FROM emp_ot_timeattandance
            WHERE att_in_time IS NULL
            AND isdelete = 'N'
            AND att_date = '$yearmonth'
        ");

        // Step 2: Check the count and update only if there are at least 2
        if (!empty($countResult[0][0]['total']) && $countResult[0][0]['total'] >= 2) {
            $update_attdate = $this->EmployeeDetails->query("
        UPDATE emp_ot_timeattandance
        SET isdelete = 'Y'
        WHERE att_in_time IS NULL
          AND isdelete = 'N'
          AND att_date = '$yearmonth'
        LIMIT 1
            ");
        }


        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `branch_code` = '" . $branch_code . "' and status = 1 "
            . " and emp_pkey not in(select emp_fkey from emp_proff where day_time_seq is null) "
            . " and (emp_pkey in(select emp_pkey from emp_ot_timeattandance where att_date = '$yearmonth' and emp_pkey = emp_details.emp_pkey and isdelete ='N' and emp_ot_timeattandance.ot_duration > 0) "
            . " OR emp_pkey in(select emp_fkey from attendance_register where branch_code='" . $branch_code . "' and 
                 month_year = DATE_FORMAT('$yearmonth1', '%Y-%m') and isdelete='N') OR emp_pkey in(select empdetails.emp_pkey from $base_table 
                left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
                left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
                left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
                left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey and emp_ot_timeattandance.att_date = '$yearmonth')
                where $base_table.emp_pkey = emp_details.emp_pkey and $base_table.att_date = '$yearmonth' 
                AND ($base_table.isdelete = 'N' or emp_ot_timeattandance.isdelete = 'N') group by emp_detail_timeattandance_pkey order by $base_table.att_date)) order by emp_details.first_name ");
        //LIMIT $ofst, $limit
        $arr_emp_pkey_count = $this->EmployeeDetails->query("select count(*) as count FROM `emp_details` WHERE `branch_code` = '" . $branch_code . "' and status = 1 "
            . " and emp_pkey not in(select emp_fkey from emp_proff where day_time_seq is null) "
            . " and (emp_pkey in(select emp_pkey from emp_ot_timeattandance where att_date = '$yearmonth' and emp_pkey = emp_details.emp_pkey and isdelete ='N' and emp_ot_timeattandance.ot_duration > 0) "
            . " OR emp_pkey in(select emp_fkey from attendance_register where branch_code='" . $branch_code . "' and 
                 month_year = DATE_FORMAT('$yearmonth1', '%Y-%m') and isdelete='N') OR emp_pkey in(select empdetails.emp_pkey from $base_table 
                left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
                left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
                left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
                left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey and emp_ot_timeattandance.att_date = '$yearmonth')
                where $base_table.emp_pkey = emp_details.emp_pkey and $base_table.att_date = '$yearmonth' 
                AND ($base_table.isdelete = 'N' or emp_ot_timeattandance.isdelete = 'N') group by emp_detail_timeattandance_pkey order by $base_table.att_date))");

        // $count = isset($arr_emp_pkey) ? count($arr_emp_pkey) : 0;
        foreach ($arr_emp_pkey as $emp_data) {
            $emp_pkey = $emp_data['emp_details']['emp_pkey'];
            $emp_name = $emp_data['emp_details']['first_name'];

            if ($emp_pkey != 0) {
                $condition = "$base_table.emp_pkey = '$emp_pkey' and ";
                $emp = $emp_pkey;
            } else {
                $condition = '';
                $emp = NULL;
            }

            //EDITED BY SINSIYA ON 06-03-2025

            // $attendances_count = $this->EmployeeDetails->query("select count(*) AS count from $base_table left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where $condition att_date = '$yearmonth' order by att_date ");
            //commented by megha on 05/08/2024
            $attendances = $this->EmployeeDetails->query("select distinct empdetails.emp_pkey,empdetails.emp_id, emp.emp_company_id,
            empdetails.first_name, empdetails.last_name, $base_table.*, wd.minuts_calc_perday, emp.emp_fkey from $base_table 
            left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
            left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
            left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
            where $condition $base_table.att_date = '$yearmonth' AND $base_table.yearmonth='$yearmonth1' "
                . " AND $base_table.isdelete = 'N' and emp.emp_fkey not in (select empdetails.emp_pkey from emp_details empdetails 
            left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey)
            where $condition $base_table.att_date = '$yearmonth' AND emp_ot_timeattandance.att_date = '$yearmonth' "
                . " AND $base_table.yearmonth='$yearmonth1'"
                . " AND emp_ot_timeattandance.isdelete = 'N') "
                . " AND emp.emp_fkey not in (select emp_fkey from attendance_register where
                 month_year = DATE_FORMAT('$yearmonth1', '%Y-%m') and isdelete='Y') order by $base_table.att_date ");

            //            $attendances2 = $this->EmployeeDetails->query("select empdetails.emp_pkey,empdetails.emp_id, emp.emp_company_id,
            //            empdetails.first_name, empdetails.last_name, $base_table.*, wd.minuts_calc_perday, emp.emp_fkey from $base_table 
            //            left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
            //            left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
            //            left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
            //            where $condition $base_table.att_date = '$yearmonth' AND $base_table.yearmonth='$yearmonth1' "
            //                . " AND emp.emp_fkey in (select emp_fkey from attendance_register where
            //                 month_year = DATE_FORMAT('$yearmonth1', '%Y-%m') and isdelete='N') "
            //                . "  order by $base_table.att_date ");

            $attendances1 = $this->EmployeeDetails->query("select distinct empdetails.emp_pkey,empdetails.emp_id, emp.emp_company_id,
            empdetails.first_name,empdetails.last_name, $base_table.*,wd.minuts_calc_perday, emp.emp_fkey,
            emp_ot_timeattandance.min_bfr_on_dutty_cal_ot,emp_ot_timeattandance.min_aftr_off_dutty_cal_ot,
            emp_ot_timeattandance.work_time_day_off_cal_ot,emp_ot_timeattandance.ot_duration,emp_ot_timeattandance.set_duration,
            emp_ot_timeattandance.remarks from $base_table 
            left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
            left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
            left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
            left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey)
            where $condition $base_table.att_date = '$yearmonth' AND emp_ot_timeattandance.att_date = '$yearmonth' "
                . " AND $base_table.yearmonth='$yearmonth1' "
                . " AND emp_ot_timeattandance.isdelete = 'N' order by $base_table.att_date ");

            $attendances2 = array();

            if (count($attendances1) == 0 && count($attendances) == 0) {
                //                $attendances2 = $this->EmployeeDetails->query("select empdetails.emp_pkey,empdetails.emp_id, emp.emp_company_id,
                //            empdetails.first_name, empdetails.last_name, $base_table.*, wd.minuts_calc_perday, emp.emp_fkey from $base_table 
                //            left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
                //            left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
                //            left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
                //            where $condition $base_table.att_date = '$yearmonth' AND $base_table.yearmonth='$yearmonth1' "
                //                    . " AND emp.emp_fkey in (select emp_fkey from attendance_register where
                //                 month_year = DATE_FORMAT('$yearmonth1', '%Y-%m') and isdelete='N') "
                //                    . "  order by $base_table.att_date ");
                $attendances2 = $this->EmployeeDetails->query("
                                                                SELECT 
                                                                    empdetails.emp_pkey,
                                                                    empdetails.emp_id,
                                                                    emp.emp_company_id,
                                                                    empdetails.first_name,
                                                                    empdetails.last_name,
                                                                    $base_table.*,
                                                                    wd.minuts_calc_perday,
                                                                    emp.emp_fkey
                                                                FROM $base_table 
                                                                LEFT JOIN emp_details AS empdetails ON empdetails.emp_pkey = $base_table.emp_pkey 
                                                                LEFT JOIN emp_proff AS emp ON emp.emp_fkey = empdetails.emp_pkey 
                                                                LEFT JOIN working_day_time_procedures AS wd ON wd.day_time_seq = emp.day_time_seq 
                                                                LEFT JOIN emp_ot_timeattandance ON emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey 
                                                                    AND emp_ot_timeattandance.att_date = '$yearmonth'
                                                                    AND emp_ot_timeattandance.yearmonth = '$yearmonth1'
                                                                WHERE $condition $base_table.att_date = '$yearmonth' 
                                                                    AND $base_table.yearmonth = '$yearmonth1' 
                                                                    AND emp.emp_fkey IN (
                                                                        SELECT emp_fkey FROM attendance_register 
                                                                        WHERE month_year = DATE_FORMAT('$yearmonth1', '%Y-%m') 
                                                                            AND isdelete = 'N'
                                                                    )
                                                                    AND emp_ot_timeattandance.isdelete = 'N'  
                                                                ORDER BY $base_table.att_date
                                                            ");

                $attendances3 = $attendances2;
            } else {

                $attendances3 = array_merge($attendances, $attendances1);
            }


            //            $attendances = $this->EmployeeDetails->query("select 
            //                empdetails.emp_pkey,emp.joining_date,
            //                empdetails.emp_id, emp.emp_company_id,
            //                empdetails.first_name, 
            //                empdetails.last_name, 
            //                $base_table.*,  
            //                wd.minuts_calc_perday, 
            //                emp.emp_fkey,
            //                emp_ot_timeattandance.min_bfr_on_dutty_cal_ot,
            //                emp_ot_timeattandance.min_aftr_off_dutty_cal_ot,
            //                emp_ot_timeattandance.work_time_day_off_cal_ot,
            //                emp_ot_timeattandance.ot_duration,
            //                emp_ot_timeattandance.set_duration,emp_ot_timeattandance.remarks
            //                from $base_table 
            //                left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
            //                left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
            //                left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
            //                left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey and emp_ot_timeattandance.att_date = '$yearmonth')
            //                where $condition $base_table.att_date = '$yearmonth' 
            //                 AND (emp_ot_timeattandance.att_date = '$yearmonth' and $base_table.yearmonth='".date('Y-m-01', strtotime($yearmonth1))."' ) "
            //                . "AND $base_table.isdelete = 'N' and empdetails.emp_pkey not in (select empdetails.emp_pkey from emp_details empdetails 
            //            left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey)
            //            where   emp_ot_timeattandance.att_date = '$yearmonth'  and emp_ot_timeattandance.yearmonth='$yearmonth1'
            // AND emp_ot_timeattandance.isdelete = 'N' ) group by emp_detail_timeattandance_pkey order by $base_table.att_date ");

            $arr_attendance_register = $this->EmployeeDetails->query("select count(*) as count from attendance_register where emp_fkey = $emp_pkey AND month_year = '" . date('Y-m', strtotime($month)) . "' AND isdelete = 'N'");
            $iseditable = isset($arr_attendance_register[0][0]['count']) ? $arr_attendance_register[0][0]['count'] : 0;

            $employee_attendance = array();
            // continue;
            // debug($attendances3);
            foreach ($attendances3 as $val) {
                $arr_output['emp_pkey'] = isset($val['empdetails']['emp_pkey']) ? $val['empdetails']['emp_pkey'] : '';
                $arr_output['emp_id'] = isset($val['empdetails']['emp_id']) ? $val['empdetails']['emp_id'] : '';
                $arr_output['first_name'] = isset($val['empdetails']['first_name']) ? $val['empdetails']['first_name'] . ' ' . $val['empdetails']['last_name'] . ' - ' . $val['emp']['emp_company_id'] : '';
                $arr_output['leaves'] = isset($val['emp_detail_timeattandance']['leaves']) ? $val['emp_detail_timeattandance']['leaves'] : '';
                $arr_output['att_date'] = isset($val[$base_table]['att_date']) ? $val[$base_table]['att_date'] : '';
                $arr_output['att_in_time'] = isset($val[$base_table]['att_in_time']) ? date('d-m-Y H:i:s', strtotime($val[$base_table]['att_in_time']))/* date("h:i:s A", strtotime($val[$base_table]['att_in_time'])) */ : '';
                $arr_output['att_out_time'] = isset($val[$base_table]['att_out_time']) ? date('d-m-Y H:i:s', strtotime($val[$base_table]['att_out_time']))/* date("h:i:s A", strtotime($val[$base_table]['att_out_time'])) */ : '';
                $arr_output['duration'] = isset($val[$base_table]['duration']) ? $val[$base_table]['duration'] : '';
                $arr_output['site_transactions_fkey'] = isset($val[$base_table]['site_transactions_fkey']) ? $val[$base_table]['site_transactions_fkey'] : '';
                $arr_output['emp_detail_timeattandance_pkey'] = isset($val['emp_detail_timeattandance']['emp_detail_timeattandance_pkey']) ? $val['emp_detail_timeattandance']['emp_detail_timeattandance_pkey'] : '';
                $arr_output['min_bfr_on_dutty_cal_ot'] = isset($val['emp_ot_timeattandance']['min_bfr_on_dutty_cal_ot']) ? $val['emp_ot_timeattandance']['min_bfr_on_dutty_cal_ot'] : '';
                $arr_output['min_aftr_off_dutty_cal_ot'] = isset($val['emp_ot_timeattandance']['min_aftr_off_dutty_cal_ot']) ? $val['emp_ot_timeattandance']['min_aftr_off_dutty_cal_ot'] : '';
                $arr_output['work_time_day_off_cal_ot'] = isset($val['emp_ot_timeattandance']['work_time_day_off_cal_ot']) ? $val['emp_ot_timeattandance']['work_time_day_off_cal_ot'] : '';
                $arr_output['ot_duration'] = isset($val['emp_ot_timeattandance']['ot_duration']) ? $val['emp_ot_timeattandance']['ot_duration'] : '';
                $arr_output['set_duration'] = isset($val['emp_ot_timeattandance']['set_duration']) ? $val['emp_ot_timeattandance']['set_duration'] : '';
                $arr_output['remarks'] = isset($val['emp_ot_timeattandance']['remarks']) ? $val['emp_ot_timeattandance']['remarks'] : '';
                //debug($arr_output['remarks']);
                //$arr_output['min_bfr_on_dutyughjyulkjyuty_cal_ot'] = isset($val[$base_table]['min_bfr_on_dutty_cal_ot'])?$val[$base_table]['min_bfr_on_dutty_cal_ot']:'';
                //$arr_output['min_aftr_off_dutty_cal_ot'] = isset($val[$base_table]['min_aftr_off_dutty_cal_ot'])?$val[$base_table]['min_aftr_off_dutty_cal_ot']:'';
                //$arr_output['ot_duration'] = isset($val[$base_table]['ot_duration'])?$val[$base_table]['ot_duration']:'';

                $status = '';
                $status_color = 'black';


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
                } else {
                    $status_color = "black";
                }
                $status = $val[$base_table]['present'] . ' ' .
                    $val[$base_table]['holiday'] . ' ' .
                    //      $val[$base_table]['leaves'] . ' ' .
                    $val[$base_table]['weekoff'] . ' ' .
                    $val[$base_table]['others'];



                $arr_output['status'] = $status;
                // debug($arr_output);
                $arr_output['status_color'] = $status_color;

                $arr_output['editable'] = !empty($iseditable) ? true : false;

                $resp_mispunches["rows"][] = $arr_output;
                // $resp_mispunches["rows"][] = $arr_outputs;
            }
        }

        $count = isset($resp_mispunches["rows"]) ? count($resp_mispunches["rows"]) : 0;

        if ($error) {
            //            $employees = '';
            //            foreach ($arry_names as $name) {
            //                $employees .= $name . ",";
            //            }
            //$resp_mispunches["total"] = $count;
            //$resp_mispunches["message"] = "The following Employee Does not have any Shift Policy , Please assign one <br>".$employees;
            //$resp_mispunches["type"] = "danger";
            echo json_encode($resp_mispunches);
            //                return false;
            //die();
        } else {
            //$resp_mispunches["total"] = $count;
            //$resp_mispunches["message"] = "Employee Attendance Retrieved Successfully ";
            //$resp_mispunches["type"] = "success";
        }

        $total = isset($arr_emp_pkey_count['0']['0']['count']) ? $arr_emp_pkey_count['0']['0']['count'] : 0;
        //debug($total); 
        if ($total > 3)
            $resp_mispunches["total"] = $total;
        echo json_encode($resp_mispunches);
        // debug($resp_mispunches); 
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




    public function editpunch($att_date = '', $emp_pkey = '', $site_t_fkey = '',  $att_in_time = '', $att_out_time = '')
    {

        $this->layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        // added by nimisha rajesh on 30/05/2019 issue - new button showing in edit puches when emp = heirarchy

        $emp_fkey = $this->Session->read('emp_fkey');
        $empid = $this->EmployeeDetails->query("SELECT `emp_id` FROM `emp_details` WHERE `emp_pkey` = '$emp_pkey'");
        $emp_id = isset($empid['0']['emp_details']['emp_id']) ? $empid['0']['emp_details']['emp_id'] : '';
        //        debug($id);
        if ($emp_pkey == $emp_fkey) {
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
        $this->set('emp_pkey', $emp_pkey);
        $this->set('emp_id', $emp_id);
        $this->set('att_in_time', $att_in_time);
        $this->set('site_detailss', $site_detailss);
        $this->set('att_out_time', $att_out_time);
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
        $this->set("emp_pkey", $emp_pkey);
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


    public function savenew()
    {
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EditPunchesHist->UseDbCnfig = $this->Session->read('ds');

        $resp = array();
        $data = array();
        $data["C1"] = $_POST["C1"];
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

    public function verify()
    {
        $this->autoRender = FALSE;
        $resp = array('success' => false);
        if ($_REQUEST) {
            $att_date = trim($_REQUEST['att_date']);
            $emp_pkey = trim($_REQUEST['emp_pkey']);
            $resp = array('success' => false);
            if ($att_date && $emp_pkey) {
                $this->EditPunches->useDbConfig = $this->Session->read('ds');
                $this->EditPunches->query("UPDATE emp_detail_timeattandance SET isdelete = 'N' WHERE emp_pkey = $emp_pkey AND att_date='$att_date'");
                $this->EditPunches->query("UPDATE emp_ot_timeattandance SET isdelete = 'N' WHERE emp_pkey = $emp_pkey AND att_date='$att_date'");
                $resp = array('success' => true);
            }
        }
        echo json_encode($resp);
    }

    public function verifyregisterentries()
    {
        $this->autoRender = FALSE;
        $arr_requestdata = $this->request->data;
        $resp = array('success' => false);
        if (isset($arr_requestdata["ids"])) {
            //$ar_ids = explode(",", $_REQUEST["ids"]);$arr_requestdata
            $arr_registerids = isset($arr_requestdata["ids"]) ? explode(",", $arr_requestdata["ids"]) : array();
            $ar_ids = array();
            $resp = array('success' => false);
            foreach ($arr_registerids as $detailtime_id) {
                $this->EditPunches->useDbConfig = $this->Session->read('ds');
                $this->EditPunches->query("UPDATE emp_detail_timeattandance SET isdelete = 'N' WHERE emp_detail_timeattandance_pkey = $detailtime_id ");
                $values = $this->EditPunches->query("select att_date,emp_pkey from emp_detail_timeattandance WHERE emp_detail_timeattandance_pkey = $detailtime_id ");
                $emp_pkey = $values['0']['emp_detail_timeattandance']['emp_pkey'];
                $att_date = $values['0']['emp_detail_timeattandance']['att_date'];

                $this->EditPunches->query("UPDATE emp_ot_timeattandance SET isdelete = 'N' WHERE emp_pkey = $emp_pkey AND att_date='$att_date'");
                $resp = array('success' => true);
            }
        }
        echo json_encode($resp);
    }

    public function removeentries()
    {
        $this->autoRender = FALSE;
        $arr_requestdata = $this->request->data;
        $success = 1;
        if (isset($arr_requestdata["ids"])) {
            $arr_registerids = isset($arr_requestdata["ids"]) ? explode(",", $arr_requestdata["ids"]) : array();
            $ar_ids = array();
            //$resp = array('success' => false);
            foreach ($arr_registerids as $detailtime_id) {
                $this->EditPunches->useDbConfig = $this->Session->read('ds');
                $values = $this->EditPunches->query("select att_date,emp_pkey, yearmonth from emp_detail_timeattandance WHERE emp_detail_timeattandance_pkey = $detailtime_id "); // Edited by Akshay on 24-2-2026
                $emp_pkey = isset($values['0']['emp_detail_timeattandance']['emp_pkey']) ? $values['0']['emp_detail_timeattandance']['emp_pkey'] : '';
                $att_date = isset($values['0']['emp_detail_timeattandance']['att_date']) ? $values['0']['emp_detail_timeattandance']['att_date'] : '';
                $yearmonth = isset($values['0']['emp_detail_timeattandance']['yearmonth']) ? $values['0']['emp_detail_timeattandance']['yearmonth'] : ''; // Edited by Akshay on 24-2-2026
                $month_year = date("Y-m", strtotime($yearmonth)); // Edited by Akshay on 24-2-2026
                $arr_emps = $this->EditPunches->query("SELECT isdelete FROM `attendance_register` WHERE `month_year` = '$month_year' AND `emp_fkey` = $emp_pkey");
                $status = isset($arr_emps['0']['attendance_register']['isdelete']) ? $arr_emps['0']['attendance_register']['isdelete'] : 'Y';
                if ($status == 'Y') {
                    $this->EditPunches->query("UPDATE emp_ot_timeattandance SET isdelete = 'Y' WHERE emp_pkey = $emp_pkey AND att_date='$att_date'");
                    $this->EditPunches->query("UPDATE emp_detail_timeattandance SET isdelete = 'Y' WHERE emp_detail_timeattandance_pkey = $detailtime_id ");
                } else {
                    $success = 0;
                }
            }
        }
        $resp = array('success' => $success);
        echo json_encode($resp);
    }

    public function updateSetDuration()
    {
        $this->autoRender = FALSE;
        $resp = array('success' => false);
        if ($_REQUEST) {
            $att_date = trim($_REQUEST['att_date']);
            $emp_pkey = trim($_REQUEST['emp_pkey']);
            $value = trim($_REQUEST['value']);
            $resp = array('success' => false);
            if ($att_date && $emp_pkey && $value >= 0) {
                $this->EditPunches->useDbConfig = $this->Session->read('ds');
                $this->EditPunches->query("UPDATE emp_ot_timeattandance SET set_duration = '$value' WHERE emp_pkey = $emp_pkey AND att_date='$att_date'");
                // echo "UPDATE emp_ot_timeattandance SET set_duration = ".$value." WHERE emp_pkey = $emp_pkey AND att_date='$att_date'";
                // exit;
                $resp = array('success' => true);
            }
        }
        echo json_encode($resp);
    }

    public function setRemarks()
    {
        $this->autoRender = FALSE;
        $resp = array('success' => false);
        if ($_REQUEST) {
            $att_date = trim($_REQUEST['att_date']);
            $emp_pkey = trim($_REQUEST['emp_pkey']);
            $value = trim($_REQUEST['value']);
            $resp = array('success' => false);
            if ($att_date && $emp_pkey && $value) {
                $this->EditPunches->useDbConfig = $this->Session->read('ds');
                $this->EditPunches->query("UPDATE emp_ot_timeattandance SET remarks = '" . $value . "' WHERE emp_pkey = $emp_pkey AND att_date='$att_date'");
                // echo "UPDATE emp_ot_timeattandance SET remarks = '".$value."' WHERE emp_pkey = $emp_pkey AND att_date='$att_date'";
                // exit;
                $resp = array('success' => true);
            }
        }
        echo json_encode($resp);
    }
}
