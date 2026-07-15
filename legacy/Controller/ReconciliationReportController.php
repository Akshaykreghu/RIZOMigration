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
class ReconciliationReportController extends AppController {
 /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'ReconciliationReport';
    public $datatable;
/**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'UserCredentials','Section', 'EmployeeDetails', 'EmployeeProfessionalDetails','EmployeeGrossDetails','Units', 'ReportCriterias','DbConfig', 'ReportAudit');
    public $components = array('MasterdataManagement');

    public function hrreports() {
       $arr_reporttypes = array(
                'Reconciliation' => 'Reconciliation Report',
                'Reconc_section' => 'Reconciliation Report - Section'
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
                case 'Reconciliation':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Reconc_section':
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
                $conditions[] = "status = '1' or status = '2' ";
                $model = 'Banks';
                //$arr_criteriaItemsDB = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("DISTINCT bank_name"), "conditions" => $conditions)));
				$arr_criteriaItemsDB1 = $this->EmployeeDetails->query("SELECT distinct ifnull(SUBSTRING_INDEX(bank_details,',',1),bank_name) bank_name FROM payroll_master pm ,emp_details where emp_details.emp_pkey =pm.emp_fkey order by bank_name asc");
				$arr_criteriaItemsDB= array();
				foreach($arr_criteriaItemsDB1 as $val){
					if($val['0']['bank_name'] != ''){
                    $arr_criteriaItemsDB[]= $val['0'];
					}
				}
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
                case 'Section':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['id'];
                        $arr_criteriaItems[$key]['text'] = $value['section_name'];
                        $key++;
                    }
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

    public function downloadHistory($type, $mode) {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'Reconciliation':
                $dataForHistory['report_type'] = "Reconciliation Report";
                break;
            case 'Reconc_section':
                $dataForHistory['report_type'] = "Reconciliation Section Report";
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
                case 'attendance': $criteria_name_array[] = 'belonging to an Attendance';
                    break;
                case 'Section': $criteria_name_array[] = 'belonging to a Section';
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

    public function generatereport($type = '', $mode = '') {
        $this->autoRender = false;
        switch ($type) {
            case 'Reconciliation':
                //Reconciliation report
                $this->generateReconciliationReport($mode);
                break;
            case 'Reconc_section':
                //Reconciliation section report
                $this->generateReconciliationSectionReport($mode);
                break;
            default:
                return false;
                break;
        }

        $this->downloadHistory($type, $mode);
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

   
    private function generateReconciliationReport($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
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
              $yrdata= strtotime($from);
              $period = date('M-Y', $yrdata);
              $this->set('period', $period);
                $additions = $this->EmployeeDetails->query("select trim(salhead.item)item,salhead.salary_head_item_pkey
                    from emp_salary_slip as ectc 
                    left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                    where ectc.salary_amount != '0' and ectc.head_operator = 'ADDITION' and ectc.month_year ='$from' and ectc.salary_head_item_desc not in ('ESI','PF','WWF','LWF')
                    and end_date_effective is null group by ectc.salary_head_item_desc"); 

                $deductions = $this->EmployeeDetails->query("select trim(ifnull(salhead.item,salary_head_item_desc))item,ifnull(salhead.salary_head_item_pkey,0) salary_head_item_pkey
                    from emp_salary_slip as ectc 
                    left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                    where ectc.salary_amount != '0' and ectc.salary_head_item_fkey != '0' and ectc.head_operator = 'DEDUCTION' and ectc.month_year ='$from' 
                    and end_date_effective is null group by ectc.salary_head_item_desc");
                $this->set('deductions', $deductions);
                $this->set('additions', $additions);
                $arr_div = $this->EmployeeDetails->query("select div_name from division where status='1'");
                $this->set('arr_div', $arr_div);
            $condition = "and ed.status = '1'  ";
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and ed.status in ('1','2') ";
        }
       $arr_salary_for_template = array();
       $arr_salary_for_template1 = array();
       $arr_salary_for_template2 = array();
       $arr_salary_for_template3 = array();
       $arr_salary_for_template4 = array();
       $arr_salary_for_template5 = array();
       $arr_salary_for_template6 = array();
        if ($arr_leavepolicygroupids != '') {
            if($str_criteria_item == 'Units'){
                
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                
                $arr_requests = $this->EmployeeDetails->query("select divn.div_name,ectc.head_operator,br.branch_code,br.branch_name,COALESCE(trim(head.head_desc),'Other')head_desc,
                    trim(ectc.salary_head_item_desc)salary_head_item_desc,ectc.salary_head_item_fkey,sum(ectc.salary_amount)totalamount,count(distinct ed.emp_pkey)vcount
                    from emp_salary_slip as ectc 
                    left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                    left join salary_heads as head on (salhead.head_fkey= head.head_pkey)
                    where ectc.salary_amount != '0' and payroll_master.branch_code= '$leavepolicygroupid' and ectc.month_year ='$from' 
                    and end_date_effective is null and payroll_master.action in ('Approved','Processed') 
                    group by divn.div_name,head.head_desc,ectc.salary_head_item_desc"); 
                
                $arr_banks = $this->EmployeeDetails->query("select divn.div_name,ed.bank_name,payroll_master.branch_code,
                    sum(payroll_master.net_salary)totalamount,count(distinct ed.emp_pkey)vcount,payroll_master.action 
                    from emp_details as ed
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join payroll_master  on (payroll_master.emp_fkey = ed.emp_pkey and payroll_master.month_year = '$from') 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where payroll_master.net_salary!= '0'  and payroll_master.month_year ='$from' and payroll_master.branch_code='$leavepolicygroupid' 
                    and payroll_master.action in ('Approved','Processed') and ed.bank_name is not null and ed.bank_name != ''
                    group by ed.bank_name,divn.div_name"); 
                $arr_bank_data = $this->EmployeeDetails->query("select divn.div_name,ed.bank_name,payroll_master.branch_code,
                    sum(payroll_master.net_salary)totalamount,count(distinct ed.emp_pkey)vcount,payroll_master.action 
                    from emp_details as ed
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join payroll_master  on (payroll_master.emp_fkey = ed.emp_pkey and payroll_master.month_year = '$from') 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where payroll_master.net_salary!= '0'  and payroll_master.month_year ='$from' and payroll_master.branch_code='$leavepolicygroupid' 
                    and payroll_master.action in ('Approved','Processed') and (ed.bank_name is null or ed.bank_name ='')  
                    group by divn.div_name"); 
                $arr_bank_hold = $this->EmployeeDetails->query("select divn.div_name,payroll_master.branch_code,
                    sum(payroll_master.net_salary)totalamount,count(distinct ed.emp_pkey)vcount,payroll_master.action 
                    from emp_details as ed
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join payroll_master  on (payroll_master.emp_fkey = ed.emp_pkey and payroll_master.month_year = '$from') 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where payroll_master.net_salary!= '0'  and payroll_master.month_year ='$from' and payroll_master.branch_code='$leavepolicygroupid' 
                    and payroll_master.action in ('Approved','Processed')   
                    group by divn.div_name,payroll_master.action");
                $arr_esi = $this->EmployeeDetails->query("select divn.div_name,ectc.head_operator,payroll_master.branch_code,
                    trim(ectc.salary_head_item_desc)salary_head_item_desc,ectc.salary_head_item_fkey,sum(ectc.salary_amount)totalamount,count(distinct ed.emp_pkey)vcount
                    from emp_salary_slip as ectc 
                    left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                    left join salary_heads as head on (salhead.head_fkey= head.head_pkey)
                    where ectc.salary_amount != '0' and payroll_master.branch_code= '$leavepolicygroupid' and ectc.month_year ='$from' and ectc.head_operator = 'ADDITION' 
                    and end_date_effective is null and payroll_master.action in ('Approved','Processed') and ectc.salary_head_item_desc in ('ESI','PF','LWF','WWF')
                    group by ectc.salary_head_item_desc,ectc.head_operator,divn.div_name"); 

                $arr_loan = $this->EmployeeDetails->query("select divn.div_name,ed.branch_code,
                    sum(ectc.salary_amount)totalamount,count(distinct ed.emp_pkey)vcount
                    from emp_salary_slip as ectc 
                    left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where ed.branch_code= '$leavepolicygroupid'  and ectc.month_year ='$from' and end_date_effective is null 
                    and ectc.salary_head_item_desc = 'Loan' group by divn.div_name");
                $arr_advance = $this->EmployeeDetails->query("select divn.div_name,ed.branch_code,
                    sum(ectc.salary_amount)totalamount,count(distinct ed.emp_pkey)vcount
                    from emp_salary_slip as ectc 
                    left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where ed.branch_code= '$leavepolicygroupid'  and ectc.month_year ='$from' and end_date_effective is null 
                    and ectc.salary_head_item_desc = 'Salary Advance' group by divn.div_name");
		if(!empty($arr_requests)){
                $arr_salary_for_template[] = array(
                    'summary' => $arr_requests,
//                    'bank' => $arr_banks,
//                    'esi' => $arr_esi,
//                    'advance' => $arr_advance,
//                    'loan' => $arr_loan,
//                    'bank' => $arr_bank_data
                );
                }
                if(!empty($arr_banks)){
                $arr_salary_for_template1[$leavepolicygroupid][] = array(
                    'bank' => $arr_banks
                );
                }
                if(!empty($arr_esi)){
                $arr_salary_for_template2[$leavepolicygroupid][] = array(
                    'esi' => $arr_esi
                );
                }
                if(!empty($arr_advance)){
                $arr_salary_for_template3[$leavepolicygroupid][] = array(
                    'advance' => $arr_advance
                );
                }
                if(!empty($arr_loan)){
                $arr_salary_for_template4[$leavepolicygroupid][] = array(
                    'loan' => $arr_loan
                );
                }
                if(!empty($arr_bank_data)){
                $arr_salary_for_template5[$leavepolicygroupid][] = array(
                    'bank' => $arr_bank_data
                );
                }
                if(!empty($arr_bank_hold)){
                $arr_salary_for_template6[$leavepolicygroupid][] = array(
                    'bank' => $arr_bank_hold
                );
                }
            }
              }else{
                  foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                
                $arr_requests = $this->EmployeeDetails->query("select section.section_name,divn.div_name,ectc.head_operator,br.branch_code,br.branch_name,COALESCE(trim(head.head_desc),'Other')head_desc,
                    trim(ectc.salary_head_item_desc)salary_head_item_desc,ectc.salary_head_item_fkey,sum(ectc.salary_amount)totalamount,count(distinct ed.emp_pkey)vcount
                    from emp_salary_slip as ectc 
                    left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    left join section on (section.id = ep.emp_sep_priv)
                    left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                    left join salary_heads as head on (salhead.head_fkey= head.head_pkey)
                    where ectc.salary_amount != '0' and ep.emp_sep_priv= '$leavepolicygroupid' and ectc.month_year ='$from' 
                    and end_date_effective is null and payroll_master.action in ('Approved','Processed') 
                    group by divn.div_name,head.head_desc,ectc.salary_head_item_desc"); 
                
                $arr_banks = $this->EmployeeDetails->query("select divn.div_name,ed.bank_name,payroll_master.branch_code,
                    sum(payroll_master.net_salary)totalamount,count(distinct ed.emp_pkey)vcount,payroll_master.action 
                    from emp_details as ed
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join payroll_master  on (payroll_master.emp_fkey = ed.emp_pkey and payroll_master.month_year = '$from') 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where payroll_master.net_salary!= '0'  and payroll_master.month_year ='$from' and ep.emp_sep_priv='$leavepolicygroupid' 
                    and payroll_master.action in ('Approved','Processed') and ed.bank_name is not null and ed.bank_name != ''
                    group by ed.bank_name,divn.div_name"); 
                $arr_bank_data = $this->EmployeeDetails->query("select divn.div_name,ed.bank_name,payroll_master.branch_code,
                    sum(payroll_master.net_salary)totalamount,count(distinct ed.emp_pkey)vcount,payroll_master.action 
                    from emp_details as ed
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join payroll_master  on (payroll_master.emp_fkey = ed.emp_pkey and payroll_master.month_year = '$from') 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where payroll_master.net_salary!= '0'  and payroll_master.month_year ='$from' and ep.emp_sep_priv='$leavepolicygroupid' 
                    and payroll_master.action in ('Approved','Processed') and (ed.bank_name is null or ed.bank_name ='')  
                    group by divn.div_name"); 
                $arr_bank_hold = $this->EmployeeDetails->query("select divn.div_name,payroll_master.branch_code,
                    sum(payroll_master.net_salary)totalamount,count(distinct ed.emp_pkey)vcount,payroll_master.action 
                    from emp_details as ed
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join payroll_master  on (payroll_master.emp_fkey = ed.emp_pkey and payroll_master.month_year = '$from') 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where payroll_master.net_salary!= '0'  and payroll_master.month_year ='$from' and ep.emp_sep_priv='$leavepolicygroupid' 
                    and payroll_master.action in ('Approved','Processed')   
                    group by divn.div_name,payroll_master.action");
                $arr_esi = $this->EmployeeDetails->query("select divn.div_name,ectc.head_operator,payroll_master.branch_code,
                    trim(ectc.salary_head_item_desc)salary_head_item_desc,ectc.salary_head_item_fkey,sum(ectc.salary_amount)totalamount,count(distinct ed.emp_pkey)vcount
                    from emp_salary_slip as ectc 
                    left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                    left join salary_heads as head on (salhead.head_fkey= head.head_pkey)
                    where ectc.salary_amount != '0' and ep.emp_sep_priv= '$leavepolicygroupid' and ectc.month_year ='$from' and ectc.head_operator = 'ADDITION' 
                    and end_date_effective is null and payroll_master.action in ('Approved','Processed') and ectc.salary_head_item_desc in ('ESI','PF','LWF','WWF')
                    group by ectc.salary_head_item_desc,ectc.head_operator,divn.div_name"); 

                $arr_loan = $this->EmployeeDetails->query("select divn.div_name,ed.branch_code,
                    sum(ectc.salary_amount)totalamount,count(distinct ed.emp_pkey)vcount
                    from emp_salary_slip as ectc 
                    left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where ep.emp_sep_priv= '$leavepolicygroupid'  and ectc.month_year ='$from' and end_date_effective is null 
                    and ectc.salary_head_item_desc = 'Loan' group by divn.div_name");
                $arr_advance = $this->EmployeeDetails->query("select divn.div_name,ed.branch_code,
                    sum(ectc.salary_amount)totalamount,count(distinct ed.emp_pkey)vcount
                    from emp_salary_slip as ectc 
                    left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where ep.emp_sep_priv = '$leavepolicygroupid'  and ectc.month_year ='$from' and end_date_effective is null 
                    and ectc.salary_head_item_desc = 'Salary Advance' group by divn.div_name");
		if(!empty($arr_requests)){
                $arr_salary_for_template[] = array(
                    'summary' => $arr_requests,
//                    'bank' => $arr_banks,
//                    'esi' => $arr_esi,
//                    'advance' => $arr_advance,
//                    'loan' => $arr_loan,
//                    'bank' => $arr_bank_data
                );
                }
                if(!empty($arr_banks)){
                $arr_salary_for_template1[$leavepolicygroupid][] = array(
                    'bank' => $arr_banks
                );
                }
                if(!empty($arr_esi)){
                $arr_salary_for_template2[$leavepolicygroupid][] = array(
                    'esi' => $arr_esi
                );
                }
                if(!empty($arr_advance)){
                $arr_salary_for_template3[$leavepolicygroupid][] = array(
                    'advance' => $arr_advance
                );
                }
                if(!empty($arr_loan)){
                $arr_salary_for_template4[$leavepolicygroupid][] = array(
                    'loan' => $arr_loan
                );
                }
                if(!empty($arr_bank_data)){
                $arr_salary_for_template5[$leavepolicygroupid][] = array(
                    'bank' => $arr_bank_data
                );
                }
                if(!empty($arr_bank_hold)){
                $arr_salary_for_template6[$leavepolicygroupid][] = array(
                    'bank' => $arr_bank_hold
                );
                }
            }
              
            }
          //      $alphabet = range('A', 'Z'); debug($alphabet);
//                            $start_letter = 1;
//                            $countdiv = count($arr_div);
//                            $countsec = count($arr_section);
//                            for ($i = 0; $i < $countsec ; $i++) {
//                              debug($alphabet[$start_letter]); 
//                              debug($alphabet[$start_letter] + $alphabet[$countdiv]);
//                            }
          // debug($arr_salary_for_template4);
            $this->set('arr_salary_for_template', $arr_salary_for_template);
            $this->set('arr_salary_for_bank', $arr_salary_for_template1);
            $this->set('arr_salary_for_esi', $arr_salary_for_template2);
            $this->set('arr_salary_for_advance', $arr_salary_for_template3);
            $this->set('arr_salary_for_loan', $arr_salary_for_template4);
            $this->set('arr_salary_for_bank_data', $arr_salary_for_template5);
            $this->set('arr_salary_for_bank_hold', $arr_salary_for_template6);
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
                    $view_output = $view->render('reconciliation');
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('reconciliation' . $from . '.pdf', 'D');
                    $this->render('reconciliation');
                    break;
                case 'excel' :
                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_Salaryslip" . $from . ".xlsx" : "SalarySlip" . strtotime() . ".xlsx";

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

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Salary Slip Report");
                    $worksheet->mergeCells('A1:D1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    $rowcount = 2;
                    $col = 0;

                    $worksheet->setCellValueByColumnAndRow(0, 2, $str_company_code);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setSize(14);
                    $worksheet->mergeCells('A2:D2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );


                    $worksheet->setCellValueByColumnAndRow(0, 3, "Month - " . $from);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount + 1))->getFont()->setSize(14);
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
                            $worksheet->mergeCells('A' . $rowcount . ':D' . $rowcount);
//                        $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
//                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                    );
                            $empstatus = isset($value['summary']['0']['ed']['status']) && $value['summary']['0']['ed']['status'] == "2" ? '  (Resigned)' : '';
                            $emp_name = isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] . " " . $value['summary']['0']['ed']['last_name'] : '';
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Salary Slip - : ' . $emp_name . $empstatus);
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
                            $weekoff = isset($value['summary']['0']['ar']['weekoff_total']) ? $value['summary']['0']['ar']['weekoff_total'] : '';
                            $holiday = isset($value['summary']['0']['ar']['holiday_total']) ? $value['summary']['0']['ar']['holiday_total'] : '';
                            $employee_id = isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : '';
                            //edited by megha on 9_7_19 date format changed
                            $join = isset($value['empdet']['0']['ep']['joining_date']) ? date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';
                            //$join = isset($value['empdet']['0']['ep']['joining_date']) ? $value['empdet']['0']['ep']['joining_date'] : '';
                            $termin = isset($value['empdet']['0']['tr']['last_approved_working_date']) ? $value['empdet']['0']['tr']['last_approved_working_date'] : '';
                            $userid = isset($value['empdet']['0']['user_credentials']['user_id']) ? $value['empdet']['0']['user_credentials']['user_id'] : '';
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Employee Name :');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+1, $rowcount, $emp_name);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Employee ID : ");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, $employee_id);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount, "User ID : ");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount, $userid);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 3, $rowcount)->getFont()->setBold(true);
                            //2nd row 
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 1, 'Branch Name :');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 1)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 1, $branch);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 1)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 1, "Designation :");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 1)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 1, $designat);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 3, $rowcount + 1)->getFont()->setBold(true);
                            //3rd row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 2, 'Department Name :');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 2)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 2, $department);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 2)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 2, 'Joining Date :');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 2)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 2, $join);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 3, $rowcount + 2)->getFont()->setBold(true);
                            //4th row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 3, 'Termination Date :');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 3)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 3, $termin);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 3)->getFont()->setBold(true);

//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+2, $rowcount+3, "Calender Days : ");
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2, $rowcount+3)->getFont()->setBold(true);
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+3, $rowcount+3, $calender_days);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+3)->getFont()->setBold(true);
//                        //5th row
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount+4, 'Working Days :');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount+4)->getFont()->setBold(true);
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+1, $rowcount+4, $wrktime);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+4)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 3, "Present Days :");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 3)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 3, $days_present);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 3, $rowcount + 3)->getFont()->setBold(true);
                            //5th row
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 4, 'Days on Leave :');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 4)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 4, $daysleave);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 4)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 4, "Loss off Pay :");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 4)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 4, $loss_offp);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 3, $rowcount + 4)->getFont()->setBold(true);

                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+4)->getFont()->setBold(true);
                            // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount+10), ' Stat Rule');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 5, 'Week Off :');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 5)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 5, $weekoff);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount + 5)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 5, "Holiday :");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 5)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 5, $holiday);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 3, $rowcount + 5)->getFont()->setBold(true);

                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+5)->getFont()->setBold(true);

                            $rowcount = $rowcount + 5;

                            $columncount = 0;

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount + 1), 'Salary Slip    ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, ($rowcount + 1))->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, ($rowcount + 1))->getFont()->setSize(14);
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
                                    $rate = round($val['ectc']['structure_det_value'],2);
                                    $amount = round($val['ectc']['salary_amount']);
                                    //edited by megha on 16/11/19 settlement amount 
                                    $settlement = $value['settle'];
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $salary);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $rate);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $amount);

                                    $rowcount++;
                                }
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), "Total");
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), round($sum));
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), round($dd));

                                $rowcount++;

                                $arr_withoutComponents = $value['withoutcomponent'];
                                if (count($arr_withoutComponents) > 0) {
                                    foreach ($arr_withoutComponents as $vals) {
                                        $tot = $tot + $vals['ectc']['structure_det_value'];
                                        $net = $net + $vals['ectc']['salary_amount'];
                                        $salary = $vals['ectc']['salary_head_item_desc'];
                                        $rate = round($vals['ectc']['structure_det_value'],2);
                                        $amount = round($vals['ectc']['salary_amount'],2);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $salary);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $rate);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $amount);
                                        $rowcount++;
                                    }
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), "Total");
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), round($tot,2));
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), round($net,2));
                                }
                                if ($value['summary']['0']['ed']['status'] == 2) {
                                    $rowcount++;
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "Settlement Amount");
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), round($settlement));
                                }
                                $rowcount++;
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), "Net Salary");
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), round($dd + $net + $settlement));

                                $pos = $rowcount;
                                $objPHPExcel->getActiveSheet()->getStyle('A' . $start . ':D' . $pos)->applyFromArray($BStyle);
                                $start = 0;
                                $pos = 0;
                            } else {
                                $msg = 'No employees found under this shift';
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $msg);
                            }

                            $rowcount++;
                        }
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
                    $this->render('reconciliation');
                    break;
            }
        }
    }

    private function generateReconciliationSectionReport($mode) {
        $arr_form_data = $_REQUEST;
        
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
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
              $yrdata= strtotime($from);
              $period = date('M-Y', $yrdata);
              $this->set('period', $period);
                $additions = $this->EmployeeDetails->query("select trim(salhead.item)item,salhead.salary_head_item_pkey
                    from emp_salary_slip as ectc 
                    left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                    where ectc.salary_amount != '0' and ectc.head_operator = 'ADDITION' and ectc.month_year ='$from' and ectc.salary_head_item_desc not in ('ESI','PF','WWF','LWF')
                    and end_date_effective is null group by ectc.salary_head_item_desc"); 

                $deductions = $this->EmployeeDetails->query("select trim(ifnull(salhead.item,salary_head_item_desc))item,ifnull(salhead.salary_head_item_pkey,0) salary_head_item_pkey
                    from emp_salary_slip as ectc 
                    left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                    where ectc.salary_amount != '0' and ectc.salary_head_item_fkey != '0' and ectc.head_operator = 'DEDUCTION' and ectc.month_year ='$from' 
                    and end_date_effective is null group by ectc.salary_head_item_desc");
                $this->set('deductions', $deductions);
                $this->set('additions', $additions);
                $arr_div = $this->EmployeeDetails->query("select div_name from division where status='1'");
                $this->set('arr_div', $arr_div);
                $arr_section = $this->EmployeeDetails->query("select section_name from section where status='1'");
                $this->set('arr_section', $arr_section);
            $condition = "and ed.status = '1'  ";
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and ed.status in ('1','2') ";
        }
       $arr_salary_for_template = array();
       $arr_salary_for_bank = array();
       $arr_salary_for_esi = array();
       $arr_salary_for_advance = array();
       $arr_salary_for_loan = array();
       $arr_salary_for_bank_data = array();
       $arr_salary_for_bank_hold = array();
      
        if ($arr_leavepolicygroupids != '') {
                
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                
                $arr_requests = $this->EmployeeDetails->query("select section.section_name,divn.div_name,ectc.head_operator,br.branch_code,br.branch_name,COALESCE(trim(head.head_desc),'Other')head_desc,
                    trim(ectc.salary_head_item_desc)salary_head_item_desc,ectc.salary_head_item_fkey,sum(ectc.salary_amount)totalamount,count(distinct ed.emp_pkey)vcount
                    from emp_salary_slip as ectc 
                    left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join section on (section.id = ep.emp_sep_priv)
                    left join payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                    left join salary_heads as head on (salhead.head_fkey= head.head_pkey)
                    where ectc.salary_amount != '0' and payroll_master.branch_code= '$leavepolicygroupid' and ectc.month_year ='$from' 
                    and section.section_name!= 'NULL' and divn.div_name!= 'NULL'
                    and end_date_effective is null and payroll_master.action in ('Approved','Processed') 
                    group by section.section_name,divn.div_name,head.head_desc,ectc.salary_head_item_desc"); 
                
                $arr_banks = $this->EmployeeDetails->query("select section.section_name,divn.div_name,ed.bank_name,payroll_master.branch_code,
                    sum(payroll_master.net_salary)totalamount,count(distinct ed.emp_pkey)vcount,payroll_master.action 
                    from emp_details as ed
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join section on (section.id = ep.emp_sep_priv)
                    left join payroll_master  on (payroll_master.emp_fkey = ed.emp_pkey and payroll_master.month_year = '$from') 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where payroll_master.net_salary!= '0'  and payroll_master.month_year ='$from' and payroll_master.branch_code='$leavepolicygroupid' 
                    and payroll_master.action in ('Approved','Processed') and ed.bank_name is not null and ed.bank_name != '' 
                    and section.section_name!= 'NULL' and divn.div_name!= 'NULL'
                    group by ed.bank_name,section.section_name,divn.div_name"); 
                $arr_bank_data = $this->EmployeeDetails->query("select section.section_name,divn.div_name,ed.bank_name,payroll_master.branch_code,
                    sum(payroll_master.net_salary)totalamount,count(distinct ed.emp_pkey)vcount,payroll_master.action 
                    from emp_details as ed
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join section on (section.id = ep.emp_sep_priv)
                    left join payroll_master  on (payroll_master.emp_fkey = ed.emp_pkey and payroll_master.month_year = '$from') 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where payroll_master.net_salary!= '0'  and payroll_master.month_year ='$from' and payroll_master.branch_code='$leavepolicygroupid' 
                    and payroll_master.action in ('Approved','Processed') and (ed.bank_name is null or ed.bank_name ='') 
                    and section.section_name!= 'NULL' and divn.div_name!= 'NULL'
                    group by section.section_name,divn.div_name"); 
                $arr_bank_hold = $this->EmployeeDetails->query("select section.section_name,divn.div_name,payroll_master.branch_code,
                    sum(payroll_master.net_salary)totalamount,count(distinct ed.emp_pkey)vcount,payroll_master.action 
                    from emp_details as ed
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join section on (section.id = ep.emp_sep_priv)
                    left join payroll_master  on (payroll_master.emp_fkey = ed.emp_pkey and payroll_master.month_year = '$from') 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where payroll_master.net_salary!= '0'  and payroll_master.month_year ='$from' and payroll_master.branch_code='$leavepolicygroupid' 
                    and payroll_master.action in ('Approved','Processed')   
                    and section.section_name!= 'NULL' and divn.div_name!= 'NULL'
                    group by section.section_name,divn.div_name,payroll_master.action");
                $arr_esi = $this->EmployeeDetails->query("select section.section_name,divn.div_name,ectc.head_operator,payroll_master.branch_code,
                    trim(ectc.salary_head_item_desc)salary_head_item_desc,ectc.salary_head_item_fkey,sum(ectc.salary_amount)totalamount,count(distinct ed.emp_pkey)vcount
                    from emp_salary_slip as ectc 
                    left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join section on (section.id = ep.emp_sep_priv)
                    left join payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join designation as desg on (desg.desig_code = ep.designation and desg.status = 1)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                    left join salary_heads as head on (salhead.head_fkey= head.head_pkey)
                    where ectc.salary_amount != '0' and payroll_master.branch_code= '$leavepolicygroupid' and ectc.month_year ='$from' and ectc.head_operator = 'ADDITION' 
                    and end_date_effective is null and payroll_master.action in ('Approved','Processed') 
                    and section.section_name!= 'NULL' and divn.div_name!= 'NULL'
                    and ectc.salary_head_item_desc in ('ESI','PF','LWF','WWF')
                    group by ectc.salary_head_item_desc,ectc.head_operator,section.section_name,divn.div_name"); 

                $arr_loan = $this->EmployeeDetails->query("select section.section_name,divn.div_name,ed.branch_code,
                    sum(ectc.salary_amount)totalamount,count(distinct ed.emp_pkey)vcount
                    from emp_salary_slip as ectc 
                    left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join section on (section.id = ep.emp_sep_priv)
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where ed.branch_code= '$leavepolicygroupid'  and ectc.month_year ='$from' and end_date_effective is null 
                    and section.section_name!= 'NULL' and divn.div_name!= 'NULL'
                    and ectc.salary_head_item_desc = 'Loan' group by section.section_name,divn.div_name");
                $arr_advance = $this->EmployeeDetails->query("select section.section_name,divn.div_name,ed.branch_code,
                    sum(ectc.salary_amount)totalamount,count(distinct ed.emp_pkey)vcount
                    from emp_salary_slip as ectc 
                    left join emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    left join section on (section.id = ep.emp_sep_priv)
                    left join branches as br on (br.branch_code = ep.emp_branch)
                    left join division as divn on (divn.id = ep.emp_vertical)
                    where ed.branch_code= '$leavepolicygroupid'  and ectc.month_year ='$from' and end_date_effective is null 
                    and section.section_name!= 'NULL' and divn.div_name!= 'NULL'
                    and ectc.salary_head_item_desc = 'Salary Advance' group by section.section_name,divn.div_name");
		if(!empty($arr_requests)){
                $arr_salary_for_template[] = array(
                    'summary' => $arr_requests,
//                    'bank' => $arr_banks,
//                    'esi' => $arr_esi,
//                    'advance' => $arr_advance,
//                    'loan' => $arr_loan,
//                    'bank' => $arr_bank_data
                );
                }
                if(!empty($arr_banks)){
                $arr_salary_for_bank[$leavepolicygroupid][] = array(
                    'bank' => $arr_banks
                );
                }
                if(!empty($arr_esi)){
                $arr_salary_for_esi[$leavepolicygroupid][] = array(
                    'esi' => $arr_esi
                );
                }
                if(!empty($arr_advance)){
                $arr_salary_for_advance[$leavepolicygroupid][] = array(
                    'advance' => $arr_advance
                );
                }
                if(!empty($arr_loan)){
                $arr_salary_for_loan[$leavepolicygroupid][] = array(
                    'loan' => $arr_loan
                );
                }
                if(!empty($arr_bank_data)){
                $arr_salary_for_bank_data[$leavepolicygroupid][] = array(
                    'bank' => $arr_bank_data
                );
                }
                if(!empty($arr_bank_hold)){
                $arr_salary_for_bank_hold[$leavepolicygroupid][] = array(
                    'bank' => $arr_bank_hold
                );
                }
            }
              
          //debug($arr_salary_for_template);
            $this->set('arr_salary_for_template', $arr_salary_for_template);
            $this->set('arr_salary_for_bank', $arr_salary_for_bank);
            $this->set('arr_salary_for_esi', $arr_salary_for_esi);
            $this->set('arr_salary_for_advance', $arr_salary_for_advance);
            $this->set('arr_salary_for_loan', $arr_salary_for_loan);
            $this->set('arr_salary_for_bank_data', $arr_salary_for_bank_data);
            $this->set('arr_salary_for_bank_hold', $arr_salary_for_bank_hold);
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
                    $view_output = $view->render('reconciliation_section');
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('reconciliation_section' . $from . '.pdf', 'D');
                    $this->render('reconciliation_section');
                    break;
                case 'excel' :
                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_Reconciliation" . $from . ".xlsx" : "Reconciliation" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Reconciliation Report By Greatleap");

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
                    //$worksheet->getColumnDimension('B')->setWidth(40);
                    //$worksheet->getColumnDimension('C')->setWidth(40);
                    //$worksheet->getColumnDimension('D')->setWidth(40);

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Reconciliation Report");
                    $worksheet->mergeCells('A1:L1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(17);
                    $rowcount = 2;
                   foreach ($arr_salary_for_template as $value) { 
                           $brch =isset($value['summary']['0']['br']['branch_code'])?$value['summary']['0']['br']['branch_code'] : '' ;
                           $data= isset($arr_salary_for_bank_data[$brch])?$arr_salary_for_bank_data[$brch]:'';
                           $esidata= isset($arr_salary_for_esi[$brch])?$arr_salary_for_esi[$brch]:'';
                           $bank= isset($arr_salary_for_bank[$brch])?$arr_salary_for_bank[$brch]:'';
                           $loan= isset($arr_salary_for_loan[$brch])?$arr_salary_for_loan[$brch]:'';
                           $advance= isset($arr_salary_for_advance[$brch])?$arr_salary_for_advance[$brch]:'';
                           $data_hold= isset($arr_salary_for_bank_hold[$brch])?$arr_salary_for_bank_hold[$brch]:'';
                    
                    $branchname = $value['summary']['0']['br']['branch_name'];
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Period - " . $period);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                    $worksheet->mergeCells('A'.$rowcount.':E'.$rowcount);
                    $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(5, $rowcount, "Branch - " . $branchname);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setSize(14);
                    $worksheet->mergeCells('F'.$rowcount.':L'.$rowcount);
                    $worksheet->getStyle('F'.$rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $columncount = 0; 
                            $rowcount++;
                            
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Section' );
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount))->getFont()->setSize(14);
                            $countdiv = count($arr_div);
                            foreach ($arr_section as $section) {
                            $name=$section['section']['section_name'];
                            
                            
                            //$worksheet->mergeCells('A' . $rowcount . ':A+2' . $rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $name);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount=$columncount+$countdiv;
                            }
                            
                            if($countdiv == 2){
                            $worksheet->mergeCells('B'.$rowcount.':C'.$rowcount);
                            $worksheet->mergeCells('D'.$rowcount.':E'.$rowcount);  
                            $worksheet->mergeCells('F'.$rowcount.':G'.$rowcount);  
                            }else if($countdiv == 3){
                            $worksheet->mergeCells('B'.$rowcount.':D'.$rowcount);
                            $worksheet->mergeCells('E'.$rowcount.':G'.$rowcount);
                            $worksheet->mergeCells('H'.$rowcount.':J'.$rowcount);
                            }else if($countdiv == 4){
                            $worksheet->mergeCells('B'.$rowcount.':E'.$rowcount);
                            $worksheet->mergeCells('F'.$rowcount.':I'.$rowcount);
                            $worksheet->mergeCells('J'.$rowcount.':M'.$rowcount);  
                            }
//                            $alphabet = range('A', 'Z');
//                            $start_letter = 1;
//                            $countdiv = count($arr_div);
//                            $countsec = count($arr_section);
//                            for ($i = 0; $i < $countsec ; $i++) {
//                              $objPHPExcel->getActiveSheet()->mergeCells($alphabet[$start_letter] . $rowcount . ':' . ($alphabet[$start_letter] + $alphabet[$countdiv]) . $rowcount);
//                            }
                            $columncount = 0;
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Division' );
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount))->getFont()->setSize(14);
                            
                            foreach ($arr_section as $section) {
                                
                                foreach ($arr_div as $div) {
                            $namediv=$div['division']['div_name'];
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $namediv);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            }}
                            
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, 'Count');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount, "Total");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            
                            $arr_items=array();
                                foreach($additions as $val){ 
                                    $columncount = 0;
                                    $count = 0; $subtotals = 0;
                                    $item= $val['0']['item'];
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $item);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            
                            foreach ($arr_section as $section) { $section1 =$section['section']['section_name'];
                                    foreach ($arr_div as $div) { $addtot=0;
                                    $div1 =$div['division']['div_name'];
                                    $arr_items[$section1][$div1][] = 0;
                                    $columncount++;
                                    foreach ($value as $key => $values) {
                                      foreach($values as $row){  
                                        if($section['section']['section_name'] == $row['section']['section_name'] && $div['division']['div_name'] == $row['divn']['div_name'] && $val['salhead']['salary_head_item_pkey'] == $row['ectc']['salary_head_item_fkey']) {
                                    $addtot += round($row['0']['totalamount']);
                                    $count += $row['0']['vcount'];
                                    $subtotals += $row['0']['totalamount'];
                                    $arr_items[$section1][$div1][] = $row['0']['totalamount'];
                                        } } } 
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $addtot);
                           // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            }}
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($count));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($subtotals));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                                }
                                $columncount = 0;
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Gross Salary");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $grdtotals = 0;
                            foreach ($arr_section as $section) { 
                                              foreach ($arr_div as $div) { $grtotals = 0; $columncount++;
                                              foreach ($value as $key => $values) { 
                          foreach($values as $row){ 
                           if($section['section']['section_name'] == $row['section']['section_name'] && $div['division']['div_name'] == $row['divn']['div_name'] && $row['ectc']['head_operator'] == 'Addition' 
                            && $row['0']['salary_head_item_desc'] !='ESI' && $row['0']['salary_head_item_desc'] !='PF' && $row['0']['salary_head_item_desc'] !='LWF'
                                                && $row['0']['salary_head_item_desc'] !='WWF') {
                                              $grtotals += round($row['0']['totalamount']);
                                              $grdtotals += round($row['0']['totalamount']);
                                        }}} 
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($grtotals));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            } }
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, '');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($grdtotals));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount = 0;
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "DEDUCTIONS");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            
                            $rowcount++;
                            $grdtotals = 0;
                            foreach($deductions as $val){ 
                                    $count1 = 0; $subtotals1 = 0; $columncount = 0;
                                    $ditem= $val['0']['item'];
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $ditem);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            foreach ($arr_section as $section) { $section1 =$section['section']['section_name'];
                                   foreach ($arr_div as $div) { 
                                   $div1 =$div['division']['div_name']; $totalded =0;
                                    foreach ($value as $key => $values) {
                                        foreach($values as $row){
                                        if($section['section']['section_name'] == $row['section']['section_name'] && $div['division']['div_name'] == $row['divn']['div_name'] && $val['0']['salary_head_item_pkey'] == $row['ectc']['salary_head_item_fkey']) {
                                            if($row['0']['salary_head_item_desc'] =='LWF'){
                                            $totalded = abs($row['0']['totalamount']);
                                            }else{
                                            $totalded = abs(round($row['0']['totalamount']));    
                                            }
                                    $count1 += $row['0']['vcount'];
                                    $subtotals1 += abs($row['0']['totalamount']);
                                    $arr_items[$section1][$div1][] = $row['0']['totalamount'];
                            }}}  
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $totalded);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++; 
                            }}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($count1));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($subtotals1));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            }
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "TOTAL DEDUCTIONS");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $grdtotals1 = 0;
                            foreach ($arr_section as $section) { 
                                foreach ($arr_div as $div) {  $grtotals1 = 0;
                                  foreach ($value as $key => $values) {
                                    foreach($values as $row){ 
                                if($section['section']['section_name'] == $row['section']['section_name'] && $div['division']['div_name'] == $row['divn']['div_name'] && $row['ectc']['head_operator'] == 'Deduction') {
                                              $grtotals1 += abs($row['0']['totalamount']);
                                              $grdtotals1 += abs($row['0']['totalamount']);
                                  }} }
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($grtotals1));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            }}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, '');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($grdtotals1));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount = 0;
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "LOAN & ADVANCE");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Salary Advance");
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $adsum = 0; $adv = 0; $advcnt = 0;
                                         foreach ($arr_section as $section) { $section1 =$section['section']['section_name'];
                                         foreach ($arr_div as $div) { $adv = 0; $div1 =$div['division']['div_name'];
                                         if(!empty($advance)){
                                         foreach ($advance['0']['advance'] as $ad) {
                                            if($section['section']['section_name'] == $ad['section']['section_name'] && $div['division']['div_name'] == $ad['divn']['div_name']) { 
                                                $adv = abs(round($ad['0']['totalamount'])); 
                                                $adsum += $adv ; 
                                                $advcnt += $ad['0']['vcount'];
                                                $arr_items[$section1][$div1][] = round($ad['0']['totalamount']);
                                         } } } 
                          $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $adv);
                          //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                  $columncount++;       }}
                          $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $advcnt);
                          //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                          $columncount++;
                          $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($adsum));
                          //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                          $rowcount++;
                          $columncount = 0;
                          $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Employee Loan");
                          //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                          $lnsum = 0; $loancnt = 0;
                          $columncount++;
                                         foreach ($arr_section as $section) { $section1 =$section['section']['section_name'];
                                         foreach ($arr_div as $div) {  $ln = 0; 
                                         $div1 =$div['division']['div_name'];
                                         if(!empty($loan)){
                                         foreach ($loan['0']['loan'] as $lon) { 
                                            if($section['section']['section_name'] == $lon['section']['section_name'] && $div['division']['div_name'] == $lon['divn']['div_name'] ) {  
                                                $ln = abs(round($lon['0']['totalamount']));
                                         $lnsum += $ln; 
                                         $loancnt += $lon['0']['vcount'];
                                         $arr_items[$section1][$div1][] = round($lon['0']['totalamount']);
                                         } } } 
                          $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $ln);
                          //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                          $columncount++;       }}
                          $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $advcnt);
                          //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                          $columncount++;
                          $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($adsum));
                          //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                          $rowcount++;
                          $columncount = 0;
                          $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Total");
                          $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                          $columncount++;
                          $grndsum1 = 0;
                                            foreach ($arr_section as $section) { 
                                            foreach ($arr_div as $div) { $grndsum = 0;
                                            if(!empty($loan)){
                                            foreach ($loan['0']['loan'] as $lon) { 
                                            if($section['section']['section_name'] == $lon['section']['section_name'] && $div['division']['div_name'] == $lon['divn']['div_name'] ) {  
                                                $grndsum += abs($lon['0']['totalamount']);
                                                $grndsum1 += abs($lon['0']['totalamount']);
                                            }} }
                                            if(!empty($advance)){
                                            foreach ($advance['0']['advance'] as $ad) {
                                            if($section['section']['section_name'] == $ad['section']['section_name'] && $div['division']['div_name'] == $ad['divn']['div_name']) { 
                                                $grndsum += abs($ad['0']['totalamount']);
                                                $grndsum1 += abs($ad['0']['totalamount']);
                                            }}} 
                                            
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, abs(round($grndsum)));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                                            }}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, '');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $loansum=round($lnsum+$adsum);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $loansum);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "NET SALARY");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++; 
                            $grand = 0; 
                                            foreach ($arr_section as $section) { $section1 =$section['section']['section_name'];
                                            foreach ($arr_div as $div) { 
                                            $div1 =$div['division']['div_name'];
                                            $subtot = array_sum($arr_items[$section1][$div1]); 
                                            $grand += $subtot;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($subtot));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                              $columncount++;              } }
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, '');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $loansum=round($lnsum+$adsum);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($grand));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "BANK DETAILS");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            
                            $grsum = 0; $arr_bank = array(); 
                            if(!empty($bank)){ $rowcount++;
                                        foreach ($bank['0']['bank'] as $bk) { $sum = 0; $columncount = 0;
                                            if($bk['ed']['bank_name'] !== ''){ 
                                                $sum += abs(round($bk['0']['totalamount']));
                                              $grsum += abs(round($bk['0']['totalamount']));
                                              $bankname = $bk['ed']['bank_name'];
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $bankname);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            foreach ($arr_section as $section) { $section1 =$section['section']['section_name'];
                                       foreach ($arr_div as $div) { $div1 =$div['division']['div_name'];
                                        $arr_bank[$section1][$div1][] = 0;
                                            if($section['section']['section_name'] == $bk['section']['section_name'] && $div['division']['div_name'] == $bk['divn']['div_name']) { 
                                                $arr_bank[$section1][$div1][] = abs($bk['0']['totalamount']); 
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, abs(round($bk['0']['totalamount'])));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                         } else{ 
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, '0');
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                             } $columncount++;
                             } }
                             $bkcount= $bk['0']['vcount'];
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $bkcount);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($sum));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            } $rowcount++;}}
                            
                            if(!empty($data)){
                                $rowcount++;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'CASH');
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $amttot = 0; $cn=0;
                                        foreach ($arr_section as $section) { $section1 =$section['section']['section_name'];
                                        foreach ($arr_div as $div) {  $amt = 0; $arr_bank[$section1][$div1][] = 0;
                                        if(!empty($data)){
                                            foreach ($data['0']['bank'] as $bk) { 
                                        $div1 =$div['division']['div_name'];
                                        
                                            if($section['section']['section_name'] == $bk['section']['section_name'] && $div['division']['div_name'] == $bk['divn']['div_name']) { 
                                                $arr_bank[$section1][$div1][] = abs($bk['0']['totalamount']); 
                                                $grsum += abs(round($bk['0']['totalamount']));
                                                $amttot += abs(round($bk['0']['totalamount']));
                                            $amt = isset($bk['0']['totalamount'])?$bk['0']['totalamount']:'';
                                            $cn +=$bk['0']['vcount'];
                                         } } }
                           
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, abs(round($amt)));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                  $columncount++;       }}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $cn);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $amttot);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $rowcount++;
                            }
                            
                           // $rowcount++;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Salary Hold');
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $cntt=0; 
                                        foreach ($arr_section as $section) { 
                                            foreach ($arr_div as $div) { $hold = 0;
                                            if(!empty($data_hold)){
                                                foreach ($data_hold['0']['bank'] as $bk) {  
                                            if($section['section']['section_name'] == $bk['section']['section_name'] && $div['division']['div_name'] == $bk['divn']['div_name'] && $bk['payroll_master']['action'] == 'Processed') {
                                                $hold += abs(round($bk['0']['totalamount'])); 
                                                $cntt +=$bk['0']['vcount'];
                                            } }}
                            
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($hold));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                $columncount++;        }}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $cntt);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($grsum));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'GRAND TOTAL');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++; $grand = 0; 
                                            foreach ($arr_section as $section) { $section1 =$section['section']['section_name'];
                                            foreach ($arr_div as $div) { 
                                            $div1 =$div['division']['div_name'];
                                            $subtot = isset($arr_bank[$section1][$div1])?array_sum($arr_bank[$section1][$div1]):''; 
                                            $grand += $subtot;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($subtot));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                            $columncount++; }}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, '');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($grand));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'EPF AND ESI Summary');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'ESI');
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $cnt=0; $totalsum=0; $arr_total = array();
                                            foreach ($arr_section as $section) { $section1 =$section['section']['section_name'];
                                            foreach ($arr_div as $div) { $esisum = 0; $div1 =$div['division']['div_name'];
                                            $arr_total[$section1][$div1][] = 0;
                                            if(!empty($esidata)){
                                            foreach ($esidata['0']['esi'] as $esi) { 
                                        if($esi['ectc']['head_operator'] == 'Addition' && $esi['0']['salary_head_item_desc'] == 'ESI' && $esi['divn']['div_name'] == $div['division']['div_name'] && $section['section']['section_name'] == $esi['section']['section_name']){  
                                            $esisum += $esi['0']['totalamount'];
                                            $totalsum += $esi['0']['totalamount'];
                                            $cnt += $esi['0']['vcount'];
                                            $arr_total[$section1][$div1][] = abs($esi['0']['totalamount']);
                                            }} }
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($esisum));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                                            }}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $cnt);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($totalsum));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'PF');
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $cnt=0;$totalsum=0; 
                                            foreach ($arr_section as $section) { $section1 =$section['section']['section_name'];
                                           foreach ($arr_div as $div) { $esisum = 0; $div1 =$div['division']['div_name'];
                                            $arr_total[$section1][$div1][] = 0;
                                             if(!empty($esidata)){
                                            foreach ($esidata['0']['esi'] as $esi) { 
                                        if($esi['ectc']['head_operator'] == 'Addition' && $esi['0']['salary_head_item_desc'] == 'PF' && $esi['divn']['div_name'] == $div['division']['div_name'] && $section['section']['section_name'] == $esi['section']['section_name']){  
                                            $esisum += $esi['0']['totalamount']; 
                                            $totalsum += $esi['0']['totalamount'];
                                            $cnt += $esi['0']['vcount'];
                                            $arr_total[$section1][$div1][] = abs($esi['0']['totalamount']);
                                             }}}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($esisum));
                           // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                                            }}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $cnt);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($totalsum));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'LWF');
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $cnt=0;$totalsum=0; 
                                            foreach ($arr_section as $section) { $section1 =$section['section']['section_name'];
                                            foreach ($arr_div as $div) { $esisum = 0; $div1 =$div['division']['div_name'];
                                            $arr_total[$section1][$div1][] = 0;
                                             if(!empty($esidata)){
                                            foreach ($esidata['0']['esi'] as $esi) { 
                                        if($esi['ectc']['head_operator'] == 'Addition' && $esi['0']['salary_head_item_desc'] == 'LWF' && $esi['divn']['div_name'] == $div['division']['div_name'] && $section['section']['section_name'] == $esi['section']['section_name']){  
                                            
                                            $esisum += $esi['0']['totalamount'];
                                            
                                            $totalsum += abs($esi['0']['totalamount']);
                                            $cnt += $esi['0']['vcount']; 
                                            $arr_total[$section1][$div1][] = abs($esi['0']['totalamount']);
                                             }}}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $esisum);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                                            }}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $cnt);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($totalsum));
                           // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'WWF');
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $cnt=0;$totalsum=0; 
                                            foreach ($arr_section as $section) { $section1 =$section['section']['section_name'];
                                            foreach ($arr_div as $div) { $esisum = 0; $div1 =$div['division']['div_name']; 
                                            $arr_total[$section1][$div1][] = 0;
                                             if(!empty($esidata)){
                                            foreach ($esidata['0']['esi'] as $esi) { 
                                        if($esi['ectc']['head_operator'] == 'Addition' && $esi['0']['salary_head_item_desc'] == 'WWF' && $esi['divn']['div_name'] == $div['division']['div_name'] && $section['section']['section_name'] == $esi['section']['section_name']){ 
                                            $esisum += abs($esi['0']['totalamount']); 
                                            $totalsum += abs($esi['0']['totalamount']); 
                                            $cnt += $esi['0']['vcount'];
                                            $arr_total[$section1][$div1][] = abs($esi['0']['totalamount']);
                                             }}}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($esisum));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                                            }}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $cnt);
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($totalsum));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'TOTAL');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $grand = 0;
                            foreach ($arr_section as $section) {  $section1 =$section['section']['section_name'];
                                            foreach ($arr_div as $div) { 
                                            $div1 =$div['division']['div_name'];
                                            $subtot = array_sum($arr_total[$section1][$div1]); 
                                            $grand += $subtot;
                                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($subtot));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            }}
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, '');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $columncount++;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, round($grand));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                       $rowcount = $rowcount + 2;  
                    }

                    $objPHPExcel->getActiveSheet()->setTitle('Reconciliation Report');

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
                    $this->render('reconciliation_section');
                    break;
            }
        }
    }


}
