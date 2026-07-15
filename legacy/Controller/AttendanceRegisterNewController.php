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
ini_set('memory_limit', '-1');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class AttendanceRegisterNewController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'AttendanceRegisterNew';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Units', 'AttendanceRegister', 'DbConfig', 'EditPunches', 'LeaveRequests', 'RegisterHistory');
    public $components = array('MasterdataManagement');

    public function indexneww()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $user_fkey = $this->Session->read('emp_fkey');
        if (!$user_fkey)
            $user_fkey = 0;
        // edited by bindhu 19-02-2026
        $user_group = $this->Session->read('user_group');
        $this->set('userGroup', $user_group);
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        // edited by bindhu 19-02-2026 end
        $company_code = strtoupper($this->Session->read('company_code'));

        $is_glet = (strpos($company_code, 'GLET') !== false);

        if (in_array($company_code, ['GLET', 'GAAR', 'ETNA', 'MNDM', 'FYNK', 'TUDS', 'IFHY', 'STFR', 'IMSC', 'ELKT', 'TSSM', 'STPN', 'FRSG', 'BNGL', 'CKWR', 'DVDS', 'INFR', 'LBLD', 'LNTT'])) {
            // New Hierarchy Logic for GLET
            $feature_fkey = $this->Session->read('current_feature_id');
            if (!$feature_fkey)
                $feature_fkey = 33;

            if ($user_group == 2) {
                // 1. Fetch access settings
                $accessResults = $this->EmployeeDetails->query("
                    SELECT branch_fkey, is_hierarchy 
                    FROM user_feature_branch_access 
                    WHERE user_fkey = ? AND feature_fkey = ? AND LCASE(active) = 'y'
                ", array($user_fkey, $feature_fkey));

                $allocated_branches = array();
                $is_hierarchy = 'N';

                if (!empty($accessResults)) {
                    foreach ($accessResults as $row) {
                        $allocated_branches[] = $row['user_feature_branch_access']['branch_fkey'];
                        if (strtoupper($row['user_feature_branch_access']['is_hierarchy']) == 'Y') {
                            $is_hierarchy = 'Y';
                        }
                    }
                }

                // 2. Filter Branch Dropdown
                if ($is_hierarchy == 'Y') {
                    // HIERARCHY: Branches of subordinates
                    $branches = $this->Units->query("
                        SELECT id, branch_code, branch_name 
                        FROM branches 
                        WHERE status = 1 
                          AND branch_code IN (SELECT DISTINCT emp_branch FROM emp_proff WHERE attr1 = ? OR emp_fkey = ?)
                        ORDER BY branch_name ASC
                    ", array($user_fkey, $user_fkey));

                    $formatted_branches = array();
                    foreach ($branches as $b) {
                        $formatted_branches[] = array('Units' => $b['branches']);
                    }
                    $branches = $formatted_branches;
                } elseif (!empty($allocated_branches)) {
                    // ALLOCATED: Use specific branches
                    $branches = $this->Units->find('all', array(
                        'fields' => array('id', 'branch_code', 'branch_name'),
                        'conditions' => array('branch_code' => $allocated_branches, 'status' => 1),
                        'order' => 'branch_name ASC'
                    ));
                } else {
                    // FALLBACK: User's own branch
                    $empBranchInfo = $this->EmployeeDetails->query("SELECT emp_branch FROM emp_proff WHERE emp_fkey = ?", array($user_fkey));
                    $own_branch = isset($empBranchInfo[0]['emp_proff']['emp_branch']) ? $empBranchInfo[0]['emp_proff']['emp_branch'] : '';
                    $branches = $this->Units->find('all', array(
                        'fields' => array('id', 'branch_code', 'branch_name'),
                        'conditions' => array('branch_code' => $own_branch, 'status' => 1)
                    ));
                }

                $arr_branches = array();
                foreach ($branches as $row) {
                    $arr_branches[] = $row['Units'];
                }

                // 3. Filter Initial Employee List
                if ($is_hierarchy == 'Y') {
                    $results = $this->EmployeeDetails->query("
                        SELECT ed.emp_pkey, ed.first_name, ed.last_name, ep.emp_company_id 
                        FROM emp_details ed
                        JOIN emp_proff ep ON ep.emp_fkey = ed.emp_pkey
                        WHERE (ep.attr1 = ? OR ep.emp_fkey = ?) AND ed.status = 1
                        ORDER BY ed.first_name ASC
                    ", array($user_fkey, $user_fkey));
                } else {
                    $results = $this->EmployeeDetails->query("
                        SELECT ed.emp_pkey, ed.first_name, ed.last_name, ep.emp_company_id 
                        FROM emp_details ed
                        JOIN emp_proff ep ON ep.emp_fkey = ed.emp_pkey
                        WHERE ed.emp_pkey = ? AND ed.status = 1
                    ", array($user_fkey));
                }

                $arr_employees = array();
                foreach ($results as $row) {
                    $arr_employees[] = array_merge($row['ed'], $row['ep']);
                }
            } else {
                // Admin for GLET
                $branches = $this->Units->find('all', array(
                    'fields' => array('id', 'branch_code', 'branch_name'),
                    'conditions' => array('status' => 1),
                    'order' => 'branch_name ASC'
                ));
                $arr_branches = array();
                foreach ($branches as $row)
                    $arr_branches[] = $row['Units'];

                $employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1), 'order' => 'first_name ASC'));
                $arr_employees = array();
                foreach ($employees as $row)
                    $arr_employees[] = $row['EmployeeDetails'];
            }
        } else {
            // Legacy Logic for non-GLET
            if ($user_group == 2) {
                $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$user_fkey'");
            }
            if (($company_code == 'DEMO' || $company_code == 'BKHS') && $user_group == 2) {
                $conditions = array("status = 1 and branch_code in (select distinct(emp_branch) from emp_proff join emp_details on (emp_proff.emp_fkey=emp_details.emp_pkey) where (emp_proff.attr1 = " . $user_fkey . " OR emp_proff.emp_fkey = " . $user_fkey . ") and emp_details.status=1)");
                $branches = $this->Units->find('all', array('fields' => 'id,branch_code,branch_name', 'conditions' => array($conditions)));
                $arr_branches = array();
                foreach ($branches as $row)
                    $arr_branches[] = $row['Units'];
            } else {
                if (isset($payroUser[0]['emp_proff']['payro_priv'])) {
                    $branches = $this->MasterdataManagement->getBranchesListForCombo();
                } else {
                    $branches = $this->MasterdataManagement->getBranchesListForCombo($user_fkey);
                }
                $arr_branches = array();
                if (!empty($branches)) {
                    foreach ($branches as $row) {
                        $arr_branches[] = isset($row['Units']) ? $row['Units'] : $row;
                    }
                }
            }
            $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        }

        // Add "ALL" option to branches list for UI
        // array_unshift($arr_branches, array('branch_code' => '0', 'branch_name' => 'ALL'));

        $this->set('arr_branches', $arr_branches);
        $this->set('arr_employees', $arr_employees);
        $this->set('company_code', $company_code);
    }

    public function registerbook($monthdd = '', $emp_pkey = '', $branch = '', $isdelete = '', $skipProc = 'N')
    {
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $company_code = strtoupper($this->Session->read('company_code'));
        $user_login = $this->Session->read('login_user_id');
        // debug($user_login);
        $month = $monthdd;
        $yearmonth = $month . '-01';



        // ---- Get date range for attendance ----
        $att_startdate = $this->EmployeeDetails->query("SELECT att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 1) AS start_date");
        $att_enddate = $this->EmployeeDetails->query("SELECT att_start_end_fn(DATE_FORMAT('$yearmonth', '%Y-%m-01'), 2) AS end_date");
        $att_startdate2 = $att_startdate[0][0]['start_date'];
        $att_enddate2 = $att_enddate[0][0]['end_date'];

        $this->set('att_startdate2', $att_startdate2);
        $this->set('att_enddate2', $att_enddate2);

        // ---- Run Attendance Procedure ----
        if ($skipProc !== 'Y') {
            $this->EmployeeDetails->query("CALL insert_update_att_reg('$branch','$att_startdate2','$att_enddate2','$user_login',@Perr_msg)");
        }

        // Fetch approved LOP leave records for the cycle to distinguish Direct LOP in view
        $lop_leaves = $this->EmployeeDetails->query("
            SELECT leaveentries.EMP_fkey, emp_leave_transactions.leave_date, emp_leave_transactions.leave_session
            FROM emp_leave_transactions
            INNER JOIN leaveentries ON leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID
            INNER JOIN salary_head_items ON salary_head_items.salary_head_item_pkey = leaveentries.salary_head_item_fkey
            WHERE emp_leave_transactions.leave_date BETWEEN '$att_startdate2' AND '$att_enddate2'
              AND emp_leave_transactions.Leavestatus IN ('Authorized', 'Approved')
              AND salary_head_items.occurance = 'LOP'
        ");

        $lop_leave_map = [];
        foreach ($lop_leaves as $row) {
            $fkey = $row['leaveentries']['EMP_fkey'];
            $ldate = $row['emp_leave_transactions']['leave_date'];
            $sess = $row['emp_leave_transactions']['leave_session'];

            if (!isset($lop_leave_map[$fkey][$ldate])) {
                $lop_leave_map[$fkey][$ldate] = [];
            }
            $lop_leave_map[$fkey][$ldate][] = $sess;
        }
        $this->set('lop_leave_map', $lop_leave_map);

        // ---- Main conditions ----
        // edited by bindu 17-02-2026

        // $conditions = ["AttendanceRegister.month_year" => $month, "AttendanceRegister.isdelete" => $isdelete];
        // if ($emp_pkey != 0) $conditions["AttendanceRegister.emp_fkey"] = $emp_pkey;
        // if (!empty($branch)) $conditions["AttendanceRegister.branch_code"] = $branch;
        $conditions = [
            "AttendanceRegister.month_year" => $month,
            "AttendanceRegister.isdelete" => $isdelete,
            "empdetails.status" => 1
        ];

        // $conditions[] = [
        //     "OR" => [
        //         "termination.last_approved_working_date IS NULL",
        //         [
        //             "termination.status" => 1,
        //             "DATE_FORMAT(termination.last_approved_working_date, '%Y-%m-%d') >=" => $att_startdate2,
        //             "DATE_FORMAT(termination.last_approved_working_date, '%Y-%m-%d') <=" => $att_enddate2
        //         ]
        //     ]
        // ];
        $conditions[] = [
            "OR" => [
                "termination.last_approved_working_date IS NULL",
                "termination.last_approved_working_date >=" => $att_startdate2
            ]
        ];

        if ($emp_pkey != 0) {
            $conditions["AttendanceRegister.emp_fkey"] = $emp_pkey;
        } else {
            // edited by athira on 04-03-2026
            // When 'All' is selected, apply the same hierarchy/branch filter used in the employee dropdown (jsons())
            if (in_array($company_code, ['GLET', 'GAAR', 'ETNA', 'MNDM', 'FYNK', 'TUDS', 'IFHY', 'STFR', 'IMSC', 'ELKT', 'TSSM', 'STPN', 'FRSG', 'BNGL', 'CKWR', 'DVDS', 'INFR', 'LBLD', 'LNTT'])) {
                $user_fkey = $this->Session->read('emp_fkey');
                $user_group = $this->Session->read('user_group');
                $feature_fkey = $this->Session->read('current_feature_id');
                if (!$feature_fkey)
                    $feature_fkey = 33;

                if ($user_group == 2) {
                    $access = $this->EmployeeDetails->query("
                SELECT branch_fkey, is_hierarchy
                FROM user_feature_branch_access
                WHERE user_fkey = ? AND feature_fkey = ? AND LCASE(active) = 'y'
            ", array($user_fkey, $feature_fkey));

                    if (!empty($access)) {
                        $is_hierarchy_active = 'N';
                        $allocated_branches = array();
                        foreach ($access as $row) {
                            if (strtoupper($row['user_feature_branch_access']['is_hierarchy']) == 'Y') {
                                $is_hierarchy_active = 'Y';
                            }
                            $allocated_branches[] = $row['user_feature_branch_access']['branch_fkey'];
                        }

                        if ($is_hierarchy_active == 'Y') {
                            // Hierarchy access: show only subordinates (reports to user) or user themselves
                            $allowed_emps = $this->EmployeeDetails->query("
                        SELECT emp_details.emp_pkey
                        FROM emp_details
                        LEFT JOIN emp_proff ON (emp_details.emp_pkey = emp_proff.emp_fkey)
                        WHERE emp_details.status = '1'
                          AND (emp_proff.attr1 = '$user_fkey' OR emp_proff.emp_fkey = '$user_fkey')
                    ");
                        } else {
                            // Branch access only
                            $b_str = implode("','", $allocated_branches);
                            $allowed_emps = $this->EmployeeDetails->query("
                        SELECT emp_details.emp_pkey
                        FROM emp_details
                        LEFT JOIN emp_proff ON (emp_details.emp_pkey = emp_proff.emp_fkey)
                        WHERE emp_details.status = '1'
                          AND emp_proff.emp_branch IN ('$b_str')
                    ");
                        }

                        $allowed_pkeys = array_column(
                            array_map(function ($r) {
                                return $r['emp_details'];
                            }, $allowed_emps),
                            'emp_pkey'
                        );

                        if (!empty($allowed_pkeys)) {
                            $conditions["AttendanceRegister.emp_fkey"] = $allowed_pkeys;
                        } else {
                            // No employees accessible — return nothing
                            $conditions["AttendanceRegister.emp_fkey"] = array(0);
                        }
                    } else {
                        // No access rows: restrict to self only
                        $conditions["AttendanceRegister.emp_fkey"] = array($user_fkey);
                    }
                }
                // For user_group != 2 (admin/super-admin), no restriction — all employees shown
            }
        }

        if (!empty($branch)) {
            $conditions["AttendanceRegister.branch_code"] = $branch;
        }


        // edited by bindu 17-02-2026 end


        // ---- Fetch attendance & employee info ----
        $attendances = $this->AttendanceRegister->find('all', [
            'fields' => [
                'AttendanceRegister.*',
                'termination.last_approved_working_date',
                'emp.joining_date',
                'emp.emp_type',
                'empdetails.emp_pkey',
                'empdetails.first_name',
                'empdetails.middile_name',
                'empdetails.last_name',
                'emp.emp_branch',
                'emp.emp_company_id',
                'wd.minuts_calc_perday'
            ],
            'joins' => [
                [
                    'table' => 'emp_details',
                    'alias' => 'empdetails',
                    'type' => 'LEFT',
                    'conditions' => ['empdetails.emp_pkey = AttendanceRegister.emp_fkey']
                ],
                [
                    'table' => 'termination',
                    'alias' => 'termination',
                    'type' => 'LEFT',
                    'conditions' => [
                        'termination.emp_fkey = AttendanceRegister.emp_fkey',
                        'termination.status = 1'
                    ]
                ],
                [
                    'table' => 'emp_proff',
                    'alias' => 'emp',
                    'type' => 'LEFT',
                    'conditions' => ['emp.emp_fkey = empdetails.emp_pkey']
                ],
                [
                    'table' => 'working_day_time_procedures',
                    'alias' => 'wd',
                    'type' => 'LEFT',
                    'conditions' => ['wd.day_time_seq = emp.day_time_seq']
                ],
            ],
            'conditions' => $conditions,
            'order' => ['empdetails.first_name' => 'ASC']
        ]);







        // ✅ Merge daily data with main attendance
        $employee_attendance = [];



        foreach ($attendances as $val) {
            $emp = $val['AttendanceRegister'];
            $emppk = isset($val['empdetails']['emp_pkey']) ? $val['empdetails']['emp_pkey'] : 0;
            $full_name = trim($val['empdetails']['first_name'] . ' ' . $val['empdetails']['middile_name'] . ' ' . $val['empdetails']['last_name']);
            $joiningDate = !empty($val['emp']['joining_date'])
                ? $val['emp']['joining_date']
                : null;

            $emp_type = !empty($val['emp']['emp_type'])
                ? $val['emp']['emp_type']
                : null;

            $lastWorkingDate = !empty($val['termination']['last_approved_working_date'])
                ? $val['termination']['last_approved_working_date']
                : null;

            $emp_company_id = !empty($val['emp']['emp_company_id'])
                ? $val['emp']['emp_company_id']
                : null;


            $emp_details = $this->EmployeeDetails->query("SELECT *  FROM emp_details WHERE emp_pkey='$emppk'");
            $emp_status = $emp_details[0]['emp_details']['status'];


            // edited by bindu 17-02-2026


            // if ($lastWorkingDate && $lastWorkingDate < $att_startdate2 || $emp_status == 2) {
            //     continue;

            // }


            // edited by bindu 17-02-2026 end

            $dailyAll = $this->AttendanceRegister->query("
        SELECT att_date, isdelete
        FROM emp_ot_timeattandance
        WHERE emp_pkey = $emppk
          AND yearmonth = '$yearmonth'
    ");

            $dailyIsDelete = [];
            foreach ($dailyAll as $row) {
                $day = (int) date('j', strtotime($row['emp_ot_timeattandance']['att_date'])); // 1-31
                $dailyIsDelete[$day] = $row['emp_ot_timeattandance']['isdelete'];
            }

            // edited by athira on 16-02-2026
            $attendanceDetailsArr = $this->AttendanceRegister->query("
        SELECT att_in_time, att_out_time, duration, att_date
        FROM emp_detail_timeattandance
        WHERE emp_pkey = $emppk
          AND att_date BETWEEN '$att_startdate2' AND '$att_enddate2'
    ");


            $attendanceDetails = [];

            foreach ($attendanceDetailsArr as $row) {
                $day = (int) date('j', strtotime($row['emp_detail_timeattandance']['att_date'])); // 1-31

                $attendanceDetails[$day] = [
                    'att_in' => isset($row['emp_detail_timeattandance']['att_in_time']) ? $row['emp_detail_timeattandance']['att_in_time'] : '',
                    'att_out' => isset($row['emp_detail_timeattandance']['att_out_time']) ? $row['emp_detail_timeattandance']['att_out_time'] : '',
                    'duration' => isset($row['emp_detail_timeattandance']['duration']) ? $row['emp_detail_timeattandance']['duration'] : ''
                ];
            }

            // $emp_data = $this->AttendanceRegister->query("
            //     SELECT 
            //         ep.structure_id,
            //         ep.day_time_seq,

            //         wd.Sunday,
            //         wd.Monday,
            //         wd.Tuesday,
            //         wd.Wednesday,
            //         wd.Thursday,
            //         wd.Friday,
            //         wd.Saturday,

            //         ss.prorate_code
            //     FROM emp_proff ep
            //     LEFT JOIN working_day_time_procedures wd 
            //         ON wd.day_time_seq = ep.day_time_seq
            //     LEFT JOIN salary_structure ss
            //         ON ss.structure_id = ep.structure_id
            //     WHERE ep.emp_fkey = '$emppk'

            // ");


            $emp_data = $this->AttendanceRegister->query("
    SELECT 
        ep.structure_id,
        ss.prorate_code
    FROM emp_proff ep
    LEFT JOIN salary_structure ss
        ON ss.structure_id = ep.structure_id
    WHERE ep.emp_fkey = '$emppk'
    
");



            if ($emp_type == 'DAILY WAGES') {
                $prorate_code = 2;
            } else {
                $prorate_code = isset($emp_data[0]['ss']['prorate_code']) ? $emp_data[0]['ss']['prorate_code'] : 0;
            }



            // $weekdays = [
            //     'Sunday' => $emp_data[0]['wd']['Sunday'],
            //     'Monday' => $emp_data[0]['wd']['Monday'],
            //     'Tuesday' => $emp_data[0]['wd']['Tuesday'],
            //     'Wednesday' => $emp_data[0]['wd']['Wednesday'],
            //     'Thursday' => $emp_data[0]['wd']['Thursday'],
            //     'Friday' => $emp_data[0]['wd']['Friday'],
            //     'Saturday' => $emp_data[0]['wd']['Saturday'],
            // ];

            // // ---------------------------
            // // COUNT WEEK OFF DAYS INSIDE CYCLE
            // // ---------------------------
            // $weekOffCountInCycle = 0;

            // if ($prorate_code == 2) {

            //     $cycleStart = new DateTime($att_startdate2);
            //     $cycleEnd   = new DateTime($att_enddate2);

            //     while ($cycleStart <= $cycleEnd) {

            //         $dayName = $cycleStart->format('l'); // Sunday, Monday...

            //         if (isset($weekdays[$dayName]) && $weekdays[$dayName] === "N") {
            //             $weekOffCountInCycle++;
            //         }

            //         $cycleStart->modify('+1 day');
            //     }
            // }


            // if ($prorate_code == 2) {
            //     $this->AttendanceRegister->updateAll(
            //         ['weekoff_total' => $weekOffCountInCycle],
            //         [
            //             'emp_fkey' => $emppk,
            //             'month_year' => $month
            //         ]
            //     );
            // }






            $record = [
                'emp_detail_timeattandance' => [
                    'emp_pkey' => $emppk,
                    'emp_name' => $full_name,
                    'branch_code' => $emp['branch_code'],
                    'yearmonth' => $emp['month_year'],
                    'prorate_code' => $prorate_code,
                    'emp_type' => $emp_type,
                    'emp_company_id' => $emp_company_id
                    // 'daily_data' => isset($dailyGrouped[$emppk]) ? $dailyGrouped[$emppk] : []
                ]
            ];

            $na_ho_count = 0;
            $na_wo_count = 0;
            $present_count = 0;
            $leave_count = 0;
            $lop_count = 0;
            $weekoff_count = 0;
            $holiday_count = 0;
            $na_count = 0;

            $halfStatuses = [
                'HO/WO',
                'WO/HO',
                'NA/HO',
                'HO/NA',
                'NA/WO',
                'WO/NA'
            ];


            $dates = [];
            $start = new DateTime($att_startdate2);
            $end = new DateTime($att_enddate2);
            while ($start <= $end) {
                $dates[] = $start->format('Y-m-d');
                $start->modify('+1 day');
            }
            // Add field values
            for ($i = 1; $i <= count($dates); $i++) {
                $field = 'FIELD' . $i;
                $record['emp_detail_timeattandance'][$field] = $emp[$field];
                $status = isset($emp[$field]) ? strtoupper(trim($emp[$field])) : '';

                if ($status != '') {
                    // Split if combination
                    $parts = explode('/', $status);
                    $weight = count($parts) > 1 ? 0.5 : 1;

                    foreach ($parts as $part) {
                        $part = strtoupper(trim($part));
                        if ($part == '')
                            continue;

                        if (in_array($part, ['P', 'P/A', 'A/P', 'P/P'])) {
                            $present_count += $weight;
                        } elseif (in_array($part, ['WO', '/WO', 'W/O'])) {
                            $weekoff_count += $weight;
                        } elseif ($part == 'HO') {
                            $holiday_count += $weight;
                        } elseif ($part == 'NA') {
                            $na_count += $weight;
                        } elseif (strpos($part, 'LOP') !== false) {
                            $lop_count += $weight;
                        } elseif ($part != '' && $part != 'A') {
                            $leave_count += $weight;
                        }
                    }

                    if (isset($dates[$i - 1])) {
                        $day = (int) date('j', strtotime($dates[$i - 1])); // actual day number
                        $attDate = $dates[$i - 1]; // Y-m-d
                        $isNAPeriod = false;

                        // NA period detection
                        if ($joiningDate && $attDate < $joiningDate) {
                            $isNAPeriod = true;
                        }
                        if ($lastWorkingDate && $attDate > $lastWorkingDate) {
                            $isNAPeriod = true;
                        }

                        // COUNT NA PERIOD HO / WO (for LOP calc in non-2 structures)
                        // if ($isNAPeriod) {
                        //     if ($status === 'HO' || $status === 'HO/HO') {
                        //         $na_ho_count += 1;
                        //     } elseif ($status === 'WO' || $status === 'WO/WO') {
                        //         $na_wo_count += 1;
                        //     } elseif (in_array($status, ['NA/HO', 'HO/NA'])) {
                        //         $na_ho_count += 0.5;
                        //     } elseif (in_array($status, ['NA/WO', 'WO/NA'])) {
                        //         $na_wo_count += 0.5;
                        //     }
                        // }

                        // edited by athira on 22-05-2025
                        if ($isNAPeriod) {
                            if ($status === 'HO' || $status === 'HO/HO') {
                                $na_ho_count += 1;
                            } elseif ($status === 'WO' || $status === 'WO/WO') {
                                $na_wo_count += 1;
                            } elseif (in_array($status, ['NA/HO', 'HO/NA'])) {
                                $na_ho_count += 0.5;
                            } elseif (in_array($status, ['NA/WO', 'WO/NA'])) {
                                $na_wo_count += 0.5;
                            } elseif (in_array($status, ['HO/WO', 'WO/HO'])) {
                                $na_ho_count += 0.5;
                                $na_wo_count += 0.5;
                            }
                        }
                        // ended by athira on 22-05-2026

                        $hideArrow = ($isNAPeriod == true);
                        if (isset($dailyIsDelete[$day]) && $dailyIsDelete[$day] === 'N') {
                            $hideArrow = true;
                        }
                        $record['emp_detail_timeattandance']['hide_arrow'][$i] = $hideArrow;

                        // Time In/Out Logic
                        if (isset($attendanceDetails[$day])) {
                            if (!empty($attendanceDetails[$day]['att_in'])) {
                                $timeIn = $attendanceDetails[$day]['att_in'];
                                $record['emp_detail_timeattandance']['att_in'][$i] = (preg_match('/^\d{2}:\d{2}:\d{2}$/', $timeIn)) ? $timeIn : date("H:i:s", strtotime($timeIn));
                            } else {
                                $record['emp_detail_timeattandance']['att_in'][$i] = '';
                            }
                            if (!empty($attendanceDetails[$day]['att_out'])) {
                                $timeOut = $attendanceDetails[$day]['att_out'];
                                $record['emp_detail_timeattandance']['att_out'][$i] = (preg_match('/^\d{2}:\d{2}:\d{2}$/', $timeOut)) ? $timeOut : date("H:i:s", strtotime($timeOut));
                            } else {
                                $record['emp_detail_timeattandance']['att_out'][$i] = '';
                            }
                            $record['emp_detail_timeattandance']['duration'][$i] = isset($attendanceDetails[$day]['duration']) ? $attendanceDetails[$day]['duration'] : '';
                        } else {
                            $record['emp_detail_timeattandance']['att_in'][$i] = '';
                            $record['emp_detail_timeattandance']['att_out'][$i] = '';
                            $record['emp_detail_timeattandance']['duration'][$i] = '';
                        }
                    } else {
                        $record['emp_detail_timeattandance']['hide_arrow'][$i] = true;
                    }
                } else {
                    $record['emp_detail_timeattandance'][$field] = '';
                    $record['emp_detail_timeattandance']['hide_arrow'][$i] = true;
                }
            }

            // ✅ Final Totals Calculation Logic
            $loponly = $lop_count;
            // always calculate both totals for persistent data stability
            $lop_total_val = $loponly + $na_count + $na_ho_count + $na_wo_count;
            $wd_lop_total_val = $loponly + $na_count;

            $cal_days = (isset($emp['calander_days']) && $emp['calander_days'] > 0) ? $emp['calander_days'] : count($dates);
            $working_days_val = $cal_days - ($weekoff_count + $holiday_count);

            $record['emp_detail_timeattandance']['na_ho_count'] = $na_ho_count;
            $record['emp_detail_timeattandance']['na_wo_count'] = $na_wo_count;
            $record['emp_detail_timeattandance']['presant_total'] = $present_count;
            $record['emp_detail_timeattandance']['leave_total'] = $leave_count;
            $record['emp_detail_timeattandance']['lop_total'] = $lop_total_val;
            $record['emp_detail_timeattandance']['wd_lop_total'] = $wd_lop_total_val;
            $record['emp_detail_timeattandance']['weekoff_total'] = $weekoff_count;
            $record['emp_detail_timeattandance']['holiday_total'] = $holiday_count;
            $record['emp_detail_timeattandance']['working_days'] = $working_days_val;
            $record['emp_detail_timeattandance']['calander_days'] = $cal_days;

            // ✅ Sync with Database to ensure accuracy for other modules
            $this->AttendanceRegister->query("
                UPDATE attendance_register
                SET
                    na_ho_count = " . (float) $na_ho_count . ",
                    na_wo_count = " . (float) $na_wo_count . ",
                    presant_total = " . (float) $present_count . ",
                    leave_total = " . (float) $leave_count . ",
                    lop_total = " . (float) $lop_total_val . ",
                    wd_lop_total = " . (float) $wd_lop_total_val . ",
                    lop_only = " . (float) $loponly . ",
                    weekoff_total = " . (float) $weekoff_count . ",
                    holiday_total = " . (float) $holiday_count . ",
                    working_days = " . (float) $working_days_val . ",
                    calander_days = " . (float) $cal_days . "
                WHERE registerid = " . (int) $emp['registerid'] . "
            ");

            $employee_attendance[$emppk][] = $record;
        }


        $employee_list = $this->AttendanceRegister->query("SELECT EmpName,emp_pkey FROM employee_info WHERE branch_code ='$branch'");
        $this->set('employee_list', $employee_list);
        $this->set(compact('employee_attendance', 'month'));
        $this->render($isdelete == 'Y' ? 'registerbook' : 'verifiedregisterbook');
    }




    public function updateStatus($newStatus, $data)
    {

        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');

        $get_emp = $this->EditPunches->query("INSERT INTO emp_detail_status_update (emp_fkey, att_date, yearmonth, main_status, aditional_status, creation_date, created_by) " . "VALUES ('" . $data['emp_pkey'] . "', '" . $data['att_date'] . "', '" . $data['yearmonth'] . "', '" . trim($newStatus) . "', '" . $data['ad_present'] . "', '" . date('Y-m-d H:i:s') . "', '" . $this->Session->read('login_user_id') . "') ");
        return $get_emp;
    }



    // public function chnagestatus()
    // {
    //     $this->autoRender = FALSE;
    //     $this->EditPunches->useDbConfig = $this->Session->read('ds');

    //     // $newStatus   = $_POST["status"];
    //     // $statusType  = $_POST["statusType"];
    //     // $newstatuses = $_POST["newstatuses"];
    //     // $att_date    = $_POST["att_date"];

    //     $newStatus   = $_POST["status"];
    //     $statusType  = $_POST["statusType"];
    //     $newstatuses = $_POST["newstatuses"];
    //     $month       = $_POST["month"];
    //     $emp_pkey    = $_POST["emp_pkey"];
    //     $dayIndex    = (int)$_POST["dayIndex"];
    //     $att_date    = $_POST["att_date"];

    //     $isPolicyLeave = isset($_POST["isPolicyLeave"]) ? ($_POST["isPolicyLeave"] === 'true' || $_POST["isPolicyLeave"] === true) : false;


    //     $emp_data = $this->EditPunches->query("
    //     SELECT 
    //         emp_proff.joining_date,
    //         emp_proff.emp_type,
    //         termination.last_approved_working_date
    //     FROM emp_proff
    //     LEFT JOIN termination 
    //         ON termination.emp_fkey = emp_proff.emp_fkey AND termination.status = 1
    //     WHERE  emp_proff.emp_fkey = {$emp_pkey}
    // ");


    // $joiningDate=$emp_data[0]['emp_proff']['joining_date'];
    // $emp_type=$emp_data[0]['emp_proff']['emp_type'];
    // $terminationDate=$emp_data[0]['termination']['last_approved_working_date'];


    //     // ✅ Full day / half day final status logic
    //     if ($statusType == 'full') {
    //         $newStatus = $newstatuses;
    //     }

    //     // edited by athira on 26-02-2026
    //     $nonLeave = ['P', 'A', 'NA', 'WO', 'HO', 'LOP'];
    //     $isLeave = false;
    //     if ($statusType == 'full') {
    //         $codes = explode('/', $newStatus);
    //         foreach ($codes as $c) {
    //             $c_trimmed = strtoupper(trim($c));
    //             if (!in_array($c_trimmed, $nonLeave) || ($isPolicyLeave && $c_trimmed === 'LOP')) {
    //                 $isLeave = true; break;
    //             }
    //         }
    //     } else {
    //         $c_trimmed = strtoupper(trim($newstatuses));
    //         if (!in_array($c_trimmed, $nonLeave) || ($isPolicyLeave && $c_trimmed === 'LOP')) {
    //             $isLeave = true;
    //         }
    //     }

    //     if ($isLeave) {
    //         if ($this->isLeaveAlreadyApplied($emp_pkey, $att_date, $statusType)) {
    //             echo json_encode(['success' => false, 'message' => 'A leave is already applied for this date.']);
    //             return;
    //         }
    //     }
    //     // ended by athira on 26-02-2026

    //     // ✅ Prepare dynamic field name
    //     $fieldName = "FIELD".$dayIndex;

    //     // ✅ 1. Update selected day's status
    //     $updateQuery = "
    //         UPDATE attendance_register
    //         SET `$fieldName` = '".$newStatus."'
    //         WHERE emp_fkey = '".$emp_pkey."'
    //           AND month_year = '".$month."'
    //     ";
    //     $this->EditPunches->query($updateQuery);

    //     // ✅ 2. Fetch updated row
    //     $row = $this->EditPunches->query("
    //         SELECT *
    //         FROM attendance_register
    //         WHERE emp_fkey = '".$emp_pkey."'
    //           AND month_year = '".$month."'
    //         LIMIT 1
    //     ");

    //     if (!empty($row)) {
    //         $row = $row[0]['attendance_register'];

    //        $presentCount = 0;
    // $leaveCount   = 0;
    // $lopCount     = 0;
    // $weekoffCount = 0;
    // $holidayCount = 0;
    // $naCount=0;
    // $na_ho_count = 0;
    // $na_wo_count = 0;

    // $halfStatuses = [
    //     'NA/HO','HO/NA',
    //     'NA/WO','WO/NA',
    //     'HO/WO','WO/HO'
    // ];

    // // ✅ Loop through FIELD1–FIELD31
    // for ($i = 1; $i <= 31; $i++) {
    //     $fieldVal = isset($row["FIELD$i"]) ? strtoupper(trim($row["FIELD$i"])) : '';
    //     if ($fieldVal == '') continue;

    //     //  $attDate = date('Y-m-d', strtotime($month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT)));

    //     // // 🔹 NA period check
    //     // $isNAperiod = false;
    //     // if ($joiningDate && $attDate < $joiningDate) $isNAperiod = true;
    //     // if ($lastWorkingDate && $attDate > $lastWorkingDate) $isNAperiod = true;

    //     // Split if combination like P/LOP or LOP/P etc.
    //     $parts = explode('/', $fieldVal);

    //     // each part = half-day weight
    //     $weight = count($parts) > 1 ? 0.5 : 1;

    //     //   if ($isNAperiod) {

    //     //     // FULL DAY
    //     //     if ($weight === 1) {
    //     //         if ($fieldVal === 'HO') $na_ho_count += 1;
    //     //         elseif ($fieldVal === 'WO') $na_wo_count += 1;
    //     //     }

    //     //     // HALF DAY
    //     //     else {
    //     //         if (strpos($fieldVal, 'HO') !== false) $na_ho_count += 0.5;
    //     //         if (strpos($fieldVal, 'WO') !== false) $na_wo_count += 0.5;
    //     //     }
    //     // }

    //     foreach ($parts as $part) {
    //         $part = strtoupper(trim($part));

    //         // ✅ Present combinations
    //         if (in_array($part, ['P', 'P/A', 'A/P', 'P/P'])) {
    //             $presentCount += $weight;
    //         }

    //         // ✅ Week Off combinations
    //         elseif (in_array($part, ['WO', '/WO', 'W/O'])) {
    //             $weekoffCount += $weight;
    //         }

    //         // ✅ Holiday combinations
    //         elseif ($part == 'HO') {
    //             $holidayCount += $weight;
    //         }
    //         elseif ($part == 'NA') {
    //             $naCount += $weight;
    //         }

    //         // ✅ LOP combinations (include all types: manual and policy)
    //         elseif ($part === 'LOP') {
    //             $lopCount += $weight;
    //         }

    //         // ✅ Anything else (leave codes like CL, SL, EL, ML etc. excluding LOP)
    //         elseif ($part != '' && $part != 'A') {
    //             $leaveCount += $weight;
    //         }
    //     }
    // }

    // // $workingDays = $presentCount + $leaveCount + $lopCount;
    // $emp_salary_structure = $this->EditPunches->query("
    //     SELECT e.emp_structure_id, s.prorate_code
    //     FROM emp_salary_structure e
    //     INNER JOIN salary_structure s 
    //         ON e.emp_structure_id = s.structure_id
    //     WHERE e.emp_fkey = '$emp_pkey'
    // ");

    // $attendance_data=$this->EditPunches->query("SELECT na_ho_count,na_wo_count FROM attendance_register WHERE emp_fkey='$emp_pkey'");
    // $na_ho_count=$attendance_data[0]['attendance_register']['na_ho_count'];
    // $na_wo_count=$attendance_data[0]['attendance_register']['na_wo_count'];

    // if($emp_type =='DAILY WAGES'){
    //     $salary_structure = 2;
    // }
    // else{
    // $salary_structure = isset($emp_salary_structure[0]['s']['prorate_code']) ? $emp_salary_structure[0]['s']['prorate_code'] : 0;
    // }


    // if($salary_structure !==2){
    //     $lopCount = $lopCount + $na_ho_count + $na_wo_count;
    // }

    // $calander_days=$row['calander_days'];

    // // $workingDays = $presentCount + $leaveCount + $lopCount+$naCount;
    // $workingDays = $calander_days -($weekoffCount + $holidayCount);
    // $loponly=$lopCount;
    // $lopCount=$lopCount+$naCount;

    // // $emp_salary_structure = $this->EditPunches->query("
    // //     SELECT e.emp_structure_id, s.prorate_code
    // //     FROM emp_salary_structure e
    // //     INNER JOIN salary_structure s 
    // //         ON e.emp_structure_id = s.structure_id
    // //     WHERE e.emp_fkey = '$emp_pkey'
    // // ");

    // // $salary_structure = $emp_salary_structure[0]['s']['prorate_code'];

    // // if ($salary_structure == 2) {
    // //     debug('hi');
    // // }



    //         // ✅ 3. Update totals in same row
    //         $updateTotals = "
    //             UPDATE attendance_register
    //             SET 
    //                 presant_total = '".$presentCount."',
    //                 lop_total = '".$lopCount."',
    //                 lop_only ='".$loponly."',
    //                 leave_total = '".$leaveCount."',
    //                 weekoff_total = '".$weekoffCount."',
    //                 holiday_total = '".$holidayCount."',
    //                 working_days = '".$workingDays."'
    //             WHERE emp_fkey = '".$emp_pkey."'
    //               AND month_year = '".$month."'
    //         ";
    //         $this->EditPunches->query($updateTotals);
    //     }

    //     // ✅ 4. Return response
    //     $resp = array(
    //         'success' => true,
    //         'status' => $newStatus,
    //         'isPolicyLeave' => $isPolicyLeave,
    //         'counts' => array(
    //             'lop' => $lopCount,
    //             'present' => $presentCount,
    //             'leave' => $leaveCount,
    //             'weekoff' => $weekoffCount,
    //             'holiday' => $holidayCount,
    //             'working' => $workingDays
    //         )
    //     );

    //     echo json_encode($resp);
    // }

    public function chnagestatus()
    {
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');

        $newStatus = $_POST["status"];
        $statusType = $_POST["statusType"];
        $newstatuses = $_POST["newstatuses"];
        $att_date = $_POST["att_date"];
        $isPolicyLeave = isset($_POST["isPolicyLeave"]) ? ($_POST["isPolicyLeave"] === 'true' || $_POST["isPolicyLeave"] === true) : false;
        $emp_pkey = $_POST["emp_pkey"];
        $dayIndex = $_POST["dayIndex"];
        $month = $_POST["month"];


        $emp_data = $this->EditPunches->query("
    SELECT 
        emp_proff.joining_date,
        emp_proff.emp_type,
        termination.last_approved_working_date
    FROM emp_proff
    LEFT JOIN termination 
        ON termination.emp_fkey = emp_proff.emp_fkey AND termination.status = 1
    WHERE  emp_proff.emp_fkey = {$emp_pkey}
");


        $joiningDate = $emp_data[0]['emp_proff']['joining_date'];
        $emp_type = $emp_data[0]['emp_proff']['emp_type'];
        $terminationDate = $emp_data[0]['termination']['last_approved_working_date'];


        // ✅ Handle full-day status formatting
        if ($statusType == 'full') {
            if (strpos($newStatus, '/') === false) {
                $newStatus = $newstatuses . '/' . $newstatuses;
            }
        }

        // edited by athira on 26-02-2026
        $nonLeave = ['P', 'A', 'NA', 'WO', 'HO', 'LOP'];
        $isLeave = false;
        if ($statusType == 'full') {
            $codes = explode('/', $newStatus);
            foreach ($codes as $c) {
                $c_trimmed = strtoupper(trim($c));
                if (!in_array($c_trimmed, $nonLeave) || ($isPolicyLeave && $c_trimmed === 'LOP')) {
                    $isLeave = true;
                    break;
                }
            }
        } else {
            $c_trimmed = strtoupper(trim($newstatuses));
            if (!in_array($c_trimmed, $nonLeave) || ($isPolicyLeave && $c_trimmed === 'LOP')) {
                $isLeave = true;
            }
        }

        // Hard Block: If any leave (Applied/Approved/Authorized) exists for this date/session, block ALL updates (Leave, P, WO, etc.)
        if ($this->isLeaveAlreadyApplied($emp_pkey, $att_date, $statusType)) {
            echo json_encode(['success' => false, 'message' => 'An active leave (Applied/Approved/Authorized) already exists for this date. Please cancel it in the Leave module first.']);
            return;
        }

        if ($isPolicyLeave) {
            $policyQuery = $this->EditPunches->query("
                SELECT lp.minimum_service, lp.exceptions, lp.maximum_leave, lp.minimum_leave, lp.min_day_before_apply
                FROM salary_head_items shi
                JOIN leavepolicy lp ON lp.salary_head_item_fkey = shi.salary_head_item_pkey
                JOIN emp_proff ep ON ep.LEAVEPOLICY_GROUP_ID = lp.LEAVEPOLICY_GROUP_ID
                WHERE shi.occurance = '{$newstatuses}' AND ep.emp_fkey = {$emp_pkey}
                LIMIT 1
            ");

            if (!empty($policyQuery)) {
                $lp = $policyQuery[0]['lp'];
                $exceptions = isset($lp['exceptions']) ? strtoupper(trim($lp['exceptions'])) : 'N';
                $minService = isset($lp['minimum_service']) ? (int) $lp['minimum_service'] : 0;
                $maxL = isset($lp['maximum_leave']) ? (int) $lp['maximum_leave'] : 0;
                $minL = isset($lp['minimum_leave']) ? (int) $lp['minimum_leave'] : 0;
                $advN = isset($lp['min_day_before_apply']) ? (int) $lp['min_day_before_apply'] : 0;

                // if ($exceptions === 'Y') {
                //     // Check generic exceptions first
                //     if ($maxL > 0 || $minL > 0 || $advN > 0) {
                //         echo json_encode(['success' => false, 'message' => 'Exceptions like max leave min leave and advance notice days cannot be added.']);
                //         return;
                //     }

                //     // Check minimum service if defined
                //     if ($joiningDate && $minService > 0) {
                //         $joining = new DateTime($joiningDate);
                //         $today = new DateTime($att_date);

                //         // Calculate total months worked
                //         $diff = $joining->diff($today);
                //         $months_worked = ($diff->y * 12) + $diff->m;

                //         // Adjusted for partial month if needed (matching user logic from commented code)
                //         if ((int) $today->format('d') < (int) $joining->format('d')) {
                //             // $months_worked--; // Logic from commented code
                //         }

                //         if ($months_worked < $minService) {
                //             echo json_encode(['success' => false, 'message' => 'Employee is in service period']);
                //             return;
                //         }
                //     }
                // }
            }
        }
        // ended by athira on 26-02-2026

        // ✅ Prepare dynamic field name
        $fieldName = "FIELD" . $dayIndex;

        // ✅ 1. Update selected day's status
        $updateQuery = "
        UPDATE attendance_register
        SET `$fieldName` = '" . $newStatus . "'
        WHERE emp_fkey = '" . $emp_pkey . "'
          AND month_year = '" . $month . "'
    ";
        $this->EditPunches->query($updateQuery);

        // ✅ 2. Fetch updated row
        $row = $this->EditPunches->query("
        SELECT *
        FROM attendance_register
        WHERE emp_fkey = '" . $emp_pkey . "'
          AND month_year = '" . $month . "'
        LIMIT 1
    ");

        if (!empty($row)) {
            $row = $row[0]['attendance_register'];

            $presentCount = 0;
            $leaveCount = 0;
            $lopCount = 0;
            $weekoffCount = 0;
            $holidayCount = 0;
            $naCount = 0;
            $na_ho_count = 0;
            $na_wo_count = 0;

            // Fetch cycle start/end for NA period check
            $cycle = $this->EditPunches->query("
                SELECT att_start_end_fn('{$month}-01',1) AS start_date,
                       att_start_end_fn('{$month}-01',2) AS end_date
            ");
            $att_startdate = isset($cycle[0][0]['start_date']) ? $cycle[0][0]['start_date'] : ($month . '-01');
            $att_enddate = isset($cycle[0][0]['end_date']) ? $cycle[0][0]['end_date'] : date('Y-m-t', strtotime($month . '-01'));

            $dates = [];
            $start = new DateTime($att_startdate);
            $end = new DateTime($att_enddate);
            while ($start <= $end) {
                $dates[] = $start->format('Y-m-d');
                $start->modify('+1 day');
            }

            $halfStatuses = [
                'NA/HO',
                'HO/NA',
                'NA/WO',
                'WO/NA',
                'HO/WO',
                'WO/HO'
            ];

            // ✅ Loop through FIELD1–FIELD31
            for ($i = 1; $i <= count($dates); $i++) {
                $fieldVal = isset($row["FIELD$i"]) ? strtoupper(trim($row["FIELD$i"])) : '';
                if ($fieldVal == '')
                    continue;

                $parts = explode('/', $fieldVal);
                $weight = count($parts) > 1 ? 0.5 : 1;

                foreach ($parts as $part) {
                    $part = strtoupper(trim($part));
                    if ($part == '')
                        continue;

                    if (in_array($part, ['P', 'P/A', 'A/P', 'P/P'])) {
                        $presentCount += $weight;
                    } elseif (in_array($part, ['WO', '/WO', 'W/O'])) {
                        $weekoffCount += $weight;
                    } elseif ($part == 'HO') {
                        $holidayCount += $weight;
                    } elseif ($part == 'NA') {
                        $naCount += $weight;
                    } elseif (strpos($part, 'LOP') !== false) {
                        $lopCount += $weight;
                    } elseif ($part != '' && $part != 'A') {
                        $leaveCount += $weight;
                    }
                }

                // Recalculate NA period HO/WO for this row
                if (isset($dates[$i - 1])) {
                    $attDate = $dates[$i - 1];
                    $isNAPeriod = false;
                    if ($joiningDate && $attDate < $joiningDate)
                        $isNAPeriod = true;
                    if ($terminationDate && $attDate > $terminationDate)
                        $isNAPeriod = true;

                    // if ($isNAPeriod) {
                    //     if ($fieldVal === 'HO' || $fieldVal === 'HO/HO') {
                    //         $na_ho_count += 1;
                    //     } elseif ($fieldVal === 'WO' || $fieldVal === 'WO/WO') {
                    //         $na_wo_count += 1;
                    //     } elseif (in_array($fieldVal, ['NA/HO', 'HO/NA'])) {
                    //         $na_ho_count += 0.5;
                    //     } elseif (in_array($fieldVal, ['NA/WO', 'WO/NA'])) {
                    //         $na_wo_count += 0.5;
                    //     }
                    // }

                    // edited by athira on 22-05-2025
                    if ($isNAPeriod) {
                        if ($fieldVal === 'HO' || $fieldVal === 'HO/HO') {
                            $na_ho_count += 1;
                        } elseif ($fieldVal === 'WO' || $fieldVal === 'WO/WO') {
                            $na_wo_count += 1;
                        } elseif (in_array($fieldVal, ['NA/HO', 'HO/NA'])) {
                            $na_ho_count += 0.5;
                        } elseif (in_array($fieldVal, ['NA/WO', 'WO/NA'])) {
                            $na_wo_count += 0.5;
                        } elseif (in_array($fieldVal, ['HO/WO', 'WO/HO'])) {
                            $na_ho_count += 0.5;
                            $na_wo_count += 0.5;
                        }
                    }
                    // ended by athira on 22-05-2026
                }
            }

            $emp_salary_structure = $this->EditPunches->query("
                SELECT e.emp_structure_id, s.prorate_code
                FROM emp_salary_structure e
                INNER JOIN salary_structure s ON e.emp_structure_id = s.structure_id
                WHERE e.emp_fkey = '$emp_pkey'
            ");


            if ($emp_type == 'DAILY WAGES') {
                $salary_structure = 2;
            } else {
                $salary_structure = isset($emp_salary_structure[0]['s']['prorate_code']) ? $emp_salary_structure[0]['s']['prorate_code'] : 0;
            }


            // if($salary_structure !==2){
            //     $lopCount = $lopCount + $naCount+ $na_ho_count + $na_wo_count;
            // }

            $loponly = $lopCount;

            // edited by athira on 13-04-2026
            if ($salary_structure == 2) {
                // DAILY WAGES
                $lopCount = $loponly + $naCount;
            } else {
                // OTHER EMPLOYEES
                $lopCount = $loponly + $naCount + $na_ho_count + $na_wo_count;
            }
            // ended by athira on 13-04-2026

            $calander_days = $row['calander_days'];


            // $workingDays = $presentCount + $leaveCount + $lopCount+$naCount;
            $workingDays = $calander_days - ($weekoffCount + $holidayCount);
            // $loponly=$lopCount;
            // $lopCount=$lopCount+$naCount;

            // $emp_salary_structure = $this->EditPunches->query("
            //     SELECT e.emp_structure_id, s.prorate_code
            //     FROM emp_salary_structure e
            //     INNER JOIN salary_structure s 
            //         ON e.emp_structure_id = s.structure_id
            //     WHERE e.emp_fkey = '$emp_pkey'
            // ");

            // $salary_structure = $emp_salary_structure[0]['s']['prorate_code'];

            // if ($salary_structure == 2) {
            //     debug('hi');
            // }


            // edited by athira on 13-04-2026
            // ✅ Always calculate BOTH totals for persistent data stability
            $lop_total_val = $loponly + $naCount + $na_ho_count + $na_wo_count;
            $wd_lop_total_val = $loponly + $naCount;

            // ✅ 3. Update totals in same row
            $updateTotals = "
            UPDATE attendance_register
            SET 
                presant_total = '" . $presentCount . "',
                lop_total = '" . $lop_total_val . "',
                wd_lop_total = '" . $wd_lop_total_val . "',
                lop_only ='" . $loponly . "',
                leave_total = '" . $leaveCount . "',
                weekoff_total = '" . $weekoffCount . "',
                holiday_total = '" . $holidayCount . "',
                working_days = '" . $workingDays . "',
                na_ho_count = '" . $na_ho_count . "',
                na_wo_count = '" . $na_wo_count . "'
            WHERE registerid = '" . $row['registerid'] . "'
        ";
            $this->EditPunches->query($updateTotals);
            // ended by athira on 13-04-2026
        }

        // Use the same splitting logic for the response JSON
        $lop_count_resp = ($salary_structure == 2) ? $wd_lop_total_val : $lop_total_val;

        // ✅ 4. Return response
        $resp = array(
            'success' => true,
            'status' => $newStatus,
            'isPolicyLeave' => $isPolicyLeave,
            'na_ho_count' => $na_ho_count,
            'na_wo_count' => $na_wo_count,
            'counts' => array(
                'lop' => $lop_count_resp,
                'present' => $presentCount,
                'leave' => $leaveCount,
                'weekoff' => $weekoffCount,
                'holiday' => $holidayCount,
                'working' => $workingDays
            )
        );

        echo json_encode($resp);
    }





    //edited by athira on 26-10-2025
    public function removeAttendance()
    {
        $this->autoRender = false;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');

        $empData = $this->request->data['emp_data'];
        $branch = $this->request->data['branch_code'];
        $monthYear = $this->request->data['month_year'];

        if (empty($empData)) {
            echo json_encode([
                'success' => false,
                'msg' => 'No employees selected'
            ]);
            return;
        }

        $removedEmployees = [];
        $skippedEmployees = [];
        $otSkippedEmployees = []; // Edited by Akshay on 27-4-2026

        foreach ($empData as $row) {

            $empPkey = $row['empPkey'];

            // Check payroll status
            $payroll = $this->AttendanceRegister->query("
            SELECT action 
            FROM payroll_master 
            WHERE emp_fkey = '$empPkey'
              AND month_year = '$monthYear'
        ");

            if (!empty($payroll)) {
                $action = strtolower($payroll[0]['payroll_master']['action']);

                if (in_array($action, ['processed', 'approved'])) {
                    $skippedEmployees[] = $empPkey;
                    continue;
                }
            }

            // // Edited by Akshay on 25-04-2026
            $monthYearFormatted = date('Y-m-01', strtotime($monthYear)); // Edited by Akshay on 25-04-2026
            $arr_ot_verified_count = $this->AttendanceRegister->query("SELECT COUNT(*) as count FROM `emp_ot_master` WHERE `emp_fkey` = '$empPkey' AND month = '$monthYearFormatted' AND is_verified = 'Y';");
            $ot_verified_count = isset($arr_ot_verified_count[0][0]['count']) ? $arr_ot_verified_count[0][0]['count'] : 0;
            if ($ot_verified_count > 0) {
                $otSkippedEmployees[] = $empPkey;
                continue;
            }
            // // End

            // Soft delete attendance
            $this->AttendanceRegister->updateAll(
                ['AttendanceRegister.isdelete' => "'Y'"],
                [
                    'AttendanceRegister.emp_fkey' => $empPkey,
                    'AttendanceRegister.branch_code' => $branch,
                    'AttendanceRegister.month_year' => $monthYear
                ]
            );


            $removedEmployees[] = $empPkey;
        }

        $response = ['success' => true];

        if (!empty($removedEmployees)) {
            $response['msg'] = 'Selected employees attendance removed successfully';
            $response['removed_count'] = count($removedEmployees);
        }

        if (!empty($skippedEmployees)) {
            $response['warning'] = 'Cannot remove attendance for some employees. Payroll already processed or approved.';
            $response['skipped_count'] = count($skippedEmployees);
        }

        // Edited by Akshay on 27-4-2026
        if (!empty($otSkippedEmployees)) {
            $response['warning'] = 'Cannot remove attendance for some employees. Monthly overtime already approved.';
            $response['skipped_count'] = count($otSkippedEmployees);
        }
        // End

        echo json_encode($response);
    }


    // public function removeAttendance()
    // {
    //     $this->autoRender = false;
    //     $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');

    //     $empData     = $this->request->data['emp_data'];
    //     $branch      = $this->request->data['branch_code'];
    //     $monthYear   = $this->request->data['month_year']; 

    //     if (empty($empData)) {
    //         echo json_encode(['success' => false, 'msg' => 'No employees selected']);
    //         return;
    //     }



    //     $startDate = $monthYear . "-01";
    //     $endDate   = date("Y-m-t", strtotime($startDate));

    //     foreach ($empData as $row) {

    //         $empPkey = $row['empPkey'];


    //         // Fetch payroll action
    //         $payroll = $this->AttendanceRegister->query("
    //             SELECT action 
    //             FROM payroll_master 
    //             WHERE emp_fkey='$empPkey' 
    //               AND month_year='$monthYear'
    //         ");

    //         // If payroll row exists
    //         if (!empty($payroll)) {
    //             $action = $payroll[0]['payroll_master']['action'];

    //             // If payroll is processed or approved → block deletion
    //             if (in_array(strtolower($action), ['processed', 'approved'])) {
    //                 echo json_encode([
    //                     'success' => false,
    //                     'msg'     => "Cannot remove attendance for employee. Payroll is already"." ". $action
    //                 ]);
    //                 return;
    //             }
    //         }

    //         // Each employee has only 1 attendance row for the month
    //         $this->AttendanceRegister->updateAll(
    //             ['AttendanceRegister.isdelete' => "'Y'"],
    //             [
    //                 'AttendanceRegister.emp_fkey'     => $empPkey,
    //                 'AttendanceRegister.branch_code'  => $branch,
    //                 'AttendanceRegister.month_year'  => $monthYear,
    //             ]
    //         );
    //     }

    //     echo json_encode(['success' => true]);
    // }


    public function verifyAttendance()
    {
        $this->autoRender = false;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');

        if (!$this->request->is('post')) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $data = $this->request->data;

        if (empty($data['emp_data'])) {
            echo json_encode(['success' => false, 'message' => 'No employees selected']);
            return;
        }

        $branch_code = $data['branch_code'];
        $month_year = $data['month_year'];

        // get cycle start
        $cycle = $this->AttendanceRegister->query("
        SELECT att_start_end_fn('{$month_year}-01',1) AS start_date,
               att_start_end_fn('{$month_year}-01',2) AS end_date
        ");
        $cycleStart = isset($cycle[0][0]['start_date']) ? $cycle[0][0]['start_date'] : ($month_year . '-01');

        try {
            $cycleStartDt = new DateTime($cycleStart);
        } catch (Exception $e) {
            $cycleStartDt = new DateTime($month_year . '-01');
        }

        foreach ($data['emp_data'] as $emp) {

            $emp_id = isset($emp['empPkey']) ? $emp['empPkey'] : 0;
            $timesheet = isset($emp['timesheet']) ? $emp['timesheet'] : [];
            $policysheet = isset($emp['policysheet']) ? $emp['policysheet'] : [];

            $this->AttendanceRegister->query("SELECT `ot_duration_register_date`('" . $month_year . '-01' . "', '" . $emp_id . "', '" . $branch_code . "') "); // Edited by Akshay on 6-3-2026

            $emp_salary_structure = $this->AttendanceRegister->query("
        SELECT e.structure_id, s.prorate_code
        FROM emp_proff e
        INNER JOIN salary_structure s 
            ON e.structure_id = s.structure_id
        WHERE e.emp_fkey = '$emp_id' LIMIT 1
        ");

            $emp_data = $this->AttendanceRegister->query("
        SELECT joining_date, emp_type, termination.last_approved_working_date
        FROM emp_proff
        LEFT JOIN termination ON termination.emp_fkey = emp_proff.emp_fkey AND termination.status = 1
        WHERE emp_proff.emp_fkey = '$emp_id' LIMIT 1
        ");

            $emp_type = isset($emp_data[0]['emp_proff']['emp_type']) ? $emp_data[0]['emp_proff']['emp_type'] : null;
            $joiningDate = isset($emp_data[0]['emp_proff']['joining_date']) ? $emp_data[0]['emp_proff']['joining_date'] : null;
            $terminationDate = isset($emp_data[0]['termination']['last_approved_working_date']) ? $emp_data[0]['termination']['last_approved_working_date'] : null;


            if ($emp_type == 'DAILY WAGES') {
                $salary_structure = 2;
            } else {
                $salary_structure = isset($emp_salary_structure[0]['s']['prorate_code']) ? $emp_salary_structure[0]['s']['prorate_code'] : 0;
            }


            // Build fields and sanitize UI strings
            $fields = [];
            // Fetch existing row early to check for leaves
            $attRow = $this->AttendanceRegister->find('first', [
                'conditions' => [
                    'emp_fkey' => $emp_id,
                    'branch_code' => $branch_code,
                    'month_year' => $month_year
                ]
            ]);

            for ($i = 1; $i <= 31; $i++) {
                $raw_ts = isset($timesheet[$i]) && $timesheet[$i] !== '' ? $timesheet[$i] : null;
                if ($raw_ts !== null) {
                    // The UI might send 'PolicyLOP', strip 'Policy' to restore the DB standard 'LOP'
                    $raw_ts = str_replace('Policy', '', $raw_ts);

                    // Block overwriting if an active leave exists for this date
                    $dateDt = clone $cycleStartDt;
                    $dateDt->modify('+' . ($i - 1) . ' days');
                    $applyDate = $dateDt->format('Y-m-d');

                    if ($this->isLeaveAlreadyApplied($emp_id, $applyDate, 'full')) {
                        // Preserve existing value from database if a leave is active
                        if (!empty($attRow) && isset($attRow['AttendanceRegister']["FIELD$i"])) {
                            $raw_ts = $attRow['AttendanceRegister']["FIELD$i"];
                        }
                    }
                }
                $fields["FIELD$i"] = $raw_ts;
            }

            // Source to detect leaves
            $source = !empty($attRow) ? $attRow['AttendanceRegister'] : $fields;

            // ---------- APPLY LEAVES ----------
            for ($i = 1; $i <= 31; $i++) {

                $fieldKey = "FIELD$i";
                $val = isset($source[$fieldKey]) ? trim($source[$fieldKey]) : '';

                if ($val === '')
                    continue;


                $nonLeave = ['P', 'A', 'NA', 'WO', 'HO', 'LOP'];

                $codes = explode('/', $val);
                $codes = array_map('trim', $codes);

                // 1️⃣ Check if full-day leave
                $c = strtoupper($codes[0]);
                $pFlags = isset($policysheet[$i]) ? explode(',', $policysheet[$i]) : ['false', 'false'];
                $isDatePolicy = ($pFlags[0] === 'true' && (isset($pFlags[1]) ? $pFlags[1] : $pFlags[0]) === 'true');


                if (count($codes) === 2 && $codes[0] === $codes[1] && (!in_array($c, $nonLeave) || ($c === 'LOP' && $isDatePolicy))) {
                    $leaveCode = $c;


                    $dateDt = clone $cycleStartDt;
                    $dateDt->modify('+' . ($i - 1) . ' days');
                    $applyDate = $dateDt->format('Y-m-d');

                    // Pass full session if no leave already exists
                    if (!$this->isLeaveAlreadyApplied($emp_id, $applyDate, 'full')) {
                        $this->AddLeave($leaveCode, $applyDate, $emp_id, 'full');
                    }
                    continue;
                }

                // 2️⃣ Handle half-day leaves (mixed day like CL/P or P/CL)
                foreach ($codes as $index => $code) {
                    if (trim($code) === '')
                        continue;
                    $cleanCode = strtoupper(trim($code));

                    $pFlags = isset($policysheet[$i]) ? explode(',', $policysheet[$i]) : ['false', 'false'];
                    $isHalfPolicy = ($index === 0 ? ($pFlags[0] === 'true') : ((isset($pFlags[1]) ? $pFlags[1] : $pFlags[0]) === 'true'));

                    if (in_array($cleanCode, $nonLeave) && !($cleanCode === 'LOP' && $isHalfPolicy))
                        continue;

                    $session = ($index === 0 ? 'first' : 'second');

                    $dateDt = clone $cycleStartDt;
                    $dateDt->modify('+' . ($i - 1) . ' days');
                    $applyDate = $dateDt->format('Y-m-d');

                    if (!$this->isLeaveAlreadyApplied($emp_id, $applyDate, $session)) {
                        $this->AddLeave($cleanCode, $applyDate, $emp_id, $session);
                    }
                }
            }

            // ---------- SAVE UPDATED ATTENDANCE ----------
            $saveData = array_merge($fields, [
                'emp_fkey' => $emp_id,
                'branch_code' => $branch_code,
                'month_year' => $month_year,
                'isdelete' => 'N',
                'record_status' => 1,
                'userid' => $this->Session->read('login_user_id'),
                'created_time' => date('Y-m-d H:i:s')
            ]);

            if ($attRow) {
                $this->AttendanceRegister->id = $attRow['AttendanceRegister']['registerid'];
            }
            // else {
            //     $this->AttendanceRegister->create();
            // }

            $this->AttendanceRegister->save($saveData);



            $saved = $this->AttendanceRegister->find('first', [
                'conditions' => [
                    'emp_fkey' => $emp_id,
                    'branch_code' => $branch_code,
                    'month_year' => $month_year,
                    'isdelete' => 'N'
                ]
            ]);

            if (!empty($saved)) {

                $row = $saved['AttendanceRegister'];

                $present = 0;
                $leave = 0;
                $lop = 0;
                $weekoff = 0;
                $holiday = 0;
                $na = 0;

                $na_ho_count = 0;
                $na_wo_count = 0;

                // Prepare dates for NA check
                $dates = [];
                $start = new DateTime($cycleStart);
                $endCycles = $this->AttendanceRegister->query("SELECT att_start_end_fn('{$month_year}-01',2) AS end_date");
                $att_enddate = isset($endCycles[0][0]['end_date']) ? $endCycles[0][0]['end_date'] : date('Y-m-t', strtotime($month_year . '-01'));
                $endDt = new DateTime($att_enddate);
                while ($start <= $endDt) {
                    $dates[] = $start->format('Y-m-d');
                    $start->modify('+1 day');
                }

                for ($i = 1; $i <= count($dates); $i++) {

                    $val = isset($row["FIELD$i"]) ? strtoupper(trim($row["FIELD$i"])) : '';

                    if ($val == '')
                        continue;

                    $parts = explode('/', $val);
                    $weight = (count($parts) > 1 ? 0.5 : 1);

                    foreach ($parts as $p) {
                        $p = strtoupper(trim($p));
                        if ($p === '')
                            continue;

                        if (in_array($p, ['P', 'P/P', 'A/P', 'P/A'])) {
                            $present += $weight;
                        } elseif (in_array($p, ['WO', 'W/O', '/WO'])) {
                            $weekoff += $weight;
                        } elseif ($p == 'HO') {
                            $holiday += $weight;
                        } elseif ($p == 'NA') {
                            $na += $weight;
                        } elseif ($p === 'LOP') {
                            $lop += $weight;
                        } elseif ($p != 'A') {
                            $leave += $weight;
                        }
                    }

                    // Recalculate NA period HO/WO
                    if (isset($dates[$i - 1])) {
                        $attDate = $dates[$i - 1];
                        $isNAPeriod = false;
                        if ($joiningDate && $attDate < $joiningDate)
                            $isNAPeriod = true;
                        if ($terminationDate && $attDate > $terminationDate)
                            $isNAPeriod = true;

                        // if ($isNAPeriod) {
                        //     if ($val === 'HO' || $val === 'HO/HO') {
                        //         $na_ho_count += 1;
                        //     } elseif ($val === 'WO' || $val === 'WO/WO') {
                        //         $na_wo_count += 1;
                        //     } elseif (in_array($val, ['NA/HO', 'HO/NA'])) {
                        //         $na_ho_count += 0.5;
                        //     } elseif (in_array($val, ['NA/WO', 'WO/NA'])) {
                        //         $na_wo_count += 0.5;
                        //     }
                        // }

                        // edited by athira on 22-05-2025
                        if ($isNAPeriod) {
                            if ($val === 'HO' || $val === 'HO/HO') {
                                $na_ho_count += 1;
                            } elseif ($val === 'WO' || $val === 'WO/WO') {
                                $na_wo_count += 1;
                            } elseif (in_array($val, ['NA/HO', 'HO/NA'])) {
                                $na_ho_count += 0.5;
                            } elseif (in_array($val, ['NA/WO', 'WO/NA'])) {
                                $na_wo_count += 0.5;
                            } elseif (in_array($val, ['HO/WO', 'WO/HO'])) {
                                $na_ho_count += 0.5;
                                $na_wo_count += 0.5;
                            }
                        }
                        // ended by athira on 22-05-2026
                    }
                }

                // $loponly=$lop;

                // $lop=$lop+$na;


                // if($salary_structure !==2){

                // $lop=$loponly+$na+$na_ho_count+$na_wo_count;
                // }

                $loponly = $lop; // original LOP from attendance

                // edited by athira on 13-04-2026
                // ✅ Always calculate BOTH totals for persistent data stability
                $lop_total_val = $loponly + $na + $na_ho_count + $na_wo_count;
                $wd_lop_total_val = $loponly + $na;

                // calendar days
                $cal = $this->AttendanceRegister->query("
                SELECT calander_days FROM attendance_register 
                WHERE registerid = '{$row['registerid']}'
            ");
                $cal_days = isset($cal[0]['attendance_register']['calander_days']) ? $cal[0]['attendance_register']['calander_days'] : 0;
                $workingDays = $cal_days - ($weekoff + $holiday);
                // ended by athira on 13-04-2026
                // update totals
                $this->AttendanceRegister->query("
                UPDATE attendance_register
                SET 
                    presant_total = '{$present}',
                    lop_total = '{$lop_total_val}',
                    wd_lop_total = '{$wd_lop_total_val}',
                    lop_only = '{$loponly}',
                    leave_total = '{$leave}',
                    weekoff_total = '{$weekoff}',
                    holiday_total = '{$holiday}',
                    working_days = '{$workingDays}',
                    na_ho_count = '{$na_ho_count}',
                    na_wo_count = '{$na_wo_count}'
                WHERE registerid = '{$row['registerid']}'
            ");
                // ended by athira on 13-04-2026

            } // end totals block

        } // end foreach employee

        echo json_encode(['success' => true, 'message' => 'Attendance verified and leaves applied + totals updated']);
        return;
    }



    // public function verifyAttendance() {
    //     $this->autoRender = false;
    //     $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');

    //     if (! $this->request->is('post')) {
    //         echo json_encode(['success' => false, 'message' => 'Invalid request']);
    //         return;
    //     }

    //     $data = $this->request->data;

    //     if (empty($data['emp_data'])) {
    //         echo json_encode(['success' => false, 'message' => 'No employees selected']);
    //         return;
    //     }

    //     $branch_code = $data['branch_code'];
    //     $month_year  = $data['month_year'];

    //     // get cycle start
    //     $cycle = $this->AttendanceRegister->query("
    //         SELECT att_start_end_fn('{$month_year}-01',1) AS start_date,
    //                att_start_end_fn('{$month_year}-01',2) AS end_date
    //     ");
    //     $cycleStart = isset($cycle[0][0]['start_date']) ? $cycle[0][0]['start_date'] : ($month_year . '-01');

    //     try {
    //         $cycleStartDt = new DateTime($cycleStart);
    //     } catch (Exception $e) {
    //         $cycleStartDt = new DateTime($month_year . '-01');
    //     }

    //     foreach ($data['emp_data'] as $emp) {

    //         $emp_id = isset($emp['empPkey']) ? $emp['empPkey'] : 0;
    //         $timesheet = isset($emp['timesheet']) ? $emp['timesheet'] : [];


    //         $emp_salary_structure = $this->AttendanceRegister->query("
    //     SELECT e.structure_id, s.prorate_code
    //     FROM emp_proff e
    //     INNER JOIN salary_structure s 
    //         ON e.structure_id = s.structure_id
    //     WHERE e.emp_fkey = '$emp_id' LIMIT 1
    // ");

    // $emp_types=$this->AttendanceRegister->query("SELECT emp_type FROM emp_proff WHERE emp_fkey='$emp_id'");
    // $emp_type=$emp_types[0]['emp_proff']['emp_type'];



    // $attendance_data = $this->AttendanceRegister->query("
    //     SELECT na_ho_count, na_wo_count
    //     FROM attendance_register
    //     WHERE emp_fkey = '$emp_id'
    //       AND month_year = '$month_year'
    //       AND isdelete IN ('Y')
    //     LIMIT 1
    // ");
    // $na_ho_count = 0;
    // $na_wo_count = 0;

    // if (!empty($attendance_data)) {
    //     $na_ho_count = (float)$attendance_data[0]['attendance_register']['na_ho_count'];
    //     $na_wo_count = (float)$attendance_data[0]['attendance_register']['na_wo_count'];
    // }

    // if ($emp_type=='DAILY WAGES'){
    //     $salary_structure='2';
    // }
    // else{
    // $salary_structure=isset($emp_salary_structure[0]['s']['prorate_code']) ? $emp_salary_structure[0]['s']['prorate_code'] : 0 ;
    // }


    //         // Build fields
    //         $fields = [];
    //         for ($i = 1; $i <= 31; $i++) {
    //             $fields["FIELD$i"] = isset($timesheet[$i]) && $timesheet[$i] !== '' ? $timesheet[$i] : null;
    //         }

    //         // Fetch existing row
    //         $attRow = $this->AttendanceRegister->find('first', [
    //             'conditions' => [
    //                 'emp_fkey'    => $emp_id,
    //                 'branch_code' => $branch_code,
    //                 'month_year'  => $month_year
    //             ]
    //         ]);

    //         // Source to detect leaves
    //         $source = !empty($attRow) ? $attRow['AttendanceRegister'] : $fields;

    //         // ---------- APPLY LEAVES ----------
    //         for ($i = 1; $i <= 31; $i++) {

    //             $fieldKey = "FIELD$i";
    //             $val = isset($source[$fieldKey]) ? trim($source[$fieldKey]) : '';

    //             if ($val === '') continue;


    //             $nonLeave = ['P','A','NA','WO','HO','LOP'];

    //             $codes = explode('/', $val);
    //             $codes = array_map('trim', $codes);

    // // 1️⃣ Check if full-day leave
    // if (count($codes) === 2 && $codes[0] === $codes[1] && !in_array($codes[0], $nonLeave)) {
    //     $leaveCode = strtoupper($codes[0]);

    //     $dateDt = clone $cycleStartDt;
    //     $dateDt->modify('+' . ($i - 1) . ' days');
    //     $applyDate = $dateDt->format('Y-m-d');

    //     // Pass full session
    //     $this->AddLeave($leaveCode, $applyDate, $emp_id, 'full');
    //     continue;
    // }

    // // 2️⃣ Handle half-day leaves (mixed day like CL/P or P/CL)
    // foreach ($codes as $index => $code) {
    //     if (trim($code) === '' || in_array(trim($code), $nonLeave)) continue;

    //     $cleanCode = strtoupper(trim($code));
    //     $session = ($index === 0 ? 'first' : 'second');

    //     $dateDt = clone $cycleStartDt;
    //     $dateDt->modify('+' . ($i - 1) . ' days');
    //     $applyDate = $dateDt->format('Y-m-d');

    //     $this->AddLeave($cleanCode, $applyDate, $emp_id, $session);
    // }

    //         }

    //         // ---------- SAVE UPDATED ATTENDANCE ----------
    //         $saveData = array_merge($fields, [
    //             'emp_fkey'       => $emp_id,
    //             'branch_code'    => $branch_code,
    //             'month_year'     => $month_year,
    //             'isdelete'       => 'N',
    //             'record_status'  => 1,
    //             'userid'         => $this->Session->read('login_user_id'),
    //             'created_time'   => date('Y-m-d H:i:s')
    //         ]);

    //         if ($attRow) {
    //             $this->AttendanceRegister->id = $attRow['AttendanceRegister']['registerid'];
    //         } else {
    //             $this->AttendanceRegister->create();
    //         }

    //         $this->AttendanceRegister->save($saveData);



    //         $saved = $this->AttendanceRegister->find('first', [
    //             'conditions' => [
    //                 'emp_fkey'    => $emp_id,
    //                 'branch_code' => $branch_code,
    //                 'month_year'  => $month_year,
    //                 'isdelete'    => 'N'
    //             ]
    //         ]);

    //         if (!empty($saved)) {

    //             $row = $saved['AttendanceRegister'];

    //             $present = 0; 
    //             $leave = 0; 
    //             $lop = 0; 
    //             $weekoff = 0; 
    //             $holiday = 0; 
    //             $na = 0;

    //             for ($i = 1; $i <= 31; $i++) {

    //                 $val = isset($row["FIELD$i"]) ? strtoupper(trim($row["FIELD$i"])) : '';

    //                 if ($val == '') continue;

    //                 $parts = explode('/', $val);
    //                 $weight = (count($parts) > 1 ? 0.5 : 1);

    //                 foreach ($parts as $p) {
    //                     $p = strtoupper(trim($p));
    //                     if ($p === '') continue;

    //                     if (in_array($p, ['P','P/P','A/P','P/A'])) {
    //                         $present += $weight;
    //                     }
    //                     elseif (in_array($p, ['WO','W/O','/WO'])) {
    //                         $weekoff += $weight;
    //                     }
    //                     elseif ($p == 'HO') {
    //                         $holiday += $weight;
    //                     }
    //                     elseif ($p == 'NA') {
    //                         $na += $weight;
    //                     }
    //                     elseif ($p === 'LOP') {
    //                         $lop += $weight;
    //                     }
    //                     elseif ($p != 'A') {
    //                         $leave += $weight;
    //                     }
    //                 }
    //             }

    //             $loponly=$lop;

    //             $lop=$lop+$na;


    //             if($salary_structure !=='2'){

    //             $lop=$lop+$na_ho_count+$na_wo_count;
    //             }


    //             // calendar days
    //             $cal = $this->AttendanceRegister->query("
    //                 SELECT calander_days FROM attendance_register 
    //                 WHERE registerid = '{$row['registerid']}'
    //             ");

    //             $cal_days = $cal[0]['attendance_register']['calander_days'];


    //             $workingDays = $cal_days-($weekoff + $holiday);

    //             // update totals
    //             $this->AttendanceRegister->query("
    //                 UPDATE attendance_register
    //                 SET 
    //                     presant_total = '{$present}',
    //                     lop_total = '{$lop}',
    //                     lop_only = '{$loponly}',
    //                     leave_total = '{$leave}',
    //                     weekoff_total = '{$weekoff}',
    //                     holiday_total = '{$holiday}',
    //                     working_days = '{$workingDays}'
    //                 WHERE registerid = '{$row['registerid']}'
    //             ");

    //         } // end totals block

    //     } // end foreach employee

    //     echo json_encode(['success' => true, 'message' => 'Attendance verified and leaves applied + totals updated']);
    //     return;
    // }






    //end

    public function empregisterbook($monthdd = '', $emp_pkey = '', $branch = '')
    {
        //debug($emp_pkey);
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $company_code = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = $monthdd;
        $yearmonth = $month . '-01';
        //  debug($yearmonth);
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
            $condition = "emp_detail_timeattandance.emp_pkey = '$emp_pkey' and ";
            $emp = $emp_pkey;
        } else {
            $condition = '';
            $emp = NULL;
        }
        if ($branch != 0) {
            $condition1 = "emp.emp_branch = '$branch' and ";
            $branch = $branch;
        } else {
            $condition1 = "";
            $branch = NULL;
        }
        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);

        $emp_condition = "";
        $employee_pkeys = $emp_pkey;
        if (!$shiftdetailed = $this->EditPunches->query("select * from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_details where emp_pkey = '$employee_pkeys' )")) {
            return FALSE;
            die();
        }
        // if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
        //     if (!$this->EditPunches->query("SELECT time_duration_check_multishift('$yearmonth', '$employee_pkeys', NULL)")) {
        //         return false;
        //         die();
        //     }
        // } else {
        //     if (!$this->EditPunches->query("SELECT time_duration_check('$yearmonth', '$employee_pkeys', NULL)")) {
        //         return false;
        //         die();
        //     }
        // }
        //$attend= $this->EmployeeDetails->query("select time_duration_check('$yearmonth','$emp','$branch')");
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $attendances = $this->EmployeeDetails->query("select empdetails.first_name,empdetails.last_name,emp_detail_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey from emp_detail_timeattandance left join emp_details as empdetails on(empdetails.emp_pkey = emp_detail_timeattandance.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where $emp_condition $condition $condition1 yearmonth = '$yearmonth' order by att_date ");
        $this->set('arr_dates', $arr_dates);
        //debug($attendances);
        $employee_attendance = array();
        foreach ($attendances as $val) {
            $emppk = $val['emp_detail_timeattandance']['emp_pkey'];
            $employee_attendance[$emppk][] = $val;
        }
        $this->set('employee_attendance', $employee_attendance);
    }

    // Add new function for bank search box  Added By Nimisha 19-03-2019
    public function getbranches()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
        } else {
            $emp_pkeys = 0;
        }
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
        //        $this->set('arr_branches', $arr_branches);
        //debug($arr_branches);
        $array = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($arr_branches as $key => $value) {
            //            debug($value);
            $branch[] = array(
                'id' => $value['branch_code'],
                'text' => $value['branch_name']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
    // function End 
    // public function jsons($branch = '', $resigned = '')
    // {
    //     $this->autoRender = false;
    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //     // debug($branch);
    //     //debug($_REQUEST['q']);
    //     $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
    //     //edited by megha on 13_09_19 added condition for branch != 0
    //     if ($branch == '0') {
    //         $branch_condition = "";
    //     } else
    //     if ($branch != null) {
    //         $branch_condition = " and branch_code in ('$branch') ";
    //     } else {
    //         $branch_condition = "";
    //     }
    //     //added by megha on 8_6_19 resigned employee data
    //     if ($resigned == '1') {
    //         $resign_condition =  "  emp_details.status in('1','2') ";
    //     } else {
    //         $resign_condition = "  emp_details.status = '1' ";
    //     }
    //     //end
    //     if ($q != null) {
    //         // $q_condition = " first_name like '%$q%' and ";
    //         $q_condition = " and (first_name like '%$q%' OR emp_proff.emp_company_id like '%$q%' ) "; //Emp Company Id Added by ***ARUL P DAS on 19/12/2019

    //     } else {
    //         $q_condition = "";
    //     }
    //     if ($this->Session->read('emp_fkey')) {
    //         $emp_pkeys = $this->Session->read('emp_fkey');
    //         // $emp_condition = " and emp_proff.attr1 = '$emp_pkeys'  ";
    //         $emp_condition = "";
    //     } else {
    //         $emp_condition = "";
    //     }
    //     //added by megha on 8_6_19 resigned employee data
    //     //$branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where status = 1 $branch_condition $q_condition $emp_condition ORDER BY emp_pkey DESC ");
    //     //$branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where  $branch_condition $q_condition $emp_condition $resign_condition ORDER BY emp_pkey DESC ");
    //     //$branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.emp_pkey not in (SELECT emp_fkey FROM `termination` where termination.status=1) $resign_condition $branch_condition $q_condition $emp_condition ORDER BY emp_pkey DESC ");
    //     $branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where  $resign_condition $branch_condition $q_condition $emp_condition ORDER BY first_name ASC ");

    //     $array = array();
    //     $branch = array();
    //     $branch[] = array("id" => "0", "text" => "ALL");
    //     foreach ($branch_array as $key => $value) {
    //         $branch[] = array(
    //             'id' => $value['emp_details']['emp_pkey'],
    //             'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
    //         );
    //     }
    //     $array['items'] = $branch;
    //     echo json_encode($array);

    //     //        $array = array(
    //     //            "items"=>
    //     //            array(
    //     //            array(
    //     //                "id"=>0,
    //     //                'text'=>'sanjun'
    //     //                
    //     //            ),
    //     //            array(
    //     //                "id"=>1,
    //     //                "text"=>'ananthu'
    //     //                
    //     //            ),
    //     //            array(
    //     //                "id"=>2,
    //     //                "text"=>'sruthi'
    //     //                
    //     //            )
    //     //                )
    //     //        );
    //     //echo json_encode($array) ;
    // }

    public function jsons($branch = '', $resigned = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code'));
        $is_glet = (strpos($company_code, 'GLET') !== false);

        if (in_array($company_code, ['GLET', 'GAAR', 'ETNA', 'MNDM', 'FYNK', 'TUDS', 'IFHY', 'STFR', 'IMSC', 'ELKT', 'TSSM', 'STPN', 'FRSG', 'BNGL', 'CKWR', 'DVDS', 'INFR', 'LBLD', 'LNTT'])) {
            // New Robust Logic for GLET
            $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
            $user_fkey = $this->Session->read('emp_fkey');
            $user_group = $this->Session->read('user_group');
            $feature_fkey = $this->Session->read('current_feature_id');
            if (!$feature_fkey)
                $feature_fkey = 33;

            $resign_condition = ($resigned == '1') ? " emp_details.status IN ('1','2') " : " emp_details.status = '1' ";
            $q_condition = ($q != null) ? " AND (emp_details.first_name LIKE '%$q%' OR emp_proff.emp_company_id LIKE '%$q%') " : "";

            $access_condition = "";
            // $user_group   = $this->Session->read('user_group');
            $company_code = $this->Session->read('company_code');
            if ($user_group == 2 && in_array($company_code, ['GLET', 'GAAR', 'ETNA', 'MNDM', 'FYNK', 'TUDS', 'IFHY', 'STFR', 'IMSC', 'ELKT', 'TSSM', 'STPN', 'FRSG', 'BNGL', 'CKWR', 'DVDS', 'INFR', 'LBLD', 'LNTT'])) {
                $access = $this->EmployeeDetails->query("
                    SELECT branch_fkey, is_hierarchy 
                    FROM user_feature_branch_access 
                    WHERE user_fkey = ? AND feature_fkey = ? AND LCASE(active) = 'y'
                ", array($user_fkey, $feature_fkey));

                if (!empty($access)) {
                    $is_hierarchy_active = 'N';
                    $allocated_branches = array();
                    foreach ($access as $row) {
                        if (strtoupper($row['user_feature_branch_access']['is_hierarchy']) == 'Y')
                            $is_hierarchy_active = 'Y';
                        $allocated_branches[] = $row['user_feature_branch_access']['branch_fkey'];
                    }

                    if ($is_hierarchy_active == 'Y') {
                        $access_condition = " AND (emp_proff.attr1 = '$user_fkey' OR emp_proff.emp_fkey = '$user_fkey') ";
                    } else {
                        if (!empty($allocated_branches)) {
                            $b_str = implode("','", $allocated_branches);
                            $access_condition = " AND emp_proff.emp_branch IN ('$b_str') ";
                        }
                    }
                } else {
                    $access_condition = " AND emp_proff.emp_fkey = '$user_fkey' ";
                }
            }

            $branch_condition = "";
            if ($branch && $branch != '0' && $branch != 'null') {
                $branch_condition = " AND emp_proff.emp_branch = '$branch' ";
            }

            $sql = "SELECT emp_details.*, emp_proff.* 
                    FROM emp_details 
                    LEFT JOIN emp_proff ON (emp_details.emp_pkey = emp_proff.emp_fkey) 
                    WHERE $resign_condition $q_condition $access_condition $branch_condition 
                    ORDER BY emp_details.first_name ASC";

            $results = $this->EmployeeDetails->query($sql);

            $items = array();
            $items[] = array("id" => "0", "text" => "ALL");
            foreach ($results as $row) {
                $items[] = array(
                    'id' => $row['emp_details']['emp_pkey'],
                    'text' => $row['emp_details']['first_name'] . ' ' . $row['emp_details']['last_name'] . ' - ' . $row['emp_proff']['emp_company_id']
                );
            }

            echo json_encode(array('items' => $items));
            exit;
        } else {
            // Legacy Logic for non-GLET
            $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
            if ($branch == '0') {
                $branch_condition = "";
            } else if ($branch != null) {
                $branch_condition = " and branch_code in ('$branch') ";
            } else {
                $branch_condition = "";
            }
            if ($resigned == '1') {
                $resign_condition = "  emp_details.status in('1','2') ";
            } else {
                $resign_condition = "  emp_details.status = '1' ";
            }
            if ($q != null) {
                $q_condition = " and (first_name like '%$q%' OR emp_proff.emp_company_id like '%$q%' ) ";
            } else {
                $q_condition = "";
            }
            if ($this->Session->read('emp_fkey')) {
                $emp_condition = ""; // Preserving old behavior
            } else {
                $emp_condition = "";
            }

            $branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where  $resign_condition $branch_condition $q_condition $emp_condition ORDER BY first_name ASC ");

            $items = array();
            $items[] = array("id" => "0", "text" => "ALL");
            foreach ($branch_array as $value) {
                $items[] = array(
                    'id' => $value['emp_details']['emp_pkey'],
                    'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
                );
            }
            echo json_encode(array('items' => $items));
            exit;
        }
    }

    public function filter()
    {
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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



        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);

        // $attend = $this->EmployeeDetails->query("select time_duration_check('$yearmonth')");
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $attendances = $this->EmployeeDetails->query("select empdetails.first_name,empdetails.last_name,emp_detail_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey from emp_detail_timeattandance left join emp_details as empdetails on(empdetails.emp_pkey = emp_detail_timeattandance.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where yearmonth = '$yearmonth' ");
        $this->set('arr_dates', $arr_dates);

        $employee_attendance = array();
        foreach ($attendances as $val) {
            $emppk = $val['emp_detail_timeattandance']['emp_pkey'];
            $employee_attendance[$emppk][] = $val;
        }
        //  debug($employee_attendance);
        $this->set('employee_attendance', $employee_attendance);
    }

    public function index()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
        } else {
            $emp_pkeys = 0;
        }
        //edited by sinsiya 26-02-2024
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
        }
        //edited by ASHIN on 29-11-24
        $company_code = strtoupper($this->Session->read('company_code'));
        if (($company_code == 'DEMO' || $company_code == 'BKHS') && $user_group == 2) {

            //edited by ASHIN ANTONY on 04-12-24       
            //  $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
            $conditions = array("status = 1 and branch_code in (select distinct(emp_branch) from emp_proff join emp_details on (emp_proff.emp_fkey=emp_details.emp_pkey) where (emp_proff.attr1 = " . $cur_emp_key . " OR emp_proff.emp_fkey = " . $cur_emp_key . ") and emp_details.status=1)");

            $arr_branches = $this->Units->find('all', array('fields' => 'id,branch_code,branch_name', 'conditions' => array($conditions)));
        } else {
            if (isset($payroUser[0]['emp_proff']['payro_priv'])) {
                $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
            } else {
                $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
            }
        }
        //    debug($arr_branches);
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);
        $this->set('company_code', $company_code); // edited by ASHIN ANTONY on 04-12-24
        //debug($arr_branches);
        $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        $this->set("arr_employees", $arr_employees);
    }

    public function empindex()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
        } else {
            $emp_pkeys = 0;
        }
        $this->set("emp", $emp_pkeys);
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
        $this->set('arr_branches', $arr_branches);
        //debug($arr_branches);
        $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        $this->set("arr_employees", $arr_employees);
    }

    public function showregister()
    {
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');


        $fields = 'AttendanceRegister.*';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey'
                )
            )
        );
        $emp_fkey = $this->Session->read('emp_fkey');
        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array(
            "fields" => array("emp_pkey", "emp_name"),
            'joins' => $joins,
            "conditions" => array('status' => 1, 'EmployeeProffessional.attr1' => $emp_fkey)
        )));
        $this->set('arr_employees', $arr_employees);
        //   debug($arr_employees);
        $arr_registerentries = array(
            'P' => array(
                'label' => 'Present',
                'color' => 'green',
                'textColor' => 'white'
            ),
            'L' => array(
                'label' => 'On Leave',
                'color' => 'orange',
                'textColor' => 'white'
            ),
            'WO' => array(
                'label' => 'Week Off',
                'color' => 'yellow',
                'textColor' => 'black'
            ),
            'HO' => array(
                'label' => 'Holiday',
                'color' => 'blue',
                'textColor' => 'white'
            ),
            'A' => array(
                'label' => 'Absent',
                'color' => 'red',
                'textColor' => 'white'
            ),
            'LOP' => array(
                'label' => 'Loss Of Pay',
                'color' => 'red',
                'textColor' => 'white'
            ),
            'OTHERS' => array(
                'label' => 'Others',
                'color' => 'deepskyblue',
                'textColor' => 'white'
            )
        );
        $this->set('arr_registerentries', $arr_registerentries);
    }

    public function showregistertab($verified = 0)
    {
        $this->set('tab', $verified);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $fields = 'AttendanceRegister.*';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey'
                )
            )
        );
        $emp_fkey = $this->Session->read('emp_fkey');
        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array(
            "fields" => array("emp_pkey", "emp_name"),
            'joins' => $joins,
            "conditions" => array('status' => 1, 'EmployeeProffessional.attr1' => $emp_fkey)
        )));
        // $this->set('arr_employees', $arr_employees);
        $arr_requestdata = $this->request->data;
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : date('Y-m');


        //  $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"),'joins' => $joins, "conditions" => array('status' => 1))));
        $this->set('arr_employees', $arr_employees);

        //Fetch company's attendance end date
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));

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

        //On 27 Dec 2015
        //$date_start = date('Y-m-d',strtotime($month.'-'.$att_startdate));
        //$date_end = date('Y-m-d',strtotime('-1 day',strtotime('+1 months',strtotime($date_start))));
        //On 26/01/2016
        $date_start = date('Y-m-d', strtotime('-1 months', strtotime($month . '-' . $att_startdate)));
        $date_end = date('Y-m-d', strtotime($month . '-' . $att_enddate));

        //$date_start = current($arr_dates);
        //$date_end = end($arr_dates);		

        $this->set('date_start', $date_start);
        $this->set('date_end', $date_end);
    }

    /*
     * List attendance register
     * By santhosh on 02 Aug 2015
     */

    public function listregisterentries()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $conditions = array('AttendanceRegister.isdelete="Y"');
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $conditions[] = 'AttendanceRegister.branch_code="' . $_REQUEST['branch'] . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $conditions[] = 'AttendanceRegister.emp_fkey=' . $_REQUEST['employee'];
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $conditions[] = 'AttendanceRegister.month_year="' . $_REQUEST['month'] . '"';
        } else {
            $conditions[] = 'AttendanceRegister.month_year="' . date('Y-m', strtotime(date('M-Y'))) . '"';
        }

        $fields = 'AttendanceRegister.*';
        $joins = array(
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'AttendanceRegister.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'AttendanceRegister.emp_fkey = EmployeeDetails.emp_pkey',
                    'EmployeeDetails.status=1'
                )
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeProffessional.attr1 = EmployeeDetails.emp_pkey'
                )
            )
        );

        $this->datatable["conditions"] = $conditions;
        $resp_register = array();
        $resp_register["rows"] = array();
        $count = $this->AttendanceRegister->find("count", array("conditions" => $conditions));
        $arr_register = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'limit' => intval($limit), 'offset' => intval($ofst)));
        foreach ($arr_register as $key => $value) {
            $resp_register["rows"][$key] = $value["AttendanceRegister"];

            $int_days_present = count(array_keys($value["AttendanceRegister"], "P"));
            $int_days_leave = count(array_keys($value["AttendanceRegister"], "L"));
            $int_days_holidays = count(array_keys($value["AttendanceRegister"], "HO"));

            $resp_register["rows"][$key]['days_present'] = $int_days_present;
            $resp_register["rows"][$key]['days_leave'] = $int_days_leave;
            $resp_register["rows"][$key]['days_holidays'] = $int_days_holidays;
        }
        $resp_register["total"] = $count;
        echo json_encode($resp_register);
    }

    public function listverifiedregisterentries()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $conditions = array('AttendanceRegister.isdelete="N"');
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $conditions[] = 'AttendanceRegister.branch_code="' . $_REQUEST['branch'] . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $conditions[] = 'AttendanceRegister.emp_fkey=' . $_REQUEST['employee'];
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $conditions[] = 'AttendanceRegister.month_year="' . $_REQUEST['month'] . '"';
        } else {
            $conditions[] = 'AttendanceRegister.month_year="' . date('Y-m', strtotime(date('M-Y'))) . '"';
        }

        $fields = 'AttendanceRegister.*';
        $joins = array(
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'AttendanceRegister.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'AttendanceRegister.emp_fkey = EmployeeDetails.emp_pkey',
                    'EmployeeDetails.status=1'
                )
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeProffessional.attr1 = EmployeeDetails.emp_pkey'
                )
            )
        );

        $this->datatable["conditions"] = $conditions;
        $resp_register = array();
        $resp_register["rows"] = array();
        $count = $this->AttendanceRegister->find("count", array("conditions" => $conditions));
        $arr_register = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'limit' => intval($limit), 'offset' => intval($ofst)));
        foreach ($arr_register as $key => $value) {
            $resp_register["rows"][$key] = $value["AttendanceRegister"];

            $int_days_present = count(array_keys($value["AttendanceRegister"], "P"));
            $int_days_leave = count(array_keys($value["AttendanceRegister"], "L"));
            $int_days_holidays = count(array_keys($value["AttendanceRegister"], "HO"));

            $resp_register["rows"][$key]['days_present'] = $int_days_present;
            $resp_register["rows"][$key]['days_leave'] = $int_days_leave;
            $resp_register["rows"][$key]['days_holidays'] = $int_days_holidays;
        }
        $resp_register["total"] = $count;
        echo json_encode($resp_register);
    }

    public function processregisterentries()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        $outputParameter = array();
        $outputParameter[] = $this->Session->read('company_code'); //company_code
        $outputParameter[] = (isset($_POST['branch']) && $_POST['branch'] != '') ? $_POST['branch'] : '';
        $outputParameter[] = $this->Session->read("login_user_id"); //user id
        $outputParameter[] = (isset($_POST['month']) && $_POST['month'] != '') ? date('Y-m-d', strtotime($_POST['month'])) : '';
        $out = $this->AttendanceRegister->insertUpdateAttendanceRegisterProc($outputParameter);
        $result['success'] = 1;
        echo json_encode($result);
    }

    public function verifyregisterentries($registerid = 0)
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);

        $arr_requestdata = $this->request->data;
        //Modified On 21 Feb 2016
        if (isset($arr_requestdata["ids"])) {
            //$ar_ids = explode(",", $_REQUEST["ids"]);$arr_requestdata
            $arr_registerids = isset($arr_requestdata["ids"]) ? explode(",", $arr_requestdata["ids"]) : array();
            $ar_ids = array();
            foreach ($arr_registerids as $register_id) {
                if (empty(json_decode($this->checkifregistercanverify($register_id, $arr_requestdata)))) {
                    $ar_ids[] = $register_id;
                }
            }
            if (!empty($ar_ids)) {
                $this->AttendanceRegister->updateAll(
                    array('isdelete' => "'N'"),
                    array('AttendanceRegister.registerid' => $ar_ids)
                );
                $result['success'] = 1;
            } else {
                $result['success'] = 0;
            }
        } else if ($registerid != 0) {
            $this->AttendanceRegister->updateAll(
                array('isdelete' => "'N'"),
                array('AttendanceRegister.registerid' => $registerid)
            );
            $result['success'] = 1;
        }
        echo json_encode($result);
    }

    public function loadattendanceregisterheader()
    {
        $this->autoRender = FALSE;
        $arr_requestdata = $this->request->data;
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : date('Y-m');

        //Fetch company's attendance start date
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));

        //On 20 Feb 2016
        //$att_startdate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_enddate = date('t',strtotime($month));
        //$att_startdate = $att_enddate + 1;
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));

        $arr_columns = array();
        $arr_columns[] = array('field' => 'emp_name', 'title' => 'Employee name', 'width' => '10%');
        for ($i = $att_startdate; $i <= $att_enddate; $i++) {
            $arr_columns[] = array('field' => 'FIELD' . $i, 'title' => $i, 'width' => '3%', 'styler:styleDay');
        }
        $arr_columns[] = array('field' => 'days_present', 'title' => 'Days present', 'width' => '10%');
        $arr_columns[] = array('field' => 'days_leave', 'title' => 'Days on leave', 'width' => '10%');
        $arr_columns[] = array('field' => 'days_holidays', 'title' => 'Holidays', 'width' => '10%');
        echo json_encode($arr_columns);
    }

    //Modified On 21 Feb 2016
    public function checkifregistercanverify($registerid = 0, $arr_requestdata = array())
    {
        $this->autoRender = FALSE;
        if (empty($arr_requestdata)) {
            $arr_requestdata = $this->request->data;
        }
        $arr_misspunched_dates = array();
        if ($registerid != 0) {
            $startdate = isset($arr_requestdata['startdate']) ? $arr_requestdata['startdate'] : '';
            $enddate = isset($arr_requestdata['enddate']) ? $arr_requestdata['enddate'] : '';

            $arr_dates_between = $this->createDateRangeArray($startdate, $enddate);

            $startTimeStamp = strtotime($startdate);
            $endTimeStamp = strtotime($enddate);

            $timeDiff = abs($endTimeStamp - $startTimeStamp);

            $numberDays = $timeDiff / 86400;  // 86400 seconds in one day
            // and you might want to convert to integer
            $numberDays = intval($numberDays);

            $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
            $arr_register = Set::extract('/AttendanceRegister/.', $this->AttendanceRegister->find("first", array("conditions" => array('registerid' => $registerid))));

            /*
             * By santhosh on 27 Dec 2015
             */
            /* for($i=1;$i<=$numberDays+1;$i++){
              if($arr_register[0]['FIELD'.$i] == 'null' || $arr_register[0]['FIELD'.$i] == ''){
              $arr_misspunched_dates[$i] = $arr_dates_between[$i-1];
              }
              } */
            foreach ($arr_dates_between as $date) {
                $day = date('j', strtotime($date));
                if ($arr_register[0]['FIELD' . $day] == 'null' || $arr_register[0]['FIELD' . $day] == '') {
                    $arr_misspunched_dates[] = $date;
                }
            }
        }
        return json_encode($arr_misspunched_dates);
    }

    public function updateregisterentries($registerid = 0)
    {
        $this->set('registerid', $registerid);
        if ($registerid != 0) {
            $arr_requestdata = $this->request->data;
            $json_dates = $arr_requestdata['dates'];
            $arr_dates = json_decode($json_dates);
            $this->set('arr_dates', $arr_dates);
        }
    }

    public function submitregisterentry()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;
        $registerid = isset($arr_requestdata['hid-registerid']) ? $arr_requestdata['hid-registerid'] : 0;
        $count = isset($arr_requestdata['hid-count-missing']) ? $arr_requestdata['hid-count-missing'] : 0;
        if ($registerid != 0) {
            if ($count != 0) {
                $arr_fields = array();
                $arr_updateentries = array();
                for ($i = 0; $i < $count; $i++) {
                    $index = isset($arr_requestdata['hid-reg-field-' . $i]) ? $arr_requestdata['hid-reg-field-' . $i] : '';
                    $arr_fields[] = $index;
                    $arr_updateentries['FIELD' . $index] = isset($arr_requestdata['reg-date-' . $i]) ? '"' . $arr_requestdata['reg-date-' . $i] . '"' : '""';
                }
                $this->AttendanceRegister->updateAll(
                    $arr_updateentries,
                    array('AttendanceRegister.registerid' => $registerid)
                );

                //Check if all fields updated
                $arr_register = Set::extract('/AttendanceRegister/.', $this->AttendanceRegister->find("first", array("conditions" => array('registerid' => $registerid))));

                $canverify = true;

                foreach ($arr_fields as $index) {
                    if ($arr_register[0]['FIELD' . $index] == 'null' || $arr_register[0]['FIELD' . $index] == '') {
                        $canverify = false;
                    }
                }

                if ($canverify) {
                    //verify register entry
                    $this->verifyregisterentries($registerid);
                } else {
                    echo json_encode(array('success' => 2));
                }
            }
        } else {
            echo json_encode(array('success' => 0));
        }
    }

    /**
     * Returns every date between two dates as an array
     * @param string $startDate the start of the date range
     * @param string $endDate the end of the date range
     * @param string $format DateTime format, default is Y-m-d
     * @return array returns every date between $startDate and $endDate, formatted as "Y-m-d"
     */
    public function createDateRange($startDate, $endDate, $format = "Y-m-d")
    {
        $begin = new DateTime($startDate);
        $end = new DateTime($endDate);

        $interval = new DateInterval('P1D'); // 1 Day
        $dateRange = new DatePeriod($begin, $interval, $end);

        $range = [];
        foreach ($dateRange as $date) {
            $range[] = $date->format($format);
        }

        return $range;
    }

    public function createDateRangeArray($strDateFrom, $strDateTo)
    {
        // takes two dates formatted as YYYY-MM-DD and creates an
        // inclusive array of the dates between the from and to dates.
        // could test validity of dates here but I'm already doing
        // that in the main script

        $aryRange = array();

        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
            while ($iDateFrom < $iDateTo) {
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }
        return $aryRange;
    }

    //Edited by Akshay on 21-4-2024
    // public function editPunch($month = '', $emp_pkey = '', $dayIndex = '',$att_date='')
    // {
    //     //debug($emp_pkey);
    //     $this->DbConfig->useDbConfig = $this->Session->read('ds');
    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //     $company_code = strtoupper($this->Session->read('company_code'));
    //     $user_login = $this->Session->read('login_user_id');

    //     // $att_date_formatted=$month."-".$att_date;
    //     $this->set('dayIndex',$dayIndex);
    //      $this->set('month',$month);
    //     $this->set('att_date_formatted',$att_date);


    //     $dats = $month . '-01'; //date('Y-m-01');
    //     $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$dats', '%Y-%m-01'), 2) as monthly_att_todate");
    //     $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];

    //     $years = $this->EmployeeDetails->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = 0 and is_current_finyear = 'Y' and status = '1' and branch_code in (select branch_code from emp_details where emp_pkey = '$emp_pkey') ");
    //     $year = isset($years['0']['fin_year']['fin_year']) ? $years['0']['fin_year']['fin_year'] : 0;
    //     $arr_leaves_heads = $this->EmployeeDetails->query("SELECT salary_head_item_pkey,occurance,item_part FROM `salary_head_items` "
    //         . "WHERE head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) "
    //         . "and (salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where LEAVEPOLICY_GROUP_ID IN "
    //         . "(SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_pkey') and status = 1 ) OR item_part = 'Indirect')");
    //     $arr_leave = array();
    //     //DEBUG($arr_leaves_heads);

    //     // $this->set('emp_detail_timeattandance_pkey', $edtPkey);
    //        $this->set('emp_pkey', $emp_pkey);

    //     // $arr_status = $this->EmployeeDetails->query("SELECT present FROM emp_detail_timeattandance WHERE emp_detail_timeattandance_pkey = '$edtPkey'");
    //     // // debug($arr_status);
    //     // $status = $arr_status[0]['emp_detail_timeattandance']['present'];
    //     // $this->set('status', $status);

    //     // $arr_status = $this->EmployeeDetails->query("SELECT presant_total FROM attendance_register WHERE emp_fkey = '$emp_pkey' AND month_year='$month'");
    //     // // debug($arr_status);
    //     // $status = $arr_status[0]['attendance_register']['presant_total'];
    //     // $this->set('status', $status);


    //     // foreach ($arr_leaves_heads as $key => $val) {
    //     //     $head = $val['salary_head_items']['occurance'];
    //     //     $slary_head_item_pkey = $val['salary_head_items']['salary_head_item_pkey'];
    //     //     $item_part = isset($val['salary_head_items']['item_part']) ? $val['salary_head_items']['item_part'] : '';

    //     //     // if($slary_head_item_pkey == '105'){
    //     //     //     continue;
    //     //     // }

    //     //     $isIndirect = (strcasecmp($item_part, 'Indirect') === 0);
    //     //     $resp = 0;

    //     //     if (!$isIndirect) {
    //     //         // $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
    //     //         $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_year_fn('$emp_pkey','$slary_head_item_pkey','$att_date') as LeaveBalance");
    //     //         // debug($lbalance);
    //     //         // debug("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
    //     //         $resp = isset($lbalance['0']['0']['LeaveBalance']) ? round($lbalance['0']['0']['LeaveBalance'], 1) : 0;
    //     //     }

    //     //     $arr_leave[] = array(
    //     //         "salary_head_item_pkey" => $slary_head_item_pkey,
    //     //         "Head" => $head,
    //     //         "leaveBalance" => $resp,
    //     //         "isIndirect" => $isIndirect,
    //     //         "itemPart" => $item_part
    //     //     );
    //     // }
    //     // $this->set('arr_leave', $arr_leave);

    //     // edited by athira on 17-03-2026
    //     $unique_leaves = array();
    // foreach ($arr_leaves_heads as $key => $val) {
    //     $head = strtoupper(trim($val['salary_head_items']['occurance']));
    //     $slary_head_item_pkey = $val['salary_head_items']['salary_head_item_pkey'];
    //     $item_part = isset($val['salary_head_items']['item_part']) ? $val['salary_head_items']['item_part'] : '';
    //     $isIndirect = (strcasecmp($item_part, 'Indirect') === 0);

    //     // Skip indirect LOP as frontend already handles it explicitly
    //     if ($head === 'LOP' && $isIndirect) {
    //         continue;
    //     }

    //     // Prioritize Policy leaves over Indirect leaves for the same Occurrence Head
    //     if (!isset($unique_leaves[$head]) || ($unique_leaves[$head]['isIndirect'] && !$isIndirect)) {
    //         $resp = 0;
    //         if (!$isIndirect) {
    //             // $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
    //             $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_year_fn('$emp_pkey','$slary_head_item_pkey','$att_date') as LeaveBalance");
    //             $resp = isset($lbalance['0']['0']['LeaveBalance']) ? round($lbalance['0']['0']['LeaveBalance'], 1) : 0;
    //         }

    //         $unique_leaves[$head] = array(
    //             "salary_head_item_pkey" => $slary_head_item_pkey,
    //             "Head" => $val['salary_head_items']['occurance'], // keep original case for display
    //             "leaveBalance" => $resp,
    //             "isIndirect" => $isIndirect,
    //             "itemPart" => $item_part
    //         );
    //     }
    // }
    // $arr_leave = array_values($unique_leaves);
    // $this->set('arr_leave', $arr_leave);
    // // ended by athira 17-03-2026
    // }


    public function editPunch($month = '', $emp_pkey = '', $dayIndex = '', $att_date = '')
    {
        //debug($emp_pkey);
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code'));
        $user_login = $this->Session->read('login_user_id');

        // $att_date_formatted=$month."-".$att_date;
        $this->set('dayIndex', $dayIndex);
        $this->set('month', $month);
        $this->set('att_date_formatted', $att_date);


        $dats = $month . '-01'; //date('Y-m-01');
        $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$dats', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];

        $years = $this->EmployeeDetails->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = 0 and is_current_finyear = 'Y' and status = '1' and branch_code in (select branch_code from emp_details where emp_pkey = '$emp_pkey') ");
        $year = isset($years['0']['fin_year']['fin_year']) ? $years['0']['fin_year']['fin_year'] : 0;

        // Fetch joining date - added by athira on 08-04-2026
        $emp_proff_data = $this->EmployeeDetails->query("SELECT joining_date, LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey = '$emp_pkey' LIMIT 1");
        $joiningDate = isset($emp_proff_data[0]['emp_proff']['joining_date']) ? $emp_proff_data[0]['emp_proff']['joining_date'] : null;
        $leavePolicyGroupId = isset($emp_proff_data[0]['emp_proff']['LEAVEPOLICY_GROUP_ID']) ? $emp_proff_data[0]['emp_proff']['LEAVEPOLICY_GROUP_ID'] : 0;

        // Updated query to fetch minimum_service and exception from leavepolicy - edited by athira on 08-04-2026
        $arr_leaves_heads = $this->EmployeeDetails->query("SELECT shi.salary_head_item_pkey, shi.occurance, shi.item_part, lp.minimum_service, lp.exceptions
            FROM `salary_head_items` AS shi 
            LEFT JOIN leavepolicy AS lp ON lp.salary_head_item_fkey = shi.salary_head_item_pkey 
                AND lp.LEAVEPOLICY_GROUP_ID = '$leavePolicyGroupId' 
                AND lp.status = 1
            WHERE shi.head_fkey IN (SELECT head_pkey FROM salary_heads WHERE LCASE(item_type)='leave' AND value='Y' AND status=1) 
            AND (shi.salary_head_item_pkey IN (SELECT salary_head_item_fkey FROM leavepolicy WHERE LEAVEPOLICY_GROUP_ID = '$leavePolicyGroupId' AND status = 1) 
                OR shi.item_part = 'Indirect')");
        $arr_leave = array();
        //DEBUG($arr_leaves_heads);

        // $this->set('emp_detail_timeattandance_pkey', $edtPkey);
        $this->set('emp_pkey', $emp_pkey);

        // $arr_status = $this->EmployeeDetails->query("SELECT present FROM emp_detail_timeattandance WHERE emp_detail_timeattandance_pkey = '$edtPkey'");
        // // debug($arr_status);
        // $status = $arr_status[0]['emp_detail_timeattandance']['present'];
        // $this->set('status', $status);

        // $arr_status = $this->EmployeeDetails->query("SELECT presant_total FROM attendance_register WHERE emp_fkey = '$emp_pkey' AND month_year='$month'");
        // // debug($arr_status);
        // $status = $arr_status[0]['attendance_register']['presant_total'];
        // $this->set('status', $status);


        // foreach ($arr_leaves_heads as $key => $val) {
        //     $head = $val['salary_head_items']['occurance'];
        //     $slary_head_item_pkey = $val['salary_head_items']['salary_head_item_pkey'];
        //     $item_part = isset($val['salary_head_items']['item_part']) ? $val['salary_head_items']['item_part'] : '';

        //     // if($slary_head_item_pkey == '105'){
        //     //     continue;
        //     // }

        //     $isIndirect = (strcasecmp($item_part, 'Indirect') === 0);
        //     $resp = 0;

        //     if (!$isIndirect) {
        //         // $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
        //         $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_year_fn('$emp_pkey','$slary_head_item_pkey','$att_date') as LeaveBalance");
        //         // debug($lbalance);
        //         // debug("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
        //         $resp = isset($lbalance['0']['0']['LeaveBalance']) ? round($lbalance['0']['0']['LeaveBalance'], 1) : 0;
        //     }

        //     $arr_leave[] = array(
        //         "salary_head_item_pkey" => $slary_head_item_pkey,
        //         "Head" => $head,
        //         "leaveBalance" => $resp,
        //         "isIndirect" => $isIndirect,
        //         "itemPart" => $item_part
        //     );
        // }
        // $this->set('arr_leave', $arr_leave);

        // edited by athira on 17-03-2026
        $unique_leaves = array();
        foreach ($arr_leaves_heads as $key => $val) {
            $head = strtoupper(trim($val['shi']['occurance']));
            $slary_head_item_pkey = $val['shi']['salary_head_item_pkey'];
            $item_part = isset($val['shi']['item_part']) ? $val['shi']['item_part'] : '';
            $minimumServiceMonths = isset($val['lp']['minimum_service']) ? (int) $val['lp']['minimum_service'] : 0;
            $exception = isset($val['lp']['exceptions']) ? $val['lp']['exceptions'] : 'N';
            $isIndirect = (strcasecmp($item_part, 'Indirect') === 0);

            $isMinimumService = false;
            // edited by athira on 10-04-2026

            // ended by athira on 10-04-2026

            // Skip indirect LOP as frontend already handles it explicitly
            if ($head === 'LOP' && $isIndirect) {
                continue;
            }


            // Prioritize Policy leaves over Indirect leaves for the same Occurrence Head
            if (!isset($unique_leaves[$head]) || ($unique_leaves[$head]['isIndirect'] && !$isIndirect)) {
                $resp = 0;

                if (!$isIndirect) {
                    // Check minimum service requirement - edited by athira on 10-04-2026

                    // if($exception=='Y'){
                    //     if ($joiningDate && $minimumServiceMonths > 0) {
                    //         $joiningDateObj = new DateTime($joiningDate);
                    //         $attDateObj = new DateTime($att_date);

                    //         $monthsOfService = (($attDateObj->format('Y') - $joiningDateObj->format('Y')) * 12) + ($attDateObj->format('n') - $joiningDateObj->format('n'));
                    //         if ((int)$attDateObj->format('d') < (int)$joiningDateObj->format('d')) {
                    //             $monthsOfService--;
                    //         }

                    //         if ($monthsOfService < $minimumServiceMonths) {
                    //             $isMinimumService = true;
                    //         }
                    //     }
                    //      }
                    //      else{
                    //         $isMinimumService = false;
                    //      }

                    $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_year_fn('$emp_pkey','$slary_head_item_pkey','$att_date') as LeaveBalance");
                    $resp = isset($lbalance['0']['0']['LeaveBalance']) ? round($lbalance['0']['0']['LeaveBalance'], 1) : 0;
                    // ended by athira on 10-04-2026
                }


                $unique_leaves[$head] = array(
                    "salary_head_item_pkey" => $slary_head_item_pkey,
                    "Head" => $val['shi']['occurance'], // keep original case for display
                    "leaveBalance" => $resp,
                    "isIndirect" => $isIndirect,
                    "itemPart" => $item_part,
                    "exception" => $exception,
                    "isMinimumService" => $isMinimumService, // added by athira on 10-04-2026
                    "joiningDate" => $joiningDate,
                    // "minServiceMonths" => $minimumServiceMonths
                );
            }
        }
        $arr_leave = array_values($unique_leaves);
        $this->set('arr_leave', $arr_leave);
        // ended by athira 17-03-2026
    }
    // public function editPunch($month = '', $emp_pkey = '', $dayIndex = '', $att_date = '')
    // {
    //     //debug($emp_pkey);
    //     $this->DbConfig->useDbConfig = $this->Session->read('ds');
    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //     $company_code = strtoupper($this->Session->read('company_code'));
    //     $user_login = $this->Session->read('login_user_id');

    //     // $att_date_formatted=$month."-".$att_date;
    //     $this->set('dayIndex', $dayIndex);
    //     $this->set('month', $month);
    //     $this->set('att_date_formatted', $att_date);


    //     $dats = $month . '-01'; //date('Y-m-01');
    //     $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$dats', '%Y-%m-01'), 2) as monthly_att_todate");
    //     $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];

    //     $years = $this->EmployeeDetails->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = 0 and is_current_finyear = 'Y' and status = '1' and branch_code in (select branch_code from emp_details where emp_pkey = '$emp_pkey') ");
    //     $year = isset($years['0']['fin_year']['fin_year']) ? $years['0']['fin_year']['fin_year'] : 0;

    //     // Fetch joining date - added by athira on 08-04-2026
    //     $emp_proff_data = $this->EmployeeDetails->query("SELECT joining_date, LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey = '$emp_pkey' LIMIT 1");
    //     $joiningDate = isset($emp_proff_data[0]['emp_proff']['joining_date']) ? $emp_proff_data[0]['emp_proff']['joining_date'] : null;
    //     $leavePolicyGroupId = isset($emp_proff_data[0]['emp_proff']['LEAVEPOLICY_GROUP_ID']) ? $emp_proff_data[0]['emp_proff']['LEAVEPOLICY_GROUP_ID'] : 0;

    //     // Updated query to fetch minimum_service and exception from leavepolicy - edited by athira on 08-04-2026
    //     $arr_leaves_heads = $this->EmployeeDetails->query("SELECT shi.salary_head_item_pkey, shi.occurance, shi.item_part, lp.minimum_service, lp.exceptions
    //         FROM `salary_head_items` AS shi 
    //         LEFT JOIN leavepolicy AS lp ON lp.salary_head_item_fkey = shi.salary_head_item_pkey 
    //             AND lp.LEAVEPOLICY_GROUP_ID = '$leavePolicyGroupId' 
    //             AND lp.status = 1
    //         WHERE shi.head_fkey IN (SELECT head_pkey FROM salary_heads WHERE LCASE(item_type)='leave' AND value='Y' AND status=1) 
    //         AND (shi.salary_head_item_pkey IN (SELECT salary_head_item_fkey FROM leavepolicy WHERE LEAVEPOLICY_GROUP_ID = '$leavePolicyGroupId' AND status = 1) 
    //             OR shi.item_part = 'Indirect')");
    //     $arr_leave = array();
    //     //DEBUG($arr_leaves_heads);

    //     // $this->set('emp_detail_timeattandance_pkey', $edtPkey);
    //     $this->set('emp_pkey', $emp_pkey);

    //     // $arr_status = $this->EmployeeDetails->query("SELECT present FROM emp_detail_timeattandance WHERE emp_detail_timeattandance_pkey = '$edtPkey'");
    //     // // debug($arr_status);
    //     // $status = $arr_status[0]['emp_detail_timeattandance']['present'];
    //     // $this->set('status', $status);

    //     // $arr_status = $this->EmployeeDetails->query("SELECT presant_total FROM attendance_register WHERE emp_fkey = '$emp_pkey' AND month_year='$month'");
    //     // // debug($arr_status);
    //     // $status = $arr_status[0]['attendance_register']['presant_total'];
    //     // $this->set('status', $status);


    //     // foreach ($arr_leaves_heads as $key => $val) {
    //     //     $head = $val['salary_head_items']['occurance'];
    //     //     $slary_head_item_pkey = $val['salary_head_items']['salary_head_item_pkey'];
    //     //     $item_part = isset($val['salary_head_items']['item_part']) ? $val['salary_head_items']['item_part'] : '';

    //     //     // if($slary_head_item_pkey == '105'){
    //     //     //     continue;
    //     //     // }

    //     //     $isIndirect = (strcasecmp($item_part, 'Indirect') === 0);
    //     //     $resp = 0;

    //     //     if (!$isIndirect) {
    //     //         // $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
    //     //         $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_year_fn('$emp_pkey','$slary_head_item_pkey','$att_date') as LeaveBalance");
    //     //         // debug($lbalance);
    //     //         // debug("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$att_enddate1','$year') as LeaveBalance");
    //     //         $resp = isset($lbalance['0']['0']['LeaveBalance']) ? round($lbalance['0']['0']['LeaveBalance'], 1) : 0;
    //     //     }

    //     //     $arr_leave[] = array(
    //     //         "salary_head_item_pkey" => $slary_head_item_pkey,
    //     //         "Head" => $head,
    //     //         "leaveBalance" => $resp,
    //     //         "isIndirect" => $isIndirect,
    //     //         "itemPart" => $item_part
    //     //     );
    //     // }
    //     // $this->set('arr_leave', $arr_leave);

    //     // edited by athira on 17-03-2026
    //     $unique_leaves = array();
    //     foreach ($arr_leaves_heads as $key => $val) {
    //         $head = strtoupper(trim($val['shi']['occurance']));
    //         $slary_head_item_pkey = $val['shi']['salary_head_item_pkey'];
    //         $item_part = isset($val['shi']['item_part']) ? $val['shi']['item_part'] : '';
    //         $minimumServiceMonths = isset($val['lp']['minimum_service']) ? (int) $val['lp']['minimum_service'] : 0;
    //         $exception = isset($val['lp']['exceptions']) ? $val['lp']['exceptions'] : 'N';
    //         $isIndirect = (strcasecmp($item_part, 'Indirect') === 0);

    //         $isMinimumService = false;
    //         // edited by athira on 10-04-2026

    //         // ended by athira on 10-04-2026

    //         // Skip indirect LOP as frontend already handles it explicitly
    //         if ($head === 'LOP' && $isIndirect) {
    //             continue;
    //         }


    //         // Prioritize Policy leaves over Indirect leaves for the same Occurrence Head
    //         if (!isset($unique_leaves[$head]) || ($unique_leaves[$head]['isIndirect'] && !$isIndirect)) {
    //             $resp = 0;

    //             if (!$isIndirect) {
    //                 // Check minimum service requirement - edited by athira on 10-04-2026

    //                 // if($exception=='Y'){
    //                 //     if ($joiningDate && $minimumServiceMonths > 0) {
    //                 //         $joiningDateObj = new DateTime($joiningDate);
    //                 //         $attDateObj = new DateTime($att_date);

    //                 //         $monthsOfService = (($attDateObj->format('Y') - $joiningDateObj->format('Y')) * 12) + ($attDateObj->format('n') - $joiningDateObj->format('n'));
    //                 //         if ((int)$attDateObj->format('d') < (int)$joiningDateObj->format('d')) {
    //                 //             $monthsOfService--;
    //                 //         }

    //                 //         if ($monthsOfService < $minimumServiceMonths) {
    //                 //             $isMinimumService = true;
    //                 //         }
    //                 //     }
    //                 //      }
    //                 //      else{
    //                 //         $isMinimumService = false;
    //                 //      }

    //                 $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_year_fn('$emp_pkey','$slary_head_item_pkey','$att_date') as LeaveBalance");
    //                 $resp = isset($lbalance['0']['0']['LeaveBalance']) ? round($lbalance['0']['0']['LeaveBalance'], 1) : 0;
    //                 // ended by athira on 10-04-2026
    //             }


    //             $unique_leaves[$head] = array(
    //                 "salary_head_item_pkey" => $slary_head_item_pkey,
    //                 "Head" => $val['shi']['occurance'], // keep original case for display
    //                 "leaveBalance" => $resp,
    //                 "isIndirect" => $isIndirect,
    //                 "itemPart" => $item_part,
    //                 "exception" => $exception,
    //                 "isMinimumService" => $isMinimumService, // added by athira on 10-04-2026
    //                 "joiningDate" => $joiningDate,
    //                 // "minServiceMonths" => $minimumServiceMonths
    //             );
    //         }
    //     }
    //     $arr_leave = array_values($unique_leaves);
    //     $this->set('arr_leave', $arr_leave);
    //     // ended by athira 17-03-2026
    // }
    //Edited by Akshay on 24-4-2024
    //     public function bulkipdatestatus()
    //     {

    //         $this->autoRender = FALSE;
    //         $this->EditPunches->useDbConfig = $this->Session->read('ds');

    //         // need to check about the N/A, WO, HO cases how to handle if they come 
    //         $device_attandance_seq = $_POST["device_attandance_seq"]; // updated string as text only
    //         $status = $_POST["status"]; // types
    //         $adstatus = $_POST["adstatus"]; // new selected status
    //         $month_year = $_POST["monthYear"]; //Edited by Akshay on 25-4-2024



    //         $arr_success = array();
    //         foreach ($device_attandance_seq as $key => $value) {

    //             $arr_attendance_register = $this->EditPunches->query("select count(*) as count from attendance_register where emp_fkey = '" .$value['empPkey']. "'"
    //                     . " AND month_year = '$month_year' AND isdelete = 'N'");
    //             $iseditable = isset($arr_attendance_register[0][0]['count']) ? $arr_attendance_register[0][0]['count'] : 0;
    //             if($iseditable == 0){
    //             if($adstatus == 'blank'){
    //                 $arr_data = $this->EditPunches->query("SELECT * FROM emp_detail_timeattandance WHERE  emp_pkey = '" . $value['empPkey'] . "' and " . " yearmonth = '" . date('Y-m-1', strtotime($month_year)) . "' and ((present is null or present = 'A/A') and leaves is null and weekoff is null and holiday is null and others is null)");
    //             }else{
    //                 $arr_data = $this->EditPunches->query("SELECT * FROM emp_detail_timeattandance WHERE  emp_pkey = '" . $value['empPkey'] . "' and " . " yearmonth = '" . date('Y-m-1', strtotime($month_year)) . "' ");
    //             }

    //             foreach($arr_data as $data){
    //                 $data = isset($data['emp_detail_timeattandance'])? $data['emp_detail_timeattandance']: array();
    //                 if (!empty($data)) {
    //                     if ($status != "0") {
    //                         if($adstatus == 'all'){
    //                          $this->checkLeaveExists($data['att_date'], $data['emp_pkey'], '');
    //                         }
    //                         // if ($adstatus != "0") {
    //                         //     $adStatsu = $adstatus;
    //                         // } else {
    //                         //     $adStatsu = $data['ad_present'];
    //                         // }

    //                         $adStatsu = '';

    // //                        try{
    // //
    // //                        }catch(Exception $e){
    //                             if ($status == "LOP") {
    //                                 $app = $this->AddLeave($status, $data['att_date'], $data['emp_pkey'], 'full');
    //                                 $get_emp = $this->EditPunches->query("INSERT INTO emp_detail_status_update (emp_fkey, att_date, yearmonth, main_status, aditional_status, creation_date, created_by) " . "VALUES ('" . $data['emp_pkey'] . "', '" . $data['att_date'] . "', '" . $data['yearmonth'] . "', '" . trim($status) . "', '" . $adStatsu . "', '" . date('Y-m-d H:i:s') . "', '" . $this->Session->read('login_user_id') . "') ");
    //                                 // $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET present = '', holiday = '', weekoff = '', others = '', leaves  = '" . $status . "' WHERE emp_detail_timeattandance_pkey = '" . $data[0]['emp_detail_timeattandance']['emp_detail_timeattandance_pkey'] . "' ");
    //                             } else {
    //                                 $get_emp = $this->EditPunches->query("INSERT INTO emp_detail_status_update (emp_fkey, att_date, yearmonth, main_status, aditional_status, creation_date, created_by) " . "VALUES ('" . $data['emp_pkey'] . "', '" . $data['att_date'] . "', '" . $data['yearmonth'] . "', '" . trim($status) . "', '" . $adStatsu . "', '" . date('Y-m-d H:i:s') . "', '" . $this->Session->read('login_user_id') . "') ");
    //                                 // $get_emps = $this->EditPunches->query("UPDATE emp_detail_timeattandance SET present = '" . $status . "', holiday = '', weekoff = '', others = '', leaves  = '' WHERE emp_detail_timeattandance_pkey = '" . $data[0]['emp_detail_timeattandance']['emp_detail_timeattandance_pkey'] . "' ");
    //                             }
    //                             $arr_success[] = array(
    //                                 'emp_pkey' => $data['emp_pkey'],
    //                                 'att_date' => $data['att_date'],
    //                                 'status' => 'Success'
    //                             );
    //                         //}
    //                     }
    //                 }
    //             }
    //             $resp = array('success' => true, 'array_success' => $arr_success);
    //             }else{
    //               $resp = array('success' => false, 'array_success' => $arr_success);  
    //             }
    //         }


    //         echo json_encode($resp);
    //     }



    // edited by athira on 26-03-2026
    public function bulkipdatestatus()
    {
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');

        $device_attandance_seq = $_POST["device_attandance_seq"];
        $status = strtoupper($_POST["status"]);  // e.g. P/P, A/A, CL/CL etc.
        $adstatus = $_POST["adstatus"];            // blank / all
        $month_year = $_POST["monthYear"];
        $yearmonth = $month_year . "-01";

        $arr_success = [];

        // ✅ Get attendance cycle start & end date
        $cycleStart = $this->EditPunches->query("SELECT att_start_end_fn('$yearmonth', 1) AS dt");
        $cycleEnd = $this->EditPunches->query("SELECT att_start_end_fn('$yearmonth', 2) AS dt");

        $startDate = $cycleStart[0][0]['dt'];
        $endDate = $cycleEnd[0][0]['dt'];

        foreach ($device_attandance_seq as $value) {
            $empPkey = $value['empPkey'];

            $emp_salary_structure = $this->EditPunches->query("
    SELECT e.emp_structure_id, s.prorate_code
    FROM emp_salary_structure e
    INNER JOIN salary_structure s 
        ON e.emp_structure_id = s.structure_id
    WHERE e.emp_fkey = '$empPkey' LIMIT 1
");

            $attendance_data = $this->EditPunches->query("
    SELECT na_ho_count, na_wo_count
    FROM attendance_register
    WHERE emp_fkey = '$empPkey'
      AND month_year = '$month_year'
      AND isdelete IN ('Y')
    LIMIT 1
");
            $na_ho_count = 0;
            $na_wo_count = 0;

            if (!empty($attendance_data)) {
                $na_ho_count = (float) $attendance_data[0]['attendance_register']['na_ho_count'];
                $na_wo_count = (float) $attendance_data[0]['attendance_register']['na_wo_count'];
            }




            $otRows = $this->EditPunches->query("
    SELECT att_date, isdelete
    FROM emp_ot_timeattandance
    WHERE emp_pkey = '$empPkey'
      AND att_date BETWEEN '$startDate' AND '$endDate'
    ORDER BY att_date
");

            $otIsDelete = [];
            foreach ($otRows as $r) {
                $attDate = $r['emp_ot_timeattandance']['att_date'];
                $otIsDelete[$attDate] = $r['emp_ot_timeattandance']['isdelete']; // 'Y' or 'N'
            }

            $reg = $this->EditPunches->query("
            SELECT * FROM attendance_register
            WHERE emp_fkey = '$empPkey'
              AND month_year = '$month_year'
              AND isdelete IN ('Y')
        ");

            if (empty($reg)) {
                $arr_success[] = ['emp_pkey' => $empPkey, 'status' => 'No Record'];
                continue;
            }

            $row = $reg[0]['attendance_register'];

            // Extract policysheet from the frontend payload to perfectly match UI context
            $policysheet = isset($value['policysheet']) ? $value['policysheet'] : [];

            $current = strtotime($startDate);
            $end = strtotime($endDate);

            $i = 1;

            // ✅ 1. Update status for all eligible days

            $empDates = $this->EditPunches->query("
    SELECT 
        ep.joining_date,ep.emp_type,
        t.last_approved_working_date
    FROM emp_proff ep
    LEFT JOIN termination t 
        ON t.emp_fkey = ep.emp_fkey AND t.status=1
    WHERE ep.emp_fkey = '$empPkey' 
    LIMIT 1
");

            $emp_type = !empty($empDates[0]['ep']['emp_type'])
                ? $empDates[0]['ep']['emp_type']
                : null;

            if ($emp_type == 'DAILY WAGES') {
                $salary_structure = 2;
            } else {
                $salary_structure = isset($emp_salary_structure[0]['s']['prorate_code']) ? $emp_salary_structure[0]['s']['prorate_code'] : 0;
            }



            $joiningDate = !empty($empDates[0]['ep']['joining_date'])
                ? $empDates[0]['ep']['joining_date']
                : null;


            $lastWorkingDate = !empty($empDates[0]['t']['last_approved_working_date'])
                ? $empDates[0]['t']['last_approved_working_date']
                : null;

            while ($current <= $end) {
                $field = "FIELD" . $i;

                $att_date = date("Y-m-d", $current);

                // -----------------------------
                // ❌ BLOCK NA PERIOD DATES
                // -----------------------------
                $isNAPeriod = false;

                // Before joining
                if ($joiningDate && $att_date < $joiningDate) {
                    $isNAPeriod = true;
                }

                // After termination
                if ($lastWorkingDate && $att_date > $lastWorkingDate) {
                    $isNAPeriod = true;
                }

                // Existing value
                $existingValueRaw = isset($row[$field]) ? $row[$field] : "";
                $existingValue = strtoupper(trim($existingValueRaw));

                // NA status check
                // $hasNA = (strpos($existingValue, 'NA') !== false);

                // // ⛔ HARD STOP
                if ($isNAPeriod) {
                    $current = strtotime("+1 day", $current);
                    $i++;
                    continue;
                }


                // Skip cell if already verified (isdelete = 'N')
                if (isset($otIsDelete[$att_date]) && $otIsDelete[$att_date] === 'N') {
                    $current = strtotime("+1 day", $current);
                    $i++;
                    continue;
                }

                // Raw DB value
                $existingValueRaw = isset($row[$field]) ? $row[$field] : "";
                $existingValue = strtoupper(trim($existingValueRaw));

                // 1️⃣ IGNORE NA always
                if ($existingValue === "NA") {
                    $current = strtotime("+1 day", $current);
                    $i++;
                    continue;
                }

                // 2️⃣ Detect actual blank
                $isReallyBlank = ($existingValue === "" || $existingValue === NULL);

                // 3️⃣ Detect LOP based blank (any part has LOP)
                $hasLop = false;
                if (!$isReallyBlank && $existingValue !== "") {
                    $parts = explode('/', $existingValue);
                    foreach ($parts as $p) {
                        if (strpos(trim($p), "LOP") !== false) {
                            $hasLop = true;
                            break;
                        }
                    }
                }

                // Now final interpretation:
                // Blank date = (really blank) OR (LOP in any part)
                $isBlankDate = ($isReallyBlank || $hasLop);

                // 4️⃣ adstatus = blank → update only blank dates
                if ($adstatus == "blank" && !$isBlankDate) {
                    $current = strtotime("+1 day", $current);
                    $i++;
                    continue;
                }

                $existingParts = explode('/', $existingValue);
                $newParts = explode('/', $status);




                $finalParts = [];

                for ($j = 0; $j < 2; $j++) {
                    $existing = isset($existingParts[$j]) ? trim($existingParts[$j]) : '';
                    $new = isset($newParts[$j]) ? trim($newParts[$j]) : '';
                    // $session = ($j === 0 ? 'first' : 'second');

                    // edited by athira on 26-04-2026
                    // Hard Block: If an active leave exists in the module for this session, preserve the existing value
                    // if ($this->isLeaveAlreadyApplied($empPkey, $att_date, $session)) {
                    //     $finalParts[$j] = $existing;
                    //     continue;
                    // }
                    // ended by athira on 26-04-2026

                    // Check if existing "LOP" is a Policy LOP
                    $isPolicyLop = false;
                    if ($existing === 'LOP') {
                        $pFlags = isset($policysheet[$i]) ? explode(',', $policysheet[$i]) : ['false', 'false'];
                        $isPolicyLop = ($j === 0 ? ($pFlags[0] === 'true') : ((isset($pFlags[1]) ? $pFlags[1] : $pFlags[0]) === 'true'));
                    }

                    if ($adstatus !== 'all' && $existing !== '' && ($existing !== 'LOP' || $isPolicyLop)) {
                        // preserve leave (CL, SL etc.) OR Policy LOP if not 'all'
                        $finalParts[$j] = $existing;
                    } else {
                        // replace only Indirect LOP or blank, or EVERYTHING if 'all' is selected
                        $finalParts[$j] = $new;
                    }
                }

                $finalStatus = implode('/', $finalParts);
                // 5️⃣ Perform update
                $updateQuery = "
        UPDATE attendance_register
        SET `$field` = '$finalStatus'
        WHERE emp_fkey = '$empPkey'
          AND month_year = '$month_year'
          AND isdelete IN ('Y')
        LIMIT 1
    ";
                $this->EditPunches->query($updateQuery);

                $arr_success[] = [
                    'emp_pkey' => $empPkey,
                    'date' => date("Y-m-d", $current),
                    'field' => $field,
                    'old' => $existingValue,
                    'new' => $finalStatus,
                    'status' => 'Updated'
                ];

                $current = strtotime("+1 day", $current);
                $i++;
            }

            // ✅ Re-fetch updated row
            $row = $this->EditPunches->query("
    SELECT * FROM attendance_register
    WHERE emp_fkey = '$empPkey'
      AND month_year = '$month_year'
      AND isdelete IN ('Y')
    LIMIT 1
");

            if (!empty($row)) {
                $row = $row[0]['attendance_register'];



                $presentCount = 0;
                $leaveCount = 0;
                $lopCount = 0;
                $weekoffCount = 0;
                $holidayCount = 0;
                $naCount = 0;
                $na_ho_count = 0;
                $na_wo_count = 0;

                // Prepare dates for NA check in totals loop
                $dates = [];
                $startDt = new DateTime($startDate);
                $endDt = new DateTime($endDate);
                while ($startDt <= $endDt) {
                    $dates[] = $startDt->format('Y-m-d');
                    $startDt->modify('+1 day');
                }

                // Loop through all days and count totals
                for ($i = 1; $i <= count($dates); $i++) {
                    $fieldVal = isset($row["FIELD$i"]) ? strtoupper(trim($row["FIELD$i"])) : '';
                    if ($fieldVal == '')
                        continue;

                    $parts = explode('/', $fieldVal);
                    $weight = count($parts) > 1 ? 0.5 : 1;

                    foreach ($parts as $part) {
                        $part = trim($part);
                        if ($part == '')
                            continue;

                        if (in_array($part, ['P', 'P/A', 'A/P', 'P/P'])) {
                            $presentCount += $weight;
                        } elseif (in_array($part, ['WO', '/WO', 'W/O'])) {
                            $weekoffCount += $weight;
                        } elseif ($part == 'HO') {
                            $holidayCount += $weight;
                        } elseif ($part == 'NA') {
                            $naCount += $weight;
                        } elseif (strpos($part, 'LOP') !== false) {
                            $lopCount += $weight;
                        } elseif ($part != '' && $part != 'A') {
                            $leaveCount += $weight;
                        }
                    }

                    // Recalculate NA period HO/WO
                    if (isset($dates[$i - 1])) {
                        $attDate = $dates[$i - 1];
                        $isNAPeriod = false;
                        if ($joiningDate && $attDate < $joiningDate)
                            $isNAPeriod = true;
                        if ($lastWorkingDate && $attDate > $lastWorkingDate)
                            $isNAPeriod = true;

                        // if ($isNAPeriod) {
                        //     if ($fieldVal === 'HO' || $fieldVal === 'HO/HO') {
                        //         $na_ho_count += 1;
                        //     } elseif ($fieldVal === 'WO' || $fieldVal === 'WO/WO') {
                        //         $na_wo_count += 1;
                        //     } elseif (in_array($fieldVal, ['NA/HO', 'HO/NA'])) {
                        //         $na_ho_count += 0.5;
                        //     } elseif (in_array($fieldVal, ['NA/WO', 'WO/NA'])) {
                        //         $na_wo_count += 0.5;
                        //     }
                        // }

                        // edited by athira on 22-05-2025
                        if ($isNAPeriod) {
                            if ($fieldVal === 'HO' || $fieldVal === 'HO/HO') {
                                $na_ho_count += 1;
                            } elseif ($fieldVal === 'WO' || $fieldVal === 'WO/WO') {
                                $na_wo_count += 1;
                            } elseif (in_array($fieldVal, ['NA/HO', 'HO/NA'])) {
                                $na_ho_count += 0.5;
                            } elseif (in_array($fieldVal, ['NA/WO', 'WO/NA'])) {
                                $na_wo_count += 0.5;
                            } elseif (in_array($fieldVal, ['HO/WO', 'WO/HO'])) {
                                $na_ho_count += 0.5;
                                $na_wo_count += 0.5;
                            }
                        }
                        // ended by athira on 22-05-2026
                    }
                }

                // Working days = present + leave + lop
                // $workingDays = $presentCount + $leaveCount + $lopCount;
                $calander_days = $row['calander_days'];

                $workingDays = $calander_days - ($weekoffCount + $holidayCount);

                // Base LOP (only actual LOP entries)
                $loponly = $lopCount;

                // edited by athira on 13-04-2026
                // ✅ Always calculate BOTH totals for persistent data stability
                $lop_total_val = $loponly + $naCount + $na_ho_count + $na_wo_count;
                $wd_lop_total_val = $loponly + $naCount;
                // ended by athira on 13-04-2026
                // Update summary totals
                $updateTotals = "
        UPDATE attendance_register
        SET 
            presant_total = '" . $presentCount . "',
            lop_total = '" . $lop_total_val . "',
            wd_lop_total = '" . $wd_lop_total_val . "',
            lop_only = '" . $loponly . "',
            leave_total = '" . $leaveCount . "',
            weekoff_total = '" . $weekoffCount . "',
            holiday_total = '" . $holidayCount . "',
            working_days = '" . $workingDays . "',
            na_ho_count = '" . $na_ho_count . "',
            na_wo_count = '" . $na_wo_count . "'
        WHERE emp_fkey = '" . $empPkey . "'
          AND month_year = '" . $month_year . "'
          AND isdelete IN ('Y')
    ";
                $this->EditPunches->query($updateTotals);
                // ended by athira on 13-04-2026
            }
        }

        // ✅ 6. Return result JSON
        echo json_encode(['success' => true, 'updates' => $arr_success]);
    }




    function checkLeaveExists($att_date, $emp_pkey, $statusType)
    {

        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');

        $data = $this->EditPunches->query("SELECT * FROM leaveentries WHERE LEAVESTATUS = 'Approved' and EMP_fkey = '" . $emp_pkey . "' and " . " FROMDATE = '" . $att_date . "' ");

        if (!empty($data)) {


            for ($i = 0; $i < count($data); $i++) {
                # code...
                $fromdate = $data[$i]['leaveentries']['FROMDATE'];
                $fromhalf = $data[$i]['leaveentries']['FROMHALF'];
                $todate = $data[$i]['leaveentries']['TODATE'];
                $tohalf = $data[$i]['leaveentries']['TOHALF'];
                $leavedays = $data[$i]['leaveentries']['leave_days'];
                $leaveentryId = $data[$i]['leaveentries']['LEAVEENTRYID'];
                $leavestatuses = "Cancelled";
                $outs = $this->LeaveRequests->query("CALL leave_transaction_prc('$leaveentryId','$emp_pkey','$fromdate','$fromhalf','$todate','$tohalf','$leavedays','$leavestatuses',@Perror_message);");
                // return true;

                $this->LeaveRequests->query("DELETE FROM emp_leave_transactions WHERE LEAVEENTRYID = '$leaveentryId' ");
                $this->LeaveRequests->query("DELETE FROM leaveentries WHERE LEAVEENTRYID = '$leaveentryId' ");
            }
        } else {
            // return true;
        }

        if (!empty($data)) {
            if ($statusType == 'full') {
                return true;
            } else {

                if ($data[0]['leaveentries']['leave_days'] == 1) {

                    if ($statusType == 'first') {
                        $statusType = 'second';
                    } else {
                        $statusType = 'first';
                    }

                    $loccurance = $this->EditPunches->query("SELECT occurance FROM salary_head_items WHERE salary_head_item_pkey = '" . $data[0]['leaveentries']['salary_head_item_fkey'] . "' ");

                    $app = $this->AddLeave($loccurance[0]['salary_head_items']['occurance'], $data[0]['leaveentries']['FROMDATE'], $data[0]['leaveentries']['EMP_fkey'], $statusType);
                } else {

                    $loccurance = $this->EditPunches->query("SELECT occurance FROM salary_head_items WHERE salary_head_item_pkey = '" . $data[0]['leaveentries']['salary_head_item_fkey'] . "' ");

                    if ($data[0]['leaveentries']['FROMHALF'] == 1) {
                        // fisrt half
                        if ($statusType == 'second') {
                            $statusType = 'first';
                            $app = $this->AddLeave($loccurance[0]['salary_head_items']['occurance'], $data[0]['leaveentries']['FROMDATE'], $data[0]['leaveentries']['EMP_fkey'], $statusType);
                        }
                    } else if ($data[0]['leaveentries']['FROMHALF'] == 2) {
                        // second half
                        if ($statusType == 'first') {
                            $statusType = 'second';
                            $app = $this->AddLeave($loccurance[0]['salary_head_items']['occurance'], $data[0]['leaveentries']['FROMDATE'], $data[0]['leaveentries']['EMP_fkey'], $statusType);
                        }
                    } else {
                        // full day
                    }
                }
                return true;
            }
        } else {
            return true;
        }
    }

    public function AddLeave($head = '', $day = '', $emp_fkey = 0, $session = "")
    {
        // debug($session);
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $leaves = array();
        //added by megha auto delete applied leaves of employees on 13/02/2020
        if ($session == 'full') {
            $sess = 3;
            $fromhalf = 1;
            $tohalf = 2;
        } else if ($session == 'first') {
            $sess = 1;
            $fromhalf = 1;
            $tohalf = 1;
        } else {
            $sess = 2;
            $fromhalf = 2;
            $tohalf = 2;
        }
        // debug($session.$sess.$tohalf);
        $leave_count = $this->LeaveRequests->query("select count(*) cnt,emp_leave_transactions.LEAVEENTRYID "
            . "from emp_leave_transactions left join leaveentries on(leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID) "
            . "where leave_date=date_format('$day','%Y-%m-%d') and EMP_fkey= '$emp_fkey'  and "
            . "emp_leave_transactions.Leavestatus in('Authorized','Approved') and emp_leave_transactions.leave_session in ('$sess') ");
        $leave_count = isset($leave_count['0']['0']['cnt']) ? $leave_count['0']['0']['cnt'] : 0;

        // if ($leave_count > 0) {
        //     $id = $leave_count['0']['emp_leave_transactions']['LEAVEENTRYID'];
        //     $this->LeaveRequests->query("DELETE FROM emp_leave_transactions WHERE LEAVEENTRYID = '$id' ");
        //     $this->LeaveRequests->query("DELETE FROM leaveentries WHERE LEAVEENTRYID = '$id' ");
        // }
        //end auto delete

        // $arr_leaves = $this->LeaveRequests->query("SELECT salary_head_item_pkey,item_part FROM `salary_head_items` WHERE `item_type` = 'Leave' AND `status` = '1' and occurance = '$head' ");
        // edited by athira on 06-04-2026
        // $arr_leaves = $this->LeaveRequests->query("
        //     SELECT salary_head_item_pkey, item_part 
        //     FROM salary_head_items 
        //     WHERE item_type = 'Leave' 
        //       AND status = '1' 
        //       AND occurance = '$head'
        //       AND salary_head_item_pkey IN (
        //           SELECT salary_head_item_fkey 
        //           FROM leavepolicy 
        //           WHERE LEAVEPOLICY_GROUP_ID = (
        //               SELECT LEAVEPOLICY_GROUP_ID 
        //               FROM emp_proff 
        //               WHERE emp_fkey = '$emp_fkey' 
        //               LIMIT 1
        //           )
        //       )
        //     LIMIT 1
        // ");





        // edited by athira on 06-04-2026
        // Existing query in AddLeave (approx line 3535)
        $arr_leaves = $this->LeaveRequests->query("
    SELECT salary_head_item_pkey, item_part 
    FROM salary_head_items 
    WHERE item_type = 'Leave' 
      AND status = '1' 
      AND occurance = '$head'
      AND (salary_head_item_pkey IN (
          SELECT salary_head_item_fkey 
          FROM leavepolicy 
          WHERE LEAVEPOLICY_GROUP_ID = (
              SELECT LEAVEPOLICY_GROUP_ID 
              FROM emp_proff 
              WHERE emp_fkey = '$emp_fkey' 
              LIMIT 1
          )
      ) OR item_part = 'Indirect')
    ORDER BY (CASE WHEN item_part = 'Indirect' THEN 1 ELSE 0 END) ASC -- Added prioritization: Policy (0) comes before Indirect (1)
    LIMIT 1
");

        // ended by athira on 06-04-2026
        $leaveentryId = 0;
        $leaves['salary_head_item_fkey'] = isset($arr_leaves['0']['salary_head_items']['salary_head_item_pkey']) ? $arr_leaves['0']['salary_head_items']['salary_head_item_pkey'] : 0;
        $item_part = isset($arr_leaves['0']['salary_head_items']['item_part']) ? $arr_leaves['0']['salary_head_items']['item_part'] : 0;


        $leaves['applied_date'] = date('Y-m-d');
        $leaves['AuthoriseRemarks'] = "Leave Authorized For Verifying Attendance";
        $leaves['ApproveRemarks'] = "Leave Approved For Verifying Attendance";
        $leaves['LEAVESTATUS'] = "Approved";
        $leaves['EMP_fkey'] = $emp_fkey;
        $leaves['FROMDATE'] = $day;
        $leaves['FROMHALF'] = $fromhalf;
        $leaves['TODATE'] = $day;
        $leaves['TOHALF'] = $tohalf;
        $leaves['ISAutherized'] = 1;
        $leaves['ISAutherizedby'] = "0";
        $leaves['Autherized_date'] = date("Y-m-d");
        $leaves['ISAPPROVED'] = 1;
        $leaves['APPROVEDBY'] = "0";
        $leaves['APPROVED_date'] = date("Y-m-d");
        $leaves['Reason'] = "Leave applied through status change";
        $leaves['REMARKS'] = "Leave applied through status change";
        $leavedays = $session == 'full' ? 1 : 0.5;
        $leaves['leave_days'] = $leavedays;
        $fromdate = $day;
        $fromhalf = $fromhalf;
        $todate = $day;
        $tohalf = $tohalf;
        $leavedays = $session == 'full' ? 1 : 0.5;
        $leavestatus = "Applied";
        $this->LeaveRequests->saveAll($leaves);
        $leaveentryId = $this->LeaveRequests->getLastInsertID();
        if ($item_part != 'Indirect') {
            $out = $this->LeaveRequests->query("CALL leave_transaction_prc('$leaveentryId','$emp_fkey','$fromdate','$fromhalf','$todate','$tohalf',$leavedays,'$leavestatus',@Perror_message);");
        }



        $leavestatuses = "Approved";
        $outs = $this->LeaveRequests->query("CALL leave_transaction_prc('$leaveentryId','$emp_fkey','$fromdate','$fromhalf','$todate','$tohalf',$leavedays,'$leavestatuses',@Perror_message);");

        return true;
    }


    public function checkprocessingstatus()
    {
        $this->autoRender = false;
        $this->RegisterHistory->useDbConfig = $this->Session->read('ds');

        $branch = $_POST['branch'];
        $month = isset($_POST['month']) ? date('Y-m', strtotime($_POST['month'])) : '';
        $user = $this->Session->read("login_user_id");

        // Check if another process is still running (status=0 within last 20 min)
        // $exists = $this->RegisterHistory->query("
        //     SELECT register_history_pkey, start_time 
        //     FROM register_history 
        //     WHERE month='$month' AND branch='$branch' AND status='1'
        //       AND start_time > DATE_SUB(NOW(), INTERVAL 20 MINUTE)
        //     ORDER BY register_history_pkey DESC
        //     LIMIT 1
        // ");

        // if (!empty($exists)) {
        //     $start_time = $exists[0]['register_history']['start_time'];
        //     $pkey = $exists[0]['register_history']['register_history_pkey'];
        //     $result = [
        //         'success' => 0,
        //         'message' => "Timesheet processing already running since $start_time by $user.",
        //         'pkey' => $pkey
        //     ];
        // } else {
        // Insert a fresh record using save() so getLastInsertID works
        $this->RegisterHistory->create();
        $this->RegisterHistory->save([
            'branch' => $branch,
            'month' => $month,
            'created_by' => $user,
            'created_date' => date('Y-m-d H:i:s'),
            'process' => 'Timesheet Process',
            'status' => 0,
            'start_time' => date('Y-m-d H:i:s')
        ]);

        $pkey = $this->RegisterHistory->id; // <-- this will correctly have the new primary key
        $result = [
            'success' => 1,
            'message' => "Timesheet processing started...",
            'pkey' => $pkey
        ];
        // }

        echo json_encode($result);
    }


    public function markprocesscomplete()
    {
        $this->autoRender = false;
        $this->RegisterHistory->useDbConfig = $this->Session->read('ds');

        $pkey = $_POST['pkey']; // now passing the pkey from frontend
        $duration = $_POST['duration'];



        $this->RegisterHistory->query("
        UPDATE register_history 
        SET end_time = NOW(), duration = $duration, status = '1'
        WHERE register_history_pkey = '$pkey'
        LIMIT 1
    ");

        echo json_encode(['success' => 1]);
    }


    // edited by athira on 26-02-2026
    private function isLeaveAlreadyApplied($emp_pkey, $att_date, $session)
    {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $sess = ($session == 'full') ? 3 : (($session == 'first') ? 1 : 2);

        $conditions = " (leave_session = '$sess' OR leave_session = 3) ";
        if ($sess == 3) {
            $conditions = " (leave_session IN (1, 2, 3)) ";
        }

        $check = $this->LeaveRequests->query("
            SELECT count(*) as cnt 
            FROM emp_leave_transactions 
            INNER JOIN leaveentries ON leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID
            WHERE leaveentries.emp_fkey = '$emp_pkey' 
              AND emp_leave_transactions.leave_date = '$att_date' 
              AND emp_leave_transactions.Leavestatus IN ('Applied','Authorized', 'Approved') 
              AND $conditions
        ");

        return (isset($check[0][0]['cnt']) && $check[0][0]['cnt'] > 0);
    }
    // ended by athira on 26-02-2026


}
