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
ini_set("display_errors", 0);

App::uses('ConnectionManager', 'Model');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class HierarchyReviewController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'HierarchyReview';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('AppModel', 'EmployeeDetails', 'AssessmentAttributesStaffDetails', 'SelfReviewDetails', 'AssessmentAttributesStaffItem');
    public $components = array('Session');

    /*
     * Leave List Landing Page
     */

    public function index()
    {
        $this->AssessmentAttributesStaffDetails->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");

        //My Leaves count
        $leave_count = $this->AssessmentAttributesStaffDetails->find('count', array('conditions' => array('EMP_fkey' => $cur_emp_key)));
        $this->set('leave_count', $leave_count);
    }

    public function listreviews()
    {
        // $this->layout = 'ajax';
        $this->autoRender = false;

        $emp_fkey = $this->Session->read('emp_fkey');
        // Optional: if you're using a different DB config set in Session
        $dsName = $this->Session->read('ds') ?: 'default';
        $db = ConnectionManager::getDataSource($dsName);

        // Read page and rows from query string (for EasyUI pagination)
        $rows = isset($_POST['rows']) ? (int)$_POST['rows'] : 10;
        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $offset = ($page - 1) * $rows;

        // Count total records
        //edited by athira on 06-05-2025
        // $countQuery = "SELECT COUNT(*) AS total FROM self_review_details WHERE $emp_fkey IN(reporting_officer,reviewing_officer)";
        $countQuery = "
                        SELECT COUNT(*) AS total
                        FROM assessment_attributes_staff_details srd
                        WHERE srd.status != 0
                        AND (
                            srd.reporting_officer = $emp_fkey
                            OR (
                                srd.reviewing_officer = $emp_fkey
                                AND srd.status >= 2
                                AND srd.status != 5
                            )
                        )";
        //end

        $total = $db->query($countQuery);
        $totalCount = isset($total[0][0]['total']) ? $total[0][0]['total'] : 0;

        // Main data query
        $dataQuery = "
                            SELECT
                                srd.*,
                                e_emp.employee_id AS employee_id,
                                CONCAT(e_emp.EmpName, ' - ', e_emp.employee_id) AS emp_fkey,
                                CONCAT(e_rep.EmpName, ' - ', e_rep.employee_id) AS reporting_officer,
                                CONCAT(e_rev.EmpName, ' - ', e_rev.employee_id) AS reviewing_officer,
                                CONCAT(e_created.EmpName, ' - ', e_created.employee_id) AS created_by,
                                CONCAT(e_modified.EmpName, ' - ', e_modified.employee_id) AS modified_by
                            FROM assessment_attributes_staff_details srd
                            LEFT JOIN employee_info e_emp ON srd.emp_fkey = e_emp.emp_pkey
                            LEFT JOIN employee_info e_rep ON srd.reporting_officer = e_rep.emp_pkey
                            LEFT JOIN employee_info e_rev ON srd.reviewing_officer = e_rev.emp_pkey
                            LEFT JOIN employee_info e_created ON srd.created_by = e_created.emp_pkey
                            LEFT JOIN employee_info e_modified ON srd.modified_by = e_modified.emp_pkey
                            WHERE srd.status != 0
                            AND ( srd.reporting_officer = $emp_fkey OR (srd.reviewing_officer = $emp_fkey AND srd.status >= 2 AND srd.status != 5))
                            ORDER BY srd.attr_staff_details_pkey DESC
                            LIMIT $rows OFFSET $offset;


        ";

        $results = $db->query($dataQuery);


        $rowsFormatted = array();
        foreach ($results as $row) {
            $created_date = isset($row['srd']['creation_date']) ? date('d-m-Y H:i:s', strtotime($row['srd']['creation_date'])) : '';
            $modified_date = isset($row['srd']['modification_date']) ? date('d-m-Y H:i:s', strtotime($row['srd']['modification_date'])) : '';

            $empId = $row['srd']['emp_fkey'];
            $reportingId = $row['srd']['reporting_officer'];
            $reviewingId = $row['srd']['reviewing_officer'];

            $drafted_by = $row['srd']['drafted_by'];
            $reported_by = $row['srd']['reported_by'];
            $reviewed_by = $row['srd']['reviewed_by'];
            $rejected_by = $row['srd']['rejected_by'];


            $statusLabel = 'Unknown';

            // Highest-priority status check first
            if ((int)$row['srd']['is_reviewed'] === 1 && $reviewed_by == $reviewingId) {
                $statusLabel = 'Reviewing Person submitted the Appraisal';
            } elseif ((int)$row['srd']['is_reported'] === 1 && $reported_by == $reportingId) {
                $statusLabel = 'Reporting person submitted the Appraisal';
            } elseif ((int)$row['srd']['is_rejected'] === 1 && $rejected_by == $reportingId) {
                $statusLabel = 'Reporting person rejected the Appraisal';
            } elseif ((int)$row['srd']['is_rejected'] === 1 && $rejected_by == $reviewingId) {
                $statusLabel = 'Reviewed person rejected the Appraisal';
            } elseif ((int)$row['srd']['is_drafted'] === 1 && $drafted_by == $empId) {
                $statusLabel = 'Employee drafted the appraisal';
            } elseif ((int)$row['srd']['is_drafted'] === 1 && $drafted_by == $reportingId) {
                $statusLabel = 'Reporting person drafted the appraisal';
            } elseif ((int)$row['srd']['is_drafted'] === 1 && $drafted_by == $reviewingId) {
                $statusLabel = 'Reviewing person drafted the appraisal';
            } elseif (empty($drafted_by) && empty($reported_by) && empty($reviewed_by)) {
                $statusLabel = 'Staff Appraisal Initiated';
            }


            $rowsFormatted[] = array(
                'attr_staff_details_pkey' => $row['srd']['attr_staff_details_pkey'],
                'emp_fkey' => $row[0]['emp_fkey'],
                'emp_pkey' => $empId,
                'reporting_officer' => $row[0]['reporting_officer'],
                'reviewing_officer' => $row[0]['reviewing_officer'],
                'created_by' => $row[0]['created_by'],
                'created_date' => $created_date,
                'modified_by' => $row[0]['modified_by'],
                'modified_date' => $modified_date,
                'status' => (int)$row['srd']['status'],
                'status_label' => $statusLabel
            );
        }
        echo json_encode(array(
            'total' => $totalCount,
            'rows' => $rowsFormatted
        ));
    }

    public function form()
    {
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $is_edit = true;

        $is_reviewing_officer = false;

        $this->set("is_reviewing_officer", $is_reviewing_officer);

        $this->set("is_edit", $is_edit);
        $this->set("emp_fkey", $emp_fkey);

        $duty_desc = '';
        $this->set("duty_desc", $duty_desc);

        $work_done_desc = '';
        $this->set("work_done_desc", $work_done_desc);

        $arr_leave = $this->EmployeeDetails->query("SELECT ei.EmpName, ei.employee_id, ei.emp_pkey FROM employee_info ei 
        WHERE ei.emp_pkey IN (SELECT DISTINCT policy_id FROM emp_config WHERE type = 'LAPPR' 
                                    AND status = 1 AND emp_fkey = $emp_fkey)
        ");
        $this->set("arr_leave", $arr_leave);


        $arr_employees = $this->EmployeeDetails->query("SELECT ei.EmpName, ei.employee_id, ei.emp_pkey FROM employee_info ei 
        WHERE ei.emp_pkey IN (SELECT DISTINCT emp_fkey FROM emp_config WHERE type = 'HIERARCHY' 
                                    AND status = 1 AND policy_id = $emp_fkey)
        ");
        $this->set("arr_employees", $arr_employees);

        $arr_attr = $this->EmployeeDetails->query("SELECT attributes_staff_pkey, attributes FROM  assessment_attributes_staff aas WHERE status = 1;");
        $this->set("arr_attr", $arr_attr);
    }

    public function getDesignation()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $emp_pkey = $arr_form_data['emp_fkey'];

        $arr_designation =  $this->EmployeeDetails->query("SELECT designation AS desig FROM employee_info ei WHERE emp_pkey = '$emp_pkey';");
        $designation = $arr_designation[0]['ei']['desig'];

        if ($designation != '') {
            $response['success'] = true;
            $response['designation'] = $designation;
        } else {
            $response['success'] = false;
        }
        echo json_encode($response);
    }

    public function view($edit = 0, $attr_staff_details_pkey, $emp_fkey_string)
    {
        $this->autoRender = false;
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->AssessmentAttributesStaffDetails->useDbConfig = $this->Session->read('ds');
        $this->AssessmentAttributesStaffItem->useDbConfig = $this->Session->read('ds');
        //edited by athira on 06-05-2025
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // $parts = explode('-', $emp_fkey_string);
        // $employee_id = isset($parts[1]) ? trim($parts[1]) : null;

        // $emp_pkey = $this->EmployeeDetails->query("SELECT emp_pkey from employee_info where employee_id='$employee_id'");debug($emp_pkey);
        $emp_pkey = $emp_fkey_string;
        $this->set("attr_staff_details_pkey", $attr_staff_details_pkey);
        //end

        $this->set("emp_fkey", $emp_fkey);

        $arr_employees = $this->AssessmentAttributesStaffItem->query("SELECT ei.EmpName, ei.employee_id, ei.emp_pkey FROM employee_info ei 
                                                                        -- WHERE ei.emp_status = 1
                                                                        ORDER BY ei.EmpName;
                                                        ");
        $this->set("arr_employees", $arr_employees);

        $arr_attr = $this->AssessmentAttributesStaffItem->query("SELECT attributes_staff_pkey, attributes FROM  assessment_attributes_staff aas WHERE status = 1;");
        $this->set("arr_attr", $arr_attr);
        //edited by athira on 06-05-2025
        $arr_designation = $this->AssessmentAttributesStaffItem->query("SELECT designation FROM employee_info ei WHERE emp_pkey = $emp_pkey;");
        $desig = isset($arr_designation[0]['ei']['designation']) ? $arr_designation[0]['ei']['designation'] : '';
        //end

        $details = $this->AssessmentAttributesStaffDetails->find('first', [
            'conditions' => [
                'AssessmentAttributesStaffDetails.attr_staff_details_pkey' => $attr_staff_details_pkey
            ],
            'joins' => [
                [
                    'table' => 'employee_info',
                    'alias' => 'ReportingOfficer',
                    'type' => 'LEFT',
                    'conditions' => [
                        'ReportingOfficer.emp_pkey = AssessmentAttributesStaffDetails.reporting_officer'
                    ]
                ],
                [
                    'table' => 'employee_info',
                    'alias' => 'ReviewingOfficer',
                    'type' => 'LEFT',
                    'conditions' => [
                        'ReviewingOfficer.emp_pkey = AssessmentAttributesStaffDetails.reviewing_officer'
                    ]
                ]
            ],
            'fields' => [
                'AssessmentAttributesStaffDetails.*',
                'ReportingOfficer.EmpName AS reporting_officer_name',
                'ReportingOfficer.designation AS reporting_officer_designation',
                'ReviewingOfficer.EmpName AS reviewing_officer_name',
                'ReviewingOfficer.designation AS reviewing_officer_designation',
            ],
            'recursive' => -1
        ]);

        $reviewing_officer = $details['AssessmentAttributesStaffDetails']['reviewing_officer'];

        $status = $details['AssessmentAttributesStaffDetails']['status'];
        $this->set("status", $status);

        if ($emp_fkey == $reviewing_officer) {
            $is_reviewing_officer = true;
        } else {
            $is_reviewing_officer = false;
        }
        $this->set("is_reviewing_officer", $is_reviewing_officer);

        $items = $this->AssessmentAttributesStaffItem->find('list', [
            'fields' => ['attributes_staff_fkey', 'marks'],
            'conditions' => ['attr_staff_details_fkey' => $attr_staff_details_pkey],
            'recursive' => -1
        ]);

        $default_data = [];

        if (!empty($details)) {
            $default_data = [
                'EmpPkey' => isset($details['AssessmentAttributesStaffDetails']['emp_fkey']) ? $details['AssessmentAttributesStaffDetails']['emp_fkey'] : '',
                'Designation' => $desig, // You might need another query for this
                'reporting_officer_name' => isset($details['ReportingOfficer']['reporting_officer_name']) ? $details['ReportingOfficer']['reporting_officer_name'] : '',
                'reporting_officer_designation' => isset($details['ReportingOfficer']['reporting_officer_designation']) ? $details['ReportingOfficer']['reporting_officer_designation'] : '',
                'reporting_officer_date' => !empty($details['AssessmentAttributesStaffDetails']['reported_date'])
                    ? date('d-m-Y', strtotime($details['AssessmentAttributesStaffDetails']['reported_date']))
                    : '',

                'reporting_officer_marks' => isset($details['AssessmentAttributesStaffDetails']['reporting_officer_marks']) ? $details['AssessmentAttributesStaffDetails']['reporting_officer_marks'] : '',
                'reporting_officer_grade' => isset($details['AssessmentAttributesStaffDetails']['reporting_officer_grade']) ? $details['AssessmentAttributesStaffDetails']['reporting_officer_grade'] : '',
                'reporting_officer_training_needs' => isset($details['AssessmentAttributesStaffDetails']['reporting_officer_training_needs']) ? $details['AssessmentAttributesStaffDetails']['reporting_officer_training_needs'] : '',
                'reporting_officer_comments' => isset($details['AssessmentAttributesStaffDetails']['reporting_officer_comments']) ? $details['AssessmentAttributesStaffDetails']['reporting_officer_comments'] : '',

                'reviewing_officer_name' => isset($details['ReviewingOfficer']['reviewing_officer_name']) ? $details['ReviewingOfficer']['reviewing_officer_name'] : '',
                'reviewing_officer_designation' => isset($details['ReviewingOfficer']['reviewing_officer_designation']) ? $details['ReviewingOfficer']['reviewing_officer_designation'] : '',
                'reviewing_officer_date' => !empty($details['AssessmentAttributesStaffDetails']['reviewed_date'])
                    ? date('d-m-Y', strtotime($details['AssessmentAttributesStaffDetails']['reviewed_date']))
                    : '',
                'reviewing_officer_training_needs' => isset($details['AssessmentAttributesStaffDetails']['reviewing_officer_training_needs']) ? $details['AssessmentAttributesStaffDetails']['reviewing_officer_training_needs'] : '',
                'reviewing_officer_comments' => isset($details['AssessmentAttributesStaffDetails']['reviewing_officer_comments']) ? $details['AssessmentAttributesStaffDetails']['reviewing_officer_comments'] : '',
            ];


            foreach ($items as $attr_fkey => $mark) {
                $default_data["reporting_officer_marks_$attr_fkey"] = ($mark !== null) ? $mark : 0;
            }
        }
        // debug($default_data);
        $this->set('default_data', $default_data);


        if ($edit == 0) {
            $this->set('is_edit', false);
        } else {
            $this->set('is_edit', true);
        }

        $this->render('form');
    }

    public function createHierarchyReview()
    {
        $this->autoRender = false;
        $this->AssessmentAttributesStaffDetails->useDbConfig = $this->Session->read('ds');
        $this->AssessmentAttributesStaffItem->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        // debug($arr_form_data);exit;

        // if (empty($arr_form_data['reporting_officer_marks'])) {
        //     $arr_form_data['reporting_officer_marks'] = 0;
        //     $arr_form_data['reporting_officer_grade'] = 'Below Average';
        // }

        $arr_form_data['status'] = 5;

        $current_emp_fkey = $this->Session->read('emp_fkey');

        $emp_pkey = $arr_form_data['emp_fkey'];

        $fin_year = $arr_form_data['fin_year'];

        $arr_count = $this->AssessmentAttributesStaffDetails->query("SELECT count(*) AS count FROM assessment_attributes_staff_details asd WHERE emp_fkey = '$emp_pkey' AND fin_year = '$fin_year' AND status != 0;");
        $count = isset($arr_count[0][0]['count']) ? $arr_count[0][0]['count'] : 0;

        if ($count != 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Record already created for this employee.'
            ]);

            exit();
        }

        $arr_leave = $this->AssessmentAttributesStaffDetails->query("
                                                                        SELECT ei.EmpName, ei.employee_id, ei.emp_pkey
                                                                        FROM employee_info ei
                                                                        WHERE ei.emp_pkey IN (
                                                                            SELECT DISTINCT policy_id
                                                                            FROM emp_config
                                                                            WHERE type = 'LAPPR' AND status = 1 AND emp_fkey = $emp_pkey
                                                                        )
                                                                        LIMIT 1
                                                                    ");

        $arr_hierarchy = $this->AssessmentAttributesStaffDetails->query("
                                                                        SELECT ei.EmpName, ei.employee_id, ei.emp_pkey
                                                                        FROM employee_info ei
                                                                        WHERE ei.emp_pkey IN (
                                                                            SELECT DISTINCT policy_id
                                                                            FROM emp_config
                                                                            WHERE type = 'HIERARCHY' AND status = 1 AND emp_fkey = $emp_pkey
                                                                        )
                                                                        LIMIT 1
                                                                    ");

        if (true) {
            // $arr_form_data['reviewing_officer'] = $arr_leave[0]['ei']['emp_pkey'];
            // $arr_form_data['reporting_officer'] = $arr_hierarchy[0]['ei']['emp_pkey'];

            $arr_form_data['created_by'] = $current_emp_fkey;

            if ($this->AssessmentAttributesStaffDetails->save($arr_form_data)) {

                $message = 'Staff Review initiated successfully.';
                echo json_encode([
                    'status' => 'success',
                    'message' => $message
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to save. Please check your input.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Please set reviewing officer and reporting officer for the selected employee.'
            ]);
        }

        exit();
    }

    public function saveHierarchyReview()
    {
        $this->autoRender = false;
        $this->AssessmentAttributesStaffDetails->useDbConfig = $this->Session->read('ds');
        $this->AssessmentAttributesStaffItem->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;

        if (empty($arr_form_data['reporting_officer_marks'])) {
            $arr_form_data['reporting_officer_marks'] = 0;
            $arr_form_data['reporting_officer_grade'] = 'Below Average';
        }

        // Ensure default 0 for each is_ field if not set
        $isFields = ['is_reported', 'is_reviewed', 'is_rejected', 'is_drafted'];

        foreach ($isFields as $field) {
            if (!isset($arr_form_data[$field])) {
                $arr_form_data[$field] = 0;
            }
        }

        $current_emp_fkey = $this->Session->read('emp_fkey');

        $attr_staff_details_pkey = $arr_form_data['attr_staff_details_pkey'];
        $arr_by = $this->AssessmentAttributesStaffDetails->query("SELECT reporting_officer, reviewing_officer FROM assessment_attributes_staff_details aasd WHERE attr_staff_details_pkey = $attr_staff_details_pkey;");
        $reporting_officer = isset($arr_by[0]['aasd']['reporting_officer']) ? $arr_by[0]['aasd']['reporting_officer'] : 0;
        $reviewing_officer = isset($arr_by[0]['aasd']['reviewing_officer']) ? $arr_by[0]['aasd']['reviewing_officer'] : 0;


        if ($arr_form_data['status'] == 1) {
            $arr_form_data['is_drafted'] = 1;
            $arr_form_data['drafted_by'] = $current_emp_fkey;
            $arr_form_data['drafted_date'] = date('Y-m-d H:i:s');
        } elseif ($arr_form_data['status'] == 2) {

            if ($reporting_officer == $current_emp_fkey) {
                $arr_form_data['is_reported'] = 1;
                $arr_form_data['reported_by'] = $current_emp_fkey;
                $arr_form_data['reported_date'] = date('Y-m-d H:i:s');
            } elseif ($reviewing_officer == $current_emp_fkey) {
                $arr_form_data['is_drafted'] = 1;
                $arr_form_data['drafted_by'] = $current_emp_fkey;
                $arr_form_data['drafted_date'] = date('Y-m-d H:i:s');
            }
        } elseif ($arr_form_data['status'] == 3) {
            if ($reviewing_officer == $current_emp_fkey) {
                $arr_form_data['is_reviewed'] = 1;
                $arr_form_data['reviewed_by'] = $current_emp_fkey;
                $arr_form_data['reviewed_date'] = date('Y-m-d H:i:s');
            }
        }


        $emp_pkey = $arr_form_data['EmpPkey'];
        $arr_form_data['emp_fkey'] = $emp_pkey;


        if (true) {
            $arr_form_data['reviewing_officer'] = $reviewing_officer;

            if (isset($arr_form_data['self_review_details_pkey'])) {
                $arr_form_data['modified_by'] = $current_emp_fkey;
                $arr_form_data['modified_date'] =  date('Y-m-d H:i:s');
            }

            $arr_form_data['reporting_officer'] = $reporting_officer;
            // debug($arr_form_data);exit;

            if ($this->AssessmentAttributesStaffDetails->save($arr_form_data)) {

                $attrStaffDetailsFkey = $this->AssessmentAttributesStaffDetails->id;

                foreach ($arr_form_data as $key => $value) {
                    if (strpos($key, 'reporting_officer_marks_') === 0) {
                        $attributeStaffFkey = str_replace('reporting_officer_marks_', '', $key);
                        $marks = (is_numeric($value) && $value !== '') ? $value + 0 : 0;

                        if (!is_null($marks)) {
                            // Check if the item already exists
                            $existing = $this->AssessmentAttributesStaffItem->find('first', [
                                'conditions' => [
                                    'AssessmentAttributesStaffItem.attr_staff_details_fkey' => $attrStaffDetailsFkey,
                                    'AssessmentAttributesStaffItem.attributes_staff_fkey' => (int)$attributeStaffFkey,
                                    'AssessmentAttributesStaffItem.status' => 1
                                ],
                                'fields' => ['AssessmentAttributesStaffItem.aast_pkey'], // Fetch only the PK
                                'recursive' => -1
                            ]);

                            // Base data
                            $itemData = [
                                'AssessmentAttributesStaffItem' => [
                                    'attr_staff_details_fkey'   => $attrStaffDetailsFkey,
                                    'attributes_staff_fkey'     => (int)$attributeStaffFkey,
                                    'marks'                     => $marks,
                                    'status'                    => 1 // default active
                                ]
                            ];

                            if (!empty($existing)) {
                                // Update path
                                $itemData['AssessmentAttributesStaffItem']['aast_pkey'] = $existing['AssessmentAttributesStaffItem']['aast_pkey'];
                                $itemData['AssessmentAttributesStaffItem']['modified_by'] = $current_emp_fkey;
                                $itemData['AssessmentAttributesStaffItem']['modification_date'] = date('Y-m-d H:i:s');
                            } else {
                                // Insert path
                                $this->AssessmentAttributesStaffItem->create(); // reset model state
                                $itemData['AssessmentAttributesStaffItem']['created_by'] = $current_emp_fkey;
                                $itemData['AssessmentAttributesStaffItem']['creation_date'] = date('Y-m-d H:i:s');
                            }

                            // Save (insert or update)
                            $this->AssessmentAttributesStaffItem->save($itemData);
                        }
                    }
                }


                echo json_encode([
                    'success' => true,
                    'message' => 'Self Review saved successfully.'
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Failed to save. Please check your input.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Please set reviewing officer for the selected employee.'
            ]);
        }

        exit();
    }

    public function deleteHierarchyReview()
    {
        $this->autoRender = false;
        $this->AssessmentAttributesStaffDetails->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
        // $this->response->type('json');

        if ($this->request->is('post')) {
            $pkey = $this->request->data['attr_staff_details_pkey'];

            $updated = $this->AssessmentAttributesStaffDetails->updateAll(
                [
                    'AssessmentAttributesStaffDetails.status' => "0", // Set status to 'Deleted'
                    'AssessmentAttributesStaffDetails.modified_by' => $emp_fkey, // Set modified_by
                    'AssessmentAttributesStaffDetails.modification_date' => "'" . date('Y-m-d H:i:s') . "'" // Set modified_date to today
                ],
                ['AssessmentAttributesStaffDetails.attr_staff_details_pkey' => $pkey]
            );
            if ($updated) {
                echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
        }
    }

    public function sendBackReview()
    {
        $this->autoRender = false;
        $this->AssessmentAttributesStaffDetails->useDbConfig = $this->Session->read('ds');
        $this->AssessmentAttributesStaffItem->useDbConfig = $this->Session->read('ds');

        if ($this->request->is('post')) {
            $arr_form_data = $this->request->data;

            if (isset($arr_form_data['attr_staff_details_pkey'])) {
                $key = $arr_form_data['attr_staff_details_pkey'];

                // Update status to 1 (or whatever status means "Sent Back")


                $current_emp_fkey = $this->Session->read('emp_fkey');

                if ($arr_form_data['status'] == 1) {
                    $arr_form_data['is_drafted'] = 1;
                    $arr_form_data['drafted_by'] = $current_emp_fkey;
                    $arr_form_data['drafted_date'] = date('Y-m-d H:i:s');
                } elseif ($arr_form_data['status'] == 2) {
                    $arr_form_data['is_reported'] = 1;
                    $arr_form_data['reported_by'] = $current_emp_fkey;
                    $arr_form_data['reported_date'] = date('Y-m-d H:i:s');
                }

                $saveData = [
                    'attr_staff_details_pkey' => $key,
                    'status' => 1,
                    'is_rejected' => 1,
                    'rejected_by' => $current_emp_fkey,
                    'rejected_date' =>  date('Y-m-d H:i:s')
                ];

                // Define all is_ fields that should default to 0
                $isFields = ['is_reported', 'is_reviewed', 'is_drafted'];

                // Set to 0 if not already present
                foreach ($isFields as $field) {
                    if (!isset($saveData[$field])) {
                        $saveData[$field] = 0;
                    }
                }

                if ($this->AssessmentAttributesStaffDetails->save($saveData)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Review sent back successfully.'
                    ]);
                    return;
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to update status.'
                    ]);
                    return;
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing required key.'
                ]);
                return;
            }
        }

        echo json_encode([
            'success' => false,
            'message' => 'Invalid request.'
        ]);
    }

   public function previewPdfReview($pkey = null)
    {
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        $fontPath = WWW_ROOT . 'fonts' . DS . 'timesnewromanbold.ttf'; // Ensure the TTF file exists at this location

        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->layout = null; // Disable default layout

        $arr_emp_fkey = $this->EmployeeDetails->query("SELECT emp_fkey,reporting_officer, reviewing_officer, is_drafted, drafted_by, is_rejected FROM assessment_attributes_staff_details WHERE attr_staff_details_pkey = '$pkey'");

        $emp_fkey = isset($arr_emp_fkey[0]['assessment_attributes_staff_details']['emp_fkey']) ? $arr_emp_fkey[0]['assessment_attributes_staff_details']['emp_fkey'] : 0;

        // Edited by Akshay on 17-7-2025
        $is_drafted = isset($arr_emp_fkey[0]['assessment_attributes_staff_details']['is_drafted']) ? $arr_emp_fkey[0]['assessment_attributes_staff_details']['is_drafted'] : 0;
        $reportingOfficer = isset($arr_emp_fkey[0]['assessment_attributes_staff_details']['reporting_officer']) ? $arr_emp_fkey[0]['assessment_attributes_staff_details']['reporting_officer'] : '';
        $reviewingOfficer = isset($arr_emp_fkey[0]['assessment_attributes_staff_details']['reviewing_officer']) ? $arr_emp_fkey[0]['assessment_attributes_staff_details']['reviewing_officer'] : '';
        $draftedBy = isset($arr_emp_fkey[0]['assessment_attributes_staff_details']['drafted_by']) ? $arr_emp_fkey[0]['assessment_attributes_staff_details']['drafted_by'] : '';
        $is_rejected = isset($arr_emp_fkey[0]['assessment_attributes_staff_details']['is_rejected']) ? $arr_emp_fkey[0]['assessment_attributes_staff_details']['is_rejected'] : 0;

        $hideReporting = ($draftedBy === $reportingOfficer);
        $hideReviewing = ($draftedBy === $reviewingOfficer);
        $hideEntireTable = $hideReporting; // hide table if drafted by reporting officer

        $this->set(compact('is_drafted', 'is_rejected', 'hideReporting', 'hideReviewing', 'hideEntireTable'));
        // End

        // $fin_years = $this->EmployeeDetails->query("SELECT fin_year FROM fin_year WHERE vattr1 = 1 AND branch_code IN (SELECT branch_code FROM employee_info WHERE emp_pkey = $emp_fkey) AND is_current_finyear = 'Y'");
        // $finYear = $fin_years[0]['fin_year']['fin_year'];
        // $finYear = '2025';
        // 1. Get the one assessment‐details record
        $assessmentDetail = $this->EmployeeDetails->query("
            SELECT *
            FROM assessment_attributes_staff_details
            WHERE attr_staff_details_pkey = $pkey
            LIMIT 1
        ");
        $finYear = $assessmentDetail[0]['assessment_attributes_staff_details']['fin_year'];
        $this->set('finYear', $finYear);


        // if no record, you might want to handle that case:
        if (empty($assessmentDetail)) {
            // e.g. set a flash or just pass an empty array
            $this->set('attributeMarks', []);
            return;
        }

        $detailPkey = $assessmentDetail[0]['assessment_attributes_staff_details']['attr_staff_details_pkey'];

        $gradeEntryDate = isset($assessmentDetail[0]['assessment_attributes_staff_details']['grade_entry_date']) ? $assessmentDetail[0]['assessment_attributes_staff_details']['grade_entry_date'] : '';

        // 2. Get *all* the attribute‐item rows for *that* assessment
        $attributeMarks = $this->EmployeeDetails->query("
            SELECT 
                i.*,
                s.attributes,
                s.marks AS max_marks,
                d.*,
                ei1.EmpName AS reporting_officer_name,
                ei1.designation AS reporting_officer_designation,
                ei2.EmpName AS reviewing_officer_name,
                ei2.designation AS reviewing_officer_designation
            FROM assessment_attributes_staff_items AS i
            LEFT JOIN assessment_attributes_staff AS s
                ON i.attributes_staff_fkey = s.attributes_staff_pkey
            LEFT JOIN assessment_attributes_staff_details AS d
                ON i.attr_staff_details_fkey = d.attr_staff_details_pkey
            LEFT JOIN employee_info AS ei1
                ON d.reporting_officer = ei1.emp_pkey
            LEFT JOIN employee_info AS ei2
                ON d.reviewing_officer = ei2.emp_pkey
            WHERE i.attr_staff_details_fkey = '$pkey'
            ORDER BY s.attributes_staff_pkey
        ");

        $emp_data = $this->EmployeeDetails->query("
            SELECT DISTINCT
                ei.EmpName,
                ei.designation,
                ei.joining_date,
                ei.grade,
                ep.emp_type,
                ei.department,
                ed.date_of_birth  
            FROM employee_info AS ei
            LEFT JOIN emp_proff AS ep ON ei.emp_pkey = ep.emp_fkey
            JOIN fin_year fy ON fy.branch_code = ei.branch_code
            LEFT JOIN emp_details AS ed ON ei.emp_pkey = ed.emp_pkey
            WHERE ei.emp_pkey = '$emp_fkey' 
            
        ");

        $this->set('emp_data', $emp_data);

        $arr_lop_total = $this->EmployeeDetails->query("SELECT 
                                                                SUM(ar.lop_total) AS total_lop
                                                            FROM 
                                                                attendance_register ar
                                                            JOIN 
                                                                employee_info ei ON ei.emp_pkey = ar.emp_fkey
                                                            JOIN 
                                                                fin_year fy ON fy.branch_code = ei.branch_code
                                                            WHERE 
                                                                ar.emp_fkey = $emp_fkey
                                                                AND fy.fin_year = $finYear
                                                                AND fy.vattr1 = 1
                                                                AND fy.status = 1
                                                                AND DATE_FORMAT(STR_TO_DATE(ar.month_year, '%Y-%m'), '%Y-%m') 
                                                                    BETWEEN DATE_FORMAT(fy.start_month, '%Y-%m') AND DATE_FORMAT(fy.end_month, '%Y-%m');
                                                        ");


        $total_lop = isset($arr_lop_total[0][0]['total_lop']) ? $arr_lop_total[0][0]['total_lop'] : 0;
        $this->set('total_lop', $total_lop);

        $this->set(compact('assessmentDetail', 'attributeMarks'));




        // Render the view content as HTML
        App::uses('View', 'View');
        $View = new View($this, false);
        $View->viewPath = 'HierarchyReview';
        $html = $View->render('performance_report');

        // Load HTML2PDF library
        App::import('Vendor', 'HTML2PDF', ['file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php']);
        $pdf = new HTML2PDF('P', 'A4', 'en');
        $pdf->pdf->SetFont('times', 'B', 12);
        $pdf->pdf->SetDisplayMode('fullpage');

        $pdf->writeHTML($html);

        // Generate and download PDF
        $filename = 'StaffDetails.pdf';
        $pdf->Output($filename, 'D');
        exit();
    }

    // Edited by Akshay on 6-6-2025
    public function createBulkHierarchyReview()
    {
        $this->autoRender = false;
        $arr_form_data = $this->request->data;

        $emp_fkeys = $arr_form_data['emp_fkey'];
        $fin_year = $arr_form_data['fin_year'];
        $reporting_officer = $arr_form_data['reporting_officer'];
        $reviewing_officer = $arr_form_data['reviewing_officer'];

        $created_by_emp_pkey = $this->Session->read('emp_fkey');

        if (empty($emp_fkeys) || !is_array($emp_fkeys)) {
            $this->Session->setFlash('No employees selected.', 'default', ['class' => 'alert alert-danger']);
            return $this->redirect($this->referer());
        }

        $this->SelfReviewDetails->useDbConfig = $this->Session->read('ds');
        $this->AssessmentAttributesStaffDetails->useDbConfig = $this->Session->read('ds');

        $emp_ids_str = implode(',', array_map('intval', $emp_fkeys));

        $employee_data = $this->AssessmentAttributesStaffDetails->query("
                                                                            SELECT 
                                                                                ei.emp_pkey,
                                                                                ei.EmpName,
                                                                                ei.employee_id,
                                                                                ei.branch_code,
                                                                                ei.designation,
                                                                                ei.department,
                                                                                ei.joining_date AS doj,
                                                                                ep.emp_type,
                                                                                ed.date_of_birth
                                                                            FROM employee_info ei
                                                                            LEFT JOIN emp_proff ep ON ep.emp_fkey = ei.emp_pkey
                                                                            LEFT JOIN emp_details ed ON ed.emp_pkey = ei.emp_pkey
                                                                            WHERE ei.emp_pkey IN ($emp_ids_str)
                                                                        ");

        $unsavedEmployees = [];

        foreach ($employee_data as $empRow) {
            $emp_pkey = $empRow['ei']['emp_pkey'];
            $empName = isset($empRow['ei']['EmpName']) ? $empRow['ei']['EmpName'] : '';
            $empId = isset($empRow['ei']['employee_id']) ? $empRow['ei']['employee_id'] : $emp_pkey;
            $branch_code = $empRow['ei']['branch_code'];

            $fin_year_data = $this->SelfReviewDetails->query("
                                                                    SELECT branch_code, fin_year
                                                                    FROM fin_year
                                                                    WHERE branch_code = '$branch_code'
                                                                    AND status = 1
                                                                    AND vattr1 = 1
                                                                ");

            $found = false;
            foreach ($fin_year_data as $fy) {
                if (isset($fy['fin_year']['fin_year']) && $fy['fin_year']['fin_year'] == $fin_year) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                // $fin_year exists in the result
            } else {
                $unsavedEmployees[] = $empName . ' - ' . $empId;
                continue;
            }

            // Check if record exists in SelfReviewDetails
            $selfReviewExists = $this->SelfReviewDetails->find('count', [
                'conditions' => [
                    'emp_fkey' => $emp_pkey,
                    'fin_year' => $fin_year,
                    'NOT' => ['status' => ['Delete', 'Deleted']]
                ]
            ]);

            // Check if record exists in AssessmentAttributesStaffDetails (without attributes_staff_fkey)
            $assessmentExists = $this->AssessmentAttributesStaffDetails->find('count', [
                'conditions' => [
                    'emp_fkey' => $emp_pkey,
                    'fin_year' => $fin_year,
                    'NOT' => ['status' => 0]
                ]
            ]);

            if ($selfReviewExists > 0 || $assessmentExists > 0) {
                $unsavedEmployees[] = $empName . ' - ' . $empId;
                continue;
            }

            // Get grade entry date
            $arr_grade = $this->SelfReviewDetails->query("
                                                            SELECT COALESCE(modification_date, creation_date) AS grade_entry_date 
                                                            FROM emp_config 
                                                            WHERE emp_fkey = {$emp_pkey} 
                                                            AND status = '1' 
                                                            AND type = 'GRADE' 
                                                            LIMIT 1
                                                        ");
            $grade_entry_date = !empty($arr_grade[0][0]['grade_entry_date']) ? date('Y-m-d', strtotime($arr_grade[0][0]['grade_entry_date'])) : null;


            // Get absence period using your helper function (define it as needed)
            $absence_period = $this->getLOP($emp_pkey, $fin_year);

            // Prepare data to save
            $saveData = [
                'AssessmentAttributesStaffDetails' => [
                    'emp_fkey' => $emp_pkey,
                    'reporting_officer' => (int)$reporting_officer,
                    'reviewing_officer' => (int)$reviewing_officer,
                    'fin_year' => (int)$fin_year,
                    'designation' => isset($empRow['ei']['designation']) ? $empRow['ei']['designation'] : null,
                    'dob' => !empty($empRow['ed']['date_of_birth']) ? date('Y-m-d', strtotime($empRow['ed']['date_of_birth'])) : null,
                    'doj' => !empty($empRow['ei']['doj']) ? date('Y-m-d', strtotime($empRow['ei']['doj'])) : null,
                    'grade_entry_date' => $grade_entry_date,
                    'employment_type' => isset($empRow['ep']['emp_type']) ? $empRow['ep']['emp_type'] : null,
                    'department' => isset($empRow['ei']['department']) ? $empRow['ei']['department'] : null,
                    'absence_period' => $absence_period,
                    'status' => 1,
                    'created_by' => $created_by_emp_pkey,
                    'creation_date' => date('Y-m-d H:i:s')
                ]
            ];

            $this->AssessmentAttributesStaffDetails->create();
            if (!$this->AssessmentAttributesStaffDetails->save($saveData)) {
                $unsavedEmployees[] = $empName . ' - ' . $empId;
            }
        }

        header('Content-Type: application/json');

        if (!empty($unsavedEmployees)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Some entries could not be saved or already exist.',
                'unsaved' => $unsavedEmployees
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => count($emp_fkeys) . ' entries created successfully.'
            ]);
        }
        return;
    }

    // Example helper to get LOP (absence period)
    private function getLOP($emp_fkey, $fin_year)
    {
        $this->AssessmentAttributesStaffDetails->useDbConfig = $this->Session->read('ds');
        $result = $this->AssessmentAttributesStaffDetails->query("
        SELECT 
            SUM(ar.lop_total) AS total_lop
        FROM attendance_register ar
        JOIN employee_info ei ON ei.emp_pkey = ar.emp_fkey
        JOIN fin_year fy ON fy.branch_code = ei.branch_code
        WHERE ar.emp_fkey = $emp_fkey
          AND fy.fin_year = $fin_year
          AND fy.vattr1 = 1
          AND fy.status = 1
          AND DATE_FORMAT(STR_TO_DATE(ar.month_year, '%Y-%m'), '%Y-%m') 
              BETWEEN DATE_FORMAT(fy.start_month, '%Y-%m') AND DATE_FORMAT(fy.end_month, '%Y-%m')
    ");

        return isset($result[0][0]['total_lop']) ? $result[0][0]['total_lop'] : 0;
    }

    // End

    // Edited by Akshay on 9-6-2025
    public function previewDocumentReview($pkey = null)
    {
        $this->autoRender = false;
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        $fontPath = WWW_ROOT . 'fonts' . DS . 'timesnewromanbold.ttf'; // Ensure the TTF file exists at this location

        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->layout = null; // Disable default layout

        $arr_emp_fkey = $this->EmployeeDetails->query("SELECT emp_fkey FROM assessment_attributes_staff_details WHERE attr_staff_details_pkey = '$pkey'");

        $emp_fkey = isset($arr_emp_fkey[0]['assessment_attributes_staff_details']['emp_fkey']) ? $arr_emp_fkey[0]['assessment_attributes_staff_details']['emp_fkey'] : 0;

        // $fin_years = $this->EmployeeDetails->query("SELECT fin_year FROM fin_year WHERE vattr1 = 1 AND branch_code IN (SELECT branch_code FROM employee_info WHERE emp_pkey = $emp_fkey) AND is_current_finyear = 'Y'");
        // $finYear = $fin_years[0]['fin_year']['fin_year'];
        // $finYear = '2025';
        // 1. Get the one assessment‐details record
        $assessmentDetail = $this->EmployeeDetails->query("
            SELECT *
            FROM assessment_attributes_staff_details
            WHERE attr_staff_details_pkey = $pkey
            LIMIT 1
        ");
        $finYear = $assessmentDetail[0]['assessment_attributes_staff_details']['fin_year'];
        $this->set('finYear', $finYear);


        // if no record, you might want to handle that case:
        if (empty($assessmentDetail)) {
            // e.g. set a flash or just pass an empty array
            $this->set('attributeMarks', []);
            return;
        }

        $detailPkey = $assessmentDetail[0]['assessment_attributes_staff_details']['attr_staff_details_pkey'];

        $gradeEntryDate = isset($assessmentDetail[0]['assessment_attributes_staff_details']['grade_entry_date']) ? $assessmentDetail[0]['assessment_attributes_staff_details']['grade_entry_date'] : '';

        // 2. Get *all* the attribute‐item rows for *that* assessment
        $attributeMarks = $this->EmployeeDetails->query("
            SELECT 
                i.*,
                s.attributes,
                s.marks AS max_marks,
                d.*,
                ei1.EmpName AS reporting_officer_name,
                ei1.designation AS reporting_officer_designation,
                ei2.EmpName AS reviewing_officer_name,
                ei2.designation AS reviewing_officer_designation
            FROM assessment_attributes_staff_items AS i
            LEFT JOIN assessment_attributes_staff AS s
                ON i.attributes_staff_fkey = s.attributes_staff_pkey
            LEFT JOIN assessment_attributes_staff_details AS d
                ON i.attr_staff_details_fkey = d.attr_staff_details_pkey
            LEFT JOIN employee_info AS ei1
                ON d.reporting_officer = ei1.emp_pkey
            LEFT JOIN employee_info AS ei2
                ON d.reviewing_officer = ei2.emp_pkey
            WHERE i.attr_staff_details_fkey = '$pkey'
            ORDER BY s.attributes_staff_pkey
        ");

        $emp_data = $this->EmployeeDetails->query("
            SELECT DISTINCT
                ei.EmpName,
                ei.designation,
                ei.joining_date,
                ei.grade,
                ep.emp_type,
                ei.department,
                ed.date_of_birth  
            FROM employee_info AS ei
            LEFT JOIN emp_proff AS ep ON ei.emp_pkey = ep.emp_fkey
            JOIN fin_year fy ON fy.branch_code = ei.branch_code
            LEFT JOIN emp_details AS ed ON ei.emp_pkey = ed.emp_pkey
            WHERE ei.emp_pkey = '$emp_fkey' 
            
        ");

        $this->set('emp_data', $emp_data);

        $arr_lop_total = $this->EmployeeDetails->query("SELECT 
                                                                SUM(ar.lop_total) AS total_lop
                                                            FROM 
                                                                attendance_register ar
                                                            JOIN 
                                                                employee_info ei ON ei.emp_pkey = ar.emp_fkey
                                                            JOIN 
                                                                fin_year fy ON fy.branch_code = ei.branch_code
                                                            WHERE 
                                                                ar.emp_fkey = $emp_fkey
                                                                AND fy.fin_year = $finYear
                                                                AND fy.vattr1 = 1
                                                                AND fy.status = 1
                                                                AND DATE_FORMAT(STR_TO_DATE(ar.month_year, '%Y-%m'), '%Y-%m') 
                                                                    BETWEEN DATE_FORMAT(fy.start_month, '%Y-%m') AND DATE_FORMAT(fy.end_month, '%Y-%m');
                                                        ");


        $total_lop = isset($arr_lop_total[0][0]['total_lop']) ? $arr_lop_total[0][0]['total_lop'] : 0;
        $this->set('total_lop', $total_lop);

        $this->set(compact('assessmentDetail', 'attributeMarks'));

        // $this->render('performance_report');
        $this->render('performance_view');
    }
    // End

    // Edited by Akshay on 11-6-2025
    public function selfAppraisalWorkmen()
    {
        $this->autoRender = false;
        $this->AssessmentAttributesStaffDetails->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");

        //My Leaves count
        $leave_count = $this->AssessmentAttributesStaffDetails->find('count', array('conditions' => array('EMP_fkey' => $cur_emp_key)));
        $this->set('leave_count', $leave_count);

        $this->render('self_review_workmen');
    }

    public function listreviewsWorkmen()
    {
        // $this->layout = 'ajax';
        $this->autoRender = false;

        $emp_fkey = $this->Session->read('emp_fkey');
        // Optional: if you're using a different DB config set in Session
        $dsName = $this->Session->read('ds') ?: 'default';
        $db = ConnectionManager::getDataSource($dsName);

        // Read page and rows from query string (for EasyUI pagination)
        $rows = isset($_POST['rows']) ? (int)$_POST['rows'] : 10;
        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $offset = ($page - 1) * $rows;

        // Count total records
        //edited by athira on 06-05-2025
        // $countQuery = "SELECT COUNT(*) AS total FROM self_review_details WHERE $emp_fkey IN(reporting_officer,reviewing_officer)";
        $countQuery = "
                        SELECT COUNT(*) AS total
                        FROM assessment_attributes_staff_details srd
                        WHERE srd.status != 0
                        AND (
                            srd.reporting_officer = $emp_fkey
                            OR (
                                srd.reviewing_officer = $emp_fkey
                                AND srd.status >= 2
                                AND srd.status != 5
                            )
                        )";
        //end

        $total = $db->query($countQuery);
        $totalCount = isset($total[0][0]['total']) ? $total[0][0]['total'] : 0;

        // Main data query
        $dataQuery = "
                            SELECT
                                srd.*,
                                e_emp.employee_id AS employee_id,
                                CONCAT(e_emp.EmpName, ' - ', e_emp.employee_id) AS emp_fkey,
                                CONCAT(e_rep.EmpName, ' - ', e_rep.employee_id) AS reporting_officer,
                                CONCAT(e_rev.EmpName, ' - ', e_rev.employee_id) AS reviewing_officer,
                                CONCAT(e_created.EmpName, ' - ', e_created.employee_id) AS created_by,
                                CONCAT(e_modified.EmpName, ' - ', e_modified.employee_id) AS modified_by
                            FROM assessment_attributes_staff_details srd
                            LEFT JOIN employee_info e_emp ON srd.emp_fkey = e_emp.emp_pkey
                            LEFT JOIN employee_info e_rep ON srd.reporting_officer = e_rep.emp_pkey
                            LEFT JOIN employee_info e_rev ON srd.reviewing_officer = e_rev.emp_pkey
                            LEFT JOIN employee_info e_created ON srd.created_by = e_created.emp_pkey
                            LEFT JOIN employee_info e_modified ON srd.modified_by = e_modified.emp_pkey
                            WHERE srd.status != 0
                            AND srd.emp_fkey = $emp_fkey
                            ORDER BY srd.attr_staff_details_pkey DESC
                            LIMIT $rows OFFSET $offset;


        ";

        $results = $db->query($dataQuery);


        $rowsFormatted = array();
        foreach ($results as $row) {
            $created_date = isset($row['srd']['creation_date']) ? date('d-m-Y H:i:s', strtotime($row['srd']['creation_date'])) : '';
            $modified_date = isset($row['srd']['modification_date']) ? date('d-m-Y H:i:s', strtotime($row['srd']['modification_date'])) : '';

            $empId = $row['srd']['emp_fkey'];
            $reportingId = $row['srd']['reporting_officer'];
            $reviewingId = $row['srd']['reviewing_officer'];

            $drafted_by = $row['srd']['drafted_by'];
            $reported_by = $row['srd']['reported_by'];
            $reviewed_by = $row['srd']['reviewed_by'];
            $rejected_by = $row['srd']['rejected_by'];


            $statusLabel = 'Unknown';

            // Highest-priority status check first
            if ((int)$row['srd']['is_reviewed'] === 1 && $reviewed_by == $reviewingId) {
                $statusLabel = 'Reviewing Person submitted the Appraisal';
            } elseif ((int)$row['srd']['is_reported'] === 1 && $reported_by == $reportingId) {
                $statusLabel = 'Reporting person submitted the Appraisal';
            } elseif ((int)$row['srd']['is_rejected'] === 1 && $rejected_by == $reportingId) {
                $statusLabel = 'Reporting person rejected the Appraisal';
            } elseif ((int)$row['srd']['is_rejected'] === 1 && $rejected_by == $reviewingId) {
                $statusLabel = 'Reviewed person rejected the Appraisal';
            } elseif ((int)$row['srd']['is_drafted'] === 1 && $drafted_by == $empId) {
                $statusLabel = 'Employee drafted the appraisal';
            } elseif ((int)$row['srd']['is_drafted'] === 1 && $drafted_by == $reportingId) {
                $statusLabel = 'Reporting person drafted the appraisal';
            } elseif ((int)$row['srd']['is_drafted'] === 1 && $drafted_by == $reviewingId) {
                $statusLabel = 'Reviewing person drafted the appraisal';
            } elseif (empty($drafted_by) && empty($reported_by) && empty($reviewed_by)) {
                $statusLabel = 'Staff Appraisal Initiated';
            }


            $rowsFormatted[] = array(
                'attr_staff_details_pkey' => $row['srd']['attr_staff_details_pkey'],
                'emp_fkey' => $row[0]['emp_fkey'],
                'emp_pkey' => $empId,
                'reporting_officer' => $row[0]['reporting_officer'],
                'reviewing_officer' => $row[0]['reviewing_officer'],
                'created_by' => $row[0]['created_by'],
                'created_date' => $created_date,
                'modified_by' => $row[0]['modified_by'],
                'modified_date' => $modified_date,
                'status' => (int)$row['srd']['status'],
                'status_label' => $statusLabel
            );
        }
        echo json_encode(array(
            'total' => $totalCount,
            'rows' => $rowsFormatted
        ));
    }
    // End
}
