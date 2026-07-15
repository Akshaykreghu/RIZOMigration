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
class ExpenseReportController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'ExpenseReport';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments','Units', 'ReportCriterias', 'ExpenseHead', 'Site', 'ExpenseType', 'Designation', 'DbConfig', 'ReportAudit');
    public $components = array('MasterdataManagement');

    public function hrreports() {
            $arr_reporttypes = array(
                'AdvanceExpense' => 'Expense Report'
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
                case 'AdvanceExpense':
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
            if($model == 'Site'){
            $conditions = array("status" => 1,"expected_starting_date !=" => '0000-00-00');
            $arr_order = array("site_id" => 'asc');
            } elseif ($model == 'ExpenseType') {
                $arr_order = array("expense_type_name" => 'asc');  
                $conditions = array();
            } elseif ($model == 'Units') {
                $arr_order = array("Units.branch_name" => "ASC");
                $conditions = array("Units.status" => 1);
            } elseif ($model == 'ExpenseHead') {
                $arr_order = array("ExpenseHead.expense_head_name" => "ASC");
                $conditions = array("ExpenseHead.status" => 1);
            }else {
                $conditions = array("status" => 1);
            }
           
            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions, "order" => $arr_order)));
            
            $arr_criteriaItems = array();
            $key = 0;
            switch ($model) {
                case 'Site':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['site_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['site_id'].'-'.$value['site_name'];
                        $key++;
                    }
                    break;
                case 'ExpenseType':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['expense_type_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['expense_type_name'];
                        $key++;
                    }
                    break;
                case 'ExpenseHead':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['expense_head_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['expense_head_name'];
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
            case 'AdvanceExpense':
                $dataForHistory['report_type'] = "Advance Expense Report";
                break;
            default : break;
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
            case 'AdvanceExpense':
                //expense report
                $this->generateExpenseReport($mode);
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

//payroll summary report
    private function generateExpenseReport($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto'])); 
        $expense_condition = array();
        
       // $needBranchWiseReport = false;
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $criteria = $str_criteria_item;
            $this->set('criteria', $criteria);
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
        $condition = ' where emp_details.status= 1';          
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "where emp_details.status in('1','2')";
        }
        $arr_data_set = array();
        $expense_condition = " and emp_expense.expense_date >= '$from' and emp_expense.expense_date <= '$to' ";
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) { 
        if($str_criteria_item == 'EmployeeDetails'){
        $arr_data = $this->EmployeeDetails->query("SELECT employee_info.branch,expense_type.expense_type_name,emp_details.first_name,"
                . "emp_details.last_name,expense_heads.expense_head_name,expense_heads.expense_head_pkey,"
                . "emp_expense.remarks,emp_expense_details.exp_date,emp_expense_details.total,site.site_name,site.site_id,site.site_pkey "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join employee_info on (employee_info.emp_pkey = emp_expense.emp_fkey) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "$condition and emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.emp_fkey = '$leavepolicygroupid' "
                . "$expense_condition and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_details_pkey "
                . "ORDER BY emp_details.first_name,emp_details.last_name asc ");
        if(!empty($arr_data)){ 
            $arr_data_set[] = $arr_data;
        }
        }else if($str_criteria_item == 'Units'){
        $arr_list = $this->EmployeeDetails->query("select distinct(emp_expense.emp_fkey) "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join employee_info on (employee_info.emp_pkey = emp_expense.emp_fkey) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "$condition and emp_expense.status = 1 and emp_expense_details.status = 1 and emp_details.branch_code = '$leavepolicygroupid' "
                . "$expense_condition and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_details_pkey ");
        if (isset($arr_list) && !empty($arr_list)) {
                    foreach ($arr_list as $emps) {
                     $emp = $emps['emp_expense']['emp_fkey'];
        $arr_data = $this->EmployeeDetails->query("SELECT employee_info.branch,expense_type.expense_type_name,emp_details.first_name,"
                . "emp_details.last_name,expense_heads.expense_head_name,expense_heads.expense_head_pkey,"
                . "emp_expense.remarks,emp_expense_details.exp_date,emp_expense_details.total,site.site_name,site.site_id,site.site_pkey "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join employee_info on (employee_info.emp_pkey = emp_expense.emp_fkey) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "$condition and emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.emp_fkey = '$emp' "
                . "$expense_condition and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_details_pkey "
                . "ORDER BY employee_info.branch,emp_details.first_name,emp_details.last_name asc ");
        if(!empty($arr_data)){ 
            $arr_data_set[] = $arr_data;
        }
                    }
        }
        }else if($str_criteria_item == 'ExpenseType'){
        $arr_list = $this->EmployeeDetails->query("select distinct(emp_expense.emp_fkey) "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join employee_info on (employee_info.emp_pkey = emp_expense.emp_fkey) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "$condition and emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense_details.expense_type_fkey = '$leavepolicygroupid' "
                . "$expense_condition and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_details_pkey ");
        if (isset($arr_list) && !empty($arr_list)) {
                    foreach ($arr_list as $emps) {
                     $emp = $emps['emp_expense']['emp_fkey'];
        $arr_data = $this->EmployeeDetails->query("SELECT employee_info.branch,expense_type.expense_type_name,emp_details.first_name,"
                . "emp_details.last_name,expense_heads.expense_head_name,expense_heads.expense_head_pkey,"
                . "emp_expense.remarks,emp_expense_details.exp_date,emp_expense_details.total,site.site_name,site.site_id,site.site_pkey "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join employee_info on (employee_info.emp_pkey = emp_expense.emp_fkey) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "$condition and emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.emp_fkey = '$emp' "
                . "$expense_condition and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_details_pkey "
                . "ORDER BY expense_type.expense_type_name,emp_details.first_name,emp_details.last_name asc ");
        if(!empty($arr_data)){ 
            $arr_data_set[] = $arr_data;
        }
                    }
        }
        }else if($str_criteria_item == 'Site'){
        $arr_list = $this->EmployeeDetails->query("select distinct(emp_expense.emp_fkey) "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join employee_info on (employee_info.emp_pkey = emp_expense.emp_fkey) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "$condition and emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$leavepolicygroupid' "
                . "$expense_condition and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_details_pkey ");
        if (isset($arr_list) && !empty($arr_list)) {
                    foreach ($arr_list as $emps) {
                     $emp = $emps['emp_expense']['emp_fkey'];
        $arr_data = $this->EmployeeDetails->query("SELECT employee_info.branch,expense_type.expense_type_name,emp_details.first_name,"
                . "emp_details.last_name,expense_heads.expense_head_name,expense_heads.expense_head_pkey,"
                . "emp_expense.remarks,emp_expense_details.exp_date,emp_expense_details.total,site.site_name,site.site_id,site.site_pkey "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join employee_info on (employee_info.emp_pkey = emp_expense.emp_fkey) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "$condition and emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.emp_fkey = '$emp' "
                . "$expense_condition and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_details_pkey "
                . "ORDER BY site.site_name asc ");
        if(!empty($arr_data)){ 
            $arr_data_set[] = $arr_data;
        }
                    }
        }
        }else {
        $arr_list = $this->EmployeeDetails->query("select distinct(emp_expense.emp_fkey) "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join employee_info on (employee_info.emp_pkey = emp_expense.emp_fkey) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "$condition and emp_expense.status = 1 and emp_expense_details.status = 1 and expense_type.expense_head_fkey = '$leavepolicygroupid' "
                . "$expense_condition and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_details_pkey ");
        if (isset($arr_list) && !empty($arr_list)) {
                    foreach ($arr_list as $emps) {
                     $emp = $emps['emp_expense']['emp_fkey'];
        $arr_data = $this->EmployeeDetails->query("SELECT employee_info.branch,expense_type.expense_type_name,emp_details.first_name,"
                . "emp_details.last_name,expense_heads.expense_head_name,expense_heads.expense_head_pkey,"
                . "emp_expense.remarks,emp_expense_details.exp_date,emp_expense_details.total,site.site_name,site.site_id,site.site_pkey "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join employee_info on (employee_info.emp_pkey = emp_expense.emp_fkey) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "$condition and emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.emp_fkey = '$emp' "
                . "$expense_condition and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_details_pkey "
                . "ORDER BY expense_heads.expense_head_name,emp_details.first_name,emp_details.last_name asc ");
        if(!empty($arr_data)){ 
            $arr_data_set[] = $arr_data;
        }
                    }
        }
        }
        }
   
            $arr_summary_for_template['summary'] = array();
           
            //employee wise report
            foreach ($arr_data_set as $set1) {
                foreach ($set1 as $set) {
                $request['expense_head_name'] = isset($set['expense_heads']['expense_head_name']) ? $set['expense_heads']['expense_head_name'] : '';
                $request['exp_date'] = isset($set['emp_expense_details']['exp_date']) ? $set['emp_expense_details']['exp_date'] : '';
                $request['expense_type_name'] = isset($set['expense_type']['expense_type_name']) ? $set['expense_type']['expense_type_name'] : '';
                $request['purpose'] = isset($set['emp_expense']['remarks']) ? $set['emp_expense']['remarks'] : '';
                $request['site_name'] = isset($set['site']['site_name']) ? $set['site']['site_name'].' - '.$set['site']['site_id']: '';
                $request['emp_name'] = isset($set['emp_details']['first_name']) ? $set['emp_details']['first_name'].' - '.$set['emp_details']['last_name'] : ''; 
                $request['amount'] = isset($set['emp_expense_details']['total']) ? $set['emp_expense_details']['total'] : '';
                $request['branch'] = isset($set['employee_info']['branch']) ? $set['employee_info']['branch'] : ''; 
                $arr_summary_for_template['summary'][] = $request;
                }       
            }
        
        //$this->set('needBranchWiseReport', $needBranchWiseReport);
        $this->set('arr_summary_for_template', $arr_summary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('expensereports');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('ExpenseReport-' . $from . " to " . $to. '.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . " Project Expense Report " . $from . " - " . $to . ".xlsx" : "Project Expense Report" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Project Expense");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Project Expense Report - " . $from . " to " . $to);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(17);

                for ($col = 'A'; $col !== 'R'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }

                $worksheet->mergeCells('A1:N1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );


                $rowcount = 2;
              //  if (isset($needBranchWiseReport) && $needBranchWiseReport == true) {

                    foreach ($arr_summary_for_template as $branch_code => $leavesummary) {
//                        $branch = $leavesummary['branch_name'];
//
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
//                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Expense Head');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Expense Type');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Description');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Project Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Zone');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $arr_data  = $arr_summary_for_template['summary'];
                                if(count($arr_data)>0){ 
                                $sum = 0;
                                $i=1;
                                foreach($arr_data as $val){ 
                                $sum = $sum + $val['amount'];

                                $exp_date = $val['exp_date'];
                                $expense_head_name = $val['expense_head_name'];
                                $expense_type_name = $val['expense_type_name'];
                                $purpose = $val['purpose'];
                                $site_name = $val['site_name'];
                                $branch = $val['branch'];
                                $emp_name = $val['emp_name'];
                                $amount = $val['amount'];

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $exp_date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $expense_head_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $expense_type_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $purpose);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $site_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $emp_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $amount);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $rowcount++;
                                $i++;
                                }
                                $worksheet->mergeCells('A'.$rowcount.':H'.$rowcount);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Total');
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $sum);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyle('I2:I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                $objPHPExcel->getActiveSheet()->getStyle('A2:I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                } 
                $objPHPExcel->getActiveSheet()->setTitle('Project Expense Report');
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
                $this->render('expensereports');
                break;
        }
    }

}
