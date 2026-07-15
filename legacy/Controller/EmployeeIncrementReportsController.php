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
ini_set('max_execution_time', 300);
App::uses('ConnectionManager', 'Model', 'EmployeeCTC');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class EmployeeIncrementReportsController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'EmployeeIncrementReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('LeavePolicyGroup', 'HolidayGroup', 'CentralControl', 'Designation', 'CompanyContactInfo', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments', 'Verticals', 'Units', 'ReportCriterias', 'DayTimeProcedures', 'ReportAudit');
    public $components = array('MasterdataManagement');


    public function hrreports()
    {
        $arr_reporttypes = array(
            'increment_history' => 'Increment History'
        );
        //        

        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    public function attendance()
    {
        $arr_reporttypes = array(
            'employee' => 'Employee Information',
            'shiftpolicy' => 'Shift Policy Reports',
            'leavepolicy' => 'Leave Policy Reports',
            'holiday' => 'Holiday Group Reports',
            /* 'leave' => 'Leaves Report',
              'attendance' => 'Attendance Summary', */
            'tax' => 'Tax Declarations'
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    public function miscellanious()
    {
        $arr_reporttypes = array(
            'employee' => 'Employee Information',
            'shiftpolicy' => 'Shift Policy Reports',
            'leavepolicy' => 'Leave Policy Reports',
            'holiday' => 'Holiday Group Reports',
            /* 'leave' => 'Leaves Report',
              'attendance' => 'Attendance Summary', */
            'tax' => 'Tax Declarations'
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    public function sallary()
    {
        $arr_reporttypes = array(
            'employee' => 'Employee Information',
            'shiftpolicy' => 'Shift Policy Reports',
            'leavepolicy' => 'Leave Policy Reports',
            'holiday' => 'Holiday Group Reports',
            /* 'leave' => 'Leaves Report',
              'attendance' => 'Attendance Summary', */
            'tax' => 'Tax Declarations'
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    public function statutory()
    {
        $arr_reporttypes = array(
            'employee' => 'Employee Information',
            'shiftpolicy' => 'Shift Policy Reports',
            'leavepolicy' => 'Leave Policy Reports',
            'holiday' => 'Holiday Group Reports',
            /* 'leave' => 'Leaves Report',
              'attendance' => 'Attendance Summary', */
            'tax' => 'Tax Declarations'
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */

    public function changereporttype($type = '')
    {
        $this->autoRender = FALSE;
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                case 'increment_history':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_increment', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type), 'order' => 'reportcriteria_desc'))));
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
            $this->set('arr_remainingcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reportcriteria NOT IN(' . $str_currentcriterias . ')', 'reporttype' => $type), 'order' => 'reportcriteria_desc')))); //Edited by Akshay on 16-11-2023

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

        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');
            $join = array();
            if ($model == 'DayTimeProcedures') {
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $conditions[] = array("emp_branch" => $cur_emp_branch);
                    $join = array(
                        array(
                            'table' => 'emp_proff',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('emp_proff.day_time_seq=DayTimeProcedures.day_time_seq')
                        ),
                        array(
                            'table' => 'emp_details',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('emp_proff.emp_fkey=emp_details.emp_pkey')
                        )
                    );
                    $conditions[] = array("active" => 1, "emp_details.status" => 1);
                } else {
                    $conditions[] = array("active" => 1);
                }
            } elseif ($model == 'Units') {
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $arr_order = array("Units.branch_name" => "ASC");
                    $conditions = array("Units.status" => 1, "branch_code" => $cur_emp_branch);
                } else {
                    $arr_order = array("Units.branch_name" => "ASC");
                    $conditions = array("Units.status" => 1);
                }
            } elseif ($model == 'Departments') {
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $conditions[] = array("emp_branch" => $cur_emp_branch, "Departments.status" => 1, "emp_details.status" => 1);
                    $join = array(
                        array(
                            'table' => 'emp_proff',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('Departments.dept_code=emp_proff.emp_dept')
                        ),
                        array(
                            'table' => 'emp_details',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('emp_proff.emp_fkey=emp_details.emp_pkey')
                        )
                    );
                } else {
                    $conditions = array("status" => 1);
                }
            } elseif ($model == 'LeavePolicyGroup') {
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $conditions[] = array("emp_branch" => $cur_emp_branch, "status" => 1);
                    $join = array(
                        array(
                            'table' => 'emp_proff',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('emp_proff.LEAVEPOLICY_GROUP_ID=LeavePolicyGroup.LEAVEPOLICY_GROUP_ID')
                        )
                    );
                } else {
                    $conditions = array("status" => 1);
                }
            } elseif ($model == 'HolidayGroup') {
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $conditions[] = array("emp_branch" => $cur_emp_branch, "HolidayGroup.status" => 1, "emp_details.status" => 1);
                    $join = array(
                        array(
                            'table' => 'emp_proff',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('emp_proff.HOLIDAY_GROUP_ID=HolidayGroup.HOLIDAY_GROUP_ID')
                        ),
                        array(
                            'table' => 'emp_details',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('emp_proff.emp_fkey=emp_details.emp_pkey')
                        )
                    );
                } else {
                    $conditions = array("status" => 1);
                }
            } else {
                $conditions = array("status" => 1);
            }


            if ($model == 'DayTimeProcedures') {
                $fields = array("DISTINCT `DayTimeProcedures`.`day_time_seq`, `DayTimeProcedures`.`day_time_desc`, `DayTimeProcedures`.`Sunday`, `DayTimeProcedures`.`Sunday_F`, `DayTimeProcedures`.`Monday`, `DayTimeProcedures`.`Monday_F`, `DayTimeProcedures`.`Tuesday`, `DayTimeProcedures`.`Tuesday_F`, `DayTimeProcedures`.`Wednesday`, `DayTimeProcedures`.`Wednesday_F`, `DayTimeProcedures`.`Thursday`, `DayTimeProcedures`.`Thursday_F`, `DayTimeProcedures`.`Friday`, `DayTimeProcedures`.`Friday_F`, `DayTimeProcedures`.`Saturday`, `DayTimeProcedures`.`Saturday_F`, `DayTimeProcedures`.`on_dutty1`, `DayTimeProcedures`.`off_dutty1`, `DayTimeProcedures`.`working_time1`, `DayTimeProcedures`.`on_dutty2`, `DayTimeProcedures`.`off_dutty2`, `DayTimeProcedures`.`working_time2`, `DayTimeProcedures`.`on_dutty3`, `DayTimeProcedures`.`off_dutty3`, `DayTimeProcedures`.`working_time3`, `DayTimeProcedures`.`on_dutty4`, `DayTimeProcedures`.`off_dutty4`, `DayTimeProcedures`.`working_time4`, `DayTimeProcedures`.`minuts_calc_perday`, `DayTimeProcedures`.`minuts_aftr_on_dutty_cal_late`, `DayTimeProcedures`.`minuts_bfr_off_dutty_cal_early`, `DayTimeProcedures`.`min_cal_late_ifnoclockin`, `DayTimeProcedures`.`min_cal_leave_early_ifnoclockout`, `DayTimeProcedures`.`min_aftr_off_dutty_cal_ot`, `DayTimeProcedures`.`min_bfr_on_dutty_cal_ot`, `DayTimeProcedures`.`work_time_day_off_cal_ot`, `DayTimeProcedures`.`active`, `DayTimeProcedures`.`isnextday`, `DayTimeProcedures`.`shift_allowance`, `DayTimeProcedures`.`otcomponents`, `DayTimeProcedures`.`start_date_effective`, `DayTimeProcedures`.`end_date_effective`, `DayTimeProcedures`.`strict_monitorings`, `DayTimeProcedures`.`minutes_per_half`, `DayTimeProcedures`.`is_multiple_days`, `DayTimeProcedures`.`no_of_shift_days`, `DayTimeProcedures`.`is_exception`, `DayTimeProcedures`.`include_break`");
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => $fields, "conditions" => $conditions, "joins" => $join)));
            } elseif ($model == 'Departments') {
                $fields = array("DISTINCT `Departments`.`id`, `Departments`.`dept_code`, `Departments`.`dept_name`, `Departments`.`status`");
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => $fields, "conditions" => $conditions, "joins" => $join)));
            } elseif ($model == 'LeavePolicyGroup') {
                $fields = array("DISTINCT `LeavePolicyGroup`.`COMPANY_CODE`, `LeavePolicyGroup`.`BRANCH_CODE`, `LeavePolicyGroup`.`LEAVEPOLICY_GROUP_ID`, `LeavePolicyGroup`.`LEAVEPOLICY_GROUP_NAME`, `LeavePolicyGroup`.`status` ");
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => $fields, "conditions" => $conditions, "joins" => $join)));
            } elseif ($model == 'HolidayGroup') {
                $fields = array("DISTINCT `HolidayGroup`.`COMPANY_CODE`, `HolidayGroup`.`BRANCH_CODE`, `HolidayGroup`.`HOLIDAY_GROUP_ID`, `HolidayGroup`.`HOLIDAY_GROUP_NAME`, `HolidayGroup`.`status`  ");
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => $fields, "conditions" => $conditions, "joins" => $join)));
            } else {
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions, "joins" => $join)));
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
                case 'Units':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['branch_code'];
                        $arr_criteriaItems[$key]['text'] = $value['branch_name'];
                        $key++;
                    }
                    break;

                case 'HolidayGroup':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['HOLIDAY_GROUP_ID'];
                        $arr_criteriaItems[$key]['text'] = $value['HOLIDAY_GROUP_NAME'];
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
                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
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
                    $conditions[] = array('status' => 1);

                    //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                    $user_group = $this->Session->read('user_group');
                    $user = $this->Session->read('company_code');
                    if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                        $cur_emp_key = $this->Session->read("emp_fkey");
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                        $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                        $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);
                    }
                    //employee branch wise sorting ends here

                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions
                    ));
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                        $key++;
                    }
                    break;
                case 'DayTimeProcedures':
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

    public function reportAudit($type, $mode)
    {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'employee':
                $dataForHistory['report_type'] = "Employee Information Report";
                break;
            case 'shiftpolicy':
                $dataForHistory['report_type'] = "Shift Policy Report";
                break;
            case 'leavepolicy':
                $dataForHistory['report_type'] = "Leave Policy Report";
                break;
            case 'holiday':
                $dataForHistory['report_type'] = "Holiday Policy Report";
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
                case 'EmployeeProfessionalDetails':
                    $criteria_name_array[] = 'belonging to a Joining Date';
                    break;
                case 'DayTimeProcedures':
                    $criteria_name_array[] = 'belonging to a Shift Policy Group';
                    break;
                case 'LeavePolicyGroup':
                    $criteria_name_array[] = 'belonging to a Leave Policy';
                    break;
                case 'HolidayGroup':
                    $criteria_name_array[] = 'belonging to a Holiday Policy Group';
                    break;

                default:
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

    public function generatereport($type = '', $mode = '')
    {
        // debug($mode);
        // debug($type);
        $this->autoRender = false;
        switch ($type) {
            case 'increment_history':
                $this->generateemployeincrement($mode);
                break;
            default:
                return false;
                break;
        }

        $this->reportAudit($type, $mode);
    }

    public function listemployeefields()
    {
        $str_company_code = $this->Session->read('company_code'); //Edited by Akshay on 18-11-2023
        if ($str_company_code == 'KWMT') {
            App::import('Vendor', 'WaterMetroEmployeeInformationFields', array('file' => 'ReportFields' . DS . 'WaterMetroEmployeeInformationFields.php'));
        } else {
            App::import('Vendor', 'EmployeeInformationFields', array('file' => 'ReportFields' . DS . 'EmployeeInformationFields.php'));
        }

        $arr_empinformation_fields = new EmployeeInformationFields();

        $str_company_code = $this->Session->read('company_code'); //Edited by Akshay on 18-11-2023
        if ($str_company_code == 'KWMT') {
            $arr_emp_field_headings = array_merge(

                $arr_empinformation_fields->getFieldHeadings('EmployeeInfo'), //Edited by Akshay on 14-11-2023
                $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'),
                $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'),
                $arr_empinformation_fields->getFieldHeadings('Departments'),
                $arr_empinformation_fields->getFieldHeadings('Units'),
                $arr_empinformation_fields->getFieldHeadings('Designation'),
                $arr_empinformation_fields->getFieldHeadings('Grades'),
                $arr_empinformation_fields->getFieldHeadings('Category'),
                $arr_empinformation_fields->getFieldHeadings('Nationality'),
                $arr_empinformation_fields->getFieldHeadings('Countries'),
                $arr_empinformation_fields->getFieldHeadings('Contract')
            );

            $arr_emp_field_names = array(
                'EmployeeInfo' => $arr_empinformation_fields->getFieldNames('EmployeeInfo'), //Edited by Akshay on 14-11-2023
                'EmployeeDetails' => $arr_empinformation_fields->getFieldNames('EmployeeDetails'),
                'EmployeeProfessionalDetails' => $arr_empinformation_fields->getFieldNames('EmployeeProfessionalDetails'),
                'Departments' => $arr_empinformation_fields->getFieldNames('Departments'),
                'Units' => $arr_empinformation_fields->getFieldNames('Units'),
                // 'Family' => $arr_empinformation_fields->getFieldNames('Family'),
                'Designation' => $arr_empinformation_fields->getFieldNames('Designation'),
                'Grades' => $arr_empinformation_fields->getFieldNames('Grades'),
                'Category' => $arr_empinformation_fields->getFieldNames('Category'),
                'Nationality' => $arr_empinformation_fields->getFieldNames('Nationality'),
                'Countries' => $arr_empinformation_fields->getFieldNames('Countries'),
                'Contract' => $arr_empinformation_fields->getFieldNames('Contract')
            );
        } else {
            $arr_emp_field_headings = array_merge(
                $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'),
                $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'),
                $arr_empinformation_fields->getFieldHeadings('Departments'),
                $arr_empinformation_fields->getFieldHeadings('Grades'),
                $arr_empinformation_fields->getFieldHeadings('Verticals'),
                $arr_empinformation_fields->getFieldHeadings('Units'),
                $arr_empinformation_fields->getFieldHeadings('Designation')
            );
            $arr_emp_field_names = array(
                'EmployeeDetails' => $arr_empinformation_fields->getFieldNames('EmployeeDetails'),
                'EmployeeProfessionalDetails' => $arr_empinformation_fields->getFieldNames('EmployeeProfessionalDetails'),
                'Departments' => $arr_empinformation_fields->getFieldNames('Departments'),
                'Grades' => $arr_empinformation_fields->getFieldNames('Grades'),
                'Verticals' => $arr_empinformation_fields->getFieldNames('Verticals'),
                'Units' => $arr_empinformation_fields->getFieldNames('Units'),
                'Family' => $arr_empinformation_fields->getFieldNames('Family'),
                'Designation' => $arr_empinformation_fields->getFieldNames('Designation')
            );
        }


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

    public function generateemployeincrement($mode)
    {
        $arr_form_data = $_REQUEST;
        $user_id = $this->Session->read('login_user_id');
        $date_time = date('d-m-Y H:i');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

        $reportFrom = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : null;
        $reportFromFormatted = $reportFrom . '-01';
        $reportYear = date('Y', strtotime($reportFrom));
        $reportMonth = date('m', strtotime($reportFrom));
        $mname = date('F', mktime(0, 0, 0, $reportMonth, 10));
        
        $employeeIds = isset($arr_form_data['EmployeeDetails']) ? $arr_form_data['EmployeeDetails'] : [];
        $employeeIdsList = !empty($employeeIds) ? "'" . implode("','", $employeeIds) . "'" : "''";

        $branchs = isset($arr_form_data['Units']) ? $arr_form_data['Units'] : [];
        $branchCondition = "";

        if (!empty($branchs)) {
            $branchsList = "'" . implode("','", $branchs) . "'";
            $branchCondition = "AND e.branch_code IN ($branchsList)";
        }

        $resigned = isset($arr_form_data['resigned']) ? $arr_form_data['resigned'] : 0;
        $condition = "";
        if ($resigned != 1) {
            $condition = " AND e.emp_status = 1";
        } else {
            $condition = " AND e.emp_status IN(1, 2)";
        }
      
        if (!empty($employeeIds)) {
            $incrementquery = $this->EmployeeCTC->query("
                SELECT c.*, e.EmpName, e.employee_id, e.joining_date, e.branch, u.user_id
                FROM emp_ctc_upload c
                JOIN employee_info e ON e.emp_pkey = c.emp_fkey
                JOIN user_credentials u ON u.emp_fkey = e.emp_pkey
                WHERE YEAR(next_increment_date) = '$reportYear'
                AND MONTH(next_increment_date) = '$reportMonth'
                AND c.emp_fkey IN ($employeeIdsList)
                AND c.created_date = (
                    SELECT MAX(created_date)
                    FROM emp_ctc_upload
                    WHERE emp_fkey = c.emp_fkey
                    AND YEAR(next_increment_date) = '$reportYear'
                    AND MONTH(next_increment_date) = '$reportMonth'
                )
                $condition
                ORDER BY e.EmpName ASC
            ");
        } else {
            $incrementquery = $this->EmployeeCTC->query("
                SELECT c.*, e.EmpName, e.employee_id, e.joining_date, e.branch, u.user_id
                FROM emp_ctc_upload c
                JOIN employee_info e ON e.emp_pkey = c.emp_fkey
                JOIN user_credentials u ON u.emp_fkey = e.emp_pkey
                WHERE YEAR(next_increment_date) = '$reportYear'
                AND MONTH(next_increment_date) = '$reportMonth'
                AND c.created_date = (
                    SELECT MAX(created_date)
                    FROM emp_ctc_upload
                    WHERE emp_fkey = c.emp_fkey
                    AND YEAR(next_increment_date) = '$reportYear'
                    AND MONTH(next_increment_date) = '$reportMonth'
                )
                $branchCondition
                $condition
                ORDER BY e.branch ASC, e.EmpName ASC
            ");
        }

        $processedData = [];
        $slNo = 1;
        foreach ($incrementquery as $row) {
            $processedData[] = [
                'SlNo'           => $slNo++,
                'EmployeeID'     => isset($row['e']['employee_id']) ? $row['e']['employee_id'] : '',
                'EmployeeName'   => isset($row['e']['EmpName']) ? trim($row['e']['EmpName']) : '',
                'JoiningDate'    => DateTime::createFromFormat('Y-m-d', $row['e']['joining_date'])->format('d-m-Y'),
                'IncrementDate'  => DateTime::createFromFormat('Y-m-d', $row['c']['next_increment_date'])->format('d-m-Y'),
                'create_date' => date('d-m-Y', strtotime($row['c']['created_date'])),
                'Amount'         => isset($row['c']['emp_anual_ctc']) ? $row['c']['emp_anual_ctc'] : '',
                'Branch'         => isset($row['c']['branch']) ? $row['c']['branch'] : '',
                'Department'     => isset($row['c']['department']) ? $row['c']['department'] : '',
                'Designation'    => isset($row['c']['designation']) ? $row['c']['designation'] : '',
                'branch_head'    => isset($row['e']['branch']) ? $row['e']['branch'] : '',
                'user_id'    => isset($row['u']['user_id']) ? $row['u']['user_id'] : '',
            ];
        }

        $this->set('selectCriteria1', $arr_form_data['select-criteria1']);
        $this->set('processedData', $processedData);
        $this->set('mname',  $mname);
        $this->set('yname',  $reportYear);
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);


        switch ($mode) {
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Employee_Increment_".$reportMonth."-".$reportYear.".xlsx" : "Events" . strtotime() . ".xlsx";
                
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
                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Increment- ".$mname."  "  .$reportYear);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:K1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $worksheet->getStyle('A2')->getFont()->setBold(true);
                $worksheet->getStyle('A2')->getFont()->setSize(13);
                $worksheet->mergeCells('A2:K2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
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

                    $worksheet->getStyle('A3:K3')->applyFromArray($headerStyleArray);

                    $worksheet->setCellValueByColumnAndRow(0, 3, "Sl No");
                    $worksheet->setCellValueByColumnAndRow(1, 3, "Employee ID");
                    $worksheet->setCellValueByColumnAndRow(2, 3, "User ID");
                    $worksheet->setCellValueByColumnAndRow(3, 3, "Employee Name");
                    $worksheet->setCellValueByColumnAndRow(4, 3, "Branch");
                    $worksheet->setCellValueByColumnAndRow(5, 3, "Department");
                    $worksheet->setCellValueByColumnAndRow(6, 3, "Designation");
                    $worksheet->setCellValueByColumnAndRow(7, 3, "Joining Date");
                    $worksheet->setCellValueByColumnAndRow(8, 3, "Amount");
                    $worksheet->setCellValueByColumnAndRow(9, 3, "Increment Date");
                    $worksheet->setCellValueByColumnAndRow(10, 3, "Created Date");
                    
                    

                    $rowIndex = 4;
                    foreach ($processedData as $data) {
                        foreach (range(0, 9) as $colIndex) {
                            $worksheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
                        }
                        $worksheet->setCellValueByColumnAndRow(0, $rowIndex, $data['SlNo']);
                        $worksheet->getStyleByColumnAndRow(0, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(1, $rowIndex, $data['EmployeeID']);
                        $worksheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(2, $rowIndex, $data['user_id']);
                        $worksheet->getStyleByColumnAndRow(2, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(3, $rowIndex, $data['EmployeeName']);
                        $worksheet->getStyleByColumnAndRow(3, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(4, $rowIndex, $data['Branch']);
                        $worksheet->getStyleByColumnAndRow(4, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(5, $rowIndex, $data['Department']);
                        $worksheet->getStyleByColumnAndRow(5, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(6, $rowIndex, $data['Designation']);
                        $worksheet->getStyleByColumnAndRow(6, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(7, $rowIndex, $data['JoiningDate']);
                        $worksheet->getStyleByColumnAndRow(7, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(8, $rowIndex, $data['Amount']);
                        $worksheet->getStyleByColumnAndRow(8, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(9, $rowIndex, $data['IncrementDate']);
                        $worksheet->getStyleByColumnAndRow(9, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(10, $rowIndex, $data['create_date']);
                        $worksheet->getStyleByColumnAndRow(10, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $rowIndex++;
                        $table_count++;
                    }
                    
                }
                $worksheet->getColumnDimension('K')->setWidth(20);
                $worksheet->setShowGridlines(false);
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );

                if ($table_count == 0) {
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria.");
                    $worksheet->mergeCells('A3:J3');
                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                    );
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'K3')->applyFromArray($styleArray); // Edited by Akshay on 22-1-2025
                } else {
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'K' . ($rowIndex - 1))->applyFromArray($styleArray); // Edited by Akshay on 22-1-2025
                }

                $objPHPExcel->getActiveSheet()->setTitle('Employee_Increment');
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $file_name . '"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save('php://output');
                exit;
            default:
                $this->set('mode', '');
                $this->render('employeeincrementview');
                break;
        }
    }

}
