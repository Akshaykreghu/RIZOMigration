<?php

class DashboardNewController extends AppController
{

    public $layout = "default";
    public $helpers = array('GoogleMap');
    public $name = "DashboardNew";
    // edited by athira on 24-07-2025
    public $uses = array("EmployeeDetails","Promotion","EmployeeExpenses","Wish", "Earlyin", "Latein", "Earlyout", "Lateout", "EmployeeProfessionalDetails", "DeviceAttendance", "IssueReport", "LeaveRequests", "EmployeeDetails", "Device", "Useraccess", "AttendancePunch", "MobileUserauditor", "MobileUserTracking", "SettingsRunner", "GeneralSettings", "ReportAudit", "Wish", "EmailContent", "CentralControl","Menu"); //Edited by Akshay on 13-6-2024
     //end
    public $components = array('DatatablesManagement');



    protected function getScopeFilter()
    {
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $emp_fkey = $this->Session->read('emp_fkey');
        $scope = $this->Session->read('scope');

        $emp_scope_in = '';
        $scope_cond = '';
        $scope_cond_ed = '';

        if ($user_group == 2 && $company_code != 'LNTT' && $scope != 'admin') {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            // Simplified by Antigravity on 28-04-2026: Only direct subordinates (attr1 = logged-in emp) and active status
            $hier = $this->EmployeeDetails->query("
                SELECT DISTINCT ep.emp_fkey FROM emp_proff ep
                JOIN emp_details ed ON ed.emp_pkey = ep.emp_fkey
                WHERE ep.attr1 = '$emp_fkey' AND ed.status = 1
            ");
            $pkeys = array('0');
            foreach ($hier as $row) {
                // Check possible result structures
                $v = isset($row['ep']['emp_fkey']) ? $row['ep']['emp_fkey'] :
                    (isset($row[0]['emp_fkey']) ? $row[0]['emp_fkey'] :
                        (isset($row['emp_proff']['emp_fkey']) ? $row['emp_proff']['emp_fkey'] : ''));
                if ($v)
                    $pkeys[] = (int) $v;
            }
            $emp_scope_in = implode(',', array_unique($pkeys));
            $scope_cond = "AND emp_pkey IN ($emp_scope_in)";
            $scope_cond_ed = "AND emp_details.emp_pkey IN ($emp_scope_in)";
        }

        return array($emp_scope_in, $scope_cond, $scope_cond_ed);
    }

    public function index()
    {
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
         // edited by athira on 24-07-2025
        $this->Wish->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //end
        $user_group = $this->Session->read('user_group'); // edited by anukrishnan 27-01-2025
        $company_code = $this->Session->read('company_code'); // Edited by Akshay on 25-1-2025
        // var_dump($company_code);
        if ($this->Session->read('ds') == null) {
            $this->redirect(array('controller' => 'Site', 'action' => 'login'));
        }

        
        // Scope filter
        list($emp_scope_in, $scope_cond, $scope_cond_ed) = $this->getScopeFilter();
        //edited by athira on 20-08-2025
        $plan=$this->DeviceAttendance->query("select plan from comp_contact_info");
        $plan=$plan[0]['comp_contact_info']['plan'];
        $this->set('plan',$plan);
        //end


         if ($emp_scope_in) {
            $count_res = $this->EmployeeDetails->query("SELECT COUNT(*) AS cnt FROM emp_details WHERE status = 1 AND emp_pkey IN ($emp_scope_in)");
            $count = isset($count_res[0][0]['cnt']) ? (int) $count_res[0][0]['cnt'] : 0;
        } else {
            $count = $this->EmployeeDetails->find("count", array("conditions" => array("emp_id !=" => null, 'status' => 1)));
        }
        $this->set("count", $count);

          //edited by athira 25-12-2025
        $todaydate = date("Y-m-d");
        $results = $this->DeviceAttendance->query("SELECT * FROM present_today_all WHERE DATE(LOGDATE) = '{$todaydate}'"); //edited by anukrishnan_10-02-2025
        // var_dump($results);
        $timezone = new DateTimeZone("Asia/Kolkata");
        $date = new DateTime();
        $date->setTimezone($timezone);
        $dates = $date->format('d-m-Y H:i a');

        $empsin = array('0');
        foreach ($results as $val) {
            $empsin[] = $val['present_today_all']['emp_pkey'];
        }
        $emps = implode(',', $empsin);
//         $absentCount = $this->DeviceAttendance->query("
//     SELECT COUNT(*) AS absent_count
//     FROM emp_details
//     LEFT JOIN emp_proff 
//         ON emp_proff.emp_fkey = emp_details.emp_pkey
//     LEFT JOIN working_day_time_procedures wdtp
//         ON wdtp.day_time_seq = emp_proff.day_time_seq
//     WHERE emp_details.emp_pkey NOT IN ($emps)
//       AND emp_details.status = 1
//       AND (wdtp.working_time1 IS NULL OR wdtp.working_time1 <> 0)
//       $scope_cond_ed
// ");
 $absentCount = $this->DeviceAttendance->query("
    SELECT COUNT(*) AS absent_count
    FROM emp_details
    WHERE emp_details.emp_pkey NOT IN ($emps)
      AND emp_details.status = 1
      $scope_cond_ed
");

        $absentcount = $absentCount[0][0]['absent_count'];
        $this->set('absentcount', $absentcount);

        //end

        if ($user_group == 2 && $company_code != 'DEMO') { // Edited by Akshay on 25-1-2025
               
            //$this->empdashboard();
            $this->Useraccess->useDbConfig = $this->Session->read('ds');
            $emp_fkey = $this->Session->read('emp_fkey');
            $useracess = $this->Useraccess->query("select * from user_access as Useraccess where user_fkey = '$emp_fkey' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            if ($access == 'Y') {
                //Hierarchy dashboard
                $this->hierarchydashboard();
            } else {
                //Employee dashboard
                $this->empdashboard();
            }
        } else {
            //Admin dashboard
            $arr_menus = $this->getMenus();
            $this->set('arr_menu', $arr_menus);

            // Edited by Akshay on 3-2-2025
            $user_group = $this->Session->read("user_group");
            $company_code = $this->Session->read('company_code');
            if ($user_group == '2' || $company_code == 'GLET' || $company_code == 'ABSG' || $company_code == 'DEMO') {
                $arr_hrm_menus = $this->getHrmMenusForAbs();
                $this->set('arr_hrm_menus', $arr_hrm_menus);
            }





            // End

            $emp_pkey = $this->Session->read('emp_fkey');

            //            $this->Latein->useDbConfig = $this->Session->read('ds');
            //            $check_setups = $this->Latein->query("SELECT * FROM wizard_config ");
            //            if ($check_setups['0']['wizard_config']['state'] == 0) {
            //                $this->redirect(array('controller' => 'DbConfig', 'action' => 'welcome'));
            //            }

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $count = $this->EmployeeDetails->find("count", array("conditions" => array("emp_id !=" => null, 'status' => 1)));
            // edited by anukrishnan 27-01-2025 open
            if ($user_group == '2') {

                $arr_is_ho = $this->EmployeeDetails->query(
                    "SELECT get_branch_code_abs_fn(:emp_pkey) as branch",
                    ['emp_pkey' => $emp_pkey]
                );
                $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                $conditions = [];

                if ($is_ho != 1) {
                    $conditions[] = "EmployeeDetails.branch_code = '$is_ho' AND EmployeeDetails.status = 1";
                } else {
                    $conditions = array("EmployeeDetails.status" => 1);
                }

                $count = $this->EmployeeDetails->find('count', [
                    'conditions' => $conditions
                ]);
            }
            // edited by anukrishnan 27-01-2025 close
            $this->set("count", $count);
       
            $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
            $arr_present = $this->DeviceAttendance->query("select count(*) as presentcount from present_today");
            $present = isset($arr_present[0][0]['presentcount']) ? $arr_present[0][0]['presentcount'] : 0;
            // edited by anukrishnan 27-01-2025 open
            if ($user_group == '2') {
                $conditions = [];
                if ($is_ho != 1) {
                    $conditions[] = "e.branch_code = '$is_ho'";
                }
                $where_clause = '';
                if (!empty($conditions)) {
                    $where_clause = 'WHERE ' . implode(' AND ', $conditions);
                }

                $arr_present = $this->DeviceAttendance->query("
                    SELECT COUNT(*) AS presentcount
                    FROM present_today p
                    JOIN emp_details e ON p.emp_pkey = e.emp_pkey
                    $where_clause
                ");
                $present = isset($arr_present[0][0]['presentcount']) ? $arr_present[0][0]['presentcount'] : 0;
            }
            // edited by anukrishnan 27-01-2025 close
            $this->set("present", $present);
            //added by megha on 25/11/2019 presenttodayall list
            $today = date('Y-m-d');
            // $query = "select count(*) as presentcount from present_today_all where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today'";
            // var_dump($query);

            $arr_presentall = $this->DeviceAttendance->query("select count(*) as presentcount from present_today_all where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today'");
            $presentall = isset($arr_presentall[0][0]['presentcount']) ? $arr_presentall[0][0]['presentcount'] : 0;
            // edited by anukrishnan 27-01-2025 open
            if ($user_group == '2') {
                $conditions = [];
                if ($is_ho != 1) {
                    $conditions[] = "DeviceAttendance.branch_code = '$is_ho'";
                }
                $conditions[] = "STR_TO_DATE(DeviceAttendance.LOGDATE, '%Y-%m-%d') = '$today'";
                $presentall = $this->DeviceAttendance->find('count', [
                    'fields' => ['emp_id', 'DATE(DeviceAttendance.LOGDATE) AS logdate', 'COUNT(*) AS count'],
                    'conditions' => implode(' AND ', $conditions), // Combine conditions as a string
                    'group' => ['emp_id', 'DATE(DeviceAttendance.LOGDATE)'],
                ]);
                if ($presentall === false) {
                    $presentall = 0;
                }
            }
            // edited by anukrishnan 27-01-2025 close
            $this->set("presentall", $presentall);

            

            $arr_empleaverequests = $this->listemployeeleaverequestscounts(0);
            $this->set("arr_empleaverequests", $arr_empleaverequests);
           $query=$this->EmployeeDetails->query("SELECT SUM(monthly_ctc) AS Salary  
FROM payroll_master  
WHERE  action = 'Approved'");



            /**
             * Fetch miss punch count of current months
             * On 04 March 2017
             */
            //            $arr_empmisspunches = $this->listemployeemisspunches();
            //            $this->set("arr_empmisspunches", $arr_empmisspunches); 
            //	//Ends
            //            $today = date('Y-m-d');
            //            $leaves = $this->Latein->query("select count(*) lea from emp_leave_transactions where Leavestatus in ('Approved') and leave_date = '$today' ");
            //            $leaves = isset($leaves['0']['0']['lea'])?$leaves['0']['0']['lea']:0;
            //            $this->set("leaves",$leaves);
        }


        //edited by  gawtham

            
        {
            $user_group = $this->Session->read('user_group');
            $company_code = $this->Session->read('company_code');
        
            if ($this->Session->read('ds') == null) {
                $this->redirect(array('controller' => 'Site', 'action' => 'login'));
            }
        
             // edited by bindu 06-12-2025
            // Fetch salary data
            $salaryData = $this->EmployeeDetails->query("
       SELECT 
    DATE_FORMAT(STR_TO_DATE(CONCAT(month_year, '-01'), '%Y-%m-%d'), '%b-%Y') AS formatted_month,
    STR_TO_DATE(CONCAT(month_year, '-01'), '%Y-%m-%d') AS month_date,
    SUM(ABS(salary_amount)) AS salary
FROM emp_salary_slip
WHERE head_operator = 'ADDITION'
  AND end_date_effective IS NULL
  AND STR_TO_DATE(CONCAT(month_year, '-01'), '%Y-%m-%d')
        BETWEEN DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 5 MONTH)
            AND DATE_FORMAT(CURDATE(), '%Y-%m-01')
GROUP BY month_date, formatted_month
ORDER BY month_date;
            ");
        // debug($salaryData);
            // Format salary data for Morris.js
           $chartData = [];

foreach ($salaryData as $row) {
    $chartData[] = [
        'month' => $row[0]['formatted_month'],
        'value' => (float) $row[0]['salary']
    ];
}
// edited by bindu 06-12-2025
        
            // Fetch employee count
            $employeeCountData = $this->EmployeeDetails->query("
                SELECT COUNT(*) AS Employee_count 
                FROM emp_details 
                WHERE account_no IS NULL 
                AND status = 1
            ");
            $employeeCount = $employeeCountData[0][0]['Employee_count'];
        
            // Employee without ESI 
            $esiEmployeeCountData = $this->EmployeeDetails->query("
                SELECT COUNT(*) AS esi_employee_count
                FROM emp_details AS ed 
                JOIN emp_ctc_transaction AS ect 
                    ON ed.emp_pkey = ect.emp_fkey 
                WHERE (ed.esi IS NULL OR ed.esi = '') 
                  AND ect.emp_anual_ctc / 12 < 21001 
                  AND ect.end_date_effective IS NULL 
                  AND ed.status = 1
            ");
            $esiEmployeeCount = $esiEmployeeCountData[0][0]['esi_employee_count'];
            $totalEmployees = $this->EmployeeDetails->query("
                SELECT COUNT(*) as total 
                FROM emp_details 
            ");
            $totalEmployeeCount = $totalEmployees[0][0]['total'];

            // Get Present Today Count
            $today = date('Y-m-d');
            $presentToday = $this->EmployeeDetails->query("
                SELECT COUNT(DISTINCT emp_pkey) as present
                FROM present_today_all 
                WHERE DATE(LOGDATE) = '$today'
            ");
            $presentTodayCount = $presentToday[0][0]['present'];
            $activeEmployees = $this->EmployeeDetails->query("
                SELECT COUNT(*) as active 
                FROM emp_details ed
                WHERE ed.status = 1 
            ");
            $activeEmployeeCount = $activeEmployees[0][0]['active'];

            // Get Absent This Month
            $firstDayOfMonth = date('Y-m-01');
            $lastDayOfMonth = date('Y-m-t');
            $absentThisMonth = $this->EmployeeDetails->query("
                SELECT COUNT(DISTINCT emp_pkey) as absent
                FROM emp_detail_timeattandance
                WHERE att_date BETWEEN '$firstDayOfMonth' AND '$lastDayOfMonth'
                AND (present = 'A/A' OR present IS NULL)
                AND weekoff IS NULL 
                AND holiday IS NULL
            ");
            $absentCount = $absentThisMonth[0][0]['absent'];
// debug($absentCount);
            // Pass data to view
            $this->set('totalEmployeeCount', $totalEmployeeCount);
            $this->set('presentTodayCount', $presentTodayCount);
            $this->set('activeEmployeeCount', $activeEmployeeCount);
            // $this->set('absentCount', $absentCount);

            // Missing qualifications
            $missingQualificationsData = $this->EmployeeDetails->query("
                SELECT COUNT(*) AS not_entered_qualifications
                FROM qualifcations
                WHERE (course IS NULL OR course = '')
                  AND (university IS NULL OR university = '')
                  AND (duration IS NULL OR duration = '')
                  AND (mark IS NULL OR mark = '');

            ");
            $missingQualificationsCount = $missingQualificationsData[0][0]['not_entered_qualifications'];
        
            // No qualifications
            $noQualificationCountData = $this->EmployeeDetails->query("
             

 select count(*) AS no_qualification_count from emp_details where status = 1 and emp_pkey not in (select emp_fkey from qualifcations where status = 1);

            ");
            $noQualificationCount = $noQualificationCountData[0][0]['no_qualification_count'];
        
            // No nominee
            $noNomineeCountData = $this->EmployeeDetails->query("
                SELECT COUNT(*) AS no_nominee_count FROM (
                    SELECT ed.emp_pkey
                    FROM emp_details AS ed
                    LEFT JOIN emp_family AS ef ON ed.emp_pkey = ef.emp_fkey
                    WHERE ef.emp_fkey IS NULL
        
                    UNION
        
                    SELECT ed.emp_pkey
                    FROM emp_details AS ed
                    WHERE ed.emp_pkey IN (
                        SELECT emp_fkey
                        FROM emp_family
                        GROUP BY emp_fkey
                        HAVING SUM(CASE WHEN is_nominee = 'Y' THEN 1 ELSE 0 END) = 0
                    )
                ) AS subquery;
            ");
            $noNomineeCount = $noNomineeCountData[0][0]['no_nominee_count'];
            
            // Pending leave approvals
//     $pendingLeaveApprovalData = $this->EmployeeDetails->query("
//     SELECT COUNT(*) AS pending_leave_approval 
//     FROM emp_leave_transactions 
//     WHERE Leavestatus IN ('Applied','Authorized');
//     ");
            
//             // Extract the pending leave approval count
// $pendingLeaveApprovalCount = $pendingLeaveApprovalData[0][0]['pending_leave_approval'];
            // --- Gawtham Edit: Dynamic Leave Count (Full + Half Day) ---
        
            // Dynamic leave date range: 21st of previous month to 20th of current month
            $today = date('Y-m-d');
            $startDate = date('Y-m-21', strtotime('first day of last month'));
            $endDate = date('Y-m-20');
        
            // Full-day leave count
            $leaveFullDayData = $this->EmployeeDetails->query("
                SELECT COUNT(*) AS vfullcnt
                FROM emp_leave_transactions
                WHERE leave_session = 3
                  AND Remarks != 'No need Leave'
                  AND LEAVEENTRYID IN (
                    SELECT LEAVEENTRYID FROM leaveentries WHERE LEAVESTATUS = 'Approved'
                  )
                  AND Leavestatus = 'Approved'
                  AND leave_date BETWEEN '$startDate' AND '$endDate';
            ");
        
            // Half-day leave count
            $leaveHalfDayData = $this->EmployeeDetails->query("
                SELECT COUNT(*) AS vhalfcnt
                FROM emp_leave_transactions
                WHERE leave_session != 3
                  AND Remarks != 'No need Leave'
                  AND LEAVEENTRYID IN (
                    SELECT LEAVEENTRYID FROM leaveentries WHERE LEAVESTATUS = 'Approved'
                  )
                  AND Leavestatus = 'Approved'
                  AND leave_date BETWEEN '$startDate' AND '$endDate';
            ");
            // Late comers in the last 8 days
// Late comers in the last 8 days
$lateComersData = $this->EmployeeDetails->query("
    SELECT COUNT(DISTINCT emp_pkey) AS late_comers_count 
    FROM emp_detail_timeattandance 
    WHERE present = 'A/P' 
      AND weekoff IS NULL 
      AND att_in_time >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 8 DAY) 
      AND att_in_time IS NOT NULL 
    ORDER BY att_in_time DESC;
");
$lateComersCount = $lateComersData[0][0]['late_comers_count'];
// Count of employees on notice period
$noticePeriodData = $this->EmployeeDetails->query("
    SELECT COUNT(DISTINCT emp_pkey) AS notice_period_count 
    FROM emp_details AS ed 
    JOIN termination AS t ON ed.emp_pkey = t.emp_fkey 
    WHERE ed.status = 1 AND t.status = 1;
");
$noticePeriodCount = $noticePeriodData[0][0]['notice_period_count'];
// Count of retired employees
$retiredEmployeesData = $this->EmployeeDetails->query("
    SELECT COUNT(*) AS retired_employees 
    FROM emp_details AS ed 
    JOIN termination AS t ON ed.emp_pkey = t.emp_fkey 
    WHERE t.status = 1 
      AND ed.status = 2 
      AND t.Reason = 'Retirement';
");

        
        //  $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        //  $arr_absent = $this->DeviceAttendance->query("select count(*) as absentcount from emp_details where emp_pkey NOT in (select DISTINCT emp_pkey from present_today where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today') ");
        //  $absent = isset($arr_absent[0][0]['absentcount']) ? $arr_absent[0][0]['absentcount'] : 0;
        //  $this->set("absent", $absent);
         
         // Count of absent employees in present_today_all
        // $today = date('Y-m-d'); 
        //  $arr_absentall = $this->DeviceAttendance->query("select count(*) as absentcount from emp_details where emp_pkey NOT in (select DISTINCT emp_pkey from present_today_all where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today') ");
        // $absentall = isset($arr_absentall[0][0]['absentcount']) ? $arr_absentall[0][0]['absentcount'] : 0;
        // $this->set("absentall", $absentall);


$retiredEmployeesCount = $retiredEmployeesData[0][0]['retired_employees'];



        
            $vfullcnt = isset($leaveFullDayData[0][0]['vfullcnt']) ? (int)$leaveFullDayData[0][0]['vfullcnt'] : 0;
            $vhalfcnt = isset($leaveHalfDayData[0][0]['vhalfcnt']) ? (int)$leaveHalfDayData[0][0]['vhalfcnt'] : 0;
            $vleavecountF1 = $vfullcnt + ($vhalfcnt * 0.5);
        
            // Pass all data to view
           // Pass all data to view// edited by bindu 06-12-2025
            $this->set('salarychartData', $chartData);
            $this->set('employeeCount', $employeeCount);
            $this->set('esiEmployeeCount', $esiEmployeeCount);
            $this->set('missingQualificationsCount', $missingQualificationsCount);
            $this->set('noQualificationCount', $noQualificationCount);
            $this->set('noNomineeCount', $noNomineeCount);
            $this->set('vleavecountF1', $vleavecountF1);
            $this->set('leaveStartDate', $startDate);
            $this->set('leaveEndDate', $endDate);
            // $this->set('pendingLeaveApprovalCount', $pendingLeaveApprovalCount);
            $this->set('lateComersCount', $lateComersCount);
            $this->set('noticePeriodCount', $noticePeriodCount);
            $this->set('retiredEmployeesCount', $retiredEmployeesCount);




        
            // Admin dashboard logic
            $arr_menus = $this->getMenus();
            $this->set('arr_menu', $arr_menus);
        }
        
        // edited by Gawtham - 24-03-2025 (leave count logic added 09-04-2025)

        // donut chart  edited by gawtham - 24-03-2025 

        //lakshmi
        $coverageStats = $this->EmployeeDetails->query("
            SELECT 
                SUM(CASE WHEN (pf IS NULL OR pf = '') THEN 1 ELSE 0 END) as uan_not_provided,
                SUM(CASE WHEN (esi IS NULL OR esi = '') THEN 1 ELSE 0 END) as esi_not_covered,
                SUM(CASE WHEN (pan_no IS NULL OR pan_no = '') THEN 1 ELSE 0 END) as pan_not_provided
            FROM emp_details 
            WHERE status = 1
        ");
         
         $today = date('Y-m-d');
            $startDate = date('Y-m-01');         // First day of current month
            $endDate = date('Y-m-t'); 

        // Get pending leave approval count
        // Edited by bindu 29-11-2025
       
         //edited by athira on 01-12-2025
        $pendingLeaves = $this->EmployeeDetails->query("
            SELECT COUNT(*) AS total_pending
FROM leaveentries
WHERE LEAVESTATUS='Applied'


        ");
        //end

// Edited by bindu 29-11-2025 end
        // Format the data correctly before sending to view
        $coverageStatsArray = array(
            'uan_not_provided' => $coverageStats[0][0]['uan_not_provided'],
            'esi_not_covered' => $coverageStats[0][0]['esi_not_covered'],
            'pan_not_provided' => $coverageStats[0][0]['pan_not_provided'],
            'total_leaves' => $pendingLeaves[0][0]['total_pending']
        );

        $this->set('coverageStats', $coverageStatsArray);

        //end
        //lakshmi-upcoming events
     // edit by bindu 15-12-2025
 // edit by bindu 15-12-2025
        $upcomingEventsQuery = "
(
    SELECT
        'Birthday' AS type,
        CONCAT(ed.first_name, ' ', IFNULL(ed.last_name, '')) AS name,
        DATE_FORMAT(ed.date_of_birth, '%m-%d') AS md,
        DATE_FORMAT(
            CASE
                WHEN DATE_FORMAT(ed.date_of_birth, '%m-%d') >= DATE_FORMAT(CURDATE(), '%m-%d')
                    THEN CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(ed.date_of_birth, '%m-%d'))
                ELSE CONCAT(YEAR(CURDATE()) + 1, '-', DATE_FORMAT(ed.date_of_birth, '%m-%d'))
            END,
            '%Y-%m-%d'
        ) AS event_date,
        'Birthday' AS description,
        ed.emp_pkey,
        1 AS sort_order
    FROM emp_details ed
    WHERE ed.status = 1
      AND DATE_FORMAT(ed.date_of_birth, '%m-%d') BETWEEN 
            DATE_FORMAT(CURDATE(), '%m-%d')
        AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%m-%d')
      " . ($emp_scope_in ? "AND ed.emp_pkey IN ($emp_scope_in)" : "") . "
)

UNION ALL
(
    SELECT
        'Work Anniversary' AS type,
        CONCAT(ed.first_name, ' ', IFNULL(ed.last_name, '')) AS name,
        DATE_FORMAT(ep.joining_date, '%m-%d') AS md,

        DATE_FORMAT(
            CASE
                WHEN DATE_FORMAT(ep.joining_date, '%m-%d') >= DATE_FORMAT(CURDATE(), '%m-%d')
                    THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(ep.joining_date, '%m-%d')), '%Y-%m-%d')
                ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()) + 1, '-', DATE_FORMAT(ep.joining_date, '%m-%d')), '%Y-%m-%d')
            END,
            '%Y-%m-%d'
        ) AS event_date,

        CONCAT(
            TIMESTAMPDIFF(
                YEAR,
                ep.joining_date,
                CASE
                    WHEN DATE_FORMAT(ep.joining_date, '%m-%d') >= DATE_FORMAT(CURDATE(), '%m-%d')
                        THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(ep.joining_date, '%m-%d')), '%Y-%m-%d')
                    ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()) + 1, '-', DATE_FORMAT(ep.joining_date, '%m-%d')), '%Y-%m-%d')
                END
            ),
            ' Year Work Anniversary'
        ) AS description,

        ed.emp_pkey,
        2 AS sort_order

    FROM emp_proff ep
    JOIN emp_details ed ON ed.emp_pkey = ep.emp_fkey

    WHERE ed.status = 1
      AND ep.joining_date <= CURDATE()
      AND DATE_FORMAT(ep.joining_date, '%m-%d') BETWEEN 
            DATE_FORMAT(CURDATE(), '%m-%d')
        AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%m-%d')
      " . ($emp_scope_in ? "AND ed.emp_pkey IN ($emp_scope_in)" : "") . "
)
ORDER BY sort_order, event_date;
";
        // edit by bindu 15-12-2025 end  
// edit by bindu 15-12-2025 end  
     //edited by athira on 24-07-2025
//    $upcomingEventsQuery = "
// SELECT 
//     'Birthday' AS type,
//     CONCAT(ed.first_name, ' ', IFNULL(ed.last_name, '')) AS name,
//     DATE_FORMAT(
//         CASE 
//             WHEN DATE_FORMAT(date_of_birth, '%m-%d') >= DATE_FORMAT(CURRENT_DATE(), '%m-%d')
//             THEN CONCAT(YEAR(CURRENT_DATE()), '-', DATE_FORMAT(date_of_birth, '%m-%d'))
//             ELSE CONCAT(YEAR(CURRENT_DATE()) + 1, '-', DATE_FORMAT(date_of_birth, '%m-%d'))
//         END, 
//         '%Y-%m-%d'
//     ) AS event_date,
//     'Birthday' AS description,
//     ed.emp_pkey,
//     1 AS sort_type
// FROM emp_details ed
// WHERE ed.status = 1
//   AND DATE_FORMAT(date_of_birth, '%m-%d') 
//       BETWEEN DATE_FORMAT(CURRENT_DATE(), '%m-%d')
//       AND DATE_FORMAT(DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY), '%m-%d')

// UNION

// SELECT 
//     'Work Anniversary' AS type,
//     CONCAT(ed.first_name, ' ', IFNULL(ed.last_name, '')) AS name,
//     DATE_FORMAT(
//         CASE 
//             WHEN DATE_FORMAT(ep.joining_date, '%m-%d') >= DATE_FORMAT(CURDATE(), '%m-%d')
//             THEN CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(ep.joining_date, '%m-%d'))
//             ELSE CONCAT(YEAR(CURDATE()) + 1, '-', DATE_FORMAT(ep.joining_date, '%m-%d'))
//         END, 
//         '%Y-%m-%d'
//     ) AS event_date,
    
//     CONCAT(
//         TIMESTAMPDIFF(YEAR, ep.joining_date,
//             CASE 
//                 WHEN DATE_FORMAT(ep.joining_date, '%m-%d') >= DATE_FORMAT(CURDATE(), '%m-%d')
//                 THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(ep.joining_date, '%m-%d')), '%Y-%m-%d')
//                 ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()) + 1, '-', DATE_FORMAT(ep.joining_date, '%m-%d')), '%Y-%m-%d')
//             END
//         ), 
//         ' Year Work Anniversary'
//     ) AS description,

//     ed.emp_pkey,
//     2 AS sort_type

// FROM emp_proff ep
// JOIN emp_details ed ON ed.emp_pkey = ep.emp_fkey
// WHERE ed.status = 1 
//   AND DATE_FORMAT(ep.joining_date, '%m-%d') 
//       BETWEEN DATE_FORMAT(CURDATE(), '%m-%d') 
//       AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 30 DAY), '%m-%d')
//   AND TIMESTAMPDIFF(
//         YEAR, ep.joining_date,
//         CASE 
//             WHEN DATE_FORMAT(ep.joining_date, '%m-%d') >= DATE_FORMAT(CURDATE(), '%m-%d')
//             THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(ep.joining_date, '%m-%d')), '%Y-%m-%d')
//             ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()) + 1, '-', DATE_FORMAT(ep.joining_date, '%m-%d')), '%Y-%m-%d')
//         END
//   ) >= 1 ORDER BY sort_type ASC, MONTH(event_date), DAY(event_date);
// ";

        $upcomingEvents = $this->EmployeeDetails->query($upcomingEventsQuery);
        
        $today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

foreach ($upcomingEvents as &$event) {
    // Assuming $event is a 2D array with index 0 for data
    $eventData = $event[0];
    $empPkey = $eventData['emp_pkey'];
    // $eventDate = date('Y-m-d', strtotime($eventData['event_date']));
    $eventType = $eventData['type'];

    $wishedCount = $this->Wish->find('count', [
        'conditions' => [
            'emp_fkey' => $empPkey,
            'type' => $eventType,
        ]
    ]);
    // debug($wishedCount);

    // Set `wished` key in event[0]
    $event[0]['wished'] = $wishedCount > 0 ? 1 : 0;
}
$this->set('upcomingEvents', $upcomingEvents);

//end




    //         $today = date('Y-m-d');
    //         $startDate = date('Y-m-01');         // First day of current month
    //         $endDate = date('Y-m-t');            // Last day of current month (handles 28, 30, 31 automatically)
    //  //lakshmi-pending approvals
    //     $pendingLeaveApprovalData = $this->EmployeeDetails->query("
    //     SELECT COUNT(*) AS pending_count 
    //     FROM emp_leave_transactions 
    //     WHERE Leavestatus IN ('Applied')
    //     AND Remarks != 'No need Leave' AND leave_date BETWEEN '$startDate' AND '$endDate'
    //     ");
         //edited by athira on 14-10-2025
//             $today = date('Y-m');
//             $today_date=$today.'-01';
//             $att_startdate = $this->EmployeeDetails->query("select att_start_end_fn('$today_date', 1) as monthly_att_fromdate");
//             $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn('$today_date', 2) as monthly_att_enddate");
//             $startDate1 = $att_startdate[0][0]['monthly_att_fromdate'];      
//             $endDate1 = $att_enddate[0][0]['monthly_att_enddate'];  
                    

 
      //lakshmi-pending approvals
//         $pendingLeaveApprovalData = $this->EmployeeDetails->query("
//         SELECT COUNT(*) AS pending_count 
//         FROM leaveentries 
//         WHERE LEAVESTATUS IN ('Applied')
//         AND (
//     (FROMDATE BETWEEN '$startDate1' AND '$endDate1') 
//     OR (TODATE BETWEEN '$startDate1' AND '$endDate1') 
//     OR (FROMDATE <= '$startDate1' AND TODATE >= '$endDate1')
// )

//         ");

//edited by athira on 11-11-2025

$today = date('Y-m');
$today_date = $today . '-01';

// Step 1: Get current cycle start and end dates
$att_startdate = $this->EmployeeDetails->query("SELECT att_start_end_fn('$today_date', 1) AS monthly_att_fromdate");
$att_enddate   = $this->EmployeeDetails->query("SELECT att_start_end_fn('$today_date', 2) AS monthly_att_enddate");

$startDate1 = $att_startdate[0][0]['monthly_att_fromdate'];
$endDate1   = $att_enddate[0][0]['monthly_att_enddate'];

// Step 2: Check if today is after the current cycle end date
$today_actual = date('Y-m-d');
if (strtotime($today_actual) > strtotime($endDate1)) {
    // Move to next month's cycle
    $next_month = date('Y-m', strtotime('+1 month', strtotime($today_date)));
    $next_month_date = $next_month . '-01';
    
    $att_startdate = $this->EmployeeDetails->query("SELECT att_start_end_fn('$next_month_date', 1) AS monthly_att_fromdate");
    $att_enddate   = $this->EmployeeDetails->query("SELECT att_start_end_fn('$next_month_date', 2) AS monthly_att_enddate");
    
    $startDate1 = $att_startdate[0][0]['monthly_att_fromdate'];
    $endDate1   = $att_enddate[0][0]['monthly_att_enddate'];
}

// Step 3: Fetch only leaves where FROMDATE is within the attendance cycle
$pendingLeaveApprovalData = $this->EmployeeDetails->query("
    SELECT COUNT(*) AS pending_count 
    FROM leaveentries 
    WHERE LEAVESTATUS = 'Applied'
    AND FROMDATE BETWEEN '$startDate1' AND '$endDate1'
");

//end
        // Get current date (today)
            $currentDate = date('Y-m-d');

            // Get start date (first day of current month)
            $startDate = date('Y-m-01');

            // Get end date (last day of current month)
            $endDate = date('Y-m-t');

            //end   
        $pendingLeaveApprovalCount = $pendingLeaveApprovalData[0][0]['pending_count'];

        $this->set('pendingLeaveApprovalCount', $pendingLeaveApprovalCount);

        $promotionApprovalData = $this->EmployeeDetails->query("
        SELECT COUNT(*) AS pending_count 
        FROM promotions 
        WHERE approved_status = 'N' AND created_date BETWEEN '$startDate' AND '$endDate'
        ");

        $promotionApprovalCount = $promotionApprovalData[0][0]['pending_count'];
        $this->set('promotionApprovalCount', $promotionApprovalCount);

        $expenseApprovalData = $this->EmployeeDetails->query("
        SELECT COUNT(*) AS pending_count 
        FROM emp_expense 
        WHERE expense_status = 'Applied' AND created_date BETWEEN '$startDate' AND '$endDate'
        ");

        $expenseApprovalCount = $expenseApprovalData[0][0]['pending_count'];
        $this->set('expenseApprovalCount', $expenseApprovalCount);

         // Attendance Regularization Count
        $attendanceRegData = $this->EmployeeDetails->query("
    SELECT COUNT(*) AS pending_count
    FROM employee_regularaization er
    WHERE DATE_FORMAT(er.LOGDATE, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m')    
");


        $attendanceRegCount = $attendanceRegData[0][0]['pending_count'];

        $this->set('attendanceRegCount', $attendanceRegCount);

        //edited by athira on 01-12-2025
        $attendanceVerifyData = $this->EmployeeDetails->query("
        SELECT COUNT(DISTINCT emp_fkey) AS pending_count 
        FROM attendance_register 
        WHERE record_status = '1' AND isdelete='Y'
        AND month_year = DATE_FORMAT(CURRENT_DATE, '%Y-%m')
        ");
        //end


        $attendanceVerifyCount = $attendanceVerifyData[0][0]['pending_count'];
        $this->set('attendanceVerifyCount', $attendanceVerifyCount);
        // End of lakshmi-pending approvals

//edited by athira on 04-07-2025
$classificationData = $this->EmployeeDetails->query(" 
SELECT 
    CASE 
        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 20 THEN 'Under 20'
        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 20 AND 29 THEN '20-30'
        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 30 AND 39 THEN '30-40'
        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 40 AND 49 THEN '40-50'
        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 50 AND 59 THEN '50-60'
        ELSE '60+'
    END AS age_group,
    COUNT(*) AS employee_count
FROM emp_details WHERE status = 1
GROUP BY age_group
ORDER BY MIN(date_of_birth);
");
//end

$colorMap = [
    'Under 20' => '#a37182',
    '20-30'    => '#60a69f',
    '30-40'    => '#41ec39',
    '40-50'    => '#78b6d9',
    '50-60'    => '#6682bb',
    '60+'      => '#E91E63'
    
];


// Format data for Morris.js Donut Chart
$donutData = [];
$colorData = [];

foreach ($classificationData as $row) {
    $classification = !empty($row[0]['age_group']) ? $row[0]['age_group'] : 'Unknown';
    $employeeCount = !empty($row[0]['employee_count']) ? (int) $row[0]['employee_count'] : 0;

    $donutData[] = [
        'label' => $classification,
        'value' => $employeeCount
    ];

    // Add matching color (default to grey if not defined)
    $colorData[] = $colorMap[$classification] ? $colorMap[$classification] : '#CCCCCC';
}

// Pass data to view as JSON
$this->set('donutData', json_encode($donutData));
$this->set('donutColors', json_encode($colorData));

//horizontal bar chart-lakshmi
$departmentStats = $this->EmployeeDetails->query("
    SELECT 
        d.dept_name AS department,
        COUNT(DISTINCT ed.emp_pkey) AS value
    FROM 
        department d
    LEFT JOIN 
        emp_proff ep ON d.dept_code = ep.emp_dept
    LEFT JOIN 
        emp_details ed ON ep.emp_fkey = ed.emp_pkey AND ed.status = 1
    WHERE 
        d.status = 1
    GROUP BY 
        d.dept_name, d.dept_code
    ORDER BY 
        value DESC, d.dept_name ASC
");

//end

// Format data properly for Morris.js
$formattedDeptStats = array_map(function($stat) {
    return array(
        'department' => trim(strval($stat['d']['department'])), // Ensure clean string
        'value' => intval($stat[0]['value'])  // Ensure integer
    );
}, $departmentStats);

$this->set('departmentStats', json_encode($formattedDeptStats));
// Format data for Morris.js horizontal bar chart
//$formattedDeptStats = array();
//foreach ($departmentStats as $stat) {
 //   $formattedDeptStats[] = array(
   //     'department' => $stat['d']['department'], 
     //   'value' => (int)$stat[0]['value']
    //);
// }

//$this->set('departmentStats', json_encode($formattedDeptStats));
// debug($departmentStats);

//end-lakshmi
        

     




        // donut chart edited by gawtham - 24-03-2025 
        // line char edited by gawtham - 24-03-2025 
        $user_group = $this->Session->read('user_group');
$company_code = $this->Session->read('company_code');

if ($this->Session->read('ds') == null) {
    $this->redirect(array('controller' => 'Site', 'action' => 'login'));
}

// Fetch salary data
$salaryData = $this->EmployeeDetails->query("  
    SELECT  
        DATE_FORMAT(STR_TO_DATE(month_year, '%Y-%m'), '%b-%Y') AS formatted_month,   
        SUM(monthly_ctc) AS salary  
    FROM payroll_master  
    WHERE action = 'Approved'  
      AND month_year >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 6 MONTH), '%Y-%m-01')  
    GROUP BY formatted_month  
    ORDER BY STR_TO_DATE(month_year, '%Y-%m') ASC;
");

// Format data for Morris.js Line Chart
$lineData = [];
foreach ($salaryData as $row) {
    $lineData[] = [
        'month' => $row[0]['formatted_month'],  // Use formatted month
        'value' => (float) $row[0]['salary']
    ];
}

// Pass data to view as JSON
$this->set('chartData', json_encode($lineData)); 
        // line chart edited by gawtham - 24-03-2025 


        // edited by gawtham 1-4-2025

            // Ensure session is valid
         // Ensure session is valid
if ($this->Session->read('ds') == null) {
    $this->redirect(['controller' => 'Site', 'action' => 'login']);
}

// Fetch absent count
$absentData = $this->EmployeeDetails->query("
SELECT COUNT(*)
 AS absent_count
FROM emp_detail_timeattandance
WHERE att_date BETWEEN '2022-12-01' AND '2022-12-31' 
AND (
    present IS NULL OR present = 'A/A'  
    OR others = 'LOP'
);
");

// Extract the absent count value
$absent_count = $absentData[0][0]['absent_count'];

// Fetch count of employees whose 'pf' is NULL or empty string
$pfData = $this->EmployeeDetails->query("
    SELECT COUNT(*) AS pf_count
    FROM emp_details
    WHERE pf IS NULL OR pf = '';
");

// Extract the pf count value
$pf_count = $pfData[0][0]['pf_count'];


$leavesData = $this->EmployeeDetails->query("
    SELECT COUNT(*) AS leave_count
    FROM emp_detail_timeattandance
    WHERE yearmonth = '2024-05-01' 
    AND leaves IS NOT NULL;
");

// Extract the leave count value
$leave_count = $leavesData[0][0]['leave_count'];
// Pass both data to the view
$this->set('absent_count', $absent_count);
$this->set('pf_count', $pf_count);
$this->set('leave_count', $leave_count);
        
        
        // edited by gawtham 1-4-2025
    }

    // edited by gawtham 25- 04-25
    // 






    public function menuAudit($menu)
    {
        $this->autoRender = FALSE;
        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $dataForHistory = array();
        $dataForHistory['report_type'] = $menu;
        $dataForHistory['mode'] = "Menu";
        $user_id = $this->Session->read('login_user_id');
        $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
        $user_name = $this->Session->read('user_name');
        $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';
        $this->ReportAudit->save($dataForHistory);
        echo json_encode(array('success' => 1, 'msg' => $menu));
    }

    public function hierarchydashboard()
    {
        //Hierarchy dashboard
        $user_group = $this->Session->read('user_group');
        $emp_fkey = $this->Session->read('emp_fkey');
        $company_code = $this->Session->read('company_code');
        $arr_menus = $this->getMenus();
        $this->set('arr_menu', $arr_menus);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //$count = $this->EmployeeDetails->find("count", array("conditions" => array("emp_id !=" => null)));
        // $this->set("total_emps", $count);
        $table_joins[] = array(
            'table' => 'emp_proff',
            'alias' => 'EmployeeProfessionalDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
        );
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        //$count = $this->EmployeeDetails->find("count", array("joins" => $table_joins, "conditions" => array("EmployeeDetails.status" => "1", "EmployeeProfessionalDetails.attr1 = '$emp_fkey' or EmployeeProfessionalDetails.emp_fkey = '$emp_fkey' ")));
        //heirarchy condition
        $today = $this->DeviceAttendance->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
        $emps = array();
        foreach ($today as $key => $att) {
            $emps[] = $att['a']['emp_fkey'];
        }
        $str = implode("','", $emps);
        $count = $this->EmployeeDetails->find("count", array("joins" => $table_joins, "conditions" => array("EmployeeDetails.status" => "1", " EmployeeProfessionalDetails.emp_fkey in('$str') ")));
        $this->set("total_emps", $count);
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        //$present = $this->DeviceAttendance->find("count",array("conditions"=>array("emp_id !="=>null)));
        //        $arr_present = $this->DeviceAttendance->query("SELECT COUNT(DISTINCT DeviceAttendance.emp_id) AS presentcount,EmployeeDetails.first_name 
        //                FROM `device_attandance` AS `DeviceAttendance` 
        //                left join emp_details as EmployeeDetails on(DeviceAttendance.emp_id = EmployeeDetails.emp_id) 
        //                left join emp_proff as EmpProff on(EmployeeDetails.emp_pkey = EmpProff.emp_fkey) 
        //                WHERE DeviceAttendance.LOGDATE >= date_sub(current_date,interval 1 day) 
        //                AND UCASE(DeviceAttendance.status) = 'Y' 
        //                AND UCASE(DeviceAttendance.c1) = 'in' 
        //                AND DeviceAttendance.emp_id not in (select emp_id from device_attandance where DeviceAttendance.LOGDATE >= date_sub(current_date,interval 1 day)
        //                and UCASE(DeviceAttendance.status) = 'Y' AND UCASE(DeviceAttendance.c1) = 'out' )
        //                    and (EmpProff.emp_fkey = '$emp_fkey' or EmpProff.attr1 = '$emp_fkey' )");
        //        $present = isset($arr_present[0][0]['presentcount']) ? $arr_present[0][0]['presentcount'] : 0;
        $emp_fkey = $this->Session->read('emp_fkey');

        $general_settings = $this->DeviceAttendance->query("SELECT COUNT(*) AS count FROM genaral_setings WHERE when_itis='Employee_Login' AND status=1");

        if ($general_settings[0][0]['count']) {
            $settings_runner = $this->DeviceAttendance->query("select count(*) as count from settings_runner where emp_fkey=$emp_fkey and exit_status IN ('Finished','Never')");
            $this->set("settings_runner", $settings_runner[0][0]['count']);
        }
        $arr_present = $this->DeviceAttendance->query("select count(*) presentcount from present_today where emp_pkey in ('$str' ) ");
        $present = isset($arr_present[0][0]['presentcount']) ? $arr_present[0][0]['presentcount'] : 0;
        $this->set("present", $present);
        //added by megha on 25/11/2019 presenttodayall list    
        $today = date('Y-m-d');     //edited by ASHIN on 28-06-24
        $arr_presentall = $this->DeviceAttendance->query("select count(*) presentcount from present_today_all where emp_pkey in ('$str' ) and STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' ");
        $presentall = isset($arr_presentall[0][0]['presentcount']) ? $arr_presentall[0][0]['presentcount'] : 0;
        $this->set("presentall", $presentall);

        $present = $this->EmployeeDetails->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
        $user_id = isset($present['0']['user_credentials']['user_id']) ? $present['0']['user_credentials']['user_id'] : '';
        $present_type = $this->DeviceAttendance->query("SELECT punchtype FROM mob_user_credentials WHERE user_id = '$user_id' ");
        $this->set("punch_type", isset($present_type['0']['mob_user_credentials']['punchtype']) ? $present_type['0']['mob_user_credentials']['punchtype'] : '');

        /* $today_join[] = array(
          'table' => 'emp_details',
          'alias' => 'EmployeeDetails',
          'type' => 'LEFT',
          'foreignKey' => false,
          'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
          );
          $today_join[] = array(
          'table' => 'emp_proff',
          'alias' => 'EmployeeProfessionalDetails',
          'type' => 'LEFT',
          'foreignKey' => false,
          'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
          );

          $today_cond = array("EmployeeProfessionalDetails.attr1" => $emp_fkey, "LOGDATE >=" => date("Y-m-d", strtotime("now")), "DeviceAttendance.status >=" => "Y");
          $today_att = $this->DeviceAttendance->find("all", array(
          "order" => "EmployeeDetails.emp_id ASC,DeviceAttendance.LOGDATE ASC",
          "conditions" => $today_cond,
          'joins' => $today_join,
          "fields" => "DeviceAttendance.*EmployeeDetails.first_name,EmployeeDetails.last_name,if((`DeviceAttendance`.`C3` in ('',NULL)),`DeviceAttendance`.`branch_code`,`DeviceAttendance`.`C3`) AS `Location`"
          )
          );
          $this->set("today_att", $today_att);

          $this_month_cond = array("EmployeeProfessionalDetails.attr1" => $emp_fkey, "LOGDATE >=" => date("Y-m-01", strtotime("now")), "LOGDATE <=" => date("Y-m-t", strtotime("now")), "DeviceAttendance.status >=" => "Y");
          $this_month_att = $this->DeviceAttendance->find("all", array(
          "order" => "EmployeeDetails.emp_id,DeviceAttendance.LOGDATE",
          "conditions" => $this_month_cond,
          'joins' => $today_join,
          "fields" => "DeviceAttendance.*,EmployeeDetails.first_name,EmployeeDetails.last_name,if((`DeviceAttendance`.`C3` in ('',NULL)),`DeviceAttendance`.`branch_code`,`DeviceAttendance`.`C3`) AS `Location`"
          )
          );
          $this->set("this_month_att", $this_month_att);

          $last_month_cond = array("EmployeeProfessionalDetails.attr1" => $emp_fkey, "LOGDATE >=" => date("Y-m-01", strtotime('-1 months', strtotime("now"))), "LOGDATE <=" => date("Y-m-t", strtotime('-1 months', strtotime("now"))), "DeviceAttendance.status >=" => "Y");
          $last_month_att = $this->DeviceAttendance->find("all", array(
          "order" => "EmployeeDetails.emp_id,DeviceAttendance.LOGDATE",
          "conditions" => $last_month_cond,
          'joins' => $today_join,
          "fields" => "DeviceAttendance.*,EmployeeDetails.first_name,EmployeeDetails.last_name,if((`DeviceAttendance`.`C3` in ('',NULL)),`DeviceAttendance`.`branch_code`,`DeviceAttendance`.`C3`) AS `Location`"
          )
          );
          $this->set("last_month_att", $last_month_att); */

        $arr_empleaverequests = $this->listemployeeleaverequests(0);
        $this->set("arr_empleaverequests", $arr_empleaverequests);
        $arr_events = $this->getEvents($emp_fkey);
        debug($arr_events);
        $this->set("arr_events", $arr_events);

        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
        $this->set('arr_employees', $arr_employees);
        $table_joins[] = array(
            'table' => 'user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
        $table_joins[] = array(
            'table' => 'emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
        //            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
        //            $this->set('arr_employees', $arr_employees);
        //        $conditions = array(
        //            'OR' => array(
        //                array("DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d')" => date('m-d'), 'status' => 1),
        //                array("DATE_FORMAT(Empproff.joining_date,'%m-%d')" => date('m-d'), 'status' => 1),
        //            )
        //        );
        //        //$conditions[] = array("EmployeeDetails.date_of_birth" == date('Y-m-d') or "Empproff.joining_date" == date('Y-m-d'));
        //        $arr_employees_pics = $this->EmployeeDetails->find("all", array("fields" => array("EmployeeDetails.date_of_birth", "EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date"), "joins" => $table_joins, "conditions" => $conditions));
        //        $this->set('arr_employees_pics', $arr_employees_pics);
        //debug($arr_employees_pics);
        $emp_pkeys = array();
        //        foreach ($arr_employees_pics as $val) {
        //            $emp_pkeys[] = $val['EmployeeDetails']['emp_pkey'];
        //        }
        //        $wish = 0;
        //        if (in_array("$emp_fkey", $emp_pkeys)) {
        //            $wish = 1;
        //        }
        //        $this->set("wish", $wish);
        $conditions = array(
            //'OR' => array(
            array("DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d')" => date('m-d'), 'status' => 1, 'emp_pkey' => $emp_fkey)
            //array("DATE_FORMAT(Empproff.joining_date,'%m-%d')" => date('m-d'), 'status' => 1),
            //)
        );
        $conditions1 = array(
            array("DATE_FORMAT(Empproff.joining_date,'%m-%d')" => date('m-d'), 'status' => 1, 'emp_pkey' => $emp_fkey)
        );
        //$conditions[] = array("EmployeeDetails.date_of_birth" == date('Y-m-d') or "Empproff.joining_date" == date('Y-m-d'));
        $arr_employees_pics = $this->EmployeeDetails->find("all", array("fields" => array("EmployeeDetails.date_of_birth", "EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar"), "joins" => $table_joins, "conditions" => $conditions));
        $this->set('arr_employees_pics', $arr_employees_pics);
        $arr_employees_work = $this->EmployeeDetails->find("all", array("fields" => array("EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date"), "joins" => $table_joins, "conditions" => $conditions1));
        $this->set('arr_employees_work', $arr_employees_work);
        //Get total number of devices
        $cnt_total_devices = $this->Device->find("count", array("conditions" => array('Device.company_code' => $this->Session->read('company_code'))));
        $this->set('cnt_total_devices', $cnt_total_devices);

        $this->render('hierarchydashboard');
    }

    public function load_birthdays()
    {
        $date = date('Y-m-d');          //edited by ASHIN on 28-06-24
        $table_joins[] = array(
            'table' => 'user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
        $table_joins[] = array(
            'table' => 'emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
        $table_joins[] = array(
            'table' => 'wishes',
            'alias' => 'Wish',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('Wish.emp_fkey = Empproff.emp_fkey', 'Wish.type' => 'Birthday', 'Wish.date' => $date)
        );
        $table_joins1[] = array(
            'table' => 'user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
        $table_joins1[] = array(
            'table' => 'emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
        $table_joins1[] = array(
            'table' => 'wishes',
            'alias' => 'Wish',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('Wish.emp_fkey = Empproff.emp_fkey', 'Wish.type' => 'Work Anniversary', 'Wish.date' => $date)
        );
        //            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
        //            $this->set('arr_employees', $arr_employees);
        $conditions = array(

            //                array("DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d')" => date('m-d'), 'status' => 1),
            //                array("DATE_FORMAT(Empproff.joining_date,'%m-%d')" => date('m-d'), 'status' => 1),
            //   array("MOD( DATEDIFF( CURDATE( ) , `EmployeeDetails.date_of_birth`) /30, 12 ) >1 and (((month(`EmployeeDetails.date_of_birth`)) = (month(curdate())) 
            //   and (day(`EmployeeDetails.date_of_birth`)) > (day (curdate() ))) or ((month(`EmployeeDetails.date_of_birth`)) = (month(curdate())+1) and (day(`EmployeeDetails.date_of_birth`)) < (day (curdate() ))))", 'status' => 1)
            array(" DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +30 DAY), '%m-%d')
 or DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +31 DAY), '%m-%d') 
 or DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +7 DAY), '%m-%d') or 
 DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +2 DAY), '%m-%d') or 
 DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(curdate(), '%m-%d')", 'status' => 1)
        );
        $conditions1 = array(

            array(" DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +30 DAY), '%m-%d')
 or DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +31 DAY), '%m-%d') 
 or DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +7 DAY), '%m-%d') or 
 DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +2 DAY), '%m-%d') or 
 DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(curdate(), '%m-%d') ", 'status' => 1)

        );
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->Latein->useDbConfig = $this->Session->read('ds');
        //$conditions[] = array("EmployeeDetails.date_of_birth" == date('Y-m-d') or "Empproff.joining_date" == date('Y-m-d'));
        $arr_employees_pics = $this->EmployeeDetails->find(
            "all",
            array(
                "fields" => array("Wish.emp_fkey,EmployeeDetails.date_of_birth", "EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date"),
                "joins" => $table_joins,
                "order" => "EmployeeDetails.date_of_birth DESC",
                "conditions" => $conditions
            )
        );
        $arr_employees_pics1 = $this->EmployeeDetails->find(
            "all",
            array(
                "fields" => array("Wish.emp_fkey,EmployeeDetails.date_of_birth", "EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date"),
                "joins" => $table_joins1,
                "order" => "Empproff.joining_date DESC",
                "conditions" => $conditions1
            )
        );

        $arr_reminders = $this->EmployeeDetails->query("select emp_details.first_name,emp_details.last_name,emp_proff.day_time_seq,emp_proff.HOLIDAY_GROUP_ID,emp_proff.LEAVEPOLICY_GROUP_ID,emp_proff.structure_id from emp_proff left join emp_details on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.status = 1 and (emp_proff.day_time_seq is null or emp_proff.HOLIDAY_GROUP_ID is null or emp_proff.LEAVEPOLICY_GROUP_ID is null or emp_proff.structure_id is null) ");
        $salary_missed = $this->Latein->query("select concat(first_name,' ',ifnull(last_name,'')) name from emp_details where emp_details.status = '1' and emp_pkey not in (select distinct(emp_fkey) from emp_ctc_upload)");

        $today = date('Y-m-d');          //edited by ASHIN on 28-06-24
        $leaves = $this->Latein->query("select count(*) lea from emp_leave_transactions where Leavestatus in ('Approved') and leave_date = '$today' ");
        $leaves = isset($leaves['0']['0']['lea']) ? $leaves['0']['0']['lea'] : 0;
        $this->set("leaves", $leaves);
        $this->set("salary_missed", $salary_missed);
        $this->set("reminders", $arr_reminders);
        $this->set('arr_employees_pics', $arr_employees_pics);
        $this->set('arr_employees_pics1', $arr_employees_pics1);
    }

    public function getEvents($emp_fkey = 0) {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $arr_events = $this->LeaveRequests->query("
            select 'HOL', HOLIDAYNAME, HOLIDAYDATE as Upcoming_month
            from holidays 
            left join emp_proff on (holidays.HOLIDAY_GROUP_ID = emp_proff.HOLIDAY_GROUP_ID) 
            where emp_fkey in (select emp_pkey from emp_details where status =1) 
and HOLIDAYDATE between current_date and date_add(HOLIDAYDATE, interval 90 day) 

union
select 'Birthday', first_name, date_of_birth as Upcoming_month
            from emp_details 
            where date_format(date_of_birth,'%m-%d') between date_format(current_date,'%m-%d') 
            and date_format(date_add(current_date,INTERVAL 30 DAY),'%m-%d')
            and status = 1 
union
select 'Work Anniversary', first_name, joining_date as Upcoming_month
            from emp_proff 
            left join emp_details on (emp_details.emp_pkey = emp_proff.emp_fkey) 
            where date_format(joining_date,'%m-%d') between date_format(current_date,'%m-%d') 
            and date_format(date_add(current_date,INTERVAL 30 DAY),'%m-%d')
            and emp_details.status = 1 order by Upcoming_month;");
        return $arr_events;
    }

    public function empcalendar()
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $emp_fkey = $this->Session->read('emp_fkey');
        $currentyear = date("Y");
        $arr_empevents = $this->EmployeeDetails->query("select emp_proff.joining_date, emp_proff.HOLIDAY_GROUP_ID,emp_proff.emp_fkey,emp_details.date_of_birth from emp_proff 
                                                          left join emp_details on emp_details.emp_pkey = emp_proff.emp_fkey where  emp_proff.emp_fkey = $emp_fkey");
        $holidayid = isset($arr_empevents['0']['emp_proff']['HOLIDAY_GROUP_ID']) ? $arr_empevents['0']['emp_proff']['HOLIDAY_GROUP_ID'] : '0';
        //         debug($arr_empevents);    
        //         $joinindate = isset($arr_empevents['0']['emp_proff']['joining_date']) ? $arr_empevents['0']['emp_proff']['joining_date'] : 0;
        //         $birthday = isset($arr_empevents['0']['emp_details']['date_of_birth']) ? $arr_empevents['0']['emp_details']['date_of_birth'] : 0;
        //         $join = $currentyear.date("-m-d", strtotime($joinindate));
        //         $birth = $currentyear.date("-m-d", strtotime($birthday));
        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAYNAME,HOLIDAYDATE,HOLIDAY_GROUP_ID,HOLIDAYTYPE from holidays where HOLIDAY_GROUP_ID = '$holidayid' and HOLIDAYDATE between '$currentyear-01-01' and '$currentyear-12-31'");
        $holidays = array();
        $data = array();
        foreach ($arr_holidays as $val) {
            $holidays['HOLIDAYID'] = $val['holidays']['HOLIDAYDATE'];
            $holidays['name'] = $val['holidays']['HOLIDAYNAME'];
            $holidays['HOLIDAYTYPE'] = $val['holidays']['HOLIDAYTYPE'];
            $holidays['backgroundColor'] = 'crimson';
            $holidays['borderColor'] = 'crimson';
            //             $holidays['rendering'] = 'background';
            $holidays['start'] = $val['holidays']['HOLIDAYDATE'];
            $holidays['end'] = $val['holidays']['HOLIDAYDATE'];

            $data[] = $holidays;
        }


        $birthday = isset($arr_empevents['0']['emp_details']['date_of_birth']) ? $arr_empevents['0']['emp_details']['date_of_birth'] : 0;
        $birth = $currentyear . date("-m-d", strtotime($birthday));
        $holidays['name'] = 'Your Birthday';
        $holidays['backgroundColor'] = 'yellow';
        $holidays['borderColor'] = 'yellow';
        $holidays['start'] = $birth;
        $holidays['end'] = $birth;
        $data[] = $holidays;

        $joinindate = isset($arr_empevents['0']['emp_proff']['joining_date']) ? $arr_empevents['0']['emp_proff']['joining_date'] : 0;
        $join = $currentyear . date("-m-d", strtotime($joinindate));
        $holidays['name'] = 'Your Work Anniversary';
        $holidays['backgroundColor'] = 'turquoise';
        $holidays['borderColor'] = 'turquoise';
        $holidays['start'] = $join;
        $holidays['end'] = $join;
        $data[] = $holidays;

        //            debug($data);
        echo json_encode($data);
    }

    public function empdashboard()
    {
        //action logic goes here..
        //Employee dashboard
        $emp_fkey = $this->Session->read('emp_fkey');
        $company_code = $this->Session->read('company_code');
        $curr_year = date("Y");

        if ($emp_fkey) {

            $arr_menus = $this->getMenus();
            $this->set('arr_menu', $arr_menus);
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
            $this->AttendancePunch->useDbConfig = $this->Session->read('ds');
            $this->AttendancePunch->query("SELECT `last_punch_fn`($emp_fkey) punch");
            $day = date("Y-m-01");
            $to_days = date("Y-m-t");
            $today = date('Y-m-d');
            $lmonth = date("Y-m-01", strtotime('-1 months', strtotime("now")));
            //            debug($yestday);
            //-----birthday and joingday -----//
            $arr_empevents = $this->EmployeeDetails->query("select emp_proff.joining_date, emp_proff.HOLIDAY_GROUP_ID,emp_proff.emp_fkey,emp_details.date_of_birth from emp_proff 
                                                  left join emp_details on emp_details.emp_pkey = emp_proff.emp_fkey where  emp_proff.emp_fkey = $emp_fkey");
            $joinindate = isset($arr_empevents['0']['emp_proff']['joining_date']) ? $arr_empevents['0']['emp_proff']['joining_date'] : 0;
            $birthday = isset($arr_empevents['0']['emp_details']['date_of_birth']) ? $arr_empevents['0']['emp_details']['date_of_birth'] : 0;
            $currentyear = date("Y");
            $join = $currentyear . date("-m-d", strtotime($joinindate));
            $birth = $currentyear . date("-m-d", strtotime($birthday));
            //            debug($join);
            $this->set("join", $join);
            $this->set("birth", $birth);

            //-----total leave count -----//
            //            $arr_yearleavcount = $this->EmployeeDetails->query("SELECT  SUM(`leave_total`)  FROM `attendance_register`WHERE `month_year` between '$curr_year-01' AND '$curr_year-12' AND `emp_fkey` = '$emp_fkey'");
            $arr_yearleavcount = $this->EmployeeDetails->query("SELECT SUM(`leave_days`) FROM `leaveentries` WHERE `EMP_fkey` = '$emp_fkey' AND  
  applied_date between '$curr_year-01-01' and '$curr_year-12-31' and (LEAVESTATUS = 'Approved') or (LEAVESTATUS = 'Authorised')");
            //            debug("SELECT  SUM(`leave_total`)  FROM `attendance_register`WHERE `month_year` between '$curr_year-01' AND '$curr_year-12' AND `emp_fkey` = '$emp_fkey'");
            $yearly_leavecount = isset($arr_yearleavcount['0']['0']['SUM(`leave_days`)']) ? $arr_yearleavcount['0']['0']['SUM(`leave_days`)'] : '0';
            $this->set("yearly_leavecount", $yearly_leavecount);
            //--days count--//

            $working_days_counts = $this->EmployeeDetails->query("select count(*) working_days from emp_detail_timeattandance where emp_pkey = '$emp_fkey' and att_date between '$day' and '$to_days' and weekoff is null and holiday is null and others is null -- leaves != LOP");
            $leaves_taken_count = $this->EmployeeDetails->query("select count(*) working_days from emp_detail_timeattandance where emp_pkey = '$emp_fkey' and att_date between '$day' and '$to_days' and leaves not in ('LOP/LOP') ");
            $present_days_count = $this->EmployeeDetails->query("select sum(a) presents from(select count(*) as a from emp_detail_timeattandance 
where (weekoff is null or holiday is null) and present in ('P/P') and weekoff is null and holiday is null and emp_pkey = '$emp_fkey' and att_date between '$day' and
'$to_days' union all
select count(*)/2 as a from emp_detail_timeattandance where ((weekoff is null) or (holiday is null)) and ((instr(present,'/P') > 0 
or instr(present,'P/') > 0)) and present != 'P/P' and emp_pkey = '$emp_fkey' 
and att_date between '$day' and '$to_days'
)ass 
");
            //                    ("select sum(a) presents from(select count(*) as a from emp_detail_timeattandance where present in ('P/P') and 	emp_pkey = '$emp_fkey'  and (weekoff is null or holiday is null) and att_date between '$day' and '$to_days' union all
            //                                                                select count(*)/2 as a from emp_detail_timeattandance where (instr(present,'/P') > 0 or instr(present,'P/') > 0) and present != 'P/P'  and 	emp_pkey = '$emp_fkey' and (weekoff is null or holiday is null) and att_date between '$day' and '$to_days')ass ");
            //            $lastmonthwrkingdays = $this->EmployeeDetails->query("select count(*) as working_days from emp_detail_timeattandance where emp_pkey = '$emp_fkey' and yearmonth  = '$lmonth' and weekoff is null and holiday is null");
            //            $lastmnthpresnt = $this->EmployeeDetails->query("select sum(a) presents from(select count(*) as a from emp_detail_timeattandance where present in ('P/P') and emp_pkey = '$emp_fkey' and yearmonth  = '$lmonth' union all select count(*)/2 as a from emp_detail_timeattandance where (instr(present,'/P') > 0 or instr(present,'P/') > 0) and present != 'P/P'  and emp_pkey = '$emp_fkey' and yearmonth  = '$lmonth')ass ");
            //            debug($working_days_counts);
            // Emp id -------//           
            $emp = $this->EmployeeDetails->query("select emp_id from emp_details where emp_pkey = $emp_fkey");
            $empid = isset($emp['0']['emp_details']['emp_id']) ? $emp['0']['emp_details']['emp_id'] : '';
            $idd = $this->EmployeeDetails->query("SELECT `employee_id` FROM `employee_info` WHERE `emp_pkey` = '$emp_fkey'");
            $employeeid = isset($idd['0']['employee_info']['employee_id']) ? $idd['0']['employee_info']['employee_id'] : '';
            $this->set("employeeid", $employeeid);
            //   --Miss punch ----//       

            $arr_misspunch = $this->EmployeeDetails->query("select ed.emp_id,concat(ed.first_name,' ',ifnull(ed.last_name,'')) as fullname, yearmonth,att_date,present, count(*) misscount
                                                            from emp_detail_timeattandance edt inner join emp_details ed on ed.emp_pkey=$emp_fkey
                                                            and present<>'P/P' and present is not null and date_format(current_date,'%y-%m') = date_format(yearmonth,'%y-%m') and att_date not in (date_format(current_date,'%y-%m-%d'))  and edt.emp_pkey = $emp_fkey
                                                            group by edt.emp_pkey,att_date order by 3 desc");
            $misspunch = isset($arr_misspunch['0']['0']['misscount']) ? $arr_misspunch['0']['0']['misscount'] : '0';
            //---- Last Punch -----// 

            $lastpunch = $this->EmployeeDetails->query("SELECT LOGDATE,DIRECTION FROM device_attandance where emp_id = $empid ORDER BY LOGDATE DESC  LIMIT 1 ;  ");

            $arr_lastpuchinfo = $this->AttendancePunch->query("SELECT `last_punch_fn`($emp_fkey) punch");
            //            debug($arr_lastpuchinfo);
            $dir = isset($arr_lastpuchinfo['0']['0']['punch']) ? $arr_lastpuchinfo['0']['0']['punch'] : 'out';
            $this->set("dir", $dir);
            //             debug($lastpunch);
            //------Absent Days -----//

            $arr_absentdays = $this->EmployeeDetails->query("select sum(absent)absent from (SELECT count(*) as absent FROM `emp_detail_timeattandance`WHERE `yearmonth` = '$day' AND `emp_pkey` = '$emp_fkey' and att_date between '$day' and '$today' 
                                                            and present is null and weekoff is null and holiday is null 
                                                            union all
                                                            SELECT count(*)*.5 as absent FROM `emp_detail_timeattandance`WHERE `yearmonth` = '$day' AND `emp_pkey` = '$emp_fkey' and att_date between '$day' and '$today' 
                                                            and present in('P/A','A/P') and weekoff is null and holiday is null)m");

            $absentdays = isset($arr_absentdays['0']['0']['absent']) ? $arr_absentdays['0']['0']['absent'] : '0';

            //---- Employee Details ------//   

            $empinfo = $this->EmployeeDetails->query("select branch,designation,department,joining_date from employee_info where emp_pkey = $emp_fkey");

            //-- Shift policy / Session -------//   

            $shiftpolicy = $this->EmployeeDetails->query("select on_dutty1,off_dutty1,off_dutty2 from working_day_time_procedures where day_time_seq in(select day_time_seq from emp_proff where emp_fkey =$emp_fkey)");

            //----Salary Bar Chart -----//

            $arr_salarybarchart = $this->EmployeeDetails->query("SELECT * FROM `payroll_master`WHERE emp_fkey = '$emp_fkey' and action = 'Approved' ORDER BY month_year  DESC LIMIT 7");
            //            debug($arr_salarybarchart);
            $arr_month = array();
            $arr_netsalary = array();
            $arr_grosssalary = array();
            if (!empty($arr_salarybarchart)) {
                foreach ($arr_salarybarchart as $value) {

                    $arr_month[] = date("Y-m", strtotime($value['payroll_master']['month_year']));
                    $arr_netsalary[] = isset($value['payroll_master']['net_salary']) ? $value['payroll_master']['net_salary'] : '0';
                    $arr_grosssalary[] = isset($value['payroll_master']['gross_salary']) ? $value['payroll_master']['gross_salary'] : '0';
                }
                $showbarchart = 'Yes';
            } else {
                $arr_month[] = 0;
                $arr_netsalary[] = 0;
                $arr_grosssalary[] = 0;
                $showbarchart = 'No';
            }
            //            debug($arr_month);
            $month_rev = array_reverse($arr_month);
            $netsalary_rev = array_reverse($arr_netsalary);
            $gross_rev = array_reverse($arr_grosssalary);
            $month = implode("','", $month_rev);
            $netsalary = implode(", ", $netsalary_rev);
            $grosssalary = implode(", ", $gross_rev);
            //            if(empty($month)){
            //                $month = "No Salary To Show";
            //            }
            //            debug($month);
            $this->set("arr_month", $month);
            $this->set("arr_netsalary", $netsalary);
            $this->set("arr_grosssalary", $grosssalary);
            $this->set("showbarchart", $showbarchart);

            //---Donut chart ----//           

            $arr_donutchartdata = $this->EmployeeDetails->query("SELECT presant_total,month_year,weekoff_total,holiday_total,leave_total,lop_total,month_year FROM `attendance_register`WHERE emp_fkey = '$emp_fkey' and isdelete = 'N' ORDER BY month_year  DESC LIMIT 1");
            //            debug($arr_donutchartdata);
            if (!empty($arr_donutchartdata)) {
                $showdonutchart = 'Yes';
            } else {
                $showdonutchart = 'No';
            }
            $lastmonth = isset($arr_donutchartdata['0']['attendance_register']['month_year']) ? $arr_donutchartdata['0']['attendance_register']['month_year'] : '';
            if ($lastmonth == '') {
                $last_date = '';
            } else {
                $last_date = date("F-Y", strtotime($lastmonth));
            }


            $presentdays = isset($arr_donutchartdata['0']['attendance_register']['presant_total']) ? $arr_donutchartdata['0']['attendance_register']['presant_total'] : '0';
            $leavedays = isset($arr_donutchartdata['0']['attendance_register']['leave_total']) ? $arr_donutchartdata['0']['attendance_register']['leave_total'] : '0';
            $lop = isset($arr_donutchartdata['0']['attendance_register']['lop_total']) ? $arr_donutchartdata['0']['attendance_register']['lop_total'] : '0';
            $holidays = isset($arr_donutchartdata['0']['attendance_register']['holiday_total']) ? $arr_donutchartdata['0']['attendance_register']['holiday_total'] : '0';
            $weekoff = isset($arr_donutchartdata['0']['attendance_register']['weekoff_total']) ? $arr_donutchartdata['0']['attendance_register']['weekoff_total'] : '0';
            $calanderdays = $presentdays + $leavedays + $lop + $holidays + $weekoff;
            $workingdays = $presentdays + $leavedays + $lop;
            $this->set("presentdays", $presentdays);
            $this->set("leavedays", $leavedays);
            $this->set("lop", $lop);
            $this->set("holidays", $holidays);
            $this->set("calanderdays", $calanderdays);
            $this->set("workingdays", $workingdays);
            $this->set("weekoff", $weekoff);
            $this->set("showdonutchart", $showdonutchart);
            $this->set("last_date", $last_date);
            $pr_count = isset($present_days_count['0']['0']['presents']) ? round($present_days_count['0']['0']['presents'], 1) : 0.0;
            if ($working_days_counts['0']['0']['working_days'] > 0) {
                $wr_count = $working_days_counts['0']['0']['working_days'];
            } else {
                $wr_count = 1;
            }
            $lea_count = isset($leaves_taken_count['0']['0']['working_days']) ? $leaves_taken_count['0']['0']['working_days'] : 0;

            $percentage_total = 0;
            if ($pr_count != 0) {
                $prsent_percentage = 100 * ($pr_count) / ($wr_count);
                $percentage_total = round($prsent_percentage, 1);
            }
            $this->set("percentage_total", $percentage_total);

            //-----------last month percentage -------------//
            $lastmonthper_total = 0;
            if ($presentdays != 0) {
                $lastmonthper = 100 * $presentdays / $workingdays;
                $lastmonthper_total = round($lastmonthper, 1);
            }
            $this->set("lastmonthper_total", $lastmonthper_total);
            // -------  line Chart ------ //

            $areachartdata = $this->EmployeeDetails->query("SELECT presant_total,leave_total,lop_total,month_year FROM `attendance_register`WHERE emp_fkey = '$emp_fkey' and isdelete = 'N' ORDER BY month_year  DESC LIMIT 7");


            //          debug($areachartdata);
            if (!empty($areachartdata)) {
                foreach ($areachartdata as $value) {

                    $precount[] = isset($value['attendance_register']['presant_total']) ? ($value['attendance_register']['presant_total']) : 0;
                    $leavecount[] = isset($value['attendance_register']['leave_total']) ? $value['attendance_register']['leave_total'] : 0;
                    $lopcount[] = isset($value['attendance_register']['lop_total']) ? $value['attendance_register']['lop_total'] : 0;
                    $area_month[] = isset($value['attendance_register']['month_year']) ? $value['attendance_register']['month_year'] : 0;
                }
                $showlinechart = 'Yes';
            } else {
                $precount[] = 0;
                $leavecount[] = 0;
                $lopcount[] = 0;
                $area_month[] = 0;
                $showlinechart = 'No';
            }
            $wrkcount = array_map(function () {
                return array_sum(func_get_args());
            }, $precount, $leavecount, $lopcount);

            $wrkcount_rev = array_reverse($wrkcount);
            $precount_rev = array_reverse($precount);
            $leavecount_rev = array_reverse($leavecount);
            $lopcount_rev = array_reverse($lopcount);
            $area_month_rev = array_reverse($area_month);
            $wrkdays = implode(", ", $wrkcount_rev);
            $chartmonth = implode("','", $area_month_rev);
            $pdays = implode(", ", $precount_rev);
            $ldays = implode(", ", $leavecount_rev);
            $lopdays = implode(", ", $lopcount_rev);

            //            debug($wrkdays);
            if (empty($chartmonth)) {
                $chartmonth = "No Salary To Show";
            }
            $this->set("chartmonth", $chartmonth);
            $this->set("pdays", $pdays);
            $this->set("ldays", $ldays);
            $this->set("lopdays", $lopdays);
            $this->set("wrkdays", $wrkdays);
            $this->set("showlinechart", $showlinechart);
            //            debug($wrkdays);
            //            debug($lopdays);
            //            
            //            debug($areachart);


            $present = $this->EmployeeDetails->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
            $user_id = isset($present['0']['user_credentials']['user_id']) ? $present['0']['user_credentials']['user_id'] : '';
            $present_type = $this->DeviceAttendance->query("SELECT punchtype FROM mob_user_credentials WHERE user_id = '$user_id' ");
            $this->set("punch_type", isset($present_type['0']['mob_user_credentials']['punchtype']) ? $present_type['0']['mob_user_credentials']['punchtype'] : '');
            /* $today_join[] = array(
              'table' => 'emp_details',
              'alias' => 'EmployeeDetails',
              'type' => 'LEFT',
              'foreignKey' => false,
              'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
              );
              $today_join[] = array(
              'table' => 'emp_proff',
              'alias' => 'EmployeeProfessionalDetails',
              'type' => 'LEFT',
                           'foreignKey' => false,
              'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
              );

              $today_cond = array("LOGDATE >=" => date("Y-m-d", strtotime("now")), "DeviceAttendance.status >=" => "Y");
              $today_att = $this->DeviceAttendance->find("all", array(
              "order" => "EmployeeDetails.emp_id ASC,DeviceAttendance.LOGDATE ASC",
              "conditions" => $today_cond,
              'joins' => $today_join,
              "fields" => "DeviceAttendance.*EmployeeDetails.first_name,EmployeeDetails.last_name,if((`DeviceAttendance`.`C3` in ('',NULL)),`DeviceAttendance`.`branch_code`,`DeviceAttendance`.`C3`) AS `Location`"
              )
              );
              $this->set("today_att", $today_att);

              $this_month_cond = array("LOGDATE >=" => date("Y-m-01", strtotime("now")), "LOGDATE <=" => date("Y-m-t", strtotime("now")), "DeviceAttendance.status >=" => "Y");
              $this_month_att = $this->DeviceAttendance->find("all", array(
              "order" => "EmployeeDetails.emp_id,DeviceAttendance.LOGDATE",
              "conditions" => $this_month_cond,
              'joins' => $today_join,
              "fields" => "DeviceAttendance.*,EmployeeDetails.first_name,EmployeeDetails.last_name,if((`DeviceAttendance`.`C3` in ('',NULL)),`DeviceAttendance`.`branch_code`,`DeviceAttendance`.`C3`) AS `Location`"
              )
              );
              $this->set("this_month_att", $this_month_att);

              $last_month_cond = array("LOGDATE >=" => date("Y-m-01", strtotime('-1 months', strtotime("now"))), "LOGDATE <=" => date("Y-m-t", strtotime('-1 months', strtotime("now"))), "DeviceAttendance.status >=" => "Y");
              $last_month_att = $this->DeviceAttendance->find("all", array(
              "order" => "EmployeeDetails.emp_id,DeviceAttendance.LOGDATE",
              "conditions" => $last_month_cond,
              'joins' => $today_join,
              "fields" => "DeviceAttendance.*,EmployeeDetails.first_name,EmployeeDetails.last_name,if((`DeviceAttendance`.`C3` in ('',NULL)),`DeviceAttendance`.`branch_code`,`DeviceAttendance`.`C3`) AS `Location`"
              )
              );
              $this->set("last_month_att", $last_month_att); */
            $table_joins[] = array(
                'table' => 'user_credentials',
                'alias' => 'USC',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
            );
            $table_joins[] = array(
                'table' => 'emp_proff',
                'alias' => 'Empproff',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
            );
            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
            $this->set('arr_employees', $arr_employees);
            $conditions = array(
                //'OR' => array(
                array("DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d')" => date('m-d'), 'status' => 1, 'emp_pkey' => $emp_fkey)
                //array("DATE_FORMAT(Empproff.joining_date,'%m-%d')" => date('m-d'), 'status' => 1),
                //)
            );
            $conditions1 = array(
                array("DATE_FORMAT(Empproff.joining_date,'%m-%d')" => date('m-d'), 'status' => 1, 'emp_pkey' => $emp_fkey)
            );
            //$conditions[] = array("EmployeeDetails.date_of_birth" == date('Y-m-d') or "Empproff.joining_date" == date('Y-m-d'));
            $arr_employees_pics = $this->EmployeeDetails->find("all", array("fields" => array("EmployeeDetails.date_of_birth", "EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar"), "joins" => $table_joins, "conditions" => $conditions));
            $this->set('arr_employees_pics', $arr_employees_pics);

            $arr_employees_work = $this->EmployeeDetails->find("all", array("fields" => array("EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date"), "joins" => $table_joins, "conditions" => $conditions1));
            $this->set('arr_employees_work', $arr_employees_work);
            //            $emp_pkeys = array();
            //            foreach ($arr_employees_pics as $val) {
            //                $emp_pkeys[] = $val['EmployeeDetails']['emp_pkey'];
            //            }
            //            $wish = 0;
            //            if (in_array("$emp_fkey", $emp_pkeys)) {
            //                $wish = 1;
            //            }
            //
            //            $this->set("wish", $wish);
            $arr_empleaverequests = $this->listemployeeleaverequests($emp_fkey);
            $this->set("arr_empleaverequests", $arr_empleaverequests);
            $arr_events = $this->getEvents($emp_fkey);
            $this->set("arr_events", $arr_events);
            $this->set("working_days_counts", $working_days_counts);
            $this->set("leaves_taken_count", $leaves_taken_count);
            $this->set("present_days_count", isset($present_days_count) ? round($present_days_count, 1) : 0);
            $this->set("shiftpolicy", $shiftpolicy);
            $this->set("empinfo", $empinfo);
            $this->set("pr_count", $pr_count);
            $this->set("lastpunch", $lastpunch);
            $this->set("absentdays", $absentdays);
            $this->set("misspunch", $misspunch);

            $general_settings = $this->DeviceAttendance->query("SELECT COUNT(*) AS count FROM genaral_setings WHERE when_itis='Employee_Login' AND status=1");
            //debug($general_settings);   exit();            
            if ($general_settings[0][0]['count']) {
                $settings_runner = $this->DeviceAttendance->query("select count(*) as count from settings_runner where emp_fkey=$emp_fkey and exit_status IN ('Finished','Never')");
                $this->set("settings_runner", $settings_runner[0][0]['count']);
            }
            //            debug($misspunch);
            $this->render('empdashboard');
        } else {
            $this->Session->destroy();
            $this->redirect("/");
        }
    }

    // public function locationtracking()
    // {
    //     $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');
    //     $emp_fkey = $this->Session->read('emp_fkey');
    //     $to = date("Y-m-d");
    //     $from_dates = isset($to) ? $to . ' 00:00' : '';
    //     $to_dates = isset($to) ? $to . ' 23:59' : '';
    //     $get_user_id = $this->MobileUserTracking->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
    //     $user_id = isset($get_user_id['0']['user_credentials']['user_id']) ? $get_user_id['0']['user_credentials']['user_id'] : '';
    //     $arr_location = $this->MobileUserTracking->query("SELECT * FROM `mob_user_tracking` WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");
    //     $this->set("arr_location", $arr_location);
    // }

    public function issuereport($issuedate = 0)
    {
        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        // $arr_issues = $this->Earlyin->query("select * from mob_report ");
        //edited by sinsiya on 18-06-2024
        $arr_issues = $this->Earlyin->query("
            SELECT 
                mob_report.*, 
                employee_info.employee_id, 
                employee_info.EmpName,
                branches.branch_name
            FROM 
                mob_report
            INNER JOIN 
                user_credentials ON mob_report.user_id = user_credentials.user_id
            INNER JOIN 
                employee_info ON user_credentials.emp_fkey = employee_info.emp_pkey
            INNER JOIN 
                branches ON employee_info.branch_code = branches.branch_code
            ORDER BY 
                mob_report.mob_report_pkey DESC    
        ");
        //edited by anukrishnan 28-01-2025
        $emp_fkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        if ($user_group == '2') {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $emp_fkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            $branch_condition = "";
            if ($is_ho != 1) {
                $branch_condition = "WHERE employee_info.branch_code = '$is_ho'";
            }
            $arr_issues = $this->Earlyin->query("
                SELECT 
                    mob_report.*, 
                    employee_info.employee_id, 
                    employee_info.EmpName,
                    branches.branch_name
                FROM 
                    mob_report
                INNER JOIN 
                    user_credentials ON mob_report.user_id = user_credentials.user_id
                INNER JOIN 
                    employee_info ON user_credentials.emp_fkey = employee_info.emp_pkey
                INNER JOIN 
                    branches ON employee_info.branch_code = branches.branch_code
                    $branch_condition
                ORDER BY  employee_info.EmpName
                        
            ");
        }
        //close
        if ($issuedate != '') {
            // $arr_issues = $this->Earlyin->query("select * from mob_report ");
            $arr_issues = $this->Earlyin->query("
                SELECT 
                    mob_report.*, 
                    employee_info.employee_id, 
                    employee_info.EmpName,
                    branches.branch_name
                FROM 
                    mob_report
                INNER JOIN 
                    user_credentials ON mob_report.user_id = user_credentials.user_id
                INNER JOIN 
                    employee_info ON user_credentials.emp_fkey = employee_info.emp_pkey
                INNER JOIN 
                    branches ON employee_info.branch_code = branches.branch_code
                ORDER BY 
                    employee_info.EmpName 
            ");
        }
        $arr_resp = array(
            'data' => array()
        );


        $i = 0;
        $j = 1;
        foreach ($arr_issues as $key => $att) {
            // debug($att); edited by sinsiya 13-06-2024
            $arr_resp['data'][$i][] = $j;
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att['employee_info']['EmpName']) ? $att['employee_info']['EmpName'] : '';
            $arr_resp['data'][$i][]/* ['employee_id'] */ = isset($att['employee_info']['employee_id']) ? $att['employee_info']['employee_id'] : '';
            $arr_resp['data'][$i][]/* ['employee_id'] */ = isset($att['branches']['branch_name']) ? $att['branches']['branch_name'] : '';
            // $date = isset($att['mob_report']['date_rep']) ? $att['mob_report']['date_rep'] : '';
            //$formatted_date = date('d-m-y', strtotime($date));
            $arr_resp['data'][$i][]/* ['date'] */ = isset($att['mob_report']['date_rep']) ? date('d-m-Y H:i:s', strtotime($att['mob_report']['date_rep'])) : '';
            // $arr_resp['data'][$i][]/* ['date'] */ = isset($att['mob_report']['date_rep']) ? $att['mob_report']['date_rep'] : '';
            $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att['mob_report']['report_type']) ? $att['mob_report']['report_type'] : '';
            //          $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att['mob_report']['user_id']) ? $att['mob_report']['user_id'] : '';
            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att['mob_report']['remarks']) ? $att['mob_report']['remarks'] : '';
            $arr_resp['data'][$i][]/* ['C1'] */ = isset($att['mob_report']['location']) ? $att['mob_report']['location'] : '';



            $i++;
            $j++;
        }
        return json_encode($arr_resp);
    }

    public function loadmap()
    {
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');

        $emp_fkey = $this->Session->read('emp_fkey');
        //        $from = date('Y-m-01');
        $to = date("Y-m-d");
        $from_dates = isset($to) ? $to . ' 00:00' : '';
        $to_dates = isset($to) ? $to . ' 23:59' : '';
        if ($emp_fkey == 0) {
            echo "NO Emp";
            return false;
        }
        $get_user_id = $this->MobileUserTracking->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
        $user_id = isset($get_user_id['0']['user_credentials']['user_id']) ? $get_user_id['0']['user_credentials']['user_id'] : '';

        if ($user_id == '') {
            echo "NO user_id";
            return FALSE;
        }
        $arr_location = $this->MobileUserTracking->query("SELECT * FROM `mob_user_tracking` WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");

        $location = array();
        foreach ($arr_location as $key => $val) {

            $location[] = array($val['mob_user_tracking']['location'], $val['mob_user_tracking']['latitude'], $val['mob_user_tracking']['longitude']);
        }
        //                            debug($location);
        if (!empty($location)) {
            foreach ($location as $value) {
                $lat[] = isset($value['1']) ? $value['1'] : '9.96';
                $long[] = isset($value['2']) ? $value['2'] : '76.28';
                $minlat = min($lat);
                $maxlong = max($long);
            }
        } else {
            $minlat = 9.96;
            $maxlong = 76.28;
        }
        $this->set("minlat", $minlat);
        $this->set("maxlong", $maxlong);

        $arr_location_updates = $this->MobileUserTracking->query("SELECT employee_info.*,mob_user_tracking.* FROM `mob_user_tracking` INNER join employee_info on (employee_info.emp_pkey = $emp_fkey) WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");

        $this->set("arr_location", $arr_location_updates);

        $this->set("datas", json_encode($location));
    }

    public function timers()
    {
        $this->autoRender = false;
        $emp_fkey = $this->Session->read('emp_fkey');
        date_default_timezone_set("Asia/Kolkata");
        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $t = time();
        $now = date("h:i:sa");
        $today = date('Y-m-d H:i:s');
        //debug($today);
        //        $sessions_start = $this->Earlyin->query("select c1,min(LOGDATE) as starts from device_attandance left join emp_details as emps on(emps.emp_id = device_attandance.emp_id) where emps.emp_pkey = '$emp_fkey' and  lcase(c1)='in'  and DATE_FORMAT(LOGDATE,'%y-%m-%d') ");
        //        debug($sessions_start);
        //        if($sessions_start['0']['0']['starts'] == null)
        //        {
        //            $sessions_start1 = $this->Earlyin->query("select c1,max(LOGDATE) as starts from device_attandance left join emp_details as emps on(emps.emp_id = device_attandance.emp_id) where emps.emp_pkey = '$emp_fkey' and DATE_FORMAT(LOGDATE,'%y-%m-%d-1') ");
        //            if($sessions_start1['0']['0']['starts'] != NULL && $sessions_start1['0']['device_attandance']['c1'] == 'in')
        //            {
        //                $session_started = $sessions_start1['0']['0']['starts'];
        //            }
        //            else
        //            {
        //                $session_started = null;
        //            }
        //        }
        //        $sessions_end = $this->Earlyin->query("select max(LOGDATE) as starts from device_attandance left join emp_details as emps on(emps.emp_id = device_attandance.emp_id) where emps.emp_pkey = '$emp_fkey' and  lcase(c1)='out'  and DATE_FORMAT(LOGDATE,'%y-%m-%d') ");
        //        debug($sessions_end);
        $sessions_start = $this->Earlyin->query("  select min(LOGDATE) vLOGDATE_intime
                  from device_attandance att,emp_details ed      
                    where att.emp_id=ed.emp_id 
                   and att.company_code=ed.company_code 
                 and lcase(c1)='in' and emp_pkey=$emp_fkey and ucase(att.status)='Y'
                and (LOGDATE,att.emp_id)
                 in (select min(LOGDATE) ,emp_id from device_attandance where 
                 lcase(c1)='in' and ucase(status)='Y'and date_format(LOGDATE,'%Y-%m-%d') >=
      date_format(current_date,'%Y-%m-%d') group by emp_id);");
        $start_date = $sessions_start['0']['0']['vLOGDATE_intime'];
        if ($start_date == NULL) {
            $set = 0;
            $t_run = 1;
        } else {
            $arr_session = $this->Earlyin->query("select ed.emp_pkey,    ed.first_name,      att.logdate,    att.C1

from device_attandance att, emp_details ed
WHERE 
                        att.emp_id = ed.emp_id and ucase(att.status)='Y'
                        AND ed.emp_pkey =  $emp_fkey
                        AND DATE_FORMAT(att.logdate,'%Y-%m-%d %T') BETWEEN 
                            DATE_FORMAT('$start_date', '%Y-%m-%d %T') AND DATE_FORMAT(CURRENT_TIMESTAMP(), '%Y-%m-%d %T');");
            $set = 0;
            $t_run = 1;
            //debug($arr_session);
            if (empty($arr_session)) {
                $stat = date_create(date($today));
                $en = date_create(date($start_date));
                $diff = date_diff($stat, $en);
                $hours = $diff->h * 60;
                $minuites = $diff->i + $hours;
                $set = $minuites;
                //debug($set);
            } else {
                $timers = array();
                foreach ($arr_session as $key => $value) {
                    if ($value['att']['C1'] == 'in' || $value['att']['C1'] == 'IN') {

                        if (isset($arr_session[$key + 1]['att']['logdate']) && strtolower($arr_session[$key + 1]['att']['C1']) == 'out') {
                            $starter = $arr_session[$key + 1]['att']['logdate'];
                            $ens = $value['att']['logdate'];
                            $stat = date_create(date($starter));
                            $en = date_create(date($ens));
                            $diff = date_diff($stat, $en);
                            $hours = $diff->h * 60;
                            $minuites = $diff->i + $hours;
                            $set = $set + $minuites;
                            $t_run = 0;
                        } else if (!isset($arr_session[$key + 1])) {
                            $ens = $value['att']['logdate'];
                            $stat = date_create(date($today));
                            $en = date_create(date($ens));
                            $diff = date_diff($stat, $en);
                            $hours = $diff->h * 60;
                            $minuites = $diff->i + $hours;
                            $set = $set + $minuites;
                            $t_run = 1;
                        } else {
                            $t_run = '';
                        }
                        //debug($set);
                    }
                }
            }
        }
        $this->set("set", $set);
        echo json_encode(array('set' => $set, 'run' => $t_run));
    }

   public function form($day = '', $day2 = 0)
    {
        //   debug($day);
        //    debug($day2);
        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $this->Latein->useDbConfig = $this->Session->read('ds');
        $this->Earlyout->useDbConfig = $this->Session->read('ds');
        $this->Lateout->useDbConfig = $this->Session->read('ds');

        $time = date("Y-m-d");
        $from = date('Y-m-1', strtotime($time));
        $to = date('Y-m-t', strtotime($time));
        $lmonth = date("Y-m-01", strtotime('-1 months', strtotime("now")));
        // debug($lmonth);
        $ltomonth = date("Y-m-t", strtotime('-1 months', strtotime("now")));
        //  debug($ltomonth);
//edited by athira on 20-08-2025
        switch ($day2) {
            case 'Daily':
        $condition = "
            WHERE emp_early_in.LOGDATE >= '{$time} 00:00:00' 
              AND emp_early_in.LOGDATE <= '{$time} 23:59:59'
        ";
        break;

    case 'Month':
        $condition = "
            WHERE emp_early_in.LOGDATE >= '{$from} 00:00:00' 
              AND emp_early_in.LOGDATE <= '{$to} 23:59:59'
        ";
        break;

    case 'LastMonth':
        $condition = "
            WHERE emp_early_in.LOGDATE >= '{$lmonth} 00:00:00' 
              AND emp_early_in.LOGDATE <= '{$ltomonth} 23:59:59'
        ";
        break;
            default:
                echo "No criterias found";
                break;
        }
          
        switch ($day) {
            case 'Earlyin':
                $results = $this->Earlyin->query("select emp_early_in.* from emp_early_in as emp_early_in $condition ORDER BY emp_early_in.EmpName ASC");
                break;
            case 'Latein':
                $results = $this->Latein->query("select emp_early_in.* from emp_late_in as emp_early_in $condition ORDER BY emp_early_in.EmpName ASC");
                break;
            case 'Earlyout':
                $results = $this->Earlyout->query("select emp_early_in.* from emp_early_out as emp_early_in $condition ORDER BY emp_early_in.EmpName ASC");
                break;
            case 'Lateout':
                $results = $this->Lateout->query("select emp_early_in.* from emp_late_out as emp_early_in $condition ORDER BY emp_early_in.EmpName ASC");
                break;
            default:
                echo "No criterias found";
                break;
        }

        //end

        // debug($results);
        $this->set("results", $results);
        $this->render('form');
    }

    public function empform($day = '', $day2 = 0)
    {
        //   debug($day);
        //    debug($day2);
        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $this->Latein->useDbConfig = $this->Session->read('ds');
        $this->Earlyout->useDbConfig = $this->Session->read('ds');
        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
        $time = date("Y-m-d");
        $from = date('Y-m-1', strtotime($time));
        $to = date('Y-m-t', strtotime($time));
        $lmonth = date("Y-m-01", strtotime('-1 months', strtotime("now")));
        // debug($lmonth);
        $ltomonth = date("Y-m-t", strtotime('-1 months', strtotime("now")));
        //  debug($ltomonth);

        switch ($day2) {
            case 'Daily':
                $condition = "where emp_early_in.LOGDATE >= '$time' ";
                break;
            case 'Month':
                $condition = "where emp_early_in.LOGDATE between '$from' and '$to'";
                break;
            case 'LastMonth':
                $condition = "where emp_early_in.LOGDATE between '$lmonth' and '$ltomonth' ";
                break;

            default:
                echo "No criterias found";
                break;
        }

        switch ($day) {
            case 'Earlyin':
                $results = $this->Earlyin->query("select emp_early_in.* from emp_early_in as emp_early_in $condition  and emp_pkey = $emp_fkey");
                break;
            case 'Latein':
                $results = $this->Latein->query("select emp_early_in.* from emp_late_in as emp_early_in $condition and emp_pkey = $emp_fkey");
                break;
            case 'Earlyout':
                $results = $this->Earlyout->query("select emp_early_in.* from emp_early_out as emp_early_in $condition and emp_pkey = $emp_fkey");
                break;
            case 'Lateout':
                $results = $this->Lateout->query("select emp_early_in.* from emp_late_out as emp_early_in $condition and emp_pkey = $emp_fkey");
                break;
            default:
                echo "No criterias found";
                break;
        }

        // debug($results);
        $this->set("results", $results);
        $this->render('form');
    }

    public function form2($day = '', $day2 = 0)
    {
        //   debug($day);
        //    debug($day2);
        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $this->Latein->useDbConfig = $this->Session->read('ds');
        $this->Earlyout->useDbConfig = $this->Session->read('ds');
        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
        $time = date("Y-m-d");
        $from = date('Y-m-1', strtotime($time));
        $to = date('Y-m-t', strtotime($time));
        $lmonth = date("Y-m-01", strtotime('-1 months', strtotime("now")));
        // debug($lmonth);
        $ltomonth = date("Y-m-t", strtotime('-1 months', strtotime("now")));
        //  debug($ltomonth);
        $join = "join emp_proff as ep on(ep.emp_fkey = emp_early_in.emp_pkey)";
        switch ($day2) {
            case 'Daily':
                $condition = "where emp_early_in.LOGDATE >= '$time' and ep.attr1 = $emp_fkey ";
                break;
            case 'Month':
                $condition = "where emp_early_in.LOGDATE between '$from' and '$to' and ep.attr1 = $emp_fkey ";
                break;
            case 'LastMonth':
                $condition = "where emp_early_in.LOGDATE between '$lmonth' and '$ltomonth' and ep.attr1 = $emp_fkey ";
                break;

            default:
                echo "No criterias found";
                break;
        }

        switch ($day) {
            case 'Earlyin':
                $results = $this->Earlyin->query("select emp_early_in.* from emp_early_in as emp_early_in $join $condition ");
                break;
            case 'Latein':
                $results = $this->Latein->query("select emp_early_in.* from emp_late_in as emp_early_in $join $condition ");
                break;
            case 'Earlyout':
                $results = $this->Earlyout->query("select emp_early_in.* from emp_early_out as emp_early_in $join $condition and emp_pkey = $emp_fkey");
                break;
            case 'Lateout':
                $results = $this->Lateout->query("select emp_early_in.* from emp_late_out as emp_early_in $join $condition and emp_pkey = $emp_fkey");
                break;
            default:
                echo "No criterias found";
                break;
        }

        // debug($results);
        $this->set("results", $results);
        $this->render('form');
    }

    //The below function is to set Emergency contact number of an employee. By ***ARUL P DAS on 14_8_2020
    public function general_setting()
    {
        $this->SettingsRunner->useDbConfig = $this->Session->read('ds');
        $this->GeneralSettings->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
        $time = date("Y-m-d");
        $from = date('Y-m-1', strtotime($time));
        $to = date('Y-m-t', strtotime($time));
        $lmonth = date("Y-m-01", strtotime('-1 months', strtotime("now")));
        // debug($lmonth);
        $ltomonth = date("Y-m-t", strtotime('-1 months', strtotime("now")));
        //  debug($ltomonth);
        $general_settings = $this->GeneralSettings->query("SELECT * FROM genaral_setings WHERE when_itis='Employee_Login' AND status=1");
        $this->set("general_settings", $general_settings);
        $emp_family_details = $general_settings = $this->GeneralSettings->query("SELECT emp_family_pkey,name,relation,contact_number,alternate_number FROM `emp_family` WHERE `emp_fkey` = $emp_fkey AND status=1 ORDER BY `emp_family_pkey`");
        $this->set("family_details", $emp_family_details);
        $this->render('generalsettings');
    }

    //The below function is to save Emergency contact number of an employee. By ***ARUL P DAS on 14_8_2020
    public function save_emergency_contact($family_key, $settings_fkey)
    {
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->SettingsRunner->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $resp = array();
        $resp["success"] = false;
        if ($this->SettingsRunner->query("UPDATE emp_family set emergency_contact = 'N' where emp_fkey in('$emp_fkey') ")) {
            $resp["success"] = true;
        }
        if ($this->SettingsRunner->query("UPDATE emp_family set emergency_contact = 'Y' where emp_family_pkey = '$family_key' ")) {
            $resp["success"] = true;
        }

        $exist_check = $this->SettingsRunner->query("select * from settings_runner where emp_fkey=$emp_fkey");
        if (isset($exist_check[0]['settings_runner'])) {
            $updated = $exist_check[0]['settings_runner']['updated_times'] + 1;
            $this->SettingsRunner->query("update settings_runner set exit_status='Finished',updated_times=$updated,modification_date=now() where emp_fkey=$emp_fkey and settings_fkey=$settings_fkey");
            $resp["success"] = true;
        } else {
            $this->SettingsRunner->query("insert into settings_runner(settings_fkey,emp_fkey,exit_status,updated_times) values($settings_fkey,$emp_fkey,'Finished',0)");
            $resp["success"] = true;
        }

        $resp["msg"] = "save success";
        echo json_encode($resp);
    }

    //The below function is to set never remaind Emergency contact number pop up modal. By ***ARUL P DAS on 14_8_2020
    public function never_remind_emergency($settings_fkey)
    {
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->SettingsRunner->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $resp = array();
        $resp["success"] = false;
        $exist_check = $this->SettingsRunner->query("select * from settings_runner where emp_fkey=$emp_fkey");
        //        debug($exist_check);
        if (isset($exist_check[0]['settings_runner'])) {
            $updated = $exist_check[0]['settings_runner']['updated_times'] + 1;
            $this->SettingsRunner->query("update settings_runner set exit_status='Never',updated_times=$updated,modification_date=now() where emp_fkey=$emp_fkey and settings_fkey=$settings_fkey");
            $resp["success"] = true;
        } else {
            $this->SettingsRunner->query("insert into settings_runner(settings_fkey,emp_fkey,exit_status,updated_times) values($settings_fkey,$emp_fkey,'Never',0)");
            $resp["success"] = true;
        }
        $resp["msg"] = "save success";
        echo json_encode($resp);
    }

    //The below function is to set remaind later Emergency contact number pop up modal. By ***ARUL P DAS on 14_8_2020
    public function later_remind_emergency($settings_fkey)
    {
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->SettingsRunner->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $resp = array();
        $resp["success"] = false;
        $exist_check = $this->SettingsRunner->query("select * from settings_runner where emp_fkey=$emp_fkey");
        if (isset($exist_check[0]['settings_runner'])) {
            $updated = $exist_check[0]['settings_runner']['updated_times'] + 1;
            $this->SettingsRunner->query("update settings_runner set exit_status='Next_time',updated_times=$updated,modification_date=now() where emp_fkey=$emp_fkey and settings_fkey=$settings_fkey");
            $resp["success"] = true;
        } else {
            $this->SettingsRunner->query("insert into settings_runner(settings_fkey,emp_fkey,exit_status,updated_times) values($settings_fkey,$emp_fkey,'Next_time',0)");
            $resp["success"] = true;
        }
        $resp["msg"] = "save success";
        echo json_encode($resp);
    }

    public function share($customerId, $recipeId)
    {
        //action logic goes here..
    }

    //added by megha on 25/11/2019 presenttodayall list
    public function presenttoday()
    {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');
        $company_code = strtoupper($this->Session->read('company_code')); //Edited by Akshay on 13-5-2024
        $today = date("Y-m-d");
        if ($user_group == 2) {
            $emp_fkey = $this->Session->read('emp_fkey');
            // $resultsall = $this->Lateout->query("select *, ei.emp_id, ei.EmpName, ei.employee_id from `present_today_all` left join `employee_info` ei on present_today_all.emp_pkey = ei.emp_pkey where emp_pkey in (select emp_fkey from emp_proff where attr1 ='$emp_fkey' or emp_fkey = '$emp_fkey' ) and STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' ");
            $resultsall = $this->Lateout->query("select *, ei.emp_id, ei.EmpName, ei.employee_id from `present_today_all` left join `employee_info` ei on present_today_all.emp_pkey = ei.emp_pkey where present_today_all.emp_pkey in (select emp_fkey from emp_proff where attr1 ='$emp_fkey' or emp_fkey = '$emp_fkey' ) and STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' ORDER BY ei.EmpName ASC");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $emp_fkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $resultsall = $this->Lateout->query("SELECT *, ei.emp_id, ei.EmpName, ei.employee_id 
                                FROM `present_today_all` 
                                LEFT JOIN `employee_info` ei 
                                ON present_today_all.emp_pkey = ei.emp_pkey 
                                WHERE STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' 
                                AND ei.branch_code = '$is_ho' ORDER BY ei.EmpName ASC");
            } else {
                $resultsall = $this->Lateout->query("select * from present_today_all where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' ");
            }
        } else {
            //edited by megha on 10-04-2024
            // $resultsall = $this->Lateout->query("select * from present_today_all");
            //Edited by Akshay on 13 -5 2024
            //if ($company_code == 'DEMO') {
            $resultsall = $this->Lateout->query("select * from present_today_all where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' ");
            //            } else {
            //                $resultsall = $this->Lateout->query("select * from present_today_all");
            //            }
            //End
            //  . "where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today'");
            //$resultsall = $this->Lateout->query("select *, ei.emp_id, ei.EmpName, ei.employee_id from `present_today_all` left join `employee_info` ei on present_today_all.emp_pkey = ei.emp_pkey  where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today'");
        }
        // $startdate = $this->Lateout->query("select start_month, end_month  from fin_year where Year_status='OPEN' and vattr1=0 and status = 1 and is_current_finyear='Y'
        // and branch_code = (select branch_code from emp_details where emp_pkey='407')  order by start_month desc limit 1");
        // debug($startdate);
        $this->set("presentall", $resultsall);

        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $time = strtotime($date_time);
        $month_year = date("d F Y", $time);
        $this->set('month_year', $month_year);
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
    }

    public function activetoday()
    {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $emp_fkey = $this->Session->read('emp_fkey');
            $today = $this->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
            $emps = array();
            foreach ($today as $key => $att) {
                $emps[] = $att['a']['emp_fkey'];
            }
            $str = implode("','", $emps);
            $results = $this->Lateout->query(
                "select *,ei.emp_id ,ei.EmpName,ei.employee_id from present_today left join employee_info ei "
                    . "ON present_today.emp_pkey = ei.emp_pkey where present_today.emp_pkey in ('$str') ORDER BY ei.EmpName"
            );

            // edited by anukrishnan_28-01-2025 open
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $emp_fkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $results = $this->Lateout->query("select *,ei.emp_id,ei.EmpName,ei.employee_id from `present_today` left join employee_info ei ON present_today.emp_pkey = ei.emp_pkey WHERE ei.branch_code = '$is_ho' ORDER BY ei.EmpName");
            } else {
                $results = $this->Lateout->query("select *,ei.emp_id,ei.EmpName,ei.employee_id from `present_today` left join employee_info ei ON present_today.emp_pkey = ei.emp_pkey ORDER BY ei.EmpName");
            }
            // edited by anukrishnan_28-01-2025 close
        } else {
            $results = $this->Lateout->query("select *,ei.emp_id,ei.EmpName,ei.employee_id from `present_today` left join employee_info ei ON present_today.emp_pkey = ei.emp_pkey ORDER BY ei.EmpName");
        }

        $this->set("results", $results);
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $time = strtotime($date_time);
        $month_year = date("d F Y", $time);
        $this->set('month_year', $month_year);
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
    }

    public function absenttoday($mode = 0)
    {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        
        $todaydate = date("Y-m-d");
        $results = $this->Lateout->query("SELECT * FROM present_today_all WHERE DATE(LOGDATE) = '{$todaydate}'"); //edited by anukrishnan_10-02-2025
        // var_dump($results);
        /* server timezone */
        $timezone = new DateTimeZone("Asia/Kolkata");
        $date = new DateTime();
        $date->setTimezone($timezone);
        $dates = $date->format('d-m-Y H:i a');

        $empsin = array('0');
        foreach ($results as $val) {
            $empsin[] = $val['present_today_all']['emp_pkey'];
        }
        $emps = implode(',', $empsin);
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $emp_fkey = $this->Session->read('emp_fkey');
            $today = $this->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
            $empstr = array();
            foreach ($today as $key => $att) {
                $empstr[] = $att['a']['emp_fkey'];
            }
            $str = implode("','", $empstr);
            //$results1 = $this->Lateout->query("select first_name,last_name from emp_details where emp_pkey not in($emps) and emp_pkey in (select emp_fkey from emp_proff where  emp_fkey = '$emp_fkey' or attr1 ='$emp_fkey' )  and status = 1");
            $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and emp_pkey in ('$str') and status = 1 ORDER BY first_name");
            // edited by anukrishnan_28-01-2025 open
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $emp_fkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1 AND emp_details.branch_code = '$is_ho' ORDER BY first_name");
            } else {
                $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1 ORDER BY first_name");
            }
            // edited by anukrishnan_28-01-2025 close
        } else {
            $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1 ORDER BY first_name");
        }
           

        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $time = strtotime($date_time);
        $month_year = date("d F Y", $time);
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $this->set('month_year', $month_year);

        $this->set("results1", $results1);
        if ($mode) {


            $content = '<style>
td,th
{
    text-align: left;
}
</style>
                <h2>Absent Employees on ' . $dates . '</h2><hr>
                ';
            $content .= '<div style="text-align:center;">';
            $content .= '<table>';
            $content .= '<thead>
                              <tr>
                              <th>Sl. No.</th>
                                  <th>Employee Name</th>
                              </tr>
                            </thead>';
            $content .= '<tbody>';
            $content .= '';
            $i = 0;
            foreach ($results1 as $val) {
                $i++;
                $content .= '<tr><td>' . $i . '</td><td>' . $val['emp_details']['first_name'] . ' ' . $val['emp_details']['last_name'] . ' - ' . $val['emp_proff']['emp_company_id'];

                $content .= '</td></tr>';
            }
            $content .= '</tbody></table></div>';
            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
            try {
                $html2pdf = new HTML2PDF('P', 'Legal', 'en');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->writeHTML($content);
                $html2pdf->Output('absenttoday.pdf', 'D');
            } catch (HTML2PDF_exception $e) {
                echo $e;
                exit;
            }
        }
    }

    public function download()
    {

        $content = ob_get_clean();
        $view = new View($this, false);
        $view_output = $view->render('download');
        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
        try {
            $html2pdf = new HTML2PDF('P', 'Legal', 'en');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($content);
            $html2pdf->Output('absenttoday.pdf', 'D');
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
        $this->render('download');
        //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
        //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
    }

    public function search($query)
    {
        //action logic goes here..
    }

    public function gettodayattendace()
    {
        $this->autoRender = false;
    }

    public function getMenus()
    {
        $resp_menu = array();

        $user_group = $this->Session->read('user_group');
        $this->set('user_group', $user_group);

        $context = isset($_REQUEST["context"]) ? $_REQUEST["context"] : "main";
        $root = isset($_REQUEST["root"]) ? $_REQUEST["root"] : 0;

        $conditions = array('active = "Y" ');

        // Edited by Akshay on 3-2-2025
        $user_group = $this->Session->read("user_group");
        $company_code = $this->Session->read('company_code');
        if ($user_group == '2' || $company_code == 'GLET' || $company_code == 'ABSG' || $company_code == 'DEMO') {
            $conditionss = array('Useraccess.active = "Y" and EmployeeMenu.active = "Y" and EmployeeMenu.is_default != "M" and EmployeeMenu.menu_id < 1175');
        } else {
            $conditionss = array('Useraccess.active = "Y" and EmployeeMenu.active = "Y" and EmployeeMenu.is_default != "M"');
        }
        // End

        if ($context == "sub") {
            $conditions['parent_id'] = $root;
        }
        if ($user_group == 2) {
            /*
             * Employee Menu
             * Added by santhosh on 14 March 2015
             */
            $emp_fkey = $this->Session->read('emp_fkey');
            $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
            $menudb = $this->EmployeeMenu->find(
                "all",
                array(
                    'joins' => array(
                        array(
                            'table' => 'user_access',
                            'alias' => 'Useraccess',
                            'type' => 'INNER',
                            'conditions' => array(
                                'EmployeeMenu.menu_id = Useraccess.menu_id'
                            )
                        )
                    ),
                    'conditions' => array(
                        $conditionss,
                        'Useraccess.user_fkey' => $emp_fkey
                    ),
                    'order' => array('EmployeeMenu.menu_id ASC')
                )
            );

            $menu = array();
            //debug($menudb); 
            //   $menudb = $this->EmployeeMenu->find("all", array("conditions" => $conditions));
            //  debug($menudb);
            /* $arr_mastermenu = array();
              $arr_sitemanagementmenu = array();
              $arr_transmenu = array();
              $arr_reportsmenu = array();
              $arr_securitymenu = array();
              foreach ($menudb as $key => $value){
              switch ($value['parent_id']) {
              case 1:
              $arr_mastermenu[] = $value;
              break;
              case 2:
              $arr_sitemanagementmenu[] = $value;
              break;
              case 3:
              $arr_transmenu[] = $value;
              break;
              case 4:
              $arr_reportsmenu[] = $value;
              break;
              case 5:
              $arr_securitymenu[] = $value;
              break;
              default:
              break;
              }
              }
              $this->set('arr_mastermenu', $arr_mastermenu);
              $this->set('arr_sitemanagementmenu', $arr_sitemanagementmenu);
              $this->set('arr_transmenu', $arr_transmenu);
              $this->set('arr_reportsmenu', $arr_reportsmenu);
              $this->set('arr_securitymenu', $arr_securitymenu); */




            foreach ($menudb as $key => $value) {
                $m = $value['EmployeeMenu'];
                $arr_menu = array();
                $arr_menu["id"] = $m["menu_id"];
                // $m["menu_url"];
                $arr_menu["url"] = $m["menu_url"];
                $arr_menu["text"] = $m["menu_title"];
                $arr_menu["iconCls"] = $m["iconCls"];
                $arr_menu["leaf"] = true;
                if (isset($menu[$m['parent_id']])) {
                    $menu[$m['parent_id']]["leaf"] = false;
                    $menu[$m['parent_id']]['children'][] = $arr_menu;
                } else {
                    $menu[$m['menu_id']] = $arr_menu;
                }
            }
            //debug($arr_menu);
        } else {
            //Admin menu
            $this->Menu->useDbConfig = $this->Session->read('ds');
            //edited by athira on 04-02-2025
            $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
            $plan = $plan['0']['comp_contact_info']['plan'];
            //$this->Menu->recover('tree');
            if ($plan == 'basic') {
                $conditions['Menu.plan'] = 'basic';
            } else {
                $menudb = $this->Menu->find("all", array("conditions" => $conditions));
            }

            $menudb = $this->Menu->find("all", array("conditions" => $conditions));

            //end
            // debug($menudb);

            $menu = array();

            foreach ($menudb as $key => $value) {
                $m = $value['Menu'];
                $arr_menu = array();
                $arr_menu["id"] = ($context == "main") ? $m["menu_id"] : (($m["menu_url"]) ? $m["menu_url"] : "none_" . $m["menu_id"]);
                $arr_menu["url"] = $m["menu_url"];
                $arr_menu["text"] = $m["menu_title"];
                $arr_menu["iconCls"] = isset($m["iconCls"]) ? $m["iconCls"] : "fa fa-user";
                $arr_menu["leaf"] = true;
                // "xf007@FontAwesome";

                if (isset($menu[$m['parent_id']])) {
                    //$menu[$m['parent_id']]["leaf"] = false;
                    $menu[$m['parent_id']]['children'][] = $arr_menu;
                } else {
                    $arr_menu["leaf"] = true;
                    $menu[$m['menu_id']] = $arr_menu;
                }
            }
        }

        //   $resp_menu["success"] = true;
        $i = 0;
        if ($context == "main") {
            foreach ($menu as $key => $value) {
                $resp_menu[$i++] = $value;
            }
        } else {
            foreach ($menu as $key => $value) {
                $resp_menu[$i++] = $value;
            }
        }
        //	debug($resp_menu);
        if ($context == "main") {
            //echo json_encode($resp_menu);
            //debug($resp_menu);
            //$conditions['parent_id'] = $root;
        } else if ($root) {
            //echo json_encode($resp_menu);
            //debug($resp_menu);
        }

        return $resp_menu;
    }

    public function listemployeeleaverequestscounts($emp_fkey = 0)
    {

        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $fields = 'count(*) as counts';
        $joins = array(
            array(
                'table' => 'salary_head_items',
                'alias' => 'SalaryHeadItems',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeaveRequests.EMP_fkey = EmployeeDetails.emp_pkey')
            )
        );
        $conditions = array();
        if ($emp_fkey != 0) {
            $conditions = array(
                'OR' => array(
                    array('ISAutherizedby' => $emp_fkey, 'LEAVESTATUS IN("Applied","Authorized")'),
                    array('APPROVEDBY' => $emp_fkey, 'LEAVESTATUS IN ("Authorized","Approved","Rejected")'),
                )
            );
        }

        $arr_empleaverequests = $this->LeaveRequests->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions
        ));
        return isset($arr_empleaverequests) ? $arr_empleaverequests['0']['0']['counts'] : 0;
    }

    public function misspunch($emp_fkey = 0)
    {
        $arr_empmisspunches = $this->listemployeemisspunches();
        // debug($arr_empmisspunches);exit;
        //            $this->set("arr_empmisspunches", $arr_empmisspunches);
        $this->set("results", $arr_empmisspunches);
    }

    public function listemployeeleaverequests($emp_fkey = 0)
    {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        //edited by sinsiya to join the table employee_info 
        $fields = 'LEAVEENTRYID, CONCAT(first_name, " ", last_name) AS emp_name,emp_info.employee_id,Branch.branch_name,Branch.branch_code,SalaryHeadItems.item as leave_type,applied_date,FROMDATE,TODATE,LEAVESTATUS';

        $joins = array(
            array(
                'table' => 'salary_head_items',
                'alias' => 'SalaryHeadItems',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeaveRequests.EMP_fkey = EmployeeDetails.emp_pkey')
            ),
            array(
                'table' => 'employee_info',
                'alias' => 'emp_info',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeaveRequests.EMP_fkey = emp_info.emp_pkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.branch_code = Branch.branch_code')
            )

        );

    //     $today = date('Y-m-d');
    //    $startDate = date('Y-m-01');         // First day of current month
    //         $endDate = date('Y-m-t');
    //     // Default condition: applied leave within date range
    //     $conditions = [
    //         'FROMDATE >=' => $startDate,
    //         'TODATE <=' => $endDate,
    //         'LEAVESTATUS' => 'Applied'
    //     ];
 //edited by athira on 14-10-2025
        // $today = date('Y-m');
        // $today_date=$today.'-01';
        // $att_startdate = $this->LeaveRequests->query("select att_start_end_fn('$today_date', 1) as monthly_att_fromdate");
        // $att_enddate = $this->LeaveRequests->query("select att_start_end_fn('$today_date', 2) as monthly_att_enddate");
        // $startDate = $att_startdate[0][0]['monthly_att_fromdate'];      
        // $endDate = $att_enddate[0][0]['monthly_att_enddate'];
        // // Default condition: applied leave within date range
        // $conditions = [
        //     'OR' => [
        //     [
        //         'FROMDATE >=' => $startDate,
        //         'FROMDATE <=' => $endDate
        //     ],
        //     [
        //         'TODATE >=' => $startDate,
        //         'TODATE <=' => $endDate
        //     ],
        //     [
        //         'FROMDATE <=' => $startDate,
        //         'TODATE >=' => $endDate
        //     ]
        // ],
        // 'LEAVESTATUS' => 'Applied'
        // ];
        // //end   
                
        //         if ($emp_fkey != 0) {
        //     $conditions = [
        //         'OR' => [
        //             [
        //                 'ISAutherizedby' => $emp_fkey,
        //                 'FROMDATE >=' => $startDate,
        //                 'TODATE <=' => $endDate,
        //                 'LEAVESTATUS' => 'Applied'
        //             ]
        //         ]
        //     ];
        // }

     //edited by athira on 11-11-2025
 // --- Attendance cycle logic (Athira's version with +1 month after cycle end) ---
    $today = date('Y-m');
    $today_date = $today . '-01';

    // Get current cycle dates
    $att_startdate = $this->LeaveRequests->query("SELECT att_start_end_fn('$today_date', 1) AS monthly_att_fromdate");
    $att_enddate = $this->LeaveRequests->query("SELECT att_start_end_fn('$today_date', 2) AS monthly_att_enddate");

    $startDate = $att_startdate[0][0]['monthly_att_fromdate'];
    $endDate = $att_enddate[0][0]['monthly_att_enddate'];

    $today_actual = date('Y-m-d');

    // If today is after the cycle end, move to next month's cycle
    if (strtotime($today_actual) > strtotime($endDate)) {
        $next_month = date('Y-m', strtotime('+1 month', strtotime($today_date)));
        $next_month_date = $next_month . '-01';

        $att_startdate = $this->LeaveRequests->query("SELECT att_start_end_fn('$next_month_date', 1) AS monthly_att_fromdate");
        $att_enddate = $this->LeaveRequests->query("SELECT att_start_end_fn('$next_month_date', 2) AS monthly_att_enddate");

        $startDate = $att_startdate[0][0]['monthly_att_fromdate'];
        $endDate = $att_enddate[0][0]['monthly_att_enddate'];
    }

    // --- Build condition: only consider FROMDATE within the attendance cycle ---
    $conditions = [
        'FROMDATE >=' => $startDate,
        'FROMDATE <=' => $endDate,
        'LEAVESTATUS' => 'Applied'
    ];

    // --- If emp_fkey is passed, filter by authorizer too ---
    if ($emp_fkey != 0) {
        $conditions['ISAutherizedby'] = $emp_fkey;
    }
     //end
        //edited by anukrishnan_28-01-2025 open
        $user_group = $this->Session->read('user_group');
        $emp_fkey = $this->Session->read('emp_fkey');
        if ($user_group == '2') {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $emp_fkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            $conditions = [];
            if ($is_ho != 1) {
                $conditions[] = "EmployeeDetails.branch_code = '$is_ho'";
            }
        }
        //close
        //edited by sinsiya on 18-06-2024 for getting latest records
        $arr_empleaverequests = $this->LeaveRequests->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions,
            'order' => ['emp_info.EmpName' => 'ASC']

        ));
        return $arr_empleaverequests;
    }

    public function todayattendance()
    {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
    }

    public function lastmonthattendanceinfo()
    {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
    }

    public function thismonthattendanceinfo()
    {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
    }
    public function listlastmonthattendance($hierarchy = '')
    {
        $this->autoRender = FALSE;

        $company_code = $this->Session->read('company_code');
        $user_group = $this->Session->read('user_group'); //edit anukrishnan_27-01-2025
        if ($company_code == 'BKHS') {
            $last_month_cond = array(
                "date_format(LOGDATE,'%Y-%m-%d') >=" => date("Y-m-01", strtotime('-1 months', strtotime("now"))),
                "date_format(LOGDATE,'%Y-%m-%d') <=" => date("Y-m-t", strtotime('-1 months', strtotime("now"))),
                "DeviceAttendance.status >=" => "Y",
                "OR" => array(
                    "DeviceAttendance.C2 " => array("MOB", "", "SIT"),
                    "DeviceAttendance.C2  is NULL"
                )
            );
        } else {
            $last_month_cond = array(
                "date_format(LOGDATE,'%Y-%m-%d') >=" => date("Y-m-01", strtotime('-1 months', strtotime("now"))),
                "date_format(LOGDATE,'%Y-%m-%d') <=" => date("Y-m-t", strtotime('-1 months', strtotime("now"))),
                "DeviceAttendance.status >=" => "Y",
                "OR" => array(
                    "DeviceAttendance.C2 " => array("MOB", ""),
                    "DeviceAttendance.C2  is NULL"
                )
            );
        }

        $today_join[] = array(
            'table' => 'emp_details',
            'alias' => 'EmployeeDetails',
            'type' => 'INNER',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
        );
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
        if ($emp_fkey != '') {
            if ($hierarchy == 'hierarchy') {
                $today_join[] = array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                );
                //$last_month_cond[] = "(EmployeeProfessionalDetails.attr1 = $emp_fkey or EmployeeProfessionalDetails.emp_fkey = $emp_fkey )";
                //heirarchy condition
                $today = $this->DeviceAttendance->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
                $emps = array();
                foreach ($today as $key => $att) {
                    $emps[] = $att['a']['emp_fkey'];
                }
                $str = implode("','", $emps);
                $last_month_cond[] = "EmployeeDetails.emp_pkey in ('$str')";
            } else {
                //edit anukrishnan_27-01-2025 open
                // $last_month_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');
                if ($user_group == '2') {
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_is_ho = $this->EmployeeDetails->query(
                        "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                        ['emp_pkey' => $emp_fkey]
                    );
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $last_month_cond['DeviceAttendance.branch_code'] = $is_ho;
                    }
                } else {
                    $last_month_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');
                }
                // close
            }
        }

        $today_join[] = array(
            'table' => 'branches',
            'alias' => 'Branch',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.branch_code = Branch.branch_code')
        );
        //edited by sinsiya on 13-06-2024
        $today_join[] = array(
            'table' => 'employee_info',
            'alias' => 'emp_info',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = emp_info.emp_pkey')
        );

        //EDITED BY SINSIYA ON 18-09-2024
        $last_month_att = $this->DeviceAttendance->find(
            "all",
            array(
                "order" => "emp_info.EmpName",
                "conditions" => $last_month_cond,
                'joins' => $today_join,
                "fields" => "Branch.branch_name,emp_info.employee_id,concat(EmployeeDetails.first_name,' ',ifnull(EmployeeDetails.middile_name,''),' ',ifnull(EmployeeDetails.last_name,'')) AS EmpName,DeviceAttendance.C1,DeviceAttendance.LOGDATE,
-- if((DeviceAttendance.C3 in ('',NULL)),Branch.branch_name,DeviceAttendance.C3) AS Location
            COALESCE(DeviceAttendance.C3,Branch.branch_name) AS Location "
            )
        );

        $arr_resp = array(
            'data' => array()
        );

        $i = 0;
        $j = 1;
        foreach ($last_month_att as $key => $att) {
            $arr_resp['data'][$i][]/* ['SL NO'] */ = $j;
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att[0]['EmpName']) ? $att[0]['EmpName'] : '';
            $arr_resp['data'][$i][]/* ['EmpID'] */ = isset($att["emp_info"]['employee_id']) ? $att["emp_info"]['employee_id'] : '';
            $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["Branch"]['branch_name']) ? $att["Branch"]['branch_name'] : '';
            $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("d-m-Y", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';      //EDITED BY ASHIN on 28-06-24
            // $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i:s", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["DeviceAttendance"]['C1']) ? $att["DeviceAttendance"]['C1'] : '';
            $arr_resp['data'][$i][]/* ['Location'] */ = isset($att[0]['Location']) ? $att[0]['Location'] : '';
            $i++;
            $j++;
        }
        return json_encode($arr_resp);
        // DEBUG($arr_resp);
    }

    public function listthismonthattendance($hierarchy = '')
    {
        $this->autoRender = FALSE;
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $user_group = $this->Session->read('user_group'); //edited by anukrishnana_27-01-2025
        // debug($user_group);
        if ($company_code == 'BKHS') {
            $this_month_cond = array(
                "date_format(LOGDATE,'%Y-%m-%d') >=" => date("Y-m-01", strtotime("now")),
                "date_format(LOGDATE,'%Y-%m-%d') <=" => date("Y-m-t", strtotime("now")),
                "DeviceAttendance.status >=" => "Y",
                "OR" => array(
                    "DeviceAttendance.C2 " => array("MOB", "", "SIT"),
                    "DeviceAttendance.C2  is NULL"
                )
            );
        } else {
            $this_month_cond = array(
                "date_format(LOGDATE,'%Y-%m-%d') >=" => date("Y-m-01", strtotime("now")),
                "date_format(LOGDATE,'%Y-%m-%d') <=" => date("Y-m-t", strtotime("now")),
                "DeviceAttendance.status >=" => "Y",
                "OR" => array(
                    "DeviceAttendance.C2 " => array("MOB", ""),
                    "DeviceAttendance.C2  is NULL"
                )
            );
        }

        $today_join[] = array(
            'table' => 'emp_details',
            'alias' => 'EmployeeDetails',
            'type' => 'INNER',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
        );

        $emp_fkey = $this->Session->read('emp_fkey');
        if ($emp_fkey != '') {
            if ($hierarchy == 'hierarchy') {
                $today_join[] = array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                );
                // $this_month_cond[] = "(EmployeeProfessionalDetails.attr1 = $emp_fkey or EmployeeProfessionalDetails.emp_fkey = $emp_fkey )";
                //heirarchy condition
                $today = $this->DeviceAttendance->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
                $emps = array();
                foreach ($today as $key => $att) {
                    $emps[] = $att['a']['emp_fkey'];
                }
                $str = implode("','", $emps);
                $this_month_cond[] = "EmployeeDetails.emp_pkey in ('$str')";
            } else {
                //edited by anukrishnana_27-01-2025 open
                // $this_month_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');
                if ($user_group == '2') {
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_is_ho = $this->EmployeeDetails->query(
                        "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                        ['emp_pkey' => $emp_fkey]
                    );
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $this_month_cond['DeviceAttendance.branch_code'] = $is_ho;
                    }
                } else {
                    $this_month_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');
                }
                //close

            }
        }

        //edited by sinsiya on 13-06-2024
        $today_join[] = array(
            'table' => 'branches',
            'alias' => 'Branch',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.branch_code = Branch.branch_code')
        );
        $today_join[] = array(
            'table' => 'employee_info',
            'alias' => 'emp_info',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = emp_info.emp_pkey')
        );

        // var_dump($this_month_cond);
        // var_dump($today_join);

        //edited by sinsiya 13-06-2024
        $this_month_att = $this->DeviceAttendance->find(
            "all",
            array(
                "order" => "emp_info.EmpName",
                "conditions" => $this_month_cond,
                'joins' => $today_join,
                "fields" => "Branch.branch_name,DeviceAttendance.device_attandance_seq,emp_info.employee_id,concat(EmployeeDetails.first_name,' ',ifnull(EmployeeDetails.middile_name,''),' ',ifnull(EmployeeDetails.last_name,'')) AS EmpName,DeviceAttendance.C1,DeviceAttendance.LOGDATE,
--  if((DeviceAttendance.C3 in ('',NULL)),Branch.branch_name,DeviceAttendance.C3) AS Location
 COALESCE(DeviceAttendance.C3,Branch.branch_name) AS Location "
            )
        );

        $arr_resp = array(
            'data' => array()
        );


        $i = 0;
        $j = 1;
        //edited by sinsiya on 13-06-2024
        // $s=1;
        foreach ($this_month_att as $key => $att) {
            $arr_resp['data'][$i][]/* ['SL NO'] */ = $j;
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att[0]['EmpName']) ? $att[0]['EmpName'] : '';
            $arr_resp['data'][$i][]/* ['EmpID'] */ = isset($att["emp_info"]['employee_id']) ? $att["emp_info"]['employee_id'] : '';
            $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["Branch"]['branch_name']) ? $att["Branch"]['branch_name'] : '';
            $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("d-m-Y", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            // $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i:s", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["DeviceAttendance"]['C1']) ? $att["DeviceAttendance"]['C1'] : '';
            $arr_resp['data'][$i][]/* ['Location'] */ = isset($att[0]['Location']) ? $att[0]['Location'] : '';
            $i++;
            $j++;
        }
        return json_encode($arr_resp);
    }
    public function LocationUpdates($hierarchy = '')
    {
        $this->autoRender = FALSE;

        $this_month_cond = array(
            "LOGDATE >=" => date("Y-m-01", strtotime("now")),
            "LOGDATE <=" => date("Y-m-t", strtotime("now")),
            "DeviceAttendance.status >=" => "Y"
        );

        $today_join[] = array(
            'table' => 'emp_details',
            'alias' => 'EmployeeDetails',
            'type' => 'INNER',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
        );

        $emp_fkey = $this->Session->read('emp_fkey');
        if ($emp_fkey != '') {
            if ($hierarchy == 'hierarchy') {
                $today_join[] = array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                );
                $this_month_cond[] = "(EmployeeProfessionalDetails.attr1 = $emp_fkey or EmployeeProfessionalDetails.emp_fkey = $emp_fkey )";
            } else {
                $this_month_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');
            }
        }

        $today_join[] = array(
            'table' => 'branches',
            'alias' => 'Branch',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.branch_code = Branch.branch_code')
        );
     //edited by sinsiya on 13-06-2024 removed order by MB.created_time, Name DESC,
     //edited by athira on 14-10-2025
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
      $this_month_att = $this->DeviceAttendance->query("select MB.*,concat(UC.first_name, ' ' ,UC.last_name)as Name,emp_info.branch,
       emp_info.employee_id from mob_user_locations as MB "
              . "left join user_credentials as UC on(UC.user_id = MB.user_id) 
              LEFT JOIN employee_info AS emp_info ON (UC.emp_fkey = emp_info.emp_pkey)
              where DATE_FORMAT(created_time,'%y-%m-%d') =current_date order by UC.first_name");
              //end
//$this_month_att = $this->DeviceAttendance->query("
//    SELECT
//        MB.*,
//        CONCAT(UC.first_name, ' ', UC.last_name) AS Name,
//        Branch.branch_name,
//        emp_info.employee_id
//    FROM
//        mob_user_locations AS MB
//    LEFT JOIN
//        user_credentials AS UC ON (UC.user_id = MB.user_id)
//    LEFT JOIN
//        employee_info AS emp_info ON (UC.emp_fkey = emp_info.emp_pkey)
//    LEFT JOIN
//        branches AS Branch ON (emp_info.branch_code = Branch.branch_code)
//    WHERE
//        DATE_FORMAT(MB.created_time,'%y-%m-%d') = CURRENT_DATE
//    ORDER BY
//     MB.mob_user_locations_pkey DESC
//");
        $arr_resp = array(
            'data' => array()
        );
        $i = 0;
        $j=1;
        //edited by sinsiya on 13-06-2024
        foreach ($this_month_att as $key => $att) {
            $locations = array_merge($att['MB'], $att['0']);
            $arr_resp['data'][$i][]/* ['SLNO'] */ = $j;
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($locations['Name']) ? $locations['Name'] : '';
            $arr_resp['data'][$i][]/* ['EmpID'] */ = isset($att["emp_info"]['employee_id']) ? $att["emp_info"]['employee_id'] : '';
            $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["emp_info"]['branch']) ? $att["emp_info"]['branch'] : '';

//edited by ASHIN on 07-07-24        
         // $arr_resp['data'][$i][]/* ['ACTION'] */ = isset($locations["stepinout"]) ? $locations["stepinout"] : '';
         $arr_resp['data'][$i][]/* ['ACTION'] */ = isset($locations["stepinout"]) ?
             (strtoupper($locations["stepinout"]) == 'IN' ? 'STEP IN' :
             (strtoupper($locations["stepinout"]) == 'OUT' ? 'STEP OUT' :
             ($locations["stepinout"] == 'START FROM' ? 'START' : ''))) : '';

            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($locations['created_time']) ? date('d-m-Y H:i:s',strtotime($locations['created_time'])) : '';    //edited by ASHIN on 02-07-24

            $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($locations["location"]) ? $locations["location"] : '';
    

        
            $i++;
            $j++;
        }
        return json_encode($arr_resp);
        //debug($arr_resp);
    }
    public function ajax_locationtracking()
{
    $this->layout = 'ajax';
    $data = $this->LocationUpdates(); // returns JSON
    $arr_location = json_decode($data, true); // convert to PHP array
    //debug($arr_location);
    $this->set('arr_location', $arr_location['data']);
    $this->render('/DashboardNew/LocationUpdates'); 
}
    public function listtodayattendance($hierarchy = '')
    {
        $this->autoRender = FALSE;
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $user_group = $this->Session->read('user_group');      // edited by anukrishnan_27-01-2025
        // // if ($company_code == 'BKHS') {
        //     $today_cond = array(
        //         "LOGDATE >=" => date("Y-m-d", strtotime("now")),
        //         "DeviceAttendance.status >=" => "Y",
        //         "OR" => array(
        //             "DeviceAttendance.C2 " => array("MOB", "", "SIT"),
        //             "DeviceAttendance.C2  is NULL"
        //         )
        //     );
        // // } 
        // else {
            $today_cond = array(
                "LOGDATE >=" => date("Y-m-d", strtotime("now")),
                "DeviceAttendance.status >=" => "Y",
                "OR" => array(
                    "DeviceAttendance.C2 " => array("MOB", ""),
                    "DeviceAttendance.C2  is NULL"
                )
            );
        // }

        $today_join[] = array(
            'table' => 'emp_details',
            'alias' => 'EmployeeDetails',
            'type' => 'INNER',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
        );

        $emp_fkey = $this->Session->read('emp_fkey');
        if ($emp_fkey != '') {
            if ($hierarchy == 'hierarchy') {
                $today_join[] = array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                );
                // $today_cond[] = "(EmployeeProfessionalDetails.attr1 = $emp_fkey or EmployeeProfessionalDetails.emp_fkey = $emp_fkey 
                $today = $this->DeviceAttendance->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
                $emps = array();
                foreach ($today as $key => $att) {
                    $emps[] = $att['a']['emp_fkey'];
                }
                $str = implode("','", $emps);
                $today_cond[] = "EmployeeDetails.emp_pkey in ('$str')";
                //$today_cond[] = "EmployeeDetails.emp_pkey in (select emp_fkey from emp_proff where attr1 = '$emp_fkey' or emp_fkey = '$emp_fkey') or EmployeeDetails.emp_pkey in ($str))  ";
            } else {
                //edited by anukrishnana_27-01-2025 open
                // $today_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');

                if ($user_group == '2') {
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_is_ho = $this->EmployeeDetails->query(
                        "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                        ['emp_pkey' => $emp_fkey]
                    );
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $today_cond['DeviceAttendance.branch_code'] = $is_ho;
                    }
                } else {
                    $today_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');
                }
                // close

            }
        }
        //edited by sinsiya on 13-06-2024
        $today_join[] = array(
            'table' => 'branches',
            'alias' => 'Branch',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.branch_code = Branch.branch_code')
        );
        $today_join[] = array(
            'table' => 'employee_info',
            'alias' => 'emp_info',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = emp_info.emp_pkey')
        );

        // var_dump($today_cond);
        // var_dump($today_join);

        //edited by sinsiya 13-06-2024
        $this_month_att = $this->DeviceAttendance->find(
            "all",
            array(
                "order" => "EmployeeDetails.first_name ASC",
                "conditions" => $today_cond,
                'joins' => $today_join,
                "fields" => "Branch.branch_name,DeviceAttendance.device_attandance_seq,emp_info.employee_id,concat(EmployeeDetails.first_name,' ',ifnull(EmployeeDetails.middile_name,''),' ',ifnull(EmployeeDetails.last_name,'')) AS EmpName,DeviceAttendance.C1,DeviceAttendance.LOGDATE,
--  if((DeviceAttendance.C3 in ('',NULL)),Branch.branch_name,DeviceAttendance.C3) AS Location
 COALESCE(DeviceAttendance.C3,Branch.branch_name) AS Location "
            )
        );

        $arr_resp = array(
            'data' => array()
        );


        $i = 0;
        $j = 1;
        //edited by sinsiya on 13-06-2024
        // $s=1;
        foreach ($this_month_att as $key => $att) {
            $arr_resp['data'][$i][]/* ['SL NO'] */ = $j;
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att[0]['EmpName']) ? $att[0]['EmpName'] : '';
            $arr_resp['data'][$i][]/* ['EmpID'] */ = isset($att["emp_info"]['employee_id']) ? $att["emp_info"]['employee_id'] : '';
            $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["Branch"]['branch_name']) ? $att["Branch"]['branch_name'] : '';
            $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("d-m-Y", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            // $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i:s", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["DeviceAttendance"]['C1']) ? $att["DeviceAttendance"]['C1'] : '';
            $arr_resp['data'][$i][]/* ['Location'] */ = isset($att[0]['Location']) ? $att[0]['Location'] : '';
            $i++;
            $j++;
        }
        return json_encode($arr_resp);
    }
    public function listemployeemisspunches()
    {
        //edited by sinsiya 13-06-2024
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //        $arr_empmisspunches = $this->EmployeeDetails->query("select ed.emp_id,concat(ed.first_name,' ',ifnull(ed.last_name,'')) as fullname, yearmonth, count(*) misscount
        //     from emp_detail_timeattandance edt ,emp_details ed where ed.emp_pkey=edt.emp_pkey 
        //    and present<>'P/P' and present is not null and date_format(current_date,'%y-%m') =date_format(yearmonth,'%y-%m')
        //    group by edt.emp_pkey,ed.first_name,yearmonth order by 3 desc");
        //edited by sinsiya on 18-06-2024

        $arr_empmisspunches = $this->EmployeeDetails->query("SELECT 
            ed.emp_id,
            CONCAT(ed.first_name, ' ', IFNULL(ed.last_name, '')) AS fullname,
            emp_info.employee_id,
            branch.branch_name,
            edt.yearmonth,
            COUNT(*) AS misscount
        FROM 
            emp_detail_timeattandance edt
        JOIN 
            emp_details ed ON ed.emp_pkey = edt.emp_pkey
        JOIN 
            employee_info emp_info ON emp_info.emp_pkey = edt.emp_pkey
        JOIN 
            branches branch ON branch.branch_code = emp_info.branch_code
        WHERE 
            edt.present <> 'P/P'
            AND edt.present IS NOT NULL
            AND DATE_FORMAT(CURRENT_DATE, '%y-%m') = DATE_FORMAT(edt.yearmonth, '%y-%m')
        group by edt.emp_pkey,ed.first_name,yearmonth ORDER BY emp_info.EmpName ASC
        ");
        // edited by anukrishnan_28-01-2025 open
        $user_group = $this->Session->read('user_group');
        $emp_fkey = $this->Session->read('emp_fkey');
        if ($user_group == '2') {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $emp_fkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            $conditions = [];

            if ($is_ho != 1) {
                $conditions[] = "emp_info.branch_code = '$is_ho'";
            } else {
                $conditions = array("EmployeeDetails.status" => 1);
            }
            $whereClause = implode(' AND ', $conditions);
            $arr_empmisspunches = $this->EmployeeDetails->query("SELECT 
                ed.emp_id,
                CONCAT(ed.first_name, ' ', IFNULL(ed.last_name, '')) AS fullname,
                emp_info.employee_id,
                branch.branch_name,
                edt.yearmonth,
                COUNT(*) AS misscount
            FROM 
                emp_detail_timeattandance edt
            JOIN 
                emp_details ed ON ed.emp_pkey = edt.emp_pkey
            JOIN 
                employee_info emp_info ON emp_info.emp_pkey = edt.emp_pkey
            JOIN 
                branches branch ON branch.branch_code = emp_info.branch_code
            WHERE 
                edt.present <> 'P/P'
                AND edt.present IS NOT NULL
                AND DATE_FORMAT(CURRENT_DATE, '%y-%m') = DATE_FORMAT(edt.yearmonth, '%y-%m')
                AND $whereClause
            group by edt.emp_pkey,ed.first_name,yearmonth ORDER BY emp_info.EmpName ASC
            ");
        }
        //close
        return $arr_empmisspunches;
    }
    public function getMissedAttendance()
    {
        $this->autoRender = false;
        $results = $this->listemployeemisspunches();
        $this->set(compact('results'));

        // Render the partial view and return as HTML
        return $this->render('/DashboardNew/misspunchnew'); // Create this file next
    }


    public function listleaverequests()
    {
        $leaverRequests = $this->listemployeeleaverequests(0);
        $this->set("results", $leaverRequests);
    }

    public function checkpunch($x = 0, $y = 0, $z = 0)
    {
        $arr_form_data = $this->request->data;

        $emp_fkey = $this->Session->read('emp_fkey');
        $this->autoRender = false;
        $this->AttendancePunch->useDbConfig = $this->Session->read('ds');
        $latitude = $y;
        $longitude = $z;

        function getlocation($latitude, $longitude)
        {
            $geolocation = $latitude . ',' . $longitude;
            //var_dump($longitude);
            $request = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $geolocation . '&sensor=false&key=AIzaSyBmp4GQqI30Qis3uVCEbDncRA667nvO61A';
            $file_contents = file_get_contents($request);
            $json_decode = json_decode($file_contents);
            // var_dump($json_decode);
            if (isset($json_decode->results[0])) {
                $formatted_address = $json_decode->results[0]->formatted_address;
            } else {
                $formatted_address = '';
            }
            return $formatted_address;
        }

        $loc = getlocation($latitude, $longitude);
        //        debug($loc);
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        function getRealIpAddr()
        {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            return $ip;
        }

        //            function getBrowser() {
        //                $browser = array("Navigator" => "/Navigator(.*)/i",
        //                    "Firefox" => "/Firefox(.*)/i",
        //                    "Internet Explorer" => "/MSIE(.*)/i",
        //                    "Google Chrome" => "/chrome(.*)/i",
        //                    "MAXTHON" => "/MAXTHON(.*)/i",
        //                    "Opera" => "/Opera(.*)/i",
        //                );
        //
        //                foreach ($browser as $key => $value) {
        //                    $info = array();
        //                    $agent = "";
        //                    if (preg_match($value, $_SERVER['HTTP_USER_AGENT'])) {
        //                        $info = array_merge($info, array("Browser" => $key));
        //                        $info = array_merge($info, array(
        //                            "Version" => "0"));
        //                        break;
        //                    } else {
        //                        $info = array_merge($info, array("Browser" => "UnKnown"));
        //                        $info = array_merge($info, array("Version" => "UnKnown"));
        //                    }
        //                }
        //                return $info['Browser'];
        //            }
        //            debug($_SERVER['HTTP_USER_AGENT']);
        //        echo($x);
        function getBrowser()
        {
            $u_agent = $_SERVER['HTTP_USER_AGENT'];
            $bname = 'Unknown';
            $platform = 'Unknown';
            $version = "";
            // First get the platform?
            if (preg_match('/linux/i', $u_agent)) {
                $platform = 'linux';
            } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
                $platform = 'mac';
            } elseif (preg_match('/windows|win32/i', $u_agent)) {
                $platform = 'windows';
            }
            // Next get the name of the useragent yes seperately and for good reason
            if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
                $bname = 'Internet Explorer';
                $ub = "MSIE";
            } elseif (preg_match('/Firefox/i', $u_agent)) {
                $bname = 'Mozilla Firefox';
                $ub = "Firefox";
            } elseif (preg_match('/Chrome/i', $u_agent)) {
                $bname = 'Google Chrome';
                $ub = "Chrome";
            } elseif (preg_match('/Safari/i', $u_agent)) {
                $bname = 'Apple Safari';
                $ub = "Safari";
            } elseif (preg_match('/Opera/i', $u_agent)) {
                $bname = 'Opera';
                $ub = "Opera";
            } elseif (preg_match('/Netscape/i', $u_agent)) {
                $bname = 'Netscape';
                $ub = "Netscape";
            }
            // finally get the correct version number
            $known = array('Version', $ub, 'other');
            $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
            if (!preg_match_all($pattern, $u_agent, $matches)) {
                // we have no matching number just continue
            }
            // see how many we have
            $i = count($matches['browser']);
            if ($i != 1) {
                //we will have two since we are not using 'other' argument yet
                //see if version is before or after the name
                if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                    $version = $matches['version'][0];
                } else {
                    $version = $matches['version'][1];
                }
            } else {
                $version = $matches['version'][0];
            }
            // check if we have a number
            if ($version == null || $version == "") {
                $version = "?";
            }
            //return array(
            //  'userAgent' => $u_agent,
            //  'name'      => $bname,
            //  'version'   => $bname,
            //  'platform'  => $platform,
            //  'pattern'    => $pattern
            //  );
            return $bname;
        }

        $browser = getBrowser();
        $os = php_uname('s');
        $host = php_uname('n');
        if ($x === '1') {
            $dir = 'in';
        } else {
            $dir = 'out';
        }
        $auditor_arr = array();

        $auditor_arr['emp_fkey'] = $emp_fkey;
        $auditor_arr['direction'] = $dir;
        $auditor_arr['ip_ad'] = getRealIpAddr();
        $auditor_arr['location'] = $loc;
        $auditor_arr['browser'] = $browser;
        $auditor_arr['host_name'] = $host;
        $auditor_arr['os'] = $os;

        $result = $this->AttendancePunch->save($auditor_arr);
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "puch success";

        echo json_encode($resp);



        //           
    }

    public function lastpunch()
    {
        $arr_form_data = $this->request->data;
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->autoRender = false;
        $this->AttendancePunch->useDbConfig = $this->Session->read('ds');
        $arr_lastpuchinfo = $this->AttendancePunch->query("SELECT `last_punch_fn`($emp_fkey) punch");
        //        debug($arr_lastpuchinfo);
        $lastpunch = isset($arr_lastpuchinfo['0']['0']['punch']) ? $arr_lastpuchinfo['0']['0']['punch'] : 'out';
        //        debug($lastpunch);
        //        echo $lastpunch;
        $resp = array();
        if ($lastpunch == 'in') {

            $resp["success"] = true;
            //            $resp["msg"] = "puch success";
        } else {
            $resp["success"] = false;
            //            $resp["msg"] = "puch success";
        }
        echo json_encode($resp);
    }
    public function ajax_today_attendance()
    {
        $this->layout = false;
        $this->autoRender = false;
        // Render the partial view (todayattendancenew.ctp)
        $this->render('/DashboardNew/todayattendancenew');
    }
    public function ajax_thismonth_attendance()
    {
    $this->layout = false;
    $this->autoRender = false;
    $this->render('/DashboardNew/thismonthattendancenew');
    }
    public function ajax_lastmonth_attendance()
{
    $this->layout = false;
    $this->autoRender = false;
    $this->render('/DashboardNew/lastmonthattendancenew');
}
    
    

    
    //Edited by Akshay on 18-11-2023
    public function wish_modal($emp_pkey = 0, $event = '')
    {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $this->set('emp_pkey', $emp_pkey);
        $this->set('event', $event);
        $emp_name = '';
        try {
            $arr_name = $this->Lateout->query("SELECT ei.EmpName FROM employee_info ei WHERE ei.emp_pkey = $emp_pkey");
            $emp_name = isset($arr_name[0]['ei']['EmpName']) ? $arr_name[0]['ei']['EmpName'] : '';
        } catch (Exception $e) {
        }
        $this->set('emp_name', $emp_name);
    }

    public function convertimage()
    {

        $this->autorender = false;
        $this->layout = null;
        $this->render(false);

        $arr_form_data = $this->request->data;

        $emp_pkey = $arr_form_data['emp_pkey'];
        $event = $arr_form_data['event'];
        
        $remark = isset($arr_form_data['remarks']) ? $arr_form_data['remarks'] : '';


        try {

            //Edited by Aksahy on 28-11-2023
            $google_fonts = "Roboto";

            $data = array(
                'html' => '',
                'css' => '',
                'google_fonts' => $google_fonts,
                'url' => 'https://v1.mypayrollmaster.online/Dashboard/renderImageTempalte'
            );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://hcti.io/v1/image");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            curl_setopt($ch, CURLOPT_POST, 1);
            // Retrieve your user_id and api_key from https://htmlcsstoimage.com/dashboard
            curl_setopt($ch, CURLOPT_USERPWD, "3ffab2bd-74e7-4198-8153-263ff1c985d2" . ":" . "8ee1bf09-7791-48d6-96ae-e6ace86eafc4");

            $headers = array();
            $headers = array("Content-Type: application/x-www-form-urlencoded");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            $res = json_decode($result, true);
            //var_dump($res);
            // $this->sendemailtemplatedatabase($emp_pkey, $event, $remark); 
            $this->sendemailtemplate($emp_pkey, $event, $remark); //Edited by Akshay on 28-11-2023
            // $this->sendemailtemplate($res['url']);
            // https://hcti.io/v1/image/202dc04d-5efc-482e-8f92-bb51612c84cf
        } catch (Exception $e) {
            //  debug($e);
        }
    }

    public function sendemailtemplate($emp_pkey = 0, $event = '', $remark = '')
    {
        $this->autorender = false;
        $this->layout = null;
        $this->render(false);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $company_code = strtoupper($this->Session->read('company_code')); // Edited by Akshay on 7-7-2025

        try {

            $arr_emp_details =  $this->EmployeeDetails->query("SELECT ei.EmpName, ed.email, uc.avatar,ei.joining_date FROM employee_info ei 
                                                            LEFT JOIN emp_details ed ON ed.emp_pkey = ei.emp_pkey
                                                            LEFT JOIN user_credentials AS uc ON uc.emp_fkey = ei.emp_pkey
                                                            WHERE ei.emp_pkey = $emp_pkey                
                                                        ");
            // debug($arr_emp_details); exit;
            $email = isset($arr_emp_details[0]['ed']['email']) ? $arr_emp_details[0]['ed']['email'] : '';
            $name  = isset($arr_emp_details[0]['ei']['EmpName']) ? $arr_emp_details[0]['ei']['EmpName'] : '';
            $avatar = isset($arr_emp_details[0]['uc']['avatar']) ? $arr_emp_details[0]['uc']['avatar'] : '';
            // Assuming $joiningDate is a string in the format "YYYY-MM-DD"
            $joiningDate =  $arr_emp_details[0]['ei']['joining_date'];

            // Create DateTime objects for the joining date and current date
            $startDate = new DateTime($joiningDate);
            $endDate = new DateTime(date("Y-m-d"));

            // Calculate the interval between the two dates
            $interval = $startDate->diff($endDate);

            // Get the number of years
            $years = $interval->y;

            // Output the work anniversary


            if ($years == 1) {
                $suffix = "ST";
            } elseif ($years == 2) {
                $suffix = "ND";
            } elseif ($years == 3) {
                $suffix = "RD";
            } else {
                $suffix = "TH";
            }

            $arr_comp_contact = $this->EmployeeDetails->query("SELECT cc.logo FROM comp_contact_info cc");
            $logo_url = isset($arr_comp_contact[0]['cc']['logo']) ? $arr_comp_contact[0]['cc']['logo'] : '';

            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            $mail->SMTPDebug = false;
            $mail->isSMTP();
            $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';
            $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('mypayrollmaster@office24.online');
            $mail->addAddress($email);
            $mail->addBCC('projects@greatleap.tech');
            $mail->addBCC('meghaforsight@gmail.com');
            $mail->addReplyTo('mypayrollmaster@office24.online');
            $mail->isHTML(true);

            $image = 'http://v1.mypayrollmaster.online/newlogin/img/6.png'; //Edited by Akshay on 28-11-2023
            // $profileimage = 'https://qaoci.mypayrollmaster.online/User/profile';
            $profileimage = 'https://v1.mypayrollmaster.online/' . $avatar;
            if ($avatar != 'img/placeholdermen.jpeg') {
                // $photo = '<div class="profile-image" style="padding-left: 20px; padding-bottom: 30px;left:20px;bottom:20px;">
                //                 <img src="' . $profileimage . '" alt="Profile Image" style="max-width: 200px; max-height: 200px; width: auto; height: auto; object-fit: contain;">
                //             </div>';
                // $photo = '<div class="profile-image" style="width:150px;height:150px;position: absolute; left: 20px; bottom: 0px; padding-left: 20px; z-index: 999; flex: 1;">
                //             <img src="'.$profileimage.'" alt="Profile Image" style="max-width: 150px; max-height: 150px; width: auto; height: auto; object-fit: contain;padding-bottom:0px;">
                //         </div>';
                if ($event == 'Work Anniversary') {
                    $photo = '<div class="profile-image" style="text-align: left; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                                <img src="' . $profileimage . '" alt="Profile Image" style="max-width: 100px; max-height: 130px; width: auto; height: auto; object-fit: contain; padding-top: 0px; padding-left: 10px;">
                            </div>
                            ';
                } else {
                    $photo = '<div class="profile-image" style="text-align: right; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                                <img src="' . $profileimage . '" alt="Profile Image" style="max-width: 150px; max-height: 100px; width: auto; height: auto; object-fit: contain; padding-bottom: 0px;">
                            </div>
                            ';
                }
            } else {
                if ($event == 'Work Anniversary') {
                    $photo = '<div class="profile-image" style="text-align: left; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                               
                            </div>
                            ';
                } else {
                    $photo = '<div class="profile-image" style="text-align: right; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                               
                            </div>
                            ';
                }
            }

            $logo_img = '';

            // if($logo_url != 'img/placeholdermen.jpeg'){
            //     $logo = 'https://qaoci.mypayrollmaster.online/'.$logo_url;
            //     $logo_img = '<div class="logo" style="text-align: left; width: 200px; height: 50px; overflow: hidden; margin-right:400px;">
            //                     <img src="'.$logo.'" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
            //                 </div>';
            // }else{
            //     $logo_img = '';
            // }



            //Edited by Akshay on 28-11-2023 
            if ($event == 'Work Anniversary') {
                // $image = 'http://qaoci.mypayrollmaster.online/newlogin/img/pink_flower_frame_edited.jpg';//Edited by Akshay on 28-11-2023
                $image = 'https://v1.mypayrollmaster.online/img/80.png';
                $color = '#fff';
                $heading = '';
                $heading = '<h3 style="margin-top:0px;text-align:center;color:#AA336A;">' . $heading . '</h3>';
                $heading_colour = '#fff';
                $wish_content = ' ' . $years . '<sup>' . $suffix . '<sup> ';

                $name_div = '<div class="text-over-image" style="color: #ff0000; text-align: left;padding-left:260px; font-size: 18px; padding-top: 10px;font-weight:bold;">
            <i> ' . '' . '</i>
            </div>';
            } else {
                // Edited by Akshay on 7-7-2025

                if ($company_code == 'GLET') {
                    $image = 'https://v1.mypayrollmaster.online/img/14.jpg';
                    $color = '#63cf22';
                    $heading = ''; // Optional if you want a headline like "Happy Birthday!"
                    $heading_colour = '#333333';
                    $wish_content = ''; // Optional, for extra lines like "Wishing you a joyful year ahead!"



                    $name_div = '<div style="position: absolute;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                background: rgba(255,255,255,0.7);
                                padding: 10px 20px;
                                border-radius: 10px;
                                font-size: 26px;
                                font-weight: bold;
                                color: #332c2b;
                                text-align: center;
                                max-width: 90%;
                                z-index: 10;
                                line-height: 1.4;
                                word-break: break-word;
                                font-family: Arial, sans-serif;">
                                Dear ' . htmlspecialchars($name) . '
                            </div>';
                }

                // End
                else {
                    $image = 'https://v1.mypayrollmaster.online/img/7.png';
                    $color = '#63cf22';
                    $heading = '';
                    $heading_colour = '#333333';
                    $wish_content = '';

                    $name_div = '<div class="text-over-image" style="color: #332c2b; text-align: right; font-size: 18px; padding-top: 0px;padding-right:90px;font-weight:900!important;">
                                Dear ' . $name . '
                            </div>';
                }
            }

            $mail->Subject = $event . ' of ' . $name;

            if ($event == 'Work Anniversary') {

                // Edited by Akshay on 8-7-2025
                $backgroundUrl = 'https://v1.mypayrollmaster.online/img/82.jpg'; // Work anniversary background
                $profileUrl = $profileimage; // Actual image URL or path of the employee
                $outputPath = WWW_ROOT . 'img' . DS . 'work_anniv_merged.jpg'; // Path to save output
                $event = 'Work Anniversary';
                // $years = $service_years; // Example: "5"
                $suffix = ' Years';      // Example: " Years" or " Yr"

                $this->mergeImages($backgroundUrl, $profileUrl, $outputPath, $event, $name, $years, $suffix);

                $generatedImageUrl = Router::url('/img/' . basename($outputPath), true) . '?v=' . time();

                if ($event == 'Work Anniversary') {
                    $mail->MsgHTML(
                        '
                                    <!DOCTYPE html>
                                    <html>
                                    <head>
                                        <title>Work Anniversary Wish</title>
                                        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                                    </head>
                                    <body style="margin: 0; padding: 0;">
                                        <div style="width: 100%; max-width: 626px; margin: 0 auto;">
                                            <img src="' . $generatedImageUrl . '" alt="Work Anniversary Banner" style="width: 100%; height: auto; display: block;" />
                                        </div>
                                        <div style="font-size: 15px; color: #ff0000; padding-top: 20px; font-weight: bold; padding-left: 10px;">
                                            ' . $remark . '
                                        </div>
                                    </body>
                                    </html>'
                    );
                }


                // End
            } else {
                // Edited by Akshay on 7-7-2025
                if ($company_code == 'GLET') {
                    $backgroundUrl = 'https://v1.mypayrollmaster.online/img/14.jpg';
                    $profileUrl = $profileimage;
                    $outputPath = WWW_ROOT . 'img' . DS . 'merged_image.jpg';
                    $this->mergeImages($backgroundUrl, $profileUrl, $outputPath, $event, $name, $years, $suffix);
                    $generatedImageUrl = Router::url('/img/' . basename($outputPath), true);

                    $generatedImageUrl .= '?v=' . time(); // or use filemtime($outputPath) if preferred

                    // $mail->addEmbeddedImage($outputPath, 'mergedimagecid', 'merged_image.jpg');
                    $mail->MsgHTML('
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <title>Birthday Wish</title>
                                    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                                </head>
                                <body style="margin: 0; padding: 0;">
                                    <div style="width: 100%; max-width: 626px; margin: 0 auto;">
                                        <img src="' . $generatedImageUrl . '" alt="Birthday Banner" style="width: 100%; height: auto; display: block;" />
                                    </div>
                                    <div style="font-size: 15px; color: #ff0000; padding-top: 20px; font-weight: bold; padding-left: 10px;">
                                        ' . $remark . '
                                    </div>
                                </body>
                                </html>');
                    // End
                } else {
                    $mail->MsgHTML('
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <title>Birthday Wish</title>
                                </head>
                                <body>
                                    <!--[if gte mso 9]>
                                    <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:420px;height:300px;">
                                        <v:fill type="tile" src="' . $image . '" color="#eeeeee" />
                                        <v:textbox inset="0,0,0,0">
                                    <![endif]-->
                                    
                                    <div class="background-image" style="background: url(' . $image . ') no-repeat center center / cover; background-color: #eeeeee; width: 626px; height: 396px; position: relative;">
                            
                                    ' . $photo . '
                                    ' . $heading . '
                                        ' . $name_div . '

                                        <div style=" display: flex;padding-top:0px;">
                                            <div class="text-over-image" style="flex: 1;color: ' . $color . '; text-align: left; font-size: 15px; padding-top: 0px;padding-left:30px;font-weight: bold;">
                                                ' . $wish_content . '
                                            </div>                    
                    
                                        </div>
                    
                                    </div>
                                    <!--[if gte mso 9]>
                                        </v:textbox>
                                    </v:rect>
                                    <![endif]-->
                                </body>
                                </html> <div style="font-size: 15px; color: #ff0000;padding-top:20px;font-weight:bold;padding-left:10px;">' . $remark . '</div>
                            ');
                }
            }

            if (!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
                $this->Wish->useDbConfig = $this->Session->read('ds');
                date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)
                $arr_form_data = $this->request->data;
                $arr_form_data['created_by'] = $this->Session->read('login_user_id');
                $arr_form_data['type'] = $event;
                $arr_form_data['date'] = date('Y-m-d');
                $arr_form_data['remarks'] = $remark;
                $arr_form_data['emp_fkey'] = $emp_pkey;

                $result = $this->Wish->save($arr_form_data);

                echo 'Message has been sent';
            }
        } catch (Exception $ex) {
            var_dump('$ex->getMessage()');
        }
    }

    public function sendemailtemplatedatabase($emp_pkey = 0, $event = '', $remark = '')
    {

        $this->autorender = false;
        $this->layout = null;
        $this->render(false);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->Wish->useDbConfig = $this->Session->read('ds');
        try {

            $arr_emp_details =  $this->EmployeeDetails->query("SELECT ei.EmpName, ed.email, uc.avatar,ei.joining_date FROM employee_info ei 
                                                            LEFT JOIN emp_details ed ON ed.emp_pkey = ei.emp_pkey
                                                            LEFT JOIN user_credentials AS uc ON uc.emp_fkey = ei.emp_pkey
                                                            WHERE ei.emp_pkey = $emp_pkey                
                                                        ");
            // debug($arr_emp_details); exit;
            $email = isset($arr_emp_details[0]['ed']['email']) ? $arr_emp_details[0]['ed']['email'] : '';
            $name  = isset($arr_emp_details[0]['ei']['EmpName']) ? $arr_emp_details[0]['ei']['EmpName'] : '';
            $avatar = isset($arr_emp_details[0]['uc']['avatar']) ? $arr_emp_details[0]['uc']['avatar'] : '';
            if ($avatar = 'img/placeholdermen.jpeg') {
                $avatar = '';
            }
            // Assuming $joiningDate is a string in the format "YYYY-MM-DD"
            $joiningDate =  $arr_emp_details[0]['ei']['joining_date'];

            // Create DateTime objects for the joining date and current date
            $startDate = new DateTime($joiningDate);
            $endDate = new DateTime(date("Y-m-d"));

            // Calculate the interval between the two dates
            $interval = $startDate->diff($endDate);

            // Get the number of years
            $years = $interval->y;

            // Output the work anniversary
            if ($years == 1) {
                $suffix = "ST";
            } elseif ($years == 2) {
                $suffix = "ND";
            } elseif ($years == 3) {
                $suffix = "RD";
            } else {
                $suffix = "TH";
            }

            $arr_comp_contact = $this->EmployeeDetails->query("SELECT cc.logo FROM comp_contact_info cc");
            $logo_url = isset($arr_comp_contact[0]['cc']['logo']) ? $arr_comp_contact[0]['cc']['logo'] : '';
            $action = $event;
            // debug($action);
            $actions = trim($action);
            $mailcontent = $this->EmployeeDetails->query("SELECT * FROM `Email_Content` WHERE `mail_type` = 'wishes' AND `Type` = '$actions'");
            //  debug($mailcontent);
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            $mail->SMTPDebug = false;
            $mail->isSMTP();
            $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';
            $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('mypayrollmaster@office24.online');
            $mail->addAddress('sales@greatleap.tech'); // Fixed recipient - company mail
            $mail->addReplyTo('mypayrollmaster@office24.online');
            $mail->isHTML(true);
            $pic = $mailcontent['0']['Email_Content']['Image'];
            //$image = 'https://v1.mypayrollmaster.online/img/6.png'; //Edited by Akshay on 28-11-2023
            // $profileimage = 'https://qaoci.mypayrollmaster.online/User/profile';
            $profileimage = 'https://v1.mypayrollmaster.online/' . $avatar;
            if ($avatar != 'img/placeholdermen.jpeg') {
                // $photo = '<div class="profile-image" style="padding-left: 20px; padding-bottom: 30px;left:20px;bottom:20px;">
                //                 <img src="' . $profileimage . '" alt="Profile Image" style="max-width: 200px; max-height: 200px; width: auto; height: auto; object-fit: contain;">
                //             </div>';
                // $photo = '<div class="profile-image" style="width:150px;height:150px;position: absolute; left: 20px; bottom: 0px; padding-left: 20px; z-index: 999; flex: 1;">
                //             <img src="'.$profileimage.'" alt="Profile Image" style="max-width: 150px; max-height: 150px; width: auto; height: auto; object-fit: contain;padding-bottom:0px;">
                //         </div>';
                if ($event == 'Work Anniversary') {
                    $photo = '';
                    if ($avatar) {
                        $photo = '<div class="profile-image" style="text-align: left; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                                <img src="' . $profileimage . '" alt="Profile Image" style="max-width: 100px; max-height: 130px; width: auto; height: auto; object-fit: contain; padding-top: 0px; padding-left: 10px;">
                            </div>
                            ';
                    }
                } else {
                    $photo = '<div class="profile-image" style="text-align: right; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                                <img src="' . $profileimage . '" alt="Profile Image" style="max-width: 150px; max-height: 100px; width: auto; height: auto; object-fit: contain; padding-bottom: 0px;">
                            </div>
                            ';
                }
            } else {
                if ($event == 'Work Anniversary') {
                    $photo = '<div class="profile-image" style="text-align: left; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                               
                            </div>
                            ';
                } else {
                    $photo = '<div class="profile-image" style="text-align: right; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                               
                            </div>
                            ';
                }
            }

            $logo_img = '';

            // if($logo_url != 'img/placeholdermen.jpeg'){
            //     $logo = 'https://qaoci.mypayrollmaster.online/'.$logo_url;
            //     $logo_img = '<div class="logo" style="text-align: left; width: 200px; height: 50px; overflow: hidden; margin-right:400px;">
            //                     <img src="'.$logo.'" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
            //                 </div>';
            // }else{
            //     $logo_img = '';
            // }



            //Edited by Akshay on 28-11-2023 
            if ($event == 'Work Anniversary') {
                // $image = 'http://qaoci.mypayrollmaster.online/newlogin/img/pink_flower_frame_edited.jpg';//Edited by Akshay on 28-11-2023
                // $image = 'https://v1.mypayrollmaster.online/img/80.png';
                $image = 'http://qaoci.mypayrollmaster.online/img/Anniversary_Wish.png'; // Edited by Akshay on 18-2-2025
                $color = '#fff';
                $heading = '';
                $heading = '<h3 style="margin-top:0px;text-align:center;color:#AA336A;">' . $heading . '</h3>';
                $heading_colour = '#fff';
                $wish_content = ' ' . $years . '<span style="font-size: 80%;">' . $suffix . '</span> ';
                if (strpos($email, '@gmail.com') !== false) {
                    $wish_content = '<div id="content" class="text-over-image" style=";color: #e4e4e2; text-align: center; font-size: 11px;  width: 500px;height:250px; padding: 27px 0 0 0;">
                    ' . $years . '<sup>' . $suffix . '<sup><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> 
                        </div>';
                } else {
                    $wish_content = '<div class="text-over-image" style=";color: #e4e4e2; text-align: center; font-size: 11px;  width: 500px;height:250px; padding: 28.5px 0 0 0;">
                    ' . $years . '<span>' . $suffix . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> 
                        </div>';
                }

                $name_div = '<div  class="text-over-image" style="color: #ff0000; text-align: left;padding-left:260px; font-size: 18px; padding-top: 10px;font-weight:bold;">
            <i> ' . '' . '</i>
        </div>';
            } else {
                // $image = 'https://v1.mypayrollmaster.online/img/6.png';
                $image = 'http://qaoci.mypayrollmaster.online/img/Birthday_wish.png'; // Edited by Akshay on 18-2-2025
                $color = '#63cf22';
                $heading = '';
                $heading_colour = '#333333';
                $wish_content = '';

                $name_div = '<div  class="text-over-image" style="color: #332c2b;position: relative; z-index: 999; text-align:center; font-weight: 900; width: 100%; padding-top:30%;">
                                ' . $name . '
                            </div>';
            }

            $mail->Subject = $event . ' of ' . $name;

            if ($event == 'Work Anniversary') {
                $mail->MsgHTML('
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Work Anniversary Wish</title>
                <style>
                    @media screen and (max-width: 480px) {
                     body{
                        height: 300px !important;
                         }
                         #back {
                width: 300px !important;
                height: 300px !important;
            }
            .profile-image img {
                width: 127px!important; 
                height: 125px!important;
                margin-left:28%!important;
            }
                #content{
                margin-bottom:26%!important;
                padding-left:11%!important; 
                font-size: 9px!important; 
                width: 300px!important; 
                height:auto!important; 
                padding-top:10px!important;
            }
                        }
                </style>
                </head>
                    <body style="margin: 0; padding: 0; width: 100%; height: 500px;">
                        <div id="back" style="position: relative; width: 500px; height: 500px; background: url(' . $image . ') no-repeat center center; background-size: contain;">
                            <div style="position: relative; width: 100%; height: 100%;">
                            ' . $photo . '
                            ' . $wish_content . '
                             </div>
                        </div>
                    </body>

                </html> <div style="font-size: 15px; color: #ff0000;padding-top:20px;font-weight:bold;padding-left:10px;">' . $remark . '</div>
            ');
            } else {
                $mail->MsgHTML('
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Birthday Wish</title>
                    <style>
                        @media screen and (max-width: 480px) {
                        body{
                        height:auto!important;
                         }
                        #back{
                        width:300px!important;
                        height:300px!important;
                         }
                                .profile-image {
                                margin-top: 8%!important;
                            }
                            .profile-image img {
                                width: 100px !important;
                                height: 100px !important;
                            }
                        }
                    </style>
                </head>
                <body style="margin: 0; padding: 0; width: 100%; height: 500px;">

                <div id="back" style="position: relative; width: 500px; height: 500px; background: url(' . $image . ') no-repeat center center; background-size: contain;">                    
                    <div style="position: relative; width: 100%; height: 100%;">
                    ' . $name_div . '
                    ' . $photo . '
    
                    </div>
                </body>
                </html> <div style="font-size: 15px; color: #ff0000;padding-top:20px;font-weight:bold;padding-left:10px;">' . $remark . '</div>
            ');
            }
            // if (extension_loaded('gd')) {

            // }
            if (!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
                $this->Wish->useDbConfig = $this->Session->read('ds');
                date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)
                $arr_form_data = $this->request->data;
                $arr_form_data['created_by'] = $this->Session->read('login_user_id');
                $arr_form_data['type'] = $event;
                $arr_form_data['date'] = date('Y-m-d');
                $arr_form_data['remarks'] = $remark;
                $arr_form_data['emp_fkey'] = $emp_pkey;

                $result = $this->Wish->save($arr_form_data);

                echo 'Message has been sent';
            }
        } catch (Exception $ex) {
            var_dump('$ex->getMessage()');
        }
    }


    //Edited by Akshay on 10-6-2024
    public function automation_load_birthdays()
    {
        $this->autoRender = false;
        $date = date('Y-m-d');
        $db = 'mypayrol_mpm586';
        $company_code = 'KDNH';
        $table_joins[] = array(
            'table' => $db . '.user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
        $table_joins[] = array(
            'table' => $db . '.emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
        $table_joins[] = array(
            'table' => $db . '.wishes',
            'alias' => 'Wish',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('Wish.emp_fkey = Empproff.emp_fkey', 'Wish.type' => 'Birthday', 'Wish.date' => $date)
        );
        $table_joins1[] = array(
            'table' => $db . '.user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
        $table_joins1[] = array(
            'table' => $db . '.emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
        $table_joins1[] = array(
            'table' => $db . '.wishes',
            'alias' => 'Wish',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('Wish.emp_fkey = Empproff.emp_fkey', 'Wish.type' => 'Work Anniversary', 'Wish.date' => $date)
        );
        $conditions = array(
            array(
                "DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(curdate(), '%m-%d')",
                'EmployeeDetails.status' => 1
            )
        );
        $conditions1 = array(
            array(
                "DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(curdate(), '%m-%d')",
                'EmployeeDetails.status' => 1
            )
        );


        $this->CentralControl->setDataSource('controldb');
        $arr_company_db = $this->CentralControl->find('first', array(
            'fields' => 'CentralControl.*',
            'conditions' => array('company_code' => $company_code, 'end_date_effective' >= date('Y-M-D')/* , 'active'=>1 */)
        ));
        if (count($arr_company_db) == 1) {
            $user_name = isset($arr_company_db['CentralControl']['Admin_name']) ? $arr_company_db['CentralControl']['Admin_name'] : '';
            $user_db = isset($arr_company_db['CentralControl']['user_db']) ? $arr_company_db['CentralControl']['user_db'] : '';
            $user_pwd = isset($arr_company_db['CentralControl']['user_pwd']) ? $arr_company_db['CentralControl']['user_pwd'] : '';
            $companydb = array(
                'datasource' => 'Database/Mysql',
                'persistent' => false,
                'host' => '127.0.0.1',  //Configure::read('SERVERHOST'),
                'login' => $user_name,
                'password' => $user_pwd,
                'database' => $user_db,
                'prefix' => '',
            );
            ConnectionManager::create('companydb', $companydb);
            $this->Session->write("ds", 'companydb');

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        }

        $arr_employees_pics = array();
        $arr_employees_pics1 = array();
        try {
            $arr_employees_pics = $this->EmployeeDetails->find(
                "all",
                array(
                    "table" => $db . ".emp_details AS EmployeeDetails",
                    "fields" => array("DISTINCT EmployeeDetails.emp_pkey", "EmployeeDetails.date_of_birth",  "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date", "Wish.emp_fkey"),
                    "joins" => $table_joins,
                    "order" => "EmployeeDetails.date_of_birth DESC",
                    "conditions" => $conditions
                )
            );
        } catch (Exception $e) {
            // debug($e);
        }

        try {
            $arr_employees_pics1 = $this->EmployeeDetails->find(
                "all",
                array(
                    "table" => $db . ".emp_details AS EmployeeDetails",
                    "fields" => array("DISTINCT EmployeeDetails.emp_pkey", "EmployeeDetails.date_of_birth", "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date", "Wish.emp_fkey"),
                    "joins" => $table_joins1,
                    "order" => "Empproff.joining_date DESC",
                    "conditions" => $conditions1
                )
            );
        } catch (Exception $e) {
            //debug($e);
        }

        $today = date('Y-m-d');
        $url = 'https://v1.mypayrollmaster.online/Dashboard/convertimage';
        foreach ($arr_employees_pics as $employee) {
            $emp_pkey = isset($employee['EmployeeDetails']['emp_pkey']) ? $employee['EmployeeDetails']['emp_pkey'] : 0;
            $this->automation_convertimage($emp_pkey, 'Birthday', 'Happy birthday.');
        }
        foreach ($arr_employees_pics1 as $employee) {
            $emp_pkey = isset($employee['EmployeeDetails']['emp_pkey']) ? $employee['EmployeeDetails']['emp_pkey'] : 0;
            $this->automation_convertimage($emp_pkey, 'Work Anniversary', 'Happy work anniversary.');
        }

        if (isset($e)) {
            $succes = false;
            $message = 'Error occured while sending message';
        } else {
            $succes = true;
            $message = 'Sending wishes';
        }
        $this->Session->destroy();
        echo json_encode(array('success' => $succes, 'msg' => $message, 'emp' => count($arr_employees_pics), 'emp1' => count($arr_employees_pics1)));
    }

    public function automation_convertimage($emp_pkey = 0, $event = '', $remark = '')
    {

        $this->autorender = false;
        $this->layout = null;
        $this->render(false);


        try {

            //Edited by Aksahy on 28-11-2023
            $google_fonts = "Roboto";

            $data = array(
                'html' => '',
                'css' => '',
                'google_fonts' => $google_fonts,
                'url' => 'https://v1.mypayrollmaster.online/Dashboard/renderImageTempalte'
            );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://hcti.io/v1/image");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            curl_setopt($ch, CURLOPT_POST, 1);
            // Retrieve your user_id and api_key from https://htmlcsstoimage.com/dashboard
            curl_setopt($ch, CURLOPT_USERPWD, "3ffab2bd-74e7-4198-8153-263ff1c985d2" . ":" . "8ee1bf09-7791-48d6-96ae-e6ace86eafc4");

            $headers = array();
            $headers = array("Content-Type: application/x-www-form-urlencoded");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            $res = json_decode($result, true);
            //var_dump($res);
            // $this->sendemailtemplatedatabase($emp_pkey, $event, $remark); 
            $this->sendemailtemplate($emp_pkey, $event, $remark); //Edited by Akshay on 28-11-2023
            // $this->sendemailtemplate($res['url']);
            // https://hcti.io/v1/image/202dc04d-5efc-482e-8f92-bb51612c84cf
        } catch (Exception $e) {
            //  debug($e);
        }
    }
    //End

    // Edited by Alkshay on 3-2-2025
    public function getHrmMenusForAbs()
    {
        $resp_menu = array();

        $user_group = $this->Session->read('user_group');
        $this->set('user_group', $user_group);

        $context = isset($_REQUEST["context"]) ? $_REQUEST["context"] : "main";
        $root = isset($_REQUEST["root"]) ? $_REQUEST["root"] : 0;

        $conditions = array('active = "Y" ');
        $conditionss = array('Useraccess.active = "Y" and EmployeeMenu.active = "Y" and EmployeeMenu.is_default != "M" and EmployeeMenu.menu_id >= 1176');
        if ($context == "sub") {
            $conditions['parent_id'] = $root;
        }
        if ($user_group == 2) {
            /*
             * Employee Menu
             * Added by santhosh on 14 March 2015
             */
            $emp_fkey = $this->Session->read('emp_fkey');
            $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
            $menudb = $this->EmployeeMenu->find(
                "all",
                array(
                    'joins' => array(
                        array(
                            'table' => 'user_access',
                            'alias' => 'Useraccess',
                            'type' => 'INNER',
                            'conditions' => array(
                                'EmployeeMenu.menu_id = Useraccess.menu_id'
                            )
                        )
                    ),
                    'conditions' => array(
                        $conditionss,
                        'Useraccess.user_fkey' => $emp_fkey
                    ),
                    'order' => array('EmployeeMenu.menu_id ASC')
                )
            );

            $menu = array();


            foreach ($menudb as $key => $value) {
                $m = $value['EmployeeMenu'];
                $arr_menu = array();
                $arr_menu["id"] = $m["menu_id"];
                // $m["menu_url"];
                $arr_menu["url"] = $m["menu_url"];
                $arr_menu["text"] = $m["menu_title"];
                $arr_menu["iconCls"] = $m["iconCls"];
                $arr_menu["leaf"] = true;
                if (isset($menu[$m['parent_id']])) {
                    $menu[$m['parent_id']]["leaf"] = false;
                    $menu[$m['parent_id']]['children'][] = $arr_menu;
                } else {
                    $menu[$m['menu_id']] = $arr_menu;
                }
            }
            //debug($arr_menu);
        } else {
            //Admin menu
            $this->Menu->useDbConfig = $this->Session->read('ds');
            //$this->Menu->recover('tree');
            $menudb = $this->Menu->find("all", array("conditions" => $conditions));

            $menu = array();

            foreach ($menudb as $key => $value) {
                $m = $value['Menu'];
                $arr_menu = array();
                $arr_menu["id"] = ($context == "main") ? $m["menu_id"] : (($m["menu_url"]) ? $m["menu_url"] : "none_" . $m["menu_id"]);
                $arr_menu["url"] = $m["menu_url"];
                $arr_menu["text"] = $m["menu_title"];
                $arr_menu["iconCls"] = isset($m["iconCls"]) ? $m["iconCls"] : "fa fa-user";
                $arr_menu["leaf"] = true;
                // "xf007@FontAwesome";

                if (isset($menu[$m['parent_id']])) {
                    //$menu[$m['parent_id']]["leaf"] = false;
                    $menu[$m['parent_id']]['children'][] = $arr_menu;
                } else {
                    $arr_menu["leaf"] = true;
                    $menu[$m['menu_id']] = $arr_menu;
                }
            }
        }

        //   $resp_menu["success"] = true;
        $i = 0;
        if ($context == "main") {
            foreach ($menu as $key => $value) {
                $resp_menu[$i++] = $value;
            }
        } else {
            foreach ($menu as $key => $value) {
                $resp_menu[$i++] = $value;
            }
        }
        //	debug($resp_menu);
        if ($context == "main") {
            //echo json_encode($resp_menu);
            //debug($resp_menu);
            //$conditions['parent_id'] = $root;
        } else if ($root) {
            //echo json_encode($resp_menu);
            //debug($resp_menu);
        }

        return $resp_menu;
    }
    // End
    //edited by athira on 10-02-2025
    public function sendFormEmail()
    {
        $this->autoRender = false; // Disable view rendering
        $this->response->type('json'); // Set response type

        if ($this->request->is('post')) {
            try {
                // Validate and process data
                $senderEmail = $this->request->data['mail'];
                $description = $this->request->data['description'];

                if (empty($senderEmail) || empty($description)) {
                    throw new Exception("All fields are required");
                }

                if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email format");
                }

                // Reuse PHPMailer config from sendemailtemplate
                App::import('Vendor', 'PHPMailer', ['file' => 'PHPMailer/PHPMailerAutoload.php']);
                $mail = new PHPMailer;
                $mail->SMTPDebug = false;
                $mail->isSMTP();
                $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';
                $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Set email addresses
                $mail->setFrom('mypayrollmaster@office24.online'); // Sender address from form -verified mail id 
                $mail->addReplyTo($senderEmail); // clients mail
                $mail->addAddress('sales@greatleap.tech'); // Fixed recipient - company mail


                // Build email content
                $mail->Subject = "Upgrade Plan Request from $senderEmail";
                $mail->Body = "
                        <html>
                        <body>
                            <p>Dear Team,</p>
                            <p>A new upgrade request has been received.</p>
                            
                            <h3>User Details:</h3>
                            <ul>
                                <li><strong>Email : </strong> $senderEmail</li>
                                <li><strong>Message : </strong><br>$description</li>
                            </ul>
                            
                            <h3>Upgrade Plan Request:</h3>
                            <p>The user is interested in upgrading their current plan. Please review their request and provide further assistance.</p>
                        </body>
                        </html>
                    ";

                $mail->isHTML(true); // Send as HTML
                if (!$mail->send()) {
                    throw new Exception("Mailer Error: " . $mail->ErrorInfo);
                }

                // Success response
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Email sent successfully'
                ]);
            } catch (Exception $ex) {
                // Error response
                echo json_encode([
                    'status' => 'error',
                    'message' => $ex->getMessage()
                ]);
            }
        }
    }
    //end

    // Edited by Akshay on 1-3-2025
    public function createOverlayImage()
    {
        $this->autoRender = FALSE;

        $backgroundUrl = 'http://qaoci.mypayrollmaster.online/img/Anniversary_Wish.png';
        $profileImageUrl = 'https://qaoci.mypayrollmaster.online/img/avatar/d3aad1dd16235d7fb8deb7c95a6fb798add9d369.png';

        // Fetch and create background image
        $bgData = file_get_contents($backgroundUrl);
        if ($bgData === false) {
            die("Failed to load background image.");
        }
        $background = imagecreatefromstring($bgData);

        // Fetch and create profile image
        $profileData = file_get_contents($profileImageUrl);
        if ($profileData === false) {
            die("Failed to load profile image.");
        }
        $profile = imagecreatefromstring($profileData);

        // Resize profile image to 500x500 px
        $size = 1265;
        $profileResized = imagecreatetruecolor($size, $size);
        imagesavealpha($profileResized, true);
        $transparent = imagecolorallocatealpha($profileResized, 0, 0, 0, 127);
        imagefill($profileResized, 0, 0, $transparent);
        imagecopyresampled($profileResized, $profile, 0, 0, 0, 0, $size, $size, imagesx($profile), imagesy($profile));

        // Create circular mask dynamically
        $mask = imagecreatetruecolor($size, $size);
        imagesavealpha($mask, true);
        $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
        imagefill($mask, 0, 0, $transparent);
        $white = imagecolorallocate($mask, 255, 255, 255);
        imagefilledellipse($mask, $size / 2, $size / 2, $size, $size, $white);

        // Apply circular mask
        for ($x = 0; $x < $size; $x++) {
            for ($y = 0; $y < $size; $y++) {
                $alpha = (imagecolorat($mask, $x, $y) >> 24) & 0xFF;
                if ($alpha > 0) {
                    imagesetpixel($profileResized, $x, $y, imagecolorallocatealpha($profileResized, 0, 0, 0, 127));
                }
            }
        }

        // Destroy the mask
        imagedestroy($mask);

        // Get background dimensions
        $bgWidth = imagesx($background);
        $bgHeight = imagesy($background);

        // Set profile image position (centered)
        $profileX = ($bgWidth - $size) / 2;
        $profileX -= 30;
        $profileY = 600;

        // Merge the circular profile onto the background
        imagecopy($background, $profileResized, $profileX, $profileY, 0, 0, $size, $size);

        // Add text below profile image
        $text = "1st";
        $fontSize = 100; // Adjust size as needed
        $angle = 0;
        if (!file_exists(WWW_ROOT . 'fonts' . DS . 'Arialn.ttf')) {
            die("Font file not found!");
        }
        $fontFile = WWW_ROOT . 'fonts' . DS . 'Arial.ttf'; // Ensure this file exists
        $textColor = imagecolorallocate($background, 255, 0, 0); // Red for visibility

        // Calculate text position
        $textX = $profileX + ($size / 2) - 50; // Centered below profile
        $textY = $profileY + $size + 150; // Position below the profile image

        imagettftext($background, $fontSize, $angle, $textX, $textY, $textColor, $fontFile, $text);

        // Save final image
        $outputPath = WWW_ROOT . 'img' . DS . 'generated_card.png';
        imagepng($background, $outputPath);

        // Free memory
        imagedestroy($background);
        imagedestroy($profile);
        imagedestroy($profileResized);

        return $outputPath;
    }



    function convertSquareToCircle()
    {
        $this->autoRender = FALSE;

        $inputImageUrl = 'https://qaoci.mypayrollmaster.online/img/avatar/d3aad1dd16235d7fb8deb7c95a6fb798add9d369.png';
        $outputImagePath = WWW_ROOT . 'img' . DS . 'circular_profile.png';

        // Load image from URL
        $imageData = file_get_contents($inputImageUrl);
        if ($imageData === FALSE) {
            throw new Exception("Failed to load image from URL.");
        }

        $image = imagecreatefromstring($imageData);
        if ($image === FALSE) {
            throw new Exception("Invalid image data.");
        }

        // Get original dimensions
        $width = imagesx($image);
        $height = imagesy($image);
        $size = min($width, $height); // Ensure square crop

        // Create a blank true color image with transparency
        $circleImage = imagecreatetruecolor($size, $size);
        imagesavealpha($circleImage, true);
        $transparent = imagecolorallocatealpha($circleImage, 0, 0, 0, 127);
        imagefill($circleImage, 0, 0, $transparent);

        // Create circular mask
        $mask = imagecreatetruecolor($size, $size);
        imagesavealpha($mask, true);
        $maskTransparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
        imagefill($mask, 0, 0, $maskTransparent);
        $white = imagecolorallocate($mask, 255, 255, 255);
        imagefilledellipse($mask, $size / 2, $size / 2, $size, $size, $white);

        // Resize the image to fit within the circular area
        imagecopyresampled($circleImage, $image, 0, 0, ($width - $size) / 2, ($height - $size) / 2, $size, $size, $size, $size);

        // Apply the mask using the alpha channel
        for ($x = 0; $x < $size; $x++) {
            for ($y = 0; $y < $size; $y++) {
                $maskPixel = imagecolorat($mask, $x, $y);
                if ($maskPixel == $maskTransparent) {
                    $color = imagecolorat($circleImage, $x, $y);
                    imagesetpixel($circleImage, $x, $y, $transparent);
                }
            }
        }

        // Save final circular image with transparency
        if (!imagepng($circleImage, $outputImagePath)) {
            throw new Exception("Failed to save the circular image.");
        }

        // Free memory
        imagedestroy($image);
        imagedestroy($circleImage);
        imagedestroy($mask);

        return $outputImagePath;
    }

   public function promotion()
{
    $this->Promotion->useDbConfig = $this->Session->read('ds');

    $startDate = date('Y-m-01'); // first day of current month
    $endDate = date('Y-m-t');    // last day of current month

    $conditions = [
        'promotion_status' => 'APPLIED',
        'created_date >=' => $startDate,
        'created_date <=' => $endDate
    ];

    $fields = ['Promotion.*', 'emp_details.first_name', 'emp_details.last_name', 'emp_info.employee_id','emp_info.department','emp_info.designation', 'Branch.branch_name'];

    $joins = [
        [
            'table' => 'emp_details',
            'alias' => 'emp_details',
            'type' => 'LEFT',
            'conditions' => 'Promotion.emp_fkey = emp_details.emp_pkey'
        ],
        [
            'table' => 'employee_info',
            'alias' => 'emp_info',
            'type' => 'LEFT',
            'conditions' => 'Promotion.emp_fkey = emp_info.emp_pkey'
        ],
        [
            'table' => 'branches',
            'alias' => 'Branch',
            'type' => 'LEFT',
            'conditions' => 'emp_details.branch_code = Branch.branch_code'
        ],
    ];

    $data = $this->Promotion->find('all', [
        'conditions' => $conditions,
        'fields' => $fields,
        'joins' => $joins,
        'order' => ['emp_info.EmpName ASC']
    ]);

    $this->set('promotionList', $data);
    // debug($data);
} 

public function expense(){
 $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');

// Current month's date range
$startDate = date('Y-m-01');
$endDate = date('Y-m-t');

// Conditions for 'APPLIED' expenses in the current month
$conditions = [
    'EmployeeExpenses.expense_status' => 'Applied',
    'EmployeeExpenses.created_date >=' => $startDate,
    'EmployeeExpenses.created_date <=' => $endDate
];

// Fields to select
$fields = [
    'EmployeeExpenses.*',
    'emp_details.first_name',
    'emp_details.last_name',
    'emp_info.employee_id',
    'Branch.branch_name'
];

// Joins
$joins = [
    [
        'table' => 'emp_details',
        'alias' => 'emp_details',
        'type' => 'LEFT',
        'conditions' => 'EmployeeExpenses.emp_fkey = emp_details.emp_pkey'
    ],
    [
        'table' => 'employee_info',
        'alias' => 'emp_info',
        'type' => 'LEFT',
        'conditions' => 'EmployeeExpenses.emp_fkey = emp_info.emp_pkey'
    ],
    [
        'table' => 'branches',
        'alias' => 'Branch',
        'type' => 'LEFT',
        'conditions' => 'emp_details.branch_code = Branch.branch_code'
    ]
];

// Run the query
$expenseList = $this->EmployeeExpenses->find('all', [
    'conditions' => $conditions,
    'fields' => $fields,
    'joins' => $joins,
    'order' => ['emp_info.EmpName ASC']
]);

// Pass to view
$this->set('expenseList', $expenseList);


}

public function AttendanceRegularisation(){
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    // $data=$this->EmployeeDetails->query("SELECT employee_regularaization.*,employee_info.EmpName,employee_info.employee_id,employee_info.branch FROM employee_regularaization LEFT JOIN employee_info ON employee_info.emp_id=employee_regularaization.empid");
      $data = $this->EmployeeDetails->query("
    SELECT 
        er.*, 
        ei.EmpName, 
        ei.employee_id, 
        ei.branch 
    FROM 
        employee_regularaization er
    LEFT JOIN 
        employee_info ei ON ei.emp_id = er.empid
    WHERE 
        DATE_FORMAT(er.LOGDATE, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m')
    ORDER BY 
        ei.EmpName ASC
");

// Pass to view
$this->set('data', $data);


}
//edited by athira on 22-07-2025
public function Attendanceverification(){
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
   $data = $this->EmployeeDetails->query("
    SELECT 
        attendance_register.*, 
        employee_info.EmpName,employee_info.employee_id,employee_info.branch,employee_info.department,employee_info.designation,employee_info.joining_date
    FROM 
        attendance_register 
    LEFT JOIN 
        employee_info ON employee_info.emp_pkey = attendance_register.emp_fkey
    WHERE 
        attendance_register.record_status = '1' 
        AND attendance_register.month_year = DATE_FORMAT(CURRENT_DATE, '%Y-%m') ORDER BY 
        employee_info.EmpName ASC
");

   
// Pass to view
$this->set('data', $data);


}
//end


// End
} // Close the DashboardNewController class
?>
