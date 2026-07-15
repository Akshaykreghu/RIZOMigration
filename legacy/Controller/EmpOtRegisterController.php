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
class EmpOtRegisterController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'EmpOtRegister';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Units', 'AttendanceRegister','DbConfig');
    public $components = array('MasterdataManagement');
    
    public function Register($month = '', $emp_pkey = 0) {

        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $current_emp_pkey = $this->Session->read('emp_fkey');
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
            $condition = "emp_fkey = '$emp_pkey' and ";
            $emp = $emp_pkey;
        } else {
            $condition = "emp_fkey in (select emp_fkey from emp_proff where attr1 = '$current_emp_pkey' ) and ";
            $emp = NULL;
        }

      
        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);

        $attend = $this->EmployeeDetails->query("select ot_duration_register_hierachy('$yearmonth','$emp','$current_emp_pkey')");
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
        $attendancesnotverified = $this->EmployeeDetails->query("select * from emp_ot_master left join emp_details as emp on (emp.emp_pkey = emp_ot_master.emp_fkey) where $condition month = '$yearmonth' and is_verified = 'N' ");
        $attendancesverified = $this->EmployeeDetails->query("select * from emp_ot_master left join emp_details as emp on (emp.emp_pkey = emp_ot_master.emp_fkey) where $condition month = '$yearmonth' and is_verified = 'Y' ");
        $this->set("attendancesnotverified",$attendancesnotverified);
        $this->set("attendancesverified",$attendancesverified);
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
    public function Approved($month = '', $emp_pkey = 0)
    {
        $this->autoRender = false;
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        
        $yearmonth = $month;
        //  debug($yearmonth);
        

        if ($emp_pkey) {
            $condition = "emp_fkey = '$emp_pkey' and ";
        } else {
            $condition = '';
        }
        $emp = isset($emp_pkey) ? $emp_pkey : NUll;
       
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $attendancesnotverified = $this->EmployeeDetails->query("select * from emp_ot_master where $condition month = '$yearmonth' and is_verified = 'N' ");
        $attendancesverified = $this->EmployeeDetails->query("select * from emp_ot_master where $condition month = '$yearmonth' and is_verified = 'Y' ");
        $this->set("attendancesnotverified",$attendancesnotverified);
        $this->set("attendancesverified",$attendancesverified);
        $this->render('register');
    }
    public function form($emp_pkey = 0,$duration = 0,$month = 0)
    {
        //debug("emp ".$emp_pkey);
        //debug("duration ".$duration);
        //debug("month ".$month);
        
        $month = $month;
        $emp = $emp_pkey;
        $duration = $duration;
        $this->set("month",$month);
        $this->set("duration",$duration);
        $this->set("emp",$emp);
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
        $this->EmployeeDetails->query("update emp_ot_master set set_duration = '$duration', remarks = '$remarks' where emp_fkey = '$emp' and month = '$month' ");
        $success = 1;
        
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
        $this->set("attendancesnotverified",$attendancesnotverified);
        foreach ($attendancesnotverified as $key => $att) {
            $arr_resp['data'][$key]/* ['EmpName'] */ = array_merge($att['0'],$att['EmployeeDetails']);
            
            $i++;
        }
        return json_encode($arr_resp);
    }
    public function index()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
         $join[] = array(
            'table' => 'emp_proff',
            'alias' => 'EmpProff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmpProff.emp_fkey = EmployeeDetails.emp_pkey')
        );
        $arr_employees = $this->EmployeeDetails->find("all",array('joins'=>$join,'conditions'=>array('status'=>1,'EmpProff.attr1'=>$emp_fkey)));
        $this->set("arr_employees", $arr_employees);
        
    }
    
    public function jsons($branch = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
        //debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q']: NULL;
       
        if($q != null)
        {
            $q_condition = "and first_name like '%$q%'";
        }
        else
        {
            $q_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_details.status = 1 and emp_proff.attr1 = '$emp_fkey' $q_condition");
        //debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        $branch[] = array("id"=>"0","text"=>"ALL");
        foreach ($branch_array as $key => $value)
        {
            $branch[] = array(
                'id' =>$value['emp_details']['emp_pkey'],
            'text' => $value['emp_details']['first_name']. ' ' .$value['emp_details']['last_name']
                );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }    
    public function subtable($emppkey=0,$month = '')
    {
        
         //$this->autoRender = false;
         $yearmonth = $month . '-01';
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
         $attendances = $this->EmployeeDetails->query("select empdetails.first_name,empdetails.last_name,emp_ot_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey from emp_ot_timeattandance left join emp_details as empdetails on(empdetails.emp_pkey = emp_ot_timeattandance.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where emp_ot_timeattandance.emp_pkey = '$emppkey' and emp_ot_timeattandance.yearmonth = '$yearmonth' ");
        $this->set('attendances', $attendances);
    
     
    }
    public function approves($selectd ='',$month = '')
    {
         $this->autoRender = false;
          $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $select = explode(',',$selectd);
        debug($select);
        foreach($select as $val)
        {
            $emp=$val;
            $month = $month;
            debug($emp);
            $this->EmployeeDetails->query("update emp_ot_timeattandance set isdelete ='N' where emp_pkey = '$emp' and yearmonth = '$month' ");
            $this->EmployeeDetails->query("update emp_ot_master set is_verified ='Y' where emp_fkey = '$emp' and month = '$month' ");
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
}