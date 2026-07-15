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
class OtAttendanceController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'OtAttendance';
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
        if (!empty($emp_pkey) && $emp_pkey != 'null') {
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
        // debug($yearmonth);

        // Edited by Akshay on 28-2-2026
        if (isset($emp) && $emp != 0) {
            $attend = $this->EmployeeDetails->query("select ot_duration_register('$yearmonth','$emp','$branch')");
        } else {
            $arr_emp = $this->EmployeeDetails->query("SELECT emp_pkey from emp_details emp WHERE $condition1 emp.status = 1");
            foreach ($arr_emp as $emp) {
                $emp = isset($emp['emp']['emp_pkey']) ? $emp['emp']['emp_pkey'] : 0;
                $attend = $this->EmployeeDetails->query("select ot_duration_register('$yearmonth','$emp','$branch')");
            }
        }
        // End

        //debug($attend);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //        $attendances = $this->EmployeeDetails->query("select empdetails.first_name,empdetails.last_name,emp_ot_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey from emp_ot_timeattandance left join emp_details as empdetails on(empdetails.emp_pkey = emp_ot_timeattandance.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where $condition yearmonth = '$yearmonth' ");
        //        $this->set('arr_dates', $arr_dates);
        //        //debug($attendances);
        //        $employee_attendance = array();
        //        foreach ($attendances as $val) {
        //            $emppk = $val['emp_ot_timeattandance']['emp_pkey'];
        //            $employee_attendance[$emppk][] = $val;
        //        }
        //        $this->set('employee_attendance', $employee_attendance);
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
        // debug("select * from emp_ot_master left join emp_details as emp on (emp.emp_pkey = emp_ot_master.emp_fkey) left join emp_proff on (emp_proff.emp_fkey = emp_ot_master.emp_fkey) where $condition_emps $condition $condition1 month = '$yearmonth' and is_verified = 'Y' and emp.status = 1 ");
        $attendancesnotverified = $this->EmployeeDetails->query("select * from emp_ot_master left join emp_details as emp on (emp.emp_pkey = emp_ot_master.emp_fkey) left join emp_proff on (emp_proff.emp_fkey = emp_ot_master.emp_fkey) where $condition_emps $condition $condition1 month = '$yearmonth' and is_verified = 'N' and emp.status = 1 ");
        // $attendancesverified = $this->EmployeeDetails->query("select * from emp_ot_master left join emp_details as emp on (emp.emp_pkey = emp_ot_master.emp_fkey) left join emp_proff on (emp_proff.emp_fkey = emp_ot_master.emp_fkey) where $condition_emps $condition $condition1 month = '$yearmonth' and is_verified = 'Y' and emp.status = 1 ");
        //$attendancesverified = $this->EmployeeDetails->query("
        //    SELECT 
        //        emp_ot_master.*, 
        //        emp.*, 
        //        emp_proff.*
        //    FROM 
        //        emp_ot_master
        //    LEFT JOIN emp_details AS emp ON emp.emp_pkey = emp_ot_master.emp_fkey
        //    LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_ot_master.emp_fkey
        //    LEFT JOIN emp_ot_process ON emp_ot_process.emp_ot_master_fkey = emp_ot_master.emp_ot_master_pkey
        //    WHERE $condition_emps $condition $condition1
        //        emp_ot_master.month = '$yearmonth'
        //        AND emp_ot_master.is_verified = 'Y'
        //        AND emp.status = 1
        //        AND emp_ot_master.emp_fkey NOT IN (
        //            SELECT emp_ot_master_fkey  
        //            FROM emp_ot_process
        //            WHERE process IN ('processed', 'approved')
        //              AND month = '$yearmonth'
        //              AND end_date_effective IS NULL
        //        )
        //");
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


        //        debug( "SELECT emp_ot_master.*, emp.*, emp_proff.*, emp_ot_process.*
        //    FROM emp_ot_master
        //    LEFT JOIN emp_details AS emp ON emp.emp_pkey = emp_ot_master.emp_fkey
        //    LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_ot_master.emp_fkey
        //    LEFT JOIN emp_ot_process ON emp_ot_process.emp_ot_master_fkey = emp_ot_master.emp_ot_master_pkey
        //    WHERE $condition_emps $condition $condition1 emp_ot_master.month = '$yearmonth' AND is_verified = 'Y' AND emp.status = 1
        //   AND emp_ot_process.process = 'processed' AND emp_ot_process.end_date_effective IS NULL");
        $this->set("company_code", $company_code);
        $this->set("attendancesnotverified", $attendancesnotverified);
        $this->set("attendancesverified", $attendancesverified);

        /*$resp_emp = array();
        $resp_emp["data"] = array();
        foreach ($employee_attendance as $key => $value) {
            $arr_emp_att = array();
            foreach ($value as $v) {
                $arr_emp_att['fullname'] = (isset($v['empdetails']['first_name'])?$v['empdetails']['first_name'].' ':'').(isset($v['empdetails']['last_name'])?$v['empdetails']['last_name']:'');
                $day = date('d',  strtotime($v['emp_ot_timeattandance']['att_date']));
                $arr_emp_att[$day] = $v['emp_ot_timeattandance']['present'].' '.$v['emp_ot_timeattandance']['holiday'].' '.$v['emp_ot_timeattandance']['leaves'].' '.$v['emp_ot_timeattandance']['weekoff'].' '.$v['emp_ot_timeattandance']['others'];
            }
            $resp_emp["data"][] = $arr_emp_att;
        }
        //  debug($resp_emp["rows"][$key]);
        echo json_encode($resp_emp);*/
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
        //        $months = date("Y-m", strtotime($yearmonth));
        //        $arr_exists = $this->EmployeeDetails->query("select count(*) AS COUNT from attendance_register where $condition month_year = '$months' and isdelete ='N'");
        //        $count = isset($arr_exists['0']['0']['COUNT'])?$arr_exists['0']['0']['COUNT']:0;
        //        $this->set("count",$count);
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
        $emp = $_POST['emp']; // Emp value
        $duration = $_POST['duration']; // Duration value
        $remarks = $_POST['remarks']; // Remarks value

        //$this->EmployeeDetails->query("update emp_ot_master set set_duration = '$duration', remarks = '$remarks', is_verified ='N' where emp_fkey = '$emp' and month = '$month' ");
        $this->EmployeeDetails->query("update emp_ot_master set set_duration =  CASE 
                      WHEN '$duration' = '' THEN NULL 
                      ELSE '$duration' 
                   END, remarks = '$remarks', is_verified ='N' where emp_fkey = '$emp' and month = '$month' ");
        // debug("update emp_ot_master set set_duration = '$duration', remarks = '$remarks', is_verified ='N' where emp_fkey = '$emp' and month = '$month'");
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
        $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        $this->set("arr_employees", $arr_employees);
    }
    public function subtable($emppkey = 0, $month = '')
    {

        //$this->autoRender = false;
        $yearmonth = $month . '-01';
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $attendances = $this->EmployeeDetails->query("select empdetails.first_name,empdetails.last_name,emp_ot_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey from emp_ot_timeattandance left join emp_details as empdetails on(empdetails.emp_pkey = emp_ot_timeattandance.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where emp_ot_timeattandance.emp_pkey = '$emppkey' and emp_ot_timeattandance.yearmonth = '$yearmonth' order by att_date");
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
        $attendances = $this->EmployeeDetails->query("select empdetails.first_name,empdetails.last_name,emp_ot_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey from emp_ot_timeattandance left join emp_details as empdetails on(empdetails.emp_pkey = emp_ot_timeattandance.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where emp_ot_timeattandance.emp_pkey = '$emppey' and emp_ot_timeattandance.yearmonth = '$yearmonth' order by att_date");
        $this->set('attendances', $attendances);
        $this->set('month', $yearmonth);
        $this->set('emp', $emppkey);
        $this->set('mode', 'pdf');
        $view = new View($this, false);
        //                    debug($attendances);
        //                    debug($emppkey);
        //                    debug($month);
        //                    echo "select empdetails.first_name,empdetails.last_name,emp_ot_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey from emp_ot_timeattandance left join emp_details as empdetails on(empdetails.emp_pkey = emp_ot_timeattandance.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where emp_ot_timeattandance.emp_pkey = '$emppey' and emp_ot_timeattandance.yearmonth = '$yearmonth' ";
        $view_output = $view->render('subtablegenpdf');
        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

        $html2pdf = new HTML2PDF('L', 'A2', 'fr');
        //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
        //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($view_output);
        $html2pdf->Output('subtable.pdf', 'D');
        $this->render('subtablegenpdf');
    }
    public function approves($selectd = '', $month = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $select = explode(',', $selectd);
        foreach ($select as $val) {
            $emp = $val;
            $month = $month;
            $this->EmployeeDetails->query("update emp_ot_timeattandance set isdelete ='N' where emp_pkey = '$emp' and yearmonth = '$month' ");
            // $this->EmployeeDetails->query("update emp_ot_master set is_verified ='Y', set_duration = total_duration where emp_fkey = '$emp' and month = '$month' ");
            //Edited by Akshay on 9-8-2024
            //$this->EmployeeDetails->query("update emp_ot_master set is_verified ='Y', total_duration =COALESCE(set_duration, total_duration), set_duration = total_duration where emp_fkey = '$emp' and month = '$month' ");
            $this->EmployeeDetails->query("update emp_ot_master set is_verified ='Y', set_duration = CASE 
                                                        WHEN set_duration IS NULL THEN total_duration
                                                        ELSE set_duration 
                                                    END 
                                                    where emp_fkey = '$emp' and month = '$month' ");
            //End
        }
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
                    //Edited by Akshay on 19-8-2024
                    //                    $result = $this->EmployeeDetails->query("UPDATE emp_ot_process p
                    //                                                                LEFT JOIN emp_ot_master m ON p.emp_ot_master_fkey = m.emp_ot_master_pkey
                    //                                                                SET p.end_date_effective = '$currentDate', 
                    //                                                                    p.process_date = '$currentDateTime'
                    //                                                                WHERE p.emp_ot_master_fkey = '$pkey' 
                    //                                                                AND m.is_verified = 'N'");
                    //End
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
}
