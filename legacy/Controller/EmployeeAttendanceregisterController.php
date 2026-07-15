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
class EmployeeAttendanceregisterController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'EmployeeAttendanceregister';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Units', 'AttendanceRegister','DbConfig');
    public $components = array('MasterdataManagement');
    
    
    public function registerbook($monthdd='',$emp_pkey = '',$branch = '')
    {
       //debug($emp_pkey);
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
        
        if($emp_pkey != 0)
        {
            $condition = "emp_detail_timeattandance.emp_pkey = '$emp_pkey' and ";
            $emp = $emp_pkey;
        }
        else
        {
            $condition = '';
            $emp = NULL;
        }
        if($branch != 0)
        {
            $condition1 = "emp.emp_branch = '$branch' and ";
            $branch = $branch;
        }
        else
        {
            $condition1 = "";
            $branch = NULL;
        }
        $arr_dates = array_merge($arr_date_in_prevmonth,$arr_date_in_selectedmonth);
        
        $attend= $this->EmployeeDetails->query("select time_duration_check('$yearmonth','$emp','$branch')");
         $emp_fkey = $this->Session->read('emp_fkey'); 
         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
         $attendances= $this->EmployeeDetails->query("select empdetails.first_name,empdetails.last_name,emp_detail_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey from emp_detail_timeattandance "
                 . "left join emp_details as empdetails on(empdetails.emp_pkey = emp_detail_timeattandance.emp_pkey) "
                 . "left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) "
                 . "left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq)"
                 . " where $condition $condition1 yearmonth = '$yearmonth'and emp.attr1='$emp_fkey' order by att_date ");
     $this->set('arr_dates', $arr_dates);
  //debug($attendances);
      $employee_attendance= array();
      foreach($attendances as $val)
      {
          $emppk = $val['emp_detail_timeattandance']['emp_pkey'];
          $employee_attendance[$emppk][] = $val;
          
      }
          $this->set('employee_attendance', $employee_attendance);
        }
        
    public function jsons($branch = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //debug($branch);
           $emp_fkey = $this->Session->read('emp_fkey'); 
        if($branch != null)
        {
            $branch_condition = "and emp_details.branch_code in ('$branch')";
        }
        else
        {
            $branch_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select emp_details.* from emp_details "
                . "left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey)"
                . "where emp_details.status = 1 and emp_proff.attr1='$emp_fkey' $branch_condition");
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
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y','company_code' => $company_code))));
        $month = $monthdd;
        $yearmonth = $month.'-01';
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
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);
        //debug($arr_branches);
      //  $arr_employees = $this->EmployeeDetails->find("all",array('conditions'=>array('status'=>1)));
       // $this->set("arr_employees", $arr_employees);
        
           $joins = array(
            array(
            'table' => 'emp_proff',
            'alias' => 'EmployeeProfessionalDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
          $emp_fkey = $this->Session->read('emp_fkey');    
        $conditions  =   array('status'=>1,'EmployeeProfessionalDetails.attr1'=>$emp_fkey);
        $fields="EmployeeProfessionalDetails.*,EmployeeDetails.*";
        $arr_employees = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees =  $this -> EmployeeDetails ->find("all",array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions,
            'order'=>'EmployeeDetails.first_name' /*code by sruthi 11/08/2010*/
        ));
        
        $this->set("arr_employees", $arr_employees);
        
        
        
        
        
    }

    public function showregister() {
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
        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all",
                array("fields" => array("emp_pkey", "emp_name"),'joins' => $joins,
                    "conditions" => array('status' => 1,'EmployeeProffessional.attr1'=>$emp_fkey))));
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
                'color' => 'maroon', 
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
    
    public function showregistertab($verified=0){
        $this->set('tab',$verified);
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
        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all",
                array("fields" => array("emp_pkey", "emp_name"),'joins' => $joins,
                    "conditions" => array('status' => 1,'EmployeeProffessional.attr1'=>$emp_fkey))));
       // $this->set('arr_employees', $arr_employees);
        $arr_requestdata = $this->request->data;
        $month = isset($arr_requestdata['month'])?$arr_requestdata['month']:date('Y-m');
        
      
      //  $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"),'joins' => $joins, "conditions" => array('status' => 1))));
        $this->set('arr_employees', $arr_employees);
        
        //Fetch company's attendance end date
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y','company_code' => $company_code))));
        
	//By santhosh on 27 Dec 2015
	//$att_startdate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_enddate = date('t',  strtotime($month));
        
        //On 20 Feb 2016
        //$att_enddate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_startdate = $att_enddate + 1;
        $attendance_date = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:0;
        $att_enddate = date('d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t', strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t',strtotime('-1 months',strtotime($month)))))))));
        $arr_date_in_selectedmonth = range(1, $att_enddate);
        if($att_startdate != 1){
            $arr_date_in_prevmonth = range($att_startdate, date('t',strtotime('-1 months',strtotime($month))));
        }else{
            $arr_date_in_prevmonth = array();
        }
        
        $arr_dates = array_merge($arr_date_in_prevmonth,$arr_date_in_selectedmonth);
        $this->set('arr_dates', $arr_dates);
        
        //On 27 Dec 2015
        //$date_start = date('Y-m-d',strtotime($month.'-'.$att_startdate));
        //$date_end = date('Y-m-d',strtotime('-1 day',strtotime('+1 months',strtotime($date_start))));
        
        //On 26/01/2016
        $date_start = date('Y-m-d',strtotime('-1 months',strtotime($month.'-'.$att_startdate)));
        $date_end = date('Y-m-d',strtotime($month.'-'.$att_enddate));
        
	//$date_start = current($arr_dates);
        //$date_end = end($arr_dates);		
        
        $this->set('date_start',$date_start);
        $this->set('date_end',$date_end);
    }

    /*
     * List attendance register
     * By santhosh on 02 Aug 2015
     */

    public function listregisterentries() {
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
    
    public function listverifiedregisterentries(){
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

    public function processregisterentries() {
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

    public function verifyregisterentries($registerid=0) {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        
        $arr_requestdata = $this->request->data;
        //Modified On 21 Feb 2016
        if (isset($arr_requestdata["ids"])) {
            //$ar_ids = explode(",", $_REQUEST["ids"]);$arr_requestdata
            $arr_registerids = isset($arr_requestdata["ids"])?explode(",", $arr_requestdata["ids"]):array();
            $ar_ids = array();
            foreach ($arr_registerids as $register_id){
                if(empty(json_decode($this->checkifregistercanverify($register_id, $arr_requestdata)))){
                    $ar_ids[] = $register_id;
                }
            }
            if(!empty($ar_ids)){
                $this->AttendanceRegister->updateAll(
                        array('isdelete' => "'N'"), array('AttendanceRegister.registerid' => $ar_ids)
                );
                $result['success'] = 1;
            }else{
                $result['success'] = 0;
            }
        }else if($registerid != 0){
            $this->AttendanceRegister->updateAll(
                    array('isdelete' => "'N'"), array('AttendanceRegister.registerid' => $registerid)
            );
            $result['success'] = 1;
        }
        echo json_encode($result);
    }
    public function loadattendanceregisterheader(){
        $this->autoRender = FALSE;
        $arr_requestdata = $this->request->data;
        $month = isset($arr_requestdata['month'])?$arr_requestdata['month']:date('Y-m');
        
        //Fetch company's attendance start date
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y','company_code' => $company_code))));
        
        //On 20 Feb 2016
        //$att_startdate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_enddate = date('t',strtotime($month));
        //$att_startdate = $att_enddate + 1;
        $attendance_date = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:0;
        $att_enddate = date('d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t',  strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t',strtotime('-1 months',strtotime($month)))))))));
        
        $arr_columns = array();
        $arr_columns[] = array('field'=>'emp_name','title'=>'Employee name','width'=>'10%');
        for ($i = $att_startdate; $i <= $att_enddate; $i++) {
            $arr_columns[] = array('field'=>'FIELD'.$i,'title'=>$i,'width'=>'3%','styler:styleDay');
        }
        $arr_columns[] = array('field'=>'days_present','title'=>'Days present','width'=>'10%');
        $arr_columns[] = array('field'=>'days_leave','title'=>'Days on leave','width'=>'10%');
        $arr_columns[] = array('field'=>'days_holidays','title'=>'Holidays','width'=>'10%');
        echo json_encode($arr_columns);
    }
    
    //Modified On 21 Feb 2016
    public function checkifregistercanverify($registerid = 0, $arr_requestdata= array()) {
        $this->autoRender = FALSE;
        if(empty($arr_requestdata)){
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

    public function updateregisterentries($registerid=0){
        $this->set('registerid',$registerid);
        if($registerid != 0){
            $arr_requestdata = $this->request->data;
            $json_dates = $arr_requestdata['dates'];
            $arr_dates = json_decode($json_dates);
            $this->set('arr_dates',$arr_dates);
        }
    }
    
    public function submitregisterentry(){
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;
        $registerid = isset($arr_requestdata['hid-registerid'])?$arr_requestdata['hid-registerid']:0;
        $count = isset($arr_requestdata['hid-count-missing'])?$arr_requestdata['hid-count-missing']:0;
        if($registerid != 0){
            if($count != 0){
                $arr_fields = array();
                $arr_updateentries = array();
                for($i = 0;$i<$count;$i++){
                    $index = isset($arr_requestdata['hid-reg-field-'.$i])?$arr_requestdata['hid-reg-field-'.$i]:'';
                    $arr_fields[] = $index;
                    $arr_updateentries['FIELD'.$index] = isset($arr_requestdata['reg-date-'.$i])?'"'.$arr_requestdata['reg-date-'.$i].'"':'""';
                }
                $this->AttendanceRegister->updateAll(
                    $arr_updateentries,
                    array('AttendanceRegister.registerid' => $registerid)
                );
                
                //Check if all fields updated
                $arr_register = Set::extract('/AttendanceRegister/.', $this->AttendanceRegister->find("first", array("conditions" => array('registerid'=>$registerid))));
                
                $canverify = true;
                
                foreach($arr_fields as $index){
                    if($arr_register[0]['FIELD'.$index] == 'null' || $arr_register[0]['FIELD'.$index] == ''){
                        $canverify = false;
                    }
                }
                
                if($canverify){
                    //verify register entry
                    $this->verifyregisterentries($registerid);
                }else{
                    echo json_encode(array('success'=>2));
                }
            }
        }else{
            echo json_encode(array('success'=>0));
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
    
    public function createDateRangeArray($strDateFrom,$strDateTo)
    {
        // takes two dates formatted as YYYY-MM-DD and creates an
        // inclusive array of the dates between the from and to dates.

        // could test validity of dates here but I'm already doing
        // that in the main script

        $aryRange=array();

        $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
        $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

        if ($iDateTo>=$iDateFrom)
        {
            array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
            while ($iDateFrom<$iDateTo)
            {
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange,date('Y-m-d',$iDateFrom));
            }
        }
        return $aryRange;
    }
}