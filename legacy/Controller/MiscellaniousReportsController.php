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
class miscellaniousReportsController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'MiscellaniousReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Menu','LeaveRequests', 'Leavestatus', 'SalaryHeadItems', 'CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'DeviceAttendance', 'Departments', 'Grades', 'Verticals', 'Units', 'ReportCriterias', 'AttendanceRegister', 'AttendanceRegisterReport', 'CompanyContactInfo','LeaveType','ReportAudit'); //santhu
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
        //edited by athira on 04-02-2024
        $this->Menu->useDbConfig = $this->Session->read('ds');
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan',$plan);

        $company_code = strtoupper($this->Session->read('company_code'));
        if($company_code == 'HRBL'){
        $arr_reporttypes = array(
            'LeaveSummary' => 'Leave Detailed Reports',
            'LeaveBalance' => 'Leave Balance Report',
            'Compoff' => 'Leave Comp Off Report',
            // 'CompoffNew' => 'Leave Comp off Report New',
            'MonthlyLeave' => 'Leave Balance Monthly Statement'
        );  
        
        }else{
            if ($plan == 'basic') {
                $arr_reporttypes = array(
                    'LeaveSummary' => 'Leave Details Reports',
                    'LeaveBalance' => 'Leave Balance Report'
                );
            } else{
          $arr_reporttypes = array(
            // 'LeaveDetaillsReport' =>  'Leave Details Reports',
           //edited by athira on 08-10-2025
                'LeaveSummary' => 'Leave Details Reports',
                'LeaveBalance' => 'Leave Balance Report',
                //            'LeaveBalanceSummary' => 'Leave Taken Summary Report',
                'Compoff' => 'Comp Off Details Report',
                'MonthlyLeave' => 'Monthly Leave Taken Register',
                //end
        );
         }  
         // Add LOP report only for HDSN
        // if ($company_code == 'HDSC') {
        //     $arr_reporttypes['LOPReport'] = 'LOP Report';
        // }
       //edited by athira on 22-05-2026
        $restrictedCompanies = array(
        'KWMT','ABSG','MBCT','MRBS','STCL',
        'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
        );

        if (!in_array($company_code, $restrictedCompanies)) {
            $arr_reporttypes['LOPReport'] = 'LOP Report';
        }
        //ended by athira on 22-05-2026

        }
        
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
                case 'LeaveSummary':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'LOPReport':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'LeaveDetaillsReport':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'LeaveBalance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'LeaveBalanceSummary':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //santhu
                case 'Compoff':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'MonthlyLeave':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'CompoffNew':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                default :
                    echo "No criterias found";
                    break;
            }
            $company_code = strtoupper($this->Session->read('company_code'));
      $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
		    $this->render('showreportnew');
        }else{
            $this->render('showreport');
        }
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

    public function listcriteriaitems($str_criteria = '')
    {
        $this->autoRender = false;
        $model = $str_criteria;
        $conditions = array(); // Edited by Akshay on 28-1-2025
        $arr_requestdata = $this->request->data;
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');
            //commented by amal on 01/08/2019 leave type list assigned direct & indirect types 1
            //            if ($model == 'LeaveType') {
            //                $conditions = array("head_fkey" => 6, "value" => 'Y', "status" => 1);
            //            } else
            if ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            } elseif ($model == 'Leavestatus') {
                $conditions = array();
            } elseif ($model == 'Units') {
                $user_group = $this->Session->read('user_group');
                if ($user_group == 2) {
                    // Edited by Akshay on 28-1-2025
                    $user = $this->Session->read('company_code');
                    if ($user == 'GLET' || $user == 'ABSG') {
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
                        $cur_emp_key = $this->Session->read("emp_fkey");
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                        $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                        $conditions = array("Units.status" => 1, "branch_code" => $cur_emp_branch);
                    }
                    $arr_order = array("Units.branch_name" => "ASC");
                    // End
                } else {
                    $arr_order = array("Units.branch_name" => "ASC");
                    $conditions = array("Units.status" => 1);
                }
            } else {
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);
                } else {
                    $conditions = array("status" => 1);
                }
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
                    //commented by amal on 01/08/2019 leave type list assigned direct & indirect types 2
                    //                case 'LeaveType':
                    //                    foreach ($arr_criteriaItemsDB as $key => $value) {
                    //                        $arr_criteriaItems[$key]['key'] = $value['salary_head_item_pkey'];
                    //                        $arr_criteriaItems[$key]['text'] = $value['item'];
                    //                        $key++;
                    //                    }
                    //                    break;
                case 'LeaveType':
                    $fields = 'LeaveType.salary_head_item_pkey, LeaveType.head_fkey, LeaveType.item, LeaveType.item_type, LeaveType.item_value, LeaveType.occurance,
                                LeaveType.start_from, LeaveType.comments, LeaveType.value, LeaveType.is_show_salslip,LeaveType.item_part,LeaveType.status, 
                            LeaveType.salary_head_item_order1,LeavePolicy.salary_head_item_fkey,EmployeeLeaveUpload.leave_type';
                    $joins = array(
                        array(
                            'table' => 'leavepolicy',
                            'alias' => 'LeavePolicy',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => ('LeavePolicy.salary_head_item_fkey = LeaveType.salary_head_item_pkey')
                        ),
                        array(
                            'table' => 'emp_leave_upload',
                            'alias' => 'EmployeeLeaveUpload',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => ('EmployeeLeaveUpload.leave_type = LeaveType.salary_head_item_pkey')
                        )
                    );
                    $conditions = array("head_fkey" => 6, "value" => 'Y', "LeaveType.status" => 1);

                    $this->LeaveType->useDbConfig = $this->Session->read('ds');
                    $arr_leave = $this->LeaveType->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions,
                        'group' => 'LeaveType.item'
                    ));
                    foreach ($arr_leave as $value) {

                        $arr_criteriaItems[$key]['key'] = $value['LeaveType']['salary_head_item_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['LeaveType']['item'];
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
                        $conditions[] = array("status in(1,2)");
                    } else {

                        $conditions[] = array("status" => 1);
                    }

                    //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                    $user_group = $this->Session->read('user_group');
                    if ($user_group == 2) {
                        // Edited by Akshay on 27-1-2025
                        if ($user == 'GLET' || $user == 'ABSG') {
                            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                            $emp_pkey = $this->Session->read('emp_fkey');
                            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                            if ($is_ho != 1) {
                                $conditions[] = array("EmployeeDetails.branch_code" => $is_ho);
                            }
                        } else {
                            $cur_emp_key = $this->Session->read("emp_fkey");
                            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                            $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);
                        }
                        // End
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

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

       switch ($type) {
            //edited by athira on 08-10-2025
            case 'LeaveSummary':
                $dataForHistory['report_type'] = "Employees Leave Details Reports";
                break;
                //end
            case 'LOPReport':
                $dataForHistory['report_type'] = "Employees Leave Details Reports";
                break;
            case 'LeaveBalance':
                $dataForHistory['report_type'] = "Employees Leave Balance Report";
                break;
                //edited by athira on 08-10-2025
            case 'Compoff':
                $dataForHistory['report_type'] = "Comp Off Details Report";
                break;
            case 'MonthlyLeave':
                $dataForHistory['report_type'] = "Monthly Leave Taken Register";
                break;
                //end
            case 'LeaveLedger':
                $dataForHistory['report_type'] = "Leave Ledger";
                break;
            case 'CompoffNew':
                $dataForHistory['report_type'] = "Compensatory Off Report New";
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
        $company_code=$this->Session->read('company_code');
        $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
        switch ($type) {
            case 'employee':
                $this->generateemployeereport($mode);
                break;
            case 'LeaveSummary':
                if($company_code == 'DEMO' || $company_code=='SRTS' || $company_code=='GLET'){
                    $this->generatesummaryreportPSQUARE($mode);
                }
                else{
                  $this->generatesummaryreport($mode);
                }
                break;
            case 'LeaveBalance':
                if($company_code == 'DEMO' || $company_code=='SRTS' ){
                $this->generateleavebalancereportPSQUARE($mode);
                }else if (!in_array($company_code, $restricted_companies, true)) {
		          $this->generateleavebalancereportnew($mode);
                }else{
                    $this->generateleavebalancereport($mode);
                }
                break;
            case 'Compoff':
                 if( $company_code == 'DEMO'|| $company_code=='SRTS'){
                   $this->generatecompoffreportPSQUARE($mode);
                }else if (!in_array($company_code, $restricted_companies, true)) {
		         $this->generatecompoffreport_new($mode);
                }else{
                    $this->generatecompoffreport($mode);
                }
                break;
            case 'CompoffNew':
                $this->generatecompoffreportnew($mode);
                break;
            case 'MonthlyLeave':
                if( $company_code == 'DEMO'|| $company_code=='SRTS' ){
                    $this->generateleavebalancemonthlyreportPSQUARE($mode);
                 }else if (!in_array($company_code, $restricted_companies, true)) {
		         $this->generateleavebalancemonthlyreportnew($mode);
                }else{
                    $this->generateleavebalancemonthlyreport($mode);
                 }
                break;
            case 'LeaveBalanceSummary':
                $this->generateleavebalancesummaryreport($type, $mode);
                break;
            case 'LOPReport':
                $this->generatelopReport($mode);
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

    private function generateemployeereport($mode = '') {
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
            // debug($arr_reportfieldheadings);
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
                $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'), $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'), $arr_empinformation_fields->getFieldHeadings('Departments'), $arr_empinformation_fields->getFieldHeadings('Grades'), $arr_empinformation_fields->getFieldHeadings('Verticals'), $arr_empinformation_fields->getFieldHeadings('Units')
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

        switch ($mode) {
            case 'pdf' :
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportemployeeinformation');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('EmployeeInformation.pdf', 'D');
                //$this->render('reportemployeeinformation');
                break;
            case 'excel' :
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
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
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
            default :
                $this->set('mode', '');
                $this->render('reportemployeeinformation');
                break;
        }
    }
//leave deatiled report
     private function generatesummaryreport($mode)
    {
ini_set('memory_limit', '1024M');
        $this->autoRender = false;
$this->layout = '';

        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));
        //edited by athira on 10-07-2025
    $company_code = strtoupper($this->Session->read('company_code')); //Edited by Akshay on 14-5-2024
    $this->set('company_code',$company_code);
    // end

        $needBranchWiseReport = false;
        $conditions = array();

        $condition = ' where EmployeeDetails.status = 1 and ';
        //        debug($arr_form_data);
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition =  "where EmployeeDetails.status in('1','2') and ";
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
                $condition .=  " EmployeeDetails.branch_code = '$is_ho' and ";
            }
        }
        // End

//edited by athira on 02-10-2025
        // $conditions[] = 'FROMDATE >="' . $from . '" and TODATE<="' . $to . '"';
        $conditions[] = '(FROMDATE <= "' . $to . '" AND TODATE >= "' . $from . '")';
        //end

        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }


            try {
                $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array(
                    "fields" => "reportcriteria,reportcriteria_field",
                    "conditions" => array(
                        "reporttype" => "LeaveSummary",
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

        // try{
        //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
        $arr_empleaverequests = $this->LeaveRequests->query('SELECT (SELECT CONCAT(first_name," ",last_name) from emp_details '
            . 'where emp_pkey =  LeaveRequests.ISAutherizedby) as Authorized_name,(SELECT CONCAT(first_name," ",last_name) '
            . 'from emp_details where emp_pkey =  LeaveRequests.APPROVEDBY) as Approved_name,`termination`.`last_approved_working_date`,'
            . '`LeaveRequests`.`LEAVEENTRYID`,`LeaveRequests`.`Reason`,`LeaveRequests`.`contact_person`,`LeaveRequests`.`FROMHALF`,`LeaveRequests`.`TOHALF`,`LeaveRequests`.`leave_days`,'
            . '`LeaveRequests`.`REMARKS`,Units.branch_name,EmployeeDetails.branch_code,EmployeeDetails.status,CONCAT(first_name, " ", last_name) AS emp_name,Info.* ,'
            . '`LeaveType`.`item` AS `leave_type`,`LeaveRequests`.`applied_date`, `LeaveRequests`.`FROMDATE`, `LeaveRequests`.`TODATE`, '
            . '`LeaveRequests`.`LEAVESTATUS`,`LeaveRequests`.`AuthoriseRemarks`,`LeaveRequests`.`ApproveRemarks`,`LeaveRequests`.`Autherized_date`,`LeaveRequests`.`APPROVED_date`  FROM `leaveentries` AS `LeaveRequests`'
            //. ' LEFT JOIN `emp_leave_transactions` AS `EmpLeaveTransactions` ON (`LeaveRequests`.`LEAVEENTRYID` = `EmpLeaveTransactions`.`LEAVEENTRYID`)'
            . ' LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`LeaveRequests`.`EMP_fkey` = `EmployeeDetails`.`emp_pkey`)'
            . ' LEFT JOIN `salary_head_items` AS `LeaveType` ON (`LeaveRequests`.`salary_head_item_fkey` = `LeaveType`.`salary_head_item_pkey`) '
            . ' LEFT JOIN `branches` AS `Units` ON (`EmployeeDetails`.`branch_code` = `Units`.`branch_code`) '
            . ' LEFT JOIN `leavestatus` AS `Leavestatus` ON (`LeaveRequests`.`LEAVESTATUS` = `Leavestatus`.`LEAVESTATUS`) '
            . 'LEFT JOIN `employee_info` AS `Info` ON (`EmployeeDetails`.`emp_pkey` = `Info`.`emp_pkey`)'
            . 'LEFT JOIN `termination` AS `termination` ON (`termination`.`emp_fkey` = `Info`.`emp_pkey` and `termination`.`status` = 1)'
            . $condition . $str_conditions . 'ORDER BY EmployeeDetails.emp_pkey desc ');
        //  debug($arr_empleaverequests);
        //        } catch (Exception $ex) {
        //
        //        }

        $pkey = isset($arr_empleaverequests['0']['Info']['emp_pkey']) ? $arr_empleaverequests['0']['Info']['emp_pkey'] : 0;

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
                    $request['status'] = (isset($leaverequest['EmployeeDetails']['status'])) && $leaverequest['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : '';
                    $request['emp_name'] = isset($leaverequest['Info']['EmpName']) ? $leaverequest['Info']['EmpName'] : '';
                    $request['employee_id'] = isset($leaverequest['Info']['employee_id']) ? $leaverequest['Info']['employee_id'] : '';
                    $request['branch'] = isset($leaverequest['Info']['branch']) ? $leaverequest['Info']['branch'] : '';
                    $request['designation'] = isset($leaverequest['Info']['designation']) ? $leaverequest['Info']['designation'] : '';
                    $request['department'] = isset($leaverequest['Info']['department']) ? $leaverequest['Info']['department'] : '';
                    $request['joining_date'] = isset($leaverequest['Info']['joining_date']) ? $leaverequest['Info']['joining_date'] : '';
                    $request['leave_type'] = isset($leaverequest['LeaveType']['leave_type']) ? $leaverequest['LeaveType']['leave_type'] : '';
                    //edited by megha on 11/10/2019 admin added in null condition
                    $request['Authorized_name'] = isset($leaverequest['0']['Authorized_name']) ? $leaverequest['0']['Authorized_name'] : '';
                    $request['Approved_name'] = isset($leaverequest['0']['Approved_name']) ? $leaverequest['0']['Approved_name'] : '';
                    $request['leave_status'] = isset($leaverequest['LeaveRequests']['LEAVESTATUS']) ? $leaverequest['LeaveRequests']['LEAVESTATUS'] : '';
                    $request['leave_from'] = isset($leaverequest['LeaveRequests']['FROMDATE']) ? $leaverequest['LeaveRequests']['FROMDATE'] : '';
                    $request['leave_to'] = isset($leaverequest['LeaveRequests']['TODATE']) ? $leaverequest['LeaveRequests']['TODATE'] : '';
                    $request['leave_applied_on'] = isset($leaverequest['LeaveRequests']['applied_date']) ? $leaverequest['LeaveRequests']['applied_date'] : '';
                    $request['fromhalf'] = isset($leaverequest['LeaveRequests']['FROMHALF']) ? $leaverequest['LeaveRequests']['FROMHALF'] : '';
                    $request['tohalf'] = isset($leaverequest['LeaveRequests']['TOHALF']) ? $leaverequest['LeaveRequests']['TOHALF'] : '';
                    $request['leavedays'] = isset($leaverequest['LeaveRequests']['leave_days']) ? $leaverequest['LeaveRequests']['leave_days'] : '';
                    //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
                    $request['Authorized_remarks'] = isset($leaverequest['LeaveRequests']['AuthoriseRemarks']) ? $leaverequest['LeaveRequests']['AuthoriseRemarks'] : '';
                    $request['Approved_remarks'] = isset($leaverequest['LeaveRequests']['ApproveRemarks']) ? $leaverequest['LeaveRequests']['ApproveRemarks'] : '';
                    $request['Autherized_date'] = isset($leaverequest['LeaveRequests']['Autherized_date']) ? $leaverequest['LeaveRequests']['Autherized_date'] : '';
                    $request['APPROVED_date'] = isset($leaverequest['LeaveRequests']['APPROVED_date']) ? $leaverequest['LeaveRequests']['APPROVED_date'] : '';
                    //end remarks
                    //Reson and contact person added by megha on 26/09/2019
                    $request['Reason'] = isset($leaverequest['LeaveRequests']['Reason']) ? $leaverequest['LeaveRequests']['Reason'] : '';
                    $request['contact_person'] = isset($leaverequest['LeaveRequests']['contact_person']) ? $leaverequest['LeaveRequests']['contact_person'] : '';

                    //added by megha userid on 27/08/2019
                    $pkey = isset($leaverequest['Info']['emp_pkey']) ? $leaverequest['Info']['emp_pkey'] : 0;
                    $userid = $this->LeaveRequests->query("select user_id from user_credentials where user_credentials.emp_fkey = $pkey");
                    $request['userid'] = $userid['0']['user_credentials']['user_id'];
                    //end userid
                    $arr_leavesummary_for_template[$branch_code]['leaverequests'][] = $request;
                }
                //debug($request);
            }
        } else {
            //Parse array for simple report
            $arr_leavesummary_for_template['leaverequests'] = array();
            foreach ($arr_empleaverequests as $leaverequest) {
                $request = array();
                $request['termination'] = isset($leaverequest['termination']['last_approved_working_date']) ? $leaverequest['termination']['last_approved_working_date'] : '';
                $request['status'] = (isset($leaverequest['EmployeeDetails']['status'])) && $leaverequest['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : '';
                $request['emp_name'] = isset($leaverequest['Info']['EmpName']) ? $leaverequest['Info']['EmpName'] : '';
                $request['employee_id'] = isset($leaverequest['Info']['employee_id']) ? $leaverequest['Info']['employee_id'] : '';
                $request['branch'] = isset($leaverequest['Info']['branch']) ? $leaverequest['Info']['branch'] : '';
                $request['designation'] = isset($leaverequest['Info']['designation']) ? $leaverequest['Info']['designation'] : '';
                $request['department'] = isset($leaverequest['Info']['department']) ? $leaverequest['Info']['department'] : '';
                $request['joining_date'] = isset($leaverequest['Info']['joining_date']) ? $leaverequest['Info']['joining_date'] : '';
                $request['leave_type'] = isset($leaverequest['LeaveType']['leave_type']) ? $leaverequest['LeaveType']['leave_type'] : '';
                $request['Authorized_name'] = isset($leaverequest['0']['Authorized_name']) ? $leaverequest['0']['Authorized_name'] : '';
                $request['Approved_name'] = isset($leaverequest['0']['Approved_name']) ? $leaverequest['0']['Approved_name'] : '';
                $request['leave_status'] = isset($leaverequest['LeaveRequests']['LEAVESTATUS']) ? $leaverequest['LeaveRequests']['LEAVESTATUS'] : '';
                $request['leave_from'] = isset($leaverequest['LeaveRequests']['FROMDATE']) ? $leaverequest['LeaveRequests']['FROMDATE'] : '';
                $request['leave_to'] = isset($leaverequest['LeaveRequests']['TODATE']) ? $leaverequest['LeaveRequests']['TODATE'] : '';
                $request['leave_applied_on'] = isset($leaverequest['LeaveRequests']['applied_date']) ? $leaverequest['LeaveRequests']['applied_date'] : '';
                $request['fromhalf'] = isset($leaverequest['LeaveRequests']['FROMHALF']) ? $leaverequest['LeaveRequests']['FROMHALF'] : '';
                $request['tohalf'] = isset($leaverequest['LeaveRequests']['TOHALF']) ? $leaverequest['LeaveRequests']['TOHALF'] : '';
                $request['leavedays'] = isset($leaverequest['LeaveRequests']['leave_days']) ? $leaverequest['LeaveRequests']['leave_days'] : '';
                //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
                $request['Authorized_remarks'] = isset($leaverequest['LeaveRequests']['AuthoriseRemarks']) ? $leaverequest['LeaveRequests']['AuthoriseRemarks'] : '';
                $request['Approved_remarks'] = isset($leaverequest['LeaveRequests']['ApproveRemarks']) ? $leaverequest['LeaveRequests']['ApproveRemarks'] : '';
                $request['Autherized_date'] = isset($leaverequest['LeaveRequests']['Autherized_date']) ? $leaverequest['LeaveRequests']['Autherized_date'] : '';
                $request['APPROVED_date'] = isset($leaverequest['LeaveRequests']['APPROVED_date']) ? $leaverequest['LeaveRequests']['APPROVED_date'] : '';
                //end remarks
                //Reson and contact person added by megha on 26/09/2019
                $request['Reason'] = isset($leaverequest['LeaveRequests']['Reason']) ? $leaverequest['LeaveRequests']['Reason'] : '';
                $request['contact_person'] = isset($leaverequest['LeaveRequests']['contact_person']) ? $leaverequest['LeaveRequests']['contact_person'] : '';
                //added by megha userid on 27/08/2019
                $pkey = isset($leaverequest['Info']['emp_pkey']) ? $leaverequest['Info']['emp_pkey'] : 0;
                $userid = $this->LeaveRequests->query("select user_id from user_credentials where user_credentials.emp_fkey = $pkey");
                $request['userid'] = isset($userid['0']['user_credentials']['user_id']) ? $userid['0']['user_credentials']['user_id'] : '';
                //end userid
                $arr_leavesummary_for_template['leaverequests'][] = $request;
            }
        } //debug($request);

        $this->set('needBranchWiseReport', $needBranchWiseReport);
        $this->set('arr_leavesummary_for_template', $arr_leavesummary_for_template);

        $dates = $from . ' - ' . $to;

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
            case 'pdf':
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
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Leave Detailed Report.xlsx" : "Leave Detailed Report" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Detailed Reports  " . $dates);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $columncount = 0;
                $rowcount = 2;


                //edited by athira on 17-06-2025
                if (!empty($arr_leavesummary_for_template)) {
                    
                    if (isset($needBranchWiseReport) && $needBranchWiseReport == true) {
                        foreach ($arr_leavesummary_for_template as $branch_code => $leavesummary) {



                            $branchname = $leavesummary['branch_name'] . ' Branch';
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $branchname);
                            $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowcount . ':J' . $rowcount);
                            $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $rowcount = $rowcount + 2;


                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Sl No . ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, 'Employee Name');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee Id');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'User Id');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 3, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 6, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 7, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 8, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Applied Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 9, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'From Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 10, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'To Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 11, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            //edited by megha reason and contact person
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Reason');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 12, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Contact Person');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 13, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            //end
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'Authorized By');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 14, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, 'Authorized Person Remarks');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 15, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, 'Authorized Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 16), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 16, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, 'Approved By');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 17), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 17, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 18), $rowcount, 'Approved Person Remarks');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 18), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 18, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 19), $rowcount, 'Approved Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 19), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 19, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 20), $rowcount, 'Rejected By');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 20), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 20, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 21), $rowcount, 'Rejected Person Remarks');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 21), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 21, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 22), $rowcount, 'Rejected Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 22), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 22, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            //end remarks
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 23), $rowcount, 'Leave Type');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 23), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 23, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 24), $rowcount, 'Leave Days');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 24), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 24, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 25), $rowcount, 'Leave Status ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 25), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 25, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');

                            $rowcount = $rowcount + 1;
                            $arr_data = $leavesummary['leaverequests'];
                            if (count($arr_data) >= 0) {
                                $i = 1;
                                foreach ($arr_data as $val) {
                                    $empstatus = $val['status'];
                                    $name = $val['emp_name'] . $empstatus;
                                    $applieddate = $val['leave_applied_on'];
                                    $frm = ($val['fromhalf'] == '1') ? $val['leave_from'] . ' ' . 'First Half' : $val['leave_from'] . ' ' . 'Second Half';
                                    $to = ($val['tohalf'] == '1') ? $val['leave_to'] . ' ' . 'First Half' : $val['leave_to'] . ' ' . 'Second Half';
                                    $status = $val['leave_status'];
                                    $type = $val['leave_type'];
                                    $id = $val['employee_id'];
                                    $clas = $val['designation'];
                                    $authorized = $val['Authorized_name'];
                                    $approved = $val['Approved_name'];
                                    //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
                                    $authorizedremarks = $val['Authorized_remarks'];
                                    $approvedremarks = $val['Approved_remarks'];
                                    $authorizeddate = $val['Autherized_date'];
                                    $approveddate = $val['APPROVED_date'];
                                    //end remarks
                                    $join = $val['joining_date'];
                                    $dept = $val['department'];
                                    $unit = $val['branch'];
                                    $leave_days = $val['leavedays'];
                                    $termination = $val['termination'];
                                    $reason = $val['Reason'];
                                    $contact_person = $val['contact_person'];
                                    //added by megha userid on 27/08/2019
                                    //$userid = $arr_userid['0']['user_credentials']['user_id'];
                                    $userid = $val['userid'];
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $i++);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $name);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $id);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $userid);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $join);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $unit);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $dept);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $clas);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), ($rowcount), $termination);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, $applieddate);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, $frm);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, $to);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, $reason);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, $contact_person);
                                    //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019

                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, $authorized);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, $authorizedremarks);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, $authorizeddate);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, $approved);
                                    if ($val['leave_status'] != 'Rejected') {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 18), $rowcount, $approvedremarks);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 19), $rowcount, $approveddate);
                                    } else {
                                        //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, '');
                                        //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, '');
                                        //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, '');
                                        //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, '');
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 18), $rowcount, '');
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 19), $rowcount, '');
                                    }
                                    if ($val['leave_status'] == 'Rejected') {
                                        if ($val['APPROVED_date'] == '') {
                                            $rejected = $val['Authorized_name'];
                                            $rejectedremark = $val['Authorized_remarks'];
                                            $rejectedate = $val['Autherized_date'];
                                        } else {
                                            $rejected = $val['Approved_name'];
                                            $rejectedremark = $val['Approved_remarks'];
                                            $rejectedate = $val['APPROVED_date'];
                                        }
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 20), $rowcount, $rejected);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 21), $rowcount, $rejectedremark);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 22), $rowcount, $rejectedate);
                                    } else {
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 20), $rowcount, '');
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 21), $rowcount, '');
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 22), $rowcount, '');
                                    }
                                    //end remarks
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 23), $rowcount, $type);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 24), $rowcount, $leave_days);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 25), $rowcount, $status);
                                    $rowcount = $rowcount + 1;
                                }
                            } else {
                                $msg = 'No Report found under this';
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $msg);
                            }
                            $rowcount++;
                        }
                    } else {

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Sl No . ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee Id');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'User Id');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 3, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 6, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 7, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 8, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Applied Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 9, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'From Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 10, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'To Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 11, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        //edited by megha reason and contact person
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Reason');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 12, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Contact Person');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 13, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        //end
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'Authorized By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 14, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, 'Authorized Person Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 15, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, 'Authorized Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 16), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 16, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, 'Approved By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 17), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 17, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 18), $rowcount, 'Approved Person Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 18), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 18, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 19), $rowcount, 'Approved Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 19), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 19, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 20), $rowcount, 'Rejected By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 20), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 20, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 21), $rowcount, 'Rejected Person Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 21), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 21, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 22), $rowcount, 'Rejected Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 22), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 22, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        //end remarks
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 23), $rowcount, 'Leave Type');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 23), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 23, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 24), $rowcount, 'Leave Days');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 24), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 24, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 25), $rowcount, 'Leave Status ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 25), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 25, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');

                        $rowcount = $rowcount + 1;


                        $arr_data = $arr_leavesummary_for_template['leaverequests'];
                        if (count($arr_data) >= 0) {
                            $i = 1;
                            foreach ($arr_data as $val) {

                                $empstatus = $val['status'];
                                $name = $val['emp_name'] . $empstatus;
                                $applieddate = $val['leave_applied_on'];
                                $frm = ($val['fromhalf'] == '1') ? $val['leave_from'] . ' ' . 'First Half' : $val['leave_from'] . ' ' . 'Second Half';
                                $to = ($val['tohalf'] == '1') ? $val['leave_to'] . ' ' . 'First Half' : $val['leave_to'] . ' ' . 'Second Half';
                                $status = $val['leave_status'];
                                $type = $val['leave_type'];
                                $id = $val['employee_id'];
                                $clas = $val['designation'];
                                $authorized = $val['Authorized_name'];
                                $approved = $val['Approved_name'];
                                //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
                                $authorizedremarks = $val['Authorized_remarks'];
                                $approvedremarks = $val['Approved_remarks'];
                                $authorizeddate = $val['Autherized_date'];
                                $approveddate = $val['APPROVED_date'];
                                //end remarks
                                $join = $val['joining_date'];
                                $dept = $val['department'];
                                $unit = $val['branch'];
                                $leave_days = $val['leavedays'];
                                $termination = $val['termination'];
                                $reason = $val['Reason'];
                                $contact_person = $val['contact_person'];
                                //added by megha userid on 27/08/2019
                                //$userid = $arr_userid['0']['user_credentials']['user_id'];
                                $userid = $val['userid'];
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $i++);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $id);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $userid);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $join);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $unit);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $clas);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $dept);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), ($rowcount), $termination);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, $applieddate);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, $frm);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, $to);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, $reason);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, $contact_person);
                                //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019

                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, $authorized);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, $authorizedremarks);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, $authorizeddate);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, $approved);
                                if ($val['leave_status'] != 'Rejected') {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 18), $rowcount, $approvedremarks);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 19), $rowcount, $approveddate);
                                } else {
                                    //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, '');
                                    //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, '');
                                    //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, '');
                                    //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, '');
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 18), $rowcount, '');
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 19), $rowcount, '');
                                }
                                if ($val['leave_status'] == 'Rejected') {
                                    if ($val['APPROVED_date'] == '') {
                                        $rejected = $val['Authorized_name'];
                                        $rejectedremark = $val['Authorized_remarks'];
                                        $rejectedate = $val['Autherized_date'];
                                    } else {
                                        $rejected = $val['Approved_name'];
                                        $rejectedremark = $val['Approved_remarks'];
                                        $rejectedate = $val['APPROVED_date'];
                                    }
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 20), $rowcount, $rejected);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 21), $rowcount, $rejectedremark);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 22), $rowcount, $rejectedate);
                                } else {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 20), $rowcount, '');
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 21), $rowcount, '');
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 22), $rowcount, '');
                                }
                                //end remarks
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 23), $rowcount, $type);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 24), $rowcount, $leave_days);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 25), $rowcount, $status);
                                $rowcount = $rowcount + 1;
                            }
                        } else {








                            $msg = 'No Report found under this';
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $msg);
                        }
                        //edited by athira on 17-06-2025
                    }
                } else {
                    $rowcount = 2; // set row just after the title
                    $worksheet->setCellValue('A' . $rowcount, 'No data available under the selected criteria.');
                    $worksheet->mergeCells("A{$rowcount}:J{$rowcount}");
                    $worksheet->getStyle("A{$rowcount}")->getFont()->setBold(true)->setSize(14);
                }
                //end
                $objPHPExcel->getActiveSheet()->setTitle('Leave Detailed Report');
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
                $this->render('reportleavesummary');
                break;
        }
    }
 //leave balance report
    private function generateleavebalancereport($mode) {

        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        //edited by athira on 10-07-2025
        $company_code = strtoupper($this->Session->read('company_code')); //Edited by Akshay on 14-5-2024
        $this->set('company_code',$company_code);
        // end
        //Build conditions based on criterias recieved
        //$fd=$arr_form_data['reportfrom'].' '.'00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);

        $report_month = $arr_form_data['reportfrom'];
        if ($arr_form_data['reportfrom'])
            $from = date('Y', strtotime($arr_form_data['reportfrom']));

        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
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

        $condition = 'and ed.status = 1';
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and ed.status in('1','2')";
        }

        $emp_branch_condition = "";
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        /* $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $emp_branch_condition = " and ed.branch_code ='" . $cur_emp_branch . "' ";
        } */



        //employee branch wise sorting ends here

        $arr_leavepolicydetails_for_template = array();
        $arr_empleaverequests2 = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $arr_leavepolicydetails_for_template2 = array();
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $year_con = "EmployeeDetails.emp_pkey = '$leavepolicygroupid' ";
                }
                if ($arr_form_data['select-criteria1'] == 'Units') {
                    $year_con = "EmployeeDetails.branch_code = '$leavepolicygroupid' ";
                }
                if ($arr_form_data['select-criteria1'] == 'LeaveType') {
//                    $year_con = "EmployeeDetails.branch_code = EmployeeDetails.branch_code";
				if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                        $emp_status_condition=" and ed.status in (1,2)";
                    }else{
                        $emp_status_condition=" and ed.status=1";
                    }
                    $leave_type_branch_find = $this->EmployeeDetails->query("select distinct emp_branch from emp_proff ep 
join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID=ep.LEAVEPOLICY_GROUP_ID) 
join salary_head_items shi on (shi.salary_head_item_pkey=lp.salary_head_item_fkey) 
join branches br on (ep.emp_branch=br.branch_code)
join emp_details ed on (ed.emp_pkey=ep.emp_fkey)
where shi.salary_head_item_pkey=" . $leavepolicygroupid . " and br.status=1 ".$emp_status_condition);

                    $branch_array = array();
                    foreach ($leave_type_branch_find as $val) {
						
                        $branch = $val['ep']['emp_branch'];
                        $fin = $this->LeaveRequests->query("select start_month,end_month,fin_year,Year_status from fin_year where branch_code = '$branch' and vattr1 ='0' and fin_year='" . $from . "' and status=1");
                        $end_month = '';
                        $cur_year = '';			   
                        if (!empty($fin)) {
                            if (count($fin) > 1) {
                                foreach ($fin as $fin_val) {
                                    if ($fin_val['fin_year']['Year_status'] == 'OPEN') {
                                        $start_month = $fin_val['fin_year']['start_month'];
                                        $end_month = $fin_val['fin_year']['end_month'];
                                        $fin_year = $fin_val['fin_year']['fin_year'];
                                    }
                                }
                            } else {
                                $start_month = $fin[0]['fin_year']['start_month'];
                                $end_month = $fin[0]['fin_year']['end_month'];
                                $fin_year = $fin[0]['fin_year']['fin_year'];
                            }
                            $branch_array[] = array('branch' => $branch, 'start_month' => $start_month, 'end_month' => $end_month, 'fin_year' => $fin_year);
                        }
                    }
                } else {
                    if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array($year_con, "status in (1,2)")));
                    } else {
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array($year_con, "status" => 1)));
                    }
                    $emp_branch = isset($cur_emp_branch_find[0]['EmployeeDetails']['branch_code']) ? $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'] : '';
                    $fin = $this->LeaveRequests->query("select start_month,end_month,fin_year,Year_status from fin_year where branch_code = '$emp_branch' and vattr1 ='0' and fin_year='" . $from . "' and status=1");

                    $start_month='';
                    $end_month='';
                    $cur_year='';
                    if (!empty($fin)) {
                        if (count($fin) > 1) {
                            foreach ($fin as $fin_val) {
                                if ($fin_val['fin_year']['Year_status'] == 'OPEN') {
                                    $start_month = $fin_val['fin_year']['start_month'];
                                    $end_month = $fin_val['fin_year']['end_month'];
                                    $cur_year = $fin_val['fin_year']['fin_year'];
                                }
                            }
                        } else {
                            $start_month = $fin['0']['fin_year']['start_month'];
                            $end_month = $fin['0']['fin_year']['end_month'];
                            $cur_year = $fin['0']['fin_year']['fin_year'];
                        }
                    }
                }


                $arr_empleaverequests = array();
                $arr_empleaverequests_leave = array();
                $arr_empleaverequests_leave_resign = array();
                if ($arr_form_data['select-criteria1'] == 'LeaveType') {

                    try {
                        $just_test = array();
                        foreach ($branch_array as $branch) {
                            $cur_branch = $branch['branch'];
                            $start_month = $branch['start_month'];
                            $end_month = $branch['end_month'];
                            $cur_year = $branch['fin_year'];
                            $temp1[] = $this->LeaveRequests->query("(SELECT distinct ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                            CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                            ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                            leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken,
                            (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                            from emp_details ed
                            join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                            join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                            LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                            left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                            and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                            join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                            join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                            left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                            left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                            where ed.emp_pkey=ed.emp_pkey
                            and ed.status='1' " . $emp_branch_condition . "
                            and ed.branch_code='" . $cur_branch . "'
                            and lp.salary_head_item_fkey=" . $leavepolicygroupid . " and ep.joining_date <= '$end_month' and finyear.fin_year = '$cur_year' and finyear.vattr1=0 and finyear.status=1 group by ed.emp_pkey,LeaveType.item order by ed.branch_code)");
                        }
                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            foreach ($branch_array as $branch) {
                                $cur_branch = $branch['branch'];
                                $start_month = $branch['start_month'];
                                $end_month = $branch['end_month'];
                                $cur_year = $branch['fin_year'];
                                $temp2[] = $this->LeaveRequests->query("(SELECT distinct ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                                CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                                ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                                leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken, 
                                (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave,
								(select count(*) as count from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) where remarks = 'terminate' and salary_paid = 'Y' and leave_encashment_master.status = 1 and emp_fkey = ed.emp_pkey) as terminate,
								(select leavepolicy.leave_encash_limit from emp_details join emp_proff ep on (emp_details.emp_pkey=ep.emp_fkey)
                                join leavepolicy on (leavepolicy.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID ) 
						        left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) 
					            where leavepolicy.status = 1 and is_leave_encash ='Y' and leave_encash_limit is NOT NULL and leavepolicy.salary_head_item_fkey='$leavepolicygroupid' and ed.emp_pkey = emp_details.emp_pkey) as leave_encash_limit
                                from emp_details ed
                                join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                                join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                                LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                                left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                                and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                                join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                                join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                                left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                                left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                                where ed.emp_pkey=ed.emp_pkey
                                and ed.status='2' " . $emp_branch_condition . " and termination.last_approved_working_date >= '$start_month'
                                and ed.branch_code='" . $cur_branch . "'
                                and lp.salary_head_item_fkey=" . $leavepolicygroupid . " and ep.joining_date <= '$end_month' and finyear.fin_year = '$cur_year' and finyear.vattr1=0 and finyear.status=1 group by ed.emp_pkey,LeaveType.item order by ed.branch_code)");
                            }
                        }
                    } catch (Exception $ex) { 
                    }
                    $arr_leavepolicydetails_for_template2 = $arr_empleaverequests2;
                } else if ($arr_form_data['select-criteria1'] == 'Departments') {
                    try {
                        $arr_empleaverequests_dept = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                        CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                        ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                        leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken,
                        (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
						from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                        and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                        where ed.emp_pkey=ed.emp_pkey
                        and ed.status='1' " . $emp_branch_condition . "
                        and ed.branch_code=ed.branch_code
                        and lp.salary_head_item_fkey=" . $leavepolicygroupid . " and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.status=1 group by LeaveType.item)");

                        $arr_empleaverequests = $arr_empleaverequests_dept;

                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            $arr_empleaverequests_dept_resign = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                            CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                            ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                            leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken,
                            (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                            from emp_details ed
                            join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                            join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                            LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                            left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                            and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                            join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                            join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                            left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                            left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                            where ed.emp_pkey=ed.emp_pkey
                            and ed.status='2' " . $emp_branch_condition . " and termination.last_approved_working_date >= '$start_month'
                            and ed.branch_code=ed.branch_code
                            and lp.salary_head_item_fkey=" . $leavepolicygroupid . " and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.status=1 group by LeaveType.item)");
                            $arr_empleaverequests = array_merge($arr_empleaverequests_dept, $arr_empleaverequests_dept_resign);
                        }
                    } catch (Exception $ex) {
                        
                    }
                } else if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

                    try {

                        $arr_empleaverequests_emp = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                        CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                        ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                        leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken, 
                        (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                        from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                        and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                        where ed.emp_pkey=" . $leavepolicygroupid . "
                        and ed.status='1' " . $emp_branch_condition . "
                        and ed.branch_code=ed.branch_code
                        and lp.salary_head_item_fkey=lp.salary_head_item_fkey and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.vattr1=0 and finyear.status=1 group by LeaveType.item)");

                        $arr_empleaverequests = $arr_empleaverequests_emp;
                   
                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            $arr_empleaverequests_emp_resign = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                            CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                            ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                            leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken, 
                            (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave,
						    (select count(*) as count from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) where remarks = 'terminate' and salary_paid = 'Y' and leave_encashment_master.status = 1 and emp_fkey = '$leavepolicygroupid') as terminate,
							(select leavepolicy.leave_encash_limit from leavepolicy left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) left join emp_details on (emp_details.emp_pkey = '$leavepolicygroupid') where leavepolicy.status = 1  and LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID  from emp_proff where emp_fkey = '$leavepolicygroupid') and is_leave_encash ='Y' and leave_encash_limit is NOT NULL and leavepolicy.salary_head_item_fkey=lp.salary_head_item_fkey) as leave_encash_limit 
                            from emp_details ed
                            join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                            join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                            LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                            left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                            and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                            join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                            join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                            left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                            left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                            where ed.emp_pkey=" . $leavepolicygroupid . "
                            and ed.status='2' " . $emp_branch_condition . " and termination.last_approved_working_date >= '$start_month'
                            and ed.branch_code=ed.branch_code
                            and lp.salary_head_item_fkey=lp.salary_head_item_fkey and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.vattr1=0 and finyear.status=1 group by LeaveType.item)");

                            $arr_empleaverequests = array_merge($arr_empleaverequests_emp, $arr_empleaverequests_emp_resign);
                        }
                   } catch (Exception $ex) {
                   
                  }
                } else {
                    try {
                        $arr_empleaverequests_branch = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                        CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                        ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                        leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken,
                        (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                        from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                        and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                        where ed.emp_pkey=ed.emp_pkey 
                        and ed.status='1' " . $emp_branch_condition . "
                        and ed.branch_code='" . $leavepolicygroupid . "'
                        and lp.salary_head_item_fkey=lp.salary_head_item_fkey and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.vattr1=0 and finyear.status=1 group by ed.emp_pkey,LeaveType.item)");
                        if (!empty($arr_empleaverequests_branch)) {
                            $arr_empleaverequests = $arr_empleaverequests_branch;
                        }
						
                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            $arr_empleaverequests_branch_resign = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                            CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                            ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                            leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken,
                            (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave,
							(select count(*) as count from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) where remarks = 'terminate' and salary_paid = 'Y' and leave_encashment_master.status = 1 and emp_fkey = ed.emp_pkey) as terminate,
							(select leavepolicy.leave_encash_limit from emp_details join emp_proff ep on (emp_details.emp_pkey=ep.emp_fkey)
                            join leavepolicy on (leavepolicy.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID ) 
						    left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) 
					        where leavepolicy.status = 1 and is_leave_encash ='Y' and leave_encash_limit is NOT NULL and  ed.emp_pkey = emp_details.emp_pkey and salary_head_items.salary_head_item_pkey = lp.salary_head_item_fkey) as leave_encash_limit
                            from emp_details ed
                            join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                            join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                            LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                            left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                            and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                            join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                            join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                            left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                            left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                            where ed.emp_pkey=ed.emp_pkey 
                            and ed.status='2' " . $emp_branch_condition . " and termination.last_approved_working_date >= '$start_month'
                            and ed.branch_code='" . $leavepolicygroupid . "'
                            and lp.salary_head_item_fkey=lp.salary_head_item_fkey and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.vattr1=0 and finyear.status=1 group by ed.emp_pkey,LeaveType.item)");
                              if (!empty($arr_empleaverequests_branch_resign)) {
                                $arr_empleaverequests = array_merge($arr_empleaverequests_branch, $arr_empleaverequests_branch_resign);
                            }
                        }
						
                     } catch (Exception $ex) {
                        
                    } 
                }
                if (!empty($arr_empleaverequests)) {//LeaveType criteria will not enter here. By ***ARUL P DAS on 21_3_2020
                    $arr_leavepolicydetails_for_template[] = array(
                        'summary' => $arr_empleaverequests
                            // 'summary' => isset($arr_empleaverequests)?$arr_empleaverequests:array(),
                    );
                }
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }

        if ($arr_form_data['select-criteria1'] == 'LeaveType') {//This leave type has separate variable. by ***ARUL P DAS on 21_3_2020
//            $arr_leavepolicydetails_for_template = $arr_leavepolicydetails_for_template2; //The $arr_leavepolicydetails_for_template2 is calculated in LeaveType section.
            if (isset($temp1)) {
                foreach ($temp1 as $val) {
                    if (!empty($val)) {
//                debug($val[0]['LeaveType']['leave_type']);
                        $leave[$val[0]['LeaveType']['leave_type']]['summary'][] = $val;
                    }
                }
            }
            if (isset($temp2)) {
                foreach ($temp2 as $val) {
                    if (!empty($val)) {
//                debug($val[0]['LeaveType']['leave_type']);
                        $leave[$val[0]['LeaveType']['leave_type']]['summary'][] = $val;
                    }
                }
            }
            if (isset($leave)) {
                $arr_leavepolicydetails_for_template = $leave;
            }
        }
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
       // $this->set('cur_year', $cur_year);

        $this->set('from', $from);


        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('dates', $from);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf' :
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportleavebalance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavebalance.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_LeaveBalanceReport.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Balance Report - " . $from);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:O1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:O2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 3;
                if (count($arr_leavepolicydetails_for_template) != 0) {
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $i = 0;
                        $arr_data = $value['summary'];
                        if (count($arr_data) > 0) {
                            $i += 1;
                            $le = 'Leave Balance Reports of ';
                            if ($cr == 'Departments') {
                                $dep = isset($value['summary']['0']['Departments']['dept_name']) ? $value['summary'][0]['Departments']['dept_name'] : '';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $dep);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                                $worksheet->mergeCells('A' . $rowcount . ':O' . $rowcount);
                                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                );
                            } else if ($cr == 'Units') {
                                $brn = isset($value['summary'][0]['Units']['branch_name']) ? $value['summary'][0]['Units']['branch_name'] : '';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $brn);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                                $worksheet->mergeCells('A' . $rowcount . ':O' . $rowcount);
                                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                );
                            } else if ($cr == 'EmployeeDetails') {
                                $empstatus = isset($value['summary'][0]['ed']['status']) && $value['summary'][0]['ed']['status'] == "2" ? '(Resigned)' : '';
                                $name = isset($value['summary'][0]['0']['emp_name']) ? $value['summary'][0]['0']['emp_name'] : '';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $name . $empstatus);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                                $worksheet->mergeCells('A' . $rowcount . ':O' . $rowcount);
                                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                );
                            } else {
                                $type = isset($value['summary'][0][0]['LeaveType']['leave_type']) ? $value['summary'][0][0]['LeaveType']['leave_type'] : '';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $type);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                                $worksheet->mergeCells('A' . $rowcount . ':O' . $rowcount);
                                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                );
                            }

                            $rowcount = $rowcount + 1;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee ID');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
//                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Joining Date');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Date Of Joining');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 3, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Designation');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 6, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Termination Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 7, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Leave Type');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
							$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Leave Policy');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Allotted Leave For The year');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Carry Forwarded');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Leave Taken');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Encashed Leaves');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'Leave Balance');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 12, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $rowcount = $rowcount + 1;
                            foreach ($arr_data as $val) {
                                if ($cr != "LeaveType") {
                                    $leavetaken = $val['0']['leavetaken'];
                                    $empstatus = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : '';
                                    $empname = isset($val['0']['emp_name']) ? $val['0']['emp_name'] : '';
                                    $emp = $empname . $empstatus;
                                    $typ = isset($val['LeaveType']['leave_type']) ? $val['LeaveType']['leave_type'] : '';
                                    
                                    //$levbalnce = isset($val['0']['leavebalance']) ? round($val['0']['leavebalance'], 1) : 0;
                                    $id = isset($val['info']['employee_id']) ? $val['info']['employee_id'] : '';
                                    $clas = isset($val['info']['designation']) ? $val['info']['designation'] : '';
                                    $join = isset($val['info']['joining_date']) ? $val['info']['joining_date'] : '';
                                    $dept = isset($val['info']['department']) ? $val['info']['department'] : '';
                                    $unit = isset($val['info']['branch']) ? $val['info']['branch'] : '';
                                    $carry = isset($val['0']['carryforwarded']) ? $val['0']['carryforwarded'] : 0;
                                    //$encashed_leaves = isset($val['0']['encashed_leave'])?$val['0']['encashed_leave']:'0';
                                    $termination = isset($val['termination']['last_approved_working_date']) ? $val['termination']['last_approved_working_date'] : '';
									if($val['lp']['leave_policy_type'] =='Y'){
										$type = 'Yearly';
									}else if($val['lp']['leave_policy_type'] =='M'){
										$type = 'Monthly';
									}else{
										$type = 'Present Days';
									}
									if($val['lp']['leave_policy_type'] == 'P'){ 
									$lp = '0'; 
									}else{ 
									$lp = isset($val['lp']['alloted_leave_forthe_year']) ? round($val['lp']['alloted_leave_forthe_year'], 1) : 0; 
									} 
									$terminate = isset($val['0']['terminate'])?$val['0']['terminate']:0;
									$limit = isset($val['0']['leave_encash_limit'])?$val['0']['leave_encash_limit']:0;
									$limit = $encashed_leaves = isset($val['0']['encashed_leave'])?$val['0']['encashed_leave']:'0'; 
//									if($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0 ){ 
//									$encashed_leaves = $limit - $val['0']['leavetaken'];
//									}else{
//									$encashed_leaves = isset($val['0']['encashed_leave'])?$val['0']['encashed_leave']:'0'; 
//									}
									if($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0 ){ 
									//$levbalnce ='0';
									}else{
									$levbalnce =isset($val['0']['leavebalance']) ? round($val['0']['leavebalance'], 1) : 0; 
									}
                                    $columncount = 0;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i++);
                                    //edited by sinsiya 05-04-2024
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $emp);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $id);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $join);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $unit);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $clas);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $dept);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $termination);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $typ);
									$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $type);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $lp);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $carry);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $leavetaken);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $encashed_leaves);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $levbalnce);
                                    $rowcount ++;
                                } else {
                                    foreach ($val as $subval) {
                                        $leavetaken = $subval['0']['leavetaken'];
                                        $empstatus = isset($subval['ed']['status']) && $subval['ed']['status'] == "2" ? '(Resigned)' : '';
                                        $empname = isset($subval['0']['emp_name']) ? $subval['0']['emp_name'] : '';
                                        $emp = $empname . $empstatus;
                                        $typ = isset($subval['LeaveType']['leave_type']) ? $subval['LeaveType']['leave_type'] : '';
                                        
                                        //$levbalnce = isset($subval['0']['leavebalance']) ? round($subval['0']['leavebalance'], 1) : 0;
                                        $id = isset($subval['info']['employee_id']) ? $subval['info']['employee_id'] : '';
                                        $clas = isset($subval['info']['designation']) ? $subval['info']['designation'] : '';
                                        $join = isset($subval['info']['joining_date']) ? $subval['info']['joining_date'] : '';
                                        $dept = isset($subval['info']['department']) ? $subval['info']['department'] : '';
                                        $unit = isset($subval['info']['branch']) ? $subval['info']['branch'] : '';
                                        //$encashed_leaves = isset($subval['0']['encashed_leave'])?$subval['0']['encashed_leave']:'0';
                                        $carry = isset($subval['0']['carryforwarded']) ? $subval['0']['carryforwarded'] : 0;
                                        $termination = isset($subval['termination']['last_approved_working_date']) ? $subval['termination']['last_approved_working_date'] : '';
										if($subval['lp']['leave_policy_type'] =='Y'){
																$type = 'Yearly';
															}else if($subval['lp']['leave_policy_type'] =='M'){
																$type = 'Monthly';
															}else{
																$type = 'Present Days';
															}
									if($subval['lp']['leave_policy_type'] == 'P'){ 
									$lp = '0'; 
									}else{ 
									$lp = isset($subval['lp']['alloted_leave_forthe_year']) ? round($subval['lp']['alloted_leave_forthe_year'], 1) : 0; 
									} 
									$terminate = isset($subval['0']['terminate'])?$subval['0']['terminate']:0;
									$limit = isset($subval['0']['leave_encash_limit'])?$subval['0']['leave_encash_limit']:0;
                                                                        $limit = $encashed_leaves = isset($subval['0']['encashed_leave'])?$subval['0']['encashed_leave']:0; 
//									if($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0 ){ 
//									$encashed_leaves = $limit - $subval['0']['leavetaken'];
//									}else{
//									$encashed_leaves = isset($subval['0']['encashed_leave'])?$subval['0']['encashed_leave']:'0'; 
//									}
									if($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0 ){ 
									//$levbalnce ='0';
									}else{
									$levbalnce =isset($subval['0']['leavebalance']) ? round($subval['0']['leavebalance'], 1) : 0; 
									}
                                        $columncount = 0;
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i++);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $emp);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $id);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $join);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $unit);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $clas);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $dept);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $termination);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $typ);
										$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $type);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $lp);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $carry);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $leavetaken);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $encashed_leaves);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $levbalnce);
                                        $rowcount ++;
                                    }
                                }
                            }
                            $rowcount ++;
                        }
                    }
                } else {
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No Data Available With The Selected Criteria");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                }
                $objPHPExcel->getActiveSheet()->setTitle('Leave Balance Report');
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
                $this->render('reportleavebalance');
                break;
        }
    }

    private function generateleavebalancesummaryreport($mode) {
        $arr_form_data = $_REQUEST;
        //   debug($mode);
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        //  debug($to);
        // debug($from);
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            //    debug($arr_leavepolicygroupids);
        }
        $arr_leavepolicydetails_for_template = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                if ($arr_form_data['select-criteria1'] == 'SalaryHeadItems') {
                    $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,ed.branch_code, CONCAT(first_name, " ", last_name)'
                            . ' AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,leave_balance_inthe_year_fn(ed.emp_pkey,'
                            . "lp.salary_head_item_fkey, '$from') leavebalance from"
                            . 'emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID)
join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
join employee_info as info on (info.emp_pkey=ep.emp_fkey)
where ed.emp_pkey=ed.emp_pkey
and ed.branch_code=ed.branch_code
and lp.salary_head_item_fkey=' . $leavepolicygroupid . '');
                } else if ($arr_form_data['select-criteria1'] == 'Departments') {
                    $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,ed.branch_code, CONCAT(first_name, " ", last_name)'
                            . " AS emp_name, info.*, LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '$from') leavebalance from emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)"
                            . 'join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID)
join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
join employee_info as info on (info.emp_pkey=ed.emp_pkey)
where ed.emp_pkey=ed.emp_pkey
and ed.branch_code=ed.branch_code
and ed.status = 1
and lp.salary_head_item_fkey=' . $leavepolicygroupid . '');
                } else if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,ed.branch_code, CONCAT(first_name, " ", last_name)'
                            . " AS emp_name, info.*, LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '$from') leavebalance from emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)"
                            . 'join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID)
join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
join employee_info as info on (info.emp_pkey=ed.emp_pkey)
where ed.emp_pkey=' . $leavepolicygroupid . '
and ed.branch_code=ed.branch_code
and ed.status = 1
and lp.salary_head_item_fkey=lp.salary_head_item_fkey');
                } else {
                    $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,Units.branch_name,ed.branch_code, CONCAT(first_name, " ", last_name)'
                            . " AS emp_name,  info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '$from') leavebalance from emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)"
                            . 'join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID)
LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
join employee_info as info on (info.emp_pkey=ed.emp_pkey)
where ed.emp_pkey=ed.emp_pkey
and ed.status = 1
and ed.branch_code="' . $leavepolicygroupid . '"
and lp.salary_head_item_fkey=lp.salary_head_item_fkey');
                }







                //debug($arr_empleaverequests);

                $arr_leavepolicydetails_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_empleaverequests,
                        // 'employees'=>$arr_leavepolicy_employees
                );
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
//  debug($arr_leavepolicydetails_for_template);
        foreach ($arr_leavepolicydetails_for_template as $key => $value) {
           
        }
        // debug($resp_register);
        // debug($arr_leavepolicydetails_for_template);  
// debug($arr_leavepolicydetails_for_template);
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);

        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf' :
                
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportleavebalance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavetakenummary.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_LeaveTaken.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Taken Report");
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
                $i = 0;
                foreach ($arr_leavepolicydetails_for_template as $value) {
                    $i += 1;
                    $le = 'Leave Balance Reports of ';
                    if ($cr == 'Departments') {
                        $dep = isset($value['summary']['0']['Departments']['dept_name']) ? $value['summary'][0]['Departments']['dept_name'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $dep);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    } else if ($cr == 'Units') {
                        $brn = isset($value['summary'][0]['Units']['branch_name']) ? $value['summary'][0]['Units']['branch_name'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $brn);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    } else if ($cr == 'EmployeeDetails') {
                        $name = isset($value['summary'][0]['0']['emp_name']) ? $value['summary'][0]['0']['emp_name'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $name);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    } else {
                        $type = isset($value['summary'][0]['LeaveType']['leave_type']) ? $value['summary'][0]['LeaveType']['leave_type'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $type);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    }

                    $rowcount = $rowcount + 1;
                    $columncount = 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Name');
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
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'leave Type');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Allotted leave For The year');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Leave Taken');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Leave Balance');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                    $rowcount = $rowcount + 1;
                    $arr_data = $value['summary'];
                    if (count($arr_data) > 0) {
                        foreach ($arr_data as $val) {
                            $leavetaken = $val['lp']['alloted_leave_forthe_year'] - $val['0']['leavebalance'];
                            $emp = $val['0']['emp_name'];
                            $typ = $val['LeaveType']['leave_type'];
                            $lp = $val['lp']['alloted_leave_forthe_year'];
                            $levbalnce = $val['0']['leavebalance'];
                            $id = $val['info']['employee_id'];
                            $clas = $val['info']['designation'];
                            $join = $val['info']['joining_date'];
                            $dept = $val['info']['department'];
                            $unit = $val['info']['branch'];
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $emp);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $unit);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $clas);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $typ);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $lp);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $leavetaken);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $levbalnce);
                            $rowcount ++;
                        }
                    } else {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No record Found');
                    }
                    $rowcount ++;
                }
                $objPHPExcel->getActiveSheet()->setTitle('Leave Taken');
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
                $this->render('reportleavebalancesummary');
                break;
        }
    }

    private function generatecompoffreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $to = date("Y-m-d", strtotime(date("Y-m-d", strtotime($from)) . " + 1 year"));
        //edited by athira on 10-07-2025
        $company_code = strtoupper($this->Session->read('company_code')); //Edited by Akshay on 14-5-2024
        $this->set('company_code',$company_code);
        // end
        
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
        try{
            $salary_head_pkey = $this->LeaveRequests->query("select salary_head_item_pkey from salary_head_items where occurance = 'COFF' ");
            $salary_head_pkey = isset($salary_head_pkey['0']['salary_head_items']['salary_head_item_pkey'])?$salary_head_pkey['0']['salary_head_items']['salary_head_item_pkey']:0 ;
            
            
        } catch (Exception $ex) {

        } 
        
        
        
        
        $arr_leavepolicydetails_for_template = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    
                    
//                    $arr_empleaverequests = $this->LeaveRequests->query("select employee_info.*,emp_name,month_year,count(*) as compoff
//                        from attendance_register,fin_year fy left join employee_info on(employee_info.emp_pkey = '$leavepolicygroupid')
//                         where (FIELD1='COFF' OR FIELD2='COFF' OR FIELD3='COFF' OR FIELD4='COFF' 
//                         OR FIELD5='COFF' OR FIELD6='COFF' OR FIELD7='COFF' OR FIELD8='COFF' OR 
//                         FIELD9='COFF' OR FIELD10='COFF' OR FIELD11='COFF' OR FIELD12='COFF' OR 
//                         FIELD13='COFF' OR FIELD14='COFF' OR FIELD15='COFF' OR FIELD16='COFF' OR 
//                         FIELD17='COFF' OR FIELD18='COFF' OR FIELD19='COFF' OR FIELD20='COFF' OR 
//                         FIELD21='COFF' OR FIELD22='COFF' OR FIELD23='COFF' OR FIELD24='COFF' OR 
//                         FIELD25='COFF' OR FIELD26='COFF' OR FIELD27='COFF' OR FIELD28='COFF' OR 
//                         FIELD29='COFF' OR FIELD30='COFF' OR FIELD31='COFF' OR FIELD32='COFF')
//                        and isdelete='N' and concat(month_year,'-01') between fy.start_month and 
//                        end_month and fy.Year_status='OPEN' and fy.vattr1 = 0 and attendance_register.emp_fkey = '$leavepolicygroupid' group by emp_name,month_year");
//
//                    $arr_empleave_eligiility = $this->LeaveRequests->query("select emp_pkey ,yearmonth ,count(*) as eligibility from
//                        emp_detail_timeattandance, fin_year fy where (weekoff is not null or holiday is not null) and present='P/P'
//                        and yearmonth between fy.start_month and end_month and fy.Year_status='OPEN' and fy.vattr1 = 0 and emp_pkey = '$leavepolicygroupid'
//                         group by emp_pkey ,yearmonth 
//                        union all
//                        select emp_pkey ,yearmonth ,count(*)*.5 from
//                        emp_detail_timeattandance , fin_year fy where (weekoff is not null or holiday is not null) and present in('P/A','A/P')
//                        and yearmonth between fy.start_month and end_month and fy.Year_status='OPEN' and fy.vattr1 = 0 and emp_pkey = '$leavepolicygroupid'
//                        group by emp_pkey ,yearmonth");
                    
                    /* @var $arr_empleave_eligiility type */
                    $arr_emps_dets = $this->LeaveRequests->query("SELECT employee_info.*
FROM `employee_info`
WHERE `emp_pkey` = '$leavepolicygroupid' limit 50
 ");
                    $arr_emp_status = $this->LeaveRequests->query("select  emp_details.status from emp_details where emp_pkey =$leavepolicygroupid ");
                  
                     // $empid = $arr_leavepolicydetails_for_template['0']['emp_dets']['0']['employee_info']['emp_pkey'];
         $arr_termin = $this->LeaveRequests->query(" select last_approved_working_date from termination where termination.emp_fkey = $leavepolicygroupid");
          //debug($arr_termin);
                    try{
                     //$arr_empleaverequests = $this->LeaveRequests->query("select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and (weekoff != '' or holiday != '') and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '2' ))");
		$arr_empleaverequests = $this->LeaveRequests->query("select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and (weekoff != '' or holiday != '') and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '2' )) union select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and  holiday != '' and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '4' ))");  
                    } catch (Exception $ex) {

                    }
                    
                    $year = date("Y",  strtotime($from));
                    try{
                        $arr_empleave_eligiility = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$leavepolicygroupid','$salary_head_pkey','$year') as blnce ");
                        
                    } catch (Exception $ex) {

                    }
                    
                    
                } else {
                    $arr_empleaverequests = $this->LeaveRequests->query("select * from leaveentries where salary_head_item_fkey = $salary_head_pkey and EMP_fkey = '$leavepolicygroupid' " );
                }
                
                
                
                $arr_empleavetaken = $this->LeaveRequests->query("select LEAVEENTRYID,ed.status,salary_head_item_fkey,applied_date,LEAVESTATUS,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,concat(ed.first_name,' ',ed.last_name) applied_name,Autherized_date,APPROVED_date,contact_person,contact_No,Reason,REMARKS,leave_days,(select concat(first_name,' ',last_name) from emp_details where emp_pkey = leaveentries.ISAutherizedby) as Authorized_name,(SELECT CONCAT(first_name,'',last_name) from emp_details where emp_pkey = leaveentries.APPROVEDBY) approved_name from leaveentries

left join emp_details ed on (ed.emp_pkey = leaveentries.EMP_fkey)
where leaveentries.salary_head_item_fkey = '$salary_head_pkey' and EMP_fkey = '$leavepolicygroupid' and TODATE between '$from' and '$to'  " ); 
               
             //   debug($arr_empleavetaken);
             //   $arr_termination = $this->LeaveRequests->query("select last_approved_working_date from termination where termination.emp_fkey = 
                $arr_leavepolicydetails_for_template[] = array(
                    'emp_dets'=>$arr_emps_dets,
                    'summary' => $arr_empleaverequests,
                    'eligibility' => $arr_empleave_eligiility,
                    'leaves' => $arr_empleavetaken,
                    'termin' => $arr_termin,
                    'status' => $arr_emp_status
                );
            }
        } else {
            echo "<div style='color:red' ><h3>No record Found</h3></div>";
            die();
        }
        
       // debug($arr_leavepolicydetails_for_template);
       
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $this->set('arr_termin', $arr_termin);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);

        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf' :
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('compoff');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavecompoffssummary.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_LeaveTaken.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Comp Off Report ");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:K1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $rowcount = 3;
                $i = 0;
                
                foreach ($arr_leavepolicydetails_for_template as $value) {
                    if (!empty($value['summary'])) {
                        $i += 1;
                        $le = 'Leave Comp Off Report of ';
                        $empstatus = isset($value['status']['0']['emp_details']['status']) && $value['status']['0']['emp_details']['status']=="2" ? '(Resigned)':'';
                        $dep = isset($value['emp_dets']['0']['employee_info']['EmpName']) ? $value['emp_dets']['0']['employee_info']['EmpName'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $dep.$empstatus);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        
                        $worksheet->mergeCells('A'.$rowcount.':K'.$rowcount);
                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                        
                        $arr_data = $value['summary'];
                        $emp_dets = $value['emp_dets'];
                        
                        $columncount = 0;
                        $rowcount++;
                        $rowcount++;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Details ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                        $worksheet->mergeCells('A'.$rowcount.':D'.$rowcount);
                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        
                        $rowcount = $rowcount + 2;
                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $arr_data = $value['summary'];
                            $empstatus = isset($value['status']['0']['emp_details']['status']) && $value['status']['0']['emp_details']['status']=="2" ? '(Resigned)':'';
                            $emp = $emp_dets['0']['employee_info']['EmpName'].$empstatus;
                            $id = $emp_dets['0']['employee_info']['employee_id'];
                            $clas = $emp_dets['0']['employee_info']['designation'];
                            $join = $emp_dets['0']['employee_info']['joining_date'];
                            $dept = $emp_dets['0']['employee_info']['department'];
                            $unit = $emp_dets['0']['employee_info']['branch'];
                            $termination = isset ($value['termin']['0']['termination']['last_approved_working_date']) ? $value['termin']['0']['termination']['last_approved_working_date'] :''; 
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $emp);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $unit);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $clas);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $termination);
                            $rowcount ++;
                            
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Compensatory Accrued Details ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                            $worksheet->mergeCells('A'.$rowcount.':D'.$rowcount);
                            $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                        
                            $rowcount = $rowcount + 2;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No.  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Accrued Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Duration');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Day Type');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                            $rowcount = $rowcount + 1;   
                            
                            
                            
                        if (count($arr_data) > 0) {
                            $used = 0;
                            foreach ($arr_data as $val) {
                                $used = $used + 1;
                                $att_date = $val['0']['att_date'];
                                $duration = $val['0']['duration'];
                                $dys = $val['0']['weekoff'] . ' ' . $val['0']['holiday'];
                                
                                $columncount = 0;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $used);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $att_date);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $duration);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $dys);
                                $rowcount ++;
                            }
                            
                            $balance_levv = isset($value['eligibility']['0']['0']['blnce']) ? $value['eligibility']['0']['0']['blnce'] : 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 0), $rowcount, 'Available Comp Off Leave Balance : '.$balance_levv);
                            
                            $worksheet->mergeCells('A'.$rowcount.':K'.$rowcount);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 0), $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            
                        } else {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Record Found');
                        }
                        
                        $arr_data = $value['leaves'];
                
                        
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Comp Off Leave List ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                            $worksheet->mergeCells('A'.$rowcount.':D'.$rowcount);
                            $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                            
                            
                            $rowcount = $rowcount + 1;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No.  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'From Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'To Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Status');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                            $rowcount = $rowcount + 2;   
                            
                        if (count($arr_data) > 0) {
                            $used = 0;
                            foreach ($arr_data as $val) {
                                $used = $used + 1;
                                $att_date = $val['leaveentries']['FROMDATE'] . ' ' . $val['leaveentries']['FROMHALF'];
                                $duration = $val['leaveentries']['TODATE'] . ' ' . $val['leaveentries']['TOHALF'];
                                $dys = $val['leaveentries']['LEAVESTATUS'];
                                
                                $columncount = 0;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $used);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $att_date);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $duration);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $dys);
                                $rowcount ++;
                            }
                            
                            $balance_levv = isset($value['eligibility']['0']['0']['blnce']) ? $value['eligibility']['0']['0']['blnce'] : 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 0), $rowcount, 'Available Comp off Leave Balance ');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, $balance_levv );
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                            $rowcount ++;
                        } else {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Record Found');
                        }
                        
                        $rowcount ++;
                        $rowcount ++;
                    }
                }

                
                
                $objPHPExcel->getActiveSheet()->setTitle('Leave Compoff Report ');
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
                $this->render('compoff');
                break;
        }
    }
        
//Leave Balance Monthly Statement
private function generateleavebalancemonthlyreport($mode) {

        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        //edited by athira on 10-07-2025
        $company_code = strtoupper($this->Session->read('company_code')); //Edited by Akshay on 14-5-2024
        $this->set('company_code',$company_code);
        // end
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $report_month = $arr_form_data['reportfrom'];
        if ($arr_form_data['reportfrom']){
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        $frm = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $year1 = date('Y', strtotime($arr_form_data['reportfrom']));
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
        }
        $condition = 'and ed.status = 1';

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and ed.status in('1','2')";
        }

        $arr_leavepolicydetails_for_template = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
         
                $arr_empleaverequests = array();
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
 
                  try {
                        $arr_leaveheads   = $this->LeaveRequests->query("select salary_head_item_fkey from leavepolicy lp left join emp_proff on 
                        (lp.LEAVEPOLICY_GROUP_ID= emp_proff.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        join salary_head_items on (salary_head_items.salary_head_item_pkey=lp.salary_head_item_fkey)
                        where emp_fkey= $leavepolicygroupid and occurance !='LOP' and salary_head_items.status =1");
                        $arr_finyear  = $this->LeaveRequests->query("SELECT fin_year from fin_year where start_month <= '$from' and 
                            end_month >= '$from'  limit 1 ");    
                        $finyear = isset($arr_finyear['0']['fin_year']['fin_year'])?$arr_finyear['0']['fin_year']['fin_year']:$year1;
                       
                        foreach ($arr_leaveheads as $lhead) {
                        $head = isset($lhead['lp']['salary_head_item_fkey'])?$lhead['lp']['salary_head_item_fkey']:'';
                        $arr_empleaverequests_emp = $this->LeaveRequests->query("SELECT ar.month_year,user_credentials.user_id,LeaveType.salary_head_item_pkey, ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,info.EmpName,
                        CONCAT(ed.first_name, ' ', ed.last_name) AS emp_name, info.*,LeaveType.item AS leave_type,
                        leave_balance_inthe_month_fn('$leavepolicygroupid','$head','$to','$finyear') as monthlybalance
                        from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        left join user_credentials on (user_credentials.emp_fkey = ed.emp_pkey)
                        left join attendance_register as ar on(ar.emp_fkey = ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                        where ed.emp_pkey=" . $leavepolicygroupid . "
                        and ed.status='1' and lp.salary_head_item_fkey=$head and ar.month_year = '$frm' group by info.emp_id");
                  
                        $arr_empleaverequests[] = $arr_empleaverequests_emp;

                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                        $arr_empleaverequests_emp_resign = $this->LeaveRequests->query("SELECT ar.month_year,user_credentials.user_id,LeaveType.salary_head_item_pkey, ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,info.EmpName,
                        CONCAT(ed.first_name, ' ', ed.last_name) AS emp_name, info.*,LeaveType.item AS leave_type,
                        leave_balance_inthe_month_fn('$leavepolicygroupid','$head','$to','$finyear') as monthlybalance
                        from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        left join user_credentials on (user_credentials.emp_fkey = ed.emp_pkey)
                        left join attendance_register as ar on(ar.emp_fkey = ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                        where ed.emp_pkey=" . $leavepolicygroupid . "
                        and ed.status='2' and lp.salary_head_item_fkey=$head and ar.month_year = '$frm' group by info.emp_id");

                        $arr_empleaverequests[] = array_merge($arr_empleaverequests_emp, $arr_empleaverequests_emp_resign);
                        }
                        if (!empty($arr_empleaverequests[0])) {
                        $arr_leavepolicydetails_for_template[$leavepolicygroupid] = array(
                        'summary' => $arr_empleaverequests
                        );
                    }
                        }
                   } catch (Exception $ex) {
                   
                  }
                } 
                
            }
        } 
 

        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        //debug($arr_leavepolicydetails_for_template);exit();
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        //$this->set('cur_year', $cur_year);
        $this->set('from', $from);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time=strtotime($f);
        $month=date("m",$time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month.'-01'; 
        $year=date("Y",$time);
        $this->set('mname1', $mname);
        $this->set('y1', $year);
        $this->set('month2', $month);
        $this->set('month1', $month1);
        $all_leaveheads   = $this->LeaveRequests->query("select distinct salary_head_item_pkey,item from salary_head_items  
            left join leavepolicy on (leavepolicy.salary_head_item_fkey = salary_head_items.salary_head_item_pkey) 
            left join leavepolicy_group on (leavepolicy_group.LEAVEPOLICY_GROUP_ID = leavepolicy.LEAVEPOLICY_GROUP_ID) 
            where item_type='LEAVE' and item_part='Direct' and occurance !='LOP' and salary_head_items.status =1
            and leavepolicy_group.status =1 and leavepolicy.status = 1");
        $this->set('all_leaveheads', $all_leaveheads);
        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('dates', $from);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf' :
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('leavebalance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavebalance.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "LeaveBalanceStatement.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Balance Statement - " . $mname . " " . $year1);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:M1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:M2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                            $rowcount = 3;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
//                        
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
//                       
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
//                        
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
//                        
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Date of Joining');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
//                        
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
//                       
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
//                        
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Termination Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);

                            $col = 9;
                             foreach ($all_leaveheads as $heads) { 
                                 $head = isset($heads['salary_head_items']['item'])?$heads['salary_head_items']['item']:'';
                                                   

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $head);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($col),$rowcount)->getFont()->setBold(true);
                            $col++;

                        }
                            $rowcount = $rowcount + 1;


                            if (count($arr_leavepolicydetails_for_template) != 0) {
                                 $i = 1;
                        foreach ($arr_leavepolicydetails_for_template as $value) {
  
                        $arr_data = $value['summary'];

     
                        if (count($arr_data) > 0) {
                          
                              $val = $arr_data[0][0];
                                if ($cr != "LeaveType") {
                                    //$leavetaken = $val['0']['leavetaken'];
                                 
                    $id = $val['info']['employee_id']; 
                    $userid =  isset($val['user_credentials']['user_id']) ?  $val['user_credentials']['user_id'] : '';
                    $emp_name = $val['0']['emp_name'];  
                    $emp_status = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : ''; 
                    $emp = $emp_name.$emp_status; 
                    $join = $val['info']['joining_date']; 
                    $branch = $val['info']['branch'];
                    $dept = $val['info']['department']; 
                    $desig = $val['info']['designation']; 
                    $termination =isset($val['termination']['last_approved_working_date']) ? $val['termination']['last_approved_working_date'] : ''; 

                   
                                    $columncount = 0;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i++);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $userid);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $emp);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 3) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $join);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $branch);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $dept);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $desig);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 7) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $termination);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


                        $temp = 9;
 foreach ($all_leaveheads as $heads) { 
                            $head_pkey = isset($heads['salary_head_items']['salary_head_item_pkey'])?$heads['salary_head_items']['salary_head_item_pkey']:'';
                            $leave_bal = 0;
                            foreach ($arr_data as $val) {
                               $key = $val[0]['LeaveType']['salary_head_item_pkey'];
                               if($head_pkey == $key){ 
                                   $leave_bal = $val[0]['0']['monthlybalance'];
                               }
                            }
   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($temp) .$rowcount, $leave_bal);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($temp) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $temp++;

                                } 
                            $rowcount++;
                 

if (!function_exists('numberToColumn')) {
    // Define the numberToColumn function
    function numberToColumn($num) {
        $str = '';
        while ($num > 0) {
            $mod = ($num - 1) % 26;
            $str = chr(65 + $mod) . $str;
            $num = floor(($num - $mod) / 26);
        }
        return $str;
    }
}


$number = $temp;
 // Increment the value

// Convert the incremented value to its alphabetic column reference
$column = numberToColumn($number);

// Define the border style
$BStyle = array(
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    )
);

$row = $rowcount - 1;

// Use the dynamic column reference  range
$range = 'A1:' . $column . $row;

// Apply the border style to the specified range
$objPHPExcel->getActiveSheet()->getStyle($range)->applyFromArray($BStyle);

// Rest of your code
// ...


                        
                   }
                }}
                 }
                 else {
                $worksheet->mergeCells('A3:S3');
                $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                );
                $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                }
                $objPHPExcel->getActiveSheet()->setShowGridlines(false); 
                $objPHPExcel->getActiveSheet()->setTitle('Leave Balance Statement');
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
                $this->render('leavebalance');
                break;
        }
    }
     private function generatecompoffreportnew($mode) {
        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $to = date("Y-m-d", strtotime(date("Y-m-d", strtotime($from)) . " + 1 year"));
        
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
        try{
            $salary_head_pkey = $this->LeaveRequests->query("select salary_head_item_pkey from salary_head_items where occurance = 'COFF' ");
            $salary_head_pkey = isset($salary_head_pkey['0']['salary_head_items']['salary_head_item_pkey'])?$salary_head_pkey['0']['salary_head_items']['salary_head_item_pkey']:0 ;
            
            
        } catch (Exception $ex) {

        } 
        
        
        
        
        $arr_leavepolicydetails_for_template = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    
                    
//                    $arr_empleaverequests = $this->LeaveRequests->query("select employee_info.*,emp_name,month_year,count(*) as compoff
//                        from attendance_register,fin_year fy left join employee_info on(employee_info.emp_pkey = '$leavepolicygroupid')
//                         where (FIELD1='COFF' OR FIELD2='COFF' OR FIELD3='COFF' OR FIELD4='COFF' 
//                         OR FIELD5='COFF' OR FIELD6='COFF' OR FIELD7='COFF' OR FIELD8='COFF' OR 
//                         FIELD9='COFF' OR FIELD10='COFF' OR FIELD11='COFF' OR FIELD12='COFF' OR 
//                         FIELD13='COFF' OR FIELD14='COFF' OR FIELD15='COFF' OR FIELD16='COFF' OR 
//                         FIELD17='COFF' OR FIELD18='COFF' OR FIELD19='COFF' OR FIELD20='COFF' OR 
//                         FIELD21='COFF' OR FIELD22='COFF' OR FIELD23='COFF' OR FIELD24='COFF' OR 
//                         FIELD25='COFF' OR FIELD26='COFF' OR FIELD27='COFF' OR FIELD28='COFF' OR 
//                         FIELD29='COFF' OR FIELD30='COFF' OR FIELD31='COFF' OR FIELD32='COFF')
//                        and isdelete='N' and concat(month_year,'-01') between fy.start_month and 
//                        end_month and fy.Year_status='OPEN' and fy.vattr1 = 0 and attendance_register.emp_fkey = '$leavepolicygroupid' group by emp_name,month_year");
//
//                    $arr_empleave_eligiility = $this->LeaveRequests->query("select emp_pkey ,yearmonth ,count(*) as eligibility from
//                        emp_detail_timeattandance, fin_year fy where (weekoff is not null or holiday is not null) and present='P/P'
//                        and yearmonth between fy.start_month and end_month and fy.Year_status='OPEN' and fy.vattr1 = 0 and emp_pkey = '$leavepolicygroupid'
//                         group by emp_pkey ,yearmonth 
//                        union all
//                        select emp_pkey ,yearmonth ,count(*)*.5 from
//                        emp_detail_timeattandance , fin_year fy where (weekoff is not null or holiday is not null) and present in('P/A','A/P')
//                        and yearmonth between fy.start_month and end_month and fy.Year_status='OPEN' and fy.vattr1 = 0 and emp_pkey = '$leavepolicygroupid'
//                        group by emp_pkey ,yearmonth");
                    
                    /* @var $arr_empleave_eligiility type */
                    $arr_emps_dets = $this->LeaveRequests->query("SELECT employee_info.*
FROM `employee_info`
WHERE `emp_pkey` = '$leavepolicygroupid' limit 50
 ");
                    $arr_emp_status = $this->LeaveRequests->query("select  emp_details.status from emp_details where emp_pkey =$leavepolicygroupid ");
                  
                     // $empid = $arr_leavepolicydetails_for_template['0']['emp_dets']['0']['employee_info']['emp_pkey'];
         $arr_termin = $this->LeaveRequests->query(" select last_approved_working_date from termination where termination.emp_fkey = $leavepolicygroupid");
          //debug($arr_termin);
                    try{
                     //$arr_empleaverequests = $this->LeaveRequests->query("select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and (weekoff != '' or holiday != '') and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '2' ))");
		$arr_empleaverequests = $this->LeaveRequests->query("select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and (weekoff != '' or holiday != '') and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '2' )) union select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and  holiday != '' and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '4' ))");  
                    } catch (Exception $ex) {

                    }
                    
                    $year = date("Y",  strtotime($from));
                    try{
                        $arr_empleave_eligiility = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$leavepolicygroupid','$salary_head_pkey','$year') as blnce ");
                        
                    } catch (Exception $ex) {

                    }
                    
                    
                } else {
                    $arr_empleaverequests = $this->LeaveRequests->query("select * from leaveentries where salary_head_item_fkey = $salary_head_pkey and EMP_fkey = '$leavepolicygroupid' " );
                }
                
               // debug("select * from leaveentries where salary_head_item_fkey = $salary_head_pkey and EMP_fkey = '$leavepolicygroupid' ");
               // debug("select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and (weekoff != '' or holiday != '') and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '2' )) union select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and  holiday != '' and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '4' ))");
                $arr_empleavetaken = $this->LeaveRequests->query("select LEAVEENTRYID,ed.status,salary_head_item_fkey,applied_date,LEAVESTATUS,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,concat(ed.first_name,' ',ed.last_name) applied_name,Autherized_date,APPROVED_date,contact_person,contact_No,Reason,REMARKS,leave_days,(select concat(first_name,' ',last_name) from emp_details where emp_pkey = leaveentries.ISAutherizedby) as Authorized_name,(SELECT CONCAT(first_name,'',last_name) from emp_details where emp_pkey = leaveentries.APPROVEDBY) approved_name from leaveentries

left join emp_details ed on (ed.emp_pkey = leaveentries.EMP_fkey)
where leaveentries.salary_head_item_fkey = '$salary_head_pkey' and EMP_fkey = '$leavepolicygroupid' and TODATE between '$from' and '$to'  " ); 
               
                    $arr_leave_compoff = $this->LeaveRequests->query("SELECT *,le.leave_days,le.FROMDATE,le.TODATE,le.LEAVESTATUS from scheduled_break_off as sb
                    left join leaveentries as le on (sb.leave_entry_id = le.LEAVEENTRYID)
                    WHERE `type` = 'W' AND sb.emp_fkey = '$leavepolicygroupid' "
                            . " AND break_off_date  between '$from' and '$to' and status =1
                    ");            


                //   debug($arr_empleavetaken);
             //   $arr_termination = $this->LeaveRequests->query("select last_approved_working_date from termination where termination.emp_fkey = 
                $arr_leavepolicydetails_for_template[] = array(
                    'emp_dets'=>$arr_emps_dets,
                    'summary' => $arr_empleaverequests,
                    'eligibility' => $arr_empleave_eligiility,
                    'leaves' => $arr_empleavetaken,
                    'termin' => $arr_termin,
                    'status' => $arr_emp_status,
                    'compoff' => $arr_leave_compoff
                );


                // debug("SELECT *,le.FROMDATE,le.TODATE,le.LEAVESTATUS from scheduled_break_off 
                // left join leaveentries as le on (scheduled_break_off.leave_entry_id = le.LEAVEENTRYID)
                // WHERE EXISTS (SELECT LEAVEENTRYID FROM leaveentries)
                // ");
             // debug($arr_leavepolicydetails_for_template); exit;
            }
        } else {
            echo "<div style='color:red' ><h3>No record Found</h3></div>";
            die();
        }
        
       // debug($arr_leavepolicydetails_for_template);
       
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $this->set('arr_termin', $arr_termin);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->set('year', $year);
        $this->set('arr_compoff', $arr_leave_compoff);
        $this->set('date_time',$date_time);
        $this->set('user_id',$user_id);
        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        // debug($cr);exit;
        switch ($mode) {
            case 'pdf' :
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('compoffnew');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavecompoffssummary.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_CompOff.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Comp Off New ");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:K1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:K2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $rowcount = 3;
                $i = 0;
                 if (count($arr_leavepolicydetails_for_template) > 0) {
                foreach ($arr_leavepolicydetails_for_template as $value) {
                    if (count($value['compoff']) > 0) {
                        $i += 1;
                        $le = 'Leave Comp Off Report of ';
                        $empstatus = isset($value['status']['0']['emp_details']['status']) && $value['status']['0']['emp_details']['status']=="2" ? '(Resigned)':'';
                        $dep = isset($value['emp_dets']['0']['employee_info']['EmpName']) ? $value['emp_dets']['0']['employee_info']['EmpName'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $dep.$empstatus);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        
                        $worksheet->mergeCells('A'.$rowcount.':K'.$rowcount);
                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                        
                        $arr_data = $value['summary'];
                        $emp_dets = $value['emp_dets'];
                        
                        $columncount = 0;
                        $rowcount++;
                        $tab1start = $rowcount; 
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Details ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                        $worksheet->mergeCells('A'.$rowcount.':H'.$rowcount);
                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        
                        $rowcount = $rowcount + 1;
                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Company ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $arr_data = $value['compoff'];
                            $empstatus = isset($value['status']['0']['emp_details']['status']) && $value['status']['0']['emp_details']['status']=="2" ? '(Resigned)':'';
                            $emp = $emp_dets['0']['employee_info']['EmpName'].$empstatus;
                            $emp_id = $emp_dets['0']['employee_info']['emp_id'];
                            $id = $emp_dets['0']['employee_info']['employee_id'];
                            $clas = $emp_dets['0']['employee_info']['designation'];
                            $join = $emp_dets['0']['employee_info']['joining_date'];
                            $dept = $emp_dets['0']['employee_info']['department'];
                            $unit = $emp_dets['0']['employee_info']['branch'];
                            $termination = isset ($value['termin']['0']['termination']['last_approved_working_date']) ? $value['termin']['0']['termination']['last_approved_working_date'] :''; 
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $emp_id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $emp);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $unit);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $clas);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $termination);
                            $tab1end = $rowcount;
                            $rowcount ++;
                        
                        if (!empty($arr_data)) {
                            $rowcount++;
                            $tab2start = $rowcount;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Compensatory Accrued Details ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                            $worksheet->mergeCells('A'.$rowcount.':F'.$rowcount);
                            $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                            

                        
                            $rowcount = $rowcount + 1;
                            $columncount = 0;
                            
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No.  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Accrued Day');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Half day/ Full day');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Corresponding date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Status');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Leave taken count');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                            $rowcount = $rowcount + 1;   
                            
                            
                        //    debug($arr_data); exit;
                        // if (count($arr_data) > 0) {
                            $used = 0;
                            $compoff = 0;
                            $usedcomp = 0;
                            foreach ($arr_data as $val) {
                                $used = $used + 1;
                                $att_date =isset($val['sb']['break_off_date'])? $val['sb']['break_off_date']:'';
                                
                                if($val['sb']['first_half'] == 'N'){
                                    $duration = 'Full day';
                                    $compoff = $compoff + 1;
                                }elseif($val['sb']['first_half'] == 'Y'){
                                    $duration = 'Half day';
                                    $compoff = $compoff + .5;
                                }
                                $correspd = $val['le']['FROMDATE'];
                                $status = $val['le']['LEAVESTATUS'];
                                $leavedays = $val['le']['leave_days'];
                                $usedcomp = $usedcomp + $leavedays;
                                
                                $columncount = 0;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $used);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $att_date);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $duration);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $correspd);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $status);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $leavedays);
                                $rowcount ++;

                                if($val['le']['leave_days'] != null){
                                    $leavedays = $leavedays + $val['le']['leave_days'];
                                }
                            }
                            $worksheet->mergeCells('A'.$rowcount.':E'.$rowcount);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), 'Available Comp Off Leave Balance :');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), ($compoff - $usedcomp));
                            $balance_levv = isset($value['eligibility']['0']['0']['blnce']) ? $value['eligibility']['0']['0']['blnce'] : 0;
                            // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 0), $rowcount, 'Available Comp Off Leave Balance : '.$balance_levv);
                            
                            // $worksheet->mergeCells('A'.$rowcount.':K'.$rowcount);
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 0), $rowcount)->getFont()->setBold(true);
                            $tab2end = $rowcount;
                            $rowcount++;
                            
                            //Border for first table
                            $worksheet->getStyle("A".$tab1start.":H".$tab1end)->applyFromArray(
                                array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('rgb' => '000000')
                                        )
                                    )
                                )
                            );

                            //Border for second table
                            $worksheet->getStyle("A".$tab2start.":F".$tab2end)->applyFromArray(
                                array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('rgb' => '000000')
                                        )
                                    )
                                )
                            );
} else {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Record Found');
                        }
                        
                        // $arr_data = $value['leaves'];
                
                        
                        //     $rowcount++;
                        //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Comp Off Leave List ');
                        //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                        //     $worksheet->mergeCells('A'.$rowcount.':D'.$rowcount);
                        //     $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                        //             array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        //     );
                            
                            
                        //     $rowcount = $rowcount + 1;
                        //     $columncount = 0;
                        //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No.  ');
                        //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        //     $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'From Date');
                        //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        //     $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'To Date');
                        //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        //     $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Status');
                        //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        //     $rowcount = $rowcount + 2;   
                            
                        // if (count($arr_data) > 0) {
                        //     $used = 0;
                        //     foreach ($arr_data as $val) {
                        //         $used = $used + 1;
                        //         $att_date = $val['leaveentries']['FROMDATE'] . ' ' . $val['leaveentries']['FROMHALF'];
                        //         $duration = $val['leaveentries']['TODATE'] . ' ' . $val['leaveentries']['TOHALF'];
                        //         $dys = $val['leaveentries']['LEAVESTATUS'];
                                
                        //         $columncount = 0;
                        //         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $used);
                        //         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $att_date);
                        //         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $duration);
                        //         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $dys);
                        //         $rowcount ++;
                        //     }
                            
                        //     $balance_levv = isset($value['eligibility']['0']['0']['blnce']) ? $value['eligibility']['0']['0']['blnce'] : 0;
                        //     $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 0), $rowcount, 'Available Comp off Leave Balance ');
                        //     $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, $balance_levv );
                        //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        //     $rowcount ++;
                        // } else {
                        //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Record Found');
                        // }
                        
                       // $rowcount ++;
                    }
        }}else{
                        $worksheet->mergeCells('A'.$rowcount.':K'.$rowcount);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 3, 'No data available under the selected criteria');
                    }

                
                
                $objPHPExcel->getActiveSheet()->setTitle('Leave Compoff Report ');
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
                $this->render('compoffnew');
                break;
        }
    }
    public function generatesummaryreportPSQUARE($mode){
     $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        //edited by athira on 07-07-2025
        $company_code = $this->Session->read('company_code');
        $this->set('company_code',$company_code);
        //end
        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));


        $needBranchWiseReport = false;
        $conditions = array();

        $condition = ' where EmployeeDetails.status = 1 and ';
        //        debug($arr_form_data);
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition =  "where EmployeeDetails.status in('1','2') and ";
        }

        $conditions[] = 'FROMDATE >="' . $from . '" and TODATE<="' . $to . '"';
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }


            try {
                $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array(
                    "fields" => "reportcriteria,reportcriteria_field",
                    "conditions" => array(
                        "reporttype" => "LeaveSummary",
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

        // try{
        //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
        $arr_empleaverequests = $this->LeaveRequests->query('SELECT (SELECT CONCAT(first_name," ",last_name) from emp_details '
            . 'where emp_pkey =  LeaveRequests.ISAutherizedby) as Authorized_name,(SELECT CONCAT(first_name," ",last_name) '
            . 'from emp_details where emp_pkey =  LeaveRequests.APPROVEDBY) as Approved_name,`termination`.`last_approved_working_date`,'
            . '`LeaveRequests`.`LEAVEENTRYID`,`LeaveRequests`.`Reason`,`LeaveRequests`.`contact_person`,`LeaveRequests`.`FROMHALF`,`LeaveRequests`.`TOHALF`,`LeaveRequests`.`leave_days`,'
            . '`LeaveRequests`.`REMARKS`,Units.branch_name,EmployeeDetails.branch_code,EmployeeDetails.status,CONCAT(first_name, " ", last_name) AS emp_name,Info.* ,'
            . '`LeaveType`.`item` AS `leave_type`,`LeaveRequests`.`applied_date`, `LeaveRequests`.`FROMDATE`, `LeaveRequests`.`TODATE`, '
            . '`LeaveRequests`.`LEAVESTATUS`,`LeaveRequests`.`AuthoriseRemarks`,`LeaveRequests`.`ApproveRemarks`,`LeaveRequests`.`Autherized_date`,`LeaveRequests`.`APPROVED_date`  FROM `leaveentries` AS `LeaveRequests`'
            //. ' LEFT JOIN `emp_leave_transactions` AS `EmpLeaveTransactions` ON (`LeaveRequests`.`LEAVEENTRYID` = `EmpLeaveTransactions`.`LEAVEENTRYID`)'
            . ' LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`LeaveRequests`.`EMP_fkey` = `EmployeeDetails`.`emp_pkey`)'
            . ' LEFT JOIN `salary_head_items` AS `LeaveType` ON (`LeaveRequests`.`salary_head_item_fkey` = `LeaveType`.`salary_head_item_pkey`) '
            . ' LEFT JOIN `branches` AS `Units` ON (`EmployeeDetails`.`branch_code` = `Units`.`branch_code`) '
            . ' LEFT JOIN `leavestatus` AS `Leavestatus` ON (`LeaveRequests`.`LEAVESTATUS` = `Leavestatus`.`LEAVESTATUS`) '
            . 'LEFT JOIN `employee_info` AS `Info` ON (`EmployeeDetails`.`emp_pkey` = `Info`.`emp_pkey`)'
            . 'LEFT JOIN `termination` AS `termination` ON (`termination`.`emp_fkey` = `Info`.`emp_pkey` and `termination`.`status` = 1)'
            . $condition . $str_conditions . 'ORDER BY EmployeeDetails.emp_pkey desc ');
        //  debug($arr_empleaverequests);
        //        } catch (Exception $ex) {
        //
        //        }

        $pkey = isset($arr_empleaverequests['0']['Info']['emp_pkey']) ? $arr_empleaverequests['0']['Info']['emp_pkey'] : 0;

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
                    $request['status'] = (isset($leaverequest['EmployeeDetails']['status'])) && $leaverequest['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : '';
                    $request['emp_name'] = isset($leaverequest['Info']['EmpName']) ? $leaverequest['Info']['EmpName'] : '';
                      //edited by athira on 07-07-2025
                    $request['EmpUSName'] = isset($leaverequest['Info']['EmpUSName']) ? $leaverequest['Info']['EmpUSName'] : '';
                    $request['emp_us_id'] = isset($leaverequest['Info']['emp_us_id']) ? $leaverequest['Info']['emp_us_id'] : '';
                    //end
                    $request['employee_id'] = isset($leaverequest['Info']['employee_id']) ? $leaverequest['Info']['employee_id'] : '';
                    $request['branch'] = isset($leaverequest['Info']['branch']) ? $leaverequest['Info']['branch'] : '';
                    $request['designation'] = isset($leaverequest['Info']['designation']) ? $leaverequest['Info']['designation'] : '';
                    $request['department'] = isset($leaverequest['Info']['department']) ? $leaverequest['Info']['department'] : '';
                    $request['joining_date'] = isset($leaverequest['Info']['joining_date']) ? $leaverequest['Info']['joining_date'] : '';
                    $request['leave_type'] = isset($leaverequest['LeaveType']['leave_type']) ? $leaverequest['LeaveType']['leave_type'] : '';
                    //edited by megha on 11/10/2019 admin added in null condition
                    $request['Authorized_name'] = isset($leaverequest['0']['Authorized_name']) ? $leaverequest['0']['Authorized_name'] : '';
                    $request['Approved_name'] = isset($leaverequest['0']['Approved_name']) ? $leaverequest['0']['Approved_name'] : '';
                    $request['leave_status'] = isset($leaverequest['LeaveRequests']['LEAVESTATUS']) ? $leaverequest['LeaveRequests']['LEAVESTATUS'] : '';
                    $request['leave_from'] = isset($leaverequest['LeaveRequests']['FROMDATE']) ? $leaverequest['LeaveRequests']['FROMDATE'] : '';
                    $request['leave_to'] = isset($leaverequest['LeaveRequests']['TODATE']) ? $leaverequest['LeaveRequests']['TODATE'] : '';
                    $request['leave_applied_on'] = isset($leaverequest['LeaveRequests']['applied_date']) ? $leaverequest['LeaveRequests']['applied_date'] : '';
                    $request['fromhalf'] = isset($leaverequest['LeaveRequests']['FROMHALF']) ? $leaverequest['LeaveRequests']['FROMHALF'] : '';
                    $request['tohalf'] = isset($leaverequest['LeaveRequests']['TOHALF']) ? $leaverequest['LeaveRequests']['TOHALF'] : '';
                    $request['leavedays'] = isset($leaverequest['LeaveRequests']['leave_days']) ? $leaverequest['LeaveRequests']['leave_days'] : '';
                    //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
                    $request['Authorized_remarks'] = isset($leaverequest['LeaveRequests']['AuthoriseRemarks']) ? $leaverequest['LeaveRequests']['AuthoriseRemarks'] : '';
                    $request['Approved_remarks'] = isset($leaverequest['LeaveRequests']['ApproveRemarks']) ? $leaverequest['LeaveRequests']['ApproveRemarks'] : '';
                    $request['Autherized_date'] = isset($leaverequest['LeaveRequests']['Autherized_date']) ? $leaverequest['LeaveRequests']['Autherized_date'] : '';
                    $request['APPROVED_date'] = isset($leaverequest['LeaveRequests']['APPROVED_date']) ? $leaverequest['LeaveRequests']['APPROVED_date'] : '';
                    //end remarks
                    //Reson and contact person added by megha on 26/09/2019
                    $request['Reason'] = isset($leaverequest['LeaveRequests']['Reason']) ? $leaverequest['LeaveRequests']['Reason'] : '';
                    $request['contact_person'] = isset($leaverequest['LeaveRequests']['contact_person']) ? $leaverequest['LeaveRequests']['contact_person'] : '';

                    //added by megha userid on 27/08/2019
                    $pkey = isset($leaverequest['Info']['emp_pkey']) ? $leaverequest['Info']['emp_pkey'] : 0;
                    $userid = $this->LeaveRequests->query("select user_id from user_credentials where user_credentials.emp_fkey = $pkey");
                    $request['userid'] = $userid['0']['user_credentials']['user_id'];
                    //end userid
                    $arr_leavesummary_for_template[$branch_code]['leaverequests'][] = $request;
                }
                //debug($request);
            }
        } else {
            //Parse array for simple report
            $arr_leavesummary_for_template['leaverequests'] = array();
            foreach ($arr_empleaverequests as $leaverequest) {
                $request = array();
                $request['termination'] = isset($leaverequest['termination']['last_approved_working_date']) ? $leaverequest['termination']['last_approved_working_date'] : '';
                $request['status'] = (isset($leaverequest['EmployeeDetails']['status'])) && $leaverequest['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : '';
                $request['emp_name'] = isset($leaverequest['Info']['EmpName']) ? $leaverequest['Info']['EmpName'] : '';
                //edited by athira on 07-07-2025
                $request['EmpUSName'] = isset($leaverequest['Info']['EmpUSName']) ? $leaverequest['Info']['EmpUSName'] : '';
                $request['emp_us_id'] = isset($leaverequest['Info']['emp_us_id']) ? $leaverequest['Info']['emp_us_id'] : '';
                //end
                $request['employee_id'] = isset($leaverequest['Info']['employee_id']) ? $leaverequest['Info']['employee_id'] : '';
                $request['branch'] = isset($leaverequest['Info']['branch']) ? $leaverequest['Info']['branch'] : '';
                $request['designation'] = isset($leaverequest['Info']['designation']) ? $leaverequest['Info']['designation'] : '';
                $request['department'] = isset($leaverequest['Info']['department']) ? $leaverequest['Info']['department'] : '';
                $request['joining_date'] = isset($leaverequest['Info']['joining_date']) ? $leaverequest['Info']['joining_date'] : '';
                $request['leave_type'] = isset($leaverequest['LeaveType']['leave_type']) ? $leaverequest['LeaveType']['leave_type'] : '';
                $request['Authorized_name'] = isset($leaverequest['0']['Authorized_name']) ? $leaverequest['0']['Authorized_name'] : '';
                $request['Approved_name'] = isset($leaverequest['0']['Approved_name']) ? $leaverequest['0']['Approved_name'] : '';
                $request['leave_status'] = isset($leaverequest['LeaveRequests']['LEAVESTATUS']) ? $leaverequest['LeaveRequests']['LEAVESTATUS'] : '';
                $request['leave_from'] = isset($leaverequest['LeaveRequests']['FROMDATE']) ? $leaverequest['LeaveRequests']['FROMDATE'] : '';
                $request['leave_to'] = isset($leaverequest['LeaveRequests']['TODATE']) ? $leaverequest['LeaveRequests']['TODATE'] : '';
                $request['leave_applied_on'] = isset($leaverequest['LeaveRequests']['applied_date']) ? $leaverequest['LeaveRequests']['applied_date'] : '';
                $request['fromhalf'] = isset($leaverequest['LeaveRequests']['FROMHALF']) ? $leaverequest['LeaveRequests']['FROMHALF'] : '';
                $request['tohalf'] = isset($leaverequest['LeaveRequests']['TOHALF']) ? $leaverequest['LeaveRequests']['TOHALF'] : '';
                $request['leavedays'] = isset($leaverequest['LeaveRequests']['leave_days']) ? $leaverequest['LeaveRequests']['leave_days'] : '';
                //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
                $request['Authorized_remarks'] = isset($leaverequest['LeaveRequests']['AuthoriseRemarks']) ? $leaverequest['LeaveRequests']['AuthoriseRemarks'] : '';
                $request['Approved_remarks'] = isset($leaverequest['LeaveRequests']['ApproveRemarks']) ? $leaverequest['LeaveRequests']['ApproveRemarks'] : '';
                $request['Autherized_date'] = isset($leaverequest['LeaveRequests']['Autherized_date']) ? $leaverequest['LeaveRequests']['Autherized_date'] : '';
                $request['APPROVED_date'] = isset($leaverequest['LeaveRequests']['APPROVED_date']) ? $leaverequest['LeaveRequests']['APPROVED_date'] : '';
                //end remarks
                //Reson and contact person added by megha on 26/09/2019
                $request['Reason'] = isset($leaverequest['LeaveRequests']['Reason']) ? $leaverequest['LeaveRequests']['Reason'] : '';
                $request['contact_person'] = isset($leaverequest['LeaveRequests']['contact_person']) ? $leaverequest['LeaveRequests']['contact_person'] : '';
                //added by megha userid on 27/08/2019
                $pkey = isset($leaverequest['Info']['emp_pkey']) ? $leaverequest['Info']['emp_pkey'] : 0;
                $userid = $this->LeaveRequests->query("select user_id from user_credentials where user_credentials.emp_fkey = $pkey");
                $request['userid'] = isset($userid['0']['user_credentials']['user_id']) ? $userid['0']['user_credentials']['user_id'] : '';
                //end userid
                $arr_leavesummary_for_template['leaverequests'][] = $request;
            }
        } //debug($request);

        $this->set('needBranchWiseReport', $needBranchWiseReport);
        $this->set('arr_leavesummary_for_template', $arr_leavesummary_for_template);

        $dates = $from . ' - ' . $to;

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
            case 'pdf':
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
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Leave Detailed Report.xlsx" : "Leave Detailed Report" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Detailed Reports  " . $dates);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                

                $columncount = 0;
                $lastColumn = $columncount + 27; // or calculate dynamically if needed

                for ($col = 0; $col <= $lastColumn; $col++) {
                    $colLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($colLetter)
                        ->setAutoSize(true);
                }
                $rowcount = 2;
                //edited by athira on 17-06-2025
                if(!empty($arr_leavesummary_for_template)){
                //end
                if (isset($needBranchWiseReport) && $needBranchWiseReport == true) {
                    foreach ($arr_leavesummary_for_template as $branch_code => $leavesummary) {



                        $branchname = $leavesummary['branch_name'] . ' Branch';
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $branchname);
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowcount . ':J' . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 2;


                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Sl No . ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount, 'Employee Name (US Format)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount, 'Employee ID (US Format)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'User Id');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Applied Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'From Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'To Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                        //edited by megha reason and contact person
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'Reason');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, 'Contact Person');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);
                        //end
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, 'Authorized By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 16), $rowcount)->getFont()->setBold(true);
                        //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, 'Authorized Person Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 17), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 18), $rowcount, 'Authorized Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 18), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 19), $rowcount, 'Approved By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 19), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 20), $rowcount, 'Approved Person Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 20), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 21), $rowcount, 'Approved Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 21), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 22), $rowcount, 'Rejected By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 22), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 23), $rowcount, 'Rejected Person Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 23), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 24), $rowcount, 'Rejected Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 24), $rowcount)->getFont()->setBold(true);
                        //end remarks
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 25), $rowcount, 'Leave Type');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 25), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 26), $rowcount, 'Leave Days');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 26), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 27), $rowcount, 'Leave Status ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 27), $rowcount)->getFont()->setBold(true);

                        for ($col = $columncount; $col <= $columncount + 27; $col++) {
                            $objPHPExcel->getActiveSheet()
                                ->getStyleByColumnAndRow($col, $rowcount)
                                ->getFill()
                                ->applyFromArray([
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'startcolor' => ['rgb' => 'D9D9D9'] // Light grey background
                                ]);
                        }


                        $rowcount = $rowcount + 1;
                        $arr_data = $leavesummary['leaverequests'];
                        if (count($arr_data) >= 0) {
                            $i = 1;
                            foreach ($arr_data as $val) {
                                $empstatus = $val['status'];
                                $name = $val['emp_name'] . $empstatus;
                                $empname_us = $val['EmpUSName'];
                                $applieddate = $val['leave_applied_on'];
                                $frm = ($val['fromhalf'] == '1') ? $val['leave_from'] . ' ' . 'First Half' : $val['leave_from'] . ' ' . 'Second Half';
                                $to = ($val['tohalf'] == '1') ? $val['leave_to'] . ' ' . 'First Half' : $val['leave_to'] . ' ' . 'Second Half';
                                $status = $val['leave_status'];
                                $type = $val['leave_type'];
                                $id = $val['employee_id'];
                                $id_us = $val['emp_us_id'];
                                $clas = $val['designation'];
                                $authorized = $val['Authorized_name'];
                                $approved = $val['Approved_name'];
                                //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
                                $authorizedremarks = $val['Authorized_remarks'];
                                $approvedremarks = $val['Approved_remarks'];
                                $authorizeddate = $val['Autherized_date'];
                                $approveddate = $val['APPROVED_date'];
                                //end remarks
                                $join = $val['joining_date'];
                                $dept = $val['department'];
                                $unit = $val['branch'];
                                $leave_days = $val['leavedays'];
                                $termination = $val['termination'];
                                $reason = $val['Reason'];
                                $contact_person = $val['contact_person'];
                                //added by megha userid on 27/08/2019
                                //$userid = $arr_userid['0']['user_credentials']['user_id'];
                                $userid = $val['userid'];
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $i++);
                                $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount, $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $name);
                                 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $empname_us);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $id);
                                $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +3 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $id_us);
                                 $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +4 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $userid);
                                $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +5 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $join);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $unit);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), ($rowcount), $dept);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), ($rowcount), $clas);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), ($rowcount), $termination);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, $applieddate);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, $frm);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, $to);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, $reason);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, $contact_person);
                                //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019

                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, $authorized);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, $authorizedremarks);
                                $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +17 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 18), $rowcount, $authorizeddate);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 19), $rowcount, $approved);
                                if ($val['leave_status'] != 'Rejected') {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 20), $rowcount, $approvedremarks);
                                    $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +20 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 21), $rowcount, $approveddate);
                                } else {
                                    //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, '');
                                    //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, '');
                                    //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, '');
                                    //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, '');
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 20), $rowcount, '');
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 21), $rowcount, '');
                                }
                                if ($val['leave_status'] == 'Rejected') {
                                    if ($val['APPROVED_date'] == '') {
                                        $rejected = $val['Authorized_name'];
                                        $rejectedremark = $val['Authorized_remarks'];
                                        $rejectedate = $val['Autherized_date'];
                                    } else {
                                        $rejected = $val['Approved_name'];
                                        $rejectedremark = $val['Approved_remarks'];
                                        $rejectedate = $val['APPROVED_date'];
                                    }
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 22), $rowcount, $rejected);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 23), $rowcount, $rejectedremark);
                                    $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +23, $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 24), $rowcount, $rejectedate);
                                } else {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 22), $rowcount, '');
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 23), $rowcount, '');
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 24), $rowcount, '');
                                }
                                //end remarks
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 25), $rowcount, $type);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 26), $rowcount, $leave_days);
                                $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount + 26 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 27), $rowcount, $status);
                                $rowcount = $rowcount + 1;
                            }
                        } 
                        $rowcount++;
                    }
                } else {

                   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Sl No . ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount, 'Employee Name (US Format)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount, 'Employee ID (US Format)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'User Id');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Applied Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'From Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'To Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                        //edited by megha reason and contact person
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'Reason');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, 'Contact Person');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);
                        //end
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, 'Authorized By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 16), $rowcount)->getFont()->setBold(true);
                        //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, 'Authorized Person Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 17), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 18), $rowcount, 'Authorized Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 18), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 19), $rowcount, 'Approved By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 19), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 20), $rowcount, 'Approved Person Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 20), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 21), $rowcount, 'Approved Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 21), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 22), $rowcount, 'Rejected By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 22), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 23), $rowcount, 'Rejected Person Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 23), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 24), $rowcount, 'Rejected Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 24), $rowcount)->getFont()->setBold(true);
                        //end remarks
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 25), $rowcount, 'Leave Type');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 25), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 26), $rowcount, 'Leave Days');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 26), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 27), $rowcount, 'Leave Status ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 27), $rowcount)->getFont()->setBold(true);

                        for ($col = $columncount; $col <= $columncount + 27; $col++) {
                            $objPHPExcel->getActiveSheet()
                                ->getStyleByColumnAndRow($col, $rowcount)
                                ->getFill()
                                ->applyFromArray([
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'startcolor' => ['rgb' => 'D9D9D9'] // Light grey background
                                ]);
                        }

                    $rowcount = $rowcount + 1;


                    $arr_data = $arr_leavesummary_for_template['leaverequests'];
                    if (count($arr_data) >= 0) {
                        $i = 1;
                        foreach ($arr_data as $val) {

                            $empstatus = $val['status'];
                            $name = $val['emp_name'] . $empstatus;
                            $empname_us = $val['EmpUSName'];
                            $applieddate = $val['leave_applied_on'];
                            $frm = ($val['fromhalf'] == '1') ? $val['leave_from'] . ' ' . 'First Half' : $val['leave_from'] . ' ' . 'Second Half';
                            $to = ($val['tohalf'] == '1') ? $val['leave_to'] . ' ' . 'First Half' : $val['leave_to'] . ' ' . 'Second Half';
                            $status = $val['leave_status'];
                            $type = $val['leave_type'];
                            $id = $val['employee_id'];
                            $id_us = $val['emp_us_id'];
                            $clas = $val['designation'];
                            $authorized = $val['Authorized_name'];
                            $approved = $val['Approved_name'];
                            //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019
                            $authorizedremarks = $val['Authorized_remarks'];
                            $approvedremarks = $val['Approved_remarks'];
                            $authorizeddate = $val['Autherized_date'];
                            $approveddate = $val['APPROVED_date'];
                            //end remarks
                            $join = $val['joining_date'];
                            $dept = $val['department'];
                            $unit = $val['branch'];
                            $leave_days = $val['leavedays'];
                            $termination = $val['termination'];
                            $reason = $val['Reason'];
                            $contact_person = $val['contact_person'];
                            //added by megha userid on 27/08/2019
                            //$userid = $arr_userid['0']['user_credentials']['user_id'];
                            $userid = $val['userid'];
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $i++);
                            $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, $name);
                              $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount, $empname_us);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +3 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $id_us);
                            $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +4 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $userid);
                            $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +5 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $unit);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), ($rowcount), $clas);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), ($rowcount), $termination);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, $applieddate);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, $frm);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, $to);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, $reason);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, $contact_person);
                            //`LeaveRequests`.`AuthoriseRemarks` added by megha on 26/09/2019

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, $authorized);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, $authorizedremarks);
                            $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +17 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 18), $rowcount, $authorizeddate);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 19), $rowcount, $approved);
                            if ($val['leave_status'] != 'Rejected') {
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 20), $rowcount, $approvedremarks);
                                $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +20 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 21), $rowcount, $approveddate);
                            } else {
                                //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, '');
                                //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, '');
                                //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, '');
                                //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, '');
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 20), $rowcount, '');
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 21), $rowcount, '');
                            }
                            if ($val['leave_status'] == 'Rejected') {
                                if ($val['APPROVED_date'] == '') {
                                    $rejected = $val['Authorized_name'];
                                    $rejectedremark = $val['Authorized_remarks'];
                                    $rejectedate = $val['Autherized_date'];
                                } else {
                                    $rejected = $val['Approved_name'];
                                    $rejectedremark = $val['Approved_remarks'];
                                    $rejectedate = $val['APPROVED_date'];
                                }
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 22), $rowcount, $rejected);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 23), $rowcount, $rejectedremark);
                                $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +23 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 24), $rowcount, $rejectedate);
                            } else {
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 22), $rowcount, '');
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 23), $rowcount, '');
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 24), $rowcount, '');
                            }
                            //end remarks
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 25), $rowcount, $type);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 26), $rowcount, $leave_days);
                            $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +26 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 27), $rowcount, $status);
                            $rowcount = $rowcount + 1;
                        }
                    } 
                }
                //edited by athira on 17-06-2025
            }else{
                $rowcount = 2; // set row just after the title
                $worksheet->setCellValue('A' . $rowcount, 'No data available under the selected criteria.');
                $worksheet->mergeCells("A{$rowcount}:J{$rowcount}");
                $worksheet->getStyle("A{$rowcount}")->getFont()->setBold(true)->setSize(14);

            }
            //end
                $objPHPExcel->getActiveSheet()->setTitle('Leave Detailed Report');
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
                $this->render('reportleavesummary');
                break;
        }
}
public function generateleavebalancemonthlyreportPSQUARE($mode){
    $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        //edited by athira on 07-07-2025
        $company_code = $this->Session->read('company_code');
        $this->set('company_code',$company_code);
        //end
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $report_month = $arr_form_data['reportfrom'];
        if ($arr_form_data['reportfrom']) {
            $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
            $frm = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $year1 = date('Y', strtotime($arr_form_data['reportfrom']));
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
        }
        $condition = 'and ed.status = 1';

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and ed.status in('1','2')";
        }

        $arr_leavepolicydetails_for_template = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                $arr_empleaverequests = array();
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

                    try {
                        $arr_leaveheads   = $this->LeaveRequests->query("select salary_head_item_fkey from leavepolicy lp left join emp_proff on 
                        (lp.LEAVEPOLICY_GROUP_ID= emp_proff.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        join salary_head_items on (salary_head_items.salary_head_item_pkey=lp.salary_head_item_fkey)
                        where emp_fkey= $leavepolicygroupid and occurance !='LOP' and salary_head_items.status =1");
                        $arr_finyear  = $this->LeaveRequests->query("SELECT fin_year from fin_year where start_month <= '$from' and 
                            end_month >= '$from'  limit 1 ");
                        $finyear = isset($arr_finyear['0']['fin_year']['fin_year']) ? $arr_finyear['0']['fin_year']['fin_year'] : $year1;

                        foreach ($arr_leaveheads as $lhead) {
                            $head = isset($lhead['lp']['salary_head_item_fkey']) ? $lhead['lp']['salary_head_item_fkey'] : '';
                            $arr_empleaverequests_emp = $this->LeaveRequests->query("SELECT ar.month_year,user_credentials.user_id,LeaveType.salary_head_item_pkey, ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,info.EmpName,
                        CONCAT(ed.first_name, ' ', ed.last_name) AS emp_name, info.*,LeaveType.item AS leave_type,
                        leave_balance_inthe_month_fn('$leavepolicygroupid','$head','$to','$finyear') as monthlybalance
                        from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        left join user_credentials on (user_credentials.emp_fkey = ed.emp_pkey)
                        left join attendance_register as ar on(ar.emp_fkey = ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                        where ed.emp_pkey=" . $leavepolicygroupid . "
                        and ed.status='1' and lp.salary_head_item_fkey=$head and ar.month_year = '$frm' group by info.emp_id");

                            $arr_empleaverequests[] = $arr_empleaverequests_emp;

                            if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                                $arr_empleaverequests_emp_resign = $this->LeaveRequests->query("SELECT ar.month_year,user_credentials.user_id,LeaveType.salary_head_item_pkey, ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,info.EmpName,
                        CONCAT(ed.first_name, ' ', ed.last_name) AS emp_name, info.*,LeaveType.item AS leave_type,
                        leave_balance_inthe_month_fn('$leavepolicygroupid','$head','$to','$finyear') as monthlybalance
                        from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        left join user_credentials on (user_credentials.emp_fkey = ed.emp_pkey)
                        left join attendance_register as ar on(ar.emp_fkey = ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                        where ed.emp_pkey=" . $leavepolicygroupid . "
                        and ed.status='2' and lp.salary_head_item_fkey=$head and ar.month_year = '$frm' group by info.emp_id");

                                $arr_empleaverequests[] = array_merge($arr_empleaverequests_emp, $arr_empleaverequests_emp_resign);
                            }
                            if (!empty($arr_empleaverequests[0])) {
                                $arr_leavepolicydetails_for_template[$leavepolicygroupid] = array(
                                    'summary' => $arr_empleaverequests
                                );
                            }
                        }
                    } catch (Exception $ex) {
                    }
                }
            }
        }


        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        // debug($arr_leavepolicydetails_for_template);exit();
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        //$this->set('cur_year', $cur_year);
        $this->set('from', $from);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $from_date = date("F-Y", strtotime($from));
        $this->set('from_date',$from_date);
        $year = date("Y", $time);
        $this->set('mname1', $mname);
        $this->set('y1', $year);
        $this->set('month2', $month);
        $this->set('month1', $month1);
        $all_leaveheads   = $this->LeaveRequests->query("select distinct salary_head_item_pkey,item from salary_head_items  
            left join leavepolicy on (leavepolicy.salary_head_item_fkey = salary_head_items.salary_head_item_pkey) 
            left join leavepolicy_group on (leavepolicy_group.LEAVEPOLICY_GROUP_ID = leavepolicy.LEAVEPOLICY_GROUP_ID) 
            where item_type='LEAVE' and item_part='Direct' and occurance !='LOP' and salary_head_items.status =1
            and leavepolicy_group.status =1 and leavepolicy.status = 1");
        $this->set('all_leaveheads', $all_leaveheads);
        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('dates', $from);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf':
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('leavebalance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavebalance.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "LeaveBalanceStatement.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Balance Statement - " . $mname . " " . $year1);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:O1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:O2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 3;
                $columncount = 0;
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                //                        
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);

                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee ID (US Format)');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                //                       
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'User ID');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                //                        
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Employee Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);

                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Employee Name (US Format)');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                //                        
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Date of Joining');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                //                        
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Branch');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                //                       
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Department');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                //                        
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Designation');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Termination Date');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);

                        for ($col = 0; $col <= 10; $col++) {
            $objPHPExcel->getActiveSheet()
                ->getStyleByColumnAndRow($col, $rowcount)
                ->getFill()
                ->applyFromArray([
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => ['rgb' => 'D9D9D9']
                ]);
        }


                $col = 11;
                foreach ($all_leaveheads as $heads) {
                    $head = isset($heads['salary_head_items']['item']) ? $heads['salary_head_items']['item'] : '';


                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $head);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($col), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()
                ->getStyleByColumnAndRow($col, $rowcount)
                ->getFill()
                ->applyFromArray([
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => ['rgb' => 'D9D9D9']
                ]);
                    $col++;
                }
                $rowcount = $rowcount + 1;


                if (count($arr_leavepolicydetails_for_template) != 0) {
                    $i = 1;
                    foreach ($arr_leavepolicydetails_for_template as $value) {

                        $arr_data = $value['summary'];


                        if (count($arr_data) > 0) {

                            $val = $arr_data[0][0];
                            if ($cr != "LeaveType") {
                                //$leavetaken = $val['0']['leavetaken'];

                                $id = $val['info']['employee_id'];
                                $id_us = $val['info']['emp_us_id'];
                                $userid =  isset($val['user_credentials']['user_id']) ?  $val['user_credentials']['user_id'] : '';
                                $emp_name = $val['0']['emp_name'];
                                $empname_us = $val['info']['EmpUSName'];
                                $emp_status = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : '';
                                $emp = $emp_name . $emp_status;
                                $join = $val['info']['joining_date'];
                                $branch = $val['info']['branch'];
                                $dept = $val['info']['department'];
                                $desig = $val['info']['designation'];
                                $termination = isset($val['termination']['last_approved_working_date']) ? $val['termination']['last_approved_working_date'] : '';


                                $columncount = 0;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i++);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $id);
                                 $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $id_us);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $userid);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 3) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $emp);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $empname_us);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $join);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $branch);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 7) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), ($rowcount), $dept);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 8) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), ($rowcount), $desig);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columncount + 9) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $termination);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


                                $temp = 11;
                                foreach ($all_leaveheads as $heads) {
                                    $head_pkey = isset($heads['salary_head_items']['salary_head_item_pkey']) ? $heads['salary_head_items']['salary_head_item_pkey'] : '';
                                    $leave_bal = 0;
                                    foreach ($arr_data as $val) {
                                        $key = $val[0]['LeaveType']['salary_head_item_pkey'];
                                        if ($head_pkey == $key) {
                                            $leave_bal = $val[0]['0']['monthlybalance'];
                                        }
                                    }
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($temp) . $rowcount, $leave_bal);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($temp) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $temp++;
                                }
                                $rowcount++;


                                if (!function_exists('numberToColumn')) {
                                    // Define the numberToColumn function
                                    function numberToColumn($num)
                                    {
                                        $str = '';
                                        while ($num > 0) {
                                            $mod = ($num - 1) % 26;
                                            $str = chr(65 + $mod) . $str;
                                            $num = floor(($num - $mod) / 26);
                                        }
                                        return $str;
                                    }
                                }


                                $number = $temp;
                                // Increment the value

                                // Convert the incremented value to its alphabetic column reference
                                $column = numberToColumn($number);

                                // Define the border style
                                $BStyle = array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN
                                        )
                                    )
                                );

                                $row = $rowcount - 1;

                                // Use the dynamic column reference  range
                                $range = 'A1:' . $column . $row;

                                // Apply the border style to the specified range
                                $objPHPExcel->getActiveSheet()->getStyle($range)->applyFromArray($BStyle);

                                // Rest of your code
                                // ...



                            }
                        }
                    }
                } else {
                    $worksheet->mergeCells('A3:S3');
                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                }
                $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                $objPHPExcel->getActiveSheet()->setTitle('Leave Balance Statement');
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
                $this->render('leavebalance');
                break;
        }
}
public function generateleavebalancereportPSQUARE($mode){
     $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        //edited by athira on 07-07-2025
        $company_code=$this->Session->read('company_code');
        $this->set('company_code',$company_code);
        //end
        //Build conditions based on criterias recieved
        //$fd=$arr_form_data['reportfrom'].' '.'00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);

        $report_month = $arr_form_data['reportfrom'];
        if ($arr_form_data['reportfrom'])
            $from = date('Y', strtotime($arr_form_data['reportfrom']));

        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
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

        $condition = 'and ed.status = 1';
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and ed.status in('1','2')";
        }

        $emp_branch_condition = "";
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        /* $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $emp_branch_condition = " and ed.branch_code ='" . $cur_emp_branch . "' ";
        } */



        //employee branch wise sorting ends here

        $arr_leavepolicydetails_for_template = array();
        $arr_empleaverequests2 = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $arr_leavepolicydetails_for_template2 = array();
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $year_con = "EmployeeDetails.emp_pkey = '$leavepolicygroupid' ";
                }
                if ($arr_form_data['select-criteria1'] == 'Units') {
                    $year_con = "EmployeeDetails.branch_code = '$leavepolicygroupid' ";
                }
                if ($arr_form_data['select-criteria1'] == 'LeaveType') {
                    //                    $year_con = "EmployeeDetails.branch_code = EmployeeDetails.branch_code";
                    if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                        $emp_status_condition = " and ed.status in (1,2)";
                    } else {
                        $emp_status_condition = " and ed.status=1";
                    }
                    $leave_type_branch_find = $this->EmployeeDetails->query("select distinct emp_branch from emp_proff ep 
join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID=ep.LEAVEPOLICY_GROUP_ID) 
join salary_head_items shi on (shi.salary_head_item_pkey=lp.salary_head_item_fkey) 
join branches br on (ep.emp_branch=br.branch_code)
join emp_details ed on (ed.emp_pkey=ep.emp_fkey)
where shi.salary_head_item_pkey=" . $leavepolicygroupid . " and br.status=1 " . $emp_status_condition);

                    $branch_array = array();
                    foreach ($leave_type_branch_find as $val) {

                        $branch = $val['ep']['emp_branch'];
                        $fin = $this->LeaveRequests->query("select start_month,end_month,fin_year,Year_status from fin_year where branch_code = '$branch' and vattr1 ='0' and fin_year='" . $from . "' and status=1");
                        $end_month = '';
                        $cur_year = '';
                        if (!empty($fin)) {
                            if (count($fin) > 1) {
                                foreach ($fin as $fin_val) {
                                    if ($fin_val['fin_year']['Year_status'] == 'OPEN') {
                                        $start_month = $fin_val['fin_year']['start_month'];
                                        $end_month = $fin_val['fin_year']['end_month'];
                                        $fin_year = $fin_val['fin_year']['fin_year'];
                                    }
                                }
                            } else {
                                $start_month = $fin[0]['fin_year']['start_month'];
                                $end_month = $fin[0]['fin_year']['end_month'];
                                $fin_year = $fin[0]['fin_year']['fin_year'];
                            }
                            $branch_array[] = array('branch' => $branch, 'start_month' => $start_month, 'end_month' => $end_month, 'fin_year' => $fin_year);
                        }
                    }
                } else {
                    if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array($year_con, "status in (1,2)")));
                    } else {
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array($year_con, "status" => 1)));
                    }
                    $emp_branch = isset($cur_emp_branch_find[0]['EmployeeDetails']['branch_code']) ? $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'] : '';
                    $fin = $this->LeaveRequests->query("select start_month,end_month,fin_year,Year_status from fin_year where branch_code = '$emp_branch' and vattr1 ='0' and fin_year='" . $from . "' and status=1");

                    $start_month = '';
                    $end_month = '';
                    $cur_year = '';
                    if (!empty($fin)) {
                        if (count($fin) > 1) {
                            foreach ($fin as $fin_val) {
                                if ($fin_val['fin_year']['Year_status'] == 'OPEN') {
                                    $start_month = $fin_val['fin_year']['start_month'];
                                    $end_month = $fin_val['fin_year']['end_month'];
                                    $cur_year = $fin_val['fin_year']['fin_year'];
                                }
                            }
                        } else {
                            $start_month = $fin['0']['fin_year']['start_month'];
                            $end_month = $fin['0']['fin_year']['end_month'];
                            $cur_year = $fin['0']['fin_year']['fin_year'];
                        }
                    }
                }


                $arr_empleaverequests = array();
                $arr_empleaverequests_leave = array();
                $arr_empleaverequests_leave_resign = array();
                if ($arr_form_data['select-criteria1'] == 'LeaveType') {

                    try {
                        $just_test = array();
                        foreach ($branch_array as $branch) {
                            $cur_branch = $branch['branch'];
                            $start_month = $branch['start_month'];
                            $end_month = $branch['end_month'];
                            $cur_year = $branch['fin_year'];
                            $temp1[] = $this->LeaveRequests->query("(SELECT distinct ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                            CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                            ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                            leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken,
                            (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                            from emp_details ed
                            join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                            join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                            LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                            left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                            and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                            join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                            join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                            left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                            left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                            where ed.emp_pkey=ed.emp_pkey
                            and ed.status='1' " . $emp_branch_condition . "
                            and ed.branch_code='" . $cur_branch . "'
                            and lp.salary_head_item_fkey=" . $leavepolicygroupid . " and ep.joining_date <= '$end_month' and finyear.fin_year = '$cur_year' and finyear.vattr1=0 and finyear.status=1 group by ed.emp_pkey,LeaveType.item order by ed.branch_code)");
                        }
                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            foreach ($branch_array as $branch) {
                                $cur_branch = $branch['branch'];
                                $start_month = $branch['start_month'];
                                $end_month = $branch['end_month'];
                                $cur_year = $branch['fin_year'];
                                $temp2[] = $this->LeaveRequests->query("(SELECT distinct ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                                CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                                ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                                leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken, 
                                (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave,
								(select count(*) as count from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) where remarks = 'terminate' and salary_paid = 'Y' and leave_encashment_master.status = 1 and emp_fkey = ed.emp_pkey) as terminate,
								(select leavepolicy.leave_encash_limit from emp_details join emp_proff ep on (emp_details.emp_pkey=ep.emp_fkey)
                                join leavepolicy on (leavepolicy.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID ) 
						        left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) 
					            where leavepolicy.status = 1 and is_leave_encash ='Y' and leave_encash_limit is NOT NULL and leavepolicy.salary_head_item_fkey='$leavepolicygroupid' and ed.emp_pkey = emp_details.emp_pkey) as leave_encash_limit
                                from emp_details ed
                                join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                                join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                                LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                                left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                                and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                                join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                                join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                                left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                                left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                                where ed.emp_pkey=ed.emp_pkey
                                and ed.status='2' " . $emp_branch_condition . " and termination.last_approved_working_date >= '$start_month'
                                and ed.branch_code='" . $cur_branch . "'
                                and lp.salary_head_item_fkey=" . $leavepolicygroupid . " and ep.joining_date <= '$end_month' and finyear.fin_year = '$cur_year' and finyear.vattr1=0 and finyear.status=1 group by ed.emp_pkey,LeaveType.item order by ed.branch_code)");
                            }
                        }
                    } catch (Exception $ex) {
                    }
                    $arr_leavepolicydetails_for_template2 = $arr_empleaverequests2;
                } else if ($arr_form_data['select-criteria1'] == 'Departments') {
                    try {
                        $arr_empleaverequests_dept = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                        CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                        ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                        leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken,
                        (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
						from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                        and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                        where ed.emp_pkey=ed.emp_pkey
                        and ed.status='1' " . $emp_branch_condition . "
                        and ed.branch_code=ed.branch_code
                        and lp.salary_head_item_fkey=" . $leavepolicygroupid . " and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.status=1 group by LeaveType.item)");

                        $arr_empleaverequests = $arr_empleaverequests_dept;

                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            $arr_empleaverequests_dept_resign = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                            CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                            ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                            leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken,
                            (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                            from emp_details ed
                            join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                            join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                            LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                            left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                            and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                            join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                            join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                            left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                            left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                            where ed.emp_pkey=ed.emp_pkey
                            and ed.status='2' " . $emp_branch_condition . " and termination.last_approved_working_date >= '$start_month'
                            and ed.branch_code=ed.branch_code
                            and lp.salary_head_item_fkey=" . $leavepolicygroupid . " and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.status=1 group by LeaveType.item)");
                            $arr_empleaverequests = array_merge($arr_empleaverequests_dept, $arr_empleaverequests_dept_resign);
                        }
                    } catch (Exception $ex) {
                    }
                } else if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

                    try {

                        $arr_empleaverequests_emp = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                        CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                        ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                        leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken, 
                        (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                        from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                        and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                        where ed.emp_pkey=" . $leavepolicygroupid . "
                        and ed.status='1' " . $emp_branch_condition . "
                        and ed.branch_code=ed.branch_code
                        and lp.salary_head_item_fkey=lp.salary_head_item_fkey and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.vattr1=0 and finyear.status=1 group by LeaveType.item)");

                        $arr_empleaverequests = $arr_empleaverequests_emp;

                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            $arr_empleaverequests_emp_resign = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                            CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                            ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                            leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken, 
                            (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave,
						    (select count(*) as count from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) where remarks = 'terminate' and salary_paid = 'Y' and leave_encashment_master.status = 1 and emp_fkey = '$leavepolicygroupid') as terminate,
							(select leavepolicy.leave_encash_limit from leavepolicy left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) left join emp_details on (emp_details.emp_pkey = '$leavepolicygroupid') where leavepolicy.status = 1  and LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID  from emp_proff where emp_fkey = '$leavepolicygroupid') and is_leave_encash ='Y' and leave_encash_limit is NOT NULL and leavepolicy.salary_head_item_fkey=lp.salary_head_item_fkey) as leave_encash_limit 
                            from emp_details ed
                            join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                            join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                            LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                            left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                            and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                            join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                            join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                            left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                            left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                            where ed.emp_pkey=" . $leavepolicygroupid . "
                            and ed.status='2' " . $emp_branch_condition . " and termination.last_approved_working_date >= '$start_month'
                            and ed.branch_code=ed.branch_code
                            and lp.salary_head_item_fkey=lp.salary_head_item_fkey and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.vattr1=0 and finyear.status=1 group by LeaveType.item)");

                            $arr_empleaverequests = array_merge($arr_empleaverequests_emp, $arr_empleaverequests_emp_resign);
                        }
                    } catch (Exception $ex) {
                    }
                } else {
                    try {
                        $arr_empleaverequests_branch = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                        CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                        ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                        leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken,
                        (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                        from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                        and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                        where ed.emp_pkey=ed.emp_pkey 
                        and ed.status='1' " . $emp_branch_condition . "
                        and ed.branch_code='" . $leavepolicygroupid . "'
                        and lp.salary_head_item_fkey=lp.salary_head_item_fkey and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.vattr1=0 and finyear.status=1 group by ed.emp_pkey,LeaveType.item)");
                        if (!empty($arr_empleaverequests_branch)) {
                            $arr_empleaverequests = $arr_empleaverequests_branch;
                        }

                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            $arr_empleaverequests_branch_resign = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,
                            CONCAT(first_name, ' ', last_name) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                            ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '" . $from . "') leavebalance,
                            leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '" . $from . "') leavetaken,
                            (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.fin_year='$from' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave,
							(select count(*) as count from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) where remarks = 'terminate' and salary_paid = 'Y' and leave_encashment_master.status = 1 and emp_fkey = ed.emp_pkey) as terminate,
							(select leavepolicy.leave_encash_limit from emp_details join emp_proff ep on (emp_details.emp_pkey=ep.emp_fkey)
                            join leavepolicy on (leavepolicy.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID ) 
						    left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) 
					        where leavepolicy.status = 1 and is_leave_encash ='Y' and leave_encash_limit is NOT NULL and  ed.emp_pkey = emp_details.emp_pkey and salary_head_items.salary_head_item_pkey = lp.salary_head_item_fkey) as leave_encash_limit
                            from emp_details ed
                            join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                            join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                            LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                            left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                            and ecf.emp_fkey=ed.emp_pkey and ecf.fin_year='" . $from . "' and ecf.status = 1)
                            join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                            join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                            left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                            left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                            where ed.emp_pkey=ed.emp_pkey 
                            and ed.status='2' " . $emp_branch_condition . " and termination.last_approved_working_date >= '$start_month'
                            and ed.branch_code='" . $leavepolicygroupid . "'
                            and lp.salary_head_item_fkey=lp.salary_head_item_fkey and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.vattr1=0 and finyear.status=1 group by ed.emp_pkey,LeaveType.item)");
                            if (!empty($arr_empleaverequests_branch_resign)) {
                                $arr_empleaverequests = array_merge($arr_empleaverequests_branch, $arr_empleaverequests_branch_resign);
                            }
                        }
                    } catch (Exception $ex) {
                    }
                }
                if (!empty($arr_empleaverequests)) { //LeaveType criteria will not enter here. By ***ARUL P DAS on 21_3_2020
                    $arr_leavepolicydetails_for_template[] = array(
                        'summary' => $arr_empleaverequests
                        // 'summary' => isset($arr_empleaverequests)?$arr_empleaverequests:array(),
                    );
                }
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }

        if ($arr_form_data['select-criteria1'] == 'LeaveType') { //This leave type has separate variable. by ***ARUL P DAS on 21_3_2020
            //            $arr_leavepolicydetails_for_template = $arr_leavepolicydetails_for_template2; //The $arr_leavepolicydetails_for_template2 is calculated in LeaveType section.
            if (isset($temp1)) {
                foreach ($temp1 as $val) {
                    if (!empty($val)) {
                        //                debug($val[0]['LeaveType']['leave_type']);
                        $leave[$val[0]['LeaveType']['leave_type']]['summary'][] = $val;
                    }
                }
            }
            if (isset($temp2)) {
                foreach ($temp2 as $val) {
                    if (!empty($val)) {
                        //                debug($val[0]['LeaveType']['leave_type']);
                        $leave[$val[0]['LeaveType']['leave_type']]['summary'][] = $val;
                    }
                }
            }
            if (isset($leave)) {
                $arr_leavepolicydetails_for_template = $leave;
            }
        }
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        // $this->set('cur_year', $cur_year);

        $this->set('from', $from);


        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('dates', $from);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf':
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportleavebalance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavebalance.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_LeaveBalanceReport.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Balance Report - " . $from);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:Q1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:Q2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 3;
                if (count($arr_leavepolicydetails_for_template) != 0) {
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $i = 0;
                        $arr_data = $value['summary'];
                        if (count($arr_data) > 0) {
                            $i += 1;
                            $le = 'Leave Balance Reports of ';
                            if ($cr == 'Departments') {
                                $dep = isset($value['summary']['0']['Departments']['dept_name']) ? $value['summary'][0]['Departments']['dept_name'] : '';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $dep);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                                $worksheet->mergeCells('A' . $rowcount . ':Q' . $rowcount);
                                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                );
                            } else if ($cr == 'Units') {
                                $brn = isset($value['summary'][0]['Units']['branch_name']) ? $value['summary'][0]['Units']['branch_name'] : '';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $brn);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                                $worksheet->mergeCells('A' . $rowcount . ':Q' . $rowcount);
                                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                );
                            } else if ($cr == 'EmployeeDetails') {
                                $empstatus = isset($value['summary'][0]['ed']['status']) && $value['summary'][0]['ed']['status'] == "2" ? '(Resigned)' : '';
                                $name = isset($value['summary'][0]['0']['emp_name']) ? $value['summary'][0]['0']['emp_name'] : '';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $name . $empstatus);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                                $worksheet->mergeCells('A' . $rowcount . ':Q' . $rowcount);
                                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                );
                            } else {
                                $type = isset($value['summary'][0][0]['LeaveType']['leave_type']) ? $value['summary'][0][0]['LeaveType']['leave_type'] : '';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $type);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                                $worksheet->mergeCells('A' . $rowcount . ':Q' . $rowcount);
                                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                );
                            }

                            $rowcount = $rowcount + 1;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name (US Format)');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee ID');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Employee ID (US Format)');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+4,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                            
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Date Of Joining');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+5,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                           
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Branch');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+6,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                            
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+7,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                           
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Department');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+8,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                            
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Termination Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+9,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                           
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Leave Type');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+10,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Leave Policy');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+11,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Allotted Leave For The year');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+12,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Carry Forwarded');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+13,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'Leave Taken');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+14,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, 'Encashed Leaves');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+15,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, 'Leave Balance');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 16), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+16,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                          
                            $rowcount = $rowcount + 1;
                            foreach ($arr_data as $val) {
                                if ($cr != "LeaveType") {
                                    $leavetaken = $val['0']['leavetaken'];
                                    $empstatus = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : '';
                                    $empname = isset($val['0']['emp_name']) ? $val['0']['emp_name'] : '';
                                    $emp = $empname . $empstatus;
                                    $typ = isset($val['LeaveType']['leave_type']) ? $val['LeaveType']['leave_type'] : '';
                                    $empname_us = isset($val['info']['EmpUSName']) ? $val['info']['EmpUSName'] : '';
                                    $id = isset($val['info']['employee_id']) ? $val['info']['employee_id'] : '';
                                     $us_id = isset($val['info']['emp_us_id']) ? $val['info']['emp_us_id'] : '';
                                    $clas = isset($val['info']['designation']) ? $val['info']['designation'] : '';
                                    $join = isset($val['info']['joining_date']) ? $val['info']['joining_date'] : '';
                                    $dept = isset($val['info']['department']) ? $val['info']['department'] : '';
                                    $unit = isset($val['info']['branch']) ? $val['info']['branch'] : '';
                                    $carry = isset($val['0']['carryforwarded']) ? $val['0']['carryforwarded'] : 0;
                                    //$encashed_leaves = isset($val['0']['encashed_leave'])?$val['0']['encashed_leave']:'0';
                                    $termination = isset($val['termination']['last_approved_working_date']) ? $val['termination']['last_approved_working_date'] : '';
                                    if ($val['lp']['leave_policy_type'] == 'Y') {
                                        $type = 'Yearly';
                                    } else if ($val['lp']['leave_policy_type'] == 'M') {
                                        $type = 'Monthly';
                                    } else {
                                        $type = 'Present Days';
                                    }
                                    if ($val['lp']['leave_policy_type'] == 'P') {
                                        $lp = '0';
                                    } else {
                                        $lp = isset($val['lp']['alloted_leave_forthe_year']) ? round($val['lp']['alloted_leave_forthe_year'], 1) : 0;
                                    }
                                    $terminate = isset($val['0']['terminate']) ? $val['0']['terminate'] : 0;
                                    $limit = isset($val['0']['leave_encash_limit']) ? $val['0']['leave_encash_limit'] : 0;
                                    $limit = $encashed_leaves = isset($val['0']['encashed_leave']) ? $val['0']['encashed_leave'] : '0';
                                    //									if($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0 ){ 
                                    //									$encashed_leaves = $limit - $val['0']['leavetaken'];
                                    //									}else{
                                    //									$encashed_leaves = isset($val['0']['encashed_leave'])?$val['0']['encashed_leave']:'0'; 
                                    //									}
                                    if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                        //$levbalnce ='0';
                                    } else {
                                        $levbalnce = isset($val['0']['leavebalance']) ? round($val['0']['leavebalance'], 1) : 0;
                                    }
                                    $columncount = 0;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i++);
                                    //edited by sinsiya 05-04-2024
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $emp);
                                     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $empname_us);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $id);
                                    $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +3 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                     $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $us_id);
                                     $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +4 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $join);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $unit);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $clas);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), ($rowcount), $dept);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), ($rowcount), $termination);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $typ);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $type);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $lp);
                                     $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +12 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $carry);
                                     $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +13 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $leavetaken);
                                     $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +14 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $encashed_leaves);
                                     $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +15 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, $levbalnce);
                                     $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +16 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $rowcount++;
                                } else {
                                    foreach ($val as $subval) {
                                        $leavetaken = $subval['0']['leavetaken'];
                                        $empstatus = isset($subval['ed']['status']) && $subval['ed']['status'] == "2" ? '(Resigned)' : '';
                                        $empname = isset($subval['0']['emp_name']) ? $subval['0']['emp_name'] : '';
                                        $emp = $empname . $empstatus;
                                        $typ = isset($subval['LeaveType']['leave_type']) ? $subval['LeaveType']['leave_type'] : '';
                                        $empname_us = isset($subval['info']['EmpUSName']) ? $subval['info']['EmpUSName'] : '';

                                        //$levbalnce = isset($subval['0']['leavebalance']) ? round($subval['0']['leavebalance'], 1) : 0;
                                        $id = isset($subval['info']['employee_id']) ? $subval['info']['employee_id'] : '';
                                         $us_id = isset($subval['info']['emp_us_id']) ? $subval['info']['emp_us_id'] : '';
                                        $clas = isset($subval['info']['designation']) ? $subval['info']['designation'] : '';
                                        $join = isset($subval['info']['joining_date']) ? $subval['info']['joining_date'] : '';
                                        $dept = isset($subval['info']['department']) ? $subval['info']['department'] : '';
                                        $unit = isset($subval['info']['branch']) ? $subval['info']['branch'] : '';
                                        //$encashed_leaves = isset($subval['0']['encashed_leave'])?$subval['0']['encashed_leave']:'0';
                                        $carry = isset($subval['0']['carryforwarded']) ? $subval['0']['carryforwarded'] : 0;
                                        $termination = isset($subval['termination']['last_approved_working_date']) ? $subval['termination']['last_approved_working_date'] : '';
                                        if ($subval['lp']['leave_policy_type'] == 'Y') {
                                            $type = 'Yearly';
                                        } else if ($subval['lp']['leave_policy_type'] == 'M') {
                                            $type = 'Monthly';
                                        } else {
                                            $type = 'Present Days';
                                        }
                                        if ($subval['lp']['leave_policy_type'] == 'P') {
                                            $lp = '0';
                                        } else {
                                            $lp = isset($subval['lp']['alloted_leave_forthe_year']) ? round($subval['lp']['alloted_leave_forthe_year'], 1) : 0;
                                        }
                                        $terminate = isset($subval['0']['terminate']) ? $subval['0']['terminate'] : 0;
                                        $limit = isset($subval['0']['leave_encash_limit']) ? $subval['0']['leave_encash_limit'] : 0;
                                        $limit = $encashed_leaves = isset($subval['0']['encashed_leave']) ? $subval['0']['encashed_leave'] : 0;
                                        //									if($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0 ){ 
                                        //									$encashed_leaves = $limit - $subval['0']['leavetaken'];
                                        //									}else{
                                        //									$encashed_leaves = isset($subval['0']['encashed_leave'])?$subval['0']['encashed_leave']:'0'; 
                                        //									}
                                        if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                            //$levbalnce ='0';
                                        } else {
                                            $levbalnce = isset($subval['0']['leavebalance']) ? round($subval['0']['leavebalance'], 1) : 0;
                                        }
                                        $columncount = 0;
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i++);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $emp);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $empname_us);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $id);
                                         $objPHPExcel->getActiveSheet()
                                        ->getStyleByColumnAndRow($columncount +3 , $rowcount)
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $us_id);
                                         $objPHPExcel->getActiveSheet()
                                        ->getStyleByColumnAndRow($columncount +4 , $rowcount)
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $join);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $unit);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $clas);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), ($rowcount), $dept);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), ($rowcount), $termination);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $typ);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $type);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $lp);
                                         $objPHPExcel->getActiveSheet()
                                        ->getStyleByColumnAndRow($columncount +12 , $rowcount)
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $carry);
                                         $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +13 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $leavetaken);
                                         $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +14 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $encashed_leaves);
                                         $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +15 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, $levbalnce);
                                         $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +16 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                        $rowcount++;
                                    }
                                }
                            }
                            $rowcount++;
                        }
                    }
                } else {
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No Data Available With The Selected Criteria");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                }
                $objPHPExcel->getActiveSheet()->setTitle('Leave Balance Report');
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
                $this->render('reportleavebalance');
                break;
        }

}
public function  generatecompoffreportPSQUARE($mode){
        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        //edited by athira on 07-07-2025
        $company_code=$this->Session->read('company_code');
        $this->set('company_code',$company_code);
        //end
        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $to = date("Y-m-d", strtotime(date("Y-m-d", strtotime($from)) . " + 1 year"));

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
        try {
            $salary_head_pkey = $this->LeaveRequests->query("select salary_head_item_pkey from salary_head_items where occurance = 'COFF' ");
            $salary_head_pkey = isset($salary_head_pkey['0']['salary_head_items']['salary_head_item_pkey']) ? $salary_head_pkey['0']['salary_head_items']['salary_head_item_pkey'] : 0;
        } catch (Exception $ex) {
        }




        $arr_leavepolicydetails_for_template = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

                    /* @var $arr_empleave_eligiility type */
                    $arr_emps_dets = $this->LeaveRequests->query("SELECT employee_info.*
                                                                FROM `employee_info`
                                                                WHERE `emp_pkey` = '$leavepolicygroupid' limit 50
                                                                ");
                    $arr_emp_status = $this->LeaveRequests->query("select  emp_details.status from emp_details where emp_pkey =$leavepolicygroupid ");

                    // $empid = $arr_leavepolicydetails_for_template['0']['emp_dets']['0']['employee_info']['emp_pkey'];
                    $arr_termin = $this->LeaveRequests->query(" select last_approved_working_date from termination where termination.emp_fkey = $leavepolicygroupid");
                    //debug($arr_termin);
                    try {
                        //$arr_empleaverequests = $this->LeaveRequests->query("select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and (weekoff != '' or holiday != '') and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '2' ))");
                        $arr_empleaverequests = $this->LeaveRequests->query("select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and (weekoff != '' or holiday != '') and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '2' )) union select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and  holiday != '' and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '4' ))");
                    } catch (Exception $ex) {
                    }

                    $year = date("Y",  strtotime($from));
                    try {
                        $arr_empleave_eligiility = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$leavepolicygroupid','$salary_head_pkey','$year') as blnce ");
                    } catch (Exception $ex) {
                    }
                } else {
                    $arr_empleaverequests = $this->LeaveRequests->query("select * from leaveentries where salary_head_item_fkey = $salary_head_pkey and EMP_fkey = '$leavepolicygroupid' ");
                }



                $arr_empleavetaken = $this->LeaveRequests->query("select LEAVEENTRYID,ed.status,salary_head_item_fkey,applied_date,LEAVESTATUS,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,concat(ed.first_name,' ',ed.last_name) applied_name,Autherized_date,APPROVED_date,contact_person,contact_No,Reason,REMARKS,leave_days,(select concat(first_name,' ',last_name) from emp_details where emp_pkey = leaveentries.ISAutherizedby) as Authorized_name,(SELECT CONCAT(first_name,'',last_name) from emp_details where emp_pkey = leaveentries.APPROVEDBY) approved_name from leaveentries
                                                                    left join emp_details ed on (ed.emp_pkey = leaveentries.EMP_fkey)
                                                                    where leaveentries.salary_head_item_fkey = '$salary_head_pkey' and EMP_fkey = '$leavepolicygroupid' and TODATE between '$from' and '$to'  ");


                $arr_leavepolicydetails_for_template[] = array(
                    'emp_dets' => $arr_emps_dets,
                    'summary' => $arr_empleaverequests,
                    'eligibility' => $arr_empleave_eligiility,
                    'leaves' => $arr_empleavetaken,
                    'termin' => $arr_termin,
                    'status' => $arr_emp_status
                );
            }
        } else {
            echo "<div style='color:red' ><h3>No record Found</h3></div>";
            die();
        }

        // debug($arr_leavepolicydetails_for_template);

        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $this->set('arr_termin', $arr_termin);
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
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('compoff');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavecompoffssummary.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_LeaveTaken.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Comp Off Report ");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:K1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $rowcount = 3;
                $i = 0;

                foreach ($arr_leavepolicydetails_for_template as $value) {
                    if (!empty($value['summary'])) {
                        $i += 1;
                        $le = 'Leave Comp Off Report of ';
                        $empstatus = isset($value['status']['0']['emp_details']['status']) && $value['status']['0']['emp_details']['status'] == "2" ? '(Resigned)' : '';
                        $dep = isset($value['emp_dets']['0']['employee_info']['EmpName']) ? $value['emp_dets']['0']['employee_info']['EmpName'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $dep . $empstatus);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');

                        $worksheet->mergeCells('A' . $rowcount . ':K' . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);

                        $arr_data = $value['summary'];
                        $emp_dets = $value['emp_dets'];

                        $columncount = 0;
                        $rowcount++;
                        $rowcount++;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Details ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                        $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $rowcount = $rowcount + 2;
                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee Name (US Format)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2, $rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee ID (US Format)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4),$rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount +4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+4, $rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount +5), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount +5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+5, $rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount +6), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount +6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+6, $rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount +7), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount +7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+7, $rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount +8), $rowcount, 'Termination Date'); 
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+8, $rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        
                        $rowcount = $rowcount + 1;
                        $arr_data = $value['summary'];
                        $empstatus = isset($value['status']['0']['emp_details']['status']) && $value['status']['0']['emp_details']['status'] == "2" ? '(Resigned)' : '';
                        $emp = $emp_dets['0']['employee_info']['EmpName'] . $empstatus;
                        $empname_us = $emp_dets['0']['employee_info']['EmpUSName'];
                        $us_id = $emp_dets['0']['employee_info']['emp_us_id'];
                        $id = $emp_dets['0']['employee_info']['employee_id'];
                        $clas = $emp_dets['0']['employee_info']['designation'];
                        $join = $emp_dets['0']['employee_info']['joining_date'];
                        $dept = $emp_dets['0']['employee_info']['department'];
                        $unit = $emp_dets['0']['employee_info']['branch'];
                        $termination = isset($value['termin']['0']['termination']['last_approved_working_date']) ? $value['termin']['0']['termination']['last_approved_working_date'] : '';
                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $emp);
                         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $empname_us);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2),($rowcount), $id);
                         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3),($rowcount), $us_id);
                          $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +3 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4),($rowcount), $join);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5),($rowcount), $unit);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6),($rowcount), $dept);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7),($rowcount), $clas);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8),($rowcount), $termination);
                        $rowcount++;

                        $rowcount++;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Compensatory Accrued Details ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                        $worksheet->mergeCells('A' . $rowcount . ':D' . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $rowcount = $rowcount + 2;
                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No.  ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFill()->applyFromArray([
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                    ]);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Accrued Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Duration');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2,$rowcount)->getFill()->applyFromArray([
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                    ]);
                    
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Day Type');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        $rowcount = $rowcount + 1;



                        if (count($arr_data) > 0) {
                            $used = 0;
                            foreach ($arr_data as $val) {
                                $used = $used + 1;
                                $att_date = $val['0']['att_date'];
                                $duration = $val['0']['duration'];
                                $dys = $val['0']['weekoff'] . ' ' . $val['0']['holiday'];

                                $columncount = 0;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $used);
                                 $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow(0 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $att_date);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $duration);
                                 $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount +2 , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $dys);
                                $rowcount++;
                            }

                            $balance_levv = isset($value['eligibility']['0']['0']['blnce']) ? $value['eligibility']['0']['0']['blnce'] : 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 0), $rowcount, 'Available Comp Off Leave Balance : ' . $balance_levv);

                            $worksheet->mergeCells('A' . $rowcount . ':K' . $rowcount);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 0), $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                        } else {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Record Found');
                        }

                        $arr_data = $value['leaves'];


                        $rowcount++;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Comp Off Leave List ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                        $worksheet->mergeCells('A' . $rowcount . ':D' . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );


                        $rowcount = $rowcount + 1;
                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No.  ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'From Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'To Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2,$rowcount)->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                        ]);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Status');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3,$rowcount)->getFill()->applyFromArray([
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'startcolor' => ['rgb' => 'D9D9D9'] // Light grey
                    ]);
                        $rowcount = $rowcount + 2;

                        if (count($arr_data) > 0) {
                            $used = 0;
                            foreach ($arr_data as $val) {
                                $used = $used + 1;
                                $att_date = $val['leaveentries']['FROMDATE'] . ' ' . $val['leaveentries']['FROMHALF'];
                                $duration = $val['leaveentries']['TODATE'] . ' ' . $val['leaveentries']['TOHALF'];
                                $dys = $val['leaveentries']['LEAVESTATUS'];

                                $columncount = 0;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $used);
                                $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow(0  , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $att_date);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $duration);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $dys);
                                $rowcount++;
                            }

                            $balance_levv = isset($value['eligibility']['0']['0']['blnce']) ? $value['eligibility']['0']['0']['blnce'] : 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 0), $rowcount, 'Available Comp off Leave Balance ');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, $balance_levv);
                            $objPHPExcel->getActiveSheet()
                                    ->getStyleByColumnAndRow($columncount + 2  , $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                        } else {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Record Found');
                        }

                        $rowcount++;
                        $rowcount++;
                    }
                }



                $objPHPExcel->getActiveSheet()->setTitle('Leave Compoff Report ');
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
                $this->render('compoff');
                break;
        }
     }

      private function getLeaveCycle($policyType, $cycleStart, $cycleEnd, $selectedDate, $dynamicPeriod = null)
{
    $selected = new DateTime($selectedDate);

    // Handle invalid or default dates
    if ($cycleStart == '0000-00-00' || $cycleEnd == '0000-00-00') {
        $cycleStart = date('Y-04-01'); // Example default: start of current FY
        $cycleEnd = date('Y-03-31', strtotime('+1 year'));
    }

    $start = new DateTime($cycleStart);
    $end = new DateTime($cycleEnd);

    switch (strtoupper($policyType)) {
        case 'Y': // Yearly
        case 'P': // Periodic - same logic as yearly
            if ($selected >= $start && $selected <= $end) {
                // Within same year
            } elseif ($selected < $start) {
                $start->modify('-1 year');
                $end->modify('-1 year');
            } elseif ($selected > $end) {
                $start->modify('+1 year');
                $end->modify('+1 year');
            }
            break;

        case 'M': // Monthly
            // Compute current month's start and end
            $monthStart = new DateTime($selected->format('Y-m-01'));
            $monthEnd = clone $monthStart;
            $monthEnd->modify('last day of this month');

            if ($selected >= $monthStart && $selected <= $monthEnd) {
                $start = $monthStart;
                $end = $monthEnd;
            } elseif ($selected < $monthStart) {
                $monthStart->modify('-1 month');
                $monthEnd = clone $monthStart;
                $monthEnd->modify('last day of this month');
                $start = $monthStart;
                $end = $monthEnd;
            } elseif ($selected > $monthEnd) {
                $monthStart->modify('+1 month');
                $monthEnd = clone $monthStart;
                $monthEnd->modify('last day of this month');
                $start = $monthStart;
                $end = $monthEnd;
            }
            break;

        case 'Q': // Quarterly
            $month = (int)$selected->format('n');
            $quarterStartMonth = (floor(($month - 1) / 3) * 3) + 1;
            $start = new DateTime($selected->format('Y') . '-' . str_pad($quarterStartMonth, 2, '0', STR_PAD_LEFT) . '-01');
            $end = clone $start;
            $end->modify('+3 months')->modify('-1 day');
            break;

        case 'H': // Half-Yearly
            $month = (int)$selected->format('n');
            $startMonth = ($month <= 6) ? 1 : 7;
            $start = new DateTime($selected->format('Y') . '-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01');
            $end = clone $start;
            $end->modify('+6 months')->modify('-1 day');
            break;

        case 'D': // Dynamic period (rolling window)
            if (!$dynamicPeriod) {
                throw new Exception("Dynamic period value required for policy type D");
            }
            $end = clone $selected;
            $start = clone $selected;
            $start->modify("-{$dynamicPeriod} days");
            break;

        default:
            // fallback to given cycleStart/cycleEnd
            break;
    }

    return [
        'start' => $start->format('Y-m-d'),
        'end' => $end->format('Y-m-d')
    ];
}

      //leave balance report
private function generateleavebalancereportnew($mode)
    {
        ini_set('memory_limit', '1024M');
        $this->autoRender = false;
        $this->layout = '';


        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        //edited by athira on 10-07-2025
        $company_code = strtoupper($this->Session->read('company_code')); //Edited by Akshay on 14-5-2024
        $this->set('company_code',$company_code);
        // end

        //Build conditions based on criterias recieved
        //$fd=$arr_form_data['reportfrom'].' '.'00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);

        $report_month = $arr_form_data['reportfrom'];
        if ($arr_form_data['reportfrom'])
            $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));

        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
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

        $condition = 'and ed.status = 1';
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and ed.status in(1,2)";
        }

        $emp_branch_condition = "";
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        /* $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $emp_branch_condition = " and ed.branch_code ='" . $cur_emp_branch . "' ";
        } */



        //employee branch wise sorting ends here

        $arr_leavepolicydetails_for_template = array();
        $arr_empleaverequests2 = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $arr_leavepolicydetails_for_template2 = array();
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $year_con = "EmployeeDetails.emp_pkey = '$leavepolicygroupid' ";
                }
                if ($arr_form_data['select-criteria1'] == 'Units') {
                    $year_con = "EmployeeDetails.branch_code = '$leavepolicygroupid' ";
                }
                if ($arr_form_data['select-criteria1'] == 'LeaveType') {
                    //                    $year_con = "EmployeeDetails.branch_code = EmployeeDetails.branch_code";
                    if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                        $emp_status_condition = " and ed.status in (1,2)";
                    } else {
                        $emp_status_condition = " and ed.status=1";
                    }
                    $leave_type_branch_find = $this->EmployeeDetails->query("select distinct emp_branch from emp_proff ep 
join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID=ep.LEAVEPOLICY_GROUP_ID) 
join salary_head_items shi on (shi.salary_head_item_pkey=lp.salary_head_item_fkey) 
join branches br on (ep.emp_branch=br.branch_code)
join emp_details ed on (ed.emp_pkey=ep.emp_fkey)
where shi.salary_head_item_pkey=" . $leavepolicygroupid . " and br.status=1 " . $emp_status_condition);


                    $branch_array = array();
                    foreach ($leave_type_branch_find as $val) {

                        $branch = $val['ep']['emp_branch'];
                        $fin = $this->LeaveRequests->query("select start_month,end_month,fin_year,Year_status from fin_year where branch_code = '$branch' and vattr1 ='0' and fin_year='" . $from . "' and status=1");
                        $end_month = '';
                        $cur_year = '';
                        if (!empty($fin)) {
                            if (count($fin) > 1) {
                                foreach ($fin as $fin_val) {
                                    if ($fin_val['fin_year']['Year_status'] == 'OPEN') {
                                        $start_month = $fin_val['fin_year']['start_month'];
                                        $end_month = $fin_val['fin_year']['end_month'];
                                        $fin_year = $fin_val['fin_year']['fin_year'];
                                    }
                                }
                            } else {
                                $start_month = $fin[0]['fin_year']['start_month'];
                                $end_month = $fin[0]['fin_year']['end_month'];
                                $fin_year = $fin[0]['fin_year']['fin_year'];
                            }
                            $branch_array[] = array('branch' => $branch, 'start_month' => $start_month, 'end_month' => $end_month, 'fin_year' => $fin_year);
                        }
                    }
                } else {
                    if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array($year_con, "status in (1,2)")));
                    } else {
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array($year_con, "status" => 1)));
                    }
                    $emp_branch = isset($cur_emp_branch_find[0]['EmployeeDetails']['branch_code']) ? $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'] : '';
                    $fin = $this->LeaveRequests->query("select start_month,end_month,fin_year,Year_status from fin_year where branch_code = '$emp_branch' and vattr1 ='0' and fin_year='" . $from . "' and status=1");

                    $start_month = '';
                    $end_month = '';
                    $cur_year = '';
                    if (!empty($fin)) {
                        if (count($fin) > 1) {
                            foreach ($fin as $fin_val) {
                                if ($fin_val['fin_year']['Year_status'] == 'OPEN') {
                                    $start_month = $fin_val['fin_year']['start_month'];
                                    $end_month = $fin_val['fin_year']['end_month'];
                                    $cur_year = $fin_val['fin_year']['fin_year'];
                                }
                            }
                        } else {
                            $start_month = $fin['0']['fin_year']['start_month'];
                            $end_month = $fin['0']['fin_year']['end_month'];
                            $cur_year = $fin['0']['fin_year']['fin_year'];
                        }
                    }
                }


                $arr_empleaverequests = array();
                $arr_empleaverequests_leave = array();
                $arr_empleaverequests_leave_resign = array();
                if ($arr_form_data['select-criteria1'] == 'LeaveType') {

                    try {
                        $leave_days="NULL";
                        $just_test = array();
                        
                        // debug($cycle_end_date);
                        foreach ($branch_array as $branch) {
                            $cur_branch = $branch['branch'];
                            $start_month = $branch['start_month'];
                            $end_month = $branch['end_month'];
                            $cur_year = $branch['fin_year'];
                            $temp1[] = $this->LeaveRequests->query("(SELECT distinct ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,lp.CARRY_FORWARD_LIMIT,lp.minimum_service,
                            CONCAT(first_name, ' ', IFNULL(last_name, '')) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                            ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$from') leavebalance,
                            leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$cycle_end_date') yearlybalance,
                            leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey) leavetaken,
                            (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                            from emp_details ed
                            join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                            join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                            LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                            left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                            and ecf.emp_fkey=ed.emp_pkey  and ecf.status = 1)
                            join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                            join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                            left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                            left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                            where ed.emp_pkey=ed.emp_pkey 
                            and ed.status='1' " . $emp_branch_condition . "
                            and ed.branch_code='" . $cur_branch . "'
                            and lp.salary_head_item_fkey=" . $leavepolicygroupid . " and ep.joining_date <= '$end_month' and finyear.fin_year = '$cur_year' and finyear.vattr1=0 and finyear.status=1 group by ed.emp_pkey,LeaveType.item order by ed.branch_code)");
                          
                        }
                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            
                            foreach ($branch_array as $branch) {
                                $cur_branch = $branch['branch'];
                                $start_month = $branch['start_month'];
                                $end_month = $branch['end_month'];
                                $cur_year = $branch['fin_year'];
                                //edited by athira on 23-08-2025
                                $temp2[] = $this->LeaveRequests->query("(SELECT distinct ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,lp.CARRY_FORWARD_LIMIT,lp.minimum_service,
                                CONCAT(first_name, ' ', IFNULL(last_name, '')) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                                ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$from') leavebalance,
                                leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$cycle_end_date') yearlybalance,
                                leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey) leavetaken, 
                                (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey ORDER BY encash.creation_date LIMIT 1) AS encashed_leave,
								(select count(*) as count from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) where remarks = 'terminate' and salary_paid = 'Y' and leave_encashment_master.status = 1 and emp_fkey = ed.emp_pkey) as terminate,
								(select leavepolicy.leave_encash_limit from emp_details join emp_proff ep on (emp_details.emp_pkey=ep.emp_fkey)
                                join leavepolicy on (leavepolicy.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID ) 
						        left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) 
					            where leavepolicy.status = 1 and is_leave_encash ='Y' and leave_encash_limit is NOT NULL and leavepolicy.salary_head_item_fkey='$leavepolicygroupid' and ed.emp_pkey = emp_details.emp_pkey) as leave_encash_limit
                                from emp_details ed
                                join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                                join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                                LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                                left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                                and ecf.emp_fkey=ed.emp_pkey  and ecf.status = 1)
                                join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                                join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                                left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 and termination.last_approved_working_date >= '$start_month' )
                                left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                                where ed.emp_pkey=ed.emp_pkey 
                                and ed.status='2' " . $emp_branch_condition . " 
                                and ed.branch_code='" . $cur_branch . "'
                                and lp.salary_head_item_fkey=" . $leavepolicygroupid . " and ep.joining_date <= '$end_month' and finyear.fin_year = '$cur_year' and finyear.vattr1=0 and finyear.status=1 group by ed.emp_pkey,LeaveType.item order by ed.branch_code)");
                                //end
                            }
                        }

                        //edited by antigravity on 22-01-2026 for minimum service logic
                        if (isset($temp1)) {
                            foreach ($temp1 as $t_key => $rows) {
                                if (!empty($rows)) {
                                    foreach ($rows as $r_key => $row) {
                                        $min_service = isset($row['lp']['minimum_service']) ? $row['lp']['minimum_service'] : 0;
                                        $joining_date = isset($row['info']['joining_date']) ? $row['info']['joining_date'] : '';
                                        if (!empty($min_service) && !empty($joining_date)) {
                                            $eligibility_date = date('Y-m-d', strtotime("+$min_service months", strtotime($joining_date)));
                                            if ($eligibility_date > $from) {
                                                $temp1[$t_key][$r_key]['0']['leavebalance'] = 0;
                                                // $temp1[$t_key][$r_key]['yearlybalance'] = 0;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if (isset($temp2)) {
                            foreach ($temp2 as $t_key => $rows) {
                                if (!empty($rows)) {
                                    foreach ($rows as $r_key => $row) {
                                        $min_service = isset($row['lp']['minimum_service']) ? $row['lp']['minimum_service'] : 0;
                                        $joining_date = isset($row['info']['joining_date']) ? $row['info']['joining_date'] : '';
                                        if (!empty($min_service) && !empty($joining_date)) {
                                            $eligibility_date = date('Y-m-d', strtotime("+$min_service months", strtotime($joining_date)));
                                            if ($eligibility_date > $from) {
                                                $temp2[$t_key][$r_key]['0']['leavebalance'] = 0;
                                                // $temp2[$t_key][$r_key]['yearlybalance'] = 0;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        //end
                    } catch (Exception $ex) {
                    }
                    $arr_leavepolicydetails_for_template2 = $arr_empleaverequests2;
                } else if ($arr_form_data['select-criteria1'] == 'Departments') {
                    try {
                        $leave_days="NULL";
                        
                        // debug($cycle_end_date);
                        $arr_empleaverequests_dept = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,lp.CARRY_FORWARD_LIMIT,lp.minimum_service,
                        CONCAT(first_name, ' ', IFNULL(last_name, '')) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                        ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$from') leavebalance,
                        leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$cycle_end_date') yearlybalance,
                        leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey) leavetaken,
                        (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey  ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
						from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                        and ecf.emp_fkey=ed.emp_pkey  and ecf.status = 1)
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                        where ed.emp_pkey=ed.emp_pkey 
                        and ed.status='1' " . $emp_branch_condition . "
                        and ed.branch_code=ed.branch_code
                        and lp.salary_head_item_fkey=" . $leavepolicygroupid . " and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.status=1 group by LeaveType.item)");

                        $arr_empleaverequests = $arr_empleaverequests_dept;

                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            
                            $arr_empleaverequests_dept_resign = $this->LeaveRequests->query("(SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,lp.CARRY_FORWARD_LIMIT,lp.minimum_service,
                            CONCAT(first_name, ' ', IFNULL(last_name, '')) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                            ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$from') leavebalance,
                            leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$cycle_end_date') yearlybalance,
                            leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey) leavetaken,
                            (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey  ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                            from emp_details ed
                            join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                            join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                            LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                            left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                            and ecf.emp_fkey=ed.emp_pkey  and ecf.status = 1)
                            join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                            join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                            left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                            left join fin_year as finyear on (ed.branch_code = finyear.branch_code)
                            where ed.emp_pkey=ed.emp_pkey 
                            and ed.status='2' " . $emp_branch_condition . " and termination.last_approved_working_date >= '$start_month'
                            and ed.branch_code=ed.branch_code
                            and lp.salary_head_item_fkey=" . $leavepolicygroupid . " and ep.joining_date <= '$end_month' and finyear.fin_year = '$from' and finyear.status=1 group by LeaveType.item)");
                            $arr_empleaverequests = array_merge($arr_empleaverequests_dept, $arr_empleaverequests_dept_resign);
                        }

                        //edited by antigravity on 22-01-2026 for minimum service logic
                        if (!empty($arr_empleaverequests)) {
                            foreach ($arr_empleaverequests as $key => $val) {
                                $min_service = isset($val['lp']['minimum_service']) ? $val['lp']['minimum_service'] : 0;
                                $joining_date = isset($val['info']['joining_date']) ? $val['info']['joining_date'] : '';
                                if (!empty($min_service) && !empty($joining_date)) {
                                    $eligibility_date = date('Y-m-d', strtotime("+$min_service months", strtotime($joining_date)));
                                    if ($eligibility_date > $from) {
                                        $arr_empleaverequests[$key]['0']['leavebalance'] = 0;
                                        // $arr_empleaverequests[$key]['yearlybalance'] = 0;
                                    }
                                }
                            }
                        }
                        //end
                    } catch (Exception $ex) {
                    }
                } 
                
                //edited by athira on 24-10-2025
                else if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    

                    // debug($leave_policy);

                    $salary_heads = $this->EmployeeDetails->query("
                        SELECT leavepolicy.salary_head_item_fkey,leavepolicy.leave_cycle_end_date,leavepolicy.leave_cycle_start_date,leavepolicy.leave_policy_type
                        FROM leavepolicy 
                        LEFT JOIN emp_proff 
                            ON leavepolicy.LEAVEPOLICY_GROUP_ID = emp_proff.LEAVEPOLICY_GROUP_ID
                        WHERE leavepolicy.status = 1 
                        AND emp_proff.emp_fkey = '$leavepolicygroupid'
                    ");


                    

                    
                           if (!empty($salary_heads)) {
                            $final_empleaverequests = array();
                            foreach ($salary_heads as $i => $head) {
                            $salary_head_item_fkey = $head['leavepolicy']['salary_head_item_fkey'];
                            $cycle_end_date = $head['leavepolicy']['leave_cycle_end_date'];

                             $policyType = $head['leavepolicy']['leave_policy_type'];
                                $cycleStart = $head['leavepolicy']['leave_cycle_start_date'];
                                $cycleEnd = $head['leavepolicy']['leave_cycle_end_date'];
                                $dynamicPeriod=isset($head['leavepolicy']['dynamic_period']) ? $head['leavepolicy']['dynamic_period'] : null;

                                $cycle = $this->getLeaveCycle($policyType, $cycleStart, $cycleEnd, $from);

                                $from_startdate = $cycle['start'];
                                $from_enddate   = $cycle['end'];
                                // debug($from_enddate);
                                

                                $sql=$this->LeaveRequests->query("SELECT leave_balance_inthe_year_fn($leavepolicygroupid,$salary_head_item_fkey,'$from_enddate') yearlybalance");
                               
                                // $yearly_balance=$sql[0][0]['yearly_balance'];
                                 $yearly_balances[$salary_head_item_fkey] = $sql[0][0]['yearlybalance'];
                            // $this->LeaveRequests->query("
                            //     CALL leave_start_end_prc('$leavepolicygroupid', $salary_head_item_fkey, '$from', @p_startdate, @p_enddate)
                            // ");

                            // $from_result = $this->LeaveRequests->query("
                            //     SELECT  @p_startdate AS start_date1, @p_enddate  AS end_date1
                            // ");

                            // $from_startdate = $from_result[0][0]['start_date1'];
                            // $from_enddate   = $from_result[0][0]['end_date1'];
                            //   leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$from_enddate') yearlybalance,
                                
                             
                    try {
                         $leave_days="NULL";
                         $arr_empleaverequests_emp = $this->LeaveRequests->query("
                        (SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,lp.CARRY_FORWARD_LIMIT,lp.salary_head_item_fkey,lp.minimum_service,
                        CONCAT(first_name, ' ', IFNULL(last_name, '')) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                        ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$from') leavebalance,
                      
                        leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey,'$from') leavetaken, 
                        (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey AND encash.approved_date BETWEEN '$from_startdate' AND  '$from_enddate' ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                        from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                        and ecf.emp_fkey=ed.emp_pkey and ecf.status = 1   AND ecf.leave_cycle_start_date >= '$from_startdate'
                        AND ecf.leave_cycle_end_date   <= '$from_enddate')
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        where ed.emp_pkey=" . $leavepolicygroupid . "
                        and ed.status=1 " . $emp_branch_condition . "
                        and ed.branch_code=ed.branch_code 
                        and lp.salary_head_item_fkey=$salary_head_item_fkey and ep.joining_date <= '$from_enddate' Group By LeaveType.item)
                        ");

                        
                        

                        $arr_empleaverequests = $arr_empleaverequests_emp;
                        if (!empty($arr_empleaverequests)) {
                    foreach ($arr_empleaverequests as $key => $empLeave) {
                        $leave_type_key = $empLeave['lp']['salary_head_item_fkey']; // adjust according to your query result array
                        if (isset($yearly_balances[$leave_type_key])) {
                            $arr_empleaverequests[$key]['yearlybalance'] = $yearly_balances[$leave_type_key];
                        } else {
                            $arr_empleaverequests[$key]['yearlybalance'] = 0;
                        }

                        //edited by antigravity on 22-01-2026 for minimum service logic
                        $min_service = isset($empLeave['lp']['minimum_service']) ? $empLeave['lp']['minimum_service'] : 0;
                        $joining_date = isset($empLeave['info']['joining_date']) ? $empLeave['info']['joining_date'] : '';
                        if (!empty($min_service) && !empty($joining_date)) {
                            $eligibility_date = date('Y-m-d', strtotime("+$min_service months", strtotime($joining_date)));
                            if ($eligibility_date > $from) {
                                $arr_empleaverequests[$key]['0']['leavebalance'] = 0;
                                // $arr_empleaverequests[$key]['yearlybalance'] = 0;
                            }
                        }
                        //end
                        }
                    }


                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

                            

                            //edited by athira on 23-08-2025
                             $arr_empleaverequests_emp_resign = $this->LeaveRequests->query("
                            (SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,lp.CARRY_FORWARD_LIMIT,lp.salary_head_item_fkey,lp.minimum_service,
                         CONCAT(first_name, ' ', IFNULL(last_name, '')) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                        ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$from') leavebalance,
                        leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey,'$from') leavetaken, 
                        (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey  ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                        from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                        and ecf.emp_fkey=ed.emp_pkey and ecf.status = 1   AND ecf.leave_cycle_start_date >= '$from_startdate'
                        AND ecf.leave_cycle_end_date   <= '$from_enddate')
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        where ed.emp_pkey=" . $leavepolicygroupid . "
                        and ed.status=2 " . $emp_branch_condition . "
                        and ed.branch_code=ed.branch_code 
                        and lp.salary_head_item_fkey=$salary_head_item_fkey and ep.joining_date <= '$from_enddate' Group By LeaveType.item)");
                            //end

                            $arr_empleaverequests = array_merge($arr_empleaverequests_emp, $arr_empleaverequests_emp_resign);

                               if (!empty($arr_empleaverequests)) {
                    foreach ($arr_empleaverequests as $key => $empLeave) {
                        $leave_type_key = $empLeave['lp']['salary_head_item_fkey']; // adjust according to your query result array
                        if (isset($yearly_balances[$leave_type_key])) {
                            $arr_empleaverequests[$key]['yearlybalance'] = $yearly_balances[$leave_type_key];
                        } else {
                            $arr_empleaverequests[$key]['yearlybalance'] = 0;
                        }

                        //edited by antigravity on 22-01-2026 for minimum service logic
                        $min_service = isset($empLeave['lp']['minimum_service']) ? $empLeave['lp']['minimum_service'] : 0;
                        $joining_date = isset($empLeave['info']['joining_date']) ? $empLeave['info']['joining_date'] : '';
                        if (!empty($min_service) && !empty($joining_date)) {
                            $eligibility_date = date('Y-m-d', strtotime("+$min_service months", strtotime($joining_date)));
                            if ($eligibility_date > $from) {
                                $arr_empleaverequests[$key]['0']['leavebalance'] = 0;
                                // $arr_empleaverequests[$key]['yearlybalance'] = 0;
                            }
                        }
                        //end
                        }
                    }
                   

                        }
                  
                        if (!empty($arr_empleaverequests)) {
                            $final_empleaverequests = array_merge($final_empleaverequests, $arr_empleaverequests);
                        }
                    } catch (Exception $ex) {
                    }
                      }
                            $arr_empleaverequests = $final_empleaverequests;
                        }
                }

else {
    $employees = $this->EmployeeDetails->query("
        SELECT ed.emp_pkey, ep.LEAVEPOLICY_GROUP_ID, ep.joining_date
        FROM emp_details ed
        JOIN emp_proff ep ON ed.emp_pkey = ep.emp_fkey
        WHERE ed.branch_code = '$leavepolicygroupid'
          AND ed.status IN ('1', '2')
    ");

    if (!empty($employees)) {
        $arr_empleaverequests = array();

        foreach ($employees as $emp) {
            $emp_pkey = $emp['ed']['emp_pkey'];
            $leavepolicygroupid_emp = $emp['ep']['LEAVEPOLICY_GROUP_ID'];

            $salary_heads = $this->EmployeeDetails->query("
                SELECT salary_head_item_fkey, leave_cycle_start_date, leave_cycle_end_date, leave_policy_type, dynamic_period
                FROM leavepolicy
                WHERE LEAVEPOLICY_GROUP_ID = '$leavepolicygroupid_emp'
                  AND status = 1
            ");

            if (!empty($salary_heads)) {
                foreach ($salary_heads as $head) {
                    $salary_head_item_fkey = $head['leavepolicy']['salary_head_item_fkey'];
                    $cycleStart = $head['leavepolicy']['leave_cycle_start_date'];
                    $cycleEnd = $head['leavepolicy']['leave_cycle_end_date'];
                    $policyType = $head['leavepolicy']['leave_policy_type'];
                    $dynamicPeriod = isset($head['leavepolicy']['dynamic_period']) ? $head['leavepolicy']['dynamic_period'] : null;

                    $cycle = $this->getLeaveCycle($policyType, $cycleStart, $cycleEnd, $from, $dynamicPeriod);
                    $from_startdate = $cycle['start'];
                    $from_enddate   = $cycle['end'];

                    $sql = $this->LeaveRequests->query("
                        SELECT leave_balance_inthe_year_fn($emp_pkey, $salary_head_item_fkey,'$from_enddate') AS yearlybalance
                    ");
                    $yearly_balances[$salary_head_item_fkey] = $sql[0][0]['yearlybalance'];

                    try {
                         $arr_emp_leave = $this->LeaveRequests->query("
                            SELECT ed.emp_pkey, termination.last_approved_working_date, ed.status, Units.branch_name, ed.branch_code, lp.CARRY_FORWARD_LIMIT, lp.salary_head_item_fkey, lp.minimum_service,
                                   CONCAT(first_name, ' ', IFNULL(last_name, '')) AS emp_name, info.*, LeaveType.item AS leave_type, lp.alloted_leave_forthe_year, lp.leave_policy_type,
                                   IFNULL(ecf.carry_forwarded,0) AS carryforwarded,
                                   leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$from') AS leavebalance,
                                   leave_taken_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$from') AS leavetaken,
                                   (SELECT SUM(encash.approved_days) FROM leave_encashment_master AS encash
                                    WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey
                                      AND encash.is_approved = 'Y'
                                      AND encash.status = 1
                                      AND encash.emp_fkey = ed.emp_pkey
                                    ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                            FROM emp_details ed
                            JOIN emp_proff ep ON ed.emp_pkey = ep.emp_fkey
                            JOIN leavepolicy lp ON lp.LEAVEPOLICY_GROUP_ID = ep.LEAVEPOLICY_GROUP_ID AND lp.status = 1
                            LEFT JOIN branches Units ON Units.branch_code = ed.branch_code
                            LEFT JOIN emp_leave_balance_year AS ecf ON ecf.salary_head_item_fkey = lp.salary_head_item_fkey
                                AND ecf.emp_fkey = ed.emp_pkey
                                AND ecf.status = 1
                                AND ecf.leave_cycle_start_date >= '$from_startdate'
                                AND ecf.leave_cycle_end_date <= '$from_enddate'
                            JOIN salary_head_items LeaveType ON LeaveType.salary_head_item_pkey = lp.salary_head_item_fkey
                            JOIN employee_info AS info ON info.emp_pkey = ed.emp_pkey
                            LEFT JOIN termination AS termination ON termination.emp_fkey = ep.emp_fkey AND termination.status = 1
                            WHERE ed.emp_pkey = $emp_pkey and lp.salary_head_item_fkey=$salary_head_item_fkey
                              AND ed.branch_code = '$leavepolicygroupid'
                              AND ed.status IN ('1')
                            GROUP BY ed.emp_pkey,LeaveType.item
                        ");

                                            if (!empty($arr_emp_leave)) {
    foreach ($arr_emp_leave as $k => $empLeave) {
        $leave_type_key = $empLeave['lp']['salary_head_item_fkey']; // get salary head for this row
         $arr_emp_leave[$k]['yearlybalance'] = isset($yearly_balances[$leave_type_key]) 
            ? $yearly_balances[$leave_type_key] 
            : 0;

        //edited by antigravity on 22-01-2026 for minimum service logic
        $min_service = isset($empLeave['lp']['minimum_service']) ? $empLeave['lp']['minimum_service'] : 0;
        $joining_date = isset($empLeave['info']['joining_date']) ? $empLeave['info']['joining_date'] : '';
        if (!empty($min_service) && !empty($joining_date)) {
            $eligibility_date = date('Y-m-d', strtotime("+$min_service months", strtotime($joining_date)));
            if ($eligibility_date > $from) {
                // $arr_emp_leave[$k]['yearlybalance'] = 0;
                $arr_emp_leave[$k]['0']['leavebalance'] = 0;
            }
        }
        //end
    }
    $arr_empleaverequests = array_merge($arr_empleaverequests, $arr_emp_leave);
}

                        
                         if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

                            

                            //edited by athira on 23-08-2025
                             $arr_emp_leave_resign = $this->LeaveRequests->query("
                            (SELECT ed.emp_pkey,termination.last_approved_working_date,ed.status,Units.branch_name,ed.branch_code,lp.CARRY_FORWARD_LIMIT,lp.salary_head_item_fkey,lp.minimum_service,
                        CONCAT(first_name, ' ', IFNULL(last_name, '')) AS emp_name, info.*,LeaveType.item AS leave_type,lp.alloted_leave_forthe_year,lp.leave_policy_type,
                        ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey,'$from') leavebalance,
                        leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey,'$from') leavetaken, 
                        (select sum(encash.approved_days) as approved_days FROM leave_encashment_master as encash WHERE LeaveType.salary_head_item_pkey = encash.salary_head_item_fkey and encash.is_approved = 'Y' AND encash.status = 1 AND encash.emp_fkey = ed.emp_pkey  ORDER BY encash.creation_date LIMIT 1) AS encashed_leave
                        from emp_details ed
                        join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                        join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                        LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                        left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey
                        and ecf.emp_fkey=ed.emp_pkey and ecf.status = 1   AND ecf.leave_cycle_start_date >= '$from_startdate'
                        AND ecf.leave_cycle_end_date   <= '$from_enddate')
                        join salary_head_items LeaveType on (LeaveType.salary_head_item_pkey=lp.salary_head_item_fkey)
                        join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                        left join termination as termination on (termination.emp_fkey=ep.emp_fkey and termination.status=1 )
                        where ed.emp_pkey=" . $emp_pkey . " and lp.salary_head_item_fkey=$salary_head_item_fkey
                        and ed.status='2' " . $emp_branch_condition . "
                        and ed.branch_code=ed.branch_code 
                        and lp.salary_head_item_fkey=lp.salary_head_item_fkey and ep.joining_date <= '$from_enddate' GROUP BY ed.emp_pkey,LeaveType.item)");
                            //end

                          

 if (!empty($arr_emp_leave_resign)) {
    foreach ($arr_emp_leave_resign as $k => $empLeave) {
        $leave_type_key = $empLeave['lp']['salary_head_item_fkey'];
         $arr_emp_leave_resign[$k]['yearlybalance'] =
            isset($yearly_balances[$leave_type_key]) ? $yearly_balances[$leave_type_key] : 0;

        //edited by antigravity on 22-01-2026 for minimum service logic
        $min_service = isset($empLeave['lp']['minimum_service']) ? $empLeave['lp']['minimum_service'] : 0;
        $joining_date = isset($empLeave['info']['joining_date']) ? $empLeave['info']['joining_date'] : '';
        if (!empty($min_service) && !empty($joining_date)) {
            $eligibility_date = date('Y-m-d', strtotime("+$min_service months", strtotime($joining_date)));
            if ($eligibility_date > $from) {
                // $arr_emp_leave_resign[$k]['yearlybalance'] = 0;
                $arr_emp_leave_resign[$k]['0']['leavebalance'] = 0;
            }
        }
        //end
    }

    // correct merge
    $arr_empleaverequests = array_merge($arr_empleaverequests, $arr_emp_leave_resign);
}

   

                        }

                        
   


                    } catch (Exception $ex) {
                        // old CakePHP style: just skip on error
                    }
                }
            }
        }
    }
}


                 if (!empty($arr_empleaverequests)) { //LeaveType criteria will not enter here. By ***ARUL P DAS on 21_3_2020
                    $arr_leavepolicydetails_for_template[] = array(
                        'summary' => $arr_empleaverequests,
                        // 'leavebalance'=> $yearly_balances[$salary_head_item_fkey],
                        // 'summary' => isset($arr_empleaverequests)?$arr_empleaverequests:array(),
                    );
                }
                //end
            
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }

        if ($arr_form_data['select-criteria1'] == 'LeaveType') { //This leave type has separate variable. by ***ARUL P DAS on 21_3_2020
            //            $arr_leavepolicydetails_for_template = $arr_leavepolicydetails_for_template2; //The $arr_leavepolicydetails_for_template2 is calculated in LeaveType section.
            if (isset($temp1)) {
                foreach ($temp1 as $val) {
                    if (!empty($val)) {
                        //                debug($val[0]['LeaveType']['leave_type']);
                        $leave[$val[0]['LeaveType']['leave_type']]['summary'][] = $val;
                    }
                }
            }
            if (isset($temp2)) {
                foreach ($temp2 as $val) {
                    if (!empty($val)) {
                        //                debug($val[0]['LeaveType']['leave_type']);
                        $leave[$val[0]['LeaveType']['leave_type']]['summary'][] = $val;
                    }
                }
            }
            if (isset($leave)) {
                $arr_leavepolicydetails_for_template = $leave;
            }
        }
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        // debug($arr_leavepolicydetails_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->set('cur_year', $cur_year);

        $this->set('from', $from);


        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('dates', $from);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf':
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportleavebalance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavebalance.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_LeaveBalanceReport.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Balance Report - " . $from);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:P1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:P2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 3;
                if (count($arr_leavepolicydetails_for_template) != 0) {
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $i = 0;
                        $arr_data = $value['summary'];
                        if (count($arr_data) > 0) {
                            $i += 1;
                            $le = 'Leave Balance Reports of ';
                            if ($cr == 'Departments') {
                                $dep = isset($value['summary']['0']['Departments']['dept_name']) ? $value['summary'][0]['Departments']['dept_name'] : '';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $dep);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                                $worksheet->mergeCells('A' . $rowcount . ':P' . $rowcount);
                                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                );
                            } else if ($cr == 'Units') {
                                $brn = isset($value['summary'][0]['Units']['branch_name']) ? $value['summary'][0]['Units']['branch_name'] : '';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $brn);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                                $worksheet->mergeCells('A' . $rowcount . ':P' . $rowcount);
                                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                );
                            } else if ($cr == 'EmployeeDetails') {
                                $empstatus = isset($value['summary'][0]['ed']['status']) && $value['summary'][0]['ed']['status'] == "2" ? '(Resigned)' : '';
                                $name = isset($value['summary'][0]['0']['emp_name']) ? $value['summary'][0]['0']['emp_name'] : '';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $name . $empstatus);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                                $worksheet->mergeCells('A' . $rowcount . ':P' . $rowcount);
                                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                );
                            } else {
                                $type = isset($value['summary'][0][0]['LeaveType']['leave_type']) ? $value['summary'][0][0]['LeaveType']['leave_type'] : '';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $type);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                                $worksheet->mergeCells('A' . $rowcount . ':P' . $rowcount);
                                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                                );
                            }

                            $rowcount = $rowcount + 1;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                            //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee ID');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                            //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            //                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Joining Date');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Date Of Joining');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                            //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 3, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                            //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Designation');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                            //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                            //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 6, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Termination Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                            //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 7, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Leave Type');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Leave Policy');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Allotted Leave For The year');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Carry Forwarded');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Leave Taken');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Encashed Leaves');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'Eligibility For Selected Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);

                             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, 'Leave Balance (End Of Period)');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);
                            //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 12, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                            $rowcount = $rowcount + 1;
                            foreach ($arr_data as $val) {
                                if ($cr != "LeaveType") {
                                    $leavetaken = $val['0']['leavetaken'];
                                    $empstatus = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : '';
                                    $empname = isset($val['0']['emp_name']) ? $val['0']['emp_name'] : '';
                                    $emp = $empname . $empstatus;
                                    $typ = isset($val['LeaveType']['leave_type']) ? $val['LeaveType']['leave_type'] : '';

                                    //$levbalnce = isset($val['0']['leavebalance']) ? round($val['0']['leavebalance'], 1) : 0;
                                    $id = isset($val['info']['employee_id']) ? $val['info']['employee_id'] : '';
                                    $clas = isset($val['info']['designation']) ? $val['info']['designation'] : '';
                                    $join = isset($val['info']['joining_date'])
                                    ? date('d-m-Y', strtotime($val['info']['joining_date']))
                                    : '';

                                    $dept = isset($val['info']['department']) ? $val['info']['department'] : '';
                                    $unit = isset($val['info']['branch']) ? $val['info']['branch'] : '';
                                    $carry = isset($val['0']['carryforwarded']) ? $val['0']['carryforwarded'] : 0;
                                    //$encashed_leaves = isset($val['0']['encashed_leave'])?$val['0']['encashed_leave']:'0';
                                    $termination = isset($val['termination']['last_approved_working_date']) ? $val['termination']['last_approved_working_date'] : '';
                                    if ($val['lp']['leave_policy_type'] == 'Y') {
                                        $type = 'Yearly';
                                    } else if ($val['lp']['leave_policy_type'] == 'M') {
                                        $type = 'Monthly';
                                    } else {
                                        $type = 'Present Days';
                                    }
                                    if ($val['lp']['leave_policy_type'] == 'P') {
                                        $lp = '0';
                                    } else {
                                        $lp = isset($val['lp']['alloted_leave_forthe_year']) ? round($val['lp']['alloted_leave_forthe_year'], 1) : 0;
                                    }
                                    $terminate = isset($val['0']['terminate']) ? $val['0']['terminate'] : 0;
                                    $limit = isset($val['0']['leave_encash_limit']) ? $val['0']['leave_encash_limit'] : 0;
                                    $limit = $encashed_leaves = isset($val['0']['encashed_leave']) ? $val['0']['encashed_leave'] : '0';
                                    //									if($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0 ){ 
                                    //									$encashed_leaves = $limit - $val['0']['leavetaken'];
                                    //									}else{
                                    //									$encashed_leaves = isset($val['0']['encashed_leave'])?$val['0']['encashed_leave']:'0'; 
                                    //									}
                                    if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                        //$levbalnce ='0';
                                    } else {
                                        $levbalnce = isset($val['0']['leavebalance']) ? round($val['0']['leavebalance'], 1) : 0;
                                        //edited by athira on 24-10-2025
                                        $yearbalnce = isset($val['yearlybalance']) ? round($val['yearlybalance'], 1) : 0;
                                        //end
                                    }
                                    $columncount = 0;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i++);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $emp);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $id);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $join);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $unit);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $clas);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $dept);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $termination);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $typ);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $type);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $lp);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $carry);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $leavetaken);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $encashed_leaves);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $levbalnce);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $yearbalnce);
                                    $objPHPExcel->getActiveSheet()->getStyle("K{$rowcount}:P{$rowcount}")
                                        ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $rowcount++;
                                } else {
                                    foreach ($val as $subval) {
                                        $leavetaken = $subval['0']['leavetaken'];
                                        $empstatus = isset($subval['ed']['status']) && $subval['ed']['status'] == "2" ? '(Resigned)' : '';
                                        $empname = isset($subval['0']['emp_name']) ? $subval['0']['emp_name'] : '';
                                        $emp = $empname . $empstatus;
                                        $typ = isset($subval['LeaveType']['leave_type']) ? $subval['LeaveType']['leave_type'] : '';

                                        //$levbalnce = isset($subval['0']['leavebalance']) ? round($subval['0']['leavebalance'], 1) : 0;
                                        $id = isset($subval['info']['employee_id']) ? $subval['info']['employee_id'] : '';
                                        $clas = isset($subval['info']['designation']) ? $subval['info']['designation'] : '';
                                        $join = isset($subval['info']['joining_date'])
                                            ? date('d-m-Y', strtotime($subval['info']['joining_date']))
                                            : '';

                                        $dept = isset($subval['info']['department']) ? $subval['info']['department'] : '';
                                        $unit = isset($subval['info']['branch']) ? $subval['info']['branch'] : '';
                                        $carry_forward_limit = isset($subval['lp']['CARRY_FORWARD_LIMIT']) ? $subval['lp']['CARRY_FORWARD_LIMIT'] : '';
                                        //$encashed_leaves = isset($subval['0']['encashed_leave'])?$subval['0']['encashed_leave']:'0';
                                        $carry = isset($subval['0']['carryforwarded']) ? $subval['0']['carryforwarded'] : 0;
                                        $carry_to_show = min($carry, $carry_forward_limit);


                                        $termination = isset($subval['termination']['last_approved_working_date']) ? $subval['termination']['last_approved_working_date'] : '';
                                        if ($subval['lp']['leave_policy_type'] == 'Y') {
                                            $type = 'Yearly';
                                        } else if ($subval['lp']['leave_policy_type'] == 'M') {
                                            $type = 'Monthly';
                                        } else {
                                            $type = 'Present Days';
                                        }
                                        if ($subval['lp']['leave_policy_type'] == 'P') {
                                            $lp = '0';
                                        } else {
                                            $lp = isset($subval['lp']['alloted_leave_forthe_year']) ? round($subval['lp']['alloted_leave_forthe_year'], 1) : 0;
                                        }
                                        $terminate = isset($subval['0']['terminate']) ? $subval['0']['terminate'] : 0;
                                        $limit = isset($subval['0']['leave_encash_limit']) ? $subval['0']['leave_encash_limit'] : 0;
                                        $limit = $encashed_leaves = isset($subval['0']['encashed_leave']) ? $subval['0']['encashed_leave'] : 0;
                                        //									if($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0 ){ 
                                        //									$encashed_leaves = $limit - $subval['0']['leavetaken'];
                                        //									}else{
                                        //									$encashed_leaves = isset($subval['0']['encashed_leave'])?$subval['0']['encashed_leave']:'0'; 
                                        //									}
                                        if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                            //$levbalnce ='0';
                                        } else {
                                            $levbalnce = isset($subval['0']['leavebalance']) ? round($subval['0']['leavebalance'], 1) : 0;
                                            //edited by athira on 24-10-2025
                                            $yearbalnce = isset($val['yearlybalance']) ? round($val['yearlybalance'], 1) : 0;
                                            //end
                                        }
                                        $columncount = 0;
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i++);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $emp);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $id);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $join);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $unit);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $clas);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $dept);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $termination);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $typ);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $type);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $lp);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $carry_to_show);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $leavetaken);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $encashed_leaves);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $levbalnce);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $yearbalnce);
                                        $objPHPExcel->getActiveSheet()->getStyle("K{$rowcount}:P{$rowcount}")
                                        ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                        $rowcount++;
                                    }
                                }
                            }
                            $rowcount++;
                        }
                    }
                } else {
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No Data Available With The Selected Criteria");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                }
                $objPHPExcel->getActiveSheet()->setTitle('Leave Balance Report');
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
                $this->render('reportleavebalancenew');
                break;
        }
    }


     private function generatecompoffreport_new($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        //edited by athira on 07-07-2025
        $company_code=$this->Session->read('company_code');
        $this->set('company_code',$company_code);
        //end
        // $report_month = $arr_form_data['reportfrom'];
        // $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        // $to = date("Y-m-d", strtotime(date("Y-m-d", strtotime($from)) . " + 1 year"));
        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));

        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));

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
        try {
            $salary_head_pkey = $this->LeaveRequests->query("select salary_head_item_pkey from salary_head_items where occurance = 'COFF' ");
            $salary_head_pkey = isset($salary_head_pkey['0']['salary_head_items']['salary_head_item_pkey']) ? $salary_head_pkey['0']['salary_head_items']['salary_head_item_pkey'] : 0;
             // edited by athira on 07-04-2026
            if ($salary_head_pkey == 0) {
                $salary_head_pkey_find = $this->LeaveRequests->query("select salary_head_item_pkey from salary_head_items where item like '%Comp%Off%' and status=1");
                $salary_head_pkey = isset($salary_head_pkey_find['0']['salary_head_items']['salary_head_item_pkey']) ? $salary_head_pkey_find['0']['salary_head_items']['salary_head_item_pkey'] : 0;
            }
            // ended by athira on 07-04-2026
        } catch (Exception $ex) {
        }




        $arr_leavepolicydetails_for_template = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

                    /* @var $arr_empleave_eligiility type */
                    $arr_emps_dets = $this->LeaveRequests->query("SELECT employee_info.*
                                                                FROM `employee_info`
                                                                WHERE `emp_pkey` = '$leavepolicygroupid' limit 50
                                                                ");
                    $arr_emp_status = $this->LeaveRequests->query("select  emp_details.status from emp_details where emp_pkey =$leavepolicygroupid ");

                    // $empid = $arr_leavepolicydetails_for_template['0']['emp_dets']['0']['employee_info']['emp_pkey'];
                    $arr_termin = $this->LeaveRequests->query(" select last_approved_working_date from termination where termination.emp_fkey = $leavepolicygroupid");
                    //debug($arr_termin);
                    try {
                        //$arr_empleaverequests = $this->LeaveRequests->query("select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and (weekoff != '' or holiday != '') and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '2' ))");
                        $arr_empleaverequests = $this->LeaveRequests->query("select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and (weekoff != '' or holiday != '') and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '2' )) union select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and  holiday != '' and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '4' ))");
                    } catch (Exception $ex) {
                    }

                    $leavepolicy=$this->LeaveRequests->query("SELECT leave_policy_type,leave_cycle_start_date,leave_cycle_end_date FROM leavepolicy WHERE LEAVEPOLICY_GROUP_ID IN(SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$leavepolicygroupid' AND salary_head_item_fkey='$salary_head_pkey' and status=1)");
                    
                    $leave_policy_type=isset($leavepolicy[0]['leavepolicy']['leave_policy_type']) ? $leavepolicy[0]['leavepolicy']['leave_policy_type'] : '';


                    $year = date("Y",  strtotime($from));

                    try {
                        $leave_days="NULL";
                        $arr_empleave_eligiility = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$leavepolicygroupid','$salary_head_pkey',$leave_days) as blnce ");
                    } catch (Exception $ex) {
                    }
                } else {
                    $arr_empleaverequests = $this->LeaveRequests->query("select * from leaveentries where salary_head_item_fkey = $salary_head_pkey and EMP_fkey = '$leavepolicygroupid' ");
                }



                // $arr_empleavetaken = $this->LeaveRequests->query("select LEAVEENTRYID,ed.status,salary_head_item_fkey,applied_date,LEAVESTATUS,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,concat(ed.first_name,' ',ed.last_name) applied_name,Autherized_date,APPROVED_date,contact_person,contact_No,Reason,REMARKS,leave_days,(select concat(first_name,' ',last_name) from emp_details where emp_pkey = leaveentries.ISAutherizedby) as Authorized_name,(SELECT CONCAT(first_name,'',last_name) from emp_details where emp_pkey = leaveentries.APPROVEDBY) approved_name from leaveentries
                //                                                     left join emp_details ed on (ed.emp_pkey = leaveentries.EMP_fkey)
                //                                                     where leaveentries.salary_head_item_fkey = '$salary_head_pkey' and EMP_fkey = '$leavepolicygroupid' and TODATE between '$from' and '$to'  ");
                  
                $arr_empleavetaken = $this->LeaveRequests->query("SELECT Leavestatus,leave_date,leave_session FROM emp_leave_transactions WHERE LEAVEENTRYID IN(SELECT LEAVEENTRYID FROM leaveentries WHERE leaveentries.salary_head_item_fkey = '$salary_head_pkey' and EMP_fkey = '$leavepolicygroupid' and TODATE between '$from' and '$to' ) ");

                $arr_leavepolicydetails_for_template[] = array(
                    'emp_dets' => $arr_emps_dets,
                    'summary' => $arr_empleaverequests,
                    'eligibility' => $arr_empleave_eligiility,
                    'leaves' => $arr_empleavetaken,
                    'termin' => $arr_termin,
                    'leavepolicy' =>$leave_policy_type,
                    'status' => $arr_emp_status
                );
            }
        } else {
            echo "<div style='color:red' ><h3>No record Found</h3></div>";
            die();
        }

        // debug($arr_leavepolicydetails_for_template);

        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $this->set('arr_termin', $arr_termin);
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
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('compoff');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavecompoffssummary.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "CompOffDetailsReport.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Comp Off Details Report ");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $rowcount = 3;
                $i = 0;

              
                foreach ($arr_leavepolicydetails_for_template as $value) {
    if (!empty($value['summary'])) {
        $i++;
        $emp = $value['emp_dets'][0]['employee_info'];

        // Employee header
        $empstatus = isset($value['status'][0]['emp_details']['status']) && $value['status'][0]['emp_details']['status'] == "2" ? '(Resigned)' : '';
        $type = $value['leavepolicy'];

        switch ($type) {
            case 'M': $type_label = 'Monthly'; break;
            case 'Y': $type_label = 'Yearly'; break;
            case 'Q': $type_label = 'Quarterly'; break;
            case 'H': $type_label = 'Half Yearly'; break;
            case 'D': $type_label = 'Running Days'; break;
            case 'P': $type_label = 'Present Days'; break;
            default: $type_label = $type; break;
        }

        $worksheet->setCellValue('A' . $rowcount, 'Employee Name : ' . $emp['EmpName'] . ' ' . $empstatus);
        // $worksheet->mergeCells('A' . $rowcount . ':F' . $rowcount);
        $worksheet->setCellValue('B' . $rowcount, 'Leave Policy Type : ' . $type_label);
        // $worksheet->mergeCells('G' . $rowcount . ':L' . $rowcount);
        $worksheet->getStyle('A' . $rowcount . ':L' . $rowcount)->getFont()->setBold(true);
        $rowcount += 2;

        // Table header
        $headers = [
            'Employee Name',
            'Employee ID',
            'Date Of Join',
            'Branch',
            'Department',
            'Designation',
            'Transaction Type',
            'Accrued / Utilized Date',
            'Day',
            'Duration',
            'Day Type',
            'Status'
        ];

        // Add US format columns if needed
        $cols = $headers;
        if ($company_code == 'GLET' || $company_code == 'SRTS') {
            array_splice($cols, 1, 0, ['Employee Name (US Format)']);
            array_splice($cols, 3, 0, ['Employee ID (US Format)']);
        }

        $colIndex = 0;
        foreach ($cols as $header) {
            $worksheet->setCellValueByColumnAndRow($colIndex, $rowcount, $header);
            $worksheet->getStyleByColumnAndRow($colIndex, $rowcount)->getFont()->setBold(true);
            $colIndex++;
        }
        $rowcount++;

        // 1️⃣ Accrued details
        $arr_data = $value['summary'];
        $count = 0;
        foreach ($arr_data as $val) {
            $count++;
            $colIndex = 0;
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['EmpName']);
            if ($company_code == 'GLET' || $company_code == 'SRTS')
                $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['EmpUSName']);
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['employee_id']);
            if ($company_code == 'GLET' || $company_code == 'SRTS')
                $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['emp_us_id']);
            // $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['joining_date']);
             $joiningDate = date('d-m-Y', strtotime($emp['joining_date']));
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $joiningDate);
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['branch']);
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['department']);
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['designation']);
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, 'Accrued');
            // $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $val[0]['att_date']);
            $leaveDate = date('d-m-Y', strtotime($val[0]['att_date']));
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $leaveDate);
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, '');
            $worksheet->setCellValueByColumnAndRow($colIndex, $rowcount, (string)$val[0]['duration']);
            $worksheet->getStyleByColumnAndRow($colIndex, $rowcount)
          ->getAlignment()
          ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
          $colIndex++;
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $val[0]['weekoff'] . ' ' . $val[0]['holiday']);
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, 'Accrued');
            $rowcount++;
        }

        // 2️⃣ Utilized details
        $leaves = $value['leaves'];
        foreach ($leaves as $lv) {
            if($lv['emp_leave_transactions']['leave_session']==1){
                $day=0.5 . " (First Half) ";
            }
            if($lv['emp_leave_transactions']['leave_session']==2){
                $day=0.5 . " (Second Half) ";
            }
            else if ($lv['emp_leave_transactions']['leave_session']==3){
                $day=1 . " (Full Day) ";
            }
            $colIndex = 0;
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['EmpName']);
            if ($company_code == 'GLET' || $company_code == 'SRTS')
                $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['EmpUSName']);
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['employee_id']);
            if ($company_code == 'GLET' || $company_code == 'SRTS')
                $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['emp_us_id']);
            // $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['joining_date']);
            // Joining Date
            $joiningDate = date('d-m-Y', strtotime($emp['joining_date']));
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $joiningDate);

            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['branch']);
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['department']);
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $emp['designation']);
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, 'Utilized');
            // $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $lv['emp_leave_transactions']['leave_date']);
            // Leave Date
            $leaveDate = date('d-m-Y', strtotime($lv['emp_leave_transactions']['leave_date']));
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $leaveDate);
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $day);
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, '');
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, '');
            $worksheet->setCellValueByColumnAndRow($colIndex++, $rowcount, $lv['emp_leave_transactions']['Leavestatus']);
            $rowcount++;
        }

     
        $rowcount += 3;
    }
}



                $objPHPExcel->getActiveSheet()->setTitle('Comp Off Details Report ');
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
                $this->render('compoff_new');
                break;
        }
    }

     private function generateleavebalancemonthlyreportnew($mode)
    {

        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        //edited by athira on 07-07-2025
        $company_code = $this->Session->read('company_code');
        $this->set('company_code',$company_code);
        //end
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $report_month = $arr_form_data['reportfrom'];
        if ($arr_form_data['reportfrom']) {
            $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
            $frm = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $year1 = date('Y', strtotime($arr_form_data['reportfrom']));
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
        }
        $condition = 'and ed.status = 1';

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and ed.status in('1','2')";
        }

        $arr_leavepolicydetails_for_template = [];

if (!empty($arr_leavepolicygroupids)) {
    foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

        if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

            try {
                // Current month approved leaves
                $arr_empleaverequests_emp = $this->LeaveRequests->query("
                  SELECT 
                                ed.emp_pkey,
                                CONCAT(ed.first_name, ' ', ed.last_name) AS emp_name,
                                info.EmpName,
                                info.employee_id,
                                info.joining_date,
                                info.department,
                                info.designation,
                                info.branch,
                                user_credentials.user_id,
                                Units.branch_name,
                                ed.branch_code,
                                lp.leave_policy_type,
                                LeaveType.item AS leave_type,
                                LeaveType.salary_head_item_pkey,
                                le.LEAVESTATUS,
                                le.salary_head_item_fkey,
                                le.applied_date,
                                le.Autherized_date,
                                le.APPROVED_date,
                                elt.leave_date,
                                termination.last_approved_working_date,

                                -- Authorized & Approved By Details
                                le.ISAutherizedby,
                                CONCAT(auth.first_name, ' ', auth.last_name) AS authorized_by_name,
                                le.APPROVEDBY,
                                CONCAT(app.first_name, ' ', app.last_name) AS approved_by_name

                            FROM emp_details ed
                            JOIN emp_proff ep 
                                ON ed.emp_pkey = ep.emp_fkey

                            left join user_credentials on (user_credentials.emp_fkey = ed.emp_pkey)

                            -- Get leave entries for the employee for the selected month
                            JOIN leaveentries AS le 
                                ON le.EMP_fkey = ep.emp_fkey 
                                AND le.LEAVESTATUS = 'Approved'

                            -- Get leave transaction dates for each entry
                            JOIN emp_leave_transactions AS elt 
                                ON elt.LEAVEENTRYID = le.LEAVEENTRYID 
                                AND DATE_FORMAT(elt.leave_date, '%Y-%m') = '$frm'

                            -- Get monthly attendance info
                            LEFT JOIN attendance_register AS ar 
                                ON ar.emp_fkey = ep.emp_fkey 
                                AND ar.month_year = '$frm'

                            -- Get policy and related info
                            JOIN leavepolicy lp 
                                ON lp.LEAVEPOLICY_GROUP_ID = ep.LEAVEPOLICY_GROUP_ID 
                                AND lp.status = 1

                            JOIN salary_head_items LeaveType 
                                ON LeaveType.salary_head_item_pkey = le.salary_head_item_fkey

                            -- Get employee info and branches
                            JOIN employee_info AS info 
                                ON info.emp_pkey = ed.emp_pkey

                            LEFT JOIN branches Units 
                                ON Units.branch_code = ed.branch_code

                            LEFT JOIN termination AS termination 
                                ON termination.emp_fkey = ep.emp_fkey 
                                AND termination.status = 1

                            -- Get Authorizer and Approver Names
                            LEFT JOIN emp_details AS auth 
                                ON auth.emp_pkey = le.ISAutherizedby
                            LEFT JOIN emp_details AS app 
                                ON app.emp_pkey = le.APPROVEDBY

                            WHERE 
                                ed.emp_pkey = $leavepolicygroupid 
                                AND ed.status = '1' GROUP BY elt.LEAVEENTRYID
                                -- AND elt.leave_date IS NOT NULL
                ");

                // Resigned employees (optional)
                $arr_empleaverequests_emp_resign = [];
                if (!empty($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                    $arr_empleaverequests_emp_resign = $this->LeaveRequests->query("
                       SELECT 
                                ed.emp_pkey,
                                CONCAT(ed.first_name, ' ', ed.last_name) AS emp_name,
                                info.EmpName,
                                info.employee_id,
                                info.joining_date,
                                info.department,
                                info.designation,
                                info.branch,
                                user_credentials.user_id,
                                Units.branch_name,
                                ed.branch_code,
                                lp.leave_policy_type,
                                LeaveType.item AS leave_type,
                                LeaveType.salary_head_item_pkey,
                                le.LEAVESTATUS,
                                le.salary_head_item_fkey,
                                le.applied_date,
                                le.Autherized_date,
                                le.APPROVED_date,
                                elt.leave_date,
                                termination.last_approved_working_date,

                                -- Authorized & Approved By Details
                                le.ISAutherizedby,
                                CONCAT(auth.first_name, ' ', auth.last_name) AS authorized_by_name,
                                le.APPROVEDBY,
                                CONCAT(app.first_name, ' ', app.last_name) AS approved_by_name

                            FROM emp_details ed
                            JOIN emp_proff ep 
                                ON ed.emp_pkey = ep.emp_fkey

                            left join user_credentials on (user_credentials.emp_fkey = ed.emp_pkey)

                            -- Get leave entries for the employee for the selected month
                            JOIN leaveentries AS le 
                                ON le.EMP_fkey = ep.emp_fkey 
                                AND le.LEAVESTATUS = 'Approved'

                            -- Get leave transaction dates for each entry
                            JOIN emp_leave_transactions AS elt 
                                ON elt.LEAVEENTRYID = le.LEAVEENTRYID 
                                AND DATE_FORMAT(elt.leave_date, '%Y-%m') = '$frm'

                            -- Get monthly attendance info
                            LEFT JOIN attendance_register AS ar 
                                ON ar.emp_fkey = ep.emp_fkey 
                                AND ar.month_year = '$frm'

                            -- Get policy and related info
                            JOIN leavepolicy lp 
                                ON lp.LEAVEPOLICY_GROUP_ID = ep.LEAVEPOLICY_GROUP_ID 
                                AND lp.status = 1

                            JOIN salary_head_items LeaveType 
                                ON LeaveType.salary_head_item_pkey = le.salary_head_item_fkey

                            -- Get employee info and branches
                            JOIN employee_info AS info 
                                ON info.emp_pkey = ed.emp_pkey

                            LEFT JOIN branches Units 
                                ON Units.branch_code = ed.branch_code

                            LEFT JOIN termination AS termination 
                                ON termination.emp_fkey = ep.emp_fkey 
                                AND termination.status = 1

                            -- Get Authorizer and Approver Names
                            LEFT JOIN emp_details AS auth 
                                ON auth.emp_pkey = le.ISAutherizedby
                            LEFT JOIN emp_details AS app 
                                ON app.emp_pkey = le.APPROVEDBY

                            WHERE 
                                ed.emp_pkey = $leavepolicygroupid 
                                AND ed.status = '2' GROUP BY elt.LEAVEENTRYID
                                -- AND elt.leave_date IS NOT NULL
                    ");
                }

                // Merge all leaves into a single flat array
                $allLeaves = array_merge($arr_empleaverequests_emp, $arr_empleaverequests_emp_resign);

                if (!empty($allLeaves)) {
                    $arr_leavepolicydetails_for_template[$leavepolicygroupid] = [
                        'summary' => $allLeaves
                    ];
                }

            } catch (Exception $ex) {
                // handle exception
            }
        }
    }
}



        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        // debug($arr_leavepolicydetails_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        //$this->set('cur_year', $cur_year);
        $this->set('from', $from);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $this->set('mname1', $mname);
        $this->set('y1', $year);
        $this->set('month2', $month);
        $this->set('month1', $month1);
        $all_leaveheads   = $this->LeaveRequests->query("select distinct salary_head_item_pkey,item from salary_head_items  
            left join leavepolicy on (leavepolicy.salary_head_item_fkey = salary_head_items.salary_head_item_pkey) 
            left join leavepolicy_group on (leavepolicy_group.LEAVEPOLICY_GROUP_ID = leavepolicy.LEAVEPOLICY_GROUP_ID) 
            where item_type='LEAVE' and item_part='Direct' and occurance !='LOP' and salary_head_items.status =1
            and leavepolicy_group.status =1 and leavepolicy.status = 1");
        $this->set('all_leaveheads', $all_leaveheads);
        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('dates', $from);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf':
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('leavebalance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavebalance.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "MonthlyLeaveTakenRegister.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Monthly Leave Taken Register - " . $mname . " " . $year1);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:M1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:M2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                

                $rowcount = 3;
                $columncount = 0;
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                //                        
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                //                       
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                //                        
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                //                        
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Date of Joining');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                //                        
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                //                       
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                //                        
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Termination Date');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Leave Policy Type');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Leave Type');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Leave Date');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Applied Date');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Authorized Date');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'Authorized Person');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, 'Approved Date');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, 'Approved Person');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 16), $rowcount)->getFont()->setBold(true);

                // $col = 9;
                // foreach ($all_leaveheads as $heads) {
                //     $head = isset($heads['salary_head_items']['item']) ? $heads['salary_head_items']['item'] : '';


                //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $head);
                //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($col), $rowcount)->getFont()->setBold(true);
                //     $col++;
                // }
                // $rowcount = $rowcount + 1;

                if (count($arr_leavepolicydetails_for_template) != 0) {

                    
    $rowcount = 4;
    $slno = 1;

    // Fill data
    foreach ($arr_leavepolicydetails_for_template as $employeeLeaves) {
        foreach ($employeeLeaves['summary'] as $val) {
             
            $leave_policy_type_map = [
                'M' => 'Monthly', 'Y' => 'Yearly', 'Q' => 'Quarterly',
                'H' => 'Half Yearly', 'D' => 'Running Days', 'P' => 'Present Days'
            ];
            $leave_policy_type = isset($val['lp']['leave_policy_type']) ? ($leave_policy_type_map[$val['lp']['leave_policy_type']]) : '';
            $emp_status = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : '';
            $emp_name = $val['info']['EmpName'] . " " . $emp_status;

            $data = [
                $slno++,
                $val['info']['employee_id'],
                $val['user_credentials']['user_id'],
                $emp_name,
                isset($val['info']['joining_date']) ? date('d-m-Y', strtotime($val['info']['joining_date'])) : '',
                $val['info']['branch'],
                $val['info']['department'],
                $val['info']['designation'],
                isset($val['termination']['last_approved_working_date']) ? date('d-m-Y', strtotime($val['termination']['last_approved_working_date'])) : '',
                $leave_policy_type,
                $val['LeaveType']['leave_type'],
                isset($val['elt']['leave_date']) ? date('d-m-Y', strtotime($val['elt']['leave_date'])) : '',
                isset($val['le']['applied_date']) ? date('d-m-Y', strtotime($val['le']['applied_date'])) : '',
                isset($val['le']['Autherized_date']) ? date('d-m-Y', strtotime($val['le']['Autherized_date'])) : '',
                $val[0]['authorized_by_name'],
                isset($val['le']['APPROVED_date']) ? date('d-m-Y', strtotime($val['le']['APPROVED_date'])) : '',
                $val[0]['approved_by_name']
            ];

            $col = 0;
foreach ($data as $index => $value) {
    // If first column (Sl No), force as string
    if ($index == 0) {
        $value = (string)$value; // convert to string
    }
    
    $worksheet->setCellValueByColumnAndRow($col, $rowcount, $value);

    // Left-align the Sl No column
    if ($index == 0) {
        $worksheet->getStyleByColumnAndRow($col, $rowcount)
                  ->getAlignment()
                  ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    }

    $col++;
}


            $rowcount++;
        }
    }

    // Auto-size columns
    foreach (range('A','Q') as $columnID) {
        $worksheet->getColumnDimension($columnID)->setAutoSize(true);
    }

}



                 else {
                    $worksheet->mergeCells('A3:S3');
                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                }
                $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                $objPHPExcel->getActiveSheet()->setTitle('Monthly Leave Taken Register');
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
                $this->render('leavebalancenew');
                break;
        }
    }

     private function generatelopReport($mode)
    {
        $this->autoRender = false;
        $this->layout = '';

        $arr_form_data = $_REQUEST;

        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        /*
        |--------------------------------------------------------------------------
        | DATE RANGE HANDLING
        |--------------------------------------------------------------------------
        | UI now sends:
        | reportfrom = dd-mm-yyyy
        | reportto   = dd-mm-yyyy
        |
        | Example:
        | 01-05-2026
        | 31-05-2026
        |--------------------------------------------------------------------------
        */

        $from_val = isset($arr_form_data['reportfrom']) && !empty($arr_form_data['reportfrom'])
            ? trim($arr_form_data['reportfrom'])
            : date('d-m-Y');

        $to_val = isset($arr_form_data['reportto']) && !empty($arr_form_data['reportto'])
            ? trim($arr_form_data['reportto'])
            : date('d-m-Y');

        // Convert dd-mm-yyyy → Y-m-d
        $fromObj = DateTime::createFromFormat('d-m-Y', $from_val);
        $toObj = DateTime::createFromFormat('d-m-Y', $to_val);

        // fallback if invalid
        if (!$fromObj) {
            $fromObj = new DateTime();
        }

        if (!$toObj) {
            $toObj = new DateTime();
        }

        $from = $fromObj->format('Y-m-d');
        $to = $toObj->format('Y-m-d');

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if ($to < $from) {
            echo "<h3>Invalid date range. End date must be greater than start date.</h3>";
            die;
        }

        /*
        |--------------------------------------------------------------------------
        | Month values required for attendance register query
        |--------------------------------------------------------------------------
        */

        $from_ym = date('Y-m', strtotime($from));
        $to_ym = date('Y-m', strtotime($to));

        /*
        |--------------------------------------------------------------------------
        | Expand month range to support attendance cycle overlap
        |--------------------------------------------------------------------------
        */

        $from_ym_expanded = date('Y-m', strtotime($from_ym . ' -1 month'));
        $to_ym_expanded = date('Y-m', strtotime($to_ym . ' +1 month'));

        /*
        |--------------------------------------------------------------------------
        | Display label for report header
        |--------------------------------------------------------------------------
        */

        $display_dates = date('d-m-Y', strtotime($from)) .
            ' to ' .
            date('d-m-Y', strtotime($to));

        $this->set('dates', $display_dates);

        $company_code = strtoupper($this->Session->read('company_code'));
        $this->set('company_code', $company_code);



        // ── Employee-status condition ────────────────────────────────
        $emp_status_sql = "EmployeeDetails.status = '1'";
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $emp_status_sql = "EmployeeDetails.status IN ('1','2')";
        }

        // ── Branch restriction for manager logins ───────────────────
        $branch_condition = '';
        $user_group = $this->Session->read('user_group');
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $emp_pkey = $this->Session->read('emp_fkey');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn($emp_pkey) AS branch"
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " AND EmployeeDetails.branch_code = '$is_ho' ";
            }
        }

        // ── Build criteria conditions (same pattern as LeaveSummary) ─
        $needBranchWiseReport = false;
        $conditions = [];
        $conditions_part_b = [];

        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            if
            ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }
            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            try {
                $arr_rc = Set::extract('/ReportCriterias/.', $this->
                    ReportCriterias->find('all', [
                            'fields' => 'reportcriteria,reportcriteria_field',
                            'conditions' => [
                                'reporttype' => 'LeaveSummary',
                                'status' => 1,
                                'reportcriteria' => $str_criteria_item,
                            ],
                        ]));
            } catch (Exception $ex) {
                $arr_rc = [];
            }

            if (isset($arr_rc[0]['reportcriteria_field']) && isset($arr_form_data[$str_criteria_item])) {
                $field_clause = $arr_rc[0]['reportcriteria'] . '.' . $arr_rc[0]['reportcriteria_field']
                    . " IN ('" . implode("','", $arr_form_data[$str_criteria_item]) . "')";
                $conditions[] = $field_clause;
                $conditions_part_b[] = $field_clause;
            }
        }

        // Build the Part B extra WHERE clauses
        $str_conditions_part_b = !empty($conditions_part_b)
            ? 'AND ' . implode(' AND ', $conditions_part_b)
            : '';

        // Date range condition for leaveentries
        $conditions[] = '(FROMDATE <= "' . $to . '" AND TODATE>= "' . $from . '")';
        $str_conditions = implode(' AND ', $conditions);

        // ── Resolve LOP salary-head pkeys ───────────────────────────
        $lop_heads_raw = $this->LeaveRequests->query(
            "SELECT salary_head_item_pkey, item, occurance, item_part
        FROM salary_head_items
        WHERE status = 1
        AND item_type = 'Leave'
        AND occurance = 'LOP'"
        );

        $lop_pkeys = [];
        $lop_head_map = []; // pkey => ['item'=>…,'occurance'=>…,'item_part'=>…]
        foreach ($lop_heads_raw as $lh) {
            $pk = $lh['salary_head_items']['salary_head_item_pkey'];
            $lop_pkeys[] = $pk;
            $lop_head_map[$pk] = [
                'item' => $lh['salary_head_items']['item'],
                'occurance' => $lh['salary_head_items']['occurance'],
                'item_part' => $lh['salary_head_items']['item_part'],
            ];
        }

        if (empty($lop_pkeys)) {
            echo "<h1>No LOP salary head configured in this company</h1>";
            die();
        }
        $lop_pkey_str = implode(',', $lop_pkeys);

        // ── Where clause prefix shared by part-A ────────────────────
        $where_prefix = "WHERE $emp_status_sql $branch_condition AND ";

        // ════════════════════════════════════════════════════════════
        // PART A – LOP rows that have a leaveentries record
        // ════════════════════════════════════════════════════════════
        $sql_part_a = "
        SELECT
        (SELECT CONCAT(first_name,' ',last_name) FROM emp_details
        WHERE emp_pkey = LeaveRequests.ISAutherizedby) AS Authorized_name,
        (SELECT CONCAT(first_name,' ',last_name) FROM emp_details
        WHERE emp_pkey = LeaveRequests.APPROVEDBY) AS Approved_name,
        termination.last_approved_working_date,
        LeaveRequests.LEAVEENTRYID,
        LeaveRequests.Reason,
        LeaveRequests.contact_person,
        LeaveRequests.FROMHALF,
        LeaveRequests.TOHALF,
        LeaveRequests.leave_days,
        LeaveRequests.REMARKS,
        Units.branch_name,
        EmployeeDetails.branch_code,
        EmployeeDetails.status,
        CONCAT(EmployeeDetails.first_name,' ',EmployeeDetails.last_name) AS emp_name,
        Info.*,
        LeaveType.item AS leave_type,
        LeaveType.occurance,
        LeaveType.item_part,
        LeaveRequests.applied_date,
        LeaveRequests.FROMDATE,
        LeaveRequests.TODATE,
        LeaveRequests.LEAVESTATUS,
        LeaveRequests.AuthoriseRemarks,
        LeaveRequests.ApproveRemarks,
        LeaveRequests.Autherized_date,
        LeaveRequests.APPROVED_date,
        'leaveentry' AS lop_source,
        NULL AS cycle_month,
        NULL AS lop_day_number,
        NULL AS cycle_start_date
        FROM leaveentries AS LeaveRequests
        LEFT JOIN emp_details AS EmployeeDetails ON (LeaveRequests.EMP_fkey = EmployeeDetails.emp_pkey)
        LEFT JOIN salary_head_items AS LeaveType ON (LeaveRequests.salary_head_item_fkey =
        LeaveType.salary_head_item_pkey)
        LEFT JOIN branches AS Units ON (EmployeeDetails.branch_code = Units.branch_code)
        LEFT JOIN leavestatus AS Leavestatus ON (LeaveRequests.LEAVESTATUS = Leavestatus.LEAVESTATUS)
        LEFT JOIN employee_info AS Info ON (EmployeeDetails.emp_pkey = Info.emp_pkey)
        LEFT JOIN termination ON (termination.emp_fkey = Info.emp_pkey AND termination.status = 1)
        $where_prefix
        LeaveRequests.salary_head_item_fkey IN ($lop_pkey_str)
        AND $str_conditions
        ORDER BY EmployeeDetails.emp_pkey DESC
        ";

        // ════════════════════════════════════════════════════════════
        // PART B – Indirect LOP visible ONLY in attendance_register
        //
        // We expand each verified register row into individual LOP
        // day rows by examining FIELD1–FIELD31 for the value 'LOP'.
        //
        // The actual calendar date of FIELDn depends on the cycle:
        // cycle_start_date + (n-1) days
        // We retrieve cycle_start_date from fin_year for the branch.
        // ════════════════════════════════════════════════════════════

        // We will build a lookup map in PHP to prevent double-counting days 
        // that exist in both sources for the same employee.
        // edited by athira on 07-05-2026 – fetch FROMHALF/TOHALF to support half-day overlap detection
        $sql_part_a_short = "
            SELECT le.EMP_fkey, le.FROMDATE, le.TODATE, le.FROMHALF, le.TOHALF
            FROM leaveentries le
            WHERE le.salary_head_item_fkey IN ($lop_pkey_str)
            AND le.FROMDATE <= '$to' AND le.TODATE >= '$from'
            AND le.LEAVESTATUS IN ('Approved','Authorised','Processed','Verified')
        ";
        $part_a_rows = $this->LeaveRequests->query($sql_part_a_short);
        // $part_a_map[$emp_fkey][$date] = ['h1' => bool, 'h2' => bool]
        // h1 = first half covered by Direct LOP, h2 = second half covered
        $part_a_map = [];
        foreach ($part_a_rows as $r) {
            $ef       = $r['le']['EMP_fkey'];
            $fromdate = $r['le']['FROMDATE'];
            $todate   = $r['le']['TODATE'];
            $fromhalf = isset($r['le']['FROMHALF']) ? $r['le']['FROMHALF'] : '1';
            $tohalf   = isset($r['le']['TOHALF'])   ? $r['le']['TOHALF']   : '2';
            $isSingleDay = ($fromdate === $todate);
            $d1 = new DateTime($fromdate);
            $d2 = new DateTime($todate);
            while ($d1 <= $d2) {
                $dt = $d1->format('Y-m-d');
                if (!isset($part_a_map[$ef][$dt])) {
                    $part_a_map[$ef][$dt] = ['h1' => false, 'h2' => false];
                }
                $isFirstDay = ($dt === $fromdate);
                $isLastDay  = ($dt === $todate);
                if ($isSingleDay) {
                    // Single-day leave: use FROMHALF/TOHALF directly
                    if ($fromhalf == '1') { $part_a_map[$ef][$dt]['h1'] = true; }
                    if ($tohalf   == '2') { $part_a_map[$ef][$dt]['h2'] = true; }
                    // FROMHALF=2,TOHALF=2 means second half only
                    if ($fromhalf == '2' && $tohalf == '2') { $part_a_map[$ef][$dt]['h2'] = true; }
                } elseif ($isFirstDay) {
                    $part_a_map[$ef][$dt]['h1'] = ($fromhalf == '1');
                    $part_a_map[$ef][$dt]['h2'] = true;
                } elseif ($isLastDay) {
                    $part_a_map[$ef][$dt]['h1'] = true;
                    $part_a_map[$ef][$dt]['h2'] = ($tohalf == '2');
                } else {
                    // Middle days are fully covered
                    $part_a_map[$ef][$dt]['h1'] = true;
                    $part_a_map[$ef][$dt]['h2'] = true;
                }
                $d1->modify('+1 day');
            }
        }
        // ended by athira on 07-05-2026


        // Base query – one row per verified register per employee.
        // We join with fin_year to get the exact cycle start date for each branch.
        $sql_part_b_base = "
            SELECT
            ar.registerid,
            ar.emp_fkey,
            ar.month_year,
            ar.branch_code,
            ar.lop_only,
            ar.lop_total,
            COALESCE(fy.start_month, att_start_end_fn(CONCAT(ar.month_year,'-01'), 1)) as cycle_start_date,
            ar.FIELD1, ar.FIELD2, ar.FIELD3, ar.FIELD4, ar.FIELD5,
            ar.FIELD6, ar.FIELD7, ar.FIELD8, ar.FIELD9, ar.FIELD10,
            ar.FIELD11, ar.FIELD12, ar.FIELD13, ar.FIELD14, ar.FIELD15,
            ar.FIELD16, ar.FIELD17, ar.FIELD18, ar.FIELD19, ar.FIELD20,
            ar.FIELD21, ar.FIELD22, ar.FIELD23, ar.FIELD24, ar.FIELD25,
            ar.FIELD26, ar.FIELD27, ar.FIELD28, ar.FIELD29, ar.FIELD30,
            ar.FIELD31,
            EmployeeDetails.status,
            EmployeeDetails.first_name,
            EmployeeDetails.last_name,
            CONCAT(EmployeeDetails.first_name,' ',EmployeeDetails.last_name) AS emp_name_raw,
            Units.branch_name,
            Info.*,
            termination.last_approved_working_date
            FROM attendance_register ar
            LEFT JOIN emp_details AS EmployeeDetails ON (ar.emp_fkey = EmployeeDetails.emp_pkey)
            LEFT JOIN branches AS Units ON (ar.branch_code = Units.branch_code)
            LEFT JOIN employee_info AS Info ON (EmployeeDetails.emp_pkey = Info.emp_pkey)
            LEFT JOIN termination ON (termination.emp_fkey = EmployeeDetails.emp_pkey AND termination.status = 1)
            LEFT JOIN fin_year AS fy ON (
                ar.branch_code = fy.branch_code 
                AND fy.vattr1 = 0 
                AND fy.status = 1 
                AND CONCAT(ar.month_year,'-01') BETWEEN fy.start_month AND fy.end_month
            )
            WHERE ar.isdelete = 'N'
            AND ar.lop_only > 0
            AND ar.month_year BETWEEN '$from_ym_expanded' AND '$to_ym_expanded'
            AND $emp_status_sql
            $branch_condition
            $str_conditions_part_b
            ORDER BY ar.emp_fkey, ar.month_year
            ";

        // ── Execute queries ──────────────────────────────────────────
        $arr_lop_part_a = $this->LeaveRequests->query($sql_part_a);
        $arr_lop_part_b_r = $this->LeaveRequests->query($sql_part_b_base);

        // ── Expand Part B register rows into individual day rows ─────
        $arr_lop_part_b = [];

        foreach ($arr_lop_part_b_r as $reg) {
            // Resolve cycle start date from the stored function result
            if (isset($reg['ar']['cycle_start_date'])) {
                $cycle_start_str = $reg['ar']['cycle_start_date'];
            } elseif (isset($reg[0]['cycle_start_date'])) {
                $cycle_start_str = $reg[0]['cycle_start_date'];
            } else {
                $month_year_key = isset($reg['ar']['month_year']) ? $reg['ar']['month_year'] : (isset($reg[0]['month_year']) ? $reg[0]['month_year'] : date('Y-m'));
                $cycle_start_str = $month_year_key . '-01';
            }

            $cycle_start_ts = strtotime($cycle_start_str);

            for ($day = 1; $day <= 31; $day++) {
                $field_key = 'FIELD' . $day;

                // Read field value — CakePHP may alias the table as 'ar' or index '0'
                if (isset($reg['ar'][$field_key])) {
                    $field_val = strtoupper(trim($reg['ar'][$field_key]));
                } elseif (isset($reg[0][$field_key])) {
                    $field_val = strtoupper(trim($reg[0][$field_key]));
                } else {
                    $field_val = '';
                }

                if (strpos($field_val, 'LOP') === false) {
                    continue;
                }

                // edited by athira on 07-05-2026 – half-day aware overlap detection
                // Determine which halves are marked LOP in the attendance register
                $att_h1 = false;
                $att_h2 = false;

                if (strpos($field_val, '/') !== false) {
                    $parts  = explode('/', $field_val);
                    $att_h1 = (trim($parts[0]) === 'LOP');
                    $att_h2 = (trim($parts[1]) === 'LOP');
                } else {
                    // No slash → full-day LOP in attendance
                    $att_h1 = true;
                    $att_h2 = true;
                }

                // Actual calendar date of this LOP day
                $actual_date = date('Y-m-d', strtotime('+' . ($day - 1) . ' days', $cycle_start_ts));

                // Skip if outside the user-requested date window
                if ($actual_date < $from || $actual_date > $to) {
                    continue;
                }

                // CHECK OVERLAP (half-day aware): subtract halves already covered by a Direct LOP (Part A)
                // This allows a day where H1=Direct LOP and H2=Indirect LOP to be captured correctly
                $ef_key = isset($reg['ar']['emp_fkey']) ? $reg['ar']['emp_fkey'] : (isset($reg[0]['emp_fkey']) ? $reg[0]['emp_fkey'] : null);
                if ($ef_key && isset($part_a_map[$ef_key][$actual_date])) {
                    $covered = $part_a_map[$ef_key][$actual_date];
                    if ($covered['h1']) { $att_h1 = false; }
                    if ($covered['h2']) { $att_h2 = false; }
                }

                // If no halves remain after removing Direct-LOP-covered ones, skip this day entirely
                if (!$att_h1 && !$att_h2) {
                    continue;
                }

                // Determine final weight and half markers for the remaining uncovered halves
                if ($att_h1 && $att_h2) {
                    $leavedays = 1.0;
                    $fromhalf  = '1';
                    $tohalf    = '2';
                } elseif ($att_h1) {
                    $leavedays = 0.5;
                    $fromhalf  = '1';
                    $tohalf    = '1';
                } else {
                    // Only second half remains
                    $leavedays = 0.5;
                    $fromhalf  = '2';
                    $tohalf    = '2';
                }
                // ended by athira on 07-05-2026

                $info = isset($reg['Info']) ? $reg['Info'] : (isset($reg['info']) ? $reg['info'] : []);

                $arr_lop_part_b[] = [
                    // Markers
                    'lop_source' => 'attendance',
                    'occurance' => 'LOP',
                    'item_part' => 'Indirect',
                    'leave_type' => 'Indirect LOP',
                    // Employee
                    'emp_name_raw' => '',
                    'Info' => $info,
                    'EmployeeDetails' => [
                        'branch_code' => isset($reg['ar']['branch_code']) ? $reg['ar']['branch_code'] : '',
                        'status' => isset($reg['EmployeeDetails']['status']) ? $reg['EmployeeDetails']['status'] : '1',
                    ],
                    'Units' => ['branch_name' => isset($reg['Units']['branch_name']) ? $reg['Units']['branch_name'] : ''],
                    'termination' => [
                        'last_approved_working_date' =>
                            isset($reg['termination']['last_approved_working_date'])
                            ? $reg['termination']['last_approved_working_date'] : ''
                    ],
                    // Leave date info
                    'LeaveRequests' => [
                        'LEAVEENTRYID' => null,
                        'FROMDATE' => $actual_date,
                        'TODATE' => $actual_date,
                        'FROMHALF' => $fromhalf,
                        'TOHALF' => $tohalf,
                        'leave_days' => $leavedays,
                        'LEAVESTAYS' => $leavedays, // Fallback
                        'LEAVESTATUS' => 'Attendance LOP',
                        'applied_date' => '',
                        'AuthoriseRemarks' => '',
                        'ApproveRemarks' => '',
                        'Autherized_date' => '',
                        'APPROVED_date' => '',
                        'Reason' => 'Leave applied through status change',
                        'contact_person' => '',
                    ],
                    'leavedays' => $leavedays,
                    // No authorisation chain for attendance-only LOP
                    '0' => [
                        'Authorized_name' => '',
                        'Approved_name' => '',
                        'lop_source' => 'attendance',
                        'emp_name' => isset($reg['EmployeeDetails']['first_name'])
                            ? trim($reg['EmployeeDetails']['first_name'] . ' ' . $reg['EmployeeDetails']['last_name'])
                            : '',
                    ],
                    // Extra info for reference
                    'cycle_month' => isset($reg['ar']['month_year']) ? $reg['ar']['month_year'] : '',
                    'lop_day_number' => $day,
                    'cycle_start_date' => $cycle_start_str,
                ];
            }
        }

        // Merge both parts
        $arr_empleoprequests = array_merge($arr_lop_part_a, $arr_lop_part_b);

        // ── Build template array (branch-wise or flat) ───────────────
        // ── Sort by Employee Name then by Date ────────────────────────
        // usort($arr_empleoprequests, function ($a, $b) {
        //     // Extract names
        //     $nameA = isset($a['EmployeeDetails']['first_name']) ? $a['EmployeeDetails']['first_name'] : '';
        //     $nameB = isset($b['EmployeeDetails']['first_name']) ? $b['EmployeeDetails']['first_name'] : '';
        //     if ($nameA != $nameB)
        //         return strcmp($nameA, $nameB);

        //     // Extract dates
        //     $dateA = isset($a['LeaveRequests']['FROMDATE']) ? $a['LeaveRequests']['FROMDATE'] : '';
        //     $dateB = isset($b['LeaveRequests']['FROMDATE']) ? $b['LeaveRequests']['FROMDATE'] : '';
        //     return strcmp($dateA, $dateB);
        // });

        // ── Sort all LOP records by employee full name + date ─────────────────
usort($arr_empleoprequests, function ($a, $b) {

    $getEmployeeName = function ($row) {
        if (!empty($row['0']['emp_name'])) {
            return strtolower(trim($row['0']['emp_name']));
        }

        if (!empty($row['EmployeeDetails']['first_name'])) {
            return strtolower(trim(
                $row['EmployeeDetails']['first_name'] . ' ' .
                $row['EmployeeDetails']['last_name']
            ));
        }

        if (!empty($row['Info']['EmpName'])) {
            return strtolower(trim($row['Info']['EmpName']));
        }

        return '';
    };

    $nameA = $getEmployeeName($a);
    $nameB = $getEmployeeName($b);

    if ($nameA !== $nameB) {
        return strcmp($nameA, $nameB);
    }

    // Secondary sort → leave date
    $dateA = isset($a['LeaveRequests']['FROMDATE']) 
        ? $a['LeaveRequests']['FROMDATE'] 
        : '';

    $dateB = isset($b['LeaveRequests']['FROMDATE']) 
        ? $b['LeaveRequests']['FROMDATE'] 
        : '';

    return strcmp($dateA, $dateB);
});

        $arr_lopreport_for_template = [];

        if ($needBranchWiseReport) {

        usort($arr_empleoprequests, function ($a, $b) {

        $branchA = strtolower(
            isset($a['Units']['branch_name']) 
                ? trim($a['Units']['branch_name']) 
                : ''
        );

        $branchB = strtolower(
            isset($b['Units']['branch_name']) 
                ? trim($b['Units']['branch_name']) 
                : ''
        );

        if ($branchA !== $branchB) {
            return strcmp($branchA, $branchB);
        }

        // Same branch → sort employee name
        $nameA = strtolower(
            !empty($a['0']['emp_name']) 
                ? $a['0']['emp_name']
                : (
                    isset($a['EmployeeDetails']['first_name']) 
                    ? trim(
                        $a['EmployeeDetails']['first_name'] . ' ' .
                        $a['EmployeeDetails']['last_name']
                    )
                    : ''
                )
        );

        $nameB = strtolower(
            !empty($b['0']['emp_name']) 
                ? $b['0']['emp_name']
                : (
                    isset($b['EmployeeDetails']['first_name']) 
                    ? trim(
                        $b['EmployeeDetails']['first_name'] . ' ' .
                        $b['EmployeeDetails']['last_name']
                    )
                    : ''
                )
        );

        if ($nameA !== $nameB) {
            return strcmp($nameA, $nameB);
        }

        // Same employee → sort by date
        $dateA = isset($a['LeaveRequests']['FROMDATE']) 
            ? $a['LeaveRequests']['FROMDATE'] 
            : '';

        $dateB = isset($b['LeaveRequests']['FROMDATE']) 
            ? $b['LeaveRequests']['FROMDATE'] 
            : '';

        return strcmp($dateA, $dateB);
    });
            foreach ($arr_empleoprequests as $loprequest) {
                $branch_code = isset($loprequest['EmployeeDetails']['branch_code'])
                    ? $loprequest['EmployeeDetails']['branch_code'] : '';
                $branch_name = isset($loprequest['Units']['branch_name'])
                    ? $loprequest['Units']['branch_name'] : '';

                if ($branch_code == '')
                    continue;

                if (!isset($arr_lopreport_for_template[$branch_code])) {
                    $arr_lopreport_for_template[$branch_code] = [
                        'branch_name' => $branch_name,
                        'loprequests' => [],
                    ];
                }
                $arr_lopreport_for_template[$branch_code]['loprequests'][] =
                    $this->_buildLopRow($loprequest);
            }
        } else {
            $arr_lopreport_for_template['loprequests'] = [];
            foreach ($arr_empleoprequests as $loprequest) {
                $arr_lopreport_for_template['loprequests'][] =
                    $this->_buildLopRow($loprequest);
            }
        }

        // ── Set view variables ───────────────────────────────────────
        $this->set('needBranchWiseReport', $needBranchWiseReport);
        $this->set('arr_lopreport_for_template', $arr_lopreport_for_template);

        $display_dates = !empty($arr_form_data['month'])
            ? "For " . date('F-Y', strtotime($arr_form_data['month'] . '-01'))
            : $from . ' - ' . $to;
        $this->set('dates', $display_dates);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);

        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        // ════════════════════════════════════════════════════════════
        // OUTPUT – PDF / Excel / HTML
        // ════════════════════════════════════════════════════════════
        switch ($mode) {

            // ── PDF ──────────────────────────────────────────────────
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportlopsummary');

                App::import('Vendor', 'HTML2PDF', ['file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php']);
                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('LOPReport.pdf', 'D');
                break;

            // ── Excel ────────────────────────────────────────────────
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = ($str_company_code ?: 'LOP') . '_LOPReport.xlsx';

                App::import('Vendor', 'PHPExcel', ['file' => 'PHPExcel.php']);
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator")->setTitle("LOP Report");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();

                // ── Title ────────────────────────────────────────────
                $worksheet->setCellValueByColumnAndRow(0, 1, "LOP Report " . $display_dates);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true)->setSize(16);
                $worksheet->mergeCells('A1:AC1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
                );
                for ($col = 'A'; $col !== 'AD'; $col++) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
                }

                $columncount = 0;
                $rowcount = 2;

                // ── Header-row writer closure ────────────────────────
                $headers = [
                    'Sl No',
                    'Employee Name',
                    'Employee ID',
                    'Date Of Join',
                    'Branch',
                    'Department',
                    'LOP Type',
                    'Leave Type',
                    'Applied Date',
                    'From Date',
                    'To Date',
                    'LOP Days',
                    'Reason',
                    'Contact Person',
                    'Authorized By',
                    'Authorized Remarks',
                    'Authorized Date',
                    'Approved By',
                    'Approved Remarks',
                    'Approved Date',
                    'Rejected By',
                    'Rejected Remarks',
                    'Rejected Date',
                    'Status',
                    
                ];
                $headerCount = count($headers);

                $writeHeaders = function () use (&$objPHPExcel, &$rowcount, $columncount, $headers) {
                    foreach ($headers as $colIdx => $header) {
                        $objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($columncount + $colIdx, $rowcount, $header);
                        $objPHPExcel->getActiveSheet()
                            ->getStyleByColumnAndRow($columncount + $colIdx, $rowcount)
                            ->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()
                            ->getStyleByColumnAndRow($columncount + $colIdx, $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('86bfe0');
                    }
                    $rowcount++;
                };

                // ── Data-row writer closure ──────────────────────────
                $writeDataRow = function ($val, &$slno) use (&$objPHPExcel, &$rowcount, $columncount) {
                    $empstatus = ($val['status'] == '2') ? ' (Resigned)' : '';
                    $name = $val['emp_name'] . $empstatus;

                    $from_half = ($val['fromhalf'] == '1') ? "First Half" : (($val['fromhalf'] == '2') ? "Second Half" : "");
                    $to_half = ($val['tohalf'] == '1') ? "First Half" : (($val['tohalf'] == '2') ? "Second Half" : "");

                    $rej_by = '';
                    $rej_rem = '';
                    $rej_date = '';
                    if ($val['leave_status'] == 'Rejected') {
                        $rej_by = empty($val['APPROVED_date']) ? $val['Authorized_name'] : $val['Approved_name'];
                        $rej_rem = empty($val['APPROVED_date']) ? $val['Authorized_remarks'] : $val['Approved_remarks'];
                        $rej_date = empty($val['APPROVED_date']) ? $val['Autherized_date'] : $val['APPROVED_date'];
                    }
                    $appr_rem = ($val['leave_status'] != 'Rejected') ? $val['Approved_remarks'] : '';
                    $appr_date = ($val['leave_status'] != 'Rejected') ? $val['APPROVED_date'] : '';

                    $lop_type_label = '';
                    if ($val['lop_source'] == 'attendance') {
                        $lop_type_label = 'Indirect (Attendance)';
                    } elseif ($val['occurance'] == 'LOP' && $val['item_part'] != 'Indirect') {
                        $lop_type_label = 'Direct / Policy LOP';
                    } else {
                        $lop_type_label = 'Indirect LOP';
                    }

                    $cells = [
                        $slno++,
                        $name,
                        $val['employee_id'],
                        $val['joining_date'],
                        $val['branch'],
                        $val['department'],
                        $lop_type_label,
                        $val['leave_type'],
                        $val['leave_applied_on'],
                        $val['leave_from'] . " " . $from_half,
                        $val['leave_to'] . " " . $to_half,
                        $val['leavedays'],
                        $val['Reason'],
                        $val['contact_person'],
                        $val['Authorized_name'],
                        $val['Authorized_remarks'],
                        $val['Autherized_date'],
                        $val['Approved_name'],
                        $appr_rem,
                        $appr_date,
                        $rej_by,
                        $rej_rem,
                        $rej_date,
                        $val['leave_status'],
                        // ($val['lop_source'] == 'attendance') ? 'Attendance Register' : 'Leave Entry'
                    ];

                    foreach ($cells as $colIdx => $cellVal) {
                        $objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($columncount + $colIdx, $rowcount, $cellVal);

                        // Left-align identifier and numeric columns
                        $headerName = isset($headers[$colIdx]) ? $headers[$colIdx] : '';
                        if (in_array($headerName, ['Sl No', 'Employee ID', 'LOP Days'])) {
                            $objPHPExcel->getActiveSheet()
                                ->getStyleByColumnAndRow($columncount + $colIdx, $rowcount)
                                ->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        }
                    }
                    $rowcount++;
                };

                // ── Write data ───────────────────────────────────────
                if ($needBranchWiseReport) {
                    foreach ($arr_lopreport_for_template as $bc => $lopsummary) {
                        // Branch heading row
                        $branchTitle = $lopsummary['branch_name'] . ' Branch';
                        $objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($columncount, $rowcount, $branchTitle);
                        $lastColLetter = PHPExcel_Cell::stringFromColumnIndex($columncount + $headerCount - 1);
                        $objPHPExcel->getActiveSheet()
                            ->mergeCells('A' . $rowcount . ':' . $lastColLetter . $rowcount);
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount)
                            ->getAlignment()->applyFromArray(['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER]);
                        $objPHPExcel->getActiveSheet()
                            ->getStyleByColumnAndRow($columncount, $rowcount)
                            ->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()
                            ->getStyleByColumnAndRow($columncount, $rowcount)
                            ->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('86bfe0');
                        $rowcount += 2;

                        $writeHeaders();

                        $slno = 1;
                        $arr_data = $lopsummary['loprequests'];
                        if (!empty($arr_data)) {
                            foreach ($arr_data as $val) {
                                $writeDataRow($val, $slno);
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()
                                ->setCellValueByColumnAndRow(
                                    $columncount,
                                    $rowcount,
                                    'No LOP records found for this branch.'
                                );
                            $rowcount++;
                        }
                        $rowcount++;
                    }
                } else {
                    $writeHeaders();
                    $slno = 1;
                    $arr_data = isset($arr_lopreport_for_template['loprequests'])
                        ? $arr_lopreport_for_template['loprequests'] : [];

                    if (!empty($arr_data)) {
                        foreach ($arr_data as $val) {
                            $writeDataRow($val, $slno);
                        }
                    } else {
                        $rowcount = 3;
                        $worksheet->setCellValue(
                            'A' . $rowcount,
                            'No LOP data available under the selected criteria.'
                        );
                        $worksheet->mergeCells('A' . $rowcount . ':' .
                            PHPExcel_Cell::stringFromColumnIndex($headerCount - 1) . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getFont()->setBold(true)->setSize(12);
                    }
                } // end else (not branch-wise)

                foreach (range(0, 27) as $c)
                    $worksheet->getColumnDimensionByColumn($c)->setAutoSize(true);


                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $file_name . '"');
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save('php://output');
                break;

            default:
                $this->set('mode', '');
                $this->render('reportlopsummary');
                break;
        } // end switch ($mode)
    } // end generatelopReport()

    /**
     * Normalises a raw DB row from EITHER part-A (leaveentries join)
     * OR part-B (attendance_register expand) into a single flat map
     * that the Excel/CTP writers consume.
     * Added by antigravity on 2026-05-07
     */
    private function _buildLopRow(array $row)
    {
        $isLeaveEntry = !isset($row['lop_source']) || $row['lop_source'] === 'leaveentry';
        $lop_source = $isLeaveEntry ? 'leaveentry' : 'attendance';

        // ── Employee name ──────────────────────────────────────────────────
        $emp_name = '';
        if (!empty($row['0']['emp_name'])) {
            $emp_name = $row['0']['emp_name'];
        } elseif (!empty($row['EmployeeDetails']['first_name'])) {
            $emp_name = trim($row['EmployeeDetails']['first_name'] . ' ' . $row['EmployeeDetails']['last_name']);
        } elseif (!empty($row['Info']['EmpName'])) {
            $emp_name = $row['Info']['EmpName'];
        }

        // ── Employee status ────────────────────────────────────────────────
        $emp_status = isset($row['EmployeeDetails']['status']) ? $row['EmployeeDetails']['status'] : '1';

        // ── User ID (pre-joined via LEFT JOIN user_credentials AS uc) ─────
        $userid = isset($row['uc']['user_id'])
            ? $row['uc']['user_id']
            : (isset($row['User']['user_id']) ? $row['User']['user_id'] : '');

        // ── Leave / LOP type metadata ──────────────────────────────────────
        if ($isLeaveEntry) {
            // Restore original name from either the item column, the alias, or the raw LeaveType array
            $leave_type = isset($row['LeaveType']['item']) ? $row['LeaveType']['item'] :
                (isset($row[0]['leave_type']) ? $row[0]['leave_type'] :
                    (isset($row['LeaveType']['leave_type']) ? $row['LeaveType']['leave_type'] : 'Direct / Policy LOP'));
            $occurance = isset($row['LeaveType']['occurance']) ? $row['LeaveType']['occurance'] : 'LOP';
            $item_part = 'Direct / Policy';
            // Use the actual leave days from the record
            $leavedays = isset($row['LeaveRequests']['leave_days']) ? $row['LeaveRequests']['leave_days'] :
                (isset($row['LeaveRequests']['LEAVEDAYS']) ? $row['LeaveRequests']['LEAVEDAYS'] :
                    (isset($row['LeaveRequests']['leavedays']) ? $row['LeaveRequests']['leavedays'] :
                        (isset($row['LeaveRequests']['LEAVE_DAYS']) ? $row['LeaveRequests']['LEAVE_DAYS'] :
                            (isset($row[0]['leave_days']) ? $row[0]['leave_days'] :
                                (isset($row[0]['LEAVEDAYS']) ? $row[0]['LEAVEDAYS'] : '')))));
            $remarks = isset($row['LeaveRequests']['REMARKS']) ? $row['LeaveRequests']['REMARKS'] : '';
            $reason = isset($row['LeaveRequests']['Reason']) ? $row['LeaveRequests']['Reason'] : '';
        } else {
            $leave_type = 'Admin LOP';
            $occurance = 'LOP';
            $item_part = 'Indirect';
            $leavedays = isset($row['leavedays']) ? $row['leavedays'] : '';
            $remarks = '';
            $reason = 'Leave applied through status change';
        }

        // ── Leave request fields ───────────────────────────────────────────
        $lr = isset($row['LeaveRequests']) ? $row['LeaveRequests'] : [];

        // ── Authorisation chain ────────────────────────────────────────────
        $auth_name = isset($row['0']['Authorized_name']) ? $row['0']['Authorized_name'] : '';
        $appr_name = isset($row['0']['Approved_name']) ? $row['0']['Approved_name'] : '';

        // ── Attendance-only traceability ───────────────────────────────────
        $cycle_month = isset($row['cycle_month']) ? $row['cycle_month'] : '';
        $lop_day_number = isset($row['lop_day_number']) ? $row['lop_day_number'] : '';
        $cycle_start_date = isset($row['cycle_start_date']) ? $row['cycle_start_date'] : '';

        return [
            'emp_name' => $emp_name,
            'EmpUSName' => isset($row['Info']['EmpUSName']) ? $row['Info']['EmpUSName'] : '',
            'status' => $emp_status,
            'employee_id' => isset($row['Info']['employee_id']) ? $row['Info']['employee_id'] : '',
            'emp_us_id' => isset($row['Info']['emp_us_id']) ? $row['Info']['emp_us_id'] : '',
            'userid' => $userid,
            'joining_date' => isset($row['Info']['joining_date']) ? $row['Info']['joining_date'] : '',
            'branch' => isset($row['Units']['branch_name']) ? $row['Units']['branch_name'] : '',
            'department' => isset($row['Info']['department']) ? $row['Info']['department'] : '',
            'designation' => isset($row['Info']['designation']) ? $row['Info']['designation'] : '',
            'termination' => isset($row['termination']['last_approved_working_date'])
                ? $row['termination']['last_approved_working_date'] : '',
            'leave_type' => $leave_type,
            'occurance' => $occurance,
            'item_part' => $item_part,
            'lop_source' => $lop_source,
            'leave_from' => isset($lr['FROMDATE']) ? $lr['FROMDATE'] : '',
            'leave_to' => isset($lr['TODATE']) ? $lr['TODATE'] : '',
            'fromhalf' => isset($lr['FROMHALF']) ? $lr['FROMHALF'] : '1',
            'tohalf' => isset($lr['TOHALF']) ? $lr['TOHALF'] : '1',
            'leave_applied_on' => isset($lr['applied_date']) ? $lr['applied_date'] : '',
            'leavedays' => $leavedays,
            'Authorized_name' => $auth_name,
            'Authorized_remarks' => isset($lr['AuthoriseRemarks']) ? $lr['AuthoriseRemarks'] : '',
            'Autherized_date' => isset($lr['Autherized_date']) ? $lr['Autherized_date'] : '',
            'Approved_name' => $appr_name,
            'Approved_remarks' =>  isset($lr['ApproveRemarks']) ? $lr['ApproveRemarks'] : $remarks,
            'APPROVED_date' => isset($lr['APPROVED_date']) ? $lr['APPROVED_date'] : '',
            'leave_status' => isset($lr['LEAVESTATUS']) ? $lr['LEAVESTATUS'] : 'Attendance LOP',
            'Reason' => $reason,
            'contact_person' => isset($lr['contact_person']) ? $lr['contact_person'] : '',
            'cycle_month' => $cycle_month,
            'lop_day_number' => $lop_day_number,
            'cycle_start_date' => $cycle_start_date,
        ];
    }
}
