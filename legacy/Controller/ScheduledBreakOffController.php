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

class ScheduledBreakOffController extends AppController
{

    public $name = 'ScheduledBreakOff';
    public $datatable;
    public $uses = array('EmployeeDetails', 'ScheduledBreakOff', 'AttendanceRegister'); //array('CentralControl', 'SalaryStructures','UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'EmployeeConfig');
    //public $components = array('MasterdataManagement');

    public function index()
    {
    }

    public function listbreakoffdatesforsbospecial($monthChoosen)
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //added and commented by megha for attendance range change for sh infra
        $arr_dates = $this->EmployeeDetails->query("select distinct att_date from emp_detail_timeattandance where yearmonth='$monthChoosen-01' and (weekoff is not null or holiday is not null)");

        foreach ($arr_dates as $key => $value) {
            $data['id'] = $value['emp_detail_timeattandance']['att_date']; //date('j',  strtotime($value));
            $data['data'] = array($value['emp_detail_timeattandance']['att_date']);
            $arr_sbo["rows"][] = $data;
        }
        echo json_encode($arr_sbo);
    }

    public function listbreakoffdatesforsbospecialall($monthChoosen)
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //added and commented by megha for attendance range change for sh infra
        $arr_dates = $this->EmployeeDetails->query("select distinct att_date from emp_detail_timeattandance where yearmonth='$monthChoosen-01' ");

        foreach ($arr_dates as $key => $value) {
            $data['id'] = $value['emp_detail_timeattandance']['att_date']; //date('j',  strtotime($value));
            $data['data'] = array($value['emp_detail_timeattandance']['att_date']);
            $arr_sbo["rows"][] = $data;
        }
        echo json_encode($arr_sbo);
    }

    public function listbreakoffdatesforsbo($monthChoosen)
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
        /*  debug($end);
        debug($range); */
        /*$this->ScheduledBreakOff->useDbConfig = $this->Session->read('ds');
        $arrScheduledBreakOff = $this->ScheduledBreakOff->find("all", array("conditions" => array(,'status' => 1)));
        $arr_lpgrp = array();

        $arr_lpgrp["rows"] = array();
        foreach ($arrScheduledBreakOff as $key => $value) {
            $data['id'] = $value["LeavePolicyGroup"]['LEAVEPOLICY_GROUP_ID'];
            $data['data'] = array($value["LeavePolicyGroup"]["LEAVEPOLICY_GROUP_NAME"]);
            $arr_sbo["rows"][] = $data;
        }*/

        foreach ($arr_dates as $key => $value) {
            $data['id'] = $value; //date('j',  strtotime($value));
            $data['data'] = array($value);
            $arr_sbo["rows"][] = $data;
        }
        echo json_encode($arr_sbo);
    }

   public function addEmpToSBO()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $sbodate = (isset($_REQUEST['sbodate']) ? $_REQUEST['sbodate'] : '');

        $month = (isset($_REQUEST['month']) ? $_REQUEST['month'] : '');
        $message = (isset($_REQUEST['message']) ? $_REQUEST['message'] : '');
        //$dutty_time = (isset($_REQUEST['dutty_time']) ? $_REQUEST['dutty_time'] : '');
        $this->ScheduledBreakOff->useDbConfig = $this->Session->read('ds');
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        // debug($ids);


        // Edited by Akshay on 10-9-2025
        $arr_compoff_pkey = $this->ScheduledBreakOff->query("SELECT salary_head_item_pkey FROM `salary_head_items` WHERE `occurance` = 'COFF' AND `item_type` = 'LEAVE' AND `status` = '1' AND `value` = 'Y'");
        $comoff_pkey = isset($arr_compoff_pkey[0]['salary_head_items']['salary_head_item_pkey'])? $arr_compoff_pkey[0]['salary_head_items']['salary_head_item_pkey']: 0;
        // $comoff_pkey = $_REQUEST['type'] == 'Attendance' ? 0: $comoff_pkey;
        // End
        $arr_failed_emp_list = array();

        if ($ids) {
            $arr_ids = explode(",", $ids);

            foreach ($arr_ids as $key => $value) {
                try {
                    //Check if this employee has already verified attendance
                    // $att_conditions = array(
                    //     'AttendanceRegister.isdelete' => 'N',
                    //     'AttendanceRegister.emp_fkey' => $value,
                    //     'AttendanceRegister.month_year' => date('Y-m', strtotime($month))
                    // );
                    // if ($this->AttendanceRegister->hasAny($att_conditions)) {
                    //     //You cannod add this employee, since his / her attendance is already verified
                    //     $arr_failed_emp_list[] = $value;
                    // } else {
                    //Proceed with add
                    //  $arr_half_day = $this->ScheduledBreakOff->query("select leave_duplication_fn('$value') as blnce ");  
                    //  debug($arr_half_day); 
                    $conditions = array(
                        'ScheduledBreakOff.emp_fkey' => $value,
                        'ScheduledBreakOff.type' => $_REQUEST['attendance'] == 'Attendance' ? 'A' : 'W',
                        'ScheduledBreakOff.break_off_date' => $sbodate
                    );

                    if ($this->ScheduledBreakOff->hasAny($conditions)) {

                        //do update
                        $this->ScheduledBreakOff->updateAll(
                            array(
                                'ScheduledBreakOff.status' => 1,
                                'ScheduledBreakOff.modified_by' => "'" . $this->Session->read('login_user_id') . "'",
                                'ScheduledBreakOff.modification_date' => "'" . date('Y-m-d') . "'"
                            ),
                            array(
                                'ScheduledBreakOff.type' => $_REQUEST['attendance'] == 'Attendance' ? 'A' : 'W',
                                'ScheduledBreakOff.emp_fkey' => $value,
                                'ScheduledBreakOff.break_off_date' => $sbodate
                                // 'ScheduledBreakOff.salary_head_item_fkey' => $comoff_pkey // Edited by Akshay on 10-9-2025
                            )
                        );
                        
                    } else {
                        // $arr_ids = explode(",", $ids);
                        //  $arr_half_day = $this->ScheduledBreakOff->query("select leave_duplication_fn('$ids') as blnce "); 
                        //  debug($arr_half_day);
                        //do insert
                        $data['id'] = 0;
                        $data['emp_fkey'] = $value;
                        $data['break_off_date'] = $sbodate;
                        //$data['dutty_time'] = $dutty_time;
                        $data['message'] = $message;
                        $message1 = '';
                        if ($message) {
                            $message1 = ' for ' . $message;
                        }
                        //                        if($dutty_time != '00:00:00'){
                        //                            $dutty_time = $dutty_time;
                        //                        }
                        $data['break_off_msg'] = "Break off applied on " . $sbodate . " " . $message1;
                        $data['type'] = $_REQUEST['attendance'] == 'Attendance' ? 'A' : 'W';
                        
                        if ($_REQUEST['attendance'] == 'Attendance') {
                            $data['first_half'] = (isset($_REQUEST['duration']) && $_REQUEST['duration'] == 'true') ? 'Y' : 'N';
                        }
                       
                        $data['created_by'] = $this->Session->read('login_user_id');
                        $data['creation_date'] = date('Y-m-d');
                        // $data['salary_head_item_fkey'] = $comoff_pkey; // Edited by Akshay on 10-9-2025
                        $this->ScheduledBreakOff->save($data);
                    }
                    // }
                } catch (Exception $e) {
                    echo $e;
                    exit;
                }
                //edited by athira 

                 $yearmonth=$month . "-01";
                 $employee_details=$this->EmployeeDetails->query("SELECT * FROM emp_details WHERE emp_pkey= '$value'");
                 $branch_code=$employee_details['0']['emp_details']['branch_code'];


             if (!$this->EmployeeDetails->query("SELECT time_duration_check('$yearmonth', '$value', '$branch_code')")) {
                    $resp_mispunches["total"] = "0";
                    $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
                    $resp_mispunches["type"] = "danger";
                    echo json_encode($resp_mispunches);
                    //                return false;
                    die();
                }
                //end
            }

           

            $result = $result = 'Empolyee added to selected date';
            // if (!empty($arr_failed_emp_list)) {
            //     if (count($arr_failed_emp_list) == 1) {
            //         $arr_emp_details = $this->EmployeeDetails->find("all", array('fields' => 'CONCAT_WS(" ",first_name,last_name) as name', 'conditions' => array('EmployeeDetails.emp_pkey' => $arr_failed_emp_list)));
            //         $str_failed_emp_name = isset($arr_failed_emp_list[0][0]['name']) ? $arr_failed_emp_list[0][0]['name'] : "";
            //         $failure_msg = !empty($str_failed_emp_name) ? $str_failed_emp_name . "'s attendance verified earlier, so you cannot proceed with them" : "One employee's attendance verified earlier, so you cannot proceed with them";
            //         $result = $failure_msg;
            //     } else {
            //         $result = "Some of the employees, were already verified their attendance, so you cannot proceed with them";
            //     }
            // }


            echo json_encode($result);
        }
    }

    
    public function removeEmpFromSBO()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $sbodate = (isset($_REQUEST['sbodate']) ? $_REQUEST['sbodate'] : '');
        $month = (isset($_REQUEST['month']) ? $_REQUEST['month'] : '');
        $this->ScheduledBreakOff->useDbConfig = $this->Session->read('ds');
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                // debug($value);
                try {
                    //Check if this employee has already verified attendance
                    $att_conditions = array(
                        'AttendanceRegister.isdelete' => 'N',
                        'AttendanceRegister.emp_fkey' => $value,
                        'AttendanceRegister.month_year' => date('Y-m', strtotime($month))
                    );
                    if ($this->AttendanceRegister->hasAny($att_conditions)) {
                        //You cannod add this employee, since his / her attendance is already verified
                        $arr_failed_emp_list[] = $value;
                    } else {
                        //Proceed with add
                        $condition['emp_fkey'] = $value;
                        $condition['break_off_date'] = $sbodate;
                        // $data['dutty_time'] = '';
                        $data['message'] = '';
                        $data['break_off_msg'] = "";

                        $this->ScheduledBreakOff->updateAll(
                            array(
                                'ScheduledBreakOff.modified_by' => "'" . $this->Session->read('login_user_id') . "'",
                                'ScheduledBreakOff.modification_date' => "'" . date('Y-m-d') . "'",
                                'ScheduledBreakOff.status' => 0,
                                'ScheduledBreakOff.first_half' => "'N'"
                            ),
                            $condition
                        );

                        $result = 'Employee removed to selected date';
                    }
                } catch (Exception $e) {
                    echo $e;
                    exit;
                }
                //edited by athira

                $yearmonth=$month . "-01";
                 $employee_details=$this->EmployeeDetails->query("SELECT * FROM emp_details WHERE emp_pkey= '$value'");
                 $branch_code=$employee_details['0']['emp_details']['branch_code'];

            $deleterecords = $this->EditPunches->query("delete from  emp_detail_timeattandance where emp_pkey in ('$value')  and yearmonth='$yearmonth' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$yearmonth','%Y-%m')) AND att_date NOT IN (SELECT att_date FROM emp_ot_timeattandance WHERE emp_pkey = '$value' AND yearmonth = '$yearmonth' AND isdelete = 'N') "); //edited by athira on 26-06-2025


             if (!$this->EmployeeDetails->query("SELECT time_duration_check('$yearmonth', '$value', '$branch_code')")) {
                    $resp_mispunches["total"] = "0";
                    $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
                    $resp_mispunches["type"] = "danger";
                    echo json_encode($resp_mispunches);
                    //                return false;
                    die();
                }
                //end
            }
        }

        if (!empty($arr_failed_emp_list)) {
            if (count($arr_failed_emp_list) == 1) {
                $arr_emp_details = $this->EmployeeDetails->find("all", array('fields' => 'CONCAT_WS(" ",first_name,last_name) as name', 'conditions' => array('EmployeeDetails.emp_pkey' => $arr_failed_emp_list)));
                $str_failed_emp_name = isset($arr_failed_emp_list[0][0]['name']) ? $arr_failed_emp_list[0][0]['name'] : "";
                // $failure_msg = !empty($str_failed_emp_name) ? "Attendance verified for this date. Comp Off allocation /reversal is not allowed" : "Attendance verified for this date. Comp Off allocation /reversal is not allowed";
                //$failure_msg = "Attendance verified for this date. Comp Off allocation or reversal is not allowed.";
                $failure_msg = "Attendance verified for this date. Comp Off reversal  is not allowed.";
                $result = $failure_msg;
            } else {
                $result = "Some of the employees, were already verified their attendance, so you cannot proceed with them";
            }
        }

        echo json_encode($result);
    }


    public function listemployeesinsbodate($attendanceType = '')
    {

        $this->autoRender = FALSE;
        $date = (isset($_REQUEST['date']) ? $_REQUEST['date'] : 0);
        $filter = (isset($_REQUEST['filter']) ? $_REQUEST['filter'] : false);

        $this->ScheduledBreakOff->useDbConfig = $this->Session->read('ds');
        $emp_in_policy = $this->ScheduledBreakOff->find(
            "list",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "break_off_date" => $date,
                    "type" => $attendanceType == 'attendance' ? 'A' : 'W',
                    "status" => 1
                )
            )
        );

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );
        $conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_in_policy);

        if ($filter == 'true') {
            $conditions[] = array("EmployeeDetails.emp_pkey IN (SELECT emp_pkey FROM device_attandance JOIN emp_details ON (emp_details.emp_id = device_attandance.emp_id) WHERE `C1` = 'in' AND `LOGDATE` > '$date 00:00:00' AND `LOGDATE` < '$date 24:00:00') ");
        }

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }

        echo json_encode($resp_emp);
    }

    public function listemployeesforsbodate()
    {

        $this->autoRender = FALSE;
        $month = (isset($_REQUEST['month']) ? $_REQUEST['month'] : '');
        $date = (isset($_REQUEST['date']) ? $_REQUEST['date'] : 0);
        $filter = (isset($_REQUEST['filter']) ? $_REQUEST['filter'] : false);
        $dutty_time = (isset($_REQUEST['outtime']) ? $_REQUEST['outtime'] : '0:00:00');

        $this->ScheduledBreakOff->useDbConfig = $this->Session->read('ds');
        $emp_in_policy = $this->ScheduledBreakOff->find(
            "list",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "break_off_date" => $date,
                    "status" => 1
                )
            )
        );

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
            array(
                'table' => 'attendance_register',
                'alias' => 'AttendanceRegister',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('AttendanceRegister.emp_fkey = EmployeeDetails.emp_pkey')
            ),

        );

        $conditions = array(
            'EmployeeDetails.status' => 1,
            'AttendanceRegister.month_year' => $month,
            'AttendanceRegister.isdelete' => 'Y'
        );

        if ($filter == 'true') {
            $conditions[] = array("EmployeeDetails.emp_pkey IN (SELECT emp_pkey FROM device_attandance JOIN emp_details ON (emp_details.emp_id = device_attandance.emp_id) "
                . "WHERE `C1` = 'in' AND `LOGDATE` > '$date 00:00:00' AND `LOGDATE` < '$date 23:59:00') ");
        }

        if ($dutty_time != '0:00:00') {
            $conditions[] = array("EmployeeDetails.emp_pkey IN (SELECT emp_pkey FROM device_attandance JOIN emp_details ON (emp_details.emp_id = device_attandance.emp_id) left join emp_proff as EmployeeProfessionalDetails on (EmployeeProfessionalDetails.emp_fkey = emp_details.emp_pkey) left join working_day_time_procedures as Shift_policy on (Shift_policy.day_time_seq = EmployeeProfessionalDetails.day_time_seq) WHERE  `LOGDATE` >= '$date $dutty_time' AND DATE_FORMAT(LOGDATE,'%HH:%MI:%SS') <= ifnull(off_dutty2,off_dutty1) and c1='out' and DATE_FORMAT(LOGDATE,'%Y-%m-%d')= '$date') ");
        }



        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
    }

    public function listemployeesforsbodateone()
    {

        $this->autoRender = FALSE;
        $month = (isset($_REQUEST['month']) ? $_REQUEST['month'] : '');
        $date = (isset($_REQUEST['date']) ? $_REQUEST['date'] : 0);
        $filter = (isset($_REQUEST['filter']) ? $_REQUEST['filter'] : false);
        $dutty_time = (isset($_REQUEST['outtime']) ? $_REQUEST['outtime'] : '0:00:00');

        $this->ScheduledBreakOff->useDbConfig = $this->Session->read('ds');
        $emp_in_policy = $this->ScheduledBreakOff->find(
            "list",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "break_off_date" => $date,
                    "status" => 1
                )
            )
        );

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
            array(
                'table' => 'attendance_register',
                'alias' => 'AttendanceRegister',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('AttendanceRegister.emp_fkey = EmployeeDetails.emp_pkey')
            ),

        );

        $conditions = array(
            'EmployeeDetails.status' => 1,
            'AttendanceRegister.month_year' => $month,
             'AttendanceRegister.isdelete' => 'Y'
        );

        if ($filter == 'true') {
            $conditions[] = array("EmployeeDetails.emp_pkey IN (SELECT emp_pkey FROM device_attandance JOIN emp_details ON (emp_details.emp_id = device_attandance.emp_id) "
                . "WHERE `C1` = 'in' AND `LOGDATE` > '$date 00:00:00' AND `LOGDATE` < '$date 23:59:00') ");
        }

        if ($dutty_time != '0:00:00') {
            $conditions[] = array("EmployeeDetails.emp_pkey IN (SELECT emp_pkey FROM device_attandance JOIN emp_details ON (emp_details.emp_id = device_attandance.emp_id) left join emp_proff as EmployeeProfessionalDetails on (EmployeeProfessionalDetails.emp_fkey = emp_details.emp_pkey) left join working_day_time_procedures as Shift_policy on (Shift_policy.day_time_seq = EmployeeProfessionalDetails.day_time_seq) WHERE  `LOGDATE` >= '$date $dutty_time' AND DATE_FORMAT(LOGDATE,'%HH:%MI:%SS') <= ifnull(off_dutty2,off_dutty1) and c1='out' and DATE_FORMAT(LOGDATE,'%Y-%m-%d')= '$date') ");
        }



        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
    }
    
}
