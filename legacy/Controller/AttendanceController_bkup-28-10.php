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
class AttendanceController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Attendance';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Units', 'AttendanceRegister', 'DbConfig','SalaryHeadItems');
    public $components = array('MasterdataManagement');

    public function showregister() {
        if($this->Session->read('emp_fkey')){
            $emp_pkeys = $this->Session->read('emp_fkey');
        }else
        {
            $emp_pkeys = 0;
        }
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
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
            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('joins' => $joins,"fields" => array("EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name"), "conditions" => $conditions)));
        }else{
            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
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

    public function showregistertab($verified = 0) {
        $this->set('tab', $verified);

        $arr_requestdata = $this->request->data;
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : date('Y-m');
        $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';

        $arr_conditions = array('status' => 1);
        if($branch != ''){
            $arr_conditions['branch_code'] = $branch;
        }
        
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
            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('joins' => $joins,"fields" => array("EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name"), "conditions" => $conditions)));
        }else{
            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
        }
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
        
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $limit = isset($_REQUEST['rows'])?$_REQUEST['rows']:50;
        $page = isset($_REQUEST['page'])?$_REQUEST['page']:1;

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
        
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
            )
        );
        
        $this->datatable["conditions"] = $conditions;
        $resp_register = array();
        $resp_register["rows"] = array();
        $count = $this->AttendanceRegister->find("count", array("conditions" => $conditions));
		$conditions[] = 'EmployeeDetails.status = 1';
                $emp_fkey = $this->Session->read('emp_fkey');
        if(isset($emp_fkey) && $emp_fkey != ''){
            $joins[] = array(
            'table' => 'emp_proff',
            'alias' => 'EmployeeProfessionalDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            );
            $conditions[] = "EmployeeProfessionalDetails.attr1 = '$emp_fkey' ";
        }
        $arr_register = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'order'=>array($sort=>$order), 'limit' => intval($limit), 'offset' => intval($ofst)));
        foreach ($arr_register as $key => $value) {
            $resp_register["rows"][$key] = $value["AttendanceRegister"];

            //Count of present / leave / lop days
            $int_days_present = 0;//count(array_keys($value["AttendanceRegister"], "P"));
            $int_days_leave = 0;//count(array_keys($value["AttendanceRegister"], "L"));
            //$int_days_holidays = 0;//count(array_keys($value["AttendanceRegister"], "HO"));
            $int_days_lop = 0;//count(array_keys($value["AttendanceRegister"], "HO"));
            
			$dayCount = 1;
            foreach($value["AttendanceRegister"] as $key1=>$val){                
                if($dayCount <= $numberOfDays && strpos($key1, "FIELD") === 0){
                    $arr_field = explode('/', $val);                    
                    // code before removing compoff p s $int_days_present += ((isset($arr_field[0]) && ($arr_field[0] == 'P' || $arr_field[0] == 'WFH' || $arr_field[0] == 'COFF' || $arr_field[0] == 'NA'))?((count($arr_field) == 1)?1:0.5):0)+((isset($arr_field[1]) && ($arr_field[1] == 'P' || $arr_field[1] == 'WFH' || $arr_field[1] == 'COFF' || $arr_field[1] == 'NA'))?0.5:0);
                $int_days_present += ((isset($arr_field[0]) && ($arr_field[0] == 'P' || $arr_field[0] == 'WFH' || $arr_field[0] == 'NA'))?((count($arr_field) == 1)?1:0.5):0)+((isset($arr_field[1]) && ($arr_field[1] == 'P' || $arr_field[1] == 'WFH' || $arr_field[1] == 'NA'))?0.5:0);
                    //$int_days_present += (strpos($val, '/') != FALSE)?substr_count($val,'P')/2:0;
                    
                    //Moved $int_days_leave calculation to js on formatRegisterEntry() method
                    //$int_days_leave += substr_count($val,'FHL')/2 + substr_count($val,'SHL')/2 + substr_count($val,'FDL'); 
                    /*foreach ($arr_field as $half){
                        if(in_array(strtoupper($half), $arr_leavetypes)){
                            $int_days_leave += 1/2;
                        }
                    }*/
                    
        			//On 31 JUly 2016
                    foreach ($arr_field as $half){
						if(count($arr_field)==1){
							if(in_array(strtoupper($half), $arr_leaveabbr)){
								$int_days_leave += 1;
							}
						}else{
							if(in_array(strtoupper($half), $arr_leaveabbr)){
								$int_days_leave += 1/2;
							}
						}
					}
                    
                    //$int_days_holidays += (($val=="H")?1:0);
					
					//On 23 Aug 2016
					//$int_days_lop += (strpos($val, '/') != FALSE)?substr_count($val,'LOP')/2:substr_count($val,'LOP');
					//if( $value["AttendanceRegister"]['registerid'] == 2289) echo "#".$val."#".PHP_EOL;
                    $int_days_lop += (strpos($val, '/') != FALSE)?substr_count($val,'LOP')/2:(empty($val)?1:substr_count($val,'LOP'));
					
					$dayCount++;
                }
            }
            $resp_register["rows"][$key]['days_present'] = $int_days_present;
            $resp_register["rows"][$key]['days_leave'] = 0;//$int_days_leave;
            //$resp_register["rows"][$key]['days_holidays'] = $int_days_holidays;
            $resp_register["rows"][$key]['days_lop'] = $int_days_lop;
        }
        $resp_register["total"] = $count;
        echo json_encode($resp_register);
    }

    public function listverifiedregisterentries() {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $limit = isset($_REQUEST['rows'])?$_REQUEST['rows']:50;
        $page = isset($_REQUEST['page'])?$_REQUEST['page']:1;

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
        
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
            )
        );

        $this->datatable["conditions"] = $conditions;
        $resp_register = array();
        $resp_register["rows"] = array();
        $count = $this->AttendanceRegister->find("count", array("conditions" => $conditions));
        $emp_fkey = $this->Session->read('emp_fkey');
        if(isset($emp_fkey) && $emp_fkey != ''){
            $joins[] = array(
            'table' => 'emp_proff',
            'alias' => 'EmployeeProfessionalDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            );
            $conditions[] = "EmployeeProfessionalDetails.attr1 = '$emp_fkey' ";
        }
        $arr_register = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'order'=>array($sort=>$order), 'limit' => intval($limit), 'offset' => intval($ofst)));
        foreach ($arr_register as $key => $value) {
            $resp_register["rows"][$key] = $value["AttendanceRegister"];
            
            $int_days_present = isset($value["AttendanceRegister"]['presant_total'])?$value["AttendanceRegister"]['presant_total']:0;
            $int_days_leave = isset($value["AttendanceRegister"]['leave_total'])?$value["AttendanceRegister"]['leave_total']:0;
            $int_days_lop = isset($value["AttendanceRegister"]['lop_total'])?$value["AttendanceRegister"]['lop_total']:0;
            
            $resp_register["rows"][$key]['days_present'] = $int_days_present;
            $resp_register["rows"][$key]['days_leave'] = $int_days_leave;
            $resp_register["rows"][$key]['days_lop'] = $int_days_lop;
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
        
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->SalaryHeadItems->query("select UCASE(ifnull(occurance,'LOP')) AS abbr from salary_head_items where ucase(item_type)='LEAVE' AND occurance != 'LOP'");
        $arr_leaveabbr = array();
        foreach ($arr_leavetypes as $leaveabbr => $leave) {
            $arr_leaveabbr[] = strtoupper($leave[0]['abbr']);
        }
        
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
                
            $arr_count_days = array();
            if (!empty($ar_ids)) {                
                $arr_register = $this->AttendanceRegister->find("all", array(
                        'fields' => 'AttendanceRegister.*',
                        'conditions' => array(
                            'AttendanceRegister.registerid' => $ar_ids
                        )
                    )
                );
                foreach ($arr_register as $key => $value) {
                    $reg_id = isset($value["AttendanceRegister"]['registerid'])?$value["AttendanceRegister"]['registerid']:0;
                    //Count of present / leave / lop days
                    $int_days_present = 0;
                    $int_days_leave = 0;
                    $int_days_lop = 0;
					
					$dayCount = 1;
                    foreach($value["AttendanceRegister"] as $key1=>$val){                
                        if($dayCount <= $numberOfDays && strpos($key1, "FIELD") === 0){
                            $arr_field = explode('/', $val);                    
                            //$int_days_present += ((isset($arr_field[0]) && $arr_field[0] == 'P')?0.5:0)+((isset($arr_field[1]) && $arr_field[1] == 'P')?0.5:0);
                            //$int_days_present += ((isset($arr_field[0]) && ($arr_field[0] == 'P' || $arr_field[0] == 'WFH' || $arr_field[0] == 'COFF' || $arr_field[0] == 'NA'))?((count($arr_field) == 1)?1:0.5):0)+((isset($arr_field[1]) && ($arr_field[1] == 'P' || $arr_field[1] == 'WFH' || $arr_field[1] == 'COFF' || $arr_field[1] == 'NA'))?0.5:0); Backup exclude N/A 02-01-2017
                            // Before removing COMPOFF from present days $int_days_present += ((isset($arr_field[0]) && ($arr_field[0] == 'P' || $arr_field[0] == 'WFH' || $arr_field[0] == 'COFF'))?((count($arr_field) == 1)?1:0.5):0)+((isset($arr_field[1]) && ($arr_field[1] == 'P' || $arr_field[1] == 'WFH' || $arr_field[1] == 'COFF'))?0.5:0);
                              $int_days_present += ((isset($arr_field[0]) && ($arr_field[0] == 'P' || $arr_field[0] == 'WFH'))?((count($arr_field) == 1)?1:0.5):0)+((isset($arr_field[1]) && ($arr_field[1] == 'P' || $arr_field[1] == 'WFH'))?0.5:0);
                            //$int_days_present += (strpos($val, '/') != FALSE)?substr_count($val,'P')/2:0;
                            //$int_days_leave += substr_count($val,'FHL')/2 + substr_count($val,'SHL')/2 + substr_count($val,'FDL');
                            foreach ($arr_field as $half){
                                if(count($arr_field)==1){
                                    if(in_array(strtoupper($half), $arr_leaveabbr)){
                                        $int_days_leave += 1;
                                    }
                                }else{
                                    if(in_array(strtoupper($half), $arr_leaveabbr)){
                                        $int_days_leave += 1/2;
                                    }
                                }
                            }
                            
							//On 23 Aug 2016
                            //$int_days_lop += (strpos($val, '/') != FALSE)?substr_count($val,'LOP')/2:substr_count($val,'LOP');
							$int_days_lop += (strpos($val, '/') != FALSE)?substr_count($val,'LOP')/2:(($val == '')?0:substr_count($val,'LOP'));
							$dayCount++;
                        }
                    }
                    $arr_count_days[$reg_id]['days_present'] = $int_days_present;
                    $arr_count_days[$reg_id]['days_leave'] = $int_days_leave;
                    $arr_count_days[$reg_id]['days_lop'] = $int_days_lop;                    
                }
            
                foreach ($ar_ids as $key => $val){                    
                    $this->AttendanceRegister->updateAll(
                        array(
                            'isdelete' => "'N'",
                            'presant_total' => $arr_count_days[$val]['days_present'],
                            'leave_total' => $arr_count_days[$val]['days_leave'],
                            'lop_total' => $arr_count_days[$val]['days_lop']
                        ),
                        array(
                            'AttendanceRegister.registerid' => $val
                        )
                    );
                }
                $result['success'] = 1;
            } else {
                $result['success'] = 0;
            }
        } else if ($registerid != 0) {            
                            
            $arr_register = $this->AttendanceRegister->find("all", array(
                    'fields' => 'AttendanceRegister.*',
                    'conditions' => array(
                        'AttendanceRegister.registerid' => $register_id
                    )
                )
            );
            
            foreach ($arr_register as $key => $value) {
            
                //Count of present / leave / lop days
                $int_days_present = 0;
                $int_days_leave = 0;
                $int_days_lop = 0;
                
				$dayCount = 1;
                foreach($value["AttendanceRegister"] as $key1=>$val){                
                    if($dayCount <= $numberOfDays && strpos($key1, "FIELD") === 0){
                        //$int_days_present += (strpos($val, '/') != FALSE)?substr_count($val,'P')/2:0;
                        $arr_field = explode('/', $val);
                        //$int_days_present += ((isset($arr_field[0]) && ($arr_field[0] == 'P' || $arr_field[0] == 'WFH' || $arr_field[0] == 'COFF' || $arr_field[0] == 'NA'))?((count($arr_field) == 1)?1:0.5):0)+((isset($arr_field[1]) && ($arr_field[1] == 'P' || $arr_field[1] == 'WFH' || $arr_field[1] == 'COFF' || $arr_field[1] == 'NA'))?0.5:0);bakups
                        $int_days_present += ((isset($arr_field[0]) && ($arr_field[0] == 'P' || $arr_field[0] == 'WFH' || $arr_field[0] == 'COFF'))?((count($arr_field) == 1)?1:0.5):0)+((isset($arr_field[1]) && ($arr_field[1] == 'P' || $arr_field[1] == 'WFH' || $arr_field[1] == 'COFF'))?0.5:0);
                        //$int_days_leave += substr_count($val,'FHL')/2 + substr_count($val,'SHL')/2 + substr_count($val,'FDL');
                        foreach ($arr_field as $half){
                            if(count($arr_field)==1){
                                if(in_array(strtoupper($half), $arr_leaveabbr)){
                                    $int_days_leave += 1;
                                }
                            }else{
                                if(in_array(strtoupper($half), $arr_leaveabbr)){
                                    $int_days_leave += 1/2;
                                }
                            }
                        }
                        
						//On 23 Aug 2016
                        //$int_days_lop += (strpos($val, '/') != FALSE)?substr_count($val,'LOP')/2:substr_count($val,'LOP');
						$int_days_lop += (strpos($val, '/') != FALSE)?substr_count($val,'LOP')/2:(($val == '')?0:substr_count($val,'LOP'));
						$dayCount++;
                    }
                }
                
                $this->AttendanceRegister->updateAll(
                    array(
                        'isdelete' => "'N'",
                        'presant_total' => $int_days_present,
                        'leave_total' => $int_days_leave,
                        'lop_total' => $int_days_lop
                    ),
                    array(
                        'AttendanceRegister.registerid' => $registerid
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

            $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
            $arr_register = Set::extract('/AttendanceRegister/.', $this->AttendanceRegister->find("first", array("conditions" => array('registerid' => $registerid))));

            /*
             * on 27 Dec 2015
             */
            /*for($i=1;$i<=$numberDays+1;$i++){
                if($arr_register[0]['FIELD'.$i] == 'null' || $arr_register[0]['FIELD'.$i] == ''){
                    $arr_misspunched_dates[$i] = $arr_dates_between[$i-1];
                }
            }*/
            
            /*
             * FIELD1 ==> Attendance for the Attendance Start Date, so process by this criteria
             * On 10 Sep 2016
             */
            /*$arr_misspunched_dates = array();
            foreach ($arr_dates_between as $date) {
                $day = date('j', strtotime($date));
                if ($arr_register[0]['FIELD' . $day] == 'null' || $arr_register[0]['FIELD' . $day] == '' || $arr_register[0]['FIELD' . $day] == 'A/A') {
                    $arr_misspunched_dates[] = $date;
                } else if (is_array(explode('/', $arr_register[0]['FIELD' . $day]))) {
                    $arr_entry = explode('/', $arr_register[0]['FIELD' . $day]);
                    if (isset($arr_entry[0]) && ($arr_entry[0] == '' || $arr_entry[0] == 'A')) {
                        $arr_misspunched_dates[] = array($date, 1);
                    }
                    if (isset($arr_entry[1]) && ($arr_entry[1] == '' || $arr_entry[1] == 'A')) {
                        $arr_misspunched_dates[] = array($date, 2);
                    }
                }
            }*/
            
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
        if ($registerid != 0) {
            $arr_requestdata = $this->request->data;
            $json_dates = $arr_requestdata['dates'];
            $arr_dates = json_decode($json_dates);
            $this->set('arr_dates', $arr_dates);
        }
    }

    public function submitregisterentry() {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;
        //debug($arr_requestdata);die();
        $registerid = isset($arr_requestdata['hid-registerid']) ? $arr_requestdata['hid-registerid'] : 0;
        $count = isset($arr_requestdata['hid-count-missing']) ? $arr_requestdata['hid-count-missing'] : 0;
        if ($registerid != 0) {
            if ($count != 0) {
                $arr_fields = array();
                
                //Save process starts
                $arr_register_before_save = Set::extract('/AttendanceRegister/.', $this->AttendanceRegister->find("first", array("conditions" => array('registerid' => $registerid))));
                $arr_updateentries = array();
                for ($i = 0; $i < $count; $i++) {
                    $index = isset($arr_requestdata['hid-reg-field-' . $i]) ? $arr_requestdata['hid-reg-field-' . $i] : '';
                    $arr_fields[] = $index;
                    $half = isset($arr_requestdata['hid-reg-field-half-' . $i]) ? $arr_requestdata['hid-reg-field-half-' . $i] : '';
                    if($half != ''){
                        //Update half
                        $fieldentry = isset($arr_updateentries['FIELD' . $index]) ? str_replace('"', '', $arr_updateentries['FIELD' . $index]) : (isset($arr_register_before_save[0]['FIELD' . $index]) ? $arr_register_before_save[0]['FIELD' . $index] : '');
                        if($half == 1){
                            $fieldentry_for_half_to_save = (isset($arr_requestdata['reg-date-' . $i]) && $arr_requestdata['reg-date-' . $i] != '') ? $arr_requestdata['reg-date-' . $i] : substr($fieldentry, 0, strpos($fieldentry, '/'));
                            $arr_updateentries['FIELD' . $index] = '"' . substr_replace($fieldentry, $fieldentry_for_half_to_save, 0, strpos($fieldentry, '/')) . '"';
                        }else{
                            $fieldentry_for_half_to_save = (isset($arr_requestdata['reg-date-' . $i]) && $arr_requestdata['reg-date-' . $i] != '') ? $arr_requestdata['reg-date-' . $i] : substr($fieldentry, strpos($fieldentry, '/')+1);
                            $arr_updateentries['FIELD' . $index] = '"' . substr_replace($fieldentry, $fieldentry_for_half_to_save, strpos($fieldentry, '/')+1) . '"';
                        }
                    }else{
                        $arr_updateentries['FIELD' . $index] = isset($arr_requestdata['reg-date-' . $i]) ? '"' . $arr_requestdata['reg-date-' . $i] . '"' : '""';
                    }
                }
                $this->AttendanceRegister->updateAll(
                        $arr_updateentries, array('AttendanceRegister.registerid' => $registerid)
                );
                //ends

                /*//Verification process starts
                //Check if all fields updated
                $arr_register = Set::extract('/AttendanceRegister/.', $this->AttendanceRegister->find("first", array("conditions" => array('registerid' => $registerid))));
                $canverify = true;
                foreach ($arr_fields as $index) {
                    //On 02 Mar 2016
                    //if($arr_register[0]['FIELD'.$index] == 'null' || $arr_register[0]['FIELD'.$index] == ''){
                    //    $canverify = false;
                    //}
                    if ($arr_register[0]['FIELD' . $index] == 'null' || $arr_register[0]['FIELD' . $index] == '' || $arr_register[0]['FIELD' . $index] == 'A/A') {
                        $canverify = false;
                    } else if (is_array(explode('/', $arr_register[0]['FIELD' . $index]))) {
                        $arr_entry = explode('/', $arr_register[0]['FIELD' . $index]);
                        if (isset($arr_entry[0]) && ($arr_entry[0] == '' || $arr_entry[0] == 'A')) {
                            $canverify = false;
                        } else if (isset($arr_entry[1]) && ($arr_entry[1] == '' || $arr_entry[1] == 'A')) {
                            $canverify = false;
                        }
                    }
                }
                if ($canverify) {
                    //verify register entry
                    $this->verifyregisterentries($registerid);
                } else {
                    echo json_encode(array('success' => 2));
                }*/
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
         $this->AttendanceRegister->useDbConfig = $this->Session->read('ds'); 
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
        
        
        $conditions = array('AttendanceRegister.isdelete="N"');
        if (isset($branch) && $branch != '') {
            $conditions[] = 'AttendanceRegister.branch_code="' . $branch. '"';
        }
        if (isset($emp) && $emp != 'null') {
            $conditions[] = 'AttendanceRegister.emp_fkey=' . $emp;
        }
        if (isset($month) && $month != '') {
            $conditions[] = 'AttendanceRegister.month_year="' . $month . '"';
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
            )
        );

        $this->datatable["conditions"] = $conditions;
        $resp_register = array();
       // $resp_register["rows"] = array();
        $count = $this->AttendanceRegister->find("count", array("conditions" => $conditions));
        $arr_register = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions));
       //debug($arr_register);
        foreach ($arr_register as $key => $value) {
            $resp_register[$key] = $value["AttendanceRegister"];
            
            $int_days_present = isset($value["AttendanceRegister"]['presant_total'])?$value["AttendanceRegister"]['presant_total']:0;
            $int_days_leave = isset($value["AttendanceRegister"]['leave_total'])?$value["AttendanceRegister"]['leave_total']:0;
            $int_days_lop = isset($value["AttendanceRegister"]['lop_total'])?$value["AttendanceRegister"]['lop_total']:0;
            
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