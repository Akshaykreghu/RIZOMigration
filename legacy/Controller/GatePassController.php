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
ini_set('max_execution_time', 2000);
ini_set('memory_limit', '1024M');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class GatePassController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'GatePass';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('DocTemplate', 'TemplatesDetails', 'Templates', 'Documents', 'EmpDetails', 'Units', 'EmployeeDetails', 'EmployeeExpenses', 'DocumentAllocation', 'DocumentUpload', 'GatePass', 'GatePassItems');
    public $components = array('MasterdataManagement');

    public function index()
    {
    }
    function pagination($data)
    {
        $limit           = (isset($data['rows']) && !empty($data['rows'])) ? "  limit  " . $data['rows'] . "" : "";
        $offset          = (isset($data['page']) && !empty($data['page'])) ? " offset  " . (($data['page'] - 1)  * $data['rows']) . "" : "";
        return array("limit" => $limit, "offset" => $offset);
    }

    public function downloadHistory($type, $mode)
    {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'salary':
                $dataForHistory['report_type'] = "Cost To Company(CTC) Summary Report";
                break;
            case 'salarystructure':
                $dataForHistory['report_type'] = "Cost To Company Detailed Report";
                break;
            case 'Grosssalary':
                $dataForHistory['report_type'] = "Gross Salary Detailed Report";
                break;
            case 'Analysis':
                $dataForHistory['report_type'] = "Salary Analysis Report";
                break;
            case 'SalaryCombined':
                $dataForHistory['report_type'] = "Salary Combined  Report";
                break;
            case 'GrosssalaryNew':
                $dataForHistory['report_type'] = "Gross Salary Detailed Report_New";
                break;
            case 'GrosssalarySummary':
                $dataForHistory['report_type'] = "Gross Salary Summary Report";
                break;
            case 'SummaryPayroll':
                $dataForHistory['report_type'] = "Payroll Summary Report";
                break;
            case 'Salaryslip':
                $dataForHistory['report_type'] = "Salary Slip";
                break;
            case 'SlipThirdVersion':
                $dataForHistory['report_type'] = "Salary Slip_Version3";
                break;
            case 'SlipSecondVersion':
                $dataForHistory['report_type'] = "Salary Slip_Version2";
                break;
            case 'SlipFirstVersion':
                $dataForHistory['report_type'] = "Salary Slip_Version1";
                break;
            case 'Salaryslipnew':
                $dataForHistory['report_type'] = "Salary Slip New";
                break;
            case 'Payrollslip':
                $dataForHistory['report_type'] = "Payroll Slip";
                break;
            case 'TimeAttendance':
                $dataForHistory['report_type'] = "Payroll Summary Report";
                break;
            case 'GrossPeriod':
                $dataForHistory['report_type'] = "Gross Salary Period Wise Report";
                break;
            case 'BankTranfer':
                $dataForHistory['report_type'] = "Salary Bank Transfer Report";
                break;
            case 'BankTranferNew':
                $dataForHistory['report_type'] = "Salary Bank Transfer_New Report";
                break;
            case 'MonthlyCTCReport':
                $dataForHistory['report_type'] = "Monthly CTC Detailed Report";
                break;
            case 'LOPREPORT':
                $dataForHistory['report_type'] = "Payroll Report with LOP";
                break;
            case 'cascadeslip':
                $dataForHistory['report_type'] = "Salary Slip_Cascade";
                break;
            case 'statutory':
                $dataForHistory['report_type'] = "Statutory Report";
                $component = $arr_form_data['hidden-report_component'];
                switch ($component) {
                    case 'cont':
                        $dataForHistory['report_component'] = 'Register Of Contractors';
                        break;
                    case 'workmen':
                        $dataForHistory['report_component'] = 'Register Of Workmen Employed By Contractor';
                        break;
                    case 'wageslip':
                        $dataForHistory['report_component'] = 'Wage Slip';
                        break;
                    case 'employmentcard':
                        $dataForHistory['report_component'] = 'Employment Card';
                        break;
                    case 'muster_roll':
                        $dataForHistory['report_component'] = 'Muster Roll';
                        break;
                    case 'register_fines':
                        $dataForHistory['report_component'] = 'Register Of Fines';
                        break;
                    case 'register_advances':
                        $dataForHistory['report_component'] = 'Register Of Advances';
                        break;
                    case 'register_overtime':
                        $dataForHistory['report_component'] = 'Register Of Overtime';
                        break;
                    case 'register_wages':
                        $dataForHistory['report_component'] = 'Register Of Wages';
                        break;
                    case 'register_musterroll':
                        $dataForHistory['report_component'] = 'Form Of Register Of Wages-cum Muster Roll';
                        break;
                    case 'register_deductions':
                        $dataForHistory['report_component'] = 'Register Of Deductions For Damage Or Loss';
                        break;
                }
                break;
        }

        $dataForHistory['report_from'] = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
        $dataForHistory['report_to'] = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] : '';
        $dataForHistory['include_resigned'] = isset($arr_form_data['resigned']) ? $arr_form_data['resigned'] : '';
        $dataForHistory['Include_negative_salary'] = isset($arr_form_data['ngtvsal']) ? $arr_form_data['ngtvsal'] : '';
        $criteria_count = isset($arr_form_data['hidden-criterias-count']) ? $arr_form_data['hidden-criterias-count'] : 1;
        $i = 1;
        $criteria_array = array();
        $criteria_name_array = array();
        $items_array = array();
        $items_count_array = array();
        while ($i <= $criteria_count) {
            $criteria = isset($arr_form_data['hidden-criteria' . $i]) ? $arr_form_data['hidden-criteria' . $i] : '';
            $criteria_array[] = $criteria;

            switch ($criteria) {
                case 'EmployeeDetails':
                    $criteria_name_array[] = 'belonging to an Employee';
                    break;
                case 'Units':
                    $criteria_name_array[] = 'belonging to a Branch';
                    break;
                case 'Departments':
                    $criteria_name_array[] = 'belonging to a Department';
                    break;
                case 'Grades':
                    $criteria_name_array[] = 'belonging to a Grade';
                    break;
                case 'Verticals':
                    $criteria_name_array[] = 'belonging to a Vertical';
                    break;
                case 'SalaryHeadItems':
                    $criteria_name_array[] = 'belonging to a Salary Head Item';
                    break;
                case 'LeaveRequests':
                    $criteria_name_array[] = 'belonging to a Leave Request';
                    break;
                case 'LeavePolicyGroup':
                    $criteria_name_array[] = 'belonging to a Bank';
                    break;
                case 'Leavestatus':
                    $criteria_name_array[] = 'belonging to a Leave status';
                    break;
                case 'LeavesPolicyGroup':
                    $criteria_name_array[] = 'belonging to a Leaves Policy Group';
                    break;
                case 'EmployeeGrossDetails':
                    $criteria_name_array[] = 'belonging to a Designation';
                    break;
                case 'DayTimeProcedures':
                    $criteria_name_array[] = 'belonging to a Day Time Procedure';
                    break;
                case 'Designation':
                    $criteria_name_array[] = 'belonging to a Designation';
                    break;
                case 'Gender':
                    $criteria_name_array[] = 'belonging to a Gender';
                    break;
                default:
                    break;
            }
            $items_array[] = implode(",", $arr_form_data[$criteria]);
            $items_count_array[] = count($arr_form_data[$criteria]);

            $i++;
        }

        $dataForHistory['criteria'] = implode(",", $criteria_array);
        $dataForHistory['criteria_name'] = implode(",", $criteria_name_array);
        $dataForHistory['items'] = implode(",", $items_array);
        $dataForHistory['items_count'] = implode(",", $items_count_array);


        if ($mode == 'pdf') {
            $dataForHistory['mode'] = 'PDF Download';
        } else if ($mode == 'excel') {
            $dataForHistory['mode'] = 'Excel Download';
        } else {
            $dataForHistory['mode'] = 'View Report';
        }

        $user_id = $this->Session->read('login_user_id');
        $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
        $user_name = $this->Session->read('user_name');
        $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';

        //        debug($dataForHistory);
        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $this->ReportAudit->save($dataForHistory);
    }

    public function uploadForm($pkey = 0)
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $this->GatePass->useDbConfig = $this->Session->read('ds');
        $this->GatePassItems->useDbConfig = $this->Session->read('ds');
        date_default_timezone_set('Asia/Kolkata');
        $currentYear = date('Y');

        $arr_gate_pass = array();
        $arr_item = array();
        try {


            $arr_emp = $this->EmployeeDetails->query("SELECT ei.emp_pkey, ei.EmpName, ei.employee_id 
                                                        FROM employee_info ei 
                                                        WHERE ei.emp_status = 1 
                                                        ORDER BY ei.EmpName;
                                                        ");
            $this->set("arr_emp", $arr_emp);
            $this->set("pkey", $pkey);
            if ($pkey == 0) {
                $arr_pass_pkey = $this->GatePass->query("SELECT COALESCE(MAX(gate_pass_pkey), 0) AS latest_pkey FROM gate_pass");
                $pass_pkey = isset($arr_pass_pkey[0][0]['latest_pkey']) ? $arr_pass_pkey[0][0]['latest_pkey'] : 0;
                $pass_pkey = $pass_pkey + 1;
                $pass_pkey = sprintf("%03d", $pass_pkey);
                $pass_no = $currentYear . '/' . ($pass_pkey);
                $arr_gate_pass[0]['GatePass']['gate_pass_no'] = $pass_no;
            } else {

                $arr_gate_pass = $this->GatePass->find('all', [
                    'fields' => ['GatePass.*, gi.*'],
                    'conditions' => ['GatePass.status' => 1, 'GatePass.gate_pass_pkey' => $pkey],
                    'joins' => [
                        [
                            'table' => 'gate_pass_items',
                            'alias' => 'gi',
                            'type' => 'LEFT',
                            'conditions' => 'gi.gate_pass_items_pkey = GatePass.gate_pass_items_fkey'
                        ]
                    ],
                    'order' => ['GatePass.gate_pass_pkey']
                ]);

                $arr_item = $this->GatePassItems->find('all', [
                    'fields' => ['GatePassItems.*'],
                    'conditions' => ['GatePassItems.status' => 1, 'GatePassItems.gate_pass_fkey' => $pkey],
                    'order' => ['GatePassItems.gate_pass_items_pkey']
                ]);

                //debug($arr_gate_pass);

            }
        } catch (Exception $e) {
            debug($e);
        }
        // debug($arr_gate_pass);

        $arr_gate_pass = $arr_gate_pass[0]['GatePass'];

        $this->set("arr_item", $arr_item);
        $this->set("arr_gate_pass", $arr_gate_pass);
    }
    public function getDocumentsFromDatabase()
    {
        $this->autoRender = false;
        $this->GatePass->useDbConfig = $this->Session->read('ds');
        $livesite = $this->webroot;
        $this->set('livesite', $livesite);
        $userGroup = $this->Session->read('user_group');
        $loginUser = $this->Session->read('login_user_id');

        $arr_data = $this->request->data;

        $data = array();
        $formattedData = array();

        $search = isset($arr_data['gate_pass_no']) ? $arr_data['gate_pass_no'] : '';
        $page = isset($arr_data['page']) ? $arr_data['page'] : 1;
        $rows = isset($arr_data['rows']) ? $arr_data['rows'] : 10;


        if (true) {
            if ($search !== '') {
                $conditions = [
                    'OR' => [
                        'GatePass.gate_pass_no LIKE' => '%' . $search . '%',
                        'GatePass.issued_date LIKE' => '%' . $search . '%',
                        'GatePass.type LIKE' => '%' . $search . '%',
                        'GatePass.creation_date LIKE' => '%' . $search . '%',
                        'ei.EmpName LIKE' => '%' . $search . '%',
                    ],
                    'status !=' => 0,
                ];
            } else {
                $conditions['status !='] = 0;
            }

            $data = $this->GatePass->find('all', [
                'conditions' => $conditions,
                'fields' => [
                    'GatePass.gate_pass_pkey',
                    'GatePass.gate_pass_no',
                    'GatePass.type',
                    'GatePass.issued_date',
                    'GatePass.creation_date',
                    'ei.EmpName', // Add this field for EmpName
                    'ei.emp_pkey',
                    'GatePass.issued_to',
                    'GatePass.status'
                ],
                'joins' => [
                    [
                        'table' => 'employee_info',
                        'alias' => 'ei',
                        'type' => 'LEFT',
                        'conditions' => [
                            'ei.emp_pkey = GatePass.issued_to',
                        ],
                    ],
                ],
                'order' => ['GatePass.gate_pass_pkey' => 'DESC']
            ]);
            // debug($data);
        }

        // debug($data);

        foreach ($data as $row) {
            $gatePkey =  $row['GatePass']['gate_pass_pkey'];
            $formattedData[] = [
                'gate_pass_pkey' => $row['GatePass']['gate_pass_pkey'],
                'gate_pass_no' => $row['GatePass']['gate_pass_no'],
                'status' => $row['GatePass']['status'],
                'type' => $row['GatePass']['type'],
                'issued_date' => $row['GatePass']['issued_date'],
                'creation_date' => $row['GatePass']['creation_date'],
                'issued_to' => $row['GatePass']['issued_to'],
                'issued_to_pkey' => $row['ei']['emp_pkey'],
                // 'download_link' => "<a href='javascript:void(0)' onclick=\"loadPDFPreview('{$pdfPath}','{$type}','{$fileName}')\"><button>View</button></a>"
                'download_link' => "<a href='javascript:void(0)' onclick=\"printGatePass('{$gatePkey}')\"><button>Download</button></a>"
            ];
        }

        $start = ($page - 1) * $rows;

        $slicedData = array_slice($formattedData, $start, $rows);

        $response = [
            'total' => count($formattedData),
            'rows' => $slicedData
        ];

        // Respond with JSON data
        echo json_encode($response);
    }

    public function savePass($pkey = 0)
    {
        $this->autoRender = false;
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $this->GatePass->useDbConfig = $this->Session->read('ds');
        $this->GatePassItems->useDbConfig = $this->Session->read('ds');

        try {
            // Assuming the request is a POST request
            if ($this->request->is('post')) {
                $postData = $this->request->data;
                // debug($postData); exit;
                $item = array();
                $data = array();

                $data['type'] = isset($postData['type']) ? $postData['type'] : '';
                $data['gate_pass_no'] = isset($postData['gate_pass_no']) ? $postData['gate_pass_no'] : '';
                $data['req_person'] = isset($postData['req_person']) ? $postData['req_person'] : '';
                $data['issued_to'] = isset($postData['issued_to']) ? $postData['issued_to'] : '';
                $data['purpose'] = isset($postData['purpose']) ? $postData['purpose'] : '';
                $data['remarks'] = isset($postData['remarks']) ? $postData['remarks'] : '';
                $data['issued_by'] = isset($postData['issued_by']) ? $postData['issued_by'] : '';
                $data['sanctioned_by'] = isset($postData['sanctioned_by']) ? $postData['sanctioned_by'] : '';
                $data['issued_date'] = isset($postData['issued_date']) ? $postData['issued_date'] : '';

                if (isset($postData['item_name'])) {
                    $item['item_name'] = isset($postData['item_name']) ? $postData['item_name'] : '';
                    $item['qty'] = isset($postData['qty']) ? $postData['qty'] : '';
                    $item['units'] = isset($postData['units']) ? $postData['units'] : '';
                    $item['rate'] = isset($postData['rate']) ? $postData['rate'] : '';
                    $item['amount'] = isset($postData['amount']) ? $postData['amount'] : '';
                    $item['return'] = isset($postData['return']) ? $postData['return'] : null;
                }

                // debug($item);exit;

                // debug($item); exit;
                $saveData = array();
                // debug($pkey);
                // debug($_POST); exit;

                $originalDate = isset($data['issued_date']) ? $data['issued_date'] : '00/00/0000';
                //$date = DateTime::createFromFormat('m/d/Y', $originalDate);
                $data['issued_date'] =  date('Y-m-d', strtotime($originalDate));

                if ($pkey == 0) {
                    // debug($data); exit;
                    $save = $this->GatePass->save($data);

                    $gatePassPkey = $this->GatePass->id;

                    // debug($gatePassPkey); exit;
                    if (isset($item['item_name'])) {
                        for ($i = 0; $i < count($item['item_name']); $i++) {
                            $itemName = isset($item['item_name'][$i]) ? $item['item_name'][$i] : '';
                            $qty = isset($item['qty'][$i]) ? $item['qty'][$i] : '';
                            $units = isset($item['units'][$i]) ? $item['units'][$i] : '';
                            $rate = isset($item['rate'][$i]) ? $item['rate'][$i] : '';
                            $amount = isset($item['amount'][$i]) ? $item['amount'][$i] : '';
                            $returnDate = isset($item['return'][$i]) ? $item['return'][$i] : null;

                            try {
                                $saveItem = $this->GatePassItems->query("INSERT INTO gate_pass_items (gate_pass_fkey, item_name, qty, units, rate, amount, `return`) VALUES ('$gatePassPkey', '$itemName', '$qty', '$units', '$rate', '$amount', '$returnDate')");
                            } catch (Exception $e) {
                                debug($e);
                                exit;
                            }
                        }
                    }
                } else {
                    try {
                        $gatePassPkey = $pkey;

                        //$save = $this->GatePass->updateAll($saveData);   
                        $type = isset($data['type']) ? $data['type'] : '';
                        $gate_pass_no = isset($data['gate_pass_no']) ? $data['gate_pass_no'] : '';
                        $req_person = isset($data['req_person']) ? $data['req_person'] : '';
                        $issued_to = isset($data['issued_to']) ? $data['issued_to'] : '';
                        $purpose = isset($data['purpose']) ? $data['purpose'] : '';
                        $remarks = isset($data['remarks']) ? $data['remarks'] : '';
                        $issued_by = isset($data['issued_by']) ? $data['issued_by'] : '';
                        $sanctioned_by = isset($data['sanctioned_by']) ? $data['sanctioned_by'] : '';
                        $issued_date = isset($data['issued_date']) ? $data['issued_date'] : '';



                        $save = $this->GatePass->query("UPDATE `gate_pass` AS `GatePass`
                                                        SET
                                                            `GatePass`.`type` = '$type',
                                                            `GatePass`.`gate_pass_no` = '$gate_pass_no',
                                                            `GatePass`.`req_person` = '$req_person',
                                                            `GatePass`.`issued_to` = '$issued_to',
                                                            `GatePass`.`purpose` = '$purpose',
                                                            `GatePass`.`remarks` = '$remarks',
                                                            `GatePass`.`issued_by` = $issued_by,
                                                            `GatePass`.`sanctioned_by` = '$sanctioned_by',
                                                            `GatePass`.`issued_date` = '$issued_date'
                                                        WHERE
                                                            `GatePass`.`gate_pass_pkey` = $pkey;
                                                        ");
                    } catch (Exception $e) {
                        debug($e);
                        exit;
                    }
                }
                //debug($save);exit;
                if ($save !== false) {
                    // Success response
                    $response = [
                        'status' => 'success',
                        'message' => 'Gate pass saved successfully.',
                        'pkey' => $gatePassPkey
                    ];
                } else {
                    // Failure response
                    $response = [
                        'status' => 'error',
                        'message' => 'Error saving gate pass. Please try again.'
                    ];
                }
            } else {
                // Invalid request type
                $response = [
                    'status' => 'error',
                    'message' => 'Invalid request type.'
                ];
            }

            // Return JSON response
            $this->response->type('json');
            echo json_encode($response);
        } catch (Exception $e) {
            debug($e);
            exit;
        }
    }

    public function savePreview($pkey = 0, $status = 0, $edit = false)
    {
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $this->GatePass->useDbConfig = $this->Session->read('ds');
        $this->GatePassItems->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        $this->set('pkey',  $pkey);
        $this->set('status', $status);
        $this->set('edit',  $edit);

        try {
            $arr_gate_pass = $this->GatePass->query("SELECT
            GatePass.*,
            req.EmpName AS req_person_name,
            issued.EmpName AS issued_to_name,
            issued_by.EmpName AS issued_by_name,
            sanctioned_by.EmpName AS sanctioned_by_name
        FROM
            gate_pass AS GatePass
        LEFT JOIN
            employee_info AS req ON GatePass.req_person = req.emp_pkey
        LEFT JOIN
            employee_info AS issued ON GatePass.issued_to = issued.emp_pkey
        LEFT JOIN
            employee_info AS issued_by ON GatePass.issued_by = issued_by.emp_pkey
        LEFT JOIN
            employee_info AS sanctioned_by ON GatePass.sanctioned_by = sanctioned_by.emp_pkey
        WHERE
            GatePass.gate_pass_pkey = '$pkey'
        ORDER BY
            GatePass.gate_pass_pkey;
        ");

            $arr_gate_pass = isset($arr_gate_pass[0]) ? $arr_gate_pass[0] : array();
            // debug($arr_gate_pass);
            $this->set('arr_gate_pass', $arr_gate_pass);

            $arr_item = $this->GatePass->query("SELECT gi.* FROM gate_pass_items gi WHERE gate_pass_fkey = '$pkey' AND status = 1 ORDER BY gi.gate_pass_items_pkey");
            // debug($arr_item);
            $this->set('arr_item',  $arr_item);

            $arr_emp = $this->GatePass->query("SELECT ei.emp_pkey, ei.EmpName, ei.employee_id 
            FROM employee_info ei 
            WHERE ei.emp_status = 1 
            ORDER BY ei.EmpName;
            ");
            $this->set("arr_emp", $arr_emp);
        } catch (Exception $e) {
            debug($e);
            exit;
        }


        // debug($arr_gate_pass);

    }

    public function printPass($pkey = 0, $is_edited = 'false', $pass = 'false')
    {
        try {


            $this->autoRender = false;
            $this->DocTemplate->useDbConfig = $this->Session->read('ds');
            $this->GatePass->useDbConfig = $this->Session->read('ds');
            $this->GatePassItems->useDbConfig = $this->Session->read('ds');
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);

            $item = array();
            if ($pass == 'true') {
                $status = 1;
            } else {
                $status = 2;
            }

            if ($this->request->is('post')) {
                $postData = $this->request->data;
                // debug($is_edited);
                // debug($postData);exit;
                if ($is_edited == 'true') {
                    // debug($is_edited);
                    $data['gate_pass_no'] = isset($postData['gate_pass_no']) ? $postData['gate_pass_no'] : '';
                    $data['req_person'] = isset($postData['req_person']) ? $postData['req_person'] : '';
                    $data['issued_to'] = isset($postData['issued_to']) ? $postData['issued_to'] : '';
                    $data['purpose'] = isset($postData['purpose']) ? $postData['purpose'] : '';
                    $data['remarks'] = isset($postData['remarks']) ? $postData['remarks'] : '';
                    $data['issued_by'] = isset($postData['issued_by']) ? $postData['issued_by'] : '';
                    $data['sanctioned_by'] = isset($postData['sanctioned_by']) ? $postData['sanctioned_by'] : '';
                    $data['issued_date'] = isset($postData['issued_date']) ? $postData['issued_date'] : '';

                    if (isset($postData['item_name'])) {
                        $item['gate_pass_items_pkey'] = isset($postData['gate_pass_items_pkey']) ? $postData['gate_pass_items_pkey'] : array();
                        $item['item_name'] = isset($postData['item_name']) ? $postData['item_name'] : array();
                        $item['qty'] = isset($postData['qty']) ? $postData['qty'] : array();
                        $item['units'] = isset($postData['units']) ? $postData['units'] : array();
                        $item['rate'] = isset($postData['rate']) ? $postData['rate'] : array();
                        $item['amount'] = isset($postData['amount']) ? $postData['amount'] : array();
                        $item['return'] = isset($postData['return']) ? $postData['return'] : array();
                    }

                    // debug($item);exit;
                } else {

                    $save = $this->GatePass->query("UPDATE `gate_pass` AS `GatePass`
                                                    SET
                                                        `GatePass`.`status` = $status
                                                    WHERE
                                                        `GatePass`.`gate_pass_pkey` = $pkey;
                                                    ");
                }


                // debug($item); exit;
                $saveData = array();
                // debug($pkey);
                // debug($_POST); exit;

                $originalDate = isset($data['issued_date']) ? $data['issued_date'] : '00/00/0000';
                //$date = DateTime::createFromFormat('m/d/Y', $originalDate);
                $data['issued_date'] =  date('Y-m-d', strtotime($originalDate));

                if ($is_edited == 'true') {
                    try {

                        $gate_pass_no = isset($data['gate_pass_no']) ? $data['gate_pass_no'] : '';
                        $req_person = isset($data['req_person']) ? $data['req_person'] : '';
                        $issued_to = isset($data['issued_to']) ? $data['issued_to'] : '';
                        $purpose = isset($data['purpose']) ? $data['purpose'] : '';
                        $remarks = isset($data['remarks']) ? $data['remarks'] : '';
                        $issued_by = isset($data['issued_by']) ? $data['issued_by'] : '';
                        $sanctioned_by = isset($data['sanctioned_by']) ? $data['sanctioned_by'] : '';
                        $issued_date = isset($data['issued_date']) ? $data['issued_date'] : '';

                        $user_id = $this->Session->read('login_user_id');
                        date_default_timezone_set('Asia/Kolkata');
                        $date_time = date('Y-m-d H:i:s');



                        $save = $this->GatePass->query("UPDATE `gate_pass` AS `GatePass`
                                                        SET
                                                            `GatePass`.`gate_pass_no` = '$gate_pass_no',
                                                            `GatePass`.`req_person` = '$req_person',
                                                            `GatePass`.`issued_to` = '$issued_to',
                                                            `GatePass`.`purpose` = '$purpose',
                                                            `GatePass`.`remarks` = '$remarks',
                                                            `GatePass`.`issued_by` = $issued_by,
                                                            `GatePass`.`sanctioned_by` = '$sanctioned_by',
                                                            `GatePass`.`issued_date` = '$issued_date',
                                                            `GatePass`.`modified_date` = '$date_time',
                                                            `GatePass`.`modified_by` = '$user_id',
                                                            `GatePass`.`status` = $status

                                                        WHERE
                                                            `GatePass`.`gate_pass_pkey` = $pkey;
                                                        ");
                        if (isset($item['item_name'])) {

                            // Update the status of other items where gate_pass_fkey is the same but gate_pass_items_pkey is not equal to $itemPkey
                            $updateOtherItems = $this->GatePassItems->query("
                                                                                UPDATE gate_pass_items
                                                                                SET
                                                                                    `status` = 0
                                                                                WHERE
                                                                                    gate_pass_fkey = '$pkey';                            
                                                                                ");

                            for ($i = 0; $i < count($item['item_name']); $i++) {
                                $itemPkey = isset($item['gate_pass_items_pkey'][$i]) ? $item['gate_pass_items_pkey'][$i] : 0;
                                $itemName = $item['item_name'][$i];
                                $qty = $item['qty'][$i];
                                $units = $item['units'][$i];
                                $rate = $item['rate'][$i];
                                $amount = $item['amount'][$i];
                                $returnDate = isset($item['return'][$i]) ? $item['return'][$i] : null;

                                try {
                                    if ($itemName != '') {
                                        if ($itemPkey != 0) {
                                            $updateItem = $this->GatePassItems->query("    
                                            UPDATE gate_pass_items
                                            SET
                                                item_name = '$itemName',
                                                qty = '$qty',
                                                units = '$units',
                                                rate = '$rate',
                                                amount = '$amount',
                                                `return` = '$returnDate',
                                                `modified_by` = '$user_id',
                                                `modified_date` = '$date_time',
                                                `status` = 1
                                            WHERE
                                                gate_pass_fkey = '$pkey'
                                            AND
                                                gate_pass_items_pkey = '$itemPkey'    
                                                ");
                                        } else {
                                            $insertItem = $this->GatePassItems->query("
                                                    INSERT INTO gate_pass_items (gate_pass_fkey, item_name, qty, units, rate, amount, `return`)
                                                    VALUES ('$pkey', '$itemName', '$qty', '$units', '$rate', '$amount', '$returnDate')
                                                ");
                                        }
                                    }
                                } catch (Exception $e) {
                                    debug($e);
                                    exit;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        debug($e);
                        exit;
                    }
                }
                //debug($save);exit;
                if ($save !== false) {
                    // Success response
                    $response = [
                        'status' => 'success',
                        'message' => 'Gate pass saved successfully.',
                        'pkey' => $pkey
                    ];
                } else {
                    // Failure response
                    $response = [
                        'status' => 'error',
                        'message' => 'Error saving gate pass. Please try again.'
                    ];
                }
            } else {
                // Invalid request type
                $response = [
                    'status' => 'error',
                    'message' => 'Invalid request type.'
                ];
            }

            // Return JSON response
            $this->response->type('json');
        } catch (Exception $e) {
            // debug($e);exit;
        }

        // JSON response (after PDF is generated)
        echo json_encode($response);
    }

    public function printGatePass($pkey = 0, $pass = 'false')
    {
        $this->autoRender = false;
        $this->GatePass->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        try {
            if ($pass == 'false') {
                $arr_gate_pass = $this->GatePass->query("SELECT
                            GatePass.*,
                            req.EmpName AS req_person_name,
                            issued.EmpName AS issued_to_name,
                            issued_by.EmpName AS issued_by_name,
                            sanctioned_by.EmpName AS sanctioned_by_name
                        FROM
                            gate_pass AS GatePass
                        LEFT JOIN
                            employee_info AS req ON GatePass.req_person = req.emp_pkey
                        LEFT JOIN
                            employee_info AS issued ON GatePass.issued_to = issued.emp_pkey
                        LEFT JOIN
                            employee_info AS issued_by ON GatePass.issued_by = issued_by.emp_pkey
                        LEFT JOIN
                            employee_info AS sanctioned_by ON GatePass.sanctioned_by = sanctioned_by.emp_pkey
                        WHERE
                            GatePass.gate_pass_pkey = '$pkey'
                        ORDER BY
                            GatePass.gate_pass_pkey;");
            }

            if ($pass == 'false') {
                $arr_gate_pass = isset($arr_gate_pass[0]) ? $arr_gate_pass[0] : array();
                $this->set('arr_gate_pass', $arr_gate_pass);

                $arr_item = $this->GatePass->query("SELECT gi.* FROM gate_pass_items gi WHERE gate_pass_fkey = '$pkey' AND gi.status = 1 ORDER BY gi.gate_pass_items_pkey");
                $this->set('arr_item', $arr_item);

                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = trim($view->render('print_pass'));
                // echo $view_output;
                // Clear the output buffer
                // ob_clean();

                // // Output PDF using TCPDF

                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('GatePass.pdf', 'D');
                // $this->render('print_pass');
            }
        } catch (Exception $e) {
            debug($e);
            exit;
        }
    }


    public function deleteFromGrid($pkey = 0)
    {

        $this->autoRender = false;
        $this->DocTemplate->useDbConfig = $this->Session->read('ds');
        $this->GatePass->useDbConfig = $this->Session->read('ds');

        try {
            $save = $this->GatePass->query("UPDATE `gate_pass` AS `GatePass`
            SET
                `GatePass`.`status` = 0
    
            WHERE
                `GatePass`.`gate_pass_pkey` = $pkey;
            ");

            // debug($save);


            $response = [
                'status' => 'success',
                'message' => 'Gate pass deleted successfully.',
                'pkey' => $pkey
            ];
        } catch (Exception $e) {
            $response = [
                'status' => 'failure',
                'message' => 'Gate pass delete failed.',
                'pkey' => $pkey
            ];
        }
        $this->response->type('json');
        // debug((json_encode($response))); exit;
        echo json_encode($response);
    }

    public function generatereport($pkey = 0)
    {
        $this->autoRender = false;
        $this->printGatePass($pkey);
    }
}
