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

class DailyOvertimeVerifyNewController extends AppController
{

    public $name = 'DailyOvertimeVerifyNew';
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

        $this->set('user_group', $user_group);
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

        // Edited by Akshay on 26-3-2026
        // Limit to current date
        $current_date = strtotime(date('Y-m-d'));
        $att_enddate1 = min($att_enddate1, $current_date);
        // End

        $att_enddate2 =  strtotime("+1 day", $att_enddate1);
        $begin = new DateTime(date('Y-m-d', $att_startdate1));
        $end = new DateTime(date('Y-m-d', $att_enddate2));
        $interval = new DateInterval('P1D'); // 0 Day
        $dateRange = new DatePeriod($begin, $interval, $end);
        $format = "d-m-Y"; //Edited by Akshay on 17-1-2024
        $arr_dates = [];
        foreach ($dateRange as $date) {
            $date->setTimezone(new DateTimeZone('Asia/Kolkata')); //Edited by Akshay on 17-1-2024
            $arr_dates[] = $date->format($format);
        }
        //end
        $query_format = "%d-%m-%Y"; //Edited by Akshay on 17-1-2024
        $arr_not_verified_dates = $this->EmployeeDetails->query("select DISTINCT DATE_FORMAT(att_date, '$query_format') AS formatted_date from 
       emp_detail_timeattandance where yearmonth='" . $month1 . "' and isdelete='Y' 
       and emp_pkey in (select emp_fkey from emp_proff where emp_branch='" . $branch . "') 
       and att_date  between att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 1) 
       and att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 2)");


        $not_verified = array();
        foreach ($arr_not_verified_dates as $arr_dates_verified) {
            $not_verified[] = $arr_dates_verified[0]['formatted_date']; //Edited by Akshay on 8-3-2024
        }
        // print_r($not_verified);
        $arr_sbo = array();
        foreach ($arr_dates as $key => $value) {
            //if (in_array($value, $not_verified)) { //edited by sinsiya to show the dates in selected month on 09-10-2024
            $data['id'] = $value; //date('j',  strtotime($value));
            $data['data'] = array($value);
            $arr_sbo["rows"][] = $data;
            // }//edited by sinsiya to show the dates in selected month on 09-10-2024
        }

        echo json_encode($arr_sbo);
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

        //        $arr_verified_dates_att = $this->EmployeeDetails->query("select distinct att_date from 
        //       emp_detail_timeattandance where yearmonth='" . $month1 . "' 
        //       and emp_pkey in (select emp_fkey from attendance_register where branch_code='" . $branch . "' and 
        //       month_year = DATE_FORMAT('" . $month1 . "', '%Y-%m') and isdelete='N' ) 
        //       and att_date  between att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 1) 
        //       and att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 2)");
        $arr_verified_dates_att = $this->EmployeeDetails->query("select distinct att_date from 
      emp_detail_timeattandance where yearmonth='" . $month1 . "' 
      and att_date  between att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 1) 
      and att_start_end_fn(DATE_FORMAT('" . $month1 . "', '%Y-%m-01'), 2)");
        $arr_verified_dates = array_merge($arr_verified_dates, $arr_verified_dates_ot, $arr_verified_dates_att);
        $not_verified = array();
        foreach ($arr_verified_dates as $arr_dates_verified) {
            $not_verified[] = isset($arr_dates_verified['emp_detail_timeattandance']['att_date']) ? $arr_dates_verified['emp_detail_timeattandance']['att_date'] : $arr_dates_verified['emp_ot_timeattandance']['att_date'];
        }
        //print_r($not_verified);
        $arr_sbo = array();
        foreach ($arr_dates as $key => $value) {
            if (in_array($value, $not_verified)) {
                $data['id'] = $value; //date('j',  strtotime($value));
                $data['data'] = array(!empty($value) ? date('d-m-Y', strtotime($value)) : ''); // Edited by Akshay on 14-1-2026
                $arr_sbo["rows"][] = $data;
            }
        }
        //debug($arr_sbo);
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

        $monthdd = (isset($_REQUEST['month']) && $_REQUEST['month'] != '') ? date('Y-m-d', strtotime($_REQUEST['month'])) : ''; // Edited by Akshay on 19-12-2025

        $eot_condition = ''; // Edited by Akshay on 30-4-2026

        // Edited by Akshay on 25-3-2026
        $monthChosen = '';

        if (isset($_REQUEST['monthChosen']) && $_REQUEST['monthChosen'] != '') {
            $input = $_REQUEST['monthChosen'];

            // Check if format is Y-m
            if (preg_match('/^\d{4}-\d{2}$/', $input)) {
                $monthChosen = $input . '-01';
            } else {
                $monthChosen = date('Y-m-d', strtotime($input));
            }
        }

        // End

        $branch_code = isset($_REQUEST['branch']) ? $_REQUEST['branch'] : '';
        if (!$branch_code) {
            return;
        }

        // Edited by Akshay on 23-7-2025
        $yearmonth1 = $monthChosen;
        $empid = isset($_REQUEST['empid']) ? $_REQUEST['empid'] : '';
        $empid_condition = '';
        if ($empid != '') {

            $empid_condition = " AND empdetails.emp_pkey = $empid 
                                
            ";
        }
        // End

        $resp_mispunches = array();
        if ($monthdd == '') {
            echo json_encode($resp_mispunches);
            die();
        }

        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');

        //added by megha on 09-07-2024 for getting yearmonth for attendance cycle changed dbs
        $selmonth = $this->EmployeeDetails->query("SELECT yearmonth FROM `emp_detail_timeattandance` WHERE `att_date` = '$monthdd' ORDER BY emp_detail_timeattandance_pkey DESC limit 1 "); // Edited by Akshay on 24-2-2026
        // $yearmonth1 = $selmonth['0']['emp_detail_timeattandance']['yearmonth'];

        $yearmonth = $monthdd;


        $error = 0;
        $arry_names = array();

        // Fetch employees with shifts - REMOVED LIMIT FROM QUERY
        // Edited by Akshay on 25-3-2026
        $total_emp = $this->EmployeeDetails->query("
                                                SELECT COUNT(DISTINCT empdetails.emp_pkey) AS total
                                                FROM emp_details AS empdetails
                                                LEFT JOIN emp_proff AS ep ON ep.emp_fkey = empdetails.emp_pkey 
                                                LEFT JOIN working_day_time_procedures wdt on wdt.day_time_seq = ep.day_time_seq
                                                LEFT JOIN termination on (termination.emp_fkey = empdetails.emp_pkey AND termination.status = 1)
                                                WHERE empdetails.branch_code = '$branch_code'
                                                AND empdetails.status = 1
                                                AND ep.emp_fkey IS NOT NULL
                                                AND ep.day_time_seq IS NOT NULL
                                                -- AND (wdt.min_aftr_off_dutty_cal_ot <> 0 or wdt.min_bfr_on_dutty_cal_ot <> 0 or wdt.work_time_day_off_cal_ot = 1)
                                                $empid_condition
                                                AND ep.joining_date <= '$yearmonth'
                                                AND (
                                                        termination.emp_fkey IS NULL
                                                        OR termination.last_approved_working_date >= '$yearmonth'
                                                    )
                                            ");

        $arr_emp_pkey = $this->EmployeeDetails->query("
                                                            SELECT 
                                                                empdetails.emp_pkey,
                                                                empdetails.emp_id,
                                                                CONCAT(
                                                                    IFNULL(empdetails.first_name, ''),
                                                                    ' ',
                                                                    IFNULL(empdetails.last_name, ''),
                                                                    IF(
                                                                        ep.emp_company_id IS NOT NULL 
                                                                        AND ep.emp_company_id != '',
                                                                        CONCAT(' - ', ep.emp_company_id),
                                                                        ''
                                                                    )
                                                                ) AS full_name
                                                            FROM emp_details AS empdetails
                                                            LEFT JOIN emp_proff AS ep ON ep.emp_fkey = empdetails.emp_pkey 
                                                            LEFT JOIN working_day_time_procedures wdt on wdt.day_time_seq = ep.day_time_seq
                                                            LEFT JOIN termination on (termination.emp_fkey = empdetails.emp_pkey AND termination.status = 1)
                                                            WHERE empdetails.branch_code = '$branch_code' 
                                                            AND empdetails.status = 1 
                                                            AND ep.emp_fkey IS NOT NULL
                                                            AND ep.day_time_seq IS NOT NULL
                                                            -- AND (wdt.min_aftr_off_dutty_cal_ot <> 0 or wdt.min_bfr_on_dutty_cal_ot <> 0 or wdt.work_time_day_off_cal_ot = 1)
                                                            $empid_condition
                                                            AND ep.joining_date <= '$yearmonth'
                                                            AND (
                                                                termination.emp_fkey IS NULL
                                                                OR termination.last_approved_working_date >= '$yearmonth'
                                                            )
                                                            GROUP BY empdetails.emp_pkey
                                                            ORDER BY trim(empdetails.first_name)
                                                        ");
        // End


        // Count of employees
        $arr_emp_pkey_count = count($arr_emp_pkey);
        // debug($arr_emp_pkey);
        foreach ($arr_emp_pkey as $emp_data) {
            $emp_pkey = isset($emp_data['empdetails']['emp_pkey'])
                ? $emp_data['empdetails']['emp_pkey']
                : 0;

            $emp_id = isset($emp_data['empdetails']['emp_id'])
                ? $emp_data['empdetails']['emp_id']
                : '';

            $full_name  = isset($emp_data[0]['full_name']) ? $emp_data[0]['full_name'] : '';

            //  debug($emp_pkey);
            $isDeletedCheck = $this->EmployeeDetails->query("
                                                                SELECT isdelete 
                                                                FROM emp_ot_timeattandance
                                                                WHERE emp_pkey = '$emp_pkey' 
                                                                AND att_date = '$yearmonth'
                                                            ");
            // DEBUG($isDeletedCheck);
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
                try {

                    $this->EmployeeDetails->query("UPDATE emp_ot_timeattandance eot
                                                        LEFT JOIN emp_proff ep 
                                                            ON ep.emp_fkey = eot.emp_pkey
                                                        LEFT JOIN working_day_time_procedures wdt 
                                                            ON wdt.day_time_seq = ep.day_time_seq

                                                        SET 
                                                            eot.ot_duration = NULL,
                                                            eot.set_duration = CASE 
                                                                WHEN eot.is_manual = 'N' THEN NULL
                                                                WHEN eot.is_manual = 'Y' THEN eot.set_duration
                                                                ELSE eot.set_duration
                                                            END

                                                        WHERE 
                                                            eot.emp_pkey = '$emp_pkey'
                                                            AND eot.att_date = '$monthdd'
                                                            -- AND IFNULL(wdt.min_aftr_off_dutty_cal_ot,0) = 0
                                                            -- AND IFNULL(wdt.min_bfr_on_dutty_cal_ot,0) = 0
                                                            -- AND IFNULL(wdt.work_time_day_off_cal_ot,0) <> 1;
                                                            AND eot.isdelete = 'Y';
                                                            ");


                    $this->EmployeeDetails->query("SELECT `ot_duration_register_date`('" . $monthdd . "', '" . $emp_pkey . "', '" . $branch_code . "') "); // Edited by Akshay on 6-3-2026

                } catch (Exception $e) {
                    debug($e);
                }

                // Edited by Akshay on 23-4-2026
                $condition = "$base_table.emp_pkey = '$emp_pkey' and ";
                $base_condition = "b.emp_pkey = '$emp_pkey' and ";
                // replace base table alias with eot for second query
                $eot_condition = str_replace($base_table, 'eot', $condition);
                // End
            } else {
                $condition = '';
                $emp = NULL;
            }


            // Edited by Akshay on 24-4-2026
            $arr_attendance_register = $this->EmployeeDetails->query("
                                                            SELECT COUNT(*) as count 
                                                            FROM attendance_register 
                                                            WHERE emp_fkey = $emp_pkey 
                                                            AND month_year = '" . date('Y-m', strtotime($yearmonth1)) . "' 
                                                            AND isdelete = 'N'
                                                        ");


            $iseditable = isset($arr_attendance_register[0][0]['count']) ? $arr_attendance_register[0][0]['count'] : 0;
            // End


            // Edited by Akshay on 23-4-2026
            $attendances = array();

            $attendances = $this->EmployeeDetails->query("
                SELECT 
                    emp_pkey,
                    att_date,

                    MAX(att_in_time) AS att_in_time,
                    MAX(att_out_time) AS att_out_time,
                    MAX(duration) AS duration,
                    MAX(site_transactions_fkey) AS site_transactions_fkey,
                    MAX(leaves) AS leaves,
                    MAX(emp_detail_timeattandance_pkey) AS emp_detail_timeattandance_pkey,

                    MAX(present) AS present,
                    MAX(weekoff) AS weekoff,
                    MAX(holiday) AS holiday,
                    MAX(others) AS others,

                    MAX(emp_company_id) AS emp_company_id,
                    MAX(joining_date) AS joining_date,
                    MAX(emp_fkey) AS emp_fkey,

                    MAX(minuts_calc_perday) AS minuts_calc_perday,

                    MAX(min_bfr_on_dutty_cal_ot) AS min_bfr_on_dutty_cal_ot,
                    MAX(min_aftr_off_dutty_cal_ot) AS min_aftr_off_dutty_cal_ot,
                    MAX(work_time_day_off_cal_ot) AS work_time_day_off_cal_ot,
                    MAX(ot_duration) AS ot_duration,
                    MAX(set_duration) AS set_duration,
                    MAX(remarks) AS remarks

                FROM (
                    -- 🔹 YOUR FULL UNION QUERY (unchanged)
                    SELECT 
                        b.emp_pkey,
                        b.att_date,
                        b.att_in_time,
                        b.att_out_time,
                        b.duration,
                        b.site_transactions_fkey,
                        b.leaves,
                        b.emp_detail_timeattandance_pkey,
                        b.present,
                        b.weekoff,
                        b.holiday,
                        b.others,

                        emp.emp_company_id,
                        emp.joining_date,
                        emp.emp_fkey,

                        wd.minuts_calc_perday,

                        eot.min_bfr_on_dutty_cal_ot,
                        eot.min_aftr_off_dutty_cal_ot,
                        eot.work_time_day_off_cal_ot,
                        eot.ot_duration,
                        eot.set_duration,
                        eot.remarks

                    FROM $base_table b
                    LEFT JOIN emp_proff emp ON emp.emp_fkey = b.emp_pkey
                    LEFT JOIN working_day_time_procedures wd ON wd.day_time_seq = emp.day_time_seq
                    LEFT JOIN emp_ot_timeattandance eot 
                        ON eot.emp_pkey = b.emp_pkey 
                        AND eot.att_date = '$yearmonth' 
                        AND eot.yearmonth = '$yearmonth1'
                    WHERE 
                        $base_condition
                        b.att_date = '$yearmonth'
                        AND b.yearmonth = '$yearmonth1'


                    UNION

                    SELECT 
                        eot.emp_pkey,
                        eot.att_date,
                        NULL, NULL, NULL, NULL, NULL, NULL,
                        NULL, NULL, NULL, NULL,

                        emp.emp_company_id,
                        emp.joining_date,
                        emp.emp_fkey,

                        wd.minuts_calc_perday,

                        eot.min_bfr_on_dutty_cal_ot,
                        eot.min_aftr_off_dutty_cal_ot,
                        eot.work_time_day_off_cal_ot,
                        eot.ot_duration,
                        eot.set_duration,
                        eot.remarks

                    FROM emp_ot_timeattandance eot
                    LEFT JOIN emp_proff emp ON emp.emp_fkey = eot.emp_pkey
                    LEFT JOIN working_day_time_procedures wd ON wd.day_time_seq = emp.day_time_seq
                    WHERE 
                        $eot_condition
                        eot.att_date = '$yearmonth'
                        AND eot.yearmonth = '$yearmonth1'
                        AND eot.isdelete = 'Y'


                ) t

                GROUP BY emp_pkey, att_date
                ORDER BY att_date
            ");



            if (empty($attendances)) {
                $arr_no_attendance_emps = $this->EmployeeDetails->query("SELECT joining_date, company_code, emp_id
                                                FROM emp_details
                                                LEFT JOIN emp_proff ON emp_fkey = emp_pkey
                                                WHERE emp_pkey = $emp_pkey;
                                                AND joining_date <= '$yearmonth';
                                                ");
                $attendances[0][0]['emp_pkey'] = $attendances[0][0]['emp_fkey'] = $emp_pkey;
                $attendances[0][0]['emp_id'] = isset($arr_no_attendance_emps['emp_details']['emp_id']) ? $arr_no_attendance_emps['emp_details']['emp_id'] : '';
                $attendances[0][0]['att_date'] = $yearmonth;
                $attendances[0][0]['company_code'] = isset($arr_no_attendance_emps['emp_details']['company_code']) ? $arr_no_attendance_emps['emp_details']['company_code'] : '';
                $attendances[0][0]['joining_date'] = isset($arr_no_attendance_emps['emp_proff']['joining_date']) ? $arr_no_attendance_emps['emp_proff']['joining_date'] : '';
            }



            foreach ($attendances as $val) {

                $row = $val[0];

                $arr_output = [];

                $arr_output['emp_pkey'] = $emp_pkey;
                $arr_output['emp_id'] = isset($emp_id) ? $emp_id : '';
                $arr_output['first_name'] = isset($full_name) ? $full_name : '';

                $arr_output['leaves'] = isset($row['leaves']) ? $row['leaves'] : '';
                $arr_output['emp_detail_timeattandance_pkey'] = isset($row['emp_detail_timeattandance_pkey']) ? $row['emp_detail_timeattandance_pkey'] : '';

                $arr_output['att_date'] = $yearmonth;
                $arr_output['yearmonth'] = $yearmonth1;

                $arr_output['att_in_time'] = isset($row['att_in_time']) ? $row['att_in_time'] : '';
                $arr_output['att_out_time'] = isset($row['att_out_time']) ? $row['att_out_time'] : '';
                $arr_output['duration'] = isset($row['duration']) ? $row['duration'] : '';
                $arr_output['site_transactions_fkey'] = isset($row['site_transactions_fkey']) ? $row['site_transactions_fkey'] : '';

                $arr_output['min_bfr_on_dutty_cal_ot'] = isset($row['min_bfr_on_dutty_cal_ot']) ? $row['min_bfr_on_dutty_cal_ot'] : '';
                $arr_output['min_aftr_off_dutty_cal_ot'] = isset($row['min_aftr_off_dutty_cal_ot']) ? $row['min_aftr_off_dutty_cal_ot'] : '';
                $arr_output['work_time_day_off_cal_ot'] = isset($row['work_time_day_off_cal_ot']) ? $row['work_time_day_off_cal_ot'] : '';
                $arr_output['ot_duration'] = isset($row['ot_duration']) ? $row['ot_duration'] : '';
                $arr_output['set_duration'] = isset($row['set_duration']) ? $row['set_duration'] : '';
                $arr_output['remarks'] = isset($row['remarks']) ? $row['remarks'] : '';

                $arr_output['joining_date'] = isset($row['joining_date']) ? $row['joining_date'] : '';

                // status
                $status = '';
                $status_color = 'black';

                if (!empty($row['weekoff'])) {
                    $status_color = "black";
                } else if (!empty($row['present'])) {
                    if (in_array(strtoupper($row['present']), ['A/A', 'P/A', 'A/P'])) {
                        $status_color = 'red';
                    } else if (strtoupper($row['present']) == 'P/P') {
                        $status_color = 'green';
                    }
                }

                $status =
                    (isset($row['present']) ? $row['present'] : '') . ' ' .
                    (isset($row['holiday']) ? $row['holiday'] : '') . ' ' .
                    (isset($row['weekoff']) ? $row['weekoff'] : '') . ' ' .
                    (isset($row['others']) ? $row['others'] : '');

                $arr_output['status'] = trim($status);
                $arr_output['status_color'] = $status_color;

                $arr_output['editable'] = ($iseditable == 0);
                $arr_output['company_code'] = strtoupper($this->Session->read('company_code'));

                $resp_mispunches["rows"][] = $arr_output;
            }
            // End
        }

        // APPLY PAGINATION ON RESPONSE ARRAY
        $all_rows = isset($resp_mispunches["rows"]) ? $resp_mispunches["rows"] : array();
        $total_records = count($all_rows);

        // Apply limit and offset to the response array
        if ($limit > 0) {
            $resp_mispunches["rows"] = array_slice($all_rows, $ofst, $limit);
        }

        $count = isset($resp_mispunches["rows"]) ? count($resp_mispunches["rows"]) : 0;

        if ($error) {
            $employees = '';
            foreach ($arry_names as $name) {
                $employees .= $name . ",";
            }

            echo json_encode($resp_mispunches);
        } else {
        }

        $resp_mispunches["total"] = $total_emp[0][0]['total']; // Edited by Akshay on 27-1-2026
        echo json_encode($resp_mispunches);
    }

    // Edited by Akshay on 23-4-2026

    public function listpunchesverify()
    {
        $this->autoRender = false;

        $limit = isset($_REQUEST['rows']) ? (int)$_REQUEST['rows'] : 20;
        $page  = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;

        $ofst = ($page - 1) * $limit;

        $monthdd = isset($_REQUEST['month'])
            ? date('Y-m-d', strtotime($_REQUEST['month']))
            : '';

        $branch_code = isset($_REQUEST['branch'])
            ? $_REQUEST['branch']
            : '';

        $yearmonth_date =
            (
                !empty($_REQUEST['yearmonth']) &&
                strtotime($_REQUEST['yearmonth'])
            )
            ? date('Y-m-01', strtotime($_REQUEST['yearmonth']))
            : '';

        if (empty($branch_code) || empty($monthdd)) {

            echo json_encode(array(
                "total" => 0,
                "rows"  => array()
            ));

            exit;
        }

        $this->EmployeeDetails->useDbConfig =
            $this->Session->read('ds');

        $month = date('Y-m', strtotime($yearmonth_date));

        // FIELD calculation
        $att_startdate = $this->EmployeeDetails->query("
        SELECT att_start_end_fn('$yearmonth_date',1) AS fromdate
    ");

        $start_date =
            $att_startdate[0][0]['fromdate'];

        $start   = new DateTime($start_date);
        $current = new DateTime($monthdd);

        $diff = $start->diff($current)->days;

        $field_index = $diff + 1;

        $field_name = "FIELD" . $field_index;

        // COUNT QUERY
        $count_sql = "
        SELECT COUNT(*) AS total

        FROM emp_ot_timeattandance eot

        INNER JOIN emp_details ed
            ON ed.emp_pkey = eot.emp_pkey

        INNER JOIN emp_proff ep
            ON ep.emp_fkey = ed.emp_pkey

        INNER JOIN working_day_time_procedures wd
            ON wd.day_time_seq = ep.day_time_seq

        WHERE
            ed.branch_code = '$branch_code'
            AND ed.status = 1
            AND eot.att_date = '$monthdd'
            AND eot.yearmonth = '$yearmonth_date'
            AND eot.isdelete = 'N'
            AND wd.overtime_monitoring = 'Y'
    ";

        $count_data = $this->EmployeeDetails->query($count_sql);

        $total = isset($count_data[0][0]['total'])
            ? $count_data[0][0]['total']
            : 0;

        // MAIN QUERY
        $sql = "
        SELECT

            eot.emp_pkey,
            eot.att_date,
            eot.yearmonth,

            COALESCE(eot.att_in_time, base.att_in_time)
                AS att_in_time,

            COALESCE(eot.att_out_time, base.att_out_time)
                AS att_out_time,

            COALESCE(eot.duration, base.duration)
                AS duration,

            COALESCE(eot.present, base.present)
                AS present,

            COALESCE(eot.weekoff, base.weekoff)
                AS weekoff,

            COALESCE(eot.leaves, base.leaves)
                AS leaves,

            COALESCE(eot.holiday, base.holiday)
                AS holiday,

            COALESCE(eot.others, base.others)
                AS others,

            eot.min_bfr_on_dutty_cal_ot,
            eot.min_aftr_off_dutty_cal_ot,
            eot.work_time_day_off_cal_ot,
            eot.ot_duration,
            eot.set_duration,
            eot.remarks,

            ed.emp_id,
            ed.first_name,
            ed.last_name,

            ep.emp_company_id,

            ar.emp_name,
            ar.isdelete AS ar_isdelete,
            ar.$field_name AS ar_status

        FROM emp_ot_timeattandance eot

        INNER JOIN emp_details ed
            ON ed.emp_pkey = eot.emp_pkey

        INNER JOIN emp_proff ep
            ON ep.emp_fkey = ed.emp_pkey

        INNER JOIN working_day_time_procedures wd
            ON wd.day_time_seq = ep.day_time_seq

        LEFT JOIN emp_detail_timeattandance base
            ON base.emp_pkey = eot.emp_pkey
            AND base.att_date = eot.att_date
            AND base.yearmonth = eot.yearmonth

        LEFT JOIN attendance_register ar
            ON ar.emp_fkey = eot.emp_pkey
            AND ar.branch_code = '$branch_code'
            AND ar.month_year = '$month'

        WHERE
            ed.branch_code = '$branch_code'
            AND ed.status = 1
            AND eot.att_date = '$monthdd'
            AND eot.yearmonth = '$yearmonth_date'
            AND eot.isdelete = 'N'
            AND wd.overtime_monitoring = 'Y'

        ORDER BY ed.first_name

        LIMIT $ofst, $limit
    ";

        $attendances =
            $this->EmployeeDetails->query($sql);

        $resp = array();

        $resp["rows"] = array();

        foreach ($attendances as $key => $val) {

            $row = array();

            $present = $val[0]['present'];
            $weekoff = $val[0]['weekoff'];
            $holiday = $val[0]['holiday'];
            $others  = $val[0]['others'];

            // STATUS
            if (
                $val['ar']['ar_isdelete'] == 'N' &&
                !empty($val['ar']['ar_status'])
            ) {

                $status = $val['ar']['ar_status'];

                $editable = false;
            } else {

                $status = trim(
                    "$present $holiday $weekoff $others"
                );

                $editable = true;
            }

            // COLOR
            $status_color = 'black';

            if (!empty($present)) {

                if (
                    in_array(
                        strtoupper($present),
                        array('A/A', 'P/A', 'A/P')
                    )
                ) {

                    $status_color = 'red';
                } elseif (
                    strtoupper($present) == 'P/P'
                ) {

                    $status_color = 'green';
                }
            }

            $row['emp_pkey'] = $val['eot']['emp_pkey'];

            $row['emp_id'] = $val['ed']['emp_id'];

            $row['first_name'] =
                $val['ed']['first_name'] . ' ' .
                $val['ed']['last_name'] . ' - ' .
                $val['ep']['emp_company_id'];

            $row['att_date'] =
                $val['eot']['att_date'];

            $row['yearmonth'] =
                $val['eot']['yearmonth'];

            $row['att_in_time'] =
                !empty($val[0]['att_in_time'])
                ? date(
                    'd-m-Y H:i:s',
                    strtotime($val[0]['att_in_time'])
                )
                : '';

            $row['att_out_time'] =
                !empty($val[0]['att_out_time'])
                ? date(
                    'd-m-Y H:i:s',
                    strtotime($val[0]['att_out_time'])
                )
                : '';

            $row['duration'] =
                $val[0]['duration'];

            $row['present'] =
                $present;

            $row['weekoff'] =
                $weekoff;

            $row['holiday'] =
                $holiday;

            $row['others'] =
                $others;

            $row['leaves'] =
                !empty($val[0]['leaves'])
                ? $val[0]['leaves']
                : '';

            $row['min_bfr_on_dutty_cal_ot'] =
                $val['eot']['min_bfr_on_dutty_cal_ot'];

            $row['min_aftr_off_dutty_cal_ot'] =
                $val['eot']['min_aftr_off_dutty_cal_ot'];

            $row['work_time_day_off_cal_ot'] =
                $val['eot']['work_time_day_off_cal_ot'];

            $row['ot_duration'] =
                $val['eot']['ot_duration'];

            $row['set_duration'] =
                $val['eot']['set_duration'];

            $row['remarks'] =
                $val['eot']['remarks'];

            $row['status'] =
                $status;

            $row['editable'] =
                $editable;

            $row['status_color'] =
                $status_color;

            $row['attendance_register'] =
                'N';

            $resp["rows"][] = $row;
        }

        $resp["total"] = $total;

        echo json_encode($resp);

        exit;
    }


    // End

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




    public function editpunch($att_date = '', $emp_id = '', $site_t_fkey = 0,  $att_in_time = '', $att_out_time = '')
    {
        $this->layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code'));
        // added by nimisha rajesh on 30/05/2019 issue - new button showing in edit puches when emp = heirarchy

        $emp_fkey = $this->Session->read('emp_fkey');
        $empid = $this->EmployeeDetails->query("SELECT `emp_id` FROM `emp_details` WHERE `emp_pkey` = '$emp_fkey'");
        //edited by sinsiya on 15-03-2024 for admin split when givving to live please add ||$company_code=='DEMO'
        if ($company_code == 'VGFS') {
            $id = isset($empid['0']['emp_details']['emp_id']) ? $empid['0']['emp_details']['emp_id'] : '';
            //        debug($id);
            if ($emp_id == $id) {
                $shownewbutton = 'no';
            } else {
                $shownewbutton = 'yes';
            }
            $this->set('shownewbutton', $shownewbutton);
        } else {
            $shownewbutton = 'yes';
            $this->set('shownewbutton', $shownewbutton);
        }
        // End
        //end of admin split
        $site_detailss = array();
        if (($site_t_fkey != 0)) {
            $site_detailss = $this->EmployeeDetails->query("select * from site_transactions left join designation on (designation.id = site_transactions.designation_id) left join site on (site.site_pkey = site_transactions.site_fkey) where site_transactions_pkey = '$site_t_fkey' ");
        }

        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $useracess = $this->EmployeeDetails->query("select * from user_access as Useraccess where user_fkey = '$emp_pkeys' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            //edited by sinsiya 15-03-2024 for admin split
            if ($company_code == 'VGFS') {
                if ($access == 'Y') {
                    $empmode = 1; //Hierarchy
                } else {
                    $empmode = 0; //employee
                }
            } else {
                $empmode = 1;
            }
        } else {
            $empmode = 2; //admin
        }
        //end
        $this->set('empmode', $empmode);
        $this->set('att_date', $att_date);
        $this->set('emp_id', $emp_id);
        $this->set('att_in_time', $att_in_time);
        $this->set('site_detailss', $site_detailss);
        $this->set('att_out_time', $att_out_time);
    }

    // Edited by Akshay on 28-11-2025
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

            date_default_timezone_set("Asia/Calcutta");
            $curtime = time();
            $time = strtotime($data["LOGDATE"]);

            //debug($data);
            if ($curtime > $time) {
                try {
                    $this->EditPunches->save($data);
                } catch (RuntimeException $e) {
                    debug($e);
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
    // End

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
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $resp = array('success' => false);
        if ($_REQUEST) {
            $att_date = trim($_REQUEST['att_date']);
            $emp_pkey = trim($_REQUEST['emp_pkey']);
            // $verification =$this->EditPunches->query("Select is_verified from emp_ot_master where emp_fkey = '$emp_pkey' and month = '$att_date'");
            // debug($verification);
            $resp = array('success' => false);
            if ($att_date && $emp_pkey) {

                $this->EditPunches->query("UPDATE emp_detail_timeattandance SET isdelete = 'N' WHERE emp_pkey = $emp_pkey AND att_date='$att_date'");
                $this->EditPunches->query("UPDATE emp_ot_timeattandance SET isdelete = 'N' WHERE emp_pkey = $emp_pkey AND att_date='$att_date'");
                //  debug("UPDATE emp_ot_timeattandance SET isdelete = 'N' WHERE emp_pkey = $emp_pkey AND att_date = '$att_date'");

                $resp = array('success' => true);
            }
        }
        echo json_encode($resp);
    }

    // Edited by Akshay on 23-4-2026
    public function verifyregisterentries()
    {
        $this->autoRender = false;

        $arr_requestdata = $this->request->data;
        $resp = array('success' => false);

        $noOtEmpCount = 0; // Edited by Akshay on 30-4-2026
        $arr_no_ot_emps = array(); // Edted by Akshay on 30-4-2026

        if (isset($arr_requestdata["records"])) {

            $records = json_decode($arr_requestdata["records"], true);

            $this->EditPunches->useDbConfig = $this->Session->read('ds');

            $arr_verified = array();
            $totalEmployees = count($records);
            $verifiedEmployees = 0;

            foreach ($records as $rec) {

                $emp_pkey = (int)$rec['emp_pkey'];
                $att_date = $rec['att_date'];
                $yearmonth = $rec['yearmonth'];

                // 🔹 Normalize date
                $converted_date = date("Y-m-d", strtotime(str_replace('-', '/', $att_date)));
                $month1 = date("Y-m-01", strtotime($converted_date));

                // 🔹 Get attendance window
                $att_startdate_q = $this->EditPunches->query("
                SELECT att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) AS monthly_att_fromdate
            ");

                $att_enddate_q = $this->EditPunches->query("
                SELECT att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) AS monthly_att_todate
            ");

                $att_startdate = $att_startdate_q[0][0]['monthly_att_fromdate'];
                $att_enddate   = $att_enddate_q[0][0]['monthly_att_todate'];

                // 🔹 Determine attendance month
                if ($converted_date < $att_startdate) {
                    $attendance_month = date("Y-m", strtotime("$month1 -1 month"));
                } elseif ($converted_date > $att_enddate) {
                    $attendance_month = date("Y-m", strtotime("$month1 +1 month"));
                } else {
                    $attendance_month = date("Y-m", strtotime($month1));
                }

                // 🔹 Check already verified
                $arr_att_reg = $this->EditPunches->query("
                SELECT COUNT(*) as verified 
                FROM attendance_register 
                WHERE emp_fkey = '$emp_pkey' 
                AND month_year = '$attendance_month' 
                AND isdelete = 'N'
            ");

                $verified_count = isset($arr_att_reg[0][0]['verified']) ? $arr_att_reg[0][0]['verified'] : 0;

                if ($verified_count > 0) {

                    $arr_emp_name = $this->EditPunches->query("
                    SELECT EmpName 
                    FROM employee_info 
                    WHERE emp_pkey = $emp_pkey
                ");

                    $empName = isset($arr_emp_name[0]['employee_info']['EmpName'])
                        ? $arr_emp_name[0]['employee_info']['EmpName']
                        : '';

                    $arr_verified[] = $empName;
                    $verifiedEmployees++;
                } else {

                    // Edited by Akshay on 8-5-2026
                    $exists = $this->EditPunches->query("
                                                                    SELECT emp_pkey
                                                                    FROM emp_ot_timeattandance
                                                                    WHERE emp_pkey = '$emp_pkey'
                                                                    AND att_date = '$att_date'
                                                                    AND yearmonth = '$yearmonth'
                                                                    LIMIT 1
                                                                ");

                    if (!empty($exists)) {

                        $this->EditPunches->query("
                                                            UPDATE emp_ot_timeattandance
                                                            SET isdelete = 'N'
                                                            WHERE emp_pkey = '$emp_pkey'
                                                            AND att_date = '$att_date'
                                                            AND yearmonth = '$yearmonth'
                                                        ");
                    } else {

                        $this->EditPunches->query("
                                                                INSERT INTO emp_ot_timeattandance
                                                                (
                                                                    emp_pkey,
                                                                    att_date,
                                                                    yearmonth,
                                                                    isdelete
                                                                )
                                                                VALUES
                                                                (
                                                                    '$emp_pkey',
                                                                    '$att_date',
                                                                    '$yearmonth',
                                                                    'N'
                                                                )
                                                            ");
                    }
                    // End
                }
            }

            // 🔹 Final response logic
            if ($verifiedEmployees === $totalEmployees) {
                $resp = array(
                    'success' => false,
                    'danger_message' => 'Selected employee attendance already verified'
                );
            } elseif ($verifiedEmployees > 0) {
                $resp = array(
                    'success' => true,
                    'danger_message' => 'Already attendance verified employees: ' . implode(", ", $arr_verified),
                    'success_message' => 'Verification successful.'
                );
            }
            // Edited by Akshay on 30-4-2026
            // elseif ($noOtEmpCount > 0) {
            //     if (($noOtEmpCount + $verifiedEmployees) == $totalEmployees) {
            //         $resp = array(
            //             'success' => false,
            //             'danger_message' => 'Selected employee attendance already verified or does not have overtime.'
            //         );
            //     } else {
            //         $resp = array(
            //             'success' => true,
            //             'danger_message' => 'Employees with no overtime entries: ' . implode(", ", $arr_no_ot_emps),
            //             'success_message' => 'Verification successful.'
            //         );
            //     }
            // }
            // End
            else {
                $resp = array(
                    'success' => true,
                    'success_message' => 'Verification successful.'
                );
            }
        }

        echo json_encode($resp);
    }
    // End

    // Edited by Akshay on 23-4-2026
    public function removeentries()
    {
        $this->autoRender = false;

        $arr_requestdata = $this->request->data;

        $removed_count = 0;
        $attendance_locked = 0;
        $ot_verified = 0;

        if (isset($arr_requestdata["records"])) {

            $records = json_decode($arr_requestdata["records"], true);

            $this->EditPunches->useDbConfig = $this->Session->read('ds');

            foreach ($records as $rec) {

                $emp_pkey   = (int)$rec['emp_pkey'];
                $att_date   = $rec['att_date'];
                $yearmonth  = $rec['yearmonth'];

                $month_year = !empty($yearmonth)
                    ? date('Y-m', strtotime($yearmonth))
                    : '';

                // Attendance register check
                $arr_emps = $this->EditPunches->query("
                SELECT isdelete
                FROM attendance_register
                WHERE month_year = '$month_year'
                AND emp_fkey = '$emp_pkey'
            ");

                $status = isset($arr_emps[0]['attendance_register']['isdelete'])
                    ? $arr_emps[0]['attendance_register']['isdelete']
                    : 'Y';

                // OT verification check
                $verification = $this->EditPunches->query("
                SELECT is_verified
                FROM emp_ot_master
                WHERE emp_fkey = '$emp_pkey'
                AND month = '$yearmonth'
            ");

                $is_verified = isset($verification[0]['emp_ot_master']['is_verified'])
                    ? $verification[0]['emp_ot_master']['is_verified']
                    : 'N';

                // OT verified
                if ($is_verified == 'Y') {

                    $ot_verified++;

                    continue;
                }

                // Attendance locked
                if ($status != 'Y') {

                    $attendance_locked++;

                    continue;
                }

                // Remove
                $this->EditPunches->query("
                UPDATE emp_ot_timeattandance
                SET isdelete = 'Y'
                WHERE emp_pkey = '$emp_pkey'
                AND att_date = '$att_date'
                AND yearmonth = '$yearmonth'
            ");

                $removed_count++;
            }
        }

        echo json_encode(array(
            'success' => true,
            'removed_count' => $removed_count,
            'attendance_locked' => $attendance_locked,
            'ot_verified' => $ot_verified
        ));

        exit;
    }
    // End

    // Edited by Akshay on 21-1-2026
    public function updateSetDuration()
    {
        $this->autoRender = false;
        $resp = array('success' => false);

        // Read JSON payload
        $data = json_decode($this->request->input(), true);
        $no_ovetime = 0;
        $success_count = 0;

        if (empty($data)) {
            echo json_encode($resp);
            return;
        }

        $value      = isset($data['value']) ? trim($data['value']) : null;
        $remark     = isset($data['remark']) ? trim($data['remark']) : '';
        $att_dates  = isset($data['att_date']) ? $data['att_date'] : array();
        $emp_pkeys  = isset($data['emp_pkey']) ? $data['emp_pkey'] : array();
        $branch = isset($data['branch']) ? trim($data['branch']) : ''; // Edited by Akshay on 30-4-2026
        $month = isset($data['month']) ? trim($data['month']) : ''; // Edited by Akshay on 30-4-2026

        if ($value === null || $value < 0 || empty($att_dates) || empty($emp_pkeys)) {
            echo json_encode($resp);
            return;
        }

        $this->EditPunches->useDbConfig = $this->Session->read('ds');

        foreach ($att_dates as $index => $att_date) {

            if (!isset($emp_pkeys[$index])) {
                continue;
            }

            $att_date = trim($att_date);
            $emp_pkey = (int)$emp_pkeys[$index];

            // Edited by Akshay on 30-4-2026
            $arr_processed_emps = $this->EditPunches->query("SELECT COUNT(*) AS count FROM attendance_register WHERE emp_fkey = '$emp_pkey' AND month_year = '$month' AND isdelete = 'N';");
            $processed_emp_count = isset($arr_processed_emps[0][0]['count'])
                ? $arr_processed_emps[0][0]['count']
                : (isset($arr_processed_emps[0]['attendance_register']['count'])
                    ? $arr_processed_emps[0]['attendance_register']['count']
                    : 0);
            if ($processed_emp_count != 0) {
                $arr_processed_emp_name = $this->EditPunches->query("
                                            SELECT CONCAT(TRIM(ed.first_name), ' ', TRIM(ed.last_name), ' - ', ep.emp_company_id) AS name
                                            FROM emp_details ed
                                            LEFT JOIN emp_proff ep ON ep.emp_fkey = ed.emp_pkey
                                            WHERE ed.emp_pkey = $emp_pkey
                                        ");
                $arr_processed_emp[] = isset($arr_processed_emp_name[0][0]['name']) ? $arr_processed_emp_name[0][0]['name'] : '';
            }

            // End
            if (!$att_date || !$emp_pkey || $processed_emp_count != 0) {
                continue;
            }

            // Check if record exists
            $count = $this->EditPunches->query("
            SELECT COUNT(*) AS cnt
            FROM emp_ot_timeattandance
            WHERE emp_pkey = $emp_pkey
              AND att_date = '$att_date'
        ")[0][0]['cnt'];

            if ($count > 0) {

                // $setParts = array(
                //     "is_manual = 'Y'"
                // );

                // // Only update set_duration if value is NOT empty string
                // if ($value !== '') {
                //     $setParts[] = "set_duration = '$value'";
                // }

                // Edited by Akshay on 15-5-2026
                $setParts = array();

                if ($value !== '') {
                    $setParts[] = "set_duration = '$value'";
                    $setParts[] = "is_manual = 'Y'";
                } else {
                    $setParts[] = "set_duration = NULL";
                    $setParts[] = "is_manual = 'N'";
                }

                // End

                // Remarks handling
                if ($remark !== '') {
                    $setParts[] = "remarks = '$remark'";
                } else {
                    $setParts[] = "remarks = NULL";
                }

                $setSql = implode(", ", $setParts);

                $this->EditPunches->query("
                                            UPDATE emp_ot_timeattandance
                                            SET $setSql
                                            WHERE emp_pkey = $emp_pkey
                                            AND att_date = '$att_date'
                                        ");
                $success_count++;
            } else {
                if ($value === '') {
                    $no_ovetime++;
                    continue; // skip insert if no duration
                }

                // Determine correct yearmonth
                $month1 = date('Y-m-01', strtotime($att_date));

                $att_startdate = $this->EditPunches->query("
                SELECT att_start_end_fn('$month1', 1) AS monthly_att_fromdate
            ");

                $att_enddate = $this->EditPunches->query("
                SELECT att_start_end_fn('$month1', 2) AS monthly_att_todate
            ");

                $att_startdate1 = strtotime($att_startdate[0][0]['monthly_att_fromdate']);
                $att_enddate1   = strtotime($att_enddate[0][0]['monthly_att_todate']);
                $att_date_ts    = strtotime($att_date);

                if ($att_date_ts < $att_startdate1) {
                    $yearmonth = date('Y-m-01', strtotime('-1 month', strtotime($month1)));
                } elseif ($att_date_ts > $att_enddate1) {
                    $yearmonth = date('Y-m-01', strtotime('+1 month', strtotime($month1)));
                } else {
                    $yearmonth = $month1;
                }

                // Insert new record
                $this->EditPunches->query("
                INSERT INTO emp_ot_timeattandance
                    (emp_pkey, att_date, set_duration, yearmonth, is_manual, remarks)
                VALUES
                    ($emp_pkey, '$att_date', '$value', '$yearmonth', 'Y',
                     " . ($remark !== '' ? "'$remark'" : "NULL") . ")
            ");

                $success_count++;
            }
        }

        $message = ($no_ovetime > 0) ? "No overtime records found for one or more employee(s)" : '';

        // Edited by Akshay on 30-4-2026
        if (!empty($arr_processed_emp)) {
            $message = 'Attendance is verified for employees: ' . implode(", ", $arr_processed_emp);
        }
        // End

        $success = ($success_count > 0) ? true : false;

        $resp = array('success' => $success, 'message' => $message);
        echo json_encode($resp);
    }


    // End

    public function setRemarks()
    {
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $resp = array('success' => false);
        if ($_REQUEST) {
            $att_date = trim($_REQUEST['att_date']);
            //edited by sinsiya on 16-10-2024
            $empdetailtimeattendance_pkey = trim($_REQUEST['emp_pkey']);
            $arr_emp = $this->EditPunches->query("select emp_pkey from emp_detail_timeattandance where emp_detail_timeattandance_pkey = $empdetailtimeattendance_pkey");
            $emp_pkey = isset($arr_emp['0']['emp_detail_timeattandance']['emp_pkey']) ? $arr_emp['0']['emp_detail_timeattandance']['emp_pkey'] : '';
            $value = trim($_REQUEST['value']);
            // debug($value);
            $resp = array('success' => false);
            if ($att_date && $emp_pkey && $value) {

                $this->EditPunches->query("UPDATE emp_ot_timeattandance SET remarks = '" . $value . "' WHERE emp_pkey = $emp_pkey AND att_date='$att_date'");
                // echo "UPDATE emp_ot_timeattandance SET remarks = '".$value."' WHERE emp_pkey = $emp_pkey AND att_date='$att_date'";
                // exit;
                // debug("UPDATE emp_ot_timeattandance SET remarks = '" . $value . "' WHERE emp_pkey = $emp_pkey AND att_date='$att_date'");
                $resp = array('success' => true);
            }
        }
        echo json_encode($resp);
    }

    // Edited by Akshay on 20-1-2026
    public function updateOvertime($set_ot = 0, $remarks = '')
    {
        $set_ot = ($set_ot == 0) ? '' : $set_ot;
        $this->set("set_ot", $set_ot);
        $this->set("remarks", $remarks);
    }

    public function overtimeDataUpdate()
    {
        $this->autoRender = false;

        // Get month
        if (isset($_REQUEST['month']) && !empty($_REQUEST['month'])) {
            $monthdd = date('Y-m-d', strtotime($_REQUEST['month']));
        } else {
            $monthdd = '';
        }

        // Get branch_code
        if (isset($_REQUEST['branch'])) {
            $branch_code = $_REQUEST['branch'];
        } else {
            $branch_code = '';
        }

        // Get empid
        if (isset($_REQUEST['empid'])) {
            $empid = $_REQUEST['empid'];
        } else {
            $empid = '';
        }

        if ($monthdd == '' || $branch_code == '') {
            return;
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        // Get yearmonth
        $selmonth = $this->EmployeeDetails->query("
        SELECT yearmonth 
        FROM emp_detail_timeattandance 
        WHERE att_date = '$monthdd' 
        LIMIT 1
        ");

        $yearmonth1 = $selmonth[0]['emp_detail_timeattandance']['yearmonth'];

        $emp_condition = '';
        if ($empid != '') {
            $emp_condition = " AND emp_pkey = $empid ";
        }

        $employees = $this->EmployeeDetails->query("
        SELECT emp_pkey 
        FROM emp_details 
        WHERE branch_code = '$branch_code'
        AND status = 1
        $emp_condition
        ");

        foreach ($employees as $emp) {
            $emp_pkey = $emp['emp_details']['emp_pkey'];

            // Edited by Akshay on 6-3-2026
            $this->EmployeeDetails->query("
            SELECT ot_duration_register_date(
                '$yearmonth1',
                '$emp_pkey',
                '$branch_code'
            )
        ");
            // End
        }
    }

    public function getEmployeesByBranch()
    {
        $this->autoRender = false;

        $branch = $this->request->data('branch');

        if (!$branch) {
            echo json_encode([]);
            return;
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $employees = $this->EmployeeDetails->query("
                                                        SELECT emp_pkey, first_name, last_name, emp_id
                                                        FROM emp_details
                                                        WHERE branch_code = '$branch'
                                                        AND status = 1
                                                        ORDER BY first_name
                                                    ");

        echo json_encode($employees);
    }
    // End
}
