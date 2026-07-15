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
class EmpattendanceregisterController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Empattendanceregister';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Units', 'AttendanceRegister','DbConfig');
    public $components = array('MasterdataManagement');
    
    
    
    public function registerbook($monthdd='',$emp_pkey = '')
    {
       $emp_fkey = $this->Session->read('emp_fkey');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y','company_code' => $company_code))));
        $month = $monthdd;
        $yearmonth = $month.'-01';
      //  debug($yearmonth);
        $attendance_date = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:0;
        $att_enddate = date('d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t',  strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t',strtotime('-1 months',strtotime($month)))))))));
	$arr_date_in_selectedmonth = range(1, $att_enddate);        
        if($att_startdate != 1){
            $arr_date_in_prevmonth = range($att_startdate, date('t',strtotime('-1 months',strtotime($month))));
        }else{
            $arr_date_in_prevmonth = array();
        }
        
        if($emp_pkey)
        {
            $condition = "emp_detail_timeattandance.emp_pkey = '$emp_pkey' and ";
        }
        else
        {
            $condition = '';
        }
   
        $arr_dates = array_merge($arr_date_in_prevmonth,$arr_date_in_selectedmonth);
        $emp = isset($emp_pkey)?$emp_pkey : '' ; 
        $attend= $this->EmployeeDetails->query("select time_duration_check_hierarchy('$yearmonth','$emp','$emp_fkey')");
         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
         $attendances= $this->EmployeeDetails->query("select empdetails.first_name,empdetails.last_name,emp_detail_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey from emp_detail_timeattandance left join emp_details as empdetails on(empdetails.emp_pkey = emp_detail_timeattandance.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where $condition yearmonth = '$yearmonth' ");
   //  debug($attendances);
        $this->set('arr_dates', $arr_dates);
   
      $employee_attendance= array();
      foreach($attendances as $val)
      {
          $emppk = $val['emp_detail_timeattandance']['emp_pkey'];
          $employee_attendance[$emppk][] = $val;
          
      }
           $this->set('employee_attendance', $employee_attendance);
        
        }
        
    public function filter()
    {
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y','company_code' => $company_code))));
        $month = $monthdd;
        $yearmonth = $month.'-01';
      //  debug($yearmonth);
        $attendance_date = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:0;
        $att_enddate = date('d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t',  strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t',strtotime('-1 months',strtotime($month)))))))));
	$arr_date_in_selectedmonth = range(1, $att_enddate);        
        if($att_startdate != 1){
            $arr_date_in_prevmonth = range($att_startdate, date('t',strtotime('-1 months',strtotime($month))));
        }else{
            $arr_date_in_prevmonth = array();
        }
        
        
   
        $arr_dates = array_merge($arr_date_in_prevmonth,$arr_date_in_selectedmonth);
        
        $attend= $this->EmployeeDetails->query("select time_duration_check('$yearmonth')");
         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
         $attendances= $this->EmployeeDetails->query("select empdetails.first_name,empdetails.last_name,emp_detail_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey from emp_detail_timeattandance left join emp_details as empdetails on(empdetails.emp_pkey = emp_detail_timeattandance.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where yearmonth = '$yearmonth' ");
   //  debug($attendances);
        $this->set('arr_dates', $arr_dates);
   
      $employee_attendance= array();
      foreach($attendances as $val)
      {
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
        $arr_branches = $this->Units->find("all", array("conditions" => array('status' => 1)));
        $this->set("arr_branches", $arr_branches);
        //debug($arr_branches);
        $arr_employees = $this->EmployeeDetails->find("all",array('conditions'=>array('status'=>1)));
        $this->set("arr_employees", $arr_employees);
    }

   
    
    
    
  
}