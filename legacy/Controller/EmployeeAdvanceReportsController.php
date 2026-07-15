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
ini_set('max_execution_time', 300);

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class EmployeeAdvanceReportsController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'EmployeeAdvanceReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array(
        'EditPunches', 'Attendance', 'CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'DeviceAttendance', 'Departments', 'Grades', 'Verticals', 'Units', 'ReportCriterias', 'AttendanceRegister', 'AttendanceRegisterReport', 'DbConfig', 'MobileUserauditor',
        'CompanyContactInfo', 'EmployeeAdvance', 'ReportAudit'
    ); //santhu
    public $components = array('MasterdataManagement');

    public function hrreports()
    {
        $arr_reporttypes = array(
            //drop down            
            //'employee' => 'Employee Information',
            'Advance' => 'Salary Advance'
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */

    public function changereporttype($type = '')
    {
        $this->autoRender = FALSE;
        // debug($type);die();
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                case 'employee':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Advance':
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
                $this->set('index', $index);
                $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
                $model = ($model == 'Units') ? 'Branches' : $model;
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
        $user_group = $this->Session->read('user_group'); // Edited by Akshay on 11-3-2025
        $model = $str_criteria;
        $arr_requestdata = $this->request->data;
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');
            if ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            }
            // Edited by Akshay on 28-1-2025
            elseif ($model == 'Units') {
                $conditions = array();
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $emp_pkey = $this->Session->read('emp_fkey');
                    $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $conditions = array("Units.status" => 1, "branch_code" => $is_ho);
                    } else {
                        $conditions = array("Units.status" => 1);
                    }
                }

                // Edited by Akshay on 11-3-2025
                elseif ($user_group == 2) {
                    $company_code = $this->Session->read('company_code');
                    if($company_code == 'GAAR' || $company_code == 'HRBL'){
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $user_id = $this->Session->read("login_user_id"); //user id
                        $special_access = $this->EmployeeDetails->query("SELECT COUNT(*) AS special_access FROM special_access WHERE user_id = '$user_id' AND status = 1;");
                        $special_access = ($special_access[0][0]['special_access'] > 0) ? 1 : 0;
    
                        if ($special_access != 1) {
                            $directors_branch = $this->EmployeeDetails->query("SELECT get_directors_branch_code() AS branch;");
                            $directors_branch = isset($directors_branch[0][0]['branch']) ? $directors_branch[0][0]['branch'] : '';
                            $conditions = array("status" => 1, "branch_code !=" => $directors_branch);
                        }
                    }
                }
                // End
            }
            // End
            else {
                $conditions = array("status" => 1);
            }

            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions)));
            $arr_criteriaItems = array();
            $key = 0;
            switch ($model) {

                case 'Units':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['branch_code'];
                        $arr_criteriaItems[$key]['text'] = $value['branch_name'];
                        $key++;
                    }
                    break;
                case 'EmployeeDetails':
                    //edited by arul - changed empid ad company id
                    $fields = 'emp_pkey,status,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name,".")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
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


                    $arr_order = array("EmployeeDetails.first_name" => "ASC");
                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                        $conditions = array("status in(1,2)");
                    } else {

                        $conditions = array("status" => 1);
                    }

                    // Edited by Akshay on 11-3-2025
                    if ($user_group == 2) {
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $company_code = $this->Session->read('company_code');
                        if($company_code == 'GAAR' || $company_code == 'HRBL'){
                            $user_id = $this->Session->read("login_user_id"); //user id
                            $special_access = $this->EmployeeDetails->query("SELECT COUNT(*) AS special_access FROM special_access WHERE user_id = '$user_id' AND status = 1;");
                            $special_access = ($special_access[0][0]['special_access'] > 0) ? 1 : 0;
    
                            if ($special_access != 1) {
                                $directors_branch = $this->EmployeeDetails->query("SELECT get_directors_branch_code() AS branch;");
                                $directors_branch = isset($directors_branch[0][0]['branch']) ? $directors_branch[0][0]['branch'] : '';
                                $conditions['branch_code !='] = $directors_branch;
                            }
                        }
                    }
                    // End

                    // Edited by Akshay on 28-1-2025
                    $user = $this->Session->read('company_code');
                    $user_group = $this->Session->read('user_group');
                    if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $emp_pkey = $this->Session->read('emp_fkey');
                        $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                        $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                        if ($is_ho != 1) {
                            $conditions[] = array("EmployeeDetails.branch_code" => $is_ho);
                        }
                    }
                    // End
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

    public function reportAudit($type, $mode)
    {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'Advance':
                $dataForHistory['report_type'] = "Employee Advance Report";
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
            }
            $items_array[] = isset($arr_form_data[$criteria]) ? implode(",", $arr_form_data[$criteria]) : '';
            $items_count_array[] = isset($arr_form_data[$criteria]) ? count($arr_form_data[$criteria]) : 0;

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

    //function for dropdown
    public function generatereport($type = '', $mode = '')
    {
        $this->autoRender = false;
        //debug($mode);
        switch ($type) {
            case 'Advance':
                $this->generateemployeeadvance($mode);
                break;

            case 'MobilelocationRep':
                $this->generatemobilelocationreport($type, $mode);
                break;
            default:
                return false;
                break;
        }
        $this->reportAudit($type, $mode);
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

    private function generateemployeeadvance($mode = '')
    {

        $arr_form_data = $_REQUEST;
        //debug($arr_form_data);
        $this->EmployeeAdvance->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $needBranchWiseReport = false;
        $conditions = array();
        $conditions[] = 'affected_month >="' . $from . '" and affected_month<="' . $to . '"';
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            //   belongs to branch section added by megha start 1...
            $needBranchWiseReport = false;
            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }
            $this->set('needBranchWiseReport', $needBranchWiseReport);
            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }


        //        for ($i = 1; $i <= $int_criterias_count; $i++) {
        //            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
        //
        //            if ($str_criteria_item == 'Units') {
        //                $needBranchWiseReport = true;
        //            }
        //        if($str_criteria_item == ''){
        //               echo "<h1>No Criteria Selected</h1>";
        //                die();
        //            }
        //            
        //            if(!isset($arr_form_data[$str_criteria_item])){
        //                echo "<h1>No Criteria Selected</h1>";
        //                die();
        //            }
        //            $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array(
        //                                "fields" => "reportcriteria,reportcriteria_field",
        //                                "conditions" => array(
        //                                    "reporttype" => "Advance",
        //                                    "status" => 1,
        //                                    'reportcriteria' => $str_criteria_item
        //                                )
        //            )));
        //            //debug($str_criteria_item);
        //            if (isset($arr_reportcriterias[0]['reportcriteria_field']) && isset($arr_form_data[$str_criteria_item])) {
        //                $conditions[] = $arr_reportcriterias[0]['reportcriteria'] . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
        //            }
        //        }
        $condition = "and EmployeeDetails.status = '1'  ";
        //        debug($arr_form_data);
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and EmployeeDetails.status in ('1','2') ";
        }
        $month_condition = " and au.affected_month like '%$from%' ";
        $str_conditions = implode(' AND ', $conditions);
        $arr_emp_advance_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            $k = 0;
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    //edited by ASHIN on 06-08-24                
                    $arr_employee_advance = $this->EmployeeAdvance->query("select au.*,ei.*,uc.user_id,tr.last_approved_working_date,EmployeeDetails.branch_code,EmployeeDetails.status,Units.branch_name
                            from emp_details EmployeeDetails
                            INNER join emp_advance au on (EmployeeDetails.emp_pkey = au.emp_fkey) 
                            LEFT JOIN branches AS Units ON (EmployeeDetails.branch_code = Units.branch_code)
                            LEFT join employee_info ei on (EmployeeDetails.emp_pkey= ei.emp_pkey)
                            LEFT join user_credentials uc on (uc.emp_fkey= ei.emp_pkey)
                            LEFT join termination tr on (tr.emp_fkey= ei.emp_pkey and tr.status = 1)
                            where  au.is_credited = 'N' and au.emp_fkey= '$leavepolicygroupid'
                            and au.status=1  
                            $condition $month_condition");
                    if (!empty($arr_employee_advance)) {
                        $arr_emp_advance_template[] = $arr_employee_advance;
                    }
                } else {
                    //EDITED BY ASHIN ON 07-08-24               
                    $arr_employee_advance = $this->EmployeeAdvance->query("select au.*,ei.*,uc.user_id,tr.last_approved_working_date,EmployeeDetails.branch_code,EmployeeDetails.status,Units.branch_name
                            from emp_details EmployeeDetails
                            INNER join emp_advance au on (EmployeeDetails.emp_pkey = au.emp_fkey) 
                            LEFT JOIN branches AS Units ON (EmployeeDetails.branch_code = Units.branch_code)
                            LEFT join employee_info ei on (EmployeeDetails.emp_pkey= ei.emp_pkey)
                            LEFT join user_credentials uc on (uc.emp_fkey= ei.emp_pkey)
                            LEFT join termination tr on (tr.emp_fkey= ei.emp_pkey and tr.status = 1)
                            where  au.is_credited = 'N' and EmployeeDetails.branch_code= '$leavepolicygroupid'
                            and au.status=1  
                            $condition $month_condition
                            ORDER BY ei.EmpName ASC");

                    // debug("select au.*,ei.*,uc.user_id,tr.last_approved_working_date,EmployeeDetails.branch_code,EmployeeDetails.status,Units.branch_name
                    //                         from emp_details EmployeeDetails
                    //                         INNER join emp_advance au on (EmployeeDetails.emp_pkey = au.emp_fkey) 
                    //                         LEFT JOIN branches AS Units ON (EmployeeDetails.branch_code = Units.branch_code)
                    //                         LEFT join employee_info ei on (EmployeeDetails.emp_pkey= ei.emp_pkey)
                    //                         LEFT join user_credentials uc on (uc.emp_fkey= ei.emp_pkey)
                    //                         LEFT join termination tr on (tr.emp_fkey= ei.emp_pkey and tr.status = 1)
                    //                         where  au.is_credited = 'N' and EmployeeDetails.branch_code= '$leavepolicygroupid'
                    //                         and au.status=1  
                    //                         $condition $month_condition");
                    if (!empty($arr_employee_advance)) {
                        $arr_emp_advance_template[] = $arr_employee_advance;
                    }
                }
            }
        }

        $arr_emp_advance_template_new = array();
        //debug($arr_emp_advance_template);
        if ($needBranchWiseReport) {
            //Parse array for branchwise report
            foreach ($arr_emp_advance_template as $employee_advance1) {
                foreach ($employee_advance1 as $employee_advance) {

                    $branch_code = isset($employee_advance['EmployeeDetails']['branch_code']) ? $employee_advance['EmployeeDetails']['branch_code'] : '';
                    $branch_name = isset($employee_advance['Units']['branch_name']) ? $employee_advance['Units']['branch_name'] : '';
                    if ($branch_code != '') {
                        if (!isset($arr_emp_advance_template_new[$branch_code])) {
                            $arr_emp_advance_template_new[$branch_code] = array(
                                'branch_name' => $branch_name,
                                'employeeadvance' => array()
                            );
                        }
                        $request = array();
                        $request['status'] = isset($employee_advance['EmployeeDetails']['status']) && $employee_advance['EmployeeDetails']['status'] == "2" ? '(Resigned)' : '';
                        $request['emp_name'] = isset($employee_advance['ei']['EmpName']) ? $employee_advance['ei']['EmpName'] : '';
                        $request['employee_id'] = isset($employee_advance['ei']['employee_id']) ? $employee_advance['ei']['employee_id'] : '';
                        $request['branch'] = isset($employee_advance['eu']['branch']) ? $employee_advance['ei']['branch'] : '';
                        $request['designation'] = isset($employee_advance['ei']['designation']) ? $employee_advance['ei']['designation'] : '';
                        $request['department'] = isset($employee_advance['ei']['department']) ? $employee_advance['ei']['department'] : '';
                        $request['advance_amount'] = isset($employee_advance['au']['advance_amount']) ? $employee_advance['au']['advance_amount'] : '';
                        $request['affected_month'] = isset($employee_advance['au']['affected_month']) ? $employee_advance['au']['affected_month'] : '';
                        $request['created_date'] = isset($employee_advance['au']['created_date']) ? $employee_advance['au']['created_date'] : '';
                        $request['userid'] = isset($employee_advance['uc']['user_id']) ? $employee_advance['uc']['user_id'] : '';
                        $request['remarks'] = isset($employee_advance['au']['remarks']) ? $employee_advance['au']['remarks'] : '';
                        $request['modified_date'] = isset($employee_advance['au']['modified_date']) ? $employee_advance['au']['modified_date'] : '';
                        $request['join'] = isset($employee_advance['ei']['joining_date']) ? $employee_advance['ei']['joining_date'] : '';
                        $request['termin'] = isset($employee_advance['tr']['last_approved_working_date']) ? $employee_advance['tr']['last_approved_working_date'] : '';

                        $arr_emp_advance_template_new[$branch_code]['employeeadvance'][] = $request;
                        //debug($arr_emp_advance_template);
                    }
                }
            }
        } else {
            //Parse array for simple report
            foreach ($arr_emp_advance_template as $employee_advance1) {
                foreach ($employee_advance1 as $employee_advance) {
                    $request = array();
                    $request['status'] = isset($employee_advance['EmployeeDetails']['status']) && $employee_advance['EmployeeDetails']['status'] == "2" ? '(Resigned)' : '';
                    $request['emp_name'] = isset($employee_advance['ei']['EmpName']) ? $employee_advance['ei']['EmpName'] : '';
                    $request['employee_id'] = isset($employee_advance['ei']['employee_id']) ? $employee_advance['ei']['employee_id'] : '';
                    $request['branch'] = isset($employee_advance['ei']['branch']) ? $employee_advance['ei']['branch'] : '';
                    $request['designation'] = isset($employee_advance['ei']['designation']) ? $employee_advance['ei']['designation'] : '';
                    $request['department'] = isset($employee_advance['ei']['department']) ? $employee_advance['ei']['department'] : '';
                    $request['advance_amount'] = isset($employee_advance['au']['advance_amount']) ? $employee_advance['au']['advance_amount'] : '';
                    $request['affected_month'] = isset($employee_advance['au']['affected_month']) ? $employee_advance['au']['affected_month'] : '';
                    $request['created_date'] = isset($employee_advance['au']['created_date']) ? $employee_advance['au']['created_date'] : '';
                    $request['userid'] = isset($employee_advance['uc']['user_id']) ? $employee_advance['uc']['user_id'] : '';
                    $request['modified_date'] = isset($employee_advance['au']['modified_date']) ? $employee_advance['au']['modified_date'] : '';
                    $request['remarks'] = isset($employee_advance['au']['remarks']) ? $employee_advance['au']['remarks'] : '';
                    $request['join'] = isset($employee_advance['ei']['joining_date']) ? $employee_advance['ei']['joining_date'] : '';
                    $request['termin'] = isset($employee_advance['tr']['last_approved_working_date']) ? $employee_advance['tr']['last_approved_working_date'] : '';
                    $arr_emp_advance_template_new['employeeadvance'][] = $request;
                }
            }
        }

        $this->set('needBranchWiseReport', $needBranchWiseReport);
        $this->set('arr_emp_advance_template', $arr_emp_advance_template_new);

        //debug($arr_emp_advance_template_new);
        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('Reportempadvance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('Emp_Advance_report.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "Salary_Advance" . $report_month . ".xlsx" : "Salary_Advance" . strtotime() . ".xlsx";              //edited by ASHIN on 06-08-24
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();

                //$worksheet->setCellValueByColumnAndRow(0, 1, "Salary Advance Report - ".$report_month."");
                $worksheet->setCellValueByColumnAndRow(0, 1, "Salary Advance - " . $mname . "  "  . $year);          //edited by ASHIN on 06-08-24
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:N1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );


                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:N2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                if (isset($needBranchWiseReport) && $needBranchWiseReport == true) {

                    if (!empty($arr_emp_advance_template_new)) {

                        $columncount = 0;
                        $rowcount = 3;


                        //$branchname = $emp_advance['branch_name'];
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $branchname);
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        // $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        //          $worksheet->mergeCells('A4:A6');
                        //           $worksheet->getStyle('A4')->getAlignment()->applyFromArray(
                        //         array('horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,)
                        // );

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');     //edited by ASHIN on 06-08-24
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);

                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Joining Date');
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        //                    
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Advance Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Affected Month');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Created Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Modified Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                        $k = 1;
                        $rowcount = 4;

                        // debug($arr_emp_advance_template_new); exit();

                        // if(!empty($arr_emp_advance_template_new)){
                        foreach ($arr_emp_advance_template_new as  $emp_advance) {
                            // $rowcount = $rowcount + 1;
                            $branch = $emp_advance['branch_name'];
                            $arr_data = $emp_advance['employeeadvance'];

                            // debug($arr_data); exit();

                            if (count($arr_data) >= 0) {

                                foreach ($arr_data as $key => $val) {

                                    $empstatus = $val['status'];
                                    $name = $val['emp_name'] . $empstatus;
                                    $emp_id = $val['employee_id'];
                                    //$branch = $val['branch'];
                                    $des = $val['designation'];
                                    $dept = $val['department'];
                                    $amt = $val['advance_amount'];
                                    $month = $val['affected_month'];
                                    $remark = $val['remarks'];
                                    $modified_date = $val['modified_date'];
                                    $create = date('Y-m-d', strtotime($val['created_date']));

                                    $userid = $val['userid'];
                                    $join = $val['join'];
                                    $termin = $val['termin'];

                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $k);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $emp_id);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $userid);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $name);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $join);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $branch);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $dept);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $des);
                                    // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $join);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), ($rowcount), $termin);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, $amt);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, $month);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, $create);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, $modified_date);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, $remark);
                                    $rowcount++;
                                    $k++;
                                }

                                $BStyle = array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN
                                        )
                                    )
                                );
                                $row = $rowcount - 1;
                                $objPHPExcel->getActiveSheet()->getStyle('A1:N' . $row)->applyFromArray($BStyle);
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('A4:N400')
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


                                //edited by ASHIN on 06-08-24
                            } else {
                                $msg = 'No data available under the selected criteria.';
                                $rowcount = 3;
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $msg);
                            }
                            // $rowcount++;
                        }
                    } else {
                        $cstyle = array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            )
                        );

                        $objPHPExcel->getActiveSheet()->getStyle("A3:N3")->applyFromArray($cstyle);
                        $msg = 'No data available under the selected criteria.';
                        $worksheet->mergeCells('A3:N3');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('A', 3, $msg);
                    }
                } else {


                    if (!empty($arr_emp_advance_template_new['employeeadvance'])) {
                        $arr_data = $arr_emp_advance_template_new['employeeadvance'];
                    } else {
                        $arr_data = '';
                    }
                    // debug($arr_data); exit();
                    if (!empty($arr_data)) {

                        $rowcount = 3;
                        $columncount = 0;

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');       //edited by ASHIN on 06-08-24
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);

                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Joining Date');
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        //                    
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Advance Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Affected Month');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Created Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Modified Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;

                        // if(!empty($arr_emp_advance_template_new['employeeadvance'])){
                        //     $arr_data = $arr_emp_advance_template_new['employeeadvance'];
                        // }
                        // else{
                        //     $arr_data='';
                        // }
                        //     // debug($arr_data); exit();
                        //     if (!empty($arr_data)) {


                        $k = 1;
                        foreach ($arr_data as $val) {
                            $empstatus = $val['status'];
                            $name = $val['emp_name'] . $empstatus;
                            $emp_id = $val['employee_id'];
                            $des = $val['designation'];
                            $dept = $val['department'];
                            $branch = $val['branch'];
                            $amt = $val['advance_amount'];
                            $month = $val['affected_month'];
                            //edited by megha removed time
                            $create = date('Y-m-d', strtotime($val['created_date']));
                            $userid = $val['userid'];
                            $join = $val['join'];
                            $termin = $val['termin'];
                            $remark = $val['remarks'];
                            $modified_date = $val['modified_date'];
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $k);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $emp_id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $userid);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $name);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $branch);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $des);
                            // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), ($rowcount), $termin);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, $amt);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, $month);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, $create);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, $modified_date);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, $remark);
                            $rowcount = $rowcount + 1;
                            $k++;
                        }
                        $BStyle = array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                        );
                        $row = $rowcount - 1;
                        $objPHPExcel->getActiveSheet()->getStyle('A1:N' . $row)->applyFromArray($BStyle);
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A4:N400')
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    } else {
                        $cstyle = array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            )
                        );

                        $objPHPExcel->getActiveSheet()->getStyle("A3:N3")->applyFromArray($cstyle);

                        $msg = 'No data available under the selected criteria.';
                        $worksheet->mergeCells('A3:N3');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('A', 3, $msg);
                    }
                }
                $objPHPExcel->getActiveSheet()->setTitle('Salary Advance');
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
                $this->set('month', $report_month);
                $this->set('mode', '');
                $this->render('Reportempadvance');
                // break;
        }
    }

    private function generatesummaryreport($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');
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
        $fd = $arr_form_data['reportfrom'] . ' ' . '00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }

        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $fields = 'EditPunches.*,Branch.branch_name,EmployeeDetails.first_name,EmployeeDetails.last_name';
                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branch',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EditPunches.branch_code = Branch.branch_code',
                            'Branch.status=1'
                        )
                    ),
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmployeeDetails',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EditPunches.emp_id = EmployeeDetails.emp_id',
                            'EmployeeDetails.status=1'
                        )
                    )
                );
                $conditions[] = 'EmployeeDetails.emp_pkey="' . $leavepolicygroupid . '" and EditPunches.DEVICEID = 0 and EditPunches.LOGDATE between "' . $from . '" and "' . $to . '"';
                $arr_leavepolicy_details = $this->EditPunches->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions));

                $arr_leavepolicydetails_for_template[] = array(
                    'summary' => $arr_leavepolicy_details,
                    // 'employees'=>$arr_leavepolicy_employees
                );
            }
            //      
            //              foreach ($arr_leavepolicydetails_for_template as $key => $value) {
            //                  foreach($value['summary'] as $ky => $vaal){
            //            $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P"));
            //            $int_days_leave = count(array_keys($vaal["AttendanceRegister"], "L"));
            //            $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "HO"));
            //
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $int_days_present;
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave;
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
            //            }
            //        }
            // debug($resp_register);
            //   debug($arr_leavepolicydetails_for_template);  

            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);

            //Set informations needed for report

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('reportsummary');
                    //   debug($view_output);
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('L', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('reportsummary.pdf', 'D');
                    // $this->render('reportsummary');                
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_attendane.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Summary Attendance Employees Report");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells('A1:F1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }

                    $rowcount = 2;
                    $columncount = 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee Id');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Joining Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Present Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Leave Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Holiday Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                    $columnindex = $columncount + 9;
                    foreach ($arr_dates as $key => $date) {

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->getFont()->setBold(true);
                        $columnindex++;
                    }
                    $rowcount = 3;
                    //$rowcount1=3;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $branch = isset($value['summary']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : 'No Datas Found Under This Branch';
                        //echo $branch;die();                     
                        //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,'branch');

                        $arr_data = $value['summary'];
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                        if (count($arr_data) >= 0) {
                            foreach ($arr_data as $key => $val) {
                                $columnindex = 0;
                                $name = $val['AttendanceRegister']['emp_name'];
                                //$name=$name.$key;
                                $present = $val['AttendanceRegister']['days_present'];
                                $leave = $val['AttendanceRegister']['days_leave'];
                                $holidays = $val['AttendanceRegister']['days_holidays'];
                                $id = $val['Info']['employee_id'];
                                $des = $val['Info']['designation'];
                                $join = $val['Info']['joining_date'];
                                $department = $val['Info']['department'];
                                $branch = $val['Info']['branch'];
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $des);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $join);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $department);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $present);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $leave);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $holidays);

                                $columnindex = $columnindex + 9;
                                foreach ($arr_dates as $key => $date) {
                                    $newIndex = 'FIELD' . ($key + 1);
                                    $dta = $val['AttendanceRegister'][$newIndex];
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                    $columnindex++;
                                }

                                $rowcount++;
                            }
                        }
                        $rowcount1 = $rowcount + 1;
                    }

                    $objPHPExcel->getActiveSheet()->setTitle('Attendance Policy');
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
                    $this->render('reportsummary');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }
}
