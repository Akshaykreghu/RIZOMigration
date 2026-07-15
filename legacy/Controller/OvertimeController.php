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
class OvertimeController extends AppController
{

    /**
     * Controller name
     *
     * @var string   
     */
    //public $layout = "default";
    public $name = 'Overtime';
    public $datatable;

    /**
     * This controller does not use a model
     * 
     * @var array  
     */
    public $uses = array('ReportAudit', 'LeavePolicyGroup', 'EmployeeDetails', 'Banks',  'Units', 'ReportCriterias', 'EmployeeSalarySlip', 'EmpCtcTransaction', 'Departments'); //Edited by Akshay on 28-6-2024
    public $components = array('MasterdataManagement');


    /*
     * HR Reports landing view
     */
    public function hrreports()
    {
        $arr_reporttypes = array(
            'Overtime_Synthiet' => 'Overtime Bank Wise',
            'OT_SYNTHIET' => 'Overtime Department Wise Summary',                   //edited by ASHIN on 05-07-24
            'OT_MONTHWISE' => 'Overtime Summary Month Wise'
        );

        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */
    public function changereporttype($type = '')
    {
        $this->autoRender = FALSE;
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {

                case 'Overtime_Synthiet':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'OT_SYNTHIET':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'OT_MONTHWISE':
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
    // if($this->_modelExists($model)){
    //     $this->set('index',$index);
    //     $model = ($model == 'EmployeeDetails')?'Employees':$model;

    public function loadcriteriaitems($index, $str_criteria = '')
    {
        $this->autoRender = FALSE;
        if ($str_criteria != '') {
            $model = $str_criteria;
            if ($this->_modelExists($model) || $model == 'Gender') {
                if ($str_criteria == 'LeavePolicyGroup' || $str_criteria == 'BankStatement') {
                    $model = 'Banks';
                }
                $this->set('index', $index);
                $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
                $model = ($model == 'Departments') ? 'Departments' : $model;
                $model = ($model == 'Designation') ? 'Designations' : $model;
                $model = ($model == 'Gender') ? 'Genders' : $model;
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
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');
        // debug( $arr_requestdata);
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');
            if (trim($model) != 'Gender') {
                $this->{$model}->useDbConfig = $this->Session->read('ds');
            }

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
                //edited by sinsiya
                if ($user_group == 2) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
                    $branch = $payroUser[0]['emp_proff']['emp_branch'];
                    if ($payroUser[0]['emp_proff']['payro_priv'] == 1) {
                        $conditions[] = "Units.branch_code !='$branch' ";
                    }
                    //debug($conditions);
                }
            } else {
                $conditions = array("status" => 1);
            }


            if ($model == 'LeavePolicyGroup' || $model == 'Banks') {
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $conditions[] = "status = '1' or status = '2' ";
                if ($user_group == 2) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
                    $branch = $payroUser[0]['emp_proff']['emp_branch'];
                    if ($payroUser[0]['emp_proff']['payro_priv'] == 1) {
                        $conditions[] = "emp_details.branch_code !='$branch' ";
                    }
                    //debug($conditions);
                }
                $model = 'Banks';

                $sql = "
    SELECT DISTINCT IFNULL(SUBSTRING_INDEX(bank_details, ',', 1), bank_name) AS bank_name 
    FROM payroll_master pm 
    INNER JOIN emp_details ON emp_details.emp_pkey = pm.emp_fkey 
    UNION 
    SELECT DISTINCT bank_name 
    FROM emp_details 
    WHERE " . implode(" AND ", $conditions) . "
    ORDER BY bank_name ASC
";
                //debug($sql);

                // Execute the SQL query
                $arr_criteriaItemsDB1 = $this->EmployeeDetails->query($sql);

                $arr_criteriaItemsDB = array();
                foreach ($arr_criteriaItemsDB1 as $val) {
                    if ($val['0']['bank_name'] != '') {
                        $arr_criteriaItemsDB[] = $val['0'];
                    }
                }
            } else if ($model == 'Gender') {
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $conditions = array();

                $arr_criteriaItemsDB[0] = 'Female';
                $arr_criteriaItemsDB[1] = 'Male';
                $arr_criteriaItemsDB[2] = 'Transgender';
            } else if ($model == 'Departments') {
                $arr_order = array("Departments.dept_name" => "ASC");
                $conditions = array("status" => 1);

                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions, "order" => array("Departments.dept_name" => "ASC"))));
            } else if ($model == 'Designation') {
                if ($user_group == 2) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
                    $branch = $payroUser[0]['emp_proff']['emp_branch'];
                    if ($payroUser[0]['emp_proff']['payro_priv'] == 1) {
                        $conditions[] = "EmpProff.emp_branch!='$branch' ";
                    }
                    //debug($conditions);
                }
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array(
                    'joins' => array(
                        array(
                            'table' => 'emp_proff', // Assuming 'emp_proff' is the table name
                            'alias' => 'EmpProff',
                            'type' => 'INNER',
                            'conditions' => array(
                                'EmpProff.designation = Designation.desig_code'
                            )
                        )
                    ),
                    'conditions' => $conditions,
                    'order' => array('Designation.desig_name' => 'ASC'),
                    'group' => array('Designation.id') //Edited by Akshay on 18-3-2024
                )));
                // debug($arr_criteriaItemsDB);
                // exit;
                // $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions, "order" => array("Designation.desig_name" => "ASC"))));
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


                    // $arr_order = array("EmployeeDetails.emp_name" => "ASC");
                    $arr_order = array("CONCAT(EmployeeDetails.first_name, IFNULL(EmployeeDetails.last_name, ' '))" => "ASC");

                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                        $conditions = array("status in(1,2)");
                    } else {

                        $conditions = array("status" => 1);
                    }
                    //edited by sinsiya
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $user_group = $this->Session->read('user_group');
                    if ($user_group == 2) {
                        $cur_emp_key = $this->Session->read("emp_fkey");
                        $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
                        $branch = $payroUser[0]['emp_proff']['emp_branch'];
                        if ($payroUser[0]['emp_proff']['payro_priv'] == 1) {
                            $conditions[] = "EmployeeDetails.branch_code!='$branch' ";
                        }
                        //debug($conditions);
                    }
                    //$this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions,
                        "order" => $arr_order
                    ));
                    //debug($arr_emp);
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
                    //Edited by Akshay on 15-9-2023
                case 'Gender':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = strtolower($value);
                        $arr_criteriaItems[$key]['text'] = $value;
                        $key++;
                    }
                    break;
                case 'Designation':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['desig_code'];
                        $arr_criteriaItems[$key]['text'] = $value['desig_name'];
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
            case 'History':
                $dataForHistory['report_type'] = "Assets History Report";
                break;
        }

        $dataForHistory['report_from'] = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
        $dataForHistory['report_to'] = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] : '';
        // $dataForHistory['include_resigned'] = isset($arr_form_data['resigned']) ? $arr_form_data['resigned'] : '';
        // $dataForHistory['Include_negative_salary'] = isset($arr_form_data['ngtvsal']) ? $arr_form_data['ngtvsal'] : '';
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
                case 'Units':
                    $criteria_name_array[] = 'belonging to a Branch';
                    break;
                case 'LeavePolicyGroup':
                    $criteria_name_array[] = 'belonging to a Bank';
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
    public function downloadHistory($type, $mode)
    {
        $this->autoRender = false;

        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'Overtime_Synthiet':
                $dataForHistory['report_type'] = "Overtime Bank Wise Report";
                break;
            case 'OT_SYNTHIET':
                $dataForHistory['report_type'] = "Overtime Department Wise Summary Report";                //edited by ASHIN on 05-07-24
                break;
            case 'OT_MONTHWISE':
                $dataForHistory['report_type'] = "Overtime Month Wise Report";
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
                case 'Designation':
                    $criteria_name_array[] = 'belonging to a Designation';
                    break;
                case 'Gender':
                    $criteria_name_array[] = 'belonging to a Gender';
                    break;
                default:
                    break;
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

    public function generatereport($type = '', $mode = '')
    {
        $this->autoRender = false;

        switch ($type) {
            case 'Overtime_Synthiet':
                $this->generateOvertimeSynthietreport($mode);
                break;
            case 'OT_SYNTHIET':
                $this->generateOtSynthietreport($mode);
                break;
            case 'OT_MONTHWISE':
                $this->generateOtMonthwiseReport($mode);
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

    public function generateOvertimeSynthietreport($mode)
    {
        $arr_form_data = $_REQUEST;
        //debug($arr_form_data);
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');

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

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }

        //        $condition = "and EmployeeDetails.status ='1'  ";
        //        $condition2 = "WHERE emp_status = '1'";
        //        $condition3 = "WHERE status = '1'";
        //        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
        //            $condition = "and EmployeeDetails.status in ('1','2') ";
        //            $condition2 = "WHERE emp_status in ('1','2')";
        //            $condition3 = "WHERE status in ('1','2')";
        //        }

        //Negative Salary added.
        if (isset($arr_form_data['ngtvsal']) && $arr_form_data['ngtvsal'] == '1') {
            $conditions1 = " ";
        } else {
            $conditions1 = " and  payroll_master.net_salary >= 0 ";
        }
        $condition = "WHERE EmployeeDetails.status = 1";
        //        debug($arr_form_data);
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

            $condition =  "WHERE EmployeeDetails.status in('1','2')";
        }

        $arr_leavepolicydetails_for_template = array();
        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $gross = array();
        //added by megha on 27_06_19 $arr_salary_for_template declaration
        $arr_salary_for_template = array();
        //if( $arr_leavepolicygroupids != 'SELECTALL'){
        if (!empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                //     if ($arr_form_data['select-criteria1'] == 'Departments') {
                //         //$arr_settle = array();
                //         $arr_gross = $this->EmpCtcTransaction->query("select info.*,user_credentials.user_id,tr.last_approved_working_date,payroll_master.*,EmployeeDetails.*,department.*,emp_proff.*, ifnull(EmployeeDetails.bank_name,'NO NAME') as BANK_NAME,payroll_master.bank_details 
                //          from payroll_master  join employee_info as info on (info.emp_pkey = payroll_master.emp_fkey)
                //          join emp_details as EmployeeDetails on (EmployeeDetails.emp_pkey = payroll_master.emp_fkey $condition)
                //           join emp_proff on (emp_proff.emp_fkey = EmployeeDetails.emp_pkey)
                //           join department on (department.dept_code = emp_proff.emp_dept)
                //          left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey and tr.status = 1)
                //          join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey)
                //          and month_year = '$from' and department.dept_code = '$leavepolicygroupid' $conditions1 and payroll_master.action in ('Approved','Processed') ORDER BY payroll_master.emp_name");
                //         //edited by megha on 13/11/2019 settlement amount 2 
                //         $arr_settle[] = $this->EmpCtcTransaction->query(" select sum(salary_amount),info.*,ectc.*,department.*,emp_proff.* from emp_settle_slip as ectc 
                //     left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
                //     left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
                //    join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)
                //           join department on (department.dept_code = emp_proff.emp_dept)
                //     left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
                //     where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and department.dept_code = '$leavepolicygroupid'  and date_format(termination.last_approved_working_date,'%Y-%m') = '$from'  group by emp_details.emp_pkey");

                // debug("select info.*,user_credentials.user_id,tr.last_approved_working_date,payroll_master.*,EmployeeDetails.*,department.*,emp_proff.*, ifnull(EmployeeDetails.bank_name,'NO NAME') as BANK_NAME,payroll_master.bank_details 
                // from payroll_master  join employee_info as info on (info.emp_pkey = payroll_master.emp_fkey)
                // join emp_details as EmployeeDetails on (EmployeeDetails.emp_pkey = payroll_master.emp_fkey $condition)
                //  join emp_proff on (emp_proff.emp_fkey = EmployeeDetails.emp_pkey)
                //  join department on (department.dept_code = emp_proff.emp_dept)
                // left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey and tr.status = 1)
                // join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey)
                // and month_year = '$from' and department.dept_code = '$leavepolicygroupid' $conditions1 and payroll_master.action in ('Approved','Processed') ORDER BY payroll_master.emp_name");exit();
                // }
                // else if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                //      $arr_gross = $this->EmpCtcTransaction->query("select info.*,user_credentials.user_id,tr.last_approved_working_date,
                //         payroll_master.net_salary,EmployeeDetails.*,ifnull(EmployeeDetails.bank_name,'NO NAME') as BANK_NAME,payroll_master.bank_details 
                //     from payroll_master  join employee_info as info on (info.emp_pkey = payroll_master.emp_fkey)
                //     join emp_details as EmployeeDetails on (EmployeeDetails.emp_pkey = payroll_master.emp_fkey $condition)
                //      left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey and tr.status = 1)
                //      join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey)
                //      and month_year = '$from' and payroll_master.emp_fkey = '$leavepolicygroupid' $conditions1 and EmployeeDetails.status in (1,2) and payroll_master.action in ('Approved','Processed')  ORDER BY payroll_master.emp_name ");
                //     //edited by megha on 13/11/2019 settlement amount 1
                //     $arr_settle[] = $this->EmpCtcTransaction->query(" select sum(salary_amount),info.*,ectc.* from emp_settle_slip as ectc  
                // left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
                // left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
                // left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
                // where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.emp_pkey='$leavepolicygroupid' and date_format(termination.last_approved_working_date,'%Y-%m') = '$from'  group by emp_details.emp_pkey");
                //     $settle = isset($arr_settle['0']['0']['sum(salary_amount)']) ? $arr_settle['0']['0']['sum(salary_amount)'] : 0;
                // }
                if ($arr_form_data['select-criteria1'] == 'Units') {

                    //                 $arr_gross = $this->EmpCtcTransaction->query("
                    //    SELECT 
                    //        info.*, 
                    //        user_credentials.user_id, 
                    //        tr.last_approved_working_date, 
                    //        payroll_master.*, 
                    //        EmployeeDetails.*, 
                    //        IFNULL(EmployeeDetails.bank_name, 'NO NAME') AS BANK_NAME, 
                    //        payroll_master.bank_details,
                    //        SUM(ectc.salary_amount) AS total_salary_amount
                    //    FROM 
                    //        payroll_master
                    //        JOIN employee_info AS info ON info.emp_pkey = payroll_master.emp_fkey
                    //        JOIN emp_details AS EmployeeDetails ON EmployeeDetails.emp_pkey = payroll_master.emp_fkey $condition
                    //        LEFT JOIN termination AS tr ON tr.emp_fkey = payroll_master.emp_fkey AND tr.status = 1
                    //        JOIN user_credentials AS user_credentials ON user_credentials.emp_fkey = payroll_master.emp_fkey
                    //        LEFT JOIN emp_salary_slip AS ectc ON ectc.emp_fkey = payroll_master.emp_fkey
                    //            AND ectc.end_date_effective IS NULL
                    //            AND ectc.salary_head_item_fkey = 107
                    //            AND ectc.month_year = '$from'
                    //            AND EmployeeDetails.branch_code = '$leavepolicygroupid'
                    //    WHERE 
                    //        payroll_master.month_year = '$from' 
                    //        AND EmployeeDetails.branch_code = '$leavepolicygroupid' $conditions1 
                    //        AND payroll_master.action IN ('Approved', 'Processed')
                    //    GROUP BY 
                    //        info.emp_pkey
                    //    ORDER BY 
                    //        payroll_master.emp_name
                    //");
                    //EDITED BY ASHIN ANTONY ON 26-07-24
                    //edited by ASHIN on 30-07-24                  
                    //                    $arr_gross = $this->EmpCtcTransaction->query("
                    //    SELECT 
                    //        info.*, 
                    //        user_credentials.user_id, 
                    //        tr.last_approved_working_date, 
                    //        EmployeeDetails.*, 
                    //        IFNULL(EmployeeDetails.bank_name, 'NO NAME') AS BANK_NAME, 
                    //        payroll_master.bank_details,
                    //         eot.*
                    //    FROM 
                    //        payroll_master
                    //        JOIN employee_info AS info ON info.emp_pkey = payroll_master.emp_fkey
                    //        JOIN emp_details AS EmployeeDetails ON EmployeeDetails.emp_pkey = payroll_master.emp_fkey $condition
                    //        LEFT JOIN termination AS tr ON tr.emp_fkey = payroll_master.emp_fkey AND tr.status = 1
                    //        JOIN user_credentials AS user_credentials ON user_credentials.emp_fkey = payroll_master.emp_fkey   
                    //        LEFT JOIN emp_ot_process AS eot ON eot.emp_ot_master_fkey = payroll_master.emp_fkey
                    //            AND eot.month = CONCAT('$from', '-01')
                    //            AND EmployeeDetails.branch_code = '$leavepolicygroupid'
                    //    WHERE 
                    //        payroll_master.month_year = '$from' 
                    //        AND EmployeeDetails.branch_code = '$leavepolicygroupid' $conditions1 
                    //        AND payroll_master.action IN ('Approved', 'Processed')
                    //    GROUP BY 
                    //        info.emp_pkey
                    //    ORDER BY 
                    //        payroll_master.emp_name
                    // ");
                    $arr_gross = $this->EmpCtcTransaction->query("SELECT `OTMASTER`.*,eot.*,"
                        . "`EmployeeDetails`.first_name,`EmployeeDetails`.ifsc_code,`EmployeeDetails`.account_no,`EmployeeDetails`.last_name,`Info`.*,`EmployeeDetails`.status,last_approved_working_date, uc.user_id"
                        . " FROM `emp_ot_master` AS `OTMASTER`"
                        . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                        . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `termination` ON (`termination`.`emp_fkey`=`EmployeeDetails`.`emp_pkey` and `termination`.`status`=1)"
                        . "LEFT JOIN `user_credentials` AS `uc` ON (`uc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `emp_ot_process` AS `eot` ON (`eot`.`emp_ot_master_fkey` = `OTMASTER`.`emp_ot_master_pkey`) "
                        . "$condition and  eot.month = CONCAT('$from', '-01') and EmployeeDetails.branch_code = '$leavepolicygroupid' and is_verified = 'Y' GROUP BY `emp_ot_master_pkey` "
                        . "ORDER BY CONCAT(`EmployeeDetails`.first_name, ' ', `EmployeeDetails`.last_name) ASC ");

                    // debug(("SELECT `OTMASTER`.*,eot.*,"
                    // . "`EmployeeDetails`.first_name,`EmployeeDetails`.ifsc_code,`EmployeeDetails`.account_no,`EmployeeDetails`.last_name,`Info`.*,`EmployeeDetails`.status,last_approved_working_date, uc.user_id"
                    // . " FROM `emp_ot_master` AS `OTMASTER`"
                    // . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                    // . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                    // . "LEFT JOIN `termination` ON (`termination`.`emp_fkey`=`EmployeeDetails`.`emp_pkey` and `termination`.`status`=1)"
                    // . "LEFT JOIN `user_credentials` AS `uc` ON (`uc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                    // . "LEFT JOIN `emp_ot_process` AS `eot` ON (`eot`.`emp_ot_master_fkey` = `OTMASTER`.`emp_ot_master_pkey`) "
                    // . "$condition and  eot.month = CONCAT('$from', '-01') and EmployeeDetails.branch_code = '$leavepolicygroupid' and is_verified = 'Y' GROUP BY `emp_ot_master_pkey` " 
                    // . "ORDER BY CONCAT(`EmployeeDetails`.first_name, ' ', `EmployeeDetails`.last_name) ASC "));
                    //                    foreach ($arr_gross as $arrg) {
                    //                        $vemp_pkey = $arrg['EmployeeDetails']['emp_pkey'];
                    //                        // debug($vemp_pkey);
                    //                        $arr_amount = $this->EmpCtcTransaction->query("select sum(ifnull(set_duration,0)) as vot_duration  from emp_ot_master
                    //        where emp_fkey = $vemp_pkey and DATE_FORMAT(month,'%Y-%m')=$from and is_verified='Y'");
                    //                        $arr_structure_det_value = $this->EmpCtcTransaction->query("select structure_det_value from salary_structure_details where salary_head_item_fkey=107");
                    //                        // debug($arr_structure_det_value);debug($arr_amount);
                    //                        if ($arr_amount[0][0]['vot_duration'] != null) {
                    //                            foreach ($arr_structure_det_value as $struct) {
                    //                                $setvemp_salary = $struct['salary_structure_details']['structure_det_value'] * ($arr_amount[0][0]['vot_duration'] / 60);
                    //                            }
                    //                        }
                    //                    }
                    //   debug($setvemp_salary);
                    //if (vot_duration is not null then
                    //   set vemp_salary = vstructure_det_value*(vot_duration/60) ;



                    // debug($arr_gross); 
                    // exit; 
                    //edited by ASHIN on 08-08-24                  
                } else {
                    $arr_gross = $this->EmpCtcTransaction->query("SELECT `OTMASTER`.*,eot.*,"
                        . "`EmployeeDetails`.first_name,`EmployeeDetails`.ifsc_code,`EmployeeDetails`.`bank_name`,`EmployeeDetails`.account_no,`EmployeeDetails`.last_name,`Info`.*,`EmployeeDetails`.status,last_approved_working_date, uc.user_id"
                        . " FROM `emp_ot_master` AS `OTMASTER`"
                        . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                        . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `termination` ON (`termination`.`emp_fkey`=`EmployeeDetails`.`emp_pkey` and `termination`.`status`=1)"
                        . "LEFT JOIN `user_credentials` AS `uc` ON (`uc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `emp_ot_process` AS `eot` ON (`eot`.`emp_ot_master_fkey` = `OTMASTER`.`emp_ot_master_pkey`) "
                        . "$condition and  eot.month = CONCAT('$from', '-01') and EmployeeDetails.bank_name = '$leavepolicygroupid' and is_verified = 'Y' GROUP BY `emp_ot_master_pkey` "
                        . "ORDER BY CONCAT(`EmployeeDetails`.first_name, ' ', `EmployeeDetails`.last_name) ASC ");


                    //condition added on 30/03/2022 megha
                    //edited by sinsiya 08-03-2024 for admin split
                    //   $condition4 = '';
                    //if($user_group==2){
                    //  $cur_emp_key = $this->Session->read("emp_fkey");
                    //    $payroUser =$this->EmpCtcTransaction->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
                    //   $branch= $payroUser[0]['emp_proff']['emp_branch'];
                    //debug($branch);
                    //   if($payroUser[0]['emp_proff']['payro_priv'] == 1){
                    //  $condition4 = "and info.branch_code !='$branch' ";
                    //  }else {
                    //  $condition4 = ""; // Reset the condition if the privilege is not 1
                    // }
                    //debug($conditions);
                    //}


                    //                $arr_gross = $this->EmpCtcTransaction->query("select ifnull(ifnull(SUBSTRING_INDEX(bank_details,',',1),EmployeeDetails.bank_name),'NO NAME') as  BANK_NAME,info.*,user_credentials.user_id,tr.last_approved_working_date,payroll_master.*,EmployeeDetails.*,
                    //                payroll_master.bank_details from payroll_master join employee_info as info on (info.emp_pkey = payroll_master.emp_fkey $condition4)
                    //                join emp_details as EmployeeDetails on (EmployeeDetails.emp_pkey = payroll_master.emp_fkey $condition)
                    //                left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey and tr.status = 1)
                    //                join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey)
                    //                and payroll_master.emp_fkey in ( SELECT emp_details.emp_pkey FROM emp_details  join payroll_master pm on (emp_details.emp_pkey = pm.emp_fkey) where  ifnull(emp_details.bank_name,'X') = '$leavepolicygroupid' and (ifnull(SUBSTRING_INDEX(bank_details,',',1),'X') = '' or bank_details is NULL) and month_year='$from'
                    //                union select pm.emp_fkey from payroll_master pm where ifnull(SUBSTRING_INDEX(bank_details,',',1),TRIM(EmployeeDetails.bank_name)) = '$leavepolicygroupid' and month_year='$from') "
                    //                        . "and  ifnull(SUBSTRING_INDEX(bank_details,',',1),EmployeeDetails.bank_name) = '$leavepolicygroupid' and month_year = '$from' $conditions1 and payroll_master.action in ('Approved','Processed') "
                    //                        . "ORDER BY payroll_master.emp_name");
                    //                //edited by megha on 13/11/2019 settlement amount 3 
                    //                $arr_settle[] = $this->EmpCtcTransaction->query(" select sum(ectc.salary_amount),ectc.salary_amount,info.* from emp_salary_slip as ectc  
                    //                left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
                    //                left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
                    //                where ectc.end_date_effective is null and ectc.salary_head_item_fkey=107 and month_year = '$from'"); 
                    //EDITED BY ASHIN ANTONY ON 26-07-24  
                    //edited by ASHIN on 07-08-24
                    //    try{         
                    //                     $arr_gross = $this->EmpCtcTransaction->query("
                    //     SELECT 
                    //         IFNULL(IFNULL(SUBSTRING_INDEX(`bank_details`, ',', 1), `EmployeeDetails`.`bank_name`), 'NO NAME') AS `BANK_NAME`,
                    //         `info`.*, 
                    //         `user_credentials`.`user_id`, 
                    //         `tr`.`last_approved_working_date`, 
                    //         `payroll_master`.*, 
                    //         `EmployeeDetails`.*, 
                    //         `payroll_master`.`bank_details`,
                    //         `eot`.*
                    //     FROM 
                    //         `payroll_master`
                    //     JOIN 
                    //         `employee_info` AS `info` ON `info`.`emp_pkey` = `payroll_master`.`emp_fkey` 
                    //     JOIN 
                    //         `emp_details` AS `EmployeeDetails` ON `EmployeeDetails`.`emp_pkey` = `payroll_master`.`emp_fkey` 

                    //     LEFT JOIN 
                    //         `termination` AS `tr` ON `tr`.`emp_fkey` = `payroll_master`.`emp_fkey` AND `tr`.`status` = 1
                    //     JOIN 
                    //         `user_credentials` AS `user_credentials` ON `user_credentials`.`emp_fkey` = `payroll_master`.`emp_fkey`
                    //     LEFT JOIN 
                    //         `emp_ot_process` AS `eot` ON `eot`.`emp_ot_master_fkey` = `payroll_master`.`emp_fkey`
                    //         AND `eot`.`month` = CONCAT('$from', '-01') 
                    //       LEFT JOIN 
                    //         `emp_ot_master` AS `OTMASTER` ON `OTMASTER`.`emp_ot_master_pkey` = `payroll_master`.`emp_fkey`        
                    //     WHERE 
                    //         `payroll_master`.`emp_fkey` IN (
                    //             SELECT 
                    //                 `emp_details`.`emp_pkey` 
                    //             FROM 
                    //                 `emp_details`  
                    //             JOIN 
                    //                 `payroll_master` `pm` ON `emp_details`.`emp_pkey` = `pm`.`emp_fkey` 
                    //             WHERE  
                    //                 IFNULL(`emp_details`.`bank_name`, 'X') = '$leavepolicygroupid' 
                    //                 AND (IFNULL(SUBSTRING_INDEX(`bank_details`, ',', 1), 'X') = '' OR `bank_details` IS NULL) 
                    //                 AND `month_year` = '$from'
                    //             UNION 
                    //             SELECT 
                    //                 `pm`.`emp_fkey` 
                    //             FROM 
                    //                 `payroll_master` `pm` 
                    //             WHERE 
                    //                 IFNULL(SUBSTRING_INDEX(bank_details, ',', 1), TRIM(EmployeeDetails.bank_name)) = '$leavepolicygroupid' 
                    //                 AND month_year = '$from'
                    //         )
                    //         AND IFNULL(SUBSTRING_INDEX(`bank_details`, ',', 1), `EmployeeDetails`.`bank_name`) = '$leavepolicygroupid' 
                    //         AND `payroll_master`.`month_year` = '$from' 
                    //         $conditions1 
                    //         AND `payroll_master`.`action` IN ('Approved', 'Processed')
                    //     GROUP BY 
                    //         `payroll_master`.`emp_fkey`
                    //     ORDER BY 
                    //         `payroll_master`.`emp_name`
                    //  ");
                    //         //    }catch(Exception $e) {
                    //         //    debug($e->getMessage());
                    //         //    }
                    //   debug(("
                    //   SELECT 
                    //       IFNULL(IFNULL(SUBSTRING_INDEX(`bank_details`, ',', 1), `EmployeeDetails`.`bank_name`), 'NO NAME') AS `BANK_NAME`,
                    //       `info`.*, 
                    //       `user_credentials`.`user_id`, 
                    //       `tr`.`last_approved_working_date`, 
                    //       `payroll_master`.*, 
                    //       `EmployeeDetails`.*, 
                    //       `payroll_master`.`bank_details`,
                    //       `eot`.*
                    //   FROM 
                    //       `payroll_master`
                    //   JOIN 
                    //       `employee_info` AS `info` ON `info`.`emp_pkey` = `payroll_master`.`emp_fkey` 
                    //   JOIN 
                    //       `emp_details` AS `EmployeeDetails` ON `EmployeeDetails`.`emp_pkey` = `payroll_master`.`emp_fkey` 

                    //   LEFT JOIN 
                    //       `termination` AS `tr` ON `tr`.`emp_fkey` = `payroll_master`.`emp_fkey` AND `tr`.`status` = 1
                    //   JOIN 
                    //       `user_credentials` AS `user_credentials` ON `user_credentials`.`emp_fkey` = `payroll_master`.`emp_fkey`
                    //   LEFT JOIN 
                    //       `emp_ot_process` AS `eot` ON `eot`.`emp_ot_master_fkey` = `payroll_master`.`emp_fkey`
                    //       AND `eot`.`month` = CONCAT('$from', '-01') 
                    //     LEFT JOIN 
                    //       `emp_ot_master` AS `OTMASTER` ON `OTMASTER`.`emp_ot_master_pkey` = `payroll_master`.`emp_fkey`        
                    //   WHERE 
                    //       `payroll_master`.`emp_fkey` IN (
                    //           SELECT 
                    //               `emp_details`.`emp_pkey` 
                    //           FROM 
                    //               `emp_details`  
                    //           JOIN 
                    //               `payroll_master` `pm` ON `emp_details`.`emp_pkey` = `pm`.`emp_fkey` 
                    //           WHERE  
                    //               IFNULL(`emp_details`.`bank_name`, 'X') = '$leavepolicygroupid' 
                    //               AND (IFNULL(SUBSTRING_INDEX(`bank_details`, ',', 1), 'X') = '' OR `bank_details` IS NULL) 
                    //               AND `month_year` = '$from'
                    //           UNION 
                    //           SELECT 
                    //               `pm`.`emp_fkey` 
                    //           FROM 
                    //               `payroll_master` `pm` 
                    //           WHERE 
                    //               IFNULL(SUBSTRING_INDEX(bank_details, ',', 1), TRIM(EmployeeDetails.bank_name)) = '$leavepolicygroupid' 
                    //               AND month_year = '$from'
                    //       )
                    //       AND IFNULL(SUBSTRING_INDEX(`bank_details`, ',', 1), `EmployeeDetails`.`bank_name`) = '$leavepolicygroupid' 
                    //       AND `payroll_master`.`month_year` = '$from' 
                    //       $conditions1 
                    //       AND `payroll_master`.`action` IN ('Approved', 'Processed')
                    //   GROUP BY 
                    //       `payroll_master`.`emp_fkey`
                    //   ORDER BY 
                    //       `payroll_master`.`emp_name`
                    // "));
                    //                    $arr_gross = $this->EmpCtcTransaction->query("SELECT `OTMASTER`.*,eot.*,"
                    //                        . "`EmployeeDetails`.first_name,`EmployeeDetails`.ifsc_code,`EmployeeDetails`.account_no,`EmployeeDetails`.last_name,`Info`.*,`EmployeeDetails`.status,last_approved_working_date, uc.user_id"
                    //                        . " FROM `emp_ot_master` AS `OTMASTER`"
                    //                        . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                    //                        . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                    //                        . "LEFT JOIN `termination` ON (`termination`.`emp_fkey`=`EmployeeDetails`.`emp_pkey` and `termination`.`status`=1)"
                    //                        . "LEFT JOIN `user_credentials` AS `uc` ON (`uc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                    //                        . "LEFT JOIN `emp_ot_process` AS `eot` ON (`eot`.`emp_ot_master_fkey` = `OTMASTER`.`emp_ot_master_pkey`) "
                    //                        . "LEFT JOIN `payroll_master` AS `pm` ON (`pm`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`) "    
                    //                        . "$condition and  eot.month = CONCAT('$from', '-01') and IFNULL(SUBSTRING_INDEX(pm.bank_details, ',', 1), EmployeeDetails.bank_name) = '$leavepolicygroupid'"
                    //                        . "and payroll_master.month_year = '$from'  and is_verified = 'Y' GROUP BY `emp_ot_master_pkey`" 
                    //                        . "ORDER BY CONCAT(`EmployeeDetails`.first_name, ' ', `EmployeeDetails`.last_name) ASC "); 
                }
                //
                //                 debug("SELECT `OTMASTER`.*,eot.*,"
                //                        . "`EmployeeDetails`.first_name,`EmployeeDetails`.ifsc_code,`EmployeeDetails`.account_no,`EmployeeDetails`.last_name,`Info`.*,`EmployeeDetails`.status,last_approved_working_date, uc.user_id"
                //                        . " FROM `emp_ot_master` AS `OTMASTER`"
                //                        . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                //                        . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                //                        . "LEFT JOIN `termination` ON (`termination`.`emp_fkey`=`EmployeeDetails`.`emp_pkey` and `termination`.`status`=1)"
                //                        . "LEFT JOIN `user_credentials` AS `uc` ON (`uc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                //                        . "LEFT JOIN `emp_ot_process` AS `eot` ON (`eot`.`emp_ot_master_fkey` = `OTMASTER`.`emp_ot_master_pkey`) "
                //                        . "$condition and  eot.month = CONCAT('$from', '-01') and  EmployeeDetails.bank_name = '$leavepolicygroupid' and is_verified = 'Y' GROUP BY `emp_ot_master_pkey` " 
                //                        . "ORDER BY CONCAT(`EmployeeDetails`.first_name, ' ', `EmployeeDetails`.last_name) ASC");
                // exit;
                if (!empty($arr_gross)) {
                    $arr_salary_for_template[] = array(
                        'leavepolicyname' => isset($arr_gross) ? $arr_gross : '',
                        // 'settlement' => isset($arr_settle)?$arr_settle:''
                        // 'employees'=>$arr_leavepolicy_employees
                    );
                }
                $gross[] = isset($arr_gross) ? $arr_gross : array();
                $k++;
            }
        }
        //edited by ASHIN on 30-07-24      
        //        if ($arr_form_data['select-criteria1'] == 'LeavePolicyGroup' || $arr_form_data['select-criteria1'] == 'Banks') {
        //            $arr_gross1 = $this->EmpCtcTransaction->query("select ifnull(ifnull(SUBSTRING_INDEX(bank_details,',',1),EmployeeDetails.bank_name),'NO NAME') BANK_NAME,info.*,user_credentials.user_id,tr.last_approved_working_date,payroll_master.*,eot.*,EmployeeDetails.*,
        //                payroll_master.bank_details from payroll_master join employee_info as info on (info.emp_pkey = payroll_master.emp_fkey)
        //                join emp_details as EmployeeDetails on (EmployeeDetails.emp_pkey = payroll_master.emp_fkey $condition)
        //                left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey and tr.status = 1)
        //                join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey)
        //                left join emp_ot_process as eot on (eot.emp_ot_master_fkey = payroll_master.emp_fkey)
        //                and payroll_master.emp_fkey in (select distinct emp_fkey  from payroll_master pm join emp_details on (emp_details.emp_pkey = pm.emp_fkey) where 
        //                month_year='$from' and (ifnull(SUBSTRING_INDEX(bank_details,',',1),'X') = '' or bank_details is NULL and emp_details.bank_name = '')
        //                and action in ('Approved','Processed')) and month_year = '$from' $conditions1 and
        //                payroll_master.action in ('Approved','Processed') ORDER BY payroll_master.emp_name");
        //            if (!empty($arr_gross1)) {
        //                $arr_salary_for_template[] = array(
        //                    'leavepolicyname' => isset($arr_gross1) ? $arr_gross1 : ''
        //
        //                );
        //            }
        //        }


        function num2alpha($n)
        {
            $r = '';
            for ($i = 1; $n >= 0 && $i < 10; $i++) {
                $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
                $n -= pow(26, $i);
            }
            return $r;
        }

        $crt = $arr_form_data['select-criteria1'];
        $this->set('criteria', $crt);
        $this->set('keys', $arr_keys);
        //  $this->set('arr_settle', $arr_settle);
        $this->set('gross', $gross);
        $this->set('array_key', $array_key);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $user_id = $this->Session->read('login_user_id');
        $date_time = date('d-m-Y h:i A');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $this->set('month', $from);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $arr_banks  = $this->EmpCtcTransaction->query("Select acct_no,bank_branch from bank where status=1 limit 1");
        //  $account_number = isset($arr_banks['0']['bank']['acct_no'])?$arr_banks['0']['bank']['acct_no']:'';
        //  $bank_branch = isset($arr_banks['0']['bank']['bank_branch'])?$arr_banks['0']['bank']['bank_branch']:'';
        //$this->set('account_number', $account_number);
        // $this->set('bank_branch', $bank_branch);
        $this->set('month', $from);
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        // $date_time = date('d.m.Y');
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);

        //Set informations needed for report
        //debug($mode);
        switch ($mode) {
            case 'pdf':
                // echo "entered in";
                $str_company_code = $this->Session->read('company_code');
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('overtime');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
               // $html2pdf->Output('Overtime Bank Wise'  . $from .  '.pdf', 'D');
                $html2pdf->Output($str_company_code  . " Overtime Bank Wise "  . $from .  '.pdf', 'D');       
                $this->render('overtime');
                break;


            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ?  $str_company_code . " Overtime Bank Wise " . $from . ".xlsx" : "OvertimeReport" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Over Time Bank Wise - " . $mname . "  " . $year);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);

                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:L2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                for ($col = 'A'; $col !== 'AZ'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 2;
                //  $i = 0;

                //Border style
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );

                $empty = false;
                $col = 0;
                $i = 0;
                $table_count = 0;
                foreach ($arr_salary_for_template as $value) {
                    foreach ($value as $valuees) {
                        if (!empty($valuees)) {
                            $i++;
                        }
                    }
                }

                if ($i == 0) {
                    $worksheet->mergeCells('A3:L3');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . (3), 'No data available under the selected criteria');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(11);
                    // $row=$rowcount-2;
                } else {
                    if ($crt != 'Banks') {
                        // $rowcount = 2;
                        // $i = 0;

                        // for ($col = 'A'; $col !== 'G'; $col++) {
                        //     $objPHPExcel->getActiveSheet()
                        //             ->getColumnDimension($col)
                        //             ->setAutoSize(true);
                        // }

                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(14);
                        // $worksheet->mergeCells('A1:G1');
                        // $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        // );



                        $col = 0;
                        $total = 0;

                        $rowcount = 3;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'ACCOUNT NO');    //EDITED BY ASHIN ON 07-08-24
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'AMOUNT');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, 'IFSC CODE');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, 'BENEFICIARY ACC');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 'BENEFICIARY NAME');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, 'ADDRESS');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, 'ACCOUNT NAME');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(false);
                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setWidth(18);
                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col + 1)->setAutoSize(false);
                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col + 1)->setWidth(10);
                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col + 5)->setAutoSize(false);
                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col + 5)->setWidth(12);
                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col + 6)->setAutoSize(false);
                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col + 6)->setWidth(25);
                        $j = 1;

                        $rowcount = $rowcount + 1;

                        foreach ($arr_salary_for_template as $value) {
                            $col = 0;
                            foreach ($value['leavepolicyname'] as $val) {

                                $settle = 0;
                                // foreach ($arr_settle as $values) {
                                // $pkey = isset($values['0']['info']['emp_pkey']) ? $values['0']['info']['emp_pkey'] : 0;
                                // if ($val['EmployeeDetails']['emp_pkey'] == $pkey) {
                                //    $settle = $values[0][0]['sum(salary_amount)'];
                                //  }
                                //  }
                                $empstatus = (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : '';
                                $col = 0;
                                $name = $val['Info']['EmpName'] . $empstatus;
                                $bnfcry_acc = $val['EmployeeDetails']['account_no'];
                                $ifsc_code = $val['EmployeeDetails']['ifsc_code'];
                                $account_number = '338505040050011';
                                // $netamt = round($val['emp_salary_slip']['salary_amount']);
                                if ($cr == 'Departments') {
                                    $id = $val['department']['dept_code'];
                                } elseif ($cr == 'Units') {
                                    $id = $val['Info']['emp_id'];
                                } else if ($cr == 'EmployeeDetails') {
                                    $id = $val['ed']['emp_id'];
                                } else {
                                    $id = $val['Info']['emp_id'];
                                }
                                $bank_branch = "PANCODE";
                                $bank = isset($val['0']['EmployeeDetails']['bank_name']) ? $val['0']['EmployeeDetails']['bank_name'] : '';     //edited by ASHIN on 08-08-24
                                //debug($val);

                                //edited by ASHIN on 08-08-24               
                                if ($bank != '') {
                                    list($bank_name, $branch_name) = explode(',', $bank);
                                } else {
                                    $bank_name = '';
                                    $branch_name = '';
                                }

                                $ot = isset($val['ot']) ? round(($val['ot'] / 60), 2) : 0;

                                if ($crt != 'Banks' || $crt != 'LeavePolicyGroup') {           //edited by ASHIN on 08-08-24
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                                    $objPHPExcel->setActiveSheetIndex(0);
                                    $sheet = $objPHPExcel->getActiveSheet();

                                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                                    );
                                    $worksheet->getStyle('B' . $rowcount)->getAlignment()->applyFromArray(
                                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                                    );
                                    $worksheet->getStyle('C' . $rowcount)->getAlignment()->applyFromArray(
                                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                                    );
                                    $worksheet->getStyle('D' . $rowcount)->getAlignment()->applyFromArray(
                                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                                    );
                                    //EDITED BY ASHIN ANTONY ON 26-07-24 
                                    //edited by ASHIN on 30-07-24 
                                    $dd_amt = round($val['eot']['ot_amount']);
                                    $netamt = $dd_amt;
                                    //   if (isset($val['eot']['ot_amount'])) {
                                    //     $netamt = $val['eot']['ot_amount'];
                                    // } else {
                                    //     $netamt = 0; // or some other default value
                                    // }

                                    $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $rowcount)->setValueExplicit($account_number, PHPExcel_Cell_DataType::TYPE_STRING);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $netamt);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $ifsc_code);
                                    //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $bnfcry_acc);
                                    $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col + 3, $rowcount)->setValueExplicit($bnfcry_acc, PHPExcel_Cell_DataType::TYPE_STRING);        //edited by ASHIN on 04-07-24

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $bank_branch);
                                    $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col + 6, $rowcount)->setValueExplicit('HERBAL ISOLATES P LTD', PHPExcel_Cell_DataType::TYPE_STRING);

                                    $col = 4;
                                    $col = $col + 1;
                                    $total = $total + $netamt;
                                    $table_count++;
                                    $rowcount++;
                                    $j++;
                                }
                            }
                        }
                        if ($table_count != 0) {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Total');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $total);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $rowcount++;

                            $BStyle = array(
                                'borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN
                                    )
                                )
                            );

                            $row = $rowcount - 1;
                            $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $row)->applyFromArray($BStyle);
                        }

                        if ($table_count == 0) {
                            $worksheet->mergeCells('A3:L3');
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . (3), 'No data available under the selected criteria');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(11);
                            // $row=$rowcount-2;
                        }




                        // }else{

                        //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . (2), 'No data available under the selected criteria');

                    } else {
                        $rowcount = 2;
                        $table_count = 0;
                        $temp_bankrow = array();
                        $total = 0;


                        $rowcount = 3;
                        $current_row = $rowcount;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'ACCOUNT NO');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'AMOUNT');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, 'IFSC CODE');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, 'BENEFICIARY ACC');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 'BENEFICIARY NAME');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, 'ADDRESS');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, 'ACCOUNT NAME');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);

                        $rowcount = $rowcount + 1;
                        foreach ($arr_salary_for_template as $value) {
                            $i = 0;
                            $col = 0;
                            $j = 1;





                            foreach ($value['leavepolicyname'] as $val) {
                                $table_count++;

                                //  $settle = 0;
                                //  foreach ($arr_settle as $values) {
                                //     $pkey = isset($values['0']['info']['emp_pkey']) ? $values['0']['info']['emp_pkey'] : 0;

                                //   if ($val['EmployeeDetails']['emp_pkey'] == $pkey) {
                                //       $settle = $values[0][0]['sum(salary_amount)'];
                                //    }
                                //   }
                                // if ($val['payroll_master']['net_salary'] > 0) {
                                $empstatus = (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : '';
                                $col = 0;
                                $name = $val['info']['EmpName'] . $empstatus;

                                $bank_name = '';
                                // $branch_name = '';
                                $ifsc_code = $val['EmployeeDetails']['ifsc_code'];
                                // $acc_number = '';
                                $account_number = '338505040050011';
                                $bnfcry_acc = $val['EmployeeDetails']['account_no'];

                                $bank = isset($val['EmployeeDetails']['bank_name']) ? $val['EmployeeDetails']['bank_name'] : '';         //edited by ASHIN on 08-08-24
                                $bank_branch = "PANCODE";
                                if ($bank != '') {
                                    list($bank_name, $branch_name) = explode(',', $bank);
                                }

                                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                                );
                                //Left align Amount
                                $worksheet->getStyle('B' . $rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                                );

                                //Left align ifsc code
                                $worksheet->getStyle('C' . $rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                                );


                                $worksheet->getStyle('D' . $rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                                );
                                //EDITED BY ASHIN ANTONY ON 26-07-24 
                                //edited by ASHIN on 30-07-24 
                                $dd_amt = round($val['eot']['ot_amount']);
                                $netamt = $dd_amt;
                                //  if (isset($val['eot']['ot_amount'])) {
                                //     $netamt = $val['eot']['ot_amount'];
                                // } else {
                                //     $netamt = 0; // or some other default value
                                // }
                                // if($bank_name != ''){
                                //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $account_number);
                                $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $rowcount)->setValueExplicit($account_number, PHPExcel_Cell_DataType::TYPE_STRING);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $netamt);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $ifsc_code);
                                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $bnfcry_acc);
                                $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col + 3, $rowcount)->setValueExplicit($bnfcry_acc, PHPExcel_Cell_DataType::TYPE_STRING);          //edited by ASHIN on 04-07-24 
                                //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $acno);
                                //$objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col + 4, $rowcount)->setValueExplicit($acno, PHPExcel_Cell_DataType::TYPE_STRING);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $bank_branch);
                                //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $account_number);
                                // $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col + 7, $rowcount)->setValueExplicit($account_number, PHPExcel_Cell_DataType::TYPE_STRING);
                                $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col + 6, $rowcount)->setValueExplicit('HERBAL ISOLATES P LTD', PHPExcel_Cell_DataType::TYPE_STRING);


                                $col = 4;
                                $col = $col + 1;
                                $total = $total + $netamt;
                                $j++;

                                //                                 $objPHPExcel->getActiveSheet()
                                //                       ->getStyle('A3:J400')
                                //                       ->getAlignment()
                                //                       ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                //                       $objPHPExcel->getActiveSheet()
                                //                       ->getStyle('A2:J2')
                                //                       ->getAlignment()
                                //                       ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


                            }
                            $rowcount++;

                            //  }

                            //}
                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Total');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $total);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $rowcount++;
                        if ($table_count == 0) {
                            $worksheet->mergeCells('A3:L3');
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . (3), 'No data available under the selected criteria');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(11);
                            // $row=$rowcount-2;
                        }
                        $BStyle = array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                        );

                        $row = $rowcount - 1;
                        $objPHPExcel->getActiveSheet()->getStyle('A2:J' . $row)->applyFromArray($BStyle);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(100);
                        // if($table_count == 0){
                        //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 3, "No data available under the selected criteria");
                        //     $worksheet->mergeCells('A' . 3 . ':G' . 3);
                        //             $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        //                     array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        //             );
                        // }

                    }


                    $lastrow = $objPHPExcel->getActiveSheet()->getHighestRow();
                    for ($l = 0; $l <= (14 + isset($col) ? $col : 0); $l++) {
                        $objPHPExcel->getActiveSheet()
                            ->getStyle(num2alpha($l) . '4:' . num2alpha($l) . $lastrow)
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    }



                    if (isset($temp_bankrow) && !empty($temp_bankrow)) {
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('D1:D' . $lastrow)
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                        foreach ($temp_bankrow as $bankrow) {
                            $worksheet->getStyle($bankrow)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                        }
                    }





                    //Border style
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                }
                $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                $objPHPExcel->getActiveSheet()->setTitle('Over Time Bank Wise');

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
                $this->render('overtime');
                break;
        }
    }

    public function generateOtSynthietreport($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $fd = $arr_form_data['reportfrom'] . ' ' . '00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }
        $user_id = $this->Session->read('login_user_id');
        //date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)
        $date_time = date('d-m-Y h:i A');
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

            $condition =  "WHERE EmployeeDetails.status in('1','2')";
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

                //edited by ASHIN on 20-07-24
                if ($arr_form_data['select-criteria1'] == 'Departments') {
                    // DEBUG($leavepolicygroupid);
                    $arr_leavepolicy_details = $this->EmpCtcTransaction->query("SELECT `OTMASTER`.*,eot.*,"
                        . "`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`Info`.*,`EmployeeDetails`.status,last_approved_working_date, uc.user_id"
                        . " FROM `emp_ot_master` AS `OTMASTER`"
                        . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                        . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `termination` ON (`termination`.`emp_fkey`=`EmployeeDetails`.`emp_pkey` and `termination`.`status`=1)"
                        . "LEFT JOIN `user_credentials` AS `uc` ON (`uc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `department` ON (`department`.`dept_name` = `Info`.`department`)"
                        . "LEFT JOIN `emp_ot_process` AS `eot` ON (`eot`.`emp_ot_master_fkey` = `OTMASTER`.`emp_ot_master_pkey`) "
                        . "$condition and OTMASTER.month = '$from' and department.dept_code = '$leavepolicygroupid' and eot.process = 'processed' GROUP BY `emp_ot_master_pkey` "
                        . "ORDER BY CONCAT(`EmployeeDetails`.first_name, ' ', `EmployeeDetails`.last_name) ASC ");
                    $reporttype = 'Departments';
                    //DEBUG($arr_leavepolicy_details);
                } else {
                    $arr_leavepolicy_details = $this->EmpCtcTransaction->query("SELECT `OTMASTER`.*,eot.*,"
                        . "`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`Info`.*,`EmployeeDetails`.status,last_approved_working_date, uc.user_id"
                        . " FROM `emp_ot_master` AS `OTMASTER`"
                        . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                        . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `termination` ON (`termination`.`emp_fkey`=`EmployeeDetails`.`emp_pkey` and `termination`.`status`=1)"
                        . "LEFT JOIN `user_credentials` AS `uc` ON (`uc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `emp_ot_process` AS `eot` ON (`eot`.`emp_ot_master_fkey` = `OTMASTER`.`emp_ot_master_pkey`) "
                        . "$condition and OTMASTER.month = '$from' and EmployeeDetails.branch_code = '$leavepolicygroupid' and eot.process = 'processed' GROUP BY `emp_ot_master_pkey` "
                        . "ORDER BY CONCAT(`EmployeeDetails`.first_name, ' ', `EmployeeDetails`.last_name) ASC ");
                    $reporttype = 'Branch';
                }
                //End
                //  else if ($arr_form_data['select-criteria1'] == 'Departments') {
                //      $arr_leavepolicy_details = $this->EmpCtcTransaction->query("SELECT `OTMASTER`.*,"
                //      . "`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`Info`.*,`EmployeeDetails`.status,last_approved_working_date, uc.user_id"
                //      . " FROM `emp_ot_master` AS `OTMASTER`"
                //      . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                //      . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                //      . "LEFT JOIN `termination` ON (`termination`.`emp_fkey`=`EmployeeDetails`.`emp_pkey` and `termination`.`status`=1)"
                //      . "LEFT JOIN `user_credentials` AS `uc` ON (`uc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                //      . "$condition and OTMASTER.month = '$from' and EmployeeDetails.emp_pkey = '$leavepolicygroupid' and is_verified = 'Y' GROUP BY `emp_ot_master_pkey` ");
                //  $reporttype = 'Employee';

                $month = date("F", strtotime($from));
                $year = date("Y", strtotime($from));
                $this->set('month', $month);
                $this->set('year', $year);
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
                $str_company_code = $this->Session->read('company_code');   
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('overtime_dptmnt');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
               // $html2pdf->Output('OvertimeDepartmentWiseSummary.pdf', 'D');                        //edited by ASHIN on 05-07-24
                $html2pdf->Output($str_company_code  . "_Overtime Department Wise Summary "  . $from2 .  '.pdf', 'D');  
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');

                $file_name = isset($str_company_code) ? $str_company_code . "_Overtime Department Wise Summary - " . $from2 . ".xlsx" : "Overtime Department Wise Summary" . strtotime() . ".xlsx";            //edited by ASHIN on 05-07-24

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Over Time Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();  //edited by ASHIN on 05-07-24
                $objPHPExcel->getActiveSheet()->freezePane('D4');  //edited by ASHIN on 05-07-24
                $worksheet->mergeCells('A1:I1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->setCellValueByColumnAndRow(0, 1, "Overtime Department Wise Summary - " . $month . " " . $year);           //edited by ASHIN on 05-07-24
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);    //edited by ASHIN on 13-07-24

                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:I2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                //edited by ASHIN on 30-05-24
                // Border style
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                //edited by ASHIN on 11-07-24
                if (empty($arr_leavepolicydetails_for_template[0]['summary'][0])) {
                    //               $worksheet->setCellValueByColumnAndRow(0, 3, "There is no data available under the selected criteria");
                    //               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                    //               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                    //               $worksheet->mergeCells('A3:M3');
                    //               $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                    //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    // );
                    // $worksheet->mergeCells('A3:L3');
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . (3), 'No data available under the selected criteria');

                }

                //else{
                // for ($col = 'A'; $col !== 'Z'; $col++) {
                //     $objPHPExcel->getActiveSheet()
                //             ->getColumnDimension($col)
                //             ->setAutoSize(true);
                // }
                // if (!empty($arr_leavepolicydetails_for_template>0)) {

                //edited by ASHIN on 20-07-24             
                //Total value decalration by Ashin on 05-07-24    
                $total_val = array(
                    'total_dur' => 0,
                    'aprvd_dur' => 0,
                    'ovrtm_rate' => 0,
                    'ovrtm_amnt' => 0
                );
                //End
                // $empty = false; 

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
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');     //edited by ASHIN on 11-07-24
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
                //edited by ASHIN on 10-07-24                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . ($rowcount), 'Termination Date');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . ($rowcount), 'Total Duration(In Hrs)');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . ($rowcount), 'Approved');
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . ($rowcount), 'Approved Duration(In Hrs)');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . ($rowcount), 'Overtime Rate');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . ($rowcount), 'Overtime Amount');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . ($rowcount), 'Remarks');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
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
                        $branch = isset($value['summary'][0]['Info']['department']) ? $value['summary'][0]['department']['dept_name'] : '';
                    }
                    // debug($branch);
                    //die();
                    $arr_data = $value['summary'];

                    // $this -> set('$arr_data', $arr_data);

                    //edited by ASHIN on 10-07-24
                    if (count($arr_data) > 0) {
                        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);

                        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(28);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(28);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(28);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(18);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(24);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(24);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(24);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(24);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(40);



                        $columnindex = 0;

                        // $rowcount = $rowcount + 1;
                        // $k = 1;
                        foreach ($arr_data as $val) {

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $k);
                            $empstatus = (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : '';
                            $name = $val['Info']['EmpName'];    //edited by ASHIN on 09-08-24
                            $empid = isset($val['uc']['user_id']) ? $val['uc']['user_id'] : '';
                            $id = $val['Info']['employee_id'];
                            $branch = $val['Info']['branch'];
                            $designation = $val['Info']['designation'];
                            $department = $val['Info']['department'];
                            // $join = $val['Info']['joining_date'];
                            //  $join = date("d-m-y", strtotime($val['Info']['joining_date']));     //edited by ASHIN on 10-97-24
                            $join = isset($val['Info']['joining_date']) ? date("d-m-Y", strtotime($val['Info']['joining_date'])) : '';
                            $totel = round(($val['OTMASTER']['total_duration'] / 60), 2);
                            $total_val['total_dur'] += $totel;
                            $verified = isset($val['OTMASTER']['set_duration']) ? round(($val['OTMASTER']['set_duration'] / 60), 2) : round(($val['OTMASTER']['total_duration'] / 60), 2);
                            $total_val['aprvd_dur'] += $verified;
                            // $approved = isset($val['OTMASTER']['is_verified']) == "Y" ? "Yes" : "NO";
                            $remarks = $val['OTMASTER']['remarks'];           //edited by ASHIN on 30-07-24
                            $status = $val['EmployeeDetails']['status'];
                            //edited by ASHIN on 20-07-24                    
                            $rate = $val['eot']['ot_rate'];
                            $total_val['ovrtm_rate'] += $rate;
                            $amount = round($val['eot']['ot_amount']);        //edited by ASHIN on 30-07-24
                            $total_val['ovrtm_amnt'] += $amount;
                            //End
                            // $termination = $val['termination']['last_approved_working_date']; //last working day added by ***ARUL P DAS on 3/1/2020
                            $termination = isset($val['termination']['last_approved_working_date'])
                                ? date("d-m-y", strtotime($val['termination']['last_approved_working_date'])) : '';     //edited by ASHIN on 10-07-24

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
                            $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(5)->setAutoSize(true);    //edited by ASHIN on 30-07-24
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $department);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), $designation);
                            $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(7)->setAutoSize(true); //Edited by Akshay on 21-3-2024 
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), $termination);
                            //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((10), ($rowcount), $approved);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), $totel);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((10), ($rowcount), $verified);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((11), ($rowcount), $rate);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((12), ($rowcount), $amount);
                            // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((11), ($rowcount), $approved);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((13), ($rowcount), $remarks);
                            $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn(13)->setAutoSize(true);        //edited by ASHIN on 30-07-24
                            $rowcount++;

                            //edited by ASHIN on 05-07-24   

                            // $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowcount . ':C' . $rowcount);
                            // $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowcount, 'TOTAL');
                            // $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount)->getFont()->setBold(true);
                            // $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            // $column = 9;
                            // foreach ($total_val as $print_total) {
                            //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, (isset($print_total) ? $print_total : ''));
                            //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                            //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            //$column++;
                            //     }

                            if ($reporttype == 'Branch') {
                                $k++;
                            }
                            if ($reporttype == 'Departments') {
                                $k++;
                            }
                        }

                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $k);
                        //edited by ASHIN on 11-07-24                

                    }
                }

                $row = $rowcount - 1;
                //EDITED BY ASHIN ON 11-07-24
                if ($k == 1) {
                    $worksheet->mergeCells('A3:N3');
                    $objPHPExcel->getActiveSheet()->freezePane(null);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . (3), 'No data available under the selected criteria');
                    //         $row=$rowcount-2;
                }
                if ($k != 1) {
                    //edited by ASHIN on 19-07-24   
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowcount . ':I' . $rowcount);      //edited by ASHIN on 13-07-24
                    $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowcount, 'TOTAL');
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $column = 9;

                    foreach ($total_val as $print_total) {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, (isset($print_total) ? $print_total : ''));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $column++;
                    }
                }
                $BStyle = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                $row++;     //edited by ASHIN on 10-07-24

                //                for ($col = 'A'; $col !== 'Z'; $col++) {
                //     $objPHPExcel->getActiveSheet()
                //             ->getColumnDimension($col)
                //             ->setAutoSize(true);
                // }
                // $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
                if ($k != 1) {
                    $objPHPExcel->getActiveSheet()->getStyle('A3:N' . $row)->applyFromArray($BStyle);
                }

                //           for ($col = 'A'; $col !== 'Z'; $col++) {
                //     $objPHPExcel->getActiveSheet()
                //             ->getColumnDimension($col)
                //             ->setAutoSize(true);
                // }      
                $objPHPExcel->getActiveSheet()
                    ->getStyle('A4:M4000')     //EDITED BY ASHIN ON 20-07-24
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


                //Hide gridlines
                $objPHPExcel->getActiveSheet()->setShowGridlines(false);   //edited by ASHIN on 05-07-24

                $objPHPExcel->getActiveSheet()->setTitle('Over Time Department Wise');              //edited by ASHIN on 05-07-24
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

                $this->set('mode', '');
                $this->render('overtime_dptmnt');
                break;

                //   die();
        }
    }
    public function generateOtMonthwiseReport($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $fd = $arr_form_data['reportstartfrom'] . ' ' . '00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        if ((isset($arr_form_data['reportstartfrom']) && $arr_form_data['reportsto'] != '')) {
            $report_month = $arr_form_data['reportstartfrom'];
            $report_to = $arr_form_data['reportsto'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportstartfrom']));
            $to = date('Y-m-1', strtotime($arr_form_data['reportsto']));
            //debug($to);
        }
        $user_id = $this->Session->read('login_user_id');
        //date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)
        $date_time = date('d-m-Y h:i A');
        $date = $arr_form_data['reportstartfrom'];
        $this->set('date', $date);
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $from2 = date('Y-m', strtotime($arr_form_data['reportstartfrom']));
        // to get the months-year between the selected months
        $startDate = new DateTime($from);
        $endDate = new DateTime($to);

        $interval = new DateInterval('P1M'); // 1 month interval
        $period = new DatePeriod($startDate, $interval, $endDate->modify('+1 month')); // End date is inclusive

        $monthYearList = [];
        foreach ($period as $date) {
            $monthYearList[] = $date->format('M-y');
        }
        $this->set('monthYearList', $monthYearList);

        //  debug($monthYearList);

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

            $condition =  "WHERE EmployeeDetails.status in('1','2')";
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
                //  debug($arr_form_data['select-criteria1']); exit;

                if ($arr_form_data['select-criteria1'] == 'Departments') {
                    // DEBUG($leavepolicygroupid);
                    $arr_leavepolicy_details = $this->EmpCtcTransaction->query("SELECT `OTMASTER`.*,SUM(ROUND(eot.ot_amount, 2)) AS total_salary_amount, SUM(eot.ot_rate) AS total_salary_rate, "
                        . "`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`Info`.*,`EmployeeDetails`.status,last_approved_working_date, uc.user_id, COUNT(DISTINCT `EmployeeDetails`.`emp_pkey`) AS employee_count "
                        . " FROM `emp_ot_master` AS `OTMASTER`"
                        . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                        . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `termination` ON (`termination`.`emp_fkey`=`EmployeeDetails`.`emp_pkey` and `termination`.`status`=1)"
                        . "LEFT JOIN `user_credentials` AS `uc` ON (`uc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `department` ON (`department`.`dept_name` = `Info`.`department`)"
                        . "LEFT JOIN `emp_ot_process` AS `eot` ON (`eot`.`emp_ot_master_fkey` = `OTMASTER`.`emp_ot_master_pkey`) "
                        . "$condition and OTMASTER.month BETWEEN '$from' AND '$to' and department.dept_code = '$leavepolicygroupid' and eot.process = 'processed' GROUP BY `emp_ot_master_pkey` ");
                    $reporttype = 'Departments';
                    // debug($arr_leavepolicy_details);
                } else {
                    $arr_leavepolicy_details = $this->EmpCtcTransaction->query("SELECT `OTMASTER`.*,SUM(ectc.salary_amount) AS total_salary_amount,SUM(ectc.salary_rate) AS total_salary_rate,"
                        . "`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`Info`.*,`EmployeeDetails`.status,last_approved_working_date, uc.user_id"
                        . " FROM `emp_ot_master` AS `OTMASTER`"
                        . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                        . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `termination` ON (`termination`.`emp_fkey`=`EmployeeDetails`.`emp_pkey` and `termination`.`status`=1)"
                        . "LEFT JOIN `user_credentials` AS `uc` ON (`uc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "LEFT JOIN `emp_salary_slip` AS `ectc` ON (`ectc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`) AND ectc.end_date_effective IS NULL AND ectc.salary_head_item_fkey = 107 AND ectc.month_year = '$from'"
                        . "$condition and OTMASTER.month = '$from' and EmployeeDetails.branch_code = '$leavepolicygroupid' and is_verified = 'Y' GROUP BY `emp_ot_master_pkey` ");
                    $reporttype = 'Branch';
                }

                //  else if ($arr_form_data['select-criteria1'] == 'Departments') {
                //      $arr_leavepolicy_details = $this->EmpCtcTransaction->query("SELECT `OTMASTER`.*,"
                //      . "`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`Info`.*,`EmployeeDetails`.status,last_approved_working_date, uc.user_id"
                //      . " FROM `emp_ot_master` AS `OTMASTER`"
                //      . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                //      . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                //      . "LEFT JOIN `termination` ON (`termination`.`emp_fkey`=`EmployeeDetails`.`emp_pkey` and `termination`.`status`=1)"
                //      . "LEFT JOIN `user_credentials` AS `uc` ON (`uc`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                //      . "$condition and OTMASTER.month = '$from' and EmployeeDetails.emp_pkey = '$leavepolicygroupid' and is_verified = 'Y' GROUP BY `emp_ot_master_pkey` ");
                //  $reporttype = 'Employee';
                $month = date("F", strtotime($from));
                //Edited by Akshay on 2-8-2024
                $toMonth = date("F", strtotime($to));
                $this->set('toMonth', $toMonth);
                $toYear = date("Y", strtotime($to));
                $this->set('toYear', $toYear);
                //End
                $year = date("Y", strtotime($from));
                $this->set('month', $month);
                $this->set('year', $year);
                $arr_leavepolicydetails_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_leavepolicy_details,
                    // 'employees'=>$arr_leavepolicy_employees
                );
            }
        }
        // debug($arr_leavepolicydetails_for_template);exit;
        //Edited by Akshay on 31-7-2024
        function columnIndexToName($index)
        {
            return PHPExcel_Cell::stringFromColumnIndex($index);
        }
        //End
        //Edited by Akshay on 1-8-2024
        function removeUnnecessaryDecimals($number)
        {
            // Convert the number to a string and remove unnecessary trailing zeros
            return strpos($number, '.') !== false ? rtrim(rtrim($number, '0'), '.') : $number;
        }
        //End

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
                $view_output = $view->render('ot_monthwise');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $str_company_code = $this->Session->read('company_code');
                $html2pdf->Output($str_company_code . '_Overtime Summary Month Wise - ' . $month . " " . $year . ' to ' . $toMonth . ' ' . $toYear . '.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . '_Overtime Summary Month Wise - ' . $month . " " . $year . ' to ' . $toMonth . ' ' . $toYear . ".xlsx" : "Overtime Month Wise" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Over Time Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->mergeCells('A1:P1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->setCellValueByColumnAndRow(0, 1, 'Overtime Summary Month Wise - ' . $month . " " . $year . ' to ' . $toMonth . ' ' . $toYear);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(14);

                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:P2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                // Transpose headings: set headers in rows
                $row = 3;

                // Prepare data
                $all_summaries_empty = true;
                foreach ($arr_leavepolicydetails_for_template as $val) {
                    if (!empty($val['summary'])) {
                        $all_summaries_empty = false;
                        break;
                    }
                }

                // Handle case where there's no data
                if ($all_summaries_empty) {
                    $worksheet->setCellValue('A3', 'No data available under the selected criteria');
                    $worksheet->mergeCells('A3:E3');
                } else {
                    $worksheet->setCellValueByColumnAndRow(0, $row, 'OT Analysis '.$year);
                    $worksheet->setCellValueByColumnAndRow(0, ($row + 1), 'Cost Center');
                    $worksheet->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
                    //Edited by Akshay on 14-8-2024
                    $worksheet->getStyle('A'.$row)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    //End
                    //Edited by Akshay on 31-7-2024
                    $printed_departments = [];
                    $department_overtime = [];
                    $department_duration = [];
                    $department_employees = [];
                    $total_overtime = array_fill_keys(array_map(function ($date) {
                        return date('M-y', strtotime($date));
                    }, $monthYearList), 0);
                    $total_duration = array_fill_keys(array_map(function ($date) {
                        return date('M-y', strtotime($date));
                    }, $monthYearList), 0);
                    $total_employees = array_fill_keys(array_map(function ($date) {
                        return date('M-y', strtotime($date));
                    }, $monthYearList), 0);

                    foreach ($arr_leavepolicydetails_for_template as $val) {
                        $arr_leavepolicy_details = $val['summary'];
                        foreach ($arr_leavepolicy_details as $value) {
                            $department = $value['Info']['department'];
                            $month = date('M-y', strtotime($value['OTMASTER']['month']));
                            $overtime_amount = $value['0']['total_salary_amount'];
                            $duration = round(($value['OTMASTER']['set_duration'] / 60), 2); //Edited by Akshay on 19-8-2024
                            $employee_count = $value['0']['employee_count'];

                            if (!isset($department_overtime[$department])) {
                                $department_overtime[$department] = [];
                            }
                            if (!isset($department_duration[$department])) {
                                $department_duration[$department] = [];
                            }
                            if (!isset($department_employees[$department])) {
                                $department_employees[$department] = [];
                            }

                            // $department_overtime[$department][$month] = $overtime_amount;
                            //Edited by Akshay on 14-8-2024
                            if (isset($department_overtime[$department][$month])) {
                                $department_overtime[$department][$month] += removeUnnecessaryDecimals($overtime_amount);
                            } else {
                                $department_overtime[$department][$month] = removeUnnecessaryDecimals($overtime_amount);
                            }
                            //End  
                            $department_duration[$department][$month] = $duration;
                            $department_employees[$department][$month] = $employee_count;

                            if (isset($total_overtime[$month])) {
                                $total_overtime[$month] += round($overtime_amount);
                            }
                            if (isset($total_duration[$month])) {
                                $total_duration[$month] += $duration;
                            }
                            if (isset($total_employees[$month])) {
                                $total_employees[$month] += $employee_count;
                            }
                        }
                    }

                    //End
                    // Fill the data
                    $column = 1;
                    foreach ($monthYearList as $date) {
                        $worksheet->setCellValueByColumnAndRow($column, 4, $date);
                        $worksheet->setCellValueByColumnAndRow($column, 5, 'RS');
                        $cellCoordinate = $worksheet->getCellByColumnAndRow($column, 5)->getCoordinate();
                        $worksheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                        $cellCoordinate = $worksheet->getCellByColumnAndRow($column, 4)->getCoordinate();
                        $worksheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


                        $column++;
                    }
                    if ($column > 2) {
                        $columnName = columnIndexToName($column - 1);
                        //Edited by Akshay on 2-8-2024
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A3:' . $columnName . '3')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('C8C8C8');
                        //End
                    }
                    $row = 6;
                    // foreach ($arr_leavepolicydetails_for_template as $val) {
                    //     foreach ($val['summary'] as $value) {
                    //         if ($dataRow == 4) {
                    //             $worksheet->setCellValueByColumnAndRow($column, $dataRow, '');
                    //             $dataRow++;
                    //         }

                    //         $overtime_amount = isset($value['OTMASTER']['total_salary_amount']) ? $value['OTMASTER']['total_salary_amount'] : 0;
                    //         $worksheet->setCellValueByColumnAndRow($column, $dataRow, $overtime_amount);
                    //         $dataRow++;

                    //         $total_duration = isset($total_duration[$date]) ? $total_duration[$date] : 0;
                    //         $worksheet->setCellValueByColumnAndRow($column, $dataRow, $total_duration);
                    //         $dataRow++;

                    //         $total_employees = isset($total_employees[$date]) ? $total_employees[$date] : 0;
                    //         $worksheet->setCellValueByColumnAndRow($column, $dataRow, $total_employees);
                    //         $dataRow++;

                    //         $total_operator_loss = isset($total_operator_loss[$date]) ? $total_operator_loss[$date] : 0;
                    //         $worksheet->setCellValueByColumnAndRow($column, $dataRow, $total_operator_loss);
                    //         $dataRow++;
                    //     }
                    // }

                    foreach ($department_overtime as $department => $month_data) {
                        if (!in_array($department, $printed_departments)) {
                            $printed_departments[] = $department;
                            $worksheet->setCellValue('A' . $row, $department);
                            $column = 'B';
                            foreach ($monthYearList as $date) {
                                $formatted_date = date('M-y', strtotime($date));
                                $overtime_amount = isset($month_data[$formatted_date]) ? $month_data[$formatted_date] : 0;
                                $worksheet->setCellValue($column . $row, round($overtime_amount));
                                $column++;
                            }
                            $row++;
                        }
                    }
                    $worksheet->setCellValue('A' . $row, 'Total Amount');
                    $worksheet->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
                    $column = 'B';
                    foreach ($monthYearList as $index => $date) {
                        $formatted_date = date('M-y', strtotime($date));
                        $total_amount = isset($total_overtime[$formatted_date]) ? $total_overtime[$formatted_date] : 0;
                        $worksheet->setCellValue($column . $row, round($total_amount));
                        $worksheet->getStyleByColumnAndRow(($index + 1), $row)->getFont()->setBold(true);
                        $currentColumn = $column;
                        $column++;
                    }
                    //Edited by Akshay on 2-8-2024
                    if ($column != 'B') {
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $row . ':' . $currentColumn . $row)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('C8C8C8');
                    }
                    //End
                    $row++;

                    $worksheet->setCellValue('A' . $row, 'Total working hrs');
                    $worksheet->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);

                    $column = 'B';
                    foreach ($monthYearList as $date) {
                        $formatted_date = date('M-y', strtotime($date));
                        $worksheet->setCellValue($column . $row, ($total_duration[$formatted_date])); //Edited by Akshay on 14-8-2024
                        $column++;
                    }
                    $row++;

                    $worksheet->setCellValue('A' . $row, 'Total no.of duty');
                    $worksheet->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
                    $column = 'B';
                    foreach ($monthYearList as $date) {
                        $formatted_date = date('M-y', strtotime($date));
                        // $worksheet->setCellValue($column . $row, $total_employees[$formatted_date]);
                        //Edited by Akshay on 12-8-2024
                        $worksheet->setCellValue($column . $row, isset($total_duration[$formatted_date]) ? round((($total_duration[$formatted_date]) / 8)) : 0);
                        //End
                        $column++;
                    }
                    $row++;

                    $worksheet->setCellValue('A' . $row, 'Operator loss/Month');
                    $worksheet->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
                    $column = 'B';
                    foreach ($monthYearList as $date) {
                        $formatted_date = date('M-y', strtotime($date));
                        //Edited by Akshay on 14-8-2024
                        $operator_loss = isset($total_duration[$formatted_date]) ? round((round((($total_duration[$formatted_date]) / 8)) / 26), 1) : 0;
                        $worksheet->setCellValue($column . $row, ($operator_loss));
                        //End
                        $column++;
                    }
                }

                // Set border style
                $BStyle = array(
                    'borders' => array(
                        'allborders' =>  array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                        )
                    )
                );


                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $objPHPExcel->getActiveSheet()->setShowGridlines(false);

                $columnCount = count($monthYearList);
                // Add gridline to column A (headings)
                $columnLetter = 'A';
                $lastRow = $objPHPExcel->getActiveSheet()->getHighestRow();
                $range = $columnLetter . '3:' . $columnLetter . $lastRow;
                $style = $objPHPExcel->getActiveSheet()->getStyle($range);
                if (!$all_summaries_empty)
                    $style->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $columnDimension = $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter);
                $columnDimension->setAutoSize(true); // Optional: set auto-size for the column

                // Loop through the remaining columns
                for ($column = 0; $column < $columnCount; $column++) {
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column + 1);
                    $lastRow = $objPHPExcel->getActiveSheet()->getHighestRow();
                    $range = $columnLetter . '3:' . $columnLetter . $lastRow;

                    // Check if the column contains data
                    $hasData = false;
                    foreach ($objPHPExcel->getActiveSheet()->rangeToArray($range) as $row) {
                        if (!empty($row[0])) {
                            $hasData = true;
                            break;
                        }
                    }

                    if ($hasData) {
                        $columnDimension = $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter);
                        $style = $objPHPExcel->getActiveSheet()->getStyle($range);
                        $style->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                        $columnDimension->setAutoSize(true); // Optional: set auto-size for the column
                    }
                }
                $objPHPExcel->getActiveSheet()->setTitle('Overtime Summary Month Wise');

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

                $this->set('mode', '');
                $this->render('ot_monthwise');
                break;

                //   die();
        }
    }
}
