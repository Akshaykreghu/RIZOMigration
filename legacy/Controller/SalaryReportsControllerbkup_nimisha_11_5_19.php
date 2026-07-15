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

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class salaryReportsController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'SalaryReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('LeavePolicyGroup', 'CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments', 'EmployeeGrossDetails', 'Verticals', 'Units', 'ReportCriterias', 'DayTimeProcedures', 'EmpCtcTransaction', 'LeaveRequests', 'Designation');
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
        $arr_reporttypes = array(
//            'employee' => 'Employee Information',
            // 'LeaveDetaillsReport' =>  'Leave Details Reports',
            'SummaryPayroll' => 'Payroll Summary Report',
            'salary' => 'CTC Summary Report',
            'BankTranfer' => 'Salary Bank Transfer Report',
            'salarystructure' => 'CTC Detail Reports',
            'Salaryslip' => 'Salary Slip ',

            'Grosssalary' => 'Gross Salary Detail Reports',
            //sruthi 
            'GrosssalarySummary' => 'Gross Salary Summary Reports',
//            'DetailedAttendance' =>  'Detailed Attendance Reports',
//             'AttendanceRep' =>  'Attendance Reports',

                /* 'leave' => 'Leaves Report',
                  'attendance' => 'Attendance Summary', */
        );
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
            switch ($type) {
                case 'employee':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'LeaveSummary':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'salarystructure':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //ARUN Gross
                case 'Grosssalary' :
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'GrosssalarySummary':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'SummaryPayroll':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'BankTranfer':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Salaryslip':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //santhu
                case 'salary':
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
                if($str_criteria == 'LeavePolicyGroup'){
                $model = 'Banks';
            }
                $this->set('index', $index);
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

    public function listcriteriaitems($str_criteria = '') {
        $this->autoRender = false;
        $model = $str_criteria;
       $arr_requestdata = $this->request->data;
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');

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
            if ($model == 'LeavePolicyGroup') {
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $conditions[] = "status = '1' or status ='2' ";
                $model = 'Banks';
                $arr_criteriaItemsDB = Set::extract('/EmployeeDetails/.',$this->EmployeeDetails->find("all",array("fields"=>array("DISTINCT bank_name"),"conditions"=>$conditions)));
//                debug($arr_criteriaItemsDB);
            } else {

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
                        $arr_criteriaItems[$key]['key'] = isset($value['bank_name'])?$value['bank_name']: null ;
                        $arr_criteriaItems[$key]['text'] = ($value['bank_name'] != '')?$value['bank_name']:' N/A  ';
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
                    //debug($arr_emp);
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value['Designation']['desig_name'];
                        $arr_criteriaItems[$key]['key'] = $value['Designation']['id'];
                        $key++;
                    }
                    // debug($arr_emp);
                    break;
                case 'EmployeeDetails':
                    $fields = 'emp_pkey,EmployeeDetails.status,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",emp_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
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
                    if(isset($arr_requestdata['name']) && $arr_requestdata['name'] =='1')
                    {
                         $conditions = array("status in(1,2)");
                    }
                    else{
                        
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
            echo json_encode($arr_criteriaItems);
        }
    }

    public function generatereport($type = '', $mode = '') {
        $this->autoRender = false;

        switch ($type) {
            case 'salary':
                //ctc summary report
                $this->generatesalaryreport($mode);
                break;
            case 'salarystructure':
                //ctc detail report
                $this->generateEmpSalaryreport($mode);
                break;
            //ARUN Gross
            case 'Grosssalary':
                //gross salary detail report
                $this->GenerateSalaryGross($mode);
                break;
            case 'GrosssalarySummary':
                //gross salary summary reports
                $this->GenerateSalaryGrossSummary($mode);
                break;
            case 'SummaryPayroll':
                //payroll summary report
                $this->GenerateSummaryPayrolreport($mode);
                break;
            case 'Salaryslip':
                //salary slip
                $this->GenerateSalarySlipreport($mode);
                break;
            case 'TimeAttendance':
                $this->generatetimeattendancereport($type, $mode);
                break;
            case 'BankTranfer':
                //salary bank transfer report
                $this->generateBanktransferreport($type, $mode);
                break;
            default:
                return false;
                break;
        }
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

    //salary slip
    private function GenerateSalarySlipreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        
            if($str_criteria_item == ''){
                
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
              echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }

        
        $condition = "and ed.status = '1'  ";
//        debug($arr_form_data);
        if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1')
        {
            $condition = "and ed.status in ('1','2') ";
        }
        $arr_leavepolicydetails_for_template = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
//  debug($arr_form_data['select-criteria1']);
                //  debug($leavepolicygroupid);
//              if($arr_form_data['select-criteria1'] == 'Units') 
//                   {       
//                                   $arr_empleaverequests = $this->EmpCtcTransaction->query("select ep.emp_branch,br.branch_name,ed.first_name,ed.last_name,ectc.* from emp_salary_structure as ectc "
//                                      . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)"
//                                           . "left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)"
//                                           . "left join branches as br on (br.branch_code = ep.emp_branch) where ectc.head_operator = 'ADDITION' and ectc.head_type = 'FIXED' and br.branch_code = '$leavepolicygroupid' and end_date_effective is null");  
// 
//                  
//                   }
//                   
//                   
//                   
//                     else  if($arr_form_data['select-criteria1'] == 'EmployeeDetails')  
//                   {
                $arr_empleaverequests = $this->EmpCtcTransaction->query("select ectc.month_year,br.branch_name,ed.first_name,ed.middile_name,ed.last_name,ed.status,ectc.*,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.ifsc_code,ed.esi,ep.joining_date,ed.status "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                       left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                   
                       left join branches as br on (br.branch_code = ep.emp_branch)
                       left join designation as desg on (desg.desig_code = ep.designation)
                       
                                          where ectc.head_operator = 'ADDITION' 
                                          and ectc.item_part = 'DIRECT' 
                                          and ectc.emp_fkey = '$leavepolicygroupid'"
                        . " and month_year ='$from' "
                        . $condition
                        . "and end_date_effective is null ");
//debug($arr_empleaverequests);
//                   }
//                debug("select ectc.month_year,br.branch_name,ed.first_name,ed.last_name,ectc.*,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.ifsc_code,ed.esi,ep.joining_date "
//                        . "from emp_salary_slip as ectc "
//                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
//                       left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
//                   
//                       left join branches as br on (br.branch_code = ep.emp_branch)
//                       left join designation as desg on (desg.desig_code = ep.designation)
//                       
//                                          where ectc.head_operator = 'ADDITION' 
//                                          and ectc.item_part = 'DIRECT' 
//                                          and ectc.emp_fkey = '$leavepolicygroupid'"
//                        . " and month_year ='$from' "
//                        . $condition
//                        . "and end_date_effective is null ");
                $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.middile_name,ed.last_name,ectc.* "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            
                                          where ectc.head_operator = 'Deduction' 
                                          and ectc.emp_fkey = '$leavepolicygroupid' "
                        . " and month_year ='$from' "
                        . "$condition and "
                        . "end_date_effective is null ");
                $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
                $empdetails = $this->EmpCtcTransaction->query("select ep.designation,tr.last_approved_working_date,user_credentials.user_id,ep.emp_dept,ep.emp_company_id,ep.joining_date,d.dept_name,dd.desig_name,payroll_master.* "
                        . "from payroll_master "
                        . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                        . "left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey) "
                        . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey) "
                        . "left join department as d on(d.dept_code = ep.emp_dept) "
                        . "left join designation as dd on (dd.desig_code = ep.designation)"
                        . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
                        . " and payroll_master.month_year ='$from'"
                        . " and payroll_master.emp_fkey = '$leavepolicygroupid' ");


//                debug($empdetails);



                $arr_salary_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_empleaverequests,
                    'withoutcomponent' => $salaryslipwithoutcomponents,
                    'empdet' => $empdetails
                );
            }


            $this->set('arr_salary_for_template', $arr_salary_for_template);

            $employee_attendance = array();
            foreach ($arr_empleaverequests as $val) {
                $emp = $val['ectc']['head_operator'];
                $emppk = $val['ectc']['head_type'];
                $itempart = $val['ectc']['item_part'];
                $employee_attendance[$emppk][$emp][$itempart][] = $val;
            }
            $this->set('employee_attendance', $employee_attendance);
            $this->set('arr_salary_for_template', $arr_salary_for_template);
            $cr = $arr_form_data['select-criteria1'];
            $this->set('cr', $cr);

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);

            switch ($mode) {
                case 'pdf' :

                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('salaryslip');
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('salaryslip'.$from.'.pdf', 'D');
                    $this->render('salaryslip');

                    break;
                case 'excel' :
                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_Salaryslip".$from.".xlsx" : "SalarySlip" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);
                    $BStyle = array(
                      'borders' => array(
                        'outline' => array(
                          'style' => PHPExcel_Style_Border::BORDER_THICK
                        )
                      )
                    );
                    
                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->getColumnDimension('A')->setWidth(40);
                    $worksheet->getColumnDimension('B')->setWidth(40);
                    $worksheet->getColumnDimension('C')->setWidth(40);
                    $worksheet->getColumnDimension('D')->setWidth(40);
                    
                    $worksheet->setCellValueByColumnAndRow(0, 1, "Salary Slip Report - ".$from."");
                    $worksheet->mergeCells('A1:D1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    
                    $rowcount = 2;
                    $col = 0;
                    
 
                $j = 0;
                foreach ($arr_salary_for_template as $value) {
                if (count($value['summary'])!= 0 ) {
                $j += 1;
             
                    $worksheet->setCellValueByColumnAndRow(0, 2, $str_company_code);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setSize(14);
                    $worksheet->mergeCells('A2:D2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    
                    
                    $worksheet->setCellValueByColumnAndRow(0, 3, "Month - ".$from);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount+1))->getFont()->setSize(14);
                    $worksheet->mergeCells('A3:D3');
                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 1)->getFont()->setBold(true);
                    
                    $columncount = 0;
                    $rowcount = 5;
                    foreach ($arr_salary_for_template as $value) {
                        if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                        $i += 1;
                        $rowcount++;
                        $start = $rowcount;
                        $worksheet->mergeCells('A'.$rowcount.':D'.$rowcount);
//                        $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
//                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                    );
                        $empstatus = isset($value['summary']['0']['ed']['status']) && $value['summary']['0']['ed']['status'] =="2" ? '  (Resigned)':'';
                        $emp_name = isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name']." ".$value['summary']['0']['ed']['last_name'] : '';
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Salary Slip - : '.$emp_name.$empstatus);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount))->getFont()->setSize(14);
                        $rowcount+=2;
                        $branch = isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : '';
                        $calender_days = isset($value['empdet']['0']['payroll_master']['calander_days']) ? $value['empdet']['0']['payroll_master']['calander_days'] : '';

                        $daysleave = isset($value['empdet']['0']['payroll_master']['days_leave']) ? $value['empdet']['0']['payroll_master']['days_leave'] : '';
                        $designat = isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : '';
                        $wrktime = isset($value['empdet']['0']['payroll_master']['working_days']) ? $value['empdet']['0']['payroll_master']['working_days'] : '';
                        $loss_offp = isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : '';
                        $department = isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '';
                        $days_present = isset($value['empdet']['0']['payroll_master']['days_presant']) ? $value['empdet']['0']['payroll_master']['days_presant'] : '';
                        $employee_id = isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : '';
                        $join = isset($value['empdet']['0']['ep']['joining_date']) ? $value['empdet']['0']['ep']['joining_date'] : '';
                        $termin= isset($value['empdet']['0']['tr']['last_approved_working_date']) ? $value['empdet']['0']['tr']['last_approved_working_date'] : '';
                        $userid = isset ($value['empdet']['0']['user_credentials']['user_id']) ? $value['empdet']['0']['user_credentials']['user_id'] : '';
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Employee Name :');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+1, $rowcount, $emp_name);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Employee ID : ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+1, $rowcount, $employee_id);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount)->getFont()->setBold(true);
                        
                         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+2, $rowcount, "User ID : ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2, $rowcount)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+3, $rowcount, $userid);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount)->getFont()->setBold(true);
                       //2nd row 
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount+1, 'Branch Name :');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount+1)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+1, $rowcount+1, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+1)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+2, $rowcount+1, "Designation :");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2, $rowcount+1)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+3, $rowcount+1, $designat);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+1)->getFont()->setBold(true);
                        //3rd row
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount+2, 'Department Name :');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount+2)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+1, $rowcount+2, $department);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+2)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+2, $rowcount+2, 'Joining Date :');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2, $rowcount+2)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+3, $rowcount+2, $join);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+2)->getFont()->setBold(true);
                        //4th row
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount+3, 'Termination Date :');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount+3)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+1, $rowcount+3, $termin);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+3)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+2, $rowcount+3, "Calender Days : ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2, $rowcount+3)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+3, $rowcount+3, $calender_days);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+3)->getFont()->setBold(true);
                        //5th row
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount+4, 'Working Days :');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount+4)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+1, $rowcount+4, $wrktime);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+4)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+2, $rowcount+4, "Present Days :");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2, $rowcount+4)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+3, $rowcount+4, $days_present);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+4)->getFont()->setBold(true);
                        //6th row
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount+5, 'Days on Leave :');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount+5)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+1, $rowcount+5, $daysleave);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+5)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+2, $rowcount+5, "Loss off Pay :");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2, $rowcount+5)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+3, $rowcount+5, $loss_offp);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+5)->getFont()->setBold(true);
                        
                        
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount+10), ' Stat Rule');

                        $rowcount = $rowcount + 6;
                        
                        $columncount = 0;

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount+1), ($rowcount + 1), 'Salary Slip    ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, ($rowcount + 1))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, ($rowcount + 1))->getFont()->setSize(14);
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), 'Salary');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Rate');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Amount');
                        for ($i = 0; $i <= 5; $i++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setSize(12);
                        }
                        $rowcount = $rowcount + 1;
                        $arr_data = $value['summary'];
                        if (count($arr_data) >= 0) {
                            $sum = 0;
                                $tot = 0;
                                $dd = 0;
                                $net = 0;
                            foreach ($value['summary'] as $val) {
                                $sum = $sum + $val['ectc']['structure_det_value'];
                                $dd = $dd + $val['ectc']['salary_amount'];
                                $salary = $val['ectc']['salary_head_item_desc'];
                                $rate = round($val['ectc']['structure_det_value'] ); 
                                $amount = round($val['ectc']['salary_amount'] );
                            
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $salary);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $rate);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $amount);

                                $rowcount++;
                            }
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), "Total");
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), round($sum ));
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), round($dd ));
                            
                            $rowcount++;
                            
                            $arr_withoutComponents = $value['withoutcomponent'];
                            if (count($arr_withoutComponents) > 0) {
                                foreach ($arr_withoutComponents as $vals) {
                                    $tot = $tot + $vals['ectc']['structure_det_value'];
                                    $net = $net + $vals['ectc']['salary_amount'];
                                    $salary = $vals['ectc']['salary_head_item_desc'];
                                    $rate = round($vals['ectc']['structure_det_value'] );
                                    $amount = round($vals['ectc']['salary_amount'] );
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $salary);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $rate);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $amount);
                                    $rowcount++;
                                }
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), "Total");
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), round($tot ));
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), round($net ));
                            }
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "Net Salary");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), round($dd + $net ));
                            
                            $pos = $rowcount;
                            $objPHPExcel->getActiveSheet()->getStyle('A'.$start.':D'.$pos)->applyFromArray($BStyle);
                            $start = 0;
                            $pos = 0;
                        } else {
                            $msg = 'No employees found under this shift';
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $msg);
                        }

                        $rowcount++;
                        }
                    }

                }
                }
              if($j=='0'){
                  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), 'No Employees Found Under This Criteria');
               }
                    $objPHPExcel->getActiveSheet()->setTitle('Salary Report');

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
                    $this->render('salaryslip');
                    break;
            }
        }
    }
//salary bank transfer report
    private function generateBanktransferreport($type,  $mode){
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');

 
        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,head_operator FROM emp_salary_slip as ectc
                                                    where item_part='Direct' Group by salary_head_item_desc
                                                    ORDER BY emp_salary_slip_pkey");
        $array_key = array();

        foreach ($arr_keys as $val) {

            if ($val['ectc']['head_operator'] == 'Addition') {
                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {
                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }

        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $conditions = array();
        $conditions[] = 'ectc.month_year="' . $from . '"';
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        
            if($str_criteria_item == ''){
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        
         $condition = "and EmployeeDetails.status ='1'  ";
//        debug($arr_form_data);
        if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1')
        {
            $condition = "and EmployeeDetails.status in ('1','2') ";
        }
        
        $arr_leavepolicydetails_for_template = array();
        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $gross = array();
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
            if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
             
            $arr_gross = $this->EmpCtcTransaction->query("select info.*,user_credentials.user_id,tr.last_approved_working_date,payroll_master.net_salary,EmployeeDetails.*,ifnull(EmployeeDetails.bank_name,'NO NAME') as BANK_NAME from payroll_master  join employee_info as info on (info.emp_pkey = payroll_master.emp_fkey)
                 join emp_details as EmployeeDetails on (EmployeeDetails.emp_pkey = payroll_master.emp_fkey $condition)
                 left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey)
                 join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey)
                 and month_year = '$from' and payroll_master.emp_fkey = '$leavepolicygroupid'  ORDER BY payroll_master.emp_name ");
            }else if ($arr_form_data['select-criteria1'] == 'Units'){
            $arr_gross = $this->EmpCtcTransaction->query("select info.*,user_credentials.user_id,tr.last_approved_working_date,payroll_master.*,EmployeeDetails.*,ifnull(EmployeeDetails.bank_name,'NO NAME') as BANK_NAME from payroll_master  join employee_info as info on (info.emp_pkey = payroll_master.emp_fkey)
                 join emp_details as EmployeeDetails on (EmployeeDetails.emp_pkey = payroll_master.emp_fkey $condition)
                 left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey)
                 join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey)
                 and month_year = '$from' and EmployeeDetails.branch_code = '$leavepolicygroupid'  ORDER BY payroll_master.emp_name ");    
            }else{
                $arr_gross = $this->EmpCtcTransaction->query("select info.*,user_credentials.user_id,tr.last_approved_working_date,payroll_master.*,EmployeeDetails.*,ifnull(EmployeeDetails.bank_name,'NO NAME') as BANK_NAME from payroll_master  join employee_info as info on (info.emp_pkey = payroll_master.emp_fkey)
                 join emp_details as EmployeeDetails on (EmployeeDetails.emp_pkey = payroll_master.emp_fkey $condition)
                 left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey)
                 join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey)
                 and month_year = '$from' and EmployeeDetails.bank_name = '$leavepolicygroupid'  ORDER BY payroll_master.emp_name ");    
            }
//           debug($arr_gross);   
            //$arr_gross = $this->EmpCtcTransaction->query(" select info.*,ectc.*,emp_details.bank_name,emp_details.branch_name,emp_details.branch_address,emp_details.ifsc_code,emp_details.account_no from emp_salary_slip as ectc "
            //        . " left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)"
            //        . "left join emp_details on (emp_details.emp_pkey = info.emp_pkey)"
            //        . "where  ectc.emp_fkey= '$leavepolicygroupid' "
            //        . "AND ectc.item_part = 'Direct' "
            //        . "AND ectc.end_date_effective is null "
            //        . "AND  $id  order by info.EmpName"); // Simplified on 2107-10-19


            //$arr_ot = $this->EmpCtcTransaction->query("select ot_master.set_duration from emp_ot_master as ot_master where ot_master.emp_fkey='$leavepolicygroupid' "
            //        . "And ot_master.month='$otdate' and ot_master.is_verified='Y'");

            //$pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
            //debug($pro_date_desc);
            //debug($arr_gross);
            
//            $this->set('arr_gross', $arr_gross);


//            if (isset($arr_gross) && !empty($arr_gross)) {
//                $gross[$k]['emp_info'] = $arr_gross[0]['info'];
//                $gross[$k]['emp_details'] = $arr_gross[0]['emp_details'];
//                $gross[$k]['ectc'] = $arr_gross[0]['ectc'];
//                //$gross[$k]['ot'] = isset($arr_ot[0]['ot_master']['set_duration']) ? $arr_ot[0]['ot_master']['set_duration'] : 0;
//                //$gross[$k]['prodata'] = $pro_date_desc;
//
//
//                $coun = count($arr_keys);
//                //debug($arr_gross);
//                for ($j = 0; $j < $coun; $j++) {
//                    if ($arr_keys[$j]['ectc']['head_operator'] == 'Addition') {
//                        $gross[$k]['Addition']['keys'][] = $arr_keys[$j][0]['sal_head'];
//                        $gross[$k]['Addition']['value'][] = 0;
//                        $gross[$k]['Addition']['actual'][] = 0;
//                    } else {
//                        $gross[$k]['Deduction']['keys'][] = $arr_keys[$j][0]['sal_head'];
//                        $gross[$k]['Deduction']['value'][] = 0;
//                        $gross[$k]['Deduction']['actual'][] = 0;
//                    }
//                }
//                foreach ($arr_gross as $value) {
//                    if ($value['ectc']['head_operator'] == 'Addition') {
//                        $data = trim($value['ectc']['salary_head_item_desc']);
//                        $key = array_search($data, $gross[$k]['Addition']['keys']); // $key = 2;
//                        $gross[$k]['Addition']['value'][$key] = $value['ectc']['salary_amount'];
//                        $gross[$k]['Addition']['actual'][$key] = $value['ectc']['salary_rate'];
//                    } else {
//                        $data = trim($value['ectc']['salary_head_item_desc']);
//                        $key = array_search($data, $gross[$k]['Deduction']['keys']); // $key = 2;
//                        $gross[$k]['Deduction']['value'][$key] = $value['ectc']['salary_amount'];
//                        $gross[$k]['Deduction']['actual'][$key] = $value['ectc']['salary_rate'];
//                    }
//                }
//            }
            
            $arr_salary_for_template[] = array(
                    'leavepolicyname'=> isset($arr_gross)?$arr_gross:'',
                    //'summary' => $arr_empleaverequests,
                    // 'employees'=>$arr_leavepolicy_employees
            );
            $gross[] = $arr_gross;
            $k++;
        }
        //}
        $this->set('keys', $arr_keys);
        $this->set('gross', $gross);
        $this->set('array_key', $array_key);
        //debug($gross);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('month', $from);
        //Set informations needed for report
        //debug($mode);
        switch ($mode) {
            case 'pdf' :
                //   echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('bankreport');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('SalaryBankTranferReport'.$from.'.pdf', 'D');
                $this->render('bankreport');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_SalaryBankTransferReport - ".$from.".xlsx" : "BankTransferReport" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Salary Bank Transfer Report for " . $from);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                for ($col = 'A'; $col !== 'N'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:N1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $rowcount = 3;
                 $i = 0;
                    foreach ($arr_salary_for_template as $value) {
                        foreach ($value as $valuees) {
//                             debug($valuees);
                             if (!empty($valuees)) {
                                 $i++;
                             }
                             }
                             }
                             if($i <= '0'){ 
                               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'There Is No Data Under This Criteria' );    
                             } else {
                foreach($arr_salary_for_template as $value){
                $i = 0;
                // $rowcount = 3;

                $col = 0;
                
                if (count($value['leavepolicyname']) <= 0) {
                    
                }else{
                if($cr=='EmployeeDetails'){ $heads =  $value['leavepolicyname']['0']['info']['EmpName']; } elseif ($cr == 'Units'){ $heads =  $value['leavepolicyname']['0']['info']['branch']; }else { $heads =  ($value['leavepolicyname']['0']['0']['BANK_NAME'])?$value['leavepolicyname']['0']['0']['BANK_NAME']:'N/A' ; }    
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $heads );
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                for ($col = 'A'; $col !== 'N'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'a7bccb')
                                    )
                                )
                        );
                        $worksheet->mergeCells('A'.$rowcount.':N'.$rowcount);
                $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
//                $objPHPExcel = new PHPExcel(); 
//$objPHPExcel->getDefaultStyle()
//    ->getNumberFormat()
//    ->setFormatCode(
//        PHPExcel_Style_NumberFormat::FORMAT_TEXT
//    );
                
                $rowcount++;
                $rowcount++;
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, ' Employee ID   ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, ' User ID   ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  Employee Name  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Designation ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Department  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Joining Date  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '  Termination Date  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '  Branch ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, '  Bank Name ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, '  Branch Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, '  IFSC Code ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, '  Account Number ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 12, $rowcount)->getFont()->setBold(true);
                

                $col = 12;

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Net Salary  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                
                $j = 1;

                $rowcount = $rowcount + 1;
                
                
                foreach ($value['leavepolicyname'] as $val) {
//                    if($val){
//                    debug($val);
                    $empstatus = (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';
                    $col = 0;
                    $name = $val['info']['EmpName'].$empstatus;
                    $id = $val['info']['employee_id'];
                    $deg = $val['info']['designation'];
                    $dep = $val['info']['department'];
                    $branch = $val['info']['branch'];
                    $bank_name = $val['0']['BANK_NAME'];
                    $branch_name = $val['EmployeeDetails']['branch_name'];
                    $ifsc_code = $val['EmployeeDetails']['ifsc_code'];
                    $acc_number = $val['EmployeeDetails']['account_no'];
                    $join = isset($val['info']['joining_date']) ? $val['info']['joining_date']:'';
                    $termin = isset($val['tr']['last_approved_working_date']) ? $val['tr']['last_approved_working_date']:'';
                    $userid = isset($val['user_credentials']['user_id']) ? $val['user_credentials']['user_id'] : '';

                    //$prodays = $val['prodata']['days'];
                    //$type = $val['prodata']['type'];
                    //$present_total = round($val['prodata']['present']);
                    $ot = isset($val['ot']) ? round(($val['ot'] / 60), 2) : 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $id);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $userid);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $name);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $deg);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $dep);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $join);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $termin);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $branch);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $bank_name);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, $branch_name);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, $ifsc_code);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, " ".$acc_number,PHPExcel_Cell_DataType::TYPE_STRING);
//                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

                    $col = 12;

                    //debug($val);
                    //$count_addition = count($val['Addition']['keys']);
//                    $grss_amt =round($val['0']['payroll_master']['gross_salary']);
//                    for ($m = 0; $m < $count_addition; $m++) {
//                        $number = round($val['Addition']['value'][$m]);
//                        $grss_amt = $number + $grss_amt;
//
//                        $val1 = $number;
//                    }

//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $grss_amt);
//                    $count_addition = count($val['Deduction']['keys']);
                    $col = $col + 1;
                    $dd_amt =round($val['payroll_master']['net_salary']);
//                    for ($m = 0; $m < $count_addition; $m++) {
//                        $number = round($val['Deduction']['value'][$m]);
//                        $dd_amt = $number + $dd_amt;
//
//                        $val1 = $number;
//                    }


                    $netamt = $dd_amt;

//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $dd_amt);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $netamt);

                    $j++;
                    $rowcount++;
//                    }
                }
                $rowcount++;
                }
                
                }
        }
//                     
//                die();
                $objPHPExcel->getActiveSheet()->setTitle('Salary Bank Transfer Report');
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
                $this->render('bankreport');
                break;
        }
    }
//ctc detail report
        private function generateEmpSalaryreport($mode) {
        $arr_form_data = $_REQUEST;
   
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
       if($str_criteria_item == ''){
                 echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            }
       $condition = "ed.status = '1'  ";
//        debug($arr_form_data);
        if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1')
        {
            $condition = "ed.status in ('1','2') ";
        }
        $arr_leavepolicydetails_for_template = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                if ($arr_form_data['select-criteria1'] == 'Units') {
                    $arr_empleaverequests = $this->EmpCtcTransaction->query("select  desg.desig_name,user_credentials.user_id,termination.last_approved_working_date,dpt.dept_name,ep.emp_branch,ep.emp_company_id,br.branch_name,ed.first_name,ed.last_name,ed.status,ectc.* from emp_salary_structure as ectc "
                            . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)"
                            . "left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)"
                            . "left join department as dpt on (dpt.dept_code = ep.emp_dept)"
                            . "left join designation as desg on (desg.desig_code = ep.designation)"
                            . "left join termination as termination on (termination.emp_fkey = ep.emp_fkey)"
                            . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = ep.emp_fkey)"
                            . "left join branches as br on (br.branch_code = ep.emp_branch) where $condition and ectc.head_operator = 'ADDITION'  and lcase(head_type) not in ('manually','variable') and br.branch_code = '$leavepolicygroupid' and end_date_effective is null");
                } else if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $arr_empleaverequests = $this->EmpCtcTransaction->query("select desg.desig_name,user_credentials.user_id,termination.last_approved_working_date,dpt.dept_name,br.branch_name,ed.first_name,ed.last_name,ed.status,ectc.*,ep.emp_company_id,ep.joining_date "
                            . "from emp_salary_structure as ectc "
                            . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) "
                            . "left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)"
                            . " left join branches as br on (br.branch_code = ep.emp_branch)"
                            . "left join department as dpt on (dpt.dept_code = ep.emp_dept)"
                            . "left join termination as termination on (termination.emp_fkey = ep.emp_fkey)"
                            . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = ep.emp_fkey)"
                            . "left join designation as desg on (desg.desig_code = ep.designation)
                                             where ectc.head_operator = 'ADDITION'  and lcase(head_type) not in ('manually','variable') and ectc.emp_fkey = '$leavepolicygroupid' and $condition and end_date_effective is null ");
                }

              //  debug($arr_empleaverequests);
                $arr_salary_for_template[] = array(
                    
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_empleaverequests,
                        // 'employees'=>$arr_leavepolicy_employees
                );
            }
        }
        //debug($arr_salary_for_template);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $employee_attendance = array();
        foreach ($arr_empleaverequests as $val) {
            $emp = $val['ectc']['head_operator'];
            $emppk = $val['ectc']['head_type'];
            $itempart = $val['ectc']['item_part'];
            $employee_attendance[$emppk][$emp][$itempart][] = $val;
        }
        $this->set('employee_attendance', $employee_attendance);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        //Set informations needed for report

        switch ($mode) {
            case 'pdf' :
                //   echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('empsalary');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('CTC-DetailedReport.pdf', 'D');
                $this->render('empsalary');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_ctc.xlsx" : "CTCReport" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "CTC Detailed Report ");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'J'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:F1');
                // $worksheet->mergeCells('A3:F3');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 2;
                $i = 0;
foreach ($arr_salary_for_template as $value) {
                        foreach ($value as $valuees) {
//                             debug($valuees);
                             if (!empty($valuees)) {
                                 $i++;
                             }
                             }
                             }
                             if($i <= '0'){ 
                                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'There Is No Data Under This Criteria ');
                                 } else {
                             
                $i = 0;
                foreach ($arr_salary_for_template as $value) {

                    if (count($value['summary']) !== 0) {
                        $i += 1;


                        if ($cr == 'Units') {

                            $name = isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : '';
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Name: ' . $name);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        } else {


                            $firstnme = isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] : '';

                            $secondnme = isset($value['summary']['0']['ed']['last_name']) ? $value['summary']['0']['ed']['last_name'] : '';
                            $empstatus = (isset($value['summary']['0']['ed']['status'])) && $value['summary']['0']['ed']['status'] =="2" ? '  (Resigned)':'';
                            $name = $firstnme . " " . $secondnme.$empstatus;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Name: ' . $name);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        }
                        $rowcount = $rowcount + 1;
                        $eid = isset($value['summary']['0']['ep']['emp_company_id']) ? $value['summary']['0']['ep']['emp_company_id'] : '';
                        $branch = isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : '';
                        $besg = isset($value['summary']['0']['desg']['desig_name']) ? $value['summary']['0']['desg']['desig_name'] : '';
                        $bepartment = isset($value['summary']['0']['dpt']['dept_name']) ? $value['summary']['0']['dpt']['dept_name'] : '';
                        $userid = isset($value['summary']['0']['user_credentials']['user_id']) ? $value['summary']['0']['user_credentials']['user_id'] : '';
                        $termin = isset($value['summary']['0']['termination']['last_approved_working_date']) ? $value['summary']['0']['termination']['last_approved_working_date'] : '';
                        $join = isset($value['summary']['0']['ep']['joining_date']) ? $value['summary']['0']['ep']['joining_date'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'EMP ID : ' . $eid);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, '  Branch : ' . $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, '  Designations : ' . $besg);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, '  Departments : ' . $bepartment);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, '  User ID : ' . $userid);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, '  Joining Date : ' . $join);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, '  Termination Date : ' . $termin);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                         $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, '  Salary ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, '  Amount ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
//$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2).$rowcount, '  Head Operator');
//$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3).$rowcount, '  Head Type ');
//$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4).$rowcount, '  Item Part');
                        $rowcount = $rowcount + 1;


                        $arr_data = $value['summary'];
                        if (count($arr_data) >= 0) {
                            $sum = 0;
                            foreach ($arr_data as $val) {

                                $sum = $sum + $val['ectc']['structure_det_value'];
                                $desc = $val['ectc']['salary_head_item_desc'];
                                $det = $val['ectc']['structure_det_value'];
                                $op = $val['ectc']['head_operator'];
                                $type = $val['ectc']['head_type'];
                                $part = $val['ectc']['item_part'];
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $desc);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $det);
//$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2).$rowcount, $op);
//$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3).$rowcount, $type);
//$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4).$rowcount, $part);                  
                                $rowcount++;
                            }
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Grand Total');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $sum);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        } else {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No employees found under this data');
                        }
                    } $rowcount++;
                }
                                 }
                $objPHPExcel->getActiveSheet()->setTitle('CTC Report ');
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
                $this->render('empsalary');
                break;
        }
    }

//ARUN Grass 
    // edited by sruthi 09/09/16
    //gross salary detail report
    private function GenerateSalaryGross($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
         $arr_requestdata = $this->request->data;

        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,head_operator FROM emp_salary_slip as ectc
                                                    where item_part='Direct' Group by salary_head_item_desc
                                                    ORDER BY emp_salary_slip_pkey");
        $array_key = array();

        foreach ($arr_keys as $val) {

            if ($val['ectc']['head_operator'] == 'Addition') {
                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {
                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }

        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $conditions = array();
        $conditions[] = 'ectc.month_year="' . $from . '"';
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
       
            if($str_criteria_item == ''){
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
        }
        $arr_leavepolicydetails_for_template = array();
        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $gross = array();
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

            $arr_gross = $this->EmpCtcTransaction->query(" select info.*,user_credentials.user_id,emp_ctc_transaction.emp_derived_anualctc,termination.last_approved_working_date,emp_details.status,ectc.* from emp_salary_slip as ectc "
                    . " left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)"
                    . "left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey)"
                    . "left join termination as termination on (termination.emp_fkey = info.emp_pkey)"
                    . "left join emp_ctc_transaction on (emp_ctc_transaction.emp_fkey = ectc.emp_fkey  and emp_ctc_transaction.end_date_effective is null )"
                    . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = info.emp_pkey)"
                    . "where  ectc.emp_fkey= '$leavepolicygroupid' "
                    . "AND ectc.item_part = 'Direct' and salary_amount != 0 "
                    . "AND ectc.end_date_effective is null  "
                    . "AND  $id  order by info.EmpName ");
                  
            $arr_ot = $this->EmpCtcTransaction->query("select ot_master.set_duration from emp_ot_master as ot_master where ot_master.emp_fkey='$leavepolicygroupid' "
                    . "And ot_master.month='$otdate' and ot_master.is_verified='Y'");
//debug($arr_gross['termination']['last_approved_working_date']);
//debug($arr_gross);
            $pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
            //debug($pro_date_desc);
            $this->set('arr_gross', $arr_gross);


            if (isset($arr_gross) && !empty($arr_gross)) {
                $gross[$k]['emp_info'] = $arr_gross[0]['info'];
                $gross[$k]['emp_details'] = $arr_gross[0]['emp_details'];
                $gross[$k]['termination'] = $arr_gross[0]['termination'];
                $gross[$k]['user_credentials'] = $arr_gross[0]['user_credentials'];
                $gross[$k]['ectc'] = $arr_gross[0]['ectc'];
                $gross[$k]['ot'] = isset($arr_ot[0]['ot_master']['set_duration']) ? $arr_ot[0]['ot_master']['set_duration'] : 0;
                $gross[$k]['prodata'] = $pro_date_desc;
                $gross[$k]['emp_ctc_transaction'] =  $arr_gross[0]['emp_ctc_transaction'];


                $coun = count($arr_keys);
                //debug($arr_gross);
                for ($j = 0; $j < $coun; $j++) {
                    if ($arr_keys[$j]['ectc']['head_operator'] == 'Addition') {
                        $gross[$k]['Addition']['keys'][] = $arr_keys[$j][0]['sal_head'];
                        $gross[$k]['Addition']['value'][] = 0;
                        $gross[$k]['Addition']['actual'][] = 0;
                    } else {
                        $gross[$k]['Deduction']['keys'][] = $arr_keys[$j][0]['sal_head'];
                        $gross[$k]['Deduction']['value'][] = 0;
                        $gross[$k]['Deduction']['actual'][] = 0;
                    }
                }
//                debug($arr_gross);
                foreach ($arr_gross as $value) {
                    if ($value['ectc']['head_operator'] == 'Addition') {
                        $data = trim($value['ectc']['salary_head_item_desc']);
                        $key = array_search($data, $gross[$k]['Addition']['keys']); // $key = 2;
                        $gross[$k]['Addition']['value'][$key] += $value['ectc']['salary_amount'];
                        $gross[$k]['Addition']['actual'][$key] += $value['ectc']['structure_det_value'];
                    } else {
                        $data = trim($value['ectc']['salary_head_item_desc']);
                        $key = array_search($data, $gross[$k]['Deduction']['keys']); // $key = 2;
                        $gross[$k]['Deduction']['value'][$key] += $value['ectc']['salary_amount'];
                        $gross[$k]['Deduction']['actual'][$key] += $value['ectc']['structure_det_value'];
                    }
                }
            }

            $arr_salary_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    //'summary' => $arr_empleaverequests,
                    // 'employees'=>$arr_leavepolicy_employees
            );
            $k++;
        }

        //}
        $this->set('keys', $arr_keys);
        $this->set('gross', $gross);
        $this->set('array_key', $array_key);
        //debug($gross);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('month', $from);
        //Set informations needed for report

        switch ($mode) {
            case 'pdf' :
                //   echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('grossreport');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A1', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('GrossSalaryDetailedReport'.$from.'.pdf', 'D');
                $this->render('grossreport');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_GrossSalaryDetailReport".$from.".xlsx" : "GrossReport" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Gross Salary Detailed Report for " . $from);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                for ($col = 'A'; $col !== 'M'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:M1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount = 2;
            if (count($gross) <= 0) {
                  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'There is no data under this criteria');
                           } else {
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Details');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'Standard Salary');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                $i = 0;
                $rowcount = 3;

                $col = 0;

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, ' Employee ID   ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, ' User ID   ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  Employee Name  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Designation ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Department  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Branch ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '  Date of Joining ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '  Date of Termination ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, '  Total Days ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, '  Days Type');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, '  Present Days ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, '  Overtime (In Hrs.) ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 12, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, '  LOP Days ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);

                $col = 13;
                $addition = $array_key['Addition'];
                foreach ($addition as $value) {
                    $head = $value;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $head);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                    $col++;
                }

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'Gross Salary');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $col++;
                
                $deduction = $array_key['Deduction'];
                foreach ($deduction as $value) {
                    $head = $value;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $head);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                    $col++;
                }
                
                $columncount = $col + 1;
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . 2, 'Actual Salary');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($col + 2), 2)->getFont()->setBold(true);



                $addition = $array_key['Addition'];
                foreach ($addition as $value) {
                    $head = $value;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $head);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                    $col++;
                }


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Gross Salary  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $col = $col + 1;
                $deduction = $array_key['Deduction'];
                foreach ($deduction as $value) {
                    $head = $value;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $head);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                    $col++;
                }


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, ' Total Deduction  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Net Salary ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow((0), 2, (13), 2);
                $objPHPExcel->getActiveSheet()
                        ->getStyleByColumnAndRow(0, 2)
                        ->applyFromArray(
                                array(
                                    'alignment' => array(
                                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    )
                                )
                );


                $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(($columncount), 2, ($col + 2), 2);
                $objPHPExcel->getActiveSheet()
                        ->getStyleByColumnAndRow($columncount, 2)
                        ->applyFromArray(
                                array(
                                    'alignment' => array(
                                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    )
                                )
                );
                $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow((14), 2, ($columncount - 1), 2);
                $objPHPExcel->getActiveSheet()
                        ->getStyleByColumnAndRow(14, 2)
                        ->applyFromArray(
                                array(
                                    'alignment' => array(
                                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    )
                                )
                );
                $j = 1;

                $rowcount = $rowcount + 1;

                foreach ($gross as $val) {
                    $col = 0;
                    $empstatus = isset($val['emp_details']['status']) && $val['emp_details']['status'] =="2" ? '  (Resigned)':'';
                    $name = $val['emp_info']['EmpName'].$empstatus;
                    $id = $val['emp_info']['employee_id'];
                    $deg = $val['emp_info']['designation'];
                    $dep = $val['emp_info']['department'];
                    $branch = $val['emp_info']['branch'];
                    $prodays = $val['prodata']['days'];
                    $type = $val['prodata']['type'];
                    $present_total = $val['prodata']['present'];
                    $userid = isset($val['user_credentials']['user_id']) ? $val['user_credentials']['user_id'] : '';
                    $join = isset($val['emp_info']['joining_date']) ? $val['emp_info']['joining_date'] : '';
                    $termin = isset($val['termination']['last_approved_working_date']) ? $val['termination']['last_approved_working_date'] : '0';
                    $ot = isset($val['ot']) ? round(($val['ot'] / 60), 2) : 0;
                    $lop = isset($val['ectc']['lop_total']) ? $val['ectc']['lop_total'] : '';
                    
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $id);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $userid);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $name);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $deg);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $dep);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $branch);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $join);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $termin);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $prodays);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, $type);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, $present_total);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, $ot);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, $lop);
                    $col = 13;
                    $count_addition = count($val['Addition']['keys']);
                    for ($m = 0; $m < $count_addition; $m++) {
                        $number = round($val['Addition']['actual'][$m]);


                        $val1 = $number;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $val1);
                        $col++;
                    }
                    $val1 = isset($val['emp_ctc_transaction']['emp_derived_anualctc']) ? $val['emp_ctc_transaction']['emp_derived_anualctc'] / 12 :0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $val1);
                    $col++;
                    $count_addition = count($val['Deduction']['keys']);
                    for ($m = 0; $m < $count_addition; $m++) {
                        $number = round($val['Deduction']['actual'][$m]); 


                        $val1 = $number;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $val1);
                        $col++;
                    }
                    
                    
                    //debug($val);
                    $count_addition = count($val['Addition']['keys']);
                    $grss_amt = 0;
                    for ($m = 0; $m < $count_addition; $m++) {
                        $number = round($val['Addition']['value'][$m],2);
                        $grss_amt = $number + $grss_amt;

                        $val1 = $number;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $val1);
                        $col++;
                    }

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $grss_amt);
                    $count_addition = count($val['Deduction']['keys']);
                    $col = $col + 1;
                    $dd_amt = 0;
                    for ($m = 0; $m < $count_addition; $m++) {
                        $number = round($val['Deduction']['value'][$m],2);
                        $dd_amt = $number + $dd_amt;

                        $val1 = $number;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $val1);
                        $col++;
                    }


                    $netamt = $grss_amt + $dd_amt;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $dd_amt);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, round($netamt));

                    $j++;
                    $rowcount++;
                }
                           }
//                                     
                $objPHPExcel->getActiveSheet()->setTitle('Gross Salary Reports');
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
                $this->render('grossreport');
                break;
        }
    }

    // edited by sruthi 09/09/16    here ends     
    // edited by sruthi 22/09/16
    //gross salary summary reports
    private function GenerateSalaryGrossSummary($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');

 
        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,head_operator FROM emp_salary_slip as ectc
                                                    where item_part='Direct' Group by salary_head_item_desc
                                                    ORDER BY emp_salary_slip_pkey");
        $array_key = array();

        foreach ($arr_keys as $val) {

            if ($val['ectc']['head_operator'] == 'Addition') {
                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {
                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }

        
            
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $conditions = array();
        $conditions[] = 'ectc.month_year="' . $from . '"';
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            
        $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            if($str_criteria_item == ''){
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $arr_leavepolicydetails_for_template = array();
        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $gross = array();
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

            $arr_gross = $this->EmpCtcTransaction->query(" select info.*,user_credentials.user_id,termination.last_approved_working_date,emp_details.status,ectc.* from emp_salary_slip as ectc "
                    . " left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)"
                    . " left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey)"
                    . " left join termination as termination on (termination.emp_fkey = emp_details.emp_pkey)"
                    . " left join user_credentials as user_credentials on (user_credentials.emp_fkey = emp_details.emp_pkey)"
                    . "where  ectc.emp_fkey= '$leavepolicygroupid' "
                    . "AND ectc.item_part = 'Direct' "
                    . "AND ectc.end_date_effective is null "
                    . "AND  $id  order by info.EmpName");

      //     debug($arr_gross['termination']['last_approved_working_date']); 
            $arr_ot = $this->EmpCtcTransaction->query("select ot_master.set_duration from emp_ot_master as ot_master where ot_master.emp_fkey='$leavepolicygroupid' "
                    . "And ot_master.month='$otdate' and ot_master.is_verified='Y'");

            $pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
           // debug($arr_gross);
            $this->set('arr_gross', $arr_gross);


            if (isset($arr_gross) && !empty($arr_gross)) {
                $gross[$k]['emp_info'] = $arr_gross[0]['info'];
                $gross[$k]['emp_details'] = $arr_gross[0]['emp_details'];
                $gross[$k]['termination'] = $arr_gross[0]['termination'];
                $gross[$k]['user_credentials'] = $arr_gross[0]['user_credentials'];
                $gross[$k]['ectc'] = $arr_gross[0]['ectc'];
                $gross[$k]['ot'] = isset($arr_ot[0]['ot_master']['set_duration']) ? $arr_ot[0]['ot_master']['set_duration'] : 0;
                $gross[$k]['prodata'] = $pro_date_desc;
                


                $coun = count($arr_keys);
                //debug($arr_gross);
                for ($j = 0; $j < $coun; $j++) {
                    if ($arr_keys[$j]['ectc']['head_operator'] == 'Addition') {
                        $gross[$k]['Addition']['keys'][] = $arr_keys[$j][0]['sal_head'];
                        $gross[$k]['Addition']['value'][] = 0;
                        $gross[$k]['Addition']['actual'][] = 0;
                    } else {
                        $gross[$k]['Deduction']['keys'][] = $arr_keys[$j][0]['sal_head'];
                        $gross[$k]['Deduction']['value'][] = 0;
                        $gross[$k]['Deduction']['actual'][] = 0;
                    }
                }
                foreach ($arr_gross as $value) {
                    if ($value['ectc']['head_operator'] == 'Addition') {
                        $data = trim($value['ectc']['salary_head_item_desc']);
                        $key = array_search($data, $gross[$k]['Addition']['keys']); // $key = 2;
                        $gross[$k]['Addition']['value'][$key] = $value['ectc']['salary_amount'];
                        $gross[$k]['Addition']['actual'][$key] = $value['ectc']['salary_rate'];
                    } else {
                        $data = trim($value['ectc']['salary_head_item_desc']);
                        $key = array_search($data, $gross[$k]['Deduction']['keys']); // $key = 2;
                        $gross[$k]['Deduction']['value'][$key] += $value['ectc']['salary_amount'];
                        $gross[$k]['Deduction']['actual'][$key] += $value['ectc']['salary_rate'];
                    }
                }
            }

            $arr_salary_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    //'summary' => $arr_empleaverequests,
                    // 'employees'=>$arr_leavepolicy_employees
            );
            $k++;
        }

        //}
        $this->set('keys', $arr_keys);
        $this->set('gross', $gross);
        $this->set('array_key', $array_key);
        //debug($gross);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('month', $from);
        //Set informations needed for report

        switch ($mode) {
            case 'pdf' :
                //   echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('grosssummaryreport');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('GrossSalarySummaryReport'.$from.'.pdf', 'D');
                $this->render('grosssummaryreport');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_GrossSalarySummaryReport".$from.".xlsx" : "GrossReport" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Gross Salary Summary Reports for " . $from);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                for ($col = 'A'; $col !== 'M'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount = 2;
                
                $i = 0;
                // $rowcount = 3;

                $col = 0;
                   if (count($gross) <= 0) { 
                       
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  There Is No Data Under This Critereia ');
             } else { 
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, ' Empolyee ID   ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, ' User ID   ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  Employee Name  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Designation ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Department  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Branch ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '  Date of Joining ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '  Date Of Termination ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, '  Total Days ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, '  Days Type');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, '  Present Days ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, '  Overtime (In Hrs.) ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 12, $rowcount)->getFont()->setBold(true);

                $col = 12;

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Gross Salary  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $col = 13;
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, ' Total Deduction  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Net Salary ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);

                $j = 1; 

                $rowcount = $rowcount + 1;

                foreach ($gross as $val) {
                    $col = 0;
                    $empstatus =  (isset($val['emp_details']['status'])) && $val['emp_details']['status'] =="2" ? '  (Resigned)':'';
                    $name = $val['emp_info']['EmpName'].$empstatus;
                    $id = $val['emp_info']['employee_id'];
                    $deg = $val['emp_info']['designation'];
                    $dep = $val['emp_info']['department'];
                    $branch = $val['emp_info']['branch'];
                    $prodays = $val['prodata']['days'];
                    $type = $val['prodata']['type'];
                    $present_total = $val['prodata']['present'];
                    $join = isset($val['emp_info']['joining_date']) ? $val['emp_info']['joining_date'] : '';
                    $termin = isset($val['termination']['last_approved_working_date']) ? $val['termination']['last_approved_working_date'] : '';
                    $userid = isset($val['user_credentials']['user_id']) ? $val['user_credentials']['user_id'] : '';
                    $ot = isset($val['ot']) ? round(($val['ot'] / 60), 2) : 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $id);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $userid);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $name);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $deg);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $dep);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $branch);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $join);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $termin);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $prodays);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, $type);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, $present_total);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, $ot);
                    
                    $col = 12;

                    //debug($val);
                    $count_addition = count($val['Addition']['keys']);
                    $grss_amt = 0;
                    for ($m = 0; $m < $count_addition; $m++) {
                        $number = round($val['Addition']['value'][$m] );
                        $grss_amt = $number + $grss_amt;

                        $val1 = $number;
                    }

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $grss_amt);
                    $count_addition = count($val['Deduction']['keys']);
                    $col = $col + 1;
                    $dd_amt = 0;
                    for ($m = 0; $m < $count_addition; $m++) {
                        $number = round($val['Deduction']['value'][$m] );
                        $dd_amt = $number + $dd_amt;

                        $val1 = $number;
                    }


                    $netamt = $grss_amt + $dd_amt;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $dd_amt);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, round($netamt));

                    $j++;
                    $rowcount++;
                }
             }
//                                     
                $objPHPExcel->getActiveSheet()->setTitle('Gross Salary Reports');
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
                $this->render('grosssummaryreport');
                break;
        }
    }

    //code ends here//










//payroll summary report
    private function GenerateSummaryPayrolreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
//        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
//        
       
        $needBranchWiseReport = false;
        $conditions = array();
        //   $conditions[] = 'FROMDATE >="' . $from . '" and TODATE<="' . $to . '"';
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
            $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array(
                                "fields" => "reportcriteria,reportcriteria_field",
                                "conditions" => array(
                                    "reporttype" => "SummaryPayroll",
                                    "status" => 1,
                                    'reportcriteria' => $str_criteria_item
                                )
            )));
            if (isset($arr_reportcriterias[0]['reportcriteria_field']) && isset($arr_form_data[$str_criteria_item])) {
                $conditions[] = $arr_reportcriterias[0]['reportcriteria'] . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
            }
        }
         $condition = ' where EmployeeDetails.status= 1';
//        debug($arr_form_data);
        if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1')
        {
            $condition =  "where EmployeeDetails.status in('1','2')";
           
        }
       
        $str_conditions = implode(' AND ', $conditions);

        $arr_empleaverequests = $this->EmpCtcTransaction->query('select designation.desig_name,user_credentials.user_id,ep.designation,ep.emp_fkey,ep.joining_date,ep.emp_dept,emp_company_id,payroll_master.*,Units.branch_name,Units.branch_code,department.dept_name,termination.last_approved_working_date,'
                . 'EmployeeDetails.emp_id,EmployeeDetails.middile_name,EmployeeDetails.last_name,EmployeeDetails.status  '
                . 'from payroll_master   '
                . 'left join emp_proff as ep on(ep.emp_fkey = payroll_master.emp_fkey) '
                . 'left join emp_details as EmployeeDetails on (EmployeeDetails.emp_pkey = payroll_master.emp_fkey)'
//                . "left join  user_credentials as uc on (EmployeeDetails.emp_pkey = uc.emp_fkey) "
                . ' left join branches as Units on(Units.branch_code = EmployeeDetails.branch_code) '
                . 'left join designation as designation on (designation.desig_code = ep.designation)'
                . 'left join department as department on (department.dept_code = ep.emp_dept)'
                . 'left join termination as termination on (termination.emp_fkey = ep.emp_fkey)'
                . 'left join user_credentials as user_credentials on (user_credentials.emp_fkey = ep.emp_fkey)'
                . $condition
                . ' and month_year = "' . $from . '"'
                . 'and ' . $str_conditions
                . '   ORDER BY payroll_master.emp_name  ');



        //debug($arr_empleaverequests);

        $arr_leavesummary_for_template = array();
        if ($needBranchWiseReport) {
            //Parse array for branchwise report
            foreach ($arr_empleaverequests as $leaverequest) {
                //  debug($leaverequest);
                $branch_code = isset($leaverequest['Units']['branch_code']) ? $leaverequest['Units']['branch_code'] : '';
                $branch_name = isset($leaverequest['Units']['branch_name']) ? $leaverequest['Units']['branch_name'] : '';
                //  debug($branch_code);
                if ($branch_code != '$branch_name') {

                    if (!isset($arr_leavesummary_for_template[$branch_code])) {
                        $arr_leavesummary_for_template[$branch_code] = array(
                            'branch_name' => $branch_name,
                            'leaverequests' => array()
                        );
                    }
                    //debug($leaverequest);
                    $request = array();
                    $request['userid'] = isset($leaverequest['user_credentials']['user_id']) ? $leaverequest['user_credentials']['user_id'] : '';
                    $request['terminatedate'] = isset($leaverequest['termination']['last_approved_working_date']) ? $leaverequest['termination']['last_approved_working_date'] : '';
                    $request['joindate'] = isset($leaverequest['ep']['joining_date']) ? $leaverequest['ep']['joining_date'] : '';
                    $request['department'] = isset($leaverequest['department']['dept_name']) ? $leaverequest['department']['dept_name'] : '';
                    $request['branch_code'] = isset($leaverequest['Units']['branch_name']) ? $leaverequest['Units']['branch_name'] : '';
                    $request['emp_name'] = isset($leaverequest['payroll_master']['emp_name']) ? $leaverequest['payroll_master']['emp_name'] : '';
                    $request['designation'] = isset($leaverequest['designation']['desig_name']) ? $leaverequest['designation']['desig_name'] : '';
                    $request['emp_id'] = isset($leaverequest['ep']['emp_company_id']) ? $leaverequest['ep']['emp_company_id'] : '';
                    $request['month_year'] = isset($leaverequest['payroll_master']['month_year']) ? $leaverequest['payroll_master']['month_year'] : '';
                    $request['payroll_master'] = isset($leaverequest['payroll_master']['days_presant']) ? $leaverequest['payroll_master']['days_presant'] : '';
                    $request['leave_to'] = isset($leaverequest['payroll_master']['days_leave']) ? $leaverequest['payroll_master']['days_leave'] : '';
                    $request['leave_applied_on'] = isset($leaverequest['payroll_master']['loss_of_pay']) ? $leaverequest['payroll_master']['loss_of_pay'] : '';
                    $request['fromhalf'] = isset($leaverequest['payroll_master']['calander_days']) ? $leaverequest['payroll_master']['calander_days'] : '';
                    $request['tohalf'] = isset($leaverequest['payroll_master']['working_days']) ? $leaverequest['payroll_master']['working_days'] : '';
                    $request['leavedays'] = isset($leaverequest['payroll_master']['days_leave']) ? $leaverequest['payroll_master']['days_leave'] : '';
                    $request['fromhalf'] = isset($leaverequest['payroll_master']['gross_salary']) ? $leaverequest['payroll_master']['gross_salary'] : '';
                    $request['tohalf'] = isset($leaverequest['payroll_master']['total_variables']) ? $leaverequest['payroll_master']['total_variables'] : '';
                    $request['loss_of_pay'] = isset($leaverequest['payroll_master']['loss_of_pay']) ? $leaverequest['payroll_master']['loss_of_pay'] : '';
                    $request['working_days'] = isset($leaverequest['payroll_master']['working_days']) ? $leaverequest['payroll_master']['working_days'] : '';
                    $request['monthly_ctc'] = isset($leaverequest['payroll_master']['monthly_ctc']) ? $leaverequest['payroll_master']['monthly_ctc'] : '';
                    $request['gross_salary'] = isset($leaverequest['payroll_master']['gross_salary']) ? $leaverequest['payroll_master']['gross_salary'] : '';
                    $request['total_deduction'] = isset($leaverequest['payroll_master']['total_deduction']) ? $leaverequest['payroll_master']['total_deduction'] : '';
                    $request['total_variables'] = isset($leaverequest['payroll_master']['total_variables']) ? $leaverequest['payroll_master']['total_variables'] : '';
                    $request['net_salary'] = isset($leaverequest['payroll_master']['net_salary']) ? $leaverequest['payroll_master']['net_salary'] : '';
                    $request['approved'] = isset($leaverequest['payroll_master']['approved']) ? $leaverequest['payroll_master']['approved'] : '';
                    $request['status'] = (isset($leaverequest['EmployeeDetails']['status'])) && $leaverequest['EmployeeDetails']['status']=="2" ? '(Resigned)':'';
                    $arr_leavesummary_for_template[$branch_code]['leaverequests'][] = $request;
                }
            }
        } else {
            // echo 'hi';
            //Parse array for simple report
            $arr_leavesummary_for_template['leaverequests'] = array();
            //debug($arr_leavesummary_for_template);
            //employee wis
            foreach ($arr_empleaverequests as $leaverequest) {
                $request['userid'] = isset($leaverequest['user_credentials']['user_id']) ? $leaverequest['user_credentials']['user_id'] : '';
                $request['terminatedate'] = isset($leaverequest['termination']['last_approved_working_date']) ? $leaverequest['termination']['last_approved_working_date'] : '';
                $request['joindate'] = isset($leaverequest['ep']['joining_date']) ? $leaverequest['ep']['joining_date'] : '';
                $request['department'] = isset($leaverequest['department']['dept_name']) ? $leaverequest['department']['dept_name'] : '';
                $request['emp_name'] = isset($leaverequest['payroll_master']['emp_name']) ? $leaverequest['payroll_master']['emp_name'] : '';
                $request['designation'] = isset($leaverequest['designation']['desig_name']) ? $leaverequest['designation']['desig_name'] : '';
                $request['emp_id'] = isset($leaverequest['ep']['emp_company_id']) ? $leaverequest['ep']['emp_company_id'] : '';
                $request['branch_code'] = isset($leaverequest['Units']['branch_name']) ? $leaverequest['Units']['branch_name'] : '';
                $request['month_year'] = isset($leaverequest['payroll_master']['month_year']) ? $leaverequest['payroll_master']['month_year'] : '';
                $request['payroll_master'] = isset($leaverequest['payroll_master']['days_presant']) ? $leaverequest['payroll_master']['days_presant'] : '';
                $request['leave_to'] = isset($leaverequest['payroll_master']['days_leave']) ? $leaverequest['payroll_master']['days_leave'] : '';
                $request['leave_applied_on'] = isset($leaverequest['payroll_master']['loss_of_pay']) ? $leaverequest['payroll_master']['loss_of_pay'] : '';
                $request['fromhalf'] = isset($leaverequest['payroll_master']['calander_days']) ? $leaverequest['payroll_master']['calander_days'] : '';
                $request['tohalf'] = isset($leaverequest['payroll_master']['working_days']) ? $leaverequest['payroll_master']['working_days'] : '';
                $request['fromhalf'] = isset($leaverequest['payroll_master']['gross_salary']) ? $leaverequest['payroll_master']['gross_salary'] : '';
                $request['tohalf'] = isset($leaverequest['payroll_master']['total_variables']) ? $leaverequest['payroll_master']['total_variables'] : '';
                $request['leavedays'] = isset($leaverequest['payroll_master']['days_leave']) ? $leaverequest['payroll_master']['days_leave'] : '';
                $request['loss_of_pay'] = isset($leaverequest['payroll_master']['loss_of_pay']) ? $leaverequest['payroll_master']['loss_of_pay'] : '';
                $request['working_days'] = isset($leaverequest['payroll_master']['working_days']) ? $leaverequest['payroll_master']['working_days'] : '';
                $request['monthly_ctc'] = isset($leaverequest['payroll_master']['monthly_ctc']) ? $leaverequest['payroll_master']['monthly_ctc'] : '';
                $request['gross_salary'] = isset($leaverequest['payroll_master']['gross_salary']) ? $leaverequest['payroll_master']['gross_salary'] : '';
                $request['total_deduction'] = isset($leaverequest['payroll_master']['total_deduction']) ? $leaverequest['payroll_master']['total_deduction'] : '';
                $request['total_variables'] = isset($leaverequest['payroll_master']['total_variables']) ? $leaverequest['payroll_master']['total_variables'] : '';
                $request['net_salary'] = isset($leaverequest['payroll_master']['net_salary']) ? $leaverequest['payroll_master']['net_salary'] : '';
                $request['approved'] = isset($leaverequest['payroll_master']['approved']) ? $leaverequest['payroll_master']['approved'] : '';
                $request['status'] = (isset($leaverequest['EmployeeDetails']['status'])) && $leaverequest['EmployeeDetails']['status']=="2" ? '(Resigned)':'';
                $arr_leavesummary_for_template['leaverequests'][] = $request;
            }
        }
        //debug($request['status']);
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('needBranchWiseReport', $needBranchWiseReport);
        $this->set('arr_leavesummary_for_template', $arr_leavesummary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('arr_empleaverequests', $arr_empleaverequests);
        $this->set('report_month', $report_month);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('payrolsummaryreports');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('PayrollSummaryReport-'.$report_month.'.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $month = isset($arr_empleaverequests['0']['payroll_master']['month_year']) ? $arr_empleaverequests['0']['payroll_master']['month_year'] : '0';
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . " Payroll Summary Report ".$report_month.".xlsx" : "Payroll Summary Report" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();
             
                $worksheet->setCellValueByColumnAndRow(0, 1, "Payroll Summary Report - ".$report_month);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                
                for ($col = 'A'; $col !== 'N'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                
                $worksheet->mergeCells('A1:N1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );


                $rowcount = 2;
                if (isset($needBranchWiseReport) && $needBranchWiseReport == true) {
                    if (count($arr_leavesummary_for_template) <= 0) { 
     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'There Is No Data Under This Criteria');
     } else {
                    foreach ($arr_leavesummary_for_template as $branch_code => $leavesummary) {
                        $branch = $leavesummary['branch_name'];

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'User ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Date of Termination');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Month');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Leave Days');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Loss Off Pay');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Working Days');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Standard Gross Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'This Month Gross Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'Total Deductions');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, 'Total Variables');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, 'Net Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, 'Verified');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17, $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $arr_data = $leavesummary['leaverequests'];
                        if (count($arr_data) > 0) {
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {

                                if ($val['fromhalf'] == '1') {
                                    $fromhalf = "First Half";
                                } else if ($val['fromhalf'] == '2') {
                                    $fromhalf = "Second Half";
                                } else {
                                    $fromhalf = "";
                                }

                                if ($val['tohalf'] == '1') {
                                    $tohalf = "First Half";
                                } else if ($val['tohalf'] == '2') {
                                    $tohalf = "Second Half";
                                } else {
                                    $tohalf = "";
                                }
//                                $empname = (isset($val['status'])) && $val['status'] =="2" ? '  (Resigned)':'';
                                $slno = $i;
                                $id = $val['emp_id'];
                                $name = $val['emp_name'].' '.$val['status'];
                                $desi = $val['designation'];
                                //  $branch = $val['month_year']. " ".$fromhalf;
                                $dep = $val['department'];
                                $join = $val['joindate'];
                                $termination = $val['terminatedate'];
                                $month = $val['month_year'];
                                $leaverequest = $val['leavedays'];
                                $lossofpay = $val['loss_of_pay'];
                                $wrkday = $val['working_days'];
                                $monthlyctc = $val['monthly_ctc'];
                                $grosssal = $val['gross_salary'];
                                $ded = $val['total_deduction'];
                                $totalvar = $val['total_variables'];
                                $net = $val['net_salary'];
                                $verified = ($val['approved'] == 'N')?"No":"Yes";
                                $userid = $val['userid'];
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $userid);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $desi);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $dep);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $join);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $termination);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $month);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $leaverequest);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $lossofpay);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $wrkday);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $monthlyctc);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $grosssal);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $ded);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(14))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $totalvar);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(15))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, $net);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(16))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, $verified);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(17))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $rowcount++;
                                $i++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }//end foreach
                } }else {
                    if (count($arr_leavesummary_for_template['leaverequests']) <= 0) { 
  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'There Is No Data Under This Criteria');
} 
else { 
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'User ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, ' Date of Join');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Date of Termination');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Branch Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Month');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Leave Days');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Loss Off Pay');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Working Days');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Standard Gross Salary');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'This Month Gross Salary');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, 'Total Deductions');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, 'Total Variables');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, 'Net Salary');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . $rowcount, 'Verified');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(18, $rowcount)->getFont()->setBold(true);
                    $rowcount = $rowcount + 1;
                    $arr_data = $arr_leavesummary_for_template['leaverequests'];
                    if (count($arr_data) > 0) {
                        $tot1 = 0;
                        $i = 1;
                        foreach ($arr_data as $val) {

                            if ($val['fromhalf'] == '1') {
                                $fromhalf = "First Half";
                            } else if ($val['fromhalf'] == '2') {
                                $fromhalf = "Second Half";
                            } else {
                                $fromhalf = "";
                            }

                            if ($val['tohalf'] == '1') {
                                $tohalf = "First Half";
                            } else if ($val['tohalf'] == '2') {
                                $tohalf = "Second Half";
                            } else {
                                $tohalf = "";
                            }
                            $slno = $i;
                            $id = $val['emp_id'];
//                            $empname = (isset($val['status'])) && $val['status'] =="2" ? '  (Resigned)':'';
                            $name = $val['emp_name'].' '.$val['status'];
                            $desi = $val['designation'];
                            $dep = $val['department'];
                            $join = $val['joindate'];
                            $termination = $val['terminatedate'];
                            $branch = $val['branch_code'];
                            $month = $val['month_year'];
                            $leaverequest = $val['leavedays'];
                            $lossofpay = $val['loss_of_pay'];
                            $wrkday = $val['working_days'];
                            $monthlyctc = $val['monthly_ctc'];
                            $grosssal = $val['gross_salary'];
                            $ded = $val['total_deduction'];
                            $totalvar = $val['total_variables'];
                            $net = $val['net_salary'];
                            $verified = ($val['approved'] == 'N')?"No":"Yes";
                            $userid = $val['userid'];
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $userid);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $name);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $desi);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $dep);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $join);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $termination);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $branch);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $month);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $leaverequest);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $lossofpay);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $wrkday);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $monthlyctc);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $grosssal);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(14))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $ded);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(15))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, $totalvar);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(16))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, $net);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(17))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . $rowcount, $verified);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(18))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                            $rowcount++;
                            $i++;
                        }
                    } else {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                        //no repotr found
                    }
                }  } $objPHPExcel->getActiveSheet()->setTitle('Payroll Summary Report');
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
                $this->render('payrolsummaryreports');
                break;
        }
    }

    // ctc summary report



    private function generatesalaryreport($mode) {
         $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');

        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            //    debug($arr_leavepolicygroupids);
        
            if($str_criteria_item == ''){
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
 $condition = "and ed.status='1'";
//        debug($arr_form_data);
        if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1')
        {
            $condition = "and ed.status in ('1','2') ";
        }
        $arr_leavepolicydetails_for_template = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                if ($arr_form_data['select-criteria1'] == 'Units') {
                    $arr_empleaverequests = $this->EmpCtcTransaction->query("select ep.emp_branch,user_credentials.user_id,ep.joining_date,ep.emp_dept,br.branch_name,ed.first_name,ed.last_name,ed.status,ectc.*,termination.last_approved_working_date,uc.user_id,desig.desig_name from emp_ctc_transaction as ectc "
                            . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)"
                            . "left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)"
                            . "left join  user_credentials as uc on (ed.emp_pkey = uc.emp_fkey) "
                            . "left join designation as desig on (ep.designation = desig.desig_code and desig.status = 1)"
                            . "left join termination as termination on (termination.emp_fkey = ep.emp_fkey)"
                            . "left join branches as br on (br.branch_code = ep.emp_branch)"
                            . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = ep.emp_fkey)"
                            . " where br.branch_code = '$leavepolicygroupid' "
                            . $condition
                            . "and end_date_effective is null ");
                } else if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $arr_empleaverequests = $this->EmpCtcTransaction->query("select ed.first_name,  "
                            . "ed.last_name,ed.status,ectc.*,user_credentials.user_id,uc.user_id,termination.last_approved_working_date,department.dept_name,desig.desig_name,br.branch_name,ep.joining_date,ep.emp_dept,ep.emp_company_id "
                            . "from emp_ctc_transaction as ectc "
                            . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)"
                            . "left join  user_credentials as uc on (ed.emp_pkey = uc.emp_fkey) "
                            . "left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)"
                            . "left join termination as termination on (termination.emp_fkey = ep.emp_fkey)"
                            . "left join department as department on (department.dept_code = ep.emp_dept)"
                            . "left join branches as br on (br.branch_code = ep.emp_branch)"
                            . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = ep.emp_fkey)"
                            . "left join designation as desig on (ep.designation = desig.desig_code and desig.status = 1)"
                            . " where ectc.emp_fkey = '$leavepolicygroupid' "
                            . "$condition and end_date_effective is null ");
                }

                  //  debug($arr_empleaverequests);    

                $arr_leavepolicydetails_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_empleaverequests,
                        // 'employees'=>$arr_leavepolicy_employees
                );
            }
        }
        //debug($arr_leavepolicydetails_for_template);
        foreach ($arr_leavepolicydetails_for_template as $key => $value) {
            
        }
        // debug($resp_register);
        // debug($arr_leavepolicydetails_for_template);  
        //  debug($arr_leavepolicydetails_for_template);
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);

        //Set informations needed for report
        /* report header */
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        /* report header */
        switch ($mode) {
            case 'pdf' :

                // echo "hlo";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportsalary');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('CTCsummaryreport.pdf', 'D');
                $this->render('reportsalary');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . " CTC Summary Report.xlsx" : " CTC Summary Report" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "CTC Summary Report");
                $worksheet->mergeCells('A1:G1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'J'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }

                $rowcount = 2;
                $i = 0;
            foreach ($arr_leavepolicydetails_for_template as $value) {
                        foreach ($value as $valuees) {
//                             debug($valuees);
                             if (!empty($valuees)) {
                                 $i++;
                             }
                             }
                             }
                             if($i <= '0'){ 
                                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'There Is No Data Under This Criteria');
                             }
                     else {
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'User ID');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Employee Name');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Department');
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Date of Joining');
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Date of Termination');
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Monthly CTC');
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Annual CTC');
                for ($i = 0; $i <= 9; $i++) {
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, $rowcount)->getFont()->setBold(true);
                }
                $rowcount = $rowcount + 1;
                $k = 0;
                $sum = 0;
                $auualgross = 0;
                $monthgross= 0;
                $i = 0;
                foreach ($arr_leavepolicydetails_for_template as $value) {
                    $i += 1;


                    if (count($value['summary'])) {

                        $arr_data = $value['summary'];

                        foreach ($arr_data as $val) {
                            $k++;
                            //$totel = $val['ectc']['emp_derived_anualctc'] * 12;
                            $empstatus = (isset($val['ed']['status'])) && $val['ed']['status'] =="2" ? '  (Resigned)':'';
                            $totel = round($val['ectc']['emp_derived_anualctc']);
                            $auualgross = $auualgross + $val['ectc']['emp_derived_anualctc']*12;
                            $monthgross = $monthgross + $totel;
                            $sum = $sum + $val['ectc']['emp_derived_anualctc']*12;
                            $name = $val['ed']['first_name'] . ' ' . $val['ed']['last_name'].$empstatus;
                            $ctc = $val['ectc']['emp_anual_ctc'];
                            $dep = $val['department']['dept_name'];
                            $join = $val['ep']['joining_date'];
                            $termination = $val['termination']['last_approved_working_date'];
                            $id = $val['ep']['emp_company_id'];
                            $brnch = $val['br']['branch_name'];
                            $des = $val['desig']['desig_name'];
                            $ctc = $val['ectc']['emp_derived_anualctc']*12;
                            $userid = isset($val['user_credentials']['user_id']) ? $val['user_credentials']['user_id'] : '';
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $k);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $userid);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $brnch);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $des);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $dep);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $join);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $termination);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $totel);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $ctc);

                            $rowcount++;
                        }
                    }
                    //$rowcount++;
                }

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Total');
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $monthgross);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $sum);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->setTitle('CTC Summary Report');
                     }
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
                $this->render('reportsalary');
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

}
