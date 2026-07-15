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

class ShiftPlannerController extends AppController
{

    //edited by athira on 29-04-2025
    public $uses = array('CentralControl','DayTimeProcedures', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails',  'EmployeeConfig', 'EditPunches');
    //end

    public function index()
    {
      $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
      $employees=$this->EmployeeDetails->query("SELECT emp_pkey,
                CONCAT(first_name, ' ',
                    IFNULL(middile_name, ''),
                    ' ',
                    last_name) AS first_name
            FROM emp_details
            WHERE status = 1");
           
      $this->set(compact('employees'));
    }

//     public function listemployees(){
//     $this->autoRender = false;
//     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//     $emp_pkey = $_REQUEST['emp_pkey'];
//     $month = $_REQUEST['month'];

//     $month_year = $month . "-01";

//     $start_range = $this->EmployeeDetails->query("SELECT att_start_end_fn('$month_year', 1) as monthly_att_fromdate");
//     $end_range = $this->EmployeeDetails->query("SELECT att_start_end_fn('$month_year', 2) as monthly_att_todate");

//     $att_start = $start_range[0][0]['monthly_att_fromdate'];
//     $att_end   = $end_range[0][0]['monthly_att_todate'];

//     // Generate dates array between start and end
//     $dates = [];
//     $current = strtotime($att_start);
//     $end = strtotime($att_end);

//     while ($current <= $end) {
//         $dayOfWeek = date('D', $current);
//         $dates[] = date('Y-m-d', $current) . " ($dayOfWeek)";
//         $current = strtotime('+1 day', $current);
//     }

//      // Fetch primary shift (always selected)
//     $primary_shift = $this->EmployeeDetails->query("
//         SELECT ec.policy_id, wdtp.day_time_desc,wdtp.on_dutty1,wdtp.off_dutty1,wdtp.working_time1,wdtp.minuts_calc_perday
//         FROM emp_config ec
//         JOIN working_day_time_procedures wdtp ON ec.policy_id = wdtp.day_time_seq
//         WHERE ec.status = 1 AND ec.type = 'SHIFT' AND emp_fkey='$emp_pkey'
//     ");

//     // Fetch secondary shifts
//     $secondary_shifts = $this->EmployeeDetails->query("
//         SELECT ec.policy_id, wdtp.day_time_desc,wdtp.on_dutty1,wdtp.off_dutty1,wdtp.working_time1,wdtp.minuts_calc_perday
//         FROM emp_config ec
//         JOIN working_day_time_procedures wdtp ON ec.policy_id = wdtp.day_time_seq
//         WHERE ec.status = 2 AND ec.type = 'MSHIFT' AND emp_fkey='$emp_pkey'
//     ");

//     // Return JSON response
//     echo json_encode(['success' => true, 'dates' => $dates,'primary_shift'=>$primary_shift,'secondary_shifts' => $secondary_shifts]);
// }

public function listemployees(){
    $this->autoRender = false;
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    $emp_pkey = $_REQUEST['emp_pkey'];
    $month = $_REQUEST['month'];

    $month_year = $month . "-01";

    // Get attendance date range
    $start_range = $this->EmployeeDetails->query("SELECT att_start_end_fn('$month_year', 1) as monthly_att_fromdate");
    $end_range   = $this->EmployeeDetails->query("SELECT att_start_end_fn('$month_year', 2) as monthly_att_todate");
    $att_start = $start_range[0][0]['monthly_att_fromdate'];
    $att_end   = $end_range[0][0]['monthly_att_todate'];

    // Generate dates array
    $dates = [];
    $current = strtotime($att_start);
    $end = strtotime($att_end);
    while ($current <= $end) {
        $dates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }

    // Get attendance records
    $attendance = $this->EmployeeDetails->query("
        SELECT att_date, present, duration 
        FROM emp_detail_timeattandance 
        WHERE yearmonth='$month_year' AND emp_pkey='$emp_pkey'
    ");

    // $attendanceMap = [];
    // foreach ($attendance as $row) {
    //     $att_date = $row['emp_detail_timeattandance']['att_date'];
    //     $attendanceMap[$att_date] = [
    //         'present' => $row['emp_detail_timeattandance']['present'],
    //         'duration' => $row['emp_detail_timeattandance']['duration']
    //     ];
    // }

    // Get primary and secondary shifts
    $primary_shift = $this->EmployeeDetails->query("
        SELECT ec.policy_id, wdtp.day_time_desc, wdtp.on_dutty1, wdtp.off_dutty1,
               wdtp.working_time1, wdtp.minuts_calc_perday
        FROM emp_config ec
        JOIN working_day_time_procedures wdtp ON ec.policy_id = wdtp.day_time_seq
        WHERE ec.status = 1 AND wdtp.active = 1 AND ec.type = 'SHIFT' AND emp_fkey='$emp_pkey'
    ");

    $secondary_shifts = $this->EmployeeDetails->query("
        SELECT ec.policy_id, wdtp.day_time_desc, wdtp.on_dutty1, wdtp.off_dutty1,
               wdtp.working_time1, wdtp.minuts_calc_perday
        FROM emp_config ec
        JOIN working_day_time_procedures wdtp ON ec.policy_id = wdtp.day_time_seq
        WHERE ec.status = 2  AND wdtp.active = 1 AND ec.type = 'MSHIFT' AND emp_fkey='$emp_pkey'
    ");

    // Get saved shifts for this employee/month
    $savedShifts = $this->EmployeeDetails->query("
        SELECT shift_date, shift_id
        FROM emp_shift_planner
        WHERE emp_fkey='$emp_pkey' AND month_year='$month' AND status=1
    ");
    $savedShiftMap = [];
    foreach ($savedShifts as $row) {
        $savedShiftMap[$row['emp_shift_planner']['shift_date']] = $row['emp_shift_planner']['shift_id'];
    }

    // Merge dates + attendance + saved shift
    $attendanceData = [];
    foreach ($dates as $d) {
        $dayOfWeek = date('D', strtotime($d));
        $attendanceData[] = [
            'date' => $d,
            'day' => $dayOfWeek,
            'saved_shift_id' => isset($savedShiftMap[$d]) ? $savedShiftMap[$d] : null
        ];
    }

    echo json_encode([
        'success' => true,
        'attendance' => $attendanceData,
        'primary_shift' => $primary_shift,
        'secondary_shifts' => $secondary_shifts
    ]);
}




public function saveRoster()
{
    $this->autoRender = false;
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    if ($this->request->is('post')) {
        $emp_pkey   = $this->request->data('emp_pkey');
        $month      = $this->request->data('month');       // YYYY-MM
        $rosterData = $this->request->data('shiftData');   // key = date, value = array('shift_id', ...)

        if (empty($emp_pkey) || empty($month) || empty($rosterData)) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        // 🔍 Step 1: Check if attendance is verified for this month
        $check = $this->EmployeeDetails->query("
            SELECT isdelete 
            FROM attendance_register 
            WHERE emp_fkey = '$emp_pkey' 
              AND month_year = '$month'
            LIMIT 1
        ");

        if (!empty($check) && $check[0]['attendance_register']['isdelete'] === 'N') {
            // Attendance verified — block saving
            echo json_encode([
                'success' => false,
                'message' => '⚠️ Attendance already verified for this month.Please re-iterate attendance in Edit Attendance to reflect shift changes.'
            ]);
            return;
        }

        // 🧾 Step 2: Proceed to save roster (if not verified)
        foreach ($rosterData as $date => $row) {
            $shiftId = $row['shift_id'];

            // Mark other shifts inactive
            $this->EmployeeDetails->query("
                UPDATE emp_shift_planner
                SET status = 0, modification_date = NOW()
                WHERE emp_fkey = '$emp_pkey' AND shift_date = '$date'
            ");

            // Check if same shift already exists
            $existing = $this->EmployeeDetails->query("
                SELECT planner_id 
                FROM emp_shift_planner 
                WHERE emp_fkey = '$emp_pkey' AND shift_date = '$date' AND shift_id = '$shiftId'
                LIMIT 1
            ");

            if (!empty($existing)) {
                $plannerId = $existing[0]['emp_shift_planner']['planner_id'];
                $this->EmployeeDetails->query("
                    UPDATE emp_shift_planner
                    SET status = 1, modification_date = NOW()
                    WHERE planner_id = '$plannerId'
                ");
            } else {
                $this->EmployeeDetails->query("
                    INSERT INTO emp_shift_planner 
                        (emp_fkey, shift_date, month_year, shift_id, status, creation_date)
                    VALUES 
                        ('$emp_pkey', '$date', '$month', '$shiftId', 1, NOW())
                ");
            }
        }

        echo json_encode(['success' => true, 'message' => 'Roster saved successfully']);
    }
}






}