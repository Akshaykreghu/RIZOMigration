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

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class StockReportController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'StockReport';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */

    public $uses = array('LeavePolicyGroup', 'CentralControl', 'Store','LoanEmi', 'MaterialRequest', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments', 'EmployeeGrossDetails', 'Verticals', 'Units', 'ReportCriterias', 'DayTimeProcedures', 'EmpCtcTransaction', 'LeaveRequests', 'Designation','PoReturn','CompanyContactInfo','item_allocate', 'allocate_details','ReportAudit','CategoryMaster');

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
            'Stock' => 'Stock Summary Report',
            'StockDetail' => 'Stock Movement Report',
            'Material' =>  'Material Request Report',
            'PO' =>  'Purchase Order Report',
            'GRN' =>  'Goods Received Notes Report',
            'Allocation' =>  'Uniform Allocation Report',
            'StockTransfer' =>  'Stock transfer Report',
            'PoReturn' =>  'PO Return Report',
            'AllocationEMI' =>  'Uniform Allocation EMI Report',
            'StockAllDetails' =>  'Item Details Report',
            'ItemRate' =>  'Item Rate Report',
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
                case 'Material':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'PO':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'StockTransfer':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Allocation':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'AllocationEMI':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'GRN':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'StockDetail':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //santhu
                case 'Stock':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                 //megha
                case 'StockAllDetails':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
		case 'ItemRate':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;				  
                case 'PoReturn':
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

//select items
    public function get_all_items($id){
        //debug($id);
        $this->autoRender = FALSE;
    //$arr_form_data = $_REQUEST;
    //debug($id);
    $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
    $arr_empleaverequests = $this->EmpCtcTransaction->query("select distinct item_master_pkey,item_desc from stock_details_view where store_master_pkey = '$id' ");
    //debug($arr_empleaverequests);
    echo json_encode($arr_empleaverequests);
//                 foreach ($arr_emp as $key => $value) {
//                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];
//                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
//                        $key++;
//                    }
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
                $model = ($model == 'CategoryMaster') ? 'Category' : $model;
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
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds'); // Edited by Akshay on 29-1-2025
        $model = $str_criteria;
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');

            $arr_order = array();
            $join = array();
            $fields = array();
            $conditions = array();
            if ($model == 'SalaryHeadItems') {
                $conditions = array("head_fkey" => 6, "value" => 'Y', "status" => 1);
            } elseif ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            } elseif ($model == 'Leavestatus') {
                $conditions = array();
            } elseif ($model == 'EmployeeGrossDetails') {
                $conditions = array();
            } elseif ($model == 'Units') {
                $user_group = $this->Session->read('user_group');
                if ($user_group == 2) {
                    // Edited by Akshay on 28-1-2025
                    $user = $this->Session->read('company_code');
                    if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                        $emp_pkey = $this->Session->read('emp_fkey');
                        $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                        $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                        if ($is_ho != 1) {
                            $conditions = array("Units.status" => 1, "branch_code" => $is_ho);
                        }
                    }else{
                        $cur_emp_key = $this->Session->read("emp_fkey");
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                        $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                        $arr_order = array("Units.branch_name" => "ASC");
                        $conditions = array("Units.status" => 1, "branch_code" => $cur_emp_branch);
                    }
                } else {
                    $arr_order = array("Units.branch_name" => "ASC");
                    $conditions = array();
                }
            } elseif ($model == 'Store') {
                $user_group = $this->Session->read('user_group');
                $emplogin_cond = "";
                if ($user_group == 2) {
                    //The below code is to display only allocated stores to logged/current employee. by ***ARUL P DAS on 17/1/2020
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $join = array(array(
                        'table' => 'access_store',
                        'type' => 'INNER',
                        'conditions' => array(
                            'access_store.store_pkey = Store.store_master_pkey'
                        )
                    ));
                    $fields = array('DISTINCT `Store`.`store_master_pkey`, `Store`.`store_code`, `Store`.`store_location`, `Store`.`address`, `Store`.`city`, `Store`.`state`, `Store`.`pincode`, `Store`.`store_manager`, `Store`.`roc`, `Store`.`tan`, `Store`.`tin`, `Store`.`rtgs`, `Store`.`created_by`, `Store`.`creation_date`, `Store`.`modified_by`, `Store`.`modified_date`, `Store`.`status`');
                    $conditions = array("Store.status" => 1, "access_store.emp_fkey" => $cur_emp_key, "access_store.status" => 1);
                    //edited by megha added admin view condition
                } else {
                    $conditions = array("Store.status" => 1);
                }
            } else {
                // Edited by Akshay on 29-1-2025
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                    $emp_pkey = $this->Session->read('emp_fkey');
                    $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $conditions = array("status" => 1, "branch_code" => $is_ho);
                    }
                }else{
                    $conditions = array("status" => 1);
                }
            }
            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => $fields, "conditions" => $conditions, "order" => $arr_order, "joins" => $join)));
            //debug($arr_criteriaItemsDB);
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
                case 'Store':

                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['store_master_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['store_location'];
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
                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name,"")," - ",emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
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
                    $conditions[] = array("status" => 1);

                    //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                    $user_group = $this->Session->read('user_group');
                    if ($user_group == 2) {
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
                case 'attendance':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['day_time_seq'];
                        $arr_criteriaItems[$key]['text'] = $value['day_time_desc'];
                        $key++;
                    }
                    break;
                case 'CategoryMaster':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['category_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['code'];
                        $key++;
                    }
            }
            echo json_encode($arr_criteriaItems);
        }
    }
	public function reportAudit($type,$mode){
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'Stock':
                $dataForHistory['report_type'] = "Stock Summary Report";
                break;
            case 'StockDetail':
                $dataForHistory['report_type'] = "Stock Movement Report";
                break;
            case 'Material':
                $dataForHistory['report_type'] = "Material Report";
                break;
            case 'PO':
                $dataForHistory['report_type'] = "Purchase Order Report";
                break;
            case 'GRN':
                $dataForHistory['report_type'] = "Goods Received Notes Report";
                break;
            case 'Allocation':
                $dataForHistory['report_type'] = "Uniform Allocation Report";
                break;
            case 'StockTransfer':
                $dataForHistory['report_type'] = "Stock Transfer Report";
                break;
            case 'PoReturn':
                $dataForHistory['report_type'] = "PO Return Report";
                break;
            case 'AllocationEMI':
                $dataForHistory['report_type'] = "Uniform Allocation EMI Report ";
                break;
            case 'StockAllDetails':
                $dataForHistory['report_type'] = "Item Details Report";
                break;
            case 'ItemRate':
                $dataForHistory['report_type'] = "Item Rate Report";
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
                case 'Store': $criteria_name_array[] = 'belonging to a Store';
                    break;
                case 'CategoryMaster': $criteria_name_array[] = 'belonging to a Category';
                    break;
                default : break;
            }
            $items_array[] = isset($arr_form_data[$criteria])?implode(",", $arr_form_data[$criteria]):'';
            $items_count_array[] = isset($arr_form_data[$criteria])?count($arr_form_data[$criteria]):0;

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
            
            case 'Stock':
                $this->GenerateSummaryPayrolreport($mode);
                break;
            case 'StockTransfer':
                $this->GenerateStockTransferreport($mode);
                break;
            case 'Allocation':
                $this->GenerateAllocationreport($mode);
                break;
            case 'StockDetail':
                $this->GenerateStockreport($mode);
                break;

            case 'Material':
                $this->GenerateSalarySlipreport($mode);
                break;
            case 'GRN':
                $this->GenerateGRNreport($mode);
                break;
            case 'PO':
                $this->GeneratePoreport($mode);
                break;
            case 'PoReturn':
                $this->GeneratePoReturnreport($mode);
                break;
            case 'AllocationEMI':
                $this->GenerateUniformAllocationEMIReport($mode);
                break;
            case 'StockAllDetails':
                $this->GenerateStockAllDetailsreport($mode);
                break;
            case 'ItemRate':
                $this->GenerateItemRatereport($mode);
                break;
													
            default:
                return false;
                break;
        }
		   $this->reportAudit($type,$mode);
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

    //get itemlist from selected stock
  public function itemlist($keys = '') {
     //debug($keys);
     $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
     $this->autoRender = false;
     $str_arr = explode (",", $keys);  
     foreach ($str_arr as $store) {
      $arr_itemlist = $this->EmpCtcTransaction->query("select distinct item_master_pkey,item_desc from  stock_details_view where store_master_pkey = '$store' ");
      $arr_itemlist_for_template[] = $arr_itemlist; 
      }
    //debug($arr_itemlist_for_template);
     //$this->set('arr_itemlist_for_template', $arr_itemlist_for_template);
     $array = array();
       $stores = array();
       //$stores[] = array("id" => "0", "text" => "-Select-");
        foreach ($arr_itemlist_for_template as $key => $value) {
            foreach ($value as $val) {
            $stores[] = array(
                'id' => $val['stock_details_view']['item_master_pkey'],
                'text' => $val['stock_details_view']['item_desc'] 
            );}
        }
		 
        //debug($stores);
       $array['items'] = $stores;
       echo json_encode($array);
      
}  

public function itemcriteria($store = ''){
    $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
    
      $arr_itemlist = $this->EmpCtcTransaction->query("select distinct item_master_pkey,item_desc from  stock_details_view where store_master_pkey = '$store' ");
      //debug($arr_itemlist);
       $this->set('arr_itemlist', $arr_itemlist);
}

//stock all details report
 private function GenerateStockAllDetailsreport($mode) {
        $arr_form_data = $_REQUEST;
        //debug($arr_form_data);
        $item = isset($arr_form_data['items-criteria'])?$arr_form_data['items-criteria']: '';
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
//        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
//        $to = date('Y-m-d', strtotime($arr_form_data['reportto'])); 
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $arr_stocksummary_for_template = array();
        $conditions = '';
        if($item){
        $conditions = " and item_master_pkey = '$item'";
        }
        if(!isset($arr_form_data['Store'])){
            echo "No Criteria Selected ";
            return false;
        }
        $arr_store = $arr_form_data['Store'];
        
        foreach ($arr_store as $val){
//        $arr_empleaverequests = $this->EmpCtcTransaction->query("select `im`.`item_master_pkey` AS `item_master_pkey`,`im`.`item_code` AS `item_code`,`im`.`item_desc` AS `item_desc`,`im`.`item_category` 
//        AS `item_category`,`sm`.`store_master_pkey` AS `store_master_pkey`,`sm`.`store_code` AS `store_code`,`sm`.`store_location` AS `store_location`,
//        `sm`.`address` AS `address`,`sd`.`stock_details_pkey` AS `stock_details_pkey`,`sd`.`store_fkey` AS `store_fkey`,`sd`.`supplier_fkey` AS `supplier_fkey`,
//        `sd`.`invoice_no` AS `invoice_no`,`sd`.`mr_no` AS `mr_no`,`sd`.`po_no` AS `po_no`,`sd`.`item_batch` AS `item_batch`,`sd`.`item_fkey` AS `item_fkey`,
//        `sd`.`received_qty` AS `received_qty`,`sd`.`item_qty` AS `item_qty`,`sd`.`free_stock` AS `free_stock`,`sd`.`offer_stock` AS `offer_stock`,
//        `sd`.`purchase_rate` AS `purchase_rate`,`sd`.`amount` AS `amount`,`sd`.`item_mrp` AS `item_mrp`,`sd`.`varified_by` AS `varified_by`,
//        `sd`.`item_state` AS `item_state`,`sd`.`created_by` AS `created_by`,`sd`.`creation_date` AS `creation_date`,`sd`.`modified_by` AS `modified_by`,
//        `sd`.`modified_date` AS `modified_date`,`sd`.`status` AS `status` from ((`stock_details` `sd` join `store_master` `sm`) join `item_master` `im`) 
//        where ((`sm`.`store_master_pkey` = `sd`.`store_fkey`) and (`im`.`item_master_pkey` = `sd`.`item_fkey`)) and sm.store_master_pkey = '$val' ");
            $arr_empleaverequests = $this->EmpCtcTransaction->query("select * from  stock_details_view where store_master_pkey = '$val' $conditions ");
            
            if (!empty($arr_empleaverequests)) {
            $arr_stocksummary_for_template[] = $arr_empleaverequests;    
            }
        }
       //debug($arr_stocksummary_for_template);
        

       ini_set('memory_limit', '-1');
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        //$this->set('month', $from. ' - '.$to);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('stockalldetails');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('itemdetailsreport.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "itemdetailedreport.xlsx" : "itemdetails" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Item Details Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Item Details Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(20);
                for ($col = 'A'; $col !== 'O'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:O1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 2, "Report Run by " . $user_id ." at ". $date_time  );
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $worksheet->mergeCells('A2:O2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 4;

                if (count($arr_stocksummary_for_template) == 0) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Data Available With The Selected Criteria');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                } else {
                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                        $branch = isset($value['0']['stock_details_view']['store_location']) ? "Store - ".$value['0']['stock_details_view']['store_location'] : '';

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(18);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        
                        $worksheet->mergeCells('A'.$rowcount.':U'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Item Code');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Store');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Invoice Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'MR Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'PO Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Item Batch');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Received Quantity');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Item Quantity');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Free Stock');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Offer Stock');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Purchase Rate');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Amount');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Item Rate');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Varified Person');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Transaction Type');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Created Person');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Creation Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Modified Person');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Modification Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                        
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
										 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {

                                $slno = $i;
                                $item_code = $val['stock_details_view']['item_code'];
                                $it_name = $val['stock_details_view']['item_desc'];
                                $store_location = $val['stock_details_view']['store_location'];
                                $invoice_no = $val['stock_details_view']['invoice_no'];
                                $mr_no = $val['stock_details_view']['mr_no'];
                                $po_no = $val['stock_details_view']['po_no'];
                                $item_batch = $val['stock_details_view']['item_batch'];
                                $received_qty = $val['stock_details_view']['received_qty'];
                                $item_qty = $val['stock_details_view']['item_qty'];
//  $free_stock = $val['sd']['free_stock'];
//  $offer_stock = $val['sd']['offer_stock'];
                                $purchase_rate = $val['stock_details_view']['purchase_rate'];
                                $amount = $val['stock_details_view']['amount'];
                                $item_mrp = $val['stock_details_view']['item_mrp'];
                                $varified_by = $val['stock_details_view']['varified_by'];
                                $item_state = $val['stock_details_view']['item_state'];
                                $created_by = $val['stock_details_view']['created_by'];
                                $creation = $val['stock_details_view']['creation_date'];
                                $creation_date = date("Y-m-d", strtotime($creation));
                                $modified_by = $val['stock_details_view']['modified_by'];
                                $modified = $val['stock_details_view']['modified_date'];
                                if($modified){
                                $modified_date = date("Y-m-d", strtotime($modified));
                                }else{
                                $modified_date = "";
                                }
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $item_code);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $it_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $store_location);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $invoice_no);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $mr_no);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $po_no);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $item_batch);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $received_qty);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $item_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $free_stock);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $offer_stock);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $purchase_rate);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $amount);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $item_mrp);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $varified_by);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $item_state);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $created_by);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $creation_date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $modified_by);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $modified_date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                
                                $rowcount++;
                                $i++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
			}	 
                $objPHPExcel->getActiveSheet()->setTitle('Item Details Report');
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
                $this->render('stockalldetails');
                break;
        }
    }

    // edited by sruthi 09/09/16    here ends     
    // edited by sruthi 22/09/16
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
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $arr_leavepolicydetails_for_template = array();
        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $gross = array();
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

            $arr_gross = $this->EmpCtcTransaction->query(" select info.*,ectc.* from emp_salary_slip as ectc "
                    . " left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)"
                    . "where  ectc.emp_fkey= '$leavepolicygroupid' "
                    . "AND ectc.item_part = 'Direct' "
                    . "AND ectc.end_date_effective is null "
                    . "AND  $id  order by info.EmpName");


            $arr_ot = $this->EmpCtcTransaction->query("select ot_master.set_duration from emp_ot_master as ot_master where ot_master.emp_fkey='$leavepolicygroupid' "
                    . "And ot_master.month='$otdate' and ot_master.is_verified='Y'");

            $pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
            //debug($pro_date_desc);
            $this->set('arr_gross', $arr_gross);


            if (isset($arr_gross) && !empty($arr_gross)) {
                $gross[$k]['emp_info'] = $arr_gross[0]['info'];
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
                        $gross[$k]['Deduction']['value'][$key] = $value['ectc']['salary_amount'];
                        $gross[$k]['Deduction']['actual'][$key] = $value['ectc']['salary_rate'];
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
                $html2pdf->Output('grosssummaryreport.pdf', 'D');
                $this->render('grosssummaryreport');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_gross.xlsx" : "GrossReport" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Gross Salary Reports for " . $from);
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
                $rowcount = 1;

                $i = 0;
                // $rowcount = 3;

                $col = 0;

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  ID   ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Employee Name  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  Designation ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Department  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Branch ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Total Days ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '  Days Type');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '  Present Days ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, '  Overtime (In Hrs.) ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);

                $col = 9;

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Gross Salary  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $col = 10;
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, ' Total Deduction  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Net Salary ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);

                $j = 1;

                $rowcount = $rowcount + 1;

                foreach ($gross as $val) {
                    $col = 0;
                    $name = $val['emp_info']['EmpName'];
                    $id = $val['emp_info']['employee_id'];
                    $deg = $val['emp_info']['designation'];
                    $dep = $val['emp_info']['department'];
                    $branch = $val['emp_info']['branch'];
                    $prodays = $val['prodata']['days'];
                    $type = $val['prodata']['type'];
                    $present_total = round($val['prodata']['present']);
                    $ot = isset($val['ot']) ? round(($val['ot'] / 60), 2) : 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $id);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $name);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $deg);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $dep);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $branch);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $prodays);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $type);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $present_total);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $ot);

                    $col = 9;

                    //debug($val);
                    $count_addition = count($val['Addition']['keys']);
                    $grss_amt = 0;
                    for ($m = 0; $m < $count_addition; $m++) {
                        $number = round($val['Addition']['value'][$m]);
                        $grss_amt = $number + $grss_amt;

                        $val1 = $number;
                    }

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $grss_amt);
                    $count_addition = count($val['Deduction']['keys']);
                    $col = $col + 1;
                    $dd_amt = 0;
                    for ($m = 0; $m < $count_addition; $m++) {
                        $number = round($val['Deduction']['value'][$m]);
                        $dd_amt = $number + $dd_amt;

                        $val1 = $number;
                    }


                    $netamt = $grss_amt + $dd_amt;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $dd_amt);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $netamt);

                    $j++;
                    $rowcount++;
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


	
    private function GenerateStockTransferreport($mode){
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);

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

        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-d', strtotime($arr_form_data['reportto']));

		$this->set('month', $from . ' to ' . $otdate);											  
//        $conditions = array();
        $conditions = "stock_tranfer.adjustment_date  between '$from' and '$otdate'  ";
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $arr_leavepolicydetails_for_template = array();
//        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $gross = array();
        if(empty($arr_leavepolicygroupids)){
            echo "<h1>Please Choose Criteria</h1>";
            return false;
        }
        $arr_stocksummary_for_template = array();
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
              $arr_empleaverequests = $this->EmpCtcTransaction->query("SELECT stock_tranfer.adjustment_date,stock_tranfer.adjustment_code,
               stock_tranfer.from_store,stock_tranfer.to_store,stock_tranfer_item.item_fkey,stock_tranfer_item.item_desc,
               stock_tranfer_item.required_qty,item_master.item_desc,
               (select store_location from store_master where store_master_pkey = stock_tranfer.to_store) as to_storelocation,
               (select store_location from store_master where store_master_pkey = stock_tranfer.from_store) as from_storelocation
               FROM `stock_tranfer` 
               join stock_tranfer_item on (stock_tranfer_item.stock_tranfer_fkey = stock_tranfer.stock_tranfer_pkey)
               join item_master on (item_master.item_master_pkey = stock_tranfer_item.item_fkey)
               WHERE stock_tranfer.from_store = '$leavepolicygroupid' and $conditions and stock_tranfer.status = 1 and stock_tranfer_item.status = 1 ORDER BY `stock_tranfer_pkey` DESC  ");

																														   
																	   
																																
																																   
									  
																																							 
																								  
																																																   
               //debug($arr_empleaverequests);
			 if (!empty($arr_empleaverequests)) {									
             $arr_stocksummary_for_template[] = $arr_empleaverequests; 
             }
            
        }
        
       //debug($arr_stocksummary_for_template);
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->set('type', $arr_form_data['select-criteria1']);
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
                $view_output = $view->render('stocktrnsfer');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('stocktransferreport.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "stocktransferreport.xlsx" : "stocktransferreport" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Stock Transfer Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Stock Transfer Report " . $from . " to " . $otdate);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'L'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->getColumnDimension('L')->setWidth(20);


$worksheet->setCellValueByColumnAndRow(0, 2, "Report run by " . $user_id . " - " . $date_time);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:L2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 4;

                if (count($arr_stocksummary_for_template) == 0) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Data Available With The Selected Criteria');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                } else {
                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                         if  ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                            $branch = isset($value['0']['employee_info']['EmpName']) ? "Allocation Report of ".$value['0']['employee_info']['EmpName'] : '';
                        } else {
                            $branch = isset($value['0']['0']['from_storelocation']) ? "Stock Transfer of ".$value['0']['0']['from_storelocation'] : '';
                        }
                        

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount);
                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        
//                        $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount);
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 2, "Report Run by " . $user_id ." at ". $date_time  );
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
//                 $worksheet->mergeCells('A2:E2');
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Adjustment Code');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'From Store ');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Transfered Store');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Transfered Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Transfered Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                
                        
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
										 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {
                               // debug($val);
//                                $sum += $val['0']['balance_qty']; 
                                

                                $slno = $i;
//                                $PO_Status = $val['po']['grn_status'];
//                                        $string_po = '';
//                                        switch ($PO_Status){
//                                            case "0": $string_po = "GRN Received";
//                                                break;
//                                            case "1": $string_po = "PO Ordered";
//                                                break;
//                                            case "2": $string_po = "Finalised";
//                                                break;
//                                            default : $string_po = "PO";
//                                                break;
//                                        }
                                $adjustment_code = $val['stock_tranfer']['adjustment_code'];
                                $store_from =  $val['0']['from_storelocation'];
                                $to_store = $val['0']['to_storelocation'];
                                $item_desc = $val['stock_tranfer_item']['item_desc'];
                                $adjustment_date = $val['stock_tranfer']['adjustment_date'];
                                $required_qty = $val['stock_tranfer_item']['required_qty'];
                                
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $adjustment_code);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $store_from);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $to_store);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $item_desc);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $adjustment_date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $required_qty);
                                                            
                                $rowcount++;
                                $i++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
                }		 
                $objPHPExcel->getActiveSheet()->setTitle('Stock Transfer Report');
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
                $this->render('stocktrnsfer');
                break;
        }
    }
    
   private function GenerateAllocationreport($mode){
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);

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

        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-d', strtotime($arr_form_data['reportto']));

//        $conditions = array();
        $conditions = "itm_allocation.date_allocated  between '$from' and '$otdate'  ";
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $arr_leavepolicydetails_for_template = array();
//        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $gross = array();
        if(empty($arr_leavepolicygroupids)){
             echo "<h1>Please Choose Criteria</h1>";
            return false;
        }
        $arr_empleaverequests = array();
        $arr_stocksummary_for_template = array();
        
        $user_group = $this->Session->read('user_group');
        $store_condition = "";
        if ($user_group == 2) {
            //The below code is to display only allocated stores to logged/current employee. by ***ARUL P DAS on 17/1/2020
            $cur_emp_key = $this->Session->read("emp_fkey");
            $join = array(array(
                    'table' => 'access_store',
                    'type' => 'INNER',
                    'conditions' => array(
                        'access_store.store_pkey = Store.store_master_pkey'
                    )
            ));
            $fields = array('DISTINCT `Store`.`store_master_pkey`, `Store`.`store_code`, `Store`.`status`');
            $st_conditions = array("Store.status" => 1, "access_store.emp_fkey" => $cur_emp_key, "access_store.status" => 1);
            $this->Store->useDbConfig = $this->Session->read('ds');
            $stores = $this->Store->find("all", array("fields" => $fields, "conditions" => $st_conditions, "joins" => $join));
//            debug($stores);
             if (count($stores) > 0) {
                $keys = "";
                $i = 0;
                $comma = "";
                foreach ($stores as $subloop) {
                    foreach ($subloop as $subloop2) {
                        if ($i > 0) {
                            $comma = ",";
                        }
                        $keys.=(string) $comma . " " . $subloop2['store_master_pkey'];
                        $i++;
                    }
                }
                $store_condition = " and store_master.store_master_pkey in (" . $keys . ") ";
            }
        }
        
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
            if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                 if ($user_group == 2) {
                    if (count($stores) > 0) {
                        $arr_empleaverequests = $this->EmpCtcTransaction->query("select itm_allocation.date_allocated AS date_allocated,employee_info.emp_pkey AS emp_pkey,employee_info.EmpName AS EmpName,
employee_info.employee_id AS employee_id,employee_info.branch AS branch,employee_info.designation AS designation,
employee_info.department AS department,employee_info.joining_date AS joining_date,store_master.store_code,store_master.store_location AS
store_location,item_master.item_desc AS item_desc,allocate_details.qty AS qty,allocate_details.returned_qty AS 
returned_qty,allocate_details.damaged_qty AS damaged_qty,additional_details.sales_price AS amount
from (((((allocate_details join itm_allocation on((itm_allocation.allocation_pkey = allocate_details.allocate_fkey))) 
join employee_info on((employee_info.emp_pkey = itm_allocation.emp_fkey))) join item_master 
on((item_master.item_master_pkey = allocate_details.item_purchase_fkey))) join store_master 
on((store_master.store_master_pkey = allocate_details.store_code))) join additional_details 
on((additional_details.item_master_fkey = allocate_details.item_purchase_fkey))) 
where itm_allocation.emp_fkey = '$leavepolicygroupid'  and ((allocate_details.qty - allocate_details.returned_qty - damaged_qty) > 0) and $conditions $store_condition and store_master.status=1 and allocate_details.allocate_status = 0 
order by allocate_details.allocate_details_pkey desc ");
                    }
                }else{
               $arr_empleaverequests = $this->EmpCtcTransaction->query("select itm_allocation.date_allocated AS date_allocated,employee_info.emp_pkey AS emp_pkey,employee_info.EmpName AS EmpName,
employee_info.employee_id AS employee_id,employee_info.branch AS branch,employee_info.designation AS designation,
employee_info.department AS department,employee_info.joining_date AS joining_date,store_master.store_code,store_master.store_location AS
store_location,item_master.item_desc AS item_desc,allocate_details.qty AS qty,allocate_details.returned_qty AS 
returned_qty,allocate_details.damaged_qty AS damaged_qty,additional_details.sales_price AS amount
from (((((allocate_details join itm_allocation on((itm_allocation.allocation_pkey = allocate_details.allocate_fkey))) 
join employee_info on((employee_info.emp_pkey = itm_allocation.emp_fkey))) join item_master 
on((item_master.item_master_pkey = allocate_details.item_purchase_fkey))) join store_master 
on((store_master.store_master_pkey = allocate_details.store_code))) join additional_details 
on((additional_details.item_master_fkey = allocate_details.item_purchase_fkey))) 
where itm_allocation.emp_fkey = '$leavepolicygroupid'  and ((allocate_details.qty - allocate_details.returned_qty - damaged_qty) > 0) and $conditions $store_condition and store_master.status=1 and allocate_details.allocate_status = 0 
order by allocate_details.allocate_details_pkey desc ");
                }
            }else{

                $user_group = $this->Session->read('user_group');
                if ($user_group == 2) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $branch_condition=" and emp_details.branch_code='".$cur_emp_branch."'";
                }else{
                    $branch_condition="";
                }
                
                $arr_empleaverequests = $this->EmpCtcTransaction->query("select itm_allocation.date_allocated AS date_allocated,employee_info.emp_pkey AS emp_pkey,employee_info.EmpName AS EmpName,
employee_info.employee_id AS employee_id,employee_info.branch AS branch,employee_info.designation AS designation,
employee_info.department AS department,employee_info.joining_date AS joining_date,store_master.store_code,store_master.store_location AS
store_location,item_master.item_desc AS item_desc,allocate_details.qty AS qty,allocate_details.returned_qty AS 
returned_qty,allocate_details.damaged_qty AS damaged_qty,additional_details.sales_price AS amount
from (((((allocate_details join itm_allocation on((itm_allocation.allocation_pkey = allocate_details.allocate_fkey))) 
join employee_info on((employee_info.emp_pkey = itm_allocation.emp_fkey))join emp_details on (emp_details.emp_pkey=employee_info.emp_pkey)) join item_master 
on((item_master.item_master_pkey = allocate_details.item_purchase_fkey))) join store_master 
on((store_master.store_master_pkey = allocate_details.store_code))) join additional_details 
on((additional_details.item_master_fkey = allocate_details.item_purchase_fkey))) 
where allocate_details.store_code = '$leavepolicygroupid'  and ((allocate_details.qty - allocate_details.returned_qty - damaged_qty) > 0) and $conditions $branch_condition and store_master.status=1 and allocate_details.allocate_status = 0 
order by allocate_details.allocate_details_pkey desc");
                
//                debug("select itm_allocation.date_allocated AS date_allocated,employee_info.emp_pkey AS emp_pkey,employee_info.EmpName AS EmpName,
//employee_info.employee_id AS employee_id,employee_info.branch AS branch,employee_info.designation AS designation,
//employee_info.department AS department,employee_info.joining_date AS joining_date,store_master.store_code,store_master.store_location AS
//store_location,item_master.item_desc AS item_desc,allocate_details.qty AS qty,allocate_details.returned_qty AS 
//returned_qty,allocate_details.damaged_qty AS damaged_qty,additional_details.sales_price AS amount
//from (((((allocate_details join itm_allocation on((itm_allocation.allocation_pkey = allocate_details.allocate_fkey))) 
//join employee_info on((employee_info.emp_pkey = itm_allocation.emp_fkey))) join item_master 
//on((item_master.item_master_pkey = allocate_details.item_purchase_fkey))) join store_master 
//on((store_master.store_master_pkey = allocate_details.store_code))) join additional_details 
//on((additional_details.item_master_fkey = allocate_details.item_purchase_fkey))) 
//where allocate_details.store_code = '$leavepolicygroupid'  and ((allocate_details.qty - allocate_details.returned_qty - damaged_qty) > 0) and $conditions and allocate_details.allocate_status = 0 
//order by allocate_details.allocate_details_pkey desc");
            }  
              if (!empty($arr_empleaverequests)) {
            $arr_stocksummary_for_template[] = $arr_empleaverequests;
              }
            
            
        }
        
    //   debug($arr_stocksummary_for_template);
        
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->set('type', $arr_form_data['select-criteria1']);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('from', $from);
        $this->set('otdate', $otdate);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('uniformallocation');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('uniformallocationreport.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "UniformAllocationReport.xlsx" : "UniformAllocationReport" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Uniform Allocation Report " . $from . ' to ' . $otdate);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'L'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->getColumnDimension('L')->setWidth(20);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 2, "Report Run by " . $user_id ." at ". $date_time  );
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->mergeCells('A2:L2');

                $rowcount = 3;


                if (count($arr_stocksummary_for_template) == 0) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Data Available With The Selected Criteria');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                } else {
                

                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                         if  ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                            $branch = isset($value['0']['employee_info']['EmpName']) ? "Allocation Report of ".$value['0']['employee_info']['EmpName'] : '';
                        } else {
                            $branch = isset($value['0']['store_master']['store_location']) ? "Allocation Report of ".$value['0']['store_master']['store_location'] : '';
                        }
                        

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('A'.$rowcount.':L'.$rowcount);
                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        
                        $worksheet->mergeCells('A'.$rowcount.':L'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Date Of Join');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Store');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Issued Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Issued Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Returned Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Balance Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Value');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                        
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
										 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {
                                
                                

                                $slno = $i;
//                                $PO_Status = $val['po']['grn_status'];
//                                        $string_po = '';
//                                        switch ($PO_Status){
//                                            case "0": $string_po = "GRN Received";
//                                                break;
//                                            case "1": $string_po = "PO Ordered";
//                                                break;
//                                            case "2": $string_po = "Finalised";
//                                                break;
//                                            default : $string_po = "PO";
//                                                break;
//                                        }
                                $employee_name = $val['employee_info']['EmpName'];
                                $employee_id =  $val['employee_info']['employee_id'];
                                $date_of_join = $val['employee_info']['joining_date'];
                                $branch = $val['employee_info']['branch'];
                                $department = $val['employee_info']['department'];
                                $designation = $val['employee_info']['designation'];
                                $item_name = $val['item_master']['item_desc'];
                                $Store_name = $val['store_master']['store_location'];
                                $issued_date = $val['itm_allocation']['date_allocated'];
                                $allocated_qty = $val['allocate_details']['qty'];
                                $retund_qty = $val['allocate_details']['returned_qty'];
                                $balance_qty  = $val['allocate_details']['qty'] - $val['allocate_details']['returned_qty'];
                                $amount = round($val['additional_details']['amount'] * (($val['allocate_details']['qty'] - $val['allocate_details']['returned_qty'])));
                                $sum += $amount; 
                                
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $employee_id);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT); 
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $employee_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $designation);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $department);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $date_of_join);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $item_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $Store_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $allocated_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $issued_date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $retund_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $balance_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $amount);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                
                                $rowcount++;
                                $i++;
                            }
                                  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "Grand Total");
                                  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                                  $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                  $worksheet->mergeCells('A'.$rowcount.':M'.$rowcount);
                                  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $sum);
                                  $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                  $rowcount++;
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
                }
                $objPHPExcel->getActiveSheet()->setTitle('Uniform Allocation Report');
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
                $this->render('uniformallocation');
                break;
        }
    }

    //----------------------//
    private function GenerateUniformAllocationEMIReport($mode){
        $arr_form_data = $_REQUEST;
								 
        $this->item_allocate->useDbConfig = $this->Session->read('ds');
        $this->LoanEmi->useDbConfig = $this->Session->read('ds');


        $arr_keys = $this->item_allocate->query("SELECT trim(salary_head_item_desc) as sal_head,head_operator FROM emp_salary_slip as ectc
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

        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-d', strtotime($arr_form_data['reportto']));
        $this->set('from', $from);
        $this->set('otdate', $otdate);
        
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
								 
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);

//        $conditions = array();
        $conditions = "itm_allocation.date_allocated  between '$from' and '$otdate'  ";
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        //debug($arr_form_data[$str_criteria_item]);
        $arr_leavepolicydetails_for_template = array();
//        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $gross = array();
        if(empty($arr_leavepolicygroupids)){
           echo "<h1>Please Choose Criteria</h1>";
            return false;
        }
          $arr_stocksummary_for_template = array();
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) { 
            if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                $user_group = $this->Session->read('user_group');
                $store_condition = "";
                $no_store_conditioin_of_emp = 0;
                if ($user_group == 2) {
                    //The below code is to display only allocated stores to logged/current employee. by ***ARUL P DAS on 17/1/2020
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $join = array(array(
                            'table' => 'access_store',
                            'type' => 'INNER',
                            'conditions' => array(
                                'access_store.store_pkey = Store.store_master_pkey'
                            )
                    ));
                    $fields = array('DISTINCT `Store`.`store_master_pkey`, `Store`.`store_code`, `Store`.`status`');
                    $st_conditions = array("Store.status" => 1, "access_store.emp_fkey" => $cur_emp_key, "access_store.status" => 1);
                    $this->Store->useDbConfig = $this->Session->read('ds');
                    $stores = $this->Store->find("all", array("fields" => $fields, "conditions" => $st_conditions, "joins" => $join));
//            debug($stores);
                    $keys = "";
                    $i = 0;
                    $comma = "";
                    if (count($stores) > 0) {
                        foreach ($stores as $subloop) {
                            foreach ($subloop as $subloop2) {
                                if ($i > 0) {
                                    $comma = ",";
                                }
                                $keys.=(string) $comma . " " . $subloop2['store_master_pkey'];
                                $i++;
                            }
                        }
                        $store_condition = " and store_master.store_master_pkey in (" . $keys . ") ";
                    } else {
                        $no_store_conditioin_of_emp = 1;
//                        echo "No data found under this Criteria";
//                        return false;
                    }
                }
                if ($no_store_conditioin_of_emp) {//This is to check whether an employee have stock. in no stocks, the report will be blank.
                    $arr_empleaverequests=array();
                } else {
                $arr_empleaverequests = $this->item_allocate->query("SELECT itm_allocation.date_allocated,itm_allocation.value,itm_allocation.loan_fkey,employee_info.*,allocate_details.*,store_master.store_location,item_master.item_desc FROM `allocate_details`"
                       ." left join itm_allocation on (itm_allocation.allocation_pkey = allocate_details.allocate_fkey)"
                                                                    ." left join employee_info on (employee_info.emp_pkey = itm_allocation.emp_fkey)"
                                                                    ." join item_master on (item_master.item_master_pkey = allocate_details.item_purchase_fkey)
                                                                    join store_master on (store_master.store_master_pkey = allocate_details.store_code)
                                                                    WHERE itm_allocation.emp_fkey = '$leavepolicygroupid'  and ((allocate_details.qty - allocate_details.returned_qty - damaged_qty) > 0) and $conditions
                                                                    $store_condition GROUP BY `allocation_pkey` ");
                        }
                                                                    }else{  
                 $user_group = $this->Session->read('user_group');
                if ($user_group == 2) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
//                    $branch_condition = " and site_pkey in (select site_fkey from site_attendance where emp_fkey in (select emp_pkey from emp_details where branch_code='" . $cur_emp_branch . "'))";
                    $conditions.=" and emp_details.branch_code='" . $cur_emp_branch . "'";
                }
                $arr_empleaverequests = $this->item_allocate->query("SELECT itm_allocation.*,employee_info.*,allocate_details.*,store_master.store_location
                                                                    FROM `allocate_details` left join itm_allocation on (itm_allocation.allocation_pkey = allocate_details.allocate_fkey) 
                                                                    join employee_info on (employee_info.emp_pkey = itm_allocation.emp_fkey) 
                                                                    join emp_details on (employee_info.emp_pkey = emp_details.emp_pkey and emp_details.status=1)
                                                                    join store_master on (store_master.store_master_pkey = allocate_details.store_code) 
                                                                    WHERE allocate_details.store_code = '$leavepolicygroupid'  and ((allocate_details.qty - allocate_details.returned_qty - damaged_qty) > 0)  and $conditions
                                                                    GROUP BY `allocation_pkey` ");
                                                                    }
                                                                    
                                                                    $arr_emireport = array();
                                                                    foreach ($arr_empleaverequests as $value) {
                                                              
                                                                            $loankey = isset($value['itm_allocation']['loan_fkey']) ? $value['itm_allocation']['loan_fkey'] : '0';
                                                                            $emiamount = $this->LoanEmi->query("SELECT `amt` FROM `emi_upload` WHERE `loan_pkey` = '$loankey' AND `status` = '1'");
                                                                            
                                                                            $emicount[] = $this->LoanEmi->query("SELECT COUNT(`loan_pkey`) FROM `emi_upload` WHERE `loan_pkey` = '$loankey' ");                                                                  
                                                                            $value['emi'] = $emiamount;
                                                                            if(!empty($value))
                                                                            $arr_emireport[] = $value;
                                                                    }  
          if(!empty($arr_emireport)){
            $arr_stocksummary_for_template[] = $arr_emireport;
          }
        } 
//         if(empty($arr_stocksummary_for_template)){
//            echo "No data found under this Criteria";
//            return false;
//        }
         if (isset($emicount)) {
            $maxemi = max($emicount);
            $maxemicount = isset($maxemi['0']['0']['COUNT(`loan_pkey`)']) ? $maxemi['0']['0']['COUNT(`loan_pkey`)'] : 1;
        } else {
            $maxemicount = 1;
        }		
							 
		 

        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->set('maxemicount', $maxemicount);
        $this->set('type', $arr_form_data['select-criteria1']);
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
                $view_output = $view->render('uniformemireport');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('uniformemireport.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "reportAllocationemi.xlsx" : "reportsalary" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

               $worksheet->setCellValueByColumnAndRow(0, 1, "Uniform Allocation EMI Report  " . $from . " to " . $otdate . "");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'L'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->getColumnDimension('L')->setWidth(20);
$worksheet->setCellValueByColumnAndRow(0, 2, "Report run by " . $user_id . " - " . $date_time);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:L2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 4;
                if (count($arr_stocksummary_for_template) == 0) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Data Available With The Selected Criteria');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                } else {
                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                         if  ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                            $branch = isset($value['0']['employee_info']['EmpName']) ? "Uniform Allocation EMI Report of ".$value['0']['employee_info']['EmpName'] : '';
                        } else {
                            $branch = isset($value['0']['store_master']['store_location']) ? "Uniform Allocation EMI  Report of ".$value['0']['store_master']['store_location'] : '';
                        }
                        

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('A'.$rowcount.':L'.$rowcount);
                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        
                        $worksheet->mergeCells('A'.$rowcount.':L'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Date Of Joining');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Store Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Issued Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Issued Qty');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Total Value');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        for($i=1; $i <= $maxemicount ; $i++){
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9+$i) . $rowcount, $i.' EMI');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9+$i))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9+$i, $rowcount)->getFont()->setBold(true);
                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9+$i) . $rowcount, 'Balance');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9+$i))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9+$i, $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
										 
                            $tot = 0;
                            $j = 1;
                            foreach ($arr_data as $val) {
//                                $sum += $val['0']['balance_qty']; 
                                

                                $slno = $j;
//                                $PO_Status = $val['po']['grn_status'];
//                                        $string_po = '';
//                                        switch ($PO_Status){
//                                            case "0": $string_po = "GRN Received";
//                                                break;
//                                            case "1": $string_po = "PO Ordered";
//                                                break;
//                                            case "2": $string_po = "Finalised";
//                                                break;
//                                            default : $string_po = "PO";
//                                                break;
//                                        }
                                $employee_name = $val['employee_info']['EmpName'];
                                $employee_id =  $val['employee_info']['employee_id'];
                                $date_of_join = $val['employee_info']['joining_date'];
                                $branch = $val['employee_info']['branch'];
                                $department = $val['employee_info']['department'];
                                $designation = $val['employee_info']['designation'];
                                $Store_name = $val['store_master']['store_location'];
                                $issueddate = $val['itm_allocation']['date_allocated'];
                                $allocated_qty = $val['allocate_details']['qty'];
                                $totalvalue = $val['itm_allocation']['value'];
                                
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $employee_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $employee_id);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $date_of_join);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $department);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $designation);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $Store_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $issueddate);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $allocated_qty);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $totalvalue);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $paid = 0;
                                for($i=0; $i < $maxemicount ; $i++ ) {
                                    $emi = isset($val['emi'][$i]['emi_upload']['amt']) ? $val['emi'][$i]['emi_upload']['amt'] : '0'; 
                                    $paid+=isset($val['emi'][$i]['emi_upload']['amt']) ? $val['emi'][$i]['emi_upload']['amt'] : '0';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10+$i) . $rowcount, $emi);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10+$i))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                }
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10+$i) . $rowcount, $totalvalue -$paid);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10+$i))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $rowcount++;
                                $j++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
			}	 
                $objPHPExcel->getActiveSheet()->setTitle('Uniform Allocation Report');
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
                $this->render('uniformemireport');
                break;
        }
    }
    
    







    private function GenerateSummaryPayrolreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        //$report_month = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date("Y-m-d");
        //$from = date('Y-m', strtotime($report_month));
//        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $from = $arr_form_data['reportfrom'];
        //debug($from);
        $arr_stocksummary_for_template = array();
        
        if(!isset($arr_form_data['Store'])){
        echo "No Items Selected ";
        return false; 
        }
        
        $arr_store = $arr_form_data['Store'];
        
        foreach ($arr_store as $val){ 
            
           /*  $arr_empleaverequests = $this->EmpCtcTransaction->query("select a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,sum(item_qty)qtyuptodate ,
                b.po_rate ,sum(item_qty)* b.po_rate as closingvalue from 
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
                item_except_grn_allocation_view where creation_date <= '$from' and store_master_pkey= '$val' group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from 
                grn_stock_details_date_view where gr_date <= '$from' and store_master_pkey= '$val' group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty	
                from Item_allocation_details_view where date_allocated <= '$from' and store_master_pkey= '$val' group by store_master_pkey,item_master_pkey,item_code,item_desc) a
                join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
                grn_stock_det_rate_date_view where gr_date <= '$from' and store_master_pkey= '$val' group by store_master_pkey,item_master_pkey,item_code,item_desc) b
                on (a.store_master_pkey= b.store_master_pkey 
               and a.item_master_pkey=b.item_master_pkey)
                group by store_master_pkey,item_master_pkey
                order by 4,5
                ;"); */
             //commented above query  by megha on 16/03/2020
            $arr_empleaverequests = $this->EmpCtcTransaction->query("select a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,sum(item_qty) qtyuptodate ,
b.po_rate ,sum(item_qty)* b.po_rate as closingvalue from
(select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
item_except_grn_allocation_view where creation_date <= '$from' and store_master_pkey= '$val'  
group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc
union all
select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
grn_stock_details_date_view where gr_date <= '$from' and store_master_pkey= '$val'  group by store_master_pkey,item_master_pkey,item_code,item_desc
union all
select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty
from Item_allocation_details_view where date_allocated <= '$from' and store_master_pkey= '$val'  group by store_master_pkey,item_master_pkey,item_code,item_desc) a
left join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
grn_stock_det_rate_date_view where gr_date <= '$from' and store_master_pkey= '$val'  group by store_master_pkey,item_master_pkey,item_code,item_desc) b
on (a.store_master_pkey= b.store_master_pkey
and a.item_master_pkey=b.item_master_pkey)
group by a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,
b.po_rate
order by 4,5;");
           //debug($arr_empleaverequests);
		   if (!empty($arr_empleaverequests)) {
            $arr_stocksummary_for_template[] = $arr_empleaverequests;
            }
            
            
        }
       //debug($arr_stocksummary_for_template);
        
//        debug($arr_stocksummary_for_template); die();
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('from', $from);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('empsalary');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
            //edited by megha on 14/08/2019 pdf size changed
                $html2pdf = new HTML2PDF('L', 'A3', 'en');
															
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage'); 
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('StockSummaryreport.pdf', 'D'); 
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "StockSummaryreport.xlsx" : "StockSummaryreport" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Stock Summary Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Stock Summary Report (" . $from . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:G1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 2, "Report Run by " . $user_id ." at ". $date_time  );
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                 $worksheet->mergeCells('A2:G2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

							  

                $rowcount = 2;
                
 if (count($arr_stocksummary_for_template) == 0) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 3, "No Data Available With The Selected Criteria");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                    $worksheet->mergeCells('A3:G3');
//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Data Available With The Selected Criteria');
//                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                } else {	
                foreach ($arr_stocksummary_for_template as $value) {
                   //debug($value);
                    if (count($value) !== 0) {
                        $rowcount++;
                        $branch = isset($value['0']['a']['store_location']) ? "Store - ".$value['0']['a']['store_location'] : '';

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        
                        $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Item Code');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Store Code');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Quantity');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Rate');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Amount (as on ' .$from. ' )');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                       
                        
                        $rowcount = $rowcount + 1;
                        
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
										 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {
																   
                                
                                
                                //  $branch = $val['month_year']. " ".$fromhalf;
                                $qty = $val['0']['qtyuptodate'];
                                
                                if($qty>0){
                                $sum += $val['0']['closingvalue']; 
                                $slno = $i;
                                $item_code = $val['a']['item_code'];
                                $it_name = $val['a']['item_desc'];
                                $store = $val['a']['store_location'];
																																					 
																	
                                $closing_val = $val['0']['closingvalue'];
                                $po_rate = $val['b']['po_rate'];

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $item_code);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $it_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $store);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $po_rate);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $closing_val);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                
                                $rowcount++;
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "Grand Total");
//                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
//                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        
//                                $worksheet->mergeCells('A'.$rowcount.':F'.$rowcount);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $sum);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $rowcount++;
                                $i++;
                              }
                            }
                                  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "Grand Total");
                                  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(11);
                                  $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                  $worksheet->mergeCells('A'.$rowcount.':F'.$rowcount);
                                  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $sum);
                                  $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                  $rowcount++;
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
                }		 
                $objPHPExcel->getActiveSheet()->setTitle('Stock Summary Report');
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
    
    private function GenerateStockreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
//        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $arr_stocksummary_for_template = array();
        
        if(!isset($arr_form_data['Store'])){
            echo "No Criteria Selected ";
            return false; 
        }
        $arr_store = $arr_form_data['Store'];
        //debug($arr_store);
       foreach ($arr_store as $val){
            $arr_empleaverequests = $this->EmpCtcTransaction->query("select z.store_master_pkey,z.store_location,z.store_location,z.item_master_pkey,z.item_code,z.item_desc,sum(z.stockitem_qty) opening_stock,
                ifnull(sum(b.received_qty),0) received_qty,ifnull(sum(c.issue_qty),0)issue_qty,ifnull(sum(d.damaged_qty),0)damaged_qty,
                ifnull(sum(e.returned_qty),0)returned_qty,ifnull(sum(f.transfer_qty),0) transfer_qty ,ifnull(sum(g.adjustment_qty),0) adjustment_qty,
                ifnull(sum(h.PORETURN_qty),0) PORETURN_qty , ifnull(sum(i.closing_qty),0) closing_qty
                from
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(a.item_qty)stockitem_qty from (
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty
                from item_except_grn_allocation_view where creation_date < '$from' and store_master_pkey = '$val' 		  
                group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty 
                from grn_stock_details_date_view where gr_date < '$from' and store_master_pkey = '$val'  
                group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty	
                from Item_allocation_details_view where date_allocated < '$from'and store_master_pkey = '$val'  
                group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc)a
                group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc )z 
                left join
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) received_qty 
                from grn_stock_details_date_view where gr_date between '$from' and '$to' and store_master_pkey = '$val'   
                group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc) b
                on (z.store_master_pkey= b.store_master_pkey and z.item_master_pkey=b.item_master_pkey)
                left join 
                ( select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(qty)issue_qty	
                from Item_allocation_details_view where date_allocated between '$from' and '$to' and store_master_pkey = '$val'  
                group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc)c
                on(z.store_master_pkey= c.store_master_pkey and z.item_master_pkey=c.item_master_pkey)
                left join 
                ( select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(damaged_qty)damaged_qty	
                from Item_allocation_details_view where date_allocated between '$from' and '$to' and store_master_pkey = '$val'  
                group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc)d
                on(z.store_master_pkey= d.store_master_pkey and z.item_master_pkey=d.item_master_pkey)
                left join
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(returned_qty)returned_qty	
                from Item_allocation_details_view where date_allocated between '$from' and '$to' and store_master_pkey = '$val'  
                group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc)e
                on(z.store_master_pkey= e.store_master_pkey and z.item_master_pkey=e.item_master_pkey)
                left join
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty)transfer_qty
                from item_except_grn_allocation_view where item_state='TRANSFER'  and creation_date between '$from' and '$to' 
                and store_master_pkey = '$val' group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc)f
                on(z.store_master_pkey= f.store_master_pkey and z.item_master_pkey=f.item_master_pkey)
                left join
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty)adjustment_qty
                from item_except_grn_allocation_view where item_state='ADJUSTMENT'  and creation_date between '$from' and '$to' 
                and store_master_pkey = '$val' group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc)g
                on(z.store_master_pkey= g.store_master_pkey and z.item_master_pkey=g.item_master_pkey)
                left join
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty)PORETURN_qty
                from item_except_grn_allocation_view where item_state='PORETURN'  and creation_date between '$from' and '$to' 
                and store_master_pkey = '$val' group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc)h
                on(z.store_master_pkey= h.store_master_pkey and z.item_master_pkey=h.item_master_pkey)
                left join
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(a.item_qty)closing_qty from (
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty
                from item_except_grn_allocation_view where creation_date <= '$to' and store_master_pkey = '$val'   
                group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty 
                from grn_stock_details_date_view where gr_date <= '$to' and store_master_pkey = '$val'  
                group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty	
                from Item_allocation_details_view where date_allocated <= '$to'and store_master_pkey = '$val'  
                group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc)a
                group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc )i
                on(z.store_master_pkey= i.store_master_pkey and z.item_master_pkey=i.item_master_pkey)
                where z.store_master_pkey = '$val' 
                group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc ");
				if (!empty($arr_empleaverequests)) {								
                $arr_stocksummary_for_template[] = $arr_empleaverequests;  
                }				
        }

        
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('from', $from);
        $day_before = date( 'Y-m-d', strtotime( $from . ' -1 day' ) );
        $this->set('to', $to);
        $this->set('month', $from. ' - '.$to);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('stockdetails');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('stockmovementreport.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "stockmovementreport.xlsx" : "stockmovementreport" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Stock Movement Report " . $from . " to " . $to);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'N'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 2, "Report Run by " . $user_id ." at ". $date_time  );
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                 $worksheet->mergeCells('A2:L2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 2;
                

              if (count($arr_stocksummary_for_template) == 0) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 3, "No Data Available With The Selected Criteria");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                    $worksheet->mergeCells('A3:L3');
//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Data Available With The Selected Criteria');
//                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                } else {
                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                        $branch = isset($value['0']['z']['store_location']) ? "Store - ".$value['0']['z']['store_location'] : '';

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        
                        $worksheet->mergeCells('A'.$rowcount.':L'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Item Code');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Opening Stock (as on ' .$day_before. ' )');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Received');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Issued');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Damaged');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Returned');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Transfer');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Adjustment');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'PO Return');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Closing Stock (as on ' .$to. ' )');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                        
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
										 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {
                                //debug($val);
                                $sum += $val['0']['closing_qty']; 
                                

                                $slno = $i;
                                $item_code = $val['z']['item_code'];
                                $it_name = $val['z']['item_desc'];
                                $opening_stock = $val['0']['opening_stock'];
                                $stock_qty = $val['0']['received_qty'];
                                $Issued_qty = $val['0']['issue_qty'];
                                $damaged_qty = $val['0']['damaged_qty'];
                                $returned_qty = $val['0']['returned_qty'];
                                $transfer_qty = $val['0']['transfer_qty'];
                                $adjustment_qty = $val['0']['adjustment_qty'];
                                $PORETURN_qty = $val['0']['PORETURN_qty'];
                                $balance_qty = $val['0']['closing_qty'];
                                //$rate = $val['0']['Prices'];
                                //$sum = round($val['0']['Prices'] * $val['0']['balance_qty']);
                                //  $branch = $val['month_year']. " ".$fromhalf;
                                //$qty = $val['0']['balance_qty'];
                                
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $item_code);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $it_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $opening_stock);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $stock_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $Issued_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $damaged_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $returned_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $transfer_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $adjustment_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $PORETURN_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $balance_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $rowcount++;
                                $i++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
                }		 
                $objPHPExcel->getActiveSheet()->setTitle('Stock Movement Report');
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
                $this->render('stockdetails');
                break;
        }
    }

    //summary report



    private function GenerateSalarySlipreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
//        $report_month = $arr_form_data['reportfrom'];
		$user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);								
        if(isset($arr_form_data['reportfrom']))
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));
//        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
//      
		  $this->set('month', $from . ' to ' . $to);										  
        $arr_stocksummary_for_template = array();
        
        if(!isset($arr_form_data['Store']))
            return false;
        $arr_store = $arr_form_data['Store'];
        
        foreach ($arr_store as $val){
            $arr_mr_details = array();
            $arr_empleaverequests = $this->EmpCtcTransaction->query("SELECT material_request.*,sm.store_location FROM material_request join store_master sm on (sm.store_master_pkey = material_request.store_code) where material_request.status = '1' and material_request.store_code = $val  and mr_date between '$from' and '$to'");
            
            $arr_material_details = array();
            
            foreach ($arr_empleaverequests as $value){
                
                $mr_fkey = isset($value['material_request']['mr_pkey'])?$value['material_request']['mr_pkey']:0;
//                debug($mr_fkey);
                $arr_mr_details = $this->EmpCtcTransaction->query("select mr_details.mr_fkey,item_master.item_code,mr_details.required_qty,mr_details.required_qty,mr_details.mr_fkey,item_master.item_desc from mr_details
                    join item_master on (item_master.item_master_pkey = mr_details.item_code) where mr_fkey = $mr_fkey and mr_details.status = 1 ");
               $value['Items'] = $arr_mr_details;
//               debug($arr_mr_details);
																																														 
               $arr_material_details[] = $value;
            }
            
			  if (!empty($arr_material_details)) {									
            $arr_stocksummary_for_template[] = $arr_material_details;
            }
            
            
        }
//        debug($arr_stocksummary_for_template);
        
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
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
                $view_output = $view->render('material');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('report.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "reportmaterial.xlsx" : "reportsalary" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Material Report " . $from . " to " . $to);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:G1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

				 $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:G2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 4;
                
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $active = 0;
				if (count($arr_stocksummary_for_template) == 0) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Data Available With The Selected Criteria');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                } else {
                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                        $i = 0;
                        $branch = isset($value['0']['sm']['store_location']) ? "Store - " . $value['0']['sm']['store_location'] : '';
                        
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $rowcount++;
                        
                        $arr_mr = $value;
                        
                            
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, "Sl No");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                            
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, "MR Code");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                            
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, "MR Date");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                            
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, "Item Code");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                                                        
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, "Item Name");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                            
//                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, "Customer");
//                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                                     
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, "Qty");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                            
                        foreach ($arr_mr as $val) {
                            
                            $mr_code = isset($val['material_request']['mr_code']) ? $val['material_request']['mr_code'] : '';
                            $mr_date = isset($val['material_request']['mr_date']) ? $val['material_request']['mr_date']:'';
                            $cutomer = isset($val['material_request']['customer_name']) ? $val['material_request']['customer_name'] : '';
                            $po_status = isset($val['material_request']['po_status']) ? $val['material_request']['po_status'] : '';
                            
                            $arr_data  = $val['Items'];
                            if(count($arr_data)>=0){
                                 $sum = 0; 
                                foreach($arr_data as $v){
                                    $rowcount++;
                                    $i++;
                                    $sum+=$v['mr_details']['required_qty'];
                                    $item_code = $v['item_master']['item_code'];
                                    $item_desc = $v['item_master']['item_desc'];
                                    $customer = $v['mr_details']['required_qty'];
                                    $qty = $v['mr_details']['required_qty'];
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $mr_code);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $mr_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $item_code);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $item_desc);
//                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $customer);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $qty);
                                }
                            }
                            
                                
                        }
                    }
                }//end foreach
                }		 
                $objPHPExcel->getActiveSheet()->setTitle('Material Report');
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
                $this->render('material');
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
        } else if ($prorate_code == '2') {
            $day['type'] = "Working Days";


            $holiday = $this->EmployeeDetails->query("SELECT count(ho.HOLIDAYID) as count FROM emp_proff as ep left join  holidays as ho on(ho.HOLIDAY_GROUP_ID=ep.HOLIDAY_GROUP_ID) where month(ho.HOLIDAYDATE)='$month' and year(ho.HOLIDAYDATE)='$year' and ep.emp_fkey='$id'");
            $weekoff = $this->EmployeeDetails->query("SELECT week_off_days FROM payroll_master WHERE emp_fkey = '$id' AND month_year = '$date'");
            // debug($holiday);die();
            $holiday_count = isset($holiday[0][0]['count']) ? $holiday[0][0]['count'] : 0;
            $weekoffdays = isset($weekoff[0]['payroll_master']['week_off_days']) ? $weekoff[0]['payroll_master']['week_off_days'] : 0;
            $day_count = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $day['days'] = $day_count - $weekoffdays - $holiday_count;
        } else {
            $day['type'] = "Fixed Days";
            $day['days'] = 30;
        }
        $day['present'] = $day['days'] - $lop;
        return $day;
    }

    //poReturn report
     private function GeneratePoReturnreport($mode){
        $arr_form_data = $_REQUEST;
        
        $this->PoReturn->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_month = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date("Y-m-d");
        $from = date('Y-m', strtotime($report_month));
        
        $fromdt = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));
		 $this->set('month', $fromdt . ' to ' . $to);

        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $arr_poreturn_for_template = array();
        if(!isset($arr_form_data['Store'])){
            echo "No Items Selected";
            return false;
        }
        $arr_store = $arr_form_data['Store'];
        
        foreach ($arr_store as $val){
        $arr_returnlist = $this->PoReturn->query("SELECT ri.*,sm.store_location,gr.gr_number,gr.gr_date,po.po_number,im.item_code,im.item_desc,gi.gr_item_pkey,gi.grn_fkey,gi.ordering_qty,gi.received_qty "
                    ." FROM `return_gr_items` ri "
                    ."join gr_item_details gi on (gi.gr_item_pkey = ri.grn_fkey) "
                    ."join item_master im on (im.item_master_pkey = ri.item_fkey) "
                    ."join store_master sm on (sm.store_master_pkey = ri.store_fkey) "
                    ."join goods_receved_notes gr on (gr.grn_pkey = gi.grn_fkey) "
                    ."join purchase_order po on (po.po_pkey = gi.po_fkey) "
                    ."where ri.store_fkey = '$val' and ri.return_date between '$fromdt' and '$to' and ri.status = 1 and delete_status = 1");
        if(!empty($arr_returnlist)){
        $arr_poreturn_for_template[] = $arr_returnlist;
        } 
            
        }
        
         //if(empty($arr_poreturn_for_template)){
        //    echo "No Data found under this Criteria";
       //     return false;
      //  }
     // debug($arr_poreturn_for_template);
        $this->set('arr_poreturn_for_template', $arr_poreturn_for_template);
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
                $view_output = $view->render('poreturn');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('PoReturnReport.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "poreturnreport.xlsx" : "poreturnreport" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Purchase order Return Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Purchase Order Return Report " . $fromdt . " to " . $to);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'K'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
$worksheet->setCellValueByColumnAndRow(0, 2, "Report run by " . $user_id . " - " . $date_time);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(15);
                $worksheet->mergeCells('A2:J2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 4;
                

 if (count($arr_poreturn_for_template) == 0) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Data Available With The Selected Criteria');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                } else {
                foreach ($arr_poreturn_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                        $branch = isset($value['0']['sm']['store_location']) ? "Store - ".$value['0']['sm']['store_location'] : '';

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(18);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        
                        $worksheet->mergeCells('A'.$rowcount.':J'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'PO Return Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'PO Return Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'GRN Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'GRN Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'PO Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Item Code');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Received Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Returned Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
										 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {

                                $slno = $i;
                                $r_number = $val['ri']['return_number'];
                                $r_date = $val['ri']['return_date'];
                                $gr_number = $val['gr']['gr_number'];
                                $gr_date = $val['gr']['gr_date'];
                                $po_number = $val['po']['po_number'];
                                $item_code = $val['im']['item_code'];
                                $item_nmes = $val['im']['item_desc'];
                                $received_qty = $val['gi']['received_qty']; 
                                $return_qty = $val['ri']['return_qty'];
                                
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $r_number);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $r_date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $gr_number);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $gr_date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $po_number);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $item_code);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $item_nmes);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $received_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $return_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                
                                $rowcount++;
                                $i++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
				} 
                $objPHPExcel->getActiveSheet()->setTitle('Purchase Order Return Report');
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
                $this->render('poreturn');
                break;
        }
    }
//edited by sreekanth on 29_5_2019 rearrangement of fields
    private function GeneratePoreport($mode){
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $user_id = $this->Session->read('login_user_id');       
        $report_month = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date("Y-m-d");
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));
		$this->set('month', $from . " to " . $to);										  
        $this->set('user_id', $user_id);
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
//      $to date('Y-m-t', strtotime($arr_form_data['reportfrom']));
//      
        $arr_stocksummary_for_template = array();
        if(!isset($arr_form_data['Store'])){
            echo "No Items Selected ";
            return false;
        }
        $arr_store = $arr_form_data['Store'];
        
        foreach ($arr_store as $val){
            
            $arr_empleaverequests = $this->EmpCtcTransaction->query("SELECT sm.store_location,po.grn_status,pd.po_item_pkey,pd.po_fkey,im.item_code,im.item_desc,pd.ordering_qty,pd.required_qty,pd.po_rate,po.po_date,po.po_number,po.supplier_name,mr.mr_code 
             FROM `po_item_details` pd left join purchase_order po on (po.po_pkey = pd.po_fkey) 
             left join material_request mr on (mr.mr_pkey = pd.mr_fkey)
             left join item_master im on (pd.item_code = im.item_master_pkey)
             join store_master sm on (sm.store_master_pkey = mr.store_code)
             where mr.store_code = '$val' and po_date between '$from' and '$to' and po.status = 1 ");           
            
			if (!empty($arr_empleaverequests)) {									
            $arr_stocksummary_for_template[] = $arr_empleaverequests;
								  
            }                      
        }
       // debug($arr_stocksummary_for_template);
        
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
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
                $view_output = $view->render('salaryslip');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('po_order_report.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "PoOrderReport.xlsx" : "reportpo_order" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Purchase Order Report " . $from . " to " . $to);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                                
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:G1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
																																	  
                  $worksheet->setCellValueByColumnAndRow(0, 2, "Report run by " . $user_id . " - " . $date_time);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:G2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 4;

                if (count($arr_stocksummary_for_template) == 0) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Data Available With The Selected Criteria');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                } else {	
                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                        $branch = isset($value['0']['sm']['store_location']) ? "Store - ".$value['0']['sm']['store_location'] : '';

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        
                        $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                     // Edited by  sreekanth -  20/05/2019  
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Item Code');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                    // Ends                          
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'PO Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'PO Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Supplier Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'MR Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Required Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Ordering Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'PO Rate');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'PO Status');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                        
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
										 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {
//                                $sum += $val['0']['balance_qty']; 
                                

                                $slno = $i;
                                $PO_Status = $val['po']['grn_status'];
                                        $string_po = '';
                                        switch ($PO_Status){
                                            case "0": $string_po = "GRN Received";
                                                break;
                                            case "1": $string_po = "PO Ordered";
                                                break;
                                            case "2": $string_po = "Finalised";
                                                break;
                                            default : $string_po = "PO";
                                                break;
                                        }
                                $item_code = $val['im']['item_code'];
                                $item_deesc = $val['im']['item_desc'];                                        
                                $po_number = $val['po']['po_number'];
                                $po_date = $val['po']['po_date'];
                                $supplier_name = $val['po']['supplier_name'];
                                $mr_number = $val['mr']['mr_code'];

                                $ordering_qty = $val['pd']['ordering_qty'];
                                $required_qty = $val['pd']['required_qty'];
                                $po_rate = $val['pd']['po_rate'];
                                $grn_status  = $string_po;
                                
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                     // Edited by  sreekanth -  20/05/2019  
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $item_code);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
         
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $item_deesc);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        // Ends        
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $po_number);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $po_date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $supplier_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $mr_number);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $required_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $ordering_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $po_rate);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $grn_status);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                
                                $rowcount++;
                                $i++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
				} 
                $objPHPExcel->getActiveSheet()->setTitle('Purchase Order  Report');
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
                $this->render('salaryslip');
                break;
        }
    }

												
    private function GenerateGRNreport($mode){
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_month = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date("Y-m-d");
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));
		$this->set('month', $from . ' to ' . $to);										  
//        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
//      
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);        
        
        $arr_stocksummary_for_template = array();
        if(!isset($arr_form_data['Store'])){
            echo "No Items Selected";
            return false;
        }
        $arr_store = $arr_form_data['Store'];
        
        foreach ($arr_store as $val){
            
            $arr_empleaverequests = $this->EmpCtcTransaction->query("SELECT sm.store_location,gr.gr_number,gr.gr_date,po.po_number,im.item_code,im.item_desc,gi.gr_item_pkey,gi.grn_fkey,gi.ordering_qty,gi.received_qty 
                FROM `gr_item_details` gi join goods_receved_notes gr on (gr.grn_pkey = gi.grn_fkey) 
                join purchase_order po on (po.po_pkey = gi.po_fkey) 
                join item_master im on (im.item_master_pkey = gi.item_code)
                join store_master sm on (sm.store_master_pkey = gr.store_code)
                where gr.store_code = '$val'  and gr_date between '$from' and '$to' and gr.status = 1 and gi.status = 1 ");
			  if (!empty($arr_empleaverequests)) {									
            $arr_stocksummary_for_template[] = $arr_empleaverequests;  
              }			
        }
        
//        debug($arr_stocksummary_for_template);
        
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
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
                $view_output = $view->render('grn');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('GRNreport.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "GRNReport.xlsx" : "reportsalary" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Goods Received Notes Report " . $from . " to " . $to);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
				$worksheet->setCellValueByColumnAndRow(0, 2, "(Report run by " . ($user_id) . " - " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(15);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:G1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 2, "Report Run by " . $user_id ." at ". $date_time  );
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $worksheet->mergeCells('A2:G2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

							  

                $rowcount = 4;

                if (count($arr_stocksummary_for_template) == 0) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Data Available With The Selected Criteria');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                } else { 
                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                        $branch = isset($value['0']['sm']['store_location']) ? "Store - ".$value['0']['sm']['store_location'] : '';

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        
                        $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                                             // Edited by  sreekanth -  20/05/2019  
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Item Code');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);                        
                                            // Ends
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'GRN Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'GRN Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'PO Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Ordering Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Received Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
										 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {

                                $slno = $i;
                                $item_code = $val['im']['item_code'];
                                $item_nmes = $val['im']['item_desc'];                                
                                $gr_number = $val['gr']['gr_number'];
                                $gr_date = $val['gr']['gr_date'];
                                $po_number = $val['po']['po_number'];
                                $ordering_qty = $val['gi']['ordering_qty'];
                                $received_qty = $val['gi']['received_qty'];
                                           // Edited by  sreekanth -  20/05/2019            
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $item_code);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $item_nmes);
            //Ends
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);                        
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $gr_number);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $gr_date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $po_number);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $ordering_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $received_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                
                                $rowcount++;
                                $i++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
                }		 
                $objPHPExcel->getActiveSheet()->setTitle('Goods Received Notes Report');
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
                $this->render('grn');
                break;
        }
    }
    //edited by sreekanth on 29_5_2019 rearrangement of fields
    
//    private function GenerateGRNreport($mode){
//        $arr_form_data = $_REQUEST;
								 
//        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
//        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
//        $report_month = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date("Y-m-d");

																																		  
																										   
																					
							 

									 

																							 
															   
					
																
			 
		 

//        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
//        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));
								  
									  
								
																					   
										   
																		
																							 
								   
									   
														
																		
																														   
		 
													
//        $arr_stocksummary_for_template = array();
//        if(!isset($arr_form_data['Store'])){
//            echo "No Items Selected";
//            return false;
						 
											  
												   
						 
//        }

																   
//        $arr_store = $arr_form_data['Store'];
//        
//        foreach ($arr_store as $val){
//            
//            $arr_empleaverequests = $this->EmpCtcTransaction->query("SELECT sm.store_location,gr.gr_number,gr.gr_date,po.po_number,im.item_code,im.item_desc,gi.gr_item_pkey,gi.grn_fkey,gi.ordering_qty,gi.received_qty 
//                FROM `gr_item_details` gi join goods_receved_notes gr on (gr.grn_pkey = gi.grn_fkey) 
//                join purchase_order po on (po.po_pkey = gi.po_fkey) 
																																															   
																																																																		  
																								  
											
					
																																																																																																						
																																								  
																																			 
//                join item_master im on (im.item_master_pkey = gi.item_code)
//                join store_master sm on (sm.store_master_pkey = gr.store_code)
//                where gr.store_code = '$val'  and gr_date between '$from' and '$to' and gr.status = 1 and gi.status = 1 ");

																																																														   
																								  
			 
											
									 
													   

																																																							
																																	   

																																   
										   
								   
											  
			 
										 
//            $arr_stocksummary_for_template[] = $arr_empleaverequests;   
			  
			  
			  
//        }
		 
													
													 
						 
		 

								 
																																						  

//        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
												
															   
//        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
//        $user_name = $this->Session->read('user_name');
//        $this->set('user_name', $user_name);
//        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
//        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
//        switch ($mode) {
//            case 'pdf' :
									 
//                $this->set('mode', 'pdf');
//                $view = new View($this, false);
//                $view_output = $view->render('grn');
//                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
//
//                $html2pdf = new HTML2PDF('L', 'A4', 'en');
//                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
//                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
//                $html2pdf->pdf->SetDisplayMode('fullpage');
//                $html2pdf->writeHTML($view_output);
//                $html2pdf->Output('report.pdf', 'D');
//                //$this->render('reportleavepolicy');
//                break;
//            case 'excel' :
//                $str_company_code = $this->Session->read('company_code');
//                $file_name = isset($str_company_code) ? $str_company_code . "reportGR.xlsx" : "reportsalary" . strtotime() . ".xlsx";
//
//                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
//                $objPHPExcel = new PHPExcel();
//
//                $objPHPExcel->getProperties()->setCreator("Administrator");
//                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
//                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
//                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
//                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
//
//                $objPHPExcel->setActiveSheetIndex(0);
//
//                $worksheet = $objPHPExcel->getActiveSheet();
//
//                $worksheet->setCellValueByColumnAndRow(0, 1, "Goods Received Notes Report");
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
//                for ($col = 'A'; $col !== 'G'; $col++) {
//                    $objPHPExcel->getActiveSheet()
//                            ->getColumnDimension($col)
//                            ->setAutoSize(true);
//                }
//                $worksheet->mergeCells('A1:G1');
//                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
//                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                );
//
//
//                $rowcount = 4;
//                
//

//                foreach ($arr_stocksummary_for_template as $value) {
//                    if (count($value) !== 0) {
//                        $rowcount++;
																															 
																																																																																						 
								
//                        $branch = isset($value['0']['sm']['store_location']) ? "Store - ".$value['0']['sm']['store_location'] : '';
//


//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        
//                        $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount);
////                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
////                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
////                        );
//                        
//                        $rowcount = $rowcount + 2;
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'GRN Number');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'GRN Date');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'PO Number');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Item Code');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Item Name');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Ordering Qty');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Received Qty');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
//                        
																																			 
																																														
																																								 
																																	   
																																														
																																								 
																																		  
																																														 
																																								  
													 
//                        $rowcount = $rowcount + 1;
//                        $arr_data  = $value;
//                        if(count($arr_data)>=0){ $sum = 0; 
//                            $tot = 0;
//                            $i = 1;
//                            foreach ($arr_data as $val) {
																	  
//
  
//                                $slno = $i;
//                                $gr_number = $val['gr']['gr_number'];
//                                $gr_date = $val['gr']['gr_date'];
																							 
																					
														
																				  
														
																				 
														
																		  
														
										   
//                                $po_number = $val['po']['po_number'];
																   
																			   
																	 
//                                $item_code = $val['im']['item_code'];
//                                $item_nmes = $val['im']['item_desc'];
//                                $ordering_qty = $val['gi']['ordering_qty'];
//                                $received_qty = $val['gi']['received_qty'];
																			 
															 
																   
															
//                                
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $gr_number);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $gr_date);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $po_number);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $item_code);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $item_nmes);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $ordering_qty);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $received_qty);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                
																																				  
																																																
																																			  
																																																
																																				  
																																																 
															 
//                                $rowcount++;
//                                $i++;
//                            }
//                        } else {
//
//                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
//                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        }
//                    }
//                }//end foreach
//                $objPHPExcel->getActiveSheet()->setTitle('Goods Received Notes Report');
//                /* header footer */
//                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
//                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
//                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
//                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
//                /* header footer */
//
//                /* print Set up */
//                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
//                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
//                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
//                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
//                /* print Set up */
//
//                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
//                $objWriter->save(dirname(__FILE__) . "/" . $file_name);
//
//                // output headers so that the file is downloaded rather than displayed
//                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
//                header('Content-Disposition: attachment; filename=' . $file_name);
//
//                readfile(dirname(__FILE__) . "/" . $file_name);
//                unlink(dirname(__FILE__) . "/" . $file_name);
//                break;
//            default :
//                $this->set('mode', '');
//                $this->render('grn');
//                break;
//        }
//    }
//    private function GeneratePoreport($mode){
//        $arr_form_data = $_REQUEST;
//        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
//        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
//        $report_month = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date("Y-m-d");
//        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
//        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));
////        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
////      
//        $arr_stocksummary_for_template = array();
//        if(!isset($arr_form_data['Store'])){
//            echo "No Items Selected ";
//            return false;
//        }
//        $arr_store = $arr_form_data['Store'];
//        
//        foreach ($arr_store as $val){
//            
//            $arr_empleaverequests = $this->EmpCtcTransaction->query("SELECT sm.store_location,po.grn_status,pd.po_item_pkey,pd.po_fkey,im.item_code,im.item_desc,pd.ordering_qty,pd.required_qty,pd.po_rate,po.po_date,po.po_number,po.supplier_name,mr.mr_code FROM `po_item_details` pd left join purchase_order po on (po.po_pkey = pd.po_fkey) 
//left join material_request mr on (mr.mr_pkey = pd.mr_fkey)
//left join item_master im on (pd.item_code = im.item_master_pkey)
//join store_master sm on (sm.store_master_pkey = mr.store_code)
//where mr.store_code = '$val' and po_date between '$from' and '$to'  ");
//            
//            $arr_stocksummary_for_template[] = $arr_empleaverequests;
//            
//            
//            
//        }
//        //debug($arr_stocksummary_for_template);
//        
//        
//        //debug($needBranchWiseReport);
//        // debug($arr_leavesummary_for_template);
//        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
//        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
//        $user_name = $this->Session->read('user_name');
//        $this->set('user_name', $user_name);
//        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
//        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
//        switch ($mode) {
//            case 'pdf' :
//                // echo "entered in";
//                $this->set('mode', 'pdf');
//                $view = new View($this, false);
//                $view_output = $view->render('salaryslip');
//                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
//
//                $html2pdf = new HTML2PDF('L', 'A3', 'en');
//                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
//                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
//                $html2pdf->pdf->SetDisplayMode('fullpage');
//                $html2pdf->writeHTML($view_output);
//                $html2pdf->Output('po_order_report.pdf', 'D');
//                //$this->render('reportleavepolicy');
//                break;
//            case 'excel' :
//                $str_company_code = $this->Session->read('company_code');
//                $file_name = isset($str_company_code) ? $str_company_code . "reportpo_order.xlsx" : "reportpo_order" . strtotime() . ".xlsx";
//
//                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
//                $objPHPExcel = new PHPExcel();
//
//                $objPHPExcel->getProperties()->setCreator("Administrator");
//                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
//                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
//                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
//                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
//
//                $objPHPExcel->setActiveSheetIndex(0);
//
//                $worksheet = $objPHPExcel->getActiveSheet();
//
//                $worksheet->setCellValueByColumnAndRow(0, 1, "Purchase Order Report");
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
//                for ($col = 'A'; $col !== 'G'; $col++) {
//                    $objPHPExcel->getActiveSheet()
//                            ->getColumnDimension($col)
//                            ->setAutoSize(true);
//                }
//                $worksheet->mergeCells('A1:G1');
//                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
//                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                );
//
//
//                $rowcount = 4;
//                
//
//                foreach ($arr_stocksummary_for_template as $value) {
//                    if (count($value) !== 0) {
//                        $rowcount++;
//                        $branch = isset($value['0']['sm']['store_location']) ? "Store - ".$value['0']['sm']['store_location'] : '';
//
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        
//                        $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount);
////                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
////                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
////                        );
//                        
//                        $rowcount = $rowcount + 2;
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'PO Number');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'PO Date');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Supplier Name');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'MR Number');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Item Code');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Item Name');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Required Qty');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
//                        //updated by megha 30_04_19
//                         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Ordering Qty');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'PO Rate');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'PO Status');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
//                        //updated by megha 30_04_19
																																																																								
																																																																																																			 
																																																														
						 
																																																																					
																																																																																																   
																																																 
//                        $rowcount = $rowcount + 1;
//                        $arr_data  = $value;
//                        if(count($arr_data)>=0){ $sum = 0; 
									 
//                            $tot = 0;
//                            $i = 1;
//                            foreach ($arr_data as $val) {
////                                $sum += $val['0']['balance_qty']; 
//                                
//
//                                $slno = $i;
//                                $PO_Status = $val['po']['grn_status'];
//                                        $string_po = '';
//                                        switch ($PO_Status){
//                                            case "0": $string_po = "GRN Received";
//                                                break;
//                                            case "1": $string_po = "PO Ordered";
//                                                break;
//                                            case "2": $string_po = "Finalised";
//                                                break;
//                                            default : $string_po = "PO";
//                                                break;
//                                        }
//                                $po_number = $val['po']['po_number'];
//                                $po_date = $val['po']['po_date'];
//                                $supplier_name = $val['po']['supplier_name'];
//                                $mr_number = $val['mr']['mr_code'];
//                                $item_code = $val['im']['item_code'];
//                                $item_deesc = $val['im']['item_desc'];
//                                $ordering_qty = $val['pd']['ordering_qty'];
//                                //updated by megha 30_04_19
//                                $required_qty = $val['pd']['required_qty'];
//                                //updated by megha 30_04_19
//                                $po_rate = $val['pd']['po_rate'];
//                                $grn_status  = $string_po;
//                                
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $po_number);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $po_date);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $supplier_name);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $mr_number);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $item_code);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $item_deesc);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $required_qty);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                //updated by megha 30_04_19
//                               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $ordering_qty);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $po_rate);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $grn_status);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                //updated by megha 30_04_19
//                                $rowcount++;
//                                $i++;
//                            }
//                        } else {
//
//                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
//                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        }
//                    }
//                }//end foreach
//                $objPHPExcel->getActiveSheet()->setTitle('Purchase Order  Report');
//                /* header footer */
//                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
//                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
//                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
//                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
//                /* header footer */
//
//                /* print Set up */
//                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
//                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
//                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
//                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
//                /* print Set up */
//
//                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
//                $objWriter->save(dirname(__FILE__) . "/" . $file_name);
//
//                // output headers so that the file is downloaded rather than displayed
//                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
//                header('Content-Disposition: attachment; filename=' . $file_name);
//
//                readfile(dirname(__FILE__) . "/" . $file_name);
//                unlink(dirname(__FILE__) . "/" . $file_name);
//                break;
//            default :
//                $this->set('mode', '');
//                $this->render('salaryslip'); 
//                break;
//        }
//    }
	  private function GenerateItemRatereport($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        
        $arr_stocksummary_for_template = array();
        
        if(!isset($arr_form_data['CategoryMaster'])){
        echo "No Items Selected ";
        return false; 
        }
        
        $arr_cat = $arr_form_data['CategoryMaster'];
        foreach ($arr_cat as $val){
            $arr_empleaverequests = $this->EmpCtcTransaction->query("select * from item_master "
                    . "left join category_master on (category_master.category_pkey = item_master.item_category) "
                    . "left join additional_details on (additional_details.item_master_fkey = item_master.item_master_pkey) "
                    . "where item_master.status = 1 and item_master.item_category = $val");
            
            if (!empty($arr_empleaverequests)) {
            $arr_stocksummary_for_template[] = $arr_empleaverequests;
            }
        }
       $arr_itemsummary_for_template = array();
        foreach ($arr_stocksummary_for_template as $itemreques) {
            foreach ($itemreques as $itemrequest) {
             $code = isset($itemrequest['item_master']['item_category']) ? $itemrequest['item_master']['item_category'] : '';
             $name = isset($itemrequest['category_master']['code']) ? $itemrequest['category_master']['code'] : '';
             if (!isset($arr_leavesummary_for_template[$name])) {
                        $arr_leavesummary_for_template[$name] = array(
                            'category_name' => $name,
                            'itemlists' => array()
                        );
                    }
             $request = array();
             $request['item_name'] = isset($itemrequest['item_master']['item_desc']) ? $itemrequest['item_master']['item_desc'] : '';
             $request['item_code'] = isset($itemrequest['item_master']['item_code']) ? $itemrequest['item_master']['item_code'] : '';
             $request['item_category'] = $name;
             $request['unit_price'] = isset($itemrequest['additional_details']['unit_price']) ? $itemrequest['additional_details']['unit_price'] : '';
             $request['sales_price'] = isset($itemrequest['additional_details']['sales_price']) ? $itemrequest['additional_details']['sales_price'] : '';
             $request['created_person'] = isset($itemrequest['item_master']['created_by']) ? $itemrequest['item_master']['created_by'] : '';
             $request['created_time'] = isset($itemrequest['item_master']['creation_date']) ? $itemrequest['item_master']['creation_date'] : '';
             $request['modified_person'] = isset($itemrequest['item_master']['modified_by']) ? $itemrequest['item_master']['modified_by'] : '';
             $request['modified_by'] = isset($itemrequest['item_master']['modified_date']) ? $itemrequest['item_master']['modified_date'] : '';
             $arr_itemsummary_for_template[$name]['itemlists'][] = $request;
            }
        }
      //debug($arr_leavesummary_for_template); die();
        //debug($needBranchWiseReport);
        $this->set('arr_itemsummary_for_template', $arr_itemsummary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('itemrate');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                $html2pdf = new HTML2PDF('L', 'A3', 'en');
															
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage'); 
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('ItemRateReport.pdf', 'D'); 
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "ItemRateReport.xlsx" : "ItemRateReport" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Item Rate Report By Greatleap");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();
                
                $worksheet->setCellValueByColumnAndRow(0, 1, "Item Rate Report" );
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(17);

                for ($col = 'A'; $col !== 'R'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 2, "Report Run by " . $user_id ." at ". $date_time  );
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $worksheet->mergeCells('A2:J2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
               
                $rowcount = 3;

                    foreach ($arr_itemsummary_for_template as $category_name => $itemsummary) {
                        
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $category_name);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $worksheet->mergeCells('A'.$rowcount.':J'.$rowcount);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Item code');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Item Category');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Unit Cost');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Sales Price');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Created Person');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Creation Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Modified Person');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Modification Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $arr_data = $itemsummary['itemlists'];
                        if (count($arr_data) > 0) {
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {
                                $slno = $i;
                                $itemname = $val['item_name'] ;
                                $itemcode = $val['item_code'];
                                $itemcategory = $val['item_category'];
                                $unitcost = $val['unit_price'];
                                $salesprice = $val['sales_price'];
                                $createdperson = $val['created_person'];
                                $creationdate = $val['created_time'];
                                $modifiedperson = $val['modified_person'];
                                $modificationdate = $val['modified_by'];
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $itemname);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $itemcode);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $itemcategory);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $unitcost);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $salesprice);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $createdperson);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $creationdate);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $modifiedperson);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $modificationdate);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $rowcount++;
                                $i++;
                            }
                             $rowcount1 = $rowcount - 1;
                             
                             $objPHPExcel->getActiveSheet()->getStyle('A3:J'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                             $BStyle = array(
                                'borders' => array(
                                'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                                    )
                                  )
                                );
                             $objPHPExcel->getActiveSheet()->getStyle('A1:J'.$rowcount1)->applyFromArray($BStyle);
                        } else {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found under this criteria.");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                
                $objPHPExcel->getActiveSheet()->setTitle('Item Rate Report');
     
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
                $this->render('itemrate');
                break;
        }
    }																																																												
																																																																																				 
}
					 
