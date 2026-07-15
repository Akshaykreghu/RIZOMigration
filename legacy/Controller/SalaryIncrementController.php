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
ini_set('max_execution_time', 300);

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class SalaryIncrementController extends AppController
{

    public $name = 'SalaryIncrement';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'EmployeeSalaryStructure', 'EmployeeConfig', 'Family', 'passport', 'Promotion', 'NoticePeriod', 'qualifcations', 'history', 'EmployeeTaxTransactions', 'EmpTaxSalTrans', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC', 'EmpAlterationDetails', 'ReportCriterias', 'SalaryIncrement', 'SalaryIncrementDetails', 'ComponentIncrement', 'EditPunches', 'SalaryStructures', 'SalaryHike', 'SalaryHikeDetail', 'EmpSalaryCompUpload'); // Edited by akshay on 24-6-2025
    public $components = array('MasterdataManagement');



    public function index() {}

    public function jsons($branch = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branch != null) {
            $branch_condition = " and emp_details.branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }

        $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition = " and emp_details.branch_code='" . $cur_emp_branch . "'";
        }

        if ($q != null) {
            $q_condition = "and (first_name like '%$q%' or emp_proff.emp_company_id like '%$q%') ";
        } else {
            $q_condition = "";
        }

        $branch_array = $this->EmployeeDetails->query("select emp_details.*,emp_proff.emp_company_id from emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.status = 1 $branch_condition $q_condition ORDER BY first_name ASC ");
        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "All");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }

    public function listemployees()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $arr_requestdata = $this->request->data;
        $user_group = $this->Session->read("user_group");
        $cur_emp_key = $this->Session->read("emp_fkey");
        $company_code = strtoupper($this->Session->read('company_code'));
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $branch = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';

        $emp = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $des = isset($arr_request_data['designation']) ? $arr_request_data['designation'] : '';
        if ($user_group == 2) {
            $payroUser = $this->EmployeeProfessionalDetails->query("select payro_priv from emp_proff where emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
        }
        if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
            $conditions = array("EmployeeDetails.status in(1,2)");
        } else {

            $conditions = array("EmployeeDetails.status" => 1);
        }
        if ($branch != '') {
            $conditions[] = 'EmployeeDetails.branch_code="' . $branch . '"';
        }
        if ($emp != '') {

            $conditions[] = 'EmployeeDetails.emp_pkey="' . $emp . '"';
        }

        if ($des == '') {
            $des = '';
        } else {
            $des = 'EmployeeProfessionalDetails.designation="' . $des . '"';
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';

        $ofst = ($page - 1) * $limit;

        $fields = 'EmployeeDetails.status,Branches.branch_name,designation.desig_name, EmployeeDetails.classification, UserCredentials.avatar, designation.desig_code,emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",EmployeeDetails.first_name,EmployeeDetails.last_name) as name,EmployeeProfessionalDetails.designation,DATE_FORMAT(EmployeeProfessionalDetails.joining_date, "%d-%m-%Y") AS joining_date,mobile_no';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'designation',
                'alias' => 'designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = designation.desig_code', 'designation.status' => 1)
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branches',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code', 'Branches.status' => 1)
            ),
            array(
                'table' => 'user_credentials',
                'alias' => 'UserCredentials',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
            )
        );

        if ($user_group == '2') {
            if ($company_code == 'VGFS' || $company_code == 'VSFS') {
                $emp_pkey = $this->Session->read('emp_fkey');
                $conditions[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
                if ($company_code != 'VGFS' && $company_code != 'VSFS' && $company_code != 'DEMO') {
                    $arr_branch = $this->EmployeeDetails->query("SELECT DISTINCT ed.branch_code FROM emp_details ed WHERE ed.emp_pkey = '$emp_pkey'");
                    $branch_code = isset($arr_branch[0]['ed']['branch_code']) ? $arr_branch[0]['ed']['branch_code'] : '';
                    $conditions[] = "EmployeeDetails.branch_code = '$branch_code'";
                } else {
                    $conditions = array("EmployeeDetails.branch_code" => 1);
                }
            } elseif ($company_code == 'GLET' || $company_code == 'ABSG' || $company_code == 'DEMO') {
                $emp_pkey = $this->Session->read('emp_fkey');
                $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                if ($is_ho != 1) {
                    $conditions[] = "EmployeeDetails.branch_code = '$is_ho'";
                }
            }
        }


        if (isset($arr_request_data['emp'])) {


            $conditions[] = "(EmployeeDetails.first_name like '%" . $arr_request_data['emp'] . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $arr_request_data['emp'] . "%'  OR EmployeeDetails.last_name like '%" . $arr_request_data['emp'] . "%')";
        }

        $this->datatable["conditions"] = $conditions;
        $resp_emp = array();
        $resp_emp["rows"] = array();
        $count = $this->EmployeeDetails->find("count", array('joins' => $joins, "conditions" => $conditions));

        if ($count == 0) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = isset($cur_emp_branch_find[0]['EmployeeDetails']['branch_code']) ? $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'] : '';
            $count = $this->EmployeeDetails->find("count", array('joins' => $joins, "conditions" => $conditions));
        }

        $arr_emp = $this->EmployeeDetails->find(
            "all",
            array(
                'fields' => $fields,
                'joins' => $joins,
                "conditions" => $conditions,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );


        $baseResumeUrl = Router::url('/', true) . 'Employee/downloadResume/';

        foreach ($arr_emp as $key => $value) {

            $currentUserImage = "";

            if (isset($value['UserCredentials']['avatar']) && $value['UserCredentials']['avatar'] != "") {
                $currentUserImage = $this->webroot . $value['UserCredentials']['avatar'];
            } else {
                if (strtolower($value["EmployeeDetails"]['classification']) == "male") {
                    $currentUserImage = $this->webroot . "img/placeholdermen.jpeg";
                } else {
                    $currentUserImage = $this->webroot . "img/placeholderwomen.jpeg";
                }
            }

            $actions = array(
                "buttons" => "<a class='resumebutton' href='" . $baseResumeUrl . $value["EmployeeDetails"]['emp_pkey'] . "' ><li class='fa fa-file-pdf-o'></li></a>",
                "avatar" => "<img style='width: 28px; margin-left: 20px; border-radius: 5px; ' src='{$currentUserImage}' alt='image' />"
            );
            $resp_emp["rows"][$key] = array_merge($value["EmployeeDetails"], $value["EmployeeProfessionalDetails"], $value[0], $value["designation"], $value["Branches"], $actions);
        }
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
    }

    public function downloadempctcformat($ctcuploadtype = 0, $branch = '', $employee = '', $salStructure = 0)
    {
        $this->autoRender = FALSE;

        $ctcuploadtype = ($ctcuploadtype === 'undefined') ? 0 : $ctcuploadtype;
        $branch = ($branch === 'undefined') ? '' : $branch;
        $employee = ($employee === 'undefined') ? '' : $employee;
        $salStructure = ($salStructure === 'undefined') ? 0 : $salStructure;
        $payout = "";
        $approved_by = '';
        $startdate = "";

        $user_group = $this->Session->read('user_group');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_employee_gross.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);
        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Administrator")
            ->setLastModifiedBy("Administrator")
            ->setTitle("Employee CTC Upload Format")
            ->setSubject("Employee CTC Upload")
            ->setDescription("Template with Instructions");

        // Sheet 1 - Actual Data Template
        $objPHPExcel->setActiveSheetIndex(0);
        $worksheet = $objPHPExcel->getActiveSheet();

        if ($ctcuploadtype == 1) {
            $worksheet->setCellValueByColumnAndRow(0, 1, "Employee ID");
            $worksheet->setCellValueByColumnAndRow(1, 1, "Employee Name");
            $worksheet->setCellValueByColumnAndRow(2, 1, "Employee Company ID");
            $worksheet->setCellValueByColumnAndRow(3, 1, "Monthly Gross Salary");
            $worksheet->setCellValueByColumnAndRow(4, 1, "Start Date Effective(dd-mm-yyyy)");
            $worksheet->getColumnDimension('A')->setWidth(14);
            $worksheet->getColumnDimension('B')->setWidth(20);
            $worksheet->getColumnDimension('C')->setWidth(25);
            $worksheet->getColumnDimension('D')->setWidth(27);
            $worksheet->getColumnDimension('E')->setWidth(27);
        } else {
            $worksheet->setCellValueByColumnAndRow(0, 1, "Employee ID");
            $worksheet->setCellValueByColumnAndRow(1, 1, "Employee Name");
            $worksheet->setCellValueByColumnAndRow(2, 1, "Employee Company ID");
            $worksheet->setCellValueByColumnAndRow(3, 1, "Monthly Gross Salary");
            $worksheet->setCellValueByColumnAndRow(4, 1, "New Monthly Gross Salary");
            $worksheet->setCellValueByColumnAndRow(5, 1, "Current Salary Structure (Code)"); // Edited by Akshay on 15-10-2025
            $worksheet->setCellValueByColumnAndRow(6, 1, "New Salary Structure (Code)"); // Edited by Akshay on 14-10-2025
            $worksheet->setCellValueByColumnAndRow(7, 1, "Start Date Effective(dd-mm-yyyy)");
            $worksheet->setCellValueByColumnAndRow(8, 1, "Next Increment Date(dd-mm-yyyy)");
            $worksheet->setCellValueByColumnAndRow(9, 1, "Payout Month(dd-mm-yyyy)");

            $worksheet->getColumnDimension('A')->setWidth(16);
            $worksheet->getColumnDimension('B')->setWidth(20);
            $worksheet->getColumnDimension('C')->setWidth(20);
            $worksheet->getColumnDimension('D')->setWidth(25);
            $worksheet->getColumnDimension('E')->setWidth(27);
            $worksheet->getColumnDimension('F')->setWidth(25); // Edited by Akshay on 14-10-2025
            $worksheet->getColumnDimension('G')->setWidth(27);
            $worksheet->getColumnDimension('H')->setWidth(30);
            $worksheet->getColumnDimension('I')->setWidth(27);
            $worksheet->getColumnDimension('J')->setWidth(27); // Edited by Akshay on 15-10-2025
        }

        // Bold header row
        foreach (range('A', 'J') as $col) {
            $worksheet->getStyle($col . '1')->getFont()->setBold(true);
        }

        // Query preparation
        $cond = '';
        if ($employee == '0') {
            $cond .= " AND  EmployeeDetails.emp_pkey = EmployeeDetails.emp_pkey ";
        } else if (isset($employee)) {
            $cond .= " AND  EmployeeDetails.emp_pkey = if('$employee' in(null,''),EmployeeDetails.emp_pkey, '$employee' )";
        }
        if ($branch == '0') {
            $cond .= " AND  EmployeeDetails.branch_code= EmployeeDetails.branch_code ";
        } else if (isset($branch)) {
            $cond .= " AND  EmployeeDetails.branch_code= if('$branch' in(null,''),EmployeeDetails.Branch_code,'$branch') ";
        }

        if ($salStructure != 0) {
            $cond .= " AND  ep.structure_id= '$salStructure' ";
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($user_group == 2) {
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch from emp_proff where emp_proff.emp_fkey ='$cur_emp_key'");
            if (!empty($payroUser) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
                $branch = $payroUser[0]['emp_proff']['emp_branch'];
                $cond .= " AND  EmployeeDetails.branch_code= if('$branch' in(null,''),EmployeeDetails.Branch_code,'$branch') ";
            }
        }

        // Query employee data
        if ($ctcuploadtype == 2) {
            $arr_empdetails = $this->EmployeeDetails->query("
            SELECT UserCredentials.user_id, EmployeeInfo.EmpName,EmployeeInfo.employee_id,ect.emp_anual_ctc,au.start_date_effective , au.next_increment_date, ep.structure_id, ep.emp_type -- Edited by Akshay on 27-3-2026
            FROM emp_details AS EmployeeDetails
            INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey)
            LEFT JOIN emp_ctc_transaction AS ect ON (EmployeeDetails.emp_pkey = ect.emp_fkey)
            INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey)
            INNER JOIN emp_proff AS ep ON (ep.emp_fkey = UserCredentials.emp_fkey) -- Edited by Akshay on 15-10-2025
            LEFT JOIN emp_ctc_upload au ON (EmployeeDetails.emp_pkey = au.emp_fkey AND au.emp_ctc_upload_pkey IN (
                SELECT ctc_upload_fkey FROM emp_ctc_transaction WHERE end_date_effective IS NULL OR end_date_effective >= CURRENT_DATE
            ))
            WHERE EmployeeDetails.status = ' 1 ' AND ect.end_date_effective IS NULL $cond
            GROUP BY UserCredentials.user_id
        ");
        } else {
            $arr_empdetails = $this->EmployeeDetails->query("
            SELECT UserCredentials.user_id,EmployeeInfo.employee_id, EmployeeInfo.EmpName, ep.emp_type -- Edited by Akshay on 27-3-2026
            FROM emp_details AS EmployeeDetails
            INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey)
            INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey)
            INNER JOIN emp_proff AS ep ON (ep.emp_fkey = UserCredentials.emp_fkey) -- Edited by Akshay on 15-10-2025
            WHERE EmployeeDetails.status = ' 1 ' $cond
            GROUP BY UserCredentials.user_id
        ");
        }

        // Edited by Akshay on 14-10-2025
        $arr_salary_structure = $this->EmployeeDetails->query("SELECT structure_name,structure_id
                                                                    FROM `salary_structure`
                                                                    WHERE `structure_active` = '1';");
        // End

        // Fill data
        $rowindex = 2;
        $columnindex = 0;
        foreach ($arr_empdetails as $value) {
            $userid = $value['UserCredentials']['user_id'];
            $empname = $value['EmployeeInfo']['EmpName'];
            $employee_company_id = $value['EmployeeInfo']['employee_id'];
            $current_sal_structure = $value['ep']['structure_id']; // Edited by Akshay on 15-10-2025
                        $emp_type = strtolower(trim($value['ep']['emp_type'])); // Edited by Akshay on 27-3-2026
            $worksheet->setCellValueByColumnAndRow($columnindex, $rowindex, $userid);
            $worksheet->setCellValueByColumnAndRow($columnindex + 1, $rowindex, $empname);
            $worksheet->setCellValueByColumnAndRow($columnindex + 2, $rowindex, $employee_company_id);

            if ($ctcuploadtype == 2) {
                $ctc = isset($value['ect']['emp_anual_ctc']) ? $value['ect']['emp_anual_ctc'] : 0;
                                $monthly_gross = ($emp_type == 'daily wages' || $emp_type == 'hourly wages') ? $ctc :  $ctc / 12; // Edited by Akshay on 27-3-2026
                $incrementdate = isset($value['au']['next_increment_date']) && $value['au']['next_increment_date']
                    ? (DateTime::createFromFormat('Y-m-d', $value['au']['next_increment_date']) !== false
                        ? DateTime::createFromFormat('Y-m-d', $value['au']['next_increment_date'])->format('d-m-Y')
                        : $value['au']['next_increment_date'])
                    : '';

                $worksheet->setCellValueByColumnAndRow($columnindex + 3, $rowindex, $monthly_gross);
                $worksheet->setCellValueByColumnAndRow($columnindex + 4, $rowindex, $monthly_gross); // New value has current gross amount as default
                $worksheet->setCellValueByColumnAndRow($columnindex + 5, $rowindex, $current_sal_structure);
                $worksheet->setCellValueByColumnAndRow($columnindex + 6, $rowindex, '');
                $worksheet->setCellValueByColumnAndRow($columnindex + 7, $rowindex, '');
                $worksheet->setCellValueByColumnAndRow($columnindex + 8, $rowindex, ''); // Edited by Akshay on 14-10-2025

                // Edited by Akshay on 22-10-2025
                // Get the cell
                $cell = $worksheet->getCellByColumnAndRow($columnindex + 6, $rowindex);

                // Create data validation for tooltip
                $validation = $cell->getDataValidation();
                $validation->setType(PHPExcel_Cell_DataValidation::TYPE_CUSTOM); // TYPE_CUSTOM just allows any value
                $validation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true); // set false if mandatory
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setPromptTitle('Tip'); // This shows as the title
                $validation->setPrompt('Refer instructions sheet for salary structure code.'); // This shows as the message

                // Get the cell
                $cell = $worksheet->getCellByColumnAndRow($columnindex + 7, $rowindex);

                $validation = $cell->getDataValidation();
                $validation->setType(PHPExcel_Cell_DataValidation::TYPE_CUSTOM); // TYPE_CUSTOM just allows any value
                $validation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true); // set false if mandatory
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setPromptTitle('Tip'); // This shows as the title
                $validation->setPrompt('Mandatory for employees who already has salary structure.'); // This shows as the message

                // Get the cell
                $cell = $worksheet->getCellByColumnAndRow($columnindex + 9, $rowindex);

                $validation = $cell->getDataValidation();
                $validation->setType(PHPExcel_Cell_DataValidation::TYPE_CUSTOM); // TYPE_CUSTOM just allows any value
                $validation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true); // set false if mandatory
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setPromptTitle('Tip'); // This shows as the title
                $validation->setPrompt('Mandatory for employees who already has salary structure.'); // This shows as the message
                // End
            }

            $rowindex++;
        }

        $worksheet->setTitle('Employee CTC Data');


        // ======= Sheet 2: Instructions =========
        $instructionSheet = new PHPExcel_Worksheet($objPHPExcel, 'Instructions');
        $objPHPExcel->addSheet($instructionSheet, 1);

        $instructionSheet->setCellValue('A1', 'Instructions for Filling Employee CTC Upload Template');
        $instructionSheet->mergeCells('A1:E1');
        $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $instructions = [
            ['Column Name', 'Description', 'Mandatory', 'Format / Example', 'Notes'],
            ['Monthly Gross Salary', 'Current monthly gross', 'Yes', 'e.g., 35000', 'Numeric only'],
            ['New Monthly Gross Salary', 'Updated gross salary', 'Optional', 'e.g., 40000', 'Leave blank if no change'],
            ['Start Date Effective', 'Date new salary is effective from', 'Yes', 'dd-mm-yyyy', 'e.g., 01-08-2025'],
            ['Next Increment Date', 'Planned next increment date', 'Optional', 'dd-mm-yyyy', 'e.g., 01-04-2026'],
            ['Payout Month', 'Salary will be paid in this month', 'Optional', 'dd-mm-yyyy', 'e.g., 01-08-2025 (1st of month)'],
            ['New Salary Structure (Code)', 'Change to new salary structure', 'Optional', 'e.g., 10', 'Numeric Only'],  // Edited by Akshay on 14-10-2025
        ];

        $row = 3;
        foreach ($instructions as $instRow) {
            $col = 'A';
            foreach ($instRow as $cell) {
                $instructionSheet->setCellValue($col . $row, $cell);
                $col++;
            }
            $row++;
        }

        // Edited by Akshay on 14-10-2025
        $row++;
        $instructionSheet->setCellValue('A' . $row, 'Salary Structure');
        $instructionSheet->setCellValue('B' . $row, 'Code');
        $instructionSheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        foreach ($arr_salary_structure as $salary_structure) {
            $row++;
            $structure_name = $salary_structure['salary_structure']['structure_name'];
            $structure_id = $salary_structure['salary_structure']['structure_id'];
            $instructionSheet->setCellValue('A' . $row, $structure_name);
            $instructionSheet->setCellValue('B' . $row, $structure_id);

            // Align both A and B cells in this row to the left
            $instructionSheet->getStyle('A' . $row . ':B' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }
        // End

        foreach (range('A', 'E') as $columnID) {
            $instructionSheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $objPHPExcel->setActiveSheetIndex(0); // Keep first sheet active
        // Output
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }


    // Edited by Akshay on 26-6-2025
    public function downloadempctcformatItem($employee = '', $branch = '', $structure = '')
    {
        $this->autoRender = FALSE;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbconfig = $this->Session->read('ds');

        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_component_upload.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";
        $dqlToFetchComponentHeads = "select item as salary_head_item_desc, salary_head_item_pkey from salary_head_items 
            where salary_head_items.head_fkey = 1 and salary_head_items.item_part = 'Direct' and status = 1
            order by salary_head_item_order1 ASC";

        //        $dqlToFetchComponentHeads = "select DISTINCT(salary_head_item_desc) as salary_head_item_desc from emp_details ed "
        //                . "join emp_salary_structure as ectc on(ed.emp_pkey = ectc.emp_fkey) "
        //                . "where ectc.head_operator='Addition' and ectc.item_part='Direct' and end_date_effective is null  ORDER BY ed.emp_pkey,ed.first_name ASC; ";
        $arr_salary_components = $this->EmployeeCTC->query($dqlToFetchComponentHeads);
        $arr_sal_head_items = array();
        foreach ($arr_salary_components as $key => $value) {
            if (isset($value['salary_head_items']['salary_head_item_pkey'])) {
                $arr_sal_head_items[] = $value['salary_head_items']['salary_head_item_pkey'];
            }
        }

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);
        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet(1);


        $dqlToFetchComponentHeads = "select item as salary_head_item_desc, salary_head_item_pkey from salary_head_items 
            where salary_head_items.head_fkey = 1 and salary_head_items.item_part = 'Direct' and status = 1
            order by salary_head_item_order1 ASC";

        //        $dqlToFetchComponentHeads = "select DISTINCT(salary_head_item_desc) as salary_head_item_desc from emp_details ed "
        //                . "join emp_salary_structure as ectc on(ed.emp_pkey = ectc.emp_fkey) "
        //                . "where ectc.head_operator='Addition' and ectc.item_part='Direct' and end_date_effective is null  ORDER BY ed.emp_pkey,ed.first_name ASC; ";
        $arr_salary_components = $this->EmployeeCTC->query($dqlToFetchComponentHeads);

        // Edited by Akshay on 14-10-2025
        $arr_salary_structure = $this->EmployeeCTC->query("SELECT structure_name,structure_id
                                                                    FROM `salary_structure`
                                                                    WHERE `structure_active` = '1';");
        // End


        $worksheet->setCellValueByColumnAndRow(0, 1, "Employee ID");
        $worksheet->setCellValueByColumnAndRow(1, 1, "Company ID");
        $worksheet->setCellValueByColumnAndRow(2, 1, "Employee Name");
        $worksheet->setCellValueByColumnAndRow(3, 1, "Current Salary Structure (Code)"); // Edited by Akshay on 15-10-2025
        $worksheet->setCellValueByColumnAndRow(4, 1, "New Salary Structure (Code)"); // Edited by Akshay on 14-10-2025

        $worksheet->setCellValueByColumnAndRow(5, 1, "Start Date Effective(dd-mm-yyyy)");
        $worksheet->setCellValueByColumnAndRow(6, 1, "Next Increment Date(dd-mm-yyyy)"); //edited by anukrishnan_03-02-2025
        $worksheet->setCellValueByColumnAndRow(7, 1, "Payout Month(dd-mm-yyyy)");
        // $worksheet->setCellValueByColumnAndRow(6, 1, "Remarks");

        $arr_sal_head_items = array();
        foreach ($arr_salary_components as $key => $value) {
            if (isset($value['salary_head_items']['salary_head_item_pkey'])) {
                $arr_sal_head_items[] = $value['salary_head_items']['salary_head_item_pkey'];
            }
            $worksheet->setCellValueByColumnAndRow($key + 8, 1, trim(isset($value['salary_head_items']['salary_head_item_desc']) ? $value['salary_head_items']['salary_head_item_desc'] : ''));
        }

        // die();


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        // $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

        for ($col = 'D'; $col !== 'AX'; $col++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setWidth(27);
            $objPHPExcel->getActiveSheet()->getStyle($col . '1')->getFont()->setBold(true);
        }

        // $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setVisible(false);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);

        // $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        // $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);

        $headerRow = array("Employee Id", "Company Id", "Employee Name", "components", "Amount");


        if ($branch != '0') {
            $conditions = " and ed.branch_code = '$branch'";
        } else {
            $conditions = " and ed.branch_code = ed.branch_code";
        }

        if ($employee != "null" && $employee != 0) {
            $conditions .= " and ed.emp_pkey ='$employee'";
        }

        if ($employee == "null" && $employee != 0) {
            $conditions .= " and ed.emp_pkey = ed.emp_pkey";
        }

        if ($employee == 0) {
            $conditions .= " and ed.emp_pkey = ed.emp_pkey";
        }

        // if ($structure != 0){

        //     $conditions .= " and ss.structure_id = '$structure'";
        // }
        // if($structure == 0){
        //     $conditions .= " and ss.structure_id = ss.structure_id ";
        // }
        $arr_att = $this->EmployeeCTC->query("select CONCAT(COALESCE(ed.first_name, ''), ' ', COALESCE(ed.last_name, '')) AS emp_name,ed.emp_pkey,ed.emp_id,emp_proff.emp_company_id, emp_proff.structure_id from emp_details ed left join emp_proff on (ed.emp_pkey = emp_proff.emp_fkey)  WHERE status = 1 $conditions ORDER BY ed.emp_pkey,ed.first_name ASC");


        $this->set("arr_att", $arr_att);
        $data = array();
        $counts = count($arr_att);
        $resp_att = array();
        $resp_att["rows"] = array();

        if (count($arr_att) > 0) {

            $rowindex = 2;

            foreach ($arr_att as $key => $value) {
                $emp_id = isset($value['ed']['emp_id']) ? $value['ed']['emp_id'] : '';
                $emp_name = !empty($value['0']['emp_name'])
                    ? $value['0']['emp_name']
                    : (!empty($value['ed']['emp_name'])
                        ? $value['ed']['emp_name']
                        : '');
                $comp_name = isset($value['emp_proff']['emp_company_id']) ? $value['emp_proff']['emp_company_id'] : '';
                $current_salary_structure = isset($value['emp_proff']['structure_id']) ? $value['emp_proff']['structure_id'] : ''; // Edited by Akshay on 15-10-2025

                // foreach ($columns as $column) {
                $columnindex = 0;
                $objPHPExcel->getActiveSheet()->setCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex, $emp_id);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex, $comp_name);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex, $emp_name);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex, $current_salary_structure); // Edited by Akshay on 13-5-2025

                // Edited by Akshay on 22-10-2025
                // Get the cell
                $nextCell = PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex;
                $objPHPExcel->getActiveSheet()->setCellValue($nextCell, ''); // You can put a default value if needed

                // Create data validation for tooltip
                $validation = $objPHPExcel->getActiveSheet()->getCell($nextCell)->getDataValidation();
                $validation->setType(PHPExcel_Cell_DataValidation::TYPE_CUSTOM); // Any value allowed
                $validation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true); // false if mandatory
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setPromptTitle('Tip'); // This shows as the title
                $validation->setPrompt('Refer instructions sheet for salary structure code.'); // This shows as the message

                // Get the cell
                $nextCell = PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex;
                $objPHPExcel->getActiveSheet()->setCellValue($nextCell, ''); // You can put a default value if needed

                // Create data validation for tooltip
                $validation = $objPHPExcel->getActiveSheet()->getCell($nextCell)->getDataValidation();
                $validation->setType(PHPExcel_Cell_DataValidation::TYPE_CUSTOM); // Any value allowed
                $validation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true); // false if mandatory
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setPromptTitle('Tip'); // This shows as the title
                $validation->setPrompt('Mandatory for employees who already has salary structure.'); // This shows as the message

                // Get the cell
                $nextCell = PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex;
                $objPHPExcel->getActiveSheet()->setCellValue($nextCell, ''); // You can put a default value if needed

                // Create data validation for tooltip
                $validation = $objPHPExcel->getActiveSheet()->getCell($nextCell)->getDataValidation();
                $validation->setType(PHPExcel_Cell_DataValidation::TYPE_CUSTOM); // Any value allowed
                $validation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true); // false if mandatory
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setPromptTitle('Tip'); // This shows as the title
                $validation->setPrompt('Mandatory for employees who already has salary structure.'); // This shows as the message
                // End

                $columnindex++;
                if ($columnindex >= 8) {
                    $emp_pkey = isset($value['ed']['emp_pkey']) ? $value['ed']['emp_pkey'] : '';
                    foreach ($arr_sal_head_items as $item_key) {
                        $arr_structure_det_value = $this->EmployeeCTC->query("SELECT structure_det_value FROM `emp_salary_structure` WHERE `emp_fkey` = '$emp_pkey' AND `end_date_effective` IS NULL AND `salary_head_item_fkey` = '$item_key'");
                        $structure_det_value = isset($arr_structure_det_value[0]['emp_salary_structure']['structure_det_value']) ? $arr_structure_det_value[0]['emp_salary_structure']['structure_det_value'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex++) . $rowindex, $structure_det_value);
                    }
                }

                $rowindex++;
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('Salary Components Upload ');


        // ======= Sheet 2: Instructions =========
        $instructionSheet = new PHPExcel_Worksheet($objPHPExcel, 'Instructions');
        $objPHPExcel->addSheet($instructionSheet, 1);

        $instructionSheet->setCellValue('A1', 'Instructions for Filling Employee CTC Upload Template');
        $instructionSheet->mergeCells('A1:E1');
        $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $instructions = [
            ['Column Name', 'Description', 'Mandatory', 'Format / Example', 'Notes'],
            ['Monthly Gross Salary', 'Current monthly gross', 'Yes', 'e.g., 35000', 'Numeric only'],
            ['New Monthly Gross Salary', 'Updated gross salary', 'Optional', 'e.g., 40000', 'Leave blank if no change'],
            ['Start Date Effective', 'Date new salary is effective from', 'Yes', 'dd-mm-yyyy', 'e.g., 01-08-2025'],
            ['Next Increment Date', 'Planned next increment date', 'Optional', 'dd-mm-yyyy', 'e.g., 01-04-2026'],
            ['Payout Month', 'Salary will be paid in this month', 'Yes', 'dd-mm-yyyy', 'e.g., 01-08-2025 (1st of month)'],
            ['New Salary Structure (Code)', 'Change to new salary structure', 'Optional', 'e.g., 10', 'Numeric Only'],  // Edited by Akshay on 14-10-2025
        ];

        $row = 3;
        foreach ($instructions as $instRow) {
            $col = 'A';
            foreach ($instRow as $cell) {
                $instructionSheet->setCellValue($col . $row, $cell);
                // Make bold only for row 3
                if ($row == 3) {
                    $instructionSheet->getStyle($col . $row)
                        ->getFont()
                        ->setBold(true);
                }
                $col++;
            }
            $row++;
        }

        // Edited by Akshay on 14-10-2025
        $row++;
        $instructionSheet->setCellValue('A' . $row, 'Salary Structure');
        $instructionSheet->setCellValue('B' . $row, 'Code');
        $instructionSheet->mergeCells('C' . $row . ':H' . $row);
        $instructionSheet->setCellValue('C' . $row, 'Items');
        $instructionSheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        foreach ($arr_salary_structure as $salary_structure) {
            $row++;
            $structure_name = $salary_structure['salary_structure']['structure_name'];
            $structure_id = $salary_structure['salary_structure']['structure_id'];
            $instructionSheet->setCellValue('A' . $row, $structure_name);
            $instructionSheet->setCellValue('B' . $row, $structure_id);

            // Edited by Akshay on 17-10-2025
            $arr_items = $this->EmployeeCTC->query("SELECT item FROM salary_head_items WHERE salary_head_item_pkey IN 
                                        (SELECT salary_head_item_fkey FROM salary_structure_details WHERE `structure_id` = '$structure_id' AND structure_det_value != 0)
                                        AND `head_fkey` = '1' AND `item_part` = 'DIRECT';");

            // Extract item names into an array
            $itemNames = [];
            foreach ($arr_items as $itemRow) {
                $itemNames[] = isset($itemRow['salary_head_items']['item']) ? trim($itemRow['salary_head_items']['item']) : '';
            }

            // Join items with commas and add to column C
            $instructionSheet->mergeCells('C' . $row . ':H' . $row);
            $instructionSheet->setCellValue('C' . $row, implode(', ', $itemNames));
            // End

            // Align both A and B cells in this row to the left
            $instructionSheet->getStyle('A' . $row . ':B' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }
        // End

        foreach (range('A', 'E') as $columnID) {
            $instructionSheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $objPHPExcel->setActiveSheetIndex(0); // Keep first sheet active

        // Output
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    public function uploadandsaveempctc($ctcuploadtype = 0)
    {
        $this->autoRender = FALSE;
        $this->SalaryHike->useDbConfig = $this->Session->read('ds');
        $this->SalaryHikeDetail->useDbConfig = $this->Session->read('ds');

        // Edited by Akshay on 23-10-2025
        $arr_salary_structure = $this->SalaryHikeDetail->query("SELECT structure_id
                                                                    FROM `salary_structure`
                                                                    WHERE `structure_active` = '1';");
        $allowed_structures = array_map(function ($row) {
            return $row['salary_structure']['structure_id'];
        }, $arr_salary_structure);
        // End

        if ($ctcuploadtype != 0) {
            //  echo "hi" ;
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_employee_gross' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
            // $approved_by = $_POST['approved_by']; // Edited by Akshay on 5-4-2025
            if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objReader = new PHPExcel_Reader_Excel2007();
                $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir
                $sheet = $objPHPExcel->getSheet(0); // Force to Sheet 1

                $lastColumn = $sheet->getHighestColumn();
                $lastColumn++;
                $highestRowIndex = $sheet->getHighestRow();
                $arrayempdata = array();
                $mandatory_fields_warning = FALSE;
                if ($highestRowIndex > 1) {
                    //edited by megha on 06_08_2019 integration- revision upload condition start
                    if ($lastColumn == 'F') {
                        $ctcuploadtype = 1;
                    } else {
                        $ctcuploadtype = 2;
                    }
                    // end integration- revision upload condition
                    //atleast one employee records found
                    $index = 0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row == 1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('Employee ID', 'Employee Name', 'Employee Company ID', 'Monthly Gross Salary', 'New Monthly Gross Salary', 'Current Salary Structure (Code)', 'New Salary Structure (Code)', 'Start Date Effective(dd-mm-yyyy)', 'Next Increment Date(dd-mm-yyyy)', 'Payout Month(dd-mm-yyyy)');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $sheet->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                            if ($array_mandatory_columns != array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')) {
                                unlink($targetpath);
                                echo json_encode(array('success' => 0, 'msg' => 'Please check if the uploaded document is in correct format.'));
                                exit;
                            }
                        } else {
                            // $array_mandatory_columns = array('A', 'B', 'C', 'D', 'G', 'H', 'I');
                            $array_mandatory_columns = array('A', 'B', 'C', 'D');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $mand_value = $sheet->getCell($col . $row)->getValue();
                                $mand_value = isset($mand_value) ? $mand_value : '';
                                if (in_array($col, $array_mandatory_columns) && $mand_value === '') { // Edited by Akshay on 14-10-2025
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                $value = $sheet->getCell($col . $row)->getValue();

                                // Edited by Akshay on 23-10-2025
                                if ($col == 'G') {
                                    $colFValue = $sheet->getCell('F' . $row)->getValue();

                                    // If D is blank → E must have a valid structure

                                    if ($colFValue == '' && ($value == '' || !in_array($value, $allowed_structures))) {
                                        $mandatory_fields_warning = true;
                                        break 2;
                                    }

                                    // If D has a value → E can be blank or valid
                                    if ($colFValue != '' && $value != '' && !in_array($value, $allowed_structures)) {
                                        $mandatory_fields_warning = true;
                                        break 2;
                                    }
                                }
                                // End

                                $arrayempdata[$index][$sheet->getCell($col . "1")->getValue()] = $value;
                            }
                            $index++;
                        }
                    }
                    if ($mandatory_fields_warning) {
                        //Exit if mandatory fields not entered
                        unlink($targetpath);
                        echo json_encode(array('success' => 0, 'msg' => 'Please ensure all mandatory fields are filled and have values in the correct format.'));
                        exit;
                    } else {

                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));

                        //edited by megha on 10/08/2019 annual gross salary amount empty excel msg 1
                        $count = 0;

                        foreach ($arrayempdata as $key => $row) {
                            // Edited by Akshay on 27-3-2026
                            $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            $arr_emp_type = $this->SalaryHikeDetail->query("SELECT ep.emp_type FROM emp_details ed
                                                            LEFT JOIN emp_proff ep ON ep.emp_fkey = ed.emp_pkey
                                                            WHERE ed.emp_id = '$user_id'
                                                            ;");
                            $emp_type = isset($arr_emp_type[0]['ep']['emp_type']) ? strtoupper(trim($arr_emp_type[0]['ep']['emp_type'])) : '';
                            // End
                            // $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            if ($ctcuploadtype == 1) {
                                if ($row['Monthly Gross Salary'] == '0') {
                                    //  continue;
                                }
                                $emp_anual_ctc = isset($row['Monthly Gross Salary']) ? (($emp_type == 'DAILY WAGES' || $emp_type == 'HOURLY WAGES') ? $row['Monthly Gross Salary'] : ($row['Monthly Gross Salary']) * 12) : '';
                                $new_salary = isset($row['New Monthly Gross Salary']) ? $row['New Monthly Gross Salary'] : '';
                                $current_salary = isset($row['Monthly Gross Salary']) ? ($row['Monthly Gross Salary']) : '';
                            } else {
                                if ($row['New Monthly Gross Salary'] == '0') {
                                    // continue;
                                }
                                $emp_anual_ctc = isset($row['New Monthly Gross Salary']) ? (($emp_type == 'DAILY WAGES' || $emp_type == 'HOURLY WAGES') ? $row['New Monthly Gross Salary'] : $row['New Monthly Gross Salary'] * 12)  : '';
                                $new_salary = isset($row['New Monthly Gross Salary']) ? $row['New Monthly Gross Salary'] : '';
                                $current_salary = isset($row['Monthly Gross Salary']) ? ($row['Monthly Gross Salary']) : '';

                                $arrear = isset($row['Arrear Need']) ? $row['Arrear Need'] : '';
                            }
                            // debug($row['Payout Month(dd-mm-yyyy)']);

                            if (!empty($row['Payout Month(dd-mm-yyyy)']) && is_numeric($row['Payout Month(dd-mm-yyyy)'])) {
                                // Step 1: Convert Excel serial to timestamp
                                $excelDate = ($row['Payout Month(dd-mm-yyyy)'] - 25569) * 86400;
                                // Step 2: Convert to d-m-Y and then to Y-m-1
                                $d = DateTime::createFromFormat('d-m-Y', date('d-m-Y', $excelDate));
                                $row['Payout Month(dd-mm-yyyy)'] = ($d !== false) ? $d->format('Y-m-') . '1' : '0000-00-00';
                            } else {
                                $d = DateTime::createFromFormat('d-m-Y', $row['Payout Month(dd-mm-yyyy)']);
                                $row['Payout Month(dd-mm-yyyy)'] = ($d !== false) ? $d->format('Y-m-') . '1' : '0000-00-00';
                            }


                            $payout = isset($row['Payout Month(dd-mm-yyyy)']) ? $row['Payout Month(dd-mm-yyyy)'] : '';
                            // debug($payout);

                            $date = '';
                            $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            if ($user_id == '') {
                                continue;
                            }
                            //debug($emp_anual_ctc);
                            //fetch emp_fkey using user_id
                            $arr_usercredentials = $this->UserCredentials->find('first', array(
                                'fields' => 'emp_fkey',
                                'conditions' => array(
                                    'user_id' => $user_id
                                )
                            ));
                            $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';

                            $arr_empctc_data = array();
                            $arr_empctc_data['emp_ctc_upload_pkey'] = 0;
                            $arr_empctc_data['status'] = 1;
                            $arr_empctc_data['emp_fkey'] = $emp_fkey;
                            $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
                            $arr_empctc_data['creation_date'] = date('Y-m-d');
                            $arr_empctc_data['emp_anual_ctc'] = $emp_anual_ctc;
                            // Edited by Akshay on 5-4-2025
                            $arr_empctc_data['arrear_salary'] = $arrear;
                            $arr_empctc_data['pay_out_month'] = $payout;
                            // $arr_empctc_data['approved_by'] = $approved_by;
                            // End

                            //edited by megha on 10/08/2019 annual gross salary amount empty excel msg 2
                            if ($emp_anual_ctc != '') {
                                $count++;
                            }


                            // End

                            $row['Next Increment Date(dd-mm-yyyy)'] = DateTime::createFromFormat('d-m-Y', $row['Next Increment Date(dd-mm-yyyy)']) !== false ? DateTime::createFromFormat('d-m-Y', $row['Next Increment Date(dd-mm-yyyy)'])->format('Y-m-d') : $row['Next Increment Date(dd-mm-yyyy)'];

                            $isValidDate = DateTime::createFromFormat('Y-m-d', $row['Next Increment Date(dd-mm-yyyy)']) !== false; // Edited by Akshay on 4-4-2025

                            // Edited by Akshay on 5-4-2025
                            if (!$isValidDate) {
                                $date = $row['Next Increment Date(dd-mm-yyyy)'];
                                if (is_numeric($date)) {
                                    $base = new DateTime('1899-12-30');
                                    $formattedDate = $base->modify("+{$date} days")->format('Y-m-d');
                                    $row['Next Increment Date(dd-mm-yyyy)'] = $formattedDate;
                                }
                                $isValidDate = DateTime::createFromFormat('Y-m-d', $row['Next Increment Date(dd-mm-yyyy)']) !== false;
                            }
                            // End

                            //edited by anukrishnan_03-02-2025 open
                            if (isset($row['Next Increment Date(dd-mm-yyyy)']) && $isValidDate) { // Edited by Akshay on 4-4-2025
                                $next_increment_date = $row['Next Increment Date(dd-mm-yyyy)'];  // Edited by Akshay on 4-4-2025
                            } else {
                                $next_increment_date = ''; // Handle invalid or empty values
                            }

                            if (isset($row['Payout Month(dd-mm-yyyy)']) && !empty($row['Payout Month(dd-mm-yyyy)'])) {
                                $date = DateTime::createFromFormat('Y-m-d', $row['Payout Month(dd-mm-yyyy)']);
                                $payout_month = ($date && $date->format('Y-m-d') === $row['Payout Month(dd-mm-yyyy)'])
                                    ? $date->format('Y-m-01')
                                    : '0000-00-00';
                            } else {
                                $payout_month = '0000-00-00';
                            }

                            $arr_empctc_data['next_increment_date'] = $next_increment_date;
                            $query = $this->EmployeeCTC->query("
                                SELECT ei.branch, ei.branch_code, ei.designation, ei.department
                                FROM employee_info ei
                                JOIN emp_ctc_upload ectc ON ei.emp_pkey = ectc.emp_fkey
                                WHERE ectc.emp_fkey = $emp_fkey
                            ");
                            $branch       = isset($query[0]['ei']['branch'])        ? $query[0]['ei']['branch']        : (isset($query[0][0]['branch'])        ? $query[0][0]['branch']        : '');
                            $branch_code  = isset($query[0]['ei']['branch_code'])   ? $query[0]['ei']['branch_code']   : (isset($query[0][0]['branch_code'])   ? $query[0][0]['branch_code']   : '');
                            $designation  = isset($query[0]['ei']['designation'])   ? $query[0]['ei']['designation']   : (isset($query[0][0]['designation'])   ? $query[0][0]['designation']   : '');
                            $department   = isset($query[0]['ei']['department'])    ? $query[0]['ei']['department']    : (isset($query[0][0]['department'])    ? $query[0][0]['department']    : '');


                            $arr_empctc_data['branch'] = $branch;
                            $arr_empctc_data['designation'] = $designation;
                            $arr_empctc_data['department'] = $department;

                            // Edited by Akshay on 26-6-2025
                            $arr_empctc_data['item'] = 'N';
                            $arr_empctc_data['remarks'] = $_POST['remark'];
                            // End

                            // Edited by Akshay on 14-10-2025
                            if (isset($row['New Salary Structure (Code)']) && trim($row['New Salary Structure (Code)']) != '') {
                                $arr_empctc_data['structure_change'] = 'Y';
                            }
                            // End
                            // debug($arr_empctc_data);exit;
                            try {

                                $result1 = $this->SalaryHike->save($arr_empctc_data);
                                $salary_hike_fkey = $this->SalaryHike->id;

                                // Edited by Akshay on 14-10-2025
                                if (isset($row['New Salary Structure (Code)']) && trim($row['New Salary Structure (Code)']) != '') {
                                    $structure_id = $row['New Salary Structure (Code)'];
                                } else {
                                    $arr_structure_id = $this->SalaryHike->query("SELECT policy_id FROM `emp_config` WHERE `emp_fkey` = '$emp_fkey' AND `status` = '1' AND `type` = 'SALARY'");
                                    $structure_id = isset($arr_structure_id[0]['emp_config']['policy_id']) ? $arr_structure_id[0]['emp_config']['policy_id'] : 0;
                                }
                                // End

                                // Salary hike details
                                if (!empty($row['Start Date Effective(dd-mm-yyyy)']) && is_numeric($row['Start Date Effective(dd-mm-yyyy)'])) {
                                    $row['Start Date Effective(dd-mm-yyyy)'] = date('Y-m-d', ($row['Start Date Effective(dd-mm-yyyy)'] - 25569) * 86400);
                                } else {
                                    $d = DateTime::createFromFormat('d-m-Y', $row['Start Date Effective(dd-mm-yyyy)']);

                                    // Edited by Akshay on 4-11-2025
                                    $arr_structure = $this->SalaryHike->query("SELECT structure_id, joining_date FROM emp_proff WHERE emp_fkey = '$emp_fkey'");
                                    $structure = isset($arr_structure[0]['emp_proff']['structure_id']) ? $arr_structure[0]['emp_proff']['structure_id'] : '';
                                    if ($structure != '') {
                                        $row['Start Date Effective(dd-mm-yyyy)'] = date('Y-m-d');
                                    } else {
                                        $joining_date = isset($arr_structure[0]['emp_proff']['joining_date']) ? $arr_structure[0]['emp_proff']['joining_date'] : '';
                                        $row['Start Date Effective(dd-mm-yyyy)'] = $joining_date;
                                    }
                                    // End
                                }

                                $with_effect_from = !empty($row['Start Date Effective(dd-mm-yyyy)']) ? $row['Start Date Effective(dd-mm-yyyy)'] : date('Y-m-d'); // Edited by Akshay on 27-10-2025

                                $next_increment_date = $arr_empctc_data['next_increment_date'];
                                $payout_month = !empty($arr_empctc_data['pay_out_month']) ? $arr_empctc_data['pay_out_month'] : '0000-00-00';

                                // Arrear
                                $is_arrear = 'N'; // default

                                if (!empty($with_effect_from) && $with_effect_from !== '0000-00-00') {
                                    $date = DateTime::createFromFormat('Y-m-d', $with_effect_from);
                                    $month_year = $date->format('Y-m');

                                    $arr_processed = $this->SalaryHike->query(
                                        "SELECT COUNT(*) as count FROM payroll_master
                                                                                    WHERE emp_fkey = $emp_fkey
                                                                                    AND month_year = '$month_year'
                                                                                    AND action IN ('Approved', 'Processed');"
                                    );

                                    $is_processed = isset($arr_processed[0][0]['count']) ? (int)$arr_processed[0][0]['count'] : 0;
                                    if ($is_processed > 0) {
                                        $is_arrear = 'Y';
                                    }
                                }
                                // End

                                $increment_amount = $new_salary - $current_salary;
                                if (!empty($current_salary) && $current_salary != 0) {
                                    $increment_percentage = ($increment_amount / $current_salary) * 100;
                                } else {
                                    $increment_percentage = 0; // or null, or handle differently
                                }


                                $arr_details_data = array(
                                    'SalaryHikeDetail' => array(
                                        'salary_hike_fkey'       => $salary_hike_fkey,
                                        'item'                   => 'N',
                                        'branch_code'            => $branch_code,              // from your logic
                                        'emp_fkey'               => $emp_fkey,                 // from your loop or form
                                        'structure_id'           => $structure_id,
                                        'with_effect_from'     => isset($with_effect_from) && !empty($with_effect_from) ? $with_effect_from : '0000-00-00',
                                        'next_increment_date'  => isset($next_increment_date) && !empty($next_increment_date) ? $next_increment_date : '0000-00-00',
                                        'payout_month'         => isset($payout_month) && !empty($payout_month) ? $payout_month : '0000-00-00',
                                        'current_amount'         => $current_salary,
                                        'new_amount'             => $new_salary,
                                        'increment_amount'       => $increment_amount,
                                        'increment_percentage'   => $increment_percentage,
                                        'arrear_salary'          => $is_arrear,
                                        'status'                 => 1
                                    )
                                );

                                if ($structure_id != 0 || $increment_amount != 0)
                                    $this->SalaryHikeDetail->create();
                                $result2 = $this->SalaryHikeDetail->save($arr_details_data);
                            } catch (Exception $e) {
                                debug($e);
                                exit;
                            }
                        }
                    }
                    unlink($targetpath);
                    //edited by megha on 10/08/2019 annual gross salary amount empty excel msg 3
                    if ($count > 0) {
                        if ($ctcuploadtype == 1) {
                            echo json_encode(array('success' => 1, 'msg' => 'Employee Gross Salary imported successfully'));
                            exit;
                        } else {
                            echo json_encode(array('success' => 1, 'msg' => 'Employee Gross Salary imported successfully')); // Edited by Akshay on 14-10-2025
                            exit;
                        }
                        //edited by megha on 10/08/2019 annual gross salary amount empty excel msg 4
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Please Enter a value.'));
                        exit;
                    }
                    //end annual gross salary amount empty excel msg
                } else {
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary import failed, no data found!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary revision failed, no data found!'));
                        exit;
                    }
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary revision failed! '));
                    exit;
                }
            }
        } else {
            if ($ctcuploadtype == 1) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary revision failed! '));
                exit;
            }
            exit;
        }
    }
    // End

    public function uploadandsaveempctcitem($ctcuploadtype = 0)
    {
        $this->autoRender = FALSE;
        $this->SalaryHike->useDbConfig = $this->Session->read('ds');
        $this->SalaryHikeDetail->useDbConfig = $this->Session->read('ds');
        $user_id = $this->Session->read("login_user_id");

        // Edited by Akshay on 23-10-2025
        $arr_salary_structure = $this->SalaryHikeDetail->query("SELECT structure_id
                                                                    FROM `salary_structure`
                                                                    WHERE `structure_active` = '1';");
        $allowed_structures = array_map(function ($row) {
            return $row['salary_structure']['structure_id'];
        }, $arr_salary_structure);
        // End
        try {
            if ($ctcuploadtype != 0) {
                $authuser['company_code'] = $this->Session->read('company_code');
                $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_employee_gross' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
                $targetpath = getcwd() . "/files/" . $filename;
                if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                    $objReader = new PHPExcel_Reader_Excel2007();
                    $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir
                    $sheet = $objPHPExcel->getSheet(0); // Only use Sheet 1

                    $lastColumn = $sheet->getHighestColumn();
                    $lastColumn++;
                    $highestRowIndex = $sheet->getHighestRow();
                    $arrayempdata = array();
                    $mandatory_fields_warning = FALSE;

                    if ($highestRowIndex > 1) {
                        //atleast one employee records found
                        $index = 0;
                        $remarks = $_POST['remark'];
                        for ($row = 1; $row <= $highestRowIndex; $row++) {
                            if ($row == 1) {
                                //Get mandatory headings array here
                                $array_mandatory_columns = array();
                                $array_mandatory_column_names = array('Employee ID', 'Company ID', 'Employee Name', 'Current Salary Structure (Code)', 'New Salary Structure (Code)', 'Start Date Effective(dd-mm-yyyy)', 'Next Increment Date(dd-mm-yyyy)', 'Payout Month(dd-mm-yyyy)');
                                for ($col = 'A'; $col != $lastColumn; $col++) {
                                    $value = $sheet->getCell($col . "1")->getValue();
                                    if (in_array($value, $array_mandatory_column_names)) {
                                        array_push($array_mandatory_columns, $col);
                                    }
                                }
                                if ($array_mandatory_columns != array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H')) {
                                    unlink($targetpath);
                                    echo json_encode(array('success' => 0, 'msg' => 'Please check if the uploaded document is in correct format.'));
                                    exit;
                                }
                            } else {
                                // $array_mandatory_columns = array('A', 'B', 'C', 'D', 'E', 'F', 'G');
                                $array_mandatory_columns = array('A', 'B', 'C');
                                $array_mandatory_column_names = array('Employee ID');
                                for ($col = 'A'; $col != $lastColumn; $col++) {
                                    $mand_value = $sheet->getCell($col . $row)->getValue();
                                    $mand_value = isset($mand_value) ? $mand_value : '';
                                    if (in_array($col, $array_mandatory_columns) && $mand_value === '') {
                                        $mandatory_fields_warning = true;
                                        break 2;
                                    }
                                    $value = $sheet->getCell($col . $row)->getValue();

                                    // Edited by Akshay on 23-10-2025
                                    if ($col == 'E') {
                                        $colDValue = $sheet->getCell('D' . $row)->getValue();

                                        // If D is blank → E must have a valid structure
                                        if ($colDValue == '' && ($value == '' || !in_array($value, $allowed_structures))) {
                                            $mandatory_fields_warning = true;
                                            break 2;
                                        }

                                        // If D has a value → E can be blank or valid
                                        if ($colDValue != '' && $value != '' && !in_array($value, $allowed_structures)) {
                                            $mandatory_fields_warning = true;
                                            break 2;
                                        }
                                    }
                                    // End

                                    if ($col == 'G') {
                                        // $remarks = isset($remarks) ? $value : $remarks;
                                    }

                                    $arrayempdata[$index][$sheet->getCell($col . "1")->getValue()] = $value;
                                }
                                $index++;
                            }
                        }

                        // debug($arrayempdata);exit;
                        if ($mandatory_fields_warning) {

                            //Exit if mandatory fields not entered
                            unlink($targetpath);
                            echo json_encode(array('success' => 0, 'msg' => 'Please ensure all mandatory fields are filled and have values in the correct format.'));
                            exit;
                        } else {

                            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                            // $this->EmpSalaryCompUpload->useDbConfig = $this->Session->read('ds');
                            $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

                            App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));



                            // Edited by Akshay on 26-6-2025
                            $arr_hike_data = array();
                            $arr_hike_data = array(
                                'SalaryHike' => array(
                                    'is_multiple'      => 'Y',
                                    'item'             => 'Y',
                                    'action'           => 'Upload',
                                    'remarks'          => $remarks,
                                    'created_by'       => $user_id,
                                    'creation_date'    => date('Y-m-d H:i:s'),
                                    'status'           => 1
                                )
                            );

                            $this->SalaryHike->create(); // optional but good practice
                            $result = $this->SalaryHike->save($arr_hike_data);

                            $salary_hike_pkey = $this->SalaryHike->id;
                            // End
                            // debug($arrayempdata);
                            foreach ($arrayempdata as $key => $row) {

                                $arr_empctc_data = array();

                                $emp_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';


                                // Edited by Akshay on 26-6-2025
                                $arr_usercredentials = $this->EmployeeDetails->find('first', array(
                                    'fields' => 'emp_pkey',
                                    'branch_code',
                                    'conditions' => array(
                                        'emp_id' => $emp_id
                                    )
                                ));
                                $emp = isset($arr_usercredentials['EmployeeDetails']['emp_pkey']) ? $arr_usercredentials['EmployeeDetails']['emp_pkey'] : '';
                                $branch_code  = isset($arr_usercredentials['EmployeeDetails']['branch_code']) ? $arr_usercredentials['EmployeeDetails']['branch_code'] : '';

                                // Edited by Akshay on 14-10-2025
                                $arr_structure_id = $this->SalaryHike->query("SELECT policy_id FROM `emp_config` WHERE `emp_fkey` = '$emp' AND `status` = '1' AND `type` = 'SALARY'");
                                $structure_id = isset($arr_structure_id[0]['emp_config']['policy_id']) ? $arr_structure_id[0]['emp_config']['policy_id'] : 0;
                                if (isset($row['New Salary Structure (Code)']) && trim($row['New Salary Structure (Code)']) != '') {
                                    if ($structure_id != $row['New Salary Structure (Code)']) {
                                        $structure_id = $row['New Salary Structure (Code)'];

                                        $this->SalaryHike->updateAll(
                                            ['structure_change' => "'Y'"],        // Set value
                                            [
                                                'salary_hike_pkey' => $salary_hike_pkey,
                                                'structure_change !=' => 'Y'      // Only update if not already 'Y'
                                            ]
                                        );
                                    }
                                } else {
                                }
                                // End

                                // Convert Start Date Effective to Y-m-d
                                if (!empty($row['Start Date Effective(dd-mm-yyyy)'])) {
                                    if (is_numeric($row['Start Date Effective(dd-mm-yyyy)'])) {
                                        $with_effect_from = date('Y-m-d', ($row['Start Date Effective(dd-mm-yyyy)'] - 25569) * 86400);
                                    } else {
                                        $dt = DateTime::createFromFormat('d-m-Y', $row['Start Date Effective(dd-mm-yyyy)']);
                                        $with_effect_from = ($dt !== false) ? $dt->format('Y-m-d') : '0000-00-00';
                                    }
                                } else {
                                    // Edited by Akshay on 4-11-2025
                                    $arr_structure = $this->SalaryHike->query("SELECT structure_id, joining_date FROM emp_proff WHERE emp_fkey = '$emp'");
                                    $structure = isset($arr_structure[0]['emp_proff']['structure_id']) ? $arr_structure[0]['emp_proff']['structure_id'] : '';
                                    if ($structure != '') {
                                        date_default_timezone_set('Asia/Kolkata');
                                        $with_effect_from = date('Y-m-d');
                                    } else {
                                        $joining_date = isset($arr_structure[0]['emp_proff']['joining_date']) ? $arr_structure[0]['emp_proff']['joining_date'] : '';
                                        $with_effect_from = $joining_date;
                                    }
                                    // End
                                }

                                // Convert and update Next Increment Date in array
                                if (!empty($row['Next Increment Date(dd-mm-yyyy)'])) {
                                    if (is_numeric($row['Next Increment Date(dd-mm-yyyy)'])) {
                                        $converted = date('Y-m-d', ($row['Next Increment Date(dd-mm-yyyy)'] - 25569) * 86400);
                                    } else {
                                        $dt = DateTime::createFromFormat('d-m-Y', $row['Next Increment Date(dd-mm-yyyy)']);
                                        $converted = ($dt !== false) ? $dt->format('Y-m-d') : '0000-00-00';
                                    }
                                    $row['Next Increment Date(dd-mm-yyyy)'] = $converted;
                                    $next_increment_date = $converted;
                                } else {
                                    $row['Next Increment Date(dd-mm-yyyy)'] = '0000-00-00';
                                    $next_increment_date = '0000-00-00';
                                }

                                // Convert and update Payout Month in array
                                if (!empty($row['Payout Month(dd-mm-yyyy)'])) {
                                    if (is_numeric($row['Payout Month(dd-mm-yyyy)'])) {
                                        $converted_month = date('Y-m', ($row['Payout Month(dd-mm-yyyy)'] - 25569) * 86400);
                                    } else {
                                        $dt = DateTime::createFromFormat('d-m-Y', $row['Payout Month(dd-mm-yyyy)']);
                                        $converted_month = ($dt !== false) ? $dt->format('Y-m') : '';
                                    }
                                    $row['Payout Month(dd-mm-yyyy)'] = $converted_month;
                                    $payout_month = !empty($converted_month) ? $converted_month . '-01' : '0000-00-00';
                                } else {
                                    $row['Payout Month(dd-mm-yyyy)'] = '';
                                    $payout_month = '0000-00-00';
                                }


                                $is_arrear = 'N'; // default

                                if (!empty($with_effect_from) && $with_effect_from !== '0000-00-00') {
                                    $date = DateTime::createFromFormat('Y-m-d', $with_effect_from);
                                    $month_year = $date->format('Y-m');

                                    $arr_processed = $this->SalaryHike->query(
                                        "SELECT COUNT(*) as count FROM payroll_master
                                                                                    WHERE emp_fkey = $emp
                                                                                    AND month_year = '$month_year'
                                                                                    AND action IN ('Approved', 'Processed');"
                                    );

                                    $is_processed = isset($arr_processed[0][0]['count']) ? (int)$arr_processed[0][0]['count'] : 0;
                                    if ($is_processed > 0) {
                                        $is_arrear = 'Y';
                                    }
                                }

                                // End

                                foreach ($row as $key => $value) {

                                    // if (!in_array($key, array("Employee ID", "Company ID", "Employee Name", "Start Date Effective(dd-mm-yyyy)", "Next Increment Date(dd-mm-yyyy)", "Payout Month(dd-mm-yyyy)", "Remarks"))) {
                                    if (!in_array($key, array("Employee ID", "Company ID", "Employee Name", "Start Date Effective(dd-mm-yyyy)", "Next Increment Date(dd-mm-yyyy)", "Payout Month(dd-mm-yyyy)", "New Salary Structure (Code)", "Current Salary Structure (Code)"))) { // Edited by Akshay on 14-10-2025

                                        $salComponents = $this->EmployeeSalaryStructure->query("select distinct salary_head_item_fkey from emp_salary_structure ed WHERE TRIM(salary_head_item_desc) = '" . $key . "' LIMIT 1 ");

                                        $head_fkey = isset($salComponents[0]['ed']['salary_head_item_fkey']) ? $salComponents[0]['ed']['salary_head_item_fkey'] : 1000;

                                        $arr_empctc_data = array();
                                        $arr_empctc_data['emp_id'] = $emp_id;
                                        $arr_empctc_data['component'] = $key;
                                        $user_ids = $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
                                        $arr_empctc_data['salary_head_item_fkey'] = isset($head_fkey) ? $head_fkey : 1000;
                                        if ($value == '') {
                                            $arr_empctc_data['rate'] = 0;
                                        } else {
                                            $arr_empctc_data['rate'] = $value;
                                        }


                                        $arr_amount  = $this->SalaryHike->query("SELECT structure_det_value 
                                                                FROM emp_salary_structure 
                                                                WHERE salary_head_item_fkey = $head_fkey 
                                                                AND emp_fkey = $emp 
                                                                AND end_date_effective IS NULL
                                                                LIMIT 1;");
                                        $current_amount = isset($arr_amount[0]['emp_salary_structure']['structure_det_value']) ? $arr_amount[0]['emp_salary_structure']['structure_det_value'] : 0;
                                        $new_amount = isset($arr_empctc_data['rate']) ? $arr_empctc_data['rate'] : 0;
                                        $increment_amount = $new_amount - $current_amount;
                                        if ($current_amount != 0) {
                                            $increment_percentage = ($increment_amount / $current_amount) * 100;
                                        } else {
                                            $increment_percentage = 0; // or null or leave it blank
                                        }



                                        // Edited by Akshay on 26-6-2025
                                        $arr_hike_detail_data = array(
                                            'SalaryHikeDetail' => array(
                                                'salary_hike_fkey'       => $salary_hike_pkey,       // from salary_hike insert
                                                'item'                   => 'Y',
                                                'branch_code'            => $branch_code,
                                                'emp_fkey'               => $emp,
                                                'structure_id'           => $structure_id,
                                                'with_effect_from'       => $with_effect_from,       // format: Y-m-d
                                                'next_increment_date'    => $next_increment_date,    // format: Y-m-d
                                                'payout_month'           => $payout_month,           // format: Y-m-d or '0000-00-00'
                                                'salary_head_item_fkey'  => $head_fkey,
                                                'current_amount'         => $current_amount,
                                                'new_amount'             => $new_amount,
                                                'increment_amount'       => $increment_amount,
                                                'increment_percentage'   => $increment_percentage,
                                                'arrear_salary'          => $is_arrear,
                                                'status'                 => 1
                                            )
                                        );
                                        // debug($arr_hike_detail_data);
                                        // exit;
                                        try {
                                            //  if ($increment_amount != 0 && $new_amount != 0) { 
                                            if ($head_fkey != 1000) { // Edited by Akshay on 19-11-2025
                                                $this->SalaryHikeDetail->create();
                                                $result1 = $this->SalaryHikeDetail->save($arr_hike_detail_data);
                                            }
                                        } catch (Exception $e) {
                                            // debug($e);
                                            // exit;
                                        }
                                        // End

                                    }
                                }
                            }

                            // Edited by Akshay on 19-11-2025
                            $countDetails = $this->SalaryHikeDetail->find('count', array(
                                'conditions' => array('salary_hike_fkey' => $salary_hike_pkey)
                            ));
                            if ($countDetails == 0) {
                                $this->SalaryHike->updateAll(
                                    array('status' => "'0'"),
                                    array('salary_hike_pkey' => $salary_hike_pkey)
                                );

                                // Give error message as no changes found
                                unlink($targetpath);
                                echo json_encode(array('success' => 0, 'msg' => 'No changes found to update.'));
                                exit;
                            }
                            // End
                        }

                        unlink($targetpath);
                        if ($ctcuploadtype == 1) {
                            echo json_encode(array('success' => 1, 'msg' => 'Employee Component Upload imported successfully.'));
                            exit;
                        } else {
                            echo json_encode(array('success' => 1, 'msg' => 'Employee Component Upload imported successfully'));
                            exit;
                        }
                    } else {
                        unlink($targetpath);
                        if ($ctcuploadtype == 1) {
                            echo json_encode(array('success' => 0, 'msg' => 'Sorry, Component Upload import failed, no data found!'));
                            exit;
                        } else {
                            echo json_encode(array('success' => 0, 'msg' => 'Sorry, Component Upload import failed, no data found!'));
                            exit;
                        }
                    }
                } else {
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee EMI Amounts import failed!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee EMI Amounts revision failed! '));
                        exit;
                    }
                }
            } else {

                echo json_encode(array('success' => 0, 'msg' => 'Please select a month '));
                exit;
            }
        } catch (Exception $e) {
            debug($e);
            exit;
        }
    }

    public function salaryIncrementForm()
    {
        $this->autoRender = FALSE;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $arr_employees = $this->EmployeeCTC->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 order by first_name ASC');
        $this->set("arr_employees", $arr_employees);

        $arr_salary = $this->EmployeeCTC->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);
        $company_code = $this->Session->read('company_code'); // Edited by Akshay on 30-1-2025

        // Edited by Akshay on 11-10-2025
        $emp_pkey = isset($_GET['emp_fkey']) ? $_GET['emp_fkey'] : 0;
        $update_status = isset($_GET['status']) ? $_GET['status'] : '';
        $this->set("emp_pkey", $emp_pkey);
        // End

        if ($company_code == FALSE) {
            $arr_salary_head_items =  $this->EmployeeCTC->query("SELECT salary_head_item_pkey, item 
            FROM salary_head_items
            WHERE salary_head_item_pkey IN (
                SELECT salary_head_item_pkey 
                FROM salary_head_items 
                WHERE head_fkey IN (1, 4, 10)
            )
            AND salary_head_item_pkey IN (
                SELECT salary_head_item_fkey 
                FROM tax_salary_components 
                WHERE tax_salary_components_name IN ('Basic', 'Dearness Allowance (DA)')
            )
            ");
            // } elseif ($company_code == 'KWMT' || $company_code == 'GTRA') {
        } elseif (false) { // Edited by Akshay on 27-9-2025
            $arr_salary_head_items =  $this->EmployeeCTC->query("SELECT salary_head_item_pkey, item 
            FROM salary_head_items
            WHERE salary_head_item_pkey IN (
                SELECT salary_head_item_pkey 
                FROM salary_head_items 
                WHERE head_fkey IN (1, 4, 10)
            )
            AND salary_head_item_pkey IN (
                SELECT salary_head_item_fkey 
                FROM tax_salary_components 
                WHERE tax_salary_components_name IN ('Basic', 'VDA')
            )
            ");
        } else {
            $arr_salary_head_items =  $this->EmployeeCTC->query("SELECT salary_head_item_pkey, item 
            FROM salary_head_items
            WHERE salary_head_item_pkey IN (
                SELECT salary_head_item_pkey 
                FROM salary_head_items 
                WHERE head_fkey IN (1, 4)
            )
            AND status = 1;
            ");

            $arr_salary_head_item_keys = array_column($arr_salary_head_items, 'salary_head_items.salary_head_item_pkey'); // Edited by Akshay on 9-10-2025


        }
        $this->set("arr_salary_head_item_keys", $arr_salary_head_item_keys); // Edited by Akshay on 9-10-2025
        $this->set("arr_salary_head_items", $arr_salary_head_items);

        $this->render('salary_increment');
    }

    public function saveIncrement()
    {
        $this->autoRender = FALSE;

        try {
            $this->SalaryHike->useDbConfig = $this->Session->read('ds');
            $this->SalaryHikeDetail->useDbConfig = $this->Session->read('ds');

            $arr_form_data = $_POST;

            $is_item = $arr_form_data['is_item'];

            $emp_fkey = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : 0;

            $arr_emp_details = $this->SalaryHike->query("SELECT branch_code FROM emp_details WHERE emp_pkey = '$emp_fkey';");


            $branch_code = $arr_emp_details[0]['emp_details']['branch_code'];

            $salary_structure = isset($arr_form_data['salary_structure']) ? $arr_form_data['salary_structure'] : '';
            $ar_cur_sal_structure = $this->SalaryHike->query("SELECT structure_id FROM emp_proff ess 
                                                                    WHERE emp_fkey = '$emp_fkey' ;");
            $current_sal_structure = isset($ar_cur_sal_structure[0]['ess']['structure_id']) ? $ar_cur_sal_structure[0]['ess']['structure_id'] : 0;

            $structure_change = ($salary_structure == $current_sal_structure) ? 'N' : 'Y';

            $user_id = $this->Session->read("login_user_id");
            $arr_form_data['created_by'] = $user_id;

            // Convert dd-mm-yyyy to Y-m-d
            $arr_form_data['start_date_effective'] = date('Y-m-d', strtotime($arr_form_data['start_date_effective']));
            $arr_form_data['next_increment_date']  = date('Y-m-d', strtotime($arr_form_data['next_increment_date']));

            // Convert September-2025 to Y-m-1
            // Edited by Akshay on 8-10-2025
            if (isset($arr_form_data['pay_out_month']) && $arr_form_data['pay_out_month'] != '') {
                $arr_form_data['pay_out_month'] = date('Y-m-01', strtotime('01-' . $arr_form_data['pay_out_month']));
            }
            // Edited by Akshay on 8-11-2025
            else {
                // if ($current_sal_structure == 0) {
                //     $arr_joining_date = $this->SalaryHike->query("SELECT joining_date FROM emp_proff WHERE emp_fkey = '$emp_fkey';");
                //     $arr_form_data['pay_out_month'] = isset($arr_joining_date[0]['emp_proff']['joining_date']) ?
                //         date('Y-m-01', strtotime($arr_joining_date[0]['emp_proff']['joining_date'])) :
                //         '0000-00-01';
                // }
            }
            // End
            // End

            // Edited by Akshay on 24-6-2025
            $this->SalaryHike->create();
            $salaryHikeData = array(
                'is_multiple' => 'N',
                'item' => $is_item,
                'structure_change' => $structure_change,
                'action' => null,
                'remarks' => !empty($arr_form_data['remarks2']) ? $arr_form_data['remarks2'] : null,
                'created_by' => $user_id, // <-- set this
                'creation_date' => date('Y-m-d H:i:s'),
                'status' => 1
            );
            $this->SalaryHike->save($salaryHikeData);
            $salaryHikeId = $this->SalaryHike->getLastInsertID();

            // 2. Prepare salary_hike_details data
            $details = array();

            $with_effect_from = date('Y-m-d', strtotime($arr_form_data['start_date_effective']));
            $date = DateTime::createFromFormat('Y-m-d', $with_effect_from);
            $month_year = $date->format('Y-m');
            $arr_processed = $this->SalaryHike->query(
                "SELECT COUNT(*) as count FROM payroll_master
                    WHERE emp_fkey = $emp_fkey
                    AND month_year = '$month_year'
                    AND action IN ('Approved', 'Processed');"
            );

            $is_processed = isset($arr_processed[0][0]['count']) ? (int)$arr_processed[0][0]['count'] : 0;
            if ($is_processed > 0) {
                $is_arrear = 'Y';
            } else {
                $is_arrear = 'N';
            }

            if ($is_item == 'Y') {
                foreach ($arr_form_data as $key => $value) {
                    if (preg_match('/^desc_(\d+)$/', $key, $matches)) {
                        $itemId = $matches[1];

                        $isIndirect = isset($arr_form_data["indirect_current_$itemId"]);

                        //Insert Values
                        if (!empty($arr_form_data["contrib_current_$itemId"])) {
                            $currentAmount = $arr_form_data["contrib_current_$itemId"];
                            $newAmount = $arr_form_data["contrib_new_value_$itemId"];
                            $incrementAmount = $arr_form_data["contrib_new_value_amt$itemId"];
                            $incrementPercentage = $arr_form_data["contrib_new_value_pct$itemId"];
                        } elseif (!empty($arr_form_data["indirect_current_$itemId"])) {
                            $currentAmount = $arr_form_data["indirect_current_$itemId"];
                            $newAmount = $arr_form_data["indirect_new_value_$itemId"];
                            $incrementAmount = $arr_form_data["indirect_new_value_amt$itemId"];
                            $incrementPercentage = $arr_form_data["indirect_new_value_pct$itemId"];
                        } else {
                            $currentAmount = $arr_form_data["current_$itemId"];
                            $newAmount = $arr_form_data["new_value_$itemId"];
                            $incrementAmount = $arr_form_data["new_value_amt$itemId"];
                            $incrementPercentage = $arr_form_data["new_value_pct$itemId"];
                        }

                        $details[] = array(
                            'salary_hike_fkey'        => $salaryHikeId,
                            'item'                    => $is_item,
                            'branch_code'             => $branch_code,
                            'emp_fkey'                => $arr_form_data['emp_fkey'],
                            'structure_id'            => $arr_form_data['salary_structure'],
                            'with_effect_from'        => $with_effect_from,
                            'next_increment_date'     => date('Y-m-d', strtotime($arr_form_data['next_increment_date'])),
                            'payout_month'            => isset($arr_form_data['pay_out_month']) ? $arr_form_data['pay_out_month'] : '0000-00-00',
                            'salary_head_item_fkey'   => $itemId,
                            'current_amount'       => $currentAmount,
                            'new_amount'           => $newAmount,
                            'increment_amount'     => $incrementAmount,
                            'increment_percentage' => $incrementPercentage,
                            'arrear_salary'           => $is_arrear,
                            'status'                  => 1
                        );
                    }
                }
            } else {
                $arr_gross = $this->SalaryHikeDetail->query("SELECT SUM(structure_det_value) AS gross FROM `emp_salary_structure` 
                                                    WHERE `emp_fkey` = '$emp_fkey' AND `end_date_effective` IS NULL
                                                    AND salary_head_item_fkey IN (SELECT salary_head_item_pkey FROM salary_head_items WHERE head_fkey = 1 AND status = 1)
                                                    AND head_operator = 'Addition' AND item_part = 'Direct';");

                $gross = isset($arr_gross[0][0]['gross']) ? $arr_gross[0][0]['gross'] : 0;
                // Edited by Akshay on 8-10-2025
                $total_direct_value = isset($arr_form_data['total_direct_value']) && $arr_form_data['total_direct_value'] !== ''
                    ? $arr_form_data['total_direct_value']
                    : null;
                // $new_gross = ($total_direct_value !== null) ? $total_direct_value : (isset($arr_form_data['emp_monthly_ctc']) ? $arr_form_data['emp_monthly_ctc'] : 0);
                // End
                // $new_gross = isset($arr_form_data['emp_monthly_ctc']) ? $arr_form_data['emp_monthly_ctc'] : 0;
                $new_gross = isset($arr_form_data['gross_new_value']) ? ($arr_form_data['gross_new_value']) : 0; // Edited by Akshay on 28-11-2025

                // Edited by Akshay on 9-10-2025
                $emp_pkey = $arr_form_data['emp_fkey'];
                $emp_structure_id = $arr_form_data['salary_structure'];
                $gross_count = $this->EmployeeCTC->query("SELECT COUNT(*) as count FROM emp_ctc_transaction WHERE `emp_fkey` = '$emp_pkey' AND `end_date_effective` IS NULL");
                $gross_count = isset($gross_count[0][0]['count']) ? $gross_count[0][0]['count'] : 0;
                $arr_structure_eg_amt = $this->EmployeeCTC->query("SELECT SUM(structure_det_value) AS structure_eg_amt FROM emp_salary_structure WHERE emp_fkey = '$emp_fkey' AND end_date_effective IS NULL AND item_part = 'Direct' AND head_operator = 'Addition' AND salary_head_item_fkey IN (SELECT salary_head_item_pkey FROM salary_head_items WHERE head_fkey = 1 AND status = 1);");
                $std_gross = isset($arr_structure_eg_amt[0][0]['structure_eg_amt']) ? $arr_structure_eg_amt[0][0]['structure_eg_amt'] : 0;

                if ($gross_count != 0) {
                    $increment_amount = $new_gross - $gross;
                } else {
                    $increment_amount = $new_gross - $std_gross;
                    $new_gross = $increment_amount;
                }

                // End

                $increment_percentage = ($gross > 0) ? ($increment_amount / $gross) * 100 : 0;


                $details[] = array(
                    'salary_hike_fkey'        => $salaryHikeId,
                    'item'                    => $is_item,
                    'branch_code'             => $branch_code,
                    'emp_fkey'                => $arr_form_data['emp_fkey'],
                    'structure_id'            => $arr_form_data['salary_structure'],
                    'with_effect_from'        => $with_effect_from,
                    'next_increment_date'     => date('Y-m-d', strtotime($arr_form_data['next_increment_date'])),
                    'payout_month'            => isset($arr_form_data['pay_out_month']) ? $arr_form_data['pay_out_month'] : '0000-00-00',
                    'current_amount'          => $gross,
                    'new_amount'              => $new_gross,
                    'increment_amount'        => $increment_amount,
                    'increment_percentage'    => $increment_percentage,
                    'arrear_salary'           => $is_arrear,
                    'status'                  => 1
                );
            }



            // 3. Save all details
            $this->SalaryHikeDetail->saveAll($details);
            // End



            echo json_encode([
                'status' => 'success',
                'message' => 'Salary increment saved successfully.',
                'increment_id' => $salaryHikeId
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to save increment.'
            ]);
        }
    }


    public function saveItemIncrement()
    {
        $this->autoRender = FALSE;

        try {
            $this->SalaryHike->useDbConfig = $this->Session->read('ds');
            $this->SalaryHikeDetail->useDbConfig = $this->Session->read('ds');

            $arr_form_data = $_POST;
            // debug($arr_form_data);
            // exit;



            $user_id = $this->Session->read("login_user_id");
            $arr_form_data['created_by'] = $user_id;

            // Convert dd-mm-yyyy to Y-m-d
            $arr_form_data['start_date_effective'] = date('Y-m-d', strtotime($arr_form_data['with_effect_from']));
            $arr_form_data['next_increment_date']  = date('Y-m-d', strtotime($arr_form_data['next_increment_date']));

            // Convert September-2025 to Y-m-1
            $arr_form_data['pay_out_month'] = date('Y-m-01', strtotime('01-' .  $arr_form_data['payout_month']));

            $itemId = $arr_form_data['component_item'];
            $formulaItemId = $arr_form_data['component_item_2'];
            $arr_pkey = $arr_form_data['emp_fkey2'];



            $sql = "    SELECT emp_fkey
                        FROM emp_salary_structure
                        WHERE end_date_effective IS NULL
                        GROUP BY emp_fkey
                        HAVING SUM(CASE WHEN salary_head_item_fkey = $formulaItemId THEN 1 ELSE 0 END) = 0
                        OR SUM(CASE WHEN salary_head_item_fkey = $itemId THEN 1 ELSE 0 END) = 0;
                        )";

            $result = $this->EmployeeCTC->query($sql);
            $rejectedEmpFkeys = array_map(function ($row) {
                return $row['emp_salary_structure']['emp_fkey'];
            }, $result);

            $rejected = array_intersect($arr_pkey, $rejectedEmpFkeys);
            if (!empty($rejected)) {
                $rejectedEmpFkeysString = implode(',', $rejected);
            }

            $validEmpKeys = array_diff($arr_pkey, $rejectedEmpFkeys);
            if (!empty($validEmpKeys)) {
                $validEmpKeysStr = implode(',', $validEmpKeys);
            }

            // Edited by Akshay on 24-6-2025
            $this->SalaryHike->create();
            $salaryHikeData = array(
                'is_multiple' => $arr_form_data['is_multiple'],
                'item' => 'Y',
                'action' => null,
                'remarks' => !empty($arr_form_data['remarks']) ? $arr_form_data['remarks'] : null,
                'created_by' => $user_id, // <-- set this
                'creation_date' => date('Y-m-d H:i:s'),
                'status' => 1
            );
            $this->SalaryHike->save($salaryHikeData);
            $salaryHikeId = $this->SalaryHike->getLastInsertID();

            // 2. Prepare salary_hike_details data
            $details = array();



            $hikePercent = isset($arr_form_data['hike']) ? $arr_form_data['hike'] : 0;
            $hikeDecimal = $hikePercent / 100;


            if (true) {
                $type = $arr_form_data['type'];
                $type_value = $arr_form_data['type_value'];

                $sql = "SELECT ep.emp_fkey 
                                FROM emp_proff ep 
                                LEFT JOIN emp_details ed ON ep.emp_fkey = ed.emp_pkey 
                                WHERE ed.status = 1
                                AND ed.emp_pkey IN ($validEmpKeysStr)";

                $arr_emp_pkey = $this->SalaryHike->query($sql);
                // debug($sql);
                // debug($arr_emp_pkey);
                // exit;

                foreach ($arr_emp_pkey as $arr_emp) {
                    $emp_pkey = $arr_emp['ep']['emp_fkey'];
                    $arr_branch = $this->SalaryHike->query("SELECT branch_code FROM emp_details WHERE emp_pkey = '$emp_pkey';");
                    $branch_code = $arr_branch[0]['emp_details']['branch_code'];

                    $hikePercent = isset($arr_form_data['hike']) ? $arr_form_data['hike'] : 0;
                    $hikeDecimal = $hikePercent / 100;
                    $itemId = $arr_form_data['component_item'];

                    $formulaItemId = $arr_form_data['component_item_2'];

                    $arr_item_details = $this->SalaryHike->query("
                                                                SELECT 
                                                                    MIN(emp_structure_id) AS emp_structure_id,
                                                                    MAX(CASE WHEN salary_head_item_fkey = '$itemId' THEN structure_det_value END) AS value1,
                                                                    MAX(CASE WHEN salary_head_item_fkey = '$formulaItemId' THEN structure_det_value END) AS value2

                                                                FROM emp_salary_structure
                                                                WHERE emp_fkey = '$emp_pkey'
                                                                AND end_date_effective IS NULL
                                                                AND salary_head_item_fkey IN ('$itemId', '$formulaItemId')
                                                            ");

                    $value1 = isset($arr_item_details[0][0]['value1']) ? $arr_item_details[0][0]['value1'] : 0;
                    $value2 = isset($arr_item_details[0][0]['value2']) ? $arr_item_details[0][0]['value2'] : 0;
                    $emp_sal_structure = isset($arr_item_details[0][0]['emp_structure_id']) ? $arr_item_details[0][0]['emp_structure_id'] : 0;

                    $month_year = date('Y-m', strtotime($arr_form_data['start_date_effective']));
                    $arr_processed = $this->SalaryHike->query(
                        "SELECT COUNT(*) as count FROM payroll_master
                    WHERE emp_fkey = $emp_pkey
                    AND month_year = '$month_year'
                    AND action IN ('Approved', 'Processed');"
                    );

                    $is_processed = isset($arr_processed[0][0]['count']) ? (int)$arr_processed[0][0]['count'] : 0;
                    if ($is_processed > 0) {
                        $is_arrear = 'Y';
                    } else {
                        $is_arrear = 'N';
                    }

                    if ($emp_sal_structure) {
                        $value3 = $value2 * $hikeDecimal;

                        $details[] = array(
                            'salary_hike_fkey'        => $salaryHikeId,
                            'item'                    => 'Y',
                            'branch_code'             => $branch_code,
                            'emp_fkey'                => $emp_pkey,
                            'structure_id'            => $emp_sal_structure,
                            'with_effect_from'        => date('Y-m-d', strtotime($arr_form_data['start_date_effective'])),
                            'next_increment_date'     => date('Y-m-d', strtotime($arr_form_data['next_increment_date'])),
                            'payout_month'            => $arr_form_data['pay_out_month'],
                            'salary_head_item_fkey'   => $itemId,
                            'current_amount'          => $value1,
                            'new_amount'              => $value3,
                            'increment_amount'        => $value3 - $value1,
                            'increment_percentage' => ($value1 != 0) ? (($value3 - $value1) / $value1) * 100 : 0,
                            'arrear_salary'         => $is_arrear,
                            'status'                  => 1
                        );
                    }
                }
            }


            // debug($details);exit;
            // 3. Save all details
            $this->SalaryHikeDetail->saveAll($details);
            // End

            if (isset($rejectedEmpFkeysString)) {
                $sql = "
                    SELECT ed.emp_pkey, CONCAT(TRIM(ed.first_name), ' ', TRIM(ed.last_name), ' - ', ep.emp_company_id) AS name 
                    FROM emp_details ed 
                    JOIN emp_proff ep ON ep.emp_fkey = ed.emp_pkey
                    WHERE ed.status = 1
                    AND ed.emp_pkey IN ($rejectedEmpFkeysString)
                    ORDER BY ed.first_name ASC;
                ";
                $arrRejEmployees = $this->SalaryHikeDetail->query($sql);

                $names = [];
                foreach ($arrRejEmployees as $emp) {
                    foreach ($emp as $key => $value) {
                        if (is_array($value) && isset($value['name'])) {
                            $names[] = $value['name'];
                        }
                    }
                }

                $namesStr = implode(',', $names);
            } else {
                $namesStr = '';
            }


            echo json_encode([
                'status' => 'success',
                'message' => 'Salary increment saved successfully.',
                'increment_id' => $salaryHikeId,
                'rejected_emps' => $namesStr // Edited by Akshay on 27-9-2025
            ]);
        } catch (Exception $e) {
            debug($e);
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to save increment.'
            ]);
        }
    }
    public function calcSalaryStructure()
    {
        $this->autoRender = false;
        $this->response->type('json'); // Set response content type as JSON

        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $empFkey      = isset($_POST['emp_fkey'])       ? (int)$_POST['emp_fkey']       : 0;
        $monthlyGross = isset($_POST['monthly_gross'])  ? (float)$_POST['monthly_gross'] : 0;
        $montlyCtc = isset($_POST['monthly_ctc'])  ? (float)$_POST['monthly_ctc'] : 0;

        $arr_emp_structure_id = $this->EmployeeCTC->query("
            SELECT DISTINCT emp_structure_id
            FROM emp_salary_structure
            WHERE emp_fkey = '$empFkey'
            AND end_date_effective IS NULL;
        ");

        $emp_structure_id = isset($arr_emp_structure_id[0]['emp_salary_structure']['emp_structure_id'])
            ? $arr_emp_structure_id[0]['emp_salary_structure']['emp_structure_id']
            : '';

        $emp_structure_id = isset($_POST['structure_id'])  ? (float)$_POST['structure_id'] : 0; // Edited by Akshay on 9-10-2025

        if ($emp_structure_id != '') {

            // Edited by Akshay on 9-10-2025
            $gross_count = $this->EmployeeCTC->query("SELECT COUNT(*) as count FROM emp_ctc_transaction WHERE `emp_fkey` = '$empFkey' AND `end_date_effective` IS NULL");
            $gross_count = isset($gross_count[0][0]['count']) ? $gross_count[0][0]['count'] : 0;
            if ($gross_count == 0) {
                $arr_structure_eg_amt = $this->EmployeeCTC->query("SELECT structure_eg_amt FROM salary_structure WHERE structure_id = '$emp_structure_id';");
                $std_gross = isset($arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt']) ? $arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt'] : 0;
                $monthlyGross = $monthlyGross - $std_gross;
            }
            // End

            $arr_result = $this->EmployeeCTC->query("CALL calculate_emp_salary_breakup('$empFkey', $emp_structure_id, '$monthlyGross');");
            // debug($arr_result);
            if (!empty($arr_result)) {
                // Convert and return JSON response
                echo json_encode($arr_result, JSON_PRETTY_PRINT);
                return;
            }
        }

        // If no result or emp_structure_id is empty, return empty JSON or error
        echo json_encode([
            'status' => 'error',
            'message' => 'No data found or invalid employee structure.'
        ]);
    }

    public function alterSalaryStructure()
    {
        $this->autoRender = FALSE;
        $this->response->type('json');
        $arr_form_data = $this->request->data;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $company = $this->Session->read('company_code');
        $user_ids = $this->Session->read('login_user_id');

        $salary_hike_pkey = $arr_form_data['salary_hike_pkey'];
        $arr_salary_hike_details = $this->EmployeeConfig->query("SELECT DISTINCT emp_fkey, structure_id FROM salary_hike_details WHERE salary_hike_fkey = '$salary_hike_pkey';");
        $response = array(); // Edited by Akshay on 17-10-2025
        $unfilled_statutotry_fields = array(); // Edited by Akshay on 1-4-2026
        $wrong_salary_message = array();  // Edited by Akshay on 1-4-2026
        // debug($arr_salary_hike_details); exit;
        // Edited by Akshay on 17-10-2025
        foreach ($arr_salary_hike_details as $salary_hike_details) {
            $arr_form_data['emp_fkey'] = $salary_hike_details['salary_hike_details']['emp_fkey'];
            $arr_form_data['structure_id'] = isset($salary_hike_details['salary_hike_details']['structure_id']) ? $salary_hike_details['salary_hike_details']['structure_id'] : 0;

            if ($arr_form_data['structure_id'] != 0) {
                $new_structure_id = $arr_form_data['structure_id']; // Edited by Akshay on 27-10-2025
                $emp = $arr_form_data['emp_fkey'];

                // Edited by Akshay on  30-10-2025
                $arr_shd = $this->EmployeeConfig->query("SELECT with_effect_from, next_increment_date, payout_month FROM salary_hike_details 
                                                            WHERE salary_hike_fkey = '$salary_hike_pkey'
                                                            AND emp_fkey = '$emp'
                                                            AND status = 1
                                                            GROUP BY emp_fkey;
                                                          ");

                $arr_emp_proff = $this->EmployeeConfig->query("SELECT structure_id, joining_date, emp_company_id, emp_type FROM emp_proff WHERE emp_fkey = '$emp';"); // Edited by Akshay on 27-3-2026
                $structure_id = isset($arr_emp_proff[0]['emp_proff']['structure_id']) ? $arr_emp_proff[0]['emp_proff']['structure_id'] : 0;
                $payout_month = isset($arr_shd[0]['salary_hike_details']['payout_month']) ? $arr_shd[0]['salary_hike_details']['payout_month'] : NULL;
                $with_effect_from = isset($arr_shd[0]['salary_hike_details']['with_effect_from']) ? $arr_shd[0]['salary_hike_details']['with_effect_from'] : NULL;
                if ($payout_month == '' || $payout_month == '0000-00-00') {
                    if ($structure_id == 0) {
                        $payout_month = isset($arr_emp_proff[0]['emp_proff']['joining_date']) && $arr_emp_proff[0]['emp_proff']['joining_date'] != ''
                            ? date('Y-m-01', strtotime($arr_emp_proff[0]['emp_proff']['joining_date']))
                            : date('Y-m-01');
                    } else {
                        $arr_next_month = $this->EmployeeConfig->query("SELECT MAX(month_year) AS latest_month_year
                                                FROM payroll_master
                                                WHERE emp_fkey = '$emp'
                                                AND action = 'processed';");

                        $payout_month = isset($arr_next_month[0][0]['latest_month_year']) && !empty($arr_next_month[0][0]['latest_month_year'])
                            ? date('Y-m-01', strtotime($arr_next_month[0][0]['latest_month_year'] . ' +1 month'))
                            : date('Y-m-01', strtotime('month'));
                    }
                }

                $joining_date = isset($arr_emp_proff[0]['emp_proff']['joining_date']) ? $arr_emp_proff[0]['emp_proff']['joining_date'] : '';
                if ($structure_id == 0) {
                    $with_effect_from = isset($with_effect_from) ? $joining_date : $with_effect_from;
                }
                if (($joining_date == '' || $joining_date > $with_effect_from)) {
                    $arr_emp_name = $this->EmployeeConfig->query("select CONCAT(COALESCE(ed.first_name, ''), ' ', COALESCE(ed.last_name, '')) AS emp_name FROM emp_details ed WHERE ed.emp_pkey ='$emp'");
                    $emp_name = isset($arr_emp_name[0][0]['emp_name']) ? $arr_emp_name[0][0]['emp_name'] : '';
                    $emp_company_id = isset($arr_emp_proff[0]['emp_proff']['emp_company_id']) ? $arr_emp_proff[0]['emp_proff']['emp_company_id'] : '';
                    continue;
                }
                // End

                $fetch_proff = $this->EmployeeConfig->query("select structure_id, emp_branch from emp_proff where emp_fkey = '$emp' ");
                $salary_id = isset($fetch_proff['0']['emp_proff']['structure_id']) ? $fetch_proff['0']['emp_proff']['structure_id'] : 0;

                // Edited by Akshay on 27-10-2025
                $arr_total = $this->EmployeeConfig->query("
                                                                    SELECT SUM(new_amount) AS amount
                                                                    FROM salary_hike_details shd
                                                                    WHERE shd.salary_hike_fkey = '$salary_hike_pkey'
                                                                    AND shd.emp_fkey = '$emp'
                                                                    AND shd.status = 1
                                                                    AND (
                                                                            shd.item <> 'Y' 
                                                                            OR shd.salary_head_item_fkey IN (
                                                                                SELECT salary_head_item_pkey
                                                                                FROM salary_head_items
                                                                                WHERE head_fkey = '1' 
                                                                                AND item_part = 'Direct' 
                                                                                AND status = '1'
                                                                            )
                                                                        )
                                                                ");

                $total = isset($arr_total[0][0]['amount']) ? $arr_total[0][0]['amount'] : 0;

                $arr_structure_eg_amt = $this->EmployeeConfig->query("SELECT structure_eg_amt
                                                        FROM `salary_structure`
                                                        WHERE `structure_id`= '$new_structure_id'
                                                        AND structure_active = 1;");
                $structure_eg_amt = isset($arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt']) ? $arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt'] : 0;


                // End

                // Edited by Akshay on 21-10-2025
                $branch = isset($fetch_proff['0']['emp_proff']['emp_branch']) ? $fetch_proff['0']['emp_proff']['emp_branch'] : 0;

                // Edited by Akshay on 31-3-2026
                $arr_emp = $this->EmployeeConfig->query("
                SELECT CONCAT(EmpName,' - ',employee_id) AS emp_name
                FROM employee_info 
                WHERE emp_pkey = $emp
            ");

                $emp_name = isset($arr_emp[0][0]['emp_name']) ? $arr_emp[0][0]['emp_name'] : '';

                $arr_statuttory_fields = $this->EmployeeConfig->query("SELECT 
                                                                            GROUP_CONCAT(DISTINCT missing_item SEPARATOR ', ') AS missing_fields
                                                                        FROM (
                                                                            
                                                                            SELECT 'UAN No' AS missing_item
                                                                            FROM salary_structure_details ssd 
                                                                            LEFT JOIN salary_head_items shi 
                                                                                ON shi.salary_head_item_pkey = ssd.salary_head_item_fkey
                                                                            LEFT JOIN emp_details ed 
                                                                                ON ed.emp_pkey = $emp
                                                                            WHERE 
                                                                                ssd.structure_id = '$new_structure_id' 
                                                                                AND ssd.structure_det_value <> 0
                                                                                AND (
                                                                                    UPPER(shi.item) LIKE '%EPF%'
                                                                                    OR UPPER(shi.item) LIKE '%PF%'
                                                                                    OR UPPER(shi.item) LIKE '%PROVIDENT FUND%'
                                                                                )
                                                                                AND (ed.company_pf IS NULL OR TRIM(ed.pf) = '')

                                                                            UNION ALL

                                                                            SELECT 'ESI No'
                                                                            FROM salary_structure_details ssd 
                                                                            LEFT JOIN salary_head_items shi 
                                                                                ON shi.salary_head_item_pkey = ssd.salary_head_item_fkey
                                                                            LEFT JOIN emp_details ed 
                                                                                ON ed.emp_pkey = $emp
                                                                            WHERE 
                                                                                ssd.structure_id = '$new_structure_id' 
                                                                                AND ssd.structure_det_value <> 0
                                                                                AND shi.item LIKE '%ESI%'
                                                                                AND (ed.esi IS NULL OR TRIM(ed.esi) = '')

                                                                            UNION ALL

                                                                            SELECT 'LWF Registration Number'
                                                                            FROM salary_structure_details ssd 
                                                                            LEFT JOIN salary_head_items shi 
                                                                                ON shi.salary_head_item_pkey = ssd.salary_head_item_fkey
                                                                            LEFT JOIN emp_details ed 
                                                                                ON ed.emp_pkey = $emp
                                                                            WHERE 
                                                                                ssd.structure_id = '$new_structure_id' 
                                                                                AND ssd.structure_det_value <> 0
                                                                                AND shi.item LIKE '%LWF%'
                                                                                AND (ed.lwf_code IS NULL OR TRIM(ed.lwf_code) = '')

                                                                            -- UNION ALL

                                                                            -- SELECT 'LWF Registration Number'
                                                                            -- FROM salary_structure_details ssd 
                                                                            -- LEFT JOIN salary_head_items shi 
                                                                            --     ON shi.salary_head_item_pkey = ssd.salary_head_item_fkey
                                                                            -- LEFT JOIN emp_details ed 
                                                                            --     ON ed.emp_pkey = $emp
                                                                            -- WHERE 
                                                                            --     ssd.structure_id = '$new_structure_id' 
                                                                            --     AND ssd.structure_det_value <> 0
                                                                            --     AND shi.item LIKE '%WWF%'
                                                                            --     AND (ed.lwf_code IS NULL OR TRIM(ed.lwf_code) = '')

                                                                            UNION ALL

                                                                            SELECT 'PAN No'
                                                                            FROM salary_structure_details ssd 
                                                                            LEFT JOIN tax_salary_components tsc 
                                                                                ON tsc.salary_head_item_Fkey = ssd.salary_head_item_fkey
                                                                            LEFT JOIN emp_details ed 
                                                                                ON ed.emp_pkey = $emp
                                                                            WHERE 
                                                                                ssd.structure_id = '$new_structure_id' 
                                                                                AND ssd.structure_det_value <> 0
                                                                                AND TRIM(tsc.tax_salary_components_name) = 'TDS'
                                                                                AND (ed.pan_no IS NULL OR TRIM(ed.pan_no) = '')

                                                                        ) t
                                                                    ");

                if (!empty($arr_statuttory_fields[0][0]['missing_fields'])) {
                    $missing_message = "Fields missing for " . $emp_name . " : " . $arr_statuttory_fields[0][0]['missing_fields'];
                    $unfilled_statutotry_fields[] = $missing_message;
                    $response[] = 0;
                    continue;
                }
                // End

                if ($structure_eg_amt > $total) {
                    $wrong_salary_message[] = "Incorrect salary for " . $emp_name;
                    $response[] = 0;
                    continue;
                } else {
                }

                if ($salary_id == 0) {
                    $arr_anual_ctc = $this->EmployeeConfig->query("SELECT emp_anual_ctc FROM emp_ctc_transaction WHERE emp_fkey = '$emp' AND end_date_effective IS NULL;");
                    if (empty($arr_anual_ctc)) {
                        if ($total > 0) {
                            $emp_type = isset($arr_emp_proff[0]['emp_proff']['emp_type']) ? strtoupper(trim($arr_emp_proff[0]['emp_proff']['emp_type'])) : ''; // Edited by Akshay on 27-3-2026
                            $arr_data = array();
                            $arr_data['emp_fkey'] = $emp;
                            $arr_data['emp_anual_ctc'] = ($emp_type == 'DAILY WAGES' || $emp_type == 'HOURLY WAGES') ? $total : $total * 12; // Edited by Akshay on 27-3-2026
                            $arr_data['emp_monthly_ctc'] = $total;
                            $arr_data['arrear_salary'] = 'N';
                            $arr_data['pay_out_month'] = $payout_month;
                            $arr_data['created_by'] = $user_ids;
                            $arr_data['created_date'] = date('Y-m-d H:i:s');
                            $arr_data['start_date_effective'] = $with_effect_from;
                            $arr_data['approved_by'] = $user_ids;
                            $arr_data['next_increment_date'] = isset($arr_shd[0]['salary_hike_details']['next_increment_date']) ? $arr_shd[0]['salary_hike_details']['next_increment_date'] : '0000-00-00';
                            $arr_data['branch'] = $branch;
                            // debug($arr_data);
                            $this->EmployeeCTC->create(); // ensures it's treated as new
                            $result = $this->EmployeeCTC->save($arr_data);
                        } else {
                            continue;
                        }
                    }
                }
                // End

                if (isset($arr_form_data['structure_id']) && $arr_form_data['structure_id'] != $salary_id) {

                    $condition2['type'] = 'SALARY';
                    $condition2['emp_fkey'] = $arr_form_data['emp_fkey'];

                    $salary_id = $arr_form_data['structure_id'];
                    $resp = 0;
                    $error = '@`Perror_massage`';
                    $company = $this->Session->read('company_code');

                    $user_ids = $this->Session->read('login_user_id');
                    $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');


                    try {
                        $proc = $this->EmployeeConfig->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
                        $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
                            "all",
                            array(
                                'fields' => 'emp_salary_structure_pkey,head_operator,remarks,salary_head_item_desc',
                                'conditions' => array(
                                    'emp_structure_id' => $salary_id,
                                    'remarks IS NOT NULL',
                                    'emp_fkey' => $emp,
                                    'end_date_effective is null'
                                )
                            )
                        );

                        foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                            $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey'] : '';
                            $head_operator = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator'] : '';
                            $formula_from_remarks = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) : '';

                            $salary_head_item_desc = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc'] : '';
                            if (!empty($formula_from_remarks)) {
                                $formula_from_remarks = preg_replace('/[A-Za-z_][A-Za-z0-9_]*/', '0', $formula_from_remarks); // Edited by Akshay on 5-5-2026
                                if (preg_match('/^[0-9+\-*\/().\s]+$/', $formula_from_remarks)) {
                                    eval('$salary_amount = ' . $formula_from_remarks . ';');
                                } else {
                                    continue;
                                }

                                if (trim(strtolower($salary_head_item_desc)) == 'esi' || trim(strtolower($salary_head_item_desc)) == 'esi - employee contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employer contribution') {

                                    if ($head_operator == 'Deduction') {
                                        $salary_amount = ceil($salary_amount);
                                    } else {
                                        $salary_amount = round($salary_amount);
                                    }


                                    if ($head_operator == 'Deduction') {
                                        $salary_amount *= -1;
                                    }


                                    $arr_emp_salary_slip_data = array(
                                        'EmployeeSalaryStructure.structure_det_value' => $salary_amount
                                    );
                                } else {
                                    $salary_amount = round($salary_amount);
                                    if ($head_operator == 'Deduction') {
                                        $salary_amount *= -1;
                                    }
                                    $arr_emp_salary_slip_data = array(
                                        'EmployeeSalaryStructure.structure_det_value' => $salary_amount
                                    );
                                }

                                $this->EmployeeSalaryStructure->updateAll(
                                    $arr_emp_salary_slip_data,
                                    array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                                );
                            }
                        }


                        $resp = 1;
                    } catch (Exception $ex) {
                        debug($ex);
                        $resp = 0;
                    }
                    try {
                        $user_ids = $this->Session->read('login_user_id');

                        $data['id'] = 0;
                        $data['emp_fkey'] = $emp;
                        $data['created_by'] = $user_ids;
                        $data['modified_by'] = $user_ids;
                        $data['modification_date '] = date("Y-m-d H:i:s");
                        $data['type'] = 'SALARY';
                        $data['policy_id'] = $arr_form_data['structure_id'];
                        $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $user_ids . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition2);

                        $this->EmployeeConfig->saveAll($data);
                        $prc = $this->EmployeeSalaryStructure->query("call salary_structure_limit_prc('$emp','$user_ids',@`perr_msg`)");
                    } catch (Exception $ex) {
                        $response[] = 0; // Edited by Akshay on 17-10-2025
                    }

                    if ($resp == 1) {
                        $response[] = 1; // Edited by Akshay on 17-10-2025
                    } else {
                        $response[] = 0; // Edited by Akshay on 17-10-2025
                    }
                } else {
                    $response[] = 0; // Edited by Akshay on 17-10-2025
                }
            }
        }

        // Edited by Akshay on 1-4-20206
        $final_missing_message = implode("<br>", $unfilled_statutotry_fields);
        $final_wrong_salary_message = implode("<br>", $wrong_salary_message);
        // End

        // Edited by Akshay on 17-10-2025
        if (array_sum($response) === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update salary structure.',
                'missing_message' => $final_missing_message,
                'wrong_salary_message' => $final_wrong_salary_message,
            ]);
        } elseif (in_array(0, $response)) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update salary structure for some employees.',
                'missing_message' => $final_missing_message,
                'wrong_salary_message' => $final_wrong_salary_message,
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Salary structure updated successfully.',
                'missing_message' => $final_missing_message,
                'wrong_salary_message' => $final_wrong_salary_message,
            ]);
        }
        exit;
        // End
        // End
    }
    
    public function onEffectiveDateChange()
    {
        $this->autoRender = FALSE;
        $company_code = strtoupper($this->Session->read('company_code')); // Edited by Akshay on 4-11-2025
        try {
            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            $empFkey = isset($_POST['empPkey']) ? (int)$_POST['empPkey'] : 0;
            $start_date_effective = isset($_POST['start_date_effective']) ? $_POST['start_date_effective'] : 0;
            if (!empty($start_date_effective)) {
                $date = DateTime::createFromFormat('d-m-Y', $start_date_effective);

                // Edited by Akshay on 4-11-2025
                if ($company_code != 'KWMT') {
                    $formatted_date = $date->format('Y-m-d');
                    $month_result = $this->EmployeeCTC->query("
                                                                SELECT `att_start_end_fn`('$formatted_date', '1') as start_date,
                                                                    `att_start_end_fn`('$formatted_date', '2') as end_date
                                                            ");

                    $start_date = isset($month_result[0][0]['start_date']) ? $month_result[0][0]['start_date'] : null;
                    $end_date = isset($month_result[0][0]['end_date']) ? $month_result[0][0]['end_date'] : null;
                    // Initialize with current month
                    $month = date('Y-m', strtotime($formatted_date));

                    // Adjust month based on boundaries
                    if ($start_date !== null && $formatted_date < $start_date) {
                        // If before start date, go back one month
                        $month = date('Y-m', strtotime('-1 month', strtotime($formatted_date)));
                    } elseif ($end_date !== null && $formatted_date > $end_date) {
                        // If after end date, go forward one month
                        $month = date('Y-m', strtotime('+1 month', strtotime($formatted_date)));
                    }
                } else {
                    $month = $date->format('Y-m');
                }
                // End

                $arr_processed = $this->EmployeeCTC->query(
                    "SELECT COUNT(*) as count FROM payroll_master
                                            WHERE emp_fkey = $empFkey
                                            AND month_year = '$month'
                                            AND action IN ('Approved', 'Processed');"
                );
                $is_processed = $arr_processed[0][0]['count'];

                $arr_processed_action = $this->EmployeeCTC->query(
                    "SELECT action FROM payroll_master
                                            WHERE emp_fkey = $empFkey
                                            AND month_year = '$month'
                                            AND action IN ('Approved', 'Processed');"
                );

                $action = isset($arr_processed_action[0]['payroll_master']['action']) ? strtolower($arr_processed_action[0]['payroll_master']['action']) : '';

                if ($is_processed > 0) {
                    echo json_encode(['status' => 'success', 'is_processed' => true,  'message' => 'Payroll already ' . $action . ' for this month. This will be included in arrear']);
                } else {
                    echo json_encode(['status' => 'success', 'is_processed' => false, 'message' => 'Payroll not yet processed for this month.']);
                }
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function getSalaryStructure($emp_pkey = 0)
    {
        $this->autoRender = FALSE;
        $this->response->type('json');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

        $arr_sal_structure = $this->EmployeeCTC->query("SELECT structure_id, emp_type
                                                            FROM emp_proff
                                                            WHERE emp_fkey = '$emp_pkey'
                                                            LIMIT 1;
                                                            ");
        $structure_id = isset($arr_sal_structure[0]['emp_proff']['structure_id']) ? $arr_sal_structure[0]['emp_proff']['structure_id'] : '';
        $emp_type = isset($arr_sal_structure[0]['emp_proff']['emp_type']) ? $arr_sal_structure[0]['emp_proff']['emp_type'] : ''; // Edited by Akshay on 28-3-2026


        $arr_emp_sal_structure = $this->EmployeeCTC->query("SELECT emp_salary_structure.salary_head_item_fkey, emp_salary_structure.salary_head_item_desc, emp_salary_structure.structure_det_value, salary_heads.head_pkey -- Edited by Akshay on 9-10-2025 
                                                                FROM emp_salary_structure
                                                                LEFT JOIN salary_head_items 
                                                                    ON emp_salary_structure.salary_head_item_fkey = salary_head_items.salary_head_item_pkey
                                                                LEFT JOIN salary_heads 
                                                                    ON salary_head_items.head_fkey = salary_heads.head_pkey
                                                                WHERE emp_fkey = $emp_pkey
                                                                AND TRIM(emp_salary_structure.head_operator) = 'Addition' 
                                                                AND TRIM(emp_salary_structure.item_part) = 'Direct' 
                                                                AND emp_salary_structure.end_date_effective IS NULL
                                                                AND salary_heads.head_pkey = 1; -- Edited by Akshay on 18-11-2025
                                                                ");
        $total_direct_value = 0; // Edited by Akshay on 8-10-2025
        $result = [];
        foreach ($arr_emp_sal_structure as $item) {
            // Edited by Akshay on 8-10-2025
            $key = ($item['emp_salary_structure']['salary_head_item_fkey']) ? $item['emp_salary_structure']['salary_head_item_fkey'] : '';
            $desc = ($item['emp_salary_structure']['salary_head_item_desc']) ? $item['emp_salary_structure']['salary_head_item_desc'] : '';
            $value = $item['emp_salary_structure']['structure_det_value'] ? $item['emp_salary_structure']['structure_det_value'] : 0; // Edited by Akshay on 27-10-2025
            // Edited by Akshay on 9-10-2025
            $head_pkey = $item['salary_heads']['head_pkey'] ? $item['salary_heads']['head_pkey'] : '';
            if ($head_pkey == 1 || $head_pkey == 4) {
                $total_direct_value += $value;
                $monthly = true;
            } else {
                $monthly = false;
            }
            // End
            $result[] = [
                'key' => $key,
                'desc' => $desc,
                'value' => $value,
                'monthly_contr' => $monthly // Edited by Akshay on 9-10-2025
            ];
            // End
        }



        $arr_emp_sal_structure = $this->EmployeeCTC->query("SELECT emp_salary_structure.salary_head_item_fkey, emp_salary_structure.salary_head_item_desc, emp_salary_structure.structure_det_value 
                                                                FROM emp_salary_structure
                                                                LEFT JOIN salary_head_items 
                                                                    ON emp_salary_structure.salary_head_item_fkey = salary_head_items.salary_head_item_pkey
                                                                LEFT JOIN salary_heads 
                                                                    ON salary_head_items.head_fkey = salary_heads.head_pkey
                                                                WHERE emp_fkey = $emp_pkey
                                                                AND TRIM(emp_salary_structure.head_operator) = 'Addition' 
                                                                AND TRIM(emp_salary_structure.item_part) = 'Indirect' 
                                                                AND emp_salary_structure.end_date_effective IS NULL
                                                                AND salary_heads.head_pkey != 1
                                                                AND salary_head_item_fkey IN
                                                                    (
                                                                    select salary_head_item_pkey from salary_head_items where head_fkey in (4,10)
                                                                    )
                                                                ;
                                                                ");

        $result_indirect = [];
        foreach ($arr_emp_sal_structure as $item) {
            $result_indirect[] = [
                'key' => ($item['emp_salary_structure']['salary_head_item_fkey']) ? $item['emp_salary_structure']['salary_head_item_fkey'] : '',
                'desc' => ($item['emp_salary_structure']['salary_head_item_desc']) ? $item['emp_salary_structure']['salary_head_item_desc'] : '',
                'value' => $item['emp_salary_structure']['structure_det_value'] ? $item['emp_salary_structure']['structure_det_value'] : '',
                'monthly_contr' => false // Edited by Akshay on 9-10-2025
            ];
        }


        // Employee contribution
        $arr_emp_sal_structure = $this->EmployeeCTC->query("SELECT emp_salary_structure.salary_head_item_fkey, emp_salary_structure.salary_head_item_desc, emp_salary_structure.structure_det_value 
                                                                FROM emp_salary_structure
                                                                LEFT JOIN salary_head_items 
                                                                    ON emp_salary_structure.salary_head_item_fkey = salary_head_items.salary_head_item_pkey
                                                                LEFT JOIN salary_heads 
                                                                    ON salary_head_items.head_fkey = salary_heads.head_pkey
                                                                WHERE emp_fkey = $emp_pkey
                                                                AND TRIM(emp_salary_structure.head_operator) = 'Deduction' 
                                                                AND TRIM(emp_salary_structure.item_part) = 'Direct' 
                                                                AND emp_salary_structure.end_date_effective IS NULL
                                                                AND salary_heads.head_pkey != 1
                                                                AND salary_head_item_fkey IN
                                                                    (
                                                                    select salary_head_item_pkey from salary_head_items where head_fkey = 5
                                                                    )
                                                                ;
                                                                ");

        $result_emp_contribution = [];
        foreach ($arr_emp_sal_structure as $item) {
            $result_emp_contribution[] = [
                'key' => ($item['emp_salary_structure']['salary_head_item_fkey']) ? $item['emp_salary_structure']['salary_head_item_fkey'] : '',
                'desc' => ($item['emp_salary_structure']['salary_head_item_desc']) ? $item['emp_salary_structure']['salary_head_item_desc'] : '',
                'value' => $item['emp_salary_structure']['structure_det_value'] ? $item['emp_salary_structure']['structure_det_value'] : '',
                'monthly_contr' => false // Edited by Akshay on 9-10-2025
            ];
        }


        $arr_emp_info = $this->EmployeeCTC->query("SELECT emp_pkey, branch, designation, department, joining_date 
                                                        FROM employee_info 
                                                        WHERE emp_pkey = $emp_pkey;
                                                        ");

        $emp = [];
        foreach ($arr_emp_info as $emp_info) {
            foreach ($emp_info as $key => $info) {
                $emp[] = $info;
            }
        }

        if ($structure_id == '') {
            echo json_encode(['success' => true, 'structure_id' => $structure_id, 'structure' => array(), 'structure_indirect' => array(), 'emp_contribution' => array(), 'emp' => $emp[0], 'ctc' => '', 'monthly_gross' => 0]); // Edited by Akshay on 8-10-2025
            return;
        }

        $arr_ctc_result = $this->EmployeeCTC->query("SELECT emp_anual_ctc 
                                                            FROM emp_ctc_transaction 
                                                            WHERE emp_fkey = $emp_pkey 
                                                            -- AND end_date_effective IS NOT NULL
                                                            AND end_date_effective IS NULL -- Edited by Akshay on 27-1-2026 
                                                            ORDER BY end_date_effective DESC 
                                                            LIMIT 1;
                                                        ");
        if (strtoupper(trim($emp_type)) == 'DAILY WAGES' || strtoupper(trim($emp_type)) == 'HOURLY WAGES') {
            $ctc = isset($arr_ctc_result[0]['emp_ctc_transaction']['emp_anual_ctc']) ? ($arr_ctc_result[0]['emp_ctc_transaction']['emp_anual_ctc']) : '';
        } else {
            $ctc = isset($arr_ctc_result[0]['emp_ctc_transaction']['emp_anual_ctc']) ? ($arr_ctc_result[0]['emp_ctc_transaction']['emp_anual_ctc'] / 12) : '';
        }

        echo json_encode(['success' => true, 'structure_id' => $structure_id, 'structure' => $result, 'structure_indirect' => $result_indirect, 'emp_contribution' => $result_emp_contribution, 'emp' => $emp[0], 'ctc' => $ctc, 'monthly_gross' => $total_direct_value]); // Edited by Akshay on 8-10-2025
        return;
    }

    public function componentAllocate($sal_fkey = 0)
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $condition = array("emp_pkey not in (select emp_fkey from component_increment_allocate where sal_fkey=$sal_fkey and status=1)");
        $this->set("sal_fkey", $sal_fkey);

        // Run the query to get the emp_fkey values
        $emp_fkeys = $this->EmployeeDetails->query('SELECT DISTINCT emp_fkey FROM emp_salary_structure WHERE end_date_effective IS NULL');

        // Extract the emp_fkey values from the result
        $emp_fkey_values = array_map(function ($row) {
            return $row['emp_salary_structure']['emp_fkey'];
        }, $emp_fkeys);

        //The below code is to display only unallocated employees to the specified store in employee list. By ***ARUL P DAS on 17/1/2020
        // $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1, $condition)));
        $arr_employees = $this->EmployeeDetails->find('all', array(
            'conditions' => array(
                'EmployeeDetails.status' => 1,
                'EmployeeDetails.emp_pkey IN' => $emp_fkey_values
            ),
            'joins' => array(
                array(
                    'table' => 'emp_proff',
                    'alias' => 'EmpProff',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'EmpProff.emp_fkey = EmployeeDetails.emp_pkey'
                    )
                )
            ),
            'fields' => array(
                'EmployeeDetails.*',
                'EmpProff.emp_company_id'
            ),
            'order' => array('EmployeeDetails.first_name' => 'ASC')
        ));


        $this->set("arr_employees", $arr_employees);
        //query edited by ***ARUL P DAS on 17/1/2020
        $arr_employees_allocates = $this->EmployeeDetails->query("select distinct emp_fkey,emp_details.first_name,last_name from component_increment_allocate join emp_details on (emp_details.emp_pkey = component_increment_allocate.emp_fkey) where sal_fkey = '$sal_fkey' and component_increment_allocate.status = '1'");
        $this->set("arr_employees_allocates", $arr_employees_allocates);
    }
    public function saveAllocate()
    {
        try {
            $this->autoRender = FALSE;
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

            $this->layout = null;
            $result = array('success' => 0);
            $arr_form_data = $this->request->data;
            $user = $this->Session->read('login_user_id');
            // debug($arr_form_data);
            // exit;
            $arr_emps = $arr_form_data['emps'];
            $sal_fkey = $arr_form_data['sal_fkey'];

            $resp_att = array();
            foreach ($arr_emps as $emps) {
                $cnt = $this->EmployeeDetails->query("select count(*) as count from component_increment_allocate where sal_fkey='$sal_fkey' and emp_fkey='$emps' and status=1 ");
                $count = $cnt['0']['0']['count'];

                // if ($count == 0) {
                if (true) {
                    try {

                        $arr_emp_proff = $this->EmployeeDetails->query("SELECT joining_date, designation, emp_dept, emp_branch FROM emp_proff WHERE emp_fkey = '$emps';");
                        $joining_date = isset($arr_emp_proff[0]['emp_proff']['joining_date']) ? $arr_emp_proff[0]['emp_proff']['joining_date'] : '';
                        $designation_code      = isset($arr_emp_proff[0]['emp_proff']['designation']) ? $arr_emp_proff[0]['emp_proff']['designation'] : '';
                        $dept_code     = isset($arr_emp_proff[0]['emp_proff']['emp_dept']) ? $arr_emp_proff[0]['emp_proff']['emp_dept'] : '';
                        $branch_code   = isset($arr_emp_proff[0]['emp_proff']['emp_branch']) ? $arr_emp_proff[0]['emp_proff']['emp_branch'] : '';

                        $this->EmployeeDetails->query("
                                                        INSERT INTO component_increment_allocate (
                                                            sal_fkey,
                                                            emp_fkey,
                                                            joining_date,
                                                            designation_code,
                                                            dept_code,
                                                            branch_code,
                                                            created_by
                                                        ) VALUES (
                                                            '$sal_fkey',
                                                            '$emps',
                                                            '$joining_date',
                                                            '$designation_code',
                                                            '$dept_code',
                                                            '$branch_code',
                                                            '$user'
                                                        )
                                                    ");

                        $resp_att['success'] = 1;
                        $resp_att['msg'] = "Saved Successfully.";
                        echo json_encode($resp_att);
                    } catch (Exception $e) {
                        debug($e);
                        $resp_att['success'] = 0;
                        $resp_att['msg'] = "Saving failed.";
                        echo json_encode($resp_att);
                    }
                } else {
                    $resp_att['success'] = 0;
                    $resp_att['msg'] = "Employee already exists.";
                    echo json_encode($resp_att);
                }
            }
        } catch (Exception $e) {
            debug($e);
        }
    }

    public function removeAllocate()
    {
        $this->autoRender = FALSE;
        // $this->Store->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;

        $emps = $arr_form_data['emps'];
        $sal_fkey = $arr_form_data['sal_fkey'];

        $resp_att = array();
        try {
            $this->EmployeeDetails->query("update component_increment_allocate set status = '0' where sal_fkey = '$sal_fkey' and emp_fkey = '$emps' ");
            $resp_att['success'] = 1;
            $resp_att['msg'] = "Removed Successfully ";
            echo json_encode($resp_att);
        } catch (Exception $e) {
            $resp_att['success'] = 0;
            $resp_att['msg'] = "Saving failed ";
            echo json_encode($resp_att);
        }
    }

    public function getEmployeesByTypeValue()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $type = $this->request->data('type');
        $value = $this->request->data('value');

        // Mapping type to field name
        $map = [
            'Branch' => 'emp_branch',
            'Department' => 'emp_dept',
            'Designation' => 'designation',
            'Joining date' => 'joining_date',
            'Grade' => 'emp_grade',
            'Employee Type' => 'emp_type',
            'Category' => 'termination'
        ];

        $field = isset($map[$type]) ? $map[$type] : null;
        if (!$field) {
            echo json_encode(['success' => false, 'data' => []]);
            return;
        }

        // Raw SQL Query to get employee details based on type and value
        if ($field != 'termination') {
            $sql = "
                    SELECT ed.emp_pkey, CONCAT_WS(' ', COALESCE(ed.first_name, ''), COALESCE(ed.last_name, ''), '-', COALESCE(ep.emp_company_id, '')) AS name 
                    FROM emp_details ed 
                    INNER JOIN emp_proff ep ON ed.emp_pkey = ep.emp_fkey 
                    WHERE ep.`$field` = '$value'
                    AND status = 1
                    ORDER BY ed.first_name ASC;
                ";
        } else {
            if ($value == 'separated') {
                $condition = " AND EXISTS (SELECT 1 FROM termination t WHERE t.emp_fkey = ed.emp_pkey AND t.status = 1) ";
            } else {
                $condition = " AND NOT EXISTS (SELECT 1 FROM termination t WHERE t.emp_fkey = ed.emp_pkey AND t.status = 1) ";
            }

            $sql = "
                    SELECT ed.emp_pkey, CONCAT_WS(' ', COALESCE(ed.first_name, ''), COALESCE(ed.last_name, ''), '-', COALESCE(ep.emp_company_id, '')) AS name 
                    FROM emp_details ed 
                    JOIN emp_proff ep ON ep.emp_fkey = ed.emp_pkey
                    WHERE ed.status = 1
                    $condition
                    ORDER BY ed.first_name ASC;
                ";
        }


        // Using query method to execute the SQL
        $results = $this->EmployeeDetails->query($sql);

        // Mapping the results to the required format
        $employees = [];
        foreach ($results as $row) {
            // Ensure the correct access to the columns
            $employees[] = ['emp_pkey' => $row['ed']['emp_pkey'], 'name' => $row['0']['name']];
        }

        // Returning the result as a JSON response
        echo json_encode(['success' => true, 'data' => $employees]);
    }




    public function getTypeValues()
    {
        $this->autoRender = false;
        $type = $this->request->data('type');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $data = [];

        switch ($type) {
            case 'Employee':
                $sql = "SELECT DISTINCT 
                            CONCAT_WS(' ', COALESCE(first_name, ''), COALESCE(last_name, '')) AS selected_value,
                            emp_pkey AS key_value
                        FROM emp_details selcted_type
                        LEFT JOIN emp_proff ep 
                            ON ep.emp_fkey = emp_pkey 
                            AND ep.structure_id IS NULL
                        WHERE 
                            selcted_type.status = 1
                            AND ep.emp_fkey IS NULL
                        ORDER BY selected_value;";
                break;
            case 'Branch':
                $sql = "SELECT DISTINCT branch_name as selected_value, branch_code as key_value FROM branches selcted_type WHERE status = 1 ORDER BY branch_name";
                break;
            case 'Department':
                $sql = "SELECT DISTINCT dept_name as selected_value , dept_code as key_value FROM department selcted_type WHERE status = 1 ORDER BY dept_name";
                break;
            case 'Designation':
                $sql = "SELECT DISTINCT desig_name as selected_value, desig_code as key_value FROM designation selcted_type WHERE status = 1 ORDER BY desig_name";
                break;
            case 'Joining date':
                $sql = "SELECT DISTINCT joining_date as selected_value FROM emp_proff selcted_type WHERE joining_date IS NOT NULL ORDER BY joining_date";
                break;
            case 'Grade':
                $sql = "SELECT DISTINCT grade_pkey as key_value, CONCAT(grade_name, ' - ' ,pay_scale) as selected_value FROM grade selcted_type WHERE status = 1 ORDER BY grade_name";
                break;
            case 'Employee Type':
                $sql = "SELECT DISTINCT emp_type as key_value, emp_type as selected_value FROM emp_proff selcted_type WHERE emp_type IS NOT NULL AND emp_type <> '' ORDER BY emp_type";
                break;
            // Edited by Akshay on 26-9-2025
            case 'Category':
                echo json_encode(['success' => true, 'data' => ["not-separated" => "Not Separated", "separated" => "Separated"]]);
                return;
                // End
            default:
                echo json_encode(['success' => false, 'data' => []]);
                return;
        }

        $results = $this->EmployeeDetails->query($sql);
        foreach ($results as $row) {
            // debug($row);
            $value = isset($row['selcted_type']['selected_value']) ? $row['selcted_type']['selected_value'] : (isset($row[0]['selected_value']) ? $row[0]['selected_value'] : ''); // get the first column
            if ($type === 'Joining date') {
                $key = date('Y-m-d', strtotime($value));
                $data[$key] = $type === 'Joining date' ? date('d-m-Y', strtotime($value)) : $value;
            } else {
                $key = $row['selcted_type']['key_value'];
                $data[$key] = $value;
            }
        }

        echo json_encode(['success' => !empty($data), 'data' => $data]);
    }

    public function saveComponentAllocate()
    {
        try {
            $this->autoRender = FALSE;
            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
            $this->layout = null;
            $result = array('success' => 0);
            $company = $this->Session->read('company_code');
            $user_id = $this->Session->read("login_user_id"); //user id
            $arr_form_data = $this->request->data;
            // debug($arr_form_data);exit;
            $arr_emps = $arr_form_data['emps'];
            $sal_fkey = $arr_form_data['sal_fkey'];
            $apply_to = $arr_form_data['apply_to'];
            $hike = $arr_form_data['hike'];
            $user = $this->Session->read('login_user_id');

            $response = array('success' => 0, 'msg' => 'Failed to allocate employees.');

            if (!empty($arr_emps)) {
                if ($apply_to == 'all') {

                    foreach ($arr_emps as $emps) {
                        // $this->EmployeeCTC->query("UPDATE component_increment_allocate SET status = 0 WHERE sal_fkey = '$sal_fkey'"); // set status to 0
                        // Allocate employees
                        $cnt = $this->EmployeeCTC->query("select count(*) as count from component_increment_allocate where sal_fkey='$sal_fkey' and emp_fkey='$emps' and status=1 ");
                        $count = $cnt['0']['0']['count'];

                        // if ($count == 0) {
                        if (true) {
                            try {

                                $arr_emp_proff = $this->EmployeeCTC->query("SELECT joining_date, designation, emp_dept, emp_branch FROM emp_proff WHERE emp_fkey = '$emps';");
                                $joining_date = isset($arr_emp_proff[0]['emp_proff']['joining_date']) ? $arr_emp_proff[0]['emp_proff']['joining_date'] : '';
                                $designation_code      = isset($arr_emp_proff[0]['emp_proff']['designation']) ? $arr_emp_proff[0]['emp_proff']['designation'] : '';
                                $dept_code     = isset($arr_emp_proff[0]['emp_proff']['emp_dept']) ? $arr_emp_proff[0]['emp_proff']['emp_dept'] : '';
                                $branch_code   = isset($arr_emp_proff[0]['emp_proff']['emp_branch']) ? $arr_emp_proff[0]['emp_proff']['emp_branch'] : '';

                                $this->EmployeeCTC->query("
                                                                INSERT INTO component_increment_allocate (
                                                                    sal_fkey,
                                                                    emp_fkey,
                                                                    joining_date,
                                                                    designation_code,
                                                                    dept_code,
                                                                    branch_code,
                                                                    created_by
                                                                ) VALUES (
                                                                    '$sal_fkey',
                                                                    '$emps',
                                                                    '$joining_date',
                                                                    '$designation_code',
                                                                    '$dept_code',
                                                                    '$branch_code',
                                                                    '$user'
                                                                )
                                                            ");
                            } catch (Exception $e) {
                                debug($e);
                            }
                        } else {
                        }


                        $arr_ctc =  $this->EmployeeCTC->query("SELECT emp_anual_ctc FROM emp_ctc_transaction WHERE emp_fkey = '$emps' AND end_date_effective IS NULL;");
                        $ctc = isset($arr_ctc[0]['emp_ctc_transaction']['emp_anual_ctc']) ? $arr_ctc[0]['emp_ctc_transaction']['emp_anual_ctc'] : '';
                        if ($ctc != '') {
                            $arr_update_data = array();
                            $arr_update_data['emp_fkey'] = $emps;
                            $arr_update_data['created_by'] = $user_id;
                            $arr_update_data['emp_anual_ctc'] = $new_value = round($ctc * (1 + ((float)$hike / 100)));
                            $arr_update_data['start_date_effective'] = date("Y-m-1");
                            $result = $this->EmployeeCTC->save($arr_update_data);

                            $arr_sal_id = $this->EmployeeCTC->query("SELECT emp_structure_id FROM emp_salary_structure ess WHERE emp_fkey = '$emps' AND end_date_effective IS NULL;");
                            $salary_id = isset($arr_sal_id[0]['ess']['emp_structure_id']) ? $arr_sal_id[0]['ess']['emp_structure_id'] : '';
                            // Edit salary strucure
                            $proc = $this->EmployeeCTC->query("select sal_structure_distribution_fn('$company',$emps,$salary_id,'$user_id') as function");
                            $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
                                "all",
                                array(
                                    'fields' => 'emp_salary_structure_pkey,head_operator,remarks,salary_head_item_desc',
                                    'conditions' => array(
                                        'emp_structure_id' => $salary_id,
                                        'remarks IS NOT NULL',
                                        'emp_fkey' => $emps,
                                        'end_date_effective is null'
                                    )
                                )
                            );

                            foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                                $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey'] : '';
                                $head_operator = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator'] : '';
                                $formula_from_remarks = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) : '';

                                $salary_head_item_desc = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_desc'] : '';

                                if (!empty($formula_from_remarks)) {
                                    eval('$salary_amount = ' . $formula_from_remarks . ';');

                                    if (trim(strtolower($salary_head_item_desc)) == 'esi' || trim(strtolower($salary_head_item_desc)) == 'esi - employee contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employer contribution') {

                                        if ($head_operator == 'Deduction') {
                                            $salary_amount = ceil($salary_amount); //Edited by Akshay on 30-4-2024
                                        } else {
                                            $salary_amount = round($salary_amount); //Edited by Akshay on 30-4-2024
                                        }


                                        if ($head_operator == 'Deduction') {
                                            $salary_amount *= -1;
                                        }

                                        $arr_emp_salary_slip_data = array(
                                            'EmployeeSalaryStructure.structure_det_value' => $salary_amount //edited by sinsiya on 26-09-2024
                                        );
                                    } else {
                                        $salary_amount = round($salary_amount);
                                        if ($head_operator == 'Deduction') {
                                            $salary_amount *= -1;
                                        }
                                        $arr_emp_salary_slip_data = array(
                                            'EmployeeSalaryStructure.structure_det_value' => $salary_amount //edited by sinsiya on 26-09-2024
                                        );
                                    }
                                    //  debug($arr_emp_salary_slip_data);
                                    $this->EmployeeSalaryStructure->updateAll(
                                        $arr_emp_salary_slip_data,
                                        array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                                    );
                                }
                            }

                            // ================================

                            $lastInsertId = $this->EmployeeCTC->query("SELECT `index` FROM emp_alteration WHERE affected_emp = '$emps' AND is_new = 'Y';");
                            $lastInsertId = isset($lastInsertId[0]['emp_alteration']['index']) ? $lastInsertId[0]['emp_alteration']['index'] : 0;
                            if ($lastInsertId != 0) {
                                $sql = "INSERT INTO emp_alteration_details (`index`,table_name,front_end_name, field, old_value, new_value, affected_emp, created_by, menu, module) 
                                VALUES ('$lastInsertId','emp_ctc_transaction','Annual Salary', 'emp_anual_ctc', '', '$new_value', '$emps', '$user_id', 'Employee setup', 'Add Employee')";
                                $result = $this->EmployeeCTC->query($sql);
                            }
                        }
                    }
                }
                $response = array('success' => 1, 'msg' => 'Employees allocated successfully.');
            } else {
                $response['msg'] = 'No employees selected for allocation.';
            }
        } catch (Exception $e) {
            debug($e);
            $response['msg'] = 'Error: ' . $e->getMessage();
        }
        echo json_encode($response);
        exit;
    }

    public function saveComponentUploads()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->EmpSalaryCompUpload->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_emps = $arr_form_data['emps'];
        $salary_head_item_fkey = $arr_form_data['component'];
        $hike = $arr_form_data['hike'];
        $sal_fkey = $arr_form_data['sal_fkey'];
        $user = $this->Session->read('login_user_id');
        $company = strtoupper($this->Session->read('company_code'));

        $arr_salary_head_item_fkey = $this->EmployeeDetails->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'Basic';");
        $basic = $arr_salary_head_item_fkey[0]['tax_salary_components']['salary_head_item_Fkey'];

        $arr_vda = $this->EmployeeDetails->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'VDA';");
        $vda = isset($arr_vda[0]['tax_salary_components']['salary_head_item_Fkey']) ? $arr_vda[0]['tax_salary_components']['salary_head_item_Fkey'] : '';

        $arr_hra = $this->EmployeeDetails->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'House Rent Allowance (HRA)';");
        $hra = isset($arr_hra[0]['tax_salary_components']['salary_head_item_Fkey']) ? $arr_hra[0]['tax_salary_components']['salary_head_item_Fkey'] : '';

        foreach ($arr_emps as $emp_key) {
            // Insert into component upload
            // $this->EmployeeDetails->query("UPDATE component_increment_allocate SET status = 0 WHERE sal_fkey = '$sal_fkey'"); // set status to 0
            // Allocate employees
            $cnt = $this->EmployeeDetails->query("select count(*) as count from component_increment_allocate where sal_fkey='$sal_fkey' and emp_fkey='$emp_key' and status=1 ");
            $count = $cnt['0']['0']['count'];

            if (true) {
                try {

                    $arr_emp_proff = $this->EmployeeDetails->query("SELECT joining_date, designation, emp_dept, emp_branch FROM emp_proff WHERE emp_fkey = '$emp_key';");
                    $joining_date = isset($arr_emp_proff[0]['emp_proff']['joining_date']) ? $arr_emp_proff[0]['emp_proff']['joining_date'] : '';
                    $designation_code      = isset($arr_emp_proff[0]['emp_proff']['designation']) ? $arr_emp_proff[0]['emp_proff']['designation'] : '';
                    $dept_code     = isset($arr_emp_proff[0]['emp_proff']['emp_dept']) ? $arr_emp_proff[0]['emp_proff']['emp_dept'] : '';
                    $branch_code   = isset($arr_emp_proff[0]['emp_proff']['emp_branch']) ? $arr_emp_proff[0]['emp_proff']['emp_branch'] : '';

                    $this->EmployeeDetails->query("
                                                    INSERT INTO component_increment_allocate (
                                                        sal_fkey,
                                                        emp_fkey,
                                                        joining_date,
                                                        designation_code,
                                                        dept_code,
                                                        branch_code,
                                                        created_by
                                                    ) VALUES (
                                                        '$sal_fkey',
                                                        '$emp_key',
                                                        '$joining_date',
                                                        '$designation_code',
                                                        '$dept_code',
                                                        '$branch_code',
                                                        '$user'
                                                    )
                                                ");
                } catch (Exception $e) {
                    debug($e);
                }
            } else {
            }

            $data = $this->EmployeeDetails->query("select emp_id from emp_details where emp_pkey = $emp_key");
            $emp_id = $data['0']['emp_details']['emp_id'];

            $arr_structure_det_value = $this->EmployeeDetails->query("SELECT structure_det_value FROM emp_salary_structure 
                                            WHERE emp_fkey = '$emp_key'
                                            AND salary_head_item_fkey = '$salary_head_item_fkey'
                                            AND end_date_effective IS NULL;
                                            ");
            $value =  isset($arr_structure_det_value[0]['emp_salary_structure']['structure_det_value']) ? $arr_structure_det_value[0]['emp_salary_structure']['structure_det_value'] : '';

            $item = $this->SalaryHeadItems->query("select item from salary_head_items where salary_head_item_pkey = $salary_head_item_fkey");
            $s_item = $item['0']['salary_head_items']['item'];

            $out['emp_id'] = $emp_id;
            $out['salary_head_item_fkey'] = $salary_head_item_fkey;
            if ($value == '') {
                $out['rate'] = 0;
            } else {
                if (($company == 'KWMT' || $company == 'GLET' || $company == 'GTRA') && ($salary_head_item_fkey == $vda || $salary_head_item_fkey == $hra)) {
                    $arr_basic_value = $this->EmployeeDetails->query("SELECT structure_det_value FROM emp_salary_structure 
                                                                                    WHERE emp_fkey = '$emp_key'
                                                                                    AND salary_head_item_fkey IN (SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'Basic')
                                                                                    AND end_date_effective IS NULL;
                                                                                    ");
                    $basic_value = isset($arr_basic_value[0]['emp_salary_structure']['structure_det_value']) ? $arr_basic_value[0]['emp_salary_structure']['structure_det_value'] : '';

                    // $value = round($basic_value * (1 + ((float)$hike / 100)));
                    $value = round($basic_value * ((float)$hike / 100));
                } else {
                    $value = round($value * (1 + ((float)$hike / 100)));
                }

                if (($company == 'KWMT' || $company == 'GLET' || $company == 'GTRA') && $salary_head_item_fkey == $basic) {
                    if ($value  % 10 !== 0) {
                        $value = ceil($value / 10) * 10;
                    }
                }

                $out['rate'] = $value;
            }
            $out['component'] = $s_item;
            $out['created_by'] = $this->Session->read('login_user_id');

            $this->EmpSalaryCompUpload->query("
                    UPDATE emp_salcomp_upload 
                    SET status = 0 
                    WHERE emp_id = '$emp_id' 
                    AND salary_head_item_fkey = '$salary_head_item_fkey' 
                    AND status = 1
                ");

            $result = $this->EmpSalaryCompUpload->saveAll($out);



            //****structure changes by megha adding distribution of salary on 20/04/2022****
            $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
            $arr_usercredentials = $this->EmployeeDetails->find('first', array(
                'fields' => 'emp_pkey',
                'conditions' => array(
                    'emp_id' => $emp_id
                )
            ));

            $emp = isset($arr_usercredentials['EmployeeDetails']['emp_pkey']) ? $arr_usercredentials['EmployeeDetails']['emp_pkey'] : '';

            $arr_process = $this->EmployeeDetails->query("CALL `ctc_component_upload_prc`('$emp', @pmessage);");
            $this->EmployeeDetails->query("UPDATE emp_ctc_transaction
                                SET ctc_upload_type = 1
                                WHERE emp_fkey = $emp
                                AND ctc_upload_type = 2
                                AND end_date_effective IS NULL;");
            // debug($arr_process);
            $salary = $this->EmployeeSalaryStructure->find(
                "all",
                array(
                    'fields' => 'emp_structure_id',
                    'conditions' => array(
                        'emp_fkey' => $emp,
                        'end_date_effective is null'
                    )
                )
            );

            $company = $this->Session->read('company_code');
            $user_ids = $this->Session->read('login_user_id');

            $salary_id = isset($salary['0']['EmployeeSalaryStructure']['emp_structure_id']) ? $salary['0']['EmployeeSalaryStructure']['emp_structure_id'] : "''";
            // $proc = $this->EmployeeSalaryStructure->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
            $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
                "all",
                array(
                    'fields' => 'emp_salary_structure_pkey,head_operator,remarks',
                    'conditions' => array(
                        'emp_structure_id' => $salary_id,
                        'emp_fkey' => $emp,
                        'remarks IS NOT NULL',
                        'end_date_effective is null'
                    )
                )
            );
            foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey'] : '';
                $head_operator = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator'] : '';
                $formula_from_remarks = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) : '';
                if (!empty($formula_from_remarks)) {
                    $salary_amount = '0';
                    eval('$salary_amount = ' . $formula_from_remarks . ';');
                    if ($head_operator == 'Deduction') {
                        $salary_amount *= -1;
                    }
                    $arr_emp_salary_slip_data = array(
                        'EmployeeSalaryStructure.structure_det_value' => round($salary_amount)
                    );
                    $this->EmployeeSalaryStructure->updateAll(
                        $arr_emp_salary_slip_data,
                        array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                    );
                }
            }
            $prc = $this->EmployeeSalaryStructure->query("call salary_structure_limit_prc('$emp','$user_ids',@`perr_msg`)");
        }

        $resp = array();
        $resp["success"] = 1;
        $resp["msg"] = "Salary component saved successfully.";
        echo json_encode($resp);
    }

    public function saveComponentAllocateKWMT()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->EmpSalaryCompUpload->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_emps = $arr_form_data['emps'];
        $salary_head_item_fkey = $arr_form_data['component'];
        $hike = $arr_form_data['hike'];
        $sal_fkey = $arr_form_data['sal_fkey'];
        $user = $this->Session->read('login_user_id');
        $company = strtoupper($this->Session->read('company_code'));

        $arr_salary_head_item_fkey = $this->EmployeeCTC->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'Basic';");
        $basic = $arr_salary_head_item_fkey[0]['tax_salary_components']['salary_head_item_Fkey'];
        $basic_value = 0;

        $arr_hra = $this->EmployeeCTC->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'House Rent Allowance (HRA)';");
        $hra = isset($arr_hra[0]['tax_salary_components']['salary_head_item_Fkey']) ? $arr_hra[0]['tax_salary_components']['salary_head_item_Fkey'] : '';

        if ($company == 'KWMT' || $company == 'GTRA') {
            $arr_vda = $this->EmployeeCTC->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'VDA';");
        } else {
            $arr_vda = $this->EmployeeCTC->query("SELECT salary_head_item_Fkey FROM tax_salary_components WHERE TRIM(tax_salary_components_name) = 'Dearness Allowance (DA)';");
        }

        $vda = isset($arr_vda[0]['tax_salary_components']['salary_head_item_Fkey']) ? $arr_vda[0]['tax_salary_components']['salary_head_item_Fkey'] : '';

        $response = array('success' => 0, 'msg' => 'Failed to allocate employees.');

        if (!empty($arr_emps)) {
            foreach ($arr_emps as $emp_key) {
                // insert into component allocate
                // $this->EmployeeDetails->query("UPDATE component_increment_allocate SET status = 0 WHERE sal_fkey = '$sal_fkey'"); // set status to 0
                // Allocate employees
                $cnt = $this->EmployeeDetails->query("select count(*) as count from component_increment_allocate where sal_fkey='$sal_fkey' and emp_fkey='$emp_key' and status=1 ");
                $count = $cnt['0']['0']['count'];

                // if ($count == 0) {
                if (true) {
                    try {

                        $arr_emp_proff = $this->EmployeeDetails->query("SELECT joining_date, designation, emp_dept, emp_branch FROM emp_proff WHERE emp_fkey = '$emp_key';");
                        $joining_date = isset($arr_emp_proff[0]['emp_proff']['joining_date']) ? $arr_emp_proff[0]['emp_proff']['joining_date'] : '';
                        $designation_code      = isset($arr_emp_proff[0]['emp_proff']['designation']) ? $arr_emp_proff[0]['emp_proff']['designation'] : '';
                        $dept_code     = isset($arr_emp_proff[0]['emp_proff']['emp_dept']) ? $arr_emp_proff[0]['emp_proff']['emp_dept'] : '';
                        $branch_code   = isset($arr_emp_proff[0]['emp_proff']['emp_branch']) ? $arr_emp_proff[0]['emp_proff']['emp_branch'] : '';

                        $this->EmployeeDetails->query("
                                                        INSERT INTO component_increment_allocate (
                                                            sal_fkey,
                                                            emp_fkey,
                                                            joining_date,
                                                            designation_code,
                                                            dept_code,
                                                            branch_code,
                                                            created_by
                                                        ) VALUES (
                                                            '$sal_fkey',
                                                            '$emp_key',
                                                            '$joining_date',
                                                            '$designation_code',
                                                            '$dept_code',
                                                            '$branch_code',
                                                            '$user'
                                                        )
                                                    ");
                    } catch (Exception $e) {
                        debug($e);
                    }
                } else {
                }

                $data = $this->EmployeeDetails->query("select emp_id from emp_details where emp_pkey = $emp_key");
                $emp_id = $data['0']['emp_details']['emp_id'];

                $arr_items =  $this->EmployeeCTC->query("SELECT * FROM emp_salary_structure
                                                            WHERE emp_fkey = '$emp_key'
                                                            AND end_date_effective IS NULL
                                                            AND head_operator = 'Addition'
                                                            -- AND item_part = 'Direct'
                                                            AND remarks IS NULL
                                                            ORDER BY salary_head_item_fkey ASC
                                                            ;");
                // debug($arr_items);
                $ctc = 0;
                $basic_value = 0;
                foreach ($arr_items as $items) {
                    $salary_head_item_fkey = $items['emp_salary_structure']['salary_head_item_fkey'];
                    // $arr_structure_det_value = $this->EmployeeDetails->query("SELECT structure_det_value FROM emp_salary_structure 
                    //                                                                 WHERE emp_fkey = '$emp_key'
                    //                                                                 AND salary_head_item_fkey = '$salary_head_item_fkey'
                    //                                                                 AND end_date_effective IS NULL;
                    //                                                                 ");
                    $value = isset($items['emp_salary_structure']['structure_det_value']) ? $items['emp_salary_structure']['structure_det_value'] : 0;
                    // $value =  isset($arr_structure_det_value[0]['emp_salary_structure']['structure_det_value']) ? $arr_structure_det_value[0]['emp_salary_structure']['structure_det_value'] : '';

                    $item = $this->SalaryHeadItems->query("select item from salary_head_items where salary_head_item_pkey = $salary_head_item_fkey");
                    $s_item = $item['0']['salary_head_items']['item'];

                    $out['emp_id'] = $emp_id;
                    $out['salary_head_item_fkey'] = $salary_head_item_fkey;
                    if ($value == '') {
                        $out['rate'] = 0;
                    } else {
                        if ($salary_head_item_fkey == $basic) {
                            $value = round($value * (1 + ((float)$hike / 100)));
                            // If $value is not a multiple of 10, round it up to the next multiple of 10
                            if ($value % 10 !== 0) {
                                $value = ceil($value / 10) * 10;
                            }
                            $basic_value = $value;
                        } elseif ($salary_head_item_fkey == $hra) {
                            $value = $basic_value * (.2);
                        } elseif ($salary_head_item_fkey == $vda) {
                            $value = $basic_value * (2.285);
                        }

                        $out['rate'] = $value;
                    }
                    $out['component'] = $s_item;
                    $out['created_by'] = $this->Session->read('login_user_id');

                    $this->EmpSalaryCompUpload->query("
                    UPDATE emp_salcomp_upload 
                    SET status = 0 
                    WHERE emp_id = '$emp_id' 
                    AND salary_head_item_fkey = '$salary_head_item_fkey' 
                    AND status = 1
                    ");

                    $result = $this->EmpSalaryCompUpload->saveAll($out);



                    //****structure changes by megha adding distribution of salary on 20/04/2022****
                    $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
                    $arr_usercredentials = $this->EmployeeDetails->find('first', array(
                        'fields' => 'emp_pkey',
                        'conditions' => array(
                            'emp_id' => $emp_id
                        )
                    ));

                    $emp = isset($arr_usercredentials['EmployeeDetails']['emp_pkey']) ? $arr_usercredentials['EmployeeDetails']['emp_pkey'] : '';

                    $arr_process = $this->EmployeeDetails->query("CALL `ctc_component_upload_prc`('$emp', @pmessage);");
                    $this->EmployeeDetails->query("UPDATE emp_ctc_transaction
                                SET ctc_upload_type = 1
                                WHERE emp_fkey = $emp
                                AND ctc_upload_type = 2
                                AND end_date_effective IS NULL;");
                    // debug($arr_process);
                    $salary = $this->EmployeeSalaryStructure->find(
                        "all",
                        array(
                            'fields' => 'emp_structure_id',
                            'conditions' => array(
                                'emp_fkey' => $emp,
                                'end_date_effective is null'
                            )
                        )
                    );

                    $company = $this->Session->read('company_code');
                    $user_ids = $this->Session->read('login_user_id');

                    $salary_id = isset($salary['0']['EmployeeSalaryStructure']['emp_structure_id']) ? $salary['0']['EmployeeSalaryStructure']['emp_structure_id'] : "''";
                    // $proc = $this->EmployeeSalaryStructure->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
                    $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
                        "all",
                        array(
                            'fields' => 'emp_salary_structure_pkey,head_operator,remarks',
                            'conditions' => array(
                                'emp_structure_id' => $salary_id,
                                'emp_fkey' => $emp,
                                'remarks IS NOT NULL',
                                'end_date_effective is null'
                            )
                        )
                    );

                    foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                        $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['emp_salary_structure_pkey'] : '';
                        $head_operator = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['head_operator'] : '';
                        $formula_from_remarks = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmployeeSalaryStructure']['remarks']) : '';
                        $key = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_fkey']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['salary_head_item_fkey'] : '';
                        if (!empty($formula_from_remarks)) {
                            $salary_amount = '0';
                            eval('$salary_amount = ' . $formula_from_remarks . ';');
                            if ($head_operator == 'Deduction') {
                                $salary_amount *= -1;
                            }

                            if ($key == $basic) {
                                $salary_amount = round($salary_amount * (1 + ((float)$hike / 100)));
                                // If $value is not a multiple of 10, round it up to the next multiple of 10
                                if ($salary_amount % 10 !== 0) {
                                    $salary_amount = ceil($salary_amount / 10) * 10;
                                }
                                $basic_value = $salary_amount;
                            } elseif ($key == $hra) {
                                $salary_amount = $basic_value * (.2);
                            } elseif ($key == $vda) {
                                $salary_amount = $basic_value * (2.285);
                            }

                            $arr_emp_salary_slip_data = array(
                                'EmployeeSalaryStructure.structure_det_value' => round($salary_amount)
                            );
                            $this->EmployeeSalaryStructure->updateAll(
                                $arr_emp_salary_slip_data,
                                array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                            );
                        } else {
                            $salary_amount = isset($row_formulae_from_remarks['EmployeeSalaryStructure']['structure_det_value']) ? $row_formulae_from_remarks['EmployeeSalaryStructure']['structure_det_value'] : 0;

                            if ($key == $basic) {
                                $salary_amount = round($salary_amount * (1 + ((float)$hike / 100)));
                                // If $value is not a multiple of 10, round it up to the next multiple of 10
                                if ($salary_amount % 10 !== 0) {
                                    $salary_amount = ceil($salary_amount / 10) * 10;
                                }
                                $basic_value = $salary_amount;
                            } elseif ($key == $hra) {
                                $salary_amount = $basic_value * (.2);
                            } elseif ($key == $vda) {
                                $salary_amount = $basic_value * (2.285);
                            }
                        }
                    }
                    $prc = $this->EmployeeSalaryStructure->query("call salary_structure_limit_prc('$emp','$user_ids',@`perr_msg`)");
                }
            }
            $response = array('success' => 1, 'msg' => 'Employees allocated successfully.');
        } else {
            $response['msg'] = 'No employees selected for allocation.';
        }


        $resp = array();
        $resp["success"] = 1;
        $resp["msg"] = "Salary component saved successfully.";
        echo json_encode($resp);
    }

    // Edited by Akshay on 24-6-2025
    public function employeelist()
    {
        $this->autoRender = false;
        $arr_request_data = $_POST;

        $employee  = isset($_POST['employee'])  ? $_POST['employee']  : '';
        $branch    = isset($_POST['branch'])    ? $_POST['branch']    : '';
        $structure = isset($_POST['structure']) ? $_POST['structure'] : '';
        $status    = isset($_POST['status'])    ? $_POST['status']    : '';

        $this->SalaryHike->useDbConfig = $this->Session->read('ds');

        $limit = isset($_REQUEST['rows']) ? (int)$_REQUEST['rows'] : 10;
        $page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
        $offset = ($page - 1) * $limit;


        $emp_name =  isset($_REQUEST['emp']) ? $_REQUEST['emp'] : '';
        // Condtions
        // Status filter (Processed / Not Processed)
        $filter_conditions = '';
        if ($status == 'Processed') {
            $filter_conditions = " AND sh.action = 'Processed'";
        } elseif ($status == 'Not Processed') {
            $filter_conditions = " AND (sh.action IS NULL OR sh.action != 'Processed') ";
        }

        if (!empty($employee)) {
            $filter_conditions .= " AND shd.emp_fkey = '$employee'";
        }

        if (!empty($branch)) {
            $filter_conditions .= " AND ei.branch_code = '$branch'";
        }

        if (!empty($structure)) {
            $filter_conditions .= " AND shd.structure_id = '$structure'";
        }

        if (trim($emp_name) != '') {
            $filter_conditions .= " AND ei.EmpName LIKE CONCAT( '%', '$emp_name', '%') ";
        }

        // Count total records
        $count_sql = "
                        SELECT COUNT(DISTINCT sh.salary_hike_pkey) AS total
                        FROM salary_hike sh
                        LEFT JOIN salary_hike_details shd ON sh.salary_hike_pkey = shd.salary_hike_fkey
                        LEFT JOIN employee_info ei ON ei.emp_pkey = shd.emp_fkey
                        WHERE sh.status = 1
                        $filter_conditions
                    ";
        $totalResult = $this->SalaryHike->query($count_sql);
        $total = isset($totalResult[0][0]['total']) ? (int)$totalResult[0][0]['total'] : 0;

        // Main paginated query
        $sql = "
                    SELECT 
                        sh.salary_hike_pkey,
                        sh.creation_date,
                        sh.modification_date, -- Edited by Akshay on 9-10-2025
                        sh.status,
                        sh.is_multiple,
                        sh.item,

                        shd.emp_fkey,
                        ss.structure_name,

                        ei.EmpName,
                        ei.employee_id,
                        ei.branch
                    FROM salary_hike sh
                    LEFT JOIN salary_hike_details shd ON sh.salary_hike_pkey = shd.salary_hike_fkey
                    LEFT JOIN employee_info ei ON ei.emp_pkey = shd.emp_fkey
                    LEFT JOIN salary_structure ss ON ss.structure_id = shd.structure_id
                    WHERE sh.status = 1
                    $filter_conditions
                    GROUP BY sh.salary_hike_pkey
                    ORDER BY sh.salary_hike_pkey DESC
                    LIMIT $offset, $limit
                ";

        $arr_att = $this->SalaryHike->query($sql);

        $resp_att = array();
        $resp_att['total'] = $total;
        $resp_att['rows'] = array();

        foreach ($arr_att as $key => $value) {
            $out = array();

            $salary_hike_pkey = $value['sh']['salary_hike_pkey'];
            $arr_emp_fkey = $this->SalaryHike->query("SELECT DISTINCT emp_fkey FROM salary_hike_details WHERE salary_hike_fkey = '$salary_hike_pkey' AND status = 1;");

            $is_multiple = false;
            if (count($arr_emp_fkey) > 1) {
                $is_multiple = true;
            }
            $out['salary_hike_pkey'] = $value['sh']['salary_hike_pkey'];
            $out['created_date']     = !empty($value['sh']['creation_date']) ? date('d-m-Y', strtotime($value['sh']['creation_date'])) : '';
            $out['modification_date']     = !empty($value['sh']['modification_date']) ? date('d-m-Y', strtotime($value['sh']['modification_date'])) : ''; // Edited by Akshay on 9-10-2025
            $out['status']           = ($value['sh']['status'] == '1') ? 'Approved' : 'Pending';

            $out['emp_fkey']     = isset($value['shd']['emp_fkey']) ? $value['shd']['emp_fkey'] : '';

            if ($is_multiple) {
                $out['empname'] = 'Multiple';
                $out['empid'] = 'N/A';
                $out['salary_structure'] = 'N/A';
                $out['branch'] = 'N/A';
            } else {
                $out['salary_structure'] = isset($value['ss']['structure_name']) ? $value['ss']['structure_name'] : 'N/A';
                $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : 'Multiple';
                $out['empid'] = isset($value['ei']['employee_id']) ? $value['ei']['employee_id'] : 'N/A';
                $out['branch'] = isset($value['ei']['branch']) ? $value['ei']['branch'] : 'N/A';
            }

            $out['is_multiple'] = isset($value['sh']['is_multiple']) ? $value['sh']['is_multiple'] : '';
            $out['item'] = isset($value['sh']['item']) ? $value['sh']['item'] : '';

            $resp_att['rows'][] = $out;
        }

        echo json_encode($resp_att);
    }

    public function process()
    {
        $all_successful = true;
        $arr_grouped = array(); // Edited by Akshay on 30-10-2025
        $arr_not_processed = array(); // Edited by Akshay on 30-10-2025
        $arr_invalid_salary = array();
        $error_message = '';
        try {
            $this->autoRender = false;
            $arr_form_data = $_POST;
            $this->SalaryHike->useDbConfig = $this->Session->read('ds');
            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
            date_default_timezone_set('Asia/Kolkata'); // Edited by Akshay on 29-10-2025

            $user_id = $this->Session->read("login_user_id");

            $salary_hike_pkey = $arr_form_data['salary_hike_pkey'];

            $is_multiple = $arr_form_data['is_multiple'];

            $arr_direct_items = $this->SalaryHike->query("SELECT DISTINCT salary_head_item_fkey FROM `emp_salary_slip` WHERE `end_date_effective` IS NULL AND `head_operator` = 'Addition' AND `item_part` = 'Direct'");
            $allowed_fkeys = array_map(function ($item) {
                return $item['emp_salary_slip']['salary_head_item_fkey'];
            }, $arr_direct_items);

            $arr_structure = $this->SalaryHike->query("SELECT structure_change FROM salary_hike WHERE salary_hike_pkey = '$salary_hike_pkey';");
            $structure_change = isset($arr_structure[0]['salary_hike']['structure_change']) ? $arr_structure[0]['salary_hike']['structure_change'] : 'N';


            $arr_raw = $this->SalaryHike->query("SELECT salary_head_item_fkey,emp_fkey, structure_id, branch_code, with_effect_from, next_increment_date, payout_month, new_amount, arrear_salary   
                                        FROM salary_hike_details shd 
                                        WHERE salary_hike_fkey = '$salary_hike_pkey'
                                        AND status  =1
                                        -- AND increment_amount != 0
                                        ;
                                        ");

            foreach ($arr_raw as $row) {
                $emp_fkey = $row['shd']['emp_fkey'];

                $exit = false;
                if ($structure_change == 'Y') {
                    $arr_structure = $this->SalaryHike->query("SELECT structure_id FROM emp_proff WHERE emp_fkey = '$emp_fkey'");
                    $structure = isset($arr_structure[0]['emp_proff']['structure_id']) ? $arr_structure[0]['emp_proff']['structure_id'] : 0;
                    $new_structure = $row['shd']['structure_id'];
                    if ($structure != $new_structure) {
                        $exit = true;
                    }
                }

                if (!$exit) {
                    $arr_grouped[$emp_fkey][] = $row['shd'];
                }
            }
            // debug($arr_grouped);
            foreach ($arr_grouped as $emp_details) {

                $gross_salary = 0;
                $emp_pkey = $emp_details[0]['emp_fkey'];

                $arr_emp_proff = $this->SalaryHike->query("SELECT designation, emp_dept, emp_branch, structure_id, joining_date, emp_company_id, emp_type FROM emp_proff WHERE emp_fkey = '$emp_pkey';"); // Edited by Akshay on 29-10-2025

                $next_increment_date = isset($emp_details[0]['next_increment_date']) ? $emp_details[0]['next_increment_date'] : '0000-00-00';
                $payout_month = isset($emp_details[0]['payout_month']) ? $emp_details[0]['payout_month'] : '';
                $with_effect_from  = isset($emp_details[0]['with_effect_from']) ? $emp_details[0]['with_effect_from'] : '';
                $arrear_salary = isset($emp_details[0]['arrear_salary']) ? $emp_details[0]['arrear_salary'] : '';
                $branch = isset($arr_emp_proff[0]['emp_proff']['emp_branch']) ? $arr_emp_proff[0]['emp_proff']['emp_branch'] : '';
                $item_fkey = isset($emp_details['emp_salary_structure']['salary_head_item_fkey']) ? $emp_details['emp_salary_structure']['salary_head_item_fkey'] : 0;

                // Edited by Akshay on 29-10-2025
                $joining_date = isset($arr_emp_proff[0]['emp_proff']['joining_date']) ? $arr_emp_proff[0]['emp_proff']['joining_date'] : '';
                $structure_id = isset($arr_emp_proff[0]['emp_proff']['structure_id']) ? $arr_emp_proff[0]['emp_proff']['structure_id'] : 0;
                $emp_type = isset($arr_emp_proff[0]['emp_proff']['emp_type']) ? strtoupper(trim($arr_emp_proff[0]['emp_proff']['emp_type'])) : 0; // Edited by Akshay on 28-3-2026

                if ($structure_id == 0) {
                    $with_effect_from = ($with_effect_from == '' || $with_effect_from == '0000-00-00') ? $joining_date : $with_effect_from;
                } else {
                    $with_effect_from = ($with_effect_from == '' || $with_effect_from == '0000-00-00') ?  date('Y-m-d') : $with_effect_from;
                }

                if (($joining_date == '' || $joining_date > $with_effect_from)) {
                    $arr_emp_name = $this->SalaryHike->query("select CONCAT(COALESCE(ed.first_name, ''), ' ', COALESCE(ed.last_name, '')) AS emp_name FROM emp_details ed WHERE ed.emp_pkey ='$emp_pkey';");
                    $emp_name = isset($arr_emp_name[0][0]['emp_name']) ? $arr_emp_name[0][0]['emp_name'] : '';
                    $emp_company_id = isset($arr_emp_proff[0]['emp_proff']['emp_company_id']) ? $arr_emp_proff[0]['emp_proff']['emp_company_id'] : '';
                    $arr_not_processed[] = trim($emp_name) . ' - ' . $emp_company_id;
                    continue;
                }

                if ($payout_month == '' || $payout_month == '0000-00-00') {
                    if ($structure_id == 0) {
                        $payout_month = isset($arr_emp_proff[0]['emp_proff']['joining_date']) && $arr_emp_proff[0]['emp_proff']['joining_date'] != ''
                            ? date('Y-m-01', strtotime($arr_emp_proff[0]['emp_proff']['joining_date']))
                            : date('Y-m-01');
                    } else {
                        $arr_next_month = $this->SalaryHike->query("SELECT MAX(month_year) AS latest_month_year
                                                FROM payroll_master
                                                WHERE emp_fkey = '$emp_pkey'
                                                AND action = 'processed';");

                        if (isset($arr_next_month[0][0]['latest_month_year']) && $arr_next_month[0][0]['latest_month_year'] != '') {

                            // If latest month exists, take next month
                            $payout_month = date('Y-m-01', strtotime($arr_next_month[0][0]['latest_month_year'] . ' +1 month'));
                        } else {

                            // If latest month doesn't exist, take joining date +1 month
                            if (isset($arr_emp_proff[0]['emp_proff']['joining_date']) && $arr_emp_proff[0]['emp_proff']['joining_date'] != '') {
                                $payout_month = date('Y-m-01', strtotime($arr_emp_proff[0]['emp_proff']['joining_date'] . ' +1 month'));
                            } else {
                                // Final fallback
                                $payout_month = date('Y-m-01');
                            }
                        }
                    }
                }

                // End

                foreach ($emp_details as $val) {
                    if (isset($val['salary_head_item_fkey'])) {
                        $salary_head_item_fkey = $val['salary_head_item_fkey'];
                        $new_amount = $val['new_amount'];

                        if (in_array($salary_head_item_fkey, $allowed_fkeys)) {
                            $gross_salary += (float)$new_amount;
                        }
                    } else {
                        $new_amount = $val['new_amount'];
                        $gross_salary += (float)$new_amount;
                    }
                }

                if ($structure_change == 'Y' || $structure_id == 0) {
                    $new_structure = $emp_details[0]['structure_id'];
                    $arr_structure_eg_amt =  $this->SalaryHike->query("SELECT structure_eg_amt FROM salary_structure WHERE structure_id = '$new_structure';");
                    $structure_eg_amt = isset($arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt']) ? $arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt'] : 0;
                } else {
                    $arr_structure_eg_amt =  $this->SalaryHike->query("SELECT structure_eg_amt FROM salary_structure WHERE structure_id = '$structure_id';");
                    $structure_eg_amt = isset($arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt']) ? $arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt'] : 0;
                }

                if ($structure_eg_amt > $gross_salary) {

                    $arr_emp_name = $this->SalaryHike->query("select CONCAT(COALESCE(ed.first_name, ''), ' ', COALESCE(ed.last_name, '')) AS emp_name FROM emp_details ed WHERE ed.emp_pkey ='$emp_pkey';");
                    $emp_name = isset($arr_emp_name[0][0]['emp_name']) ? $arr_emp_name[0][0]['emp_name'] : '';
                    $emp_company_id = isset($arr_emp_proff[0]['emp_proff']['emp_company_id']) ? $arr_emp_proff[0]['emp_proff']['emp_company_id'] : '';
                    $arr_invalid_salary[] = trim($emp_name) . ' - ' . $emp_company_id;
                    continue;
                }

                $this->SalaryHike->query("CALL copy_salary_structure_to_new({$emp_pkey});");

                $arr_data = array();
                $arr_data['emp_fkey'] = $emp_pkey;
                $arr_data['emp_anual_ctc'] = (strtoupper(trim($emp_type)) == 'DAILY WAGES' || strtoupper(trim($emp_type)) == 'HOURLY WAGES') ? $gross_salary :  $gross_salary * 12; // Edited by Akshay on 28-3-2026
                $arr_data['emp_monthly_ctc'] = $gross_salary;
                $arr_data['arrear_salary'] = $arrear_salary;
                $arr_data['pay_out_month'] = $payout_month;
                $arr_data['created_by'] = $user_id;
                $arr_data['created_date'] = date('Y-m-d H:i:s');
                $arr_data['start_date_effective'] = $with_effect_from;
                $arr_data['approved_by'] = $user_id;
                $arr_data['next_increment_date'] = $next_increment_date;
                $arr_data['branch'] = $branch;
                // debug($arr_data);
                $this->EmployeeCTC->create(); // ensures it's treated as new
                try {
                    $result = $this->EmployeeCTC->save($arr_data);

                    // Edited by Akshay on 30-10-2025
                    $this->SalaryHike->query(
                        "UPDATE salary_hike_details 
                            SET processed = 'Y', 
                                payout_month = ?
                            WHERE salary_hike_fkey = ? 
                            AND emp_fkey = ? 
                            AND status = 1 
                            -- AND increment_amount != 0 
                            AND processed = 'N'",
                        array($payout_month, $salary_hike_pkey, $emp_pkey)  // Add payout_month to parameters
                    );
                    // End

                } catch (Exception $e) {
                    debug($e);
                }
            }
        } catch (Exception $e) {
            $all_successful = false;
            debug($e);
            exit;
        }

        if (count($arr_grouped) >  count($arr_not_processed) || count($arr_grouped) > count($arr_invalid_salary)) {
            $this->SalaryHike->updateAll(
                array('SalaryHike.action' => "'Processed'"), // or any string like 'Completed', 'Done'
                array('SalaryHike.salary_hike_pkey' => $salary_hike_pkey)
            );
        } else {
            $all_successful = false;
        }

        // Edited by Akshay on 30-10-2025
        if (!empty($arr_not_processed)) {
            $not_processed_count = count($arr_not_processed);
            $not_processed_list = implode(', ', $arr_not_processed);
            $error_message = "{$not_processed_count} employee(s) could not be processed due to invalid start date effective: " . $not_processed_list;
        }

        if (!empty($arr_invalid_salary)) {
            $not_processed_count = count($arr_invalid_salary);
            $not_processed_list = implode(', ', $arr_invalid_salary);
            $error_message .= "{$not_processed_count} employee(s) could not be processed due to invalid salary amount as per structure: " . $not_processed_list;
        }
        // End

        echo json_encode([
            'success' => $all_successful,
            'message' => $all_successful ? 'Processed successfully' : 'Some records failed to process',
            'error_message' => $error_message,
        ]);
    }


    public function onIncrementChange()
    {
        $this->autoRender = false;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

        $emp_pkey = $_POST['emp_pkey'];
        $salary_head_item_pkey = $_POST['salary_head_item_pkey']; // e.g. 6
        $current_value = $_POST['current_value'];
        $new_value = $_POST['new_value'];
        $gross_amount = $_POST['gross_amount'];
        $structure_id = $_POST['structure_id'];

        // Call stored procedure to get fresh component breakup
        $arr_result = $this->EmployeeCTC->query("CALL calculate_emp_component_breakup('$structure_id', '$salary_head_item_pkey', '$new_value', '$gross_amount');");

        // Recalculate gross after changes
        $arr_gross = $this->EmployeeCTC->query("
                                                SELECT SUM(structure_det_value) AS std_monthly_ctc 
                                                FROM emp_salary_structure 
                                                WHERE emp_fkey = '$emp_pkey' 
                                                AND end_date_effective IS NULL 
                                                AND LCASE(head_operator) = 'addition' 
                                                AND LCASE(item_part) = 'direct'
                                            ");
        $gross_amount = isset($arr_gross[0][0]['std_monthly_ctc']) ? $arr_gross[0][0]['std_monthly_ctc'] : 0;

        // Get rembalance percentage
        $arr_rembalance = $this->EmployeeCTC->query("
                                                        SELECT structure_derived_perc 
                                                        FROM salary_structure_details 
                                                        WHERE structure_id = '$structure_id' 
                                                        AND TRIM(structure_formula) = 'Remaining Balance'
                                                    ");
        $rembalance_prc = isset($arr_rembalance[0]['salary_structure_details']['structure_derived_perc']) ? $arr_rembalance[0]['salary_structure_details']['structure_derived_perc'] : 0;
        $rembalance = $gross_amount * $rembalance_prc / 100;

        // Filter & recalculate only affected rows
        $final_result = [];

        if (!empty($arr_result)) {
            foreach ($arr_result as &$row) {
                $item_pkey = $row['salary_breakup_temp']['salary_head_item_fkey'];

                $include_row = false;

                if ($salary_head_item_pkey == $item_pkey) {
                    $row['salary_breakup_temp']['calculated_value'] = $new_value;
                    $include_row = true;
                } else {
                    $temp = $row['salary_breakup_temp'];
                    $formula = $temp['structure_det_calequation'];

                    // ✅ Check if formula uses this item (e.g., 6_Special_Allowance)
                    if (!preg_match('/\b' . preg_quote($salary_head_item_pkey, '/') . '_\w+\b/', $formula)) {
                        continue; // not dependent, skip it
                    }

                    $include_row = true;

                    // Step 1: Replace placeholders
                    $formula = str_replace(['monthsal', 'rembalance'], [$gross_amount, $rembalance], $formula);

                    // Step 2: Extract keys like 1_Basic
                    preg_match_all('/\b(\d+)_\w+\b/', $formula, $matches);
                    $item_keys = $matches[1];
                    $values = [];

                    foreach ($item_keys as $key) {
                        if ($key == $salary_head_item_pkey) {
                            $values[$key] = $new_value;
                        } else {
                            $sql = "
                            SELECT structure_det_value 
                            FROM emp_salary_structure 
                            WHERE emp_fkey = $emp_pkey 
                            AND salary_head_item_fkey = $key 
                            AND end_date_effective IS NULL 
                            LIMIT 1
                        ";
                            $result = $this->EmployeeCTC->query($sql);
                            $value = !empty($result[0][0]['structure_det_value']) ? $result[0][0]['structure_det_value'] : 0;
                            $values[$key] = $value;
                        }
                    }

                    // Step 3: Replace item references with values
                    $evaluated_formula = preg_replace_callback('/\b(\d+)_\w+\b/', function ($match) use ($values) {
                        $key = $match[1];
                        return isset($values[$key]) ? $values[$key] : 0;
                    }, $formula);

                    // Step 4: Sanitize and evaluate
                    $evaluated_formula = str_replace(' ', '', $evaluated_formula);
                    $evaluated_formula = preg_replace('/(?<!\d)\.(\d+)/', '0.$1', $evaluated_formula);

                    $calculated_value = 0;
                    if ($evaluated_formula !== '') {
                        @eval("\$calculated_value = $evaluated_formula;");
                    }

                    // Apply operator logic like in the stored procedure
                    $operator = strtolower($row['salary_breakup_temp']['structure_det_operator']);
                    $depends = isset($row['salary_breakup_temp']['structure_det_depends']) ? (float)$row['salary_breakup_temp']['structure_det_depends'] : 0;
                    $is_deduction = strtoupper($row['salary_breakup_temp']['is_deduction']) === 'Y';

                    // Now apply operator-based logic
                    switch ($operator) {
                        case 'limit':
                            $calculated_value = min($calculated_value, (float)$row['salary_breakup_temp']['structure_det_value']);
                            break;
                        case 'limit_wl':
                            $calculated_value = min($depends, $calculated_value);
                            break;
                        case 'limit_wg':
                            $calculated_value = max($depends, $calculated_value);
                            break;
                        case 'fixed':
                            $calculated_value = (float)$row['salary_breakup_temp']['structure_det_value'];
                            break;
                            // 'formula' just uses the evaluated_formula as-is
                    }

                    // Apply deduction flag
                    if ($is_deduction) {
                        $calculated_value = -abs($calculated_value);
                    }

                    $row['salary_breakup_temp']['calculated_value'] = $calculated_value;
                }

                if ($include_row) {
                    $final_result[] = $row;
                }
            }

            echo json_encode($final_result, JSON_PRETTY_PRINT);
            return;
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'No data found or invalid employee structure.'
        ]);
    }

    // Edited by Akshay on 11-12-2025
    public function onIncrementChangeNew()
    {
        $this->autoRender = false;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

        $emp_pkey = $_POST['emp_pkey'];
        $salary_head_item_pkey = $_POST['salary_head_item_pkey']; // e.g. 6
        $current_value = $_POST['current_value'];
        $new_value = $_POST['new_value'];
        $gross_amount = $_POST['gross_amount'];
        $structure_id = $_POST['structure_id'];
        $new_values = $_POST['new_values'];

        // Call stored procedure to get fresh component breakup
        $arr_result = $this->EmployeeCTC->query("CALL calculate_emp_component_breakup('$structure_id', '$salary_head_item_pkey', '$new_value', '$gross_amount');");

        // Recalculate gross after changes
        $gross_amount = array_sum($new_values);

        // Get rembalance percentage
        $arr_rembalance = $this->EmployeeCTC->query("
                                                        SELECT structure_derived_perc 
                                                        FROM salary_structure_details 
                                                        WHERE structure_id = '$structure_id' 
                                                        AND TRIM(structure_formula) = 'Remaining Balance'
                                                    ");
        $rembalance_prc = isset($arr_rembalance[0]['salary_structure_details']['structure_derived_perc']) ? $arr_rembalance[0]['salary_structure_details']['structure_derived_perc'] : 0;
        $rembalance = $gross_amount * $rembalance_prc / 100;

        // Filter & recalculate only affected rows
        $final_result = [];

        if (!empty($arr_result)) {
            foreach ($arr_result as &$row) {
                $item_pkey = $row['salary_breakup_temp']['salary_head_item_fkey'];

                $include_row = false;

                if ($salary_head_item_pkey == $item_pkey) {
                    $row['salary_breakup_temp']['calculated_value'] = $new_value;
                    $include_row = true;
                } else {
                    $temp = $row['salary_breakup_temp'];
                    $formula = $temp['structure_det_calequation'];

                    // ✅ Check if formula uses this item (e.g., 6_Special_Allowance)
                    if (!preg_match('/\b' . preg_quote($salary_head_item_pkey, '/') . '_\w+\b/', $formula)) {
                        // continue; // not dependent, skip it
                    }

                    $include_row = true;

                    // Step 1: Replace placeholders
                    $formula = str_replace(['monthsal', 'rembalance'], [$gross_amount, $rembalance], $formula);

                    // Step 2: Extract keys like 1_Basic
                    preg_match_all('/\b(\d+)_\w+\b/', $formula, $matches);
                    $item_keys = $matches[1];
                    $values = [];

                    foreach ($item_keys as $key) {
                        if ($key == $salary_head_item_pkey) {
                            $values[$key] = $new_value;
                        } else {
                            $sql = "
                            SELECT structure_det_value 
                            FROM emp_salary_structure 
                            WHERE emp_fkey = $emp_pkey 
                            AND salary_head_item_fkey = $key 
                            AND end_date_effective IS NULL 
                            LIMIT 1
                        ";
                            // $result = $this->EmployeeCTC->query($sql);
                            // $value = !empty($result[0][0]['structure_det_value']) ? $result[0][0]['structure_det_value'] : 0;
                            $result = $_POST['new_values'];
                            $value = !empty($result[$key]) ? $result[$key] : 0;
                            $values[$key] = $value;
                        }
                    }

                    // Step 3: Replace item references with values
                    $evaluated_formula = preg_replace_callback('/\b(\d+)_\w+\b/', function ($match) use ($values) {
                        $key = $match[1];
                        return isset($values[$key]) ? $values[$key] : 0;
                    }, $formula);

                    // Step 4: Sanitize and evaluate
                    $evaluated_formula = str_replace(' ', '', $evaluated_formula);
                    $evaluated_formula = preg_replace('/(?<!\d)\.(\d+)/', '0.$1', $evaluated_formula);

                    $calculated_value = 0;
                    if ($evaluated_formula !== '') {
                        @eval("\$calculated_value = $evaluated_formula;");
                    }

                    // Apply operator logic like in the stored procedure
                    $operator = strtolower($row['salary_breakup_temp']['structure_det_operator']);
                    $depends = isset($row['salary_breakup_temp']['structure_det_depends']) ? (float)$row['salary_breakup_temp']['structure_det_depends'] : 0;
                    $is_deduction = strtoupper($row['salary_breakup_temp']['is_deduction']) === 'Y';

                    // Now apply operator-based logic
                    switch ($operator) {
                        case 'limit':
                            $calculated_value = min($calculated_value, (float)$row['salary_breakup_temp']['structure_det_value']);
                            break;
                        case 'limit_wl':
                            $calculated_value = min($depends, $calculated_value);
                            break;
                        case 'limit_wg':
                            $calculated_value = max($depends, $calculated_value);
                            break;
                        case 'fixed':
                            $calculated_value = (float)$row['salary_breakup_temp']['structure_det_value'];
                            break;
                            // 'formula' just uses the evaluated_formula as-is
                    }

                    // Apply deduction flag
                    if ($is_deduction) {
                        $calculated_value = -abs($calculated_value);
                    }

                    $row['salary_breakup_temp']['calculated_value'] = $calculated_value;
                }

                if ($include_row) {

                    $final_result[] = $row;
                }
            }

            echo json_encode($final_result, JSON_PRETTY_PRINT);
            return;
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'No data found or invalid employee structure.'
        ]);
    }
    // End

    public function processItem()
    {
        $arr_not_processed = array(); // Edited by Akshay on 30-10-2025
        $arr_invalid_salary = array();
        try {
            $this->autoRender = false;
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $this->EmpSalaryCompUpload->useDbConfig = $this->Session->read('ds');
            $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
            $this->SalaryHike->useDbConfig = $this->Session->read('ds');
            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

            $user_id = $this->Session->read("login_user_id");
            $arr_form_data = $_POST;

            $salary_hike_pkey = $arr_form_data['salary_hike_pkey'];

            $arr_salary_hike =  $this->SalaryHike->query("SELECT * FROM salary_hike sh WHERE sh.salary_hike_pkey = '$salary_hike_pkey';");


            // Edited by Akshay on 27-10-2025
            // debug($arr_salary_hike);
            $structure_change = isset($arr_salary_hike[0]['sh']['structure_change']) ? $arr_salary_hike[0]['sh']['structure_change'] : 'N';
            // End

            $arr_raw = $this->SalaryHike->query("
                    SELECT salary_head_item_fkey, emp_fkey, structure_id, branch_code, with_effect_from, 
                        next_increment_date, payout_month, new_amount, arrear_salary   
                    FROM salary_hike_details shd 
                    WHERE salary_hike_fkey = '$salary_hike_pkey'
                        AND status = 1
                        -- AND ROUND(increment_amount) != 0 -- Edited by Akshay on 24-9-2025
                        ");
            // debug($arr_raw);
            if (empty($arr_raw)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No valid increment records found.'
                ]);
                return;
            }

            $arr_grouped = [];
            // debug($arr_raw);
            foreach ($arr_raw as $row) {
                $emp_fkey = $row['shd']['emp_fkey'];

                // Edited by Akshay on 27-10-2025
                $exit = false;
                // debug($structure_change);
                if ($structure_change == 'Y') {
                    $arr_structure = $this->SalaryHike->query("SELECT structure_id FROM emp_proff WHERE emp_fkey = '$emp_fkey'");
                    $structure = isset($arr_structure[0]['emp_proff']['structure_id']) ? $arr_structure[0]['emp_proff']['structure_id'] : 0;
                    $new_structure = $row['shd']['structure_id'];
                    // debug($structure);
                    // debug($new_structure);
                    if ($structure != $new_structure) {
                        $exit = true;
                    }
                }

                if (!$exit) {
                    $arr_grouped[$emp_fkey][] = $row['shd'];
                }
                // debug($arr_grouped);
                // End

            }
            // debug($arr_grouped);
            foreach ($arr_grouped as $emp_details) {
                $emp = $emp_details[0]['emp_fkey'];

                $this->SalaryHike->query("CALL copy_salary_structure_to_new({$emp});");

                // Edited by Akshay on 28-3-2026
                $arr_monthly_gross = $this->SalaryHike->query("
                                                                SELECT 
                                                                    CASE 
                                                                        WHEN UPPER(TRIM(ep.emp_type)) IN ('DAILY WAGES', 'HOURLY WAGES') 
                                                                            THEN emp_ctc_transaction.emp_anual_ctc
                                                                        ELSE emp_ctc_transaction.emp_anual_ctc / 12
                                                                    END AS monthly_ctc

                                                                FROM emp_ctc_transaction

                                                                LEFT JOIN emp_proff ep 
                                                                    ON ep.emp_fkey = emp_ctc_transaction.emp_fkey

                                                                WHERE emp_ctc_transaction.emp_fkey = '$emp' 
                                                                AND emp_ctc_transaction.end_date_effective IS NULL
                                                            ");
                // End

                $monthly_gross = isset($arr_monthly_gross[0][0]['monthly_ctc']) ? $arr_monthly_gross[0][0]['monthly_ctc'] : 0;

                $arr_emp_id = $this->SalaryHike->query("SELECT ed.emp_id, ed.branch_code,ep.emp_type FROM emp_details ed LEFT JOIN emp_proff ep ON ep.emp_fkey = ed.emp_pkey WHERE ed.emp_pkey = '$emp'"); // Edited by Akshay on 28-3-2026
                $emp_id = $arr_emp_id[0]['ed']['emp_id'];
                $emp_type = isset($arr_emp_id[0]['ep']['emp_type']) ? strtoupper(trim($arr_emp_id[0]['ep']['emp_type'])) : ''; // Edited by Akshay on 28-3-2026

                $branch = $emp_details[0]['branch_code'];
                $with_effect_from = $emp_details[0]['with_effect_from'];
                $next_increment_date = $emp_details[0]['next_increment_date'];
                $payout_month = $emp_details[0]['payout_month'];

                // Edited by Akshay on  30-10-2025
                $arr_emp_proff = $this->SalaryHike->query("SELECT designation, emp_dept, emp_branch, structure_id, joining_date, emp_company_id FROM emp_proff WHERE emp_fkey = '$emp';");
                $structure_id = isset($arr_emp_proff[0]['emp_proff']['structure_id']) ? $arr_emp_proff[0]['emp_proff']['structure_id'] : 0;
                if ($payout_month == '' || $payout_month == '0000-00-00') {
                    if ($structure_id == 0) {
                        $payout_month = isset($arr_emp_proff[0]['emp_proff']['joining_date']) && $arr_emp_proff[0]['emp_proff']['joining_date'] != ''
                            ? date('Y-m-01', strtotime($arr_emp_proff[0]['emp_proff']['joining_date']))
                            : date('Y-m-01');
                    } else {
                        $arr_next_month = $this->SalaryHike->query("SELECT MAX(month_year) AS latest_month_year
                                                FROM payroll_master
                                                WHERE emp_fkey = '$emp'
                                                AND action = 'processed';");

                        if (isset($arr_next_month[0][0]['latest_month_year']) && $arr_next_month[0][0]['latest_month_year'] != '') {

                            // latest processed month exists → next month
                            $payout_month = date('Y-m-01', strtotime($arr_next_month[0][0]['latest_month_year'] . ' +1 month'));
                        } else {

                            // latest month does NOT exist → joining date +1 month
                            if (isset($arr_emp_proff[0]['emp_proff']['joining_date']) && $arr_emp_proff[0]['emp_proff']['joining_date'] != '') {
                                $payout_month = date('Y-m-01', strtotime($arr_emp_proff[0]['emp_proff']['joining_date'] . ' +1 month'));
                            } else {
                                // fallback
                                $payout_month = date('Y-m-01');
                            }
                        }
                    }
                }

                $joining_date = isset($arr_emp_proff[0]['emp_proff']['joining_date']) ? $arr_emp_proff[0]['emp_proff']['joining_date'] : '';
                if ($structure_id == 0) {
                    $with_effect_from = ($with_effect_from == '') ? $joining_date : $with_effect_from;
                }
                if (($joining_date == '' || $joining_date > $with_effect_from)) {
                    $arr_emp_name = $this->SalaryHike->query("select CONCAT(COALESCE(ed.first_name, ''), ' ', COALESCE(ed.last_name, '')) AS emp_name FROM emp_details ed WHERE ed.emp_pkey ='$emp';");
                    $emp_name = isset($arr_emp_name[0][0]['emp_name']) ? $arr_emp_name[0][0]['emp_name'] : '';
                    $emp_company_id = isset($arr_emp_proff[0]['emp_proff']['emp_company_id']) ? $arr_emp_proff[0]['emp_proff']['emp_company_id'] : '';
                    $arr_not_processed[] = trim($emp_name) . ' - ' . $emp_company_id;
                    continue;
                }
                // End

                $gross_salary = 0;
                foreach ($emp_details as $item) {
                    $gross_salary += $item['new_amount'];
                }

                if ($structure_change == 'Y' || $structure_id == 0) {
                    $new_structure = $emp_details[0]['structure_id'];
                    $arr_structure_eg_amt =  $this->SalaryHike->query("SELECT structure_eg_amt FROM salary_structure WHERE structure_id = '$new_structure';");
                    $structure_eg_amt = isset($arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt']) ? $arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt'] : 0;
                } else {
                    $arr_structure_eg_amt =  $this->SalaryHike->query("SELECT structure_eg_amt FROM salary_structure WHERE structure_id = '$structure_id';");
                    $structure_eg_amt = isset($arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt']) ? $arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt'] : 0;
                }

                if ($structure_eg_amt > $gross_salary) {
                    $arr_emp_name = $this->SalaryHike->query("select CONCAT(COALESCE(ed.first_name, ''), ' ', COALESCE(ed.last_name, '')) AS emp_name FROM emp_details ed WHERE ed.emp_pkey ='$emp';");
                    $emp_name = isset($arr_emp_name[0][0]['emp_name']) ? $arr_emp_name[0][0]['emp_name'] : '';
                    $emp_company_id = isset($arr_emp_proff[0]['emp_proff']['emp_company_id']) ? $arr_emp_proff[0]['emp_proff']['emp_company_id'] : '';
                    $arr_invalid_salary[] = trim($emp_name) . ' - ' . $emp_company_id;
                    continue;
                }


                $gross_salary = 0;
                foreach ($emp_details as $item_details) {
                    $head_fkey = $item_details['salary_head_item_fkey'];
                    $arr_sal_head = $this->SalaryHike->query("
                            SELECT item, head_fkey, item_part FROM salary_head_items 
                            WHERE salary_head_item_pkey = '$head_fkey' AND status = 1;
                        ");
                    $salary_head_item = isset($arr_sal_head[0]['salary_head_items']['item']) ? trim($arr_sal_head[0]['salary_head_items']['item']) : '';
                    $value = $item_details['new_amount'];
                    $item_part = isset($arr_sal_head[0]['salary_head_items']['item_part']) ? trim($arr_sal_head[0]['salary_head_items']['item_part']) : '';

                    // Insert into CTC upload
                    $head = isset($arr_sal_head[0]['salary_head_items']['head_fkey']) ? trim($arr_sal_head[0]['salary_head_items']['head_fkey']) : '';
                    if (strtolower(trim($item_part)) == 'direct' && $value > 0) {
                        $gross_salary += $value;
                    }

                    // End

                    // First set status = 0 for existing rows
                    $this->EmpSalaryCompUpload->updateAll(
                        ['EmpSalaryCompUpload.status' => 0], // fields to update
                        [ // conditions
                            'EmpSalaryCompUpload.emp_id' => $emp_id,
                            'EmpSalaryCompUpload.component' => $salary_head_item,
                            'EmpSalaryCompUpload.salary_head_item_fkey' => isset($head_fkey) ? $head_fkey : 1000
                        ]
                    );
                    $arr_empctc_data = array(
                        'emp_id' => $emp_id,
                        'component' => $salary_head_item,
                        'created_by' => $user_id,
                        'salary_head_item_fkey' => isset($head_fkey) ? $head_fkey : 1000,
                        'rate' => ($value == '') ? 0 : $value,
                        'status' => 1
                    );
                    // debug($arr_empctc_data);
                    $this->EmpSalaryCompUpload->saveAll($arr_empctc_data);
                }


                // Insert into ctc upload
                $month_year = date('Y-m', strtotime($with_effect_from));
                $arr_processed = $this->EmpSalaryCompUpload->query(
                    "SELECT COUNT(*) as count FROM payroll_master
                                                            WHERE emp_fkey = $emp
                                                            AND month_year = '$month_year'
                                                            AND action IN ('Approved', 'Processed');"
                );

                $is_processed = isset($arr_processed[0][0]['count']) ? (int)$arr_processed[0][0]['count'] : 0;
                if ($is_processed > 0) {
                    $is_arrear = 'Y';
                } else {
                    $is_arrear = 'N';
                }

                $arr_empctc_data = array();
                $arr_empctc_data['emp_ctc_upload_pkey'] = 0;
                $arr_empctc_data['status'] = 1;
                $arr_empctc_data['emp_fkey'] = $emp_fkey;
                $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
                $arr_empctc_data['created_date'] = date('Y-m-d');
                $arr_empctc_data['emp_anual_ctc'] = ($emp_type == 'DAILY WAGES' || $emp_type == 'HOURLY WAGES') ? $gross_salary : $gross_salary * 12;
                $arr_empctc_data['arrear_salary'] = $is_arrear;
                $arr_empctc_data['pay_out_month'] = $payout_month;
                $arr_empctc_data['start_date_effective'] = $with_effect_from;
                $arr_empctc_data['next_increment_date'] = $next_increment_date;
                $arr_empctc_data['branch'] = $branch;
                // debug($arr_empctc_data);exit;
                // try {
                //     $result1 = $this->EmployeeCTC->save($arr_empctc_data);
                // } catch (Exception $e) {
                //     debug($e);
                // }
                // End

                // Upload component via procedure
                // $this->EmployeeDetails->query("CALL `ctc_component_upload_prc`('$emp', @pmessage);");ctc_component_update_and_upload_prc

                $this->EmployeeDetails->query("CALL `ctc_component_update_and_upload_prc`('$emp', @pmessage);");

                // Update arrear
                $this->EmployeeDetails->query("
                                                UPDATE emp_ctc_transaction 
                                                SET 
                                                    arrear_salary = '$is_arrear',
                                                    pay_out_month = '$payout_month',
                                                    start_date_effective = '$with_effect_from',
                                                    next_increment_date = '$next_increment_date',
                                                     ctc_upload_type = 1
                                                WHERE emp_fkey = $emp 
                                                AND end_date_effective IS NULL
                                            ");

                // Distribute structure
                $salary = $this->EmployeeSalaryStructure->find("all", array(
                    'fields' => 'emp_structure_id',
                    'conditions' => array(
                        'emp_fkey' => $emp,
                        'end_date_effective IS NULL'
                    )
                ));

                $company = $this->Session->read('company_code');
                $user_ids = $this->Session->read('login_user_id');
                $salary_id = isset($salary[0]['EmployeeSalaryStructure']['emp_structure_id']) ? $salary[0]['EmployeeSalaryStructure']['emp_structure_id'] : "''";

                // $this->EmployeeSalaryStructure->query("CALL update_salary_structure_remarks($emp, $head_fkey)");

                // $this->EmployeeSalaryStructure->query("CALL update_formula_remarks($emp, $monthly_gross )");

                // Edited by Akshay on 24-7-2025
                $results = $this->EmployeeSalaryStructure->query("
                                                                    SELECT 
                                                                        ess.emp_salary_structure_pkey,
                                                                        ess.salary_head_item_fkey,
                                                                        ess.remarks,
                                                                        ess.structure_det_value,
                                                                        ssd.structure_det_calequation AS formula
                                                                    FROM emp_salary_structure ess
                                                                    LEFT JOIN salary_structure_details ssd
                                                                        ON ssd.salary_head_item_fkey = ess.salary_head_item_fkey
                                                                        AND ssd.structure_id = ess.emp_structure_id
                                                                    WHERE ess.emp_fkey = '$emp' 
                                                                    AND ess.end_date_effective IS NULL
                                                                    AND ess.remarks IS NOT NULL
                                                                    ;
                                                                ");
                // debug($results);

                foreach ($results as $row) {
                    $emp_salary_structure_pkey = $row['ess']['emp_salary_structure_pkey'];
                    $salary_head_item_fkey = $row['ess']['salary_head_item_fkey'];
                    $remarks = $row['ess']['remarks'];
                    $formula = $row['ssd']['formula'];
                    $new_value = $row['ess']['structure_det_value'];

                    if (!empty($formula) && !empty($remarks)) {

                        // Step 3: Replace value in remarks if item is present
                        if (preg_match('/(?:\b\d+_[A-Za-z0-9_]+|monthsal|rembalance)/i', $formula)) {
                            $updated_remarks = $this->updateRemarkValue($formula, $remarks, $emp);
                            // debug($updated_remarks);
                            // Step 4: Update via raw SQL
                            $this->EmployeeSalaryStructure->query("
                                                    UPDATE emp_salary_structure
                                                    SET remarks = '" . addslashes($updated_remarks) . "'
                                                    WHERE emp_salary_structure_pkey = '$emp_salary_structure_pkey'
                                                ");
                        }
                    }
                }
                // End


                $arr_items = $this->SalaryHike->query("
                                                    SELECT salary_head_item_fkey   
                                                    FROM salary_hike_details shd 
                                                    WHERE salary_hike_fkey = '$salary_hike_pkey'
                                                        AND status = 1
                                                        AND increment_amount > 0
                                                        AND salary_head_item_fkey IN 
                                                        (
                                                            SELECT salary_head_item_pkey
                                                            FROM `salary_head_items`
                                                            WHERE `head_fkey` IN (1,10)
                                                            AND `status` = 1
                                                        )
                                                        AND emp_fkey = $emp
                                                        ;
                                                ");
                // debug($arr_items);exit;
                // Step 2: Extract fkeys into a flat array
                $excluded_keys = array_map(function ($item) {
                    return $item['shd']['salary_head_item_fkey'];
                }, $arr_items);

                // Step 3: Build conditions and add NOT IN if applicable
                $conditions = array(
                    'emp_structure_id' => $salary_id,
                    'emp_fkey' => $emp,
                    'remarks IS NOT NULL',
                    'end_date_effective IS NULL'
                );

                if (!empty($excluded_keys)) {
                    $conditions['NOT'] = array('salary_head_item_fkey' => $excluded_keys);
                }

                // Evaluate remarks-based formulas
                $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find("all", array(
                    'fields' => 'emp_salary_structure_pkey, head_operator, remarks',
                    'conditions' => $conditions
                ));

                foreach ($arr_formulae_from_remarks as $row_formulae) {
                    $emp_salary_slip_pkey = $row_formulae['EmployeeSalaryStructure']['emp_salary_structure_pkey'];
                    $head_operator = $row_formulae['EmployeeSalaryStructure']['head_operator'];
                    $formula_raw = $row_formulae['EmployeeSalaryStructure']['remarks'];

                    // Remove whitespace
                    $formula = preg_replace("/\s+/", "", $formula_raw);

                    // Only allow simple math expressions with numbers and operators
                    if (preg_match('/^[0-9\.\+\-\*\/\(\)]+$/', $formula)) {
                        $salary_amount = 0;
                        @eval('$salary_amount = ' . $formula . ';');

                        if ($head_operator == 'Deduction') {
                            $salary_amount *= -1;
                        }

                        $this->EmployeeSalaryStructure->updateAll(
                            array('EmployeeSalaryStructure.structure_det_value' => round($salary_amount)),
                            array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                        );
                    } else {
                        // Optionally log or skip complex/non-evaluable formulas
                        // For debug purposes only:
                        // echo "Skipping invalid formula: $formula_raw\n";
                        continue;
                    }
                }

                // Final structure limit check
                $this->EmployeeSalaryStructure->query("CALL salary_structure_limit_prc('$emp', '$user_ids', @perr_msg)");


                // Edited by Akshay on 30-10-2025
                $this->SalaryHike->query(
                    "UPDATE salary_hike_details SET processed = 'Y', payout_month = ? 
                        WHERE salary_hike_fkey = ? AND emp_fkey = ? AND status = 1 
                        -- AND increment_amount != 0 
                        AND processed = 'N'",
                    array($payout_month, $salary_hike_pkey, $emp)
                );
                // End
            }

            // Update salary hike
            if (count($arr_not_processed) < count($arr_grouped) || count($arr_grouped) > count($arr_invalid_salary)) {
                $this->SalaryHike->query("UPDATE salary_hike SET action = 'Processed'  WHERE salary_hike_pkey = '$salary_hike_pkey'");
                $no_process = false;
            } else {
                $no_process = true;
            }

            // Edited by Akshay on 30-10-2025
            $error_message = '';
            if (!empty($arr_not_processed)) {
                $not_processed_count = count($arr_not_processed);
                $not_processed_list = implode(', ', $arr_not_processed);
                $error_message = "{$not_processed_count} employee(s) could not be processed due to invalid start date effective: " . $not_processed_list;
            }

            if (!empty($arr_invalid_salary)) {
                $not_processed_count = count($arr_invalid_salary);
                $not_processed_list = implode(', ', $arr_invalid_salary);
                if ($no_process == true) {
                    $error_message .= 'Employee(s) could not be processed due to invalid salary amount as per structure';
                }
                if (count($arr_grouped) == count($arr_invalid_salary)) {
                    $error_message .= "Employee(s) could not be processed due to invalid salary amount as per structure: " . $not_processed_list;
                }
            }

            // End

            // ✅ Return success response
            echo json_encode([
                'success' => !$no_process,
                'message' => $no_process ? 'Failed to process' : 'Processed successfully.',
                'error_message' => $error_message,
            ]);
            return;
        } catch (Exception $e) {
            debug($e);
            exit;
            echo json_encode([
                'success' => false,
                'message' => 'Some server issue occurred.'
            ]);
            return;
        }
    }
    // End

    public function itemIncerementReport()
    {
        $this->autoRender = false;

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmpSalaryCompUpload->useDbConfig = $this->Session->read('ds');
        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
        $this->SalaryHike->useDbConfig = $this->Session->read('ds');
        $this->SalaryHikeDetail->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $_POST;
        $salary_hike_pkey = $arr_form_data['salary_hike_pkey'];
        $is_multiple = $arr_form_data['is_multiple'];

        $arr_salary_hike = $this->SalaryHike->query("SELECT remarks FROM salary_hike sh WHERE salary_hike_pkey = '$salary_hike_pkey';");
        $remarks = isset($arr_salary_hike[0]['sh']['remarks']) ? $arr_salary_hike[0]['sh']['remarks'] : '';

        // Safe date formatting function
        $formatSafeDate = function ($dateStr, $format) {
            if (empty($dateStr) || $dateStr === '0000-00-00' || strtotime($dateStr) === false) {
                return '';
            }
            return date($format, strtotime($dateStr));
        };

        $arr_raw = $this->SalaryHike->query("
                                                SELECT shd.*, ei.EmpName, ei.employee_id, ei.branch,ei.designation, ei.department, shi.item, ss.structure_name    
                                                FROM salary_hike_details shd
                                                LEFT JOIN employee_info ei ON ei.emp_pkey = shd.emp_fkey
                                                LEFT JOIN salary_head_items shi ON (shi.salary_head_item_pkey = shd.salary_head_item_fkey AND shi.status = 1)
                                                LEFT JOIN salary_structure ss ON ss.structure_id = shd.structure_id
                                                WHERE shd.salary_hike_fkey = '$salary_hike_pkey'
                                                AND shd.status = 1
                                                AND (ROUND(shd.increment_amount) != 0 -- Edited by Akshay on 27-9-2025
                                                OR shd.salary_hike_fkey IN (SELECT salary_hike_pkey FROM salary_hike WHERE structure_change = 'Y')
                                                )
                                                GROUP BY shd.emp_fkey
                                            ");


        $arr_grouped = [];
        foreach ($arr_raw as $row) {
            $emp_fkey = $row['shd']['emp_fkey'];
            $entry = array_merge(
                $row['shd'],
                [
                    'EmpName'        => $row['ei']['EmpName'],
                    'employee_id'    => $row['ei']['employee_id'],
                    'branch'         => $row['ei']['branch'],
                    'designation'    => $row['ei']['designation'],
                    'department'     => $row['ei']['department'],
                    'item'           => $row['shi']['item'],
                    'structure_name' => $row['ss']['structure_name']
                ]
            );
            $arr_grouped[$emp_fkey][] = $entry;
        }
        // debug($arr_grouped);exit;
        // File name
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code)
            ? strtolower($str_company_code) . "_ItemWiseIncrementReport.xlsx"
            : "ItemWiseIncrementReport_" . time() . ".xlsx";

        // Load PHPExcel
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        App::import('Vendor', 'PHPExcel_IOFactory', array('file' => 'PHPExcel/IOFactory.php'));

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false); // Hide gridlines

        // Headers and column widths
        $headers = [
            'Sl No',                  // A
            'Employee Name',         // B
            'Employee ID',           // C
            'Branch', // G
            'Designation', // H
            'Department',
            'Start Date Effective(dd-mm-yyyy)', // I
            'Next Increment Date(dd-mm-yyyy)', // J
            'Arrear Need',           // K
            'Payout Month(dd-mm-yyyy)',         // L
            'Status' // M
            // 'Remarks'
        ];

        $columnWidths = [6, 25, 20, 30, 30, 30, 15, 30, 15, 30, 15];

        // Styling
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9D9D9'] // Light grey
            ],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]]
        ];

        // Write header row
        foreach ($headers as $col => $text) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($col);
            $cell = $colLetter . '1';
            $sheet->setCellValueByColumnAndRow($col, 1, $text);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
            $sheet->getColumnDimension($colLetter)->setWidth($columnWidths[$col]);
        }

        // Fill data and apply border
        $rowNum = 2;
        $slno = 1;
        foreach ($arr_grouped as $arr_emp_details) {
            foreach ($arr_emp_details as $value) {
                $sheet->setCellValueByColumnAndRow(0, $rowNum, $slno++);
                $sheet->setCellValueByColumnAndRow(1, $rowNum, trim($value['EmpName']));
                $sheet->setCellValueByColumnAndRow(2, $rowNum, $value['employee_id']);
                // $sheet->setCellValueByColumnAndRow(3, $rowNum, $value['item']);
                // $sheet->setCellValueByColumnAndRow(4, $rowNum, $value['current_amount']);
                // $sheet->setCellValueByColumnAndRow(5, $rowNum,  round(isset($value['new_amount']) ? $value['new_amount'] : 0));
                $sheet->setCellValueByColumnAndRow(3, $rowNum, $value['branch']);
                $sheet->setCellValueByColumnAndRow(4, $rowNum, $value['designation']);
                $sheet->setCellValueByColumnAndRow(5, $rowNum, $value['department']);
                $sheet->setCellValueByColumnAndRow(6, $rowNum, $formatSafeDate($value['with_effect_from'], 'd-m-Y'));
                $sheet->setCellValueByColumnAndRow(7, $rowNum, $formatSafeDate($value['next_increment_date'], 'd-m-Y'));
                $sheet->setCellValueByColumnAndRow(8, $rowNum, $value['arrear_salary']);
                $sheet->setCellValueByColumnAndRow(9, $rowNum, $formatSafeDate($value['payout_month'], 'F-Y'));
                $sheet->setCellValueByColumnAndRow(10, $rowNum, ($value['processed'] == 'Y') ? 'Processed' : 'Not processed');

                // Apply border
                for ($col = 0; $col <= 10; $col++) {
                    $cell = PHPExcel_Cell::stringFromColumnIndex($col) . $rowNum;
                    $sheet->getStyle($cell)->applyFromArray([
                        'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]]
                    ]);
                }

                $rowNum++;
            }
        }

        if (ob_get_length()) ob_end_clean();

        // Output XLSX
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$file_name\"");
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }


    public function incerementReport()
    {
        $this->autoRender = false;

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmpSalaryCompUpload->useDbConfig = $this->Session->read('ds');
        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
        $this->SalaryHike->useDbConfig = $this->Session->read('ds');
        $this->SalaryHikeDetail->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $_POST;
        $salary_hike_pkey = $arr_form_data['salary_hike_pkey'];
        $is_multiple = $arr_form_data['is_multiple'];

        $arr_salary_hike = $this->SalaryHike->query("SELECT remarks FROM salary_hike sh WHERE salary_hike_pkey = '$salary_hike_pkey';");
        $remarks = isset($arr_salary_hike[0]['sh']['remarks']) ? $arr_salary_hike[0]['sh']['remarks'] : '';

        $arr_raw = $this->SalaryHike->query("
                                                SELECT shd.*, ei.EmpName, ei.employee_id, ei.branch, ei.designation, ei.department, shi.item, ss.structure_name    
                                                FROM salary_hike_details shd
                                                LEFT JOIN employee_info ei ON ei.emp_pkey = shd.emp_fkey
                                                LEFT JOIN salary_head_items shi ON (shi.salary_head_item_pkey = shd.salary_head_item_fkey AND shi.status = 1)
                                                LEFT JOIN salary_structure ss ON ss.structure_id = shd.structure_id
                                                WHERE shd.salary_hike_fkey = '$salary_hike_pkey'
                                                AND shd.status = 1
                                                AND (shd.increment_amount != 0
                                                OR shd.salary_hike_fkey IN (SELECT salary_hike_pkey FROM salary_hike WHERE structure_change = 'Y')
                                                )
                                            ");



        $arr_grouped = [];
        foreach ($arr_raw as $row) {
            $emp_fkey = $row['shd']['emp_fkey'];
            $entry = array_merge(
                $row['shd'],
                [
                    'EmpName'        => $row['ei']['EmpName'],
                    'employee_id'    => $row['ei']['employee_id'],
                    'branch'         => $row['ei']['branch'],
                    'designation'    => $row['ei']['designation'],
                    'department'     => $row['ei']['department'],
                    'item'           => $row['shi']['item'],
                    'structure_name' => $row['ss']['structure_name']
                ]
            );
            $arr_grouped[$emp_fkey][] = $entry;
        }

        // Safe date formatting function
        $formatSafeDate = function ($dateStr, $format) {
            if (empty($dateStr) || $dateStr === '0000-00-00' || strtotime($dateStr) === false) {
                return '';
            }
            return date($format, strtotime($dateStr));
        };

        // File name
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code)
            ? strtolower($str_company_code) . "_GrossIncrementReport.xlsx"
            : "GrossIncrementReport_" . time() . ".xlsx";

        // Load PHPExcel
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        App::import('Vendor', 'PHPExcel_IOFactory', array('file' => 'PHPExcel/IOFactory.php'));

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false); // Hide gridlines

        // Headers and column widths
        $headers = [
            'Sl No',                  // A
            'Employee Name',         // B
            'Employee ID',           // C
            'Branch', // D
            'Designation', // E
            'Department', // F
            'Start Date Effective(dd-mm-yyyy)', // G
            'Next Increment Date(dd-mm-yyyy)', // H
            'Arrear Need',           // I
            'Payout Month(dd-mm-yyyy)', // J
            'Status'
        ];

        $columnWidths = [6, 25, 20, 30, 30, 30, 30, 30, 15, 30, 15];

        // Styling
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9D9D9'] // Light grey
            ],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]]
        ];

        // Write header row
        foreach ($headers as $col => $text) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($col);
            $cell = $colLetter . '1';
            $sheet->setCellValueByColumnAndRow($col, 1, $text);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
            $sheet->getColumnDimension($colLetter)->setWidth($columnWidths[$col]);
        }

        // Fill data and apply border
        $rowNum = 2;
        $slno = 1;
        foreach ($arr_grouped as $arr_emp_details) {
            foreach ($arr_emp_details as $value) {
                $sheet->setCellValueByColumnAndRow(0, $rowNum, $slno++);
                $sheet->setCellValueByColumnAndRow(1, $rowNum, trim($value['EmpName']));
                $sheet->setCellValueByColumnAndRow(2, $rowNum, $value['employee_id']);
                $sheet->setCellValueByColumnAndRow(3, $rowNum, $value['branch']);
                $sheet->setCellValueByColumnAndRow(4, $rowNum, $value['designation']);
                $sheet->setCellValueByColumnAndRow(5, $rowNum, $value['department']);
                $sheet->setCellValueByColumnAndRow(6, $rowNum, $formatSafeDate($value['with_effect_from'], 'd-m-Y'));
                $sheet->setCellValueByColumnAndRow(7, $rowNum, $formatSafeDate($value['next_increment_date'], 'd-m-Y'));
                $sheet->setCellValueByColumnAndRow(8, $rowNum, $value['arrear_salary']);
                $sheet->setCellValueByColumnAndRow(9, $rowNum, $formatSafeDate($value['payout_month'], 'F-Y'));
                $sheet->setCellValueByColumnAndRow(10, $rowNum, ($value['processed'] == 'Y') ? 'Processed' : 'Not processed');

                // Apply border
                for ($col = 0; $col <= 10; $col++) {
                    $cell = PHPExcel_Cell::stringFromColumnIndex($col) . $rowNum;
                    $sheet->getStyle($cell)->applyFromArray([
                        'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]]
                    ]);
                }

                $rowNum++;
            }
        }

        if (ob_get_length()) ob_end_clean();

        // Output XLSX
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$file_name\"");
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }



    public function viewSalaryIncrementForm($salary_hike_pkey = 0, $empName)
    {
        $this->autoRender = FALSE;
        $this->SalaryHike->useDbConfig = $this->Session->read('ds');

        $arr_salary_hike = $this->SalaryHike->query("SELECT remarks, item, structure_change, `action` FROM salary_hike sh WHERE salary_hike_pkey = '$salary_hike_pkey';");
        $remarks = isset($arr_salary_hike[0]['sh']['remarks']) ? $arr_salary_hike[0]['sh']['remarks'] : '';
        $is_item = isset($arr_salary_hike[0]['sh']['item']) ? $arr_salary_hike[0]['sh']['item'] : '';
        $action = isset($arr_salary_hike[0]['sh']['action']) ? $arr_salary_hike[0]['sh']['action'] : '';
        $is_multiple = ($empName == 'Multiple') ? 'Y' : 'N';
        $structure_change = isset($arr_salary_hike[0]['sh']['structure_change']) ? $arr_salary_hike[0]['sh']['structure_change'] : '';
        $this->set('structure_change', $structure_change);

        $item_condition = ($is_item == 'Y') ? 'ROUND(shd.increment_amount) != 0' : 'shd.increment_amount != 0'; // Edited by Akshay on 27-9-2025
        $arr_raw = $this->SalaryHike->query("
                                                SELECT shd.*, ei.EmpName, ei.employee_id, ei.branch, shi.item, ss.structure_name, shd.salary_head_item_fkey    
                                                FROM salary_hike_details shd
                                                LEFT JOIN employee_info ei ON ei.emp_pkey = shd.emp_fkey
                                                LEFT JOIN salary_head_items shi ON (shi.salary_head_item_pkey = shd.salary_head_item_fkey AND shi.status = 1)
                                                LEFT JOIN salary_structure ss ON ss.structure_id = shd.structure_id
                                                WHERE shd.salary_hike_fkey = '$salary_hike_pkey'
                                                AND shd.status = 1
                                                AND ($item_condition -- Edited by Akshay on 24-9-2025
                                                OR shd.salary_hike_fkey IN (SELECT salary_hike_pkey FROM salary_hike WHERE structure_change = 'Y')
                                                )
                                                GROUP BY shd.emp_fkey
                                            ");



        $arr_grouped = [];
        $empCounts = [];
        foreach ($arr_raw as $row) {
            $emp_fkey = $row['shd']['emp_fkey'];
            $salary_head_item_fkey = isset($row['shd']['salary_head_item_fkey']) ? $row['shd']['salary_head_item_fkey'] : 0;
            if ($salary_head_item_fkey != 0) {
                $is_item = 'Y';
            }
            // Count how many times each emp_fkey appears
            if (!isset($empCounts[$emp_fkey])) {
                $empCounts[$emp_fkey] = 1;
            } else {
                $empCounts[$emp_fkey]++;
            }
            $item = $row['shi']['item'];

            $entry = array_merge(
                $row['shd'],
                [
                    'EmpName'        => $row['ei']['EmpName'],
                    'employee_id'    => $row['ei']['employee_id'],
                    'branch'         => $row['ei']['branch'],
                    'item'           => $row['shi']['item'],
                    'structure_name' => $row['ss']['structure_name']
                ]
            );
            $arr_grouped[$emp_fkey][] = $entry;
        }

        // To know duplicates
        $hasDuplicates = false;
        foreach ($empCounts as $count) {
            if ($count > 1) {
                $hasDuplicates = true;
                break;
            }
        }

        if ($hasDuplicates) {
            $is_item = 'Y';
        }


        $this->set('arr_grouped', $arr_grouped);
        $this->set('salary_hike_pkey', $salary_hike_pkey);
        $this->set('remarks', $remarks);
        $this->set('is_item', $is_item);
        $this->set('action', $action);
        $this->set('is_multiple', $is_multiple);

        $this->render('view');
    }


    // Edited by Akshay on 27-6-2025
    public function getTempSalaryStructure($emp_pkey = 0, $structure_id, $gross)
    {
        $this->autoRender = FALSE;
        $this->response->type('json');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

        $company_code = $this->Session->read('company_code');

        // Edited by Akshay on 7-10-2025
        $gross_count = $this->EmployeeCTC->query("SELECT COUNT(*) as count FROM emp_ctc_transaction WHERE `emp_fkey` = '$emp_pkey' AND `end_date_effective` IS NULL");
        $gross_count = isset($gross_count[0][0]['count']) ? $gross_count[0][0]['count'] : 0;
        if ($gross_count == 0) {
            $arr_structure_eg_amt = $this->EmployeeCTC->query("SELECT structure_eg_amt FROM salary_structure WHERE structure_id = '$structure_id';");
            $gross = isset($arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt']) ? $arr_structure_eg_amt[0]['salary_structure']['structure_eg_amt'] : 0;
        }
        // End

        // Edited by Akshay on 31-3-2026
        $arr_emp_id = $this->EmployeeCTC->query("SELECT ep.emp_type FROM emp_proff ep WHERE ep.emp_fkey = '$emp_pkey'"); // Edited by Akshay on 28-3-2026
        $emp_type = isset($arr_emp_id[0]['ep']['emp_type']) ? strtoupper(trim($arr_emp_id[0]['ep']['emp_type'])) : ''; // Edited by Akshay on 28-3-2026
        $anual_gross =  ($emp_type == 'DAILY WAGES' || $emp_type == 'HOURLY WAGES') ? $gross : $gross * 12;
        // End

        $arr_sal_structure = $this->EmployeeCTC->query("CALL structure_preview_prc('$company_code', '$emp_pkey', '$structure_id','$anual_gross' );");
        // debug("CALL structure_preview_fn('$company_code', '$emp_pkey', '$structure_id','$anual_gross' );");
        // debug($arr_sal_structure);
        // exit;

        $valid_keys_result = $this->EmployeeCTC->query("
                                                SELECT salary_head_item_pkey 
                                                FROM salary_head_items
                                                WHERE 
                                                -- head_fkey IN (1, 4)
                                                head_fkey = 1
                                                AND item_part = 'Direct'
                                                AND status = 1
                                            ");
        $valid_keys = array_column(array_column($valid_keys_result, 'salary_head_items'), 'salary_head_item_pkey');
        $total_direct_value = 0;

        $result = [];
        $result_indirect = [];
        $result_emp_contr = [];
        foreach ($arr_sal_structure as $item) {
            $head_operator = ($item['salary_preview_temp']['head_operator']) ? $item['salary_preview_temp']['head_operator'] : '';
            $item_part = ($item['salary_preview_temp']['item_part']) ? $item['salary_preview_temp']['item_part'] : '';
            $monthly = false; // Edited by Akshay on 9-10-2025
            if ($head_operator == 'Addition') {

                if ($item_part == 'Direct') {

                    $key = isset($item['salary_preview_temp']['salary_head_item_fkey']) ? $item['salary_preview_temp']['salary_head_item_fkey'] : '';
                    $value = isset($item['salary_preview_temp']['structure_det_value']) ? $item['salary_preview_temp']['structure_det_value'] : 0;
                    $desc = isset($item['salary_preview_temp']['salary_head_item_desc']) ? $item['salary_preview_temp']['salary_head_item_desc'] : '';

                    // Edited by Akshay on 18-11-2025
                    if (in_array($key, $valid_keys)) {
                        $result[] = [
                            'key' => $key,
                            'desc' => $desc,
                            'value' => ($gross_count != 0) ? $value : 0
                        ];

                        $total_direct_value += $value;
                        $monthly = true; // Edited by Akshay on 9-10-2025
                    }
                    // End
                }

                if ($item_part == 'Indirect')
                    $result_indirect[] = [
                        'key' => ($item['salary_preview_temp']['salary_head_item_fkey']) ? $item['salary_preview_temp']['salary_head_item_fkey'] : '',
                        'desc' => ($item['salary_preview_temp']['salary_head_item_desc']) ? $item['salary_preview_temp']['salary_head_item_desc'] : '',
                        'value' => ($gross_count != 0) ? ($item['salary_preview_temp']['structure_det_value'] ? $item['salary_preview_temp']['structure_det_value'] : '') : 0,
                        'monthly_contr' => $monthly // Edited by Akshay on 9-10-2025
                    ];
            } elseif ($head_operator == 'Deduction') {
                if ($item_part == 'Direct')
                    $result_emp_contr[] = [
                        'key' => ($item['salary_preview_temp']['salary_head_item_fkey']) ? $item['salary_preview_temp']['salary_head_item_fkey'] : '',
                        'desc' => ($item['salary_preview_temp']['salary_head_item_desc']) ? $item['salary_preview_temp']['salary_head_item_desc'] : '',
                        'value' => ($gross_count != 0) ? ($item['salary_preview_temp']['structure_det_value'] ? $item['salary_preview_temp']['structure_det_value'] : '') : 0,
                        'monthly_contr' => $monthly // Edited by Akshay on 9-10-2025
                    ];
            }
        }


        $arr_emp_info = $this->EmployeeCTC->query("SELECT emp_pkey, branch, designation, department, joining_date 
                                                        FROM employee_info 
                                                        WHERE emp_pkey = $emp_pkey;
                                                        ");

        $emp = [];
        foreach ($arr_emp_info as $emp_info) {
            foreach ($emp_info as $key => $info) {
                $emp[] = $info;
            }
        }


        echo json_encode(['success' => true, 'structure_id' => $structure_id, 'structure' => $result, 'structure_indirect' => $result_indirect, 'emp_contribution' => $result_emp_contr, 'emp' => $emp[0], 'ctc' => $anual_gross, 'temp_monthly_gross' => $total_direct_value]); // Edited by Akshay on 8-10-2025
        return;
    }
    // End

    public function deleteIncrement()
    {
        $this->autoRender = false;
        $this->layout = false;

        $salary_hike_pkey = $this->request->data('salary_hike_pkey'); // safer than $_POST directly
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

        if (!empty($salary_hike_pkey)) {
            try {
                $result = $this->EmployeeCTC->query("UPDATE salary_hike SET status = 0 WHERE salary_hike_pkey = '$salary_hike_pkey'");

                echo json_encode([
                    'success' => true,
                    'message' => 'Record deleted successfully.'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid salary_hike_pkey.'
            ]);
        }
    }

    public function fileUpload()
    {
        $this->autoRender = false;
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryStructures->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $user_group = $this->Session->read('user_group');
        $this->set('user_group', $user_group);
        $emp_pkey = $this->Session->read('emp_fkey');
        $is_ho = 0;
        $this->set('is_ho', $is_ho);
        if ($user_group == 2) {
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            $this->set('is_ho', $is_ho);
            if ($is_ho != 1) {
                $conditions[] = array("branch_code" => $is_ho, "status" => 1);
            }
        }
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $conditions[] = array("branch_code" => $cur_emp_branch, "status" => 1);
        } else {
            $conditions[] = array("status" => 1);
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $conditions[] = array("branch_code !=" => $branch);
        }
        $this->set("arr_branches", $arr_branches = $this->Units->find("all", array("conditions" => $conditions)));

        // $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), 'conditions' => $conditions)));
        $arr_employees = $this->EmployeeDetails->query("SELECT EmpName, employee_id, emp_pkey FROM employee_info ei WHERE emp_status = 1;");
        $this->set("arr_employees", $arr_employees);

        $arr_structure = $this->SalaryStructures->find("all", array("conditions" => array("structure_active" => 1)));
        $this->set("arr_structure", $arr_structure);
        $this->render('upload');
    }

    public function getGrossByEmp($emp_pkey = null)
    {
        $this->autoRender = false;
        $this->layout = false;

        if (empty($emp_pkey) || !is_numeric($emp_pkey)) {
            echo json_encode(['success' => false, 'message' => 'Invalid Employee ID']);
            return;
        }

        $arr_gross = $this->EmployeeCTC->query("
        SELECT SUM(ectc.structure_det_value) AS gross
        FROM emp_salary_structure ectc
        LEFT JOIN salary_head_items shi ON shi.salary_head_item_pkey = ectc.salary_head_item_fkey
        WHERE ectc.head_operator = 'Addition' 
          AND ectc.emp_fkey = $emp_pkey
          AND ectc.item_part = 'direct' 
          AND ectc.end_date_effective IS NULL
          AND shi.head_fkey = 1
        ");

        $arr_sal_structure = $this->EmployeeCTC->query("SELECT structure_id FROM emp_proff WHERE emp_fkey = $emp_pkey;");
        $sal_structure = isset($arr_sal_structure[0]['emp_proff']['structure_id']) ? $arr_sal_structure[0]['emp_proff']['structure_id'] : 0;

        $gross = isset($arr_gross[0][0]['gross']) ? (float)$arr_gross[0][0]['gross'] : 0;

        echo json_encode([
            'success' => true,
            'gross' => $gross,
            'structure_id' => $sal_structure
        ]);
    }


    // Edited by Akshay on 24-7-2025
    function updateRemarkValue($formula, $remarks, $emp)
    {
        // =========================
        // 1. FETCH DB VALUES
        // =========================
        $rows = $this->EmployeeSalaryStructure->query("
        SELECT 
            ess.emp_salary_structure_pkey,
            ess.salary_head_item_fkey,
            ess.structure_det_value,
            ess.salary_head_item_desc,
            ess.head_operator,
            ess.item_part,
            shi.head_fkey
        FROM emp_salary_structure ess
        LEFT JOIN salary_head_items shi 
            ON (ess.salary_head_item_fkey = shi.salary_head_item_pkey)
        WHERE ess.emp_fkey = '$emp'
          AND ess.end_date_effective IS NULL
        ");

        // =========================
        // 2. BUILD TOKEN MAP + MONTHSAL
        // =========================
        $map = [];
        $monthsal = 0;

        foreach ($rows as $r) {

            $itemKey       = $r['ess']['salary_head_item_fkey'];
            $value         = $r['ess']['structure_det_value'];
            $head_operator = $r['ess']['head_operator'];
            $item_part     = $r['ess']['item_part'];
            $head_fkey     = isset($r['shi']['head_fkey']) ? $r['shi']['head_fkey'] : 0;

            $item = trim($r['ess']['salary_head_item_desc']);
            $item = str_replace('-', ' ', $item);
            $item = preg_replace('/\s+/', '_', $item);
            $item = str_replace(['(', ')'], '_', $item);

            $token = $itemKey . '_' . $item;
            $map[$token] = $value;

            // monthsal = sum of Direct Additions (salary head)
            if ($head_fkey == 1 && $head_operator == 'Addition' && $item_part == 'Direct') {
                $monthsal += $value;
            }
        }

        $map['monthsal'] = $monthsal;

        // =========================
        // 3. FETCH REMBALANCE %
        // =========================
        $arr_rembalance = $this->EmployeeSalaryStructure->query("
        SELECT structure_derived_perc
        FROM salary_structure_details
        WHERE structure_id = (
            SELECT IFNULL(ep.structure_id, 0)
            FROM emp_proff ep
            WHERE ep.emp_fkey = '$emp'
        )
        AND TRIM(structure_formula) = 'Remaining Balance'
        ");

        $rembalance_prc = 0;
        if (isset($arr_rembalance[0]['salary_structure_details']['structure_derived_perc'])) {
            $rembalance_prc = $arr_rembalance[0]['salary_structure_details']['structure_derived_perc'];
        }

        $rembalance = ($rembalance_prc > 0) ? ($monthsal * $rembalance_prc) / 100 : 0;
        $map['rembalance'] = $rembalance;

        // =========================
        // 4. EXTRACT TOKENS FROM FORMULA
        // =========================
        // Includes: salary-head tokens, monthsal, rembalance
        preg_match_all(
            '/(?:monthsal|rembalance|[0-9]+_[A-Za-z0-9_]+)/',
            $formula,
            $matches
        );
        $tokens = $matches[0];

        // =========================
        // 5. EXTRACT NUMBERS FROM REMARKS
        // =========================
        preg_match_all('/\b\d+(\.\d+)?\b/', $remarks, $matches2);
        $numbers = $matches2[0];

        // =========================
        // 6. POSITION-BASED REPLACEMENT
        // =========================
        foreach ($tokens as $i => $token) {

            if (!isset($numbers[$i])) {
                continue;
            }

            if (!isset($map[$token])) {
                continue;
            }

            $oldValue = $numbers[$i];
            $newValue = $map[$token];

            // Replace only the first occurrence at this position
            $remarks = preg_replace(
                '/\b' . preg_quote($oldValue, '/') . '\b/',
                $newValue,
                $remarks,
                1
            );
        }

        return $remarks;
    }






    // End

    // Edited by Akshay on 9-10-2025
    public function download($salary_hike_pkey)
    {
        $this->autoRender = false;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

        // Fetch employee keys
        $arr_emp_fkey = $this->EmployeeCTC->query("SELECT DISTINCT emp_fkey FROM salary_hike_details WHERE salary_hike_fkey = '$salary_hike_pkey' 
                                                    -- AND processed = 'Y'
                                                    ;");
        $arr_pkey = array_column(array_column($arr_emp_fkey, 'salary_hike_details'), 'emp_fkey');

        $arr_salary_for_template = [];
        $str_query = "select desg.desig_name,user_credentials.user_id,termination.last_approved_working_date,dpt.dept_name,br.branch_name,ed.first_name,ed.last_name,ed.status,
                            ectc.emp_fkey,ectc.salary_head_item_fkey,ectc.salary_head_item_desc,ectc.structure_det_value,ectc.head_operator,ectc.head_type,ectc.item_part,ep.emp_company_id,ep.joining_date,shead.salary_head_item_order1 from emp_salary_structure as ectc left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) left join branches as br on (br.branch_code = ep.emp_branch and br.status=1) 
                            left join department as dpt on (dpt.dept_code = ep.emp_dept) left join termination as termination on (termination.emp_fkey = ep.emp_fkey 
                            and termination.status = 1 ) left join user_credentials as user_credentials on (user_credentials.emp_fkey = ep.emp_fkey) 
                            left join designation as desg on (desg.desig_code = ep.designation) 
                            left join salary_head_items as shead on (shead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                            where ectc.head_operator = 'ADDITION' and ectc.emp_fkey = '_emp_pkey' and ectc.end_date_effective is null 
                            and salary_head_item_fkey in(select salary_head_item_pkey from salary_head_items where head_fkey in (1,4,10)) 
                            group by ectc.salary_head_item_desc 
                            union
                            select desg.desig_name,user_credentials.user_id,termination.last_approved_working_date,dpt.dept_name,br.branch_name,ed.first_name,ed.last_name,ed.status,
                            ectc.emp_fkey,ectc.salary_head_item_fkey,ectc.salary_head_item_desc,ectc.structure_det_value,ectc.head_operator,ectc.head_type,ectc.item_part,ep.emp_company_id,ep.joining_date ,shead.salary_head_item_order1 from emp_variable_pay_upload as ectc left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) left join branches as br on (br.branch_code = ep.emp_branch and br.status=1) 
                            left join department as dpt on (dpt.dept_code = ep.emp_dept) left join termination as termination on (termination.emp_fkey = ep.emp_fkey 
                            and termination.status = 1 ) left join user_credentials as user_credentials on (user_credentials.emp_fkey = ep.emp_fkey) 
                            left join designation as desg on (desg.desig_code = ep.designation) 
                            left join salary_head_items as shead on (shead.salary_head_item_pkey = ectc.salary_head_item_fkey) where ectc.emp_fkey = '_emp_pkey' order by salary_head_item_order1, CASE WHEN item_part = 'Indirect' THEN 1 ELSE 0 END;";

        foreach ($arr_pkey as $emp_pkey) {
            $arr_sal_structure = $this->EmployeeCTC->query("SELECT structure_id FROM emp_proff WHERE emp_fkey = '$emp_pkey';");
            $sal_structure = isset($arr_sal_structure[0]['emp_proff']['structure_id'])? $arr_sal_structure[0]['emp_proff']['structure_id']: 0;
            $arr_results = $this->EmployeeCTC->query(str_replace("_emp_pkey", $emp_pkey, $str_query));
            if (!empty($arr_results) && $sal_structure != 0) {
                $arr_salary_for_template[0][] = ['summary' => [$arr_results]];
            }
        }

        // if (empty($arr_salary_for_template)) {
        //     echo "No data available to export.";
        //     exit;
        // }

        App::import('Vendor', 'PHPExcel', ['file' => 'PHPExcel.php']);
        $objPHPExcel = new PHPExcel();

        $user_name = $this->Session->read('user_name');
        $date_time = date('d-m-Y H:i:s');

        $objPHPExcel->getProperties()->setCreator("Administrator")
            ->setTitle("Cost To Company Report");

        $worksheet = $objPHPExcel->getActiveSheet();
        $worksheet->setShowGridlines(false);

        // Report header
        $worksheet->setCellValue('A1', 'Cost To Company Detailed Report');
        $worksheet->mergeCells('A1:F1');
        $worksheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $worksheet->setCellValue('A2', "Report Run by $user_name at $date_time");
        $worksheet->mergeCells('A2:F2');
        $worksheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $rowcount = 3;

        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        // Employee Data Loop
        foreach ($arr_salary_for_template as $value) {
            foreach ($value as $values) {
                $emp = $values['summary'][0][0][0];

                // Employee Info
                $worksheet->setCellValue("A$rowcount", "Employee Name: " . $emp['first_name'] . ' ' . $emp['last_name']);
                $worksheet->mergeCells("A$rowcount:F$rowcount");
                $worksheet->getStyle("A$rowcount")->getFont()->setBold(true);
                $worksheet->getStyle("A$rowcount")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $rowcount++;

                $worksheet->setCellValue("A$rowcount", "EMP ID: " . $emp['emp_company_id']);
                $worksheet->setCellValue("B$rowcount", "Branch: " . $emp['branch_name']);
                $worksheet->setCellValue("C$rowcount", "Designation: " . $emp['desig_name']);
                $worksheet->setCellValue("D$rowcount", "Department: " . $emp['dept_name']);
                $rowcount++;

                // Table header
                $worksheet->setCellValue("A$rowcount", "Salary Component");
                $worksheet->mergeCells("A$rowcount:E$rowcount");
                $worksheet->setCellValue("F$rowcount", "Amount");
                $worksheet->getStyle("A$rowcount:F$rowcount")->getFont()->setBold(true);
                $rowcount++;

                // Salary components
                $sum = 0;

                foreach ($values['summary'][0] as $val) {
                    $item_part = $val[0]['item_part'];
                    // if ($val[0]['structure_det_value'] != '0' && $item_part == 'Direct') {
                    if ($item_part == 'Direct') {
                        $desc = $val[0]['salary_head_item_desc'];
                        $det = ($val[0]['head_type'] == 'fixed' || $val[0]['head_type'] == 'manually' || $val[0]['head_type'] == 'limit')
                            ? $val[0]['structure_det_value'] : round($val[0]['structure_det_value']);
                        $sum += $det;

                        $worksheet->setCellValue("A$rowcount", $desc);
                        $worksheet->mergeCells("A$rowcount:E$rowcount");
                        $worksheet->setCellValue("F$rowcount", $det);
                        $rowcount++;
                    }
                }

                // Grand Total
                $worksheet->setCellValue("A$rowcount", "Gross Total");
                $worksheet->mergeCells("A$rowcount:E$rowcount");
                $worksheet->setCellValue("F$rowcount", $sum);
                $worksheet->getStyle("A$rowcount:F$rowcount")->getFont()->setBold(true);
                $rowcount++;

                // Indirect
                foreach ($values['summary'][0] as $val) {
                    $item_part = $val[0]['item_part'];
                    // if ($val[0]['structure_det_value'] != '0' && $item_part == 'Indirect') {
                    if ($item_part == 'Indirect') {
                        $desc = $val[0]['salary_head_item_desc'];
                        $det = ($val[0]['head_type'] == 'fixed' || $val[0]['head_type'] == 'manually' || $val[0]['head_type'] == 'limit')
                            ? $val[0]['structure_det_value'] : round($val[0]['structure_det_value']);
                        $sum += $det;

                        $worksheet->setCellValue("A$rowcount", $desc);
                        $worksheet->mergeCells("A$rowcount:E$rowcount");
                        $worksheet->setCellValue("F$rowcount", $det);
                        $rowcount++;
                    }
                }

                // Grand Total
                $worksheet->setCellValue("A$rowcount", "Grand Total");
                $worksheet->mergeCells("A$rowcount:E$rowcount");
                $worksheet->setCellValue("F$rowcount", $sum);
                $worksheet->getStyle("A$rowcount:F$rowcount")->getFont()->setBold(true);
                $rowcount += 2;
            }
        }

        if (empty($arr_salary_for_template)) {
            $worksheet->setCellValue("A3", "No structure details available.");
            $worksheet->mergeCells("A3:F3");
        }

        // Sheet title
        $objPHPExcel->getActiveSheet()->setTitle('Cost To Company Report');

        // Output file
        $file_name = "CostToCompany_" . date('Ymd_His') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$file_name\"");
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    // End

    // Edited by Akshay on 11-10-2025
    public function employeelistPending()
    {
        $this->autoRender = false;
        $arr_request_data = $_POST;

        $employee  = isset($_POST['employee'])  ? $_POST['employee']  : '';
        $branch    = isset($_POST['branch'])    ? $_POST['branch']    : '';
        $structure = isset($_POST['structure']) ? $_POST['structure'] : '';
        $status    = isset($_POST['status'])    ? $_POST['status']    : '';

        $this->SalaryHike->useDbConfig = $this->Session->read('ds');

        $limit = isset($_REQUEST['rows']) ? (int)$_REQUEST['rows'] : 10;
        $page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
        $offset = ($page - 1) * $limit;


        $emp_name =  isset($_REQUEST['emp']) ? $_REQUEST['emp'] : '';
        // Condtions
        // Status filter (Processed / Not Processed)
        $filter_conditions = '';

        if (!empty($employee)) {
            $filter_conditions .= " AND ect.emp_fkey = '$employee' ";
        }

        if (!empty($branch)) {
            $filter_conditions .= " AND ei.branch_code = '$branch'";
        }

        if (!empty($structure)) {
            $filter_conditions .= " AND ep.structure_id = '$structure'";
        }

        if (trim($emp_name) != '') {
            $filter_conditions .= " AND ei.EmpName LIKE CONCAT( '%', '$emp_name', '%') ";
        }


        // Edited by Akshay on 13-10-2025
        $condition_next_increment_due = "DATEDIFF(ect.next_increment_date, CURDATE()) <= 44 AND ect.next_increment_date >= CURDATE()";
        $condition_next_increment_overdue = "ect.next_increment_date < CURDATE()";
        $condition_no_structure = "ep.structure_id IS NULL";

        // Apply based on $status
        switch ($status) {
            case 'Due':
                $extra_conditions = "AND $condition_next_increment_due AND ect.next_increment_date IS NOT NULL AND ect.next_increment_date != '0000-00-00'";
                break;
            case 'Overdue':
                $extra_conditions = "AND $condition_next_increment_overdue AND ect.next_increment_date IS NOT NULL AND ect.next_increment_date != '0000-00-00'";
                break;
            case 'NoStructure':
                $extra_conditions = "AND $condition_no_structure";
                break;
            default:
                $extra_conditions = "AND (
                                ($condition_next_increment_due 
                                 AND ect.next_increment_date IS NOT NULL 
                                 AND ect.next_increment_date != '0000-00-00')
                                OR
                                ($condition_next_increment_overdue 
                                 AND ect.next_increment_date IS NOT NULL 
                                 AND ect.next_increment_date != '0000-00-00')
                                OR
                                ($condition_no_structure)
                             )";
                break;
        }
        // End

        // Main paginated query
        $sql = "
                    SELECT 
                        ect.emp_ctc_transaction,
                        ect.next_increment_date,
                        ei.emp_pkey,
                        ss.structure_name,

                        ei.EmpName,
                        ei.employee_id,
                        ei.branch
                    FROM employee_info ei
                    LEFT JOIN emp_proff ep ON ep.emp_fkey = ei.emp_pkey
                    LEFT JOIN emp_ctc_transaction ect ON (ect.emp_fkey = ep.emp_fkey AND ect.end_date_effective IS NULL) 
                    LEFT JOIN salary_structure ss ON ss.structure_id = ep.structure_id
                    WHERE ei.emp_status = 1
                    $extra_conditions
                    $filter_conditions
                    -- AND ect.next_increment_date IS NOT NULL AND ect.next_increment_date != '0000-00-00'
                    GROUP BY ei.emp_pkey
                    ORDER BY 
                        -- First, employees with a next_increment_date
                        CASE WHEN ect.next_increment_date IS NULL THEN 1 ELSE 0 END,
                        -- Sort by next_increment_date if exists, else by EmpName
                        COALESCE(ect.next_increment_date, '9999-12-31') ASC,
                        ei.EmpName ASC
                    LIMIT $offset, $limit
                ";

        $arr_att = $this->SalaryHike->query($sql);

        $count_sql = "
                        SELECT COUNT(*) AS total_count FROM (
                            SELECT ei.emp_pkey
                            FROM employee_info ei
                            LEFT JOIN emp_proff ep ON ep.emp_fkey = ei.emp_pkey
                            LEFT JOIN emp_ctc_transaction ect 
                                ON (ect.emp_fkey = ep.emp_fkey AND ect.end_date_effective IS NULL)
                            LEFT JOIN salary_structure ss 
                                ON ss.structure_id = ep.structure_id
                            WHERE ei.emp_status = 1
                            $extra_conditions
                            $filter_conditions
                            -- AND ect.next_increment_date IS NOT NULL AND ect.next_increment_date != '0000-00-00'
                            GROUP BY ei.emp_pkey
                        ) AS temp
                    ";
        $arr_total_count = $this->SalaryHike->query($count_sql);
        $total = isset($arr_total_count[0][0]['total_count']) ? $arr_total_count[0][0]['total_count'] : 0;

        $resp_att = array();
        $resp_att['total'] = $total;
        $resp_att['rows'] = array();

        foreach ($arr_att as $key => $value) {
            $out = array();
            $pkey = $value['ect']['emp_ctc_transaction'];
            $out['ect_pkey'] = $pkey;
            // Edited by Akshay on 9-10-2025
            $out['next_increment_date'] = (
                !empty($value['ect']['next_increment_date']) &&
                $value['ect']['next_increment_date'] != '0000-00-00'
            )
                ? date('d-m-Y', strtotime($value['ect']['next_increment_date']))
                : '';
            // End
            $out['status']           = 'Due';
            $out['emp_fkey']     = $emp_pkey = isset($value['ei']['emp_pkey']) ? $value['ei']['emp_pkey'] : '';
            $out['salary_structure'] = isset($value['ss']['structure_name']) ? $value['ss']['structure_name'] : 'N/A';
            $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : 'Multiple';
            $out['empid'] = isset($value['ei']['employee_id']) ? $value['ei']['employee_id'] : 'N/A';
            $out['branch'] = isset($value['ei']['branch']) ? $value['ei']['branch'] : 'N/A';

            $nextIncrement = !empty($value['ect']['next_increment_date']) ? $value['ect']['next_increment_date'] : null;
            $today = time();
            if ($nextIncrement) {
                $nextIncrementTs = strtotime(date('Y-m-d', strtotime($nextIncrement)));
                $todayTs         = strtotime(date('Y-m-d'));
                $diffDays = ($nextIncrementTs - $todayTs) / (60 * 60 * 24);

                if ($diffDays < 0) {
                    // Date is before today
                    $out['status'] = 'Overdue';
                } elseif ($diffDays <= 44) {
                    // Within 45 days from today
                    $out['status'] = 'Due';
                } else {
                    $out['status'] = 'None';
                }
            } else {
                $out['status'] = 'None';
            }

            $arr_structure = $this->SalaryHike->query("SELECT structure_id
                                        FROM `emp_proff`
                                        WHERE `emp_fkey` = '$emp_pkey';");
            $structure = isset($arr_structure[0]['emp_proff']['structure_id']) ? $arr_structure[0]['emp_proff']['structure_id'] : 0;
            $out['status'] = ($structure != 0) ? $out['status'] : 'NoStructure';
            $resp_att['rows'][] = $out;
        }

        echo json_encode($resp_att);
    }
    // End

    // Edited by Akshay on 26-11-2025
    public function getSummaryValue()
    {
        $this->autoRender = false;
        $this->SalaryHike->useDbConfig = $this->Session->read('ds');

        $sql = "
        SELECT 
            SUM(CASE 
                    WHEN ep.structure_id IS NULL THEN 1 
                    ELSE 0 
                END) AS no_structure,

            SUM(CASE 
                    WHEN ect.next_increment_date IS NOT NULL 
                     AND ect.next_increment_date != '0000-00-00'
                     AND DATEDIFF(ect.next_increment_date, CURDATE()) <= 44
                     AND ect.next_increment_date >= CURDATE()
                    THEN 1 
                    ELSE 0 
                END) AS due,

            SUM(CASE 
                    WHEN ect.next_increment_date IS NOT NULL
                     AND ect.next_increment_date != '0000-00-00'
                     AND ect.next_increment_date < CURDATE()
                    THEN 1 
                    ELSE 0 
                END) AS over_due
        FROM employee_info ei
        LEFT JOIN emp_proff ep ON ep.emp_fkey = ei.emp_pkey
        LEFT JOIN emp_ctc_transaction ect 
            ON (ect.emp_fkey = ep.emp_fkey AND ect.end_date_effective IS NULL)
        WHERE ei.emp_status = 1
    ";

        $arr_status = $this->SalaryHike->query($sql);

        $resp = array();
        $resp['pending']   = intval(isset($arr_status[0][0]['no_structure']) ? $arr_status[0][0]['no_structure'] : 0);
        $resp['due']       = intval(isset($arr_status[0][0]['due']) ? $arr_status[0][0]['due'] : 0);
        $resp['over_due']  = intval(isset($arr_status[0][0]['over_due']) ? $arr_status[0][0]['over_due'] : 0);

        echo json_encode($resp);
    }

    // End
}
