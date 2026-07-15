<?php



class TeamReviewController extends AppController
{

    public $uses = array('AppModel', 'LeaveRequests', 'EmpLeaveApproval', 'EmployeeConfig', 'SalaryHeadItems', 'EmployeeDetails', 'EmployeeLeaveTransaction', 'LeavePolicy', 'EmployeeInfo', 'AssessmentAttributesExecutiveDetails');
    public $components = array('Session');



    public function index()
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $emp_pkey = $this->Session->read('emp_fkey');

        // Check role directly from self_review_details
        $roleCheck = $this->EmployeeDetails->find('first', [
            'joins' => [
                [
                    'table' => 'self_review_details',
                    'alias' => 'SelfReview',
                    'type' => 'INNER',
                    'conditions' => [
                        'OR' => [
                            'SelfReview.reporting_officer' => $emp_pkey,
                            'SelfReview.reviewing_officer' => $emp_pkey
                        ]
                    ]
                ]
            ],
            'fields' => ['SelfReview.reporting_officer', 'SelfReview.reviewing_officer']
        ]);

        if (!empty($roleCheck) && isset($roleCheck['SelfReview']['reporting_officer']) && $roleCheck['SelfReview']['reporting_officer'] == $emp_pkey) {
            $role = 'reporting_officer';
        } elseif (!empty($roleCheck)) {
            $role = 'reviewing_officer';
        } else {
            $role = 'employee'; // Or whatever default role makes sense
        }


        $this->set('role', $role);
    }

//     public function listrequest()
//     {
//         $this->autoRender = false;
//         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

//         $status = $_GET['status'];
//         if ($status == "Self Review Completed") {
//             $status = 'Applied';
//         }

//         $officer_id = $this->Session->read('login_user_id');
//         $officer_pkey = $this->EmployeeDetails->query("SELECT emp_fkey FROM user_credentials WHERE user_id='$officer_id'");
//         $officer_pkey = $officer_pkey[0]['user_credentials']['emp_fkey'];
//  $role = 'reviewing_officer';
//         $sql = "
//            SELECT  
//     sr.emp_fkey,sr.reporting_officer,sr.reviewing_officer,sr.self_review_details_pkey, sr.fin_year, sr.status, sr.created_date, sr.created_by, sr.modified_date, sr.modified_by,

//     CONCAT(e.first_name, ' ', COALESCE(e.middile_name, ''), ' ', e.last_name, ' - ', ei.employee_id) AS employee_name,
//     CONCAT(ro.first_name, ' ', COALESCE(ro.middile_name, ''), ' ', ro.last_name, ' - ', roi.employee_id) AS reviewing_officer_name,
//     CONCAT(rp.first_name, ' ', COALESCE(rp.middile_name, ''), ' ', rp.last_name, ' - ', rpi.employee_id) AS reporting_officer_name,

//     CONCAT(cb.first_name, ' ', COALESCE(cb.middile_name, ''), ' ', cb.last_name, ' - ', cbi.employee_id) AS created_by_name,
//     CONCAT(mb.first_name, ' ', COALESCE(mb.middile_name, ''), ' ', mb.last_name, ' - ', mbi.employee_id) AS modified_by_name

// FROM self_review_details sr
// JOIN emp_details e ON sr.emp_fkey = e.emp_pkey
// LEFT JOIN employee_info ei ON ei.emp_pkey = e.emp_pkey

// LEFT JOIN emp_details ro ON sr.reviewing_officer = ro.emp_pkey
// LEFT JOIN employee_info roi ON roi.emp_pkey = ro.emp_pkey

// LEFT JOIN emp_details rp ON sr.reporting_officer = rp.emp_pkey
// LEFT JOIN employee_info rpi ON rpi.emp_pkey = rp.emp_pkey

// LEFT JOIN emp_details cb ON sr.created_by = cb.emp_pkey
// LEFT JOIN employee_info cbi ON cbi.emp_pkey = cb.emp_pkey

// LEFT JOIN emp_details mb ON sr.modified_by = mb.emp_pkey
// LEFT JOIN employee_info mbi ON mbi.emp_pkey = mb.emp_pkey

// WHERE '$officer_pkey' IN (sr.reviewing_officer)";


       

//         //edited by athira on 21-05-2025
//         if (!empty($status)) {
           
//             if ($role === 'reviewing_officer' && $status === 'Reporting Person submitted the Appraisal') {
//                 $sql .= " AND sr.status IN ('Reporting Person submitted the Appraisal','Reviewing Person Drafted the Appraisal')";
//             }

//             if ($role === 'reviewing_officer' && $status === 'Reviewing Person submitted the Appraisal') {
//                 $sql .= " AND sr.status IN ('Reviewing Person submitted the Appraisal')";
//             }
//         }

//         //end


//         $sql .= " ORDER BY sr.created_date DESC";



//         $arr_request = $this->EmployeeDetails->query($sql);

//  $role = 'reporting_officer';
         
//  $sql1 = "
//            SELECT  
//     sr.emp_fkey,sr.reporting_officer,sr.reviewing_officer,sr.self_review_details_pkey, sr.fin_year, sr.status, sr.created_date, sr.created_by, sr.modified_date, sr.modified_by,

//     CONCAT(e.first_name, ' ', COALESCE(e.middile_name, ''), ' ', e.last_name, ' - ', ei.employee_id) AS employee_name,
//     CONCAT(ro.first_name, ' ', COALESCE(ro.middile_name, ''), ' ', ro.last_name, ' - ', roi.employee_id) AS reviewing_officer_name,
//     CONCAT(rp.first_name, ' ', COALESCE(rp.middile_name, ''), ' ', rp.last_name, ' - ', rpi.employee_id) AS reporting_officer_name,

//     CONCAT(cb.first_name, ' ', COALESCE(cb.middile_name, ''), ' ', cb.last_name, ' - ', cbi.employee_id) AS created_by_name,
//     CONCAT(mb.first_name, ' ', COALESCE(mb.middile_name, ''), ' ', mb.last_name, ' - ', mbi.employee_id) AS modified_by_name

// FROM self_review_details sr
// JOIN emp_details e ON sr.emp_fkey = e.emp_pkey
// LEFT JOIN employee_info ei ON ei.emp_pkey = e.emp_pkey

// LEFT JOIN emp_details ro ON sr.reviewing_officer = ro.emp_pkey
// LEFT JOIN employee_info roi ON roi.emp_pkey = ro.emp_pkey

// LEFT JOIN emp_details rp ON sr.reporting_officer = rp.emp_pkey
// LEFT JOIN employee_info rpi ON rpi.emp_pkey = rp.emp_pkey

// LEFT JOIN emp_details cb ON sr.created_by = cb.emp_pkey
// LEFT JOIN employee_info cbi ON cbi.emp_pkey = cb.emp_pkey

// LEFT JOIN emp_details mb ON sr.modified_by = mb.emp_pkey
// LEFT JOIN employee_info mbi ON mbi.emp_pkey = mb.emp_pkey

// WHERE '$officer_pkey' IN (sr.reporting_officer)";


       

//         //edited by athira on 21-05-2025
//         if (!empty($status)) {
//             if ($role === 'reporting_officer' && $status === 'Reporting Person submitted the Appraisal') {
//                 // Show both Authorized and Approved for reporting officer
//                 $sql1 .= " AND sr.status IN ('Applied','Reporting Person submitted the Appraisal','Reviewing Person submitted the Appraisal')";
//             }
//             if ($role === 'reporting_officer' && $status === 'Applied') {
//                 $sql1 .= " AND sr.status IN ('Applied','Reporting Person Drafted the Appraisal','Reviewing Person Rejected the Appraisal')";
//             }

           
//         }

//         //end


//         $sql1 .= " ORDER BY sr.created_date DESC";



//         $arr_request1 = $this->EmployeeDetails->query($sql1);
//         $response = ["rows" => [], "total" => count($arr_request)+count($arr_request1)];

//         foreach ($arr_request as $value) {
//             $createdDate = isset($value["sr"]["created_date"]) && !empty($value["sr"]["created_date"])
//                 ? date('d-m-Y H:i:s', strtotime($value["sr"]["created_date"]))
//                 : ' ';

//             $modifiedDate = isset($value["sr"]["modified_date"]) && !empty($value["sr"]["modified_date"])
//                 ? date('d-m-Y H:i:s', strtotime($value["sr"]["modified_date"]))
//                 : ' ';


//             if ($value["sr"]["status"] === 'Applied') {
//                 $statusDisplay = 'Self Review Completed';
//             } elseif ($value["sr"]["status"] === 'New') {
//                 $statusDisplay = 'Self Review Initiated';
//             } else {
//                 $statusDisplay = $value["sr"]["status"];
//             }
//             //edited by athira on 28-05-2025
//             $response["rows"][] = [
//                 "emp_fkey" => $value['sr']['emp_fkey'],
//                 //edited by athira on 22-05-2025
//                 "pkey" => $value['sr']['self_review_details_pkey'],
//                 //end
//                 "reporting_officer" => $value['sr']['reporting_officer'],
//                 "reviewing_officer" => $value['sr']['reviewing_officer'],
//                 "employee_name" => $value[0]["employee_name"],
//                 "reviewing_officer_name" => $value[0]["reviewing_officer_name"],
//                 "reporting_officer_name" => $value[0]["reporting_officer_name"],
//                 // "status" => $value["sr"]["status"],
//                 "status" => $statusDisplay,
//                 "created_date" => $createdDate,
//                 "modified_date" => $modifiedDate,
//                 "created_by" => $value[0]["created_by_name"],
//                 "modified_by" => $value[0]["modified_by_name"]
//             ];
//             //end
//         }
//      //   debug($response);
        

// //debug($sql1);
//        // $response = ["rows" => [], "total" => count($arr_request1)];

//         foreach ($arr_request1 as $value) {
//           //  debug($response);
//             $createdDate = isset($value["sr"]["created_date"]) && !empty($value["sr"]["created_date"])
//                 ? date('d-m-Y H:i:s', strtotime($value["sr"]["created_date"]))
//                 : ' ';

//             $modifiedDate = isset($value["sr"]["modified_date"]) && !empty($value["sr"]["modified_date"])
//                 ? date('d-m-Y H:i:s', strtotime($value["sr"]["modified_date"]))
//                 : ' ';


//             if ($value["sr"]["status"] === 'Applied') {
//                 $statusDisplay = 'Self Review Completed';
//             } elseif ($value["sr"]["status"] === 'New') {
//                 $statusDisplay = 'Self Review Initiated';
//             } else {
//                 $statusDisplay = $value["sr"]["status"];
//             }
//             //edited by athira on 28-05-2025
//             $response["rows"][] = [
//                 "emp_fkey" => $value['sr']['emp_fkey'],
//                 //edited by athira on 22-05-2025
//                 "pkey" => $value['sr']['self_review_details_pkey'],
//                 //end
//                 "reporting_officer" => $value['sr']['reporting_officer'],
//                 "reviewing_officer" => $value['sr']['reviewing_officer'],
//                 "employee_name" => $value[0]["employee_name"],
//                 "reviewing_officer_name" => $value[0]["reviewing_officer_name"],
//                 "reporting_officer_name" => $value[0]["reporting_officer_name"],
//                 // "status" => $value["sr"]["status"],
//                 "status" => $statusDisplay,
//                 "created_date" => $createdDate,
//                 "modified_date" => $modifiedDate,
//                 "created_by" => $value[0]["created_by_name"],
//                 "modified_by" => $value[0]["modified_by_name"]
//             ];
//             //end
//         }
// //  debug($response);

//         echo json_encode($response);
//     }
public function listrequest()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $status = $_GET['status'];
        $position = $_GET['position'];

        //edited by athira on 11-06-2025
        // Pagination parameters
        $page = isset($this->request->query['page']) ? (int)$this->request->query['page'] : 1;
        $rows = isset($this->request->query['rows']) ? (int)$this->request->query['rows'] : 10;
        $offset = ($page - 1) * $rows;
        //end

        if ($status == "Self Review Completed") {
            $status = 'Applied';
        }

        $officer_id = $this->Session->read('login_user_id');
        $officer_pkey = $this->EmployeeDetails->query("SELECT emp_fkey FROM user_credentials WHERE user_id='$officer_id'");
        $officer_pkey = $officer_pkey[0]['user_credentials']['emp_fkey'];

        //edited by athira on 11-06-2025
        // Common WHERE conditions
        $where = " WHERE '$officer_pkey' IN (sr.reviewing_officer, sr.reporting_officer) ";

        $role = $_GET['role'];

        //edited by athira on 21-05-2025
        if (!empty($status)) {
            // if ($role === 'reporting_officer' && $status === 'Reporting Person submitted the Appraisal') {
            //     $where .= " AND sr.status IN ('Reporting Person submitted the Appraisal','Reviewing Person submitted the Appraisal')";
            // } elseif ($role === 'reporting_officer' && $status === 'Applied') {
            //     $where .= " AND sr.status IN ('Applied','Reporting Person Drafted the Appraisal','Reviewing Person Rejected the Appraisal')";
            // } elseif ($role === 'reviewing_officer' && $status === 'Reporting Person submitted the Appraisal') {
            //     $where .= " AND sr.status IN ('Reporting Person submitted the Appraisal','Reviewing Person Drafted the Appraisal')";
            // } elseif ($role === 'reviewing_officer' && $status === 'Reviewing Person submitted the Appraisal') {
            //     $where .= " AND sr.status IN ('Reviewing Person submitted the Appraisal')";
            // }

            if ($position === 'left') {
                $where .= " AND ((sr.status IN ('Applied', 'Reporting Person Drafted the Appraisal', 'Reviewing Person Rejected the Appraisal') AND sr.reporting_officer = '$officer_pkey')
                        OR (sr.status IN ('Reporting Person submitted the Appraisal', 'Reviewing Person Drafted the Appraisal') AND sr.reviewing_officer = '$officer_pkey')
                            )";
            } else {
                $where .= " AND ((sr.status IN ('Reporting Person submitted the Appraisal', 'Reviewing Person submitted the Appraisal','Reviewing Person Drafted the Appraisal') AND sr.reporting_officer = '$officer_pkey')
                        OR (sr.status IN ('Reviewing Person submitted the Appraisal') AND sr.reviewing_officer = '$officer_pkey')
                            )";
            }
        }
        //end


        // Total count query
        $countQuery = "
        SELECT COUNT(*) AS total
        FROM self_review_details sr
        JOIN emp_details e ON sr.emp_fkey = e.emp_pkey
        $where
        ";
        $countResult = $this->EmployeeDetails->query($countQuery);
        $totalCount = $countResult[0][0]['total'];

        // Main paginated data query
        $sql = "
            SELECT
                sr.emp_fkey, sr.reporting_officer, sr.reviewing_officer, sr.self_review_details_pkey,
                sr.fin_year, sr.status, sr.created_date, sr.created_by, sr.modified_date, sr.modified_by,

                CONCAT(e.first_name, ' ', COALESCE(e.middile_name, ''), ' ', e.last_name, ' - ', ei.employee_id) AS employee_name,
                CONCAT(ro.first_name, ' ', COALESCE(ro.middile_name, ''), ' ', ro.last_name, ' - ', roi.employee_id) AS reviewing_officer_name,
                CONCAT(rp.first_name, ' ', COALESCE(rp.middile_name, ''), ' ', rp.last_name, ' - ', rpi.employee_id) AS reporting_officer_name,
                CONCAT(cb.first_name, ' ', COALESCE(cb.middile_name, ''), ' ', cb.last_name, ' - ', cbi.employee_id) AS created_by_name,
                CONCAT(mb.first_name, ' ', COALESCE(mb.middile_name, ''), ' ', mb.last_name, ' - ', mbi.employee_id) AS modified_by_name

            FROM self_review_details sr
            JOIN emp_details e ON sr.emp_fkey = e.emp_pkey
            LEFT JOIN employee_info ei ON ei.emp_pkey = e.emp_pkey
            LEFT JOIN emp_details ro ON sr.reviewing_officer = ro.emp_pkey
            LEFT JOIN employee_info roi ON roi.emp_pkey = ro.emp_pkey
            LEFT JOIN emp_details rp ON sr.reporting_officer = rp.emp_pkey
            LEFT JOIN employee_info rpi ON rpi.emp_pkey = rp.emp_pkey
            LEFT JOIN emp_details cb ON sr.created_by = cb.emp_pkey
            LEFT JOIN employee_info cbi ON cbi.emp_pkey = cb.emp_pkey
            LEFT JOIN emp_details mb ON sr.modified_by = mb.emp_pkey
            LEFT JOIN employee_info mbi ON mbi.emp_pkey = mb.emp_pkey

            $where
            ORDER BY sr.created_date DESC
            LIMIT $rows OFFSET $offset
        ";

        $arr_request = $this->EmployeeDetails->query($sql);
        $response = ["rows" => [], "total" => $totalCount];

        //end

        foreach ($arr_request as $value) {
            $createdDate = isset($value["sr"]["created_date"]) && !empty($value["sr"]["created_date"])
                ? date('d-m-Y H:i:s', strtotime($value["sr"]["created_date"]))
                : ' ';

            $modifiedDate = isset($value["sr"]["modified_date"]) && !empty($value["sr"]["modified_date"])
                ? date('d-m-Y H:i:s', strtotime($value["sr"]["modified_date"]))
                : ' ';


            if ($value["sr"]["status"] === 'Applied') {
                $statusDisplay = 'Self Review Completed';
            } elseif ($value["sr"]["status"] === 'New') {
                $statusDisplay = 'Self Review Initiated';
            } else {
                $statusDisplay = $value["sr"]["status"];
            }
            //edited by athira on 28-05-2025
            $response["rows"][] = [
                "emp_fkey" => $value['sr']['emp_fkey'],
                //edited by athira on 22-05-2025
                "pkey" => $value['sr']['self_review_details_pkey'],
                //end
                "reporting_officer" => $value['sr']['reporting_officer'],
                "reviewing_officer" => $value['sr']['reviewing_officer'],
                "employee_name" => $value[0]["employee_name"],
                "reviewing_officer_name" => $value[0]["reviewing_officer_name"],
                "reporting_officer_name" => $value[0]["reporting_officer_name"],
                // "status" => $value["sr"]["status"],
                "status" => $statusDisplay,
                "created_date" => $createdDate,
                "modified_date" => $modifiedDate,
                "created_by" => $value[0]["created_by_name"],
                "modified_by" => $value[0]["modified_by_name"]
            ];
            //end
        }


        echo json_encode($response);
    }

    public function saveRequest()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->AssessmentAttributesExecutiveDetails->useDbConfig = $this->Session->read('ds');

        $arr_data = $this->request->data;
        $pkey = $arr_data['pkey'];
        $actionType = isset($arr_data['action_type']) ? $arr_data['action_type'] : 'approve';
        $emp_fkey = $arr_data['emp_fkey'];
        $emp_pkey = $this->Session->read('emp_fkey');
      
        $roleCheck = $this->EmployeeDetails->find('first', [
            'joins' => [
                [
                    'table' => 'self_review_details',
                    'alias' => 'SelfReview',
                    'type' => 'INNER',
                    'conditions' => [
                        'OR' => [
                            'SelfReview.self_review_details_pkey' => $pkey,
                        ]
                    ]
                ]
            ],
            'fields' => ['SelfReview.reporting_officer', 'SelfReview.reviewing_officer']
        ]);

        if ($roleCheck['SelfReview']['reporting_officer'] == $emp_pkey) {
            $role = 'reporting_officer';
        } else {
            $role = 'reviewing_officer';
        }

        $fin_years = $this->EmployeeDetails->query("
    SELECT fin_year 
    FROM self_review_details 
    WHERE self_review_details_pkey = $pkey
    
");

        $finYear = !empty($fin_years) ? $fin_years[0]['self_review_details']['fin_year'] : null;



        // ✅ Handle draft action

        if ($actionType === 'draft') {
            $statusToUpdate = ($role === 'reporting_officer')
                ? 'Reporting Person Drafted the Appraisal'
                : 'Reviewing Person Drafted the Appraisal';

            $statusColumn = $role === 'reporting_officer' ? 'reporting_officer_marks' : 'reviewing_officer_marks';
            $statusFlagColumn = $role === 'reporting_officer' ? 'reporting_officer_status' : 'reviewing_officer_status';

            // Loop through attributes and save marks
            foreach ($arr_data['attributes'] as $value) {
                if (isset($value['attribute_key'])) {
                    $attributeKey = (int)$value['attribute_key'];
                    $mark = isset($value['mark']) ? (float)$value['mark'] : 'NULL';

                    $existing = $this->EmployeeDetails->query("
                SELECT attr_exec_details_pkey FROM assessment_attributes_executive_details 
                WHERE emp_fkey = $emp_fkey 
                  AND attributes_exec_fkey = $attributeKey 
                  AND fin_year = '$finYear'
            ");

                    if (!empty($existing)) {
                        $this->EmployeeDetails->query("
                    UPDATE assessment_attributes_executive_details 
                    SET $statusColumn = $mark,
                        $statusFlagColumn = 0,
                        status = '$statusToUpdate',
                        modified_by = $emp_pkey,
                        modification_date = NOW()
                    WHERE emp_fkey = $emp_fkey 
                      AND attributes_exec_fkey = $attributeKey 
                      AND fin_year = '$finYear'
                ");
                    } else {
                        $reportingOfficerMarks = $role === 'reporting_officer' ? $mark : 'NULL';
                        $reviewingOfficerMarks = $role === 'reviewing_officer' ? $mark : 'NULL';
                        $reportingStatus = $role === 'reporting_officer' ? 0 : 0;
                        $reviewingStatus = $role === 'reviewing_officer' ? 0 : 0;

                        $this->EmployeeDetails->query("
                    INSERT INTO assessment_attributes_executive_details 
                    (attributes_exec_fkey, emp_fkey, fin_year, reporting_officer_marks, reviewing_officer_marks, reporting_officer_status, reviewing_officer_status, created_by, creation_date, status) 
                    VALUES (
                        $attributeKey, 
                        $emp_fkey, 
                        '$finYear', 
                        $reportingOfficerMarks, 
                        $reviewingOfficerMarks, 
                        $reportingStatus, 
                        $reviewingStatus,
                        $emp_pkey, 
                        NOW(), 
                        '$statusToUpdate'
                    )
                ");
                    }
                }
            }

            // Save summary (comments, training, etc.)
            $totalMarks = isset($arr_data['total_marks']) ? (float)$arr_data['total_marks'] : 0;
            $grade = isset($arr_data['grade']) ? addslashes($arr_data['grade']) : '';
            $comments = isset($arr_data['comments']) ? addslashes($arr_data['comments']) : '';
            $trainingNeed = isset($arr_data['training_need']) ? addslashes($arr_data['training_need']) : '';
            $agreement = isset($arr_data['agreement_with_part2']) ? addslashes($arr_data['agreement_with_part2']) : '';

            $existingSummary = $this->EmployeeDetails->query("
        SELECT summary_exec_pkey FROM assessment_summary_executive 
        WHERE emp_fkey = $emp_fkey 
          AND officer_fkey = $emp_pkey 
          AND fin_year = '$finYear'
    ");

            if (!empty($existingSummary)) {
                $this->EmployeeDetails->query("
            UPDATE assessment_summary_executive SET
                total_marks = $totalMarks,
                grade = '$grade',
                comments_recommendation = '$comments',
                training_need = '$trainingNeed',
                agreement_comment = '$agreement',
                modified_by = $emp_pkey,
                modified_date = NOW(),
                status = '$statusToUpdate'
            WHERE emp_fkey = $emp_fkey 
              AND officer_fkey = $emp_pkey 
              AND fin_year = '$finYear'
        ");
            } else {
                $this->EmployeeDetails->query("
            INSERT INTO assessment_summary_executive 
            (emp_fkey, officer_fkey, fin_year, total_marks, grade, comments_recommendation, training_need, agreement_comment, created_by, created_date, status)
            VALUES (
                $emp_fkey,
                $emp_pkey,
                '$finYear',
                $totalMarks,
                '$grade',
                '$comments',
                '$trainingNeed',
                '$agreement',
                $emp_pkey,
                NOW(),
                '$statusToUpdate'
            )
        ");
            }

            // Update self_review_details
            $this->EmployeeDetails->query("
        UPDATE self_review_details
        SET
            status = '$statusToUpdate',
            drafted_by = $emp_pkey,
            drafted_date = NOW(),
            is_drafted=1,
            is_rejected=0,
            is_reported=0,
            is_reviewed=0,
            modified_by = $emp_pkey,
            modified_date = NOW()
        WHERE emp_fkey = $emp_fkey
          AND fin_year = '$finYear'
    ");

            echo json_encode([
                'status' => 'success',
                'message' => 'Draft saved successfully.'
            ]);
            return;
        }

        //handle send back
        if ($actionType === 'send_back') {
            $statusToUpdate = ($role === 'reporting_officer') ? 'Reporting Person Rejected the Appraisal' : 'Reviewing Person Rejected the Appraisal';
            $statusColumnReset = ($role === 'reporting_officer')
                ? "reporting_officer_status = 0"
                : "reviewing_officer_status = 0";

            if ($statusToUpdate == 'Reviewing Person Rejected the Appraisal') {
                $was_rejected_by = 1;
            } else {
                $was_rejected_by = 0;
            }
            //edited by athira on 23-05-2025
            // Update self_review_details
            $this->EmployeeDetails->query("
                UPDATE self_review_details
                SET rejected_by='$emp_pkey',
                rejected_date=NOW(),
                is_rejected=1,
                was_rejected_by_reviewer=$was_rejected_by,
                is_reported=0,
                is_reviewed=0,
                is_drafted=0,
                status = '$statusToUpdate',
                    modified_by = $emp_pkey,
                    modified_date = NOW()
                WHERE emp_fkey = $emp_fkey
                  AND fin_year = '$finYear'
            ");

            //end

            // Update assessment_attributes_executive_details
            $this->EmployeeDetails->query("
                UPDATE assessment_attributes_executive_details
                SET status = '$statusToUpdate',
                    $statusColumnReset
                WHERE emp_fkey = $emp_fkey
                  AND fin_year = '$finYear'
            ");

            echo json_encode([
                'status' => 'success',
                'message' => 'Assessment has been sent back '
            ]);
            return;
        }


        $userRole = $this->Session->read('user_role');
        $selectedEmpId = $arr_data['emp_fkey'];

        foreach ($arr_data['attributes'] as $value) {
            if (isset($value['attribute_key'], $value['mark']) && $value['mark'] !== '') {
                $attributeKey = (int)$value['attribute_key'];
                $mark = (float)$value['mark'];

                $existing = $this->EmployeeDetails->query("
                    SELECT attr_exec_details_pkey FROM assessment_attributes_executive_details 
                    WHERE emp_fkey = $emp_fkey 
                      AND attributes_exec_fkey = $attributeKey 
                      AND fin_year = '$finYear'
                ");

                if (!empty($existing)) {
                    $columnToUpdate = $role === 'reporting_officer' ? 'reporting_officer_marks' : 'reviewing_officer_marks';
                    $statusUpdate = '';

                    if ($role === 'reporting_officer') {
                        $statusUpdate = "reporting_officer_status = 1, status = 'Reporting Person submitted the Appraisal'";
                    } else {
                        $checkReportingStatus = $this->EmployeeDetails->query("
                            SELECT reporting_officer_status FROM assessment_attributes_executive_details 
                            WHERE emp_fkey = $emp_fkey 
                              AND attributes_exec_fkey = $attributeKey 
                              AND fin_year = '$finYear'
                        ");

                        if (empty($checkReportingStatus) || $checkReportingStatus[0]['assessment_attributes_executive_details']['reporting_officer_status'] != 1) {
                            echo json_encode(['status' => 'error', 'message' => 'Reporting Officer has not completed the assessment yet.']);
                            return;
                        }

                        $statusUpdate = "reviewing_officer_status = 1, status = 'Reviewing Person submitted the Appraisal'";
                    }

                    $this->EmployeeDetails->query("
                        UPDATE assessment_attributes_executive_details 
                        SET $columnToUpdate = $mark,
                            $statusUpdate,
                            modified_by = $emp_pkey,
                            modification_date = NOW()
                        WHERE emp_fkey = $emp_fkey 
                          AND attributes_exec_fkey = $attributeKey 
                          AND fin_year = '$finYear'
                    ");
                } else {
                    $reportingOfficerMarks = $role === 'reporting_officer' ? $mark : 'NULL';
                    $reviewingOfficerMarks = $role === 'reviewing_officer' ? $mark : 'NULL';
                    $reportingStatus = $role === 'reporting_officer' ? 1 : 0;
                    $reviewingStatus = $role === 'reviewing_officer' ? 1 : 0;
                    $finalStatus = $role === 'reporting_officer' ? 'Reporting Person submitted the Appraisal' : 'Reviewing Person submitted the Appraisal';

                    $this->EmployeeDetails->query("
                        INSERT INTO assessment_attributes_executive_details 
                        (attributes_exec_fkey, emp_fkey, fin_year, reporting_officer_marks, reviewing_officer_marks, reporting_officer_status, reviewing_officer_status, created_by, creation_date, status) 
                        VALUES (
                            $attributeKey, 
                            $emp_fkey, 
                            '$finYear', 
                            $reportingOfficerMarks, 
                            $reviewingOfficerMarks, 
                            $reportingStatus, 
                            $reviewingStatus,
                            $emp_pkey, 
                            NOW(), 
                            '$finalStatus'
                        )
                    ");
                }
            }
        }


        $totalMarks = isset($arr_data['total_marks']) ? (float)$arr_data['total_marks'] : 0;
        $grade = isset($arr_data['grade']) ? addslashes($arr_data['grade']) : '';
        $comments = isset($arr_data['comments']) ? addslashes($arr_data['comments']) : '';
        $trainingNeed = isset($arr_data['training_need']) ? addslashes($arr_data['training_need']) : '';
        $agreement = isset($arr_data['agreement_with_part2']) ? addslashes($arr_data['agreement_with_part2']) : '';

        $existingSummary = $this->EmployeeDetails->query("
            SELECT summary_exec_pkey FROM assessment_summary_executive 
            WHERE emp_fkey = $emp_fkey 
              AND officer_fkey = $emp_pkey 
              AND fin_year = '$finYear'
        ");

        if (!empty($existingSummary)) {
            $this->EmployeeDetails->query("
                UPDATE assessment_summary_executive SET
                    total_marks = $totalMarks,
                    grade = '$grade',
                    comments_recommendation = '$comments',
                    training_need = '$trainingNeed',
                    agreement_comment = '$agreement',
                    modified_by = $emp_pkey,
                    modified_date = NOW()
                WHERE emp_fkey = $emp_fkey 
                  AND officer_fkey = $emp_pkey 
                  AND fin_year = '$finYear'
            ");
        } else {
            $this->EmployeeDetails->query("
                INSERT INTO assessment_summary_executive 
                (emp_fkey, officer_fkey, fin_year, total_marks, grade, comments_recommendation, training_need, agreement_comment, created_by, created_date)
                VALUES (
                    $emp_fkey,
                    $emp_pkey,
                    '$finYear',
                    $totalMarks,
                    '$grade',
                    '$comments',
                    '$trainingNeed',
                    '$agreement',
                    $emp_pkey,
                    NOW()
                )
            ");
        }
        //edited by athira 23-05-2025
        if ($role === 'reporting_officer') {
            $this->EmployeeDetails->query("
                        UPDATE self_review_details
                        SET reported_by='$emp_pkey',
                        reported_date = NOW(),
                        is_reported=1,
                        is_rejected=0,
                        is_reviewed=0,
                        is_drafted=0,
                        status = 'Reporting Person submitted the Appraisal',
                            modified_by = $emp_pkey,
                            modified_date = NOW()
                        WHERE emp_fkey = $emp_fkey
                          
                    ");
        } else {
            $this->EmployeeDetails->query("
                        UPDATE self_review_details
                        SET  reviewed_by='$emp_pkey',
                        reviewed_date = NOW(),
                        is_reviewed=1,
                        is_rejected=0,
                        is_reported=0,
                        is_drafted=0,
                        status = 'Reviewing Person submitted the Appraisal',
                            modified_by = $emp_pkey,
                            modified_date = NOW()
                        WHERE emp_fkey = $emp_fkey
                          
                    ");
        }

        //end

        $message = ($role === 'reporting_officer')
            ? 'Assessment authorized successfully '
            : 'Assessment approved successfully ';

        echo json_encode(['status' => 'success', 'message' => $message]);

        return;
    }


    public function approverequest($emp_fkey = null)
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $attributes = $this->EmployeeDetails->query("SELECT attributes_exec_pkey,attributes from assessment_attributes_executive");
        $selected_emp_id = $this->request->query['emp_fkey'];
        $pkey = $this->request->query['pkey'];
        $emp_pkey = $this->Session->read('emp_fkey');
        //edited by athira on 04-07-2025
        $reporting_officer=$this->request->query['reporting_officer'];
        $reviewing_officer=$this->request->query['reviewing_officer'];
        $emp_pkey = $this->Session->read('emp_fkey');

        // Determine the role
        if ( $emp_pkey == $reporting_officer) {
            $role = 'reporting_officer';
        } elseif ($emp_pkey == $reviewing_officer) {
            $role = 'reviewing_officer';
        } else {
            $role = 'employee'; // Default case if neither
        }
        //end
        // Check role directly from self_review_details
        // $roleCheck = $this->EmployeeDetails->find('first', [
        //     'joins' => [
        //         [
        //             'table' => 'self_review_details',
        //             'alias' => 'SelfReview',
        //             'type' => 'INNER',
        //             'conditions' => [
        //                 'OR' => [
        //                     'SelfReview.self_review_details_pkey' => $pkey ,
        //                    // 'SelfReview.reviewing_officer' => $emp_pkey
        //                 ]
        //             ]
        //         ]
        //     ],
        //     'fields' => ['SelfReview.reporting_officer', 'SelfReview.reviewing_officer']
        // ]);

        // if ($roleCheck['SelfReview']['reporting_officer'] == $emp_pkey) {
        //     $role = 'reporting_officer';
        // } elseif ($roleCheck['SelfReview']['reviewing_officer'] == $emp_pkey) {
        //     $role = 'reviewing_officer';
        // } else {
        //     $role = 'employee'; // Default case if neither
        // }
//debug($role);
        // Store role in session for conditional rendering
        $this->Session->write('user_role', $role);
        $this->set('role', $role);

        //edited by athira on 02-06-2025
        $self_review_details = $this->EmployeeDetails->query("
        SELECT was_rejected_by_reviewer,duty_desc, work_done_desc 
        FROM self_review_details 
        WHERE self_review_details_pkey = '$pkey' 
        ORDER BY created_date DESC, modified_date DESC
        LIMIT 1
        ");
        //end


        //edited by athira on 06-05-2025
        $employee_details = $this->EmployeeDetails->query("
        SELECT  
            CONCAT(e.first_name, ' ', COALESCE(e.middile_name, ''), ' ', e.last_name) AS employee_name,
            p.designation,
            p.employee_id
        FROM emp_details e
        JOIN employee_info p ON e.emp_pkey = p.emp_pkey
        WHERE e.emp_pkey='$selected_emp_id';
        ");
        //end

        $emp_fkey = $this->request->query['emp_fkey'];
        $reporting_officer = $this->request->query['reporting_officer'];
        $reviewing_officer = $this->request->query['reviewing_officer'];
        // Get the correct fin_year from self_review_details
        $fin_years = $this->EmployeeDetails->query("
        SELECT fin_year 
        FROM self_review_details 
        WHERE  self_review_details_pkey= $pkey
        
        ");
        $fin_year = !empty($fin_years) ? $fin_years[0]['self_review_details']['fin_year'] : null;

        // Edited by Akshay on 3-6-2025
        $arr_dates = $this->EmployeeDetails->query("
        SELECT reported_date, reviewed_date 
        FROM self_review_details 
        WHERE  self_review_details_pkey= $pkey
        
        ");
        $reported_date = isset($arr_dates[0]['self_review_details']['reported_date']) ? $arr_dates[0]['self_review_details']['reported_date'] : '';
        $this->set('reported_date', $reported_date);
        $reviewed_date = isset($arr_dates[0]['self_review_details']['reviewed_date']) ? $arr_dates[0]['self_review_details']['reviewed_date'] : '';
        $this->set('reviewed_date', $reviewed_date);
        // End

        $attributeMarks = $this->EmployeeDetails->query("
        SELECT d.*, a.attributes,a.attributes_exec_pkey
        FROM assessment_attributes_executive_details d
        JOIN assessment_attributes_executive a ON d.attributes_exec_fkey = a.attributes_exec_pkey
        WHERE d.emp_fkey = $emp_fkey  AND fin_year = '$fin_year'
        ");


        $this->set('attributeMarks', $attributeMarks);

        $summaryData = $this->EmployeeDetails->query("
            SELECT * FROM assessment_summary_executive
            WHERE emp_fkey = $emp_fkey AND fin_year = '$fin_year'
        ");

        $reportingSummary = null;
        $reviewingSummary = null;

        foreach ($summaryData as $row) {
            $data = isset($row['assessment_summary_executive']) ? $row['assessment_summary_executive'] : $row[0];

            if ($role == 'reporting_officer' && $data['officer_fkey'] == $emp_pkey) {
                $reportingSummary = $data;
            } elseif ($role == 'reviewing_officer' && $data['officer_fkey'] == $emp_pkey) {
                $reviewingSummary = $data;
            } else {
                // Whichever one is not the logged-in officer is the counterpart
                if ($role == 'reporting_officer') {
                    $reviewingSummary = $data;
                } else {
                    $reportingSummary = $data;
                }
            }
        }



        $this->set(compact('reportingSummary', 'reviewingSummary', 'role'));



        $reporting_data = $this->EmployeeDetails->query("
        SELECT DISTINCT ei.EmpName, ei.designation
        FROM employee_info ei
        JOIN self_review_details sr ON sr.reporting_officer = ei.emp_pkey
        WHERE sr.reporting_officer='$reporting_officer' 
        ");

        $reviewing_data = $this->EmployeeDetails->query("
        SELECT DISTINCT ei.EmpName, ei.designation
        FROM employee_info ei
        JOIN self_review_details sr ON sr.reviewing_officer = ei.emp_pkey
        WHERE sr.reviewing_officer='$reviewing_officer'  
            ");


        $this->set('reporting_data', $reporting_data);
        $this->set('reviewing_data', $reviewing_data);
        $this->set('attributes', $attributes);
        $this->set('pkey', $pkey);
        $this->set('selected_emp_id', $selected_emp_id);
        $this->set('self_review_details', $self_review_details);
        $this->set('employee_details', $employee_details);
    }


    public function viewForm()
    {
        // Load summary data for this employee and officer
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // Fetch emp_pkey from user_credentials
        $emp_pkey = $this->Session->read('emp_fkey');
        $emp_fkey = $this->request->query['emp_fkey'];
        $pkey = $this->request->query['pkey'];
        // Determine role from self_review_details
        $roleCheck = $this->EmployeeDetails->find('first', [
            'joins' => [
                [
                    'table' => 'self_review_details',
                    'alias' => 'SelfReview',
                    'type' => 'INNER',
                    'conditions' => [
                        'OR' => [
                            'SelfReview.self_review_details_pkey' => $pkey,
                        ]
                    ]
                ]
            ],
            'fields' => ['SelfReview.reporting_officer', 'SelfReview.reviewing_officer']
        ]);


        if ($roleCheck['SelfReview']['reporting_officer'] == $emp_pkey) {
            $officerRole = 'reporting_officer';
        } else {
            $officerRole = 'reviewing_officer';
        }

        // Get the correct fin_year from self_review_details
        $fin_years = $this->EmployeeDetails->query("
    SELECT fin_year 
    FROM self_review_details 
    WHERE self_review_details_pkey = $pkey
    
");

        $fin_year = !empty($fin_years) ? $fin_years[0]['self_review_details']['fin_year'] : null;


        if (!$fin_year) {
            echo "Unable to determine financial year.";
            return;
        }

        $this->set('officerRole', $officerRole);
        $summary = $this->EmployeeDetails->query("
    SELECT * FROM assessment_summary_executive 
    WHERE emp_fkey = $emp_fkey  AND fin_year = '$fin_year' AND officer_fkey = $emp_pkey 
    ")[0]['assessment_summary_executive'];

        // Load attribute-wise marks
        $attributeMarks = $this->EmployeeDetails->query("
    SELECT d.*, a.attributes 
    FROM assessment_attributes_executive_details d
    JOIN assessment_attributes_executive a ON d.attributes_exec_fkey = a.attributes_exec_pkey
    WHERE d.emp_fkey = $emp_fkey  AND fin_year = '$fin_year'
    ");

        //edited by athira on 06-05-2025
        $employee_details = $this->EmployeeDetails->query("
    SELECT  
        CONCAT(e.first_name, ' ', COALESCE(e.middile_name, ''), ' ', e.last_name) AS employee_name,
        p.designation,
        p.employee_id
    FROM emp_details e
    JOIN employee_info p ON e.emp_pkey = p.emp_pkey
    WHERE e.emp_pkey='$emp_fkey';
    ");


        //end
        $this->set('summary', $summary);
        $this->set('attributeMarks', $attributeMarks);
        $this->set('employee_details', $employee_details);
    }

    public function previewPdfReview($pkey = null, $self_pkey = null)
    {
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        $fontPath = WWW_ROOT . 'fonts' . DS . 'timesnewromanbold.ttf'; // Ensure the TTF file exists at this location

        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->layout = null; // Disable default layout

        // Load model and data
        // $this->loadModel('SelfReview');
        // $review = $this->SelfReview->find('first', [
        //     'conditions' => ['SelfReview.pkey' => $pkey],
        //     'recursive' => 1
        // ]);

        // $this->set('review', $review);

        $emp_pkey = $this->Session->read('emp_fkey');
        $this->set('loggedInOfficerId', $emp_pkey);




        // Check role directly from self_review_details
        $roleCheck = $this->EmployeeDetails->find('first', [
            'joins' => [
                [
                    'table' => 'self_review_details',
                    'alias' => 'SelfReview',
                    'type' => 'INNER',
                    'conditions' => [
                        'OR' => [
                            'SelfReview.reporting_officer' => $emp_pkey,
                            'SelfReview.reviewing_officer' => $emp_pkey
                        ]
                    ]
                ]
            ],
            'fields' => ['SelfReview.reporting_officer', 'SelfReview.reviewing_officer']
        ]);

        if (!empty($roleCheck) && isset($roleCheck['SelfReview']['reporting_officer']) && $roleCheck['SelfReview']['reporting_officer'] == $emp_pkey) {
            $role = 'reporting_officer';
        } elseif (!empty($roleCheck)) {
            $role = 'reviewing_officer';
        } else {
            $role = 'employee'; // Or whatever default role makes sense
        }


        $this->set('role', $role);


        $emp_fkey = $pkey;

        $fin_years = $this->EmployeeDetails->query("
    SELECT fin_year 
    FROM self_review_details 
    WHERE self_review_details_pkey = $self_pkey
    
");

        $finYear = !empty($fin_years) ? $fin_years[0]['self_review_details']['fin_year'] : null;


        $emp_data = $this->EmployeeDetails->query("
        SELECT e.EmpName, s.*
        FROM self_review_details s
        JOIN employee_info e ON s.emp_fkey = e.emp_pkey where s.emp_fkey='$emp_fkey' AND fin_year='$finYear'
        ");
        //edited by athira on 02-06-2025

        $reportingDate = null;
        $reviewingDate = null;

        if (!empty($emp_data)) {
            $reportingRaw = isset($emp_data[0]['s']['reported_date']) ? $emp_data[0]['s']['reported_date'] : null;
            $reviewingRaw = isset($emp_data[0]['s']['reviewed_date']) ? $emp_data[0]['s']['reviewed_date'] : null;

            if ($reportingRaw) {
                $reportingDate = date('d-m-Y', strtotime($reportingRaw));
            }

            if ($reviewingRaw) {
                $reviewingDate = date('d-m-Y', strtotime($reviewingRaw));
            }
        }

        $this->set('reportingDate', $reportingDate);
        $this->set('reviewingDate', $reviewingDate);


        //end


        $attributeMarks = $this->EmployeeDetails->query("
    SELECT d.*, a.attributes 
    FROM assessment_attributes_executive_details d
    JOIN assessment_attributes_executive a ON d.attributes_exec_fkey = a.attributes_exec_pkey
    WHERE d.emp_fkey = $emp_fkey  AND fin_year = '$finYear'
    ");
        // debug($attributeMarks);
        $this->set('attributeMarks', $attributeMarks);


        $executive_summary = $this->EmployeeDetails->query("
    SELECT assessment_summary_executive.*, ei.EmpName AS EmpName, ei.designation 
    FROM assessment_summary_executive 
    LEFT JOIN employee_info ei ON assessment_summary_executive.officer_fkey = ei.emp_pkey
    WHERE assessment_summary_executive.emp_fkey = '$emp_fkey' AND assessment_summary_executive.fin_year = '$finYear'
");


        $this->set('executive_summary', $executive_summary);




        // $this->set(compact('company_logo', 'company_name'));
        $this->set('emp_data', $emp_data);
        $reportingOfficerId = null;
        $reviewingOfficerId = null;

        if (!empty($emp_data)) {
            $reportingOfficerId = $emp_data[0]['s']['reporting_officer'];
            $reviewingOfficerId = $emp_data[0]['s']['reviewing_officer'];
        }

        $this->set('reportingOfficerId', $reportingOfficerId);
        $this->set('reviewingOfficerId', $reviewingOfficerId);


        // Render the view content as HTML
        App::uses('View', 'View');
        $View = new View($this, false);
        $View->viewPath = 'TeamReview';
        $html = $View->render('performance_report');

        // Load HTML2PDF library
        App::import('Vendor', 'HTML2PDF', ['file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php']);
        $pdf = new HTML2PDF('P', 'A4', 'en');
        $pdf->pdf->SetFont('times', 'B', 12);
        $pdf->pdf->SetDisplayMode('fullpage');

        $pdf->writeHTML($html);

        // Generate and download PDF
        $filename = 'ExecutiveDetails.pdf';
        $pdf->Output($filename, 'D');
        exit();
    }

    // Edited by Akshay on 9-6-2025
    public function previewDocumentReview($pkey = null, $self_pkey = null)
    {
        $this->autoRender = false;
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        $fontPath = WWW_ROOT . 'fonts' . DS . 'timesnewromanbold.ttf'; // Ensure the TTF file exists at this location

        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->layout = null; // Disable default layout

        // Load model and data
        // $this->loadModel('SelfReview');
        // $review = $this->SelfReview->find('first', [
        //     'conditions' => ['SelfReview.pkey' => $pkey],
        //     'recursive' => 1
        // ]);

        // $this->set('review', $review);

        $emp_pkey = $this->Session->read('emp_fkey');
        $this->set('loggedInOfficerId', $emp_pkey);




        // Check role directly from self_review_details
        $roleCheck = $this->EmployeeDetails->find('first', [
            'joins' => [
                [
                    'table' => 'self_review_details',
                    'alias' => 'SelfReview',
                    'type' => 'INNER',
                    'conditions' => [
                        'OR' => [
                            'SelfReview.self_review_details_pkey' => $self_pkey
                        ]
                    ]
                ]
            ],
            'fields' => ['SelfReview.reporting_officer', 'SelfReview.reviewing_officer']
        ]);

        if (!empty($roleCheck) && isset($roleCheck['SelfReview']['reporting_officer']) && $roleCheck['SelfReview']['reporting_officer'] == $emp_pkey) {
            $role = 'reporting_officer';
        } elseif (!empty($roleCheck)) {
            $role = 'reviewing_officer';
        } else {
            $role = 'employee'; // Or whatever default role makes sense
        }


        $this->set('role', $role);


        $emp_fkey = $pkey;

        $fin_years = $this->EmployeeDetails->query("
            SELECT fin_year 
            FROM self_review_details 
            WHERE self_review_details_pkey = $self_pkey
            
        ");

        $finYear = !empty($fin_years) ? $fin_years[0]['self_review_details']['fin_year'] : null;


        $emp_data = $this->EmployeeDetails->query("
        SELECT e.EmpName, s.*
        FROM self_review_details s
        JOIN employee_info e ON s.emp_fkey = e.emp_pkey where s.emp_fkey='$emp_fkey' AND fin_year='$finYear'
        ");
        //edited by athira on 02-06-2025

        $reportingDate = null;
        $reviewingDate = null;

        if (!empty($emp_data)) {
            $reportingRaw = isset($emp_data[0]['s']['reported_date']) ? $emp_data[0]['s']['reported_date'] : null;
            $reviewingRaw = isset($emp_data[0]['s']['reviewed_date']) ? $emp_data[0]['s']['reviewed_date'] : null;

            if ($reportingRaw) {
                $reportingDate = date('d-m-Y', strtotime($reportingRaw));
            }

            if ($reviewingRaw) {
                $reviewingDate = date('d-m-Y', strtotime($reviewingRaw));
            }
        }

        $this->set('reportingDate', $reportingDate);
        $this->set('reviewingDate', $reviewingDate);


        //end


        $attributeMarks = $this->EmployeeDetails->query("
            SELECT d.*, a.attributes 
            FROM assessment_attributes_executive_details d
            JOIN assessment_attributes_executive a ON d.attributes_exec_fkey = a.attributes_exec_pkey
            WHERE d.emp_fkey = $emp_fkey  AND fin_year = '$finYear'
            ");
        // debug($attributeMarks);
        $this->set('attributeMarks', $attributeMarks);


        $executive_summary = $this->EmployeeDetails->query("
            SELECT assessment_summary_executive.*, ei.EmpName AS EmpName, ei.designation 
            FROM assessment_summary_executive 
            LEFT JOIN employee_info ei ON assessment_summary_executive.officer_fkey = ei.emp_pkey
            WHERE assessment_summary_executive.emp_fkey = '$emp_fkey' AND assessment_summary_executive.fin_year = '$finYear'
        ");


        $this->set('executive_summary', $executive_summary);




        // $this->set(compact('company_logo', 'company_name'));
        $this->set('emp_data', $emp_data);
        $reportingOfficerId = null;
        $reviewingOfficerId = null;

        if (!empty($emp_data)) {
            $reportingOfficerId = $emp_data[0]['s']['reporting_officer'];
            $reviewingOfficerId = $emp_data[0]['s']['reviewing_officer'];
        }

        $this->set('reportingOfficerId', $reportingOfficerId);
        $this->set('reviewingOfficerId', $reviewingOfficerId);

        $this->render('performance_view');
    }
    // End

    // Edited by Akshay on 9-6-2025
    public function previewPdfReviewHR($pkey = null, $self_pkey = null)
    {
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        $fontPath = WWW_ROOT . 'fonts' . DS . 'timesnewromanbold.ttf'; // Ensure the TTF file exists at this location

        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->layout = null; // Disable default layout

        // Load model and data
        // $this->loadModel('SelfReview');
        // $review = $this->SelfReview->find('first', [
        //     'conditions' => ['SelfReview.pkey' => $pkey],
        //     'recursive' => 1
        // ]);

        // $this->set('review', $review);

        $emp_pkey = $this->Session->read('emp_fkey');
        $this->set('loggedInOfficerId', $emp_pkey);


        $role = 'reviewing_officer';

        $this->set('role', $role);


        $emp_fkey = $pkey;

        $fin_years = $this->EmployeeDetails->query("
            SELECT fin_year, status 
            FROM self_review_details 
            WHERE self_review_details_pkey = $self_pkey
            
        ");

        $finYear = !empty($fin_years) ? $fin_years[0]['self_review_details']['fin_year'] : null;

        // Edited by Akshay on 17-7-2025
        $status = !empty($fin_years) ? trim($fin_years[0]['self_review_details']['status']) : null;
        $this->set('status', $status);
        // End

        $emp_data = $this->EmployeeDetails->query("
        SELECT e.EmpName, s.*
        FROM self_review_details s
        JOIN employee_info e ON s.emp_fkey = e.emp_pkey where s.emp_fkey='$emp_fkey' AND fin_year='$finYear'
        ");
        //edited by athira on 02-06-2025

        $reportingDate = null;
        $reviewingDate = null;

        if (!empty($emp_data)) {
            $reportingRaw = isset($emp_data[0]['s']['reported_date']) ? $emp_data[0]['s']['reported_date'] : null;
            $reviewingRaw = isset($emp_data[0]['s']['reviewed_date']) ? $emp_data[0]['s']['reviewed_date'] : null;

            if ($reportingRaw) {
                $reportingDate = date('d-m-Y', strtotime($reportingRaw));
            }

            if ($reviewingRaw) {
                $reviewingDate = date('d-m-Y', strtotime($reviewingRaw));
            }
        }

        $this->set('reportingDate', $reportingDate);
        $this->set('reviewingDate', $reviewingDate);


        //end


        $attributeMarks = $this->EmployeeDetails->query("
        SELECT d.*, a.attributes 
        FROM assessment_attributes_executive_details d
        JOIN assessment_attributes_executive a ON d.attributes_exec_fkey = a.attributes_exec_pkey
        WHERE d.emp_fkey = $emp_fkey  AND fin_year = '$finYear'
        ");
        // debug($attributeMarks);
        $this->set('attributeMarks', $attributeMarks);


        $executive_summary = $this->EmployeeDetails->query("
            SELECT assessment_summary_executive.*, ei.EmpName AS EmpName, ei.designation 
            FROM assessment_summary_executive 
            LEFT JOIN employee_info ei ON assessment_summary_executive.officer_fkey = ei.emp_pkey
            WHERE assessment_summary_executive.emp_fkey = '$emp_fkey' AND assessment_summary_executive.fin_year = '$finYear'
        ");


        $this->set('executive_summary', $executive_summary);




        // $this->set(compact('company_logo', 'company_name'));
        $this->set('emp_data', $emp_data);
        $reportingOfficerId = null;
        $reviewingOfficerId = null;

        if (!empty($emp_data)) {
            $reportingOfficerId = $emp_data[0]['s']['reporting_officer'];
            $reviewingOfficerId = $emp_data[0]['s']['reviewing_officer'];
        }

        $this->set('reportingOfficerId', $reportingOfficerId);
        $this->set('reviewingOfficerId', $reviewingOfficerId);


        // Render the view content as HTML
        App::uses('View', 'View');
        $View = new View($this, false);
        $View->viewPath = 'TeamReview';
        $html = $View->render('performance_report');

        // Load HTML2PDF library
        App::import('Vendor', 'HTML2PDF', ['file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php']);
        $pdf = new HTML2PDF('P', 'A4', 'en');
        //edited by akshay
        $pdf->setTestTdInOnePage(false); // allow breaking inside <td>
        $pdf->pdf->SetAutoPageBreak(true, 0); // avoid pushing last block
        $pdf->pdf->SetFont('times', 'B', 12);
        $pdf->pdf->SetDisplayMode('fullpage');

        $pdf->writeHTML($html);

        // Generate and download PDF
        $filename = 'ExecutiveDetails.pdf';
        $pdf->Output($filename, 'D');
        exit();
    }
    // End
}
