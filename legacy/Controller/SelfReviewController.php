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
class SelfReviewController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'SelfReview';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('AppModel', 'EmployeeDetails', 'LeaveRequests', 'SelfReviewDetails', 'AssessmentAttributesStaffDetails');
    public $components = array('Session');

    /*
     * Leave List Landing Page
     */

    public function index()
    {
        $this->SelfReviewDetails->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");

        //My Leaves count
        $leave_count = $this->SelfReviewDetails->find('count', array('conditions' => array('EMP_fkey' => $cur_emp_key)));
        $this->set('leave_count', $leave_count);
    }

    public function initaiteSelfReview()
    {
        $this->autoRender = false;
        $this->SelfReviewDetails->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");

        //My Leaves count
        $leave_count = $this->SelfReviewDetails->find('count', array('conditions' => array('EMP_fkey' => $cur_emp_key)));
        $this->set('leave_count', $leave_count);
        $this->render('initaite_self_review');
    }

    public function newReview()
    {
        $this->autoRender = false;
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $edit = 1;
        $this->set("edit", $edit);
        $this->set("emp_fkey", $emp_fkey);

        $duty_desc = '';
        $this->set("duty_desc", $duty_desc);

        $work_done_desc = '';
        $this->set("work_done_desc", $work_done_desc);

        $arr_employees = $this->EmployeeDetails->query("SELECT ei.EmpName, ei.employee_id, ei.emp_pkey FROM employee_info ei 
                                                            WHERE ei.emp_status = 1
                                                            ORDER BY TRIM(ei.EmpName)
                                                            ;
                                                            ");
        $this->set("arr_employees", $arr_employees);

        // $arr_leave = $this->EmployeeDetails->query("SELECT ei.EmpName, ei.employee_id, ei.emp_pkey FROM employee_info ei 
        // WHERE ei.emp_pkey IN (SELECT DISTINCT policy_id FROM emp_config WHERE type = 'LAPPR' 
        //                             AND status = 1 AND emp_fkey = $emp_fkey)
        //                             ORDER BY TRIM(ei.EmpName);
        // ");
        // $this->set("arr_leave", $arr_leave);


        // $arr_hierarchy = $this->EmployeeDetails->query("SELECT ei.EmpName, ei.employee_id, ei.emp_pkey FROM employee_info ei 
        // WHERE ei.emp_pkey IN (SELECT DISTINCT policy_id FROM emp_config WHERE type = 'HIERARCHY' 
        //                             AND status = 1 AND emp_fkey = $emp_fkey)
        //                             ORDER BY TRIM(ei.EmpName);
        // ");
        // $this->set("arr_hierarchy", $arr_hierarchy);

        $this->render('new_review');
    }

    public function listreviews()
    {
        $this->autoRender = false;
        $arr_form_data = $this->request->data;
        $emp_fkey = $this->Session->read('emp_fkey');

        $dsName = $this->Session->read('ds');
        if (!$dsName) {
            $dsName = 'default';
        }
        $db = ConnectionManager::getDataSource($dsName);

        $page = isset($arr_form_data['page']) ? (int)$arr_form_data['page'] : 1;
        $rows = isset($arr_form_data['rows']) ? (int)$arr_form_data['rows'] : 10;
        $offset = ($page - 1) * $rows;

        $isHR = isset($_POST['isHR']) ? $_POST['isHR'] : '';

        if ($isHR == true) {
            $condition = " AND srd.created_by = $emp_fkey ";
        } else {
            $condition = " AND (srd.emp_fkey = $emp_fkey OR srd.reporting_officer = $emp_fkey OR srd.reviewing_officer = $emp_fkey) ";
        }
        $condition = '';
        $rowsFormatted = array();
        $totalCount = 0;

        // ---------- First: self_review_details ----------
        $countQuery1 = "
        SELECT COUNT(*) AS total 
        FROM self_review_details srd
        WHERE srd.status != 'Deleted' $condition
        ";
        $total1 = $db->query($countQuery1);
        if (isset($total1[0][0]['total'])) {
            $totalCount += (int)$total1[0][0]['total'];
        }

        $dataQuery1 = "
                        SELECT
                            srd.*,
                            CONCAT(e_emp.EmpName, ' - ', e_emp.employee_id) AS emp_fkey,
                            CONCAT(e_rep.EmpName, ' - ', e_rep.employee_id) AS reporting_officer,
                            CONCAT(e_rev.EmpName, ' - ', e_rev.employee_id) AS reviewing_officer,
                            CONCAT(e_created.EmpName, ' - ', e_created.employee_id) AS created_by,
                            CONCAT(e_modified.EmpName, ' - ', e_modified.employee_id) AS modified_by
                        FROM self_review_details srd
                        LEFT JOIN employee_info e_emp ON srd.emp_fkey = e_emp.emp_pkey
                        LEFT JOIN employee_info e_rep ON srd.reporting_officer = e_rep.emp_pkey
                        LEFT JOIN employee_info e_rev ON srd.reviewing_officer = e_rev.emp_pkey
                        LEFT JOIN employee_info e_created ON srd.created_by = e_created.emp_pkey
                        LEFT JOIN employee_info e_modified ON srd.modified_by = e_modified.emp_pkey
                        WHERE srd.status != 'Deleted' $condition
                        ORDER BY srd.self_review_details_pkey DESC
                        ";
        $results1 = $db->query($dataQuery1);


        foreach ($results1 as $row) {
            $created_date = '';
            if (!empty($row['srd']['created_date'])) {
                $created_date = date('d-m-Y H:i:s', strtotime($row['srd']['created_date']));
            }
            $modified_date = '';
            if (!empty($row['srd']['modified_date'])) {
                $modified_date = date('d-m-Y H:i:s', strtotime($row['srd']['modified_date']));
            }


            $empId = $row['srd']['emp_fkey'];
            $reportingId = $row['srd']['reporting_officer'];
            $reviewingId = $row['srd']['reviewing_officer'];

            $drafted_by = $row['srd']['drafted_by'];
            $reported_by = $row['srd']['reported_by'];
            $applied_by = $row['srd']['applied_by'];
            $reviewed_by = $row['srd']['reviewed_by'];
            $rejected_by = $row['srd']['rejected_by'];

            $statusLabel = 'Unknown';

            // Highest-priority status check first
            if (str_word_count($row['srd']['status']) > 2) {
                $statusLabel = $row['srd']['status'];
            } elseif ((int)$row['srd']['is_reviewed'] === 1 && $reviewed_by == $reviewingId) {
                $statusLabel = 'Reviewing Person submitted the Appraisal';
            } elseif ((int)$row['srd']['is_reported'] === 1 && $reported_by == $reportingId) {
                $statusLabel = 'Reporting person submitted the Appraisal';
            } elseif ((int)$row['srd']['is_applied'] === 1 && $applied_by == $empId) {
                $statusLabel = 'Self Review Completed';
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
                $statusLabel = 'Self Appraisal Initiated';
            }


            if ($statusLabel == 'Unknown') {
                $statusLabel = $row['srd']['status'];
            }

            $rowsFormatted[] = array(
                'table' => 'self_review_details',
                'pkey' => $row['srd']['self_review_details_pkey'],
                'emp_pkey' => $empId, //Edited by Akshay on 9-6-2025
                'emp_fkey' => $row[0]['emp_fkey'],
                'reporting_officer' => $row[0]['reporting_officer'],
                'reviewing_officer' => $row[0]['reviewing_officer'],
                'created_by' => $row[0]['created_by'],
                'created_date' => $created_date,
                'modified_by' => $row[0]['modified_by'],
                'modified_date' => $modified_date,
                'status' => $row['srd']['status'],
                'status_label' => $statusLabel
            );
        }

        // ---------- Second: assessment_attributes_staff_details ----------
        $countQuery2 = "
        SELECT COUNT(*) AS total 
        FROM assessment_attributes_staff_details srd
        WHERE srd.status != 0 
        -- AND srd.created_by = $emp_fkey
        ";
        $total2 = $db->query($countQuery2);
        if (isset($total2[0][0]['total'])) {
            $totalCount += (int)$total2[0][0]['total'];
        }

        $dataQuery2 = "
                        SELECT
                            srd.*,
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
                        -- AND srd.created_by = $emp_fkey
                        ORDER BY srd.attr_staff_details_pkey DESC
                    ";
        $results2 = $db->query($dataQuery2);


        foreach ($results2 as $row) {
            $created_date = '';
            if (!empty($row['srd']['creation_date'])) {
                $created_date = date('d-m-Y H:i:s', strtotime($row['srd']['creation_date']));
            }
            $modified_date = '';
            if (!empty($row['srd']['modification_date'])) {
                $modified_date = date('d-m-Y H:i:s', strtotime($row['srd']['modification_date']));
            }

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

            if ($statusLabel == 'Unknown') {
                $statusLabel = $row['srd']['status'];
            }

            $rowsFormatted[] = array(
                'table' => 'assessment_attributes_staff_details',
                'pkey' => $row['srd']['attr_staff_details_pkey'],
                'emp_pkey' => $empId, //Edited by Akshay on 9-6-2025
                'emp_fkey' => $row[0]['emp_fkey'],
                'reporting_officer' => $row[0]['reporting_officer'],
                'reviewing_officer' => $row[0]['reviewing_officer'],
                'created_by' => $row[0]['created_by'],
                'created_date' => $created_date,
                'modified_by' => $row[0]['modified_by'],
                'modified_date' => $modified_date,
                'status' => $row['srd']['status'],
                'status_label' => $statusLabel
            );
        }

        usort($rowsFormatted, function ($a, $b) {
            $dateA = strtotime($a['created_date']);
            $dateB = strtotime($b['created_date']);
            if ($dateA == $dateB) return 0;
            return ($dateA < $dateB) ? 1 : -1; // For descending order
        });



        // ---------- Final: sort and paginate combined results ----------
        usort($rowsFormatted, function ($a, $b) {
            $aDate = strtotime($a['created_date']);
            $bDate = strtotime($b['created_date']);
            return $bDate - $aDate; // descending
        });

        $pagedRows = array_slice($rowsFormatted, $offset, $rows);

        echo json_encode(array(
            'total' => $totalCount,
            'rows' => $pagedRows
        ));
    }

    public function listreviews_self_review()
    {
        // $this->layout = 'ajax';
        $this->autoRender = false;

        $emp_fkey = $this->Session->read('emp_fkey');
        // Optional: if you're using a different DB config set in Session
        $dsName = $this->Session->read('ds') ?: 'default';
        $db = ConnectionManager::getDataSource($dsName);

        // Read page and rows from query string (for EasyUI pagination)
        $page = isset($this->request->query['page']) ? (int)$this->request->query['page'] : 1;
        $rows = isset($this->request->query['rows']) ? (int)$this->request->query['rows'] : 10;
        $offset = ($page - 1) * $rows;

        $isHR = isset($_POST['isHR']) ? $_POST['isHR'] : '';

        if ($isHR == true) {
            $condition = " AND srd.created_by = $emp_fkey ";
        } else {
            $condition = " AND (srd.emp_fkey = $emp_fkey OR srd.reporting_officer = $emp_fkey OR srd.reviewing_officer = $emp_fkey) ";
        }

        $condition = " AND srd.emp_fkey = $emp_fkey ";

        // Count total records
        $totalCount = 0;

        // ---------- First: self_review_details ----------
        $countQuery1 = "
        SELECT COUNT(*) AS total 
        FROM self_review_details srd
        WHERE srd.status != 'Deleted' $condition
        ";
        $total1 = $db->query($countQuery1);
        if (isset($total1[0][0]['total'])) {
            $totalCount += (int)$total1[0][0]['total'];
        }

        // Main data query
        $dataQuery = "
                            SELECT
                                srd.*,
                                e_emp.emp_pkey AS emp_pkey, 
                                CONCAT(e_emp.EmpName, ' - ', e_emp.employee_id) AS emp_fkey,
                                CONCAT(e_rep.EmpName, ' - ', e_rep.employee_id) AS reporting_officer,
                                CONCAT(e_rev.EmpName, ' - ', e_rev.employee_id) AS reviewing_officer,
                                CONCAT(e_created.EmpName, ' - ', e_created.employee_id) AS created_by,
                                CONCAT(e_modified.EmpName, ' - ', e_modified.employee_id) AS modified_by
                            FROM self_review_details srd
                            LEFT JOIN employee_info e_emp ON srd.emp_fkey = e_emp.emp_pkey
                            LEFT JOIN employee_info e_rep ON srd.reporting_officer = e_rep.emp_pkey
                            LEFT JOIN employee_info e_rev ON srd.reviewing_officer = e_rev.emp_pkey
                            LEFT JOIN employee_info e_created ON srd.created_by = e_created.emp_pkey
                            LEFT JOIN employee_info e_modified ON srd.modified_by = e_modified.emp_pkey
                            WHERE srd.status != 'Deleted' $condition
                            ORDER BY srd.self_review_details_pkey DESC
                            LIMIT $rows OFFSET $offset;


        ";

        $results = $db->query($dataQuery);



        $rowsFormatted = array();
        foreach ($results as $row) {
            $created_date = isset($row['srd']['created_date']) ? date('d-m-Y H:i:s', strtotime($row['srd']['created_date'])) : '';
            $modified_date = isset($row['srd']['modified_date']) ? date('d-m-Y H:i:s', strtotime($row['srd']['modified_date'])) : '';

            $empId = $row['srd']['emp_fkey'];
            $reportingId = $row['srd']['reporting_officer'];
            $reviewingId = $row['srd']['reviewing_officer'];
            //edited by athira on 19-05-2025
            $emp_pkey = $row['e_emp']['emp_pkey'];
            //end
            $drafted_by = $row['srd']['drafted_by'];
            $applied_by = $row['srd']['applied_by']; // Edited by Akshay on 18-5-2025
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
                $statusLabel = 'Self Appraisal Initiated';
            }

            if ($row['srd']['status'] == 'Applied') {
                $statusLabel = 'Self Review Completed';
            }

            if ($statusLabel == 'Unknown') {
                $statusLabel = $row['srd']['status'];
            }


            $rowsFormatted[] = array(
                'pkey' => $row['srd']['self_review_details_pkey'],
                //edited by athira on 19-05-2025
                'emp_pkey' => $row['e_emp']['emp_pkey'],
                //end
                'emp_fkey' => $row[0]['emp_fkey'],
                'reporting_officer' => $row[0]['reporting_officer'],
                'reviewing_officer' => $row[0]['reviewing_officer'],
                'created_by' => $row[0]['created_by'],
                'created_date' => $created_date,
                'modified_by' => $row[0]['modified_by'],
                'modified_date' => $modified_date,
                'status' => $row['srd']['status'],
                'status_label' => $statusLabel
            );
        }
        echo json_encode(array(
            'total' => $totalCount,
            'rows' => $rowsFormatted
        ));
    }


    public function getEmployeeDetails()
    {
        $this->autoRender = false;
        $arr_form_data = $this->request->data;
        $emp_fkey = $arr_form_data['emp_fkey'];

        if (!$emp_fkey) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid employee ID']);
            return;
        }

        $this->SelfReviewDetails->useDbConfig = $this->Session->read('ds');

        // Get main employee data
        $sql = "
            SELECT 
                ei.emp_pkey,
                ei.EmpName,
                ei.employee_id,
                ei.designation AS emp_info_designation,
                ei.department AS emp_info_department,
                ei.joining_date AS doj,
                ep.emp_type,
                ep.designation AS emp_proff_designation,
                ep.emp_dept,
                ep.emp_grade,
                ed.date_of_birth
            FROM 
                employee_info ei
            LEFT JOIN 
                emp_proff ep ON ep.emp_fkey = ei.emp_pkey
            LEFT JOIN
                emp_details ed ON ed.emp_pkey = ei.emp_pkey
            WHERE 
                ei.emp_pkey = {$emp_fkey}
        ";

        $result = $this->SelfReviewDetails->query($sql);

        if (empty($result)) {
            echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
            return;
        }

        $employee = $result[0];

        // Get Reporting Officers
        $reporting_officers = $this->SelfReviewDetails->query("
            SELECT ei.EmpName, ei.employee_id, ei.emp_pkey 
            FROM employee_info ei 
            WHERE ei.emp_pkey IN (
                SELECT DISTINCT policy_id 
                FROM emp_config 
                WHERE type = 'HIERARCHY' AND status = 1 AND emp_fkey = {$emp_fkey}
            )
            AND ei.emp_status = 1;
        ");

        // Get Reviewing Officers
        $reviewing_officers = $this->SelfReviewDetails->query("
            SELECT ei.EmpName, ei.employee_id, ei.emp_pkey 
            FROM employee_info ei 
            WHERE ei.emp_pkey IN (
                SELECT DISTINCT policy_id 
                FROM emp_config 
                WHERE type = 'LAPPR' AND status = 1 AND emp_fkey = {$emp_fkey}
            )
            AND ei.emp_status = 1;
        ");

        if (empty($reporting_officers) || empty($reviewing_officers)) {
            $arr_employees = $this->SelfReviewDetails->query("
                                                                SELECT ei.EmpName, ei.employee_id, ei.emp_pkey 
                                                                FROM employee_info ei 
                                                                WHERE ei.emp_status = 1
                                                                ORDER BY ei.EmpName;
                                                            ");

            if (empty($reporting_officers)) {
                $reporting_officers = $arr_employees;
            }

            if (empty($reviewing_officers)) {
                $reviewing_officers = $arr_employees;
            }
        }

        $fin_years = $this->SelfReviewDetails->query("SELECT fin_year AS fin_year, CONCAT(YEAR(start_month), '-', YEAR(end_month)) AS year_range FROM fin_year WHERE vattr1 = 1 AND branch_code IN (SELECT branch_code FROM employee_info WHERE emp_pkey = $emp_fkey) AND status = 1 ORDER BY fin_year DESC;");

        $arr_grade = $this->SelfReviewDetails->query("SELECT 
                                                            COALESCE(modification_date, creation_date) AS grade_entry_date 
                                                        FROM `emp_config` 
                                                        WHERE `emp_fkey` = {$emp_fkey} 
                                                        AND `status` = '1' 
                                                        AND `type` = 'GRADE' 
                                                        LIMIT 1;
                                                    ");
        $grade_entry_date = '';
        if (!empty($arr_grade) && !empty($arr_grade[0][0]['grade_entry_date'])) {
            $grade_entry_date = date('Y-m-d', strtotime($arr_grade[0][0]['grade_entry_date']));
        }

        $arr_category = $this->SelfReviewDetails->query("SELECT c.category_code
                                                                FROM emp_proff e
                                                                LEFT JOIN grade g ON e.emp_grade = g.grade_pkey
                                                                LEFT JOIN category c ON g.category_fkey = c.category_pkey
                                                                WHERE e.emp_fkey = $emp_fkey;
                                                                ");
        $category_code = isset($arr_category[0]['c']['category_code']) ? $arr_category[0]['c']['category_code'] : '';
        // debug($category_code);
        if ($category_code !== '') {
            $category = ($category_code !== 'WORK') ? 'employee' : 'hierarchy';
        } else {
            $category = '';
        }


        echo json_encode([
            'status' => 'success',
            'employee' => [
                'emp_pkey' => $employee['ei']['emp_pkey'],
                'EmpName' => $employee['ei']['EmpName'],
                'employee_id' => $employee['ei']['employee_id'],
                'emp_info_designation' => $employee['ei']['emp_info_designation'],
                'emp_info_department' => $employee['ei']['emp_info_department'],
                'doj' => $employee['ei']['doj'],
                'emp_type' => $employee['ep']['emp_type'],
                'emp_proff_designation' => $employee['ep']['emp_proff_designation'],
                'emp_dept' => $employee['ep']['emp_dept'],
                'emp_grade' => $employee['ep']['emp_grade'],
                'date_of_birth' => $employee['ed']['date_of_birth'],
                'grade_entry_date' => $grade_entry_date,
                'category' => $category
            ],
            'reporting_officers' => array_map(function ($officer) {
                return $officer['ei'];
            }, $reporting_officers),
            'reviewing_officers' => array_map(function ($officer) {
                return $officer['ei'];
            }, $reviewing_officers),
            'fin_years' => array_map(function ($fin_year) {
                return [
                    'fin_year' => $fin_year['fin_year']['fin_year'],
                    'year_range' => $fin_year[0]['year_range']
                ];  // Returning both fin_year and year_range
            }, $fin_years)
        ]);
    }

    public function getAbsencePeriod()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $this->request->data;
        $emp_fkey = $arr_form_data['emp_fkey'];
        $fin_year = $arr_form_data['fin_year'];

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
                                                                AND fy.fin_year = $fin_year
                                                                AND fy.vattr1 = 1
                                                                AND fy.status = 1
                                                                AND DATE_FORMAT(STR_TO_DATE(ar.month_year, '%Y-%m'), '%Y-%m') 
                                                                    BETWEEN DATE_FORMAT(fy.start_month, '%Y-%m') AND DATE_FORMAT(fy.end_month, '%Y-%m');
                                                        ");


        $total_lop = isset($arr_lop_total[0][0]['total_lop']) ? $arr_lop_total[0][0]['total_lop'] : 0;

        echo json_encode([
            'status' => 'success',
            'absence_period' => $total_lop
        ]);
        return;
    }




    public function createSelfReview()
    {
        $this->autoRender = false;
        $this->SelfReviewDetails->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $emp_fkey = $this->Session->read('emp_fkey');

        $arr_form_data['status'] = 'New';
        $arr_form_data['created_by'] = $emp_fkey;

        $emp_pkey = $arr_form_data['emp_fkey'];
        $fin_year = $arr_form_data['fin_year'];
        $arr_count = $this->SelfReviewDetails->query("SELECT count(*) AS count FROM self_review_details asd WHERE emp_fkey = '$emp_pkey' AND fin_year = '$fin_year' AND status != 'Deleted';");
        $count = isset($arr_count[0][0]['count']) ? $arr_count[0][0]['count'] : 0;

        if ($count != 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Record already created for this employee.'
            ]);

            exit();
        }

        // Ensure default 0 for each is_ field if not set
        $isFields = ['is_reported', 'is_reviewed', 'is_rejected', 'is_drafted', 'is_applied'];

        foreach ($isFields as $field) {
            if (!isset($arr_form_data[$field])) {
                $arr_form_data[$field] = 0;
            }
        }


        if ($this->SelfReviewDetails->save($arr_form_data)) {

            $message = 'Self Review initiated successfully.';
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
        exit();
    }

    public function form()
    {
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $edit = 1;
        $this->set("edit", $edit);
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
        WHERE ei.emp_pkey IN (SELECT DISTINCT policy_id FROM emp_config WHERE type = 'HIERARCHY' 
                                    AND status = 1 AND emp_fkey = $emp_fkey)
        ");
        $this->set("arr_employees", $arr_employees);
    }

    public function view($edit = 0, $key)
    {
        $this->autoRender = false;
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $this->set("edit", $edit);
        $this->set("emp_fkey", $emp_fkey);
        $this->set("self_review_details_pkey", $key);

        $arr_self_review_details = $this->EmployeeDetails->query("SELECT * FROM self_review_details WHERE self_review_details_pkey = '$key';");
        $duty_desc = $arr_self_review_details[0]['self_review_details']['duty_desc'];
        $this->set("duty_desc", $duty_desc);

        $work_done_desc = $arr_self_review_details[0]['self_review_details']['work_done_desc'];
        $this->set("work_done_desc", $work_done_desc);

        $reporting_officer = $arr_self_review_details[0]['self_review_details']['reporting_officer'];
        $this->set("reporting_officer", $reporting_officer);

        $reviewing_officer = $arr_self_review_details[0]['self_review_details']['reviewing_officer'];
        $this->set("reviewing_officer", $reviewing_officer);

        $arr_leave = array();

        $arr_employees = array();

        if (empty($arr_leave) || empty($arr_employees)) {
            $arr_employees_list = $this->EmployeeDetails->query("
                                                                SELECT ei.EmpName, ei.employee_id, ei.emp_pkey 
                                                                FROM employee_info ei 
                                                                WHERE ei.emp_status = 1
                                                                ORDER BY ei.EmpName;
                                                            ");

            if (empty($arr_leave)) {
                $arr_leave = $arr_employees_list;
            }

            if (empty($arr_employees)) {
                $arr_employees = $arr_employees_list;
            }
        }

        $this->set("arr_leave", $arr_leave);
        $this->set("arr_employees", $arr_employees);

        $this->render('form');
    }

    public function saveSelfReview()
    {
        $this->autoRender = false;
        $this->SelfReviewDetails->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $emp_fkey = $this->Session->read('emp_fkey');

        if (!isset($arr_form_data['self_review_details_pkey']) || empty($arr_form_data['self_review_details_pkey'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Update failed: Missing review ID.'
            ]);
            exit();
        }

        if (isset($arr_form_data['self_review_details_pkey'])) {
            $arr_form_data['modified_by'] = $emp_fkey;
            $arr_form_data['modified_date'] =  date('Y-m-d H:i:s');
        }

        // Ensure default 0 for each is_ field if not set
        $isFields = ['is_reported', 'is_reviewed', 'is_rejected', 'is_drafted', 'is_applied'];

        foreach ($isFields as $field) {
            if (!isset($arr_form_data[$field])) {
                $arr_form_data[$field] = 0;
            }
        }

        $status = isset($arr_form_data['status']) ? $arr_form_data['status'] : '';

        if ($status == 'Applied') {
            // Edited by Akshay on 18-5-2025
            $arr_form_data['is_applied'] = 1;
            $arr_form_data['applied_by'] = $emp_fkey;
            $arr_form_data['applied_date'] = date('Y-m-d H:i:s');
            // End
        } elseif ($status == 'Draft') {
            $arr_form_data['is_drafted'] = 1;
            $arr_form_data['drafted_by'] = $emp_fkey;
            $arr_form_data['drafted_date'] = date('Y-m-d H:i:s');
        }


        try {
            if ($this->SelfReviewDetails->save($arr_form_data)) {
                if ($arr_form_data['status'] == 'Applied') {
                    $message = 'Self Appraisal submitted successfully.';
                } else {
                    $message = 'Self Appraisal saved successfully.';
                }
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
        } catch (Exception $e) {
            debug($e);
        }

        exit();
    }

    public function deleteSelfReview()
    {
        $this->autoRender = false;
        $this->SelfReviewDetails->useDbConfig = $this->Session->read('ds');
        $this->AssessmentAttributesStaffDetails->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->response->type('json');

        if ($this->request->is('post')) {
            $pkey = $this->request->data['pkey'];
            $table = $this->request->data['table'];

            $db = ($table == 'assessment_attributes_staff_details') ? 'AssessmentAttributesStaffDetails' : 'SelfReviewDetails';
            $pKeyName = ($table == 'assessment_attributes_staff_details') ? 'attr_staff_details_pkey' : 'self_review_details_pkey';
            $statusValue = ($table == 'assessment_attributes_staff_details') ? 0 : "'Deleted'";
            $modified_date = ($table == 'assessment_attributes_staff_details') ? "modification_date" : "modified_date";

            $updated = $this->$db->updateAll(
                [
                    "$db.status" => $statusValue,
                    "$db.modified_by" => $emp_fkey,
                    "$db.$modified_date" => "'" . date('Y-m-d') . "'"
                ],
                ["$db.$pKeyName" => $pkey]
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

    // Edited by Athira
    public function previewPdfReview($emp_pkey = null, $pkey = 0)
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

        $emp_pkey = $emp_pkey;

        $emp_data = $this->EmployeeDetails->query("
                                                    SELECT e.EmpName, s.*
                                                    FROM self_review_details s
                                                    JOIN employee_info e ON s.emp_fkey = e.emp_pkey where s.self_review_details_pkey='$pkey'
                                                    ");


        // $this->set(compact('company_logo', 'company_name'));
        $this->set('emp_data', $emp_data);

        // Render the view content as HTML
        App::uses('View', 'View');
        $View = new View($this, false);
        $View->viewPath = 'SelfReview';
        $html = $View->render('performance_report');

        // Load HTML2PDF library
        App::import('Vendor', 'HTML2PDF', ['file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php']);
        $pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', [15, 15, 15, 15]); // [left, top, right, bottom]
        $pdf->pdf->SetFont('times', 'B', 12);
        $pdf->pdf->SetDisplayMode('fullpage');
        $pdf->writeHTML($html);

        // Generate and download PDF
        $filename = 'SelfAppraisal.pdf';
        $pdf->Output($filename, 'D');
        exit();
    }
    // End

    // Edited by Akshay on 5-6-2025
    public function newBulkReview()
    {
        $this->autoRender = false;
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $edit = 1;
        $this->set("edit", $edit);
        $this->set("emp_fkey", $emp_fkey);

        $duty_desc = '';
        $this->set("duty_desc", $duty_desc);

        $work_done_desc = '';
        $this->set("work_done_desc", $work_done_desc);

        // Category
        $arr_category = $this->EmployeeDetails->query("SELECT category_pkey, category_name FROM category WHERE status = 1;");
        $this->set("arr_category", $arr_category);

        $arr_employees = $this->EmployeeDetails->query("SELECT ei.EmpName, ei.employee_id, ei.emp_pkey FROM employee_info ei 
                                                            WHERE ei.emp_status = 1
                                                            ORDER BY TRIM(ei.EmpName)
                                                            ;
                                                            ");
        $this->set("arr_employees", $arr_employees);

        // $financial_years = $this->EmployeeDetails->query("SELECT fin_year AS fin_year, CONCAT(YEAR(start_month), '-', YEAR(end_month)) AS year_range FROM fin_year WHERE vattr1 = 1 AND status = 1 ORDER BY fin_year DESC;");

        // $fin_years = array_map(function ($fin_year) {
        //     return [
        //         'fin_year' => $fin_year['fin_year']['fin_year'],
        //         'year_range' => $fin_year[0]['year_range']
        //     ];  // Returning both fin_year and year_range
        // }, $financial_years);

        // $this->set("fin_years", $fin_years);

        $this->render('new_bulk_review');
    }

    public function getEmployeesByCategory()
    {
        $this->autoRender = false;
        $this->request->onlyAllow('post');

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $category_id = $this->request->data('category');

        $ds = $this->EmployeeDetails->useDbConfig;
        $conn = ConnectionManager::getDataSource($ds);

        $sql = "
        SELECT ei.emp_pkey, ei.EmpName, ei.employee_id 
        FROM employee_info ei
        WHERE ei.emp_pkey IN (
            SELECT ep.emp_fkey 
            FROM emp_proff ep
            INNER JOIN grade g ON ep.emp_grade = g.grade_pkey
            WHERE g.category_fkey = :category_id AND g.status = 1
        )
        AND ei.emp_status = 1
        ORDER BY ei.EmpName ASC
    ";

        $params = ['category_id' => $category_id];
        $employees = $conn->fetchAll($sql, $params);

        echo json_encode([
            'status' => !empty($employees) ? 'success' : 'error',
            'employees' => $employees
        ]);
    }
    // End

    // Edited by Akshay on 6-6-2025
    public function getFinYears()
    {
        $this->autoRender = false;
        $arr_form_data = $this->request->data;
        $emp_fkeys = $arr_form_data['emp_fkey'];

        if (empty($emp_fkeys) || !is_array($emp_fkeys)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid employee ID(s)']);
            return;
        }

        $this->SelfReviewDetails->useDbConfig = $this->Session->read('ds');

        $emp_fkeys_int = array_map('intval', $emp_fkeys);
        $emp_fkeys_list = implode(',', $emp_fkeys_int);

        $fin_years = $this->SelfReviewDetails->query("
                                                        SELECT DISTINCT fin_year AS fin_year, CONCAT(YEAR(start_month), '-', YEAR(end_month)) AS year_range 
                                                        FROM fin_year 
                                                        WHERE vattr1 = 1 
                                                        AND branch_code IN (
                                                            SELECT DISTINCT branch_code 
                                                            FROM employee_info 
                                                            WHERE emp_pkey IN ({$emp_fkeys_list})
                                                        ) 
                                                        AND status = 1 
                                                        ORDER BY fin_year DESC;
                                                            ");

        echo json_encode([
            'status' => 'success',
            'fin_years' => array_map(function ($fin_year) {
                return [
                    'fin_year' => $fin_year['fin_year']['fin_year'],
                    'year_range' => $fin_year[0]['year_range']
                ];
            }, $fin_years)
        ]);
    }

    // Edited by Akshay on 10-6-2025
    protected function getLOP($emp_fkey, $fin_year)
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $result = $this->EmployeeDetails->query("
        SELECT 
            SUM(ar.lop_total) AS total_lop
        FROM 
            attendance_register ar
        JOIN 
            employee_info ei ON ei.emp_pkey = ar.emp_fkey
        JOIN 
            fin_year fy ON fy.branch_code = ei.branch_code
        WHERE 
            ar.emp_fkey = $emp_fkey
            AND fy.fin_year = $fin_year
            AND fy.vattr1 = 1
            AND fy.status = 1
            AND DATE_FORMAT(STR_TO_DATE(ar.month_year, '%Y-%m'), '%Y-%m') 
                BETWEEN DATE_FORMAT(fy.start_month, '%Y-%m') AND DATE_FORMAT(fy.end_month, '%Y-%m')
    ");

        $total_lop = isset($result[0][0]['total_lop']) ? $result[0][0]['total_lop'] : 0;

        return $total_lop;
    }


    public function createBulkSelfReview()
    {
        try {
            $this->autoRender = false;
            $arr_form_data = $this->request->data;
            // debug( $arr_form_data);exit;
            $emp_fkeys = $arr_form_data['emp_fkey'];
            $category_input = $arr_form_data['category'];
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

            $employee_data = $this->SelfReviewDetails->query("
                                                                SELECT 
                                                                    ei.emp_pkey,
                                                                    ei.EmpName,
                                                                    ei.employee_id,
                                                                    ei.branch_code,
                                                                    ei.designation AS emp_info_designation,
                                                                    ei.department AS emp_info_department,
                                                                    ei.joining_date AS doj,
                                                                    ep.emp_type,
                                                                    ep.designation AS emp_proff_designation,
                                                                    ep.emp_dept,
                                                                    ep.emp_grade,
                                                                    ed.date_of_birth
                                                                FROM employee_info ei
                                                                LEFT JOIN emp_proff ep ON ep.emp_fkey = ei.emp_pkey
                                                                LEFT JOIN emp_details ed ON ed.emp_pkey = ei.emp_pkey
                                                                WHERE ei.emp_pkey IN ($emp_ids_str)
                                                        ");
            $unsavedEmployees = [];

            foreach ($employee_data as $empRow) {
                $emp_pkey = $empRow['ei']['emp_pkey'];
                $branch_code = $empRow['ei']['branch_code'];
                $empName = isset($empRow['ei']['EmpName']) ? $empRow['ei']['EmpName'] : '';
                $empId = isset($empRow['ei']['emp_pkey']) ? $empRow['ei']['emp_pkey'] : '';

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


                $absence_period = $this->getLOP($emp_pkey, $fin_year);

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

                // Get category
                $arr_category = $this->SelfReviewDetails->query("
                                                                SELECT c.category_code
                                                                FROM emp_proff e
                                                                LEFT JOIN grade g ON e.emp_grade = g.grade_pkey
                                                                LEFT JOIN category c ON g.category_fkey = c.category_pkey
                                                                WHERE e.emp_fkey = {$emp_pkey}
                                                            ");
                $category_code = isset($arr_category[0]['c']['category_code']) ? $arr_category[0]['c']['category_code'] : '';
                $category = ($category_code !== '') ? (($category_code !== 'WORK') ? 'employee' : 'hierarchy') : '';


                // Prepare data to save
                $saveData = [
                    'SelfReviewDetails' => [
                        'emp_fkey' => isset($empRow['ei']['emp_pkey']) ? $empRow['ei']['emp_pkey'] : null,
                        'designation' => isset($empRow['ei']['emp_info_designation']) ? $empRow['ei']['emp_info_designation'] : null,
                        'dob' => !empty($empRow['ed']['date_of_birth']) ? date('Y-m-d', strtotime($empRow['ed']['date_of_birth'])) : null,
                        'doj' => !empty($empRow['ei']['doj']) ? date('Y-m-d', strtotime($empRow['ei']['doj'])) : null,
                        'grade_entry_date' => $grade_entry_date,
                        'employment_type' => isset($empRow['ep']['emp_type']) ? $empRow['ep']['emp_type'] : null,
                        'department' => isset($empRow['ei']['emp_info_department']) ? $empRow['ei']['emp_info_department'] : null,
                        'absence_period' => $absence_period,  // assign actual value if you have it
                        'fin_year' => (int)$fin_year,
                        'reporting_officer' => (int)$reporting_officer,
                        'reviewing_officer' => (int)$reviewing_officer,
                        'status' => 'New',  // default status
                        'created_by' => isset($created_by_emp_pkey) ? (int)$created_by_emp_pkey : 0,
                        'created_date' => date('Y-m-d H:i:s')
                    ]
                ];

                // debug($saveData);

                // Save the data
                $this->SelfReviewDetails->create(); // for fresh insert
                if (!$this->SelfReviewDetails->save($saveData)) {
                    // Assuming employee name and ID keys
                    $unsavedEmployees[] = $empName . ' - ' . $empId;
                }
            }
            // exit;
            header('Content-Type: application/json');

            if (!empty($unsavedEmployees)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Some entries could not be saved.',
                    'unsaved' => $unsavedEmployees
                ]);
            } else {
                echo json_encode([
                    'status' => 'success',
                    'message' => count($emp_fkeys) . ' self-review entries created successfully.'
                ]);
            }
        } catch (Exception $e) {
            debug($e);
        }
        return;
    }
    // End
}
