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
class SiteAttendanceManageController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'SiteAttendanceManage';
    public $datatable;
                
    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Units', 'AttendanceRegister','Siteattendanceregister','Site','SiteAttendance', 'DbConfig','SalaryHeadItems','LeaveRequests');
    public $components = array('MasterdataManagement');

    public function showregister() {
       if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
        } else {
		 
            $emp_pkeys = 0;
        }
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $joins = array(
                array(
                    'table' => 'branches',
                    'alias' => 'Branches',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array(
                        'Branches.branch_code = EmployeeDetails.branch_code'
                    )
                )
            );
            $emp_branch = $this->EmployeeDetails->find("all", array("fields" => "Branches.id,Branches.branch_code,Branches.branch_name", "joins" => $joins, "conditions" => array("emp_pkey" => $cur_emp_key, "EmployeeDetails.status" => 1)));
            $arr_branches = array(
                array(
                    'id' => $emp_branch[0]['Branches']['id'],
                    'branch_code' => $emp_branch[0]['Branches']['branch_code'],
                    'branch_name' => $emp_branch[0]['Branches']['branch_name']
                )
            );
        }else{
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
        }
        $this->set('arr_branches', $arr_branches);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
        if(isset($emp_fkey) && $emp_fkey != ''){
            $joins = array(
            array(
            'table' => 'emp_proff',
            'alias' => 'EmployeeProfessionalDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
            );
            $conditions  =   array('status'=>1,'EmployeeProfessionalDetails.attr1'=>$emp_fkey);
            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order'=> array('emp_pkey DESC'), 'joins' => $joins,"fields" => array("EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name"), "conditions" => $conditions)));
        }else{
            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order'=> array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
        }
        $this->set('arr_employees', $arr_employees);

        $arr_registerentries = array(
            'P' => array(
                'label' => 'Present',
                'color' => 'green',
                'textColor' => 'white'
            ),
            /*'L' => array(
                'label' => 'On Leave',
                'color' => 'orange',
                'textColor' => 'white'
            ),*/
            /*'FDL' => array(
                'label' => 'Full Day Leave',
                'color' => 'orange',
                'textColor' => 'white'
            ),
            'FHL' => array(
                'label' => 'First Half Leave',
                'color' => 'orange',
                'textColor' => 'white'
            ),
            'SHL' => array(
                'label' => 'Second Half Leave',
                'color' => 'orange',
                'textColor' => 'white'
            ),*/
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
        
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->SalaryHeadItems->query("select UCASE(ifnull(occurance,'LOP')) AS abbr,item from salary_head_items where ucase(item_type)='LEAVE' AND occurance != 'LOP'");
        $arr_leaveabbr = array();
        foreach ($arr_leavetypes as $leaveabbr => $leave) {
            $arr_registerentries[$leave[0]['abbr']] = array(
                'label' => $leave['salary_head_items']['item'],
                'color' => 'orange',
                'textColor' => 'white'
            );
            $arr_leaveabbr[] = strtoupper($leave[0]['abbr']);
        }
        //On 31 July 2016
        //$arr_leaveabbr[] = "LOP";
        $this->set('str_leaveabbr', implode('#', $arr_leaveabbr));
        
        $this->set('arr_registerentries', $arr_registerentries);
    }
      public function jsons($branchget = '') {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//            debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branchget == '0') {
            
             $branch_condition = "";
        } else {
           $branch_condition = "where site_fkey = $branchget";
        }
        if ($q != null) {
            $q_condition = "and first_name like '%$q%'";
        } else {
            $q_condition = "";
        }
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $emp_condition = "and emp_proff.attr1 = '$emp_pkeys' ";
        } else {
            $emp_condition = "";
        }
//        $branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where status = 1  $q_condition $emp_condition ORDER BY emp_pkey DESC ");
        $branch_array = $this->EmployeeDetails->query("Select emp_pkey,first_name,last_name,emp_id From emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) 
                                                       where  status = 1 and emp_details.emp_pkey in (select emp_pkey from emp_detail_timeattandance $branch_condition)  ORDER BY emp_pkey");
//        debug("Select emp_pkey,first_name,last_name,emp_id From emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) 
//                                                       where  status = 1 and emp_details.emp_pkey in (select emp_pkey from emp_detail_timeattandance $branch_condition)  ORDER BY emp_pkey");
//        debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
      $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_details']['emp_id']
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
    public function showregistertab($verified = 0) {
        $this->set('tab', $verified);

        $arr_requestdata = $this->request->data;
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : date('Y-m');
        $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
//Employee Company ID added by ***ARUL P DAS on 12/12/2019
//        $arr_conditions = array('status' => 1);
//        if($branch != ''){
//            $arr_conditions['branch_code'] = $branch;
//        }
//        
//        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//        $emp_fkey = $this->Session->read('emp_fkey');
//        if(isset($emp_fkey) && $emp_fkey != ''){
//            $joins = array(
//            array(
//            'table' => 'emp_proff',
//            'alias' => 'EmployeeProfessionalDetails',
//            'type' => 'LEFT',
//            'foreignKey' => false,
//            'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
//            )
//            );
//            $conditions  =   array('status'=>1,'EmployeeProfessionalDetails.attr1'=>$emp_fkey);
//            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order'=> array('emp_pkey DESC'), 'joins' => $joins,"fields" => array("EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name"), "conditions" => $conditions)));
//        }else{
//            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order'=> array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
//        }
//        $this->set('arr_employees', $arr_employees);
        $arr_conditions = "";
        if ($branch != '') {
            $user_group = $this->Session->read('user_group');
            if ($user_group != 2) {
                $arr_conditions .= ' and emp_details.branch_code="' . $branch . '"';
            }
        }

        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $arr_conditions .= ' and emp_details.branch_code="' . $cur_emp_branch . '"';
																																																																																   
			  
																																																																					 
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 ' . $arr_conditions);

//        $emp_fkey = $this->Session->read('emp_fkey');
//        if (isset($emp_fkey) && $emp_fkey != '') {
//            $arr_conditions .= ' and emp_proff.attr1=' . $emp_fkey . '';
//            $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 ' . $arr_conditions);
//        } else {
//            $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 ');
//        }
        $this->set('arr_employees', $arr_employees);
        //end Employee company id added
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
        //$date_start = date('Y-m-d', strtotime('-1 months', strtotime($month . '-' . $att_startdate)));
        //$date_end = date('Y-m-d', strtotime($month . '-' . $att_enddate));        
        //02 March 2016
        $date_end = date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        $date_start = date('Y-m-d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month))))))))); 
        //$date_start = current($arr_dates);
        //$date_end = end($arr_dates);		

        $this->set('date_start', $date_start);
        $this->set('date_end', $date_end);
    }

    /*
     * List attendance register
     * By santhosh on 02 Aug 2015
     */

    public function listregisterentries() {
        $this->autoRender = FALSE;
		
		$month = isset($_REQUEST['month'])?$_REQUEST['month']:'';
		//Fetch company's attendance end date
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month))));
        $att_startdate = strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month))))))));
		$datediff = $att_enddate - $att_startdate;
		$numberOfDays = floor($datediff/(60*60*24))+1;
		
        //On 31 JUly 2016
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->SalaryHeadItems->query("select UCASE(ifnull(occurance,'LOP')) AS abbr from salary_head_items where ucase(item_type)='LEAVE' AND occurance != 'LOP'");
        $arr_leaveabbr = array();
        foreach ($arr_leavetypes as $leaveabbr => $leave) {
            $arr_leaveabbr[] = strtoupper($leave[0]['abbr']);
        }
        
        $this->Siteattendanceregister->useDbConfig = $this->Session->read('ds');
        $limit = isset($_REQUEST['rows'])?$_REQUEST['rows']:50;
        $page = isset($_REQUEST['page'])?$_REQUEST['page']:1;

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
        
        $ofst = ($page - 1) * $limit;
		
        $conditions = array('Siteattendanceregister.isdelete="Y"');
//        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
//            $conditions[] = 'Siteattendanceregister.branch_code="' . $_REQUEST['branch'] . '"';
//        }
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $user_group = $this->Session->read('user_group');
            if ($user_group != 2) {
                $conditions[] = 'Siteattendanceregister.branch_code="' . $_REQUEST['branch'] . '"';
            }
        }

        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $conditions[] = 'Siteattendanceregister.branch_code="' . $cur_emp_branch . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $conditions[] = 'Siteattendanceregister.emp_fkey=' . $_REQUEST['employee'];
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $conditions[] = 'Siteattendanceregister.month_year="' . $_REQUEST['month'] . '"';
        } else {
            $conditions[] = 'Siteattendanceregister.month_year="' . date('Y-m', strtotime(date('M-Y'))) . '"';
        }
		
        $fields = 'Siteattendanceregister.*';
        $joins = array(
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Siteattendanceregister.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Siteattendanceregister.emp_fkey = EmployeeDetails.emp_pkey',
                    'EmployeeDetails.status=1'
                )
            )
        );
        
        $this->datatable["conditions"] = $conditions;
        $resp_register = array();
        $resp_register["rows"] = array();
        $count = $this->Siteattendanceregister->find("count", array("conditions" => $conditions));
		$conditions[] = 'EmployeeDetails.status = 1';
                $emp_fkey = $this->Session->read('emp_fkey');
//        if(isset($emp_fkey) && $emp_fkey != ''){
//            $joins[] = array(
//            'table' => 'emp_proff',
//            'alias' => 'EmployeeProfessionalDetails',
//            'type' => 'LEFT',
//            'foreignKey' => false,
//            'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
//            );
//            $conditions[] = "EmployeeProfessionalDetails.attr1 = '$emp_fkey' ";
//        }
        $arr_register = $this->Siteattendanceregister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'order'=>array($sort=>$order), 'limit' => intval($limit), 'offset' => intval($ofst)));
        foreach ($arr_register as $key => $value) {
            $resp_register["rows"][$key] = $value["Siteattendanceregister"];

            //Count of present / leave / lop days
            $int_days_present = 0;//count(array_keys($value["Siteattendanceregister"], "P"));
            $int_days_leave = 0;//count(array_keys($value["AttendanceRegister"], "L"));
            //$int_days_holidays = 0;//count(array_keys($value["AttendanceRegister"], "HO"));
            $int_days_lop = 0;//count(array_keys($value["AttendanceRegister"], "HO"));
            
			$dayCount = 1;
            foreach($value["Siteattendanceregister"] as $key1=>$val){                
                if($dayCount <= $numberOfDays && strpos($key1, "FIELD") === 0){
                 $arr_field = explode('/', $val);   
                        $int_days_present += ((isset($arr_field[0]) && ($arr_field[0] > 0 ))?1:0);
                         if($arr_field[0] == '0'){
                                        $int_days_lop += 1;
                                    }
                        $dayCount++;
                }
            }
            $resp_register["rows"][$key]['days_present'] = $int_days_present;
           // $resp_register["rows"][$key]['days_leave'] = 0;//$int_days_leave;
            //$resp_register["rows"][$key]['days_holidays'] = $int_days_holidays;
            $resp_register["rows"][$key]['days_lop'] = $int_days_lop;
        }
        $resp_register["total"] = $count;
        echo json_encode($resp_register);
    }

    public function listverifiedregisterentries() {
        $this->autoRender = FALSE;
        $this->Siteattendanceregister->useDbConfig = $this->Session->read('ds');
        $limit = isset($_REQUEST['rows'])?$_REQUEST['rows']:50;
        $page = isset($_REQUEST['page'])?$_REQUEST['page']:1;

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
        
        $ofst = ($page - 1) * $limit;

        $conditions = array('Siteattendanceregister.isdelete="N"');
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $conditions[] = 'Siteattendanceregister.emp_fkey="' . $_REQUEST['employee'] . '"';
        }
//        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
//            $conditions[] = 'Siteattendanceregister.branch_code="' . $_REQUEST['branch'] . '"';
//        }
          if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $user_group = $this->Session->read('user_group');
            if ($user_group != 2) {
                $conditions[] = 'Siteattendanceregister.branch_code="' . $_REQUEST['branch'] . '"';
            }
        }

        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $conditions[] = 'Siteattendanceregister.branch_code="' . $cur_emp_branch . '"';
        }
//        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
//            $conditions[] = 'AttendanceRegister.emp_fkey=' . $_REQUEST['employee'];
//        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $conditions[] = 'Siteattendanceregister.month_year="' . $_REQUEST['month'] . '"';
        } else {
            $conditions[] = 'Siteattendanceregister.month_year="' . date('Y-m', strtotime(date('M-Y'))) . '"';
        }

        $fields = 'Siteattendanceregister.*';
        $joins = array(
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Siteattendanceregister.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Siteattendanceregister.emp_fkey = EmployeeDetails.emp_pkey',
                    'EmployeeDetails.status=1'
                )
            )
        );

        $this->datatable["conditions"] = $conditions;
        $resp_register = array();
        $resp_register["rows"] = array();
        $count = $this->Siteattendanceregister->find("count", array("conditions" => $conditions));
        $emp_fkey = $this->Session->read('emp_fkey');
//        if(isset($emp_fkey) && $emp_fkey != ''){
//            $joins[] = array(
//            'table' => 'emp_proff',
//            'alias' => 'EmployeeProfessionalDetails',
//            'type' => 'LEFT',
//            'foreignKey' => false,
//            'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
//            );
//            $conditions[] = "EmployeeProfessionalDetails.attr1 = '$emp_fkey' ";
//        }
        $arr_register = $this->Siteattendanceregister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'order'=>array($sort=>$order), 'limit' => intval($limit), 'offset' => intval($ofst)));
        //debug($arr_register);
        foreach ($arr_register as $key => $value) {
            $resp_register["rows"][$key] = $value["Siteattendanceregister"];
            
            $int_days_present = isset($value["Siteattendanceregister"]['presant_total'])?$value["Siteattendanceregister"]['presant_total']:0;
            //$int_days_leave = isset($value["Siteattendanceregister"]['leave_total'])?$value["Siteattendanceregister"]['leave_total']:0;
            $int_days_lop = isset($value["Siteattendanceregister"]['lop_total'])?$value["Siteattendanceregister"]['lop_total']:0;
            
            $resp_register["rows"][$key]['days_present'] = $int_days_present;
            //$resp_register["rows"][$key]['days_leave'] = $int_days_leave;
            $resp_register["rows"][$key]['days_lop'] = $int_days_lop;
        }
        $resp_register["total"] = $count;
        echo json_encode($resp_register);
    }

    public function processregisterentries() {
        $this->autoRender = FALSE;
        $this->Siteattendanceregister->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
//        $outputParameter = array();
        $companycode = $this->Session->read('company_code'); //company_code
        $branchcode = (isset($_POST['branch']) && $_POST['branch'] != '') ? $_POST['branch'] : '';
        $userid = $this->Session->read("login_user_id"); //user id
        $monthselected = (isset($_POST['month']) && $_POST['month'] != '') ? date('Y-m-d', strtotime($_POST['month'])) : '';
       // debug("CALL site_insert_update_att_reg('$companycode', '$branchcode', '$userid', '$monthselected', @`Perr_msg`) ");
        $out = $this->Siteattendanceregister->query("CALL site_insert_update_att_reg('$companycode', '$branchcode', '$userid', '$monthselected', @`Perr_msg`) ");
//        insertUpdateAttendanceRegisterProc($outputParameter);
        $result['success'] = 1;
        echo json_encode($result);
    }

    public function verifyregisterentries($registerid = 0) {
        $this->autoRender = FALSE;
		
		$month = isset($_REQUEST['month'])?$_REQUEST['month']:'';
		//Fetch company's attendance end date
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month))));
        $att_startdate = strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month))))))));
	$datediff = $att_enddate - $att_startdate;
	$numberOfDays = floor($datediff/(60*60*24))+1;
  
        $this->Siteattendanceregister->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);

        $arr_requestdata = $this->request->data;
        //Modified On 21 Feb 2016
        if (isset($arr_requestdata["ids"])) {
            //$ar_ids = explode(",", $_REQUEST["ids"]);$arr_requestdata
            $arr_registerids = isset($arr_requestdata["ids"]) ? explode(",", $arr_requestdata["ids"]) : array();
            $ar_ids = array();
            foreach ($arr_registerids as $register_id) {
               $ar_ids[] = $register_id;
//                if (empty(json_decode($this->checkifregistercanverify($register_id, $arr_requestdata)))) {
//                    $ar_ids[] = $register_id;
//                }
            }
                
            $arr_count_days = array();
            if (!empty($ar_ids)) {                
                $arr_register = $this->Siteattendanceregister->find("all", array(
                        'fields' => 'Siteattendanceregister.*',
                        'conditions' => array(
                            'Siteattendanceregister.registerid' => $ar_ids
                        )
                    )
                );
                foreach ($arr_register as $key => $value) {
                    $reg_id = isset($value["Siteattendanceregister"]['registerid'])?$value["Siteattendanceregister"]['registerid']:0;
                    //Count of present / leave / lop days
                    $int_days_present = 0;
                    $int_days_leave = 0;
                    $int_days_lop = 0;
		    $dayCount = 1;
                    foreach($value["Siteattendanceregister"] as $key1=>$val){                
                        if($dayCount <= $numberOfDays && strpos($key1, "FIELD") === 0){
                        $arr_field = explode('/', $val);   
                        $int_days_present += ((isset($arr_field[0]) && ($arr_field[0] > 0 ))?1:0);
                         if($arr_field[0] == '0'){
                                        $int_days_lop += 1;
                                    }
                        $dayCount++;
                        }
                    }
                    $arr_count_days[$reg_id]['days_present'] = $int_days_present;
                    $arr_count_days[$reg_id]['days_leave'] = $int_days_leave;
                    $arr_count_days[$reg_id]['days_lop'] = $int_days_lop;                    
                }
            
                foreach ($ar_ids as $key => $val){                    
                    $this->Siteattendanceregister->updateAll(
                        array(
                            'isdelete' => "'N'",
                            'presant_total' => $arr_count_days[$val]['days_present'],
                            'leave_total' => $arr_count_days[$val]['days_leave'],
                            'lop_total' => $arr_count_days[$val]['days_lop']
                        ),
                        array(
                            'Siteattendanceregister.registerid' => $val
                        )
                    );
                }
                $result['success'] = 1;
            } else {
                $result['success'] = 0;
            }
        } else if ($registerid != 0) {            
                            
            $arr_register = $this->Siteattendanceregister->find("all", array(
                    'fields' => 'Siteattendanceregister.*',
                    'conditions' => array(
                        'Siteattendanceregister.registerid' => $register_id
                    )
                )
            );
            
            foreach ($arr_register as $key => $value) {
            
                //Count of present / leave / lop days
                $int_days_present = 0;
                $int_days_leave = 0;
                $int_days_lop = 0;
                $dayCount = 1;
                foreach($value["Siteattendanceregister"] as $key1=>$val){                
                    if($dayCount <= $numberOfDays && strpos($key1, "FIELD") === 0){
                        $arr_field = explode('/', $val);   
                        $int_days_present += ((isset($arr_field[0]) && ($arr_field[0] > 0 )));
                        $int_days_lop += ((isset($arr_field[0]) && ($arr_field[0] = 0 )));
                        $dayCount++;
                    }
                }
                
                $this->Siteattendanceregister->updateAll(
                    array(
                        'isdelete' => "'N'",
                        'presant_total' => $int_days_present,
                        'leave_total' => $int_days_leave,
                        'lop_total' => $int_days_lop
                    ),
                    array(
                        'Siteattendanceregister.registerid' => $registerid
                    )
                );             
            }
            $result['success'] = 1;
        }
        echo json_encode($result);
    }

    public function loadattendanceregisterheader() {
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
    public function checkifregistercanverify($registerid = 0, $arr_requestdata = array()) {
        $this->autoRender = FALSE;
        if (empty($arr_requestdata)) {
            $arr_requestdata = $this->request->data;
        }
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

            $this->Siteattendanceregister->useDbConfig = $this->Session->read('ds');
            $arr_register = Set::extract('/Siteattendanceregister/.', $this->Siteattendanceregister->find("first", array("conditions" => array('registerid' => $registerid))));

            
            $no_days_in_register = count($arr_dates_between);
            $arr_misspunched_date_fields = array();
            $i=1;
            while($i<=$no_days_in_register) {
                if ($arr_register[0]['FIELD' . $i] == 'null' || $arr_register[0]['FIELD' . $i] == '' || $arr_register[0]['FIELD' . $i] == 'A/A') {
                    $arr_misspunched_date_fields[$i] = $arr_dates_between[$i-1];
                } else if (is_array(explode('/', $arr_register[0]['FIELD' . $i]))) {
                    $arr_entry = explode('/', $arr_register[0]['FIELD' . $i]);
                    if (isset($arr_entry[0]) && ($arr_entry[0] == '' || $arr_entry[0] == 'A')) {
                        $arr_misspunched_date_fields[$i] = array($arr_dates_between[$i-1], 1);
                    }
                    if (isset($arr_entry[1]) && ($arr_entry[1] == '' || $arr_entry[1] == 'A')) {
                        $arr_misspunched_date_fields[$i] = array($arr_dates_between[$i-1], 2);
                    }
                }
                $i++;
            }
            //Ends
        }
        return json_encode($arr_misspunched_date_fields);
    }

    public function updateregisterentries($registerid = 0) {
        $this->set('registerid', $registerid);
        $this->Siteattendanceregister->useDbConfig = $this->Session->read('ds');
        if ($registerid != 0) {
            $arr_requestdata = $this->request->data;
            $json_dates = $arr_requestdata['dates'];
            $arr_dates = json_decode($json_dates);
            $this->set('arr_dates', $arr_dates);
//            $emp_pkeys = $this->Siteattendanceregister->query("select month_year,emp_fkey from Siteattendanceregister where registerid = '$registerid' ");
//            $emp_pkey = $emp_pkeys['0']['Siteattendanceregister']['emp_fkey'];
//            $month = $emp_pkeys['0']['Siteattendanceregister']['month_year'];
//            $years = $this->Siteattendanceregister->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = 0 and is_current_finyear = 'Y' and status = '1'");
//            $year = isset($years['0']['fin_year']['fin_year'])?$years['0']['fin_year']['fin_year']:0;
//            $arr_leaves_heads = $this->Siteattendanceregister->query("SELECT salary_head_item_pkey,occurance FROM `salary_head_items` WHERE head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_pkey') and status = 1 ) and occurance != 'LOP' LIMIT 50"); 
//            $arr_leave = array();
//            $this->set('arr_leaves', $arr_leaves_heads);
//            foreach($arr_leaves_heads as $key => $val){
//            $head = $val['salary_head_items']['occurance'];
//            $slary_head_item_pkey = $val['salary_head_items']['salary_head_item_pkey'];
//            $lbalance = $this->Siteattendanceregister->query("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$month','$year') as LeaveBalance");
//            $resp = isset($lbalance['0']['0']['LeaveBalance'])?round($lbalance['0']['0']['LeaveBalance'],1):0;
//            $arr_leave[] = array(
//              "salary_head_item_pkey"=>$slary_head_item_pkey,
//              "Head"=>$head,
//              "leaveBalance"=>$resp
//            );
//            }
             // $this->set('arr_leave', $arr_leave);
        
        }
    }

    public function submitregisterentry() {
        $this->autoRender = FALSE;
        $this->Siteattendanceregister->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;
        //debug($arr_requestdata);die();
        $registerid = isset($arr_requestdata['hid-registerid']) ? $arr_requestdata['hid-registerid'] : 0;
        $count = isset($arr_requestdata['hid-count-missing']) ? $arr_requestdata['hid-count-missing'] : 0;
        $attendance_date = $this->Siteattendanceregister->query("select month_year,emp_fkey from site_attendance_register where registerid = '$registerid' ");
       
        $day = isset($attendance_date['0']['Siteattendanceregister']['month_year'])?$attendance_date['0']['Siteattendanceregister']['month_year']:'';
        $emp_fkey = isset($attendance_date['0']['Siteattendanceregister']['emp_fkey'])?$attendance_date['0']['Siteattendanceregister']['emp_fkey']:'';
        //$arr_leaves = $this->Siteattendanceregister->query("SELECT salary_head_item_pkey,occurance FROM `salary_head_items` WHERE head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_fkey'))  and occurance != 'LOP' LIMIT 50"); 
        //$arr_leave = array();
        //foreach($arr_leaves as $key => $val){
            //$arr_leave[] = $val['salary_head_items']['occurance'];
            //$arr_leave[$key]['pkey'] = $val['salary_head_items']['salary_head_item_pkey'];
        //}
        // debug($arr_leaves);
        if ($registerid != 0) {
            if ($count != 0) {
                $arr_fields = array();
                
                //Save process starts
                $arr_register_before_save = Set::extract('/Siteattendanceregister/.', $this->Siteattendanceregister->find("first", array("conditions" => array('registerid' => $registerid))));
                $arr_updateentries = array();
                for ($i = 0; $i < $count; $i++) {
                    $index = isset($arr_requestdata['hid-reg-field-' . $i]) ? $arr_requestdata['hid-reg-field-' . $i] : '';
                    $arr_fields[] = $index;
                    //$half = isset($arr_requestdata['hid-reg-field-half-' . $i]) ? $arr_requestdata['hid-reg-field-half-' . $i] : '';
//                    if($half != ''){
//                            $leave_head = $arr_requestdata['reg-date-' . $i];
//                            $leave_days = $day."-".$arr_requestdata['hid-reg-field-' . $i];
//                        //Update half
//                        $fieldentry = isset($arr_updateentries['FIELD' . $index]) ? str_replace('"', '', $arr_updateentries['FIELD' . $index]) : (isset($arr_register_before_save[0]['FIELD' . $index]) ? $arr_register_before_save[0]['FIELD' . $index] : '');
//                        if($half == 1){
//                            $fieldentry_for_half_to_save = (isset($arr_requestdata['reg-date-' . $i]) && $arr_requestdata['reg-date-' . $i] != '') ? $arr_requestdata['reg-date-' . $i] : substr($fieldentry, 0, strpos($fieldentry, '/'));
//                            $arr_updateentries['FIELD' . $index] = '"' . substr_replace($fieldentry, $fieldentry_for_half_to_save, 0, strpos($fieldentry, '/')) . '"';
//                            if(in_array($arr_requestdata['reg-date-' . $i],$arr_leave)){
//                                $out = $this->AddHalfLeave($leave_head,$leave_days,$emp_fkey,"1");
//                            }
//                        }else{
//                            $fieldentry_for_half_to_save = (isset($arr_requestdata['reg-date-' . $i]) && $arr_requestdata['reg-date-' . $i] != '') ? $arr_requestdata['reg-date-' . $i] : substr($fieldentry, strpos($fieldentry, '/')+1);
//                            $arr_updateentries['FIELD' . $index] = '"' . substr_replace($fieldentry, $fieldentry_for_half_to_save, strpos($fieldentry, '/')+1) . '"';
//                            if(in_array($arr_requestdata['reg-date-' . $i],$arr_leave)){
//                                $out = $this->AddHalfLeave($leave_head,$leave_days,$emp_fkey,"2");
//                            }
//                        }
//                    }else{
//                        if(in_array($arr_requestdata['reg-date-' . $i],$arr_leave)){
//                            $leave_head = $arr_requestdata['reg-date-' . $i];
//                            $leave_days = $day."-".$arr_requestdata['hid-reg-field-' . $i];
//                            $out = $this->AddLeave($leave_head,$leave_days,$emp_fkey);
//                        }
//                        debug($arr_requestdata['reg-date-' . $i]);
                        if($arr_requestdata['reg-date-' . $i] != '')
                        $arr_updateentries['FIELD' . $index] = isset($arr_requestdata['reg-date-' . $i]) ? '"' .$arr_requestdata['reg-date-' . $i].'"' : '""';
                    //}
                }
                $this->Siteattendanceregister->updateAll(
                        $arr_updateentries, array('Siteattendanceregister.registerid' => $registerid)
                );
                //ends

                echo json_encode(array('success' => 1));
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
    public function createDateRange($startDate, $endDate, $format = "Y-m-d") {
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

    public function AddLeave($head = '',$day = '',$emp_fkey = 0){
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $leaves = array();
        
        $arr_attendance_register = $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$day','%Y-%m') and isdelete='N' and emp_fkey= '$emp_fkey' ");
        if (isset($arr_attendance_register) && $arr_attendance_register['0']['0']['cnt'] != 0) {
            return true;
        }
        
        $arr_leave_check = $this->LeaveRequests->query("Select count(*) from emp_leave_transactions 
                                where leave_date = '$day'   and LEAVEENTRYID in (select LEAVEENTRYID  
                                from leaveentries where EMP_fkey = $emp_fkey) and LEAVESTATUS in ('Applied','Approved','Authorized' ) 
                            ");
        $data = $arr_leave_check['0']['0']['count(*)'];
        if ($data != 0) {
            return true;
        }

        $arr_leaves = $this->LeaveRequests->query("SELECT salary_head_item_pkey FROM `salary_head_items` WHERE `item_type` = 'Leave' AND `status` = '1' and occurance = '$head' LIMIT 50"); 
        $leaveentryId = 0;
        $leaves['salary_head_item_fkey'] = isset($arr_leaves['0']['salary_head_items']['salary_head_item_pkey'])?$arr_leaves['0']['salary_head_items']['salary_head_item_pkey']:0;
        $leaves['applied_date'] = $day;
        $leaves['LEAVESTATUS'] = "Approved";
        $leaves['EMP_fkey'] = $emp_fkey;
        $leaves['FROMDATE'] =  $day;
        $leaves['FROMHALF'] = 1;
        $leaves['TODATE'] = $day;
        $leaves['TOHALF'] = 2;
        $leaves['ISAutherized'] = 1;
        $leaves['ISAutherizedby'] = "0";
        $leaves['Autherized_date'] = date("Y-m-d");
        $leaves['ISAPPROVED'] = 1;
        $leaves['APPROVEDBY'] = "0";
        $leaves['APPROVED_date'] = date("Y-m-d");
        $leaves['Reason'] = "Auto uploaded For Verifying Attendance";
        $leaves['REMARKS'] = "Auto uploaded For Verifying Attendance";
        $leaves['leave_days'] = 1;
        $fromdate = $day;
        $fromhalf = 1;
        $todate = $day;
        $tohalf = 2;
        $leavedays = 1;
        $leavestatus = "Applied";
        $this->LeaveRequests->saveAll($leaves);
        $leaveentryId = $this->LeaveRequests->getLastInsertID();
        $out = $this->LeaveRequests->query("CALL leave_transaction_prc('$leaveentryId','$emp_fkey','$fromdate','$fromhalf','$todate','$tohalf','$leavedays','$leavestatus',@Perror_message);");
        $leavestatuses = "Approved";
        $outs = $this->LeaveRequests->query("CALL leave_transaction_prc('$leaveentryId','$emp_fkey','$fromdate','$fromhalf','$todate','$tohalf','$leavedays','$leavestatuses',@Perror_message);");
        return true;
    }
    
    public function AddHalfLeave($head = '',$day = '',$emp_fkey = 0,$session = ""){
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $leaves = array();
        $arr_leaves = $this->Siteattendanceregister->query("SELECT salary_head_item_pkey FROM `salary_head_items` WHERE `item_type` = 'Leave' AND `status` = '1' and occurance = '$head' LIMIT 50"); 
        $leaveentryId = 0;
        $leaves['salary_head_item_fkey'] = isset($arr_leaves['0']['salary_head_items']['salary_head_item_pkey'])?$arr_leaves['0']['salary_head_items']['salary_head_item_pkey']:0;
        $leaves['applied_date'] = $day;
        $leaves['LEAVESTATUS'] = "Approved";
        $leaves['EMP_fkey'] = $emp_fkey;
        $leaves['FROMDATE'] =  $day;
        $leaves['FROMHALF'] = $session;
        $leaves['TODATE'] = $day;
        $leaves['TOHALF'] = $session;
        $leaves['ISAutherized'] = 1;
        $leaves['ISAutherizedby'] = "0";
        $leaves['Autherized_date'] = date("Y-m-d");
        $leaves['ISAPPROVED'] = 1;
        $leaves['APPROVEDBY'] = "0";
        $leaves['APPROVED_date'] = date("Y-m-d");
        $leaves['Reason'] = "Auto uploaded For Verifying Attendance";
        $leaves['REMARKS'] = "Auto uploaded For Verifying Attendance";
        $leaves['leave_days'] = 0.5;
        $fromdate = $day;
        $fromhalf = $session;
        $todate = $day;
        $tohalf = $session;
        $leavedays = 0.5;
        $leavestatus = "Applied";
        $this->LeaveRequests->saveAll($leaves);
        $leaveentryId = $this->LeaveRequests->getLastInsertID();
        $out = $this->LeaveRequests->query("CALL leave_transaction_prc('$leaveentryId','$emp_fkey','$fromdate','$fromhalf','$todate','$tohalf','$leavedays','$leavestatus',@Perror_message);");
        $leavestatuses = "Approved";
        $outs = $this->LeaveRequests->query("CALL leave_transaction_prc('$leaveentryId','$emp_fkey','$fromdate','$fromhalf','$todate','$tohalf','$leavedays','$leavestatuses',@Perror_message);");
        return true;
    }
        
    public function createDateRangeArray($strDateFrom, $strDateTo) {
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
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }
        return $aryRange;
    }
    public function verifiedpdf($branch,$month,$emp,$mode)
            
    {  
       //echo $emp; die();
        $this->autoRender = FALSE;
         $this->Siteattendanceregister->useDbConfig = $this->Session->read('ds'); 
          $this->Units->useDbConfig = $this->Session->read('ds'); 
           $this->EmployeeDetails->useDbConfig = $this->Session->read('ds'); 
         $arr_registerentries = array(
            'P' => array(
                'label' => 'Present',
                'color' => 'green',
                'textColor' => 'white'
            ),
            /*'L' => array(
                'label' => 'On Leave',
                'color' => 'orange',
                'textColor' => 'white'
            ),*/
            'FDL' => array(
                'label' => 'Full Day Leave',
                'color' => 'orange',
                'textColor' => 'white'
            ),
            'FHL' => array(
                'label' => 'First Half Leave',
                'color' => 'orange',
                'textColor' => 'white'
            ),
            'SHL' => array(
                'label' => 'Second Half Leave',
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
             'COFF' => array(
                'label' => 'COMBO OFF',
                'color' => 'deepskyblue',
                'textColor' => 'white'
            ),
             'WFH' => array(
                'label' => 'Work From Home',
                'color' => 'deepskyblue',
                'textColor' => 'white'
            ),
            'OTHERS' => array(
                'label' => 'Others',
                'color' => 'deepskyblue',
                'textColor' => 'white'
            )
        );
        $this->set('arr_registerentries', $arr_registerentries);
        
        
        $conditions = array('Siteattendanceregister.isdelete="N"');
        if (isset($branch) && $branch != '') {
            $conditions[] = 'Siteattendanceregister.branch_code="' . $branch. '"';
        }
        if (isset($emp) && $emp != 'null') {
            $conditions[] = 'Siteattendanceregister.emp_fkey=' . $emp;
        }
        if (isset($month) && $month != '') {
            $conditions[] = 'Siteattendanceregister.month_year="' . $month . '"';
        } else {
            $conditions[] = 'Siteattendanceregister.month_year="' . date('Y-m', strtotime(date('M-Y'))) . '"';
        }

        $fields = 'Siteattendanceregister.*';
        $joins = array(
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Siteattendanceregister.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Siteattendanceregister.emp_fkey = EmployeeDetails.emp_pkey',
                    'EmployeeDetails.status=1'
                )
            )
        );

        $this->datatable["conditions"] = $conditions;
        $resp_register = array();
       // $resp_register["rows"] = array();
        $count = $this->Siteattendanceregister->find("count", array("conditions" => $conditions));
        $arr_register = $this->Siteattendanceregister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions));
       //debug($arr_register);
        foreach ($arr_register as $key => $value) {
            $resp_register[$key] = $value["Siteattendanceregister"];
            
            $int_days_present = isset($value["Siteattendanceregister"]['presant_total'])?$value["Siteattendanceregister"]['presant_total']:0;
            $int_days_leave = isset($value["Siteattendanceregister"]['leave_total'])?$value["Siteattendanceregister"]['leave_total']:0;
            $int_days_lop = isset($value["Siteattendanceregister"]['lop_total'])?$value["Siteattendanceregister"]['lop_total']:0;
            
            $resp_register[$key]['days_present'] = $int_days_present;
            $resp_register[$key]['days_leave'] = $int_days_leave;
            $resp_register[$key]['days_lop'] = $int_days_lop;
        }
      $branch=$this->Getbranchname($branch);
      //debug($branch);
               $this->set('resp_register',$resp_register); 
         $this->set('month',$month); 
          $this->set('branch',$branch); 
        
 if($mode=='pdf')
    {
                   //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
  // $this->set('mode','pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportverified');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A2', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('Reportverified.pdf', 'D');
    }
    else{
        //echo $mode;
         $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."_verifiedattendance.xlsx":"ShiftPolicy".strtotime().".xlsx";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");            

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Attendance Register for ".$month);
                $worksheet->setCellValueByColumnAndRow(1, 2, $branch);
                  
                  $rowcount = 2;   
                  $columncount=0;
                        
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount).$rowcount, 'Employee NAME');
             $columncount=1;
               for($i=1;$i<=31;$i++) {
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount).$rowcount, $i);
             $columncount++;
               }
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount).$rowcount, 'Days Present');
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount+1).$rowcount, 'Day On Leave');
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount+2).$rowcount, 'Loss Of Pay');
             
             $rowcount = $rowcount+1; 
             foreach ($resp_register as $val) {
              $columncount=0;

                $name = $val['emp_name'];
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount) . $rowcount, $name);
                $columncount = 1;
                for ($i = 1; $i <= 31; $i++) {


                    $FIELD = 'FIELD' . $i;
                    $fl = $val[$FIELD];
                    $color=$this->getcolor($fl);
                   $bak_color= $color['back'];
                   $color="";
                   $color=  trim($bak_color);
                   //echo $color;die();
         $objPHPExcel->getActiveSheet()
    ->getStyleByColumnAndRow($columncount,$rowcount)
    ->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' =>$color)
            )
        )
    );
       

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount) . $rowcount, $fl);
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount,$rowcount)->applyFromArray($styleArray);
        
                    $columncount++;
                }
               // echo $color;die();                      
       // die();
                $dp = $val['days_present'];
                $lea = $val['days_leave'];
                $lop = $val['days_lop'];
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount) . $rowcount, $dp);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 1) . $rowcount, $lea);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 2) . $rowcount, $lop);
                $rowcount++;
            }
            $rowcount=$rowcount+1;
            $columncount=0;
            foreach ($arr_registerentries as $key => $entry) {
               $color=$this->getcolor($key);
                   $bak_color= $color['back'];
                   $color="";
                   $color=  trim($bak_color);
                 $label=$key.':'. $entry['label']; 
                    $objPHPExcel->getActiveSheet()
    ->getStyleByColumnAndRow($columncount,$rowcount)
    ->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' =>$color)
            )
        )
    );     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount) . $rowcount, $label);
          $columncount++;  }
            $worksheet->mergeCells('A1:F1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                  $objPHPExcel->getActiveSheet()->setTitle('Attendance Verfied Report ');

                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__)."/".$file_name);

                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename='.$file_name);                        

                readfile(dirname(__FILE__)."/".$file_name);
                unlink(dirname(__FILE__)."/".$file_name);
    }
    }
    public function  Getbranchname($branchid)
    {
        $this->autoRender = FALSE;
        $this->Units->useDbConfig = $this->Session->read('ds'); 
                 $arr_branches = Set::extract('/Units/.', $this->Units->find("first", array("conditions" => array('branch_code' => $branchid))));
                 $branch=$arr_branches[0]['branch_name'];
        //$this->controller->Units->useDbConfig = $this->Session->read('ds');
		//$arr_branches	=	Set::extract('/Units/.',$this->controller->Units->find('all',array('fields'=>'id,branch_code,branch_name','conditions'=>array('status'=>1))));
		return $branch;
    }
    public function removeAttendanceEntry() {
        $this->autoRender = FALSE;
        $this->Siteattendanceregister->useDbConfig = $this->Session->read('ds');

        $success = 1;
        $arr_requestdata = $this->request->data;
        if (isset($arr_requestdata["payroll_pkey"])) {
            $arr_payroll_pkeys = $arr_requestdata["payroll_pkey"];
            $arr_payroll_pkeys1 = str_replace(",", "','", $arr_payroll_pkeys);
            $this->Siteattendanceregister->query("UPDATE site_attendance_register SET isdelete = 'Y' WHERE registerid in ('$arr_payroll_pkeys1') ");
        }
        $result = array('success' => $success);
        echo json_encode($result);
    }
    public function  getcolor($fl)
    {
                          switch ($fl) {
                case 'P':
                case 'p':
                    $Color['back'] = '008000';
                    $Color['txt'] = 'FFFFFF';
                    break;
                //case 'L':
                //case 'l':
                case 'FDL':
                case 'fdl':
                case 'FHL':
                case 'fhl':
                case 'SHL':
                case 'shl':
                     $Color['back'] = 'FFA500';
                   $Color['txt'] = 'FFFFFF';
                    break;
                case 'WO':
                case 'wo':
                     $Color['back'] = 'FFFF00';
                    $Color['txt'] = '000000';
                    break;
                case 'HO':
                case 'ho':
                     $Color['back'] = '0000FF';
                  $Color['txt'] = 'FFFFFF';
                    break;
                case 'LOP':
                case 'lop':
                     $Color['back']= '800000';
                   $Color['txt'] = 'FFFFFF';
                    break;
                case 'COFF':
                case 'coff':
                case 'WFH':
                case 'wfh':
                case 'NA':
                case 'na':
                case 'OTHERS':
                case 'others':
                    $Color['back'] = '00BFFF ';
                    $Color['txt']= '#FFFFFF';
                    break;
                default:
                     $Color['back'] = 'FF0000';
                   $Color['txt']= 'FFFFFF';
                    break;
                   
            }
         return $Color;       
    }
}

/*function getLeaveCount($leave, $key, $arrParams)
{
    $arrParams[1] = substr_count($arrParams[0],  strtoupper($leave[0]['abbr']))/2;
}*/