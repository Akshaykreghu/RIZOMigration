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
App::uses('CakeEmail', 'Network/Email');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class RegularisationController extends AppController
{

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Regularisation';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'CompanyContactInfo', 'EditPunches', 'EmployeeDetails', 'DbConfig', 'EditPunchesHist', 'AttendanceRegister', 'Units');
    public $components = array('DatatablesManagement', 'MasterdataManagement');

    /*
     * Dashboard landing view
     */

    public function index($emp_id = '')
    {
        $this->layout = null;

        $emp_pkeys = $this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $current_emp_array = $this->EmployeeDetails->query("SELECT emp_id from emp_details where `emp_pkey` = '$emp_pkeys' 
            and emp_details.status = 1 ");
        $emp_id = $current_emp_array[0]['emp_details']['emp_id'];

        $this->set('current_pkey', $emp_pkeys);
        $this->set('current_emp_id', $emp_id);

        //         $hierarchy = $this->EmployeeDetails->query("select emp_id,CONCAT_WS(' ',first_name,middile_name,last_name) as text
        //         from emp_details 
        //         left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)
        //         WHERE (`attr1` = '$emp_pkeys' OR `emp_pkey` = '$emp_pkeys') and emp_details.status = 1 ");


        //         $hierarchy = $this->EmployeeDetails->query("
        //     SELECT emp_id,
        //     CONCAT_WS(' ', first_name, middile_name, last_name) AS text
        //     FROM emp_details
        //     WHERE emp_pkey = '$emp_pkeys'
        //     AND status = 1
        // ");        
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $not_allowed_companies = [
            'KWMT',
            'ABSG',
            'MBCT',
            'DRRC',
            'SRTS',
            'MRBS',
            'DJIC',
            'STCL',
            'SHYD',
            'AGNG',
            'ESNP',
            'GTRA',
            'VGNN',
            'AYRK',
            'VGFS',
            'VSFS'
        ];

        if ($user_group == 2 && !in_array($company_code, $not_allowed_companies)) {

            // Only Logged In Employee
            $hierarchy = $this->EmployeeDetails->query("
        SELECT emp_id,
        CONCAT_WS(' ', first_name, middile_name, last_name) AS text
        FROM emp_details
        WHERE emp_pkey = '$emp_pkeys'
        AND status = 1
    ");
        } else {

            // Self + Subordinates
            $hierarchy = $this->EmployeeDetails->query("
        SELECT emp_id,
        CONCAT_WS(' ', first_name, middile_name, last_name) AS text
        FROM emp_details
        LEFT JOIN emp_proff ON (emp_proff.emp_fkey = emp_details.emp_pkey)
        WHERE (attr1 = '$emp_pkeys' OR emp_pkey = '$emp_pkeys')
        AND emp_details.status = 1
    ");
        }

        $SqlQuery = "SELECT * FROM `employee_regularaization` 
        WHERE empid = '" . $emp_id . "' AND att_date LIKE '%" . date("Y-m") . "%' AND approved = 'P' AND status = 1";
        $regularasation_data = $this->EmployeeDetails->query($SqlQuery);


        $emp_array = [];
        if ($hierarchy) {
            $obj = new stdClass();
            $obj->id = "";
            $obj->text = "";
            $emp_array[] = $obj;
            foreach ($hierarchy as $data) {
                $obj = new stdClass();
                $obj->id = $data['emp_details']['emp_id'];
                $obj->text = $data[0]['text'];
                $emp_array[] = $obj;
            }
        }
        $this->set('arr_employees', $emp_array);

        $arr_months = json_decode($this->getmonths());
        $this->set('arr_months', $arr_months);

        $this->set("regularasation_data", $regularasation_data);

        // $arr_employees = json_decode($this->requestAction("/ApiRequest/listemployees/" . $emp_pkeys));
        // debug($arr_employees);

        if (!empty($emp_id)) {
            $this->set('emp_id', $emp_id);
        }
    }

    public function hierarchyindex($emp_id = '')
    {
        $this->layout = null;

        $emp_pkeys = $this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $current_emp_array = $this->EmployeeDetails->query("SELECT emp_id from emp_details where `emp_pkey` = '$emp_pkeys' 
            and emp_details.status = 1 ");
        $empid = $current_emp_array[0]['emp_details']['emp_id'];

        $this->set('current_pkey', $emp_pkeys);
        $this->set('current_emp_id', $empid);


        $hierarchy = $this->EmployeeDetails->query("select emp_id,CONCAT_WS(' ',first_name,middile_name,last_name) as text
        from emp_details 
        left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)
        WHERE (`attr1` = '$emp_pkeys') and emp_details.status = 1 "); //  OR `emp_pkey` = '$emp_pkeys'

        $SqlQuery = "SELECT * FROM `employee_regularaization` 
        WHERE empid = '" . $empid . "' AND att_date LIKE '%" . date("Y-m") . "%' AND approved = 'P' AND status = 1";
        $regularasation_data = $this->EmployeeDetails->query($SqlQuery);


        $emp_array = [];
        if ($hierarchy) {
            $obj = new stdClass();
            $obj->id = "";
            $obj->text = "";
            $emp_array[] = $obj;
            foreach ($hierarchy as $data) {
                $obj = new stdClass();
                $obj->id = $data['emp_details']['emp_id'];
                $obj->text = $data[0]['text'];
                $emp_array[] = $obj;
            }
        }
        $this->set('arr_employees', $emp_array);

        $arr_months = json_decode($this->getmonths());
        $this->set('arr_months', $arr_months);

        $this->set("regularasation_data", $regularasation_data);

        // $arr_employees = json_decode($this->requestAction("/ApiRequest/listemployees/" . $emp_pkeys));
        // debug($arr_employees);

        if (!empty($emp_id)) {
            $this->set('emp_id', $emp_id);
        }
    }

    public function adminindex($emp_id = '')
    {
        $this->layout = null;

        // $emp_pkeys = $this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $employees = $this->EmployeeDetails->query("select emp_id,CONCAT_WS(' ',first_name,middile_name,last_name) as text
        from emp_details 
        left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)
        WHERE emp_details.status = 1 order by trim(first_name) ASC "); //  OR `emp_pkey` = '$emp_pkeys'

        $emp_array = [];
        if ($employees) {
            $obj = new stdClass();
            $obj->id = "";
            $obj->text = "";
            $emp_array[] = $obj;
            foreach ($employees as $data) {
                $obj = new stdClass();
                $obj->id = $data['emp_details']['emp_id'];
                $obj->text = $data[0]['text'];
                $emp_array[] = $obj;
            }
        }

        $branches = $this->EmployeeDetails->query("SELECT branch_name,branch_code FROM `branches` WHERE status = 1 order by trim(branch_name) ASC");

        $this->set('arr_employees', $emp_array);
        $this->set('branches', $branches);

        if (!empty($emp_id)) {
            $this->set('emp_id', $emp_id);
        }

        // $arr_months = json_decode($this->getmonths());
        // $this->set('arr_months', $arr_months);

        // $SqlQuery = "SELECT * FROM `employee_regularaization` 
        // WHERE empid = '" . $empid . "' AND att_date LIKE '%" . date("Y-m") . "%' AND approved = 'P' AND status = 1";
        // $regularasation_data = $this->EmployeeDetails->query($SqlQuery);

        // $this->set("regularasation_data", $regularasation_data);

        // $arr_employees = json_decode($this->requestAction("/ApiRequest/listemployees/" . $emp_pkeys));
        // debug($arr_employees);

    }

    public function adminindexnew($emp_id = '')
    {
        $this->layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $user_group = $this->Session->read('user_group');
        $emp_pkey = $this->Session->read('emp_fkey');
        $user_id_session = $this->Session->read('user_id');
        $user_id = !empty($user_id_session) ? $user_id_session : $emp_pkey;
        $feature_id = $this->Session->read('current_feature_id');
        $company_code = $this->Session->read('company_code');

        $context = $this->MasterdataManagement->getFeatureAccessContext();
        $not_allowed_companies = [
            'KWMT',
            'ABSG',
            'MBCT',
            'DRRC',
            'SRTS',
            'MRBS',
            'DJIC',
            'STCL',
            'SHYD',
            'AGNG',
            'ESNP',
            'GTRA',
            'VGNN',
            'AYRK',
            'VGFS',
            'VSFS'
        ];

        if ($user_group == 1) {
            /* Admin gets all branches */
            $branches = $this->MasterdataManagement->getBranchesForAll();
        } else if (
            $user_group == 2 &&
            !in_array(strtoupper($company_code), $not_allowed_companies) &&
            $context['has_access']
        ) {
            if ($context['is_hierarchy']) {
                /* Get hierarchy branches */
                $branches = $this->MasterdataManagement->getHierarchyBranches($emp_pkey);
            } else {
                /* Get allocated branches */
                $branches = $this->MasterdataManagement->getAllocatedBranches($user_id, $feature_id);
            }
        } else {
            /* Not in allocated/hierarchy list or not GLET/Group2 - Show own branch only */
            $branches = $this->MasterdataManagement->getOwnBranch($emp_pkey);
        }

        $branch_array = [];
        if (!empty($branches)) {
            foreach ($branches as $b) {
                $obj = new stdClass();
                $obj->id = $b['b']['branch_code'];
                $obj->text = $b['b']['branch_name'];
                $branch_array[] = $obj;
            }
        }

        $this->set('arr_branches', $branch_array);
        $this->set('is_ho', ($user_group == 1 || ($user_group == 2 && count($branch_array) > 1)) ? 1 : 0);
        $this->set('user_group', $user_group);

        // Edited by Athira on 31-05-2026
        if ($user_group == 1) {
            $employees = $this->EmployeeDetails->query("select emp_pkey,CONCAT(first_name,' (',emp_id,')') as text
            from emp_details
            WHERE status = 1 order by trim(first_name) ASC");
        } else {
            $employees = $this->EmployeeDetails->query("select emp_pkey,CONCAT(first_name,' (',emp_id,')') as text
            from emp_details
            WHERE branch_code = '" . (!empty($branch_array) ? $branch_array[0]->id : '') . "' and status = 1 order by trim(first_name) ASC LIMIT 500 ");
        }

        $emp_array = [];
        if ($employees) {
            $obj = new stdClass();
            $obj->id = "0";
            $obj->text = "All";
            $emp_array[] = $obj;
            foreach ($employees as $data) {
                $empdata = isset($data['emp_details']) ? $data['emp_details'] : array();
                $calcdata = isset($data[0]) ? $data[0] : array();
                if (isset($empdata['emp_pkey'])) {
                    $obj = new stdClass();
                    $obj->id = $empdata['emp_pkey'];
                    $obj->text = isset($calcdata['text']) ? $calcdata['text'] : $empdata['emp_pkey'];
                    $emp_array[] = $obj;
                }
            }
        }
        $this->set('arr_employees', $emp_array);
        // Ended by Athira 31-05-2026

        if (!empty($emp_id)) {
            $this->set('emp_id', $emp_id);
        }
    }

    public function hierarchybulkindex($emp_id = '')
    {
        $this->layout = null;

        $emp_pkeys = $this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');


        $employees = $this->EmployeeDetails->query("select emp_id,CONCAT_WS(' ',first_name,middile_name,last_name) as text
        from emp_details 
        left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)
        WHERE emp_proff.attr1 = '$emp_pkeys' and emp_details.status = 1 order by trim(first_name) ASC "); //  OR `emp_pkey` = '$emp_pkeys'

        $emp_array = [];
        if ($employees) {
            $obj = new stdClass();
            $obj->id = "";
            $obj->text = "";
            $emp_array[] = $obj;
            foreach ($employees as $data) {
                $obj = new stdClass();
                $obj->id = $data['emp_details']['emp_id'];
                $obj->text = $data[0]['text'];
                $emp_array[] = $obj;
            }
        }

        $branches = $this->EmployeeDetails->query("SELECT branch_name,branch_code FROM `branches` WHERE status = 1 order by trim(branch_name) ASC");

        $this->set('arr_employees', $emp_array);
        $this->set('branches', $branches);

        if (!empty($emp_id)) {
            $this->set('emp_id', $emp_id);
        }
    }

    public function hierarchy()
    {
        $this->layout = null;

        $arr_months = json_decode($this->getmonths());
        $this->set('arr_months', $arr_months);

        $arr_employees = json_decode($this->requestAction("/ApiRequest/listemployeesforhierarchy/"));
        $this->set('arr_employees', $arr_employees);
        $this->render('index');
    }

    public function employeeeditpunch()
    {
        $arr_months = json_decode($this->getmonths());
        $this->set('arr_months', $arr_months);
        $arr_employees = json_decode($this->requestAction("/ApiRequest/listemployees/"));
        $this->set('arr_employees', $arr_employees);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $emp_pkeys = $this->Session->read('emp_fkey');
        $emp_idss = $this->EmployeeDetails->query("select emp_id from emp_details where emp_pkey = '$emp_pkeys' ");
        if (!empty($emp_id)) {
            $this->set('emp_id', $emp_id);
        }
        $this->set('emp_pkeys', $emp_idss['0']['emp_details']['emp_id']);
        //        $this->set('emp_pkeys',$emp_pkeys);
    }
    public function listpunches($hierarchy = false)
    {
        $this->autoRender = FALSE;
        $arr_leave_status = array();
        $arr_type = array();
        $arr_session = array();
        $state = array();
        $leave_details = array();
        $arr_datess = array();
        $arr_outputs = array();
        $base_table = "emp_detail_timeattandance";

        $emp = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : 0;
        if ($emp == 0) {
            return;
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_id` = '$emp'");
        $emp_pkey = isset($arr_emp_pkey[0]['emp_details']['emp_pkey']) ? $arr_emp_pkey[0]['emp_details']['emp_pkey'] : 0;
        $branch_code = isset($arr_emp_pkey['0']['emp_details']['branch_code']) ? $arr_emp_pkey['0']['emp_details']['branch_code'] : '';
        $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
        // debug($monthdd);

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();
        $resp_mispunches["data"] = array();
        $resp_mispunch["out"] = array();

        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');

        $month = $monthdd;
        $month1 = $month . '-01';

        // Use att_start_end_fn to get cycle start and end dates - Edited by Antigravity
        $att_startdate_query = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate_query = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");

        $cycleStart = $att_startdate_query[0][0]['monthly_att_fromdate'];
        $cycleEnd = $att_enddate_query[0][0]['monthly_att_todate'];

        $arr_dates = array();
        $curr = strtotime($cycleStart);
        $last = strtotime($cycleEnd);
        while ($curr <= $last) {
            $arr_dates[] = date('Y-m-d', $curr);
            $curr = strtotime('+1 day', $curr);
        }

        $emp_id_val = $emp;
        if ($emp_pkey != 0) {
            $condition = "$base_table.emp_pkey = '$emp_pkey' and ";
            $emp = $emp_pkey;
        } else {
            $condition = '';
            $emp = NULL;
        }

        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $useracess = $this->EmployeeDetails->query("select * from user_access as Useraccess where user_fkey = '$emp_pkeys' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            if ($access == 'Y') {
                $emp_condition = "";
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
            echo json_encode($resp_mispunches);
            die();
        }

        if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
            $this->EmployeeDetails->query("SELECT time_duration_check_multishift('$cycleEnd', '$emp_pkey', '$branch_code')");
        } else {
            $this->EmployeeDetails->query("SELECT time_duration_check('$cycleEnd', '$emp_pkey', '$branch_code')");
        }

        $regularasation_data = [];
        if ($emp_pkey) {
            $current_emp_condition = "";
            if ($hierarchy) {
                $current_emp_condition = " AND approved_person  = " . $this->Session->read('emp_fkey');
            }
            $regularasation_data_temp = $this->EmployeeDetails->query("select employee_regularaization.* from 
            employee_regularaization 
            left join emp_details on (emp_details.emp_id = employee_regularaization.empid) 
            where employee_regularaization.status = 1 and employee_regularaization.approved IN ('P') 
            and emp_details.emp_pkey = " . $emp_pkey . $current_emp_condition . " AND att_date BETWEEN '$cycleStart' AND '$cycleEnd' order by att_date,C1");

            foreach ($regularasation_data_temp as $reg_data) {
                $regularasation_data[$reg_data['employee_regularaization']['att_date']][] = $reg_data['employee_regularaization'];
            }
        }

        $attendances = $this->EmployeeDetails->query("select empdetails.emp_pkey,empdetails.emp_id,empdetails.first_name,empdetails.last_name, 
			$base_table.*,wd.minuts_calc_perday,emp.emp_fkey,isdelete,emp.joining_date from $base_table 
			left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
			left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
			left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
			where $emp_condition $condition att_date BETWEEN '$cycleStart' AND '$cycleEnd' order by att_date
		");

        $arr_attendance_register = $this->EmployeeDetails->query("select count(*) as count from attendance_register where emp_fkey = $emp_pkey AND month_year = '" . date('Y-m', strtotime($cycleEnd)) . "' AND isdelete = 'Y'");
        $iseditable = isset($arr_attendance_register[0][0]['count']) ? $arr_attendance_register[0][0]['count'] : 0;

        $indexed_attendances = array();
        foreach ($attendances as $val) {
            $indexed_attendances[$val[$base_table]['att_date']] = $val;
        }

        // Fetch basic employee info for missing dates
        $emp_info_query = $this->EmployeeDetails->query("SELECT emp_details.emp_pkey, emp_details.emp_id, emp_details.first_name, emp_details.last_name, emp_proff.joining_date 
            FROM emp_details 
            LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_details.emp_pkey 
            WHERE emp_details.emp_id = '$emp_id_val'");
        $base_emp_info = isset($emp_info_query[0]) ? $emp_info_query[0] : array();

        $total_count = count($arr_dates);
        $dates_to_show = array_slice($arr_dates, $ofst, $limit);

        foreach ($dates_to_show as $date) {
            $val = isset($indexed_attendances[$date]) ? $indexed_attendances[$date] : null;

            if (!$val) {
                $val = array(
                    'empdetails' => isset($base_emp_info['emp_details']) ? $base_emp_info['emp_details'] : array('emp_pkey' => '', 'emp_id' => '', 'first_name' => '', 'last_name' => ''),
                    $base_table => array(
                        'att_date' => $date,
                        'att_in_time' => '',
                        'att_out_time' => '',
                        'duration' => '',
                        'present' => '',
                        'holiday' => '',
                        'weekoff' => '',
                        'others' => '',
                        'leaves' => '',
                        'isdelete' => 'Y',
                        'site_transactions_fkey' => 0
                    ),
                    'emp' => isset($base_emp_info['emp_proff']) ? $base_emp_info['emp_proff'] : array('joining_date' => '')
                );
            }

            $arr_output = array();
            $arr_output['emp_pkey'] = isset($val['empdetails']['emp_pkey']) ? $val['empdetails']['emp_pkey'] : '';
            $arr_output['emp_id'] = isset($val['empdetails']['emp_id']) ? $val['empdetails']['emp_id'] : '';
            $arr_output['leaves'] = isset($val[$base_table]['leaves']) ? $val[$base_table]['leaves'] : '';
            $arr_output['att_date'] = isset($val[$base_table]['att_date']) ? $val[$base_table]['att_date'] : '';
            $arr_output['att_in_time'] = isset($val[$base_table]['att_in_time']) ? $val[$base_table]['att_in_time'] : '';
            $arr_output['att_out_time'] = isset($val[$base_table]['att_out_time']) ? $val[$base_table]['att_out_time'] : '';
            $arr_output['duration'] = isset($val[$base_table]['duration']) ? $val[$base_table]['duration'] : '';
            $arr_output['site_transactions_fkey'] = isset($val[$base_table]['site_transactions_fkey']) ? $val[$base_table]['site_transactions_fkey'] : '';
            $arr_output['isdelete'] = isset($val[$base_table]['isdelete']) ? $val[$base_table]['isdelete'] : '';
            $arr_output['joining_date'] = isset($val['emp']['joining_date']) ? $val['emp']['joining_date'] : '';

            $status = '';
            $status_color = 'black';

            if (!empty($val[$base_table]['weekoff'])) {
                $status_color = "black";
            } else if (!empty($val[$base_table]['present'])) {
                if (in_array(strtoupper($val[$base_table]['present']), array('A/A', 'P/A', 'A/P'))) {
                    $status_color = 'red';
                } else if (strtoupper($val[$base_table]['present']) == 'P/P') {
                    $status_color = 'green';
                }
            } else {
                $status_color = "black";
            }

            $status = ($val[$base_table]['present'] ? $val[$base_table]['present'] : '') . ' ' .
                ($val[$base_table]['holiday'] ? $val[$base_table]['holiday'] : '') . ' ' .
                ($val[$base_table]['weekoff'] ? $val[$base_table]['weekoff'] : '') . ' ' .
                ($val[$base_table]['others'] ? $val[$base_table]['others'] : '');

            $arr_output['status'] = trim($status);
            $arr_output['status_color'] = $status_color;
            $arr_output['editable'] = !empty($iseditable) ? true : false;

            $arr_output['applied_attendance'] = false;
            $arr_output['applied_date'] = '';
            $arr_output['applied_in_time'] = '';
            $arr_output['applied_out_time'] = '';
            $arr_output['applied_duration'] = '';
            $arr_output['applied_status'] = '';
            $arr_output['applied_status_color'] = 'black';

            if ($regularasation_data) {
                if (isset($regularasation_data[$arr_output['att_date']]) && $regularasation_data[$arr_output['att_date']]) {
                    foreach ($regularasation_data[$arr_output['att_date']] as $reg) {
                        if ($reg['approved'] == 'P') {
                            $arr_output['applied_attendance'] = true;
                        }
                        $arr_output['applied_date'] = $reg['att_date'];
                        $date_log = date('Y-m-d H:i:s', strtotime($reg['LOGDATE'] . ' ' . $reg['LOGTIME']));
                        if (strtolower($reg['C1']) == "in") {
                            $arr_output['applied_in_time'] = $date_log;
                        } else {
                            $arr_output['applied_out_time'] = $date_log;
                        }
                    }
                }
            }

            if ($arr_output['att_in_time'] && $arr_output['applied_out_time']) {
                $diff = date_diff(new DateTime($arr_output['att_in_time']), new DateTime($arr_output['applied_out_time']));
                $hour = (int)$diff->format("%h");
                $minutes = (int)$diff->format("%i");
                $arr_output['duration'] = ($hour * 60) + $minutes;
            } else if ($arr_output['att_out_time'] && $arr_output['applied_in_time']) {
                $diff = date_diff(new DateTime($arr_output['att_out_time']), new DateTime($arr_output['applied_in_time']));
                $hour = (int)$diff->format("%h");
                $minutes = (int)$diff->format("%i");
                $arr_output['duration'] = ($hour * 60) + $minutes;
            }

            if ($arr_output['applied_in_time'] && $arr_output['applied_out_time']) {
                $diff = date_diff(new DateTime($arr_output['applied_in_time']), new DateTime($arr_output['applied_out_time']));
                $hour = (int)$diff->format("%h");
                $minutes = (int)$diff->format("%i");
                $arr_output['applied_duration'] = ($hour * 60) + $minutes;
                $arr_output['duration'] = ($hour * 60) + $minutes;
            }

            if ($hierarchy) {
                if (!$arr_output['applied_in_time'] && !$arr_output['applied_out_time']) {
                    $total_count--;
                    continue;
                }
            }

            $resp_mispunches["rows"][] = $arr_output;
        }

        $resp_mispunches["total"] = $total_count;
        $resp_mispunches["message"] = "Employee Attendance Retrieved Successfully ";
        $resp_mispunches["type"] = "success";
        echo json_encode($resp_mispunches);
    }

    // public function listpunches($hierarchy = false)
    // {
    //     $this->autoRender = FALSE;
    //     $arr_leave_status = array();
    //     $arr_type = array();
    //     $arr_session = array();
    //     $state = array();
    //     $leave_details = array();
    //     $arr_datess = array();
    //     $arr_outputs = array();
    //     $base_table = "emp_detail_timeattandance";

    //     $emp = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : 0;
    //     if ($emp == 0) {
    //         return;
    //     }
    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //     $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_id` = '$emp'");
    //     $emp_pkey = isset($arr_emp_pkey[0]['emp_details']['emp_pkey']) ? $arr_emp_pkey[0]['emp_details']['emp_pkey'] : 0;
    //     $branch_code = isset($arr_emp_pkey['0']['emp_details']['branch_code']) ? $arr_emp_pkey['0']['emp_details']['branch_code'] : '';
    //     $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
    //     // debug($monthdd);

    //     $limit = $_REQUEST['rows'];
    //     $page = $_REQUEST['page'];

    //     $ofst = ($page - 1) * $limit;

    //     $resp_mispunches = array();
    //     $resp_mispunches["rows"] = array();
    //     $resp_mispunches["data"] = array();
    //     $resp_mispunch["out"] = array();

    //     $this->DbConfig->useDbConfig = $this->Session->read('ds');
    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //     $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
    //     $company_code = $this->Session->read('company_code');
    //     $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
    //     $month = $monthdd;
    //     $yearmonth = $month . '-01';

    //     $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
    //     $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
    //     $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
    //     $arr_date_in_selectedmonth = range(1, $att_enddate);

    //     if ($att_startdate != 1) {
    //         $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
    //     } else {
    //         $arr_date_in_prevmonth = array();
    //     }

    //     if ($emp_pkey != 0) {
    //         $condition = "$base_table.emp_pkey = '$emp_pkey' and ";
    //         $emp = $emp_pkey;
    //     } else {
    //         $condition = '';
    //         $emp = NULL;
    //     }

    //     $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
    //     if ($this->Session->read('emp_fkey')) {
    //         $emp_pkeys = $this->Session->read('emp_fkey');
    //         $useracess = $this->EmployeeDetails->query("select * from user_access as Useraccess where user_fkey = '$emp_pkeys' and menu_id = '0' and active = 'Y'");
    //         $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
    //         if ($access == 'Y') {
    //             $emp_condition = ""; // "emp.attr1 = '$emp_pkeys' and ";
    //         } else {
    //             $emp_condition = "";
    //         }
    //     } else {
    //         $emp_condition = "";
    //     }
    //     //                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //     //		$attend = $this->EmployeeDetails->query("select time_duration_check('$yearmonth','$emp','')");
    //     //		
    //     if (!$shiftdetailed = $this->EmployeeDetails->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey')")) {
    //         $resp_mispunches["total"] = "0";
    //         $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
    //         $resp_mispunches["type"] = "danger";
    //         echo json_encode($resp_mispunches);
    //         //            return FALSE;
    //         die();
    //     }

    //     //debug($shiftdetailed);
    //     if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
    //         if (!$this->EmployeeDetails->query("SELECT time_duration_check_multishift('$yearmonth', '$emp_pkey', '$branch_code')")) {
    //             $resp_mispunches["total"] = "0";
    //             $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
    //             $resp_mispunches["type"] = "danger";
    //             echo json_encode($resp_mispunches);
    //             //                return false;
    //             die();
    //         }
    //     } else {
    //         if (!$this->EmployeeDetails->query("SELECT time_duration_check('$yearmonth', '$emp_pkey', '$branch_code')")) {
    //             $resp_mispunches["total"] = "0";
    //             $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
    //             $resp_mispunches["type"] = "danger";
    //             echo json_encode($resp_mispunches);
    //             //                return false;
    //             die();
    //         }
    //     }
    //     $attendances_count = $this->EmployeeDetails->query("select count(*) AS count from $base_table left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where $emp_condition $condition yearmonth = '$yearmonth' order by att_date ");
    //     $count = isset($attendances_count[0][0]['count']) ? $attendances_count[0][0]['count'] : 0;

    //     $regularasation_data = [];
    //     if ($emp_pkey) {

    //         $current_emp_condition = "";
    //         if ($hierarchy) {
    //             $current_emp_condition = " AND approved_person  = " . $this->Session->read('emp_fkey');
    //         }
    //         $regularasation_data_temp = $this->EmployeeDetails->query("select employee_regularaization.* from 
    //         employee_regularaization 
    //         left join emp_details on (emp_details.emp_id = employee_regularaization.empid) 
    //         where employee_regularaization.status = 1 and employee_regularaization.approved IN ('P') 
    //         and emp_details.emp_pkey = " . $emp_pkey . $current_emp_condition . " order by att_date,C1");
    //     }

    //     foreach ($regularasation_data_temp as $reg_data) {
    //         $regularasation_data[$reg_data['employee_regularaization']['att_date']][]  = $reg_data['employee_regularaization'];
    //     }


    //     $attendances = $this->EmployeeDetails->query("select empdetails.emp_pkey,empdetails.emp_id,empdetails.first_name,empdetails.last_name, 
    // 		$base_table.*,wd.minuts_calc_perday,emp.emp_fkey,isdelete,emp.joining_date from $base_table 
    // 		left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
    // 		left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
    // 		left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
    // 		where $emp_condition $condition yearmonth = '$yearmonth' order by att_date LIMIT $ofst, $limit
    // 	");

    //     //$leave_details=$this->AttendanceRegister->query("select emp_leave_transactions.Leavestatus,emp_leave_transactions.leave_date,emp_leave_transactions.leave_session,leaveentries.EMP_fkey, salary_head_items.occurance from emp_leave_transactions left join leaveentries on (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID) left join salary_head_items on (salary_head_items.salary_head_item_pkey = leaveentries.salary_head_item_fkey) where leaveentries.EMP_fkey = '$emp_pkey' and date_format(emp_leave_transactions.leave_date,'%Y-%m') = '$month' and emp_leave_transactions.Leavestatus in ('Approved' , 'Applied')");
    //     //$this->set('leave_details',$leave_details);

    //     //            foreach($leave_details as $val){
    //     //                $arr_datess= isset($val['emp_leave_transactions']['leave_date']) ? $val['emp_leave_transactions']['leave_date'] : '';
    //     //                $arr_leave_status= isset($val['emp_leave_transactions']['Leavestatus']) ? $val['emp_leave_transactions']['Leavestatus'] : '';
    //     //                $arr_type = isset($val['salary_head_items']['occurance']) ? $val['salary_head_items']['occurance'] : '';  
    //     //                $session = isset($val['emp_leave_transactions']['leave_session']) ? $val['emp_leave_transactions']['leave_session'] : '';
    //     //                if( $session == 1){
    //     //                $state= "First Half";
    //     //                }
    //     //
    //     // elseif($session == 2){
    //     //$state= "Second Half";
    //     //}
    //     //// if(isset($val['emp_leave_transactions']['leave_session']) ? $val['emp_leave_transactions']['leave_session'] : ''== 1)
    //     //else{
    //     //$state= "Full Day";
    //     //}
    //     //$arr_session = $state;
    //     //   $arr_output['leave_date'] =  $arr_datess;
    //     //            $arr_output['leave_status'] = $arr_leave_status;
    //     //            $arr_output['leave_type'] = $arr_type;
    //     //            $arr_output['leave_session'] = $arr_session;
    //     //            $resp_mispunches["rows"][]  = $arr_output;
    //     //
    //     //}

    //     $arr_attendance_register = $this->EmployeeDetails->query("select count(*) as count from attendance_register where emp_fkey = $emp_pkey AND month_year = '" . date('Y-m', strtotime($month)) . "' AND isdelete = 'Y'");
    //     $iseditable = isset($arr_attendance_register[0][0]['count']) ? $arr_attendance_register[0][0]['count'] : 0;

    //     $employee_attendance = array();
    //     // continue;
    //     foreach ($attendances as $val) {
    //         // foreach ($arr_outputs as $val){
    //         // 	debug($val);
    //         // }
    //         //$emppk = $val[$base_table]['emp_pkey'];
    //         //$employee_attendance[$emppk][] = $val;

    //         $arr_output['emp_pkey'] = isset($val['empdetails']['emp_pkey']) ? $val['empdetails']['emp_pkey'] : '';
    //         $arr_output['emp_id'] = isset($val['empdetails']['emp_id']) ? $val['empdetails']['emp_id'] : '';
    //         $arr_output['leaves'] = isset($val['emp_detail_timeattandance']['leaves']) ? $val['emp_detail_timeattandance']['leaves'] : '';
    //         $arr_output['att_date'] = isset($val[$base_table]['att_date']) ? $val[$base_table]['att_date'] : '';
    //         $arr_output['att_in_time'] = isset($val[$base_table]['att_in_time']) ? $val[$base_table]['att_in_time']/* date("h:i:s A", strtotime($val[$base_table]['att_in_time'])) */ : '';
    //         $arr_output['att_out_time'] = isset($val[$base_table]['att_out_time']) ? $val[$base_table]['att_out_time']/* date("h:i:s A", strtotime($val[$base_table]['att_out_time'])) */ : '';
    //         $arr_output['duration'] = isset($val[$base_table]['duration']) ? $val[$base_table]['duration'] : '';
    //         $arr_output['site_transactions_fkey'] = isset($val[$base_table]['site_transactions_fkey']) ? $val[$base_table]['site_transactions_fkey'] : '';
    //         $arr_output['isdelete'] = isset($val[$base_table]['isdelete']) ? $val[$base_table]['isdelete'] : '';
    //         $arr_output['joining_date'] = isset($val['emp']['joining_date']) ? $val['emp']['joining_date'] : '';


    //         //$arr_output['min_bfr_on_dutyughjyulkjyuty_cal_ot'] = isset($val[$base_table]['min_bfr_on_dutty_cal_ot'])?$val[$base_table]['min_bfr_on_dutty_cal_ot']:'';
    //         //$arr_output['min_aftr_off_dutty_cal_ot'] = isset($val[$base_table]['min_aftr_off_dutty_cal_ot'])?$val[$base_table]['min_aftr_off_dutty_cal_ot']:'';
    //         //$arr_output['ot_duration'] = isset($val[$base_table]['ot_duration'])?$val[$base_table]['ot_duration']:'';

    //         $status = '';
    //         $status_color = 'black';


    //         if (!empty($val[$base_table]['weekoff'])) {
    //             //$status = $val[$base_table]['weekoff'];
    //             $status_color = "black";
    //         } else if (!empty($val[$base_table]['present'])) {
    //             //$status = $val[$base_table]['present'];
    //             if (in_array(strtoupper($val[$base_table]['present']), array('A/A', 'P/A', 'A/P'))) {
    //                 $status_color = 'red';
    //             } else if (strtoupper($val[$base_table]['present']) == 'P/P') {
    //                 $status_color = 'green';
    //             }
    //         }/* else if(!empty($val[$base_table]['holiday'])){
    //           $status = $val[$base_table]['holiday'];
    //           $status_color = "blue";
    //           }else if(!empty($val[$base_table]['leaves'])){
    //           $status = $val[$base_table]['leaves'];
    //           $status_color = "black";
    //           }else if(!empty($val[$base_table]['others'])){
    //           $status = $val[$base_table]['others'];
    //           $status_color = "black";
    //           }else{
    //           $status = $val[$base_table]['others'];
    //           $status_color = "black";
    //           } */ else {
    //             $status_color = "black";
    //         }
    //         $status = $val[$base_table]['present'] . ' ' .
    //             $val[$base_table]['holiday'] . ' ' .
    //             //      $val[$base_table]['leaves'] . ' ' .
    //             $val[$base_table]['weekoff'] . ' ' .
    //             $val[$base_table]['others'];



    //         $arr_output['status'] = $status;
    //         // debug($arr_output);
    //         $arr_output['status_color'] = $status_color;

    //         $arr_output['editable'] = !empty($iseditable) ? true : false;


    //         $arr_output['applied_attendance'] = false;
    //         $arr_output['applied_date'] = '';
    //         $arr_output['applied_in_time'] = '';
    //         $arr_output['applied_out_time'] = '';
    //         $arr_output['applied_duration'] = '';
    //         $arr_output['applied_status'] = '';
    //         $arr_output['applied_status_color'] = 'black';

    //         // Regularisation Data
    //         // debug($regularasation_data);
    //         if ($regularasation_data) {
    //             if (isset($regularasation_data[$arr_output['att_date']]) && $regularasation_data[$arr_output['att_date']]) {
    //                 foreach ($regularasation_data[$arr_output['att_date']] as $reg) {
    //                     if ($reg['approved'] == 'P') {
    //                         $arr_output['applied_attendance'] = true;
    //                     }
    //                     $arr_output['applied_date'] = $reg['att_date'];
    //                     $date = date('Y-m-d H:i:s', strtotime($reg['LOGDATE'] . ' ' . $reg['LOGTIME']));
    //                     if ($reg['C1'] == "in" || $reg['C1'] == "IN") {
    //                         $arr_output['applied_in_time'] = $date;
    //                     } else {
    //                         $arr_output['applied_out_time'] = $date;
    //                     }
    //                 }
    //             }
    //         }

    //         if ($arr_output['att_in_time'] && $arr_output['applied_out_time']) { // Already in and applied out
    //             $diff = date_diff(new DateTime($arr_output['att_in_time']), new DateTime($arr_output['applied_out_time']));
    //             $hour = (int) $diff->format("%h");
    //             $minutes = (int) $diff->format("%h");
    //             $arr_output['duration'] = ($hour * 60) + $minutes;
    //         } else if ($arr_output['att_out_time'] && $arr_output['applied_in_time']) { // Already out and applied in
    //             $diff = date_diff(new DateTime($arr_output['att_out_time']), new DateTime($arr_output['applied_in_time']));
    //             $hour = (int) $diff->format("%h");
    //             $minutes = (int) $diff->format("%h");
    //             $arr_output['duration'] = ($hour * 60) + $minutes;
    //         }

    //         if ($arr_output['applied_in_time'] && $arr_output['applied_out_time']) { // applied in and applied out
    //             $diff = date_diff(new DateTime($arr_output['applied_in_time']), new DateTime($arr_output['applied_out_time']));
    //             $hour = (int) $diff->format("%h");
    //             $minutes = (int) $diff->format("%h");
    //             $arr_output['applied_duration'] = ($hour * 60) + $minutes;
    //             $arr_output['duration'] = ($hour * 60) + $minutes;
    //         }

    //         // if ($arr_output['applied_in_time'] || $arr_output['applied_out_time']) {
    //         //     $arr_output['applied_status'] = (($arr_output['applied_in_time']) ? 'P/' : 'A/') . (($arr_output['applied_out_time']) ? 'P' : 'A');

    //         //     if (in_array(strtoupper($arr_output['applied_status']), array('A/A', 'P/A', 'A/P'))) {
    //         //         $status_color = 'red';
    //         //     } else if (strtoupper($arr_output['applied_status']) == 'P/P') {
    //         //         $status_color = 'green';
    //         //     }
    //         //     $arr_output['applied_status_color'] = $status_color;
    //         // }

    //         if ($hierarchy) { // This is to hide all date fields which not having regularisation data. from hierarchy login.
    //             if (!$arr_output['applied_in_time'] && !$arr_output['applied_out_time']) {
    //                 // unset($arr_output);
    //                 $count--;
    //                 continue;
    //             }
    //         }

    //         $resp_mispunches["rows"][] = $arr_output;
    //         // $resp_mispunches["rows"][] = $arr_outputs;
    //     }
    //     //debug($resp_mispunches);
    //     $resp_mispunches["total"] = $count;
    //     $resp_mispunches["message"] = "Employee Attendance Retrieved Successfully ";
    //     $resp_mispunches["type"] = "success";
    //     echo json_encode($resp_mispunches);
    // }

    public function listregularization()
    {
        $this->autoRender = FALSE;
        $base_table = "employee_regularaization";

        $emp = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : 0;
        if ($emp == 0) {
            return;
        }
        $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_id` = '$emp'");
        $emp_pkey = isset($arr_emp_pkey[0]['emp_details']['emp_pkey']) ? $arr_emp_pkey[0]['emp_details']['emp_pkey'] : 0;
        $branch_code = isset($arr_emp_pkey['0']['emp_details']['branch_code']) ? $arr_emp_pkey['0']['emp_details']['branch_code'] : '';

        // debug($monthdd);

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();
        $resp_mispunches["data"] = array();
        $resp_mispunch["out"] = array();


        // Edited by Athira on 03-06-2026
        $month1 = $monthdd . '-01';
        $att_startdate_query = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate_query = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
        $cycleStart = $att_startdate_query[0][0]['monthly_att_fromdate'];
        $cycleEnd = $att_enddate_query[0][0]['monthly_att_todate'];

        $SqlQuery = " FROM `employee_regularaization`
        left join emp_details ed on(ed.emp_pkey = employee_regularaization.approved_person)
        WHERE empid = '" . $emp . "' AND employee_regularaization.approved NOT IN ('P') ";
        $SqlQuery .= " AND att_date BETWEEN '$cycleStart' AND '$cycleEnd' ";
        $SqlQuery .= " AND employee_regularaization.status IN (1,0) order by DATE(LOGDATE) asc, TIME(LOGTIME) asc ";
        // Ended by Athira on 03-06-2026

        //and employee_regularaization.C3 NOT REGEXP 'Attendance cannot be saved.'  

        $count_array = $this->EmployeeDetails->query("SELECT COUNT(*) as total " . $SqlQuery);
        $count = ($count_array) ? (isset($count_array[0][0]['total']) ? $count_array[0][0]['total'] : 1) : 1;

        $attendances = $this->EmployeeDetails->query("SELECT employee_regularaization.*,ed.first_name as approved_person_name " . $SqlQuery . " limit $limit  offset $ofst"); // LIMIT $ofst, $limit

        foreach ($attendances as $val) {
            $arr_output = [];
            $arr_output['id'] = $val['employee_regularaization']['id'];
            $arr_output['att_date'] = $val['employee_regularaization']['att_date'];
            $arr_output['empid'] = $val['employee_regularaization']['empid'];
            $arr_output['emp_pkey'] = $emp_pkey;
            $arr_output['C1'] = $val['employee_regularaization']['C1'];
            $arr_output['C3'] = $val['employee_regularaization']['C3'];
            $arr_output['LOGDATE'] = $val['employee_regularaization']['LOGDATE'];
            $arr_output['LOGTIME'] = $val['employee_regularaization']['LOGTIME'];
            $arr_output['approved'] = $val['employee_regularaization']['approved'];
            $arr_output['approved_person'] = $val['employee_regularaization']['approved_person'];
            $arr_output['remarks'] = $val['employee_regularaization']['remarks'];
            $arr_output['approved_person_name'] = ($val['ed']['approved_person_name']) ? $val['ed']['approved_person_name'] : 'Admin';
            $arr_output['status'] = $val['employee_regularaization']['status'];
            $resp_mispunches["rows"][] = $arr_output;
        }

        $resp_mispunches["total"] = $count;
        $resp_mispunches["message"] = "Employee Regularisation Retrieved Successfully ";
        $resp_mispunches["type"] = "success";
        echo json_encode($resp_mispunches);
    }
    public function listadminregularizationnew()
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $base_table = "employee_regularaization";

        $emp = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : '';
        $empBranch = isset($_REQUEST['empBranch']) ? $_REQUEST['empBranch'] : '';
        $regStatus = isset($_REQUEST['regStatus']) ? $_REQUEST['regStatus'] : '';
        $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');

        $user_group = $this->Session->read('user_group');
        $context = $this->MasterdataManagement->getFeatureAccessContext();
        $company_code = $this->Session->read('company_code');
        $condition = "";
        $not_allowed_companies = [
            'KWMT',
            'ABSG',
            'MBCT',
            'DRRC',
            'SRTS',
            'MRBS',
            'DJIC',
            'STCL',
            'SHYD',
            'AGNG',
            'ESNP',
            'GTRA',
            'VGNN',
            'AYRK',
            'VGFS',
            'VSFS'
        ];

        if ($user_group == 1) {
            /* Admin gets all records */
            $condition = "";
        } else if (
            $user_group == 2 &&
            !in_array(strtoupper($company_code), $not_allowed_companies) &&
            $context['has_access']
        ) {
            if ($context['is_hierarchy']) {
                $emp_pkey = $this->Session->read('emp_fkey');
                $condition = " AND emp_proff.attr1 = '$emp_pkey'";
            } else {
                $user_id_session = $this->Session->read('user_id');
                $user_id = !empty($user_id_session) ? $user_id_session : $this->Session->read('emp_fkey');
                $feature_id = $this->Session->read('current_feature_id');
                $condition = " AND eed.branch_code IN (
                    SELECT branch_fkey 
                    FROM user_feature_branch_access 
                    WHERE user_fkey = '$user_id' 
                    AND feature_fkey = '$feature_id' 
                    AND active = 'Y'
                )";
            }
        } else {
            /* Not in list or not GLET/Group2 - Show own branch only */
            $emp_pkey = $this->Session->read('emp_fkey');
            $own_branch_res = $this->MasterdataManagement->getOwnBranch($emp_pkey);
            $own_branch = !empty($own_branch_res) ? $own_branch_res[0]['b']['branch_code'] : '';
            $condition = " AND eed.branch_code = '$own_branch'";
        }

        if ($empBranch != '' && $empBranch != '0') {
            $condition .= " AND eed.branch_code = " . $this->EmployeeDetails->getDataSource()->value($empBranch);
        }

        // Ensure that if a specific employee is selected, we filter by that employee primary key immediately
        if ($emp != '' && $emp != '0') {
            $condition .= " AND eed.emp_pkey = " . $this->EmployeeDetails->getDataSource()->value($emp);
        }

        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT eed.emp_pkey, eed.emp_id FROM `emp_details` eed left join emp_proff on (emp_proff.emp_fkey = eed.emp_pkey) WHERE  eed.status = 1 " . $condition);
        // Edited by Athira on 31-05-2026
        if (!$arr_emp_pkey) {
            echo json_encode(['total' => 0, 'rows' => [], 'message' => 'No records found', 'type' => 'success']);
            return;
        }
        // Ended by Athira 31-05-2026

        $all_emp_ids = array();
        $all_emp_pkeys = array();
        foreach ($arr_emp_pkey as $v) {
            $data = isset($v['eed']) ? $v['eed'] : (isset($v['EmployeeDetails']) ? $v['EmployeeDetails'] : (isset($v['emp_details']) ? $v['emp_details'] : (isset($v[0]) ? $v[0] : array())));
            if (isset($data['emp_id'])) {
                $all_emp_ids[] = $data['emp_id'];
            }
            if (isset($data['emp_pkey'])) {
                $all_emp_pkeys[] = $data['emp_pkey'];
            }
        }



        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();



        // Edited by Athira on 03-06-2026
        $month1 = $monthdd . '-01';
        $att_startdate_query = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate_query = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
        $cycleStart = $att_startdate_query[0][0]['monthly_att_fromdate'];
        $cycleEnd = $att_enddate_query[0][0]['monthly_att_todate'];
        // Ended by Athira on 03-06-2026

        $SqlQuery = " FROM `employee_regularaization`
        left join emp_details eed on(eed.emp_id = employee_regularaization.empid)
        left join emp_proff on (eed.emp_pkey = emp_proff.emp_fkey)
        left join branches br on(eed.branch_code = br.branch_code)
        WHERE eed.status = 1 ";

        $SqlQuery .= ($all_emp_ids) ? " AND employee_regularaization.empid IN ('" . trim(implode("','", $all_emp_ids)) . "') " : "";
        $SqlQuery .= ($regStatus) ? " AND employee_regularaization.approved IN ('" . $regStatus . "') " : "";

        // Edited by Athira on 03-06-2026
        $SqlQuery .= " AND employee_regularaization.att_date BETWEEN '$cycleStart' AND '$cycleEnd' ";
        // Ended by Athira on 03-06-2026
        $SqlQuery .= " AND employee_regularaization.status IN (1,0) order by DATE(LOGDATE) asc, eed.emp_id asc, TIME(LOGTIME) asc";

        $count_array = $this->EmployeeDetails->query("SELECT COUNT(*) as total " . $SqlQuery);
        $count = ($count_array) ? (isset($count_array[0][0]['total']) ? $count_array[0][0]['total'] : 1) : 1;

        $approved_person_query = " (SELECT ed.first_name FROM emp_details ed WHERE ed.emp_pkey = employee_regularaization.approved_person LIMIT 1) ";
        $attendances = $this->EmployeeDetails->query("SELECT employee_regularaization.*,$approved_person_query as approved_person_name,eed.first_name,eed.middile_name,eed.last_name,br.branch_name,emp_proff.emp_company_id " . $SqlQuery . " LIMIT $ofst, $limit ");

        foreach ($attendances as $val) {
            $arr_output = [];
            $arr_output['id'] = $val['employee_regularaization']['id'];
            $arr_output['att_date'] = $val['employee_regularaization']['att_date'];
            $arr_output['empid'] = $val['employee_regularaization']['empid'];
            $arr_output['C1'] = $val['employee_regularaization']['C1'];
            $arr_output['C3'] = $val['employee_regularaization']['C3'];
            $arr_output['LOGDATE'] = $val['employee_regularaization']['LOGDATE'];
            $arr_output['LOGTIME'] = $val['employee_regularaization']['LOGTIME'];
            $arr_output['approved'] = $val['employee_regularaization']['approved'];
            $arr_output['remarks'] = $val['employee_regularaization']['remarks'];
            $arr_output['approved_person_name'] = ($val[0]['approved_person_name']) ? $val[0]['approved_person_name'] : 'Admin';
            $arr_output['status'] = $val['employee_regularaization']['status'];
            $arr_output['first_name'] = $val['eed']['first_name'];
            $arr_output['middile_name'] = $val['eed']['middile_name'];
            $arr_output['last_name'] = $val['eed']['last_name'];
            $arr_output['branch_name'] = $val['br']['branch_name'];
            $arr_output['emp_company_id'] = $val['emp_proff']['emp_company_id'];

            if ($arr_output['approved'] == 'P' && $arr_output['status'] == '0') {
                $count--;
                continue;
            }
            $resp_mispunches["rows"][] = $arr_output;
        }

        $resp_mispunches["total"] = $count;
        $resp_mispunches["message"] = "Retrieved Successfully ";
        $resp_mispunches["type"] = "success";
        echo json_encode($resp_mispunches);
    }

    public function listadminregularization()
    {
        $this->autoRender = FALSE;
        $base_table = "employee_regularaization";

        $emp = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : '';
        $empBranch = isset($_REQUEST['empBranch']) ? $_REQUEST['empBranch'] : '';
        $regStatus = isset($_REQUEST['regStatus']) ? $_REQUEST['regStatus'] : '';
        // if ($emp == 0) {
        //     return;
        // }
        $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
        $condition = "";
        $condition .= ($emp) ? " AND `emp_id` = '$emp' " : "";
        $condition .= ($empBranch) ? " AND branch_code = '" . $empBranch . "'" : "";

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE  status = 1 " . $condition);
        if (!$arr_emp_pkey) {
            return;
        }

        $all_emp_ids = array_column((array_column($arr_emp_pkey, 'emp_details')), 'emp_id');
        $all_emp_pkeys = array_column((array_column($arr_emp_pkey, 'emp_details')), 'emp_pkey');

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();
        $resp_mispunches["data"] = array();
        $resp_mispunch["out"] = array();

        // Edited by Athira on 03-06-2026
        $month1 = $monthdd . '-01';
        $att_startdate_query = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate_query = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
        $cycleStart = $att_startdate_query[0][0]['monthly_att_fromdate'];
        $cycleEnd = $att_enddate_query[0][0]['monthly_att_todate'];

        $SqlQuery = " FROM `employee_regularaization`
        left join emp_details eed on(eed.emp_id = employee_regularaization.empid)
        left join emp_proff on (eed.emp_pkey = emp_proff.emp_fkey)
        left join branches br on(eed.branch_code = br.branch_code)
        WHERE eed.status = 1 ";

        $SqlQuery .= ($all_emp_ids) ? " AND employee_regularaization.empid IN (" . trim(implode(',', $all_emp_ids)) . ") " : "";
        $SqlQuery .= ($regStatus) ? " AND employee_regularaization.approved IN ('" . $regStatus . "') " : "";

        $SqlQuery .= " AND employee_regularaization.att_date BETWEEN '$cycleStart' AND '$cycleEnd' ";
        // Ended by Athira on 03-06-2026
        $SqlQuery .= " AND employee_regularaization.status IN (1,0) order by DATE(LOGDATE) asc, eed.emp_id asc, TIME(LOGTIME) asc";

        $count_array = $this->EmployeeDetails->query("SELECT COUNT(*) as total " . $SqlQuery);
        $count = ($count_array) ? (isset($count_array[0][0]['total']) ? $count_array[0][0]['total'] : 1) : 1;

        $approved_person_query = " (SELECT ed.first_name FROM emp_details ed WHERE ed.emp_pkey = employee_regularaization.approved_person LIMIT 1) ";

        $attendances = $this->EmployeeDetails->query("SELECT employee_regularaization.*,$approved_person_query as approved_person_name,eed.first_name,eed.middile_name,eed.last_name,br.branch_name,emp_proff.emp_company_id " . $SqlQuery . " LIMIT $ofst, $limit "); // limit $limit  offset $ofst

        foreach ($attendances as $val) {
            $arr_output = [];
            $arr_output['id'] = $val['employee_regularaization']['id'];
            $arr_output['att_date'] = date('d-m-Y', strtotime($val['employee_regularaization']['att_date']));
            $arr_output['empid'] = $val['employee_regularaization']['empid'];
            // $arr_output['emp_pkey']             = $emp_pkey;
            $arr_output['C1'] = $val['employee_regularaization']['C1'];
            $arr_output['C3'] = $val['employee_regularaization']['C3'];
            $arr_output['LOGDATE'] = date('d-m-Y', strtotime($val['employee_regularaization']['LOGDATE']));
            $arr_output['LOGTIME'] = $val['employee_regularaization']['LOGTIME'];
            $arr_output['approved'] = $val['employee_regularaization']['approved'];
            $arr_output['approved_person'] = $val['employee_regularaization']['approved_person'];
            $arr_output['remarks'] = $val['employee_regularaization']['remarks'];
            $arr_output['approved_person_name'] = ($val[0]['approved_person_name']) ? $val[0]['approved_person_name'] : 'Admin';
            $arr_output['status'] = $val['employee_regularaization']['status'];

            $arr_output['first_name'] = $val['eed']['first_name'];
            $arr_output['middile_name'] = $val['eed']['middile_name'];
            $arr_output['last_name'] = $val['eed']['last_name'];
            $arr_output['branch_name'] = $val['br']['branch_name'];
            $arr_output['emp_company_id'] = $val['emp_proff']['emp_company_id'];

            // This is to avoid pending inactive regularisations. by Arul on 12-11-22
            if ($arr_output['approved'] == 'P' && $arr_output['status'] == '0') {
                $count--;
                continue;
            }
            // Ends here

            $resp_mispunches["rows"][] = $arr_output;
        }

        $resp_mispunches["total"] = $count;
        $resp_mispunches["message"] = "Employee Regularisation Retrieved Successfully ";
        $resp_mispunches["type"] = "success";
        echo json_encode($resp_mispunches);
    }

    public function listhierarchyregularization()
    {
        $this->autoRender = FALSE;
        $base_table = "employee_regularaization";
        $hierarchy_person = $this->Session->read('emp_fkey');

        $emp = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : '';
        $empBranch = isset($_REQUEST['empBranch']) ? $_REQUEST['empBranch'] : '';
        $regStatus = isset($_REQUEST['regStatus']) ? $_REQUEST['regStatus'] : '';
        // if ($emp == 0) {
        //     return;
        // }
        $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
        $condition = "";
        $condition .= ($emp) ? " AND `emp_id` = '$emp' " : "";
        $condition .= ($empBranch) ? " AND emp_details.branch_code = '" . $empBranch . "'" : "";

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $employee_query = "SELECT * FROM `emp_details` 
        LEFT JOIN emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) 
        WHERE  status = 1 AND emp_proff.attr1 = $hierarchy_person " . $condition;

        $arr_emp_pkey = $this->EmployeeDetails->query($employee_query);
        if (!$arr_emp_pkey) {
            $resp_mispunches = array();
            $resp_mispunches["rows"] = array();
            $resp_mispunches["data"] = array();
            $resp_mispunches["total"] = 0;
            $resp_mispunches["message"] = "Employee Regularisation Retrieved Successfully ";
            $resp_mispunches["type"] = "success";
            echo json_encode($resp_mispunches);
            return;
            die();
        }

        $all_emp_ids = array_column((array_column($arr_emp_pkey, 'emp_details')), 'emp_id');
        $all_emp_pkeys = array_column((array_column($arr_emp_pkey, 'emp_details')), 'emp_pkey');

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();
        $resp_mispunches["data"] = array();
        $resp_mispunch["out"] = array();

        // Edited by Athira on 03-06-2026
        $month1 = $monthdd . '-01';
        $att_startdate_query = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate_query = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
        $cycleStart = $att_startdate_query[0][0]['monthly_att_fromdate'];
        $cycleEnd = $att_enddate_query[0][0]['monthly_att_todate'];

        $SqlQuery = " FROM `employee_regularaization`
        left join emp_details eed on(eed.emp_id = employee_regularaization.empid)
        left join emp_proff on (eed.emp_pkey = emp_proff.emp_fkey)
        left join branches br on(eed.branch_code = br.branch_code)
        WHERE eed.status = 1 ";

        $SqlQuery .= ($all_emp_ids) ? " AND employee_regularaization.empid IN (" . trim(implode(',', $all_emp_ids)) . ") " : "";
        $SqlQuery .= ($regStatus) ? " AND employee_regularaization.approved IN ('" . $regStatus . "') " : "";

        $SqlQuery .= " AND employee_regularaization.att_date BETWEEN '$cycleStart' AND '$cycleEnd' ";
        // Ended by Athira on 03-06-2026
        $SqlQuery .= " AND employee_regularaization.status IN (1,0) order by DATE(LOGDATE) asc, eed.emp_id asc, TIME(LOGTIME) asc";



        $count_array = $this->EmployeeDetails->query("SELECT COUNT(*) as total " . $SqlQuery);
        $count = ($count_array) ? (isset($count_array[0][0]['total']) ? $count_array[0][0]['total'] : 1) : 1;

        $approved_person_query = " (SELECT ed.first_name FROM emp_details ed WHERE ed.emp_pkey = employee_regularaization.approved_person LIMIT 1) ";

        $attendances = $this->EmployeeDetails->query("SELECT employee_regularaization.*,$approved_person_query as approved_person_name,eed.first_name,eed.middile_name,eed.last_name,br.branch_name,emp_proff.emp_company_id " . $SqlQuery . " LIMIT $ofst, $limit "); // limit $limit  offset $ofst

        foreach ($attendances as $val) {
            $arr_output = [];
            $arr_output['id'] = $val['employee_regularaization']['id'];
            $arr_output['att_date'] = $val['employee_regularaization']['att_date'];
            $arr_output['empid'] = $val['employee_regularaization']['empid'];
            // $arr_output['emp_pkey']             = $emp_pkey;
            $arr_output['C1'] = $val['employee_regularaization']['C1'];
            $arr_output['C3'] = $val['employee_regularaization']['C3'];
            $arr_output['LOGDATE'] = $val['employee_regularaization']['LOGDATE'];
            $arr_output['LOGTIME'] = $val['employee_regularaization']['LOGTIME'];
            $arr_output['approved'] = $val['employee_regularaization']['approved'];
            $arr_output['approved_person'] = $val['employee_regularaization']['approved_person'];
            $arr_output['remarks'] = $val['employee_regularaization']['remarks'];
            $arr_output['approved_person_name'] = ($val[0]['approved_person_name']) ? $val[0]['approved_person_name'] : 'Admin';
            $arr_output['status'] = $val['employee_regularaization']['status'];

            $arr_output['first_name'] = $val['eed']['first_name'];
            $arr_output['middile_name'] = $val['eed']['middile_name'];
            $arr_output['last_name'] = $val['eed']['last_name'];
            $arr_output['branch_name'] = $val['br']['branch_name'];
            $arr_output['emp_company_id'] = $val['emp_proff']['emp_company_id'];

            // This is to avoid pending inactive regularisations. by Arul on 12-11-22
            if ($arr_output['approved'] == 'P' && $arr_output['status'] == '0') {
                $count--;
                continue;
            }
            // Ends here

            $resp_mispunches["rows"][] = $arr_output;
        }

        $resp_mispunches["total"] = $count;
        $resp_mispunches["message"] = "Employee Regularisation Retrieved Successfully ";
        $resp_mispunches["type"] = "success";
        echo json_encode($resp_mispunches);
    }

    public function editpunch($att_date = '', $emp_id = '', $site_t_fkey = '', $att_in_time = '', $att_out_time = '')
    {
        $this->layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        // added by nimisha rajesh on 30/05/2019 issue - new button showing in edit puches when emp = heirarchy

        $emp_fkey = $this->Session->read('emp_fkey');
        $empid = $this->EmployeeDetails->query("SELECT `emp_id` FROM `emp_details` WHERE `emp_pkey` = '$emp_fkey'");
        $id = isset($empid['0']['emp_details']['emp_id']) ? $empid['0']['emp_details']['emp_id'] : '';
        //        debug($id);

        // only logged person can edit his attendance. by Arul on 12-10-22
        if ($emp_id == $id) {
            $shownewbutton = 'yes';
        } else {
            $shownewbutton = 'no';
        }
        $this->set('shownewbutton', $shownewbutton);

        // End

        $site_detailss = array();
        if (($site_t_fkey != 0)) {
            $site_detailss = $this->EmployeeDetails->query("select * from site_transactions left join designation on (designation.id = site_transactions.designation_id) left join site on (site.site_pkey = site_transactions.site_fkey) where site_transactions_pkey = '$site_t_fkey' ");
        }
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $useracess = $this->EmployeeDetails->query("select * from user_access as Useraccess where user_fkey = '$emp_pkeys' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            if ($access == 'Y') {
                $empmode = 1; //Hierarchy
            } else {
                $empmode = 0; //employee
            }
        } else {
            $empmode = 2; //admin
        }
        $this->set('empmode', $empmode);
        $this->set('att_date', $att_date);
        $this->set('emp_id', $emp_id);
        $this->set('att_in_time', $att_in_time);
        $this->set('site_detailss', $site_detailss);
        $this->set('att_out_time', $att_out_time);
    }

    public function viewattendancedetails($att_date = '', $emp_id = '', $site_t_fkey = '', $att_in_time = '', $att_out_time = '')
    {
        $this->layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        // added by nimisha rajesh on 30/05/2019 issue - new button showing in edit puches when emp = heirarchy

        $emp_fkey = $this->Session->read('emp_fkey');
        $empid = $this->EmployeeDetails->query("SELECT `emp_id` FROM `emp_details` WHERE `emp_pkey` = '$emp_fkey'");
        $id = isset($empid['0']['emp_details']['emp_id']) ? $empid['0']['emp_details']['emp_id'] : '';
        //        debug($id);

        // only logged person can edit his attendance. by Arul on 12-10-22
        // if ($emp_id == $id) {
        //     $shownewbutton = 'yes';
        // } else {
        //     $shownewbutton = 'no';
        // }

        $shownewbutton = 'no';
        $this->set('shownewbutton', $shownewbutton);

        // End

        $site_detailss = array();
        if (($site_t_fkey != 0)) {
            $site_detailss = $this->EmployeeDetails->query("select * from site_transactions left join designation on (designation.id = site_transactions.designation_id) left join site on (site.site_pkey = site_transactions.site_fkey) where site_transactions_pkey = '$site_t_fkey' ");
        }
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $useracess = $this->EmployeeDetails->query("select * from user_access as Useraccess where user_fkey = '$emp_pkeys' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            if ($access == 'Y') {
                $empmode = 1; //Hierarchy
            } else {
                $empmode = 0; //employee
            }
        } else {
            $empmode = 2; //admin
        }
        $this->set('empmode', $empmode);
        $this->set('att_date', $att_date);
        $this->set('emp_id', $emp_id);
        $this->set('att_in_time', $att_in_time);
        $this->set('site_detailss', $site_detailss);
        $this->set('att_out_time', $att_out_time);
    }

    public function listpunchesbydate($shownewbutton = "")
    {
        $this->autoRender = FALSE;

        $employee = isset($_REQUEST['empid']) ? $_REQUEST['empid'] : 0;
        $att_date = isset($_REQUEST['att_date']) ? $_REQUEST['att_date'] : '';
        $att_in_time = isset($_REQUEST['att_in_time']) ? $_REQUEST['att_in_time'] : '';
        $att_out_time = isset($_REQUEST['att_out_time']) ? $_REQUEST['att_out_time'] : '';
        $includeinactive = isset($_REQUEST['includeinactive']) ? $_REQUEST['includeinactive'] : '';

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;
        $count = 0;
        $i = 0;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();

        $temp_array = array();
        $sorting_array = array();

        // Edit Punch Attendance section
        if ($shownewbutton && $shownewbutton != "no") {
            if ($includeinactive && $includeinactive == 'Y') {
                $attendance_condition['status'] = array('Y', 'N');
            } else {
                $attendance_condition['status'] = array('Y');
            }
            if ($employee) {
                $attendance_condition['emp_id'] = $employee;
            }
            if ($att_date) {
                if (!empty($att_in_time) && !empty($att_out_time)) {
                    $attendance_condition[] = "DATE_FORMAT(LOGDATE,'%Y-%m-%d') BETWEEN '$att_in_time' AND '$att_out_time'";
                } else {
                    $attendance_condition['DATE_FORMAT(LOGDATE,"%Y-%m-%d")'] = $att_date;
                }
            }

            if ($employee) {
                $this->EditPunches->useDbConfig = $this->Session->read('ds');
                $count = $this->EditPunches->find("count", array('conditions' => $attendance_condition));
                $arr_mispunches = $this->EditPunches->find(
                    "all",
                    array(
                        'conditions' => $attendance_condition,
                        'order' => array(
                            'EditPunches.LOGDATE'
                        ),
                        'limit' => intval($limit),
                        'offset' => intval($ofst)
                    )
                );

                foreach ($arr_mispunches as $key => $value) {
                    // $resp_mispunches["rows"][$i] = $value["EditPunches"];
                    // $resp_mispunches["rows"][$i]['approved_person_name'] = "";

                    $temp_array["rows"][$i] = $value["EditPunches"];
                    $temp_array["rows"][$i]['approved_person_name'] = "";
                    $temp_array["rows"][$i]['attendance_type'] = "existing";

                    $sorting_array[$i] = date("Y-m-d H:i:s", strtotime($temp_array["rows"][$i]['LOGDATE']));

                    $i++;
                }
            }
        }

        //////// END

        $condition = " AND employee_regularaization.status = 1 AND employee_regularaization.approved IN ('P') "; // AND employee_regularaization.approved IN ('A','P') 
        if ($includeinactive && $includeinactive == 'Y') {
            $condition = " AND employee_regularaization.status IN (1, 0) AND employee_regularaization.approved IN ('R','P')  "; // AND employee_regularaization.approved IN ('A','R','P') 
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_id` = '$employee'");
        $emp_pkey = isset($arr_emp_pkey[0]['emp_details']['emp_pkey']) ? $arr_emp_pkey[0]['emp_details']['emp_pkey'] : 0;

        if ($employee) {
            $this->EditPunches->useDbConfig = $this->Session->read('ds');
            $count_array = $this->EmployeeDetails->query("SELECT COUNT(*) as total FROM `employee_regularaization` 
            left join emp_details ed on(ed.emp_pkey = employee_regularaization.approved_person)
            WHERE empid = '" . $employee . "' AND att_date LIKE '%" . $att_date . "%' $condition 
            order by  TIME(LOGTIME) asc");
            $count += ($count_array) ? $count_array[0][0]['total'] : 0;

            $SqlQuery = "SELECT employee_regularaization.*,ed.first_name as approved_person_name FROM `employee_regularaization` 
            left join emp_details ed on(ed.emp_pkey = employee_regularaization.approved_person)
            WHERE empid = '" . $employee . "' AND att_date LIKE '%" . $att_date . "%' $condition 
            order by  TIME(LOGTIME) asc
            limit $limit  offset $ofst"; //AND approved = 'P'
            $attendances = $this->EmployeeDetails->query($SqlQuery);

            // debug($attendances);

            foreach ($attendances as $key => $value) {
                // $resp_mispunches["rows"][$i] = $value["employee_regularaization"];
                // $resp_mispunches["rows"][$i]['approved_person_name'] = $value["ed"]['approved_person_name'];

                $temp_array["rows"][$i] = $value["employee_regularaization"];
                $temp_array["rows"][$i]['approved_person_name'] = $value["ed"]['approved_person_name'];
                $temp_array["rows"][$i]['attendance_type'] = "regularisation";

                $sorting_array[$i] = date('Y-m-d H:i:s', strtotime($temp_array["rows"][$i]['LOGDATE'] . ' ' . $temp_array["rows"][$i]['LOGTIME']));

                $i++;
            }

            if ($sorting_array) {
                asort($sorting_array); // This is to sort all data by LOGTIME. by Arul on 16-10-2022
            }
            // debug($sorting_array);

            $i = 0;
            foreach ($sorting_array as $key => $val) {
                $resp_mispunches["rows"][$i] = $temp_array["rows"][$key];
                $i++;
            }
        }
        $resp_mispunches["total"] = $count;
        echo json_encode($resp_mispunches);
    }

    //Ends

    // public function Updateame()
    // {
    //     $this->layout = null;
    //     $this->autoRender = FALSE;
    //     $this->EditPunches->useDbConfig = $this->Session->read('ds');
    //     $arr_data = $this->request->data;
    //     //debug($arr_data);
    //     $emp_id = $arr_data['emp'];
    //     $month = $arr_data['month'] . '-01';
    //     $get_emp = $this->EditPunches->query("select emp_pkey,branch_code from emp_details where emp_id= '$emp_id' and status = '1' ");
    //     $emp_pkey = $get_emp['0']['emp_details']['emp_pkey'];
    //     $branch_code = isset($get_emp['0']['emp_details']['branch_code']) ? $get_emp['0']['emp_details']['branch_code'] : 'NULL';
    //     $deleterecords = $this->EditPunches->query("delete from emp_detail_timeattandance where emp_pkey='$emp_pkey' and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m') ) ");
    //     if (!$shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )")) {
    //         return FALSE;
    //         die();
    //     }
    //     if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
    //         if (!$this->EditPunches->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")) {
    //             return false;
    //             die();
    //         }
    //     } else {
    //         if (!$this->EditPunches->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")) {
    //             return false;
    //             die();
    //         }
    //     }
    // }
    public function Updateame()
    {
        $this->layout = null;
        $this->autoRender = false;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');

        $arr_data = $this->request->data;

        $emp_id = $arr_data['emp'];
        $month = $arr_data['month'] . '-01';

        $get_emp = $this->EditPunches->query("
        SELECT emp_pkey, branch_code 
        FROM emp_details 
        WHERE emp_id = '$emp_id' 
        AND status = '1'
    ");

        if (empty($get_emp)) {
            echo json_encode(['success' => false, 'message' => 'Employee not found']);
            return;
        }

        $emp_pkey = $get_emp[0]['emp_details']['emp_pkey'];
        $branch_code = isset($get_emp[0]['emp_details']['branch_code'])
            ? $get_emp[0]['emp_details']['branch_code']
            : null;

        $company_code = $this->Session->read('company_code');

        $specialCompanies = [
            'ABSG',
            'VGFS',
            'VSFS',
            'DRRC',
            'DJIC',
            'AGNG',
            'AYRK',
            'GTRA',
            'VGNN',
            'SHYD',
            'SRTS'
        ];


        if (in_array($company_code, $specialCompanies)) {

            $this->EditPunches->query("
            DELETE FROM emp_detail_timeattandance 
            WHERE emp_pkey = '$emp_pkey'
            AND yearmonth = '$month'
            AND emp_pkey NOT IN (
                SELECT emp_fkey 
                FROM attendance_register 
                WHERE isdelete = 'N'
                AND month_year = DATE_FORMAT('$month','%Y-%m')
            )
        ");

            $shiftdetailed = $this->EditPunches->query("
            SELECT is_multiple_days 
            FROM working_day_time_procedures 
            WHERE day_time_seq IN (
                SELECT day_time_seq 
                FROM emp_proff 
                WHERE emp_fkey = '$emp_pkey'
            )
        ");

            if (!$shiftdetailed) {
                return false;
            }

            if ($shiftdetailed[0]['working_day_time_procedures']['is_multiple_days'] == 'Y') {

                $this->EditPunches->query("
                SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')
            ");
            } else {

                $this->EditPunches->query("
                SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')
            ");
            }
        } else {

            $att_start_date = $this->EditPunches->query("
            SELECT att_start_end_fn('$month', 1) AS start_date
        ");

            $att_end_date = $this->EditPunches->query("
            SELECT att_start_end_fn('$month', 2) AS end_date
        ");

            $start_date = $att_start_date[0][0]['start_date'];
            $end_date = $att_end_date[0][0]['end_date'];

            $dates = $this->EditPunches->query("
            SELECT DISTINCT SHIFTDATE
            FROM device_attandance
            WHERE emp_id = '$emp_id'
            AND SHIFTDATE BETWEEN '$start_date' AND '$end_date'
            AND status = 'Y'
            ORDER BY SHIFTDATE
        ");

            if (empty($dates)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No attendance found for this period'
                ]);
                return;
            }

            foreach ($dates as $row) {

                $shift_date = $row['device_attandance']['SHIFTDATE'];

                $this->EditPunches->query("
                SELECT time_duration_check('$shift_date', '$emp_pkey', '$branch_code')
            ");
            }
        }

        echo json_encode(['success' => true]);
    }

    public function Updateamendmens()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        $branch_code = $arr_data['brn'];
        $month = $arr_data['month'] . '-01';
        $get_emp = $this->EditPunches->query("select emp_pkey from emp_details where branch_code= '$branch_code' and status = '1' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq is not null)");


        //die();
        //debug($get_emp);
        foreach ($get_emp as $value) {
            //debug($value);
            $emp_pkey = isset($value['emp_details']['emp_pkey']) ? $value['emp_details']['emp_pkey'] : 0;
            $deleterecords = $this->EditPunches->query("delete from  emp_detail_timeattandance where emp_pkey in ('$emp_pkey')  and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m')) ");


            $shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )");
            if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
                if (!$this->EditPunches->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")) {
                    continue;
                    //return false;
                    //die();
                }
            } else {
                if (!$this->EditPunches->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")) {
                    continue;
                    //return false;
                    //die();
                }
            }
        }
        return true;
    }

    public function form($empid = 0, $att_date = '', $site_t_fkey = '')
    {
        $this->layout = null;
        $this->set("empid", $empid);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $site_detailss = array();

        if (($site_t_fkey != 0)) {
            $site_detailss = $this->EmployeeDetails->query("select * from site_transactions left join designation on (designation.id = site_transactions.designation_id) left join site on (site.site_pkey = site_transactions.site_fkey) where site_transactions_pkey = '$site_t_fkey' ");
        }
        $emp = $this->EmployeeDetails->query("select emp_pkey from emp_details where emp_id = $empid");
        foreach ($emp as $val) {
            $emp_pkey = isset($val['emp_details']['emp_pkey']) ? $val['emp_details']['emp_pkey'] : '';
        }
        $arr_leave = $this->EmployeeDetails->query("select emp_leave_transactions.Leavestatus, emp_leave_transactions.leave_session,emp_leave_transactions.Remarks,le.salary_head_item_fkey,sal.item from emp_leave_transactions
            left join leaveentries as le on (le.LEAVEENTRYID  = emp_leave_transactions.LEAVEENTRYID)
            left join salary_head_items as sal on (sal.salary_head_item_pkey = le.salary_head_item_fkey) where leave_date ='$att_date' and emp_leave_transactions.LEAVESTATUS in 
            ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
            and emp_leave_transactions.LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$emp_pkey')");

        $arr_leave_exists = $this->EmployeeDetails->query("select count(*) as COUNT  from emp_leave_transactions where leave_session = '3' and leave_date ='$att_date' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$emp_pkey')");
        $arr_diff_half_leave = $this->EmployeeDetails->query("select count(*) as COUNT from emp_leave_transactions where leave_session <> '3' "
            . " and leave_date ='$att_date' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') "
            . " and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$emp_pkey')");
        $diff_half_count = isset($arr_diff_half_leave[0][0]['COUNT']) ? $arr_diff_half_leave[0][0]['COUNT'] : 0;
        $leave = '';
        $sessions = '';
        $remarks = '';
        $statuss = '';
        $items = '';
        $half = '';
        $h_leave = array();
        foreach ($arr_leave as $value) {
            $status = isset($value['emp_leave_transactions']['Leavestatus']) ? $value['emp_leave_transactions']['Leavestatus'] : '';
            $remark = isset($value['emp_leave_transactions']['Remarks']) ? $value['emp_leave_transactions']['Remarks'] : '';
            $session = isset($value['emp_leave_transactions']['leave_session']) ? $value['emp_leave_transactions']['leave_session'] : '';
            $item = isset($value['sal']['item']) ? $value['sal']['item'] : '';
            if ($session == 3) {
                $remark = "Full day";
            }

            $leave .= "Cannot add attendance ," . $item . ' ' . $status . ' on ' . $remark . "";
            $sessions .= $session;
            if ($session == 1) {
                $half = "First half";
            }
            if ($session == 2) {
                $half = "Second half";
            }
            $remarks .= $remark;
            $statuss .= $status;
            $items .= $item;
            $h_leave[] = $half . ' ' . $item . ' ' . $status;
        }
        $count = isset($arr_leave_exists[0][0]['COUNT']) ? $arr_leave_exists[0][0]['COUNT'] : 0;

        $self_login = ($emp_pkey == $this->Session->read('emp_fkey')) ? true : false;

        $hierarchy = $this->EmployeeDetails->query("SELECT attr1
        FROM `emp_proff`
        WHERE `emp_fkey` = '" . $emp_pkey . "'");

        $hierarchy_person = (isset($hierarchy[0]['emp_proff']['attr1']) && $hierarchy[0]['emp_proff']['attr1']) ? $hierarchy[0]['emp_proff']['attr1'] : false;

        $this->set("hierarchy_person", $hierarchy_person);
        $this->set("self_login", $self_login);


        $this->set("leave", $leave);
        $this->set("statuss", $statuss);
        $this->set("items", $items);
        $this->set("h_leave", $h_leave);
        $this->set("sessions", $sessions);
        $this->set("remarks", $remarks);
        $this->set("count", $count);
        $this->set("att_date", $att_date);
        $this->set("site_t_fkey", $site_t_fkey);
        $this->set("diff_half_count", $diff_half_count);
    }

    // Bulk update by Arul on 10-09-22
    public function bulkform()
    {
        $empid = $_REQUEST['employee'];
        $att_date = implode(',', $_REQUEST['selectDate']);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $temp_att_date = $_REQUEST['selectDate'];
        foreach ($temp_att_date as &$value) {
            $value = '\'' . $value . '\''; // This is to append quotes along with dates.
        }
        unset($value);

        $SqlQuery = "SELECT * FROM `employee_regularaization` 
        WHERE empid = '" . $empid . "' AND att_date IN (" . implode(",", $temp_att_date) . ") AND approved = 'P' AND status = 1";

        $regularasation_data = $this->EmployeeDetails->query($SqlQuery);

        $SqlQuery = "SELECT emp_pkey FROM `emp_details` WHERE `emp_id` = '" . $empid . "'";
        $emp_details = $this->EmployeeDetails->query($SqlQuery);

        $self_login = ($emp_details[0]['emp_details']['emp_pkey'] == $this->Session->read('emp_fkey')) ? true : false;

        $hierarchy = $this->EmployeeDetails->query("SELECT attr1
        FROM `emp_proff`
        WHERE `emp_fkey` = '" . $emp_details[0]['emp_details']['emp_pkey'] . "'");

        $hierarchy_person = (isset($hierarchy[0]['emp_proff']['attr1']) && $hierarchy[0]['emp_proff']['attr1']) ? $hierarchy[0]['emp_proff']['attr1'] : false;

        $this->set("hierarchy_person", $hierarchy_person);
        $this->set("empid", $empid);
        $this->set("self_login", $self_login); // This is to restrict direct approval of self attendance entry
        $this->set("att_date", $att_date);
        $this->set("site_t_fkey", 0);
        $this->set("regularasation_data", $regularasation_data); // This is going to be new tab to approve/reject attendance
    }

    // Bulk update by Arul on 10-09-22
    public function bulkapprove()
    {
        $empid = $_REQUEST['employee'];
        $att_date = implode(',', $_REQUEST['selectRegDate']);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $temp_att_date = $_REQUEST['selectRegDate'];
        foreach ($temp_att_date as &$value) {
            $value = '\'' . $value . '\''; // This is to append quotes along with dates.
        }
        unset($value);

        $SqlQuery = "SELECT * FROM `employee_regularaization` 
        WHERE empid = '" . $empid . "' AND att_date IN (" . implode(",", $temp_att_date) . ") AND approved = 'P' 
        AND approved_person = '" . $this->Session->read('emp_fkey') . "' AND status = 1 order by DATE(LOGDATE) asc,TIME(LOGTIME) asc";

        $regularasation_data = $this->EmployeeDetails->query($SqlQuery);

        $this->set("empid", $empid);
        $this->set("att_date", $att_date);
        $this->set("site_t_fkey", 0);
        $this->set("regularasation_data", $regularasation_data); // This is going to be new tab to approve/reject attendance
    }

    // Bulk update by Arul on 10-09-22
    public function bulkadminapprove()
    {
        // $empid = $_REQUEST['employee'];
        $regIds = implode(',', $_REQUEST['selectRegIds']);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        // $temp_att_date = $_REQUEST['selectRegIds'];
        // foreach ($temp_att_date as &$value) {
        //     $value = '\'' . $value . '\''; // This is to append quotes along with dates.
        // }
        // unset($value);

        $SqlQuery = "SELECT * FROM `employee_regularaization` 
        WHERE id IN (" . trim($regIds) . ") order by DATE(LOGDATE) asc,TIME(LOGTIME) asc";

        // AND approved = 'P' AND status = 1

        $regularasation_data = $this->EmployeeDetails->query($SqlQuery);

        // $this->set("empid", $empid);
        $this->set("regIds", $regIds);
        $this->set("site_t_fkey", 0);
        $this->set("regularasation_data", $regularasation_data); // This is going to be new tab to approve/reject attendance
    }

    // Bulk update for hierarchy person by Arul on 08-01-23
    public function bulkhierarchyapprove()
    {
        // $empid = $_REQUEST['employee'];
        $regIds = implode(',', $_REQUEST['selectRegIds']);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        // $temp_att_date = $_REQUEST['selectRegIds'];
        // foreach ($temp_att_date as &$value) {
        //     $value = '\'' . $value . '\''; // This is to append quotes along with dates.
        // }
        // unset($value);

        $SqlQuery = "SELECT * FROM `employee_regularaization` 
        WHERE id IN (" . trim($regIds) . ") order by DATE(LOGDATE) asc,TIME(LOGTIME) asc";

        // AND approved = 'P' AND status = 1

        $regularasation_data = $this->EmployeeDetails->query($SqlQuery);

        // $this->set("empid", $empid);
        $this->set("regIds", $regIds);
        $this->set("site_t_fkey", 0);
        $this->set("regularasation_data", $regularasation_data); // This is going to be new tab to approve/reject attendance
    }

    // Bulk save into employee_regularaization table
    public function bulkupdate_self()
    {
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EditPunchesHist->UseDbCnfig = $this->Session->read('ds');

        $resp = array();
        $resp["success"] = true;
        $resp['msg'] = "";
        $false_falg = 0;
        $pending_error = "";
        $upcoming_error = "";

        $emp_details = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_id` = '" . $_POST["empid"] . "' and status = 1");
        $emp_pkey = $emp_details[0]['emp_details']['emp_pkey'];

        $attendance_dates = ($_POST['att_date']) ? explode(",", $_POST['att_date']) : [];
        foreach ($attendance_dates as $date) {
            date_default_timezone_set('Asia/Kolkata');
            $curtime = time();
            $time = strtotime($date);
            if ($curtime > $time) {

                // Attendance verified check by Arul on 11-12-22
                $month = date('m', strtotime($date));
                $year = date('Y', strtotime($date));
                $month1 = $year . '-' . $month . '-01';
                $att_startdate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
                $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
                $att_startdate1 = $att_startdate['0']['0']['monthly_att_fromdate'];
                $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];
                if (($date >= $att_startdate1) && ($date <= $att_enddate1)) {
                    $yearmonth = date('Y-m-d', strtotime($att_enddate1));
                    $arr_attendance_register = $this->EmployeeDetails->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth','%Y-%m') and isdelete='N' and emp_fkey= '$emp_pkey' ");
                    $count = $arr_attendance_register['0']['0']['cnt'];
                } else if (($date <= $att_enddate1) && ($date >= $att_enddate1)) {
                    $yearmonth = date('Y-m-d', strtotime($att_enddate1));
                    $arr_attendance_register = $this->EmployeeDetails->query("select count(*) cnt from attendance_register where month_year=date_format('$date','%Y-%m') and isdelete='N' and emp_fkey= '$emp_pkey' ");
                    $yearmonth1 = date('Y-m-d', strtotime($date . ' + 1 months'));
                    $arr_attendance_register1 = $this->EmployeeDetails->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth1','%Y-%m') and isdelete='N' and emp_fkey= '$emp_pkey' ");
                    $count = $arr_attendance_register['0']['0']['cnt'] + $arr_attendance_register1['0']['0']['cnt'];
                } else if (($date >= $att_enddate1) && ($date >= $att_enddate1)) {
                    $yearmonth = date('Y-m-d', strtotime($att_enddate1 . ' + 1 months'));
                    $arr_attendance_register = $this->EmployeeDetails->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth','%Y-%m') and isdelete='N' and emp_fkey= '$emp_pkey' ");
                    $count = $arr_attendance_register['0']['0']['cnt'];
                } else if (($date <= $att_enddate1) && ($date <= $att_enddate1)) {
                    $yearmonth = date('Y-m-d', strtotime($att_enddate1));
                    $arr_attendance_register = $this->EmployeeDetails->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth','%Y-%m') and isdelete='N' and emp_fkey= '$emp_pkey' ");
                    $count = $arr_attendance_register['0']['0']['cnt'];
                } else {
                    $count = 0;
                }

                if (isset($count) && $count > 0) {
                    $false_falg = 1;
                    $upcoming_error .= "Cannot add attendance. Attendance verified for this month.";
                    continue;
                }


                // Daily verify check by Arul on 12-12-22
                $count1 = 0;
                $daily_attendance = $this->EmployeeDetails->query("select count(*) cnt from emp_detail_timeattandance where att_date = '$date' and isdelete='N' and emp_pkey= '$emp_pkey' ");
                if ($daily_attendance && isset($daily_attendance['0']['0']['cnt'])) {
                    $count1 = $daily_attendance['0']['0']['cnt'];
                }

                if (isset($count1) && $count1 > 0) {
                    $false_falg = 1;
                    $upcoming_error .= "Cannot add attendance. Attendance verified for this date " . date('d-m-Y', strtotime($date)) . "";
                    continue;
                }
            } else {
                // $resp["success"] = false;
                $false_falg = 1;
                $upcoming_error .= "Cannot add attendance to upcoming dates.(" . date('d-m-Y', strtotime($date)) . ")";
                // return json_encode($resp);
                continue;
            }



            $data = array();

            $data["C1"] = $_POST["C1"];
            $data["C3"] = $_POST["C3"];
            $data["empid"] = $_POST["empid"];
            $data["LOGTIME"] = $_POST["LOGTIME"];
            $data["LOGDATE"] = $date;
            $data["created_date"] = date('Y-m-d h-i-s');
            $data["created_by"] = $this->Session->read('login_user_id');

            $arr_regularisation_exist = $this->EmployeeDetails->query("SELECT count(*) as COUNT from employee_regularaization where
            C1 = '" . $data["C1"] . "' and LOGDATE = '" . $data["LOGDATE"] . "' and approved in ('P') 
            and empid = '" . $data["empid"] . "' and status = 1");

            $count = isset($arr_regularisation_exist[0][0]['COUNT']) ? $arr_regularisation_exist[0][0]['COUNT'] : 0;
            if ($count > 0) {
                $pending_error .= $data["LOGDATE"] . " (" . $data["C1"] . ")  ";
                $false_falg = 1;
                continue;
            }

            $hierarchy_head = ($_POST["hierarchy_head"]) ? $_POST["hierarchy_head"] : 0;

            $SqlQuery = "INSERT INTO `employee_regularaization` 
            (`att_date`, `C1`, `C3`, `empid`, `LOGDATE`, `LOGTIME`, `approved`, `approved_person`, `status`, 
            `created_by`, `created_date`, `updated_by`, `updated_date`)
            VALUES ('" . $data['LOGDATE'] . "', '" . $data['C1'] . "', '" . $data['C3'] . "', '" . $data['empid'] . "', 
            '" . $data['LOGDATE'] . "', '" . $data['LOGTIME'] . "', 'P', '" . $hierarchy_head . "' , '1', 
            '" . $data['created_by'] . "', '" . date('Y-m-d H:i:s') . "', '', '')";

            $this->EmployeeDetails->query($SqlQuery);
        }

        if ($false_falg) {
            if ($pending_error) {
                $resp['msg'] = "Cannot add attendance. Attendance Regularisation Pending in,  ";
                $resp['msg'] .= $pending_error;
            }
            if ($upcoming_error) {
                $resp['msg'] .= $upcoming_error;
            }
            $resp["success"] = false;
        }
        return json_encode($resp);
        //End Arul 10-09-22

    }


    // This is to update/ approve the changes into detail time attendance table.
    public function bulkupdate($adminUpdate = '')
    {
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EditPunchesHist->UseDbCnfig = $this->Session->read('ds');

        if (!$_POST['id'] || !isset($_POST['approved'])) {
            $resp = [];
            $resp['msg'] = "Cannot find any record.";
            $resp["success"] = false;
            return json_encode($resp);
            exit;
        }


        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $remarks = ($_POST['remarks']) ? $_POST['remarks'] : '';
            $remarks_rej = $remarks;
        } else {
            $remarks = 'Approved By Admin';
            $remarks_rej = 'Rejected By Admin';
        }
        $condition = ($adminUpdate) ? "" : " AND approved = 'P' AND status = 1 ";

        $SqlQuery = "SELECT * FROM `employee_regularaization` WHERE id IN (" . implode(",", $_POST['id']) . ") " . $condition;

        $regularasation_data = $this->EmployeeDetails->query($SqlQuery);

        $resp = array();
        $resp["success"] = true;
        $resp['msg'] = "";
        $resp["approve"] = $_POST['approved'];
        $false_falg = 0;
        date_default_timezone_set('Asia/Kolkata');
        // $attendance_dates = ($_POST['att_date']) ? explode(",", $_POST['att_date']) : [];
        foreach ($regularasation_data as $reg) {

            $current_iterate_false_flag = 0;
            $current_error_msg = "";
            $regular_data = $reg['employee_regularaization'];

            if ($_POST['approved'] == 'A') {
                $data = array();
                $date = $regular_data['att_date'];

                // This is to check whether attendance already processed or not
                if (in_array($regular_data['approved'], ['A', 'R'])) {
                    $existing_action = "";
                    $existing_action = ($regular_data['approved'] == "A") ? 'Approved' : 'Rejected';
                    $false_falg = 1;
                    $current_iterate_false_flag = 1;
                    $resp['msg'] .= "Attendance already " . $existing_action . " in selected date.(" . date('d-m-Y', strtotime($date)) . ")";
                    // return json_encode($resp);
                    continue;
                }

                $data["C1"] = $regular_data['C1'];
                $data["C3"] = $regular_data["C3"];
                $leavedate = $date;
                $emp_id = $regular_data["empid"];
                $status = $regular_data["empid"];
                $logdate = $date . ' ' . $regular_data["LOGTIME"];
                $d = strtotime($logdate);
                $data["LOGDATE"] = date("Y-m-d H:i:s", $d);
                $data["emp_id"] = $regular_data["empid"];
                $data["device_attandance_seq"] = 0;
                $data["DEVICEID"] = 0;
                $data["C2"] = "REG";
                $data["company_code"] = $this->Session->read('company_code');
                $data["created_by"] = $this->Session->read('login_user_id');
                try {
                    $br_details = $this->EmployeeDetails->find("first", array("conditions" => array("emp_id" => $regular_data["empid"]), "fields" => array("branch_code")));
                    $data["branch_code"] = isset($br_details['EmployeeDetails']['branch_code']) ? $br_details['EmployeeDetails']['branch_code'] : "";

                    //--- Add condition for checking leave exists and restrict adding punch in upcoming days ---- Added By Nimisha 18/03/2019
                    // start 
                    $empid = $this->EmployeeDetails->query("select emp_pkey from emp_details where  emp_id = '$emp_id'");
                    $status = $this->EmployeeDetails->query("select status from emp_details where  emp_id = '$emp_id'");
                    $data['status'] = 'Y';

                    $empkey = $empid['0']['emp_details']['emp_pkey'];
                    $status = $status['0']['emp_details']['status'];
                    $arr_leave = $this->EmployeeDetails->query("select Leavestatus,Remarks  from emp_leave_transactions where leave_session = '3' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                       and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");

                    $arr_leave_exists = $this->EmployeeDetails->query("select count(*) AS COUNT from emp_leave_transactions where  leave_session = '3' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                       and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");
                    $arr_f_half_day = $this->EmployeeDetails->query("select count(*) as COUNT  from emp_leave_transactions where leave_session = '1' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                       and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");
                    $arr_s_half_day = $this->EmployeeDetails->query("select count(*) as COUNT  from emp_leave_transactions where leave_session = '2' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                       and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");

                    $f_half_count = isset($arr_f_half_day[0][0]['COUNT']) ? $arr_f_half_day[0][0]['COUNT'] : 0;
                    $s_half_count = isset($arr_s_half_day[0][0]['COUNT']) ? $arr_s_half_day[0][0]['COUNT'] : 0;
                    $leave = '';
                    foreach ($arr_leave as $value) {
                        $status = isset($value['emp_leave_transactions']['Leavestatus']) ? $value['emp_leave_transactions']['Leavestatus'] : '';
                        $remark = isset($value['emp_leave_transactions']['Remarks']) ? $value['emp_leave_transactions']['Remarks'] : '';
                        $leave .= $remark . '-' . $status;
                    }

                    $count = isset($arr_leave_exists[0][0]['COUNT']) ? $arr_leave_exists[0][0]['COUNT'] : 0;

                    if ($count > 0) {
                        $false_falg = 1;
                        $current_iterate_false_flag = 1;
                        $resp['msg'] .= "Cannot add attendance, Leave Exists in this Date.(" . date('d-m-Y', strtotime($date)) . ")";
                        $current_error_msg = "Cannot add attendance, Leave Exists in this Date.(" . date('d-m-Y', strtotime($date)) . ")";
                    }


                    // Attendance verify check by Arul on 11-Dec-22
                    $month = date('m', strtotime($date));
                    $year = date('Y', strtotime($date));
                    $month1 = $year . '-' . $month . '-01';
                    $att_startdate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
                    $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
                    $att_startdate1 = $att_startdate['0']['0']['monthly_att_fromdate'];
                    $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];
                    if (($date >= $att_startdate1) && ($date <= $att_enddate1)) {
                        $yearmonth = date('Y-m-d', strtotime($att_enddate1));
                        $arr_attendance_register = $this->EmployeeDetails->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth','%Y-%m') and isdelete='N' and emp_fkey= '$empkey' ");
                        $count = $arr_attendance_register['0']['0']['cnt'];
                    } else if (($date <= $att_enddate1) && ($date >= $att_enddate1)) {
                        $yearmonth = date('Y-m-d', strtotime($att_enddate1));
                        $arr_attendance_register = $this->EmployeeDetails->query("select count(*) cnt from attendance_register where month_year=date_format('$date','%Y-%m') and isdelete='N' and emp_fkey= '$empkey' ");
                        $yearmonth1 = date('Y-m-d', strtotime($date . ' + 1 months'));
                        $arr_attendance_register1 = $this->EmployeeDetails->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth1','%Y-%m') and isdelete='N' and emp_fkey= '$empkey' ");
                        $count = $arr_attendance_register['0']['0']['cnt'] + $arr_attendance_register1['0']['0']['cnt'];
                    } else if (($date >= $att_enddate1) && ($date >= $att_enddate1)) {
                        $yearmonth = date('Y-m-d', strtotime($att_enddate1 . ' + 1 months'));
                        $arr_attendance_register = $this->EmployeeDetails->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth','%Y-%m') and isdelete='N' and emp_fkey= '$empkey' ");
                        $count = $arr_attendance_register['0']['0']['cnt'];
                    } else if (($date <= $att_enddate1) && ($date <= $att_enddate1)) {
                        $yearmonth = date('Y-m-d', strtotime($att_enddate1));
                        $arr_attendance_register = $this->EmployeeDetails->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth','%Y-%m') and isdelete='N' and emp_fkey= '$empkey' ");
                        $count = $arr_attendance_register['0']['0']['cnt'];
                    } else {
                        $count = 0;
                    }

                    if (isset($count) && $count > 0) {
                        $false_falg = 1;
                        $current_iterate_false_flag = 2;
                        $resp['msg'] .= "Attendance cannot be saved. Attendance verified for this month.";
                        $current_error_msg = "Attendance cannot be saved. Attendance verified for this month.";
                        $remarks = "Attendance verified for this month.";
                    }


                    // Daily attendance verify by Arul on 12-12-22

                    $count = 0;
                    $arr_attendance_register = $this->EmployeeDetails->query("select count(*) cnt from emp_detail_timeattandance where att_date = '$leavedate' and isdelete='N' and emp_pkey= '$empkey' ");
                    if ($arr_attendance_register && isset($arr_attendance_register['0']['0']['cnt'])) {
                        $count = $arr_attendance_register['0']['0']['cnt'];
                    }

                    if (isset($count) && $count > 0) {
                        $false_falg = 1;
                        $current_iterate_false_flag = 2;
                        $resp['msg'] .= "Attendance cannot be saved. Attendance verified for this date.";
                        $current_error_msg = "Attendance cannot be saved. Attendance verified for this date.";
                        $remarks = "Attendance verified for this date.";
                    }

                    // Verify check ends here //////////////////////

                    date_default_timezone_set("Asia/Calcutta");
                    $curtime = time();
                    $time = strtotime($data["LOGDATE"]);
                    if (($curtime > $time) && !$current_iterate_false_flag) {
                        try {
                            // This is to remove already existing attendance from device_attendance by Arul on 11-Dec-22
                            $c1 = $data["C1"];
                            $asc_desc = (strtolower($c1) == 'in') ? 'ASC' : 'DESC';
                            $attendance_exist = $this->EditPunches->query("SELECT * FROM `device_attandance` WHERE `emp_id` = '$emp_id' AND `C1` = '$c1' AND `LOGDATE` LIKE '%$leavedate%' AND status = 'Y' ORDER BY `LOGDATE` $asc_desc LIMIT 1");

                            if ($attendance_exist && isset($attendance_exist[0])) {
                                $dev_seq_id = $attendance_exist[0]['device_attandance']['device_attandance_seq'];
                                $this->EditPunches->query("UPDATE device_attandance SET status = 'N' WHERE device_attandance_seq = $dev_seq_id");
                            }
                            // removing existing attendance ends here

                            // This is to add attendance into device attendance table
                            $this->EditPunches->save($data);
                        } catch (RuntimeException $e) {
                            $false_falg = 1;
                            $current_iterate_false_flag = 1;
                            $resp['msg'] .= "Cannot add duplicate attendance for the same date.(" . date('d-m-Y', strtotime($date)) . ")";
                            $current_error_msg = "Cannot add duplicate attendance for the same date.(" . date('d-m-Y', strtotime($date)) . ")";
                        }

                        if ($current_iterate_false_flag == 0) {
                            $data["status"] = 'Y';
                            $data["action"] = 'insert';
                            $this->EmployeeDetails->query($this->insert_func("device_attandance_hist", $data));
                            $resp['msg'] .= (!$current_iterate_false_flag) ? "New Attendance saved successfully.(" . date('d-m-Y', strtotime($date)) . ")" : "";
                        }
                    } else {

                        if (!$current_iterate_false_flag) {
                            $false_falg = 1;
                            $current_iterate_false_flag = 1;
                            $resp['msg'] .= "Cannot add attendance to upcoming dates.(" . date('d-m-Y', strtotime($date)) . ")";
                            $current_error_msg = "Cannot add attendance to upcoming dates.(" . date('d-m-Y', strtotime($date)) . ")";
                            continue;
                        }
                    }
                } catch (RuntimeException $e) {
                    $false_falg = 1;
                    $current_iterate_false_flag = 1;
                    $resp['msg'] .= "Cannot add duplicate attendance for the same date.(" . date('d-m-Y', strtotime($date)) . ")";
                    $current_error_msg = "Cannot add duplicate attendance for the same date.(" . date('d-m-Y', strtotime($date)) . ")";
                }
                $SqlQuery = '';
                if ($current_iterate_false_flag) {
                    $approved_person_id = ($this->Session->read('emp_fkey')) ? $this->Session->read('emp_fkey') : 0;
                    $SqlQuery = "UPDATE `employee_regularaization` SET status = 0 , approved = 'R' , 
                    remarks = '" . $remarks . "',
                    C3 = '" . $current_error_msg . "',
                    updated_by = '" . $data["created_by"] . "' , 
                    updated_date = '" . date('Y-m-d H:i:s') . "' 
                    WHERE id = " . $regular_data["id"];
                } else {
                    $approved_person_id = ($this->Session->read('emp_fkey')) ? $this->Session->read('emp_fkey') : 0;
                    $SqlQuery = "UPDATE `employee_regularaization` SET status = 1 , approved = 'A' , 
                    remarks = '" . $remarks . "',
                    updated_by = '" . $data["created_by"] . "' , 
                    updated_date = '" . date('Y-m-d H:i:s') . "' 
                    WHERE id = " . $regular_data["id"];
                }
                $this->EmployeeDetails->query($SqlQuery);
            } else {

                date_default_timezone_set("Asia/Calcutta");
                // This is to check whether attendance already processed or not
                if (in_array($regular_data['approved'], ['R'])) {
                    $false_falg = 1;
                    $current_iterate_false_flag = 1;
                    $resp['msg'] .= "Attendance already Rejected in selected date.(" . date('d-m-Y', strtotime($regular_data['att_date'])) . ")";
                    continue;
                }

                $resp['msg'] .= (!$current_iterate_false_flag) ? "Attendance Rejected successfully.(" . date('d-m-Y', strtotime($regular_data['att_date'])) . ")" : "";

                $approved_person_id = ($this->Session->read('emp_fkey')) ? $this->Session->read('emp_fkey') : 0;
                $SqlQuery = "UPDATE `employee_regularaization` SET status = 0 ,
                approved = 'R',  remarks = '" . $remarks_rej . "',
                updated_by = '" . $this->Session->read('login_user_id') . "' , 
                updated_date = '" . date('Y-m-d H:i:s') . "' 
                WHERE id = " . $regular_data["id"];
                $this->EmployeeDetails->query($SqlQuery);

                // This is to delete already existing attendance from attendance_register
                $c1 = $regular_data['C1'];
                $emp_id = $regular_data["empid"];
                // $empid = $this->EmployeeDetails->query("select emp_pkey from emp_details where  emp_id = '$emp_id'");
                // $empkey = $empid['0']['emp_details']['emp_pkey'];
                if ($regular_data["LOGTIME"] && $regular_data["LOGDATE"]) {
                    $leavedate = date('Y-m-d H:i:s', strtotime($regular_data["LOGDATE"] . ' ' . $regular_data["LOGTIME"]));
                } else {
                    $leavedate = $regular_data['att_date'];
                }

                $asc_desc = (strtolower($c1) == 'in') ? 'ASC' : 'DESC';
                $attendance_exist = $this->EditPunches->query("SELECT * FROM `device_attandance` WHERE `emp_id` = '$emp_id' AND `C1` = '$c1' AND `LOGDATE` LIKE '%$leavedate%' AND status = 'Y' ORDER BY `LOGDATE` $asc_desc LIMIT 1");

                if ($attendance_exist && isset($attendance_exist[0])) {
                    $dev_seq_id = $attendance_exist[0]['device_attandance']['device_attandance_seq'];
                    $this->EditPunches->query("UPDATE device_attandance SET status = 'N' WHERE device_attandance_seq = $dev_seq_id");
                }
                //////////////////////////////////////////////////////////////////////////
            }
        }
        if ($false_falg) {
            $resp["success"] = false;
        }
        return json_encode($resp);
        //End Arul 10-09-22

    }

    // This is to save directly to attendance tables. (FOR HIERARCHY EMPLOYEE)
    public function bulksavenew()
    {
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EditPunchesHist->UseDbCnfig = $this->Session->read('ds');

        $resp = array();
        $resp["success"] = true;
        $resp['msg'] = "";
        $false_falg = 0;

        $attendance_dates = ($_POST['att_date']) ? explode(",", $_POST['att_date']) : [];
        foreach ($attendance_dates as $date) {
            $data = array();

            $data["C1"] = $_POST["C1"];
            $data["C3"] = $_POST["C3"];
            $leavedate = $date;
            $emp_id = $_POST["empid"];
            $status = $_POST["empid"];
            $logdate = $date . ' ' . $_POST["LOGTIME"];
            $d = strtotime($logdate);
            $data["LOGDATE"] = date("Y-m-d H:i:s", $d);
            $data["emp_id"] = $_POST["empid"];
            $data["device_attandance_seq"] = 0;
            $data["DEVICEID"] = 0;
            $data["C2"] = "REG";
            $data["company_code"] = $this->Session->read('company_code');
            date_default_timezone_set('Asia/Kolkata');
            $data["created_date"] = date('Y-m-d h-i-s');
            $data["created_by"] = $this->Session->read('login_user_id');
            try {
                $br_details = $this->EmployeeDetails->find("first", array("conditions" => array("emp_id" => $_POST["empid"]), "fields" => array("branch_code")));
                $data["branch_code"] = isset($br_details['EmployeeDetails']['branch_code']) ? $br_details['EmployeeDetails']['branch_code'] : "";

                //--- Add condition for checking leave exists and restrict adding punch in upcoming days ---- Added By Nimisha 18/03/2019
                // start 
                $empid = $this->EmployeeDetails->query("select emp_pkey from emp_details where  emp_id = '$emp_id'");
                $status = $this->EmployeeDetails->query("select status from emp_details where  emp_id = '$emp_id'");
                $data['status'] = 'Y';

                $empkey = $empid['0']['emp_details']['emp_pkey'];
                $status = $status['0']['emp_details']['status'];
                $arr_leave = $this->EmployeeDetails->query("select Leavestatus,Remarks  from emp_leave_transactions where leave_session = '3' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");

                $arr_leave_exists = $this->EmployeeDetails->query("select count(*) AS COUNT from emp_leave_transactions where  leave_session = '3' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");
                $arr_f_half_day = $this->EmployeeDetails->query("select count(*) as COUNT  from emp_leave_transactions where leave_session = '1' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");
                $arr_s_half_day = $this->EmployeeDetails->query("select count(*) as COUNT  from emp_leave_transactions where leave_session = '2' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");

                $f_half_count = isset($arr_f_half_day[0][0]['COUNT']) ? $arr_f_half_day[0][0]['COUNT'] : 0;
                $s_half_count = isset($arr_s_half_day[0][0]['COUNT']) ? $arr_s_half_day[0][0]['COUNT'] : 0;
                $leave = '';
                foreach ($arr_leave as $value) {
                    $status = isset($value['emp_leave_transactions']['Leavestatus']) ? $value['emp_leave_transactions']['Leavestatus'] : '';
                    $remark = isset($value['emp_leave_transactions']['Remarks']) ? $value['emp_leave_transactions']['Remarks'] : '';
                    $leave .= $remark . '-' . $status;
                }

                $count = isset($arr_leave_exists[0][0]['COUNT']) ? $arr_leave_exists[0][0]['COUNT'] : 0;

                if ($count > 0) {
                    // $resp["success"] = false;
                    $false_falg = 1;
                    $resp['msg'] .= "Cannot add attendance, Leave Exists in this Date.(" . date('d-m-Y', strtotime($date)) . ")";
                    // return json_encode($resp);
                    continue;
                }

                date_default_timezone_set("Asia/Calcutta");
                $curtime = time();
                $time = strtotime($data["LOGDATE"]);
                //debug($data);
                if ($curtime > $time) {
                    try {
                        $this->EditPunches->save($data);
                    } catch (RuntimeException $e) {
                        // $resp["success"] = false;
                        $false_falg = 1;
                        $resp['msg'] .= "Cannot add duplicate attendance for the same date.(" . date('d-m-Y', strtotime($date)) . ")";
                        // return json_encode($resp);
                        continue;
                    }
                    //     debug($data);
                    // $this->EditPunchesHist->save($data);
                    // amal->
                    $data["status"] = 'Y';
                    $data["action"] = 'insert';
                    $this->EmployeeDetails->query($this->insert_func("device_attandance_hist", $data));

                    // <-amal
                    // $resp["success"] = true;
                    $resp['msg'] .= (!$false_falg) ? "New Attendance saved successfully.(" . date('d-m-Y', strtotime($date)) . ")" : "";
                    // return json_encode($resp);

                    $SqlQuery = "INSERT INTO `employee_regularaization` 
                    (`att_date`, `C1`, `C3`, `empid`, `LOGDATE`, `LOGTIME`, `approved`, `approved_person`, `status`, 
                    `created_by`, `created_date`, `updated_by`, `updated_date`)
                    VALUES ('" . $data['LOGDATE'] . "', '" . $data['C1'] . "', '" . $data['C3'] . "', '" . $data['emp_id'] . "', 
                    '" . $data['LOGDATE'] . "', '" . $_POST["LOGTIME"] . "', 'A', '" . $this->Session->read('emp_fkey') . "' , '1', 
                    '" . $data['created_by'] . "', '" . date('Y-m-d H:i:s') . "', '', '')";

                    $this->EmployeeDetails->query($SqlQuery);
                } else {
                    // $resp["success"] = false;
                    $false_falg = 1;
                    $resp['msg'] .= "Cannot add attendance to upcoming dates.(" . date('d-m-Y', strtotime($date)) . ")";
                    // return json_encode($resp);
                    continue;
                }
            } catch (RuntimeException $e) {
                // $resp["success"] = false;
                $false_falg = 1;
                $resp['msg'] .= "Cannot add duplicate attendance for the same date.(" . date('d-m-Y', strtotime($date)) . ")";
                // return json_encode($resp);
                continue;
            }
        }
        if ($false_falg) {
            $resp["success"] = false;
        }
        return json_encode($resp);
        //End Arul 10-09-22

    }


    public function remove()
    {
        $this->autoRender = FALSE;
        $device_attandance_seq = 0;
        $resp = array('success' => false);
        if (isset($_REQUEST['device_attandance_seq']) && $_REQUEST['device_attandance_seq'] != 0) {
            $device_attandance_seq = $_REQUEST['device_attandance_seq'];

            $data['device_attandance_seq'] = $device_attandance_seq;
            $data['status'] = "D";
            $this->EditPunches->useDbConfig = $this->Session->read('ds');
            $this->EditPunches->save($data);
            $resp = array('success' => true);
        }
        echo json_encode($resp);
    }

    //   <=======Edited by Amal ========> 
    public function insert_func($tableName = '', $data = array())
    {
        $this->EditPunchesHist->UseDbCnfig = $this->Session->read('ds');
        $fields = "INSERT INTO $tableName (";
        $values = " VALUES (";
        foreach ($data as $key => $value) {
            # code...
            $fields .= $key;
            $values .= "'" . $value . "'";
            end($data);
            if ($key != key($data)) {
                $fields .= ",";
                $values .= ",";
            }
        }
        $fields .= ")";
        $values .= ")";

        return $fields . " " . $values;
    }

    public function savenew()
    {
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EditPunchesHist->UseDbCnfig = $this->Session->read('ds');

        $resp = array();
        $data = array();
        $data["C1"] = $_POST["C1"];
        $data["C3"] = $_POST["C3"];
        $leavedate = $_POST["LOGDATE"];
        $emp_id = $_POST["empid"];
        $status = $_POST["empid"];
        $logdate = $_POST["LOGDATE"] . ' ' . $_POST["LOGTIME"];
        $d = strtotime($logdate);
        $data["LOGDATE"] = date("Y-m-d H:i:s", $d);
        $data["emp_id"] = $_POST["empid"];
        $data["device_attandance_seq"] = 0;
        $data["DEVICEID"] = 0;
        $data["C2"] = "REG";
        $data["company_code"] = $this->Session->read('company_code');
        $data["created_by"] = $this->Session->read('login_user_id');
        try {
            $br_details = $this->EmployeeDetails->find("first", array("conditions" => array("emp_id" => $_POST["empid"]), "fields" => array("branch_code")));
            $data["branch_code"] = isset($br_details['EmployeeDetails']['branch_code']) ? $br_details['EmployeeDetails']['branch_code'] : "";

            //--- Add condition for checking leave exists and restrict adding punch in upcoming days ---- Added By Nimisha 18/03/2019
            // start 
            $empid = $this->EmployeeDetails->query("select emp_pkey from emp_details where  emp_id = '$emp_id'");
            $status = $this->EmployeeDetails->query("select status from emp_details where  emp_id = '$emp_id'");
            $data['status'] = 'Y';

            $empkey = $empid['0']['emp_details']['emp_pkey'];
            $status = $status['0']['emp_details']['status'];
            $arr_leave = $this->EmployeeDetails->query("select Leavestatus,Remarks  from emp_leave_transactions where leave_session = '3' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");

            $arr_leave_exists = $this->EmployeeDetails->query("select count(*) AS COUNT from emp_leave_transactions where  leave_session = '3' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");
            $arr_f_half_day = $this->EmployeeDetails->query("select count(*) as COUNT  from emp_leave_transactions where leave_session = '1' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");
            $arr_s_half_day = $this->EmployeeDetails->query("select count(*) as COUNT  from emp_leave_transactions where leave_session = '2' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");

            $f_half_count = isset($arr_f_half_day[0][0]['COUNT']) ? $arr_f_half_day[0][0]['COUNT'] : 0;
            $s_half_count = isset($arr_s_half_day[0][0]['COUNT']) ? $arr_s_half_day[0][0]['COUNT'] : 0;
            $leave = '';
            foreach ($arr_leave as $value) {
                $status = isset($value['emp_leave_transactions']['Leavestatus']) ? $value['emp_leave_transactions']['Leavestatus'] : '';
                $remark = isset($value['emp_leave_transactions']['Remarks']) ? $value['emp_leave_transactions']['Remarks'] : '';
                $leave .= $remark . '-' . $status;
            }

            $count = isset($arr_leave_exists[0][0]['COUNT']) ? $arr_leave_exists[0][0]['COUNT'] : 0;

            if ($count > 0) {
                $resp["success"] = false;
                $resp['msg'] = "Cannot add attendance, Leave Exists in this Date.";
                return json_encode($resp);
            }

            date_default_timezone_set("Asia/Calcutta");
            $curtime = time();
            $time = strtotime($data["LOGDATE"]);
            //debug($data);
            if ($curtime > $time) {
                try {
                    $this->EditPunches->save($data);
                } catch (RuntimeException $e) {
                    $resp["success"] = false;
                    $resp['msg'] = "Cannot add duplicate attendance for the same date.";
                    return json_encode($resp);
                }
                //     debug($data);
                // $this->EditPunchesHist->save($data);
                // amal->
                $data["status"] = 'Y';
                $data["action"] = 'insert';
                $this->EmployeeDetails->query($this->insert_func("device_attandance_hist", $data));

                // <-amal
                $resp["success"] = true;
                $resp['msg'] = "New Attendance saved successfully";
                return json_encode($resp);
            } else {
                $resp["success"] = false;
                $resp['msg'] = "Cannot add attendance to upcoming dates.";
                return json_encode($resp);
            }
        } catch (RuntimeException $e) {
            $resp["success"] = false;
            $resp['msg'] = "Cannot add duplicate attendance for the same date.";
            return json_encode($resp);
        }
        //End Nimisha 18/03/2019

    }
    public function savepunch()
    {

        $resp = array('success' => true);
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EditPunchesHist->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $_POST["modified_by"] = $this->Session->read('login_user_id');

        $data = array();

        $this->EditPunches->save($_POST);
        $data = $_REQUEST;

        //-> amal
        $direction = $data['C1'];
        $action = $data['status'];
        if ($action == 'Y') {
            $actions = 'Active';
        } else {
            $actions = 'Inactive';
        }

        if ($direction == 'in') {
            $dire = 'In';
        } else {
            $dire = 'Out';
        }
        if ($action == 'Y') {
            $out = $actions;
        } else {
            $out = $actions;
        }
        $data['action'] = $out;
        $data['created_by'] = $this->Session->read('login_user_id');

        $this->EmployeeDetails->query($this->insert_func("device_attandance_hist", $data));
        // <-amal
        echo json_encode($resp);
    }
    //    public function savepunch($empid = 0) {
    //
    //       
    //        $resp = array('success' => true);
    //        $arr_data = $this->request->data;
    //        $this->autoRender = FALSE;
    //        $this->EditPunches->useDbConfig = $this->Session->read('ds');
    //        $this->EditPunchesHist->useDbConfig = $this->Session->read('ds');
    //        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //        $resp = array();
    //        $data = array();
    //        $data["C1"] = $_POST["C1"];
    //        $data["C3"] = $_POST["C3"];
    //        $leavedate = $_POST["LOGDATE"];
    //        
    //        $logdate = $_POST["LOGDATE"] . ' ' . $_POST["LOGTIME"];
    //        $d = strtotime($logdate);
    //        $data["LOGDATE"] = date("Y-m-d H:i:s", $d);
    //        $data["emp_id"] =$empid;
    //       
    //        $data["device_attandance_seq"] = 0;
    //        $data["DEVICEID"] = 0;
    //        $data["company_code"] = $this->Session->read('company_code');
    //        $data["created_by"] = $this->Session->read('login_user_id');
    //        $data["status"] = $arr_data['status'];
    //        //$data["company_code"] = $this->Session->read('company_code');
    //        $br_details = $this->EmployeeDetails->find("first", array("conditions" => array("emp_id" => $empid), "fields" => array("branch_code")));
    //      
    //        $data['branch_code'] = isset($br_details['EmployeeDetails']['branch_code']) ? $br_details['EmployeeDetails']['branch_code'] : "";
    //        
    //       //--- Add condition for checking leave exists and restrict adding punch in upcoming days ---- Added By Nimisha 18/03/2019
    //        // start 
    //      
    //        $count = isset($arr_leave_exists[0][0]['COUNT']) ? $arr_leave_exists[0][0]['COUNT'] : 0;
    //       
    ////        $_POST["edited"] = 'Y';
    ////        $_POST["edited_time"] = date('Y-m-d H:i:s');
    //        $this->EditPunches->save($data);
    //        
    //
    //          //-> amal
    //         $data = $_REQUEST;
    //      $direction = $data['C1'];
    //      $action = $data['status'];
    //         if ($action == 'Y'){
    //        $actions = 'Active';
    //      }
    //      else{
    //        $actions = 'Inactive';
    //      }
    //     
    //      if($direction == 'in' ){
    //              $dire = 'In';
    //      }
    //      else{
    //        $dire = 'Out';
    //      }
    //      if($action == 'Y'){
    //        $out =$actions ;
    //      }
    //      else{
    //          $out = $actions;
    //      }
    //       $data['action'] = $out;
    //        //-> amal
    //        $this->EmployeeDetails->query($this->insert_func("device_attandance_hist",$data));
    //        // <-amal
    //        echo json_encode($resp);
    //    }
    public function getmonths()
    {
        //$this->autoRender = FALSE;
        $arr_months = array();
        $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
        for ($i = 0; $i < 12; $i++) {
            $month = date('Y-m', strtotime("-$i month", $start_month));
            $arr_months[] = array(
                'id' => $month,
                'text' => $month
            );
        }
        return json_encode($arr_months);
    }

    public function sendmemo()
    {



        $Email = new CakeEmail();
        $Email->from(array('sruthi.pb@gmail.com' => 'My Site'));
        $Email->to('sruthiforsight@gmail.com');
        $Email->subject('About');
        $Email->send('My message');
        //debug($Email);
        echo "sucess";
    }

    public function updateStatus()
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $id = $_POST['id'];
        $status = ($_POST['status']) ? $_POST['status'] : 0;
        $C1 = ($_POST['C1']) ? " C1 = '" . $_POST['C1'] . "' , " : '';
        $SqlQuery = "UPDATE `employee_regularaization` SET status = $status , $C1
        updated_by = '" . $this->Session->read('login_user_id') . "' , 
        updated_date = '" . date('Y-m-d H:i:s') . "' 
        WHERE id = " . $id;
        $this->EmployeeDetails->query($SqlQuery);
        $resp["success"] = true;
        return json_encode($resp);
    }

    public function getEmployeesByBranch()
    {
        $this->autoRender = false;

        $branch = isset($_REQUEST['branch']) ? $_REQUEST['branch'] : '0';
        $emp_pkey = $this->Session->read('emp_fkey');

        $user_group = $this->Session->read('user_group');
        $context = $this->MasterdataManagement->getFeatureAccessContext();

        // Edited by Athira on 31-05-2026
        if ($user_group == 1 && ($branch == '0' || $branch == '')) {
            // Admin + All branches selected: return all employees
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $raw = $this->EmployeeDetails->query("SELECT emp_pkey, emp_id, first_name FROM emp_details WHERE status = 1 ORDER BY TRIM(first_name) ASC");
            $result = [];
            foreach ($raw as $row) {
                $empdata = isset($row['emp_details']) ? $row['emp_details'] : (isset($row[0]) ? $row[0] : array());
                if (isset($empdata['emp_pkey']) && $empdata['emp_pkey'] !== null && $empdata['emp_pkey'] !== '') {
                    $obj = new stdClass();
                    $obj->id = $empdata['emp_pkey'];
                    $obj->text = $empdata['first_name'] . ' (' . $empdata['emp_id'] . ')';
                    $result[] = $obj;
                }
            }
            echo json_encode($result);
            return;

        } elseif ($user_group == 1) {
            // Admin: employees in the selected branch
            $employees = $this->MasterdataManagement
                ->getAllEmployeesByBranch($branch, 0);

        } elseif ($context['is_hierarchy']) {
            // Hierarchy user: only their subordinates in that branch
            $employees = $this->MasterdataManagement
                ->getHierarchyEmployeesByBranch($emp_pkey, $branch);

        } else {
            // Allocated-branch user: employees in the selected allocated branch only
            $employees = $this->MasterdataManagement
                ->getAllEmployeesByBranch($branch, $emp_pkey);
        }

        $result = [];
        if (!empty($employees)) {
            foreach ($employees as $e) {
                $obj = new stdClass();
                $obj->id = $e['e']['id'];
                $obj->text = $e[0]['text'];
                $result[] = $obj;
            }
        }

        echo json_encode($result);
        // Ended by Athira 31-05-2026
    }
}
