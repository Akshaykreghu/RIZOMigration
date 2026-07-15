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
App::uses('ConnectionManager', 'Model');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class ReportsController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'Reports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Menu', 'LeavePolicyGroup', 'HolidayGroup', 'CentralControl', 'Designation', 'CompanyContactInfo', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments', 'Verticals', 'Units', 'ReportCriterias', 'DayTimeProcedures', 'ReportAudit', 'SalaryStructures'); // Edited by Akshay on 11-3-2026
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

    public function hrreports()
    {
        //edited by athira on 04-02-2024
        $this->Menu->useDbConfig = $this->Session->read('ds');
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        //end
        //edited by athira on 04-02-2025
        $arr_reporttypes = array(
            'employee' => 'Employee Information',
            'shiftpolicy' => 'Shift Policy Reports',
            'leavepolicy' => 'Leave Policy Reports',
            'holiday' => 'Holiday Group Reports',
            /* 'leave' => 'Leaves Report',
                  'attendance' => 'Attendance Summary', */
            //'tax' => 'Tax Declarations'
            'salarystructures' => 'Salary Structure Reports', // Edited by Akshay on 11-3-2026
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
                case 'employee':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type), "order" => "reportcriteria_desc")))); //Edited by Akshay on 15-11-2023
                    break;
                case 'shiftpolicy':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;

                case 'leavepolicy':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'holiday':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                // Edited by Akshay on 11-3-2026
                case 'salarystructures':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                // End
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

    public function listcriteriaitems($str_criteria = '', $type = '')
    {
        $this->autoRender = false;
        $model = $str_criteria;
        $arr_requestdata = $this->request->data;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $conditions = array(); // Edited by Akshay on 28-1-2025
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
                }
                // Edited by Akshay on 27-1-2025
                elseif ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $emp_pkey = $this->Session->read('emp_fkey');
                    $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $conditions = array("Units.status" => 1, "branch_code" => $is_ho);
                    } else {
                        $conditions = array("Units.status" => 1);
                    }
                } else {
                    $arr_order = array("Units.branch_name" => "ASC");
                    $conditions = array("Units.status" => 1);
                }
            } elseif ($model == 'Departments') {
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
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
                $arr_order = array("TRIM(LeavePolicyGroup.LEAVEPOLICY_GROUP_NAME)" => "ASC");
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
            }

            // Edited by Akshay on 11-3-2026
            elseif ($model == 'SalaryStructures') {
                $conditions = array("structure_active" => 1);
            }
            // End
            else {
                $conditions = array("status" => 1);
                // Edited by Akshay on 27-1-2025
                $company_code = $this->Session->read('company_code');
                $user_group = $this->Session->read('user_group');
                if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $emp_pkey = $this->Session->read('emp_fkey');
                    $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $conditions['branch_code'] = $is_ho;
                    }
                }
                // End
            }


            if ($model == 'DayTimeProcedures') {
                $fields = array("DISTINCT `DayTimeProcedures`.`day_time_seq`, `DayTimeProcedures`.`day_time_desc`, `DayTimeProcedures`.`Sunday`, `DayTimeProcedures`.`Sunday_F`, `DayTimeProcedures`.`Monday`, `DayTimeProcedures`.`Monday_F`, `DayTimeProcedures`.`Tuesday`, `DayTimeProcedures`.`Tuesday_F`, `DayTimeProcedures`.`Wednesday`, `DayTimeProcedures`.`Wednesday_F`, `DayTimeProcedures`.`Thursday`, `DayTimeProcedures`.`Thursday_F`, `DayTimeProcedures`.`Friday`, `DayTimeProcedures`.`Friday_F`, `DayTimeProcedures`.`Saturday`, `DayTimeProcedures`.`Saturday_F`, `DayTimeProcedures`.`on_dutty1`, `DayTimeProcedures`.`off_dutty1`, `DayTimeProcedures`.`working_time1`, `DayTimeProcedures`.`on_dutty2`, `DayTimeProcedures`.`off_dutty2`, `DayTimeProcedures`.`working_time2`, `DayTimeProcedures`.`on_dutty3`, `DayTimeProcedures`.`off_dutty3`, `DayTimeProcedures`.`working_time3`, `DayTimeProcedures`.`on_dutty4`, `DayTimeProcedures`.`off_dutty4`, `DayTimeProcedures`.`working_time4`, `DayTimeProcedures`.`minuts_calc_perday`, `DayTimeProcedures`.`minuts_aftr_on_dutty_cal_late`, `DayTimeProcedures`.`minuts_bfr_off_dutty_cal_early`, `DayTimeProcedures`.`min_cal_late_ifnoclockin`, `DayTimeProcedures`.`min_cal_leave_early_ifnoclockout`, `DayTimeProcedures`.`min_aftr_off_dutty_cal_ot`, `DayTimeProcedures`.`min_bfr_on_dutty_cal_ot`, `DayTimeProcedures`.`work_time_day_off_cal_ot`, `DayTimeProcedures`.`active`, `DayTimeProcedures`.`isnextday`, `DayTimeProcedures`.`shift_allowance`, `DayTimeProcedures`.`otcomponents`, `DayTimeProcedures`.`start_date_effective`, `DayTimeProcedures`.`end_date_effective`, `DayTimeProcedures`.`strict_monitorings`, `DayTimeProcedures`.`minutes_per_half`, `DayTimeProcedures`.`is_multiple_days`, `DayTimeProcedures`.`no_of_shift_days`, `DayTimeProcedures`.`is_exception`, `DayTimeProcedures`.`include_break`");
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => $fields, "conditions" => $conditions, "joins" => $join)));
            } elseif ($model == 'Departments') {
                $fields = array("DISTINCT `Departments`.`id`, `Departments`.`dept_code`, `Departments`.`dept_name`, `Departments`.`status`");
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => $fields, "conditions" => $conditions, "joins" => $join)));
            } elseif ($model == 'LeavePolicyGroup') {
                $fields = array("DISTINCT `LeavePolicyGroup`.`COMPANY_CODE`, `LeavePolicyGroup`.`BRANCH_CODE`, `LeavePolicyGroup`.`LEAVEPOLICY_GROUP_ID`, `LeavePolicyGroup`.`LEAVEPOLICY_GROUP_NAME`, `LeavePolicyGroup`.`status` ");
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => $fields, "conditions" => $conditions, "joins" => $join, "order" => $arr_order)));
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
                    $arr_order = array("CONCAT(EmployeeDetails.first_name, IFNULL(EmployeeDetails.last_name, ' '))" => "ASC");
                    //$conditions[] = array('status' => 1);//edited by sinsiya
                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                        // Edited by Akshay on 17-2-2025
                        // if ($type == 'empAlteration') {
                        //     $conditions = array("status in(1,2,3)");
                        // } else {
                        $conditions = array("status in(1,2)");
                        // }
                        // End
                    } else {
                        // Edited by Akshay on 17-2-2025
                        // if ($type == 'empAlteration') {
                        //     $conditions = array("status in(1,3)");
                        // } else {
                        $conditions = array("status" => 1);
                        // }
                        // End
                    }
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
                    // Edited by Akshay on 27-1-2025
                    elseif ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $emp_pkey = $this->Session->read('emp_fkey');
                        $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                        $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                        if ($is_ho != 1) {
                            $conditions[] = array("branch_code" => $is_ho);
                        }
                    }
                    // End
                    //employee branch wise sorting ends here

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
                // Edited by Akshay on 11-3-2026
                case 'SalaryStructures':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['structure_id'];
                        $arr_criteriaItems[$key]['text'] = $value['structure_name'];
                        $key++;
                    }
                    break;
                    // End
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
            // Edited by Akshay on 11-3-2026
            case 'salarystructures':
                $dataForHistory['report_type'] = "Salary Structure Reports";
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
            case 'employee':
                $this->generateemployeereport($mode);
                break;
            case 'shiftpolicy':
                $this->generateshiftpolicyreport($mode);
                break;
            case 'leavepolicy':
                $this->generateleavepolicyreport($mode);
                break;
            case 'holiday':
                $this->generateholidaypolicyreport($mode);
                break;
            // Edited by Akshay on 11-3-2026
            case 'salarystructures':
                $this->generateSalaryStructureReport($mode);
                break;
            // End
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

    private function generateemployeereport($mode = '')
    {
        try {
            $arr_form_data = $_REQUEST;
            $str_company_code = $this->Session->read('company_code'); //Edited by Akshay on 18-11-2023
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

            $arr_reportfields = array();
            $fields = '';
            if (isset($arr_form_data['hidden-reportfields']) && $arr_form_data['hidden-reportfields'] != '') {
                $fields = $arr_form_data['hidden-reportfields'];
                // debug($fields);  edited by sinsiya on 08-11-2024
                if (strpos($fields, 'EmployeeDetails.status') === false) {
                    // If not, append it with a comma
                    $fields .= ',EmployeeDetails.status';
                }
                //Edited by Akshay on 15-9-2023
                // Check if 'EmployeeDetails.physical_handicap' is present in $fields
                if (strpos($fields, 'EmployeeDetails.physical_handicap') !== false) {
                    // Append the additional fields before 'physical_handicap'
                    $fields = str_replace('EmployeeDetails.physical_handicap', 'EmployeeDetails.locomotive,EmployeeDetails.hearing,EmployeeDetails.visual,EmployeeDetails.physical_handicap', $fields);
                }

                if (strpos($fields, 'Category.category_pkey') !== false) {
                    // Append the additional fields before 'Category.category_pkey'
                    $fields = str_replace('Category.category_pkey', 'Category.category_name,Category.category_code,Category.category_pkey', $fields);
                }
                // debug($fields); exit;
                if (strpos($fields, 'Grades.grade_pkey') !== false) {
                    // Append the additional fields before 'Category.category_pkey'
                    $fields = str_replace('Grades.grade_pkey', 'Grades.grade_name,Grades.grade_code,Grades.grade_pkey', $fields);
                }

                if (strpos($fields, 'Contract.contracted_days_pkey') !== false) {
                    // Append the additional fields before 'Category.category_pkey'
                    $fields = str_replace('Contract.contracted_days_pkey', 'Contract.contract_start_date,Contract.contract_end_date,Contract.contracted_days_pkey', $fields);
                }

                $search = array('EmployeeDetails.', 'EmployeeProfessionalDetails.', 'Departments.', 'Verticals.', 'Units.', 'Designation.');
                $replace = array('', '', '', '', '', '');
                $str_reportfieldheadings = str_replace($search, $replace, $fields);
                $arr_reportfieldheadings = explode(',', $str_reportfieldheadings);
            }

            if (isset($arr_reportfieldheadings) && !empty($arr_reportfieldheadings)) {
                if ($str_company_code == 'KWMT') {
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
                            'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code and Departments.status=1')
                        ),
                        array(
                            'table' => 'designation',
                            'alias' => 'Designation',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code and Designation.status=1')
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
                            'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code and Units.status=1')
                        ),
                        // array(
                        //     'table' => 'emp_family',
                        //     'alias' => 'Family',
                        //     'type' => 'LEFT',
                        //     'foreignKey' => false,
                        //     'conditions' => array('EmployeeDetails.emp_pkey = Family.emp_fkey', 'is_nominee' => 'Y'),
                        //     'limit' => 1
                        // ),
                        array( //Edited by Akshay on 14-11-2023
                            'table' => 'employee_info',
                            'alias' => 'EmployeeInfo',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeInfo.emp_pkey')
                        ),
                        array( //Edited by Akshay on 14-11-2023
                            'table' => 'grade',
                            'alias' => 'Grades',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeProfessionalDetails.emp_grade = Grades.grade_pkey')
                        ),
                        array( //Edited by Akshay on 14-11-2023
                            'table' => 'category',
                            'alias' => 'Category',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array(
                                'Grades.category_fkey = Category.category_pkey',
                                "EmployeeProfessionalDetails.emp_type = 'Permanent'"
                            )
                        ),
                        array( //Edited by Akshay on 14-11-2023
                            'table' => 'countries_nationality',
                            'alias' => 'Nationality',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.nationality_id = Nationality.id')
                        ),
                        array( //Edited by Akshay on 15-11-2023
                            'table' => 'countries',
                            'alias' => 'Countries',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.country = Countries.id')
                        ),
                        array( //Edited by Akshay on 20-11-2023
                            'table' => 'contracted_days',
                            'alias' => 'Contract',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array(
                                'EmployeeDetails.emp_pkey = Contract.emp_fkey',
                                'Contract.end_date_effective IS NULL'
                            )
                        ),
                    );
                } else {
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
                            'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code and Departments.status=1')
                        ),
                        array(
                            'table' => 'designation',
                            'alias' => 'Designation',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code and Designation.status=1')
                        ),
                        array(
                            'table' => 'grade',
                            'alias' => 'Grades',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeProfessionalDetails.emp_grade = Grades.grade_pkey and Grades.status=1')
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
                            'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code and Units.status=1')
                        ),
                        array(
                            'table' => 'emp_family',
                            'alias' => 'Family',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.emp_pkey = Family.emp_fkey', 'is_nominee' => 'Y'),
                            'limit' => 1
                        ),
                        array( //Edited by Akshay on 14-11-2023
                            'table' => 'employee_info',
                            'alias' => 'EmployeeInfo',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeInfo.emp_pkey')
                        )
                    );
                }

                // $conditions = array('EmployeeDetails.status' => 1);

                //ASHIN ANTONY         
                $conditions1 = []; // Initialize conditions1
                if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                    $conditions1[] = "EmployeeDetails.status IN ('1', '2')";
                } else {
                    $conditions1[] = "EmployeeDetails.status = 1";
                }

                $conditions = $conditions1;
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("EmployeeDetails.emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $conditions[] = array("`EmployeeDetails`.`branch_code`" => $cur_emp_branch);
                }

                // Edited by Akshay on 11-2-2025
                elseif ($user_group == '2' && ($user == 'GLET' || $user == 'ABSG')) {
                    $current_emp_pkey = $this->Session->read('emp_fkey');
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_is_ho = $this->EmployeeDetails->query(
                        "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                        ['emp_pkey' => $current_emp_pkey]
                    );
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $conditions[] = array("`EmployeeDetails`.`branch_code`" => $is_ho);
                    }
                }
                // End
                //Build conditions based on criterias recieved
                $int_criterias_count = $arr_form_data['hidden-criterias-count'];
                if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '') && (isset($arr_form_data['reportto']) && $arr_form_data['reportto'] != '')) {
                    $conditions[] = 'EmployeeProfessionalDetails.joining_date BETWEEN "' . $arr_form_data['reportfrom'] . '" AND "' . $arr_form_data['reportto'] . '"';
                }
                for ($i = 1; $i <= $int_criterias_count; $i++) {
                    $str_criteria_item = isset($arr_form_data['hidden-criteria' . $i]) ? $arr_form_data['hidden-criteria' . $i] : ''; //isset added by **ARUL P DAS on 17/12/2019

                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    if ($str_criteria_item != '') { //This is to check whether criteria selected or not by **ARUL P DAS on 17/12/2019
                        $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("fields" => "reportcriteria_field", "conditions" => array("status" => 1, 'reportcriteria' => $str_criteria_item))));
                        // debug($arr_reportcriterias); exit;
                        if (isset($arr_reportcriterias[0]['reportcriteria_field'])) {
                            if ($str_criteria_item != 'EmployeeProfessionalDetails') {
                                if (isset($arr_form_data[$str_criteria_item])) { //This is to check whether criteria items selected or not by **ARUL P DAS on 17/12/2019
                                    // $conditions[] = $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                                    if ($arr_reportcriterias[0]['reportcriteria_field'] == 'emp_pkey') { //Edited by Akshay on 14-11-2023
                                        $conditions[] = 'EmployeeDetails.emp_pkey IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                                    } elseif ($arr_reportcriterias[0]['reportcriteria_field'] == 'joining_date') {
                                        $conditions[] = 'EmployeeDetails.emp_pkey IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                                    } else {
                                        $conditions[] = $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                                    }
                                }
                            }
                        }
                    }
                }

                //debug($conditions);
                $arr_emp_details = $this->EmployeeDetails->find("all", array(
                    'fields' => $fields,
                    'joins' => $joins,
                    'conditions' => $conditions,
                    'group' => array("EmployeeDetails.emp_pkey"), //edited by bindu v on 27-11-2025
                    'order' => array("EmployeeInfo.EmpName ASC") //Edited by Akshay on 15-11-2023
                ));
                //debug($arr_emp_details);
                if ($str_company_code == 'KWMT') {
                    App::import('Vendor', 'WaterMetroEmployeeInformationFields', array('file' => 'ReportFields' . DS . 'WaterMetroEmployeeInformationFields.php'));
                } else {
                    App::import('Vendor', 'EmployeeInformationFields', array('file' => 'ReportFields' . DS . 'EmployeeInformationFields.php'));
                }
                $arr_empinformation_fields = new EmployeeInformationFields();
                if ($str_company_code == 'KWMT') {
                    $arr_emp_field_headings = array_merge(
                        //edited by megha on 19/07/2019 branch name duplication 1

                        $arr_empinformation_fields->getFieldHeadings('EmployeeInfo'), //Edited by Akshay on 14-11-2023
                        $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'),
                        $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'),
                        $arr_empinformation_fields->getFieldHeadings('Departments'),
                        //$arr_empinformation_fields->getFieldHeadings('Verticals'),
                        array('branch' => 'Branch'),
                        // $arr_empinformation_fields->getFieldHeadings('Family'),
                        $arr_empinformation_fields->getFieldHeadings('Designation'),
                        $arr_empinformation_fields->getFieldHeadings('Grades'),
                        $arr_empinformation_fields->getFieldHeadings('Category'),
                        $arr_empinformation_fields->getFieldHeadings('Nationality'),
                        $arr_empinformation_fields->getFieldHeadings('Countries'),
                        $arr_empinformation_fields->getFieldHeadings('Contract')
                    );

                    $arr_emp_field_names = array(
                        'EmployeeInfo' => array_keys($arr_empinformation_fields->getFieldNames('EmployeeInfo')), //Edited by Akshay on 14-11-2023
                        'EmployeeDetails' => array_keys($arr_empinformation_fields->getFieldNames('EmployeeDetails')),
                        'EmployeeProfessionalDetails' => array_keys($arr_empinformation_fields->getFieldNames('EmployeeProfessionalDetails')),
                        'Departments' => array_keys($arr_empinformation_fields->getFieldNames('Departments')),
                        //'Grades' => array_keys($arr_empinformation_fields->getFieldNames('Grades')),
                        //'Verticals' => array_keys($arr_empinformation_fields->getFieldNames('Verticals')),
                        'Units' => array_keys($arr_empinformation_fields->getFieldNames('Units')),
                        // 'Family' => array_keys($arr_empinformation_fields->getFieldNames('Family')),
                        'Designation' => array_keys($arr_empinformation_fields->getFieldNames('Designation')),
                        'Grades' => array_keys($arr_empinformation_fields->getFieldNames('Grades')),
                        'Category' => array_keys($arr_empinformation_fields->getFieldNames('Category')),
                        'Nationality' => array_keys($arr_empinformation_fields->getFieldNames('Nationality')),
                        'Countries' => array_keys($arr_empinformation_fields->getFieldNames('Countries')),
                        'Contract' => array_keys($arr_empinformation_fields->getFieldNames('Contract'))
                    );
                } else {
                    $arr_emp_field_headings = array_merge(
                        //edited by megha on 19/07/2019 branch name duplication 1
                        // $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'), $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'), $arr_empinformation_fields->getFieldHeadings('Departments'), $arr_empinformation_fields->getFieldHeadings('Grades'), $arr_empinformation_fields->getFieldHeadings('Verticals'), $arr_empinformation_fields->getFieldHeadings('Units'), $arr_empinformation_fields->getFieldHeadings('Family'), $arr_empinformation_fields->getFieldHeadings('Designation')
                        $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'),
                        $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'),
                        $arr_empinformation_fields->getFieldHeadings('Departments'),
                        $arr_empinformation_fields->getFieldHeadings('Grades'),
                        $arr_empinformation_fields->getFieldHeadings('Verticals'),
                        array('branch' => 'Branch'),
                        $arr_empinformation_fields->getFieldHeadings('Family'),
                        $arr_empinformation_fields->getFieldHeadings('Designation')
                    );
                    $arr_emp_field_names = array(
                        'EmployeeDetails' => array_keys($arr_empinformation_fields->getFieldNames('EmployeeDetails')),
                        'EmployeeProfessionalDetails' => array_keys($arr_empinformation_fields->getFieldNames('EmployeeProfessionalDetails')),
                        'Departments' => array_keys($arr_empinformation_fields->getFieldNames('Departments')),
                        'Grades' => array_keys($arr_empinformation_fields->getFieldNames('Grades')),
                        'Verticals' => array_keys($arr_empinformation_fields->getFieldNames('Verticals')),
                        'Units' => array_keys($arr_empinformation_fields->getFieldNames('Units')),
                        'Family' => array_keys($arr_empinformation_fields->getFieldNames('Family')),
                        'Designation' => array_keys($arr_empinformation_fields->getFieldNames('Designation'))
                    );
                }

                //debug($arr_emp_field_names);
                $arr_merge_details = array();
                foreach ($arr_emp_details as $val) {
                    //edited by megha on 19/07/2019 branch name duplication 2
                    //debug($val['EmployeeDetails']);
                    if (isset($val['Units'])) {
                        $val['Branch']['branch'] = $val['Units']['branch_name'];
                    }
                    //edited by sinsiya on 08-11-2024
                    // if (isset($val['EmployeeDetails']['status'])) {
                    //  debug($arr_form_data['resigned']);
                    if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                        //  debug($val['EmployeeDetails']['status']);
                        if ($val['EmployeeDetails']['status'] == 2) {
                            $val['EmployeeDetails']['first_name'] .= " (Resigned)";
                        }
                        unset($val['EmployeeDetails']['status']); // Remove status from EmployeeDetails
                    } else {
                        unset($val['EmployeeDetails']['status']);
                    }
                    if ($str_company_code == 'KWMT') {
                        $arr_merge_details[] = array_merge(

                            isset($val['EmployeeInfo']) ? $val['EmployeeInfo'] : array(), //Edited by Akshay on 14-11-2023
                            isset($val['EmployeeDetails']) ? $val['EmployeeDetails'] : array(),
                            isset($val['EmployeeProfessionalDetails']) ? $val['EmployeeProfessionalDetails'] : array(),
                            isset($val['Departments']) ? $val['Departments'] : array(),
                            isset($val['Branch']) ? $val['Branch'] : array(),
                            isset($val['Family']) ? $val['Family'] : array(),
                            isset($val['Designation']) ? $val['Designation'] : array(),
                            isset($val['Grades']) ? $val['Grades'] : array(),
                            isset($val['Category']) ? $val['Category'] : array(),
                            isset($val['Nationality']) ? $val['Nationality'] : array(),
                            isset($val['Countries']) ? $val['Countries'] : array(),
                            isset($val['Contract']) ? $val['Contract'] : array()
                        );
                    } else {
                        $arr_merge_details[] = array_merge(
                            //$arr_emp_details['EmployeeDetails'], $arr_emp_details['EmployeeProfessionalDetails'], $arr_emp_details['Departments'], $arr_emp_details['Grades'], $arr_emp_details['Verticals'], $arr_emp_details['Units']
                            //edited by megha on 19/07/2019 branch name duplication 3
                            //$arr_emp_details['EmployeeDetails'], $arr_emp_details['EmployeeProfessionalDetails'], $arr_emp_details['Departments'], $arr_emp_details['Grades'], $arr_emp_details['Verticals'], $arr_emp_details['Units']
                            isset($val['EmployeeDetails']) ? $val['EmployeeDetails'] : array(),
                            isset($val['EmployeeProfessionalDetails']) ? $val['EmployeeProfessionalDetails'] : array(),
                            isset($val['Departments']) ? $val['Departments'] : array(),
                            isset($val['Grades']) ? $val['Grades'] : array(),
                            isset($val['Verticals']) ? $val['Verticals'] : array(),
                            isset($val['Branch']) ? $val['Branch'] : array(),
                            isset($val['Family']) ? $val['Family'] : array(),
                            isset($val['Designation']) ? $val['Designation'] : array()
                        );
                    }
                }
                //debug($arr_merge_details);
                //Edited by Akshay on 18-11-2023
                $user_id = $this->Session->read('login_user_id');
                date_default_timezone_set('Asia/Kolkata');
                $date_time = date('d-m-Y H:i');
                $this->set('user_id', $user_id);
                $this->set('date_time', $date_time);

                $arr_merge_details = array_merge($arr_merge_details);
                $this->set('arr_emp_field_headings', $arr_emp_field_headings);
                $this->set('arr_report_field_headings', $arr_reportfieldheadings);
                $this->set('arr_emp_field_names', $arr_emp_field_names);
                $this->set('arr_employee_report_details', $arr_emp_details);
                $this->set('arr_employee_report_details_merged', $arr_merge_details);
                $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
                $user_name = $this->Session->read('user_name');
                $this->set('user_name', $user_name);
                $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
                $this->set('arr_comp_contact_info', $arr_comp_contact_info);
                //Edited by Akshay on 30-7-2024
                $this->set('user', $user);
                //End
            } else {
                echo "<div><h3>No data available under the selected criteria  </h3></div>";
                die();
            }

            //Edited by Akshay on 15-11-2023
            function columnNumberToLetters($columnNumber)
            {
                $letters = '';
                while ($columnNumber > 0) {
                    $remainder = ($columnNumber - 1) % 26;
                    $letters = chr(65 + $remainder) . $letters;
                    $columnNumber = intval(($columnNumber - $remainder) / 26);
                }
                return $letters;
            }

            switch ($mode) {
                case 'pdf':
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('reportemployeeinformation');
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('L', 'A2', 'fr');
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
                    //edited by sinsiya 06-06-2024 to bring center allignment
                    $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Information ");
                    $worksheet->mergeCells('A1:E1'); // merge cells A1 to E1
                    $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setSize(16);
                    $worksheet->getStyle('A1:E1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    date_default_timezone_set('Asia/Kolkata');
                    // $worksheet->mergeCells("A2:N2");
                    //edited by sinsiya 06-06-2024 to change the size of report run by
                    $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    //                    $objPHPExcel->getActiveSheet()
                    //                        ->getStyle('A3')
                    //                        ->getFont()
                    //                        ->getColor()
                    //                        ->setRGB('FF0000');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    $sheet = array($arr_emp_field_headings);

                    //Edited by Akshay on 15-11-2023
                    //Border style
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    // Get the style of the cell
                    $leftstyle = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        )
                    );
                    $objPHPExcel->getActiveSheet()->freezePane('A4');

                    $columnindex = 0;
                    $columnname = 'E';
                    //edited by athira on 17-06-2025

                    $hasData = array_filter($arr_merge_details, function ($row) {
                        return count(array_filter(array_diff_key($row, ['attr4' => '']))) > 0;
                    });

                    $sheet = $objPHPExcel->getActiveSheet();
                    $sheet->freezePane('A4');

                    if (empty($arr_merge_details) || empty($hasData)) {
                        // Show only "No Data Found" in A3
                        $sheet->setCellValue('A3', 'There is no data found under this criteria');
                        $sheet->mergeCells("A3:E3"); // Adjust D to last visible column
                        $sheet->getStyle("A3")->getFont()->setBold(true);
                        $sheet->getStyle("A3")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    } else {
                        // === HEADER SECTION ===
                        $columnindex = 0;
                        $sheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . "3", 'Sl No');
                        $sheet->getStyleByColumnAndRow($columnindex, 3)->getFont()->setBold(true);
                        $sheet->getStyleByColumnAndRow($columnindex, 3)->applyFromArray($leftstyle);
                        $columnindex++;

                        $heading_arr = isset($arr_merge_details) ? $arr_merge_details[0] : [];

                        foreach ($heading_arr as $key => $val) {
                            //edited by sinsiya 06-06-2024
                            if (trim($key) != 'locomotive' && trim($key) != 'hearing' && trim($key) != 'visual' && trim($key) != 'category_name' && trim($key) != 'category_code' && trim($key) != 'grade_code' && trim($key) != 'contract_start_date' && trim($key) != 'contract_end_date') {

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . "3", $arr_emp_field_headings[$key]);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnindex, 3)->getFont()->setBold(true);

                                $columnindex++;

                                //Edited by Akshay on 15-11-2023
                                // debug($arr_emp_field_headings[$key]);
                                $columnname = columnNumberToLetters($columnindex);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnname)->setAutoSize(false);
                                if (isset($arr_emp_field_headings[$key]) && ($arr_emp_field_headings[$key] == 'Address' || $arr_emp_field_headings[$key] == 'Bank Name')) {
                                    $minimumWidth = 100;
                                } else {
                                    $minimumWidth = 55;
                                }
                                $currentWidth = $objPHPExcel->getActiveSheet()->getColumnDimension($columnname)->getWidth();
                                $newWidth = max($currentWidth, $minimumWidth);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnname)->setWidth($newWidth);
                            }
                            //echo '<th>' . isset($arr_emp_field_headings[$val]) ? $arr_emp_field_headings[$val] : "" . '</th>';
                        }

                        if (!empty($arr_merge_details)) {
                            //Background color for heading
                            //                        $objPHPExcel->getActiveSheet()
                            //                            ->getStyle('A4:' . $columnname . '4')
                            //                            ->getFill()
                            //                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            //                            ->getStartColor()
                            //                            ->setRGB('C8C8C8');
                        }


                        $rowcount = 4;
                        //edited by megha sl.no added for excel 10/08/2019 2
                        $j = 0;
                        // debug($arr_merge_details);exit;
                        foreach ($arr_merge_details as $key => $value) {
                            $columnindex = 0;
                            //edited by megha sl.no added for excel 10/08/2019 3
                            $j++;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $j);
                            //Edited by Akshay on 15-11-2023
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), ($rowcount))->applyFromArray($leftstyle);
                            $columnindex++;
                            //Edited by Akshay on 15-9-2023
                            $physical_handicap = '';
                            $category_name = null;
                            $category_code = null;
                            $grade_name = null;
                            $grade_code = null;
                            $contract_startdate = null;
                            $contract_enddate = null;
                            $grade = '';
                            $category = '';
                            $contract = '';
                            //end sl.no
                            foreach ($value as $key => $val) {
                                $dspl = $val;

                                if ($key == 'date_of_birth') {
                                    $dspl = date("d-m-Y", strtotime($val));
                                } else if ($key == 'joining_date') {
                                    $dspl = date("d-m-Y", strtotime($val));
                                } else if (trim($key) == 'locomotive' || trim($key) == 'hearing' || trim($key) == 'visual') {
                                    if (trim($val) == 'Y') {
                                        if (trim($physical_handicap) == '') {
                                            $physical_handicap .= ucfirst($key);
                                        } else {
                                            $physical_handicap .= ', ' . ucfirst($key);
                                        }
                                    }
                                } else if (trim($key) == 'physical_handicap') {
                                    if (trim($val) == 'Y') {
                                        $dspl = $physical_handicap;
                                    } else {
                                        $dspl = '';
                                    }
                                } else {

                                    $dspl = $val;
                                }

                                if (trim($key) != 'locomotive' && trim($key) != 'hearing' && trim($key) != 'visual') {
                                    if (trim($dspl) == 'Y' && (trim($key) == 'eps' || trim($key) == 'international_worker')) {
                                        $dspl = 'Yes';
                                    } else if ($dspl == 'N' && (trim($key) == 'eps' || trim($key) == 'international_worker')) {
                                        $dspl = 'No';
                                    } else if ($key == 'classification') {
                                        $dspl = ucfirst($val);
                                        if ($val == 'Other') {
                                            $dspl = 'Transgender';
                                        }
                                    } else if ($key == 'maritual_status') {
                                        $dspl = ucfirst($val);
                                    }
                                    //Grade and category
                                    else if ((trim($key) == 'category_name' || trim($key) == 'category_code')) {
                                        if (trim($key) == 'category_name') {
                                            $category_name = $val;
                                        } elseif (trim($key) == 'category_code') {
                                            $category_code = $val;
                                        }
                                        if ($category_name != null && $category_code != null) {
                                            $category = $category_name . ' (' . $category_code . ')';
                                        }
                                    } elseif (trim($key) == 'category_pkey') {
                                        if (trim($val) != null) {
                                            $dspl =  $category;
                                        } else {
                                            $dspl = '';
                                        } //edited by sinsiya 06-06-2024 to show the grade when selected center allignment
                                    } else if ((trim($key) == 'grade_name' || trim($key) == 'grade_code')) {
                                        if (trim($key) == 'grade_name') {
                                            $grade_name = $val;
                                        } elseif (trim($key) == 'grade_code') {
                                            $grade_code = $val;
                                        }
                                        if ($grade_name != null || $grade_code != null) {
                                            // $grade = $grade_name . ' (' . $grade_code . ')';
                                            $grade = $grade_name;
                                        }
                                        //edited by sinsiya 06-06-2024
                                        if (trim($val) != null) {
                                            $dspl = $grade;
                                        } else {
                                            $dspl = '';
                                        }
                                    } //elseif (trim($key) == 'grade_pkey') {

                                    //}
                                    else if ((trim($key) == 'contract_start_date' || trim($key) == 'contract_end_date')) {
                                        if (trim($key) == 'contract_start_date') {
                                            $contract_startdate = isset($val) ?  date("d-m-Y", strtotime($val)) : '';
                                        } elseif (trim($key) == 'contract_end_date') {
                                            $contract_enddate = isset($val) ?  date("d-m-Y", strtotime($val)) : '';
                                        }
                                        if ($contract_startdate != null && $contract_enddate != null) {
                                            $contract = $contract_startdate . ' to ' . $contract_enddate;
                                        }
                                    } elseif (trim($key) == 'contracted_days_pkey') {
                                        if (trim($val) != null) {
                                            $dspl = $contract;
                                        } else {
                                            $dspl = '';
                                        }
                                    }
                                    if (trim($key) != 'locomotive' && trim($key) != 'hearing' && trim($key) != 'visual' && trim($key) != 'category_name' && trim($key) != 'category_code' && trim($key) != 'grade_code' && trim($key) != 'contract_start_date' && trim($key) != 'contract_end_date') {
                                        $objPHPExcel->getActiveSheet()->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dspl, PHPExcel_Cell_DataType::TYPE_STRING);
                                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

                                        $columnindex++;
                                    }


                                    //Edited by Akshay on 15-11-2023
                                    $columnname = columnNumberToLetters($columnindex);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnname)->setAutoSize(false);
                                    $minimumWidth = 65;
                                    $currentWidth = $objPHPExcel->getActiveSheet()->getColumnDimension($columnname)->getWidth();
                                    $newWidth = max($currentWidth, $minimumWidth);
                                    // $objPHPExcel->getActiveSheet()->getColumnDimension($columnname)->setWidth($newWidth);
                                    //debug($columnname);exit;

                                }
                            }
                            $rowcount++;
                        } // //edited by sinsiya 06-06-2024 to merge
                        if (!empty($arr_merge_details)) {
                            $worksheet->mergeCells('A2:D2');
                            //$worksheet->mergeCells('A3:D3');
                        } else {
                            $worksheet->mergeCells('A2:N2');
                            $worksheet->mergeCells('A3:N3');
                        }

                        //Edited by Akshay on 15-11-2023
                        //Hide grid lines
                        $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                        $lastrow = $objPHPExcel->getActiveSheet()->getHighestRow();
                        if (!empty($arr_merge_details)) {
                            $objPHPExcel->getActiveSheet()->getStyle('A3:' . $columnname . $lastrow)->applyFromArray($styleArray);
                        } else {
                            $objPHPExcel->getActiveSheet()->getStyle('A3:N' . $lastrow)->applyFromArray($styleArray);
                        }

                        $objPHPExcel->getActiveSheet()->setTitle('Employee Information');
                        /* header footer */
                        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                        /* header footer */
                    }

                    // end - athira 17-06-2025
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
                    $this->render('reportemployeeinformation');
                    break;
            }
        } catch (Exception $e) {
            // debug($e);
        }
    }

    private function generateshiftpolicyreport($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');

        $fields = 'DayTimeProcedures.*,EmployeeConfig.*,Info.*';

        $joins = array(
            //edited by athira on 10-09-2025
            array(
                'table' => 'emp_config',
                'alias' => 'EmployeeConfig',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('DayTimeProcedures.day_time_seq = EmployeeConfig.policy_id AND EmployeeConfig.status = 1')
            ),
            //end
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeConfig.emp_fkey = EmployeeDetails.emp_pkey')
            ),
            //            array(
            //                'table' => 'emp_proff',
            //                'alias' => 'EmployeeProfessionalDetails',
            //                'type' => 'LEFT',
            //                'foreignKey' => false,
            //                'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            //            ),
            //            array(
            //                'table' => 'department',
            //                'alias' => 'Departments',
            //                'type' => 'LEFT',
            //                'foreignKey' => false,
            //                'conditions'=> array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            //            ),
            //            array(
            //                'table' => 'grade',
            //                'alias' => 'Grades',
            //                'type' => 'LEFT',
            //                'foreignKey' => false,
            //                'conditions'=> array('EmployeeProfessionalDetails.emp_grade = Grades.grade_code')
            //            ),
            //            array(
            //                'table' => 'verticals',
            //                'alias' => 'Verticals',
            //                'type' => 'LEFT',
            //                'foreignKey' => false,
            //                'conditions'=> array('EmployeeProfessionalDetails.emp_vertical = Verticals.vert_code')
            //            ),
            //            array(
            //                'table' => 'branches',
            //                'alias' => 'Units',
            //                'type' => 'LEFT',
            //                'foreignKey' => false,
            //                'conditions'=> array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            //            ),
            array(
                'table' => 'employee_info',
                'alias' => 'Info',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeConfig.emp_fkey  = Info.emp_pkey')
            ),
        );
        //edited by ASHIN on 06-11-24      
        // $conditions = array('EmployeeDetails.status' => 1, ' EmployeeConfig.type="SHIFT" ');
        $conditions = array(
            'EmployeeConfig.type' => 'SHIFT'
        );

        $conditions1 = []; // Initialize conditions1
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $conditions1[] = "EmployeeDetails.status IN ('1', '2')";
        } else {
            $conditions1[] = "EmployeeDetails.status = 1";
        }

        // Edited by Akshay on 11-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $conditions1[] = "EmployeeDetails.branch_code = '$is_ho'";
            }
        }
        // End
        $conditions = $conditions1;

        //END  
        //
        //Build conditions based on criterias recieved
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '') && (isset($arr_form_data['reportto']) && $arr_form_data['reportto'] != '')) {
            $conditions[] = 'EmployeeProfessionalDetails.joining_date BETWEEN "' . $arr_form_data['reportfrom'] . '" AND "' . $arr_form_data['reportto'] . '"';
        }
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("fields" => "reportcriteria,reportcriteria_field", "conditions" => array("status" => 1, 'reportcriteria' => $str_criteria_item))));
            if (isset($arr_reportcriterias[0]['reportcriteria_field'])) {
                if ($str_criteria_item != 'EmployeeProfessionalDetails') {
                    if (isset($arr_form_data[$str_criteria_item])) {
                        $conditions[] = $arr_reportcriterias[0]['reportcriteria'] . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                        //debug($conditions);}
                    }
                }
            }
        }
        // debug($arr_form_data);
        //        for($i=0;$i<count($arr_days);$i++){
        //            if(isset($arr_shiftpolicy_details[0]['DayTimeProcedures'][$arr_days[$i]])){
        //                if($arr_shiftpolicy_details[0]['DayTimeProcedures'][$arr_days[$i]] == 'Y'){
        //                    $arr_workingdays[] = $arr_days[$i];
        //                }else if($arr_shiftpolicy_details[0]['DayTimeProcedures'][$arr_days[$i]] == 'N'){
        //                    $arr_offdays[] = $arr_days[$i];
        //                }
        //            }
        //        }



        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            //debug($str_criteria_item);
            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;
        }
        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            $arr_shiftpolicy = array(); //This is added by ***ARUL P DAS on 20/1/2020
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                //   debug($leavepolicygroupid);

                $cur_emp_condition = "";
                //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $cur_emp_condition = " AND EmployeeDetails.branch_code='" . $cur_emp_branch . "'";
                }
                //employee branch wise sorting ends here

                //edited by ASHIN on 06-11-24
                //$conditions = array('EmployeeDetails.status' => 1, ' EmployeeConfig.type="SHIFT" ', ' EmployeeConfig.status=1 ', 'DayTimeProcedures.day_time_seq = ' . $leavepolicygroupid . $cur_emp_condition);

                //edited by athira on 06-11-2025

                // Make a fresh copy of base conditions for each loop
                $shift_conditions = $conditions;

                // Add shift-specific condition
                $shift_conditions[] = 'DayTimeProcedures.day_time_seq = ' . $leavepolicygroupid . $cur_emp_condition;

                $arr_shiftpolicy_details = $this->DayTimeProcedures->find("all", array(
                    'fields' => $fields,
                    'joins' => $joins,
                    'conditions' => $shift_conditions,
                    'group' => array("EmployeeDetails.emp_pkey")
                ));
                //end


                // debug($arr_shiftpolicy_details);
                //The below query is to collect all shift policies. by ***ARUL P DAS on 20/1/2020
                $arr_shift_items = $this->DayTimeProcedures->query('SELECT `DayTimeProcedures`.* FROM `working_day_time_procedures` AS `DayTimeProcedures` WHERE `DayTimeProcedures`.`day_time_seq` = ' . $leavepolicygroupid . ' and active=1');
                $arr_shiftpolicy[] = $arr_shift_items;

                $arr_duty = array();
                for ($i = 1; $i <= 4; $i++) {
                    $duty = array();
                    $duty['on_dutty'] = isset($arr_shiftpolicy_details[0]['DayTimeProcedures']['on_dutty' . $i]) ? $arr_shiftpolicy_details[0]['DayTimeProcedures']['on_dutty' . $i] : '';
                    $duty['off_dutty'] = isset($arr_shiftpolicy_details[0]['DayTimeProcedures']['off_dutty' . $i]) ? $arr_shiftpolicy_details[0]['DayTimeProcedures']['off_dutty' . $i] : '';
                    $duty['working_time'] = isset($arr_shiftpolicy_details[0]['DayTimeProcedures']['working_time' . $i]) ? $arr_shiftpolicy_details[0]['DayTimeProcedures']['working_time' . $i] : '';
                    $arr_duty[] = $duty;
                }


                //  $this->set('arr_workingdays',$arr_workingdays);
                // $this->set('arr_offdays',$arr_offdays);
                $arr_days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
                $this->set('arr_days', $arr_days);

                foreach ($arr_shiftpolicy_details as $key => $leavedays) {

                    $arr_workingdays = array();
                    $arr_offdays = array();
                    for ($i = 0; $i < count($arr_days); $i++) {

                        if (isset($leavedays['DayTimeProcedures'][$arr_days[$i]])) {
                            if ($leavedays['DayTimeProcedures'][$arr_days[$i]] == 'Y') {
                                $arr_workingdays[] = $arr_days[$i];
                            } else if ($leavedays['DayTimeProcedures'][$arr_days[$i]] == 'N') {
                                $arr_offdays[] = $arr_days[$i];
                            }
                        }
                    }
                    $onday = implode(',', $arr_workingdays);
                    $offdays = implode(',', $arr_offdays);
                    $arr_shiftpolicy_details[$key]['DayTimeProcedures']['ondays'] = $onday;
                    $arr_shiftpolicy_details[$key]['DayTimeProcedures']['offdays'] = $offdays;
                    //   debug($arr_shiftpolicy_details[$key]['DayTimeProcedures']);
                    //   debug($onday);
                    //   debug($offdays);
                    //   debug($arr_workingdays);
                    // debug($arr_offdays);
                }


                $arr_leavepolicydetails_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_shiftpolicy_details,
                    'policytitle' => isset($arr_shiftpolicy_details[0]['DayTimeProcedures']['day_time_desc']) ? $arr_shiftpolicy_details[0]['DayTimeProcedures']['day_time_desc'] : '',
                    //The day_time_seq is added by ***ARUL P DAS on 20/1/2020
                    'day_time_seq' => isset($arr_shift_items[0]['DayTimeProcedures']['day_time_seq']) ? $arr_shift_items[0]['DayTimeProcedures']['day_time_seq'] : ''

                    // 'employees'=>$arr_leavepolicy_employees
                );
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        //debug($arr_leavepolicydetails_for_template)  ;die();
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $this->set('arr_shiftpolicy', $arr_shiftpolicy);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportshiftpolicy');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                //The below code is edited by ***ARUL P DAS on 20/1/2020
                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                $html2pdf->setTestTdInOnePage(false);

                //$html2pdf = new HTML2PDF('P', 'Legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('ShiftPolicyReport.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_ShiftPolicyReports.xlsx" : "EmployeeInformation_" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Shift Policy Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Shift Policy Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'B'; $col !== 'I'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:H1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->mergeCells('A2:H2');
                $worksheet->mergeCells('A3:H3');
                $worksheet->mergeCells('A4:H4');
                $worksheet->mergeCells('A5:H5');
                $worksheet->mergeCells('A6:H6');
                $worksheet->mergeCells('A7:H7');
                $worksheet->mergeCells('A8:H8');
                $worksheet->mergeCells('A9:H9');
                $worksheet->mergeCells('A10:H10');
                $worksheet->mergeCells('A11:H11');
                $i = 0;
                $columncount = 0;
                $rowcount = 2;
                //The $arr_shiftpolicy created by ***ARUL P DAS. This array consist of only shift policy details.
                $shift_iteration = 0;
                foreach ($arr_leavepolicydetails_for_template as $value) {
                    $i += 1;
                    $policyTitle = isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['day_time_desc']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['day_time_desc'] : '';
                    $workingdays = isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['ondays']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['ondays'] : '';
                    $offdays = isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['offdays']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['offdays'] : '';

                    $onduty = isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['on_dutty1']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['on_dutty1'] : '';
                    $offduty = isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['off_dutty1']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['off_dutty1'] : '';
                    $wrktime = isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['working_time1']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['working_time1'] : '';

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Shift Summary of ' . $policyTitle);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setSize(14);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, ($rowcount + 1), 'Policy Title :' . $policyTitle);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, ($rowcount + 2), 'Working Days :' . $workingdays);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, ($rowcount + 3), 'Off Days :' . $offdays);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, ($rowcount + 4), ' On Duty :' . $onduty . '                Off Duty  :' . $offduty . '                Working time   :' . $wrktime);
                    //                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount + 4), 'Off Duty  :' . $offduty);
                    //                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount + 4), 'working time   :' . $wrktime);
                    $daypro = isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_calc_perday']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_calc_perday'] : '';
                    $daypro1 = isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_aftr_on_dutty_cal_late']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_aftr_on_dutty_cal_late'] : '';
                    $daypro2 = isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_bfr_off_dutty_cal_early']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_bfr_off_dutty_cal_early'] : '';

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount + 5), ' Stat Rule');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount + 5))->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount + 5))->getFont()->setSize(14);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount + 6), ' Minutes calculated as per day:' . $daypro);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount + 7), ' Minutes after On duty calculated as late:' . $daypro1);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount + 8), 'Minutes before Off duty calculated as early:' . $daypro2);
                    // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount+10), ' Stat Rule');

                    $rowcount = $rowcount + 9;
                    if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_late_ifnoclockin']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_late_ifnoclockin'] = '') {

                        $val1 = 'Minutes calculated as late if no clock-in:' . $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_late_ifnoclockin'];
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $val1);
                        $rowcount++;
                    }
                    if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_leave_early_ifnoclockout']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_leave_early_ifnoclockout'] = '') {

                        $val2 = 'Minutes calculated as leave early if no clock-out: ' . $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_leave_early_ifnoclockout'];
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $val2);
                        $rowcount++;
                    }
                    if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_aftr_off_dutty_cal_ot']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_aftr_off_dutty_cal_ot'] = '') {

                        $val3 = 'Minutes after Off duty calculated as overtime:' . $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_aftr_off_dutty_cal_ot'];
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $val3);
                        $rowcount++;
                    }
                    if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_bfr_on_dutty_cal_ot']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_bfr_on_dutty_cal_ot'] = '') {

                        $val4 = 'Minutes before On duty calculated as overtime:' . $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_bfr_on_dutty_cal_ot'];
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $val4);
                        $rowcount++;
                    }
                    if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['work_time_day_off_cal_ot']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['work_time_day_off_cal_ot'] = '') {

                        $val5 = 'Working time in day off calculated as overtime:  ' . $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['work_time_day_off_cal_ot'];
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $val5);
                        $rowcount++;
                    }
                    $columncount = 0;
                    $shift_iteration++;
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount + 1), 'Employee List');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount + 1))->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount + 1))->getFont()->setSize(14);
                    $rowcount = $rowcount + 2;
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), 'Sl No');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Employee Name');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee ID');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Designation');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Date Of Joining');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Department');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Branch');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Grade');
                    for ($i = 0; $i <= 7; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setSize(12);
                    }
                    $rowcount = $rowcount + 1;
                    $i = 1;
                    if ($value['policytitle'] != '') {
                        foreach ($value['summary'] as $val) {
                            //edited by ASHIN on 08-11-24       
                            $empstatus = (isset($val['Info']['emp_status']) && $val['Info']['emp_status'] == "2") ? ' (Resigned)' : ''; // Check for resigned status
                            $name = $val['Info']['EmpName'] . $empstatus;
                            $id = $val['Info']['employee_id'];
                            $clas = $val['Info']['designation'];
                            $join = $val['Info']['joining_date'];
                            $dept = $val['Info']['department'];
                            $unit = $val['Info']['branch'];
                            $grade = $val['Info']['grade'];
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $i);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $name);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), $clas);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $unit);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), $grade);

                            $rowcount++;
                            $i++;
                        }
                    } else {
                        $msg = 'No employees found under this shift';
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $msg);
                    }

                    $rowcount++;
                }

                $objPHPExcel->getActiveSheet()->setTitle('Shift Policy Report');
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
                $this->render('reportshiftpolicy');
                break;
        }
    }

    private function generateleavepolicyreport($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->LeavePolicyGroup->useDbConfig = $this->Session->read('ds');

        //Build conditions based on criterias recieved
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            if ($str_criteria_item == 'LeavePolicyGroup') {
                //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
                $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            }
        }
        //edited by ASHIN on 06-11-24
        // Initialize conditions1 and set condition for resigned employees if required
        $conditions1 = [];
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $conditions1[] = "EmployeeDetails.status IN ('1', '2')";
        } else {
            $conditions1[] = "EmployeeDetails.status = 1";
        }
        // Edited by Akshay on 11-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $branch_condition = "";
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $conditions1[] = "EmployeeDetails.branch_code = '$is_ho'";
            }
        }
        // End

        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            $arr_leavepolicydetails_for_template = array();
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $str_conditions = ' WHERE LeavePolicyGroup.LEAVEPOLICY_GROUP_ID=' . $leavepolicygroupid;
                $arr_leavepolicy_name = $this->LeavePolicyGroup->query(''
                    . 'SELECT '
                    . ' LEAVEPOLICY_GROUP_NAME '
                    . 'FROM '
                    . '`leavepolicy_group` AS `LeavePolicyGroup` '
                    . $str_conditions);
                $arr_leavepolicy_details = $this->LeavePolicyGroup->query(''
                    . 'SELECT '
                    . '`LeavePolicyGroup`.*, LeavePolicy.*,salary_head_items.item '
                    . 'FROM '
                    . '`leavepolicy_group` AS `LeavePolicyGroup` '
                    . 'LEFT JOIN `leavepolicy` AS `LeavePolicy` ON (`LeavePolicyGroup`.`LEAVEPOLICY_GROUP_ID` = `LeavePolicy`.`LEAVEPOLICY_GROUP_ID`)'
                    . 'LEFT JOIN `salary_head_items` AS `salary_head_items` ON (`salary_head_items`.`salary_head_item_pkey` = `LeavePolicy`.`salary_head_item_fkey`)'
                    . $str_conditions . ' and LeavePolicy.status=1');

                $conditions = ' WHERE LeavePolicyGroup.LEAVEPOLICY_GROUP_ID=' . $leavepolicygroupid . ' AND ' . implode(' AND ', $conditions1);
                $user_group = $this->Session->read('user_group');
                $emp_branch_conditions = "";
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $emp_branch_conditions = " and `EmployeeDetails`.`branch_code` ='" . $cur_emp_branch . "'";
                }
                $arr_leavepolicy_employees = $this->LeavePolicyGroup->query(''
                    . 'SELECT '
                    . '`Info`.* '
                    . 'FROM '
                    . '`leavepolicy_group` AS `LeavePolicyGroup` '
                    . 'LEFT JOIN `emp_proff` AS `EmployeeProfessionalDetails` ON (`LeavePolicyGroup`.`LEAVEPOLICY_GROUP_ID` = `EmployeeProfessionalDetails`.`LEAVEPOLICY_GROUP_ID`) '
                    . 'LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeProfessionalDetails`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`) '
                    . 'LEFT JOIN `employee_info` AS `Info` ON (`EmployeeProfessionalDetails`.`emp_fkey` = `Info`.`emp_pkey`)' . $conditions . $emp_branch_conditions);

                $arr_leavepolicydetails_for_template[] = array(
                    'leavepolicyname' => isset($arr_leavepolicy_name[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']) ? $arr_leavepolicy_name[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'] : '',
                    'summary' => $arr_leavepolicy_details,
                    'employees' => $arr_leavepolicy_employees
                );
            }

            //End           

            // debug($arr_leavepolicydetails_for_template);
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
                    $view_output = $view->render('reportleavepolicy');
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('LeavePolicyReport.pdf', 'D');
                    //$this->render('reportshiftpolicy');                
                    break;
                case 'excel':
                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "LeavePolicyReport.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Policy Report");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                    for ($col = 'A'; $col !== 'K'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(false);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $worksheet->mergeCells('A1:F1');
                    //                    $worksheet->mergeCells('A2:F2');
                    //                    $worksheet->mergeCells('A3:F3');
                    $worksheet->mergeCells('A4:B4');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $rowcount = 2;
                    $columncount = 0;
                    $i = 0;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $i += 1;
                        $pcy = 'Leave Summary of ' . $value['leavepolicyname'];
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $pcy);
                        $worksheet->mergeCells('A' . ($rowcount) . ':D' . ($rowcount));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setSize(14);
                        $name = 'Policy Title : ' . $value['leavepolicyname'];
                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $name);
                        $worksheet->mergeCells('A' . ($rowcount) . ':D' . ($rowcount));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setSize(12);
                        // $rowcount=$rowcount+1;
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Stat Rule');  
                        //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setSize(12);
                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), $rowcount, 'Leave Type');
                        $worksheet->mergeCells('A' . ($rowcount) . ':B' . ($rowcount));
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Remarks ');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Leave For The Year');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Leave For The Month');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Carry Forwards Limit');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Applicable To');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Allow Negative');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Sandwich ');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Leave EnCash ');
                        for ($i = 0; $i <= 9; $i++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, $rowcount)->getFont()->setBold(true);
                        }
                        $rowcount = $rowcount + 1;
                        $arr_data = $value['summary'];
                        $worksheet->mergeCells('A' . (5 + $columncount) . ':B' . (5 + $columncount));
                        $j = 1;
                        if (count($arr_data) > 0) {
                            foreach ($arr_data as $val) {
                                $type = $val['salary_head_items']['item'];
                                $remark = $val['LeavePolicy']['REMARKS'];
                                $aleaveyear = $val['LeavePolicy']['alloted_leave_forthe_year'];
                                $allaevemonth = $val['LeavePolicy']['alloted_leave_forthe_month'];
                                $cfl = $val['LeavePolicy']['CARRY_FORWARD_LIMIT'];
                                $apto = $val['LeavePolicy']['APPLICABLE_TO'];
                                $alneg = $val['LeavePolicy']['ALLOW_NEGETIVE'];
                                $sanwinch = $val['LeavePolicy']['IS_SANDWICH'];
                                $encl = $val['LeavePolicy']['is_leave_encash'];
                                $worksheet->mergeCells('A' . ($rowcount) . ':B' . ($rowcount)); //This is to merge leave type names
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), $rowcount, $type);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, $remark);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, $aleaveyear);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, $allaevemonth);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, $cfl);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, $apto);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, $alneg);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, $sanwinch);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, $encl);
                                $j++;
                                $rowcount = $rowcount + 1;
                            }
                        } else {
                            $worksheet->mergeCells('A' . ($rowcount) . ':D' . ($rowcount));
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), $rowcount, 'No leave types found under this leave policy');
                            $rowcount = $rowcount + 1;
                        }
                        $worksheet->mergeCells('A' . ($rowcount) . ':B' . ($rowcount)); //This is to merge Employee List
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), $rowcount, 'Employee List');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount), ($rowcount))->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee ID ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Designation ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Grade ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Date Of Joining ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Department ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), ($rowcount))->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $arr_data = $value['employees'];
                        if (count($arr_data) > 0) {
                            $i = 1;
                            foreach ($arr_data as $val) {
                                //edited by ASHIN on 08-11-24
                                $empstatus = (isset($val['Info']['emp_status']) && $val['Info']['emp_status'] == "2") ? ' (Resigned)' : ''; // Check for resigned status
                                $name = $val['Info']['EmpName'] . $empstatus;
                                $deg = $val['Info']['designation'];
                                $branch = $val['Info']['branch'];
                                $id = $val['Info']['employee_id'];
                                $join = $val['Info']['joining_date'];
                                $dept = isset($val['Info']['department']) ? $val['Info']['department'] : '';
                                $grade = isset($val['Info']['grade']) ? $val['Info']['grade'] : '';
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), $rowcount, $i);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, $id);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, $deg);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, $grade);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, $join);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, $dept);

                                $i++;
                                $rowcount++;
                            }
                        } else {
                            $worksheet->mergeCells('A' . ($rowcount) . ':D' . ($rowcount));
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), $rowcount, 'No employees found under this leave policy');
                        }
                        $rowcount++;
                    }
                    $objPHPExcel->getActiveSheet()->setTitle('Leave Policy Report');
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
                    $this->render('reportleavepolicy');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }

    private function generateholidaypolicyreport($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->HolidayGroup->useDbConfig = $this->Session->read('ds');

        //edited by athira on 21-06-2025

        $report_from = $arr_form_data['reportfrom']; // e.g., "2023-01"
        $year = substr($report_from, 0, 4);          // Get year part => "2023"

        $from_date = $year . '-01-01';               // Start of year
        $to_date   = $year . '-12-31';               // End of year

        //end
        //Build conditions based on criterias recieved
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            if ($str_criteria_item == 'HolidayGroup') {
                //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
                $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
                //debug($arr_leavepolicygroupids);
            }
        }

        $arr_leavepolicydetails_for_template = array();
        //isset($leavepolicygroupid)?$leavepolicygroupid:'';
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $str_conditions = ' WHERE HolidayGroup.HOLIDAY_GROUP_ID=' . $leavepolicygroupid;

                // $arr_leavepolicy_details = $this->HolidayGroup->query(''
                //     . 'SELECT '
                //     . '`HolidayGroup`.*, holidays.*'
                //     . 'FROM '
                //     . '`holiday_group` AS `HolidayGroup` '
                //     . 'LEFT JOIN `holidays` AS `holidays` ON (`HolidayGroup`.`HOLIDAY_GROUP_ID` = `holidays`.`HOLIDAY_GROUP_ID`)'
                //     . $str_conditions . ' and holidays.status=1');
                //edited by athira on 21-06-2025

                $arr_leavepolicy_details = $this->HolidayGroup->query(
                    'SELECT 
                        `HolidayGroup`.*, 
                        holidays.*
                    FROM 
                        `holiday_group` AS `HolidayGroup`
                    LEFT JOIN 
                        `holidays` AS `holidays` 
                        ON (`HolidayGroup`.`HOLIDAY_GROUP_ID` = `holidays`.`HOLIDAY_GROUP_ID`)' .
                        $str_conditions .
                        " AND holidays.HOLIDAYDATE BETWEEN '$from_date' AND '$to_date' AND holidays.status = 1"
                );

                //end 

                $arr_leavepolicy_name = $this->HolidayGroup->query(''
                    . 'SELECT '
                    . 'HOLIDAY_GROUP_NAME'
                    . ' FROM '
                    . '`holiday_group` AS `HolidayGroup` '
                    . $str_conditions);
                //            echo $str_conditions;
                //            debug($leavepolicygroupid);
                //            $arr_leavepolicy_employees = $this->HolidayGroup->query(''
                //                    . 'SELECT '
                //                    . '`EmployeeDetails`.first_name, `EmployeeDetails`.last_name, '
                //                    . '`EmployeeProfessionalDetails`.emp_company_id, `EmployeeProfessionalDetails`.designation, '
                //                    . '`Units`.branch_name '
                //                    . 'FROM '
                //                    . '`client_db1`.`leavepolicy_group` AS `LeavePolicyGroup` '
                //                    . 'LEFT JOIN `client_db1`.`emp_proff` AS `EmployeeProfessionalDetails` ON (`LeavePolicyGroup`.`LEAVEPOLICY_GROUP_ID` = `EmployeeProfessionalDetails`.`LEAVEPOLICY_GROUP_ID`) '
                //                    . 'LEFT JOIN `client_db1`.`emp_details` AS `EmployeeDetails` ON (`EmployeeProfessionalDetails`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`) '
                //                    . 'LEFT JOIN `client_db1`.`branches` AS `Units` ON (`EmployeeProfessionalDetails`.`emp_branch` = `Units`.`branch_code`)'.$str_conditions);
                ////            

                $arr_leavepolicydetails_for_template[] = array(
                    'summary' => $arr_leavepolicy_details,
                    'HoliDayName' => $arr_leavepolicy_name[0]['HolidayGroup']['HOLIDAY_GROUP_NAME']
                );
            }

            //       debug($arr_leavepolicydetails_for_template);
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
                    $view_output = $view->render('reportholidaypolicy');
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('L', 'A4', 'en');
                    $html2pdf->setTestTdInOnePage(false);
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('HolidayPolicyReport.pdf', 'D');

                    //$this->render('reportshiftpolicy');                
                    break;
                case 'excel':
                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_HolidayPolicyReport.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Holiday Policy Report");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                    for ($col = 'B'; $col !== 'J'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    $worksheet->mergeCells('A1:F1');
                    $worksheet->mergeCells('A2:F2');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $columncount = 0;
                    $rowcount = 2;
                    $i = 0;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $i += 1;
                        $Hname = 'Holiday Summary Of ' . $value['HoliDayName'];
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $Hname);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Day List');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Holiday');
                        //                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Date');
                        //                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Type');
                        //                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Description');
                        //                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Holiday');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Type');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Description');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $arr_data = $value['summary'];
                        //if ($arr_data[0]['holidays']['HOLIDAYID'] != null) { 
                        if (count($arr_data) > 0) {
                            $i = 1;
                            foreach ($arr_data as $val) {
                                $holidayname = $val['holidays']['HOLIDAYNAME'];
                                $date = $val['holidays']['HOLIDAYDATE'];
                                $type = $val['holidays']['HOLIDAYTYPE'];
                                $desc = $val['holidays']['DESCRIPTION'];
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $i);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, $holidayname);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, $date);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, $type);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, $desc);
                                $rowcount = $rowcount + 1;
                                $i++;
                            }
                        } else {
                            $msg = 'No holidays found under this policy';
                            $worksheet->mergeCells('A' . ($rowcount) . ':D' . ($rowcount));
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $msg);
                        }
                        $rowcount++;
                    }
                    $objPHPExcel->getActiveSheet()->setTitle('Holiday Policy Report');
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
                    $this->render('reportholidaypolicy');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }

    // Edited by Akshay on 11-3-2026
    private function generateSalaryStructureReport($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->SalaryStructures->useDbConfig = $this->Session->read('ds');

        $fields = 'SalaryStructures.*,ep.*,Info.*';

        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'ep',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('SalaryStructures.structure_id = ep.structure_id')
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('ep.emp_fkey = EmployeeDetails.emp_pkey')
            ),

            array(
                'table' => 'employee_info',
                'alias' => 'Info',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('ep.emp_fkey  = Info.emp_pkey')
            ),
        );

        $conditions = array(
            'ep.structure_id IS NOT' => null
        );

        $conditions1 = [];
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $conditions1[] = "EmployeeDetails.status IN ('1', '2')";
        } else {
            $conditions1[] = "EmployeeDetails.status = 1";
        }

        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $conditions1[] = "EmployeeDetails.branch_code = '$is_ho'";
            }
        }

        $conditions = $conditions1;

        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '') && (isset($arr_form_data['reportto']) && $arr_form_data['reportto'] != '')) {
            $conditions[] = 'EmployeeProfessionalDetails.joining_date BETWEEN "' . $arr_form_data['reportfrom'] . '" AND "' . $arr_form_data['reportto'] . '"';
        }
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("fields" => "reportcriteria,reportcriteria_field", "conditions" => array("status" => 1, 'reportcriteria' => $str_criteria_item))));
            if (isset($arr_reportcriterias[0]['reportcriteria_field'])) {
                if ($str_criteria_item != 'EmployeeProfessionalDetails') {
                    if (isset($arr_form_data[$str_criteria_item])) {
                        $conditions[] = $arr_reportcriterias[0]['reportcriteria'] . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                    }
                }
            }
        }




        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;
        }
        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            $arr_salary_structure = array(); //This is added by ***ARUL P DAS on 20/1/2020
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                $cur_emp_condition = "";
                //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $cur_emp_condition = " AND EmployeeDetails.branch_code='" . $cur_emp_branch . "'";
                }

                $structure_conditions = $conditions;


                $structure_conditions[] = 'SalaryStructures.structure_id = ' . $leavepolicygroupid . $cur_emp_condition;


                $arr_salary_structure_details = $this->SalaryStructures->find("all", array(
                    'fields' => $fields,
                    'joins' => $joins,
                    'conditions' => $structure_conditions,
                    'group' => array("EmployeeDetails.emp_pkey")
                ));


                $arr_salary_structures = $this->SalaryStructures->query('SELECT `SalaryStructures`.* FROM `salary_structure` AS `SalaryStructures` WHERE `SalaryStructures`.`structure_id` = ' . $leavepolicygroupid . ' and structure_active = 1');
                $arr_salary_structure[] = $arr_salary_structures;




                $arr_leavepolicydetails_for_template[] = array(
                    'summary' => $arr_salary_structure_details,
                    'policytitle' => isset($arr_salary_structure_details[0]['SalaryStructures']['structure_name']) ? $arr_salary_structure_details[0]['SalaryStructures']['structure_name'] : '',
                    'structure_id' => isset($arr_salary_structures[0]['SalaryStructures']['structure_id']) ? $arr_salary_structures[0]['SalaryStructures']['structure_id'] : ''
                );
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }

        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $this->set('arr_salary_structure', $arr_salary_structure);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportsalarystructure');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                //The below code is edited by ***ARUL P DAS on 20/1/2020
                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                $html2pdf->setTestTdInOnePage(false);

                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('SalaryStructureReport.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_SalaryStructureReports.xlsx" : "EmployeeInformation_" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("salary Structure Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Salary Structure Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'B'; $col !== 'I'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:H1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->mergeCells('A2:H2');
                // $worksheet->mergeCells('A3:H3');
                // $worksheet->mergeCells('A4:H4');
                // $worksheet->mergeCells('A5:H5');
                // $worksheet->mergeCells('A6:H6');
                // $worksheet->mergeCells('A7:H7');
                // $worksheet->mergeCells('A8:H8');
                // $worksheet->mergeCells('A9:H9');
                // $worksheet->mergeCells('A10:H10');
                // $worksheet->mergeCells('A11:H11');
                $i = 0;
                $columncount = 0;
                $rowcount = 2;

                $structure_iteration = 0;
                foreach ($arr_leavepolicydetails_for_template as $value) {
                    $i += 1;
                    $policyTitle = isset($arr_salary_structure[$structure_iteration]['0']['SalaryStructures']['structure_name']) ? $arr_salary_structure[$structure_iteration]['0']['SalaryStructures']['structure_name'] : '';
                    $min_gross = isset($arr_salary_structure[$structure_iteration]['0']['SalaryStructures']['structure_eg_amt']) ? $arr_salary_structure[$structure_iteration]['0']['SalaryStructures']['structure_eg_amt'] : '';

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Structure Summary of ' . $policyTitle);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setSize(14);
                    $worksheet->mergeCells('A' . ($rowcount + 1) . ':B' . ($rowcount + 1));
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, ($rowcount + 1), 'Policy Title  :' . $policyTitle);
                    $worksheet->mergeCells('A' . ($rowcount + 2) . ':B' . ($rowcount + 2));
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, ($rowcount + 2), 'Minimum Gross :' . $min_gross);


                    $rowcount = $rowcount + 3;

                    $columncount = 0;
                    $structure_iteration++;
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount + 1), 'Employee List');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount + 1))->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount + 1))->getFont()->setSize(14);
                    $rowcount = $rowcount + 2;

                    if ($value['policytitle'] != '') {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), 'Sl No');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Employee Name');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee ID');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Designation');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Department');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Branch');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Grade');
                        for ($i = 0; $i <= 7; $i++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setSize(12);
                        }
                        $rowcount = $rowcount + 1;
                        $i = 1;

                        foreach ($value['summary'] as $val) {
                            //edited by ASHIN on 08-11-24       
                            $empstatus = (isset($val['Info']['emp_status']) && $val['Info']['emp_status'] == "2") ? ' (Resigned)' : ''; // Check for resigned status
                            $name = $val['Info']['EmpName'] . $empstatus;
                            $id = $val['Info']['employee_id'];
                            $clas = $val['Info']['designation'];
                            $join = $val['Info']['joining_date'];
                            $dept = $val['Info']['department'];
                            $unit = $val['Info']['branch'];
                            $grade = $val['Info']['grade'];
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $i);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $name);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), $clas);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $unit);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), $grade);

                            $rowcount++;
                            $i++;
                        }
                    } else {
                        $msg = 'No employees found under this salary structure';
                        $worksheet->mergeCells('A' . $rowcount . ':H' . $rowcount);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $msg);
                        $rowcount++;
                    }

                    $rowcount++;
                }

                $objPHPExcel->getActiveSheet()->setTitle('Salary Structure Report');
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
                $this->render('reportsalarystructure');
                break;
        }
    }
    // End
}
