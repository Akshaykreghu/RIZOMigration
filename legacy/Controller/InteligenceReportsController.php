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
class inteligenceReportsController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'InteligenceReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('LeavePolicyGroup', 'CentralControl', 'Banks', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments', 'EmployeeGrossDetails', 'Verticals', 'Units', 'ReportCriterias', 'DayTimeProcedures', 'EmpCtcTransaction', 'LeaveRequests', 'Designation', 'DbConfig', 'ReportAudit');
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

    public function hrreports()
    {
        $company_code = $this->Session->read('company_code');
        if (($company_code == 'HDEQ') || ($company_code == 'HDFN') || ($company_code == 'HDSC') || ($company_code == 'HDCM') || ($company_code == 'DEMO')) {
            $arr_reporttypes = array(

                'InteligenceNonCompli' => 'Intelligence and Non Compliance'
            );
        } else {
            $arr_reporttypes = array(

                'InteligenceNonCompli' => 'Intelligence and Non Compliance'
            );
        }
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */

    public function changereporttype($type = '')
    {
        // debug($type);
        $this->autoRender = FALSE;
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {

                    //Edited by Akshay on 7-8-2023
                case 'InteligenceNonCompli':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                default:
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

    public function addreportcriteria($type = '', $newindex = '', $str_currentcriterias = '')
    {
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

    public function loadcriteriaitems($index, $str_criteria = '')
    {
        $this->autoRender = FALSE;
        if ($str_criteria != '') {
            $model = $str_criteria;
            if ($this->_modelExists($model)) {
                if ($str_criteria == 'LeavePolicyGroup' || $str_criteria == 'BankStatement') {
                    $model = 'Banks';
                }
                $this->set('index', $index);
                $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
                $model = ($model == 'Departments') ? 'Departments' : $model;
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
        $model = $str_criteria;
        $arr_requestdata = $this->request->data;
        // debug( $arr_requestdata);
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
                $arr_criteriaItemsDB = array();
                foreach ($arr_criteriaItemsDB1 as $val) {
                    if ($val['0']['bank_name'] != '') {
                        $arr_criteriaItemsDB[] = $val['0'];
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
            }
            echo json_encode($arr_criteriaItems);
        }
    }

    public function downloadHistory($type, $mode)
    {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {

                //Edited by Akshay on 7-8-2023
            case 'InteligenceNonCompli':
                $dataForHistory['report_type'] = "Inteligence and Non Compliance Report";
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
                case 'EmployeeDetails':
                    $criteria_name_array[] = 'belonging to an Employee';
                    break;
                case 'Units':
                    $criteria_name_array[] = 'belonging to a Branch';
                    break;
                case 'Departments':
                    $criteria_name_array[] = 'belonging to a Department';
                    break;
                case 'Grades':
                    $criteria_name_array[] = 'belonging to a Grade';
                    break;
                case 'Verticals':
                    $criteria_name_array[] = 'belonging to a Vertical';
                    break;
                case 'SalaryHeadItems':
                    $criteria_name_array[] = 'belonging to a Salary Head Item';
                    break;
                case 'LeaveRequests':
                    $criteria_name_array[] = 'belonging to a Leave Request';
                    break;
                case 'LeavePolicyGroup':
                    $criteria_name_array[] = 'belonging to a Bank';
                    break;
                case 'Leavestatus':
                    $criteria_name_array[] = 'belonging to a Leave status';
                    break;
                case 'LeavesPolicyGroup':
                    $criteria_name_array[] = 'belonging to a Leaves Policy Group';
                    break;
                case 'EmployeeGrossDetails':
                    $criteria_name_array[] = 'belonging to a Designation';
                    break;
                case 'DayTimeProcedures':
                    $criteria_name_array[] = 'belonging to a Day Time Procedure';
                    break;
                case 'attendance':
                    $criteria_name_array[] = 'belonging to an Attendance';
                    break;
                default:
                    break;
            }
            if ($arr_form_data[$criteria] != 'SELECTALL') {
                $items_array[] = implode(",", $arr_form_data[$criteria]);
            }
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

    public function generatereport($type = '', $mode = '')
    {
        $this->autoRender = false;
        switch ($type) {

                //Edited by Akshay on 7-8-2023
            case 'InteligenceNonCompli':
                //payroll summary report
                $this->IntelligenceandNonCompliancereport($mode);
                break;

            default:
                return false;
                break;
        }

        $this->downloadHistory($type, $mode);
    }

    public function listemployeefields()
    {
        App::import('Vendor', 'EmployeeInformationFields', array('file' => 'ReportFields' . DS . 'EmployeeInformationFields.php'));
        $arr_empinformation_fields = new EmployeeInformationFields();
        $arr_emp_field_headings = array_merge(
            $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'),
            $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'),
            $arr_empinformation_fields->getFieldHeadings('Departments'),
            $arr_empinformation_fields->getFieldHeadings('Grades'),
            $arr_empinformation_fields->getFieldHeadings('Verticals'),
            $arr_empinformation_fields->getFieldHeadings('Units')
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

    private function _modelExists($modelName)
    {
        $models = App::objects('model');
        return in_array($modelName, $models);
    }



    //Edited by Akshay Inteligence & Compliance Report
    private function IntelligenceandNonCompliancereport($mode)
    {
        $arr_form_data = $_REQUEST;
        // debug($arr_form_data); exit;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_month = $arr_form_data['reportfrom'];
        date_default_timezone_set('Asia/Kolkata');
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));

        $cond = array();
        $needBranchWiseReport = false;
        $conditions = array();

        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            } else {
                $needBranchWiseReport = false;
            }
            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array(
                "fields" => "reportcriteria,reportcriteria_field",
                "conditions" => array(
                    "reporttype" => "InteligenceNonCompli",
                    "status" => 1,
                    'reportcriteria' => $str_criteria_item
                )
            )));

            $order_by2 = '';

            if (isset($arr_reportcriterias[0]['reportcriteria_field']) && isset($arr_form_data[$str_criteria_item])) {
                if ($needBranchWiseReport == true) {
                    // $conditions[] = $arr_reportcriterias[0]['reportcriteria'] . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                    $conditions[] = 'ei.branch_code IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                    // $order_by = " ei.branch ASC";
                    // $order_by2 = "trim(ei.EmpName) ASC,";

                    $order_by = " trim(ei.EmpName) ASC";
                    $order_by2 = "trim(ei.branch) ASC,";
                } else {
                    $arr_form_data[$str_criteria_item][] = ''; //Added to avoid error during single employee selection
                    // debug($arr_form_data[$str_criteria_item]);
                    $conditions[] = 'ei' . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                    $order_by = " ei.EmpName ASC";
                }
            }
        }
        $condition = ' AND ei.emp_status= 1';
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = " AND ei.emp_status in('1','2')";
        }

        $ngtvsal = " AND pm.net_salary >= 0 AND pm.month_year = '$from' AND pm.action IN ('Processed', 'Approved')";
        if (isset($arr_form_data['ngtvsal']) && $arr_form_data['ngtvsal'] == '1') {
            $ngtvsal =  " AND pm.month_year = '$from' AND pm.action IN ('Processed', 'Approved')";
        }

        $str_conditions = implode(' AND ', $conditions);
        $cond = " and ar.month_year = '$from' and ";

        // debug($str_conditions);
        //Minimum Wages Non Compliance
        $arr_minwages_noncompliance = array();
        $arr_minwages_noncompliance = $this->EmpCtcTransaction->query("SELECT ei.employee_id, ei.EmpName, ei.joining_date, ei.branch,ei.branch_code, ei.designation,ei.emp_status, ect.emp_anual_ctc
                                                                    FROM employee_info ei
                                                                    LEFT JOIN emp_ctc_transaction ect ON ect.emp_fkey = ei.emp_pkey
                                                                    left join attendance_register as ar on(ar.emp_fkey = ei.emp_pkey and ar.isdelete = 'N')
                                                                    left join branches as Units on(Units.branch_code = ei.branch_code and Units.status = 1)
                                                                    left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                                    WHERE ect.emp_anual_ctc < 150000 $cond $str_conditions $condition
                                                                    AND ect.end_date_effective IS NULL
                                                                    $ngtvsal
                                                                    ORDER BY $order_by2
                                                                    $order_by");

        // debug($arr_minwages_noncompliance); exit;
        $arr_leavesummary_for_template['minwages_noncompliance'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_minwages_noncompliance as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['minwages_noncompliance'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['minwages_noncompliance'] = $arr_minwages_noncompliance;
        }

        // debug( $arr_leavesummary_for_template['minwages_noncompliance']); exit;
        //PF Short Deduction
        $arr_pf_short_deducion = $this->EmpCtcTransaction->query("SELECT ei.employee_id, ei.EmpName, ei.joining_date, ei.branch,ei.branch_code, ei.designation,ei.emp_status,ectc.structure_det_value,es.structure_det_value
                                                                    FROM employee_info ei
                                                                    LEFT JOIN emp_salary_structure es ON es.emp_fkey = ei.emp_pkey
                                                                    left join attendance_register as ar on(ar.emp_fkey = ei.emp_pkey and ar.isdelete = 'N')
                                                                    LEFT JOIN emp_salary_slip ectc ON es.emp_fkey = ectc.emp_fkey
                                                                    left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                                    WHERE ectc.month_year = '$from'
                                                                    AND es.end_date_effective IS NULL
                                                                    AND ectc.end_date_effective IS NULL
                                                                    AND ectc.salary_head_item_fkey IN (SELECT salary_head_item_Fkey FROM tax_salary_components WHERE tax_salary_components_name = 'Employee EPF')
                                                                    AND es.salary_head_item_fkey IN (SELECT salary_head_item_Fkey FROM tax_salary_components WHERE tax_salary_components_name = 'Employee EPF')
                                                                    $cond
                                                                    $str_conditions $condition
                                                                    AND (
                                                                        ( ABS(ectc.structure_det_value) < 1380)
                                                                        OR
                                                                        (ABS(es.structure_det_value) < 1380)
                                                                    )
                                                                    $ngtvsal
                                                                    ORDER BY $order_by2
                                                                    $order_by");

        // debug($str_conditions);
        // debug($arr_pf_short_deducion); exit;
        $arr_leavesummary_for_template['pf_short_deduction'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_pf_short_deducion as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['pf_short_deduction'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['pf_short_deduction'] = $arr_pf_short_deducion;
        }

        //PF Non Compliance
        $arr_pf_noncompliance = $this->EmpCtcTransaction->query("SELECT ei.employee_id, ei.EmpName, ei.joining_date, ei.branch,ei.branch_code, ei.designation,ei.emp_status, ect.emp_anual_ctc
                                                                    FROM employee_info ei
                                                                    LEFT JOIN emp_ctc_transaction ect ON ect.emp_fkey = ei.emp_pkey
                                                                    left join attendance_register as ar on(ar.emp_fkey = ei.emp_pkey and ar.isdelete = 'N')
                                                                    left join branches as Units on(Units.branch_code = ei.branch_code and Units.status = 1)
                                                                    left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                                    WHERE ect.emp_anual_ctc < 180012 $cond $str_conditions $condition
                                                                    AND ect.end_date_effective IS NULL
                                                                    AND ei.emp_pkey NOT IN (SELECT emp_fkey FROM emp_salary_slip WHERE month_year = '$from' 
                                                                    AND salary_head_item_fkey IN (SELECT salary_head_item_Fkey FROM tax_salary_components WHERE tax_salary_components_name = 'Employee EPF'))
                                                                    $ngtvsal
                                                                    ORDER BY $order_by2
                                                                    $order_by");
        // debug($arr_pf_noncompliance); exit;
        $arr_leavesummary_for_template['pf_noncompliance'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_pf_noncompliance as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['pf_noncompliance'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['pf_noncompliance'] = $arr_pf_noncompliance;
        }
        // debug($arr_leavesummary_for_template['pf_noncompliance']); exit;
        //ESI Non Compliance
        $arr_esi_noncompliance = $this->EmpCtcTransaction->query("SELECT ei.employee_id, ei.EmpName, ei.joining_date, ei.branch,ei.branch_code, ei.designation,ei.emp_status, ect.emp_anual_ctc
                                                                    FROM employee_info ei
                                                                    LEFT JOIN emp_ctc_transaction ect ON ect.emp_fkey = ei.emp_pkey
                                                                    left join attendance_register as ar on(ar.emp_fkey = ei.emp_pkey and ar.isdelete = 'N')
                                                                    left join branches as Units on(Units.branch_code = ei.branch_code and Units.status = 1)
                                                                    left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                                    WHERE ect.emp_anual_ctc < 252012 $cond $str_conditions $condition
                                                                    AND ect.end_date_effective IS NULL
                                                                    AND ei.emp_pkey NOT IN (SELECT emp_fkey FROM emp_salary_slip WHERE month_year = '$from' 
                                                                    AND salary_head_item_fkey IN (SELECT salary_head_item_Fkey FROM tax_salary_components WHERE tax_salary_components_name = 'Employee ESI'))
                                                                    $ngtvsal
                                                                    ORDER BY $order_by2
                                                                    $order_by");
        // debug($arr_esi_noncompliance); exit;
        $arr_leavesummary_for_template['esi_noncompliance'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_esi_noncompliance as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['esi_noncompliance'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['esi_noncompliance'] = $arr_esi_noncompliance;
        }
        //WWF/LWF Deductions
        $arr_wwf_lwf_deductions = $this->EmpCtcTransaction->query("SELECT DISTINCT ei.emp_pkey, ei.employee_id, ei.EmpName, ei.joining_date, ei.branch,ei.branch_code, ei.designation,ei.emp_status,
                                                                    (SELECT SUM(ROUND(ectc1.structure_det_value,0)) 
                                                                        FROM emp_salary_slip as ectc1
                                                                        WHERE ectc1.emp_fkey = ei.emp_pkey
                                                                        AND ectc1.month_year = '$from'
                                                                        AND ectc1.head_operator = 'Addition'
                                                                        AND ectc1.head_type != 'manually'
                                                                        AND ectc1.item_part = 'Direct'
                                                                        AND ectc1.end_date_effective IS NULL
                                                                        AND ectc1.salary_head_item_fkey IN (SELECT salary_head_item_pkey
                                                                                                                            FROM salary_head_items
                                                                                                                            WHERE head_fkey IN (SELECT sh.head_pkey FROM salary_heads sh WHERE sh.head_desc = 'Heads for Monthly Earningss' 
                                                                                                                            OR
                                                                                                                            sh.head_desc = 'Heads For Monthly Variable'
                                                                                                                            OR
                                                                                                                            sh.head_desc = 'HEADS FR MONTHLY EARNINGS' ))
                                                                                                    ) AS standard_salary
                                                                    FROM employee_info ei
                                                                    LEFT JOIN emp_ctc_transaction ect ON ect.emp_fkey = ei.emp_pkey
                                                                    LEFT JOIN emp_salary_structure es ON es.emp_fkey = ei.emp_pkey
                                                                    LEFT JOIN emp_salary_slip ectc ON es.emp_fkey = ectc.emp_fkey
                                                                    left join attendance_register as ar on(ar.emp_fkey = ei.emp_pkey and ar.isdelete = 'N')
                                                                    left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                                    WHERE ectc.month_year = '$from'
                                                                    AND es.end_date_effective IS NULL
                                                                    AND ectc.end_date_effective IS NULL
                                                                    AND ect.end_date_effective IS NULL
                                                                        AND ei.emp_pkey NOT IN (
                                                                            SELECT emp_fkey FROM emp_salary_slip WHERE salary_head_item_fkey IN (
                                                                            SELECT salary_head_item_Fkey FROM tax_salary_components WHERE tax_salary_components_name = 'Employee WWW'
                                                                            ) AND month_year = '$from'
                                                                        )
                                                                        AND ei.emp_pkey NOT IN (
                                                                            SELECT emp_fkey FROM emp_salary_slip WHERE salary_head_item_fkey IN (
                                                                            SELECT salary_head_item_Fkey FROM tax_salary_components WHERE tax_salary_components_name = 'Employee LWF'
                                                                            ) AND month_year = '$from'
                                                                        )
                                                                         $cond $str_conditions $condition
                                                                         $ngtvsal
                                                                         ORDER BY $order_by2
                                                                         $order_by");
        // // debug($str_conditions);
        //debug($arr_wwf_lwf_deductions); exit;
        $arr_leavesummary_for_template['wwf_lwf_deductions'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_wwf_lwf_deductions as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['wwf_lwf_deductions'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['wwf_lwf_deductions'] = $arr_wwf_lwf_deductions;
        }

        //Professional Tax Deductions	

        $fromDate = new DateTime($from);

        // Initialize a string to store the comma-separated list
        $previousSixMonthsList = '';
        $previousMonthDate = clone $fromDate;
        $previousSixMonthsList .= "'$from',";
        // Calculate and add the previous six months to the list
        for ($i = 1; $i <= 5; $i++) {
            // Create a clone to avoid modifying the original date
            $previousMonthDate->modify('-1 month');
            $previousMonthYear = $previousMonthDate->format('Y-m');
            $previousSixMonthsList .= "'$previousMonthYear',"; // Add each month to the list
        }

        // Remove the trailing comma, if any
        $previousSixMonthsList = rtrim($previousSixMonthsList, ',');
        // debug($previousSixMonthsList); exit;

        $arr_pro_tax_deductions = $this->EmpCtcTransaction->query("SELECT ei.employee_id, ei.EmpName, ei.joining_date, ei.branch,ei.branch_code, ei.designation,ei.emp_status, ect.emp_anual_ctc
                                                                    FROM employee_info ei
                                                                    LEFT JOIN emp_ctc_transaction ect ON ect.emp_fkey = ei.emp_pkey
                                                                    LEFT JOIN emp_salary_structure es ON es.emp_fkey = ei.emp_pkey
                                                                    LEFT JOIN emp_salary_slip ectc ON es.emp_fkey = ectc.emp_fkey
                                                                    left join attendance_register as ar on(ar.emp_fkey = ei.emp_pkey and ar.isdelete = 'N')
                                                                    left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                                    WHERE es.end_date_effective IS NULL
                                                                    AND ectc.end_date_effective IS NULL
                                                                    AND ect.end_date_effective IS NULL
                                                                        AND ei.emp_pkey NOT IN (
                                                                            SELECT emp_fkey FROM emp_salary_slip WHERE salary_head_item_fkey = (
                                                                            SELECT salary_head_item_Fkey FROM tax_salary_components WHERE tax_salary_components_name = 'PROFESSIONAL TAX' 
                                                                            )
                                                                            AND month_year IN ($previousSixMonthsList) 
                                                                           
                                                                        )


                                                                        $cond
                                                                        $str_conditions $condition
                                                                        $ngtvsal
                                                                        GROUP BY ei.emp_pkey
                                                                        ORDER BY $order_by2
                                                                        $order_by");
        // debug($arr_pro_tax_deductions); exit;
        $arr_leavesummary_for_template['pro_tax_deductions'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_pro_tax_deductions as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['pro_tax_deductions'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['pro_tax_deductions'] =  $arr_pro_tax_deductions;
        }



        //PF Deductions	
        $arr_pf_deductions = array();
        $arr_months = array();
        $dateTime = new DateTime($from);
        $dateTime->modify('-1 month');
        $reducedOneMonth = $dateTime->format('Y-m');

        $dateTime = new DateTime($from);
        $dateTime->modify('-2 month');
        $reducedTwoMonth = $dateTime->format('Y-m');

        $sal_head_fkey = $this->EmpCtcTransaction->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE tax_salary_components_name IN ('Basic', 'Dearness Allowance (DA)')");
        $resultArray = array();

        foreach ($sal_head_fkey as $item) {
            $resultArray[] = (int) $item['tax_salary_components']['salary_head_item_Fkey'];
        }

        // $months = $this->EmpCtcTransaction->query("SELECT ");

        $resultString = implode(', ', $resultArray);


        $arr_employees =  $this->EmpCtcTransaction->query("SELECT DISTINCT ei.emp_pkey FROM employee_info ei
                                                                    LEFT JOIN emp_ctc_transaction ect ON ect.emp_fkey = ei.emp_pkey
                                                                    left join attendance_register as ar on(ar.emp_fkey = ei.emp_pkey and ar.isdelete = 'N')
                                                                    left join branches as Units on(Units.branch_code = ei.branch_code and Units.status = 1)
                                                                    LEFT JOIN emp_salary_slip ectc ON ectc.emp_fkey = ei.emp_pkey
                                                                    left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                                        WHERE ect.emp_anual_ctc > 180000
                                                                        $cond
                                                                        $str_conditions $condition
                                                                        $ngtvsal
                                                                        AND ect.end_date_effective IS NULL
                                                                        AND ei.emp_pkey NOT IN ( SELECT emp_fkey FROM emp_salary_slip WHERE salary_head_item_fkey = (SELECT salary_head_item_Fkey FROM tax_salary_components WHERE tax_salary_components_name = 'Employee EPF') AND month_year = '$from')            
                                                                        ORDER BY $order_by2
                                                                        $order_by
                                                                        ");
        $employees = array();
        foreach ($arr_employees as $employee) {
            $employees[] = isset($employee['ei']['emp_pkey']) ? $employee['ei']['emp_pkey'] : '';
        }

        foreach ($employees as $emp) {
            $arr_months[$emp] = $this->EmpCtcTransaction->query("SELECT DISTINCT month_year FROM payroll_master WHERE emp_fkey = $emp 
                                                                                    AND action = 'Processed'
                                                                                    AND month_year <= '$from'
                                                                                    ORDER BY month_year DESC
                                                                                    LIMIT 3;");
            if (count($arr_months[$emp]) == 3) {
                $month3 = isset($arr_months[$emp][2]['payroll_master']['month_year']) ? $arr_months[$emp][2]['payroll_master']['month_year'] : '';
                $month2 = isset($arr_months[$emp][1]['payroll_master']['month_year']) ? $arr_months[$emp][1]['payroll_master']['month_year'] : '';
                $month1 = isset($arr_months[$emp][0]['payroll_master']['month_year']) ? $arr_months[$emp][0]['payroll_master']['month_year'] : '';

                $arr_pf_deductions[$emp] = $this->EmpCtcTransaction->query("SELECT ei.emp_pkey ,ei.employee_id,ei.EmpName, ei.joining_date, ei.branch,ei.branch_code, ei.designation,ei.emp_status, ect.emp_anual_ctc, SUM(ectc.salary_amount)
                                                                                FROM employee_info ei
                                                                                LEFT JOIN emp_salary_slip ectc ON ectc.emp_fkey = ei.emp_pkey
                                                                                LEFT JOIN emp_ctc_transaction ect ON ect.emp_fkey = ei.emp_pkey
                                                                                left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                                                WHERE 
                                                                                (
                                                                                    SELECT SUM(ectc.salary_amount)
                                                                                    FROM emp_salary_slip ectc
                                                                                    WHERE ectc.salary_head_item_fkey IN ($resultString,'') 
                                                                                    AND ectc.month_year = '$month1'
                                                                                    AND ectc.emp_fkey = $emp
                                                                                    AND ectc.end_date_effective IS NULL
                                                                                ) < 15001
                                                                                AND (
                                                                                    SELECT SUM(ectc.salary_amount)
                                                                                    FROM emp_salary_slip ectc
                                                                                    WHERE ectc.salary_head_item_fkey IN ($resultString,'') 
                                                                                    AND ectc.month_year = '$month2'
                                                                                    AND ectc.emp_fkey = $emp
                                                                                    AND ectc.end_date_effective IS NULL
                                                                                ) < 15001
                                                                                AND (
                                                                                    SELECT SUM(ectc.salary_amount)
                                                                                    FROM emp_salary_slip ectc
                                                                                    WHERE ectc.salary_head_item_fkey IN ($resultString,'') 
                                                                                    AND ectc.month_year = '$month3'
                                                                                    AND ectc.emp_fkey = $emp
                                                                                    AND ectc.end_date_effective IS NULL
                                                                                ) < 15001
                                                                                $condition
                                                                                $ngtvsal
                                                                               
                                                                                AND ei.emp_pkey = $emp
                                                                                AND ectc.month_year = '$from'
                                                                                AND ectc.salary_head_item_fkey IN ($resultString,'')
                                                                                AND ectc.end_date_effective IS NULL
                                                                                ");
            }
        }



        // debug($arr_months);
        // debug($arr_employees);
        // debug($arr_pf_deductions);
        // exit;
        $arr_leavesummary_for_template['pf_deductions'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_pf_deductions as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['pf_deductions'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['pf_deductions'] =  $arr_pf_deductions;
        }


        //ESI Deductions
        $arr_esi_deductions = array();
        $arr_months = array();
        $dateTime = new DateTime($from);
        $dateTime->modify('-1 month');
        $reducedOneMonth = $dateTime->format('Y-m');

        $dateTime = new DateTime($from);
        $dateTime->modify('-2 month');
        $reducedTwoMonth = $dateTime->format('Y-m');

        $sal_head_fkey = $this->EmpCtcTransaction->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE tax_salary_components_name IN ('Basic', 'Dearness Allowance (DA)')");
        $resultArray = array();

        foreach ($sal_head_fkey as $item) {
            $resultArray[] = (int) $item['tax_salary_components']['salary_head_item_Fkey'];
        }

        // $months = $this->EmpCtcTransaction->query("SELECT ");

        $resultString = implode(', ', $resultArray);


        $arr_employees =  $this->EmpCtcTransaction->query("SELECT DISTINCT ei.emp_pkey FROM employee_info ei
                                                                    LEFT JOIN emp_ctc_transaction ect ON ect.emp_fkey = ei.emp_pkey
                                                                    left join attendance_register as ar on(ar.emp_fkey = ei.emp_pkey and ar.isdelete = 'N')
                                                                    left join branches as Units on(Units.branch_code = ei.branch_code and Units.status = 1)
                                                                    LEFT JOIN emp_salary_slip ectc ON ectc.emp_fkey = ei.emp_pkey
                                                                    left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                                        WHERE ect.emp_anual_ctc > 180000
                                                                        $cond
                                                                        $str_conditions $condition
                                                                        $ngtvsal
                                                                        AND ect.end_date_effective IS NULL
                                                                        AND ei.emp_pkey NOT IN ( SELECT emp_fkey FROM emp_salary_slip WHERE salary_head_item_fkey = (SELECT salary_head_item_Fkey FROM tax_salary_components WHERE tax_salary_components_name = 'Employee ESI') AND month_year = '$from')
                                                                        ORDER BY $order_by2
                                                                        $order_by
                                                                        ");
        $employees = array();
        foreach ($arr_employees as $employee) {
            $employees[] = isset($employee['ei']['emp_pkey']) ? $employee['ei']['emp_pkey'] : '';
        }

        foreach ($employees as $emp) {
            $arr_months[$emp] = $this->EmpCtcTransaction->query("SELECT DISTINCT month_year FROM payroll_master WHERE emp_fkey = $emp 
                                                                                    AND action = 'Processed'
                                                                                    AND month_year <= '$from'
                                                                                    ORDER BY month_year DESC
                                                                                    LIMIT 3;");
            if (count($arr_months[$emp]) == 3) {
                $month3 = isset($arr_months[$emp][2]['payroll_master']['month_year']) ? $arr_months[$emp][2]['payroll_master']['month_year'] : '';
                $month2 = isset($arr_months[$emp][1]['payroll_master']['month_year']) ? $arr_months[$emp][1]['payroll_master']['month_year'] : '';
                $month1 = isset($arr_months[$emp][0]['payroll_master']['month_year']) ? $arr_months[$emp][0]['payroll_master']['month_year'] : '';

                $arr_esi_deductions[$emp] = $this->EmpCtcTransaction->query("SELECT ei.emp_pkey ,ei.employee_id,ei.EmpName, ei.joining_date, ei.branch,ei.branch_code, ei.designation,ei.emp_status, ect.emp_anual_ctc, SUM(ectc.salary_amount)
                                                                                FROM employee_info ei
                                                                                LEFT JOIN emp_salary_slip ectc ON ectc.emp_fkey = ei.emp_pkey
                                                                                LEFT JOIN emp_ctc_transaction ect ON ect.emp_fkey = ei.emp_pkey
                                                                                left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                                                WHERE 
                                                                                (
                                                                                    SELECT SUM(ectc.salary_amount)
                                                                                    FROM emp_salary_slip ectc
                                                                                    WHERE ectc.salary_head_item_fkey IN ($resultString,'') 
                                                                                    AND ectc.month_year = '$month1'
                                                                                    AND ectc.emp_fkey = $emp
                                                                                    AND ectc.item_part = 'Direct'
                                                                                    AND ectc.head_operator = 'Addition'
                                                                                    AND ectc.head_type != 'Manually'
                                                                                    AND ectc.end_date_effective IS NULL
                                                                                ) < 21001
                                                                                AND (
                                                                                    SELECT SUM(ectc.salary_amount)
                                                                                    FROM emp_salary_slip ectc
                                                                                    WHERE ectc.salary_head_item_fkey IN ($resultString,'') 
                                                                                    AND ectc.month_year = '$month2'
                                                                                    AND ectc.emp_fkey = $emp
                                                                                    AND ectc.item_part = 'Direct'
                                                                                    AND ectc.head_operator = 'Addition'
                                                                                    AND ectc.head_type != 'Manually'
                                                                                    AND ectc.end_date_effective IS NULL
                                                                                ) < 21001
                                                                                AND (
                                                                                    SELECT SUM(ectc.salary_amount)
                                                                                    FROM emp_salary_slip ectc
                                                                                    WHERE ectc.salary_head_item_fkey IN ($resultString,'') 
                                                                                    AND ectc.month_year = '$month3'
                                                                                    AND ectc.emp_fkey = $emp
                                                                                    AND ectc.item_part = 'Direct'
                                                                                    AND ectc.head_operator = 'Addition'
                                                                                    AND ectc.head_type != 'Manually'
                                                                                    AND ectc.end_date_effective IS NULL
                                                                                ) < 21001
                                                                                $condition
                                                                                $ngtvsal
                                                                                
                                                                                AND ei.emp_pkey = $emp
                                                                                AND ectc.month_year = '$from'
                                                                                AND ectc.salary_head_item_fkey IN ($resultString,'')
                                                                                AND ectc.end_date_effective IS NULL
                                                                                ");
            }
        }


        // debug( $arr_esi_deductions); exit; 
        $arr_leavesummary_for_template['esi_deductions'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_esi_deductions as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['esi_deductions'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['esi_deductions'] =  $arr_esi_deductions;
        }


        //Payroll Details
        $arr_payroll_details = $this->EmpCtcTransaction->query("SELECT DISTINCT ei.employee_id, ei.EmpName, ei.joining_date, ei.branch,ei.branch_code, ei.designation,ei.emp_status, pm.action, da.C2, da.C3
                                                                                FROM employee_info ei
                                                                                left join attendance_register as ar on(ar.emp_fkey = ei.emp_pkey and ar.isdelete = 'N')
                                                                                left join branches as Units on(Units.branch_code = ei.branch_code and Units.status = 1)
                                                                                LEFT JOIN payroll_master pm ON pm.emp_fkey = ei.emp_pkey
                                                                                LEFT JOIN device_attandance da ON ( da.emp_id = ei.emp_id AND DATE_FORMAT(da.LOGDATE, '%Y-%m') = '$from' AND da.C2 IS NULL AND da.DEVICEID = 0)
                                                                                WHERE pm.action IN ('Approved', 'Processed') 

                                                                                $cond
                                                                                $str_conditions $condition
                                                                                AND pm.month_year = '$from'
                                                                                AND da.C3 IS NOT NULL
                                                                                $ngtvsal
                                                                                ORDER BY $order_by2
                                                                                $order_by");

        // debug($arr_payroll_details); exit; 
        $arr_leavesummary_for_template['payroll_details'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_payroll_details as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['payroll_details'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['payroll_details'] =  $arr_payroll_details;
        }
        // debug($arr_leavesummary_for_template['payroll_details']); exit;

        //Attendance Details
        $arr_attendance_details = $this->EmpCtcTransaction->query("SELECT ei.employee_id, ei.EmpName, ei.joining_date, ei.branch,ei.branch_code, ei.designation,ei.emp_status, da.C3, da.C2
                                                                                FROM employee_info ei
                                                                                left join attendance_register as ar on(ar.emp_fkey = ei.emp_pkey and ar.isdelete = 'N')
                                                                                left join branches as Units on(Units.branch_code = ei.branch_code and Units.status = 1)
                                                                                LEFT JOIN device_attandance da ON da.emp_id = ei.emp_id
                                                                                left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                                                WHERE DATE_FORMAT(da.LOGDATE, '%Y-%m') = '$from'
                                                                                
                                                                                AND da.C2 IS NULL
                                                                                $cond
                                                                                $str_conditions $condition
                                                                                $ngtvsal
                                                                                GROUP BY da.C2
                                                                                ORDER BY $order_by2
                                                                                $order_by");

        // debug($arr_attendance_details); exit; 
        $arr_leavesummary_for_template['attendance_details'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_attendance_details as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['attendance_details'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['attendance_details'] =  $arr_attendance_details;
        }


        //Salary Details

        $prev_month = date('Y-m', strtotime($from . ' -1 month'));

        $arr_salary_details = $this->EmpCtcTransaction->query("SELECT ei.employee_id, ei.EmpName, ei.joining_date, ei.branch,ei.branch_code, ei.designation,ei.emp_status,
                                                                                            (SELECT SUM(ROUND(ectc1.structure_det_value,0)) 
                                                                                                    FROM emp_salary_slip as ectc1
                                                                                                    WHERE ectc1.emp_fkey = ei.emp_pkey
                                                                                                    AND ectc1.month_year = '$prev_month'
                                                                                                    AND ectc1.head_operator = 'Addition'
                                                                                                    AND ectc1.head_type != 'Manually'
                                                                                                    AND ectc1.item_part = 'Direct'
                                                                                                    AND ectc1.end_date_effective IS NULL
                                                                                                    AND ectc1.salary_head_item_fkey IN (SELECT salary_head_item_pkey
                                                                                                                            FROM salary_head_items
                                                                                                                            WHERE head_fkey IN (SELECT sh.head_pkey FROM salary_heads sh WHERE sh.head_desc = 'Heads for Monthly Earningss' 
                                                                                                                            OR
                                                                                                                            sh.head_desc = 'Heads For Monthly Variable'
                                                                                                                            OR
                                                                                                                            sh.head_desc = 'HEADS FR MONTHLY EARNINGS' ))
                                                                                                    ) AS old_salary,
                                                                                                    (SELECT SUM(ROUND(ectc2.structure_det_value,0)) 
                                                                                                    FROM emp_salary_slip as ectc2
                                                                                                    WHERE ectc2.emp_fkey = ei.emp_pkey
                                                                                                    AND ectc2.month_year = '$from'
                                                                                                    AND ectc2.head_operator = 'Addition'
                                                                                                    AND ectc2.head_type != 'Manually'
                                                                                                    AND ectc2.item_part = 'Direct'
                                                                                                    AND ectc2.end_date_effective IS NULL
                                                                                                    AND ectc2.salary_head_item_fkey IN (SELECT salary_head_item_pkey
                                                                                                                            FROM salary_head_items
                                                                                                                            WHERE head_fkey IN (SELECT sh.head_pkey FROM salary_heads sh WHERE sh.head_desc = 'Heads for Monthly Earningss' 
                                                                                                                            OR
                                                                                                                            sh.head_desc = 'Heads For Monthly Variable'
                                                                                                                            OR
                                                                                                                            sh.head_desc = 'HEADS FR MONTHLY EARNINGS' ))
                                                                                                    ) AS new_salary
                                                                                
                                                                                FROM employee_info ei 
                                                                                left join branches as Units on(Units.branch_code = ei.branch_code and Units.status = 1)
                                                                                LEFT JOIN emp_ctc_transaction ect ON ect.emp_fkey = ei.emp_pkey
                                                                                left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey and pm.action in ('Processed','')
                                                                                LEFT JOIN emp_salary_slip as ectc ON ectc.emp_fkey = ei.emp_pkey
                                                                                WHERE
                                                                                 (
                                                                                    pm.month_year = '$prev_month'
                                                                                    OR
                                                                                    pm.month_year = '$from'
                                                                                    
                                                                                  )
                                                                                
                                                                                AND
                                                                                $str_conditions $condition
                                                                                $ngtvsal
                                                                                AND ectc.end_date_effective IS NULL
                                                                                AND ectc.month_year IN ('$prev_month','$from')
                                                                                GROUP BY
                                                                                        ei.employee_id,
                                                                                        ei.EmpName,
                                                                                        ei.joining_date,
                                                                                        ei.branch,
                                                                                        ei.branch_code,
                                                                                        ei.designation,
                                                                                        ei.emp_status
                                                                                ORDER BY $order_by2
                                                                                $order_by");

        // debug($arr_salary_details); exit; 
        $arr_leavesummary_for_template['salary_details'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_salary_details as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['salary_details'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['salary_details'] =  $arr_salary_details;
        }


        //Variable Details
        $formattedFrom = date('m-Y', strtotime($from));
        $arr_variable_details = $this->EmpCtcTransaction->query("SELECT ei.employee_id, ei.EmpName, ei.joining_date, ei.branch,ei.branch_code, ei.designation,ei.emp_status,
                                                                                evu.salary_head_item_desc, 
                                                                                evu.uploaded_amount
                                                                                
                                                                                FROM employee_info ei 
                                                                                left join branches as Units on(Units.branch_code = ei.branch_code and Units.status = 1)
                                                                                LEFT JOIN emp_variables_upload evu ON evu.emp_fkey = ei.emp_pkey
                                                                                left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                                                WHERE evu.month_year = '$formattedFrom'
                                                                               
                                                                                AND
                                                                                $str_conditions $condition
                                                                                $ngtvsal
                                                                                ORDER BY $order_by2
                                                                                $order_by
                                                                                
                                                                                ");


        $arr_leavesummary_for_template['variable_details'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_variable_details as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['variable_details'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['variable_details'] =  $arr_variable_details;
        }


        //PF To Be Covered Employees
        $arr_hra_key = $this->EmpCtcTransaction->query("SELECT salary_head_item_Fkey FROM tax_salary_components
                                                                    WHERE tax_salary_components_name = 'House Rent Allowance (HRA)'");
        $hra_key = isset($arr_hra_key[0]['tax_salary_components']['salary_head_item_Fkey']) ? $arr_hra_key[0]['tax_salary_components']['salary_head_item_Fkey'] : 0;


        $arr_pf_to_be_covered = $this->EmpCtcTransaction->query("SELECT DISTINCT ei.emp_pkey,
                                                                ei.employee_id,
                                                                ei.EmpName,
                                                                ei.joining_date,
                                                                ei.branch,
                                                                ei.branch_code,
                                                                ei.designation,
                                                                ei.emp_status,
                                                                ectc.structure_det_value,
                                                                ectc.salary_head_item_desc,
                                                                ect.emp_anual_ctc
                                                            FROM
                                                                employee_info ei
                                                            LEFT JOIN
                                                                branches AS Units ON (Units.branch_code = ei.branch_code AND Units.status = 1)
                                                            LEFT JOIN
                                                                attendance_register AS ar ON (ar.emp_fkey = ei.emp_pkey AND ar.isdelete = 'N')
                                                            LEFT JOIN
                                                                emp_ctc_transaction ect ON ect.emp_fkey = ei.emp_pkey
                                                            LEFT JOIN
                                                                emp_salary_slip ectc ON (ectc.emp_fkey = ei.emp_pkey AND ectc.month_year = '$from')
                                                            left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                            WHERE
                                                                ei.emp_pkey NOT IN (
                                                                    SELECT
                                                                        emp_fkey
                                                                    FROM
                                                                        emp_salary_slip
                                                                    WHERE
                                                                        salary_head_item_fkey IN (
                                                                            SELECT
                                                                                salary_head_item_Fkey
                                                                            FROM
                                                                                tax_salary_components
                                                                            WHERE
                                                                                tax_salary_components_name = 'Employee EPF'
                                                                        )
                                                                )
                                                                AND ectc.end_date_effective IS NULL
                                                                AND ect.end_date_effective IS NULL
                                                                AND ectc.salary_head_item_fkey = $hra_key
                                                                $cond 
                                                                $str_conditions $condition
                                                                $ngtvsal
                                                            ORDER BY
                                                                ei.branch;
                                                            ");

        // debug($arr_pf_to_be_covered); exit; 

        $arr_leavesummary_for_template['pf_to_be_covered'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_pf_to_be_covered as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['pf_to_be_covered'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['pf_to_be_covered'] =  $arr_pf_to_be_covered;
        }



        //ESI To Be Covered Employees

        $arr_esi_to_be_covered = $this->EmpCtcTransaction->query("SELECT ei.employee_id, ei.EmpName, ei.joining_date, ei.branch,ei.branch_code, ei.designation,ei.emp_status,
                                                                                (CASE WHEN salary_head_item_fkey = (SELECT tc.salary_head_item_Fkey FROM tax_salary_components tc WHERE tc.tax_salary_components_name = 'Conveyance Allowance' ) THEN ROUND(structure_det_value,0) ELSE 0 END) AS conveyance_allowance,
                                                                                (CASE WHEN salary_head_item_fkey = (SELECT tc.salary_head_item_Fkey FROM tax_salary_components tc WHERE tc.tax_salary_components_name = 'Washing Allowance') THEN ROUND(structure_det_value,0) ELSE 0 END) AS washing_allowance,
                                                                                (SELECT SUM(ROUND(ectc1.structure_det_value,0)) 
                                                                                    FROM emp_salary_slip as ectc1
                                                                                    WHERE ectc1.emp_fkey = ei.emp_pkey
                                                                                    AND ectc1.month_year = '$from'
                                                                                    AND ectc1.head_operator = 'Addition'
                                                                                    AND ectc1.head_type != 'manually'
                                                                                    AND ectc1.item_part = 'Direct'
                                                                                    AND ectc1.salary_head_item_fkey IN (SELECT salary_head_item_pkey
                                                                                                                            FROM salary_head_items
                                                                                                                            WHERE head_fkey IN (SELECT sh.head_pkey FROM salary_heads sh WHERE sh.head_desc = 'Heads for Monthly Earningss' 
                                                                                                                            OR
                                                                                                                            sh.head_desc = 'Heads For Monthly Variable'
                                                                                                                            OR
                                                                                                                            sh.head_desc = 'HEADS FR MONTHLY EARNINGS' ))
                                                                                                    ) AS standard_salary,
                                                                                    ectc.month_year
                                                                                
                                                                                FROM employee_info ei 
                                                                                left join branches as Units on(Units.branch_code = ei.branch_code and Units.status = 1)
                                                                                left join attendance_register as ar on(ar.emp_fkey = ei.emp_pkey and ar.isdelete = 'N')
                                                                                LEFT JOIN emp_ctc_transaction ect ON ect.emp_fkey = ei.emp_pkey AND ect.end_date_effective IS NULL
                                                                                LEFT JOIN emp_salary_slip ectc ON (ei.emp_pkey = ectc.emp_fkey) 
                                                                                            
                                                                                left join payroll_master as pm on pm.emp_fkey = ei.emp_pkey
                                                                                WHERE ei.emp_pkey NOT IN (
                                                                                    SELECT emp_fkey FROM emp_salary_slip WHERE salary_head_item_fkey IN (
                                                                                    SELECT salary_head_item_Fkey FROM tax_salary_components WHERE tax_salary_components_name = 'Employee ESI'
                                                                                    AND month_year = '$from'
                                                                                    )
                                                                                )
                                                                                AND ectc.month_year = '$from' AND ectc.end_date_effective IS NULL

                                                                                $cond
                                                                                $str_conditions $condition
                                                                                $ngtvsal
                                                                                ORDER BY $order_by2
                                                                                $order_by");

        //debug($arr_esi_to_be_covered); exit;
        $arr_leavesummary_for_template['esi_to_be_covered'] = array();
        if ($needBranchWiseReport == true) {
            foreach ($arr_esi_to_be_covered as $leavesummary) {
                $unit = isset($leavesummary['ei']['branch_code']) ? $leavesummary['ei']['branch_code'] : '';
                $arr_leavesummary_for_template['esi_to_be_covered'][$unit][] = $leavesummary;
            }
        } else {
            $arr_leavesummary_for_template['esi_to_be_covered'] =  $arr_esi_to_be_covered;
        }
        // debug($arr_leavesummary_for_template['esi_to_be_covered']);exit;
        $this->set('needBranchWiseReport', $needBranchWiseReport);
        $this->set('arr_leavesummary_for_template', $arr_leavesummary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        // $this->set('arr_empleaverequests', $arr_empleaverequests);
        $this->set('report_month', $report_month);
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);

        function num2alpha($n)
        {
            $r = '';
            for ($i = 1; $n >= 0 && $i < 10; $i++) {
                $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
                $n -= pow(26, $i);
            }
            return $r;
        }
        $end_heading = 0;

        switch ($mode) {
            case 'pdf':
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('pdfinteligencereports');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                // $html2pdf = new HTML2PDF('P', 'A4', 'en');

                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage', 'SinglePage', 'UseNone', '100');
                $html2pdf->writeHTML($view_output);
                $str_company_code = $this->Session->read('company_code');
                $html2pdf->Output($str_company_code . '_Intelligence and Non Compliance-' . $report_month . '.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel':
                $month = isset($arr_empleaverequests['0']['payroll_master']['month_year']) ? $arr_empleaverequests['0']['payroll_master']['month_year'] : '0';
                $str_company_code = $this->Session->read('company_code');
                // $file_name = isset($str_company_code) ? $str_company_code . " Payroll Summary" . $month . ".xlsx" : "Payroll Summary" . strtotime() . ".xlsx";
                $file_name = isset($str_company_code) ? $str_company_code . "_Intelligence and Non Compliance" . $f . ".xlsx" : "Intelligence and Non Compliance" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();


                $worksheet->mergeCells('A1:G1');

                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->setCellValueByColumnAndRow(0, 1, "Intelligence and Non Compliance Report for " . $year . " "  . $mname);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(11);

                // $worksheet->mergeCells('A1:G1');
                $worksheet->mergeCells('A2:G2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(14);




                $worksheet->mergeCells('A3:G3');
                // $worksheet->mergeCells('E2:N2');
                $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->setCellValueByColumnAndRow(0, 3, "Details");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(11);



                $worksheet->mergeCells('A4:G4');
                // $worksheet->mergeCells('E2:N2');
                $worksheet->getStyle('A4')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $borderNone = array(
                    'borders' => array(
                        'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_DOUBLE
                        ),
                    )
                );


                //Minimum Wages Non Compliance					
                $worksheet->setCellValueByColumnAndRow(0, 4, "Minimum Wages Non Compliance");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 4)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 4)->getFont()->setSize(11);
                $objPHPExcel->getActiveSheet()
                    ->getStyle('A4')
                    ->getFont()
                    ->getColor()
                    ->setRGB('FF0000');
                //Background color
                $objPHPExcel->getActiveSheet()
                    ->getStyle('A4')
                    ->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F2DCDB');

                $worksheet->mergeCells('A5:G5');
                // $worksheet->mergeCells('E2:N2');
                $worksheet->getStyle('A5')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->setCellValueByColumnAndRow(0, 5, "(Employees who has got Standard Salary less than 12500/Month)");
                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 5)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 5)->getFont()->setSize(11);


                //Background color
                $objPHPExcel->getActiveSheet()
                    ->getStyle('A5')
                    ->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F2DCDB');



                for ($col = 'A'; $col !== 'R'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                //Border style
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );

                //Giving border to details
                $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArray);
                $styleArraySide = array(
                    'borders' => array(
                        'left' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        ),
                        'right' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );

                $styleArrayBottom = array(
                    'borders' => array(
                        'bottom' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        ),
                    )
                );

                $leftstyle = array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    )
                );

                $rightstyle = array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    )
                );

                $centerstyle = array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );

                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(70);




                $rowcount = 4;
                if (isset($needBranchWiseReport) && $needBranchWiseReport == true) {
                    $rowcount = 6;
                    if (count($arr_leavesummary_for_template) != 0) {


                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Standard Gross Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);



                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //edited by megha  standard gross salary 3 end
                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['minwages_noncompliance']) > 0) {
                            foreach ($arr_leavesummary_for_template['minwages_noncompliance'] as $units => $arr_data) {
                                // debug($branch_code);

                                if (count($arr_data) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;
                                    foreach ($arr_data as $val) {

                                        $slno = $i;
                                        $id = $val['ei']['employee_id'];
                                        $name = $val['ei']['EmpName'];
                                        if ($val['ei']['emp_status'] == 1) {
                                            $status = '';
                                        } else {
                                            $status = '(Resigned)';
                                        }
                                        $desi = $val['ei']['designation'];
                                        $join = $val['ei']['joining_date'];
                                        $branch = $val['ei']['branch'];
                                        $std_gross_sal = $val['ect']['emp_anual_ctc'] / 12;

                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $desi);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, round($std_gross_sal, 0));
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                        for ($column = 0; $column < 7; $column++) {
                                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                        }
                                        $rowcount++;
                                        $i++;
                                    }
                                } else {
                                }
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A4')
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');

                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $objPHPExcel->getActiveSheet()->freezePane(false);
                            $rowcount++;
                        }

                        $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                    }

                    // if(isset( $heading2)) {
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                    $objPHPExcel->getActiveSheet()->getStyle('A4:' . 'G5')->applyFromArray($styleArraySide);
                    $objPHPExcel->getActiveSheet()->getStyle('A6:' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    // }

                    $rowcount++;


                    //PF short deduction branch wise
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "PF Short Deduction");
                    $heading2 = $rowcount;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(Employees Standard salary PF deduction and if any employees PF deduction is below 1380 )");
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);

                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'PF Deduction');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');

                        //edited by megha  standard gross salary 3 end
                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['pf_short_deduction']) > 0) {
                            foreach ($arr_leavesummary_for_template['pf_short_deduction'] as $units => $arr_data) {
                                // debug($branch_code);

                                if (count($arr_data) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;
                                    foreach ($arr_data as $val) {

                                        $slno = $i;
                                        $id = $val['ei']['employee_id'];
                                        $name = $val['ei']['EmpName'];
                                        if ($val['ei']['emp_status'] == 1) {
                                            $status = '';
                                        } else {
                                            $status = '(Resigned)';
                                        }
                                        $desi = $val['ei']['designation'];
                                        $join = $val['ei']['joining_date'];
                                        $branch = $val['ei']['branch'];

                                        if (isset($val['ectc']['structure_det_value'])) {
                                            $pf_ded = $val['ectc']['structure_det_value'];
                                        } else {
                                            $pf_ded = $val['es']['structure_det_value'];
                                        }

                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $desi);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, abs(round($pf_ded, 0)));
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                        for ($column = 0; $column < 7; $column++) {
                                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                        }
                                        $rowcount++;
                                        $i++;
                                    }
                                } else {
                                }
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $heading2)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');

                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $objPHPExcel->getActiveSheet()->freezePane(false);
                            $rowcount++;
                        }

                        $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A6:' . 'G' . '6')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                    }

                    if (isset($heading2)) {
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $heading2 . ':' . 'G' . ($heading2 + 1))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($heading2 + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    }
                    $rowcount++;


                    //PF Non Compliance branch wise

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "PF Non Compliance");
                    $heading2 = $rowcount;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(Employees who got Standard salary below 15001 and employees who does not have any PF deductions)");
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Standard Gross Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');


                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['pf_noncompliance']) > 0) {
                            foreach ($arr_leavesummary_for_template['pf_noncompliance'] as $units => $arr_data) {
                                // debug($branch_code);

                                if (count($arr_data) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;
                                    foreach ($arr_data as $val) {

                                        $slno = $i;
                                        $id = $val['ei']['employee_id'];
                                        $name = $val['ei']['EmpName'];
                                        if ($val['ei']['emp_status'] == 1) {
                                            $status = '';
                                        } else {
                                            $status = '(Resigned)';
                                        }
                                        $desi = $val['ei']['designation'];
                                        $join = $val['ei']['joining_date'];
                                        $branch = $val['ei']['branch'];

                                        if (isset($val['ect']['emp_anual_ctc'])) {
                                            $gross = isset($val['ect']['emp_anual_ctc']) ? ($val['ect']['emp_anual_ctc']) / 12 : 0;
                                        } else {
                                            $gross = isset($val['es']['emp_anual_ctc']) ? ($val['es']['emp_anual_ctc']) / 12 : 0;
                                        }

                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $desi);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, abs(round($gross, 0)));
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                        for ($column = 0; $column < 7; $column++) {
                                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                        }
                                        $rowcount++;
                                        $i++;
                                    }
                                } else {
                                }
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $heading2)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $objPHPExcel->getActiveSheet()->freezePane(false);
                            $rowcount++;
                        }

                        $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A6:' . 'G' . '6')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                    }

                    if (isset($heading2)) {
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $heading2 . ':' . 'G' . ($heading2 + 1))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($heading2 + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    }

                    $rowcount++;


                    //ESI Non Compliance

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "ESI Non Compliance");
                    $heading2 = $rowcount;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(Employees who got Standard salary below 21001 and employees who does not have ESI deductions)");
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Standard Gross Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');


                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['esi_noncompliance']) > 0) {
                            foreach ($arr_leavesummary_for_template['esi_noncompliance'] as $units => $arr_data) {
                                // debug($branch_code);

                                if (count($arr_data) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;
                                    foreach ($arr_data as $val) {

                                        $slno = $i;
                                        $id = $val['ei']['employee_id'];
                                        $name = $val['ei']['EmpName'];
                                        if ($val['ei']['emp_status'] == 1) {
                                            $status = '';
                                        } else {
                                            $status = '(Resigned)';
                                        }
                                        $desi = $val['ei']['designation'];
                                        $join = $val['ei']['joining_date'];
                                        $branch = $val['ei']['branch'];


                                        $gross = isset($val['ect']['emp_anual_ctc']) ? ($val['ect']['emp_anual_ctc']) / 12 : 0;


                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $desi);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, abs(round($gross, 0)));
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                        for ($column = 0; $column < 7; $column++) {
                                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                        }
                                        $rowcount++;
                                        $i++;
                                    }
                                } else {
                                }
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $heading2)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $objPHPExcel->getActiveSheet()->freezePane(false);
                            $rowcount++;
                        }

                        $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A6:' . 'G' . '6')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                    }

                    if (isset($heading2)) {
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $heading2 . ':' . 'G' . ($heading2 + 1))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($heading2 + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    }

                    $rowcount++;

                    //WWF/LWF Deductions

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "WWF/LWF Deductions");
                    $heading2 = $rowcount;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(Employees who does not have WWF/LWF deductions)");
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Standard Gross Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');


                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['wwf_lwf_deductions']) > 0) {
                            foreach ($arr_leavesummary_for_template['wwf_lwf_deductions'] as $units => $arr_data) {
                                // debug($branch_code);

                                if (count($arr_data) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;
                                    foreach ($arr_data as $val) {

                                        $slno = $i;
                                        $id = $val['ei']['employee_id'];
                                        $name = $val['ei']['EmpName'];
                                        if ($val['ei']['emp_status'] == 1) {
                                            $status = '';
                                        } else {
                                            $status = '(Resigned)';
                                        }
                                        $desi = $val['ei']['designation'];
                                        $join = $val['ei']['joining_date'];
                                        $branch = $val['ei']['branch'];


                                        $gross = isset($val[0]['standard_salary']) ? $val[0]['standard_salary'] : 0;


                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $desi);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, abs(round($gross, 0)));
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                        for ($column = 0; $column < 7; $column++) {
                                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                        }
                                        $rowcount++;
                                        $i++;
                                    }
                                } else {
                                }
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $heading2)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $objPHPExcel->getActiveSheet()->freezePane(false);
                            $rowcount++;
                        }

                        $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A6:' . 'G' . '6')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                    }

                    if (isset($heading2)) {
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $heading2 . ':' . 'G' . ($heading2 + 1))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($heading2 + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    }

                    $rowcount++;


                    //Professional Tax Deductions





                    //PF Deductions





                                        //ESI Deductions





                    //Attendance Details






                    //Salary Details
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Salary Details");
                    $heading2 = $rowcount;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    $sal_det_head = $rowcount;

                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(Take out all employees data whoes salary get changed [Standard Salary] in this month)");
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Previous Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'New Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');


                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['salary_details']) > 0) {
                            foreach ($arr_leavesummary_for_template['salary_details'] as $units => $arr_data) {
                                // debug($branch_code);

                                if (count($arr_data) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;
                                    // debug($arr_data); exit;
                                    foreach ($arr_data as $val) {

                                        $slno = $i;
                                        $id = isset($val['ei']['employee_id']) ? $val['ei']['employee_id'] : '';
                                        $name = isset($val['ei']['EmpName']) ? $val['ei']['EmpName'] : '';
                                        if ($val['ei']['emp_status'] == 1) {
                                            $status = '';
                                        } else {
                                            $status = '(Resigned)';
                                        }
                                        $desi = isset($val['ei']['designation']) ? $val['ei']['designation'] : '';
                                        $join = isset($val['ei']['joining_date']) ? $val['ei']['joining_date'] : '';


                                        $old_sal = isset($val[0]['old_salary']) ? $val[0]['old_salary'] : '';
                                        $new_sal = isset($val[0]['new_salary']) ? $val[0]['new_salary'] : '';
                                        if ($old_sal != '' && $new_sal != '' && $old_sal != $new_sal && $old_sal != null && $new_sal != null) {
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $desi);
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $old_sal);
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $new_sal);
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                            for ($column = 0; $column < 7; $column++) {
                                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                            }
                                            $rowcount++;
                                            $i++;
                                        }else{
                                           
                                            // $rowcount++;
                                        }
                                    }
                                    // debug($i);
                                    // debug($rowcount); exit;
                                    // debug($i); exit;

                                } else {
                                }
                            }
                            if ($i <= 1) {
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('A' . $sal_det_head)
                                    ->getFont()
                                    ->getColor()
                                    ->setRGB('000000');
                                $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                                $rowcount++;
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $sal_det_head)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $objPHPExcel->getActiveSheet()->freezePane(false);
                            $rowcount++;
                        }
                       
                        $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A6:' . 'G' . '6')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                    }

                    if (isset($heading2)) {
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $heading2 . ':' . 'G' . ($heading2 + 1))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($heading2 + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    }

                    $rowcount++;
                    // $rowcount++;
                    $heading2 = $rowcount;




                    //Variable Details
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $heading2 = $rowcount;
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Variable Details");
                    // $heading2 = $rowcount;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(Take out all employees data who has get any variable component uploaded)");
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Variable');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');


                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['variable_details']) > 0) {
                            foreach ($arr_leavesummary_for_template['variable_details'] as $units => $arr_data) {
                                // debug($branch_code);

                                if (count($arr_data) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;
                                    foreach ($arr_data as $val) {

                                        $slno = $i;
                                        $id = isset($val['ei']['employee_id']) ? $val['ei']['employee_id'] : '';
                                        $name = isset($val['ei']['EmpName']) ? $val['ei']['EmpName'] : '';
                                        if ($val['ei']['emp_status'] == 1) {
                                            $status = '';
                                        } else {
                                            $status = '(Resigned)';
                                        }
                                        $desi = isset($val['ei']['designation']) ? $val['ei']['designation'] : '';
                                        $join = isset($val['ei']['joining_date']) ? $val['ei']['joining_date'] : '';


                                        $variable = isset($val['evu']['salary_head_item_desc']) ? $val['evu']['salary_head_item_desc'] : '';
                                        $amount = isset($val['evu']['uploaded_amount']) ? $val['evu']['uploaded_amount'] : '';

                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $desi);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $variable);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $amount);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                        for ($column = 0; $column < 7; $column++) {
                                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                        }
                                        $rowcount++;
                                        $i++;
                                    }
                                } else {
                                }
                            }
                            $rowcount--;
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $heading2)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $objPHPExcel->getActiveSheet()->freezePane(false);
                            // $rowcount++;
                        }

                        // $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A6:' . 'G' . '6')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                    }

                    if (isset($heading2)) {
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $heading2 . ':' . 'G' . ($heading2 + 1))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($heading2 + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    }

                    $rowcount++;



                    //PF To Be Covered Employees
                    $rowcount++;
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "PF To Be Covered Employees");
                    $heading2 = $rowcount;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                    //Background color
                    $objPHPExcel->getActiveSheet()
                    ->getStyle('A' . $rowcount)
                    ->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F2DCDB');

                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(When PF deduction is not there and Standard Salary [gross]-HRA is below 15001)");
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                                        //Background color
                                        $objPHPExcel->getActiveSheet()
                                        ->getStyle('A' . $rowcount)
                                        ->getFill()
                                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                                        ->getStartColor()
                                        ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'PF Salary (Standard Salary - HRA)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');


                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['pf_to_be_covered']) > 0) {
                            foreach ($arr_leavesummary_for_template['pf_to_be_covered'] as $units => $arr_data) {
                                // debug($branch_code);

                                if (count($arr_data) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;
                                    foreach ($arr_data as $val) {

                                        $slno = $i;
                                        $id = isset($val['ei']['employee_id']) ? $val['ei']['employee_id'] : '';
                                        $name = isset($val['ei']['EmpName']) ? $val['ei']['EmpName'] : '';
                                        if ($val['ei']['emp_status'] == 1) {
                                            $status = '';
                                        } else {
                                            $status = '(Resigned)';
                                        }
                                        $desi = isset($val['ei']['designation']) ? $val['ei']['designation'] : '';
                                        $join = isset($val['ei']['joining_date']) ? $val['ei']['joining_date'] : '';

                                        $branch = isset($val['ei']['branch']) ? $val['ei']['branch'] : '';

                                        $annual_salary = isset($val['ect']['emp_anual_ctc']) ? $val['ect']['emp_anual_ctc'] : 0;
                                        $std_sal = $annual_salary / 12;
                                        $hra = isset($val['ectc']['structure_det_value']) ? $val['ectc']['structure_det_value'] : 0;
                                        $pf_sal = $std_sal - $hra;

                                        if ($pf_sal < 15001) {
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $desi);
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $branch);
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, round($pf_sal, 0));
                                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                            for ($column = 0; $column < 7; $column++) {
                                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                            }
                                            $rowcount++;
                                            $i++;
                                        }
                                    }
                                } else {
                                }
                            }
                            if ($i == 1) {
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('A' . $heading2)
                                    ->getFont()
                                    ->getColor()
                                    ->setRGB('000000');
                                $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $heading2)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $objPHPExcel->getActiveSheet()->freezePane(false);
                            $rowcount++;
                        }

                        $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A6:' . 'G' . '6')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                        $rowcount++;
                    }

                    if (isset($heading2)) {
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($heading2 - 1) . ':' . 'G' . ($heading2 - 1))->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($heading2 - 1). ':' . 'G' . ($heading2 + 1))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($heading2 + 2) . ':' . 'G' . ($rowcount-1))->applyFromArray($styleArray);
                    }

                    // $rowcount++;






                    //ESI To Be Covered Employees
                    // $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    // $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount . ':G' . $rowcount)->applyFromArray($styleArray);
                    // $rowcount++;
                    // $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    // $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                    //     array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    // );
                    // $worksheet->setCellValueByColumnAndRow(0, $rowcount, "ESI To Be Covered Employees");
                    // $heading2 = $rowcount;
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    // $objPHPExcel->getActiveSheet()
                    //     ->getStyle('A' . $rowcount)
                    //     ->getFont()
                    //     ->getColor()
                    //     ->setRGB('FF0000');
                    // //Background color
                    // $objPHPExcel->getActiveSheet()
                    //     ->getStyle('A' . $rowcount)
                    //     ->getFill()
                    //     ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    //     ->getStartColor()
                    //     ->setRGB('F2DCDB');

                    // $rowcount++;

                    // $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    // $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                    //     array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    // );
                    // $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(When ESI deduction is not there and Standard Gross - [Conveyance+Washing Allowance] is below 21001)");
                    // //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    // //Background color
                    // $objPHPExcel->getActiveSheet()
                    //     ->getStyle('A' . $rowcount)
                    //     ->getFill()
                    //     ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    //     ->getStartColor()
                    //     ->setRGB('F2DCDB');

                    // if (count($arr_leavesummary_for_template) != 0) {
                    //     $rowcount++;

                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                    //     // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                    //     // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'ESI Salary (Standard Salary -[Conveyance+Washing Allowance])');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                    //     for ($column = 0; $column < 7; $column++) {
                    //         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                    //     }

                    //     //Background color for heading
                    //     $objPHPExcel->getActiveSheet()
                    //         ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                    //         ->getFill()
                    //         ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    //         ->getStartColor()
                    //         ->setRGB('D9D9D9');


                    //     $rowcount = $rowcount + 1;
                    //     $i = 1;
                    //     $total = array();
                    //     for ($j = 10; $j <= 19; $j++) {
                    //         $total[$j] = 0;
                    //     }
                    //     if (count($arr_leavesummary_for_template['esi_to_be_covered']) > 0) {
                    //         foreach ($arr_leavesummary_for_template['esi_to_be_covered'] as $units => $arr_data) {
                    //             // debug($branch_code);

                    //             if (count($arr_data) > 0) {
                    //                 $tot = 0;
                    //                 // $i = 1;
                    //                 $startrow = $rowcount;
                    //                 foreach ($arr_data as $val) {

                    //                     $slno = $i;
                    //                     $id = isset($val['ei']['employee_id']) ? $val['ei']['employee_id'] : '';
                    //                     $name = isset($val['ei']['EmpName']) ? $val['ei']['EmpName'] : '';
                    //                     if ($val['ei']['emp_status'] == 1) {
                    //                         $status = '';
                    //                     } else {
                    //                         $status = '(Resigned)';
                    //                     }
                    //                     $br = isset($val['ei']['branch']) ? $val['ei']['branch'] : '';
                    //                     $desi = isset($val['ei']['designation']) ? $val['ei']['designation'] : '';
                    //                     $join = isset($val['ei']['joining_date']) ? $val['ei']['joining_date'] : '';



                    //                     $std_sal = isset($val[0]['standard_salary']) ? $val[0]['standard_salary'] : "0";
                                       
                    //                     $conveyance = isset($val[0]['conveyance_allowance']) ? $val[0]['conveyance_allowance'] : "0";
                    //                     $washing = isset($val[0]['washing_allowance']) ? $val[0]['washing_allowance'] : "0";
                    //                     $pf_sal = $std_sal - ($conveyance + $washing);

                    //                     if ($pf_sal < "21001") {
                    //                         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                    //                         $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                    //                         $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                    //                         $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                    //                         $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $br);
                    //                         $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $desi);
                    //                         $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $pf_sal);
                    //                         $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                    //                         for ($column = 0; $column < 7; $column++) {
                    //                             $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                    //                         }
                    //                         $rowcount++;
                    //                         $i++;
                    //                     }
                    //                 }
                    //                 // debug($i); exit;

                                   
                    //             } else {
                    //                 $objPHPExcel->getActiveSheet()
                    //                 ->getStyle('A' . $heading2)
                    //                 ->getFont()
                    //                 ->getColor()
                    //                 ->setRGB('000000');
                                   
                    //                 $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    //                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                    //                 $objPHPExcel->getActiveSheet()->freezePane(false);
                    //             }
                    //         }
                    //         // $rowcount--;
                    //         if($i == 1){
                    //             // $rowcount++;
                    //             $objPHPExcel->getActiveSheet()
                    //             ->getStyle('A' . $heading2)
                    //             ->getFont()
                    //             ->getColor()
                    //             ->setRGB('000000');
                               
                    //             $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    //             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                    //             $objPHPExcel->getActiveSheet()->freezePane(false);
                              
                    //         }else{
                    //             $rowcount--;
                    //         }
                    //     } else {
                    //         $objPHPExcel->getActiveSheet()
                    //             ->getStyle('A' . $heading2)
                    //             ->getFont()
                    //             ->getColor()
                    //             ->setRGB('000000');
                               
                    //         $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    //         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                    //         $objPHPExcel->getActiveSheet()->freezePane(false);
                    //         // $rowcount++;
                    //     }



                    //     // $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    //     //Background color for heading
                    //     $objPHPExcel->getActiveSheet()
                    //         ->getStyle('A6:' . 'G' . '6')
                    //         ->getFill()
                    //         ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    //         ->getStartColor()
                    //         ->setRGB('D9D9D9');
                    // } else {
                    //     $worksheet->mergeCells('A4:G4');
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                    //     $objPHPExcel->getActiveSheet()->freezePane(false);
                    // }

                    // if (isset($heading2)) {
                    //     // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                    //     $objPHPExcel->getActiveSheet()->getStyle('A' . $heading2 . ':' . 'G' . ($heading2 + 1))->applyFromArray($styleArraySide);
                    //     $objPHPExcel->getActiveSheet()->getStyle('A' . ($heading2 + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    // }



                } else { //Employee wise 

                    $rowcount = 6;
                    // Minimum Wages Non Compliance
                    if (count($arr_leavesummary_for_template) != 0) {


                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Standard Gross Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //edited by megha  standard gross salary 3 end
                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }

                        if (count($arr_leavesummary_for_template['minwages_noncompliance']) > 0) {
                            foreach ($arr_leavesummary_for_template['minwages_noncompliance'] as $key => $val) {
                                // debug($branch_code);

                                if (count($val) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;
                                    //foreach ($arr_data as $val) {

                                    $slno = $i;
                                    $id = $val['ei']['employee_id'];
                                    $name = $val['ei']['EmpName'];
                                    if ($val['ei']['emp_status'] == 1) {
                                        $status = '';
                                    } else {
                                        $status = '(Resigned)';
                                    }
                                    $desi = $val['ei']['designation'];
                                    $join = $val['ei']['joining_date'];
                                    $branch = $val['ei']['branch'];
                                    $std_gross_sal = $val['ect']['emp_anual_ctc'] / 12;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $desi);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, round($std_gross_sal, 0));
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                    for ($column = 0; $column < 7; $column++) {
                                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                    }
                                    $rowcount++;
                                    $i++;
                                } else {
                                }
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A4')
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)->applyFromArray($styleArray);
                            $objPHPExcel->getActiveSheet()->freezePane(false);
                            $rowcount++;
                        }

                        $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A6:' . 'G' . '6')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->getStyle('A4:' . 'G4')->applyFromArray($styleArray);
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                    }

                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                    $objPHPExcel->getActiveSheet()->getStyle('A4:' . 'G5')->applyFromArray($styleArraySide);
                    $objPHPExcel->getActiveSheet()->getStyle('A6:' . 'G' . ($rowcount))->applyFromArray($styleArray);

                    $rowcount++;
                    //PF short deduction employee wise
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "PF Short Deduction");
                    $heading2 = $rowcount;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(Employees Standard salary PF deduction and if any employees PF deduction is below 1380 )");
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'PF Deduction');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');

                        //edited by megha  standard gross salary 3 end
                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['pf_short_deduction']) > 0) {
                            foreach ($arr_leavesummary_for_template['pf_short_deduction'] as $units => $val) {
                                // debug($branch_code);

                                if (count($val) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;


                                    $slno = $i;
                                    $id = $val['ei']['employee_id'];
                                    $name = $val['ei']['EmpName'];
                                    if ($val['ei']['emp_status'] == 1) {
                                        $status = '';
                                    } else {
                                        $status = '(Resigned)';
                                    }
                                    $desi = $val['ei']['designation'];
                                    $join = $val['ei']['joining_date'];
                                    $branch = $val['ei']['branch'];

                                    if (isset($val['ectc']['structure_det_value'])) {
                                        $pf_ded = $val['ectc']['structure_det_value'];
                                    } else {
                                        $pf_ded = $val['es']['structure_det_value'];
                                    }

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $desi);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, abs(round($pf_ded, 0)));
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                    for ($column = 0; $column < 7; $column++) {
                                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                    }
                                    $rowcount++;
                                    $i++;
                                } else {
                                }
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $heading2)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                            $objPHPExcel->getActiveSheet()->freezePane(false);
                            $rowcount++;
                        }
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->getStyle('A4:' . 'G4')->applyFromArray($styleArray);
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                    }
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    if (isset($heading2)) {
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $heading2 . ':' . 'G' . ($heading2 + 1))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($heading2 + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    }
                    $rowcount++;

                    //PF Non Compliance employee wise
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "PF Non Compliance");
                    $pfncew = $rowcount;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(Employees who got Standard salary below 15001 and employees who does not have any PF deductions)");
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);

                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Standard Gross Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');

                        //edited by megha  standard gross salary 3 end
                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['pf_noncompliance']) > 0) {
                            foreach ($arr_leavesummary_for_template['pf_noncompliance'] as $units => $val) {
                                // debug($branch_code);

                                if (count($val) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;


                                    $slno = $i;
                                    $id = $val['ei']['employee_id'];
                                    $name = $val['ei']['EmpName'];
                                    if ($val['ei']['emp_status'] == 1) {
                                        $status = '';
                                    } else {
                                        $status = '(Resigned)';
                                    }
                                    $desi = $val['ei']['designation'];
                                    $join = $val['ei']['joining_date'];
                                    $branch = $val['ei']['branch'];

                                    if (isset($val['ect']['emp_anual_ctc'])) {
                                        $gross = isset($val['ect']['emp_anual_ctc']) ? ($val['ect']['emp_anual_ctc']) / 12 : 0;
                                    } else {
                                        $gross = isset($val['es']['emp_anual_ctc']) ? ($val['es']['emp_anual_ctc']) / 12 : 0;
                                    }

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $desi);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, abs(round($gross, 0)));
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                    for ($column = 0; $column < 7; $column++) {
                                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                    }
                                    $rowcount++;
                                    $i++;
                                } else {
                                }
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $pfncew)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                            $rowcount++;
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        }
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                    }
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    if (isset($pfncew)) {
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $pfncew . ':' . 'G' . ($pfncew + 1))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($pfncew + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    }
                    $rowcount++;


                    //ESI Non Compliance employee wise
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "ESI Non Compliance");
                    $pfncew = $rowcount;
                    $esinew = $rowcount;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(Employees who got Standard salary below 21001 and employees who does not have ESI deductions)");
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);

                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Standard Gross Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');

                        //edited by megha  standard gross salary 3 end
                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['esi_noncompliance']) > 0) {
                            foreach ($arr_leavesummary_for_template['esi_noncompliance'] as $units => $val) {

                                if (count($val) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;


                                    $slno = $i;
                                    $id = $val['ei']['employee_id'];
                                    $name = $val['ei']['EmpName'];
                                    if ($val['ei']['emp_status'] == 1) {
                                        $status = '';
                                    } else {
                                        $status = '(Resigned)';
                                    }
                                    $desi = $val['ei']['designation'];
                                    $join = $val['ei']['joining_date'];
                                    $branch = $val['ei']['branch'];


                                    $gross = isset($val['ect']['emp_anual_ctc']) ? ($val['ect']['emp_anual_ctc']) / 12 : 0;


                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $desi);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, abs(round($gross, 0)));
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                    for ($column = 0; $column < 7; $column++) {
                                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                    }
                                    $rowcount++;
                                    $i++;
                                } else {
                                }
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $pfncew)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $rowcount++;
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        }
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $rowcount++;
                        $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                    }
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    if (isset($esinew)) {
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' .  $esinew . ':' . 'G' . ($esinew + 1))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($esinew + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    }
                    $rowcount++;


                    //WWF/LWF Deductions
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "WWF/LWF Deductions");
                    $pfncew = $rowcount;
                    $wwflwf = $rowcount;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(Employees who does not have WWF/LWF deductions)");
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);

                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Standard Gross Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');

                        //edited by megha  standard gross salary 3 end
                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['wwf_lwf_deductions']) > 0) {
                            foreach ($arr_leavesummary_for_template['wwf_lwf_deductions'] as $units => $val) {
                                // debug($branch_code);

                                if (count($val) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;


                                    $slno = $i;
                                    $id = $val['ei']['employee_id'];
                                    $name = $val['ei']['EmpName'];
                                    if ($val['ei']['emp_status'] == 1) {
                                        $status = '';
                                    } else {
                                        $status = '(Resigned)';
                                    }
                                    $desi = $val['ei']['designation'];
                                    $join = $val['ei']['joining_date'];
                                    $branch = $val['ei']['branch'];


                                    $gross = isset($val[0]['standard_salary']) ? $val[0]['standard_salary'] : 0;


                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $desi);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, abs(round($gross, 0)));
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                    for ($column = 0; $column < 7; $column++) {
                                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                    }
                                    $rowcount++;
                                    $i++;
                                } else {
                                }
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $pfncew)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $rowcount++;
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        }
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $pfncew)
                            ->getFont()
                            ->getColor()
                            ->setRGB('000000');
                    }
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);

                    if (isset($wwflwf)) {
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' .  $wwflwf . ':' . 'G' . ($wwflwf + 1))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($wwflwf + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    }

                    $rowcount++;


                    //Professional Tax Deductions




                    //PF Deductions




                    //ESI Deductions




                    //Salary Details
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Salary Details");
                    $pfncew = $rowcount;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    $sal_det_head = $rowcount;
                    $heading2 = $rowcount;
                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(Take out all employees data whoes salary get changed [Standard Salary] in this month)");
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Previous Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'New Salary');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');

                        //edited by megha  standard gross salary 3 end
                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['salary_details']) > 0) {
                            foreach ($arr_leavesummary_for_template['salary_details'] as $units => $val) {
                                // debug($branch_code);

                                if (count($val) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;


                                    $slno = $i;
                                    $id = isset($val['ei']['employee_id']) ? $val['ei']['employee_id'] : '';
                                    $name = ($val['ei']['EmpName']) ? $val['ei']['EmpName'] : '';
                                    if ($val['ei']['emp_status'] == 1) {
                                        $status = '';
                                    } else {
                                        $status = '(Resigned)';
                                    }
                                    $desi = isset($val['ei']['designation']) ? $val['ei']['designation'] : '';
                                    $join = isset($val['ei']['joining_date']) ? $val['ei']['joining_date'] : '';

                                    $old_sal = isset($val[0]['old_salary']) ? $val[0]['old_salary'] : '';
                                    $new_sal = isset($val[0]['new_salary']) ? $val[0]['new_salary'] : '';

                                    if ($old_sal != '' && $new_sal != '' && $old_sal != $new_sal && $old_sal != null && $new_sal != null) {
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $desi);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $old_sal);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $new_sal);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                        for ($column = 0; $column < 7; $column++) {
                                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                        }
                                        $rowcount++;
                                        $i++;
                                    }
                                } else {
                                }
                            }
                            if ($i == 1) {
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('A' . $sal_det_head)
                                    ->getFont()
                                    ->getColor()
                                    ->setRGB('000000');
                                $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                                $rowcount++;
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $sal_det_head)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $rowcount++;
                        }
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                        $rowcount++;
                    }
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    if (isset($heading2)) {
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($heading2) . ':' . 'G' . ($rowcount + 2))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($heading2 + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    }


                    $rowcount++;
                    


                    //Variable Details
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Variable Details");
                    $pfncew = $rowcount;
                    $variabledetails_row = $rowcount;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(Take out all employees data who has get any variable component uploaded)");
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    //Background color
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Variable');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');

                        //edited by megha  standard gross salary 3 end
                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['variable_details']) > 0) {
                            foreach ($arr_leavesummary_for_template['variable_details'] as $units => $val) {
                                // debug($branch_code);

                                if (count($val) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;


                                    $slno = $i;
                                    $id = isset($val['ei']['employee_id']) ? $val['ei']['employee_id'] : '';
                                    $name = ($val['ei']['EmpName']) ? $val['ei']['EmpName'] : '';
                                    if ($val['ei']['emp_status'] == 1) {
                                        $status = '';
                                    } else {
                                        $status = '(Resigned)';
                                    }
                                    $desi = isset($val['ei']['designation']) ? $val['ei']['designation'] : '';
                                    $join = isset($val['ei']['joining_date']) ? $val['ei']['joining_date'] : '';

                                    $variable = isset($val['evu']['salary_head_item_desc']) ? $val['evu']['salary_head_item_desc'] : '';
                                    $amount = isset($val['evu']['uploaded_amount']) ? $val['evu']['uploaded_amount'] : '';


                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $desi);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $variable);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $amount);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                    for ($column = 0; $column < 7; $column++) {
                                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                    }
                                    $rowcount++;
                                    $i++;
                                } else {
                                }
                            }
                            // $rowcount--;
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $pfncew)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'No data available under the selected criteria');
                            $rowcount++;
                            // $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        }
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        // $rowcount++;
                        // $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                    }
                    // $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    if (isset($pfncew)) {
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' .  $pfncew . ':' . 'G' . ($pfncew + 1))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($pfncew + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    }
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $rowcount++;


                    //PF To Be Covered Employees
                    // $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    // $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount . ':G' . $rowcount)->applyFromArray($styleArrayBottom);
                    $pfncew = $rowcount;
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "PF To Be Covered Employees");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFont()
                        ->getColor()
                        ->setRGB('FF0000');
                        //Background color
                        $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F2DCDB');

                    $rowcount++;

                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(When PF deduction is not there and Standard Salary [gross]-HRA is below 15001)");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(false);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    $objPHPExcel->getActiveSheet()
                    ->getStyle('A' . $rowcount)
                    ->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F2DCDB');

                    if (count($arr_leavesummary_for_template) != 0) {
                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'PF Salary (Standard Salary - HRA)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        for ($column = 0; $column < 7; $column++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                        }

                        //Background color for heading
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');

                        //edited by megha  standard gross salary 3 end
                        $rowcount = $rowcount + 1;
                        $i = 1;
                        $total = array();
                        for ($j = 10; $j <= 19; $j++) {
                            $total[$j] = 0;
                        }
                        if (count($arr_leavesummary_for_template['pf_to_be_covered']) > 0) {
                            foreach ($arr_leavesummary_for_template['pf_to_be_covered'] as $units => $val) {
                                // debug($branch_code);

                                if (count($val) > 0) {
                                    $tot = 0;
                                    // $i = 1;
                                    $startrow = $rowcount;


                                    $slno = $i;
                                    $id = isset($val['ei']['employee_id']) ? $val['ei']['employee_id'] : '';
                                    $name = ($val['ei']['EmpName']) ? $val['ei']['EmpName'] : '';
                                    
                                    if (isset($val['ei']['emp_status']) && $val['ei']['emp_status'] == 1) {
                                        $status = '';
                                    } else {
                                        $status = '(Resigned)';
                                    }
                                    
                                    $desi = isset($val['ei']['designation']) ? $val['ei']['designation'] : '';
                                    $join = isset($val['ei']['joining_date']) ? $val['ei']['joining_date'] : '';
                                    $branch = isset($val['ei']['branch']) ? $val['ei']['branch'] : '';

                                    $annual_salary = isset($val['ect']['emp_anual_ctc']) ? $val['ect']['emp_anual_ctc'] : 0;
                                    $std_sal = $annual_salary / 12;
                                    $hra = isset($val['ectc']['structure_det_value']) ? $val['ectc']['structure_det_value'] : 0;
                                    $pf_sal = $std_sal - $hra;
                                    if ($pf_sal < 15001) {
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $desi);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, round($pf_sal, 0));
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                        for ($column = 0; $column < 7; $column++) {
                                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                                        }
                                        $rowcount++;
                                        $i++;
                                    }
                                } else {
                                }
                            }
                            if ($i == 1) {
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('A' . $pfncew)
                                    ->getFont()
                                    ->getColor()
                                    ->setRGB('000000');
                                $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                                $rowcount++;
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A' . $pfncew)
                                ->getFont()
                                ->getColor()
                                ->setRGB('000000');
                            $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                            $rowcount++;
                        }
                    } else {
                        $worksheet->mergeCells('A4:G4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                        $rowcount++;
                    }
                    $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    if (isset($pfncew)) {
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                        $objPHPExcel->getActiveSheet()->getStyle('A' .  $pfncew . ':' . 'G' . ($pfncew + 1))->applyFromArray($styleArraySide);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($pfncew + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    }
                    $rowcount++;





                    //ESI To Be Covered Employees
                    // $esitobecovered_emp_row = $rowcount;
                    // $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    // $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                    //     array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    // );
                    // $worksheet->setCellValueByColumnAndRow(0, $rowcount, "ESI To Be Covered Employees");
                    // $heading2 = $rowcount;
                    // $esitobe = $rowcount;
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    // $objPHPExcel->getActiveSheet()
                    //     ->getStyle('A' . $rowcount)
                    //     ->getFont()
                    //     ->getColor()
                    //     ->setRGB('FF0000');
                    // //Background color
                    // $objPHPExcel->getActiveSheet()
                    //     ->getStyle('A' . $rowcount)
                    //     ->getFill()
                    //     ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    //     ->getStartColor()
                    //     ->setRGB('F2DCDB');

                    // $rowcount++;

                    // $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    // $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                    //     array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    // );
                    // $worksheet->setCellValueByColumnAndRow(0, $rowcount, "(When ESI deduction is not there and Standard Gross - [Conveyance+Washing Allowance] is below 21001)");
                    // //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                    // //Background color
                    // $objPHPExcel->getActiveSheet()
                    //     ->getStyle('A' . $rowcount)
                    //     ->getFill()
                    //     ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    //     ->getStartColor()
                    //     ->setRGB('F2DCDB');

                    // if (count($arr_leavesummary_for_template) != 0) {
                    //     $rowcount++;

                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Joining Date');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                    //     // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Designation');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                    //     // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Joining');
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'ESI Salary (Standard Salary -[Conveyance+Washing Allowance])');
                    //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                    //     for ($column = 0; $column < 7; $column++) {
                    //         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                    //     }

                    //     //Background color for heading
                    //     $objPHPExcel->getActiveSheet()
                    //         ->getStyle('A' . $rowcount . ':' . 'G' . $rowcount)
                    //         ->getFill()
                    //         ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    //         ->getStartColor()
                    //         ->setRGB('D9D9D9');


                    //     $rowcount = $rowcount + 1;
                    //     $i = 1;
                    //     $total = array();
                    //     for ($j = 10; $j <= 19; $j++) {
                    //         $total[$j] = 0;
                    //     }
                    //     if (count($arr_leavesummary_for_template['esi_to_be_covered']) > 0) {
                    //         foreach ($arr_leavesummary_for_template['esi_to_be_covered'] as $units => $val) {
                    //             // debug($branch_code);

                    //             if (count($val) > 0) {
                    //                 $tot = 0;
                    //                 // $i = 1;
                    //                 $startrow = $rowcount;
                    //                 // foreach ($arr_data as $val) {

                    //                 $slno = $i;
                    //                 $id = isset($val['ei']['employee_id']) ? $val['ei']['employee_id'] : '';
                    //                 $name = isset($val['ei']['EmpName']) ? $val['ei']['EmpName'] : '';
                    //                 if ($val['ei']['emp_status'] == 1) {
                    //                     $status = '';
                    //                 } else {
                    //                     $status = '(Resigned)';
                    //                 }
                    //                 $br = isset($val['ei']['branch']) ? $val['ei']['branch'] : '';
                    //                 $desi = isset($val['ei']['designation']) ? $val['ei']['designation'] : '';
                    //                 $join = isset($val['ei']['joining_date']) ? $val['ei']['joining_date'] : '';

                          
                    //                 $std_sal = isset($val[0]['standard_salary']) ? $val[0]['standard_salary'] : "0";
                    //                 //$hra = isset($val['ectc']['structure_det_value']) ? $val['ectc']['structure_det_value'] : 0;
                    //                 $conveyance = isset($val[0]['conveyance_allowance']) ? $val[0]['conveyance_allowance'] : "0";
                    //                 $washing = isset($val[0]['washing_allowance']) ? $val[0]['washing_allowance'] : "0";
                    //                 $pf_sal = $std_sal - ($conveyance + $washing);

                    //                 if ($pf_sal < "21001") {
                    //                     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                    //                     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                    //                     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $name . " " . $status);
                    //                     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $join);
                    //                     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $br);
                    //                     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $desi);
                    //                     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $pf_sal);
                    //                     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                    //                     for ($column = 0; $column < 7; $column++) {
                    //                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->applyFromArray($centerstyle);
                    //                     }
                    //                     $rowcount++;
                    //                     $i++;
                    //                 }
                    //                 // }
                    //             } else {
                    //             }
                    //         }
                    //         // $rowcount--;
                    //     } else {
                    //         $objPHPExcel->getActiveSheet()
                    //             ->getStyle('A' . $heading2)
                    //             ->getFont()
                    //             ->getColor()
                    //             ->setRGB('000000');
                    //         // $worksheet->mergeCells('A' . ($rowcount) . ':G' . ($rowcount));
                    //         // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'No data available under the selected criteria1');
                    //         // $objPHPExcel->getActiveSheet()->freezePane(false);
                    //         // $rowcount++;
                    //     }
                      
                    //     if ($i == 1) {
                    //         $objPHPExcel->getActiveSheet()
                    //             ->getStyle('A' . $heading2)
                    //             ->getFont()
                    //             ->getColor()
                    //             ->setRGB('000000');
                    //         $worksheet->mergeCells('A' . ($rowcount) . ':G' . ($rowcount));
                    //         $objPHPExcel->getActiveSheet()->getStyle('A' . ($rowcount) . ':G' . ($rowcount))->applyFromArray($styleArray);
                    //         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'No data available under the selected criteria');
                    //         // $objPHPExcel->getActiveSheet()->freezePane(false);
                    //     }else{
                    //         $rowcount--;
                    //     }

                    //     // $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                    //     //Background color for heading
                    //     $objPHPExcel->getActiveSheet()
                    //         ->getStyle('A6:' . 'G' . '6')
                    //         ->getFill()
                    //         ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    //         ->getStartColor()
                    //         ->setRGB('D9D9D9');
                    // } else {
                    //     $worksheet->mergeCells('A4:G4');
                    //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 4, 'No data available under the selected criteria');
                    //     $objPHPExcel->getActiveSheet()->freezePane(false);
                    // }
                    // if (isset($esitobe)) {
                    //     // $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'G3')->applyFromArray($styleArrayBottom);
                    //     $objPHPExcel->getActiveSheet()->getStyle('A' .  $esitobe . ':' . 'G' . ($esitobe + 1))->applyFromArray($styleArraySide);
                    //     $objPHPExcel->getActiveSheet()->getStyle('A' . ($esitobe + 2) . ':' . 'G' . ($rowcount))->applyFromArray($styleArray);
                    // }



                }
                //                              //Hide gridlines
                $objPHPExcel->getActiveSheet()->setShowGridlines(false);

                //Border
                $lastrow = $objPHPExcel->getActiveSheet()->getHighestRow();
                // $objPHPExcel->getActiveSheet()->getStyle('A4:' . 'G' . ($rowcount))->applyFromArray($styleArray);
                // $objPHPExcel->getActiveSheet()->getStyle('A4:' . 'G' . ($heading2 - 1))->applyFromArray($styleArray);

                // $objPHPExcel->getActiveSheet()->getStyle('A'.($heading2 ).':' . 'G' . ($heading2 + 1))->applyFromArray($styleArraySide);

                // $objPHPExcel->getActiveSheet()->getStyle('A'.($heading2 + 2).':' . 'G' . $rowcount)->applyFromArray($styleArray);


                $objPHPExcel->getActiveSheet()->setTitle('Intelligence and Non Compliance');
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
            default:
                $this->set('mode', '');
                $this->render('inteligencereports');
                break;
        }
    }
}
