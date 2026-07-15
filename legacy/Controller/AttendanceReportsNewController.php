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
ini_set('max_execution_time', 30000);

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class attendanceReportsNewController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'AttendanceReportsNew';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Menu', 'Attendance', 'CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'DeviceAttendance', 'Departments', 'Grades', 'Verticals', 'Units', 'ReportCriterias', 'AttendanceRegister', 'AttendanceRegisterReport', 'DbConfig', 'MobileUserauditor', 'CompanyContactInfo', 'ReportAudit'); //santhu
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

    public function hrreportsNew()
    {
        $this->Menu->useDbConfig = $this->Session->read('ds');
        //edited by athira on 04-02-2024
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        //end
        $user_group = $this->Session->read('user_group');
        $user = strtoupper($this->Session->read('company_code'));

        if ($user == 'MBCT' || $user == 'DEMO' || $user == 'GEDE' || $user == 'DDP' || $user == 'GLET' || $user == 'GAAR' || $user == 'ABSG') { // Edited by Akshay on 23-1-2025
            $arr_reporttypes = array(
                'Attendance' => 'Attendance Register_New',
                'VerifiedAttendance' => 'Attendance Register', //Changed as per requirement Task #11430
                'DetailedAttendance' => 'Detailed Attendance Reports',
                'OvertimeReport' => 'Overtime Reports',
                'Overtime' => 'Approved Over Time', //Edited by Akshay on 4-4-2024
                'Dashboard' => 'Employee Check-in/out logs',
                'regularisation' => 'Attendance Regularisation',
                //Edited by Akshay on 13-2-2024
                'movements' => 'Attendance Movements',
                'LOP' => 'LOP', // Edited by Akshay
                'nonpunched' => 'Non-Punched & Non-Attendance',        //changes anukrishnan 17-01-2025
                // 'nonattendance' => 'Non-Attendance',  //changes anukrishnan 17-01-2025
            );
       } else {
                $arr_reporttypes = array(
                    //'employee' => 'Employee Information',
                    'VerifiedAttendance' => 'Attendance Register', //Changed as per requirement Task #11430
                    'DetailedAttendance' => 'Detailed Attendance Reports',
                    //'AttendanceRep' => 'Attendance Reports',
                    //'MobilelocationRep' => 'Mobile User Location Reports',
                    'OvertimeReport' => 'Overtime Reports',
                    'Overtime' => 'Approved Over Time', //Edited by Akshay on 4-4-2024
                    'Dashboard' => 'Employee Check-in/out logs',
                    'regularisation' => 'Attendance Regularisation',
                );
            }
        
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
                case 'Attendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;

                case 'VerifiedAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;

                case 'AttendanceRep':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'DetailedAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //santhu
                case 'TimeAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //Sanju
                case 'Dashboard':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'MobilelocationRep':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Overtime':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'OvertimeReport':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'regularisation':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //Edited by Akshay on 13-2-2024
                case 'movements':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                // Edited by Akshay on 10-12-2024
                case 'LOP':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type), "order" => array("reportcriteria_desc ASC")))));
                    break;
                // End
                //changes anukrishnan 17-01-2025 open
                case 'nonpunched':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'nonattendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //changes anukrishnan 17-01-2025 close
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
        $conditions = array();
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');

            $arr_order = array();
            if ($model == 'Departments') {
                $arr_order = array("Departments.dept_name" => "ASC");
                // Edited by Akshay on 28-1-2025
                $user = $this->Session->read('company_code');
                $user_group = $this->Session->read('user_group');
                if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $emp_pkey = $this->Session->read('emp_fkey');
                    $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $arr_department = $this->EmployeeDetails->query("SELECT emp_dept AS dept_code FROM emp_proff WHERE emp_fkey = $emp_pkey;");
                        $dept_code = isset($arr_department[0]['emp_proff']['dept_code']) ? $arr_department[0]['emp_proff']['dept_code'] : 0;
                        $conditions[] = array("Departments.dept_code" => $dept_code, "Departments.status" => 1);
                    }
                } else {
                    $conditions = array("status" => 1);
                }
                // End
            } else if ($model == 'Grades') {
                $arr_order = array("Grades.grade_name" => "ASC");
                $conditions = array("status" => 1);
            } else if ($model == 'Verticals') {
                $arr_order = array("Verticals.vertical_name" => "ASC");
                $conditions = array("status" => 1);
            } elseif ($model == 'Units') {
                $user_group = $this->Session->read('user_group');
                $emp_pkey = $this->Session->read('emp_fkey');
                $feature_id = $this->Session->read('current_feature_id');
                $context = $this->MasterdataManagement->getFeatureAccessContext();

                if ($user_group == 1) {
                    $branches = $this->MasterdataManagement->getBranchesForAll();
                } else if ($context['has_access']) {
                    if ($context['is_hierarchy']) {
                        $branches = $this->MasterdataManagement->getHierarchyBranches($emp_pkey);
                    } else {
                        $branches = $this->MasterdataManagement->getAllocatedBranches($emp_pkey, $feature_id);
                    }
                } else {
                    $branches = $this->MasterdataManagement->getOwnBranch($emp_pkey);
                }

                $arr_criteriaItems = array();
                if (!empty($branches)) {
                    foreach ($branches as $key => $b) {
                        $arr_criteriaItems[$key]['key'] = $b['b']['branch_code'];
                        $arr_criteriaItems[$key]['text'] = $b['b']['branch_name'];
                    }
                }
                echo json_encode($arr_criteriaItems);
                $this->autoRender = false;
                return;
            } else if ($model == 'DayTimeProcedures') {
                $arr_order = array("DayTimeProcedures.day_time_desc" => "ASC");
                $conditions = array("active" => 1);
            } else if ($model == 'LeavePolicyGroup') {
                $arr_order = array("LeavePolicyGroup.LEAVEPOLICY_GROUP_NAME" => "ASC");
                $conditions = array("status" => 1);
            } else {
                $conditions = array("status" => 1);
            }

            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions, "order" => $arr_order)));
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
                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,status';
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
                    $arr_order = array("CONCAT(EmployeeDetails.first_name, ifnull(EmployeeDetails.last_name,''))" => "ASC");

                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                        $conditions[] = array("status in(1,2)");
                    } else {
                        $conditions[] = array("status" => 1);
                    }

                    $user_group = $this->Session->read('user_group');
                    $emp_pkey = $this->Session->read('emp_fkey');
                    $feature_id = $this->Session->read('current_feature_id');
                    $context = $this->MasterdataManagement->getFeatureAccessContext();

                    if ($user_group == 1) {
                        // Admin: no extra conditions
                    } else if ($context['has_access']) {
                        if ($context['is_hierarchy']) {
                            $conditions[] = array("EmployeeProfessionalDetails.attr1" => $emp_pkey);
                        } else {
                            $branches = $this->MasterdataManagement->getAllocatedBranches($emp_pkey, $feature_id);
                            $branch_codes = array();
                            if (!empty($branches)) {
                                foreach ($branches as $b) {
                                    $branch_codes[] = $b['b']['branch_code'];
                                }
                            }
                            $conditions[] = array("EmployeeDetails.branch_code" => $branch_codes);
                        }
                    } else {
                        $own_branch = $this->MasterdataManagement->getOwnBranch($emp_pkey);
                        $branch_code = isset($own_branch[0]['b']['branch_code']) ? $own_branch[0]['b']['branch_code'] : '0';
                        $conditions[] = array("EmployeeDetails.branch_code" => $branch_code);
                    }

                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions,
                        'order' => $arr_order
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

    public function downloadHistory($type, $mode)
    {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'Attendance':
                $dataForHistory['report_type'] = "Attendance Register_New Report";
                break;
            case 'VerifiedAttendance':
                $dataForHistory['report_type'] = "Attendance Register Report";
                break;
            case 'DetailedAttendance':
                $dataForHistory['report_type'] = "Employee Attendance Report";
                break;
            case 'Overtime':
                $dataForHistory['report_type'] = "Employee Approved Over Time Attendance Report";
                break;
            case 'OvertimeReport':
                $dataForHistory['report_type'] = "Employee Over Time Attendance Report";
                break;
            case 'Dashboard':
                $dataForHistory['report_type'] = "Employee Check In/Out Logs Report";
                break;
            //Edited by Akshay
            case 'regularisation':
                $dataForHistory['report_type'] = "Attendance Regularisation Report";
                break;
            //Edited by Akshay on 13-2-2024
            case 'movements':
                $dataForHistory['report_type'] = "Attendance Movements Report";
                break;
            // Edited by Akshay on 10-12-2024
            case 'LOP':
                $dataForHistory['report_type'] = "LOP Report";
                break;
            // End
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
                default:
                    break;
            }
            $stcriteria = isset($arr_form_data[$criteria]) ? $arr_form_data[$criteria] : '';
            if ($stcriteria) {
                $items_array[] = implode(",", $arr_form_data[$criteria]);
                $items_count_array[] = count($arr_form_data[$criteria]);
            }
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

    public function generatereport($type = '', $mode = '')
    {
        $this->autoRender = false;
        //debug($mode);
        switch ($type) {
            case 'employee':
                $this->generateemployeereport($mode);
                break;
            case 'Attendance':
                $this->generatesummaryreport($mode);
                break;
            case 'VerifiedAttendance':
                $this->generateVerifiedAttendancereport($mode);
                break;
            case 'AttendanceRep':
                $this->generateattendancereport($mode);
                break;
            case 'DetailedAttendance':
                $this->generateDetailedreport($mode);
                break;
            case 'TimeAttendance':
                $this->generatetimeattendancereport($type, $mode);
                break;
            case 'MobilelocationRep':
                $this->generatemobilelocationreport($type, $mode);
                break;
            case 'Dashboard':
                $this->generateCheckinlogsReport($type, $mode);
                break;
            case 'Overtime':
                $this->Overtimereport($type, $mode);
                break;
            case 'OvertimeReport':
                $this->generateOvertimereport($type, $mode);
                break;
            //Edited by Akshay
            case 'regularisation':
                $this->generateregularisationreport($type, $mode);
                break;
            //Edited by Akshay on 13-2-2024
            case 'movements':
                $this->generatemovementsreport($type, $mode);
                break;
            // Edited by Akshay on 10-12-2024
            case 'LOP':
                $this->generateLOPreport($mode);
                break;
            // End
            // Edited by Anu on 23-1-2025
            case 'nonpunched':
                $this->generatenonpunchedreport($type, $mode);
                break;
            case 'nonattendance':
                $this->generatenonattendancereport($type, $mode);
            default:
            // End
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

    private function generateemployeereport($mode = '')
    {
        $arr_form_data = $_REQUEST;

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $arr_reportfields = array();
        $fields = '';
        if (isset($arr_form_data['hidden-reportfields']) && $arr_form_data['hidden-reportfields'] != '') {
            $fields = $arr_form_data['hidden-reportfields'];
            $search = array('EmployeeDetails.', 'EmployeeProfessionalDetails.', 'Departments.', 'Grades.', 'Verticals.', 'Units.');
            $replace = array('', '', '', '', '', '');
            $str_reportfieldheadings = str_replace($search, $replace, $fields);
            $arr_reportfieldheadings = explode(',', $str_reportfieldheadings);
        }
        //$fields = 'EmployeeDetails.*,EmployeeProfessionalDetails.*,Departments.*,Grades.*,Verticals.*,Units.*';

        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
            array(
                'table' => 'grade',
                'alias' => 'Grades',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_grade = Grades.grade_code')
            ),
            array(
                'table' => 'verticals',
                'alias' => 'Verticals',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_vertical = Verticals.vert_code')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
        );
        $conditions = array('EmployeeDetails.status' => 1);

        //Build conditions based on criterias recieved
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '') && (isset($arr_form_data['reportto']) && $arr_form_data['reportto'] != '')) {
            $conditions[] = 'EmployeeProfessionalDetails.joining_date BETWEEN "' . $arr_form_data['reportfrom'] . '" AND "' . $arr_form_data['reportto'] . '"';
        }
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$str_employee_reportcriteria_field = $this->arr_employee_reportcriteria_fields[$str_criteria_item];
            //$this->arr_employee_reportcriteria_fields[$str_criteria_item];

            $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("fields" => "reportcriteria_field", "conditions" => array("status" => 1, 'reportcriteria' => $str_criteria_item))));
            if (isset($arr_reportcriterias[0]['reportcriteria_field'])) {
                if ($str_criteria_item != 'EmployeeProfessionalDetails') {
                    $conditions[] = $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                }
            }
        }
        $arr_emp_details = $this->EmployeeDetails->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions
        ));

        /* $arr_employee_personal = Set::extract('/EmployeeDetails/.',$arr_emp_details);
          $arr_employee_professional = Set::extract('/EmployeeProfessionalDetails/.',$arr_emp_details);
          $arr_employee_departments = Set::extract('/Departments/.',$arr_emp_details);
          $arr_employee_grades = Set::extract('/Grades/.',$arr_emp_details);
          $arr_employee_verticals = Set::extract('/Verticals/.',$arr_emp_details);
          $arr_employee_units = Set::extract('/Units/.',$arr_emp_details);
          $arr_employee_report_details = array_merge($arr_employee_personal,$arr_employee_professional,$arr_employee_departments,$arr_employee_grades,$arr_employee_verticals,$arr_employee_units); */

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
            'EmployeeDetails' => array_keys($arr_empinformation_fields->getFieldNames('EmployeeDetails')),
            'EmployeeProfessionalDetails' => array_keys($arr_empinformation_fields->getFieldNames('EmployeeProfessionalDetails')),
            'Departments' => array_keys($arr_empinformation_fields->getFieldNames('Departments')),
            'Grades' => array_keys($arr_empinformation_fields->getFieldNames('Grades')),
            'Verticals' => array_keys($arr_empinformation_fields->getFieldNames('Verticals')),
            'Units' => array_keys($arr_empinformation_fields->getFieldNames('Units'))
        );

        $this->set('arr_emp_field_headings', $arr_emp_field_headings);
        $this->set('arr_report_field_headings', $arr_reportfieldheadings);
        $this->set('arr_emp_field_names', $arr_emp_field_names);
        $this->set('arr_employee_report_details', $arr_emp_details);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportemployeeinformation');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('EmployeeInformation.pdf', 'D');
                //$this->render('reportemployeeinformation');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_EmployeeInformation.xlsx" : "EmployeeInformation_" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Information Report");
                $worksheet->mergeCells('A1:F1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );

                $sheet = array($arr_emp_field_headings);

                $columnindex = 0;
                foreach ($sheet as $row => $columns) {
                    foreach ($columns as $column => $data) {
                        if (in_array($column, $arr_reportfieldheadings)) {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . "2", $data);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $columnindex++;
                        }
                    }
                }

                $rowcount = 3;
                foreach ($arr_emp_details as $value) {
                    $columnindex = 0;
                    foreach ($arr_emp_field_names as $key => $val) {
                        foreach ($val as $val1) {
                            if (isset($value[$key][$val1])) {
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $value[$key][$val1]);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $columnindex++;
                            }
                        }
                    }
                    $rowcount++;
                }

                $objPHPExcel->getActiveSheet()->setTitle('Employee Information');

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
                $this->render('reportemployeeinformation');
                break;
        }
    }

    private function generateattendancereport($mode)
    {
        $arr_form_data = $_REQUEST;
        // debug($mode);

        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');

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
        //  debug($arr_dates);
        $d = $arr_form_data['reportfrom'] . '-01';
        //  debug($d);
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;
        }

        $arr_leavepolicydetails_for_template = array();
        //  debug($arr_leavepolicygroupids);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                //            $str_conditions = ' WHERE Attendance.branch_code="'.$leavepolicygroupid.'" and intime between "'.$fd.'" and "'.$Td.'";';
                //            $arr_leavepolicy_details = $this->AttendanceRegister->query(''
                //                    . 'SELECT '
                //                    . '*'
                //                    . 'FROM '
                //                    . '`client_db1`.`Attandance` AS `Attendance` ' 
                //                     .$str_conditions);
                //                     
                //                     
                //            
                // debug($leavepolicygroupid);
                //   $from="2015-10-06";

                $month = $arr_form_data['reportfrom'];
                $rand = $leavepolicygroupid . strtotime('now');
                // debug($rand);
                $this->AttendanceRegisterReport->useDbConfig = $this->Session->read('ds');
                $outputParameter = array();
                $outputParameter[] = $this->Session->read('company_code');
                $outputParameter[] = $leavepolicygroupid;
                $outputParameter[] = $rand;
                $outputParameter[] = $d;
                $out = $this->AttendanceRegisterReport->insertUpdateAttendanceRegisterForReportProc($outputParameter);
                //debug($out);
                $arr_attendance_register_entries = $this->AttendanceRegisterReport->query(" (SELECT AttendanceRegisterReport.company_code,"
                    . "AttendanceRegisterReport.branch_code,info.*,registerid,emp_fkey,month_year,emp_company_id,emp_name,FIELD1,FIELD2,FIELD3,FIELD4,FIELD5,FIELD6,FIELD7,FIELD8,FIELD9,FIELD10,FIELD11,FIELD12,FIELD13,FIELD14,FIELD15,FIELD16,FIELD17,FIELD18,FIELD19,FIELD20,FIELD21,FIELD22,FIELD23,FIELD24,FIELD25,FIELD26,FIELD27,FIELD28,FIELD29,FIELD30,FIELD31,FIELD32,isdelete,record_status,userid,id,branch_name,address,city,state,pincode,status,deleted "
                    . "FROM attendance_register_rep AS AttendanceRegisterReport "
                    . "left join branches as branchs on (branchs.branch_code = AttendanceRegisterReport.branch_code) "
                    . "left join employee_info as info on (info.emp_pkey = emp_fkey) "
                    . "where userid = '$rand'  and month_year='$month' ) "
                    . "union all (SELECT AttendanceRegister.company_code,AttendanceRegister.branch_code,info.*,registerid,emp_fkey,month_year,emp_company_id,emp_name,FIELD1,FIELD2,FIELD3,FIELD4,FIELD5,FIELD6,FIELD7,FIELD8,FIELD9,FIELD10,FIELD11,FIELD12,FIELD13,FIELD14,FIELD15,FIELD16,FIELD17,FIELD18,FIELD19,FIELD20,FIELD21,FIELD22,FIELD23,FIELD24,FIELD25,FIELD26,FIELD27,FIELD28,FIELD29,FIELD30,FIELD31,FIELD32,isdelete,record_status,userid,id,branch_name,address,city,state,pincode,status,deleted "
                    . "FROM attendance_register AS AttendanceRegister"
                    . " left join branches as branchs on (branchs.branch_code = AttendanceRegister.branch_code)"
                    . "left join employee_info as info on (info.emp_pkey = emp_fkey) "
                    . " where isdelete='N' and userid = '$rand'  and month_year='$month' ");
                // debug($arr_attendance_register_entries);

                $context = $this->MasterdataManagement->getFeatureAccessContext();
                $is_hierarchy = isset($context['is_hierarchy']) ? $context['is_hierarchy'] : false;
                $emp_pkey = $this->Session->read('emp_fkey');

                if (isset($arr_form_data['hidden-criteria1']) && $arr_form_data['hidden-criteria1'] == 'Units' && $is_hierarchy) {
                    $hierarchy_emps = $this->MasterdataManagement->getHierarchyEmployeesByBranch($emp_pkey, $leavepolicygroupid);
                    $hierarchy_emp_keys = array();
                    if (!empty($hierarchy_emps)) {
                        foreach ($hierarchy_emps as $hemp) {
                            $hierarchy_emp_keys[] = $hemp[0]['id'];
                        }
                    }

                    $filtered_entries = array();
                    foreach ($arr_attendance_register_entries as $entry) {
                        $emp_fkey = isset($entry['info']['emp_pkey']) ? $entry['info']['emp_pkey'] : null;
                        if (empty($emp_fkey)) {
                            $emp_fkey = isset($entry['AttendanceRegisterReport']['emp_fkey']) ? $entry['AttendanceRegisterReport']['emp_fkey'] : null;
                        }
                        if (empty($emp_fkey)) {
                            $emp_fkey = isset($entry['AttendanceRegister']['emp_fkey']) ? $entry['AttendanceRegister']['emp_fkey'] : null;
                        }
                        if (in_array($emp_fkey, $hierarchy_emp_keys)) {
                            $filtered_entries[] = $entry;
                        }
                    }
                    $arr_attendance_register_entries = $filtered_entries;
                }

                $arr_leavepolicydetails_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_attendance_register_entries,
                    // 'employees'=>$arr_leavepolicy_employees
                );
            }

            foreach ($arr_leavepolicydetails_for_template as $key => $value) {
                //  debug($value);
                foreach ($value['summary'] as $ky => $vaal) {
                    //     debug($vaal['AttendanceRegister']);
                    //   $resp_register["rows"][$key] = $value["AttendanceRegister"];
                    // debug($vaal["AttendanceRegister"]);
                    $int_days_present = count(array_keys($vaal["0"], "P"));
                    $int_days_leave = count(array_keys($vaal["0"], "L"));
                    $int_days_holidays = count(array_keys($vaal["0"], "HO"));

                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["0"]['days_present'] = $int_days_present;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["0"]['days_leave'] = $int_days_leave;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["0"]['days_holidays'] = $int_days_holidays;
                }
            }
            // debug($resp_register);
            // debug($arr_leavepolicydetails_for_template);  
            // debug($arr_leavepolicydetails_for_template);die();
            $this->set('month', $month);
            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            //Set informations needed for report
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }

        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportsattendance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A2', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('Attendance.pdf', 'D');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Attendance.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Verified Attendance Register Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Verified Attendance Register Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:Ai1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );
                $worksheet->mergeCells('A2:F2');
                $rowcount = 2;
                $i = 0;
                foreach ($arr_leavepolicydetails_for_template as $value) {
                    $i += 1;
                    $branch = $value['summary']['0']['0']['branch_name'];
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Attendance Reports of ' . $branch . ' For the month ' . $month);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                    $rowcount = 3;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Employee ID');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee Name');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Designation');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Date Of joining');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Department');

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Present Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Leave Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Holiday Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    for ($i = 0; $i <= 8; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                    }

                    $columnindex = 4;
                    foreach ($arr_dates as $key => $date) {

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnindex, ($rowcount))->getFont()->setBold(true);
                        $columnindex++;
                    }
                    $rowcount = 4;

                    $arr_data = $value['summary'];                        // debug($arr_data);die();
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                    if (count($arr_data) >= 0) {
                        $k = 1;
                        foreach ($arr_data as $key => $val) {
                            $columnindex = 0;
                            $name = $val[0]['EmpName'];
                            //$name=$name.$key;
                            $present = $val[0]['days_present'];
                            $leave = $val[0]['days_leave'];
                            $holidays = $val[0]['days_holidays'];
                            $id = $val[0]['employee_id'];
                            $des = $val[0]['designation'];
                            $join = $val[0]['joining_date'];
                            $department = $val[0]['department'];
                            $branch = $val[0]['branch'];
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $des);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $department);
                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $branch);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $present);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $leave);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $holidays);

                            $columnindex = 4;
                            foreach ($arr_dates as $key => $date) {
                                $newIndex = 'FIELD' . ($key + 1);
                                $dta = $val[0][$newIndex];
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                $columnindex++;
                            }

                            $rowcount++;
                            $k++;
                        }
                    }
                    $rowcount1 = $rowcount + 1;
                }

                $objPHPExcel->getActiveSheet()->setTitle('Attendance');
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
                $this->render('reportsattendance');
                break;
        }
    }
    //attendance register report
    private function generatesummaryreport($mode)
    {
        $arr_form_data = $_REQUEST;
        date_default_timezone_set('Asia/Kolkata');

        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $date = date('d-m-Y');
        $this->set('date', $date);
        $date_time = date('d-m-Y H:i');
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 = $month . '-01';
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('login_user_id');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));

        //added by megha on on 11_03_2020 changed for SH Infra
        $month = $arr_form_data['reportfrom'];
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $month1 = $month . '-01';
        $att_startdate = $this->AttendanceRegister->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->AttendanceRegister->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_startdate1 = date("d", strtotime($att_startdate['0']['0']['monthly_att_fromdate']));
        $att_enddate1 = date("d", strtotime($att_enddate['0']['0']['monthly_att_todate']));

        $arr_date_in_selectedmonth = range(1, $att_enddate1);

        if ($att_startdate1 != 1) {
            $arr_date_in_prevmonth = range($att_startdate1, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }
        //end

        $hiddenreporttype = $arr_form_data['hidden-report-type'];
        $this->set('reporttype', $hiddenreporttype);
        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        $this->set('arr_dates', $arr_dates);
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
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

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->AttendanceRegister->query("select UCASE(ifnull(occurance,'LOP')) AS abbr from salary_head_items where ucase(item_type)='LEAVE' AND occurance != 'LOP'");
        $arr_leaveabbr = array();
        $arr_leavetype = array();
        foreach ($arr_leavetypes as $leaveabbr => $leave) {
            $arr_leaveabbr[] = strtoupper($leave[0]['abbr']) . "/" . strtoupper($leave[0]['abbr']);
            $arr_leavetype[] = strtoupper($leave[0]['abbr']);
        }

        $query = "CALL insert_update_att_reg('$company_code','NULL','$user_id','$month');";

        $arr_leavepolicydetails_for_template = array();
        $new = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                // debug($leavepolicygroupid);
                // edited by sinsiya on 29-10-2024
                $fields = 'AttendanceRegister.*,EmployeeDetails.status,Termination.last_approved_working_date,Branch.branch_name,config.policy_id,config.hirc_leval,Info.*';

                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branch',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'AttendanceRegister.branch_code = Branch.branch_code',
                            'Branch.status = 1'

                        )
                    ),
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmployeeDetails',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'AttendanceRegister.emp_fkey = EmployeeDetails.emp_pkey'
                        )
                    ),
                    array(
                        'table' => 'employee_info',
                        'alias' => 'Info',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmployeeDetails.emp_pkey  = Info.emp_pkey')

                    ),
                    array(
                        'table' => 'emp_config',
                        'alias' => 'config',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmployeeDetails.emp_pkey  = config.emp_fkey', 'config.type = "HIERARCHY"', 'config.status = 1')

                    ),
                    array(
                        'table' => 'termination',
                        'alias' => 'Termination',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmployeeDetails.emp_pkey  = Termination.emp_fkey', 'Termination.status = 1')
                    ),
                    array(
                        'table' => 'emp_proff',
                        'alias' => 'EmployeeProfessionalDetails',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                    )
                );

                $context = $this->MasterdataManagement->getFeatureAccessContext();
                $is_hierarchy = isset($context['is_hierarchy']) ? $context['is_hierarchy'] : false;
                $emp_pkey = $this->Session->read('emp_fkey');

                $conditions = array("Branch.status" => "1"); //"isdelete"=>"N",
                if ($arr_form_data['hidden-criteria1'] == "Units") {
                    $conditions[] = 'AttendanceRegister.branch_code="' . $leavepolicygroupid . '" and AttendanceRegister.month_year = "' . $report_month . '"  ';
                    if ($is_hierarchy) {
                        $conditions[] = 'EmployeeProfessionalDetails.attr1="' . $emp_pkey . '"';
                    }
                } else {
                    $conditions[] = 'EmployeeDetails.emp_pkey="' . $leavepolicygroupid . '" and AttendanceRegister.month_year = "' . $report_month . '"';
                }
                if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

                    $conditions[] = "EmployeeDetails.status in('1','2')";
                } else {
                    $conditions[] = "EmployeeDetails.status ='1' ";
                }

                $arr_leavepolicy_details = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, "order" => array("ifnull(config.hirc_leval,1000)", "Info.EmpName", "Info.branch")));
                //  if ($arr_form_data['hidden-criteria1'] == "Units") {
                //  $emp=$this->AttendanceRegister->query("select emp_pkey from emp_details where branch_code= '$leavepolicygroupid'");
                //  foreach($emp as $arremp){
                //   $empid=$arremp['emp_details']['emp_pkey'];

                //   $array_hierarchy = $this->AttendanceRegister->query("SELECT policy_id FROM emp_config WHERE emp_fkey = '$empid' AND type = 'HIERARCHY' AND status = 1");   
                //  }

                //  }else{

                //   $array_hierarchy = $this->AttendanceRegister->query("SELECT policy_id FROM emp_config WHERE emp_fkey = '$leavepolicygroupid' AND type = 'HIERARCHY' AND status = 1");
                //  }
                //debug($array_hierarchy);
                if (!empty($arr_leavepolicy_details)) {
                    $arr_leavepolicydetails_for_template[] = array(

                        'summary' => $arr_leavepolicy_details

                    );
                }
            }

            function getcounts($input, $arr_datas)
            {
                $result = preg_grep('~' . $input . '~', $arr_datas);
                return count($result);
            }

            foreach ($arr_leavepolicydetails_for_template as $key => $value) {
                foreach ($value['summary'] as $ky => $vaal) {
                    $int_days_leave = 0;
                    $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P/P")) + count(array_keys($vaal["AttendanceRegister"], "P/A")) / 2 + count(array_keys($vaal["AttendanceRegister"], "A/P")) / 2 + count(array_keys($vaal["AttendanceRegister"], "WFH"));
                    $count_arr = count($vaal["AttendanceRegister"]);

                    foreach ($arr_leavetype as $leaves) {

                        $j = 0;

                        foreach ($vaal["AttendanceRegister"] as $dat) {
                            if ($j > 6 && $j < ($count_arr - 8)) {

                                $arr_temp = explode("/", $dat);

                                foreach ($arr_temp as $temp) {

                                    if ($leaves == $temp) {
                                        $int_days_leave += 1;
                                    }
                                }
                            }
                            $j++;
                        }
                    }


                    $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "WO")) + getcounts(preg_quote("WO/", '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote("/WO", '~'), $vaal["AttendanceRegister"]) / 2;

                    $int_days_LOPs = getcounts(preg_quote("LOP/", '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote("/LOP", '~'), $vaal["AttendanceRegister"]) / 2 + count(array_keys($vaal["AttendanceRegister"], "LOP"));
                    $int_days_holidays_holi = count(array_keys($vaal["AttendanceRegister"], "HO"));
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = getcounts(preg_quote('P/', '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote('/P', '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote('WFH/', '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote('/WFH', '~'), $vaal["AttendanceRegister"]) / 2 + count(array_keys($vaal["AttendanceRegister"], "WFH")) + getcounts(preg_quote('WFO/', '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote('/WFO', '~'), $vaal["AttendanceRegister"]) / 2 + count(array_keys($vaal["AttendanceRegister"], "WFO")) - getcounts(preg_quote("LOP/", '~'), $vaal["AttendanceRegister"]) / 2;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave / 2;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['lops'] = $int_days_LOPs;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['HO'] = $int_days_holidays_holi;
                    if ($vaal["AttendanceRegister"]['isdelete'] == 'N') {
                        $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $vaal["AttendanceRegister"]['presant_total'];
                        $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['lops'] = $vaal["AttendanceRegister"]['lop_total'];
                        $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['HO'] = $vaal["AttendanceRegister"]['holiday_total'];
                        $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $vaal["AttendanceRegister"]['leave_total'];
                    }
                }
            }

            // debug($arr_leavepolicydetails_for_template);exit();

            $present_employees = array();
            $new_array = array();

            foreach ($arr_leavepolicydetails_for_template as $value) {


                $arr_data = $value['summary'];

                foreach ($arr_data as $key => $val) {
                    $date1 = (date("Y-m", strtotime($month)));

                    if ($val['Termination']['last_approved_working_date'] != null) {
                        $term_date = (date("Y-m", strtotime($val['Termination']['last_approved_working_date'])));
                    } else {
                        $term_date = $val['Termination']['last_approved_working_date'];
                    }
                    if ($date1 <= $term_date || $term_date == null) {
                        $new_array[] = $val;
                    }
                }
                $present_employees['list'] = $new_array;
            }
            if (!empty($present_employees['list'])) {
                $present_employee['summary'] = $present_employees['list'];
            }

            if (empty($present_employees['list'])) {
                $present_employee = array();
            }

            $branch_array = array();
            $branch_employees = array();
            if ($arr_form_data['hidden-criteria1'] == "Units") {
                foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                    if (isset($present_employee['summary'])) {
                        foreach ($present_employee['summary'] as $value) {
                            $branch = $value['AttendanceRegister']['branch_code'];
                            if ($branch == $leavepolicygroupid) {
                                $branch_array[$leavepolicygroupid]['summary'][] = $value;
                            }
                        }
                        if (!empty($branch_array[$leavepolicygroupid])) {
                            $branch_employees[] = $branch_array[$leavepolicygroupid];
                        }
                    }
                }
            } else {

                if (isset($present_employee['summary'])) {
                    foreach ($present_employee['summary'] as $value) {
                        $branch_array = array();
                        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                            $branch_array[$leavepolicygroupid]['summary'][] = $value;
                        }
                        if (!empty($branch_array[$leavepolicygroupid])) {
                            $branch_employees[] = $branch_array[$leavepolicygroupid];
                        }
                    }
                }
            }
            //           if(!empty($branch_employees)){
            //                         $branch_employee = $branch_employees;
            //                    }
            //           if(empty($branch_employees)){
            //                         $branch_employee = array();
            //                 } 



            $this->set('present_employees', $branch_employees);
            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("report_month", $report_month);
            switch ($mode) {
                case 'pdf':

                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('reportsummary');

                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                    $html2pdf = new HTML2PDF('L', 'A2', 'en');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('VerifiedAttendanceRegisterReport.pdf', 'D');
                    // $this->render('reportsummary');                
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_AttendanceRegister.xlsx" : "AttendanceRegister" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Attendance Register By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Attendance Register_New - " . $date);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells("A1:N1");
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );
                    date_default_timezone_set('Asia/Kolkata');
                    $worksheet->mergeCells("A2:N2");
                    $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );

                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }

                    if (!empty($present_employee)) {
                        $rowcount = 3;
                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 0), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Company ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
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
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Present Days');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Leave Days');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Week Off');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Holidays');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'LOP');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $columnindex = $columncount + 14;

                        $alphabets = array();
                        $alphabet = 'A';
                        while ($alphabet != 'CZ') {
                            $alphabets[] = $alphabet++;
                        }

                        foreach ($arr_dates as $key => $date) {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->getFont()->setBold(true);

                            $column3 = $alphabets[$columnindex];
                            $column4 = $alphabets[$columnindex + 1];
                            $worksheet->mergeCells($column3 . "3:" . $column4 . "3");

                            $style = array(
                                'alignment' => array(
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                )
                            );
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->applyFromArray($style);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($column3)->setAutoSize(false);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($column4)->setAutoSize(false);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($column3)->setWidth(10);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($column4)->setWidth(10);

                            $columnindex++;
                            $columnindex++;
                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, 'Status'); //$coloumnindex
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + $columnindex), $rowcount)->getFont()->setBold(true);
                        $columnname = $alphabets[$columnindex];

                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnname)->setAutoSize(true);

                        $rowcount = 4;
                        //edited by megha 2/12/2019 serial no.corrected
                        if ($arr_form_data['hidden-criteria1'] == "EmployeeDetails") {
                            $k = 1;
                        }
                        $k = 1;

                        foreach ($present_employee as $value['summary']) {
                            $branch = isset($value['summary']['0']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : 'No Datas Found Under This Branch';

                            $arr_data = isset($value['summary']) ? $value['summary'] : 'null';

                            if (count($arr_data) >= 0) {
                                //edited by megha 2/12/2019 serial no.corrected
                                if ($arr_form_data['hidden-criteria1'] == "Units") {
                                }
                                foreach ($arr_data as $key => $val) {

                                    //Calculating LOP
                                    $count = 0;
                                    foreach ($arr_dates as $key => $date) {
                                        $newIndex = 'FIELD' . ($key + 1);
                                        $dta = $val['AttendanceRegister'][$newIndex];

                                        $arr = explode("/", $dta);

                                        foreach ($arr as $ar) {
                                            if ($ar == null || $ar == 'A' || $ar == 'LOP' || $ar == '') {
                                                if (count($arr) == 2) {
                                                    $count = $count + 1;
                                                } elseif (count($arr) == 1) {
                                                    $count = $count + 2;
                                                }
                                            }
                                        }
                                    }

                                    $count = $count / 2;
                                    $columnindex = 0;
                                    $name = $val['Info']['EmpName'];
                                    $status = isset($val['EmployeeDetails']['status']) && $val['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : '';
                                    $present = $val['AttendanceRegister']['days_present'];
                                    $leave = $val['AttendanceRegister']['days_leave'];
                                    $holidays = $val['AttendanceRegister']['days_holidays'];
                                    $holi = $val['AttendanceRegister']['HO'];
                                    // $lop = $count;
                                    $lop = $val['AttendanceRegister']['lop_total'];
                                    $id = $val['Info']['employee_id'];
                                    $eid = $val['Info']['emp_id'];
                                    $des = $val['Info']['designation'];
                                    $join = $val['Info']['joining_date'];
                                    $join_date = date('d-m-Y', strtotime($join));
                                    $termination = $val['Termination']['last_approved_working_date'];


                                    $termin_date = !empty($termination) ? date('d-m-Y', strtotime($termination)) : '';



                                    $department = $val['Info']['department'];
                                    $branch = $val['Info']['branch'];
                                    $statusv = $val['AttendanceRegister']['isdelete'];


                                    switch ($statusv) {
                                        case "N":
                                            $statusv = 'Verified';
                                            break;
                                        case "Y":
                                            $statusv = 'Not Verified';
                                            break;
                                        default:
                                            $statusv = 'Invalid data';
                                    }

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $eid);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $name . $status);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $department);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $des);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $termin_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $present);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $leave);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $holidays);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12) . $rowcount, $holi);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowcount, $lop);

                                    $columnindex = $columnindex + 14;

                                    foreach ($arr_dates as $key => $date) {
                                        $newIndex = 'FIELD' . ($key + 1);
                                        $dta = $val['AttendanceRegister'][$newIndex];
                                        $pieces = array();
                                        if (strpos($dta, '/') !== false) {
                                            $pieces = explode('/', $dta);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $pieces[0]);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $pieces[1]);
                                        } else {
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $dta);
                                        }
                                        $columnindex++;
                                        $columnindex++;
                                    }
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $statusv);
                                    $k++;
                                    $rowcount++;
                                }

                                $BStyle = array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN
                                        )
                                    )
                                );
                                $objPHPExcel->getActiveSheet()->getStyle('C4:C3000')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $row = $rowcount - 1;
                                $column = $columnindex - 1;
                                $bordercolumnrange = (PHPExcel_Cell::stringFromColumnIndex($column + 1));
                                $borderrange = $bordercolumnrange . $row;
                                $objPHPExcel->getActiveSheet()->getStyle('A1:' . $borderrange)->applyFromArray($BStyle);
                            }

                            $rowcount1 = $rowcount + 1;
                        }
                    } else {
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under this selected criteria. ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                        $worksheet->mergeCells('A3:N3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, )
                        );
                    }


                    $objPHPExcel->getActiveSheet()->setTitle('Attendance Register_New');
                    /* border */

                    /* border */
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
                    $this->set('month', $month);
                    $this->render('reportsummary');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }

    private function generateVerifiedAttendancereport($mode)
    {
        $arr_form_data = $_REQUEST;

        //       if($arr_form_data['select-criteria1']!='Units')
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        /* $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m-1');
       $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
       $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
       $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
       $arr_date_in_selectedmonth = range(1, $att_enddate);
       if ($att_startdate != 1) {
           $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
       } else {
           $arr_date_in_prevmonth = array();
       } */


        //added by megha on on 11_03_2020 changed for SH Infra
        $month = $arr_form_data['reportfrom'];
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $month1 = $month . '-01';
        $att_startdate = $this->AttendanceRegister->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->AttendanceRegister->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_startdate1 = date("d", strtotime($att_startdate['0']['0']['monthly_att_fromdate']));
        $att_enddate1 = date("d", strtotime($att_enddate['0']['0']['monthly_att_todate']));

        $arr_date_in_selectedmonth = range(1, $att_enddate1);

        if ($att_startdate1 != 1) {
            $arr_date_in_prevmonth = range($att_startdate1, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }
        //end
        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        $this->set('arr_dates', $arr_dates);



        $hiddenreporttype = $arr_form_data['hidden-report-type'];
        $this->set('reporttype', $hiddenreporttype);
        $fd = $arr_form_data['reportfrom'] . ' ' . '00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
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
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->AttendanceRegister->query("select UCASE(ifnull(occurance,'LOP')) AS abbr from salary_head_items where ucase(item_type)='LEAVE' AND occurance != 'LOP'");
        $arr_leaveabbr = array();
        $arr_leavetype = array();
        foreach ($arr_leavetypes as $leaveabbr => $leave) {
            $arr_leaveabbr[] = strtoupper($leave[0]['abbr']) . "/" . strtoupper($leave[0]['abbr']);
            $arr_leavetype[] = strtoupper($leave[0]['abbr']);
        }
        //debug($arr_leaveabbr);
        $query = "CALL insert_update_att_reg('$company_code','NULL','$user_id','$month');";

        $arr_leavepolicydetails_for_template = array();
        $new = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                //edited by megha add termination date in excel 28/12/2019
                $fields = 'AttendanceRegister.*,EmployeeDetails.status,Termination.last_approved_working_date,Branch.branch_name,Info.*'; //edited by ASHIN on 20-05-24

                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branch',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'AttendanceRegister.branch_code = Branch.branch_code',
                            'Branch.status = 1'
                        )
                    ),
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmployeeDetails',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'AttendanceRegister.emp_fkey = EmployeeDetails.emp_pkey'
                        )
                    ),
                    array(
                        'table' => 'employee_info',
                        'alias' => 'Info',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmployeeDetails.emp_pkey  = Info.emp_pkey')
                    ),
                    array(
                        'table' => 'termination',
                        'alias' => 'Termination',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmployeeDetails.emp_pkey  = Termination.emp_fkey', 'Termination.status = 1')
                    )
                );
                //$conditions = array('AttendanceRegister.isdelete="Y"');
                $conditions = array("Branch.status" => "1");
                if ($arr_form_data['hidden-criteria1'] == "Units") {
                    $conditions[] = 'AttendanceRegister.branch_code="' . $leavepolicygroupid . '" and AttendanceRegister.month_year = "' . $report_month . '"  ';
                } else {
                    $conditions[] = 'EmployeeDetails.emp_pkey="' . $leavepolicygroupid . '" and AttendanceRegister.month_year = "' . $report_month . '"  ';
                }
                if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

                    $conditions[] = "EmployeeDetails.status in('1','2')";
                } else {
                    $conditions[] = "EmployeeDetails.status = '1' ";
                }
                $arr_leavepolicy_details = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, "order" => "Info.EmpName", "Info.branch"));

                if (!empty($arr_leavepolicy_details)) {
                    $arr_leavepolicydetails_for_template[] = array(
                        //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                        'summary' => $arr_leavepolicy_details,
                        // 'employees'=>$arr_leavepolicy_employees
                    );
                }

                // $log = $this->AttendanceRegister->getDataSource()->getLog(false, false);
                //                 debug($log);
                // debug($arr_leavepolicydetails_for_template);
                // exit();
            }
            function getcounts($input, $arr_datas)
            {
                //debug($arr_datas);
                //                $input = preg_quote('/A', '~'); // don't forget to quote input string!
                $result = preg_grep('~' . $input . '~', $arr_datas);
                return count($result);
            }

            foreach ($arr_leavepolicydetails_for_template as $key => $value) {
                foreach ($value['summary'] as $ky => $vaal) {
                    $int_days_leave = 0;
                    $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P/P")) + count(array_keys($vaal["AttendanceRegister"], "P/A")) + count(array_keys($vaal["AttendanceRegister"], "A/A")) / 2 + count(array_keys($vaal["AttendanceRegister"], "A/P")) / 2 + count(array_keys($vaal["AttendanceRegister"], "WFH")); //Edited by ASHIN on 22-05-24
                    // debug($arr_leavetypes);
                    $count_arr = count($vaal["AttendanceRegister"]);
                    foreach ($arr_leavetype as $leaves) {
                        //                        $inp = isset($leave['0']['abbr'])?$leave['0']['abbr']:NULL;

                        //    $int_days_leave +=  getcounts(preg_quote("$leaves/", '~'),$vaal["AttendanceRegister"])/2 + getcounts(preg_quote("/$leaves", '~'),$vaal["AttendanceRegister"])/2 ;

                        $j = 0;
                        foreach ($vaal["AttendanceRegister"] as $dat) {
                            if ($j > 6 && $j < ($count_arr - 8)) {

                                $arr_temp = explode("/", $dat);

                                foreach ($arr_temp as $temp) {

                                    // if($leaves !='0')
                                    if ($leaves == $temp) {
                                        $int_days_leave += 1;
                                    }
                                }
                            }
                            $j++;
                        }
                        // debug($vaal["AttendanceRegister"]);
                        // debug($int_days_leave);
                    }

                    $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "WO")) + getcounts(preg_quote("WO/", '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote("/WO", '~'), $vaal["AttendanceRegister"]) / 2;
                    $int_days_LOPs = getcounts(preg_quote("LOP/", '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote("/LOP", '~'), $vaal["AttendanceRegister"]) / 2 + count(array_keys($vaal["AttendanceRegister"], "LOP"));
                    $int_days_holidays_holi = count(array_keys($vaal["AttendanceRegister"], "HO"));
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = getcounts(preg_quote('P/', '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote('/P', '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote('WFH/', '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote('/WFH', '~'), $vaal["AttendanceRegister"]) / 2 + count(array_keys($vaal["AttendanceRegister"], "WFH")) + getcounts(preg_quote('WFO/', '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote('/WFO', '~'), $vaal["AttendanceRegister"]) / 2 + count(array_keys($vaal["AttendanceRegister"], "WFO")) - getcounts(preg_quote("LOP/", '~'), $vaal["AttendanceRegister"]) / 2;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave / 2;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['lops'] = $int_days_LOPs;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['HO'] = $int_days_holidays_holi;
                    //HIDED BY ASHIN ANTONY ON 18-05-24
                    // if ($vaal["AttendanceRegister"]['isdelete'] == 'N') {
                    //     $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $vaal["AttendanceRegister"]['presant_total'];
                    //     $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $vaal["AttendanceRegister"]['weekoff_total'];
                    //     $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['lops'] = $vaal["AttendanceRegister"]['lop_total'];
                    //     $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['HO'] = $vaal["AttendanceRegister"]['holiday_total'];
                    //     $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $vaal["AttendanceRegister"]['leave_total'];
                    // }
                }
            }


            $present_employees = array();
            $new_array = array();

            foreach ($arr_leavepolicydetails_for_template as $value) {


                $arr_data = $value['summary'];

                foreach ($arr_data as $key => $val) {
                    $date1 = (date("Y-m", strtotime($month)));

                    if ($val['Termination']['last_approved_working_date'] != null) {
                        $term_date = (date("Y-m", strtotime($val['Termination']['last_approved_working_date'])));
                    } else {
                        $term_date = $val['Termination']['last_approved_working_date'];
                    }
                    if ($date1 <= $term_date || $term_date == null) {
                        $new_array[] = $val;
                    }
                }
                $present_employees['list'] = $new_array;
            }
            if (!empty($present_employees['list'])) {
                $present_employee['summary'] = $present_employees['list'];
            }

            if (empty($present_employees['list'])) {
                $present_employee = array();
            }

            $branch_array = array();
            $branch_employees = array();
            if ($arr_form_data['hidden-criteria1'] == "Units") {
                foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                    if (isset($present_employee['summary'])) {
                        foreach ($present_employee['summary'] as $value) {
                            $branch = $value['AttendanceRegister']['branch_code'];
                            if ($branch == $leavepolicygroupid) {
                                $branch_array[$leavepolicygroupid]['summary'][] = $value;
                            }
                        }
                        if (!empty($branch_array[$leavepolicygroupid])) {
                            $branch_employees[] = $branch_array[$leavepolicygroupid];
                        }
                    }
                }
            } else {

                if (isset($present_employee['summary'])) {
                    foreach ($present_employee['summary'] as $value) {
                        $branch_array = array();
                        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                            $branch_array[$leavepolicygroupid]['summary'][] = $value;
                        }
                        if (!empty($branch_array[$leavepolicygroupid])) {
                            $branch_employees[] = $branch_array[$leavepolicygroupid];
                        }
                    }
                }
            }
            //edited by ASHIN on 20-05-24
            function num2alpha($n)
            {
                $r = '';
                for ($i = 1; $n >= 0 && $i < 10; $i++) {
                    $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
                    $n -= pow(26, $i);
                }
                return $r;
            }

            // debug($arr_leavepolicydetails_for_template);
            // exit;
            $this->set('present_employees', $branch_employees);
            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $user_id = $this->Session->read('login_user_id');
            date_default_timezone_set('Asia/Kolkata');
            $date = date('d-m-Y', strtotime($arr_form_data['reportfrom']));
            $this->set('date', $date);
            $date_time = date('d-m-Y H:i');
            $this->set('user_id', $user_id);
            $this->set('date_time', $date_time);

            $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $time = strtotime($f);
            $month = date("m", $time);
            $mname = date('F', mktime(0, 0, 0, $month, 10));
            $month1 = $month . '-01';
            $year = date("Y", $time);
            $this->set('mname', $mname);
            $this->set('year', $year);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("report_month", $report_month);
            $end_heading = 0; //edited by Ashin on 20-05-24
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('attendancereportsummary');

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
                    $file_name = isset($str_company_code) ? $str_company_code . "_Attendance_Register" . $from . ".xlsx" : "AttendanceA" . strtotime() . ".xlsx";   //Edited by ASHIN on 20-05-24

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee Attendance Register  Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();
                    // $objPHPExcel->getActiveSheet()->freezePane('E4'); //Edited by ASHIN on 20-05-24
                    $worksheet->setCellValueByColumnAndRow(0, 1, "Attendance Register - " . $date);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells('A1:M1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );

                    $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    $worksheet->mergeCells('A2:M2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );
                    //edited by ASHIN on 20-05-24
                    //Border style
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );



                    //                if (count($arr_leavepolicydetails_for_template) == 0){

                    //                    $worksheet->setCellValueByColumnAndRow(0, 3, "   There is no data available with the selected criteria");
                    //                     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                    //                  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                    //                  $worksheet->mergeCells('A3:M3');
                    //                  $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                    //                          array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    //                  );}

                    // else{
                    // if(!empty($arr_leavepolicydetails_for_template)){
                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    //Total value decalration edited by Ashin on 28-05-24
                    $total_val = array(
                        'present_days' => 0,
                        'leave_days' => 0,
                        'week_off' => 0,
                        'holidays' => 0,
                        'lop' => 0
                    );

                    //edited by athira on 23-06-2025

                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    //end

                    if (!empty($present_employee)) {

                        $rowcount = 3;
                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Company ID');
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

                        //added by megha termination date 
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);


                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Present Days');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Leave Days');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Week Off');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Holidays');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'LOP');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                        //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Present Days');
                        //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Leave Days');
                        //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Holiday Days');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(14))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                        $columnindex = $columncount + 14;
                        foreach ($arr_dates as $key => $date) {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->getFont()->setBold(true);
                            $columnindex++;

                            //edited by Ashin on 20-05-24
                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, 'Status'); //$coloumnindex
                            // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + $columnindex), $rowcount)->getFont()->setBold(true);
                            // $objPHPExcel->getActiveSheet()->getColumnDimension('AR')->setWidth(17);
                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($columnindex);
                            $objPHPExcel->getActiveSheet()->SetCellValue($columnLetter . $rowcount, 'Status');
                            $objPHPExcel->getActiveSheet()->getStyle($columnLetter)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + $columnindex), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(20); // Adjust the width as needed
                        }
                        $end_heading = $columnindex; //edited by Ashin on 20-05-24
                        $rowcount = 4;
                        //$rowcount1=3;
                        if ($arr_form_data['hidden-criteria1'] == "EmployeeDetails") {
                            $k = 1;
                        }
                        $k = 1;
                        foreach ($arr_leavepolicydetails_for_template as $value) {
                            // debug($value);exit();
                            // $branch = isset($value['summary']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : 'No Datas Found Under This Branch';
                            //echo $branch;die();                     
                            //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,'branch');

                            $arr_data = $value['summary'];
                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                            if (count($arr_data) >= 0) {
                                if ($arr_form_data['hidden-criteria1'] == "Units") {
                                    //  $k = 1;
                                }

                                foreach ($arr_data as $key => $val) {
                                    //Comparing resigned
                                    // debug($f);
                                    $date1 = (date("Y-m", strtotime($f)));
                                    // debug($date1);
                                    if ($val['Termination']['last_approved_working_date'] != null)
                                        $term_date = (date("Y-m", strtotime($val['Termination']['last_approved_working_date'])));
                                    else {
                                        $term_date = $val['Termination']['last_approved_working_date'];
                                    }
                                    // debug($term_date);
                                    if ($date1 <= $term_date || $term_date == null) {
                                        //Calculating LOP
                                        $count = 0;
                                        foreach ($arr_dates as $key => $date) {
                                            $newIndex = 'FIELD' . ($key + 1);
                                            $dta = $val['AttendanceRegister'][$newIndex];
                                            // debug($dta);
                                            // exit;
                                            $arr = explode("/", $dta);

                                            // debug($arr);


                                            foreach ($arr as $ar) {
                                                if ($ar == null || $ar == 'A' || $ar == 'LOP' || $ar == '') {
                                                    if (count($arr) == 2) {
                                                        $count = $count + 1;
                                                    } elseif (count($arr) == 1) {
                                                        $count = $count + 2;
                                                    }
                                                }
                                            }



                                            //        if($dta == null || $dta == 'A/A' || $dta == 'LOP' || $dta == 'LOP/LOP' || $dta == 'A/LOP' || $dta == 'LOP/A'){

                                            //            $count = $count + 2;
                                            //            // debug($count);
                                            //    }  
                                            //    elseif(strpos($dta,'A/') != false|| strpos($dta,'/A') != false || strpos($dta,'LOP/') != false || strpos($dta,'/LOP') != false || $dta == 'A/WO' || $dta == 'WO/A'){
                                            //                 $count = $count + 1;

                                            //    }


                                        }

                                        $count = $count / 2;
                                        // debug($val);exit();
                                        $columnindex = 0;
                                        $status = isset($val['Info']['emp_status']) && $val['Info']['emp_status'] == "2" ? '  (Resigned)' : '';
                                        // debug($status);exit();
                                        $name = $val['Info']['EmpName'] . $status;

                                        //$name = $val['AttendanceRegister']['emp_name'];
                                        //Edited by ASHIN on 28-05-24           
                                        $present = $val['AttendanceRegister']['days_present'];
                                        $total_val['present_days'] += $present;

                                        $leave = $val['AttendanceRegister']['days_leave'];
                                        $total_val['leave_days'] += $leave;
                                        $holidays = $val['AttendanceRegister']['days_holidays'];
                                        $total_val['week_off'] += $holidays;
                                        $holi = $val['AttendanceRegister']['HO'];
                                        $total_val['holidays'] += $holi;
                                        // $lop = $val['AttendanceRegister']['lops'];
                                        $lop = $count;
                                        $total_val['lop'] += $lop;
                                        $emp_id = $val['Info']['emp_id'];
                                        $company_id = $val['Info']['employee_id'];
                                        $des = $val['Info']['designation'];
                                        $join = $val['Info']['joining_date'];
                                        $join_date = date('d-m-Y', strtotime($join));
                                        $termination = $val['Termination']['last_approved_working_date'];
                                        $termin_date = !empty($termination) ? date('d-m-Y', strtotime($termination)) : '';
                                        //edited by athira on 22-08-2025
                                        $status = isset($val['0']['summary']['EmployeeDetails']['status']);
                                        if ($status == '2') {
                                            $name .= '(Resigned)';
                                        }
                                        //end



                                        $department = $val['Info']['department'];
                                        $branch = isset($val['Branch']['branch_name']) ? $val['Branch']['branch_name'] : ''; // Edited by Akshay on 5-3-2025
                                        $statusv = $val['AttendanceRegister']['isdelete'];


                                        switch ($statusv) {
                                            case "N":
                                                $statusv = 'Verified';
                                                break;
                                            case "Y":
                                                $statusv = 'Not Verified';
                                                break;
                                            default:
                                                $statusv = 'Invalid data';
                                        }

                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $emp_id);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $company_id);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $name);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join_date);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $department);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $des);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $termin_date);

                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $present);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $leave);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $holidays);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12) . $rowcount, $holi);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowcount, $lop);

                                        $columnindex = $columnindex + 14;
                                        foreach ($arr_dates as $key => $date) {
                                            $newIndex = 'FIELD' . ($key + 1);
                                            $dta = $val['AttendanceRegister'][$newIndex];
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                            $columnindex++;
                                        }
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $statusv);
                                        //   $objPHPExcel->getActiveSheet()->getColumnDimension('AR')->setWidth(35);

                                        $k++;
                                        $rowcount++;
                                    }
                                    $objPHPExcel->getActiveSheet()
                                        ->getStyle('B4:B4000')
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()
                                        ->getStyle('C4:C4000')
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()
                                        ->getStyle('I4:N1000')
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                }
                            }
                            // $rowcount1 = $rowcount + 1;
                        }
                        //edited by athira on 23-06-2025
                        $column = 9;
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowcount . ':D' . $rowcount);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'TOTAL');
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowcount, 'TOTAL');
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        foreach ($total_val as $print_total) {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, (isset($print_total) ? $print_total : ''));
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                            $column++;
                        }

                        $lastrow = $objPHPExcel->getActiveSheet()->getHighestRow();
                        $objPHPExcel->getActiveSheet()->getStyle('A3:' . num2alpha($end_heading) . $rowcount)->applyFromArray($styleArray);
                        $objPHPExcel->getActiveSheet()->freezePane('E4'); //Edited by ASHIN on 20-05-24
                        //end
                    } else {
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under this selected criteria. ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                        $worksheet->mergeCells('A3:N3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, )
                        );
                    }
                    //edited by ASHIN on 28-05-24
                    // Total

                    //edited by ASHIN on 20-05-24
                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    //Border style

                    // $lastrow = $objPHPExcel->getActiveSheet()->getHighestRow();
                    // $objPHPExcel->getActiveSheet()->getStyle('A3:' . num2alpha($end_heading) . $rowcount)->applyFromArray($styleArray);

                    $objPHPExcel->getActiveSheet()->setTitle('Attendance Register ');
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
                    $this->set('f', $f);
                    $this->render('attendancereportsummary');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }
    private function generateDetailedreport($mode)
    {
        $arr_form_data = $_REQUEST;
        //  debug($arr_form_data);
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $fd = $arr_form_data['reportfrom'] . ' ' . '00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';

        /*  if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t H:m:s', strtotime($arr_form_data['reportfrom']. ' ' . '23:00:00' ));
        } */

        //added by megha on on 11_03_2020 changed for SH Infra
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $month = $arr_form_data['reportfrom'];
            $month1 = $month . '-01';
            $att_startdate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
            $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
            $att_startdate1 = $att_startdate['0']['0']['monthly_att_fromdate'];
            $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];
            $from = $att_startdate1;
            $to = date('Y-m-d H:m:s', strtotime($att_enddate1 . ' ' . '23:00:00'));
        }
        //end
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            // $arr_leavepolicygroupids =$arr_form_data[$str_criteria_item];
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }

        $condition = 'and  EmployeeDetails.status = 1';
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and  EmployeeDetails.status in('1','2')";
        }
        $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $condition .= " and EmployeeDetails.branch_code ='" . $cur_emp_branch . "'";
        }
        //employee branch wise sorting ends here
        $arr_leavepolicydetails_for_template = array();
        //  debug($arr_leavepolicygroupids);
        $arr_DetaildAttendance = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                //            $str_conditions = ' WHERE Attendance.branch_code="'.$leavepolicygroupid.'" and intime between "'.$fd.'" and "'.$Td.'";';
                //            $arr_leavepolicy_details = $this->AttendanceRegister->query(''
                //                    . 'SELECT '
                //                    . '*'
                //                    . 'FROM '
                //                    . '`client_db1`.`Attandance` AS `Attendance` ' 
                //                     .$str_conditions)
                //  debug($leavepolicygroupid);
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT `DeviceAttendance`.LOGDATE,`DeviceAttendance`.C1,"
                        . "`DeviceAttendance`.C2,`DeviceAttendance`.C3,`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`info`.*,"
                        . "`department`.`dept_name`,`empproff`.emp_dept FROM `device_attandance` AS `DeviceAttendance`"
                        . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_id` = `DeviceAttendance`.`emp_id`)"
                        . " LEFT JOIN `emp_proff` AS `empproff` ON (`empproff`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`) "
                        . " LEFT JOIN `department` AS `department` ON (`department`.`dept_code` = `empproff`.`emp_dept`)"
                        . " LEFT JOIN `employee_info` AS `info` ON (`info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                        . " WHERE DeviceAttendance.status = 'Y' and COALESCE(DeviceAttendance.C2,'X') not in('SIT') $condition and `LOGDATE` between '$from' and '$to' and EmployeeDetails.emp_pkey='$leavepolicygroupid' "
                        . " ORDER BY  `EmployeeDetails`.`emp_id`,LOGDATE");
                } else {
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT `DeviceAttendance`.LOGDATE,`DeviceAttendance`.C1,`DeviceAttendance`.C2,"
                        . "`DeviceAttendance`.C3,`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`branches`.branch_name,`info`.*"
                        . " FROM `device_attandance` AS `DeviceAttendance`"
                        . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_id` = `DeviceAttendance`.`emp_id`)"
                        . " LEFT JOIN `branches` AS `branches` ON (`DeviceAttendance`.`branch_code` = `branches`.`branch_code`) "
                        . " LEFT JOIN `employee_info` AS `info` ON (`info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                        . " WHERE DeviceAttendance.status = 'Y' and COALESCE(DeviceAttendance.C2,'X') not in('SIT') $condition and `LOGDATE` between '$from' and '$to' and EmployeeDetails.branch_code='$leavepolicygroupid'"
                        . " ORDER BY `EmployeeDetails`.`emp_id`,LOGDATE");
                }


                $arr_leavepolicydetails_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_leavepolicy_details,
                    // 'employees'=>$arr_leavepolicy_employees
                );
            }
            //debug($arr_leavepolicydetails_for_template);
            $arr_dates = array();
            foreach ($arr_leavepolicydetails_for_template as $val) {
                //debug($val);

                foreach ($val['summary'] as $dates) {
                    $date = date('Y-m-d', strtotime($dates['DeviceAttendance']['LOGDATE']));
                    $emp = $dates['info']['emp_pkey'];
                    $branch = $dates['info']['branch'];
                    //$arr_dates[$branch][$emp]['Name'] = $dates['Info'];
                    $arr_dates[$branch][$emp][$date][] = $dates;
                }
                //$arr_DetaildAttendance[] = $arr_dates;
            }
            //debug($arr_dates);
            $this->set('arr_leavepolicydetails_for_template', $arr_dates);
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        //debug($this_month_att);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('detailedattendance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('AttendanceDetailed.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Detailedattendance.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Detailed Attendance  Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );
                $rowcount = 3;
                $i = 0;
                $border_style1 = array(
                    'borders' => array(
                        'top' => array(
                            'style' =>
                                PHPExcel_Style_Border::BORDER_THICK,
                            'color' => array('argb' => '766f6e'),
                        )
                    )
                );
                //edited by athira on 17-06-2025
                if (!empty($arr_dates)) {
                    foreach ($arr_dates as $branch => $employees) {

                        $i += 1;
                        //$arr_daata = $value['summary'];


                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Attendance  Details of ' . $branch);

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $worksheet->mergeCells("A" . ($rowcount) . ":C" . ($rowcount));
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $rowcount = $rowcount + 2;
                        foreach ($employees as $employee => $date) {
                            $info = current($date);
                            //edited by athira on 22-08-2025
                            $empName = isset($info['0']['info']['EmpName']) ? $info['0']['info']['EmpName'] : '';
                            $empStatus = isset($info['0']['info']['emp_status']) ? $info['0']['info']['emp_status'] : '';

                            if ($empStatus == '2') {
                                $empName .= '(Resigned)';
                            }

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Employee NAME :' . $empName);
                            //end
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount), 'Employee ID : ' . $info['0']['info']['employee_id']);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Branch Name : ' . $info['0']['info']['branch']);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Department :' . $info['0']['info']['department']);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Designation :' . $info['0']['info']['designation']);
                            for ($i = 0; $i <= 8; $i++) {
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                            }
                            $worksheet->getStyle("A" . ($rowcount) . ":F" . ($rowcount + 1))->applyFromArray($border_style1);

                            $columnindex = 1;
                            // 

                            //$arr_data = $value['summary'];
                            if (count($date) > 0) {
                                $rowcount = $rowcount + 3;
                                $k = 1;
                                $border_style = array(
                                    'borders' => array(
                                        'bottom' => array(
                                            'style' =>
                                                PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => '766f6e'),
                                        )
                                    )
                                );

                                foreach ($date as $key => $val) {
                                    //debug($val);
                                    $col3 = 2;
                                    $col4 = 3;
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), date("M d", strtotime($key)));
                                    $worksheet->mergeCells("A" . ($rowcount) . ":A" . ($rowcount + 1));
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow((0), ($rowcount))->getFont()->setBold(true);
                                    foreach ($val as $value) {
                                        //debug($value);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex), ($rowcount), $value['DeviceAttendance']['C1']);
                                        $columnindex++;
                                    }
                                    $worksheet->getStyle("A" . ($rowcount) . ":F" . ($rowcount + 1))->applyFromArray($border_style);
                                    $columnindex = 1;
                                    $rowcount++;
                                    foreach ($val as $value) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex), ($rowcount), $value['DeviceAttendance']['LOGDATE']);
                                        $columnindex++;
                                    }
                                    $columnindex = 1;
                                    $rowcount++;
                                    $k++;
                                }
                            }
                            $rowcount++;
                        }
                    }
                } else {
                    $rowcount = 2; // set row just after the title
                    $worksheet->setCellValue('A' . $rowcount, 'No data available under the selected criteria.');
                    $worksheet->mergeCells("A{$rowcount}:J{$rowcount}");
                    $worksheet->getStyle("A{$rowcount}")->getFont()->setBold(true)->setSize(14);
                }

                //end

                $objPHPExcel->getActiveSheet()->setTitle('Detailed Attendance Report');
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
                $this->render('detailedattendance');
                break;
        }
    }

    //santhu
    private function generatetimeattendancereport($type = '', $mode = '')
    {
        $arr_form_data = $_REQUEST;

        $arr_registerentry_heads = array(
            'P' => 'Present',
            'L' => 'Leave',
            'WO' => 'Week Off',
            'HO' => 'Holiday',
            'A' => 'Absent',
            'LOP' => 'Loss Of Pay',
            'OTHERS' => 'Others'
        );
        $this->set('arr_registerentry_heads', $arr_registerentry_heads);

        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        //$fd = $arr_form_data['reportfrom'] . ' ' . '00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            //$from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            //$to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }

        $this->set("report_month", $report_month);
        $yearmonth = date('Y-m-1', strtotime($report_month));

        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        //debug($arr_form_data);
        $str_criteria_item1 = $arr_form_data['hidden-criteria1'];
        if (isset($arr_form_data[$str_criteria_item1]) > 0) {
            for ($i = 1; $i <= $int_criterias_count; $i++) {
                $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
                $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("fields" => "reportcriteria, reportcriteria_field", "conditions" => array("status" => 1, 'reporttype' => 'TimeAttendance', 'reportcriteria' => $str_criteria_item))));
                //debug($arr_reportcriterias);
                if (isset($arr_reportcriterias[0]['reportcriteria_field'])) {
                    $conditions[] = $arr_reportcriterias[0]['reportcriteria'] . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                }
                //$arr_leavepolicygroupids = $arr_form_data[$str_criteria_item];
                $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
                $arr_leavepolicygroupids = $crit;

                //debug($arr_leavepolicygroupids);
            }
            foreach ($arr_leavepolicygroupids as $branches) {
                $attend = $this->EmployeeDetails->query("select time_duration_check('$yearmonth','NULL','$branches')");
            }
            $str_conditions = implode(' AND ', $conditions);
            $query_timeattendance = "select EmployeeDetails.first_name,EmployeeDetails.last_name,emp.emp_company_id,Units.*,emp_detail_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey,emp.emp_company_id "
                . "from emp_detail_timeattandance "
                . "left join emp_details as EmployeeDetails on(EmployeeDetails.emp_pkey = emp_detail_timeattandance.emp_pkey)"
                . " left join emp_proff as emp on (emp.emp_fkey = EmployeeDetails.emp_pkey) "
                . "left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) "
                . "left join branches as Units on(Units.branch_code = EmployeeDetails.branch_code) "
                . "where EmployeeDetails.status=1 "
                . "and  $str_conditions "
                . "and yearmonth = '$yearmonth' order by EmployeeDetails.first_name,att_date";
            $arr_timeattendances = $this->DeviceAttendance->query($query_timeattendance);
            //debug($arr_timeattendances);
            $arr_branch_details = array();
        }
        $arr_timeattendances_for_template = array();
        if (!empty($arr_timeattendances)) {
            $arr_dates = array();
            foreach ($arr_timeattendances as $timeattendance) {

                $branch_code = isset($timeattendance['Units']['branch_code']) ? $timeattendance['Units']['branch_code'] : 0;
                if (!isset($arr_branch_details[$branch_code])) {
                    $arr_branch_details[$branch_code] = isset($timeattendance['Units']) ? $timeattendance['Units'] : array();
                }

                if (!isset($arr_timeattendances_for_template[$branch_code])) {
                    $arr_timeattendances_for_template[$branch_code] = array();
                }

                $emp_pkey = isset($timeattendance['emp_detail_timeattandance']['emp_pkey']) ? $timeattendance['emp_detail_timeattandance']['emp_pkey'] : 0;
                if ($emp_pkey) {
                    if (!isset($arr_timeattendances_for_template[$branch_code][$emp_pkey]['employeeinfo'])) {
                        $arr_timeattendances_for_template[$branch_code][$emp_pkey]['employeeinfo'] = isset($timeattendance['EmployeeDetails']) ? $timeattendance['EmployeeDetails'] : array();
                    }
                    if (!isset($arr_timeattendances_for_template[$branch_code][$emp_pkey]['emp'])) {
                        $arr_timeattendances_for_template[$branch_code][$emp_pkey]['emp'] = isset($timeattendance['emp']) ? $timeattendance['emp'] : array();
                    }
                    $att_date = isset($timeattendance['emp_detail_timeattandance']['att_date']) ? $timeattendance['emp_detail_timeattandance']['att_date'] : '';
                    $att_day = date('Y-m-d', strtotime($att_date));
                    if (!in_array($att_day, array_keys($arr_dates))) {
                        $arr_dates[$att_day] = $att_day;
                    }

                    $present = isset($timeattendance['emp_detail_timeattandance']['present']) ? $timeattendance['emp_detail_timeattandance']['present'] . ' ' : '';
                    $leaves = isset($timeattendance['emp_detail_timeattandance']['leaves']) ? $timeattendance['emp_detail_timeattandance']['leaves'] . ' ' : '';
                    $weekoff = isset($timeattendance['emp_detail_timeattandance']['weekoff']) ? $timeattendance['emp_detail_timeattandance']['weekoff'] . ' ' : '';
                    $holiday = isset($timeattendance['emp_detail_timeattandance']['holiday']) ? $timeattendance['emp_detail_timeattandance']['holiday'] . ' ' : '';
                    $others = isset($timeattendance['emp_detail_timeattandance']['others']) ? $timeattendance['emp_detail_timeattandance']['others'] : '';

                    if (!isset($arr_timeattendances_for_template[$branch_code][$emp_pkey]['registerentries'])) {
                        $arr_timeattendances_for_template[$branch_code][$emp_pkey]['registerentries'] = array();
                    }
                    $arr_timeattendances_for_template[$branch_code][$emp_pkey]['registerentries'][$att_date] = $present . $leaves . $weekoff . $holiday . $others;

                    //Present Count
                    if (!isset($arr_timeattendances_for_template[$branch_code][$emp_pkey]['registerentrysummary'][0])) {
                        $arr_timeattendances_for_template[$branch_code][$emp_pkey]['registerentrysummary'][0] = 0 + (substr_count(strtoupper($present), 'P') / 2);
                        ;
                    } else {
                        $arr_timeattendances_for_template[$branch_code][$emp_pkey]['registerentrysummary'][0] += (substr_count(strtoupper($present), 'P') / 2);
                    }


                    $att_in_time = isset($timeattendance['emp_detail_timeattandance']['att_in_time']) ? $timeattendance['emp_detail_timeattandance']['att_in_time'] : '';
                    $att_out_time = isset($timeattendance['emp_detail_timeattandance']['att_out_time']) ? $timeattendance['emp_detail_timeattandance']['att_out_time'] : '';
                    $duration = isset($timeattendance['emp_detail_timeattandance']['duration']) ? $timeattendance['emp_detail_timeattandance']['duration'] : '';
                    $arr_timeattendances_for_template[$branch_code][$emp_pkey]['checkin'][$att_day] = $att_in_time;
                    $arr_timeattendances_for_template[$branch_code][$emp_pkey]['checkout'][$att_day] = $att_out_time;
                    $arr_timeattendances_for_template[$branch_code][$emp_pkey]['duration'][$att_day] = $duration;
                }
            }
            //            /debug($arr_timeattendances_for_template);die();
            $this->set('arr_timeattendancereporttemplate', $arr_timeattendances_for_template);
            //debug($arr_timeattendances_for_template);
            $this->set('arr_dates', $arr_dates);

            if (isset($arr_branch_details) && !empty($arr_branch_details)) {
                $this->set('arr_branchinfo', $arr_branch_details);
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reporttimeattendance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('helvica', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('Timeattendance.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel':

                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Attendance.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Attendance Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                //autowidth
                for ($col = 'A'; $col !== 'AG'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:F1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );

                $rowcount = 2;
                $counts = 0;
                foreach ($arr_timeattendances_for_template as $key => $value) {
                    //                    debug($value);die();

                    $branch = (isset($arr_branchinfo[$branch_code]['branch_code']) ? $arr_branchinfo[$branch_code]['branch_code'] . ' - ' : '') . (isset($arr_branchinfo[$branch_code]['branch_name']) ? $arr_branchinfo[$branch_code]['branch_name'] : '');
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowcount . ':F' . $rowcount);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Attedance Reports of ' . $branch . ' For the month ' . date("Y-m", strtotime($yearmonth)));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                    $rowcount += 1;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Present Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Leave Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Holiday Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    for ($i = 0; $i <= 8; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                    }

                    $columnindex = 2;
                    foreach ($arr_dates as $key => $date) {

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnindex, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columnindex)->setWidth(12);


                        $columnindex++;
                    }
                    $rowcount += 1;

                    $arr_data = $value;
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                    if (count($arr_data) >= 0) {

                        foreach ($arr_data as $key => $val) {
                            //                            debug($val);
                            //                            die();
                            $columnindex = 0;
                            $counts += 1;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $counts);
                            $columnindex = 1;
                            $id = $val['emp']['emp_company_id'];
                            $name = $val['employeeinfo']['first_name'] . ' ' . $val['employeeinfo']['last_name'];


                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $id . ' ' . $name);

                            $columnindex = 2;
                            foreach ($val['registerentries'] as $key => $date) {

                                $dta = $date;

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                $columnindex++;
                            }

                            $rowcount++;
                            $columnindex = 1;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, "check in");
                            $columnindex = 2;
                            foreach ($val['checkin'] as $key => $date) {

                                if ($date) {
                                    $dta = date("m-d : h:i", strtotime($date));
                                } else {
                                    $dta = $date;
                                }
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                $columnindex++;
                            }

                            $rowcount++;
                            $columnindex = 1;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, "check out");
                            $columnindex = 2;
                            foreach ($val['checkout'] as $key => $date) {

                                if ($date) {
                                    $dta = date("m-d : h:i", strtotime($date));
                                } else {
                                    $dta = $date;
                                }
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                $columnindex++;
                            }
                            $rowcount++;
                            $columnindex = 1;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, "Duration");
                            $columnindex = 2;
                            foreach ($val['duration'] as $key => $date) {

                                $dta = $date;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                $columnindex++;
                            }
                            $rowcount++;
                        }
                    }
                    $rowcount1 = $rowcount + 1;
                }

                $objPHPExcel->getActiveSheet()->setTitle('Attendance');
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
                $this->render('reporttimeattendance');
                break;
        }
    }

    private function generatemobilelocationreport($type = '', $mode = '')
    {
        $arr_form_data = $_REQUEST;
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            // debug($arr_leavepolicygroupids);
        }
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            $condion1 = "";
            if ((isset($arr_form_data['select-criteria1']) && $arr_form_data['select-criteria1'] == 'EmployeeDetails')) {
                $arr_leavepolicygroupids = implode(", ", $arr_leavepolicygroupids);
                $condion1 .= 'and  emp_fkey in(' . $arr_leavepolicygroupids . ')';
            }

            $date = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');
            $month = date('m', strtotime($date));
            $year = date('Y', strtotime($date));


            $arr_mob_location = $this->MobileUserauditor->query("select a.emp_fkey,ei.designation,department,branch,a.userid,a.empname	,a.datetimecheck ,a.location,a.in_out 
                from (SELECT emp_fkey, mlu.userid, concat (first_name,' ',middle_name,' ' ,last_name) empname ,mlu.time_check as datetimecheck ,mlu.location,mlu.in_out
                FROM mob_user_login_auditor mlu , user_credentials uc left join employee_info as info on(info.emp_pkey=uc.emp_fkey)
                 where mlu.userid=uc.user_id 
                       union all 
                SELECT emp_fkey,mbuserloc.user_id,concat (first_name,' ',middle_name,' ' ,last_name) empname,mbuserloc.created_time as datetimecheck ,mbuserloc.location,
                'Updated Location' from mob_user_locations mbuserloc, user_credentials uc
                 left join employee_info as info on(info.emp_pkey=uc.emp_fkey)
                 where mbuserloc.user_id=uc.user_id) a, employee_info ei 
                where ei.emp_pkey=a.emp_fkey
                and  month(datetimecheck)='$month' and year(datetimecheck)='$year' $condion1
                order by empname, datetimecheck,in_out
                ,in_out
                ");

            //}
            //debug($arr_mob_location);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $this->set('date', $date);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            if (isset($arr_mob_location) && !empty($arr_mob_location)) {
                $this->set('arr_mob_location', $arr_mob_location);

                switch ($mode) {
                    case 'pdf':
                        $this->set('mode', 'pdf');
                        $view = new View($this, false);
                        $view_output = $view->render('reportmobilelocation');
                        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                        $html2pdf = new HTML2PDF('L', 'A3', 'en');
                        $html2pdf->pdf->SetDisplayMode('fullpage');
                        $html2pdf->writeHTML($view_output);
                        $html2pdf->Output('Mobilelocation.pdf', 'D');
                        break;
                    case 'excel':
                        $str_company_code = $this->Session->read('company_code');
                        $file_name = isset($str_company_code) ? $str_company_code . "_mobilelocation.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                        $objPHPExcel = new PHPExcel();

                        $objPHPExcel->getProperties()->setCreator("Administrator");
                        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                        $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                        $objPHPExcel->setActiveSheetIndex(0);

                        $worksheet = $objPHPExcel->getActiveSheet();

                        $worksheet->setCellValueByColumnAndRow(0, 1, "  Mobile Location Report");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                        for ($col = 'A'; $col !== 'J'; $col++) {
                            $objPHPExcel->getActiveSheet()
                                ->getColumnDimension($col)
                                ->setAutoSize(true);
                        }
                        $worksheet->mergeCells('A1:F1');
                        $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                        );
                        $worksheet->setCellValueByColumnAndRow(0, 2, "For the Month : " . $date);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(16);
                        $worksheet->mergeCells('A2:F2');
                        $rowcount = 3;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee ID');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Designation');
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Date Of join');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Departments');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Branch');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Action');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Location');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        for ($i = 0; $i <= 8; $i++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                        }
                        $columnindex = 0;
                        $rowcount = 4;
                        $empnme = "";
                        foreach ($arr_mob_location as $value) {

                            //$name=$name.$key;
                            $name = $value['a']['empname'];
                            $date = $value['a']['datetimecheck'];
                            $action = $value['a']['in_out'];
                            $location = $value['a']['location'];
                            $id = $value['a']['userid']; // correcte by sruthi 28/07/2016
                            $clas = $value['ei']['designation'];
                            //$join = $value['ei']['joining_date'];
                            $dept = $value['ei']['department'];
                            $unit = $value['ei']['branch'];

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                            if ($empnme == $name) {
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, " ");
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 2), ($rowcount), $id);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 3), ($rowcount), "");
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 4), ($rowcount), "");
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 5), ($rowcount), "");
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 6), ($rowcount), "");
                                // $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($rowcount1, $columnindex);
                                // $objPHPExcel->setActiveSheetIndex(0)->mergeCells(($columnindex+1).$rowcount.':'.($columnindex+1).($rowcount+1));
                            } else {
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 2), ($rowcount), $id);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 3), ($rowcount), $clas);
                                //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 4), ($rowcount), $join);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 5), ($rowcount), $dept);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 6), ($rowcount), $unit);
                            }

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $action);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $location);
                            $rowcount++;
                            //  $empnme = $value[0]['empname']; corrected by sruthi
                            $empnme = $value['a']['empname'];
                        }


                        $objPHPExcel->getActiveSheet()->setTitle('Mobile Location');
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
                        $this->render('reportmobilelocation');
                        break;
                }
            } else {
                echo "<div style='color:red'><h3>No record Found</h3></div>";
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
        }
    }

    //Over Time
    public function Overtimereport($type = '', $mode = '')
    {
        $arr_form_data = $_REQUEST;
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $fd = $arr_form_data['reportfrom'] . ' ' . '00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)
        $date_time = date('d-m-Y H:i');
        $date = $arr_form_data['reportfrom'];
        $this->set('date', $date);
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $from2 = date('Y-m', strtotime($arr_form_data['reportfrom']));



        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            // $arr_leavepolicygroupids =$arr_form_data[$str_criteria_item];
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        $condition = "WHERE EmployeeDetails.status = 1";
        //        debug($arr_form_data);
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

            $condition = "WHERE EmployeeDetails.status in('1','2')";
        }
        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                //            $str_conditions = ' WHERE Attendance.branch_code="'.$leavepolicygroupid.'" and intime between "'.$fd.'" and "'.$Td.'";';
                //            $arr_leavepolicy_details = $this->AttendanceRegister->query(''
                //                    . 'SELECT '
                //                    . '*'
                //                    . 'FROM '
                //                    . '`client_db1`.`Attandance` AS `Attendance` ' 
                //                     .$str_conditions)
                // debug($arr_leavepolicygroupids); exit();

                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT `OTMASTER`.*,"
                        . "`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`Info`.*,`EmployeeDetails`.status,last_approved_working_date, uc.user_id"
                        . " FROM `emp_ot_master` AS `OTMASTER`"
                        . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                        . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `termination` ON (`termination`.`emp_fkey`=`EmployeeDetails`.`emp_pkey` and `termination`.`status`=1)"
                        . "LEFT JOIN `user_credentials` AS `uc` ON (`uc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "$condition and OTMASTER.month = '$from' and EmployeeDetails.emp_pkey = '$leavepolicygroupid' and is_verified = 'Y' GROUP BY `emp_ot_master_pkey` ");
                    $reporttype = 'Employee';
                } else {
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT `OTMASTER`.*,"
                        . "`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`Info`.*,`EmployeeDetails`.status,last_approved_working_date, uc.user_id"
                        . " FROM `emp_ot_master` AS `OTMASTER`"
                        . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                        . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `termination` ON (`termination`.`emp_fkey`=`EmployeeDetails`.`emp_pkey` and `termination`.`status`=1)"
                        . "LEFT JOIN `user_credentials` AS `uc` ON (`uc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "$condition and OTMASTER.month = '$from' and EmployeeDetails.branch_code = '$leavepolicygroupid' and is_verified = 'Y' GROUP BY `emp_ot_master_pkey` ");
                    $reporttype = 'Branch';
                }

                $month = date("F", strtotime($from));
                $year = date("Y", strtotime($from));

                $arr_leavepolicydetails_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_leavepolicy_details,
                    // 'employees'=>$arr_leavepolicy_employees
                );
            }
        }
        // debug($arr_leavepolicydetails_for_template);exit;



        $this->set('reporttype', $arr_form_data['select-criteria1']);
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('overtime');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('OvertimeAttendance.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');

                $file_name = isset($str_company_code) ? $str_company_code . "_Approved Over Time - " . $from2 . ".xlsx" : "Approved Over Time" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Over Time Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );
                $worksheet->setCellValueByColumnAndRow(0, 1, "Approved Over Time - " . $month . " " . $year);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(14);

                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:L2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );


                if (empty($arr_leavepolicydetails_for_template[0]['summary'][0])) {
                    //               $worksheet->setCellValueByColumnAndRow(0, 3, "There is no data available under the selected criteria");
                    //               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                    //               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                    //               $worksheet->mergeCells('A3:M3');
                    //               $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                    //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    // );


                }
                // for ($col = 'A'; $col !== 'Z'; $col++) {
                //     $objPHPExcel->getActiveSheet()
                //             ->getColumnDimension($col)
                //             ->setAutoSize(true);
                // }
                // if (!empty($arr_leavepolicydetails_for_template>0)) {

                $rowcount = 3;
                $columncount = 0;
                $i = 0;


                // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                // $rowcount = $rowcount + 1;
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Sl No');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount), 'Employee ID');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee Name');
                // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Company ID');
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

                // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Company ID');
                // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Employee Name');
                // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Joining Date');
                // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Branch');
                // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Department');
                // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Designation');
                //Added by **ARUL P DAS on 3/1/2020
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . ($rowcount), 'Termination Date');
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . ($rowcount), 'Total Duration(In Hrs)');
                //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . ($rowcount), 'Approved');
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . ($rowcount), 'Approved Duration(In Hrs)');
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . ($rowcount), 'Approved / Rejected');
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . ($rowcount), 'Remarks');
                //set bond text size
                for ($i = 0; $i <= 11; $i++) {
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                }

                // debug($arr_leavepolicydetails_for_template[0]['summary']); exit();
                // if (!empty($arr_leavepolicydetails_for_template[0]['summary'])) {


                //Headings
                $k = 1;
                //$arr_leavepolicydetails_for_template
                $rowcount = $rowcount + 1;
                foreach ($arr_leavepolicydetails_for_template as $value) {


                    //    debug($value);
                    $i += 1;
                    $arr_daata = $value['summary'];
                    //debug($arr_daata);
                    $pp = isset($value['summary'][0]['Info']['branch']) ? $value['summary'][0]['Info']['branch'] : '';
                    if ($pp != '') {
                        $branch = isset($value['summary'][0]['Info']['branch']) ? $value['summary'][0]['Info']['branch'] : '';
                    } else {
                        $branch = isset($value['summary'][0]['Info']['dept_name']) ? $value['summary'][0]['department']['dept_name'] : '';
                    }
                    // debug($branch);
                    //die();
                    $arr_data = $value['summary'];

                    // $this -> set('$arr_data', $arr_data);

                    if (count($arr_data) > 0) {
                        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(24);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(24);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);

                        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(28);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(24);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(22);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(24);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(24);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(24);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(24);


                        $columnindex = 0;

                        // $rowcount = $rowcount + 1;
                        // $k = 1;
                        foreach ($arr_data as $val) {

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $k);

                            $name = $val['Info']['EmpName'];
                            $empid = isset($val['uc']['user_id']) ? $val['uc']['user_id'] : '';
                            $id = $val['Info']['employee_id'];
                            $branch = $val['Info']['branch'];
                            $designation = $val['Info']['designation'];
                            $department = $val['Info']['department'];
                            $join = $val['Info']['joining_date'];
                            $totel = round(($val['OTMASTER']['total_duration'] / 60), 2);
                            $verified = isset($val['OTMASTER']['set_duration']) ? round(($val['OTMASTER']['set_duration'] / 60), 2) : round(($val['OTMASTER']['total_duration'] / 60), 2);
                            // $approved = isset($val['OTMASTER']['is_verified']) == "Y" ? "Yes" : "NO";
                            $remarks = $val['OTMASTER']['remarks'];
                            $status = $val['EmployeeDetails']['status'];
                            $termination = $val['termination']['last_approved_working_date']; //last working day added by ***ARUL P DAS on 3/1/2020
                            // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $k);
                            if ($status == 2) {
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), $name . ' (Resigned)'); //Resigned status added bt **ARUL P DAS on 3/1/2020
                            } else {
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), $name);
                            }
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $empid);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $branch);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $department);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), $designation);
                            $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(7)->setAutoSize(true); //Edited by Akshay on 21-3-2024
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), $termination);
                            //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((10), ($rowcount), $approved);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), $totel);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((10), ($rowcount), $verified);
                            // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((11), ($rowcount), $approved);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((11), ($rowcount), $remarks);
                            $rowcount++;
                            if ($reporttype == 'Branch') {
                                $k++;
                            }
                        }
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $k);
                        if ($reporttype == 'Employee') {
                            $k++;
                        }
                    }


                    // }

                    //                if(empty($arr_leavepolicydetails_for_template[0]['summary'][0])) {
                    //               $worksheet->setCellValueByColumnAndRow(0, 3, "There is no data available under the selected criteria");
                    //               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                    //               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                    //               $worksheet->mergeCells('A3:M3');
                    //               $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                    //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    // );


                    //                  }
                    // $i = $i+1; 

                }

                $row = $rowcount - 1;

                if ($k == 1) {
                    $worksheet->mergeCells('A3:L3');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . (3), 'No data available under the selected criteria');
                    // $row=$rowcount-2;
                }

                $BStyle = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                //                for ($col = 'A'; $col !== 'Z'; $col++) {
                //     $objPHPExcel->getActiveSheet()
                //             ->getColumnDimension($col)
                //             ->setAutoSize(true);
                // }
                // $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
                if ($k != 1) {
                    $objPHPExcel->getActiveSheet()->getStyle('A1:L' . $row)->applyFromArray($BStyle);
                }

                //           for ($col = 'A'; $col !== 'Z'; $col++) {
                //     $objPHPExcel->getActiveSheet()
                //             ->getColumnDimension($col)
                //             ->setAutoSize(true);
                // }      
                $objPHPExcel->getActiveSheet()
                    ->getStyle('A4:L4000')
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                //Hide gridlines
                $objPHPExcel->getActiveSheet()->setShowGridlines(false);  //Edited by Akshay on 21-3-2024

                $objPHPExcel->getActiveSheet()->setTitle('Approved Over Time');
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
                // $this -> set('$arr_data', $arr_data);
                $this->set('month', $month);
                $this->set('year', $year);
                $this->set('mode', '');
                $this->render('overtime');
                break;

            //   die();
        }
    }

    public function generateOvertimereport($type = '', $mode = '')
    {
        $arr_form_data = $_REQUEST;
        //  debug($arr_form_data);
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $fd = $arr_form_data['reportfrom'] . ' ' . '00:00:00';
        $user_id = $this->Session->read('login_user_id');
        $date_time = date('d-m-Y H:i');
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-01', strtotime($arr_form_data['reportfrom']));
            //debug($from);
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
            //debug($to);
        }
        //$date = date('d-m-Y', strtotime($arr_form_data['reportfrom']));
        $date = date('F - Y', strtotime($arr_form_data['reportfrom']));
        $this->set('date', $date);


        //edited by sinisya 15-04-2024
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = $arr_form_data['reportfrom'];
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $month1 = $month . '-01';
        $att_startdate = $this->AttendanceRegister->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->AttendanceRegister->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_startdate1 = date("d", strtotime($att_startdate['0']['0']['monthly_att_fromdate']));
        $att_enddate1 = date("d", strtotime($att_enddate['0']['0']['monthly_att_todate']));

        $arr_date_in_selectedmonth = range(1, $att_enddate1);

        if ($att_startdate1 != 1) {
            $arr_date_in_prevmonth = range($att_startdate1, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }

        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        $this->set('arr_dates', $arr_dates);
        //end
        //$attend = $this->EmployeeDetails->query("select ot_duration_register('$from','','')");
        // $attend = $this->EmployeeDetails->query("select ot_duration_register('$yearmonth','$emp','$branch')");    
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            // $arr_leavepolicygroupids =$arr_form_data[$str_criteria_item];
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

            $condition = "WHERE empdetails.status in('1','2')";
        } else {
            $condition = "WHERE empdetails.status = 1";
        }
        $arr_leavepolicydetails_for_template = array();
        //  debug($arr_leavepolicygroupids);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                if ($str_criteria_item == 'EmployeeDetails') {
                    // debug($leavepolicygroupid); edited by sinsiya 0n 11-03-2024
                    $empbranch = $this->EmployeeDetails->query("select branch_code from employee_info where emp_pkey = '$leavepolicygroupid'");
                    $empbranchnew = $empbranch[0]['employee_info']['branch_code'];

                    $attend = $this->EmployeeDetails->query("select ot_duration_register('$from','$leavepolicygroupid','$empbranchnew')");
                    //debug($attend);
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("select empdetails.first_name,Info.*,empdetails.last_name,empdetails.status,emp_ot_timeattandance.*,"
                        . "wd.minuts_calc_perday,emp.emp_fkey from emp_ot_timeattandance "
                        . "left join emp_details as empdetails on(empdetails.emp_pkey = emp_ot_timeattandance.emp_pkey) "
                        . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `empdetails`.`emp_pkey`)"
                        . "left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) "
                        . "left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) "
                        . "$condition and empdetails.emp_pkey = '$leavepolicygroupid' and emp_ot_timeattandance.yearmonth = '$from'");




                    if (!empty($arr_leavepolicy_details)) {
                        $arr_leavepolicydetails_for_template[] = array(
                            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                            'summary' => $arr_leavepolicy_details

                        );
                    }
                } else {
                    //debug($leavepolicygroupid);

                    $arr_leavepolicy_emps = $this->DeviceAttendance->query("select distinct(emp.emp_fkey),Info.EmpName,empdetails.status"
                        . " from emp_ot_timeattandance "
                        . "left join emp_details as empdetails on(empdetails.emp_pkey = emp_ot_timeattandance.emp_pkey) "
                        . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `empdetails`.`emp_pkey`)"
                        . "left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) "
                        . "left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) "
                        . "$condition and empdetails.branch_code = '$leavepolicygroupid' and emp_ot_timeattandance.yearmonth = '$from'");

                    //  $branch_name = $arr_leavepolicy_details['0']['Info']['branch'];
                    if (isset($arr_leavepolicy_emps) && !empty($arr_leavepolicy_emps)) {
                        foreach ($arr_leavepolicy_emps as $emps) {
                            $emp = $emps['emp']['emp_fkey'];
                            //edited by sinsiya 0n 11-03-2024
                            $attend = $this->EmployeeDetails->query("select ot_duration_register('$from','$emp','$leavepolicygroupid')");
                            //  debug($attend);
                            $arr_leavepolicy_details = $this->DeviceAttendance->query("select Info.*,emp_ot_timeattandance.*,empdetails.status,"
                                . "wd.minuts_calc_perday from emp_ot_timeattandance "
                                . "left join emp_details as empdetails on(empdetails.emp_pkey = emp_ot_timeattandance.emp_pkey) "
                                . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `empdetails`.`emp_pkey`)"
                                . "left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) "
                                . "left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) "
                                . "$condition and empdetails.emp_pkey = '$emp' and emp_ot_timeattandance.yearmonth = '$from'");
                            $arr_leavepolicydetails_for_template[] = array(
                                'summary' => $arr_leavepolicy_details
                            );
                        }
                    }
                    //                        if (!isset($arr_leavepolicydetails_for_template[$leavepolicygroupid])) {
                    //                        $arr_leavepolicydetails_for_template[$leavepolicygroupid] = array(
                    //                            'branch_name' => $branch_name,
                    //                            'summary' => array($dataarray)
                    //                        );
                    //                    }
                    //                       
                    //                           
                    //                   }
                    //                   $arr_leavepolicydetails_for_template[] = array(
                    //                    'summary' => $dataarray
                    //                );

                }
            }

            //  debug($arr_leavepolicydetails_for_template);
            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        // debug($arr_leavepolicydetails_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('criteria', $str_criteria_item);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {

            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Overtime-" . $report_month . ".xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Over Time Report By Greatleap");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                //                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Over Time Attendance  Report - ".$date);
                //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                //                for ($col = 'A'; $col !== 'AF'; $col++) {
                //                    $objPHPExcel->getActiveSheet()
                //                            ->getColumnDimension($col)
                //                            ->setAutoSize(true);
                //                }
                //                $worksheet->mergeCells('A1:AE1');
                //                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                //                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                //                );
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 1, 'Note : If the shift policy is Flexible, will not be shown before and after OT minutes.');
                //EDITED BY SINSIYA 16-03-2024
                $worksheet->setCellValueByColumnAndRow(0, 2, "Overtime - " . $date);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(18);
                $worksheet->mergeCells('A2:Z2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );
                $worksheet->setCellValueByColumnAndRow(0, 3, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                $worksheet->mergeCells('A3:Z3');
                $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                if (count($arr_leavepolicydetails_for_template) == 0) {
                    $worksheet->setCellValueByColumnAndRow(0, 4, "No Data Found");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 4)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 4)->getFont()->setSize(12);
                    $worksheet->mergeCells('A4:Z4');
                    $worksheet->getStyle('A4')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );
                }
                $rowcount = 4;
                $i = 0;
                if ($str_criteria_item == 'EmployeeDetails') {
                    foreach ($arr_leavepolicydetails_for_template as $val) {
                        $i += 1;
                        $pp = isset($val['summary'][0]['Info']['branch']) ? $val['summary'][0]['Info']['branch'] : '';
                        if ($pp != '') {
                            $branch = isset($val['summary'][0]['Info']['branch']) ? $val['summary'][0]['Info']['branch'] : '';
                        } else {
                            $branch = isset($val['summary'][0]['Info']['dept_name']) ? $val['summary'][0]['department']['dept_name'] : '';
                        }
                        $arr_data = $val['summary'];
                        $name = $arr_data['0']['Info']['EmpName'];
                        $empid = $arr_data['0']['Info']['employee_id'];
                        //edited by sinsiya 19-03-2024
                        if (isset($arr_data['0']['empdetails']['status']) && $arr_data['0']['empdetails']['status'] == "2") {
                            $resign = "-(Resigned)";
                        } else {
                            $resign = "";
                        }
                        if (count($arr_data) > 0) {
                            if ($val['summary']['0']['emp_ot_timeattandance']['ot_duration'] > 0) {
                                //header                    
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $name . '(' . $empid . ')' . $resign);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(15);
                                $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowcount . ':G' . $rowcount);
                                //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Branch : ' . $branch);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setSize(12);
                                $objPHPExcel->getActiveSheet()->mergeCells('H' . $rowcount . ':AF' . $rowcount);
                                //merge cell
                                //$objPHPExcel->getActiveSheet()->mergeCells('A3:D3');
                                //set width
                                // $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                                // $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                                // $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(22);
                                // $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);

                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(10);
                                //field start                   
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Date');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                foreach ($arr_dates as $key => $date) {
                                    $datevalue = $date;
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $datevalue);
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                    $columncount++;
                                }
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Att IN:');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                foreach ($arr_dates as $date) {
                                    $found = false;

                                    foreach ($val['summary'] as $value) {
                                        $in = $value['emp_ot_timeattandance']['att_in_time'];
                                        if (isset($in)) {
                                            $dateTime = new DateTime($in);
                                            $in_date = intval($dateTime->format('j'));
                                            $in_time = $dateTime->format('d-m-Y H:i:s');
                                            if ($in_date == $date) {
                                                $found = true;
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $in_time);
                                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true);
                                                $columncount++; // Moved this line outside of the if condition
                                                break;
                                            }
                                        }
                                    }

                                    if (!$found) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), '');
                                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true); // Or any other placeholder text
                                        $columncount++; // Increment $columncount here as well
                                    }
                                }

                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Att OUT:');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                // foreach ($val['summary'] as $value) {
                                // $out = $value['emp_ot_timeattandance']['att_out_time'];
                                //   $dateTime = new DateTime($out);
                                //   $out_time = $dateTime->format('d-m-Y H:i:s');
                                //  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $out_time);
                                // $columncount++;
                                // }
                                foreach ($arr_dates as $date) {
                                    $found = false;

                                    foreach ($val['summary'] as $value) {
                                        $out = $value['emp_ot_timeattandance']['att_out_time'];
                                        if (isset($out)) {
                                            $dateTime = new DateTime($out);
                                            $out_date = intval($dateTime->format('j'));
                                            $out_time = $dateTime->format('d-m-Y H:i:s');
                                            if ($out_date == $date) {
                                                $found = true;
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $out_time);
                                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true);
                                                $columncount++; // Moved this line outside of the if condition
                                                break;
                                            }
                                        }
                                    }

                                    if (!$found) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), '');
                                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true); // Or any other placeholder text
                                        $columncount++; // Increment $columncount here as well
                                    }
                                }
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Min before on duty OT:');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                //foreach ($val['summary'] as $val3) {
                                //  $min = $val3['emp_ot_timeattandance']['min_bfr_on_dutty_cal_ot'];
                                //  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $min);
                                //  $columncount++;
                                // }
                                foreach ($arr_dates as $date) {
                                    $found = false;

                                    foreach ($val['summary'] as $val3) {
                                        $out = $val3['emp_ot_timeattandance']['att_out_time'];
                                        if (isset($out)) {
                                            $dateTime = new DateTime($out);
                                            $out_date = intval($dateTime->format('j'));
                                            $min = $val3['emp_ot_timeattandance']['min_bfr_on_dutty_cal_ot'];
                                            if ($out_date == $date) {
                                                $found = true;
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $min);
                                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true);
                                                $columncount++; // Moved this line outside of the if condition
                                                break;
                                            }
                                        }
                                    }

                                    if (!$found) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), '');
                                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true); // Or any other placeholder text
                                        $columncount++; // Increment $columncount here as well
                                    }
                                }
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Min after off duty OT:');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                //foreach ($val['summary'] as $val4) {
                                //$off = $val4['emp_ot_timeattandance']['min_aftr_off_dutty_cal_ot'];
                                // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $off);
                                // $columncount++;
                                // }
                                foreach ($arr_dates as $date) {
                                    $found = false;

                                    foreach ($val['summary'] as $val4) {
                                        $out = $val4['emp_ot_timeattandance']['att_out_time'];
                                        if (isset($out)) {
                                            $dateTime = new DateTime($out);
                                            $out_date = intval($dateTime->format('j'));
                                            $off = $val4['emp_ot_timeattandance']['min_aftr_off_dutty_cal_ot'];
                                            if ($out_date == $date) {
                                                $found = true;
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $off);
                                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true);
                                                $columncount++; // Moved this line outside of the if condition
                                                break;
                                            }
                                        }
                                    }

                                    if (!$found) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), '');
                                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true); // Or any other placeholder text
                                        $columncount++; // Increment $columncount here as well
                                    }
                                }
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'OT Duration:');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                $total = 0;
                                // foreach ($val['summary'] as $val6) {
                                //  $ot = $val6['emp_ot_timeattandance']['ot_duration'];
                                //  $total = $total + $val6['emp_ot_timeattandance']['ot_duration'];
                                //  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $ot);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                //  $columncount++;
                                // }
                                foreach ($arr_dates as $date) {
                                    $found = false;

                                    foreach ($val['summary'] as $val6) {
                                        $out = $val6['emp_ot_timeattandance']['att_out_time'];
                                        if (isset($out)) {
                                            $dateTime = new DateTime($out);
                                            $out_date = intval($dateTime->format('j'));
                                            $ot = $val6['emp_ot_timeattandance']['ot_duration'];
                                            //edited by sinsiya on 19-06-2024 hided the below code
                                            //$total = $total + $val6['emp_ot_timeattandance']['ot_duration'];
                                            if ($out_date == $date) {
                                                $found = true;
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $ot);
                                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true);
                                                $columncount++; // Moved this line outside of the if condition
                                                //edited by sinsiya on 19-06-2024
                                                $total = $total + $val6['emp_ot_timeattandance']['ot_duration'];
                                                break;
                                            }
                                        }
                                    }

                                    if (!$found) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), '');
                                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true); // Or any other placeholder text
                                        $columncount++; // Increment $columncount here as well
                                    }
                                }
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'OT Duration Hrs:');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                //foreach ($val['summary'] as $val6) {
                                //$hrs = isset($val6['emp_ot_timeattandance']['ot_duration']) ? round(($val6['emp_ot_timeattandance']['ot_duration'] / 60), 2) : '';
                                //  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $hrs);
                                //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                //  $columncount++;
                                // }
                                foreach ($arr_dates as $date) {
                                    $found = false;

                                    foreach ($val['summary'] as $val6) {
                                        $out = $val6['emp_ot_timeattandance']['att_out_time'];
                                        if (isset($out)) {
                                            $dateTime = new DateTime($out);
                                            $out_date = intval($dateTime->format('j'));
                                            $hrs = isset($val6['emp_ot_timeattandance']['ot_duration']) ? round(($val6['emp_ot_timeattandance']['ot_duration'] / 60), 2) : '';

                                            if ($out_date == $date) {
                                                $found = true;
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $hrs);
                                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true);
                                                $columncount++; // Moved this line outside of the if condition
                                                break;
                                            }
                                        }
                                    }

                                    if (!$found) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), '');
                                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true); // Or any other placeholder text
                                        $columncount++; // Increment $columncount here as well
                                    }
                                }
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Total OT Duration');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                $objPHPExcel->getActiveSheet()->mergeCells('B' . $rowcount . ':AF' . $rowcount);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $total . ' Min  -     ' . round(($total / 60), 2) . ' Hrs.');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                //                    else {
                                //                        $msg = 'No employees found under this shift';
                                //                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . ($rowcount), $msg);
                                //  
                                //                                      }

                                $rowcount++;
                            }
                        }
                    }
                    // $columnLetter = PHPExcel_Cell::stringFromColumnIndex($columncount);

                } else {
                    $prevBranch = null;
                    foreach ($arr_leavepolicydetails_for_template as $val) {
                        $i += 1;
                        $pp = isset($val['summary'][0]['Info']['branch']) ? $val['summary'][0]['Info']['branch'] : '';
                        if ($pp != '') {
                            $branch = isset($val['summary'][0]['Info']['branch']) ? $val['summary'][0]['Info']['branch'] : '';
                        } else {
                            $branch = isset($val['summary'][0]['Info']['dept_name']) ? $val['summary'][0]['department']['dept_name'] : '';
                        }
                        $arr_data = $val['summary'];
                        $name = $arr_data['0']['Info']['EmpName'];
                        $empid = $arr_data['0']['Info']['employee_id'];
                        //edited by sinsiya 19-03-2024
                        if (isset($arr_data['0']['empdetails']['status']) && $arr_data['0']['empdetails']['status'] == "2") {
                            $resign = "-(Resigned)";
                        } else {
                            $resign = "";
                        }
                        if (count($arr_data) > 0) {
                            if ($val['summary']['0']['emp_ot_timeattandance']['ot_duration'] > 0) {
                                if ($val['summary'][0]['Info']['branch'] != $prevBranch) {
                                    //header                    
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Over Time  Details of ' . $branch);
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(15);
                                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowcount . ':AF' . $rowcount);
                                    $rowcount++;
                                    $prevBranch = $val['summary'][0]['Info']['branch'];
                                }
                                //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Branch : ' . $branch);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $name . '(' . $empid . ')' . $resign);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                                $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowcount . ':AF' . $rowcount);
                                //merge cell
                                //$objPHPExcel->getActiveSheet()->mergeCells('A3:D3');
                                //set width
                                // $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                                // $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                                // $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(22);
                                // $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);

                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(10);
                                //field start                   
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Date');
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                foreach ($arr_dates as $key => $date) {
                                    $datevalue = $date;
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $datevalue);
                                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                    $columncount++;
                                }
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Att IN:');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                foreach ($arr_dates as $date) {
                                    $found = false;

                                    foreach ($val['summary'] as $value) {
                                        $in = $value['emp_ot_timeattandance']['att_in_time'];
                                        if (isset($in)) {
                                            $dateTime = new DateTime($in);
                                            $in_date = intval($dateTime->format('j'));
                                            $in_time = $dateTime->format('d-m-Y H:i:s');
                                            if ($in_date == $date) {
                                                $found = true;
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $in_time);
                                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true);
                                                $columncount++; // Moved this line outside of the if condition
                                                break;
                                            }
                                        }
                                    }

                                    if (!$found) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), '');
                                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true); // Or any other placeholder text
                                        $columncount++; // Increment $columncount here as well
                                    }
                                }

                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Att OUT:');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                // foreach ($val['summary'] as $value) {
                                // $out = $value['emp_ot_timeattandance']['att_out_time'];
                                //   $dateTime = new DateTime($out);
                                //   $out_time = $dateTime->format('d-m-Y H:i:s');
                                //  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $out_time);
                                // $columncount++;
                                // }
                                foreach ($arr_dates as $date) {
                                    $found = false;

                                    foreach ($val['summary'] as $value) {
                                        $out = $value['emp_ot_timeattandance']['att_out_time'];
                                        if (isset($out)) {
                                            $dateTime = new DateTime($out);
                                            $out_date = intval($dateTime->format('j'));
                                            $out_time = $dateTime->format('d-m-Y H:i:s');
                                            if ($out_date == $date) {
                                                $found = true;
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $out_time);
                                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true);
                                                $columncount++; // Moved this line outside of the if condition
                                                break;
                                            }
                                        }
                                    }

                                    if (!$found) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), '');
                                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true); // Or any other placeholder text
                                        $columncount++; // Increment $columncount here as well
                                    }
                                }
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Min before on duty OT:');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                //foreach ($val['summary'] as $val3) {
                                //  $min = $val3['emp_ot_timeattandance']['min_bfr_on_dutty_cal_ot'];
                                //  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $min);
                                //  $columncount++;
                                // }
                                foreach ($arr_dates as $date) {
                                    $found = false;

                                    foreach ($val['summary'] as $val3) {
                                        $out = $val3['emp_ot_timeattandance']['att_out_time'];
                                        if (isset($out)) {
                                            $dateTime = new DateTime($out);
                                            $out_date = intval($dateTime->format('j'));
                                            $min = $val3['emp_ot_timeattandance']['min_bfr_on_dutty_cal_ot'];
                                            if ($out_date == $date) {
                                                $found = true;
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $min);
                                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true);
                                                $columncount++; // Moved this line outside of the if condition
                                                break;
                                            }
                                        }
                                    }

                                    if (!$found) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), '');
                                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true); // Or any other placeholder text
                                        $columncount++; // Increment $columncount here as well
                                    }
                                }
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Min after off duty OT:');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                //foreach ($val['summary'] as $val4) {
                                //$off = $val4['emp_ot_timeattandance']['min_aftr_off_dutty_cal_ot'];
                                // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $off);
                                // $columncount++;
                                // }
                                foreach ($arr_dates as $date) {
                                    $found = false;

                                    foreach ($val['summary'] as $val4) {
                                        $out = $val4['emp_ot_timeattandance']['att_out_time'];
                                        if (isset($out)) {
                                            $dateTime = new DateTime($out);
                                            $out_date = intval($dateTime->format('j'));
                                            $off = $val4['emp_ot_timeattandance']['min_aftr_off_dutty_cal_ot'];
                                            if ($out_date == $date) {
                                                $found = true;
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $off);
                                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true);
                                                $columncount++; // Moved this line outside of the if condition
                                                break;
                                            }
                                        }
                                    }

                                    if (!$found) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), '');
                                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true); // Or any other placeholder text
                                        $columncount++; // Increment $columncount here as well
                                    }
                                }
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'OT Duration:');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                $total = 0;
                                // foreach ($val['summary'] as $val6) {
                                //  $ot = $val6['emp_ot_timeattandance']['ot_duration'];
                                //  $total = $total + $val6['emp_ot_timeattandance']['ot_duration'];
                                //  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $ot);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                //  $columncount++;
                                // }
                                foreach ($arr_dates as $date) {
                                    $found = false;

                                    foreach ($val['summary'] as $val6) {
                                        $out = $val6['emp_ot_timeattandance']['att_out_time'];
                                        if (isset($out)) {
                                            $dateTime = new DateTime($out);
                                            $out_date = intval($dateTime->format('j'));
                                            $ot = $val6['emp_ot_timeattandance']['ot_duration'];
                                            //edited by sinsiya on 19-06-2024 hided the below code
                                            // $total = $total + $val6['emp_ot_timeattandance']['ot_duration'];
                                            if ($out_date == $date) {
                                                $found = true;
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $ot);
                                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true);
                                                $columncount++; // Moved this line outside of the if condition
                                                //edited by sinsiya on 19-06-2024
                                                $total = $total + $val6['emp_ot_timeattandance']['ot_duration'];
                                                break;
                                            }
                                        }
                                    }

                                    if (!$found) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), '');
                                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true); // Or any other placeholder text
                                        $columncount++; // Increment $columncount here as well
                                    }
                                }
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'OT Duration Hrs:');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                //foreach ($val['summary'] as $val6) {
                                //$hrs = isset($val6['emp_ot_timeattandance']['ot_duration']) ? round(($val6['emp_ot_timeattandance']['ot_duration'] / 60), 2) : '';
                                //  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $hrs);
                                //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                //  $columncount++;
                                // }
                                foreach ($arr_dates as $date) {
                                    $found = false;

                                    foreach ($val['summary'] as $val6) {
                                        $out = $val6['emp_ot_timeattandance']['att_out_time'];
                                        if (isset($out)) {
                                            $dateTime = new DateTime($out);
                                            $out_date = intval($dateTime->format('j'));
                                            $hrs = isset($val6['emp_ot_timeattandance']['ot_duration']) ? round(($val6['emp_ot_timeattandance']['ot_duration'] / 60), 2) : '';

                                            if ($out_date == $date) {
                                                $found = true;
                                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $hrs);
                                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true);
                                                $columncount++; // Moved this line outside of the if condition
                                                break;
                                            }
                                        }
                                    }

                                    if (!$found) {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), '');
                                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columncount)->setAutoSize(true); // Or any other placeholder text
                                        $columncount++; // Increment $columncount here as well
                                    }
                                }
                                $rowcount = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Total OT Duration');
                                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(0)->setWidth(22);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $columncount = 1;
                                $objPHPExcel->getActiveSheet()->mergeCells('B' . $rowcount . ':AF' . $rowcount);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $total . ' Min  -     ' . round(($total / 60), 2) . ' Hrs.');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                //                    else {
                                //                        $msg = 'No employees found under this shift';
                                //                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . ($rowcount), $msg);
                                //                    }


                                $rowcount++;
                            }
                        }
                    }
                }
                $objPHPExcel->getActiveSheet()->getStyle('A4:AF' . ($rowcount - 1))->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                //                edited by sinsiya 18-03-2024
                $objPHPExcel->getActiveSheet()->setTitle('Overtime');
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
                //edited by sinsiya 18-03-2024
                $user_id = $this->Session->read('login_user_id');
                //date_default_timezone_set('Asia/Kolkata');
                $date_time = date('d-m-Y H:i');
                $this->set('user_id', $user_id);
                $this->set('date_time', $date_time);
                $this->render('overtimedetails');
                break;
        }
    }

    private function generateCheckinlogsReport($type, $mode)
    {
        $arr_form_data = $_REQUEST;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m-1');

        //Parsing the months
        $spli_month = explode(" - ", $month);
        $start_month = $month;
        //        isset($spli_month[0])?date('Y-m-d',  strtotime($spli_month[0])):date('Y-m-d');
        $end_month = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] . " " . "23:59:59" : date('Y-m-1');
        //        isset($spli_month[1])?date('Y-m-d',  strtotime($spli_month[1])):date('Y-m-d');
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
        $fd = $start_month . ' ' . '00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }
        $report_month = $arr_form_data['reportfrom'] . " - " . $arr_form_data['reportto'];
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');

        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
            }



            $today_cond = array(
                "DeviceAttendance.LOGDATE BETWEEN '$fd' and '$end_month' ",
                "DeviceAttendance.status >=" => "Y",
                "EI.employee_id is not null",
                "OR" => array(
                    "DeviceAttendance.C2 !=" => "SIT",
                    "DeviceAttendance.C2  is NULL"
                )
            );

            //edited by megha on 03_08_2019 order changed 1
            if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                $today_cond[] = array("EI.emp_pkey" => $arr_leavepolicygroupids);
                $order = "EmpName ASC,DeviceAttendance.LOGDATE ASC";
            } else {
                $today_cond[] = array("EI.branch_code" => $arr_leavepolicygroupids);
                $order = "Branch ASC, EmpName ASC ,DeviceAttendance.LOGDATE ASC";
            }

            if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                $today_cond[] = array("EI.emp_status in('1','2') ");
            } else {
                $today_cond[] = array("EI.emp_status" => "1");
            }


            $today_join[] = array(
                'table' => 'employee_info',
                'alias' => 'EI',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('DeviceAttendance.emp_id = EI.emp_id')
            );
            //emp_proff, employee_info joined by ***ARUL P DAS on 27_1_2020
            //<!--Added by megha device id on 20/07/19-->
            $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
            $today_att = $this->DeviceAttendance->find(
                "all",
                array(
                    //edited by megha on 03_08_2019 order changed 2
                    // "order" => "DeviceAttendance.LOGDATE ASC",
                    "order" => $order,
                    "conditions" => $today_cond,
                    'joins' => $today_join,
                    "fields" => "EI.employee_id,EI.Branch,EI.designation,EI.department,EI.EmpName,
                DeviceAttendance.C1,DeviceAttendance.C3,DeviceAttendance.LOGDATE,
                COALESCE(DeviceAttendance.C3,EI.Branch) AS Location,if(DeviceAttendance.deviceid in ('',0),(ifnull(DeviceAttendance.C2,'EDIT')),
                DeviceAttendance.deviceid) AS Device,EI.emp_status"
                )
            );

            $arr_resp = array(
                'data' => array()
            );


            $i = 0;
            foreach ($today_att as $key => $att) {

                $arr_resp['data'][$i][]/* ['EmpID'] */ = isset($att["EI"]['employee_id']) ? $att["EI"]['employee_id'] : '';
                $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att["EI"]['EmpName']) ? $att["EI"]['EmpName'] : $att["0"]['EmpName'];
                $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["EI"]['branch']) ? $att["EI"]['branch'] : '';
                $arr_resp['data'][$i][]/* ['Department'] */ = isset($att["EI"]['department']) ? $att["EI"]['department'] : '';
                $arr_resp['data'][$i][]/* ['Designation'] */ = isset($att["EI"]['designation']) ? $att["EI"]['designation'] : '';

                //EmpID,Designation,Department fields added by ***ARUL P DAS on 27/1/2020
                $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("d-m-Y", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
                $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["DeviceAttendance"]['C1']) ? $att["DeviceAttendance"]['C1'] : '';
                $arr_resp['data'][$i][]/* ['Location'] */ = isset($att[0]['Location']) ? $att[0]['Location'] : '';
                //$arr_resp['data'][$i][]/* ['Location'] */ = isset($att["DeviceAttendance"]['C3']) ? $att["DeviceAttendance"]['C3'] : '';//This location added by ARUL P DAS on 22_4_2020
                // <!--Added by megha device id on 20/07/19-->
                $arr_resp['data'][$i][]/* ['Location'] */ = isset($att[0]['Device']) ? $att[0]['Device'] : '';
                $arr_resp['data'][$i][]/* ['Status'] */ = isset($att['EmployeeDetails']['status']) ? $att['EmployeeDetails']['status'] : ''; //Status field added by ***ARUL P DAS on 3/1/2020
                $i++;
            }

            $this->set('arr_leavepolicydetails_for_template', $arr_resp);
            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("report_month", $report_month);
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('Dashboard');

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

                    $this->autoRender = false;
                    $this->layout = null;

                    // Increase memory & execution time for large reports
                    ini_set('memory_limit', '1024M'); // 1GB
                    set_time_limit(0);

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = !empty($str_company_code)
                        ? $str_company_code . "_checkin_logs.xlsx"
                        : "CheckinLogs_" . time() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();
                    $sheet = $objPHPExcel->setActiveSheetIndex(0);

                    /* ================= TITLE ================= */
                    $sheet->setCellValue('A1', 'Employee Check In / Out Logs Report');
                    $sheet->mergeCells('A1:K1');
                    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                    $sheet->getStyle('A1')->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('A1:K1')->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('D9D9D9');

                    $sheet->setCellValue('A2', 'Employee Check In / Out Logs Report - ' . $start_month . ' - ' . $end_month);
                    $sheet->mergeCells('A2:K2');
                    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                    $sheet->getStyle('A2')->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('A2:K2')->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('D9D9D9');

                    /* ================= HEADERS ================= */
                    $headers = array(
                        'Sl No',
                        'Employee ID',
                        'Employee Name',
                        'Branch',
                        'Department',
                        'Designation',
                        'Date',
                        'Time',
                        'Check-In/Out',
                        'Location',
                        'Punch Type'
                    );

                    if (empty($arr_resp['data'])) {
                        $sheet->setCellValueByColumnAndRow(0, 3, 'No data available under the selected criteria');
                        $sheet->mergeCells('A3:K3');
                    } else {
                        $headerRow = 3;
                        foreach ($headers as $col => $header) {
                            $sheet->setCellValueByColumnAndRow($col, $headerRow, $header);
                            $sheet->getStyleByColumnAndRow($col, $headerRow)->getFont()->setBold(true);
                            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
                        }

                        /* ================= DATA ================= */
                        $data = $arr_resp['data'];   // Your query result
                        $row = 4;
                        $i = 1;

                        // Use chunking to reduce memory usage (optional for extremely large datasets)
                        foreach ($data as $val) {

                            $empName = $val[1];
                            if (!empty($val[10]) && $val[10] == 2) {
                                $empName .= ' (Resigned)';
                            }

                            $sheet->setCellValueByColumnAndRow(0, $row, $i++);
                            $sheet->setCellValueByColumnAndRow(1, $row, $val[0]);
                            $sheet->setCellValueByColumnAndRow(2, $row, $empName);
                            $sheet->setCellValueByColumnAndRow(3, $row, $val[2]);
                            $sheet->setCellValueByColumnAndRow(4, $row, $val[3]);
                            $sheet->setCellValueByColumnAndRow(5, $row, $val[4]);
                            $sheet->setCellValueByColumnAndRow(6, $row, $val[5]);
                            $sheet->setCellValueByColumnAndRow(7, $row, $val[6]);
                            $sheet->setCellValueByColumnAndRow(8, $row, $val[7]);
                            $sheet->setCellValueByColumnAndRow(9, $row, $val[8]);
                            $sheet->setCellValueByColumnAndRow(10, $row, $val[9]);

                            $row++;

                            // Optional: periodically clear memory (helps with very large datasets)
                            if ($i % 5000 == 0) {
                                $objPHPExcel->garbageCollect();
                            }
                        }
                    }


                    /* ================= DOWNLOAD ================= */
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment; filename="' . $file_name . '"');
                    header('Cache-Control: max-age=0');

                    $writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                    $writer->save('php://output');
                    exit;


                default:
                    $this->set('mode', '');
                    $this->render('Dashboard');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }

    private function generateregularisationreport($type, $mode)
    {
        $arr_form_data = $_REQUEST;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $company_code = $this->Session->read('company_code'); //company_code
        $fromdate = date('d-m-Y', strtotime($arr_form_data['reportfrom']));
        $todate = date("d-m-Y", strtotime($arr_form_data['reportto']));
        $from_dates = isset($fromdate) ? $fromdate . ' 00:00' : '';
        $to_dates = isset($todate) ? $todate . ' 23:59' : '';
        $this->set('fromdate', $fromdate);
        $this->set('todate', $todate);

        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
        $date_time = date('d-m-Y H:i');
        $conditions = array();
        $arr_lateoutdata = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_lateoutdata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }

        if ($str_criteria_item == '') {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }

        if (!isset($arr_form_data[$str_criteria_item])) {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $conditions = " and ed.emp_status in('1','2')";
        } else {
            $conditions = " and ed.emp_status ='1'";
        }

        $arr_lateoutdata_for_template = array();
        if (isset($arr_lateoutdata) && !empty($arr_lateoutdata)) {
            foreach ($arr_lateoutdata as $lateoutdata) {
                if ($arr_form_data['select-criteria1'] == 'Units') {
                    $arr_lateoutdata = $this->EmployeeDetails->query("SELECT ed.*,edd.*, employee_regularaization.*,
           (select EmpName from employee_regularaization er
           left join employee_info as ed on (ed.emp_pkey = er.approved_person)
           where employee_regularaization.approved_person = er.approved_person limit 1) as person,
           (select EmpName from employee_info where emp_pkey = (select emp_proff.attr1 from employee_info 
           left join emp_proff on (employee_info.emp_pkey = emp_proff.emp_fkey)
           where employee_info.emp_id = employee_regularaization.empid limit 1)) as hierarchy_person
           FROM employee_regularaization
           left join employee_info as ed  on (ed.emp_id = employee_regularaization.empid)
           left join termination as edd  on (employee_regularaization.empid = edd.emp_fkey)
           where branch_code = '$lateoutdata' and LOGDATE BETWEEN '$fromdate' AND '$todate' $conditions order by emp_pkey,att_date,created_date ");
                } else {
                    $arr_lateoutdata = $this->EmployeeDetails->query("SELECT ed.*,edd.*, employee_regularaization.*,
           (select EmpName from employee_regularaization er
           left join employee_info as ed on (ed.emp_pkey = er.approved_person)
           where employee_regularaization.approved_person = er.approved_person limit 1) as person,
           (select EmpName from employee_info where emp_pkey = (select emp_proff.attr1 from employee_info 
           left join emp_proff on (employee_info.emp_pkey = emp_proff.emp_fkey)
           where employee_info.emp_id = employee_regularaization.empid limit 1)) as hierarchy_person
           FROM employee_regularaization
           left join employee_info as ed  on (ed.emp_id = employee_regularaization.empid)
           left join termination as edd  on (employee_regularaization.empid = edd.emp_fkey)
           where emp_pkey = '$lateoutdata' and LOGDATE BETWEEN '$fromdate' AND '$todate' $conditions order by emp_pkey,att_date,created_date");
                }

                if (!empty($arr_lateoutdata)) {
                    $arr_lateoutdata_for_template[] = array(
                        'summary' => $arr_lateoutdata
                    );
                }
            }
            $this->set('arr_lateoutdata_for_template', $arr_lateoutdata_for_template);
            $cr = $arr_form_data['select-criteria1'];
            $this->set('cr', $cr);
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
                    $view_output = $view->render('regularisation');
                    //   debug($view_output);
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('EmployeeLateOutdurationReport.pdf', 'D');
                    // $this->render('earlyinreport');                
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_AttendanceRegularisation.xlsx" : "Attendance";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $todate = ' - ' . $todate;

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Attendance Regularisation - " . $fromdate . $todate);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells('A1:S1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );
                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }

                    $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    $worksheet->mergeCells('A2:S2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );


                    if (count($arr_lateoutdata_for_template) == 0) {
                        //  echo "<h3>No Data Available With The Selected Criteria</h3>";

                        //print nodata
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                        $worksheet->mergeCells('A3:F3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                        );
                    } else {

                        $columncount = 0;
                        $rowcount = 3;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No. ');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Company ID');
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
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Attendance Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Time');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Direction');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Created Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Remark');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'Hierarchy Person');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, 'Approved / Rejected By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, 'Approved / Rejected Person Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 16), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, 'Approved / Rejected Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 17), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 18), $rowcount, 'Status');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 18), $rowcount)->getFont()->setBold(true);

                        $rowcount += 1;
                        $i = 1;
                        foreach ($arr_lateoutdata_for_template as $value) {
                            $arr_data = $value['summary'];
                            if (count($arr_data) >= 0) {
                                foreach ($arr_data as $key => $val) {
                                    $columnindex = 0;
                                    // $empstatus = isset($val['ed']['emp_status']) && $val['ed']['emp_status']=="2" ? '(Resigned)':'' ;
                                    $emp_id = $val['employee_regularaization']['empid'];
                                    $company_id = $val['ed']['employee_id'];
                                    $name = $val['ed']['EmpName'];
                                    $join_date = $val['ed']['joining_date'];
                                    $join = date('d-m-Y', strtotime($join_date));
                                    $branch = $val['ed']['branch'];
                                    $dep = $val['ed']['department'];
                                    $designation = $val['ed']['designation'];
                                    $termin_date = $val['edd']['last_approved_working_date'];
                                    $termin = !empty($termin_date) ? date('d-m-Y', strtotime($termin_date)) : '';
                                    $at_date = $val['employee_regularaization']['att_date'];
                                    $dateTime = new DateTime($at_date);
                                    $att_date = $dateTime->format('d-m-Y H:i:s');
                                    $time = $val['employee_regularaization']['LOGTIME'];
                                    $direction = $val['employee_regularaization']['C1'];
                                    $cdate = $val['employee_regularaization']['created_date'];
                                    $dateTime = new DateTime($cdate);
                                    $c_date = $dateTime->format('d-m-Y H:i:s');
                                    $remark1 = $val['employee_regularaization']['C3'];
                                    if ($val['employee_regularaization']['remarks'] == 'Approved By Admin' || $val['employee_regularaization']['remarks'] == 'Rejected By Admin') {
                                        $approved_rejected_by = 'Admin';
                                    } else {
                                        $approved_rejected_by = $val['0']['person'];
                                    }
                                    $hierarchy = isset($val['0']['hierarchy_person']) ? $val['0']['hierarchy_person'] : '';
                                    $approved_rejected_remark = $val['employee_regularaization']['remarks'];
                                    if ($val['employee_regularaization']['updated_date'] != '0000-00-00 00:00:00') {
                                        $approvedrejected_date = $val['employee_regularaization']['updated_date'];
                                        $dateTime = new DateTime($approvedrejected_date);
                                        $approved_rejected_date = $dateTime->format('d-m-Y H:i:s');
                                    } else {
                                        $approved_rejected_date = '';
                                    }
                                    switch ($val['employee_regularaization']['approved']) {
                                        case "P":
                                            $status = 'Pending';
                                            break;
                                        case "A":
                                            $status = 'Approved';
                                            break;
                                        case "R":
                                            $status = 'Rejected';
                                            break;
                                    }
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $emp_id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $company_id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $dep);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $designation);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $termin);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $att_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $time);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $direction);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12) . $rowcount, $c_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowcount, $remark1);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 14) . $rowcount, $hierarchy);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 15) . $rowcount, $approved_rejected_by);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 16) . $rowcount, $approved_rejected_remark);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 17) . $rowcount, $approved_rejected_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 18) . $rowcount, $status);

                                    $columnindex = $columnindex + 8;

                                    $rowcount++;
                                    $i = $i + 1;
                                    $BStyle = array(
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN
                                            )
                                        )
                                    );
                                    $row = $rowcount - 1;
                                    $objPHPExcel->getActiveSheet()->getStyle('A1:S' . $row)->applyFromArray($BStyle);
                                    $objPHPExcel->getActiveSheet()
                                        ->getStyle('B3:B40000')
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()
                                        ->getStyle('C3:A40000')
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                }
                            }
                            $rowcount1 = $rowcount + 1;
                        }
                    }

                    $objPHPExcel->getActiveSheet()->setTitle('Attendance Regularisation');
                    /* header footer */
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                    /* header footer */

                    /*print Set up*/
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                    /*print Set up*/
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
                    $this->render('regularisation');
                    break;
            }
        }
    }

    //Edited by Akshay on 13-2-2024

    private function generatemovementsreport($type, $mode)
    {
        $arr_form_data = $_REQUEST;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $company_code = $this->Session->read('company_code'); //company_code
        $fromdate = date('d-m-Y', strtotime($arr_form_data['reportfrom']));
        $todate = date("d-m-Y", strtotime($arr_form_data['reportto']));
        $from_dates = isset($fromdate) ? $fromdate . ' 00:00' : '';
        $to_dates = isset($todate) ? $todate . ' 23:59' : '';
        $this->set('fromdate', $fromdate);
        $this->set('todate', $todate);


        //Edited by Akshay on 13-2-2024
        function changeDateFormat($dateString)
        {
            // Create a DateTime object from the original date string
            $dateString = ltrim($dateString, '- ');
            $originalDate = DateTime::createFromFormat('d-m-Y', $dateString);
            if ($originalDate === false) {
                debug($dateString);
                exit;
            }
            // Format the date in the desired format
            $formattedDate = $originalDate->format('d F Y');
            return $formattedDate;
        }

        // function getDate($dateString){
        //     $dateTime = new DateTime($dateString);
        //     $date = $dateTime->format('Y-m-d');
        //     return $date;
        // }

        function changeMonthFormat($dateString)
        {
            // Create a DateTime object from the original date string
            $dateString = ltrim($dateString, '- ');
            $originalDate = DateTime::createFromFormat('d-m-Y', $dateString);
            if ($originalDate === false) {
                debug($dateString);
                exit;
            }
            // Format the date in the desired format
            $formattedDate = $originalDate->format('d M Y');
            return $formattedDate;
        }

        function changeDateFormat2($dateString)
        {
            // Create a DateTime object from the original date string
            $dateString = ltrim($dateString, '- ');
            $originalDate = DateTime::createFromFormat('d-m-Y', $dateString);
            if ($originalDate === false) {
                debug($dateString);
                exit;
            }
            // Format the date in the desired format
            $formattedDate = $originalDate->format('Y-m-d');
            return $formattedDate;
        }

        function minutesToHours($minutes)
        {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;

            return sprintf("%02d:%02d:%02d", $hours, $remainingMinutes, 0);
        }

        // function calculateDuration($inTime, $outTime)
        // {
        //     debug($inTime);
        //     debug($outTime);
        //     $inDateTime = new DateTime($inTime);
        //     $outDateTime = new DateTime($outTime);
        //     debug($inDateTime);
        //     debug($outDateTime);

        //     $interval = $inDateTime->diff($outDateTime);

        //     // Format the duration
        //     $duration = sprintf(
        //         '%02d:%02d:%02d',
        //         $interval->h,
        //         $interval->i,
        //         $interval->s
        //     );
        //     debug($duration);exit;
        //     return $duration;
        // }

        function calculateDuration($inTime, $outTime)
        {
            if (empty($inTime) || empty($outTime)) {
                return '';
            }

            $inDateTime = new DateTime($inTime);
            $outDateTime = new DateTime($outTime);

            $interval = $inDateTime->diff($outDateTime);

            // Format the duration
            $duration = sprintf(
                '%02d:%02d:%02d',
                $interval->h,
                $interval->i,
                $interval->s
            );

            return $duration;
        }


        function changeTimeFormat($originalTime)
        {
            $formattedTime = date("h:i:s A", strtotime($originalTime));
            return $formattedTime;
        }

        function changeDateTimeFormat($originalDateTime)
        {
            $dateTime = new DateTime($originalDateTime);
            $formattedTime = $dateTime->format("h:i:s A");
            return $formattedTime;
        }

        function lateEarlyFormat($originalTimeString)
        {
            // Create a DateTime object
            $dateTime = new DateTime($originalTimeString);
            // Format the DateTime object without milliseconds
            $formattedTime = $dateTime->format("H:i:s");
            return $formattedTime;
        }

        function changeDurationFormat($timeString = '')
        {
            // Convert time string to seconds
            $seconds = strtotime($timeString) - strtotime('00:00:00');

            // Create a DateInterval object
            $interval = new DateInterval('PT' . abs($seconds) . 'S');

            // Create a DateTime object with a base time (e.g., midnight)
            $baseTime = new DateTime('00:00:00');

            // Add the interval to the base time
            $dateTime = $baseTime->add($interval);

            // Format the DateTime object in the desired format
            $formattedTime = $dateTime->format('h:i:s');
            return $formattedTime;
        }

        function compareDurations($duration)
        {
            $interval = new DateInterval('PT0S'); // Represents zero seconds
            $durationInterval = new DateInterval($duration);

            $compareResult = $interval->format('%r%S') - $durationInterval->format('%r%S');

            return $compareResult;
        }

        function convertMinutesToHoursMinutesSeconds($minutes)
        {
            if ($minutes == 0 || $minutes == '' || $minutes == null) {
                return '';
            }
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;

            $formattedTime = sprintf('%02d:%02d:00', $hours, $remainingMinutes);

            return $formattedTime;
        }



        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
        $date_time = date('d-m-Y H:i');
        $conditions = array();
        $arr_empdata = array();
        $arr_lateoutdata = array();
        $arr_lateoutdata_unit = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_empdata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }

        if ($str_criteria_item == '') {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }

        if (!isset($arr_form_data[$str_criteria_item])) {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $conditions = " and ei.emp_status in('1','2')";
        } else {
            $conditions = " and ei.emp_status ='1'";
        }

        $fromdateString = changeDateFormat2($fromdate);
        $todateString = changeDateFormat2($todate);
        $arr_branch_code = array();
        $arr_lateoutdata_for_template = array();
        if (isset($arr_empdata) && !empty($arr_empdata)) {
            foreach ($arr_empdata as $lateoutdata) {
                if ($arr_form_data['select-criteria1'] == 'Units') {
                    //To process attendance
                    $month1 = date('Y-m', strtotime($fromdateString));

                    $outputParameter = array();
                    $outputParameter[] = $company_code; //company_code
                    $outputParameter[] = (isset($lateoutdata) && $lateoutdata != '') ? $lateoutdata : '';
                    $outputParameter[] = $user_id; //user id
                    $outputParameter[] = (isset($month1) && $month1 != '') ? date('Y-m-d', strtotime($month1)) : '';
                    $month = (isset($month1) && $month1 != '') ? date('Y-m', strtotime($month1)) : '';
                    $this->AttendanceRegister->query("UPDATE `attendance_register_update` SET `status` = '0'
                                                        WHERE DATE_FORMAT(month_year,'%Y-%m') = '$month' ");
                    try {
                        $out = $this->AttendanceRegister->insertUpdateAttendanceRegisterProc($outputParameter);
                    } catch (Exception $e) {
                    }

                    $attend = $this->AttendanceRegister->query("SELECT ot_duration_register('$month-01', '', '')");
                    // processregisterentries($lateoutdata, $month1);
                    $month2 = date('Y-m', strtotime($todateString));
                    if ($month1 != $month2) {
                        $outputParameter = array();
                        $outputParameter[] = $company_code; //company_code
                        $outputParameter[] = (isset($lateoutdata) && $lateoutdata != '') ? $lateoutdata : '';
                        $outputParameter[] = $user_id; //user id
                        $outputParameter[] = (isset($month2) && $month2 != '') ? date('Y-m-d', strtotime($month2)) : '';
                        $month = (isset($month2) && $month2 != '') ? date('Y-m', strtotime($month2)) : '';
                        //    debug($month);
                        $this->AttendanceRegister->query("UPDATE `attendance_register_update` SET `status` = '0'
                                                            WHERE DATE_FORMAT(month_year,'%Y-%m') = '$month' ");
                        try {
                            $out = $this->AttendanceRegister->insertUpdateAttendanceRegisterProc($outputParameter);
                        } catch (Exception $e) {
                        }

                        $attend = $this->AttendanceRegister->query("SELECT ot_duration_register('$month-01', '', '')");
                        // processregisterentries($lateoutdata, $month2);
                    }
                    // debug(); exit;
                    $arr_lateoutdata = $this->EmployeeDetails->query("SELECT ei.emp_pkey, ei.emp_id, ei.EmpName, ei.emp_status, ei.employee_id,ei.branch, ei.designation,ei.department,wd.day_time_desc, wd.minuts_calc_perday, wd.day_time_desc,wd.working_time1,wd.on_dutty1, wd.off_dutty1, wd.isnextday, eo.LogDate, eo.ShiftEndTime, eo.OutTime,eo.EarlyOutTime, eo.OffDutyTime, 
                    eli.ShiftTime, eli.InTime, eli.LateTime, eli.LateInLimit, eli.LogDate,edta.att_in_time, edta.att_out_time, edta.duration, edta.present, edta.att_date, section.section_name
                                                                        FROM emp_detail_timeattandance edta
                                                                        LEFT JOIN employee_info ei ON (ei.emp_pkey = edta.emp_pkey )
                                                                        LEFT JOIN emp_proff ep ON ep.emp_fkey = ei.emp_pkey
                                                                        LEFT JOIN working_day_time_procedures wd ON wd.day_time_seq = ep.day_time_seq
                                                                        LEFT JOIN emp_early_out eo ON (eo.emp_pkey = ei.emp_pkey AND eo.LogDate = edta.att_out_time)
                                                                        LEFT JOIN emp_late_in eli ON ( eli.emp_pkey = ei.emp_pkey AND eli.LogDate = edta.att_in_time)
                                                                        LEFT JOIN emp_ot_timeattandance eot ON (eot.emp_pkey = ei.emp_pkey AND eot.att_date = edta.att_date)
                                                                        
                                                                        LEFT JOIN section section ON (ep.emp_sep_priv = section.id)

                                                                        WHERE ei.branch_code = '$lateoutdata' AND edta.att_date  BETWEEN '$fromdateString' AND '$todateString' $conditions
                                                                        -- AND edta.att_in_time IS NOT NULL AND edta.att_out_time IS NOT NULL 
                                                                        AND (edta.att_in_time IS NOT NULL OR edta.att_out_time IS NOT NULL)
                                                                        GROUP BY ei.emp_pkey,edta.att_date  
                                                                        ORDER BY ei.EmpName,edta.att_date");

                    if (!empty($arr_lateoutdata)) {
                        foreach ($arr_lateoutdata as $key => $data) {
                            $pkey = isset($data['ei']['emp_pkey']) ? $data['ei']['emp_pkey'] : 0;
                            $emp_id = isset($data['ei']['emp_id']) ? $data['ei']['emp_id'] : 0;
                            $date = isset($data['edta']['att_date']) ? $data['edta']['att_date'] : 0;

                            $arr_movement = $this->EmployeeDetails->query("SELECT TIME(dh.LOGDATE) as time, dh.C1
                                                                            FROM device_attandance dh
                                                                            WHERE dh.emp_id = '$emp_id' AND DATE(dh.LOGDATE) = '$date' AND dh.status = 'Y';                            
                                                                            ");
                            $arr_ot = $this->EmployeeDetails->query("SELECT eot.ot_duration FROM emp_ot_timeattandance eot
                                                                            WHERE eot.emp_pkey = '$pkey' AND eot.att_date = '$date'
                                                                            ");
                            $ot = isset($arr_ot[0]['eot']['ot_duration']) ? $arr_ot[0]['eot']['ot_duration'] : '';
                            $resultArray = array();
                            $resultString = '';

                            foreach ($arr_movement as $movement) {
                                // Check if both 'time' and 'C1' keys are present
                                if (isset($movement[0]['time'], $movement['dh']['C1'])) {
                                    $status = ($movement['dh']['C1'] === 'out') ? 'OUT' : strtoupper($movement['dh']['C1']);
                                    // $resultString .= changeDateTimeFormat($movement[0]['time']) . "($status), ";
                                    $resultArray[] = changeDateTimeFormat($movement[0]['time']) . "($status)";
                                }
                            }

                            $resultString = implode(', ', $resultArray);
                            $arr_lateoutdata[$key]['movement'][] = $resultString;
                            $arr_lateoutdata[$key]['ot'][] = $ot;
                        }
                    }
                } else {
                    try {
                        $arr_branch = $this->EmployeeDetails->query("SELECT ei.branch_code FROM employee_info ei WHERE ei.emp_pkey = '$lateoutdata';");
                        $branch_code = isset($arr_branch[0]['ei']['branch_code']) ? $arr_branch[0]['ei']['branch_code'] : '';

                        if (($branch_code != '') && !in_array($branch_code, $arr_branch_code)) {
                            $arr_branch_code[] = $branch_code;
                            $month1 = date('Y-m', strtotime($fromdateString));
                            $outputParameter = array();
                            $outputParameter[] = $company_code; //company_code
                            $outputParameter[] = (isset($branch_code) && $branch_code != '') ? $branch_code : '';
                            $outputParameter[] = $user_id; //user id
                            $outputParameter[] = (isset($month1) && $month1 != '') ? date('Y-m-d', strtotime($month1)) : '';
                            $month = (isset($month1) && $month1 != '') ? date('Y-m', strtotime($month1)) : '';

                            $this->AttendanceRegister->query("UPDATE `attendance_register_update` SET `status` = '0'
                                                                WHERE DATE_FORMAT(month_year,'%Y-%m') = '$month' ");

                            try {
                                $out = $this->AttendanceRegister->insertUpdateAttendanceRegisterProc($outputParameter);
                            } catch (Exception $e) {
                            }
                            $attend = $this->AttendanceRegister->query("SELECT ot_duration_register('$month-01', '', '')");
                            // processregisterentries($branch_code, $month1);
                            $month2 = date('Y-m', strtotime($todateString));
                            if ($month1 != $month2) {
                                $arr_branch_code[] = $branch_code;
                                $month1 = date('Y-m', strtotime($fromdateString));
                                $outputParameter = array();
                                $outputParameter[] = $company_code; //company_code
                                $outputParameter[] = (isset($branch_code) && $branch_code != '') ? $branch_code : '';
                                $outputParameter[] = $user_id; //user id
                                $outputParameter[] = (isset($month2) && $month2 != '') ? date('Y-m-d', strtotime($month2)) : '';
                                $month = (isset($month2) && $month2 != '') ? date('Y-m', strtotime($month2)) : '';
                                //    debug($month);
                                $this->AttendanceRegister->query("UPDATE `attendance_register_update` SET `status` = '0'
                                                                    WHERE DATE_FORMAT(month_year,'%Y-%m') = '$month' ");
                                try {
                                    $out = $this->AttendanceRegister->insertUpdateAttendanceRegisterProc($outputParameter);
                                } catch (Exception $e) {
                                }
                                $attend = $this->AttendanceRegister->query("SELECT ot_duration_register('$month-01', '', '')");
                                // processregisterentries($branch_code, $month2);
                            }
                        }

                        $arr_lateoutdata = $this->EmployeeDetails->query("SELECT ei.emp_pkey, ei.emp_id, ei.EmpName,ei.emp_status, ei.employee_id,ei.branch, ei.designation,ei.department,wd.day_time_desc, wd.minuts_calc_perday, wd.day_time_desc,wd.working_time1,wd.on_dutty1, wd.off_dutty1, wd.isnextday, eo.LogDate, eo.ShiftEndTime, eo.OutTime,eo.EarlyOutTime, eo.OffDutyTime, 
                                                                                eli.ShiftTime, eli.InTime, eli.LateTime, eli.LateInLimit, eli.LogDate,edta.att_in_time, edta.att_out_time, edta.duration, edta.present, edta.att_date, section.section_name
                                                                                FROM emp_detail_timeattandance edta
                                                                                LEFT JOIN employee_info ei ON (ei.emp_pkey = edta.emp_pkey )
                                                                                LEFT JOIN emp_proff ep ON ep.emp_fkey = ei.emp_pkey
                                                                                LEFT JOIN working_day_time_procedures wd ON wd.day_time_seq = ep.day_time_seq
                                                                                LEFT JOIN emp_early_out eo ON (eo.emp_pkey = ei.emp_pkey AND edta.att_out_time = eo.LogDate)
                                                                                LEFT JOIN emp_late_in eli ON ( eli.emp_pkey = ei.emp_pkey AND eli.LogDate = edta.att_in_time)
                                                                                LEFT JOIN emp_ot_timeattandance eot ON eot.emp_pkey = ei.emp_pkey AND eot.att_in_time = edta.att_in_time
                                                                                LEFT JOIN section section ON ep.emp_sep_priv = section.id

                                                                                WHERE edta.emp_pkey = '$lateoutdata' AND edta.att_date  BETWEEN '$fromdateString' AND '$todateString' $conditions
                                                                                AND (edta.att_in_time IS NOT NULL OR edta.att_out_time IS NOT NULL)
                                                                                -- AND edta.att_in_time IS NOT NULL 
                                                                                -- AND edta.att_out_time IS NOT NULL 
                                                                                -- AND edta.present IS NOT NULL
                                                                                GROUP BY ei.emp_pkey, edta.att_date, edta.att_in_time, edta.att_out_time  
                                                                                ORDER BY ei.EmpName,edta.att_date");
                    } catch (Exception $e) {
                        debug($e);
                        exit;
                    }


                    if (!empty($arr_lateoutdata)) {
                        foreach ($arr_lateoutdata as $key => $data) {
                            $pkey = isset($data['ei']['emp_pkey']) ? $data['ei']['emp_pkey'] : 0;
                            $emp_id = isset($data['ei']['emp_id']) ? $data['ei']['emp_id'] : 0;
                            $date = isset($data['edta']['att_date']) ? $data['edta']['att_date'] : 0;

                            $arr_movement = $this->EmployeeDetails->query("SELECT TIME(dh.LOGDATE) as time, dh.C1
                                                                            FROM device_attandance dh
                                                                            WHERE dh.emp_id = '$emp_id' AND DATE(dh.LOGDATE) = '$date' AND dh.status = 'Y';                            
                                                                            ");
                            $arr_ot = $this->EmployeeDetails->query("SELECT eot.ot_duration FROM emp_ot_timeattandance eot
                                                                            WHERE eot.emp_pkey = '$pkey' AND eot.att_date = '$date'
                                                                            ");
                            $ot = isset($arr_ot[0]['eot']['ot_duration']) ? $arr_ot[0]['eot']['ot_duration'] : '';

                            $resultArray = array();
                            $resultString = '';

                            foreach ($arr_movement as $movement) {
                                // Check if both 'time' and 'C1' keys are present
                                if (isset($movement[0]['time'], $movement['dh']['C1'])) {
                                    $status = ($movement['dh']['C1'] === 'out') ? 'OUT' : strtoupper($movement['dh']['C1']);
                                    $resultArray[] = changeDateTimeFormat($movement[0]['time']) . "($status)";
                                    // $resultString .= changeDateTimeFormat($movement[0]['time']) . "($status), ";
                                }
                            }

                            $resultString = implode(', ', $resultArray);
                            $arr_lateoutdata[$key]['movement'][] = $resultString;
                            $arr_lateoutdata[$key]['ot'][] = $ot;
                        }
                    }
                }
                // debug($arr_lateoutdata); exit;
                if (!empty($arr_lateoutdata)) {
                    $arr_lateoutdata_for_template[] = array(
                        'summary' => $arr_lateoutdata
                    );
                }
            }

            $this->set('arr_lateoutdata_for_template', $arr_lateoutdata_for_template);
            $cr = $arr_form_data['select-criteria1'];
            $this->set('cr', $cr);
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
                    $view_output = $view->render('movements');
                    //   debug($view_output);
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('EmployeeLateOutdurationReport.pdf', 'D');
                    // $this->render('earlyinreport');                
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_AttendanceMovements_" . $fromdateString . "_to_" . $todateString . ".xlsx" : "AttendanceMovements";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $objPHPExcel->getActiveSheet()->freezePane('H4');

                    $todate = ' - ' . $todate;

                    $worksheet->setCellValueByColumnAndRow(0, 1, isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : '' . " - " . $fromdate . $todate);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells('A1:G1');
                    $worksheet->mergeCells('H1:T1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT, )
                    );
                    // Adjust cell padding
                    $cellStyle = $worksheet->getStyleByColumnAndRow(0, 1);
                    // $cellStyle->getAlignment()->setIndent(50);

                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }

                    $worksheet->setCellValueByColumnAndRow(0, 2, "Daily Attendance - Employee Movements - " . changeMonthFormat($fromdate) . " - " . changeMonthFormat($todate));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    $worksheet->mergeCells('A2:T2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, )
                    );


                    if (count($arr_lateoutdata_for_template) == 0) {
                        //  echo "<h3>No Data Available With The Selected Criteria</h3>";
                        $objPHPExcel->getActiveSheet()->freezePane(false);
                        //print nodata
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria.");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(false);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                        $worksheet->mergeCells('A3:G3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, )
                        );
                    } else {

                        $columncount = 1;
                        $rowcount = 3;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                        $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount);
                        $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount) . $rowcount, 'Date ');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'NAME');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Location');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Section');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'SHIFT');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'SHIFT START TIME');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'IN TIME');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'SHIFT END TIME');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'OUT TIME');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'SCH. HRS');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'ACT. HRS');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'LATE');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, 'EARLY');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, 'OT');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 16), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, 'STATUS');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 17), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 18), $rowcount, 'EMP. MOVEMENT');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 18), $rowcount)->getFont()->setBold(true);

                        // debug($arr_lateoutdata_for_template); exit;

                        $rowcount += 1;
                        $i = 1;
                        foreach ($arr_lateoutdata_for_template as $value) {
                            $arr_data = $value['summary'];
                            // debug( $arr_data); exit;
                            // exit;
                            if (count($arr_data) >= 0) {
                                foreach ($arr_data as $key => $val) {
                                    $columnindex = 0;
                                    // debug($val);
                                    // $empstatus = isset($val['ed']['emp_status']) && $val['ed']['emp_status']=="2" ? '(Resigned)':'' ;
                                    $emp_id = $val['ei']['employee_id'];
                                    $name = $val['ei']['EmpName'];

                                    if (isset($val['ei']['emp_status']) && ($val['ei']['emp_status'] == 2)) {
                                        $res_status = " (Resigned)";
                                    } else {
                                        $res_status = '';
                                    }
                                    $branch = $val['ei']['branch'];
                                    $dep = $val['ei']['department'];
                                    $designation = $val['ei']['designation'];
                                    $section = isset($val['section']['section_name']) ? $val['section']['section_name'] : '';
                                    $shift = $val['wd']['day_time_desc'];
                                    $sch_hrs = minutesToHours($val['wd']['working_time1']);
                                    $shift_str_time = isset($val['wd']['on_dutty1']) ? changeTimeFormat($val['wd']['on_dutty1']) : '';
                                    $shift_end_time = isset($val['wd']['off_dutty1']) ? changeTimeFormat($val['wd']['off_dutty1']) : '';
                                    $next_day = $val['wd']['isnextday'];
                                    $inTime = isset($val['edta']['att_in_time']) ? changeDateTimeFormat($val['edta']['att_in_time']) : '';
                                    $outTime = isset($val['edta']['att_out_time']) ? changeDateTimeFormat($val['edta']['att_out_time']) : '';
                                    $act_hrs = calculateDuration($inTime, $outTime);

                                    $at_date = $val['edta']['att_date'];
                                    $dateTime = new DateTime($at_date);
                                    $att_date = $dateTime->format('d-M-Y');


                                    $lateInLogDate = isset($val['eli']['LogDate']) ? new DateTime($val['eli']['LogDate']) : new DateTime('');
                                    $lateInLogDate = $lateInLogDate->format('Y-m-d');
                                    // debug($lateInLogDate);exit;
                                    $dateTime = $dateTime->format('Y-m-d');
                                    if ($lateInLogDate == $dateTime) {
                                        $late_time = isset($val['eli']['LateTime']) ? (($val['eli']['LateTime']) >= 0 ? lateEarlyFormat($val['eli']['LateTime']) : '') : '';
                                    } else {
                                        $late_time = '';
                                    }

                                    $earlyOutLogDate = isset($val['eo']['LogDate']) ? new DateTime($val['eo']['LogDate']) : new DateTime('');
                                    $earlyOutLogDate = $earlyOutLogDate->format('Y-m-d');
                                    $offDuty = isset($val['eo']['OffDutyTime']) ? ($val['eo']['OffDutyTime']) : '';
                                    $earlyOutTime = isset($val['eo']['OutTime']) ? ($val['eo']['OutTime']) : '';
                                    if (($earlyOutLogDate == $dateTime) && ((strtotime($offDuty) - strtotime('TODAY')) >= (strtotime($earlyOutTime) - strtotime('TODAY')))) {
                                        // debug($dateTime);exit;
                                        $early_time = isset($val['eo']['EarlyOutTime']) ? (($val['eo']['EarlyOutTime']) >= 0 ? lateEarlyFormat($val['eo']['EarlyOutTime']) : '') : '';
                                    } else {
                                        $early_time = '';
                                    }

                                    $status = $val['edta']['present'];
                                    $overtime = isset($val['ot'][0]) ? convertMinutesToHoursMinutesSeconds($val['ot'][0]) : '';
                                    $movement = isset($val['movement'][0]) ? $val['movement'][0] : '';
                                    // exit;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $i);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $att_date);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $emp_id);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $name . $res_status);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $branch);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $dep);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $designation);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $section);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $shift);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $shift_str_time);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $inTime);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $shift_end_time);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12) . $rowcount, $outTime);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowcount, $sch_hrs);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 14) . $rowcount, $act_hrs);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 14) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 15) . $rowcount, $late_time);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 15) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 16) . $rowcount, $early_time);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 16) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 17) . $rowcount, $overtime);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 17) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 18) . $rowcount, $status);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 19) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 19) . $rowcount, $movement);
                                    $style = $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 19) . $rowcount);
                                    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                    $columnindex = $columnindex + 8;

                                    $rowcount++;
                                    $i = $i + 1;

                                    $row = $rowcount - 1;

                                    // $objPHPExcel->getActiveSheet()
                                    //     ->getStyle('B3:B40000')
                                    //     ->getAlignment()
                                    //     ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()
                                        ->getStyle('C3:A40000')
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                    //Align left
                                    // $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                                    //     array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                    // );
                                }
                            }
                            $rowcount1 = $rowcount + 1;
                        }
                        // exit;
                    }

                    $BStyle = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    if (isset($rowcount)) {
                        $objPHPExcel->getActiveSheet()->getStyle('A1:T' . ($rowcount - 1))->applyFromArray($BStyle);
                    }

                    // debug($highestRow); exit;
                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    $objPHPExcel->getActiveSheet()->setTitle('Attendance Movements');
                    /* header footer */
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                    /* header footer */

                    /*print Set up*/
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                    /*print Set up*/
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
                    $this->render('movements');
                    break;
            }
        }
    }
    // Edited by Akshay on 10-12-2024
    private function generateLOPreport($mode)
    {
        $arr_form_data = $_REQUEST;
        date_default_timezone_set('Asia/Kolkata');

        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $date = date('d-m-Y');
        $this->set('date', $date);
        $date_time = date('d-m-Y H:i');
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 = $month . '-01';
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        // $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('login_user_id');
        // $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));

        //added by megha on on 11_03_2020 changed for SH Infra
        $month = $arr_form_data['reportfrom'];
        // $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $month1 = $month . '-01';
        $att_startdate = $this->AttendanceRegister->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->AttendanceRegister->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_startdate1 = date("d", strtotime($att_startdate['0']['0']['monthly_att_fromdate']));
        $att_enddate1 = date("d", strtotime($att_enddate['0']['0']['monthly_att_todate']));

        $arr_date_in_selectedmonth = range(1, $att_enddate1);

        if ($att_startdate1 != 1) {
            $arr_date_in_prevmonth = range($att_startdate1, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }
        //end

        $hiddenreporttype = $arr_form_data['hidden-report-type'];
        $this->set('reporttype', $hiddenreporttype);
        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        $this->set('arr_dates', $arr_dates);
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
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

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->AttendanceRegister->query("select UCASE(ifnull(occurance,'LOP')) AS abbr from salary_head_items where ucase(item_type)='LEAVE' AND occurance != 'LOP'");
        $arr_leaveabbr = array();
        $arr_leavetype = array();
        foreach ($arr_leavetypes as $leaveabbr => $leave) {
            $arr_leaveabbr[] = strtoupper($leave[0]['abbr']) . "/" . strtoupper($leave[0]['abbr']);
            $arr_leavetype[] = strtoupper($leave[0]['abbr']);
        }

        $query = "CALL insert_update_att_reg('$company_code','NULL','$user_id','$month');";

        $arr_leavepolicydetails_for_template = array();
        $new = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                // debug($leavepolicygroupid);
                $fields = 'AttendanceRegister.*,EmployeeDetails.status,Termination.last_approved_working_date,Branch.branch_name,config.hirc_leval,Info.*, uc.user_id';

                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branch',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'AttendanceRegister.branch_code = Branch.branch_code',
                            'Branch.status = 1'

                        )
                    ),
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmployeeDetails',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'AttendanceRegister.emp_fkey = EmployeeDetails.emp_pkey'
                        )
                    ),
                    array(
                        'table' => 'employee_info',
                        'alias' => 'Info',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmployeeDetails.emp_pkey  = Info.emp_pkey')

                    ),
                    array(
                        'table' => 'emp_config',
                        'alias' => 'config',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmployeeDetails.emp_pkey  = config.emp_fkey', 'config.type = "HIERARCHY"', 'config.status = 1')

                    ),
                    array(
                        'table' => 'termination',
                        'alias' => 'Termination',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmployeeDetails.emp_pkey  = Termination.emp_fkey', 'Termination.status = 1')
                    ),
                    array(
                        'table' => 'user_credentials',
                        'alias' => 'uc',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmployeeDetails.emp_pkey  = uc.emp_fkey')

                    ),
                );

                $conditions = array("Branch.status" => "1"); //"isdelete"=>"N",
                if ($arr_form_data['hidden-criteria1'] == "Units") {
                    $conditions[] = 'AttendanceRegister.branch_code="' . $leavepolicygroupid . '" and AttendanceRegister.month_year = "' . $report_month . '"  ';
                } else {
                    $conditions[] = 'EmployeeDetails.emp_pkey="' . $leavepolicygroupid . '" and AttendanceRegister.month_year = "' . $report_month . '"';
                }
                if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

                    $conditions[] = "EmployeeDetails.status in('1','2')";
                } else {
                    $conditions[] = "EmployeeDetails.status ='1' ";
                }

                // Edited by Akshay on 21-1-2025
                if ($arr_form_data['hidden-criteria1'] == "Units") {
                    $order = array(
                        "CASE WHEN config.hirc_leval IS NULL THEN 0 ELSE config.hirc_leval END",
                        "config.hirc_leval",
                        "Info.EmpName ASC"
                    );
                } else {
                    $order = array("Info.EmpName", "Info.branch");
                }
                // End

                $arr_leavepolicy_details = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, "order" => $order));

                if (!empty($arr_leavepolicy_details)) {
                    $arr_leavepolicydetails_for_template[] = array(

                        'summary' => $arr_leavepolicy_details,

                    );
                }
            }

            function getcounts($input, $arr_datas)
            {
                $result = preg_grep('~' . $input . '~', $arr_datas);
                return count($result);
            }

            foreach ($arr_leavepolicydetails_for_template as $key => $value) {
                foreach ($value['summary'] as $ky => $vaal) {
                    $int_days_leave = 0;
                    $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P/P")) + count(array_keys($vaal["AttendanceRegister"], "P/A")) / 2 + count(array_keys($vaal["AttendanceRegister"], "A/P")) / 2 + count(array_keys($vaal["AttendanceRegister"], "WFH"));
                    $count_arr = count($vaal["AttendanceRegister"]);

                    foreach ($arr_leavetype as $leaves) {

                        $j = 0;

                        foreach ($vaal["AttendanceRegister"] as $dat) {
                            if ($j > 6 && $j < ($count_arr - 8)) {

                                $arr_temp = explode("/", $dat);

                                foreach ($arr_temp as $temp) {

                                    if ($leaves == $temp) {
                                        $int_days_leave += 1;
                                    }
                                }
                            }
                            $j++;
                        }
                    }


                    $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "WO")) + getcounts(preg_quote("WO/", '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote("/WO", '~'), $vaal["AttendanceRegister"]) / 2;

                    $int_days_LOPs = getcounts(preg_quote("LOP/", '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote("/LOP", '~'), $vaal["AttendanceRegister"]) / 2 + count(array_keys($vaal["AttendanceRegister"], "LOP"));
                    $int_days_holidays_holi = count(array_keys($vaal["AttendanceRegister"], "HO"));
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = getcounts(preg_quote('P/', '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote('/P', '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote('WFH/', '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote('/WFH', '~'), $vaal["AttendanceRegister"]) / 2 + count(array_keys($vaal["AttendanceRegister"], "WFH")) + getcounts(preg_quote('WFO/', '~'), $vaal["AttendanceRegister"]) / 2 + getcounts(preg_quote('/WFO', '~'), $vaal["AttendanceRegister"]) / 2 + count(array_keys($vaal["AttendanceRegister"], "WFO")) - getcounts(preg_quote("LOP/", '~'), $vaal["AttendanceRegister"]) / 2;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave / 2;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['lops'] = $int_days_LOPs;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['HO'] = $int_days_holidays_holi;
                    if ($vaal["AttendanceRegister"]['isdelete'] == 'N') {
                        $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $vaal["AttendanceRegister"]['presant_total'];
                        $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['lops'] = $vaal["AttendanceRegister"]['lop_total'];
                        $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['HO'] = $vaal["AttendanceRegister"]['holiday_total'];
                        $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $vaal["AttendanceRegister"]['leave_total'];
                    }
                }
            }

            // debug($arr_leavepolicydetails_for_template);exit();

            $present_employees = array();
            $new_array = array();

            foreach ($arr_leavepolicydetails_for_template as $value) {


                $arr_data = $value['summary'];

                foreach ($arr_data as $key => $val) {
                    $date1 = (date("Y-m", strtotime($month)));

                    if ($val['Termination']['last_approved_working_date'] != null) {
                        $term_date = (date("Y-m", strtotime($val['Termination']['last_approved_working_date'])));
                    } else {
                        $term_date = $val['Termination']['last_approved_working_date'];
                    }
                    // if ($date1 <= $term_date || $term_date == null) {
                    //     $new_array[] = $val;
                    // }
                    $new_array[] = $val;
                }
                $present_employees['list'] = $new_array;
            }
            if (!empty($present_employees['list'])) {
                $present_employee['summary'] = $present_employees['list'];
            }

            if (empty($present_employees['list'])) {
                $present_employee = array();
            }

            $branch_array = array();
            $branch_employees = array();
            if ($arr_form_data['hidden-criteria1'] == "Units") {
                foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                    if (isset($present_employee['summary'])) {
                        foreach ($present_employee['summary'] as $value) {
                            $branch = $value['AttendanceRegister']['branch_code'];
                            if ($branch == $leavepolicygroupid) {
                                $branch_array[$leavepolicygroupid]['summary'][] = $value;
                            }
                        }
                        if (!empty($branch_array[$leavepolicygroupid])) {
                            $branch_employees[] = $branch_array[$leavepolicygroupid];
                        }
                    }
                }
            } else {

                if (isset($present_employee['summary'])) {
                    foreach ($present_employee['summary'] as $value) {
                        $branch_array = array();
                        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                            $branch_array[$leavepolicygroupid]['summary'][] = $value;
                        }
                        if (!empty($branch_array[$leavepolicygroupid])) {
                            $branch_employees[] = $branch_array[$leavepolicygroupid];
                        }
                    }
                }
            }
            //           if(!empty($branch_employees)){
            //                         $branch_employee = $branch_employees;
            //                    }
            //           if(empty($branch_employees)){
            //                         $branch_employee = array();
            //                 } 



            $this->set('present_employees', $branch_employees);
            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $report_month1 = $report_month;
            $timestamp = strtotime($report_month . '-01'); // Add a day to create a valid date
            $report_month = date('F Y', $timestamp); // Format as 'November 2024'
            $this->set("report_month", $report_month);
            $str_company_code = $this->Session->read('company_code');
            switch ($mode) {
                case 'pdf':

                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    // $view_output = '<h4>Report</h4>'; // or use $view->render('lopreport') for dynamic view content
                    $view_output = $view->render('lopreport');
                    // $view_output = preg_replace('/<h4/', '<div class="page-break"></div><h4', $view_output);
                    // debug($view_output);exit;
                    try {
                        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                    } catch (Exception $e) {
                        debug($e);
                    }
                    $html2pdf = new HTML2PDF('L', 'A4', 'en');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);

                    $file_name = isset($str_company_code)
                        ? $str_company_code . "_LOP_" . $report_month1 . ".pdf"
                        : "LOP" . time() . ".pdf";

                    $html2pdf->Output($file_name, 'D');
                    break;
                case 'excel':

                    $file_name = isset($str_company_code) ? $str_company_code . "_LOP_" . $report_month1 . ".xlsx" : "LOP" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Attendance Register By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "LOP - " . $report_month);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells("A1:N1");
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );
                    date_default_timezone_set('Asia/Kolkata');
                    $worksheet->mergeCells("A2:N2");
                    $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );

                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }

                    if (!empty($present_employee)) {
                        $rowcount = 3;
                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 0), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
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
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'LOP Count');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $columnindex = $columncount + 10;

                        $alphabets = array();
                        $alphabet = 'A';
                        while ($alphabet != 'CZ') {
                            $alphabets[] = $alphabet++;
                        }


                        $rowcount = 4;
                        //edited by megha 2/12/2019 serial no.corrected
                        if ($arr_form_data['hidden-criteria1'] == "EmployeeDetails") {
                            $k = 1;
                        }
                        $k = 1;
                        $total_lop = 0;
                        foreach ($present_employee as $value['summary']) {
                            $branch = isset($value['summary']['0']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : 'No Datas Found Under This Branch';

                            $arr_data = isset($value['summary']) ? $value['summary'] : 'null';

                            if (count($arr_data) >= 0) {
                                //edited by megha 2/12/2019 serial no.corrected
                                if ($arr_form_data['hidden-criteria1'] == "Units") {
                                }
                                foreach ($arr_data as $key => $val) {

                                    //Calculating LOP
                                    $count = 0;
                                    foreach ($arr_dates as $key => $date) {
                                        $newIndex = 'FIELD' . ($key + 1);
                                        $dta = $val['AttendanceRegister'][$newIndex];

                                        $arr = explode("/", $dta);

                                        foreach ($arr as $ar) {
                                            if ($ar == null || $ar == 'A' || $ar == 'LOP' || $ar == '') {
                                                if (count($arr) == 2) {
                                                    $count = $count + 1;
                                                } elseif (count($arr) == 1) {
                                                    $count = $count + 2;
                                                }
                                            }
                                        }
                                    }

                                    $count = $count / 2;
                                    $columnindex = 0;
                                    $name = $val['Info']['EmpName'];
                                    $status = isset($val['EmployeeDetails']['status']) && $val['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : '';
                                    $present = $val['AttendanceRegister']['days_present'];
                                    $leave = $val['AttendanceRegister']['days_leave'];
                                    $holidays = $val['AttendanceRegister']['days_holidays'];
                                    $holi = $val['AttendanceRegister']['HO'];
                                    $lop = $count;
                                    $id = $val['Info']['employee_id'];
                                    $eid = $val['Info']['emp_id'];
                                    $user_id = isset($val['uc']['user_id']) ? $val['uc']['user_id'] : '';
                                    $des = $val['Info']['designation'];
                                    $join = $val['Info']['joining_date'];
                                    $join_date = date('d-m-Y', strtotime($join));
                                    $termination = $val['Termination']['last_approved_working_date'];


                                    $termin_date = !empty($termination) ? date('d-m-Y', strtotime($termination)) : '';



                                    $department = $val['Info']['department'];
                                    $branch = $val['Info']['branch'];
                                    $statusv = $val['AttendanceRegister']['isdelete'];


                                    switch ($statusv) {
                                        case "N":
                                            $statusv = 'Verified';
                                            break;
                                        case "Y":
                                            $statusv = 'Not Verified';
                                            break;
                                        default:
                                            $statusv = 'Invalid data';
                                    }

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $user_id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $name . $status);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $department);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $des);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $termin_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $lop);
                                    $total_lop += $lop;

                                    $k++;
                                    $rowcount++;
                                }
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, 'TOTAL');
                                $cellCoordinate = PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount;
                                $objPHPExcel->getActiveSheet()->getStyle($cellCoordinate)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyle($cellCoordinate)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                $objPHPExcel->getActiveSheet()->getStyle($cellCoordinate)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                                $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $total_lop);
                                $cellCoordinate = PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount;
                                $objPHPExcel->getActiveSheet()->getStyle($cellCoordinate)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyle($cellCoordinate)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                                $rowcount++;
                                $columnindex = $columnindex + 10;
                                $BStyle = array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN
                                        )
                                    )
                                );
                                $objPHPExcel->getActiveSheet()->getStyle('C4:C3000')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $row = $rowcount - 1;
                                $column = $columnindex - 1;
                                $bordercolumnrange = (PHPExcel_Cell::stringFromColumnIndex($column));
                                $borderrange = $bordercolumnrange . $row;
                                $objPHPExcel->getActiveSheet()->getStyle('A1:' . $borderrange)->applyFromArray($BStyle);
                            }

                            $rowcount1 = $rowcount + 1;
                        }
                    } else {
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under this selected criteria. ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                        $worksheet->mergeCells('A3:N3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, )
                        );
                    }


                    $objPHPExcel->getActiveSheet()->setTitle('LOP');
                    /* border */

                    /* border */
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
                    $this->set('month', $month);
                    $this->render('lopreport');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }
    // End

    // edited by anu krishnan 22-01-2025 start
    public function generatenonpunchedreport($type, $mode)
    {
        $arr_form_data = $_REQUEST;
        // var_dump($arr_form_data);
        $user_id = $this->Session->read('login_user_id');
        $date_time = date('d-m-y H:i:s');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $employeeIds = isset($arr_form_data['EmployeeDetails']) ? $arr_form_data['EmployeeDetails'] : [];
        $branchs = isset($arr_form_data['Units']) ? $arr_form_data['Units'] : [];
        $reportYear = $reportMonth = null;

        $reporttfrom = isset($arr_form_data['reportfrom']) && !empty($arr_form_data['reportfrom']) ?
            date('Y-m-d', strtotime($arr_form_data['reportfrom'])) : null;

        $reportto = isset($arr_form_data['reportto']) && !empty($arr_form_data['reportto']) ?
            date('Y-m-d', strtotime($arr_form_data['reportto'])) : null;


        // Edited by Akshay on 22-1-2025
        $resigned = isset($arr_form_data['resigned']) ? $arr_form_data['resigned'] : 0;
        $condition = "";
        if ($resigned != 1) {
            $condition = " AND ei.emp_status = 1";
        } else {
            $condition = " AND ei.emp_status IN(1, 2)";
        }
        // End

        $baseQuery = "SELECT DISTINCT ei.emp_pkey,ei.EmpName, ei.joining_date, tm.last_working_date, 
                        ei.branch, ei.department, ei.designation,
                        uc.user_id, ei.employee_id, ei.emp_status
                        FROM employee_info AS ei
                        LEFT JOIN termination AS tm ON (ei.emp_pkey = tm.emp_fkey AND tm.status = 1 )
                        LEFT JOIN user_credentials AS uc ON ei.emp_pkey = uc.emp_fkey
                        WHERE 
                        ei.emp_id NOT IN (
                                            SELECT DISTINCT emp_id
                                            FROM device_attandance
                                            WHERE DATE_FORMAT(LOGDATE, '%Y-%m-%d') BETWEEN '$reporttfrom' AND '$reportto'
                                            AND C1 IN ('in', 'out')
                                            AND status = 'Y'
                        ) 
                        $condition
                        ";
        if ($arr_form_data['select-criteria1'] == 'EmployeeDetails' && !empty($employeeIds)) {
            $employeeIdsPlaceholder = implode(",", array_map('intval', $employeeIds));
            $baseQuery .= " AND ei.emp_pkey IN ($employeeIdsPlaceholder)";
        } elseif (!empty($branchs)) {
            $branchsPlaceholder = "'" . implode("','", array_map('addslashes', $branchs)) . "'";
            $baseQuery .= " AND ei.branch_code IN ($branchsPlaceholder)";
        }

        // Edited by Akshay on 22-1-2025
        $condition = "";
        if ($resigned != 1) {
            $condition = " AND ed.status = 1";
        } else {
            $condition = " AND ed.status IN(1, 2)";
        }
        if ($arr_form_data['select-criteria1'] == 'EmployeeDetails' && !empty($employeeIds)) {

            if ($reporttfrom && $reportto) {
                // Convert the dates to DateTime objects
                $startDate = new DateTime($reporttfrom);
                $endDate = new DateTime($reportto);

                // Initialize an array to store the months
                $months = [];

                // Iterate through the months
                while ($startDate <= $endDate) {
                    $months[] = $startDate->format('Y-m');
                    $startDate->modify('+1 month');
                }

                // foreach ($months as $mnth) {
                //     foreach ($employeeIds as $empPkey) {
                //         $this->request->data = [
                //             'month' => $mnth, // Example month
                //             'emp' => $empPkey,    // Example employee primary key
                //         ];
                //         $result = $this->Updateame();
                //         $result = $this->Listpunches($empPkey, $mnth);
                //     }
                // }
            }
            // }
        } else {
            if ($reporttfrom && $reportto) {
                // Convert the dates to DateTime objects
                $startDate = new DateTime($reporttfrom);
                $endDate = new DateTime($reportto);

                // Initialize an array to store the months
                $months = [];

                // Iterate through the months
                while ($startDate <= $endDate) {
                    $months[] = $startDate->format('Y-m');
                    $startDate->modify('+1 month');
                }
                // foreach ($branchs as $brnch) {
                //     $arr_emmPkey = $this->DeviceAttendance->query("SELECT emp_pkey FROM emp_details ed WHERE ed.branch_code = '$brnch' $condition");
                //     foreach ($months as $mnth) {
                //         foreach ($arr_emmPkey as $empPkey) {
                //             $empPkey = isset($empPkey['ed']['emp_pkey']) ? $empPkey['ed']['emp_pkey'] : '';
                //             if ($empPkey != '') {
                //                 $this->request->data = [
                //                     'month' => $mnth, // Example month
                //                     'emp' => $empPkey,    // Example employee primary key
                //                 ];
                //                 // $result = $this->Updateame();
                //                 // $result = $this->Listpunches($empPkey, $mnth);
                //             }
                //         }
                //     }
                // }
            }
            // }
        }

        // End


        // if ($reportYear && $reportMonth) {
        //     $baseQuery .= " AND YEAR(edta.yearmonth) = '$reportYear' AND MONTH(edta.yearmonth) = '$reportMonth'";
        // } elseif ($reporttfrom && $reportto) {
        //     $baseQuery .= " AND edta.att_date BETWEEN '$reporttfrom' AND '$reportto'";
        // }
        try {
        } catch (Exception $e) {
            debug($e);
        }
        $baseQuery .= " GROUP BY ei.emp_pkey ORDER BY ei.branch, ei.EmpName";
        try {
            // debug($baseQuery);exit;
            $result = $this->DeviceAttendance->query($baseQuery);
        } catch (Exception $e) {
            debug($e);
        }

        $processedData = [];
        foreach ($result as $row) {
            $processedData[] = [
                'sl_no' => count($processedData) + 1,
                'employee_id' => isset($row['ei']['employee_id']) ? $row['ei']['employee_id'] : (isset($row[0]['employee_id']) ? $row[0]['employee_id'] : ''),
                'user_id' => isset($row['uc']['user_id']) ? $row['uc']['user_id'] : '',
                'first_name' => isset($row['ei']['EmpName'])
                    ? $row['ei']['EmpName'] . (isset($row['ei']['emp_status']) && $row['ei']['emp_status'] == 1 ? '' : ' (Resigned)')
                    : (isset($row[0]['EmpName']) ? $row[0]['EmpName'] : ''),
                'joining_date' => isset($row['ei']['joining_date'])
                    ? DateTime::createFromFormat('Y-m-d', $row['ei']['joining_date'])->format('d-m-Y')
                    : (isset($row[0]['joining_date']) ? DateTime::createFromFormat('Y-m-d', $row[0]['joining_date'])->format('d-m-Y') : ''),
                'branch' => isset($row['ei']['branch']) ? $row['ei']['branch'] : (isset($row[0]['branch']) ? $row[0]['branch'] : ''),
                'department' => isset($row['ei']['department']) ? $row['ei']['department'] : (isset($row[0]['department']) ? $row[0]['department'] : ''),
                'designation' => isset($row['ei']['designation']) ? $row['ei']['designation'] : (isset($row[0]['designation']) ? $row[0]['designation'] : ''),
                'last_working_date' => isset($row['tm']['last_working_date']) ? DateTime::createFromFormat('Y-m-d', $row['tm']['last_working_date'])->format('d-m-Y') : '',
                'att_date' => isset($row['edta']['att_date']) ? DateTime::createFromFormat('Y-m-d', $row['edta']['att_date'])->format('d-m-Y') : ''
            ];
        }

        $reporttfrom = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
        $reportto = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] : '';
        $heading = "Non - Punched & Non - Attendance from " . date('d-m-Y', strtotime($reporttfrom)) . " to " . date('d-m-Y', strtotime($reportto));

        $this->set('selectCriteria1', $arr_form_data['select-criteria1']);
        $this->set('processedData', $processedData);
        $this->set('heading', $heading);
        $this->set('user_id', $user_id);
        $this->set('datetime', $date_time);
        switch ($mode) {
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                // $file_name = isset($str_company_code) ? $str_company_code . "_Nonpunched.xlsx" : "_Nonpunched_" . strtotime() . ".xlsx";
                if (isset($arr_form_data['reportfrom']) && isset($arr_form_data['reportto'])) {
                    $reportFrom = $arr_form_data['reportfrom'];
                    $reportTo = $arr_form_data['reportto'];
                    $file_name = isset($str_company_code) ? "{$str_company_code}_Non - Punched & Non - Attendance - {$reportFrom} - {$reportTo}.xlsx" : "Nonpunched & Non - Attendance - {$reportFrom} - {$reportTo}.xlsx";
                } else {
                    $file_name = isset($str_company_code) ? "{$str_company_code}_Non - Punched_" . strtotime() . ".xlsx" : "Nonpunched_" . strtotime() . ".xlsx";
                }
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Attendance Register By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $table_count = 0;
                $worksheet->setCellValueByColumnAndRow(0, 1, $heading);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );

                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $worksheet->getStyle('A2')->getFont()->setBold(true);
                $worksheet->getStyle('A2')->getFont()->setSize(13);
                $worksheet->mergeCells('A2:J2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );

                if ($processedData) {
                    $headerStyleArray = [
                        'fill' => [
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => 'D9E1F2']
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '000000'],
                            'size' => 12
                        ],
                        'alignment' => [
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        ],
                        'borders' => [
                            'allborders' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ];

                    $worksheet->getStyle('A3:I3')->applyFromArray($headerStyleArray);

                    $worksheet->setCellValueByColumnAndRow(0, 3, "Sl No");
                    $worksheet->setCellValueByColumnAndRow(1, 3, "Employee ID");
                    $worksheet->setCellValueByColumnAndRow(2, 3, "User ID");
                    $worksheet->setCellValueByColumnAndRow(3, 3, "Employee Name");
                    $worksheet->setCellValueByColumnAndRow(4, 3, "Joining Date");
                    $worksheet->setCellValueByColumnAndRow(5, 3, "Branch");
                    $worksheet->setCellValueByColumnAndRow(6, 3, "Department");
                    $worksheet->setCellValueByColumnAndRow(7, 3, "Designation");
                    $worksheet->setCellValueByColumnAndRow(8, 3, "Termination Date");
                    // $worksheet->setCellValueByColumnAndRow(9, 3, "Non-Punched Date");

                    $rowIndex = 4;
                    foreach ($processedData as $data) {
                        foreach (range(0, 9) as $colIndex) {
                            $worksheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
                        }
                        $worksheet->setCellValueByColumnAndRow(0, $rowIndex, $data['sl_no']);
                        $worksheet->getStyleByColumnAndRow(0, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(1, $rowIndex, $data['employee_id']);
                        $worksheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(2, $rowIndex, $data['user_id']);
                        $worksheet->getStyleByColumnAndRow(2, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(3, $rowIndex, $data['first_name']);
                        $worksheet->getStyleByColumnAndRow(3, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(4, $rowIndex, $data['joining_date']);
                        $worksheet->getStyleByColumnAndRow(4, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(5, $rowIndex, $data['branch']);
                        $worksheet->getStyleByColumnAndRow(5, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(6, $rowIndex, $data['department']);
                        $worksheet->getStyleByColumnAndRow(6, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(7, $rowIndex, $data['designation']);
                        $worksheet->getStyleByColumnAndRow(7, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(8, $rowIndex, $data['last_working_date']);
                        $worksheet->getStyleByColumnAndRow(8, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        // $worksheet->setCellValueByColumnAndRow(9, $rowIndex, $data['att_date']);
                        // $worksheet->getStyleByColumnAndRow(9, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $rowIndex++;
                        $table_count++;
                    }
                }

                // Edited by Akshay on 22-1-2025
                $worksheet->setShowGridlines(false);
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                // End

                if ($table_count == 0) {
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria.");
                    $worksheet->mergeCells('A3:J3');
                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                    );
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'J3')->applyFromArray($styleArray); // Edited by Akshay on 22-1-2025
                } else {
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'I' . ($rowIndex - 1))->applyFromArray($styleArray); // Edited by Akshay on 22-1-2025
                }

                // }

                $objPHPExcel->getActiveSheet()->setTitle('Non-Punched & Non - Attendance');
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $file_name . '"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save('php://output');
                exit;
            default:
                $this->set('mode', '');
                $this->render('nonpunch');
                break;
        }
    }

    public function generatenonattendancereport($type, $mode)
    {
        $arr_form_data = $_REQUEST;
        $user_id = $this->Session->read('login_user_id');
        $date_time = date('d-m-y H:i:s');

        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $employeeIds = isset($arr_form_data['EmployeeDetails']) ? $arr_form_data['EmployeeDetails'] : [];

        $branchs = isset($arr_form_data['Units']) ? $arr_form_data['Units'] : [];

        $reportYear = $reportMonth = null;

        $reporttfrom = isset($arr_form_data['reportfrom']) && !empty($arr_form_data['reportfrom']) ?
            date('Y-m-d', strtotime($arr_form_data['reportfrom'])) : null;

        $reportto = isset($arr_form_data['reportto']) && !empty($arr_form_data['reportto']) ?
            date('Y-m-d', strtotime($arr_form_data['reportto'])) : null;

        // Edited by Akshay on 29-1-2025
        $startDate = new DateTime($reporttfrom);
        $endDate = new DateTime($reportto);

        // Initialize an array to store the months
        $months = [];

        // Iterate through the months
        while ($startDate <= $endDate) {
            $months[] = $startDate->format('Y-m');
            $startDate->modify('+1 month');
        }

        $modifiedMonths = array_map(function ($month) {
            return "'" . $month . "-01'"; // Append '-01' and wrap in single quotes
        }, $months);
        $commaSeparatedMonths = implode(',', $modifiedMonths);
        // End
        // }

        // Edited by Akshay on 22-1-2025
        $resigned = isset($arr_form_data['resigned']) ? $arr_form_data['resigned'] : 0;
        $condition = "";
        if ($resigned != 1) {
            $condition = " AND ei.emp_status = 1";
        } else {
            " AND ei.emp_status IN(1, 2)";
        }


        // End

        $baseQuery = "SELECT DISTINCT ei.emp_pkey,ei.EmpName, ei.joining_date, tm.last_working_date, 
                        ei.branch, ei.department, ei.designation, edta.att_date,
                        uc.user_id, ei.employee_id, ei.emp_status
                        FROM emp_site_detail_timeattandance AS edta
                        LEFT JOIN employee_info AS ei ON edta.emp_pkey = ei.emp_pkey
                        LEFT JOIN termination AS tm ON ei.emp_pkey = tm.emp_fkey
                        LEFT JOIN user_credentials AS uc ON ei.emp_pkey = uc.emp_fkey
                        WHERE edta.att_in_time IS NULL
                        AND edta.att_out_time IS NULL
                        AND edta.holiday IS NULL
                        AND edta.weekoff IS NULL
                        -- AND (edta.others IS NULL OR edta.others = 'NA')
                        AND ei.emp_pkey IN (
                                SELECT DISTINCT emp_fkey 
                                FROM site_attendance_register sar 
                                WHERE sar.month_year = DATE_FORMAT(edta.yearmonth, '%Y-%m')
                            )
                        -- Edited by Akshay on 7-3-2025
                        AND ei.emp_pkey NOT IN (
                                SELECT DISTINCT emp_pkey 
                                FROM emp_site_detail_timeattandance
                                WHERE att_date BETWEEN '$reporttfrom' AND '$reportto' 
                                AND (att_in_time IS NOT NULL OR att_out_time IS NOT NULL)
                        ) 
                        -- End
                        $condition
                        ";

        if ($arr_form_data['select-criteria1'] == 'EmployeeDetails' && !empty($employeeIds)) {
            $employeeIdsPlaceholder = implode(",", array_map('intval', $employeeIds));
            $baseQuery .= " AND ei.emp_pkey IN ($employeeIdsPlaceholder)";

            $arr_sites_pkey = $this->DeviceAttendance->query("SELECT DISTINCT site_pkey FROM site WHERE status = 1;");
            foreach ($months as $monthYear) {
                foreach ($arr_sites_pkey as $site) {
                    $site_pkey = isset($site['site']['site_pkey']) ? $site['site']['site_pkey'] : '';
                    if ($site_pkey != '' && preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
                        $monthYearWithDay = $monthYear . '-01';
                        $result = $this->DeviceAttendance->query("SELECT `site_time_duration_check`('$monthYearWithDay', '', '$site_pkey')");
                    }
                }
            }
            // End

        } elseif (!empty($branchs)) {
            $branchsPlaceholder = "'" . implode("','", array_map('addslashes', $branchs)) . "'";
            $baseQuery .= " AND ei.branch_code IN ($branchsPlaceholder)";

            // Edited by Akshay on 25-2-2025
            $arr_sites_pkey = $this->DeviceAttendance->query("SELECT DISTINCT site_pkey FROM site WHERE status = 1;");
            // End

            // Edited by Akshay on 29-1-2025
            foreach ($months as $monthYear) {
                foreach ($arr_sites_pkey as $site) {
                    $site_pkey = isset($site['site']['site_pkey']) ? $site['site']['site_pkey'] : '';
                    if ($site_pkey != '' && preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
                        $monthYearWithDay = $monthYear . '-01';
                        $result = $this->DeviceAttendance->query("SELECT `site_time_duration_check`('$monthYearWithDay', '', '$site_pkey')");
                    }
                }
                foreach ($branchs as $branchcode) {
                    if ($branchcode != '') {
                        $result = $this->processregisterentries($branchcode, $monthYear);
                    }
                }
            }
            // End
        }


        if ($reportYear && $reportMonth) {
            $baseQuery .= " AND YEAR(edta.yearmonth) = '$reportYear' AND MONTH(edta.yearmonth) = '$reportMonth'";
        } elseif ($reporttfrom && $reportto) {
            $baseQuery .= " AND edta.att_date BETWEEN '$reporttfrom' AND '$reportto'";
        }

        // Edited by Akshay on 23-2-2025
        $baseQuery .= " AND NOT EXISTS (
                        SELECT 1 
                        FROM emp_site_detail_timeattandance sub_edta
                        WHERE sub_edta.emp_pkey = edta.emp_pkey
                        AND sub_edta.att_date = edta.att_date
                        AND (sub_edta.att_in_time IS NOT NULL
                        OR sub_edta.att_out_time IS NOT NULL)
                    ) ";
        // End

        if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
            $baseQuery .= " ORDER BY ei.EmpName, edta.att_date ASC";
        } else {
            $baseQuery .= " ORDER BY ei.branch, ei.EmpName, edta.att_date ASC";
        }

        // DEBUG($baseQuery);
        try {
            $result = $this->DeviceAttendance->query($baseQuery);
        } catch (Exception $e) {
            debug($e);
        }

        $processedData = [];
        foreach ($result as $row) {
            $processedData[] = [
                'sl_no' => count($processedData) + 1,
                'employee_id' => isset($row['ei']['employee_id']) ? $row['ei']['employee_id'] : '',
                'user_id' => isset($row['uc']['user_id']) ? $row['uc']['user_id'] : '',
                'first_name' => isset($row['ei']['EmpName'])
                    ? $row['ei']['EmpName'] . (isset($row['ei']['emp_status']) && $row['ei']['emp_status'] == 1 ? '' : ' (Resigned)')
                    : '',
                'joining_date' => isset($row['ei']['joining_date']) ? DateTime::createFromFormat('Y-m-d', $row['ei']['joining_date'])->format('d-m-Y') : '',
                'branch' => isset($row['ei']['branch']) ? $row['ei']['branch'] : '',
                'department' => isset($row['ei']['department']) ? $row['ei']['department'] : '',
                'designation' => isset($row['ei']['designation']) ? $row['ei']['designation'] : '',
                'last_working_date' => isset($row['tm']['last_working_date']) ? DateTime::createFromFormat('Y-m-d', $row['tm']['last_working_date'])->format('d-m-Y') : '',
                'att_date' => isset($row['edta']['att_date']) ? DateTime::createFromFormat('Y-m-d', $row['edta']['att_date'])->format('d-m-Y') : ''
            ];
        }

        if (isset($arr_form_data['select-type']) && $arr_form_data['select-type'] == 'month') {
            $reportFrom = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
            $reportMonth = date('F Y', strtotime($reportFrom));
            $heading = "Non - Attendance - " . $reportMonth;
        } else {
            $reporttfrom = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
            $reportto = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] : '';
            $heading = "Non - Attendance from " . date('d-m-Y', strtotime($reporttfrom)) . " to " . date('d-m-Y', strtotime($reportto));
        }
        $this->set('selectCriteria1', $arr_form_data['select-criteria1']);
        $this->set('processedData', $processedData);
        $this->set('heading', $heading);
        $this->set('user_id', $user_id);
        $this->set('datetime', $date_time);

        switch ($mode) {
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                // $file_name = isset($str_company_code) ? $str_company_code . "_Nonattendance.xlsx" : "_Nonattendance_" . strtotime() . ".xlsx";
                if (isset($arr_form_data['reportfrom']) && isset($arr_form_data['reportto'])) {
                    $reportFrom = $arr_form_data['reportfrom'];
                    $reportTo = $arr_form_data['reportto'];
                    $file_name = isset($str_company_code) ? "{$str_company_code}_Non - Attendance - {$reportFrom} - {$reportTo}.xlsx" : "Nonattendance - {$reportFrom} - {$reportTo}.xlsx";
                } else {
                    $file_name = isset($str_company_code) ? "{$str_company_code}_Non - Attendance" . strtotime() . ".xlsx" : "Nonattendance" . strtotime() . ".xlsx";
                }
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Attendance Register By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $table_count = 0;
                $worksheet->setCellValueByColumnAndRow(0, 1, $heading);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:K1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );

                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $worksheet->getStyle('A2')->getFont()->setBold(true);
                $worksheet->getStyle('A2')->getFont()->setSize(13);
                $worksheet->mergeCells('A2:K2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );
                if ($processedData) {
                    $headerStyleArray = [
                        'fill' => [
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => 'D9E1F2']
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '000000'],
                            'size' => 12
                        ],
                        'alignment' => [
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        ],
                        'borders' => [
                            'allborders' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ];

                    $worksheet->getStyle('A3:J3')->applyFromArray($headerStyleArray);

                    $worksheet->setCellValueByColumnAndRow(0, 3, "Sl No");
                    $worksheet->setCellValueByColumnAndRow(1, 3, "Employee ID");
                    $worksheet->setCellValueByColumnAndRow(2, 3, "User ID");
                    $worksheet->setCellValueByColumnAndRow(3, 3, "Employee Name");
                    $worksheet->setCellValueByColumnAndRow(4, 3, "Joining Date");
                    $worksheet->setCellValueByColumnAndRow(5, 3, "Branch");
                    $worksheet->setCellValueByColumnAndRow(6, 3, "Department");
                    $worksheet->setCellValueByColumnAndRow(7, 3, "Designation");
                    $worksheet->setCellValueByColumnAndRow(8, 3, "Termination Date");
                    $worksheet->setCellValueByColumnAndRow(9, 3, "Non-Attendance Date");

                    $rowIndex = 4;
                    foreach ($processedData as $data) {
                        foreach (range(0, 9) as $colIndex) {
                            $worksheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
                        }
                        $worksheet->setCellValueByColumnAndRow(0, $rowIndex, $data['sl_no']);
                        $worksheet->getStyleByColumnAndRow(0, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(1, $rowIndex, $data['employee_id']);
                        $worksheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(2, $rowIndex, $data['user_id']);
                        $worksheet->getStyleByColumnAndRow(2, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(3, $rowIndex, $data['first_name']);
                        $worksheet->getStyleByColumnAndRow(3, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(4, $rowIndex, $data['joining_date']);
                        $worksheet->getStyleByColumnAndRow(4, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(5, $rowIndex, $data['branch']);
                        $worksheet->getStyleByColumnAndRow(5, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(6, $rowIndex, $data['department']);
                        $worksheet->getStyleByColumnAndRow(6, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(7, $rowIndex, $data['designation']);
                        $worksheet->getStyleByColumnAndRow(7, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(8, $rowIndex, $data['last_working_date']);
                        $worksheet->getStyleByColumnAndRow(8, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(9, $rowIndex, $data['att_date']);
                        $worksheet->getStyleByColumnAndRow(9, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $rowIndex++;
                        $table_count++;
                    }
                }

                // Edited by Akshay on 22-1-2025
                $worksheet->setShowGridlines(false);
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                // End

                if ($table_count == 0) {
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria.");
                    $worksheet->mergeCells('A3:J3');
                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                    );
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'J3')->applyFromArray($styleArray); // Edited by Akshay on 22-1-2025
                } else {
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'J' . ($rowIndex - 1))->applyFromArray($styleArray); // Edited by Akshay on 22-1-2025
                }

                $objPHPExcel->getActiveSheet()->setTitle('Non-Attendance');
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $file_name . '"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save('php://output');
                exit;
            default:
                $this->set('mode', '');
                $this->render('nonattendance');
                break;
        }
    }
    // edited by anu krishnan 22-01-2025 end

    // Edited by Akshay on 22-1-2025
    public function Updateame()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        // debug($arr_data);

        $month = $arr_data['month'] . '-01';
        $get_emp = array();
        $emp_pkey = $arr_data['emp'];
        $branch_code = isset($get_emp['0']['emp_details']['branch_code']) ? $get_emp['0']['emp_details']['branch_code'] : 'NULL';
        $deleterecords = $this->EmployeeDetails->query("delete from emp_detail_timeattandance where emp_pkey='$emp_pkey' and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m') ) ");
        if (!$shiftdetailed = $this->EmployeeDetails->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )")) {
            return FALSE;
            die();
        }
        if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
            try {
                if (!$this->EmployeeDetails->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")) {
                    return false;
                    die();
                }
            } catch (Exception $e) {
                // debug($e);
            }
        } else {
            try {
                if (!$this->EmployeeDetails->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")) {
                    return false;
                    die();
                }
            } catch (Exception $e) {
                // debug($e);
            }
        }
    }

    public function listpunches($emp_pkey = 0, $month = '')
    {
        $this->autoRender = FALSE;

        $base_table = "emp_detail_timeattandance";

        if ($emp_pkey == 0) {
            return;
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_pkey` = '$emp_pkey'");
        $branch_code = isset($arr_emp_pkey['0']['emp_details']['branch_code']) ? $arr_emp_pkey['0']['emp_details']['branch_code'] : '';

        $limit = 32;
        $page = 1;

        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();
        $resp_mispunches["data"] = array();
        $resp_mispunch["out"] = array();

        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));

        $yearmonth = $month . '-01';

        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
        $arr_date_in_selectedmonth = range(1, $att_enddate);

        if ($att_startdate != 1) {
            $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }

        if ($emp_pkey != 0) {
            $condition = "$base_table.emp_pkey = '$emp_pkey' and ";
            $emp = $emp_pkey;
        } else {
            $condition = '';
            $emp = NULL;
        }

        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $useracess = $this->EmployeeDetails->query("select * from user_access as Useraccess where user_fkey = '$emp_pkeys' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            if ($access == 'Y') {
                $emp_condition = ""; // "emp.attr1 = '$emp_pkeys' and ";
            } else {
                $emp_condition = "";
            }
        } else {
            $emp_condition = "";
        }


        if (!$shiftdetailed = $this->EmployeeDetails->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey')")) {
            $resp_mispunches["total"] = "0";
            $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
            $resp_mispunches["type"] = "danger";
            return FALSE;
            // die();
        }

        //debug($shiftdetailed);
        if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
            try {
                if (!$this->EmployeeDetails->query("SELECT time_duration_check_multishift('$yearmonth', '$emp_pkey', '$branch_code')")) {
                    $resp_mispunches["total"] = "0";
                    $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
                    $resp_mispunches["type"] = "danger";
                    return false;
                    // die();
                }
            } catch (Exception $e) {
                // debug($e);
            }
        } else {
            try {
                if (!$this->EmployeeDetails->query("SELECT time_duration_check('$yearmonth', '$emp_pkey', '$branch_code')")) {
                    $resp_mispunches["total"] = "0";
                    $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
                    $resp_mispunches["type"] = "danger";
                    return false;
                    // die();
                }
            } catch (Exception $e) {
                // debug($e);
            }
        }
        $attendances_count = $this->EmployeeDetails->query("select count(*) AS count from $base_table left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where $emp_condition $condition yearmonth = '$yearmonth' order by att_date ");
        $count = isset($attendances_count[0][0]['count']) ? $attendances_count[0][0]['count'] : 0;
    }

    public function processregisterentries($branchcode = '', $month = '')
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        //        $outputParameter = array();
        $companycode = $this->Session->read('company_code'); //company_code
        // $branchcode = (isset($_POST['branch']) && $_POST['branch'] != '') ? $_POST['branch'] : '';
        $userid = $this->Session->read("login_user_id"); //user id
        $monthselected = (isset($month) && $month != '') ? date('Y-m-d', strtotime($month)) : '';
        // debug("CALL site_insert_update_att_reg('$companycode', '$branchcode', '$userid', '$monthselected', @`Perr_msg`) ");
        $out = $this->EmployeeDetails->query("CALL site_insert_update_att_reg('$companycode', '$branchcode', '$userid', '$monthselected', @`Perr_msg`) ");
        //        insertUpdateAttendanceRegisterProc($outputParameter);
        // $arr_sitemonth = $this->EmployeeDetails->query("SELECT `site_time_duration_check`('$monthselected', '', '$branchcode')");
        $result['success'] = 1;
        sleep(2); // Wait for 2 seconds before retrying
        return $result;
    }

    public function getEmployeesByBranch()
    {
        $this->autoRender = false;
        $branch = isset($_REQUEST['branch']) ? $_REQUEST['branch'] : '0';
        $emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $context = $this->MasterdataManagement->getFeatureAccessContext();

        if ($user_group == 1) {
            $employees = $this->MasterdataManagement->getAllEmployeesByBranch($branch, 0);
        } else if ($context['has_access']) {
            if ($context['is_hierarchy']) {
                $employees = $this->MasterdataManagement->getHierarchyEmployeesByBranch($emp_pkey, $branch);
            } else {
                $employees = $this->MasterdataManagement->getAllEmployeesByBranch($branch, 0);
            }
        } else {
            $employees = $this->MasterdataManagement->getAllEmployeesByBranch($branch, 0);
        }

        $result = array();
        if (!empty($employees)) {
            foreach ($employees as $emp) {
                $obj = new stdClass();
                $obj->id = $emp[0]['id'];
                $obj->text = $emp[0]['text'];
                $result[] = $obj;
            }
        }
        echo json_encode($result);
    }
}