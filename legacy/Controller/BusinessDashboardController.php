<?php

class BusinessDashboardController extends AppController
{
    public $uses = array("EmployeeDetails", "Promotion", "EmployeeExpenses", "Wish", "Earlyin", "Latein", "Earlyout", "Lateout", "EmployeeProfessionalDetails", "DeviceAttendance", "IssueReport", "LeaveRequests", "EmployeeDetails", "Device", "Useraccess", "AttendancePunch", "MobileUserauditor", "MobileUserTracking", "SettingsRunner", "GeneralSettings", "ReportAudit", "Wish", "EmailContent", "CentralControl"); //Edited by Akshay on 13-6-2024
    public $components = array('DatatablesManagement');

    public function index()
    {
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->Wish->useDbConfig = $this->Session->read('ds');
        $this->CentralControl->setDataSource('controldb');
        $this->loadModel('CentralControl'); 
        $company_code = $this->Session->read('company_code'); 
        $company = $this->CentralControl->find('first', [
            'conditions' => ['CentralControl.company_code' => $company_code],
            'fields' => ['CentralControl.company_name']
        ]);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $activeEmployees = $this->EmployeeDetails->query("
                SELECT COUNT(*) as total 
                FROM emp_details WHERE emp_details.status=1
            ");
        $activeEmployeeCount = $activeEmployees[0][0]['total'];
        $this->set('activeEmployee', $activeEmployeeCount);
        $user_group = $this->Session->read('user_group'); 
        $company = $this->EmployeeDetails->query("
    SELECT 
        business_name,
        logo,address,city,pincode,state 
    FROM comp_contact_info
");

        if (!empty($company)) {
            $company_name   = isset($company[0]['comp_contact_info']['business_name']) ? $company[0]['comp_contact_info']['business_name'] : '';
            $company_logo   = isset($company[0]['comp_contact_info']['logo']) ? $company[0]['comp_contact_info']['logo'] : '';
            $company_address = isset($company[0]['comp_contact_info']['address']) ? $company[0]['comp_contact_info']['address'] : '';
            $company_city   = isset($company[0]['comp_contact_info']['city']) ? $company[0]['comp_contact_info']['city'] : '';
            $company_pincode = isset($company[0]['comp_contact_info']['pincode']) ? $company[0]['comp_contact_info']['pincode'] : '';
            $company_state  = isset($company[0]['comp_contact_info']['state']) ? $company[0]['comp_contact_info']['state'] : '';

            $this->set('company_name', $company_name);
            $this->set('company_logo', $company_logo);
            $this->set('company_address', $company_address);
            $this->set('company_city', $company_city);
            $this->set('company_pincode', $company_pincode);
            $this->set('company_state', $company_state);
        }

        if ($this->Session->read('ds') == null) {
            $this->redirect(array('controller' => 'Site', 'action' => 'login'));
        }


        if ($user_group == 2 && $company_code != 'DEMO') { 

            $this->Useraccess->useDbConfig = $this->Session->read('ds');
            $emp_fkey = $this->Session->read('emp_fkey');
            $useracess = $this->Useraccess->query("select * from user_access as Useraccess where user_fkey = '$emp_fkey' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            if ($access == 'Y') {
                
                $this->hierarchydashboard();
            } else {
              
                $this->empdashboard();
            }
        } else {
            
            $user_group = $this->Session->read("user_group");
            $company_code = $this->Session->read('company_code');
            

            $emp_pkey = $this->Session->read('emp_fkey');

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $count = $this->EmployeeDetails->find("count", array("conditions" => array("emp_id !=" => null, 'status' => 1)));
           
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
        
            $this->set("count", $count);

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

            $colorMap = [
                'Under 20' => '#a37182',
                '20-30'    => '#60a69f',
                '30-40'    => '#41ec39',
                '40-50'    => '#78b6d9',
                '50-60'    => '#6682bb',
                '60+'      => '#E91E63'

            ];


            $donutData = [];
            $colorData = [];

            foreach ($classificationData as $row) {
                $classification = !empty($row[0]['age_group']) ? $row[0]['age_group'] : 'Unknown';
                $employeeCount = !empty($row[0]['employee_count']) ? (int) $row[0]['employee_count'] : 0;

                $donutData[] = [
                    'label' => $classification,
                    'value' => $employeeCount
                ];

                $colorData[] = $colorMap[$classification] ? $colorMap[$classification] : '#CCCCCC';
            }

            $this->set('donutData', json_encode($donutData));
            $this->set('donutColors', json_encode($colorData));


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

            $formattedDeptStats = array_map(function ($stat) {
                return array(
                    'department' => trim(strval($stat['d']['department'])), 
                    'value' => intval($stat[0]['value'])  
                );
            }, $departmentStats);

            $this->set('departmentStats', json_encode($formattedDeptStats));



            $user_group = $this->Session->read('user_group');
            $company_code = $this->Session->read('company_code');

            if ($this->Session->read('ds') == null) {
                $this->redirect(array('controller' => 'Site', 'action' => 'login'));
            }


            $genderRaw = $this->EmployeeDetails->query("
 SELECT 
    classification AS classification, 
    COUNT(*) AS value
FROM emp_details
WHERE classification IS NOT NULL
  AND classification != ''
  AND status = 1 
GROUP BY classification;
");

            $genderData = [];
            foreach ($genderRaw as $row) {
                $gender = ucfirst(trim($row['emp_details']['classification']));

                if ($gender === 'Other') {
                    $gender = 'Others';
                }

                $genderData[] = [
                    'label' => $gender,
                    'value' => (int)$row[0]['value']
                ];
            }

            $this->set('genderData', json_encode($genderData));

            $salaryData = $this->EmployeeDetails->query("  
   SELECT  
    DATE_FORMAT(STR_TO_DATE(month_year, '%Y-%m'), '%b-%Y') AS formatted_month,   
    SUM(monthly_ctc) AS salary  
FROM 
    payroll_master  
WHERE 
    action = 'Approved'  
    AND month_year >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m')
    AND month_year <= DATE_FORMAT(CURDATE(), '%Y-%m')
GROUP BY 
    formatted_month  
ORDER BY 
    STR_TO_DATE(month_year, '%Y-%m') ASC;
");

            $lineData = [];
            foreach ($salaryData as $row) {
                $lineData[] = [
                    'month' => $row[0]['formatted_month'],  
                    'value' => (float) $row[0]['salary']
                ];
            }

            $this->set('chartData', json_encode($lineData));
     
            $designationRaw = $this->EmployeeDetails->query("
  SELECT 
    d.desig_name AS designation,
    COUNT(ed.emp_pkey) AS value
FROM designation d
LEFT JOIN emp_proff ep 
       ON d.desig_code = ep.designation
LEFT JOIN emp_details ed
       ON ed.emp_pkey = ep.emp_fkey
      AND ed.status = 1
WHERE d.status = 1
GROUP BY d.desig_name
ORDER BY value DESC, d.desig_name ASC;
");

            $designationData = [];
            foreach ($designationRaw as $row) {
                $designationData[] = [
                    'designation' => $row['d']['designation'],  
                    'value' => (int)$row[0]['value']         
                ];
            }

          
            $this->set('designationData', json_encode($designationData));


            if ($this->Session->read('ds') == null) {
                $this->redirect(['controller' => 'Site', 'action' => 'login']);
            }

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

           
            $absent_count = $absentData[0][0]['absent_count'];

      
            $pfData = $this->EmployeeDetails->query("
    SELECT COUNT(*) AS pf_count
    FROM emp_details
    WHERE pf IS NULL OR pf = '';
");

            $pf_count = $pfData[0][0]['pf_count'];


            $leavesData = $this->EmployeeDetails->query("
    SELECT COUNT(*) AS leave_count
    FROM emp_detail_timeattandance
    WHERE yearmonth = '2024-05-01' 
    AND leaves IS NOT NULL;
");

           
            $leave_count = $leavesData[0][0]['leave_count'];
          
            $this->set('absent_count', $absent_count);
            $this->set('pf_count', $pf_count);
            $this->set('leave_count', $leave_count);


            $branchData = $this->EmployeeDetails->query("
      SELECT 
    b.branch_name, 
    COUNT(e.emp_pkey) AS employee_count
FROM branches b
LEFT JOIN emp_details e 
       ON e.branch_code = b.branch_code 
      AND e.status = 1   -- employee status check here
WHERE b.status = 1        -- branch active
GROUP BY 
    b.branch_code, b.branch_name
ORDER BY 
    employee_count DESC, 
    b.branch_name ASC
");
          
            $branchChartData = [];

            if (!empty($branchData) && is_array($branchData)) {
                foreach ($branchData as $row) {
                    $label = isset($row['b']['branch_name']) ? trim($row['b']['branch_name']) : 'Unknown';
                    $value = isset($row[0]['employee_count']) ? (int)$row[0]['employee_count'] : 0;

                    $branchChartData[] = [
                        'branch' => $label, 
                        'value' => $value
                    ];
                }
            }

            $this->set('branchempdata', json_encode($branchChartData));
            
            //edited  by bindu v 31-12-2025
            // $availableMonths = [];
            // for ($i = 11; $i >= 0; $i--) {
            //     $monthYear = date('Y-m', strtotime("-$i month"));
            //     $monthName = date('F Y', strtotime($monthYear . '-01'));
            //     $availableMonths[] = [
            //         'value' => $monthYear,
            //         'label' => $monthName
            //     ];
            // }
            // $this->set('availableMonths', $availableMonths);

            $availableMonths = [];

            $baseDate = date('Y-m-01'); // First day of current month

            for ($i = 11; $i >= 0; $i--) {
                $date = date('Y-m-01', strtotime("$baseDate -$i months"));

                $availableMonths[] = [
                    'value' => date('Y-m', strtotime($date)),
                    'label' => date('F Y', strtotime($date))
                ];
            }

            // debug($availableMonths);
            $this->set('availableMonths', $availableMonths);

            //end


            $monthlyCTCRaw = $this->EmployeeDetails->query("
    SELECT 
        DATE_FORMAT(STR_TO_DATE(CONCAT(s.month_year, '-01'), '%Y-%m-%d'), '%b-%Y') AS formatted_month,
        s.month_year,
        SUM(s.salary_amount) AS total_ctc
    FROM emp_salary_slip AS s
    WHERE 
 (s.end_date_effective IS NULL OR s.end_date_effective = '')
        AND STR_TO_DATE(CONCAT(s.month_year, '-01'), '%Y-%m-%d')
            BETWEEN DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
            AND LAST_DAY(CURDATE())
        AND s.head_operator = 'Addition'
    GROUP BY s.month_year, formatted_month
    ORDER BY s.month_year
");

            $monthlyCTCData = [];
            foreach ($monthlyCTCRaw as $row) {
                $monthlyCTCData[] = [
                    'month' => $row[0]['formatted_month'],
                    'ctc' => (float)$row[0]['total_ctc']
                ];
            }
            $this->set('monthlyCTCData', json_encode($monthlyCTCData));


            $CTCBreakupsalary = $this->EmployeeDetails->query("
SELECT 
    DATE_FORMAT(STR_TO_DATE(CONCAT(s.month_year, '-01'), '%Y-%m-%d'), '%M') AS month_name,
    SUM(CASE WHEN shi.head_fkey = 1 THEN s.salary_amount ELSE 0 END) AS fixed_salary,
    SUM(CASE WHEN shi.head_fkey IN (9, 10) THEN s.salary_amount ELSE 0 END) AS variable_salary,
    SUM(CASE WHEN shi.head_fkey = 4 THEN s.salary_amount ELSE 0 END) AS employer_contribution
FROM emp_salary_slip AS s
JOIN salary_head_items AS shi 
    ON s.salary_head_item_fkey = shi.salary_head_item_pkey
WHERE 
    shi.status = 1
    AND s.month_year IN (SELECT DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m'))
    AND s.end_date_effective IS NULL
GROUP BY month_name;
");
            $ctcData = isset($CTCBreakupsalary[0][0]) ? $CTCBreakupsalary[0][0] : [];
$this->set('CTCBreakupsalary', json_encode($ctcData));

            $salaryByDept = $this->EmployeeDetails->query("
    SELECT 
        ei.department,
        MONTHNAME(STR_TO_DATE(CONCAT(s.month_year, '-01'), '%Y-%m-%d')) AS month_name,
        SUM(s.salary_amount) AS total_salary
    FROM emp_salary_slip AS s
    JOIN employee_info AS ei 
        ON s.emp_fkey = ei.emp_pkey
    WHERE 
        s.month_year = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')
        AND s.end_date_effective IS NULL
        AND s.head_operator = 'Addition'
    GROUP BY 
        ei.department, month_name
    ORDER BY 
        total_salary DESC
");

            $salaryDeptChartData = [];
            $monthName = ''; 
            foreach ($salaryByDept as $row) {
                $dept = $row['ei']['department'];
                $total = (float)$row[0]['total_salary'];
                $monthName = $row[0]['month_name']; 
                $salaryDeptChartData[] = [
                    'label' => $dept,
                    'value' => $total
                ];
            }

            $this->set([
                'salaryDeptChartData' => json_encode($salaryDeptChartData),
                'monthName' => $monthName
            ]);

          
            $salaryByDesignation = $this->EmployeeDetails->query("
     SELECT 
         ei.designation,
        MONTHNAME(STR_TO_DATE(CONCAT(s.month_year, '-01'), '%Y-%m-%d')) AS month_name,
        SUM(s.salary_amount) AS total_salary
    FROM emp_salary_slip AS s
    JOIN employee_info AS ei 
        ON s.emp_fkey = ei.emp_pkey
    WHERE 
        s.month_year = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')
        AND s.end_date_effective IS NULL
        AND s.head_operator = 'Addition'
    GROUP BY 
       ei.designation, month_name
    ORDER BY 
        total_salary DESC
");

            $designationChartData = [];
            $monthName = ''; 
            foreach ($salaryByDesignation as $row) {
                $designation = isset($row['ei']['designation']) && $row['ei']['designation'] !== ''
                    ? $row['ei']['designation']
                    : 'Unknown';
                $monthName = $row[0]['month_name']; 
                $total = isset($row[0]['total_salary']) ? (float)$row[0]['total_salary'] : 0;

                $designationChartData[] = [
                    'label' => $designation,
                    'value' => $total
                ];
            }

            $this->set([
                'designationChartData' => json_encode($designationChartData),
                'monthNamedesignation' => $monthName
            ]);

            $salaryByBranch = $this->EmployeeDetails->query("
   SELECT 
        ei.branch,
        MONTHNAME(STR_TO_DATE(CONCAT(s.month_year, '-01'), '%Y-%m-%d')) AS month_name,
        SUM(s.salary_amount) AS total_salary
    FROM emp_salary_slip AS s
    JOIN employee_info AS ei 
        ON s.emp_fkey = ei.emp_pkey
    WHERE 
        s.month_year = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')
        AND s.end_date_effective IS NULL
        AND s.head_operator = 'Addition'
    GROUP BY 
        ei.branch, month_name
    ORDER BY 
        total_salary DESC
");

            $branchChartData = [];
            $monthName = '';
            foreach ($salaryByBranch as $row) {
                $branch = isset($row['ei']['branch']) && $row['ei']['branch'] !== ''
                    ? $row['ei']['branch']
                    : 'Unknown';
                $monthName = $row[0]['month_name']; 
                $total = isset($row[0]['total_salary']) ? (float)$row[0]['total_salary'] : 0;

                $branchChartData[] = [
                    'label' => $branch,
                    'value' => $total
                ];
            }

        
            $this->set([
                'branchChartData' => json_encode($branchChartData),
                'monthNamebranch' => $monthName
            ]);

            $defaultMonth = date('Y-m');
            $this->set('defaultMonth', $defaultMonth);
        }
          $coverageStats = $this->EmployeeDetails->query("
            SELECT 
                SUM(CASE WHEN (pf IS NULL OR pf = '') THEN 1 ELSE 0 END) as uan_not_provided,
                SUM(CASE WHEN (esi IS NULL OR esi = '') THEN 1 ELSE 0 END) as esi_not_covered,
                SUM(CASE WHEN (pan_no IS NULL OR pan_no = '') THEN 1 ELSE 0 END) as pan_not_provided
            FROM emp_details 
            WHERE status = 1
        ");
         
         $today = date('Y-m-d');
            $startDate = date('Y-m-01');       
            $endDate = date('Y-m-t'); 

   
       $pendingLeaves = $this->EmployeeDetails->query("
            SELECT COUNT(*) AS total_pending
FROM leaveentries
WHERE LEAVESTATUS='Applied'


        ");



        $coverageStatsArray = array(
            'uan_not_provided' => $coverageStats[0][0]['uan_not_provided'],
            'esi_not_covered' => $coverageStats[0][0]['esi_not_covered'],
            'pan_not_provided' => $coverageStats[0][0]['pan_not_provided'],
            'total_leaves' => $pendingLeaves[0][0]['total_pending']
        );

        $this->set('coverageStats', $coverageStatsArray);

    }

    public function getCTCBreakup()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $monthYear = isset($this->request->query['month_year']) ? $this->request->query['month_year'] : date('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
            $monthYear = date('Y-m');
        }

        $CTCBreakupsalary = $this->EmployeeDetails->query("
SELECT 
    DATE_FORMAT(STR_TO_DATE(CONCAT(s.month_year, '-01'), '%Y-%m-%d'), '%M %Y') AS month_name,
    SUM(CASE WHEN shi.head_fkey = 1 THEN ABS(s.salary_amount) ELSE 0 END) AS fixed_salary,
    SUM(CASE WHEN shi.head_fkey IN (9, 10) THEN ABS(s.salary_amount) ELSE 0 END) AS variable_salary,
    SUM(CASE WHEN shi.head_fkey = 4 THEN ABS(s.salary_amount) ELSE 0 END) AS employer_contribution
FROM emp_salary_slip AS s
JOIN salary_head_items AS shi 
    ON s.salary_head_item_fkey = shi.salary_head_item_pkey
WHERE 
    shi.status = 1
    AND s.month_year = '" . $monthYear . "'
    AND s.end_date_effective IS NULL
    AND s.head_operator = 'Addition'
GROUP BY month_name;
");

        $result = !empty($CTCBreakupsalary) ? $CTCBreakupsalary[0][0] : [
            'month_name' => date('F Y', strtotime($monthYear )),
            'fixed_salary' => 0,
            'variable_salary' => 0,
            'employer_contribution' => 0
        ];

        echo json_encode($result);
    }

    public function getCTCBreakupByDept()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $monthYear = isset($this->request->query['month_year']) ? $this->request->query['month_year'] : date('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
            $monthYear = date('Y-m');
        }


        $salaryByDept = $this->EmployeeDetails->query("
    SELECT 
        ei.department,
        MONTHNAME(STR_TO_DATE(CONCAT(s.month_year, '-01'), '%Y-%m-%d')) AS month_name,
        SUM(ABS(s.salary_amount)) AS total_salary
    FROM emp_salary_slip AS s
    JOIN employee_info AS ei 
        ON s.emp_fkey = ei.emp_pkey
    WHERE 
        s.month_year = '" . $monthYear . "'
        AND s.end_date_effective IS NULL
        AND s.head_operator = 'Addition'
    GROUP BY 
        ei.department, month_name
    ORDER BY 
        total_salary DESC
");

        $salaryDeptChartData = [];
        $monthName = '';

        foreach ($salaryByDept as $row) {
            $dept = $row['ei']['department'];
            $total = (float)$row[0]['total_salary'];
            $monthName = $row[0]['month_name'];
            $salaryDeptChartData[] = [
                'label' => $dept,
                'value' => $total
            ];
        }

        echo json_encode([
            'data' => $salaryDeptChartData,
            'monthName' => $monthName
        ]);
    }

    public function getCTCBreakupByDesignation()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $monthYear = isset($this->request->query['month_year']) ? $this->request->query['month_year'] : date('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
            $monthYear = date('Y-m');
        }

        $salaryByDesignation = $this->EmployeeDetails->query("
     SELECT 
         ei.designation,
        MONTHNAME(STR_TO_DATE(CONCAT(s.month_year, '-01'), '%Y-%m-%d')) AS month_name,
    SUM(ABS(s.salary_amount)) AS total_salary
    FROM emp_salary_slip AS s
    JOIN employee_info AS ei 
        ON s.emp_fkey = ei.emp_pkey
    WHERE 
        s.month_year = '" . $monthYear . "'
        AND s.end_date_effective IS NULL
        AND s.head_operator = 'Addition'
    GROUP BY 
       ei.designation, month_name
    ORDER BY 
        total_salary DESC
");

        $designationChartData = [];
        $monthName = '';

        foreach ($salaryByDesignation as $row) {
            $designation = isset($row['ei']['designation']) && $row['ei']['designation'] !== ''
                ? $row['ei']['designation']
                : 'Unknown';
            $monthName = $row[0]['month_name'];
            $total = isset($row[0]['total_salary']) ? (float)$row[0]['total_salary'] : 0;

            $designationChartData[] = [
                'label' => $designation,
                'value' => $total
            ];
        }

        echo json_encode([
            'data' => $designationChartData,
            'monthName' => $monthName
        ]);
    }
    public function getCTCBreakupByBranch()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $monthYear = isset($this->request->query['month_year']) ? $this->request->query['month_year'] : date('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
            $monthYear = date('Y-m');
        }

        $salaryByBranch = $this->EmployeeDetails->query("
  SELECT 
    ei.branch,
    MONTHNAME(STR_TO_DATE(CONCAT(s.month_year, '-01'), '%Y-%m-%d')) AS month_name,
    SUM(ABS(s.salary_amount)) AS total_salary
FROM emp_salary_slip AS s
JOIN employee_info AS ei 
    ON s.emp_fkey = ei.emp_pkey
WHERE 
    s.month_year = '" . $monthYear . "'
    AND s.end_date_effective IS NULL
    AND s.head_operator = 'Addition'
GROUP BY 
    ei.branch, month_name
ORDER BY 
    total_salary DESC;
");

        $branchChartData = [];
        $monthName = '';

        foreach ($salaryByBranch as $row) {
            $branch = isset($row['ei']['branch']) && $row['ei']['branch'] !== ''
                ? $row['ei']['branch']
                : 'Unknown';
            $monthName = $row[0]['month_name'];
            $total = isset($row[0]['total_salary']) ? (float)$row[0]['total_salary'] : 0;

            $branchChartData[] = [
                'label' => $branch,
                'value' => $total
            ];
        }

        echo json_encode([
            'data' => $branchChartData,
            'monthName' => $monthName
        ]);
    }
  public function getEmployeeCount() {
    $this->autoRender = false;
    $this->response->type('json');
    $month = isset($this->request->query['month']) ? $this->request->query['month'] : date('Y-m');
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

   $empPerMonthRaw = $this->EmployeeDetails->query("
    SELECT
        DATE_FORMAT(m.month_start, '%b %Y') AS month,
        COUNT(ed.emp_pkey) AS count
    FROM (
        SELECT DATE_FORMAT(DATE_SUB('".$month."-01', INTERVAL seq MONTH), '%Y-%m-01') AS month_start
        FROM (SELECT 0 seq UNION SELECT 1 UNION SELECT 2 UNION
              SELECT 3 UNION SELECT 4 UNION SELECT 5) x
    ) m
    LEFT JOIN emp_proff ep
        ON ep.joining_date <= LAST_DAY(m.month_start)
    LEFT JOIN emp_details ed
        ON ed.emp_pkey = ep.emp_fkey
    WHERE ed.status = 1
      AND NOT (
        ed.status = 2
        AND ed.modified_date BETWEEN m.month_start AND LAST_DAY(m.month_start)
    )
    GROUP BY m.month_start
    ORDER BY m.month_start ASC
");

    $empPerMonth = [];
    foreach ($empPerMonthRaw as $row) {
        $empPerMonth[] = [
            'month' => $row[0]['month'],
            'count' => (int)$row[0]['count']
        ];
    }

    echo json_encode(['empPerMonth' => $empPerMonth], JSON_NUMERIC_CHECK);
}
public function getPresentCount() {
    $this->autoRender = false;
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    $selectedMonth = isset($this->request->query['month']) ? $this->request->query['month'] : date('Y-m');

    $presentCountRaw = $this->EmployeeDetails->query("
        SELECT 
            month_year,
            SUM(presant_total) AS total_present_days
        FROM attendance_register
        WHERE isdelete = 'n'
          AND STR_TO_DATE(CONCAT(month_year, '-01'), '%Y-%m-%d')
              BETWEEN DATE_FORMAT(DATE_SUB('".$selectedMonth."-01', INTERVAL 5 MONTH), '%Y-%m-01')
              AND LAST_DAY('".$selectedMonth."-01')
        GROUP BY month_year
        ORDER BY month_year ASC
    ");

    $presentCount = [];
    foreach ($presentCountRaw as $row) {
        $presentCount[] = [
            'month' => $row['attendance_register']['month_year'],
            'count' => (float)$row[0]['total_present_days']
        ];
    }

    echo json_encode(['presentCount' => $presentCount]);
}
public function getVariableAddition() {
    $this->autoRender = false;
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    $monthYear = isset($this->request->query['month_year']) ? $this->request->query['month_year'] : date('Y-m');

 
    $variableAddition = $this->EmployeeDetails->query("
        SELECT 
            DATE_FORMAT(STR_TO_DATE(m.month_year, '%m-%Y'), '%b %Y') AS month_year, 
            COALESCE(d.total_upload, 0) AS total_upload
        FROM (
            SELECT DATE_FORMAT(DATE_SUB('".$monthYear."-01', INTERVAL 5 MONTH), '%m-%Y') AS month_year
            UNION ALL SELECT DATE_FORMAT(DATE_SUB('".$monthYear."-01', INTERVAL 4 MONTH), '%m-%Y')
            UNION ALL SELECT DATE_FORMAT(DATE_SUB('".$monthYear."-01', INTERVAL 3 MONTH), '%m-%Y')
            UNION ALL SELECT DATE_FORMAT(DATE_SUB('".$monthYear."-01', INTERVAL 2 MONTH), '%m-%Y')
            UNION ALL SELECT DATE_FORMAT(DATE_SUB('".$monthYear."-01', INTERVAL 1 MONTH), '%m-%Y')
            UNION ALL SELECT DATE_FORMAT('".$monthYear."-01', '%m-%Y')
        ) AS m
        LEFT JOIN (
            SELECT 
                CONVERT(evu.month_year USING utf8mb4) AS month_year,
                COALESCE(SUM(evu.uploaded_amount), 0) AS total_upload
            FROM emp_variables_upload AS evu
            JOIN payroll_master AS pm 
                ON evu.emp_fkey = pm.emp_fkey
                AND CONVERT(DATE_FORMAT(STR_TO_DATE(evu.month_year, '%m-%Y'), '%Y-%m') USING utf8mb4)
                    = CONVERT(pm.month_year USING utf8mb4)
            WHERE 
                pm.action IN ('Processed', 'Approved')
                AND evu.status = 1
                AND evu.head_operator = 'Addition'
            GROUP BY evu.month_year
        ) AS d 
            ON m.month_year = d.month_year
        ORDER BY STR_TO_DATE(m.month_year, '%m-%Y');
    ");

    echo json_encode($variableAddition);
}

public function getVariableDeduction() {
    $this->autoRender = false;
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

 
    $monthYear = isset($this->request->query['month_year']) ? $this->request->query['month_year'] : date('Y-m');

    $variableDeduction = $this->EmployeeDetails->query("
        SELECT 
            m.month_year,
            COALESCE(d.total_upload, 0) AS total_upload
        FROM (
            SELECT DATE_FORMAT(DATE_SUB('".$monthYear."-01', INTERVAL 5 MONTH), '%m-%Y') AS month_year
            UNION ALL SELECT DATE_FORMAT(DATE_SUB('".$monthYear."-01', INTERVAL 4 MONTH), '%m-%Y')
            UNION ALL SELECT DATE_FORMAT(DATE_SUB('".$monthYear."-01', INTERVAL 3 MONTH), '%m-%Y')
            UNION ALL SELECT DATE_FORMAT(DATE_SUB('".$monthYear."-01', INTERVAL 2 MONTH), '%m-%Y')
            UNION ALL SELECT DATE_FORMAT(DATE_SUB('".$monthYear."-01', INTERVAL 1 MONTH), '%m-%Y')
            UNION ALL SELECT DATE_FORMAT('".$monthYear."-01', '%m-%Y')
        ) AS m
        LEFT JOIN (
            SELECT 
                CONVERT(evu.month_year USING utf8mb4) AS month_year,
                COALESCE(SUM(evu.uploaded_amount), 0) AS total_upload
            FROM emp_variables_upload AS evu
            JOIN payroll_master AS pm 
                ON evu.emp_fkey = pm.emp_fkey
                AND CONVERT(DATE_FORMAT(STR_TO_DATE(evu.month_year, '%m-%Y'), '%Y-%m') USING utf8mb4)
                    = CONVERT(pm.month_year USING utf8mb4)
            WHERE 
                pm.action IN ('Processed', 'Approved')
                AND evu.status = 1
                AND evu.head_operator = 'Deduction'
            GROUP BY evu.month_year
        ) AS d 
            ON CONVERT(m.month_year USING utf8mb4) = d.month_year
        ORDER BY STR_TO_DATE(m.month_year, '%m-%Y');
    ");

    echo json_encode($variableDeduction);
}

public function getAbsenceData() {
    $this->autoRender = false;
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    $selectedMonth = $this->request->query('month');
    if (!$selectedMonth) {
        $selectedMonth = date('Y-m');
    }


    $selectedDate = $selectedMonth . "-01";


    $absenceCountRaw = $this->EmployeeDetails->query("
        SELECT 
            month_year,
            SUM(leave_total) AS total_leave_days,
            SUM(lop_total)   AS total_lop_days
        FROM attendance_register
        WHERE isdelete = 'n'
          AND STR_TO_DATE(CONCAT(month_year, '-01'), '%Y-%m-%d')
              BETWEEN DATE_FORMAT(DATE_SUB('{$selectedDate}', INTERVAL 5 MONTH), '%Y-%m-01')
              AND LAST_DAY('{$selectedDate}')
        GROUP BY month_year
        ORDER BY STR_TO_DATE(month_year, '%Y-%m')
    ");

    $absenceCount = [];

    foreach ($absenceCountRaw as $row) {
        $rawMonth = $row['attendance_register']['month_year'];
        $formatted = date("M Y", strtotime($rawMonth . "-01")); 

        $absenceCount[] = [
            'month' => $formatted,
            'leave' => (float)$row[0]['total_leave_days'],
            'lop'   => (float)$row[0]['total_lop_days']
        ];
    }

    echo json_encode($absenceCount);
}
public function highestSalaries() {
    $this->autoRender = false;
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
$month = !empty($this->request->query('month')) ? $this->request->query('month') : date('Y-m');


$highSalaryRaw = $this->EmployeeDetails->query("
    SELECT 
        e.first_name,
        SUM(s.salary_amount) AS total_ctc
    FROM emp_salary_slip AS s
    JOIN emp_details AS e ON e.emp_pkey = s.emp_fkey
    WHERE s.head_operator = 'Addition'
      AND s.end_date_effective IS NULL
      AND s.month_year = :month
    GROUP BY s.emp_fkey, e.first_name
    ORDER BY total_ctc DESC
    LIMIT 5
", ['month' => $month]);

    $totalHigh = 0;
    $count = 1;
    $html = '';

    if (!empty($highSalaryRaw)) {
        foreach ($highSalaryRaw as $row) {
            $totalHigh += $row[0]['total_ctc'];
            $html .= '<tr>';
            $html .= '<td class="text-center">' . $count++ . '</td>';
            $html .= '<td class="fw-medium ps-3">' . h($row['e']['first_name']) . '</td>';
            $html .= '<td class="text-end fw-bold text-success pe-3">' . number_format($row[0]['total_ctc']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '<tr class="table-light fw-bold">';
        $html .= '<td colspan="2" class="text-end pe-3">Total</td>';
        $html .= '<td class="text-end text-success pe-3">' . number_format($totalHigh) . '</td>';
        $html .= '</tr>';
    } else {
        $html .= '<tr><td colspan="3" class="text-center text-muted py-3">No data found</td></tr>';
    }

    echo $html;
}
public function lowestSalaries() {
    $this->autoRender = false;
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    $month = $this->request->query('month');
    if (!$month) {
        $month = date('Y-m');
    }

    $lowSalaryRaw = $this->EmployeeDetails->query("
        SELECT 
            e.first_name,
            SUM(s.salary_amount) AS total_ctc
        FROM emp_salary_slip AS s
        JOIN emp_details AS e ON e.emp_pkey = s.emp_fkey
        WHERE s.head_operator = 'Addition'
          AND s.end_date_effective IS NULL
          AND s.month_year = :month
        GROUP BY s.emp_fkey, e.first_name
        ORDER BY total_ctc ASC
        LIMIT 5
    ", ['month' => $month]);

    $totalLow = 0;
    $count = 1;
    $html = '';

    if (!empty($lowSalaryRaw)) {
        foreach ($lowSalaryRaw as $row) {
            $totalLow += $row[0]['total_ctc'];
            $html .= '<tr>';
            $html .= '<td class="text-center">' . $count++ . '</td>';
            $html .= '<td class="fw-medium ps-3">' . h($row['e']['first_name']) . '</td>';
            $html .= '<td class="text-end fw-bold text-danger pe-3">' . number_format($row[0]['total_ctc']) . '</td>';
            $html .= '</tr>';
        }

        
        $html .= '<tr class="table-light fw-bold">';
        $html .= '<td colspan="2" class="text-end pe-3">Total</td>';
        $html .= '<td class="text-end text-danger pe-3">' . number_format($totalLow) . '</td>';
        $html .= '</tr>';
    } else {
        $html .= '<tr><td colspan="3" class="text-center text-muted py-3">No data found</td></tr>';
    }

    echo $html;
}

public function monthlyCTCChartData()
{
    $this->autoRender = false;
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

   
    $selectedMonth = $this->request->query('month') ?: date('Y-m');
    $selectedDate  = $selectedMonth . '-01';


    $months = [];
    $dateObj = new DateTime($selectedDate);
    for ($i = 5; $i >= 0; $i--) {
        $m = clone $dateObj;
        $m->modify("-{$i} month");
        $ym = $m->format('Y-m');     
        $label = $m->format('M Y'); 
        $months[$ym] = $label;
    }


    $salaryDataRaw = $this->EmployeeDetails->query("
        SELECT month_year, SUM(abs(salary_amount)) AS monthly_ctc
        FROM emp_salary_slip
        WHERE head_operator = 'ADDITION'
          AND end_date_effective IS NULL
          AND STR_TO_DATE(CONCAT(month_year, '-01'), '%Y-%m-%d')
              BETWEEN DATE_SUB(STR_TO_DATE(:selectedDate, '%Y-%m-%d'), INTERVAL 5 MONTH)
                  AND STR_TO_DATE(:selectedDate, '%Y-%m-%d')
        GROUP BY month_year
        ORDER BY month_year
    ", ['selectedDate' => $selectedDate]);

    $salaryData = [];
    foreach ($months as $ym => $label) {
        $salaryData[$label] = 0;
    }

    foreach ($salaryDataRaw as $row) {
        $ym = isset($row['emp_salary_slip']['month_year']) ? $row['emp_salary_slip']['month_year'] : null;
        if ($ym !== null) {
            $label = date('M Y', strtotime($ym . '-01'));
            $salaryData[$label] = isset($row[0]['monthly_ctc']) ? (float) $row[0]['monthly_ctc'] : 0;
        }
    }

   
    $lineData = [];
    foreach ($salaryData as $label => $value) {
        $lineData[] = [
            'month' => $label,
            'value' => $value
        ];
    }

    echo json_encode($lineData);
}


}
