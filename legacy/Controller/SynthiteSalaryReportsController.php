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
ini_set('max_execution_time', 2000);
ini_set('memory_limit', '1024M');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class SynthiteSalaryReportsController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'SynthiteSalaryReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('LeavePolicyGroup', 'CentralControl', 'Banks','UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments', 'EmployeeGrossDetails', 'Verticals', 'Units', 'ReportCriterias', 'DayTimeProcedures', 'EmpCtcTransaction', 'LeaveRequests', 'Designation', 'DbConfig', 'ReportAudit','FinancialYear','EmployeeTaxsalsumNew','EmployeeTaxsalsum','Gender');
    public $components = array('MasterdataManagement');

    /* public $arr_employee_reportcriterias = array(
      'Departments' => 'belonging to a Department',
      'Grades' => 'belonging to a Grade',
      'Verticals' => 'belonging to a Vertical',
      'Units' => 'belonging to a Branch',salary
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
        $company_code = strtoupper($this->Session->read('company_code'));

       // if(($company_code == 'DEMO') || ($company_code == 'GEDE')){
        $arr_reporttypes = array(
            'DepositslipSynthite' => 'Deposit Summary Synthite ',
            // 'SalaryslipVayalatNew' => 'Deposit Summary Synthite_New '
        );
       // }
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */
                
    public function changereporttype($type = '') {
        $this->autoRender = FALSE;
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        if ($type != '') {
            $this->set('type', $type);
            //debug($type);exit();
            switch ($type) {
                case 'DepositslipSynthite':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                 case 'SalaryslipVayalatNew':
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
            $this->set('arr_remainingcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reportcriteria NOT IN(' . $str_currentcriterias . ')', 'reporttype' => $type, 'order' => 'reportcriteria')))));
            $this->set('newindex', $newindex);
            // debug($arr_remainingcriterias);exit;
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
            if ($this->_modelExists($model) || $model == 'Gender') {
        if ($str_criteria == 'LeavePolicyGroup' || $str_criteria == 'BankStatement') {
                    $model = 'Banks';
                }
                $this->set('index', $index);
                $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
                $model = ($model == 'Departments') ? 'Departments' : $model;
                $model = ($model == 'Designation') ? 'Designations' : $model;
                $model = ($model == 'Gender') ? 'Genders' : $model;
                $this->set('criteria', $model);
                $this->render('loadcriteriaitems');
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public function listcriteriaitems($str_criteria = '') {
        $this->autoRender = false;
        $model = $str_criteria;
        $arr_requestdata = $this->request->data;
        // debug( $arr_requestdata);
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');
            if(trim($model) != 'Gender'){
                $this->{$model}->useDbConfig = $this->Session->read('ds');
            }

            $arr_order = array();
            if ($model == 'SalaryHeadItems') {
                $conditions = array("head_fkey" => 6, "value" => 'Y', "status" => 1);
            } elseif ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            } elseif ($model == 'Leavestatus') {
                $conditions = array();
            } elseif ($model == 'EmployeeGrossDetails') {
                $conditions = array();
            } elseif ($model == 'Units') {
                $arr_order = array("Units.branch_name" => "ASC");
                $conditions = array("Units.status" => 1);
            } else {
                $conditions = array("status" => 1);
            }
            if ($model == 'LeavePolicyGroup' || $model == 'Banks') {
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $conditions[] = "status = '1' or status = '2' ";
                $model = 'Banks';
                //$arr_criteriaItemsDB = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("DISTINCT bank_name"), "conditions" => $conditions)));
                //$arr_criteriaItemsDB1 = $this->EmployeeDetails->query("SELECT distinct ifnull(SUBSTRING_INDEX(bank_details,',',1),bank_name) bank_name FROM payroll_master pm ,emp_details where emp_details.emp_pkey =pm.emp_fkey order by bank_name asc");
                $arr_criteriaItemsDB1 = $this->EmployeeDetails->query("SELECT distinct ifnull(SUBSTRING_INDEX(bank_details,',',1),bank_name) bank_name FROM payroll_master pm ,emp_details where emp_details.emp_pkey =pm.emp_fkey 
                                                                        UNION
                                                                        SELECT distinct bank_name FROM emp_details order by bank_name asc
                ");
                // $arr_criteriaItemsDB2 = $this->EmployeeDetails->query("SELECT distinct bank_name FROM emp_details");
                // debug($arr_criteriaItemsDB1);
                $arr_criteriaItemsDB= array();
                foreach($arr_criteriaItemsDB1 as $val){
                    if($val['0']['bank_name'] != ''){
                    $arr_criteriaItemsDB[]= $val['0'];
                    }
                }
            }else if($model == 'Gender'){
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $conditions = array();

                $arr_criteriaItemsDB[0] = 'Female';
                $arr_criteriaItemsDB[1] = 'Male';
                $arr_criteriaItemsDB[2] = 'Transgender';
            }else if($model == 'Departments'){

                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions, "order" => array("Departments.dept_name" => "ASC"))));
            }
            else if($model == 'Designation'){

                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions, "order" => array("Designation.desig_name" => "ASC"))));
            }
             else {

                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions, "order" => $arr_order)));
            }

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
                case 'SalaryHeadItems':
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
                    
                case 'Banks':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = isset($value['bank_name']) ? $value['bank_name'] : null;
                        $arr_criteriaItems[$key]['text'] = ($value['bank_name'] != '') ? $value['bank_name'] : ' N/A  ';
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

                case 'LeavesPolicyGroup':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['LEAVEPOLICY_GROUP_ID'];
                        $arr_criteriaItems[$key]['text'] = $value['LEAVEPOLICY_GROUP_NAME'];
                        $key++;
                    }
                    break;
                //ARUN Gross
                case 'EmployeeGrossDetails':
                    // echo 'ih';
                    $fields = 'id,Designation.desig_name';
//                                    $joins = array(
//                                        array(
//                                        'table' => 'emp_proff',
//                                        'alias' => 'EmployeeProfessionalDetails',
//                                        'type' => 'LEFT',
//                                        'foreignKey' => false,
//                                        'conditions'=> array('EmployeeGrossDetails.emp_fkey = EmployeeProfessionalDetails.emp_fkey')
//                                        ),
//                                        array(
//                                        'table' => 'emp_details',
//                                        'alias' => 'EmployeeDetails',
//                                        'type' => 'LEFT',
//                                        'foreignKey' => false,
//                                        'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeGrossDetails.emp_fkey')
//                                        )
//                                    );
                    $conditions = array('status' => 1);
                    $this->Designation->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->Designation->find("all", array(
                        'fields' => $fields,
                        // 'joins' => $joins,
                        'conditions' => $conditions,
                            // 'group' => 'EmployeeGrossDetails.emp_fkey'
                    ));
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value['Designation']['desig_name'];
                        $arr_criteriaItems[$key]['key'] = $value['Designation']['id'];
                        $key++;
                    }
                    // debug($arr_emp);
                    break;
                case 'EmployeeDetails':
                    $fields = 'emp_pkey,EmployeeDetails.status,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
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
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions,
                        "order" => $arr_order
                    ));
                    foreach ($arr_emp as $key => $value) {
                        //edited by Akshay
                        // if($value[0]['name'] != null){
                            $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                            $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                            $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];
                            $key++;
                        // }
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
                //Edited by Akshay on 15-9-2023
                case 'Gender':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = strtolower($value);
                        $arr_criteriaItems[$key]['text'] = $value;
                        $key++;
                    }
                    break;
                case 'Designation':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['desig_code'];
                        $arr_criteriaItems[$key]['text'] = $value['desig_name'];
                        $key++;
                    }
                        break;
            }
            echo json_encode($arr_criteriaItems);
        }
    }

    public function downloadHistory($type, $mode, $rep_month) {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();

        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');
        if($user_group == 1){
            $arr_form_data = $_REQUEST;
        }else{
            $user_id = $this->Session->read('login_user_id');
            $arr_emp_pkey = $this->EmpCtcTransaction->query("SELECT DISTINCT emp_fkey FROM user_credentials uc WHERE uc.user_id =  '$user_id'");
            $pkey = isset($arr_emp_pkey[0]['uc']['emp_fkey'])? $arr_emp_pkey[0]['uc']['emp_fkey']:0;

            $arr_form_data['hidden-report-type'] =  'SalaryslipVayalatNew';
            $arr_form_data['hidden-criterias-count'] = '';
            $arr_form_data['hidden-reportfields'] = '';
            $arr_form_data['reportfrom'] = $rep_month;
            $arr_form_data['hidden-criteria1'] = 'EmployeeDetails';
            $arr_form_data['select-criteria1'] =  'EmployeeDetails';
            $arr_form_data['EmployeeDetails'][] = $pkey;
            $arr_form_data['resigned'] = '0';
            $arr_form_data['ngtvsal'] = '1';
            $arr_form_data['selectall'] = '0';

        }
        

        switch ($type) {
            case 'salary':
                $dataForHistory['report_type'] = "Cost To Company(CTC) Summary Report";
                break;
            case 'salarystructure':
                $dataForHistory['report_type'] = "Cost To Company Detailed Report";
                break;
            case 'Grosssalary':
                $dataForHistory['report_type'] = "Gross Salary Detailed Report";
                break;
            case 'Analysis':
                $dataForHistory['report_type'] = "Salary Analysis Report";
                break;
            case 'Gross':
                $dataForHistory['report_type'] = "Gross Salary_vayalat";
                break;
            case 'SalaryCombined':
                $dataForHistory['report_type'] = "Salary Combined  Report";
                break;
            case 'GrosssalaryNew':
                $dataForHistory['report_type'] = "Gross Salary Detailed Report_New";
                break;
            case 'GrosssalarySummary':
                $dataForHistory['report_type'] = "Gross Salary Summary Report";
                break;
            case 'SummaryPayroll':
                $dataForHistory['report_type'] = "Payroll Summary Report";
                break;
            case 'DepositslipSynthite':
                $dataForHistory['report_type'] = "Salary Slip";
                break;
            case 'SalaryslipVayalatNew':
                $dataForHistory['report_type'] = "Salary Slip";
                break;
            case 'SlipThirdVersion':
                $dataForHistory['report_type'] = "Salary Slip_Version3";
                break;  
             case 'SlipSecondVersion':
                $dataForHistory['report_type'] = "Salary Slip_Version2";
                break;
            case 'SlipFirstVersion':
                $dataForHistory['report_type'] = "Salary Slip_Version1";
                break;  
            case 'Salaryslipnew':
                $dataForHistory['report_type'] = "Salary Slip New";
                break;
            case 'Payrollslip':
                $dataForHistory['report_type'] = "Payroll Slip";
                break;
            case 'TimeAttendance':
                $dataForHistory['report_type'] = "Payroll Summary Report";
                break;
            case 'GrossPeriod':
                $dataForHistory['report_type'] = "Gross Salary Period Wise Report";
                break;
            case 'BankTranfer':
                $dataForHistory['report_type'] = "Salary Bank Transfer Report";
                break;
            case 'BankTranferNew':
                $dataForHistory['report_type'] = "Salary Bank Transfer_New Report";
                break;
            case 'MonthlyCTCReport':
                $dataForHistory['report_type'] = "Monthly CTC Detailed Report";
                break;
            case 'LOPREPORT':
                $dataForHistory['report_type'] = "Payroll Report with LOP";
                break;
            case 'statutory':
                $dataForHistory['report_type'] = "Statutory Report";
                $component = $arr_form_data['hidden-report_component'];
                switch ($component) {
                    case 'cont' : $dataForHistory['report_component'] = 'Register Of Contractors';
                        break;
                    case 'workmen' : $dataForHistory['report_component'] = 'Register Of Workmen Employed By Contractor';
                        break;
                    case 'wageslip' : $dataForHistory['report_component'] = 'Wage Slip';
                        break;
                    case 'employmentcard' : $dataForHistory['report_component'] = 'Employment Card';
                        break;
                    case 'muster_roll' : $dataForHistory['report_component'] = 'Muster Roll';
                        break;
                    case 'register_fines' : $dataForHistory['report_component'] = 'Register Of Fines';
                        break;
                    case 'register_advances' : $dataForHistory['report_component'] = 'Register Of Advances';
                        break;
                    case 'register_overtime' : $dataForHistory['report_component'] = 'Register Of Overtime';
                        break;
                    case 'register_wages' : $dataForHistory['report_component'] = 'Register Of Wages';
                        break;
                    case 'register_musterroll' : $dataForHistory['report_component'] = 'Form Of Register Of Wages-cum Muster Roll';
                        break;
                    case 'register_deductions' : $dataForHistory['report_component'] = 'Register Of Deductions For Damage Or Loss';
                        break;
                }
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
                case 'EmployeeDetails': $criteria_name_array[] = 'belonging to an Employee';
                    break;
                case 'Units': $criteria_name_array[] = 'belonging to a Branch';
                    break;
                case 'Departments': $criteria_name_array[] = 'belonging to a Department';
                    break;
                case 'Grades': $criteria_name_array[] = 'belonging to a Grade';
                    break;
                case 'Verticals': $criteria_name_array[] = 'belonging to a Vertical';
                    break;
                case 'SalaryHeadItems': $criteria_name_array[] = 'belonging to a Salary Head Item';
                    break;
                case 'LeaveRequests': $criteria_name_array[] = 'belonging to a Leave Request';
                    break;
                case 'LeavePolicyGroup': $criteria_name_array[] = 'belonging to a Bank';
                    break;
                case 'Leavestatus': $criteria_name_array[] = 'belonging to a Leave status';
                    break;
                case 'LeavesPolicyGroup': $criteria_name_array[] = 'belonging to a Leaves Policy Group';
                    break;
                case 'EmployeeGrossDetails': $criteria_name_array[] = 'belonging to a Designation';
                    break;
                case 'DayTimeProcedures': $criteria_name_array[] = 'belonging to a Day Time Procedure';
                    break;
                case 'Designation': $criteria_name_array[] = 'belonging to a Designation';
                    break;
                case 'Gender': $criteria_name_array[] = 'belonging to a Gender';
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

//        debug($dataForHistory);
        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $this->ReportAudit->save($dataForHistory);
    }

    public function generatereport($type = '', $mode = '', $month = '') {

        $this->autoRender = false;

        if($mode == 'view'){
            $mode = '';
        }
        switch ($type) {
            case 'DepositslipSynthite':
                //salary slip
                $this->GenerateDepositSlipreportSynthite($mode, $month);
                break;
            case 'SalaryslipVayalatNew':
                //salary slip
                $this->GenerateSalarySlipreportVayalatnew($mode, $month);
                break;
            default:
                return false;
                break;
        }

        $this->downloadHistory($type, $mode, $month);
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

    private function generateemployeestatutory($mode = '') {
        $report_component = $_REQUEST['hidden-report_component'];
        // debug($report_component); exit;
//        if($report_component == "cont"){
//            $this->GenerateContactorsReport($mode);
//        }
//        if($report_component == "workmen"){
//            $this->GenerateWorkmenReport($mode);
//        }
//        if($report_component == "muster_roll"){
//            $this->GenerateMasterrollReport($mode);
//        }
        switch ($report_component) {
            case "cont" : $this->GenerateContactorsReport($mode);
                break;
            case "workmen" : $this->GenerateWorkmenReport($mode);
                break;
            case "wageslip" : $this->GenerateWageslipReport($mode);
                break;
            case "employmentcard" : $this->GenerateEmploymentCardReport($mode);
                break;
            case "muster_roll" : $this->GenerateMusterRollReport($mode);
                break;
            case "register_fines" : $this->GenerateFinesReport($mode);
                break;
            case "register_advances" : $this->GenerateAdvancesReport($mode);
                break;
            case "register_overtime" : $this->GenerateOvertimeReport($mode);
                break;
            case "register_wages" : $this->GenerateWagesReport($mode);
                break;
            case "register_musterroll" : $this->GenerateWagesCumMasterRollReport($mode);
                break;
            case "register_deductions" : $this->GenerateDeductionReport($mode);
                break;
            default : return false;
                break;
        }
    }

    public function getprodataDesc($id, $date) {
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $arr_pro_data = $this->EmpCtcTransaction->query("select distinct(prorate_code)  "
                . "from emp_salary_structure as ectc "
                . " where ectc.emp_fkey = '$id' "
                . "and end_date_effective is null ");
        $prorate_code = isset($arr_pro_data[0]['ectc']['prorate_code']) ? $arr_pro_data[0]['ectc']['prorate_code'] : '1'; //set default as calender days
        $year = date('Y', strtotime($date));
        $month = $from = date('m', strtotime($date));
        $day = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $loparray = $this->EmployeeDetails->query("SELECT loss_of_pay FROM payroll_master WHERE emp_fkey = '$id' AND month_year = '$date'");
        $lop = isset($loparray[0]['payroll_master']['loss_of_pay']) ? $loparray[0]['payroll_master']['loss_of_pay'] : 0;
        if ($prorate_code == '1') {
            $day['type'] = "Calender Days";
            $day['days'] = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            //added by megha on 7_5_19
            $weekoff = $this->EmployeeDetails->query("SELECT week_off_days,working_days,days_presant,days_leave FROM payroll_master WHERE emp_fkey = '$id' AND month_year = '$date'");
            $leave = isset($weekoff[0]['payroll_master']['days_leave']) ? $weekoff[0]['payroll_master']['days_leave'] : 0;
            //added by megha on 7_5_19
        } else if ($prorate_code == '2') {
            $day['type'] = "Working Days";


            $holiday = $this->EmployeeDetails->query("SELECT count(ho.HOLIDAYID) as count FROM emp_proff as ep left join  holidays as ho on(ho.HOLIDAY_GROUP_ID=ep.HOLIDAY_GROUP_ID) where month(ho.HOLIDAYDATE)='$month' and year(ho.HOLIDAYDATE)='$year' and ep.emp_fkey='$id'");
            $weekoff = $this->EmployeeDetails->query("SELECT week_off_days,working_days,days_presant,days_leave FROM payroll_master WHERE emp_fkey = '$id' AND month_year = '$date'");
            // debug($holiday);die();
            $holiday_count = isset($holiday[0][0]['count']) ? $holiday[0][0]['count'] : 0;
            $weekoffdays = isset($weekoff[0]['payroll_master']['week_off_days']) ? $weekoff[0]['payroll_master']['week_off_days'] : 0;
            $day_count = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $day['days'] = isset($weekoff[0]['payroll_master']['working_days']) ? $weekoff[0]['payroll_master']['working_days'] : 0; //$day_count - $weekoffdays - $holiday_count;
            $leave = isset($weekoff[0]['payroll_master']['days_leave']) ? $weekoff[0]['payroll_master']['days_leave'] : 0;
        } else {
            $day['type'] = "Fixed Days";
            $day['days'] = 30;
            //added by megha on 7_5_19
            $weekoff = $this->EmployeeDetails->query("SELECT week_off_days,working_days,days_presant,days_leave FROM payroll_master WHERE emp_fkey = '$id' AND month_year = '$date'");
            $leave = isset($weekoff[0]['payroll_master']['days_leave']) ? $weekoff[0]['payroll_master']['days_leave'] : 0;
            //added by megha on 7_5_19
        }
        $day['present'] = isset($weekoff[0]['payroll_master']['days_presant']) ? $weekoff[0]['payroll_master']['days_presant'] + $leave : 0; //$day['days'] - $lop;
        return $day;
    }


    //salary slip
    private function GenerateDepositSlipreportSynthite($mode, $rep_month) {
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');
        if($user_group == 1){
            $arr_form_data = $_REQUEST;
            $condition_approve = " and payroll_master.action in ('Approved','Processed') ";
        }else{
            $user_id = $this->Session->read('login_user_id');
            $arr_emp_pkey = $this->EmpCtcTransaction->query("SELECT DISTINCT emp_fkey FROM user_credentials uc WHERE uc.user_id =  '$user_id'");
            $pkey = isset($arr_emp_pkey[0]['uc']['emp_fkey'])? $arr_emp_pkey[0]['uc']['emp_fkey']:0;

            $arr_form_data['hidden-report-type'] =  'DepositslipSynthite';
            $arr_form_data['hidden-criterias-count'] = '1';
            $arr_form_data['hidden-reportfields'] = '';
            $arr_form_data['reportfrom'] = $rep_month;
            $arr_form_data['hidden-criteria1'] = 'EmployeeDetails';
            $arr_form_data['select-criteria1'] =  'EmployeeDetails';
            $arr_form_data['EmployeeDetails'][] = $pkey;
            $arr_form_data['resigned'] = '0';
            $arr_form_data['ngtvsal'] = '1';
            $arr_form_data['selectall'] = '0';
            // debug($arr_form_data);
            $condition_approve = " and payroll_master.action in ('Approved') ";
        }
        
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        //Functions for view and pdf
        function convertToWords($number)
        {
            $number = abs($number);
            $words = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine');
            $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');
            $teens = array('Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');

            $amount_in_words = '';

            if ($number < 10) {
                $amount_in_words .= $words[$number];
            } elseif ($number < 20) {
                $amount_in_words .= $teens[$number - 10];
            } elseif ($number < 100) {
                $amount_in_words .= $tens[floor($number / 10)];
                if ($number % 10 > 0) {
                    $amount_in_words .= ' ' . $words[$number % 10];
                }
            } elseif ($number < 1000) {
                $amount_in_words .= $words[floor($number / 100)] . ' Hundred';
                if ($number % 100 > 0) {
                    $amount_in_words .= ' and ' . convertToWords($number % 100);
                }
            } elseif ($number < 100000) {
                $amount_in_words .= convertToWords(floor($number / 1000)) . ' Thousand';
                if ($number % 1000 > 0) {
                    $amount_in_words .= ' ' . convertToWords($number % 1000);
                }
            } elseif ($number < 10000000) {
                $amount_in_words .= convertToWords(floor($number / 100000)) . ' Lakh';
                if ($number % 100000 > 0) {
                    $amount_in_words .= ' ' . convertToWords($number % 100000);
                }
            } else {
                $amount_in_words .= convertToWords(floor($number / 10000000)) . ' Crore';
                if ($number % 10000000 > 0) {
                    $amount_in_words .= ' ' . convertToWords($number % 10000000);
                }
            }

            return $amount_in_words;
        }
    function formatIndianNumber($amount)
        {
            setlocale(LC_MONETARY, 'en_IN');
            $amount = money_format('%!i', $amount);
            return rtrim(($amount), '.');
          // return rtrim(rtrim($amount, '0'), '.') . '.00';
        }
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';

            if ($str_criteria_item == '') {

                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        $variable_key = array();
        //Variable
        $variable_keys = $this->EmpCtcTransaction->query("select trim(salary_head_item_desc) as sal_head,ectc.head_operator FROM emp_salary_slip as ectc
        left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
        left join salary_heads on (salary_heads.head_pkey = salhead.head_fkey) where ectc.item_part='Direct' and salary_heads.head_pkey in (2,7,9)
        and end_date_effective is null  and month_year = '$from' Group by salary_head_item_desc ORDER BY salhead.salary_head_item_order1 asc");

        foreach ($variable_keys as $val) {
            if ($val['ectc']['head_operator'] == 'Addition') {
                $variable_key['VAddition'][] = $val[0]['sal_head'];
            } 
        }
        // debug( $variable_key);exit;
        $this->set($variable_key,'variable_key');
        $condition = "and ed.status = '1'  ";

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and ed.status in ('1','2') ";
        }
        if(isset($arr_form_data['ngtvsal']) && $arr_form_data['ngtvsal'] == '0') {
            $condition .= " and payroll_master.net_salary >= 0 ";
        }
        $arr_salary_for_template = array();
        $arr_empleaverequests = array();

        if ($arr_leavepolicygroupids != '') {
            // Convert the array of pkey to a comma-separated string
            // $leavepolicygroupid = implode(',', $arr_leavepolicygroupids);
            $leavepolicygroupid = "'" . implode("','", $arr_leavepolicygroupids) . "'";
            // debug($leavepolicygroupid); exit;
            if($str_criteria_item == 'EmployeeDetails'){
               // $pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
               try{

                
               $arr_empleaverequests = $this->EmpCtcTransaction->query("select distinct ectc.month_year, ectc.salary_head_item_fkey,ectc.salary_head_item_desc, ectc.head_type, COALESCE(SUM(ectc.structure_det_value), 0) AS total_actual_salary, SUM(ROUND(ectc.salary_amount,0)) AS total_earned_salary,SUM(ROUND(ectc.salary_amount,2)) AS total_not_round_earned_salary, ed.status "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and round(ectc.salary_amount) != '0' 
                           and ectc.emp_fkey in ($leavepolicygroupid) and desg.status = 1 "
                        . "and ectc.month_year ='$from' "
                        . $condition
                        . "and end_date_effective is null $condition_approve and ar.isdelete= 'N' and ar.month_year= '$from' " 
                        . "group by ectc.salary_head_item_fkey "
                        . "order by salhead.salary_head_item_order1");
                        $arr_empleaverequests1 = $this->EmpCtcTransaction->query("select distinct ectc.month_year, ectc.salary_head_item_fkey,ectc.salary_head_item_desc, ectc.head_type, COALESCE(SUM(ectc.structure_det_value), 0) AS total_actual_salary, SUM(ROUND(ectc.salary_amount,0)) AS total_earned_salary,SUM(ROUND(ectc.salary_amount,2)) AS total_not_round_earned_salary, ed.status "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join site_attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and round(ectc.salary_amount) != '0' 
                           and ectc.emp_fkey in ($leavepolicygroupid) and desg.status = 1 "
                        . "and ectc.month_year ='$from' "
                        . $condition
                        . "and end_date_effective is null $condition_approve and ar.month_year= '$from' "
                        . "group by ectc.salary_head_item_fkey "
                        . "order by salhead.salary_head_item_order1");
                        $arr_empleaverequests = array_merge($arr_empleaverequests, $arr_empleaverequests1);

                        //debug($arr_empleaverequests);exit;
                //edited by megha on 9_7_19 salary head component ordering
                $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select distinct ectc.month_year, ectc.salary_head_item_fkey,ectc.salary_head_item_desc, ectc.head_type, COALESCE(SUM(ectc.structure_det_value), 0) AS total_actual_salary, SUM(ROUND(ectc.salary_amount,0)) AS total_earned_salary,SUM(ROUND(ectc.salary_amount,2)) AS total_not_round_earned_salary, ed.status "
                . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                            left join branches as br on (br.branch_code = ep.emp_branch)
                             left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                                          where ectc.head_operator = 'Deduction'  
                                          and ectc.emp_fkey in ($leavepolicygroupid) "
                        . " and ectc.month_year ='$from' "
                        . "$condition and "
                        . "end_date_effective is null $condition_approve group by ectc.salary_head_item_fkey "
                        . "order by salhead.salary_head_item_order1  "
                        );
                //  debug($arr_leavepolicygroupids); exit;
            }catch(Exception $e){
                debug($e);exit;
            } 

//    debug($lop); exit;
                $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
                $empdetails = $this->EmpCtcTransaction->query("select ep.emp_fkey ,ep.designation,ed.payment_type,tr.last_approved_working_date,user_credentials.user_id,ep.emp_dept,ep.emp_company_id,ep.joining_date,d.dept_name,dd.desig_name,payroll_master.calander_days,payroll_master.days_leave,payroll_master.working_days,payroll_master.loss_of_pay,payroll_master.days_presant "
                        . "from payroll_master "
                        . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                        . "left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey) "
                        . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey) "
                        . "left join department as d on(d.dept_code = ep.emp_dept) "
                        . "left join designation as dd on (dd.desig_code = ep.designation)"
                        . "left join emp_details as ed on (ed.emp_pkey = payroll_master.emp_fkey)"
                        . " where payroll_master.month_year ='$from' and dd.status = 1 "
                        . " and payroll_master.emp_fkey in ($leavepolicygroupid) $condition $condition_approve "
                        . "group by ed.emp_pkey"    
                    );

           foreach($empdetails as $employee){
            
            $emp_fkey = isset($employee['ep']['emp_fkey'])? $employee['ep']['emp_fkey']:0;
            // debug($emp_fkey);
            $lop[] = $this->EmpCtcTransaction->query("SELECT ectc.month_year,ectc.emp_fkey, SUM(ROUND(ectc.salary_rate, 0)) AS total_salary_amount,ectc.presant_total,ectc.leave_total,
            ectc.lop_total, pm.calander_days, pm.working_days,pm.week_off_days,pm.days_presant, pm.days_leave, pm.loss_of_pay FROM emp_salary_slip AS ectc
            LEFT JOIN emp_details AS ed ON ed.emp_pkey = ectc.emp_fkey 
            LEFT JOIN payroll_master AS pm ON pm.emp_fkey = ed.emp_pkey 
            WHERE head_operator = 'Addition' AND head_type != 'Manually'
            AND item_part = 'Direct' AND ectc.emp_fkey = '$emp_fkey' AND ectc.month_year = '$from' AND pm.month_year = '$from' AND pm.action IN ('Approved', 'Processed') AND ectc.end_date_effective IS NULL");
         
            $pro_date_desc = $this->getprodataDesc($emp_fkey, $from);
            $count = count($lop);
            if($count >0){
                $lop[($count-1)][0]['calander_days'] = isset($pro_date_desc['type'])? $pro_date_desc['type']:'';
            }
        } 
        // exit;
            $emp_count = count($empdetails);
            $this->set('emp_count', $emp_count);
            //$pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
            $empdetails['prodate_type'] = isset($pro_date_desc['type'])? $pro_date_desc['type']:'';

            $arr_settle = $this->EmpCtcTransaction->query(" select sum(salary_amount) from emp_settle_slip as ectc  
            left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
            left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
            left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
            where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.emp_pkey in ($leavepolicygroupid) and date_format(termination.last_approved_working_date,'%Y-%m') = '$from'  group by emp_details.emp_pkey");
                $settle = isset($arr_settle['0']['0']['sum(salary_amount)']) ? $arr_settle['0']['0']['sum(salary_amount)'] : 0;
           if(!empty($arr_empleaverequests)){
                $arr_empleaverequests_refined = array();
                $incentives = 0;
                
                foreach($arr_empleaverequests as $value){
                    if(in_array($value['ectc']['salary_head_item_desc'], $variable_key['VAddition'])){
                        $incentives += isset($value['ectc']['salary_amount']) ? abs(round($value['ectc']['salary_amount'])) : 0;
                    }else{
                        $arr_empleaverequests_refined[] = $value;
                    }
                }
                $arr_empleaverequests = $arr_empleaverequests_refined;

                    $arr_salary_for_template[] = array(
                        'summary' => $arr_empleaverequests,
                        'withoutcomponent' => $salaryslipwithoutcomponents,
                        'empdet' => $empdetails,
                        'settle' => $settle,
                        'variables'=> $incentives,
                        'lopdeduction'=> $lop
                    );


           }
            } else{
              //debug($leavepolicygroupid);exit;
               $arr_list = $this->EmpCtcTransaction->query("select distinct(ed.emp_pkey) 
                           from emp_salary_slip as ectc left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           left join employee_info as ei on (ei.emp_pkey = ed.emp_pkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' 
                           and ep.emp_branch in ($leavepolicygroupid) and ectc.month_year ='$from'  $condition and desg.status = 1 
                           and end_date_effective is null $condition_approve order by ei.EmpName");
               // debug($arr_list); exit;
                //$emp_count = count($arr_list);
                // debug($emp_count);exit;
               // $this->set('emp_count', $emp_count);
                $emp = "'" . implode("','", array_map(function($item) {
                    return $item['ed']['emp_pkey'];
                }, $arr_list)) . "'";
                // debug($emp); exit;

               if (isset($arr_list) && !empty($arr_list)) {
                    //foreach ($arr_list as $emps) {
                     //$emp = $emps['ed']['emp_pkey'];
                     $arr_empleaverequests = $this->EmpCtcTransaction->query("select distinct ectc.month_year, ectc.salary_head_item_fkey,ectc.salary_head_item_desc, ectc.head_type, COALESCE(SUM(ectc.structure_det_value), 0) AS total_actual_salary, SUM(ROUND(ectc.salary_amount,0)) AS total_earned_salary,SUM(ROUND(ectc.salary_amount,2)) AS total_not_round_earned_salary, ed.status "
                     . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and round(ectc.salary_amount) != '0' 
                           and ectc.emp_fkey in ($emp) and desg.status = 1 "
                        . "and ectc.month_year ='$from' $condition and end_date_effective is null $condition_approve and ar.isdelete= 'N' and ar.month_year= '$from' group by ectc.salary_head_item_fkey order by salhead.salary_head_item_order1");
                        $arr_empleaverequests1 = $this->EmpCtcTransaction->query("select distinct ectc.month_year, ectc.salary_head_item_fkey,ectc.salary_head_item_desc, ectc.head_type, COALESCE(SUM(ectc.structure_det_value), 0) AS total_actual_salary, SUM(ROUND(ectc.salary_amount,0)) AS total_earned_salary,SUM(ROUND(ectc.salary_amount,2)) AS total_not_round_earned_salary, ed.status "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join site_attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and round(ectc.salary_amount) != '0' and desg.status = 1 
                           and ectc.emp_fkey in ($emp) and ectc.month_year ='$from' $condition and end_date_effective is null $condition_approve and ar.month_year= '$from' group by ectc.salary_head_item_fkey order by salhead.salary_head_item_order1");
                        $arr_empleaverequests = array_merge($arr_empleaverequests, $arr_empleaverequests1);

                        //debug($arr_empleaverequests); exit;
                //edited by megha on 9_7_19 salary head component ordering
                        $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select distinct ectc.month_year, ectc.salary_head_item_fkey,ectc.salary_head_item_desc, ectc.head_type, ectc.head_type, COALESCE(SUM(ectc.structure_det_value), 0) AS total_actual_salary, SUM(ROUND(ectc.salary_amount,0)) AS total_earned_salary,SUM(ROUND(ectc.salary_amount,2)) AS total_not_round_earned_salary, ed.status "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                            where ectc.head_operator = 'Deduction'  and round(ectc.salary_amount) != '0' 
                            and ep.emp_branch in ($leavepolicygroupid) and ectc.month_year ='$from' $condition and "
                        . "end_date_effective is null $condition_approve group by ectc.salary_head_item_fkey  order by salhead.salary_head_item_order1");
                       
                       //debug($salaryslipwithoutcomponents);exit;
    //                    $lop = $this->EmpCtcTransaction->query("SELECT month_year,emp_fkey,SUM(salary_rate) AS total_salary_amount,presant_total,leave_total,
    // lop_total FROM emp_salary_slip AS ectc LEFT JOIN emp_details AS ed ON ed.emp_pkey = ectc.emp_fkey WHERE head_operator = 'Addition' AND head_type != 'Manually'
    // AND item_part = 'Direct' AND ectc.emp_fkey in ($emp) AND ectc.month_year = '$from' AND end_date_effective IS NULL");
                // $arr_emp = $this->EmpCtcTransaction->query("SELECT ei.emp_pkey FROM employee_info ei WHERE ")
                
                foreach($arr_list as $emps){
                    //debug($emps); exit;
                    $employee= isset($emps['ed']['emp_pkey'])? $emps['ed']['emp_pkey']: 0;
                    $lop[] = $this->EmpCtcTransaction->query("SELECT ectc.month_year,ectc.emp_fkey, SUM(ROUND(ectc.salary_rate, 0)) AS total_salary_amount,ectc.presant_total,ectc.leave_total,
                    ectc.lop_total, pm.calander_days, pm.working_days,pm.week_off_days,pm.days_presant, pm.days_leave, pm.loss_of_pay, pm.payroll_master_pkey FROM emp_salary_slip AS ectc
                    LEFT JOIN emp_details AS ed ON ed.emp_pkey = ectc.emp_fkey 
                    LEFT JOIN payroll_master AS pm ON pm.emp_fkey = ed.emp_pkey 
                    WHERE head_operator = 'Addition' AND head_type != 'Manually'
                    AND item_part = 'Direct' AND ectc.emp_fkey = '$employee' AND ectc.month_year = '$from' AND pm.month_year = '$from' AND pm.action IN ('Approved', 'Processed') AND ectc.end_date_effective IS NULL");
                 
                    $pro_date_desc = $this->getprodataDesc($employee, $from);
                    $count = count($lop);
                    if($count >0){
                        $lop[($count-1)][0]['calander_days'] = isset($pro_date_desc['type'])? $pro_date_desc['type']:'';
                    }
                } 
                //debug($lop); exit;

                $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
                $empdetails = $this->EmpCtcTransaction->query("select ep.designation,ed.payment_type,tr.last_approved_working_date,user_credentials.user_id,ep.emp_dept,ep.emp_company_id,ep.joining_date,d.dept_name,dd.desig_name,payroll_master.calander_days,payroll_master.days_leave,payroll_master.working_days,payroll_master.loss_of_pay,payroll_master.days_presant "
                        . "from payroll_master "
                        . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                        . "left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey) "
                        . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey) "
                        . "left join department as d on(d.dept_code = ep.emp_dept) "
                        . "left join designation as dd on (dd.desig_code = ep.designation) "
                        . "left join emp_details as ed on (ed.emp_pkey = payroll_master.emp_fkey)"
                        . "where payroll_master.month_year ='$from' and dd.status = 1 "
                        . "and ep.emp_branch in ($leavepolicygroupid) $condition $condition_approve"
                        . "group by ed.emp_pkey"
                    );

                $emp_count = count($empdetails);
                $this->set('emp_count', $emp_count);
                //$pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
                $empdetails['prodate_type'] = isset($pro_date_desc['type'])? $pro_date_desc['type']:'';
            $arr_settle = $this->EmpCtcTransaction->query(" select sum(salary_amount) from emp_settle_slip as ectc  
            left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
            left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
            left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
            where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.emp_pkey in ($emp) and 
                date_format(termination.last_approved_working_date,'%Y-%m') = '$from'  group by emp_details.emp_pkey");
                $settle = isset($arr_settle['0']['0']['sum(salary_amount)']) ? $arr_settle['0']['0']['sum(salary_amount)'] : 0;
                if(!empty($arr_empleaverequests)){
                $arr_empleaverequests_refined = array();
                $incentives = 0;
                foreach($arr_empleaverequests as $value){
                    //if(isset($variable_key['VAddition']))
                    if(isset($variable_key['VAddition']) && in_array($value['ectc']['salary_head_item_desc'], $variable_key['VAddition'])){
                        $incentives += isset($value['ectc']['salary_amount']) ? abs(round($value['ectc']['salary_amount'])) : 0;
                    }else{
                        $arr_empleaverequests_refined[] = $value;
                    }
                }
                $arr_empleaverequests = $arr_empleaverequests_refined;

                    $arr_salary_for_template[][] = array( 
                        'summary' => $arr_empleaverequests,
                        'withoutcomponent' => $salaryslipwithoutcomponents,
                        'empdet' => $empdetails,
                        'settle' => $settle,
                        'variables'=> $incentives,
                        'lopdeduction' =>$lop
                    );

                }
                //}
               }
            //}
            }

//debug($arr_salary_for_template);
            $this->set('arr_salary_for_template', $arr_salary_for_template);

            //Function to convert amount to words


            $employee_attendance = array();
            if(!empty($arr_empleaverequests)){
            foreach ($arr_empleaverequests as $val) {
                $emp = isset($val['ectc']['head_operator'])? $val['ectc']['head_operator']:'';
                $emppk = isset($val['ectc']['head_type'])? $val['ectc']['head_type']:'';
                $itempart = isset($val['ectc']['item_part'])? $val['ectc']['item_part']:'';
                $employee_attendance[$emppk][$emp][$itempart][] = $val;
            }}
            $this->set('employee_attendance', $employee_attendance);
            $cr = $arr_form_data['select-criteria1'];
            $this->set('cr', $cr);

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
             $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $time=strtotime($f);
            $month=date("m",$time);

            $mname = date('F', mktime(0, 0, 0, $month, 10));
            $month1 =  $month.'-01'; 
            $year=date("Y",$time);
           
            $this->set('mname1', $mname);
            $this->set('y1', $year);
            $user_id = $this->Session->read('login_user_id');
            $date_time = date('d-m-Y H:i');

            $this->set('user_id',$user_id);
            $this->set('date_time', $date_time); 
            $this->set('month', $month);
            

            switch ($mode) {
                case 'pdf' :

                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('deposit_sensite');
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    // debug($view_output);exit;
                    $html2pdf->writeHTML($view_output);
                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_Employee Deposit Summary" . $from .'.pdf':"Employee Deposit Summary" . strtotime() .".pdf";
                    $html2pdf->Output($file_name, 'D');
                    
                    // $this->render('salaryslip_vayalat');

                    break;
                case 'excel' :
                    
                

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_Salaryslip" . $from . ".xlsx" : "SalarySlip" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();
                    $worksheet = $objPHPExcel->getActiveSheet();
                    $style = $worksheet->getStyleByColumnAndRow(1, 4);

                         // Set the font to bold
                    $style->getFont()->setBold(true);

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                    $worksheet->getColumnDimension('A')->setWidth(5);
                    $worksheet->getColumnDimension('B')->setWidth(30);
                    $worksheet->getColumnDimension('C')->setWidth(20);
                    $worksheet->getColumnDimension('D')->setWidth(10);
                    $worksheet->getColumnDimension('E')->setWidth(20);
                    $worksheet->getColumnDimension('F')->setWidth(30);
                    $worksheet->getColumnDimension('G')->setWidth(50);

                    // $worksheet->setCellValueByColumnAndRow(0, 1, " ");
                    $worksheet->setCellValueByColumnAndRow(1, 1, "Salary Slip - ".$mname." ".$year  );
                    
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 2)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 2)->getFont()->setSize(11);
                    for ($col = 'A'; $col !== 'BZ'; $col++) {
                        $objPHPExcel->getActiveSheet()
                                ->getColumnDimension($col)
                                ->setAutoSize(false);
                    }
                    // $worksheet->mergeCells('B1:G1');
                    // $worksheet->getStyle('B1')->getAlignment()->applyFromArray(
                    //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    // );
    
                    $worksheet->mergeCells('B1:G1');
                    $worksheet->getStyle('B1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
    
                        $worksheet->setCellValueByColumnAndRow(1, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()
                    ->getStyle('B2')
                    ->getFont()
                    ->getColor()
                    ->setRGB ('FF0000'); 
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(14);
                    $worksheet->mergeCells('B2:G2');
                    $worksheet->mergeCells('B3:G3');
                    $worksheet->getStyle('B2')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    //Border style
                    $objPHPExcel->setActiveSheetIndex(0);
                    $BStyle = array(
                        'borders' => array(
                            'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );

                    $THStyle = array(
                        'borders' => array(
                            'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            ),
                            'left' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ),
                            'right' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ),
                        )
                    );

                    //Left align
                    $leftstyle = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        )
                    );
$centerstyle = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        )
                    );

                    //Right align
                    $righttstyle = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        )
                    );
                    
                    //Grid lines
                                    //Border style
                $styleArray = array(
                    'borders' => array(
                      'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                      )
                    )
                  );
                    


                    $worksheet->getStyle('B1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    $rowcount = 2;
                    $col = 0;

                    $worksheet->getStyle('B2')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );


                    $worksheet->getStyle('B3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $company_name = isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : '';

                    $columncount = 1;
                    $rowcount = 4;
                    if($cr == 'EmployeeDetails'){ 
                       
                        if(count($arr_salary_for_template)>0){
                        foreach ($arr_salary_for_template as $value) {
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount , $rowcount,  $company_name);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , ($rowcount))->getFont()->setSize(10);
                            $worksheet->mergeCells('B' . $rowcount . ':G' . $rowcount);
                            //Form XII
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount , $rowcount,  '[FORM XIII See rule 29(2)]');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , ($rowcount))->getFont()->setSize(10);

                            $worksheet->mergeCells('B' . $rowcount . ':G' . $rowcount);
                            $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );

                            //Pay slip
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Pay Slip -' . $mname .' '.$year);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount))->getFont()->setSize(10);
                            $worksheet->mergeCells('B' . $rowcount . ':G' . $rowcount);
                            $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );

                      if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                            $i += 1;
                            
                            $start = $rowcount - 2;
                            // $worksheet->mergeCells('A' . $rowcount . ':D' . $rowcount);
//                        $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
//                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                    );
                            $empstatus = isset($value['summary']['0']['ed']['status']) && $value['summary']['0']['ed']['status'] == "2" ? ' (Resigned)' : '';
                            $emp_name = isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] . " " . $value['summary']['0']['ed']['last_name'] : '';
                          
                            $payment_type =isset($value['summary']['0']['ed']['payment_type']) ? $value['summary']['0']['ed']['payment_type'] : '';
                            $rowcount++;
                            $branch = isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : '';
                            $calender_days = isset($value['empdet']['0']['payroll_master']['calander_days']) ? $value['empdet']['0']['payroll_master']['calander_days'] : '';

                            $daysleave = isset($value['empdet']['0']['payroll_master']['days_leave']) ? $value['empdet']['0']['payroll_master']['days_leave'] : '';
                            $designat = isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : '';
                            $wrktime = isset($value['empdet']['0']['payroll_master']['working_days']) ? $value['empdet']['0']['payroll_master']['working_days'] : '';
                            $loss_offp = isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : '';
                            $department = isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '';
                            $days_present = isset($value['summary']['0']['ar']['presant_total']) ? $value['summary']['0']['ar']['presant_total'] : '';
                            $weekoff = isset($value['summary']['0']['ar']['weekoff_total']) ? $value['summary']['0']['ar']['weekoff_total'] : '';
                            $holiday = isset($value['summary']['0']['ar']['holiday_total']) ? $value['summary']['0']['ar']['holiday_total'] : '';
                            $employee_id = isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : '';
                            $gender = isset($value['summary']['0']['ed']['classification'])?strtoupper($value['summary']['0']['ed']['classification']):'';
                            //edited by megha on 9_7_19 date format changed
                            $join = isset($value['empdet']['0']['ep']['joining_date']) ? date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';
                            //$join = isset($value['empdet']['0']['ep']['joining_date']) ? $value['empdet']['0']['ep']['joining_date'] : '';
                            $termin = isset($value['empdet']['0']['tr']['last_approved_working_date']) ? $value['empdet']['0']['tr']['last_approved_working_date'] : '';
                            $userid = isset($value['empdet']['0']['user_credentials']['user_id']) ? $value['empdet']['0']['user_credentials']['user_id'] : '';
                            
                            $pf = isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : '';
                            $esi = isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : '';
                            $uan =  isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : '';
                            $bank_name = '';
					            $branch_name = '';
					            $ifsc_code = '';
					            $acc_number = '';
								$bank = isset($value['empdet']['0']['payroll_master']['bank_details'])?$value['empdet']['0']['payroll_master']['bank_details']:'';
								if($bank !=''){
					            list($bank_name, $branch_name, $ifsc_code,$acc_number) = explode(',', $bank);
					            }
								if($bank_name == ''){
                                $bank_name = isset($value['summary']['0']['ed']['bank_name'])?$value['summary']['0']['ed']['bank_name']:'';
								}
								if($branch_name == ''){
                                $branch_name = isset($value['summary']['0']['ed']['branch_name'])?$value['summary']['0']['ed']['branch_name']:'';
								}
								if($ifsc_code == ''){
                                $ifsc_code = isset($value['summary']['0']['ed']['ifsc_code'])?$value['summary']['0']['ed']['ifsc_code']:'';
								}
								if($acc_number == ''){
                                $acc_number = isset($value['summary']['0']['ed']['account_no'])?$value['summary']['0']['ed']['account_no']:'';
								} 
                            //1st row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Emp Code : ");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(false);
                            
                            $worksheet->mergeCells('C'.$rowcount.':E'.$rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, $employee_id);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->applyFromArray($leftstyle);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount, "Bank : ");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount, $bank_name);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->applyFromArray($leftstyle);
                            //2nd row 
                            // $worksheet->mergeCells('C'.$rowcount.':E'.$rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 1, 'Name :');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 1)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 1, $emp_name.$empstatus);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 1)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 1)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+1).':E'.($rowcount+1));
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount + 1, "A/C NO:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount + 1)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 1, ($acc_number));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 1)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 1)->applyFromArray($leftstyle);

                            //3rd row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 2, 'Department:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 2)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 2, $department);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 2)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 2)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+2).':E'.($rowcount+2));

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount + 2, 'UAN NO:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount + 2)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 2, $uan);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 2)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 2)->applyFromArray($leftstyle);
                            $objPHPExcel->getActiveSheet()->getStyle('G'.($rowcount+2))->getNumberFormat()->setFormatCode('0');
                            //4th row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 3, 'Designation:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 3)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 3, $designat);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 3)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 3)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+3).':E'.($rowcount+3));

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount + 3, 'ESI NO:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount + 3)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 3, ($esi));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 3)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 3)->applyFromArray($leftstyle);
                            $objPHPExcel->getActiveSheet()->getStyle('G'.($rowcount+3))->getNumberFormat()->setFormatCode('0');
                            //5th row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 4, "Pay Mode:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 4)->getFont()->setBold(false);
                            $pay_mode = '';
                            if($payment_type == 'bank'){
                                $pay_mode = 'Bank transfer';
                            }else if($payment_type == 'neft'){
                                $pay_mode = 'NEFT';
                            }else{
                                $pay_mode = ucfirst($payment_type);
                            }
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 4, $pay_mode);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 4)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 4)->applyFromArray($leftstyle);

                            $worksheet->mergeCells('C'.($rowcount+4).':D'.($rowcount+4));
                            $worksheet->mergeCells('E'.($rowcount+4).':G'.($rowcount+4));
                            
                            $objPHPExcel->getActiveSheet()->getStyle('B' . ($rowcount + 4) . ':G' . ($rowcount + 4))->applyFromArray($THStyle);
                            //6th row

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 5, "Paid days:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 5)->getFont()->setBold(true);

                            $prodate = isset($value['empdet']['prodate_type'])? $value['empdet']['prodate_type']:'';
                            if ($prodate == 'Calender Days') {
                                $paid_days = abs($calender_days - $loss_offp);
                            }else{
                                $paid_days =  isset($value['empdet']['0']['payroll_master']['days_presant']) ? abs($value['empdet']['0']['payroll_master']['days_presant']) : '';
                            }

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 5, $paid_days);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 5)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 5)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+5).':D'.($rowcount+5));
                            $worksheet->mergeCells('E'.($rowcount+5).':G'.($rowcount+5));
                            $objPHPExcel->getActiveSheet()->getStyle('B' . ($rowcount + 5) . ':G' . ($rowcount + 5))->applyFromArray($THStyle);
                            
                            $rowcount = $rowcount + 4;

                            $columncount = 1;

                            // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount + 1), 'Salary Slip    ');
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, ($rowcount + 1))->getFont()->setBold(false);
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, ($rowcount + 1))->getFont()->setSize(10);
                            $rowcount = $rowcount + 2;
                            $payment_row = $rowcount;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Earnings (Rs.)');
                             //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->applyFromArray($centerstyle);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Actual Amount');

                            $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Earned Amount');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Deductions (Rs.)');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Amount');
                            for ($i = 1; $i <= 6; $i++) {
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setSize(10);
                            }
                            $rowcount = $rowcount + 1;
                            $ded_row = $rowcount;
                            $arr_data = $value['summary'];

                            if (count($arr_data) >= 0) {
                                $variables = isset($value['variables'])? $value['variables']:0;
                                // debug($value['summary']);exit;
                                if($variables > 0 ){
                                    $k = count($arr_data);
                                    $value['summary'][$k]['ectc']['salary_amount'] = $variables;
                                    $value['summary'][$k]['ectc']['structure_det_value'] = 0;
                                    $value['summary'][$k]['ectc']['salary_head_item_desc'] = 'Variable Pay';
                                }
                                $sum = 0;
                                $tot = 0;
                                $dd = 0;
                                $net = 0;
                                foreach ($value['summary'] as $val) {
                                    $sum = $sum + $val['ectc']['structure_det_value'];
                                    $dd = $dd + $val['ectc']['salary_amount'];
                                    $salary = trim($val['ectc']['salary_head_item_desc']);
                                    $rate = round($val['ectc']['structure_det_value'],2);
                                    $amount = round($val['ectc']['salary_amount']);
                                    //edited by megha on 16/11/19 settlement amount 
                                    $settlement = $value['settle'];
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $salary);
                                    $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $rate);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $amount);

                                    $rowcount++;
                                }
                           
                                $deduction = 0;
                                $arr_withoutComponents = $value['withoutcomponent'];
                                
                                if (count($arr_withoutComponents) > 0) {
                                    foreach ($arr_withoutComponents as $vals) {
                                        $tot = $tot + $vals['ectc']['structure_det_value'];
                                        $net = $net + $vals['ectc']['salary_amount'];
                                        $salary = trim($vals['ectc']['salary_head_item_desc']);
                                        $rate = round($vals['ectc']['structure_det_value'],2);
                                        $amount = round($vals['ectc']['salary_amount'],2);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($ded_row), $salary);
                                        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $rate);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($ded_row), abs($amount));
                                        $deduction += $amount;
                                        $worksheet->mergeCells('C'.$ded_row.':D'.$ded_row);
                                        $ded_row++;
                                    }
                                    $rowcount = max($ded_row, $rowcount);
                                    $rowcount--;
                                }
                                $cell_vis = $rowcount;
                                $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                                $rowcount++;
                                $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "Gross Salary :");
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), formatIndianNumber(abs(round($sum))));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->applyFromArray($righttstyle);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), formatIndianNumber(abs(round($dd))));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->applyFromArray($righttstyle);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), formatIndianNumber(round(abs($deduction))));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->applyFromArray($righttstyle);


                                $status = isset($value['summary']['0']['ed']['status'])?$value['summary']['0']['ed']['status']:0;
                                if ($status == 2) {
                                    $rowcount++;
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), "Settlement Amount");
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), formatIndianNumber(round($settlement)));
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->applyFromArray($righttstyle);
                                }
                                $rowcount++;
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), "Net Salary:");
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                                $net_amount = round($dd + $net + $settlement);
                                //debug($net_amount);exit();
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), formatIndianNumber($net_amount));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->applyFromArray($righttstyle);
                                $rowcount++;
                                $rowcount++;
                                if($net_amount >=0){
                                    $amount_in_words = '';
                                }else{
                                    $amount_in_words =' Negative ';
                                }
                                $amount_in_words .=convertToWords($net_amount);
                                //Amount in words
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "Amount In Words:- Rupees ".$amount_in_words. " Only");
                                $worksheet->mergeCells('B'.$rowcount.':G'.$rowcount);
                                $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                 );
                                $rowcount++;
                                $rowcount++;
                                //Decalaration
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "This is a computer generated Salary Slip and does not require Signature");
                                $worksheet->mergeCells('B'.$rowcount.':G'.$rowcount);
                                $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                 );


                                $pos = $rowcount;
                                $objPHPExcel->getActiveSheet()->getStyle('B' . $start . ':G' . $pos)->applyFromArray($BStyle);
                                $objPHPExcel->getActiveSheet()->getStyle('B' . $start . ':G' . $cell_vis)->applyFromArray($styleArray);
                                $objPHPExcel->getActiveSheet()->getStyle('B' . ($payment_row) . ':G' . ($payment_row))->applyFromArray($THStyle);

                                $start = 0;
                                $pos = 0;
                            } else {
                                $msg = 'No data available under the selected criteria.';
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), (4), $msg);
                                $objPHPExcel->getActiveSheet()->getStyle('B4:G4')->applyFromArray($BStyle);
                                $worksheet->mergeCells('B4:G4');
                            }

                            $rowcount++;
                        } 
                        $rowcount++;
                    }
                    }else {
                        $msg = 'No data available under the selected criteria.';
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), (4), $msg);
                        $objPHPExcel->getActiveSheet()->getStyle('B4:G4')->applyFromArray($BStyle);
                        $worksheet->mergeCells('B4:G4');
                    }
                        }else{
              
                        if(count($arr_salary_for_template)> 0){
                         foreach ($arr_salary_for_template as $val) { 
                             $branch = isset($val['0']['summary']['0']['br']['branch_name']) ? $val['0']['summary']['0']['br']['branch_name']: '';
                            // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount , $rowcount,  $branch);
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , $rowcount)->getFont()->setBold(false);
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , ($rowcount))->getFont()->setSize(10);
                         
                      foreach ($val as $value) {
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount , $rowcount,  $company_name);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , ($rowcount))->getFont()->setSize(10);
                            $worksheet->mergeCells('B' . $rowcount . ':G' . $rowcount);
                            //Form XII
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount , $rowcount,  '[FORM XIII See rule 29(2)]');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , ($rowcount))->getFont()->setSize(10);

                            $worksheet->mergeCells('B' . $rowcount . ':G' . $rowcount);
                            $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );

                            //Pay slip
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Pay Slip - ' . $mname .' '.$year);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount))->getFont()->setSize(10);
                            $worksheet->mergeCells('B' . $rowcount . ':G' . $rowcount);
                            $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );

                      if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                            $i += 1;
                            // $rowcount += 2;
                            $start = $rowcount - 2;
                            // $worksheet->mergeCells('A' . $rowcount . ':D' . $rowcount);
//                        $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
//                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                    );
                            $empstatus = isset($value['summary']['0']['ed']['status']) && $value['summary']['0']['ed']['status'] == "2" ? ' (Resigned)' : '';
                            $emp_name = isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] . " " . $value['summary']['0']['ed']['last_name'] : '';
                            $payment_type = isset($value['summary']['0']['ed']['payment_type']) ? $value['summary']['0']['ed']['payment_type'] : '';
                            $rowcount++;
                            
                            $branch = isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : '';
                            $calender_days = isset($value['empdet']['0']['payroll_master']['calander_days']) ? $value['empdet']['0']['payroll_master']['calander_days'] : '';

                            $daysleave = isset($value['empdet']['0']['payroll_master']['days_leave']) ? $value['empdet']['0']['payroll_master']['days_leave'] : '';
                            $designat = isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : '';
                            $wrktime = isset($value['empdet']['0']['payroll_master']['working_days']) ? $value['empdet']['0']['payroll_master']['working_days'] : '';
                            $loss_offp = isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : '';
                            $department = isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '';
                            $days_present = isset($value['summary']['0']['ar']['presant_total']) ? $value['summary']['0']['ar']['presant_total'] : '';
                            $weekoff = isset($value['summary']['0']['ar']['weekoff_total']) ? $value['summary']['0']['ar']['weekoff_total'] : '';
                            $holiday = isset($value['summary']['0']['ar']['holiday_total']) ? $value['summary']['0']['ar']['holiday_total'] : '';
                            $employee_id = isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : '';
                            $gender = isset($value['summary']['0']['ed']['classification'])?strtoupper($value['summary']['0']['ed']['classification']):'';
                            //edited by megha on 9_7_19 date format changed
                            $join = isset($value['empdet']['0']['ep']['joining_date']) ? date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';
                            //$join = isset($value['empdet']['0']['ep']['joining_date']) ? $value['empdet']['0']['ep']['joining_date'] : '';
                            $termin = isset($value['empdet']['0']['tr']['last_approved_working_date']) ? $value['empdet']['0']['tr']['last_approved_working_date'] : '';
                            $userid = isset($value['empdet']['0']['user_credentials']['user_id']) ? $value['empdet']['0']['user_credentials']['user_id'] : '';
                            
                            $pf = isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : '';
                            $esi = isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : '';
                            $uan =  isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : '';
                            $bank_name = '';
					            $branch_name = '';
					            $ifsc_code = '';
					            $acc_number = '';
								$bank = isset($value['empdet']['0']['payroll_master']['bank_details'])?$value['empdet']['0']['payroll_master']['bank_details']:'';
								if($bank !=''){
					            list($bank_name, $branch_name, $ifsc_code,$acc_number) = explode(',', $bank);
					            }
								if($bank_name == ''){
                                $bank_name = isset($value['summary']['0']['ed']['bank_name'])?$value['summary']['0']['ed']['bank_name']:'';
								}
								if($branch_name == ''){
                                $branch_name = isset($value['summary']['0']['ed']['branch_name'])?$value['summary']['0']['ed']['branch_name']:'';
								}
								if($ifsc_code == ''){
                                $ifsc_code = isset($value['summary']['0']['ed']['ifsc_code'])?$value['summary']['0']['ed']['ifsc_code']:'';
								}
								if($acc_number == ''){
                                $acc_number = isset($value['summary']['0']['ed']['account_no'])?$value['summary']['0']['ed']['account_no']:'';
								} 
                            //1st row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Emp Code : ");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(false);
                            
                            $worksheet->mergeCells('C'.$rowcount.':E'.$rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, $employee_id);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->applyFromArray($leftstyle);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount, "Bank : ");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount, $bank_name);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->applyFromArray($leftstyle);
                            //2nd row 
                            // $worksheet->mergeCells('C'.$rowcount.':E'.$rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 1, 'Name :');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 1)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 1, $emp_name.$empstatus);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 1)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 1)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+1).':E'.($rowcount+1));
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount + 1, "A/C NO:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount + 1)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 1, ($acc_number));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 1)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 1)->applyFromArray($leftstyle);

                            //3rd row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 2, 'Department:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 2)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 2, $department);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 2)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 2)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+2).':E'.($rowcount+2));

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount + 2, 'UAN NO:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount + 2)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 2, $uan);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 2)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 2)->applyFromArray($leftstyle);
                            $objPHPExcel->getActiveSheet()->getStyle('G'.($rowcount+2))->getNumberFormat()->setFormatCode('0');
                            //4th row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 3, 'Designation:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 3)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 3, $designat);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 3)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 3)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+3).':E'.($rowcount+3));

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount + 3, 'ESI NO:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount + 3)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 3, ($esi));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 3)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 3)->applyFromArray($leftstyle);
                            $objPHPExcel->getActiveSheet()->getStyle('G'.($rowcount+3))->getNumberFormat()->setFormatCode('0');
                            //5th row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 4, "Pay Mode:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 4)->getFont()->setBold(false);
                            
                            $pay_mode = '';
                            if($payment_type == 'bank'){
                                $pay_mode = 'Bank transfer';
                            }else if($payment_type == 'neft'){
                                $pay_mode = 'NEFT';
                            }else{
                                $pay_mode = ucfirst($payment_type);
                            }
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 4, $pay_mode);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 4)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 4)->applyFromArray($leftstyle);

                            $worksheet->mergeCells('C'.($rowcount+4).':D'.($rowcount+4));
                            $worksheet->mergeCells('E'.($rowcount+4).':G'.($rowcount+4));
                            
                            $objPHPExcel->getActiveSheet()->getStyle('B' . ($rowcount + 4) . ':G' . ($rowcount + 4))->applyFromArray($THStyle);
                            //6th row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 5, "Paid days:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 5)->getFont()->setBold(true);
                            $prodate = isset($value['empdet']['prodate_type'])? $value['empdet']['prodate_type']:'';
                            if ($prodate == 'Calender Days') {
                                $paid_days = abs($calender_days - $loss_offp);
                            }else{
                                $paid_days =  isset($value['empdet']['0']['payroll_master']['days_presant']) ? abs($value['empdet']['0']['payroll_master']['days_presant']) : '';
                            }
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 5, $paid_days);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 5)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount + 5)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+5).':D'.($rowcount+5));
                            $worksheet->mergeCells('E'.($rowcount+5).':G'.($rowcount+5));
                            $objPHPExcel->getActiveSheet()->getStyle('B' . ($rowcount + 5) . ':G' . ($rowcount + 5))->applyFromArray($THStyle);
                            
                            $rowcount = $rowcount + 4;

                            $columncount = 1;

                            // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount + 1), 'Salary Slip    ');
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, ($rowcount + 1))->getFont()->setBold(false);
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, ($rowcount + 1))->getFont()->setSize(10);
                            $rowcount = $rowcount + 2;
                            $payment_row = $rowcount;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Earnings (Rs.)');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Actual Amount');
                            $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Earned Amount');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Deductions (Rs.)');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Amount');
                            for ($i = 1; $i <= 6; $i++) {
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setSize(10);
                            }
                            $rowcount = $rowcount + 1;
                            $ded_row = $rowcount;
                            $arr_data = $value['summary'];
                            if (count($arr_data) >= 0) {
                                $variables = isset($value['variables'])? $value['variables']:0;
                                // debug($value['summary']);exit;
                                if($variables > 0 ){
                                    $k = count($arr_data);
                                    $value['summary'][$k]['ectc']['salary_amount'] = $variables;
                                    $value['summary'][$k]['ectc']['structure_det_value'] = 0;
                                    $value['summary'][$k]['ectc']['salary_head_item_desc'] = 'Variable Pay';
                                }
                                $sum = 0;
                                $tot = 0;
                                $dd = 0;
                                $net = 0;
                                foreach ($value['summary'] as $val) {
                                    $sum = $sum + $val['ectc']['structure_det_value'];
                                    $dd = $dd + $val['ectc']['salary_amount'];
                                    $salary = trim($val['ectc']['salary_head_item_desc']);
                                    $rate = round($val['ectc']['structure_det_value'],2);
                                    $amount = round($val['ectc']['salary_amount']);
                                    //edited by megha on 16/11/19 settlement amount 
                                    $settlement = $value['settle'];
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $salary);
                                    $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $rate);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $amount);

                                    $rowcount++;
                                }
                           
                                $deduction = 0;
                                $arr_withoutComponents = $value['withoutcomponent'];
                                if (count($arr_withoutComponents) > 0) {
                                    foreach ($arr_withoutComponents as $vals) {
                                        $tot = $tot + $vals['ectc']['structure_det_value'];
                                        $net = $net + $vals['ectc']['salary_amount'];
                                        $salary = trim($vals['ectc']['salary_head_item_desc']);
                                        $rate = round($vals['ectc']['structure_det_value'],2);
                                        $amount = round($vals['ectc']['salary_amount'],2);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($ded_row), $salary);
                                        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $rate);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($ded_row), abs($amount));
                                        $deduction += $amount;
                                        $worksheet->mergeCells('C'.$ded_row.':D'.$ded_row);
                                        $ded_row++;
                                    }
                                    $rowcount = max($ded_row, $rowcount);
                                    $rowcount--;
                                }
                                $cell_vis = $rowcount;
                                $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                                $rowcount++;
                                $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "Gross Salary :");
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), formatIndianNumber(abs(round($sum))));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->applyFromArray($righttstyle);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), formatIndianNumber(abs(round($dd))));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->applyFromArray($righttstyle);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), formatIndianNumber(round(abs($deduction))));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->applyFromArray($righttstyle);

                            

                                $status = isset($value['summary']['0']['ed']['status'])?$value['summary']['0']['ed']['status']:0;
                                if ($status == 2) {
                                    $rowcount++;
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), "Settlement Amount");
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), formatIndianNumber(round($settlement)));
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->applyFromArray($righttstyle);
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                }
                                $rowcount++;
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), "Net Salary:");
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                                $net_amount = round($dd + $net + $settlement);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), formatIndianNumber($net_amount));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->applyFromArray($righttstyle);
                                $rowcount++;
                                $rowcount++;
                                if($net_amount >=0){
                                    $amount_in_words = '';
                                }else{
                                    $amount_in_words =' Negative ';
                                }
                                $amount_in_words .= convertToWords($net_amount);
                                //Amount in words
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "Amount In Words:- Rupees ".$amount_in_words." Only");
                                $worksheet->mergeCells('B'.$rowcount.':G'.$rowcount);
                                $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                 );
                                $rowcount++;
                                $rowcount++;
                                //Decalaration
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "This is a computer generated Salary Slip and does not require Signature");
                                $worksheet->mergeCells('B'.$rowcount.':G'.$rowcount);
                                $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                 );


                                $pos = $rowcount;
                                $objPHPExcel->getActiveSheet()->getStyle('B' . $start . ':G' . $pos)->applyFromArray($BStyle);
                                $objPHPExcel->getActiveSheet()->getStyle('B' . $start . ':G' . $cell_vis)->applyFromArray($styleArray);
                                $objPHPExcel->getActiveSheet()->getStyle('B' . ($payment_row) . ':G' . ($payment_row))->applyFromArray($THStyle);

                                $start = 0;
                                $pos = 0;
                            } else {
                                $msg = 'No data available under the selected criteria.';
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), (4), $msg);
                                $objPHPExcel->getActiveSheet()->getStyle('B4:G4')->applyFromArray($BStyle);
                                $worksheet->mergeCells('B4:G4');
                            }

                            $rowcount++;
                        }
                        $rowcount++;
                    }
                      }
                    }else {
                        $msg = 'No data available under the selected criteria.';
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), (4), $msg);
                        $objPHPExcel->getActiveSheet()->getStyle('B4:G4')->applyFromArray($BStyle);
                        $worksheet->mergeCells('B4:G4');
                       
                    }
                    }
                    // exit;
                    //Hide grid lines
                    $objPHPExcel->getActiveSheet()->setShowGridlines(false); 
                    $objPHPExcel->getActiveSheet()->setSelectedCells('B4');

                    $objPHPExcel->getActiveSheet()->setTitle('Salary Slip');

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
                    $this->render('deposit_sensite');
                    break;
            }
        }
    }


 private function GenerateSalarySlipreportVayalatnew($mode, $rep_month) {
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');
        if($user_group == 1){
            $arr_form_data = $_REQUEST;
            $condition_approve = " and payroll_master.action in ('Approved','Processed') ";
        }else{
            $user_id = $this->Session->read('login_user_id');
            $arr_emp_pkey = $this->EmpCtcTransaction->query("SELECT DISTINCT emp_fkey FROM user_credentials uc WHERE uc.user_id =  '$user_id'");
            $pkey = isset($arr_emp_pkey[0]['uc']['emp_fkey'])? $arr_emp_pkey[0]['uc']['emp_fkey']:0;

            $arr_form_data['hidden-report-type'] =  'DepositslipSynthite';
            $arr_form_data['hidden-criterias-count'] = '1';
            $arr_form_data['hidden-reportfields'] = '';
            $arr_form_data['reportfrom'] = $rep_month;
            $arr_form_data['hidden-criteria1'] = 'EmployeeDetails';
            $arr_form_data['select-criteria1'] =  'EmployeeDetails';
            $arr_form_data['EmployeeDetails'][] = $pkey;
            $arr_form_data['resigned'] = '0';
            $arr_form_data['ngtvsal'] = '1';
            $arr_form_data['selectall'] = '0';
            // debug($arr_form_data);
            $condition_approve = " and payroll_master.action in ('Approved') ";
        }
        
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        //Functions for view and pdf
        function convertToWords($number)
        {
            $number = abs($number);
            $words = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine');
            $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');
            $teens = array('Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');

            $amount_in_words = '';

            if ($number < 10) {
                $amount_in_words .= $words[$number];
            } elseif ($number < 20) {
                $amount_in_words .= $teens[$number - 10];
            } elseif ($number < 100) {
                $amount_in_words .= $tens[floor($number / 10)];
                if ($number % 10 > 0) {
                    $amount_in_words .= ' ' . $words[$number % 10];
                }
            } elseif ($number < 1000) {
                $amount_in_words .= $words[floor($number / 100)] . ' Hundred';
                if ($number % 100 > 0) {
                    $amount_in_words .= ' and ' . convertToWords($number % 100);
                }
            } elseif ($number < 100000) {
                $amount_in_words .= convertToWords(floor($number / 1000)) . ' Thousand';
                if ($number % 1000 > 0) {
                    $amount_in_words .= ' ' . convertToWords($number % 1000);
                }
            } elseif ($number < 10000000) {
                $amount_in_words .= convertToWords(floor($number / 100000)) . ' Lakh';
                if ($number % 100000 > 0) {
                    $amount_in_words .= ' ' . convertToWords($number % 100000);
                }
            } else {
                $amount_in_words .= convertToWords(floor($number / 10000000)) . ' Crore';
                if ($number % 10000000 > 0) {
                    $amount_in_words .= ' ' . convertToWords($number % 10000000);
                }
            }

            return $amount_in_words;
        }
    function formatIndianNumber($amount)
        {
            setlocale(LC_MONETARY, 'en_IN');
            $amount = money_format('%!i', $amount);
            return rtrim(rtrim($amount, '0'), '.');
        }
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';

            if ($str_criteria_item == '') {

                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        $variable_key = array();
        //Variable
        $variable_keys = $this->EmpCtcTransaction->query("select trim(salary_head_item_desc) as sal_head,ectc.head_operator FROM emp_salary_slip as ectc
        left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
        left join salary_heads on (salary_heads.head_pkey = salhead.head_fkey) where ectc.item_part='Direct' and salary_heads.head_pkey in (2,7,9)
        and end_date_effective is null  and month_year = '$from' Group by salary_head_item_desc ORDER BY salhead.salary_head_item_order1 asc");

        foreach ($variable_keys as $val) {
            if ($val['ectc']['head_operator'] == 'Addition') {
                $variable_key['VAddition'][] = $val[0]['sal_head'];
            } 
        }
        // debug( $variable_key);exit;
        $this->set($variable_key,'variable_key');
        $condition = "and ed.status = '1'  ";

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and ed.status in ('1','2') ";
        }
        if(isset($arr_form_data['ngtvsal']) && $arr_form_data['ngtvsal'] == '0') {
            $condition .= " and payroll_master.net_salary >= 0 ";
        }
        $arr_salary_for_template = array();
        $arr_empleaverequests = array();

        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
            if($str_criteria_item == 'EmployeeDetails'){
                $pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
                 $arr_empleaverequests = $this->EmpCtcTransaction->query("select distinct ectc.month_year,br.branch_name,ed.first_name,"
                . "ed.middile_name,ed.last_name,ed.status,ed.payment_type,ectc.payroll_master_fkey,ectc.head_operator,ectc.head_type,ectc.item_part,ectc.salary_head_item_desc,
                ectc.structure_det_value,ectc.salary_amount,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.pf,ed.classification,"
                        . "ed.ifsc_code,ed.esi,ep.joining_date,ed.status,ar.weekoff_total,ar.presant_total,ar.holiday_total,payroll_master.days_leave "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and round(ectc.salary_amount) != '0' 
                           and ectc.emp_fkey = '$leavepolicygroupid' and desg.status = 1 "
                        . "and ectc.month_year ='$from' "
                        . $condition
                        . "and end_date_effective is null $condition_approve and ar.isdelete= 'N' and ar.month_year= '$from' order by salhead.salary_head_item_order1");
                        $arr_empleaverequests1 = $this->EmpCtcTransaction->query("select distinct ectc.month_year,br.branch_name,ed.first_name,"
                        . "ed.middile_name,ed.last_name,ed.status,ectc.payroll_master_fkey,ectc.head_operator,ectc.head_type,ectc.item_part,ectc.salary_head_item_desc,
                        ectc.structure_det_value,ectc.salary_amount,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.pf,ed.classification,"
                        . "ed.ifsc_code,ed.esi,ep.joining_date,ed.status,ar.weekoff_total,ar.presant_total,ar.holiday_total,payroll_master.days_leave "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join site_attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and round(ectc.salary_amount) != '0' 
                           and ectc.emp_fkey = '$leavepolicygroupid' and desg.status = 1 "
                        . "and ectc.month_year ='$from' "
                        . $condition
                        . "and end_date_effective is null $condition_approve and ar.month_year= '$from' order by salhead.salary_head_item_order1");
                        $arr_empleaverequests = array_merge($arr_empleaverequests, $arr_empleaverequests1);
                //edited by megha on 9_7_19 salary head component ordering
                $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.middile_name,ed.last_name,ectc.salary_head_item_desc,
                    ectc.structure_det_value,ectc.salary_amount "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                            left join branches as br on (br.branch_code = ep.emp_branch)
                             left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                                          where ectc.head_operator = 'Deduction'  and round(ectc.salary_amount) != '0' 
                                          and ectc.emp_fkey = '$leavepolicygroupid' "
                        . " and ectc.month_year ='$from' "
                        . "$condition and "
                        . "end_date_effective is null $condition_approve order by salhead.salary_head_item_order1");
                 
  $lop = $this->EmpCtcTransaction->query("SELECT month_year,emp_fkey,SUM(salary_rate) AS total_salary_amount,presant_total,leave_total,
    lop_total FROM emp_salary_slip AS ectc LEFT JOIN emp_details AS ed ON ed.emp_pkey = ectc.emp_fkey WHERE head_operator = 'Addition' AND head_type != 'Manually'
    AND item_part = 'Direct' AND ectc.emp_fkey = '$leavepolicygroupid' AND ectc.month_year = '$from' AND end_date_effective IS NULL");

                $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
                $empdetails = $this->EmpCtcTransaction->query("select ep.designation,ed.payment_type,tr.last_approved_working_date,user_credentials.user_id,ep.emp_dept,ep.emp_company_id,ep.joining_date,d.dept_name,dd.desig_name,payroll_master.calander_days,payroll_master.days_leave,payroll_master.working_days,payroll_master.loss_of_pay,payroll_master.days_presant "
                        . "from payroll_master "
                        . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                        . "left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey) "
                        . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey) "
                        . "left join department as d on(d.dept_code = ep.emp_dept) "
                        . "left join designation as dd on (dd.desig_code = ep.designation)"
                        . "left join emp_details as ed on (ed.emp_pkey = payroll_master.emp_fkey)"
                        . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
                        . " and payroll_master.month_year ='$from' and dd.status = 1 "
                        . " and payroll_master.emp_fkey = '$leavepolicygroupid' $condition_approve ");
            
            $pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
            $empdetails['prodate_type'] = isset($pro_date_desc['type'])? $pro_date_desc['type']:'';

            $arr_settle = $this->EmpCtcTransaction->query(" select sum(salary_amount) from emp_settle_slip as ectc  
            left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
            left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
            left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
            where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.emp_pkey='$leavepolicygroupid' and date_format(termination.last_approved_working_date,'%Y-%m') = '$from'  group by emp_details.emp_pkey");
                $settle = isset($arr_settle['0']['0']['sum(salary_amount)']) ? $arr_settle['0']['0']['sum(salary_amount)'] : 0;
           if(!empty($arr_empleaverequests)){
                $arr_empleaverequests_refined = array();
                $incentives = 0;
                
                foreach($arr_empleaverequests as $value){
                    if(in_array($value['ectc']['salary_head_item_desc'], $variable_key['VAddition'])){
                        $incentives += isset($value['ectc']['salary_amount']) ? abs(round($value['ectc']['salary_amount'])) : 0;
                    }else{
                        $arr_empleaverequests_refined[] = $value;
                    }
                }
                $arr_empleaverequests = $arr_empleaverequests_refined;

                    $arr_salary_for_template[] = array(
                        'summary' => $arr_empleaverequests,
                        'withoutcomponent' => $salaryslipwithoutcomponents,
                        'empdet' => $empdetails,
                        'settle' => $settle,
                        'variables'=> $incentives,
                        'lopdeduction'=> $lop
                    );


           }
            } else{
               $arr_list = $this->EmpCtcTransaction->query("select distinct(ed.emp_pkey) 
                           from emp_salary_slip as ectc left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           left join employee_info as ei on (ei.emp_pkey = ed.emp_pkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and round(ectc.salary_amount) != '0' 
                           and ep.emp_branch = '$leavepolicygroupid' and ectc.month_year ='$from'  $condition and desg.status = 1 
                           and end_date_effective is null $condition_approve order by ei.EmpName");
               if (isset($arr_list) && !empty($arr_list)) {
                    foreach ($arr_list as $emps) {
                     $emp = $emps['ed']['emp_pkey'];
                     $arr_empleaverequests = $this->EmpCtcTransaction->query("select distinct ectc.month_year,br.branch_name,ed.first_name,"
                        . "ed.middile_name,ed.last_name,ed.status,ectc.payroll_master_fkey,ectc.head_operator,ectc.head_type,ectc.item_part,ectc.salary_head_item_desc,
                        ectc.structure_det_value,ectc.salary_amount,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.pf,ed.classification,"
                        . "ed.ifsc_code,ed.esi,ed.payment_type,ep.joining_date,ed.status,ar.weekoff_total,ar.presant_total,ar.holiday_total,payroll_master.days_leave "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and round(ectc.salary_amount) != '0' 
                           and ectc.emp_fkey = '$emp' and desg.status = 1 "
                        . "and ectc.month_year ='$from' $condition and end_date_effective is null $condition_approve and ar.isdelete= 'N' and ar.month_year= '$from' order by salhead.salary_head_item_order1");
                        $arr_empleaverequests1 = $this->EmpCtcTransaction->query("select distinct ectc.month_year,br.branch_name,ed.first_name,"
                        . "ed.middile_name,ed.last_name,ed.status,ectc.payroll_master_fkey,ectc.head_operator,ectc.head_type,ectc.item_part,ectc.salary_head_item_desc,
                        ectc.structure_det_value,ectc.salary_amount,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.pf,ed.classification,"
                        . "ed.ifsc_code,ed.esi,ep.joining_date,ed.status,ar.weekoff_total,ar.presant_total,ar.holiday_total,payroll_master.days_leave "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join site_attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and round(ectc.salary_amount) != '0' and desg.status = 1 
                           and ectc.emp_fkey = '$emp' and ectc.month_year ='$from' $condition and end_date_effective is null $condition_approve and ar.month_year= '$from' order by salhead.salary_head_item_order1");
                        $arr_empleaverequests = array_merge($arr_empleaverequests, $arr_empleaverequests1);
                //edited by megha on 9_7_19 salary head component ordering
                        $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.middile_name,ed.last_name,ectc.salary_head_item_desc,
                        ectc.structure_det_value,ectc.salary_amount from emp_salary_slip as ectc left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                            where ectc.head_operator = 'Deduction'  and round(ectc.salary_amount) != '0' 
                            and ectc.emp_fkey = '$emp' and ectc.month_year ='$from' $condition and "
                        . "end_date_effective is null $condition_approve order by salhead.salary_head_item_order1");

                            $lop = $this->EmpCtcTransaction->query("SELECT month_year,emp_fkey,SUM(salary_rate) AS total_salary_amount,presant_total,leave_total,
    lop_total FROM emp_salary_slip AS ectc LEFT JOIN emp_details AS ed ON ed.emp_pkey = ectc.emp_fkey WHERE head_operator = 'Addition' AND head_type != 'Manually'
    AND item_part = 'Direct' AND ectc.emp_fkey = '$emp' AND ectc.month_year = '$from' AND end_date_effective IS NULL");


                $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
                $empdetails = $this->EmpCtcTransaction->query("select ep.designation,ed.payment_type,tr.last_approved_working_date,user_credentials.user_id,ep.emp_dept,ep.emp_company_id,ep.joining_date,d.dept_name,dd.desig_name,payroll_master.calander_days,payroll_master.days_leave,payroll_master.working_days,payroll_master.loss_of_pay,payroll_master.days_presant "
                        . "from payroll_master "
                        . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                        . "left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey) "
                        . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey) "
                        . "left join department as d on(d.dept_code = ep.emp_dept) "
                        . "left join designation as dd on (dd.desig_code = ep.designation) "
                        . "left join emp_details as ed on (ed.emp_pkey = payroll_master.emp_fkey)"
                        . "where payroll_master.payroll_master_pkey = '$payroll_pkey' "
                        . "and payroll_master.month_year ='$from' and dd.status = 1 "
                        . "and payroll_master.emp_fkey = '$emp' $condition_approve ");
                $pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
                $empdetails['prodate_type'] = isset($pro_date_desc['type'])? $pro_date_desc['type']:'';
            $arr_settle = $this->EmpCtcTransaction->query(" select sum(salary_amount) from emp_settle_slip as ectc  
            left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
            left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
            left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
            where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.emp_pkey='$emp' and 
                date_format(termination.last_approved_working_date,'%Y-%m') = '$from'  group by emp_details.emp_pkey");
                $settle = isset($arr_settle['0']['0']['sum(salary_amount)']) ? $arr_settle['0']['0']['sum(salary_amount)'] : 0;
                if(!empty($arr_empleaverequests)){
                $arr_empleaverequests_refined = array();
                $incentives = 0;
                foreach($arr_empleaverequests as $value){
                    //if(isset($variable_key['VAddition']))
                    if(isset($variable_key['VAddition']) && in_array($value['ectc']['salary_head_item_desc'], $variable_key['VAddition'])){
                        $incentives += isset($value['ectc']['salary_amount']) ? abs(round($value['ectc']['salary_amount'])) : 0;
                    }else{
                        $arr_empleaverequests_refined[] = $value;
                    }
                }
                $arr_empleaverequests = $arr_empleaverequests_refined;

                    $arr_salary_for_template[$leavepolicygroupid][] = array( 
                        'summary' => $arr_empleaverequests,
                        'withoutcomponent' => $salaryslipwithoutcomponents,
                        'empdet' => $empdetails,
                        'settle' => $settle,
                        'variables'=> $incentives,
                        'lopdeduction' => $lop
                    );

                }
                }
               }
            }
            }

//debug($arr_salary_for_template);
            $this->set('arr_salary_for_template', $arr_salary_for_template);

            //Function to convert amount to words


            $employee_attendance = array();
            if(!empty($arr_empleaverequests)){
            foreach ($arr_empleaverequests as $val) {
                $emp = $val['ectc']['head_operator'];
                $emppk = $val['ectc']['head_type'];
                $itempart = $val['ectc']['item_part'];
                $employee_attendance[$emppk][$emp][$itempart][] = $val;
            }}
            $this->set('employee_attendance', $employee_attendance);
            $cr = $arr_form_data['select-criteria1'];
            $this->set('cr', $cr);

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
             $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $time=strtotime($f);
            $month=date("m",$time);

            $mname = date('F', mktime(0, 0, 0, $month, 10));
            $month1 =  $month.'-01'; 
            $year=date("Y",$time);
           
            $this->set('mname1', $mname);
            $this->set('y1', $year);
            $user_id = $this->Session->read('login_user_id');
            $date_time = date('d-m-Y H:i');

            $this->set('user_id',$user_id);
            $this->set('date_time', $date_time); 
            $this->set('month', $month);
            

            switch ($mode) {
                case 'pdf' :

                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('salaryslip_vayalatnew');
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    // debug($view_output);exit;
                    $html2pdf->writeHTML($view_output);
                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_Salaryslip" . $from .'.pdf':"SalarySlip" . strtotime() .".pdf";
                    $html2pdf->Output($file_name, 'D');
                    
                    // $this->render('salaryslip_vayalat');

                    break;
                case 'excel' :
                    
                    // function convertToWords($number)
                    // {   
                    //     $number = abs($number);
                    //     $words = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine');
                    //     $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');
                    //     $teens = array('Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
    
                    //     $amount_in_words = '';
    
                    //     if ($number < 10) {
                    //         $amount_in_words .= $words[$number];
                    //     } elseif ($number < 20) {
                    //         $amount_in_words .= $teens[$number - 10];
                    //     } elseif ($number < 100) {
                    //         $amount_in_words .= $tens[floor($number / 10)];
                    //         if ($number % 10 > 0) {
                    //             $amount_in_words .= ' ' . $words[$number % 10];
                    //         }
                    //     } elseif ($number < 1000) {
                    //         $amount_in_words .= $words[floor($number / 100)] . ' Hundred';
                    //         if ($number % 100 > 0) {
                    //             $amount_in_words .= ' and ' . convertToWords($number % 100);
                    //         }
                    //     } elseif ($number < 100000) {
                    //         $amount_in_words .= convertToWords(floor($number / 1000)) . ' Thousand';
                    //         if ($number % 1000 > 0) {
                    //             $amount_in_words .= ' ' . convertToWords($number % 1000);
                    //         }
                    //     } elseif ($number < 10000000) {
                    //         $amount_in_words .= convertToWords(floor($number / 100000)) . ' Lakh';
                    //         if ($number % 100000 > 0) {
                    //             $amount_in_words .= ' ' . convertToWords($number % 100000);
                    //         }
                    //     } else {
                    //         $amount_in_words .= convertToWords(floor($number / 10000000)) . ' Crore';
                    //         if ($number % 10000000 > 0) {
                    //             $amount_in_words .= ' ' . convertToWords($number % 10000000);
                    //         }
                    //     }
    
                    //     return $amount_in_words;
                    // }

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_Salaryslip" . $from . ".xlsx" : "SalarySlip" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();
                    $worksheet = $objPHPExcel->getActiveSheet();
                    $style = $worksheet->getStyleByColumnAndRow(1, 4);

                         // Set the font to bold
                    $style->getFont()->setBold(true);

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                    $worksheet->getColumnDimension('A')->setWidth(5);
                    $worksheet->getColumnDimension('B')->setWidth(30);
                    $worksheet->getColumnDimension('C')->setWidth(20);
                    $worksheet->getColumnDimension('D')->setWidth(10);
                    $worksheet->getColumnDimension('E')->setWidth(20);
                    $worksheet->getColumnDimension('F')->setWidth(30);
                    $worksheet->getColumnDimension('G')->setWidth(50);

                    // $worksheet->setCellValueByColumnAndRow(0, 1, " ");
                    $worksheet->setCellValueByColumnAndRow(1, 1, "Salary Slip - ".$mname." ".$year  );
                    
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 2)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 2)->getFont()->setSize(11);
                    for ($col = 'A'; $col !== 'BZ'; $col++) {
                        $objPHPExcel->getActiveSheet()
                                ->getColumnDimension($col)
                                ->setAutoSize(false);
                    }
                    // $worksheet->mergeCells('B1:G1');
                    // $worksheet->getStyle('B1')->getAlignment()->applyFromArray(
                    //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    // );
    
                    $worksheet->mergeCells('B1:G1');
                    $worksheet->getStyle('B1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
    
                        $worksheet->setCellValueByColumnAndRow(1, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()
                    ->getStyle('B2')
                    ->getFont()
                    ->getColor()
                    ->setRGB ('FF0000'); 
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(14);
                    $worksheet->mergeCells('B2:G2');
                    $worksheet->mergeCells('B3:G3');
                    $worksheet->getStyle('B2')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    //Border style
                    $objPHPExcel->setActiveSheetIndex(0);
                    $BStyle = array(
                        'borders' => array(
                            'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );

                    $THStyle = array(
                        'borders' => array(
                            'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            ),
                            'left' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ),
                            'right' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ),
                        )
                    );

                    //Left align
                    $leftstyle = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        )
                    );

                    //Right align
                    $righttstyle = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        )
                    );
                    
                    //Grid lines
                                    //Border style
                $styleArray = array(
                    'borders' => array(
                      'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                      )
                    )
                  );
                    


                    $worksheet->getStyle('B1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    $rowcount = 2;
                    $col = 0;

                    $worksheet->getStyle('B2')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );


                    $worksheet->getStyle('B3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $company_name = isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : '';

                    $columncount = 1;
                    $rowcount = 4;
                    if($cr == 'EmployeeDetails'){ 
                       
                        if(count($arr_salary_for_template)>0){
                        foreach ($arr_salary_for_template as $value) {
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount , $rowcount,  $company_name);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , ($rowcount))->getFont()->setSize(10);
                            $worksheet->mergeCells('B' . $rowcount . ':G' . $rowcount);
                            //Form XII
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount , $rowcount,  '[FORM XIII See rule 29(2)]');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , ($rowcount))->getFont()->setSize(10);

                            $worksheet->mergeCells('B' . $rowcount . ':G' . $rowcount);
                            $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );

                            //Pay slip
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Pay Slip -' . $mname .' '.$year);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount))->getFont()->setSize(10);
                            $worksheet->mergeCells('B' . $rowcount . ':G' . $rowcount);
                            $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );

                      if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                            $i += 1;
                            
                            $start = $rowcount - 2;
                            // $worksheet->mergeCells('A' . $rowcount . ':D' . $rowcount);
//                        $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
//                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                    );
                            $empstatus = isset($value['summary']['0']['ed']['status']) && $value['summary']['0']['ed']['status'] == "2" ? ' (Resigned)' : '';
                            $emp_name = isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] . " " . $value['summary']['0']['ed']['last_name'] : '';
                          
                            $payment_type =isset($value['summary']['0']['ed']['payment_type']) ? $value['summary']['0']['ed']['payment_type'] : '';
                            $rowcount++;
                            $branch = isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : '';
                            // $calender_days = isset($value['empdet']['0']['payroll_master']['calander_days']) ? $value['empdet']['0']['payroll_master']['calander_days'] : '';
                            // $loss_offp = isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : '';

                             $calender_days = isset($value['empdet']['0']['payroll_master']['calander_days']) ? $value['empdet']['0']['payroll_master']['calander_days'] : '';
                             $loss_offp = isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : '';

                            $daysleave = isset($value['empdet']['0']['payroll_master']['days_leave']) ? $value['empdet']['0']['payroll_master']['days_leave'] : '';
                            $designat = isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : '';
                            $wrktime = isset($value['empdet']['0']['payroll_master']['working_days']) ? $value['empdet']['0']['payroll_master']['working_days'] : '';
                          
                            $department = isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '';
                            $days_present = isset($value['summary']['0']['ar']['presant_total']) ? $value['summary']['0']['ar']['presant_total'] : '';
                            $weekoff = isset($value['summary']['0']['ar']['weekoff_total']) ? $value['summary']['0']['ar']['weekoff_total'] : '';
                            $holiday = isset($value['summary']['0']['ar']['holiday_total']) ? $value['summary']['0']['ar']['holiday_total'] : '';
                            $employee_id = isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : '';
                            $gender = isset($value['summary']['0']['ed']['classification'])?strtoupper($value['summary']['0']['ed']['classification']):'';
                            //edited by megha on 9_7_19 date format changed
                           $join = isset($value['empdet']['0']['ep']['joining_date']) ? date('j-M-y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';

                            //$join = isset($value['empdet']['0']['ep']['joining_date']) ? $value['empdet']['0']['ep']['joining_date'] : '';
                            $termin = isset($value['empdet']['0']['tr']['last_approved_working_date']) ? $value['empdet']['0']['tr']['last_approved_working_date'] : '';
                            $userid = isset($value['empdet']['0']['user_credentials']['user_id']) ? $value['empdet']['0']['user_credentials']['user_id'] : '';
                            
                            $pf = isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : '';
                            $esi = isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : '';
                            $uan =  isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : '';
                            $bank_name = '';
                                $branch_name = '';
                                $ifsc_code = '';
                                $acc_number = '';
                                $bank = isset($value['empdet']['0']['payroll_master']['bank_details'])?$value['empdet']['0']['payroll_master']['bank_details']:'';
                                if($bank !=''){
                                list($bank_name, $branch_name, $ifsc_code,$acc_number) = explode(',', $bank);
                                }
                                if($bank_name == ''){
                                $bank_name = isset($value['summary']['0']['ed']['bank_name'])?$value['summary']['0']['ed']['bank_name']:'';
                                }
                                if($branch_name == ''){
                                $branch_name = isset($value['summary']['0']['ed']['branch_name'])?$value['summary']['0']['ed']['branch_name']:'';
                                }
                                if($ifsc_code == ''){
                                $ifsc_code = isset($value['summary']['0']['ed']['ifsc_code'])?$value['summary']['0']['ed']['ifsc_code']:'';
                                }
                                if($acc_number == ''){
                                $acc_number = isset($value['summary']['0']['ed']['account_no'])?$value['summary']['0']['ed']['account_no']:'';
                                } 
                            //1st row
                           $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Emp Code : ");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(false);
                            
                            $worksheet->mergeCells('C'.$rowcount.':E'.$rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, $employee_id);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->applyFromArray($leftstyle);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount, "DOJ : ");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount, $join);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->applyFromArray($leftstyle);
                            //2nd row 
                            // $worksheet->mergeCells('C'.$rowcount.':E'.$rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 1, 'Name :');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 1)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 1, $emp_name.$empstatus);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 1)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 1)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+1).':E'.($rowcount+1));
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount + 1, "UAN No:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount + 1)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 1, ($uan));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 1)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 1)->applyFromArray($leftstyle);

                            //3rd row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 2, 'Department:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 2)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 2, $department);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 2)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 2)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+2).':E'.($rowcount+2));

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount + 2, 'IP No:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount + 2)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 2, $esi);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 2)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 2)->applyFromArray($leftstyle);
                            $objPHPExcel->getActiveSheet()->getStyle('G'.($rowcount+2))->getNumberFormat()->setFormatCode('0');
                            //4th row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 3, 'Designation:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 3)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 3, $designat);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 3)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 3)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+3).':E'.($rowcount+3));

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount + 3, 'Bank:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount + 3)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 3, ($bank_name));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 3)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 3)->applyFromArray($leftstyle);
                            $objPHPExcel->getActiveSheet()->getStyle('G'.($rowcount+3))->getNumberFormat()->setFormatCode('0');
                            //5th row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 4, "Pay Mode:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 4)->getFont()->setBold(false);
                            
                            
                            $pay_mode = '';
                            if($payment_type == 'bank'){
                                $pay_mode = 'Bank transfer';
                            }else if($payment_type == 'neft'){
                                $pay_mode = 'NEFT';
                            }else{
                                $pay_mode = ucfirst($payment_type);
                            }
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 4, $pay_mode);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 4)->getFont()->setBold(false);
                             $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount +4, $rowcount + 4, "Ac No:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount +4, $rowcount + 4)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 4, $acc_number);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 4)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 4)->applyFromArray($leftstyle);
                             $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 4)->applyFromArray($leftstyle);

                            $worksheet->mergeCells('C'.($rowcount+4).':E'.($rowcount+4));
                            //$worksheet->mergeCells('E'.($rowcount+4).':G'.($rowcount+4));
                            
                            $objPHPExcel->getActiveSheet()->getStyle('B' . ($rowcount + 4) . ':G' . ($rowcount + 4))->applyFromArray($THStyle);
                            
                            //$objPHPExcel->getActiveSheet()->getStyle('B' . ($rowcount + 4) . ':G' . ($rowcount + 4))->applyFromArray($THStyle);
                            //6th row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 5, "Total Paid days:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 5)->getFont()->setBold(false);

                           $prodate = isset($value['empdet']['prodate_type'])? $value['empdet']['prodate_type']:'';
                            // if ($prodate == 'Calender Days') {
                                $paid_days = abs($calender_days - $loss_offp);
                                //debug($paid_days);exit();
                            // }else{
                            //     $paid_days =  isset($value['empdet']['0']['payroll_master']['days_presant']) ? abs($value['empdet']['0']['payroll_master']['days_presant']) : '';
                            // }
                          $worksheet->getStyleByColumnAndRow($columncount + 1, $rowcount + 5)->getNumberFormat()->setFormatCode('0.0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 5, $paid_days);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 5)->getFont()->setBold(false);
                             $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 5)->applyFromArray($leftstyle);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount +4, $rowcount + 5, "LOP:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount +4, $rowcount + 5)->getFont()->setBold(false);
                              $worksheet->getStyleByColumnAndRow($columncount + 5, $rowcount + 5)->getNumberFormat()->setFormatCode('0.0');
                             $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 5, $loss_offp);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 5)->getFont()->setBold(false);
                             $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 5)->applyFromArray($leftstyle);


                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 5)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+5).':E'.($rowcount+5));
                            //$worksheet->mergeCells('E'.($rowcount+5).':G'.($rowcount+5));
                           // $objPHPExcel->getActiveSheet()->getStyle('B' . ($rowcount + 5) . ':G' . ($rowcount + 5))->applyFromArray($THStyle);
                            
                            $rowcount = $rowcount + 4;

                            $columncount = 1;

                            // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount + 1), 'Salary Slip    ');
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, ($rowcount + 1))->getFont()->setBold(false);
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, ($rowcount + 1))->getFont()->setSize(10);
                            $rowcount = $rowcount + 2;
                            $payment_row = $rowcount;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Earnings (Rs.)');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Actual Amount');
                            $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Earned Amount');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Deductions (Rs.)');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Amount');
                            for ($i = 1; $i <= 6; $i++) {
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setSize(10);
                            }
                            $rowcount = $rowcount + 1;
                            $ded_row = $rowcount;
                            $arr_data = $value['summary'];
                            //debug($arr_data);exit();

                            if (count($arr_data) >= 0) {
                                $variables = isset($value['variables'])? $value['variables']:0;
                                 //debug($variables);exit;
                                if($variables > 0 ){
                                    $k = count($arr_data);
                                    $value['summary'][$k]['ectc']['salary_amount'] = $variables;
                                    $value['summary'][$k]['ectc']['structure_det_value'] = $variables;
                                    $value['summary'][$k]['ectc']['salary_head_item_desc'] = 'Variable Pay';
                                }
                                $sum = 0;
                                $tot = 0;
                                $dd = 0;
                                $net = 0;
                                foreach ($value['summary'] as $val) {
                                    $sum = $sum + $val['ectc']['structure_det_value'];
                                    $dd = $dd + $val['ectc']['salary_amount'];
                                    $salary = trim($val['ectc']['salary_head_item_desc']);
                                    $rate = round($val['ectc']['structure_det_value'],2);
                                    $amount = round($val['ectc']['salary_amount']);
                                    //edited by megha on 16/11/19 settlement amount 
                                    $settlement = $value['settle'];
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $salary);
                                    $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $rate);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $amount);

                                    $rowcount++;
                                }
                           
                                $deduction = 0;
                                // $arr_withoutComponents = $value['withoutcomponent'];
                                
                                // if (count($arr_withoutComponents) > 0) {
                                //     foreach ($arr_withoutComponents as $vals) {
                                //         $tot = $tot + $vals['ectc']['structure_det_value'];
                                //         $net = $net + $vals['ectc']['salary_amount'];
                                //         $salary = trim($vals['ectc']['salary_head_item_desc']);
                                //         $rate = round($vals['ectc']['structure_det_value'],2);
                                //         $amount = round($vals['ectc']['salary_amount'],2);
                                //         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($ded_row), $salary);
                                //         //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $rate);
                                //         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($ded_row), abs($amount));
                                //         $deduction += $amount;
                                //         $worksheet->mergeCells('C'.$ded_row.':D'.$ded_row);
                                //         $ded_row++;
                                //     }
                                //     $temp = $ded_row;

                                //     $rowcount = max($ded_row +1, $rowcount);
                                //     $rowcount--;
                                // }
                                //   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($temp), "LOP");

        $arr_withoutComponents = $value['withoutcomponent'];
        //debug($arr_withoutComponents);exit();
 
if (count($arr_withoutComponents) > 0) {
    // Initialize $temp before the loop

    foreach ($arr_withoutComponents as $vals) {
        //debug($vals);exit();
        $tot = $tot + $vals['ectc']['structure_det_value'];
        $net = $net + $vals['ectc']['salary_amount'];
        $salary = trim($vals['ectc']['salary_head_item_desc']);
        $rate = round($vals['ectc']['structure_det_value'], 2);
       $amount = abs($vals['ectc']['salary_amount']);
        //debug($amount);exit();

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($ded_row), $salary);
        //debug($salary);exit();
        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $rate);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($ded_row), abs($amount));

        $deduction += $amount;
        $worksheet->mergeCells('C'.$ded_row.':D'.$ded_row);
        $ded_row++;
    }
            //debug($amount);exit();
    $temp = $ded_row;
    $rowcount = max($ded_row + 1, $rowcount);
    $rowcount--;
}

                          $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($temp), "LOP");


                                $lopdeduction = $value['lopdeduction'];
                                 if(count($lopdeduction) > 0) {
                                    foreach ($lopdeduction as $vals) {
                                    $tot = $vals['0']['total_salary_amount'];
                                                                                       
                                    $perday = $tot / $calender_days;                                                   
                                    $lop_amount = round($perday * $loss_offp);
                                                                                       

                                                        }
                                                 } 
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($temp), $lop_amount);
                                //debug($lop_amount);exit();

                                $cell_vis = $rowcount;

                                $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                                $rowcount++;
                                $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "Gross Salary :");
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), formatIndianNumber(abs(round($sum))));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->applyFromArray($righttstyle);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), formatIndianNumber(abs(round($dd))));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->applyFromArray($righttstyle);
                                //debug($deduction);exit();
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), formatIndianNumber(round(abs($deduction + $lop_amount))));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->applyFromArray($righttstyle);


                                $status = isset($value['summary']['0']['ed']['status'])?$value['summary']['0']['ed']['status']:0;
                                if ($status == 2) {
                                    $rowcount++;
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), "Settlement Amount");
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), formatIndianNumber(round($settlement)));
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->applyFromArray($righttstyle);
                                }
                                $rowcount++;
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), "Net Salary:");
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                                $net_amount = round($dd + $net + $settlement);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), formatIndianNumber($net_amount));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->applyFromArray($righttstyle);
                                $rowcount++;
                                $rowcount++;
                                if($net_amount >=0){
                                    $amount_in_words = '';
                                }else{
                                    $amount_in_words =' Negative ';
                                }
                                $amount_in_words .=convertToWords($net_amount);
                                //Amount in words
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "Amount In Words:- Rupees ".$amount_in_words. " Only");
                                $worksheet->mergeCells('B'.$rowcount.':G'.$rowcount);
                                $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                 );
                                $rowcount++;
                                $rowcount++;
                                //Decalaration
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "This is a computer generated Salary Slip and does not require Signature");
                                $worksheet->mergeCells('B'.$rowcount.':G'.$rowcount);
                                $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                 );


                                $pos = $rowcount;
                                $objPHPExcel->getActiveSheet()->getStyle('B' . $start . ':G' . $pos)->applyFromArray($BStyle);
                                $objPHPExcel->getActiveSheet()->getStyle('B' . $start . ':G' . $cell_vis)->applyFromArray($styleArray);
                                $objPHPExcel->getActiveSheet()->getStyle('B' . ($payment_row) . ':G' . ($payment_row))->applyFromArray($THStyle);

                                $start = 0;
                                $pos = 0;
                            } else {
                                $msg = 'No data available under the selected criteria.';
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), (4), $msg);
                                $objPHPExcel->getActiveSheet()->getStyle('B4:G4')->applyFromArray($BStyle);
                                $worksheet->mergeCells('B4:G4');
                            }

                            $rowcount++;
                        } 
                        $rowcount++;
                    }
                    }else {
                        $msg = 'No data available under the selected criteria.';
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), (4), $msg);
                        $objPHPExcel->getActiveSheet()->getStyle('B4:G4')->applyFromArray($BStyle);
                        $worksheet->mergeCells('B4:G4');
                    }
                        }else{
              
                        if(count($arr_salary_for_template)> 0){
                         foreach ($arr_salary_for_template as $val) { 
                             $branch = isset($val['0']['summary']['0']['br']['branch_name']) ? $val['0']['summary']['0']['br']['branch_name']: '';
                            // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount , $rowcount,  $branch);
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , $rowcount)->getFont()->setBold(false);
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , ($rowcount))->getFont()->setSize(10);
                         
                      foreach ($val as $value) {
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount , $rowcount,  $company_name);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , ($rowcount))->getFont()->setSize(10);
                            $worksheet->mergeCells('B' . $rowcount . ':G' . $rowcount);
                            //Form XII
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount , $rowcount,  '[FORM XIII See rule 29(2)]');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount , ($rowcount))->getFont()->setSize(10);

                            $worksheet->mergeCells('B' . $rowcount . ':G' . $rowcount);
                            $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );

                            //Pay slip
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Pay Slip - ' . $mname .' '.$year);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount))->getFont()->setSize(10);
                            $worksheet->mergeCells('B' . $rowcount . ':G' . $rowcount);
                            $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );

                      if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                            $i += 1;
                            // $rowcount += 2;
                            $start = $rowcount - 2;
                            // $worksheet->mergeCells('A' . $rowcount . ':D' . $rowcount);
//                        $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
//                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                    );
                            $empstatus = isset($value['summary']['0']['ed']['status']) && $value['summary']['0']['ed']['status'] == "2" ? ' (Resigned)' : '';
                            $emp_name = isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] . " " . $value['summary']['0']['ed']['last_name'] : '';
                            $payment_type = isset($value['summary']['0']['ed']['payment_type']) ? $value['summary']['0']['ed']['payment_type'] : '';
                            $rowcount++;
                            
                            $branch = isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : '';
                            $calender_days = isset($value['empdet']['0']['payroll_master']['calander_days']) ? $value['empdet']['0']['payroll_master']['calander_days'] : '';
                             $loss_offp = isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : '';

                            $daysleave = isset($value['empdet']['0']['payroll_master']['days_leave']) ? $value['empdet']['0']['payroll_master']['days_leave'] : '';
                            $designat = isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : '';
                            $wrktime = isset($value['empdet']['0']['payroll_master']['working_days']) ? $value['empdet']['0']['payroll_master']['working_days'] : '';
                           
                            $department = isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '';
                            $days_present = isset($value['summary']['0']['ar']['presant_total']) ? $value['summary']['0']['ar']['presant_total'] : '';
                            $weekoff = isset($value['summary']['0']['ar']['weekoff_total']) ? $value['summary']['0']['ar']['weekoff_total'] : '';
                            $holiday = isset($value['summary']['0']['ar']['holiday_total']) ? $value['summary']['0']['ar']['holiday_total'] : '';
                            $employee_id = isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : '';
                            $gender = isset($value['summary']['0']['ed']['classification'])?strtoupper($value['summary']['0']['ed']['classification']):'';
                            //edited by megha on 9_7_19 date format changed
                            //$join = isset($value['empdet']['0']['ep']['joining_date']) ? date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';
                           $join = isset($value['empdet']['0']['ep']['joining_date']) ? date('j-M-y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';


                            //$join = isset($value['empdet']['0']['ep']['joining_date']) ? $value['empdet']['0']['ep']['joining_date'] : '';
                            $termin = isset($value['empdet']['0']['tr']['last_approved_working_date']) ? $value['empdet']['0']['tr']['last_approved_working_date'] : '';
                            $userid = isset($value['empdet']['0']['user_credentials']['user_id']) ? $value['empdet']['0']['user_credentials']['user_id'] : '';
                            
                            $pf = isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : '';
                            $esi = isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : '';
                            $uan =  isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : '';
                            $bank_name = '';
                                $branch_name = '';
                                $ifsc_code = '';
                                $acc_number = '';
                                $bank = isset($value['empdet']['0']['payroll_master']['bank_details'])?$value['empdet']['0']['payroll_master']['bank_details']:'';
                                if($bank !=''){
                                list($bank_name, $branch_name, $ifsc_code,$acc_number) = explode(',', $bank);
                                }
                                if($bank_name == ''){
                                $bank_name = isset($value['summary']['0']['ed']['bank_name'])?$value['summary']['0']['ed']['bank_name']:'';
                                }
                                if($branch_name == ''){
                                $branch_name = isset($value['summary']['0']['ed']['branch_name'])?$value['summary']['0']['ed']['branch_name']:'';
                                }
                                if($ifsc_code == ''){
                                $ifsc_code = isset($value['summary']['0']['ed']['ifsc_code'])?$value['summary']['0']['ed']['ifsc_code']:'';
                                }
                                if($acc_number == ''){
                                $acc_number = isset($value['summary']['0']['ed']['account_no'])?$value['summary']['0']['ed']['account_no']:'';
                                } 
                            //1st row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Emp Code : ");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(false);
                            
                            $worksheet->mergeCells('C'.$rowcount.':E'.$rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, $employee_id);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->applyFromArray($leftstyle);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount, "DOJ : ");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount, $join);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->applyFromArray($leftstyle);
                            //2nd row 
                            // $worksheet->mergeCells('C'.$rowcount.':E'.$rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 1, 'Name :');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 1)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 1, $emp_name.$empstatus);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 1)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 1)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+1).':E'.($rowcount+1));
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount + 1, "UAN No:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount + 1)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 1, ($uan));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 1)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 1)->applyFromArray($leftstyle);

                            //3rd row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 2, 'Department:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 2)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 2, $department);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 2)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 2)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+2).':E'.($rowcount+2));

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount + 2, 'IP No:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount + 2)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 2, $esi);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 2)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 2)->applyFromArray($leftstyle);
                            $objPHPExcel->getActiveSheet()->getStyle('G'.($rowcount+2))->getNumberFormat()->setFormatCode('0');
                            //4th row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 3, 'Designation:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 3)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 3, $designat);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 3)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 3)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+3).':E'.($rowcount+3));

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount + 3, 'Bank:');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount + 3)->getFont()->setBold(false);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 3, ($bank_name));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 3)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 3)->applyFromArray($leftstyle);
                            $objPHPExcel->getActiveSheet()->getStyle('G'.($rowcount+3))->getNumberFormat()->setFormatCode('0');
                            //5th row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 4, "Pay Mode:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 4)->getFont()->setBold(false);
                            
                            
                            $pay_mode = '';
                            if($payment_type == 'bank'){
                                $pay_mode = 'Bank transfer';
                            }else if($payment_type == 'neft'){
                                $pay_mode = 'NEFT';
                            }else{
                                $pay_mode = ucfirst($payment_type);
                            }
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 4, $pay_mode);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 4)->getFont()->setBold(false);
                             $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount +4, $rowcount + 4, "Ac No:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount +4, $rowcount + 4)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 4, $acc_number);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 4)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 4)->applyFromArray($leftstyle);
                             $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 4)->applyFromArray($leftstyle);


                            $worksheet->mergeCells('C'.($rowcount+4).':E'.($rowcount+4));
                            //$worksheet->mergeCells('E'.($rowcount+4).':G'.($rowcount+4));
                            
                            $objPHPExcel->getActiveSheet()->getStyle('B' . ($rowcount + 4) . ':G' . ($rowcount + 4))->applyFromArray($THStyle);
                              $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 5, "Total Paid days:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 5)->getFont()->setBold(false);

                            $prodate = isset($value['empdet']['prodate_type'])? $value['empdet']['prodate_type']:'';
                            if ($prodate == 'Calender Days') {
                                $paid_days = abs($calender_days - $loss_offp);
                            }else{
                                $paid_days =  isset($value['empdet']['0']['payroll_master']['days_presant']) ? abs($value['empdet']['0']['payroll_master']['days_presant']) : '';
                            }
                              $worksheet->getStyleByColumnAndRow($columncount + 1, $rowcount + 5)->getNumberFormat()->setFormatCode('0.0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 5, $paid_days);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 5)->getFont()->setBold(false);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount +4, $rowcount + 5, "LOP:");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount +4, $rowcount + 5)->getFont()->setBold(false);
                              $worksheet->getStyleByColumnAndRow($columncount + 5, $rowcount + 5)->getNumberFormat()->setFormatCode('0.0');
                             $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 5, $rowcount + 5, $loss_offp);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 5)->getFont()->setBold(false);
                             $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount + 5)->applyFromArray($leftstyle);

                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 5)->applyFromArray($leftstyle);
                            $worksheet->mergeCells('C'.($rowcount+5).':E'.($rowcount+5));
                            $objPHPExcel->getActiveSheet()->getStyle('B' . ($rowcount + 5) . ':G' . ($rowcount + 5))->applyFromArray($THStyle);
                            
                            $rowcount = $rowcount + 4;

                            $columncount = 1;

                            // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount + 1), 'Salary Slip    ');
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, ($rowcount + 1))->getFont()->setBold(false);
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, ($rowcount + 1))->getFont()->setSize(10);
                            $rowcount = $rowcount + 2;
                            $payment_row = $rowcount;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Earnings (Rs.)');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Actual Amount');
                            $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Earned Amount');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Deductions (Rs.)');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Amount');
                            for ($i = 1; $i <= 6; $i++) {
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setSize(10);
                            }
                            $rowcount = $rowcount + 1;
                            $ded_row = $rowcount;
                            $arr_data = $value['summary'];
                            if (count($arr_data) >= 0) {
                                $variables = isset($value['variables'])? $value['variables']:0;
                                // debug($value['summary']);exit;
                                if($variables > 0 ){
                                    $k = count($arr_data);
                                    $value['summary'][$k]['ectc']['salary_amount'] = $variables;
                                    $value['summary'][$k]['ectc']['structure_det_value'] = $variables;
                                    $value['summary'][$k]['ectc']['salary_head_item_desc'] = 'Variable Pay';
                                }
                                $sum = 0;
                                $tot = 0;
                                $dd = 0;
                                $net = 0;
                                foreach ($value['summary'] as $val) {
                                    $sum = $sum + $val['ectc']['structure_det_value'];
                                    $dd = $dd + $val['ectc']['salary_amount'];
                                    $salary = trim($val['ectc']['salary_head_item_desc']);
                                    $rate = round($val['ectc']['structure_det_value'],2);
                                    $amount = round($val['ectc']['salary_amount']);
                                    //edited by megha on 16/11/19 settlement amount 
                                    $settlement = $value['settle'];
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $salary);
                                    $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $rate);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $amount);

                                    $rowcount++;
                                }
                           
                                $deduction = 0;
                                $arr_withoutComponents = $value['withoutcomponent'];
                                $temp1 = $ded_row;
                                if (count($arr_withoutComponents) > 0) {
                                    foreach ($arr_withoutComponents as $vals) {
                                        $tot = $tot + $vals['ectc']['structure_det_value'];
                                        $net = $net + $vals['ectc']['salary_amount'];
                                        $salary = trim($vals['ectc']['salary_head_item_desc']);
                                        $rate = round($vals['ectc']['structure_det_value'],2);
                                        $amount = round($vals['ectc']['salary_amount'],2);
                                        //debug($amount);exit();
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($ded_row), $salary);
                                        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $rate);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($ded_row), abs($amount));
                                        $deduction += $amount;
                                        $worksheet->mergeCells('C'.$ded_row.':D'.$ded_row);
                                        $ded_row++;
                                    }
                                     $temp1 = $ded_row;
                                    $rowcount = max($ded_row + 1, $rowcount);
                                    $rowcount--;
                                }
                               if (isset($temp1)) {
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($temp1), "LOP");
    $lopdeduction = $value['lopdeduction'];

    $lop_amount = 0; // Initialize $lop_amount outside of the loop

    if (count($lopdeduction) > 0) {
        foreach ($lopdeduction as $vals) {
            $tot = $vals['0']['total_salary_amount'];
            $perday = $tot / $calender_days;
            $lop_amount += round($perday * $loss_offp); // Accumulate the value instead of overwriting
        }

        // Move this line outside of the loop
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($temp1), $lop_amount);
        //debug($lop_amount);exit();
    }
}

                                $cell_vis = $rowcount;
                                $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                                $rowcount++;
                                $worksheet->mergeCells('C'.$rowcount.':D'.$rowcount);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "Gross Salary :");
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), formatIndianNumber(abs(round($sum))));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->applyFromArray($righttstyle);
                                //debug($dd);exit();
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), formatIndianNumber(abs(round($dd))));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->applyFromArray($righttstyle);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), formatIndianNumber(round(abs($deduction))));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->applyFromArray($righttstyle);

                            

                                $status = isset($value['summary']['0']['ed']['status'])?$value['summary']['0']['ed']['status']:0;
                                if ($status == 2) {
                                    $rowcount++;
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), "Settlement Amount");
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), formatIndianNumber(round($settlement)));
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->applyFromArray($righttstyle);
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                }
                                $rowcount++;
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), "Net Salary:");
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                                $net_amount = round($dd + $net + $settlement);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), formatIndianNumber($net_amount));
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->applyFromArray($righttstyle);
                                $rowcount++;
                                $rowcount++;
                                if($net_amount >=0){
                                    $amount_in_words = '';
                                }else{
                                    $amount_in_words =' Negative ';
                                }
                                $amount_in_words .= convertToWords($net_amount);
                                //Amount in words
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "Amount In Words:- Rupees ".$amount_in_words." Only");
                                $worksheet->mergeCells('B'.$rowcount.':G'.$rowcount);
                                $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                 );
                                $rowcount++;
                                $rowcount++;
                                //Decalaration
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "This is a computer generated Salary Slip and does not require Signature");
                                $worksheet->mergeCells('B'.$rowcount.':G'.$rowcount);
                                $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                 );


                                $pos = $rowcount;
                                $objPHPExcel->getActiveSheet()->getStyle('B' . $start . ':G' . $pos)->applyFromArray($BStyle);
                                $objPHPExcel->getActiveSheet()->getStyle('B' . $start . ':G' . $cell_vis)->applyFromArray($styleArray);
                                $objPHPExcel->getActiveSheet()->getStyle('B' . ($payment_row) . ':G' . ($payment_row))->applyFromArray($THStyle);

                                $start = 0;
                                $pos = 0;
                            } else {
                                $msg = 'No data available under the selected criteria.';
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), (4), $msg);
                                $objPHPExcel->getActiveSheet()->getStyle('B4:G4')->applyFromArray($BStyle);
                                $worksheet->mergeCells('B4:G4');
                            }

                            $rowcount++;
                        }
                        $rowcount++;
                    }
                      }
                    }else {
                        $msg = 'No data available under the selected criteria.';
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), (4), $msg);
                        $objPHPExcel->getActiveSheet()->getStyle('B4:G4')->applyFromArray($BStyle);
                        $worksheet->mergeCells('B4:G4');
                       
                    }
                    }
                    // exit;
                    //Hide grid lines
                    $objPHPExcel->getActiveSheet()->setShowGridlines(false); 
                    $objPHPExcel->getActiveSheet()->setSelectedCells('B4');

                    $objPHPExcel->getActiveSheet()->setTitle('Salary Slip');

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
                    $this->render('salaryslip_vayalatnew');
                    break;
            }
        }
    }
    //Edited by Akshay on 11-12-2023
    public function sendNewSliptoMail() {
        
        ini_set('memory_limit', '2G');
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $this->EmployeeTaxsalsumNew->useDbConfig = $this->Session->read('ds');
        $this->EmployeeTaxsalsum->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE;

        $arr_employees = $_REQUEST['emps'];


            $arr_form_data['hidden-report-type'] =  'DepositslipSynthite';
            $arr_form_data['hidden-criterias-count'] = '1';
            $arr_form_data['hidden-reportfields'] = '';
            $arr_form_data['reportfrom'] =$_REQUEST['month'];
            $arr_form_data['hidden-criteria1'] = 'EmployeeDetails';
            $arr_form_data['select-criteria1'] =  'EmployeeDetails';
            
            $arr_form_data['resigned'] = '0';
            $arr_form_data['ngtvsal'] = '1';
            $arr_form_data['selectall'] = '0';
            $condition_approve = " and payroll_master.action in ('Approved') ";

            $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $variable_key = array();
            //Variable
            $variable_keys = $this->EmpCtcTransaction->query("select trim(salary_head_item_desc) as sal_head,ectc.head_operator FROM emp_salary_slip as ectc
            left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
            left join salary_heads on (salary_heads.head_pkey = salhead.head_fkey) where ectc.item_part='Direct' and salary_heads.head_pkey in (2,7,9)
            and end_date_effective is null  and month_year = '$from' Group by salary_head_item_desc ORDER BY salhead.salary_head_item_order1 asc");
    
            foreach ($variable_keys as $val) {
                if ($val['ectc']['head_operator'] == 'Addition') {
                    $variable_key['VAddition'][] = $val[0]['sal_head'];
                } 
            }

            function convertToWords($number)
                {
                    $number = abs($number);
                    $words = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine');
                    $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');
                    $teens = array('Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');

                    $amount_in_words = '';

                    if ($number < 10) {
                        $amount_in_words .= $words[$number];
                    } elseif ($number < 20) {
                        $amount_in_words .= $teens[$number - 10];
                    } elseif ($number < 100) {
                        $amount_in_words .= $tens[floor($number / 10)];
                        if ($number % 10 > 0) {
                            $amount_in_words .= ' ' . $words[$number % 10];
                        }
                    } elseif ($number < 1000) {
                        $amount_in_words .= $words[floor($number / 100)] . ' Hundred';
                        if ($number % 100 > 0) {
                            $amount_in_words .= ' and ' . convertToWords($number % 100);
                        }
                    } elseif ($number < 100000) {
                        $amount_in_words .= convertToWords(floor($number / 1000)) . ' Thousand';
                        if ($number % 1000 > 0) {
                            $amount_in_words .= ' ' . convertToWords($number % 1000);
                        }
                    } elseif ($number < 10000000) {
                        $amount_in_words .= convertToWords(floor($number / 100000)) . ' Lakh';
                        if ($number % 100000 > 0) {
                            $amount_in_words .= ' ' . convertToWords($number % 100000);
                        }
                    } else {
                        $amount_in_words .= convertToWords(floor($number / 10000000)) . ' Crore';
                        if ($number % 10000000 > 0) {
                            $amount_in_words .= ' ' . convertToWords($number % 10000000);
                        }
                    }

                    return $amount_in_words;
                }
            function formatIndianNumber($amount)
                {
                    setlocale(LC_MONETARY, 'en_IN');
                    $amount = money_format('%!i', $amount);
                    return rtrim(rtrim($amount, '0'), '.');
                }

        foreach($arr_employees as $employee){
            $arr_form_data['EmployeeDetails'][0] = $employee;
        

        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';

            if ($str_criteria_item == '') {

                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }

        // debug( $arr_leavepolicygroupids);
        $this->set($variable_key,'variable_key');
        $condition = "and ed.status = '1'  ";

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and ed.status in ('1','2') ";
        }
        if(isset($arr_form_data['ngtvsal']) && $arr_form_data['ngtvsal'] == '0') {
            $condition .= " and payroll_master.net_salary >= 0 ";
        }
        $arr_salary_for_template = array();
        $arr_empleaverequests = array();

        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
            if($str_criteria_item == 'EmployeeDetails'){
                $pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
                 $arr_empleaverequests = $this->EmpCtcTransaction->query("select distinct ectc.month_year,br.branch_name,ed.email,ed.first_name,"
                . "ed.middile_name,ed.last_name,ed.status,ed.payment_type,ectc.payroll_master_fkey,ectc.head_operator,ectc.head_type,ectc.item_part,ectc.salary_head_item_desc,
                ectc.structure_det_value,ectc.salary_amount,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.pf,ed.classification,"
                        . "ed.ifsc_code,ed.esi,ep.joining_date,ed.status,ar.weekoff_total,ar.presant_total,ar.holiday_total,payroll_master.days_leave "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and round(ectc.salary_amount) != '0' 
                           and ectc.emp_fkey = '$leavepolicygroupid' and desg.status = 1 "
                        . "and ectc.month_year ='$from' "
                        . $condition
                        . "and end_date_effective is null $condition_approve and ar.isdelete= 'N' and ar.month_year= '$from' order by salhead.salary_head_item_order1");
                        $arr_empleaverequests1 = $this->EmpCtcTransaction->query("select distinct ectc.month_year,br.branch_name,ed.email,ed.first_name,"
                        . "ed.middile_name,ed.last_name,ed.status,ectc.payroll_master_fkey,ectc.head_operator,ectc.head_type,ectc.item_part,ectc.salary_head_item_desc,
                        ectc.structure_det_value,ectc.salary_amount,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.pf,ed.classification,"
                        . "ed.ifsc_code,ed.esi,ep.joining_date,ed.status,ar.weekoff_total,ar.presant_total,ar.holiday_total,payroll_master.days_leave "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join site_attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and round(ectc.salary_amount) != '0' 
                           and ectc.emp_fkey = '$leavepolicygroupid' and desg.status = 1 "
                        . "and ectc.month_year ='$from' "
                        . $condition
                        . "and end_date_effective is null $condition_approve and ar.month_year= '$from' order by salhead.salary_head_item_order1");
                        $arr_empleaverequests = array_merge($arr_empleaverequests, $arr_empleaverequests1);
                //edited by megha on 9_7_19 salary head component ordering
                $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.middile_name,ed.last_name,ectc.salary_head_item_desc,
                    ectc.structure_det_value,ectc.salary_amount "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                            left join branches as br on (br.branch_code = ep.emp_branch)
                             left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                                          where ectc.head_operator = 'Deduction'  and round(ectc.salary_amount) != '0' 
                                          and ectc.emp_fkey = '$leavepolicygroupid' "
                        . " and ectc.month_year ='$from' "
                        . "$condition and "
                        . "end_date_effective is null $condition_approve order by salhead.salary_head_item_order1");

                $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
                $empdetails = $this->EmpCtcTransaction->query("select ep.designation,ed.payment_type,tr.last_approved_working_date,user_credentials.user_id,ep.emp_dept,ep.emp_company_id,ep.joining_date,d.dept_name,dd.desig_name,payroll_master.calander_days,payroll_master.days_leave,payroll_master.working_days,payroll_master.loss_of_pay,payroll_master.days_presant "
                        . "from payroll_master "
                        . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                        . "left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey) "
                        . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey) "
                        . "left join department as d on(d.dept_code = ep.emp_dept) "
                        . "left join designation as dd on (dd.desig_code = ep.designation)"
                        . "left join emp_details as ed on (ed.emp_pkey = payroll_master.emp_fkey)"
                        . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
                        . " and payroll_master.month_year ='$from' and dd.status = 1 "
                        . " and payroll_master.emp_fkey = '$leavepolicygroupid' $condition_approve ");
            
            $pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
            $empdetails['prodate_type'] = isset($pro_date_desc['type'])? $pro_date_desc['type']:'';

            $arr_settle = $this->EmpCtcTransaction->query(" select sum(salary_amount) from emp_settle_slip as ectc  
            left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
            left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
            left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
            where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.emp_pkey='$leavepolicygroupid' and date_format(termination.last_approved_working_date,'%Y-%m') = '$from'  group by emp_details.emp_pkey");
                $settle = isset($arr_settle['0']['0']['sum(salary_amount)']) ? $arr_settle['0']['0']['sum(salary_amount)'] : 0;
           if(!empty($arr_empleaverequests)){
                $arr_empleaverequests_refined = array();
                $incentives = 0;
                
                foreach($arr_empleaverequests as $value){
                    if(in_array($value['ectc']['salary_head_item_desc'], $variable_key['VAddition'])){
                        $incentives += isset($value['ectc']['salary_amount']) ? abs(round($value['ectc']['salary_amount'])) : 0;
                    }else{
                        $arr_empleaverequests_refined[] = $value;
                    }
                }
                $arr_empleaverequests = $arr_empleaverequests_refined;

                    $arr_salary_for_template[] = array(
                        'summary' => $arr_empleaverequests,
                        'withoutcomponent' => $salaryslipwithoutcomponents,
                        'empdet' => $empdetails,
                        'settle' => $settle,
                        'variables'=> $incentives
                    );


           }
            }
            }

            $name = isset($arr_salary_for_template['0']['summary']['0']['ed']['first_name']) ? $arr_salary_for_template['0']['summary']['0']['ed']['first_name'] : '';
            $email = isset($arr_salary_for_template['0']['summary']['0']['ed']['email']) ? $arr_salary_for_template['0']['summary']['0']['ed']['email'] : '';

// debug($arr_salary_for_template);exit;
            $this->set('arr_salary_for_template', $arr_salary_for_template);

            //Function to convert amount to words


            $employee_attendance = array();
            if(!empty($arr_empleaverequests)){
            foreach ($arr_empleaverequests as $val) {
                $emp = $val['ectc']['head_operator'];
                $emppk = $val['ectc']['head_type'];
                $itempart = $val['ectc']['item_part'];
                $employee_attendance[$emppk][$emp][$itempart][] = $val;
            }}
            $this->set('employee_attendance', $employee_attendance);
            $cr = $arr_form_data['select-criteria1'];
            $this->set('cr', $cr);

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
             $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $time=strtotime($f);
            $month=date("m",$time);

            $mname = date('F', mktime(0, 0, 0, $month, 10));
            $month1 =  $month.'-01'; 
            $year=date("Y",$time);
           
            $this->set('mname1', $mname);
            $this->set('y1', $year);
            $user_id = $this->Session->read('login_user_id');
            $date_time = date('d-m-Y H:i');

            $this->set('user_id',$user_id);
            $this->set('date_time', $date_time); 
            $this->set('month', $month);

           try{
                ob_start();
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('salaryslip_vayalat');
                // $view_output = '<h1>hello</h1>';
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                $html2pdf->setTestTdInOnePage(false);
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                // $pdf = $html2pdf->Output('Salaryslipnew.pdf', 'D');
                $pdf = $html2pdf->Output('', true);
                // $this->render('salaryslip_vayalat');

                App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
                $mail = new PHPMailer;
                $mail->SMTPDebug = false;                               // Enable verbose debug output
                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';                 // SMTP username
                $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
                $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
                $mail->Port = 587; //25;                                    // TCP port to connect to   

                $mail->setFrom('mypayrollmaster@office24.online');
                $mail->addAddress($email);     // Add a recipient
                // $mail->addBCC('projects@greatleap.tech');     // Add a recipient
                // $mail->addBCC('crm@greatleap.tech');  
                //$mail->addBCC($email); 
                $mail->addReplyTo('mypayrollmaster@office24.online');
                $mail->isHTML(true);                                  // Set email format to HTML
                // $mail->AddEmbeddedImage('https://login.mypayrollmaster.online/newlogin/img/logo.png', 'MPM');
                $mail->AltBody    = '<!DOCTYPE html>';
                $pdfname = 'Salary_Slip_'.$name.'.pdf';
                $mail->addStringAttachment($pdf, $pdfname);
                $mail->Subject = 'Salary_Slip of '.$name;
                $mail->MsgHTML('<h4>Hi '.$name.',</h4><div>Your Salary Slip for '.$mname.' '.$year.' is now available. Please find the attached salary slip as PDF document.<br>
                        <br><h4>*** This is a system generated mail and should not be replied to ***</h4></div>');
                ob_clean(); 
                if (!$mail->send()) {
                    echo 'Message could not be sent.';
                    echo 'Mailer Error: ' . $mail->ErrorInfo;
                } else {
                    echo 'Message has been sent';
                }
            } catch (HTML2PDF_exception $e) {
                    echo 'Error: ' . $e->getMessage();
            }
        }

        }

    }


}
