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
class EmployeeRegisterController extends AppController {

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'EmployeeRegister';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'CompanyContactInfo', 'EditPunches', 'EmployeeDetails', 'DbConfig','EditPunchesHist','AttendanceRegister');
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */


    public function index($emp_id = '')
{
    $this->layout = null;

    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    // Months (keep if needed in UI)
    $arr_months = json_decode($this->getmonths());
    $this->set('arr_months', $arr_months);

    // Logged-in employee
    $user_fkey = $this->Session->read('emp_fkey');

    // Fetch only logged-in employee details
    $employee = $this->EmployeeDetails->query("
        SELECT emp_id, first_name, last_name 
        FROM emp_details 
        WHERE emp_pkey = ? AND status = 1
    ", array($user_fkey));

    $arr_employees = [];

    if (!empty($employee)) {
        $emp = $employee[0]['emp_details'];

        $arr_employees[] = (object)[
    'id' => $emp['emp_id'],
    'text' => $emp['first_name'] . ' ' . $emp['last_name']
];
    }

    // Optional: Branch (only own branch)
    $this->loadModel('Units');
    $this->Units->useDbConfig = $this->Session->read('ds');

    $branch = $this->EmployeeDetails->query("
        SELECT emp_branch FROM emp_proff WHERE emp_fkey = ?
    ", array($user_fkey));

    $arr_branches = [];

    if (!empty($branch)) {
        $branch_code = $branch[0]['emp_proff']['emp_branch'];

        $branch_data = $this->Units->find('first', [
            'fields' => ['branch_code', 'branch_name'],
            'conditions' => ['branch_code' => $branch_code, 'status' => 1]
        ]);

        if (!empty($branch_data)) {
            $arr_branches[] = $branch_data['Units'];
        }
    }

    // Pass to view
    $this->set('arr_employees', $arr_employees);
    $this->set('arr_branches', $arr_branches);
    $this->set('company_code', strtoupper($this->Session->read('company_code')));

    if (!empty($emp_id)) {
        $this->set('emp_id', $emp_id);
    }

    // Render
    $this->render('index_new');
}

    public function jsons($branch = '', $resigned = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->loadModel('Units');

        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        $user_fkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = strtoupper($this->Session->read('company_code'));
        $feature_fkey = $this->Session->read('current_feature_id');
        if (!$feature_fkey) $feature_fkey = 39;

        $conditions = " WHERE ed.status = 1 ";
        if ($resigned == '1') {
            $conditions = " WHERE ed.status IN (1, 2) ";
        }

        if ($branch != '0' && !empty($branch)) {
            $conditions .= " AND ed.branch_code = '$branch' ";
        }

        if ($q != null) {
            $conditions .= " AND (ed.first_name LIKE '%$q%' OR ep.emp_company_id LIKE '%$q%') ";
        }

        if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'GAAR' || $company_code == 'ETNA'|| $company_code == 'MNDM'|| $company_code == 'FYNK'|| $company_code == 'EXTR'|| $company_code == 'TUDS'|| $company_code == 'IFHY'|| $company_code == 'INFR'|| $company_code == 'CKWR'|| $company_code == 'BNGL'|| $company_code == 'FRSG'|| $company_code == 'DVDS'|| $company_code == 'STPN'|| $company_code == 'TSSM'|| $company_code == 'ELKT'|| $company_code == 'IMSC'|| $company_code == 'STFR'|| $company_code == 'LBLD'|| $company_code == 'LNTT'|| $company_code == 'EDGM'|| $company_code == 'ASQR'|| $company_code == 'SVNS'|| $company_code == 'AMST')) {
            $accessResults = $this->EmployeeDetails->query("
                SELECT branch_fkey, is_hierarchy 
                FROM user_feature_branch_access 
                WHERE user_fkey = ? AND feature_fkey = ? AND LCASE(active) = 'y'
            ", array($user_fkey, $feature_fkey));

            $allocated_branches = array();
            $is_hierarchy = 'N';

            foreach ($accessResults as $row) {
                $r = isset($row['user_feature_branch_access']) ? $row['user_feature_branch_access'] : $row[0];
                $allocated_branches[] = $r['branch_fkey'];
                if (strtoupper($r['is_hierarchy']) == 'Y') $is_hierarchy = 'Y';
            }

            if ($is_hierarchy == 'Y') {
                $conditions .= " AND (ep.attr1 = '$user_fkey' OR ep.emp_fkey = '$user_fkey') ";
            } elseif (!empty($allocated_branches)) {
                $branch_list = "'" . implode("','", $allocated_branches) . "'";
                $conditions .= " AND ep.emp_branch IN ($branch_list) ";
            } else {
                $payro = $this->EmployeeDetails->query("SELECT payro_priv FROM emp_proff WHERE emp_fkey = ?", array($user_fkey));
                $p_priv = isset($payro[0]['emp_proff']['payro_priv']) ? $payro[0]['emp_proff']['payro_priv'] : (isset($payro[0][0]['payro_priv']) ? $payro[0][0]['payro_priv'] : 0);
                if ($p_priv != 1) {
                    $conditions .= " AND ed.emp_pkey = '$user_fkey' ";
                }
            }
        }

        $query = "SELECT ed.emp_pkey, ed.first_name, ed.last_name, ep.emp_company_id 
                  FROM emp_details ed
                  JOIN emp_proff ep ON ep.emp_fkey = ed.emp_pkey
                  $conditions
                  ORDER BY ed.first_name ASC";
                  
        $res = $this->EmployeeDetails->query($query);
        
        $items = array();
        foreach ($res as $val) {
            $r = isset($val['ed']) ? $val['ed'] : (isset($val['emp_details']) ? $val['emp_details'] : $val[0]);
            $p = isset($val['ep']) ? $val['ep'] : (isset($val['emp_proff']) ? $val['emp_proff'] : $val[0]);
            
            $items[] = array(
                'id' => $r['emp_pkey'],
                'text' => $r['first_name'] . ' ' . $r['last_name'] . ' - ' . $p['emp_company_id']
            );
        }

        echo json_encode(array('items' => $items));
        exit;
    }

    public function indexload($emp_id = '') {
        $this->layout = null;

        $arr_months = json_decode($this->getmonths());
        $this->set('arr_months', $arr_months);

        $arr_employees = json_decode($this->requestAction("/ApiRequest/listemployees/"));
        $this->set('arr_employees', $arr_employees);

        if (!empty($emp_id)) {
            $this->set('emp_id', $emp_id);
        }
         //edited by athira on 06-11-2025
                $company_code = strtoupper($this->Session->read('company_code'));
		$this->set("company_code", $company_code);
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
       //edited by athira on 11-02-2026
			$restrictedCompanies = [
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK',
    'GTRA','VGNN','SHYD','SRTS'
		];

		if (!in_array($company_code, $restrictedCompanies)) {
        $this->render('index_new');
        }
        //end
    }

     public function Iterateame()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        // debug($arr_data);
        $emp_id = $arr_data['emp'];
        $month = $arr_data['month'] . '-01';
        $get_emp = $this->EditPunches->query("select emp_pkey from emp_details where emp_id= '$emp_id' and status = '1' ");
        // debug($get_emp);
        $emp_pkey = $get_emp['0']['emp_details']['emp_pkey'];
        //debug($emp_pkey);
        $branch_code = isset($get_emp['0']['emp_details']['branch_code']) ? $get_emp['0']['emp_details']['branch_code'] : 'NULL';
        //  $deleterecords = $this->EditPunches->query("delete from emp_detail_timeattandance where emp_pkey='$emp_pkey' and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m') ) ");
        //  if (!$shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )")) {
        // return FALSE;
        //   die();
        //  }
        if ($f = $this->EditPunches->query("SELECT device_logs_iteration_fn('$emp_id' , '$month')")) {
            // debug($f);
            return false;
            die();
        }
    }

    public function hierarchy() {
        $this->layout = null;

        $arr_months = json_decode($this->getmonths());
        $this->set('arr_months', $arr_months);
        //edited by sinsiya 26-02-2024
        $user_group = $this->Session->read('user_group');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if($user_group==2){
        $cur_emp_key = $this->Session->read("emp_fkey");
        $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
        }
        if($payroUser[0]['emp_proff']['payro_priv']){
            $arr_employees = json_decode($this->requestAction("/ApiRequest/listemployees/"));
        }else{
            $arr_employees = json_decode($this->requestAction("/ApiRequest/listemployeesforhierarchy/"));
        }
        //$arr_employees = json_decode($this->requestAction("/ApiRequest/listemployeesforhierarchy/"));
        $this->set('arr_employees', $arr_employees);
        $this->render('index');
    }

    public function employeeeditpunch() {
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

    /* public function listpunches() {
      $this->autoRender = FALSE;
      $this->EditPunches->useDbConfig = $this->Session->read('ds');
      //debug($this->Session->read('ds'));

      $employee = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : 0;
      $month = isset($_REQUEST['month']) ? $_REQUEST['month'] : '';
      $includeinactive = isset($_REQUEST['includeinactive']) ? $_REQUEST['includeinactive'] : '';
      $limit = $_REQUEST['rows'];
      $page = $_REQUEST['page'];

      $ofst = ($page - 1) * $limit;

      $resp_mispunches = array();
      $resp_mispunches["rows"] = array();

      $condition = array();

      if ($includeinactive) {
      if ($includeinactive == 'N') {
      $condition['status'] = array('Y');
      } else {
      $condition['status'] = array('Y', 'N');
      }
      } else {
      $condition['status'] = array('Y');
      }
      if ($employee) {
      $condition['emp_id'] = $employee;
      }
      if ($month) {
      //$condition['MONTH(LOGDATE)'] = $month;
      $condition['DATE_FORMAT(LOGDATE,"%Y-%c")'] = $month;
      }
      $count = 0;
      if ($employee) {
      $count = $this->EditPunches->find("count", array('conditions' => $condition));
      $arr_mispunches = $this->EditPunches->find("all", array('conditions' => $condition, 'order' => array('EditPunches.LOGDATE'), 'limit' => intval($limit), 'offset' => intval($ofst)));
      }
      foreach ($arr_mispunches as $key => $value) {
      $resp_mispunches["rows"][$key] = $value["EditPunches"];
      }
      $resp_mispunches["total"] = $count;
      echo json_encode($resp_mispunches);
      } */

    //New List starts


    public function listpunches()
    {
        $this->autoRender = FALSE;
 //edited by athira on 06-11-2025
                $company_code = strtoupper($this->Session->read('company_code'));
		$this->set("company_code", $company_code);
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
        //edited by athira on 11-02-2026
		$restrictedCompanies = [
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK',
    'GTRA','VGNN','SHYD','SRTS'
		];

		if (!in_array($company_code, $restrictedCompanies)) {
             $base_table = "emp_detail_timeattandance";
    $emp = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : 0;
    if ($emp == 0) return;

    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    $this->DbConfig->useDbConfig = $this->Session->read('ds');
    $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
    $company_code = $this->Session->read('company_code');

    // Employee basic details
    $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM emp_details WHERE emp_id = '$emp'");
    $emp_pkey = $arr_emp_pkey[0]['emp_details']['emp_pkey'];

    // Modified Query to Include HOLIDAY_GROUP_ID
        $have_shift = $this->EmployeeDetails->query("SELECT day_time_seq, HOLIDAY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_pkey'");
        $shift_id = $have_shift[0]['emp_proff']['day_time_seq'];
        $holiday_group_id = $have_shift[0]['emp_proff']['HOLIDAY_GROUP_ID'];
    
  
    $branch_code = $arr_emp_pkey[0]['emp_details']['branch_code'] ;
    $monthdd = $_REQUEST['month'];
    $yearmonth = $monthdd . '-01';

    $limit = $_REQUEST['rows'];
    $page = $_REQUEST['page'];
    $ofst = ($page - 1) * $limit;

    $resp_mispunches = ["rows" => [], "data" => [], "out" => []];

    if ($emp_pkey == 0) return;

    $condition = "$base_table.emp_pkey = '$emp_pkey' AND ";

    // Attendance start and end date
    $att_start_date = $this->EmployeeDetails->query("SELECT att_start_end_fn('$yearmonth', 1) AS start_date");
    $att_end_date = $this->EmployeeDetails->query("SELECT att_start_end_fn('$yearmonth', 2) AS end_date");
    $attendance_start = $att_start_date[0][0]['start_date'];
    $attendance_end = $att_end_date[0][0]['end_date'];

// Fetch Holidays
        $holidays_query = $this->EmployeeDetails->query("
            SELECT HOLIDAYDATE, HOLIDAYNAME 
            FROM holidays 
            WHERE HOLIDAY_GROUP_ID = '$holiday_group_id' 
            AND status = 1 
            AND HOLIDAYDATE BETWEEN '$attendance_start' AND '$attendance_end'
        ");
        $holiday_map = [];
        foreach ($holidays_query as $h) {
            $holiday_map[$h['holidays']['HOLIDAYDATE']] = $h['holidays']['HOLIDAYNAME'];
        }

        // Fetch Week Off Config
        $shift_config = $this->EmployeeDetails->query("
            SELECT Sunday, Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, 
                   Sunday_F, Monday_F, Tuesday_F, Wednesday_F, Thursday_F, Friday_F, Saturday_F
            FROM working_day_time_procedures 
            WHERE day_time_seq = '$shift_id'
        ");
        $sc = $shift_config[0]['working_day_time_procedures'];

        // Fetch leave details separately
// ... (leave query) ...

// Build date range
        $period = [];
        $current = strtotime($attendance_start);
        $end = strtotime($attendance_end);
        while ($current <= $end) {
            $period[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }
        
        // Loop through dates
        foreach ($period as $att_date) {
            // ... (overtime logic or skip) ...

            $is_holiday = isset($holiday_map[$att_date]);
            $day_name = date('l', strtotime($att_date)); // e.g. Sunday
            $is_wo = ($sc[$day_name] == 'N');
            $is_half_wo = ($sc[$day_name . '_F'] == 'Y');
            
            $special_status = '';
            if ($is_holiday) {
                $special_status = 'HO';
            } elseif ($is_wo) {
                $special_status = 'WO';
            } elseif ($is_half_wo) {
                $special_status = '/WO';
            }

            // ... (rest of logic) ...
            
            // Logic to incorporate HO/WO into Status
            // If present string is empty or 'A/A' (Absent), and we have a special status (HO/WO), use that?
            // Actually, if a day is HO/WO, usually there is no attendance expected.
            // If there is a punch, it might be Overtime or Present on WO.
            // If there is NO punch (empty day), definitely use HO/WO.
            // If there is 'A' (Absent), replace with HO/WO.
            
            // ...
        }


        $branch_code = $arr_emp_pkey[0]['emp_details']['branch_code'];
        $monthdd = $_REQUEST['month'];
        $yearmonth = $monthdd . '-01';

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;

        $resp_mispunches = ["rows" => [], "data" => [], "out" => []];

        if ($emp_pkey == 0) return;

        $condition = "$base_table.emp_pkey = '$emp_pkey' AND ";

        

        // Fetch only attendance within the calculated date range
        // $dates = $this->EmployeeDetails->query("
        //                                             SELECT DISTINCT SHIFTDATE
        //                                             FROM device_attandance
        //                                             WHERE emp_id = '$emp'
        //                                             AND SHIFTDATE BETWEEN '$attendance_start' AND '$attendance_end' AND status='Y'
        //                                             ORDER BY SHIFTDATE
        //                                         ");

        // if (empty($dates)) {
        //     echo json_encode(['success' => false, 'message' => 'No attendance found for this period']);
        //     return;
        // }

        //edited by athira on 22-05-2026
        // Check editable status
        $arr_attendance_register = $this->EmployeeDetails->query("
                                            SELECT *
                                            FROM attendance_register
                                            WHERE emp_fkey = $emp_pkey
                                            AND month_year = '" . date('Y-m', strtotime($monthdd)) . "'
                                            AND isdelete = 'N'
                                        ");

        if (empty($arr_attendance_register)) {
            $iseditable = 1;
        } else {
            $iseditable = 0;
        }

        // Fetch finalized attendance register data for verified status display
        $arr_att_reg_final = $this->EmployeeDetails->query("
                                            SELECT *
                                            FROM attendance_register
                                            WHERE emp_fkey = $emp_pkey
                                            AND month_year = '" . date('Y-m', strtotime($monthdd)) . "'
                                            AND isdelete = 'N'
                                        ");
        $att_reg_final_record = !empty($arr_att_reg_final) ? $arr_att_reg_final[0]['attendance_register'] : null;
        //end edited by athira on 22-05-2026


        


        // ✅ Fetch all attendance data for that employee in one go
        $attendances = $this->EmployeeDetails->query("
        SELECT 
            empdetails.emp_id, 
            empdetails.first_name, 
            empdetails.last_name, 
            $base_table.*, 
            wd.minuts_calc_perday, 
            COALESCE(wd_shift.day_time_desc, wd.day_time_desc) AS shift_name,
            emp.emp_fkey, emp.joining_date
        FROM $base_table
        LEFT JOIN emp_details AS empdetails 
            ON empdetails.emp_pkey = $base_table.emp_pkey 
        LEFT JOIN emp_proff AS emp 
            ON emp.emp_fkey = empdetails.emp_pkey 
        LEFT JOIN working_day_time_procedures AS wd 
            ON wd.day_time_seq = emp.day_time_seq 
        LEFT JOIN (
            SELECT d1.emp_id, d1.SHIFTDATE, d1.SHIFT
            FROM device_attandance d1
            INNER JOIN (
                SELECT emp_id, SHIFTDATE, MAX(LOGDATE) AS max_logdate
                FROM device_attandance
                GROUP BY emp_id, SHIFTDATE
            ) d2 
                ON d1.emp_id = d2.emp_id 
                AND d1.SHIFTDATE = d2.SHIFTDATE 
                AND d1.LOGDATE = d2.max_logdate
        ) AS da 
            ON da.emp_id = empdetails.emp_id 
            AND da.SHIFTDATE = $base_table.att_date
        LEFT JOIN working_day_time_procedures AS wd_shift 
            ON wd_shift.day_time_seq = da.SHIFT
        WHERE $condition 
              $base_table.att_date BETWEEN '$attendance_start' AND '$attendance_end'
        ORDER BY $base_table.att_date
    ");

        // Fetch leave details separately
        $leaves_query = $this->EmployeeDetails->query("
            SELECT 
                elt.leave_date, 
                elt.leave_session, 
                elt.Leavestatus,
                shi.occurance
            FROM emp_leave_transactions AS elt
            JOIN leaveentries AS leaves ON leaves.LEAVEENTRYID = elt.LEAVEENTRYID
            JOIN salary_head_items AS shi ON shi.salary_head_item_pkey = leaves.salary_head_item_fkey
            WHERE leaves.EMP_fkey = '$emp_pkey' AND elt.Leavestatus IN ('Applied','Approved','Authorized')
            AND elt.leave_date BETWEEN '$attendance_start' AND '$attendance_end'
           
        ");


       

        $leave_map = [];
        foreach ($leaves_query as $lq) {
            $leave_map[$lq['elt']['leave_date']] = $lq;
        }


        // Map attendance by date
        $attendance_map = [];
        foreach ($attendances as $row) {
            $attendance_map[$row[$base_table]['att_date']] = $row;
        }

        // Generate grid rows
        foreach ($period as $index => $att_date) {
            // Edited by Akshay on 3-12-2025
            // debug($iseditable);

            $selmonth = $this->EmployeeDetails->query("SELECT yearmonth FROM `emp_detail_timeattandance` WHERE `att_date` = '$att_date' limit 1 ");
            if (!empty($selmonth)) {
                $yearmonth1 = $selmonth[0]['emp_detail_timeattandance']['yearmonth'];

                $arr_emp_ot_timeattandance1 = $this->EmployeeDetails->query("
                                                                            SELECT COUNT(*) AS total_count
                                                                            FROM emp_detail_timeattandance 
                                                                            LEFT JOIN emp_details AS empdetails ON empdetails.emp_pkey = emp_detail_timeattandance.emp_pkey 
                                                                            LEFT JOIN emp_proff AS emp ON emp.emp_fkey = empdetails.emp_pkey 
                                                                            LEFT JOIN working_day_time_procedures AS wd ON wd.day_time_seq = emp.day_time_seq
                                                                            WHERE emp_detail_timeattandance.emp_pkey = '$emp_pkey'
                                                                            AND emp_detail_timeattandance.att_date = '$att_date'
                                                                            AND emp_detail_timeattandance.yearmonth = '$yearmonth1'
                                                                            AND emp_detail_timeattandance.isdelete = 'N'
                                                                            AND emp.emp_fkey NOT IN (
                                                                                    SELECT empdetails.emp_pkey
                                                                                    FROM emp_details empdetails
                                                                                    LEFT JOIN emp_ot_timeattandance ON emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey
                                                                                    WHERE emp_detail_timeattandance.emp_pkey =  '$emp_pkey'
                                                                                    AND emp_detail_timeattandance.att_date = '$att_date'
                                                                                    AND emp_ot_timeattandance.att_date = '$att_date'
                                                                                    AND emp_detail_timeattandance.yearmonth = '$yearmonth1'
                                                                                    AND emp_ot_timeattandance.isdelete = 'N'
                                                                            )
                                                                            AND emp.emp_fkey NOT IN (
                                                                                    SELECT emp_fkey
                                                                                    FROM attendance_register
                                                                                    WHERE month_year = DATE_FORMAT('$yearmonth1', '%Y-%m')
                                                                                    AND isdelete = 'Y'
                                                                            )
                                                                            AND wd.overtime_monitoring = 'Y';
                                                                    ");

                $overtime1 = $arr_emp_ot_timeattandance1[0][0]['total_count'];

                $arr_emp_ot_timeattandance2 = $this->EmployeeDetails->query("select COUNT(*) AS total_count from emp_detail_timeattandance 
                                                                            left join emp_details as empdetails on(empdetails.emp_pkey = emp_detail_timeattandance.emp_pkey) 
                                                                            left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
                                                                            left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
                                                                            left join emp_ot_timeattandance on(emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey)
                                                                            where emp_detail_timeattandance.emp_pkey = '$emp_pkey' and  emp_detail_timeattandance.att_date = '$att_date' 
                                                                            AND emp_ot_timeattandance.att_date = '$att_date'  AND emp_detail_timeattandance.yearmonth='$yearmonth1'  
                                                                            AND emp_ot_timeattandance.isdelete = 'N'  AND wd.overtime_monitoring = 'Y'
                                                                            order by emp_detail_timeattandance.att_date");
                $overtime2 = $arr_emp_ot_timeattandance2[0][0]['total_count'];

                $arr_emp_ot_timeattandance3 = $this->EmployeeDetails->query("
                                                                            SELECT COUNT(*) AS total_count
                                                                            FROM emp_detail_timeattandance 
                                                                            LEFT JOIN emp_details AS empdetails ON empdetails.emp_pkey = emp_detail_timeattandance.emp_pkey 
                                                                            LEFT JOIN emp_proff AS emp ON emp.emp_fkey = empdetails.emp_pkey 
                                                                            LEFT JOIN working_day_time_procedures AS wd ON wd.day_time_seq = emp.day_time_seq 
                                                                            LEFT JOIN emp_ot_timeattandance ON emp_ot_timeattandance.emp_pkey = empdetails.emp_pkey 
                                                                                AND emp_ot_timeattandance.att_date = '$att_date'
                                                                                AND emp_ot_timeattandance.yearmonth = '$yearmonth1'
                                                                            WHERE emp_detail_timeattandance.emp_pkey = '$emp_pkey' and  emp_detail_timeattandance.att_date = '$att_date' 
                                                                                AND emp_detail_timeattandance.yearmonth = '$yearmonth1' 
                                                                                AND emp.emp_fkey IN (
                                                                                    SELECT emp_fkey FROM attendance_register 
                                                                                    WHERE month_year = DATE_FORMAT('$yearmonth1', '%Y-%m') 
                                                                                        AND isdelete = 'N'
                                                                                )
                                                                                AND emp_ot_timeattandance.isdelete = 'N'  
                                                                            ORDER BY emp_detail_timeattandance.att_date
                                                                        ");
                $overtime3 = $arr_emp_ot_timeattandance3[0][0]['total_count'];

                $overtime = $overtime1 + $overtime2;

                if ($overtime == 0) {
                    $overtime = $overtime3;
                }
            }


            // End

            $status_color = 'black';
            $leave_status_str = '';
            $occurance = '';
            $special_status = '';

            //edited by athira on 22-05-2026
            // Verified attendance register status for this date
            $reg_status = '';
            if ($att_reg_final_record) {
                $field_name = 'FIELD' . ($index + 1);
                $reg_status = isset($att_reg_final_record[$field_name]) ? $att_reg_final_record[$field_name] : '';
                if (!empty($reg_status)) {
                    $reg_status = trim(str_ireplace(['Approved', 'Authorized', 'Applied', 'Rejected'], '', $reg_status));
                }
            }
            //end edited by athira on 22-05-2026

            // Check Holiday
            if (isset($holiday_map[$att_date])) {
                $special_status = 'HO';
            } else {
                // Check Week Off
                $day_name = date('l', strtotime($att_date)); 
                if ($sc[$day_name] == 'N') {
                    $special_status = 'WO';
                } elseif ($sc[$day_name . '_F'] == 'Y') {
                    $special_status = '/WO';
                }
            }

            // Check in separate leave map for ANY day (Attendance record present or not)
            if (isset($leave_map[$att_date])) {
                $l_data = $leave_map[$att_date];
                if (!empty($l_data['elt']['leave_session']) && !empty($l_data['shi']['occurance'])) {
                    $session = $l_data['elt']['leave_session'];
                    $occurance = $l_data['shi']['occurance'];
                    $l_status = isset($l_data['elt']['Leavestatus']) ? $l_data['elt']['Leavestatus'] : '';
                    
                    if ($session == 3) {
                        $leave_status_str = "$occurance/$occurance";
                    } elseif ($session == 1) {
                            $leave_status_str = "$occurance/";
                    } elseif ($session == 2) {
                            $leave_status_str = "/$occurance";
                    }
                    
                    // Append Leave Status like (Approved)
                    if (!empty($l_status)) {
                        $leave_status_str .= " ($l_status)";
                    }
                }
            }

            if (isset($attendance_map[$att_date])) {

                $val = $attendance_map[$att_date];
                $shift_name = !empty($val[$base_table]['present']) ? $val[0]['shift_name'] : '';
                
                 // Logic: If present string contains the leave occurance (e.g. A/CL), replace with 'A'
                $present_str = trim($val[$base_table]['present']);
                if (!empty($occurance)) {
                     $present_str = str_replace($occurance, 'A', $present_str);
                }
                
                // Logic: If Absent ('A', 'A/A') or Empty, and match HO/WO, use HO/WO
                $check_absent = strtoupper($present_str);
                // If strictly Absent or Empty, override with Special Status (HO/WO)
                if ((empty($check_absent) || $check_absent == 'A' || $check_absent == 'A/A') && empty($val[$base_table]['att_in_time'])) {
                     if (!empty($special_status)) {
                         // If Leave is ALSO present? Leave takes priority/should be shown.
                         // If Leave exists, we typically don't show HO/WO unless requested.
                         // User said "map WO and HO to dates".
                         // Only override if NO Leave? 
                         if (empty($leave_status_str)) {
                             $present_str = $special_status;
                         }
                     }
                }

                //edited by athira on 22-05-2026
                $computed_status = trim(
                    $present_str . ' ' .
                        $leave_status_str . ' ' .
                        $special_status . ' ' .
                        ($val[$base_table]['holiday']) . ' ' .
                        ($val[$base_table]['weekoff']) . ' ' .
                        ($val[$base_table]['others'])
                );
                $final_status = !empty($reg_status) ? $reg_status : $computed_status;

                $arr_output = [
                    'att_date' => ($company_code == 'KWMT') ? date('d-m-Y', strtotime($att_date)) : $att_date,
                    'emp_id' => $val['empdetails']['emp_id'],
                    'att_in_time' => $val[$base_table]['att_in_time'],
                    'att_out_time' => $val[$base_table]['att_out_time'],
                    'duration' => $val[$base_table]['duration'],
                    'isdelete' => $val[$base_table]['isdelete'],
                    'joining_date' => $val['emp']['joining_date'],
                    'ad_present' => $val[$base_table]['ad_present'],
                    'ad_remarks' => $val[$base_table]['ad_remarks'],
                    'status' => $final_status,
                    'shift_name' => $shift_name,
                    'status_color' => !empty($reg_status) ?
                        (strtoupper($reg_status) == 'P/P' ? 'green' :
                            (in_array(strtoupper($reg_status), ['A/A', 'LOP/LOP', 'ABSENT']) ? 'red' : 'black')) :
                        (!empty($val[$base_table]['present']) && in_array(strtoupper($val[$base_table]['present']), ['A/A', 'P/A', 'A/P'])
                            ? 'red'
                            : ((strtoupper($val[$base_table]['present']) == 'P/P') ? 'green' : 'black')),
                    'editable' => $iseditable,
                ];
                //end edited by athira on 22-05-2026

            } else {
                //edited by athira on 22-05-2026
                // Empty day status
                $status_display = '';
                if (!empty($reg_status)) {
                    $status_display = $reg_status;
                } elseif (!empty($leave_status_str)) {
                    $status_display = $leave_status_str;
                } elseif (!empty($special_status)) {
                    $status_display = $special_status;
                }

                // Empty day record
                $arr_output = [
                    'att_date' => ($company_code == 'KWMT') ? date('d-m-Y', strtotime($att_date)) : $att_date,
                    'emp_id' => $emp,
                    'att_in_time' => '',
                    'att_out_time' => '',
                    'duration' => '',
                    'status' => $status_display,
                    'status_color' => !empty($reg_status) ?
                        (strtoupper($reg_status) == 'P/P' ? 'green' :
                            (in_array(strtoupper($reg_status), ['A/A', 'LOP/LOP', 'ABSENT']) ? 'red' : 'black')) : 'black',
                    'editable' => $iseditable,
                    'shift_name' => ''
                ];
                //end edited by athira on 22-05-2026
            }

            $resp_mispunches["rows"][] = $arr_output;
        }

        $total = count($resp_mispunches["rows"]);

        if (empty($shift_id)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'type' => 'error',
                'message' => "No shift allocated for this employee",
                'rows' => [],
                'total' => 0,
            ]);
            return;
        }

        $this->autoRender = false;
        $resp_mispunches = [
            'rows'  => array_values($resp_mispunches["rows"]),
            'data' => array(),
            'type' => 'success',
            'total' => "$total",
            'message' => "Employee Attendance Retrieved Successfully"

        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resp_mispunches);
        }else{
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
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = $monthdd;
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
        //                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //		$attend = $this->EmployeeDetails->query("select time_duration_check('$yearmonth','$emp','')");
        //		
        // if (!$shiftdetailed = $this->EmployeeDetails->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey')")) {
        //     $resp_mispunches["total"] = "0";
        //     $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
        //     $resp_mispunches["type"] = "danger";
        //     echo json_encode($resp_mispunches);
        //     //            return FALSE;
        //     die();
        // }

        // //debug($shiftdetailed);
        // if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
        //     if (!$this->EmployeeDetails->query("SELECT time_duration_check_multishift('$yearmonth', '$emp_pkey', '$branch_code')")) {
        //         $resp_mispunches["total"] = "0";
        //         $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
        //         $resp_mispunches["type"] = "danger";
        //         echo json_encode($resp_mispunches);
        //         //                return false;
        //         die();
        //     }
        // } else {
        //     if (!$this->EmployeeDetails->query("SELECT time_duration_check('$yearmonth', '$emp_pkey', '$branch_code')")) {
        //         $resp_mispunches["total"] = "0";
        //         $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
        //         $resp_mispunches["type"] = "danger";
        //         echo json_encode($resp_mispunches);
        //         //                return false;
        //         die();
        //     }
        // }
        $attendances_count = $this->EmployeeDetails->query("select count(*) AS count from $base_table left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where $emp_condition $condition yearmonth = '$yearmonth' order by att_date ");
        $count = isset($attendances_count[0][0]['count']) ? $attendances_count[0][0]['count'] : 0;

        $attendances = $this->EmployeeDetails->query("select 
			empdetails.emp_id, 
			empdetails.first_name, 
			empdetails.last_name, 
			$base_table.*, 
			wd.minuts_calc_perday, 
			emp.emp_fkey,isdelete,emp.joining_date
			FROM 
			$base_table 
			left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
			left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
			left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
            where $emp_condition $condition yearmonth = '$yearmonth' 
            order by att_date 
			LIMIT $ofst, $limit
		");

        $arr_attendance_register = $this->EmployeeDetails->query("select count(*) as count from attendance_register where emp_fkey = $emp_pkey AND month_year = '" . date('Y-m', strtotime($month)) . "' AND isdelete = 'Y'");
        $iseditable = isset($arr_attendance_register[0][0]['count']) ? $arr_attendance_register[0][0]['count'] : 0;

        $employee_attendance = array();
        // continue;
        foreach ($attendances as $val) {
            // foreach ($arr_outputs as $val){
            // 	debug($val);
            // }
            //$emppk = $val[$base_table]['emp_pkey'];
            //$employee_attendance[$emppk][] = $val;


            $arr_output['emp_id'] = isset($val['empdetails']['emp_id']) ? $val['empdetails']['emp_id'] : '';
            $arr_output['leaves'] = isset($val['emp_detail_timeattandance']['leaves']) ? $val['emp_detail_timeattandance']['leaves'] : '';
            //Edited by Akshay on 15-5-2024
            if($company_code == 'KWMT'){
                $arr_output['att_date'] = isset($val[$base_table]['att_date']) ? date('d-m-Y', strtotime($val[$base_table]['att_date'])) : ''; //edited by sinsiya
            }else{
                $arr_output['att_date'] = isset($val[$base_table]['att_date']) ? $val[$base_table]['att_date'] : '';
            }
            //End
            $arr_output['att_in_time'] = isset($val[$base_table]['att_in_time']) ? $val[$base_table]['att_in_time']/* date("h:i:s A", strtotime($val[$base_table]['att_in_time'])) */ : '';
            //edited by sinsiya on 04-11-2024
            if(!empty($arr_output['att_in_time'])){
            $arr_output['att_out_time'] = isset($val[$base_table]['att_out_time']) ? $val[$base_table]['att_out_time']/* date("h:i:s A", strtotime($val[$base_table]['att_out_time'])) */ : '';
           }else{
               $arr_output['att_out_time'] = "";
           }   
           // $arr_output['duration'] = isset($val[$base_table]['duration']) ? $val[$base_table]['duration'] : '';
            // //Edited by Akshay on 29-7-2024
//            if ($arr_output['att_in_time'] != '' && $arr_output['att_out_time'] != '') {
//                $start_datetime = new DateTime($arr_output['att_in_time']);
//                $diff = $start_datetime->diff(new DateTime($arr_output['att_out_time']));
//                $total_minutes = ($diff->days * 24 * 60);
//                $total_minutes += ($diff->h * 60);
//                $total_minutes += $diff->i;
//                $arr_output['duration'] = isset($total_minutes) ? $total_minutes : '';
//            } else {
           if(!empty($arr_output['att_in_time'])){
                $arr_output['duration'] = isset($val[$base_table]['duration']) ? $val[$base_table]['duration'] : '';
                 }else{
               $arr_output['duration'] = "";
           } 
            //}
            $arr_output['ad_shift_string'] = isset($val[$base_table]['AD_shift_string']) ? $val[$base_table]['AD_shift_string'] : '';
            $arr_output['ad_present'] = isset($val[$base_table]['ad_present']) ? $val[$base_table]['ad_present'] : '';
            $arr_output['ad_in_time'] = isset($val[$base_table]['ad_in_time']) ? $val[$base_table]['ad_in_time'] : '';
            $arr_output['ad_out_time'] = isset($val[$base_table]['ad_out_time']) ? $val[$base_table]['ad_out_time'] : '';
            $arr_output['ad_duration'] = isset($val[$base_table]['ad_duration']) ? $val[$base_table]['ad_duration'] : '';
            $arr_output['ad_remarks'] = isset($val[$base_table]['ad_remarks']) ? $val[$base_table]['ad_remarks'] : '';
            $arr_output['site_transactions_fkey'] = isset($val[$base_table]['site_transactions_fkey']) ? $val[$base_table]['site_transactions_fkey'] : '';
            $arr_output['isdelete'] = isset($val[$base_table]['isdelete']) ? $val[$base_table]['isdelete'] : '';
            $arr_output['joining_date'] = isset($val['emp']['joining_date']) ? $val['emp']['joining_date'] : '';


            //$arr_output['min_bfr_on_dutyughjyulkjyuty_cal_ot'] = isset($val[$base_table]['min_bfr_on_dutty_cal_ot'])?$val[$base_table]['min_bfr_on_dutty_cal_ot']:'';
            //$arr_output['min_aftr_off_dutty_cal_ot'] = isset($val[$base_table]['min_aftr_off_dutty_cal_ot'])?$val[$base_table]['min_aftr_off_dutty_cal_ot']:'';
            //$arr_output['ot_duration'] = isset($val[$base_table]['ot_duration'])?$val[$base_table]['ot_duration']:'';

            $status = '';
            $status_color = 'black';

 
            //edited by sinsiya on 04-11-2024
//if(!empty($arr_output['att_in_time'])){
            if (!empty($val[$base_table]['weekoff'])) {
                //$status = $val[$base_table]['weekoff'];
                $status_color = "black";
            } else if (!empty($val[$base_table]['present'])) {
                //$status = $val[$base_table]['present'];
                if (in_array(strtoupper($val[$base_table]['present']), array('A/A', 'P/A', 'A/P'))) {
                    $status_color = 'red';
                } else if (strtoupper($val[$base_table]['present']) == 'P/P') {
                    $status_color = 'green';
                }
            }/* else if(!empty($val[$base_table]['holiday'])){
              $status = $val[$base_table]['holiday'];
              $status_color = "blue";
              }else if(!empty($val[$base_table]['leaves'])){
              $status = $val[$base_table]['leaves'];
              $status_color = "black";
              }else if(!empty($val[$base_table]['others'])){
              $status = $val[$base_table]['others'];
              $status_color = "black";
              }else{
              $status = $val[$base_table]['others'];
              $status_color = "black";
              } */ else {
                $status_color = "black";
            }
             //edited by sinsiya on 22-11-2024
            if(!empty($arr_output['att_in_time'])){
             $status = $val[$base_table]['present'] . ' ' .
                $val[$base_table]['holiday'] . ' ' .
                //      $val[$base_table]['leaves'] . ' ' .
                $val[$base_table]['weekoff'] . ' ' .
                $val[$base_table]['others'];
            }else{
                  $present_status = strtoupper($val[$base_table]['present']) == 'A/A' ? '' : $val[$base_table]['present'];

              $status = trim($present_status . ' ' .
                       $val[$base_table]['holiday'] . ' ' .
                       $val[$base_table]['weekoff'] . ' ' .
                       $val[$base_table]['others']);

              $arr_output['status'] = $status;
              $arr_output['status_color'] = 'black'; // Set default or dynamic color
            }


            $arr_output['status'] = $status;
            // debug($arr_output);
            $arr_output['status_color'] = $status_color;
//}else{
   // $arr_output['status'] ='';
  //  $arr_output['status_color'] ='';
//}

            $arr_output['editable'] = !empty($iseditable) ? true : false;

            $resp_mispunches["rows"][] = $arr_output;
            // $resp_mispunches["rows"][] = $arr_outputs;
        }

        $resp_mispunches["total"] = $count;
        $resp_mispunches["message"] = "Employee Attendance Retrieved Successfully ";
        $resp_mispunches["type"] = "success";
        echo json_encode($resp_mispunches);
        // debug($resp_mispunches);
        // 
    }
    }

      public function listpunchesnew()
    {
        $this->autoRender = FALSE;
                $company_code = strtoupper($this->Session->read('company_code'));
		$this->set("company_code", $company_code);
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
        //edited by athira on 11-02-2026
			$restrictedCompanies = [
   'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK',
    'GTRA','VGNN','SHYD','SRTS'
		];

		if (!in_array($company_code, $restrictedCompanies)) {
             $base_table = "emp_detail_timeattandance";
    $emp = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : 0;
    if ($emp == 0) return;

    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    $this->DbConfig->useDbConfig = $this->Session->read('ds');
    $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
    $company_code = $this->Session->read('company_code');

    // Employee basic details
    $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM emp_details WHERE emp_id = '$emp'");
    $emp_pkey = $arr_emp_pkey[0]['emp_details']['emp_pkey'];

    $have_shift=$this->EmployeeDetails->query("SELECT day_time_seq FROM emp_proff WHERE emp_fkey='$emp_pkey'");
    $shift_id=$have_shift[0]['emp_proff']['day_time_seq'];
    
  
    $branch_code = $arr_emp_pkey[0]['emp_details']['branch_code'] ;
    $monthdd = $_REQUEST['month'];
    $yearmonth = $monthdd . '-01';

    $limit = $_REQUEST['rows'];
    $page = $_REQUEST['page'];
    $ofst = ($page - 1) * $limit;

    $resp_mispunches = ["rows" => [], "data" => [], "out" => []];

    if ($emp_pkey == 0) return;

    $condition = "$base_table.emp_pkey = '$emp_pkey' AND ";

    // Attendance start and end date
    $att_start_date = $this->EmployeeDetails->query("SELECT att_start_end_fn('$yearmonth', 1) AS start_date");
    $att_end_date = $this->EmployeeDetails->query("SELECT att_start_end_fn('$yearmonth', 2) AS end_date");
    $attendance_start = $att_start_date[0][0]['start_date'];
    $attendance_end = $att_end_date[0][0]['end_date'];

// Fetch only attendance within the calculated date range
$dates = $this->EmployeeDetails->query("
    SELECT DISTINCT SHIFTDATE
    FROM device_attandance
    WHERE emp_id = '$emp'
      AND SHIFTDATE BETWEEN '$attendance_start' AND '$attendance_end' AND status='Y'
    ORDER BY SHIFTDATE
");

 // Check editable status
    $arr_attendance_register = $this->EmployeeDetails->query("
        SELECT COUNT(*) AS count 
        FROM attendance_register 
        WHERE emp_fkey = $emp_pkey 
        AND month_year = '" . date('Y-m', strtotime($monthdd)) . "' 
        AND isdelete = 'Y'
    ");
    $iseditable = $arr_attendance_register[0][0]['count'];

// Loop through dates and call function for each
if($iseditable == '1'){
foreach ($dates as $row) {
    $shift_date = $row['device_attandance']['SHIFTDATE'];

    $this->EmployeeDetails->query("
        SELECT time_duration_check('$shift_date', '$emp_pkey', '$branch_code')
    ");
}
}
    // Build date range
    $period = [];
    $current = strtotime($attendance_start);
    $end = strtotime($attendance_end);
    while ($current <= $end) {
        $period[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }
   

    // ✅ Fetch all attendance data for that employee in one go
    $attendances = $this->EmployeeDetails->query("
        SELECT 
            empdetails.emp_id, 
            empdetails.first_name, 
            empdetails.last_name, 
            $base_table.*, 
            wd.minuts_calc_perday, 
            COALESCE(wd_shift.day_time_desc, wd.day_time_desc) AS shift_name,
            emp.emp_fkey, emp.joining_date
        FROM $base_table
        LEFT JOIN emp_details AS empdetails 
            ON empdetails.emp_pkey = $base_table.emp_pkey 
        LEFT JOIN emp_proff AS emp 
            ON emp.emp_fkey = empdetails.emp_pkey 
        LEFT JOIN working_day_time_procedures AS wd 
            ON wd.day_time_seq = emp.day_time_seq 
        LEFT JOIN (
            SELECT d1.emp_id, d1.SHIFTDATE, d1.SHIFT
            FROM device_attandance d1
            INNER JOIN (
                SELECT emp_id, SHIFTDATE, MAX(LOGDATE) AS max_logdate
                FROM device_attandance
                GROUP BY emp_id, SHIFTDATE
            ) d2 
                ON d1.emp_id = d2.emp_id 
                AND d1.SHIFTDATE = d2.SHIFTDATE 
                AND d1.LOGDATE = d2.max_logdate
        ) AS da 
            ON da.emp_id = empdetails.emp_id 
            AND da.SHIFTDATE = $base_table.att_date
        LEFT JOIN working_day_time_procedures AS wd_shift 
            ON wd_shift.day_time_seq = da.SHIFT
        WHERE $condition 
              $base_table.att_date BETWEEN '$attendance_start' AND '$attendance_end'
        ORDER BY $base_table.att_date
    ");
    

    // Map attendance by date
    $attendance_map = [];
    foreach ($attendances as $row) {
        $attendance_map[$row[$base_table]['att_date']] = $row;
    }

    // Generate grid rows
    foreach ($period as $att_date) {
        if (isset($attendance_map[$att_date])) {
            
            $val = $attendance_map[$att_date];
            $shift_name = !empty($val[$base_table]['present']) ? $val[0]['shift_name'] : '';
            $arr_output = [
                'att_date' => ($company_code == 'KWMT') ? date('d-m-Y', strtotime($att_date)) : $att_date,
                'emp_id' => $val['empdetails']['emp_id'],
                'att_in_time' => $val[$base_table]['att_in_time'] ,
                'att_out_time' => $val[$base_table]['att_out_time'] ,
                'duration' => $val[$base_table]['duration'] ,
                'leaves' => $val[$base_table]['leaves'] ,
                'isdelete' => $val[$base_table]['isdelete'] ,
                 'joining_date' => $val['emp']['joining_date'] ,
                
                'ad_present' => $val[$base_table]['ad_present'] ,
                'ad_remarks' => $val[$base_table]['ad_remarks'] ,
                'status' => trim(
                    ($val[$base_table]['present'] ) . ' ' .
                    ($val[$base_table]['holiday'] ) . ' ' .
                    ($val[$base_table]['weekoff'] ) . ' ' .
                    ($val[$base_table]['others'] )
                ),
                'shift_name' => $shift_name ,
                'joining_date' => $val['emp']['joining_date'] ,
                'status_color' => !empty($val[$base_table]['present']) && in_array(strtoupper($val[$base_table]['present']), ['A/A', 'P/A', 'A/P'])
                    ? 'red'
                    : ((strtoupper($val[$base_table]['present'] ) == 'P/P') ? 'green' : 'black'),
                'editable' => !empty($iseditable),
            ];
        } else {
            // Empty day record
            $arr_output = [
                'att_date' => ($company_code == 'KWMT') ? date('d-m-Y', strtotime($att_date)) : $att_date,
                'emp_id' => $emp,
                'att_in_time' => '',
                'att_out_time' => '',
                'duration' => '',
                'status' => '',
                'status_color' => 'black',
                'editable' => !empty($iseditable),
                'leaves' => '',
                'shift_name' => ''
            ];
        }

        $resp_mispunches["rows"][] = $arr_output;
        }

    $total = count($resp_mispunches["rows"]);

   if (empty($shift_id)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'type' => 'error',
        'message' => "No shift allocated for this employee",
        'rows' => [],
        'total' => 0,
    ]);
    return;
}

$this->autoRender = false;
$resp_mispunches= [
    'rows'  => array_values($resp_mispunches["rows"]),
    'data' => array(),
    'type' => 'success',
    'total' => "$total",
    'message' => "Employee Attendance Retrieved Successfully"
    
];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($resp_mispunches);
        }else{
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
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = $monthdd;
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
        // if ($this->Session->read('emp_fkey')) {
        //     $emp_pkeys = $this->Session->read('emp_fkey');
        //     $useracess = $this->EmployeeDetails->query("select * from user_access as Useraccess where user_fkey = '$emp_pkeys' and menu_id = '0' and active = 'Y'");
        //     $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
        //     if ($access == 'Y') {
        //         $emp_condition = ""; // "emp.attr1 = '$emp_pkeys' and ";
        //     } else {
        //         $emp_condition = "";
        //     }
        // } else {
            $emp_condition = "";
       // }
       	
        if (!$shiftdetailed = $this->EmployeeDetails->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey')")) {
            $resp_mispunches["total"] = "0";
            $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
            $resp_mispunches["type"] = "danger";
            echo json_encode($resp_mispunches);
            //            return FALSE;
            die();
        }

        //debug($shiftdetailed);
        if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
            if (!$this->EmployeeDetails->query("SELECT time_duration_check_multishift('$yearmonth', '$emp_pkey', '$branch_code')")) {
                $resp_mispunches["total"] = "0";
                $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
                $resp_mispunches["type"] = "danger";
                echo json_encode($resp_mispunches);
                //                return false;
                die();
            }
        } else {
            if (!$this->EmployeeDetails->query("SELECT time_duration_check('$yearmonth', '$emp_pkey', '$branch_code')")) {
                $resp_mispunches["total"] = "0";
                $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
                $resp_mispunches["type"] = "danger";
                echo json_encode($resp_mispunches);
                //                return false;
                die();
            }
        }
        $attendances_count = $this->EmployeeDetails->query("select count(*) AS count from $base_table left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where $emp_condition $condition yearmonth = '$yearmonth' order by att_date ");
        $count = isset($attendances_count[0][0]['count']) ? $attendances_count[0][0]['count'] : 0;

        $attendances = $this->EmployeeDetails->query("select 
			empdetails.emp_id, 
			empdetails.first_name, 
			empdetails.last_name, 
			$base_table.*, 
			wd.minuts_calc_perday, 
			emp.emp_fkey,isdelete,emp.joining_date
			FROM 
			$base_table 
			left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
			left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
			left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
            where $emp_condition $condition yearmonth = '$yearmonth' 
            order by att_date 
			LIMIT $ofst, $limit
		");

        $arr_attendance_register = $this->EmployeeDetails->query("select count(*) as count from attendance_register where emp_fkey = $emp_pkey AND month_year = '" . date('Y-m', strtotime($month)) . "' AND isdelete = 'Y'");
        $iseditable = isset($arr_attendance_register[0][0]['count']) ? $arr_attendance_register[0][0]['count'] : 0;

        $employee_attendance = array();
        // continue;
        foreach ($attendances as $val) {
            
            $arr_output['emp_id'] = isset($val['empdetails']['emp_id']) ? $val['empdetails']['emp_id'] : '';
            $arr_output['leaves'] = isset($val['emp_detail_timeattandance']['leaves']) ? $val['emp_detail_timeattandance']['leaves'] : '';
            //Edited by Akshay on 15-5-2024
            if($company_code == 'KWMT'){
                $arr_output['att_date'] = isset($val[$base_table]['att_date']) ? date('d-m-Y', strtotime($val[$base_table]['att_date'])) : ''; //edited by sinsiya
            }else{
                $arr_output['att_date'] = isset($val[$base_table]['att_date']) ? $val[$base_table]['att_date'] : '';
            }
            //End
            $arr_output['att_in_time'] = isset($val[$base_table]['att_in_time']) ? $val[$base_table]['att_in_time']/* date("h:i:s A", strtotime($val[$base_table]['att_in_time'])) */ : '';
            //edited by sinsiya on 04-11-2024
            if(!empty($arr_output['att_in_time'])){
            $arr_output['att_out_time'] = isset($val[$base_table]['att_out_time']) ? $val[$base_table]['att_out_time']/* date("h:i:s A", strtotime($val[$base_table]['att_out_time'])) */ : '';
           }else{
               $arr_output['att_out_time'] = "";
           }   
       
           if(!empty($arr_output['att_in_time'])){
                $arr_output['duration'] = isset($val[$base_table]['duration']) ? $val[$base_table]['duration'] : '';
                 }else{
               $arr_output['duration'] = "";
           } 
            //}
            $arr_output['ad_shift_string'] = isset($val[$base_table]['AD_shift_string']) ? $val[$base_table]['AD_shift_string'] : '';
            $arr_output['ad_present'] = isset($val[$base_table]['ad_present']) ? $val[$base_table]['ad_present'] : '';
            $arr_output['ad_in_time'] = isset($val[$base_table]['ad_in_time']) ? $val[$base_table]['ad_in_time'] : '';
            $arr_output['ad_out_time'] = isset($val[$base_table]['ad_out_time']) ? $val[$base_table]['ad_out_time'] : '';
            $arr_output['ad_duration'] = isset($val[$base_table]['ad_duration']) ? $val[$base_table]['ad_duration'] : '';
            $arr_output['ad_remarks'] = isset($val[$base_table]['ad_remarks']) ? $val[$base_table]['ad_remarks'] : '';
            $arr_output['site_transactions_fkey'] = isset($val[$base_table]['site_transactions_fkey']) ? $val[$base_table]['site_transactions_fkey'] : '';
            $arr_output['isdelete'] = isset($val[$base_table]['isdelete']) ? $val[$base_table]['isdelete'] : '';
            $arr_output['joining_date'] = isset($val['emp']['joining_date']) ? $val['emp']['joining_date'] : '';

            $status = '';
            $status_color = 'black';

//if(!empty($arr_output['att_in_time'])){
            if (!empty($val[$base_table]['weekoff'])) {
                //$status = $val[$base_table]['weekoff'];
                $status_color = "black";
            } else if (!empty($val[$base_table]['present'])) {
                //$status = $val[$base_table]['present'];
                if (in_array(strtoupper($val[$base_table]['present']), array('A/A', 'P/A', 'A/P'))) {
                    $status_color = 'red';
                } else if (strtoupper($val[$base_table]['present']) == 'P/P') {
                    $status_color = 'green';
                }
            }else {
                $status_color = "black";
            }
             //edited by sinsiya on 22-11-2024
            if(!empty($arr_output['att_in_time'])){
             $status = $val[$base_table]['present'] . ' ' .
                $val[$base_table]['holiday'] . ' ' .
                //      $val[$base_table]['leaves'] . ' ' .
                $val[$base_table]['weekoff'] . ' ' .
                $val[$base_table]['others'];
            }else{
                  $present_status = strtoupper($val[$base_table]['present']) == 'A/A' ? '' : $val[$base_table]['present'];

              $status = trim($present_status . ' ' .
                       $val[$base_table]['holiday'] . ' ' .
                       $val[$base_table]['weekoff'] . ' ' .
                       $val[$base_table]['others']);

              $arr_output['status'] = $status;
              $arr_output['status_color'] = 'black'; // Set default or dynamic color
            }


            $arr_output['status'] = $status;
            // debug($arr_output);
            $arr_output['status_color'] = $status_color;
//}else{

            $arr_output['editable'] = !empty($iseditable) ? true : false;

            $resp_mispunches["rows"][] = $arr_output;
            // $resp_mispunches["rows"][] = $arr_outputs;
        }

        $resp_mispunches["total"] = $count;
        $resp_mispunches["message"] = "Employee Attendance Retrieved Successfully ";
        $resp_mispunches["type"] = "success";
        echo json_encode($resp_mispunches);
      
    }
    }
     public function editpunch($att_date = '', $emp_id = '', $site_t_fkey = '',  $att_in_time = '', $att_out_time = '')
    {
        $this->layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code'));
        // added by nimisha rajesh on 30/05/2019 issue - new button showing in edit puches when emp = heirarchy

        $emp_fkey = $this->Session->read('emp_fkey');
        $empid = $this->EmployeeDetails->query("SELECT `emp_id` FROM `emp_details` WHERE `emp_pkey` = '$emp_fkey'");
        //edited by sinsiya on 15-03-2024 for admin split when givving to live please add ||$company_code=='DEMO'
        $id = isset($empid['0']['emp_details']['emp_id']) ? $empid['0']['emp_details']['emp_id'] : '';
        $user_group=$this->Session->read('user_group');
        //        debug($id);
        if ($user_group == 2) {
            $shownewbutton = 'no';
        } else {
            $shownewbutton = 'yes';
        }
        $this->set('shownewbutton', $shownewbutton);
        
        // End
//end of admin split
        $site_detailss = array();
        if (($site_t_fkey != 0)) {
            $site_detailss = $this->EmployeeDetails->query("select * from site_transactions left join designation on (designation.id = site_transactions.designation_id) left join site on (site.site_pkey = site_transactions.site_fkey) where site_transactions_pkey = '$site_t_fkey' ");
        }
        
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $useracess = $this->EmployeeDetails->query("select * from user_access as Useraccess where user_fkey = '$emp_pkeys' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            //edited by sinsiya 15-03-2024 for admin split
             if($company_code=='VGFS'){
            if ($access == 'Y') {
                $empmode = 1; //Hierarchy
            } else {
                $empmode = 0; //employee
            }
          }
          else{
              $empmode = 1;
          }
        } else {
            $empmode = 2; //admin
        }
        //end
        $user_group=$this->Session->read('user_group');
        $this->set('user_group',$user_group);
        $this->set('empmode', $empmode);
        $this->set('att_date', $att_date);
        $this->set('emp_id', $emp_id);
        $this->set('att_in_time', $att_in_time);
        $this->set('site_detailss', $site_detailss);
        $this->set('att_out_time', $att_out_time);
 //edited by athira on 06-11-2025
                $company_code = strtoupper($this->Session->read('company_code'));
		$this->set("company_code", $company_code);
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
        //edited by athira on 11-02-2026
			$restrictedCompanies = [
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK',
    'GTRA','VGNN','SHYD','SRTS'
		];

		if (!in_array($company_code, $restrictedCompanies)) {
            $this->render('editpunch_new');
        }
        //end
    }

    public function listpunchesbydate() {
        $this->autoRender = FALSE;

        $employee = isset($_REQUEST['empid']) ? $_REQUEST['empid'] : 0;
        $att_date = isset($_REQUEST['att_date']) ? $_REQUEST['att_date'] : '';
        $att_in_time = isset($_REQUEST['att_in_time']) ? $_REQUEST['att_in_time'] : '';
        $att_out_time = isset($_REQUEST['att_out_time']) ? $_REQUEST['att_out_time'] : '';
        $includeinactive = isset($_REQUEST['includeinactive']) ? $_REQUEST['includeinactive'] : '';


        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();

        $condition = array();

        if ($includeinactive) {
            if ($includeinactive == 'N') {
                $condition['status'] = array('Y');
            } else {
                $condition['status'] = array('Y', 'N');
            }
        } else {
            $condition['status'] = array('Y');
        }
        if ($employee) {
            $condition['emp_id'] = $employee;
        }
        if ($att_date) {
            //$condition['MONTH(LOGDATE)'] = $month;
            //$condition['DATE_FORMAT(LOGDATE,"%Y-%m-%d")'] = $att_date;
            if (!empty($att_in_time) && !empty($att_out_time)) {
                $condition[] = "DATE_FORMAT(LOGDATE,'%Y-%m-%d') BETWEEN '$att_in_time' AND '$att_out_time'";
            } else {
                //edited by athira on 06-11-2025
                $company_code = strtoupper($this->Session->read('company_code'));
		$this->set("company_code", $company_code);
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
        //edited by athira on 11-02-2026
			$restrictedCompanies = [
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK',
    'GTRA','VGNN','SHYD','SRTS'
		];

		if (!in_array($company_code, $restrictedCompanies)) {
        $condition['DATE_FORMAT(SHIFTDATE,"%Y-%m-%d")'] = $att_date;
        }else{
         $condition['DATE_FORMAT(LOGDATE,"%Y-%m-%d")'] = $att_date;
        }  
            }
        }

        $count = 0;
        if ($employee) {
            $this->EditPunches->useDbConfig = $this->Session->read('ds');
            $count = $this->EditPunches->find("count", array('conditions' => $condition));
            $arr_mispunches = $this->EditPunches->find("all", array(
                'conditions' => $condition,
                'order' => array(
                    'EditPunches.LOGDATE'),
                'limit' => intval($limit),
                'offset' => intval($ofst)
                    )
            );

            foreach ($arr_mispunches as $key => $value) {
                $resp_mispunches["rows"][$key] = $value["EditPunches"];
            }
        }
        $resp_mispunches["total"] = $count;
        echo json_encode($resp_mispunches);
    }

    //Ends

    public function Updateame() {
        $this->layout = null;
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
         //edited by athira on 06-11-2025
        $company_code = strtoupper($this->Session->read('company_code'));
		$this->set("company_code", $company_code);
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
        //edited by athira on 11-02-2026
			$restrictedCompanies = [
   'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK',
    'GTRA','VGNN','SHYD','SRTS'
		];

		if (!in_array($company_code, $restrictedCompanies)) {
        $emp_id = $arr_data['emp'];
        $month = $arr_data['month'].'-01';
        $get_emp = $this->EditPunches->query("select emp_pkey,branch_code from emp_details where emp_id= '$emp_id' and status = '1' ");
        $emp_pkey = $get_emp['0']['emp_details']['emp_pkey'];
        $branch_code = isset($get_emp['0']['emp_details']['branch_code']) ? $get_emp['0']['emp_details']['branch_code'] : 'NULL';
       
       $att_start_date = $this->EditPunches->query("SELECT att_start_end_fn('$month', 1) AS start_date");
$att_end_date   = $this->EditPunches->query("SELECT att_start_end_fn('$month', 2) AS end_date");

$start_date = $att_start_date[0][0]['start_date'];
$end_date   = $att_end_date[0][0]['end_date'];

// Fetch only attendance within the calculated date range
$dates = $this->EditPunches->query("
    SELECT DISTINCT SHIFTDATE
    FROM device_attandance
    WHERE emp_id = '$emp_id'
      AND SHIFTDATE BETWEEN '$start_date' AND '$end_date' AND status='Y'
    ORDER BY SHIFTDATE
");

if (empty($dates)) {
    echo json_encode(['success' => false, 'message' => 'No attendance found for this period']);
    return;
}

// Loop through dates and call function for each
foreach ($dates as $row) {
    $shift_date = $row['device_attandance']['SHIFTDATE'];
     
    $this->EditPunches->query("
        SELECT time_duration_check('$shift_date', '$emp_pkey', '$branch_code')
    ");
}
        }else{

        $emp_id = $arr_data['emp'];
        $month = $arr_data['month'] . '-01';
        $get_emp = $this->EditPunches->query("select emp_pkey from emp_details where emp_id= '$emp_id' and status = '1' ");
        $emp_pkey = $get_emp['0']['emp_details']['emp_pkey'];
        $branch_code = isset($get_emp['0']['emp_details']['branch_code']) ? $get_emp['0']['emp_details']['branch_code'] : 'NULL';
        $month = ($month != '') ? date('Y-m', strtotime($month)) . '-01' : ''; // Edited by Akshay on 26-5-2025
        $deleterecords = $this->EditPunches->query("delete from emp_detail_timeattandance where emp_pkey='$emp_pkey' and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m') ) ");
        if (!$shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )")) {
            return FALSE;
            die();
        }
        if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
            if (!$this->EditPunches->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")) {
                return false;
                die();
            }
        } else {
            if (!$this->EditPunches->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")) {
                return false;
                die();
            }
        }
    }
    }

    public function Updateamendmens() {
        $this->layout = null;
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        $branch_code = $arr_data['brn'];
        $month = $arr_data['month'] . '-01';
        $get_emp = $this->EditPunches->query("select emp_pkey from emp_details where branch_code= '$branch_code' and status = '1' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq is not null)");

$specialCompanies = [
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK',
    'GTRA','VGNN','SHYD','SRTS'
];
$company_code = $this->Session->read('company_code');
        //die();
        //debug($get_emp);
        foreach ($get_emp as $value) {
            //debug($value);
            $emp_pkey = isset($value['emp_details']['emp_pkey']) ? $value['emp_details']['emp_pkey'] : 0;
            // $deleterecords = $this->EditPunches->query("delete from  emp_detail_timeattandance where emp_pkey in ('$emp_pkey')  and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m')) ");
            if (in_array($company_code, $specialCompanies)) {

    $deleterecords = $this->EditPunches->query("
        DELETE FROM emp_detail_timeattandance
        WHERE emp_pkey IN ('$emp_pkey')
        AND yearmonth = '$month'
        AND emp_pkey NOT IN (
            SELECT emp_fkey
            FROM attendance_register
            WHERE isdelete = 'N'
            AND month_year = DATE_FORMAT('$month','%Y-%m')
        )
    ");

}


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

    public function form($empid = 0, $att_date = '', $site_t_fkey = '' ) {
        $this->layout = null;
        $this->set("empid", $empid);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $site_detailss = array();

        if(($site_t_fkey != 0)){
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
        $leave ='';
        $sessions = '';
        $remarks = '';
        $statuss ='';
        $items = '';
        $half ='';
        $h_leave = array();
        foreach ($arr_leave as $value) {
          $status = isset($value['emp_leave_transactions']['Leavestatus']) ? $value['emp_leave_transactions']['Leavestatus'] : ''; 
          $remark = isset($value['emp_leave_transactions']['Remarks']) ? $value['emp_leave_transactions']['Remarks'] : '';  
          $session = isset($value['emp_leave_transactions']['leave_session']) ? $value['emp_leave_transactions']['leave_session'] : ''; 
          $item = isset($value['sal']['item']) ? $value['sal']['item'] : '';
          if($session ==3){
            $remark = "Full day";
          }

          $leave .= "Cannot add attendance ," .$item.' '.$status.' on '.$remark."";
          $sessions .= $session;
          if($session == 1){
            $half = "First half";
          }
          if($session == 2){
            $half = "Second half";
          }
          $remarks .=  $remark;
          $statuss .= $status;
          $items .= $item;
          $h_leave[] = $half.' '.$item.' '.$status;
          }
        $count = isset($arr_leave_exists[0][0]['COUNT']) ? $arr_leave_exists[0][0]['COUNT'] : 0;
   
        $this->set("leave" , $leave);
        $this->set("statuss" ,$statuss);
        $this->set("items" ,$items);
        $this->set("h_leave" ,$h_leave);
        $this->set("sessions" ,$sessions);
        $this->set("remarks" ,$remarks);
        $this->set("count" , $count);
        $this->set("att_date", $att_date);
        $this->set("site_t_fkey", $site_t_fkey);
        $this->set("diff_half_count",$diff_half_count);
    }


    public function remove() {
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
    public function insert_func($tableName = '', $data = array()){
      $this->EditPunchesHist->UseDbCnfig = $this->Session->read('ds');
      $fields = "INSERT INTO $tableName (";
      $values = " VALUES (";
      foreach ($data as $key => $value) {
        # code...
        $fields .= $key;
        $values .= "'".$value."'";
        end($data);
        if ($key != key($data)){
          $fields .= ",";
          $values .= ",";
        }
      }
      $fields .= ")";
      $values .= ")";

      return $fields." ".$values;
    }

    public function savenew() {
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EditPunchesHist->UseDbCnfig = $this->Session->read('ds');
        
        $resp = array();
        $data = array();
        $data["DIRECTION"] = $data["C1"] = $_POST["C1"];
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
        $data["company_code"] = $this->Session->read('company_code');
        $data["created_by"] = $this->Session->read('login_user_id');
       try{
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
        $arr_s_half_day =  $this->EmployeeDetails->query("select count(*) as COUNT  from emp_leave_transactions where leave_session = '2' and leave_date ='$leavedate' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') 
                                                           and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = '$empkey')");
       
        $f_half_count = isset($arr_f_half_day[0][0]['COUNT']) ? $arr_f_half_day[0][0]['COUNT'] : 0;
        $s_half_count = isset($arr_s_half_day[0][0]['COUNT']) ? $arr_s_half_day[0][0]['COUNT'] : 0;
        $leave ='';
        foreach ($arr_leave as $value) {
          $status = isset($value['emp_leave_transactions']['Leavestatus']) ? $value['emp_leave_transactions']['Leavestatus'] : ''; 
          $remark = isset($value['emp_leave_transactions']['Remarks']) ? $value['emp_leave_transactions']['Remarks'] : '';  
          $leave .= $remark.'-'.$status;
        }

        $count = isset($arr_leave_exists[0][0]['COUNT']) ? $arr_leave_exists[0][0]['COUNT'] : 0;
        
        if($count > 0){
            $resp["success"] = false;
            $resp['msg'] = "Cannot add attendance, Leave Exists in this Date.";
            return json_encode($resp);
        }
       
        date_default_timezone_set("Asia/Calcutta");
        $curtime = time();
        $time = strtotime($data["LOGDATE"]);
      //debug($data);
         if ($curtime > $time)
        {
           try{
            $this->EditPunches->save($data);
            }catch (RuntimeException $e) {
              $resp["success"] = false;
             $resp['msg']="Cannot add duplicate attendance for the same date.";
             return json_encode($resp);
           }
      //     debug($data);
            // $this->EditPunchesHist->save($data);
            // amal->
          $data["status"] = 'Y';
          $data["action"] = 'insert';
          $this->EmployeeDetails->query($this->insert_func("device_attandance_hist",$data));
             
            // <-amal
            $resp["success"] = true;
            $resp['msg'] = "New Attendance saved successfully";
            return json_encode($resp);
           
        }  else {
            $resp["success"] = false;
            $resp['msg'] = "Cannot add attendance to upcoming dates.";
            return json_encode($resp);
        }
        } catch (RuntimeException $e) {
              $resp["success"] = false;
             $resp['msg']="Cannot add duplicate attendance for the same date.";
             return json_encode($resp);
       }
       //End Nimisha 18/03/2019
        
    }
public function savepunch() {

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
         if ($action == 'Y'){
        $actions = 'Active';
      }
      else{
        $actions = 'Inactive';
      }
     
      if($direction == 'in' ){
              $dire = 'In';
      }
      else{
        $dire = 'Out';
      }
      if($action == 'Y'){
        $out =$actions ;
      }
      else{
          $out = $actions;
      }
       $data['action'] = $out;
       $data['created_by'] = $this->Session->read('login_user_id');

        $this->EmployeeDetails->query($this->insert_func("device_attandance_hist",$data));
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
    public function getmonths() {
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

    public function sendmemo() {



        $Email = new CakeEmail();
        $Email->from(array('sruthi.pb@gmail.com' => 'My Site'));
        $Email->to('sruthiforsight@gmail.com');
        $Email->subject('About');
        $Email->send('My message');
//debug($Email);
        echo "sucess";
    }

public function updateShiftDate() {
    $this->autoRender = false;
      $this->EditPunches->useDbConfig = $this->Session->read('ds');

    $device_attandance_seq = $this->request->data('device_attandance_seq');
    $shiftdate = $this->request->data('shiftdate');
     $empid = $this->request->data('empid');
     $emp_data=$this->EditPunches->query("SELECT emp_pkey,branch_code FROM emp_details WHERE emp_id='$empid'");
     $emp_pkey=$emp_data[0]['emp_details']['emp_pkey'];
     $branch_code=$emp_data[0]['emp_details']['branch_code'];
     $old_shiftdate=$this->request->data('old_shiftdate');


    if ($device_attandance_seq && $shiftdate) {
        // Format date safely and escape values
        $device_attandance_seq = addslashes($device_attandance_seq);
        $shiftdate = addslashes($shiftdate);

        // Run manual SQL update
        $this->EditPunches->query("
            UPDATE device_attandance 
            SET shiftdate = '$shiftdate' 
            WHERE device_attandance_seq = '$device_attandance_seq'
        ");

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'msg' => 'Invalid data']);
    }

    $this->EditPunches->query("
        SELECT time_duration_check('$old_shiftdate', '$emp_pkey', '$branch_code')
    ");
}

//end
 public function Syncame()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        //debug($arr_data);
        $emp_id = $arr_data['emp'];
        $month = $arr_data['month'] . '-01';
        $get_emp = $this->EditPunches->query("select emp_pkey,branch_code from emp_details where emp_id= '$emp_id' and status = '1' ");
        // debug($get_emp);
        $emp_pkey = $get_emp['0']['emp_details']['emp_pkey'];
        //debug($emp_pkey);
        $branch_code = isset($get_emp['0']['emp_details']['branch_code']) ? $get_emp['0']['emp_details']['branch_code'] : 'NULL';
        //  $deleterecords = $this->EditPunches->query("delete from emp_detail_timeattandance where emp_pkey='$emp_pkey' and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m') ) ");
        //  if (!$shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )")) {
        // return FALSE;
        //   die();
        //  }
        if ($f = $this->EditPunches->query("SELECT device_logs_resync_fn('$emp_id' , '$month')")) {
            // debug($f);
            return false;
            die();
        }
    }
    // End

    //edited by athira on 16-02-2026
//     function SyncAttendance() {

//     $this->layout = null;
//     $this->autoRender = false;
//     $this->EditPunches->useDbConfig = $this->Session->read('ds');

//     // ✅ Current month first day (YYYY-MM-01)
//     $month = date('Y-m-01');

//     // ✅ Get all active employees
//     $employees = $this->EditPunches->query("
//         SELECT emp_id 
//         FROM emp_details 
//         WHERE status = '1'
//     ");

//     if (!empty($employees)) {

//         foreach ($employees as $emp) {

//             $emp_id = $emp['emp_details']['emp_id'];

//             // Call your DB function
//             $this->EditPunches->query("
//                 SELECT device_logs_iteration_fn('$emp_id', '$month')
//             ");
//         }
//     }

//     return true;
// }
public function SyncAttendance() {

    $this->layout = null;
    $this->autoRender = false;

    $this->EditPunches->useDbConfig = $this->Session->read('ds');

    try {

        $month = date('Y-m-01');

        $employees = $this->EditPunches->query("
            SELECT emp_id 
            FROM emp_details 
            WHERE status = '1'
        ");

        if (!empty($employees)) {

            foreach ($employees as $emp) {

                $emp_id = $emp['emp_details']['emp_id'];

                $this->EditPunches->query("
                    SELECT device_logs_iteration_fn('$emp_id', '$month')
                ");
            }
        }

        echo json_encode([
            "status" => 200,
            "message" => "Attendance synced successfully."
        ]);

    } catch (Exception $e) {

        echo json_encode([
            "status" => 500,
            "message" => "Sync failed. Please try again."
        ]);
    }

    exit;
}


//end
}
