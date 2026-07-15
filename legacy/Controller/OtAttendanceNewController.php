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
class OtAttendanceNewController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'OtAttendanceNew';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Units', 'AttendanceRegister', 'DbConfig');
    public $components = array('MasterdataManagement');

    public function Register($month = '', $emp_pkey = 0, $branch = '')
    {

        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));

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
        if (!empty($emp_pkey)) {
            $condition = "emp_proff.emp_fkey = '$emp_pkey' and ";
            $emp = $emp_pkey;

            //	echo "inside";
        } else {
            $condition = '';
            $emp = 0;
        }
        if (!empty($branch)) {
            $condition1 = "emp.branch_code = '$branch' and ";
            $branch = $branch;
        } else {
            $condition1 = '';
            $branch = NULL;
        }
        $arr_exists = $this->EmployeeDetails->query("select count(*) AS COUNT from attendance_register where emp_fkey in ($emp_pkey) and  month_year = '$month' and isdelete ='Y'");
        $count = isset($arr_exists['0']['0']['COUNT']) ? $arr_exists['0']['0']['COUNT'] : 0;
        $this->set("count", $count);
        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        // $attend = $this->EmployeeDetails->query("select ot_duration_register_date('$yearmonth','$emp','$branch')");

        // Edited by Akshay on 27-4-2026
        // 🔹 Get cycle dates
        $res = $this->EmployeeDetails->query("
            SELECT 
                att_start_end_fn('$yearmonth', 1) AS start_date,
                att_start_end_fn('$yearmonth', 2) AS end_date
        ");

        $cycleStart = !empty($res[0][0]['start_date']) ? $res[0][0]['start_date'] : $yearmonth;
        $cycleEnd   = !empty($res[0][0]['end_date']) ? $res[0][0]['end_date'] : $yearmonth;


        // 🔹 Loop through each date
        $start = new DateTime($cycleStart);
        $end   = new DateTime($cycleEnd);
        $end->modify('+1 day'); // include last day

        $period = new DatePeriod($start, new DateInterval('P1D'), $end);


        // 🔹 Call function for each day
        foreach ($period as $date) {

            $loopDate = $date->format('Y-m-d');

            $this->EmployeeDetails->query(
                "
                SELECT ot_duration_register_date('$loopDate', '$emp', '$branch')"
            );
        }
        // End

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $condition_emps = "emp_proff.attr1 = '$emp_pkeys' and ";
        } else {
            $condition_emps = "";
        }
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read('emp_fkey');
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");

            if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
                $branch = $payroUser[0]['emp_proff']['emp_branch'];
                $condition_emps = "branch_code !='$branch' and ";
            }
        }

        $attendancesnotverified = $this->EmployeeDetails->query("select * from emp_ot_master left join emp_details as emp on (emp.emp_pkey = emp_ot_master.emp_fkey) left join emp_proff on (emp_proff.emp_fkey = emp_ot_master.emp_fkey) where $condition_emps $condition $condition1 month = '$yearmonth' and is_verified = 'N' and emp.status = 1 ");

        //Edited by Akshay on 13-8-2024
        if ($company_code  == 'HRBL' || $company_code  == 'GEDE') {
            $attendancesverified = $this->EmployeeDetails->query("
            SELECT 
                emp_ot_master.*, 
                emp.*, 
                emp_proff.*, 
                emp_ot_process.*
            FROM 
                emp_ot_master
            LEFT JOIN 
                emp_details AS emp ON emp.emp_pkey = emp_ot_master.emp_fkey
            LEFT JOIN 
                emp_proff ON emp_proff.emp_fkey = emp_ot_master.emp_fkey
            LEFT JOIN 
                emp_ot_process ON emp_ot_process.emp_ot_master_fkey = emp_ot_master.emp_ot_master_pkey
            WHERE $condition_emps $condition $condition1
                emp_ot_master.month = '$yearmonth' 
                AND emp_ot_master.is_verified = 'Y' 
                AND emp.status = 1
                AND (emp_ot_process.emp_ot_master_fkey IS NULL OR 
                     (emp_ot_process.process != 'processed'))
        ");

            $attendanceprocessed = $this->EmployeeDetails->query("
            SELECT emp_ot_master.*, emp.*, emp_proff.*, emp_ot_process.*
            FROM emp_ot_master
            LEFT JOIN emp_details AS emp ON emp.emp_pkey = emp_ot_master.emp_fkey
            LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_ot_master.emp_fkey
            LEFT JOIN emp_ot_process ON emp_ot_process.emp_ot_master_fkey = emp_ot_master.emp_ot_master_pkey
            WHERE $condition_emps $condition $condition1 emp_ot_master.month = '$yearmonth' AND is_verified = 'Y' AND emp.status = 1
           AND emp_ot_process.process = 'processed'");

            $this->set("attendanceprocessed", $attendanceprocessed);
        } else {
            $attendancesverified = $this->EmployeeDetails->query("
            SELECT 
                emp_ot_master.*, 
                emp.*, 
                emp_proff.*
            FROM 
                emp_ot_master
            LEFT JOIN 
                emp_details AS emp ON emp.emp_pkey = emp_ot_master.emp_fkey
            LEFT JOIN 
                emp_proff ON emp_proff.emp_fkey = emp_ot_master.emp_fkey
            WHERE $condition_emps $condition $condition1
                emp_ot_master.month = '$yearmonth' 
                AND emp_ot_master.is_verified = 'Y' 
                AND emp.status = 1
        ");
        }
        //End

        $this->set("company_code", $company_code);
        $this->set("attendancesnotverified", $attendancesnotverified);
        $this->set("attendancesverified", $attendancesverified);
    }

    public function Approved($month = '', $emp_pkey = 0, $tab = 1, $branch = '0') //Edited by Akshay on 25-7-2024
    {
        $this->autoRender = false;
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $yearmonth = $month;
        //  debug($yearmonth);

        //Edited by Akshay on 25-7-2024
        $this->set("tab", $tab);
        //End
        if ($emp_pkey != 0) {
            if ($company_code  == 'HRBL' || $company_code  == 'GEDE') {
                $condition = "emp_ot_master.emp_fkey = '$emp_pkey' and "; //Edited by Akshay on 12-8-2024
            } else {
                $condition = "emp_ot_master.emp_fkey = '$emp_pkey' ";
            }
        } else {
            $condition = '';
        }
        //Edited by Akshay on 12-8-2024
        if ($branch != '0') {
            if ($company_code  == 'HRBL' || $company_code  == 'GEDE') {
                $condition = $condition . "emp_details.branch_code = '$branch' and ";
            } else {
                $condition = $condition . "emp_details.branch_code = '$branch' ";
            }
        }
        //Edited by Akshay on 12-8-2024
        $emp = isset($emp_pkey) ? $emp_pkey : NUll;
        $attendancesverified = array();
        $attendancesnotverified = array();

        try {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $attendancesnotverified = $this->EmployeeDetails->query("select * from emp_ot_master 
                                                                    LEFT JOIN emp_details ON emp_details.emp_pkey = emp_ot_master.emp_fkey
                                                                    where $condition and month = '$yearmonth' and is_verified = 'N' ");
            // $attendancesverified = $this->EmployeeDetails->query("select * from emp_ot_master where $condition month = '$yearmonth' and is_verified = 'Y' ");

            //Edited by Akshay on 13-8-2024

            if ($company_code  == 'HRBL' || $company_code  == 'GEDE') {
                $attendancesverified = $this->EmployeeDetails->query("
                SELECT emp_ot_master.*
                FROM emp_ot_master
                LEFT JOIN emp_ot_process ON emp_ot_process.emp_ot_master_fkey = emp_ot_master.emp_ot_master_pkey
                LEFT JOIN emp_details ON emp_details.emp_pkey = emp_ot_master.emp_fkey
                WHERE emp_ot_master.is_verified = 'Y'
                  AND emp_ot_master.month = '$yearmonth'
                  AND $condition
                  (emp_ot_process.emp_ot_master_fkey IS NULL OR 
                         (emp_ot_process.process != 'processed'))
            ");

                $attendanceprocessed = $this->EmployeeDetails->query("
                SELECT emp_ot_master.*
                FROM emp_ot_master
                LEFT JOIN emp_ot_process ON emp_ot_process.emp_ot_master_fkey = emp_ot_master.emp_ot_master_pkey
                LEFT JOIN emp_details ON emp_details.emp_pkey = emp_ot_master.emp_fkey
                WHERE emp_ot_master.is_verified = 'Y'
                  AND emp_ot_master.month = '$yearmonth'
                  AND $condition 
                   emp_ot_process.process = 'processed' 
            ");

                $this->set("attendanceprocessed", $attendanceprocessed);
            } else {
                $attendancesverified = $this->EmployeeDetails->query("
                SELECT emp_ot_master.*
                FROM emp_ot_master
                LEFT JOIN emp_details ON emp_details.emp_pkey = emp_ot_master.emp_fkey
                WHERE emp_ot_master.is_verified = 'Y'
                  AND emp_ot_master.month = '$yearmonth'
                  AND $condition
            ");
            }
            //End
        } catch (Exception $e) {
            //  debug($e);
        }
        // debug($attendanceprocessed);
        $this->set("company_code", $company_code);
        $this->set("attendancesverified", $attendancesverified);
        $this->set("attendancesnotverified", $attendancesnotverified);

        $this->render('register');
    }
    public function form($emp_pkey = 0, $duration = 0, $month = 0)
    {
        //debug("emp ".$emp_pkey);
        //debug("duration ".$duration);
        //debug("month ".$month);

        $month = $month;
        $emp = $emp_pkey;
        $duration = $duration;
        $this->set("month", $month);
        $this->set("duration", $duration);
        $this->set("emp", $emp);
    }
    public function save()
    {
        $this->autoRender = false;
        $arr_data = $this->request->data;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $duration = $arr_data['duration'];
        $remarks = $arr_data['remarks'];
        $month = $arr_data['month'];
        $emp = $arr_data['emp'];
        // $this->EmployeeDetails->query("update emp_ot_master set set_duration = '$duration', remarks = '$remarks',  is_verified ='Y' where emp_fkey = '$emp' and month = '$month' ");
        //Edited by Akshay on 13-8-2024
        //$this->EmployeeDetails->query("update emp_ot_master set set_duration = '$duration', remarks = '$remarks', is_verified ='Y', total_duration =COALESCE(set_duration, total_duration), set_duration = total_duration where emp_fkey = '$emp' and month = '$month' ");
        $this->EmployeeDetails->query("update emp_ot_master set set_duration =  CASE 
                      WHEN '$duration' = '' THEN NULL 
                      ELSE '$duration' 
                   END, remarks = '$remarks', is_verified ='Y',  set_duration = COALESCE(set_duration, total_duration) where emp_fkey = '$emp' and month = '$month' ");
        //End
        $success = 1;
    }

    public function savedata()
    {
        $this->autoRender = false;
        $arr_data = $this->request->data;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        //  $pkeys = $_POST['pkeys']; // Array of pkeys
        $month = $_POST['month']; // Month value
        $month = $month . '-01';
        $emp = $_POST['emp']; // Emp value
        $duration = $_POST['duration']; // Duration value
        $remarks = $_POST['remarks']; // Remarks value

        //$this->EmployeeDetails->query("update emp_ot_master set set_duration = '$duration', remarks = '$remarks', is_verified ='N' where emp_fkey = '$emp' and month = '$month' ");
        $this->EmployeeDetails->query("update emp_ot_master set set_duration =  CASE 
                      WHEN '$duration' = '' THEN NULL 
                      ELSE '$duration' 
                   END, remarks = '$remarks', is_verified ='N' where emp_fkey = '$emp' and month = '$month' ");
        $result = 1;
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
    public function Toapproved()
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $yearmonth = $month . '-01';
        //  debug($yearmonth);

        $arr_resp = array(
            'data' => array()
        );
        if ($emp_pkey) {
            $condition = "emp_fkey = '$emp_pkey' and ";
        } else {
            $condition = '';
        }
        $emp = isset($emp_pkey) ? $emp_pkey : NUll;
        $attendancesnotverified = $this->EmployeeDetails->query("select * from emp_ot_master where $condition month = '$yearmonth' and is_verified = 'N' ");
        $this->set("attendancesnotverified", $attendancesnotverified);
        foreach ($attendancesnotverified as $key => $att) {
            $arr_resp['data'][$key]/* ['EmpName'] */ = array_merge($att['0'], $att['EmployeeDetails']);

            $i++;
        }
        return json_encode($arr_resp);
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
        if (isset($payroUser[0]['emp_proff']['payro_priv'])) {
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        } else {
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
        }
        $this->set('arr_branches', $arr_branches);
        //debug($arr_branches);
        $this->set('user_group', $user_group);
        $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        $this->set("arr_employees", $arr_employees);
    }
    public function subtable($emppkey = 0, $month = '')
    {

        //$this->autoRender = false;
        $yearmonth = $month . '-01';
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        // Edited by Akshay on 22-5-2026
        $res = $this->EmployeeDetails->query("
            SELECT 
                att_start_end_fn('$yearmonth', 1) AS start_date,
                att_start_end_fn('$yearmonth', 2) AS end_date
        ");
        $cycleStart = !empty($res[0][0]['start_date']) ? $res[0][0]['start_date'] : $yearmonth;
        $cycleEnd   = !empty($res[0][0]['end_date']) ? $res[0][0]['end_date'] : $yearmonth;
        // End
        $startDate = new DateTime($cycleStart);
        $endDate   = new DateTime($cycleEnd);

        $days = $startDate->diff($endDate)->days;

        $sequence = [];

        for ($i = 0; $i <= $days; $i++) {
            $sequence[] = "SELECT $i AS seq";
        }

        $sequenceSql = implode(" UNION ALL ", $sequence);

        $query = "

                    SELECT 
                        empdetails.first_name,
                        empdetails.last_name,

                        eot.id,
                        empdetails.emp_pkey,

                        dates.att_date,

                        CASE 
                            WHEN termination.emp_fkey IS NULL 
                                OR termination.last_approved_working_date >= dates.att_date
                            THEN eot.att_in_time
                            ELSE ''
                        END AS att_in_time,
                        CASE 
                            WHEN termination.emp_fkey IS NULL 
                                OR termination.last_approved_working_date >= dates.att_date
                            THEN eot.att_out_time
                            ELSE ''
                        END AS att_out_time,

                        IFNULL(edt.duration,0) AS duration,

                        eot.min_bfr_on_dutty_cal_ot,
                        eot.min_aftr_off_dutty_cal_ot,
                        eot.work_time_day_off_cal_ot,
                        CASE 
                            WHEN termination.emp_fkey IS NULL 
                                OR termination.last_approved_working_date >= dates.att_date
                            THEN eot.ot_duration
                            ELSE ''
                        END AS ot_duration,

                        edt.present,
                        edt.weekoff,
                        edt.leaves,
                        edt.holiday,
                        edt.others,

                        CASE
                            WHEN EXISTS (
                                SELECT 1
                                FROM holidays h
                                WHERE h.HOLIDAY_GROUP_ID = emp.HOLIDAY_GROUP_ID
                                AND h.HOLIDAYDATE = dates.att_date
                            )
                            THEN 'HO'

                            WHEN (

                                (
                                    WEEKDAY(dates.att_date) = 0
                                    AND (
                                        wd.monday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'monday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                                OR (
                                    WEEKDAY(dates.att_date) = 1
                                    AND (
                                        wd.tuesday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'tuesday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                                OR (
                                    WEEKDAY(dates.att_date) = 2
                                    AND (
                                        wd.wednesday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'wednesday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                                OR (
                                    WEEKDAY(dates.att_date) = 3
                                    AND (
                                        wd.thursday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'thursday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                                OR (
                                    WEEKDAY(dates.att_date) = 4
                                    AND (
                                        wd.friday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'friday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                                OR (
                                    WEEKDAY(dates.att_date) = 5
                                    AND (
                                        wd.saturday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'saturday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                                OR (
                                    WEEKDAY(dates.att_date) = 6
                                    AND (
                                        wd.sunday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'sunday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                            )
                            THEN 'WO'

                            WHEN (
                                (WEEKDAY(dates.att_date) = 0 AND wd.monday_f = 'Y')
                                OR (WEEKDAY(dates.att_date) = 1 AND wd.tuesday_f = 'Y')
                                OR (WEEKDAY(dates.att_date) = 2 AND wd.wednesday_f = 'Y')
                                OR (WEEKDAY(dates.att_date) = 3 AND wd.thursday_f = 'Y')
                                OR (WEEKDAY(dates.att_date) = 4 AND wd.friday_f = 'Y')
                                OR (WEEKDAY(dates.att_date) = 5 AND wd.saturday_f = 'Y')
                                OR (WEEKDAY(dates.att_date) = 6 AND wd.sunday_f = 'Y')
                            )
                            THEN '/WO'

                            ELSE ''

                        END AS off_type,
                        CASE 
                            WHEN termination.emp_fkey IS NULL 
                                OR termination.last_approved_working_date >= dates.att_date
                            THEN eot.set_duration
                            ELSE ''
                        END AS set_duration,
                        eot.remarks,
                        eot.is_manual,

                        edt.yearmonth,

                        eot.created_by,
                        eot.creation_date,
                        eot.isdelete,

                        wd.minuts_calc_perday,
                        emp.emp_fkey

                    FROM
                    (
                        SELECT 
                            DATE('$cycleStart') + INTERVAL seq DAY AS att_date
                        FROM
                        (
                            $sequenceSql
                        ) seq_table
                    ) dates

                    LEFT JOIN emp_details AS empdetails
                        ON empdetails.emp_pkey = '$emppkey'

                    LEFT JOIN emp_proff AS emp
                        ON emp.emp_fkey = empdetails.emp_pkey

                    LEFT JOIN working_day_time_procedures AS wd
                        ON wd.day_time_seq = emp.day_time_seq

                    LEFT JOIN emp_ot_timeattandance eot
                        ON eot.emp_pkey = empdetails.emp_pkey
                        AND eot.att_date = dates.att_date

                    LEFT JOIN emp_detail_timeattandance edt
                        ON edt.emp_pkey = empdetails.emp_pkey
                        AND edt.att_date = dates.att_date

                    LEFT JOIN termination on (termination.emp_fkey = empdetails.emp_pkey AND termination.status = 1)

                    WHERE dates.att_date >= emp.joining_date

                    ORDER BY dates.att_date
                    ";

        $attendances = $this->EmployeeDetails->query($query);

        // Edited by Akshay on 22-5-2026
        $ot_master = $this->EmployeeDetails->query("SELECT 
                                                IFNULL(SUM(set_duration), 0) AS new_ot_duration,
                                                set_duration
                                            FROM emp_ot_master
                                            WHERE emp_fkey = '$emppkey'
                                            AND month = '$yearmonth';");
        $new_ot_duration = isset($ot_master[0][0]['new_ot_duration']) ? $ot_master[0][0]['new_ot_duration'] : '';
        $set_ot_duration = isset($ot_master[0]['emp_ot_master']['set_duration']) ? $ot_master[0]['emp_ot_master']['set_duration'] : '';
        $this->set('new_ot_duration', $new_ot_duration);
        $this->set('set_ot_duration', $set_ot_duration);
        // End

        $this->set('attendances', $attendances);
        $this->set('month', $yearmonth);
        $this->set('emp', $emppkey);
    }
    public function subtablegenpdf($emppkey = 0, $month = '')
    {
        //$this->autoRender = FALSE;
        $yearmonth = $month;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $emppey = $emppkey;
        // Edited by Akshay on 11-5-2026
        // Edited by Akshay on 22-5-2026
        $res = $this->EmployeeDetails->query("
            SELECT 
                att_start_end_fn('$yearmonth', 1) AS start_date,
                att_start_end_fn('$yearmonth', 2) AS end_date
        ");
        $cycleStart = !empty($res[0][0]['start_date']) ? $res[0][0]['start_date'] : $yearmonth;
        $cycleEnd   = !empty($res[0][0]['end_date']) ? $res[0][0]['end_date'] : $yearmonth;
        // End
        $startDate = new DateTime($cycleStart);
        $endDate   = new DateTime($cycleEnd);

        $days = $startDate->diff($endDate)->days;

        $sequence = [];

        for ($i = 0; $i <= $days; $i++) {
            $sequence[] = "SELECT $i AS seq";
        }

        $sequenceSql = implode(" UNION ALL ", $sequence);

        $query = "

                    SELECT 
                        empdetails.first_name,
                        empdetails.last_name,

                        eot.id,
                        empdetails.emp_pkey,

                        dates.att_date,

                        CASE 
                            WHEN termination.emp_fkey IS NULL 
                                OR termination.last_approved_working_date >= dates.att_date
                            THEN eot.att_in_time
                            ELSE ''
                        END AS att_in_time,
                        CASE 
                            WHEN termination.emp_fkey IS NULL 
                                OR termination.last_approved_working_date >= dates.att_date
                            THEN eot.att_out_time
                            ELSE ''
                        END AS att_out_time,

                        IFNULL(edt.duration,0) AS duration,

                        eot.min_bfr_on_dutty_cal_ot,
                        eot.min_aftr_off_dutty_cal_ot,
                        eot.work_time_day_off_cal_ot,
                        CASE 
                            WHEN termination.emp_fkey IS NULL 
                                OR termination.last_approved_working_date >= dates.att_date
                            THEN eot.ot_duration
                            ELSE ''
                        END AS ot_duration,

                        edt.present,
                        edt.weekoff,
                        edt.leaves,
                        edt.holiday,
                        edt.others,

                        CASE
                            WHEN EXISTS (
                                SELECT 1
                                FROM holidays h
                                WHERE h.HOLIDAY_GROUP_ID = emp.HOLIDAY_GROUP_ID
                                AND h.HOLIDAYDATE = dates.att_date
                            )
                            THEN 'HO'

                            WHEN (

                                (
                                    WEEKDAY(dates.att_date) = 0
                                    AND (
                                        wd.monday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'monday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                                OR (
                                    WEEKDAY(dates.att_date) = 1
                                    AND (
                                        wd.tuesday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'tuesday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                                OR (
                                    WEEKDAY(dates.att_date) = 2
                                    AND (
                                        wd.wednesday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'wednesday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                                OR (
                                    WEEKDAY(dates.att_date) = 3
                                    AND (
                                        wd.thursday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'thursday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                                OR (
                                    WEEKDAY(dates.att_date) = 4
                                    AND (
                                        wd.friday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'friday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                                OR (
                                    WEEKDAY(dates.att_date) = 5
                                    AND (
                                        wd.saturday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'saturday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                                OR (
                                    WEEKDAY(dates.att_date) = 6
                                    AND (
                                        wd.sunday = 'N'
                                        OR EXISTS (
                                            SELECT 1
                                            FROM shift_exceptions se
                                            WHERE se.shift_id = wd.day_time_seq
                                            AND se.week_off = 'Y'
                                            AND se.status = 1
                                            AND LCASE(se.ex_week_day) = 'sunday'
                                            AND CAST(se.ex_week AS UNSIGNED) =
                                                FLOOR((DAYOFMONTH(dates.att_date)-1)/7)+1
                                        )
                                    )
                                )

                            )
                            THEN 'WO'

                            WHEN (
                                (WEEKDAY(dates.att_date) = 0 AND wd.monday_f = 'Y')
                                OR (WEEKDAY(dates.att_date) = 1 AND wd.tuesday_f = 'Y')
                                OR (WEEKDAY(dates.att_date) = 2 AND wd.wednesday_f = 'Y')
                                OR (WEEKDAY(dates.att_date) = 3 AND wd.thursday_f = 'Y')
                                OR (WEEKDAY(dates.att_date) = 4 AND wd.friday_f = 'Y')
                                OR (WEEKDAY(dates.att_date) = 5 AND wd.saturday_f = 'Y')
                                OR (WEEKDAY(dates.att_date) = 6 AND wd.sunday_f = 'Y')
                            )
                            THEN '/WO'

                            ELSE ''

                        END AS off_type,

                        CASE 
                            WHEN termination.emp_fkey IS NULL 
                                OR termination.last_approved_working_date >= dates.att_date
                            THEN eot.set_duration
                            ELSE ''
                        END AS set_duration,
                        eot.remarks,
                        eot.is_manual,

                        edt.yearmonth,

                        eot.created_by,
                        eot.creation_date,
                        eot.isdelete,

                        wd.minuts_calc_perday,
                        emp.emp_fkey

                    FROM
                    (
                        SELECT 
                            DATE('$cycleStart') + INTERVAL seq DAY AS att_date
                        FROM
                        (
                            $sequenceSql
                        ) seq_table
                    ) dates

                    LEFT JOIN emp_details AS empdetails
                        ON empdetails.emp_pkey = '$emppkey'

                    LEFT JOIN emp_proff AS emp
                        ON emp.emp_fkey = empdetails.emp_pkey

                    LEFT JOIN working_day_time_procedures AS wd
                        ON wd.day_time_seq = emp.day_time_seq

                    LEFT JOIN emp_ot_timeattandance eot
                        ON eot.emp_pkey = empdetails.emp_pkey
                        AND eot.att_date = dates.att_date

                    LEFT JOIN emp_detail_timeattandance edt
                        ON edt.emp_pkey = empdetails.emp_pkey
                        AND edt.att_date = dates.att_date

                    LEFT JOIN termination on (termination.emp_fkey = empdetails.emp_pkey AND termination.status = 1)

                    WHERE dates.att_date >= emp.joining_date

                    ORDER BY dates.att_date
                    ";

        $attendances = $this->EmployeeDetails->query($query);

        $ot_master = $this->EmployeeDetails->query("SELECT 
                                                IFNULL(SUM(set_duration), 0) AS new_ot_duration,
                                                set_duration

                                            FROM emp_ot_master
                                            WHERE emp_fkey = '$emppkey'
                                            AND month = '$yearmonth';");
        $new_ot_duration = isset($ot_master[0][0]['new_ot_duration']) ? $ot_master[0][0]['new_ot_duration'] : '';
        $set_ot_duration = isset($ot_master[0]['emp_ot_master']['set_duration']) ? $ot_master[0]['emp_ot_master']['set_duration'] : '';
        $this->set('new_ot_duration', $new_ot_duration);
        $this->set('set_ot_duration', $set_ot_duration);
        // End

        $this->set('attendances', $attendances);
        $this->set('month', $yearmonth);
        $this->set('emp', $emppkey);
        $this->set('mode', 'pdf');
        $view = new View($this, false);
        //    debug($attendances);
        //    debug($emppkey);
        //    debug($yearmonth);
        //                    echo "select empdetails.first_name,empdetails.last_name,emp_ot_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey from emp_ot_timeattandance left join emp_details as empdetails on(empdetails.emp_pkey = emp_ot_timeattandance.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where emp_ot_timeattandance.emp_pkey = '$emppey' and emp_ot_timeattandance.yearmonth = '$yearmonth' ";
        $view_output = $view->render('subtablegenpdf');
        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

        $html2pdf = new HTML2PDF('L', 'A2', 'fr');

        // Edited by Akshay on 22-5-2026
        // $html2pdf = new HTML2PDF(
        //     'L',
        //     array(600, 297),
        //     'en'
        // );
        // End

        // $html2pdf = new HTML2PDF('L', array(600,297), 'en');
        //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
        //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($view_output);
        $html2pdf->Output(date('Y-F', strtotime($yearmonth)) . '_Monthly_overtime_report_.pdf', 'D');
        $this->render('subtablegenpdf');
    }

    public function approves()
    {
        // Edited by Akshay on 28-11-2025
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $user_id = $this->Session->read('login_user_id');

        // Read POST data
        $month = $this->request->data['month']; // YYYY-MM
        $employees = $this->request->data['employees']; // array of objects

        // Convert month to YYYY-MM-01
        $month2 = $month . '-01';


        foreach ($employees as $emp) {

            $emp_fkey = $emp['emp_fkey'];

            $set_duration_min = $emp['set_duration_min'];  // new value to update

            // Edited by Akshay on 27-3-2026
            $check = $this->EmployeeDetails->query("
                                                    SELECT emp_ot_master_pkey 
                                                    FROM emp_ot_master 
                                                    WHERE emp_fkey = '$emp_fkey'
                                                    AND month = '$month2'
                                                    LIMIT 1
                                                ");
            // End

            if (!empty($check)) {

                if ($set_duration_min != '') {
                    // 1️⃣ Update emp_ot_master set_duration first
                    $this->EmployeeDetails->query("
                                                UPDATE emp_ot_master 
                                                SET set_duration = $set_duration_min
                                                WHERE emp_fkey = '$emp_fkey' 
                                                AND month = '$month2'
                                            ");
                }

                // 2️⃣ Then update emp_ot_timeattandance
                // $this->EmployeeDetails->query("
                //                             UPDATE emp_ot_timeattandance 
                //                             SET isdelete = 'N' 
                //                             WHERE emp_pkey = '$emp_fkey' 
                //                             AND yearmonth = '$month2'
                //                         ");
            } else {
                $this->EmployeeDetails->query(
                    "INSERT INTO emp_ot_master (emp_fkey, emp_name, month, total_duration, is_verified)
                        SELECT ?, CONCAT_WS(' ', first_name, last_name), ?, ?, 'Y'
                        FROM emp_details
                        WHERE emp_pkey = ?",
                    array($emp_fkey, $month2, $set_duration_min, $emp_fkey)
                );
            }


            // 3️⃣ Then update emp_ot_master is_verified
            $this->EmployeeDetails->query("
            UPDATE emp_ot_master 
            SET is_verified = 'Y',
                set_duration = CASE 
                    WHEN set_duration IS NULL THEN total_duration
                    ELSE set_duration
                END
            WHERE emp_fkey = '$emp_fkey' 
            AND month = '$month2'
        ");

            $this->EmployeeDetails->query("CALL calculate_ot_allowance_prc($emp_fkey, '$month', '$user_id');"); // Edited by Akshay on 10-12-2025
        }

        echo json_encode(['status' => 'success']);

        exit;
        // End
    }


    public function Setvalue()
    {
        $this->autoRender = false;
        $arr_data = $this->request->data;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $emp = $arr_data['emp_pkey'];
        $set = $arr_data['set'];
        $month = $arr_data['month'];
        //$this->EmployeeDetails->query("update emp_ot_timeattandance set isdelete ='N' where emp_pkey = '$emp' and yearmonth = '$month' ");
        $this->EmployeeDetails->query("update emp_ot_master set set_duration = '$set' where emp_fkey = '$emp' and month = '$month' ");
    }

    public function remarks()
    {
        $this->autoRender = false;
        $arr_data = $this->request->data;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $emp = $arr_data['emp_pkey'];
        $set = $arr_data['set'];
        $month = $arr_data['month'];
        //$this->EmployeeDetails->query("update emp_ot_timeattandance set isdelete ='N' where emp_pkey = '$emp' and yearmonth = '$month' ");
        $this->EmployeeDetails->query("update emp_ot_master set remarks = '$set' where emp_fkey = '$emp' and month = '$month' ");
    }
    public function counting($month = '', $emp_pkey = 0)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_exists = $this->EmployeeDetails->query("select count(*) AS COUNT from attendance_register where emp_fkey in ($emp_pkey) and  month_year = '$month' and isdelete ='Y'");
        $count = isset($arr_exists['0']['0']['COUNT']) ? $arr_exists['0']['0']['COUNT'] : 0;
        //approves($emp_pkey,$month);
        echo json_encode(array('count' => $count));
    }

    //Edited by Askhay on 22-3-2024
    public function remove()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        $company_code = strtoupper($this->Session->read('company_code'));
        $month = $arr_data['month'];
        $month = date('Y-m', strtotime($month));
        $message = array();
        $success = false;
        //Edited by Akshay on 13-8-2024
        $date = new DateTime("now", new DateTimeZone('Asia/Kolkata'));
        $currentDateTime = $date->format('Y-m-d H:i:s');
        $currentDate = $date->format('Y-m-d');
        //End

        $user_id = $this->Session->read('login_user_id'); // Edited by
        try {
            foreach ($arr_data['pkeys'] as $pkey) {
                //Edited by Akshay on 1-4-2024
                $arr_emp = $this->EmployeeDetails->query("SELECT emp_fkey FROM emp_ot_master WHERE emp_ot_master_pkey = '$pkey'");
                $emp_pkey = isset($arr_emp[0]['emp_ot_master']['emp_fkey']) ? $arr_emp[0]['emp_ot_master']['emp_fkey'] : 0;
                if ($company_code  == 'HRBL' || $company_code  == 'DEMO' || $company_code  == 'GEDE') {
                    $count = 0;
                } else {
                    $count = $this->EmployeeDetails->query("SELECT COUNT(*) AS count FROM payroll_master WHERE emp_fkey = '$emp_pkey' AND month_year = '$month' AND action IN ('Approved', 'Processed')");
                    $count = isset($count[0][0]['count']) ? $count[0][0]['count'] : 0; //Edited by Akshay on 13-8-2024
                }
                // if ($count[0][0]['count'] == 0) {
                if ($count == 0) { //Edited by Akshay on 13-8-2024
                    $result = $this->EmployeeDetails->query("UPDATE emp_ot_master SET is_verified = 'N' WHERE emp_ot_master_pkey = '$pkey' AND is_verified = 'Y'");


                    // Edited by Akshay on 10-12-2025
                    $arr_ot_component = $this->EmployeeDetails->query("
                                                                        SELECT wdt.otcomponents
                                                                        FROM emp_ot_master eot
                                                                        LEFT JOIN emp_proff ep ON eot.emp_fkey = ep.emp_fkey
                                                                        LEFT JOIN working_day_time_procedures wdt ON ep.day_time_seq = wdt.day_time_seq
                                                                        WHERE eot.emp_ot_master_pkey = '$pkey'
                                                                        AND eot.is_verified = 'N'
                                                                        AND wdt.otcomponents IS NOT NULL
                                                                        AND TRIM(COALESCE(wdt.otcomponents, '')) != ''

                                                                        AND NOT EXISTS (
                                                                                SELECT 1
                                                                                FROM emp_ot_master eot2
                                                                                LEFT JOIN emp_proff ep2 ON eot2.emp_fkey = ep2.emp_fkey
                                                                                LEFT JOIN working_day_time_procedures wdt2 ON ep2.day_time_seq = wdt2.day_time_seq
                                                                                WHERE eot2.emp_fkey = eot.emp_fkey
                                                                                AND DATE_FORMAT(eot2.month, '%Y-%m') = DATE_FORMAT(eot.month, '%Y-%m')
                                                                                AND eot2.is_verified = 'Y'
                                                                                AND wdt2.otcomponents = wdt.otcomponents
                                                                        )
                                                                    ");

                    $ot_component = isset($arr_ot_component[0]['wdt']['otcomponents']) ? $arr_ot_component[0]['wdt']['otcomponents'] : 0;

                    if ($ot_component != 0) {
                        $this->EmployeeDetails->query("UPDATE emp_calc_variable_components
                                                            SET end_date_effective = NOW(), modified_by = '$user_id'
                                                            WHERE emp_fkey = $emp_pkey
                                                            AND month_year = '$month'
                                                            AND salary_head_item_fkey = '$ot_component'
                                                            AND end_date_effective IS NULL;");
                    }
                    // End

                    $success = true;
                } else {
                    $message[] = 'Payroll already processed for the month.';
                }
            }
            echo json_encode(array('success' => $success, 'message' => $message));
        } catch (Exception $e) {
            debug($e);
            echo json_encode(array('success' => false, 'message' => 'Error'));
        }
    }
    public function removenew()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        $company_code = strtoupper($this->Session->read('company_code'));
        $month = $arr_data['month'];
        $month = date('Y-m', strtotime($month));
        $message = array();
        $success = false;
        //Edited by Akshay on 12-8-2024
        $user_id = $this->Session->read('login_user_id');
        $date = new DateTime("now", new DateTimeZone('Asia/Kolkata'));
        $currentDateTime = $date->format('Y-m-d H:i:s');
        $currentDate = $date->format('Y-m-d');
        //End
        try {
            foreach ($arr_data['pkeys'] as $pkey) {
                //Edited by Akshay on 1-4-2024
                $arr_emp = $this->EmployeeDetails->query("SELECT emp_fkey FROM emp_ot_master WHERE emp_ot_master_pkey = '$pkey'");
                $emp_pkey = isset($arr_emp[0]['emp_ot_master']['emp_fkey']) ? $arr_emp[0]['emp_ot_master']['emp_fkey'] : 0;
                if ($company_code  == 'HRBL' || $company_code  == 'DEMO' || $company_code  == 'GEDE') {
                    $count = 0;
                } else {
                    $count = $this->EmployeeDetails->query("SELECT COUNT(*) AS count FROM payroll_master WHERE emp_fkey = '$emp_pkey' AND month_year = '$month' AND action IN ('Approved', 'Processed')");
                    $count = isset($count[0][0]['count']) ? $count[0][0]['count'] : 0; //Edited by Akshay on 13-8-2024
                }
                // if ($count[0][0]['count'] == 0) {
                if ($count == 0) { //Edited by Akshay on 13-8-2024
                    // $result = $this->EmployeeDetails->query("UPDATE emp_ot_process SET process = '', process_date = NOW() WHERE emp_ot_master_fkey = '$pkey' AND process = 'processed'");
                    //Edited by Akshay on 12-8-2024
                    $result = $this->EmployeeDetails->query("UPDATE emp_ot_process SET process = '', process_date = '$currentDateTime', process_by = '$user_id' WHERE emp_ot_master_fkey = '$pkey' AND process = 'processed'");
                    //End
                    $success = true;
                } else {
                    $message[] = 'Payroll already processed for the month.';
                }
            }
            echo json_encode(array('success' => $success, 'message' => $message));
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'Error'));
        }
    }
    public function otprocess()
    {
        try {
            $this->autoRender = false;
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_data = $this->request->data;
            $yearmonth = $arr_data['month'];
            $month = $arr_data['month'];
            $month = date('Y-m', strtotime($month));
            $message = array();
            $success = false;
            $company_code = strtoupper($this->Session->read('company_code'));
            $date = new DateTime("now", new DateTimeZone('Asia/Kolkata'));
            $currentDateTime = $date->format('Y-m-d H:i:s');
            $user_id = $this->Session->read('login_user_id');
            // try {
            foreach ($arr_data['pkeys'] as $pkey) {
                //Edited by Akshay on 1-4-2024
                $arr_emp = $this->EmployeeDetails->query("SELECT emp_fkey FROM emp_ot_master WHERE emp_ot_master_pkey = '$pkey'");
                $emp_pkey = isset($arr_emp[0]['emp_ot_master']['emp_fkey']) ? $arr_emp[0]['emp_ot_master']['emp_fkey'] : 0;
                if ($company_code  == 'HRBL' || $company_code  == 'DEMO' || $company_code  == 'GEDE') {
                    $count = 0;
                } else {
                    $count = $this->EmployeeDetails->query("SELECT COUNT(*) AS count FROM payroll_master WHERE emp_fkey = '$emp_pkey' AND month_year = '$month' AND action IN ('Approved', 'Processed')");
                    $count = isset($count[0][0]['count']) ? $count[0][0]['count'] : 0; //Edited by Akshay on 13-8-2024
                }
                // $arr_amount = $this->EmployeeDetails->query("select sum(ifnull(set_duration,0)) as vot_duration  from emp_ot_master
                // where emp_fkey = $emp_pkey and DATE_FORMAT(month,'%Y-%m')='$month' and is_verified='Y'");

                //Edited by Akshay on 12-8-2024
                $arr_amount = $this->EmployeeDetails->query("select sum(ifnull(set_duration,0)) as vot_duration  from emp_ot_master
                where emp_fkey = $emp_pkey and DATE_FORMAT(month,'%Y-%m')='$month' and is_verified='Y'");
                //End

                $arr_structure_det_value = $this->EmployeeDetails->query("select emp_fkey,structure_det_value,remarks from emp_salary_structure where salary_head_item_desc = 'Overtime Allowance(OT)' and emp_fkey= $emp_pkey AND end_date_effective IS NULL");
                // debug("select emp_fkey,structure_det_value,remarks from emp_salary_structure where salary_head_item_fkey= 37 and emp_fkey= $emp_pkey AND end_date_effective IS NULL");exit;

                if ($arr_amount[0][0]['vot_duration'] != null) {
                    $ot_amount = (isset($arr_structure_det_value[0]['emp_salary_structure']['structure_det_value']) ? $arr_structure_det_value[0]['emp_salary_structure']['structure_det_value'] : 0) * ($arr_amount[0][0]['vot_duration'] / 60);
                } else {
                    $ot_amount = 0;
                }

                if (!empty($arr_structure_det_value[0]['emp_salary_structure']['remarks'])) {
                    $ot_rate_formula = $arr_structure_det_value[0]['emp_salary_structure']['remarks'];
                } else {
                    $ot_rate_formula = '';
                }
                $ot_rate = isset($arr_structure_det_value[0]['emp_salary_structure']['structure_det_value']) ? $arr_structure_det_value[0]['emp_salary_structure']['structure_det_value'] : 0;

                // if ($count[0][0]['count'] == 0) {
                if ($count == 0) { //Edited by Akshay on 13-8-2024
                    $arr_value = $this->EmployeeDetails->query("select COUNT(*) AS count  from emp_ot_process where emp_fkey= $emp_pkey and month = '$yearmonth'");
                    // $result = $this->EmployeeDetails->query("UPDATE emp_ot_master SET is_verified = 'N' WHERE emp_ot_master_pkey = '$pkey' AND is_verified = 'Y'");

                    if ($arr_value[0][0]['count'] == 0) {
                        $result = $this->EmployeeDetails->query("
    INSERT INTO emp_ot_process (
        emp_ot_master_fkey, 
        ot_rate, 
        ot_amount, 
        end_date_effective, 
        created_by, 
        created_date, 
        process, 
        process_date, 
        process_by,
        emp_fkey,
        month,
        ot_rate_formula
    ) VALUES (
        '$pkey', 
        '$ot_rate', 
        '$ot_amount', 
        '', 
        '$user_id',
        '$currentDateTime', 
        'processed', 
        '', 
        '',
        '$emp_pkey',
        '$yearmonth',
        '$ot_rate_formula'   
    )
 ");
                    } else {
                        $result = $this->EmployeeDetails->query("UPDATE emp_ot_process SET process = 'processed',process_date = '$currentDateTime', ot_rate = '$ot_rate', ot_amount = '$ot_amount' WHERE emp_ot_master_fkey = '$pkey' AND emp_fkey = '$emp_pkey'");
                    }


                    $success = true;
                } else {
                    $message[] = 'Payroll already processed for the month.';
                }
            }
        } catch (Exception $e) {
            debug($e);
        }
        echo json_encode(array('success' => $success, 'message' => $message));
        //        } catch (Exception $e) {
        //            echo json_encode(array('success' => false, 'message'=>'Error'));
        //        }
    }
    public function process($month = '', $emp_pkey = 0, $tab = 1, $branch = '0') //Edited by Akshay on 26-7-2024
    {
        try {


            $this->autoRender = false;
            $this->DbConfig->useDbConfig = $this->Session->read('ds');
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $company_code = $this->Session->read('company_code');
            $yearmonth = $month;
            //  debug($yearmonth);

            //Edited by Akshay on 26-7-2024
            $this->set("tab", $tab);
            //End
            if ($emp_pkey) {
                $condition = "emp_ot_master.emp_fkey = '$emp_pkey' and ";
            } else {
                $condition = '';
            }
            if ($branch != '0') {
                if ($condition == '') {
                    $condition = $condition . "emp_details.branch_code = '$branch' and ";
                } else {
                    $condition = $condition . "emp_details.branch_code = '$branch' and";
                }
            }
            $emp = isset($emp_pkey) ? $emp_pkey : NUll;
            //        $months = date("Y-m", strtotime($yearmonth));
            //        $arr_exists = $this->EmployeeDetails->query("select count(*) AS COUNT from attendance_register where $condition month_year = '$months' and isdelete ='N'");
            //        $count = isset($arr_exists['0']['0']['COUNT'])?$arr_exists['0']['0']['COUNT']:0;
            //        $this->set("count",$count);
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $attendancesnotverified = $this->EmployeeDetails->query("select * from emp_ot_master 
                                                                    LEFT JOIN emp_details ON emp_details.emp_pkey = emp_ot_master.emp_fkey
                                                                    where $condition month = '$yearmonth' and is_verified = 'N' ");
            // $attendancesverified = $this->EmployeeDetails->query("select * from emp_ot_master where $condition month = '$yearmonth' and is_verified = 'Y' ");

            if ($company_code  == 'HRBL' || $company_code  == 'GEDE') { //Edited by Akshay on 13-8-2024
                $attendancesverified = $this->EmployeeDetails->query("
                SELECT emp_ot_master.*
                FROM emp_ot_master
                 LEFT JOIN emp_ot_process ON emp_ot_process.emp_ot_master_fkey = emp_ot_master.emp_ot_master_pkey
                 LEFT JOIN emp_details ON emp_details.emp_pkey = emp_ot_master.emp_fkey
                WHERE emp_ot_master.is_verified = 'Y'
                  AND emp_ot_master.month = '$yearmonth'
                  AND $condition
                  (emp_ot_process.emp_ot_master_fkey IS NULL OR 
                         (emp_ot_process.process != 'processed'))
            ");

                $attendanceprocessed = $this->EmployeeDetails->query("
                SELECT emp_ot_master.*, emp_ot_process.ot_rate, emp_ot_process.ot_amount
                FROM emp_ot_master
                 LEFT JOIN emp_ot_process ON emp_ot_process.emp_ot_master_fkey = emp_ot_master.emp_ot_master_pkey
                 LEFT JOIN emp_details ON emp_details.emp_pkey = emp_ot_master.emp_fkey
                WHERE emp_ot_master.is_verified = 'Y'
                  AND emp_ot_master.month = '$yearmonth'
                  AND $condition 
                   emp_ot_process.process = 'processed' 
            ");

                $this->set("attendancesverified", $attendancesverified);
                $this->set("attendanceprocessed", $attendanceprocessed);
            }
            //End
            // debug($attendanceprocessed);
            $this->set("company_code", $company_code);
            $this->set("attendancesnotverified", $attendancesnotverified);

            $this->render('register');
        } catch (Exception $e) {
            debug($e);
        }
    }

    // Edited by Akshay on 24-11-2025
    public function getDurationRegister()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $month = $this->request->data['month'];
        $yearmonth = $month . '-01';
        $emp = $this->request->data['emp'];
        $branch = $this->request->data['branch'];

        $emp = ($emp == 0 || $emp == '0') ? '' : $emp;
        $branch = ($branch === 0 || $branch === '0') ? '' : $branch;



        $result[0][0] = ["duration" => "OK"];

        try {
            // Edited by Akshay on 26-3-2026
            if ($emp != '') {
                // Insert or update emp_ot_master
                // Check if verified record exists
                $verified = $this->EmployeeDetails->query("
                                                            SELECT 1 
                                                            FROM emp_ot_master 
                                                            WHERE emp_fkey = '$emp'
                                                            AND month = '$yearmonth'
                                                            AND is_verified = 'Y'
                                                        ");

                // If verified exists → do nothing
                if (empty($verified)) {

                    $arr_branch_code = $this->EmployeeDetails->query("
                                                                        SELECT 
                                                                            branch_code, 
                                                                            CONCAT(IFNULL(first_name,''), ' ', IFNULL(last_name,'')) AS emp_name
                                                                        FROM emp_details 
                                                                        WHERE emp_pkey = '$emp'
                                                                    ");
                    if ($branch == '') {
                        $branch = isset($arr_branch_code[0]['emp_details']['branch_code']) ? $arr_branch_code[0]['emp_details']['branch_code'] : '';
                    }

                    
                                            // Edited by Akshay on 27-5-2026
                        $att_startdate = $this->EmployeeDetails->query("
                        SELECT att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 1) AS monthly_att_fromdate
                    ");

                        $att_enddate = $this->EmployeeDetails->query("
                        SELECT att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 2) AS monthly_att_todate
                    ");

                        $start_date = $att_startdate[0][0]['monthly_att_fromdate'];
                        $end_date   = $att_enddate[0][0]['monthly_att_todate'];

                        $start = new DateTime($start_date);
                        $end   = new DateTime($end_date);

                        // Include end date
                        $end->modify('+1 day');

                        $interval = new DateInterval('P1D');
                        $daterange = new DatePeriod($start, $interval, $end);

                        foreach ($daterange as $date_obj) {

                            $date = $date_obj->format('Y-m-d');

                            $this->EmployeeDetails->query("
                            SELECT ot_duration_register_date('$date', $emp, '$branch')
                        ");
                        }
                        // End

                    $empName = isset($arr_branch_code[0][0]['emp_name'])
                        ? trim($arr_branch_code[0][0]['emp_name'])
                        : '';

                    $yearmonth_ym = date('Y-m', strtotime($yearmonth));
                    $arr_verified_count = $this->EmployeeDetails->query("SELECT COUNT(*) as veified_count FROM attendance_register WHERE emp_fkey = '$emp'  AND month_year = '$yearmonth_ym ' AND isdelete = 'N' ;");
                    $veified_count = isset($arr_verified_count[0][0]['veified_count']) ? $arr_verified_count[0][0]['veified_count'] : 0;

                    if ($veified_count == 0) {
                        $result[0][0] = ["duration" => "OK"];
                        echo json_encode($result);
                        exit;
                    }

                    // Edited by Akshay on 26-3-2026
                    $month1 = $yearmonth_ym . '-01';

                    $att_range = $this->EmployeeDetails->query("
                    SELECT 
                        att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) AS start_date,
                        att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) AS end_date
                ");

                    $start_date = $att_range[0][0]['start_date'];
                    $end_date   = $att_range[0][0]['end_date'];
                    // End





                    // Calculate total duration
                    $total = $this->EmployeeDetails->query("
                                                                    SELECT 
                                                                        SUM(
                                                                            CASE 
                                                                                WHEN IFNULL(is_manual,'N') = 'Y' 
                                                                                THEN IFNULL(set_duration,0)
                                                                                ELSE IFNULL(ot_duration,0)
                                                                            END
                                                                        ) AS total_duration
                                                                    FROM emp_ot_timeattandance
                                                                    LEFT JOIN emp_proff ep ON ep.emp_fkey = emp_ot_timeattandance.emp_pkey
                                                                    LEFT JOIN termination on (termination.emp_fkey = emp_ot_timeattandance.emp_pkey AND termination.status = 1)
                                                                    WHERE emp_pkey = '$emp'
                                                                    -- AND yearmonth = '$yearmonth'
                                                                    AND att_date BETWEEN '$start_date' AND '$end_date'
                                                                    AND (isdelete = 'N' or isdelete = 'Y')
                                                                    AND emp_ot_timeattandance.att_date >=  ep.joining_date
                                                                    AND (
                                                                        termination.emp_fkey IS NULL
                                                                        OR termination.last_approved_working_date >= emp_ot_timeattandance.att_date
                                                                    )
                                                                ");

                    $total_duration = isset($total[0][0]['total_duration']) ? $total[0][0]['total_duration'] : 0;

                    if ($total_duration >= 0) {
                        // Check if row exists
                        $exists = $this->EmployeeDetails->query("
                                                                SELECT emp_ot_master_pkey
                                                                FROM emp_ot_master
                                                                WHERE emp_fkey = '$emp'
                                                                AND month = '$yearmonth'
                                                            ");

                        if (!empty($exists)) {

                            // Update if exists
                            $this->EmployeeDetails->query("
                                                        UPDATE emp_ot_master 
                                                        SET total_duration = '$total_duration',
                                                        set_duration = NULL
                                                        WHERE emp_fkey = '$emp'
                                                        AND month = '$yearmonth'
                                                        AND is_verified != 'Y'
                                                    ");
                        } else {

                            // Insert if not exists
                            $this->EmployeeDetails->query("
                                                        INSERT INTO emp_ot_master 
                                                        (emp_fkey, emp_name, month, total_duration) 
                                                        VALUES 
                                                        ('$emp', '$empName', '$yearmonth', '$total_duration')
                                                    ");
                        }
                    }
                }
                // End
            } else {

                $arr_end_date = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 2) end_date");
                $end_date = $arr_end_date[0][0]['end_date'];

                $branch_condition = '';
                if ($branch != '') {
                    $branch_condition = "AND ed.branch_code = '$branch' ";
                }

                $arr_emp_details = $this->EmployeeDetails->query("
                                                                    SELECT 
                                                                        ed.emp_pkey, 
                                                                        ed.branch_code, 
                                                                        CONCAT(IFNULL(ed.first_name,''), ' ', IFNULL(ed.last_name,'')) AS emp_name
                                                                    FROM emp_details ed
                                                                    INNER JOIN emp_proff ep 
                                                                        ON ed.emp_pkey = ep.emp_fkey
                                                                    LEFT JOIN working_day_time_procedures wdtp
                                                                        ON wdtp.day_time_seq = ep.day_time_seq
                                                                        AND wdtp.active = 1
                                                                    LEFT JOIN emp_ot_master eom ON (eom.emp_fkey = ed.emp_pkey AND eom.month = '$yearmonth')
                                                                    WHERE 
                                                                        ed.status = '1'
                                                                        AND ep.joining_date <= '$end_date'
                                                                        $branch_condition
                                               
                                                                        AND (eom.is_verified IS NULL OR eom.is_verified = 'N')
                                                                    ORDER BY ed.first_name ASC
                                                                ");

                foreach ($arr_emp_details as $emp_details) {
                    $emp = $emp_details['ed']['emp_pkey'];
                    $branch = $emp_details['ed']['branch_code'];
                    $empName = trim($emp_details[0]['emp_name']);

                    $yearmonth_ym = date('Y-m', strtotime($yearmonth));
                    $arr_verified_count = $this->EmployeeDetails->query("SELECT COUNT(*) as veified_count FROM attendance_register WHERE emp_fkey = '$emp' AND month_year = '$yearmonth_ym ' AND isdelete = 'N' ;");
                    $veified_count = isset($arr_verified_count[0][0]['veified_count']) ? $arr_verified_count[0][0]['veified_count'] : 0;




                    // Edited by Akshay on 26-3-2026
                    $month1 = $yearmonth_ym . '-01';


                    $att_range = $this->EmployeeDetails->query("
                                                                    SELECT 
                                                                        att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) AS start_date,
                                                                        att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) AS end_date
                                                                ");

                    $start_date = $att_range[0][0]['start_date'];
                    $end_date   = $att_range[0][0]['end_date'];

                    // End
                    // Insert or update emp_ot_master
                    // Check if verified record exists
                    $verified = $this->EmployeeDetails->query("
                                                            SELECT 1 
                                                            FROM emp_ot_master 
                                                            WHERE emp_fkey = '$emp'
                                                            AND month = '$yearmonth'
                                                            AND is_verified = 'Y'
                                                        ");


                    // If verified exists → do nothing
                    if (empty($verified)) {

                        // Edited by Akshay on 27-5-2026

                        $start = new DateTime($start_date);
                        $end   = new DateTime($end_date);

                        // Include end date
                        $end->modify('+1 day');

                        $interval = new DateInterval('P1D');
                        $daterange = new DatePeriod($start, $interval, $end);

                        foreach ($daterange as $date_obj) {

                            $date = $date_obj->format('Y-m-d');

                            $this->EmployeeDetails->query("
                            SELECT ot_duration_register_date('$date', $emp, '$branch')
                        ");
                        }
                        // End
                        if ($veified_count == 0) {
                            continue;
                        }

                        // Calculate total duration
                        $total = $this->EmployeeDetails->query("
                                                                    SELECT 
                                                                        SUM(
                                                                            CASE 
                                                                                WHEN IFNULL(is_manual,'N') = 'Y' 
                                                                                THEN IFNULL(set_duration,0)
                                                                                ELSE IFNULL(ot_duration,0)
                                                                            END
                                                                        ) AS total_duration
                                                                    FROM emp_ot_timeattandance
                                                                    LEFT JOIN emp_proff ep ON ep.emp_fkey = emp_ot_timeattandance.emp_pkey
                                                                    LEFT JOIN termination on (termination.emp_fkey = emp_ot_timeattandance.emp_pkey AND termination.status = 1)
                                                                    WHERE emp_pkey = '$emp'
                                                                    -- AND yearmonth = '$yearmonth'
                                                                    AND att_date BETWEEN '$start_date' AND '$end_date'
                                                                    AND (isdelete = 'N' or isdelete = 'Y')
                                                                    AND emp_ot_timeattandance.att_date >=  ep.joining_date
                                                                    AND (
                                                                        termination.emp_fkey IS NULL
                                                                        OR termination.last_approved_working_date >= emp_ot_timeattandance.att_date
                                                                    )
                                                                ");

                        $total_duration = isset($total[0][0]['total_duration']) ? $total[0][0]['total_duration'] : 0;

                        if ($total_duration >= 0) {
                            // Check if row exists
                            $exists = $this->EmployeeDetails->query("
                                                                SELECT emp_ot_master_pkey
                                                                FROM emp_ot_master
                                                                WHERE emp_fkey = '$emp'
                                                                AND month = '$yearmonth'
                                                            ");

                            if (!empty($exists)) {

                                // Update if exists
                                $this->EmployeeDetails->query("
                                                        UPDATE emp_ot_master 
                                                        SET total_duration = '$total_duration',
                                                        set_duration = NULL
                                                        WHERE emp_fkey = '$emp'
                                                        AND month = '$yearmonth'
                                                        AND is_verified != 'Y'
                                                    ");
                            } else {

                                // Insert if not exists
                                $this->EmployeeDetails->query("
                                                        INSERT INTO emp_ot_master 
                                                        (emp_fkey, emp_name, month, total_duration) 
                                                        VALUES 
                                                        ('$emp', '$empName', '$yearmonth', '$total_duration')
                                                    ");
                            }
                        }
                    }
                }
                $result[0][0] = ["duration" => "OK"];
            }
            // End
        } catch (Exception $e) {
            debug($e);
        }
        echo json_encode($result);
        exit;
    }


    // public function getNotApprovedData()
    // {
    //     $this->DbConfig->useDbConfig = $this->Session->read('ds');
    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //     $company_code = $this->Session->read('company_code');

    //     $month = $_POST['month'];
    //     $emp_pkey = $_POST['emp'];
    //     $branch = $_POST['branch'];
    //     $status = isset($_POST['status']) ? $_POST['status'] : '';

    //     $yearmonth = $month . '-01';

    //     // Apply conditions based on parameters
    //     if (!empty($emp_pkey)) {
    //         $condition = "emp_proff.emp_fkey = '$emp_pkey' and ";
    //         $emp = $emp_pkey;
    //     } else {
    //         $condition = '';
    //         $emp = 0;
    //     }

    //     if (!empty($branch)) {
    //         $condition1 = "emp.branch_code = '$branch' and ";
    //         $branch = $branch;
    //     } else {
    //         $condition1 = '';
    //         $branch = NULL;
    //     }

    //     $condition1 .= " emp_proff.joining_date <= (select att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 2))  and ";

    //     // Handle user permissions
    //     if ($this->Session->read('emp_fkey')) {
    //         $emp_pkeys = $this->Session->read('emp_fkey');
    //         $condition_emps = "emp_proff.attr1 = '$emp_pkeys' and ";
    //     } else {
    //         $condition_emps = "";
    //     }

    //     $user_group = $this->Session->read('user_group');
    //     if ($user_group == 2) {
    //         $cur_emp_key = $this->Session->read('emp_fkey');
    //         $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");

    //         if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
    //             $branch = $payroUser[0]['emp_proff']['emp_branch'];
    //             $condition_emps = "branch_code !='$branch' and ";
    //         }
    //     }

    //     // Get search parameters from EasyUI
    //     $searchName = isset($_REQUEST['searchName']) ? $_REQUEST['searchName'] : '';
    //     $searchValue = isset($_REQUEST['searchValue']) ? $_REQUEST['searchValue'] : '';

    //     // Build search condition
    //     $searchCondition = '';
    //     if (!empty($searchValue)) {
    //         if ($searchName == 'emp_name') {
    //             $searchCondition = " AND (emp.first_name LIKE '%$searchValue%' OR emp.last_name LIKE '%$searchValue%')";
    //         } elseif ($searchName == 'remarks') {
    //             // $searchCondition = " AND emp_ot_master.remarks LIKE '%$searchValue%'";
    //         }
    //     }

    //     // Get pagination parameters from EasyUI
    //     $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    //     $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 12;
    //     $offset = ($page - 1) * $rows;

    //     // Build the base conditions for both queries
    //     $baseConditions = "$condition_emps $condition $condition1";

    //     // Edited by Akshay on 6-2-2026
    //     $attRegConditon1 = " emp_ot_master.emp_fkey NOT IN (select emp_fkey from attendance_register where month_year = '$month' and isdelete='Y') AND";
    //     $attRegConditon2 = " emp_detail_timeattandance.emp_pkey NOT IN (select emp_fkey from attendance_register where month_year = '$month' and isdelete='Y') AND";
    //     // End

    //     // Handle status filter
    //     $otOnlyCondition = "";
    //     $noOtOnlyCondition = "";

    //     if ($status == 'Eligible') {
    //         $otOnlyCondition = " AND 1=1"; // Include OT records
    //         $noOtOnlyCondition = " AND 1=0"; // Exclude non-OT records
    //     } elseif ($status == 'Not Eligible') {
    //         $otOnlyCondition = " AND 1=0"; // Exclude OT records
    //         $noOtOnlyCondition = " AND 1=1"; // Include non-OT records
    //     } else {
    //         $otOnlyCondition = " AND 1=1"; // Include both
    //         $noOtOnlyCondition = " AND 1=1"; // Include both
    //     }

    //     // Main UNION query for data
    //     $dataQuery = "
    //     SELECT * FROM (
    //         -- Employees WITH OT records (Eligible)
    //         (SELECT 
    //             emp_ot_master.emp_ot_master_pkey,
    //             emp_ot_master.emp_fkey,
    //             CONCAT_WS(' - ', CONCAT_WS(' ', emp.first_name, emp.last_name), emp_proff.emp_company_id) AS emp_name,
    //             emp_ot_master.total_duration,
    //             emp_ot_master.set_duration,
    //             emp_ot_master.remarks,
    //             (
    //                 SELECT SUM(IFNULL(duration, 0)) 
    //                 FROM emp_ot_timeattandance 
    //                 WHERE emp_ot_timeattandance.emp_pkey = emp_ot_master.emp_fkey 
    //                 AND emp_ot_timeattandance.yearmonth = '$yearmonth'
    //                 AND emp_ot_timeattandance.isdelete = 'N'
    //             ) / 60 AS total_time_worked,
    //             ROUND((emp_ot_master.total_duration / 60), 2) AS total_duration_hrs,
    //             CASE 
    //                 WHEN emp_ot_master.set_duration IS NULL THEN ROUND((emp_ot_master.total_duration / 60), 2)
    //                 ELSE ROUND((emp_ot_master.set_duration / 60), 2)
    //             END AS set_duration_hrs,
    //             CASE 
    //                 WHEN emp_ot_master.set_duration IS NULL THEN emp_ot_master.total_duration
    //                 ELSE emp_ot_master.set_duration
    //             END AS set_duration_min,
    //             'YES' as eligible,
    //             0 as total_ot_duration,
    //             emp.first_name,
    //             emp.last_name
    //         FROM emp_ot_master
    //         LEFT JOIN emp_details AS emp ON emp.emp_pkey = emp_ot_master.emp_fkey
    //         LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_ot_master.emp_fkey
    //         WHERE $baseConditions
    //             $attRegConditon1
    //             emp_ot_master.month = '$yearmonth'
    //             AND emp_ot_master.is_verified = 'N'
    //             AND emp.status = 1
    //             $searchCondition
    //             $otOnlyCondition)

    //         UNION ALL

    //         -- Employees WITHOUT OT records (Not Eligible)
    //         (SELECT 
    //             0 as emp_ot_master_pkey,
    //             emp_detail_timeattandance.emp_pkey as emp_fkey,
    //             CONCAT_WS(' - ', CONCAT_WS(' ', emp.first_name, emp.last_name), emp_proff.emp_company_id) AS emp_name,
    //             0 as total_duration,
    //             0 as set_duration,
    //             '' as remarks,
    //             SUM(IFNULL(emp_detail_timeattandance.duration, 0))/60 AS total_time_worked,
    //             0 as total_duration_hrs,
    //             0 as set_duration_hrs,
    //             0 as set_duration_min,
    //             'NO' as eligible,
    //             SUM(IFNULL(emp_detail_timeattandance.duration, 0)) as total_ot_duration,
    //             emp.first_name,
    //             emp.last_name
    //         FROM emp_detail_timeattandance 
    //         LEFT JOIN emp_details as emp ON (emp.emp_pkey = emp_detail_timeattandance.emp_pkey) 
    //         LEFT JOIN emp_proff ON (emp_proff.emp_fkey = emp_detail_timeattandance.emp_pkey) 
    //         WHERE $baseConditions
    //             $attRegConditon2
    //             emp_detail_timeattandance.yearmonth = '$yearmonth' 
    //             AND emp.status = 1 
    //             $searchCondition
    //             AND emp_detail_timeattandance.emp_pkey NOT IN (
    //                 SELECT emp_fkey FROM emp_ot_master 
    //                 WHERE month = '$yearmonth' AND (is_verified = 'N' OR is_verified = 'Y')
    //             )
    //             $noOtOnlyCondition
    //         GROUP BY emp_detail_timeattandance.emp_pkey, emp.first_name, emp.last_name, emp_proff.emp_company_id)
    //     ) as combined_data
    //    ORDER BY eligible DESC, emp_name
    //     LIMIT $offset, $rows
    //     ";

    //     // Count query using the same UNION logic
    //     $countQuery = "
    //     SELECT COUNT(*) as total FROM (
    //         -- Count employees WITH OT records
    //         (SELECT 1
    //         FROM emp_ot_master
    //         LEFT JOIN emp_details AS emp ON emp.emp_pkey = emp_ot_master.emp_fkey
    //         LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_ot_master.emp_fkey
    //         WHERE $baseConditions
    //             $attRegConditon1
    //             emp_ot_master.month = '$yearmonth'
    //             AND emp_ot_master.is_verified = 'N'
    //             AND emp.status = 1
    //             $searchCondition
    //             $otOnlyCondition)

    //         UNION ALL

    //         -- Count employees WITHOUT OT records
    //         (SELECT 1
    //         FROM emp_detail_timeattandance 
    //         LEFT JOIN emp_details as emp ON (emp.emp_pkey = emp_detail_timeattandance.emp_pkey) 
    //         LEFT JOIN emp_proff ON (emp_proff.emp_fkey = emp_detail_timeattandance.emp_pkey) 
    //         WHERE $baseConditions
    //             $attRegConditon2
    //             emp_detail_timeattandance.yearmonth = '$yearmonth' 
    //             AND emp.status = 1 
    //             $searchCondition
    //             AND emp_detail_timeattandance.emp_pkey NOT IN (
    //                 SELECT emp_fkey FROM emp_ot_master 
    //                 WHERE month = '$yearmonth' AND (is_verified = 'N' OR is_verified = 'Y')
    //             )
    //             $noOtOnlyCondition
    //         GROUP BY emp_detail_timeattandance.emp_pkey)
    //     ) as count_data
    //     ";

    //     // Execute queries
    //     $totalResult = $this->EmployeeDetails->query($countQuery);
    //     $total = isset($totalResult[0][0]['total']) ? $totalResult[0][0]['total'] : 0;

    //     $attendances = $this->EmployeeDetails->query($dataQuery);

    //     // Format data for EasyUI DataGrid
    //     $result = array();
    //     $result['total'] = $total;
    //     $result['rows'] = array();

    //     if (!empty($attendances)) {
    //         foreach ($attendances as $attendance) {
    //             $row = array(
    //                 'emp_ot_master_pkey' => $attendance['combined_data']['emp_ot_master_pkey'],
    //                 'emp_fkey' => $attendance['combined_data']['emp_fkey'],
    //                 'empname' => $attendance['combined_data']['emp_name'],
    //                 'total_duration' => isset($attendance['combined_data']['total_time_worked']) ? ROUND($attendance['combined_data']['total_time_worked'], 2) : 0,
    //                 'eligible' => $attendance['combined_data']['eligible'],
    //                 'total_ot_duration_hrs' => $attendance['combined_data']['eligible'] == 'YES' ?
    //                     (isset($attendance['combined_data']['total_duration']) ? round($attendance['combined_data']['total_duration'] / 60, 2) : 0) : 0,
    //                 'total_ot_duration' => $attendance['combined_data']['eligible'] == 'YES' ?
    //                     (isset($attendance['combined_data']['total_duration']) ? $attendance['combined_data']['total_duration'] : 0) : 0,
    //                 'set_duration' => $attendance['combined_data']['set_duration'],
    //                 'remarks' => $attendance['combined_data']['remarks'],
    //                 'total_duration_hrs' => $attendance['combined_data']['total_duration_hrs'],
    //                 'set_duration_hrs' => $attendance['combined_data']['set_duration_hrs'],
    //                 'set_duration_min' => $attendance['combined_data']['set_duration_min'],
    //                 'approved' => 'N'
    //             );
    //             $result['rows'][] = $row;
    //         }
    //     }

    //     echo json_encode($result);
    //     exit;
    // }

    // Edited by Akshay on 27-3-2026
    // public function getNotApprovedData()
    // {
    //     $this->DbConfig->useDbConfig = $this->Session->read('ds');
    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    //     $company_code = $this->Session->read('company_code');

    //     $month = $_POST['month'];
    //     $emp_pkey = $_POST['emp'];
    //     $branch = $_POST['branch'];
    //     $status = isset($_POST['status']) ? $_POST['status'] : '';

    //     $yearmonth = $month . '-01';

    //     /* ---------------------------------------------
    //         Base Conditions
    //         --------------------------------------------- */

    //     $conditions = [];

    //     if (!empty($emp_pkey) && $emp_pkey != 0) {
    //         $conditions[] = "emp.emp_pkey = '$emp_pkey'";
    //     }

    //     if (!empty($branch) && $branch != 0) {
    //         $conditions[] = "emp.branch_code = '$branch'";
    //     }

    //     // Joining date condition
    //     $conditions[] = "ep.joining_date <= (
    //         select att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 2)
    //         )";

    //     // Attendance register condition
    //     $conditions[] = "EXISTS (
    //                                     SELECT 1 
    //                                     FROM attendance_register ar
    //                                     WHERE ar.emp_fkey = emp.emp_pkey
    //                                     AND ar.month_year = '$month'
    //                                     AND ar.isdelete = 'N'
    //                                 )";

    //     // User Permission
    //     if ($this->Session->read('emp_fkey')) {
    //         $emp_pkeys = $this->Session->read('emp_fkey');
    //         $conditions[] = "ep.attr1 = '$emp_pkeys'";
    //     }

    //     // Payroll User Restriction
    //     $user_group = $this->Session->read('user_group');

    //     if ($user_group == 2) {

    //         $cur_emp_key = $this->Session->read('emp_fkey');

    //         $payroUser = $this->EmployeeDetails->query("
    //             select emp_proff.payro_priv,emp_proff.emp_branch
    //             from emp_proff 
    //             where emp_proff.emp_fkey ='$cur_emp_key'
    //         ");

    //         if (
    //             isset($payroUser[0]['emp_proff']['payro_priv']) &&
    //             $payroUser[0]['emp_proff']['payro_priv'] == '1'
    //         ) {

    //             $branch = $payroUser[0]['emp_proff']['emp_branch'];
    //             $conditions[] = "emp.branch_code != '$branch'";
    //         }
    //     }

    //     /* ---------------------------------------------
    //         Search Condition
    //         --------------------------------------------- */

    //     $searchName = isset($_REQUEST['searchName']) ? $_REQUEST['searchName'] : '';
    //     $searchValue = isset($_REQUEST['searchValue']) ? $_REQUEST['searchValue'] : '';

    //     if (!empty($searchValue)) {

    //         if ($searchName == 'emp_name') {
    //             $conditions[] = "(emp.first_name LIKE '%$searchValue%' 
    //                             OR emp.last_name LIKE '%$searchValue%')";
    //         }
    //     }

    //     /* ---------------------------------------------
    //         Status Condition
    //         --------------------------------------------- */

    //     $conditions[] = "COALESCE(eom.is_verified,'N') != 'Y'";

    //     if ($status == 'Eligible') {
    //         $conditions[] = "eom.emp_ot_master_pkey IS NOT NULL";
    //     }

    //     if ($status == 'Not Eligible') {
    //         $conditions[] = "eom.emp_ot_master_pkey IS NULL";
    //     }

    //     $where = implode(" AND ", $conditions);

    //     /* ---------------------------------------------
    //         Pagination
    //         --------------------------------------------- */

    //     $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    //     $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 12;
    //     $offset = ($page - 1) * $rows;

    //     /* ---------------------------------------------
    //         Main Query
    //         --------------------------------------------- */

    //     $dataQuery = "

    //                         SELECT 
    //                             COALESCE(eom.emp_ot_master_pkey,0) as emp_ot_master_pkey,
    //                             emp.emp_pkey as emp_fkey,

    //                             CONCAT_WS(' - ', 
    //                                 CONCAT_WS(' ', emp.first_name, emp.last_name), 
    //                                 ep.emp_company_id
    //                             ) AS emp_name,

    //                             IFNULL(eom.total_duration,0) as total_duration,
    //                             eom.set_duration,
    //                             eom.remarks,

    //                             IFNULL(att.total_time_worked,0)/60 as total_time_worked,

    //                             ROUND(IFNULL(eom.total_duration,0)/60,2) as total_duration_hrs,

    //                             ROUND(
    //                                 IFNULL(
    //                                     CASE 
    //                                         WHEN eom.set_duration IS NULL 
    //                                         THEN eom.total_duration 
    //                                         ELSE eom.set_duration 
    //                                     END
    //                                 ,0)/60,2
    //                             ) as set_duration_hrs,

    //                             CASE 
    //                                 WHEN eom.emp_ot_master_pkey IS NULL 
    //                                 THEN 'NO'
    //                                 ELSE 'YES'
    //                             END as eligible,

    //                             IFNULL(att.total_time_worked,0) as total_ot_duration,

    //                             emp.first_name,
    //                             emp.last_name

    //                         FROM emp_details emp

    //                         LEFT JOIN emp_proff ep 
    //                             ON ep.emp_fkey = emp.emp_pkey

    //                         LEFT JOIN emp_ot_master eom 
    //                             ON eom.emp_fkey = emp.emp_pkey 
    //                             AND eom.month = '$yearmonth'
    //                             -- AND eom.is_verified = 'N'

    //                         LEFT JOIN (
    //                             SELECT 
    //                                 emp_pkey,
    //                                 SUM(duration) as total_time_worked
    //                             FROM emp_detail_timeattandance
    //                             WHERE yearmonth = '$yearmonth'
    //                             GROUP BY emp_pkey
    //                         ) att 
    //                             ON att.emp_pkey = emp.emp_pkey

    //                         WHERE 
    //                             emp.status = 1
    //                             AND $where

    //                         ORDER BY eligible DESC, emp_name
    //                         LIMIT $offset, $rows

    //                         ";

    //     /* ---------------------------------------------
    //         Count Query
    //         --------------------------------------------- */

    //     $countQuery = "

    //                         SELECT COUNT(*) as total

    //                         FROM emp_details emp

    //                         LEFT JOIN emp_proff ep 
    //                             ON ep.emp_fkey = emp.emp_pkey

    //                         LEFT JOIN emp_ot_master eom 
    //                             ON eom.emp_fkey = emp.emp_pkey 
    //                             AND eom.month = '$yearmonth'
    //                             -- AND eom.is_verified = 'N'

    //                         WHERE 
    //                             emp.status = 1
    //                             AND $where

    //                         ";

    //     /* ---------------------------------------------
    //         Execute Queries
    //         --------------------------------------------- */

    //     $totalResult = $this->EmployeeDetails->query($countQuery);
    //     $total = isset($totalResult[0][0]['total']) ? $totalResult[0][0]['total'] : 0;

    //     $attendances = $this->EmployeeDetails->query($dataQuery);

    //     /* ---------------------------------------------
    //         Format Result
    //         --------------------------------------------- */

    //     $result = array();
    //     $result['total'] = $total;
    //     $result['rows'] = array();

    //     if (!empty($attendances)) {

    //         foreach ($attendances as $attendance) {

    //             $data = $attendance[0];
    //             $emp  = $attendance['emp'];
    //             $eom  = $attendance['eom'];

    //             $row = array(
    //                 'emp_ot_master_pkey' => $data['emp_ot_master_pkey'],
    //                 'emp_fkey' => $emp['emp_fkey'],
    //                 'empname' => $data['emp_name'],
    //                 'total_duration' => round($data['total_time_worked'], 2),
    //                 'eligible' => $data['eligible'],
    //                 'total_ot_duration_hrs' => round($data['total_duration'] / 60, 2),
    //                 'total_ot_duration' => $data['total_duration'],
    //                 'set_duration' => $eom['set_duration'],
    //                 'set_duration_min' => isset($eom['set_duration'])? $eom['set_duration']:'',
    //                 'remarks' => $eom['remarks'],
    //                 'total_duration_hrs' => $data['total_duration_hrs'],
    //                 'set_duration_hrs' => $data['set_duration_hrs'],
    //                 'approved' => 'N'
    //             );

    //             $result['rows'][] = $row;
    //         }
    //     }

    //     echo json_encode($result);
    //     exit;
    // }

    public function getNotApprovedData()
    {
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $company_code = $this->Session->read('company_code');

        $month    = $_POST['month'];
        $emp_pkey = $_POST['emp'];
        $branch   = $_POST['branch'];
        $status   = isset($_POST['status']) ? $_POST['status'] : '';

        $yearmonth = $month . '-01';

        /* ---------------------------------------------
            BASE CONDITIONS
        --------------------------------------------- */

        $conditions = array();

        if (!empty($emp_pkey) && $emp_pkey != 0) {
            $conditions[] = "emp.emp_pkey = '$emp_pkey'";
        }


        if (!empty($branch) && ($branch != 0 || $branch != '')) {
            $conditions[] = "emp.branch_code = '$branch'";
        }

        $conditions[] = "ep.joining_date <= (
            SELECT att_start_end_fn(DATE_FORMAT('$yearmonth','%Y-%m-01'), 2)
        )";

        $conditions[] = "EXISTS (
            SELECT 1 
            FROM attendance_register ar
            WHERE ar.emp_fkey = emp.emp_pkey
            AND ar.month_year = '$month'
            AND ar.isdelete = 'N'
        )";

        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $conditions[] = "ep.attr1 = '$emp_pkeys'";
        }

        $user_group = $this->Session->read('user_group');

        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read('emp_fkey');

            $payroUser = $this->EmployeeDetails->query("
                SELECT payro_priv, emp_branch
                FROM emp_proff 
                WHERE emp_fkey ='$cur_emp_key'
            ");

            if (
                !empty($payroUser) &&
                isset($payroUser[0]['emp_proff']['payro_priv']) &&
                $payroUser[0]['emp_proff']['payro_priv'] == '1'
            ) {

                $branch = $payroUser[0]['emp_proff']['emp_branch'];
                $conditions[] = "emp.branch_code != '$branch'";
            }
        }

        /* ---------------------------------------------
            SEARCH
        --------------------------------------------- */

        $searchName  = isset($_REQUEST['searchName']) ? $_REQUEST['searchName'] : '';
        $searchValue = isset($_REQUEST['searchValue']) ? $_REQUEST['searchValue'] : '';

        if (!empty($searchValue) && $searchName == 'emp_name') {
            $conditions[] = "(emp.first_name LIKE '%$searchValue%' 
                            OR emp.last_name LIKE '%$searchValue%')";
        }

        /* ---------------------------------------------
            COMMON CONDITIONS
        --------------------------------------------- */

        $conditions[] = "emp.status = 1";
        $conditions[] = "COALESCE(eom.is_verified,'N') != 'Y'";

        /* ---------------------------------------------
            ELIGIBILITY CONDITION
        --------------------------------------------- */

        // $eligibilityExpr = "
        //     (
        //         wdtp.min_aftr_off_dutty_cal_ot > 0
        //         OR wdtp.min_bfr_on_dutty_cal_ot > 0
        //         OR wdtp.work_time_day_off_cal_ot = 1
        //     )
        // ";

        $eligibilityExpr = "
                            (
                                (
                                    wdtp.min_aftr_off_dutty_cal_ot > 0
                                    OR wdtp.min_bfr_on_dutty_cal_ot > 0
                                    OR wdtp.work_time_day_off_cal_ot = 1
                                )

                                OR

                                EXISTS (
                                    SELECT 1
                                    FROM emp_shift_planner esp
                                    LEFT JOIN working_day_time_procedures wdt2 
                                        ON wdt2.day_time_seq = esp.shift_id
                                    WHERE esp.emp_fkey = emp.emp_pkey
                                    AND (
                                        wdt2.min_aftr_off_dutty_cal_ot > 0
                                        OR wdt2.min_bfr_on_dutty_cal_ot > 0
                                        OR wdt2.work_time_day_off_cal_ot = 1
                                    )
                                )
                            )
                            ";

        if ($status == 'Eligible') {
            $conditions[] = $eligibilityExpr;
        }

        if ($status == 'Not Eligible') {
            $conditions[] = "NOT ($eligibilityExpr)";
        }

        $where = implode(" AND ", $conditions);

        /* ---------------------------------------------
            PAGINATION
        --------------------------------------------- */

        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 10;
        $offset = ($page - 1) * $rows;

        /* ---------------------------------------------
            MAIN QUERY
        --------------------------------------------- */

        $dataQuery = "

            SELECT 
                emp.emp_pkey AS emp_fkey,

                CONCAT_WS(' - ', 
                    CONCAT_WS(' ', emp.first_name, emp.last_name), 
                    ep.emp_company_id
                ) AS emp_name,

                COALESCE(eom.emp_ot_master_pkey,0) as emp_ot_master_pkey,

                CASE 
                    WHEN $eligibilityExpr THEN 'YES'
                    ELSE 'NO'
                END AS eligible,

                IFNULL(eom.total_duration,0) as total_duration,
                eom.set_duration,
                eom.remarks,

                IFNULL(att.total_time_worked,0)/60 as total_time_worked,

                ROUND(IFNULL(eom.total_duration,0)/60,2) as total_duration_hrs,

                ROUND(
                    IFNULL(
                        CASE 
                            WHEN eom.set_duration IS NULL 
                            THEN eom.total_duration 
                            ELSE eom.set_duration 
                        END
                    ,0)/60,2
                ) as set_duration_hrs

            FROM emp_details emp

            LEFT JOIN emp_proff ep 
                ON ep.emp_fkey = emp.emp_pkey

            LEFT JOIN working_day_time_procedures wdtp
                ON wdtp.day_time_seq = ep.day_time_seq
                AND wdtp.active = 1

            LEFT JOIN emp_ot_master eom 
                ON eom.emp_fkey = emp.emp_pkey 
                AND eom.month = '$yearmonth'

            LEFT JOIN (
                SELECT 
                    emp_pkey,
                    SUM(duration) as total_time_worked
                FROM emp_detail_timeattandance
                WHERE yearmonth = '$yearmonth'
                GROUP BY emp_pkey
            ) att 
                ON att.emp_pkey = emp.emp_pkey

            WHERE $where

            ORDER BY eligible DESC, emp_name
            LIMIT $offset, $rows
        ";

        /* ---------------------------------------------
            COUNT QUERY
        --------------------------------------------- */

        $countQuery = "

            SELECT COUNT(*) as total

            FROM emp_details emp

            LEFT JOIN emp_proff ep 
                ON ep.emp_fkey = emp.emp_pkey

            LEFT JOIN working_day_time_procedures wdtp
                ON wdtp.day_time_seq = ep.day_time_seq
                AND wdtp.active = 1

            LEFT JOIN emp_ot_master eom 
                ON eom.emp_fkey = emp.emp_pkey 
                AND eom.month = '$yearmonth'

            WHERE $where
        ";

        /* ---------------------------------------------
            EXECUTION
        --------------------------------------------- */

        $totalResult = $this->EmployeeDetails->query($countQuery);

        $total = 0;
        if (!empty($totalResult) && isset($totalResult[0][0]['total'])) {
            $total = $totalResult[0][0]['total'];
        }

        $attendances = $this->EmployeeDetails->query($dataQuery);

        /* ---------------------------------------------
            FORMAT RESULT
        --------------------------------------------- */

        $result = array();
        $result['total'] = $total;
        $result['rows'] = array();

        if (!empty($attendances)) {
            foreach ($attendances as $ot_key => $attendance) {
                $emp_fkey = $attendance['emp']['emp_fkey'];
                $d = $attendance[0];
                $e = isset($attendance['eom']) ? $attendance['eom'] : array();

                // Edited by Akshay on 16-5-2026
                $arr_count = $this->EmployeeDetails->query("SELECT COUNT(*) AS count FROM emp_ot_timeattandance WHERE emp_pkey  = $emp_fkey AND yearmonth = '$yearmonth'  AND ( ot_duration IS NOT NULL OR is_manual = 'Y');");
                $count = $arr_count[0][0]['count'];
                // End

                $result['rows'][] = array(
                    // 'emp_ot_master_pkey' => $d['emp_ot_master_pkey'],
                    'emp_ot_master_pkey' => $ot_key + 1,
                    'emp_fkey' => $emp_fkey,
                    'empname' => $d['emp_name'],
                    'eligible' => $d['eligible'],

                    'total_duration' => round($d['total_time_worked'], 2),

                    'total_ot_duration_hrs' => $d['total_duration_hrs'],
                    'total_ot_duration' => $d['total_duration'],

                    'set_duration' => isset($e['set_duration']) ? $e['set_duration'] : '',
                    'set_duration_min' => isset($e['set_duration']) ? $e['set_duration'] : '',
                    'remarks' => isset($e['remarks']) ? $e['remarks'] : '',

                    'set_duration_hrs' => $d['set_duration_hrs'],

                    'approved' => 'N',

                    'daily_entries' => ($count > 0) ? 'YES' : 'NO' // Edited by Akshay on 16-5-2026
                );
            }
        }

        echo json_encode($result);
        exit;
    }
    // End

    public function getApprovedData()
    {
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');

        $month = $_POST['month'];
        $emp_pkey = $_POST['emp'];
        $branch = $_POST['branch'];

        $yearmonth = $month . '-01';

        // Apply conditions based on parameters
        if (!empty($emp_pkey)) {
            $condition = "emp_proff.emp_fkey = '$emp_pkey' and ";
            $emp = $emp_pkey;
        } else {
            $condition = '';
            $emp = 0;
        }

        if (!empty($branch)) {
            $condition1 = "emp.branch_code = '$branch' and ";
            $branch = $branch;
        } else {
            $condition1 = '';
            $branch = NULL;
        }

        // Handle user permissions
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $condition_emps = "emp_proff.attr1 = '$emp_pkeys' and ";
        } else {
            $condition_emps = "";
        }

        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read('emp_fkey');
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");

            if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
                $branch = $payroUser[0]['emp_proff']['emp_branch'];
                $condition_emps = "branch_code !='$branch' and ";
            }
        }

        // Get search parameters from EasyUI
        $searchName = isset($_REQUEST['searchName']) ? $_REQUEST['searchName'] : '';
        $searchValue = isset($_REQUEST['searchValue']) ? $_REQUEST['searchValue'] : '';

        // Build search condition
        $searchCondition = '';
        if (!empty($searchValue)) {
            if ($searchName == 'emp_name') {
                $searchCondition = " AND (emp.first_name LIKE '%$searchValue%' OR emp.last_name LIKE '%$searchValue%')";
            } elseif ($searchName == 'remarks') {
                $searchCondition = " AND emp_ot_master.remarks LIKE '%$searchValue%'";
            }
        }

        // Get pagination parameters from EasyUI
        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 12;
        $offset = ($page - 1) * $rows;

        // Build query based on company code
        if ($company_code == 'HRBL' || $company_code == 'GEDE') {
            $countQuery = "SELECT COUNT(*) as total 
                       FROM emp_ot_master 
                       LEFT JOIN emp_details AS emp ON emp.emp_pkey = emp_ot_master.emp_fkey
                       LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_ot_master.emp_fkey
                       LEFT JOIN emp_ot_process ON emp_ot_process.emp_ot_master_fkey = emp_ot_master.emp_ot_master_pkey
                       WHERE $condition_emps $condition $condition1
                       emp_ot_master.month = '$yearmonth' 
                       AND emp_ot_master.is_verified = 'Y' 
                       AND emp.status = 1
                       AND (emp_ot_process.emp_ot_master_fkey IS NULL OR 
                            (emp_ot_process.process != 'processed'))
                       $searchCondition";

            $dataQuery = "SELECT 
                        emp_ot_master.emp_ot_master_pkey,
                        emp_ot_master.emp_fkey,
                        CONCAT(emp.first_name, ' ', emp.last_name, ' - ', emp_proff.emp_company_id) as emp_name,
                        emp_ot_master.total_duration,
                        emp_ot_master.set_duration,
                        emp_ot_master.remarks,
                        ROUND((emp_ot_master.total_duration / 60), 2) as total_duration_hrs,
                        CASE 
                            WHEN emp_ot_master.set_duration IS NULL THEN ROUND((emp_ot_master.total_duration / 60), 2)
                            ELSE ROUND((emp_ot_master.set_duration / 60), 2)
                        END as approved_duration_hrs
                      FROM emp_ot_master
                      LEFT JOIN emp_details AS emp ON emp.emp_pkey = emp_ot_master.emp_fkey
                      LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_ot_master.emp_fkey
                      LEFT JOIN emp_ot_process ON emp_ot_process.emp_ot_master_fkey = emp_ot_master.emp_ot_master_pkey
                      WHERE $condition_emps $condition $condition1
                      emp_ot_master.month = '$yearmonth' 
                      AND emp_ot_master.is_verified = 'Y' 
                      AND emp.status = 1
                      AND (emp_ot_process.emp_ot_master_fkey IS NULL OR 
                           (emp_ot_process.process != 'processed'))
                      $searchCondition
                      ORDER BY emp.first_name, emp.last_name
                      LIMIT $offset, $rows";
        } else {
            $countQuery = "SELECT COUNT(*) as total 
                       FROM emp_ot_master 
                       LEFT JOIN emp_details AS emp ON emp.emp_pkey = emp_ot_master.emp_fkey
                       LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_ot_master.emp_fkey
                       WHERE $condition_emps $condition $condition1
                       emp_ot_master.month = '$yearmonth' 
                       AND emp_ot_master.is_verified = 'Y' 
                       AND emp.status = 1
                       $searchCondition";

            $dataQuery = "SELECT 
                        emp_ot_master.emp_ot_master_pkey,
                        emp_ot_master.emp_fkey,
                        CONCAT(emp.first_name, ' ', emp.last_name, ' - ', emp_proff.emp_company_id) as emp_name,
                        emp_ot_master.total_duration,
                        emp_ot_master.set_duration,
                        emp_ot_master.remarks,
                        ROUND((emp_ot_master.total_duration / 60), 2) as total_duration_hrs,
                        CASE 
                            WHEN emp_ot_master.set_duration IS NULL THEN ROUND((emp_ot_master.total_duration / 60), 2)
                            ELSE ROUND((emp_ot_master.set_duration / 60), 2)
                        END as approved_duration_hrs
                      FROM emp_ot_master
                      LEFT JOIN emp_details AS emp ON emp.emp_pkey = emp_ot_master.emp_fkey
                      LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_ot_master.emp_fkey
                      WHERE $condition_emps $condition $condition1
                      emp_ot_master.month = '$yearmonth' 
                      AND emp_ot_master.is_verified = 'Y' 
                      AND emp.status = 1
                      $searchCondition
                      ORDER BY emp.first_name, emp.last_name
                      LIMIT $offset, $rows";
        }

        $totalResult = $this->EmployeeDetails->query($countQuery);
        $total = isset($totalResult[0][0]['total']) ? $totalResult[0][0]['total'] : 0;

        $attendances = $this->EmployeeDetails->query($dataQuery);

        // Format data for EasyUI DataGrid
        $result = array();
        $result['total'] = $total;
        $result['rows'] = array();

        foreach ($attendances as $attendance) {
            $emp_fkey = $attendance['emp_ot_master']['emp_fkey'];
            // $arr_count = $this->EmployeeDetails->query("SELECT COUNT(*) AS count FROM emp_ot_timeattandance WHERE emp_pkey  = $emp_fkey AND yearmonth = '$yearmonth' ");
            $arr_count = $this->EmployeeDetails->query("SELECT COUNT(*) AS count FROM emp_ot_timeattandance WHERE emp_pkey  = $emp_fkey AND yearmonth = '$yearmonth'  AND ( ot_duration IS NOT NULL OR is_manual = 'Y');");
            $count = $arr_count[0][0]['count'];
            $row = array(
                'emp_ot_master_pkey' => $attendance['emp_ot_master']['emp_ot_master_pkey'],
                'emp_fkey' => $emp_fkey,
                'empname' => $attendance[0]['emp_name'],
                'total_duration' => isset($attendance['emp_ot_master']['total_duration']) ? round(($attendance['emp_ot_master']['total_duration'] / 60), 2) : 0,
                'approved_duration_min' => $attendance['emp_ot_master']['set_duration'],
                'approved_duration_hrs' => isset($attendance['emp_ot_master']['set_duration']) ? round(($attendance['emp_ot_master']['set_duration'] / 60), 2) : 0,
                'remarks' => $attendance['emp_ot_master']['remarks'],
                'total_duration_hrs' => $attendance[0]['total_duration_hrs'],
                'approved_duration_hrs' => $attendance[0]['approved_duration_hrs'],
                'approved' => 'Y',
                'daily_entries' => ($count > 0) ? 'YES' : 'NO'
            );
            $result['rows'][] = $row;
        }

        echo json_encode($result);
        exit;
    }

    public function getProcessedData($month = '', $emp_pkey = 0, $branch = '')
    {
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');

        // Only allow for specific companies
        if ($company_code != 'HRBL' && $company_code != 'GEDE') {
            $result = array('total' => 0, 'rows' => array());
            echo json_encode($result);
            exit;
        }

        $yearmonth = $month . '-01';

        // Apply conditions based on parameters
        if (!empty($emp_pkey)) {
            $condition = "emp_proff.emp_fkey = '$emp_pkey' and ";
            $emp = $emp_pkey;
        } else {
            $condition = '';
            $emp = 0;
        }

        if (!empty($branch)) {
            $condition1 = "emp.branch_code = '$branch' and ";
            $branch = $branch;
        } else {
            $condition1 = '';
            $branch = NULL;
        }

        // Handle user permissions
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $condition_emps = "emp_proff.attr1 = '$emp_pkeys' and ";
        } else {
            $condition_emps = "";
        }

        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read('emp_fkey');
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");

            if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
                $branch = $payroUser[0]['emp_proff']['emp_branch'];
                $condition_emps = "branch_code !='$branch' and ";
            }
        }

        // Get search parameters from EasyUI
        $searchName = isset($_REQUEST['searchName']) ? $_REQUEST['searchName'] : '';
        $searchValue = isset($_REQUEST['searchValue']) ? $_REQUEST['searchValue'] : '';

        // Build search condition
        $searchCondition = '';
        if (!empty($searchValue)) {
            if ($searchName == 'emp_name') {
                $searchCondition = " AND (emp.first_name LIKE '%$searchValue%' OR emp.last_name LIKE '%$searchValue%')";
            } elseif ($searchName == 'remarks') {
                $searchCondition = " AND emp_ot_master.remarks LIKE '%$searchValue%'";
            }
        }

        // Get pagination parameters from EasyUI
        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 12;
        $offset = ($page - 1) * $rows;

        // Get total count
        $countQuery = "SELECT COUNT(*) as total 
                   FROM emp_ot_master
                   LEFT JOIN emp_details AS emp ON emp.emp_pkey = emp_ot_master.emp_fkey
                   LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_ot_master.emp_fkey
                   LEFT JOIN emp_ot_process ON emp_ot_process.emp_ot_master_fkey = emp_ot_master.emp_ot_master_pkey
                   WHERE $condition_emps $condition $condition1 
                   emp_ot_master.month = '$yearmonth' 
                   AND is_verified = 'Y' 
                   AND emp.status = 1
                   AND emp_ot_process.process = 'processed'
                   $searchCondition";

        $totalResult = $this->EmployeeDetails->query($countQuery);
        $total = isset($totalResult[0][0]['total']) ? $totalResult[0][0]['total'] : 0;

        // Get data with pagination
        $dataQuery = "SELECT 
                    emp_ot_master.emp_ot_master_pkey,
                    emp_ot_master.emp_fkey,
                    CONCAT(emp.first_name, ' ', emp.last_name) as emp_name,
                    emp_ot_master.total_duration,
                    emp_ot_master.set_duration,
                    emp_ot_master.remarks,
                    ROUND((emp_ot_master.total_duration / 60), 2) as total_duration_hrs,
                    CASE 
                        WHEN emp_ot_master.set_duration IS NULL THEN ROUND((emp_ot_master.total_duration / 60), 2)
                        ELSE ROUND((emp_ot_master.set_duration / 60), 2)
                    END as approved_duration_hrs,
                    emp_ot_process.ot_rate,
                    emp_ot_process.ot_amount
                  FROM emp_ot_master
                  LEFT JOIN emp_details AS emp ON emp.emp_pkey = emp_ot_master.emp_fkey
                  LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_ot_master.emp_fkey
                  LEFT JOIN emp_ot_process ON emp_ot_process.emp_ot_master_fkey = emp_ot_master.emp_ot_master_pkey
                  WHERE $condition_emps $condition $condition1 
                  emp_ot_master.month = '$yearmonth' 
                  AND is_verified = 'Y' 
                  AND emp.status = 1
                  AND emp_ot_process.process = 'processed'
                  $searchCondition
                  ORDER BY emp.first_name, emp.last_name
                  LIMIT $offset, $rows";

        $attendances = $this->EmployeeDetails->query($dataQuery);

        // Format data for EasyUI DataGrid
        $result = array();
        $result['total'] = $total;
        $result['rows'] = array();

        foreach ($attendances as $attendance) {
            $row = array(
                'emp_ot_master_pkey' => $attendance['emp_ot_master']['emp_ot_master_pkey'],
                'emp_fkey' => $attendance['emp_ot_master']['emp_fkey'],
                'emp_name' => $attendance[0]['emp_name'],
                'total_duration' => $attendance['emp_ot_master']['total_duration'],
                'set_duration' => $attendance['emp_ot_master']['set_duration'],
                'remarks' => $attendance['emp_ot_master']['remarks'],
                'total_duration_hrs' => $attendance[0]['total_duration_hrs'],
                'approved_duration_hrs' => $attendance[0]['approved_duration_hrs'],
                'ot_rate' => isset($attendance['emp_ot_process']['ot_rate']) ? $attendance['emp_ot_process']['ot_rate'] : 0,
                'ot_amount' => isset($attendance['emp_ot_process']['ot_amount']) ? $attendance['emp_ot_process']['ot_amount'] : 0
            );
            $result['rows'][] = $row;
        }

        echo json_encode($result);
        exit;
    }

    public function jsons($branch = '', $resigned = '', $month)
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
        if ($branch != null && $branch != 'null') { // Edited by Akshay on 1-2-2025
            $branch_condition = " and branch_code in ('$branch') ";
        } else {
            $branch_condition = "";
        }

        // Edited by Akshay on 31-1-2025
        $emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition .= " and branch_code= '$is_ho' ";
            } else {
            }
        }
        // End
        // Edited by Akshay on 11-3-2025
        elseif ($user_group == 2) {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $company_code = $this->Session->read('company_code');
            if ($company_code == 'GAAR' || $company_code == 'HRBL') {
                $user_id = $this->Session->read("login_user_id"); //user id
                $special_access = $this->EmployeeDetails->query("SELECT COUNT(*) AS special_access FROM special_access WHERE user_id = '$user_id' AND status = 1;");
                $special_access = ($special_access[0][0]['special_access'] > 0) ? 1 : 0;

                if ($special_access != 1) {
                    $directors_branch = $this->EmployeeDetails->query("SELECT get_directors_branch_code() AS branch;");
                    $directors_branch = isset($directors_branch[0][0]['branch']) ? $directors_branch[0][0]['branch'] : '';
                    $branch_condition .= " and branch_code != '$directors_branch' ";
                }
            }
        }
        // End
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

        $yearmonth = $month . '-01';
        $emp_condition .= " and emp_proff.joining_date <= (select att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 2)) ";

        //added by megha on 8_6_19 resigned employee data
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
    }
    // End
}
