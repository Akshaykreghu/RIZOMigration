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

class EmployeeConfigController extends AppController
{

    public $name = 'EmployeeConfig';
    public $datatable;
    public $uses = array('CentralControl', 'SalaryStructures', 'DayTimeProcedures', 'UserCredentials', 'Grades', 'Section', 'EmployeeDetails', 'Division', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'EmployeeConfig', 'NoticePeriod', 'EmployeeSalaryStructure', 'EditPunches');
    public $components = array('MasterdataManagement');

    public function index()
    {
        //$this -> layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $user_group = $this->Session->read('user_group');
        $this->set('user_group', $user_group);

        // Edited by Akshay on 13-5-2025
        $company_code = strtoupper($this->Session->read('company_code'));
        $this->set('company_code', $company_code);
        // End

        $active_emp_count = $this->EmployeeDetails->find('count', array('conditions' => array('status' => 1)));
        $this->set('active_emp_count', $active_emp_count);

        //Fetch Units for the company
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,parent';
        $joins = array(array('table' => 'emp_proff', 'alias' => 'EmployeeProfessionalDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')));
        $conditions = array('status' => 1);

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
        //debug($arr_emp);
        $data = array();
        foreach ($arr_emp as $key => $value) {
            $data[$key]["id"] = $value['EmployeeDetails']['emp_pkey'];
            $data[$key]["parent"] = $value['EmployeeDetails']['parent'];

            $data[$key]["name"] = $value['0']['name'];
        }

        //debug($data);
        //$tree = $this->createTree($data);
        //$tree = $this ->createTree($new, array($new[0]));
        //debug($arr_emp);
        //debug($tree);
        //$this->set('employeetree', $tree);
        //$this->set('employees', $arr_emp);
        //edited by sinsiya
        if ($user_group == 2) {
            $payroUser = $this->EmployeeProfessionalDetails->query("select payro_priv from emp_proff where emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
        }
        //else{
        //  $user_group 
        // }

        // back button
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        $this->CentralUserCredentials->setDataSource('controldb');

        $company_code = $this->Session->read('company_code');

        $data = $this->CentralUserCredentials->find('first', array(
            'conditions' => array(
                'CentralUserCredentials.company_code' => $company_code
            ),
            'fields' => array('CentralUserCredentials.plan_id'),
            'recursive' => -1
        ));

        $planId = !empty($data)
            ? (int)$data['CentralUserCredentials']['plan_id']
            : null;
        $user_group = $this->Session->read('user_group');

        $this->set('planId', $planId);
        $this->set('user_group', $user_group);
    }

    function getEmployeeTree()
    {
        $this->autoRender = false;
        $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,date_of_birth ,last_name,first_name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,parent';
        $joins = array(array('table' => 'emp_proff', 'alias' => 'EmployeeProfessionalDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')));
        $conditions = array('status' => 1);

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
        $log = $this->EmployeeDetails->getDataSource()->getLog(false, false);
        //debug($log);

        $data = array();
        foreach ($arr_emp as $key => $value) {
            $data[$key]["id"] = $value['EmployeeDetails']['emp_pkey'];
            $data[$key]["parent"] = $value['EmployeeDetails']['parent'];
            $data[$key]["first_name"] = $value['EmployeeDetails']['first_name'];
            $data[$key]["last_name"] = $value['EmployeeDetails']['last_name'];
            $data[$key]["designation"] = "";
            $data[$key]["dob"] = $value['EmployeeDetails']['date_of_birth'];
            $data[$key]["iconCls"] = "fa fa-user";

            $data[$key]["text"] = $value['0']['name'];
        }

        $tree = $this->createTree($data);

        echo json_encode($tree);
    }

    function createBranch(&$parents, $children)
    {
        $tree = array();
        foreach ($children as $child) {
            if (isset($parents[$child['id']])) {
                $child['children'] = $this->createBranch($parents, $parents[$child['id']]);
            }
            $tree[] = $child;
        }
        return $tree;
    }

    /* Initialization */

    function createTree($flat, $root = 0)
    {
        $parents = array();
        foreach ($flat as $a) {
            $parents[$a['parent']][] = $a;
        }
        return $this->createBranch($parents, $parents[$root]);
    }

    //Edited by Akshay on 26-9-2024
    public function addEmpToShift()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $shift = (isset($_REQUEST['shift']) ? $_REQUEST['shift'] : -1);

        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $resp_mispunches = array();
        $company_code = $this->Session->read('company_code');


        // Loop through multiple employee IDs
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $emp_pkey) {
                $data = array(
                    'id' => 0,
                    'type' => 'SHIFT',
                    'emp_fkey' => $emp_pkey,
                    'policy_id' => $shift,
                    'created_by' => $this->Session->read('login_user_id')
                );

                $arr_emp_proff = $this->EmployeeConfig->query("SELECT multishift FROM emp_proff WHERE emp_fkey = '$emp_pkey';");
                $multishift = isset($arr_emp_proff[0]['emp_proff']['multishift']) ? $arr_emp_proff[0]['emp_proff']['multishift'] : '';
                if ($multishift != $shift) {
                    if ($this->EmployeeConfig->save($data)) {
                        // Success response for each employee
                        $response = [
                            'status' => 'success',
                            'emp_fkey' => $emp_pkey,
                            'message' => 'Empolyee Added To Selected Shift Policy'
                        ];
                    } else {
                        // Error response if save failed
                        $response = [
                            'status' => 'error',
                            'emp_fkey' => $emp_pkey,
                            'message' => 'Failed to assign shift'
                        ];
                    }
                } else {
                    $response = [
                        'status' => 'success',
                        'emp_fkey' => $value,
                        'message' => "Employee has been successfully assigned to the selected shift policy, but the shift policy matches the existing additional shift"
                    ];
                }

                // Retrieve branch code for the current employee
                $branch_query = $this->EmployeeDetails->query("SELECT branch_code FROM emp_details WHERE emp_pkey='$emp_pkey'");
                $branch_code = !empty($branch_query) ? $branch_query[0]['emp_details']['branch_code'] : 'NULL';
                $month = date('Y-m-01');

                $restrictedCompanies = [
                    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK',
                    'GTRA','VGNN','SHYD','SRTS'
                ];

                if (in_array($company_code, $restrictedCompanies)) {
                    // Delete records
                    $this->EditPunches->query("DELETE FROM emp_detail_timeattandance 
                WHERE emp_pkey='$emp_pkey' 
                AND yearmonth='$month' 
                AND emp_pkey NOT IN (
                    SELECT emp_fkey FROM attendance_register 
                    WHERE isdelete='N' AND month_year = DATE_FORMAT('$month','%Y-%m')
                )");

                    // Check if the shift spans multiple days
                    $shiftdetailed = $this->EditPunches->query("SELECT is_multiple_days FROM working_day_time_procedures 
                WHERE day_time_seq IN (
                    SELECT day_time_seq FROM emp_proff 
                    WHERE emp_fkey = '$emp_pkey'
                )");

                    if (!empty($shiftdetailed) && $shiftdetailed[0]['working_day_time_procedures']['is_multiple_days'] == 'Y') {
                        // Check time duration for multiple shifts
                        $result = $this->EditPunches->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')");
                        if (!$result) {
                            return false;
                        }
                    } else {
                        // Check time duration for regular shift
                        $result = $this->EditPunches->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')");
                        if (!$result) {
                            return false;
                        }
                    }
                } else {
                    $att_start_date = $this->EmployeeDetails->query("SELECT att_start_end_fn('$month', 1) AS start_date");
                    $att_end_date = $this->EmployeeDetails->query("SELECT att_start_end_fn('$month', 2) AS end_date");
                    $attendance_start = $att_start_date[0][0]['start_date'];
                    $attendance_end = $att_end_date[0][0]['end_date'];


                    $emp = $this->EmployeeDetails->query("
    SELECT emp_id 
    FROM emp_details 
    WHERE emp_pkey ='$emp_pkey'
");

                    $emp_id = !empty($emp) ? $emp[0]['emp_details']['emp_id'] : '';

                    $dates = $this->EmployeeDetails->query("
                                                    SELECT DISTINCT SHIFTDATE
                                                    FROM device_attandance
                                                    WHERE emp_id = '$emp_id'
                                                    AND SHIFTDATE BETWEEN '$attendance_start' AND '$attendance_end' AND status='Y'
                                                    ORDER BY SHIFTDATE
                                                ");
                    foreach ($dates as $row) {
                        $shift_date = $row['device_attandance']['SHIFTDATE'];

                        $this->EmployeeDetails->query("
                                                    SELECT time_duration_check('$shift_date', '$emp_pkey', '$branch_code')
                                                ");
                    }
                }
            }
        } else {
            // Error response if no IDs are provided
            $response = [
                'status' => 'error',
                'message' => 'No employee IDs provided.'
            ];
        }
        echo json_encode($response);
    }


    //End

    public function removeEmpFromShift()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];

        $shift = (isset($_REQUEST['shift']) ? $_REQUEST['shift'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $condition['type'] = 'SHIFT';
                $condition['emp_fkey'] = $value;
                $condition['policy_id'] = $shift;
                $curr_user_id = $this->Session->read('login_user_id');
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);
            }
        }
    }

    public function listemployeesinshift()
    {

        $shift = (isset($_REQUEST['shift']) ? $_REQUEST['shift'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $emp_in_shift = $this->EmployeeConfig->find(
            "list",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "type" => "SHIFT",
                    "policy_id" => $shift,
                    "status" => 1
                )
            )
        );

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';

        //$joins[] = array('table' => 'emp_proff', 'alias' => 'EmployeeProfessionalDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey'),);
        //$joins[] = array('table' => 'branches', 'alias' => 'Branches', 'type' => 'LEFT', 'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code'));
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );

        $conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_in_shift);

        // Edited by Akshay on 7-2-2025
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            //debug($value);
            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    public function listemployeesforshift()
    {

        $shift = (isset($_REQUEST['shift']) ? $_REQUEST['shift'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');

        $db = $this->EmployeeConfig->getDataSource();
        //$conditions[] = $db->expression('status=1');
        //$this->User->find('all', compact('conditions'));

        /*$joins[] = array(
            'table' => 'branches', 
            'alias' => 'Branches', 
            'type' => 'LEFT', 
            'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code')
        );        
        //array('status' => 1, 'EmployeeDetails.emp_pkey NOT IN' => $emp_in_shift);        
        //$emp_in_shift = $this->EmployeeConfig->find("list", array("fields" => "emp_fkey", "conditions" => array("type" => "SHIFT", "day_time_seq" => $shift)));
        if (!empty($emp_in_shift)) {
            $conditions["NOT"] = array('EmployeeDetails.emp_pkey' => $emp_in_shift);
        }
        $conditions["EmployeeDetails.status"] = 1;*/

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );

        $conditions = array(
            'EmployeeDetails.status' => 1,
            'OR' => array(
                'EmployeeProfessionalDetails.day_time_seq = ""',
                'EmployeeProfessionalDetails.day_time_seq IS NULL'
            ),
            // 'EmployeeProfessionalDetails.multishift !=' => $shift //Edited by Akshay on 12-9-2024
        );

        // Edited by Akshay on 7-2-2025
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $arr_emp = $this->EmployeeDetails->find(
            "all",
            array(
                'fields' => $fields,
                'joins' => $joins,
                'conditions' => $conditions
            )
        );
        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    /**
     * Holiday Config Start
     */
    public function listemployeesinholiday()
    {

        $holiday = (isset($_REQUEST['holiday']) ? $_REQUEST['holiday'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //echo $shift;
        $emp_in_holiday = $this->EmployeeConfig->find(
            "list",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "type" => "HOLIDAY",
                    "policy_id" => $holiday,
                    "status" => 1
                )
            )
        );

        /*$fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,Branches.branch_code,Branches.branch_name';
        $joins[] = array('table' => 'emp_proff', 'alias' => 'EmployeeProfessionalDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey'));
        $joins[] = array('table' => 'branches', 'alias' => 'Branches', 'type' => 'LEFT', 'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code'));*/

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );
        $conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_in_holiday);

        // Edited by Akshay on 7-2-2025
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    public function listemployeesforholiday()
    {

        /*$holiday = (isset($_REQUEST['holiday']) ? $_REQUEST['holiday'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $emp_in_holiday = $this->EmployeeConfig->find("list", array("fields" => "emp_fkey", "conditions" => array("type" => "HOLIDAY", "HOLIDAY_GROUP_ID" => $holiday)));

        $db = $this->EmployeeConfig->getDataSource();
        $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,Branches.branch_code,Branches.branch_name';
        $joins[] = array('table' => 'emp_proff', 'alias' => 'EmployeeProfessionalDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey'));
        $joins[] = array('table' => 'branches', 'alias' => 'Branches', 'type' => 'LEFT', 'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code'));
        $conditions["EmployeeDetails.status"] = 1;
        //array('status' => 1, 'EmployeeDetails.emp_pkey NOT IN' => $emp_in_shift);
        if (!empty($emp_in_holiday)) {
            $conditions["NOT"] = array('EmployeeDetails.emp_pkey' => $emp_in_holiday);
        }*/

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );

        $conditions = array(
            'EmployeeDetails.status' => 1,
            'OR' => array(
                'EmployeeProfessionalDetails.HOLIDAY_GROUP_ID = ""',
                'EmployeeProfessionalDetails.HOLIDAY_GROUP_ID IS NULL'
            )
        );

        // Edited by Akshay on 7-2-2025
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    public function addEmpToHoliday()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];

        $resp_emp["rows"] = array();
        //foreach ($arr_emp as $key => $value) {

        $holiday = (isset($_REQUEST['holiday']) ? $_REQUEST['holiday'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $data['id'] = 0;
                $data['type'] = 'HOLIDAY';
                $data['emp_fkey'] = $value;
                $data['policy_id'] = $holiday;
                $data['created_by'] = $this->Session->read('login_user_id');
                $this->EmployeeConfig->save($data);
            }
        }
        //}
    }

    public function removeEmpFromHoliday()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];

        $holiday = (isset($_REQUEST['holiday']) ? $_REQUEST['holiday'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                /*
                  $data['id'] = 0;
                 */
                $condition['type'] = 'HOLIDAY';
                $condition['emp_fkey'] = $value;
                $condition['policy_id'] = $holiday;
                $curr_user_id = $this->Session->read('login_user_id');
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);
            }
        }
    }

    /**
     * Holiday COnfig Ends
     */
    public function setup($emp_pkey = 0)
    {
        $this->layout = null;

        /* $sessionObj = $this->Session->read("Auth.User");
          if(isset($sessionObj['user_group']) && $sessionObj['user_group'] == 2){
          $emp_pkey   = $sessionObj['emp_fkey'];
          } */

        if ($emp_pkey) {
            //edit mode
            $this->set('emp_pkey', $emp_pkey);
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_details = $this->EmployeeDetails->find('first', array('conditions' => array('emp_pkey' => $emp_pkey)));
            if (is_array($arr_emp_details['EmployeeDetails'])) {
                $this->set('arr_emp_details', $arr_emp_details['EmployeeDetails']);
            }

            if (isset($sessionObj['user_group']) && $sessionObj['user_group'] == 2) {
                //Employee
                $head = 'My Profile';
            } else {
                //Admin
                $head = $arr_emp_details['EmployeeDetails']['first_name'] . " " . $arr_emp_details['EmployeeDetails']['middile_name'] . " " . $arr_emp_details['EmployeeDetails']['last_name'] . "'s Profile";
            }
            $this->set('head', $head);
        } else {
            //add mode
            $this->set('emp_pkey', 0);
        }

        //Fetch departments for the company
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);
        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);
    }

    public function loadEmpDetails($emp_pkey = 0)
    {
        $this->layout = null;
        $this->autoRender = FALSE;

        /* $sessionObj = $this->Session->read("Auth.User");
          if(isset($sessionObj['user_group']) && $sessionObj['user_group'] == 2){
          $emp_pkey   = $sessionObj['emp_fkey'];
          } */

        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $arr_emp_personal_profile = array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_personal_profile = $this->EmployeeDetails->find('first', array('conditions' => array('emp_pkey' => $emp_pkey)));
            $arr_emp_personal_profile = $arr_emp_personal_profile['EmployeeDetails'];
            //return json_encode($arr_emp_personal_profile);
            return json_encode(array('success' => true, 'empPkey' => $emp_pkey, 'data' => $arr_emp_personal_profile));
        } else {
            return json_encode(array('success' => false, 'data' => array()));
        }
    }

    public function loadEmpProfDetails($emp_pkey = '')
    {
        $this->layout = null;
        $this->autoRender = FALSE;

        $sessionObj = $this->Session->read("Auth.User");
        if (isset($sessionObj['user_group']) && $sessionObj['user_group'] == 2) {
            $emp_pkey = $sessionObj['emp_fkey'];
        }

        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $arr_emp_professional_profile = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile = $this->EmployeeProfessionalDetails->find('first', array('conditions' => array('emp_fkey' => $emp_pkey)));
            if (empty($arr_emp_professional_profile)) {
                $empId = '';
                $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                $arr_emp_professional_profile_last = $this->EmployeeProfessionalDetails->find('first', array('order' => array('emp_proff_pkey' => 'DESC')));
                if (isset($arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'])) {
                    $str_emp_id = $arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'];
                    $sessionObj = $this->Session->read("Auth.User");
                    $company_key = $sessionObj['company_key'];
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('company_code'), 'conditions' => array('control_pkey' => $company_key)));
                    $str_company_code = isset($arr_central_control['CentralControl']['company_code']) ? $arr_central_control['CentralControl']['company_code'] : '';
                    $arr_emp_id = explode($str_company_code, $str_emp_id);
                    if (isset($arr_emp_id[1]) && $arr_emp_id[1] != '') {
                        $emp_id = $str_company_code . ($arr_emp_id[1] + 1);
                    } else {
                        $emp_id = '';
                    }
                } else {
                    //He is the first employee
                    $sessionObj = $this->Session->read("Auth.User");
                    $company_key = $sessionObj['company_key'];
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('company_code'), 'conditions' => array('control_pkey' => $company_key)));
                    $str_company_code = isset($arr_central_control['CentralControl']['company_code']) ? $arr_central_control['CentralControl']['company_code'] : '';
                    $emp_id = $str_company_code . "1000";
                }
                $arr_emp_professional_profile['emp_id'] = $emp_id;
                $arr_emp_professional_profile['emp_fkey'] = $emp_pkey;
            } else {
                $arr_emp_professional_profile = $arr_emp_professional_profile['EmployeeProfessionalDetails'];
            }

            //return json_encode($arr_emp_professional_profile);
            return json_encode(array('success' => true, 'empPkey' => $emp_pkey, 'data' => $arr_emp_professional_profile));
        } else {
            return json_encode(array('success' => false, 'data' => array()));
        }
    }

    public function empprofdetails($emp_pkey = 0)
    {
        $this->layout = null;
        $emp_pkey = 1;

        //Fetch departments for the company
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        //Fetch employee professional details if in edit mode
        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $arr_emp_professional_profile = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile = $this->EmployeeProfessionalDetails->find('first', array('conditions' => array('emp_fkey' => $emp_pkey)));
            $arr_emp_professional_profile = $arr_emp_professional_profile['EmployeeProfessionalDetails'];
            $this->set('arr_emp_professional_profile', $arr_emp_professional_profile);
            $this->set('emp_id', $arr_emp_professional_profile['emp_id']);
            $emp_proff_pkey = $arr_emp_professional_profile['emp_proff_pkey'];
        } else {
            $emp_proff_pkey = 0;
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile_last = $this->EmployeeProfessionalDetails->find('last');
            if (isset($arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'])) {
                $str_emp_id = $arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'];
                $company_key = $this->session->read('company_key');
                $arr_central_control = $this->CentralControl->find('first', array('fields' => array('company_code'), 'conditions' => array('company_pkey' => $company_key)));
                $str_company_code = isset($arr_central_control['CentralControl']['company_code']) ? $arr_central_control['CentralControl']['company_code'] : '';
                $arr_emp_id = explode($str_company_code, $str_emp_id);
                if (isset($arr_emp_id[1]) && $arr_emp_id[1] != '') {
                    $emp_id = $str_company_code . ($arr_emp_id[1] + 1);
                } else {
                    $emp_id = '';
                }
            } else {
                $emp_id = '';
            }
            $this->set('emp_id', $arr_emp_professional_profile_last['emp_id']);
        }
        $this->set('emp_proff_pkey', $emp_proff_pkey);
    }

    public function emptaxationdetails($emp_pkey = 0) {}

    public function saveemployeesetup()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $arr_form_data = $this->request->data;
        $model = $arr_form_data['model'];
        switch ($model) {
            case 'EmployeeDetails':
                $pkey = $arr_form_data['emp_pkey'];
                $message = 'Personal Details Saved Successfully';
                break;
            case 'EmployeeProfessionalDetails':
                $pkey = $arr_form_data['emp_fkey'];
                $message = 'Professional Details Saved Successfully';
                break;
            default:
                $pkey = 0;
                $message = '';
                break;
        }
        $this->{$model}->useDbConfig = $this->Session->read('ds');
        $result = $this->{$model}->save($arr_form_data);
        if (!empty($result)) {
            if ($pkey == 0) {
                $pkey = $this->{$model}->getLastInsertID();
                if ($model == 'EmployeeDetails') {
                    $sessionObj = $this->Session->read("Auth.User");
                    $company_key = $sessionObj['company_key'];
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('punch_type'), 'conditions' => array('control_pkey' => $company_key)));
                    $punch_type = isset($arr_central_control['CentralControl']['punch_type']) ? $arr_central_control['CentralControl']['punch_type'] : '';
                    if ($punch_type == 'device') {
                        //Device available, so generate emp id concatenate with device id and emp id from device
                        $arr_user_cred = array();
                        if ($pkey > 0) {
                            //Insert user credentials
                            //Get company_code
                            $auth_user = $this->Session->read("Auth.User");
                            $str_company_code = isset($auth_user['company_code']) ? $auth_user['company_code'] : '';

                            $emp_username = '';
                            //No device details here on manually entering emp data

                            $arr_user_cred['user_pkey'] = 0;
                            $arr_user_cred['emp_fkey'] = $pkey;
                            $arr_user_cred['company_code'] = $str_company_code;
                            $arr_user_cred['user_id'] = $emp_username;
                            $arr_user_cred['password'] = /* Security::hash(rand(), null, true);// */ rand();
                            $arr_user_cred['access_allowed'] = 'n';
                            $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                            $arr_user_cred['last_name'] = $arr_form_data['last_name'];
                            $arr_user_cred['middle_name'] = $arr_form_data['middile_name'];
                            $arr_user_cred['email'] = $arr_form_data['email'];
                            $arr_user_cred['phone'] = $arr_form_data['mobile_no'];

                            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                            $result = $this->UserCredentials->save($arr_user_cred);
                        }
                    } else {
                        //Manually generate emp id for companies without device
                        $arr_user_cred = array();
                        if ($pkey > 0) {
                            //Insert user credentials
                            //Get company_code
                            $auth_user = $this->Session->read("Auth.User");
                            $str_company_code = isset($auth_user['company_code']) ? $auth_user['company_code'] : '';

                            //Generate user_id
                            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                            $arr_last_user = $this->UserCredentials->find('first', array('order' => array('user_pkey' => 'DESC')));
                            $auth_user_id = isset($auth_user['user_id']) ? $auth_user['user_id'] : '';
                            if (isset($arr_last_user['UserCredentials']['user_id']) && $arr_last_user['UserCredentials']['user_id'] != $auth_user_id) {
                                if (isset($arr_last_user['UserCredentials']['user_id']) && $arr_last_user['UserCredentials']['user_id'] != '') {
                                    $arr_user_id = explode($str_company_code, $arr_last_user['UserCredentials']['user_id']);
                                    $last_user_id = isset($arr_user_id[1]) ? $arr_user_id[1] : 0;
                                    $user_id = $last_user_id + 1;
                                } else {
                                    $user_id = '1000';
                                }
                            } else {
                                //no employees added yet
                                $user_id = '1000';
                            }
                            $arr_user_cred['user_pkey'] = 0;
                            $arr_user_cred['emp_fkey'] = $pkey;
                            $arr_user_cred['company_code'] = $str_company_code;
                            $arr_user_cred['user_id'] = $str_company_code . $user_id;
                            $arr_user_cred['password'] = /* Security::hash(rand(), null, true);// */ rand();
                            $arr_user_cred['access_allowed'] = 'n';
                            $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                            $arr_user_cred['last_name'] = $arr_form_data['last_name'];
                            $arr_user_cred['middle_name'] = $arr_form_data['middile_name'];
                            $arr_user_cred['email'] = $arr_form_data['email'];
                            $arr_user_cred['phone'] = $arr_form_data['mobile_no'];

                            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                            $result = $this->UserCredentials->save($arr_user_cred);
                        }
                    }
                }
            } else {
                if ($model == 'EmployeeDetails') {
                    //Update user credentials
                    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                    $this->UserCredentials->updateAll(array('UserCredentials.first_name' => "'" . $arr_form_data['first_name'] . "'", 'UserCredentials.last_name' => "'" . $arr_form_data['last_name'] . "'", 'UserCredentials.middle_name' => "'" . $arr_form_data['middile_name'] . "'", 'UserCredentials.email' => "'" . $arr_form_data['email'] . "'", 'UserCredentials.phone' => "'" . $arr_form_data['mobile_no'] . "'",), array('UserCredentials.emp_fkey' => $pkey));
                }
            }
            return json_encode(array('success' => TRUE, 'pkey' => $pkey, 'message' => $message));
        }
    }

    public function deleteEmployees()
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["ids"])) {
            $ar_ids = explode(",", $_REQUEST["ids"]);
            //debug($ar_ids);
            $this->EmployeeDetails->updateAll(array('EmployeeDetails.status' => 0), array('EmployeeDetails.emp_pkey' => $ar_ids));
            $result['success'] = 1;
        }
        echo json_encode($result);
    }

    public function downloadempdataformat()
    {
        $this->autoRender = FALSE;

        $auth_user = $this->Session->read("Auth.User");
        $file_name = isset($auth_user['company_code']) ? $auth_user['company_code'] . ".xlsx" : "employeedataformat_" . strtotime() . ".xlsx";

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);

        // create a file pointer connected to the output stream
        //$output = fopen('php://output', 'w');

        App::import('Vendor', 'EmployeeCSVData', array('file' => 'EmployeeCSVData.php'));
        $empcsvdata = new EmployeeCSVData();
        $emp_details_schema = $empcsvdata->getFieldHeadings('EmployeeDetails');
        $emp_prof_schema = $empcsvdata->getFieldHeadings('EmployeeProfessionalDetails');
        $emp_schema = array_merge($emp_details_schema, $emp_prof_schema);
        //fputcsv($output, $emp_schema);

        App::import('Vendor', 'EmployeeCSVData', array('file' => 'EmployeeCSVData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $empcsvdata = new EmployeeCSVData();
        $emp_details_schema = $empcsvdata->getFieldHeadings('EmployeeDetails');
        $emp_prof_schema = $empcsvdata->getFieldHeadings('EmployeeProfessionalDetails');
        $emp_schema = array_merge($emp_details_schema, $emp_prof_schema);

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");

        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet();

        $sheet = array($emp_schema);
        foreach ($sheet as $row => $columns) {
            foreach ($columns as $column => $data) {
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . "1", $data);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('Employee Data');

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    public function uploadandsaveempdetails($emp_branch = 0)
    {
        $this->autoRender = FALSE;
        $authuser = $this->Session->read("Auth.User");
        $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_' . strtotime("now") . '.xlsx' : 'empdata_' . strtotime("now") . '.xlsx';
        $targetpath = getcwd() . "/files/" . $filename;
        if (move_uploaded_file($_FILES['empdata']['tmp_name'][0], $targetpath)) {

            App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

            $objReader = new PHPExcel_Reader_Excel2007();
            $objPHPExcel = $objReader->load($targetpath);
            //ARCHIVE excel2007 dir

            $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
            $lastColumn++;
            $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
            $arrayempdata = array();
            $mandatory_fields_warning = FALSE;
            if ($highestRowIndex > 1) {
                //atleast one employee records found
                $index = 0;
                for ($row = 1; $row <= $highestRowIndex; $row++) {
                    if ($row == 1) {
                        //Get mandatory headings array here
                        $array_mandatory_columns = array();
                        $array_mandatory_column_names = array('First Name', 'Last Name', 'Gender', 'Joining Date');
                        for ($col = 'A'; $col != $lastColumn; $col++) {
                            $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                            if (in_array($value, $array_mandatory_column_names)) {
                                array_push($array_mandatory_columns, $col);
                            }
                        }
                    } else {
                        for ($col = 'A'; $col != $lastColumn; $col++) {
                            if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                $mandatory_fields_warning = true;
                                break 2;
                            }
                            $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                            $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
                        }
                        $index++;
                    }
                }
                if ($mandatory_fields_warning) {
                    //Exit if mandatory fields not entered
                    unlink($targetpath);
                    echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
                    exit;
                } else {
                    //Continue with save if mandatory field warning is not there
                    //Save employees and return success
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

                    App::import('Vendor', 'EmployeeCSVData', array('file' => 'EmployeeCSVData.php'));
                    $empcsvdata = new EmployeeCSVData();
                    $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
                    $arr_empprof_fields = $empcsvdata->getFieldNames('EmployeeProfessionalDetails');
                    foreach ($arrayempdata as $key => $row) {
                        $arr_empdetails_data = array();
                        $arr_empdetails_data['status'] = 1;
                        $str_company_code = isset($authuser['company_code']) ? $authuser['company_code'] : '';
                        $arr_empdetails_data['company_code'] = $str_company_code;
                        $arr_empdetails_data['branch_code'] = $emp_branch;
                        foreach ($arr_empdetails_fields as $field => $fieldlabel) {
                            if ($field == 'date_of_birth') {
                                $fieldValue = $row[$fieldlabel];
                                $fieldValue = substr($fieldValue, 4, 4) . '-' . substr($fieldValue, 2, 2) . '-' . substr($fieldValue, 0, 2);
                            } else if (in_array($field, array('classification', 'maritual_status'))) {
                                $fieldValue = strtolower($row[$fieldlabel]);
                            } else {
                                $fieldValue = $row[$fieldlabel];
                            }
                            $arr_empdetails_data[$field] = $fieldValue;
                        }
                        $result1 = $this->EmployeeDetails->save($arr_empdetails_data);

                        if (!empty($result1)) {
                            $pkey = $this->EmployeeDetails->getLastInsertID();

                            $arr_user_cred = array();
                            if ($pkey > 0) {
                                //Insert user credentials
                                //Get company_code
                                $auth_user = $this->Session->read("Auth.User");
                                $str_company_code = isset($auth_user['company_code']) ? $auth_user['company_code'] : '';

                                $sessionObj = $this->Session->read("Auth.User");
                                $company_key = $sessionObj['company_key'];
                                $arr_central_control = $this->CentralControl->find('first', array('fields' => array('punch_type'), 'conditions' => array('control_pkey' => $company_key)));
                                $punch_type = isset($arr_central_control['CentralControl']['punch_type']) ? $arr_central_control['CentralControl']['punch_type'] : '';
                                if ($punch_type == 'device') {
                                    $user_id = '';
                                    //No device details here on manually entering emp data
                                } else {
                                    //Generate user_id
                                    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                    $arr_last_user = $this->UserCredentials->find('first', array('order' => array('user_pkey' => 'DESC')));
                                    $auth_user_id = isset($auth_user['user_id']) ? $auth_user['user_id'] : '';
                                    if (isset($arr_last_user['UserCredentials']['user_id']) && $arr_last_user['UserCredentials']['user_id'] != $auth_user_id) {
                                        //fetch userid and increment it
                                        if (isset($arr_last_user['UserCredentials']['user_id']) && $arr_last_user['UserCredentials']['user_id'] != '') {
                                            $arr_user_id = explode($str_company_code, $arr_last_user['UserCredentials']['user_id']);
                                            $last_user_id = isset($arr_user_id[1]) ? $arr_user_id[1] : 0;
                                            $user_id = $last_user_id + 1;
                                        } else {
                                            $user_id = '1000';
                                        }
                                    } else {
                                        //no employees added yet
                                        $user_id = '1000';
                                    }
                                }
                                $arr_user_cred['user_pkey'] = 0;
                                $arr_user_cred['emp_fkey'] = $pkey;
                                $arr_user_cred['company_code'] = $str_company_code;
                                $arr_user_cred['user_id'] = $str_company_code . $user_id;
                                $arr_user_cred['password'] = /* Security::hash(rand(), null, true);// */ rand();
                                $arr_user_cred['access_allowed'] = 'n';
                                $arr_user_cred['first_name'] = isset($arr_empdetails_data['first_name']) ? $arr_empdetails_data['first_name'] : '';
                                $arr_user_cred['last_name'] = isset($arr_empdetails_data['last_name']) ? $arr_empdetails_data['last_name'] : '';
                                $arr_user_cred['middle_name'] = isset($arr_empdetails_data['middile_name']) ? $arr_empdetails_data['middile_name'] : '';
                                $arr_user_cred['email'] = isset($arr_empdetails_data['email']) ? $arr_empdetails_data['email'] : '';
                                $arr_user_cred['phone'] = isset($arr_empdetails_data['mobile_no']) ? $arr_empdetails_data['mobile_no'] : '';

                                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                $result = $this->UserCredentials->save($arr_user_cred);

                                $arr_empprof_data = array();
                                $arr_empprof_data['emp_fkey'] = $pkey;
                                $arr_empprof_data['emp_id'] = $str_company_code . $user_id;
                                $arr_empprof_data['emp_branch'] = $emp_branch;

                                foreach ($arr_empprof_fields as $field => $fieldlabel) {
                                    if ($field == 'joining_date') {
                                        $fieldValue = $row[$fieldlabel];
                                        $fieldValue = substr($fieldValue, 4, 4) . '-' . substr($fieldValue, 2, 2) . '-' . substr($fieldValue, 0, 2);
                                    } else {
                                        $fieldValue = $row[$fieldlabel];
                                    }
                                    $arr_empprof_data[$field] = $fieldValue;
                                }
                                $result2 = $this->EmployeeProfessionalDetails->save($arr_empprof_data);
                            }
                        }
                    }
                }
                unlink($targetpath);
                echo json_encode(array('success' => 1, 'msg' => 'Employee data imported successfully'));
                exit;
            } else {
                unlink($targetpath);
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee data import failed, no data found!'));
                exit;
            }
        } else {
            echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee data import failed!'));
            exit;
        }
    }

    public function getcurrentemployeekey()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $sessionObj = $this->Session->read("Auth.User");
        if (isset($sessionObj['user_group']) && $sessionObj['user_group'] == 2) {
            return json_encode(array('success' => true, 'empPkey' => $sessionObj['emp_fkey']));
        }
        return json_encode(array('success' => false));
    }

    public function addEmpToLeave()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];

        $leavegroup = (isset($_REQUEST['leavegroup']) ? $_REQUEST['leavegroup'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $data['id'] = 0;
                $data['type'] = 'LEAVE';
                $data['emp_fkey'] = $value;
                $data['policy_id'] = $leavegroup;
                $data['created_by'] = $this->Session->read('login_user_id');
                $this->EmployeeConfig->save($data);
            }
        }
    }

    public function removeEmpFromLeave()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];

        $leavegroup = (isset($_REQUEST['leavegroup']) ? $_REQUEST['leavegroup'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                /*
                  $data['id'] = 0;
                 */
                $condition['type'] = 'LEAVE';
                $condition['emp_fkey'] = $value;
                $condition['policy_id'] = $leavegroup;
                $curr_user_id = $this->Session->read('login_user_id');
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);
            }
        }
    }

    public function listemployeesinleave()
    {

        $leavegroup = (isset($_REQUEST['leavegroup']) ? $_REQUEST['leavegroup'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //echo $shift;
        $emp_in_policy = $this->EmployeeConfig->find(
            "list",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "type" => "LEAVE",
                    "policy_id" => $leavegroup,
                    "status" => 1
                )
            )
        );

        /*$fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,Branches.branch_code,Branches.branch_name';
        $joins[] = array('table' => 'emp_proff', 'alias' => 'EmployeeProfessionalDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey'));
        $joins[] = array('table' => 'branches', 'alias' => 'Branches', 'type' => 'LEFT', 'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code'));*/

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );
        $conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_in_policy);

        // Edited by Akshay on 7-2-2025
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }

        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    public function listemployeesforleave()
    {

        /*$leavegroup = (isset($_REQUEST['leavegroup']) ? $_REQUEST['leavegroup'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $emp_in_leave = $this->EmployeeConfig->find("list", array("fields" => "emp_fkey", "conditions" => array("type" => "LEAVE", "LEAVEPOLICY_GROUP_ID" => $leavegroup)));

        $db = $this->EmployeeConfig->getDataSource();
        $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,Branches.branch_code,Branches.branch_name';
        $joins[] = array('table' => 'emp_proff', 'alias' => 'EmployeeProfessionalDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey'));
        $joins[] = array('table' => 'branches', 'alias' => 'Branches', 'type' => 'LEFT', 'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code'));
        $conditions["EmployeeDetails.status"] = 1;
        //array('status' => 1, 'EmployeeDetails.emp_pkey NOT IN' => $emp_in_shift);
        if (!empty($emp_in_leave)) {
            $conditions["NOT"] = array('EmployeeDetails.emp_pkey' => $emp_in_leave);
        }*/

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );

        $conditions = array(
            'EmployeeDetails.status' => 1,
            'OR' => array(
                'EmployeeProfessionalDetails.LEAVEPOLICY_GROUP_ID = ""',
                'EmployeeProfessionalDetails.LEAVEPOLICY_GROUP_ID IS NULL'
            )
        );

        // Edited by Akshay on 7-2-2025
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    public function listemployeesforconfig()
    {

        $this->autoRender = false;
        $this->layout = null;

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name';
        $conditions  =  array(
            'EmployeeDetails.status' => 1
        );

        // Edited by Akshay on 7-2-2025
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $employeelist = $this->EmployeeDetails->find(
            "all",
            array(
                'fields' => $fields,
                'conditions' => $conditions
            )
        );

        $arr_employees = array();

        $arr_employees["rows"] = array();
        foreach ($employeelist as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name']);
            $resp_emp["rows"][] = $data;
        }

        echo json_encode($resp_emp);
    }
    public function listsalaryemployeesforconfig()
    {

        $this->autoRender = false;
        $this->layout = null;

        $fields = 'structure_id,structure_name,structure_active,structure_eg_amt';
        $conditions  =  array(
            'SalaryStructures.structure_active' => 1
        );
        $this->SalaryStructures->useDbConfig = $this->Session->read('ds');
        $employeelist = $this->SalaryStructures->find(
            "all",
            array(
                'fields' => $fields,
                'conditions' => $conditions
            )
        );
        //  debug($employeelist);
        $arr_employees = array();

        $arr_employees["rows"] = array();
        $data = array();
        foreach ($employeelist as $key => $value) {

            $data['id'] = $value['SalaryStructures']['structure_id'];
            $data['data'] = array($value['SalaryStructures']['structure_name'] . ' - ' . $value['SalaryStructures']['structure_eg_amt']);
            // debug($data['data']);
            $resp_emp["rows"][] = $data;
        }

        echo json_encode($resp_emp);
    }
    public function listemployeesforhierarchy()
    {

        $selected_emp_pkey = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);
        $arr_employees_to_exclude = array($selected_emp_pkey);
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeProfessionalDetails->find(
            "all",
            array(
                'fields' => 'attr1',
                'conditions' => array(
                    'EmployeeProfessionalDetails.emp_fkey' => $selected_emp_pkey
                )
            )
        );
        foreach ($arr_emp as $val) {
            if (isset($val['EmployeeProfessionalDetails']['attr1'])) {
                $arr_employees_to_exclude[] = $val['EmployeeProfessionalDetails']['attr1'];
            }
        }

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );

        $conditions = array(
            'EmployeeDetails.status' => 1,
            'OR' => array(
                'EmployeeProfessionalDetails.attr1 = ""',
                'EmployeeProfessionalDetails.attr1 IS NULL'
            ),
            'NOT' => array(
                'EmployeeDetails.emp_pkey' => $arr_employees_to_exclude
            )
        );

        // Edited by Akshay on 7-2-2025
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    public function listemployeesinsalary()
    {

        $parent = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $emp_under = $this->EmployeeConfig->find(
            "list",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "type" => "SALARY",
                    "policy_id" => $parent,
                    "status" => 1
                )
            )
        );
        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,EMPCTCTransaction.emp_anual_ctc,Designation.desig_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code', 'Designation.status' => 1)
            ),
            array(
                'table' => 'emp_ctc_transaction',
                'alias' => 'EMPCTCTransaction',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EMPCTCTransaction.emp_fkey', "EMPCTCTransaction.end_date_effective is null")
            ),
        );
        $conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_under);

        // Edited by Akshay on 7-2-2025
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], round($value["EMPCTCTransaction"]['emp_anual_ctc'] / 12));
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }



    public function listemployeesforsalary()
    {

        $selected_emp_pkey = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);

        $conditionss = "structure_id = $selected_emp_pkey";
        $this->SalaryStructures->useDbConfig = $this->Session->read('ds');
        $employeelist = $this->SalaryStructures->find(
            "all",
            array(
                'conditions' => $conditionss
            )
        );
        $ctccheck = $employeelist['0']['SalaryStructures']['structure_eg_amt'];
        $arr_employees_to_exclude = array();
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeProfessionalDetails->find(
            "all",
            array(
                'fields' => 'attr1',
                'conditions' => array(
                    'EmployeeProfessionalDetails.emp_fkey' => $selected_emp_pkey
                )
            )
        );

        //foreach ($arr_emp as $val){
        //    if(isset($val['EmployeeProfessionalDetails']['attr1'])){
        //        $arr_employees_to_exclude[] = $val['EmployeeProfessionalDetails']['attr1'];
        //    }            
        //}

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,EMPCTCTransaction.emp_anual_ctc,Units.branch_name,EmployeeProfessionalDetails.emp_type';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'emp_ctc_transaction',
                'alias' => 'EMPCTCTransaction',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EMPCTCTransaction.emp_fkey', '`end_date_effective` IS NULL')
            ),
        );

        $conditions = array(
            'EmployeeDetails.status' => 1,
            'OR' => array(
                'EmployeeProfessionalDetails.structure_id = ""',
                'EmployeeProfessionalDetails.structure_id IS NULL'
            ),
            'NOT' => array(
                'EmployeeDetails.emp_pkey' => $arr_employees_to_exclude
            )
        );

        // Edited by Akshay on 7-2-2025
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
        //debug($arr_emp);       
        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {
            $type = $value['EmployeeProfessionalDetails']['emp_type'];
            $ctcemp = $value["EMPCTCTransaction"]['emp_anual_ctc'];
            if ($type == 'DAILY WAGES') {
                $monthlyCtc = $ctcemp;
            } else {
                $monthlyCtc = $ctcemp / 12;
            }
            if ($monthlyCtc >= $ctccheck) {
                $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
                $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], round($monthlyCtc));
                $resp_emp["rows"][] = $data;
            }
        }

        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    public function listemployeesinhierarchy()
    {

        $parent = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $emp_under = $this->EmployeeConfig->find(
            "list",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "type" => "HIERARCHY",
                    "policy_id" => $parent,
                    "status" => 1
                )
            )
        );

        $emp_under = $parent;
        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name, EmployeeConfig.hirc_leval as hirc_leval, Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
            array(
                'table' => 'emp_config',
                'alias' => 'EmployeeConfig',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeConfig.emp_fkey = EmployeeProfessionalDetails.emp_fkey', 'EmployeeConfig.type = "HIERARCHY"', 'EmployeeConfig.status = 1', 'policy_id' => $parent)
            ),
        );
        //$conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_under);
        $conditions = array(
            'EmployeeDetails.status' => 1,
            'OR' => array(
                'EmployeeProfessionalDetails.attr1' => $emp_under
            )
        );

        // Edited by Akshay on 7-2-2025
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'order' => array('EmployeeConfig.hirc_leval' => 'ASC'), 'joins' => $joins, 'conditions' => $conditions));

        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name'], $value['EmployeeConfig']['hirc_leval']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }
    public function addEmpToHierarchy()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];

        $parent_emp_pkey = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $data['created_by'] = $createdby = $this->Session->read('login_user_id');
                $order = $this->EmployeeProfessionalDetails->query("select `hirc_leval` from emp_config WHERE  `emp_config`.`emp_fkey` = $value"
                    . " and type = 'HIERARCHY' and `status` = 1");
                $this->EmployeeProfessionalDetails->query("UPDATE `emp_config`   SET `status` = 0,modified_by = '$createdby',
 `modification_date` = now()  WHERE `emp_config`.`emp_fkey` = $value and type = 'HIERARCHY' and `status` = 1");
                $order = isset($order['0']['emp_config']['hirc_leval']) ? $order['0']['emp_config']['hirc_leval'] : '';
                if ($order > 1) {
                    $data['hirc_leval'] = $order;
                }
                $data['id'] = 0;
                $data['type'] = 'HIERARCHY';
                $data['emp_fkey'] = $value;
                $data['policy_id'] = $parent_emp_pkey;

                $this->EmployeeConfig->save($data);
                $conditions = array('EmployeeProfessionalDetails.emp_fkey' => $value);

                //$this->EmployeeProfessionalDetails->updateAll(array('EmployeeProfessionalDetails.attr155' => $parent_emp_pkey, 'EmployeeProfessionalDetails.modified_date' => 'now()'),$conditions);
                $this->EmployeeProfessionalDetails->query("UPDATE `emp_proff` AS `EmployeeProfessionalDetails`  SET `EmployeeProfessionalDetails`.`attr1` = $parent_emp_pkey,
 `EmployeeProfessionalDetails`.`modified_date` = now()  WHERE `EmployeeProfessionalDetails`.`emp_fkey` = $value");
            }
        }
    }

    public function reOrderEmps()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $createdby = $this->Session->read('login_user_id');
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $parent_emp_pkey = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);

        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $i =  $key + 1;
                $condition['status'] = 1;
                $condition['type'] = 'HIERARCHY';
                $condition['emp_fkey'] = $value;
                $condition['policy_id'] = $parent_emp_pkey;
                //$result = $this->EmployeeConfig->updateAll(array('EmployeeConfig.hirc_up_flag' => '"Y"', 'EmployeeConfig.hirc_leval' => $i),$condition);
                $result = $this->EmployeeProfessionalDetails->query("UPDATE `emp_config` AS `EmployeeConfig` SET `EmployeeConfig`.`hirc_up_flag` = 'Y', "
                    . "`EmployeeConfig`.`hirc_leval` = '$i' WHERE `status` = 1 AND `type` = 'HIERARCHY' AND `emp_fkey` = $value AND "
                    . "`policy_id` = $parent_emp_pkey");
                // debug($result);
                if (empty($result)) {
                    $this->EmployeeProfessionalDetails->query("UPDATE `emp_config`   SET `status` = 0,modified_by = '$createdby',
 `modification_date` = now()  WHERE `emp_config`.`emp_fkey` = $value and type = 'HIERARCHY' and `status` = 1");
                    $data['hirc_leval'] = $i;
                    $data['id'] = 0;
                    $data['type'] = 'HIERARCHY';
                    $data['emp_fkey'] = $value;
                    $data['policy_id'] = $parent_emp_pkey;
                    $this->EmployeeConfig->save($data);
                }
            }
        }
    }

    public function removeEmpFromHierarchy()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];

        $parent_emp_pkey = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                /*
                  $data['id'] = 0;
                 */
                $condition['type'] = 'HIERARCHY';
                $condition['emp_fkey'] = $value;
                $condition['policy_id'] = $parent_emp_pkey;
                //$condition['hirc_up_flag'] = 'N';
                $curr_user_id = $this->Session->read('login_user_id');
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);
                $conditions = array('EmployeeProfessionalDetails.emp_fkey' => $value);
                $this->EmployeeProfessionalDetails->updateAll(array('EmployeeProfessionalDetails.attr1' => NULL, 'EmployeeProfessionalDetails.modified_date' => 'now()'), $conditions);
            }
        }
    }
    public function checkEmpSalaryStructure()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['emp_id'];
        $empId = $ids;
        //  debug($empId);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //added by megha sallary allocation 1
        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

        try {
            if (count($empId > 1)) {
                $valueArray = explode(',', $empId);
                foreach ($valueArray as $value) {
                    $componentUpload = $this->EmployeeSalaryStructure->query("SELECT created_by FROM emp_salary_structure WHERE emp_fkey = $value");
                }
            } else {
                $componentUpload = $this->EmployeeSalaryStructure->query("SELECT created_by FROM emp_salary_structure WHERE emp_fkey = $empId");
            }
            // debug($componentUpload);
            $createdBy = '';
            foreach ($componentUpload as $index => $item) {
                // debug($item['emp_salary_structure']['created_by']);
                if (isset($item['emp_salary_structure']['created_by']) && $item['emp_salary_structure']['created_by'] === 'upload') {
                    $createdBy = $item['emp_salary_structure']['created_by'];
                    break; // Stop the loop once the first match is found
                }
            }
            if ($createdBy === '') {
                $createdBy = ''; // Replace 'default_value' with your desired default value
            }


            echo json_encode(['created_by' => $createdBy]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
            exit();
        }
    }
    public function checkEmpShift()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['emp_id'];
        //debug($ids);
        $empId = $ids;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //added by megha sallary allocation 1
        // $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

        try {
            if (count($empId > 1)) {
                $valueArray = explode(',', $empId);
                foreach ($valueArray as $value) {
                    $shiftUpload = $this->EmployeeConfig->query("SELECT day_time_seq FROM emp_proff WHERE emp_fkey = $value");
                }
            } else {
                // $shiftUpload = $this->EmployeeConfig->query("SELECT policy_id FROM emp_config WHERE emp_fkey = $empId and type = 'SHIFT' and status = '1'");
                $shiftUpload = $this->EmployeeConfig->query("SELECT day_time_seq FROM emp_proff WHERE emp_fkey = $empId");
            }
            //  debug($shiftUpload);exit;
            $shiftvalue = '';
            foreach ($shiftUpload as $index => $item) {
                //debug($item['emp_proff']['emp_grade']);
                if (isset($item['emp_proff']['day_time_seq']) && $item['emp_proff']['day_time_seq'] != '0') {
                    $shiftvalue = $item['emp_proff']['day_time_seq'];
                    break; // Stop the loop once the first match is found
                }
            }
            if ($shiftvalue === '') {
                $shiftvalue = ''; // Replace 'default_value' with your desired default value
            }


            echo json_encode(['shift_value' => $shiftvalue]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
            exit();
        }
    }
    public function checkEmpMultiShift()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['emp_id'];
        //debug($ids);
        $empId = $ids;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //added by megha sallary allocation 1
        // $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

        try {
            if (count($empId > 1)) {
                $valueArray = explode(',', $empId);
                foreach ($valueArray as $value) {
                    $multishiftUpload = $this->EmployeeConfig->query("SELECT multishift FROM emp_proff WHERE emp_fkey = $value");
                }
            } else {
                $multishiftUpload = $this->EmployeeConfig->query("SELECT multishift FROM emp_proff WHERE emp_fkey = $empId");
            }
            //  debug($shiftUpload);exit;
            $multishiftvalue = '';
            foreach ($multishiftUpload as $index => $item) {
                //debug($item['emp_proff']['emp_grade']);
                if (isset($item['emp_proff']['multishift']) && $item['emp_proff']['multishift'] != '0') {
                    $multishiftvalue = $item['emp_proff']['multishift'];
                    break; // Stop the loop once the first match is found
                }
            }
            if ($multishiftvalue === '') {
                $multishiftvalue = ''; // Replace 'default_value' with your desired default value
            }


            echo json_encode(['multishift_value' => $multishiftvalue]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
            exit();
        }
    }
    public function checkEmpLeavepolicy()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['emp_id'];
        //debug($ids);
        $empId = $ids;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //added by megha sallary allocation 1
        // $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

        try {
            if (count($empId > 1)) {
                $valueArray = explode(',', $empId);
                foreach ($valueArray as $value) {
                    // $leaveUpload = $this->EmployeeConfig->query("SELECT policy_id FROM emp_config WHERE emp_fkey = $empId and type = 'LEAVE' and status = '1'");
                    $leaveUpload = $this->EmployeeConfig->query("SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey = $value");
                }
            } else {
                $leaveUpload = $this->EmployeeConfig->query("SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey = $empId");
            }
            //  debug($leaveUpload);exit;
            $leavevalue = '';
            foreach ($leaveUpload as $index => $item) {
                //debug($item['emp_proff']['emp_grade']);
                if (isset($item['emp_proff']['LEAVEPOLICY_GROUP_ID']) && $item['emp_proff']['LEAVEPOLICY_GROUP_ID'] != '0') {
                    $leavevalue = $item['emp_proff']['LEAVEPOLICY_GROUP_ID'];
                    break; // Stop the loop once the first match is found
                }
            }
            if ($leavevalue === '') {
                $leavevalue = ''; // Replace 'default_value' with your desired default value
            }


            echo json_encode(['leave_value' => $leavevalue]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
            exit();
        }
    }
    public function checkEmpHoliday()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['emp_id'];
        //debug($ids);
        $empId = $ids;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //added by megha sallary allocation 1
        // $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

        try {
            if (count($empId > 1)) {
                $valueArray = explode(',', $empId);
                foreach ($valueArray as $value) {
                    // $leaveUpload = $this->EmployeeConfig->query("SELECT policy_id FROM emp_config WHERE emp_fkey = $empId and type = 'LEAVE' and status = '1'");
                    $HolidayUpload = $this->EmployeeConfig->query("SELECT HOLIDAY_GROUP_ID FROM emp_proff WHERE emp_fkey = $value");
                }
            } else {
                $HolidayUpload = $this->EmployeeConfig->query("SELECT HOLIDAY_GROUP_ID FROM emp_proff WHERE emp_fkey = $empId");
            }
            //  debug($leaveUpload);exit;
            $holidayvalue = '';
            foreach ($HolidayUpload as $index => $item) {
                //debug($item['emp_proff']['emp_grade']);
                if (isset($item['emp_proff']['HOLIDAY_GROUP_ID']) && $item['emp_proff']['HOLIDAY_GROUP_ID'] != '0') {
                    $holidayvalue = $item['emp_proff']['HOLIDAY_GROUP_ID'];
                    break; // Stop the loop once the first match is found
                }
            }
            if ($holidayvalue === '') {
                $holidayvalue = ''; // Replace 'default_value' with your desired default value
            }


            echo json_encode(['holiday_value' => $holidayvalue]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
            exit();
        }
    }
    public function checkEmphierarchy()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['emp_id'];
        //debug($ids);
        $empId = $ids;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //added by megha sallary allocation 1
        // $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

        try {
            if (count($empId > 1)) {
                $valueArray = explode(',', $empId);
                foreach ($valueArray as $value) {
                    // $leaveUpload = $this->EmployeeConfig->query("SELECT policy_id FROM emp_config WHERE emp_fkey = $empId and type = 'LEAVE' and status = '1'");
                    $EmphierarchypUpload = $this->EmployeeConfig->query("SELECT attr1 FROM emp_proff WHERE emp_fkey = $value");
                }
            } else {
                $EmphierarchypUpload = $this->EmployeeConfig->query("SELECT attr1 FROM emp_proff WHERE emp_fkey = $empId");
            }
            //  debug($leaveUpload);exit;
            $Emphierarchyvalue = '';
            foreach ($EmphierarchypUpload as $index => $item) {
                //debug($item['emp_proff']['emp_grade']);
                if (isset($item['emp_proff']['attr1']) && $item['emp_proff']['attr1'] != '0') {
                    $Emphierarchyvalue = $item['emp_proff']['attr1'];
                    break; // Stop the loop once the first match is found
                }
            }
            if ($Emphierarchyvalue === '') {
                $Emphierarchyvalue = ''; // Replace 'default_value' with your desired default value
            }


            echo json_encode(['Emphierarchy_value' => $Emphierarchyvalue]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
            exit();
        }
    }
    public function checkEmpNoticedays()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['emp_id'];
        //debug($ids);
        $empId = $ids;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //added by megha sallary allocation 1
        // $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

        try {
            // $leaveUpload = $this->EmployeeConfig->query("SELECT policy_id FROM emp_config WHERE emp_fkey = $empId and type = 'LEAVE' and status = '1'");
            if (count($empId > 1)) {
                $valueArray = explode(',', $empId);
                foreach ($valueArray as $value) {
                    $EmpnoticepUpload = $this->EmployeeConfig->query("SELECT notice_days FROM emp_proff WHERE emp_fkey = $value");
                }
            } else {
                $EmpnoticepUpload = $this->EmployeeConfig->query("SELECT notice_days FROM emp_proff WHERE emp_fkey = $empId");
            }
            //  debug($leaveUpload);exit;
            $Empnoticevalue = '';
            foreach ($EmpnoticepUpload as $index => $item) {
                //debug($item['emp_proff']['emp_grade']);
                if (isset($item['emp_proff']['notice_days']) && $item['emp_proff']['notice_days'] != '0') {
                    $Empnoticevalue = $item['emp_proff']['notice_days'];
                    break; // Stop the loop once the first match is found
                }
            }
            if ($Empnoticevalue === '') {
                $Empnoticevalue = ''; // Replace 'default_value' with your desired default value
            }


            echo json_encode(['Empnotice_value' => $Empnoticevalue]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
            exit();
        }
    }
    public function checkEmpDivision()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['emp_id'];
        //debug($ids);
        $empId = $ids;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //added by megha sallary allocation 1
        // $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

        try {
            if (count($empId > 1)) {
                $valueArray = explode(',', $empId);
                foreach ($valueArray as $value) {
                    // $leaveUpload = $this->EmployeeConfig->query("SELECT policy_id FROM emp_config WHERE emp_fkey = $empId and type = 'LEAVE' and status = '1'");
                    $EmpdivisionUpload = $this->EmployeeConfig->query("SELECT emp_vertical FROM emp_proff WHERE emp_fkey = $value");
                }
            } else {
                $EmpdivisionUpload = $this->EmployeeConfig->query("SELECT emp_vertical FROM emp_proff WHERE emp_fkey = $empId");
            }
            //  debug($leaveUpload);exit;
            $Empdivisionvalue = '';
            foreach ($EmpdivisionUpload as $index => $item) {
                //debug($item['emp_proff']['emp_grade']);
                if (isset($item['emp_proff']['emp_vertical']) && $item['emp_proff']['emp_vertical'] != '0') {
                    $Empdivisionvalue = $item['emp_proff']['emp_vertical'];
                    break; // Stop the loop once the first match is found
                }
            }
            if ($Empdivisionvalue === '') {
                $Empdivisionvalue = ''; // Replace 'default_value' with your desired default value
            }


            echo json_encode(['Empdivision_value' => $Empdivisionvalue]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
            exit();
        }
    }
    public function checkEmpSection()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['emp_id'];
        //debug($ids);
        $empId = $ids;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //added by megha sallary allocation 1
        // $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

        try {
            if (count($empId > 1)) {
                $valueArray = explode(',', $empId);
                foreach ($valueArray as $value) {
                    // $leaveUpload = $this->EmployeeConfig->query("SELECT policy_id FROM emp_config WHERE emp_fkey = $empId and type = 'LEAVE' and status = '1'");
                    $EmpsectionUpload = $this->EmployeeConfig->query("SELECT emp_sep_priv FROM emp_proff WHERE emp_fkey = $value");
                }
            } else {
                $EmpsectionUpload = $this->EmployeeConfig->query("SELECT emp_sep_priv FROM emp_proff WHERE emp_fkey = $empId");
            }
            //  debug($leaveUpload);exit;
            $Empsectionvalue = '';
            foreach ($EmpsectionUpload as $index => $item) {
                //debug($item['emp_proff']['emp_grade']);
                if (isset($item['emp_proff']['emp_sep_priv']) && $item['emp_proff']['emp_sep_priv'] != '0') {
                    $Empsectionvalue = $item['emp_proff']['emp_sep_priv'];
                    break; // Stop the loop once the first match is found
                }
            }
            if ($Empsectionvalue === '') {
                $Empsectionvalue = ''; // Replace 'default_value' with your desired default value
            }


            echo json_encode(['Empsection_value' => $Empsectionvalue]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
            exit();
        }
    }
    public function checkEmpgrade()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['emp_id'];
        //debug($ids);
        $empId = $ids;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //added by megha sallary allocation 1
        // $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

        try {
            // $leaveUpload = $this->EmployeeConfig->query("SELECT policy_id FROM emp_config WHERE emp_fkey = $empId and type = 'LEAVE' and status = '1'");
            if (count($empId > 1)) {
                $valueArray = explode(',', $empId);
                foreach ($valueArray as $value) {
                    $EmpgradeUpload = $this->EmployeeConfig->query("SELECT emp_grade FROM emp_proff WHERE emp_fkey = $value");
                }
            } else {
                $EmpgradeUpload = $this->EmployeeConfig->query("SELECT emp_grade FROM emp_proff WHERE emp_fkey = $empId");
            }
            //  debug($leaveUpload);exit;
            $Empgradevalue = '';
            foreach ($EmpgradeUpload as $index => $item) {
                //debug($item['emp_proff']['emp_grade']);
                if (isset($item['emp_proff']['emp_grade']) && $item['emp_proff']['emp_grade'] != '0') {
                    $Empgradevalue = $item['emp_proff']['emp_grade'];
                    break; // Stop the loop once the first match is found
                }
            }
            if ($Empgradevalue === '') {
                $Empgradevalue = ''; // Replace 'default_value' with your desired default value
            }


            echo json_encode(['Empgrade_value' => $Empgradevalue]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
            exit();
        }
    }
    public function checkEmpleavehierarchy()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['emp_id'];
        //debug($ids);
        $empId = $ids;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //added by megha sallary allocation 1
        // $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

        try {
            if (count($empId > 1)) {
                $valueArray = explode(',', $empId);
                foreach ($valueArray as $value) {
                    $EmpleavehierarchyUpload = $this->EmployeeConfig->query("SELECT policy_id FROM emp_config WHERE emp_fkey = $value and type = 'LAPPR' and status = '1'");
                }
            } else {
                $EmpleavehierarchyUpload = $this->EmployeeConfig->query("SELECT policy_id FROM emp_config WHERE emp_fkey = $empId and type = 'LAPPR' and status = '1'");
            }
            //$EmpleavehierarchyUpload = $this->EmployeeConfig->query("SELECT emp_grade FROM emp_proff WHERE emp_fkey = $empId");
            //  debug($leaveUpload);exit;
            $Empleavehierarchyvalue = '';
            foreach ($EmpleavehierarchyUpload as $index => $item) {
                //debug($item['emp_proff']['emp_grade']);
                if (isset($item['emp_config']['policy_id']) && $item['emp_config']['policy_id'] != '0') {
                    $Empleavehierarchyvalue = $item['emp_config']['policy_id'];
                    break; // Stop the loop once the first match is found
                }
            }
            if ($Empleavehierarchyvalue === '') {
                $Empleavehierarchyvalue = ''; // Replace 'default_value' with your desired default value
            }


            echo json_encode(['Empleavehierarchy_value' => $Empleavehierarchyvalue]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
            exit();
        }
    }
    public function addEmpToSallary()
    {
        $this->autoRender = false;
        // Edited by Akshay on 14-3-2026
        $company_code = strtoupper($this->Session->read('company_code'));
        $specialCompanies = [
           'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK',
            'GTRA','VGNN','SHYD','SRTS'
        ];
        // End

        $ids = $_REQUEST['id'];

        $emp = $ids;
        //debug($emp);
        $salary_id = $_REQUEST['parent_emp_pkey'];
        $salaryitem = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //added by megha sallary allocation 1
        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
        $resp = 0;
        if ($ids) {

            // Edited by Akshay on 14-3-2026
            if (!in_array($company_code, $specialCompanies)) {
                $this->EmployeeConfig->query("UPDATE emp_ctc_transaction
                                                SET ctc_upload_type = 1
                                                WHERE emp_fkey = $emp
                                                AND ctc_upload_type = 2
                                                AND end_date_effective IS NULL;");
            }
            // End

            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');
            $user_ids = $this->Session->read('login_user_id');
            $proc = $this->EmployeeConfig->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");

            //added by megha on 29_06_19 emp_fkey added
            $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
                "all",
                array(
                    'fields' => 'emp_salary_structure_pkey,head_operator,remarks,salary_head_item_desc',
                    'conditions' => array(
                        'emp_structure_id' => $salary_id,
                        'emp_fkey' => $emp,
                        'remarks IS NOT NULL',
                        'end_date_effective is null'
                    )
                )
            );
            foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey'] : '';
                $head_operator = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator'] : '';
                $formula_from_remarks = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) : '';
                $salary_head_item_desc = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc'] : '';

                //debug($arr_formulae_from_remarks);exit;
                if (!empty($formula_from_remarks)) {
                    // debug($formula_from_remarks);
                    eval('$salary_amount = ' . $formula_from_remarks . ';');
                    //$nameOfVar = '$salary_amount = ' . $formula_from_remarks;
                    //$title = $$nameOfVar;  debug($salary_amount);

                    if (trim(strtolower($salary_head_item_desc)) == 'esi' || trim(strtolower($salary_head_item_desc)) == 'ESI - Employer Contribution' || trim(strtolower($salary_head_item_desc)) == 'ESI - Employee Contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employee contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employer contribution') {

                        if ($head_operator == 'Deduction') {
                            $salary_amount = ceil($salary_amount);
                        } else {
                            $salary_amount = round($salary_amount);
                        }
                    } else {
                        $salary_amount = round($salary_amount);
                    }
                    if ($head_operator == 'Deduction') {
                        $salary_amount *= -1;
                    }

                    $arr_emp_salary_slip_data = array(
                        'EmployeeSalaryStructure.structure_det_value' => $salary_amount
                    );
                    $this->EmployeeSalaryStructure->updateAll(
                        $arr_emp_salary_slip_data,
                        array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                    );
                }
            }
            //end sallary allocation      
            $resp = 1;
            //} catch (Exception $ex) {
            //    $resp = 0;
            // }
            $result = isset($proc['0']['0']['function']) ? $proc['0']['0']['function'] : 0;
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $data['id'] = 0;
                $data['type'] = 'SALARY';
                $data['emp_fkey'] = $value;
                $data['policy_id'] = $salaryitem;
                $data['created_by'] = $this->Session->read('login_user_id');
                if ($result == '1') {
                    $this->EmployeeConfig->save($data);
                    $resp = 1;
                } else {
                    $resp = 0;
                }
                //added by megha salary allocation 2
                try {
                    $prc = $this->EmployeeSalaryStructure->query("call salary_structure_limit_prc('$emp','$user_ids',@`perr_msg`)");
                } catch (Exception $e) {
                    debug($e);
                }
                //debug($prc);
                //Evaluate value from remarks : Added on 04 Oct 2017
                //Uncomment, on ashok's opinion
                //$this->setStructureDetValueFromRemarks($value, $salary_id);
            }
            return $resp;
        }
    }

    public function setStructureDetValueFromRemarks($emp_fkey, $emp_structure_id)
    {
        //Update salary_amount after procedure executed on emp_salary_slip
        //Fetch formula from remarks
        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
        $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
            "all",
            array(
                'fields' => 'emp_salary_structure_pkey,head_operator,head_type,remarks',
                'conditions' => array(
                    'emp_structure_id ' => $emp_structure_id,
                    'emp_fkey ' => $emp_fkey,
                    'remarks IS NOT NULL'
                )
            )
        );

        foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
            $emp_salary_structure_pkey = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey'] : '';
            $head_operator = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator'] : '';
            $head_type = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_type']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_type'] : '';
            $formula_from_remarks = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['remarks'] : '';

            if (!empty($formula_from_remarks)) {

                eval('$salary_amount = ' . $formula_from_remarks . ';');

                if ($head_operator == 'Deduction') {
                    $salary_amount *= -1;
                }

                $arr_emp_salary_structure_data = array(
                    'EmployeeSalaryStructure.structure_det_value' => $salary_amount
                );
                $this->EmployeeSalaryStructure->updateAll(
                    $arr_emp_salary_structure_data,
                    array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_structure_pkey)
                );
            }
        }
    }

    public function removeEmpFromSallary()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $parent_emp_pkey = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                /*
                  $data['id'] = 0;
                 */
                $condition['type'] = 'SALARY';
                $condition['emp_fkey'] = $value;
                $condition['policy_id'] = $parent_emp_pkey;
                //edited by megha on 28/09/2019
                //  $date = now();
                $curr_user_id = $this->Session->read('login_user_id');
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);
                // Edited by Akshay on 25-2-2026
                $this->EmployeeConfig->query("UPDATE emp_salary_structure
                                                SET end_date_effective = DATE_FORMAT(NOW(), '%Y-%m-%d')
                                                WHERE emp_fkey = '$value'
                                                AND end_date_effective IS NULL;");
                // End
            }
        }
    }
    public function listnoticemaster()
    {

        $this->autoRender = false;
        $this->layout = null;

        $fields = 'notice_pkey,notice_days,description';
        $conditions  =  array(
            'NoticePeriod.status' => 1
        );
        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        $noticeperiod = $this->NoticePeriod->find(
            "all",
            array(
                'fields' => $fields,
                'conditions' => $conditions
            )
        );
        //  debug($employeelist);
        $arr_periods = array();

        $arr_periods["rows"] = array();
        $data = array();
        foreach ($noticeperiod as $key => $value) {

            $data['id'] = $value['NoticePeriod']['notice_days'];
            $data['data'] = array($value['NoticePeriod']['notice_days'] . ' - ' . $value['NoticePeriod']['description']);
            // debug($data['data']);
            $arr_periods["rows"][] = $data;
        }

        echo json_encode($arr_periods);
    }

    public function form() {}
    public function savenoticeperiod()
    {
        $this->autoRender = FALSE;
        $arr_from_data = $this->request->data;
        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        $data = array();
        $response = array();
        $data['notice_days'] = $current_days = $arr_from_data['Days'];
        $data['description'] = $arr_from_data['Description'];
        $conditionss = "notice_days = '$current_days' ";
        $employeelist = $this->NoticePeriod->find(
            "all",
            array(
                'conditions' => $conditionss
            )
        );
        $response['status'] = 1;
        if ($employeelist) {
            $response['msg'] = "Notice Period '$current_days' Already Exist, Cannot save this period";
            $response['type'] = "warning";
        } else {
            if ($this->NoticePeriod->save($data)) {
                $response['msg'] = "Notice Period saved Successfully";
                $response['type'] = "success";
            } else {
                $response['msg'] = "Cannot save data error";
                $response['type'] = "danger";
                $response['status'] = 0;
            }
        }
        echo json_encode($response);
    }

    public function listemployeesforperiod()
    {

        $selected_emp_pkey = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);


        $conditionss = "notice_days = $selected_emp_pkey";
        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        $employeelist = $this->NoticePeriod->find(
            "all",
            array(
                'conditions' => $conditionss
            )
        );
        //        $arr_employees_to_exclude = array($selected_emp_pkey);
        //        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        //        $arr_emp = $this->EmployeeProfessionalDetails->find("all", 
        //            array(
        //                'fields' => 'attr1', 
        //                'conditions' => array(
        //                    'EmployeeProfessionalDetails.emp_fkey'=>$selected_emp_pkey
        //                )
        //            )
        //        );
        //        
        //        foreach ($arr_emp as $val){
        //            if(isset($val['EmployeeProfessionalDetails']['attr1'])){
        //                $arr_employees_to_exclude[] = $val['EmployeeProfessionalDetails']['attr1'];
        //            }            
        //        }

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,EMPCTCTransaction.emp_anual_ctc,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'emp_ctc_transaction',
                'alias' => 'EMPCTCTransaction',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EMPCTCTransaction.emp_fkey')
            ),
        );

        $conditions = array(
            'EmployeeDetails.status' => 1,
            'OR' => array(
                'EmployeeProfessionalDetails.notice_days = ""',
                'EmployeeProfessionalDetails.notice_days IS NULL'
            )
            //            ,
            //            'NOT'=>array(
            //                'EmployeeDetails.emp_pkey' => $arr_employees_to_exclude
            //            )
        );

        // Edited by Akshay on 7-2-2025
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {



            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }
    public function listemployeesinperiod()
    {

        $parent = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $emp_under = $this->EmployeeConfig->find(
            "list",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "type" => "NOTICEPER",
                    "policy_id" => $parent,
                    "status" => 1
                )
            )
        );

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );
        $conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_under);

        // Edited by Akshay on 7-2-2025
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }
    public function addEmpToNotice()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $emp = $ids;
        $salary_id = $_REQUEST['parent_emp_pkey'];
        $salaryitem = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $company = $this->Session->read('company_code');
            $user_ids = $this->Session->read('login_user_id');
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $data['id'] = 0;
                $data['type'] = 'NOTICEPER';
                $data['emp_fkey'] = $value;
                $data['policy_id'] = $salaryitem;
                $data['created_by'] = $this->Session->read('login_user_id');

                $this->EmployeeConfig->save($data);
            }
        }
    }
    public function removeEmpFromNotice()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $parent_emp_pkey = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                /*
                  $data['id'] = 0;
                 */
                $condition['type'] = 'NOTICEPER';
                $condition['emp_fkey'] = $value;
                $condition['policy_id'] = $parent_emp_pkey;
                $curr_user_id = $this->Session->read('login_user_id');
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);
            }
        }
    }

    public function listdivmaster()
    {

        $this->autoRender = false;
        $this->layout = null;

        $fields = 'id,div_code,div_name';
        $conditions  =  array(
            'Division.status' => 1
        );
        $this->Division->useDbConfig = $this->Session->read('ds');
        $div = $this->Division->find(
            "all",
            array(
                'fields' => $fields,
                'conditions' => $conditions
            )
        );
        //  debug($employeelist);
        $arr_div = array();

        $arr_div["rows"] = array();
        $data = array();
        foreach ($div as $key => $value) {

            $data['id'] = $value['Division']['id'];
            $data['data'] = array($value['Division']['div_code'] . ' - ' . $value['Division']['div_name']);
            // debug($data['data']);
            $arr_div["rows"][] = $data;
        }

        echo json_encode($arr_div);
    }
    public function listemployeesfordiv()
    {
        $selected_emp_pkey = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);
        $conditionss = "id = $selected_emp_pkey";
        $this->Division->useDbConfig = $this->Session->read('ds');
        $employeelist = $this->Division->find(
            "all",
            array(
                'conditions' => $conditionss
            )
        );
        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,EMPCTCTransaction.emp_anual_ctc,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'emp_ctc_transaction',
                'alias' => 'EMPCTCTransaction',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EMPCTCTransaction.emp_fkey')
            ),
        );
        $conditions = array(
            'EmployeeDetails.status' => 1,
            'OR' => array(
                'EmployeeProfessionalDetails.emp_vertical = "0"',
                'EmployeeProfessionalDetails.emp_vertical IS NULL'
            )
            //            ,
            //            'NOT'=>array(
            //                'EmployeeDetails.emp_pkey' => $arr_employees_to_exclude
            //            )
        );

        // Edited by Akshay on 7-2-2025
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {
            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }
    public function listemployeesindiv()
    {
        $parent = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $emp_under = $this->EmployeeConfig->find(
            "list",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "type" => "DIVISION",
                    "policy_id" => $parent,
                    "status" => 1
                )
            )
        );
        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );
        $conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_under);

        // Edited by Akshay on 7-2-2025
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {
            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }
    public function addEmpToDiv()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $emp = $ids;
        $salary_id = $_REQUEST['parent_emp_pkey'];
        $salaryitem = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $company = $this->Session->read('company_code');
            $user_ids = $this->Session->read('login_user_id');
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $data['id'] = 0;
                $data['type'] = 'DIVISION';
                $data['emp_fkey'] = $value;
                $data['policy_id'] = $salaryitem;
                $data['created_by'] = $this->Session->read('login_user_id');
                $this->EmployeeConfig->save($data);
                $condition['emp_fkey'] = $value;
                $this->EmployeeProfessionalDetails->updateAll(array('EmployeeProfessionalDetails.emp_vertical' => "'" . $salary_id . "'"), $condition);
            }
        }
    }
    public function removeEmpFromDiv()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $parent_emp_pkey = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                /*
                  $data['id'] = 0;
                 */
                $condition['type'] = 'DIVISION';
                $condition['emp_fkey'] = $value;
                $condition['policy_id'] = $parent_emp_pkey;
                $curr_user_id = $this->Session->read('login_user_id');
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);
                $conditions['emp_fkey'] = $value;
                $this->EmployeeProfessionalDetails->updateAll(array('EmployeeProfessionalDetails.emp_vertical' => "0"), $conditions);
            }
        }
    }
    //section functions
    public function listsectionmaster()
    {

        $this->autoRender = false;
        $this->layout = null;

        $fields = 'id,section_code,section_name';
        $conditions  =  array(
            'Section.status' => 1
        );
        $this->Section->useDbConfig = $this->Session->read('ds');
        $section = $this->Section->find(
            "all",
            array(
                'fields' => $fields,
                'conditions' => $conditions
            )
        );

        $arr_section = array();

        $arr_section["rows"] = array();
        $data = array();
        foreach ($section as $key => $value) {

            $data['id'] = $value['Section']['id'];
            $data['data'] = array($value['Section']['section_code'] . ' - ' . $value['Section']['section_name']);

            $arr_section["rows"][] = $data;
        }

        echo json_encode($arr_section);
    }
    public function listemployeesforsection()
    {
        $selected_emp_pkey = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);
        $conditionss = "id = $selected_emp_pkey";
        $this->Section->useDbConfig = $this->Session->read('ds');
        $employeelist = $this->Section->find(
            "all",
            array(
                'conditions' => $conditionss
            )
        );
        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,EMPCTCTransaction.emp_anual_ctc,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'emp_ctc_transaction',
                'alias' => 'EMPCTCTransaction',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EMPCTCTransaction.emp_fkey')
            ),
        );
        $conditions = array(
            'EmployeeDetails.status' => 1,
            'OR' => array(
                'EmployeeProfessionalDetails.emp_sep_priv = "0"',
                'EmployeeProfessionalDetails.emp_sep_priv IS NULL'
            )
            //            ,
            //            'NOT'=>array(
            //                'EmployeeDetails.emp_pkey' => $arr_employees_to_exclude
            //            )
        );

        // Edited by Akshay on 7-2-2025
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {
            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }
    public function listemployeesinsection()
    {
        $parent = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $emp_under = $this->EmployeeConfig->find(
            "list",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "type" => "SECTION",
                    "policy_id" => $parent,
                    "status" => 1
                )
            )
        );
        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );
        $conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_under);

        // Edited by Akshay on 7-2-2025
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {
            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }
    public function addEmpToSection()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $emp = $ids;
        $salary_id = $_REQUEST['parent_emp_pkey'];
        $salaryitem = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $company = $this->Session->read('company_code');
            $user_ids = $this->Session->read('login_user_id');
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $data['id'] = 0;
                $data['type'] = 'SECTION';
                $data['emp_fkey'] = $value;
                $data['policy_id'] = $salaryitem;
                $data['created_by'] = $this->Session->read('login_user_id');
                $this->EmployeeConfig->save($data);
                $condition['emp_fkey'] = $value;
                $this->EmployeeProfessionalDetails->updateAll(array('EmployeeProfessionalDetails.emp_sep_priv' => "'" . $salary_id . "'"), $condition);
            }
        }
    }
    public function removeEmpFromSection()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $parent_emp_pkey = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                /*
                  $data['id'] = 0;
                 */
                $condition['type'] = 'SECTION';
                $condition['emp_fkey'] = $value;
                $condition['policy_id'] = $parent_emp_pkey;
                $curr_user_id = $this->Session->read('login_user_id');
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);
                $conditions['emp_fkey'] = $value;
                $this->EmployeeProfessionalDetails->updateAll(array('EmployeeProfessionalDetails.emp_sep_priv' => "0"), $conditions);
            }
        }
    }
    //grade functions
    public function listgrademaster()
    {

        $this->autoRender = false;
        $this->layout = null;

        $fields = 'grade_pkey,grade_code,grade_name';
        $conditions  =  array(
            'Grades.status' => 1
        );
        $this->Grades->useDbConfig = $this->Session->read('ds');
        $grade = $this->Grades->find(
            "all",
            array(
                'fields' => $fields,
                'conditions' => $conditions
            )
        );

        $arr_grade = array();

        $arr_grade["rows"] = array();
        $data = array();
        foreach ($grade as $key => $value) {

            $data['id'] = $value['Grades']['grade_pkey'];
            $data['data'] = array($value['Grades']['grade_code'] . ' - ' . $value['Grades']['grade_name']);

            $arr_grade["rows"][] = $data;
        }

        echo json_encode($arr_grade);
    }
    public function listemployeesforgrade()
    {
        // $selected_emp_pkey = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);
        //$conditionss = "id = $selected_emp_pkey";
        $this->Grades->useDbConfig = $this->Session->read('ds');
        //        $employeelist = $this->Grades->find("all", 
        //            array(
        //                'conditions' => $conditionss
        //            )
        //        );
        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,EMPCTCTransaction.emp_anual_ctc,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'emp_ctc_transaction',
                'alias' => 'EMPCTCTransaction',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EMPCTCTransaction.emp_fkey')
            ),
        );
        $conditions = array(
            'EmployeeDetails.status' => 1,
            'OR' => array(
                'EmployeeProfessionalDetails.emp_grade = ""',
                'EmployeeProfessionalDetails.emp_grade = "0"',
                'EmployeeProfessionalDetails.emp_grade IS NULL'
            )
            //            ,
            //            'NOT'=>array(
            //                'EmployeeDetails.emp_pkey' => $arr_employees_to_exclude
            //            )
        );

        // Edited by Akshay on 7-2-2025
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {
            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }
    public function listemployeesingrade()
    {
        $parent = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $emp_under = $this->EmployeeConfig->find(
            "list",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "type" => "GRADE",
                    "policy_id" => $parent,
                    "status" => 1
                )
            )
        );
        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );
        $conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_under);

        // Edited by Akshay on 7-2-2025
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {
            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }
    public function addEmpToGrade()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $emp = $ids;
        $salary_id = $_REQUEST['parent_emp_pkey'];
        $salaryitem = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $company = $this->Session->read('company_code');
            $user_ids = $this->Session->read('login_user_id');
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $data['id'] = 0;
                $data['type'] = 'GRADE';
                $data['emp_fkey'] = $value;
                $data['policy_id'] = $salaryitem;
                $data['created_by'] = $this->Session->read('login_user_id');
                $this->EmployeeConfig->save($data);
                $condition['emp_fkey'] = $value;
                $this->EmployeeProfessionalDetails->updateAll(array('EmployeeProfessionalDetails.emp_grade' => "'" . $salary_id . "'"), $condition);
            }
        }
    }
    public function removeEmpFromGrade()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $parent_emp_pkey = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $condition['type'] = 'GRADE';
                $condition['emp_fkey'] = $value;
                $condition['policy_id'] = $parent_emp_pkey;
                $curr_user_id = $this->Session->read('login_user_id');
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);
                $conditions['emp_fkey'] = $value;
                $this->EmployeeProfessionalDetails->updateAll(array('EmployeeProfessionalDetails.emp_grade' => "0"), $conditions);
            }
        }
    }
    //Leave hierarchy

    public function listemployeesinleavehierarchy()
    {
        $parent = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //        $emp_under = $this->EmployeeConfig->find("list", 
        //            array(
        //                "fields" => "emp_fkey", 
        //                "conditions" => array(
        //                    "type" => "LAPPR", 
        //                    "policy_id" => $parent,
        //                    "status" => 1
        //                )
        //            )
        //        );
        $emp_under = $parent;
        $fields = 'EmployeeConfig.emp_fkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_config',
                'alias' => 'EmployeeConfig',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeConfig.emp_fkey')
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );
        //$conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_under);
        $conditions = array(
            'EmployeeDetails.status' => 1,
            'EmployeeConfig.type' => 'LAPPR',
            'EmployeeConfig.status' => 1,
            'OR' => array(
                'EmployeeConfig.policy_id' => $emp_under
            )
        );

        // Edited by Akshay on 7-2-2025
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeConfig"]['emp_fkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    public function addEmpToLeaveHierarchy()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];

        $parent_emp_pkey = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $data['id'] = 0;
                $data['type'] = 'LAPPR';
                $data['emp_fkey'] = $value;
                $data['policy_id'] = $parent_emp_pkey;
                $data['created_by'] = $this->Session->read('login_user_id');
                $this->EmployeeConfig->save($data);
            }
        }
    }

    public function removeEmpFromLeaveHierarchy()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];
        $parent_emp_pkey = (isset($_REQUEST['parent_emp_pkey']) ? $_REQUEST['parent_emp_pkey'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $condition['type'] = 'LAPPR';
                $condition['emp_fkey'] = $value;
                $condition['policy_id'] = $parent_emp_pkey;
                $curr_user_id = $this->Session->read('login_user_id');
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);
            }
        }
    }
    public function listemployeesforleavehierarchy()
    {

        $selected_emp_pkey = (isset($_REQUEST['emp_pkey']) ? $_REQUEST['emp_pkey'] : -1);
        $arr_employees_to_exclude = array($selected_emp_pkey);
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeConfig->find(
            "all",
            array(
                'fields' => 'emp_fkey',
                'conditions' => array(
                    'EmployeeConfig.type' => 'LAPPR',
                    'EmployeeConfig.status' => 1,
                    'EmployeeConfig.policy_id' => $selected_emp_pkey
                )
            )
        );
        $arr_emp_parents = $this->EmployeeConfig->find(
            "all",
            array(
                'fields' => 'policy_id',
                'conditions' => array(
                    'EmployeeConfig.type' => 'LAPPR',
                    'EmployeeConfig.status' => 1,
                    'EmployeeConfig.emp_fkey' => $selected_emp_pkey
                )
            )
        );
        foreach ($arr_emp as $val) {
            if (isset($val['EmployeeConfig']['emp_fkey'])) {
                $arr_employees_to_exclude[] = $val['EmployeeConfig']['emp_fkey'];
            }
        }
        foreach ($arr_emp_parents as $val) {
            if (isset($val['EmployeeConfig']['policy_id'])) {
                $arr_employees_to_exclude[] = $val['EmployeeConfig']['policy_id'];
            }
        }
        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            //            array(
            //                'table' => 'emp_config', 
            //                'alias' => 'EmployeeConfig', 
            //                'type' => 'LEFT', 
            //                'foreignKey' => false, 
            //                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeConfig.policy_id')
            //            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );

        $conditions = array(
            'EmployeeDetails.status' => 1,

            //            'OR'=>array(
            //                'EmployeeProfessionalDetails.attr1 = ""',
            //                'EmployeeProfessionalDetails.attr1 IS NULL'
            //            ),
            'NOT' => array(
                'EmployeeProfessionalDetails.emp_fkey' => $arr_employees_to_exclude
            )
        );

        // Edited by Akshay on 7-2-2025
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {
            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }
    public function listemployeesforleaveconfig()
    {

        $this->autoRender = false;
        $this->layout = null;

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name';
        $conditions  =  array(
            'EmployeeDetails.status' => 1
        );

        // Edited by Akshay on 7-2-2025
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $employeelist = $this->EmployeeDetails->find(
            "all",
            array(
                'fields' => $fields,
                'conditions' => $conditions
            )
        );

        $arr_employees = array();

        $arr_employees["rows"] = array();
        foreach ($employeelist as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name']);
            $resp_emp["rows"][] = $data;
        }

        echo json_encode($resp_emp);
    }

    //Edited by Akshay on 30-12-2023
    public function listemployeesinmultishift()
    {

        $shift = (isset($_REQUEST['shift']) ? $_REQUEST['shift'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $emp_in_shift = $this->EmployeeConfig->find(
            "list",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "type" => "MSHIFT",
                    "policy_id" => $shift,
                    "status" => 1
                )
            )
        );

        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';

        //$joins[] = array('table' => 'emp_proff', 'alias' => 'EmployeeProfessionalDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey'),);
        //$joins[] = array('table' => 'branches', 'alias' => 'Branches', 'type' => 'LEFT', 'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code'));
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );

        // $conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey' => $emp_in_shift, 'EmployeeProfessionalDetails.multishift' => 1);
        //Edited by Akshay on 10-9-2024
        $conditions = array(
            'EmployeeDetails.status' => 1,
            //'EmployeeDetails.emp_pkey' => $emp_in_shift,
            'EmployeeProfessionalDetails.multishift' => $shift
            //            'OR' => array(
            //                'EmployeeProfessionalDetails.multishift' => $shift,
            //               // 'EmployeeProfessionalDetails.day_time_seq' => $shift
            //            )
        );

        //End

        // Edited by Akshay on 7-2-2025
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            //debug($value);
            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    public function listemployeesformultishift()
    {

        $selected_shift_pkey = (isset($_REQUEST['shift']) ? $_REQUEST['shift'] : -1);
        // $arr_employees_to_exclude = array($selected_shift_pkey);
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //Edited by Akshay on 10-9-2024
        $arr_emp = $this->EmployeeConfig->find(
            "all",
            array(
                "fields" => "emp_fkey",
                "conditions" => array(
                    "OR" => array(
                        "EmployeeConfig.type" => "MSHIFT",
                        "EmployeeConfig.type" => "SHIFT",
                    ),
                    "EmployeeConfig.policy_id" => $selected_shift_pkey,
                    "EmployeeConfig.status" => 1
                )
            )
        );
        $arr_employees_to_exclude = array();
        //End


        foreach ($arr_emp as $val) {
            if (isset($val['EmployeeConfig']['emp_fkey'])) {
                $arr_employees_to_exclude[] = $val['EmployeeConfig']['emp_fkey'];
            }
        }

        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');

        $db = $this->EmployeeConfig->getDataSource();


        $fields = 'emp_pkey,CONCAT_WS(" ",first_name,last_name) as name,Designation.desig_name,Departments.dept_name,Units.branch_name';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
        );

        $conditions = array(
            'EmployeeDetails.status' => 1,
            'OR' => array(
                'EmployeeProfessionalDetails.multishift = "0"',
                'EmployeeProfessionalDetails.multishift IS NULL'
            ),
            'NOT' => array(
                'EmployeeProfessionalDetails.emp_fkey' => $arr_employees_to_exclude
            )
        );

        // Edited by Akshay on 7-2-2025
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
                $conditions['EmployeeDetails.branch_code'] = $is_ho;
            }
        }
        // End

        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeDetails->find(
            "all",
            array(
                'fields' => $fields,
                'joins' => $joins,
                'conditions' => $conditions
            )
        );
        $resp_emp["rows"] = array();
        foreach ($arr_emp as $key => $value) {

            $data['id'] = $value["EmployeeDetails"]['emp_pkey'];
            $data['data'] = array($value[0]['name'], $value["Units"]['branch_name'], $value["Designation"]['desig_name'], $value["Departments"]['dept_name']);
            $resp_emp["rows"][] = $data;
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    public function removeEmpFromMultiShift()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];

        $shift = (isset($_REQUEST['shift']) ? $_REQUEST['shift'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $condition['type'] = 'MSHIFT';
                $condition['emp_fkey'] = $value;
                $condition['policy_id'] = $shift;
                $curr_user_id = $this->Session->read('login_user_id');
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);
                //debug($condition);
            }
        }
    }


    public function addEmpToMultiShift()
    {
        $this->autoRender = false;
        $ids = $_REQUEST['id'];

        $shift = (isset($_REQUEST['shift']) ? $_REQUEST['shift'] : -1);
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');

        // Edited by Akshay on 1-1-2024
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

        $response = [];
        if ($ids) {
            $arr_ids = explode(",", $ids);
            foreach ($arr_ids as $key => $value) {
                $data['id'] = 0;
                $data['type'] = 'MSHIFT';
                $data['emp_fkey'] = $emp_fkey = $value;
                $data['policy_id'] = $shift;
                $data['created_by'] = $this->Session->read('login_user_id');

                $arr_shift = $this->EmployeeConfig->query("SELECT day_time_seq FROM emp_proff WHERE emp_fkey = '$emp_fkey';");
                $shift = isset($arr_shift[0]['emp_proff']['day_time_seq']) ? $arr_shift[0]['emp_proff']['day_time_seq'] : '';
                if ($shift != '') {
                    if ($this->EmployeeConfig->save($data)) {
                        $response = [
                            'status' => 'success',
                            'emp_fkey' => $value,
                            'message' => 'Empolyee Added To Selected Shift Policy'
                        ];
                    } else {
                        $response = [
                            'status' => 'error',
                            'emp_fkey' => $value,
                            'message' => 'Failed to add employee to multi-shift'
                        ];
                    }
                } else {
                    $response = [
                        'status' => 'error',
                        'emp_fkey' => $value,
                        'message' => 'Shift not allocated, please allocate primary shift policy.'
                    ];
                }
            }
        } else {
            $response = [
                'status' => 'error',
                'message' => 'No employee IDs provided'
            ];
        }

        echo json_encode($response);
    }

    //edited by athira on 06-11-2025
    public function listemployeesforpolicy()
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');

        $conditions = ['EmployeeDetails.status' => 1];

        $employees = $this->EmployeeDetails->find('all', [
            'fields' => [
                'EmployeeDetails.emp_pkey',
                'EmployeeDetails.first_name',
                'EmployeeDetails.middile_name',
                'EmployeeDetails.last_name',
                'Units.branch_name'
            ],
            'joins' => [
                [
                    'table' => 'branches',
                    'alias' => 'Units',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => ['EmployeeDetails.branch_code = Units.branch_code']
                ]
            ],
            'conditions' => $conditions
        ]);

        $arr_policies = ['rows' => []];

        foreach ($employees as $value) {
            $row = [];
            $row['id'] = $value['EmployeeDetails']['emp_pkey'];

            // Concatenate full name
            $fullName = trim(
                $value['EmployeeDetails']['first_name'] . ' ' .
                    $value['EmployeeDetails']['middile_name'] . ' ' .
                    $value['EmployeeDetails']['last_name']
            );

            $branchName = isset($value["Units"]["branch_name"]) ? $value["Units"]["branch_name"] : '';

            $row['data'] = array($fullName, $branchName);
            $arr_policies["rows"][] = $row;
        }

        // debug($arr_policies);
        echo json_encode($arr_policies);
        $this->autoRender = false;
    }


    // public function listshiftsforemployees() {
    //     $emp_fkey = isset($_REQUEST['employee']) ? $_REQUEST['employee'] : -1;
    //     $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
    //     $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');

    //     // Check if the employee has any allocated shifts
    //     $allocated = $this->DayTimeProcedures->query("
    //         SELECT ec.policy_id
    //         FROM emp_config ec
    //         WHERE ec.emp_fkey = $emp_fkey AND ec.status IN (1,2) 
    //     ");

    //     // Extract all day_time_seq values and filter out nulls
    // $hasAllocatedShifts = false;
    // foreach ($allocated as $row) {
    //     if (!empty($row['ec']['policy_id'])) {
    //         $hasAllocatedShifts = true;
    //         break;
    //     }
    // }

    // if (!$hasAllocatedShifts) {
    //     // Show all shifts
    //     $unallocatedShifts = $this->DayTimeProcedures->query("
    //         SELECT wdtp.day_time_seq, wdtp.day_time_desc
    //         FROM working_day_time_procedures as wdtp WHERE wdtp.active = 1
    //         ORDER BY wdtp.day_time_seq
    //     ");
    // } else {
    //     // Show only unallocated shifts
    //     $unallocatedShifts = $this->DayTimeProcedures->query("
    //         SELECT wdtp.day_time_seq, wdtp.day_time_desc
    //         FROM working_day_time_procedures as wdtp
    //         WHERE wdtp.active = 1 AND wdtp.day_time_seq NOT IN (
    //             SELECT ec.policy_id
    //             FROM emp_config ec
    //             WHERE ec.emp_fkey = $emp_fkey AND ec.policy_id IS NOT NULL AND ec.status IN (1,2)
    //         )
    //         ORDER BY wdtp.day_time_seq
    //     ");
    // }



    //     // Format the response
    //     $resp = ['rows' => []];
    //     foreach ($unallocatedShifts as $shift) {
    //         $row = [];
    //         $row['id'] = $shift['wdtp']['day_time_seq'];
    //         $row['data'] = [$shift['wdtp']['day_time_desc']];
    //         $resp['rows'][] = $row;
    //     }

    //     echo json_encode($resp);
    //     $this->autoRender = false;
    // }


    public function listshiftsforemployees()
    {
        $emp_fkey = isset($_REQUEST['employee']) ? $_REQUEST['employee'] : -1;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');

        // Check if the employee has any allocated shifts
        $allocated = $this->DayTimeProcedures->query("
        SELECT ec.policy_id
        FROM emp_config ec
        WHERE ec.emp_fkey = $emp_fkey AND ec.status IN (1,2) AND ec.type IN ('SHIFT', 'MSHIFT')
    ");

        // Extract all day_time_seq values and filter out nulls
        $hasAllocatedShifts = false;
        foreach ($allocated as $row) {
            if (!empty($row['ec']['policy_id'])) {
                $hasAllocatedShifts = true;
                break;
            }
        }

        if (!$hasAllocatedShifts) {
            // Show all shifts
            $unallocatedShifts = $this->DayTimeProcedures->query("
        SELECT wdtp.day_time_seq, wdtp.day_time_desc
        FROM working_day_time_procedures as wdtp WHERE wdtp.active = 1
        ORDER BY wdtp.day_time_seq
    ");
        } else {
            // Show only unallocated shifts
            $unallocatedShifts = $this->DayTimeProcedures->query("
        SELECT wdtp.day_time_seq, wdtp.day_time_desc
        FROM working_day_time_procedures as wdtp
        WHERE wdtp.active = 1 AND wdtp.day_time_seq NOT IN (
            SELECT ec.policy_id
            FROM emp_config ec
            WHERE ec.emp_fkey = $emp_fkey AND ec.policy_id IS NOT NULL AND ec.status IN (1,2) AND ec.type IN ('SHIFT', 'MSHIFT')
        )
        ORDER BY wdtp.day_time_seq
    ");
        }



        // Format the response
        $resp = ['rows' => []];
        foreach ($unallocatedShifts as $shift) {
            $row = [];
            $row['id'] = $shift['wdtp']['day_time_seq'];
            $row['data'] = [$shift['wdtp']['day_time_desc']];
            $resp['rows'][] = $row;
        }

        echo json_encode($resp);
        $this->autoRender = false;
    }


    // public function listshiftsinemployees() {
    //     $emp_fkey = isset($_REQUEST['employee']) ? $_REQUEST['employee'] : -1;
    //     $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
    //     $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');

    //     // Get shifts from emp_config where policy_id = day_time_seq
    //     $shifts = $this->DayTimeProcedures->query("
    //         SELECT wdtp.day_time_seq, wdtp.day_time_desc, ec.status
    //         FROM working_day_time_procedures as wdtp
    //         INNER JOIN emp_config ec ON ec.policy_id = wdtp.day_time_seq
    //         WHERE ec.emp_fkey = $emp_fkey AND ec.status IN (1, 2) AND wdtp.active = 1
    //         ORDER BY wdtp.day_time_seq
    //     ");

    //     $resp = ['rows' => []];
    //     foreach ($shifts as $shift) {
    //         $row = [];
    //         $row['id'] = $shift['wdtp']['day_time_seq'];
    //         $desc = $shift['wdtp']['day_time_desc'];

    //          // Highlight active shifts in green
    //         if ((int)$shift['ec']['status'] == 1) {
    //             $desc .= ' <span style="color: green; font-weight: bold;">(Primary Shift)</span>';
    //         }

    //         $row['data'] = [$desc];
    //         $resp['rows'][] = $row;
    //     }

    //     echo json_encode($resp);
    //     $this->autoRender = false;
    // }


    public function listshiftsinemployees()
    {
        $emp_fkey = isset($_REQUEST['employee']) ? $_REQUEST['employee'] : -1;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');

        // Get shifts from emp_config where policy_id = day_time_seq
        $shifts = $this->DayTimeProcedures->query("
        SELECT wdtp.day_time_seq, wdtp.day_time_desc, ec.status
        FROM working_day_time_procedures as wdtp
        INNER JOIN emp_config ec ON ec.policy_id = wdtp.day_time_seq
        WHERE ec.emp_fkey = $emp_fkey AND ec.status IN (1, 2) AND wdtp.active = 1 AND ec.type IN ('SHIFT', 'MSHIFT')
        ORDER BY wdtp.day_time_seq
    ");

        $resp = ['rows' => []];
        foreach ($shifts as $shift) {
            $row = [];
            $row['id'] = $shift['wdtp']['day_time_seq'];
            $desc = $shift['wdtp']['day_time_desc'];

            // Highlight active shifts in green
            if ((int)$shift['ec']['status'] == 1) {
                $desc .= ' <span style="color: green; font-weight: bold;">(Primary Shift)</span>';
            }

            $row['data'] = [$desc];
            $resp['rows'][] = $row;
        }

        echo json_encode($resp);
        $this->autoRender = false;
    }

    // public function addShiftToEmp()
    // {
    //     $this->autoRender = false;

    //     $emp_fkey = isset($_REQUEST['emp_fkey']) ? $_REQUEST['emp_fkey'] : null;
    //     $shift_ids = isset($_REQUEST['shift']) ? $_REQUEST['shift'] : '';

    //     $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //     $this->EditPunches->useDbConfig = $this->Session->read('ds');

    //     if (empty($emp_fkey) || empty($shift_ids)) {
    //         echo json_encode([
    //             'status' => 'error',
    //             'message' => 'Missing employee ID or shift IDs.'
    //         ]);
    //         return;
    //     }

    //     $shift_arr = explode(",", $shift_ids);
    //     $responses = [];

    //     // Check if a primary SHIFT already exists
    //     $primaryShift = $this->EmployeeConfig->find('first', [
    //         'conditions' => [
    //             'emp_fkey' => $emp_fkey,
    //             'type' => 'SHIFT',
    //             'status' => 1
    //         ],
    //         'fields' => ['id']
    //     ]);
    //     $hasPrimary = !empty($primaryShift);

    //     foreach ($shift_arr as $index => $shift) {
    //         $shift = trim($shift);
    //         $status = ($hasPrimary) ? 2 : 1;
    //         $type = ($hasPrimary) ? 'MSHIFT' : 'SHIFT';

    //         // 1. Check if this shift already exists (SHIFT or MSHIFT)
    //         $existing = $this->EmployeeConfig->find('first', [
    //             'conditions' => [
    //                 'emp_fkey' => $emp_fkey,
    //                 'policy_id' => $shift
    //             ],
    //             'fields' => ['id', 'status', 'type']
    //         ]);

    //         if (!empty($existing)) {
    //             $this->EmployeeConfig->id = $existing['EmployeeConfig']['id'];

    //             $newData = [
    //                 'status' => $status,
    //                 'modified_by' => $this->Session->read('login_user_id'),
    //                 'modification_date' => date('Y-m-d H:i:s')
    //             ];

    //             // Promote/demote type only if status changes to/from 1
    //             if ($existing['EmployeeConfig']['status'] != $status) {
    //                 $newData['type'] = ($status === 1) ? 'SHIFT' : 'MSHIFT';
    //             }

    //             $saved = $this->EmployeeConfig->save($newData);

    //             if ($status === 1 && $saved) {
    //                 $hasPrimary = true;
    //             }

    //             $responses[] = [
    //                 'status' => $saved ? 'success' : 'error',
    //                 'shift_id' => $shift,
    //                 'message' => $saved ? 'Existing shift updated.' : 'Failed to update existing shift.'
    //             ];
    //         } else {
    //             // 2. Insert new
    //             $data = [
    //                 'type' => $type,
    //                 'emp_fkey' => $emp_fkey,
    //                 'policy_id' => $shift,
    //                 'status' => $status,
    //                 'created_by' => $this->Session->read('login_user_id'),
    //                 'creation_date' => date('Y-m-d H:i:s')
    //             ];

    //             $saved = $this->EmployeeConfig->save($data);
    //             if ($status === 1 && $saved) {
    //                 $hasPrimary = true;
    //             }

    //             $responses[] = [
    //                 'status' => $saved ? 'success' : 'error',
    //                 'shift_id' => $shift,
    //                 'message' => $saved ? "New {$type} assigned with status {$status}." : 'Failed to assign new shift.'
    //             ];
    //         }
    //     }

    //     echo json_encode($responses);
    // }


    public function addShiftToEmp()
    {
        $this->autoRender = false;

        $emp_fkey = isset($_REQUEST['emp_fkey']) ? $_REQUEST['emp_fkey'] : null;
        $shift_ids = isset($_REQUEST['shift']) ? $_REQUEST['shift'] : '';

        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds'); // ✅ needed
        $this->EditPunches->useDbConfig = $this->Session->read('ds');

        if (empty($emp_fkey) || empty($shift_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Missing employee ID or shift IDs.'
            ]);
            return;
        }

        $shift_arr = explode(",", $shift_ids);
        $responses = [];

        // Check if a primary SHIFT already exists
        $primaryShift = $this->EmployeeConfig->find('first', [
            'conditions' => [
                'emp_fkey' => $emp_fkey,
                'type' => 'SHIFT',
                'status' => 1
            ],
            'fields' => ['id']
        ]);
        $hasPrimary = !empty($primaryShift);

        $curr_user_id = $this->Session->read('login_user_id');

        foreach ($shift_arr as $index => $shift) {
            $shift = trim($shift);
            // $status = ($hasPrimary) ? 2 : 1;
            // $type = ($hasPrimary) ? 'MSHIFT' : 'SHIFT';

            // edited by athira on 24-04-2026
            // Check if ANY OTHER shift is primary for this employee
            $otherPrimary = $this->EmployeeConfig->find('first', [
                'conditions' => [
                    'emp_fkey' => $emp_fkey,
                    'type' => 'SHIFT',
                    'status' => 1,
                    'NOT' => ['policy_id' => $shift]
                ],
                'fields' => ['id']
            ]);
            $hasOtherPrimary = !empty($otherPrimary);

            $status = ($hasOtherPrimary) ? 2 : 1;
            $type = ($hasOtherPrimary) ? 'MSHIFT' : 'SHIFT';
            // ended by athira on 24-04-2026

            // 1️⃣ Check if this shift already exists (SHIFT or MSHIFT)
            $existing = $this->EmployeeConfig->find('first', [
                'conditions' => [
                    'emp_fkey' => $emp_fkey,
                    'policy_id' => $shift
                ],
                'fields' => ['id', 'status', 'type']
            ]);

            if (!empty($existing)) {
                $this->EmployeeConfig->id = $existing['EmployeeConfig']['id'];

                $newData = [
                    'status' => $status,
                    'modified_by' => $curr_user_id,
                    'modification_date' => date('Y-m-d H:i:s')
                ];

                // Promote/demote type only if status changes to/from 1
                if ($existing['EmployeeConfig']['status'] != $status) {
                    $newData['type'] = ($status === 1) ? 'SHIFT' : 'MSHIFT';
                }

                $saved = $this->EmployeeConfig->save($newData);

                if ($status === 1 && $saved) {
                    // ✅ Update emp_proff when new primary shift assigned
                    $this->EmployeeProfessionalDetails->updateAll([
                        'EmployeeProfessionalDetails.day_time_seq' => "'" . $shift . "'",
                        'EmployeeProfessionalDetails.modified_by' => "'" . $curr_user_id . "'",
                        'EmployeeProfessionalDetails.modified_date' => 'NOW()'
                    ], [
                        'EmployeeProfessionalDetails.emp_fkey' => $emp_fkey
                    ]);
                    $hasPrimary = true;
                }

                $responses[] = [
                    'status' => $saved ? 'success' : 'error',
                    'shift_id' => $shift,
                    'message' => $saved ? 'Existing shift updated.' : 'Failed to update existing shift.'
                ];
            } else {
                // 2️⃣ Insert new shift record
                $data = [
                    'type' => $type,
                    'emp_fkey' => $emp_fkey,
                    'policy_id' => $shift,
                    'status' => $status,
                    'created_by' => $curr_user_id,
                    'creation_date' => date('Y-m-d H:i:s')
                ];

                $this->EmployeeConfig->create();
                $saved = $this->EmployeeConfig->save($data);

                if ($status === 1 && $saved) {
                    // ✅ Update emp_proff for newly created primary shift
                    $this->EmployeeProfessionalDetails->updateAll([
                        'EmployeeProfessionalDetails.day_time_seq' => "'" . $shift . "'",
                        'EmployeeProfessionalDetails.modified_by' => "'" . $curr_user_id . "'",
                        'EmployeeProfessionalDetails.modified_date' => 'NOW()'
                    ], [
                        'EmployeeProfessionalDetails.emp_fkey' => $emp_fkey
                    ]);
                    $hasPrimary = true;
                }

                $responses[] = [
                    'status' => $saved ? 'success' : 'error',
                    'shift_id' => $shift,
                    'message' => $saved ? "New {$type} assigned with status {$status}." : 'Failed to assign new shift.'
                ];
            }
        }

        echo json_encode($responses);
    }




    // public function removeShiftFromEmp()
    // {
    //     $this->autoRender = false;

    //     $empFkeys = isset($_REQUEST['emp_fkey']) ? $_REQUEST['emp_fkey'] : '';
    //     $shift = isset($_REQUEST['shift']) ? $_REQUEST['shift'] : -1;

    //     $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
    //     $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

    //     if (!empty($empFkeys) && $shift != -1) {
    //         $empIds = explode(",", $empFkeys);
    //         $curr_user_id = $this->Session->read('login_user_id');

    //         foreach ($empIds as $empId) {
    //             $empId = (int)$empId;
    //             $shift = (int)$shift;

    //             // Step 1: Check if the shift exists and get its type/status
    //             $currentShift = $this->EmployeeConfig->find('first', [
    //                 'conditions' => [
    //                     'emp_fkey' => $empId,
    //                     'policy_id' => $shift,
    //                     'type' => ['SHIFT', 'MSHIFT']
    //                 ],
    //                 'fields' => ['status', 'id', 'type']
    //             ]);

    //             if (!empty($currentShift)) {
    //                 $isPrimary = $currentShift['EmployeeConfig']['status'] == 1 && $currentShift['EmployeeConfig']['type'] === 'SHIFT';

    //                 // Step 2: Set status = 0 (soft delete shift)
    //                 $this->EmployeeConfig->updateAll([
    //                     'EmployeeConfig.status' => 0,
    //                     'EmployeeConfig.modified_by' => "'" . $curr_user_id . "'",
    //                     'EmployeeConfig.modification_date' => 'NOW()'
    //                 ], [
    //                     'EmployeeConfig.emp_fkey' => $empId,
    //                     'EmployeeConfig.policy_id' => $shift,
    //                     'EmployeeConfig.type' => ['SHIFT', 'MSHIFT'] // remove both types if they exist
    //                 ]);

    //                 // Step 3: If a primary SHIFT was removed, promote a MSHIFT
    //                 if ($isPrimary) {
    //                     $another = $this->EmployeeConfig->find('first', [
    //                         'conditions' => [
    //                             'emp_fkey' => $empId,
    //                             'status' => 2,
    //                             'type' => 'MSHIFT'
    //                         ],
    //                         'order' => ['modification_date DESC'], // most recent
    //                         'fields' => ['id']
    //                     ]);

    //                     if (!empty($another)) {
    //     // Promote the shift
    //     $this->EmployeeConfig->updateAll([
    //         'EmployeeConfig.status' => 1,
    //         'EmployeeConfig.type' => "'SHIFT'",
    //         'EmployeeConfig.modified_by' => "'" . $curr_user_id . "'",
    //         'EmployeeConfig.modification_date' => 'NOW()'
    //     ], [
    //         'EmployeeConfig.id' => $another['EmployeeConfig']['id']
    //     ]);

    //     // Manually update emp_proff (or EmployeeDetails) since trigger won’t fire on UPDATE
    //     $promotedShift = $this->EmployeeConfig->find('first', [
    //         'conditions' => ['id' => $another['EmployeeConfig']['id']],
    //         'fields' => ['policy_id']
    //     ]);

    //     if (!empty($promotedShift)) {
    //         $newShiftPolicyId = $promotedShift['EmployeeConfig']['policy_id'];

    //         $this->EmployeeProfessionalDetails->updateAll([
    //             'EmployeeProfessionalDetails.day_time_seq' => "'" . $newShiftPolicyId . "'",
    //             'EmployeeProfessionalDetails.modified_by' => "'" . $curr_user_id . "'",
    //             'EmployeeProfessionalDetails.modified_date' => 'NOW()'
    //         ], [
    //             'EmployeeProfessionalDetails.emp_fkey' => $empId
    //         ]);
    //     }
    // }

    //                 }
    //             }
    //         }
    //     }
    // }

    // edited by athira
    public function removeShiftFromEmp()
    {
        $this->autoRender = false;

        $empFkeys = isset($_REQUEST['emp_fkey']) ? $_REQUEST['emp_fkey'] : '';
        $shift = isset($_REQUEST['shift']) ? $_REQUEST['shift'] : -1;

        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

        if (!empty($empFkeys) && $shift != -1) {
            $empIds = explode(",", $empFkeys);
            $curr_user_id = $this->Session->read('login_user_id');

            foreach ($empIds as $empId) {
                $empId = (int)$empId;
                $shift = (int)$shift;

                // Step 1: Check if the shift exists and get its type/status
                $currentShift = $this->EmployeeConfig->find('first', [
                    'conditions' => [
                        'emp_fkey' => $empId,
                        'policy_id' => $shift,
                        'type' => ['SHIFT', 'MSHIFT']
                    ],
                    'fields' => ['status', 'id', 'type']
                ]);

                if (!empty($currentShift)) {
                    $isPrimary = $currentShift['EmployeeConfig']['status'] == 1 && $currentShift['EmployeeConfig']['type'] === 'SHIFT';

                    // Step 2: Set status = 0 (soft delete shift)
                    $this->EmployeeConfig->updateAll([
                        'EmployeeConfig.status' => 0,
                        'EmployeeConfig.modified_by' => "'" . $curr_user_id . "'",
                        'EmployeeConfig.modification_date' => 'NOW()'
                    ], [
                        'EmployeeConfig.emp_fkey' => $empId,
                        'EmployeeConfig.policy_id' => $shift,
                        'EmployeeConfig.type' => ['SHIFT', 'MSHIFT'] // remove both types if they exist
                    ]);

                    // Step 3: If a primary SHIFT was removed, promote a MSHIFT
                    // edited by athira on 24-04-2026
                    if ($isPrimary) {
                        $another = $this->EmployeeConfig->find('first', [
                            'conditions' => [
                                'emp_fkey' => $empId,
                                'status' => 2,
                                'type' => 'MSHIFT'
                            ],
                            'order' => ['modification_date DESC'], // most recent
                            'fields' => ['id']
                        ]);

                        if (!empty($another)) {
                            // Promote the shift
                            $this->EmployeeConfig->updateAll([
                                'EmployeeConfig.status' => 1,
                                'EmployeeConfig.type' => "'SHIFT'",
                                'EmployeeConfig.modified_by' => "'" . $curr_user_id . "'",
                                'EmployeeConfig.modification_date' => 'NOW()'
                            ], [
                                'EmployeeConfig.id' => $another['EmployeeConfig']['id']
                            ]);

                            // Manually update emp_proff (or EmployeeDetails) since trigger won’t fire on UPDATE
                            $promotedShift = $this->EmployeeConfig->find('first', [
                                'conditions' => ['id' => $another['EmployeeConfig']['id']],
                                'fields' => ['policy_id']
                            ]);

                            if (!empty($promotedShift)) {
                                $newShiftPolicyId = $promotedShift['EmployeeConfig']['policy_id'];

                                $this->EmployeeProfessionalDetails->updateAll([
                                    'EmployeeProfessionalDetails.day_time_seq' => "'" . $newShiftPolicyId . "'",
                                    'EmployeeProfessionalDetails.modified_by' => "'" . $curr_user_id . "'",
                                    'EmployeeProfessionalDetails.modified_date' => 'NOW()'
                                ], [
                                    'EmployeeProfessionalDetails.emp_fkey' => $empId
                                ]);
                            }
                        } else {
                            // No other shift found to promote, clear the current shift from emp_proff
                            $this->EmployeeProfessionalDetails->updateAll([
                                'EmployeeProfessionalDetails.day_time_seq' => "'0'",
                                'EmployeeProfessionalDetails.modified_by' => "'" . $curr_user_id . "'",
                                'EmployeeProfessionalDetails.modified_date' => 'NOW()'
                            ], [
                                'EmployeeProfessionalDetails.emp_fkey' => $empId
                            ]);
                        }
                    }
                    // ended by athira on 24-04-2026
                }
            }
        }
    }
    //end
}
