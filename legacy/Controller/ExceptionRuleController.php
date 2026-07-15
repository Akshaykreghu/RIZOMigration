<?php
// create by bindu 17-10-2025

class ExceptionRuleController extends AppController
{
    public $name = 'ExceptionRule';
    public $uses = array('CentralControl', 'EmpDocument', 'EmpFam', 'Education', 'WorkExperience', 'EmployeeJoin', 'EmployeeSalaryStructure', 'EmployeeConfig', 'Family', 'passport', 'Promotion', 'NoticePeriod', 'qualifcations', 'history', 'EmployeeTaxTransactions', 'EmpTaxSalTrans', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC', 'EmpAlterationDetails', 'ReportCriterias', 'SalaryIncrement', 'SalaryIncrementDetails', 'ComponentIncrement', 'EditPunches', 'ExceptionRule');

    public $components = array('MasterdataManagement');

    public function index()
    {
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        // debug($arr_branches);
        $this->set('arr_branches', $arr_branches);
        $this->ExceptionRule->useDbConfig = $this->Session->read('ds');
       $rules = $this->ExceptionRule->find('all', [
    'fields' => [
        'exception_id',
        'rule_name',
        'activate_status'
    ],
    'conditions' => [
        'activate_status' => 1
    ]
]);

$this->set('rules', $rules);
    }
     public function getActiveRulesList()
{
    $this->autoRender = false;
    $this->response->type('json');

    // Use the selected DB config
    $this->ExceptionRule->useDbConfig = $this->Session->read('ds');

    // Fetch active rules
    $rules = $this->ExceptionRule->find('all', [
        'fields' => [
            'exception_id',
            'rule_name'
        ],
        'conditions' => [
            'activate_status' => 1
        ],
        'order' => ['exception_id' => 'DESC']
    ]);

    // Format for dropdown
    $data = [];
    foreach ($rules as $r) {
        $data[] = [
            'id' => $r['ExceptionRule']['exception_id'],
            'text' => $r['ExceptionRule']['rule_name']
        ];
    }

    echo json_encode($data);
    return;
}

    public function newForm()
    {
        $this->autoRender = false;
        $this->ExceptionRule->useDbConfig = $this->Session->read('ds');
        $this->render('newform');
    }


    public function saveRule()
    {
        $this->autoRender = false;
        $this->layout = '';
        $this->ExceptionRule->useDbConfig = $this->Session->read('ds');

        if ($this->request->is('post')) {
            $data = $this->request->input('json_decode', true);
            $created_by = $this->Session->read('login_user_id');

            if (!$data) {
                $data = $this->request->data;
            }
            if (empty($data) || empty($data['ruleName']) || empty($data['ruleType'])) {
                return $this->response->body(json_encode([
                    'status' => 'error',
                    'message' => 'Invalid or empty data. Please fill all required fields.'
                ]));
            }

            function customRound($value)
            {
                $intPart = floor($value); // integer part
                $decimal = $value - $intPart;

                if ($decimal < 0.5) {
                    return $intPart; // round down
                } else {
                    return $intPart + 0.5; // round to nearest half
                }
            }

            $saveData = [
                'ExceptionRule' => [
                    'rule_name' => trim($data['ruleName']),
                    'rule_type' => trim($data['ruleType']),
                    'data_type' => (int)$data['dataType'],
                    'exception_days' => !empty($data['exceptionDays']) ? (int)$data['exceptionDays'] : null,
                    'exception_time' => !empty($data['exceptionTimeLimit']) ? (float)$data['exceptionTimeLimit'] : null,
                    'action_after_exception' => (int)$data['actionException'],
                    'detect_count' =>  !empty($data['countDetection']) ? customRound((float)$data['countDetection']) : 0,
                    'leave_detect_type' =>
            ((int)$data['actionException'] === 1)
                ? 105 // LOP leave type
                : ((!empty($data['leaveType']) && (int)$data['leaveType'] > 0)
                    ? (int)$data['leaveType']
                    : 0),
                    'reset_status' => !empty($data['resetCheckbox']) ? 1 : 0,
                    'status' => 0,
                    'activate_status' => !empty($data['activateCheckbox']) ? 1 : 0,
                    'creation_time' => date('Y-m-d H:i:s'),
                    'created_by' => $created_by,
                    'modification_time' => null,
                    'modified_by' => null
                ]
            ];

            $this->loadModel('ExceptionRule');

            if ($this->ExceptionRule->save($saveData)) {
                return $this->response->body(json_encode([
                    'status' => 'success',
                    'message' => 'Rule saved successfully'
                ]));
            } else {
                return $this->response->body(json_encode([
                    'status' => 'error',
                    'message' => 'Failed to save rule'
                ]));
            }
        } else {
            return $this->response->body(json_encode([
                'status' => 'error',
                'message' => 'Invalid request method'
            ]));
        }
    }
    public function getRuleById($id = null)
    {
        $this->autoRender = false;
        $this->ExceptionRule->useDbConfig = $this->Session->read('ds');

        if (!$id) {
            return $this->response->body(json_encode([
                'status' => 'error',
                'message' => 'No rule ID provided'
            ]));
        }

        $rule = $this->ExceptionRule->find('first', [
            'conditions' => ['ExceptionRule.exception_id' => $id],
            'fields' => [
                'exception_id',
                'rule_name',
                'rule_type',
                'data_type',
                'exception_days',
                'exception_time',
                'action_after_exception',
                'detect_count',
                'leave_detect_type',
                'reset_status',
                'activate_status'
            ]
        ]);
        $this->set('rule', $rule);
        $this->render('newform');
    }

   public function updateRule()
{
    $this->autoRender = false;
    $this->ExceptionRule->useDbConfig = $this->Session->read('ds');

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['exception_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'No Rule ID provided']);
        return;
    }

    function customRound($value)
    {
        $intPart = floor($value);
        $decimal = $value - $intPart;
        return ($decimal < 0.5) ? $intPart : $intPart + 0.5;
    }

    // ✅ Leave Type Logic
   $leaveTypeValue = null;

if (isset($data['actionException'])) {
    if ((int)$data['actionException'] === 1) {
        // LOP → leave type should be 105
        $leaveTypeValue = 105;
    } else {
        // Leave Deduction → valid leave type required, or 0 if not provided
        $leaveTypeValue = !empty($data['leaveType']) ? (int)$data['leaveType'] : 0;
    }
}


    $rule = [
        'exception_id' => $data['exception_id'],
        'rule_name' => $data['ruleName'],
        'rule_type' => $data['ruleType'],
        'data_type' => $data['dataType'],
        'action_after_exception' => $data['actionException'],
        'exception_days' => $data['exceptionDays'],
        'exception_time' => $data['exceptionTimeLimit'],
        'detect_count' => customRound((float)$data['countDetection']),
        'leave_detect_type' => $leaveTypeValue,
        'reset_status' => $data['resetCheckbox'],
        'activate_status' => $data['activateCheckbox'],
        'modification_time' => date('Y-m-d H:i:s'),
        'modified_by' => $this->Session->read('login_user_id')
    ];

    if ($this->ExceptionRule->save($rule)) {
        echo json_encode(['status' => 'success', 'message' => 'Rule updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update rule']);
    }
}

  public function getAllRules()
{
    $this->autoRender = false;
    $this->layout = '';
    $this->ExceptionRule->useDbConfig = $this->Session->read('ds');

    $page = !empty($this->request->query('page')) ? $this->request->query('page') : 1;
    $rows = !empty($this->request->query('rows')) ? $this->request->query('rows') : 10;

    $limit = (int)$rows;
    $offset = ($page - 1) * $limit;

    $conditions = [
        'ExceptionRule.status' => 0   // ✅ only active rules
    ];

    $rules = $this->ExceptionRule->find('all', [
        'conditions' => $conditions,
        'fields' => [
            'exception_id',
            'rule_name',
            'rule_type',
            'data_type',
            'exception_days',
            'action_after_exception',
            'detect_count',
            'leave_detect_type',
            'reset_status',
            'activate_status',
            'created_by',
            'creation_time',
            'exception_time',
        ],
        'group' => ['ExceptionRule.exception_id'],
        'limit' => $limit,
        'offset' => $offset,
        'order' => ['ExceptionRule.exception_id' => 'DESC']
    ]);

    $total = $this->ExceptionRule->find('count', [
        'conditions' => $conditions,
        'fields' => ['DISTINCT ExceptionRule.exception_id']
    ]);

    $data = [];
    foreach ($rules as $r) {
        $rule = $r['ExceptionRule'];
        $data[] = [
            'exception_id' => $rule['exception_id'],
            'rule_name' => $rule['rule_name'],
            'rule_type' => ucfirst($rule['rule_type']),
            'data_type' => $this->getDataTypeLabel($rule['data_type']),
            'exception' => !empty($rule['exception_days'])
                ? $rule['exception_days'] . ' Days'
                : $rule['exception_time'] . ' min',
            'action' => $rule['action_after_exception'] == 0 ? 'Leave Deduction' : 'Loss of Pay',
            'detect_count' => $rule['detect_count'],
            'leave_type' => $this->getLeaveTypeLabel($rule['leave_detect_type']),
            'reset_status' => $rule['reset_status'] == 1 ? 'Yes' : 'No',
            'activate_status' => $rule['activate_status'] == 1 ? 'Yes' : 'No',
            'creation_time' => date('d-m-Y', strtotime($rule['creation_time']))
        ];
    }

    echo json_encode([
        'total' => $total,
        'rows' => $data
    ]);
}

//   public function getAppliedList()
// {
//     $this->autoRender = false;

//     // Use ExceptionRule model for all queries
//     $this->ExceptionRule->useDbConfig = $this->Session->read('ds');

//     $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
//     $limit = 10;
//     $offset = ($page - 1) * $limit;

//     // Main query to fetch applied rules
//     $sql = "
//         SELECT 
//             exception_applied_pkey,
//             rule_id,
//             branch_code,
//             applied_date,
//             month_year,
//             creation_date,
//             created_by
//         FROM exception_applied
//         ORDER BY exception_applied_pkey DESC
//         LIMIT $offset, $limit
//     ";

//     $rawData = $this->ExceptionRule->query($sql);

//     $finalData = [];

//     foreach ($rawData as $row) {

//         $item = $row['exception_applied'];

//         $branch_code = $item['branch_code'];
//         $rule_id     = $item['rule_id'];

//         // Fetch branch name (also using ExceptionRule model)
//         $branch = $this->ExceptionRule->query("
//             SELECT branch
//             FROM employee_info
//             WHERE branch_code = '$branch_code'
//             LIMIT 1
//         ");
//         $branch_name = isset($branch[0]['employee_info']['branch']) 
//             ? $branch[0]['employee_info']['branch']
//             : '';

//         // Fetch rule name (also using ExceptionRule model)
//         $rule = $this->ExceptionRule->query("
//             SELECT rule_name
//             FROM exception_rule
//             WHERE exception_id = $rule_id
//             LIMIT 1
//         ");
//         $rule_name = isset($rule[0]['exception_rule']['rule_name']) 
//             ? $rule[0]['exception_rule']['rule_name']
//             : '';

//         $finalData[] = [
//             "exception_applied_pkey" => $item["exception_applied_pkey"],
//             "rule_id"   => $item["rule_id"],
//             "rule_name" => $rule_name,
//             "branch_name" => $branch_name,
//             "applied_date" => $item["applied_date"],
//             "month_year"   => $item["month_year"],
//             "creation_date" => $item["creation_date"],
//             "created_by" => $item["created_by"]
//         ];
//     }

//     echo json_encode([
//         "rows"  => $finalData,
//         "total" => count($finalData),
//         "page"  => $page
//     ]);
// }

public function getAppliedList()
{
    $this->autoRender = false;

    $this->ExceptionRule->useDbConfig = $this->Session->read('ds');

    $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // 🔹 1. Count total rows (NO LIMIT)
    $countResult = $this->ExceptionRule->query("
        SELECT COUNT(*) AS total
        FROM exception_applied
    ");
    $totalRecords = $countResult[0][0]['total'];

    // 🔹 2. Fetch paginated rows
    $sql = "
        SELECT 
            exception_applied_pkey,
            rule_id,
            branch_code,
            applied_date,
            month_year,
            creation_date,
            created_by
        FROM exception_applied
        ORDER BY exception_applied_pkey DESC
        LIMIT $offset, $limit
    ";
    $rawData = $this->ExceptionRule->query($sql);

    $finalData = [];

    foreach ($rawData as $row) {
        $item = $row['exception_applied'];

        $branch_code = $item['branch_code'];
        $rule_id     = $item['rule_id'];

        // Fetch branch name
        $branch = $this->ExceptionRule->query("
            SELECT branch
            FROM employee_info
            WHERE branch_code = '$branch_code'
            LIMIT 1
        ");
        $branch_name = isset($branch[0]['employee_info']['branch'])
            ? $branch[0]['employee_info']['branch']
            : '';

        // Fetch rule name
        $rule = $this->ExceptionRule->query("
            SELECT rule_name
            FROM exception_rule
            WHERE exception_id = $rule_id
            LIMIT 1
        ");
        $rule_name = isset($rule[0]['exception_rule']['rule_name'])
            ? $rule[0]['exception_rule']['rule_name']
            : '';

        $finalData[] = [
            "exception_applied_pkey" => $item["exception_applied_pkey"],
            "branch_code"=> $branch_code,
            "rule_id"   => $item["rule_id"],
            "rule_name" => $rule_name,
            "branch_name" => $branch_name,
            "applied_date" => $item["applied_date"],
            "month_year"   => $item["month_year"],
            "creation_date" => $item["creation_date"],
            "created_by" => $item["created_by"]
        ];
    }

    // 🔹 3. Return correct total for pagination
    echo json_encode([
        "rows"  => $finalData,
        "total" => $totalRecords,
        "page"  => $page
    ]);
}


    private function getDataTypeLabel($type)
    {
        switch ($type) {
            case 0:
                return 'Early Out';
            case 1:
                return 'Late In';
            case 2:
                return 'Late In and Early Out';
            default:
                return 'Unknown';
        }
    }

    private function getLeaveTypeLabel($type)
    {
        switch ($type) {
            case 87:
                return 'Casual Leave';
            case 86:
                return 'Sick Leave';
            case 88:
                return 'Earned Leave';
            case 114:
                return 'Compository Off';
            case 89:
                return 'Privilege Leave';
            default:
                return 'LOP';
        }
    }



    // public function deleteRule()
    // {
    //     $this->autoRender = false;
    //     $this->layout = '';
    //     $this->ExceptionRule->useDbConfig = $this->Session->read('ds');

    //     if ($this->request->is('post')) {
    //         $id = $this->request->data('id');
    //         if (!$id) {
    //             echo json_encode([
    //                 'status' => 'error',
    //                 'message' => 'No rule ID provided'
    //             ]);
    //             return;
    //         }

    //         $rule = $this->ExceptionRule->findByExceptionId($id);
    //         if (!$rule) {
    //             echo json_encode([
    //                 'status' => 'error',
    //                 'message' => 'Rule not found'
    //             ]);
    //             return;
    //         }

    //         if ($this->ExceptionRule->delete($id)) {
    //             echo json_encode([
    //                 'status' => 'success',
    //                 'message' => 'Rule deleted successfully'
    //             ]);
    //         } else {
    //             echo json_encode([
    //                 'status' => 'error',
    //                 'message' => 'Failed to delete rule'
    //             ]);
    //         }
    //     } else {
    //         echo json_encode([
    //             'status' => 'error',
    //             'message' => 'Invalid request method'
    //         ]);
    //     }
    // }
 
public function deleteRule()
{
    $this->autoRender = false;
    $this->layout = '';
    $this->ExceptionRule->useDbConfig = $this->Session->read('ds');

    if ($this->request->is('post')) {

        $id = $this->request->data('id');

        if (empty($id)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No rule ID provided'
            ]);
            return;
        }

        // Check if rule exists
        $rule = $this->ExceptionRule->find('first', [
            'conditions' => [
                'ExceptionRule.exception_id' => $id
            ]
        ]);

        if (empty($rule)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Rule not found'
            ]);
            return;
        }

        // Soft delete: status = 1, activate_status = 0
        $this->ExceptionRule->id = $id;
        if ($this->ExceptionRule->save([
            'status' => 1,
            'activate_status' => 0
        ])) {

            echo json_encode([
                'status' => 'success',
                'message' => 'Rule deleted successfully'
            ]);

        } else {

            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to delete rule'
            ]);
        }

    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method'
        ]);
    }
}


    public function checkRuleName()
    {
        $this->autoRender = false;
        $this->response->type('json');
        $this->ExceptionRule->useDbConfig = $this->Session->read('ds');
        $ruleName = $this->request->data('ruleName');

        $exists = $this->ExceptionRule->hasAny([
            'LOWER(ExceptionRule.rule_name)' => strtolower($ruleName)
        ]);

        if ($exists) {
            $message = 'This rule name already exists!';
        } else {
            $message = '';
        }

        echo json_encode(['exists' => $exists, 'message' => $message]);
        exit;
    }

public function applyRule()
{
    $this->autoRender = false;
    $this->ExceptionRule->useDbConfig = $this->Session->read('ds');

    if ($this->request->is(['post', 'get'])) {

        $apply = $this->request->is('post') ? $this->request->data : $this->request->query;

        $branch_code = !empty($apply['branch_code']) ? trim($apply['branch_code']) : null;
        $rule_id     = !empty($apply['rule_id']) ? trim($apply['rule_id']) : null;
        $month       = !empty($apply['month_start']) ? trim($apply['month_start']) : null;
         $user_login = $this->Session->read('login_user_id');
        // debug($user_login);

        if (!empty($month)) {
            $month_start = date('Y-m-01', strtotime($month));
            $month_year  = date('Y-m', strtotime($month));
        }

        // ---- VALIDATION ----
        if (empty($branch_code)) {
            echo json_encode(['status' => 'error', 'message' => 'Branch code is required']);
            return;
        }
        if (empty($rule_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Rule ID is required']);
            return;
        }
        if (empty($month)) {
            echo json_encode(['status' => 'error', 'message' => 'Month start date is required']);
            return;
        }

        // ---- Prevent double apply ----
        $check_sql = "
            SELECT registerid 
            FROM attendance_register
            WHERE branch_code = '$branch_code'
              AND month_year = '$month_year'
              AND isdelete = 'N'
            LIMIT 1
        ";

        $check = $this->ExceptionRule->query($check_sql);

        if (!empty($check)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Attendance already verified for this month. Rule cannot be applied.'
            ]);
            return;
        }
// ---- CHECK IF ALREADY APPLIED ----
$dup_sql = "
    SELECT exception_applied_pkey 
    FROM exception_applied
    WHERE branch_code = '$branch_code'
      AND rule_id = $rule_id
      AND month_year = '$month_year'
    LIMIT 1
";

$duplicate = $this->ExceptionRule->query($dup_sql);

if (!empty($duplicate)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'This rule is already applied for this branch and month.'
    ]);
    return;
}
$dup_already = "
    SELECT ea.exception_applied_pkey, er.rule_name
    FROM exception_applied ea
    JOIN exception_rule er 
        ON er.exception_id = ea.rule_id
    WHERE ea.branch_code = '$branch_code'
      AND ea.month_year  = '$month_year'
    LIMIT 1
";

$duplicated = $this->ExceptionRule->query($dup_already);

if (!empty($duplicated)) {

    $ruleName = $duplicated[0]['er']['rule_name'];

    echo json_encode([
        'status'  => 'error',
        'message' => "Rule '{$ruleName}' is already applied for this branch and month."
    ]);
    return;
}


        // ---- CALL PROCEDURE ----
      $proc_sql = "
    CALL exception_rule_apply_prce(
        '$branch_code',
        '$month_start',
        $rule_id,
        '$user_login',
        @p_output
    );
";

$this->ExceptionRule->query($proc_sql);

// Fetch output from procedure
$output = $this->ExceptionRule->query("SELECT @p_output AS message");

$proc_message = !empty($output[0][0]['message'])
    ? $output[0][0]['message']
    : "Procedure executed successfully";

        // ---- INSERT INTO exception_applied ----
        $created_by = $this->Session->read('username');
        $today      = date('Y-m-d H:i:s');

        $insert_sql = "
            INSERT INTO exception_applied
            (branch_code, rule_id, applied_date, month_year, creation_date, created_by)
            VALUES
            ('$branch_code', $rule_id, '$today', '$month_year', '$today', '$created_by')
        ";

        $this->ExceptionRule->query($insert_sql);

        // ---- FETCH THE LAST INSERTED ROW ----
        $select_sql = "
            SELECT *
            FROM exception_applied
            WHERE branch_code = '$branch_code'
              AND rule_id = $rule_id
              AND month_year = '$month_year'
            ORDER BY  exception_applied_pkey DESC
            LIMIT 1
        ";

        $saved_row = $this->ExceptionRule->query($select_sql);

        // ---- FINAL OUTPUT ----
        echo json_encode([
            'status' => 'success',
            'message' => $proc_message,
            'saved_row' => $saved_row,   // <---- returning the inserted row
            'info' => 'Entry inserted into exception_applied table'
        ]);
        return;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid request type']);
}

    public function processAndGetLogs()
    {
        $this->autoRender = false;
        $this->layout = '';
        $this->ExceptionRule->useDbConfig = $this->Session->read('ds');

        $ruleId = $this->request->query('rule_id');
        $branchCode = $this->request->query('branch_code');
        $monthStart = $this->request->query('month_start'); // expected format YYYY-MM-01

        $page = !empty($this->request->query('page')) ? (int)$this->request->query('page') : 1;
        $rows = !empty($this->request->query('rows')) ? (int)$this->request->query('rows') : 10;
        $limit = $rows;
        $offset = ($page - 1) * $limit;

        if (empty($ruleId) || empty($branchCode) || empty($monthStart)) {
            echo json_encode(['total' => 0, 'rows' => [], 'message' => 'Missing parameters']);
            return;
        }

        $ruleId = (int)$ruleId;
        $branchCode = trim($branchCode);
        $monthStart = date('Y-m-01', strtotime($monthStart));

        try {
            // Optional: only call procedure if explicitly requested
            $shouldApply = $this->request->query('apply');
            if (!empty($shouldApply)) {
                $this->ExceptionRule->query(
                    "CALL ProcessExceptionRules($ruleId, '" . addslashes($branchCode) . "', '" . addslashes($monthStart) . "')"
                );
            }

            // After processing, fetch from log table for the selected branch, rule and month
            $start = $monthStart;
            $end = date('Y-m-t', strtotime($monthStart));

            $sql = "SELECT change_id, emp_pkey, emp_id, att_date, branch_code, old_in_time, old_out_time, new_in_time, new_out_time, change_reason, changed_by, rule_id, change_timestamp
                    FROM exception_attendance_change_log
                    WHERE rule_id = $ruleId
                      AND branch_code = '" . addslashes($branchCode) . "'
                      AND att_date BETWEEN '" . addslashes($start) . "' AND '" . addslashes($end) . "'
                    ORDER BY change_timestamp DESC
                    LIMIT $limit OFFSET $offset";

            $rowsData = $this->ExceptionRule->query($sql);

            $countSql = "SELECT COUNT(1) as cnt
                         FROM exception_attendance_change_log
                         WHERE rule_id = $ruleId
                           AND branch_code = '" . addslashes($branchCode) . "'
                           AND att_date BETWEEN '" . addslashes($start) . "' AND '" . addslashes($end) . "'";
            $countRes = $this->ExceptionRule->query($countSql);
            $total = isset($countRes[0][0]['cnt']) ? (int)$countRes[0][0]['cnt'] : 0;

            // Flatten results if Cake returns nested arrays
            $out = [];
            foreach ($rowsData as $r) {
                $flat = [];
                foreach ($r as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $fk => $fv) {
                            $flat[$fk] = $fv;
                        }
                    }
                }
                if (empty($flat)) {
                    foreach ($r as $fk => $fv) {
                        if (!is_array($fv)) {
                            $flat[$fk] = $fv;
                        }
                    }
                }
                $out[] = $flat;
            }

            echo json_encode(['total' => $total, 'rows' => $out]);
        } catch (Exception $ex) {
            echo json_encode(['total' => 0, 'rows' => [], 'message' => $ex->getMessage()]);
        }
    }
//  public function downloadExceptionExcel()
// {
//     $this->autoRender = false;

//     // read inputs
//     $rule_id      = (int)$this->request->query('rule_id');
//     $applied_date = $this->request->query('applied_date'); // expected YYYY-MM-DD

//     // basic validation
//     if (!$rule_id || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $applied_date)) {
//         echo "Invalid request parameters";
//         return;
//     }

//     // use ExceptionRule model & DB config from session (you asked this explicitly)
//     $this->ExceptionRule->useDbConfig = $this->Session->read('ds');

//     // raw SQL with positional parameters (Cake 2.x)
//     $sql = "
//         SELECT 
//             emp_id,
//             att_date,
//             branch_code,
//             old_in_time,
//             old_out_time,
//             new_in_time,
//             new_out_time,
//             change_reason,
//             changed_by,
//             change_timestamp
//         FROM exception_attendance_change_log
//         WHERE rule_id = ?
//         AND DATE(change_timestamp) = ?
//         ORDER BY change_id DESC
//     ";

//     try {
//         $rows = $this->ExceptionRule->query($sql, [$rule_id, $applied_date]);
//     } catch (Exception $e) {
//         // DB error
//         echo "Database error: " . h($e->getMessage());
//         return;
//     }

//     if (empty($rows)) {
//         echo "No data found";
//         return;
//     }

//     // Load PHPExcel (same import pattern you used)
//     App::import('Vendor', 'PHPExcel', ['file' => 'PHPExcel.php']);
//     $objPHPExcel = new PHPExcel();
//     $sheet = $objPHPExcel->getActiveSheet();
//     $sheet->setTitle('Exception Logs');
//     $sheet->setShowGridlines(false);

//     // Header rows
//     $sheet->setCellValue("A1", "Exception Rule Change Log");
//     $sheet->mergeCells("A1:J1");
//     $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(16);
//     $sheet->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//     $sheet->setCellValue("A2", "Rule ID: {$rule_id}     Date: {$applied_date}");
//     $sheet->mergeCells("A2:J2");
//     $sheet->getStyle("A2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//     // Column headers
//     $headers = [
//         "Emp ID", "Attendance Date", "Branch", "Old In", "Old Out",
//         "New In", "New Out", "Change Reason", "Changed By", "Timestamp"
//     ];
//     $col = 'A';
//     $headerRow = 4;
//     foreach ($headers as $h) {
//         $sheet->setCellValue($col . $headerRow, $h);
//         $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
//         $col++;
//     }

//     // Write data (flatten robustly)
//     $rowNum = $headerRow + 1;
//     foreach ($rows as $r) {
//         // Cake can return either:
//         // [ 'exception_attendance_change_log' => [ ... ] ]
//         // OR numeric-keyed array [ 0 => [ 'emp_id' => ... ] ]
//         // So normalize into $rec
//         if (isset($r['exception_attendance_change_log']) && is_array($r['exception_attendance_change_log'])) {
//             $rec = $r['exception_attendance_change_log'];
//         } elseif (isset($r[0]) && is_array($r[0])) {
//             $rec = $r[0];
//         } else {
//             // last ditch: flatten first nested array entry
//             $rec = [];
//             foreach ($r as $sub) {
//                 if (is_array($sub)) {
//                     $rec = $sub;
//                     break;
//                 }
//             }
//         }

//         // fallback safe keys (use empty string if missing)
//         $emp_id        = isset($rec['emp_id']) ? $rec['emp_id'] : '';
//         $att_date      = isset($rec['att_date']) ? $rec['att_date'] : '';
//         $branch_code   = isset($rec['branch_code']) ? $rec['branch_code'] : '';
//         $old_in        = isset($rec['old_in_time']) ? $rec['old_in_time'] : '';
//         $old_out       = isset($rec['old_out_time']) ? $rec['old_out_time'] : '';
//         $new_in        = isset($rec['new_in_time']) ? $rec['new_in_time'] : '';
//         $new_out       = isset($rec['new_out_time']) ? $rec['new_out_time'] : '';
//         $reason        = isset($rec['change_reason']) ? $rec['change_reason'] : '';
//         $changed_by    = isset($rec['changed_by']) ? $rec['changed_by'] : '';
//         $timestamp     = isset($rec['change_timestamp']) ? $rec['change_timestamp'] : '';

//         $sheet->setCellValue("A{$rowNum}", $emp_id);
//         $sheet->setCellValue("B{$rowNum}", $att_date);
//         $sheet->setCellValue("C{$rowNum}", $branch_code);
//         $sheet->setCellValue("D{$rowNum}", $old_in);
//         $sheet->setCellValue("E{$rowNum}", $old_out);
//         $sheet->setCellValue("F{$rowNum}", $new_in);
//         $sheet->setCellValue("G{$rowNum}", $new_out);
//         $sheet->setCellValue("H{$rowNum}", $reason);
//         $sheet->setCellValue("I{$rowNum}", $changed_by);
//         $sheet->setCellValue("J{$rowNum}", $timestamp);

//         $rowNum++;
//     }

//     // Auto size columns A-J
//     foreach (range('A', 'J') as $c) {
//         $sheet->getColumnDimension($c)->setAutoSize(true);
//     }

//     // Prepare download (xlsx)
//     $fileName = "Exception_Logs_{$rule_id}_{$applied_date}.xlsx";
//     header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
//     header("Content-Disposition: attachment;filename=\"{$fileName}\"");
//     header("Cache-Control: max-age=0");

//     $writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//     $writer->save('php://output');
//     exit;
// }
// public function downloadExceptionExcel()
// {
//     $this->autoRender = false;

//     $rule_id      = (int)$this->request->query('rule_id');
//     $applied_date = $this->request->query('applied_date'); // YYYY-MM-DD

//     if (!$rule_id || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $applied_date)) {
//         echo "Invalid request parameters";
//         return;
//     }

//     $this->ExceptionRule->useDbConfig = $this->Session->read('ds');
//     $db = $this->ExceptionRule->getDataSource();

//     // Fetch exception log entries
//     $sql = "
//         SELECT 
//             emp_id,
//             att_date,
//             branch_code,
//             old_in_time,
//             old_out_time,
//             new_in_time,
//             new_out_time,
//             change_reason,
//             changed_by,
//             change_timestamp
//         FROM exception_attendance_change_log
//         WHERE rule_id = {$rule_id}
//         AND DATE(change_timestamp) = " . $db->value($applied_date) . "
//         ORDER BY change_id DESC
//     ";

//     try {
//         $rows = $this->ExceptionRule->query($sql);
//     } catch (Exception $e) {
//         echo "Database error: " . h($e->getMessage());
//         return;
//     }

//     if (empty($rows)) {
//         echo "No data found";
//         return;
//     }

//     // Load PHPExcel
//     App::import('Vendor', 'PHPExcel', ['file' => 'PHPExcel.php']);
//     $objPHPExcel = new PHPExcel();
//     $sheet = $objPHPExcel->getActiveSheet();
//     $sheet->setTitle('Exception Logs');
//     $sheet->setShowGridlines(false);

//     // Header rows
//     $sheet->setCellValue("A1", "Exception Rule Change Log");
//     $sheet->mergeCells("A1:J1");
//     $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(16);
//     $sheet->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//     $sheet->setCellValue("A2", "Rule ID: {$rule_id}     Date: {$applied_date}");
//     $sheet->mergeCells("A2:J2");
//     $sheet->getStyle("A2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//     // Column headers
//     $headers = [
//         "Emp ID", "Attendance Date", "Employee Name", "Branch", "Old In", "Old Out",
//         "New In", "New Out", "Change Reason", "Changed By", "Timestamp"
//     ];
//     $col = 'A';
//     $headerRow = 4;
//     foreach ($headers as $h) {
//         $sheet->setCellValue($col . $headerRow, $h);
//         $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
//         $col++;
//     }

//     $rowNum = $headerRow + 1;

//     // Load employee info model for lookup
//     $this->loadModel('EmployeeInfo');
//     $this->EmployeeInfo->useDbConfig = $this->Session->read('ds');

//     foreach ($rows as $r) {
//         if (isset($r['exception_attendance_change_log'])) {
//             $rec = $r['exception_attendance_change_log'];
//         } elseif (isset($r[0])) {
//             $rec = $r[0];
//         } else {
//             $rec = [];
//             foreach ($r as $sub) {
//                 if (is_array($sub)) {
//                     $rec = $sub;
//                     break;
//                 }
//             }
//         }

//         $emp_id    = isset($rec['emp_id']) ? $rec['emp_id'] : '';
//         $att_date  = isset($rec['att_date']) ? $rec['att_date'] : '';
//         $old_in    = isset($rec['old_in_time']) ? $rec['old_in_time'] : '';
//         $old_out   = isset($rec['old_out_time']) ? $rec['old_out_time'] : '';
//         $new_in    = isset($rec['new_in_time']) ? $rec['new_in_time'] : '';
//         $new_out   = isset($rec['new_out_time']) ? $rec['new_out_time'] : '';
//         $reason    = isset($rec['change_reason']) ? $rec['change_reason'] : '';
//         $changed_by= isset($rec['changed_by']) ? $rec['changed_by'] : '';
//         $timestamp = isset($rec['change_timestamp']) ? $rec['change_timestamp'] : '';

//         // Lookup employee info by employee_id (emp_id from log)
//         $empInfo = $this->EmployeeInfo->find('first', [
//             'conditions' => ['EmployeeInfo.employee_id' => $emp_id],
//             'fields' => ['EmployeeInfo.EmpName', 'EmployeeInfo.branch'],
//             'recursive' => -1
//         ]);

//         $empName = !empty($empInfo) ? $empInfo['EmployeeInfo']['EmpName'] : '';
//         $branchName = !empty($empInfo) ? $empInfo['EmployeeInfo']['branch'] : '';

//         // Write to Excel
//         $sheet->setCellValue("A{$rowNum}", $emp_id);
//         $sheet->setCellValue("B{$rowNum}", $att_date);
//         $sheet->setCellValue("C{$rowNum}", $empName);
//         $sheet->setCellValue("D{$rowNum}", $branchName);
//         $sheet->setCellValue("E{$rowNum}", $old_in);
//         $sheet->setCellValue("F{$rowNum}", $old_out);
//         $sheet->setCellValue("G{$rowNum}", $new_in);
//         $sheet->setCellValue("H{$rowNum}", $new_out);
//         $sheet->setCellValue("I{$rowNum}", $reason);
//         $sheet->setCellValue("J{$rowNum}", $changed_by);
//         $sheet->setCellValue("K{$rowNum}", $timestamp);

//         $rowNum++;
//     }

//     // Auto size columns A-K
//     foreach (range('A', 'K') as $c) {
//         $sheet->getColumnDimension($c)->setAutoSize(true);
//     }

//     $fileName = "Exception_Logs_{$rule_id}_{$applied_date}.xlsx";
//     header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
//     header("Content-Disposition: attachment;filename=\"{$fileName}\"");
//     header("Cache-Control: max-age=0");

//     $writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//     $writer->save('php://output');
//     exit;
// }
public function downloadExceptionExcel()
{
    $this->autoRender = false;
        $rule_id      = (int)$this->request->query('rule_id');
    $applied_date = $this->request->query('applied_date'); // YYYY-MM-DD   
$applied_month =$this->request->query('applied_month');
$applied_month_date = $applied_month . '-01';   
    if (!$rule_id || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $applied_date)) {
        die("Invalid request parameters");
    }

    $this->ExceptionRule->useDbConfig = $this->Session->read('ds');
    $db = $this->ExceptionRule->getDataSource();

    $sql = "
        SELECT 
            emp_id,
            att_date,
            branch_code,
            old_in_time,
            old_out_time,
            new_in_time,
            new_out_time,
            change_reason,
            changed_by,
            change_timestamp
        FROM exception_attendance_change_log
        WHERE rule_id = {$rule_id}
     AND applied_month = " . $db->value($applied_month_date) . "
        ORDER BY change_id DESC
    ";

    try {
        $rows = $this->ExceptionRule->query($sql);
    } catch (Exception $e) {
        die("Database error");
    }

    // Load PHPExcel
    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
    $objPHPExcel = new PHPExcel();
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle('Exception Logs');
    $sheet->setShowGridlines(false);

    /* ================= HEADER ================= */

    $sheet->setCellValue("A1", "Exception Rule Change Log");
    $sheet->mergeCells("A1:K1");
    $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle("A1")->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue("A2", "Rule ID: {$rule_id}    Date: {$applied_date}");
    $sheet->mergeCells("A2:K2");
    $sheet->getStyle("A2")->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    /* ================= COLUMN HEADERS ================= */

    $headers = array(
        "Emp ID", "Attendance Date", "Employee Name", "Branch",
        "Old In", "Old Out", "New In", "New Out",
        "Change Reason", "Changed By", "Timestamp"
    );

    $headerRow = 4;
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col . $headerRow, $h);
        $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
        $col++;
    }

    $rowNum = $headerRow + 1;

    /* ================= DATA OR NO DATA ================= */

    if (empty($rows)) {

        $sheet->mergeCells("A{$rowNum}:K{$rowNum}");
        $sheet->setCellValue(
            "A{$rowNum}",
            "No exception records found for the selected rule and date."
        );

        $sheet->getStyle("A{$rowNum}")->getFont()->setBold(true)->setItalic(true);
        $sheet->getStyle("A{$rowNum}")->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    } else {

        $this->loadModel('EmployeeInfo');
        $this->EmployeeInfo->useDbConfig = $this->Session->read('ds');

        foreach ($rows as $r) {

            if (isset($r['exception_attendance_change_log'])) {
                $rec = $r['exception_attendance_change_log'];
            } else {
                $rec = $r[0];
            }

            $emp_id     = isset($rec['emp_id']) ? $rec['emp_id'] : '';
            $att_date   = isset($rec['att_date']) ? $rec['att_date'] : '';
            $old_in     = isset($rec['old_in_time']) ? $rec['old_in_time'] : '';
            $old_out    = isset($rec['old_out_time']) ? $rec['old_out_time'] : '';
            $new_in     = isset($rec['new_in_time']) ? $rec['new_in_time'] : '';
            $new_out    = isset($rec['new_out_time']) ? $rec['new_out_time'] : '';
            $reason     = isset($rec['change_reason']) ? $rec['change_reason'] : '';
            $changed_by = isset($rec['changed_by']) ? $rec['changed_by'] : '';
            $timestamp  = isset($rec['change_timestamp']) ? $rec['change_timestamp'] : '';

            $empInfo = $this->EmployeeInfo->find('first', array(
                'conditions' => array('EmployeeInfo.emp_id' => $emp_id),
                'fields' => array('EmployeeInfo.EmpName', 'EmployeeInfo.branch'),
                'recursive' => -1
            ));

            $empName = !empty($empInfo) ? $empInfo['EmployeeInfo']['EmpName'] : '';
            $branch  = !empty($empInfo) ? $empInfo['EmployeeInfo']['branch'] : '';

            $sheet->setCellValue("A{$rowNum}", $emp_id);
            $sheet->setCellValue("B{$rowNum}", $att_date);
            $sheet->setCellValue("C{$rowNum}", $empName);
            $sheet->setCellValue("D{$rowNum}", $branch);
            $sheet->setCellValue("E{$rowNum}", $old_in);
            $sheet->setCellValue("F{$rowNum}", $old_out);
            $sheet->setCellValue("G{$rowNum}", $new_in);
            $sheet->setCellValue("H{$rowNum}", $new_out);
            $sheet->setCellValue("I{$rowNum}", $reason);
            $sheet->setCellValue("J{$rowNum}", $changed_by);
            $sheet->setCellValue("K{$rowNum}", $timestamp);

            $rowNum++;
        }
    }

    /* ================= AUTO SIZE ================= */

    foreach (range('A', 'K') as $c) {
        $sheet->getColumnDimension($c)->setAutoSize(true);
    }

    /* ================= DOWNLOAD ================= */

    $fileName = "Exception_Logs_{$rule_id}_{$applied_date}.xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"{$fileName}\"");
    header('Cache-Control: max-age=0');

    $writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $writer->save('php://output');
    exit;
}

 public function reverseAppliedRule()
    {
        $this->autoRender = false;
        $this->ExceptionRule->useDbConfig = $this->Session->read('ds');

        if (!$this->request->is('post')) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method'
            ]);
            return;
        }

        // Get parameters from POST
        $data = $this->request->data;
        // debug($data);
        
        $exception_applied_pkey = !empty($data['exception_applied_pkey']) ? (int)$data['exception_applied_pkey'] : null;
        $branch_code = !empty($data['branch_code']) ? trim($data['branch_code']) : null;
        $rule_id = !empty($data['rule_id']) ? (int)$data['rule_id'] : null;
        $month_year = !empty($data['month_year']) ? trim($data['month_year']) : null;

        // Validation
        if (!$exception_applied_pkey || !$branch_code || !$rule_id || !$month_year) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Missing required parameters'
            ]);
            return;
        }

        // Convert month_year (YYYY-MM) to month_start (YYYY-MM-01)
        $month_start = $month_year . '-01';

        try {
            // Step 1: Call the reversal procedure
            $proc_sql = "
                CALL exception_rule_reversal_proc(
                    $rule_id,
                    '$branch_code',
                    '$month_start',
                    @p_output
                );
            ";

            $this->ExceptionRule->query($proc_sql);

            // Fetch output from procedure
            $output = $this->ExceptionRule->query("SELECT @p_output AS message");
            $proc_message = !empty($output[0][0]['message'])
                ? $output[0][0]['message']
                : "Reversal procedure executed successfully";

            // Step 2: Delete from exception_applied table
            $delete_sql = "
                DELETE FROM exception_applied
                WHERE exception_applied_pkey = $exception_applied_pkey
            ";

            $this->ExceptionRule->query($delete_sql);

            // Success response
            echo json_encode([
                'status' => 'success',
                'message' => 'Rule reversed and entry deleted successfully',
                'procedure_output' => $proc_message
            ]);

        } catch (Exception $e) {
            // Error response
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to reverse rule: ' . $e->getMessage()
            ]);
        }
    }


}
