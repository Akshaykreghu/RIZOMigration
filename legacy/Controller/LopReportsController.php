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
ini_set('max_execution_time', 200);

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class lopReportsController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'LopReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('LeaveRequests', 'Leavestatus', 'SalaryHeadItems', 'CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'DeviceAttendance', 'Departments', 'Grades', 'Verticals', 'Units', 'ReportCriterias', 'AttendanceRegister', 'AttendanceRegisterReport', 'CompanyContactInfo','LeaveType','ReportAudit'); //santhu
    public $components = array('MasterdataManagement');

    /* public $arr_employee_reportcriterias = array(
      'Departments' => 'belonging to a Department',
      'Grades' => 'belonging to a Grade',
      'Verticals' => 'belonging to a Vertical',
      'Units' => 'belonging to a Branch',
      'EmployeeDetails' => 'randomly, without criteria'
      );

      public $arr_employee_reportcriteria_fields = array(
      'Departments' => 'emp_dept',
      'Grades' => 'emp_grade',
      'Verticals' => 'emp_vertical',
      'Units' => 'emp_branch',
      'EmployeeDetails' => 'emp_pkey'
      ); */

    /*
     * HR Reports landing view
     */

    public function hrreports() {
        $arr_reporttypes = array(
//            'employee' => 'Employee Information',
            // 'LeaveDetaillsReport' =>  'Leave Details Reports',
            'Lop' => 'LOP Detailed'
           
           
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */

    public function changereporttype($type = '') {
        $this->autoRender = FALSE;
        // debug($type);
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                case 'employee':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Lop':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
               
                default :
                    echo "No criterias found";
                    break;
            }
            $this->render('showreport');
        } else {
            echo "No criterias found";
        }
    }

    /*
     * Add criterias
     */

    public function addreportcriteria($type = '', $newindex = '', $str_currentcriterias = '') {
        $this->autoRender = FALSE;
        if ($type != '' && $str_currentcriterias != '') {
            //$arr_currentcriterias = explode(',', $str_currentcriterias);
            //$arr_remainingcriterias = array_diff(array_flip($this->arr_employee_reportcriterias), $arr_currentcriterias);
            //$this->set('arr_remainingcriterias',array_flip($arr_remainingcriterias));
            $str_currentcriterias = "'" . str_replace(",", "','", $str_currentcriterias) . "'";
            $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
            $this->set('arr_remainingcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reportcriteria NOT IN(' . $str_currentcriterias . ')', 'reporttype' => $type)))));

            $this->set('newindex', $newindex);
            $this->render('showcriteria');
        } else {
            return '';
        }
    }

    /*
     * Load criteria items
     */

    public function loadcriteriaitems($index, $str_criteria = '') {
        $this->autoRender = FALSE;
        if ($str_criteria != '') {
            $model = $str_criteria;
            if ($this->_modelExists($model)) {
                $this->set('index', $index);
               
                 $model = ($model == 'Units') ? 'Branches' : $model;
                  $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
                $this->set('criteria', $model);
                $this->render('loadcriteriaitems');
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public function listcriteriaitems($str_criteria = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds'); // Edited by Akshay on 29-1-2025
        $model = $str_criteria;
        $arr_requestdata = $this->request->data;
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');
            $arr_order = array();
            if ($model == 'LeaveType') {
                $conditions = array("head_fkey" => 6, "value" => 'Y', "status" => 1);
            } elseif ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            } elseif ($model == 'Leavestatus') {
                $conditions = array();
            }
            // Edited by Akshay on 29-1-2025
            elseif ($model == 'Units') {
                $conditions = array("status" => 1);
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                    $emp_pkey = $this->Session->read('emp_fkey');
                    $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $conditions = array("status" => 1,"Units.branch_code" => $is_ho);
                    }
                }
            }
            // End
            else {
                $conditions = array("status" => 1);
            }
            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions)));
            $arr_criteriaItems = array();
            $key = 0;
            switch ($model) {
                case 'Departments':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['dept_code'];
                        $arr_criteriaItems[$key]['text'] = $value['dept_name'];
                        $key++;
                    }
                    break;
                case 'Grades':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['grade_code'];
                        $arr_criteriaItems[$key]['text'] = $value['grade_name'];
                        $key++;
                    }
                    break;
                case 'Verticals':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['vert_code'];
                        $arr_criteriaItems[$key]['text'] = $value['vert_name'];
                        $key++;
                    }
                    break;
                case 'LeaveType':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['salary_head_item_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['item'];
                        $key++;
                    }
                    break;
                case 'LeaveRequests':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['LEAVEENTRYID'];
                        $arr_criteriaItems[$key]['text'] = $value['LEAVESTATUS'];
                        $key++;
                    }
                    break;
                case 'Leavestatus':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['LEAVESTATUS'];
                        $arr_criteriaItems[$key]['text'] = $value['LEAVESTATUS'];
                        $key++;
                    }
                    break;
                case 'Units':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['branch_code'];
                        $arr_criteriaItems[$key]['text'] = $value['branch_name'];
                        $key++;
                    }
                    break;

                case 'LeavePolicyGroup':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['LEAVEPOLICY_GROUP_ID'];
                        $arr_criteriaItems[$key]['text'] = $value['LEAVEPOLICY_GROUP_NAME'];
                        $key++;
                    }
                    break;


                case 'EmployeeDetails':
                    //edited by arul - changed empid ad company id
                    $fields = 'emp_pkey,status,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
                    $joins = array(
                        array(
                            'table' => 'emp_proff',
                            'alias' => 'EmployeeProfessionalDetails',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                        )
                    );
                    $conditions = array();


                    $arr_order = array("EmployeeDetails.emp_name" => "ASC");
                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                        $conditions = array("status in(1,2)");
                    } else {

                        $conditions = array("status" => 1);
                    }

                    // Edited by Akshay on 29-1-2025
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $user_group = $this->Session->read('user_group');
                    $user = $this->Session->read('company_code');
                    if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                        $emp_pkey = $this->Session->read('emp_fkey');
                        $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                        $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                        if ($is_ho != 1) {
                            $conditions["EmployeeDetails.branch_code" ] = $is_ho;
                        }
                    }
                    // End

                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions
                    ));
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                        $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];
                        $key++;
                    }
                    break;
                case 'DayTimeProcedures':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['day_time_seq'];
                        $arr_criteriaItems[$key]['text'] = $value['day_time_desc'];
                        $key++;
                    }
                case 'attendance':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['day_time_seq'];
                        $arr_criteriaItems[$key]['text'] = $value['day_time_desc'];
                        $key++;
                    }
                    break;
            }
            //  debug($arr_emp);
            echo json_encode($arr_criteriaItems);
        }
    }
public function downloadHistory($type, $mode) {
        $this->autoRender = false;

        //This is to save download history.
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'Lop':
                $dataForHistory['report_type'] = "Employees Lop Reports";
                break;
           
        }

        $dataForHistory['report_from'] = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
        $dataForHistory['report_to'] = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] : '';
        $dataForHistory['include_resigned'] = isset($arr_form_data['resigned']) ? $arr_form_data['resigned'] : '';
        $dataForHistory['Include_negative_salary'] = isset($arr_form_data['ngtvsal']) ? $arr_form_data['ngtvsal'] : '';
        $criteria_count = isset($arr_form_data['hidden-criterias-count']) ? $arr_form_data['hidden-criterias-count'] : 1;
        $i = 1;
        $criteria_array = array();
        $criteria_name_array = array();
        $items_array = array();
        $items_count_array = array();
        while ($i <= $criteria_count) {
            $criteria = isset($arr_form_data['hidden-criteria' . $i]) ? $arr_form_data['hidden-criteria' . $i] : '';
            $criteria_array[] = $criteria;

            switch ($criteria) {
                case 'Units': $criteria_name_array[] = 'belonging to a Branch';
                    break;
                case 'LeaveType': $criteria_name_array[] = 'belonging to a Leave Type';
                    break;
                case 'Leavestatus': $criteria_name_array[] = 'belonging to a Leave status';
                    break;
                case 'EmployeeDetails': $criteria_name_array[] = 'belonging to an Employee';
                    break;
                default : break;
            }
            $items_array[] = implode(",", $arr_form_data[$criteria]);
            $items_count_array[] = count($arr_form_data[$criteria]);

            $i++;
        }

        $dataForHistory['criteria'] = implode(",", $criteria_array);
        $dataForHistory['criteria_name'] = implode(",", $criteria_name_array);
        $dataForHistory['items'] = implode(",", $items_array);
        $dataForHistory['items_count'] = implode(",", $items_count_array);


        if ($mode == 'pdf') {
            $dataForHistory['mode'] = 'PDF Download';
        } else if ($mode == 'excel') {
            $dataForHistory['mode'] = 'Excel Download';
        } else {
            $dataForHistory['mode'] = 'View Report';
        }

        $user_id = $this->Session->read('login_user_id');
        $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
        $user_name = $this->Session->read('user_name');
        $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';

        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $this->ReportAudit->save($dataForHistory);
    }

    public function generatereport($type = '', $mode = '') {
        $this->autoRender = false;

        switch ($type) {
           
            case 'Lop':
                $this->generatelopreport($mode);
                break;
         
            default:
                return false;
                break;
        }
		  $this->downloadHistory($type,$mode);
    }

    public function listemployeefields() {
        App::import('Vendor', 'EmployeeInformationFields', array('file' => 'ReportFields' . DS . 'EmployeeInformationFields.php'));
        $arr_empinformation_fields = new EmployeeInformationFields();
        $arr_emp_field_headings = array_merge(
                $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'), $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'), $arr_empinformation_fields->getFieldHeadings('Departments'), $arr_empinformation_fields->getFieldHeadings('Grades'), $arr_empinformation_fields->getFieldHeadings('Verticals'), $arr_empinformation_fields->getFieldHeadings('Units')
        );
        $arr_emp_field_names = array(
            'EmployeeDetails' => $arr_empinformation_fields->getFieldNames('EmployeeDetails'),
            'EmployeeProfessionalDetails' => $arr_empinformation_fields->getFieldNames('EmployeeProfessionalDetails'),
            'Departments' => $arr_empinformation_fields->getFieldNames('Departments'),
            'Grades' => $arr_empinformation_fields->getFieldNames('Grades'),
            'Verticals' => $arr_empinformation_fields->getFieldNames('Verticals'),
            'Units' => $arr_empinformation_fields->getFieldNames('Units')
        );

        $resp_emp = array();
        $resp_emp["rows"] = array();
        foreach ($arr_emp_field_names as $key => $value) {
            foreach ($value as $key1 => $value1) {
                $data['id'] = $key . '.' . $key1;
                $data['data'] = array($value1);
                $resp_emp["rows"][] = $data;
            }
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    private function _modelExists($modelName) {
        $models = App::objects('model');
        return in_array($modelName, $models);
    }

    
//lop deatiled report
    private function generatelopreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_month = $arr_form_data['reportfrom'];
        $from = date('d/m/Y', strtotime($arr_form_data['reportfrom']));
        $to = date('d/m/Y', strtotime($arr_form_data['reportto']));
          $login_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');

      
        $needBranchWiseReport = false;
        $conditions = array();
        
          $condition = ' where EmployeeDetails.status = 1 and ';
        if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1')
        {
            $condition =  "where EmployeeDetails.status in('1','2') and ";
        }
        $end_month = isset($arr_form_data['reportto']) ? $arr_form_data['reportto']. " " ."23:59:59" : date('Y-m-t');
        $start_month  = isset($arr_form_data['reportfrom'])? $arr_form_data['reportfrom']. ' ' . '00:00:00':date('Y-m-1');
        
       // $conditions[] = 'FROMDATE >="' . $from . '" and TODATE<="' . $to . '"';
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }
            if($str_criteria_item == ''){
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            if(!isset($arr_form_data[$str_criteria_item])){
            echo "<h1>No Criteria Selected</h1>";
                die();
            }
            try{
                $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array(
                                "fields" => "reportcriteria,reportcriteria_field",
                                "conditions" => array(
                                    "reporttype" => "Lop",
                                    "status" => 1,
                                    'reportcriteria' => $str_criteria_item
                                )
            )));
            } catch (Exception $ex) {

            }
            if (isset($arr_reportcriterias[0]['reportcriteria_field']) && isset($arr_form_data[$str_criteria_item])) {
                $conditions[] = $arr_reportcriterias[0]['reportcriteria'] . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
            }
        }
        $str_conditions = implode(' AND ', $conditions);
       
      $conditionn = "AND (emp.others = 'LOP' OR( emp.leaves = 'LOP/LOP' AND emp.weekoff IS NULL)) AND emp.att_date BETWEEN '$start_month' AND '$end_month'";

        try{
            $arr_empleaverequests = $this->LeaveRequests->query('SELECT Info.branch,EmployeeDetails.branch_code,emp.others,emp.att_date,emp.yearmonth,
                                    emp.emp_pkey,EmployeeDetails.status,EmployeeDetails.mobile_no,CONCAT(first_name, " ", last_name) AS emp_name,Info.*
                                    FROM `emp_details` AS `EmployeeDetails` LEFT JOIN `employee_info` AS `Info` ON (`EmployeeDetails`.`emp_pkey` = `Info`.`emp_pkey`) 
                                    LEFT JOIN `emp_detail_timeattandance` AS `emp` ON (`EmployeeDetails`.`emp_pkey` = `emp`.`emp_pkey`) 
                                    LEFT JOIN `branches` AS `Units` ON (`Info`.`branch_code` = `Units`.`branch_code`) 
                                    LEFT JOIN `termination` AS `termination` ON
                                    (`termination`.`emp_fkey` = `Info`.`emp_pkey` and `termination`.`status` = 1)'
                                    . $condition . $str_conditions . $conditionn.  'ORDER BY Info.EmpName');
        } 
        catch (Exception $ex) {
        }

       // debug($arr_empleaverequests);exit();
       
        $pkey = isset($arr_empleaverequests['0']['Info']['emp_pkey'])?$arr_empleaverequests['0']['Info']['emp_pkey']:0 ;
        
         $arr_userid = $this->LeaveRequests->query("select user_id from user_credentials where user_credentials.emp_fkey = $pkey");
//          debug($arr_userid); 
        $arr_leavesummary_for_template = array();
        if ($needBranchWiseReport) { 
            //Parse array for branchwise report
            foreach ($arr_empleaverequests as $leaverequest) {
                //  debug($leaverequest);
                $branch_code = isset($leaverequest['EmployeeDetails']['branch_code']) ? $leaverequest['EmployeeDetails']['branch_code'] : '';
                
                $branch_name = isset($leaverequest['Units']['branch_name']) ? $leaverequest['Units']['branch_name'] : '';
                if ($branch_code != '') {
                    if (!isset($arr_leavesummary_for_template[$branch_code])) {
                        $arr_leavesummary_for_template[$branch_code] = array(
                            'branch_name' => $branch_name,
                            'leaverequests' => array()
                        );
                    }
                    $request = array();
                    $request['termination'] = isset($leaverequest['termination']['last_approved_working_date']) ? $leaverequest['termination']['last_approved_working_date'] : '';
                    $request['status'] = (isset($leaverequest['EmployeeDetails']['status'])) && $leaverequest['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';
                    $request['emp_name'] = isset($leaverequest['Info']['EmpName']) ? $leaverequest['Info']['EmpName'] : '';
                        $request['att_date'] = isset($leaverequest['emp']['att_date']) ? $leaverequest['emp']['att_date'] : '';


                  
                     $request['mobile_no'] = isset($leaverequest['EmployeeDetails']['mobile_no']) ? $leaverequest['EmployeeDetails']['mobile_no'] : '';
                    $request['employee_id'] = isset($leaverequest['Info']['employee_id']) ? $leaverequest['Info']['employee_id'] : '';
                    $request['branch'] = isset($leaverequest['Info']['branch']) ? $leaverequest['Info']['branch'] : '';
                    $request['designation'] = isset($leaverequest['Info']['designation']) ? $leaverequest['Info']['designation'] : '';
                    $request['department'] = isset($leaverequest['Info']['department']) ? $leaverequest['Info']['department'] : '';
                    $request['joining_date'] = isset($leaverequest['Info']['joining_date']) ? $leaverequest['Info']['joining_date'] : '';
                    //added by megha userid on 27/08/2019
                    $pkey = isset($leaverequest['Info']['emp_pkey'])?$leaverequest['Info']['emp_pkey']:0 ;
                    $userid = $this->LeaveRequests->query("select user_id from user_credentials where user_credentials.emp_fkey = $pkey");
                    $request['userid'] = isset($userid['0']['user_credentials']['user_id'])?$userid['0']['user_credentials']['user_id']:'';
                    $arr_leavesummary_for_template[$branch_code]['leaverequests'][] = $request;
                }
               
            }
        } else {
            //Parse array for simple report
            $arr_leavesummary_for_template['leaverequests'] = array();
            foreach ($arr_empleaverequests as $leaverequest) {
                $request = array(); 
                $request['termination'] = isset($leaverequest['termination']['last_approved_working_date']) ? $leaverequest['termination']['last_approved_working_date'] : '';
                $request['att_date'] = isset($leaverequest['emp']['att_date']) ? $leaverequest['emp']['att_date'] : '';

                     $request['mobile_no'] = isset($leaverequest['EmployeeDetails']['mobile_no']) ? $leaverequest['EmployeeDetails']['mobile_no'] : '';
                $request['status'] = (isset($leaverequest['EmployeeDetails']['status'])) && $leaverequest['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';
                $request['emp_name'] = isset($leaverequest['Info']['EmpName']) ? $leaverequest['Info']['EmpName'] : '';
                $request['employee_id'] = isset($leaverequest['Info']['employee_id']) ? $leaverequest['Info']['employee_id'] : '';
                $request['branch'] = isset($leaverequest['Info']['branch']) ? $leaverequest['Info']['branch'] : '';
                $request['designation'] = isset($leaverequest['Info']['designation']) ? $leaverequest['Info']['designation'] : '';
                $request['department'] = isset($leaverequest['Info']['department']) ? $leaverequest['Info']['department'] : '';
                $request['joining_date'] = isset($leaverequest['Info']['joining_date']) ? $leaverequest['Info']['joining_date'] : ''; 
                //added by megha userid on 27/08/2019
                $pkey = isset($leaverequest['Info']['emp_pkey'])?$leaverequest['Info']['emp_pkey']:0 ;
                $userid = $this->LeaveRequests->query("select user_id from user_credentials where user_credentials.emp_fkey = $pkey");
                $request['userid'] = isset($userid['0']['user_credentials']['user_id'])?$userid['0']['user_credentials']['user_id']:'';
                //end userid
                $arr_leavesummary_for_template['leaverequests'][] = $request;
            }
        }
        $this->set('needBranchWiseReport', $needBranchWiseReport);
        $this->set('arr_leavesummary_for_template', $arr_leavesummary_for_template);

        $dates = $from .' - '.$to;
        
//        debug($arr_leavesummary_for_template);
        //Set informations needed for report 
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('dates', $dates);
        
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
      //  debug($arr_data);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportleavesummary');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('LeaveDetailedReports.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_LOP Detailed.xlsx" : "Lop Detailed" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "                                   LOP Detailed  ".$dates);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:K1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                );
                 
                  $worksheet->setCellValueByColumnAndRow(0, 2, "                    
                    (Report Run by " . $login_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:K2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                 if (empty($arr_leavesummary_for_template))   {
                    $worksheet->mergeCells("A3:K3");
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria.");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);

                } 
                else{
                $columncount = 0;
                $rowcount = 3;
                if (isset($needBranchWiseReport) && $needBranchWiseReport == true) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Sl No');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, 'Employee Name');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee ID');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'User ID');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Mobile Number');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Termination Date');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Loss Of Pay Dates');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
 $rowcount = $rowcount + 1;
$i = 1;
                    foreach ($arr_leavesummary_for_template as $branch_code => $leavesummary) {

  
                       // Function to convert dates to timestamps
if (!function_exists('dateToTimestamp')) {
    // Function to convert dates to timestamps
    function dateToTimestamp($date) {
        return strtotime(trim($date));
    }
}

$arr_data = $leavesummary['leaverequests'];

// Create arrays to store basic details and LOP days
$basicDetails = [];
$lopDaysArray = [];

if (count($arr_data) >= 0) {
    
    foreach ($arr_data as $val) {
        $id = $val['employee_id'];

        // Check if basic details for this employee have not been stored yet
        if (!isset($basicDetails[$id])) {
            $empstatus = $val['status'];
            $name = $val['emp_name'] . $empstatus;
            $clas = $val['designation'];
            $mob = $val['mobile_no'];
            $join = $val['joining_date'];
            $dept = $val['department'];
            $unit = $val['branch'];
            $termination = $val['termination'];
         
            $userid = $val['userid'];

            // Store basic details in the array indexed by $id
            $basicDetails[$id] = [
                'name' => $name,
                'clas' => $clas,
                'mob' => $mob,
                'join' => $join,
                'dept' => $dept,
                'unit' => $unit,
                'termination' => $termination,
             
                'userid' => $userid,
            ];
        }

        // Handle LOP days
        $att = $val['att_date'];

        if (!empty($att) && $att != "0") {
            $attDates = explode(',', $att);

            // Check if LOP days array for this employee exists, if not, create it
            if (!isset($lopDaysArray[$id])) {
                $lopDaysArray[$id] = [];
            }

            // Add LOP days to the LOP days array for this employee
            foreach ($attDates as $attDate) {
                $lopDaysArray[$id][] = trim($attDate);
            }
        }

    }

    foreach ($basicDetails as $id => $basicInfo) {
        
        // Increment row count for each employee

        // Set the basic details in the spreadsheet
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $i++);
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
         $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount+2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
       $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), isset($basicInfo['name']) ? $basicInfo['name'] : '');
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), isset($id) ? $id : '');
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), isset($basicInfo['userid']) ? $basicInfo['userid'] : '');
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), isset($basicInfo['join']) ? $basicInfo['join'] : '');
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), isset($basicInfo['unit']) ? $basicInfo['unit'] : '');
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), isset($basicInfo['dept']) ? $basicInfo['dept'] : '');
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), isset($basicInfo['clas']) ? $basicInfo['clas'] : '');
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), ($rowcount), isset($basicInfo['termination']) ? $basicInfo['termination'] : '');
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, isset($basicInfo['mob']) ? $basicInfo['mob'] : '');



// Set the horizontal alignment for the cell
// if (isset($basicInfo['mob'])) {
    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 8) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
// }


        // Check if LOP days array exists for this employee
        if (isset($lopDaysArray[$id])) {
            sort($lopDaysArray[$id]);

            // Convert dates to timestamps and remove duplicates
            $uniqueLopDays = array_unique(array_map('dateToTimestamp', $lopDaysArray[$id]));

            // Convert timestamps back to date format
            $uniqueLopDays = array_map(function ($timestamp) {
                return date('Y-m-d', $timestamp);
            }, $uniqueLopDays);

            // Join unique LOP days with commas and set in the same row
            $lopDaysString = implode(', ', $uniqueLopDays);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, isset($lopDaysString) ? $lopDaysString : '');

    
            
        }
         $rowcount++;
    }
                // $objPHPExcel->getActiveSheet()->getStyle('K4:K600')->getAlignment()->setWrapText(true);

                    $BStyle = array(

                        'borders' => array(

                            'allborders' => array(

                                'style' => PHPExcel_Style_Border::BORDER_THIN

                            )

                        )

                    );

                    $row = $rowcount - 1;

                    $objPHPExcel->getActiveSheet()->getStyle('A1:K' . $row)->applyFromArray($BStyle);
                  
// $objPHPExcel->getActiveSheet()
// ->getStyle('A4:K800')
// ->getAlignment()
// ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
} 
//  else {
//     $msg = 'No Report found under this';
//     $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $msg);
// }



}}

else {
     
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Sl No ');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, 'Employee Name');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee ID');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'User ID');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Mobile Number');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Termination Date');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Loss Of Pay Dates');
$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);

  
                        $rowcount = $rowcount + 1;

                       // Function to convert dates to timestamps
function dateToTimestamp($date) {
    return strtotime(trim($date));
}

$arr_data = $arr_leavesummary_for_template['leaverequests'];

// Create arrays to store basic details and LOP days
$basicDetails = [];
$lopDaysArray = [];
if (empty($arr_data)) {

                      $worksheet = $objPHPExcel->getActiveSheet();
                     $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria.");
                     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                     $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                     $worksheet->mergeCells("A3:K3");

}


else {
    $i = 1;
    foreach ($arr_data as $val) {
        $id = $val['employee_id'];

        // Check if basic details for this employee have not been stored yet
        if (!isset($basicDetails[$id])) {
            $empstatus = $val['status'];
            $name = $val['emp_name'] . $empstatus;
            $clas = $val['designation'];
            $mob = $val['mobile_no'];
            $join = $val['joining_date'];
            $dept = $val['department'];
            $unit = $val['branch'];
            $termination = $val['termination'];
         
            $userid = $val['userid'];

            // Store basic details in the array indexed by $id
            $basicDetails[$id] = [
                'name' => $name,
                'clas' => $clas,
                'mob' => $mob,
                'join' => $join,
                'dept' => $dept,
                'unit' => $unit,
                'termination' => $termination,
             
                'userid' => $userid,
            ];
        }

        // Handle LOP days
        $att = $val['att_date'];

        if (!empty($att) && $att != "0") {
            $attDates = explode(',', $att);

            // Check if LOP days array for this employee exists, if not, create it
            if (!isset($lopDaysArray[$id])) {
                $lopDaysArray[$id] = [];
            }

            // Add LOP days to the LOP days array for this employee
            foreach ($attDates as $attDate) {
                $lopDaysArray[$id][] = trim($attDate);
            }
        }

    }

    foreach ($basicDetails as $id => $basicInfo) {
        
        // Increment row count for each employee

        // Set the basic details in the spreadsheet
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $i++);
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $basicInfo['name']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $id);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $basicInfo['userid']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $basicInfo['join']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $basicInfo['unit']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $basicInfo['dept']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $basicInfo['clas']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), ($rowcount), $basicInfo['termination']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, $basicInfo['mob']);

        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 1 ), $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 3) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 7) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 8) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 9) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        // Check if LOP days array exists for this employee
        if (isset($lopDaysArray[$id])) {
            sort($lopDaysArray[$id]);

            // Convert dates to timestamps and remove duplicates
            $uniqueLopDays = array_unique(array_map('dateToTimestamp', $lopDaysArray[$id]));

            // Convert timestamps back to date format
            $uniqueLopDays = array_map(function ($timestamp) {
                return date('Y-m-d', $timestamp);
            }, $uniqueLopDays);

            // Join unique LOP days with commas and set in the same row
            $lopDaysString = implode(', ', $uniqueLopDays);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, $lopDaysString);
        }
         $rowcount++;
    }



} 
//  else {
//     $worksheet->setCellValueByColumnAndRow(0, 3, "No Data ");
// //                     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
// }

                    $BStyle = array(

                        'borders' => array(

                            'allborders' => array(

                                'style' => PHPExcel_Style_Border::BORDER_THIN

                            )

                        )

                    );

                    $row = $rowcount - 1;

                    $objPHPExcel->getActiveSheet()->getStyle('A1:K' . $row)->applyFromArray($BStyle);
   


                }
} 
//  $objPHPExcel->getActiveSheet()
//     ->getProtection()->setSheet(true);
// $objPHPExcel->getActiveSheet()->getStyle('B2:K2')
//     ->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
 $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                $objPHPExcel->getActiveSheet()->setTitle('LOP Detailed');
                /* header footer */
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                /* header footer */

                /* print Set up */
                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                /* print Set up */
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $file_name);

                readfile(dirname(__FILE__) . "/" . $file_name);
                unlink(dirname(__FILE__) . "/" . $file_name);
                break;
            default :
                $this->set('mode', '');
                $this->render('reportleavesummary');
                break;
        }
    }
 }